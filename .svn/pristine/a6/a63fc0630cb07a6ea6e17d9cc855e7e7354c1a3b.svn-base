<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(!$id){
	die('');	
}
$oids = [];
$getpon = $db->Fast('switch_pon','*',['id'=>$id]);
$getswitch = $db->Fast('switch','*',['id'=>$getpon['oltid']]);
if(!empty($getpon['id']) && !empty($getswitch['id'])){
if ($getswitch['oidid'] == 1) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.3320.101.107.1.3.'.$getpon['sfpid'],
		'temp' => '1.3.6.1.4.1.3320.101.107.1.6.'.$getpon['sfpid'],
		'volt' => '1.3.6.1.4.1.3320.101.107.1.7.'.$getpon['sfpid']
    );
} elseif ($getswitch['oidid'] == 6) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.3902.1082.30.45.2.4.1.2.'.$getpon['sfpid'],'temp' => '1.3.6.1.4.1.3902.1082.30.45.2.4.1.6.'.$getpon['sfpid']
    );
} elseif ($getswitch['oidid'] == 9) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.13464.1.13.2.2.1.6.0.'.$getpon['sort'],'temp' => '1.3.6.1.4.1.13464.1.13.2.2.1.5.0.'.$getpon['sort']
    );
} elseif ($getswitch['oidid'] == 10) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.8888.1.13.2.2.1.6.0.'.$getpon['sort'],'temp' => '1.3.6.1.4.1.8888.1.13.2.2.1.3.0.'.$getpon['sort']
    );
}elseif ($getswitch['oidid'] == 11) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.8888.1.14.2.3.3.1.12.2.'.$getpon['sort'],'temp' => '1.3.6.1.4.1.8888.1.14.2.3.3.1.12.2.'.$getpon['sort']
    );
} elseif ($getswitch['oidid'] == 12) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0.'.$getpon['sfpid']+4
    );		
} elseif ($getswitch['oidid'] == 15) {
    $oids = array(
        'rx' => '1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0.'.$getpon['sfpid']+4
    );		
} elseif ($getswitch['oidid'] == 2) {
    $oids = array(
        'temp' => '1.3.6.1.4.1.3320.10.2.2.1.2.'.$getpon['sfpid'],'volt' => '1.3.6.1.4.1.3320.10.2.2.1.3.'.$getpon['sfpid'],'rx' => '1.3.6.1.4.1.3320.10.2.2.1.5.'.$getpon['sfpid']
    );	
} elseif ($getswitch['oidid'] == 7) {
    $oids = array();
    $oids['rx'] = '1.3.6.1.4.1.3902.1015.3.1.13.1.4.'.$getpon['sfpid'];
    $oids['temp'] = '1.3.6.1.4.1.3902.1015.3.1.13.1.12.'.$getpon['sfpid'];	
} elseif ($getswitch['oidid'] == 14) {
    $oids = array();
    if (stripos($getpon['pon'], 'gpon') !== false) {
        $oids['rx'] = '1.3.6.1.4.1.2011.6.128.1.1.2.23.1.4.'.$getpon['sfpid'];
        $oids['temp'] = '1.3.6.1.4.1.2011.6.128.1.1.2.23.1.1.'.$getpon['sfpid'];
    } elseif (stripos($getpon['pon'], 'epon') !== false) {
        $oids['rx'] = '1.3.6.1.4.1.2011.6.128.1.1.2.33.1.4.'.$getpon['sfpid'];
        $oids['temp'] = '1.3.6.1.4.1.2011.6.128.1.1.2.33.1.1.'.$getpon['sfpid'];
    }
}
}
$add_monitor_temp = '';
if(is_array($oids)){
	foreach ($oids as $name => $oid) {
		if (isset($confPMon['TEMPERATURE_MONITOR']) && !empty($confPMon['TEMPERATURE_MONITOR']) && $confPMon['TEMPERATURE_MONITOR']==1) {
			if($name=='temp'){
				$add_monitor_temp = '<a href="/?do=temp&act=add&d='.$getswitch['id'].'&t=sfp&o='.$oid.'">
					<img src="/style/img/add.png"></a>';
			}
		}
		$value = @snmp2_get($getswitch['netip'],$getswitch['snmpro'],$oid);
		$sfp_value = csfp($value);
		if($sfp_value && $getswitch['oidid']==1){
			if($name=='rx'){
				echo sprintf('<div class="sfp-rx"><img src="../style/img/sfpsignal.png">%0.2f dbm</div>', (float) $sfp_value / 10);
			}elseif($name=='temp'){
				echo sprintf('<div class="sfp-temp"><img src="../style/img/sfptemp.png">%0.2f °C</div>'.$add_monitor_temp,(float) $sfp_value / 256);
			}elseif($name=='volt'){
				echo sprintf('<div class="sfp-volt"><img src="../style/img/sfpvolt.png">%0.2f V</div>',(float) $sfp_value / 10000);
			}
		}elseif($sfp_value && $getswitch['oidid']==9){
			if($name=='rx'){
				echo '<div class="sfp-rx"><img src="../style/img/sfpsignal.png">'.csfp($value).'dbm</div>';
			}elseif($name=='temp'){
				echo '<div class="sfp-temp"><img src="../style/img/sfptemp.png">'.csfp($value).'°C</div>'.$add_monitor_temp;
			}
		}elseif($sfp_value && $getswitch['oidid']==7){
			if($name=='rx'){
				echo '<div class="sfp-rx"><img src="../style/img/sfpsignal.png">'.($sfp_value==2147483647?0:sprintf('%.2f',$sfp_value/1000)).'dbm</div>';
			}elseif($name=='temp'){
				echo '<div class="sfp-temp"><img src="../style/img/sfptemp.png">'.(int)($sfp_value==2147483647?0:$sfp_value/1000).'°C</div>'.$add_monitor_temp;
			}			
		}elseif($sfp_value && $getswitch['oidid']==6){
			if($name=='rx'){
				echo '<div class="sfp-rx"><img src="../style/img/sfpsignal.png">'.sprintf('%.2f',$sfp_value/1000).'</div>';
			}elseif($name=='temp'){
				echo '<div class="sfp-temp"><img src="../style/img/sfptemp.png">'.sprintf('%.2f',($sfp_value/1000)).'°C</div>'.$add_monitor_temp;
			}
		}elseif($sfp_value && $getswitch['oidid']==10){
			if($name=='rx'){
				echo '<div class="sfp-rx"><img src="../style/img/sfpsignal.png">'.csfp($value).'</div>';
			}elseif($name=='temp'){
				echo '<div class="sfp-temp"><img src="../style/img/sfptemp.png">'.csfp($value).'°C</div>'.$add_monitor_temp;
			}
		}elseif($sfp_value && $getswitch['oidid']==15){
			if($name=='rx'){
				echo sprintf('<div class="sfp-rx"><img src="../style/img/sfpsignal.png">%0.2f dbm</div>', (float) $sfp_value / 100);
			}		
		}elseif($sfp_value && $getswitch['oidid']==11){
			if($name=='rx'){
				echo '<div class="sfp-rx"><img src="../style/img/sfpsignal.png">'.csfp($value).'</div>';
			}elseif($name=='temp'){
				echo '<div class="sfp-temp"><img src="../style/img/sfptemp.png">'.csfp($value).'°C</div>'.$add_monitor_temp;
			}
		}elseif($sfp_value && $getswitch['oidid']==2){
			if($name=='temp')
				echo sprintf('<div class="sfp-temp"><img src="../style/img/sfptemp.png">%0.2f °C</div>'.$add_monitor_temp,(float) $sfp_value / 10);			
			if($name=='rx')
				echo sprintf('<div class="sfp-rx"><img src="../style/img/sfpsignal.png">%0.2f dbm</div>',(float) $sfp_value / 10);
			if($name=='volt')
				echo sprintf('<div class="sfp-volt"><img src="../style/img/sfpvolt.png">%0.2f V</div>',(float) $sfp_value / 10);
		}elseif($sfp_value && $getswitch['oidid']==14){
			if($name=='rx'){
				echo sprintf('<div class="sfp-rx"><img src="../style/img/sfpsignal.png">%0.2f dbm</div>', (float) $sfp_value / 100);
			}elseif($name=='temp'){
				echo sprintf('<div class="sfp-temp"><img src="../style/img/sfptemp.png">%s°C</div>'.$add_monitor_temp, $sfp_value);
			}
		}elseif($sfp_value && $getswitch['oidid']==12){
			if($name=='rx'){
				echo sprintf('<div class="sfp-rx"><img src="../style/img/sfpsignal.png">%0.2f dbm</div>', (float) $sfp_value / 100);
			}	
		}
	}
	#print_R($getpon);
	#$dataerror = $db->Simple("SELECT * FROM `switch_port_err` 
	#	WHERE `llid` = '".$vport['llid']."' AND `deviceid` = '".$getswitch['id']."' 
	#		ORDER BY `added` DESC LIMIT 1");
}
?>