<?php
/*
=====================================================
 Copyright (c) 2024 PMon
=====================================================
 1. notifications about new ones
 2. notifications about the number of disconnected ONTs
 3. SQL update about new ones Switch
 3. notifications about Pon LOSS ONTs 
=====================================================
*/
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
$currentTime = strtotime($timer);
// ONU_NEW
if (isset($confPMon['ONU_NEW']) && !empty($confPMon['ONU_NEW']) && $confPMon['ONU_NEW']==1) {
$tempNewOnu = $db->Simple("SELECT * FROM tempdate WHERE file = 'new_onu'");
$lastProcessed = $tempNewOnu ? strtotime($tempNewOnu['last_processed']) : strtotime('-10 minutes');
$lastProcessedDate = date('Y-m-d H:i:s', $lastProcessed);
$sql = "SELECT onus.type, onus.inface, onus.olt, onus.added as add_time, onus.dist, onus.rx, onus.status, onus.mac, onus.sn, onus.rx, switch.place 
    FROM onus 
    LEFT JOIN switch ON onus.olt = switch.id WHERE onus.added > '{$lastProcessedDate}'";
$sqlNewOnu = $db->SimpleWhile($sql);
if (!empty($sqlNewOnu)) {
    foreach ($sqlNewOnu as $ont) {
        $dist = isset($ont['dist']) && !empty($ont['dist']) && $ont['dist'] > 0 ? ', ' . sprintf('%.2f', (intval($ont['dist']) / 1000)) . " km" : '';
        $rx = isset($ont['rx']) && !empty($ont['rx']) ? ', ' . $ont['rx'] . ' dBm' : '';
        $onukey = $ont['mac'] ?? $ont['sn'];
        $status = (isset($ont['status']) && $ont['status'] == 1) ? '[icon-online-min]' : '[icon-offline-min]';
        $messageNewOnu = "[icon-fun] [b]ONUREG[/b]: {$onukey}, {$ont['place']}, {$status} {$ont['type']} {$ont['inface']}{$rx}{$dist}" . (isset($ont['rx']) && $ont['rx'] > 0 ? "{$ont['rx']} dBm" : '');
        $sql = "INSERT INTO notification (status, type, system, message, added) VALUES ('1', '32', 'monitor', '{$messageNewOnu}', '{$timer}')";
       $db->query($sql);
        }
}
$db->query("INSERT INTO tempdate (file, last_processed) VALUES ('new_onu', '".date('Y-m-d H:i:s', $currentTime)."')  ON DUPLICATE KEY UPDATE last_processed = '".date('Y-m-d H:i:s', $currentTime)."'");
}
/// BADSIGNAL
$tempBadOnu = $db->Simple("SELECT * FROM tempdate WHERE file = 'badsignalonu' LIMIT 1");
$currentBadSignalData = $tempBadOnu ? (int)$tempBadOnu['data'] : 0;
$badsignalstart = '-' . $config['badsignalstart'];
$badsignalend = '-' . $config['badsignalend'];
$where_onus = "WHERE status = '1' AND rx IS NOT NULL AND rx != '' AND rx != '0' AND rx BETWEEN " . (int)$badsignalend . ".99 AND " . (int)$badsignalstart . ".00 ORDER BY CAST(rx AS DECIMAL(10, 2)) ASC";
$count_online = $db->Simple("SELECT count(idonu) as online FROM onus WHERE status = '1'");
$count_all = $db->Simple("SELECT count(idonu) as onu_all FROM onus WHERE olt != '0'");
$count_offline = $count_all['onu_all'] - $count_online['online'];
$count_bad_rx = $db->Simple("SELECT count(idonu) as bad_rx FROM onus $where_onus");
$db->SQLinsert(
        'pmonstats',
        [
            'datetime' => date('Y-m-d H:i:s'),
            'countonu' => $count_all['onu_all'],
            'badsignal' => $count_bad_rx['bad_rx'],
            'online' => $count_online['online'],
            'offline' => $count_offline
        ]
    );
