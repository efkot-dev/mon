<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$file = isset($_POST['file']) ? Clean::text($_POST['file']): null;
function addLineBreaks($inputString) {
    $lines = explode("\n", $inputString);
    $result = '';
    foreach ($lines as $line) {
        $result .= $line . "<br>";
    }
    return $result;
}
if(isset($file)){
	$pattern = '/olt_(\d+)_onu_(\w+)_(\d+).(\d+).(\d+):(\d+)\.regeronu/';
	if(preg_match($pattern, $file, $matches)) {
		$dir = ROOT_DIR . '/export/temp/';
		$files = $dir . 'olt_' . $matches[1] . '_onu_'.$matches[2].'_'.$matches[3].'.'.$matches[4].'.'.$matches[5].':'.$matches[6].'.regeronu';	
		if (file_exists($files)) {
			$result = file_get_contents($files);
			$result = addLineBreaks($result);
			echo'<div class="configonu" style="text-align:left;font-size:11px;">'.$result.'</div>';
		}
	}
}
?>