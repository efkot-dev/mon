<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
function fun_unit($id){
	if($id==1){
		return 'метраж';	
	}elseif($id==2){
		return 'кількість';	
	}elseif($id==3){
		return 'інвертарний номер';		
	}
}
$catid = isset($_POST['catid']) ? (int)$_POST['catid'] : 0;
$subcatid = isset($_POST['subcatid']) ? (int)$_POST['subcatid'] : 0;
$products = [];
if (isset($_POST['products']) && is_array($_POST['products'])) {
    $count = count($_POST['products']);
    for ($i = 0; $i < $count; $i++) {
		if(isset($catid) && $catid>0){
			$sql_cat = "SELECT * FROM sklad_category WHERE id = :category_id";
			$cat = $pdo->prepare($sql_cat);
			$cat->bindParam(':category_id', $catid, PDO::PARAM_INT);
			$cat->execute();
			$result_category = $cat->fetch(PDO::FETCH_ASSOC);	
			$products[] = [
				'mac' => $_POST['products'][$i]['mac'] ?? null,
				'name' => $_POST['products'][$i]['name'],
				'category_id' => $catid,
				'sub_cat_id' => $subcatid,
				'unit' => fun_unit($result_category['types']),
				'oblik' => $result_category['types'],
				'quantity' => $_POST['products'][$i]['quantity'] ?? 0,
				'price' => $_POST['products'][$i]['price'] ?? null,
				'price_pdv' => $_POST['products'][$i]['price_pdv'] ?? null,
				'inventory_number' => $_POST['products'][$i]['inventory_number'] ?? null,
			];
		}
    }
}
if (empty($products)) {
    echo json_encode(['status' => 'error', 'message' => 'Немає даних для збереження']);
    exit;
}
$query = "INSERT INTO sklad_tovar (name, category_id, unit, quantity, sub_cat_id, description, price_pdv, price, inventory_number, date_added, oblik, mac) 
          VALUES (:name, :category_id, :unit, :quantity, :sub_cat_id, :description, :price_pdv, :price, :inventory_number, CURDATE(), :oblik, :mac)";
$stmt = $pdo->prepare($query);
$errors = [];
foreach ($products as $product) {
    $mac = isset($product['mac']) ? Clean::text($product['mac']) : null;
    $name = isset($product['name']) ? Clean::text($product['name']) : null;
    $oblik = isset($product['oblik']) ? Clean::text($product['oblik']) : null;
    $category_id = isset($product['category_id']) ? (int)$product['category_id'] : null;
    $unit = isset($product['unit']) ? Clean::text($product['unit']) : null;
    $quantity = isset($product['quantity']) ? (float)$product['quantity'] : 0;
    $sub_cat_id = isset($product['sub_cat_id']) ? (float)$product['sub_cat_id'] : 0;
    $price = isset($product['price']) ? (float)$product['price'] : null;
    $price_pdv = isset($product['price_pdv']) ? (float)$product['price_pdv'] : null;
    $inventory_number = isset($product['inventory_number']) ? Clean::text($product['inventory_number']) : null;
    if (!$name || !$unit) {
        $errors[] = "Помилка у товарі: " . json_encode($product);
        continue;
    }
    $stmt->execute([
        ':oblik' => $oblik,
        ':mac' => $mac,
        ':name' => $name,
        ':category_id' => $category_id,
        ':unit' => $unit,
        ':quantity' => $quantity,
        ':sub_cat_id' => $sub_cat_id,
        ':description' => null,
        ':price' => $price,
        ':price_pdv' => $price_pdv,
        ':inventory_number' => $inventory_number
    ]);
}
if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => 'Деякі товари не збережено', 'errors' => $errors]);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Всі товари успішно збережено']);
}
?>
