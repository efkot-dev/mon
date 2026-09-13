<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');
require_once ENGINE_DIR.'ajax.php';
if(isset($_POST['idonu'])){
$item_per_page 	= 7; 
$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']): null;
if(is_valid_id($idonu)){
	if(isset($_POST["page"])){
		$page_number = filter_var($_POST["page"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);
		if(!is_numeric($page_number)){die('Invalid page number!');} 
	}else{
		$page_number = 1; 
	}
	$count_log = $db->Simple("SELECT COUNT(*) as count FROM `onus_log` WHERE idonu = ".$idonu);
	if(isset($count_log['count']) && $count_log['count']>0){
		echo '<div class="block_log_ont">';
		$total_pages = ceil($count_log['count']/$item_per_page);
		$page_position = (($page_number-1) * $item_per_page);
		$res = $db->SimpleWhile("SELECT * FROM onus_log WHERE idonu = ".$idonu." ORDER BY `added` ASC LIMIT ".$page_position.", ".$item_per_page."");
		foreach ($res as $id => $log) {
			$icon = ''; 
			if($log['types'] =='status'){
				$icon = '<span class="statuslog st_'.$log['status'].'"></span>';
			}
			echo'<div class="logonu"><span>'.$log['added'].'</span>
				<h2>'.$icon.''.$log['message'].'</h2></div>';
		}
		echo '<div align="center">';
		echo paginate_function($item_per_page, $page_number, $get_total_rows, $total_pages);
		echo '</div>';	
		echo '</div>';	
	}
}
}
?>