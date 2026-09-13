<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
require_once ENGINE_DIR.'functions/traffic.php';
$traffic_data = [];
$oltid = isset($_POST['oltid']) ? Clean::int($_POST['oltid']) : null;
$portid = isset($_POST['portid']) ? Clean::int($_POST['portid']) : null;
if ($oltid && $portid) {
	$getswitch = $db->Fast('switch', 'netip,snmpro,oidid,device,id', ['id' => $oltid]);
	if(!empty($getswitch['device']) && !empty($getswitch['netip']) && !empty($getswitch['snmpro']) && 
	$getswitch['oidid']!=9	) {
	if ($getswitch['id'] && $portid) {
		if($getswitch['device']=='olt'){
			if($getswitch['oidid']==14 || $getswitch['oidid']==33 || $getswitch['oidid']==6){
				$getonu = $db->Fast('onus', '*', ['idonu' => $portid]);
				$traffic_data = get_traffic_onu($getswitch, $getonu);			
			}elseif($getswitch['oidid']==41){
				$traffic_data = get_traffic_onu($getswitch, $portid);
			}elseif($getswitch['oidid']==7){
				$getonu = $db->Fast('onus', '*', ['idonu' => $portid]);
				if($getonu['type']=='gpon'){
					preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/',$getonu['inface'],$ont);
					$index_type = str_pad(decbin(1), 4, '0', STR_PAD_LEFT);
					$rack = str_pad(decbin(1), 4, '0', STR_PAD_LEFT);
					$shelf = str_pad(decbin($ont[1]), 8, '0', STR_PAD_LEFT);
					$slot = str_pad(decbin($ont[2]), 8, '0', STR_PAD_LEFT);
					$olt = str_pad(decbin($ont[3]), 8, '0', STR_PAD_LEFT);
					$port_index = bindec($index_type . $rack . $shelf . $slot  . $olt);
					$traffic_data = get_traffic_onu_zte3_gpon($getswitch, $getonu, $port_index);
				}					
			}else{
				$traffic_data = get_traffic_data($getswitch['netip'], $getswitch['snmpro'], $portid);
			}
		}else{
			$traffic_data = get_traffic_data_switch($getswitch['netip'], $getswitch['snmpro'], $portid);
		}
		header('Content-Type: application/json');
		echo json_encode($traffic_data);
		exit;
	} else {
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Missing data']);
		exit;
	}	
	}
}
?>