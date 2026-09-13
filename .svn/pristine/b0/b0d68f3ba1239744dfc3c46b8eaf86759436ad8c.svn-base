<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function getNewPDO(): PDO {
    $dsn = "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8";
    return new PDO($dsn, DBUSER, DBPASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}
function get_snmp_uptime(array $switch): ?string {
    try {
        $session = new SNMP(SNMP::VERSION_2c, $switch['netip'], $switch['snmpro'], 500000, 2);
        $snmp_uptime = @$session->get("1.3.6.1.2.1.1.3.0");
        $session->close();
        return $snmp_uptime !== false ? $snmp_uptime : null;
    } catch (SNMPException $e) {
        error_log('SNMP Exception: ' . $e->getMessage());
        return null;
    }
}
function get_snmp_status(array $switch): ?string {
    try {
        $session = new SNMP(SNMP::VERSION_2c, $switch['netip'], $switch['snmpro'], 500000, 2);
        $snmp_uptime = @$session->get("1.3.6.1.2.1.1.3.0");
        $session->close();
        return $snmp_uptime !== false ? $snmp_uptime : null;
    } catch (SNMPException $e) {
        return null;
    }
}
function switch_uptime(string $value): string {
    if (strpos($value, 'Timeticks') !== false) {
        preg_match('/\((.*?)\)/', $value, $matches);
        if (isset($matches[1])) return formatUptime($matches[1]);
    } elseif (preg_match('/^\d+$/', $value)) {
        return formatUptime($value);
    } elseif (preg_match('/^\(\d+\)/', $value, $matches)) {
        $value = preg_replace('/[^\d]/', '', $matches[0]);
        return formatUptime($value);
    } else {
        preg_match('/\((.*?)\)/', $value, $matches);
        if (isset($matches[1])) return formatUptime($matches[1]);
    }
    return '';
}
function formatUptime($ticks): string {
    global $lang;
    $total_seconds = $ticks / 100;
    $days = floor($total_seconds / 86400);
    $hours = floor(($total_seconds % 86400) / 3600);
    $minutes = floor(($total_seconds % 3600) / 60);
    return "$days " . $lang['day'] . " $hours " . $lang['god'] . " $minutes " . $lang['min'];
}
function save_snmp_uptime(string $snmp_uptime, array $switch, PDO $pdo, array $config): void {
	$temp_message = '';
	$value = strtolower(str_replace(['INTEGER:', ' ', '"'], '', trim($snmp_uptime)));
    $valuetime = switch_uptime($value);
	$snmp_access = 'none';
    if (!empty($valuetime) && $valuetime!=false) {
        $snmp_access = 'up';
    }else{
		$snmp_access = 'down';
	}
	$stmt = $pdo->prepare("UPDATE switch SET uptime = :uptime, snmp_access = :snmp_access WHERE id = :id");
	$stmt->execute([':snmp_access' => $snmp_access,':uptime' => $valuetime,':id' => $switch['id']]);
	if($switch['snmp_access']=='none' && $snmp_access=='up'){
		$temp_message = "[icon-super] SNMP UP on {$switch['place']} ({$switch['netip']})\n";
		log_device_snmp('monitor_snmp_up', 'SNMP Up', $switch, $pdo);
	}elseif($switch['snmp_access']=='down' && $snmp_access=='up'){
		$temp_message = "[icon-super] SNMP UP on {$switch['place']} ({$switch['netip']})\n";
		log_device_snmp('monitor_snmp_up', 'SNMP Up', $switch, $pdo);
	}elseif($switch['snmp_access']=='up' && $snmp_access=='down'){
		$temp_message = "[icon-los-pon] SNMP DOWM on {$switch['place']} ({$switch['netip']})\n";
		log_device_snmp('monitor_snmp_down', 'SNMP Down', $switch, $pdo);
	}
	if(!empty($temp_message) && $temp_message!=false){
		snmp_send_telegram($temp_message, $switch, $pdo, $config);
	}
}
function snmp_send_telegram(string $data, array $switch, PDO $pdo, array $config){
	$stmt = $pdo->prepare("INSERT INTO notification (status, type, system, message, added) VALUES (:status, :type, :system, :message, :added)");
    $stmt->execute([':status' => 1, ':type' => 53,':system' => 'temperatura',':message' => $data, ':added' => date('Y-m-d H:i:s')]);	
}
function log_device_snmp(string $status, string $message, array $switch, PDO $pdo){
	$stmt = $pdo->prepare("INSERT INTO devicelogs (deviceid, type, descr, who, added) VALUES (:deviceid, :type, :descr, :who, :added)");
    $stmt->execute([':deviceid' => $switch['id'], ':type' => $status,':descr' => $message,':who' => 'cron', ':added' => date('Y-m-d H:i:s')]);	
}
function check_snmp_uptime(array $switch, PDO $pdo, array $config): bool {
    $attempt = function() use ($switch) {
        return get_snmp_uptime($switch);
    };
    if ($snmp_uptime = $attempt()) {
        save_snmp_uptime($snmp_uptime, $switch, $pdo, $config);
        return true;
    }
    sleep(2);
    if ($snmp_uptime = $attempt()) {
        save_snmp_uptime($snmp_uptime, $switch, $pdo, $config);
        return true;
    }
	if($switch['snmp_access']=='up' || $switch['snmp_access']=='up'){
		$stmt = $pdo->prepare("UPDATE switch SET snmp_access = :snmp_access WHERE id = :id");
		$stmt->execute([':snmp_access' => 'down',':id' => $switch['id']]);
		$temp_message = "[icon-los-pon] SNMP DOWM on {$switch['place']} ({$switch['netip']})\n";
		if(!empty($temp_message) && $temp_message!=false){
			log_device_snmp('monitor_snmp_down', 'SNMP Down', $switch, $pdo);
			snmp_send_telegram($temp_message, $switch, $pdo, $config);
		}
		
	}
    return false;
}
?>