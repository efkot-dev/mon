<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}

$decodeCoordinates = function($raw) {
	if (!is_string($raw) || $raw === '') {
		return null;
	}
	$raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$decoded = json_decode($raw, true);
	if (!is_array($decoded)) {
		return null;
	}

	$isPoint = function($point){
		return is_array($point)
			&& isset($point['lat'], $point['lng'])
			&& is_numeric($point['lat'])
			&& is_numeric($point['lng']);
	};

	$normalizePoint = function($point){
		$lat = round((float)$point['lat'], 6);
		$lng = round((float)$point['lng'], 6);
		if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
			return null;
		}
		return ['lat' => $lat, 'lng' => $lng];
	};

	$rings = [];
	if (isset($decoded[0]) && is_array($decoded[0]) && $isPoint($decoded[0])) {
		$ring = [];
		foreach ($decoded as $point) {
			$np = $normalizePoint($point);
			if ($np !== null) {
				$ring[] = $np;
			}
		}
		if (count($ring) >= 3) {
			$rings[] = $ring;
		}
	} else {
		foreach ($decoded as $maybeRing) {
			if (!is_array($maybeRing)) {
				continue;
			}
			$ring = [];
			foreach ($maybeRing as $point) {
				if (!$isPoint($point)) {
					continue;
				}
				$np = $normalizePoint($point);
				if ($np !== null) {
					$ring[] = $np;
				}
			}
			if (count($ring) >= 3) {
				$rings[] = $ring;
			}
		}
	}

	return count($rings) ? $rings : null;
};

$loadZones = function() use ($db, $decodeCoordinates) {
	$rows = $db->SimpleWhile("SELECT id, competitorid, name, color, coordinates, created_at FROM competitor_locations ORDER BY id DESC");
	$zones = [];
	if (!is_array($rows)) {
		return $zones;
	}
	foreach ($rows as $row) {
		$coordinates = $decodeCoordinates((string)$row['coordinates']);
		if ($coordinates === null) {
			continue;
		}
		$color = (string)$row['color'];
		if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
			$color = '#ff0000';
		}
		$zones[] = [
			'id' => (int)$row['id'],
			'competitorid' => (int)$row['competitorid'],
			'name' => (string)$row['name'],
			'color' => $color,
			'coordinates' => $coordinates,
			'created_at' => (string)$row['created_at'],
		];
	}
	return $zones;
};

