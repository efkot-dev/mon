<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function typesMereja($types){
	switch ($types) {
        case 'pon':
            return 'PON';
        case 'magistral':
            return 'LINE';
        case 'connect':
            return 'CONNECT';        
		case 'well':
            return 'RJK';
        default:
            return 'N_A';
    }
}
function types_box_add_icon($box){
	if($box['types']==1){
		return 'min_myfta';
	}elseif($box['types']==3){
		return "min_myfta_proxid";
	}else{
		return '';		
	}	
}
function get_fiber_map_pon($sqlpontree,$getfiber){
	global $db;
	$marker = '';
	$optic = '';
	if(isset($sqlpontree) && count($sqlpontree)>0){
		$where .='WHERE tree IN (';
		foreach($sqlpontree as $pontree){
			$where .="".$pontree['id'].",";
			$arraytree .= $pontree['id'].',';
		}
		$where = rtrim($where, ",");
		$arrayString = rtrim($arraytree, ",");
		$where_onus = rtrim($arraytree, ",");
		$where .= ");";
	}
	$markers = [];
	$sqlponelement = $db->SimpleWhile('SELECT * FROM ponelement '.$where);
	if(isset($sqlponelement) && count($sqlponelement)>0){
		foreach($sqlponelement as $ponelem){
			if(!empty($ponelem['lan']) && !empty($ponelem['lon']) ){
				if(!empty($ponelem['myicon'])){
					$icon = str_replace(['.png', '.jpg', ';', ' '], '',$ponelem['myicon']);
					$icon_element = $icon;
				}else{
					$icon_element = types_box_add_icon($ponelem);
				}
				$markers[] = [
                    'lat' => $ponelem['lan'],
                    'icon' => $icon_element,
                    'lng' => $ponelem['lon'],
                    'label' => $ponelem['name'],
                    'status' => ($ponelem['types'] == 2 && isset($ponelem['status']) ? $ponelem['status']==1?'m_on':'m_off':'m_empty'),
                    'id' => $ponelem['id']
                ];
			}
		}
	}
	if(isset($arrayString) && isset($sqlpontree) && count($sqlpontree)>0){
		$sql = "SELECT * FROM fibers WHERE tree1 IN ($arrayString) OR tree2 IN ($arrayString) ";
		$sqlponfiber = $db->SimpleWhile($sql);		
		if(count($sqlponfiber)>0){
			foreach($sqlponfiber as $fiber){
				if(isset($fiber['geo'])){
					$optic .= getFibers($fiber,$getfiber);					
				}				
			}
		}	
	}
	return array('marker'=>$markers,'optic'=>$optic);	
}
function get_akb($data,$db){
	global $lang;
	$vyzol = '';
	$sql = "SELECT bu.batteryid AS battery_id, bu.unitid, bu.added AS battery_unit_added,
 b.id AS battery_id, 
 b.name AS battery_name, b.amper AS battery_amper, b.voltage AS battery_voltage
, b.model AS battery_model, b.types AS battery_types
FROM battery_unit bu
JOIN battery b ON b.id = bu.batteryid
WHERE bu.unitid = '{$data['id']}'";
	$sqlselect = $db->SimpleWhile($sql);
	if(is_array($sqlselect)){	
		$vyzol .= '<table class="view-ping3"><tbody>';
		foreach($sqlselect as $getBattery){
			$vyzol .= '<tr>
				<td class="ping3-img">
					<div class="lg">
						<img src="../style/img/'.$getBattery['battery_types'].'batteryimg.png">
					</div>
				</td>
				<td class="ping3-info">
					<h2>'.$getBattery['battery_name'].'</h2>
					'.$getBattery['battery_model'].'<br>
					<b>'.$lang['addbattery_1'].':</b> '.$getBattery['battery_types'].'<br>
					<b>'.$getBattery['battery_amper'].'Ah</b> <b>'.$getBattery['battery_voltage'].'V</b>
				</td></tr>';
		}
		$vyzol .= '</tbody></table>';
	}	
	return $vyzol;
}
function get_fevice_fiber_map($data,$db){
	$tpl = '<div class="fiber_device_panel">
		
	</div>';
	// <a href="/"><img src="../style/img/min_add.png">Підключити комутатор</a>
	if(isset($data) && count($data)>0){
		$tpl .= '<div class="fiber_device">';
		foreach($data as $olt){
			if(isset($olt['olt']) && $olt['olt']>0){
				$tpl .= building_device($olt['olt'],$db);
			}
		}
		$tpl .= '</div>';
	}
	return $tpl;
}
function building_device($deviceid,$db){
	$device = $db->Simple("SELECT * FROM switch WHERE id = '{$deviceid}'");
	return '
			<div class="house_device">
				<div class="device_photo"><img src="../style/device/'.$device['img'].'"></div>
				<div class="device_model">'.$device['inf'].' '.$device['model'].'</div>
				<div class="device_place"><a class="aswitch" href="/?do=detail&act='.$device['device'].'&id='.$device['id'].'">'.$device['place'].'</a></div>
			</div>
			';	
}
function building_device_($deviceid,$db){
	$get_device = $db->Simple("SELECT * FROM switch WHERE id = '{$deviceid}'");
	return"
		<div id='building_device' data-id='{$deviceid}'>
			<div class='device-logo'>
				<img src='../style/device/{$get_device['img']}'>
			</div>
			<div class='device-information'>
			<h2>{$get_device['place']}</h2>
			<h3>{$get_device['inf']} {$get_device['model']}</h3>
			<span class=\"olt-dev-olt\"><span class=\"timer\"><span>{$get_device['uptime']}</span></span></span>
			</div>
		</div>
	";
}
function dist_($dist){
	if(isset($dist) && $dist>0){
		$dist = isset($dist) ? intval($dist) / 1000 : 0;
		return sprintf('%.2f',$dist);
	}
return 0;	
}
function list_pon_element_map($data){
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
	$tpl = '';
    $sqlonus = $db->SimpleWhile("SELECT * FROM onusdata " . $where_onus);
	if(isset($sqlonus) && count($sqlonus)>0){
		$tpl .= '<div class="ponelementdevicemap">';
		foreach($sqlonus as $idont => $ont){
			$sqlonu = $db->Simple("SELECT idonu, status, rx, dist, offline, online, inface, type FROM onus WHERE mac = '".$ont['onukey']."' OR sn = '".$ont['onukey']."' LIMIT 1");
			if(!empty($sqlonu['status'])){
			$tpl .= '<div class="onu_style_map '.($sqlonu['status']==1?'on':'off').'status 
			'.(!empty($building['id']) ? 'building' : '').'">
			<div class="onu_img">	
				<img src="../style/ponmap/onu.png">
			</div>
			<div class="map_onu_fiber">
				<div class="map_onu_pon">
					<div>
						<a href="/?do=onu&id='.$sqlonu['idonu'].'">'.$sqlonu['type'].' '.$sqlonu['inface'].'</a>
					</div>
					<div class="onukey">
					'.$ont['onukey'].' '.($sqlonu['status']==2 ? '<div class="time_off">'.aftertime($sqlonu['offline']).'</div>' : ($sqlonu['status']==1 ? '<div class="time_on">'.aftertime($sqlonu['online']).'</div>' : '' ) ).'
					</div>
				</div>
			</div>
			<div class="onu_map_rx">	
				'.($sqlonu['status']==1?'<span class="signal">'.signalTerminal($sqlonu['rx']).'</span>':'').'			
			</div>			
			<div class="onu_map_dist">	
				'.dist_($sqlonu['dist']).' km			
			</div>
			</div>
			';
			/*
			<a class="onu_ponelement '.($sqlonu['status']==1?'on':'off').'status" href="/?do=onu&id='.$sqlonu['idonu'].'">
			'.($sqlonu['status']==1?'<span class="signal">'.signalTerminal($sqlonu['rx']).'</span>':'').
			$ont['onukey'].'</a>
			'.($sqlonu['status']==2 ? '<div class="time_off">'.aftertime($sqlonu['offline']).'</div>' : '' ).'
			'.($sqlonu['status']==1 ? '<div class="time_on">'.aftertime($sqlonu['online']).'</div>' : '' ).'
			</div>
			*/
			}
		}
		$tpl .= '</div>';
	}
	return $tpl;
}
function onu_fiber_element_($pontree){
global $db;
$where_onus = '';
if (!empty($pontree['id'])) {
$where_onus = ' WHERE ponelement = ' . $pontree['id'];
$sqlonus = $db->SimpleWhile("SELECT id, lan, lon FROM onusdata " . $where_onus);
$data = '';
foreach($sqlonus as $idont => $ont){
if (!empty($ont['lan']) && !empty($ont['lon'])) {
$data .= 'var conn_fiber'.$ont['id'].' = [['.$ont['lan'].','.$ont['lon'].'],['.$pontree['lan'].','.$pontree['lon'].']];
var polyline'.$ont['id'].' = L.polyline(conn_fiber'.$ont['id'].', {color: "'.(isset($pontree['status']) && $pontree['status']==2?'red':'#FFC107').'", weight: 2, opacity: 0.9}).bindTooltip("ONU").addTo(map);
';
if (isset($pontree['status']) && $pontree['status']==2) {
$data .= 'setInterval(function() {
polyline'.$ont['id'].'.setStyle({opacity: 0.4});
setTimeout(function() {polyline'.$ont['id'].'.setStyle({opacity: 0.9});}, 600);
}, 1000);
';
}
}
}
}	
return $data;
}
function marker_onu($data) {
global $db, $lang;
$mapont = '';
$sqlmaponu = "
SELECT 
    onusdata.onukey, 
    onus.mac, 
    onus.sn, 
    onus.idonu, 
    onusdata.lan, 
    onusdata.lon, 
    onus.inface, 
    onus.rx, 
    onus.dist, 
    onus.status, 
    onus.olt, 
    onus.type
FROM 
    onusdata
JOIN 
    onus ON onusdata.onukey = onus.mac OR onusdata.onukey = onus.sn
WHERE 
    onusdata.pontree IN (".$data.") 
    AND onusdata.lan != '' 
    AND onusdata.lan != '0'";

$new_masiv = [];
$uniqueCoordinates = [];
if(isset($sqlmaponu) && $sqlmaponu){
$getmaponu = $db->SimpleWhile($sqlmaponu);
if (isset($getmaponu) && count($getmaponu) > 0) {
    foreach ($getmaponu as $onu) {
		$onukey = (!empty($onu['mac']) ? $onu['mac'] : (!empty($onu['sn']) ? $onu['sn'] : null));
        if (!empty($onu['lan']) && !empty($onu['lon'])) {
            $latitude = $onu['lan'];
            $longitude = $onu['lon'];
			$coordinatesKey = md5($latitude.$longitude);
            $uniqueCoordinates[$coordinatesKey] = true;
            $new_masiv[] = [
                'id' => $onu['idonu'],
                'onukey' => $onu['onukey'],
                'lan' => $latitude,
                'lon' => $longitude,
                'ont' => $onu,
                'temp' => $onu
            ];
        }
    }
}
}
if (!empty($new_masiv)) {
    foreach ($new_masiv as $onu) {
        if (!empty($onu['lan']) && !empty($onu['lan'])) {
            $view = ' ';
            $iconHtml = getMapOnu(($onu['ont']['status'] == 1 ? $onu['ont']['rx'] : 0), $view, $onu['ont']['status'], $onu['ont']['reason']);
            $popupContent = "<div class=\"div-l\"><a href=\"/?do=onu&id={$onu['ont']['idonu']}\">{$onu['ont']['mac']}{$onu['ont']['sn']}</a><br>";
            $popupContent .= "{$onu['ont']['type']} {$onu['ont']['inface']}<br><b>{$lang['signal']}:</b> " . ($onu['ont']['status'] == 1 ? $onu['ont']['rx'] : 0) . " dbm<br><b>{$lang['distance']}:</b> {$onu['ont']['dist']}" . (!empty($onu['ont']['tag']) ? "<br><b>{$onu['ont']['tag']}</b>" : "");
            $popupContent .= (isset($onu['ont']['status']) && $onu['ont']['status'] == 1 ? '<br><b>' . $lang['online'] . ':</b> ' . aftertime($onu['ont']['online']) : '<br><b>' . $lang['offline'] . ':</b> ' . aftertime($onu['ont']['offline'])) . "</div>";
            $mapont .= "var onu_{$onu['ont']['idonu']} = L.marker([{$onu['lan']},{$onu['lon']}], {icon: L.divIcon({className: 'ont', html: '{$iconHtml}'})})";
            $mapont .= ".bindTooltip('{$onu['ont']['type']} {$onu['ont']['inface']}<br><b>{$onu['ont']['mac']}{$onu['ont']['sn']}</b><br><b>{$lang['signal']}:</b> " . ($onu['ont']['status'] == 1 ? $onu['ont']['rx'] : 0) . " dbm<br><b>{$lang['distance']}:</b> {$onu['ont']['dist']}" . (!empty($onu['ont']['tag']) ? "<br><b>{$onu['ont']['tag']}</b>" : "") . (isset($onu['ont']['status']) && $onu['ont']['status'] == 1 ? '<br><b>' . $lang['online'] . ':</b> ' . aftertime($onu['ont']['online']) : '<br><b>' . $lang['offline'] . ':</b> ' . aftertime($onu['ont']['offline'])) . "')";
            $mapont .= ".bindPopup('{$popupContent}').openPopup()";
            $mapont .= ".addTo(map);";
        }
    }	
}
return $mapont;
}
function truncateAfterDot($number, $precision = 5) {
    return (float) number_format($number, $precision, '.', '');
}
function spliter_types($types) {
	$spliter_list = [
		'0' => 'err','1' => '5*95','2' => '10*90','3' => '15*85','4' => '20*80','5' => '25*75','6' => '30*70','7' => '35*65','8' => '40*60','9' => '45*55','10' => '50*50','11' => '1/2','12' => '1/3','13' => '1/4','14' => '1/6','15' => '1/8','16' => '1/16','17' => '1/32','18' => '1/64','19' => '1*128','20' => '1*256'
	];
	return $spliter_list[$types] ?? 'err_'.$types;	
}
function spliter_list($type=array()) {
	if(isset($type['type']) && $type['type']=='vidgaluj'){
	return'<select class="select" name="ponbox_element" id="ponbox_element"><option value="0"></option>
	<option value="1" '.(isset($type['id']) && $type['id']==1?'selected':'').'>5*95</option>
	<option value="2" '.(isset($type['id']) && $type['id']==2?'selected':'').'>10*90</option>
	<option value="3" '.(isset($type['id']) && $type['id']==3?'selected':'').'>15*85</option>
	<option value="4" '.(isset($type['id']) && $type['id']==4?'selected':'').'>20*80</option>
	<option value="5" '.(isset($type['id']) && $type['id']==5?'selected':'').'>25*75</option>
	<option value="6" '.(isset($type['id']) && $type['id']==6?'selected':'').'>30*70</option>
	<option value="7" '.(isset($type['id']) && $type['id']==7?'selected':'').'>35*65</option>
	<option value="8" '.(isset($type['id']) && $type['id']==8?'selected':'').'>40*60</option>
	<option value="9" '.(isset($type['id']) && $type['id']==9?'selected':'').'>45*55</option>
	<option value="10" '.(isset($type['id']) && $type['id']==10?'selected':'').'>50*50</option>
	</select>';
	}elseif(isset($type['type']) && $type['type']=='dilnuk'){
	return'<select class="select" name="ponbox_element" id="ponbox_element"><option value="0"></option>
	<option value="11" '.(isset($type['id']) && $type['id']==11?'selected':'').'>1/2</option>
	<option value="12" '.(isset($type['id']) && $type['id']==12?'selected':'').'>1/3</option>
	<option value="13" '.(isset($type['id']) && $type['id']==13?'selected':'').'>1/4</option>
	<option value="14" '.(isset($type['id']) && $type['id']==14?'selected':'').'>1/6</option>
	<option value="15" '.(isset($type['id']) && $type['id']==15?'selected':'').'>1/8</option>
	<option value="16" '.(isset($type['id']) && $type['id']==16?'selected':'').'>1/16</option>
	<option value="17" '.(isset($type['id']) && $type['id']==17?'selected':'').'>1/32</option>
	<option value="18" '.(isset($type['id']) && $type['id']==18?'selected':'').'>1/64</option>
	<option value="19" '.(isset($type['id']) && $type['id']==19?'selected':'').'>1/128</option>
	<option value="20" '.(isset($type['id']) && $type['id']==20?'selected':'').'>1/256</option>
	</select>';	
	}
}
function calculateLineDistance($geo) {
    // Видалимо зайві символи (квадратні дужки та крапку з комою) та пробіли
    $geo = str_replace(['[', ']', ';', ' '], '', $geo);

    // Розділити рядок на масив точок
    $pointStrings = explode(',', $geo);

    // Перетворити рядки у числа та групувати їх по парах
    $points = [];
    for ($i = 0; $i < count($pointStrings); $i += 2) {
        $lat = floatval($pointStrings[$i]);
        $lon = floatval($pointStrings[$i + 1]);
        $points[] = [$lat, $lon];
    }
    // Радіус Землі в метрах (приблизно)
    $earthRadius = 6371000; // 1 км = 1000 метрів

    $totalDistance = 0;

    // Обчислюємо відстань між послідовними точками у масиві
    for ($i = 0; $i < count($points) - 1; $i++) {
        $lat1 = $points[$i][0];
        $lon1 = $points[$i][1];
        $lat2 = $points[$i + 1][0];
        $lon2 = $points[$i + 1][1];

        // Радіани для широт і довгот
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Різниця в радіанах між точками
        $latDiff = $lat2 - $lat1;
        $lonDiff = $lon2 - $lon1;

        // Формула Гаверсінуса
        $a = sin($latDiff / 2) * sin($latDiff / 2) + cos($lat1) * cos($lat2) * sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Відстань між точками в метрах
        $distanceInMeters = $earthRadius * $c;

        // Додати відстань до загальної відстані
        $totalDistance += $distanceInMeters;
		// Закруглити відстань до більшого значення і перетворити в ціле число
		$roundedDistance = ceil($totalDistance);
    }

    return $roundedDistance;
}
function validateGeoString($geo) {
    // Перевірка та додавання префіксу "[[" при необхідності
    if (strpos($geo, '[[') !== 0) {
        $geo = '[[' . $geo;
    }
    // Перевірка та додавання суфіксу "]];" при необхідності
    if (strpos($geo, ']];') === false) {
        $geo .= ']];
';
    }
    return $geo;
}
function allkabelarray(){
	global $db;
	$masiv = array();
	$kab = $db->SimpleWhile("SELECT * FROM kabel");
	if(is_array($kab)){
		foreach($kab as $k){
			$masiv[$k['id']]['name'] = $k['name'];
			$masiv[$k['id']]['color'] = $k['color'];
			$masiv[$k['id']]['volokon'] = $k['volokon'];
			$masiv[$k['id']]['modules'] = $k['modules'];
				$vols = $db->SimpleWhile("SELECT * FROM kabel_volokno WHERE kabelidid = ".$k['id']);
				if(is_array($vols)){
					foreach($vols as $v){
						$masiv[$k['id']]['vols'][$v['moduleid']][$v['voloknoid']]['color'] = $v['voloknocolor'];
					}					
				}
		}
	}
	return $masiv;
}
function zvarkavolokon($id){
	global $db;
	$masiv = "[
    { start: 'volokno-161411', end: 'volokno-161613', color: 'tomato'},
    { start: 'volokno-161612', end: 'volokno-161513', color: '#5992dd'},
    { start: 'volokno-161413', end: 'volokno-161511', color: '#5992dd'},
    { start: 'volokno-161614', end: 'volokno-161414', color: '#3F51B5'}
	]";
	return $masiv;
}
function getMapLocation(){
	global $db;
	$masivLocation = array();
	$location = getListLocations();
	if(is_array($location)){
		foreach($location as $loc){
			$masivLocation[$loc['id']]['id'] = $loc['id'];
			$masivLocation[$loc['id']]['name'] = $loc['name'];
			$masivLocation[$loc['id']]['lan'] = $loc['lan'];
			$masivLocation[$loc['id']]['lon'] = $loc['lon'];
		}
	}
	return $masivLocation;
}
function connPillar(PDO $pdo, int $elementId): string {
	 $result = '<div class="list_pillar_ip">
					<img src="../style/img/l_prov_0.png">
					<a href="/">Чернівецький РЕМ<span>опора №45 лінія 4</span></a>
						</div>';
	return $result;
}
function connFelementBulk(PDO $pdo, array $elementIds): array {
    $result = [];
    $elementIds = array_values(array_unique(array_filter(array_map('intval', $elementIds), static function ($v) {
        return $v > 0;
    })));
    if (empty($elementIds)) {
        return $result;
    }
    foreach ($elementIds as $eid) {
        $result[$eid] = [];
    }
    $makePlaceholders = static function (array $ids, string $prefix): array {
        $ph = [];
        $params = [];
        foreach ($ids as $i => $val) {
            $key = ":{$prefix}{$i}";
            $ph[] = $key;
            $params[$key] = (int)$val;
        }
        return [$ph, $params];
    };
    $marks = implode(',', array_fill(0, count($elementIds), '?'));
    $sqlFibers = "SELECT id, name, kabel, geo, conn1, conn2 FROM fibers WHERE conn1 IN ($marks) OR conn2 IN ($marks)";
    $stmt = $pdo->prepare($sqlFibers);
    $stmt->execute(array_merge($elementIds, $elementIds));
    $fibers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$fibers) {
        return $result;
    }
    $relatedIds = [];
    foreach ($fibers as $f) {
        $relatedIds[(int)$f['conn1']] = true;
        $relatedIds[(int)$f['conn2']] = true;
    }
    $relatedIds = array_values(array_filter(array_keys($relatedIds), static function ($v) {
        return $v > 0;
    }));
    if (empty($relatedIds)) {
        return $result;
    }
    [$relPh, $relParams] = $makePlaceholders($relatedIds, 'rid');
    $stmt = $pdo->prepare("SELECT id, name FROM ponelement WHERE id IN (" . implode(',', $relPh) . ")");
    $stmt->execute($relParams);
    $peRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ponelementById = [];
    foreach ($peRows as $row) {
        $ponelementById[(int)$row['id']] = ['id' => (int)$row['id'], 'name' => (string)$row['name']];
    }
    $stmt = $pdo->prepare("SELECT id, elementid, connect1, connect2 FROM ponmap_connect WHERE elementid IN (" . implode(',', $relPh) . ")");
    $stmt->execute($relParams);
    $mapRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mapByElement = [];
    foreach ($mapRows as $m) {
        $eid = (int)$m['elementid'];
        $mapByElement[$eid][] = [
            'id' => (int)$m['id'],
            'connect1' => (string)$m['connect1'],
            'connect2' => (string)$m['connect2'],
        ];
    }
    $idSet = array_fill_keys($elementIds, true);
    foreach ($fibers as $f) {
        $fid = (int)$f['id'];
        $c1 = (int)$f['conn1'];
        $c2 = (int)$f['conn2'];
        $pairs = [];
        if (isset($idSet[$c1]) && $c2 > 0) {
            $pairs[] = [$c1, $c2];
        }
        if (isset($idSet[$c2]) && $c1 > 0 && $c1 !== $c2) {
            $pairs[] = [$c2, $c1];
        }
        if (empty($pairs)) {
            continue;
        }
        $delId = null;
        $candidates = array_merge($mapByElement[$c1] ?? [], $mapByElement[$c2] ?? []);
        $needle = 'v' . $fid . 'm';
        foreach ($candidates as $c) {
            if (
                (isset($c['connect1']) && strncmp($c['connect1'], $needle, strlen($needle)) === 0) ||
                (isset($c['connect2']) && strncmp($c['connect2'], $needle, strlen($needle)) === 0)
            ) {
                $delId = $c['id'];
                break;
            }
        }
        foreach ($pairs as $pair) {
            [$owner, $other] = $pair;
            if (!isset($ponelementById[$other])) {
                continue;
            }
            $result[$owner][$other] = [
                'id' => $ponelementById[$other]['id'],
                'name' => $ponelementById[$other]['name'],
                'del' => $delId,
                'fiber' => $f['name'] ?? null,
                'kabelid' => isset($f['kabel']) ? (int)$f['kabel'] : null,
                'fiberid' => $fid,
                'geo' => $f['geo'] ?? null,
            ];
        }
    }
    return $result;
}
function connFelement(PDO $pdo, int $elementId): array {
    $all = connFelementBulk($pdo, [$elementId]);
    return $all[$elementId] ?? [];
}
function mapper_edit_fiber($fibers_id,$position,$fibersgeo,$zoom,$mapper,$marker,$polyline,$lastfibers){
global $lang;
return <<<HTML
<script src="../style/pon/leaflet-editable-polyline.js"></script>
<div id="module_map"><div id="map"></div></div>
<script>
var map = L.map('map');
map.setView([{$position}],{$zoom});
var coordinates1 = [{$fibersgeo}];
var polyline_edit = L.Polyline.PolylineEditor(coordinates1, {maxMarkers: 100}).addTo(map);
{$mapper}
{$marker}
{$polyline}
{$pontreeid}
map.fitBounds(polyline_edit.getBounds());
var dumpPoints = function() {
var pointsTextArea = '';
map.getEditablePolylines().forEach(function(polyline_edit) {
var points = polyline_edit.getPoints();
points.forEach(function(point) {
var latLng = point.getLatLng();
pointsTextArea += '[' + latLng.lat + ',' + latLng.lng + '],';
});
});
$('#save').html('<img src="../style/img/accept.png">');
$.post("/?do=fiber&act=saveposition",{id:{$fibers_id},geo:pointsTextArea});
setTimeout(sayHi,1000);
};
</script>
HTML;
}
function getPonElement($type = '', $requiredTypes = []) {
    $options = [
        1 => 'Оптична муфти (Прохідна)',
        3 => 'Оптична муфта (Тупикова)',
        2 => 'PON бокс',
        17 => 'Мікро-бокс',
        18 => 'Міні-бокс',
        19 => 'Дводверний бокс',
        20 => 'Дводверний бокс',
        30 => 'Вузол зв`язку'
    ];
    if (empty($requiredTypes)) {
        $requiredTypes = array_keys($options);
    }
    $output = '<select class="select" id="types" name="types">';    
    foreach ($requiredTypes as $value) {
        if (isset($options[$value])) {
            $selected = ($type == $value) ? 'selected' : '';
            $output .= '<option value="' . $value . '" ' . $selected . '>' . $options[$value] . '</option>';
        }
    }
    $output .= '</select>';    
    return $output;
}

