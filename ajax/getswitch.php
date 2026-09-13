<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$result = array();
$getport = [];
$resulttype  = array();
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(isset($id)){
$getOLT = $db->Fast('switch','*',['id' => $id]);
$snmp_vlan = '';
if(!empty($getOLT['netip']) && !empty($getOLT['class'])){
$timeout = 100000;
$retries = 5;
$oid = [
	'oid' => '1.3.6.1.2.1.31.1.1.1.1',
	'cache' => true,'timecache' => 11000,'namecache' => 'list_port_'.$getOLT['id'],'type' => 'class','deloid' => true,
	'ip' => $getOLT['netip'],'community'=> $getOLT['snmpro']
];
$inface = [];
$tempinface = pmon_walk($oid);
if(is_array_empty($tempinface)){
	foreach($tempinface as $pi1 => $type) {
		if(!empty($type['result']) && preg_match("/Ethernet/i", $type['result'])){
			$portid = trim($pi1);
			if(isset($portid)){
				$oid_ = '1.3.6.1.2.1.2.2.1.8.'.$portid;
				$timeoid_ = '1.3.6.1.2.1.2.2.1.9.'.$portid;
				$temp_status = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],$oid_, $timeout, $retries);
				$temp_uptime = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],$timeoid_, $timeout, $retries);
				$inface[$portid]['switchid'] = $getOLT['id'];
				$inface[$portid]['sort'] = $portid;
				$inface[$portid]['llid'] = $portid;
				$inface[$portid]['uptime'] = switch_uptime(str_replace($timeoid_,'',$temp_uptime));
				preg_match('/0\/0\/(\d+)/',$type['result'],$infaceif);
				if(preg_match("/GigabitEthernet/i", $type['result'])){
					$inface[$portid]['nameport'] = 'GigabitEthernet 0/0/'.$infaceif[1];
					$inface[$portid]['typeport'] = 'combosfp';	
				}elseif( preg_match("/Ethernet/i", $type['result'])){
					$inface[$portid]['nameport'] = 'Ethernet 0/0/'.$infaceif[1];
					$inface[$portid]['typeport'] = 'eth';
				}
				$status = valueStringSnmp(str_replace($oid_,'',$temp_status));
				$inface[$portid]['operstatus'] = (isset($status) && $status==2 ? 'down' : 'up');	
			}
		}
	}
	/*
	$vlanoid = [
		'oid' => '1.3.6.1.2.1.17.7.1.4.3.1.1','type' => 'exec','deloid' => true,'ip' => $getOLT['netip'],'community'=> $getOLT['snmpro']
	];
	$vlans = [];
	$tempvlan = pmon_walk($vlanoid);
	if(is_array($tempvlan)){
		foreach ($tempvlan as $vlan_key => $vlan_value){
			preg_match('/(\d+) =/',$vlan_value['result'],$mat);
			$idvlan = trim($mat[1]);
			$vlans[$idvlan] = [
				'vlankey' => $idvlan,
				'vlanid' => 'Vlan '.$idvlan
			];
		}
	}
	*/
	$maclistoid = [
		'oid' => '1.3.6.1.2.1.17.4.3.1.2','type' => 'class','deloid' => true,'ip' => $getOLT['netip'],'community'=> $getOLT['snmpro']
	];
	$maclist = [];
	$tempmaclist = pmon_walk($maclistoid);
	if(is_array_empty($tempmaclist)){
		$int_mac = 1;
		foreach ($tempmaclist as $mac_key => $mac_value){
				$ps_iface = explode('.',trim($mac_key));
				$ps_temp = sizeof($ps_iface);
				$ps_mac = substr('0'.dechex($ps_iface[($ps_temp-6)]),-2).substr('0'.dechex($ps_iface[($ps_temp-5)]),-2).substr('0'.dechex($ps_iface[($ps_temp-4)]),-2).substr('0'.dechex($ps_iface[($ps_temp-3)]),-2).substr('0'.dechex($ps_iface[($ps_temp-2)]),-2).substr('0'.dechex($ps_iface[($ps_temp-1)]),-2);
				$real_mac = preg_replace('/(.{2})/','\1:',$ps_mac,5);
				$maclist[$int_mac] = [
					'port'=>trim(str_replace(' ', '',str_replace('INTEGER:', '',$mac_value['result']))),
					'mac'=>$real_mac
				];
				$int_mac ++;
		}
		usort($maclist, function($a, $b) {
			return $a['port'] - $b['port'];
		});			
	}
}
if(is_array_empty($inface)){
	echo'<div class="details_switch"><div id="chassisHolderDiv" class="switch1"><div id="chassisDiv" style="position:relative;">';
	foreach ($inface as $idport => $item){
		echo huaweiS2326TP($item);
	}
	echo'<img style="top:-8px;left:510px;position:absolute;visibility:visible;" src="../style/img/up_st.png">';
	echo'<img style="top:68px;left:560px;position:absolute;visibility:visible;" src="../style/img/down_st.png">';
	echo'</div></div>';
	echo'<div class="listclan">';
	/*
		if(is_array($vlans)){
			echo'<div class="vlan_list">';
			foreach ($vlans as $idvlan => $typevlan){
				echo'<div class="vlan" id="vlan_id_'.$typevlan['vlankey'].'">'.$typevlan['vlanid'].'</div>';
			}
			echo'</div>';	
		}
		*/
	$getmapper = $db->Fast('geodevice','*',['deviceid' => $id]);
	if(!empty($getmapper['id'])){
		$mapper = getMap();
		$mapont = "L.marker([".$getmapper['lan'].",".$getmapper['lon']."],{icon: L.divIcon({className: 'mapper', html: '<div class=\"mappericon\"><img src=\"../style/img/database.png\"></div>'})})";
		$mapont .= ".bindTooltip('".$getmapper['name']."<br>')";
		$mapont .= ".bindPopup('<div class=\"div-l\"><a href=\"/?do=detail&act=".$getmapper['device']."&id=".$getmapper['deviceid']."\">".$getmapper['name']."</a><br></div>').openPopup()";
		$mapont .= ".addTo(map);";
		echo'<div id="onumapper" style="height:100px;width:400px;"></div>
		<script>
		var lat = "'.$getmapper['lan'].'"; 
		var lon = "'.$getmapper['lon'].'";
		var map = L.map(\'onumapper\');
		map.setView([lat, lon], 17);
		'.$mapont.$mapper.'
		</script>';
	}else{
		echo'<div class="w400"><div class="add_onu_maps"><a href="/?do=mapper&act=add&id='.$getOLT['id'].'">'.$lang['addmapper'].'</a></div></div>';
	}
	echo'</div>';

	echo'</div><br>';
		echo'<div id="trafficport"></div>';
	if(is_array($maclist)){	
		echo'<div id="ontbdcomepon" style="width: 100%;padding: 0;"><table class="resp-tab"><thead><tr><th width="15%">'.$lang['inface'].'</th><th width="20%">Mac</th><th width="15%">'.$lang['uptime'].'</th><th></th></tr></thead><tbody>';
		foreach ($maclist as $idportmac => $portdata){
			if(isset($inface[$portdata['port']]) && $portdata['port']<=31 && !preg_match("/GigabitEthernet /i", $inface[$portdata['port']]['nameport']) && !empty($inface[$portdata['port']]['nameport'])){
				echo'<tr><td class="ethswitch"><img src="../style/img/port.png">'.$inface[$portdata['port']]['nameport'].'</td>
				<td><b>'.$portdata['mac'].'</b></td><td>'.$inface[$portdata['port']]['uptime'].'</td>
				<td class="typesmac"><img onclick="getmacswitch(\''.$portdata['mac'].'\','.$idportmac.')" src="../style/img/online.png">
				<div style="display:initial;" id="getmac_'.$idportmac.'"></div><div style="display:initial;" id="gettraff_'.$idportmac.'"></div></td>
				</tr>';
			}
		}	
		echo'</tbody></table></div>';
	}		
	/*
	if(is_array($maclist)){	
		echo'<br><div id="ontbdcomepon" style="width: 100%;padding: 0;"><table class="resp-tab"><thead><tr><th width="15%">'.$lang['inface'].'</th><th width="20%">Mac</th><th width="15%">'.$lang['uptime'].'</th><th></th></tr></thead><tbody>';
		foreach ($maclist as $idportmac => $portdata){
			echo'<tr><td class="ethswitch"><img src="../style/img/port.png">'.$inface[$portdata['port']]['nameport'].'</td>
			<td><b>'.$portdata['mac'].'</b></td><td>'.$inface[$portdata['port']]['uptime'].'</td>
			<td class="typesmac"><img onclick="getmacswitch(\''.$portdata['mac'].'\','.$idportmac.')" src="../style/img/online.png">
			<div style="display:initial;" id="getmac_'.$idportmac.'"></div><div style="display:initial;" id="gettraff_'.$idportmac.'"></div></td>
			</tr>';
		}	
		echo'</tbody></table></div>';
	}
*/	
}
}
}
}
?>