<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
if(!$id){
	$go->redirect('fiber');
}
$select_pon_tree = '';
$ponelement = $db->Fast('ponelement','*',['id'=>$id]);
if(empty($ponelement['location'])){
	$ponunit = $db->Fast('pontree','*',['id'=>$ponelement['tree']]);
	if(empty($ponunit['location'])){
		$unit = $db->Fast('ponunit','*',['id'=>$ponelement['unit_id']]);
		$location = $unit['location'];
	}else{
		$location = $ponunit['location'];
	}	
}else{
	$location = $ponelement['location'];
}
if(!empty($ponelement['id'])){
$poncity = $db->Fast('location','*',['id'=>$location]);
$db_pontree = ['sql'=>'SELECT * FROM pontree WHERE id = '.$ponelement['tree'],'key' => 'pontree_'.$ponelement['tree'],'time' => 600];
$pontree = cache_simple_sql($db_pontree);
$mapper = getMap();
$zoom = '17';
$lan = (!empty($ponelement['lan']) ?$ponelement['lan']:(!empty($ponunit['lan']) ? $ponunit['lan'] : $poncity['lan']));
$lon = (!empty($ponelement['lon'])?$ponelement['lon']:(!empty($ponunit['lon']) ? $ponunit['lon'] : $poncity['lon']));
$bar_tpl .= '
	<div class="nav-bar">
		<a href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
		<a href="/?do=fiber&act=tree"><i class="fi fi-rr-angle-left"></i>'.$pontree['name'].'</a>
		<a href="/?do=fiber&act=details&id='.$ponelement['id'].'"><i class="fi fi-rr-angle-left"></i>'.$ponelement['name'].'</a>
		<span class="active"><i class="fi fi-rr-angle-left"></i>'.$lang['edit'].'</span>
	</div>';
$metatags = array('title'=>$pontree['name'].', '.$ponelement['name'],'description'=>$pontree['name'].', '.$ponelement['name'],'page'=>'edit');	
$sqlponelement = $db->Multi('pontree');
if(isset($sqlponelement) && count($sqlponelement)>0){
	$select_pon_tree .= '
	<label for="type">Pon дерево:</label>
	<select class="select" name="pontree" id="pontree">';
	foreach($sqlponelement as $pontree_all){
		$ponunit = $db->Fast('ponunit','*',['id'=>$pontree_all['unit_id']]);
		$select_pon_tree .= '<option value="'.$pontree_all['id'].'" '.($pontree_all['id']==$pontree['id']?'selected="selected"':'').'>'.$ponunit['name'].' -> '.$pontree_all['name'].'</option>';
	}
	$select_pon_tree .= '</select></br>';
}
$resutltpl .= '
<div class="block_flex">
	<div class="class1">
		<div class="nav-fiber p10">
			<form action="/?do=fiber" method="post">
			<input name="id" type="hidden" value="'.$ponelement['id'].'">
			<input name="act" type="hidden" value="update">
			<label for="name">Керування: <a onclick="return confirmpmon(\''.$lang['delet_ponbox'].'\')"  
		href="/?do=fiber&act=delete&id=' . $ponelement["id"]. '">
			Видалити елемент</a></label><br><br>
			<label for="name">'.$lang['name'].':</label>
			<input type="text" id="name" name="name" required autocomplete="off" value="'.$ponelement['name'].'"><br>
			<input type="hidden" id="tree" name="tree" value="'.$pontree['id'].'">
			<input type="hidden" id="location" name="location" value="'.$pontree['location'].'">
			<label for="type">'.$lang['oid_types'].':</label>'.getPonElement($ponelement['types']).'<br>'.$select_pon_tree.'

			<label>Icon:</label><br>
			<div id="icon_selector" style="display:flex; gap:10px; flex-wrap:wrap;">';
$selected_icon = $ponelement['myicon_file'] ?? '';
$resutltpl .= '<input type="hidden" name="myicon_file" id="myicon_file" value="'.htmlspecialchars($selected_icon).'">';
foreach($icons_pon_list as $name => $file){
    $active = ($selected_icon == $file) ? 'border:3px solid #007bff;' : 'border:1px solid #ccc;';
    $resutltpl .= '<img src="../style/ponmap/'.$file.'" data-file="'.$file.'" title="'.htmlspecialchars($name).'" class="icon_choice" style="width:40px;height:40px;cursor:pointer;'.$active.'">';
}
$resutltpl .= '</div>
<script>
document.querySelectorAll("#icon_selector .icon_choice").forEach(img => {
    img.addEventListener("click", function(){
        document.querySelectorAll("#icon_selector .icon_choice").forEach(i => i.style.border="1px solid #ccc");
        this.style.border="3px solid #007bff";
        document.getElementById("myicon_file").value = this.getAttribute("data-file");
    });
});
</script></br>
<label for="description">'.$lang['opis'].':</label>
<input type="hidden" id="lan" name="lan">
<input type="hidden" id="lon" name="lon">
<textarea id="description" name="description">'.$ponelement['description'].'</textarea><br>
<input type="submit" value="'.$lang['update'].'">
</form>
</div>
</div>
<div class="class1">';
$ponelement = (!empty($ponelement['lan']) ? "var marker = L.marker([".$ponelement['lan'].",".$ponelement['lon']."]).addTo(map);":"");
$mapjs = <<<HTML
<script>
	var lat = '$lan'; 
	var lon = '$lon';
	var map = L.map('divmap');
	map.setView([lat, lon], {$zoom});
	{$ponelement}
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
<div id="divmap" style="height:425px;"></div>
'.$mapjs.'
</div>
</div>';
}else{
	$go->redirect('fiber');
}
?>