function getListUnit(){
	global $db;
	$list = $db->Multi('ponunit');
	return (isset($list) ? $list : false);
}
function mapper_add_tree($data) {
    global $lang;
    $formType = ($data['types'] == 'add') ? 'add' : 'edit';
    $formtypes = ($data['types'] == 'add') ? 'savetree' : 'updatetree';
	$name = (!empty($data['pon']['name'])?$data['pon']['name']:'');
	$inputid = (!empty($data['pon']['id'])?'<input type="hidden" name="id" value="'.$data['pon']['id'].'">':'');
    $form = <<<HTML
    var popup = L.popup()
    map.on('click', function(e) {
        popup.setLatLng(e.latlng).setContent('<h2>' + location_select + '</h2>' +
            '<form action="/?do=fiber&act={$formtypes}" method="post">' +
            '<input type="hidden" name="unit" value="{$data['unitid']}">' +
            '<input type="hidden" name="lan" value="' + e.latlng.lat + '">' +
            '<input type="hidden" name="lon" value="' + e.latlng.lng + '">' +
            '<input type="text" name="name" autocomplete="off" required value="{$name}"/>{$inputid}<br>' +
            '<input type="submit" value="{$lang['save']}"></form>').openOn(map);
    });
    HTML;

    return <<<HTML
    <div id="module_map"><div id="map"></div></div>
    <script>
    var location_select = '{$data['city']['name']}';
    var center = [{$data['lan']},{$data['lon']}];
    var map = L.map('map').setView(center, 16);
    {$data['mapper']}
    {$data['pontrees']}
    {$form}
    </script>
    HTML;
}



