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
    $subCatData[$sub['cat_id']][] = [
        'id' => $sub['id'],
        'name' => $sub['name']
    ];
}
$subCatJson = json_encode($subCatData, JSON_UNESCAPED_UNICODE);
$categoryOptions = "<option value=''></option>";
foreach ($categories as $category) {
    $categoryOptions .= "<option value='" . (int)$category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
}
$metatags = [
    'title' => 'Імпорт з Exel',
    'description' => 'Імпорт з Exel',
    'page' => 'import'
];
$content .= '<div class="container"><div class="left-column">';
$content .= '<div class="menu_olt_left">';
$content .= '<div>
    <form id="uploadForm" enctype="multipart/form-data">
        <select style="width: 250px;" name="category_id" class="category-select">' . $categoryOptions . '</select>
        <select style="width:250px;margin-top:10px;" name="sub_id" class="sub-category-select"><option value=""></option></select>
        <input style="margin-top:10px;" type="file" name="file" id="fileInput" required>
        <span style="margin-top:10px;display:block;">Ціна <input style="width: 70px;" name="price" type="text" required></span> <!-- Додано name="price" -->
                <button class="send-btn" style="margin-top: 20px;" type="submit">Завантажити</button>
    </form>        
</div>';
$content .= '</div></div><div class="right-column">';
$content .= '<div id="result"></div>';
$content .= '</div></div>';
$content .= '
<script>
let subCategories = '.$subCatJson.';
const pdvPercentage = '.$pdv_firma.';
$(document).ready(function(){
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
    $("#uploadForm").on("submit", function(e){
        e.preventDefault();        
        var formData = new FormData(this);  
        let price = $("input[name=\'price\']").val();
        formData.append("price", price);
        $("#result").html(\'<div id="loading">Завантаження...</div>\');
        $(".category-select").css({"pointer-events": "none", "opacity": "0.5"});
        $("#fileInput").css({"display": "none"});
        $(".send-btn").css({"display": "none"});
        $(".sub-category-select").css({"pointer-events": "none", "opacity": "0.5"});
        $.ajax({
            url: "/?do=tmc&act=temp",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $("#result").html(response);
            }
        });
    });
    $(document).on("click", ".removeProduct", function () {
        $(this).closest("tr").remove();
    });
});
</script>
';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Імпортувати дані з Exel</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
?>
