<?php
if (!defined('PONMONITOR') && !defined('FIBER')) {
    die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if (!$id) {
    $go->go('/?do=fiber&act=unit');
    return;
}
$stmt = $pdo->prepare('SELECT id, name, location, lan, lon FROM ponunit WHERE id = :id LIMIT 1');
$stmt->execute([':id' => (int)$id]);
$ponunit = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ponunit) {
    $go->go('/?do=fiber&act=unit');
    return;
}
$poncity = [];
if (!empty($ponunit['location'])) {
    $stmt = $pdo->prepare('SELECT lan, lon FROM location WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$ponunit['location']]);
    $poncity = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}
$lan = null;
$lon = null;
if (isset($ponunit['lan']) && is_numeric($ponunit['lan'])) $lan = (float)$ponunit['lan'];
if (isset($ponunit['lon']) && is_numeric($ponunit['lon'])) $lon = (float)$ponunit['lon'];
if ($lan === null && isset($poncity['lan']) && is_numeric($poncity['lan'])) $lan = (float)$poncity['lan'];
if ($lon === null && isset($poncity['lon']) && is_numeric($poncity['lon'])) $lon = (float)$poncity['lon'];
if ($lan === null) $lan = (float)($config['geo_lan'] ?? 0);
if ($lon === null) $lon = (float)($config['geo_lon'] ?? 0);
$metatags = ['title' => $lang['volsmeraja'], 'description' => $lang['volsmeraja'], 'page' => 'ponunit'];
$resutltpl .= ponjs();
$bar_tpl .= '<div class="nav-bar">';
$bar_tpl .= '<a href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$bar_tpl .= '<a href="/?do=fiber&act=unit"><i class="fi fi-rr-angle-left"></i>' . $lang['volsmeraja'] . '</a>';
$bar_tpl .= '<a href="/?do=fiber&act=viewunit&id=' . (int)$ponunit['id'] . '"><i class="fi fi-rr-angle-left"></i>' . htmlspecialchars($ponunit['name'], ENT_QUOTES, 'UTF-8') . '</a>';
$bar_tpl .= '<span class="active"><i class="fi fi-rr-angle-left"></i>' . $lang['fiber_change_position'] . '</span>';
$bar_tpl .= '</div>';
$unitId   = (int)$ponunit['id'];
$nameHtml = htmlspecialchars((string)$ponunit['name'], ENT_QUOTES, 'UTF-8');
$centerJson = json_encode([(float)$lan, (float)$lon], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$mapper = getMap();
$resutltpl .= <<<HTML
<div class="nav-fiber" style="height:100%;width:100%;">
  <div id="maps" style="height:100%;width:100%;"></div>
</div>
<script>
(function() {
  const center = {$centerJson};
  const map = L.map('maps', { center: center, zoom: 16 });
  {$mapper}
  map.on('click', (e) => {
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);
    const html = `
      <form action="/?do=fiber&act=save_unit" method="post" class="popup-form">
        <div class="popup-title">{$nameHtml}</div>
        <input type="hidden" name="lan" value="\${lat}">
        <input type="hidden" name="lon" value="\${lng}">
        <input type="hidden" name="unit" value="{$unitId}">
        <button type="submit">{$lang['save']}</button>
      </form>`;
    L.popup().setLatLng(e.latlng).setContent(html).openOn(map);
  });
})();
</script>
HTML;
?>
