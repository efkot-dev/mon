<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$page){
	if($dataSwitch['device']=='switch' && $dataSwitch['class']=='huawei2326switch'){
		$addjs .= 'getswitch('.$id.');';
		$tplRes .= '<div id="getswitch"><div class="loadcat"><img src="../style/img/computer-seriously.gif"></div></div>';
	}elseif($dataSwitch['device']=='switch'){
		if($SQLPortDevice){
			$tplRes .='<div class="connect-list-head"><h2>'.$lang['port'].'</h2></div><div class="list_pon_switch">';
				foreach($SQLPortDevice as $PortData => $Port){
					$tplRes .='<div class="port_switch '.($port==$Port['llid']?'sel':'').'"><div class="port_img"><img src="../style/img/'.(!empty($Port['operstatus']) && $Port['operstatus']=='up' ? 'zte6.png':'zte0.png').'"></div><div class="port_name_switch text_'.$Port['operstatus'].'">';
					if($Port['operstatus']==='up'){
						$tplRes .='<a href="/?do=detail&act=switch&id='.$id.'&view=tree&port='.$Port['llid'].'">';
					}
					$tplRes .=''.$Port['nameport'].'';
					if(isset($Port['descrport']))
						$tplRes .='<span class="descrport">'.$Port['descrport'].'</span>';
					if($Port['operstatus']==='up'){
						$tplRes .='</a>';
					}
					$tplRes .='</div></div>';
				}
			$tplRes .='</div>';
		}
		if(isset($_GET['view']) && $_GET['view']=='tree' && isset($_GET['port'])){
			#$addjs .= 'trafficport('.$id.','.(int)$port.');';
			#$tplRes .='<div class="w300"><div id="trafficport"></div></div>';
		}		
		if($dataSwitch['device']=='switch' && $dataSwitch['class']=='mikrotik_ccr'){
			$addjs .= 'mikrotik_ccr_sfp('.$id.');';
			$tplRes .='<div id="mikrotik_ccr_sfp"></div><div id="signalsfp"></div>';
		}elseif($dataSwitch['device']=='switch' && $dataSwitch['class']=='cisconx3000'){
			$addjs .= 'cisco_3000_sfp('.$id.');';
			$tplRes .='<div class="w300"><div id="cisco_3000_sfp"></div></div>';
		}
	}
	if(isset($SQLPortDevice) && $SQLPortDevice>0){
		$port_this_device = false;
		foreach($SQLPortDevice as $PortDevice => $PortData){
			if(!empty($PortData['llid']) || $port['typeport']=='epon' || $port['typeport']=='gpon'){
				$dataPon[$id][$PortData['llid']]['name'] = $PortData['nameport'];
				$dataPon[$id][$PortData['llid']]['descr'] = $PortData['descrport'];
			}			
		}
	}
	/// OLT
	if($dataSwitch['device']=='olt' && is_array($SQLPon) && ($dataSwitch['oidid']==7 
		|| $dataSwitch['oidid']==41 || ($dataSwitch['oidid'] == 14 || $dataSwitch['oidid'] == 33) 
		|| $dataSwitch['oidid']==3)){
		$arraypon = processPonData($SQLPon);
		$tplRes .= '<div class="tpl_type_olt">';
		if (is_array($arraypon['platu'])) {
			foreach ($arraypon['platu'] as $dataoltpon => $datavalue) {
				$tplRes .= '<div class="tpl_type_list">';
				$rest = isset($datavalue[0]) && isset($datavalue[1]) ? array_merge($datavalue[0], $datavalue[1]) : (isset($datavalue[0]) ? $datavalue[0] : (isset($datavalue[1]) ? $datavalue[1] : []));
				foreach ($rest as $slotpon => $ponvalue) {
					$tplRes .= '<div class="tpl_type_list_p">';
					$tplRes .= '<div class="tpl_type_pon">';
					$tplRes .= '<div class="typepon"><span>SLOT: '.$slotpon.' ' . $dataoltpon.'</span></div>';
					$tplRes .= '</div>';
					$tplRes .= '<div class="dip">';
					foreach ($ponvalue as $portpon => $ponizer) {
						#$sqlbasignal = getBadRxPonInfo($id,$ponizer['sfpid']);
						$sql_new_onus = $pdo->prepare("SELECT COUNT(idonu) AS new FROM onus WHERE added >= CURDATE() AND olt = :olt AND portolt = :portolt");
						$sql_new_onus->execute([
							'olt' => $id,'portolt' => $ponizer['sfpid']
						]);
						$result = $sql_new_onus->fetch(PDO::FETCH_ASSOC);
						$new_count = $result['new'] ?? 0;
						$css_load_bar = loadbarpon($ponizer['support'],$ponizer['count']);
						$loads ='<div class="loadpon"><div class="load '.$css_load_bar['css'].'" style="width: '.$css_load_bar['width'].'%;"></div><span></span></div>';
						$tplRes .= '<a href="/?do=terminal&id='.$id.'&port='.$ponizer['id'].'" class="tpl_inface">';
						$tplRes .= '<div class="name_inface_count"><span>'.$ponizer['count'].'</span></div>';
						$tplRes .= '<div class="name_inface_sub">';
							$tplRes .= '<div class="name_inface" '.(isset($sqlbasignal['cont_onu']) && $sqlbasignal['cont_onu']>1?'style="color:red;"':'').'>' . $ponizer['pon'].($new_count!=0 ? '<span class="ont_new">+'.$new_count.'</span>':'').'</div>';
							if(!empty($dataPon[$id][$ponizer['sfpid']]['descr'])){
								$tplRes .='<span class="name_inface_descr">'.$dataPon[$id][$ponizer['sfpid']]['descr'].'</span>';	
							}
							$tplRes .= '<div class="name_load">'.$loads.'</div>';
						$tplRes .= '</div>';
						
						$tplRes .= '</a>';
					}
					$tplRes .= '</div>';
					$tplRes .= '</div>';
				}
				$tplRes .= '</div>';
			}
		}
		$tplRes .= '</div>';
		// BDCOM CDATA GCOM
		if(isset($arraypon['port']) && is_array($arraypon['port'])){
			foreach($arraypon['port'] as $dataoltpon => $datavalue){
				foreach($datavalue as $slotpon => $ponvalue){

				}
			}
		}
	}else{
		if(!empty($SQLPon) && $SQLPon>0){
			usort($SQLPon, function($a, $b){
				return ($a['sort'] - $b['sort']);
			});
			/// PON
			$tplRes .='<div class="connect-list-head"><h2>PON</h2></div><div class="list_pon">';
			foreach($SQLPon as $PortData => $Pon){
				$sqlbasignal = getBadRxPonInfo($id,$Pon['sfpid']);
				$sqlnewonutoday = getNewOnuPon($id,$Pon['sfpid']);
				$tplRes .='<div class="style_pon pon_olt"><a href="/?do=terminal&id='.$id.'&port='.$Pon['id'].'" class="sc-psedN fLDHlO"></a>';			
				$tplRes .='<div class="sc-qQWDO frpbEt icon_pon_port">';
				if(!empty($Pon['count'])){	
					$tplRes .='<span class="pon_support'.$Pon['support'].'">'.$Pon['count'].'</span>';
					if(isset($sqlnewonutoday['cont_onu']) && $sqlnewonutoday['cont_onu']>0){
						$tplRes .='<span class="todayport">+'.$sqlnewonutoday['cont_onu'].'</span>';
					}
				}
				$tplRes .='</div><div class="sc-qZtVr brvuoL" '.(isset($sqlbasignal['cont_onu']) && $sqlbasignal['cont_onu']>1?'style="color:red;"':'').'>'.$Pon['pon'].'';
				if(!empty($dataPon[$id][$Pon['sfpid']]['descr'])){
					$tplRes .='<span class="olt-descr-pon">'.$dataPon[$id][$Pon['sfpid']]['descr'].'</span>';	
				}
				$css_load_bar = loadbarpon($Pon['support'],$Pon['count']);
				$tplRes .='<div class="loadpon"><div class="load '.$css_load_bar['css'].'" style="width: '.$css_load_bar['width'].'%;"></div><span></span></div>';
				$tplRes .='<div class="dropdown-content">';
				$tplRes .='<div class="poptech"><span class="lang" style="color:green;">'.$lang['online'].'</span><span class="types">'.$Pon['online'].'</span></div>';
				$tplRes .='<div class="poptech"><span class="lang" style="color:red;">'.$lang['offline'].'</span><span class="types">'.$Pon['offline'].'</span></div>';
				$tplRes .='<div class="poptech"><span class="lang">'.$lang['dilen'].'</span><span class="types">1:'.$Pon['support'].'</span></div>';
				$tplRes .='<div class="poptech"><span class="lang">'.$lang['sfpconn'].'</span><span class="types"> '.$Pon['count'].'</span></div>';
				if($counttoday){
					$tplRes .='<div class="poptech"><span class="lang">'.$lang['newonuday'].'</span><span class="types"> '.$counttoday.'</span></div>';
				}
				if(isset($sqlbasignal['cont_onu']) && $sqlbasignal['cont_onu']>0){
					$tplRes .='<div class="poptech"><span class="lang-red">'.$lang['portbadrx'].'</span><span class="types"> '.$sqlbasignal['cont_onu'].'</span></div>';
				}
				$tplRes .='</div></div></div>';
			}
			$tplRes .='</div>';
		}
	}
	// LIST VLAN SUPPORT
	if($access->get('connectport') && isset($confPMon['VIEW_OLT_VLAN']) && !empty($confPMon['VIEW_OLT_VLAN']) && $confPMon['VIEW_OLT_VLAN']==1){
		$get_key = '';
		if(isset($id) && $id > 0){
			$stmt = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file");
			$stmt->execute([':file' => 'get_list_olt_vlan_' . $id]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!empty($result['data'])){
				$decoded_data = json_decode($result['data'], true);
			}
			if (!empty($decoded_data)) {
				foreach ($decoded_data as $vlan => $vl) {
					if (is_array($vl)) {
						foreach ($vl as $item) {
							$get_key .= '<span class="l-vlan" id="v-' . htmlspecialchars($vlan . '-' . $item) . '">' . htmlspecialchars($item) . '</span>';
						}
					} else {
						$get_key .= '<span class="l-vlan" id="v-' . htmlspecialchars($vlan) . '">' . htmlspecialchars($vl) . '</span>';
					}
				}
			} else {
				$get_key .= "";
			}
		}
		if(isset($get_key) && $get_key!=false){
			$tplRes .='
				<div class="connect-list-head">
					<h2>'.$lang['list_vlan'].'</h2>
				</div>
				<div class="list_vlan">
				'.$get_key.'
				</div>';
		}
	}
	// LIST VLAN SUPPORT
	$getpage = 'pon';
	if(isset($confPMon['STICKERS']) && !empty($confPMon['STICKERS']) && $confPMon['STICKERS']==1){
		$sql_stickers_onus = $pdo->prepare("SELECT * FROM onus WHERE olt = :olt AND stikers = :stikers");
		$sql_stickers_onus->execute([
			'olt' => $id,'stikers' => 1
		]);
		$sqlstikersonu = $sql_stickers_onus->fetchAll(PDO::FETCH_ASSOC);
		if ($sqlstikersonu && count($sqlstikersonu) > 0) {
		$tplRes .='<div class="stikers-head"><h2>'.$lang['stikers_onu'].'</h2></div>';
		$tplRes .='<table class="resp-tab" width="100%"><thead><tr><th width="5%">Status</th><th width="15%">Mac_Sn</th><th width="15%">Interface</th><th width="10%">Signal</th><th width="10%">Distance</th><th>Other</th></tr></thead><tbody>';
			foreach($sqlstikersonu as $idonu => $ont){
				$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
				if (!empty($onukey)) {
					$sql_onusdata = $pdo->prepare("SELECT * FROM onusdata WHERE onukey = :onukey LIMIT 1");
					$sql_onusdata->execute(['onukey' => $onukey]);
					$datatemponu = $sql_onusdata->fetch(PDO::FETCH_ASSOC);
				}
				$tplRes .= '<tr>
				<td><span class="statusonu st_'.$ont['status'].'"</span></td>
				<td><font color="#1f7bc3">'.(isset($ont['mac'])?$ont['mac']:(isset($ont['sn'])?$ont['sn']:'n/a')).'</font></td>
				<td class="td_url"><a href="/?do=onu&id='.$ont['idonu'].'" '.($ont['status']==2?'class="colorgrey"':'').'>'.$ont['type'].' '.$ont['inface'].'</a></td>
				<td>'.($ont['status']==1?signalTerminal($ont['rx']):'').''.($ont['status']==1 && !empty($ont['rxolt'])?signalTerminal($ont['rxolt']):'').'</td>
				<td><font color="#1f7bc3">'.$ont['dist'].'</font></td>
				<td class="description_name">
				'.($ont['status']==2?(!empty($ont['reason'])?'<span class="reason_'.$ont['reason'].'"></span>':''):'').'
				'.($ont['status']==2?(!empty($ont['offline'])?'<font color="tomato">'.aftertime($ont['offline']).'</font>':''):'').'
				'.(!empty($datatemponu['tag'])?'<span class="terminaltag">'.$datatemponu['tag'].'</span>':'').'
				'.(!empty($ont['name'])?'<span class="terminaltag">'.$ont['name'].'</span>':'').'
				</td>
				</tr>';
			}
		$tplRes .='</tbody></table>';
		}
	}
}
?>