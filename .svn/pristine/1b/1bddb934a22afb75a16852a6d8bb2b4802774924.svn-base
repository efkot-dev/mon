<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$result = array();
$resulttype  = array();
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$getONT = $db->Fast('onus','*',['idonu' => $id]);
if(!empty($getONT['idonu'])){
$getOLT = $db->Fast('switch','class,snmpro,oidid,netip,id',['id' => $getONT['olt']]);
$snmp_vlan = '';
if(!empty($getOLT['netip']) && !empty($getOLT['class']) && $getOLT['oidid']==9){
	$mac_epon = [
		'oid' => '1.3.6.1.4.1.13464.1.13.3.16.1.5.0.'.$getONT['zte_idport'].'.'.$getONT['keyonu'],
		'type' => 'real','ip' => $getOLT['netip'],'community'=> $getOLT['snmpro']
	];
	$indexmac = @pmon_walk($mac_epon);
	$found = false; 
	foreach ($indexmac as $pi1 => $check) {
		if (!preg_match("/available/i", $check['result']) && !preg_match("/currently/i", $check['result'])) {
			$found = true; // Позначаємо, що вони знайдено
			break;
		}
	}
	
	if(is_array($indexmac) && $found){
		echo'<table class="resp-tab"><thead><tr><th width="15%">vlan</th><th width="25%">mac</th><th>type</th></tr></thead><tbody>';
			$i = 1;
			foreach($indexmac as $pi1 => $type) {
				if(!empty($type['result'])){
					if (preg_match("/available/i", $type['result']) || preg_match("/currently/i", $type['result'])){
						echo'<tr><td>--</td><td><b>MAC address missing '.(preg_match("/currently/i", $type['result'])?'ONU offline':'').'</b></td><td class="typesmac"></td></tr>';
					}else{
						$mac = MacHuawei($type['result']);
						LoginDataBase($mac,$getONT);
						// '.$pi1.'
						echo'<tr><td></td><td><b>'.$mac.'</b></td><td class="typesmac">'.(strcasecmp($mac, $getONT['mac']) === 0 ?
						'<img src="../style/img/code.png">ONU':'<img onclick="getmacswitch(\''.$mac.'\','.$i.')" src="../style/img/online.png"><div style="display:initial;" id="getmac_'.$i.'"></div>').'</td></tr>';
						$i++;
					}
				}
			}
		echo'</tbody></table>'; 		
	}
}
}
}
?>