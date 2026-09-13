<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if(!$access->get('board_fault_edit')) {
	$go->go('/?do=board');
	exit;	
}
$incidents_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(isset($incidents_id) && $incidents_id > 0) {
	$sql_board = $pdo->prepare("SELECT * FROM incident WHERE id = :id");
	$sql_board->execute(['id' => $incidents_id]);
	$data_board = $sql_board->fetch(PDO::FETCH_ASSOC);
	if (!$data_board) {
		$go->go('/?do=board');
		exit;
	}
	$data = [
		'name' => $data_board['reason'],
		'description' => $data_board['description'],
		'end_time' => $data_board['restore_time'],
		'start_time' => $data_board['start_time'],
		'cron' => $data_board['cron']
	];
	$stmt = $pdo->prepare("SELECT value FROM config WHERE name = ?");
	$stmt->execute(['template_new_border_telegram']);
	$template_new = $stmt->fetchColumn();
	$message = RepeatTemplateMessage($pdo, $data, $incidents_id, $template_new);
	$board_config = ConfigBoardTelegram($pdo);
	SendBoardTelegram($board_config,$message);
}
$go->go('/?do=board&act=view&id='.$incidents_id);
exit;
?>
