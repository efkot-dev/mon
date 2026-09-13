<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_edit')) {
    $go->go('/?do=board');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $go->go('/?do=board');
    exit;
}

$comment = trim((string)($_POST['content'] ?? ''));
$endInput = trim((string)($_POST['end_time'] ?? ''));

try {
    $restore_time = $endInput !== ''
        ? (new DateTime($endInput))->format('Y-m-d H:i:s')
        : date('Y-m-d H:i:s');
} catch (Throwable $e) {
    $restore_time = date('Y-m-d H:i:s');
}

try {
    $pdo->beginTransaction();

    $sql_board = $pdo->prepare('SELECT * FROM incident WHERE id = :id FOR UPDATE');
    $sql_board->execute(['id' => $id]);
    $data_board = $sql_board->fetch(PDO::FETCH_ASSOC);

    if (!$data_board) {
        $pdo->rollBack();
        $go->go('/?do=board');
        exit;
    }

    $status = 'closed';
    $update = $pdo->prepare(
        'UPDATE incident
         SET comment = :comment,
             user_id_close = :user_id_close,
             restore_time = :restore_time,
             status = :status
         WHERE id = :id'
    );
    $update->execute([
        ':comment' => $comment,
        ':status' => $status,
        ':user_id_close' => $USER['id'],
        ':restore_time' => $restore_time,
        ':id' => $id,
    ]);

    LogBoard($pdo, $id, 'Закриття аварійних робіт', $USER['id'], $time);
    $pdo->commit();

    $stmt = $pdo->prepare('SELECT value FROM config WHERE name = ?');
    $stmt->execute(['template_end_border_telegram']);
    $template_message = $stmt->fetchColumn();

    $data = [
        'name' => $data_board['reason'],
        'description' => $data_board['description'],
        'end_time' => $restore_time,
        'start_time' => $data_board['start_time']
    ];

    $telegramMessage = TemplateMessage($pdo, $data, $id, $template_message);
    $board_config = ConfigBoardTelegram($pdo);
    SendBoardTelegram($board_config, $telegramMessage);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

$go->go('/?do=board');
exit;
?>
