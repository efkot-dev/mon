<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$catid = isset($_GET['catid']) ? (int)$_GET['catid'] : 0;
$subid = isset($_GET['subid']) ? (int)$_GET['subid'] : 0;
$stmt_cat = $pdo->prepare("SELECT * FROM sklad_category WHERE id = :id");
$stmt_cat->bindParam(':id', $catid, PDO::PARAM_STR);
$stmt_cat->execute();
$categoria = $stmt_cat->fetch(PDO::FETCH_ASSOC);
$metatags = ['title'=> 'Категорія: '.$categoria['name'],'description'=>'Список категорій','page'=>'sklad'];
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc&act=get"><i class="fi fi-rr-angle-left"></i>Список категорій</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$categoria['name'].'</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';	
$content .= '
<div class="container">
	<div class="left-column">
		<div class="menu_olt_left">
			<div class="name_cat_lit">Категорії</div>
			<div id="categories-list"></div>
		</div>
	</div>
	<div class="right-column">
		<div id="products-list"></div>
	</div>
</div>';
if($subid>0){
	$ja = "?do=tmc&act=load_products&category_id={$catid}&cat_id={$subid}&page=1"; //".($subid>0 ? "&subid=".$subid : "")."
}else{
	$ja = "{$url_skald}&act=load_today&catid={$catid}";
}
$content .= "<script>
$(document).ready(function () {
$.ajax({
    url: '{$ja}',
    method: 'GET',
    success: function (response) {
        $('#products-list').html(response);
	}
});
$.ajax({
    url: '{$url_skald}&act=load_categories&catid={$catid}',
        method: 'GET',
        success: function (response) {
        $('#categories-list').html(response);
    }
});
$(document).on('click','.category-link',function (e) {
    e.preventDefault();
    var categoryId = $(this).data('category-id');
    loadProducts(categoryId, 1);
});
$(document).on('click','.page-link',function (e) {
    e.preventDefault();
    var categoryId = $(this).data('cat');
    var page = $(this).data('page');
    loadProducts(categoryId, page);
});
$(document).on('click','.page-new',function (e) {
    e.preventDefault();
    var categoryId = $(this).data('cat');
    var page = $(this).data('page');
    loadTodayProducts(page);
});
function loadProducts(categoryId, page) {
    $('#products-column').data('category-id', categoryId);
    $.ajax({
            url: '{$url_skald}&act=load_products',
            method: 'GET',
            data: { category_id: {$catid}, cat_id: categoryId, page: page },
            success: function (response) {
                $('#products-list').html(response);
            }
    });
}
function loadTodayProducts(page) {
    $.ajax({
            url: '{$url_skald}&act=load_today',
            method: 'GET',
            data: {catid: {$catid}, page: page },
            success: function (response) {
                $('#products-list').html(response);
            }
    });
}
});
</script>";
?>
