<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
function stats_api_read_params(array $src): array{
    $hours = stats_api_to_int($src['hours'] ?? 24, 24, 1, 168);
    $top = stats_api_to_int($src['top'] ?? 20, 20, 1, 200);
    $limit = stats_api_to_int($src['limit'] ?? 20000, 20000, 200, 200000);
    $bucketSeconds = stats_api_to_int($src['bucket'] ?? 60, 60, 10, 3600);
    $deviceId = filter_var($src['id'] ?? null, FILTER_VALIDATE_INT);
    if ($deviceId === false || (int)$deviceId <= 0) {
        $deviceId = null;
    } else {
        $deviceId = (int)$deviceId;
    }
    $doFilter = strtolower(trim((string)($src['do_filter'] ?? '')));
    $doFilter = preg_replace('/[^a-z_]/', '', $doFilter);
    if ($doFilter === '') {
        $doFilter = null;
    }
    $format = strtolower(trim((string)($src['format'] ?? ($src['view'] ?? 'json'))));
    if (!in_array($format, ['json', 'html'], true)) {
        $format = 'json';
    }
    $slowOnly = stats_api_to_bool($src['slow_only'] ?? false);
    $eventsKey = trim((string)($src['events_key'] ?? ''));
    if ($eventsKey === '') {
        $eventsKey = defined('SERVER_PROFILE_REDIS_KEY_EVENTS') ? (string)SERVER_PROFILE_REDIS_KEY_EVENTS : STATS_API_DEFAULT_EVENTS_KEY;
    }
    $slowKey = trim((string)($src['slow_key'] ?? ''));
    if ($slowKey === '') {
        $slowKey = defined('SERVER_PROFILE_REDIS_KEY_SLOW') ? (string)SERVER_PROFILE_REDIS_KEY_SLOW : STATS_API_DEFAULT_SLOW_KEY;
    }
    $slowMs = (int)(defined('SERVER_PROFILE_SLOW_MS') ? SERVER_PROFILE_SLOW_MS : STATS_API_DEFAULT_SLOW_MS);
    if ($slowMs < 1) {
        $slowMs = STATS_API_DEFAULT_SLOW_MS;
    }
    return [
        'hours' => $hours,
        'top' => $top,
        'limit' => $limit,
        'bucket' => $bucketSeconds,
        'id' => $deviceId,
        'do_filter' => $doFilter,
        'format' => $format,
        'slow_only' => $slowOnly,
        'events_key' => $eventsKey,
        'slow_key' => $slowKey,
        'slow_ms' => $slowMs,
    ];
}
function stats_api_to_int($value, int $default, int $min, int $max): int{
    $v = filter_var($value, FILTER_VALIDATE_INT);
    if ($v === false) {
        return $default;
    }
    return max($min, min($max, (int)$v));
}
function stats_api_to_bool($value): bool{
    if (is_bool($value)) {
        return $value;
    }
    $v = strtolower(trim((string)$value));
    return in_array($v, ['1', 'true', 'yes', 'on'], true);
}
function stats_api_get_redis(){
    if (class_exists('Predis\\Client')) {
        return stats_api_build_predis_client();
    }
    $autoload = stats_api_root_dir() . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }
    if (!class_exists('Predis\\Client')) {
        return null;
    }
    return stats_api_build_predis_client();
}
function stats_api_build_predis_client(){
    try {
        $host = defined('REDIS_IP') ? (string)REDIS_IP : '127.0.0.1';
        $port = defined('REDIS_PORT') ? (int)REDIS_PORT : 6379;
        return new Predis\Client([
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port,
            'persistent' => true,
            'timeout' => 0.7,
            'read_write_timeout' => 0.7,
        ]);
    } catch (Throwable $e) {
        error_log('[STATS API] Redis init failed: ' . $e->getMessage());
        return null;
    }
}
function stats_api_root_dir(): string{
    if (defined('ROOT_DIR')) {
        return (string)ROOT_DIR;
    }
    return dirname(__DIR__, 2);
}
function stats_api_load_rows($redis, string $key, int $limit): array{
    try {
        $rawRows = $redis->lrange($key, -$limit, -1);
    } catch (Throwable $e) {
        error_log('[STATS API] Redis read failed: ' . $e->getMessage());
        return [];
    }
    if (!is_array($rawRows)) {
        return [];
    }
    $rows = [];
    foreach ($rawRows as $raw) {
        if (!is_string($raw) || $raw === '') {
            continue;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            continue;
        }

        $rows[] = $decoded;
    }

    return $rows;
}

