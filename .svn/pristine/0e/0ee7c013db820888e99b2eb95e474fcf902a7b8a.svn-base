<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;

$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;

if($id){
	$getONT = $db->Fast('onus','*',['idonu' => $id]);
}
if(!empty($getONT['idonu'])){
$getOLT = $db->Fast('switch','*',['id' => $getONT['olt']]);

if(!empty($getOLT['netip']) && !empty($getOLT['class'])){
/*
echo'<div class="onu-olt efect1 m20b mobile">';
echo'<div class="ont-block-bookmarks"><a class="addstikers" href="#" onclick="ajaxstikers(3301,\'add\')">Перезавантажити</a></div>';
echo'</div>';
*/
}}}
?>

