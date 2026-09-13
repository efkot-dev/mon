<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$file = true;
$sqlinsert = array();
if(isset($_POST['name'])) 
	$sqlinsert['name'] = Clean::text($_POST['name']);	
if(isset($_POST['types'])) 
	$sqlinsert['types'] = Clean::int($_POST['types']);
if($sqlinsert['types']==3){
	$sqlinsert['number'] = 'yes';
}else{
	$sqlinsert['number'] = 'no';	
}
if(isset($_POST['note'])) 
	$sqlinsert['note'] = Clean::text($_POST['note']);
		if(!empty($_FILES['file']['name'])) {
			if (
				!isset($_FILES['file']) ||
				!isset($_FILES['file']['type']) ||
				!array_key_exists($_FILES['file']['type'], $allowed_types) ||
				!preg_match('/^(.+)\.(jpg|jpeg|png)$/i', $_FILES['file']['name'])
			) {
				$file = false;
			}
			if($file){
				$newname = substr(md5(uniqid(rand(), true)), 0, rand(7, 13)).'.'.$allowed_types[$_FILES['file']['type']];
				$copy = @copy($_FILES['file']['tmp_name'], $uploaddir.$newname);
				if(!$copy){
				
				}else{
					$sqlinsert['img'] = $newname;
				}
			}
		}else{
			$sqlinsert['img'] = 'pmon_category.jpeg';
		}
if(!empty($sqlinsert['name'])){
	$db->SQLinsert('sklad_category',$sqlinsert);
	$go->go('/?do=tmc&act=category');
	exit;	
}
$go->go('/?do=tmc');
exit;	
?>
