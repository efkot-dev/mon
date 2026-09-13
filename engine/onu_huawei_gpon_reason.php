<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$starttime = microtime(true);
if(isset($olt) && $olt>0){
	$id_device = intval($olt);
}
if(isset($id_device) && $id_device>0){	
$timer = date('Y-m-d H:i:s');
	$temp_reason_time = [];
	$temp_list_mac = [];
	$switch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$sqlonu = $db->SimpleWhile("SELECT zte_idport, idonu, type, keyonu FROM onus WHERE type = 'gpon' AND olt = ".$switch['id']."");
	$pauseInterval = 50;
	$counter = 0;
	if(isset($sqlonu) && count($sqlonu) > 0){
		$count_onu = 1;
		foreach ($sqlonu as $idonu => $onu){
			$get_reason_gpon = array(
				'id' => $switch['id'],'do' => 'oid',
				'oid'=> vsprintf('1.3.6.1.4.1.2011.6.128.1.1.2.46.1.24.%s.%s',[$onu['zte_idport'],$onu['keyonu']])
			);
			$result_reason = api__($config['monitorapi'],$get_reason_gpon);
			if(isset($result_reason['result']) && !empty($result_reason['result'])){
				$value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '',$result_reason['result']);
				$reason = reasonGponHuawei($value);
				if(isset($reason)){
					$db->query("UPDATE onus SET reason = '".$reason."' WHERE idonu = '{$onu['idonu']}'");
				}
			}
			$counter++;
			if ($counter % $pauseInterval === 0) {
				$currentLoad = sys_getloadavg()[0];
				sleep(max(1, min(4, intdiv($currentLoad, 2))));
			}
			$count_onu++;
		}
		$time_check = intval((int)microtime(true) - $starttime);
		$log_monitor = [
			'log'=>'device',
			'type'=>'monitor',
			'descr'=>vsprintf($lang['monitor_reason_onu'],[$count_onu,$time_check]),
			'deviceid'=>$id_device,
			'who'=>'cron'
			];
		$logger->init($log_monitor);
}
}
?>
