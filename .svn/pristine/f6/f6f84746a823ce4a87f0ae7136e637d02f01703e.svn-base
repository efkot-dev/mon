<?php
/*
=====================================================
 Copyright (c) 2024 PMon
=====================================================
 1. notifications about offline ONTs
 2. notifications about Loss ONTs
=====================================================
*/
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

require ROOT_DIR . '/inc/init.monitor.php';
require ROOT_DIR . '/inc/functions/pmon.php';

$timer = date('Y-m-d H:i:s');

$stmtTempRead = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file LIMIT 1");
$stmtTempUpsert = $pdo->prepare("
    INSERT INTO tempdate (file, data)
    VALUES (:file, :data)
    ON DUPLICATE KEY UPDATE
      data = VALUES(data),
      updated_at = :updated_at,
      last_processed = :last_processed
");
$stmtNotif = $pdo->prepare("
    INSERT INTO notification (status, type, system, message, added)
    VALUES (1, 27, 'monitor', :message, :added)
");

if (!empty($confPMon['ONU_OFF']) && (int)$confPMon['ONU_OFF'] === 1) {
    $sqlCounts = "
        SELECT
            SUM(CASE WHEN offline >= CURDATE() AND status = '2' THEN 1 ELSE 0 END) AS off_onu,
            SUM(CASE WHEN status = '1' THEN 1 ELSE 0 END) AS active_onu
        FROM onus
    ";
    $countRow = $pdo->query($sqlCounts)->fetch(PDO::FETCH_ASSOC) ?: ['off_onu' => 0, 'active_onu' => 0];
    $offOnu = (int)($countRow['off_onu'] ?? 0);
    $activeOnu = (int)($countRow['active_onu'] ?? 0);

    $stmtTempRead->execute([':file' => 'poweroffonu']);
    $currentData = (int)($stmtTempRead->fetchColumn() ?: 0);

    if ($currentData !== $offOnu) {
        $difference = abs($currentData - $offOnu);
        $differenceDefault = !empty($confPMon['CRITICAL_CHANGE_OFFLINE_ALL']) ? (int)$confPMon['CRITICAL_CHANGE_OFFLINE_ALL'] : 100;
        if ($difference > $differenceDefault) {
            $sender = '[icon-right][b]' . $lang['offef'] . ' ONU[/b] ' .
                $lang['count_last'] . ': ' . $currentData . ' ' .
                $lang['count_curent'] . ': ' . $offOnu . ' ' .
                $lang['count_curent_on'] . ': ' . $activeOnu;
            $stmtNotif->execute([
                ':message' => $sender,
                ':added' => $timer
            ]);
        }
        $stmtTempUpsert->execute([
            ':file' => 'poweroffonu',
            ':data' => $offOnu,
            ':updated_at' => $timer,
            ':last_processed' => $timer
        ]);
    }
}

$countLosThreshold = !empty($confPMon['MONITOR_ONU_LOS_COUNT']) ? (int)$confPMon['MONITOR_ONU_LOS_COUNT'] : 5;
$sqlLos = "
    SELECT COUNT(idonu) AS los_onu
    FROM onus
    WHERE offline >= CURDATE()
      AND status = '2'
      AND (reason = 'err8' OR reason = 'err6')
";
$losRow = $pdo->query($sqlLos)->fetch(PDO::FETCH_ASSOC) ?: ['los_onu' => 0];
$losOnu = (int)($losRow['los_onu'] ?? 0);

$stmtTempRead->execute([':file' => 'losonu']);
$currentLosData = (int)($stmtTempRead->fetchColumn() ?: 0);

if ($losOnu > 0 && $currentLosData !== $losOnu) {
    $difference = abs($losOnu - $currentLosData);
    if ($difference > $countLosThreshold) {
        $resultWord = '=';
        $icon = '';
        if ($losOnu > $currentLosData) {
            $resultWord = 'збільшилась до';
            $icon = 'up';
        } elseif ($losOnu < $currentLosData) {
            $resultWord = 'зменшилась до';
            $icon = 'down';
        }

        $senderLos = '[icon-alarm][b]ONU LOS[/b] ' .
            $lang['count_last'] . ': ' . $currentLosData . ' ' . $resultWord . ' [b]' . $losOnu . '[/b]';
        $stmtNotif->execute([
            ':message' => $senderLos,
            ':added' => $timer
        ]);

        $logText = 'ONU LOS ' . $lang['count_last'] . ': ' . $currentLosData . ' ' . $resultWord . ' ' . $losOnu;
        if (!empty($confPMon['PMON_LOG']) && (int)$confPMon['PMON_LOG'] === 1 && $logText !== '') {
            $pmonLog = ['types' => 'onu_los_' . $icon, 'status' => 'critical', 'message' => $logText];
            $sql = PMon($pmonLog, $timer);
            if ($sql) {
                $pdo->exec($sql);
            }
        }
    }
}

$stmtTempUpsert->execute([
    ':file' => 'losonu',
    ':data' => $losOnu,
    ':updated_at' => $timer,
    ':last_processed' => $timer
]);
?>
