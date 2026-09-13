<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

$speedbar = '';
$result = '';
switch ($act) {
    case 'get':
        $result = $db->SimpleWhile("SELECT * FROM topology_switches");
        echo json_encode($result);
        exit;
        break;

    case 'addSwitch':
        $name = $_POST['name'] ?? '';
        $x = $_POST['x'] ?? 0;
        $y = $_POST['y'] ?? 0;
        $ports = $_POST['ports'] ?? 0;
        $db->query("INSERT INTO topology_switches (name, x, y, ports) VALUES ('{$name}', '{$x}', '{$y}', '{$ports}')");
        echo json_encode(["id" => $db->getInsertId()]);
        exit;
        break;

    case 'addConn':
        $switch1 = $_POST['switch1'] ?? 0;
        $port1 = $_POST['port1'] ?? 0;
        $switch2 = $_POST['switch2'] ?? 0;
        $port2 = $_POST['port2'] ?? 0;
        $db->query("INSERT INTO topology_connect (switch1_id, port1, switch2_id, port2) VALUES ('{$switch1}', '{$port1}', '{$switch2}', '{$port2}')");
        echo json_encode(["success" => true]);
        exit;
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $lat = $_POST['lat'] ?? 0;
        $lng = $_POST['lng'] ?? 0;
        $db->query("UPDATE topology_switches SET lat = '{$lat}', lng = '{$lng}' WHERE id = {$id}");
        echo json_encode(["success" => true]);
        exit;
        break;

    case 'conn':
        $result = $db->SimpleWhile("SELECT * FROM topology_connect");
        echo json_encode($result);
        exit;
        break;
}
$md5 = md5(date('Y-m-d H:i:s'));
$result = <<<HTML
<style>
    #map {
        width: 100%;
        height: 800px;
        position: relative;
    }

    .switch { cursor: pointer; }
    .port { }
    .port.selected { fill: red; }
    .line { stroke: yellow; stroke-width: 2; }
    .dragger { stroke: red; stroke-width: 3; }
    
    .arrow { fill: none; stroke: red; stroke-width: 2; }
    .arrowhead { fill: red; }

    /* Високий z-index для SVG */
    .leaflet-overlay-pane {
        z-index: 500; /* Підвищуємо пріоритет для SVG шару */
    }

    .leaflet-map-pane {
        pointer-events: none; /* Забороняємо події для картки, щоб SVG отримував фокус */
    }

    .leaflet-overlay-pane svg {
        pointer-events: auto; /* Дозволяємо події на SVG */
    }
</style>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="../style/js/shop.js?do={$md5}"></script>

HTML;





$metatags = array(
    'title' => 'Topology',
    'description' => 'Topology',
    'page' => 'topology'
);

$tpl->load_template('battery/page.tpl');
$tpl->set('{speedbar}', $speedbar);
$tpl->set('{result}', $result);
$tpl->compile('content');
$tpl->clear();
?>
