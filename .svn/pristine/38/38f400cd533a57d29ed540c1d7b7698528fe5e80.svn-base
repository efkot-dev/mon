<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('sklad')) 
	$go->redirect('main');
$act = ($act ? $act : 'sfp');
switch($act){
	case 'device';
		$SQLCount = $db->Multi('sklad_device');
		$page = '&';
		$head = 'ТМЦ Активне мережеве обладнання';
		list($pagertop,$pagerbottom,$limit,$offset) = pager($config['countviewpageonu'],count($SQLCount),'////url');
		$sql_list = $db->Multi('sklad_device','*',null,null,$offset,$limit);
		if(count($sql_list)>0){
			foreach($sql_list as $device){
				$tpl->load_template('sklad/list-device.tpl');
				$tpl->set('{id}',$device['id']);
				$tpl->set('{install}',skladInstallStatus($device['install']));
				$tpl->set('{model}',$device['model']);
				$tpl->set('{ip}',($device['ip']?'<span class="style1">IP</span>'.$device['ip']:''));
				$tpl->set('{sn}',($device['sn']?'<span class="style1">SN</span>'.$device['sn']:''));
				$tpl->set('{mac}',($device['mac']?'<span class="style1">MAC</span>'.$device['mac']:''));
				$tpl->set('{added}',$device['added']);
				$tpl->compile('sklad');
				$tpl->clear();			
			}
		}else{
			
		}
	break;	
	case 'switch';
		$SQLCount = $db->Multi('sklad_switch');
		$page = '&';
		$head = 'ТМЦ Пасивне мережеве обладнання';
		list($pagertop,$pagerbottom,$limit,$offset) = pager($config['countviewpageonu'],count($SQLCount),'////url');
		$sql_list = $db->Multi('sklad_switch','*',null,null,$offset,$limit);
		if(count($sql_list)){
			foreach($sql_list as $device){
				$tpl->load_template('sklad/list-switch.tpl');
				$tpl->set('{id}',$device['id']);
				$tpl->set('{install}',skladInstallStatus($device['install']));
				$tpl->set('{model}',$device['model']);
				$tpl->set('{port}',($device['port']?'<span class="style1">Портів</span>'.$device['port']:''));
				$tpl->set('{sn}',($device['sn']?'<span class="style1">SN</span>'.$device['sn']:''));
				$tpl->set('{added}',$device['added']);
				$tpl->compile('sklad');
				$tpl->clear();				
			}
		}else{
			
		}
	break;	
	case 'sfp';
		$SQLCount = $db->Multi('sfp');
		$page = '&';
		$head = 'Список SFP';
		list($pagertop,$pagerbottom,$limit,$offset) = pager($config['countviewpageonu'],count($SQLCount),'////url');
		$sql_list = $db->Multi('sfp','*',null,null,$offset,$limit);
		if(count($sql_list)){
			foreach($sql_list as $device){
				$tpl->load_template('sklad/list-sfp.tpl');
				$tpl->set('{id}',$device['id']);
				$tpl->set('{model}',($device['model']?$device['model']:'noName'));
				$tpl->set('{dist}',($device['dist']?'<span class="style1">Дистанція</span>'.$device['dist'].'km':''));
				$tpl->set('{wavelength}',($device['wavelength']?'<span class="style1">Частота:</span>'.$device['wavelength']:''));
				$tpl->set('{speed}',($device['speed']?'<span class="style1">Швидкість:</span>'.$device['speed'].'G':''));
				$tpl->set('{types}',($device['types']?'<span class="style1">Тип волокна:</span>'.$device['types']:''));
				$tpl->set('{connector}',($device['connector']?'<span class="style1">Тип конектора:</span>'.$device['connector']:''));
				$tpl->set('{added}',(!empty($device['added']) ? $device['added'] : ''));
				$tpl->compile('sklad');
				$tpl->clear();				
			}
		}else{
			
		}
	break;
}
$navigation ='<div class="navigation">';
#$navigation .='<a class="pageadd" href="/?do=add">Додати новий</a>';
#$navigation .='<a class="add '.($act=='device'?'active':'').'" href="/?do=sklad&act=device'.$page.'">Активне</a>';
#$navigation .='<a class="add '.($act=='switch'?'active':'').'" href="/?do=sklad&act=switch'.$page.'">Пасивне</a>';
#$navigation .='<a class="add '.($act=='ups'?'active':'').'" href="/?do=sklad&act=ups'.$page.'">ДБЖ</a>';
#$navigation .='<a class="add '.($act=='battery'?'active':'').'" href="/?do=sklad&act=battery'.$page.'">Акамулятори</a>';
#$navigation .='<a class="add '.($act=='sfp'?'active':'').'" href="/?do=sklad&act=sfp'.$page.'">SFP</a>';
#$navigation .='<div class="sklad_search"><input name="search" class="input1" placeholder="model, sn, mac, ip" type="text" value=""></div>';
$navigation .='</div>';
$metatags = array('title'=>$head,'description'=>$head,'page'=>'sklad');
$tpl->load_template('sklad/main.tpl');
$tpl->set('{name}',$head);
$tpl->set('{navigation}',$navigation);
$tpl->set('{result}',$tpl->result['sklad']);
$tpl->set('{pagerbottom}',$pagertop);
$tpl->compile('content');
$tpl->clear();
?>