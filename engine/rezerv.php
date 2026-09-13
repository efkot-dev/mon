<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.monitor.php';
$yesterday = date('Y-m-d', strtotime('-1 day'));
$check_sql = "SELECT COUNT(*) as count FROM bandwidth_daily WHERE date = :yesterday";
$stmt = $pdo->prepare($check_sql);
$stmt->execute(['yesterday' => $yesterday]);
$row_check = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row_check['count'] == 0) {
    $ports_sql = "SELECT DISTINCT portid FROM snmp_data WHERE DATE(timestamp) = :yesterday";
    $stmt_ports = $pdo->prepare($ports_sql);
    $stmt_ports->execute(['yesterday' => $yesterday]);
    while ($port = $stmt_ports->fetch(PDO::FETCH_ASSOC)) {
        $portid = $port['portid'];
        $traffic_sql = "
            SELECT 
                HOUR(timestamp) AS hour, 
                MINUTE(timestamp) AS minute,
                in_bps, 
                out_bps 
            FROM snmp_data 
            WHERE portid = :portid AND DATE(timestamp) = :yesterday
            ORDER BY timestamp ASC";        
        $stmt_traffic = $pdo->prepare($traffic_sql);
        $stmt_traffic->execute([
            'portid' => $portid,
            'yesterday' => $yesterday
        ]);
        $traffic_array = [];
        while ($row = $stmt_traffic->fetch(PDO::FETCH_ASSOC)) {
            $traffic_array[] = [
                'time' => sprintf("%02d:%02d", $row['hour'], $row['minute']),
                'in_bps' => (float) $row['in_bps'],
                'out_bps' => (float) $row['out_bps']
            ];
        }
        $traffic_json = json_encode($traffic_array, JSON_UNESCAPED_UNICODE);
        $insert_sql = "
            INSERT INTO bandwidth_daily (portid, date, traffic_day)
            VALUES (:portid, :yesterday, :traffic_json)
            ON DUPLICATE KEY UPDATE traffic_day = VALUES(traffic_day)";
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute([
            'portid' => $portid,
            'yesterday' => $yesterday,
            'traffic_json' => $traffic_json
        ]);
    }
    $delete_sql = "DELETE FROM snmp_data WHERE DATE(timestamp) = :yesterday";
    $stmt_delete = $pdo->prepare($delete_sql);
    $stmt_delete->execute(['yesterday' => $yesterday]);
}
$delete_sql_snmp_data = "DELETE FROM snmp_data WHERE timestamp < CURDATE() - INTERVAL 2 DAY";
$stmt_delete_snmp_data = $pdo->prepare($delete_sql_snmp_data);
$stmt_delete_snmp_data->execute();
$delete_sql_bandwidth_daily = "DELETE FROM bandwidth_daily WHERE created_at < CURDATE() - INTERVAL 30 DAY";
$stmt_delete_bandwidth_daily = $pdo->prepare($delete_sql_bandwidth_daily);
$stmt_delete_bandwidth_daily->execute();
?>
