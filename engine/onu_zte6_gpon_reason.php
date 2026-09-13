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
		'oid' => '1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.7','type' => 'class','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$reasons = [
		'1' => 'err5',
		'2' => 'err6',
		'3' => 'err6',
		'4' => 'err37',
		'5' => 'err16',
		'6' => 'err39',
		'7' => 'err40',
		'8' => 'err22',
		'9' => 'err1',
		'10' => 'err55',
		'11' => 'err56',
		'12' => 'err12',
		'13' => 'err0',
		'14' => 'err57',
		'15' => 'err58',
	];
	$temp_gpon = array();
	$tmp_gpon = pmon_walk($status_gpon);
	if(isset($tmp_gpon) && count($tmp_gpon)>1){
		foreach($tmp_gpon as $tmp_onu => $date) {
			preg_match('/(\d+).(\d+)/',$tmp_onu,$onu);
			$reason = clearDataMacRe($date['result']);
			if(isset($reason) && $reason>0){
				$temp_gpon[$onu[1]][$onu[2]]['reason'] = $reasons[$reason] ?? 'err0';
			}
		}
	}
	$count = 0;
	if(isset($temp_gpon) && count($temp_gpon)>1){
		$sqlonu_gpon = $db->SimpleWhile("SELECT zte_idport, idonu, type, keyonu, status FROM onus WHERE type = 'gpon' AND olt = ".$id_device."");	
		foreach($sqlonu_gpon as $gpon_onu => $gpon_temp) {
			if(isset($temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['reason'])){
				$reason = $temp_gpon[$gpon_temp['zte_idport']][$gpon_temp['keyonu']]['reason'];	
				$db->query("UPDATE onus SET reason = '".$reason."' WHERE idonu  = '".$gpon_temp['idonu']."'");
			}
		}
		$logger->init(['log'=>'device','type'=>'monitor','descr'=>'Monitoring ONT reason','deviceid'=>$id_device,'who'=>'cron']);	
	}
}
?>
