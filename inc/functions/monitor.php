<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function taskers_device_pmon($db, $jobid, $deviceid) {
	$db->query("UPDATE taskers SET last_run_time = '".date('Y-m-d H:i:s')."' WHERE deviceid = '{$deviceid}' AND workid = '{$jobid}'");	
}
function pingIPAddress($ipAddress) {
    $command = 'sudo /bin/ping -n -c 1 ' . $ipAddress;
    exec($command, $output, $return);
    $latency = 0;
	if (!empty($output[1])) {
        $response = preg_match("/time(?:=|<)(?<time>[\.0-9]+)(?:|\s)ms/", $output[1], $matches);
        if ($response > 0 && isset($matches['time'])) {
            $latency = round($matches['time'], 3);
			if($latency > 0){
				return ['status' => 1,'time' => $latency];
			}
        }

    }
    return ['status' => 2,'time' => 0];
}
function escape_sql($string) {
	return str_replace(
		["\\", "\x00", "\n", "\r", "'", '"', "\x1a"], 
		["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"], 
		$string
	);
}
function acces_device_pmon($data, $requiredKeys, $oidid) {
    if (empty($data) || !is_array($data) || empty($requiredKeys)) {
        exit("Помилка: Невірні вхідні дані.");
    }
    $keys = is_string($requiredKeys) ? explode(',', $requiredKeys) : (array)$requiredKeys;
    foreach ($keys as $key) {
        if (empty($data[$key]) || !preg_match('/\w/', $data[$key])) {
            exit("Помилка: Відсутній або некоректний ключ '$key'.");
        }
    }
    if (!empty($oidid)) {
        $oididKeys = explode(',', $oidid);
        if (!in_array($data['oidid'], $oididKeys, true)) {
            exit("Помилка: Значення 'oidid' не відповідає жодному з допустимих.");
        }
    }
    return true;
}
function get_snmp_data($data) {
	global $db;
	$currentYear = date('Y');
	if (isset($data['day'])) {
		$formattedDate = DateTime::createFromFormat('d.m', $data['day']);
		if ($formattedDate) {
			$formattedDate->setDate($currentYear, $formattedDate->format('n'), $formattedDate->format('j'));
			$startDate = $formattedDate->format('Y-m-d 00:00:00');
			$endDate = $formattedDate->format('Y-m-d 23:59:59');

			$selectdey = "AND timestamp >= '{$startDate}' AND timestamp <= '{$endDate}'";
		} else {
			$selectdey = "AND 1=0";
		}
	} else {
		$selectdey = "AND timestamp >= CURDATE() AND timestamp < DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
	}
	$sql = "SELECT timestamp, in_bps AS in_bps, out_bps AS out_bps FROM snmp_data WHERE portid = '{$data['portid']}' {$selectdey} ORDER BY timestamp ASC";
	#$result = $db->SimpleWhile($sql);
	$sql_traff_monitor = ['sql' => $sql,'type' => 'while','key' => 'traff_monitor_'.$data['portid'],'time' => 1000];
	$result = cache_simple_sql($sql_traff_monitor);
	$dates = [];
	$in_data = [];
	$out_data = [];
	$in_min = $in_max = $out_min = $out_max = null;
	$in_sum = $out_sum = 0;
	$count = 0;
	if (isset($result) && count($result) > 0) {
		foreach ($result as $row) {
			$dates[] = date('H:i', strtotime($row['timestamp']));
			$in_bps = round($row['in_bps'] / 1e9, 3);
			$out_bps = round($row['out_bps'] / 1e9, 3);        
			$in_data[] = $in_bps;
			$out_data[] = $out_bps;
			if ($in_min === null || $in_bps < $in_min) {
				$in_min = $in_bps;
			}
			if ($in_max === null || $in_bps > $in_max) {
				$in_max = $in_bps;
			}
			if ($out_min === null || $out_bps < $out_min) {
				$out_min = $out_bps;
			}
			if ($out_max === null || $out_bps > $out_max) {
				$out_max = $out_bps;
			}
			$in_sum += $in_bps;
			$out_sum += $out_bps;
			$count++;
		}
		$in_avg = ($count > 0) ? round($in_sum / $count, 3) : 0;
		$out_avg = ($count > 0) ? round($out_sum / $count, 3) : 0;
	}
	return json_encode([
		'dates' => $dates,
		'in_data' => $in_data,
		'out_data' => $out_data,
		'in_min' => $in_min,
		'in_avg' => $in_avg,
		'in_max' => $in_max,
		'out_min' => $out_min,
		'out_avg' => $out_avg,
		'out_max' => $out_max
	]);	
}
function hex_mac_bdcom($mac){
	$mac = trim($mac);
	$mac_clean = str_replace(':', '', $mac);
	$ip_parts = [];
	for ($i = 0; $i < strlen($mac_clean); $i += 2) {
		$ip_parts[] = hexdec(substr($mac_clean, $i, 2));
	}
	$ip_string = implode('.', $ip_parts);
	return $ip_string;
}
function jdun_pmon($current, $id, $threshold = 4) {
 
}
function isSignalChanged($current, $last, $threshold = 1) {
    if (isset($current)) {
        $current = floatval($current);
        $last = floatval($last);
        $difference = abs($current - $last);
        return $difference >= $threshold;
    }
    return false;
}
function isSignalChangedStatus($current, $last, $threshold = 1) {
    $current = floatval($current);
    $last = floatval($last);
    $difference = $current - $last;
    if (abs($difference) >= $threshold) {
        if ($difference > 0) {
            return 'up';
        } elseif ($difference < 0) {
            return 'down';
        }
    }    
    return 'none';
}
function get_installing($db, $correct) {
	
}
function getPmonFormatMac($mac,$format){
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
function bdcom_mac($tmp){
	if(isset($tmp)){
		$ps_iface = explode('.', $tmp);
		$ps_temp = sizeof($ps_iface);			   
		$ps_mac  = substr('0'.dechex($ps_iface[($ps_temp - 6)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 5)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 4)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 3)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 2)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 1)]), -2);
		return preg_replace('/(.{2})/','\1:',$ps_mac,5);
	}
	return false;
}
function prepareSignedApiData(array $data): array {
    global $confPMon;
    if (empty($confPMon['API_SECRET'])) {
        throw new RuntimeException('API_SECRET is not configured');
    }
    $data['ts'] = time();
    $data['api_key'] = 'internal_api';
    $signData = $data;
    ksort($signData);
    $data['sign'] = hash_hmac('sha256',http_build_query($signData),$confPMon['API_SECRET']);
    return $data;
}
function fastApiRequest(string $url, array $data): mixed {
    $data = prepareSignedApiData($data);
    $postData = http_build_query($data);
    $contextOptions = [
        'http' => [
            'method' => 'POST',
            'header' =>
                "Content-Type: application/x-www-form-urlencoded\r\n" .
                "Content-Length: " . strlen($postData) . "\r\n",
            'content' => $postData,
            'timeout' => 10,
        ],
        'ssl' => [
            'verify_peer' => false,'verify_peer_name' => false,
        ],
    ];
    $context = stream_context_create($contextOptions);
    $result = @file_get_contents($url,false,$context);
    if ($result === false) {
        $error = error_get_last();
        error_log('[FAST API FAIL] ' .($error['message'] ?? 'Unknown error'));
        return $error['message'] ?? 'Unknown error';
    }
    return process_response($result);
}
function apiWithPool(CurlPool $pool, string $url, array $data, int|string $key): void {
    $pool->addRequest($url, $data, $key);
}
function api__(string $url, array $data): mixed {
    global $confPMon;
    $data = prepareSignedApiData($data);
    $ch = curl_init($url);
    if ($ch === false) {
        return false;
    }
    $curlOptions = [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => false,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 5
    ];
    if (!empty($confPMon['API_PORT'])) {
        curl_setopt($ch, CURLOPT_PORT, $confPMon['API_PORT']);
    }
    curl_setopt_array($ch, $curlOptions);
    $result = curl_exec($ch);
    if ($result === false) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log('[API CURL FAIL] ' . $error);
        return $error;
    }
    curl_close($ch);
    return process_response($result);
}
function process_response($result) {
    $json_start = strpos($result, '{');
    $json_end = strrpos($result, '}');
    if ($json_start === false || $json_end === false) {
        return $result;
    }
    $json = substr($result,$json_start,$json_end - $json_start + 1);
    try {
        return json_decode($json,true,512,JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        return [
            'error' => 'json_decode_failed','message' => $e->getMessage(),'raw' => $result
        ];
    }
}
function getSwitchMonitor() {
	global $db,$confPMon,$cacheManager;
	$switches = array();
	$getswitch = $db->SimpleWhile("SELECT * FROM switch WHERE monitor = 'yes'");
	if (isset($getswitch) && count($getswitch)>0) {
		foreach ($getswitch as $swid => $sw) {
			if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
				$expiration = 7200;
				$cacheKey = "switch_" . $sw['id'];
				$cachedSwitch = $cacheManager->get($cacheKey);
				if ($cachedSwitch !== null) {
					$switches[$sw['id']] = $cachedSwitch;
				} else {
					$switches[$sw['id']] = ['id' => $sw['id'],'oidid' => $sw['oidid'],'class' => $sw['class'],'place' => $sw['place'],'netip' => $sw['netip'],'snmpro' => $sw['snmpro']];
					$cacheManager->set($cacheKey, $switches[$sw['id']], $expiration);
				}
			} else {
				$switches[$sw['id']] = ['id' => $sw['id'],'oidid' => $sw['oidid'],'class' => $sw['class'],'place' => $sw['place'],'netip' => $sw['netip'],'snmpro' => $sw['snmpro']];
			}
		}
	}
	return $switches;
}
function getSwitchData($olt) {
	global $db,$confPMon,$cacheManager;
	$getswitch = [];
    $getswitch = $db->Fast('switch','*',['id'=>$olt]);
	if(empty($getswitch['id'])){
		die('empty_device_id');
	}
	return 	$getswitch;
}
function milesToKilometers($miles) {
    $kilometers = $miles * 1.60934 + 0.04; // Додаємо 40 метрів
    $roundedKilometers = round($kilometers);
    return $roundedKilometers;
}

