<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('QUEUE',true);
require ENGINE_DIR.'functions/sql_pdo.php';
require ENGINE_DIR.'functions/vlan.php';
require ENGINE_DIR.'classes/telnet.class.php';
require ENGINE_DIR.'classes/system.class.php';
require ENGINE_DIR.'classes/delete.class.php';
require ENGINE_DIR . 'init.queue.php';
switch($act){
	case 'savedescription';	// bdcom epon add description onu
		$olt = isset($_POST['olt'])?Clean::int($_POST['olt']) : null;
		$onu = isset($_POST['onu'])?Clean::int($_POST['onu']) : null;
		$oidid = isset($_POST['oidid'])?Clean::int($_POST['oidid']) : null;
		$description = isset($_POST['description'])?Clean::text($_POST['description']) : null;
		$getswitch = $db->Fast('switch','*',['id'=>$olt]);
		$getonu = $db->Fast('onus','*',['idonu'=>$onu]);
		if(!empty($getswitch['snmprw']) && ($oidid==1 || $oidid==33)){
			snmp2_set($getswitch['netip'], $getswitch['snmprw'], '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.9.'.$getonu['zte_idport'].'.'.$getonu['keyonu'], 's', "{$description}");
			$db->SQLupdate('onus',['name'=>trim($description)],['idonu'=>$onu]);
		}		
		if(!empty($getswitch['username']) && !empty($getonu['inface']) && !empty($getswitch['password']) && $oidid==1 && $description){
			$telnet = new PMonTelnet($getswitch);	
			$err_num = $telnet->err_num;
			if($err_num){	
				$telnet->err('interface epon '.$getonu['inface'].' add description '.$telnet->descr($err_num));
			}
			$commands = [
				"enable","config","interface epon".$getonu['inface'],"description ".trim($description),"exit","write","exit","exit"
			];
			$result = $telnet->executeCommands($commands);
			$db->SQLupdate('onus',['name'=>trim($description)],['idonu'=>$onu]);
		}
		$go->go('?do=onu&id='.$onu);
	break;
	case 'change_vlan':	
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;
		$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']) : null;
		$name = isset($_POST['name']) ? Clean::text($_POST['name']) : null;
		if (isset($olt) && $olt > 0 && isset($idonu) && $idonu > 0 && isset($name)) {
			$switch = getSwitchById($pdo,$olt);
			if(!empty($switch['id']) && !empty($switch['username']) && $access->get('dev' . $switch['id'])) {
				$replacements = [
					'lang_edit' => $lang['edit'],'idonu' => $idonu,'olt' => $olt
				];
				if($name=='bdcomgpon'){
					echo $tpl->loadForm('bdcom_gpon_form_vlan_input.tpl', $replacements);
				}elseif($name=='gcomepon'){
					$onus = getOnuById($pdo,$idonu);
					$onu_vlan = @snmp2_walk($switch['netip'],$switch['snmpro'], '1.3.6.1.4.1.13464.1.13.3.16.1.7.0.'.$onus['zte_idport'].'.'.$onus['keyonu'].'.1', 100000, 5);
					if(isset($onu_vlan[0])){
						$uvlan = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '',$onu_vlan[0]);
						$uvlan = str_replace([' ', '.', '-', ':'], '', $uvlan);
					}
					$replacements['vlan'] = $uvlan;
					echo $tpl->loadForm('gcom_epon_form_vlan_input.tpl', $replacements);
				}			
			}			
		}
		die;		
		break;	
	case 'bdcom_gpon_basic_info':			
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;
		$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']) : null;
		if (isset($olt) && $olt > 0 && isset($idonu) && $idonu > 0) {
			$switch = getSwitchById($pdo,$olt);
			$onus = getOnuById($pdo,$idonu);
			if(!empty($switch['id']) && !empty($switch['username']) && $access->get('dev' . $switch['id'])) {
				$cmd_basic_info = bdcom_gpon_basic_info($onus);
				$telnet = new PMonTelnet($switch);	
				if($telnet->err_num){	
					$telnet->err('Telnet error '.$telnet->descr($err_num));
				}
				$commands = array(
					"enable", $cmd_basic_info, " "
				);	
				$result = $telnet->do_comand("enable\r", true);
				$result .= $telnet->do_comand($cmd_basic_info."\r", true);
				usleep(100);
				$result .= $telnet->do_comand(" ", true);
				usleep(100);				
				$result .= $telnet->do_comand(" ", true);
				usleep(100);				
				$result .= $telnet->do_comand(" ", true);
				usleep(100);
				echo format_time_bfcom_gpon_time($result);
			}
		}	
		die;
		break;	
	case 'change_vlan_telnet':	
		$vlan = isset($_POST['vlan']) ? Clean::int($_POST['vlan']) : 1;
		$gvan = isset($_POST['gvan']) ? Clean::text($_POST['gvan']) : null;
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;
		$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']) : null;
		$name = isset($_POST['name']) ? Clean::text($_POST['name']) : null;
		if (isset($olt) && $olt > 0 && isset($idonu) && $idonu > 0 && isset($vlan) && $vlan>0) {
			$switch = getSwitchById($pdo,$olt);
			$onus = getOnuById($pdo,$idonu);
			if(!empty($switch['id']) && !empty($switch['username']) && $access->get('dev' . $switch['id'])) {
				$data_vlan = ['switch' => $switch,'onus'=> $onus,'vlan'=> $vlan];
				if ($gvan) {
					$data_vlan['gvan'] = $gvan;
				}
				$command_vlan = template_change_vlan($data_vlan);
				$telnet = new PMonTelnet($switch);	
				if($telnet->err_num){	
					$telnet->err('Telnet error '.$telnet->descr($err_num));
				}
				$result = $telnet->executeCommands($command_vlan);
				echo'ok';
				$sender = str_replace('>', ']',str_replace('<', '[',sprintf($lang['editvlan'],$switch['place'], $onus['inface'], $vlan).''));
				$db->SQLinsert('notification',['status'=>1,'type'=>27,'system'=>'monitor','message'=>$sender,'added'=>date('Y-m-d H:i:s')]);
			}
		}
		die;
		break;
	case 'deleteont':
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;
		if (isset($olt) && $olt > 0) {
			$switch = getSwitchById($pdo,$olt);
			if(
				($switch['oidid'] == 1 || $switch['oidid'] == 2) 
				&& 
				(isset($_POST['data']) && is_array($_POST['data']))
			) {
				$temp_idonu = array_map('intval', $_POST['data']);
				$DeleteManager->signature($switch);
				foreach ($temp_idonu as $idonu) {
					$DeleteManager->delete_ont($idonu);
				}
			}
		}
		exit;
	break;	
	case 'deletetaskers':
		$ids = $_POST['data'] ?? [];
		$ids = array_filter(array_map('intval', (array)$ids));
		if ($ids) {
			$in  = str_repeat('?,', count($ids) - 1) . '?';   // ?,?,?
			$sql = "DELETE FROM taskers WHERE id IN ($in)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute($ids);
			echo $stmt->rowCount();
		}
		exit;
	break;
	case 'backup':		
		if(isset($confPMon['BACKUP_OLT']) && !empty($confPMon['BACKUP_OLT']) && $confPMon['BACKUP_OLT']==1){
			$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;	
			if(isset($olt) && $olt>0){
				$getswitch = $db->Fast('switch','class',['id'=>$olt]);
				$task_data = [
					'data' => [45],'switch' => $olt,'type' => 'worker','properties' => [1],'headers' => [1]
				];
				$message = $context->createMessage(json_encode($task_data));
				$context->createProducer()->send($queue, $message);	
				echo 'ok';
			}
		}
		die;
	break;	
	case 'worker':	
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;	
		$jobid = isset($_POST['jobid']) ? Clean::int($_POST['jobid']) : null;	
		if(isset($olt) && $olt>0 && isset($jobid) && $jobid>0){
			$task_data = [
				'workid' => $jobid,'device_id' => (int)$olt,'type' => 'monitor'
			];
			$message = $context->createMessage(json_encode($task_data));
			$context->createProducer()->send($queue, $message);	
			echo 'ok';
		}	
		exit;
	break;	
	case 'fdbtable':		
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;	
		if(isset($olt) && $olt>0){
			$getswitch = $db->Fast('switch','class',['id'=>$olt]);
			$task_data = [
				'data' => [$olt],'switch' => $olt,'type' => 'worker','properties' => [1],'headers' => [1]
			];
			$message = $context->createMessage(json_encode($task_data));
			$context->createProducer()->send($queue, $message);	
			echo 'ok';
		}
		die;
	break;
	case 'system1':		
		$olt = isset($_POST['olt'])?Clean::int($_POST['olt']) : null;
		if(isset($olt)){
			$getswitch = $db->Fast('switch','*',['id'=>$olt]);
			if(!empty($getswitch['netip']) && !empty($getswitch['snmprw']) && $getswitch['oidid']==6){
				$result = snmp2_set($getswitch['netip'],$getswitch['snmprw'],"1.3.6.1.4.1.3902.1082.20.1.2.10.1.1.0",'i',"1");
				echo 'ok';
			}			
		}
		die;
	break;	
	case 'system2':		
		$olt = isset($_POST['olt'])?Clean::int($_POST['olt']) : null;
		if(isset($olt) && $olt > 0){
			$getswitch = $db->Fast('switch','id,snmprw,netip,oidid',['id'=>$olt]);
			$logger->init(['log'=>'device','type'=>'monitor','descr'=>'Write all','deviceid'=>$olt,'userid'=>$USER['id'],'username'=>$USER['username']]);
			if(!empty($getswitch['snmprw']) && !empty($getswitch['netip'])  && !empty($getswitch['oidid']) == 1){
				@snmp2_set($getswitch['netip'],$getswitch['snmprw'], "1.3.6.1.4.1.3320.20.15.1.1.0",'i',"1");
				sleep(3);
				echo 'ok';
				exit;
			}
			echo 'ok';
			exit;
		}	
	break;	
	case 'bdcomwriteall':		
		$olt = isset($_POST['olt'])?Clean::int($_POST['olt']) : null;
		if(isset($olt)){
			$getswitch = $db->Fast('switch','*',['id'=>$olt]);
			if(!empty($getswitch['username']) && !empty($getswitch['password']) && $oidid==1){
				$telnet = new PMonTelnet($getswitch);	
				$err_num = $telnet->err_num;
				if($err_num){	
					$telnet->err('write '.$telnet->descr($err_num));
				}
				$commands = [
					"enable","config","write","exit","exit","exit"
				];
				$result = $telnet->executeCommands($commands);
			}
			$go->go('?do=detail&act=olt&id='.$olt);
		}
	break;
	case 'huaweiregmod1':	
		require ENGINE_DIR.'functions/regonu.php';
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (
				isset($_POST['data']['olt']) && 
				isset($_POST['data']['onu_id']) && 
				isset($_POST['data']['ifindex']) && 
				isset($_POST['data']['onu_name']) && 
				isset($_POST['data']['onu_vlan']) && 
				isset($_POST['data']['onu_invlan']) && 
				isset($_POST['data']['onu_lp']) && 
				isset($_POST['data']['onu_sp']) && 
				isset($_POST['data']['onu_sn']) && 
				isset($_POST['data']['onu_inface'])
			) {
				$olt = (int)$_POST['data']['olt'];
				$onu_id = (int)$_POST['data']['onu_id'];
				$ifindex = (int)$_POST['data']['ifindex'];
				$onu_name = Clean::text($_POST['data']['onu_name']);
				$onu_vlan = (int)$_POST['data']['onu_vlan'];
				$onu_invlan = (int)$_POST['data']['onu_invlan'];
				$lineprofile_name = Clean::text($_POST['data']['onu_lp']);
				$srvprofile_name = Clean::text($_POST['data']['onu_sp']);
				$onu_sn = Clean::text($_POST['data']['onu_sn']);
				$onu_inface = Clean::text($_POST['data']['onu_inface']);
			}
			$getswitch = $db->Fast('switch','id,snmprw,snmpro,oidid,place,netip',['id'=>$olt]);
			if(!empty($getswitch['snmprw']) && ($getswitch['oidid']==14 || $getswitch['oidid']==33)){
				$host = $getswitch['netip'];
				$privat = $getswitch['snmprw'];
				$cmd_onu = "snmpset -v2c -c $privat -Ir  $host " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.43.1.2.{$ifindex}.{$onu_id}' 'i' '1' " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3.{$ifindex}.{$onu_id}' 'x' '$onu_sn' " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.43.1.6.{$ifindex}.{$onu_id}' 'i' '1' " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.43.1.7.{$ifindex}.{$onu_id}' 's' '$lineprofile_name' " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.43.1.8.{$ifindex}.{$onu_id}' 's' '$srvprofile_name' " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.43.1.9.{$ifindex}.{$onu_id}' 's' '$onu_name' " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.43.1.10.{$ifindex}.{$onu_id}' 'i' '4'";
					exec($cmd_onu, $output, $return_var);
					sleep(1);
					$sp = get_service_port($host, $getswitch['snmpro']);
					$p_rack = ($ifindex & 16252928) >> 19;
					$p_slot = ($ifindex & 253952) >> 13;
					$p_port = ($ifindex & 3840) >> 8;
					$uvlan = $sp + 100 + $onu_id;	
				$cmd_serviceport = "snmpset -v2c -c $privat -Ir  $host " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.2.{$sp}' 'i' '{$p_rack}' " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.3.{$sp}' 'i' '{$p_slot}' " . 
					"'1.3.6.1.4.1.2011.5.14.5.2.1.4.{$sp}' 'i' '{$p_port}' " . 
					"'1.3.6.1.4.1.2011.5.14.5.2.1.5.{$sp}' 'i' '{$onu_id}' " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.6.{$sp}' 'i' '1' " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.7.{$sp}' 'i' '4' " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.8.{$sp}' 'i' '{$onu_vlan}' " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.11.{$sp}' 'i' '1' " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.12.{$sp}' 'i' '{$onu_invlan}' " .
					"'1.3.6.1.4.1.2011.5.14.5.2.1.25.{$sp}' 'i' '{$uvlan}' ".
					"'1.3.6.1.4.1.2011.5.14.5.2.1.15.{$sp}' 'i' '4' ";
					exec($cmd_serviceport, $output, $return_var);
					sleep(5);
				$cmd_nat_vlan = "snmpset -v2c -c $privat -Ir  $host " .
					"'1.3.6.1.4.1.2011.6.128.1.1.2.62.1.7.{$ifindex}.{$onu_id}.1' 'i' '{$onu_invlan}'";
					exec($cmd_nat_vlan, $output, $return_var);

			}
		}
		die;
	break;	
}
?>
