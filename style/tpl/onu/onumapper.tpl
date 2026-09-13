<div id="onumapper" style="height:{visotakartu}px;">{editor}</div>
<script>
var lat = "{maplan}"; 
var lon = "{maplon}";
var map = L.map('onumapper');
map.setView([lat, lon], 17);
{mapper}
{marker}
var popup = L.popup();	
function onMapClick(e) {
var lat = e.latlng.lat.toFixed(6);
var lon = e.latlng.lng.toFixed(6);
popup.setLatLng(e.latlng).setContent('<input id="lan" name="lan" type="hidden" value="' + lat + '"><input id="lon" name="lon" type="hidden" value="' + lon + '"><span class="koomap"><b>[lang:geo]</b>: ' + lat + ' ' + lon + '</span><br>' +
'<button type="submit" class="cssadd" onclick="markmap({id})">[lang:save]</button>')
.openOn(map);
}
{addonumap}
</script>