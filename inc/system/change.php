<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$listgroup = '';
if($access->get('setupdevice')){
	$metatags = array('title'=>'Changing switch templates','description'=>'Changing switch templates','page'=>'change');
	$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
	if(!$id){
		$go->redirect('main');
		exit;
	}		
	$dataSwitch = $db->Fast('switch','*',['id'=>$id]);
	if(!$dataSwitch['id']){
		$go->redirect('main');
		exit;		
	}
	$setup = '<form action="/?do=send" method="post" id="formadd">
	<input name="act" type="hidden" value="changemodel">
	<input name="id" type="hidden" value="'.$dataSwitch['id'].'">';
	$getType = null;
	$listDevice = getListDevice($getType);
	$pole_device = '';
	if(isset($listDevice) && count($listDevice)>0){
		$pole_device .= '<select class="select" name="deviceid" id="device">';
		foreach($listDevice as $Device)
			$pole_device .= '<option value="'.$Device['id'].'">'.$Device['name'].' '.$Device['model'].'</option>';
		$pole_device .='</select>';
	}
	$setup .= formpage(['img'=>'addconnect.png','name'=>$lang['inputnamedevice'],'descr'=>$lang['curname'],'pole'=>'<input types name="place" class="input1" type="text" value="'.$dataSwitch['place'].'">']);
	$setup .= formpage(['img'=>'sfp-port.png','name'=>'The current template',
		'descr'=>'The current template for the switch','pole'=>"<div class=\"style_model\"><span>".$dataSwitch['inf']."</span>".$dataSwitch['model']."</div> "]);	
	$setup .= formpage(['img'=>'img1.png','name'=>$lang['ip'],'descr'=>$lang['ipdescr'],'pole'=>'<input types name="netip" class="input1" type="text" value="'.$dataSwitch['netip'].'">']);
	$setup .= formpage(['img'=>'rotate.png','name'=>'List of switch templates',
		'descr'=>'The switch model that will be applied to the switch','pole'=>$pole_device]);
	$setup .= '<div class="warning_info_device">All switch data in PMon will be deleted. List of ports, List of Ont, History of signal logs</div>';
	$setup .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form>';
	$tpl->load_template('setup/main.tpl');
	$tpl->set('{langsetup}','Changing switch templates');
	$tpl->set('{id}',$id);
	$tpl->set('{place}',$dataSwitch['place']);
	$tpl->set('{langlist}',$lang['alldevice']);
	$tpl->set('{result}',$setup);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');
}
?>