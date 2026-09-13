<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$telnet = false;
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(!$id){
	die;
}
$stmt = $pdo->prepare("SELECT * FROM onus WHERE idonu = :id");
$stmt->execute(['id' => $id]);
$getONU = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($getONU['idonu'])) {
$stmt = $pdo->prepare("SELECT * FROM switch WHERE id = :olt");
$stmt->execute(['olt' => $getONU['olt']]);
$getSwitch = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("SELECT * FROM switch_pon WHERE oltid = :olt AND sfpid = :portolt");
$stmt->execute(['olt' => $getONU['olt'], 'portolt' => $getONU['portolt']]);
$getPon = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($getSwitch['username']) && !empty($getSwitch['password'])) {
	$telnet = true;
}
$allowedIds = array(1, 2, 3, 7, 14, 12, 15, 13, 6, 33, 35, 34, 6, 9);
if (in_array($getSwitch['oidid'], $allowedIds)) {
	echo'<div class="command-panel">';	
	if($access->get('edit_profile_speed') && $telnet && $getSwitch['oidid']==1){
		echo'<div class="commands blue" onclick="funcpanel_(\'beps\','.$getONU['idonu'].',\'panel-ont\')"><i class="fi fi-rr-list"></i>'.$lang['speed'].' Onu</div>';	
	}
	if($access->get('fdbmac') && $telnet){
		echo'<div class="commands blue" onclick="funcpanel(\'fdbmac\','.$getONU['idonu'].')"><i class="fi fi-rr-list"></i>'.$lang['allmac'].'</div>';	
	}	
	if($access->get('rebootonu') && isset($getSwitch['snmprw']) && isset($getSwitch['oidid']) && $getSwitch['oidid']==1){
		echo'<div class="commands reboot" onclick="funcpanel(\'bdcomeponrebootonu\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['reboot'].' [S]</div>';
	}	
	if($access->get('rebootonu') && isset($getSwitch['snmprw']) && isset($getSwitch['oidid']) && $getSwitch['oidid']==15){
		echo'<div class="commands reboot" onclick="funcpanel(\'cdata12eponrebootonu\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['reboot'].' [S]</div>';
	}	
	if($access->get('deletonu') && $telnet && $getSwitch['oidid']==9){
		echo'<div class="commands delete" onclick="funcpanel(\'deletonugcomepon\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['delet'].' [T]</div>';
	}		
	if($access->get('rebootonu') && isset($getSwitch['snmprw']) 
		&& isset($getSwitch['oidid']) && $getONU['type']=='gpon' && ($getSwitch['oidid']==7 || $getSwitch['oidid']==34)){
		echo'<div class="commands reboot" onclick="funcpanel(\'zte3gponrebootonu\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['reboot'].' [S]</div>';
	}	
	if($access->get('rebootonu') && isset($getSwitch['snmprw']) && isset($getSwitch['oidid']) && $getSwitch['oidid']==2){
		echo'<div class="commands delet" onclick="funcpanel(\'bdcomgponrebootonu\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['reboot'].' S]</div>';
	}	
	if($access->get('deletonu') && isset($getSwitch['snmprw']) && isset($getSwitch['oidid']) && ($getSwitch['oidid']==14 || $getSwitch['oidid']==33) && $getONU['type']=='gpon'){
		echo'<div class="commands delete" onclick="funcpanel(\'deletonuhuawei\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['delet'].' [T]</div>';
	}	
	if($access->get('deletonu') && $telnet && isset($getSwitch['oidid']) && ($getSwitch['oidid']==14 || $getSwitch['oidid']==33) && $getONU['type']=='gpon'){
		echo'<div class="commands delete" onclick="funcpanel(\'deletonuhuaweigpon\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['delet'].' S]</div>';
	}
	if($access->get('rebootonu') && isset($getSwitch['snmprw']) && isset($getSwitch['oidid']) && ($getSwitch['oidid']==14 || $getSwitch['oidid']==33) && $getONU['type']=='gpon'){
		echo'<div class="commands reboot" onclick="funcpanel(\'huaweigponrebootonus\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['reboot'].' [S]</div>';
	}
	if($access->get('rebootonu') && $telnet){
		echo'<div class="commands reboot" onclick="funcpanel(\'rebootonu\','.$getONU['idonu'].')"><i class="fi fi-rr-rotate-right"></i>'.$lang['reboot'].' [T]</div>';	
	}
	if($access->get('blacklist') && $getSwitch['oidid']==15 && $access->get('addblacklist') && $telnet){
		echo'<div class="commands" onclick="funcpanel(\'blacklist12\','.$getONU['idonu'].')">'.$lang['addblacklist'].'</div>';	
	}	
	if($access->get('blacklist') && $getSwitch['oidid']==13 && $access->get('addblacklist') && $telnet){
		echo'<div class="commands" onclick="funcpaneldel(\'blacklist11\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')">'.$lang['addblacklist'].'</div>';	
	}
	if($access->get('deletonu') && $getSwitch['oidid']==13 && $telnet){
		echo'<div class="commands delete" onclick="funcpaneldel(\'deletonucdata11\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><i class="fi fi-rr-cross"></i>'.$lang['delet'].'</div>';
	}	
	if($access->get('deletonu') && $getSwitch['oidid']==15 && $telnet){
		echo'<div class="commands delete" onclick="funcpaneldel(\'deletonucdata12\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><i class="fi fi-rr-cross"></i>'.$lang['delet'].'</div>';
	}	
	if($access->get('deletonu') && $getSwitch['oidid']==1 && $telnet){
		echo'<div class="commands delete" onclick="funcpaneldel(\'deletonubdcomepon\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><i class="fi fi-rr-cross"></i>'.$lang['delet'].'</div>';
	}	
	if($access->get('deletonu') && $getSwitch['oidid']==1 && isset($getSwitch['snmprw'])){
		echo'<div class="commands delete" onclick="funcpaneldel_ok(\'deletsnmponubdcomepon\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].',\'Are you sure you want to delete?\')"><i class="fi fi-rr-cross"></i>'.$lang['delet'].' [S]</div>';
	}		
	if($access->get('deletonu') && $telnet && ($getSwitch['oidid']==7 || $getSwitch['oidid']==34)){
		echo'<div class="commands delete" onclick="funcpaneldel(\'deletonuzte3\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><i class="fi fi-rr-cross"></i>'.$lang['delet'].'</div>';		
		echo'<div class="commands" onclick="funcpaneldel(\'resetonuzte3\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')">Reset</div>';
		echo'<div class="commands" onclick="funcpaneldel(\'configonuzte3\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><img src="../style/img/config.svg" alt="" class="default-icon">Config ONU</div>';
		echo'<div class="commands" onclick="funcpaneldel(\'detailinfoonuzte3\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><img src="../style/img/config.svg" alt="" class="default-icon">Detail info ONU</div>';
	}	
	if($access->get('deletonu') && $getSwitch['oidid']==2 && $telnet){
		echo'<div class="commands delete" onclick="funcpaneldel(\'deletonubdcomgpon\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><i class="fi fi-rr-cross"></i>'.$lang['delet'].'</div>';
	}	
	if($access->get('deletonu') && $getSwitch['oidid']==6 && $telnet){
		echo'<div class="commands delete" onclick="funcpaneldel(\'deletonuzte6\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><i class="fi fi-rr-cross"></i>'.$lang['delet'].'</div>';
	}		
	if($access->get('rebootonu') && $getSwitch['oidid']==6 && isset($getSwitch['snmprw']) ){
		echo'<div class="commands reboot" onclick="funcpaneldel(\'zte6rebootonusnmp\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><i class="fi fi-rr-cross"></i>'.$lang['reboot'].' [S]</div>';
	}	
	if($access->get('deletonu') && $getSwitch['oidid']==1 && $telnet){
		echo'<div class="commands" onclick="funcpaneldel(\'configonu\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')"><img src="../style/img/config.svg" alt="" class="default-icon">Config ONU</div>';
	}	
	if($access->get('deletonu') && $getSwitch['oidid']==7 && $telnet){
		echo'<div id="zte_onu_disable" class="commands delete hides" onclick="funcpaneldel(\'disableonuzte3\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')">
		<i class="fi fi-rr-ban"></i>Disabled ONU</div>';		
		echo'<div id="zte_onu_enable"  class="commands reboot hides" onclick="funcpaneldel(\'enableonuzte3\','.$getONU['idonu'].','.$getONU['olt'].','.$getPon['id'].')">
		<i class="fi fi-rr-play"></i>Enable ONU</div>';
	}
	echo'</div>';
}
}
?>