function mapper_add_vyzol($data){
$text = '<h2>'.$data['name'].'<br></h2>';
return <<<HTML
<div id="module_map"><div id="map"></div></div>
<script>
var center = [{$data['lan']},{$data['lon']}];
var map = L.map('map').setView(center, 16);
{$data['mapper']}
var popup = L.popup()
map.on('click', function(e) {
popup.setLatLng(e.latlng).setContent('<form action="/?do=fiber&act=save_unit" method="post">{$data['name']}<input type="hidden" name="lan" value="'+ e.latlng.lat +'"><input type="hidden" name="lon" value="'+ e.latlng.lng +'"><input type="hidden" name="unit" value="{$data['id']}"><input type="submit" value="Зберегти"></form>').openOn(map);
});
</script>
HTML;
}
function mapper_add($ponboxid,$lan,$lon,$mapper,$marker='',$fibers=''){
	global $lang,$db;
	$ponbox = $db->Fast('ponelement','*',['id'=>$ponboxid]);
	$text = '<h2>'.$ponbox['name'].' '.$lang['add_mapa'].'<br></h2>';
return <<<HTML
<div id="module_map"><div id="map"></div></div>
<script>
var typesbox = '{$ponboxid}';
var center = [{$lan},{$lon}];
var map = L.map('map').setView(center, 16);
{$fibers}
{$mapper}
{$marker}
var popup = L.popup()
map.on('click', function(e) {
popup.setLatLng(e.latlng).setContent('<form action="/?do=fiber&act=geo&type=1" method="post">{$text}<input type="hidden" name="lan" value="'+ e.latlng.lat +'"><input type="hidden" name="lon" value="'+ e.latlng.lng +'"><input type="hidden" name="element" value="'+ typesbox +'"><input type="submit" value="Зберегти"></form>').openOn(map);
});
</script>
HTML;
}
function getFibers($fibers,$getfiber){
$l = '';
if($fibers['types']!=1){
if(!empty($getfiber[$fibers['kabel']]['name'])){
$l = "var conn_fiber".$fibers['id']." = ".validateGeoString($fibers['geo'])."
L.polyline(conn_fiber".$fibers['id'].", {color: '".(!empty($getfiber[$fibers['kabel']]['color'])?$getfiber[$fibers['kabel']]['color']:'red')."', weight: 3, opacity: 0.7})";
if(!empty($getfiber[$fibers['kabel']]['name'])){
	$dist = calculateLineDistance($fibers['geo']);
	$l .= ".bindTooltip('<b>Кабель:</b> ".$getfiber[$fibers['kabel']]['name']." (".$dist."м)').bindPopup('<b>Кабель:</b> ".$getfiber[$fibers['kabel']]['name']."<br><b>Довжина кабелю: </b> ".$dist."м<br><b>Волоконість: </b>".$getfiber[$fibers['kabel']]['modules']*$getfiber[$fibers['kabel']]['volokon']."<br><a href=\"/?do=fiber&act=editfiber&id=".$fibers['id']."\">змінити монтаж</a> | <a href=\"/?do=fiber&act=delconnkabel&id=".$fibers['id']."\">демонтаж</a>')";
}
if(!empty($fibers['note'])){
$l .= ".setText(' ".$fibers['note']."',{offset: 10,center: true,color: 'red'})";	
}

$l .= ".addTo(map);
";
}
					
return $l;
}
}
function getFibersUnit($fibers,$getfiber){
if(!empty($getfiber[$fibers['kabel']]['name'])){
$l = "var conn_fiber_unit_".$fibers['id']." = ".validateGeoString($fibers['geo'])."
L.polyline(conn_fiber_unit_".$fibers['id'].", {color: '".(!empty($getfiber[$fibers['kabel']]['color'])?$getfiber[$fibers['kabel']]['color']:'red')."', weight: 4, opacity: 0.7})";
if(!empty($getfiber[$fibers['kabel']]['name'])){
	$dist = calculateLineDistance($fibers['geo']);
	$l .= ".bindTooltip('<b>Кабель:</b> ".$getfiber[$fibers['kabel']]['name']." (".$dist."м)').bindPopup('<b>Кабель:</b> ".$getfiber[$fibers['kabel']]['name']."<br><b>Довжина кабелю: </b> ".$dist."м<br><b>Волоконість: </b>".$getfiber[$fibers['kabel']]['modules']*$getfiber[$fibers['kabel']]['volokon']."<br><a href=\"/?do=fiber&act=editfiber&id=".$fibers['id']."\">змінити монтаж</a> | <a href=\"/?do=fiber&act=delconnkabel&id=".$fibers['id']."\">демонтаж</a>')";
}
$l .= ".addTo(map);
";	
return $l;
}
}
function get_list_kabel(){
	global $db;
	$kabel = array();
	$k = $db->Multi('kabel');
	if(count($k)){
		foreach($k as $f){
			$kabel[$f['id']] = $f;
		}
	}
	return $kabel;
}
function img_types_element($types){
	if($types==1){
		return'<img src="../style/ponmap/myfta.png">';
	}elseif($types==2){
		return'<img src="../style/ponmap/mdu.png">';
	}elseif($types==3){
		return'<img src="../style/ponmap/myfta_proxid.png">';
	}elseif($types==4){
		return'<img src="../style/ponmap/lep04.png">';
	}elseif($types==5){
		return'<img src="../style/ponmap/lep10.png">';
	}elseif($types==17){
		return'<img src="../style/ponmap/ipcamera.png">';
	}elseif($types==30){
		return'<img src="../style/ponmap/unit.png">';
	}elseif($types==18){
		return'<img src="../style/ponmap/box550.png">';
	}else{
		return'';
	}	
}
function getFibers_edit($fibers){
global $getfiber;
$l = "";
if(!empty($getfiber[$fibers['kabel']]['name'])){
$l .= "var conn_fiber".$fibers['id']." = ".validateGeoString($fibers['geo']);
$l .= "L.polyline(conn_fiber".$fibers['id'].", {color: '".(!empty($getfiber[$fibers['kabel']]['color'])?$getfiber[$fibers['kabel']]['color']:'red')."', weight: 3, opacity: 0.7})";
$l .= ".bindPopup('<b>Кабель:</b> ".$getfiber[$fibers['kabel']]['name']."<br><b>Волоконість: </b>".$getfiber[$fibers['kabel']]['modules']*$getfiber[$fibers['kabel']]['volokon']."<br><a href=\"/?do=fiber&act=editfiber&id=".$fibers['id']."\">змінити монтаж</a>')";
$l .= ".addTo(map);
";
}
return $l;
}
function types_box_add($box){
	if(!empty($box['lon']) && !empty($box['lan'])){
		if($box['types']==1){
			return "L.marker([".$box['lan'].",".$box['lon']."],{icon: min_myfta}).on('click',function(){getPonobj('".$box['id']."',this.getLatLng());}).addTo(map);
		";
		}elseif($box['types']==3){
			return "L.marker([".$box['lan'].",".$box['lon']."],{icon: min_myfta_proxid}).on('click',function(){getPonobj('".$box['id']."',this.getLatLng());}).addTo(map);
		";
		}elseif($box['types']==2){
			$status = (isset($box['status'])?$box['status']==1?'m_on':'m_off':'m_empty');
			return "L.marker([".$box['lan'].",".$box['lon']."],{icon: L.divIcon({html:'<div class=\"mdumap {$status}\">".$box['name']."</div>'})}).on('click',function(){getPonobj('".$box['id']."',this.getLatLng());}).addTo(map);
		";
		}elseif($box['types']==3){
			#return "L.marker([".$box['lan'].", ".$box['lon']."],{icon: slyp1}).addTo(map);";	
		}elseif($box['types']==4){
			#return "L.marker([".$box['lan'].", ".$box['lon']."],{icon: slyp2}).addTo(map);";	
		}elseif($box['types']==5){
			#return "L.marker([".$box['lan'].", ".$box['lon']."],{icon: slyp3}).addTo(map);";	
		}else{
			
		}
	}
}
function marker_types_box_edit($box){
global $pmonimg;
$status = (isset($box['status'])?$box['status']==1?'m_on':'m_off':'m_empty');
if($box['types']==1){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: min_myfta}).on('click',function(){getPonobj('" . $box['id'] . "',this.getLatLng());}).bindTooltip('".$box['name']."').addTo(map);	
";
}elseif($box['types']==2){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: L.divIcon({html:'<div class=\"fiber_icon $status\"></div>'})}).on('click',function(){getPonobj('" . $box['id'] . "',this.getLatLng());}).bindTooltip('".$box['name']."".(isset($box['count'])?' Onu`s: '.$box['count']:'')."').addTo(map);
";	
}elseif($box['types']==3){
#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp1}).addTo(location_".$box['locations'].");";	
}elseif($box['types']==4){
#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp2}).addTo(location_".$box['locations'].");";	
}elseif($box['types']==5){
#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp3}).addTo(location_".$box['locations'].");";	
}elseif($box['types']==17){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: ipcamera}).addTo(map);	
";		
}elseif($box['types']==18){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: metalbox}).addTo(map);	
";
}else{

}
}
function marker_types_box($box){
global $pmonimg;
$status = (isset($box['status'])?$box['status']==1?'m_on':'m_off':'m_empty');
$status_js = (isset($box['status'])?$box['status']==1?"' + ponbox_on + '":"' + ponbox_off + '":"' + ponbox_empty + '");
if($box['types']==1){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: min_myfta}).on('click',function(){getPonobj('" . $box['id'] . "',this.getLatLng());}).bindTooltip('".$box['name']."').addTo(map);
";
}elseif($box['types']==2){
return "var ponbox_".$box['id']." = L.marker([".$box['lan'].",".$box['lon']."],{icon: L.divIcon({html:'<div class=\"fiber_icon {$status}\">{$status_js}</div>'})}).on('click',function(){getPonobj('" . $box['id'] . "',this.getLatLng());}).bindTooltip('".$box['name']."".(isset($box['count'])?' Onu`s: '.$box['count']:'')."').addTo(map);
";	
}elseif($box['types']==3){
#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp1}).addTo(location_".$box['locations'].");";	
}elseif($box['types']==4){
#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp2}).addTo(location_".$box['locations'].");";	
}elseif($box['types']==5){
#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp3}).addTo(location_".$box['locations'].");";	
}elseif($box['types']==17){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: ipcamera}).addTo(tree".$box['tree'].");";	
}elseif($box['types']==18){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: metalbox}).addTo(tree".$box['tree'].");";	
}else{

}
}
function marker_types_eth($box){
global $pmonimg;
if($box['types']==1){
return "L.marker([".$box['lan'].",".$box['lon']."],{icon: L.divIcon({html:'<div class=\"switch_icon\"></div>'})}).bindTooltip('".$box['name']."').addTo(map);
";
}elseif($box['types']==2){

}else{

}
}
function marker_types_box2($box){
	if($box['types']==1){
		return "L.marker([".$box['lan'].",".$box['lon']."],{icon: min_myfta}).on('click',function(){getPonobj('" . $box['id'] . "',this.getLatLng());}).bindTooltip('".$box['name']."').addTo(tree".$box['tree'].");\r\n";
	}elseif($box['types']==2){
		return "L.marker([".$box['lan'].",".$box['lon']."],{icon: min_mdu}).on('click',function(){getPonobj('".$box['id']."',this.getLatLng());}).bindTooltip('".$box['name']."').addTo(tree".$box['tree'].");\r\n";
	}elseif($box['types']==3){
		#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp1}).addTo(location_".$box['locations'].");";	
	}elseif($box['types']==4){
		#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp2}).addTo(location_".$box['locations'].");";	
	}elseif($box['types']==5){
		#return "L.marker([".$box['lan'].",".$box['lon']."],{icon: slyp3}).addTo(location_".$box['locations'].");";	
	}elseif($box['types']==17){
		return "L.marker([".$box['lan'].",".$box['lon']."],{icon: ipcamera}).addTo(tree".$box['tree'].");";	
	}elseif($box['types']==18){
		return "L.marker([".$box['lan'].",".$box['lon']."],{icon: metalbox}).addTo(tree".$box['tree'].");";	
	}else{
		
	}
}

