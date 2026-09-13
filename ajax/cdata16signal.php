<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(is_valid_id($id)){
$getONT = $db->Fast('onus','*',['idonu' => $id]);
if(is_valid_id($getONT['idonu'])){
$getOLT = $db->Fast('switch','*',['id' => $getONT['olt']]);
$snmp_vlan = '';
$array_status = [
'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],'oid' => '1.3.6.1.4.1.17409.2.8.4.1.1.7.'.$getONT['keyonu']
];
$onu_status = get_snmp_pmon($array_status);
if(isset($onu_status) && $onu_status ==1){
	
	echo'<div class="block_dbm">';
	$array_tx = [
		'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],
		'oid' => '1.3.6.1.4.1.17409.2.8.4.4.1.5.'. $getONT['keyonu'].'.0.0'
	];
	$onu_tx = get_snmp_pmon($array_tx);
	if ($onu_tx !== false) {
		$tx_value = number_format(floatval($onu_tx) / 100, 2);
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:2);
		$olt_color = $tx_value > $minbad ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">TX ONU</div>
					<div class="s"><span style="color:' . $olt_color . ';">' . $tx_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}
	$array_rx = [
		'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],
		'oid' => '1.3.6.1.4.1.17409.2.8.4.4.1.4.'. $getONT['keyonu'].'.0.0'
	];
	$onu_rx = get_snmp_pmon($array_rx);
	if ($onu_rx !== false) {
		$rx_value = number_format(floatval($onu_rx) / 100, 2);
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
		$olt_color = $rx_value < -$minbad ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX ONU</div>
					<div class="s"><span style="color:' . $olt_color . ';">' . $rx_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}		
	$array_rx_olt = [
		'netip' => $getOLT['netip'],'snmpro' => $getOLT['snmpro'],
		'oid' => '1.3.6.1.4.1.17409.2.3.3.6.1.2.' . $getONT['keyonu']
	];
	$olt_rx = get_snmp_pmon($array_rx_olt);
	if ($olt_rx !== false) {
		$rxolt_value = number_format(floatval($olt_rx) / 100, 2);
		$olt_color = $rxolt_value < -29 ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX OLT</div>
					<div class="s"><span style="color:' . $olt_color . ';">' . $rxolt_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}	
	echo'</div>';
}
}
}
?>