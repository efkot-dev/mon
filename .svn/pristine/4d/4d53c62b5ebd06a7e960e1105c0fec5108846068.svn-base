<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array('title'=>$lang['onu_offline'],'description'=>$lang['onu_offline'],'page'=>'onoffline');
$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
$masiv_switch = [];
$array_switch = [];
$urlswitch ='';
$tplresult = '';
	$sql_olt = $db->SimpleWhile("SELECT id, place, model, inf FROM switch WHERE device = 'olt' AND monitor = 'yes'");
	if(isset($sql_olt) && count($sql_olt)>0){
		if(isset($id)){
			$selectswitch = $id;	
		}
		foreach($sql_olt as $switch){
			$masiv_switch[$switch['id']] = [
				'swid' => $switch['id'],'place' => $switch['place'],'model' => $switch['inf'].' '.$switch['model']
			];
		}
	}
	$selectswitch = isset($selectswitch) ? $selectswitch : '';
	$where_onus = empty($selectswitch) ? "WHERE" : "WHERE olt = '$selectswitch' AND ";
	if(isset($act) && $act=='los'){
		$orderby = "(status = '2' AND offline >= CURDATE() AND (reason = 'err8' OR reason = 'err6')) ORDER BY offline DESC";
	}else{
		$orderby = "status = '2' AND offline >= CURDATE() ORDER BY offline DESC";
	}
	$sqltemponu = $db->SimpleWhile("SELECT * FROM onus $where_onus $orderby");
	$sqlonus = [];
	$array_reason = [];
	if(isset($sqltemponu) && count($sqltemponu) > 0){
		foreach($sqltemponu as $ontid => $ont){
			if($access->get('dev'.$ont['olt'])){
				$sqlonus[$ont['olt']]['ont'][$ont['idonu']] = $ont;
				if(!empty($ont['reason'])){
					$array_reason[$ont['reason']] = $ont['olt'];
				}
				$array_switch[$ont['olt']]['swid'] = isset($masiv_switch[$ont['olt']]['id']) ? $masiv_switch[$ont['olt']]['id'] : '';
				$array_switch[$ont['olt']]['place'] = isset($masiv_switch[$ont['olt']]['place']) ? $masiv_switch[$ont['olt']]['place'] : '';
			}
		}
	}
	if(isset($array_switch) && count($array_switch) > 0){
		$tplresult .= '<div id="dev-loc">';
		if(count($array_switch)==1){
			$tplresult .='<a href="/?do=onuoffline" class="urlelelement"><i class="fi fi-rr-caret-left"></i>'.$lang['view_all'].'</a>';
		}
		foreach($array_switch as $oltid => $device){
			$tplresult .= '<a href="/?do=onuoffline&id='.$oltid.'" class="urlelelement">'.$device['place'].' <span class="count">'.count($sqlonus[$oltid]['ont']).'</span></a>';
		}
		$tplresult .= '</div>';
	}
	if(isset($sqlonus) && count($sqlonus) > 0){
		$tplresult .= '<table class="resp-tab"><thead><tr>
			<th width="3%">'.$lang['status'].'</th>
			<th>MAC Serial</th>
			<th width="10%">'.$lang['gilka'].'</th>
			<th width="6%">'.$lang['dists'].'</th>
			<th width="10%">'.$lang['offline'].'</th>
			<th width="10%">'.$lang['offline'].'</th>
			<th width="6%">
				<span class="inf_signal">
					<span class="sig2">RX ONU</span>
				</span>
			</th>
			<th width="7%">
				<span class="inf_signal">
					<span class="sig1">'.$lang['last_signal'].'</span>
				</span>
			</th>
			<th>'.$lang['reason'].'</th>
			</tr></thead><tbody>';
		foreach($sqlonus as $oltid => $olt){
			$tplresult .= '<td colspan="10" class="reason_td"> 
					<span class="model">'.$masiv_switch[$oltid]['model'].'</span>
					<a href="/?do=onulist&id='.$masiv_switch[$oltid]['swid'].'">'.(isset($masiv_switch[$oltid]['place'])?$masiv_switch[$oltid]['place']:'').'</a>
					<span class="count_onu">'.count($olt['ont']).'</span>
				</td>';
			foreach($olt['ont'] as $ontid => $onu){
				$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:'111'));
				$status = (isset($onu['reason']) && ($onu['reason'] == 'err8' || $onu['reason'] == 'err6') ? 2:3);
				$onuname = (!empty($onu['name'])? '<span class="bad_name_onu">'.$onu['name'].'</span>':'');
				$tplresult .= '<tr>
				<td>
					<span id="'.$onu['reason'].'" class="statusonu st_'.$status.'"</span>
				</td>
				<td class="td_url">
					<a href="/?do=onu&id='.$onu['idonu'].'">'.$onukey.' '.$onuname.'</a>
				</td>
				<td class="td_url">
					<font color="#0f73c3">'.strtoupper($onu['type']).' '.$onu['inface'].'
				</td>
				<td class="dist mobile">'.($onu['dist'] ? metersToKilometers($onu['dist']) : '').'</td>	
				<td>
					<span class="off_">'.$onu['offline'].'</span>
				</td>				
				<td>
					<font color="#222"><b>'.aftertime($onu['offline']).'</b></font>
				</td>				
				<td>
					'.(!empty($onu['rx'])?signalTerminal($onu['rx']):'N/A').'
				</td>
				<td>
					'.(!empty($onu['lastrx'])?signalTerminal($onu['lastrx']):'N/A').'
				</td>
				<td>
					'.(isset($onu['reason']) ? '<span class="reason_">'.$lang[$onu['reason']].'</span>':'').'
				</td>
				';
				$tplresult .= '</tr>';
			}
		}
		$tplresult .= '</table>';
	}	
if(isset($act) && $act=='los'){
	$speedbar_main = $lang['onu_los_24_7'];	
}else{
	$speedbar_main = $lang['onu_offline_24_7'];
}
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$speedbar_main.'</span></div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>