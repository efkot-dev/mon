<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
switch($act){
	case 'save': 
    $markup = floatval($_POST['markup']);
    $invoiceName = isset($_POST['invoice_name']) ? Clean::text($_POST['invoice_name']) : null;
    $invoiceDescription = isset($_POST['invoice_description']) ? Clean::text($_POST['invoice_description']) : null;
    $productNames = $_POST['product_name'];
    $purchasePrices = $_POST['purchase_price'];
    $quantities = $_POST['quantity'];
    $types = $_POST['type'];
    $customPrices = $_POST['custom_price']; // Add this line to capture custom prices
    $added = date('Y-m-d H:i:s');

    $db->query("INSERT INTO shop_invoices (markup_percentage, name, description, created_at, status) 
                VALUES ('$markup', '$invoiceName', '$invoiceDescription', '$added' , '1')");        
    $invoiceId = $db->getInsertId();
    
    foreach ($productNames as $index => $productName) {
        $productName = cl_snmp($productName);
        $type = cl_snmp($types[$index]);
        $purchasePrice = floatval($purchasePrices[$index]);
        $quantity = intval($quantities[$index]);
        $totalPurchase = $purchasePrice * $quantity;
        $priceWithMarkup = $purchasePrice + ($purchasePrice * ($markup / 100));
        $totalWithMarkup = $priceWithMarkup * $quantity;
        $customPrice = floatval($customPrices[$index]); // Capture custom price for this item
        $customTotal = $customPrice > 0 ? $customPrice * $quantity : $totalWithMarkup; // Calculate custom total

        $productId = $db->Simple("SELECT id FROM shop_products WHERE name = '$productName' LIMIT 1");
        if (!$productId) {
            $db->query("INSERT INTO shop_products (name, types, purchase_price) VALUES ('$productName', '$type', '$purchasePrice')");
            $product_sql = $db->getInsertId();
        } else {
            $product_sql =  $productId['id'];
        }
        $sql = "INSERT INTO shop_invoice_items (invoice_id, product_id, quantity, purchase_price, total_purchase, total_price_with_markup, custom_price, custom_total) 
                VALUES ('$invoiceId', '$product_sql', '$quantity', '$purchasePrice', '$totalPurchase', '$totalWithMarkup', '$customPrice', '$customTotal')";
        $db->query($sql);
    }        
    echo "Накладна успішно збережена!";
    break;			
	case 'user':     
		if(isset($id) && $id>0){
			$selectedUser = [];
			$list_usr = $db->SimpleWhile("SELECT * FROM users");
			foreach ($list_usr as $id_usr => $usr) {
				if (!empty($selectedUser)) {
					$checked_usr = in_array($usr['id'],$selectedUser) ? 'checked' : '';
				}else{
					$checked_usr = '';
				}
				$select_usr .= '<div class="check_location">
					<input type="checkbox" name="usr[]" value="'.$usr['id'].'" '.$checked_usr.'>'.(!empty($usr['name']) ? ''.formatPib($usr['name']).'' : $usr['username']).'</div>';
			}
			echo '<form action="/" method="post" class="form_center"><input type="hidden" name="do" value="calc"><input type="hidden" name="act" value="used"><input type="hidden" name="id" value="'.$id.'">	';
			echo '<div class="pop-flex-down" style="width:300px;">';
			echo '<div class="checkbox-container">';
			echo $select_usr;
			echo '</div>';
			echo '<input type="submit" value="'.$lang['save'].'">	';
			echo '</div>';
			echo '</div>';
			echo '</form>';
		}
		break;		
	case 'update': 		
		$invoiceId = intval($_POST['invoice_id']);
		$markup = floatval($_POST['markup']);
		$invoiceName = isset($_POST['invoice_name']) ? Clean::text($_POST['invoice_name']) : null;
		$invoiceDescription = isset($_POST['invoice_description']) ? Clean::text($_POST['invoice_description']) : null;
		$productNames = $_POST['product_name'];
		$purchasePrices = $_POST['purchase_price'];
		$quantities = $_POST['quantity'];
		$types = $_POST['type'];
		$db->query("UPDATE shop_invoices 
					SET markup_percentage = '$markup', name = '$invoiceName', description = '$invoiceDescription' 
					WHERE id = '$invoiceId'");
		#$db->query("DELETE FROM shop_invoice_items WHERE invoice_id = '$invoiceId'");
		foreach ($productNames as $index => $productName) {
			$productName = cl_snmp($productName);
			$type = cl_snmp($types[$index]);
			$purchasePrice = floatval($purchasePrices[$index]);
			$quantity = intval($quantities[$index]);
			$totalPurchase = $purchasePrice * $quantity;
			$priceWithMarkup = $purchasePrice + ($purchasePrice * ($markup / 100));
			$totalWithMarkup = $priceWithMarkup * $quantity;
			$productId = $db->Simple("SELECT id FROM shop_products WHERE name = '$productName' LIMIT 1");
			if (!$productId) {
				$db->query("INSERT INTO shop_products (name, types, purchase_price) VALUES ('$productName', '$type', '$purchasePrice')");
				$productId = $db->getInsertId();
			}
			$db->query("INSERT INTO shop_invoice_items (invoice_id, product_id, quantity, purchase_price, total_purchase, total_price_with_markup) 
						VALUES ('$invoiceId', '$productId', '$quantity', '$purchasePrice', '$totalPurchase', '$totalWithMarkup')");
		}
		echo "Зміни успішно збережені!";	
		break;	
	case 'worker': 
		if(isset($id) && $id>0){
		echo'<form action="/" method="post" class="form_center">
			<input type="hidden" name="do" value="calc">
			<input type="hidden" name="act" value="worker">
			<input type="hidden" name="id" value="'.$id.'">		
			<div class="pop-flex">		
				<select name="type" class="selects">
					<option value="5">Виконано</option>
					<option value="6">Аванс, підтвердили</option>
					<option value="7">Передумав</option>
					<option value="8">Подумають</option>
					<option value="9">Цікавились</option>
					<option value="10">Розрахунок</option>
					<option value="11">Очікуємо</option>
				</select>
				<input type="submit" value="'.$lang['save'].'">			
			</div>
			</form>';
		}
		break;	
	case 'products': 
        $productNames = [];
		$result = $db->SimpleWhile("SELECT name, types, purchase_price FROM shop_products");
		foreach ($result as $id => $row) {
			$productNames[] = [
				'name' => $row['name'],
				'type' => $row['types'],
				'purchase_price' => $row['purchase_price']
			];
		}
        echo json_encode($productNames);	
	break;	
	case 'driver':	

	break;
}
?>