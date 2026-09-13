<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
if(empty($id)){
	$go->redirect('main');
}
$metatags = array('title'=>$lang['onuerror'],'description'=>$lang['onuerror'],'page'=>'onuerror');
$getSwitch = $db->Fast('switch','*',['id' => $id]);
if(!empty($getSwitch['netip']) && $getSwitch['monitor']=='yes' && !empty($confPMon['ERRORONUBDCOM']) && $confPMon['ERRORONUBDCOM'] == 1 ) {
	$tpl->load_template('bdcomonuerror.tpl');
	$tpl->set('{script}','<script>bdcomonuerror('.$getSwitch['id'].');</script>');
	$tpl->set('{place}',$getSwitch['place']);
	$tpl->set('{id}',$getSwitch['id']);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');	
}
?>
