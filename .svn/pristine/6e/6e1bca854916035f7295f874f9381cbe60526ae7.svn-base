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
		'oid' => '1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.2','type' => 'class','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$temp_gpon = array();
	$tmp_gpon = pmon_walk($status_gpon);
	if(isset($tmp_gpon) && count($tmp_gpon)>1){
		foreach($tmp_gpon as $tmp_onu => $date) {
			preg_match('/(\d+).(\d+)/',$tmp_onu,$onu);
			$status = clearDataMacRe($date['result']);
			if(isset($status)){
				$temp_gpon[$onu[1]][$onu[2]]['status'] = ($status==1 ? 1 : 2);
			}
		}
	}
	$count = 0;
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
				$count ++;
			}
		}
		$logger->init(['log'=>'device','type'=>'monitor','descr'=>'Monitoring ONT Status ['.$count.']','deviceid'=>$id_device,'who'=>'cron']);	
	}
}
?>
