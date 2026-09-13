<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$color_signal = '';
$iddevice = isset($_POST['iddevice']) ? Clean::int($_POST['iddevice']): null;
if(!$iddevice){
	die('');	
}
$idport = isset($_POST['idport']) ? Clean::int($_POST['idport']): null;
if(!$idport){
	die('');	
}
function bdcomfsp($clisignal){
	$signal = intval($clisignal);
	if ($signal >= 6) {
		return 'cool_sfp';
	} elseif ($signal >= 4 && $signal < 6) {
		return 'min_sfp';
	} else {
		return 'bad_sfp';
	}
}
if($iddevice && $idport){
$getswitch = $db->Simple("SELECT * FROM switch WHERE id = ".$iddevice." LIMIT 1");
$getpon = $db->Simple("SELECT * FROM switch_pon WHERE id = ".$idport." LIMIT 1");
if ($getswitch['oidid'] == 1) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.3320.101.107.1.3.'.$getpon['sfpid']
    );
} elseif ($getswitch['oidid'] == 9) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.13464.1.13.2.2.1.6.0.'.$getpon['sort']
    );
} elseif ($getswitch['oidid'] == 10) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.8888.1.13.2.2.1.6.0.'.$getpon['sort']
    );
}elseif ($getswitch['oidid'] == 155) { 
	$oids = array(
        'rx' => '1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0.'.$getpon['sfpid']+4
    );	   
}elseif ($getswitch['oidid'] == 12) {    
	$oids = array(
        'rx' => '1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0.'.$getpon['sfpid']+4
    );	
}elseif ($getswitch['oidid'] == 11) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.8888.1.14.2.3.3.1.12.2.'.$getpon['sort']
    );
} elseif ($getswitch['oidid'] == 2) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.3320.10.2.2.1.5.'.$getpon['sfpid']
    );	
} elseif ($getswitch['oidid'] == 14) {
    if (stripos($getpon['pon'], 'gpon') !== false) {
        $oids['rx'] = '1.3.6.1.4.1.2011.6.128.1.1.2.23.1.4.'.$getpon['sfpid'];
    } elseif (stripos($getpon['pon'], 'epon') !== false) {
        $oids['rx'] = '1.3.6.1.4.1.2011.6.128.1.1.2.33.1.4.'.$getpon['sfpid'];
    }
}else{
	
}
if(!empty($oids['rx'])){
$value = api__($config['monitorapi'],['do' => 'oid', 'oid' => $oids['rx'], 'id' => $getswitch['id']]);
if(isset($value['result']) && !empty($value['result'])){
	$sfp_value = csfp($value['result']);
	if($getswitch['oidid']==1){
		$signal = sprintf('%0.2f', (float) $sfp_value / 10);
	}elseif($getswitch['oidid']==9 || $getswitch['oidid']==10 || $getswitch['oidid']==11){
		$signal = $sfp_value;
	}elseif($getswitch['oidid']==2){
		$signal = sprintf('%0.2f',(float) $sfp_value / 10);
	}elseif($getswitch['oidid']==14 || $getswitch['oidid']==15 || $getswitch['oidid']==12){
		$signal = sprintf('%0.2f', (float) $sfp_value / 100);
	}else{
		$signal = '';
	}
	if($signal && isset($signal)){
		if (strpos($signal, '6553') !== false) {
			$signal = 0;
			$color_signal = 4;
		}else{
			$color_signal = bdcomfsp($signal);
		}
	echo'<div class="load-sfp '.$color_signal.'"><div class="name-sfp">'.$getpon['pon'].'</div>
	<div class="stats-sfp">
	<span class="signal-sfp">'.($signal?$signal.' dbm':'N/A').'</span>
	</div>
	</div>';
	}
}
}
}
die;
?>