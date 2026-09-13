<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');
require_once ENGINE_DIR.'ajax.php';

$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$act = isset($_POST['act']) ? Clean::int($_POST['act']): null;

echo '<div class="contentping">' . viewGraphPing3($id,'ping3',$act).'</div>';
?>