<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function PMon($data, $clock) {
    $deviceid = $data['id'] ?? null;
    $types = $data['types'] ?? 'system';
    $status = $data['status'] ?? 'normal';
    $message = preg_s($data['message']) ?? null;
    if ($message) {
        return sprintf(
            "INSERT INTO pmon_log (deviceid, types, status, message, added) VALUES (%s, %s, %s, %s, %s)",
            sql_log($deviceid),
            sql_log($types),
            sql_log($status),
            sql_log($message),
            sql_log($clock)
        );
    }
    return '';
}
function cpuLoad()
{
	$command = "top -bn1 | grep 'Cpu(s)' | awk '{print 100 - \$8}'";
	$cpu_usage = shell_exec(escapeshellcmd($command));
    return [
        'cpu_load' => [
            '1_min' => $cpu_usage,
        ]
    ];
}
function preg_s($output) {
	$output = str_replace('[b]','', $output);
	$output = str_replace('[/b]','', $output);
	return $output;
}
function sql_log($value, $force = false) {
    if ($value === null) {
        return "NULL"; // Повертаємо NULL як рядок, якщо значення є null
    }
    if (is_numeric($value) && !$force) {
        return $value; // Повертаємо числове значення без лапок
    }
    $value = str_replace(
        ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"],
        ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
        $value
    );
    return "'" . $value . "'"; // Огортаємо оброблене значення в лапки
}

?>
