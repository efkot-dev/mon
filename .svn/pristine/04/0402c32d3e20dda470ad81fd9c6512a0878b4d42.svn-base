<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$s = 0;
require ENGINE_DIR.'classes/thumbs.class.php';
$img = $_GET['img'];
$type = $_GET['type'];
$s = intval($_GET['s']) ?? null;
if($type=='photo'){
	  $sourcePath = ROOT_DIR . '/file/photo/' . $img;
}
$image = new Thumbs($sourcePath);
if($s==1){
	$image->resize(350, 0);
}elseif($s==2){
	$image->resize(900, 0);	
}elseif($s==3){	
	$image->resize(100, 100);	
}elseif($s==4){
	$image->resize(55, 38);	
}else{
	$image->thumb(300, 300);	
}
$image->output();
die;
?>