function stats_api_build_report(array $rows, array $params): array
{
    $now = time();
    $cutoff = $now - ($params['hours'] * 3600);
    $slowMs = (float)$params['slow_ms'];

    $filtered = [];
    foreach ($rows as $row) {
        $ts = (int)($row['ts'] ?? 0);
        if ($ts <= 0 || $ts < $cutoff) {
            continue;
        }

        $id = isset($row['id']) && $row['id'] !== null ? (int)$row['id'] : null;
        if ($params['id'] !== null && $id !== $params['id']) {
            continue;
        }

        $do = strtolower((string)($row['do'] ?? 'unknown'));
        $do = preg_replace('/[^a-z_]/', '', $do);
        if ($do === '') {
            $do = 'unknown';
        }

        if ($params['do_filter'] !== null && $do !== $params['do_filter']) {
            continue;
        }

        $ms = max(0.0, (float)($row['ms'] ?? 0));
        if ($params['slow_only'] && $ms < $slowMs) {
            continue;
        }

        $filtered[] = [
            'ts' => $ts,
            'status' => (int)($row['status'] ?? 0),
            'ms' => round($ms, 2),
            'do' => $do,
            'id' => $id,
            'ip' => (string)($row['ip'] ?? ''),
        ];
    }

    $summary = stats_api_build_summary($filtered, $params['slow_ms']);
    $top = $params['top'];

    $recent = $filtered;
    usort($recent, static fn(array $a, array $b): int => ($b['ts'] <=> $a['ts']));
    $recent = array_slice($recent, 0, $top);

    $slowest = array_values(array_filter($filtered, static fn(array $r): bool => (float)$r['ms'] >= (float)$params['slow_ms']));
    usort($slowest, static fn(array $a, array $b): int => ($b['ms'] <=> $a['ms']));
    $slowest = array_slice($slowest, 0, $top);

    $byDo = stats_api_group_by_do($filtered, $params['slow_ms'], $top);
    $byId = stats_api_group_by_id($filtered, $params['slow_ms'], $top);
    $series = stats_api_timeseries($filtered, $params['bucket'], $params['slow_ms']);
    $switchNames = stats_api_get_switch_names(array_column($byId, 'id'));
    $byId = stats_api_attach_switch_names($byId, $switchNames);
    $overview = stats_api_build_overview($filtered, $series, $summary, $params);

    return [
        'ok' => true,
        'query' => [
            'hours' => $params['hours'],
            'top' => $params['top'],
            'limit' => $params['limit'],
            'bucket' => $params['bucket'],
            'id' => $params['id'],
            'do_filter' => $params['do_filter'],
            'slow_only' => $params['slow_only'],
        ],
        'window' => [
            'from_ts' => $cutoff,
            'to_ts' => $now,
        ],
        'storage' => [
            'engine' => 'redis',
            'events_key' => $params['events_key'],
            'slow_key' => $params['slow_key'],
            'slow_ms' => $params['slow_ms'],
        ],
        'summary' => $summary,
        'overview' => $overview,
        'recent' => $recent,
        'slowest' => $slowest,
        'by_do' => $byDo,
        'by_id' => $byId,
        'series' => $series,
        'switch_names' => $switchNames,
    ];
}

