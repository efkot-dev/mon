<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

define('QUEUE', true);
define('CONFIG', true);
require_once ROOT_DIR . '/inc/database.php';
require_once ENGINE_DIR . 'init.time.php';
require_once ENGINE_DIR . 'init.pmon.php';
require_once ENGINE_DIR . 'functions/pmon.php';
require_once ROOT_DIR . '/vendor/autoload.php';
require_once ENGINE_DIR . 'init.queue.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if ($mysqli->connect_error) {
    die("Error connect MYSQL: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT * FROM taskers");
if ($result->num_rows > 0) {
    $context = \Interop\Queue\Context::create();
    $queue = $context->createQueue('tasks');
    while ($task = $result->fetch_assoc()) {
        $current_time = time();
        $last_run_time = strtotime($task['last_run_time']);
        $interval = intval($task['interval']);
        $next_run_time = $last_run_time + $interval;
        if ($current_time >= $next_run_time) {
            $task_data = [
                'id' => $task['id'],
                'workid' => $task['workid'],
                'type' => $task['type'],
                'properties' => [],
                'headers' => []
            ];
            if ($task['type'] === 'monitor' && !empty($task['deviceid'])) {
                $task_data['device_id'] = $task['deviceid'];
                $task_data['properties'] = [2];
                $task_data['headers'] = [2];
            } elseif ($task['type'] === 'system') {
                $task_data['properties'] = [1];
                $task_data['headers'] = [1];
            }
            #$message = $context->createMessage(json_encode($task_data));
            #$context->createProducer()->send($queue, $message);
            $status = 'running';
			$mysqli->query("UPDATE taskers SET last_run_time = NOW(), status = '{$status}' WHERE id = '{$task['id']}'");
            echo "Task ID {$task['id']} (Type: {$task['type']}) has been sent to Redis.\n";
        } else {
            $time_remaining = $next_run_time - $current_time;
            echo "Task ID {$task['id']} (Type: {$task['type']}) is scheduled to run in {$time_remaining} seconds.\n";
        }
    }
} else {
    echo "No tasks found.\n";
}
$mysqli->close();
