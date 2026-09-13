<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

header('Content-Type: application/json; charset=utf-8');

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$q = trim((string)($_GET['q'] ?? ''));

if ($product_id > 0) {
    $stmt = $pdo->prepare("
        SELECT id, name, inventory_number, mac, quantity
          FROM sklad_tovar
         WHERE id = :product_id
           AND status = 'active'
         LIMIT 1
    ");
    $stmt->execute([':product_id' => $product_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['items' => $row ? [$row] : []], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT id, name, inventory_number, mac, quantity
          FROM sklad_tovar
         WHERE status = 'active'
           AND (inventory_number LIKE :q OR mac LIKE :q OR name LIKE :q)
      ORDER BY name ASC
         LIMIT 50
    ");
    $stmt->execute([':q' => '%' . $q . '%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['items' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['items' => []], JSON_UNESCAPED_UNICODE);
exit;
?>
