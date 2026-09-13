<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$starttime = microtime(true);
$timer = date('Y-m-d H:i:s');
function zte_3reason($reason) {
	$reason  = trim($reason);
	$errorMap = [
		0 => 'err1',
		1 => 'err5',
		2 => 'err6',
		3 => 'err6',
		7 => 'err25',
		9 => 'err1',
		12 => 'err0',
	];
	return isset($errorMap[$reason]) ? $errorMap[$reason] : 'err0';
}
if (is_numeric($olt)) {
	$id_device = $olt;
}
if(isset($id_device) && $id_device>0){
	$switch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$status_gpon = [
		'oid' => '1.3.6.1.4.1.3902.1012.3.28.2.1.7','type' => 'class',
		'deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$temp_gpon = array();
	$tmp_gpon = pmon_walk($status_gpon);
	if(isset($tmp_gpon) && count($tmp_gpon)>1){
		foreach($tmp_gpon as $tmp_onu => $date) {
			preg_match('/(\d+).(\d+)/',$tmp_onu,$onu);
			$reason = clearDataMacRe($date['result']);
			if(isset($reason)){
				$temp_gpon[$onu[1]][$onu[2]]['reason'] = zte_3reason($reason);
			}
		}
	}
	$count_onu = 0;
	if(isset($temp_gpon) && count($temp_gpon)>1){
		$sqlonu_gpon = $db->SimpleWhile("SELECT zte_idport, idonu, type, keyonu, status FROM onus WHERE type = 'gpon' AND olt = ".$id_device."");	
		foreach($sqlonu_gpon as $gpon_onu => $gpon_temp) {
			$types = '';
			if(isset($temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['reason'])){
				$reason = $temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['reason'];	
				$sql = "UPDATE onus SET reason = '{$reason}' WHERE idonu  = '{$gpon_temp['idonu']}'";
				$db->query($sql);
				$count_onu ++;
			}
		}
		$time_check = intval((int)microtime(true) - $starttime);
				$log_monitor = [
				'log'=>'device',
				'type'=>'monitor',
				'descr'=>vsprintf($lang['monitor_reason_onu'],[$count_onu,$time_check]),
				'deviceid'=>$id_device,
				'who'=>'cron'
			];
		$logger->init($log_monitor);	
	}
}
?>
