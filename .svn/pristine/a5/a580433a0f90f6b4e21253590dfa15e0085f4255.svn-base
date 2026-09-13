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
$metatags = ['title'=>'Редагувати каталог','description'=>'Редагувати каталог','page'=>'editcategory'];
$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc&act=category"><i class="fi fi-rr-angle-left"></i>Категорії товарів</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагувати каталог '.$get_cat['name'].'</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
$content .= '<form action="/?do=tmc" method="post" enctype="multipart/form-data"><input name="act" type="hidden" value="updatecategory"><div class="main_provider">';	
$content .= '<div class="form-provider"><label for="name">'.$lang['name'].': </label>
			<input class="wi300" type="text" name="name" id="name" value="'.$get_cat['name'].'" />
			<input type="hidden" name="id" id="id" value="'.$get_cat['id'].'" />
			</div>';		
$content .= '<div class="form-provider"><label for="model">Опис: </label>
			<textarea name="note" class="input1" rows="7" style="width: 50%;">'.$get_cat['note'].'</textarea></div>';		
			$types ='<select class="select" name="types" id="format">';
			$types .='<option value="1" '.($get_cat['types']==1 ? 'selected' : '').'>Метри</option>';
			$types .='<option value="2" '.($get_cat['types']==2 ? 'selected' : '').'>Кількість</option>';
			$types .='<option value="3" '.($get_cat['types']==3 ? 'selected' : '').'>Інв. номер</option>';
			$types .='</select>';
$content .= '<div class="form-provider"><label for="name">Облік ведеться по: </label>'.$types.'</div>';
$content .= '<div class="form-provider"><label for="name">Лого</label>' . (!empty($get_cat['img']) ? '<img style="height: 72px;" src="../file/photo/' .$get_cat['img'] . '">' : '') . '</div>';
$content .= '<div class="form-provider"><label for="name">Замінити: </label>
			<input type="file" id="file" name="file" multiple></div>';		
$content .= '</div><input type="submit" value="'.$lang['update'].'"></form>';
?>
