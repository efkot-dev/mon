<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ENGINE_DIR.'functions/service.php';
require_once ENGINE_DIR . 'init.data.php';
if (isset($_POST['action']) && isset($_POST['module'])) {
    $module = $_POST['module'];
    $action = $_POST['action'];    
    if ($action === 'enable' || $action === 'disable') {
        if (array_key_exists($module,$GLOBAL_MODULE)) {
			if ($action == 'enable') {
				foreach ($GLOBAL_MODULE[$module]['module'] as $variable) {
					if ($variable !== null) {
						$sql = "INSERT INTO pmonini (name, value) VALUES ('{$variable}', '1')";
						$db->query($sql);
						if($variable == 'SWITCH_TEMPERATURE_MONITORING'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '61' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,61,'monitor_access_alarm',200,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}	
						}
						if($variable == 'SECURITY_PING3'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '54' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,54,'monitor_access_alarm',100,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}	
						}						
						if($variable == 'TEMPERATURE_MONITOR'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '64' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,64,'monitor_temp',180,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}	
						}							
						if($variable == 'NETWORK_TOPOLOGY'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '105' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,105,'monitor_topology',280,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}	
						}							
						if($variable == 'TRANSPORT_ONU'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '9' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,9,'monitor_onu_trasport',300,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}	
						}						
						if($variable == 'CHECK_SNMP'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '3' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,3,'monitor_access_snmp',300,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}	
						}						
						if($variable == 'IPCAM'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '62' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,62,'monitor_access_alarm',500,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}	
						}
						if($variable == 'ONU_MONITOR_SIGNAL'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '1' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,1,'monitor_signal_onu',60,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}							
						}
						if($variable == 'ABILLS_ISP_IMPORT'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '60' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,60,'import_users_abills',70,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}							
						}						
						if($variable == 'MIKBILL_ISP_IMPORT'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '18' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,18,'import_users_mikbill',70,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}							
						}						
						if($variable == 'ONU_ERROR'){
							#$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '65' LIMIT 1");
							#if(empty($checker_workid['id'])){
							#	$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,65,'onu_error',300,'running','work','manual','".date('Y-m-d H:i:s')."')");
							#}
							$switch = $db->SimpleWhile("SELECT id, place, oidid, inf, model FROM switch WHERE oidid IN (1, 14)");
							if (!empty($switch)) {
								$start_time = 300;
								$stmt = $pdo->prepare("INSERT INTO taskers	(deviceid, workid, type, `interval`, status, userid, added, pmon) VALUES (:deviceid, 65, 'monitor', :interval, 'pending', :userid, :added, 'work')");
								foreach ($switch as $res) {
									$stmt->execute([
										':deviceid' => (int)$res['id'],':interval' => $start_time,':userid' => (int)$USER['id'],':added' => date('Y-m-d H:i:s')
									]);
									$start_time += 30;
								}
							}
						}
						if($variable == 'BOARD_FAULT'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '47' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,47,'board_fault',120,'running','work','manual','".date('Y-m-d H:i:s')."')");
							}							
						}
						if($variable == 'GPS_TRACCAR' || $variable == 'GPS_TRACKER_COM_UA'){
							$checker_workid = $db->Simple("SELECT workid, id FROM taskers WHERE workid = '52' LIMIT 1");
							if(empty($checker_workid['id'])){
								$db->query("INSERT INTO `taskers` (`deviceid`, `workid`, `type`, `interval`, `status`, `pmon`, `type_scheduler`, `last_run_time`) VALUES (0,52,'gps_taccar',300,'pending','work','custom','".date('Y-m-d H:i:s')."')");
							}
						}
					}
				}
				$response['success'] = true;
			} else if ($action == 'disable') {
				foreach ($GLOBAL_MODULE[$module]['variables'] as $variable) {
					if ($variable !== null) {
						$sql = "DELETE FROM pmonini WHERE name = '{$variable}'";
						$db->query($sql);
						if($variable == 'GPS_TRACCAR' || $variable == 'GPS_TRACKER_COM_UA'){
							$db->SQLdelete('taskers',['workid' => 52]);
						}						
						if($variable == 'SWITCH_TEMPERATURE_MONITORING'){
							$db->SQLdelete('taskers',['workid' => 61]);
						}							
						if($variable == 'NETWORK_TOPOLOGY'){
							$db->SQLdelete('taskers',['workid' => 105]);
						}							
						if($variable == 'TEMPERATURE_MONITOR'){
							$db->SQLdelete('taskers',['workid' => 64]);
						}						
						if($variable == 'ONU_ERROR'){
							$db->SQLdelete('taskers',['workid' => 65]);
						}						
						if($variable == 'TRANSPORT_ONU'){
							$db->SQLdelete('taskers',['workid' => 9]);
						}
						if($variable == 'CHECK_SNMP'){
							$db->SQLdelete('taskers',['workid' => 3]);
							$db->query("UPDATE switch SET snmp_access = 'none'");
						}						
						if($variable == 'ONU_MONITOR_SIGNAL'){
							$db->SQLdelete('taskers',['workid' => 1]);
						}
						if($variable == 'ABILLS_ISP_IMPORT'){
							$db->SQLdelete('taskers',['workid' => 60]);
						}						
						if($variable == 'MIKBILL_ISP_IMPORT'){
							$db->SQLdelete('taskers',['workid' => 18]);
						}							
						if($variable == 'IPCAM'){
							$db->SQLdelete('taskers',['workid' => 62]);
						}						
						if($variable == 'BOARD_FAULT'){
							$db->SQLdelete('taskers',['workid' => 47]);
						}
					}
				}
				$response['success'] = true;
			}
			$cacheType = defined('CACHE') ? CACHE : 'file';
			if($cacheType=='file'){
				if (file_exists($cacheFilePath_1)) {
					@unlink($cacheFilePath_1);
				}
			}elseif($cacheType=='redis'){
				$cacheKey = 'config:' . CACHE_FILE_NAME;
				$redis->del($cacheKey);
			}
			header('Content-Type: application/json');
			echo json_encode($response);
			exit();
		}
    } else if ($action == 'onusupport') {
		if(isset($_POST['api_key'])){
			$api_key_ = Clean::str($_POST['api_key']);
		}		
		if(isset($_POST['pmon_api'])){
			$pmon_api_ = Clean::str($_POST['pmon_api']);
		}		
		if(isset($_POST['uid_isp'])){
			$uid_isp = Clean::int($_POST['uid_isp']);
		}
		if(empty($pmon_api_) || empty($api_key_) || empty($uid_isp)){
			die('not_support_services');
		}
		/*
		$api_key_get = (isset($confPMon['PMON_TECH_API_GET']) && !empty($confPMon['PMON_TECH_API_GET']) ? $confPMon['PMON_TECH_API_GET']:false);
		$api_key = (isset($confPMon['PMON_TECH_API']) && !empty($confPMon['PMON_TECH_API']) ? $confPMon['PMON_TECH_API']:false);
		$uid_key = (isset($confPMon['PMON_TECH_UID']) && !empty($confPMon['PMON_TECH_UID']) ? $confPMon['PMON_TECH_UID']:false);
		if(!empty($api_key_get)){
			$db->query("UPDATE pmonini SET value = '{$pmon_api_}' WHERE name = 'PMON_TECH_API_GET'");
		}else{
			$db->query("INSERT INTO pmonini (name, value) VALUES ('PMON_TECH_API_GET', '{$pmon_api_}')");
		}		
		if(!empty($api_key)){
			$db->query("UPDATE pmonini SET value = '{$api_key_}' WHERE name = 'PMON_TECH_API'");
		}else{
			$db->query("INSERT INTO pmonini (name, value) VALUES ('PMON_TECH_API', '{$api_key_}')");
		}		
		if(!empty($uid_key)){
			$db->query("UPDATE pmonini SET value = '{$uid_isp}' WHERE name = 'PMON_TECH_UID'");
		}else{
			$db->query("INSERT INTO pmonini (name, value) VALUES ('PMON_TECH_UID', '{$uid_isp}')");
		}
		*/
		$cacheKey = 'config:' . CACHE_FILE_NAME;
		$redis->del($cacheKey);
		$go->go('/?do=onusupport&act=setting');		
	}
}
die;
?>