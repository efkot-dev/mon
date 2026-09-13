var newIcon = L.icon({iconUrl: '../style/ponmap/tochka.png',iconSize: [8, 8]}); 
var min_myfta = L.icon({iconUrl: '../style/ponmap/min_myfta.png',iconSize: [22,37],iconAnchor: [12,37]});
var min_myfta_proxid = L.icon({iconUrl: '../style/ponmap/min_myfta_proxid.png',iconSize: [22,37],iconAnchor: [12,37]});
var myfta = L.icon({iconUrl: '../style/ponmap/myfta.png',iconSize: [22,22],iconAnchor: [11, 38]});
var mdu = L.icon({iconUrl: '../style/ponmap/mdu.png',iconSize: [32,32],iconAnchor: [15, 10]});
var min_mdu = L.icon({iconUrl: '../style/ponmap/min_mdu.png',iconSize: [22,37],iconAnchor: [12,37]});
var slyp1 = L.icon({iconUrl: '../style/ponmap/lep.png',iconSize: [22,38],iconAnchor: [11, 38]});
var slyp2 = L.icon({iconUrl: '../style/ponmap/lep04.png',iconSize: [22,38],iconAnchor: [11, 38]});
var slyp3 = L.icon({iconUrl: '../style/ponmap/lep10.png',iconSize: [22,38],iconAnchor: [11, 38]});
var ipcamera = L.icon({iconUrl: '../style/ponmap/ipcamera.png',iconSize: [22,38],iconAnchor: [11, 38]});

var onu = L.icon({iconUrl: '../style/ponmap/onu.png',iconSize: [22,38],iconAnchor: [11, 38]});
var ubnt = L.icon({iconUrl: '../style/ponmap/ubnt.png',iconSize: [22,38],iconAnchor: [11, 38]});
var fiber = L.icon({iconUrl: '../style/ponmap/fiber.png',iconSize: [22,38],iconAnchor: [11, 38]});
var device = L.icon({iconUrl: '../style/ponmap/device.png',iconSize: [32,32],iconAnchor: [14, 16]});
var switch24 = L.icon({iconUrl: '../style/ponmap/switch24.png',iconSize: [32,32],iconAnchor: [14, 8]});
var myfta_proxid = L.icon({iconUrl: '../style/ponmap/myfta_proxid.png',iconSize: [22,38],iconAnchor: [11, 38]});
var box550 = L.icon({iconUrl: '../style/ponmap/box550.png',iconSize: [32,32],iconAnchor: [11,8]});

var metalbox = L.icon({iconUrl: '../style/ponmap/minbox.png',iconSize: [22,38],iconAnchor: [11, 38]});

