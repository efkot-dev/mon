<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$result = '';
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if(!$id){
	$go->redirect('main');	
}
$dataswitch = $db->Fast('switch','*',['id'=>$id]);
if(!$dataswitch['id']){
	$go->redirect('main');	
}
$result .= '<div class="unit-switch"><div class="portswitch" id="switch">';
$sqlall = $db->Multi('switch_port','*',['deviceid'=>$dataswitch['id']]);
$resultdata = getAllPortDevice($sqlall);	
if(is_array($resultdata)){
	foreach($resultdata as $portnametype => $listsPort){
		if($portnametype=='port' || $portnametype=='gigaethernet' || $portnametype=='tgigaethernet' || $portnametype=='gpon' || $portnametype=='epon'){
			$result .='<div class="portswitchsfp '.$portnametype.'">';
			foreach($listsPort as $portid => $valuePort){
				$result .='<div class="sfpswitch" id="port-'.$valuePort['id'].'">';
				$result .='<span><img src="../style/img/unit/'.$portnametype.'_'.$valuePort['operstatus'].'.png">';
				#$list .='<div class="numberport">'.$valuePort['idport'].'</div>';
				$result .=''.($valuePort['operstatus']=='up'?'<div class="sfpswitchup"></div>':'').'</span>';
				$result .='<h3>'.$valuePort['name'].'</h3>';
				$result .='</div>';
			}
		$result .= '</div>';
		}
	}
}
$result .= '</div></div>';
$metatags = array('title'=>$lang['portmon'],'description'=>$lang['portmon'],'page'=>'statusport');
$tpl->load_template('ponport.tpl');
$tpl->set('{id}',$dataswitch['id']);
$tpl->set('{port}','PON');
$tpl->set('{name}',$dataswitch['place']);
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
?>