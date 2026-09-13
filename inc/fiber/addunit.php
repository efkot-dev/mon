<?php
if (!defined('PONMONITOR') && !defined('FIBER')) {
    die('Hacking attempt!');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
    $lan_raw = isset($_POST['lan']) ? trim((string)$_POST['lan']) : '';
    $lon_raw = isset($_POST['lon']) ? trim((string)$_POST['lon']) : '';
    $location_id = isset($_POST['location']) ? (int)$_POST['location'] : 0;
    $description = isset($_POST['description'])? trim((string)$_POST['description']) : '';
    $name = Clean::str($name);
    $description = Clean::str($description);
    $lan = ($lan_raw === '' ? null : (float)$lan_raw);
    $lon = ($lon_raw === '' ? null : (float)$lon_raw);
    $errors = [];
    if ($name === '') {
        $errors[] = 'Вкажіть назву вузла.';
    }
    if ($location_id <= 0) {
        $errors[] = 'Оберіть локацію.';
    }
    if ($lan !== null && ($lan < -90 || $lan > 90)) {
        $errors[] = 'Некоректна широта (lan).';
    }
    if ($lon !== null && ($lon < -180 || $lon > 180)) {
        $errors[] = 'Некоректна довгота (lon).';
    }
    if (!empty($errors)) {
        $error_html = '<div class="error">'.implode('<br>', array_map('htmlspecialchars', $errors)).'</div>';
    } else {
        $sql = "INSERT INTO ponunit (name, lan, lon, location, note, added)
                VALUES (:name, :lan, :lon, :location, :description, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name',$name,PDO::PARAM_STR);
        $stmt->bindValue(':lan',$lan,$lan === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':lon',$lon,$lon === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':location',$location_id, PDO::PARAM_INT);
        $stmt->bindValue(':description',$description, PDO::PARAM_STR);
        try {
            $stmt->execute();
            $go->go('?do=fiber&act=unit');
            exit;
        } catch (PDOException $e) {
            $error_html = '<div class="error">Помилка збереження. Спробуйте ще раз.</div>';
        }
    }
}
$metatags = [
    'title' => 'add unit',
    'description' => 'add unit',
    'page' => 'addunit'
];
$bar_tpl .= '
    <div class="nav-bar">
        <a href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
        <a href="/?do=fiber&act=unit"><i class="fi fi-rr-angle-left"></i>'.$lang['volsmeraja'].'</a>
        <span class="active"><i class="fi fi-rr-angle-left"></i>Новий вузол</span>
    </div>';
$zoom = 17;
$lan  = isset($ponunit['lan']) ? $ponunit['lan']
      : (isset($poncity['lan']) ? $poncity['lan']
      : ($config['geo_lan'] ?? 0));
$lon  = isset($ponunit['lon']) ? $ponunit['lon']
      : (isset($poncity['lon']) ? $poncity['lon']
      : ($config['geo_lon'] ?? 0));
$listlocation = '';
$location = getListLocations();
if (!empty($location)) {
    foreach ($location as $loc) {
        $listlocation .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
    }
}
if (!empty($error_html ?? '')) {
    $resutltpl .= $error_html;
}
$resutltpl .= '
    <div class="block_flex">
        <div class="class1">
            <div class="nav-fiber p10">
                <form action="/?do=fiber" method="post" id="formadd" autocomplete="off">
                    <input name="act" type="hidden" value="addunit">                    
                    <label for="name">'.$lang['name'].':</label>
                    <input type="text" id="name" name="name" required><br>
                    <input type="hidden" id="lan" name="lan">
                    <input type="hidden" id="lon" name="lon">
                    <label for="location">'.$lang['location'].':</label>
                    <select class="select" name="location" id="location" required>
                        <option value="0"></option>'.$listlocation.'
                    </select><br>
                    <label for="description">'.$lang['opis'].':</label>
                    <textarea id="description" name="description" rows="3"></textarea><br>

                    <input type="submit" value="'.$lang['add'].'">
                </form>
            </div>
        </div>
        <div class="class1">';
$mapper = getMap();
$lan_js = json_encode((float)$lan, JSON_UNESCAPED_UNICODE);
$lon_js = json_encode((float)$lon, JSON_UNESCAPED_UNICODE);
$mapjs = <<<HTML
<script>
  var lat = $lan_js;
  var lon = $lon_js;
  var map = L.map('divmap', { zoomControl: true });
  map.setView([lat, lon], $zoom);
  $mapper
  map.on('click', function (event) {
    var c = event.latlng;
    L.popup()
      .setLatLng(c)
      .setContent("Latitude: " + c.lat.toFixed(6) + "<br>Longitude: " + c.lng.toFixed(6))
      .openOn(map);
    document.getElementById('lan').value = c.lat.toFixed(6);
    document.getElementById('lon').value = c.lng.toFixed(6);
  });
</script>
HTML;
$resutltpl .= '
        <link rel="stylesheet" href="../style/map/leaflet.css" />
        <script src="../style/map/leaflet.js"></script>
        <script src="../style/map/mymarker.js"></script>
        <div id="divmap" style="height:400px;"></div>
        '.$mapjs.'
        </div>
    </div>';
?>
