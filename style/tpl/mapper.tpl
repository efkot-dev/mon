<div id="onumapper" style="height:100px;width:400px;"></div>
<link rel="stylesheet" href="../style/map/leaflet.css"/>
<script src="../style/map/leaflet.js"></script>
<script>
var lat = "{maplan}"; 
var lon = "{maplon}";
var map = L.map('onumapper');
map.setView([lat, lon], 17);
{marker}
{addonumap}
</script>