<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$checkLicenseSwitch = getSwitchAll();
$metatags = array('title'=>$lang['onu_offline'],'description'=>$lang['onu_offline'],'page'=>'onoffline');
$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
$masiv_switch = [];
$array_switch = [];
$urlswitch ='';
$tplresult = '';
	if(count($checkLicenseSwitch)>0){
		if(isset($id)){
			$selectswitch = $id;	
		}
		foreach($checkLicenseSwitch as $switch){
			$masiv_switch[$switch['id']] = [
				'swid' => $switch['id'],'place' => $switch['place'],'model' => $switch['model'] . $switch['inf']
			];
		}
	}
	$selectswitch = isset($selectswitch) ? $selectswitch : '';
	$where_onus = empty($selectswitch) ? "WHERE" : "WHERE olt = '$selectswitch' AND ";
	if(isset($act) && $act=='los'){
		$orderby = "(status = '2' AND (reason = 'err8' OR reason = 'err6')) ORDER BY offline ASC";
	}else{
		$orderby = "status = '2' ORDER BY offline ASC";
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
				$array_switch[$ont['olt']]['url'] = '<a href="/?do=onulist&id='.$masiv_switch[$ont['olt']]['swid'].'" class="urlelelement">'.$masiv_switch[$ont['olt']]['place'].'</a>';
			}
		}
	}
	if(isset($array_switch) && count($array_switch) > 0){
		$tplresult .= '<div id="dev-loc">';
		if(count($array_switch)==1){
			$tplresult .='<a href="/?do=onulist'.(isset($act) && $act=='los'?'&act=los':'').'" class="urlelelement"><i class="fi fi-rr-caret-left"></i>'.$lang['view_all'].'</a>';
		}
		foreach($array_switch as $oltid => $device){
			$tplresult .= '<a href="/?do=onulist&id='.$oltid.(isset($act) && $act=='los'?'&act=los':'').'" class="urlelelement">'.$device['place'].' <span class="count">'.count($sqlonus[$oltid]['ont']).'</span></a>';
		}
		$tplresult .= '</div>';
	}
	if(isset($sqlonus) && count($sqlonus) > 0){
		$tplresult .= '<table class="resp-tab"><thead><tr>
			<th width="3%">'.$lang['status'].'</th>
			<th>Mac_SN</th>
			<th width="10%">'.$lang['inface'].'</th>
			<th width="10%">'.$lang['dist'].'</th>
			<th width="15%">'.$lang['reason'].'</th>	
			<th width="10%">'.$lang['offline'].'</th>			
			<th width="10%">'.$lang['offline'].'</th>			
			<th width="7%">'.$lang['signal'].'</th>
			<th width="7%">'.$lang['last_signal'].'</th>
			
			</tr></thead><tbody>';
		foreach($sqlonus as $oltid => $olt){
			$tplresult .= '<td colspan="10"  class="td_url"> <a href="/?do=onulist&id='.$masiv_switch[$oltid]['swid'].'">'.(isset($masiv_switch[$oltid]['place'])?$masiv_switch[$oltid]['place']:'').' onu:'.count($olt['ont']).'</a></td>';
			foreach($olt['ont'] as $ontid => $onu){
				$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:'111'));
				$status = (isset($onu['reason']) && ($onu['reason'] == 'err8' || $onu['reason'] == 'err6') ? 2:3);
				$tplresult .= '<tr>
				<td><span id="'.$onu['reason'].'" class="statusonu st_'.$status.'"</span></td>
				<td class="td_url"><a href="/?do=onu&id='.$onu['idonu'].'">'.$onukey.'</a></td>
				<td class="td_url"><font color="#0f73c3">'.strtoupper($onu['type']).' '.$onu['inface'].'</td>
				<td class="td_url">'.$onu['dist'].'</td>
				<td>
				'.(isset($onu['reason']) ? $lang[$onu['reason']]:'N/A').'
				</td>
				<td><font color="tomato">'.$onu['offline'].'</font></td>				
				<td><font color="#222"><b>'.aftertime($onu['offline']).'</b></font></td>				
				<td>'.(!empty($onu['rx'])?signalTerminal($onu['rx']):'N/A').'</td>
				<td>'.(!empty($onu['lastrx'])?signalTerminal($onu['lastrx']):'N/A').'</td>
				';
				$tplresult .= '</tr>';
			}
		}
		$tplresult .= '</table>';
	}	
if(isset($act) && $act=='los'){
	$speedbar_main = $lang['onu_los'];	
}else{
	$speedbar_main = $lang['onu_offline'];
}
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$speedbar_main.'</span></div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>