<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$ping_values = [];
if ($id !== null) {
$sqlping = $db->SimpleWhile("SELECT * FROM monitor_ip_log WHERE ip_id = " .$id);
	if(isset($sqlping) && count($sqlping)>0){
		foreach ($sqlping as $chatId => $data) {
			$ping_values[] = $data['time'];
		}
	}
    echo json_encode($ping_values);
} else {
    echo json_encode(array('error' => 'err_id'));
}
?>
