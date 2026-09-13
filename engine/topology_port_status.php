<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.monitor.php';
$time = date('Y-m-d H:i:s');
$stmt = $pdo->query("SELECT id, node_id, port, last_status, snmp_ip, snmp_ro, snmp_oid, bind_switch_id, bind_llid FROM topology_ports");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
if (empty($rows)) {
    return;
}
$nodesRaw = $pdo->query("SELECT id, uid, name FROM topology_nodes")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$nodeById = [];
foreach ($nodesRaw as $n) {
    $nodeById[(int)$n['id']] = ['uid' => (string)$n['uid'], 'name' => (string)($n['name'] ?? '')];
}
$snmp = new SnmpMonitor(false);
$switchCache = [];
$switchStmt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$update = $pdo->prepare("UPDATE topology_ports SET last_status = :last_status, last_value = :last_value, last_polled_at = :last_polled_at    WHERE id = :id");
foreach ($rows as $row) {
    $id = (int)$row['id'];
    $nodeId = isset($row['node_id']) ? (int)$row['node_id'] : 0;
    $portNum = isset($row['port']) ? (int)$row['port'] : 0;
    $prevStatus = isset($row['last_status']) ? (int)$row['last_status'] : 0;
    $ip = trim((string)($row['snmp_ip'] ?? ''));
    $ro = trim((string)($row['snmp_ro'] ?? ''));
    $oid = trim((string)($row['snmp_oid'] ?? ''));
    $bindSwitchId = isset($row['bind_switch_id']) ? (int)$row['bind_switch_id'] : 0;
    $bindLlid = isset($row['bind_llid']) ? (int)$row['bind_llid'] : 0;
    if ($bindSwitchId > 0 && $bindLlid > 0) {
        if (!isset($switchCache[$bindSwitchId])) {
            $switchStmt->execute([':id' => $bindSwitchId]);
            $switchCache[$bindSwitchId] = $switchStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        $sw = $switchCache[$bindSwitchId];
        if (is_array($sw)) {
            $ip = trim((string)($sw['netip'] ?? ''));
            $ro = trim((string)($sw['snmpro'] ?? ''));
            $oid = '1.3.6.1.2.1.2.2.1.8.' . $bindLlid;
        }
    }
    if ($ip === '' || $ro === '' || $oid === '') {
        continue;
    }
    $raw = $snmp->get($ip, $ro, $oid);
    $value = null;
    if (is_string($raw) && $raw !== '') {
        if (preg_match('/\\bINTEGER\\s*:\\s*([0-9]+)/i', $raw, $m)) {
            $value = (int)$m[1];
        } elseif (preg_match('/\\bup\\((\\d+)\\)/i', $raw, $m)) {
            $value = (int)$m[1];
        } elseif (preg_match('/\\bdown\\((\\d+)\\)/i', $raw, $m)) {
            $value = (int)$m[1];
        }
    }
    $status = null;
    if ($value === 1) {
        $status = 1;
    } elseif ($value !== null) {
        $status = 2;
    }
    $update->execute([':last_status' => $status,':last_value' => ($value !== null ? (string)$value : null),':last_polled_at' => $time,':id' => $id]);
    try {
        if (isset($redis) && $nodeId > 0 && $portNum > 0) {
            $newStatus = ($status === null) ? 0 : (int)$status;
            $oldStatus = ($prevStatus === 1 || $prevStatus === 2) ? (int)$prevStatus : 0;
            if ($oldStatus !== $newStatus && $oldStatus !== 0) {
                $node = $nodeById[$nodeId] ?? null;
                $swUid = is_array($node) ? ($node['uid'] ?? '') : '';
                if ($swUid !== '') {
                    $eventId = (int)$redis->incr('topology:events_id');
                    $payload = json_encode([
                        'id' => $eventId,
                        'ts' => $time,
                        'type' => 'port_status',
                        'sw' => $swUid,
                        'sw_name' => is_array($node) ? ($node['name'] ?? '') : '',
                        'port' => $portNum,
                        'prev' => $oldStatus,
                        'status' => $newStatus,
                    ], JSON_UNESCAPED_UNICODE);
                    if (is_string($payload) && $payload !== '') {
                        $redis->rpush('topology:events', [$payload]);
                        $redis->ltrim('topology:events', -500, -1);
                    }
                }
            }
        }
    } catch (Throwable $e) {

    }
}
?>
