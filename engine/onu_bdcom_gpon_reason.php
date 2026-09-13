<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$timer = date('Y-m-d H:i:s');
if (is_numeric($olt)) {
	$id_device = $olt;
}
if(isset($id_device) && $id_device>0){
	$switch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	if(empty($switch['id'])){
		die('not_support');
	}
	if(!empty($switch['monitor'])){
		$name_gpon = [
			'oid' => '1.3.6.1.4.1.3320.10.3.1.1.35',
			'type' => 'exec','deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
		];
		$array_reason = [];
		$indexonu = pmon_walk($name_gpon);
		if (preg_match('/Object/', $indexonu[0]['result'])) {
			
		}else{
			if(isset($indexonu) && count($indexonu)>0){
				foreach($indexonu as $pi1 => $type) {
					preg_match('/(\d+)\s*=\s*(.*?)\s*$/', $type['result'],$temp);
					$array_reason[$temp[1]]['reason'] = valueStringSnmp($temp[2]);
					$array_reason[$temp[1]]['keyonu'] = trim($temp[1]);
				}		
			}	
			if(isset($array_reason) && count($array_reason)>0){
				$getonu = $db->SimpleWhile("SELECT idonu,keyonu,reason FROM onus WHERE olt = '".$switch['id']."'");
				if(isset($getonu) && count($getonu)>0){
					foreach($getonu as $id => $onu) {
						if(isset($array_reason[$onu['keyonu']]['keyonu']) && $array_reason[$onu['keyonu']]['keyonu']==$onu['keyonu']){
							$reason = $array_reason[$onu['keyonu']]['keyonu'];
							$db->SQLupdate('onus',[
								'reason' => reason_bdcom_gpon($reason)
							],
							['idonu' => $onu['idonu']]);
						}
					}
				}
			}
		}
	}
}
?>
