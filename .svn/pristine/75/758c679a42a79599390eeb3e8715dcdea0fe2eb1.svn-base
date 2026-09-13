<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
ini_set('display_startup_errors', 1); ini_set('display_errors', 1); error_reporting(E_ALL);
require ROOT_DIR.'/inc/init.monitor.php';
$starttime = microtime(true);
function status_bdcom($status) {
	$statuses = ["0" => 1,"1" => 1,"2" => 2,"3" => 1,"4" => 2];		
	return isset($statuses[$status]) ? $statuses[$status] : 2;
}
$timer = date('Y-m-d H:i:s');	
if (is_numeric($olt)) {
	$id_device = $olt;
}
if(isset($id_device) && $id_device>0){
	$temp_reason_time = [];
	$temp_list_mac = [];
	$switch = $db->Simple("SELECT id,netip,snmpro,oidid FROM switch WHERE id = '{$id_device}' LIMIT 1");
	acces_device_pmon($switch,'netip,id,oidid,snmpro','1');
	$reason_epon = [
		'oid' => '1.3.6.1.2.1.2.2.1.8','type' => 'real','deloid' => true,
		'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$array = [];
	$indexonu = pmon_walk($reason_epon);
	if(isset($indexonu) && count($indexonu)>0) {
		foreach($indexonu as $pi1 => $type) {
			if(isset($pi1) && !empty($type['result'])){
				$keyonu = str_replace([' ', 'iso.3.6.1.2.1.2.2.1.8.', '-', ':'], '', $pi1);
				$status = valueStringSnmp($type['result']);					
				$array[$keyonu]['status'] = status_bdcom($status);					
			}
		}
		if(isset($array) && count($array)>0){
			$count_onu = 1;
			$getmac = $db->SimpleWhile("SELECT idonu, keyonu, status FROM onus WHERE olt = '".$id_device."'");
			if(isset($getmac) && count($getmac)>0){
				foreach ($getmac as $id => $onu) {
					if (!isset($array[$onu['keyonu']]['status']) || $onu['idonu'] <= 0) {
						continue;
					}
					$status = $array[$onu['keyonu']]['status'];
					$types = '';
					if ($onu['status'] == 2 && $status == 1) {
						$types = ", online = '{$timer}'";
						$change = 1;
					} elseif ($onu['status'] == 1 && $status == 2) {
						$types = ", offline = '{$timer}'";
						$change = 2;
					}
					if ($types !== '') {
						$db->query("UPDATE onus SET status = '{$status}' {$types} WHERE idonu = '{$onu['idonu']}'");
					}
					$count_onu++;
					if(isset($change) && $change>0){
						$logont = [
							'log' => 'ont','type' => 'status','status' =>$change,'idonu' => $onu['idonu'],'olt' => $id_device,'time' => $timer,
							'message' => ($change==1 ? $lang['device_online'] : $lang['device_offline'])
						];
						$logger->init($logont);
					}						
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
}
?>
