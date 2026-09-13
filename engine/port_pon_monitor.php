<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$timer = date('Y-m-d H:i:s');
$array_pon = [];
$foldercache = ROOT_DIR.'/export/cache/';
$sql_list_switch = $db->SimpleWhile("SELECT * FROM switch WHERE monitor = 'yes' AND device = 'olt'");
if (isset($sql_list_switch) && count($sql_list_switch) > 0) {
    if (isset($sql_list_switch) && is_array($sql_list_switch)) {
        foreach ($sql_list_switch as $switch) {
            $switch_array[$switch['id']] = [
                'place' => $switch['place'],'id' => $switch['id'],'model' => $switch['model']
            ];
        }        
    }
    $sqlpon = $db->SimpleWhile("SELECT * FROM switch_pon");
    if (isset($switch_array) && isset($sqlpon) && is_array($sqlpon)) {
        foreach ($sqlpon as $pon) {
			$count = $pon['support'] - (isset($pon['count']) ? $pon['count'] : 0);
			if (isset($confPMon['MONITOR_PON_LOS']) && !empty($confPMon['MONITOR_PON_LOS']) && $confPMon['MONITOR_PON_LOS'] == 1) {		
				if ((($pon['offline'] == $pon['count']) || ($pon['offline'] == ($pon['count'] - 1))) && $pon['count'] > 5) {					
				$sql_los_onu = $db->SimpleWhile("
					SELECT idonu, offline FROM onus  
						WHERE zte_idport = {$pon['sort']} AND olt = {$pon['oltid']} AND status = '2'
							AND (reason = 'err8' OR reason = 'err6') ORDER BY offline DESC");
					if(count($sql_los_onu) > 0.9 * $pon['count']) {
						$array_pon[$pon['oltid']]['pon'][$pon['id']] = array(
							'id' => $pon['id'],'pon' => $pon['pon'],'place' => $switch_array[$pon['oltid']]['place'],'olt' => $pon['oltid'],'count' => $pon['count'],'offline' => $pon['offline'],'online' => $pon['online'],'support' => $pon['support']
						);
					}				
				}
			}
			$filePath = 'pon_' . $pon['id'] . '';
			$currentDataRow = $db->Simple("SELECT * FROM tempdate WHERE file = '{$filePath}' LIMIT 1");
            $key = 'criticonu' . $pon['support'];
			if (isset($config[$key]) && $count <= $config[$key]) {
                $currentData = $currentDataRow ? (int)$currentDataRow['data'] : 0;
                if ($currentData != $count) {
                    $sender = '[icon-warning][b]' . $pon['pon'] . '[/b] - ' . $switch_array[$pon['oltid']]['place'] . '  support: ' . $pon['support'] . ' current: [b]' . $pon['count'] . '[/b] online: ' . $pon['online'] . ' offline: ' . $pon['offline'] . '';
                    $db->SQLinsert('notification', ['status' => 1, 'type' => 105, 'system' => 'monitor', 'message' => $sender, 'added' => $timer]);
                }
				$db->query("INSERT INTO tempdate (file, data) VALUES ('{$filePath}', '".$count."') ON DUPLICATE KEY UPDATE data = '".$count."', updated_at =  '{$timer}', last_processed =  '{$timer}'");
            }
	    }
    }	
    if (isset($confPMon['MONITOR_PON_LOS']) && !empty($confPMon['MONITOR_PON_LOS']) && $confPMon['MONITOR_PON_LOS'] == 1) {
		$currentPonRow = $db->Simple("SELECT * FROM tempdate WHERE file = 'pon_los_onu' LIMIT 1");
        $notificationData = [];
		$previousData = isset($currentPonRow['data']) ? json_decode($currentPonRow['data'], true) : [];
        if (isset($array_pon) && count($array_pon) > 0) {
            foreach ($array_pon as $oltid => $pon_onu) {
                foreach ($pon_onu['pon'] as $pon_id => $pon_data) {
                    $previousPonData = $previousData[$oltid]['pon'][$pon_data['id']] ?? null;
                    if ($previousPonData !== $pon_data) {
                        $notificationData[] = $pon_data;
                    }
                    $previousData[$oltid]['pon'][$pon_id] = $pon_data;
                }
            }
        }
        if(isset($notificationData) && count($notificationData)>0){
			foreach($notificationData as $olt_pon){
				$sender = '[icon-los-pon] [b]Обрив ВОК '.$olt_pon['pon'].'[/b] '.$olt_pon['place'].' : support: ' . $olt_pon['support'] . ' current: [b]' . $olt_pon['count'] . '[/b] online: [b]' . $olt_pon['online'] . '[/b] offline: [b]' . $olt_pon['offline'] . '[/b]';
				$db->SQLinsert('notification',['status'=>1,'type'=>$olt_pon['olt'],'system'=>'pinger','message'=>$sender,'added'=>$timer]);
			}
		}
		$db->query("INSERT INTO tempdate (file, data) VALUES ('pon_los_onu', '".json_encode($previousData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."') ON DUPLICATE KEY UPDATE data = '".json_encode($previousData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."', last_processed =  '{$timer}', updated_at =  '{$timer}'");
    }
}
?>
