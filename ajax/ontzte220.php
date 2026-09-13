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
if(!empty($getOLT['netip']) && !empty($getOLT['class']) && $getONT['type']=='epon'){
snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
$snmpeth1 = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],"1.3.6.1.4.1.3902.1015.1010.1.1.3.2.1.6.".$getONT['keyonu'].".1");
echo'<div class="zte_onu"><div class="zte_eth">';
// ETH1
if(isset($snmpeth1)){
	$eth1 = typeOnuzte2Port($snmpeth1);
	if(isset($eth1)){
		echo'<div class="link link1"><div class="linkname">Eth1</div><img src="../style/img/'.$eth1['img'].'"><div class="linkstatus'.$eth1['status'].'"></div></div>';	
	}
}
echo'</div>';
echo'<div class="zte_status"><div class="zte_getstatus"><span>Port type:</span><span class="typortzte"><div class="eth_auto"></div><div class="eth_name">Auto</div><div class="eth_10"></div><div class="eth_name">10Mbps</div><div class="eth_100"></div><div class="eth_name">100Mbps</div><div class="eth_1000"></div><div class="eth_name">1G</div></span></div><div class="zte_gettype"><span>Port status:</span><span class="typeportstatus"><div class="eth_online"></div><div class="eth_name">Online</div><div class="eth_offline"></div><div class="eth_name">Offline</div><div class="eth_disable"></div><div class="eth_name">Disable</div></span></div></div></div>';

