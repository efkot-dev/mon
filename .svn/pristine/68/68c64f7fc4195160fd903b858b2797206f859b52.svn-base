<?php
define('AJAX', true);
define('ROOT_DIR', substr(__DIR__, 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';

header('Content-Type: application/json; charset=utf-8');

$uid = isset($USER['id']) ? (int)$USER['id'] : 0;
$act = Clean::text($_GET['act'] ?? 'list_switches');

function porterror_range_sql(string $range): array {
    switch ($range) {
        case 'yesterday':
            return ['from' => "DATE_SUB(CURDATE(), INTERVAL 1 DAY)", 'to' => "CURDATE()", 'step' => 600];
        case '7d':
            return ['from' => "DATE_SUB(NOW(), INTERVAL 7 DAY)", 'to' => "NOW()", 'step' => 3600];
        case '30d':
            return ['from' => "DATE_SUB(NOW(), INTERVAL 30 DAY)", 'to' => "NOW()", 'step' => 86400];
        case 'today':
        default:
            return ['from' => "CURDATE()", 'to' => "DATE_ADD(CURDATE(), INTERVAL 1 DAY)", 'step' => 300];
    }
}

if ($act === 'list_switches') {
    $sql = "
      SELECT
        s.id,
        s.place,
        s.model,
        s.inf,
        SUM(CASE WHEN sp.id IS NOT NULL THEN 1 ELSE 0 END) AS ports_monitored,
        SUM(CASE WHEN sp.id IS NOT NULL AND sp.error_today > 0 THEN 1 ELSE 0 END) AS ports_with_err_today,
        COALESCE(SUM(sp.error_today),0) AS sum_err_today
      FROM `switch` s
      JOIN switch_port sp
        ON sp.deviceid = s.id
       AND sp.monitor  = 'yes'
      LEFT JOIN checkaccess a
        ON a.types = CONCAT('dev', s.id)
       AND a.uid   = :uid
      WHERE s.monitor = 'yes'
        AND a.uid IS NOT NULL
      GROUP BY s.id
      ORDER BY ports_with_err_today DESC, s.place ASC
      LIMIT 1000
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $uid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'switches' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($act === 'ports_by_switch') {
    $deviceid = Clean::int($_GET['deviceid'] ?? 0);
    if ($deviceid <= 0) {
        echo json_encode(['ok' => false, 'error' => 'bad device']); exit;
    }
    $sql = "
      SELECT
        sp.id, sp.deviceid, sp.llid, sp.nameport, sp.descrport,
        sp.operstatus, sp.error_count, sp.error_today
      FROM switch_port sp
      LEFT JOIN checkaccess a
        ON a.types = CONCAT('dev', sp.deviceid)
       AND a.uid = :uid
      WHERE sp.deviceid = :d
        AND sp.monitor  = 'yes'
        AND a.uid IS NOT NULL
      ORDER BY sp.nameport ASC
      LIMIT 2000
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':d' => $deviceid, ':uid' => $uid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'ports' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($act === 'series') {
    $deviceid = Clean::int($_GET['deviceid'] ?? 0);
    $llid = Clean::int($_GET['llid'] ?? 0);
    $range = Clean::text($_GET['range'] ?? 'today');
    if ($deviceid <= 0 || $llid <= 0) {
        echo json_encode(['ok' => false, 'error' => 'bad params']); exit;
    }

    $rangeCfg = porterror_range_sql($range);
    $fromExpr = $rangeCfg['from'];
    $toExpr = $rangeCfg['to'];
    $step = (int)$rangeCfg['step'];

    $sql = "
      SELECT
        (FLOOR(UNIX_TIMESTAMP(added) / :step_div) * :step_mul) AS ts_s,
        SUM(newin)  AS in_sum,
        SUM(newout) AS out_sum
      FROM switch_port_err
      WHERE deviceid = :d
        AND llid     = :l
        AND added   >= $fromExpr
        AND added   <  $toExpr
      GROUP BY ts_s
      ORDER BY ts_s ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':step_div' => $step,
        ':step_mul' => $step,
        ':d' => $deviceid,
        ':l' => $llid
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $points = [];
    foreach ($rows as $r) {
        $points[] = [
            'ts' => ((int)$r['ts_s']) * 1000,
            'in_sum' => (int)$r['in_sum'],
            'out_sum' => (int)$r['out_sum']
        ];
    }
    echo json_encode(['ok' => true, 'range' => $range, 'points' => $points], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($act === 'series_by_switch') {
    $deviceid = Clean::int($_GET['deviceid'] ?? 0);
    $range = Clean::text($_GET['range'] ?? 'today');
    if ($deviceid <= 0) {
        echo json_encode(['ok' => false, 'error' => 'bad params']); exit;
    }

    $chk = $pdo->prepare("SELECT 1 FROM checkaccess WHERE uid = :uid AND types = :types LIMIT 1");
    $chk->execute([':uid' => $uid, ':types' => 'dev' . $deviceid]);
    if (!$chk->fetchColumn()) {
        echo json_encode(['ok' => false, 'error' => 'forbidden']); exit;
    }

    $rangeCfg = porterror_range_sql($range);
    $fromExpr = $rangeCfg['from'];
    $toExpr = $rangeCfg['to'];
    $step = (int)$rangeCfg['step'];

    $sql = "
      SELECT
        e.llid,
        (FLOOR(UNIX_TIMESTAMP(e.added) / :step_div) * :step_mul) AS ts_s,
        SUM(e.newin)  AS in_sum,
        SUM(e.newout) AS out_sum
      FROM switch_port_err e
      INNER JOIN switch_port sp
        ON sp.deviceid = e.deviceid
       AND sp.llid = e.llid
       AND sp.monitor = 'yes'
      WHERE e.deviceid = :d
        AND e.added >= $fromExpr
        AND e.added <  $toExpr
      GROUP BY e.llid, ts_s
      ORDER BY e.llid ASC, ts_s ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':step_div' => $step,
        ':step_mul' => $step,
        ':d' => $deviceid
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $series = [];
    foreach ($rows as $r) {
        $llid = (int)$r['llid'];
        if (!isset($series[$llid])) {
            $series[$llid] = [];
        }
        $series[$llid][] = [
            'ts' => ((int)$r['ts_s']) * 1000,
            'in_sum' => (int)$r['in_sum'],
            'out_sum' => (int)$r['out_sum']
        ];
    }
    echo json_encode(['ok' => true, 'range' => $range, 'series' => $series], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'unknown act']);
?>
