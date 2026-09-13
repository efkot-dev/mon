<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$getONT = $db->Fast('onus','*',['idonu' => $id]);
if(is_valid_id($getONT['idonu'])){
$getOLT = $db->Fast('switch','netip,snmpro,class,oidid',['id' => $getONT['olt']]);
$array_status = [
'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],'oid' => '1.3.6.1.4.1.17409.2.3.4.1.1.8.'.$getONT['keyonu']
];
$onu_status = get_snmp_pmon($array_status);
$array_admin = [
'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],'oid' => '1.3.6.1.4.1.17409.2.3.4.1.1.9.'.$getONT['keyonu']
];
$onu_admin = get_snmp_pmon($array_admin);
if(isset($onu_admin) && $onu_admin==2){	
	echo'<div class="onu_reasons view_onu_none"><b>Admin Status</b>Disable</div>';
}

if(isset($onu_status) && $onu_status ==1){
	$array_eth = [
	'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],'oid' => '1.3.6.1.4.1.17409.2.3.4.1.1.8.'.$getONT['keyonu']
	];
	$onu_eth = get_snmp_pmon($array_eth);
	if ($onu_eth !== false) {
		echo'<div class="zte_onu"><div class="zte_eth">';
		$onu_eth = typeOnubdcomPort($onu_eth);
		echo'<div class="link link4"><div class="linkname">Eth1</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div>';
		echo '</div>';
		$eth_onu[1]['status'] = $onu_eth['status'];			
		$eth_onu[1]['type'] = 'eth';	
	}		
	echo info_port();	
	echo'<div class="block_dbm">';
	$array_vlan = [
	'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],'oid' => '1.3.6.1.4.1.17409.2.3.7.3.1.1.7.'.$getONT['keyonu'].'.0.1'
	];
	$vlan = get_snmp_pmon($array_vlan);
	if ($vlan !== false) {
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/manager_vlan.png"></div><div class="text"><div class="n">Vlan</div>';
		echo'<div class="s"><span onclick="showHideBlock(\'blockvlan\',\'knopka2\')" id="knopka2" class="knopka">'.$lang['pt_vlan'].'</span></div></div></div>';
	}	
	$array_tx = [
	'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],'oid' => '1.3.6.1.4.1.17409.2.3.4.2.1.5.' . $getONT['keyonu'] . '.0.0'
	];
	$tx_value = get_snmp_pmon($array_tx);
	if ($tx_value !== false) {
		$tx_value = number_format(floatval($tx_value) / 10, 2);
		$minbad = $config['badsignalstart'] ?? 2;
		$color = $tx_value > $minbad ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">TX ONU</div>
					<div class="s"><span style="color:' . $color . ';">' . $tx_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}
	$rx_value = @snmp2_get($getOLT['netip'], $getOLT['snmpro'], '1.3.6.1.4.1.17409.2.3.4.2.1.4.'. $getONT['keyonu'].'.0.0');
	if ($rx_value !== false) {
		$rx_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $rx_value);
		$rx_value = number_format(floatval($rx_value) / 100, 2);
		$minbad = $config['badsignalstart'] ?? 26;
		$color = $rx_value < -$minbad ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX ONU</a></div>
					<div class="s"><span style="color:' . $color . ';">' . $rx_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}
	$rxolt_value = @snmp2_get($getOLT['netip'], $getOLT['snmpro'], '1.3.6.1.4.1.17409.2.3.3.6.1.2.'. $getONT['keyonu']);
	if ($rxolt_value !== false) {
		$rxolt_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $rxolt_value);
		$rxolt_value = number_format(floatval($rxolt_value) / 100, 2);
		$minsbad = $config['badsignalstart'] ?? 26;
		$colorrx = $rxolt_value < -$minsbad ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX OLT</a></div>
					<div class="s"><span style="color:' . $colorrx . ';">' . $rxolt_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}
	echo'</div>';
	// C_DATA 12 EPON - зміна влан
	if(isset($vlan) && $vlan>0){
		echo'<div id="blockvlan" style="display: none;">
			<div class="blockvlan">';
			$onu_eth_id = 1;
			echo ont_label('<img class="man_vlan" src="../style/img/eth.png">'.$lang['bdcom_vlan'].' eth1',$vlan.'<span id="load-edit-vlan-'.$onu_eth_id.'"></span><span id="edit-vlan-'.$onu_eth_id.'" ></span><span class="ont-btn" id="btn-edit-vlan-'.$onu_eth_id.'" onclick="bdcomvlanonu_ajax('.$getONT['olt'].','.$getONT['idonu'].',\'cdata12formeditonu\','.$onu_eth_id.')">'.$lang['edit'].'</span>');

		echo'
			</div>
		</div>';
		$db->query("UPDATE onus SET wan = '{$vlan}' WHERE idonu  = {$getONT['idonu']}");
	}

	sleep(2);
	echo'<script type="text/javascript">
		fdbdata12('.$getONT['idonu'].');
	</script>';
}
}
}
?>