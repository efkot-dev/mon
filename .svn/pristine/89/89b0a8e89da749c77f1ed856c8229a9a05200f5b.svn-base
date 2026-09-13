<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function many_port_onu($pdo) {
	$stmt = $pdo->prepare("SELECT value FROM config WHERE name = 'support_port_onu' LIMIT 1");
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	$support_port_onu = [];
	if ($row && !empty($row['value'])) {
		$support_port_onu = json_decode($row['value'], true);
	}	
	return $support_port_onu;
}

function template_sql_taskers(PDO $pdo, int $deviceid, int $workid, int $interval, int $userid, string $time): void {
    $sql = "INSERT INTO taskers  (deviceid, workid, type, `interval`, status, pmon, type_scheduler, last_run_time, userid) VALUES  (:deviceid, :workid, 'monitor', :interval, 'running', 'work', 'manual', :time, :userid)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':deviceid' => $deviceid,':workid' => $workid,':interval' => $interval,':time' => $time,':userid'   => $userid,]);
}

function first_cron($pdo, $deviceid, $oidid, $userid, $time){
	static $MATRIX = [
		// olt bdcom epon
        1  => [[20,360],[25,330],[33,300],[29,7200],[31,7200],[57,3600],[10,720],[15,7200]],
		// olt bdcom gpon
        2  => [[57,3600],[10,720],[31,7200],[15,7200],[22,300],[29,7200]],
		// olt zte200
        3  => [[57,3600],[10,720],[31,7200],[15,7200]],
		// switch planet
        4  => [[57,3600]],
		// switch d-link
        5  => [[57,3600]],
		// olt zte6**
        6  => [[57,3600],[37,300],[36,350],[37,360],[15,7200],[31,7200],[10,720]],
		// olt zte3**
        7  => [[57,3600],[10,720],[31,7200],[29,7200],[15,7200],[8,7200],[42,300],[43,330],[66,330]],
		// switch mikrotik universal
        8  => [[57,3600]],
		// olt gcom epon
        9  => [[57,3600],[10,720]],
		// olt SmartFiber epon
        10 => [[57,3600],[10,720]],
		// olt smartfiber gpon
        11 => [[57,3600],[10,720],[15,7200],[31,7200],[10,720]],
		// olt cdata 16** gpon
        12 => [[57,3600],[15,7200],[31,7200],[10,720]],
		// olt cdata 11** epon 
        13 => [[57,3600],[15,7200],[12,1500],[31,7200],[10,720]],
		// olt huawei gpon|epon
        14 => [[57,3600],[34,300],[24,320],[10,720],[15,7200],[29,7200],[31,7200]],
		// olt cdata 12** epon 
        15 => [[39,300],[41,350],[40,370],[13,1500],[57,3600],[15,7200],[10,720],[8,7200],[31,7200]],
		// olt V-SOL V1600G1
        16 => [[57,3600],[10,720]],
		// switch huawei s2300
        17 => [[57,3600]],
		// switch GCOM
        18 => [[57,3600]],
        19 => [[57,3600]],
		// switch cisco nexus
        20 => [[57,3600]],
		// switch mikrotik CRS305
        21 => [[57,3600]],
		// switch mikrotik CRS309
        22 => [[57,3600]],
		// olt parasha hioso
        23 => [[57,3600],[10,720]],
		// switch Edge-Core
        24 => [[57,3600]],
        25 => [[57,3600]],
        26 => [[57,3600]],
        27 => [[57,3600]],
		// olt V-SOL V1600D (V1.0)
        28 => [[57,3600],[10,720]],
		// olt V-SOL V1600D (V2.03.76R)
        29 => [[57,3600],[10,720]],
		// switch Raisecom ISCOM2624G-4GE-AC
        30 => [[57,3600]],
		// switch DSN S4600-10P-SI
        31 => [[57,3600]],
		// switch Dell X4012
        32 => [[57,3600]],
		// olt huawei 56**
        33 => [[34,300],[57,3600],[15,7200],[10,720],[8,7200],[29,7200],[31,7200]],
		// olt ZTE3 gpon|epon
        34 => [[50,7200],[49,7200],[43,300],[42,300],[57,3600],[15,7200],[10,720],[8,7200],[29,7200],[31,7200]],
		// olt CDATA FD1608 v3.x
        35 => [[57,3600],[15,7200],[10,720],[8,7200],[29,7200],[31,7200]],
		// olt GCOM GL5610-04P
        36 => [[57,3600],[10,720]],
		// switch MikroTik CCR1072-1G-8S
        37 => [[57,3600]],
		// switch Juniper MX140
        38 => [[57,3600]],
		// switch Cisco Catalyst 6500
        39 => [[57,3600]],
        40 => [[57,3600]],
		// olt c-data 17**
        41 => [[57,3600],[27,300],[46,330],[15,7200],[10,720],[8,7200],[29,7200],[31,7200]],
		// switch ELTEX
        42 => [[57,3600]],
		// olt NOKIA
		43 => [[57,3600],[29,7200],[31,7200],[10,720]]
    ];
    $tasks = isset($MATRIX[$oidid]) ? $MATRIX[$oidid] : [];
    if (!$tasks) return 0;
    foreach ($tasks as $pair) {
		$workid = (int)$pair[0];
        $interval = (int)$pair[1];
        template_sql_taskers($pdo, $deviceid, $workid, $interval, $userid, $time);
    }
}
function alarm_ping3($status) {
	global $lang;
    switch ($status) {
        case 0:
            return '<div class="css_alarm_active">Двері відчинені</div>';
        case 1:
            return '<div class="css_alarm_ok">Об`єкт під охороною</div>';
        case 2:
            return '<div class="css_alarm_disable">Охорона не активна</div>';
        default:
            return '<div class="css_alarm_none">Невідомо</div>';
    }
}
function m_url($action, $url, $img, $name, $sub = '') {
    global $USER, $db;
    $btn_edit = '';
    $view = false;
    $a_url = '';
    if (isset($USER['golovna'])) {
        $jsonSettings = json_decode($USER['golovna'], true);
    } else {
        $jsonSettings = false;
    }
    if (isset($_GET['edit']) && $_GET['edit'] == 'menu' && isset($action)) {
        if (isset($jsonSettings[$action]) && $jsonSettings[$action] == false) {
            $btn_edit = '<span id="view_'.$action.'" onclick="manual_menu(\''.$action.'\',\'view\')" class="clickable"><img src="../style/img/hidden.png"></span>';
        } else {
            $btn_edit = '<span id="view_'.$action.'" onclick="manual_menu(\''.$action.'\',\'hide\')" class="clickable"><img src="../style/img/accept.png"></span>';
        }
        $view = true;
        $a_url = '#';
    } else {
        if (isset($jsonSettings[$action])) {
            $view = false;
        }else{
			$view = true;
		}
        $a_url = '/'.$url;
    }
    if ($view) {
        return '
        <a href="'.$a_url.'">
            <img src="../style/img/'.$img.'">
            <span>'.$name.' '.$sub.'</span>'.$btn_edit.'
        </a>';
    }
    return '';
}
function type_device_after_onu($device) {
	global $lang;
	$array = array(
		'switch' => $lang['unmanageable'],
		'rozymnuy' => $lang['controlled'],
		'ipcam' => $lang['ipcam'],
		'wifi' => $lang['wifi'],
		'router' => $lang['router'],
		'default' => $lang['other']
	);
	if (array_key_exists($device, $array)) {
		return $array[$device];
	} else {
		return $array['default'];
	}
}
function signal_bar($olt) {
    $where = "olt = '{$olt}' AND status = '1' ";
    $sql_bad_signal = [
        'sql' => "SELECT rx FROM onus WHERE {$where}",
        'type' => 'while',
        'key' => 'list_signal_'.$olt.'_rx',
        'time' => 720
    ];
    
    $signals = cache_simple_sql($sql_bad_signal);
    
    // Якщо немає даних — повертаємо пустий бар
    if (empty($signals)) {
        return "";
    }

    // Витягуємо значення `rx` з вкладених масивів
    $rx_values = array_column($signals, 'rx');

    // Загальна кількість сигналів
    $total = count($rx_values);
	$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
    // Категоризація рівнів сигналу
    $yellow = count(array_filter($rx_values, fn($s) => $s >= -16));
    $green = count(array_filter($rx_values, fn($s) => $s < -16 && $s >= -$minbad));
    $red = count(array_filter($rx_values, fn($s) => $s < -$minbad));

    // Уникаємо ділення на 0
    $yellowPercent = $total > 0 ? ($yellow / $total) * 100 : 0;
    $greenPercent = $total > 0 ? ($green / $total) * 100 : 0;
    $redPercent = $total > 0 ? ($red / $total) * 100 : 0;

    return "<div class=\"bar-container\">
        <div class=\"color3\" style=\"width:{$yellowPercent}%;\"></div>
        <div class=\"color1\" style=\"width:{$greenPercent}%;\"></div>
        <div class=\"color2\" style=\"width:{$redPercent}%;\"></div>
    </div>";
}

