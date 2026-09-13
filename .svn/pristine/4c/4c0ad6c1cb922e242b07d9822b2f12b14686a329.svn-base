<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$table = '';
$price = isset($_POST['price']) ? $_POST['price'] : 0;
$pricepdv = isset($_POST['pricepdv']) ? $_POST['pricepdv'] : 0;
$catid = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$subcatid = isset($_POST['sub_id']) ? (int)$_POST['sub_id'] : 0;
if(!$catid || !$subcatid){
	die('');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $file_content = file($fileTmpPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $table = '<table id="productsTable" class="resp-tab list-onu-olt"><tr>
		<th>Назва</th>
		<th>Кількість</th>
		<th>Інвентарний номер</th>
		<th>MAC-адреса</th>
		<th>Ціна</th>
		<th>з ПДВ</th>
		<th></th>
		</tr>';
    foreach ($file_content as $line) {
        $parts = str_getcsv($line);        
        $name = $parts[0] ?? '';
        $quantity = $parts[1] ?? '';
        $inventory_number = $parts[2] ?? '';
        $mac = $parts[3] ?? '';
        $table .= '<tr>';
        $table .= '<td width="40%"><input name="name[]" type="text" value="' . htmlspecialchars($name) . '" required></td>';
        $table .= '<td width="7%"><input name="quantity[]" type="number" min="0" value="' . htmlspecialchars($quantity) . '" required></td>';
        $table .= '<td><input type="text" name="inventory_number[]" value="' . htmlspecialchars($inventory_number) . '"></td>';
		$table .= '<td><input type="text" name="mac[]" value="' . htmlspecialchars($mac) . '"></td>';
		$table .= '<td width="7%"><input type="text" name="price[]" value="' . htmlspecialchars($price) . '"></td>';
		$table .= '<td width="7%"><input type="text" name="price_pdv[]" value="' . htmlspecialchars($pricepdv) . '"></td>';
        $table .= '<td width="5%"><span class="removeProduct">❌</span></td>';
        $table .= '</tr>';
    }
    $table .= '</table>';
    $table .= '<span id="saveProducts" class="reger_btn">Імпортувати дані</span>';
	$table .= '
		<script>
		$(document).ready(function(){
			$("#saveProducts").click(function() {
				let products = [];
				console.log(products);
				$("#productsTable tbody tr").each(function () {
					let product = {
						mac: $(this).find("input[name=\'mac[]\']").val(),
						name: $(this).find("input[name=\'name[]\']").val(),
						quantity: $(this).find("input[name=\'quantity[]\']").val(),
						price_pdv: $(this).find("input[name=\'price_pdv[]\']").val(),
						price: $(this).find("input[name=\'price[]\']").val(),
						inventory_number: $(this).find("input[name=\'inventory_number[]\']").val()
					};
					if (product.name !== "" && product.quantity !== "") {
						products.push(product);
					}
				});
				$.ajax({
					url: "'.$url_skald.'&act=saveimport",
					type: "POST",
					data: { products: products, catid: '.$catid.', subcatid: '.$subcatid.' },
					success: function (response) {
						showNotification("Успішно імпортовані дані","/?do=tmc&act=list&catid='.$catid.'&subid='.$subcatid.'");
					}
				});
			});
		});
	</script>';
}
echo $table;
exit;
?>
