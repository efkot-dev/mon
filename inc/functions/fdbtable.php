<?php
if (!defined('PONMONITOR') || !defined('FDBTABLE')) {
    die('Hacking attempt!');
}
function TelnetFdbTableCdata16($db, $id_device, $switch, $filefdb) {
	loggerBackground_monitor('show mac address-table interface','/export/cache/');	
	$datafdb = array();
	$telnet = new PMonTelnet($switch);  
	$tmp_result = array();
	$tmp = $telnet->do_comand("enable\r",true);
	$subname = extract_data_between_enable_and_hash($tmp);
	$tmp .= $telnet->do_comand("config\r",true);
	if(isset($tmp)){
		$sqlpon = $db->SimpleWhile("SELECT id, pon FROM switch_pon WHERE oltid = '{$id_device}'");
		foreach ($sqlpon as $pon) {
			$gpon = trim(strtolower(str_replace('', '', $pon['pon'])));
			$command = "show mac-address port {$gpon}";
			$startTime = time();
			$result = null;
			$timeout = 10;
			try {
				$result = get_mac_epon_onu($telnet, $command, $subname);
			} catch (Exception $e) {
				error_log("CDATA16_{$subname}: Error: {$e->getMessage()}\n");
			}
			if ((time() - $startTime) > $timeout) {
				error_log("CDATA16_{$subname}: Timeout exceeded for command: $command\n");
				continue;
			}
			$tmp_result[$pon['id']] = $result;
			usleep(2000000);
		}
		if(isset($tmp_result)){
			foreach ($tmp_result as $resultpon) {
				$datafdb = array_merge($datafdb, array_get_data_fdb($resultpon, $id_device, $switch['oidid']));
			}
		}
	}
	cachefile($datafdb,$filefdb);
}
function TelnetFdbTableCdata12($db, $id_device, $switch, $filefdb) {
	loggerBackground_monitor('show mac address-table interface','/export/cache/');
	$datafdb = array();
	$telnet = new PMonTelnet($switch);  
	$tmp_result = array();
	$tmp = $telnet->do_comand("enable\r",true);
	$subname = extract_data_between_enable_and_hash($tmp);
	if(isset($subname) && $subname!=false){
		$tmp .= $telnet->do_comand("config\r",true);
		if(isset($tmp)){
			$sqlpon = $db->SimpleWhile("SELECT id, pon FROM switch_pon WHERE oltid = '{$id_device}'");
			foreach ($sqlpon as $pon) {
				$epon = trim(strtolower(str_replace('0/', '0/0/', $pon['pon'])));
				$command = "show mac-address port {$epon} with-ont-location";
				$startTime = time();
				$result = null;
				$timeout = 10;
				try {
					$result = get_mac_epon_onu($telnet, $command, $subname);
				} catch (Exception $e) {
					error_log("CDATA12_{$subname}: Error: {$e->getMessage()}\n");
				}
				if ((time() - $startTime) > $timeout) {
					error_log("CDATA12_{$subname}: Timeout exceeded for command: $command\n");
					continue;
				}
				$tmp_result[$pon['id']] = $result;
				usleep(2000000);
			}
		}
		if(isset($tmp_result)){
			foreach ($tmp_result as $resultpon) {
				$datafdb = array_merge($datafdb, array_get_data_fdb($resultpon, $id_device, $switch['oidid']));
			}
		}
	}
	cachefile($datafdb,$filefdb);	
}
function processFdbTable($mysqli, $content, $onu, $olt ) {
	$data = unserialize($content);
	$timer = date('Y-m-d H:i:s');
	$mysqli->query("DELETE FROM fdb_tables WHERE olt = {$olt['id']}");
	if(is_array($data)){
		if($olt['oidid']==1 || $olt['oidid']==12 || $olt['oidid']==15){
			foreach($data as $oltid => $ont){
				if(isset($onu[$ont['port']][$ont['onu']]['idonu'])){
					$idonu = $onu[$ont['port']][$ont['onu']]['idonu'];
					$inface = $onu[$ont['port']][$ont['onu']]['inface'];
					$keyonu = $onu[$ont['port']][$ont['onu']]['keyonu'];
					$mac = $ont['mac'];
					$vlan = $ont['vlan'];
					$query = "INSERT INTO fdb_tables (mac, inface, olt, idonu, vlan, keyonu, added) VALUES 
				('$mac', '$inface', '{$olt['id']}', '$idonu', '$vlan', '$keyonu', '$timer') 
					ON DUPLICATE KEY UPDATE 
					inface=VALUES(inface), olt=VALUES(olt), idonu=VALUES(idonu), vlan=VALUES(vlan), keyonu=VALUES(keyonu), added=VALUES(added)";
					$mysqli->query($query);
				}				
			}
		}			
	}
}
function get_mac_epon_onu($telnet, $command, $subname) {
    $collectedData = '';  // Збирає всі отримані дані
    $lastMatch = '';      // Останній виявлений шаблон
    $repeatCount = 0;     // Лічильник повторюваних шаблонів
    $repeatThreshold = 2; // Поріг повторень для завершення збору даних
    $moreFound = true;    // Прапорець для контролю циклу
    $initialData = $telnet->do_lite("$command\r", true);
    $collectedData .= $initialData;
    while ($moreFound) {
        $responseData = $telnet->do_lite("\r", true);
        $collectedData .= $responseData;
        $foundEnd = stripos($responseData, '#') !== false;
        $foundConfig = stripos($responseData, "{$subname}#") !== false;        
        if ($foundEnd || $foundConfig) {
            $currentMatch = $foundEnd ? '#' : "{$subname}#";
            if ($currentMatch === $lastMatch) {
                $repeatCount++;
            } else {
                $lastMatch = $currentMatch;
                $repeatCount = 1;
				usleep(100000);
            }
            if ($repeatCount >= $repeatThreshold) {
                $moreFound = false;
            }
        } else {
            $repeatCount = 0;
        }
        usleep(100000);
    }
    return $collectedData;
}
function extract_data_between_enable_and_hash($data) {
if (preg_match('/enable(.*?)#/', $data, $matches)) {
return trim($matches[1]); 
}
if (preg_match('/(.*?)#/', $data, $matches)) {
return trim($matches[1]); 
}
return '';
}
function formatTempmac($mac,$format){
$mac = str_replace([' ', '.', '-', ':'], '', $mac);
$mac = strtolower($mac);
return match($format) {
1 => preg_replace('/(.{2})/', '\1:', $mac, 5),
2 => preg_replace('/(.{4})/', '\1.', $mac, 2),
3 => preg_replace('/(.{4})/', '\1-', $mac, 2),
4 => preg_replace('/(.{4})/', '\1:', $mac, 2),
5 => preg_replace('/(.{2})/', '\1.', $mac, 5),
default => $mac,
};
}
function cachefile($datafdb,$filefdb){
	if(is_array($datafdb) && isset($datafdb)){
		@file_put_contents($filefdb, serialize($datafdb));
	}	
}
function array_get_data_fdb($temp_mac,$olt,$oidid){
$temp = '';
$array = [];
$active = false;
$pattern_bdcom = '/\b([0-9]+)\s+([A-F0-9]{4}\.[A-F0-9]{4}\.[A-F0-9]{4})\s+(dynamic|static)\s+epon([0-9]+)\/([0-9]+):([0-9]+)\b/i';
$pattern_cdata12 = '/\b([0-9A-Fa-f:]{17})\s+([0-9]+)\s+pon0\/([0-9]+)\/([0-9]+)\s+([0-9]+)\s+(dynamic|static)\b/i';
$pattern_cdata16 = '/\b([0-9A-Fa-f:]{17})\s+([0-9]+)\s+([0-9]+)\s+pon0\/([0-9]+)\/([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(dynamic|static)\b/i';
if(isset($temp_mac)){			
// BDCOM EPOn
if (preg_match_all($pattern_bdcom, $temp_mac, $bdcom) && $oidid == 1) {
	foreach ($bdcom[0] as $index => $fullMatch) {
		$mac = trim($bdcom[2][$index]);
		$idonu = md5($mac);
		$array[$idonu] = array(
			'olt'=> $olt,'vlan'=> trim($bdcom[1][$index]),'mac'=> formatTempmac($mac,1),'type'=> trim($bdcom[3][$index]),'pon'=> 'epon','port'=> trim($bdcom[5][$index]),'onu'=> trim($bdcom[6][$index])				
		);
	}
}
// CDATA 16
if(preg_match_all($pattern_cdata16, $temp_mac, $cdata16) && $oidid == 12){
	foreach ($cdata16[1] as $index => $fullMatch) {
		$mac = trim($cdata16[1][$index]);
		$idonu = md5($mac);
		$array[$idonu] = array(
			'olt'=> $olt,
			'vlan'=> trim($cdata16[2][$index]),'svlan'=> trim($cdata16[3][$index]),'mac'=> formatTempmac($mac,1),'type'=> trim($cdata16[6][$index]),
			'pon'=> 'gpon','port'=> trim($cdata16[5][$index]),'onu'=> trim($cdata16[6][$index])		
		);
	}
}
// CDATA 12
if(preg_match_all($pattern_cdata12, $temp_mac, $cdata12) && $oidid == 15){
	foreach ($cdata12[1] as $index => $fullMatch) {
		$mac = trim($cdata12[1][$index]);
		$idonu = md5($mac);
		$array[$idonu] = array(
			'olt'=> $olt,'vlan'=> trim($cdata12[2][$index]),'mac'=> formatTempmac($mac,1),'type'=> trim($cdata12[6][$index]),'pon'=> 'epon','port'=> trim($cdata12[4][$index]),'onu'=> trim($cdata12[5][$index])		
		);
	}
}	
}
return $array;
}
?>