<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$checkLicenseSwitch = getSwitchAll();
$metatags = array('title'=>$lang['page_title_stats'],'description'=>$lang['page_title_descr'],'page'=>'changesignal');
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
			if($access->get('dev'.$switch['id'])){
			$masiv_switch[$switch['id']]['swid'] = $switch['id'];
			$masiv_switch[$switch['id']]['place'] = $switch['place'];
			$masiv_switch[$switch['id']]['model'] = $switch['model'].''.$switch['inf'];
			}
		}
	}
	if(isset($selectswitch)){
		$where_onus = "WHERE olt = '".$selectswitch."'";
		#$orderby = "AND status = '1' AND rx BETWEEN '-".$config['badsignalstart']."' AND '-".$config['badsignalend']."' AND rxstatus = 'up' AND changerx  >= CURDATE()";
		$orderby = "AND changerx  >= CURDATE()";
	}else{
		$where_onus = "WHERE ";
		#$orderby = "WHERE status = '1' AND rx BETWEEN '-".$config['badsignalstart']."' AND '-".$config['badsignalend']."' AND rxstatus = 'up' AND changerx  >= CURDATE()";
		$orderby = " changerx >= CURDATE()";
	}
	$sql ="SELECT * FROM onus $where_onus $orderby";
	$sqltemponu = $db->SimpleWhile($sql);
	$sqlonus = [];
	if(isset($sqltemponu) && count($sqltemponu) > 0){
		foreach($sqltemponu as $ontid => $ont){
			if($access->get('dev'.$ont['olt'])){
				$sqlonus[$ont['olt']]['ont'][$ont['idonu']] = $ont;
				$array_switch[$ont['olt']]['swid'] = (isset($masiv_switch[$ont['olt']]['id'])?$masiv_switch[$ont['olt']]['id']:'');
				$array_switch[$ont['olt']]['place'] = (isset($masiv_switch[$ont['olt']]['place'])?$masiv_switch[$ont['olt']]['place']:'');
			}
		}
	}
	if(isset($array_switch) && count($array_switch) > 0){
		$tplresult .= '<div id="dev-loc">';
		if(count($array_switch)==1){
			$tplresult .='<a href="/?do=changesignal" class="urlelelement"><i class="fi fi-rr-caret-left"></i>'.$lang['view_all'].'</a>';
		}
		foreach($array_switch as $oltid => $device){
			$tplresult .= '<a href="/?do=changesignal&id='.$oltid.'" class="urlelelement">'.$device['place'].' <span class="count">'.count($sqlonus[$oltid]['ont']).'</span></a>';
		}
		$tplresult .= '</div>';
	}
	if(isset($sqlonus) && count($sqlonus) > 0){
		$tplresult .= '<table class="resp-tab"><thead><tr>
			<th width="5%">'.$lang['status'].'</th>
			<th>Mac_SN</th>
			<th width="10%">'.$lang['inface'].'</th>
			<th width="7%">'.$lang['dist'].'</th>
			<th width="7%">'.$lang['signal'].'</th>
			<th width="10%">'.$lang['change_time'].'</th>
			<th width="7%">'.$lang['last_signal'].'</th>
			<th class="mobile" width="15%">
				<span class="inf_status">
					<span class="tim1">'.$lang['online'].'</span>
					<span class="tim2">'.$lang['offline'].'</span>
				</span>
			</th>
			</tr></thead><tbody>';
		foreach($sqlonus as $oltid => $olt){
			$tplresult .= '<td colspan="10"  class="td_url"> <a href="/?do=changesignal&id='.$masiv_switch[$oltid]['swid'].'">'.(isset($masiv_switch[$oltid]['place'])?$masiv_switch[$oltid]['place']:'').'</a></td>';
			foreach($olt['ont'] as $ontid => $onu){
				$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:'111'));
				$tplresult .= '<tr>
				<td><span class="statusonu st_'.$onu['status'].'"</span></td>
				<td class="td_url"><a href="/?do=onu&id='.$onu['idonu'].'">'.($onu['status']==2?'<font color="grey">':'').''.$onukey.'</a></td>
				<td class="td_url"><font color="grey">'.strtoupper($onu['type']).' '.$onu['inface'].'</td>
				<td class="td_url">'.$onu['dist'].'</td>
				<td>'.($onu['status']==2?'N/A':signalTerminal($onu['rx'])).'</td>
				<td><font color="red">'.aftertime($onu['changerx']).'</font></td>
				<td>'.($onu['status']==2?'N/A':signalTerminal($onu['lastrx'])).'</td>';
				$tplresult .= '<td class="mobile">';
						if($onu['status']==1){
							$tplresult .= '<span class="on_">'.aftertime($onu['online']).'</span>';
						}else{
							$tplresult .= '<span class="off_">'.aftertime($onu['offline']).'</span>';
						}
					$tplresult .= '</td>';
				$tplresult .= '</tr>';
			}
		}
		$tplresult .= '</table>';
	}	
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['titlecriticsignalonu_24_7'].'</span></div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>