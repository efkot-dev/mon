<?php
if (!defined('PONMONITOR')) {
	die('Hacking attempt!');
}

require_once ENGINE_DIR . 'functions/digital_twin.php';

if (!$access->get('porterror') && !$access->get('monitordevice') && !dt_user_is_admin($USER)) {
	$go->redirect('main');
}

$metatags = array(
	'title' => 'Групові погіршення сигналу ONU',
	'description' => 'Групові погіршення сигналу ONU',
	'page' => 'signaldegradation'
);

$speedbar = '
<div id="onu-speedbar">
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Групові погіршення сигналу ONU</span>
</div>';

$assetV = is_file(ROOT_DIR . '/style/css/digitaltwin.css') ? (int)filemtime(ROOT_DIR . '/style/css/digitaltwin.css') : time();

$templates = '
<div class="dt-wrap">
	<div id="dt-signal-degradation">
		<div class="dt-card"><div class="dt-help">Йде розрахунок групових погіршень сигналу...</div></div>
	</div>
</div>
<link rel="stylesheet" href="/style/css/digitaltwin.css?v='.$assetV.'">
<script>
document.addEventListener("DOMContentLoaded", function () {
	var container = document.getElementById("dt-signal-degradation");
	if (!container) return;
	$.post(root + "?do=core&act=signaldegradationpage", {olt_id: 0}, function (response) {
		container.innerHTML = response;
	}, "html");
});
</script>';

$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', '<div class="mainadmin">'.$speedbar.$templates.'</div>');
$tpl->compile('content');
$tpl->clear();
?>
