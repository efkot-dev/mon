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
	$temp_reason_time = [];
	$temp_list_mac = [];
	$switch = $db->Simple("SELECT netip, snmpro, id FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$reason_epon = [
		'oid' => '1.3.6.1.4.1.17409.2.3.4.1.1.8','type' => 'real','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$array = [];
	$indexonu = pmon_walk($reason_epon);
	if(isset($indexonu) && count($indexonu)>0) {
		foreach($indexonu as $pi1 => $type) {
			if(isset($pi1) && !empty($type['result'])){
				$keyonu = str_replace([' ', 'iso.3.6.1.4.1.17409.2.3.4.1.1.8.', '-', ':'], '', $pi1);
				$status = valueStringSnmp($type['result']);					
				$array[$keyonu]['status'] = ($status==1 ? 1 : 2);					
			}
		}
		if(isset($array) && count($array)>0){
			$getmac = $db->SimpleWhile("SELECT idonu, keyonu, status FROM onus WHERE olt = '".$id_device."'");
			if(isset($getmac) && count($getmac)>0){
				foreach($getmac as $id => $onu) {
					$types = '';
					if(isset($array[$onu['keyonu']]['status']) && $onu['idonu']>0){
						$status =  $array[$onu['keyonu']]['status'];
						if($onu['status']==2 && $status==1){
							$types = ", online = '{$timer}'";
						}elseif($onu['status']==1 && $status==2){
							$types = ", offline = '{$timer}'";
						}
						$db->query("UPDATE onus SET status = '{$status}' {$types} WHERE idonu  = '".$onu['idonu']."'");								
					}
				}
			}
		}
	}
	if(isset($array) && count($array)>0){
		$replacements = [
			'&onu' => count($array),'&time' => number_format(microtime(true) - $starttime, 2)
		];
		$status_onu_description = str_replace(array_keys($replacements), array_values($replacements),$lang['cron_status_onu']);
		$logger->init([
			'log'=>'device',
			'type'=>'monitor','descr'=>$status_onu_description,
			'deviceid'=>$id_device,'who'=>'cron']
		);
	}
}
?>
