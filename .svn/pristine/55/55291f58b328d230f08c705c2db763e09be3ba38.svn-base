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
	function onu_cdata_reason_1216($value){
		if (strpos($value, 'lossi') !== false){
			return 'err8';
		}elseif (strpos($value, 'dying') !== false){
			return 'err1';
		}else{
			return 'err0';
		}
	}
	$switch = $db->Simple("SELECT netip, snmpro, id FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$reason_epon = [
		'oid' => '1.3.6.1.4.1.34592.1.3.100.12.3.1.1.7','type' => 'real','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$array = [];
	$indexonu = pmon_walk($reason_epon);
	if(isset($indexonu) && count($indexonu)>0) {
		foreach($indexonu as $pi1 => $type) {
			if(isset($pi1) && !empty($type['result'])){
				$keyonu = str_replace([' ', 'iso.3.6.1.4.1.34592.1.3.100.12.3.1.1.7.', '-', ':'], '', $pi1);
				$reason = valueStringSnmp($type['result']);					
				$reason = onu_cdata_reason_1216($reason);					
				$array[$keyonu]['reason'] = $reason;					
			}
		}
		if(isset($array) && count($array)>0){
			$getmac = $db->SimpleWhile("SELECT idonu, keyonu, reason FROM onus WHERE olt = '".$id_device."'");
			if(isset($getmac) && count($getmac)>0){
				foreach($getmac as $id => $onu) {
					$types = '';
					if(isset($array[$onu['keyonu']]['reason']) && $onu['idonu']>0){
						$reason =  $array[$onu['keyonu']]['reason'];
						$db->query("UPDATE onus SET reason = '{$reason}' WHERE idonu  = '".$onu['idonu']."'");								
						}
				}
			}
		}
		if(isset($array) && count($array)>0){
			$replacements = [
				'&onu' => count($array),'&time' => number_format(microtime(true) - $starttime, 2)
			];
			$status_onu_description = str_replace(array_keys($replacements), array_values($replacements),$lang['cron_reason_onu']);
			$logger->init([
				'log'=>'device',
				'type'=>'monitor','descr'=>$status_onu_description,
				'deviceid'=>$id_device,'who'=>'cron']
			);
		}
	}
}
?>