function stats_api_build_overview(array $rows, array $series, array $summary, array $params): array
{
    $count = (int)($summary['count'] ?? 0);
    $hours = max(1, (int)($params['hours'] ?? 1));
    $mins = max(1, $hours * 60);

    $peakBucket = 0;
    $bucketTotal = 0;
    $bucketCount = 0;
    foreach ($series as $point) {
        $c = (int)($point['count'] ?? 0);
        if ($c > $peakBucket) {
            $peakBucket = $c;
        }
        $bucketTotal += $c;
        $bucketCount++;
    }

    $uniqueIds = [];
    foreach ($rows as $row) {
        $id = isset($row['id']) ? (int)$row['id'] : 0;
        if ($id > 0) {
            $uniqueIds[$id] = true;
        }
    }

    $slowRate = (float)($summary['slow_rate_percent'] ?? 0.0);
    $errorRate = (float)($summary['error_rate_percent'] ?? 0.0);
    $processingScore = max(0.0, 100.0 - ($slowRate * 0.8) - ($errorRate * 1.6));

    return [
        'total_requests' => $count,
        'requests_per_hour' => round($count / $hours, 2),
        'requests_per_minute' => round($count / $mins, 3),
        'active_switches' => count($uniqueIds),
        'peak_bucket_requests' => $peakBucket,
        'avg_bucket_requests' => round($bucketCount > 0 ? ($bucketTotal / $bucketCount) : 0.0, 2),
        'processing_avg_ms' => round((float)($summary['avg_ms'] ?? 0.0), 2),
        'processing_p95_ms' => round((float)($summary['p95_ms'] ?? 0.0), 2),
        'processing_p99_ms' => round((float)($summary['p99_ms'] ?? 0.0), 2),
        'slow_rate_percent' => round($slowRate, 2),
        'error_rate_percent' => round($errorRate, 2),
        'processing_score' => round($processingScore, 2),
    ];
}

function stats_api_attach_switch_names(array $rows, array $switchNames): array
{
    if (empty($rows)) {
        return [];
    }

    foreach ($rows as &$row) {
        $id = (int)($row['id'] ?? 0);
        if ($id > 0) {
            $row['switch_name'] = $switchNames[$id] ?? ('ID #' . $id);
        } else {
            $row['switch_name'] = 'Unknown';
        }
    }
    unset($row);

    return $rows;
}

function stats_api_get_switch_names(array $ids): array
{
    $idList = [];
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $idList[$id] = true;
        }
    }

    $idList = array_keys($idList);
    if (empty($idList)) {
        return [];
    }

    $names = [];

    if (isset($GLOBALS['db']) && is_object($GLOBALS['db']) && method_exists($GLOBALS['db'], 'SimpleWhile')) {
        $in = implode(',', array_map('intval', $idList));
        $sql = 'SELECT id, place, name, netip FROM switch WHERE id IN (' . $in . ')';
        try {
            $rows = $GLOBALS['db']->SimpleWhile($sql);
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $id = isset($row['id']) ? (int)$row['id'] : 0;
                    if ($id <= 0) {
                        continue;
                    }
                    $title = trim((string)($row['place'] ?? ''));
                    if ($title === '') {
                        $title = trim((string)($row['name'] ?? ''));
                    }
                    if ($title === '') {
                        $title = 'Switch #' . $id;
                    }
                    $ip = trim((string)($row['netip'] ?? ''));
                    $names[$id] = $ip !== '' ? ($title . ' (' . $ip . ')') : $title;
                }
            }
        } catch (Throwable $e) {
            error_log('[STATS API] Switch names query failed: ' . $e->getMessage());
        }
    }

    if (empty($names) && isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        try {
            $placeholders = implode(',', array_fill(0, count($idList), '?'));
            $stmt = $GLOBALS['pdo']->prepare('SELECT id, place, name, netip FROM switch WHERE id IN (' . $placeholders . ')');
            foreach ($idList as $idx => $id) {
                $stmt->bindValue($idx + 1, (int)$id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $id = isset($row['id']) ? (int)$row['id'] : 0;
                    if ($id <= 0) {
                        continue;
                    }
                    $title = trim((string)($row['place'] ?? ''));
                    if ($title === '') {
                        $title = trim((string)($row['name'] ?? ''));
                    }
                    if ($title === '') {
                        $title = 'Switch #' . $id;
                    }
                    $ip = trim((string)($row['netip'] ?? ''));
                    $names[$id] = $ip !== '' ? ($title . ' (' . $ip . ')') : $title;
                }
            }
        } catch (Throwable $e) {
            error_log('[STATS API] Switch names PDO query failed: ' . $e->getMessage());
        }
    }

    return $names;
}

