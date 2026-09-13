<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$starttime = microtime(true);
$statusreason = [
	8 => 'err8',               // wire-down
	3 => 'err30',              // mpcp-down
	4 => 'err31',              // oam-down
	5 => 'err32',              // firmware-download
	6 => 'err33',              // illegal-mac
	7 => 'err34',              // lid-admin-down
	9 => 'err1'                // DyingGasp - power-off
];
$timer = date('Y-m-d H:i:s');
if (isset($olt) && is_numeric($olt) && $olt>0){
	$id_device = $olt;
}
if(isset($id_device) && $id_device>0){
	$temp_reason_time = [];
	$temp_list_mac = [];
	$switch = $db->Simple("SELECT id,netip,snmpro,oidid FROM switch WHERE id = '{$id_device}' LIMIT 1");
	acces_device_pmon($switch,'netip,id,oidid,snmpro','1');
	$reason_epon = [
		'oid' => '1.3.6.1.4.1.3320.101.11.1.1.11','type' => 'real','deloid' => true,
		'ip' => $switch['netip'],'community'=> $switch['snmpro']
	];
	$array_reason = [];
	$indexonu = pmon_walk($reason_epon);
	if(isset($indexonu)) {
		foreach($indexonu as $pi1 => $type) {
			if(isset($pi1) && !empty($type['result'])){
				$reason = valueStringSnmp($type['result']);
				$mac = bdcom_epon_mac_ont($pi1);
				if(isset($reason) && isset($mac)){
					$array_reason[$mac]['reason'] = $statusreason[$reason] ?? 'err0';
				}
			}
		}
		if(isset($array_reason) && count($array_reason)>0){
			$getmac = $db->SimpleWhile("SELECT idonu, mac FROM onus WHERE olt = '".$id_device."'");
			$count_onu = 1;
			if(isset($getmac) && count($getmac)>0){
				foreach($getmac as $id => $onu) {
					if(isset($array_reason[$onu['mac']]['reason']) && $onu['idonu']>0){
						$db->query("UPDATE onus SET reason = '".$array_reason[$onu['mac']]['reason']."' WHERE idonu  = '".$onu['idonu']."'");	
						$count_onu++;							
					}
				}
			}
			$time_check = intval((int)microtime(true) - $starttime);
			$log_monitor = [
				'log'=>'device','type'=>'monitor',
				'descr'=>vsprintf($lang['monitor_reason_onu'],[$count_onu,$time_check]),
				'deviceid'=>$id_device,'who'=>'cron'
			];
			$logger->init($log_monitor);
		}
	}
}
?>
