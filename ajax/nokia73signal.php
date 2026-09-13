<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$sql_ont = $pdo->prepare("SELECT * FROM onus WHERE idonu = :idonu");
$sql_ont->execute(['idonu' => $id]);
$data_ont = $sql_ont->fetch(PDO::FETCH_ASSOC);
if(!empty($data_ont['idonu'])){
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $data_ont['olt']]);
$data_olt = $sql_olt->fetch(PDO::FETCH_ASSOC);
$core_snmp = new SNMP(SNMP::VERSION_2C,$data_olt['netip'],$data_olt['snmpro']);
$temp_status = $core_snmp->get('1.3.6.1.2.1.2.2.1.8.'.$data_ont['keyonu'],true);
$real_onu_status = getsnmp_integer($temp_status);
$control = '';
if(!empty($data_olt['class']) && $data_olt['oidid']==43 && $real_onu_status != false){
	$oid_onu_rx = '1.3.6.1.4.1.637.61.1.35.10.14.1.2.'.$data_ont['keyonu'];
	$oid_onu_tx = '1.3.6.1.4.1.637.61.1.35.10.14.1.4.'.$data_ont['keyonu'];
	$oid_onu_rx_olt = '1.3.6.1.4.1.637.61.1.35.10.18.1.2.'.$data_ont['keyonu'];
	$oid_onu_eth_status = '1.3.6.1.4.1.637.61.1.35.13.2.1.8.'.$data_ont['keyonu'].'.1';
	if(isset($real_onu_status) && $real_onu_status == 1){
		echo $ethvalue = $core_snmp->get($oid_onu_eth_status,true);
	}
	if(isset($ethvalue) && $ethvalue != false){
		echo'<div class="zte_onu">
			<div class="zte_eth">';
		$onu_eth = typeOnubdcomPort($ethvalue);
		echo'
			<div class="link link4" data-llid="'.$getONT['keyonu'].'">
				<div class="linkname">Eth1</div>
				<img src="../style/img/'.$onu_eth['img'].'">
				<div class="linkstatus'.$onu_eth['status'].'"></div>
			</div>
		</div>';

	echo'
		<div class="zte_status">
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
	echo'<div class="block_dbm">';
	if(isset($real_onu_status) && $real_onu_status == 1){
		$tx_value = $core_snmp->get($oid_onu_tx,true);
		if(isset($tx_value) && $tx_value!=false) {
			$tx_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $tx_value);
			$tx_value = number_format(floatval($tx_value) * 0.002, 2);
			$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:2);
			$olt_color = $tx_value > $minbad ? "red" : "#36b105";
			echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">TX ONU</div>';
			echo'<div class="s"><span style="color:' . $olt_color . ';">' . $tx_value . '</span><b>dBm</b></div></div></div>';
		}
		$rx_value = $core_snmp->get($oid_onu_rx,true);
		if(isset($rx_value) && $rx_value!=false) {
			$rx_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $rx_value);
			$rx_value = number_format(floatval($rx_value) * 0.002, 2);
			$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
			$olt_color = $rx_value < -$minbad ? "red" : "#36b105";
			echo '<div class="dbm_block">
					<div class="i"><img src="../style/img/rx.png"></div>
					<div class="text">
						<div class="n">RX ONU<a href="/?do=signal&id='.$id.'" class="ont-graph-rx"><img src="../style/img/sfpsignal.png"></a></div>
						<div class="s"><span style="color:' . $olt_color . ';">' . $rx_value . '</span><b>dBm</b></div>
					</div>
				</div>';
		}	
		$rxolt_value = $core_snmp->get($oid_onu_rx_olt,true);
		if(isset($rxolt_value) && $rxolt_value!=false) {
			$rxolt_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $rxolt_value);
			$rxolt_value = number_format(floatval($rxolt_value) / 10, 2);
			$olt_color = $rxolt_value < -29 ? "red" : "#36b105";
			echo '<div class="dbm_block">
					<div class="i"><img src="../style/img/rx.png"></div>
					<div class="text">
						<div class="n">RX OLT</div>
						<div class="s"><span style="color:' . $olt_color . ';">' . $rxolt_value . '</span><b>dBm</b></div>
					</div>
				</div>';
		}
	}

	echo'</div>';	
	$core_snmp->close();	
}
}
}
?>