function stats_api_build_summary(array $rows, int $slowMs): array
{
    $count = count($rows);
    if ($count === 0) {
        return [
            'count' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'fast_count' => 0,
            'slow_count' => 0,
            'success_rate_percent' => 0.0,
            'error_rate_percent' => 0.0,
            'fast_rate_percent' => 0.0,
            'slow_rate_percent' => 0.0,
            'avg_ms' => 0.0,
            'p95_ms' => 0.0,
            'p99_ms' => 0.0,
            'max_ms' => 0.0,
        ];
    }

    $errorCount = 0;
    $successCount = 0;
    $fastCount = 0;
    $slowCount = 0;
    $total = 0.0;
    $max = 0.0;
    $samples = [];

    foreach ($rows as $r) {
        $ms = (float)$r['ms'];
        $status = (int)$r['status'];
        $total += $ms;
        if ($ms > $max) {
            $max = $ms;
        }
        $samples[] = $ms;

        if ($status >= 400) {
            $errorCount++;
        } else if ($status > 0) {
            $successCount++;
        }
        if ($ms >= $slowMs) {
            $slowCount++;
        } else {
            $fastCount++;
        }
    }

    sort($samples, SORT_NUMERIC);

    return [
        'count' => $count,
        'success_count' => $successCount,
        'error_count' => $errorCount,
        'fast_count' => $fastCount,
        'slow_count' => $slowCount,
        'success_rate_percent' => round($successCount * 100.0 / $count, 2),
        'error_rate_percent' => round($errorCount * 100.0 / $count, 2),
        'fast_rate_percent' => round($fastCount * 100.0 / $count, 2),
        'slow_rate_percent' => round($slowCount * 100.0 / $count, 2),
        'avg_ms' => round($total / $count, 2),
        'p95_ms' => stats_api_percentile($samples, 95.0),
        'p99_ms' => stats_api_percentile($samples, 99.0),
        'max_ms' => round($max, 2),
    ];
}

function stats_api_group_by_do(array $rows, int $slowMs, int $top): array
{
    $map = [];

    foreach ($rows as $r) {
        $do = $r['do'];
        if (!isset($map[$do])) {
            $map[$do] = ['do' => $do, 'count' => 0, 'sum_ms' => 0.0, 'max_ms' => 0.0, 'slow_count' => 0, 'error_count' => 0];
        }

        $ms = (float)$r['ms'];
        $status = (int)$r['status'];

        $map[$do]['count']++;
        $map[$do]['sum_ms'] += $ms;
        if ($ms > $map[$do]['max_ms']) {
            $map[$do]['max_ms'] = $ms;
        }
        if ($ms >= $slowMs) {
            $map[$do]['slow_count']++;
        }
        if ($status >= 400) {
            $map[$do]['error_count']++;
        }
    }

    $list = [];
    foreach ($map as $do => $v) {
        $count = max(1, (int)$v['count']);
        $list[] = [
            'do' => $do,
            'count' => (int)$v['count'],
            'avg_ms' => round(((float)$v['sum_ms']) / $count, 2),
            'max_ms' => round((float)$v['max_ms'], 2),
            'slow_count' => (int)$v['slow_count'],
            'error_count' => (int)$v['error_count'],
            'slow_rate_percent' => round(((float)$v['slow_count'] * 100.0) / $count, 2),
        ];
    }

    usort($list, static fn(array $a, array $b): int => ($b['avg_ms'] <=> $a['avg_ms']) ?: ($b['count'] <=> $a['count']));
    return array_slice($list, 0, $top);
}

