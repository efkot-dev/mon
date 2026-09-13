<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
define('BOARD' , true);
$timer = date('Y-m-d H:i:s');
require ROOT_DIR . '/inc/init.core.php';
require ROOT_DIR . '/inc/functions/sql_pdo.php';
require ROOT_DIR . '/inc/functions/core.php';
require ROOT_DIR . '/inc/functions/board.php';
if (isset($confPMon['BOARD_FAULT']) && !empty($confPMon['BOARD_FAULT']) && $confPMon['BOARD_FAULT'] == 1) {
$stmt = $pdo->prepare("SELECT id, message FROM incident WHERE cron = '1' AND status = 'open' AND restore_time <= ?");
$stmt->execute([$timer]);
$message_send = [];
$incidents_to_close = $stmt->fetchAll(PDO::FETCH_ASSOC);
if(!empty($incidents_to_close)) {
$update_stmt = $pdo->prepare("UPDATE incident SET cron = '3', status = 'closed' WHERE id = ?");
foreach ($incidents_to_close as $incident) {
if (isset($incident['message']) && $incident['message'] == 3) {
$message_send[] = $incident['id'];
}
$update_stmt->execute([$incident['id']]);
}
}
if (!empty($message_send)) {
$stmt = $pdo->prepare("SELECT value FROM config WHERE name = ?");
$stmt->execute(['template_end_border_telegram']);
$template_message = $stmt->fetchColumn();
foreach ($message_send as $send_incident_id) {
$message = 'Закриття аварійних робіт';
LogBoard($pdo,$send_incident_id,$message,0,$timer);
$sql_board = $pdo->prepare("SELECT * FROM incident WHERE id = :id");
$sql_board->execute(['id' => $send_incident_id]);
$data_board = $sql_board->fetch(PDO::FETCH_ASSOC);
$data = ['name'=>$data_board['reason'],'description'=>$data_board['description'],'end_time'=>$timer,'start_time'=>$data_board['start_time']];
$message = TemplateMessage($pdo, $data, $send_incident_id, $template_message);
$board_config = ConfigBoardTelegram($pdo);
SendBoardTelegram($board_config,$message);
$update_stmt_s = $pdo->prepare("UPDATE incident SET message = '1' WHERE id = ?");
$update_stmt_s->execute([$send_incident_id]);
}
}
}
exit;
?>
