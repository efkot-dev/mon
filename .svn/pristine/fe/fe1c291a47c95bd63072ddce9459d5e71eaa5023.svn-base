<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

function is($data){
	$tmp_value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|\s|=|"/', '', $data);
	return trim($tmp_value);	
}
function sfp_mikrotik($res){
	$data = array();
	$oid_rx = '1.3.6.1.4.1.14988.1.1.19.1.1.9.'.$res['llid'];
	$oid_tx = '1.3.6.1.4.1.14988.1.1.19.1.1.10.'.$res['llid'];
	$current_rx_result = @snmp2_get($res['netip'],$res['snmpro'],$oid_rx);
	$current_tx_result = @snmp2_get($res['netip'],$res['snmpro'],$oid_tx);
	if ($current_tx_result !== false && $current_rx_result !== false) {
		$current_rx = is($current_rx_result);
		$rx = intval($current_rx) / 1000;
		$current_tx = is($current_tx_result);
		$tx = intval($current_tx) / 1000;
	}
	if ($tx !== false || $rx !== false) {
		$data = array('id'=>$res['id'], 'rx'=>sprintf('%.2f',$rx),'tx'=>sprintf('%.2f',$tx));
	}
	return $data;
}
?>