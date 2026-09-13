<?php
if (!defined('PONMONITOR')) {
	die('Hacking attempt!');
}

require_once ENGINE_DIR . 'functions/digital_twin.php';

if (!$access->get('porterror') && !$access->get('pon_calc') && !$access->get('monitordevice') && !dt_user_is_admin($USER)) {
	$go->redirect('main');
}

$metatags = array(
	'title' => 'Digital Twin ISP',
	'description' => 'Digital Twin ISP',
	'page' => 'digitaltwin'
);

$speedbar = '
<div id="onu-speedbar">
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Digital Twin ISP</span>
</div>';

$assetV = is_file(ROOT_DIR . '/style/css/digitaltwin.css') ? (int)filemtime(ROOT_DIR . '/style/css/digitaltwin.css') : time();

$templates = '
<div class="dt-wrap">
	<div id="dt-dashboard">
		<div class="dt-card">
			<div class="dt-help">Йде розрахунок проблем по сигналам, ONU error, CRC і SFP...</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="/style/css/digitaltwin.css?v='.$assetV.'">
<script>
document.addEventListener("DOMContentLoaded", function () {
	var container = document.getElementById("dt-dashboard");
	if (!container) return;
	container.innerHTML = \'<div class="dt-card"><div class="dt-help">Йде розрахунок проблем по сигналам, ONU error, CRC і SFP...</div></div>\';
	$.post(root + "?do=core&act=digitaltwinpage", {olt_id: 0}, function (response) {
		container.innerHTML = response;
	}, "html");
});
</script>';

$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', '<div class="mainadmin">'.$speedbar.$templates.'</div>');
$tpl->compile('content');
$tpl->clear();
?>
