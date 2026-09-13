<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$getONT = $db->Fast('onus','*',['idonu' => $id]);
$support_count_port = 1;
$support_port_onu = many_port_onu($pdo);
$vlan_modes = array(1 => "Transparent",2 => "Tag mode",3 => "Translation mode",4 => "Trunk mode",5 => "Hybrid mode");
$oid_epon_status_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.7.4.1.17.%s',[$getONT['keyonu']])
);
$oid_epon_model_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.7.4.1.5.%s',[$getONT['keyonu']])
);
$oid_epon_vlanmode_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.1.1.10.1.1.1.%s.1',[$getONT['keyonu']])
);
$oid_epon_rx_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.5.%s',[$getONT['keyonu']])
);
$oid_epon_tx_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.4.%s',[$getONT['keyonu']])
);
$oid_epon_vlan_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.1.1.10.2.1.1.%s.1',[$getONT['keyonu']])
);
$oid_epon_eth1_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.1.1.5.1.2.%s.1',[$getONT['keyonu']])
);
$oid_epon_eth1_admin_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.1.3.1.1.1.%s.1',[$getONT['keyonu']])
);
$oid_epon_get_admin_onu = array(
'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1015.1010.1.7.4.1.6.%s',[$getONT['keyonu']])
);
if($getONT['type']=='epon'){
	$getOLT = $db->Fast('switch','*',['id' => $getONT['olt']]);
	$core_snmp = new SNMP(SNMP::VERSION_2C,$getOLT['netip'],$getOLT['snmpro']);
	$llid = $getONT['keyonu'];
	$admin_status_onu = $core_snmp->get('1.3.6.1.4.1.3902.1015.1010.1.7.4.1.6.'.$llid,true);
	$admin_status_onu_snmp = getsnmp_integer($admin_status_onu);
	$status_onu = $core_snmp->get('1.3.6.1.4.1.3902.1015.1010.1.7.4.1.17.'.$llid,true);
	$status_onu_snmp = getsnmp_integer($status_onu);	
	$model_onu = $core_snmp->get('1.3.6.1.4.1.3902.1015.1010.1.7.4.1.5.'.$llid,true);
	$get_onu_model = getsnmp_string($model_onu);	
	if (isset($get_onu_model) && in_array(strtoupper($get_onu_model), array_map('strtoupper', $support_port_onu))) {
		$support_count_port = 4;
	}
	if(isset($admin_status_onu_snmp) && $admin_status_onu_snmp==2){	
		echo'
		<div class="onu_reasons view_onu_none"><b>Admin Status</b>Disable</div>
			<script>        
            document.getElementById(\'zte_onu_enable\').style.display = "inline-block";
			</script>
		';
	}else{
		echo'
			<script>        
            document.getElementById(\'zte_onu_disable\').style.display = "inline-block";
			</script>
		';
	}	
	echo'<div class="zte_onu">
			<div class="zte_eth">';
	if($support_count_port==1){
		$eth_1_onu = $core_snmp->get('1.3.6.1.4.1.3902.1015.1010.1.1.1.5.1.2.'.$llid.'.1',true);
		$eth_1_status = getsnmp_integer($eth_1_onu);
		if($eth_1_status==2){
			$eth_1_onu_speed = $core_snmp->get('1.3.6.1.4.1.3902.1015.1010.1.1.3.2.1.6.'.$llid.'.1',true);
			$eth_1_speed = getsnmp_integer($eth_1_onu_speed);
			$eth_img = ($eth_1_speed==1 ? 'zte6.png' : 'zte5.png');
			$status_eth_img = 'linkstatusup';
			$eth_status = 'enable';
		}else{
			$eth_admin_onu = $core_snmp->get('1.3.6.1.4.1.3902.1015.1010.1.1.3.1.1.1.'.$llid.'.1',true);
			$eth_1_status = getsnmp_integer($eth_admin_onu);
			if($eth_1_status==2){
				$eth_img = 'zte0.png';
				$status_eth_img = 'linkstatusdown';
				$eth_status = 'down';
			}else{
				$eth_img = 'zte0.png';
				$status_eth_img = 'linkstatusdisable';
				$eth_status = 'disable';
			}
		}
		$eth_onu_array[1]['status'] = $eth_status;			
		$eth_onu_array[1]['type'] = 'eth';
		echo '<div class="link 1"><div class="linkname">Eth1</div><img src="../style/img/'.$eth_img.'"><div class="'.$status_eth_img.'"></div></div>';
	}else{
		for ($i_port = 1; $i_port <= 4; $i_port++) {
			$port_eth = array('do' =>'oid','id'=>$getONT['olt'],'oid' => '1.3.6.1.4.1.3902.1015.1010.1.1.1.5.1.2.'.$getONT['keyonu'].'.'.$i_port);
			$eth_onu = api__($config['monitorapi'],$port_eth);
			$eth_status_oid = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $eth_onu['result']);
			$eth_img = 'zte0.png';
			$status_eth_img = '';
			if ($eth_status_oid == 2) {
				$eth_img = 'zte5.png';
				$status_eth_img = 'linkstatusup';
				$eth_status = 'enable';
			} else {
				$eth_admin_onu = api__($config['monitorapi'], ['id'=>$getONT['olt'],'do' => 'oid', 'oid' => vsprintf('1.3.6.1.4.1.3902.1015.1010.1.1.3.1.1.1.%s.'.$i, [$getONT['keyonu']])]);
				$eth_admin_status_oid = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $eth_admin_onu['result']);
				if ($eth_admin_status_oid == 2) {
					$eth_img = 'zte0.png';
					$status_eth_img = 'linkstatusdown';
					$eth_status = 'down';
				} else {
					$status_eth_img = 'linkstatusdisable';
					$eth_status = 'disable';
				}
			}
			echo '<div class="link '.$i_port.'"><div class="linkname">Eth'.$i_port.'</div><img src="../style/img/'.$eth_img.'"><div class="'.$status_eth_img.'"></div></div>';
			$eth_onu_array[$i_port]['status'] = $eth_status;			
			$eth_onu_array[$i_port]['type'] = 'eth';	
		}		
	}
	echo'</div>';
	echo'<div class="zte_status">
			<div class="zte_getstatus">
				<span>Port type:</span>
				<span class="typortzte">
					<div class="eth_auto"></div>
					<div class="eth_name">Auto</div>
					<div class="eth_10"></div><div class="eth_name">10Mbps</div>
					<div class="eth_100"></div><div class="eth_name">100Mbps</div>
					<div class="eth_1000"></div><div class="eth_name">1G</div>
				</span>
			</div>
			<div class="zte_gettype">
				<span>Port status:</span>
				<span class="typeportstatus">
					<div class="eth_online"></div><div class="eth_name">Online</div>
					<div class="eth_offline"></div><div class="eth_name">Offline</div>
					<div class="eth_disable"></div><div class="eth_name">Disable</div>
				</span>
			</div>
		</div>
	</div>
	<div class="onuztecontrol">';
	if($support_count_port==1){
		$vlav_onu = api__($config['monitorapi'],$oid_epon_vlan_onu);
		$result_vlan_onu = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$vlav_onu['result']);
		echo'<div class="control_eth"><b>Eth1</b><span class="onu_vlan">Vlan:<span> '.$result_vlan_onu.'</span></span></div>';
	}else{
		for ($i_v_port = 1; $i_v_port <= 4; $i_v_port++) {
			$vl = ['id'=>$getONT['olt'],'do'=>'oid','oid'=>'1.3.6.1.4.1.3902.1015.1010.1.1.1.10.2.1.1.'.$getONT['keyonu'].'.'.$i_v_port];
			$vlav_onu = api__($config['monitorapi'],$vl);
			$result_vlan_onu = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$vlav_onu['result']);
			echo'
			<div class="control_eth">
				<b>Eth'.$i_v_port.'</b>
				<span class="onu_vlan">Vlan:
					<span> '.$result_vlan_onu.'</span>
				</span>';
			echo'</div>';
		}
	}
	echo'</div>';
	echo'<div class="block_dbm">';
	if($support_count_port>0){
		$vlan_mode_onu = api__($config['monitorapi'],$oid_epon_vlanmode_onu);
		$vlanMode = str_replace(['INTEGER:',' ', '"'], ['','',''], $vlan_mode_onu['result']);
		echo'<div class="dbm_block">
			<div class="i"><img src="../style/img/manager_vlan.png"></div>
			<div class="text">
				<div class="n">Vlan Mode</div>
				<div class="s">
				<span id="knopka2" class="knopka"">'.(isset($vlan_modes[$vlanMode])?$vlan_modes[$vlanMode]:'N/A').'</span>
				</div>
			</div>
		</div>';
	}
	if(isset($status_onu_snmp) && $status_onu_snmp==3){
		$signal_rx_onu = api__($config['monitorapi'],$oid_epon_rx_onu);
		if(isset($signal_rx_onu['result'])){
			$rx_onu = str_replace(['STRING:',' ', '"'], ['','',''], $signal_rx_onu['result']);
			$rx_onu = intval($rx_onu);
			echo'
			<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX ONU</div>
					<div class="s">
					<span style="color:'.($rx_onu < -29 ? "red":"#36b105").';">'.number_format($rx_onu,2).'</span><b>dBm</b>
				</div>
				</div>
			</div>';
		}	
		$signal_tx_onu = api__($config['monitorapi'],$oid_epon_tx_onu);
		if(isset($signal_tx_onu['result'])){
			$tx_onu = str_replace(['STRING:',' ', '"'], ['','',''], $signal_tx_onu['result']);
			echo'
			<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">TX ONU</div>
					<div class="s">
					<span style="color:'.($tx_onu < 1.7 ? "red":"#36b105").';">'.number_format($tx_onu,2).'</span><b>dBm</b>
				</div>
				</div>
			</div>';
		}
	}
	echo'</div>';
	if(isset($status_onu_snmp) && $status_onu_snmp==3){	
		sleep(1);
		echo'<script type="text/javascript">
			ajaxfdbonuzte3epon('.$getONT['idonu'].');
		</script>';
	}
}else{
	$oid_gpon_status_onu = array(
		'id'=>$getONT['olt'],'do'=>'oid',
		'oid'=> vsprintf('1.3.6.1.4.1.3902.1012.3.28.2.1.4.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
	);
	$oid_gpon_model_onu = array(
		'id'=>$getONT['olt'],'do'=>'oid',
		'oid'=> vsprintf('1.3.6.1.4.1.3902.1012.3.50.11.2.1.17.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
	);	
	$oid_gpon_vlan_onu = array(
		'id'=>$getONT['olt'],'do'=>'oid',
		'oid'=> vsprintf('1.3.6.1.4.1.3902.1012.3.50.15.100.1.1.4.%s.%s.1.1',[$getONT['zte_idport'],$getONT['keyonu']])
	);
	$oid_gpon_eth_onu = array(
		'id'=>$getONT['olt'],'do'=>'oid',
		'oid'=> vsprintf('1.3.6.1.4.1.3902.1012.3.50.14.1.1.6.%s.%s.1',[$getONT['zte_idport'],$getONT['keyonu']])
	);
	$oid_gpon_eth_onu_type = array(
		'id'=>$getONT['olt'],'do'=>'oid',
		'oid'=> vsprintf('1.3.6.1.4.1.3902.1012.3.50.14.1.1.7.%s.%s.1',[$getONT['zte_idport'],$getONT['keyonu']])
	);
	$oid_gpon_get_admin_onu = array(
		'id'=>$getONT['olt'],'do'=>'oid','oid'=> vsprintf('1.3.6.1.4.1.3902.1012.3.28.2.1.1.%s.%s',[$getONT['zte_idport'],$getONT['keyonu']])
	);
	$temp_status_onu = api__($config['monitorapi'],$oid_gpon_status_onu);
	$temp_model_onu = api__($config['monitorapi'],$oid_gpon_model_onu);
	$model_onu = preg_replace('/^.*?(STRING:)|"|N\/A/i','',$temp_model_onu['result']);
	if(isset($temp_status_onu['result'])){
		$status_onu_snmp = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$temp_status_onu['result']);
	}
	$admin_status_onu = api__($config['monitorapi'],$oid_gpon_get_admin_onu);
	$admin_status_onu_snmp = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$admin_status_onu['result']);
	if(isset($admin_status_onu_snmp) && $admin_status_onu_snmp==2){	
		echo'
		<div class="onu_reasons view_onu_none"><b>Admin Status</b>Disable</div>
			<script>        
            document.getElementById(\'zte_onu_enable\').style.display = "inline-block";
			</script>
		';
	}else{
		echo'
			<script>        
            document.getElementById(\'zte_onu_disable\').style.display = "inline-block";
			</script>
		';
	}
	if (isset($model_onu) && in_array(strtoupper($model_onu), array_map('strtoupper', $support_port_onu))) {
		$support_count_port = 4;
	}
	echo'<div class="zte_onu">';
	echo'<div class="zte_eth">';
		if($support_count_port==1){
			$vlan_1_onu = api__($config['monitorapi'],$oid_gpon_vlan_onu);
			$eth_1_vlan = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$vlan_1_onu['result']);
			$eth_1_onu = api__($config['monitorapi'],$oid_gpon_eth_onu);
			$eth_1_status = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$eth_1_onu['result']);
			if($eth_1_status==1){
				$eth_type = api__($config['monitorapi'],$oid_gpon_eth_onu_type);
				$eth_1_type = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$eth_type['result']);
			}else{
				$eth_1_type = $eth_1_status;
			}
			$eth1 = typeOnuztePort($eth_1_type);
			if(isset($eth1)){
				echo'<div class="link link1">
					<div class="linkname">Eth1</div>
					<img src="../style/img/'.$eth1['img'].'">
					<div class="linkstatus'.$eth1['status'].'"></div>
				</div>';	
			}
		}else{
			for ($i_v_port = 1; $i_v_port <= 4; $i_v_port++) {
				$snmp_eth = array(
					'id'=>$getONT['olt'],'do'=>'oid',
					'oid'=>'1.3.6.1.4.1.3902.1012.3.50.14.1.1.6.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.'.$i_v_port);
				$eth_onu = api__($config['monitorapi'],$snmp_eth);
				$eth_status = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$eth_onu['result']);
				if($eth_status==1){
					$oid_gpon_eth_onu_type = array(
						'id'=>$getONT['olt'],'do'=>'oid',
						'oid'=> '1.3.6.1.4.1.3902.1012.3.50.14.1.1.7.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.'.$i_v_port
					);
					$eth_type = api__($config['monitorapi'],$oid_gpon_eth_onu_type);
					$eth_type = preg_replace('/^.*?(INTEGER:)|"|N\/A/i','',$eth_type['result']);
				}else{
					$eth_type = $eth_status;
				}
				$eth = typeOnuztePort($eth_type);
				if(isset($eth)){
					echo'<div class="link link1">
						<div class="linkname">Eth'.$i_v_port.'</div>
						<img src="../style/img/'.$eth['img'].'">
						<div class="linkstatus'.$eth['status'].'"></div>
					</div>';	
				}	
			}	
		}
	echo'</div>';
	echo'<div class="zte_status">
			<div class="zte_getstatus">
				<span>Port type:</span>
				<span class="typortzte">
					<div class="eth_auto"></div>
					<div class="eth_name">Auto</div>
					<div class="eth_10"></div><div class="eth_name">10Mbps</div>
					<div class="eth_100"></div><div class="eth_name">100Mbps</div>
					<div class="eth_1000"></div><div class="eth_name">1G</div>
				</span>
			</div>
			<div class="zte_gettype">
				<span>Port status:</span>
				<span class="typeportstatus">
					<div class="eth_online"></div><div class="eth_name">Online</div>
					<div class="eth_offline"></div><div class="eth_name">Offline</div>
					<div class="eth_disable"></div><div class="eth_name">Disable</div>
				</span>
			</div>
		</div>
	</div>';
	if(isset($status_onu_snmp) && $status_onu_snmp==3){	
	$getOLT = $db->Fast('switch','netip,snmpro,id',['id' => $getONT['olt']]);
	echo'<div class="block_dbm">';	
		$snmp_onu_tariff = array(
			'id'=>$getONT['olt'],'do'=>'oid',
			'oid'=>'1.3.6.1.4.1.3902.1012.3.30.1.1.3.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1'
		);
		$temp_onu_tr = api__($config['monitorapi'],$snmp_onu_tariff);
		if(isset($temp_onu_tr['result']) && !empty($temp_onu_tr['result'])){
			$onu_profile = preg_replace('~^.*?(?=INTEGER:)~i','',$temp_onu_tr['result']);
			$onu_profile = preg_replace ('/INTEGER:/','',$onu_profile);
			if(isset($onu_profile)){
				$snmp_onu_inf_tariff = array(
					'id'=>$getONT['olt'],'do'=>'oid',
					'oid'=>'1.3.6.1.4.1.3902.1012.3.26.1.1.2.'.$onu_profile
				);
				$temp_onu_profile = api__($config['monitorapi'],$snmp_onu_inf_tariff);
				$onu_profile_inf = preg_replace('~^.*?(?=STRING:)~i','',$temp_onu_profile['result']);
				$onu_profile_inf = preg_replace ('/STRING:/','',$onu_profile_inf);
			}
			echo'
				<div class="dbm_block">
					<div class="i"><img src="../style/img/m8.png"></div>
					<div class="text">
						<div class="n">User tariff</div>
						<div class="s">
							<span onclick="showHideBlock(\'blockprofile\',\'knopka\')" class="knopka">'.$onu_profile_inf.'</span>
						</div>
					</div>
				</div>
			';	
		}	
		if(isset($eth_1_vlan)){
			echo'
				<div class="dbm_block">
					<div class="i"><img src="../style/img/manager_vlan.png"></div>
					<div class="text">
						<div class="n">Vlan</div>
						<div class="s"><span onclick="showHideBlock(\'blockvlan\',\'knopka\')" class="knopka"">'.$eth_1_vlan.'</span>
						</div>
					</div>
				</div>';
		}	
		
		$snmp_onu_tx = array(
			'id'=>$getONT['olt'],'do'=>'oid',
			'oid'=>'.1.3.6.1.4.1.3902.1012.3.50.12.1.1.14.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1');
		$temp_onu_tx = api__($config['monitorapi'],$snmp_onu_tx);
		if(isset($temp_onu_tx['result']) && !empty($temp_onu_tx['result'])){
			$onu_tx = preg_replace('~^.*?(?=INTEGER:)~i','',$temp_onu_tx['result']);
			$onutx = preg_replace ('/INTEGER:/','',$onu_tx);
			if ($onutx*1 <30001) {
				$onutx = $onutx*0.002 - 30.0;
			} else {
				if ($onutx*1 < 665535)
					$onutx = ($onutx-65535)*0.002 - 30.0;
				else $onutx = 0;
			}
		echo'
		<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">TX ONU</div>
					<div class="s">
						<span style="color:'.(intval($onutx) >= 2 ? "#36b105":"red").';">'.number_format($onutx,2).'</span>
						<b>dBm</b>
				</div>
			</div>
		</div>';		
		$snmp_onu_rx = array(
			'id'=>$getONT['olt'],'do'=>'oid',
			'oid'=>'.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1');
		$temp_onu_rx = api__($config['monitorapi'],$snmp_onu_rx);
		if(isset($temp_onu_rx['result']) && !empty($temp_onu_rx['result'])){
			$onurx = preg_replace('~^.*?(?=INTEGER:)~i','',$temp_onu_rx['result']);
			$onurx = preg_replace ('/INTEGER:/','',$onurx);
			if ($onurx*1 <30001) {
			  $onurx=$onurx*0.002 - 30.0;
			} else {
				if ($onurx*1 < 665535)
				   $onurx=($onurx-65535)*0.002 - 30.0;
			   else $onurx = 0;
			}
			echo'
			<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX ONU</div>
					<div class="s">
					<span style="color:'.($onurx<-29?"red":"#36b105").';">'.number_format($onurx,2).'</span>
					<b>dBm</b>
				</div>
				</div>
			</div>';
		}
		$snmp_onu_rx_olt = array(
			'id'=>$getONT['olt'],'do'=>'oid',
			'oid'=>'.1.3.6.1.4.1.3902.1015.1010.11.2.1.2.'.$getONT['zte_idport'].'.'.$getONT['keyonu']);
		$temp_onu_rx_olt = api__($config['monitorapi'],$snmp_onu_rx_olt);
		if(isset($temp_onu_rx_olt['result']) && !empty($temp_onu_rx_olt['result'])){
			$onurxolt = preg_replace('~^.*?(?=INTEGER:)~i','',$temp_onu_rx_olt['result']);
			$onurxolt = preg_replace ('/INTEGER:/','',$onurxolt);
			$onurxolt = $onurxolt/1000;
			echo'
			<div class="dbm_block">
				<div class="i"><img src="../style/img/rx.png"></div>
				<div class="text">
					<div class="n">RX OLT</div>
					<div class="s">
						<span style="color:'.($onurxolt <-29?"red":"#36b105").';">'.number_format($onurxolt,2).'</span>
						<b>dBm</b>
					</div>
				</div>
			</div>';
		}
		echo'</div>';
		}		
	}
	echo'<div id="blockvlan" style="display: none;"><div class="blockvlan">';	
	echo'list vlan';
	echo'</div></div>';	
	echo'<div id="blockprofile" style="display: none;"><div class="blockprofile">';	
	echo'list profile';
	echo'</div></div>';
	if(isset($eth_1_vlan) && $eth_1_vlan>0){
		$db->query("UPDATE onus SET wan = '{$eth_1_vlan}' WHERE idonu  = {$getONT['idonu']}");
	}
	if(isset($status_onu_snmp) && $status_onu_snmp==3){	
		sleep(2);
		echo'<script type="text/javascript">
			ajaxfdbonuzte3gpon('.$getONT['idonu'].');
		</script>';
	}
}
}
?>