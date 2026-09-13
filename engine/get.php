<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.get.php';
$access_key = false;
$portid = getInput("portid");
$signal = getInput("signal");
$key = getInput("key");
$do = getInput("do");
$dataonu = getInput("data");
$billing = (int)getInput("billing");
if(isValidContentSql($key) || isValidContentSql($do) || isValidContentSql($dataonu)){
	$response = array('success' => false, 'message' => 'not_support_input_data');
	detectError($response);	
}
if(isset($do)){
	$do = totranslit($do);
}else{
	$response = array('success' => false, 'message' => 'not_support_input_do');
	detectError($response);
}
if(empty($key)){
	$response = array('success' => false, 'message' => 'not_support_input_key');
	detectError($response);
}else{
	$key = md5($key);
}
if(isset($do) && $do == 'snmp'){
	require ROOT_DIR.'/inc/init.support.php';
	require ROOT_DIR.'/inc/init.olt.php';
	require ENGINE_DIR.'classes/equipment.class.php';
	require ENGINE_DIR.'classes/snmp.class.php';
	require ENGINE_DIR.'classes/ont.class.php';
}
if (isset($confPMon['PMONAPI']) && !empty($confPMon['PMONAPI']) && $confPMon['PMONAPI'] == 1) {
	$checker = array();
	$sqlapikey = $db->SimpleWhile("SELECT * FROM apikey");
	$access_key = false;
	if(isset($sqlapikey) && count($sqlapikey)>0){
		foreach($sqlapikey as $ap){
			$md5_key = md5($ap['apikey']);
			$checker[$md5_key] = [
				'apikey' => $md5_key,'types' => trim($ap['types']),'ipaccess' => $ap['ipaccess'],'id' => intval($ap['id']),'uid' => intval($ap['userid'])
			];
		}
		if(isset($checker[$key]['apikey']) && $checker[$key]['apikey'] == $key){
			$access_key = true;
			if(isset($checker[$key]['uid'])){
				$usr = $db->Simple("SELECT id, username FROM users WHERE id = '{$checker[$key]['uid']}'");
				$userid = ($usr['id'] ?? null);
				if(isset($userid) && $userid > 0){
					$db->query("UPDATE apikey SET count = COALESCE(count, 0) + 1 WHERE id  = '{$checker[$key]['id']}'");
				}
				if(isset($checker[$key]['ipaccess']) && !empty($checker[$key]['ipaccess'])){
					$my_ip = $db->get_ip();
					if($my_ip != $checker[$key]['ipaccess']){
						$response = array('success' => false, 'message' => 'Check IP for access API: {err1}');
						detectError($response);
					}
				}
			}
		}

	}else{
		$response = array('success' => false, 'message' => 'Check the API KEY err2');
		detectError($response);
	}
}else{
	$response = array('success' => false, 'message' => 'Check the work API: {err1}');
	detectError($response);
}
if(!$access_key){
	$response = array('success' => false, 'message' => 'Check the work API: {err2}');
	detectError($response);
}
if(isset($dataonu) && isset($do) && $dataonu=='uid' && $billing>0){
	$dataType['uid'] = true;
	$api = true;
}elseif(isset($dataonu) && isset($do) && $dataonu=='search' ){
	$api = true;	
}elseif(isset($dataonu) && isset($do)){
	$dataType = detectOnuType($dataonu);
	$api = true;
}
if(!empty($dataType['idonu'])){
	$getonu = $db->Simple("SELECT * FROM onus WHERE idonu = '".(int)$dataType['idonu']."' LIMIT 1");
}elseif(!empty($dataType['uid'])){
	$getonu_ = $db->Simple("SELECT * FROM onus WHERE uid = '".(int)$billing."' LIMIT 1");
	if(isset($getonu_['idonu']) && !empty($getonu_['idonu'])){
		$getonu = $getonu_;
	}	
}elseif(!empty($dataType['sql'])){
	$getonu_ = $db->Simple("SELECT * FROM onus WHERE sn LIKE '%".sanitizeInputSql($dataType['sql'])."%' OR mac LIKE '%".sanitizeInputSql($dataType['sql'])."%' LIMIT 1");
	if(isset($getonu_['idonu']) && !empty($getonu_['idonu'])){
		$getonu = $getonu_;
	}
}elseif(!empty($dataType['mac'])){
	$getonu = $db->Fast('onus','*',['mac' => $dataType['mac']]);
}
if(isset($getonu['idonu']) && !empty($getonu['idonu'])){
	$api = true;
}else{
	$api = true;
}
if($api){
	$expiration = isset($_GET['time']) ? min(300, intval($_GET['time'])) : 300;
	switch($do){
		case 'onu': 
			if(!empty($getonu['mac']))
				$result['mac'] = $getonu['mac'];			
			if(!empty($getonu['sn']))
				$result['sn'] = $getonu['sn'];			
			if(!empty($getonu['inface']))
				$result['inface'] = $getonu['inface'];			
			if(!empty($getonu['type']))
				$result['pon'] = $getonu['type'];			
			if(!empty($getonu['rx']))
				$result['rx'] = $getonu['rx'];			
		break;
	case 'search':
		$search = trim($dataType['sql']);
		if ($search === '') {
			$result = 'Empty search value';
			break;
		}
		$searchSql = sanitizeInputSql($search);
		$sql = "SELECT o.idonu, o.mac, o.sn, o.status, o.rx, o.olt, o.type, o.inface, s.inf, s.model, s.place, s.netip FROM onus o LEFT JOIN switch s ON s.id = o.olt WHERE o.mac LIKE '%{$searchSql}%' OR o.sn  LIKE '%{$searchSql}%' LIMIT 10";
		$rows = $db->SimpleWhile($sql);
		if (empty($rows)) {
			$result = 'ONU not found';
			break;
		}
		if (count($rows) === 1) {
			$row = $rows[0];
			$result = [
				'list' => [
					'idonu'  => (int)$row['idonu'],
					'onukey' => $row['mac'] ?: $row['sn'],
					'status' => (int)$row['status'],
					'rx' => (float)($row['rx'] ?? 0),
					'pon' => $row['type'],
					'inface' => $row['inface'],
					'olt' => [
						'model' => $row['inf'].' '.$row['model'],
						'name' => $row['place'],
						'ip' => $row['netip']
					]
				]
			];			
			break;
		}
		$list = [];
		foreach ($rows as $row) {
			$list[] = ['idonu' => (int)$row['idonu'],'status' => (int)$row['status'],'onukey' => $row['mac'] ?: $row['sn'],'rx' => (float)($row['rx'] ?? 0),'pon' => $row['type'],'inface' => $row['inface'],'olt' => ['model' => $row['inf'].' '.$row['model'],'name' => $row['place'],'ip' => $row['netip']]];
		}
		$result['list'] = $list;
		break;
	case 'snmp': 
		$getolt = $db->Fast('switch','*',['id' => $getonu['olt']]);
		if(snmp_access($getolt)){
			if(!empty($getolt['id']) && $getolt['monitor']=='yes'){
				$OnuClass = new Ont($getolt['id'],$getolt['class'], $db, $logger, $config, $cacheManager, $php_class_device);
				$support = $OnuClass->Support();
				$manual = false;
				if (isset($_GET['type']) && $_GET['type'] != false) {
					$manual = preg_replace('/[^a-zA-Z,]/', '', $_GET['type']);
					if ($manual === '') {
						$manual = false;
					}
				}	
				$cacheKey = md5('api_olt_'.$getolt['id'].'_onu_'.$getonu['idonu'].'_'.$manual);					
				$cachedResult = $cacheManager->get($cacheKey);
				if ($cachedResult !== null) {
					$result = $cachedResult;
					$result['cache'] = true;
				} else {
					if(isset($support)){
						$result = $OnuClass->getResult($getonu, $manual);
					}
					if(!empty($getonu['idonu']))
						$result['idonu'] = $getonu['idonu'];							
					if(!empty($getonu['mac']))
						$result['mac'] = $getonu['mac'];			
					if(!empty($getonu['sn']))
						$result['sn'] = $getonu['sn'];			
					if(!empty($getonu['inface']))
						$result['inface'] = $getonu['inface'];			
					if(!empty($getonu['type']))
						$result['pon'] = $getonu['type'];							
					if(!empty($getonu['online']))
						$result['online'] = $getonu['online'];							
					if(!empty($getonu['offline']))
							$result['offline'] = $getonu['offline'];					
						if(!empty($getolt['id'])){
							$result['olt']['name'] = $getolt['place'];
							$result['olt']['netip'] = $getolt['netip'];						
							$result['olt']['cron'] = $getolt['updates'];
						}
						$cacheManager->set($cacheKey,$result,$expiration);
					}
			}
		}else{
			$response = array('success' => true, 'message' => 'check_switch');
			detectError($response);
		}
		break;
	case 'history':
		if(!empty($getonu['idonu']) && $getonu['idonu']>0){
			$idOnu = (int)$getonu['idonu'];
			$sql = "SELECT curr.signal, curr.datetime FROM historysignal AS curr WHERE curr.onu = {$idOnu} AND curr.datetime >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
			AND NOT EXISTS (
				SELECT 1 FROM historysignal prev
					WHERE prev.onu = curr.onu
						AND prev.device = curr.device
						AND prev.datetime >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
						AND (
							prev.datetime < curr.datetime OR (prev.datetime = curr.datetime AND prev.id < curr.id)
						)
						AND prev.signal = curr.signal
				  )
			ORDER BY curr.datetime, curr.id";
			$result['history'] = [];
			$rows = $db->SimpleWhile($sql);
			if (!empty($rows)) {
				foreach ($rows as $h) {
					$result['history'][] = [
						'rx' => (float)$h['signal'],'time' => $h['datetime']
					];
				}
			}
		}
		break;
		case 'bandwidth':
			$data = array('portid' => $portid);
			$result = get_snmp_data($data);
		break;
	}
}else{
	$response = array('success' => false, 'message' => 'check_input_api_key');
	detectError($response);	
}
if(empty($result)){
	$response = array('success' => true, 'message' => 'empty');
	detectError($response);
}else{
	header('Content-type: application/json');
	$response = array('success' => true, 'message' => $result);
	detectError($response);
}
?>
