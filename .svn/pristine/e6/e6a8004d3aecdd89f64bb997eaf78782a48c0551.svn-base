<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (isset($confPMon['PMONAPP']) && !empty($confPMon['PMONAPP']) && $confPMon['PMONAPP'] == 1 && $access->get('setup')) {
if($act=='del'){
	$id = isset($_GET['id'])?Clean::int($_GET['id']):null;
	if ($id !== null) {
		$acceskey = $db->Fast('apikey', '*', ['id' => $id]);
		$db->SQLdelete('apikey',['id' => $acceskey['id']]);
	}
	$go->go('/?do=pmonapi');
}elseif($act=='parametr'){
	$sqlinsert = array();
	$sqlinsert['apikey'] = isset($_POST['apikey']) ? Clean::text($_POST['apikey']): null;
	$sqlinsert['types'] = isset($_POST['types']) ? Clean::text($_POST['types']): null;
	$sqlinsert['ipaccess'] = isset($_POST['ip']) ? Clean::text($_POST['ip']): null;
	$sqlinsert['userid'] = isset($_POST['users']) ? Clean::int($_POST['users']): null;
	$sqlinsert['added'] = date('Y-m-d H:i:s');
	if(!empty($sqlinsert['apikey']) && !empty($sqlinsert['userid']) && !empty($sqlinsert['types'])){
		$db->SQLinsert('apikey',$sqlinsert);
	}
	$go->go('/?do=pmonapi');
}
$metatags = array('title'=>$lang['apiaccess'],'description'=>$lang['apiaccess'],'page'=>'pmonapi');
$result = '';
$ipaddr = '';
$tpl->load_template('block/addapikey.tpl');
$tpl->set('{apikey}',generatePassword());
$select_type ='<select class="css_select" name="types" id="format">';
$select_type .='<option value="none"></option>';
$select_type .='<option value="app">Andoird APP</option>';
$select_type .='</select>';
$select_user ='<select class="css_select" name="users" id="format">';
$select_user .='<option value="0"></option>';
$sqlusers = $db->SimpleWhile("SELECT * FROM users");
if(is_array($sqlusers)){
	foreach($sqlusers as $us){
$select_user .='<option value="'.$us['id'].'">'.$us['username'].'</option>';
}}
$select_user .='</select>';
$tpl->set('{select_type}',$select_type);
$tpl->set('{select_user}',$select_user);
$tpl->compile('addform');
$tpl->clear();	
$arrayus = array();
$sqlusers = $db->SimpleWhile("SELECT * FROM users");
if(is_array($sqlusers)){
	foreach($sqlusers as $us){
		$arrayus[$us['id']]['usname'] = $us['username'];
	}
}
$sqlapikey = $db->SimpleWhile("SELECT * FROM apikey");
if(is_array($sqlapikey)){
	$result .= '<div id="ontbdcomepon" style="width: 100%;padding: 0;" class="elen"><table class="resp-tab"><thead><tr><th>ID</th><th>added</th><th>Key</th><th>Acess IP</th><th>Stats</th><th>User</th><th>Types</th></thead><tbody>';
	foreach($sqlapikey as $ap){
			$result .='<tr><td>'.$ap['id'].'</td>
			<td width="15%">'.$ap['added'].'</td>
			<td width="20%">'.$ap['apikey'].'<a href="/?do=pmonapi&act=del&id='.$ap['id'].'"><img class="delpmonapi" src="../style/img/close.png"></a></td>
			<td>'.(!empty($ap['ipaccess'])?$ap['ipaccess']:'Full').'</td>
			<td>'.(!empty($ap['count'])?$ap['count']:'--').'</td>
			<td>'.(!empty($arrayus[$ap['userid']]['usname'])?$arrayus[$ap['userid']]['usname']:'-=X=-').'</td>
			<td>'.$ap['types'].'</td>
			</tr></tr>';
	}
	$result .='</tbody></table></div>';	
	$result .= (isset($tpl->result['addform']) ? $tpl->result['addform'] : '');
}
$tpl->load_template('pmonapi.tpl');
$tpl->set('{speedbar}','<div id="onu-speedbar"><a class="brmhref" href="/?do=pmonapi"><i class="fi fi-rr-apps"></i>'.$lang['apiaccess'].'</a>'.$getpage.'</div>');
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();		
}else{
$go->redirect('main');
}
?>