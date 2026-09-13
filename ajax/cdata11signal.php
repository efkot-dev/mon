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
$switch = $db->Fast('switch','*',['id' => $getONT['olt']]);
// many port onu
$llid = $getONT['zte_idport'].'.'. $getONT['keyonu'];
$public = $switch['snmpro'];
$ip = $switch['netip'];
$support_port_onu = array('302.E','3024','1112','1005','208B','417R','X640');
// get snmp onu status
$snmp_return_status = @snmp2_get($ip,$public,'1.3.6.1.4.1.34592.1.3.4.1.1.11.1.'.$llid,100000,5);
$real_onu_status = @getsnmp_integer($snmp_return_status);
if(isset($real_onu_status) && $real_onu_status == 3 && $switch['oidid']==13){
	// get snmp onu model 
	$onu_model = @snmp2_get($ip,$public,'1.3.6.1.4.1.34592.1.3.4.1.1.5.1.'.$llid,100000,5);
	if ($onu_model!=false) {
		$get_onu_model = getsnmp_string($onu_model);
	}
	# БАГАТО ПОРТОВІ ОНУ
	if (isset($get_onu_model) && in_array(strtoupper($get_onu_model), array_map('strtoupper', $support_port_onu))) {
		$all_eth_cdata = @snmp2_real_walk($ip,$public,'1.3.6.1.4.1.34592.1.3.4.3.1.3.1.'.$llid);	
		#$status_eth_cdata = @snmp2_real_walk($ip,$public,'1.3.6.1.4.1.34592.1.3.4.3.1.5.1.'.$llid);			
		if(isset($status_eth_cdata) && $status_eth_cdata!=false && is_array($status_eth_cdata)){
			foreach ($status_eth_cdata as $onu_eth_st) {
				$onu_stats = typeOnubdcomPort($onu_eth_st);
			}
		}
	}
	$eth_onu = [];
	if(isset($all_eth_cdata) && $all_eth_cdata!=false && is_array($all_eth_cdata)){
		$countport = 1;
		echo'<div class="zte_onu"><div class="zte_eth">';
		foreach ($all_eth_cdata as $onu_eth) {
			$onu_eth = typeOnubdcomPort($onu_eth);
			echo'<div class="link link4"><div class="linkname">Eth'.$countport.'</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div>';
			$eth_onu[$countport]['status'] = $onu_eth['status'];			
			$eth_onu[$countport]['type'] = 'eth';	
			$countport ++ ;				
		}
		echo '</div>';
	}else{
		$ethvalue = @snmp2_get($ip,$public,'1.3.6.1.4.1.34592.1.3.4.3.1.4.1.'.$llid.'.1',100000, );
		if(isset($ethvalue)){
			echo'<div class="zte_onu"><div class="zte_eth">';
			$onu_eth = typeOnubdcomPort($ethvalue);
			echo'<div class="link link4"><div class="linkname">Eth1</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div>';
			echo $tv_port.'</div>';
			$eth_onu[1]['status'] = $onu_eth['status'];			
			$eth_onu[1]['type'] = 'eth';	
		}		
	}	
	echo'<div class="zte_status"><div class="zte_gettype"><span>Port status:</span><span class="typeportstatus"><div class="eth_online"></div><div class="eth_name">Online</div><div class="eth_offline"></div><div class="eth_name">Offline</div><div class="eth_disable"></div><div class="eth_name">Disable</div></span></div></div></div>';

	echo'<div class="block_dbm">';
	$tx_value = snmp2_get($switch['netip'], $switch['snmpro'],'1.3.6.1.4.1.34592.1.3.4.1.1.37.1.'.$getONT['zte_idport'].'.'. $getONT['keyonu']);
	if ($tx_value) {
		$tx_value = preg_replace('/^.*?(?=INTEGER:)/i', '', $tx_value);
		$tx_value = preg_replace('/INTEGER:/', '', $tx_value);
		$tx_value = str_replace(['"', 'N/A'], ['', '0'], $tx_value);
		if ($tx_value == 0 OR !$tx_value OR $tx_value == NULL) {
			$tx_value = 0;
		} else {
			$tx_value = sprintf("%.2f",(10 * log10($tx_value) - 40));  
		}
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
	$rx_value = @snmp2_get($switch['netip'], $switch['snmpro'], '1.3.6.1.4.1.34592.1.3.4.1.1.36.1.'.$getONT['zte_idport'].'.'. $getONT['keyonu']);
	if ($rx_value) {
		$rx_value = preg_replace('/^.*?(?=INTEGER:)/i', '', $rx_value);
		$rx_value = preg_replace('/INTEGER:/', '', $rx_value);
		$rx_value = str_replace(['"', 'N/A'], ['', '0'], $rx_value);
		if ($rx_value == 0 OR !$rx_value OR $rx_value == NULL) {
			$rx_value = 0;
		} else {
			$rx_value = sprintf("%.2f",(10 * log10($rx_value) - 40));  
		}
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
	$rx_olt_value = @snmp2_get($switch['netip'], $switch['snmpro'], '1.3.6.1.4.1.34592.1.3.4.1.1.36.1.'.$getONT['zte_idport'].'.'. $getONT['keyonu']);
	if ($rx_olt_value!=false) {
		$rx_olt_value = preg_replace('/^.*?(?=INTEGER:)/i', '', $rx_olt_value);
		$rx_olt_value = preg_replace('/INTEGER:/', '', $rx_olt_value);
		$rx_olt_value = str_replace(['"', 'N/A'], ['', '0'], $rx_olt_value);
		if ($rx_olt_value == 0 OR !$rx_olt_value OR $rx_olt_value == NULL) {
			$rx_olt_value = 0;
		} else {
			$rx_olt_value = sprintf("%.2f",(10 * log10($rx_olt_value) - 40));  
		}
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
		$olt_color = $rx_olt_value < -$minbad ? "red" : "#36b105";
		echo '<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX OLT</div>
					<div class="s"><span style="color:' . $olt_color . ';">' . $rx_olt_value . '</span><b>dBm</b></div>
				</div>
			</div>';
	}		

	echo'</div>';
}
}
}
?>