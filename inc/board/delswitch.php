<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if(!$access->get('board_fault_edit')) {
	$go->go('/?do=board');
	exit;	
}
$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
if(isset($id) && $id>0){
    $stmt = $pdo->prepare("DELETE FROM incident_log_switch WHERE id = ?");
    $stmt->execute([$id]);
}
exit;
?>
