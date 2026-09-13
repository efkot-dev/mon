<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$sqlinsert = [];
if (isset($_POST['name'])) {
    $sqlinsert['name'] = Clean::text($_POST['name']);
}
if (isset($_POST['catid'])) {
    $sqlinsert['cat_id'] = Clean::int($_POST['catid']);
}
if (isset($_POST['note'])) {
    $sqlinsert['note'] = Clean::text($_POST['note']);
}
if (isset($_POST['code'])) {
    $sqlinsert['code'] = Clean::text($_POST['code']);
}
if (!empty($sqlinsert['name']) && !empty($sqlinsert['cat_id'])) {
    $stmt = $pdo->prepare("INSERT INTO sklad_sub_category (name, code, cat_id, description, icon, quantity) VALUES (:name, :code, :cat_id, :note, :icon, :quantity)");
    $stmt->execute([
        ':code'   => $sqlinsert['code'] ?? null,
        ':name'   => $sqlinsert['name'],
        ':cat_id' => $sqlinsert['cat_id'],
        ':note'   => $sqlinsert['note'] ?? null,
        ':quantity'   => $sqlinsert['quantity'] ?? 0,
        ':icon'   => $sqlinsert['icon'] ?? null,
    ]);
    $go->go('/?do=tmc&act=category');
   exit;
}
$go->go('/?do=tmc');
exit;
?>

