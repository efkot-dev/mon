<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$user_id_pmon = $USER['id'];
$sqlinsert = array();
$data_sql = array();
switch($act){
	case 'savedevice': 
		if($access->get('setup')){		
		if(isset($_POST['name']))
			$data_sql['name'] = Clean::text($_POST['name']);			
		if(isset($_POST['deviceid']))
			$data_sql['deviceid'] = Clean::int($_POST['deviceid']);			
		if(isset($_POST['group']))
			$data_sql['groups'] = Clean::int($_POST['group']);		
		if(isset($_POST['ip']))
			$data_sql['ip'] = Clean::str($_POST['ip']);
		if(isset($_POST['mac']))
			$data_sql['mac'] = Clean::str($_POST['mac']);
		if(isset($_POST['sn'])) 
			$data_sql['sn'] = Clean::str($_POST['sn']);		
		if(isset($_POST['community']))
			$data_sql['community'] = Clean::str($_POST['community']);	
		if(!empty($data_sql['deviceid']) && !empty($data_sql['ip'])){
			$get_model = $db->Fast('equipment','*',['id'=>$data_sql['deviceid']]);
		}
		if(!empty($data_sql['ip'])){
			$select_switch = $db->Fast('switch','*',['netip'=>$data_sql['ip']]);
		}
		if(empty($select_switch['id'])){
			if(!empty($data_sql['deviceid']) && !empty($data_sql['ip']) && !empty($data_sql['community']) && !empty($get_model['name'])){
				$sqlinsert['inf'] = $get_model['name'];
				$sqlinsert['model'] = $get_model['model'];
				$sqlinsert['netip'] = $data_sql['ip'];
				$sqlinsert['snmpro'] = $data_sql['community'];
				if(!empty($data_sql['groups']))
					$sqlinsert['groups'] = $data_sql['groups'];
				$sqlinsert['device'] = $get_model['device'];
				$sqlinsert['oidid'] = $get_model['oidid'];
				$sqlinsert['img'] = $get_model['photo'];
				$sqlinsert['added'] = $time;
				$sqlinsert['updates'] = date('Y-m-d H:i:s', strtotime('- 1 hour'));
				$sqlinsert['place'] = $data_sql['name'];
				$sqlinsert['monitor'] = 'no';
				$sqlinsert['typecheck'] = '1h';
				if(!empty($data_sql['mac']))
					$sqlinsert['mac'] = $data_sql['mac'];
				if(!empty($data_sql['sn']))
					$sqlinsert['sn'] = $data_sql['sn'];			
				if(!empty($get_model['phpclass'])){
					$sqlinsert['class'] = $get_model['phpclass'];
				}
				$db->SQLinsert('switch',$sqlinsert);
				$deviceid = $db->getInsertId();
				if(isset($deviceid) && $deviceid > 0){
					$db->SQLinsert('checkaccess',['uid'=>$user_id_pmon,'types'=>'dev'.$deviceid]);
					$cacheManager->delete("user_access_".$user_id_pmon);
					del_cache_simple_sql('checkaccess_'.$user_id_pmon);
					first_cron($pdo,$deviceid,$sqlinsert['oidid'],$user_id_pmon,$time);
				}
			}
		}
		}
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("list_olt_all");
		}
		$go->redirect('device');	
		break;	
	case 'changemodel': // edit model			
		if($access->get('setupdevice')){
			if(isset($_POST['place'])){
				$place = Clean::text($_POST['place']);
			}			
			if(isset($_POST['netip'])){
				$netip = Clean::text($_POST['netip']);
			}
			$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
			$deviceid = isset($_POST['deviceid']) ? Clean::int($_POST['deviceid']): null;	
			if(isset($deviceid) && $deviceid>0 && isset($id) && $id>0 && isset($place) && isset($netip)){
				$data_switch = $db->Fast('switch','*',['id'=>$id]);
				$data_equipment = $db->Fast('equipment','*',['id'=>$deviceid]);
				$device = $data_equipment['device'];
				$device_inf = $data_equipment['name'];
				$device_model = $data_equipment['model'];
				$device_phpclass = $data_equipment['phpclass'];
				$img = $data_equipment['photo'];
				$oidid = $data_equipment['oidid'];
				$sql = "
				UPDATE switch SET
				oidid = '{$oidid}', img = '{$img}', place = '{$place}', class = '{$device_phpclass}', netip = '{$netip}', 
				model = '{$device_model}', inf = '{$device_inf}', device = '{$device}'
				WHERE id = '{$id}'";
				$db->query($sql);
				$db->SQLdelete('onus',['olt' => $data_switch['id']]);
				$db->SQLdelete('switch_port_err',['deviceid' => $data_switch['id']]);
				$db->SQLdelete('switch_port',['deviceid' => $data_switch['id']]);
				$db->SQLdelete('switch_photo',['deviceid' => $data_switch['id']]);
				$db->SQLdelete('switch_pon',['oltid' => $data_switch['id']]);				
				$db->SQLdelete('monitoring',['deviceid' => $data_switch['id']]);		
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("list_olt_all");
					$cacheManager->delete("equipment_" . $data_switch['id']);
				}
				$go->go('/?do=detail&act=olt&id='.$id);
			}
		}
		die;
		break;	
	case 'taskers': // Taskers		
		$deviceId = intval($_GET['deviceid']);
		if(isset($deviceId) && $deviceId>0){
			$sql = "
				SELECT 
					t.id AS task_id, 
					t.workid, 
					t.type, 
					t.interval, 
					t.last_run_time, 
					t.status 
				FROM taskers t
				WHERE t.deviceid = $deviceId
				ORDER BY t.workid ASC
			";
			$tasks = $db->SimpleWhile($sql);
				header('Content-Type: application/json');
			echo json_encode($tasks);
			exit;
		}else{
			die('');
		}
		break;	

	case 'updatevlan': // зберігаємо дані по влан		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if(isset($id) && $id>0){
			$getvlan = $db->Fast('ipvlans','*',['id'=>$id]);
			if(isset($_POST['name'])){
				$sqlinsert['name'] = Clean::str($_POST['name']);
			}			
			if(isset($_POST['vlan'])){
				$sqlinsert['vlan'] = Clean::int($_POST['vlan']);
			}
			if(isset($getvlan['id']) && $getvlan['id']>0 && isset($sqlinsert)){
				$db->SQLupdate('ipvlans',$sqlinsert,['id'=>$getvlan['id']]);
			}
		}
		$go->redirect('vlan');
		exit;
	break;	
	case 'deletvlan': // видалити влан		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if(isset($id) && $id>0){
			$getvlan = $db->Fast('ipvlans','*',['id'=>$id]);
			if(isset($getvlan['id']) && $getvlan['id']>0){
				$db->SQLdelete('ipvlans',['id' => $getvlan['id']]);
			}
		}
		$go->redirect('vlan');
		exit;
		break;	
	case 'scheduler': 	
		if(isset($_POST['category'])){
			$category_id = Clean::int($_POST['category']);
		}	
		$icon = isset($_POST['icon']) ? Clean::text($_POST['icon']): '';
		$description = '';		
		if(isset($_POST['user'])){
			$employee_id = Clean::int($_POST['user']);
		}		
		if(isset($_POST['start_date'])){
			$start_date = Clean::str($_POST['start_date']);
		}			
		if(isset($_POST['description'])){
			$description = Clean::str($_POST['description']);
		}		
		if(isset($_POST['end_date'])){
			$end_date = Clean::str($_POST['end_date']);
		}	
		if (is_valid_id($category_id) && is_valid_id($employee_id) && isset($start_date) && isset($end_date)) {			
			$db->query("INSERT INTO calendar_events 
			(`employee_id`,`category_id`,`start_date`,`end_date`,`description`,`icon`) VALUES 
			('{$employee_id}','{$category_id}', '{$start_date}','{$end_date}','{$description}','{$icon}')");
		}
		$go->redirect('calendar');
		exit;
		break;	
	case 'savecalendar': 
		if(isset($_POST['name'])){
			$name = Clean::str($_POST['name']);
		}		
		if(isset($_POST['color_text'])){
			$color_text = Clean::str($_POST['color_text']);
		}		
		if(isset($_POST['color_fon'])){
			$color_fon = Clean::str($_POST['color_fon']);
		}
		if(isset($name) && isset($color_text) && isset($color_fon)){
			$db->query("INSERT INTO calendar_categories (`name`, `color`) VALUES ('{$name}','{$color_fon}|{$color_text}')");
		}
		$go->redirect('calendar');
		exit;
		break;	
	case 'updatenote': // зберігаємо доступ до додатка	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		$notes = isset($_POST['notes']) ? Clean::int($_POST['notes']): null;	
		if(isset($_POST['name'])){
			$title = Clean::str($_POST['name']);
		}
		if(isset($_POST['content'])){
			$message = $_POST['content'];
		}
		if(is_valid_id($id) && isset($title) && isset($message) && is_valid_id($notes)){		
			$db->query("UPDATE notes SET title = '{$title}', message = '{$message}' WHERE id = {$notes}");
			$go->go('/?do=detail&act=olt&page=note&id='.$id.'&view=detail&notes='.$notes);
			exit;
		}
		$go->redirect('main');
		exit;	
	break;
	case 'savenote': // зберігаємо доступ до додатка
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if(isset($_POST['name'])){
			$title = Clean::str($_POST['name']);
		}
		if(isset($_POST['content'])){
			$message = $_POST['content'];
			$deviceid = $id;
		}
		if(is_valid_id($id)){		
			$db->query("INSERT INTO notes (`title`, `message`, `deviceid`, `userid`) VALUES ('{$title}','{$message}','{$deviceid}','{$user_id_pmon}')");
			$go->go('/?do=detail&act=olt&page=note&id='.$id);
			exit;
		}
		$go->redirect('main');
		exit;
	break;
	case 'updateuserapk': // зберігаємо доступ до додатка
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if($id>1 && $access->get('setup')){
			$allowedFields = [
				'post_searchonu','post_alarmping3','post_oblenergo','post_newonu','post_ponmap','post_gpstracker','post_sklad','post_admin','post_olt','post_odometr','post_ping3'
			];
			$selectedFields = [];
			foreach ($allowedFields as $field) {
				if (isset($_POST[$field]) && $_POST[$field] == 'on') {
					$selectedFields[] = $field;
				}
			}
			$dataString = implode(',', $selectedFields);
			$dataString = str_replace(['post_', '.', ':'], '', $dataString);
			if(isset($dataString)){
				$db->SQLupdate('users',['android'=>$dataString],['id'=>$id]);
				$go->redirect('users');	
				exit;
			}
		}
	break;	

	case 'telegram': // зберігаємо telegram			
		if(isset($_POST['types'])){
			$types = Clean::str($_POST['types']);
		}		
		if(isset($_POST['chatid'])){
			$telegramchatid = Clean::str($_POST['chatid']);
		}		
		if(isset($_POST['messageid'])){
			$messageid = Clean::str($_POST['messageid']);
		}		
		if(isset($_POST['userid'])){
			$userid = Clean::str($_POST['userid']);
		}
		if(isset($types)){
			if($types=='chat'){
				$db->SQLupdate('config',['value'=>'chat'],['name'=>'typestelegram']);
			$db->SQLupdate('config',['value'=>$telegramchatid],['name'=>'telegramchatid']);
			}elseif($types=='bot'){
				$db->SQLupdate('config',['value'=>'bot'],['name'=>'typestelegram']);
				$db->SQLupdate('config',['value'=>$userid],['name'=>'userid']);				
			}elseif($types=='groups'){
				$db->SQLupdate('config',['value'=>'groups'],['name'=>'typestelegram']);
				$db->SQLupdate('config',['value'=>$telegramchatid],['name'=>'telegramchatid']);
				$db->SQLupdate('config',['value'=>$messageid],['name'=>'messageid']);
			}elseif($types=='off'){
				$db->SQLupdate('config',['value'=>'off'],['name'=>'typestelegram']);	
				$db->SQLupdate('config',['value'=>''],['name'=>'telegramchatid']);
				$db->SQLupdate('config',['value'=>''],['name'=>'messageid']);				
				$db->SQLupdate('config',['value'=>''],['name'=>'userid']);				
				$db->SQLupdate('config',['value'=>'off'],['name'=>'telegram']);				
			}
			$cacheType = defined('CACHE') ? CACHE : 'file';
			if($cacheType=='file'){
				$cacheFilePath_1 = CACHE_DIR.CACHE_FILE_CONFIG.'.json';
				if (file_exists($cacheFilePath_1)) {
					@unlink($cacheFilePath_1);
				}
			}elseif($cacheType=='redis'){
				$cacheKey = 'config:' . CACHE_FILE_CONFIG;
				$redis->del($cacheKey);
			}
		}
		$go->go('/?do=telegram');
	break;	
	case 'onu_equipment':		
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::str($_POST['name']);
		}			
		if(isset($_POST['model'])){
			$sqlinsert['model'] = Clean::str($_POST['model']);
		}		
		if(isset($_POST['mac'])){
			$sqlinsert['mac'] = Clean::str($_POST['mac']);
		}		
		if(isset($_POST['sn'])){
			$sqlinsert['sn'] = Clean::str($_POST['sn']);
		}			
		if(isset($_POST['port'])){
			$sqlinsert['port'] = Clean::int($_POST['port']);
		}			
		if(isset($_POST['idonu'])){
			$idonu = Clean::int($_POST['idonu']);
			$sqlinsert['idonu'] = Clean::int($_POST['idonu']);
		}
		if(isset($_POST['netip'])){
			$sqlinsert['netip'] = Clean::str($_POST['netip']);
		}			
		if(isset($_POST['device_id'])){
			$device_id = Clean::int($_POST['device_id']);
			$device = $db->Fast('switch','*',['id'=>$device_id]);
			$sqlinsert['device_id'] = $device['id'];
			$sqlinsert['netip'] = $device['netip'];
			$sqlinsert['model'] = $device['inf'].' '.$device['model'];
		}		
		if(isset($_POST['device'])){
			$sqlinsert['device'] = Clean::str($_POST['device']);
		}	
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['model']) && !empty($sqlinsert['name']) && $idonu>0){
			$db->SQLinsert('onus_equipment',$sqlinsert);
		}
		$go->go('/?do=onu&id='.$idonu);
	break;	
	case 'cleared': 
		$sqlinsert = [];
		$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']) : null;
		$dataonusid = isset($_POST['dataonusid']) ? Clean::int($_POST['dataonusid']) : null;
		if ($idonu && $dataonusid) {
			$sqlinsert['idonu'] = $idonu;
			$sqlinsert['dataonusid'] = $dataonusid;
			$db->query("UPDATE onusdata SET pontree = '0', ponelement = '0' WHERE id = '{$sqlinsert['dataonusid']}'");
			$dataonu = $db->Fast('onusdata', '*', ['id' => $sqlinsert['dataonusid']]);
			$onus = $db->Fast('onus', '*', ['idonu' => $sqlinsert['dataonusid']]);
			if (!empty($confPMon['CACHE'])) {
				$cacheManager->delete("onus_data_" . $dataonu['onukey']);
			}
			$onusId = (int)$sqlinsert['dataonusid'];
			$keyPon = 'onu_id_'.(int)$idonu;
			$inputPon = 'input_onu_id_'.(int)$idonu;
			$stmt = $pdo->prepare("SELECT id FROM ponmap_elements WHERE type='onu' AND name=:name LIMIT 1");
			$stmt->execute([':name' => $keyPon]);
			$pon = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!empty($pon['id'])) {
				$eid = (int)$pon['id'];
				$stmt1 = $pdo->prepare("DELETE FROM ponmap_connect WHERE connect1= '{$inputPon}' OR connect2= '{$inputPon}'");
				$stmt1->execute();
				$stmt2 = $pdo->prepare("DELETE FROM ponmap_connectors WHERE element_id = '{$eid}'");
				$stmt2->execute();
				$stmt3 = $pdo->prepare("DELETE FROM ponmap_elements WHERE id = '{$eid}'");
				$stmt3->execute();
			}			
			$logger->init([
				'log'=>'onu','type'=>'fibermap_reconnect',
				'descr'=>$lang['fiber_del_conn_log'].' '.$onus['inface'].' '.$dataonu['onukey'],
				'deviceid'=>$onus['olt'],'onuid'=>$idonu,'userid'=>$user_id_pmon, 'username'=>$USER['username']]);
		}
		exit;
	break;	
	case 'savetaskcomment': // зберігаємо коментар до завдання		
		if(isset($_POST['comment'])){
			$sqlinsert['comment'] = Clean::str($_POST['comment']);
		}		
		if(isset($_POST['id'])){
			$sqlinsert['taskid'] = Clean::int($_POST['id']);
		}	
		if(isset($sqlinsert['taskid']) && !empty($sqlinsert['taskid']) && isset($sqlinsert['comment']) && !empty($sqlinsert['comment'])){
			$sqlinsert['autorid'] = $user_id_pmon;
			$sqlinsert['added'] = $time;
			$db->SQLinsert('task_list_comment',$sqlinsert);
			$go->go('/?do=taskman&act=view&id='.$sqlinsert['taskid']);
		}
		$go->go('/?do=taskman');
		break;	
		
	case 'updatemonitorip': // зберігаємо ір в базу monitor_ip
		if(isset($_POST['id'])){
			$id = Clean::int($_POST['id']);
		}	
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::str($_POST['name']);
		}		
		if(isset($_POST['ip'])){
			$sqlinsert['ip'] = Clean::str($_POST['ip']);
		}		
		if(isset($_POST['monitor'])){
			$sqlinsert['monitor'] = Clean::str($_POST['monitor']);
			$sqlinsert['status'] = 1;
			$sqlinsert['online'] = $time;
		}else{
			$sqlinsert['status'] = 2;
		}
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['name']) && !empty($sqlinsert['ip']) && isset($id) && $id>0){
			$db->SQLupdate('monitor_ip',$sqlinsert,['id'=>$id]);
			$go->go('/?do=monitorip&act=view&id='.$id);
		}
		$go->go('/?do=monitorip');
		break;		
	break;	
		
	case 'savemonitorip': // зберігаємо ір в базу monitor_ip
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::str($_POST['name']);
		}		
		if(isset($_POST['ip'])){
			$sqlinsert['ip'] = Clean::str($_POST['ip']);
		}		
		if(isset($_POST['monitor'])){
			$sqlinsert['monitor'] = Clean::str($_POST['monitor']);
			$sqlinsert['status'] = 1;
			$sqlinsert['online'] = $time;
		}
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['name']) && !empty($sqlinsert['ip'])){
			$db->SQLinsert('monitor_ip',$sqlinsert);
		}
		$go->go('/?do=monitorip');
		break;			
	case 'saveipmanvlan': // зберігаємо ір в базу
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::str($_POST['name']);
		}		
		if(isset($_POST['vlan'])){
			$sqlinsert['vlan'] = Clean::str($_POST['vlan']);
		}		
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['name']) && !empty($sqlinsert['vlan'])){
			$db->SQLinsert('ipvlans',$sqlinsert);
		}
		$go->go('/?do=vlan');
		break;	
		
	case 'saveipman': // зберігаємо ір в базу
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::str($_POST['name']);
		}		
		if(isset($_POST['note'])){
			$sqlinsert['note'] = Clean::str($_POST['note']);
		}			
		if(isset($_POST['ipblock'])){
			$sqlinsert['ipblock'] = Clean::str($_POST['ipblock']);
		}		
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['name']) && !empty($sqlinsert['ipblock'])){
			$db->SQLinsert('ipblock',$sqlinsert);
		}
		$go->go('/?do=ipman');
		break;	
	
	case 'saveipmangroup': // зберігаємо групу для IP в базу		
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::str($_POST['name']);
		}
		if(isset($_POST['note'])) {
			$sqlinsert['note'] = Clean::str($_POST['note']);
		}
		if(isset($_POST['color'])){
			$sqlinsert['color'] = Clean::str($_POST['color']);
		}
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['name']) && !empty($sqlinsert['color'])){
			$db->SQLinsert('ipgroups', $sqlinsert); // Виконуємо вставку даних до бази
		}
		$go->go('/?do=ipman');
		break;
		
	case 'addipipman': // зберігаємо ір в базу		
		if(isset($_POST['id'])){
			$sqlinsert['blockid'] = Clean::str($_POST['id']);
		}		
		if(isset($_POST['vlan'])){
			$sqlinsert['vlan'] = Clean::str($_POST['vlan']);
		}			
		if(isset($_POST['ip'])){
			$sqlinsert['ip'] = Clean::str($_POST['ip']);	
			$sqlinsert['name'] = Clean::str($_POST['name']);	
		}
		if(isset($_POST['group'])){
			$sqlinsert['idgroups'] = Clean::str($_POST['group']);	
		}
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['ip']) && !empty($sqlinsert['blockid'])){
			$db->SQLinsert('ipaddress',$sqlinsert);
			$go->go('/?do=ipman&idblock='.$sqlinsert['blockid']);
		}
		$go->redirect('main');	
		break;	

	case 'savebattery': // зберігаємо savebattery			
		if(isset($_POST['name']))
			$sqlinsert['name'] = Clean::str($_POST['name']);			
		if(isset($_POST['sn']))
			$sqlinsert['sn'] = Clean::text($_POST['sn']);			
		if(isset($_POST['model']))
			$sqlinsert['model'] = Clean::str($_POST['model']);		
		if(isset($_POST['types']))
			$sqlinsert['types'] = Clean::str($_POST['types']);		
		if(isset($_POST['typebattery']))
			$sqlinsert['voltage'] = Clean::int($_POST['typebattery']);		
		if(isset($_POST['amper']))
			$sqlinsert['amper'] = Clean::int($_POST['amper']);			
		$sqlinsert['added'] = $time;
		if(!empty($sqlinsert['name']) && !empty($sqlinsert['model']) && !empty($sqlinsert['types']) && !empty($sqlinsert['voltage'])){
			$db->SQLinsert('battery',$sqlinsert);
		}
		if(isset($sqlinsert['batteryid']) && (int)$sqlinsert['batteryid'] > 0){
			$go->go('/?do=battery&act=viewbattery&id='.(int)$sqlinsert['batteryid']);
			break;
		}
		$go->redirect('battery');
		break;	

	case 'sendbattery': // підключаємо батарею до моніторинга + логуємо			
		if(isset($_POST['batteryid']))
			$sqlinsert['batteryid'] = Clean::int($_POST['batteryid']);		
		if(isset($_POST['unitid']))
			$sqlinsert['unitid'] = Clean::int($_POST['unitid']);	
		if(isset($sqlinsert['batteryid']) && isset($sqlinsert['unitid']) && $sqlinsert['unitid']>0){
			$sqlinsert['added'] = $time;
			$db->SQLinsert('battery_unit',$sqlinsert);			
		}
		if(isset($sqlinsert['batteryid']) && (int)$sqlinsert['batteryid'] > 0){
			$go->go('/?do=battery&act=viewbattery&id='.(int)$sqlinsert['batteryid']);
			break;
		}
		$go->redirect('battery');
		break;	

	case 'connectbattery': // підключаємо батарею до моніторинга + логуємо			
		if(isset($_POST['batteryid']))
			$sqlinsert['batteryid'] = Clean::int($_POST['batteryid']);		
		if(isset($_POST['deviceid']))
			$sqlinsert['deviceid'] = Clean::int($_POST['deviceid']);	
		if(isset($_POST['monitor']))
			$sqlinsert['connectd'] = Clean::str($_POST['monitor']);	
		if(!empty($sqlinsert['batteryid']) && !empty($sqlinsert['deviceid']) && !empty($sqlinsert['connectd'])){
			$batteryId = (int)$sqlinsert['batteryid'];
			$deviceId = (int)$sqlinsert['deviceid'];
			$connectd = (string)$sqlinsert['connectd'];
			$addedAt = $time;

			$oldUsed = $db->Simple("SELECT * FROM battery_used WHERE batteryid = '{$batteryId}' ORDER BY id DESC LIMIT 1");
			$oldUnit = $db->Simple("SELECT * FROM battery_unit WHERE batteryid = '{$batteryId}' ORDER BY id DESC LIMIT 1");

			// One battery -> one current placement.
			$db->SQLdelete('battery_used', ['batteryid' => $batteryId]);
			$db->SQLdelete('battery_unit', ['batteryid' => $batteryId]);

			if ($connectd === 'unit') {
				$db->SQLinsert('battery_unit', [
					'batteryid' => $batteryId,
					'unitid' => $deviceId,
					'added' => $addedAt,
				]);
			} else {
				$db->SQLinsert('battery_used', [
					'batteryid' => $batteryId,
					'deviceid' => $deviceId,
					'connectd' => $connectd,
					'added' => $addedAt,
				]);
			}

			$oldPlace = '';
			if (!empty($oldUsed['id'])) {
				$oldPlace = 'from ' . (string)$oldUsed['connectd'] . ' #' . (int)$oldUsed['deviceid'];
			} elseif (!empty($oldUnit['id'])) {
				$oldPlace = 'from unit #' . (int)$oldUnit['unitid'];
			}

			$newPlace = $connectd . ' #' . $deviceId;
			$historyText = $lang['battery_1'] . ' ' . $newPlace;
			if ($oldPlace !== '') {
				$historyText = 'Moved ' . $oldPlace . ' to ' . $newPlace;
			}

			$db->SQLinsert('battery_history', [
				'batteryid' => $batteryId,
				'deviceid' => $deviceId,
				'connectd' => $connectd,
				'history' => $historyText,
				'added' => $addedAt,
			]);
		}
		if(isset($sqlinsert['batteryid']) && (int)$sqlinsert['batteryid'] > 0){
			$go->go('/?do=battery&act=viewbattery&id='.(int)$sqlinsert['batteryid']);
			break;
		}
		$go->redirect('battery');	
	break;	

	case 'updateping3': // update інформацію про ping3	
		if(isset($_POST['name']))
			$sqlinsert['name'] = Clean::str($_POST['name']);			
		if(isset($_POST['netip']))
			$sqlinsert['netip'] = Clean::str($_POST['netip']);				
		if(isset($_POST['snmpro']))
			$sqlinsert['snmpro'] = Clean::str($_POST['snmpro']);			
		if(isset($_POST['typebattery']))
			$sqlinsert['typebattery'] = Clean::str($_POST['typebattery']);			
		if(isset($_POST['energystatus']))
			$sqlinsert['energystatus'] = Clean::str($_POST['energystatus']);			
		if(isset($_POST['id']))
			$id = Clean::int($_POST['id']);			
		if(isset($_POST['location']))
			$sqlinsert['locationid'] = Clean::int($_POST['location']);			
		if(isset($_POST['group']))
			$sqlinsert['groups'] = Clean::int($_POST['group']);			
		if(isset($_POST['channel']))
			$sqlinsert['channel'] = Clean::int($_POST['channel']);		
		if(isset($_POST['status20']))
			$sqlinsert['status20'] = Clean::int($_POST['status20']);			
		if(isset($_POST['status40']))
			$sqlinsert['status40'] = Clean::int($_POST['status40']);			
		if(isset($_POST['status60']))
			$sqlinsert['status60'] = Clean::int($_POST['status60']);			
		if(isset($_POST['status80']))
			$sqlinsert['status80'] = Clean::int($_POST['status80']);			
		if(isset($_POST['status100']))
			$sqlinsert['status100'] = Clean::int($_POST['status100']);
		if($_POST['monitor']=='yes'){
			$sqlinsert['monitor'] = 'yes';
		}else{
			$sqlinsert['monitor'] = 'no';
		}		
		if($id && !empty($sqlinsert['name']) && !empty($sqlinsert['netip']) && !empty($sqlinsert['snmpro']) && !empty($sqlinsert['typebattery'])){
			$db->SQLupdate('mon_ping3',$sqlinsert,['id'=>$id]);
			$go->go('/?do=ping3&act=view&id='.$id);
		}
		$go->redirect('ping3');	
	break;	

	case 'saveping3': // зберігаємо saveping3	
		if(isset($_POST['name']))
			$sqlinsert['name'] = Clean::str($_POST['name']);			
		if(isset($_POST['netip']))
			$sqlinsert['netip'] = Clean::str($_POST['netip']);				
		if(isset($_POST['snmpro']))
			$sqlinsert['snmpro'] = Clean::str($_POST['snmpro']);			
		if(isset($_POST['typebattery']))
			$sqlinsert['typebattery'] = Clean::str($_POST['typebattery']);			
		if(isset($_POST['energystatus']))
			$sqlinsert['energystatus'] = Clean::str($_POST['energystatus']);			
		if(isset($_POST['location']))
			$sqlinsert['locationid'] = Clean::int($_POST['location']);		
		if(isset($_POST['typedevice']))
			$sqlinsert['typedevice'] = Clean::int($_POST['typedevice']);			
		if(isset($_POST['group']))
			$sqlinsert['groups'] = Clean::int($_POST['group']);	
		if(isset($_POST['channel']))
			$sqlinsert['channel'] = Clean::int($_POST['channel']);	
		/*	
		if(isset($_POST['status20']))
			$sqlinsert['status20'] = Clean::int($_POST['status20']);			
		if(isset($_POST['status40']))
			$sqlinsert['status40'] = Clean::int($_POST['status40']);			
		if(isset($_POST['status60']))
			$sqlinsert['status60'] = Clean::int($_POST['status60']);			
		if(isset($_POST['status80']))
			$sqlinsert['status80'] = Clean::int($_POST['status80']);			
		if(isset($_POST['status100']))
			$sqlinsert['status100'] = Clean::int($_POST['status100']);	
		*/
		if($_POST['monitor']=='yes'){
			$sqlinsert['monitor'] = 'yes';
		}else{
			$sqlinsert['monitor'] = 'no';
		}			
		if(!empty($sqlinsert['name']) && !empty($sqlinsert['netip']) && !empty($sqlinsert['snmpro']) && !empty($sqlinsert['typebattery'])){
			$db->SQLinsert('mon_ping3',$sqlinsert);	
		}
		$go->redirect('ping3');
	break;	

	case 'saveconfig': // зберігаємо налаштування системи	
		if($access->get('setup')){	
			if(isset($_POST['sklad']))
				$sqlinsert['sklad'] = Clean::str($_POST['sklad']);			
			if(isset($_POST['lang']))
				$sqlinsert['lang'] = Clean::str($_POST['lang']);	
			if(isset($_POST['pon']))
				$sqlinsert['pon'] = Clean::str($_POST['pon']);	
			if(isset($_POST['tag']))
				$sqlinsert['tag'] = Clean::str($_POST['tag']);	
			if(isset($_POST['comment']))
				$sqlinsert['comment'] = Clean::str($_POST['comment']);	
			if(isset($_POST['configport']))
				$sqlinsert['configport'] = Clean::str($_POST['configport']);	
			if(isset($_POST['unit']))
				$sqlinsert['unit'] = Clean::str($_POST['unit']);	
			if(isset($_POST['telegram']))
				$sqlinsert['telegram'] = Clean::str($_POST['telegram']);	
			if(isset($_POST['telegramtoken']))
				$sqlinsert['telegramtoken'] = Clean::str($_POST['telegramtoken']);	
			$sqlinsert['criticsignal'] = (isset($_POST['criticsignal']) ? Clean::int($_POST['criticsignal']):1);	
			$sqlinsert['criticonu64'] = (isset($_POST['criticonu64']) ? Clean::int($_POST['criticonu64']):1);	
			$sqlinsert['criticonu128'] = (isset($_POST['criticonu128']) ? Clean::int($_POST['criticonu128']):10);	
			if(isset($_POST['telegramchatid']))
				$sqlinsert['telegramchatid'] = Clean::str($_POST['telegramchatid']);	
			if(isset($_POST['viewipswitch']))
				$sqlinsert['viewipswitch'] = Clean::str($_POST['viewipswitch']);				
			if(isset($_POST['marker']))
				$sqlinsert['marker'] = Clean::str($_POST['marker']);			
			if(isset($_POST['logsignal']))
				$sqlinsert['logsignal'] = Clean::str($_POST['logsignal']);	
			if(isset($_POST['map']))
				$sqlinsert['map'] = Clean::str($_POST['map']);			
			if(isset($_POST['geo_lon']))
				$sqlinsert['geo_lon'] = Clean::str($_POST['geo_lon']);				
			if(isset($_POST['statusport']))
				$sqlinsert['statusport'] = Clean::str($_POST['statusport']);				
			if(isset($_POST['errorport']))
				$sqlinsert['errorport'] = Clean::str($_POST['errorport']);				
			if(isset($_POST['geo_lan']))
				$sqlinsert['geo_lan'] = Clean::str($_POST['geo_lan']);		
			if(isset($_POST['monitorapi']))
				$sqlinsert['monitorapi'] = Clean::str($_POST['monitorapi']);			
			if(isset($_POST['rsyslog']))
				$sqlinsert['rsyslog'] = Clean::str($_POST['rsyslog']);					
			if(isset($_POST['typemap']))
				$sqlinsert['typemap'] = Clean::str($_POST['typemap']);			
			if(isset($_POST['skin']))
				$sqlinsert['skin'] = Clean::str($_POST['skin']);			
			if(isset($_POST['root']))
				$sqlinsert['root'] = Clean::str($_POST['root']);		
			if(isset($_POST['url']))
				$sqlinsert['url'] = Clean::str($_POST['url']);			
			if(isset($_POST['badsignalstart']) && isset($_POST['badsignalend'])){
				$badsignalstart = (intval($_POST['badsignalstart']) ? Clean::int($_POST['badsignalstart']) : 28 );
				$badsignalend = (intval($_POST['badsignalend']) ? Clean::int($_POST['badsignalend']) : 40 );
				if($badsignalstart>=$badsignalend){

				}else{
					$sqlinsert['badsignalstart'] = (int)$badsignalstart;
					$sqlinsert['badsignalend'] = (int)$badsignalend;
				}
			}
			if(isset($_POST['countviewpageonu']))
				$sqlinsert['countviewpageonu'] = Clean::int($_POST['countviewpageonu']);			
			if(isset($_POST['countviewpageswitch']))
				$sqlinsert['countviewpageswitch'] = Clean::int($_POST['countviewpageswitch']);
			foreach($config as $val => $data_config){
				if(!empty($sqlinsert[$val]) && $sqlinsert[$val]!==$config[$val]){
					$db->SQLupdate('config',['value'=>$sqlinsert[$val],'update'=>$time],['name'=>$val]);
				}
			}
			$cacheType = defined('CACHE') ? CACHE : 'file';
			if($cacheType=='file'){
				$cacheFilePath_1 = CACHE_DIR.CACHE_FILE_CONFIG.'.json';
				if (file_exists($cacheFilePath_1)) {
					@unlink($cacheFilePath_1);
				}
			}elseif($cacheType=='redis'){
				$cacheKey = 'config:' . CACHE_FILE_CONFIG;
				$redis->del($cacheKey);
			}			
		}
		$go->redirect('config');	
		break;	
	case 'saveoid':	
		if($access->get('setup')){	
			if(isset($_POST['sqlid']))
				$whereoidid['id'] = Clean::int($_POST['sqlid']);		
			if(isset($_POST['oidid']))
				$sqlinsert['oidid'] = Clean::int($_POST['oidid']);		
			if(isset($_POST['descr']))
				$sqlinsert['descr'] = Clean::str($_POST['descr']);		
			if(isset($_POST['pon']))
				$sqlinsert['pon'] = Clean::str($_POST['pon']);		
			if(isset($_POST['types']))
				$sqlinsert['types'] = Clean::str($_POST['types']);		
			if(isset($_POST['inf']))
				$sqlinsert['inf'] = Clean::str($_POST['inf']);		
			if(isset($_POST['oid']))
				$sqlinsert['oid'] = Clean::str($_POST['oid']);
			if(!empty($whereoidid['id']) && is_array($sqlinsert)){
				$db->SQLupdate('oid',$sqlinsert,['id'=>$whereoidid['id']]);
			}
			$go->go('/?do=oid&oidid='.$sqlinsert['oidid']);
		}
		$go->redirect('oid');
		break;	
	case 'newoid':	// додаємо в базу новий оід
		if($access->get('setup')){
		if(isset($_POST['oidid']))
			$sqlinsert['oidid'] = Clean::text($_POST['oidid']);
		if(!empty($sqlinsert['oidid'])){
			$getOID = $db->Simple("SELECT * FROM `equipment` WHERE `oidid` = '".(int)$sqlinsert['oidid']."' LIMIT 1");
			if(!empty($getOID['name'])){
				$sqlinsert['model'] = mb_strtolower($getOID['name']);	
				if(isset($_POST['types']))
					$sqlinsert['types'] = Clean::text($_POST['types']);		
				if(isset($_POST['inf']))
					$sqlinsert['inf'] = Clean::text($_POST['inf']);
				if(isset($_POST['format']))
					$sqlinsert['format'] = Clean::text($_POST['format']);
				if(isset($_POST['descr']))
					$sqlinsert['descr'] = Clean::text($_POST['descr']);
				if(isset($_POST['oid']))
					$sqlinsert['oid'] = Clean::text($_POST['oid']);
				if(preg_match('/epon/i',$_POST['pon'])){
					$sqlinsert['pon'] = 'epon';			
				}elseif(preg_match('/gpon/i',$_POST['pon'])){
					$sqlinsert['pon'] = 'gpon';
				}else{
					$sqlinsert['types'] = Clean::text($_POST['pon']);
				}
				if(!empty($sqlinsert['oid']) && !empty($sqlinsert['oidid']) && !empty($sqlinsert['types']) && !empty($sqlinsert['inf']))
					$db->SQLinsert('oid',$sqlinsert);
			}
		}
		}
		$go->redirect('oid');
		break;	
	case 'deletonu':	// видалення onu			
		if (isset($confPMon['DELETE_ONU']) && !empty($confPMon['DELETE_ONU']) 
			&& $confPMon['DELETE_ONU'] == 1 && isset($USER['class']) && $USER['class']>=4){
			if(isset($_POST['idonu'])){
				$idonu = Clean::int($_POST['idonu']);	
				if(isset($idonu) && $idonu>0){
					delete_onu($idonu);
				}
			}
		}
		break;	
	case 'deletuser':	// видалення користувача	
		if($access->get('setup')){
			if(isset($_POST['id']))
				$sqlinsert['id'] = Clean::int($_POST['id']);
			$getus = $db->Fast('users','*',['id'=>$sqlinsert['id']]);	
			if(!empty($getus['id']))
				$db->SQLdelete('users',['id' => $getus['id']]);
				$db->SQLdelete('checkaccess',['uid' => $getus['id']]);
				del_cache_simple_sql('checkaccess_'.$getus['id']);
			$go->redirect('users');		
		}
		break;		
	case 'delgroup':	// видалення групи	
		if($access->get('group')){	
			if(isset($_POST['id']))
				$sqlinsert['id'] = Clean::int($_POST['id']);
			$getgroup = $db->Fast('groups','*',['id'=>$sqlinsert['id']]);	
			if(!empty($getgroup['id'])){
				$db->SQLdelete('groups',['id' => $getgroup['id']]);
			}
			if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
				$cacheManager->delete("list_groups");
			}
			$go->redirect('group');		
		}
		break;		
	case 'delgroupdev':	// видалення групи	
		if($access->get('group')){	
			if(isset($_POST['id']))
				$sqlinsert['id'] = Clean::int($_POST['id']);
			$getgroupswitch = $db->Fast('switch','*',['id'=>$sqlinsert['id']]);	
			$db->SQLupdate('switch',['groups'=>0],['id'=>$getgroupswitch['id']]);
			$go->go('/?do=group&id='.$getgroupswitch['groups']);
		}
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("list_groups");
		}
		$go->redirect('group');	
		break;		
	case 'deletdevice':	// видалення пристроя	
		if($access->get('setup')){	
			if(isset($_POST['id']))
				$sqlinsert['id'] = Clean::int($_POST['id']);
			$getDev = $db->Fast('switch','*',['id'=>$sqlinsert['id']]);	
			if(!empty($getDev['id'])){
				$db->SQLdelete('switch',['id' => $getDev['id']]);
				$db->SQLdelete('onus',['olt' => $getDev['id']]);
				$db->SQLdelete('swlogport',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_traffic',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_port_err_daily',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_port_err',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_logs',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_port',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_photo',['deviceid' => $getDev['id']]);
				$db->SQLdelete('taskers',['deviceid' => $getDev['id']]);				
				$db->SQLdelete('historysignal',['device' => $getDev['id']]);				
				$db->SQLdelete('geodevice',['deviceid' => $getDev['id']]);				
				$db->SQLdelete('switch_pon',['oltid' => $getDev['id']]);				
				$db->SQLdelete('monitoring',['deviceid' => $getDev['id']]);		
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("list_olt_all");
					$cacheManager->delete("equipment_" . $getDev['id']);
				}				
			}
			$go->redirect('main');		
		}
		break;	
	case 'resetdevice':	// reset пристроя	
		if($access->get('setup')){	
			if(isset($_POST['id']))
				$sqlinsert['id'] = Clean::int($_POST['id']);
			$getDev = $db->Fast('switch','*',['id'=>$sqlinsert['id']]);	
			if(!empty($getDev['id'])){
				$db->SQLupdate('switch',['status'=>'no'],['id'=>$getDev['id']]);
				$db->SQLdelete('onus',['olt' => $getDev['id']]);
				$db->SQLdelete('switch_port_err',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_port',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_photo',['deviceid' => $getDev['id']]);
				$db->SQLdelete('switch_pon',['oltid' => $getDev['id']]);				
				$db->SQLdelete('monitoring',['deviceid' => $getDev['id']]);				
				$db->SQLdelete('devicelogs',['deviceid' => $getDev['id']]);
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("list_olt_all");
					$cacheManager->delete("equipment_" . $getDev['id']);
				}				
			}
			$go->redirect('device');		
		}
		break;	
	case 'updateuser':	// обновлення інформації про користувача
		if($access->get('setup')){	
			$sqlinsert = array();
			if(isset($_POST['id']))
				$getuser['id'] = Clean::int($_POST['id']);
			$getus = $db->Fast('users','*',['id'=>$getuser['id']]);	
			if(!empty($getus['id'])){
				if(isset($_POST['class']))
					$sqlinsert['class'] = Clean::int($_POST['class']);
				if(isset($_POST['username']))
					$sqlinsert['username'] = mb_strtolower(trim(Clean::text($_POST['username'])));				
				if(isset($_POST['lang']))
					$sqlinsert['lang'] = Clean::text($_POST['lang']);		
				if(isset($_POST['name']))
					$sqlinsert['name'] = Clean::text($_POST['name']);			
				if(isset($_POST['newpassword']) && isset($_POST['editpass']) && $_POST['editpass']=='on'){
					$password = Clean::text($_POST['newpassword']);	
					if(strlen($password) >= 6){
						$sqlinsert['password'] = $auth->hashPassword($password);
					}
				}
				if(isset($_POST['setip']) && isset($_POST['onlyip']) && $_POST['onlyip']=='on'){
					$sqlinsert['setip'] = Clean::text($_POST['setip']);
					$sqlinsert['onlyip'] = 'on';
				}else{
					if(isset($_POST['setip']))
						$sqlinsert['setip'] = Clean::text($_POST['setip']);
					$sqlinsert['onlyip'] = 'off';	
				}
				if(isset($_POST['mail']))
					$sqlinsert['email'] = Clean::text($_POST['mail']);
				if(is_array($sqlinsert) && !empty($sqlinsert)){
					$allowed = array('class','username','lang','name','password','setip','onlyip','email');
					$set = array();
					$params = array(':id' => (int)$getus['id']);
					foreach($sqlinsert as $field => $value){
						if(!in_array($field, $allowed, true)){
							continue;
						}
						$set[] = "`{$field}` = :{$field}";
						$params[":{$field}"] = $value;
					}
					if(!empty($set)){
						$sql = "UPDATE users SET ".implode(', ', $set)." WHERE id = :id";
						$stmt = $pdo->prepare($sql);
						$stmt->execute($params);
					}
				}					
			}
		}
		$go->redirect('users');	
		break;	
	case 'saveportmonitor':
		if($access->get('monitordevice')){	
			if(isset($_POST['id']))
				$where['id'] = Clean::int($_POST['id']);
			if(!empty($where['id'])){
				del_cache_simple_sql('switch_port_monitor');
				$getSw = $db->Fast('switch','*',['id'=>$where['id']]);
				if(!empty($getSw['id'])){
					if(is_array($_POST['monitorport'])){
						foreach($_POST['monitorport'] as $key1 => $portid1){
							$configport[$portid1]['id'] = (int)$portid1;
						}
					}
					if(is_array($_POST['monitortelegram'])){
						foreach($_POST['monitortelegram'] as $key2 => $portid2){
							$configsmsport[$portid2]['id'] = (int)$portid2;
						}
					}
					if(is_array($_POST['monitorerr'])){
						foreach($_POST['monitorerr'] as $key3 => $portid3){
							$configerrport[$portid3]['id'] = (int)$portid3;
						}
					}
					$dataPortSwitch = $db->Multi('switch_port','id,monitor',['deviceid'=>$getSw['id']]);
					$setupport = array();
					foreach($dataPortSwitch as $idport => $portvalue) {
						$monitor = false;
						if(!empty($configport[$portvalue['id']]['id']) && $configport[$portvalue['id']]['id']==$portvalue['id'])
							$monitor = true;
						$db->SQLupdate('switch_port',['monitor'=>($monitor?'yes':'no')],['deviceid'=>$getSw['id'],'id'=>$portvalue['id']]);
					}					
					foreach($dataPortSwitch as $idport => $portvalue) {
						$error = false;
						if(!empty($configerrport[$portvalue['id']]['id']) && $configerrport[$portvalue['id']]['id']==$portvalue['id'])
							$error = true;
						$db->SQLupdate('switch_port',['error'=>($error?'yes':'no')],['deviceid'=>$getSw['id'],'id'=>$portvalue['id']]);
					}					
					foreach($dataPortSwitch as $idport => $portvalue) {
						$sms = false;
						if(!empty($configsmsport[$portvalue['id']]['id']) && $configsmsport[$portvalue['id']]['id']==$portvalue['id'])
							$sms = true;
						$db->SQLupdate('switch_port',['sms'=>($sms?'yes':'no')],['deviceid'=>$getSw['id'],'id'=>$portvalue['id']]);
					}
				}
				$go->go('/?do=detail&act=olt&page=monitoring&id='.$getSw['id']);
			}
		}	
		break;	
	case 'newuser':	
		if($access->get('setup')){	
			$class = isset($_POST['class']) ? Clean::int($_POST['class']) : 1;
			$username = isset($_POST['username']) ? mb_strtolower(trim(Clean::text($_POST['username']))) : '';
			$langCode = isset($_POST['lang']) ? Clean::text($_POST['lang']) : 'ua';
			$name = isset($_POST['name']) ? Clean::text($_POST['name']) : '';
			$setip = isset($_POST['setip']) ? Clean::text($_POST['setip']) : '';
			$onlyip = (isset($_POST['onlyip']) && $_POST['onlyip']=='on') ? 'on' : 'off';
			$email = isset($_POST['mail']) ? Clean::text($_POST['mail']) : '';
			$passwordRaw = isset($_POST['password']) ? Clean::text($_POST['password']) : '';

			if($username !== '' && strlen($passwordRaw) >= 6){
				$exists = $db->Fast('users','id',['username'=>$username]);
				if(empty($exists['id'])){
					$passwordHash = $auth->hashPassword($passwordRaw);
					$stmt = $pdo->prepare("INSERT INTO users (`class`,`username`,`lang`,`name`,`setip`,`onlyip`,`password`,`email`,`access`) VALUES (:class,:username,:lang,:name,:setip,:onlyip,:password,:email,:access)");
					$stmt->execute([
						':class' => (int)$class,
						':username' => (string)$username,
						':lang' => ($langCode !== '' ? (string)$langCode : 'ua'),
						':name' => (string)$name,
						':setip' => (string)$setip,
						':onlyip' => (string)$onlyip,
						':password' => (string)$passwordHash,
						':email' => (string)$email,
						':access' => 'yes'
					]);
				}
			}
		}
		$go->redirect('users');	
		break;	
	case 'saveswitch':	
		if($access->get('setup')){	
			if(isset($_POST['devicemodel']))
				$sqlinsert['model'] = Clean::text($_POST['devicemodel']);		
			if(isset($_POST['sn']))
				$sqlinsert['sn'] = Clean::text($_POST['sn']);
			if(isset($_POST['port']))
				$sqlinsert['port'] = Clean::int($_POST['port']);
			$sqlinsert['added'] = $time;
			if(!empty($sqlinsert['model']) && !empty($sqlinsert['port']) && !empty($sqlinsert['sn']))
				$db->SQLinsert('sklad_switch',$sqlinsert);
		}
		break;		
	case 'addmapperip':			
		if(isset($_POST['id'])){	
			$deviceid['id'] = Clean::int($_POST['id']);				
			if(isset($_POST['lan']))
				$sqlinsert['lan'] = Clean::text($_POST['lan']);			
			if(isset($_POST['lon']))
				$sqlinsert['lon'] = Clean::text($_POST['lon']);	
			$db->SQLupdate('monitor_ip',$sqlinsert,['id'=>$deviceid['id']]);
			$go->go('/?do=monitorip&act=view&id='.$deviceid['id']);
		}
		$go->go('/?do=monitorip');
		break;		
	case 'addmapper':			
		if(isset($_POST['id'])){			
			if(isset($_POST['lan']))
				$sqlinsert['lan'] = Clean::text($_POST['lan']);			
			if(isset($_POST['lon']))
				$sqlinsert['lon'] = Clean::text($_POST['lon']);			
			$deviceid['id'] = Clean::int($_POST['id']);			
			if(!empty($deviceid['id']))
				$getswitch = $db->Fast('switch','*',['id'=>$deviceid['id']]);			
			if(!empty($getswitch['id'])){				
				$getdev = $db->Fast('geodevice','*',['deviceid'=>$getswitch['id']]);				
				if(!empty($getdev['id'])){
					$db->SQLupdate('geodevice',$sqlinsert,['id'=>$getdev['id']]);
				}else{					
					$sqlinsert['added'] = $time;
					$sqlinsert['deviceid'] = $getswitch['id'];
					$sqlinsert['device'] = $getswitch['device'];
					$sqlinsert['iconmapper'] = $getswitch['device'];
					$sqlinsert['iconup'] = 'up_'.$getswitch['device'];
					$sqlinsert['icondown'] = 'down_'.$getswitch['device'];
					$sqlinsert['name'] = $getswitch['place'];
					$sqlinsert['name'] = $getswitch['place'];
					$db->SQLinsert('geodevice',$sqlinsert);
				}
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("list_olt_all");
					$cacheManager->delete("equipment_" . $deviceid['id']);
				}
			}
		}
		die();
		break;		
	case 'savegeolocation':	
		if($access->get('location')){	
			if(isset($_POST['id']))
				$getDataWhere['id'] = Clean::int($_POST['id']);
			if(!empty($getDataWhere['id'])){
				$getLoca = $db->Fast('location','*',['id'=>$getDataWhere['id']]);
				if(isset($_POST['lan']))
					$sqlinsert['lan'] = Clean::text($_POST['lan']);
				if(isset($_POST['lon']))
					$sqlinsert['lon'] = Clean::text($_POST['lon']);
				if(!empty($sqlinsert['lon']) && !empty($sqlinsert['lan'])){
					$db->SQLupdate('location',$sqlinsert,['id'=>$getLoca['id']]);
				}
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("location_pmon");
				}
				$go->go('/?do=location&id='.$getDataWhere['id']);	
			}
		}
		break;		
	case 'saveportdescr':
		if($access->get('note_port')){	
			if(isset($_POST['descrport'])){
				$sqlinsert['descrport'] = Clean::text($_POST['descrport']);
			}
			if ($access->get('lock_port') && isset($_POST['lock'])) {
				$inf = Clean::text($_POST['lock']);
				if (in_array($inf, ['no', 'yes'], true)) {
					$sqlinsert['lockport'] = $inf;
				}
			}
			if(isset($_POST['id'])){
				$id = Clean::int($_POST['id']);
			}
			if(!empty($sqlinsert['descrport']) && isset($id) && $id>0){
				$db->SQLupdate('switch_port',$sqlinsert,['id'=>$id]);
			}
			if(isset($id) && $id>0){
				$getPort = $db->Fast('switch_port','deviceid',['id'=>$id]);
				$go->go('/?do=detail&act=olt&page=connect&id='.$getPort['deviceid']);	
			}
		}
		break;		
	case 'delconnect':	
		if($access->get('connectport')){	
			if(isset($_POST['id'])) 
				$sqlinsert['id'] = Clean::int($_POST['id']);			
			$getConnect = $db->Fast('connect_port','*',['id'=>$sqlinsert['id']]);
			if(!empty($getConnect['id'])){
				$db->SQLdelete('connect_port',['id' => $getConnect['id']]);
				$go->go('/?do=detail&act=olt&id='.$getConnect['curd']);
			}
		}
		break;		
	case 'saveconnect':	
		if($access->get('connectport')){		
			if(isset($_POST['curp'])) 
				$sqlinsert['curp'] = Clean::int($_POST['curp']);		
			if(isset($_POST['cursfp'])) 
				$sqlinsert['cursfp'] = Clean::int($_POST['cursfp']);			
			if(isset($_POST['connsfp'])) 
				$sqlinsert['connsfp'] = Clean::int($_POST['connsfp']);			
			if(isset($_POST['curd'])) 
				$sqlinsert['curd'] = Clean::int($_POST['curd']);		
			if(isset($_POST['connp'])) 
				$sqlinsert['connp'] = Clean::int($_POST['connp']);			
			if(isset($_POST['connd'])) 
				$sqlinsert['connd'] = Clean::int($_POST['connd']);	
			if(!empty($sqlinsert['connd']) && !empty($sqlinsert['connp']) && !empty($sqlinsert['curp']) && !empty($sqlinsert['curd'])){
				$db->SQLinsert('connect_port',['cursfp' => $sqlinsert['cursfp'],'connsfp' => $sqlinsert['connsfp'],'connd' => $sqlinsert['connd'],'connp'=>$sqlinsert['connp'],'curd'=>$sqlinsert['curd'],'curp'=>$sqlinsert['curp']]);
				$db->SQLinsert('connect_port',['cursfp' => ($sqlinsert['connsfp']??0),'connsfp' => ($sqlinsert['cursfp']??0),'connd' => $sqlinsert['curd'],'connp'=>$sqlinsert['curp'],'curd'=>$sqlinsert['connd'],'curp'=>$sqlinsert['connp']]);
				$go->go('/?do=detail&act=olt&id='.$sqlinsert['curd']);		
			}
		}
		break;		
	case 'savesfp':	
		if($access->get('sklad')){		
			if(isset($_POST['dist']))
				$sqlinsert['dist'] = Clean::int($_POST['dist']);			
			if(isset($_POST['speed']))
				$sqlinsert['speed'] = Clean::int($_POST['speed']);		
			if(isset($_POST['wavelength']))
				$sqlinsert['wavelength'] = Clean::int($_POST['wavelength']);	
			if(isset($_POST['model'])) 
				$sqlinsert['model'] = Clean::text($_POST['model']);	
			if(isset($_POST['connector'])) 
				$sqlinsert['connector'] = Clean::text($_POST['connector']);	
			if(isset($_POST['types'])) 
				$sqlinsert['types'] = Clean::text($_POST['types']);	
			if(!empty($sqlinsert['types']) && !empty($sqlinsert['wavelength']) && !empty($sqlinsert['connector']) && !empty($sqlinsert['dist']) && !empty($sqlinsert['speed'])){
				$db->SQLinsert('sfp',$sqlinsert);
			}
			$go->go('/?do=sklad&act=sfp');
		}
		break;	
	case 'deleteonuphoto': // видалення всіх фото для ону
		if (empty($_POST['idonu'])) {
			$go->go('/?do=main&error=invalid_id');
			exit;
		}
		$idonu = (int)$_POST['idonu'];
		if ($idonu <= 0) {
			$go->go('/?do=main&error=invalid_id');
			exit;
		}
		$stmt = $pdo->prepare("SELECT id, img FROM onus_photo WHERE idonu = :idonu");
		$stmt->execute(['idonu' => $idonu]);
		$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!$photos) {
			$go->go('/?do=onu&id='.$idonu.'&error=no_photos');
			exit;
		}
		foreach ($photos as $photo) {
			$file = $uploaddir . $photo['img'];
			if (is_file($file)) {
				@unlink($file);
			}
		}
		$stmt = $pdo->prepare("DELETE FROM onus_photo WHERE idonu = :idonu");
		$stmt->execute(['idonu' => $idonu]);
		$go->go('/?do=onu&id='.$idonu.'&success=photos_deleted');
		exit;
		break;
	case 'saveonuphoto': // додавання фото для ону
		if (empty($_POST['id'])) {
			$go->go('/?do=main&error=invalid_id');
			exit;
		}
		$id = (int)$_POST['id'];
		if ($id <= 0) {
			$go->go('/?do=main&error=invalid_id');
			exit;
		}
		$stmt = $pdo->prepare("SELECT * FROM onus WHERE idonu = :id LIMIT 1");
		$stmt->execute(['id' => $id]);
		$onus = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$onus) {
			$go->go('/?do=main&error=onu_not_found');
			exit;
		}
		$onukey = trim(!empty($onus['mac']) ? $onus['mac'] :(!empty($onus['sn']) ? $onus['sn'] : ''));
		if (!$onukey) {
			$go->go('/?do=onu&id='.$id.'&error=invalid_onu_key');
			exit;
		}
		$comment = !empty($_POST['comment'])? Clean::text($_POST['comment']): null;
		if (empty($_FILES['file']['tmp_name'])) {
			$go->go('/?do=onu&id='.$id.'&error=no_file');
			exit;
		}
		$tmpFile = $_FILES['file']['tmp_name'];
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime  = finfo_file($finfo, $tmpFile);
		finfo_close($finfo);
		if (!isset($allowed_types[$mime])) {
			$go->go('/?do=onu&id='.$id.'&error=invalid_file_type');
			exit;
		}
		$newName = 'onu_photo_' . substr(md5(uniqid('', true)), 0, 10) . '.' . $allowedTypes[$mime];
		$uploadPath = $uploaddir . $newName;
		if (!move_uploaded_file($tmpFile, $uploadPath)) {
			$go->go('/?do=onu&id='.$id.'&error=file_upload_failed');
			exit;
		}
		$stmt = $pdo->prepare("INSERT INTO onus_photo	(idonu, onukey, img, comment, userid, added) VALUES (:idonu, :onukey, :img, :comment, :userid, :time)");
		$stmt->execute(['idonu' => $id,'onukey' => $onukey,'img' => $newName,'time' => $time,'comment' => $comment,'userid' => $userid]);
		$go->go('/?do=onu&id='.$id.'&success=true');
		exit;
		break;	
	case 'saveponboxphoto':    
		if (isset($_POST['id'])) {
			$id = Clean::int($_POST['id']);
		}		
		$name = '';
		$note = '';		
		if (isset($id) && $id > 0) {        
			if (isset($_POST['name'])) {
				$name = Clean::text($_POST['name']);
			}                
			if (isset($_POST['note'])) {
				$note = Clean::text($_POST['note']);
			}			
			if (!empty($_FILES['file']['name']) && !empty($name)) {
				$fileType = $_FILES['file']['type'];
				$fileName = $_FILES['file']['name'];
				$tmpFile = $_FILES['file']['tmp_name'];
				$allowed_types = [
					'image/jpeg' => 'jpg','image/png' => 'png',
				];				
				if (!array_key_exists($fileType, $allowed_types)) {
					$go->go('/?do=fiber&act=details&id='.$id.'&error=invalid_file_type');
					exit;
				}				
				if (!preg_match('/^(.+)\.(jpg|jpeg|png)$/i', $fileName)) {
					$go->go('/?do=fiber&act=details&id='.$id.'&error=invalid_file_name');
					exit;
				}				
				$newname = strtolower('pon_' . substr(md5(uniqid(rand(), true)), 0, rand(7, 13)) . '.' . $allowed_types[$fileType]);
				$uploadDir = $uploaddir . $newname;
				if (!move_uploaded_file($tmpFile, $uploadDir)) {
					$go->go('/?do=fiber&act=details&id='.$id.'&error=file_upload_failed');
					exit;
				}
				$sql = "INSERT INTO ponmap_photo (ponid, photo, added, name, note, userid) VALUES ('{$id}', '{$newname}', '{$time}', '{$name}', '{$note}', '{$user_id_pmon}')";
				$db->query($sql);
				$go->go('/?do=fiber&act=details&id='.$id.'&success=true');
				exit;
			}
			$go->go('/?do=main&error=missing_file_or_name');
			exit;
		} 
		$go->go('/?do=main&error=invalid_id');
		exit;
	break;	
	case 'savephoto':
		if($access->get('gallerydevice')){		
			if(isset($_POST['id']))
				$id = Clean::int($_POST['id']);	
			if(isset($_POST['name'])) 
				$sqlinsert['name'] = Clean::text($_POST['name']);	
			if(isset($_POST['note'])) 
				$sqlinsert['note'] = Clean::text($_POST['note']);	
			if(!empty($_FILES['file']['name']) && $id) {
				if (!array_key_exists($_FILES['file']['type'], $allowed_types) )
					$file = false; 
				if (!preg_match('/^(.+)\.(jpg|jpeg|png)$/si', $_FILES['file']['name']) )
					$file = false; 
				if(!empty($sqlinsert['name'])){
					$newname = strtolower('gallerry_'.substr(md5(uniqid(rand(), true)), 0, rand(7, 13)).'.'.$allowed_types[$_FILES['file']['type']]);
					$ifile = $_FILES['file']['tmp_name'];
					$copy = copy($ifile, $uploaddir.$newname);
					if(!$copy){
						
					}else{
						$sql = "INSERT INTO switch_photo (deviceid, photo, added) VALUES ('{$id}', '{$newname}', '{$time}')";
						$db->query($sql);
					}
					$go->go('/?do=detail&act=olt&id='.$id.'&page=gallery');
					exit;
				}
			}
			$go->go('/?do=main');
			exit;
		}
		break;	
	case 'deletlocation':
		if($access->get('location')){			
			if(isset($_POST['id']))
				$id = Clean::int($_POST['id']);	
			$getLoc = $db->Fast('location','*',['id'=>$id]);
			if(!empty($getLoc['id']))
				$db->SQLdelete('location',['id' => $getLoc['id']]);
			if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
				$cacheManager->delete("location_pmon");
			}
		}
		$go->redirect('location');	
		break;	
	case 'addgroup':		
		if($access->get('group')){		
			if(isset($_POST['name'])) 
				$sqlinsert['name'] = Clean::text($_POST['name']);
			if(isset($_POST['groups']))
				$sqlinsert['group_types'] = Clean::int($_POST['groups']);
			$sqlinsert['added'] = $time;
			if(!empty($sqlinsert['name']))
				$db->SQLinsert('groups',$sqlinsert);
		}
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("list_groups");
		}
		$go->redirect('group');
		break;	
	case 'newlocation':	
		if($access->get('location')){		
			if(isset($_POST['name'])) 
				$sqlinsert['name'] = Clean::text($_POST['name']);	
			if(isset($_POST['note'])) 
				$sqlinsert['note'] = Clean::text($_POST['note']);
			if(!empty($_FILES['file']['name']) && $id) {
				if (!array_key_exists($_FILES['file']['type'], $allowed_types) )
					$file = false; 
				if (!preg_match('/^(.+)\.(jpg|jpeg|png)$/si', $_FILES['file']['name']) )
					$file = false; 
				if($file){
					$newname = substr(md5(uniqid(rand(), true)), 0, rand(7, 13)).'.'.$allowed_types[$_FILES['file']['type']];
					$ifile = $_FILES['file']['tmp_name'];
					$copy = copy($ifile, $uploaddir.$newname);
					if(!$copy){
					
					}else{
						$sqlinsert['photo'] = $newname;
					}
				}
			}
			$sqlinsert['added'] = $time;
			if(!empty($sqlinsert['name'])){
				$db->SQLinsert('location',$sqlinsert);
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("location_pmon");
				}
			}
		}
		$go->redirect('location');	
		break;	
	case 'saveeditgroup':		
		if($access->get('group')){			
			if(isset($_POST['id']))
				$id = Clean::int($_POST['id']);	
			$getgr = $db->Fast('groups','*',['id'=>$id]);
			if(!empty($getgr['id'])){
				if(isset($_POST['name'])) 
					$sqlinsert['name'] = Clean::text($_POST['name']);
				if(!empty($sqlinsert['name'])){
					$db->SQLupdate('groups',$sqlinsert,['id'=>$getgr['id']]);
					$go->go('/?do=group&id='.$id);
				}
			}
		}
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("list_groups");
		}
		$go->redirect('group');	
		break;	
	case 'saveeditlocation':	
		if($access->get('location')){			
			if(isset($_POST['id']))
				$id = Clean::int($_POST['id']);	
			$getLoc = $db->Fast('location','*',['id'=>$id]);
			if(!empty($getLoc['id'])){
				if(isset($_POST['name'])) 
					$sqlinsert['name'] = Clean::text($_POST['name']);	
				if(isset($_POST['note'])) 
					$sqlinsert['note'] = Clean::text($_POST['note']);
				if(!empty($_FILES['file']['name']) && $id) {
					if (!array_key_exists($_FILES['file']['type'], $allowed_types) )
						$file = false; 
					if (!preg_match('/^(.+)\.(jpg|jpeg|png)$/si', $_FILES['file']['name']) )
						$file = false; 
					if($file){
						$newname = substr(md5(uniqid(rand(), true)), 0, rand(7, 13)).'.'.$allowed_types[$_FILES['file']['type']];
						$ifile = $_FILES['file']['tmp_name'];
						$copy = copy($ifile, $uploaddir.$newname);
						if(!$copy){
						
						}else{
							$sqlinsert['photo'] = $newname;
						}
					}
				}
				$db->SQLupdate('location',$sqlinsert,['id'=>$getLoc['id']]);
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("location_pmon");
				}
				$go->go('/?do=location&id='.$id);
			}
		}
		$go->redirect('location');
		break;	
	case 'deletsfp':
		if($access->get('sklad')){		
			if(isset($_POST['id']))
				$id = Clean::int($_GET['id']);	
		}
		break;		
	case 'markonu':
		if(isset($_POST['id']))
			$sqlinsert['id'] = Clean::int($_POST['id']);			
		if(isset($_POST['lan']))
			$sqlinsert['lan'] = Clean::text($_POST['lan']);		
		if(isset($_POST['lon']))
			$sqlinsert['lon'] = Clean::text($_POST['lon']);	
		if(!empty($sqlinsert['lon']) && !empty($sqlinsert['lan']) && !empty($sqlinsert['id'])){
			if(!empty($sqlinsert['id']))			
				$dataonu = $db->Fast('onus','*',['idonu'=>$sqlinsert['id']]);
			if(!empty($dataonu['idonu'])){
				$onukey = (!empty($dataonu['mac'])?$dataonu['mac']:(!empty($dataonu['sn'])?$dataonu['sn']:null));
				if(isset($onukey)){
					$getonu = $db->Fast('onusdata','*',['onukey' => $onukey]);
					if(!empty($getonu['id'])){
						$db->SQLupdate('onusdata',['lon'=>$sqlinsert['lon'],'lan'=>$sqlinsert['lan']],['id'=>$getonu['id']]);
					}else{
						$db->SQLinsert('onusdata',['onukey'=>$onukey,'lan'=>$sqlinsert['lan'],'lon'=>$sqlinsert['lon']]);
					}
					if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
						$cacheManager->delete("onus_data_".$onukey);
					}
				}
				$go->go('/?do=onu&id='.$sqlinsert['id']);
			}
		}
		break;
	case 'connecthouse':			
		if(isset($_POST['device'])){
			$device = Clean::int($_POST['device']);
		}
		if(isset($_POST['id'])){
			$id = Clean::int($_POST['id']);
		}
		if(is_valid_id($id) && is_valid_id($device)){
			$getSwitch = $db->Fast('switch','*',['id'=>$device]);
			if(is_valid_id($getSwitch['id'])){			
				$db->SQLinsert('skyscraper_device',['build_id'=>$id,'userid'=>$user_id_pmon,'device_id'=>$device,'types'=>$getSwitch['type'],'added'=> $time ]
				);
			}
			$go->go('/?do=house&act=view&id='.$id);
			exit;
		}
		$go->go('/?do=house');
		break;
	case 'updatehouse':		
		if(isset($_POST['pidizd'])){
			$pidizd = Clean::int($_POST['pidizd']);
		}		
		if(isset($_POST['streetid'])){
			$streetid = Clean::int($_POST['streetid']);
		}
		if(isset($_POST['poverx'])){
			$poverx = Clean::int($_POST['poverx']);
		}		
		if(isset($_POST['id'])){
			$houseid = Clean::int($_POST['id']);
		}
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::text($_POST['name']);	
		}
		if(isset($_POST['note'])){
			$sqlinsert['note'] = Clean::text($_POST['note']);	
		}
		$file = true;
		if(!empty($_FILES['file']['name']) && is_valid_id($houseid)){
			if (!array_key_exists($_FILES['file']['type'], $allowed_types) )
				$file = false; 
			if (!preg_match('/^(.+)\.(jpg|jpeg|png)$/si', $_FILES['file']['name']) )
				$file = false; 
			if(!empty($sqlinsert['name'])){
				$newname = 'house_'.substr(md5(uniqid(rand(), true)), 0, rand(7, 13)).'.'.$allowed_types[$_FILES['file']['type']];
				$ifile = $_FILES['file']['tmp_name'];
				$copy = copy($ifile, $uploaddir.$newname);
				if(!$copy){
					$file = false; 
				}else{
					$sqlinsert['photo'] = $newname;
				}
			}
		}
		if(is_valid_id($houseid)){
			require_once ENGINE_DIR.'functions/building.php';
			$getHouse = $db->Fast('skyscraper','*',['id'=>$houseid]);
			if(is_valid_id($getHouse['id'])){
				if(is_valid_id($pidizd) && $pidizd > $getHouse['pidizdiv_int']){
					$sqlinsert['pidizdiv_int'] = $pidizd;
				}
				if(is_valid_id($streetid) && $streetid != $getHouse['streetid']){
					$getStreet = $db->Fast('location_street','*',['id'=>$streetid]);
					$sqlinsert['streetid'] = $getStreet['id'];
					$sqlinsert['street'] =  $getStreet['name'];
				}
				if(is_valid_id($poverx) && $poverx > $getHouse['poverxiv_int']){
					$sqlinsert['poverxiv'] = gen_sequence_not($poverx);
					$sqlinsert['poverxiv_int'] = $poverx;
				}				
				if(isset($sqlinsert)){
					$db->SQLupdate('skyscraper',$sqlinsert,['id'=>$getHouse['id']]);
				}
				$go->go('/?do=house&act=view&id='.$getHouse['id']);
				exit;
			}
		}
		$go->redirect('main');
		exit;
		break;
	case 'addmapperonu':			
		if(isset($_POST['id']))
			$sqlinsert['id'] = Clean::int($_POST['id']);			
		if(isset($_POST['lan']))
			$sqlinsert['lan'] = Clean::text($_POST['lan']);		
		if(isset($_POST['lon']))
			$sqlinsert['lon'] = Clean::text($_POST['lon']);	
		if(!empty($sqlinsert['lon']) && !empty($sqlinsert['lan']) && !empty($sqlinsert['id'])){
			if(!empty($sqlinsert['id']))			
				$dataonu = $db->Fast('onus','*',['idonu'=>$sqlinsert['id']]);
			if(!empty($dataonu['idonu'])){
				$onukey = (!empty($dataonu['mac'])?$dataonu['mac']:(!empty($dataonu['sn'])?$dataonu['sn']:null));
				if(isset($onukey)){
					$getonu = $db->Fast('onusdata','*',['onukey' => $onukey]);
					if(!empty($getonu['id'])){
						$db->SQLupdate('onusdata',['lon'=>$sqlinsert['lon'],'lan'=>$sqlinsert['lan']],['id'=>$getonu['id']]);
					}else{
						$db->SQLinsert('onusdata',['onukey'=>$onukey,'lan'=>$sqlinsert['lan'],'lon'=>$sqlinsert['lon']]);
					}
					if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
						$cacheManager->delete("onus_data_".$onukey);
					}
				}
			}
		}
		die;
		break;	
	case 'saveuid':	
		if($access->get('addbillingonu')){		
			if(isset($_POST['id']))
				$sqlinsert['id'] = Clean::int($_POST['id']);			
			if(isset($_POST['uid']))
				$sqlinsert['uid'] = Clean::text($_POST['uid']);	
			if(!empty($sqlinsert['id']))			
				$dataonu = $db->Fast('onus','*',['idonu'=>$sqlinsert['id']]);
			if(!empty($sqlinsert['uid']) && !empty($sqlinsert['id']) && !empty($dataonu['idonu'])){
				$onukey = (!empty($dataonu['mac'])?$dataonu['mac']:(!empty($dataonu['sn'])?$dataonu['sn']:null));
				if(isset($onukey)){
					$getonu = $db->Fast('onusdata','*',['onukey' => $onukey]);
					if(!empty($getonu['id'])){
						$db->SQLupdate('onusdata',['uid'=>$sqlinsert['uid']],['id'=>$getonu['id']]);
					}else{
						$db->SQLinsert('onusdata',['onukey'=>$onukey,'uid'=>$sqlinsert['uid']]);
					}
					if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
						$cacheManager->delete("onus_data_".$onukey);
					}
				}
				$go->go('/?do=onu&id='.$sqlinsert['id']);
			}
		}
		break;	
	case 'savetag':	
		if($access->get('addtagonu')){
			$onukey = $onukey ?? null;			
			if(isset($_POST['id']))
				$sqlinsert['id'] = Clean::int($_POST['id']);			
			if(isset($_POST['tag']))
				$sqlinsert['tag'] = Clean::text($_POST['tag']);
			if(!empty($sqlinsert['id']))			
				$dataonu = $db->Fast('onus','*',['idonu'=>$sqlinsert['id']]);
			if(!empty($sqlinsert['tag']) && !empty($sqlinsert['id']) && !empty($dataonu['idonu'])){
				$onukey = (!empty($dataonu['mac'])?$dataonu['mac']:(!empty($dataonu['sn'])?$dataonu['sn']:null));
				if(isset($onukey)){
					$getonu = $db->Fast('onusdata','*',['onukey' => $onukey]);
					if(!empty($getonu['id'])){
						$db->SQLupdate('onusdata',['tag'=>$sqlinsert['tag']],['id'=>$getonu['id']]);
					}else{
						$db->SQLinsert('onusdata',['onukey'=>$onukey,'tag'=>$sqlinsert['tag']]);
					}
					if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
						$cacheManager->delete("onus_data_".$onukey);
					}
				}
				$go->go('/?do=onu&id='.$sqlinsert['id']);
			}
		}
		break;		
	case 'savebilling':	
		if($access->get('setup')){	
			if(isset($_POST['billing']))
				$sqlinsert['billing'] = Clean::str($_POST['billing']);			
			if(isset($_POST['billingtype']))
				$sqlinsert['billingtype'] = Clean::str($_POST['billingtype']);			
			if(isset($_POST['billingurl']))
				$sqlinsert['billingurl'] = Clean::str($_POST['billingurl']);			
			if(isset($_POST['billingapikey']))
				$sqlinsert['billingapikey'] = Clean::str($_POST['billingapikey']);
			if(is_array($sqlinsert)){
				foreach($sqlinsert as $conf => $value){
					if($conf){
						$SQLconf = $db->Fast('config','id',['name'=>$conf]);
						if(!empty($SQLconf['id'])){
							$db->SQLupdate('config',['value'=>$value],['id'=>$SQLconf['id']]);
						}
					}
				}
				$cacheType = defined('CACHE') ? CACHE : 'file';
				if($cacheType=='file'){
					$cacheFilePath_1 = CACHE_DIR.CACHE_FILE_CONFIG.'.json';
					if (file_exists($cacheFilePath_1)) {
						@unlink($cacheFilePath_1);
					}
				}elseif($cacheType=='redis'){
					$cacheKey = 'config:' . CACHE_FILE_CONFIG;
					$redis->del($cacheKey);
				}
			}
			$go->go('/?do=apibilling');	
		}
		break;		
	case 'savesetup':
		if($access->get('setup')){		
		if(isset($_POST['id']))
			$sqlinsert['id'] = Clean::int($_POST['id']);		
		if(isset($_POST['location'])){
			$sqlinsert['location'] = Clean::int($_POST['location']);
			$dataLocation = $db->Fast('location','*',['id'=>$sqlinsert['location']]);
			if(!empty($dataLocation['name'])){
				$sqlinsert['locationname'] = $dataLocation['name'];
			}
		}
		$dataSwitch = $db->Fast('switch','*',['id'=>$sqlinsert['id']]);
		if(!$dataSwitch['id'])
			$go->redirect('main');
		if(isset($_POST['place']))
			$sqlinsert['place'] = Clean::text($_POST['place']);
		if(isset($_POST['mac']))
			$sqlinsert['mac'] = Clean::text($_POST['mac']);
		if(isset($_POST['sn']))
			$sqlinsert['sn'] = Clean::text($_POST['sn']);
		if(isset($_POST['group']) && !empty($_POST['group'])){
			$sqlinsert['groups'] = Clean::int($_POST['group']);
		}		
		if(isset($_POST['curl_pool']) && !empty($_POST['curl_pool'])){
			$sqlinsert['curl_pool'] = Clean::int($_POST['curl_pool']);
		}		
		if(isset($_POST['temp_cpu']) && !empty($_POST['temp_cpu'])){
			$sqlinsert['temp_cpu'] = Clean::int($_POST['temp_cpu']);
		}
		if($_POST['monitor']=='yes'){
			$sqlinsert['monitor'] = 'yes';
		}else{
			$sqlinsert['monitor'] = 'no';
		}		
		if($_POST['connect']=='yes'){
			$sqlinsert['connect'] = 'yes';
		}else{
			$sqlinsert['connect'] = 'no';
		}		
		if($_POST['gallery']=='yes'){
			$sqlinsert['gallery'] = 'yes';
		}else{
			$sqlinsert['gallery'] = 'no';
		}
		if(isset($_POST['typecheck'])){
			$sqlinsert['typecheck'] = Clean::text($_POST['typecheck']);
		}			
		if(isset($_POST['netip']))
			$sqlinsert['netip'] = Clean::text($_POST['netip']);
		if(!empty($dataSwitch['id'])){
			if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
				$cacheManager->delete("list_olt_all");
				$cacheManager->delete("equipment_" . $sqlinsert['id']);
			}
			$db->SQLupdate('switch',$sqlinsert,['id'=>$sqlinsert['id']]);
			$go->go('/?do=setup&id='.$sqlinsert['id']);
		}	
		}		
		break;		
	case 'deleteonu':	
		if(isset($_GET['idonu'])){
			$idonu = (int)$_GET['idonu'];
			delete_onu($idonu);
			$go->go('/?do=main');
		}
	break;		
	case 'menu':
		$action = isset($_POST['action']) ? Clean::text($_POST['action']) : false;
		$show = ($_POST['show'] === 'view');
		if (isset($user_id_pmon) && $user_id_pmon > 0 && $action) {
			if (!empty($USER['golovna'])) {
				$userSettings = json_decode($USER['golovna'], true);
			} else {
				$userSettings = [];
			}
			if ($show=='view') {
				unset($userSettings[$action]);
			} else {
				$userSettings[$action] = false;
			}
			$jsonSettings = json_encode($userSettings);
			$sql = "UPDATE users SET golovna = :golovna WHERE id = :userId";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':golovna', $jsonSettings, PDO::PARAM_STR);
			$stmt->bindParam(':userId', $user_id_pmon, PDO::PARAM_INT);
			$stmt->execute();            
		} 
		if ($show=='view') {
			echo json_encode(['status' => 'true']);			
		}else {
			echo json_encode(['status' => 'false']);
		}
		die();
		break;		
	case 'saveaccess':	
		if($access->get('setup')){	
			if(isset($_POST['id']))
				$getWhere['id'] = Clean::int($_POST['id']);
			$dataSwitch = $db->Fast('switch','*',['id'=>$getWhere['id']]);
			if(!empty($dataSwitch['id'])){
				if(isset($_POST['netip']))
					$sqlinsert['netip'] = Clean::text($_POST['netip']);					
				if(isset($_POST['enablepassword']))
					$sqlinsert['enablepassword'] = Clean::text($_POST['enablepassword']);				
				if(isset($_POST['public']))
					$sqlinsert['snmpro'] = Clean::text($_POST['public']);			
				if(isset($_POST['private']))
					$sqlinsert['snmprw'] = Clean::text($_POST['private']);			
				if(isset($_POST['username']))
					$sqlinsert['username'] = Clean::text($_POST['username']);			
				if(isset($_POST['password']))
					$sqlinsert['password'] = Clean::text($_POST['password']);				
				if(isset($_POST['telnet_port']))
					$sqlinsert['telnet_port'] = Clean::int($_POST['telnet_port']);
				if(is_array($sqlinsert))
					$db->SQLupdate('switch',$sqlinsert,['id'=>$dataSwitch['id']]);
				$go->go('/?do=detail&act='.$dataSwitch['device'].'&id='.$dataSwitch['id']);
			}
		}			
		break;		
}
$go->redirect('main');
die;
?>
