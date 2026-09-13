<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$timeupdates = date('Y-m-d H:i:s');
$act = isset($_POST['act']) ? Clean::text($_POST['act']) : null;
$masiv_onu = [];
switch($act){
	case 'pon': 
		$pon = isset($_POST['pon']) ? Clean::int($_POST['pon']) : null;
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;
		if(isset($olt) && isset($pon) && $pon>0 && $olt>0){
			$sql_obl_con = $db->SimpleWhile("SELECT idonu from onus where olt = '{$olt}' and portolt = '{$pon}'");
			if(isset($sql_obl_con) && count($sql_obl_con)>0) {
				foreach($sql_obl_con as $id => $data) {
					$masiv_onu[$data['idonu']]['idonu'] = $data['idonu'];
				}
			}
		}
	break;	
	case 'house': 
		$idhouse = isset($_POST['idhouse']) ? Clean::int($_POST['idhouse']) : null;
		if(isset($_POST['data']) && count($_POST['data'])>0){
			foreach($_POST['data'] as $idonu) {
				$idonu = (int)$idonu;
				$masiv_onu[$idonu]['idonu'] = $idonu;
			}
		}		
	break;
	default:
		if(isset($_POST['data']) && count($_POST['data'])>0){
			foreach($_POST['data'] as $idonu) {
				$idonu = (int)$idonu;
				$masiv_onu[$idonu]['idonu'] = $idonu;
			}
		}
}
$switches = getSwitchMonitor();
if(isset($masiv_onu) && count($masiv_onu)>0){
$inConditions = implode(',', array_map('intval', array_keys($masiv_onu)));
$onu_na_perevirky = $db->SimpleWhile("SELECT idonu, olt, rx, inface, type, keyonu, zte_idport, online, offline, status, mac, sn, portolt, updates FROM onus WHERE idonu IN ($inConditions)");
if ($onu_na_perevirky) {
    $updateIds = [];
    foreach ($onu_na_perevirky as $val) {
        if (isset($switches[$val['olt']]['id']) && $switches[$val['olt']]['id'] == $val['olt']) {
            $polleronu[$val['idonu']] = $val;
            $updateIds[] = $val['idonu'];
        }
    }
    if (!empty($updateIds)) {
       $db->query("UPDATE `onus` SET updates = '".$timeupdates."' WHERE idonu IN (".implode(",", $updateIds).")");
    }
	foreach ($onu_na_perevirky as $temp) {
		$getmonitor = new Monitor($temp['olt'], $switches[$temp['olt']]['class'], $db, $logger, $classOLT, $cacheManager, $php_class_device);
        $supportpoller = $getmonitor->getPollerOnu();
		if ($supportpoller) {
            $getdata = $getmonitor->getDataPoller($temp);	
            if (!empty($getdata['types'])) {
                $getapidataont = api__($config['monitorapi'],$getdata);
                $getdataont = array_merge($temp, $getapidataont,$getdata);
                if (isset($getdataont)) {
                    if (!empty($getdata['types']) && $getdata['pon'] == 'epon') {
                        $getmonitor->tempSaveEpon($getdataont);
                        if (!empty($getdataont['rx'])){
                            $getmonitor->tempSaveSignalEpon($getdataont);
						}
                    }
                    if (!empty($getdata['types']) && $getdata['pon'] == 'gpon') {
                        $getmonitor->tempSaveGpon($getdataont);
                        if (!empty($getdataont['rx'])){
                            $getmonitor->tempSaveSignalGpon($getdataont);
						}
                    }
                }
            }
        }
	}
	echo'ok';
}
}
?>