$control = '';
// eth1
if(isset($eth1)){
	if($eth1['st']=='enable')
		$control .='<div class="control_eth"><b>Eth1</b><div class="ctrl disable" onclick="portcontrol(\'disable\',\'zte6port\','.$id.',\'1\')">disable</div></div>';
	if($eth1['st']=='disable')
		$control .='<div class="control_eth"><b>Eth1</b><div class="ctrl enable" onclick="portcontrol(\'enable\',\'zte6port\','.$id.',\'1\')">enable</div></div>';
}
if($control){
	#echo'<div class="onuztecontrol">'.$control.'</div>';
}
echo'<div class="block_dbm">';
	$ont_rx_signal = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],'1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.5.'.$getONT['keyonu']);
	if(isset($ont_rx_signal)){
		$ont_rx_signal = preg_replace('~^.*?(?=STRING:)~i','',$ont_rx_signal);
		$ont_rx_signal = preg_replace ('/STRING:/','',$ont_rx_signal);
		$ont_rx_signal = str_replace('/','',trim(str_replace('"','',$ont_rx_signal)));
		if (preg_match('/N/i',$ont_rx_signal)){
			$ont_rx_signal = 0;
		} elseif(preg_match('/65535/i',$ont_rx_signal)) {
			$ont_rx_signal = 0;
		} else{
			$ont_rx_signal = sprintf("%.2f",$ont_rx_signal);			
		}
		$olt_color = $ont_rx_signal <-29?"red":"#36b105";
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text">';
		echo'<div class="n">RX ONU</div><div class="s"><span style="color:'.$olt_color.';">'.number_format ($ont_rx_signal ,2).'</span><b>dBm</b></div></div></div>';
	}
	$ont_tx_signal = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],'1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.4.'.$getONT['keyonu']);
	if(isset($ont_tx_signal)){
		$ont_tx_signal = preg_replace('~^.*?(?=STRING:)~i','',$ont_tx_signal);
		$ont_tx_signal = preg_replace('/STRING:/','',$ont_tx_signal);
		$ont_tx_signal = str_replace('/','',trim(str_replace('"','',$ont_tx_signal)));
		if (preg_match('/N/i',$ont_tx_signal)){
			$ont_tx_signal = 0;
		} elseif(preg_match('/65535/i',$ont_tx_signal)) {
			$ont_tx_signal = 0;
		} else{
			$ont_tx_signal = sprintf("%.2f",$ont_tx_signal);			
		}
		$onu_color = $ont_tx_signal<-29?"red":"#36b105";
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text">';
		echo'<div class="n">TX ONU</div><div class="s"><span style="color:'.$onu_color.';">'.number_format ($ont_tx_signal ,2).'</span><b>dBm</b></div></div></div>';
	}	
	echo'</div>';
}elseif($getONT['type']=='gpon'){
$snmpeth1 = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],"1.3.6.1.4.1.3902.1012.3.50.14.1.1.7.".$getONT['zte_idport'].'.'.$getONT['keyonu'].".1");
$snmpeth2 = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],"1.3.6.1.4.1.3902.1012.3.50.14.1.1.7.".$getONT['zte_idport'].'.'.$getONT['keyonu'].".2");
$snmpeth3 = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],"1.3.6.1.4.1.3902.1012.3.50.14.1.1.7.".$getONT['zte_idport'].'.'.$getONT['keyonu'].".3");
$snmpeth4 = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],"1.3.6.1.4.1.3902.1012.3.50.14.1.1.7.".$getONT['zte_idport'].'.'.$getONT['keyonu'].".4");
$snmptv = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],"1.3.6.1.4.1.3902.1012.3.50.19.1.1.1.".$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1');
echo'<div class="zte_onu"><div class="zte_eth">';
// ETH1
if($snmpeth1){
	$eth1 = typeOnuztePort($snmpeth1);
	if(isset($eth1)){
		echo'<div class="link link1"><div class="linkname">Eth1</div><img src="../style/img/'.$eth1['img'].'"><div class="linkstatus'.$eth1['status'].'"></div></div>';	
	}
}
// ETH2
if($snmpeth2){
	$eth2 = typeOnuztePort($snmpeth2);
	if(isset($eth2)){
		echo'<div class="link link2"><div class="linkname">Eth2</div><img src="../style/img/'.$eth2['img'].'"><div class="linkstatus'.$eth2['status'].'"></div></div>';	
	}
}
// ETH3
if($snmpeth3){
	$eth3 = typeOnuztePort($snmpeth3);
	if(isset($eth3)){
		echo'<div class="link link3"><div class="linkname">Eth3</div><img src="../style/img/'.$eth3['img'].'"><div class="linkstatus'.$eth3['status'].'"></div></div>';	
	}
}
// ETH4
if($snmpeth4){
	$eth4 = typeOnuztePort($snmpeth4);
	if(isset($eth4)){
		echo'<div class="link link4"><div class="linkname">Eth4</div><img src="../style/img/'.$eth4['img'].'"><div class="linkstatus'.$eth4['status'].'"></div></div>';	
	}
}
// TV port
if($snmptv){
	$tv = typeOnuzteVideoPort($snmptv);
	if(isset($tv)){	
		echo'<div class="linktvbord"></div><div class="linktv"><div class="linkname">TV</div><img src="../style/img/ztetv.png"><div class="linkstatus'.$tv['st'].'"></div></div>';
	}
}
echo'</div>';
echo'<div class="zte_status"><div class="zte_getstatus"><span>Port type:</span><span class="typortzte"><div class="eth_auto"></div><div class="eth_name">Auto</div><div class="eth_10"></div><div class="eth_name">10Mbps</div><div class="eth_100"></div><div class="eth_name">100Mbps</div><div class="eth_1000"></div><div class="eth_name">1G</div></span></div><div class="zte_gettype"><span>Port status:</span><span class="typeportstatus"><div class="eth_online"></div><div class="eth_name">Online</div><div class="eth_offline"></div><div class="eth_name">Offline</div><div class="eth_disable"></div><div class="eth_name">Disable</div></span></div></div></div>';

