<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$pdv_firma = (isset($confPMon['PDV_ISP']) && !empty($confPMon['PDV_ISP']) ? $confPMon['PDV_ISP'] : 20);
function fun_unit($id){
	if($id==1){
		return 'метраж';	
	}elseif($id==2){
		return 'кількість';	
	}elseif($id==3){
		return 'інвертарний номер';		
	}
}
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$go = isset($_POST['go']) ? Clean::text($_POST['go']) : null;
if($id > 0){
	$cat = $pdo->prepare("SELECT * FROM sklad_tovar WHERE id = :id");
	$cat->bindParam(':id', $id, PDO::PARAM_INT);
	$cat->execute();
	$result_tovar = $cat->fetch(PDO::FETCH_ASSOC);	
switch ($go) {
    case 'name':
        echo '
			<div class="main_provider">
				<div class="form-provider">
					<label for="name">Назва: </label>
						<input class="wi300" type="text" name="name" id="name" required="" value="'.$result_tovar['name'].'">
					</div>
					<div class="form-provider">
						<label for="model">Короткий опис:</label>
						<textarea name="description" id="description" class="input1" rows="7" style="width: 50%;">'.$result_tovar['description'].'</textarea>
					</div>
					<div class="key-btn">
						<span class="reger_btn add_opis_tovar" data-id="'.$id.'"  >Оновити</span>
					</div>
				</div>			
			';
            break;
    case 'update':
		if (isset($_POST['name'])) {
			$name = Clean::text($_POST['name']);
		}
		if (isset($_POST['id'])) {
			$id = Clean::int($_POST['id']);
		}
		if (isset($_POST['description'])) {
			$description = Clean::text($_POST['description']);
		}
		if(isset($id) && $id > 0 && isset($name) && !empty($name)){
			$sql_update = $pdo->prepare("UPDATE sklad_tovar SET name = :name, description = :description WHERE id = :id");
			$sql_update->bindParam(':id', $id);
			$sql_update->bindParam(':name', $name);
			$sql_update->bindParam(':description', $description);
			$sql_update->execute();
		}
		die;
        break;
	case 'move':
		if(isset($id) && $id>0){
			$get_tovar = $pdo->prepare("SELECT * FROM sklad_tovar WHERE id = :id");
			$get_tovar->execute([':id' => $id]);
			$tovar = $get_tovar->fetchAll(PDO::FETCH_ASSOC);
			// $tovar['quantity'] кількість товар
			$stmt = $pdo->prepare("SELECT * FROM sklad_category ORDER BY name ASC");
			$stmt->execute();
			$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt_sub = $pdo->prepare("SELECT * FROM sklad_sub_category ORDER BY name ASC");
			$stmt_sub->execute();
			$sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
			$subCatData = [];
			foreach ($sub_categories as $sub) {
				$subCatData[$sub['cat_id']][] = ['id' => $sub['id'],'name' => $sub['name']];
			}
			$subCatJson = json_encode($subCatData, JSON_UNESCAPED_UNICODE);
			if($categories){
				$categoryOptions = "<option value=''></option>";
				foreach ($categories as $category) {
					$categoryOptions .= "<option value='" . (int)$category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
				}
			}
			echo'
				<div class="main_provider">
					<form id="uploadForm">
						<select id="category_id"  name="category_id" class="category-select">' . $categoryOptions . '</select>
						<select id="sub_id"  name="sub_id" class="mt10 sub-category-select"><option value=""></option></select>
					</form>
					<div class="key-btn">
						<span class="reger_btn add_move_tovar" data-id="'.$id.'">Перемістити</span>
					</div>
				</div>
				<script>
					let subCategories = '.$subCatJson.';
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
					});
				</script>
			';
		}
		die;
        break; 		
	case 'updatemove':
		if (isset($_POST['id'])) {
			$id = Clean::int($_POST['id']);
		}
		if (isset($_POST['category_id'])) {
			$category_id = Clean::int($_POST['category_id']);
		}
		if (isset($_POST['sub_id'])) {
			$sub_cat_id = Clean::int($_POST['sub_id']);
		}
		if(isset($sub_cat_id) && $sub_cat_id>0 && isset($category_id) && $category_id>0 && isset($id) && $id>0){
			$cat = $pdo->prepare("SELECT * FROM sklad_category WHERE id = :category_id");
			$cat->bindParam(':category_id', $category_id, PDO::PARAM_INT);
			$cat->execute();
			$result_category = $cat->fetch(PDO::FETCH_ASSOC);
			$sql_update = $pdo->prepare("UPDATE sklad_tovar SET category_id = :category_id, oblik = :oblik, unit = :unit, sub_cat_id = :sub_cat_id WHERE id = :id");
			$sql_update->bindParam(':id', $id);
			$sql_update->bindParam(':oblik', $result_category['types']);
			$sql_update->bindParam(':unit', fun_unit($result_category['types']));
			$sql_update->bindParam(':category_id', $category_id);
			$sql_update->bindParam(':sub_cat_id', $sub_cat_id);
			$sql_update->execute();			
		}
		die;
        break;	
	case 'hide':
		$hide = 'yes';
		if($result_tovar['hide']=='yes'){
			$hide = 'no';
		}
		if (isset($_POST['id'])) {
			$id = Clean::int($_POST['id']);
		}
		if(isset($id) && $id > 0){
			$sql_update = $pdo->prepare("UPDATE sklad_tovar SET hide = :hide WHERE id = :id");
			$sql_update->bindParam(':id', $id);
			$sql_update->bindParam(':hide', $hide);
			$sql_update->execute();
		}
		die;
        break;		
	case 'price':
		echo'
			<div class="main_provider">
				<form id="uploadForm" style="text-align: left;">
					<span style="display:block;"><font color="green">Ціна</font> 
					<input style="width: 70px;" id="price"  name="price" type="text" required value="'.$result_tovar['price'].'" autocomplete="off" autocorrect="off" spellcheck="false"></span>
				</form>
				<div class="key-btn">
					<span class="reger_btn add_price_tovar" data-id="'.$id.'">Оновити ціну</span>
				</div>
			</div>
			';
			die;
        break;		
	case 'updateprice':
        $price = isset($_POST['price']) ? (float)$_POST['price'] : null;
		if(!empty($price)){
			$sql_update = $pdo->prepare("UPDATE sklad_tovar SET price = :price WHERE id = :id");
			$sql_update->bindParam(':id', $result_tovar['id']);
			$sql_update->bindParam(':price', $price);
			$sql_update->execute();
		}
		die;
        break;	
	case 'move11':
        echo 'menu3';
        break;
	default:
		echo'<div class="ui stackable grid">
		<div class="column ui list">
			<a data-id="'.$id.'" class="edit_tovar_opis ui mini compact basic fluid icon button mb-5">
			<i class="fi fi-rr-edit"></i>
			Редагувати опис</a>
			<a data-id="'.$id.'" class="edit_tovar_move ui mini compact basic fluid icon button mb-5">
			<i class="fi fi-rr-arrows"></i>
			Перемістити</a>
			<a data-id="'.$id.'" class="hide_tovar_list ui mini compact basic fluid icon button mb-5">
			<i class="fi fi-rr-eye-crossed"></i>
			'.($result_tovar['hide']=='yes'?'<font color="blue">Відкрити</font>':'Приховати').'</a>
		</div>
			<div class="column ui list">
			<a data-id="'.$id.'"  class="price_tovar_list ui mini compact basic fluid icon button mb-5"><font color="green">
			<i class="fi fi-rr-money"></i></font>
			Змінити ціну</a>
			<a data-id="'.$id.'"  class="ui mini compact basic fluid icon button mb-5"><font color="blue">
			<i class="fi fi-rr-headset"></i></font>
			Перемістити в сервіс</a>
			<a data-id="'.$id.'"  class="ui mini compact basic fluid icon button mb-5"><font color="red">
			<i class="fi fi-rr-lock"></i></font>
			Заблокувати видачу</a>
		</div>
	</div>';		
	}
}
die;
?>
