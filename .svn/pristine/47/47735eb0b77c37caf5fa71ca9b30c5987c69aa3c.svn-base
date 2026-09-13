<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']): null;
$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;
$array_switch = [];
$location = $db->SimpleWhile("SELECT * FROM location");
if(is_array($location)){
    foreach($location as $loc){
		$switch = $db->SimpleWhile("SELECT * FROM switch WHERE device ='olt' AND location = ".$loc['id']);
		if(is_array($switch) && count($switch)>0){
			foreach($switch as $sw){
				#$onus = $db->SimpleWhile("SELECT mac, sn, status, reason, rx, offline, rxstatus, changerx FROM onus WHERE olt = ".$sw['id']." AND ((offline >= CURDATE() AND status = '2') OR (status = '1' AND changerx >= CURDATE()))");				
$zapros1 ='mdu';
$zapros2 ='switch';
// (name LIKE '%".$zapros1."%' OR name LIKE '%".$zapros2."%')
$onus = $db->SimpleWhile("SELECT mac, sn, status, reason, rx, offline, rxstatus, name, changerx 
FROM onus WHERE olt = ".$sw['id']."  
	AND (status = '2' OR status = '1')");
				if(is_array($onus) && count($onus)>1){
					foreach($onus as $ont){
						$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
						if($onukey){
							$datatemponu = getFastOnusData($onukey);
						}
						if(!empty($datatemponu['lan'])){
							$view = ($ont['status'] == 1 ? 'vision_1' : 'pulse');
							$geo = $datatemponu['lan'] . ',' . $datatemponu['lon'];
							$icon = getMapOnu(($ont['status'] == 1 ? $ont['rx'] : 0), $view, $ont['status'], $ont['reason']);
							$array_switch[$loc['id']]['onu'][] = [
								'geo' => $geo,
								'icon' => $icon
							];                }
					}
				}
			}
		}
	}
}

header('Content-Type: application/json');
echo json_encode($array_switch);
?>
