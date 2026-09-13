<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$auto = false;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id){
	$go->go('/?do=tmc&act=category');
	exit;		
}
$sql_cat = $pdo->prepare("SELECT * FROM sklad_sub_category WHERE id = :id");
$sql_cat->execute([':id' => $id]);
$get_cat = $sql_cat->fetch(PDO::FETCH_ASSOC);
if(!$get_cat){
	
}else{
	$sklad_category = $pdo->prepare("DELETE FROM sklad_sub_category WHERE id = :id");
    $sklad_category->execute([':id' => $id]);
}
$go->go('/?do=tmc&act=category');
exit;	
?>