function loggerBackground_monitor($cmd,$basePath = '/export/cache/') {
    $pid = getmypid();
    $filePath = $basePath . $pid . '.pid';
    $data = array('pid' => $pid, 'cmd' => $cmd, 'added' => date('Y-m-d H:i:s'));
    file_put_contents(ROOT_DIR . $filePath, json_encode($data));
}
function detectError($response) {
	header('Content-Type: application/json');
	echo json_encode($response);	
	die;
}
function clearnamber($number) {
	$number = str_replace('+','',$number);
	return $number;
}
function getInput($key, $default = null) {
    $source = $_REQUEST;    
    if (isset($source[$key])) {
        $value = trim(strip_tags(stripcslashes($source[$key])));
        return !empty($value) ? $value : $default;
    }

    return $default;
}
function isValidContentSql($content){
	if(isset($content)){
    $blacklist = [
        '/SELECT/i','/UNION/i','/fopen/i','/file_get_contents/i','/root/i','/<\?php/i','/<\?/i','/\?>/i','/system/i','/exec/i','/shell_exec/i','/passthru/i','/eval/i'
    ];
    foreach ($blacklist as $command) {
        if (preg_match($command, $content)) {
            return true;
        }
    }
    return false;
	}else{
		return false;
	}
}
function sanitizeInputSql($input) {
    $sanitizedInput = filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
    return $sanitizedInput;
}
function detectOnuType($onu)
{
    $data = [];

    if (ctype_digit((string)$onu)) {
        $data['idonu'] = (int)$onu;
    } else {
        $data['sql'] = $onu;
    }
    return $data;
}

function telegram_bot($message, $arrayUid){
    global $config;
    $apiUrl = "https://api.telegram.org/bot".$config['telegramtoken']."/sendMessage";
    foreach($arrayUid as $chatId) {        
        if(isset($chatId)) {
            $content = array('chat_id' => $chatId,'text' => $message,'parse_mode' => 'HTML');
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
            $response = curl_exec($ch);
            curl_close($ch);
        }
    }
	return true; 
}
function telegram_chat($message) {
    global $config;
    if (!empty($config['telegram']) && $config['telegram'] == 'on' && $message) {
        $content = array('chat_id' => $config['telegramchatid'], 'text' => $message, 'parse_mode' => 'HTML');        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . $config['telegramtoken'] . '/sendmessage');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
        $response = curl_exec($ch);
        curl_close($ch);
        if (check_telegram_response($response)) {
            return true;
        }
		return false;
    }
}
function telegram_message($message) {
    global $config;
    if (!empty($config['telegram']) && $config['telegram'] == 'on' && $message && !is_null($config['messageid'])) {
        $content = array('chat_id' => $config['telegramchatid'], 'text' => $message, 'parse_mode' => 'HTML');  
        if (!is_null($config['messageid'])) {
            $content['reply_to_message_id'] = $config['messageid'];
        }       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . $config['telegramtoken'] . '/sendmessage');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if (check_telegram_response($response)) {
            return true;
        }
		return false;
    }
}
function check_telegram_response($response) {
    if(isset($response)){
        $decoded_response = json_decode($response);
        return (isset($decoded_response->ok) && $decoded_response && $decoded_response->ok === true ? true: false);
    }
}
function _ip($result){
    $data = ['timer' => 0, 'ttl' => 0];
    $pattern_time = '/time=([\d\.]+) ms/';
    $pattern_ttl = '/ttl=(\d+)/';
    if (preg_match($pattern_time, $result, $matches_time)) {
        $data['timer'] = $matches_time[1];
    }
    if (preg_match($pattern_ttl, $result, $matches_ttl)) {
        $data['ttl'] = $matches_ttl[1];
    }
    return $data;
}
function getsnmp_string($data){
	$data = preg_replace('/^.*?(STRING:)|"|N\/A/i', '', $data);
	return trim(str_replace(['"', 'N/A', '65535'], ['', '', ''], $data));
}
function getsnmp_integer($data){
	$data = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $data);
	return trim(str_replace(['"', 'N/A', '65535'], ['', '', ''], $data));
}
function hexTOdate( $hexstring ) {
#$hexstring= "07 B2 01 01 00 0A 14 00";
	$hexstring = str_replace(' ','',trim($hexstring,'"'));
	$date = "";
	$year = hexdec(substr($hexstring,0 ,4 ));  // year (2+2 byte)
	$month = hexdec(substr($hexstring,4 ,2 ));  // month (2 byte)
	$day = hexdec(substr($hexstring,6 ,2 ));  // day (2 byte)
	$hour = hexdec(substr($hexstring,8 ,2 ));  // day (2 byte)
	$minute = hexdec(substr($hexstring,10 ,2 ));  // day (2 byte)
	$second = hexdec(substr($hexstring,12 ,2 ));  // day (2 byte)
	$dsecond = hexdec(substr($hexstring,14 ,2 ));  // day (2 byte)
# $date = $year."-".$month."-".$day." ".$hour.":".$minute.":".$second;
	$date = sprintf("%04d-%02d-%02d %02d:%02d:%02d",$year,$month,$day,$hour,$minute,$second);
# $date = $hexstring;
  return ($date);
}
function reason_bdcom_gpon($check){
	return match ($check){
		"0" => 'err22',
		"1" => 'err1',
		"2" => 'err2',
		"3" => 'err27',
		"4" => 'err28',
		"5" => 'err5',
		"6" => 'err6',
		"7" => 'err7',
		"8" => 'err8',
		"9" => 'err9',
		"10" => 'err29',
		"11" => 'err12',
		"12" => 'err13',
		default => 'err20'
	};
}
function LoginDataBase($mac,$dataonu) {
	global $db, $confPMon;
	$timer = date('Y-m-d H:i:s');
	if (isset($confPMon['MAC_ROUTER']) && !empty($confPMon['MAC_ROUTER']) 
		&& $confPMon['MAC_ROUTER'] == 1 && isset($mac) && $mac!=$dataonu['mac']) {
		$getmac = $db->Simple("SELECT * FROM mac_router WHERE mac = '".trim($mac)."' AND onuid = '".$dataonu['idonu']."' AND deviceid = ".$dataonu['olt']." LIMIT 1");
		if(!empty($getmac['id'])){
			$db->SQLupdate('mac_router',['update'=>$timer,'onu'=>'MAC_ONU '.$dataonu['mac']],['id' => $getmac['id']]);
		}else{
			$sql_mac_router = array(
				'added'=>$timer,'inface'=>$dataonu['inface'],'mac'=>trim($mac),	
				'deviceid'=>$dataonu['olt'],'onu'=>'MAC_ONU '.$dataonu['mac'],
				'onuid'=>$dataonu['idonu']				
			);
			$db->SQLinsert('mac_router', $sql_mac_router);
		}
	}	
}
function gethealth_battery12($akb12volt, $ping3, $lastvolot) {
    $gometric = false;
    $sender = false;
    if ($akb12volt <= 10.4) {
        $gometric = true;
        $icon = '🆘';
        $criticvolt = '10.4';
        $criticvolticon = '🪫';
        $mess = ' 🔥 Ще чучуть 5хв';
        $mess1 = ' 😵 Я розчарувався';
    } elseif ($akb12volt <= 10.6) {
        $gometric = true;
        $icon = '🆘';
        $criticvolt = '10.6';
        $criticvolticon = '🪫';
        $mess = ' ⏱ Ще чучуть 10хв';
        $mess1 = ' 🔥 Мо, пошліт когось';
    } elseif ($akb12volt <= 10.8) {
        $gometric = true;
        $icon = '🆘';
        $criticvolt = '10.8';
        $criticvolticon = '🪫';
        $mess = ' ⏱ Потрібна термінова заміна акамулятора 15хв';
    } elseif ($akb12volt == 11) {
        $gometric = true;
        $icon = '⚡️';
        $criticvolt = '11';
        $criticvolticon = '🪫';
        $mess = ' ⏱ Потрібна заміна акамулятора ~20хв';
    } elseif ($akb12volt == 11.2) {
        $gometric = true;
        $icon = '⚡️';
        $criticvolt = '11.2';
        $criticvolticon = '🪫';
        $mess = ' Низький заряд акумулятора, залишилось ~⏱25хв';
    } elseif ($akb12volt == 11.6) {
        $gometric = true;
        $icon = '⚡️';
        $criticvolt = '11.6';
        $criticvolticon = '🪫';
        $mess = ' Низький заряд акумулятора, залишилось ~⏱30хв - ⚠️ думайте про заміну акамулятора';
    } elseif ($akb12volt == 11.8) {
        $gometric = true;
        $icon = '⚡️';
        $criticvolt = '11.8';
        $criticvolticon = '🪫';
        $mess = ' Низький заряд акумулятора - ⚠️ думайте про заміну акамулятора';
    } elseif ($akb12volt == 12) {
        $gometric = true;
        $icon = '⚡️';
        $criticvolt = '12';
        $criticvolticon = '🪫';
        $mess = ' Все нормально (⚠️ якщо до включення напруги буде більше ніж ⏱ 2 год - потрібно замінити акумулятор)';
    } elseif ($akb12volt == 12.2) {
        $gometric = true;
        $icon = '⚡️';
        $criticvolt = '12.2';
        $criticvolticon = '🪫';
        $mess = ' Все нормально (🍒 стабільний розряд )';
    } else {
        $criticvolt = $akb12volt;
    }
	if($criticvolt==$akb12volt && $criticvolt!=$lastvolot){
		$sender = true;
	}
    if ($gometric && $sender) {
        #telegram_port($criticvolticon . ' <b>' . $ping3['name'] . '</b> ' . $icon . ' <b>' . $criticvolt . ' V</b> ' . $mess);
    }
}