$critical = isset($confPMon['CRITICAL_CHANGE_RX_ALL']) && !empty($confPMon['CRITICAL_CHANGE_RX_ALL']) ? $confPMon['CRITICAL_CHANGE_RX_ALL'] : 5;
$sum = $currentBadSignalData - (int)$count_bad_rx['bad_rx'];
if ($currentBadSignalData != (int)$count_bad_rx['bad_rx'] && abs($sum) > $critical) {
    $sender = "[icon-myxa][b]All bad signals[/b] ".$currentBadSignalData." => ".$count_bad_rx['bad_rx']."";
    $sql = "INSERT INTO notification (status, type, system, message, added) 
            VALUES ('1', '37', 'monitor', '{$sender}', '{$timer}')";
    $db->query($sql);
}
$db->query("INSERT INTO tempdate (file, data) VALUES ('badsignalonu', '{$count_bad_rx['bad_rx']}') ON DUPLICATE KEY UPDATE data = '{$count_bad_rx['bad_rx']}', updated_at =  '{$timer}', last_processed =  '{$timer}'");
$sql_olt = getSwitchAll();
if (isset($sql_olt) && count($sql_olt) > 0) {
	foreach ($sql_olt as $olt) {
		$cont_new_onu = $db->Simple("SELECT count(idonu) as cont_new_onu FROM onus WHERE olt = '{$olt['id']}' AND added  >= curdate()");
		if (isset($cont_new_onu['cont_new_onu']) && $cont_new_onu['cont_new_onu']>0) {
			$count_new_onu = $cont_new_onu['cont_new_onu'];
		}else{
			$count_new_onu = 0;
		}	
		$db->query("UPDATE switch SET todayonu = '{$count_new_onu}' WHERE id = '{$olt['id']}'");
	}
}
if (isset($sql_olt) && count($sql_olt) > 0) {
	$tempPonLosOnu = $db->Simple("SELECT * FROM tempdate WHERE file = 'ponlosonu' LIMIT 1");
    $previousData = isset($tempPonLosOnu['data']) ? json_decode($tempPonLosOnu['data'], true) : [];
    $oltIds = array_column($sql_olt, 'id');
    $oltPlaceMap = array_column($sql_olt, 'place', 'id');
    $ponData = $db->SimpleWhile("SELECT * FROM switch_pon WHERE oltid IN ('" . implode("','", $oltIds) . "')");
    $ponMap = [];
    foreach ($ponData as $pon) {
        $ponMap[$pon['oltid']][$pon['sfpid']] = $pon;
    }
    $onuData = $db->SimpleWhile("SELECT * FROM onus WHERE olt IN ('" . implode("','", $oltIds) . "') AND status = '2' AND offline >= CURDATE() AND (reason = 'err8' OR reason = 'err6')  ORDER BY offline DESC");
    $currentData = [];
	if (isset($onuData) && count($onuData) > 0) {
		foreach ($onuData as $ont) {
			$switchId = $ont['olt'];
			$portOlt = $ont['portolt'];
			if (isset($ponMap[$switchId][$portOlt])) {
				$pon = $ponMap[$switchId][$portOlt];
				$ponId = $pon['id'];
				if (!isset($currentData[$switchId][$ponId])) {
					$currentData[$switchId][$ponId] = [
						'place' => $oltPlaceMap[$switchId],'pon' => $pon['pon'],'count' => 0
					];
				}
				$currentData[$switchId][$ponId]['count']++;
			}
		}
		$changes = [];
		foreach ($currentData as $switchId => $ponList) {
			foreach ($ponList as $ponId => $ponData) {
				$currentCount = $ponData['count'];
				$previousCount = $previousData[$switchId][$ponId]['count'] ?? 0;
				if ($currentCount !== $previousCount) {
					$difference = $currentCount - $previousCount;
					$changeType = $difference > 0 ? 'increased' : 'decreased';
					if (abs($difference) > 3) {
						$changes[] = [
							'switch_id' => $switchId,'pon_id' => $ponId,'place' => $ponData['place'],'pon' => $ponData['pon'],'previous_count' => $previousCount,'current_count' => $currentCount,'difference' => abs($difference),'change_type' => $changeType
						];
					}
				}
			}
		}
	}
	$db->query("INSERT INTO tempdate (file, data) VALUES ('ponlosonu', '".json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."') ON DUPLICATE KEY UPDATE data = '".json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."', updated_at =  '{$timer}', last_processed =  '{$timer}'");
	if (isset($changes) && count($changes) > 0) {
		foreach ($changes as $changes_olt => $temp_data) {
			if ($temp_data['change_type'] == 'increased') {
				$result_icon = '[icon-up]';
			} else{
				$result_icon = '[icon-down]';
			}
			$sender_los = "[icon-alarm][b]PON LOS[/b] ".$temp_data['place']." [b]".$temp_data['pon']."[/b]: ".$temp_data['previous_count']." {$result_icon} [b]".$temp_data['current_count']."[/b]";
			$sql = "INSERT INTO notification (status, type, system, message, added) VALUES ('1','32','monitor','{$sender_los}','{$timer}')";
			$db->query($sql);
		}
	}
}
?>
