<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
$starttime = microtime(true);
if (isset($confPMon['ONU_RX_OLT_SIGNAL']) && !empty($confPMon['ONU_RX_OLT_SIGNAL']) && $confPMon['ONU_RX_OLT_SIGNAL'] == 1){
	$pauseInterval = 30;
	$counter = 0;
	$count_onu = 0;
	if(is_valid_id($olt)){
		$sql_switch = ['sql' => "SELECT * FROM switch WHERE id = '{$olt}' LIMIT 1",'key' => "device_{$olt}",'time' => 60];
		$switches = cache_simple_sql($sql_switch);
		if(!empty($switches['id'])){
			taskers_device_pmon($db, 31, $switches['id']);
			$getmonitor = new Monitor($switches['id'], $switches['class'], $db, $logger, $classOLT, $cacheManager, $php_class_device);
			$supportpoller = $getmonitor->getPollerRxOlt();
			if($supportpoller) {
				$polleronu = $db->SimpleWhile("SELECT idonu, olt, rx, inface, type, keyonu, zte_idport, online, offline, status, mac, sn, portolt, updates FROM onus WHERE status = 1 AND olt = '{$switches['id']}'");
				if(isset($polleronu) && count($polleronu)>0){
					foreach ($polleronu as $temp) {  
						$getdata = $getmonitor->getDataRxPoller($temp);
						if (!empty($getdata['types'])) {
							$getapidataont = api__($config['monitorapi'],$getdata);
							if(is_array($getapidataont)){
								$getdataont = array_merge($temp, $getapidataont,$getdata);
								if (isset($getdataont)) {
									if (!empty($getdata['types']) && $getdata['pon'] == 'epon') {
										$getmonitor->RxOltSaveSignalEpon($getdataont);
									}elseif(!empty($getdata['types']) && $getdata['pon'] == 'gpon'){
										$getmonitor->RxOltSaveSignalGpon($getdataont);
									}			 
								}
							}
						}
						$counter++;
						$count_onu++;
						if ($counter % $pauseInterval === 0) {
							sleep(max(1, min(6, 3)));
						}
					}
					if(isset($get_result) && $get_result>0){
						echo 'ok';
					}
				}
				$time_check = intval((int)microtime(true) - $starttime);
				$log_monitor = [
					'log'=>'device','type'=>'monitor_rx_olt','descr'=>vsprintf($lang['monitor_rx_olt_onu'],[$count_onu,$time_check]),'deviceid'=>$olt,'who'=>'cron'
				];
				$logger->init($log_monitor);
			}
		}
	}
}
?>
