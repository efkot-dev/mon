<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$color_signal = '';
function my_im($delimiter, $string) {
    $array = explode($delimiter, $string);
    $array = array_unique($array);
    $array = array_map('trim', $array);
    $array = array_map(function($value) {
        return "'" . addslashes($value) . "'";
    }, $array);
    return implode(',', $array);
}
$vlan = isset($_POST['vlan']) ? Clean::text($_POST['vlan']): null;
if(!$vlan){
	die('Відсутня інформація');	
}else{
	if (is_numeric($vlan)) {
		$getsql = "SELECT * FROM `ipvlans` WHERE `vlan` = '{$vlan}' LIMIT 1";
		$sql_result = $db->Simple($getsql);

		if ($sql_result && count($sql_result) > 0) {
			echo '<div class="windows_vlan">'.$sql_result['name'].'</div>';
		} else {
			echo '';
		}
	} else {
		$get = my_im(',', $vlan); // Припускаючи, що `imrobe` повертає коректний SQL-фільтр
		$getsql = "SELECT * FROM `ipvlans` WHERE `vlan` IN ({$get})";

		$sql_result = $db->SimpleWhile($getsql);

		if ($sql_result && count($sql_result) > 0) {
			echo'<div class="windows_vlan">';
			foreach ($sql_result as $data) {
				echo '' . htmlspecialchars($data['vlan'], ENT_QUOTES, 'UTF-8') . ') ' . htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8') . '<br>';
			}
			echo'</div>';
		} else {
			echo '';
		}
	}
}
?>