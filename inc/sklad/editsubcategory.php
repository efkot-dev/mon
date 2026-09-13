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
$sql_cat = $pdo->prepare("SELECT * FROM sklad_sub_category WHERE id = :id");
$sql_cat->execute([':id' => $id]);
$get_cat = $sql_cat->fetch(PDO::FETCH_ASSOC);
if(!$get_cat){
	$go->go('/?do=tmc&act=category');
	exit;		
}
$delete_url = '/?do=tmc&act=delsubcategory&id='.$get_cat['id'];
$info_tovar = '';
$metatags = ['title'=>'Редагувати підкатегорію','description'=>'Редагувати підкатегорію','page'=>'editsubcategory'];
$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc&act=category"><i class="fi fi-rr-angle-left"></i>Категорії товарів</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагувати підкатегорію '.$get_cat['name'].'</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
$content .= '<form action="/?do=tmc" method="post"><input name="act" type="hidden" value="updatesubcategory"><div class="main_provider">';	
$content .= '<div class="form-provider"><label for="name">'.$lang['name'].': </label>
			<input class="wi300" type="text" name="name" id="name" value="'.$get_cat['name'].'" />
			<input type="hidden" name="id" id="id" value="'.$get_cat['id'].'" />
			</div>';		
$content .= '<div class="form-provider"><label for="model">Опис: </label>
			<textarea name="note" class="input1" rows="7" style="width: 50%;">'.$get_cat['note'].'</textarea></div>';		
$content .= '<div class="form-provider"><label for="name">Видалити розділ: </label>
			<a href="'.$delete_url.'">Видалити'.$info_tovar.'</a>
			</div>';
$content .= '</div><input type="submit" value="'.$lang['update'].'"></form>';
?>