function mapsaveelement() {
	var lan = $('#lan').val();	
	var lon = $('#lon').val();	
	var gettypes = $('#types').val();	
	var gettree = $('#gettree').val();	
	var unit = $('#unit').val();
	var name = $('#name').val();
	$.post('/?do=fiber', {act:'element',name:name,lan:lan,lon:lon,gettypes:gettypes,gettree:gettree,unit:unit});
	window.location.replace('/?do=fiber&act=map&unit=' + unit + '');
}
function showCustomPopup(latlng, content, map) {
    if (map._popup) {
        map.closePopup();
    }
    var popup = L.popup({
        maxWidth: 500, // Максимальна ширина popup
        className: 'custom-popup', // Додаємо клас для кастомної стилізації
        closeButton: true // Додаємо кнопку для закриття popup
    })
    .setLatLng(latlng)
    .setContent(content)
    .openOn(map);
}
function sendconnectfibber(id) {
    var kabel    = $('#kabel').val();
    var gettypes = $('#gettypes').val();
    var gettree  = $('#gettree').val();
    $.post('/?do=fiber', {
        act: 'savefiber',
        gettypes: gettypes,
        kabel: kabel,
        id: id,
        gettree: gettree
    }, function (response) {
        if (response.trim() === 'ok') {
            location.href = location.pathname + location.search + "&rnd=" + Date.now();
        } else {
            alert("Помилка при збереженні з'єднання");
        }
    });
}
function connunitfiber(id) {
	var kabel = $('#kabel').val();
	var gettypes = $('#gettypes').val();
	var gettree = $('#gettree').val();
	$.post('/?do=fiber', {act:'savefiberunit',gettypes:gettypes,kabel:kabel,id:id,gettree:gettree});
	$(location).attr('href');
	location.reload(); 
}
function connectfiber(id,latlng) {
	$("#keyconnectfiber").hide();
    $.post('/ajax/fiber.php', { get: 'connectfiber',  id: id }, function(response) {
	$("#connectfiber").html(response);
	}, "html");
}
function connectfiberunit(id,latlng) {
	$("#keyconnectfiber").hide();
    $.post('/ajax/fiber.php', { get: 'connectfiberunit',  id: id }, function(response) {
	$("#connectfiber").html(response);
	}, "html");
}
function opisfiber(id,latlng) {
	$("#keydescr").hide();
    $.post('/ajax/fiber.php', { get: 'opisfiber',  id: id }, function(response) {
	$("#opis_element_" + id ).html(response);
	}, "html");
}
function connectonu(id,latlng) {
	$("#keydescr").hide();
    $.post('/ajax/fiber.php', { get: 'connectonu',  id: id }, function(response) {
	$("#opis_element_" + id ).html(response);
	}, "html");
}
function savedescrfiber(id,latlng) {
	$("#keydescr").show();
	var descr = $('#description_' + id).val();
    $.post('/ajax/fiber.php', { get: 'saveopisfiber',  id: id ,  descr: descr }, function(response) {
	$("#opis_element_" + id ).html(response);
	}, "html");
}



function selectponelement(lan,lon,unit) {
    $.post('/ajax/fiber.php', { get: 'selectelement', lan: lan , lon: lon , unit: unit }, function(response) {
	$("#map-menu").html(response);
	}, "html");
}
function selectpononu(lan,lon,unit) {
    $.post('/ajax/fiber.php', { get: 'selectpononu', lan: lan , lon: lon , unit: unit }, function(response) {
	$("#map-menu").html(response);
	}, "html");
}
function selecteth(lan,lon,unit) {
    $.post('/ajax/fiber.php', { get: 'selecteth', lan: lan , lon: lon , unit: unit }, function(response) {
	$("#map-menu").html(response);
	}, "html");
}
function selectvyzol(lan,lon,unit) {
    $.post('/ajax/fiber.php', { get: 'selectvyzol', lan: lan , lon: lon , unit: unit }, function(response) {
	$("#map-menu").html(response);
	}, "html");
}
function getPonobj(id,latlng) {
    $.post('/ajax/fiber.php', {get:'info',id:id},function(response){
        L.popup().setLatLng(latlng).setContent(response).openOn(map);
    });
}
function getUnitobj(id,latlng) {
    $.post('/ajax/fiber.php', {get:'detail',id:id},function(response){
        L.popup().setLatLng(latlng).setContent(response).openOn(map);
    });
}
function getlocationobj(id,latlng) {
    $.post('/ajax/fiber.php', { get: 'fibers',  id: id }, function(response) {
        L.popup()
        .setLatLng(latlng).setContent(response).openOn(map);
    });
}
function addtochka(latlng){
	var geofiber = document.getElementById('geofiber');
	var currentCoords = geofiber.value;
	var marker = L.marker(latlng, {icon: newIcon}).addTo(map);
	marker.addTo(map);		
	if (currentCoords.length > 0) {
		currentCoords += ',';
	}
	currentCoords += '[' + latlng.lat + ',' + latlng.lng + ']';
	geofiber.value = currentCoords;	
	polylineCoords.push(latlng);
	updatetochka();
}
function updatetochka() {
	polylineLayer.clearLayers();
	var polyline = L.polyline(polylineCoords);
	polyline.addTo(polylineLayer);
}