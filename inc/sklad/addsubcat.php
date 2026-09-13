<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$auto = false;
$catid = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$sql_cat = "SELECT * FROM sklad_category WHERE id = :category_id";
$cat = $pdo->prepare($sql_cat);
$cat->bindParam(':category_id', $catid, PDO::PARAM_INT);
$cat->execute();
$result_category = $cat->fetch(PDO::FETCH_ASSOC);	
$metatags = ['title'=>'Додати підкатегорію','description'=>'Додати підкатегорію','page'=>'catalog'];
$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc&act=category"><i class="fi fi-rr-angle-left"></i>Категорії товарів</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати підкатегорію</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
$content .= '<form action="/?do=tmc" method="post">
			<input name="act" type="hidden" value="savesubcat">
			<input name="catid" type="hidden" value="'.$result_category['id'].'">
				<div class="main_provider">';	
$content .= '<div class="form-provider"><label for="name">Категорія: </label>
			<b>'.$result_category['name'].'</b></div>';	
$content .= '<div class="form-provider"><label for="code">Код категорії (використовується в штрихкоді): </label>
			<input class="wi300" type="text" name="code" id="code" /></div>';
$content .= '<div class="form-provider"><label for="name">'.$lang['name'].' підкатегорії: </label>
			<input class="wi300" type="text" name="name" id="name" required /></div>';		
$content .= '<div class="form-provider"><label for="model">Опис: </label>
			<textarea name="note" class="input1" rows="7" style="width: 50%;"></textarea></div>';		
$content .= '</div><input type="submit" value="'.$lang['addeds'].'"></form>';
?>
