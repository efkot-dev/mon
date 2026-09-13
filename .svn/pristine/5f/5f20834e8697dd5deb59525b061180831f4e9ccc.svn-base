<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('FDBTABLE',true);
$timer = date('Y-m-d H:i:s');
$starttime = microtime(true);
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/classes/telnet.class.php';
require ROOT_DIR.'/inc/classes/fdb.class.php';
$tempfolder = ROOT_DIR.'/export/temp/';
if(isset($olt) && $olt>0){
	$id_device = intval($olt);
}
if(isset($id_device) && $id_device>0 && isset($confPMon['FDB_TABLE']) && !empty($confPMon['FDB_TABLE']) && $confPMon['FDB_TABLE'] == 1){
	$res = $pdo->query("SELECT oidid, id, netip, username, password, place FROM switch WHERE id = '{$id_device}' LIMIT 1");
	$getswitch = $res->fetch(PDO::FETCH_ASSOC);
	if (isset($getswitch['oidid']) && $getswitch['oidid']>0 && isset($getswitch['password']) && isset($getswitch['username']) && isset($getswitch['netip'])) {
		$fdb_table = new PMon_FDB($id_device, $pdo, $getswitch, $confPMon);
		if ($getswitch['oidid'] == 1) {
			$temp_fdb_array = $fdb_table->BDCOM_Epon();
			if(!empty($temp_fdb_array)){
				$fdb_table->BDCOM_Epon_Save($temp_fdb_array);
			}
		}elseif($getswitch['oidid'] == 12){
			$temp_fdb_array = $fdb_table->Cdata16();
			if(!empty($temp_fdb_array)){
				$fdb_table->BDCOM_Epon_Save($temp_fdb_array);
			}
		}elseif($getswitch['oidid'] == 6){	
			die('not_support');
			$temp_fdb_array = $fdb_table->ZTE6();
		}elseif(($getswitch['oidid'] == 7) || ($getswitch['oidid'] == 34)){
			$temp_fdb_array = $fdb_table->ZTE3();
			if(!empty($temp_fdb_array)){
				$fdb_table->ZTE3_Save($temp_fdb_array);
			}
		}elseif(($getswitch['oidid'] == 14) || ($getswitch['oidid'] == 33)){
			// HUAWEI GPON
		}elseif($getswitch['oidid'] == 35){			
			$temp_fdb_array = $fdb_table->Cdata16_v3();
			if(!empty($temp_fdb_array)){
				$fdb_table->BDCOM_Epon_Save($temp_fdb_array);
			}
		}elseif($getswitch['oidid'] == 13){
			$temp_fdb_array = $fdb_table->Cdata11();
			if(!empty($temp_fdb_array)){
				$fdb_table->CData11_Save($temp_fdb_array);
			}			
		}elseif($getswitch['oidid'] == 15){
			$temp_fdb_array = $fdb_table->Cdata12();
			if(!empty($temp_fdb_array)){
				$fdb_table->CData12_Save($temp_fdb_array);
			}
		}
	}
	
}
?>
