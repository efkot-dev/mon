<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$getONT = $db->Fast('onus','*',['idonu' => $id]);
if($getONT['type']=='epon'){
	die('');
}
$get_onu_vlan = array(
	'id'=>$getONT['olt'],'do'=>'oid',
	'oid'=> vsprintf('1.3.6.1.4.1.3902.1082.500.20.2.4.63.1.4.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
);
if(!empty($getONT['idonu'])){
$getOLT = $db->Fast('switch','*',['id' => $getONT['olt']]);
if(!empty($getOLT['netip']) && !empty($getOLT['class']) && $getONT['type']=='gpon'){
$get_status = array(
	'id'=>$getONT['olt'],
	'do'=>'oid',
	'oid'=> vsprintf('1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.2.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
);	
$result_status = api__($config['monitorapi'],$get_status);
$status_onu_snmp = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$result_status['result']);
if(isset($status_onu_snmp) && $status_onu_snmp==1){
	snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
	$snmpeth1 = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],"1.3.6.1.4.1.3902.1082.500.20.2.3.2.1.7.".$getONT['zte_idport'].'.'.$getONT['keyonu'].".1");
	echo'<div class="zte_onu"><div class="zte_eth">';
	if($snmpeth1){
		$eth1 = typeOnuzte6Port($snmpeth1);
		if(isset($eth1)){
			echo'<div class="link link1"><div class="linkname">Eth1</div><img src="../style/img/'.$eth1['img'].'"><div class="linkstatus'.$eth1['status'].'"></div></div>';	
		}
	}
	echo'</div>';
	echo'<div class="zte_status"><div class="zte_getstatus"><span>Port type:</span><span class="typortzte"><div class="eth_auto"></div><div class="eth_name">Auto</div><div class="eth_10"></div><div class="eth_name">10Mbps</div><div class="eth_100"></div><div class="eth_name">100Mbps</div><div class="eth_1000"></div><div class="eth_name">1G</div></span></div><div class="zte_gettype"><span>Port status:</span><span class="typeportstatus"><div class="eth_online"></div><div class="eth_name">Online</div><div class="eth_offline"></div><div class="eth_name">Offline</div><div class="eth_disable"></div><div class="eth_name">Disable</div></span></div></div></div>';
	$control = '';
	if(isset($eth1)){
		if($eth1['st']=='enable')
			$control .='<div class="control_eth"><b>Eth1</b><div class="ctrl disable" onclick="portcontrol(\'disable\',\'zte6port\','.$id.',\'1\')">'.$lang['disable'].'</div></div>';
		if($eth1['st']=='disable')
			$control .='<div class="control_eth"><b>Eth1</b><div class="ctrl enable" onclick="portcontrol(\'enable\',\'zte6port\','.$id.',\'1\')">'.$lang['enable'].'</div></div>';
	}
	if($control){
		echo'<div class="onuztecontrol">'.$control.'</div>';
	}
	echo'<div class="block_dbm">';
		$olt_rx = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],'1.3.6.1.4.1.3902.1082.500.1.2.4.2.1.2.'.$getONT['zte_idport'].'.'.$getONT['keyonu']);
		if($olt_rx!=false){
			$olt_rxc = preg_replace('~^.*?(?=INTEGER:)~i','',$olt_rx);
			$olt_rxc1 = preg_replace ('/INTEGER:/','',$olt_rxc);
			$olt_rxc1 = $olt_rxc1/1000;
			$olt_color = $olt_rxc1 <-29?"red":"#36b105";
			echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text">';
			echo'<div class="n">RX OLT</div><div class="s"><span style="color:'.$olt_color.';">'.number_format ($olt_rxc1,2).'</span><b>dBm</b></div></div></div>';
		}
		$onu_rx = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],'1.3.6.1.4.1.3902.1082.500.20.2.2.2.1.10.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1');
		if($onu_rx!=false){
			$onu_rxc = preg_replace('~^.*?(?=INTEGER:)~i','',$onu_rx);
			$onu_rxc1 = preg_replace ('/INTEGER:/','',$onu_rxc);
			   if ($onu_rxc1*1 <30001) {
				  $onu_rxc1=$onu_rxc1*0.002 - 30.0;
			   } else {
				   if ($onu_rxc1*1 < 665535)
					   $onu_rxc1=($onu_rxc1-65535)*0.002 - 30.0;
				   else $onu_rxc1 = 0;
			   }
			$onu_color = $onu_rxc1<-29?"red":"#36b105";
			echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text">';
			echo'<div class="n">RX ONU</div><div class="s"><span style="color:'.$olt_color.';">'.number_format ($onu_rxc1,2).'</span><b>dBm</b></div></div></div>';
		}		
		$tx_onu = snmp2_get($getOLT['netip'],$getOLT['snmpro'],'1.3.6.1.4.1.3902.1082.500.20.2.2.2.1.14.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1');
		if($tx_onu!=false){
			$tx_onu = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '',$tx_onu);
			$tx_onu = number_format(floatval($tx_onu)/10000,2);
			echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text">';
			echo'<div class="n">TX ONU</div><div class="s"><span style="color:'.($tx_onu<-29?"red":"#36b105").';">'.number_format ($tx_onu ,2).'</span><b>dBm</b></div></div></div>';
		}
		echo'</div>';
}
	echo'<div id="onu_detail">';
	if(isset($status_onu_snmp) && $status_onu_snmp==1){
		$get_time_reason = array(
			'id'=>$getONT['olt'],'do'=>'oid',
			'oid'=> vsprintf('1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.5.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
		);	
		$result_time_reason = api__($config['monitorapi'],$get_time_reason);
		if (isset($result_time_reason['result']) && !empty($result_time_reason['result'])) {
			preg_match('/:(.*)/', $result_time_reason['result'], $tm);
			$time_reason = preg_match('/Hex-STRING/i', $result_time_reason['result']) ? $tm[1] : $result_time_reason['result'];
			echo '
				<div class="block_onu uvlan">
					<div class="n">'.$lang['uptime'].'</div>
					<div class="v">' . aftertime(hexTOdate($time_reason)) . '</div>
				</div>';
		}
	}else{
		$get_time_reason = array(
			'id'=>$getONT['olt'],'do'=>'oid',
			'oid'=> vsprintf('1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.6.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
		);	
		$result_time_off = api__($config['monitorapi'],$get_time_reason);
		if (isset($result_time_off['result']) && !empty($result_time_off['result'])) {
			$time_reason = $result_time_off['result'];
			echo '
				<div class="block_onu uvlan">
					<div class="n">'.$lang['offline'].'</div>
					<div class="v">' . aftertime(hexTOdate($time_reason)) . '</div>
				</div>';
		}		
	}
	$get_type_onu = array(
		'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.12.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
	);	
	$result_type_onu = api__($config['monitorapi'],$get_type_onu);	
	if (isset($result_type_onu['result']) && !empty($result_type_onu['result'])) {
		$types = array(1 => 'GPON',2 => 'XG-PON',3 => 'XGS-PON',255 => 'unknown');
		preg_match('/:(.*)/', $result_type_onu['result'], $tm);
		$type_onu = preg_match('/INTEGER/i', $result_type_onu['result']) ? $tm[1] : $result_type_onu['result'];
		echo '
			<div class="block_onu count_mac">
				<div class="n">Тип Onu</div>
				<div class="v">' . $types[$type_onu] . '</div>
			</div>';
	}	
	echo '</div>';
	if(isset($status_onu_snmp) && $status_onu_snmp==1){	
		sleep(2);
		echo'<script type="text/javascript">
			ajaxfdbonuzte6gpon('.$getONT['idonu'].');
		</script>';
	}
}
}
}
?>