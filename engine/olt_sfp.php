<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
$foldercache = ROOT_DIR . '/export/cache/';
require ROOT_DIR . '/inc/init.monitor.php';
require ROOT_DIR . '/inc/init.silent.php';
$starttime = microtime(true);
if(isset($jobid) && $jobid>0){
	if(isset($olt) && $olt>0){
		$id_device = intval($olt);
	}	
	if(isset($id_device) && $id_device>0) {
		$ws = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");	
		if(!empty($ws['id'])){
			taskers_device_pmon($db, 15, $ws['id']);
			$oid_sfp = get_oid_sfp($ws);
			if ($oid_sfp != false) {
				$data_to_save = [];
				$common_params = ['ip' => $ws['netip'],'community' => $ws['snmpro']];
				switch ($ws['oidid']) {
					case 14:
						$epon_params = array_merge($common_params, ['oid' => $oid_sfp['epon'], 'type' => 'real']);
						$gpon_params = array_merge($common_params, ['oid' => $oid_sfp['gpon'], 'type' => 'real']);
						$epon_data = pmon_walk_m($epon_params);
						$gpon_data = pmon_walk_m($gpon_params);
						$data_to_save[] = [
							'file' => $ws['id'] . '_epon_signal','data' => serialize($epon_data)
						];
						$data_to_save[] = [
							'file' => $ws['id'] . '_gpon_signal','data' => serialize($gpon_data)
						];
						break;
					case 15:
						$exec_params = array_merge($common_params, [
							'oid' => $oid_sfp,'type' => 'exec','deloid' => true
						]);
						$temp_list_ = pmon_walk_m($exec_params);
						$processed_data = [];
						foreach ($temp_list_ as $temp) {
							if (preg_match('/^(.*?)\s*=\s*INTEGER:\s*(\d+)/', $temp['result'], $matches)) {
								$processed_data[$matches[1]]['result'] = $matches[2];
							}
						}
						$data_to_save[] = [
							'file' => $ws['id'] . '_pon_signal','data' => serialize($processed_data)
						];
						break;
					default:
						$default_params = array_merge($common_params, ['oid' => $oid_sfp, 'type' => 'class']);
						$default_data = pmon_walk_m($default_params);
						$data_to_save[] = [
							'file' => $ws['id'] . '_pon_signal','data' => serialize($default_data)
						];
						break;
				}
				foreach ($data_to_save as $entry) {
					if (!empty($entry['data'])) {
						$escaped_data = escape_sql($entry['data']);
						$sql = "INSERT INTO tempdate (file, data) VALUES ('{$entry['file']}', '{$escaped_data}') ON DUPLICATE KEY UPDATE data = '{$escaped_data}', last_processed = '{$timer}', updated_at = '{$timer}';";
						$db->query($sql);
					}
				}
				$time_check = number_format(microtime(true) - $starttime, 2);
				$log_monitor = [
					'log'=>'device',
					'type'=>'monitor',
					'descr'=>vsprintf($lang['monitor_tx_sfp_onu'],[$time_check]),
					'deviceid'=>$ws['id'],
					'who'=>'cron'
				];
				$logger->init($log_monitor);
			}

		}
	}
	if (isset($id_device) && $id_device>0) {
		echo'ok';
	}
}
?>