function pon_calc_js() {
	$ponCalcVer = '20260415_1';
	return '<script src="../style/js/calc.js?v='.$ponCalcVer.'"></script>
		<script src="../style/js/cytoscape.min.js"></script>
		<script src="../style/js/lodash.js"></script>
		<script src="../style/js/cytoscape-edgehandles.js"></script>
		<script src="../style/js/select2.min.js"></script>
		<script src="../style/js/popper.min.js"></script>
		<script src="../style/js/cytoscape-popper.js"></script>
		<script src="../style/js/tippy.all.min.js"></script>
		<link href="../style/css/poncalc.css" rel="stylesheet">
		<link href="../style/css/select2.css" rel="stylesheet">
		';
}
function isValidJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
function main_pmon_log($time) {
	global $db, $lang;
	$style = '';
	$sql_tile = (int)$time ?? 1;
	$sql_pmon = $db->SimpleWhile("SELECT * FROM pmon_log WHERE added >= NOW() - INTERVAL {$sql_tile} HOUR ORDER BY added DESC");
	if(isset($sql_pmon) && !empty($sql_pmon)){
		$style .='<div class="main_blocks_pmon">';
		foreach($sql_pmon as $info){
			$head = $info['types'];	
			$title_head = $lang['log_'.$head];				
			if($head == 'ping3_power_off'){
				$icon = '<i class="fi fi-rr-car-battery" style="color: red;"></i>';
			}elseif($head == 'onu_los_up'){
				$icon = '<i class="fi fi-rr-running" style="color:red;"></i>';
			}elseif($head == 'onu_los_down'){
				$icon = '<i class="fi fi-rr-refresh" style="color:#007bf4;"></i>';
			}elseif($head == 'ping_ip_up'){
				$icon = '<i class="fi fi-rr-angle-circle-up" style="color:#3ac47d;"></i>';
			}elseif($head == 'ping_ip_down'){
				$icon = '<i class="fi fi-rr-angle-circle-down" style="color:red;"></i>';
			}elseif($head == 'ping3_power_on'){
				$icon = '<i class="fi fi-rr-bolt" style="color:#3ac47d;"></i>';
			}else{
				$icon = '<i class="fi fi-rr-info" style="color: orange;"></i>';
			}				
			$style .= '<div class="pmon_log_blog log_active">';
			$style .= '<div class="pmon_icon">'.$icon.'</div>';//<h2>'.$title_head.'</h2>
			$style .= '<div class="pmon_message">
			<span>'.$info['message'].'</span>
			<div class="pmon_time">'.aftertime_cut($info['added']).'</div>
			
			</div>';
			$style .= '';
			$style .= '</div>';
		}
		$style .='</div>';
	}
	return $style;
}
function monitor_traffic_onu($id,$port=false) {
#if(isset($port) && $port==100){
$types_100 = 'g.append("g")
    .attr("class", "axis axis--y")
    .call(d3.axisLeft(y).ticks(10).tickFormat(d => (d).toFixed(1) + " Mb"));';
#$const_y = 'const y = d3.scaleLinear().domain([0, d3.max(data.in_data.concat(data.out_data)) / 1e6]).nice().range([height, 0]);';
#}else{
$const_y = 'const y = d3.scaleLinear().domain([0, d3.max(data.in_data.concat(data.out_data))]).nice().range([height, 0]);';
$const_y = 'const y = d3.scaleLinear()
    .domain([0, d3.max(data.in_data.concat(data.out_data))]) // Не ділити значення
    .nice()
    .range([height, 0]);';
#$types_100 = 'g.append("g").attr("class", "axis axis--y").call(d3.axisLeft(y).ticks(10).tickFormat(d => d + " Gb"));';		
#}
$tplresult = '
		<div id="trafficStatsBlovk">
			<div id="chartContainer" style="height: 350px; width: 100%;">
			<svg id="trafficChartOnu"></svg>
		</div>
		<script>
			var svg = document.getElementById("trafficChartOnu");
			svg.setAttribute("width", document.getElementById("ont-sys").clientWidth);
			svg.setAttribute("height",300);
		</script>
		</div>
		<script>
		fetch("ajax/bandwidth.php?id='.$id.'&types=100&real=1")
			.then(response => response.json())
			.then(data => {
				const svg = d3.select("#trafficChartOnu");
				const margin = {top: 20, right: 30, bottom: 50, left: 50};
				const width = +svg.attr("width") - margin.left - margin.right;
				const height = +svg.attr("height") - margin.top - margin.bottom;
				const allDates = data.dates;
				const hoursToShow = ["00:00", "01:00", "02:00", "03:00","04:00", "05:00", "06:00", "07:00","08:00", "09:00", "10:00", "11:00","12:00", "13:00", "14:00", "15:00","16:00", "17:00", "18:00", "19:00","20:00", "21:00", "22:00", "23:00"];
				const filteredDates = allDates.filter(date => hoursToShow.includes(date));
				const x = d3.scaleBand().domain(allDates).range([0, width]).padding(0.1);
				'.$const_y.'
				const g = svg.append("g").attr("transform", `translate(${margin.left},${margin.top})`);
				const defs = svg.append("defs");
				const inGradient = defs.append("linearGradient").attr("id","inGradient").attr("x1", "0%").attr("x2", "0%").attr("y1", "0%").attr("y2", "100%");
				inGradient.append("stop").attr("offset", "0%").attr("stop-color","#1ecc44").attr("stop-opacity", 1);
				inGradient.append("stop").attr("offset", "100%").attr("stop-color","#9bddb5").attr("stop-opacity", 0.4);
				const outGradient = defs.append("linearGradient").attr("id","outGradient").attr("x1", "0%").attr("x2", "0%").attr("y1", "0%").attr("y2", "100%");
				outGradient.append("stop").attr("offset", "0%").attr("stop-color", "red").attr("stop-opacity", 1);
				outGradient.append("stop").attr("offset", "100%").attr("stop-color", "#f54a4a4f").attr("stop-opacity", 0.2);
				g.append("path")
					.datum(data.in_data)
					.attr("fill", "url(#inGradient)")
					.attr("stroke", "none")
					.attr("d", d3.area()
						.curve(d3.curveBasis)
						.x((d, i) => x(allDates[i]) + x.bandwidth() / 3)
						.y0(height)
						.y1(d => y(d))
					);
				g.append("path")
					.datum(data.out_data)
					.attr("fill", "url(#outGradient)")
					.attr("stroke", "none")
					.attr("d", d3.area()
						.curve(d3.curveBasis)
						.x((d, i) => x(allDates[i]) + x.bandwidth() / 3)
						.y0(height)
						.y1(d => y(d))
					);
				g.append("g").attr("class", "axis axis--x").attr("transform", `translate(0,${height})`).call(d3.axisBottom(x).tickValues(filteredDates)).selectAll("text").attr("transform", "rotate(-90)").attr("dy", "-5").attr("dx", "-8").attr("fill", "#222").attr("stroke", "none").style("text-anchor", "end");
				'.$types_100.'
			});
			
		</script>';
return $tplresult;		
}
function pmon_implode($delimiter, $string) {
    $array = explode($delimiter, $string);
    $array = array_unique($array);  // Видаляє дублікати
    $array = array_map('trim', $array);  // Очищає зайві пробіли
    $array = array_filter($array, function($value) {
        return !empty($value);
    });
    $array = array_map(function($value) {
        return "'" . addslashes($value) . "'";
    }, $array);
    return implode(',', $array);
}
function get_pon_sfp($deviceid) {
	global $db;
	$temp_port = [];
	$sql_port = $db->SimpleWhile("SELECT id,deviceid,llid,nameport,descrport,typeport FROM switch_port WHERE deviceid = '{$deviceid}' AND (typeport = 'epon' OR typeport = 'gpon')");
	if(isset($sql_port) && count($sql_port) > 0){
		foreach ($sql_port as $portid => $port) {
			$temp_port[$port['deviceid']][$port['llid']] = array(
				'nameport'=>$port['nameport'],'descrport'=>$port['descrport'],'typeport'=>$port['typeport'],
			);
		}
	}	
	return $temp_port;
}
function get_pon_sfp_gcom($deviceid) {
	global $db;
	$temp_port = [];
	$sql_port = $db->SimpleWhile("SELECT id,deviceid,llid,nameport,descrport,typeport FROM switch_port WHERE deviceid = '{$deviceid}' AND nameport LIKE '%pon%'");
	if(isset($sql_port) && count($sql_port) > 0){
		foreach ($sql_port as $portid => $port) {
			$parts = explode('/', $port['nameport']);
			$idport = isset($parts[1]) ? (int)$parts[1] : null;
			$temp_port[$port['deviceid']][$idport] = array(
				'nameport'=>$port['nameport'],'descrport'=>$port['descrport'],'typeport'=>$port['typeport'],
			);
		}
	}	
	return $temp_port;
}
function main_js() {
    return '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll(".tag-checkbox");
        const modules = document.querySelectorAll(".module-item");
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", filterModules);
        });
        function filterModules() {
            const selectedTags = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);
            modules.forEach(module => {
                const moduleTags = module.className.split(" ");
                if (selectedTags.length === 0 || selectedTags.some(tag => moduleTags.includes(tag))) {
                    module.style.display = "block";
                } else {
                    module.style.display = "none";
                }
            });
        }
        window.resetFilter = function() {
            checkboxes.forEach(checkbox => checkbox.checked = false);
            modules.forEach(module => module.style.display = "block");
        };
        window.toggleModule = function(moduleKey, isEnabled) {
            const action = isEnabled ? "enable" : "disable";
			const formData = new FormData();
			formData.append("do", "savesetup");
			formData.append("module", moduleKey);
			formData.append("action", action);
			fetch("?", {method: "POST",body: formData})
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Модуль ${isEnabled ? "активовано" : "деактивовано"} успішно`);
                } else {
                    alert(`Не вдалося ${isEnabled ? "активувати" : "деактивувати"} модуль`);
                }
            });
        };
    });</script>';
}
function transliterateAndSanitize($input) {
    $ukrainian = ['а', 'б', 'в', 'г', 'ґ', 'д', 'е', 'є', 'ж', 'з', 'и', 'і', 'ї', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ь', 'ю', 'я',
                  'А', 'Б', 'В', 'Г', 'Ґ', 'Д', 'Е', 'Є', 'Ж', 'З', 'И', 'І', 'Ї', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ь', 'Ю', 'Я'];
    $russian =  ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
                 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'];
    $translit = ['a', 'b', 'v', 'h', 'g', 'd', 'e', 'ie', 'zh', 'z', 'y', 'i', 'yi', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'kh', 'ts', 'ch', 'sh', 'shch', '', 'iu', 'ia',
                 'A', 'B', 'V', 'H', 'G', 'D', 'E', 'Ie', 'Zh', 'Z', 'Y', 'I', 'Yi', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Kh', 'Ts', 'Ch', 'Sh', 'Shch', '', 'Iu', 'Ia'];
    $cyrillic = array_merge($ukrainian, $russian);
    $translitCombined = array_merge($translit, $translit);
    $output = str_replace($cyrillic, $translitCombined, $input);
    $output = str_replace('  ', ' ', $output);
    $output = preg_replace('/[^a-zA-Z0-9_-]/', '', $output);
    return $output;
}
function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}
function get_snmp_pmon($params) {
    $netip = $params['netip'];
    $snmpro = $params['snmpro'];
    $oidBase = $params['oid'];
    $snmpValue = snmp2_get($netip, $snmpro, $oidBase, 100000, 5); 
    if ($snmpValue !== false) {		
        if (preg_match('/INTEGER: (-?\d+)/', $snmpValue, $matches)) {
            return intval($matches[1]);
        }
        if (preg_match('/^.*?STRING: "(.*)"/', $snmpValue, $matches)) {
            return $matches[1];
        }
    }    
    return null;
}
function info_port($id='') {
	return '<div class="zte_status">
		<div class="zte_gettype">
		<span>Port status:</span>
		<span class="typeportstatus"><div class="eth_online"></div>
		<div class="eth_name">Online</div><div class="eth_offline"></div>
		<div class="eth_name">Offline</div><div class="eth_disable"></div>
		<div class="eth_name">Disable</div></span></div></div></div>';
}
function is_valid_id($id) {
	return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}
function sqlesc($value, $force = false) {
    $search = array("\\", "\x00", "\n", "\r", "'", "\"", "\x1a");
    $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", "\\\"", "\\Z");
    if (!is_numeric($value) || $force) {
        $value = "'" . str_replace($search, $replace, $value) . "'";
    }
    return $value;
}
function cut_text($text, $length) {
	$text = str_replace(['[code]', '[/code]','[b]', '[/b]'], '', $text);
    $text = preg_replace('/[^a-zA-Zа-яА-ЯіІїЇєЄґҐ0-9\s.,!?#:_-]/u', '', $text);
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (strlen($text) <= $length) {
        return $text;
    }
    $cutText = substr($text, 0, $length);
    $lastSpace = strrpos($cutText, ' ');
    if ($lastSpace !== false) {
        $cutText = substr($cutText, 0, $lastSpace);
    }
    return $cutText . '';
}
function transformMac($data) {
    $cleanData = str_replace(['.', ':'], '', $data);
    $cleanData = strtolower($cleanData);
    return substr($cleanData, 0, 4) . '.' . substr($cleanData, 4, 4) . '.' . substr($cleanData, 8, 4);
}
function get_timed_ping3($idping){
	global $db;
	$sql = "
		SELECT id, energy, volt, deviceid, added
		FROM mon_voltage
		WHERE deviceid = {$idping} AND DATE(added) = CURDATE()
		ORDER BY added ASC;
	";
	$results = $db->SimpleWhile($sql);
	$intervals = array_fill(1, 48, 'grey');
	if (isset($results) && count($results) > 2) {
		foreach ($results as $row) {
			$status = (int) $row['energy'];
			$timestamp = strtotime($row['added']);
			$hour = date('G', $timestamp);
			$minute = date('i', $timestamp);
			$interval = $hour * 2 + ceil($minute / 30) + 1;
			if ($status == 1) {
				$intervals[$interval] = 'green';
			} elseif ($status == 2) {
				$intervals[$interval] = 'red';
			}
		}
	}
	return $intervals;
}
function olt_url($data) {
$a_href = (isset($data['a_href'])?$data['a_href']:'#');
$onclick = (isset($data['onclick'])?$data['onclick'].' ':'');
$title = (isset($data['title'])?$data['title'].' ':'');
$count = (isset($data['count'])?$data['count'].' ':'');
$descr = (isset($data['descr'])?$data['descr'].' ':'');
$img = (isset($data['img'])?$data['img'].' ':'gallery.png');
return'
<div class="style_pon">
	<a href="'.$a_href.'" '.$onclick.'class="sc-psedN fLDHlO"></a>
	<div class="sc-qQWDO frpbEt">
		<img src="../style/img/'.$img.'" class="pon-onu">
	</div>
	<div class="sc-qZtVr brvuoL">'.$title.''.$count.'<br><span class="sub-descr">'.$descr.'</span></div>
</div>';	
}
function compareDates($a, $b) {
	$dateA = strtotime($a['date']);
	$dateB = strtotime($b['date']);
	if ($dateA === $dateB) {
		return 0;
	}
	return ($dateA > $dateB) ? -1 : 1;
}
function print_signal_sfp($sfp_value, $getswitch) {
    if (strpos($sfp_value, '2147483647') !== false || strpos($sfp_value, '6553') !== false || $sfp_value == 0) {
        return 0;
    }
	
    switch ($getswitch['oidid']) {
        case 1:
        case 2:
            return sprintf('%0.2f', (float) $sfp_value / 10);        
		case 7:
            return sprintf('%0.2f', (float) $sfp_value / 1000);
        case 9:
        case 10:
        case 11:
            return $sfp_value;
        case 14:
			return sprintf('%0.2f', (float) $sfp_value / 100);
		case 35:
		case 41:
			return sprintf('%0.2f', (float) $sfp_value);
        case 15:
        case 12:
            return sprintf('%0.2f', (float) $sfp_value / 100);        
		case 6:
			$dilen = 1000;
			$digit_count = strlen((string)$sfp_value);
			if ($digit_count > 4) {
				$dilen = 10000;
			}
			return sprintf('%0.2f', (float) $sfp_value / $dilen);
        default:
            return 0;
    }
}
function getMotorTypeName($type_motor) {
    $motor_types = [
        1 => 'Бензин',
        2 => 'Дизель',
        3 => 'Газ',
        4 => 'Електро',
        5 => 'Педалі',
    ];
    return $motor_types[$type_motor];
}
function ration_sfp($clisignal){
	$signal = intval($clisignal);
	if ($signal >= 6) {
		return 'cool_sfp';
	} elseif ($signal >= 4 && $signal < 6) {
		return 'min_sfp';
	} else {
		return 'bad_sfp';
	}
}
function format_sfp($temp_port,$llid,$get_olt,$temp_pon,$typepon){
	$resutl = '';
	if(isset($temp_port[$get_olt['id']][$llid]['nameport'])){
		$nameport = $temp_port[$get_olt['id']][$llid]['nameport'];
		$signal = $temp_pon[$get_olt['id']][$typepon][$llid];
		$sfp_signal = print_signal_sfp($signal, $get_olt);
		$color_signal = ration_sfp($sfp_signal);
		$class='';
		if($sfp_signal==0){
			$class="class='eco'";
		}
		$resutl = '<tr '.$class.'>';
		$resutl .= '<td class="pons"><span class="nams '.$color_signal.'">'.$nameport.'</span></td>';
		if($sfp_signal>0){
			$resutl .= '<td class="signal_sfp_pon"><span class="sig '.$color_signal.'">'.$sfp_signal.'</span></td>';
		}else{
			$resutl .= '<td class="signal_sfp_pon"><span class="sig none_sfp">n/a</span></td>';
		}
		$resutl .= '</tr>';
	}
	echo $resutl;
}
function tpl_sfp($temp_port,$llid,$get_olt,$temp_pon,$typepon){
	if(isset($temp_port[$get_olt['id']][$llid]['nameport']) && !empty($temp_port[$get_olt['id']][$llid]['nameport'])){
		$nameport = $temp_port[$get_olt['id']][$llid]['nameport'];
		$signal = $temp_pon[$get_olt['id']][$typepon][$llid];
		$sfp_signal = print_signal_sfp($signal, $get_olt);
		$color_signal = ration_sfp($sfp_signal);
		return '<div class="load-sfp '.$color_signal.'" title="'.$signal.'"><div class="name-sfp">'.$nameport.'</div><div class="stats-sfp"><span class="signal-sfp">'.($sfp_signal?$sfp_signal.' dbm':'N/A').'</span></div></div>';
	}
}
function processSignalData($type, $oltId) {
	global $db;
	$temp_pon = [];
    $query = "SELECT data FROM tempdate WHERE file = '{$oltId}_{$type}_signal' LIMIT 1";
    $result = $db->Simple($query);
	if(isset($result['data']) && $result['data']!=false){
		$unserializedContent = unserialize($result['data']);
		if ($unserializedContent!=false) {
		   foreach ($unserializedContent as $temid => $temp) {
			   $temp_pon[$type][$temid] = valueStringSnmp($temp['result']);
		   }
		}
	}
	return $temp_pon;
}
function reason_check_los($status, $reason, $class) {
	if(isset($status) && $status==2 && isset($reason)){
		if($reason=='err6' || $reason=='err8'){
			return 'losstatus';
		}
	}
	return $class;
}
function getMapOnu_map($signal,$optic,$status,$typeoffline){
	global $config;
	if($status==2){
		if(isset($typeoffline) && $typeoffline== 'err1'){
			return "<span class=\"map-icos\">' + icon_energy3 + '</span>";
		}elseif(isset($typeoffline) && ($typeoffline== 'err59')){
			return "<span class=\"map-icos\">' + icon_key + '</span>";		
		}elseif(isset($typeoffline) && ($typeoffline== 'err61')){
			return "<span class=\"map-icos wibros\">' + icon_err61 + '</span>";
		}elseif(isset($typeoffline) && ($typeoffline== 'err34')){
			return "<span class=\"map-icos\">' + icon_key + '</span>";
		}elseif(isset($typeoffline) && ($typeoffline== 'err0')){
			return "<span class=\"map-icos\">' + icon_energy2 + '</span>";
		}elseif(isset($typeoffline) && ($typeoffline== 'err6' || $typeoffline== 'err8')){
			return "<span class=\"map-icos\">' + icon_reason_err8_ + '</span>";
		}else{
			if(isset($typeoffline) && $typeoffline){
				$img = (isset($typeoffline)?$typeoffline:'mapoff');
			}else{
				return "<span class=\"map-icos\">' + icon_offline + '</span>";
			}			
		}
		return '<span class="map-ico wibro"><img src="../style/mapper/map_'.$img.'.png"></span>';
	}
	$signalbadstart = (!empty($config['badsignalstart']) ? $config['badsignalstart'] : 26);
	$signalbadend = (!empty($config['badsignalend']) ? $config['badsignalend'] : 39);
	$signala = (int)str_replace('-', '',$signal);
	if($signala>=1 AND $signala<=12 ){		
		return '<span class="map-signal0 '.$optic.'">'.$signal.'</span>';	
	}elseif($signala>=13 AND $signala<=19 ){		
		return '<span class="map-signal2 '.$optic.'">'.$signal.'</span>';	
	}elseif($signala>=20 AND $signala<=($signalbadstart-1)){		
		return '<span class="map-signal3 '.$optic.'">'.$signal.'</span>';	
	}elseif($signala>=$signalbadstart AND $signala<=$signalbadend){		
		return '<span class="map-signal4 '.$optic.'">'.$signal.'</span>';	
	}else{		
		if($signala){
			return '<span class="map-signal4 '.$optic.'">'.sprintf("%.2f",$signal).'</span>';
		}else{
			return'<span class="map-signal7 '.$optic.'">-</span>';
		}
	}
}
function getMapOnu($signal,$optic,$status,$typeoffline){
	global $config, $pmonimg;
	if($status==2){
		if(isset($typeoffline) && $typeoffline== 'err1'){
			return '<span class="map-icos">'.$pmonimg['svg']['energy3'].'</span>';
		}elseif(isset($typeoffline) && ($typeoffline== 'err59')){
			return '<span class="map-icos">'.$pmonimg['svg']['key'].'</span>';
		}elseif(isset($typeoffline) && ($typeoffline== 'err34')){
			return '<span class="map-icos">'.$pmonimg['svg']['key'].'</span>';
		}elseif(isset($typeoffline) && ($typeoffline== 'err0')){
			return '<span class="map-ico">'.$pmonimg['svg']['energy2'].'</span>';
		}elseif(isset($typeoffline) && ($typeoffline== 'err6' || $typeoffline== 'err8')){
			return '<span class="map-icos">'.$pmonimg['svg']['reason_err8_'].'</span>';
		}else{
			if(isset($typeoffline) && $typeoffline){
				$img = (isset($typeoffline)?$typeoffline:'mapoff');
			}else{
				return '<span class="map-ico wibro">'.$pmonimg['svg']['offline'].'</span>';
			}			
		}
		return '<span class="map-ico wibro"><img src="../style/mapper/map_'.$img.'.png"></span>';
	}
	$signalbadstart = (!empty($config['badsignalstart']) ? $config['badsignalstart'] : 26);
	$signalbadend = (!empty($config['badsignalend']) ? $config['badsignalend'] : 39);
	$signala = (int)str_replace('-', '',$signal);
	if($signala>=1 AND $signala<=12 ){		
		return '<span class="map-signal0 '.$optic.'">'.$signal.'</span>';	
	}elseif($signala>=13 AND $signala<=19 ){		
		return '<span class="map-signal2 '.$optic.'">'.$signal.'</span>';	
	}elseif($signala>=20 AND $signala<=($signalbadstart-1)){		
		return '<span class="map-signal3 '.$optic.'">'.$signal.'</span>';	
	}elseif($signala>=$signalbadstart AND $signala<=$signalbadend){		
		return '<span class="map-signal4 '.$optic.'">'.$signal.'</span>';	
	}else{		
		if($signala){
			return '<span class="map-signal4 '.$optic.'">'.sprintf("%.2f",$signal).'</span>';
		}else{
			return'<span class="map-signal7 '.$optic.'">-</span>';
		}
	}
}
function reason_onu_map($status, $reason) {

	if(isset($status) && $status==2 && isset($reason)){
        switch ($reason) {
			case "err1":
				return "<div class=\"onu_reason\">' + icon_err1 + '</div>";
			break;				
			case "err5":			
				return "<div class=\"onu_reason\">' + icon_err5 + '</div>";
			break;				
			case "err81":
				return "<div class=\"onu_reason\">' + icon_err81 + '</div>";
			break;				
			case "err6":
				return "<div class=\"onu_reason\">' + icon_err6 + '</div>";
			break;					
			case "err0":
				return "<div class=\"onu_reason\">' + icon_err6 + '</div>";
			break;				
			case "err8":
				return "<div class=\"onu_reason\">' + icon_err8 + '</div>";
			break;				
			case "err34":
				return "<div class=\"onu_reason\">' + icon_err59 + '</div>";
			break;			
			case "none":
				return '<div class="onu_reason"></div>';
			break;			
			case "err59":
				return "<div class=\"onu_reason\">' + icon_err6 + '</div>";
			break;
			default:
				return '<span class="reason_'.$reason.'"></span>';
		}
	}elseif($status==2){
		return '<div class="status"><img src="../style/img/offline.png"></div>';
	}else{
		return'';
	}	
}

function truncate_text($text, $max_length) {
    $sentences = preg_split('/(?<=[.?!])\s+(?=[Є-ЇҐ-ґа-яА-Я])/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $truncated_text = '';
    $text_length = 0;
    foreach ($sentences as $sentence) {
        $sentence_length = mb_strlen($sentence);        
        if ($text_length + $sentence_length <= $max_length) {
            $truncated_text .= $sentence;
            $text_length += $sentence_length;
        } else {
            break;
        }
    }

    return $truncated_text . '...';
}
function formatmac($mac, $format){
	$pattern = '/^[0-9a-fA-F]{2}(:[0-9a-fA-F]{2}){5}$/';
	if (preg_match($pattern, $mac)) {
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
	}else{
		return $mac;
	}
}
function getListUser() {
	global $db;	
	$data = [];
	$listus = $db->SimpleWhile("SELECT id, username, class, name  FROM users");
	if(isset($listus) && count($listus) > 0){
		foreach($listus as $uid => $usr){
			$data[$usr['id']] = array(
				'userid' => $usr['id'],
				'username' => $usr['username'],
				'name' => (isset($usr['name']) && !empty($usr['name']) ? $usr['name'] : null),
				'uclass' => getClassUser($usr['class']),
				'userlass' => $usr['class']
			);
		}
	}
	return $data;		
}
function formatPib($pib) {
    if (preg_match('/^(\S+)\s+(\S+)$/u', $pib, $matches)) {
        $lastName = $matches[1];
        $firstName = $matches[2];
        if (strlen($firstName) > 1) {
            return $lastName . ' ' . mb_substr($firstName, 0, 1) . '.';
        }
        return $pib;
    }
    if (preg_match('/^(\S+)\s+(\S)\S*\s+(\S)\S*$/u', $pib, $matches)) {
        return $matches[1] . ' ' . $matches[2] . '.' . $matches[3] . '.';
    }
    return $pib;
}
function fucntion_timed_work($startDate, $endDate = null) {
    if ($endDate === null) {
        $end = new DateTime($startDate);
        $end->setTime(18, 0); // Встановлюємо час на 18:00
    } else {
        $end = new DateTime($endDate);
    }    
    $start = new DateTime($startDate);
    $interval = $start->diff($end);
    
    $hours = ($interval->days * 24) + $interval->h;
    
    return $hours + ($interval->i / 60);
}
function PaidTypeApp($types) {
    global $lang;    
    $payment_types = [
        1 => [
            'label' => $lang['type_paid'],
            'formats' => '%s',
            'format' => '%s %s',
            'data' => $types['money'],
        ],
        2 => [
            'label' => $lang['type_free_charge'],
            'formats' => '%s',
            'format' => '%s',
            'data' => '',
        ],
        3 => [
            'label' => $lang['type_service'],
            'formats' => '%s',
            'format' => '%s №%s',
            'data' => $types['dogovir'],
        ],
    ];
    $payment_type = isset($payment_types[$types['payment_type']]) ? $payment_types[$types['payment_type']] : null;
    $data = [
        'type' => ($payment_type !== null) ? sprintf($payment_type['formats'], $payment_type['label'], $payment_type['data']) : '',
        'typeid' => $types['payment_type'],
        'type_data' => ($payment_type !== null) ? $payment_type['data'] : '',
        'type_note' => ($payment_type !== null) ? sprintf($payment_type['format'], $payment_type['label'], $payment_type['data']) : '',
    ];
    return $data;
}


function getPanelSwitch($switch) {
	global $lang, $access, $db, $confPMon;
	$tplRes = '';
	$tplRes .= '<div class="admin_panel">';
	// ZTE 6 WRITE ALL

	if($switch['device']=='olt' && $switch['oidid']==6 ){
		$descr_btn = 'Save current config';
		$tplRes .= '<div class="command" onclick="command_data('.$switch['id'].',\'system1\',\''.$descr_btn.'\')"><img src="../style/img/huawei_countmac.png"><div class="dscr"><h2>Write All</h2><h3>'.$descr_btn.'</h3></div></div>';
	}
	if($switch['device']=='olt' && $switch['oidid']==1){
		$descr_btn = 'Save current config';
		$tplRes .= '<div class="command" onclick="command_data('.$switch['id'].',\'system2\',\''.$descr_btn.'\')"><img src="../style/img/huawei_countmac.png"><div class="dscr"><h2>Write All</h2><h3>'.$descr_btn.'</h3></div></div>';
	}
	if($switch['oidid'] == 14 || $switch['oidid'] == 33) {
		$descr_btn = 'Get onu check';
		$tplRes .= '<div class="command" onclick="sysComm('.$switch['id'].',\'worker\',\''.$descr_btn.'\',8)"><img src="../style/img/pmon_error.png"><div class="dscr"><h2>'.$lang['onuerror'].'</h2><h3>'.$descr_btn.'</h3></div></div>';
	}
	// BACKUP OLT
	if(isset($confPMon['BACKUP_OLT']) && !empty($confPMon['BACKUP_OLT']) && $confPMon['BACKUP_OLT']==1){
		if (!empty($switch['password'])) {
			$descr_btn = 'Backup current config';		
			$descr_btn_backup = 'Backup current config';		
			$tplRes .= '<div class="command" onclick="command_data('.$switch['id'].',\'backup\',\''.$descr_btn.'\')"><img src="../style/img/pmon_backup.png"><div class="dscr"><h2>Backup</h2><h3>'.$descr_btn_backup.'</h3></div></div>';
		}	
	}
	if ($switch['oidid']==1 && !empty($switch['password'])) {
		$telnet_mac_jobid = 53;
	}elseif($switch['oidid']==15 && !empty($switch['password'])){
		$telnet_mac_jobid = 54;
	}	
	if ($switch['oidid']==1 && $switch['snmprw']) {
		$snmp_mac_jobid = 46;
	}
	// FDB TABLE OLT
	/*
	if($switch['device']=='olt' && $snmp_mac_jobid>0){		
	#	$tplRes .= '<div class="command" onclick="checker_data('.$switch['id'].','.$snmp_mac_jobid.',\'перевірити\')">
	#		<img src="../style/img/huawei_countmac.png"><div class="dscr">
	#			<h2>FDB таблиця v.1</h2><h3>Отримання таблиці MAC за допомогою snmp</h3></div></div>';
	}
	*/
	if($switch['device']=='olt' && $telnet_mac_jobid>0){
		$descr_btn = 'Show mac address-table?';		
		$tplRes .= '<div class="command" onclick="pmon_worker('.$switch['id'].',\'worker\',\''.$descr_btn.'\','.$telnet_mac_jobid.')">
			<img src="../style/img/pmon_table.png"><div class="dscr">
				<h2>FDB таблиця</h2><h3>Show mac address-table</h3></div></div>';
	}	
	// RX OLT ONU
	if($switch['device']=='olt'){
		$rx_onu_olt_jobid = 31;
		$descr_btn = 'Отримання сигналів';
		$tplRes .= '<div class="command" onclick="checker_data('.$switch['id'].','.$rx_onu_olt_jobid.',\''.$descr_btn.'\')"><img src="../style/img/pmon_signal.png"><div class="dscr"><h2>Сигнали RX OLT</h2><h3>'.$descr_btn.'</h3></div></div>';
	}	
	// VENDOR ONU ALL SUPPORT
	if($switch['device']=='olt'){
		$tplRes .= '<div class="command" onclick="checker_onu_vendor('.$switch['id'].')"><img src="../style/img/huawei_countmac.png"><div class="dscr"><h2>Vendor ID</h2><h3>'.$lang['load_info'].'</h3></div></div>';
	}
	$tplRes .= '<div class="command" onclick="checker_port_status('.$switch['id'].',2)"><img src="../style/img/pmon_port.png"><div class="dscr"><h2>'.$lang['statport'].'</h2><h3>'.$lang['load_info'].'</h3></div></div>';
	$tplRes .= '</div>';
	return $tplRes;
}

function geoDevice($dataid) {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 7200;
	$data = [];
	$cacheKey = "geodevice_".$dataid;	
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data = $db->Fast('geodevice','*',['deviceid'=>$dataid]);
			$cacheManager->set($cacheKey,$data,$expiration);
		}
	}else{
		$data = $db->Fast('geodevice','*',['deviceid'=>$dataid]);
	}
	return $data;	
}
function get_connect_battery($ping3id) {
	global $db, $config;
	$result = '';
	$result_akb ='';
	$sqlused = $db->SimpleWhile("SELECT * FROM battery_used Where deviceid = ".$ping3id." AND connectd = 'ping3'");
	if(is_array($sqlused)){
		foreach ($sqlused as $id => $used) {
			$getbattery = $db->Fast('battery', '*', ['id' => $used['batteryid']]);
			$type_akb = str_replace('lifepo4', 'LP4', $getbattery['types']);
			$result_akb .= '' . $type_akb . '<span class="bored_horizontal"></span>';
			$result_akb .= '' . $getbattery['amper'] . 'Ah';
			if (count($sqlused) > 2) {
				$result_akb .= '<span class="bored_horizontal_end"></span>';
			} elseif (count($sqlused) === 2 && $id === 0) {
				$result_akb .= '<span class="bored_horizontal_end"></span>';
			}
		}
		$result = '<div class="battery-subname">'.$result_akb.'</div>';
	}
	return $result;
}
function getBadRxPonInfo($olt,$sfpid) {
	global $db, $config;
	$badsignalstart = '-'.$config['badsignalstart'];
	$badsignalend = '-'.$config['badsignalend'];
	$where = " status = '1' AND rx IS NOT NULL AND rx != '' AND rx != '0' AND rx BETWEEN " . (int)$badsignalend.".99 AND " . (int)$badsignalstart . ".00 ";
	$data = $db->Simple("SELECT count(idonu) as cont_onu FROM onus WHERE {$where} AND olt = '".$olt."' AND portolt = '".$sfpid."' ORDER BY CAST(rx AS DECIMAL(10, 2)) ASC");
	return $data;	
}
function get_bad_rx_onu_all() {
	global $db, $config;
	$badsignalstart = '-'.$config['badsignalstart'];
	$badsignalend = '-'.$config['badsignalend'];
	$where = " status = '1' AND rx IS NOT NULL AND rx != '' AND rx != '0' AND rx BETWEEN " . (int)$badsignalend.".99 AND " . (int)$badsignalstart . ".00 ";
	$sql_bad_signal = [
		'sql' => "SELECT count(idonu) as bad_rx FROM onus WHERE {$where} ORDER BY CAST(rx AS DECIMAL(10, 2)) ASC",
		'key' => 'bad_signal_all_onu','time' => 720
	];
	$data = cache_simple_sql($sql_bad_signal);
	return $data;	
}
function get_bad_rx_olt($olt) {
	global $db, $config;
	$badsignalstart = '-'.$config['badsignalstart'];
	$badsignalend = '-'.$config['badsignalend'];
	$where = "olt = '{$olt}' AND status = '1' AND rx IS NOT NULL AND rx != '' AND rx != '0' AND rx BETWEEN " . (int)$badsignalend.".99 AND " . (int)$badsignalstart . ".00 ";
	$sql_bad_signal = [
		'sql' => "SELECT count(idonu) as bad_rx FROM onus WHERE {$where} ORDER BY CAST(rx AS DECIMAL(10, 2)) ASC",
		'key' => 'bad_signal_'.$olt.'_onu','time' => 720
	];
	$data = cache_simple_sql($sql_bad_signal);
	return $data['bad_rx'];
}
function getNewOnuPon($olt,$sfpid) {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 300;
	$data = [];
	$cacheKey = "olt_".$olt."_pon_".$sfpid;	
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data = $db->Simple("SELECT count(idonu) as cont_onu FROM `onus` WHERE added  >= curdate() AND olt = '".$olt."' AND portolt = '".$sfpid."'");
			$cacheManager->set($cacheKey,$data,$expiration);
		}
	}else{
		$data = $db->Simple("SELECT count(idonu) as cont_onu FROM `onus` WHERE added  >= curdate() AND olt = '".$olt."' AND portolt = ".$sfpid);
	}
	return $data;	
}
function getOnusErrorDeltaToday(PDO $pdo, array $switch, int $idonu, array $confPMon, $cacheManager): int {
    $delta = 0;
	if (!empty($switch['oidid']) && 
	(
		(int)$switch['oidid'] === 1 || (int)$switch['oidid'] === 14
	)) {
		$cacheKey   = "onus_error_delta_today_" . $idonu . "_" . date('Y-m-d');
		$expiration = 300;
		if (!empty($confPMon['CACHE']) && (int)$confPMon['CACHE'] === 1) {
			$cached = $cacheManager->get($cacheKey);
			if ($cached !== null) {
				return (int)$cached;
			}
		}
		$stmt = $pdo->prepare("SELECT COALESCE(SUM(GREATEST(riznica, 0)), 0) AS delta_today FROM onus_error  WHERE idonu = :idonu  AND added >= CURRENT_DATE()");
		$stmt->execute([':idonu' => $idonu]);
		$delta = (int)($stmt->fetchColumn() ?: 0);
		if (!empty($confPMon['CACHE']) && (int)$confPMon['CACHE'] === 1) {
			$cacheManager->set($cacheKey, $delta, $expiration);
		}
	}
    return $delta;
}
function getBadRxPon($olt,$sfpid) {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 720;
	$data = [];
	$cacheKey = "bad_olt_".$olt."_pon_".$sfpid;	
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data = $db->Simple("SELECT count(idonu) as cont_onu FROM `onus` WHERE added  >= curdate() AND olt = '".$olt."' AND portolt = '".$sfpid."'");
			$cacheManager->set($cacheKey,$data,$expiration);
		}
	}else{
		$data = $db->Simple("SELECT count(idonu) as cont_onu FROM `onus` WHERE added  >= curdate() AND olt = '".$olt."' AND portolt = ".$sfpid);
	}
	return $data;	
}
function getFastOnusData(?string $onukey): array {
	global $pdo, $confPMon, $cacheManager;
    $onukey = trim((string)$onukey);
	if ($onukey === null) return [];
    if ($onukey === '') return [];
    $expiration = 600;
    $cacheKey = "onus_data_" . $onukey;	
    $data = [];
    if (!empty($confPMon['CACHE']) && (int)$confPMon['CACHE'] === 1) {
        $cachedResult = $cacheManager->get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }
    }
    $stmt = $pdo->prepare("SELECT * FROM onusdata WHERE onukey = :onukey LIMIT 1");
    $stmt->execute([':onukey' => $onukey]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    if (!empty($confPMon['CACHE']) && (int)$confPMon['CACHE'] === 1) {
        $cacheManager->set($cacheKey, $data, $expiration);
    }
    return $data;
}
function PmonBillingTemplate($pmon_billing) {
	global $db, $confPMon, $cacheManager; // Додаємо глобальну змінну pmon_billing
	if(isset($pmon_billing['city'])){
		if (isset($confPMon['PB_LIST_TEMPLATE']) && !empty($confPMon['PB_LIST_TEMPLATE'])) {
			$template = $confPMon['PB_LIST_TEMPLATE'];
			$template = str_replace('_', '=', $template);
			if (isset($pmon_billing['city']) && !empty($pmon_billing['city'])) {
				$template = str_replace('[city]', $pmon_billing['city'], $template);
			} else {
				$template = str_replace('[city]', '', $template); // Видалення, якщо значення немає
			}
			if (isset($pmon_billing['street']) && !empty($pmon_billing['street'])) {
				$template = str_replace('[street]', ' вул.'.$pmon_billing['street'], $template);
			} else {
				$template = str_replace('[street]', '', $template); // Видалення, якщо значення немає
			}
			if (isset($pmon_billing['houser']) && !empty($pmon_billing['houser'])) {
				$template = str_replace('[houser]', ' № '.$pmon_billing['houser'], $template);
			} else {
				$template = str_replace('[houser]', '', $template); // Видалення, якщо значення немає
			}
			if (isset($pmon_billing['nomer']) && !empty($pmon_billing['nomer'])) {
				$nomer = is_numeric($pmon_billing['nomer']) ? ' кв ' . $pmon_billing['nomer'] : ' '.$pmon_billing['nomer'];
				$template = str_replace('[nomer]', $nomer, $template);
			} else {
				$template = str_replace('[nomer]', '', $template); // Видалення, якщо значення немає
			}
			if (isset($pmon_billing['uid']) && !empty($pmon_billing['uid'])) {
				$template = str_replace('[uid]', $pmon_billing['uid'], $template);
			} else {
				$template = str_replace('[uid]', '', $template); // Видалення, якщо значення немає
			}
            // Обробка [url] і [href:url]
            if (strpos($template, '[url]') !== false) {
                // Заміна тексту всередині [url]...[/url]
                $text = preg_replace('/\[url\](.*?)\[\/url\]/s', '$1', $template);
                // Заміна [href:url]
                preg_match('/\[href:([^\]]+)\]/', $template, $matches);
                if (isset($matches[1])) {
                    $url = $matches[1];
                    if (isset($pmon_billing['uid']) && !empty($pmon_billing['uid'])) {
                        $url = str_replace('[uid]', $pmon_billing['uid'], $url);
                    }
                    $template = '<a href="' . $url . '">' . $text . '</a>';
                }
                // Видалення [href:url] з шаблону
                $template = preg_replace('/\[href:[^\]]+\]/', '', $template);
            }
            return '<span class="on_">' . $template . '</span>';
		}else{
			// Обробка випадку, коли $confPMon['PB_LIST_TEMPLATE'] не задано
			$address = '';
			if (isset($pmon_billing['pib']) && !empty($pmon_billing['pib'])) {
				$address .= $pmon_billing['pib'].' ';
			}
			return trim($address);
		}
	}
	
	return $pmon_billing;
}
function PmonBillingData($dataonu) {
	global $db, $confPMon, $cacheManager;
	$expiration = 300;
	$onukey = trim((!empty($dataonu['mac'])?$dataonu['mac']:(!empty($dataonu['sn'])?$dataonu['sn']:null)));
	$cacheKey = "pmon_billing_".$onukey;	
	$data = [];
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data = $db->Simple("SELECT * FROM billing_usr WHERE onumac = '{$onukey}' LIMIT 1");					
			$cacheManager->set($cacheKey,$data,$expiration);
		}
	}else{
		$data = $db->Simple("SELECT * FROM billing_usr WHERE onumac = '{$onukey}' LIMIT 1");
	}
	return $data;	
}
function getGroupsAll() {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 7200;
	$data = [];
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey = "list_groups";		
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data = $db->SimpleWhile("SELECT * FROM `groups`");
			if(isset($data) && count($data)>0){
				$cacheManager->set($cacheKey,$data,$expiration);
			}
		}
	}else{
		$data = $db->Multi('groups');
	}
	return $data;
}
function sql__(array $data) {
    global $db, $confPMon, $cacheManager;
    $expiration = !empty($data['time']) ? (int)$data['time'] : 3600;
    if (isset($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
        if (!empty($data['key'])) {
            $cacheKey = $data['key'];
            $cachedResult = $cacheManager->get($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
    }
    if (!empty($data['sql'])) {
        if (!empty($data['type']) && $data['type'] == 'while') {
            $resultSet = $db->SimpleWhile($data['sql']);
        } else {
            $resultSet = $db->Simple($data['sql']);
        }
        $result = [];
        if (isset($data['uniq']) && !empty($data['uniq'])) {
            foreach ($resultSet as $row) {
                $uniqKey = $row[$data['uniq']] ?? null;
                if ($uniqKey !== null) {
                    $result[$uniqKey][] = $row;
                }
            }
        } else {
            $result = $resultSet;
        }
        if (isset($confPMon['CACHE']) && $confPMon['CACHE'] == 1 && !empty($data['key'])) {
            $cacheManager->set($data['key'], $result, $expiration);
        }
        return $result;
    }
    return null;
}
function getSwitchAll() {
	$sql_switch = [
		'sql' => 'SELECT * FROM switch','type' => 'while','key' => 'list_olt_all','time' => 3600
	];
	$data = cache_simple_sql($sql_switch);
	return $data;
}
function getSwitchOlt() {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 3600;
	$data = [];
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey = "app_list_olt";		
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data = $db->SimpleWhile("SELECT * FROM switch WHERE device = 'olt'");
			$cacheManager->set($cacheKey,$data, $expiration);
		}
	}else{
		$data = $db->SimpleWhile("SELECT * FROM switch WHERE device = 'olt'");
	}
	return $data;
}
function metersToKilometers($meters) {
    $kilometers = $meters / 1000;
    $formatted = number_format($kilometers, 1, '.', '');
    return $formatted . ' km';
}
function APP_getLocation() {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 3600;
	$data = [];
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey = "app_list_location";		
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data_olt = $db->SimpleWhile("SELECT * FROM location");
			if(isset($data_olt) && count($data_olt)>0){
				foreach($data_olt as $temp){	
					$data[$temp['id']] = $temp;
				}
			}
			$cacheManager->set($cacheKey,$data, $expiration);
		}
	}else{
		$data = $db->SimpleWhile("SELECT * FROM location");
	}
	return $data;
}
function APP_getSwitchOlt() {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 3600;
	$data = [];
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey = "list_olt";		
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data_olt = $db->SimpleWhile("SELECT * FROM switch WHERE device = 'olt'");
			if(isset($data_olt) && count($data_olt)>0){
				foreach($data_olt as $temp){	
					$data[$temp['id']] = $temp;
				}
			}
			$cacheManager->set($cacheKey,$data, $expiration);
		}
	}else{
		$data = $db->SimpleWhile("SELECT * FROM switch WHERE device = 'olt'");
	}
	return $data;
}
function silent_getSwitchOlt() {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 3600;
	$sql = "SELECT s.* FROM checkaccess a JOIN switch s ON CONCAT('dev', s.id) = a.types WHERE a.uid = '{$USER['id']}' AND s.device = 'olt'";
	$data = [];
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey = "list_olt_module";		
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$data_olt = $db->SimpleWhile($sql);
			if(isset($data_olt) && count($data_olt)>0){
				foreach($data_olt as $temp){	
					$data[$temp['id']] = $temp;
				}
			}
			$cacheManager->set($cacheKey,$data, $expiration);
		}
	}else{
		$data = $db->SimpleWhile($sql);
	}
	return $data;
}
function get_map_onu_olt($dataonu,$id) {
	global $db, $config, $confPMon, $cacheManager;
	$cacheKey = "map_onu_olt_".$dataonu['olt'];
	$marker = '';
	$sql_switch = [
		'sql' => 'SELECT d.lan AS plan, d.lon AS plon, d.uid, o.rx, o.idonu
FROM onus o
LEFT JOIN onusdata d
ON d.onukey COLLATE utf8mb3_general_ci = o.mac COLLATE utf8mb3_general_ci
WHERE o.olt = '.$dataonu['olt'].'
UNION ALL
SELECT d.lan AS plan, d.lon AS plon, d.uid, o.rx, o.idonu
FROM onus o
LEFT JOIN onusdata d
ON d.onukey COLLATE utf8mb3_general_ci = o.sn COLLATE utf8mb3_general_ci
WHERE o.olt = '.$dataonu['olt'].'
AND (o.mac IS NULL OR o.sn <> o.mac)','type' => 'while',
		'key' => "map_onu_olt_".$dataonu['olt'],'time' => 3600
	];
	$allmaponu = cache_simple_sql($sql_switch);
	if(!empty($allmaponu)){
	foreach($allmaponu as $maponu){	
	if(!empty($maponu['plon']) && !empty($maponu['plon']) && $maponu['idonu']!=$id){
	$marker .= "L.marker([".$maponu['plan'].",".$maponu['plon']."],{icon: L.divIcon({html:'".MapRxTerminal($maponu['rx'])."'})}).addTo(map);
	";}}}	
	return $marker;
}
function getLocation() {
	$sql_switch = [
		'sql' => 'SELECT * FROM location','type' => 'while','key' => 'location_pmon','time' => 3600
	];
	$data = cache_simple_sql($sql_switch);
	return $data;
}
function del_cache_simple_sql($key) {
	global $confPMon, $cacheManager;
	if (isset($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheManager->delete($key);
	}
}
function cache_simple_sql(array $data) {
    global $db, $confPMon, $cacheManager;
    $expiration = !empty($data['time']) ? (int)$data['time'] : 3600;
    if (isset($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
        if (!empty($data['key'])) {
            $cacheKey = $data['key'];
            $cachedResult = $cacheManager->get($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }
        }
    }
    if (!empty($data['sql'])) {
		if (!empty($data['type']) && $data['type']=='while') {
			$result = $db->SimpleWhile($data['sql']);
		}else{
			$result = $db->Simple($data['sql']);			
		}
		if (!empty($data['unique'])) {
			$temp = [];
			if(isset($result) && count($result)>0){
				foreach($result as $row){
					if (isset($row[$data['unique']])) {
						$temp[$row[$data['unique']]] = $row;
					}
				}
				$result = $temp;
			}
		}
        if (isset($confPMon['CACHE']) && $confPMon['CACHE'] == 1 && !empty($data['key'])) {
            $cacheManager->set($data['key'], $result, $expiration);
        }
        return $result;
    }
    return null;
}
function snmp_data_day($portid) {
    global $db, $config, $confPMon, $cacheManager;
    $expiration = 1200;
    $data = [];
    if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
        $cacheKey = "band_traff_timestamp_port_".$portid;        
        $cachedResult = $cacheManager->get($cacheKey);
        if ($cachedResult !== null) {
            $data = $cachedResult;
        } else {
            $sql = "SELECT COUNT(*) AS days FROM bandwidth_daily WHERE portid = '{$portid}' AND traffic_day != ''";
            $data = $db->Simple($sql);
            $cacheManager->set($cacheKey, $data, $expiration);
        }
    } else {
        $data = $db->Simple("SELECT COUNT(*) AS days FROM bandwidth_daily WHERE portid = '{$portid}' AND traffic_day != ''");
    }

    return $data;
}
function getOltStats($olt) {
	global $db, $config, $confPMon, $cacheManager;
	$data = [];	
	$expiration1 = 360;
	$expiration2 = 360;	
	$expiration3 = 360;	
	$expiration4 = 360;	
	$expiration5 = 360;	
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey1 = "sql_count_onu_" . $olt;		
		$cachedResult1 = $cacheManager->get($cacheKey1);
		if ($cachedResult1 !== null) {
			$data['sql_count_onu'] = $cachedResult1;
		} else {
			$sql_count_onu = $db->Simple("SELECT count(idonu) as cont_onu FROM onus WHERE olt = " . $olt);
			if (isset($sql_count_onu)) {
				$data['sql_count_onu'] = $sql_count_onu['cont_onu'];
			}
			$cacheManager->set($cacheKey1, $data['sql_count_onu'], $expiration1);
		}
	} else {
		$sql_count_onu = $db->Simple("SELECT count(idonu) as cont_onu FROM onus WHERE olt = " . $olt);
		if (isset($sql_count_onu)) {
			$data['sql_count_onu'] = $sql_count_onu['cont_onu'];
		}
	}	
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey2 = "sql_count_onu_on_" . $olt;		
		$cachedResult2 = $cacheManager->get($cacheKey2);
		if ($cachedResult2 !== null) {
			$data['sql_count_onu_on'] = $cachedResult2;
		} else {
			$sql_count_onu_on = $db->Simple("SELECT count(idonu) as cont_onu FROM onus WHERE status = 1 AND olt = " . $olt);
			if (isset($sql_count_onu_on)) {
				$data['sql_count_onu_on'] = $sql_count_onu_on['cont_onu'];
			}
			$cacheManager->set($cacheKey2, $data['sql_count_onu_on'], $expiration2);
		}
	} else {
		$sql_count_onu_on = $db->Simple("SELECT count(idonu) as cont_onu FROM onus WHERE status = 1 AND olt = " . $olt);
		if (isset($sql_count_onu_on)) {
			$data['sql_count_onu_on'] = $sql_count_onu_on['cont_onu'];
		}
	}		
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey3 = "cont_pon_" . $olt;		
		$cachedResult3 = $cacheManager->get($cacheKey3);
		if ($cachedResult3 !== null) {
			$data['sql_count_pon'] = $cachedResult3;
		} else {
			$sql_count_pon = $db->Simple("SELECT count(id) as cont_pon FROM switch_pon WHERE oltid = ".$olt);
			if (isset($sql_count_pon)) {
				$data['sql_count_pon'] = $sql_count_pon['cont_pon'];
			}
			$cacheManager->set($cacheKey3, $data['sql_count_pon'], $expiration3);
		}
	} else {
		$sql_count_pon = $db->Simple("SELECT count(id) as cont_pon FROM switch_pon WHERE oltid = ".$olt);
		if (isset($sql_count_pon)) {
			$data['sql_count_pon'] = $sql_count_pon['cont_pon'];
		}
	}	
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey4 = "cont_port_" . $olt;		
		$cachedResult4 = $cacheManager->get($cacheKey4);
		if ($cachedResult4 !== null) {
			$data['sql_count_port'] = $cachedResult4;
		} else {
			$sql_count_port = $db->Simple("SELECT count(id) as cont_port FROM switch_pon WHERE oltid = ".$olt);
			if (isset($sql_count_port)) {
				$data['sql_count_port'] = $sql_count_port['cont_port'];
			}
			$cacheManager->set($cacheKey4, $data['sql_count_port'], $expiration4);
		}
	} else {
		$sql_count_port = $db->Simple("SELECT count(id) as cont_port FROM switch_pon WHERE oltid = ".$olt);
		if (isset($sql_count_port)) {
			$data['sql_count_port'] = $sql_count_port['cont_port'];
		}
	}		
	return $data;
}
function loggerBackground_core($cmd,$basePath = '/export/cache/') {
    $pid = getmypid();
    $filePath = $basePath . $pid . '.pid';
    $data = array('pid' => $pid, 'cmd' => $cmd, 'added' => date('Y-m-d H:i:s'));
    file_put_contents(ROOT_DIR . $filePath, json_encode($data));
}
function div_err_snmp() {
	global $lang, $db;
	$timer = date('Y-m-d H:i:s');
	echo'<div class="onu_reasons view_onu_none"><b>'.$lang['not_snmp'].'</b>'.$lang['not_snmp_descr'].'<span>'.$timer.'</span></div>';
	die;
}
function save_snmp_uptime($snmp_uptime,$switch) {
    global $db, $logger;	
	$value = strtolower(str_replace('INTEGER:', '', str_replace(' ', '', str_replace('"', '', trim($snmp_uptime)))));
    $valuetime = switch_uptime($value);
    if(isset($valuetime)){
        $db->SQLupdate('switch',['uptime'=>trim($valuetime)],['id' => $switch['id']]);
    }
}
function get_snmp_uptime($switch): ?string {
    $version = SNMP::VERSION_2c;
    $timeout = 500000;
    $retries = 2;      
    try {
        $session = new SNMP($version, $switch['netip'], $switch['snmpro'], $timeout, $retries);
        $snmp_uptime = @$session->get("1.3.6.1.2.1.1.3.0");
        $session->close();
        return $snmp_uptime !== false ? $snmp_uptime : null;
    } catch (SNMPException $e) {
        error_log('SNMP Exception: ' . $e->getMessage());
        return null;
    }
}
function check_snmp_uptime($switch): bool {
    global $logger;
    $snmp_uptime = get_snmp_uptime($switch);
    if ($snmp_uptime) {
        save_snmp_uptime($snmp_uptime, $switch);
        return true;
    }
    sleep(1);
    $snmp_uptime = get_snmp_uptime($switch);
    if ($snmp_uptime) {
        save_snmp_uptime($snmp_uptime, $switch);
        return true;
    }
    $logger->init([
        'log' => 'device',
        'type' => 'monitor',
        'descr' => 'Error SNMP: Failed to retrieve uptime',
        'deviceid' => $switch['id'],
        'who' => 'cron',
    ]);
    return false;
}
function snmp_access($switch): bool {
    global $confPMon;
    if (empty($confPMon['CHECK_SNMP']) || $confPMon['CHECK_SNMP'] != 1) {
        return true;
    }
    return check_snmp_uptime($switch);
}
function grab_telegram($message) {
	$sms = trim($message);
	$sms = str_replace('[b]','<b>', $sms);
	$sms = str_replace('[/b]','</b>', $sms);
	$sms = str_replace('icon_battery_1','🪫', $sms);
	$sms = str_replace('icon_battery_2','🔋', $sms);
	$sms = str_replace('icon_battery_3','🔌', $sms);
	$sms = str_replace('[icon-fire]','🔥', $sms);
	$sms = str_replace('[icon-ice]','❄️', $sms);
	$sms = str_replace('[icon-temp]','🌡', $sms);
	$sms = str_replace('[icon-fun]','🏝', $sms);
	$sms = str_replace('[icon-non_fire]','🧯', $sms);
	$sms = str_replace('[icon-pazle]','🧩', $sms);
	$sms = str_replace('[icon-offline-min]','🟥', $sms);
	$sms = str_replace('[icon-online-min]','🟩', $sms);
	$sms = str_replace('[icon-time]','⏰', $sms);
	$sms = str_replace('[icon-work]','🚧', $sms);
	$sms = str_replace('[icon-volt]','⚡️', $sms);
	$sms = str_replace('[icon-power-low]','🪫', $sms);
	$sms = str_replace('[icon-power]','🔌', $sms);
	$sms = str_replace('[icon-right]','🔀', $sms);
	$sms = str_replace('[icon-alarm]','🚨', $sms);
	$sms = str_replace('[icon-warning]','⚠️', $sms);
	$sms = str_replace('[icon-los-pon]','🔴', $sms);
	$sms = str_replace('[icon-up]','⬆️', $sms);
	$sms = str_replace('[icon-down]','⬇️', $sms);
	$sms = str_replace('[icon-plan]','🚧', $sms);
	$sms = str_replace('[icon-bomb]','💣', $sms);
	$sms = str_replace('[icon-taktor]','🚜', $sms);
	$sms = str_replace('[icon-reger]','🔘', $sms);
	$sms = str_replace('[icon-myxa]','♨️', $sms);
	$sms = str_replace('[icon-unlock]','🔐', $sms);
	$sms = str_replace('[icon-lock]','🔐', $sms);
	$sms = str_replace('[icon-super]','✅', $sms);
	$sms = str_replace('[icon-stop]','⛔️', $sms);
	$sms = str_replace('[icon-okey]','🟢', $sms);
	$sms = str_replace('[icon-key]','🔑', $sms);
	$sms = str_replace('[icon-online]','🔅', $sms);
	$sms = str_replace('[icon-offline]','❗️', $sms);
	return $sms;
}
function findDumping() {
    $directory = ROOT_DIR . '/file/backup/';
    $array = [];
    if (is_dir($directory)) {
        $files = glob($directory . 'backup_*.sql');
        $i = 1;		
        foreach ($files as $file) {
            if (preg_match('/backup_(\d{4}_\d{2}_\d{2}-\d{2}_\d{2}_\d{2}).sql/', $file, $matches)) {
				$array[$i] = $matches[1];
                $i++;
            }
        }
    }
	if (isset($array) && is_array($array)) {
		return $array;
	}
	return false;
}
function getListOlt(){
	global $confPMon, $db, $cacheManager;
	$switches = array();
	$getswitch = $db->SimpleWhile("SELECT * FROM switch");
	if (isset($getswitch)) {
		foreach ($getswitch as $swid => $sw) {
			if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
				$expiration = 3600;
				$cacheKey = "switch_get_" . $sw['id'];
				$cachedSwitch = $cacheManager->get($cacheKey);
				if ($cachedSwitch !== null) {
					$switches[$sw['id']] = $cachedSwitch;
				} else {
					$switches[$sw['id']] = $sw;
					$cacheManager->set($cacheKey, $switches[$sw['id']], $expiration);
				}
			} else {
				$switches[$sw['id']] = $sw;
			}
		}
	}
	return $switches;
}
function generateAutoLoadScript() {
    $script = <<<HTML
<script>
$(document).ready(function() {
    $('div[data-id]').each(function() {
        var id = $(this).data('id');
        var tree = $(this).data('tree');
        var resultDiv = $(this);
        $.ajax({
            type: 'POST',
            url: 'ajax/fiber.php',
            data: {
				get: 'listonu' ,
                id: id,
                tree: tree
            },
            success: function(response) {
                resultDiv.html(response);
            }
        });
    });    
});
$(document).ready(function() {
	$('div[data-xd]').each(function() {
        var xd = $(this).data('xd');
        var xtree = $(this).data('xtree');
        var resultDiv = $(this);
        $.ajax({
            type: 'POST',
            url: 'ajax/fiber.php',
            data: {
				get: 'sersignal' ,
                id: xd,
                tree: xtree
            },
            success: function(response) {
                resultDiv.html(response);
            }
        });
    });
});
</script>
HTML;
return $script;
}
function list_house_comm($id){
    global $db;
	$data = array();
    $sqlcomm = $db->SimpleWhile("SELECT * FROM skyscraper_comm WHERE house = " . $id);
	if(isset($sqlcomm) && count($sqlcomm)>0){
		foreach($sqlcomm as $id => $comm){
			$data[$comm['kv']][$comm['id']]['img'] = '<img src="../style/img/house-'.$comm['info'].'.png">';
			$data[$comm['kv']][$comm['id']]['note'] = ''.$comm['note'].'';
		}
	}
	return $data;	
}
function list_pon_element_spliters($id){
    global $db;
    $sqlspliters = $db->SimpleWhile("SELECT * FROM spliters WHERE ponelement = " . $id);
	$data = '<div class="ponelementdevicemap_spliter">';
    foreach($sqlspliters as $idspliters => $spliters){
		$data .= '<span id="spliter_'.$spliters['id'].'"><div onclick="spliters(\'spliterview\','.$spliters['id'].')">'.spliter_types($spliters['spliterid']).'</div></span>';
	}
	$data .= '</div>';	
	return $data;	
}
function onu_bulding($ont){
	global $db, $confPMon;
	if (isset($confPMon['PON_HIGH_RISE']) && !empty($confPMon['PON_HIGH_RISE']) && $confPMon['PON_HIGH_RISE'] == 1) {
		$sql = "SELECT id FROM skyscraper_kv WHERE idonu = '".$ont['idonu']."' LIMIT 1";
		$building = $db->Simple($sql);
		if(!empty($building['id'])){
			return 'int_building';
		}else{
			return '1111';
		}
	}
	return false;
}
function signal_min($data){
    global $db;
    $where_onus = '';

    if (!empty($data['pontree'])) {
        $where_onus = ' WHERE pontree = ' . $data['pontree'];
    }
    if (!empty($data['ponelement'])) {
        if (!empty($where_onus)) {
            $where_onus .= ' AND';
        } else {
            $where_onus = ' WHERE';
        }
        $where_onus .= ' ponelement = ' . $data['ponelement'];
    }
	$sqlonus = $db->SimpleWhile("SELECT onukey FROM onusdata " . $where_onus);
	$rxValues = [];
	foreach ($sqlonus as $idont => $ont) {
		$sqlonu = $db->Simple("SELECT idonu, status, rx, rxolt, dist, rxstatus, reason, online, offline FROM onus WHERE status = 1 AND (mac = '" . $ont['onukey'] . "' OR sn = '" . $ont['onukey'] . "') LIMIT 1");
		if (isset($sqlonu['idonu']) && $sqlonu['idonu'] > 0) {
			$rxValues[] = floatval($sqlonu['rx']);
		}
	}
	$averageRx = 0;
	if (!empty($rxValues)) {
		$averageRx = array_sum($rxValues) / count($rxValues);
	}
	$data = '';
	if ($averageRx < -25.90) {
		$color = 'color4';
	} elseif ($averageRx >= -25.90 && $averageRx < -21.10) {
		$color = 'color3';
	} elseif ($averageRx >= -21.10 && $averageRx < -16.10) {
		$color = 'color2';
	} else {
		$color = 'color1';
	}
	if($averageRx!='0.00'){
		$data = '<div class="minimal_signal">';
		$data .= '<div class="'.$color.'">' . number_format($averageRx, 2) . '</div>';
		$data .= '</div>';
	}
	return $data;
}
function list_pon_element(PDO $pdo, array $filters): string
{
	global $lang;
    $where  = [];
    $params = [];
    if (!empty($filters['pontree'])) {
        $where[] = 'od.pontree = :pontree';
        $params[':pontree'] = (int)$filters['pontree'];
    }
    if (!empty($filters['ponelement'])) {
        $where[] = 'od.ponelement = :ponelement';
        $params[':ponelement'] = (int)$filters['ponelement'];
    }
    $sql = "
        SELECT
            od.onukey,
            o.idonu,
            o.status,
            o.rx,
            o.rxolt,
            o.dist,
            o.rxstatus,
            o.reason,
            o.online,
            o.offline
        FROM onusdata od
        LEFT JOIN onus o
               ON (o.mac = od.onukey OR o.sn = od.onukey)
    ";
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY (o.status = 1) DESC, od.onukey ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $html = '<div class="ponelementdevice">';
    foreach ($rows as $r) {
        if (empty($r['idonu'])) {
            continue;
        }
        $cssBuilding = onu_bulding($r);
        $isOnline    = ((int)$r['status'] === 1);
        $statusClass = $isOnline ? 'onstatus' : reason_check_los($r['status'], $r['reason'], 'offstatus');
        $signalHtml = '';
        if ($isOnline) {
            $signalHtml .= '<span class="signal">' . signalTerminal($r['rx']) . '</span>';
            if ($r['rxolt'] !== null && $r['rxolt'] !== '') {
                $signalHtml .= '<span class="signal">' . signalTerminalRx($r['rxolt'],29,39) . '</span>';
            }
        } else {
            $signalHtml .= reason_onu((int)$r['status'], (string)$r['reason']);
        }
        if (!empty($r['rxstatus']) && ($r['rxstatus'] === 'up' || $r['rxstatus'] === 'down')) {
            $signalHtml .= '<span class="signal' . $r['rxstatus'] . '"><i class="fi fi-rr-angle-small-' . $r['rxstatus'] . '"></i></span>';
        }
        $stateLabel = $isOnline ? $lang['online'] : $lang['offline'];
        $timeValue  = aftertime($isOnline ? $r['online'] : $r['offline']);
        $html .= sprintf(
            '<a data-id="%d" id="onu-%d" class="%s onu_ponelement %s" href="/?do=onu&id=%d">
                %s %s
                <img class="linkus" src="../style/img/link.png">
                <div class="subinfo">
                    <span class="class_%d">%s</span>
                    <span class="class_next_%d">%s</span>
                </div>
            </a>',(int)$r['idonu'],(int)$r['idonu'],htmlspecialchars($cssBuilding),htmlspecialchars($statusClass),(int)$r['idonu'],$signalHtml,htmlspecialchars($r['onukey']),(int)$r['status'],$stateLabel,(int)$r['status'],$timeValue
        );
    }
    $html .= '</div>';
    return $html;
}
function reason_onu($status, $reason) {
	global $pmonimg;
	if(isset($status) && $status==2 && isset($reason)){
        switch ($reason) {
			case "err1":
				return '<div class="onu_reason">'.$pmonimg['svg']['energy3'].'</div>';
			break;				
			case "err5":			
				return '<div class="onu_reason">'.$pmonimg['svg']['reason_err5'].'</div>';
			break;				
			case "err81":
				return '<div class="onu_reason">'.$pmonimg['svg']['reason_err81'].'</div>';
			break;				
			case "err6":
				return '<div class="onu_reason">'.$pmonimg['svg']['reason_err6'].'</div>';
			break;					
			case "err0":
				return '<div class="onu_reason">'.$pmonimg['svg']['reason_err6'].'</div>';
			break;				
			case "err8":
				return '<div class="onu_reason">'.$pmonimg['svg']['reason_err8'].'</div>';
			break;				
			case "err34":
				return '<div class="onu_reason">'.$pmonimg['svg']['reason_err59'].'</div>';
			break;			
			case "none":
				return '<div class="onu_reason"></div>';
			break;			
			case "err59":
				return '<div class="onu_reason">'.$pmonimg['svg']['reason_err6'].'</div>';
			break;
			default:
				return '<span class="reason_'.$reason.'"></span>';
		}
	}elseif($status==2){
		return '<div class="status"><img src="../style/img/offline.png"></div>';
	}else{
		return'';
	}	
}
function getPMonSwitch() {
	global $db, $confPMon, $cacheManager;
	$switches = array();
	$getswitch = $db->SimpleWhile("SELECT * FROM switch WHERE monitor = 'yes'");
	if (isset($getswitch)) {
		foreach ($getswitch as $swid => $sw) {
			if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
				$expiration = 14000;
				$cacheKey = "switch_" . $sw['id'];
				$cachedSwitch = $cacheManager->get($cacheKey);
				if ($cachedSwitch !== null) {
					$switches[$sw['id']] = $cachedSwitch;
				} else {
					$switches[$sw['id']] = [
						'id' => $sw['id'],'oidid' => $sw['oidid'],'class' => $sw['class'],'place' => $sw['place'],'netip' => $sw['netip'],'snmpro' => $sw['snmpro']
					];
					$cacheManager->set($cacheKey, $switches[$sw['id']], $expiration);
				}
			} else {
				$switches[$sw['id']] = [
					'id' => $sw['id'],'oidid' => $sw['oidid'],'class' => $sw['class'],'place' => $sw['place'],'netip' => $sw['netip'],'snmpro' => $sw['snmpro']
				];
			}
		}
	}
	return $switches; 	
}
function formatBitsPerSecond(float $bps, string $preferUnits = 'auto'): string {
    if ($bps < 0) $bps = 0;
    $mbps = $bps / 1_000_000;
    $gbps = $bps / 1_000_000_000;
    if ($preferUnits === 'Gb') return number_format($gbps, 2, '.', ' ') . ' Gb';
    if ($preferUnits === 'Mb') return number_format($mbps, 2, '.', ' ') . ' Mb';
    return ($gbps >= 1)
        ? number_format($gbps, 2, '.', ' ') . ' Gb'
        : number_format($mbps, 2, '.', ' ') . ' Mb';
}
function bandwidth_today_stats(int $portId, PDO $pdo, string $metric = 'both', int $ttl = 60): array {
    global $cacheManager;
    $metric = in_array($metric, ['avg','max','both'], true) ? $metric : 'both';
    $cacheKey = "bw:today:{$metric}:{$portId}:" . date('Y-m-d'); // унікально на день
    if (!empty($cacheManager)) {
        $cached = $cacheManager->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }
    }
    $sql = "
        SELECT
            AVG(in_bps)  AS avg_in_bps,
            AVG(out_bps) AS avg_out_bps,
            MAX(in_bps)  AS max_in_bps,
            MAX(out_bps) AS max_out_bps
        FROM snmp_data
        WHERE portid = :pid
          AND timestamp >= CURDATE()
          AND in_bps IS NOT NULL
          AND out_bps IS NOT NULL
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $portId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $out = [
        'avg_in_bps' => (float)($row['avg_in_bps'] ?? 0),
        'avg_out_bps'=> (float)($row['avg_out_bps'] ?? 0),
        'max_in_bps' => (float)($row['max_in_bps'] ?? 0),
        'max_out_bps'=> (float)($row['max_out_bps'] ?? 0),
    ];
    if (!empty($cacheManager)) {
        $cacheManager->set($cacheKey, $out, $ttl);
    }
    return $out;
}
function postCleanValue($key, $default = null, $cleanFunction = 'text') {
    return isset($_POST[$key]) ? Clean::$cleanFunction($_POST[$key]) : $default;
}
function generatePassword($length = 15) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = mt_rand(0, strlen($characters) - 1);
        $password .= $characters[$randomIndex];
    }
    return $password;
}
function pmon_walk_m($data) {
    $result = [];
    $raw = '';
	if(empty($data['community']) || empty($data['ip']) || empty($data['oid'])){
		die('check get snmpwalk parametr');
	}
    if ($data['oid']) {
        switch ($data['type']) {
            case "exec":
                $command = "snmpwalk -v2c -c " . $data['community'] . " " . $data['ip'] . " " . $data['oid'];
				loggerBackground_core($command);
                $raw = shell_exec($command);
                $rawlines = explode(PHP_EOL, trim($raw));
                $raw = array_filter($rawlines, 'strlen');
                break;
            case "class":
                $session = new SNMP(SNMP::VERSION_2C, $data['ip'], $data['community']);
				$session->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
                $raw = @$session->walk($data['oid']);
				if (isset($session))
					unset($session);
                break;
            case "real":
                snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
                $raw = @snmp2_real_walk($data['ip'], $data['community'], $data['oid']);
                break;
        }	
        if (is_array($raw) && count($raw) > 0) {
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
    return $result;
}
function list_ip_pmon_device(){
	global $db;
	$array = [];
	$ip_switch = $db->SimpleWhile("SELECT netip FROM switch");
	if(is_array($ip_switch)){	
		foreach($ip_switch as $sw){
			$array[$sw['netip']] = $sw['netip'];
		}
	}	
	$ip_ping = $db->SimpleWhile("SELECT netip FROM mon_ping3");
	if(is_array($ip_ping)){	
		foreach($ip_ping as $pin3){
			$array[$pin3['netip']] = $pin3['netip'];
		}
	}	
	$ip_mod = $db->SimpleWhile("SELECT ip FROM ipaddress");
	if(is_array($ip_mod)){	
		foreach($ip_mod as $moduleip){
			$array['netip'] = $moduleip['ip'];
		}
	}
	return $array;
}
function list_ip_user(){
	global $db;
	$array = [];
	$ip_mod = $db->SimpleWhile("SELECT ip FROM ipaddress");
	if(is_array($ip_mod)){	
		foreach($ip_mod as $moduleip){
			$array[$moduleip['ip']] = $moduleip['ip'];
		}
	}
	return $array;
}
function list_ip_user_data($id){
	global $db;
	$array = [];
	$ip_mod = $db->SimpleWhile("SELECT * FROM ipaddress Where blockid = ".$id);
	if(is_array($ip_mod)){	
		foreach($ip_mod as $moduleip){
			$array[$moduleip['ip']] = [
				'blockid' => $moduleip['blockid'],
				'vlan' => $moduleip['vlan'],
				'name' => $moduleip['name'],
				'group' => $moduleip['idgroups']
			];
		}
	}
	return $array;
}
function checkerip_type($ip, $systemip, $userip) { 
    if (is_array($systemip) && in_array($ip, $systemip, true)) {
        return 'usedsystem';
    } elseif (is_array($userip) && in_array($ip, $userip, true)) {
        return 'useduser';
    } else {
        return 'lite';
    }
}
function getIPManGroups(){
	global $db;
	$array = [];
	$ipgroups = $db->SimpleWhile("SELECT * FROM ipgroups");
	if(is_array($ipgroups)){	
		foreach($ipgroups as $gr){
			$array[$gr['id']] = [
				'id' => $gr['id'],
				'name' => $gr['name'],
				'color' => $gr['color']
			];
		}
	}
	return $array;
}
function generateIPRange($ipRangeString) {
    $ipRange = [];
    if (strpos($ipRangeString, '/') !== false) {
        list($startIP, $subnet) = explode('/', $ipRangeString);
        $startLong = ip2long($startIP);
        $endLong = $startLong + pow(2, (32 - $subnet)) - 1;
    } elseif (strpos($ipRangeString, '-') !== false) {
        list($startIP, $endIP) = explode('-', $ipRangeString);
        $startLong = ip2long($startIP);
        $endLong = ip2long($endIP);
    } else {
        return $ipRange; // Invalid format
    }

    for ($ip = $startLong; $ip <= $endLong; $ip++) {
        $ipRange[] = long2ip($ip);
    }

    return $ipRange;
}
function clearpon($value) {
	return str_replace('0/','', str_replace('"','', strtolower(str_replace(' ', '', str_replace('EPON', '', trim($value))))));	
}
function switch_uptime($value){
	$valuetime = '';
	if (strpos($value, 'Timeticks') !== false){
		preg_match('/\((.*?)\)/', $value, $matches);
		if(isset($matches[1]))
			$valuetime = " ".formatUptime($matches[1]);
	}
	elseif(preg_match('/^\d+$/', $value)) {
		$valuetime = formatUptime($value);
	}
	elseif(preg_match('/^\(\d+\)/', $value, $matches)) {
		$value = preg_replace('/[^\d]/', '', $matches[0]);
		$valuetime = formatUptime($value);
	}
	else {
		preg_match('/\((.*?)\)/', $value, $matches);
		if(isset($matches[1]))
			$valuetime = " ".formatUptime($matches[1]);
	}
	return $valuetime; 	
}
function huaweiS2326TP($data){
	if (preg_match("/GigabitEthernet/i",$data['nameport'])){
		$name = (int)str_replace('GigabitEthernet 0/0/','',$data['nameport']);
		$name_id = 24 + $name;
		return '<a href="#" '.($data['operstatus']=='up'?'onclick="showtraffic('.$data['switchid'].','.$data['llid'].')"':'').'><div class="nameportswitch sw_img_port'.$name_id.' '.($data['operstatus']=='up'?'actives':'').'">'.$name_id.'</div></a><a href=""><div class="nameportswitch sw_img_port'.$name_id.'i '.($data['operstatus']=='up'?'actives':'').'">'.$name_id.'</div></a>';
	} elseif(preg_match("/Ethernet/i",$data['nameport'])) {
		$name_id = str_replace('Ethernet 0/0/','',$data['nameport']);
		return '<a href="#" '.($data['operstatus']=='up'?'onclick="showtraffic('.$data['switchid'].','.$data['llid'].')"':'').'><div class="nameportswitch sw_img_port'.$name_id.' '.($data['operstatus']=='up'?'actives':'').'">'.$name_id.'</div></a>';
	}
}
function valueStringSnmp($value) {
	if (strpos($value, 'STRING') !== false) {
		return str_replace('=','',  str_replace('"','', strtolower(str_replace(' ', '', str_replace('STRING: ', '', trim($value))))));
	}elseif (strpos($value, 'Counter32') !== false) {
		return str_replace('=','',  str_replace('"','', strtolower(str_replace(' ', '', str_replace('Counter32: ', '', trim($value))))));
	}elseif (strpos($value, 'Gauge32') !== false) {
		return str_replace('=','',  str_replace('"','', strtolower(str_replace(' ', '', str_replace('Gauge32: ', '', trim($value))))));
	}elseif (strpos($value, 'INTEGER') !== false) {
		return str_replace('=','',  str_replace('"','', strtolower(str_replace(' ', '', str_replace('INTEGER: ', '', trim($value))))));
	}else{
		return str_replace('=','',  str_replace('"','', strtolower(str_replace(' ', '', str_replace('EPON', '', trim($value))))));
	}
}
function valueStringSnmp_($value) {
	if (strpos($value, 'STRING') !== false) {
		return str_replace('=','',  str_replace('"','', str_replace(':', '', str_replace('STRING: ', '', trim($value)))));
	}elseif (strpos($value, 'Counter32') !== false) {
		return str_replace('=','',  str_replace('"','', str_replace(':', '', str_replace('Counter32: ', '', trim($value)))));
	}elseif (strpos($value, 'Gauge32') !== false) {
		return str_replace('=','',  str_replace('"','', str_replace(':', '', str_replace('Gauge32: ', '', trim($value)))));
	}elseif (strpos($value, 'INTEGER') !== false) {
		return str_replace('=','',  str_replace('"','', str_replace(':', '', str_replace('INTEGER: ', '', trim($value)))));
	}else{
		return str_replace('=','',  str_replace('"','', str_replace(':', '', str_replace('EPON', '', trim($value)))));
	}
}
function processPonData($data) {
    $result = [];
	if(is_array($data)){
		foreach ($data as $entry) {
			$pon = strtolower(str_replace(['EPON','GPON', ' ', 'olt-', '"', '-'], '',$entry['pon']));
			if (strlen($pon) > 4) {
				#preg_match('/^(EPON|GPON|XPON)\s+(\d+)\/(\d+)\/?(\d*)/', $entry['pon'], $matches);
				$type_pon = str_replace('olt-','', $entry['pon']);
				preg_match('/^(EPON|GPON)\s+(\d+)\/(\d+)\/?(\d*)/', $type_pon, $matches);
				$portType = strtolower($matches[1]);
				$slot1 = intval($matches[2]);
				$slot2 = intval($matches[3]);
				$slot3 = isset($matches[4]) ? intval($matches[4]) : null;
				if (!isset($result['platu'][$portType])) {
					$result['platu'][$portType] = [];
				}
				if (!isset($result['platu'][$portType][$slot1])) {
					$result['platu'][$portType][$slot1] = [];
				}
				if (!isset($result['platu'][$portType][$slot1][$slot2])) {
					$result['platu'][$portType][$slot1][$slot2] = [];
				}
				if ($slot3 !== null) {
					$ponName = str_replace(['olt-', '"', '-'], '',$entry['pon']);
					$result['platu'][$portType][$slot1][$slot2][$slot3]['pon'] = $ponName;
					$result['platu'][$portType][$slot1][$slot2][$slot3]['id'] = $entry['id'];
					$result['platu'][$portType][$slot1][$slot2][$slot3]['support'] = $entry['support'];
					$result['platu'][$portType][$slot1][$slot2][$slot3]['count'] = $entry['count'];
					$result['platu'][$portType][$slot1][$slot2][$slot3]['sfpid'] = $entry['sfpid'];
				} else {
					$result['platu'][$portType][$slot1][$slot2][] = $entry['id'];
				}
			}else{
				preg_match('/^(EPON|GPON|XPON)\s+(\d+)\/(\d+)/', $entry['pon'], $matches);
				$portType = strtolower($matches[1]);
				$slot1 = intval($matches[2]);
				$sfpid = $entry['sfpid'];
				if (!isset($result['port'][$portType][$slot1])) {
					$result['port'][$portType][$slot1] = [];
				}
				$result['port'][$portType][$slot1][] = $sfpid;
			}
		}
	}
    return $result;
}
function delete_kabel($id){
	global $db;
	if($id>0){
		$db->SQLdelete('kabel',['id' => $id]);
		$db->SQLdelete('kabel_module',['kabelidid' => $id]);
		$db->SQLdelete('kabel_volokno',['kabelidid' => $id]);
		return true;
	}else{
		return false;
	}
}
function statsurl($url,$count,$name,$subname='',$countcss=''){
	return'<a href="'.$url.'" class="knopka"><span class="font_stats '.$countcss.'">'.$count.'</span><span class="text_stats">'.$name.'<span>'.$subname.'</span></span></a>';			
}
function critical_signal($text){
    $text = strtolower(str_replace([':', ',', '"'], '', trim($text)));
    $pattern = '/-3[0-9]+/';
    if (preg_match($pattern, $text)) {
        return true;
    } else {
        return false;
    }
}
function getMap(){
	global $config, $confPMon;
	$map = (!empty($config['typemap'])?$config['typemap']:"google");
	$visicomkey = (!empty($confPMon['VISICOM_API_KEY']) ? $confPMon['VISICOM_API_KEY'] : "000000000000000000");
	switch($map){
		case 'openstreetmap':
			return "var tile = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}); tile.addTo(map);\n";
		case 'openstreetmap_fr':
			return "var tile = L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',{maxZoom:20}); tile.addTo(map);\n";
		case 'carto_light':
			return "var tile = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',{maxZoom:19}); tile.addTo(map);\n";
		case 'stamen_terrain':
			return "var tile = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg',{maxZoom:18, subdomains:'abcd'}); tile.addTo(map);\n";
		case 'esri_world':
			return "var tile = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{maxZoom:19}); tile.addTo(map);\n";
		case 'vision':
			return "var tile = L.tileLayer('https://{s}.visicom.ua/2.0.0/planet3/base/{z}/{x}/{y}.png?key=".$visicomkey."',{
				subdomains:['tms0','tms1','tms2','tms3'],maxZoom:19,tms:true}); tile.addTo(map);\n";
		case 'google':
			return "var tile = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
				maxZoom:19,subdomains:['mt0','mt1','mt2','mt3']}); tile.addTo(map);\n";
		default:
			return "console.error('unknown map type=".$config['typemap']."');";
	}
}
function get_heght($signal,$maxvalue){
	$heigh = 0;
	$heigh = ($signal / $maxvalue) * 100;
	if ($heigh < 6) {
		$heigh = 6;
	} else {
		$heigh = $heigh;
	}
	return intval($heigh);
}
function main_color($percent_equivalent=''){
	if($percent_equivalent){
		$rounded_percent = round($percent_equivalent / 10) * 10;
		switch ($rounded_percent) {
			case 0:
			case 10:
			case 20:
				$result = 'col-blue';
				break;
			case 30:
			case 40:
				$result = 'col-green';
				break;
			case 50:
				$result = 'col-orange';
				break;
			case 60:
			case 70:
			case 80:
				$result = 'col-red';
				break;
			case 90:
			case 100:
				$result = 'col-red';
				break;
			default:
				$result = '';
				break;
		}
		return $result;
	}
}
function csfp(?string $powerblock): ?string {
    if ($powerblock === null) {
        return null;
    }
    if (strpos($powerblock, '214748') !== false || strpos($powerblock, '6553') !== false) {
        return null;
    }
    $powerblock = strtolower($powerblock);
    return preg_replace('/(integer:|string:|[\s"-])/i', '', $powerblock);
}
function smartfiber(?string $powerblock): ?string {
	if ($powerblock !== null) {
		return strtolower(str_replace(['INTEGER:','STRING:', ' ', '"'], '',$powerblock));
	} else {
		return null;
	}
}
function formatUptime($ticks) {
	global $lang;
	if (!is_numeric($ticks)) {
		if (is_string($ticks)) {
			// SNMP Timeticks can arrive as strings like:
			// "Timeticks: (123456) 14 days, 6:56:00.00"
			if (preg_match('/\((\d+)\)/', $ticks, $m)) {
				$ticks = (float)$m[1];
			} else {
				$cleanTicks = preg_replace('/[^0-9.\-]/', '', $ticks);
				$ticks = is_numeric($cleanTicks) ? (float)$cleanTicks : 0.0;
			}
		} else {
			$ticks = 0.0;
		}
	}
	$total_seconds = max(0, (float)$ticks) / 100;
    $days = floor($total_seconds / (3600 * 24));
    $seconds_left = $total_seconds - ($days * 3600 * 24);
    $hours = floor($seconds_left / 3600);
    $seconds_left = $seconds_left - ($hours * 3600);
    $minutes = floor($seconds_left / 60);
    return "$days ".$lang['day']." $hours ".$lang['god']." $minutes ".$lang['min']."";
}
function parse_ip_port($address) {
	$port = 22; 
	if (strpos($address, ":") !== false) {
		list($ip, $port) = explode(":", $address);
	} else {
		$ip = $address;
	}
	return array('ip' => $ip, 'port' => $port);
}
function checkWhenAdded($data) {
    $onudata = date_create_from_format('Y-m-d H:i:s', $data);
    $now = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
    if ($onudata->format('Y-m-d') === $now->format('Y-m-d')) {
        return 'today';
    }
    return '';
}
function checkRXDay($data) {
    $onudata = date_create_from_format('Y-m-d H:i:s', $data);
    $now = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
    if ($onudata->format('Y-m-d') === $now->format('Y-m-d')) {
        return 'today';
    }
    return '';
}
function HuaweiReasonGpon($data) {
	$errors = [
		'1' => 'err6',   // LOS
		'2' => 'err36',  // LOSi(Loss of signal for ONUi) or LOBi (Loss of burst for ONUi)
		'3' => 'err37',  // LOFI(Loss of frame of ONUi)
		'4' => 'err38',  // SFI(Signal fail of ONUi)
		'5' => 'err39',  // LOAI(Loss of acknowledge with ONUi)
		'6' => 'err40',  // LOAMI(Loss of PLOAM for ONUi)
		'7' => 'err41',  // deactive ONT fails
		'8' => 'err42',  // deactive ONT success
		'9' => 'err43',  // reset ONT
		'10' => 'err44', // re-register ONT
		'11' => 'err45', // pop up fail
		'13' => 'err1',
		'255' => 'err1',
		'15' => 'err46', // LOKI(Loss of key synch with ONUi)
		'18' => 'err47', // deactived ONT due to the ring
		'30' => 'err48', // shut down ONT optical module
		'31' => 'err49', // reset ONT by ONT command
		'32' => 'err50', // reset ONT by ONT reset button
		'33' => 'err51', // reset ONT by ONT software
		'34' => 'err52', // deactived ONT due to broadcast attack
		'35' => 'err53', // operator check fail
		'37' => 'err54', // a rogue ONT detected by itself
		'-1' => 'err6'
	];
	return $errors[$data] ?? 'err20';
}
function GCOM_client_mac($netip,$snmpro,$port,$onu) {
	$listmac = '';
    if ($netip && $snmpro) {
		$oid = '1.3.6.1.4.1.8888.1.13.3.16.1.5.0.'.$port.'.'.$onu;
        $getmaconu = @snmp2_real_walk($netip, $snmpro,$oid);
        if ($getmaconu) {
            foreach ($getmaconu as $key => $value) {
                $listmac .=  '<span class="mac_na_onu">'.ClearDataMac($value).'</span>';
            }
        }
    }
    return $listmac ?: null;	
}
function typeOnuzteVideoPort($typetv){
	$type = preg_replace('/^.*? = /i', '', $typetv);
	$type = preg_replace(['/INTEGER: 1/', '/INTEGER: 2/', '/INTEGER: 65535/'], ['1', '2', '65535'], $type);
	$type = preg_replace('/No Such Instance currently exists at this OID/', '0', $type);
	switch ($type){
		case 1: 
			return ['img' => 'tv', 'st' => 'up'];
		case 2: 
			return ['img' => 'tv', 'st' => 'disable'];
		case 65535: 
			return ['img' => 'tv', 'st' => 'down'];
		default:
			return ['img' => 'tv', 'st' => 'down'];
	}
}
function typeOnuBdcomVideoPort($typetv){
	$type = preg_replace('/^.*? = /i', '', $typetv);
	$type = preg_replace(['/INTEGER: 1/', '/INTEGER: 2/', '/INTEGER: 65535/'], ['1', '2', '65535'], $type);
	$type = preg_replace('/No Such Instance currently exists at this OID/', '0', $type);
	switch ($type){
		case 1: 
			return ['img' => 'tv', 'st' => 'up'];
		case 2: 
			return ['img' => 'tv', 'st' => 'disable'];
		case 65535: 
			return ['img' => 'tv', 'st' => 'down'];
		default:
			return false;
	}
}
function typeOnuztePort($snmptype) {
	$snmptype = preg_replace('/^.*? = /i', '', $snmptype);
	$snmptype = preg_replace(['/INTEGER: 1/','/INTEGER: 5/', '/INTEGER: 6$/', '/INTEGER: 65535/', '/No Such Instance currently exists at this OID/'], ['1', '5', '6', '65535', '0'], $snmptype);
	if ($snmptype == 6) {
		return ['img' => 'zte6.png', 'txt' => '1 Gbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 3) {
		return ['img' => 'zte3.png', 'txt' => '10 Mbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 5) {
		return ['img' => 'zte5.png', 'txt' => '100 Mbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 1) {
		return ['img' => 'zte0.png', 'txt' => 'Offline', 'st' => 'down', 'status' => 'down'];
	} elseif ($snmptype == 0) {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'disable', 'status' => 'disable'];
	} else {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'disable', 'status' => 'disable'];
	}
}
function typeOnubdcomPort($snmptype) {
	$snmptype = preg_replace('/^.*? = /i', '', $snmptype);
	$snmptype = preg_replace(['/INTEGER: 1/','/INTEGER: 2/', '/INTEGER: 3$/', '/INTEGER: 65535/', '/No Such Instance currently exists at this OID/'], ['1', '2', '3', '65535', '0'], $snmptype);
	if ($snmptype == 1) {
		return ['img' => 'zte5.png', 'txt' => '100 Mbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 2) {
		return ['img' => 'zte0.png', 'txt' => 'Offline', 'st' => 'down', 'status' => 'down'];
	} elseif ($snmptype == 0) {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'disable', 'status' => 'disable'];
	} else {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'disable', 'status' => 'disable'];
	}
}
function typeOnuzte2Port($snmptype) {
	$snmptype = preg_replace('/^.*? = /i', '', $snmptype);
	$snmptype = preg_replace(['/INTEGER: 1/','/INTEGER: 2/','/INTEGER: 3/','/INTEGER: 5/', '/INTEGER: 6$/', '/INTEGER: 65535/', '/No Such Instance currently exists at this OID/'], ['1', '2', '3', '5', '6', '65535', '0'], $snmptype);
	if ($snmptype == 6) {
		return ['img' => 'zte3.png', 'txt' => '10 Mbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 2) {
		return ['img' => 'zte5.png', 'txt' => '100 Mbps', 'st' => 'enable', 'status' => 'up'];	
	} elseif ($snmptype == 3) {
		return ['img' => 'zte6.png', 'txt' => '1 Gbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 5) {
		return ['img' => 'zte1.png', 'txt' => 'Auto', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 1) {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'disable', 'status' => 'disable'];
	} elseif ($snmptype == 0) {
		return ['img' => 'zte0.png', 'txt' => 'Offline', 'st' => 'down', 'status' => 'down'];
	} else {
		return ['img' => 'zte0.png', 'txt' => 'Offline', 'st' => 'down', 'status' => 'down'];
	}
}
function typeOnuzte6Port($snmptype) {
	$snmptype = preg_replace('/^.*? = /i', '', $snmptype);
	$snmptype = preg_replace(['/INTEGER: 1/','/INTEGER: 5/', '/INTEGER: 6$/', '/INTEGER: 65535/', '/No Such Instance currently exists at this OID/'], ['1', '5', '6', '65535', '0'], $snmptype);
	if ($snmptype == 6) {
		return ['img' => 'zte6.png', 'txt' => '1 Gbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 3) {
		return ['img' => 'zte3.png', 'txt' => '10 Mbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 5) {
		return ['img' => 'zte5.png', 'txt' => '100 Mbps', 'st' => 'enable', 'status' => 'up'];
	} elseif ($snmptype == 1) {
		return ['img' => 'zte0.png', 'txt' => 'Down', 'st' => 'disable', 'status' => 'disable'];
	} elseif ($snmptype == 0) {
		return ['img' => 'zte0.png', 'txt' => 'Offline', 'st' => 'down', 'status' => 'down'];
	} else {
		return ['img' => '', 'txt' => '', 'st' => '', 'status' => ''];
	}
}
function telegram_sms($type) {
    global $config;
    if ($config['telegram'] == 'on' && $type) {
        $url = 'https://api.telegram.org/bot' . $config['telegramtoken'] . '/sendmessage';
        $data = array(
            'chat_id' => $config['telegramchatid'],
            'text' => $type,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
function blockStatsONU($olt){
	global $config, $db, $lang;
	$style ='';
	$getONU = $db->Multi('onus','status',['olt'=>$olt]);
	if(count($getONU)){
		$off = 1; $on = 1;
		$data['count'] = count($getONU);
		foreach($getONU as $value){
			if($value['status']==1){
				$data['online'] = $on;
				$on ++;
			}			
			if($value['status']==2){
				$data['offline'] = $off;
				$off ++;	
			}
		}
	$widht1 = (100/(!empty($data['count'])?$data['count']:0))*(!empty($data['online'])?$data['online']:0);
	$widht2 = (100/(!empty($data['count'])?$data['count']:0))*(!empty($data['offline'])?$data['offline']:0);
	$style .='<div class="blockstats">';
	$style .='<div class="bl_online"><header class="col1"><b>'.$lang['online'].'</b><b>'.(!empty($data['online'])?$data['online']:0).'</b></header><div class="bars1"><div class="percent1" style="width:'.$widht1.'%;"></div></div></div>';			
	$style .='<div class="bl_online"><header class="col2"><b>'.$lang['offline'].'</b><b>'.(!empty($data['offline'])?$data['offline']:0).'</b></header><div class="bars1"><div class="percent2" style="width:'.$widht2.'%;"></div></div></div>';
	$style .='</div>';
	}
	return $style;
}
function delete_onu($idonu){
	global $db;
	$getONU = $db->Fast('onus','*',['idonu'=>$idonu]);
	$traff_monitor = $db->Fast('traff_monitor','*',['types'=>'onu','idonu'=>$idonu]);
	if(!empty($getONU['idonu'])){
		$db->query('DELETE FROM onus WHERE idonu = '.$getONU['idonu']);
		$db->query('DELETE FROM onus_comm WHERE idonu = '.$getONU['idonu']);
		$db->query('DELETE FROM historysignal WHERE onu = '.$getONU['idonu']);
		$db->query('DELETE FROM kvarturu WHERE idonu = '.$getONU['idonu']);
		$db->query('DELETE FROM mac_router WHERE onuid = '.$getONU['idonu']);
		$db->query('DELETE FROM onus_equipment WHERE idonu = '.$getONU['idonu']);
		$db->query('DELETE FROM onus_log WHERE idonu = '.$getONU['idonu']);
		$db->query('DELETE FROM onus_monitor WHERE idonu = '.$getONU['idonu']);
		$db->query('DELETE FROM rxolt_signal WHERE onu = '.$getONU['idonu']);
		if(!empty($traff_monitor['id'])){
			$db->query('DELETE FROM traff_monitor WHERE types = "onu" AND idonu = '.$getONU['idonu']);
			$db->query('DELETE FROM bandwidth_daily WHERE portid = '.$traff_monitor['id']);
		}
	}
}
function is_array_empty_masiv($array) {
    if(is_array($array) && count(array_filter($array))){
		return true;
	}else{
		return false;
	}
}
function ListSwitchMonitor(){
	global $db;
	$data = array();
	$sql_data = ['sql' => "SELECT * FROM switch WHERE monitor = 'yes'",'key' => 'sql_list_switch','type' => 'while','time' => 600];
	$sqldevice = cache_simple_sql($sql_data);
	if(isset($sqldevice) && count($sqldevice)>0){
		foreach ($sqldevice as $value) {
			$data[$value['id']] = [
				'id' => $value['id'],
				'place' => $value['place'],
				'netip' => $value['netip'],
				'location' => $value['location'],
				'updates' => $value['updates'],
				'model' => $value['inf'] . ' ' . $value['model'],
			];
		}
	}
	return $data;
}
function highlight_word($title,$searched_word) {
    return str_replace($searched_word,'<font color=red>'.$searched_word.'</font>',$title); // replace content
}
function MapRxTerminal($signal){
	global $config;
	if(isset($signal)){
		$signalbadstart = (!empty( $config['badsignalstart']) ? $config['badsignalstart'] : 26);
		$signalbadend = (!empty( $config['badsignalend']) ? $config['badsignalend'] : 39);
		$signala = (int)str_replace('-', '',$signal);
		if($signala>=1 AND $signala<=12 ){		
			return '<span class="map-signal0">'.$signal.'</span>';	
		}elseif($signala>=13 AND $signala<=19 ){		
			return '<span class="map-signal2">'.$signal.'</span>';	
		}elseif($signala>=20 AND $signala<=($signalbadstart-1)){		
			return '<span class="map-signal3">'.$signal.'</span>';	
		}elseif($signala>=$signalbadstart AND $signala<=$signalbadend){		
			return '<span class="map-signal4">'.$signal.'</span>';	
		}else{		
			if($signala){
				return '<span class="map-signal4">'.sprintf("%.2f",$signal).'</span>';
			}else{
				return'<span class="map-signal7">N/A</span>';
			}
		}
	}
}
function styleRxMap($rx){
    $signal = $rx;
    if (!$signal || $signal == '-70') {
        return '';
    }
    $signala = (int)str_replace('-', '', $signal);
    switch (true) {
        case ($signala > 1 && $signala <= 12):
            $result='<span class=rx0>'.$rx.'</span>';
            break;
        case ($signala >= 13 && $signala <= 17):
            $result='<span class=rx1>'.$rx.'</span>';
            break;
        case ($signala >= 18 && $signala <= 24):
            $result='<span class=rx2>'.$rx.'</span>';
            break;
        case ($signala >= 25 && $signala <= 29):
            $result='<span class=rx4>'.$rx.'</span>';
            break;
        case ($signala >= 30 && $signala <= 70):
            $result='<span class=rx5>'.$rx.'</span>';
            break;
        default:
            $result = '';
            break;
    }
    return $result;
}
function infdisplay($text) {
	return '<span class="inf_display"><i class="fi fi-rr-comment-info"></i>'.($text?$text:'empty_err').'</span>';
}
function aftertime_cut($start) {
    global $lang;
    if(isset($start) && !preg_match("/0000/i", $start)) {
        $diff = time() - strtotime($start);
        $months = floor($diff / (30 * 24 * 60 * 60));
        $diff -= $months * (30 * 24 * 60 * 60);
        $days = floor($diff / (24 * 60 * 60));
        $diff -= $days * (24 * 60 * 60);
        $hours = floor($diff / (60 * 60));
        $diff -= $hours * (60 * 60);
        $minutes = floor($diff / 60);
        
        if ($months > 0) {
            return $months . ' '.$lang['mouth'].' ' . $days . ' '.$lang['day'];
        } elseif ($days > 0) {
            return $days . ' '.$lang['day'].' ' . $hours . ' '.$lang['god'];
        } elseif ($hours > 0) {
            return $hours . ' '.$lang['god'].' ' . $minutes . ' '.$lang['min'];
        } else {
            return $minutes . ' '.$lang['min'];
        }
    } else {
        return '---';
    }
}
function aftertime($start) {
    global $lang;
    if (empty($start) || preg_match('/0000/i', $start)) {
        return '---';
    }
    $ts = strtotime($start);
    if ($ts === false) {
        return '---';
    }
    $diff = time() - $ts;
    if ($diff < 0) {
        $diff = 0; // на всяк випадок
    }
    if ($diff < 60) {
        return $diff . ' сек';
    }
    $SEC_PER_MIN = 60;
    $SEC_PER_HOUR = 3600;
    $SEC_PER_DAY = 86400;
    $SEC_PER_MONTH = 2592000; // 30 * 86400
    $months = (int) floor($diff / $SEC_PER_MONTH);
    $diff -= $months * $SEC_PER_MONTH;
    $days = (int) floor($diff / $SEC_PER_DAY);
    $diff -= $days * $SEC_PER_DAY;
    $hours = (int) floor($diff / $SEC_PER_HOUR);
    $diff -= $hours * $SEC_PER_HOUR;
    $mins = (int) floor($diff / $SEC_PER_MIN);
    if ($months > 0) {
        $out = [];
        $out[] = $months . ' ' . ($lang['mouth'] ?? 'month');
        if ($days > 0) {
            $out[] = $days . ' ' . ($lang['day'] ?? 'day');
        }
        return implode(' ', $out);
    }
    $out = [];
    if ($days > 0) {
        $out[] = $days . ' ' . ($lang['day'] ?? 'day');
    }
    if ($hours > 0) {
        $out[] = $hours . ' ' . ($lang['god'] ?? 'hour');
    }
    if ($mins > 0) {
        $out[] = $mins . ' ' . ($lang['min'] ?? 'min');
    }
    return $out ? implode(' ', $out) : '0 ' . ($lang['min'] ?? 'min');
}
function infstatus($status, $count=null){
	global $confPMon;
	if (isset($confPMon['CHECK_SNMP']) && !empty($confPMon['CHECK_SNMP']) && $confPMon['CHECK_SNMP'] == 1) {
		if($status=='yes' && $count){
			return '<div class="online_green speed1"></div>';
		}elseif($status=='yes' && !$count){
			return '<div class="online_green"></div>';		
		}elseif($status=='no'){
			return '<div class="online_red"></div>';		
		}else{
			return '<div class="online_red"></div>';		
		}
	}
}
function getClassUser($class){
	global $lang;
	$classNames = [
		7 => $lang['class7'],6 => $lang['class6'],5 => $lang['class5'],4 => $lang['class4'],3 => $lang['class3'],2 => $lang['class2'],1 => $lang['class1']
	];
	if (isset($classNames[$class])) {
		return $classNames[$class];
	}
	return false;
}
function devLocation($object){
	if(!empty($object['type'])){
		$tplStyle ='<div class="object">';
		switch($object['type']){
			case 'switch':
				$tplStyle .='<div class="img"><img src="../style/device/'.$object['img'].'"></div>';
				$tplStyle .='<a href="/?do=detail&act='.$object['type'].'&id='.$object['id'].'">'.$object['name'].'</a>';
				$tplStyle .='<h3>'.$object['model'].'</h3>';
			break;			
			case 'olt':
				$tplStyle .='<div class="img"><img src="../style/device/'.$object['img'].'"></div>';
				$tplStyle .='<a href="/?do=detail&act='.$object['type'].'&id='.$object['id'].'">'.$object['name'].'</a>';
				$tplStyle .='<h3>'.$object['model'].'</h3>';
			break;		
			case 'unit':
				$tplStyle .='<div class="img"><img src="../style/img/box.png"></div>';
				$tplStyle .='<a href="/">'.$object['name'].'</a>';
				$tplStyle .='<h3>'.$object['model'].'</h3>';
			break;
		}
		$tplStyle .='</div>';
	}
	return $tplStyle;
}
function devPing3($object){
	if(!empty($object['sw_type'])){
		$tplStyle ='<div class="object">';
		switch($object['sw_type']){
			case 'olt':
				$tplStyle .='<div class="img"><img src="../style/device/'.$object['sw_img'].'"></div>';
				$tplStyle .='<a href="/?do=detail&act='.$object['sw_type'].'&id='.$object['sw_id'].'">'.$object['sw_place'].'</a>';
				$tplStyle .='<h3>'.$object['sw_model'].''.$object['sw_inf'].'</h3>';
			break;		
			case 'unit':
				$tplStyle .='<div class="img"><img src="../style/img/box.png"></div>';
				$tplStyle .='<a href="/">'.$object['sw_place'].'</a>';
				$tplStyle .='<h3>'.$object['model'].'</h3>';
			break;
		}
		$tplStyle .='</div>';
	}
	return $tplStyle;
}
function getAllDeviceLocation(string $location): array {
    global $db;
    $dataSwitch = $db->Multi('switch', '*', ['location' => $location]);
    $arrayswitch = [];
    foreach ($dataSwitch as $id => $value) {
		$arrayswitch[$id] = [
			'id' => $value['id'],
			'name' => $value['place'],
			'type' => $value['device'],
			'img' => $value['img'],
			'model' => $value['inf'] . '' . $value['model'],
		];
    }
    $arrayun = [];
    if (isset($arrayswitch) && count($arrayun) > 0) {
        $arraydata = array_merge($arrayswitch, $arrayun);
    } elseif (count($arrayswitch) > 0 && !count($arrayun)) {
        $arraydata = $arrayswitch;
    } else {
        $arraydata = $arrayun;
    }
    return $arraydata;
}
function checkAccess($class){
	global $USER;
	if(!$USER['class']){
		return false;
	}else
	if(!empty($USER['class']) && $USER['class']>=$class){
		return true;
	}else{ 
		return false;
	}
}
function getListSFParray(){
	global $db;
	$dataArraySfp = $db->Multi('sfp');
	if(count($dataArraySfp)){
		$arraSfp = array();
		foreach($dataArraySfp as $id => $value){
			$arraSfp[$id]['id'] = $value['id'];
			$arraSfp[$id]['types'] = $value['types'];
			$arraSfp[$id]['wavelength'] = $value['wavelength'];
			$arraSfp[$id]['connector'] = $value['connector'];
			$arraSfp[$id]['dist'] = $value['dist'];
			$arraSfp[$id]['speed'] = $value['speed'];
		}
	}else{
		$arraSfp = null;
	}	
	return $arraSfp;	
}
function tplreason($title,$css){
	return'<span class="css_'.$css.'">'.$title.'</span>';
}
function geterrorPonTpl($llid,$deviceid){
	global $db;
	$dataLastPortError = $db->Simple("SELECT * FROM `switch_port_err` WHERE `llid` = '".(int)$llid."' AND `deviceid` = '".(int)$deviceid."' ORDER BY `added` DESC LIMIT 1");
	if(!empty($dataLastPortError['inerror'])){
		$tpl .= '<div class="porterrblock">';
		$tpl .= '<div class="info"><h2>IfInErrors</h2><span>'.$dataLastPortError['added'].'</span></div>';				
		if($dataLastPortError['status_inerror']=='up'){
			$tpl .= '<div class="icoup"><span><i class="fi fi-rr-angle-small-up"></i></span></div>';
		}elseif($dataLastPortError['status_inerror']=='down'){
			$tpl .= '<div class="icoup"><span><i class="fi fi-rr-angle-small-up"></i></span></div>';
		}else{
			$tpl .= '<div class="iconone"><span><i class="fi fi-br-minus"></i></span></div>';	
		}
		$tpl .= '<div class="errcount">'.$dataLastPortError['inerror'].($dataLastPortError['newin'] && $dataLastPortError['newin']!==$dataLastPortError['inerror']?'<b>+'.$dataLastPortError['newin'].'</b>':'').'</div>';
		$tpl .= '</div>';
	}
	return $tpl;
}
function getlistPonTpl($data){
	global $db;
	$tpl = '';
	$SQLport = $db->Multi('switch_pon','*',['oltid'=>$data['deviceid']],['sort'=>'asc']);
	if(count($SQLport)){
		foreach($SQLport as $Port){
			$css_load_bar = loadbarpon($Port['support'],$Port['count']);
			$tpl .= '<li '.(!empty($data['ponid']) && $data['ponid']==$Port['id']?'class="active"':'').'>'.(!empty($data['ponid']) && $data['ponid']==$Port['id'] && $Port['count']>10?'':'').'';//<span class="connect_bd"></span>
			$tpl .= '<div class="elem nam"><a href="/?do=terminal&id='.$data['deviceid'].'&port='.$Port['id'].'">'.$Port['pon'].'';
			$SQLgetPort = $db->Fast('switch_port','*',['deviceid'=>$Port['oltid'],'llid'=>$Port['sfpid']]);
			if(!empty($Port['support'])){
				$tpl .= '<span class="subinf">';
				if(!empty($Port['online']))
					$tpl .= '<span class="pon_online">'.$Port['online'].'</span>';
				if(!empty($Port['offline']))
					$tpl .= '<span class="pon_offline">'.$Port['offline'].'</span>';
				$tpl .= '<span class="pon_real">'.$Port['count'].'</span>';
				$tpl .= '<span class="pon_support">'.$Port['support'].'</span>';
				$tpl .= '</span>';
				if(!empty($SQLgetPort['descrport']))
					$tpl .= '<span class="element1">'.$SQLgetPort['descrport'].'</span>';
			}
			$tpl .= '</a><div class="loadli"><div class="bdload '.$css_load_bar['css'].'" style="width:'.$css_load_bar['width'].'%;"></div></div></div></li>';
		}
	}
	return $tpl;
}
function cl_snmp($inface){
	$inface = str_replace('"','',$inface);
	$inface = str_replace("'",'',$inface);
	$inface = str_replace(":",'',$inface);
	return trim($inface);
}
function huawei_linktype_onu_img($value) {
    $ont_speed_eth = cl_snmp($value);
    switch ($ont_speed_eth) {
        case '1':
        case '5':
            return 3;
        case '2':
        case '6':
            return 5;
        case '3':
        case '7':
        case '8':
        case '9':
        case '10':
        case '11':
            return 6;
        case '4':
            return 1;
        case '-1':
            return 0;
        default:
            return 0; 
    }
}
function huawei_linktype_onu($value){
	$ont_speed_eth = cl_snmp($value);
	switch ($ont_speed_eth) {
		case '1':
		return '10Mb manual';
			break;
		case '2':
		return '100Mb manual';
			break;
		case '3':
		return '1Gb manual<br>';
			break;
		case '4':
		return 'Auto';
			break;
		case '5':
		return '10Mb Auto';
			break;
		case '6':
		return '100Mb Auto';
			break;
		case '7':
		return '1Gb Auto';
			break;
		case '8':
		return '10Gb manual';
			break;
		case '9':
		return '10Gb Auto';
			break;
		case '10':
		return '2.5Gb manual';
			break;
		case '11':
		return '2.5Gb Auto';
			break;
		case '-1':
		return 'invalid';
		break;
	}
}
function huawei_linktype($value){
    $result_sp = cl_snmp($value);
    switch ($result_sp) {
        case 2:
        case 6:
            $value = 100;
            break;
        case 7:
        case 3:
        case 4:
            $value = 1000;
            break;
        default:
            $value = 10;
            break;
    }
    switch ($value) {
        case 1000:
            $data = '1Gb';
            break;
        case 10:
            $data = '10Mb';
            break;
        case 100:
            $data = '100Mb';
            break;
        case 10000:
            $data = '10Gb';
            break;
        default:
            $data = 'N/A';
            break;
    }
    return $data;
}
function cl_inface($inface){
	if($inface){
	$inface = str_replace('"','',$inface);
	$inface = trim($inface);
	preg_match('/(.*?):/',$inface,$inf);
	if(!empty($inf[0]))
		return str_replace(':','',$inf[0]);
	else return '';
	} else return '';
}
function sql($value, $force = false) {
    if (is_numeric($value) && !$force) {
        return $value;
    }
    $value = str_replace(
        ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"],
        ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
        $value
    );
    return "'" . $value . "'";
}
function ont_label($name, $data, $img = '') {
    $i = '';
    if (!empty($img)) {
        $i = '<img src="../style/img/' . $img . '" alt="icon">';
    }
    return '
    <div class="ont-label">        
        <div class="name-label">' . $i . '' . $name . '</div>
        <div class="data-label">' . $data . '</div>
    </div>';
}
function loadbarpon($portonu,$portcountonu){
	$portonu = (isset($portonu) && $portonu == 64 ? 64 : 128);
	$data = array();
	$width = (100/$portonu)*$portcountonu;
	$count = ceil($width);
	if($count>=1 AND $count<=49){
		$styleport='load0';	
	}elseif($count>=50 AND $count<=60){
		$styleport='load1';	
	}elseif($count>=61 AND $count<=70){
		$styleport='load2';	
	}elseif($count>=71 AND $count<=80){
		$styleport='load3';	
	}elseif($count>=81 AND $count<=90){
		$styleport='load4';	
	}elseif($count>=91 AND $count<=99){
		$styleport='load5';	
	}else{
		$styleport='full';
	}
	$data['css'] = $styleport;
	$data['width'] = $width;
	return $data;
}
function nameport_pon2($string) {
	$string = mb_strtolower($string);
	$result = preg_replace("/[^a-zа-я\s]/iu","",$string);
	#$result = preg_replace("/[^a-zа-я1-9\s]/iu","",$string);
	return str_replace(" ","",$result);
}
function nameport_pon($value) {
	$value = mb_strtolower($value);
	if(preg_match('/xgei/i',$value)){
		return 'sfp'; // xgei (интерфейс 10G Ethernet).
	}elseif(preg_match('/xge/i',$value)){
		return 'xge'; // xgei (интерфейс 10G Ethernet).
	}elseif(preg_match('/ge/i',$value)){
		return 'ge'; // xgei (интерфейс 10G Ethernet).
	}elseif(preg_match('/gei/i',$value)){
		return 'eth1000'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/gpon/i',$value)){
		return 'gpon';
	}elseif(preg_match('/epon/i',$value)){
		return 'epon';
	}elseif(preg_match('/rxolt/i',$value)){
		return '';	
	}elseif(preg_match('/tgigaethernet/i',$value)){
		return 'sfp'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/gigaethernet/i',$value)){
		return 'sfp'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/fastethernet/i',$value)){
		return 'eth100'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/Mng1/i',$value)){
		return 'mng1'; // gei (интерфейс 1000M Ethernet)	
	}elseif(preg_match('/ethernet/i',$value)){
		return 'ethernet';	
	}else{
		return 'port';
	}
}
function idport_switch($string) {
	preg_match('/0\/(\d+)/',$string,$match);
	return $match[1];
}
function getAllPortDevice($port) {
    $data = [];
    foreach ($port as $p) {
        $nameport = nameport_pon($p['nameport']);
        $idport = $p['llid'];        
        $data[$nameport][$idport] = [
            'name' => $p['nameport'],
            'llid' => $p['llid'],
            'deviceid' => $p['deviceid'],
            'descr' => $p['descrport'],
            'id' => $p['id'],
            'operstatus' => $p['operstatus'],
            'typeport' => $p['typeport'],
            'updates' => $p['updates'],
            'sms' => $p['sms'],
            'error' => $p['error'],
            'monitor' => $p['monitor'],
            'idport' => $idport,
            'lock' => $p['lockport'],
        ];
    }
    return $data;
}
function tplErrorPort($deviceid,$llid){
	global $db;
	$style ='';
	$dataLastPortError = $db->Simple("SELECT * FROM `switch_port_err` WHERE `llid` = '".(int)$llid."' AND `deviceid` = '".(int)$deviceid."' ORDER BY `added` DESC LIMIT 1");	
	if(!empty($dataLastPortError['inerror']))
		$style .='<span class="block_error"><span class="title">IfInErrors:</span><span class="counterr">'.$dataLastPortError['inerror'].($dataLastPortError['newin'] && $dataLastPortError['newin']!==$dataLastPortError['inerror']?'<b>+'.$dataLastPortError['newin'].'</b>':'').'</span></span>';	
	if(!empty($dataLastPortError['outerror']))
		$style .='<span class="block_error"><span class="title">out:</span><span class="counterr">'.$dataLastPortError['outerror'].'</span></span>';
	if(!empty($dataLastPortError['id']))
		return $style;
}
function blockonline($count){
	global $lang;
	$style = '';
	if($count)
		$style = '<div class="listcheck"><div class="onu-online"><h2>'.$lang['blockonline'].'</h2><div class="onu-bar"><div class="onu-load" style="width:'.$count.'%;"></div></div></div></div>';
	return $style;		
}
function blockoffline($count){
	global $lang;
	$style = '';
	if($count)
		$style = '<div class="listcheck"><div class="onu-offline"><h2>'.$lang['blockoffline'].'</h2><div class="onu-bar"><div class="onu-load" style="width:'.$count.'%;"></div></div></div></div>';
	return $style;		
}
function info($title,$descr){
	$style = '<div class="information">';
	$style .= '<h2>'.$title.'</h2>';
	$style .= '<b>'.$descr.'</b>';
	$style .= '</div>';
	return $style;
}
function getPortConnectSFP($deviceid){
	global $db, $lang;
	$SQLSfp = $db->Multi('connect_port','*',['curd'=>$deviceid]);
	if(count($SQLSfp)){
		$result ='';
		$array = array();
		foreach($SQLSfp as $sfp){
			$array[$sfp['id']]['id'] = $sfp['id'];
			$array[$sfp['id']]['types'] = $sfp['types'];
			$array[$sfp['id']]['current']['device_id'] =  $deviceid;
			$array[$sfp['id']]['current']['device_id'] =  $sfp['curp'];
			$array[$sfp['id']]['connect']['device_id'] =  $sfp['connd'];
			$array[$sfp['id']]['connect']['port_id'] =  $sfp['connp'];
			// CONNECT DEVICE: place, port
			$Devcurrent = $db->Simple('SELECT switch.place, switch.id, switch_port.nameport FROM switch, switch_port WHERE switch.id = switch_port.deviceid AND switch_port.id = '.$sfp['curp']);
			if(!empty($Devcurrent['id'])){
				$array[$sfp['id']]['current']['place'] = $Devcurrent['place'];
				$array[$sfp['id']]['current']['port'] = $Devcurrent['nameport'];
			}
			// SFP CURRENT
			$SFPcurrent = $db->Fast('sfp','*',['id'=>$sfp['cursfp']]);
			if(!empty($SFPcurrent['id'])){
				$array[$sfp['id']]['current']['sfp']['id'] = $SFPcurrent['id']; 
				$array[$sfp['id']]['current']['sfp']['wav'] = $SFPcurrent['wavelength']; 
				$array[$sfp['id']]['current']['sfp']['dist'] = $SFPcurrent['dist']; 
			}
			// CONNECT DEVICE: place, port
			$Devconnect = $db->Simple('SELECT switch.place, switch.id, switch_port.nameport FROM switch, switch_port WHERE switch.id = switch_port.deviceid AND switch_port.id = '.$sfp['connp']);
			if(!empty($Devconnect['id'])){
				$array[$sfp['id']]['connect']['place'] = $Devconnect['place'];
				$array[$sfp['id']]['connect']['port'] = $Devconnect['nameport'];
			}
			// SFP CONNECT
			$SFPconnect = $db->Fast('sfp','*',['id'=>$sfp['connsfp']]);
			if(!empty($SFPconnect['id'])){
				$array[$sfp['id']]['connect']['sfp']['id'] = $SFPconnect['id']; 
				$array[$sfp['id']]['connect']['sfp']['wav'] = $SFPconnect['wavelength']; 
				$array[$sfp['id']]['connect']['sfp']['dist'] = $SFPconnect['dist']; 
			}
		}
	}
	if(is_array($array)){
		foreach($array as $community){
			$result .='<div class="connect-port">';
			$result .='<div class="connect-cur"><h2>'.$community['current']['place'].'</h2><span>'.$community['current']['port'].'</span></div>';	
			if(is_array($community['current']['sfp'])){
				$result .='<div class="connect-cur-sfp"><span class="css_sfp"><span class="cursfpkm">'.$community['current']['sfp']['dist'].'km<span></span></span><span class="sfp'.$community['current']['sfp']['wav'].'">'.$community['current']['sfp']['wav'].'</span></span></div>';	
			}
			$result .='<div class="connect-ico"><span><img onclick="ajaxconnect(\'edit\','.$community['id'].');" src="/style/img/'.($community['types']?$community['types']:'sc').'.png"></span></div>';	
			if(is_array($community['connect']['sfp'])){
				$result .='<div class="connect-conn-sfp"><span class="css_sfp"><span class="sfp'.$community['connect']['sfp']['wav'].'">'.$community['connect']['sfp']['wav'].'</span><span class="commsfpkm">'.$community['connect']['sfp']['dist'].'km<span></span></span></span></div>';	
			}
			$result .='<div class="connect-conn"><h2>'.$community['connect']['place'].'</h2><span>'.$community['connect']['port'].'</span></div>';			
			$result .='<a class="connect-url" href="/?do=detail&act=olt&id='.$community['connect']['device_id'].'"><img src="/style/img/hub.png"></a>';	
			$result .='</div>';	
		}
	}else{
		$result .='<div class="empty_connect"><i class="fi fi-rr-comment-info"></i>'.$lang['empty'].'</div>';	
	}
	return $result;
}
function getPortConnect($data,$id){
	global $db;
	$SQLSwitch = $db->Fast('switch','place,netip,id',['id'=>$data[$id]['connectdevice']]);
	if(!empty($SQLSwitch['id'])){
		$style ='<div class="connectswitch">';
		$style .='<img class="sw-icon" src="../style/img/servers.png">';
		$style .='<div class="namedev"><a class="connectnext" href="/?do=detail&act=olt&id='.$data[$id]['connectdevice'].'&page=connect">';
			$style .='<div class="dev">'.$SQLSwitch['place'].'</div>';
			$style .='<div class="port">'.$data[$id]['nameport'].'</div>';
		$style .='</a></div>';
		$style .='</div>';
	}
	return $style;
}
function getConnection($id){
	global $db;
	$array_conn = null;
	$SQLconnect = $db->Multi('connect_port','*',['curd'=>$id]);
	if(count($SQLconnect)){
		$array_conn = array();
		foreach($SQLconnect as $connect){
			$SQLconnectDevice = $db->Fast('switch_port','*',['id'=>$connect['connp']]);
			if(!empty($SQLconnectDevice['id'])){
				$array_conn[$connect['curp']]['id'] = $SQLconnectDevice['id'];
				$array_conn[$connect['curp']]['nameport'] = $SQLconnectDevice['nameport'];
				$array_conn[$connect['curp']]['descrport'] = $SQLconnectDevice['descrport'];
				$array_conn[$connect['curp']]['operstatus'] = $SQLconnectDevice['operstatus'];
				$array_conn[$connect['curp']]['connectdevice'] = $connect['connd'];
				$array_conn[$connect['curp']]['connectport'] = $connect['connp'];
				$array_conn[$connect['curp']]['added'] = $connect['added'];
			}
		}
	}
	return $array_conn;					
}
function getListLocation(){
	global $db;
	$SQLgetListLocation = $db->Multi('location');
	if(count($SQLgetListLocation)){
		$location = array();
		foreach($SQLgetListLocation as $loc){
			$location[$loc['id']]['name'] = $loc['name'];
			if(!empty($loc['lan']))
				$location[$loc['id']]['geo']['lan'] = $loc['lan'];
			if(!empty($loc['lon']))
				$location[$loc['id']]['geo']['lon'] = $loc['lon'];
		}
		return $location;
	}else{
		return false;
	}
}
function getListDevice_($type, $global_key = false) {
    $encrypted = file_get_contents('https://pmon.com.ua/device.php?key='.$global_key);
	if ($encrypted === false) {
        return false;
    }	
    function decryptData($encryptedData, $key) {
        $cipher = "AES-256-CBC";
        $data = base64_decode($encryptedData);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($data, 0, $ivlen);
        $ciphertext = substr($data, $ivlen);
        $original = openssl_decrypt($ciphertext, $cipher, $key, 0, $iv);
        return $original;
    }
    $json = decryptData($encrypted, $global_key);
    if (!$json) {
        return false;
    }
    $data = json_decode($json, true);
    if (!$data || !is_array($data)) {
        return false;
    }
    $result = array_filter($data, function($item) use ($type) {
        if ($item['work'] !== 'yes') {
            return false;
        }
        if ($type && $item['device'] !== $type) {
            return false;
        }
        return true;
    });
    return $result ?: false;
}
function getListGroup(){
	global $db;
	$list = $db->Multi('groups');
	return $list ?: false;
}
function getListLocations(){
	global $db;
	$list = $db->Multi('location');
	return (isset($list) ? $list : false);
}
function form($data = array()){
	$style = '<div class="pole1">';
	$style .= '<div class="form1">'.$data['name'].'';
	if(!empty($data['descr']))
		$style .= '<b>'.$data['descr'].'</b>';
	$style .= '</div>';
	$style .= '<div class="form2">'.$data['pole'].'</div>';
	$style .= '</div>';	
	return $style;
}
function formpage($data = array()){
	$style = '<div class="pole1 '.(isset($data['class']) && !empty($data['class']) ? $data['class'] : "").'">';
	if(!empty($data['img']))
		$style .= '<div class="img"><img src="../style/img/'.$data['img'].'"></div>';
	$style .= '<div class="form1">'.$data['name'].'';
	if(!empty($data['descr']))
		$style .= '<b>'.$data['descr'].'</b>';
	$style .= '</div>';
	if(isset($data['pole']))
		$style .= '<div class="form2">'.$data['pole'].'</div>';
	$style .= '</div>';	
	return $style;
}
function okno_title($title){
	echo'<div class="overlay"><div id="okno"><div id="oknoheader" class="title"><img class="logo-pop" src="../style/pop.png">'.$title.'<span class="close" onclick="oknoclose()"><img style="height: 13px;" src="/style/img/multiply.png"></span></div><div class="result">';
}
function okno_end(){
	echo'</div></div></div><script>dragElement(document.getElementById("okno"));</script>';
}
function getPonPortOLt($olt,$llid){
	global $db;
	return $db->Fast('switch_pon','*',['oltid'=>$olt,'sfpid'=>$llid]);
}
function idblock($name) {
    $map = [
        'fastethernet' => 1,
        'epon' => 2,
        'gpon' => 3,
        'tgigaethernet' => 4,
        'gigaethernet' => 5,
        'xge' => 6,
        'ge' => 7,
    ];
    $id = $map[mb_strtolower(trim($name))] ?? 10;
    return $id;
}

function statsPonPort($data, $support) {
	global $lang;
    $style = '';
    if (!empty($data['support'])) {
        $style .= '<div class="curent-onu">';
        $counts = array(
            'online' => 'counton',
            'offline' => 'countoff',
            'count' => 'count',
        );
        foreach ($counts as $key => $class) {
            if (!empty($data[$key])) {
                $style .= '<div><span class="' . $class . '">' . $data[$key] . '</span><span class="countname">' . ($key === 'count' ? $lang['all'] : ($key === 'online' ? $lang['online'] :  $lang['offline'])) . '</span></div>';
            }
        }
        $style .= '<div><span class="support">' . $data['support'] . '</span><span class="supportname">' . $support . '</span></div>';
        $style .= '</div>';
    }
    return $style;
}

function statusInfo($status){
	return '<span class="infos"><img src="../style/img/settings.png">'.$status.'</span>';
}
function pager($rpp, $count, $href, $opts = array()) {
	$bregs = '';
	$pager2 = '';
	$pager = '';
	$pagerbottom = '';
	$pages = ceil($count / $rpp);
	$pagedefault = 0;
	if (!empty($opts['lastpagedefault']))
		$pagedefault = floor(($count - 1) / $rpp);
		if ($pagedefault < 0)
			$pagedefault = 0;
	else {
		$pagedefault = 0;
	}
	if (isset($_GET["page"])) {
		$page = (int)$_GET["page"];
		if ($page < 0)
			$page = $pagedefault;
	}
	else
		$page = $pagedefault;	   

	$mp = $pages - 1;
	$as = '<i class="fi fi-rr-angle-left"></i>';
	if ($page >= 1) {
		$pager .= "<td style=\"border:none\">";
		$pager .= "</td>";
	}
	$as = '<i class="fi fi-rr-angle-right"></i>';
	if ($page < $mp && $mp >= 0) {
		$pager2 .= "<td style=\"border:none\">";
		$pager2 .= "<a class=\"navi\" href=\"{$href}&page=" . ($page + 1) . "\" style=\"text-decoration: none;\">$as</a>";
		$pager2 .= "</td>$bregs";
	}else	 $pager2 .= $bregs;

	if ($count) {
		$pagerarr = array();
		$dotted = 0;
		$dotspace = 3;
		$dotend = $pages - $dotspace;
		$curdotend = $page - $dotspace;
		$curdotstart = $page + $dotspace;
		for ($i = 0; $i < $pages; $i++) {
			if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
				if (!$dotted)
				   $pagerarr[] = "<td style=\"border:none\" ><span class=\"clear\">...</span></td>";
				$dotted = 1;
				continue;
			}
			$dotted = 0;
			$start = $i * $rpp + 1;
			$end = $start + $rpp - 1;
			if ($end > $count)
				$end = $count;

			 $text = $i+1;
			if ($i != $page){
				if(!$i){
					$new_url=$href;
				}else{
					$new_url=$href.'&page='.$i;
				}
				$pagerarr[] = "<td style=\"border:none\"><a class=\"navi\" title=\"$start&nbsp;-&nbsp;$end\" href=\"{$new_url}\" style=\"text-decoration: none;\">$text</a></td>";
			}else{
				$pagerarr[] = "<td style=\"border:none\"><span>$text</span></td>";
			}
		}
		$pagerstr = join("", $pagerarr);
		$pagertop = "<table class=\"navs navigation\"><tr>$pager $pagerstr $pager2</tr></table>\n";
	
	}else {
		$pagertop = $pager;
		$pagerbottom = $pagertop;
	}
	$start = $page * $rpp;
	return array($pagertop, $pagerbottom, $start, $rpp);
}
function statusTermianl($status){
	$data = array(
		'css' => ($status == 1) ? 'up' : 'down',
		'img' => '<img src="../style/img/' . (($status == 1) ? 'online' : 'offline') . '.png">'
	);	
	return $data;
}
function signalTerminalRx($signal,$signalbadstart,$signalbadend){
	$signala = abs((int)$signal);
	$signal_str = sprintf("%.2f", $signal);
	switch (true) {
		case ($signala >= 1 && $signala <= 12):
			return '<span class="signal1">' . $signal_str . ' </span>';
		case ($signala >= 13 && $signala <= 19):
			return '<span class="signal2">' . $signal_str . ' </span>';
		case ($signala >= 20 && $signala <= ($signalbadstart-1)):
			return '<span class="signal3">' . $signal_str . ' </span>';
		case ($signala >= $signalbadstart && $signala <= $signalbadend):
			return '<span class="signal4">' . $signal_str . ' </span>';
		default:
			return $signala ? '<span class="signal0">' . $signal_str . ' </span>' : '';
	}
}
function signalTerminal($signal){
	global $config;
	$signalbadstart = !empty($config['badsignalstart']) ? $config['badsignalstart'] : 26;
	$signalbadend = !empty($config['badsignalend']) ? $config['badsignalend'] : 39;
	$signala = abs((int)$signal);
	$signal_str = sprintf("%.2f", $signal);
	switch (true) {
		case ($signala >= 1 && $signala <= 12):
			return '<span class="signal1">' . $signal_str . ' </span>';
		case ($signala >= 13 && $signala <= 19):
			return '<span class="signal2">' . $signal_str . ' </span>';
		case ($signala >= 20 && $signala <= ($signalbadstart-1)):
			return '<span class="signal3">' . $signal_str . ' </span>';
		case ($signala >= $signalbadstart && $signala <= $signalbadend):
			return '<span class="signal4">' . $signal_str . ' </span>';
		default:
			return $signala ? '<span class="signal0">' . $signal_str . ' </span>' : '';
	}
}
function signalTerminalPonAnalyzer($signal){
	$signal_str = sprintf("%.2f", $signal);
	$chars = array("'", '"', '-', '<');
	$signala = str_replace($chars, '', $signal);
	switch (true) {
		case ($signala >= 0.4 && $signala <= 0.9):
			return '<span class="signal3">' . $signal_str . ' </span>';
		case ($signala >= 1 && $signala <= 2):
			return '<span class="signal5">' . $signal_str . ' </span>';
		case ($signala >= 2.1 && $signala <= 5):
			return '<span class="signal4">' . $signal_str . ' </span>';
		case ($signala >= 5.1 && $signala <= 10):
			return '<span class="signal4">' . $signal_str . ' </span>';
		default:
			return $signala ? '<span class="signal0">' . $signal_str . ' </span>' : '';
	}
}
function SQLclear($result) {
	if($result){
		$chars = array("'", '"', '>', '<');
		$result = str_replace($chars, '', $result);
		return $result;
	}
}
function sql_checker($result) {
	if($result){
		$chars = array("'", '"', '>', '<', ' ', ':', ',', '.');
		$result = str_replace($chars, '', $result);
		return $result;
	}
}
function totranslit($var, $lower = true, $punkt = true, $langtranslit = []) {
    if ( is_array($var) ) {
        return "";
    }    
    $var = str_replace(chr(0), '', $var);    
    if ( !is_array($langtranslit) || !count($langtranslit) ) {
        $var = trim( strip_tags( $var ) );
        $var = $punkt ? preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var ) : preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );
        $var = preg_replace( '#[.]+#i', '.', $var );
        $var = str_replace( ".php", ".ppp", $var );        
        if ( $lower ) {
            $var = strtolower( $var );
        }        
        return $var;
    }    
    $var = trim( strip_tags( $var ) );
    $var = preg_replace( "/\s+/ms", "-", $var );
    $var = str_replace( "/", "-", $var );
    $var = strtr($var, $langtranslit);
    $var = $punkt ? preg_replace_callback( "/[^a-z0-9\_\-.]+/mi", fn($match) => '', $var ) : preg_replace_callback( "/[^a-z0-9\_\-]+/mi", fn($match) => '', $var );
    $var = preg_replace( '#[\-]+#i', '-', $var );
    $var = preg_replace( '#[.]+#i', '.', $var );    
    if ( $lower ) {
        $var = strtolower( $var );
    }    
    $var = str_replace( ".php", "", $var );
    $var = str_replace( ".php", ".ppp", $var );    
    if ( strlen( $var ) > 200 ) {        
        $var = substr( $var, 0, 200 );        
        if ( ($temp_max = strrpos( $var, '-' )) ) {
            $var = substr( $var, 0, $temp_max );
        }   
    }      
    return $var;
}
function isvalidtext(string $content): bool {
    $blacklist = [
        '/SELECT/i','/UNION/i','/fopen/i','/file_get_contents/i','/root/i','/<\?php/i','/<\?/i','/\?>/i','/system/i','/exec/i','/shell_exec/i','/passthru/i','/eval/i','/script/i','/alert/i'
    ];
    foreach ($blacklist as $command) {
        if (preg_match($command, $content)) {
            return false;
        }
    }
    return true;
}
function getVolt($volt,$types){
	if($types==12){
		if ($volt > 13) {
			return 100;
		} elseif ($volt > 12.4) {
			return 80;
		} elseif ($volt > 12.2) {
			return 60;
		} elseif ($volt > 12) {
			return 40;
		} elseif ($volt > 11.8) {
			return 20;
		} else {
			return 0;
		}
	}elseif($types==24){
		if ($volt > 26) {
			return 100;
		} elseif ($volt > 25) {
			return 80;
		} elseif ($volt > 24.2) {
			return 60;
		} elseif ($volt > 23) {
			return 40;
		} elseif ($volt > 22) {
			return 20;
		} else {
			return 0;
		}
	}elseif($types==48){

	}elseif($types==72){
		if ($volt > 76.5) {
			return 100;
		} elseif ($volt > 76.3) {
			return 80;
		} elseif ($volt > 76.2) {
			return 60;
		} elseif ($volt > 76.1) {
			return 40;
		} elseif ($volt > 76) {
			return 20;
		} else {
			return 0;
		}
	}
}
function monitorPing3img($sql){
	$monitor220 = '';
	if($sql['energystatus']=='yes' && !empty($sql['energystatus'])){
		if($sql['energy']==2)
			$monitor220 = 'class="name220off"';
	}
	if(!empty($sql['status0']) && !empty($sql['status20']) && !empty($sql['status40']) && !empty($sql['status60']) && !empty($sql['status80'])){
		$getvolt = getVolt($sql['volt'],$sql['typebattery']);
	}else{
		$getvolt = getVolt($sql['volt'],$sql['typebattery']);
	}
	$img = ($sql['energystatus']=='yes' && $sql['energy']==2 ? 'no':'').(isset($getvolt)?$getvolt:'0');
	return'<img '.$monitor220.' src="../style/ping3/'.$img.'.png">';
}
function mereja($value){
	$value = str_replace('2', '0',$value);
	return str_replace('1', '5',$value);	
}
function getAllBattery(){
	global $db;
	$array = [];
	$sqlbattery = $db->SimpleWhile("SELECT * FROM battery");
	if(is_array($sqlbattery)){
		foreach($sqlbattery as $battery){
			$unit = '';
			$used = '';
			$ping3 = '';
			$getusedBattery = $db->Simple("SELECT id, deviceid, connectd, added FROM battery_used WHERE batteryid = '{$battery['id']}' LIMIT 1");
			$getusedUnit = $db->Simple("SELECT id, unitid, added FROM battery_unit WHERE batteryid = '{$battery['id']}' LIMIT 1");
			if(isset($getusedUnit['unitid']) && $getusedUnit['unitid'] > 0){
				$unit = $db->Simple("SELECT id, name FROM ponunit WHERE id = '{$getusedUnit['unitid']}' LIMIT 1");
			}
			if(isset($getusedBattery['connectd']) && $getusedBattery['connectd'] =='ping3'){
				$ping3 = $db->Simple("SELECT name FROM mon_ping3 WHERE id = '{$getusedBattery['deviceid']}' LIMIT 1");
			}
			$used = (isset($getusedBattery['id']) || isset($unit['id']) ? 'used' : 'sklad');
			$array[$used][$battery['id']] = [
				'device_ping3' => (isset($ping3['name']) ? $ping3['name'] : ''),
				'device_unit' => (isset($unit['name']) ? $unit['name'] : ''),
				'name' => $battery['name'],
				'voltage' => $battery['voltage'],
				'amper' => $battery['amper'],
				'batteryid' => $battery['id'],
				'model' => $battery['model'],
				'types' => $battery['types'],
				'unitid' => (isset($unit['id']) ? $unit['id'] : ''),
				'unit' => (isset($getusedUnit['id']) ? 'yes' : 'no'),
				'unit_added' => (isset($getusedUnit['added']) ? $getusedUnit['added'] : null),
				'used' => (isset($getusedBattery['id']) ? 'yes' : 'no')
			];
		}
	}
	return $array;
}
function bb_code($text){
	$text = str_replace("[sql_hr]", "<hr><br>", $text);
	return $text;
}
function generatePagination($currentPage, $totalPages) {
    $pagination = [];
    for ($i = 1; $i <= min(4, $totalPages); $i++) {
        $pagination[] = $i;
    }
    if ($currentPage > 5) {
        $pagination[] = '...';
    }
    $start = max(5, $currentPage - 1);
    $end = min($totalPages - 4, $currentPage + 1);
    for ($i = $start; $i <= $end; $i++) {
        if (!in_array($i, $pagination) && $i > 0 && $i <= $totalPages) {
            $pagination[] = $i;
        }
    }
    if ($currentPage < $totalPages - 4) {
        $pagination[] = '...';
    }
    for ($i = max($totalPages - 3, $end + 1); $i <= $totalPages; $i++) {
        if (!in_array($i, $pagination) && $i > 0 && $i <= $totalPages) {
            $pagination[] = $i;
        }
    }
    return $pagination;
}
function renderPagination($currentPage, $totalPages) {
	global $lang;
    $pages = generatePagination($currentPage, $totalPages);
    echo '<div class="paginators">';
    if ($currentPage > 1) {
        echo '<a href="#" onclick="loadPage(' . ($currentPage - 1) . ')">&laquo; '.$lang['page_prev'].'</a> ';
    }
    foreach ($pages as $page) {
        if ($page === '...') {
            echo '<span class="procheckrk">' . $page . '</span> ';
        } else {
            if ($page == $currentPage) {
                echo '<span class="active">' . $page . '</span> ';
            } else {
                echo '<a href="#" onclick="loadPage(' . $page . ')">' . $page . '</a> ';
            }
        }
    }
    if ($currentPage < $totalPages) {
        echo '<a href="#" onclick="loadPage(' . ($currentPage + 1) . ')">'.$lang['page_next'].' &raquo;</a> ';
    }
    echo '</div>';
}
function renderPaginationtpl($currentPage, $totalPages) {
    global $lang;
    $echo = '<div class="paginators">';
    if ($currentPage > 1) {
        $echo .= '<a href="#" onclick="loadPage(' . ($currentPage - 1) . ')">&laquo; ' . $lang['page_prev'] . '</a> ';
    }
    if ($totalPages <= 4) {
        for ($page = 1; $page <= $totalPages; $page++) {
            if ($page == $currentPage) {
                $echo .= '<span class="active">' . $page . '</span> ';
            } else {
                $echo .= '<a href="#" onclick="loadPage(' . $page . ')">' . $page . '</a> ';
            }
        }
    } else {
        $pages = generatePagination($currentPage, $totalPages);
        $previousPage = null; // Track previous page to avoid duplicates
        foreach ($pages as $page) {
            if ($page === '...') {
                $echo .= '<span class="procheckrk">' . $page . '</span> ';
            } else {
                if ($page == $currentPage) {
                    $echo .= '<span class="active">' . $page . '</span> ';
                } else {
                    if ($page !== $previousPage) {
                        $echo .= '<a href="#" onclick="loadPage(' . $page . ')">' . $page . '</a> ';
                    }
                }
                $previousPage = $page;
            }
        }
    }
    if ($currentPage < $totalPages) {
        $echo .= '<a href="#" onclick="loadPage(' . ($currentPage + 1) . ')">' . $lang['page_next'] . ' &raquo;</a> ';
    }
    $echo .= '</div>';
    return $echo;
}
function get_period_ping3($deviceid) {
	global $lang, $db;
	$data = [];
	$records = $db->SimpleWhile("SELECT * FROM mon_voltage WHERE deviceid = '{$deviceid}' AND DATE(added) = CURDATE() ORDER BY added");
	$periods = [
		'on' => 0,  // Періоди, коли було світло
		'off' => 0  // Періоди, коли не було світла
	];
	$current_status = null;
	foreach ($records as $record) {
		$energy = $record['energy'];		
		if ($current_status === null) {
			$current_status = $energy;
			$periods[$energy == 1 ? 'on' : 'off']++;
		} elseif ($energy != $current_status) {
			$current_status = $energy;
			$periods[$energy == 1 ? 'on' : 'off']++;
		}
	}
$data['off'] = " Вимикали за сьогодні: [b]" . $periods['on'] . "[/b]";
$data['on'] = " Вмикали за сьогодні: [b]" . $periods['off'] . "[/b]";
return $data;
}


function get_gps_car($data_conf) {
	global $confPMon, $db;
	$mapont = '';
	if (isset($confPMon['GPS_TRACKER_COM_UA']) && !empty($confPMon['GPS_TRACKER_COM_UA']) && $confPMon['GPS_TRACKER_COM_UA'] == 1) {
		$templatgps = $db->Simple("SELECT data FROM tempdate WHERE file = 'gps_tracker' LIMIT 1");
		if (!empty($templatgps['data'])) {
			$data = @unserialize($templatgps['data']);
			if ($data === false) {
				return;
			}
			$mapont .= "var gps_tracker = L.markerClusterGroup();\n";
			foreach ($data['rows'] as $idcar => $car) {
				$activity = aftertime(date('Y-m-d H:i:s', strtotime($car['last_packet_date'])));
				$name = str_replace(["`", "'", ".", '"'], '', $car['CarName']);
				$Speed = str_replace(["`", "'", ".", '"'], '', $car['Speed']);
				$color = (isset($car['SpeedV']) && $car['SpeedV'] == 1 ? 'green' : 'red');
				$carSvg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' fill='#000000'><path d='M22,6h-2v2.65l-1.02-4.84C18.89,3.34,18.48,3,18,3H6C5.52,3,5.11,3.34,5.02,3.81L4,8.64V6H2v3h1.62L2.3,10.28 C2.11,10.47,2,10.73,2,11v9c0,0.552,0.448,1,1,1h2l-0.006-3h14.011L19,21h2c0.552,0,1-0.448,1-1v-9c0-0.27-0.11-0.53-0.31-0.72 L20.37,9H22V6z M6.82,5h10.36l0.79,4H6.03L6.82,5z M5.5,15C4.67,15,4,14.33,4,13.5C4,12.67,4.67,12,5.5,12S7,12.67,7,13.5 C7,14.33,6.33,15,5.5,15z M15,16H9v-4h6V16z M18.5,15c-0.83,0-1.5-0.67-1.5-1.5c0-0.83,0.67-1.5,1.5-1.5s1.5,0.67,1.5,1.5 C20,14.33,19.33,15,18.5,15z' fill='{$color}'/></svg>";
				$carSvgJson = json_encode($carSvg);
				$popupContent = $name.'<br>'.$Speed;
				$mapont .= "\nvar car = L.marker([".$car['X'].", ".$car['Y']."], {icon: L.divIcon({className: 'car_gps',html: $carSvgJson,iconSize: [32, 32],iconAnchor: [16, 32],popupAnchor: [0, -32]})}).bindPopup('{$popupContent}').openPopup().bindTooltip('{$name}');\n";				
				$mapont .= "gps_tracker.addLayer(car);\n";
			}
			$mapont .= "{$data_conf['map']}.addLayer(gps_tracker);\n";
		}
	}

	if (isset($confPMon['GPS_TRACCAR_LOGIN']) && !empty($confPMon['GPS_TRACCAR_LOGIN']) && isset($confPMon['GPS_TRACCAR']) && !empty($confPMon['GPS_TRACCAR']) && $confPMon['GPS_TRACCAR'] == 1) {
		$templatgps = $db->Simple("SELECT data FROM tempdate WHERE file = 'gps_traccar' LIMIT 1");
		if (!empty($templatgps['data'])) {
			$data = @unserialize($templatgps['data']);
			if ($data === false) {
				return;
			}
			$mapont .= "var gar_gps_traccar = L.markerClusterGroup();\n";
			foreach ($data as $idcar => $car) {
				$activity = aftertime(date('Y-m-d H:i:s', strtotime($car['car_update'])));
				$color = (isset($car['car_status']) && $car['car_status'] == 'online' ? 'green' : 'red');
				$name_ar = (isset($car['car_name']) ? $car['car_name'] : $car['name']);
				$name = str_replace(["`", "'", ".", '"'], '', $name_ar);
				$speed = (isset($car['speed']) ? $car['speed'] : $car['speed']);
				$name_or = str_replace(["`", "'", ".", '"'], '', $car['name']);
				$carSvg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' fill='#000000'><path d='M22,6h-2v2.65l-1.02-4.84C18.89,3.34,18.48,3,18,3H6C5.52,3,5.11,3.34,5.02,3.81L4,8.64V6H2v3h1.62L2.3,10.28 C2.11,10.47,2,10.73,2,11v9c0,0.552,0.448,1,1,1h2l-0.006-3h14.011L19,21h2c0.552,0,1-0.448,1-1v-9c0-0.27-0.11-0.53-0.31-0.72 L20.37,9H22V6z M6.82,5h10.36l0.79,4H6.03L6.82,5z M5.5,15C4.67,15,4,14.33,4,13.5C4,12.67,4.67,12,5.5,12S7,12.67,7,13.5 C7,14.33,6.33,15,5.5,15z M15,16H9v-4h6V16z M18.5,15c-0.83,0-1.5-0.67-1.5-1.5c0-0.83,0.67-1.5,1.5-1.5s1.5,0.67,1.5,1.5 C20,14.33,19.33,15,18.5,15z' fill='{$color}'/></svg>";
				$carSvgJson = json_encode($carSvg);
				$popupContent = (isset($name) ? "<b>Швидкисть:</b> ".$speed."км/год <b>Двигун:</b> <font color=\"".(isset($car['ignition']) && $car['ignition']?'green':'red')."\">".(isset($car['ignition']) && $car['ignition']?'Enable':'Disable')."</font><br><b>Інформація:</b> ".$name."<br><b>Перевірено:</b> ".$activity."<br>" : "")."<b>Локація:</b> ".$name_or;
				$mapont .= "\nvar car = L.marker([".$car['lan'].", ".$car['lon']."], {icon: L.divIcon({className: 'car_gps',html: $carSvgJson,iconSize: [32, 32],iconAnchor: [16, 32],popupAnchor: [0, -32]})}).bindPopup('{$popupContent}').openPopup().bindTooltip('{$name}');\n";				
				$mapont .= "gar_gps_traccar.addLayer(car);\n";
			}
			$mapont .= "{$data_conf['map']}.addLayer(gar_gps_traccar);\n";
		}
	}
	return $mapont;
}
function positionTraccarData($apiUrl, $username, $password) {
    $ch = curl_init();
	$url = $apiUrl.'/api/positions';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return json_decode($response, true);
}
function carTraccarData($apiBaseUrl, $username, $password) {
    $apiUrl = $apiBaseUrl . '/api/devices';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password); // Авторизація
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    $data = json_decode($response, true);
    if (isset($data['error'])) {
        echo 'Error: ' . $data['error'];
        return false;
    }    
    return $data;
}
// logont.php
function paginate_function($item_per_page, $current_page, $total_records, $total_pages){
    $pagination = '';
    if($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages){ //verify total pages and current page number
        $pagination .= '<ul class="paginations">';        
        $right_links    = $current_page + 3; 
        $previous       = $current_page - 3;
        $next           = $current_page + 1;
        $first_link     = true;  
        if($current_page > 1){
			$previous_link = ($previous==0)?1:$previous;
            #$pagination .= '<li class="first"><a href="#" data-page="1" title="First">«</a></li>'; //first link
            #$pagination .= '<li><a href="#" data-page="'.$previous_link.'" title="Previous"><</a></li>'; //previous link
                for($i = ($current_page-2); $i < $current_page; $i++){ //Create left-hand side links
                    if($i > 0){
                        $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page'.$i.'">'.$i.'</a></li>';
                    }
                }   
            $first_link = false;
        }        
        if($first_link){ 
            $pagination .= '<li class="first active">'.$current_page.'</li>';
        }elseif($current_page == $total_pages){ 
            $pagination .= '<li class="last active">'.$current_page.'</li>';
        }else{ 
            $pagination .= '<li class="active">'.$current_page.'</li>';
        }                
        for($i = $current_page+1; $i < $right_links ; $i++){ 
            if($i<=$total_pages){
                $pagination .= '<li><a href="#" data-page="'.$i.'" title="Page '.$i.'">'.$i.'</a></li>';
            }
        }
        if($current_page < $total_pages){ 
				$next_link = ($i > $total_pages)? $total_pages : $i;
                #$pagination .= '<li><a href="#" data-page="'.$next_link.'" title="Next">></a></li>'; //next link
                #$pagination .= '<li class="last"><a href="#" data-page="'.$total_pages.'" title="Last">»</a></li>'; //last link
        }        
        $pagination .= '</ul>'; 
    }
    return $pagination;
}
function getListDevice($type = false, $pmon_license = '') { 
	global $db;
	$where = ['work' => 'yes'];
	if ($type) {
		$where['device'] = $type;
	}
	$list = $db->Multi('equipment', '*', $where);
	return $list ? $list : false;
}
function charts_olt_signal($data_json, $id) {
$result = <<<HTML
<div id="graph_signal_{$id}" style="width: 100%;"></div>
<style>
    .bar-hover {
        animation: blink 1s infinite alternate;
    }
    @keyframes blink {
        from {
            opacity: 1;
        }
        to {
            opacity: 0.6;
        }
    }
</style>
<script>
    const chartData = {$data_json};
    // Clear existing SVG
    d3.select("#graph_signal_{$id}").selectAll("svg").remove();
    const container = d3.select("#graph_signal_{$id}");
    const containerWidth = container.node().getBoundingClientRect().width;
    const margin = { top: 20, right: 10, bottom: 30, left: 30 },
          width = containerWidth - margin.left - margin.right,
          height = 230 - margin.top - margin.bottom;
    const svg = container
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", `translate(\${margin.left}\,\${margin.top}\)`);
    const x = d3.scaleBand()
        .domain(chartData.map(d => d.signal))
        .range([0, width])
        .padding(0.1);
    const y = d3.scaleLinear()
        .domain([0, d3.max(chartData, d => d.count)])
        .nice()
        .range([height, 0]);
    svg.append("g")
        .selectAll("rect")
        .data(chartData)
        .join("rect")
        .attr("x", d => x(d.signal))
        .attr("y", d => y(d.count))
        .attr("width", x.bandwidth())
        .attr("height", d => height - y(d.count))
        .attr("fill", d => d.color)
        .attr("class", "bar")
        .style("cursor", "pointer")
        .on("mouseover", function () {
            d3.select(this).classed("bar-hover", true);
        })
        .on("mouseout", function () {
            d3.select(this).classed("bar-hover", false);
        })
        .on("click", function (event, d) {
            showbadsignal({$id}, d.signal);
        });
    svg.append("g")
        .attr("transform", `translate(0,\${height}\)`)
        .call(d3.axisBottom(x).tickSizeOuter(0));
    svg.append("g")
        .call(d3.axisLeft(y).ticks(5).tickFormat(d => d));
    svg.selectAll(".bar-label")
        .data(chartData)
        .join("text")
        .attr("class", "bar-label")
        .attr("x", d => x(d.signal) + x.bandwidth() / 2)
        .attr("y", d => y(d.count) - 5)
        .attr("text-anchor", "middle")
        .attr("fill", "black")
        .style("font-size", "11px")
        .text(d => d.count);
    window.addEventListener("resize", () => {
        const newWidth = container.node().getBoundingClientRect().width;
        svg.attr("width", newWidth);
    });
</script>
HTML;
echo $result;
}
// competetior.php
function allDrawwCoordinates() {
    global $db;
	$marker = '';
	$sql = $db->SimpleWhile("SELECT * FROM competitor_locations");
	if(isset($sql) && is_array($sql)){
		foreach($sql as $row){
			$coordinates = str_replace('&quot;', '"',$row["coordinates"]);
			$coordinates = str_replace("\/", '',$coordinates);
            $marker .= "var latlngs = " . $coordinates . ";";
            $marker .= "var polygon = L.polygon(latlngs, {color: '" . $row["color"] . "'}).addTo(map);";
            $marker .= "polygon.bindPopup('<b>Name: " . addslashes($row["name"]) . "</b><br>ID: " . $row["competitorid"] . "');";		
		}
	}
	return $marker;
}
function safe_math_formula($formula, $temp) {
    $formula = str_replace('$temp', "($temp)", $formula);
    $allowedFunctions = ['round', 'intval', 'abs', 'floor', 'ceil'];
    preg_match_all('/([a-z_][a-z0-9_]*)\s*\(/i', $formula, $matches);
    foreach ($matches[1] as $func) {
        if (!in_array($func, $allowedFunctions, true)) {
            return null;
        }
    }
    if (preg_match('/[^0-9\.\+\-\*\/\(\),\s$A-Za-z_]/', $formula)) {
        return null;
    }
    try {
        $result = eval('return ' . $formula . ';');
        return is_numeric($result) ? (float)$result : null;
    } catch (Throwable $e) {
        return null;
    }
}
function isValidOID($oid) {
    return preg_match('/^(\d+\.)+\d+$/', $oid);
}
?>
