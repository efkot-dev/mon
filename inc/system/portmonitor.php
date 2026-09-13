<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array('title'=>'pormonitor','description'=>'pormonitor','page'=>'pormonitor');
$tplresult = '';
$sql_port = [
	'sql' => "SELECT * FROM switch_port WHERE monitor = 'yes'",'type' => 'while','uniq' => 'deviceid','key' => 'switch_port_monitor','time' => 120
];
$monitor_port = sql__($sql_port);
if(isset($monitor_port) && count($monitor_port)>0){
	$tplresult .= '<div class="page_monitor_page">';
	foreach($monitor_port as $id => $tmp_port){	
		$tplresult .= '<div class="page_monitor">';	
		$mon_switch = $db->Simple("SELECT place,model,inf,id FROM switch WHERE id = '{$id}'");
		$tplresult .= '<div class="page_monitor_head">
			<div class="main-switch-name">
				<span class="m">'.$mon_switch['inf'].'</span>
				<span class="s">'.$mon_switch['model'].'</span>
				<span class="n">'.$mon_switch['place'].'</span>	
				<a href="#" onclick="updateport('.$mon_switch['id'].', event)" class="checker"><img src="../style/img/refresh.png">Update</a>
			</div>		
		</div>';
		$tplresult .= '<div class="page_monitor_list" id="port-device-'.$mon_switch['id'].'">';
		foreach($tmp_port as $idport => $port){
			$tplresult .= '<div class="page_monitor_port">';
			if($port['operstatus']=='up'){
				$tplresult .= '<div class="page_monitor_port_time_up">'.aftertime($port['timeup']).'</div>';
			}else{
				$tplresult .= '<div class="page_monitor_port_time_down">'.aftertime($port['timedown']).'</div>';	
			}
			$tplresult .= '<div class="page_monitor_port_status"><img src="../style/img/'.($port['operstatus']=='down'?'zte0.png':'zte6.png').'"></div>';
			$tplresult .= '<div class="page_monitor_port_name">'.$port['nameport'].'</div>';
			$tplresult .= '<div class="page_monitor_port_description">'.$port['descrport'].'</div>';
			$tplresult .= '</div>';
		}
		$tplresult .= '</div>';		
		$tplresult .= '</div>';		
	}
	$tplresult .= '</div>';
}

$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Port monitor</span></div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$result);
$tpl->compile('content');
$tpl->clear();
?>