<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
header('Content-Type: application/json; charset=utf-8');
if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    $query = "SELECT inventory_number FROM sklad_tovar 
		WHERE id = :product_id 
			AND inventory_number IS NOT NULL
				AND status = 'active'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':product_id' => $product_id]);
    $inventoryList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($inventoryList) > 0) {
        echo json_encode($inventoryList);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
die;
?>
