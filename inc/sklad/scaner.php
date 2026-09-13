<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$pdv_firma = (isset($confPMon['PDV_ISP']) && !empty($confPMon['PDV_ISP']) ? $confPMon['PDV_ISP'] : 20);
$stmt = $pdo->prepare("SELECT * FROM sklad_category ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt_sub = $pdo->prepare("SELECT * FROM sklad_sub_category ORDER BY name ASC");
$stmt_sub->execute();
$sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
$subCatData = [];
foreach ($sub_categories as $sub) {
    $subCatData[$sub['cat_id']][] = ['id' => $sub['id'], 'name' => $sub['name']];
}
$subCatJson = json_encode($subCatData, JSON_UNESCAPED_UNICODE);
$categoryOptions = "<option value=''></option>";
foreach ($categories as $category) {
    $categoryOptions .= "<option value='" . (int)$category['id'] . "'>" . $category['name'] . "</option>";
}
$metatags = [
    'title' => 'Додавання товару через сканер',
    'description' => 'Додавання товару через сканер',
    'page' => 'scaner'
];
$content .= '
<div class="container">
	<div class="left-column">
		<div class="menu_olt_left">
			<div>
				<form id="uploadForm">
					<input id="name" class="name"  name="name" type="text" required>
					<select style="margin-top:10px;width: 250px;" name="category_id" class="category-select">' . $categoryOptions . '</select>
					<select style="width:250px;margin-top:10px;" name="sub_id" class="sub-category-select"><option value=""></option></select>
					<span style="margin-top:10px;display:block;">Ціна <input autocomplete="off" autofocus="false" style="width: 70px;" class="price_js" name="price_js" type="text" required></span>
					<button class="send-btn" style="margin-top: 20px;" type="submit">Почати роботу</button>
				</form>
			</div>
		</div>
	</div>
	<div class="right-column">
		<div id="result"></div>
		<br><span id="saveProducts" class="reger_btn" style="display: none;">Додати в базу</span>
	</div>
</div>
<script>
let subCategories = ' . $subCatJson . ';
const pdvPercentage = ' . $pdv_firma . ';
$(document).ready(function(){
    let scannedProducts = {};
    $("#uploadForm").on("submit", function(e){
		$(".name").css({"pointer-events": "none","opacity": "0.5"});
		$(".price_js").css({"pointer-events": "none","opacity": "0.5"});
		$(".price_js").css({"pointer-events": "none","opacity": "0.5"});
		$(".category-select").css({"pointer-events": "none","opacity": "0.5"});
		$(".sub-category-select").css({"pointer-events": "none","opacity": "0.5"});
		$(".send-btn").hide();
		$("#saveProducts").show();
        e.preventDefault();
        startScanning();
    });  
    $(document).on("change", ".category-select", function () {
        let categoryId = $(this).val();
        let subSelect = $(this).closest("form").find(".sub-category-select");
        subSelect.empty().append("<option value=\'\'></option>");
        if (categoryId && subCategories[categoryId]) {
            subCategories[categoryId].forEach(sub => {
                subSelect.append("<option value=" + sub.id + ">" + sub.name + "</option>");
            });
        }
    });    
    function startScanning() {
        let scanContainer = $("<div>").attr("id", "scanContainer");
        let scanInput = $("<input>").attr({type: "text", id: "scanInput", autofocus: true, placeholder: "Скануйте код"}).css("width", "250px");
        $("#result").html(scanContainer.append(scanInput));
        $("#scanInput").focus();
    }    
    let scannedCode = "";
	$(document).on("keydown", "#scanInput", function(e) {
		if (e.key === "Enter") {
			e.preventDefault();
			if (scannedCode !== "") {
				addProduct(scannedCode);
				scannedCode = "";
				$(this).val("");
			}
		} else {
			scannedCode += e.key;
		}
	});    
    function addProduct(code) {
        let table = $("#productsTable");
        if (table.length === 0) {
            $("#result").append(`
                <br><table id="productsTable" class="resp-tab list-onu-olt">
                    <tr>
                        <th>К-ть</th>
                        <th>ШтрихКод</th>
                        <th>MAC-адреса</th>
                        <th>Ціна</th>
                        <th></th>
                    </tr>
                </table>
            `);
        }
        if (scannedProducts[code]) {
            let quantityInput = $(`input[name="quantity[]"][data-code="${code}"]`);
            quantityInput.val(parseInt(quantityInput.val()) + 1);
        } else {
			let real_price = $(\'input[name="price_js"]\').val();
			let real_price_pdv = $(\'input[name="pricepdv_js"]\').val();
            scannedProducts[code] = true;
            $("#productsTable").append(`
                <tr data-code="${code}">
                    <td><input style="width:70px;" name="quantity[]" type="number" min="1" value="1" data-code="${code}" required></td>
                    <td><input type="text" name="inventory_number[]" value="${code}"></td>
                    <td><input type="text" name="mac[]" value=""></td>
                    <td><input type="text" name="price[]" value="${real_price}"></td>
                    <td><span class="removeProduct">❌</span></td>
                </tr>
            `);
        }
    }    
    $(document).on("click", ".removeProduct", function () {
        let row = $(this).closest("tr");
        let code = row.attr("data-code");
        delete scannedProducts[code];
        row.remove();
    });
	$("#saveProducts").click(function () {
		let products = [];
		let name = $("input[name=\'name\']").val();
		let category_id = $("select[name=\'category_id\']").val();
		let sub_id = $("select[name=\'sub_id\']").val();
		$("#productsTable tr").each(function () {
			let product = {
				name: name,
				catid: category_id,
				subcatid: sub_id,
				mac: $(this).find("input[name=\'mac[]\']").val(),
				price: $(this).find("input[name=\'price[]\']").val(),
				inventory_number: $(this).find("input[name=\'inventory_number[]\']").val(),
				quantity: $(this).find("input[name=\'quantity[]\']").val()
			};
			products.push(product);
		});
		$.ajax({
			url: "'.$url_skald.'&act=savescaner",
			type: "POST",
			data: { products: products },
			success: function (response) {
				showNotification("Успішно","/?do=tmc&act=get");
			}
		});
	});
});
</script>
';
$speedbar .= '
	<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
	<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додавання товару через сканер</span>
';
$speedbar_block .= '<div id="onu-speedbar">' . $speedbar . '</div>';
?>
