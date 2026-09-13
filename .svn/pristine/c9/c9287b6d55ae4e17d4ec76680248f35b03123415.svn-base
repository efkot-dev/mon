<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$timer = date('Y-m-d H:i:s');
if(isset($olt) && $olt>0){
	$id_device = intval($olt);
}
if(isset($id_device) && $id_device>0){
	$switch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	if(empty($switch['id'])){
		die('not_support_or_first_run');
	}
}
if(!empty($switch['id'])){
	taskers_device_pmon($db, 47, $switch['id']);
	$sqlonu = $db->SimpleWhile("SELECT idonu, mac, keyonu, olt, inface FROM onus WHERE status = '1' AND olt = '{$switch['id']}'");
	$pauseInterval = 40;
	$counter = 0;
	$vlan_array = array();
	if(isset($sqlonu) && count($sqlonu) > 0){
		foreach ($sqlonu as $onu){
			$onu_vlan = @snmp2_get($switch['netip'], $switch['snmpro'], '1.3.6.1.4.1.3320.101.12.1.1.3.' . $onu['keyonu'].'.1', 100000, 5);
			if(isset($onu_vlan)){
				$vlan = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_vlan);
				$vlan_array[$onu['idonu']]['vlan'] = $vlan;
			}
			$counter++;
			if ($counter % $pauseInterval === 0) {
				sleep(rand(1,2));
			}
		}
		if(isset($vlan_array) && !empty($vlan_array)){
			foreach ($vlan_array as $onu_id => $value) {
				if(isset($value['vlan'])){
					$db->query("UPDATE onus SET wan = '{$value['vlan']}' WHERE idonu  = '{$onu_id}' AND olt = '{$id_device}'");
				}
			}
		}
	}	
	$logger->init(['log'=>'device','type'=>'monitor','descr'=>'Get VLAN ','deviceid'=>$id_device,'who'=>'cron']);
	
}
?>
