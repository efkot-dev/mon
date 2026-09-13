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
if(isset($id_device) && $id_device>0){
	$switch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$status_gpon = [
		'oid' => '1.3.6.1.4.1.3902.1012.3.28.2.1.4','type' => 'class','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$temp_gpon = array();
	$tmp_gpon = pmon_walk($status_gpon);
	if(isset($tmp_gpon) && count($tmp_gpon)>1){
		foreach($tmp_gpon as $tmp_onu => $date) {
			preg_match('/(\d+).(\d+)/',$tmp_onu,$onu);
			$status = clearDataMacRe($date['result']);
			if(isset($status)){
				$temp_gpon[$onu[1]][$onu[2]]['status'] = ($status==3 ? 1 : 2);
			}
		}
	}
	$count_onu = 0;
	if(isset($temp_gpon) && count($temp_gpon)>1){
		$sqlonu_gpon = $db->SimpleWhile("SELECT zte_idport, idonu, type, keyonu, status FROM onus WHERE type = 'gpon' AND olt = ".$id_device."");	
			foreach($sqlonu_gpon as $gpon_onu => $gpon_temp) {
			$types = '';
			if(isset($temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['status'])){
				$status = $temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['status'];	
				if($gpon_temp['status']==2 && $status==1){
					$types = ", online = '{$timer}'";
				}elseif($gpon_temp['status']==1 && $status==2){
					$types = ", offline = '{$timer}'";
				}
				$sql = "UPDATE onus SET status = '{$status}' {$types} WHERE idonu  = '{$gpon_temp['idonu']}'";
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
