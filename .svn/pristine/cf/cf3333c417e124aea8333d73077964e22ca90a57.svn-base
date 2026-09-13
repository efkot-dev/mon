<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (isset($confPMon['MAC_ROUTER']) && !empty($confPMon['MAC_ROUTER']) && $confPMon['MAC_ROUTER'] == 1) {
	$metatags = array('title'=>$lang['searchmac'],'description'=>$lang['searchmac'],'page'=>'searchmac');
	$tpl->load_template('searchmac.tpl');
	$tpl->set('{result}',$selbill);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');
}
?>