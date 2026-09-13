<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$starttime = microtime(true);
$onu_port = false;
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(is_valid_id($id)){
$sql_ont = $pdo->prepare("SELECT * FROM onus WHERE idonu = :idonu");
$sql_ont->execute(['idonu' => $id]);
$data_ont = $sql_ont->fetch(PDO::FETCH_ASSOC);
if(is_valid_id($data_ont['idonu'])){
$llid = $data_ont['zte_idport'].'.'.$data_ont['keyonu'];
$support_port_onu = many_port_onu($pdo);
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $data_ont['olt']]);
$data_olt = $sql_olt->fetch(PDO::FETCH_ASSOC);
$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:2);
$core_snmp = new SNMP(SNMP::VERSION_2C,$data_olt['netip'],$data_olt['snmpro']);
if(!empty($data_olt['netip']) && $data_ont['type']==='gpon'){
$temp_status = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15.'.$data_ont['zte_idport'].'.'.$data_ont['keyonu'],true);
$status_onu_snmp = getsnmp_integer($temp_status);
if(isset($status_onu_snmp) && $status_onu_snmp==1){
	$onu_model = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.45.1.4.'.$llid,true);
	if($onu_model!=false){
		$get_onu_model = getsnmp_string($onu_model);
	}
	if(isset($get_onu_model) && in_array(strtoupper($get_onu_model), array_map('strtoupper', $support_port_onu))) {
		$all_eth_huawei = @snmp2_real_walk($data_olt['netip'],$data_olt['snmpro'],'1.3.6.1.4.1.2011.6.128.1.1.2.62.1.22.'.$llid);	
		if(!empty($all_eth_huawei) && is_array($all_eth_huawei)) {
			$i_port = 1;
			$port_onu = [];
			foreach ($all_eth_huawei as $onu_eth) {
				$eth_onu = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_eth);
				if(in_array($eth_onu, [1, 2])) {
					$port_onu[$i_port]['status'] = (($eth_onu == 1) ? 'up' : 'down');
					$i_port++;
				}
			}
		}
	}else{
		$tmp_speet_onu_eth = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.62.1.4.'.$llid.'.1',true);
		$speet_onu_eth = getsnmp_integer($tmp_speet_onu_eth);
		$tmp_status_onu_eth = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.62.1.22.'.$llid.'.1',true);
		$status_eth = getsnmp_integer($tmp_status_onu_eth);
		$onu_port = true;
	}
	if(isset($onu_port) && $onu_port == true) {
		if(isset($speet_onu_eth) && $speet_onu_eth !=false ) {
			$img_speed = huawei_linktype_onu_img($speet_onu_eth);
		}
		$eth_port = '<div class="zte_eth"><div class="link link4"><div class="linkname">Eth1</div><img src="../style/img/zte'.$img_speed.'.png"><div class="linkstatus'.(($status_eth == 1) ? 'up' : 'down').'"></div></div></div>';
	}else{		
		$eth_port .= '<div class="zte_eth">';
		foreach ($port_onu as $idport => $status_eth) {
			$eth_port .= '<div class="link link4"><div class="linkname">Eth'.$idport.'</div><img src="../style/img/'.($status_eth['status']=='up'?'zte5':'zte0').'.png"><div class="linkstatus'.$status_eth['status'].'"></div></div>';
		}
		$eth_port .= '</div>';
	}
	echo'<div class="zte_onu">
			'.$eth_port.'
			<div class="zte_status">
				<div class="zte_getstatus">
					<span>Port type:</span>
					<span class="typortzte">
						<div class="eth_auto"></div>
						<div class="eth_name">Auto</div>
						<div class="eth_10"></div>
						<div class="eth_name">10Mbps</div>
						<div class="eth_100"></div>
						<div class="eth_name">100Mbps</div>
						<div class="eth_1000"></div>
						<div class="eth_name">1G</div>
					</span>
				</div>
				<div class="zte_gettype">
					<span>Port status:</span>
					<span class="typeportstatus">
						<div class="eth_online"></div>
						<div class="eth_name">Online</div>
						<div class="eth_offline"></div>
						<div class="eth_name">Offline</div>
						<div class="eth_disable"></div>
						<div class="eth_name">Disable</div>
					</span>
				</div>
			</div>
		</div>';
}
echo'<div class="zte_onu"><div class="zte_eth">';
if(isset($status_onu_snmp) && $status_onu_snmp==1){
	echo'<div class="block_dbm">';
	$tmp_signal_rx_olt = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.51.1.6.'.$llid,true);
	$rx_olt = getsnmp_integer($tmp_signal_rx_olt);
	if(isset($rx_olt) && $rx_olt!=false) {
		$rx_olt_formatted = number_format((10000-floatval($rx_olt))/100,2);	
		echo'
		<div class="dbm_block">
			<div class="i"><img src="../style/img/rx.png"></div>
			<div class="text">
				<div class="n">RX OLT</div>
				<div class="s">
					<span style="color:'.(($rx_olt > $minbad) ? "red" : "#36b105").';">'.$rx_olt_formatted . '</span><b>dBm</b>
				</div>
			</div>
		</div>';
	}
	$signal_onu_rx = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4.'.$llid,true);
	$rx_onu = getsnmp_integer($signal_onu_rx);	
	if(isset($rx_onu) && $rx_onu!=false) {
		$rx_onu_formatted = number_format(floatval($rx_onu) / 100, 2);	
		echo'
		<div class="dbm_block">
			<div class="i"><img src="../style/img/rx.png"></div>
			<div class="text">
				<div class="n">RX ONU</div>
				<div class="s">
					<span style="color:'.(($rx_onu > $minbad) ? "red" : "#36b105").';">'.$rx_onu_formatted . '</span><b>dBm</b>
				</div>
			</div>
		</div>';
	}
	$signal_onu_tx = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.51.1.3.'.$llid,true);
	$tx_onu = getsnmp_integer($signal_onu_tx);	
	if(isset($tx_onu) && $tx_onu!=false) {
		$tx_onu = number_format(floatval($tx_onu) / 100, 2);
		echo'
		<div class="dbm_block">
			<div class="i"><img src="../style/img/rx.png"></div>
			<div class="text">
				<div class="n">TX ONU</div>
				<div class="s">
					<span style="color:' . ($tx_onu < 1 ? "red" : "#36b105" ). ';">' . $tx_onu . '</span><b>dBm</b>
				</div>
			</div>
		</div>';
	}	
	echo'</div>';
}
if(isset($status_onu_snmp) && ($status_onu_snmp==1 || $status_onu_snmp==2)){
	echo'<div id="onu_detail">';
	$count_onu_mac = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.46.1.21.'.$llid,true);
	$countmac = getsnmp_integer($count_onu_mac);
	if (isset($countmac) && $countmac!=false) {
		echo '
			<div class="block_onu count_mac">
				<div class="n">MAC`ів за ону</div>
				<div class="v">' . cl_snmp($countmac) . '</div>
			</div>';
	}	
	if(isset($speet_onu_eth) && $speet_onu_eth!=false) {
		echo '
			<div class="block_onu speed_port">
				<div class="n">Швидкість порта</div>
				<div class="v">' . huawei_linktype_onu($speet_onu_eth) . '</div>
			</div>';
	}
	$result_uvlan = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.62.1.7.'.$llid.'.1',true);
	$uvlan = getsnmp_integer($result_uvlan);
	if (isset($uvlan) && $uvlan!=false) {
		echo '
			<div class="block_onu uvlan">
				<div class="n">Ethernet Vlan</div>
				<div class="v">' . cl_snmp($uvlan) . '</div>
			</div>';
		$result_spport = $core_snmp->get('1.3.6.1.4.1.2011.5.14.5.5.1.7.'.$data_ont['zte_idport'].'.4.'.$data_ont['keyonu'].'.4294967295.4294967295.1.'.$uvlan,true);
		$sport_onu = getsnmp_integer($result_spport);
		if(isset($sport_onu) && $sport_onu>0) {
			$tmp_result_tariff = $core_snmp->get('1.3.6.1.4.1.2011.5.14.5.2.1.22.'.$sport_onu,true);
			$result_tariff = getsnmp_string($tmp_result_tariff);
			if(isset($result_tariff) && $result_tariff!=false) {
				echo '
					<div class="block_onu lineprofile">
						<div class="n">Template</div>
						<div class="v">' . $result_tariff . '</div>
					</div>';
			}						
		}
	}	
	$result_lineprofile = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.43.1.7.'.$llid,true);
	$linepro = getsnmp_string($result_lineprofile);
	if (isset($linepro) && $linepro!=false) {
		echo '
			<div class="block_onu lineprofile">
				<div class="n">LineProfName</div>
				<div class="v">' . cl_snmp($linepro) . '</div>
			</div>';
	}
	$result_sprofile = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.43.1.8.'.$llid,true);
	$s_profile = getsnmp_string($result_sprofile);
	if (isset($s_profile) && $s_profile!=false) {
		echo '
			<div class="block_onu lineprofile">
				<div class="n">Service profile</div>
				<div class="v">' . cl_snmp($s_profile) . '</div>
			</div>';
	}
	$result_ulan = $core_snmp->get('1.3.6.1.4.1.2011.6.128.1.1.2.62.1.7.'.$llid.'.1',true);
	$s_u_vlan = getsnmp_string($result_ulan);
	if (isset($s_u_vlan) && $s_u_vlan!=false) {
		echo '
			<div class="block_onu lineprofile">
				<div class="n">InnerVlan</div>
				<div class="v">' . smartfiber($s_u_vlan) . '</div>
			</div>';
	}
	echo'</div>';
	$core_snmp->close();
}
	if(isset($status_onu_snmp) && ($status_onu_snmp==1 || $status_onu_snmp==2)){
		echo'<div class="block_all"><h2 onclick="show_reason_huawei('.$data_ont['idonu'].');"><b>'.$lang['power_all'].'  '.$data_ont['type'].' '.$data_ont['inface'].'</b></h2>';
		echo'<div class="block_body" id="reason_huawei"></div></div>';
	}
}
}
}
?>

