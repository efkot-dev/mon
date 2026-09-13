<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.monitor.php';
$time = date('Y-m-d H:i:s');
function PortStatus(mixed $snmpValue, int $oidId): string {
    if (!isset($snmpValue) || $snmpValue === '') {
        return '';
    }
    if ($oidId === 14) {
        return (string)portstatusHuawei($snmpValue);
    }
    if ($oidId === 8) {
        if ((string)$snmpValue === '6' || (string)$snmpValue === '1') {
            return (string)statusMonitor(1);
        }
        return (string)statusMonitor(2);
    }
    return is_numeric($snmpValue) ? (string)statusMonitor((int)$snmpValue) : (string)$snmpValue;
}
if (empty($config['monitorapi'])) {
    exit("monitorapi_not_set\n");
}
$sqlPorts = "SELECT p.id AS portid,  p.deviceid,  p.llid,  p.nameport,  p.descrport,  p.sms, p.operstatus,  s.place, s.oidid FROM switch_port p INNER JOIN switch s ON s.id = p.deviceid WHERE p.monitor = 'yes' AND s.monitor = 'yes' AND p.llid IS NOT NULL";
$ports = $pdo->query($sqlPorts)->fetchAll(PDO::FETCH_ASSOC);
if (!$ports) {
    exit("not_support\n");
}
$updUp = $pdo->prepare("UPDATE switch_port SET timeup=:time, operstatus='up', updates=:time WHERE id=:id");
$updDown = $pdo->prepare("UPDATE switch_port SET timedown=:time, operstatus='down', updates=:time WHERE id=:id");
$insNotification = $pdo->prepare("INSERT INTO notification (status, type, system, message, added)  VALUES (1, 4, 'monitor', :message, :added)");
foreach ($ports as $port) {
    $deviceId = (int)$port['deviceid'];
    $portId = (int)$port['portid'];
    $llid = (int)$port['llid'];
    $oidId = (int)$port['oidid'];
    if ($deviceId <= 0 || $portId <= 0 || $llid <= 0) {
        continue;
    }
    $oid = '1.3.6.1.2.1.2.2.1.8.' . $llid;
    $apiResult = api__($config['monitorapi'], ['do' => 'oid','oid' => $oid,'id' => $deviceId]);
    $newStatus = PortStatus($apiResult['result'] ?? null, $oidId);
    $lastStatus = (string)($port['operstatus'] ?? 'none');
    if ($newStatus === '' || $newStatus === $lastStatus) {
        continue;
    }
    $text = '';
    if ($lastStatus === 'down' && $newStatus === 'up') {
        $updUp->execute([':time' => $time, ':id' => $portId]);
        $text = '[b]' . $lang['up_port'] . ':[/b] ' . $lang['port'] . ': [b]' . $port['nameport'] . '[/b] ' . (!empty($port['descrport']) ? '(' . $port['descrport'] . ')' : '') . ' - ' . $port['place'];
    } elseif ($lastStatus === 'up' && $newStatus === 'down') {
        $updDown->execute([':time' => $time, ':id' => $portId]);
        $text = '[b]' . $lang['down_port'] . ':[/b] ' . $lang['port'] . ': [b]' . $port['nameport'] . '[/b] ' . (!empty($port['descrport']) ? '(' . $port['descrport'] . ')' : '') . ' - ' . $port['place'];
    } elseif ($lastStatus === 'none' && $newStatus === 'up') {
        $updUp->execute([':time' => $time, ':id' => $portId]);
    } elseif ($lastStatus === 'none' && $newStatus === 'down') {
        $updDown->execute([':time' => $time, ':id' => $portId]);
    }
    if (($port['sms'] ?? '') === 'yes' && $text !== '') {
        $insNotification->execute([':message' => $text,':added' => $time]);
    }
}
?>
