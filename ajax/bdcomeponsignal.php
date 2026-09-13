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
$dataportstatus = array('1'=>'enable','2'=>'disable','3'=>'none');
$support_port_onu = many_port_onu($pdo);
$support_tv_onu = array('D431','2011','134z','XPAG');
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $data_ont['olt']]);
$data_olt = $sql_olt->fetch(PDO::FETCH_ASSOC);
$core_snmp = new SNMP(SNMP::VERSION_2C,$data_olt['netip'],$data_olt['snmpro']);
$temp_status = $core_snmp->get('1.3.6.1.2.1.2.2.1.8.'.$data_ont['keyonu'],true);
$real_onu_status = getsnmp_integer($temp_status);
$control = '';
$tv_port = '';
if(!empty($data_olt['class']) && $data_olt['oidid']==1 && $real_onu_status != false){
	$oid_model = '1.3.6.1.4.1.3320.101.10.1.1.2.'.$data_ont['keyonu'];
	$oid_tv_port = '1.3.6.1.4.1.3320.101.10.30.1.2.'.$data_ont['keyonu'];
	$oid_many_port = '1.3.6.1.4.1.3320.101.12.1.1.8.'.$data_ont['keyonu'];
	$oid_tv_signal = '1.3.6.1.4.1.3320.101.10.31.1.2.'.$data_ont['keyonu'];
	$oid_one_port = '1.3.6.1.4.1.3320.101.12.1.1.8.'.$data_ont['keyonu'].'.1';
	$oid_vlan_port = '1.3.6.1.4.1.3320.101.12.1.1.3.'.$data_ont['keyonu'];
	$oid_tx_sfp = '1.3.6.1.4.1.3320.101.107.1.3.'.$data_ont['portolt'];
	$oid_onu_tx = '1.3.6.1.4.1.3320.101.10.5.1.6.'.$data_ont['keyonu'];
	$oid_onu_rx = '1.3.6.1.4.1.3320.101.10.5.1.5.'.$data_ont['keyonu'];
	$oid_onu_rx_olt = '1.3.6.1.4.1.3320.101.108.1.3.'.$data_ont['keyonu'];
	$vlan_array = [];
	$onu_model = $core_snmp->get($oid_model,true);
	if (isset($onu_model)) {
		$get_onu_model = getsnmp_string($onu_model);
		if (isset($get_onu_model) && in_array(strtoupper($get_onu_model), array_map('strtoupper', $support_tv_onu))) {
			$temp_tvport = $core_snmp->get($oid_tv_port,true);
			$tvport = getsnmp_integer($temp_tvport);
		}
		if (isset($get_onu_model) && in_array(strtoupper($get_onu_model), array_map('strtoupper', $support_port_onu))) {
			$all_eth_bdcom = $core_snmp->walk($oid_many_port,true);	
		}
	} 
	if(isset($tvport)){
		$result_tv = typeOnuBdcomVideoPort($tvport);
		if(!empty($result_tv['st'])){
			$rxcatv_value = $core_snmp->get($oid_tv_signal,true);
			$rxcatv_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $rxcatv_value);
			$rxcatv_value = str_replace(['"', 'N/A', '65535'], ['', '0', '0'], $rxcatv_value);
			$rxcatv_value = trim($rxcatv_value);
			$tv_port = '<div class="linktvbord"></div><div class="linktv"><div class="linkname">CATV</div><img src="../style/img/ztetv.png"><div class="linkstatus'.$result_tv['st'].'"></div></div>';
		}
	}
	$eth_onu = [];
	if(isset($all_eth_bdcom) && is_array($all_eth_bdcom)){
		$countport = 1;
		echo'<div class="zte_onu"><div class="zte_eth">';
		foreach ($all_eth_bdcom as $onu_eth) {
			$onu_eth = typeOnubdcomPort($onu_eth);
			echo'<div class="link link4" data-llid="'.$data_ont['keyonu'].'"><div class="linkname">Eth'.$countport.'</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div>';
			$eth_onu[$countport]['status'] = $onu_eth['status'];			
			$eth_onu[$countport]['type'] = 'eth';	
			$countport ++ ;				
		}
		echo $tv_port.'</div>';
	}else{
		$ethvalue = $core_snmp->get($oid_one_port,true);
		if(isset($ethvalue) && $ethvalue != false){
			echo'<div class="zte_onu"><div class="zte_eth">';
			$onu_eth = typeOnubdcomPort($ethvalue);
			echo'<div class="link link4" data-llid="'.$data_ont['keyonu'].'"><div class="linkname">Eth1</div><img src="../style/img/'.$onu_eth['img'].'"><div class="linkstatus'.$onu_eth['status'].'"></div></div>';
			echo $tv_port.'</div>';
			$eth_onu[1]['status'] = $onu_eth['status'];			
			$eth_onu[1]['type'] = 'eth';	
		}		
	}	
	echo'<div class="zte_status"><div class="zte_gettype"><span>Port status:</span><span class="typeportstatus"><div class="eth_online"></div><div class="eth_name">Online</div><div class="eth_offline"></div><div class="eth_name">Offline</div><div class="eth_disable"></div><div class="eth_name">Disable</div></span></div></div></div>';
	if(isset($eth_onu) && !empty($eth_onu)){
		foreach ($eth_onu as $onu_eth_id => $value) {
			$onu_vlan = $core_snmp->get($oid_vlan_port.'.'.$onu_eth_id,true);
			if(isset($onu_vlan) && $access->get('vlan_edit')){
				$vlan = getsnmp_integer($onu_vlan);
				$vlan_array[$onu_eth_id]['vlan'] = $vlan;
				$vlan_array[$onu_eth_id]['eth'] = $onu_eth_id;
			}
		}
	}
	if(isset($eth_onu) && !empty($eth_onu)){
		foreach ($eth_onu as $onu_eth_id => $value) {
			$control .='<div class="control_eth">
			<b>Eth'.$onu_eth_id.'</b>'.(isset($vlan_array[$onu_eth_id]['vlan'])?'<span class="onu_vlan">Vlan:<span>'.$vlan_array[$onu_eth_id]['vlan'].'</span></span>':'').'';
			if($access->get('rebootonu') && !empty($data_olt['password'])){
				$control .='<div class="ctrl '.(isset($value['status']) && $value['status']=='up'?'disable':'enable').'" onclick="funcpanel(\'bdcomeponport'.(isset($value['status']) && $value['status']=='up'?'down':'up').'\','.$data_ont['idonu'].','.$onu_eth_id.')">'.$lang['ports_'.$value['status']].'</div>';
			}
			$control .='</div>';
		}
	}
	if(isset($control) && $control != false){
		echo'<div class="onuztecontrol">'.$control.'</div>';
	}
	echo'<div class="block_dbm">';
	if(isset($eth_onu) && !empty($eth_onu) && !empty($data_olt['snmprw']) && $access->get('vlan_edit')){
		echo'<div class="dbm_block"><div class="i"><img src="../style/img/manager_vlan.png"></div><div class="text"><div class="n">Vlan</div>';
		echo'<div class="s"><span onclick="showHideBlock(\'blockvlan\',\'knopka2\')" id="knopka2" class="knopka">'.$lang['pt_vlan'].'</span></div></div></div>';
	}
	if(isset($real_onu_status) && $real_onu_status == 1){
		$tx_sfp = $core_snmp->get($oid_tx_sfp,true);
			if(isset($tx_sfp) && $tx_sfp!=false) {
				$tx_sfp = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $tx_sfp);
				$tx_sfp = number_format(floatval($tx_sfp) / 10, 2);
				$olt_sfp_color = $tx_sfp < 5 ? "red" : "#36b105";
				echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">TX SFP</div>';
				echo'<div class="s"><span style="color:' . $olt_sfp_color . ';">' . $tx_sfp . '</span><b>dBm</b></div></div></div>';
			}	
		$tx_value = $core_snmp->get($oid_onu_tx,true);
		if(isset($tx_value) && $tx_value!=false) {
			$tx_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $tx_value);
			$tx_value = number_format(floatval($tx_value) / 10, 2);
			$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:2);
			$olt_color = $tx_value > $minbad ? "red" : "#36b105";
			echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">TX ONU</div>';
			echo'<div class="s"><span style="color:' . $olt_color . ';">' . $tx_value . '</span><b>dBm</b></div></div></div>';
		}
		$rx_value = $core_snmp->get($oid_onu_rx,true);
		if(isset($rx_value) && $rx_value!=false) {
			$rx_value = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $rx_value);
			$rx_value = number_format(floatval($rx_value) / 10, 2);
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
	$controltv = '';
	if(isset($result_tv['st']) && !empty($data_olt['id']) && !empty($data_olt['username'])){
		if(isset($result_tv['st']) && $result_tv['st']=='up'){
			$controltv .= '<span class="port_down" onclick="tvportdown(\'bdcomeponcatv\',\''.$data_olt['id'].'\',\''.$data_ont['idonu'].'\',\'2\')">'.$lang['ports_up'].'</span>';	
		}
		if(isset($result_tv['st']) && $result_tv['st']=='disable'){
			$controltv .= '<span class="port_up" onclick="tvportdown(\'bdcomeponcatv\',\''.$data_olt['id'].'\',\''.$data_ont['idonu'].'\',\'1\')">'.$lang['ports_down'].'</span>';		
		}
	}
	if(isset($result_tv['st']) && !empty($result_tv['st']) && isset($rxcatv_value) && $rxcatv_value!=false){		
		$rxcatv_value = floatval($rxcatv_value)/10;
		$rxcatv_value_color = $rxcatv_value < -19 ? "red" : "#36b105";
		echo '<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">CATV</div>
		<div class="s"><span style="color:' . $rxcatv_value_color . ';">' . $rxcatv_value .$controltv. '</span></div></div></div>';
	}
	echo'</div>';	
	if(isset($eth_onu) && !empty($eth_onu) && !empty($data_olt['snmprw'])){
		echo'<div id="blockvlan" style="display: none;"><div class="blockvlan">';
		foreach ($eth_onu as $onu_eth_id => $value) {
			if(isset($onu_eth_id) && isset($vlan_array[$onu_eth_id]['vlan'])){
				$vlan = $vlan_array[$onu_eth_id]['vlan'];
				if(isset($vlan)){
					echo ont_label('<img class="man_vlan" src="../style/img/eth.png">'.$lang['bdcom_vlan'].' eth'.$onu_eth_id,$vlan.(!empty($data_olt['snmprw'])?'<span id="load-edit-vlan-'.$onu_eth_id.'"></span><span id="edit-vlan-'.$onu_eth_id.'" ></span><span class="ont-btn" id="btn-edit-vlan-'.$onu_eth_id.'" onclick="bdcomvlanonu_ajax('.$data_ont['olt'].','.$data_ont['idonu'].',\'bdcomformeditonu\','.$onu_eth_id.')">'.$lang['edit'].'</span>':''));
				}	
			}			
		}
		echo'</div></div>';
		if(isset($vlan_array) && isset($vlan_array[1]['vlan']) && !empty($vlan_array[1]['vlan'])){
			$db->query("UPDATE onus SET wan = '{$vlan_array[1]['vlan']}' WHERE idonu  = {$data_ont['idonu']}");
		}
	}
	$core_snmp->close();	
}

if(isset($rxolt_value)){

}

sleep(1);
echo'
	<script type="text/javascript">
		ajaxbdcomepon('.$data_ont['idonu'].');
</script>';
}
}
?>