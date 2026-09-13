<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
if(empty($id)){
	$go->redirect('main');
}
$metatags = array('title'=>$lang['sys_erroronuhuawei'],'description'=>$lang['sys_erroronuhuawei'],'page'=>'erroronuhuawei');
$getSwitch = $db->Fast('switch','*',['id' => $id]);
if($getSwitch['oidid'] == 14 || $getSwitch['oidid'] == 33) {
	$tpl->load_template('huaweionuerror.tpl');
	$tpl->set('{script}','<script>huaweionuerror('.$getSwitch['id'].');</script>');
	$tpl->set('{place}',$getSwitch['place']);
	$tpl->set('{id}',$getSwitch['id']);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');	
}
?>
