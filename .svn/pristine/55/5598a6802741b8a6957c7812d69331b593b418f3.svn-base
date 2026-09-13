<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if($page=='connect' && $dataSwitch['connect']=='yes' && $access->get('connectport')){
	$sql_port_data = $db->Multi('switch_port','*',['deviceid'=>$id]);
	if(isset($sql_port_data) && count($sql_port_data)>0){
		$allowedIds = array(1,2,12,15);
		if (in_array($dataSwitch['oidid'], $allowedIds)) {
			$oidid_support = true;
		}			
		$tplRes .='
		<div class="dashboard_pon mb20">
			<a href="#" class="snmp_pon" onclick="checker_port_status('.$id.',2)">'.$lang['statport'].'</a>
		</div>';
		$list_pon = getAllPortDevice($sql_port_data);
		foreach($list_pon as $PortData => $resData){
			$tplRes .='
			<table class="tableport" border="0" cellspacing="0" cellpadding="10" style="width:100%;"><tr><td class="name-port" colspan="5">'.$PortData.'';
			$tplRes .='</td></tr></table>';
			$tplRes .='<table id="sb" style="width:100%;" class="tableport" border="0" cellspacing="0" cellpadding="10" style="width: 100%;">';
			foreach($resData as $portID => $port){
				$tplRes .='<tr class="hover">
				<td data-llid="'.$port['llid'].'" id="status-'.$id.'-'.$port['id'].'" class="td0 status-'.$port['operstatus'].' '.($port['operstatus']=='up'?'blink_status':'').'">
				'.$port['operstatus'].'<div class="pmon_new"></div>
				</td>';
				if(isset($oidid_support)) {
					if (strpos((string)$port['typeport'], 'pon') !== false || strpos((string)$port['typeport'], 'sfp') !== false){
						if($access->get('shutdown_port')){
							$tplRes .='<td class="td0 port_panel">
								'.($port['lock']=='no' ? 
									'<div class="load-port" id="inface-'.$id.'-'.$port['id'].'"></div>' :
									'<div class="lock-port"><img src="../style/img/lock_port.png"></div>').'
							</td>';
						}
					}
				}
				$tplRes .='<td class="td1 name">';
				$tplRes .='<h2>'.$port['name'].'</h2>';
				$tplRes .= '<div class="descr">';
				if (!empty($port['descr'])) {
					if ($access->get('note_port')) {
						$tplRes .= '<span class="add" onclick="port(\'edit\',' . $port['id'] . ')">' . $lang['edit_descr'] . '</span>';
					}
					$tplRes .= '<h3>' . $port['descr'] . '</h3>';
				} else {
					if ($access->get('note_port')) {
						$tplRes .= '<span class="add" onclick="port(\'descr\',' . $port['id'] . ')">' . $lang['add_descr'] . '</span>';
					}
				}
				if($dataSwitch['oidid']==1 && $access->get('note_port')){
					$tplRes .= '<span class="add" onclick="port(\'bdcomeponportedit\',' . $port['id'] . ')">' . $lang['edit_descr'] . ' OLT</span>';
				}
				$tplRes .= '</div>';
				$tplRes .='</td>
				<td class="td2">';
				if (preg_match("/PON/i",$port['typeport']) || preg_match("/pon/i",$port['typeport'])) {
					$PonInf = getPonPortOLt($id,$port['llid']);
					if(!empty($PonInf['support'])){
						$tplRes .= statsPonPort($PonInf,$port['typeport']);
					}
				}
				$tplRes .='</td>';				
				$tplRes .='<td class="tdempty" data-inf="'.$port['typeport'].'">';
					if($port['typeport']=='port' || $port['typeport']=='pon' || $port['typeport']=='xgei' || $port['typeport']=='ge' || $port['typeport']=='xge' 
					|| $port['typeport']=='sfp' || $port['typeport']=='gepon' || $port['typeport']=='ethernet' || $port['typeport']=='gpon' 
					|| $port['typeport']=='epon' || $port['typeport']=='gei'|| $port['typeport']=='mikrotik'){
						#$tplRes .='<div id="port-control-'.$portID.'"></div>';
						if($access->get('bandwidth_monitor')){
							$todaydays = date('d.m');
							$bandwidth_monitor = $db->Simple("SELECT id FROM traff_monitor WHERE deviceid = '{$port['deviceid']}' AND llid = '{$port['llid']}' LIMIT 1");
							if(!empty($bandwidth_monitor['id'])){
								$tplRes .= '<a class="taff" href="/?do=bandwidth&act=view&id='.$bandwidth_monitor['id'].'&d='.$todaydays.'"><img src="../style/img/web-traffic.png">'.$lang['load_statistics'].'</a>';
							}else{
								$tplRes .= '<a href="#" class="taff" onclick="funbandwidth(\''.$port['id'].'\',\'active\',\''.$port['typeport'].'\')"><img src="../style/img/traffic-light.png">'.$lang['monitor_traffic'].'</a>';
							}
						}
					}
				$tplRes .='</td>';
				
				$tplRes .='<td class="td3">';
				if($port['error']=='yes'){
					$tplRes .= tplErrorPort($port['deviceid'],$port['llid']);
				}
				$tplRes .='</td>';
				$tplRes .='</tr>';
			}
			$tplRes .='</table>';
			$tplRes .='
	<script>
		get_object_port();
	</script>	
	';	
		}
	}else{
		$tplRes .='<span id="infEmpty">'.$lang['empty'].'</span>';
	}
}
if($page=='connect' && $dataSwitch['connect']=='yes' && $access->get('connectport')){
	$addjs .= 'get_port_control('.$id.');';
}
?>