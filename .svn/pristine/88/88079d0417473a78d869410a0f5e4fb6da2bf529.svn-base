<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
function computeCycleStats(array $sessions): array {
    $cycleCounter = ['charge'=>0,'discharge'=>0];
    foreach ($sessions as $s) {
        if ($s['status'] === 'finished') {
            if ($s['type']==='charge') $cycleCounter['charge']++;
            elseif ($s['type']==='discharge') $cycleCounter['discharge']++;
        }
    }
    $chargeCount = (int)$cycleCounter['charge'];
    $dischCount  = (int)$cycleCounter['discharge'];
    $maxCount = max(1,$chargeCount,$dischCount);
    return [
        'chargeCount'=>$chargeCount,
        'dischCount'=>$dischCount,
        'chargePct'=>round($chargeCount/$maxCount*100),
        'dischPct'=>round($dischCount/$maxCount*100),
        'maxCount'=>$maxCount
    ];
}

function mapDeviceIds(array $battery_used): array {
    $ping3Ids = $switchIds = [];
    foreach ($battery_used as $bu) {
        if ($bu['connectd']==='ping3') $ping3Ids[] = (int)$bu['deviceid'];
        if ($bu['connectd']==='olt') $switchIds[] = (int)$bu['deviceid'];
    }
    return [$ping3Ids, $switchIds];
}

function prepareJsSeries(array $voltageSeries): array {
    $jsSeries = [];
    foreach ($voltageSeries as $name => $points) {
        $jsSeries[] = [
            'label'=>$name,
            'data'=>array_map(fn($p)=>['x'=>$p['t'],'y'=>$p['v']], $points)
        ];
    }
    return $jsSeries;
}
function getBattery(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM battery WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getBatteryUsed(PDO $pdo, int $id): array {
    $stmt = $pdo->prepare("SELECT * FROM battery_used WHERE batteryid = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDevices(PDO $pdo, array $ids, string $table, array $fields): array {
    if (!$ids) return [];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT ".implode(',', $fields)." FROM $table WHERE id IN ($in)");
    $stmt->execute($ids);
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $map[(int)$row['id']] = $row;
    }
    return $map;
}

function getVoltageSeries(PDO $pdo, array $ping3Ids, string $since): array {
    if (!$ping3Ids) return [];
    $in = implode(',', array_fill(0, count($ping3Ids), '?'));
    $sql = "SELECT v.deviceid, v.added, v.volt, p.name
              FROM mon_voltage v
              JOIN mon_ping3 p ON p.id = v.deviceid
             WHERE v.mon_types = 'ping3' AND v.deviceid IN ($in) AND v.added >= ?
             ORDER BY v.added ASC";
    $bind = $ping3Ids; $bind[] = $since;
    $q = $pdo->prepare($sql);
    $q->execute($bind);
    $series = [];
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $name = $row['name'] ?: ('Device#'.$row['deviceid']);
        $series[$name][] = ['t'=>$row['added'], 'v'=>(float)$row['volt']];
    }
    return $series;
}

function getBatterySessions(PDO $pdo, array $ping3Ids, int $limit = 500): array {
    if (!$ping3Ids) return [];
    $in = implode(',', array_fill(0, count($ping3Ids), '?'));
    $stmt = $pdo->prepare("SELECT s.*, p.name AS device_name 
                             FROM battery_sessions s 
                             JOIN mon_ping3 p ON p.id = s.deviceid 
                            WHERE s.connectd = 'ping3' AND s.deviceid IN ($in)
                            ORDER BY s.time_start DESC
                            LIMIT $limit");
    $stmt->execute($ping3Ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>