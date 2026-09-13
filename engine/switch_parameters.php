<?php
if (!defined('PONMONITOR')){
    die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$today = date('Y-m-d');
define('CRITICAL_TEMP', 60);
if (isset($confPMon['SWITCH_TEMPERATURE_MONITORING']) && !empty($confPMon['SWITCH_TEMPERATURE_MONITORING']) && $confPMon['SWITCH_TEMPERATURE_MONITORING']==1) {
$sql = "SELECT DISTINCT switch.id, switch.netip, switch.snmpro, switch.temp_cpu, switch.place, switch.oidid, oid.types, oid.oid
FROM switch
INNER JOIN oid ON switch.oidid = oid.oidid
WHERE switch.monitor = 'yes'
  AND oid.inf = 'health'
  AND oid.types = 'temp'";
$stmt = $pdo->query($sql);
$switches = $stmt->fetchAll(PDO::FETCH_ASSOC);
$temp_message = [];
foreach ($switches as $pon) {		
	if (!is_array($pon) || !isset($pon['oid'], $pon['id'])) {
        continue;
    }
    $res_snmp = api__($config['monitorapi'], [
        'do' => 'oid','oid' => $pon['oid'],'id'  => $pon['id']
    ]);
    if (empty($res_snmp['result']) || $res_snmp['result'] === false) {
        continue;
    }
    $temperatura = (float)$res_snmp['result'];
	if ($temperatura > 100) {
		$temperatura /= 10;
	}
    $filePath = 'temp_olt_' . $pon['id'];
    $stmtSelect = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file LIMIT 1");
    $stmtSelect->execute(['file' => $filePath]);
    $currentRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);    
	if ($currentRow) {
        $data = json_decode($currentRow['data'], true);
    } else {
        $data = ['last' => 0,'prev' => 0,'status' => 'normal','history' => []];
    }
    $prevStatus = $data['status'];
	$config_switch_cratical_temp = ($pon['temp_cpu'] ?? CRITICAL_TEMP);
    $newStatus  = ($temperatura >= $config_switch_cratical_temp) ? 'critical' : 'normal';
    $data['history'] = array_filter($data['history'], function($entry) use ($today) {
        return strpos($entry['time'], $today) === 0;
    });
    $data['history'][] = ['time'  => $time,'value' => $temperatura];
    $data['prev']   = $data['last'];
    $data['last']   = $temperatura;
    $data['status'] = $newStatus;
    if ($newStatus !== $prevStatus) {
        if ($newStatus === 'critical') {
            $temp_message[] = "[icon-fire] CRITICAL TEMP RAISED on {$pon['place']} ({$pon['netip']}): [b]{$temperatura}[/b]°C";
        } else {
            $temp_message[] = "[icon-ice] TEMP NORMALIZED on {$pon['place']} ({$pon['netip']}): [b]{$temperatura}[/b]°C\n";
        }
    } elseif ($newStatus === 'critical') {
        if ($temperatura > $data['prev']) {
            $temp_message[] = "[icon-temp] TEMP INCREASED on {$pon['place']} ({$pon['netip']}): [b]{$temperatura}[/b]°C\n";
        } elseif ($temperatura < $data['prev']) {
            $temp_message[] = "[icon-non_fire] TEMP DECREASED on {$pon['place']} ({$pon['netip']}): [b]{$temperatura}[/b]°C\n";
        }
    }
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmtInsert = $pdo->prepare("INSERT INTO tempdate (file, data, updated_at, last_processed) VALUES (:file, :data, :updated_at, :last_processed) ON DUPLICATE KEY UPDATE data = :data_update, updated_at = :updated_at_update, last_processed = :last_processed_update");
    $stmtInsert->execute([
        'file' => $filePath,'data' => $jsonData,'updated_at' => $time,'last_processed' => $time,'data_update' => $jsonData,'updated_at_update' => $time,'last_processed_update' => $time
    ]);
}
if (isset($temp_message) && count($temp_message) > 0) {
    $stmt = $pdo->prepare("INSERT INTO notification (status, type, system, message, added) VALUES (:status, :type, :system, :message, :added)");
    foreach ($temp_message as $id => $data) {
        $stmt->execute([
            ':status' => 1,':type' => 6,':system' => 'temperatura',':message' => $data,':added' => $time
        ]);
    }
}
}
?>
