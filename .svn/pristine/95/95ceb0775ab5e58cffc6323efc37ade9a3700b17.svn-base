<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
define('QUEUE', true);
require ENGINE_DIR . 'init.queue.php';
$switch_id  = Clean::int($_POST['id']  ?? 0);
$taskers_id = Clean::int($_POST['tid'] ?? 0);
if ($switch_id <= 0 || $taskers_id <= 0) {
    die('invalid');
}
$stmt = $pdo->prepare("
    SELECT id, workid, deviceid
    FROM taskers
    WHERE deviceid = :deviceid
      AND workid   = :workid
    LIMIT 1
");
$stmt->execute([
    'deviceid' => $switch_id,
    'workid'   => $taskers_id
]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$task) {
    die('not found');
}
$pmon_time = date('Y-m-d H:i:s');
$update = $pdo->prepare("
    UPDATE taskers
    SET last_run_time = :time
    WHERE id = :id
      AND (
            last_run_time IS NULL
         OR last_run_time = '0000-00-00 00:00:00'
         OR last_run_time < DATE_SUB(NOW(), INTERVAL 5 SECOND)
      )
");
$update->execute(['time' => $pmon_time,'id'=> $task['id']]);
if ($update->rowCount() === 0) {
    die('already');
}
$task_data = ['id'=> (int)$task['id'],'workid'=> (int)$taskers_id,'device_id'=> (int)$switch_id,'type'=> 'monitor'];
$message = $context->createMessage(json_encode($task_data));
$context->createProducer()->send($queue, $message);
die('ok');
?>