function stats_api_group_by_id(array $rows, int $slowMs, int $top): array
{
    $map = [];

    foreach ($rows as $r) {
        if (!isset($r['id']) || $r['id'] === null) {
            continue;
        }

        $id = (int)$r['id'];
        if ($id <= 0) {
            continue;
        }

        if (!isset($map[$id])) {
            $map[$id] = ['id' => $id, 'count' => 0, 'sum_ms' => 0.0, 'max_ms' => 0.0, 'slow_count' => 0, 'error_count' => 0];
        }

        $ms = (float)$r['ms'];
        $status = (int)$r['status'];

        $map[$id]['count']++;
        $map[$id]['sum_ms'] += $ms;
        if ($ms > $map[$id]['max_ms']) {
            $map[$id]['max_ms'] = $ms;
        }
        if ($ms >= $slowMs) {
            $map[$id]['slow_count']++;
        }
        if ($status >= 400) {
            $map[$id]['error_count']++;
        }
    }

    $list = [];
    foreach ($map as $id => $v) {
        $count = max(1, (int)$v['count']);
        $list[] = [
            'id' => (int)$id,
            'count' => (int)$v['count'],
            'avg_ms' => round(((float)$v['sum_ms']) / $count, 2),
            'max_ms' => round((float)$v['max_ms'], 2),
            'slow_count' => (int)$v['slow_count'],
            'error_count' => (int)$v['error_count'],
            'slow_rate_percent' => round(((float)$v['slow_count'] * 100.0) / $count, 2),
        ];
    }

    usort($list, static fn(array $a, array $b): int => ($b['avg_ms'] <=> $a['avg_ms']) ?: ($b['count'] <=> $a['count']));
    return array_slice($list, 0, $top);
}

function stats_api_timeseries(array $rows, int $bucket, int $slowMs): array
{
    $map = [];

    foreach ($rows as $r) {
        $ts = (int)$r['ts'];
        $bin = (int)(floor($ts / $bucket) * $bucket);

        if (!isset($map[$bin])) {
            $map[$bin] = [
                'ts' => $bin,
                'count' => 0,
                'error_count' => 0,
                'slow_count' => 0,
                'sum_ms' => 0.0,
                'max_ms' => 0.0,
            ];
        }

        $ms = (float)$r['ms'];
        $status = (int)$r['status'];

        $map[$bin]['count']++;
        $map[$bin]['sum_ms'] += $ms;
        if ($ms > $map[$bin]['max_ms']) {
            $map[$bin]['max_ms'] = $ms;
        }
        if ($status >= 400) {
            $map[$bin]['error_count']++;
        }
        if ($ms >= $slowMs) {
            $map[$bin]['slow_count']++;
        }
    }

    ksort($map, SORT_NUMERIC);

    $series = [];
    foreach ($map as $bin => $v) {
        $count = max(1, (int)$v['count']);
        $series[] = [
            'ts' => (int)$bin,
            'count' => (int)$v['count'],
            'error_count' => (int)$v['error_count'],
            'slow_count' => (int)$v['slow_count'],
            'avg_ms' => round(((float)$v['sum_ms']) / $count, 2),
            'max_ms' => round((float)$v['max_ms'], 2),
        ];
    }

    return $series;
}

