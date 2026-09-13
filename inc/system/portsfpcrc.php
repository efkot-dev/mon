<?php
if (!defined('PONMONITOR')) {
	die('Hacking attempt!');
}

require_once ENGINE_DIR . 'functions/digital_twin.php';

if (!$access->get('porterror') && !$access->get('monitordevice') && !dt_user_is_admin($USER)) {
	$go->redirect('main');
}

$metatags = array(
	'title' => 'Проблеми порту / SFP / CRC',
	'description' => 'Проблеми порту / SFP / CRC',
	'page' => 'portsfpcrc'
);

$speedbar = '
<div id="onu-speedbar">
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Проблеми порту / SFP / CRC</span>
</div>';

$assetV = is_file(ROOT_DIR . '/style/css/digitaltwin.css') ? (int)filemtime(ROOT_DIR . '/style/css/digitaltwin.css') : time();

$templates = '
<div class="dt-wrap">
	<div id="dt-port-sfp-crc">
		<div class="dt-card"><div class="dt-help">Йде розрахунок проблем по портах, SFP і CRC...</div></div>
	</div>
</div>
<link rel="stylesheet" href="/style/css/digitaltwin.css?v='.$assetV.'">
<script>
document.addEventListener("DOMContentLoaded", function () {
	var container = document.getElementById("dt-port-sfp-crc");
	if (!container) return;
	$.post(root + "?do=core&act=portsfpcrcpage", {olt_id: 0}, function (response) {
		container.innerHTML = response;
	}, "html");
});
</script>';

$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', '<div class="mainadmin">'.$speedbar.$templates.'</div>');
$tpl->compile('content');
$tpl->clear();
?>