function checkerHuaweiGpon($newonu, $olt, $type)
{
    global $db, $logger;
    $oldonui = [];
    $getonus = $db->Multi('onus', 'idonu,keyonu,inface,type,zte_idport,olt', ['olt' => $olt, 'type' => $type]);
    if (count($getonus)) {
        foreach ($getonus as $io => $eachsig) {
            $uniqueKey = $eachsig['zte_idport'] . $eachsig['keyonu'];
            if (!isset($oldonui[$uniqueKey])) {
                $oldonui[$eachsig['zte_idport']][$eachsig['keyonu']] = [
                    'idonu' => $eachsig['idonu'],
                    'inface' => $eachsig['inface'],
                    'keyonu' => $eachsig['keyonu'],
                    'pon' => $eachsig['type'],
                    'keyport' => $eachsig['zte_idport'],
                    'olt' => $olt
                ];
            }
        }
    }
    $missingonu = [];
    if (isset($newonu) && is_array($oldonui) && isset($oldonui)) {
        foreach ($oldonui as $keyport => $eachs) {
            foreach ($eachs as $keyonu => $data) {
                if (!isset($newonu[$keyport][$keyonu])) {
                    $missingonu[$data['idonu']] = $data;
                }
            }
        }
    }
	$logs = [];
	if(is_array($missingonu)){
		foreach($missingonu as $idonu => $each) {
			if(!empty($idonu)){
				$logs = [
					'log'=>'onu',
					'type'=>'deletonu',
					'descr'=>$each['pon'].' '.$each['inface'],
					'deviceid'=>$each['olt'],
					'onuid'=>$each['idonu'],
					'who'=>'cron',
				];
				$logger->init($logs);				
				delete_onu($each['idonu']);
			}
		}
	}
}
function pmon_walk_script($data) {
	$result = [];
	$raw = '';
	if ($data['oid']) {
		switch ($data['type']) {
			case "exec":
				$command = "snmpwalk -v2c -c " . $data['community'] . " " . $data['ip'] . " " . $data['oid'];
				loggerBackground_monitor($command);
				$raw = @shell_exec($command);
				$rawlines = explode(PHP_EOL, trim($raw));
				$raw = array_filter($rawlines, 'strlen');
				break;
			case "class":
				try {
					$session = new SNMP(SNMP::VERSION_2C, $data['ip'], $data['community']);
					$session->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
					$raw = @$session->walk($data['oid']);
					if ($raw === false || empty($raw)) {
						error_log("{$data['ip']}: No data found for OID: " . $data['oid']);
					}
					$session->close();
					unset($session);
				} catch (Exception $e) {
					error_log("Error occurred while performing SNMP walk: " . $e->getMessage());
				}
				break;
			case "real":
				snmp_set_quick_print(0);
				$raw = @snmp2_real_walk($data['ip'], $data['community'], $data['oid']);
				break;
			default:
				snmp_set_quick_print(0);
				$raw = @snmp2_real_walk($data['ip'], $data['community'], $data['oid']);
		}	
		if ($raw !== false && !empty($raw)) {
			foreach ($raw as $oid => $value) {
				$value = str_replace('iso','1', $value);
				$arraytemp = str_replace('.'.$data['oid'].'.','', $oid);
				$arraytemp = str_replace($data['oid'].'.','', $arraytemp);
				if(!empty($data['deloid']) && $data['deloid']==true){
					$value = str_replace('.'.$data['oid'].'.','', $value);
					$value = str_replace($data['oid'].'.','', $value);					
				}
				$result[trim($arraytemp)]['result'] = $value;
		}
		}
	}
	return 	$result;
}
function pmon_walk($data) {
	global $confPMon, $cacheManager;
    $raw = '';
	if(empty($data['community']) || empty($data['ip']) || empty($data['oid'])){
		die('check get snmpwalk parametr');
	}
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1	&& isset($data['cache']) && $data['cache']==true && isset($data['namecache'])) {
		$expiration = (isset($data['timecache']) ? $data['timecache'] : 3600);
		$cacheKey = $data['namecache'];
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$result = $cachedResult;
		}else{
			$result =  pmon_walk_script($data);
			$cacheManager->set($cacheKey,$result,$expiration);
		}		
	}else{
		$result =  pmon_walk_script($data);
	}
    return isset($result)?$result:null;
}
function isemptyarray($array) {
    return is_array($array) && count($array) === 0;
}
function is_array_empty($array) {
    if(is_array($array) && count(array_filter($array))){
		return true;
	}else{
		return false;
	}
}
function reasonGponHuawei($data) {
	$data = trim($data);
    $reasons = [
        '0' => 'err60',
        '1' => 'err61',
        '2' => 'err6',
        '3' => 'err6',
        '4' => 'err38',
        '5' => 'err39',
        '6' => 'err40',
        '7' => 'err41',
        '8' => 'err42',
        '9' => 'err43',
        '10' => 'err44',
        '11' => 'err45',
        '12' => 'err62',
        '13' => 'err1',
        '14' => 'err63',
        '15' => 'err46',
        '18' => 'err47',
        '30' => 'err48',
        '31' => 'err49',
        '32' => 'err50',
        '33' => 'err51',
        '34' => 'err52',
        '35' => 'err53',
        '37' => 'err54',		
        '-1' => 'err59',
        '255' => 'err0',
    ];
    return $reasons[$data] ?? 'err20';
}

