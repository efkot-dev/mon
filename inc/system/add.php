<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$ping3 = '';
if(!$access->get('setup')) {
    $go->redirect('main');
}
$div_message = '';
$div_message .= Message::fromRequest();
switch ($act) {
	case 'all':
		if (isset($confPMon['PING3']) && !empty($confPMon['PING3']) && $access->get('monitordevice')){
			$ping3 .= '<div class="add-dev-one"><a href="/?do=ping3&act=add" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/ping.png"></div></div><div class="add-name">'.$lang['add'].' '.$lang['monbattery'].'</div></div>';
		}		
		if (isset($confPMon['BATTERY']) && !empty($confPMon['BATTERY'])){
			$ping3 .= '<div class="add-dev-one"><a href="/?do=battery&act=addbattery" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/list_battery.png"></div></div><div class="add-name">'.$lang['add_mon_battery'].'</div></div>';
		}		
		if (isset($confPMon['FIBERMAP']) && !empty($confPMon['FIBERMAP']) && $confPMon['FIBERMAP']==1){
			$ping3 .= '<div class="add-dev-one"><a href="#" onclick="ajaxcore(\'addunit\');" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/12unit.png"></div></div><div class="add-name">'.$lang['add'].' '.$lang['vyzol'].'</div></div>';
			$ping3 .= '<div class="add-dev-one"><a href="/?do=fiber&act=kabel" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/vok.png"></div></div><div class="add-name">ВОК</div></div>';
		}		
		if (isset($confPMon['OBL_ENERGO']) && !empty($confPMon['OBL_ENERGO']) && $confPMon['OBL_ENERGO'] == 1) {
			$ping3 .= '<div class="add-dev-one"><a href="/?do=oblenergo&act=add" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/oblenergo.png"></div></div><div class="add-name">'.$lang['oblenergo_add'].'</div></div>';	
			$ping3 .= '<div class="add-dev-one"><a href="/?do=oblenergo&act=addtp" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/oblenergo.png"></div></div><div class="add-name">'.$lang['oblenergo_add_tp'].'</div></div>';			
			$ping3 .= '<div class="add-dev-one"><a href="/?do=oblenergo&act=listconcurent" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/concurent.png"></div></div><div class="add-name">'.$lang['list_provider'].'</div></div>';			
		}
		if (isset($confPMon['PON_HIGH_RISE']) && !empty($confPMon['PON_HIGH_RISE']) && $confPMon['PON_HIGH_RISE'] == 1) {
			$ping3 .= '<div class="add-dev-one"><a href="/?do=house&act=add" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/ponhouse.png"></div></div><div class="add-name">'.$lang['add_pon_house'].'</div></div>';
		}
		$ping3 .= '<div class="add-dev-one"><a href="/?do=car&act=addcar" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/list_car.png"></div></div><div class="add-name">'.$lang['add_car'].'</div></div>';
		$tpl->load_template('add/sfp.tpl');
		$tpl->set('{ping3}',($ping3?:''));
		$tpl->compile('all-device');
		$tpl->clear();
	break;		
}
$metatags = array('title'=>$lang['pt_add'],'description'=>$lang['pd_add'],'page'=>'add');
$tpl->load_template('add/list.tpl');
$tpl->set('{all}',(isset($tpl->result['all-device'])?$tpl->result['all-device']:''));
$tpl->set('{url}',$config['url']);
$tpl->set('{div_message}',$div_message);
$tpl->set('{newdevice}',$lang['newdevice']);
$tpl->compile('block-add');
$tpl->clear();
$tpl->load_template('add/main.tpl');
$tpl->set('{result}',$tpl->result['block-add']);
$tpl->compile('content');
$tpl->clear();	
?>