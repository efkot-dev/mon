<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
$oltid = isset($_POST['oltid']) ? Clean::int($_POST['oltid']) : null;
$portid = isset($_POST['portid']) ? Clean::int($_POST['portid']) : null;
if ($oltid && $portid) {
	$getswitch = $db->Fast('switch', '*', ['id' => $oltid]);
	if(!empty($getswitch['device']) && !empty($getswitch['netip']) && !empty($getswitch['snmpro'])) {
		if($getswitch['device']=='olt'){
			$speed = get_traffic_data($getswitch['netip'],$getswitch['snmpro'],$portid);		
		}elseif($getswitch['device']=='switch'){
			$speed = get_traffic_data_switch($getswitch['netip'],$getswitch['snmpro'],$portid);
		}else{
			$speed = get_traffic_data($getswitch['netip'],$getswitch['snmpro'],$portid);	
		}
		if(is_array($speed)){
			echo'<div class="snmptraffic"><div class="block_in"><div class="tr_name">'.$lang['traffic_in'].':</div><div class="tr_traff">'.$speed['in'].'</div></div><div class="block_out"><div class="tr_name">'.$lang['traffic_out'].':</div><div class="tr_traff">'.$speed['out'].'</div></div></div>';
		}
	}
}
?>