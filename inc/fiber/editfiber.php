<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
function mapper_edit_fiber_ols($fibers_id,$position,$fibersgeo,$zoom,$mapper,$marker,$polyline,$lastfibers){
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
$zoom = 14;
$marker = '';
$polyline = '';
$lastfibers = '';
$fibersgeo = '';
$metatags = array('title'=>''.$lang['volsmeraja'].'','description'=>''.$lang['volsmeraja'].'','page'=>'editunit');
$fibers = $db->Fast('fibers','*',['id'=>$id]);
$gettree = $db->Fast('pontree','*',['id'=>$fibers['tree1']]);
$ponunit = $db->Fast('ponunit','*',['id'=>$gettree['unit_id']]);
$position = ''.$gettree['lan'].','.$gettree['lon'].'';
$mapper = getMap();
		$sqlponelement = $db->Multi('ponelement','*',['tree'=>$gettree['id']]);
		if(count($sqlponelement)>0){
			foreach($sqlponelement as $ponelem){
				if(!empty($ponelem['lan']) && !empty($ponelem['lon']) ){
					$marker .= marker_types_box_edit($ponelem);
				}
			}
		}
		$query = "SELECT id, name, geo, kabel, km, location1, tree1, conn1, location2, tree2, conn2 FROM fibers WHERE (tree1 = " . $gettree['id'] . " OR tree2 = " . $gettree['id'] . ")";
		$sqlponfiber = $db->SimpleWhile($query);
		if(empty($gettree['lan']) && empty($gettree['lon'])){
			preg_match('/\[(\d+\.\d+),(\d+\.\d+)]/', $fibers['geo'], $matches);
			if(isset($matches[1]) && isset($matches[2])){
				$position = ''.$matches[1].','.$matches[2].'';
			}
		}
		if(isset($sqlponfiber) && count($sqlponfiber)>0){
			$getfiber = get_list_kabel();
			foreach($sqlponfiber as $fiber){
				if(!empty($fiber['geo']) && $fiber['id']!==$fibers['id']){
					$polyline .= getFibers_edit($fiber);
				}
			}
		}
$bar_tpl .= '
	<STYLE>body {overflow: hidden;}</STYLE>
	<div class="link_app">
		<div class="link_menu_app">
			<div class="m_editor">
				<a class="pen" href="/?do=pon"><i class="fi fi-rr-grid"></i>'.$lang['volsmeraja'].'</a>
				<a class="pen" href="/?do=fiber&act=unit"><i class="fi fi-rr-list"></i>'.$lang['list'].'</a>
				<a class="pen" href="/?do=fiber&act=map&unit='.$ponunit['id'].'"><i class="fi fi-rr-chart-tree"></i>'.$ponunit['name'].'</a>
			</div>
		</div>
	</div>
';
		$resutltpl .= ponjs();
		$btnsave = "<div id=\"save\"><a class=\"btnsave\" href=\"javascript:void(dumpPoints())\"><img src=\"../style/img/save.png\"></a></div>";
		$fibergeo = str_replace(']];',']', str_replace('[[','[', $fibers['geo']));
		$resutltpl .= $btnsave.mapper_edit_fiber_ols($id,$position,$fibergeo,$zoom,$mapper,$marker,$polyline,$lastfibers);
?>