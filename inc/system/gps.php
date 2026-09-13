<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if ((isset($confPMon['GPS_TRACCAR']) && !empty($confPMon['GPS_TRACCAR']) && $confPMon['GPS_TRACCAR'] == 1)
 || (isset($confPMon['GPS_TRACKER_COM_UA']) && !empty($confPMon['GPS_TRACKER_COM_UA']) && $confPMon['GPS_TRACKER_COM_UA'] == 1 )) {
$visicomkey = 'pmon';
if(isset($confPMon['VISICOM_API_KEY']) && !empty($confPMon['VISICOM_API_KEY'])){
	$visicomkey = $confPMon['VISICOM_API_KEY'];
}
$blockurl = '';
$zoom = '14';
$gpslan = $config['geo_lan'];
$gpslon = $config['geo_lon'];
$metatags = array('title'=>'GPS Locator','description'=>'GPS Locator','page'=>'gps');
$data_conf = array('map' => 'map');
$map_car = get_gps_car($data_conf);
$mapper = getMap();
$mapjs = <<<HTML
<link rel="stylesheet" href="../style/map/leaflet.css" />
<script src="../style/map/leaflet.js"></script>
<script src="../style/map/mymarker.js?fd=11"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script>
var markers = []; 
var layers = {};
var currentMap; 
var lat = '{$gpslan}';
var lon = '{$gpslon}';
var minZoomLevel = 17;
var map = L.map('mapper');
map.setView([lat, lon], {$zoom});
{$mapper}
{$map_car}
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
</script>
HTML;
$mapdiv = <<<HTML
<div id="mapper"></div>
HTML;
$tpl->load_template('map/main.tpl');
$tpl->set('{blockurl}',$blockurl);
$tpl->set('{mapjs}',$mapjs);
$tpl->set('{mapdiv}',$mapdiv);
$tpl->set('{result}','<div id="maps"></div>');
$tpl->compile('content');
$tpl->clear();
}else{
	$go->redirect('main'); 
}
?>