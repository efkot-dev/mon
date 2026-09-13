<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$time = date('Y-m-d H:i:s');
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	$worker = filter_input(INPUT_POST, 'result', FILTER_SANITIZE_NUMBER_INT);
	$olt = filter_input(INPUT_POST, 'olt', FILTER_SANITIZE_NUMBER_INT);
	$jobid = filter_input(INPUT_POST, 'jobid', FILTER_SANITIZE_NUMBER_INT);  
	if(!is_null($olt) && !is_null($jobid)){
		$olt = intval($olt);
		$jobid = intval($jobid);  		
	}
}
if(!$olt && !$jobid) {
	$options = getopt("s:j:", ["switch:", "jobid:"]);
	$olt = $options["s"] ?? $options["switch"] ?? null;
	$jobid = $options["j"] ?? $options["jobid"] ?? null;
	if(empty($olt) && empty($jobid)) {
		die('correct_system_cron');
	}
}
if(isset($olt)){
	$timeout = 100000;
	$retries = 5;
	$sqlswitch = $db->Fast('switch', 'id, oidid, netip, snmpro', ['id' => $olt]);
	$portarray = $db->Multi('switch_port', 'id, llid', ['deviceid' => $sqlswitch['id']]);
	if (!empty($portarray)) {
		foreach ($portarray as $port) {
			$llidport = $port['llid'];
			$snmp_status = @snmp2_get($sqlswitch['netip'], $sqlswitch['snmpro'], "1.3.6.1.2.1.2.2.1.8.$llidport", $timeout, $retries);
			$statusport = strtolower(trim(preg_replace('/^(integer:|["\s]+)/i', '', $snmp_status)));
			switch ($sqlswitch['oidid']) {
				case 14:
					$status = portstatusHuawei($statusport);
					break;
				case 8:
					$statusMap = [
						'6' => 1, // Включено
						'1' => 1, // Адміністративно включено
						'2' => 2  // Вимкнено
					];
					$status = $statusMap[$statusport] ?? 2; // Статус за замовчуванням
					break;
				default:
					$status = !empty($statusport) ? $statusport : 2;
			}
			if($sqlswitch['oidid']!=14){
				$status = statusMonitor($status);
			}
			if(isset($status) && $status!=false){
				$db->query("UPDATE `switch_port` SET updates = '".$time."', operstatus = '".$status."' WHERE id = '".$port['id']."'");
			}
		}
	}
	if(isset($worker) && $worker>0){
		echo'ok';
	}
}
?>
