<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('TASK', true);
require ROOT_DIR.'/inc/init.monitor.php';
$starttime = microtime(true);
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
		'oid' => '1.3.6.1.4.1.3320.101.11.1.1.10','type' => 'real',
		'deloid' => true,'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$tmp_reason_epon = pmon_walk($reason_epon);
	if(isset($tmp_reason_epon) && count($tmp_reason_epon)>0){
		foreach($tmp_reason_epon as $tmp_mac => $date) {
			$mac = bdcom_mac($tmp_mac);
			if(isset($mac) && !empty($date['result'])){
				$time_olt = clearDataMacRe($date['result']);
				$temp_reason_time[$mac]['offline'] = hexTOdate($time_olt);
			}
		}
		$getmac = $db->SimpleWhile("SELECT idonu, mac FROM onus WHERE olt = '".$switch['id']."'");
		if(isset($getmac) && count($getmac)>0){
			foreach($getmac as $idonu => $onu) {
				$temp_list_mac[$onu['mac']] = array('idonu' => $onu['idonu'], 'mac'=>$onu['mac']);
			}
		}
	}
	if(isset($temp_reason_time) && count($temp_reason_time)>0 && isset($temp_list_mac) && count($temp_list_mac)>0){
		$count_onu = 0;
		foreach($temp_reason_time as $mac => $type) {
			if(isset($temp_list_mac[$mac]['idonu']) && $temp_list_mac[$mac]['mac']==$mac){
				$db->query("UPDATE onus SET offline = '".$type['offline']."' WHERE idonu  = '".$temp_list_mac[$mac]['idonu']."'");
				$count_onu ++;
			}
		}
		$time_check = intval((int)microtime(true) - $starttime);
		$log_monitor = [
			'log'=>'device','type'=>'monitor',
			'descr'=>vsprintf($lang['monitor_time_reason_onu'],[$count_onu,$time_check]),
			'deviceid'=>$id_device,'who'=>'cron'
		];
		$logger->init($log_monitor);
	}
}
?>
