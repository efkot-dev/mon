<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
$getmap = array();
$data = array();
$locationid = (isset($_GET['locationid'])?Clean::int($_GET['locationid']):null);
$tree = (isset($_GET['tree'])?Clean::int($_GET['tree']):null);
$unit = (isset($_GET['unit'])?Clean::int($_GET['unit']):null);
$data_conf = array('map' => 'map');
$markers_js = '';
$marker_unit ='';
$mapper = '';
$marker = get_gps_car($data_conf);
$var_marker='';
$add_map='';
$polylineFiber='';
$addLayer='';
$removeLayer='';
$var_gr='';
$onu_client = '';
$where = '';
$arraytree = '';
//
if(isset($unit) && $unit>0){
	$getmap['unit_id'] = $unit;
}		
if(isset($tree) && $tree>0){
	$getmap['id'] = $tree;
	$pon_tree = $db->Fast('pontree','*',['id'=>$tree]);
}
if(isset($unit) && $unit>0){
	$ponunit = $db->Fast('ponunit','*',['id'=>$unit]);
	if(!empty($ponunit['lan']) && !empty(!empty($ponunit['lon']))){
		$latitude = $ponunit['lan'];
		$longitude = $ponunit['lon'];	
		$data['location'] = $ponunit['location'];				
		$data['unit_id'] = $ponunit['id'];	
	}			
	$sqlpontree = $db->Multi('pontree','*',$getmap);	
}else{
	$latitude = $config['geo_lan'];
	$longitude = $config['geo_lon'];
	$sqlpontree = $db->Multi('pontree');			
}
$getfiber = get_list_kabel();
$pmon_marker = get_fiber_map_pon($sqlpontree,$getfiber);
$metatags = array('title'=>'Редагування карти','description'=>'Редагування карти','page'=>'map');		
$bar_tpl .= '
	<STYLE>body {overflow: hidden;}</STYLE>
	<div class="link_app">
		<div class="link_menu_app">
			<div class="m_editor">
				<a class="pen" href="/?do=pon"><i class="fi fi-rr-grid"></i>'.$lang['volsmeraja'].'</a>
				<a class="pen" href="/?do=fiber&act=unit"><i class="fi fi-rr-list"></i>'.$lang['list'].'</a>
				<a class="pen" href="/?do=fiber&act=viewunit&id='.$ponunit['id'].'"><i class="fi fi-rr-chart-tree"></i>'.$ponunit['name'].'</a>
			</div>
		</div>
	</div>
';
$map_zoom = 15;
$map_js = ponjs();
$map_mapper = getMap();
$map_marker = $pmon_marker['marker'];
$map_optic = $pmon_marker['optic'];
if(isset($pmon_marker['marker'])){
$markers_js .= "const markers = " . json_encode($pmon_marker['marker']) . ";
markers.forEach(function(marker) {
    let icon;
    if (typeof marker.icon === 'string' && window[marker.icon]) {
        icon = window[marker.icon];
    } else {
        icon = L.divIcon({html:`<div class=\"mdumap \${marker.status}\">\${marker.label}</div>`});
    }

    L.marker([marker.lat, marker.lng], { icon: icon })
        .bindPopup(`\${marker.label}`)
        .on('click', function() { getPonobj(marker.id, this.getLatLng()); })
        .addTo(map);
});";
}
$resutltpl = <<<HTML
{$map_js}
<div id="module_map"><div id="map"></div></div>
<script>
var unit_img = '{$pmonimg['svg']['unit_map']}';
var map = L.map('map').setView([{$latitude},{$longitude}],{$map_zoom});
{$map_mapper}
{$markers_js}
{$map_optic}
{$polylineFiber}

</script>
HTML;


?>