<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR . '/inc/init.monitor.php';
require ROOT_DIR . '/inc/functions/system.php';
$starttime = microtime(true);
if(isset($jobid) && $jobid==58){
	$sql = "SELECT switch_port.llid,switch_port.id,switch_port.nameport,switch_port.deviceid, switch.netip,switch.snmpro, switch.oidid FROM switch_port LEFT JOIN switch ON switch.id = switch_port.deviceid WHERE switch_port.signal = 'yes';";
	$list = $db->SimpleWhile($sql);
	if(isset($list) && count($list)>0){
		foreach($list as $id => $res){
			if($res['oidid']==37){
				$data = sfp_mikrotik($res);
			}
			if(isset($data) && !empty($data['id'])){
				$sql_insert = "INSERT INTO signal_sfp (rx,tx,sfpid,datetime) VALUES ('{$data['rx']}','{$data['tx']}','{$data['id']}',NOW())";
				$db->query($sql_insert);
			}
		}
	}
}
?>
