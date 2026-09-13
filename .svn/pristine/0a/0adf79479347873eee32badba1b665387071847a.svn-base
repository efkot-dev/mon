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
	$status_gpon = [
		'oid' => ' 1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.6','type' => 'class','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$temp_gpon = array();
	$tmp_gpon = pmon_walk($status_gpon);
	if(isset($tmp_gpon) && count($tmp_gpon)>1){
		foreach($tmp_gpon as $tmp_onu => $date) {
			preg_match('/(\d+).(\d+)/',$tmp_onu,$onu);
			$time_onu = clearDataMacRe($date['result']);
			$temp_gpon[$onu[1]][$onu[2]]['offline'] = hexTOdate($time_onu);
		}
	}
	$count = 0;
	if(isset($temp_gpon) && count($temp_gpon)>1){
		$sqlonu_gpon = $db->SimpleWhile("SELECT zte_idport, idonu, type, keyonu, status FROM onus WHERE type = 'gpon' AND olt = ".$id_device."");	
		foreach($sqlonu_gpon as $gpon_onu => $gpon_temp) {
			if(isset($temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['offline'])){
				$offline = $temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['offline'];	
					$db->query("UPDATE onus SET offline = '".$offline."' WHERE idonu  = '".$gpon_temp['idonu']."'");
			}
		}
		$logger->init(['log'=>'device','type'=>'monitor','descr'=>'Monitoring ONT time reason','deviceid'=>$id_device,'who'=>'cron']);	
	}
}
?>