switch($act){
	case 'save':
		header('Content-Type: application/json; charset=utf-8');
		if (!$access->get('monitordevice')) {
			http_response_code(403);
			die(json_encode(['ok' => false, 'error' => 'forbidden']));
		}
		$competitorid = isset($_POST['competitorid']) ? Clean::int($_POST['competitorid']) : 0;
		$name = isset($_POST['name']) ? trim(Clean::text($_POST['name'])) : '';
		$color = isset($_POST['color']) ? trim(Clean::text($_POST['color'])) : '';
		$rawCoordinates = isset($_POST['coordinates']) ? (string)$_POST['coordinates'] : '';

		$nameLength = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
		if ($name === '' || $nameLength > 200) {
			die(json_encode(['ok' => false, 'error' => 'invalid_name']));
		}
		if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
			$color = '#ff0000';
		}
		$coordinates = $decodeCoordinates($rawCoordinates);
		if ($coordinates === null) {
			die(json_encode(['ok' => false, 'error' => 'invalid_coordinates']));
		}
		$db->SQLinsert('competitor_locations', [
			'act' => 'polygon',
			'competitorid' => (int)$competitorid,
			'name' => $name,
			'color' => $color,
			'coordinates' => json_encode($coordinates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		]);
		$zoneId = (int)$db->getInsertId();
		die(json_encode([
			'ok' => true,
			'zone' => [
				'id' => $zoneId,
				'competitorid' => (int)$competitorid,
				'name' => $name,
				'color' => $color,
				'coordinates' => $coordinates,
				'created_at' => date('Y-m-d H:i:s'),
			],
		]));
	break;
	case 'delete':
		header('Content-Type: application/json; charset=utf-8');
		if (!$access->get('monitordevice')) {
			http_response_code(403);
			die(json_encode(['ok' => false, 'error' => 'forbidden']));
		}
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		if ($id <= 0) {
			die(json_encode(['ok' => false, 'error' => 'invalid_id']));
		}
		$db->SQLdelete('competitor_locations', ['id' => $id]);
		die(json_encode(['ok' => true, 'id' => $id]));
	break;
	case 'list':
		header('Content-Type: application/json; charset=utf-8');
		if (!$access->get('view_map') && !$access->get('monitordevice')) {
			http_response_code(403);
			die(json_encode(['ok' => false, 'error' => 'forbidden']));
		}
		die(json_encode(['ok' => true, 'zones' => $loadZones()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	break;
	default:
		if (!$access->get('view_map') && !$access->get('monitordevice')) {
			$go->redirect('main');
		}
		$metatags = array('title'=>'Competitor Coverage','description'=>'Competitor Coverage','page'=>'competitor');
		$speedbar = '
			<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Competitor Coverage</span>
		';
		$gpslan = isset($config['geo_lan']) ? (float)$config['geo_lan'] : 50.4501;
		$gpslon = isset($config['geo_lon']) ? (float)$config['geo_lon'] : 30.5234;
		$zonesJson = json_encode($loadZones(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$canEdit = $access->get('monitordevice') ? 'true' : 'false';
		$jsScript = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<style>
  .competitor-wrap{display:grid;grid-template-columns:340px 1fr;gap:12px;}
  .competitor-side{background:#fff;border-radius:8px;padding:12px;max-height:72vh;overflow:auto;}
  .competitor-toolbar{display:flex;gap:8px;align-items:center;margin-bottom:8px;}
  .competitor-toolbar input{flex:1;min-width:0;}
  .competitor-stats{font-size:12px;color:#666;margin-bottom:10px;}
  .competitor-list{display:flex;flex-direction:column;gap:8px;}
  .competitor-item{border:1px solid #e5e5e5;border-radius:8px;padding:8px;cursor:pointer;}
  .competitor-item.active{border-color:#2b7cff;box-shadow:0 0 0 1px #2b7cff inset;}
  .competitor-item-row{display:flex;justify-content:space-between;align-items:center;gap:8px;}
  .competitor-color{width:12px;height:12px;border-radius:99px;display:inline-block;margin-right:6px;border:1px solid rgba(0,0,0,.2);}
  .competitor-actions{display:flex;gap:6px;margin-top:6px;}
  #mappers{height:72vh;border-radius:8px;overflow:hidden;}
  #mappers{height:72vh;border-radius:8px;overflow:hidden;}
  .zone-popup label{display:block;margin-top:6px;font-size:12px;color:#555}
  .zone-popup input{width:100%;box-sizing:border-box;}
  .zone-popup button{margin-top:8px;}
  @media (max-width: 980px){
    .competitor-wrap{grid-template-columns:1fr;}
    .competitor-side{max-height:none;}
    #mappers{height:62vh;}
  }
  #mappers {
    min-height: 100%;
    top: 0;
    min-width: 1024px;
    width: 100%;
    position: relative;
}
</style>
<div class="competitor-wrap">
  <aside class="competitor-side">
    <div class="competitor-toolbar">
      <input id="zone-search" class="input1" type="text" placeholder="Вибрати зону">
      '.($access->get('monitordevice') ? '<button id="zone-draw-btn" class="knopkagreen" type="button">Показати</button>' : '').'
    </div>
    <div class="competitor-stats" id="zone-stats"></div>
    <div class="competitor-list" id="zone-list"></div>
  </aside>
  <div id="mappers"></div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
  (function(){
    const canEdit = '.$canEdit.';
    const initialZones = '.$zonesJson.';
    const fallbackLat = '.$gpslan.';
    const fallbackLng = '.$gpslon.';
    const stateKey = "competitorMapStateV2";
    function loadMapState(){
      try {
        const state = JSON.parse(localStorage.getItem(stateKey) || "{}");
        if (typeof state.lat === "number" && typeof state.lng === "number" && typeof state.zoom === "number") {
          return state;
        }
      } catch (e) {}
      return {lat: fallbackLat, lng: fallbackLng, zoom: 14};
    }
    function saveMapState(map){
      const c = map.getCenter();
      localStorage.setItem(stateKey, JSON.stringify({lat:c.lat,lng:c.lng,zoom:map.getZoom()}));
    }
    function escHtml(v){
      return String(v == null ? "" : v)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/\'/g, "&#39;");
    }

    const state = loadMapState();
    const map = L.map("mappers", {preferCanvas: true}).setView([state.lat, state.lng], state.zoom);
    const baseLayers = {
      "OpenStreetMap": L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {maxZoom:19}),
      "Visicom": L.tileLayer("https://{s}.visicom.ua/2.0.0/planet3/base/{z}/{x}/{y}.png?key=pmon", {subdomains:["tms0","tms1","tms2","tms3"],maxZoom:19,tms:true}),
      "Google Hybrid": L.tileLayer("https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}", {maxZoom:19,subdomains:["mt0","mt1","mt2","mt3"]})
    };
    baseLayers["Google Hybrid"].addTo(map);
    L.control.layers(baseLayers, {}).addTo(map);

    map.on("moveend", function(){ saveMapState(map); });

    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);
    if (canEdit) {
      map.addControl(new L.Control.Draw({
        draw: false,
        edit: {featureGroup: drawnItems}
      }));
    }

    const zonesById = new Map();
    const layersById = new Map();
    const listNode = document.getElementById("zone-list");
    const statsNode = document.getElementById("zone-stats");
    const searchNode = document.getElementById("zone-search");
    let activeZoneId = null;
    let drawHandler = null;

    function zoneCountText(){
      return "Zones: " + zonesById.size;
    }

    function polygonBoundsFromZone(zone){
      let bounds = null;
      (zone.coordinates || []).forEach(function(ring){
        (ring || []).forEach(function(p){
          if (!bounds) {
            bounds = L.latLngBounds([p.lat, p.lng], [p.lat, p.lng]);
          } else {
            bounds.extend([p.lat, p.lng]);
          }
        });
      });
      return bounds;
    }

    function buildPopupHtml(zone){
      const name = escHtml(zone.name || ("Zone #" + zone.id));
      const color = escHtml(zone.color || "#ff0000");
      const comp = Number(zone.competitorid || 0);
      let html = "<div><b>" + name + "</b><br>ID: " + comp + "</div>";
      if (canEdit) {
        html += "<div style=\\"margin-top:8px;\\"><button type=\\"button\\" class=\\"knopkared\\" onclick=\\"window.__deleteZone(" + Number(zone.id) + ")\\">Видалити</button></div>";
      }
      html += "<div style=\\"margin-top:6px;color:#666;font-size:12px;\\">Колір: " + color + "</div>";
      return html;
    }

    function highlightZone(zoneId){
      activeZoneId = zoneId;
      listNode.querySelectorAll(".competitor-item").forEach(function(el){
        el.classList.toggle("active", Number(el.getAttribute("data-zone-id")) === Number(zoneId));
      });
      layersById.forEach(function(layer, id){
        const zone = zonesById.get(id);
        const color = zone && zone.color ? zone.color : "#ff0000";
        layer.setStyle({
          color: color,
          weight: Number(id) === Number(zoneId) ? 4 : 2,
          fillOpacity: Number(id) === Number(zoneId) ? 0.3 : 0.18
        });
      });
    }

    function renderList(){
      const q = (searchNode.value || "").trim().toLowerCase();
      const zones = Array.from(zonesById.values()).sort(function(a,b){ return Number(b.id) - Number(a.id); });
      let html = "";
      zones.forEach(function(zone){
        const title = zone.name || ("Zone #" + zone.id);
        if (q && title.toLowerCase().indexOf(q) === -1 && String(zone.id).indexOf(q) === -1) {
          return;
        }
        html += "<div class=\\"competitor-item" + (Number(zone.id) === Number(activeZoneId) ? " active" : "") + "\\" data-zone-id=\\"" + Number(zone.id) + "\\">";
        html += "<div class=\\"competitor-item-row\\"><div><span class=\\"competitor-color\\" style=\\"background:" + escHtml(zone.color || "#ff0000") + "\\"></span><b>" + escHtml(title) + "</b></div><div>#" + Number(zone.id) + "</div></div>";
        html += "<div style=\\"margin-top:4px;font-size:12px;color:#666;\\">Competitor ID: " + Number(zone.competitorid || 0) + "</div>";
        html += "<div class=\\"competitor-actions\\"><button type=\\"button\\" class=\\"knopkagreen zone-zoom\\" data-zone-id=\\"" + Number(zone.id) + "\\">Показати</button>";
        if (canEdit) {
          html += "<button type=\\"button\\" class=\\"knopkared zone-del\\" data-zone-id=\\"" + Number(zone.id) + "\\">Видалити</button>";
        }
        html += "</div></div>";
      });
      listNode.innerHTML = html || "<div style=\\"color:#777;font-size:13px;\\">Не вибрано</div>";
      statsNode.textContent = zoneCountText();
    }

    function addZoneToMap(zone, fit){
      if (!zone || !zone.id || !Array.isArray(zone.coordinates)) {
        return;
      }
      if (layersById.has(zone.id)) {
        drawnItems.removeLayer(layersById.get(zone.id));
        layersById.delete(zone.id);
      }
      zonesById.set(zone.id, zone);
      const latlngs = zone.coordinates.map(function(ring){
        return (ring || []).map(function(p){ return [p.lat, p.lng]; });
      });
      const polygon = L.polygon(latlngs, {
        color: zone.color || "#ff0000",
        weight: 2,
        fillOpacity: 0.18
      });
      polygon.on("click", function(){
        highlightZone(zone.id);
      });
      polygon.bindPopup(buildPopupHtml(zone));
      polygon.addTo(drawnItems);
      layersById.set(zone.id, polygon);
      if (fit) {
        const bounds = polygonBoundsFromZone(zone);
        if (bounds) {
          map.fitBounds(bounds, {padding:[20,20]});
        }
      }
      renderList();
    }

    function removeZoneLocal(id){
      const zid = Number(id);
      if (layersById.has(zid)) {
        drawnItems.removeLayer(layersById.get(zid));
        layersById.delete(zid);
      }
      zonesById.delete(zid);
      if (Number(activeZoneId) === zid) {
        activeZoneId = null;
      }
      renderList();
    }

    function saveZone(payload){
      return $.ajax({
        url: root + "?do=competitor",
        method: "POST",
        dataType: "json",
        data: payload
      });
    }

    function startDrawingPolygon(){
      if (!canEdit) return;
      if (drawHandler) drawHandler.disable();
      drawHandler = new L.Draw.Polygon(map, {
        allowIntersection: false,
        showArea: true,
        shapeOptions: {color: "#ff0000", weight: 2}
      });
      drawHandler.enable();
    }

    if (canEdit) {
      map.on("contextmenu", startDrawingPolygon);
      const drawBtn = document.getElementById("zone-draw-btn");
      if (drawBtn) drawBtn.addEventListener("click", startDrawingPolygon);
    }

    map.on("draw:created", function(e){
      if (!canEdit) return;
      const layer = e.layer;
      map.addLayer(layer);
      const coords = JSON.stringify(layer.getLatLngs());
      const popupHtml = [
        "<div class=\\"zone-popup\\">",
        "  <label>Назва</label>",
        "  <input type=\\"text\\" id=\\"zone_name\\" value=\\"\\">",
        "  <label>Color</label>",
        "  <input type=\\"color\\" id=\\"zone_color\\" value=\\"#ff0000\\">",
        "  <input type=\\"hidden\\" id=\\"zone_geo\\" value=\\"" + escHtml(coords) + "\\">",
        "  <button type=\\"button\\" class=\\"knopkagreen\\" id=\\"zone_save_btn\\">Зберегти</button>",
        "</div>"
      ].join("");
      layer.bindPopup(popupHtml).openPopup();

      layer.on("popupopen", function(){
        const btn = document.getElementById("zone_save_btn");
        if (!btn) return;
        btn.addEventListener("click", function(){
          const nameEl = document.getElementById("zone_name");
          const colorEl = document.getElementById("zone_color");
          const geoEl = document.getElementById("zone_geo");
          const name = (nameEl && nameEl.value ? nameEl.value.trim() : "");
          const color = (colorEl && colorEl.value ? colorEl.value : "#ff0000");
          const geo = (geoEl && geoEl.value ? geoEl.value : "");
          if (!name || !geo) {
            alert("Name and polygon are required");
            return;
          }
          saveZone({
            act: "save",
            competitorid: Date.now(),
            name: name,
            color: color,
            coordinates: geo
          }).done(function(resp){
            if (!resp || !resp.ok || !resp.zone) {
              alert("Save error");
              return;
            }
            map.removeLayer(layer);
            addZoneToMap(resp.zone, true);
            highlightZone(resp.zone.id);
          }).fail(function(){
            alert("Save error");
          });
        }, {once:true});
      });

      if (drawHandler) {
        drawHandler.disable();
      }
    });

    window.__deleteZone = function(id){
      if (!canEdit) return;
      const zid = Number(id);
      if (!zid || !confirm("Delete this zone?")) {
        return;
      }
      $.ajax({
        url: root + "?do=competitor",
        method: "POST",
        dataType: "json",
        data: {act: "delete", id: zid}
      }).done(function(resp){
        if (resp && resp.ok) {
          removeZoneLocal(zid);
        }
      });
    };

    listNode.addEventListener("click", function(e){
      const zoomBtn = e.target.closest(".zone-zoom");
      const delBtn = e.target.closest(".zone-del");
      const zoneCard = e.target.closest(".competitor-item");
      if (zoomBtn) {
        const zid = Number(zoomBtn.getAttribute("data-zone-id"));
        const zone = zonesById.get(zid);
        if (!zone) return;
        highlightZone(zid);
        const bounds = polygonBoundsFromZone(zone);
        if (bounds) {
          map.fitBounds(bounds, {padding:[20,20]});
        }
        return;
      }
      if (delBtn) {
        const zid = Number(delBtn.getAttribute("data-zone-id"));
        window.__deleteZone(zid);
        return;
      }
      if (zoneCard) {
        const zid = Number(zoneCard.getAttribute("data-zone-id"));
        highlightZone(zid);
      }
    });

    searchNode.addEventListener("input", renderList);

    initialZones.forEach(function(zone){
      addZoneToMap(zone, false);
    });
    statsNode.textContent = zoneCountText();
  })();
</script>
';
}

$tpl->load_template('battery/page.tpl');
$tpl->set('{speedbar}',$speedbar);
$tpl->set('{result}',$jsScript);
$tpl->compile('content');
$tpl->clear();
?>