function mapper_view($data,$mapper,$latitude,$longitude,$var_marker,$marker,$add_map,$polylineFiber,$addLayer,$removeLayer,$var_gr){
	global $pmonimg;
	$marker_onu  ='';
	if(isset($data['onu']) && !empty($data['onu'])){
		$marker_onu .= $data['onu'];
	}	
	if(isset($data['onu_fiber']) && !empty($data['onu_fiber'])){
		$marker_onu .= $data['onu_fiber'];
	}
	if(isset($data['unit_id']) && $data['unit_id']>0){
		$get = "?unit=".$data['unit_id'];
	}
return <<<HTML
{$data['js']}
<div id="module_map"><div id="map"></div></div>
<script>
var ponbox_off = '{$pmonimg['svg']['ponbox_m_off']}';
var ponbox_empty = '{$pmonimg['svg']['ponbox_m_empty']}';
var ponbox_on = '{$pmonimg['svg']['ponbox_m_on']}';
var unit_img = '{$pmonimg['svg']['unit_map']}';
{$var_marker}
{$var_gr}
var map = L.map('map').setView([{$latitude},{$longitude}],{$data['zoom']});
{$data['mapper']}
var popup = L.popup();
{$marker_onu}
{$marker}
{$add_map}
{$polylineFiber}
map.on('zoomend', function() {
if (map.getZoom() < 14) {
{$addLayer}
} else {
{$removeLayer}
}
});
map.on('contextmenu', function(e) {
$.post('ajax/fiber.php{$get}', {
	lan: e.latlng.lat,
	lon: e.latlng.lng
}, function(result) {
	L.popup().setLatLng(e.latlng).setContent(result).openOn(map);
});
});
</script>
HTML;
}
function ponjs(){
$time =  time();
return'
<script src="../style/pon/leaflet.js?do='.md5($time).'"></script>	
<script src="../style/pon/leaflet.textpath.js"></script>
<script src="../style/pon/fiber.js?do='.md5($time).'"></script>
';
}
?>