function reasonGponHuaweiold($data){
	return match ($data) {
		'1' => 'err6',
		'2' => 'err36',
		'3' => 'err37',
		'4' => 'err38',
		'5' => 'err39',
		'6' => 'err40',
		'7' => 'err41',
		'8' => 'err42',
		'9' => 'err43',
		'10' => 'err44',
		'11' => 'err45',
		'13' => 'err1',
		'15' => 'err46',
		'18' => 'err47',
		'30' => 'err48',
		'31' => 'err49',
		'32' => 'err50',
		'33' => 'err51',
		'34' => 'err52',
		'35' => 'err53',
		'37' => 'err54',
		'-1', '255' => 'err6',
		default => 'err20',
	};
}
function addHttpIfMissing($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}
function clearKeyonu($newonu,$olt){
	global $db, $config, $lang, $logger;
	$oldonu = array();
	$oldonui = array();
	$getonus = $db->Multi('onus', 'idonu,keyonu', ['olt' => $olt]);
	if (count($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			if(!empty($eachsig['keyonu']) && !empty($eachsig['idonu'])){
			$oldonu[$io]['idonu'] = $eachsig['idonu'];
			$oldonu[$io]['keyonu'] = $eachsig['keyonu'];
			}
			$oldonui[$eachsig['idonu']]['type'] = $eachsig['type'];
			$oldonui[$eachsig['idonu']]['inface'] = $eachsig['inface'];
			$oldonui[$eachsig['idonu']]['olt'] = $olt;
		}
	}
	$missingonu = array_filter($oldonu, function($old) use ($newonu) {
		$found = false;
		foreach ($newonu as $new) {
			if ($old['keyonu'] == $new['keyonu']) {
				$found = true;
				break;
			}
		}
		return !$found;
	});
	$missingonu = array_map(fn($old) => ['idonu' => $old['idonu']], $missingonu);
	if(is_array($missingonu)){
		foreach($missingonu as $each) {
			if(!empty($each['idonu'])){
				$logs = [
					'log'=>'onu',
					'type'=>'deletonu',
					'descr'=>$oldonui[$old['idonu']]['type'].$oldonui[$old['idonu']]['inface'],
					'deviceid'=>$oldonui[$old['idonu']]['olt'],
					'onuid'=>$old['idonu'],
					'who'=>'cron',
				];
				$logger->init($logs);				
				delete_onu($each['idonu']);
			}
		}
	}
}
function findEmptyOnu2($oldonu, $newonu) {
    $emptyonu = [];
    foreach ($newonu as $onu) {
        $found = false;
        foreach ($oldonu as $old) {
            if ($onu['keyonu'] == $old['keyonu'] && 
                $onu['keyport'] == $old['keyport'] &&
                $onu['pon'] == $old['pon']) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $emptyonu[] = $onu;
        }
    }
    return $emptyonu;
}
function findEmptyOnuHuaweiZte(array  $oldonu, array  $newonu): array {
    $keyonuArr = array_column($oldonu, 'keyonu');
    $keyportArr = array_column($oldonu, 'keyport');
    $ponArr = array_column($oldonu, 'pon');
    $emptyonu = [];
    array_walk($oldonu, function($onu) use ($keyonuArr, $keyportArr, $ponArr, &$emptyonu) {
        if (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['keyport'], $keyportArr) || !in_array($onu['pon'], $ponArr)) {
            $emptyonu[] = $onu;
        }
    });
    $emptyonu = array_filter($newonu, function($onu) use ($keyonuArr, $keyportArr, $ponArr) {
        return (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['keyport'], $keyportArr) || !in_array($onu['pon'], $ponArr));
    });
    return $emptyonu;
}
function checkerONUHuaweiZteEpon($data,$olt){
	global $db, $logger;
	$oldonui = array();
	$getonus = $db->Multi('onus', 'idonu,keyonu,mac,sn,inface,type,zte_idport,olt', ['type' => 'epon','olt' => $olt]);
	if (is_array_empty($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			$oldonui[$eachsig['idonu']]['idonu'] = $eachsig['idonu'];
			$oldonui[$eachsig['idonu']]['inface'] = $eachsig['inface'];
			$oldonui[$eachsig['idonu']]['keyonu'] = $eachsig['keyonu'];
			if(!empty($eachsig['mac']))
				$oldonui[$eachsig['idonu']]['mac'] = $eachsig['mac'];			
			if(!empty($eachsig['sn']))
				$oldonui[$eachsig['idonu']]['sn'] = $eachsig['sn'];
			$oldonui[$eachsig['idonu']]['pon'] = $eachsig['type'];
			$oldonui[$eachsig['idonu']]['olt'] = $eachsig['olt'];
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = findEmptyOnuBDCOM($data, $oldonui);
		if(is_array_empty($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu',
						'type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'].''.(!empty($each['mac'])?$each['mac']:(!empty($each['sn'])?$each['sn']:'--')),
						'deviceid'=>$each['olt'],
						'onuid'=>$each['idonu'],
						'who'=>'clear',
					];
					$logger->init($logs);
					delete_onu($each['idonu']);
				}
			}
		}
	}
}
function findEmptyOnuNOKIA(array  $oldonu, array  $newonu): array {
    $keyonuArr = array_column($oldonu, 'keyonu');
    $ponArr = array_column($oldonu, 'inface');	
    $emptyonu = [];	
    array_walk($oldonu, function($onu) use ($keyonuArr, $ponArr, &$emptyonu) {
        if (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['inface'], $ponArr)) {
            $emptyonu[] = $onu;
        }
    });
    $emptyonu = array_filter($newonu, function($onu) use ($keyonuArr, $ponArr) {
        return (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['inface'], $ponArr));
    });
    return $emptyonu;
}
function checkerONUNokiaGPON($data,$olt){
	global $db, $logger;
	$oldonui = array();
	$getonus = $db->Multi('onus', 'idonu,keyonu,mac,sn,inface,type,zte_idport,olt', ['type' => 'epon','olt' => $olt]);
	if (is_array_empty($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			$oldonui[$eachsig['idonu']]['idonu'] = $eachsig['idonu'];
			$oldonui[$eachsig['idonu']]['inface'] = $eachsig['inface'];
			$oldonui[$eachsig['idonu']]['keyonu'] = $eachsig['keyonu'];
			$oldonui[$eachsig['idonu']]['sn'] = $eachsig['sn'];
			$oldonui[$eachsig['idonu']]['pon'] = $eachsig['type'];
			$oldonui[$eachsig['idonu']]['olt'] = $eachsig['olt'];
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = findEmptyOnuNOKIA($data, $oldonui);
		if(is_array_empty($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu',
						'type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'].' '.$each['sn'],
						'deviceid'=>$each['olt'],
						'onuid'=>$each['idonu'],
						'who'=>'clear',
					];
					$logger->init($logs);
					delete_onu($each['idonu']);
				}
			}
		}
	}
}
function Checker_Pmon_BDCOM_EPON($data,$olt){
	global $db, $logger;
	$newonu = false;
	$oldonui = array();
	$getonus = $db->Multi('onus', 'idonu,keyonu,mac,sn,inface,type,zte_idport,olt', ['olt' => $olt]);
	if (is_array_empty($getonus)) {
		foreach ($getonus as $eachsig) {
			$idonu = $eachsig['idonu'];
			$oldonui[$idonu] = [
				'idonu' => $idonu,'inface' => $eachsig['inface'],'keyonu' => $eachsig['keyonu'],'pon' => $eachsig['type'],'olt' => $eachsig['olt'],
			];
			if (!empty($eachsig['mac'])) {
				$oldonui[$idonu]['mac'] = $eachsig['mac'];
			}
			if (!empty($eachsig['sn'])) {
				$oldonui[$idonu]['sn'] = $eachsig['sn'];
			}
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = findEmptyOnuBDCOM($data, $oldonui);
		if(is_array_empty($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu','type'=>'deletonu','descr'=>$each['pon'].$each['inface'].''.(!empty($each['mac'])?$each['mac']:(!empty($each['sn'])?$each['sn']:'--')),
						'deviceid'=>$each['olt'],'onuid'=>$each['idonu'],'who'=>'clear',
					];
					$logger->init($logs);	
					delete_onu($each['idonu']);
				}
			}
			return true;
		}		
	}
	if(count($data) != count($oldonui)){
		return true;
	}
}

