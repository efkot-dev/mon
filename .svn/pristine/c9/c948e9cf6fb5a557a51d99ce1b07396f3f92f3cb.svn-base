<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$checkLicenseSwitch = getSwitchAll();
$metatags = array('title'=>$lang['page_badsignal'],'description'=>$lang['page_badsignal'],'page'=>'badsignal');
$selectswitch = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
$masiv_switch = [];
$array_switch = [];
$urlswitch ='';
$tplresult = '';
	if (!empty($checkLicenseSwitch)) {
		foreach ($checkLicenseSwitch as $switch) {
			if (isset($switch['device']) && $switch['device'] === 'olt') {
				$sql_pon = "SELECT pon, id, sfpid,support,count,online,offline FROM `switch_pon` WHERE oltid = '{$switch['id']}'";
				$sqlpon = $db->SimpleWhile($sql_pon);
				if ($sqlpon) {
					foreach ($sqlpon as $pon) {
						$data_pon[$pon['sfpid']] = $pon;
					}
				}
				$masiv_switch[$switch['id']] = [
					'swid' => $switch['id'],
					'place' => $switch['place'],
					'model' => $switch['model'] . $switch['inf'],
					'pon' => $data_pon,
				];
			}
		}
	}
	$badsignalstart = '-'.$config['badsignalstart'];
	$badsignalend = '-'.$config['badsignalend'];
	$where_onus = "";
	if(isset($selectswitch) && $selectswitch>0){
		$where_onus = "WHERE olt = '".$selectswitch."' AND";
	}else{
		$where_onus = "WHERE ";
	}
	$orderby = " status = '1' AND rx IS NOT NULL AND rx != '' AND rx != '0' AND rx BETWEEN " . (int)$badsignalend.".99 AND " . (int)$badsignalstart . ".00 ORDER BY CAST(rx AS DECIMAL(10, 2)) ASC";
	$sql = "SELECT * FROM onus $where_onus $orderby";
	$sqltemponu = $db->SimpleWhile($sql);
	$sqlonus = [];
	$count_all = 1;
	if (isset($sqltemponu) && count($sqltemponu) > 0) {
		foreach ($sqltemponu as $ontid => $ont) {
			if ($access->get('dev' . $ont['olt'])) {
				if (!isset($sqlonus[$ont['olt']]['count'])) {
					$sqlonus[$ont['olt']]['count'] = 0;
				}				
				$sqlonus[$ont['olt']]['count']++;
				$sqlonus[$ont['olt']]['port'][$ont['portolt']]['ont'][$ont['idonu']] = $ont;
				$count_all ++;
			}
		}
	}
	$tplresult .= '<div class="container"><div class="left-column">';
	$tplresult .= "<div class='menu_olt_left'>";
	if (isset($selectswitch) && $selectswitch > 0) {
		$tplresult .= "<a class='menu-sub' href='/?do=badsignal'><img src='../style/img/pmon_return.png'>View all</a>"; 
	}
	foreach($masiv_switch as $oltid => $device){
		$count_ont = $sqlonus[$oltid]['count'] ?? 0;
		if(isset($count_ont) && $count_ont>0 && $access->get('dev' . $oltid)) {
			if($selectswitch == $oltid){
				$bar = '';
			}else{
				$per_olt = (int)($count_all > 0) ? round(($count_ont / $count_all) * 100) : 0;
				$bar = "<div class=\"port_precent\"><div class='load' style='width:{$per_olt}%;'></div></div>";
			}
		$tplresult .= "<a class='menu-sub' href='/?do=badsignal&id=".$oltid."'>
			".$device['place']."
			<span class=\"badont\">{$count_ont}</span>
				{$bar}
			</a>";
		}
	}
	$tplresult .= '</div></div><div class="right-column">';
	if(isset($sqlonus) && count($sqlonus) > 0){
		$tplresult .= '<table class="resp-tab"><thead><tr>
			<th width="5%">'.$lang['status'].'</th>
			<th>Mac_SN</th>
			<th width="10%">'.$lang['inface'].'</th>
			<th width="8%">'.$lang['dist'].'</th>
			<th width="8%">RxOnu</th>
			<th width="8%">RxOlt</th>
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
			$tplresult .= '<tr><td colspan="10" class="pon_list_olt"> 
			<a href="/?do=badsignal&id='.$masiv_switch[$oltid]['swid'].'">'.(isset($masiv_switch[$oltid]['place'])?$masiv_switch[$oltid]['place']:'').'</a>
			</td></tr>';
			foreach($olt['port'] as $portid => $listont){
				$count = count($listont['ont']);
				$all = $masiv_switch[$oltid]['pon'][$portid]['count'];
				$percentage = (int)($all > 0) ? round(($count / $all) * 100) : 0;
				if ($percentage < 10) {
					$class = "green";
				} elseif ($percentage >= 10 && $percentage < 30) {
					$class = "orange";
				} else {
					$class = "red";
				}
				$tplresult .= '<tr><td colspan="10" class="pon_list_port">
					' . $masiv_switch[$oltid]['pon'][$portid]['pon'] . ' 
					<div class="port_bar">
						<div class="' . $class . '" style="width: ' . $percentage . '%;"></div>
					</div>
					' . $percentage . '% 
					</td></tr>';
				foreach($listont['ont'] as $ontid => $onu){
					$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:'n/a'));
					$onuname = (!empty($onu['name'])? '<span class="bad_name_onu">'.$onu['name'].'</span>':'');
					$tplresult .= '<tr>
					<td><span class="statusonu st_'.$onu['status'].'"</span></td>
					<td class="td_url"><a href="/?do=onu&id='.$onu['idonu'].'">'.($onu['status']==2?'<font color="grey">':'').''.$onukey.' '.$onuname.'</a></td>
					<td class="td_url"><font color="grey">'.strtoupper($onu['type']).' '.$onu['inface'].'</td>
					<td class="td_url">'.$onu['dist'].'</td>
					<td class="bad_list">'.($onu['status']==2?'N/A':'<span class="signal4">'.$onu['rx'].'</span>').'</td>
					<td class="bad_list">'.($onu['status']==2?'N/A':'<span class="signal4">'.$onu['rxolt'].'</span>').'</td>
					<td><font color="#2196F3">'.(isset($onu['changerx']) ? aftertime($onu['changerx']) : '').'</font></td>
					<td>'.($onu['status']==2?'N/A':signalTerminal($onu['lastrx'])).'</td>
					';
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
		}
		$tplresult .= '</table>';
	}	
	$tplresult .= '</div>';
	$selected_menu = '';
	if(isset($selectswitch) && $selectswitch>0){
		$selected_menu .= '<a class="brmhref" href="/?do=badsignal"><i class="fi fi-rr-angle-left"></i>'.$lang['page_badsignal'].'</a>';
		$selected_menu .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$masiv_switch[$selectswitch]['place'].'</span>';
	}else{
		$selected_menu .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['page_badsignal'].'</span>';
	}
	$result ='<div id="onu-speedbar">
		<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
		'.$selected_menu.'
		</div>
		'.$tplresult;
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>