<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if($dataSwitch['monitor']=='yes' && $access->get('monitordevice')){
	$dataPortSwitch = $db->Multi('switch_port','*',['deviceid'=>$dataSwitch['id']]);
	if(isset($dataPortSwitch) && count($dataPortSwitch)>0){
		$tplRes .='<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveportmonitor"><input name="id" type="hidden" value="'.$dataSwitch['id'].'"><div class="monitor-port-ajax"><div class="monitor-port-name portimg1">'.$lang['monport'].'<p>'.$lang['monportdescr'].'</p></div><div class="monitor-port-input">';
		foreach($dataPortSwitch as $PortData){
			$tplRes .='<div class="port '.($PortData['monitor']=='yes'?'selectport':'').'"><input class="checkcss" name="monitorport[]" value="'.$PortData['id'].'" type="checkbox" '.($PortData['monitor']=='yes'?'checked':'').'><b>'.$PortData['nameport'].'</b></div>';
		}
		$tplRes .='</div><div class="monitor-port-name portimg2">'.$lang['indescrerr'].'<p>'.$lang['indescrerr'].'</p></div><div class="monitor-port-input">';
		foreach($dataPortSwitch as $PortData){
			$tplRes .='<div class="port '.($PortData['error']=='yes'?'selectport':'').'"><input class="checkcss" name="monitorerr[]" value="'.$PortData['id'].'" type="checkbox" '.($PortData['error']=='yes'?'checked':'').'><b>'.$PortData['nameport'].'</b></div>';
		}
		$tplRes .='</div>';
		if($config['telegram']=='on'){
			$tplRes .='<div class="monitor-port-name portimg3">'.$lang['sennametg'].'<p>'.$lang['sennametgdescr'].'</p></div><div class="monitor-port-input">';
			foreach($dataPortSwitch as $PortData){
				$tplRes .='<div class="port '.($PortData['sms']=='yes'?'selectport':'').'"><input class="checkcss" name="monitortelegram[]" value="'.$PortData['id'].'" type="checkbox" '.($PortData['sms']=='yes'?'checked':'').'><b>'.$PortData['nameport'].'</b></div>';
			}
			$tplRes .='</div>';
		}
		$tplRes .='</div><button type="submit" form="formadd" class="monitorsave" value="submit">'.$lang['save'].'</button></form>';
	}
}
?>