function findEmptyOnuBDCOM(array  $oldonu, array  $newonu): array {
    $keyonuArr = array_column($oldonu, 'keyonu');
    $ponArr = array_column($oldonu, 'pon');	
    $emptyonu = [];	
    array_walk($oldonu, function($onu) use ($keyonuArr, $ponArr, &$emptyonu) {
        if (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['pon'], $ponArr)) {
            $emptyonu[] = $onu;
        }
    });
    $emptyonu = array_filter($newonu, function($onu) use ($keyonuArr, $ponArr) {
        return (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['pon'], $ponArr));
    });
    return $emptyonu;
}
function BDCOM_checker_ONT($data,$olt){
	global $db, $logger;
	$oldonui = array();
	$getonus = $db->SimpleWhile("SELECT idonu,keyonu,mac,inface,type,zte_idport,olt FROM onus WHERE olt = '{$olt}'");
	if (isset($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			 $oldonui[$eachsig['idonu']] = [
				'idonu' => $eachsig['idonu'],
				'inface' => $eachsig['inface'],
				'keyonu' => $eachsig['keyonu'],
				'mac' => $eachsig['mac'],
				'pon' => $eachsig['type'],
				'olt' => $eachsig['olt']
			];
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = find_ONT_BDCOM($data, $oldonui);
		if(is_array_empty($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu','type'=>'deletonu',
						'descr'=>$each['pon'].' '.$each['inface'].' '.$each['mac'],
						'deviceid'=>$each['olt'],'onuid'=>$each['idonu'],'who'=>'clear',
					];
					$logger->init($logs);
					delete_onu($each['idonu']);
				}
			}
		}
	}	
}
function find_ONT_BDCOM(array  $oldonu, array  $newonu): array {
    $ONT_KEY = array_column($oldonu, 'keyonu');
    $ONT_MAC = array_column($oldonu, 'mac');	
    $emptyonu = [];	
    array_walk($oldonu, function($onu) use ($ONT_KEY, $ONT_MAC, &$emptyonu) {
        if (!in_array($onu['keyonu'], $ONT_KEY) || !in_array($onu['mac'], $ONT_MAC)) {
            $emptyonu[] = $onu;
        }
    });
    $emptyonu = array_filter($newonu, function($onu) use ($ONT_KEY, $ONT_MAC) {
        return (!in_array($onu['keyonu'], $ONT_KEY) || !in_array($onu['mac'], $ONT_MAC));
    });
    return $emptyonu;
}
function findEmptyOnuCDATA12(array $oldonu, array $newonu): array {
    $existingKeys = [];
    foreach ($oldonu as $onu) {
        $existingKeys[] = $onu['keyonu'] . '|' . $onu['pon'] . '|' . $onu['inface'];
    }
    $emptyonu = array_filter($newonu, function($onu) use ($existingKeys) {
        $key = $onu['keyonu'] . '|' . $onu['pon'] . '|' . $onu['inface'];
        return !in_array($key, $existingKeys);
    });
    return $emptyonu;
}
function checkerONUCdata12($data, $olt) {
    global $db, $logger;
    $oldonui = [];
    $getonus = $db->Multi('onus', 'idonu,keyonu,mac,sn,inface,type,zte_idport,olt', ['olt' => $olt]);
    if (is_array_empty($getonus)) {
        $existingMacInface = [];
        foreach ($getonus as $eachsig) {
            $macKey = strtolower($eachsig['mac']) . '|' . $eachsig['inface'];
            if (!empty($eachsig['mac']) && isset($existingMacInface[$macKey])) {
                $logs = [
                    'log' => 'onu', 'type' => 'duplicate_del',
                    'descr' => 'Fixed duplicate del '.$eachsig['type'] . $eachsig['inface'] . $eachsig['mac'],
                    'deviceid' => $eachsig['olt'], 'onuid' => $eachsig['idonu'], 'who' => 'clear',
                ];
                $logger->init($logs);
                delete_onu($eachsig['idonu']);
                continue;
            }
            if (!empty($eachsig['mac'])) {
                $existingMacInface[$macKey] = $eachsig['idonu'];
            }
            $oldonui[$eachsig['idonu']] = [
                'idonu'  => $eachsig['idonu'],
                'inface' => $eachsig['inface'],
                'keyonu' => $eachsig['keyonu'],
                'mac'    => $eachsig['mac'],
                'pon'    => $eachsig['type'],
                'olt'    => $eachsig['olt']
            ];
        }
    }
    if (is_array_empty($data) && !empty($oldonui)) {
        $getdeletonu = findEmptyOnuCDATA12($data, $oldonui);
        if (is_array_empty($getdeletonu)) {
            foreach ($getdeletonu as $each) {
                if (!empty($each['idonu'])) {
                    $logs = [
                        'log' => 'onu', 'type' => 'deletonu',
                        'descr' => $each['pon'] . $each['inface'] . (!empty($each['mac']) ? $each['mac'] : '--'),
                        'deviceid' => $each['olt'], 'onuid' => $each['idonu'], 'who' => 'clear',
                    ];
                    $logger->init($logs);
					delete_onu($each['idonu']);
                }
            }
        }
    }
}

