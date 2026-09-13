<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
$time_limit = date("Y-m-d H:i:s", strtotime("-20 minutes"));
$stmt = $pdo->query("SELECT ip_address, MAX(last_attempt) as last_attempt, COUNT(*) as total_attempts
                    FROM login_attempts 
                    GROUP BY ip_address 
                    HAVING total_attempts > 5");
$blocked_ips = $stmt->fetchAll();
foreach ($blocked_ips as $ip) {
    if ($ip['last_attempt'] < $time_limit) {
        $delete_stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $delete_stmt->execute([$ip['ip_address']]);
    }
}
$systemtime = time();
$timeout = 30 * 60;
$basePath = ROOT_DIR . '/export/cache/';
$files = glob($basePath . '*.pid');
function isProcessRunning($pid) {
    return posix_kill($pid, 0);
}
$logDir = ROOT_DIR . '/export/log/';
$logFile = $logDir . 'kill_' . date('Y-m-d') . '.log';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}
function logKilledProcess($pid, $cmd) {
    global $logFile;
    $logMessage = date('Y-m-d H:i:s') . " - Killed Process: PID=$pid, Command=$cmd\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
if (isset($files)) {
    foreach ($files as $file) {
        $pid = intval(pathinfo($file, PATHINFO_FILENAME));
        $isRunning = isProcessRunning($pid);
        if (!$isRunning) {
            unlink($file);
        } else {
            $fileContent = file_get_contents($file);
            $existingData = json_decode($fileContent, true);
            if (isset($existingData['added'])) {
                $processStartTime = strtotime($existingData['added']);
                $elapsedTime = $systemtime - $processStartTime;
                if ($elapsedTime >= $timeout) {
                    unlink($file);
                    posix_kill($pid, SIGTERM);
                    logKilledProcess($pid, $existingData['cmd']);  // Логування знищеного процесу
                }
            }
        }
    }
}
function getProcessStartTime($pid) {
    $output = [];
    exec("ps -p $pid -o lstart=", $output);
    return isset($output[0]) ? strtotime($output[0]) : false;
}
$currentTime = time();
exec("ps aux | grep 'php poller.php' | grep -v 'grep'", $processes);
foreach ($processes as $process) {
    preg_match('/\s*(\d+)\s/', $process, $matches);
    $pid = $matches[1] ?? null;
    if ($pid) {
        $startTime = getProcessStartTime($pid);
        if ($startTime !== false) {
            $duration = $currentTime - $startTime;
            if ($duration > 1800) {
                exec("kill -9 $pid");
                logKilledProcess($pid, $process);  // Логування знищеного процесу з командою
            }
        }
    }
}
?>
