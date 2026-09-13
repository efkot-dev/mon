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
		if($getOLT['oidid']==36){
			$ip = $getOLT['netip'];
			$snmp = $getOLT['snmpro'];
			$oid_status = '1.3.6.1.4.1.13464.1.14.2.4.1.1.1.6.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
			$oid_rx_onu = '1.3.6.1.4.1.13464.1.14.2.4.1.4.1.5.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
			$oid_tx_onu = '1.3.6.1.4.1.13464.1.14.2.4.1.4.1.6.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
			$oid_rx_onu_olt = '1.3.6.1.4.1.13464.1.14.2.4.1.4.1.11.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'];
			$oid_eth_onu = '1.3.6.1.4.1.13464.1.14.2.4.2.1.1.6.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1';
			$oid_speed_eth_onu = '1.3.6.1.4.1.13464.1.14.2.4.2.1.1.7.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1';
			$eth_speed_snmp = @snmp2_get($ip,$snmp,$oid_speed_eth_onu, 100000, 5);
			$eth_speed_snmp = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $eth_speed_snmp);
			$eth_speed_snmp = str_replace(['"', 'N/A', '65535'], ['', '0', '0'], $eth_speed_snmp);
			$eth_onu_speed = trim($eth_speed_snmp);	
			$eth_snmp = @snmp2_get($ip,$snmp,$oid_eth_onu, 100000, 5);
			$eth_snmp = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $eth_snmp);
			$eth_snmp = str_replace(['"', 'N/A', '65535'], ['', '0', '0'], $eth_snmp);
			$eth_onu = trim($eth_snmp);
			if($eth_onu_speed==3){
				$img_speed = 6;
			}elseif($eth_onu_speed==2){
				$img_speed = 5;
			}elseif($eth_onu_speed==1){				
				$img_speed = 0;
			}else{
				$img_speed = 1;
			}			
			$eth_port = '
				<div class="zte_eth">
					<div class="link link4">
						<div class="linkname">Eth1</div><img src="../style/img/zte'.$img_speed.'.png">
						<div class="linkstatus'.(($eth_onu == 1) ? 'up' : 'down').'"></div>
					</div>
				</div>';
		echo'<div class="zte_onu">
				'.$eth_port.'
				<div class="zte_status">
					<div class="zte_getstatus">
						<span>Port type:</span>
						<span class="typortzte">
							<div class="eth_auto"></div>
							<div class="eth_name">Auto</div>
							<div class="eth_10"></div>
							<div class="eth_name">10Mbps</div>
							<div class="eth_100"></div>
							<div class="eth_name">100Mbps</div>
							<div class="eth_1000"></div>
							<div class="eth_name">1G</div>
						</span>
					</div>
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
			$snmp_status = @snmp2_get($ip,$snmp,$oid_status, 100000, 5);
			$onu_status = @getsnmp_integer($snmp_status);
			if(isset($onu_status) && $onu_status == 1){
				echo'<div class="block_dbm">';
				$tx_snmp = @snmp2_get($ip,$snmp,$oid_tx_onu, 100000, 5);
				$tx_snmp = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $tx_snmp);
				$tx_snmp = str_replace(['"', 'N/A', '65535'], ['', '0', '0'], $tx_snmp);
				$tx_onu = trim($tx_snmp);
				if($tx_onu!=false){
					$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:2);
					$tx_color = $tx_onu > $minbad ? "red" : "#36b105";
					echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">TX ONU</div>';
					echo'<div class="s"><span style="color:' . $tx_color . ';">' . $tx_onu . '</span><b>dBm</b></div></div></div>';					
				}
				$rx_snmp = @snmp2_get($ip,$snmp,$oid_rx_onu, 100000, 5);
				$rx_snmp = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $rx_snmp);
				$rx_snmp = str_replace(['"', 'N/A', '65535'], ['', '0', '0'], $rx_snmp);
				$rx_onu = trim($rx_snmp);
				if($rx_onu!=false){
					$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
					$rx_color = $rx_onu < -$minbad ? "red" : "#36b105";
					echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">RX ONU</div>';
					echo'<div class="s"><span style="color:' . $rx_color . ';">' . $rx_onu . '</span><b>dBm</b></div></div></div>';					
				}				
				$rx_olt_snmp = @snmp2_get($ip,$snmp,$oid_rx_onu_olt, 100000, 5);
				$rx_olt_snmp = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $rx_olt_snmp);
				$rx_olt_snmp = str_replace(['"', 'N/A', '65535'], ['', '0', '0'], $rx_olt_snmp);
				$rx_olt_onu = trim($rx_olt_snmp);
				if($rx_olt_onu!=false){
					$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
					$rx_color = $rx_olt_onu < -$minbad ? "red" : "#36b105";
					echo'<div class="dbm_block"><div class="i"><img src="../style/img/rx.png"></div><div class="text"><div class="n">RX OLT</div>';
					echo'<div class="s"><span style="color:' . $rx_color . ';">' . $rx_olt_onu . '</span><b>dBm</b></div></div></div>';					
				}
				echo'</div>';
			}else{
				
			}
		}
	}
}
?>