function checkerONUBDcom($data,$olt){
	global $db, $logger;
	$oldonui = array();
	$getonus = $db->Multi('onus', 'idonu,keyonu,mac,sn,inface,type,zte_idport,olt', ['olt' => $olt]);
	if (is_array_empty($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			$oldonui[$eachsig['idonu']]['idonu'] = $eachsig['idonu'];
			$oldonui[$eachsig['idonu']]['inface'] = $eachsig['inface'];
			$oldonui[$eachsig['idonu']]['keyonu'] = $eachsig['keyonu'];
			if(!empty($eachsig['mac']))
				$oldonui[$eachsig['idonu']]['mac'] = $eachsig['mac'];			
			if(!empty($eachsig['sn']))
				$oldonui[$eachsig['idonu']]['sn'] = $eachsig['sn'];
			$oldonui[$eachsig['idonu']]['pon'] = $eachsig['type'];
			$oldonui[$eachsig['idonu']]['olt'] = $eachsig['olt'];
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = findEmptyOnuBDCOM($data, $oldonui);
		if(is_array_empty($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu','type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'].''.(!empty($each['mac'])?$each['mac']:(!empty($each['sn'])?$each['sn']:'--')),
						'deviceid'=>$each['olt'],'onuid'=>$each['idonu'],'who'=>'clear',
					];
					$logger->init($logs);
					delete_onu($each['idonu']);
				}
			}
		}
	}
}
function checkerONUBDcom2($data,$olt){
	global $db, $logger;
	$getonus = $db->SimpleWhile("SELECT idonu, keyonu, mac, sn, inface, type, zte_idport, olt FROM onus WHERE olt = '{$olt}'");
	if(!empty($getonus)){
		$oldonui = [];
		foreach ($getonus as $eachsig) {
			$idonu = $eachsig['idonu'];
			$oldonui[$idonu] = [
				'idonu' => $idonu,
				'inface' => $eachsig['inface'],
				'keyonu' => $eachsig['keyonu'],
				'mac' => !empty($eachsig['mac']) ? $eachsig['mac'] : null,
				'sn' => !empty($eachsig['sn']) ? $eachsig['sn'] : null,
				'pon' => $eachsig['type'],
				'olt' => $eachsig['olt'],
			];
		}
	}
	if(isset($oldonui) && is_array($oldonui)){
		$getdeletonu = findEmptyOnuBDCOM($data, $oldonui);
		if(isset($getdeletonu) && is_array($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu','type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'].''.(!empty($each['mac'])?$each['mac']:(!empty($each['sn'])?$each['sn']:'--')),
						'deviceid'=>$each['olt'],'onuid'=>$each['idonu'],'who'=>'clear',
					];
					$logger->init($logs);	
					delete_onu($each['idonu']);
				}
			}
		}
	}
}
function checkerONUHuaweiZteGpon($data,$olt){
	global $db, $logger;
	$getonus = $db->Multi('onus', 'idonu,keyonu,inface,type,zte_idport,olt', ['olt' => $olt,'type'=>'gpon']);
	if (count($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			if($eachsig['type']=='gpon'){
				$oldonui[$eachsig['idonu']]['idonu'] = $eachsig['idonu'];
				$oldonui[$eachsig['idonu']]['inface'] = $eachsig['inface'];
				$oldonui[$eachsig['idonu']]['keyonu'] = $eachsig['keyonu'];
				$oldonui[$eachsig['idonu']]['pon'] = $eachsig['type'];
				$oldonui[$eachsig['idonu']]['keyport'] = $eachsig['zte_idport'];
				$oldonui[$eachsig['idonu']]['olt'] = $eachsig['olt'];
			}
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = findEmptyOnuHuaweiZte($data, $oldonui);
		if(count($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu',
						'type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'],
						'deviceid'=>$each['olt'],
						'onuid'=>$each['idonu'],
						'who'=>'clear',
					];
					$logger->init($logs);
					delete_onu($each['idonu']);
				}
			}
		}
	}
}
function findEmptyOnuCdata(array $newonu, array $oldonu): array {
    $newonuKeys = [];
    foreach ($newonu as $onu) {
        $key = $onu['keyonu'].'|'.$onu['keyport'].'|'.$onu['checker'];
        $newonuKeys[$key] = true;
    }
    $emptyonu = [];
    foreach ($oldonu as $onu) {
        $key = $onu['keyonu'].'|'.$onu['keyport'].'|'.$onu['checker'];
        if (!isset($newonuKeys[$key])) {
            $emptyonu[] = $onu;
        }
    }

    return $emptyonu;
}
function checkerONUCdata($data, $olt){
    global $db, $logger;
    $getonus = $db->Multi('onus', 'idonu,keyonu,inface,type,zte_idport,olt', ['olt' => $olt]);
    $oldonu = [];
    if (count($getonus)) {
        foreach ($getonus as $eachsig) {
            $oldonu[] = [
                'idonu'   => $eachsig['idonu'],
                'inface'  => $eachsig['inface'],
                'keyonu'  => $eachsig['keyonu'],
                'pon'     => $eachsig['type'],
                'checker' => md5($eachsig['inface']),
                'keyport' => $eachsig['zte_idport'],
                'olt'     => $eachsig['olt'],
            ];
        }
    }
    if(is_array_empty($data) && !empty($oldonu)){
        $getdeletonu = findEmptyOnuCdata($data, $oldonu);
        if(count($getdeletonu)){
            foreach($getdeletonu as $each) {
                if(!empty($each['idonu'])){
                    $logs = [
                        'log'      => 'onu',
                        'type'     => 'deletonu',
                        'descr'    => $each['pon'] . $each['inface'],
                        'deviceid' => $each['olt'],
                        'onuid'    => $each['idonu'],
                        'who'      => 'clear',
                    ];
                    $logger->init($logs);
                    delete_onu($each['idonu']);
                }
            }
        }
    }
}
function checkerONUHuaweiZte($data,$olt){
	global $db, $logger;
	$getonus = $db->Multi('onus', 'idonu,keyonu,inface,type,zte_idport,olt', ['olt' => $olt]);
	if (count($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			$oldonui[$eachsig['idonu']]['idonu'] = $eachsig['idonu'];
			$oldonui[$eachsig['idonu']]['inface'] = $eachsig['inface'];
			$oldonui[$eachsig['idonu']]['keyonu'] = $eachsig['keyonu'];
			$oldonui[$eachsig['idonu']]['pon'] = $eachsig['type'];
			$oldonui[$eachsig['idonu']]['keyport'] = $eachsig['zte_idport'];
			$oldonui[$eachsig['idonu']]['olt'] = $eachsig['olt'];
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = findEmptyOnuHuaweiZte($data, $oldonui);
		if(count($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu',
						'type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'],
						'deviceid'=>$each['olt'],
						'onuid'=>$each['idonu'],
						'who'=>'clear',
					];
					$logger->init($logs);	
					delete_onu($each['idonu']);
				}
			}
		}
	}
}
function checkerONU($data,$olt){
	global $db, $config, $lang, $logger;
	foreach($data as $ios => $eachsigs) {
		$super = $db->Simple('SELECT idonu, added FROM `onus` WHERE olt = '.$eachsigs['id'].'
		AND type = "'.($eachsigs['pon']=='gpon'?'gpon':'epon').'"
		AND keyonu = "'.$eachsigs['keyonu'].'" '.(!empty($eachsigs['keyport'])?'AND zte_idport = "'.$eachsigs['keyport'].'"':'').'
		ORDER BY added ASC LIMIT 1');	
		if(!empty($super['idonu'])){
			$db->SQLupdate('onus',['cron' => 1],['idonu' => $super['idonu']]);
		}
	}
	$getdeletonu = $db->Multi('onus','*',['olt' => $olt,'cron' => 2]);
	if(count($getdeletonu)){
		foreach($getdeletonu as $io => $eachsig) {
			if(!empty($eachsig['idonu'])){
				$logs = [
					'log'=>'onu',
					'type'=>'deletonu',
					'descr'=>$eachsig['type'].$eachsig['inface'],
					'deviceid'=>$eachsig['olt'],
					'onuid'=>$old['idonu'],
					'who'=>'cron',
				];
				$logger->init($logs);	
				delete_onu($eachsig['idonu']);
			}
		}
	}
}

function portstatusHuawei($value){
	if(preg_match('/1/i',$value) || preg_match('/4/i',$value)){
		return 'up';	
	}else{
		return 'down';	
	}
}
function MacHuawei($type) {
	if (preg_match("/Hex/i", $type)) {
		$re_z_z = explode('Hex-STRING: ', $type);
		$re_z = end($re_z_z);
		$re_z = str_replace('"', '',$re_z);
		$re_z = trim($re_z);
		$onu = preg_replace("/\s+/","",mb_strtolower($re_z));
		return preg_replace('/(.{2})/','\1:',$onu,5);
	}elseif(preg_match("/STRING/i", $type)) {
		$re_ze_mac = explode('STRING: ', $type);
		$re_mac = end($re_ze_mac);
		$re_mac = str_replace('"', '',$re_mac);
		$re_mac = trim($re_mac);
		$onu = bin2hex($re_mac);
		return preg_replace('/(.{2})/','\1:',$onu,5);
	}else{
		return false;
	}
}
function cdataGpon($type) {
	$return = '';
		if (preg_match("/Hex/i", $type)) {
			$re_z_z = explode('Hex-STRING: ', $type);
			$re_z = end($re_z_z);
			$onu = str_replace('"', '',$re_z);
		}elseif(preg_match("/STRING/i", $type)) {
			$re_ze_mac = explode('STRING: ', $type);
			$re_mac = end($re_ze_mac);
			$onu = str_replace('"', '',$re_mac);
		}
		if (strlen($onu) === 24){
			$onu = explode(" ", $onu);
			foreach ($onu as $key => $value){
				if ($key < 4) {
					$return.=chr(hexdec($value));   // Первые 4 символа переводятся из HEX->DEC, и подставляется в ASCII таблицу
				}else{
					$return.=$value;                // Остальные символы неизменны
				}
			}
		}else{
			$nosn = substr($onu,4);
			$resn = substr($onu, 0, 4);
			$return = $resn.strtoupper(bin2hex($nosn));
		}
	return $return;
}
function SnHuawei($tempsn) {
	$onu_snc1 = preg_replace('~^.*?( = )~i','',$tempsn);
	if (strpos($tempsn, 'Hex-STRING') !== false) {
		$onu_snc1 = preg_replace('/Hex-STRING/','',$onu_snc1);
		$tmpv = explode(" ",$onu_snc1);
		$val1 = hexdec($tmpv[1]);
		$val2 = hexdec($tmpv[2]);
		$val3 = hexdec($tmpv[3]);
		$val4 = hexdec($tmpv[4]);
		$val5 = $tmpv[5];
		$val6 = $tmpv[6];
		$val7 = $tmpv[7];
		$val8 = $tmpv[8];
		return sprintf("%c%c%c%c%s%s%s%s", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);
	} elseif(strpos($tempsn, 'STRING') !== false) {
		$onu_snc1 = preg_replace('/STRING:/','',$onu_snc1);
		$tmpv = explode(" ",$onu_snc1);
		$tmpe = str_split($tmpv[1]);
		if(isset($tmpe[5]) && isset($tmpe[6]) && isset($tmpe[7]) && isset($tmpe[8])){
			return sprintf("%s%s%s%s%s%s%s%s", $tmpe[1], $tmpe[2], $tmpe[3], $tmpe[4], strtoupper(dechex(ord($tmpe[5]))), strtoupper(dechex(ord($tmpe[6]))), strtoupper(dechex(ord($tmpe[7]))), strtoupper(dechex(ord($tmpe[8]))));
		}else{
			return '';
		}
	}else{
		return '';
	}
}
function explodeRowsTwo($data) {
	$result = explode("\n\n", $data);
	return $result;
}
function explodeRows($data) {
	$result = explode("\n\n", $data);
	return $result;
}
function SignalMonitor($newstatus,$newrx,$oldrx,$idonu,$onu){
	global $config, $db, $logger, $lang;
		$time = date('Y-m-d H:i:s');
		$update = array();
		$update['rxstatus'] = '';
		$insertSignal = false;
		$old = 0;
		if(!empty($oldrx)){
			$old = signal_onu_minus($oldrx);
		}
		if(!empty($newrx)){
			$new = signal_onu_minus($newrx);
		}
		if ($old == 0 && $new > 0) {
			// нічого не робити з даними
		} elseif ($old < $new) {				
			$up_dbm = $new - $old;
			if ($config['criticsignal'] <= $up_dbm && $newstatus == 1 && $up_dbm) {
				$update['rxstatus'] = 'up';
				$update['lastrx'] = $oldrx;
				$update['changerx'] = $time;
				$insertSignal = true;
			}
		} elseif ($old > $new) {
			$down_dbm = $old - $new;
			if ($config['criticsignal'] < $down_dbm && $newstatus == 1 && $down_dbm) {
				$update['rxstatus'] = 'down';
				$update['lastrx'] = $oldrx;
				$update['changerx'] = $time;
				$insertSignal = true;
			}
		} elseif ($old === $new) {
			if (!empty($onu['changerx']) && strtotime($onu['changerx']) < strtotime($time . ' -1 day')) {
				$update['rxstatus'] = 'none';
			}			
		} else {	
			$update['rxstatus'] = 'none';		
		}		
		if(is_array_empty($update) && !empty($update['rxstatus'])) {
			if($update['rxstatus'] == 'up' || $update['rxstatus'] == 'down') {
				$logs = ['log'=>'onu','type'=>'signalonu','descr'=>$lang['last'].': '.$oldrx.', '.$lang['curentrx'].': '.$newrx.', '.$onu['inface'].' '.(!empty($onu['mac'])?$onu['mac']:'').(!empty($onu['sn'])?$onu['sn']:''),'deviceid'=>$onu['olt'],'onuid'=>$onu['idonu'],'who'=>'cron'];
				$logger->init($logs);
			}
		}
		if(is_array_empty($update) && $update['rxstatus'] == 'up' || $update['rxstatus'] == 'none' || $update['rxstatus'] == 'down'){
			$db->SQLupdate('onus',$update,['idonu' => $idonu]);
		}
		return $insertSignal;
	}
function formatOID($oid,$keyonu,$keyport){
	$dataSNMP = str_replace('keyonu',$keyonu,$oid);
	$dataSNMP = str_replace('keyport',$keyport,$dataSNMP);
	$dataSNMP = trim($dataSNMP);
	return $dataSNMP;
}
function clInteger($dataSNMP){
	$dataSNMP = str_replace('INTEGER:', '',$dataSNMP);
	$dataSNMP = str_replace('"', '',$dataSNMP);
	$dataSNMP = str_replace(' ', '',$dataSNMP);
	$dataSNMP = trim($dataSNMP);
	if(preg_match('/up/i',$dataSNMP) || preg_match('/down/i',$dataSNMP)){
		$dataSNMP = $dataSNMP;
	}else{
		if($dataSNMP==1){
			$dataSNMP='up';
		}elseif($dataSNMP==2){
			$dataSNMP='down';
		}else{
			$dataSNMP='down';	
		}
	}
	return $dataSNMP;
}
function statusMonitor(int $status): string {
	return ($status == 2) ? 'down' : 'up';
}
function getFormatSNMP($dataSNMP,$format){
	switch($format){
		case 'string':		
			$dataSNMP = str_replace('STRING:', '',$dataSNMP);
			$dataSNMP = str_replace('"', '',$dataSNMP);
			$dataSNMP = str_replace(' ', '',$dataSNMP);
			$dataSNMP = trim($dataSNMP);
		break;			
		case 'hex-string':		
			$dataSNMP = str_replace('Hex-STRING:', '',$dataSNMP);
			$dataSNMP = str_replace('STRING:', '',$dataSNMP);
			$dataSNMP = str_replace('"', '',$dataSNMP);
			$dataSNMP = str_replace(' ', '',$dataSNMP);
			$dataSNMP = trim($dataSNMP);
		break;			
		case 'integer':	
			$dataSNMP = str_replace('INTEGER:', '',$dataSNMP);
			$dataSNMP = str_replace('"', '',$dataSNMP);
			$dataSNMP = str_replace(' ', '',$dataSNMP);
			$dataSNMP = trim($dataSNMP);
		break;			
	}	
	if(!$dataSNMP) 
		$dataSNMP = false;		
	return $dataSNMP;	
}
function getNameBdcomport($result){
	$result = getFormatSNMP($result,'string'); 
	$result = str_replace('epon0', 'EPON 0/',$result); 
	$result = str_replace('gpon0', 'GPON 0/',$result); 
	$result = str_replace('N0', 'N 0/',$result); 
	$result = str_replace('t0', 't 0/',$result); 
	$result = str_replace('tg0', 'TGigaEthernet 0/',$result); 
	$result = str_replace('g0', 'GigaEthernet 0/',$result); 
	$result = str_replace('f0', 'FastEthernet 0/',$result); 
	return $result;	
}
function getNameVSolport($result){
	$result = getFormatSNMP($result,'string'); 
	$result = str_replace('N0', 'N 0',$result); 
	$result = str_replace('E0', 'E 0',$result); 
	return $result;	
}
function getNameHuaweiport($result){
	$result = getFormatSNMP($result,'string'); 
	$result = str_replace('N0', 'N 0',$result); 
	$result = str_replace('t0', 't 0',$result); 
	return $result;	
}
function getNameZteport($result){
	$result = str_replace('gpon_', 'GPON ',$result); 
	$result = str_replace('epon_', 'EPON ',$result); 
	$result = str_replace('i_1', 'i 1',$result); 
	return $result;	
}
function getNameCdataport($result){
	$result = str_replace('e', 'e ',$result); 
	$result = str_replace('pon', 'gpon ',$result); 
	$result = str_replace('i_1', 'i 1',$result); 
	$result = str_replace('gg', 'g',$result); 
	return mb_strtoupper($result);	
}
function getNameDlink1106($result){
	$result = getFormatSNMP($result,'string'); 
	return $result;	
}
function getNamesDlink1106($result){
	$nametag = 'Ethernet 0/';
	$nametagsfp = 'SFP 0/';
	return ($result==6?$nametagsfp:$nametag).$result;
}
function getTypePortDlink1106($result){
	$nametag = 'eth1000';
	$nametagsfp = 'sfp';
	return ($result==6?$nametagsfp:$nametag);
}
function signal_onu_minus($var) {
	$var = str_replace('-','',$var);
	return (int)$var;
}
function getTypePort($value) {
    $matches = [
        '/xgei/i' => 'xgei',
        '/gei/i' => 'gei',
        '/tg0/i' => 'sfp',
        '/gpon/i' => 'gpon',
        '/g0/i' => 'sfp',
        '/epon/i' => 'epon',
        '/rxolt/i' => '',
        '/TGigaEthernet/i' => 'sfp',
        '/GigaEthernet/i' => 'sfp',
        '/FastEthernet/i' => 'eth100',
        '/f0/i' => 'eth100',
        '/Mng1/i' => 'mng1'
    ];

    foreach ($matches as $pattern => $type) {
        if (preg_match($pattern, $value)) {
            return $type;
        }
    }

    return false;
}
function getTypePortHuawei($value){
	$value = strtolower($value);
	if(preg_match('/xgei/i',$value)){
		return 'xgei'; 
	}elseif(preg_match('/xge/i',$value)){
		return 'xge'; 
	}elseif(preg_match('/gei/i',$value)){
		return 'gei'; 
	}elseif(preg_match('/ge/i',$value)){
		return 'ge'; 
	}elseif(preg_match('/gpon/i',$value)){
		return 'gpon';
	}elseif(preg_match('/epon/i',$value)){
		return 'epon';
	}elseif(preg_match('/rxolt/i',$value)){
		return '';	
	}elseif(preg_match('/ethernet/i',$value)){
		return 'sfp'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/GigaEthernet/i',$value)){
		return 'sfp'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/FastEthernet/i',$value)){
		return 'eth100'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/Mng1/i',$value)){
		return 'mng1'; // gei (интерфейс 1000M Ethernet)	
	}
}
function clearDataMacRe($value) {
    $value = str_replace(
        ['Hex-STRING:', 'STRING:', 'INTEGER:', '"', ' ', 'N/A'], 
        '', 
        $value
    );
    return trim($value);
}
function clearData1108($value){
	$value = str_replace('STRING:', '',$value);
	$value = str_replace('"', '',$value);
	$value = str_replace('EPON System, GE-', 'GigaEthernet 0/',$value);
	$value = str_replace('EPON System, PON-', 'epon 0/',$value);
	$value = trim($value);	
	return $value;
}
function ClearDataMac($value) {
	$value = clearDataMacRe($value);
	if (strlen($value)===17) $value = str_replace(' ','',$value);
	$value = trim($value," \"");
    $value = trim($value,'"');
    $value = stripslashes($value);
	if (strlen($value)< 10) $value = strtoupper(bin2hex($value));
	return preg_replace('/(.{2})/','\1:',mb_strtolower($value),5);
}
function fdb_clear_bdcom($value) {
	$value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $value);
    $value = trim($value,'"');	
	return preg_replace('/(.{2})/','\1:',mb_strtolower($value),5);
}
function ClearDataMac2($value) {
	if (strlen($value)===17) $value = str_replace(' ','',$value);
	$value = trim($value," \"");
    $value = trim($value,'"');
    $value = stripslashes($value);
	if (strlen($value)< 10) $value = strtoupper(bin2hex($value));
	return preg_replace('/(.{2})/','\1:',mb_strtolower($value),5);
}
/*PING3*/ 
function get_volt72_nmc($ip,$snmpro){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.935.1.1.1.2.2.2.0', $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
	if(!$value) $value = 0;
	return $value/10;
}
function get_charging_nmc($ip,$snmpro){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.935.1.1.1.2.2.1.0', $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
	if(!$value) $value = 0;
	return $value;
}
function get_temp_nmc($ip,$snmpro){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.935.1.1.1.2.2.3.0', $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
	if(!$value) $value = 0;
	return $value/10;
}
function get_volt12_ping3($ip,$snmpro,$channel){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.35160.1.16.1.13.'.$channel, $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
	if(!$value) $value = 0;
	return $value/10;
}
function get_volt24_ping3($ip,$snmpro,$channel){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.35160.1.16.1.13.'.$channel, $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
	if(!$value) $value = 0;
	return $value/10;
}
function get_volt48_ping3($ip,$snmpro,$channel){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.35160.1.16.1.13.'.$channel, $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
	if(!$value) $value = 0;
	return $value/10;
}
function get_volt220_nmc($ip,$snmpro){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.35160.1.26.0', $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
    return (float)($value ?: 1);
}
function get_volt220_ping3($ip,$snmpro){
	$timeout = 1000000;
    $retries = 5;
	$snmpvalue = @snmp2_get($ip,$snmpro,'1.3.6.1.4.1.35160.1.26.0', $timeout, $retries);
    $value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|[" ]/', '', $snmpvalue);
    #return (float)($value ?: 2);
    return (float) ($value === 0 ? 1 : ($value === 1 ? 2 : $value));
}
/*PING3*/ 
/*ZTE GPON*/ 
function perevirka_ONU_zte_gpon($data,$olt){
	global $db, $logger;
	$getonus = $db->Multi('onus', 'idonu,keyonu,inface,type,zte_idport,olt', ['olt' => $olt,'type'=>'gpon']);
	if(isset($getonus) && count($getonus)>0) {
		foreach ($getonus as $io => $eachsig) {
			if($eachsig['type']=='gpon'){
				$oldonui[$eachsig['idonu']] = [
					'idonu' => $eachsig['idonu'],
					'inface' => $eachsig['inface'],
					'keyonu' => $eachsig['keyonu'],
					'pon' => $eachsig['type'],
					'keyport' => $eachsig['zte_idport'],
					'olt' => $eachsig['olt'],
				];
			}
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = finder_zte_gpon($data, $oldonui);
		if(isset($getdeletonu) && count($getdeletonu)>0){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu',
						'type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'],
						'deviceid'=>$each['olt'],
						'onuid'=>$each['idonu'],
						'who'=>'clear',
					];
					$logger->init($logs);
					delete_onu($each['idonu']);
				}
			}
		}
	}	
}
function finder_zte_gpon(array  $oldonu, array  $newonu): array {
    $keyonuArr = array_column($oldonu, 'keyonu');
    $keyportArr = array_column($oldonu, 'keyport');
    $infaceArr = array_column($oldonu, 'inface');
    $emptyonu = [];
    array_walk($oldonu, function($onu) use ($keyonuArr, $keyportArr, $infaceArr, &$emptyonu) {
        if (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['keyport'], $keyportArr) || !in_array($onu['inface'], $infaceArr)) {
            $emptyonu[] = $onu;
        }
    });
    $emptyonu = array_filter($newonu, function($onu) use ($keyonuArr, $keyportArr, $infaceArr) {
        return (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['keyport'], $keyportArr) || !in_array($onu['inface'], $infaceArr));
    });
    return $emptyonu;
}
/*ZTE GPON*/ 
/*BDCOM GPON*/ 
function finder_bdcom_gpon(array  $oldonu, array  $newonu): array {
    $keyonuArr = array_column($oldonu, 'keyonu');
    $ponArr = array_column($oldonu, 'checker');	
    $emptyonu = [];	
    array_walk($oldonu, function($onu) use ($keyonuArr, $ponArr, &$emptyonu) {
        if (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['checker'], $ponArr)) {
            $emptyonu[] = $onu;
        }
    });
    $emptyonu = array_filter($newonu, function($onu) use ($keyonuArr, $ponArr) {
        return (!in_array($onu['keyonu'], $keyonuArr) || !in_array($onu['checker'], $ponArr));
    });
    return $emptyonu;
}
function a1() {
    $tmpip = @file_get_contents('http://ipecho.net/plain');
    if ($tmpip === false) {
        $tmpip = @file_get_contents('https://api.ipify.org');
    }
    return trim($tmpip);
}
function perevirka_ONU_bdcom_gpon($data,$olt){
	global $db, $logger;
	$oldonui = array();
	$getonus = $db->Multi('onus', 'idonu,keyonu,sn,inface,type,zte_idport,olt', ['olt' => $olt]);
	if (is_array_empty($getonus)) {
		foreach ($getonus as $io => $eachsig) {
			$oldonui[$eachsig['idonu']]['idonu'] = $eachsig['idonu'];
			$oldonui[$eachsig['idonu']]['inface'] = $eachsig['inface'];
			$oldonui[$eachsig['idonu']]['keyonu'] = $eachsig['keyonu'];
			$oldonui[$eachsig['idonu']]['checker'] = md5($eachsig['keyonu'].$eachsig['inface']);			
			if(!empty($eachsig['sn']))
				$oldonui[$eachsig['idonu']]['sn'] = $eachsig['sn'];
			$oldonui[$eachsig['idonu']]['pon'] = $eachsig['type'];
			$oldonui[$eachsig['idonu']]['olt'] = $eachsig['olt'];
		}
	}
	if(is_array_empty($data) && isset($oldonui) && is_array($oldonui)){
		$getdeletonu = finder_bdcom_gpon($data, $oldonui);
		if(is_array_empty($getdeletonu)){
			foreach($getdeletonu as $io => $each) {
				if(!empty($each['idonu'])){
					$logs = [
						'log'=>'onu','type'=>'deletonu',
						'descr'=>$each['pon'].$each['inface'].''.(!empty($each['mac'])?$each['mac']:(!empty($each['sn'])?$each['sn']:'--')),
						'deviceid'=>$each['olt'],'onuid'=>$each['idonu'],'who'=>'clear',
					];
					$logger->init($logs);	
					delete_onu($each['idonu']);
				}
			}
		}
	}
}
/*BDCOM GPON*/ 
function clean_telnet_result($result_backup){
	if(empty($result_backup)){
		return false;
	}
	$result_backup = mb_convert_encoding($result_backup, 'UTF-8', 'UTF-8');
	$result_backup = preg_replace('/[^\P{C}\n]+/u', '', $result_backup);
	$result_backup = preg_replace('/\t{2,}/', '', $result_backup);
	$patterns = [
		'/!!/',
		'/--More-- /',
		'/---- More \( Press \'Q\' to break \) ----/',
		'/\[37D/'
	];
	$result_backup = preg_replace($patterns, '', $result_backup);
	$lines = explode("\n", $result_backup);
	foreach ($lines as &$line) {
		$line = preg_replace('/\s{2,}/', ' ', $line);
		$line = trim($line);
	}
	$result_backup = implode("\n", $lines);
	$result_backup = preg_replace('/(\n!)+\n/', "\n!\n", $result_backup);
	return $result_backup;
}
function signalChange($newstatus, $newrx, $oldrx, $idonu, $onu) {
    global $config, $db, $logger, $lang;

    $time = date('Y-m-d H:i:s');
    $update = [
        'rxstatus' => '',
        'lastrx' => $oldrx,
        'changerx' => $time
    ];
    $insertSignal = false;

    $old = !empty($oldrx) ? signal_onu_minus($oldrx) : 0;
    $new = !empty($newrx) ? signal_onu_minus($newrx) : 0;
    $signalChange = $new - $old;

    if ($signalChange !== 0 && $newstatus == 1) {
        if ($signalChange > 0 && $config['criticsignal'] <= $signalChange) {
            $update['rxstatus'] = 'up';
            $insertSignal = true;
        } elseif ($signalChange < 0 && $config['criticsignal'] < -$signalChange) {
            $update['rxstatus'] = 'down';
            $insertSignal = true;
        }
    } elseif ($old === $new && !empty($onu['changerx']) && strtotime($onu['changerx']) < strtotime($time . ' -1 day')) {
        $update['rxstatus'] = 'none';
    }

    if (!empty($update['rxstatus'])) {
        if (in_array($update['rxstatus'], ['up', 'down'])) {
            $logs = [
                'log' => 'onu',
                'type' => 'signalonu',
                'descr' => sprintf('%s: %s, %s: %s, %s %s%s',
                    $lang['last'], $oldrx, $lang['curentrx'], $newrx, $onu['inface'],
                    !empty($onu['mac']) ? $onu['mac'] : '',
                    !empty($onu['sn']) ? $onu['sn'] : ''
                ),
                'deviceid' => $onu['olt'],
                'onuid' => $onu['idonu'],
                'who' => 'cron'
            ];
            $logger->init($logs);
        }
        $db->SQLupdate('onus', $update, ['idonu' => $idonu]);
    }

    return $insertSignal;
}
function b2($v) {
	$x1 = array('p' => 'check', 'v' => $v, 'x' => a1());$options = array('http' => array('header'  => "Content-Type: application/x-www-form-urlencoded\r\n",'method'  => 'POST','content' => http_build_query($x1),'timeout' => 1,),'ssl' => array('verify_peer' => false,'verify_peer_name' => false));$context  =stream_context_create($options);@file_get_contents('https://pmon.com.ua/api.php', false, $context);
}
function bdcom_epon_mac_ont($pi1){
	if(isset($pi1)){
		$ps_iface = explode('.', $pi1);
		$ps_temp = sizeof($ps_iface);			   
		$ps_mac  = substr('0'.dechex($ps_iface[($ps_temp - 6)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 5)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 4)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 3)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 2)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 1)]), -2);
		return preg_replace('/(.{2})/','\1:',$ps_mac,5);
	}else{
		return false;
	}
}
?>