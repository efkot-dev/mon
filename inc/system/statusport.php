<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$tplresult = '';
if(!$access->get('statusport')) 
	$go->redirect('main');
$metatags = array('title'=>$lang['portmon'],'description'=>$lang['portmon'],'page'=>'statusport');
$sqlportdevice = $db->Multi('switch_port','*',['monitor'=>'yes','operstatus'=>'down']);
$sqllistdeive = ListSwitchMonitor();
if(count($sqlportdevice)){
	foreach($sqlportdevice as $idmonitorport => $monitorportdata){
		if(!empty($sqllistdeive[$monitorportdata['deviceid']]['place'])){
		$tplresult .='<div class="port-status status-port-'.$monitorportdata['operstatus'].'">';
		$tplresult .='<div class="port-status-name"><a href="/?do=detail&act=olt&id='.$monitorportdata['deviceid'].'">'.$sqllistdeive[$monitorportdata['deviceid']]['place'].' -> '.$monitorportdata['nameport'].'</a></div>';
		$tplresult .='<div class="port-status-time"><b>'.$lang['offef'].'</b>: '.aftertime($monitorportdata['timedown']).'</div>';
		$tplresult .='</div>';
		}
	}
}else{
	$tplresult .= '';
}
$result ='<div class="mainblocklite"><div class="monitor-list-head"><h2><i class="fi fi-rr-clock"></i>'.$lang['monitorport'].'</h2></div><div class="list_pon_main">'.$tplresult.'</div></div>';
$tpl->load_template('page-status-port.tpl');
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
?>