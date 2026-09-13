<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array('title'=>'Mapper ','description'=>'Mapper','page'=>'mappers');
if(!$access->get('location')){
	$go->redirect('main');
}
$mapont = '';
$markers = '';
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if($id){
	$getdevice = $db->Fast('switch','*',['id'=>$id]);
}
if(!empty($getdevice['id'])){
	if(!empty($getdevice['location'])){
		$getlocation = $db->Fast('location','*',['id'=>$getdevice['location']]);
	}
$gpslan = (!empty($getlocation['lan'])?$getlocation['lan']:$config['geo_lan']);
$gpslon = (!empty($getlocation['lon'])?$getlocation['lon']:$config['geo_lon']);
$getmapper = $db->Fast('geodevice','*',['deviceid'=>$getdevice['id']]);
if(!empty($getmapper['id'])){
	$mapont .= "L.marker([".$getmapper['lan'].",".$getmapper['lon']."],{icon: L.divIcon({className: 'mapper', html: '<div class=\"mappericon\"><img src=\"../style/img/database.png\"></div>'})})";
	$mapont .= ".bindTooltip('".$getmapper['name']."<br>')";
	$mapont .= ".bindPopup('<div class=\"div-l\"><a href=\"/?do=detail&act=".$getmapper['device']."&id=".$getmapper['deviceid']."\">".$getmapper['name']."</a><br></div>').openPopup()";
	$mapont .= ".addTo(map);";
}
if(isset($act) && $act=='add'){
$markers .= <<<HTML
var popup = L.popup();	
function onMapClick(e) {
var lat = e.latlng.lat.toFixed(6);
var lon = e.latlng.lng.toFixed(6);
popup
.setLatLng(e.latlng)
.setContent('<b>{$lang['add_geo_base']}</b> <br>' +
'<input id="lan" name="lan" type="hidden" value="' + lat + '"><input name="lon" id="lon" type="hidden" value="' + lon + '"><span class="koomap"><b>{$lang['geo']}</b>: ' + lat + ' ' + lon + '</span><br>' +
'<button type="submit" class="cssadd" onclick="addmapper({$id})">{$lang['save']}</button>')
.openOn(map);
}
map.on('click', onMapClick);
HTML;
}
$zoom = '15';
$mapper = getMap();
$mapjs = <<<HTML
<script>
	var lat = '$gpslan'; 
	var lon = '$gpslon';
	var map = L.map('mapper');
	map.setView([lat, lon], {$zoom});
	{$mapper}{$markers}{$mapont}
</script>
HTML;
$blockurl ='';
$mapdiv = '<div id="mapper"></div>';
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