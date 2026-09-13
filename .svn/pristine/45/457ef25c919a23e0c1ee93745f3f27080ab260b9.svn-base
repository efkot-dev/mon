<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
$category_id = isset($_POST['category_id']) ? Clean::int($_POST['category_id']) : null;
$sub_id = isset($_POST['sub_id']) ? Clean::int($_POST['sub_id']) : null;
$dia = isset($_POST['dia']) ? Clean::text($_POST['dia']) : null;
if (!$id || !$dia) {
    $go->go('/?do=tmc&act=category');
    exit;
}
if ($dia === 'delete') {
    $sklad_category = $pdo->prepare("DELETE FROM sklad_category WHERE id = :id");
    $sklad_category->execute([':id' => $id]);
    $sklad_sub_category = $pdo->prepare("DELETE FROM sklad_sub_category WHERE cat_id = :id");
    $sklad_sub_category->execute([':id' => $id]);    
	$sklad_tovar = $pdo->prepare("DELETE FROM sklad_tovar WHERE category_id = :id AND status = 'active'");
    $sklad_tovar->execute([':id' => $id]);
}
if ($dia === 'move') {

}
?>
