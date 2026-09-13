<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$starttime = microtime(true);
$timer = date('Y-m-d H:i:s');
if (is_numeric($olt)) {
	$id_device = $olt;
}
function zte_3reason($reason) {
	$reason  = trim($reason);
	$errorMap = [
		1 => 'err1',2 => 'err8'
	];
	return isset($errorMap[$reason]) ? $errorMap[$reason] : 'err0';
}
if(isset($id_device) && $id_device>0){
	$switch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$status_gpon = [
		'oid' => '1.3.6.1.4.1.3902.1015.1010.1.7.4.1.17','type' => 'class','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$temp_epon = array();
	$tmp_epon = pmon_walk($status_gpon);
	if(isset($tmp_epon) && count($tmp_epon)>1){
		foreach($tmp_epon as $tmp_onu => $date) {
			$status = clearDataMacRe($date['result']);
			if(isset($status)){
				$temp_epon[$tmp_onu]['status'] = ($status==3 ? 1 : 2);
				$temp_epon[$tmp_onu]['reason'] = zte_3reason($status);
			}
		}
	}
	$count_onu = 0;
	if(isset($temp_epon) && count($temp_epon)>1){
		$sqlonu_gpon = $db->SimpleWhile("SELECT zte_idport, idonu, type, keyonu, status FROM onus WHERE type = 'epon' AND olt = ".$id_device."");	
			foreach($sqlonu_gpon as $gpon_onu => $gpon_temp) {
			$types = '';
			if(isset($temp_epon[$gpon_temp['keyonu']]['status'])){
				$status = $temp_epon[$gpon_temp['keyonu']]['status'];	
				$reason = $temp_epon[$gpon_temp['keyonu']]['reason'];	
				if($gpon_temp['status']==2 && $status==1){
					$types = ", online = '{$timer}'";
				}elseif($gpon_temp['status']==1 && $status==2){
					$types = ", offline = '{$timer}'";
				}
				$sql = "UPDATE onus SET status = '{$status}', reason = '{$reason}' {$types} WHERE idonu  = '{$gpon_temp['idonu']}'";
				$db->query($sql);
				$count_onu ++;
			}
		}
		$time_check = intval((int)microtime(true) - $starttime);
		$log_monitor = [
			'log'=>'device',
			'type'=>'monitor',
			'descr'=>vsprintf($lang['monitor_status_onu'],[$count_onu,$time_check]),
			'deviceid'=>$id_device,
			'who'=>'cron'
		];
		$logger->init($log_monitor);	
	}
}
?>
