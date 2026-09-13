<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
$id = isset($_GET['id'])?Clean::int($_GET['id']):null;
if(!$id){
	$go->redirect('fiber');
}
$metatags = array('title'=>'add','description'=>'add','page'=>'add');
$pontree = $db->Fast('pontree','*',['id'=>$id]);
if(!empty($pontree['id'])){
	$ponunit = $db->Fast('ponunit','*',['id'=>$pontree['unit_id']]);
	$poncity = $db->Fast('location','*',['id'=>$ponunit['location']]);
	$mapper = getMap();
	$zoom = '17';
	$lan = (isset($ponunit['lan']) ?$ponunit['lan']:(isset($poncity['lan']) ?$poncity['lan']:$config['geo_lan']));
	$lon = (isset($ponunit['lon'])?$ponunit['lon']:(isset($poncity['lon']) ?$poncity['lon']:$config['geo_lan']));
	$bar_tpl .= '
		<div class="nav-bar">
			<a href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			<a href="/?do=fiber&act=unit"><i class="fi fi-rr-angle-left"></i>'.$lang['list'].'</a>
			<a href="/?do=fiber&act=viewunit&id='.$ponunit['unit_id'].'"><i class="fi fi-rr-angle-left"></i>'.$ponunit['name'].'</a>
			<a href="/?do=fiber&act=viewtree&id='.$id.'"><i class="fi fi-rr-angle-left"></i>'.$pontree['name'].'</a>
			<span class="active"><i class="fi fi-rr-angle-left"></i>'.$lang['newobj'].'</span>
		</div>';			
	$resutltpl .= '
		<div class="block_flex"><div class="class1">
			<div class="nav-fiber p10">
				<form action="/?do=fiber" method="post"><input name="act" type="hidden" value="save">
				<label for="name">'.$lang['name'].':</label><input type="text" id="name" name="name" required autocomplete="off"><br>
				<input type="hidden" id="tree" name="tree" value="'.$pontree['id'].'">
				<input type="hidden" id="unit" name="unit" value="'.$pontree['unit_id'].'">
				<input type="hidden" id="lan" name="lan">
				<input type="hidden" id="lon" name="lon">
				<label for="type">'.$lang['oid_types'].':</label>'.getPonElement().'<br>
				<label for="description">'.$lang['opis'].':</label><textarea id="description" name="description"></textarea><br>
				<input type="submit" value="'.$lang['add'].'">
				</form>
			</div>';
	$resutltpl .= '</div><div class="class1">';		
	// map
	$mapjs = <<<HTML
	<script>
	var lat = '$lan'; 
	var lon = '$lon';
	var map = L.map('divmap');
	map.setView([lat, lon], {$zoom});
	{$mapper}
	map.on('click', function(event) {
		var clickedLatLng = event.latlng;
		L.popup().setLatLng(clickedLatLng).setContent("Latitude: " + clickedLatLng.lat + "<br>Longitude: " + clickedLatLng.lng).openOn(map);
		document.getElementById('lan').value = clickedLatLng.lat;
		document.getElementById('lon').value = clickedLatLng.lng;
	});
	</script>
	HTML;
	$resutltpl .= '
		<link rel="stylesheet" href="../style/map/leaflet.css" />
		<script src="../style/map/leaflet.js"></script>
		<script src="../style/map/mymarker.js"></script>
		<div id="divmap" style="height:400px;"></div>
	'.$mapjs.'
	</div></div>';
}else{
	$go->redirect('fiber');
}
?>