<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
function typeOnuBdcomGponEth($snmptype) {
	if ($snmptype == 1 || $snmptype == 17) {
		return ['img' => 'zte3.png', 'txt' => '10M', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 2 || $snmptype == 18) {
		return ['img' => 'zte5.png', 'txt' => '100M', 'st' => 'enable', 'status' => 'up'];	
	} elseif ($snmptype == 3 || $snmptype == 19) {
		return ['img' => 'zte6.png', 'txt' => '1G', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 4) {
		return ['img' => 'zte1.png', 'txt' => '10G', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 0) {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'down', 'status' => 'down'];
	} else {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'down', 'status' => 'down'];
	}
}
if($_POST['id']){
$result = array();
$resulttype  = array();
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$sql_ont = $pdo->prepare("SELECT * FROM onus WHERE idonu = :idonu");
$sql_ont->execute(['idonu' => $id]);
$data_ont = $sql_ont->fetch(PDO::FETCH_ASSOC);
if(!empty($data_ont['idonu'])){
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $data_ont['olt']]);
$data_olt = $sql_olt->fetch(PDO::FETCH_ASSOC);
$support_port_onu = many_port_onu($pdo);
if(!empty($data_olt['netip']) && !empty($data_olt['class']) && $data_olt['oidid']==2){
	$core_snmp = new SNMP(SNMP::VERSION_2C,$data_olt['netip'],$data_olt['snmpro']);
	$temp_status = $core_snmp->get('1.3.6.1.2.1.2.2.1.8.'.$data_ont['keyonu'],true);
	$real_onu_status = getsnmp_integer($temp_status);
}
if(!empty($data_olt['class']) && $data_olt['oidid']==2 && $real_onu_status != false){
	$oid_model = '1.3.6.1.4.1.3320.10.3.1.1.9.'.$data_ont['keyonu'];
	$oid_one_port = '1.3.6.1.4.1.3320.10.4.17.1.4.'.$data_ont['keyonu'];
	$onu_model = $core_snmp->get($oid_model,true);
	$get_onu_model = getsnmp_string($onu_model);
	if (isset($get_onu_model) && in_array(strtoupper($get_onu_model), array_map('strtoupper', $support_port_onu))) {
			$all_eth_bdcom = $core_snmp->walk($oid_one_port,true);	
	}else{
		$ethvalue = $core_snmp->get($oid_one_port.'.1',true);
		if(!$ethvalue){
			$ethvalue = $core_snmp->get('1.3.6.1.2.1.2.2.1.8.'.$data_ont['keyonu'],true);
			$eth_types = getsnmp_integer($ethvalue);
			if($eth_types==1){
				$eth_types = 2;
			}elseif($eth_types==2){
				$eth_types = 0;
			}
		}else{
			$eth_types = getsnmp_integer($ethvalue);
		}		
	}
	if(isset($all_eth_bdcom) && is_array($all_eth_bdcom)){
		$countport = 1;
		echo'<div class="zte_onu"><div class="zte_eth">';
		foreach ($all_eth_bdcom as $onu_eth) {
			$eth_types = getsnmp_integer($onu_eth);
			$onu_eth = typeOnuBdcomGponEth($eth_types);
			echo'<div class="link link4" data-llid="'.$data_ont['keyonu'].'"><div class="linkname">Eth'.$countport.' ('.$onu_eth['txt'].')</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div>';
			$countport ++ ;				
		}
		echo $tv_port.'</div>';
	}else{
		$onu_eth = typeOnuBdcomGponEth($eth_types);
		echo'<div class="zte_onu"><div class="zte_eth"><div class="link link4"><div class="linkname">Eth1 ('.$onu_eth['txt'].')</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div></div>';
	}	
	echo'<div class="zte_status"><div class="zte_gettype"><span>Port status:</span><span class="typeportstatus"><div class="eth_online"></div><div class="eth_name">Online</div><div class="eth_offline"></div><div class="eth_name">Offline</div><div class="eth_disable"></div><div class="eth_name">Disable</div></span></div></div></div>';
	echo'<div class="block_dbm">';
	$tx_value = @snmp2_get($data_olt['netip'], $data_olt['snmpro'], '1.3.6.1.4.1.3320.10.3.4.1.3.' . $data_ont['keyonu']);
	if ($tx_value) {
		$tx_value = preg_replace('/^.*?(?=INTEGER:)/i', '', $tx_value);
		$tx_value = preg_replace('/INTEGER:/', '', $tx_value);
		$tx_value = str_replace(['"', 'N/A'], ['', '0'], $tx_value);
		$tx_value = trim($tx_value);
		$tx_value = floatval($tx_value) / 10;
		$tx_value = number_format($tx_value, 2);
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
	$rx_value = @snmp2_get($data_olt['netip'], $data_olt['snmpro'], '1.3.6.1.4.1.3320.10.3.4.1.2.' . $data_ont['keyonu']);
	if ($rx_value) {
		$rx_value = preg_replace('/^.*?(?=INTEGER:)/i', '', $rx_value);
		$rx_value = preg_replace('/INTEGER:/', '', $rx_value);
		$rx_value = str_replace(['"', 'N/A'], ['', '0'], $rx_value);
		$rx_value = trim($rx_value);
		$rx_value = floatval($rx_value) / 10;
		$rx_value = number_format($rx_value, 2);
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
		$olt_color = $rx_value < -$minbad ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX ONU <a href="/?do=signal&id='.$id.'" class="ont-graph-rx"><img src="../style/img/sfpsignal.png"></a></div>
					<div class="s"><span style="color:' . $olt_color . ';">' . $rx_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}		
	$rxolt_value = @snmp2_get($data_olt['netip'], $data_olt['snmpro'], '1.3.6.1.4.1.3320.10.2.3.1.3.' . $data_ont['keyonu']);
	if ($rxolt_value) {
		$rxolt_value = preg_replace('/^.*?(?=INTEGER:)/i', '', $rxolt_value);
		$rxolt_value = preg_replace('/INTEGER:/', '', $rxolt_value);
		$rxolt_value = str_replace(['"', 'N/A'], ['', '0'], $rxolt_value);
		$rxolt_value = trim($rxolt_value);
		$rxolt_value = floatval($rxolt_value) / 10;
		$rxolt_value = number_format($rxolt_value, 2);
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:29);
		$olt_color = $rxolt_value < -$minbad ? "red" : "#36b105";
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