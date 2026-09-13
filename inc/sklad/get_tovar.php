<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
header('Content-Type: application/json; charset=utf-8');
if (isset($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];
    $query = "SELECT * FROM sklad_tovar WHERE category_id = :category_id AND status = 'active'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':category_id' => $category_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
}
exit;
?>
