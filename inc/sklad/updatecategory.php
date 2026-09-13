<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] <= 0) {
    $go->go('/?do=tmc');
    exit;
}
$id = Clean::int($_POST['id']);
$sqlinsert = [];
if (!empty($_POST['name'])) {
    $sqlinsert['name'] = Clean::text($_POST['name']);
}
if (!empty($_POST['types'])) {
    $sqlinsert['types'] = Clean::int($_POST['types']);
    $sqlinsert['number'] = ($sqlinsert['types'] == 3) ? 'yes' : 'no';
}
if (!empty($_POST['note'])) {
    $sqlinsert['note'] = Clean::text($_POST['note']);
}
if (!empty($_FILES['file']['name']) && isset($_FILES['file']['type']) && isset($allowed_types[$_FILES['file']['type']])) {
    if (preg_match('/^(.+)\.(jpg|jpeg|png)$/i', $_FILES['file']['name'])) {
        $newname = substr(md5(uniqid(rand(), true)), 0, rand(7, 13)) . '.' . $allowed_types[$_FILES['file']['type']];
        if (@copy($_FILES['file']['tmp_name'], $uploaddir . $newname)) {
            $sqlinsert['img'] = $newname;
        }
    }
}
if (!empty($sqlinsert)) {
    try {
        $setPart = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($sqlinsert)));
        $stmt = $pdo->prepare("UPDATE sklad_category SET $setPart WHERE id = :id");
        $sqlinsert['id'] = $id;
        $stmt->execute($sqlinsert);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }    
    $go->go('/?do=tmc&act=category');
    exit;
}
$go->go('/?do=tmc');
exit;
