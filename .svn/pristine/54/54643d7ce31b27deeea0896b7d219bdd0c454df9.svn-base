<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$select_olt = '';
$checkLicenseSwitch = getSwitchAll();
$metatags = array('title'=>$lang['onu_offline'],'description'=>$lang['onu_offline'],'page'=>'onoffline');
$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
$signal = (isset($_GET['signal']) ? Clean::int($_GET['signal']) : $config['badsignalstart']);
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
	if (isset($id)) {
		$select_olt = "olt = '".$id."' AND ";
	} else {
		$select_olt = "";
	}
	$and_access = "AND (a.uid IS NOT NULL OR idonu IS NULL)";
	$access_sql = "LEFT JOIN checkaccess a ON CONCAT('dev', olt) = a.types AND a.uid = '{$USER['id']}'";
	$where_data = "WHERE {$select_olt} status = '1' 
		AND rx IS NOT NULL 
		AND rx != '' 
		AND rx != '0' 
		AND rx BETWEEN '-".($signal).".99' AND '-".($signal).".00' ";
	$sqlorderby = 'ORDER BY CAST(rx AS DECIMAL(10, 2)) ASC';
	$sql_zaput_v_bazy = "SELECT * FROM onus 
		{$access_sql}
		{$where_data}
		{$and_access}
		{$sqlorderby}";
	$sqltemponu = $db->SimpleWhile($sql_zaput_v_bazy);
	$sqlonus = [];
	$array_reason = [];
	if(isset($sqltemponu) && count($sqltemponu) > 0){
		foreach($sqltemponu as $ontid => $ont){
			if($access->get('dev'.$ont['olt'])){
				$sqlonus[$ont['olt']]['ont'][$ont['idonu']] = $ont;
				$array_switch[$ont['olt']] = [
					'swid'  => $masiv_switch[$ont['olt']]['id'] ?? 0,'place' => $masiv_switch[$ont['olt']]['place'] ?? '',
				];
			}
		}
	}
	$rx_and_access = "AND (a.uid IS NOT NULL OR idonu IS NULL)";
	$rx_access_sql = "LEFT JOIN checkaccess a ON CONCAT('dev', olt) = a.types AND a.uid = '{$USER['id']}'";
	$rx_sql_data = "SELECT rx, status FROM onus {$access_sql} WHERE status = '1' {$and_access}";
	$getallrx = $db->SimpleWhile($rx_sql_data);
	if(count($getallrx)>5){
		$signals = array();
		$counts = array();
		foreach($getallrx as $item) {
			$rx = str_replace("-","",intval($item['rx']));
			if ($rx != 0 && $item['status'] == 1) {
				if (array_key_exists($rx,$signals)) {
					$counts[$rx]++;
				} else {
					$signals[$rx] = $item;
					$counts[$rx] = 1;
				}
			}
		}
		if(is_array($signals)){
			if(count($counts)>=5){
			$count = count($counts);
			if ($count <= 5) {
				$with =  intval(30/count($counts));
			} elseif ($count > 5 && $count < 10) {
				$with =  intval(40/count($counts));		
			} elseif ($count >= 10 && $count <= 14) {
				$with =  intval(60/count($counts));		
			} else {
				$with = intval(100 / $count);
			}
		$maxvalue = max($counts);
		ksort($signals);
		$tplresult .= '<div class="classh listbad">';
		foreach ($signals as $rxs => $signa) {
			if($rxs>5){
				$tplresult .= '<div class="load-onu-stats" style="width: '.$with.'%;" onclick="badsignal(\''. $rxs.'\');">';
				$tplresult .= '<div class="sig" '.($signal==$rxs?'style="color:#fff;background:red;"':'').'>-'. $rxs.'</div><div class="load-onu"><div class="load-sig ';
				$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
				$maxbad = (!empty($config['badsignalend'])?$config['badsignalend']:31);
				if ($rxs < 15) {
					$tplresult .= 'color_sig_5';
				}elseif ($rxs < 18 && $rxs < $minbad) {
					$tplresult .= 'color_sig_4';
				}elseif ($rxs < $minbad) {
					$tplresult .= 'color_sig_1';
				} elseif ($rxs >= $minbad && $rxs < $maxbad) {
					$tplresult .= 'color_sig_2';
				} else {
					$tplresult .= 'color_sig_3';
				}
				$tplresult .= '" style="height:'.get_heght($counts[$rxs],$maxvalue).'%;"></div>';
				$tplresult .= '</div>';
				$tplresult .= '<div class="count-sig" '.($signal==$rxs?'style="color:#fff;background:red;"':'').'>'. $counts[$rxs].'</div>';
				$tplresult .= '</div>';
			}
		}
		}
		$tplresult .= '</div>';
		}
	}
	if(isset($array_switch) && count($array_switch) > 0){
		$tplresult .= '<div id="dev-loc">';
		if(count($array_switch)==1){
			$tplresult .='<a href="/?do=listbadsignal'.(isset($signal)?'&signal='.$signal:'').'" class="urlelelement"><i class="fi fi-rr-caret-left"></i>'.$lang['view_all'].'</a>';
		}
		foreach($array_switch as $oltid => $device){
			$tplresult .= '<a href="/?do=listbadsignal&id='.$oltid.(isset($signal)?'&signal='.$signal:'').'" class="urlelelement">'.$device['place'].' <span class="countrx">'.count($sqlonus[$oltid]['ont']).'</span></a>';
		}
		$tplresult .= '</div>';
	}
	if(isset($sqlonus) && count($sqlonus) > 0){
		$tplresult .= '<table class="resp-tab"><thead><tr>
			<th width="5%">'.$lang['status'].'</th>
			<th>Mac_SN</th>
			<th width="15%">'.$lang['inface'].'</th>
			<th width="10%">'.$lang['dist'].'</th>
			<th width="10%">'.$lang['signal'].'</th>
			<th width="10%">'.$lang['last_signal'].'</th>

			</tr></thead><tbody>';
		foreach($sqlonus as $oltid => $olt){
			$tplresult .= '<td colspan="10"  class="td_url"> <a href="/?do=listbadsignal&id='.$masiv_switch[$oltid]['swid'].'
			'.(isset($signal)?'&signal='.$signal:'').'
			">'.(isset($masiv_switch[$oltid]['place'])?$masiv_switch[$oltid]['place']:'').' onu:'.count($olt['ont']).'</a></td>';
			foreach($olt['ont'] as $ontid => $onu){
				$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:'111'));
				$status = (isset($onu['status']) && ($onu['status'] == 1) ?1:3);
				$tplresult .= '<tr>
				<td><span id="'.$onu['reason'].'" class="statusonu st_'.$status.'"</span></td>
				<td class="td_url"><a href="/?do=onu&id='.$onu['idonu'].'">'.$onukey.'</a></td>
				<td class="td_url"><font color="#0f73c3">'.strtoupper($onu['type']).' '.$onu['inface'].'</td>
				<td class="td_url">'.$onu['dist'].'</td>
				<td>'.(!empty($onu['rx'])?signalTerminal($onu['rx']):'N/A').'</td>
				<td>'.(!empty($onu['lastrx'])?signalTerminal($onu['lastrx']):'N/A').'</td>
	
				';
				$tplresult .= '</tr>';
			}
		}
		$tplresult .= '</table>';
	}	
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['list_bad_signal'].'</span></div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>