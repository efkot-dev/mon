<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function get_traffic_onu($switch,$onu){
	$timeout = 100000;
	$retries = 5;
	$interface = ''.$onu['zte_idport'].'.'.$onu['keyonu'];
	$ip = $switch['netip'];
	$public = $switch['snmpro'];
	if($onu['type']=='gpon' && $switch['oidid']==14){
		$output_oid = '1.3.6.1.4.1.2011.6.128.1.1.4.23.1.4.' . $interface;
		$input_oid = '1.3.6.1.4.1.2011.6.128.1.1.4.23.1.3.' . $interface;
		$replace = "Counter64: ";
	}elseif($switch['oidid']==33){
		$output_oid = '1.3.6.1.4.1.2011.6.128.1.1.4.23.1.4.' . $interface;
		$input_oid = '1.3.6.1.4.1.2011.6.128.1.1.4.23.1.3.' . $interface;
		$replace = "Counter64: ";		
	}elseif($switch['oidid']==41){
		$output_oid = '1.3.6.1.4.1.34592.1.3.100.12.7.1.5.' . $onu;
		$input_oid = '1.3.6.1.4.1.34592.1.3.100.12.7.1.4.' . $onu;
		$replace = "Counter64: ";		
	}elseif($switch['oidid']==6){
		$output_oid = '1.3.6.1.4.1.3902.1082.500.4.2.2.2.1.46.' . $interface;
		$input_oid = '1.3.6.1.4.1.3902.1082.500.4.2.2.2.1.3.' . $interface;
		$replace = "Gauge32: ";
	}else{
		die('');
	}
	$speed_out = 0;
	$speed_in = 0;
	if($switch['oidid']==33){
		$snmp = new SNMP(SNMP::VERSION_2C, $ip, $public, $timeout, $retries);
		$snmp->valueretrieval = SNMP_VALUE_PLAIN;
		$snmpifInOctets  = $snmp->get($input_oid);
		$snmpifOutOctets = $snmp->get($output_oid);
		$snmp->close();
	}else{
		$snmpifInOctets = @snmpget($ip, $public, $input_oid, $timeout, $retries);
		$snmpifOutOctets = @snmpget($ip, $public, $output_oid, $timeout, $retries);
	}
    $cacheFolder = ROOT_DIR.'/export/snmpcache/';
    $cacheName = $cacheFolder . 'octets_in_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
    $newTime = time();
	if(isset($snmpifOutOctets) && isset($snmpifInOctets)){
		$cmdIn = str_replace($replace, "", $snmpifInOctets);
		$dddIn = intval($cmdIn);
		if (file_exists($cacheName)) {
			$oldTime = filemtime($cacheName);
			$oldOctets = file_get_contents($cacheName);
			$traffDiff = $dddIn - $oldOctets;
			$timeDiff = $newTime - $oldTime;
			if ($timeDiff != 0) {
				$speed_in = ($traffDiff * 8) / $timeDiff;
			} else {
				$speed_in = 'no speed';
			}
			file_put_contents($cacheName, $dddIn);
		} else {
			file_put_contents($cacheName, $dddIn);
			$speed_in = 0;
		}
		$cacheNameOut = $cacheFolder . 'octets_out_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
		$cmdOut = str_replace($replace, "", $snmpifOutOctets);
		$dddOut = intval($cmdOut);
		if (file_exists($cacheNameOut)) {
			$oldTimeOut = filemtime($cacheNameOut);
			$oldOctetsOut = file_get_contents($cacheNameOut);
			$traffDiffOut = $dddOut - $oldOctetsOut;
			$timeDiffOut = $newTime - $oldTimeOut;
			if ($timeDiffOut != 0) {
				$speed_out = ($traffDiffOut * 8) / $timeDiffOut;
			} else {
				$speed_out = 'no speed';
			}
			file_put_contents($cacheNameOut, $dddOut);
		} else {
			file_put_contents($cacheNameOut, $dddOut);
			$speed_out = 0;
		}
	}
    return [
        'in' => $speed_out,
        'out' => $speed_in,
    ];
}
function get_traffic_onu_zte3_gpon($switch,$onu,$port){
	$timeout = 100000;
	$retries = 5;
	$interface = ''.$port.'.'.$onu['keyonu'];
	$ip = $switch['netip'];
	$public = $switch['snmpro'];
	if($switch['oidid']==7){
		$output_oid = '1.3.6.1.4.1.3902.1082.500.4.2.2.2.1.46.' . $interface;
		$input_oid = '1.3.6.1.4.1.3902.1082.500.4.2.2.2.1.3.' . $interface;
		$replace = "Gauge32: ";
	}else{
		die('');
	}
    $snmpifInOctets = @snmpget($ip, $public, $input_oid, $timeout, $retries);
    $snmpifOutOctets = @snmpget($ip, $public, $output_oid, $timeout, $retries);
    $cacheFolder = ROOT_DIR.'/export/snmpcache/';
    $cacheName = $cacheFolder . 'octets_in_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
    $newTime = time();
    $cmdIn = str_replace($replace, "", $snmpifInOctets);
    $dddIn = intval($cmdIn);
    if (file_exists($cacheName)) {
        $oldTime = filemtime($cacheName);
        $oldOctets = file_get_contents($cacheName);
        $traffDiff = $dddIn - $oldOctets;
        $timeDiff = $newTime - $oldTime;
        if ($timeDiff != 0) {
			$speed_in = ($traffDiff * 8) / $timeDiff;
        } else {
            $speed_in = 'no speed';
        }
        file_put_contents($cacheName, $dddIn);
    } else {
        file_put_contents($cacheName, $dddIn);
        $speed_in = 0;
    }
    $cacheNameOut = $cacheFolder . 'octets_out_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
    $cmdOut = str_replace($replace, "", $snmpifOutOctets);
    $dddOut = intval($cmdOut);
    if (file_exists($cacheNameOut)) {
        $oldTimeOut = filemtime($cacheNameOut);
        $oldOctetsOut = file_get_contents($cacheNameOut);
        $traffDiffOut = $dddOut - $oldOctetsOut;
        $timeDiffOut = $newTime - $oldTimeOut;
        if ($timeDiffOut != 0) {
			$speed_out = ($traffDiffOut * 8) / $timeDiffOut;
        } else {
            $speed_out = 'no speed';
        }
        file_put_contents($cacheNameOut, $dddOut);
    } else {
        file_put_contents($cacheNameOut, $dddOut);
        $speed_out = 0;
    }
    return [
        'in' => $speed_out,
        'out' => $speed_in,
    ];
}
function get_traffic_data(string $ip, string $public, int $interface): array {
	$timeout = 100000;
	$retries = 5;
    $input_oid = '1.3.6.1.2.1.2.2.1.10.' . $interface;
    $output_oid = '1.3.6.1.2.1.2.2.1.16.' . $interface;
    $snmpifInOctets = @snmpget($ip, $public, $input_oid, $timeout, $retries);
    $snmpifOutOctets = @snmpget($ip, $public, $output_oid, $timeout, $retries);
    $cacheFolder = ROOT_DIR.'/export/snmpcache/';
    $cacheName = $cacheFolder . 'octets_in_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
    $newTime = time();
    $cmdIn = str_replace("Counter32: ", "", $snmpifInOctets);
    $dddIn = intval($cmdIn);
    if (file_exists($cacheName)) {
        $oldTime = filemtime($cacheName);
        $oldOctets = file_get_contents($cacheName);
        $traffDiff = $dddIn - $oldOctets;
        $timeDiff = $newTime - $oldTime;
        if ($timeDiff != 0) {
			$speed_in = ($traffDiff * 8) / $timeDiff;
        } else {
            $speed_in = 'no speed';
        }
        file_put_contents($cacheName, $dddIn);
    } else {
        file_put_contents($cacheName, $dddIn);
        $speed_in = 0;
    }
    $cacheNameOut = $cacheFolder . 'octets_out_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
    $cmdOut = str_replace("Counter32: ", "", $snmpifOutOctets);
    $dddOut = intval($cmdOut);
    if (file_exists($cacheNameOut)) {
        $oldTimeOut = filemtime($cacheNameOut);
        $oldOctetsOut = file_get_contents($cacheNameOut);
        $traffDiffOut = $dddOut - $oldOctetsOut;
        $timeDiffOut = $newTime - $oldTimeOut;
        if ($timeDiffOut != 0) {
			$speed_out = ($traffDiffOut * 8) / $timeDiffOut;
        } else {
            $speed_out = 'no speed';
        }
        file_put_contents($cacheNameOut, $dddOut);
    } else {
        file_put_contents($cacheNameOut, $dddOut);
        $speed_out = 0;
    }
    return [
        'in' => $speed_out,
        'out' => $speed_in,
    ];
}
function get_traffic_data_switch(string $ip, string $public, int $interface): array {
	$timeout = 100000;
	$retries = 5;
    $input_oid = '1.3.6.1.2.1.2.2.1.10.' . $interface;
    $output_oid = '1.3.6.1.2.1.2.2.1.16.' . $interface;
    $snmpifInOctets = @snmpget($ip, $public, $input_oid, $timeout, $retries);
    $snmpifOutOctets = @snmpget($ip, $public, $output_oid, $timeout, $retries);
    $cacheFolder = ROOT_DIR.'/export/snmpcache/';
    $cacheName = $cacheFolder . 'octets_in_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
    $newTime = time();
    $cmdIn = str_replace("Counter32: ", "", $snmpifInOctets);
	$dddIn = intval($cmdIn);
    if (file_exists($cacheName)) {
        $oldTime = filemtime($cacheName);
        $oldOctets = file_get_contents($cacheName);
        $traffDiff = $dddIn - $oldOctets;
        $timeDiff = $newTime - $oldTime;
        if ($timeDiff != 0) {
			$speed_in = ($traffDiff * 8) / $timeDiff;
        } else {
            $speed_in = 'no speed';
        }
        file_put_contents($cacheName, $dddIn);
    } else {
        file_put_contents($cacheName, $dddIn);
        $speed_in = 0;
    }
    $cacheNameOut = $cacheFolder . 'octets_out_' . str_replace('"', '', strtolower(str_replace(' ', '', str_replace('.', '-', trim($ip))))) . '_' . $interface;
    $cmdOut = str_replace("Counter32: ", "", $snmpifOutOctets);
    $dddOut = intval($cmdOut);
    if (file_exists($cacheNameOut)) {
        $oldTimeOut = filemtime($cacheNameOut);
        $oldOctetsOut = file_get_contents($cacheNameOut);
        $traffDiffOut = $dddOut - $oldOctetsOut;
        $timeDiffOut = $newTime - $oldTimeOut;
        if ($timeDiffOut != 0) {
			$speed_out = ($traffDiffOut * 8) / $timeDiffOut;
        } else {
            $speed_out = 'no speed';
        }
        file_put_contents($cacheNameOut, $dddOut);
    } else {
        file_put_contents($cacheNameOut, $dddOut);
        $speed_out = 0;
    }
    return [
        'in' => $speed_out,
        'out' => $speed_in,
    ];
}
?>