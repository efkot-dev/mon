<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$auto = false;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id){
	$go->go('/?do=tmc&act=category');
	exit;		
}
$sql_cat = $pdo->prepare("SELECT * FROM sklad_category WHERE id = :id");
$sql_cat->execute([':id' => $id]);
$get_cat = $sql_cat->fetch(PDO::FETCH_ASSOC);
if(!$get_cat){
	$go->go('/?do=tmc&act=category');
	exit;		
}
$stmt = $pdo->prepare("SELECT * FROM sklad_category");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt_sub = $pdo->prepare("SELECT * FROM sklad_sub_category");
$stmt_sub->execute();
$sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
$subCatData = [];
foreach ($sub_categories as $sub) {
    $subCatData[$sub['cat_id']][] = ['id' => $sub['id'],'name' => $sub['name']];
}
$subCatJson = json_encode($subCatData, JSON_UNESCAPED_UNICODE);
$categoryOptions = "<option value=''>Оберіть категорію</option>";
foreach ($categories as $category) {
	if($category['id']!=$get_cat['id']){
		$categoryOptions .= "<option value='" . (int)$category['id'] . "'>" . $category['name'] . "</option>";
	}
}
$metatags = ['title'=>'Видалити каталог','description'=>'Видалити каталог','page'=>'delcategory'];
$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc&act=category"><i class="fi fi-rr-angle-left"></i>Категорії товарів</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Видалити каталог '.$get_cat['name'].'</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
$content = '
<form action="/?do=tmc" method="post">
	<input name="act" type="hidden" value="deletecategory">
	<div class="main_provider"><div class="form-provider">
		<label for="name">' . $lang['name'] . ': </label>' . $get_cat['name'] . '
		<input type="hidden" name="id" id="id" value="' . (int)$get_cat['id'] . '" />
	</div>
	<label for="name">Дії з товаром в категорії: </label>
	<select name="dia" class="tovar-dia">
		<option value="delete">Видалити</option>
		<option value="move">Перемістити</option>
	</select>
	<div id="move" style="display:none;">
		<br>
		<select name="category_id" class="category-select" style="width: 200px;">' . $categoryOptions . '</select>
		<br>
		<select name="sub_id" class="sub-category-select"><option value="">Оберіть підкатегорію</option></select>
	</div>
	</div>
	<input type="submit" value="Виконати">
</form>
<script>
let subCategories = ' . $subCatJson . ';
$(document).ready(function() {
    $(".tovar-dia").on("change", function () {
        let selectedAction = $(this).val();
        if (selectedAction === "move") {
            $("#move").show();
        } else {
            $("#move").hide();
        }
    });
    $(".category-select").on("change", function () {
        let categoryId = $(this).val();
        let subSelect = $(this).parent().find(".sub-category-select");
        subSelect.empty().append("<option value=\'\'>Оберіть підкатегорію</option>");
        if (categoryId && subCategories[categoryId]) {
            subCategories[categoryId].forEach(sub => {
                subSelect.append("<option value=\'" + sub.id + "\'>" + sub.name + "</option>");
            });
        }
    });
    $("form").on("submit", function () {
        let selectedAction = $(".tovar-dia").val();
        if (selectedAction !== "move") {
            $("#move select").val("");
        }
    });
});
</script>
';
?>
