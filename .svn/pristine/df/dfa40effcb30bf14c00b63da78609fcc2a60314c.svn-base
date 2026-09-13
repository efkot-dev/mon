<?php
if (!defined('PONMONITOR')){
    die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$today = date('Y-m-d');
define('CRITICAL_TEMP', 65);
if (isset($confPMon['TEMPERATURE_MONITOR']) && !empty($confPMon['TEMPERATURE_MONITOR']) && $confPMon['TEMPERATURE_MONITOR']==1) {
$stmt = $pdo->query("SELECT * FROM monitor_temp");
$list_device = $stmt->fetchAll(PDO::FETCH_ASSOC);
$temp_message = [];
foreach ($list_device as $dev) {		
    $res_snmp = api__($config['monitorapi'], [
        'do' => 'snmpget','oid' => $dev['oidtemp'],'snmpro' => $dev['snmpro'],'netip'  => $dev['netip']
    ]);
    if (empty($res_snmp['result']) || $res_snmp['result'] === false) {
        continue;
    }
	$formula = $dev['formula'] ?? '';
	if (!empty($formula)) {
		$computed = safe_math_formula($formula,$res_snmp['result']);
		if ($computed !== null) {
			$temperatura = (float)$computed;
		}
	}else{
		$temperatura = (float)$res_snmp['result'];
	} 
	if ($temperatura > 101) {
		$temperatura /= 10;
	}
	$temperatura = intval($temperatura);
    $filePath = 'temp_device_' . $dev['id'];
    $stmtSelect = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file LIMIT 1");
    $stmtSelect->execute(['file' => $filePath]);
    $currentRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);    
	if ($currentRow) {
        $data = json_decode($currentRow['data'], true);
    } else {
        $data = ['last' => 0,'prev' => 0,'status' => 'normal','history' => []];
    }
    $prevStatus = $data['status'];
	$config_switch_cratical_temp = ($dev['critical_temp'] ?? CRITICAL_TEMP);
    $newStatus  = ($temperatura >= $config_switch_cratical_temp) ? 'critical' : 'normal';
    if(!empty($data['history'])){
		$data['history'] = array_filter($data['history'], function($entry) use ($today) {
			return strpos($entry['time'], $today) === 0;
		});
	}
    $data['history'][] = ['time'  => $time,'value' => $temperatura];
    $data['prev']   = $data['last'];
    $data['last']   = $temperatura;
    $data['status'] = $newStatus;
	if (!empty($dev['sendtelegram']) && $dev['sendtelegram']=='yes'){
		if ($newStatus !== $prevStatus) {
			if ($newStatus === 'critical') {
				$temp_message[] = "[icon-fire] CRITICAL TEMP RAISED on {$dev['name']}: [b]{$temperatura}[/b]°C";
			} else {
				$temp_message[] = "[icon-ice] TEMP NORMALIZED on {$dev['name']}: [b]{$temperatura}[/b]°C\n";
			}
		} elseif ($newStatus === 'critical') {
			if ($temperatura > $data['prev']) {
				$temp_message[] = "[icon-temp] TEMP INCREASED on {$dev['name']}: [b]{$temperatura}[/b]°C\n";
			} elseif ($temperatura < $data['prev']) {
				$temp_message[] = "[icon-non_fire] TEMP DECREASED on {$dev['name']}: [b]{$temperatura}[/b]°C\n";
			}
		}
	}
	$data_update = [':id' => $dev['id'],':temp' => $temperatura];
	$stmt_update = $pdo->prepare("UPDATE monitor_temp SET temp = :temp WHERE id = :id");
    $stmt_update->execute($data_update);
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
