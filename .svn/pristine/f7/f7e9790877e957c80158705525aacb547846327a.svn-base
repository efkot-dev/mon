<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require_once ENGINE_DIR.'functions/charts.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): 0;
$act = isset($_POST['act']) ? Clean::int($_POST['act']): null;
if($id > 0){
	echo '<div class="contentping">' . viewGraphPing3($id,'ping3',$act).'</div>';
}
exit;
?>