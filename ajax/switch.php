<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '900');
ini_set('output_buffering', 'Off');	
require_once ENGINE_DIR.'ajax.php';
$timeout = 1000000;
$retries = 5;
if($_POST['id']){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if($id){
	$switch = $db->Fast('switch','*',['id'=>$id]);
	if(!empty($switch['id'])){
		if($switch['oidid']==24){
		$list = @snmp2_real_walk($switch['netip'],$switch['snmpro'],'1.3.6.1.4.1.259.10.1.45.1.2.11.1.5');
		if($list){
			echo'<div class="block_sfp">';
			foreach($list as $keyid => $value){
				$llid = str_replace('.1.3.6.1.4.1.259.10.1.45.1.2.11.1.5.','',$keyid);
				$tx_power = @snmp2_get($switch['netip'],$switch['snmpro'],'1.3.6.1.4.1.259.10.1.45.1.2.11.1.6.'.$llid);
				$values = strtolower(str_replace('STRING:','',str_replace('normal','',str_replace('"','',trim($value)))));	
				$tx_power = strtolower(str_replace('STRING:','',str_replace('normal','',str_replace('"','',trim($tx_power)))));	
				echo'<div class="sfp_">';					
				echo'<div class="sfp_block"><img src="../style/img/code.png"><h1>SFP '.$llid.'</h1></div>';					
				echo'<div class="sfp_block"><div class="sfp_name">RxPower</div><div class="sfp_signal">'.$values.'</div></div>';					
				echo'<div class="sfp_block"><div class="sfp_name">TxPower</div><div class="sfp_signal">'.$tx_power.'</div></div>';					
				echo'</div>';					
			}
			echo'</div>';
		}
	}
}
}
}
?>

