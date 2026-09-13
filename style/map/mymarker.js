
var map_location = L.icon({iconUrl: '../style/map/images/m2x.png',iconSize: [32,38],iconAnchor: [17,17]});
var map_onu_err1 = L.icon({iconUrl: '../style/map/images/map_err1.png',iconSize: [28,28],iconAnchor: [17,17]});
var map_onu_err6 = L.icon({iconUrl: '../style/map/images/map_err6.png',iconSize: [24,24],iconAnchor: [17,17]});
var map_onu_err8 = L.icon({iconUrl: '../style/map/images/map_err8.png',iconSize: [24,24],iconAnchor: [17,17]});
var map_onu_err34 = L.icon({iconUrl: '../style/map/images/map_err34.png',iconSize: [24,24],iconAnchor: [17,17]});
var map_onu_err59 = L.icon({iconUrl: '../style/map/images/map_err59.png',iconSize: [24,24],iconAnchor: [17,17]});
var map_onu_offline = L.icon({iconUrl: '../style/map/images/map_offline.png',iconSize: [24,24],iconAnchor: [17,17]});
var map_red_box = L.icon({iconUrl: '../style/map/images/map_red_box.png',iconSize: [24,24],iconAnchor: [17,17]});

var __pmonSignalIconCache = {};

function pmonSignalPalette(signalClass) {
    switch (signalClass) {
    case 'map-signal0':
        return { bg: '#f8e71c', border: '#b89c00', text: '#1f1f1f' };
    case 'map-signal2':
        return { bg: '#278dd3', border: '#155f97', text: '#ffffff' };
    case 'map-signal3':
        return { bg: '#7ef66a', border: '#2b8b1a', text: '#133b0f' };
    case 'map-signal4':
    default:
        return { bg: '#ff5c5c', border: '#b30000', text: '#ffffff' };
    }
}

function pmonEscapeSvgText(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function pmonBuildSignalSvg(text, palette, width, height) {
    var safe = pmonEscapeSvgText(text);
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' + width + '" height="' + height + '" viewBox="0 0 ' + width + ' ' + height + '">' +
        '<rect x="0.5" y="0.5" width="' + (width - 1) + '" height="' + (height - 1) + '" rx="4" ry="4" fill="' + palette.bg + '" stroke="' + palette.border + '" stroke-width="1"/>' +
        '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="700" fill="' + palette.text + '">' + safe + '</text>' +
        '</svg>';
}

function getMapSignalIcon(signal, signalClass) {
    var text = (signal === undefined || signal === null || signal === '') ? '0' : String(signal);
    var normalized = parseFloat(String(text).replace(',', '.'));
    if (isFinite(normalized)) {
        text = String(Math.round(normalized * 10) / 10);
    }
    var cls = signalClass || 'map-signal4';
    var cacheKey = cls + '|' + text;

    if (__pmonSignalIconCache[cacheKey]) {
        return __pmonSignalIconCache[cacheKey];
    }

    var palette = pmonSignalPalette(cls);
    var width = Math.max(28, Math.min(52, 14 + (text.length * 7)));
    var height = 20;
    var svg = pmonBuildSignalSvg(text, palette, width, height);
    var url = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);

    var icon = L.icon({
        iconUrl: url,
        iconSize: [width, height],
        iconAnchor: [Math.floor(width / 2), Math.floor(height / 2)],
        popupAnchor: [0, -10],
        tooltipAnchor: [0, -10]
    });

    __pmonSignalIconCache[cacheKey] = icon;
    return icon;
}

window.getMapSignalIcon = getMapSignalIcon;

var pberr = L.icon({className: 'criticnagios',iconUrl: '../style/map/images/e2x.png',iconSize:[30,36],iconAnchor: [16, 36]});
var maplocation = L.icon({iconUrl: '../style/map/images/o2x.png',iconSize:[30,36],iconAnchor: [16, 36]});
var onu = L.icon({iconUrl: '../style/map/images/onu.png',iconSize:     [20,20]});
var cube1 = L.icon({iconUrl: '../style/img/pon/cube.png',iconSize:[9,9]});
var myftamap = L.icon({iconUrl: '../style/ponmap/mmyfta.png',iconSize: [22,38],iconAnchor:[11, 38]});
var house = L.icon({iconUrl: '../style/img/pon/house.png',iconSize: [30, 30],popupAnchor: [2, -8],iconAnchor: [10, 10]});
var mduon = L.divIcon({className: 'custom-div-icon',html: "<div class='ponbox-online'></div>",iconSize: [30, 30],popupAnchor: [2, -8],iconAnchor: [10, 10]});

var ponboxmdu = L.icon({iconUrl: '../style/img/pon/mdu.png',iconSize:[30,30],iconAnchor:[15,15],popupAnchor:[0, -15]});
var mapswitch = L.icon({iconUrl: '../style/map/images/mapswitch.png',iconSize:[30,30],iconAnchor:[15,15],popupAnchor:[0, -15]});

function set_icon(icon_type){
	var LeafIcon = L.Icon.extend({
        options:{
			iconSize:     [40, 40],
			iconAnchor:   [21,40],
			popupAnchor:  [0, -40]
        }
    });
	switch (icon_type){
    case 'box550':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/box550.png'})
    break
    case 'mdu':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/mdu.png'})
    break
    case 'myfta':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/myfta.png'})
    break    
	case 'camera':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/camera.png'})
    break	
	case 'onu':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/onu.png'})
    break		
	case 'lep04':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/lep04.png'})
    break		
	case 'lep10':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/lep10.png'})
    break	
	case 'lep':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/lep.png'})
    break	
	case 'tp':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/tp.png'})
    break		
	case 'onu':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/onu.png'})
    break		
	case 'switch24':
		var icon = new LeafIcon({iconUrl: '/style/ponmap/switch24.png'})
    break	
    default:
		var icon = new LeafIcon({iconUrl: '/style/ponmap/default.png'})
    break
	};
	return icon
};
function getMenu(types,city){
	$.post(root + 'ajax/map.php',{act:'object',types:types,city:city},function(data){
		jQuery('#display-events-menu').html(data);
	});	
};
function init_map(coordinates,div_id,default_zoom){
	map = L.map(div_id, {zoomControl:false}).setView(coordinates,default_zoom); 
	map.panTo(coordinates); 
	var googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
		maxZoom: 20,
		subdomains:['mt0','mt1','mt2','mt3']
	});
	googleHybrid.addTo(map);
	return map;
};
function init_map2(coordinates,div_id,default_zoom){
	map = L.map(div_id, {zoomControl:false}).setView(coordinates,default_zoom); 
	map.panTo(coordinates); 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
	return map;
};
