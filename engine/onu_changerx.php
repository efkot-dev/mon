<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.monitor.php';
$added = date('Y-m-d H:i:s');
if (isset($olt) && (int)$olt > 0) {
    $id_device = (int)$olt;
}
if (!isset($id_device) || (int)$id_device <= 0) {
    return;
}
$timer = !empty($confPMon['TIMER_CHANGE_RX']) ? (int)$confPMon['TIMER_CHANGE_RX'] : 10;
$criticalCountChangeRx = !empty($confPMon['CRITICAL_COUNT_CHANGE_RX']) ? (int)$confPMon['CRITICAL_COUNT_CHANGE_RX'] : 5;
$stmtSwitch = $pdo->prepare("SELECT id, place FROM switch WHERE id = :id LIMIT 1");
$stmtSwitch->execute([':id' => (int)$id_device]);
$switch = $stmtSwitch->fetch(PDO::FETCH_ASSOC);
if (!$switch) {
    return;
}
$stmtPon = $pdo->prepare("SELECT id, oltid, sfpid, pon, `count` FROM switch_pon WHERE oltid = :oltid AND sfpid IS NOT NULL");
$stmtPon->execute([':oltid' => (int)$switch['id']]);
$ponRows = $stmtPon->fetchAll(PDO::FETCH_ASSOC);
if (!$ponRows) {
    return;
}
$portMeta = [];
$portIds = [];
foreach ($ponRows as $row) {
    $sfpid = (int)$row['sfpid'];
    if ($sfpid <= 0) {
        continue;
    }
    $portMeta[$sfpid] = ['pon' => (string)$row['pon'],'count' => (int)$row['count']];
    $portIds[] = $sfpid;
}
if (!$portIds) {
    return;
}
$placeholders = implode(',', array_fill(0, count($portIds), '?'));
$sqlCounts = "SELECT portolt, rxstatus, COUNT(idonu) AS bad_rx FROM onus WHERE olt = ? AND portolt IN ($placeholders) AND rxstatus IN ('up', 'down') AND changerx >= (NOW() - INTERVAL ? MINUTE) GROUP BY portolt, rxstatus";
$stmtCounts = $pdo->prepare($sqlCounts);
$params = array_merge([(int)$switch['id']], $portIds, [$timer]);
$stmtCounts->execute($params);
$countRows = $stmtCounts->fetchAll(PDO::FETCH_ASSOC);
if (!$countRows) {
    return;
}
$signalLangByStatus = ['up' => $lang['bades_signal'],'down' => $lang['super_signal']];
$iconByStatus = ['up' => 'myxa','down' => 'super'];
$stmtInsertNotification = $pdo->prepare("INSERT INTO notification (status, type, system, message, added) VALUES (1, 6, 'pinger', :message, :added)");
foreach ($countRows as $row) {
    $sfpid = (int)$row['portolt'];
    $rxStatus = (string)$row['rxstatus'];
    $badRx = (int)$row['bad_rx'];
    if (!isset($portMeta[$sfpid]) || !isset($signalLangByStatus[$rxStatus])) {
        continue;
    }
    if ($badRx <= 0) {
        continue;
    }
    $totalOnu = $portMeta[$sfpid]['count'];
    if ($totalOnu <= 0) {
        continue;
    }
    $percent = (int)((100 / $totalOnu) * $badRx);
    if ($percent <= $criticalCountChangeRx) {
        continue;
    }
    $sender = "[icon-" . $iconByStatus[$rxStatus] . "][b]" . $switch['place'] . "[/b] " .
        $signalLangByStatus[$rxStatus] . " [b]" . $portMeta[$sfpid]['pon'] . "[/b] " .
        $lang['count'] . ": [b]" . $badRx . "[/b]";
    $stmtInsertNotification->execute([':message' => $sender,':added' => $added]);
}
?>
