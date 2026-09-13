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
$getOLT = $db->Fast('switch','*',['id' => $getONT['olt']]);
$snmp_vlan = '';
if(!empty($getOLT['netip']) && !empty($getOLT['class']) && $getOLT['oidid']==13){
	$mac_epon = [
		'oid' => '1.3.6.1.4.1.34592.1.3.4.10.2.1.2.1.'.$getONT['zte_idport'].'.'.$getONT['keyonu'].'.1.1',
		'type' => 'exec','deloid' => true,'ip' => $getOLT['netip'],'community'=> $getOLT['snmpro']
	];
	$indexmac = @pmon_walk($mac_epon);
	if(is_array($indexmac)){
		echo'<table class="resp-tab"><thead><tr><th width="15%">vlan</th><th width="25%">mac</th><th></th></tr></thead><tbody>';
			foreach($indexmac as $pi1 => $type) {
				if(!empty($type['result'])){
					if (preg_match("/available/i", $type['result']) || preg_match("/currently/i", $type['result'])){
						echo'<tr><td>--</td><td><b>MAC address missing '.(preg_match("/currently/i", $type['result'])?'ONU offline':'').'</b></td><td class="typesmac"></td></tr>';
					}else{
						preg_match('/^(\d+)\./', $type['result'], $matches);
						$mac = MacHuawei($type['result']);
						LoginDataBase($mac,$getONT);
						echo'<tr><td>'.$matches[1].'</td><td><b>'.$mac.'</b></td><td class="typesmac">'.(strcasecmp($mac, $getONT['mac']) === 0 ?
						'<img src="../style/img/code.png">ONU':'<img onclick="getmacswitch(\''.$mac.'\','.$pi1.')" src="../style/img/online.png"><div style="display:initial;" id="getmac_'.$pi1.'"></div>').'</td></tr>';
					}
				}
			}
		echo'</tbody></table>'; 		
	}
}
}
}
?>