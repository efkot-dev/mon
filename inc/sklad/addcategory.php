<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$auto = false;
$metatags = ['title'=>$lang['sklad_catalog'],'description'=>$lang['sklad_catalog'],'page'=>'catalog'];
$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc&act=category"><i class="fi fi-rr-angle-left"></i>Категорії товарів</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати нову категорію</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
$content .= '<form action="/?do=tmc" method="post" enctype="multipart/form-data">
			<input name="act" type="hidden" value="savecatalog">
				<div class="main_provider">';	
$content .= '<div class="form-provider"><label for="name">'.$lang['name'].': </label>
			<input class="wi300" type="text" name="name" id="name" required /></div>';		
$content .= '<div class="form-provider"><label for="model">Опис: </label>
			<textarea name="note" class="input1" rows="7" style="width: 50%;"></textarea></div>';		
			$types ='<select class="select" name="types" id="format">';
			$types .='<option value="1">Метри</option>';
			$types .='<option value="2">Кількість</option>';
			$types .='<option value="3">Інв. номер</option>';
			//$types .='<option value="4">МАС</option>';
			$types .='</select>';
$content .= '<div class="form-provider"><label for="name">Облік ведеться по: </label>'.$types.'</div>';
$content .= '<div class="form-provider"><label for="name">Фото: </label>
			<input type="file" id="file" name="file" multiple></div>';		
$content .= '</div><input type="submit" value="'.$lang['addeds'].'"></form>';
?>
