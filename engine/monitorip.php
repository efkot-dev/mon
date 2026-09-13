<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
ini_set('display_startup_errors', 1); ini_set('display_errors', 1); error_reporting(E_ALL);
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/functions/pmon.php';
function monitor_ip_ping($ping){
	global $timer, $lang, $db, $confPMon;
	$mess = '';
    $update['updates'] = $timer;  
    $update['timer'] = number_format($ping['ttl'], 2, '.', '');  
    if ($ping['status'] == 2 && $ping['last_status']==1 ) {
        $mess = '[icon-stop][b]' . $ping['name'] . '[/b] '.$lang['monitor_ip_not_ping'].' - ' . $ping['ip'] . '';
        $update['status'] = 2;
        $update['offline'] = $timer;
		$_list = 'PING IP ' . $ping['name'] . ' '.$lang['monitor_ip_not_ping'].' ' . $ping['ip'] . '';
		if (isset($confPMon['PMON_LOG']) && !empty($confPMon['PMON_LOG']) && $confPMon['PMON_LOG'] == 1) {
			if($_list!=false){
				$pmon_log = ['status' => 'critical','types' => 'ping_ip_down' , 'message' => $_list];
				$sql = PMon($pmon_log,$timer);
				$db->query($sql);
			}
		}
    } elseif($ping['status'] == 1 && $ping['last_status'] == 2) {
        $mess = '[icon-okey][b]' . $ping['name'] . '[/b] '.$lang['monitor_ip_ping'].' - ' . $ping['ip'] . '';
        $update['status'] = 1;
        $update['online'] = $timer;
		$_list = 'PING IP ' . $ping['name'] . ' '.$lang['monitor_ip_ping'].' ' . $ping['ip'] . '';
		if (isset($confPMon['PMON_LOG']) && !empty($confPMon['PMON_LOG']) && $confPMon['PMON_LOG'] == 1) {
			if($_list!=false){
				$pmon_log = ['status' => 'success','types' => 'ping_ip_up' , 'message' => $_list];
				$sql = PMon($pmon_log,$timer);
				$db->query($sql);
			}
		}
    } elseif ($ping['status'] == 1 && $ping['last_status'] == 1) {
		$update['status'] = 1;
    } elseif ($ping['status']==2 && $ping['last_status'] == 2) {
        $update['status'] = 2;
    }  
	foreach ($update as $column => $value) {
		if(isset($value) && isset($column)){
			$setClause[] = "$column = '{$value}'";
		}
	}
	$setClause = implode(', ', $setClause);
	$db->query("UPDATE monitor_ip SET $setClause WHERE id = '{$ping['id']}'");
	$db->query("INSERT INTO monitor_ip_log (ip_id, time, status, added) VALUES ('{$ping['id']}','{$update['timer']}','{$update['status']}','{$timer}')");
	if(isset($mess) && !empty($mess)){
		return $mess;
	}
}
$monitor_ip = [];
$list_monitor_ip = [];
if (isset($confPMon['MONITOR_IP']) && !empty($confPMon['MONITOR_IP']) && $confPMon['MONITOR_IP'] == 1) {
	$sql_ip = $db->SimpleWhile("SELECT * FROM monitor_ip WHERE monitor = 'yes'");
	if (isset($sql_ip)){
        foreach($sql_ip as $ip) {
			$list_monitor_ip[] = array(
				'ip'=>$ip['ip'], 'id'=>$ip['id'], 'status'=>$ip['status'], 'id'=>$ip['id'], 'name'=>$ip['name']
			);
		}
	}
    if (isset($list_monitor_ip)) {
		foreach ($list_monitor_ip as $dataip) {
			$ip = $dataip['ip'];
			if (strpos($ip, ':') !== false) {
				list($ipAddress, $port) = explode(':', $ip);
			} else {
				$ipAddress = $ip;
			}
			$temp_ip = pingIPAddress($ipAddress);
			$monitor_ip[$dataip['id']] = [
				'status' => $temp_ip['status'], 
				'id' => $dataip['id'], 
				'ip' => $dataip['ip'], 
				'last_status' => $dataip['status'], 
				'name' => $dataip['name'], 
				'ttl' => $temp_ip['time']
			];
		}
	}
	$telega = array();
	if (isset($monitor_ip) && count($monitor_ip)>0) {
        foreach($monitor_ip as $rta) {
			$telega[$rta['ip']] = monitor_ip_ping($rta);
		}
	}
	if (isset($telega) && count($telega)>0) {
		foreach($telega as $ip => $value) {
			if(isset($value) && !empty($value)){
				$db->SQLinsert('notification', ['status' => 1, 'type' => 30, 'system' => 'monitorip', 'message' => $value, 'added' => $timer]);
			}
		}
	}
}
?>
