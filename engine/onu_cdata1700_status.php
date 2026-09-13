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
	$switch = $db->Simple("SELECT netip, snmpro, id, oidid FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$getmac = $db->SimpleWhile("SELECT idonu, keyonu, status FROM onus WHERE olt = '{$id_device}'");
	if($switch['oidid']!=41){
		die('not_support_device');
	}
	$array_data_tmp = [
		'oid' => '1.3.6.1.4.1.17409.2.8.4.1.1.7','type' => 'real','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];	
	$array_data_tmp_time = [
		'oid' => '1.3.6.1.4.1.17409.2.8.4.1.1.102','type' => 'real','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$array = [];
	$array_time = [];
	$tmp = pmon_walk($array_data_tmp);
	if(isset($tmp) && count($tmp)>0) {
		foreach($tmp as $pi1 => $type) {
			if(isset($pi1) && !empty($type['result'])){
				$keyonu = str_replace([' ', 'iso.3.6.1.4.1.17409.2.8.4.1.1.7.', '-', ':'], '', $pi1);
				$status = valueStringSnmp($type['result']);					
				$array[$keyonu]['status'] = ($status==1 ? 1 : 2);					
			}
		}	
	}
	sleep(1);
	$tmp_time = pmon_walk($array_data_tmp_time);
	if(isset($tmp_time) && count($tmp_time)>0) {
		foreach($tmp_time as $pi2 => $data) {
			if(isset($pi2) && !empty($data['result'])){
				$keyonu = str_replace([' ', 'iso.3.6.1.4.1.17409.2.8.4.1.1.102.', '-', ':'], '', $pi2);
				$time = str_replace(['STRING:', '"',], '', $data['result']);					
				$array_time[$keyonu]['time'] = trim($time);					
			}
		}
		if(isset($getmac) && count($getmac)>0){
			foreach($getmac as $id => $onu) {
				if(isset($array[$onu['keyonu']]['status']) && $onu['idonu']>0){
					$types = '';
					if(isset($array[$onu['keyonu']]['status']) && $onu['idonu']>0){
						$timer_s = $array_time[$onu['keyonu']]['time'] ?? $timer;
						$status =  $array[$onu['keyonu']]['status'];
						if($onu['status']==2 && $status==1){
							$types = ", online = '{$timer_s}'";
						}elseif($onu['status']==2 && $status==2){
							$types = ", offline = '{$timer_s}'";
						}elseif($onu['status']==1 && $status==1){
							$types = ", online = '{$timer_s}'";
						}elseif($onu['status']==1 && $status==2){
							$types = ", offline = '{$timer_s}'";
						}
						$db->query("UPDATE onus SET status = '{$status}' {$types} WHERE idonu  = '".$onu['idonu']."'");								
					}
				}					
			}
		}
	}
}
?>