function stats_api_percentile(array $sortedValues, float $percent): float
{
    $count = count($sortedValues);
    if ($count === 0) {
        return 0.0;
    }
    if ($count === 1) {
        return round((float)$sortedValues[0], 2);
    }

    $percent = max(0.0, min(100.0, $percent));
    $rank = ($percent / 100.0) * ($count - 1);
    $low = (int)floor($rank);
    $high = (int)ceil($rank);

    $lowValue = (float)$sortedValues[$low];
    $highValue = (float)$sortedValues[$high];
    if ($low === $high) {
        return round($lowValue, 2);
    }

    $weight = $rank - $low;
    return round($lowValue + (($highValue - $lowValue) * $weight), 2);
}

function stats_api_respond_error(string $error, int $statusCode, array $extra = []): void
{
    http_response_code($statusCode);
    $body = ['ok' => false, 'error' => $error];
    if (!empty($extra)) {
        $body['meta'] = $extra;
    }

    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function stats_api_render_html(array $report, array $params): string
{
    $summary = $report['summary'] ?? [];
    $series = $report['series'] ?? [];

    $countValues = [];
    $avgValues = [];
    foreach ($series as $point) {
        $countValues[] = (float)($point['count'] ?? 0);
        $avgValues[] = (float)($point['avg_ms'] ?? 0);
    }

    $countSvg = stats_api_svg_polyline($countValues, 920, 120, '#118ab2');
    $avgSvg = stats_api_svg_polyline($avgValues, 920, 120, '#ef476f');

    $title = 'API Stats Dashboard';
    if (!empty($params['id'])) {
        $title .= ' (Device #' . (int)$params['id'] . ')';
    }

    $recentRows = stats_api_html_table_rows($report['recent'] ?? []);
    $slowRows = stats_api_html_table_rows($report['slowest'] ?? []);
    $byDoRows = stats_api_html_group_rows($report['by_do'] ?? [], 'do');
    $byIdRows = stats_api_html_group_rows($report['by_id'] ?? [], 'id');

    return '<!doctype html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>'
        . '<style>'
        . 'body{font-family:Segoe UI,Arial,sans-serif;background:#f7f9fc;color:#1f2937;margin:0;padding:16px;}'
        . '.grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:16px;}'
        . '.card{background:#fff;border:1px solid #dde5ef;border-radius:10px;padding:10px;}'
        . '.k{font-size:12px;color:#6b7280;} .v{font-size:22px;font-weight:700;}'
        . '.chart{background:#fff;border:1px solid #dde5ef;border-radius:10px;padding:10px;margin-bottom:12px;}'
        . 'table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #dde5ef;border-radius:10px;overflow:hidden;margin-bottom:12px;}'
        . 'th,td{padding:8px;border-bottom:1px solid #eef2f7;font-size:12px;text-align:left;}'
        . 'th{background:#f3f6fb;font-size:11px;text-transform:uppercase;color:#64748b;}'
        . 'h2{font-size:16px;margin:14px 0 8px;}'
        . '</style></head><body>'
        . '<h1 style="margin:0 0 12px;font-size:22px;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
        . '<div class="grid">'
        . '<div class="card"><div class="k">Requests</div><div class="v">' . (int)($summary['count'] ?? 0) . '</div></div>'
        . '<div class="card"><div class="k">Errors</div><div class="v">' . (int)($summary['error_count'] ?? 0) . '</div></div>'
        . '<div class="card"><div class="k">Slow</div><div class="v">' . (int)($summary['slow_count'] ?? 0) . '</div></div>'
        . '<div class="card"><div class="k">Avg ms</div><div class="v">' . (float)($summary['avg_ms'] ?? 0.0) . '</div></div>'
        . '<div class="card"><div class="k">P95 ms</div><div class="v">' . (float)($summary['p95_ms'] ?? 0.0) . '</div></div>'
        . '<div class="card"><div class="k">P99 ms</div><div class="v">' . (float)($summary['p99_ms'] ?? 0.0) . '</div></div>'
        . '</div>'
        . '<div class="chart"><div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Requests per bucket</div>' . $countSvg . '</div>'
        . '<div class="chart"><div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Average response time (ms)</div>' . $avgSvg . '</div>'
        . '<h2>Recent Requests</h2><table><thead><tr><th>ts</th><th>do</th><th>id</th><th>status</th><th>ms</th><th>ip</th></tr></thead><tbody>' . $recentRows . '</tbody></table>'
        . '<h2>Slowest Requests</h2><table><thead><tr><th>ts</th><th>do</th><th>id</th><th>status</th><th>ms</th><th>ip</th></tr></thead><tbody>' . $slowRows . '</tbody></table>'
        . '<h2>By Action</h2><table><thead><tr><th>do</th><th>count</th><th>avg_ms</th><th>max_ms</th><th>slow_count</th><th>error_count</th><th>slow_rate_%</th></tr></thead><tbody>' . $byDoRows . '</tbody></table>'
        . '<h2>By Device ID</h2><table><thead><tr><th>id</th><th>count</th><th>avg_ms</th><th>max_ms</th><th>slow_count</th><th>error_count</th><th>slow_rate_%</th></tr></thead><tbody>' . $byIdRows . '</tbody></table>'
        . '</body></html>';
}

function stats_api_svg_polyline(array $values, int $width, int $height, string $color): string
{
    if (empty($values)) {
        return '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '"><text x="8" y="20" fill="#94a3b8" font-size="12">No data</text></svg>';
    }

    $max = max($values);
    if ($max <= 0) {
        $max = 1;
    }

    $count = count($values);
    $stepX = ($count > 1) ? ($width - 20) / ($count - 1) : 0;
    $points = [];

    foreach ($values as $i => $v) {
        $x = 10 + ($i * $stepX);
        $y = ($height - 10) - ((float)$v / $max) * ($height - 20);
        $points[] = round($x, 2) . ',' . round($y, 2);
    }

    return '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">'
        . '<rect x="0" y="0" width="' . $width . '" height="' . $height . '" fill="#ffffff" />'
        . '<polyline fill="none" stroke="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '" stroke-width="2" points="' . implode(' ', $points) . '" />'
        . '</svg>';
}

function stats_api_html_table_rows(array $rows): string
{
    if (empty($rows)) {
        return '<tr><td colspan="6">No data</td></tr>';
    }

    $html = '';
    foreach ($rows as $r) {
        $html .= '<tr>'
            . '<td>' . (int)($r['ts'] ?? 0) . '</td>'
            . '<td>' . htmlspecialchars((string)($r['do'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . ($r['id'] !== null ? (int)$r['id'] : '-') . '</td>'
            . '<td>' . (int)($r['status'] ?? 0) . '</td>'
            . '<td>' . (float)($r['ms'] ?? 0.0) . '</td>'
            . '<td>' . htmlspecialchars((string)($r['ip'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
            . '</tr>';
    }

    return $html;
}

function stats_api_html_group_rows(array $rows, string $key): string
{
    if (empty($rows)) {
        return '<tr><td colspan="7">No data</td></tr>';
    }

    $html = '';
    foreach ($rows as $r) {
        if ($key === 'id') {
            $id = (int)($r['id'] ?? 0);
            $switchName = trim((string)($r['switch_name'] ?? ''));
            $main = $switchName !== '' ? ($switchName . ' [#' . $id . ']') : (string)$id;
        } else {
            $main = (string)($r['do'] ?? '');
        }
        $html .= '<tr>'
            . '<td>' . htmlspecialchars($main, ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . (int)($r['count'] ?? 0) . '</td>'
            . '<td>' . (float)($r['avg_ms'] ?? 0.0) . '</td>'
            . '<td>' . (float)($r['max_ms'] ?? 0.0) . '</td>'
            . '<td>' . (int)($r['slow_count'] ?? 0) . '</td>'
            . '<td>' . (int)($r['error_count'] ?? 0) . '</td>'
            . '<td>' . (float)($r['slow_rate_percent'] ?? 0.0) . '</td>'
            . '</tr>';
    }

    return $html;
}
?>