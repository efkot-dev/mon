<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$zoom = '14';
$gpslan = $config['geo_lan'];
$gpslon = $config['geo_lon'];
$mapper = getMap();
$mapjs = <<<HTML
var lat = '$gpslan'; 
var lon = '$gpslon';
var map = L.map('mappers');
map.setView([lat, lon], {$zoom});
{$mapper}
HTML;
?>
<!DOCTYPE html>
<html lang="ua">
<head>
<meta charset="utf-8">
<title>PMon </title>
<meta name="description" content="Map ONU " />
<script src="../style/js/jquery-3.6.0.min.js"></script>
<script src="../style/js/jquery.cookies.js"></script>
<link rel="icon" type="image/png" href="../style/img/pmon_favicon.png" sizes="32x32" />
<meta name="generator" content="PMon5">
<meta name="author" content="Momotiuk Oleksiy">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<script type="text/javascript">
var root   = "/";
var author  = "@momotuk88";
</script>
<STYLE>
html,
body {
    height: 100%;
    margin: 0;
}

#mappers {
    width: 100%;
    height: 100%;
}
.vision_1 {
	opacity: 1;
  animation: blink_onu 1s linear infinite;
}

@keyframes blink_onu {
  25% {
    opacity: 0.5;
  }
  50% {
    opacity: 0;
  }
  75% {
    opacity: 0.5;
  }
}
.wibro {
  border-radius: 10%;
  box-shadow: 1 1 1 red;
  animation: pulse 2s infinite;
  border:1px solid red;
}
.pulse:hover {
  animation: none;
}

@-webkit-keyframes pulse {
	 border:1px solid #fff;
  0% {
    -webkit-box-shadow: 0 0 0 0 rgba(204,169,44, 0.4);
  }
  70% {
      -webkit-box-shadow: 0 0 0 10px rgba(204,169,44, 0);
  }
  100% {
      -webkit-box-shadow: 0 0 0 0 rgba(204,169,44, 0);
  }
}
@keyframes pulse {
  0% {
    -moz-box-shadow: 0 0 0 0 rgba(204,169,44, 0.4);
    box-shadow: 0 0 0 0 rgba(204,169,44, 0.4);
  }
  70% {
      -moz-box-shadow: 0 0 0 10px rgba(204,169,44, 0);
      box-shadow: 0 0 0 10px rgba(204,169,44, 0);
  }
  100% {
      -moz-box-shadow: 0 0 0 0 rgba(204,169,44, 0);
      box-shadow: 0 0 0 0 rgba(204,169,44, 0);
  }
}
</STYLE>
<link rel="stylesheet" href="../style/map/leaflet.css" />
<script src="../style/map/leaflet.js"></script>
<script src="../style/map/mymarker.js"></script>
<link href="../style/css/styles.css" type="text/css" rel="stylesheet" />
<div id="mappers"></div>

<script>
<?php echo $mapjs; ?>

function fetchDataAndDisplayMarkers() {
    fetch('ajax/vision.php')
        .then(response => response.json())
        .then(data => {
            displayMarkers(data);
            setInterval(fetchDataAndDisplayMarkers, 3 * 60 * 1000); // Оновлення даних кожні 5 хвилин
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
}

function displayMarkers(data) {
    clearMap();
    let groupIndex = 0;
    let groups = Object.values(data);
    function displayMarkersForGroup() {
        let group = groups[groupIndex];
        let markers = [];

        if (group.hasOwnProperty('onu') && Array.isArray(group.onu)) {
            group.onu.forEach(device => {
                let coords = device.geo.split(',');
                let marker = L.marker(coords, { icon: L.divIcon({ className: 'ont', html: device.icon }) }).addTo(map);
                markers.push(marker);
            });
            setTimeout(() => {
                let firstDeviceCoords = group.onu[0].geo.split(',');
                map.setView([parseFloat(firstDeviceCoords[0]), parseFloat(firstDeviceCoords[1])], 14);
            }, 3 * 1000);
        }

        if (groupIndex < groups.length - 1) {
            groupIndex++;

            setTimeout(() => {
                clearMap();
                displayMarkersForGroup();
            }, 10 * 1000); // Затримка перед відображенням наступної групи
        } else {
            groupIndex = 0;

            setTimeout(() => {
                clearMap();
                fetchDataAndDisplayMarkers(); // Оновлення даних кожні 5 хвилин після відображення всіх груп
            }, 3 * 60 * 1000); // Затримка перед перезавантаженням даних
        }
    }

    displayMarkersForGroup();
}

function clearMap() {
    map.eachLayer(layer => {
        if (layer instanceof L.Marker) {
            map.removeLayer(layer);
        }
    });
}

// Виклик функції для першого завантаження даних та відображення маркерів
fetchDataAndDisplayMarkers();

</script>

<?php
die;
?>