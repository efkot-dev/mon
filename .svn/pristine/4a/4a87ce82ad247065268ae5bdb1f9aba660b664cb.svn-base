<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array('title'=>$lang['insector_functions'],'description'=>$lang['insector_functions'],'page'=>'nagios');
$sqlstikersonu = $db->Multi('onus', '*', ['inspector' => 2]);
if(isset($sqlstikersonu) && count($sqlstikersonu)>0){
	$tplRes ='<table class="resp-tab" width="100%"><thead><tr>
	<th width="5%">'.$lang['status'].'</th>
	<th width="10%">MAC Serial</th>
	<th width="10%">'.$lang['gilka'].'</th>
	<th width="6%">
		<span class="inf_signal">
			<span class="sig2">RX ONU</span>
		</span>
	</th>';
	if(isset($confPMon['ONU_RX_OLT_SIGNAL']) && !empty($confPMon['ONU_RX_OLT_SIGNAL']) && $confPMon['ONU_RX_OLT_SIGNAL']==1){		
	$tplRes .='
		<th width="6%">
			<span class="inf_signal">
				<span class="sig1">RX OLT</span>
			</span>
		</th>';
	}
		$tplRes .='<th width="6%">'.$lang['dists'].'</th>
	<th>Info</th>
	</tr></thead><tbody>';
	foreach($sqlstikersonu as $idonu => $ont){
		if($access->get('dev'.$ont['olt'])){
		$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
		$datatemponu = getFastOnusData($onukey);
		$tplRes .= '<tr>
			<td>
				<span class="statusonu st_'.$ont['status'].'"></span>
			</td>
			<td class="td_url">
				<a href="/?do=onu&id='.$ont['idonu'].'" '.($ont['status']==2?'class="colorgrey"':'').'>'.(isset($ont['mac'])?$ont['mac']:(isset($ont['sn'])?$ont['sn']:'n/a')).'</a>
			</td>
			<td class="td_url">
				<a href="/?do=onu&id='.$ont['idonu'].'" '.($ont['status']==2?'class="colorgrey"':'').'>'.$ont['type'].' '.$ont['inface'].'</a>
			</td>
			<td>
				'.($ont['status']==1?signalTerminal($ont['rx']):'n/a').'
			</td>';
			if(isset($confPMon['ONU_RX_OLT_SIGNAL']) && !empty($confPMon['ONU_RX_OLT_SIGNAL']) && $confPMon['ONU_RX_OLT_SIGNAL']==1){
				$tplRes .= '<td>
					'.($ont['status']==1 && !empty($ont['rxolt'])?signalTerminal($ont['rxolt']):'').'
				</td>';
			}
			// name
			$onu_name = (!empty($ont['name'])?'<span class="name-onu">'.$ont['name'].'</span>':'');
			// Tag		
			$tag = (!empty($datatemponu['tag'])?'<span class="terminaltag">'.$datatemponu['tag'].'</span>':'');
			// vendor
			$tplRes .= '
			<td class="dist mobile">'.($ont['dist'] ? metersToKilometers($ont['dist']) : '').'</td>
			
			<td class="description_name mobile_font stikers">
				'.($ont['status']==2?(!empty($ont['reason'])?'<span class="reason_'.$ont['reason'].'"></span>':''):'').'
				'.($ont['status']==2?(!empty($ont['offline'])?'<span class="off_">'.aftertime($ont['offline']).'</span> ':''):'').'
				'.$onu_name.' '.$tag.'
			</td>
		</tr>';
		}
	}
	$tplRes .='</tbody></table>';
}else{
	$go->redirect('main');		
}
$tpl->load_template('nagios.tpl');
$tpl->set('{result}',$tplRes);
$tpl->compile('content');
$tpl->clear();
?>