$control = '';
// eth1
if(isset($eth1)){
	if($eth1['st']=='enable')
		$control .='<div class="control_eth"><b>Eth1</b><div class="ctrl disable" onclick="ajaxzteonuport(\'off\','.$id.',\'1\')">disable</div></div>';
	if($eth1['st']=='disable')
		$control .='<div class="control_eth"><b>Eth1</b><div class="ctrl enable" onclick="ajaxzteonuport(\'on\','.$id.',\'1\')">enable</div></div>';
}
// eth2
if(isset($eth2) and is_array($eth2)){
	if($eth2['st']=='enable')
		$control .='<div class="control_eth"><b>Eth2</b><div class="ctrl disable" onclick="ajaxzteonuport(\'off\','.$id.',\'2\')">disable</div></div>';
	if($eth2['st']=='disable')
		$control .='<div class="control_eth"><b>Eth2</b><div class="ctrl enable" onclick="ajaxzteonuport(\'on\','.$id.',\'2\')">enable</div></div>';
}
// eth3
if(isset($eth3) and is_array($eth3)){
	if($eth3['st']=='enable')
		$control .='<div class="control_eth"><b>Eth3</b><div class="ctrl disable" onclick="ajaxzteonu(\'port\','.$id.',\'3\',\'2\',\'popup\')">disable</div></div>';
	if($eth3['st']=='disable')
		$control .='<div class="control_eth"><b>Eth3</b><div class="ctrl enable" onclick="ajaxzteonu(\'port\','.$id.',\'3\',\'1\',\'popup\')">enable</div></div>';
}// eth4
if(isset($eth4) and is_array($eth4)){
	if($eth4['st']=='enable')
		$control .='<div class="control_eth"><b>Eth4</b><div class="ctrl disable" onclick="ajaxzteonu(\'port\','.$id.',\'4\',\'2\',\'popup\')">disable</div></div>';
	if($eth4['st']=='disable')
		$control .='<div class="control_eth"><b>Eth4</b><div class="ctrl enable" onclick="ajaxzteonu(\'port\','.$id.',\'4\',\'1\',\'popup\')">enable</div></div>';
}
// tv	
if(isset($tv) and is_array($tv)){
	if($tv['st']=='up'){
		$control .='<div class="control_eth"><b>TV</b><div class="ctrl disable" onclick="ztetvport(\'ontvzteport1\',\''.$id.'\');">disable</div></div>';
	}else{
		$control .='<div class="control_eth"><b>TV</b><div class="ctrl enable" onclick="ztetvport(\'offtvzteport1\',\''.$id.'\');">enable</div></div>';
	}
}
if($control){
	echo'<div class="onuztecontrol">'.$control.'</div>';
}
echo'<div class="block_dbm">';
// ONU TX
$onu_tx = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],'.1.3.6.1.4.1.3902.1012.3.50.12.1.1.14.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1');
if($onu_tx){
	$onu_txc = preg_replace('~^.*?(?=INTEGER:)~i','',$onu_tx);
	$onu_txc1 = preg_replace ('/INTEGER:/','',$onu_txc);
	if ($onu_txc1*1 <30001) {
		$onu_txc1 = $onu_txc1*0.002 - 30.0;
	} else {
		if ($onu_txc1*1 < 665535)
			$onu_txc1 = ($onu_txc1-65535)*0.002 - 30.0;
		else $onu_txc1 = 0;
	}
	echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text">';
	echo'<div class="n">TX ONU</div><div class="s"><span>'.number_format ($onu_txc1 ,3).'</span><b>dBm</b></div></div></div>';
}
// ONU RX
	$olt_rx = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],'.1.3.6.1.4.1.3902.1015.1010.11.2.1.2.'.$getONT['zte_idport'].'.'.$getONT['keyonu']);
	if($olt_rx){
		$olt_rxc = preg_replace('~^.*?(?=INTEGER:)~i','',$olt_rx);
		$olt_rxc1 = preg_replace ('/INTEGER:/','',$olt_rxc);
		$olt_rxc1 = $olt_rxc1/1000;
		$olt_color = $olt_rxc1 <-29?"red":"#36b105";
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text">';
		echo'<div class="n">RX OLT</div><div class="s"><span style="color:'.$olt_color.';">'.number_format ($olt_rxc1 ,3).'</span><b>dBm</b></div></div></div>';
	}
	$onu_rx = @snmp2_get($getOLT['netip'],$getOLT['snmpro'],'.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1');
	if($onu_rx){
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
		echo'<div class="n">RX ONU</div><div class="s"><span style="color:'.$olt_color.';">'.number_format ($onu_rxc1 ,3).'</span><b>dBm</b></div></div></div>';
	}
	echo'</div>';
}
}
}
if(isset($getONT['idonu']) && !empty($getONT['idonu']) && isset($onu_rxc1)){
	$db->SQLupdate('onus',['rx'=>$onu_rxc1],['idonu'=>$getONT['idonu']]);
}
?>