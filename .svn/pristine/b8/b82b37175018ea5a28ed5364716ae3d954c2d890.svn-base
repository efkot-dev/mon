<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$redirect = false;
if(!$access->get('setup')) {
    $go->redirect('main');
}
$id = $_GET['id'] ?? null;
if(!$id) {
    $go->redirect('main');
}
$getusers = $db->Fast('users','*',['id'=>$id]);
if(empty($getusers['id'])){
	$go->redirect('main');
}
$rulesMap = [];
$rulesMapPing = [];
$rulesMapDev = [];
$rulesMapAll = [];
$rulesMapAlarm = [];
$listdevice = $db->Multi('switch');
if(isset($listdevice) && count($listdevice)>0){
	foreach($listdevice as $dev){
		$device_access = 'dev'.$dev['id'];
		$rulesMapDev[$device_access] = [
			'type' => $device_access,'description' => $dev['device'].'=>'.$dev['place']
		];
	}
}
$listping3 = $db->Multi('mon_ping3');
if(isset($listping3) && count($listping3)>0){
	foreach($listping3 as $ping){
		$ping_access = 'ping3_'.$ping['id'];
		$rulesMapPing[$ping_access] = [
			'type' => $ping_access,'description' => 'ping3=>'.$ping['name']
		];
	}
}
$alarm_ping3 = $db->Multi('mon_ping3');
if(isset($alarm_ping3) && count($alarm_ping3)>0){
	foreach($alarm_ping3 as $alarm){
		$ping_access_alarm = 'alarm_ping3_'.$alarm['id'];
		$rulesMapAlarm[$ping_access_alarm] = [
			'type' => $ping_access_alarm,'description' => 'Alarms '.$alarm['name']
		];
	}
}
$allrules = $db->Multi('rules');
if(isset($allrules) && count($allrules)>0){
	foreach($allrules as $rule) {
		$rulesMapAll[$rule['types']] = [
			'type' => $rule['types'],'description' => $lang[$rule['descr']]
		];
	}
}
if (isset($rulesMapDev)) {
	$rulesMap = array_merge($rulesMapDev, $rulesMapAll, $rulesMapAlarm, $rulesMapPing);
} else {
	$rulesMap = $rulesMapAll;
}
if(isset($rulesMapDev)) {
	$rulesMap = array_merge($rulesMapDev, $rulesMapAll, $rulesMapAlarm, $rulesMapPing);
}else{
	$rulesMap = $rulesMapAll;
}
if($access->get('setup') && isset($_GET['types']) && isset($_GET['act'])){
	$typ = Clean::text($_GET['types']);
	if($act=='add' && isset($rulesMap[$typ]) && !empty($getusers['id'])){
		$db->SQLinsert('checkaccess',['uid'=>$getusers['id'],'types'=>$rulesMap[$typ]['type']]);
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("user_access_".$getusers['id']);
			del_cache_simple_sql('checkaccess_'.$getusers['id']);
		}
		$redirect = true;
	}elseif($act=='del' && isset($rulesMap[$typ]) && !empty($getusers['id'])){
		$db->SQLdelete('checkaccess',['uid'=>$getusers['id'],'types'=>$rulesMap[$typ]['type']]);
		$redirect = true;
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("user_access_".$getusers['id']);
			del_cache_simple_sql('checkaccess_'.$getusers['id']);
		}
	}
	if($redirect){
		$go->go('/?do=access&id='.$getusers['id']);
	}
}
$userRulesList = '';
$userRulesMap = [];
$userRules = $db->Multi('checkaccess','*',['uid'=>$getusers['id']]);
if(isset($userRules) && count($userRules)>0){
	foreach($userRules as $userRule) {
		if(isset($rulesMap[$userRule['types']])) {
			$userRulesList .= '<a href="/?do=access&id='.$getusers['id'].'&act=del&types='.$userRule['types'].'"><div class="r-del">'.$rulesMap[$userRule['types']]['description'].'</div></a>';
			$userRulesMap[$userRule['types']] = true;
		}
	}
}
$availableRulesList = '';
foreach($rulesMap as $rule) {
    if(!isset($userRulesMap[$rule['type']])) {
        $availableRulesList .= '<a href="/?do=access&id='.$getusers['id'].'&act=add&types='.$rule['type'].'"><div class="r-add">'.$rule['description'].'</div></a>';
    }
}
$metatags = array('title'=>$lang['access'],'description'=>$lang['descraccess'],'page'=>'access');
$tpl->load_template('access/page.tpl');
$tpl->set('{user}',$getusers['username']);
$tpl->set('{userrulles}',$userRulesList);
$tpl->set('{rulles}',$availableRulesList);
$tpl->set('{result}',$access);
$tpl->compile('content');
$tpl->clear();
?>