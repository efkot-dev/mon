<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if(isset($_POST['idonu'])){
	$result = array();
	$resulttype  = array();
	$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']): null;
	$getONT = $db->Fast('onus','*',['idonu' => $idonu]);
	if(!empty($getONT['idonu'])){
		$getOLT = $db->Fast('switch','*',['id' => $getONT['olt']]);
		$timeout = 100000;
		$retries = 5;
		
		if(!empty($getOLT['netip']) && !empty($getOLT['class']) && $getONT['type']==='gpon'){
			$res_type = @snmp2_real_walk($getOLT['netip'],$getOLT['snmpro'],'1.3.6.1.4.1.2011.6.128.1.1.2.101.1.8.'.$getONT['zte_idport'].'.'.$getONT['keyonu']);
			if(isset($res_type)){
				$is = 1;
				foreach($res_type as $keys => $values){
					$resulttype[$is]['type'] = HuaweiReasonGpon(str_replace('INTEGER:','',str_replace('Z','',str_replace('"','',str_replace(' ','',$values)))));
					$is++;
				}
			}
			$res_time = @snmp2_real_walk($getOLT['netip'],$getOLT['snmpro'],'1.3.6.1.4.1.2011.6.128.1.1.2.101.1.7.'.$getONT['zte_idport'].'.'.$getONT['keyonu']);
			if(isset($res_time)){
				$i = 1;
				foreach($res_time as $key => $value){
					preg_match('/101.1.7.([\d]+).([\d]+).([\d]+)$/',$key,$arr);
					$result[$i]['time'] = str_replace('STRING:','',str_replace('Z','',str_replace('"','',$value)));
					$i++;
				}
			}
			if(isset($getONT['zte_idport']) && isset($getONT['keyonu'])){
				if(isset($result) && is_array($resulttype)){
					foreach($result as $key => $time_curent){
						echo'<div class="list_time">';
						echo'<div class="down_time">'.date( "Y-m-d H:i:s", (strtotime($time_curent['time']." -2 hour"))).'</div>';
						echo'<div class="down_type">'.$lang[$resulttype[$key]['type']].'</div>';
						echo'</div>';
					}
				}
			}
		}
	}
}
?>