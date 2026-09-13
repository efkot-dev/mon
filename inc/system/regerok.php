<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$directory = ROOT_DIR . '/export/temp/';
$pattern = $directory . 'olt_' . $id . '_onu_*.regeronu';
$files = glob($pattern);
$fileNames = [];
if(isset($files)){
	foreach ($files as $file) {
		$fileName = basename($file);
		$fileNames[] = $fileName;
	}
	$list_onu_regger = array();
	foreach ($fileNames as $fileName) {
		$pattern = '/olt_(\d+)_onu_(\w+)_(\d+).(\d+).(\d+):(\d+)\.regeronu/';
		if (preg_match($pattern, $fileName, $matches)) {
			$inface = $matches[2].' '.$matches[3].'/'.$matches[4].'/'.$matches[5].':'.$matches[6];
			#$fileName = str_replace(".", "_",$fileName);
			$list_onu_regger[md5($inface)] = array(
				'file' => $fileName,
				'inface' => $inface
			);
		}
	}
}
if(isset($list_onu_regger)){
	$tplRes .='<div class="regger_onu">';
	foreach ($list_onu_regger as $idmd => $inf_onu) {
		$tplRes .='
			<span class="db openPopup" data-popup-id="backup_panel" onclick="get_console(\''.$inf_onu['file'].'\');">
				<img src="../style/img/onu_success.png">'.$inf_onu['inface'].'
			</span>';
	}
	$tplRes .='
	</div>
	<div id="backup_panel" class="popupContainer">
		<div class="popupContent">
			<div id="result_ajax"></div>
		</div>
	</div>';
}else{
	
}
?>