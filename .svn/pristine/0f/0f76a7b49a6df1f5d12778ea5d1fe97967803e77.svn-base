<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_edit')) {
    $go->go('/?do=board');
    exit;
}

$updated_at = date('Y-m-d H:i:s');
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim((string)($_POST['name'] ?? ''));
$description = trim((string)($_POST['content'] ?? ''));
$cron = (int)($_POST['cron'] ?? 0);
$startInput = trim((string)($_POST['start_time'] ?? ''));
$endInput = trim((string)($_POST['end_time'] ?? ''));

if ($id <= 0 || $name === '' || $startInput === '' || $endInput === '' || empty($USER['id'])) {
    $go->go('/?do=board');
    exit;
}

try {
    $startTime = (new DateTime($startInput))->format('Y-m-d H:i:s');
    $endTime = (new DateTime($endInput))->format('Y-m-d H:i:s');
} catch (Throwable $e) {
    $go->go('/?do=board&act=view&id=' . $id);
    exit;
}

$sql = 'UPDATE incident
        SET updated_at = :updated_at,
            reason = :name,
            description = :description,
            start_time = :start_time,
            restore_time = :end_time,
            cron = :cron
        WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':name' => $name,
    ':description' => $description,
    ':updated_at' => $updated_at,
    ':start_time' => $startTime,
    ':end_time' => $endTime,
    ':cron' => $cron,
    ':id' => $id,
]);

$go->go('/?do=board&act=view&id=' . $id);
exit;
?>
