<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require_once ENGINE_DIR.'functions/building.php';
require ENGINE_DIR.'classes/mapper.class.php';
$map = new Mapper($pdo, $lang, $confPMon, $config);
$loc = [];
$select = [];
$marker = '';
$location = $location ?? null;
$array_count = [];
$mapper = [];
$polygon = '';
$mapont = '';
$sqlmapbyd = '';
$blockurl = '';
$visicomkey = 'pmon';
if(isset($confPMon['VISICOM_API_KEY']) && !empty($confPMon['VISICOM_API_KEY'])){
$visicomkey = $confPMon['VISICOM_API_KEY'];
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$locations = isset($_GET['location']) ? Clean::int($_GET['location']) : null;
// COUNT SWITCH
$sqlountdev = getSwitchAll();		
if(isset($sqlountdev) && count($sqlountdev)>0){
	foreach($sqlountdev as $sw){
		if(!empty($sw['location'])){
			if ($access->get('dev' . $sw['id'])) {
				$array_count[$sw['location']][$sw['id']] = $sw['id'];
				if(isset($locations)){
					$sqlgeo = geoDevice($sw['id']);
					if(!empty($sqlgeo['id']) && $locations){
						$mapper[$sw['id']] = ['id'=>$sqlgeo['id'],
						'lan'=>$sqlgeo['lan'],
						'name'=>$sqlgeo['name'],
						'lon'=>$sqlgeo['lon'],
						'deviceid'=>$sqlgeo['deviceid'],
						'device'=>$sqlgeo['device']];
					}
				}
			}
		}
	}	
}
$navigation_location = '';
$sqllocation = getLocation();	
if(isset($sqllocation) && count($sqllocation)>0){
	foreach($sqllocation as $location){
		if(!empty($location['lan']) && !empty($location['lon'])){
			$loc[$location['id']] = [
				'lan' => $location['lan'],
				'lon' => $location['lon'],
				'name' => $location['name']
			];
			if($locations==$location['id']){

			}else{
				$marker .= $map->icon_location($location);	
			}
			$navigation_location .= "<a ".(isset($locations) && $locations==$location['id']?'class="act"':'')."href=\"/?do=map&location=".$location['id']."\">".$location['name']."</a>";
		}
	}
}
if(isset($locations) && $locations>0){
$sqlskyscraper = $db->SimpleWhile("SELECT * FROM skyscraper WHERE locationid = ".$locations);
if(isset($sqlskyscraper) && count($sqlskyscraper) > 0) {
    foreach($sqlskyscraper as $ho) {
        if(!empty($ho['lan']) && !empty($ho['lon'])) {
            $mapont .= create_house($ho['lan'], $ho['lon'], $ho['name'], $ho['id'], $ho['onus'], $ho['online'], $ho['offline'], $ho['photo'],$ho);
        }
    }
}
}
$sqlmaponu = '';
$select_onu = 'idonu,olt,portolt,keyonu,zte_idport,status,inface,type,mac,name,descr,sn,rx,reason,dist,name,offline,online';
if(!empty($array_count[$locations]) && is_array($array_count)){
	$olt_array = array_unique(array_values($array_count[$locations]));
	if(count($olt_array) == 1){
		$sqlmaponu = "SELECT {$select_onu} FROM onus WHERE `olt` = ".$olt_array[0];
	} else {
		$olt_values = implode(',', $olt_array);
		$sqlmaponu = "SELECT {$select_onu} FROM onus WHERE `olt` IN ($olt_values)";
	}
}
$new_masiv = [];
if(isset($sqlmaponu) && $sqlmaponu){
$getmaponu = $db->SimpleWhile($sqlmaponu);
if (isset($getmaponu) && count($getmaponu) > 0) {
    foreach ($getmaponu as $onu) {
		if ($access->get('dev' . $onu['olt'])) {
			$onukey = (!empty($onu['mac']) ? $onu['mac'] : (!empty($onu['sn']) ? $onu['sn'] : null));
			$datatemponu = getFastOnusData($onukey);
			if (!empty($datatemponu['lan']) && !empty($datatemponu['lon'])) {
				$latitude = $datatemponu['lan'];
				$longitude = $datatemponu['lon'];
				$new_masiv[] = [
					'id' => $onu['idonu'],'onukey' => $onukey,'lan' => $latitude,'lon' => $longitude,'ont' => $onu,'datatemponu' => $datatemponu
				];
			}
		}
    }
}
}
if (!empty($new_masiv)) {
    foreach ($new_masiv as $onu) {
        if (!empty($onu['lan']) && !empty($onu['lon'])) {
            $mapont .= $map->map_onu($onu);
        }
    }
}
$map_js = $map->map_js();
if(!empty($loc[$locations]['name'])){
	$gpslan = (!empty($loc[$locations]['lan'])?$loc[$locations]['lan']:$config['geo_lan']);
	$gpslon = (!empty($loc[$locations]['lon'])?$loc[$locations]['lon']:$config['geo_lon']);
	$zoom = '15';
	$metatags = array('title'=>'ONU '.$loc[$locations]['name'],'description'=>'ONU '.$loc[$locations]['name'],'page'=>'map');
}else{
	$zoom = '14';
	$gpslan = $config['geo_lan'];
	$gpslon = $config['geo_lon'];
	$metatags = array('title'=>'Map ONU ','description'=>'Map ONU ','page'=>'map');
}
$data_conf = array('map' => 'map');
$map_car = get_gps_car($data_conf);
$mapper = getMap();
$mapjs = <<<HTML
<script>
var markers = []; 
var layers = {};
var currentMap; 
var lat = '{$gpslan}';
var lon = '{$gpslon}';
var minZoomLevel = 17;
if (typeof map !== 'undefined' && map.remove) {
    map.remove();
}
var map = L.map('mapper');
map.setView([lat, lon], {$zoom});
window.onuLayer = L.layerGroup();
{$mapper}{$marker}{$polygon}{$mapont}{$map_car}
map.addLayer(window.onuLayer);
layers['openstreetmap'] = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
});
layers['vision'] = L.tileLayer('https://{s}.visicom.ua/2.0.0/planet3/base/{z}/{x}/{y}.png?key={$visicomkey}', {
    subdomains:['tms0','tms1','tms2','tms3'],
    maxZoom: 19,
    tms: true
});
layers['google'] = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
    maxZoom: 19,
    subdomains:['mt0','mt1','mt2','mt3']
});
currentMap = '{$config['typemap']}';
map.on('load', updateMarkersVisibility);
map.on('zoomend', updateMarkersVisibility);
updateMarkersVisibility();
</script>
<div id="portInfo"></div>
HTML;
$mapdiv = <<<HTML
<div id="mapControls">
<span class="mapcurrent" id="menu_1_key" onclick="toggleMenu('menu_1')">{$pmonimg['svg']['planet']}</span>
<span class="mapcurrent" id="menu_2_key" onclick="toggleMenu('menu_2')">{$pmonimg['svg']['city']}</span>
</div>
<div class="menu_content" id="menu_1_content">
<div class="flex_block">
    <a href="#" onclick="switchMap('openstreetmap')">OpenStreet</a>
    <a href="#" onclick="switchMap('vision')">Visicom</a>
    <a href="#" onclick="switchMap('google')">Google</a>
</div>
</div>

<div class="menu_content" id="menu_2_content">
<div class="flex_block">
{$navigation_location}
</div>
</div>

<div class="menu_content" id="menu_3_content">
<div class="flex_block">
<a href="">ONU LOS</a>
<a href="">ONU POWER</a>
</div>
</div>
{$map_js}
<div id="mapper"></div>
HTML;

$tpl->load_template('map/main.tpl');
$tpl->set('{blockurl}',$blockurl);
$tpl->set('{mapjs}',$mapjs);
$tpl->set('{mapdiv}',$mapdiv);
$tpl->set('{result}','<div id="maps"></div>');
$tpl->compile('content');
$tpl->clear();
?>
