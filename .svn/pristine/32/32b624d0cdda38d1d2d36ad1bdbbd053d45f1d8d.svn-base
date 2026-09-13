<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
	function oltgetmac($pi1){
		if($pi1){
			$ps_iface = explode('.', $pi1);
			$ps_temp = sizeof($ps_iface);			   
			$ps_mac  = substr('0'.dechex($ps_iface[($ps_temp - 6)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 5)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 4)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 3)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 2)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 1)]), -2);
			return preg_replace('/(.{2})/','\1:',$ps_mac,5);
		}
	}
$result = array();
$resulttype  = array();
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$get_onu = $db->Fast('onus','*',['idonu' => $id]);
	if(isset($get_onu['idonu']) && !empty($get_onu['idonu'])){
		$get_olt = $db->Fast('switch','class,snmpro,oidid,netip,id',['id' => $get_onu['olt']]);
		if(!empty($get_olt['netip'])){
			preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/',$get_onu['inface'],$ont);
			$index_type = str_pad(decbin(1), 4, '0', STR_PAD_LEFT);
			$rack = str_pad(decbin(1), 4, '0', STR_PAD_LEFT);
			$shelf = str_pad(decbin($ont[1]), 8, '0', STR_PAD_LEFT);
			$slot = str_pad(decbin($ont[2]), 8, '0', STR_PAD_LEFT);
			$olt = str_pad(decbin($ont[3]), 8, '0', STR_PAD_LEFT);
			$port_index = bindec($index_type . $rack . $shelf . $slot  . $olt);
			$ont_type = str_pad(decbin(2), 5, '0', STR_PAD_LEFT);
			$onu_number = str_pad(decbin($ont[4]), 11, '0', STR_PAD_LEFT);
			$bridgeport = str_pad(decbin(1), 8, '0', STR_PAD_LEFT);
			$empty = str_pad(decbin(0), 8, '0', STR_PAD_LEFT);
			$onu_index =  $olt_port_index = bindec($ont_type . $onu_number . $bridgeport . $empty);
			$inface = $port_index.'.'.$onu_index;
			$mac_gpon = [
				'oid' => '1.3.6.1.4.1.3902.1082.40.10.2.1.2.1.50.1.'.$inface,
				'type' => 'class','ip' => $get_olt['netip'],'community'=> $get_olt['snmpro']
			];
			$indexmac = @pmon_walk($mac_gpon);
			if(isset($indexmac) && $indexmac!=false){
				echo'<table class="resp-tab"><thead><tr><th width="15%">vlan</th><th width="25%">mac</th><th>type</th></tr></thead><tbody>';
				$i = 1;
				foreach($indexmac as $temp_mac => $type) {
					$numbers = explode(".", $temp_mac);
					$first_vlan_number = $numbers[0];
					if(isset($first_vlan_number) && $first_vlan_number>0){
						$value_mac = substr($temp_mac, strlen($first_vlan_number) + 1);
						$mac = oltgetmac($value_mac);
						echo '<tr>
							<td>'.$first_vlan_number.'</td>
							<td><b>'.$mac.'</b></td>
							<td class="typesmac"><img onclick="getmacswitch(\''.$mac.'\','.$i.')" src="../style/img/online.png"><div style="display:initial;" id="getmac_'.$i.'"></div></td></tr>';
							$i++;
					}
				}
				echo'</tbody></table>'; 
			}
		}
	}
}
?>