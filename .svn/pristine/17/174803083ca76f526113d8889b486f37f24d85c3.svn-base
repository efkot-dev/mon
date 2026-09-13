<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$getONT = $db->Fast('onus','*',['idonu' => $id]);
if(!empty($getONT['idonu'])){
$getOLT = $db->Fast('switch','*',['id' => $getONT['olt']]);
$timeout = 1000000;
$retries = 5;
$snmp_vlan = '';
$rxcatv_value = '';
$control = '';
$control_vlan = '';
$tv_port = '';
#$oid_eth = '1.3.6.1.4.1.13464.1.13.4.1.1.6.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1';
#$sfp_tx_signal = '1.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.5.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
$onu_tx_signal = '1.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.6.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
$onu_rx_signal = '1.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.7.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
$onu_volt = '1.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.5.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
if(!empty($getOLT['netip']) && !empty($getOLT['class']) && $getOLT['oidid']==29){
	$ethvalue = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],$oid_eth,$timeout,$retries);
	if(isset($ethvalue)){
		$dataportstatus = array('1'=>'enable','2'=>'disable','3'=>'none');
		$eth = trim(preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $ethvalue));
		echo'<div class="zte_onu"><div class="zte_eth">';
		$onu_eth = typeOnubdcomPort($eth);
		if(isset($onu_eth['status']) && !empty($onu_eth['status'])){
			echo'<div class="link link4"><div class="linkname">Eth1</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div></div>';
			echo'<div class="zte_status"><div class="zte_gettype"><span>Port status:</span><span class="typeportstatus"><div class="eth_online"></div><div class="eth_name">Online</div><div class="eth_offline"></div><div class="eth_name">Offline</div><div class="eth_disable"></div><div class="eth_name">Disable</div></span></div></div></div>';
		}
	}
	echo'<div class="block_dbm">';
	$onu_rx_value = @snmp2_get($getOLT['netip'], $getOLT['snmpro'],$onu_rx_signal, $timeout,$retries);
	if($onu_rx_value) {
		$onu_rx_value = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $onu_rx_value);
		$onu_rx_value = trim($onu_rx_value);
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:2);
		$olt_color = $onu_rx_value > $minbad ? "red" : "#36b105";
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">RX ONU</div><div class="s"><span>' . $onu_rx_value . '</span></div></div></div>';
	}		
	$onu_tx_value = @snmp2_get($getOLT['netip'], $getOLT['snmpro'],$onu_tx_signal, $timeout,$retries);
	if($onu_tx_value) {
		$onu_tx_value = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $onu_tx_value);
		$onu_tx_value = trim($onu_tx_value);
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:2);
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">TX ONU</div><div class="s"><span>' . $onu_tx_value . '</span></div></div></div>';
	}		
	$onu_volt_value = @snmp2_get($getOLT['netip'], $getOLT['snmpro'],$onu_volt, $timeout,$retries);
	if($onu_volt_value) {
		$onu_volt_value = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $onu_volt_value);
		$onu_volt_value = trim($onu_volt_value);
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/voltage.png"></div><div class="text"><div class="n">BiasCurent</div><div class="s"><span style="color:' . $olt_color . ';">' . $onu_volt_value . '</span></div></div></div>';
	}	
	echo'</div>';	
}
}
}
?>