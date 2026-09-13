<?php
if (!defined('PMONSERVER') && !defined('PONMONITOR')) {
    die('Hacking attempt!');
}

if (!defined('SERVER_SNMP_TIMEOUT')) {
    define('SERVER_SNMP_TIMEOUT', 800000);
}
if (!defined('SERVER_SNMP_RETRIES')) {
    define('SERVER_SNMP_RETRIES', 1);
}
if (!defined('SERVER_SNMP_RECHECK_ATTEMPTS')) {
    define('SERVER_SNMP_RECHECK_ATTEMPTS', 1);
}
if (!defined('SERVER_SNMP_RECHECK_SLEEP_US')) {
    define('SERVER_SNMP_RECHECK_SLEEP_US', 0);
}
if (!defined('SERVER_SWITCH_CACHE_TTL')) {
    define('SERVER_SWITCH_CACHE_TTL', 3600);
}
if (!defined('SERVER_OID_CACHE_TTL')) {
    define('SERVER_OID_CACHE_TTL', 7200);
}
if (!defined('SERVER_ONU_RESULT_CACHE_TTL')) {
    define('SERVER_ONU_RESULT_CACHE_TTL', 3);
}
if (!defined('SERVER_OID_RESULT_CACHE_TTL')) {
    define('SERVER_OID_RESULT_CACHE_TTL', 2);
}
if (!defined('SERVER_DEVICE_RESULT_CACHE_TTL')) {
    define('SERVER_DEVICE_RESULT_CACHE_TTL', 3);
}
if (!defined('SERVER_PORT_RESULT_CACHE_TTL')) {
    define('SERVER_PORT_RESULT_CACHE_TTL', 2);
}
if (!defined('SERVER_SNMPGET_RESULT_CACHE_TTL')) {
    define('SERVER_SNMPGET_RESULT_CACHE_TTL', 1);
}
if (!defined('SERVER_SNMP_BULK_ENABLED')) {
    define('SERVER_SNMP_BULK_ENABLED', false);
}
if (!defined('SERVER_PROFILE_ENABLED')) {
    define('SERVER_PROFILE_ENABLED', true);
}
if (!defined('SERVER_PROFILE_SLOW_MS')) {
    define('SERVER_PROFILE_SLOW_MS', 300);
}
if (!defined('SERVER_PROFILE_REDIS_KEY_EVENTS')) {
    define('SERVER_PROFILE_REDIS_KEY_EVENTS', 'pmon:api:profile:events');
}
if (!defined('SERVER_PROFILE_REDIS_KEY_SLOW')) {
    define('SERVER_PROFILE_REDIS_KEY_SLOW', 'pmon:api:profile:slow');
}
if (!defined('SERVER_PROFILE_REDIS_TTL')) {
    define('SERVER_PROFILE_REDIS_TTL', 172800);
}
if (!defined('SERVER_PROFILE_REDIS_MAX_ITEMS')) {
    define('SERVER_PROFILE_REDIS_MAX_ITEMS', 120000);
}
if (!defined('SERVER_PROFILE_REDIS_MAX_SLOW_ITEMS')) {
    define('SERVER_PROFILE_REDIS_MAX_SLOW_ITEMS', 60000);
}
if (!defined('SERVER_PROFILE_STATS_DEFAULT_HOURS')) {
    define('SERVER_PROFILE_STATS_DEFAULT_HOURS', 24);
}
if (!defined('SERVER_PROFILE_STATS_MAX_HOURS')) {
    define('SERVER_PROFILE_STATS_MAX_HOURS', 168);
}
if (!defined('SERVER_PROFILE_STATS_DEFAULT_TOP')) {
    define('SERVER_PROFILE_STATS_DEFAULT_TOP', 20);
}
if (!defined('SERVER_PROFILE_STATS_MAX_TOP')) {
    define('SERVER_PROFILE_STATS_MAX_TOP', 100);
}
if (!defined('SERVER_PROFILE_STATS_DEFAULT_LIMIT')) {
    define('SERVER_PROFILE_STATS_DEFAULT_LIMIT', 20000);
}
if (!defined('SERVER_PROFILE_STATS_MAX_LIMIT')) {
    define('SERVER_PROFILE_STATS_MAX_LIMIT', 100000);
}
if (!defined('SERVER_CACHE_STAMPEDE_LOCK_TTL')) {
    define('SERVER_CACHE_STAMPEDE_LOCK_TTL', 2);
}
if (!defined('SERVER_CACHE_STAMPEDE_WAIT_US')) {
    define('SERVER_CACHE_STAMPEDE_WAIT_US', 20000);
}
if (!defined('SERVER_CACHE_STAMPEDE_WAIT_ATTEMPTS')) {
    define('SERVER_CACHE_STAMPEDE_WAIT_ATTEMPTS', 4);
}

function cleanInput($input)
{
    return preg_replace('/[^a-zA-Z0-9@#$!%_\-]/', '', (string)$input);
}

function api_auth_check(string $secret, bool $logSuccess = false): void
{
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    $apiKey = trim((string)($_POST['api_key'] ?? getApiKeyFromHeaders() ?? ''));
    $clientSignature = trim((string)($_POST['sign'] ?? ''));
    $ts = (int)($_POST['ts'] ?? 0);

    $postData = $_POST;
    unset($postData['sign']);
    ksort($postData);

    $signString = http_build_query($postData);
    $serverSignature = hash_hmac('sha256', $signString, $secret);

    if (!hash_equals('internal_api', $apiKey)) {
        error_log("[API AUTH FAIL] forbidden | IP={$clientIp}");
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['error' => 'forbidden']));
    }

    if ($clientSignature === '' || !hash_equals($serverSignature, $clientSignature)) {
        error_log("[API AUTH FAIL] invalid_signature | IP={$clientIp} | URI={$requestUri}");
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['error' => 'invalid_signature']));
    }

    if (!$ts || abs(time() - $ts) > 60) {
        error_log("[API AUTH FAIL] expired | IP={$clientIp} | TS={$ts}");
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['error' => 'expired']));
    }

    if ($logSuccess) {
        error_log("[API AUTH OK] IP={$clientIp} | ACTION=" . ($_POST['do'] ?? 'unknown'));
    }
}

function getApiKeyFromHeaders(): ?string
{
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach (['X-API-KEY', 'x-api-key', 'X-Api-Key'] as $keyName) {
            if (isset($headers[$keyName])) {
                return (string)$headers[$keyName];
            }
        }
    }

    foreach (['HTTP_X_API_KEY', 'HTTP_X_APIKEY', 'HTTP_API_KEY'] as $serverKey) {
        if (isset($_SERVER[$serverKey])) {
            return (string)$_SERVER[$serverKey];
        }
    }

    return null;
}

function server_profiler_bind_cache($cache): void
{
    if (!SERVER_PROFILE_ENABLED) {
        return;
    }
    $GLOBALS['SERVER_PROFILE_CACHE'] = $cache;
}

function server_profiler_bootstrap(array $requestData = []): void
{
    if (!SERVER_PROFILE_ENABLED) {
        return;
    }

    $do = strtolower((string)($requestData['do'] ?? $_POST['do'] ?? ''));
    $do = preg_replace('/[^a-z_]/', '', $do);
    if ($do === '') {
        $do = 'unknown';
    }

    $requestId = server_profile_extract_request_id($requestData);
    $wantsProfile = ((string)($requestData['profile'] ?? $_POST['profile'] ?? '') === '1');

    $GLOBALS['SERVER_PROFILER'] = [
        'started_at' => microtime(true),
        'done' => false,
        'request' => [
            'do' => $do,
            'id' => $requestId,
            'ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
            'method' => (string)($_SERVER['REQUEST_METHOD'] ?? ''),
            'uri' => (string)($_SERVER['REQUEST_URI'] ?? ''),
        ],
        'wants_profile' => $wantsProfile,
    ];
}

function server_profiler_finalize(int $statusCode, $payload)
{
    if (!SERVER_PROFILE_ENABLED) {
        return $payload;
    }
    if (
        !isset($GLOBALS['SERVER_PROFILER'])
        || !is_array($GLOBALS['SERVER_PROFILER'])
        || !empty($GLOBALS['SERVER_PROFILER']['done'])
    ) {
        return $payload;
    }

    $totalMs = (microtime(true) - (float)$GLOBALS['SERVER_PROFILER']['started_at']) * 1000.0;
    $GLOBALS['SERVER_PROFILER']['done'] = true;

    $entry = [
        'ts' => time(),
        'status' => (int)$statusCode,
        'ms' => round($totalMs, 2),
        'do' => (string)($GLOBALS['SERVER_PROFILER']['request']['do'] ?? 'unknown'),
        'id' => $GLOBALS['SERVER_PROFILER']['request']['id'] ?? null,
        'ip' => (string)($GLOBALS['SERVER_PROFILER']['request']['ip'] ?? ''),
    ];

    server_profiler_log_redis($entry);

    if (!empty($GLOBALS['SERVER_PROFILER']['wants_profile']) && is_array($payload)) {
        $payload['_profile'] = [
            'status' => (int)$statusCode,
            'ms' => round($totalMs, 2),
            'slow' => ($totalMs >= SERVER_PROFILE_SLOW_MS),
        ];
    }

    return $payload;
}

function server_profiler_log_redis(array $entry): void
{
    $redis = server_profile_get_redis_client();
    if ($redis === null) {
        return;
    }

    $json = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return;
    }

    $isSlow = ((float)$entry['ms'] >= (float)SERVER_PROFILE_SLOW_MS);

    try {
        $eventsKey = (string)SERVER_PROFILE_REDIS_KEY_EVENTS;
        $slowKey = (string)SERVER_PROFILE_REDIS_KEY_SLOW;
        $ttl = max(60, (int)SERVER_PROFILE_REDIS_TTL);
        $maxEvents = max(1000, (int)SERVER_PROFILE_REDIS_MAX_ITEMS);
        $maxSlow = max(1000, (int)SERVER_PROFILE_REDIS_MAX_SLOW_ITEMS);

        $redis->pipeline(static function ($pipe) use ($eventsKey, $slowKey, $json, $ttl, $maxEvents, $maxSlow, $isSlow): void {
            $pipe->rpush($eventsKey, $json);
            $pipe->ltrim($eventsKey, -$maxEvents, -1);
            $pipe->expire($eventsKey, $ttl);

            if ($isSlow) {
                $pipe->rpush($slowKey, $json);
                $pipe->ltrim($slowKey, -$maxSlow, -1);
                $pipe->expire($slowKey, $ttl);
            }
        });
    } catch (Throwable $e) {
        error_log('[PROFILE] Redis write failed: ' . $e->getMessage());
    }
}

function server_profile_get_redis_client()
{
    static $client = null;
    static $initialized = false;

    if ($initialized) {
        return $client;
    }
    $initialized = true;

    $cache = $GLOBALS['SERVER_PROFILE_CACHE'] ?? null;
    if (is_object($cache) && method_exists($cache, 'redisClient')) {
        try {
            $client = $cache->redisClient();
            return $client;
        } catch (Throwable $e) {
            error_log('[PROFILE] Redis client fetch failed: ' . $e->getMessage());
        }
    }

    return null;
}

function server_profile_extract_request_id(array $requestData): ?int
{
    $raw = $requestData['id'] ?? ($_POST['id'] ?? null);
    if ($raw === null || $raw === '') {
        return null;
    }

    $id = filter_var($raw, FILTER_VALIDATE_INT);
    if ($id === false || (int)$id <= 0) {
        return null;
    }

    return (int)$id;
}

function server_profile_parse_limits(array $requestData): array
{
    $hours = isset($requestData['hours']) ? (int)$requestData['hours'] : SERVER_PROFILE_STATS_DEFAULT_HOURS;
    $hours = max(1, min(SERVER_PROFILE_STATS_MAX_HOURS, $hours));

    $top = isset($requestData['top']) ? (int)$requestData['top'] : SERVER_PROFILE_STATS_DEFAULT_TOP;
    $top = max(1, min(SERVER_PROFILE_STATS_MAX_TOP, $top));

    $limit = isset($requestData['limit']) ? (int)$requestData['limit'] : SERVER_PROFILE_STATS_DEFAULT_LIMIT;
    $limit = max(200, min(SERVER_PROFILE_STATS_MAX_LIMIT, $limit));

    return [$hours, $top, $limit];
}

function server_profile_load_events(int $limit, bool $slowOnly = false): array
{
    $redis = server_profile_get_redis_client();
    if ($redis === null) {
        return [];
    }

    $key = $slowOnly ? (string)SERVER_PROFILE_REDIS_KEY_SLOW : (string)SERVER_PROFILE_REDIS_KEY_EVENTS;
    try {
        $rawRows = $redis->lrange($key, -$limit, -1);
        if (!is_array($rawRows)) {
            return [];
        }

        $rows = [];
        foreach ($rawRows as $raw) {
            if (!is_string($raw) || $raw === '') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $rows[] = $decoded;
            }
        }

        return $rows;
    } catch (Throwable $e) {
        error_log('[PROFILE] Redis read failed: ' . $e->getMessage());
        return [];
    }
}

function handleProfileStatsRequest(array $requestData): array
{
    [$hours, $top, $limit] = server_profile_parse_limits($requestData);
    $cutoff = time() - ($hours * 3600);

    $rows = server_profile_load_events($limit, false);
    $stats = server_profile_aggregate_rows($rows, $cutoff, null, $top);

    return [
        'window' => [
            'hours' => $hours,
            'from_ts' => $cutoff,
            'to_ts' => time(),
        ],
        'storage' => [
            'engine' => 'redis',
            'events_key' => (string)SERVER_PROFILE_REDIS_KEY_EVENTS,
            'slow_key' => (string)SERVER_PROFILE_REDIS_KEY_SLOW,
            'slow_ms' => (int)SERVER_PROFILE_SLOW_MS,
        ],
        'stats' => $stats,
    ];
}

function handleProfileByIdRequest(array $requestData): array
{
    [$hours, $top, $limit] = server_profile_parse_limits($requestData);
    $targetId = getIntValue($requestData, 'id');
    if ($targetId === null || $targetId <= 0) {
        return ['error' => 'invalid_id'];
    }

    $cutoff = time() - ($hours * 3600);
    $rows = server_profile_load_events($limit, false);
    $stats = server_profile_aggregate_rows($rows, $cutoff, $targetId, $top);

    return [
        'id' => $targetId,
        'window' => [
            'hours' => $hours,
            'from_ts' => $cutoff,
            'to_ts' => time(),
        ],
        'storage' => [
            'engine' => 'redis',
            'events_key' => (string)SERVER_PROFILE_REDIS_KEY_EVENTS,
            'slow_ms' => (int)SERVER_PROFILE_SLOW_MS,
        ],
        'stats' => $stats,
    ];
}

function server_profile_aggregate_rows(array $rows, int $cutoffTs, ?int $targetId, int $top): array
{
    $count = 0;
    $errorCount = 0;
    $slowCount = 0;
    $totalMs = 0.0;
    $samples = [];
    $slowRows = [];
    $recentRows = [];
    $doMap = [];

    foreach ($rows as $row) {
        $ts = (int)($row['ts'] ?? 0);
        if ($ts <= 0 || $ts < $cutoffTs) {
            continue;
        }

        $id = isset($row['id']) && $row['id'] !== null ? (int)$row['id'] : null;
        if ($targetId !== null && $id !== $targetId) {
            continue;
        }

        $ms = max(0.0, (float)($row['ms'] ?? 0.0));
        $status = (int)($row['status'] ?? 0);
        $do = strtolower((string)($row['do'] ?? 'unknown'));
        $do = preg_replace('/[^a-z_]/', '', $do);
        if ($do === '') {
            $do = 'unknown';
        }

        $count++;
        $totalMs += $ms;
        $samples[] = $ms;
        $recentRows[] = [
            'ts' => $ts,
            'do' => $do,
            'id' => $id,
            'ip' => (string)($row['ip'] ?? ''),
            'status' => $status,
            'ms' => round($ms, 2),
        ];

        if ($status >= 400) {
            $errorCount++;
        }
        if ($ms >= SERVER_PROFILE_SLOW_MS) {
            $slowCount++;
            $slowRows[] = [
                'ts' => $ts,
                'do' => $do,
                'id' => $id,
                'ip' => (string)($row['ip'] ?? ''),
                'status' => $status,
                'ms' => round($ms, 2),
            ];
        }

        if (!isset($doMap[$do])) {
            $doMap[$do] = [
                'count' => 0,
                'total_ms' => 0.0,
                'max_ms' => 0.0,
                'slow_count' => 0,
            ];
        }

        $doMap[$do]['count']++;
        $doMap[$do]['total_ms'] += $ms;
        if ($ms > $doMap[$do]['max_ms']) {
            $doMap[$do]['max_ms'] = $ms;
        }
        if ($ms >= SERVER_PROFILE_SLOW_MS) {
            $doMap[$do]['slow_count']++;
        }
    }

    usort($slowRows, static function (array $a, array $b): int {
        return ($b['ms'] <=> $a['ms']);
    });
    $slowRows = array_slice($slowRows, 0, $top);

    usort($recentRows, static function (array $a, array $b): int {
        return ($b['ts'] <=> $a['ts']);
    });
    $recentRows = array_slice($recentRows, 0, $top);

    $byDo = [];
    foreach ($doMap as $do => $entry) {
        $doCount = max(1, (int)$entry['count']);
        $byDo[] = [
            'do' => $do,
            'count' => (int)$entry['count'],
            'avg_ms' => round(((float)$entry['total_ms']) / $doCount, 2),
            'max_ms' => round((float)$entry['max_ms'], 2),
            'slow_count' => (int)$entry['slow_count'],
            'slow_rate_percent' => round(((float)$entry['slow_count'] * 100.0) / $doCount, 2),
        ];
    }
    usort($byDo, static function (array $a, array $b): int {
        return ($b['avg_ms'] <=> $a['avg_ms']) ?: ($b['count'] <=> $a['count']);
    });
    $byDo = array_slice($byDo, 0, $top);

    sort($samples, SORT_NUMERIC);
    $avgMs = $count > 0 ? round($totalMs / $count, 2) : 0.0;

    return [
        'count' => $count,
        'error_count' => $errorCount,
        'slow_count' => $slowCount,
        'avg_ms' => $avgMs,
        'p95_ms' => server_profile_percentile($samples, 95.0),
        'p99_ms' => server_profile_percentile($samples, 99.0),
        'recent' => $recentRows,
        'slowest' => $slowRows,
        'by_do' => $byDo,
    ];
}

function server_profile_percentile(array $sortedValues, float $percent): float
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

function server_cache_has_meaningful_value($value): bool
{
    if ($value === null) {
        return false;
    }

    if (is_string($value)) {
        return trim($value) !== '';
    }

    if (is_int($value) || is_float($value)) {
        return true;
    }

    if (is_bool($value)) {
        return $value;
    }

    if (is_array($value)) {
        if (empty($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (server_cache_has_meaningful_value($item)) {
                return true;
            }
        }

        return false;
    }

    return true;
}

function server_cache_is_valid_port_payload($value): bool
{
    if (!is_array($value) || !array_key_exists('in', $value) || !array_key_exists('out', $value)) {
        return false;
    }

    return server_cache_has_meaningful_value($value['in']) || server_cache_has_meaningful_value($value['out']);
}

function server_cache_is_valid_result_payload($value): bool
{
    if (!is_array($value) || !array_key_exists('result', $value)) {
        return false;
    }

    return server_cache_has_meaningful_value($value['result']);
}

function server_cache_is_valid_array_payload($value): bool
{
    return is_array($value) && server_cache_has_meaningful_value($value);
}

function server_cache_is_valid_oid_map_payload($value): bool
{
    return is_array($value) && !empty($value['oid']) && is_array($value['oid']);
}

function server_cache_is_valid_switch_payload($value): bool
{
    return is_array($value) && !empty($value['id']) && !empty($value['netip']) && !empty($value['snmpro']);
}

function server_cache_validate_hit($cache, string $cacheKey, $cachedValue, callable $validator)
{
    if ($cachedValue === null) {
        return null;
    }

    if ($validator($cachedValue)) {
        return $cachedValue;
    }

    try {
        $cache->delete($cacheKey);
    } catch (Throwable $e) {
        error_log('[CACHE] Failed to delete invalid cache key: ' . $e->getMessage());
    }

    return null;
}

function server_cache_get_redis_client($cache)
{
    if (!is_object($cache) || !method_exists($cache, 'redisClient')) {
        return null;
    }

    try {
        return $cache->redisClient();
    } catch (Throwable $e) {
        error_log('[CACHE] Redis client unavailable: ' . $e->getMessage());
        return null;
    }
}

function server_cache_acquire_lock($cache, string $cacheKey): ?array
{
    $redis = server_cache_get_redis_client($cache);
    if ($redis === null) {
        return null;
    }

    $lockKey = 'lock:' . $cacheKey;
    $ttl = max(1, (int)SERVER_CACHE_STAMPEDE_LOCK_TTL);

    try {
        $token = bin2hex(random_bytes(8));
    } catch (Throwable $e) {
        $token = uniqid('', true);
    }

    try {
        $ok = $redis->set($lockKey, $token, 'EX', $ttl, 'NX');
        if ($ok === true || $ok === 'OK') {
            return ['acquired' => true, 'key' => $lockKey, 'token' => $token];
        }

        return ['acquired' => false];
    } catch (Throwable $e) {
        error_log('[CACHE] Lock acquire failed: ' . $e->getMessage());
    }

    return null;
}

function server_cache_release_lock($cache, ?array $lock): void
{
    if (
        $lock === null
        || empty($lock['acquired'])
        || empty($lock['key'])
        || !isset($lock['token'])
    ) {
        return;
    }

    $redis = server_cache_get_redis_client($cache);
    if ($redis === null) {
        return;
    }

    $lockKey = (string)$lock['key'];
    $token = (string)$lock['token'];

    try {
        $redis->eval(
            'if redis.call("get", KEYS[1]) == ARGV[1] then return redis.call("del", KEYS[1]) else return 0 end',
            1,
            $lockKey,
            $token
        );
    } catch (Throwable $e) {
        try {
            $currentToken = $redis->get($lockKey);
            if ((string)$currentToken === $token) {
                $redis->del([$lockKey]);
            }
        } catch (Throwable $inner) {
            error_log('[CACHE] Lock release failed: ' . $inner->getMessage());
        }
    }
}

function server_cache_wait_for_fresh_value($cache, string $cacheKey, callable $validator)
{
    $attempts = max(0, (int)SERVER_CACHE_STAMPEDE_WAIT_ATTEMPTS);
    $waitUs = max(0, (int)SERVER_CACHE_STAMPEDE_WAIT_US);

    for ($i = 0; $i < $attempts; $i++) {
        if ($waitUs > 0) {
            usleep($waitUs);
        }

        $cachedValue = server_cache_validate_hit($cache, $cacheKey, $cache->get($cacheKey), $validator);
        if ($cachedValue !== null) {
            return $cachedValue;
        }
    }

    return null;
}

function server_cache_get_or_compute($cache, string $cacheKey, int $ttl, callable $validator, callable $producer)
{
    $cachedValue = server_cache_validate_hit($cache, $cacheKey, $cache->get($cacheKey), $validator);
    if ($cachedValue !== null) {
        return $cachedValue;
    }

    $lock = server_cache_acquire_lock($cache, $cacheKey);
    if ($lock === null) {
        $result = $producer();
        if ($ttl > 0 && $validator($result)) {
            $cache->set($cacheKey, $result, $ttl);
        }

        return $result;
    }

    if (empty($lock['acquired'])) {
        $waitedValue = server_cache_wait_for_fresh_value($cache, $cacheKey, $validator);
        if ($waitedValue !== null) {
            return $waitedValue;
        }

        $result = $producer();
        if ($ttl > 0 && $validator($result)) {
            $cache->set($cacheKey, $result, $ttl);
        }

        return $result;
    }

    try {
        $cachedValue = server_cache_validate_hit($cache, $cacheKey, $cache->get($cacheKey), $validator);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $result = $producer();
        if ($ttl > 0 && $validator($result)) {
            $cache->set($cacheKey, $result, $ttl);
        }

        return $result;
    } finally {
        server_cache_release_lock($cache, $lock);
    }
}

function handleRequest($requestData, &$pdo, $cache)
{
    if (!is_array($requestData) || !isset($requestData['do'])) {
        return ['error' => 'Invalid request'];
    }

    $do = strtolower((string)$requestData['do']);
    $do = preg_replace('/[^a-z_]/', '', $do);

    if ($do === '') {
        return ['error' => 'Invalid request'];
    }

    switch ($do) {
        case 'onu':
            return handleOnuRequest($requestData, $pdo, $cache);
        case 'oid':
            return handleOidRequest($requestData, $pdo, $cache);
        case 'snmpget':
            return handleSnmpGetRequest($requestData, $pdo, $cache);
        case 'device':
            return ['result' => handleDeviceRequest($requestData, $pdo, $cache)];
        case 'port':
            return handlePortRequest($requestData, $pdo, $cache);
        case 'profile_stats':
        case 'stats':
            return handleProfileStatsRequest($requestData);
        case 'profile_by_id':
        case 'id_stats':
            return handleProfileByIdRequest($requestData);
        case 'test':
            return ['error' => 'missing_device'];
        default:
            return ['error' => 'Invalid action'];
    }
}

function handlePortRequest($array, &$pdo, $cache)
{
    $switch = getSwitchFromRequest($array, $pdo, $cache);
    $keyPort = getIntValue($array, 'keyport');

    if (empty($switch) || $keyPort === null || $keyPort <= 0) {
        return ['error' => 'invalid_input'];
    }

    $cacheKey = 'api:port:' . $switch['id'] . ':' . $keyPort;
    return server_cache_get_or_compute(
        $cache,
        $cacheKey,
        SERVER_PORT_RESULT_CACHE_TTL,
        'server_cache_is_valid_port_payload',
        static function () use ($switch, $keyPort): array {
            $dataIn = snmp_get_re([
                'netip' => $switch['netip'],
                'snmpro' => $switch['snmpro'],
                'oid' => '1.3.6.1.2.1.2.2.1.14.' . $keyPort,
                'quick' => 'quick_print',
            ]);

            $dataOut = snmp_get_re([
                'netip' => $switch['netip'],
                'snmpro' => $switch['snmpro'],
                'oid' => '1.3.6.1.2.1.2.2.1.20.' . $keyPort,
                'quick' => 'quick_print',
            ]);

            return [
                'in' => snmp_out_($dataIn),
                'out' => snmp_out_($dataOut),
            ];
        }
    );
}

function handleDeviceRequest($array, &$pdo, $cache)
{
    $switch = getSwitchFromRequest($array, $pdo, $cache);
    if (empty($switch) || empty($switch['oidid'])) {
        return false;
    }

    $cacheKey = 'api:device:' . $switch['id'];
    return server_cache_get_or_compute(
        $cache,
        $cacheKey,
        SERVER_DEVICE_RESULT_CACHE_TTL,
        'server_cache_is_valid_array_payload',
        static function () use ($switch, &$pdo, $cache) {
            $selectArray = [
                'oidid' => $switch['oidid'],
                'id' => $switch['id'],
                'netip' => $switch['netip'],
                'snmpro' => $switch['snmpro'],
            ];

            $switchMib = fetchOidDeviceWithCache($selectArray, $pdo, $cache);
            return pmon_snmp_foreach($switchMib, $selectArray);
        }
    );
}

function handleOidRequest($array, &$pdo, $cache)
{
    $switch = getSwitchFromRequest($array, $pdo, $cache);
    if (empty($switch) || empty($switch['oidid']) || empty($switch['netip'])) {
        return false;
    }

    $oid = sanitize_oid((string)($array['oid'] ?? ''));
    if ($oid === null) {
        return ['error' => 'invalid_oid'];
    }

    $cacheKey = 'api:oid:' . $switch['id'] . ':' . md5($oid);
    return server_cache_get_or_compute(
        $cache,
        $cacheKey,
        SERVER_OID_RESULT_CACHE_TTL,
        'server_cache_is_valid_result_payload',
        static function () use ($switch, $oid): array {
            $selectArray = [
                'oid' => $oid,
                'oidid' => $switch['oidid'],
                'id' => $switch['id'],
                'netip' => $switch['netip'],
                'snmpro' => $switch['snmpro'],
            ];

            return pmon_snmp_lite($selectArray);
        }
    );
}

function handleSnmpGetRequest($array, &$pdo, $cache)
{
    $oid = sanitize_oid((string)($array['oid'] ?? ''));
    $netip = trim((string)($array['netip'] ?? ''));
    $snmpro = trim((string)($array['snmpro'] ?? ''));

    if ($oid === null || $netip === '' || $snmpro === '') {
        return false;
    }

    $cacheKey = 'api:snmpget:' . md5($netip . '|' . $snmpro . '|' . $oid);
    return server_cache_get_or_compute(
        $cache,
        $cacheKey,
        SERVER_SNMPGET_RESULT_CACHE_TTL,
        'server_cache_is_valid_result_payload',
        static function () use ($oid, $netip, $snmpro): array {
            $selectArray = [
                'oid' => $oid,
                'netip' => $netip,
                'snmpro' => $snmpro,
            ];

            return pmon_snmp_lite($selectArray);
        }
    );
}

function handleOnuRequest($array, &$pdo, $cache)
{
    $switch = getSwitchFromRequest($array, $pdo, $cache);
    if (empty($switch) || empty($switch['oidid'])) {
        return false;
    }

    $keyOnu = getIntValue($array, 'keyonu');
    if ($keyOnu === null || $keyOnu <= 0) {
        return false;
    }

    $typesRaw = (string)($array['types'] ?? '');
    $types = normalize_types_list($typesRaw);
    $pon = trim((string)($array['pon'] ?? ''));

    if (empty($types) || $pon === '') {
        return false;
    }

    $keyPort = getIntValue($array, 'keyport');
    $cacheKey = 'api:onu:' . $switch['id'] . ':' . $keyOnu . ':' . (int)$keyPort . ':' . md5($pon . '|' . implode(',', $types));
    return server_cache_get_or_compute(
        $cache,
        $cacheKey,
        SERVER_ONU_RESULT_CACHE_TTL,
        'server_cache_is_valid_array_payload',
        static function () use ($switch, $types, $pon, $keyOnu, $keyPort, &$pdo, $cache) {
            $selectArray = [
                'oidid' => $switch['oidid'],
                'id' => $switch['id'],
                'netip' => $switch['netip'],
                'snmpro' => $switch['snmpro'],
                'types' => implode(',', $types),
                'pon' => $pon,
                'keyonu' => $keyOnu,
                'global' => 'onu',
            ];

            if ($keyPort !== null && $keyPort > 0) {
                $selectArray['keyport'] = $keyPort;
            }

            $switchMib = fetchOidData($selectArray, $pdo, $cache);
            return pmon_snmp_get($switchMib);
        }
    );
}

function handleOnuRequest_($array, &$pdo, $cache)
{
    return handleOnuRequest($array, $pdo, $cache);
}

function get_switch_data($id, &$pdo, $cache)
{
    $id = (int)$id;
    if ($id <= 0) {
        return null;
    }

    static $localCache = [];
    if (isset($localCache[$id])) {
        return $localCache[$id];
    }

    $cacheKey = 'data_switch_cache_' . $id;
    $cachedResult = server_cache_validate_hit($cache, $cacheKey, $cache->get($cacheKey), 'server_cache_is_valid_switch_payload');

    if (is_array($cachedResult)) {
        $localCache[$id] = $cachedResult;
        return $cachedResult;
    }

    $pdo = ensurePdo($pdo, $cache);
    $stmt = $pdo->prepare('SELECT netip, id, snmpro, oidid FROM switch WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $switch = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($switch) || empty($switch['id'])) {
        return null;
    }

    $cache->set($cacheKey, $switch, SERVER_SWITCH_CACHE_TTL);
    $localCache[$id] = $switch;
    return $switch;
}

function pmon_snmp_lite($value)
{
    $result = [];

    if (isset($value['oid']) && $value['oid'] !== '') {
        $get = snmp_get_re($value);
        $result['result'] = snmp_output($get, $value['oid']);
    }

    return $result;
}

function pmon_snmp_foreach($array, $switch)
{
    $result = [];

    if (!isset($array['oid']) || !is_array($array['oid']) || empty($array['oid'])) {
        return $result;
    }

    $host = (string)($switch['netip'] ?? '');
    $community = (string)($switch['snmpro'] ?? '');

    if (SERVER_SNMP_BULK_ENABLED && $host !== '' && $community !== '' && count($array['oid']) > 1) {
        $bulkItems = [];
        foreach ($array['oid'] as $type => $oid) {
            $safeOid = sanitize_oid((string)$oid);
            if ($safeOid === null) {
                continue;
            }
            $bulkItems[] = ['types' => (string)$type, 'oid' => $safeOid];
        }

        if (count($bulkItems) > 1) {
            $bulkResult = snmp_get_bulk($host, $community, $bulkItems);
            if (is_array($bulkResult) && !isset($bulkResult['error'])) {
                foreach ($bulkResult as $type => $data) {
                    $result[$type] = snmp_out($data['result'] ?? '');
                }
                if (!empty($result)) {
                    return $result;
                }
            }
        }
    }

    foreach ($array['oid'] as $type => $oid) {
        $safeOid = sanitize_oid((string)$oid);
        if ($safeOid === null) {
            continue;
        }

        $switchData = [
            'netip' => $host,
            'snmpro' => $community,
            'oid' => $safeOid,
            'quick' => 'quick_print',
        ];

        $get = snmp_get_re($switchData);
        $result[$type] = snmp_out($get);
    }

    return $result;
}

function pmon_snmp_get($array)
{
    $result = [];

    if (!is_array($array) || empty($array)) {
        return $result;
    }

    $first = reset($array);
    $host = (string)($first['netip'] ?? '');
    $community = (string)($first['snmpro'] ?? '');

    if (SERVER_SNMP_BULK_ENABLED && $host !== '' && $community !== '' && count($array) > 1) {
        $bulkItems = [];
        foreach ($array as $type => $value) {
            $safeOid = sanitize_oid((string)($value['oid'] ?? ''));
            if ($safeOid === null) {
                continue;
            }

            $bulkItems[] = [
                'types' => (string)$type,
                'oid' => $safeOid,
                'format' => $value['format'] ?? null,
            ];
        }

        if (count($bulkItems) > 1) {
            $bulkResult = snmp_get_bulk($host, $community, $bulkItems);
            if (is_array($bulkResult) && !isset($bulkResult['error'])) {
                foreach ($bulkResult as $type => $data) {
                    $value = $data['result'] ?? null;
                    if (isset($data['format']) && $data['format'] !== '' && $value !== null) {
                        $result[$type] = result_format($value, $data['format']);
                    } else {
                        $result[$type] = $value;
                    }
                }

                if (!empty($result)) {
                    return $result;
                }
            }
        }
    }

    foreach ($array as $type => $value) {
        if (!isset($value['oid']) || $value['oid'] === '') {
            continue;
        }

        $get = snmp_get_re($value);
        $data = snmp_output($get, $value['oid']);

        if (isset($value['format']) && $value['format'] !== '' && $data !== null) {
            $result[$type] = result_format($data, $value['format']);
        } else {
            $result[$type] = $data;
        }
    }

    return $result;
}

function fetchMysql($array, &$pdo, $cache)
{
    $cacheKey = 'oid_' . $array['oidid'] . '_' . $array['pon'] . '_' . $array['global'];

    static $localCache = [];
    if (isset($localCache[$cacheKey])) {
        return $localCache[$cacheKey];
    }

    $cachedResult = server_cache_validate_hit($cache, $cacheKey, $cache->get($cacheKey), 'server_cache_is_valid_oid_map_payload');
    if (is_array($cachedResult)) {
        $localCache[$cacheKey] = $cachedResult;
        return $cachedResult;
    }

    $pdo = ensurePdo($pdo, $cache);

    $sql = 'SELECT types, oid, result FROM oid WHERE pon = :pon AND oidid = :oidid AND inf = :inf';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':pon' => $array['pon'],
        ':oidid' => $array['oidid'],
        ':inf' => $array['global'],
    ]);

    $sqloid = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $confapi = [];
    foreach ($sqloid as $conf) {
        $type = (string)($conf['types'] ?? '');
        $oidTemplate = trim((string)($conf['oid'] ?? ''));
        if ($type === '' || $oidTemplate === '' || strlen($oidTemplate) > 255) {
            continue;
        }

        // Keep OID template as-is (it may contain keyonu/keyport/s placeholders).
        $confapi['oid'][$type] = $oidTemplate;

        if (!empty($conf['result'])) {
            $confapi['result'][$type] = (string)$conf['result'];
        }
    }

    if (SERVER_OID_CACHE_TTL > 0 && server_cache_is_valid_oid_map_payload($confapi)) {
        $cache->set($cacheKey, $confapi, SERVER_OID_CACHE_TTL);
    }
    $localCache[$cacheKey] = $confapi;
    return $confapi;
}

function fetchOidDataWithCache($array, &$pdo, $cache)
{
    return fetchMysql($array, $pdo, $cache);
}

function fetchOidData($array, &$pdo, $cache)
{
    $result = [];
    $confapi = fetchOidDataWithCache($array, $pdo, $cache);
    $types = normalize_types_list((string)($array['types'] ?? ''));

    foreach ($types as $type) {
        if (empty($confapi['oid'][$type])) {
            continue;
        }

        $resolvedOid = zamina(
            (string)$confapi['oid'][$type],
            isset($array['keyonu']) ? (string)$array['keyonu'] : null,
            isset($array['keyport']) ? (string)$array['keyport'] : null
        );
        $safeOid = sanitize_oid($resolvedOid);
        if ($safeOid === null) {
            continue;
        }

        $result[$type]['oid'] = $safeOid;
        $result[$type]['netip'] = $array['netip'];
        $result[$type]['id'] = $array['id'];
        $result[$type]['snmpro'] = $array['snmpro'];

        if (!empty($array['keyonu'])) {
            $result[$type]['keyonu'] = $array['keyonu'];
        }

        if (!empty($array['keyport'])) {
            $result[$type]['keyport'] = $array['keyport'];
        }

        if (!empty($confapi['result'][$type])) {
            $result[$type]['format'] = $confapi['result'][$type];
        }
    }

    return $result;
}

function zamina(string $oid, ?string $keyonu = null, ?string $keyport = null): string
{
    $result = str_replace(
        ['keyonu', 'keyport'],
        [$keyonu ?? '', $keyport ?? ''],
        $oid
    );

    if ($keyonu !== null && $keyonu !== '') {
        $parts = explode('.', $result);
        foreach ($parts as &$part) {
            if ($part === 's') {
                $part = $keyonu;
            }
        }
        unset($part);
        $result = implode('.', $parts);
    }

    return trim($result);
}

function zamina_($oid, $keyonu = null, $keyport = null)
{
    return zamina((string)$oid, $keyonu !== null ? (string)$keyonu : null, $keyport !== null ? (string)$keyport : null);
}

function result_format($data, $format)
{
    if ($format === null || $format === '' || $data === null) {
        return $data;
    }

    $format = (string)$format;

    if (str_starts_with($format, 'a:')) {
        $res = @unserialize($format, ['allowed_classes' => false]);
        if (is_array($res)) {
            if (array_key_exists($data, $res)) {
                return $res[$data];
            }
            $key = (string)$data;
            if (array_key_exists($key, $res)) {
                return $res[$key];
            }
            return '';
        }
    }

    if (stripos($format, 'FUNC') !== false && preg_match('/=(.*)INT(-?\d+)=/i', $format, $matches)) {
        $operation = strtoupper((string)$matches[1]);
        $operand = (float)$matches[2];
        $numeric = (float)$data;

        if (str_contains($operation, 'FUNCT1')) {
            return ($operand == 0.0) ? null : ($numeric / $operand);
        }

        if (str_contains($operation, 'FUNCT2')) {
            return $numeric * $operand;
        }
    }

    return $data;
}

function result_format_($data, $format)
{
    return result_format($data, $format);
}

function snmp_get_recheck($value)
{
    for ($attempt = 0; $attempt < SERVER_SNMP_RECHECK_ATTEMPTS; $attempt++) {
        $response = @snmp2_get(
            (string)$value['netip'],
            (string)$value['snmpro'],
            (string)$value['oid'],
            SERVER_SNMP_TIMEOUT,
            SERVER_SNMP_RETRIES
        );

        if ($response !== null && $response !== false && $response !== '') {
            return $response;
        }

        if (SERVER_SNMP_RECHECK_SLEEP_US > 0) {
            usleep(SERVER_SNMP_RECHECK_SLEEP_US);
        }
    }

    return null;
}

function snmp_get_re($value)
{
    $host = (string)($value['netip'] ?? '');
    $community = (string)($value['snmpro'] ?? '');
    $oid = (string)($value['oid'] ?? '');

    if ($host === '' || $community === '' || $oid === '') {
        return null;
    }

    if (isset($value['quick']) && $value['quick'] === 'quick_print') {
        snmp_set_quick_print(0);
    }

    $response = @snmp2_get($host, $community, $oid, SERVER_SNMP_TIMEOUT, SERVER_SNMP_RETRIES);
    if ($response !== null && $response !== false && $response !== '') {
        return $response;
    }

    return snmp_get_recheck($value);
}

function snmp_output($data, $oid = null)
{
    if ($data === null || $data === false) {
        return '';
    }

    $replacements = [
        'INTEGER:',
        'Hex-STRING:',
        'STRING:',
        'Gauge32:',
        'Gauge64:',
        'Counter32:',
        'Counter64:',
        'Timeticks:',
        (string)$oid,
        '=',
        '"',
        ' ',
    ];

    $value = str_replace($replacements, '', (string)$data);
    return trim($value);
}

function snmp_out($data)
{
    return $data;
}

function snmp_out_($data)
{
    if ($data === null || $data === false) {
        return '';
    }

    $replacements = [
        'INTEGER:',
        'Hex-STRING:',
        'STRING:',
        'Gauge32:',
        'Gauge64:',
        'Counter32:',
        'Counter64:',
        'Timeticks:',
        '=',
        '"',
    ];

    $value = str_replace($replacements, '', (string)$data);
    return trim($value);
}

function fetchOidDeviceWithCache($array, &$pdo, $cache)
{
    $cacheKey = 'device_' . $array['oidid'] . '_health';

    static $localCache = [];
    if (isset($localCache[$cacheKey])) {
        return $localCache[$cacheKey];
    }

    $cachedResult = server_cache_validate_hit($cache, $cacheKey, $cache->get($cacheKey), 'server_cache_is_valid_oid_map_payload');
    if (is_array($cachedResult)) {
        $localCache[$cacheKey] = $cachedResult;
        return $cachedResult;
    }

    $pdo = ensurePdo($pdo, $cache);

    $sql = 'SELECT types, oid, result FROM oid WHERE pon = :pon AND oidid = :oidid AND inf = :inf';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':pon' => 'device',
        ':oidid' => $array['oidid'],
        ':inf' => 'health',
    ]);

    $sqloid = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $confapi = [];
    foreach ($sqloid as $conf) {
        $type = (string)($conf['types'] ?? '');
        $oidTemplate = trim((string)($conf['oid'] ?? ''));
        if ($type === '' || $oidTemplate === '' || strlen($oidTemplate) > 255) {
            continue;
        }

        $confapi['oid'][$type] = $oidTemplate;

        if (!empty($conf['result'])) {
            $confapi['result'][$type] = (string)$conf['result'];
        }
    }

    if (SERVER_OID_CACHE_TTL > 0 && server_cache_is_valid_oid_map_payload($confapi)) {
        $cache->set($cacheKey, $confapi, SERVER_OID_CACHE_TTL);
    }
    $localCache[$cacheKey] = $confapi;
    return $confapi;
}

function snmp_get_bulk($host, $community, array $oids, $nonRepeaters = 0, $maxRepetitions = 10)
{
    $results = [];

    $host = trim((string)$host);
    $community = trim((string)$community);

    if ($host === '' || $community === '') {
        return ['error' => 'invalid_snmp_credentials', 'raw' => []];
    }

    $safeItems = [];
    foreach ($oids as $item) {
        if (!is_array($item)) {
            continue;
        }

        $oid = sanitize_oid((string)($item['oid'] ?? ''));
        $type = isset($item['types']) ? (string)$item['types'] : '';

        if ($oid === null || $type === '') {
            continue;
        }

        $safeItems[] = [
            'oid' => $oid,
            'types' => $type,
            'format' => $item['format'] ?? null,
        ];
    }

    if (empty($safeItems)) {
        return ['error' => 'empty_oids', 'raw' => []];
    }

    $oidArgs = implode(' ', array_map(
        static fn(array $item): string => escapeshellarg($item['oid']),
        $safeItems
    ));

    $cmd = sprintf(
        'snmpbulkget -v2c -c %s -Cn%d -Cr%d %s %s 2>/dev/null',
        escapeshellarg($community),
        max(0, (int)$nonRepeaters),
        max(1, (int)$maxRepetitions),
        escapeshellarg($host),
        $oidArgs
    );

    $output = [];
    $returnCode = 0;
    exec($cmd, $output, $returnCode);

    if ($returnCode !== 0 || empty($output)) {
        return ['error' => 'SNMP no response', 'raw' => []];
    }

    $output = array_values(array_filter($output, static fn($line): bool => trim((string)$line) !== ''));

    foreach ($safeItems as $lineIndex => $item) {
        $type = $item['types'];
        $line = $output[$lineIndex] ?? null;

        if ($line === null) {
            $results[$type]['result'] = null;
            continue;
        }

        $parsed = parse_snmp_value((string)$line, $item['oid']);
        $results[$type]['result'] = $parsed;

        if (!empty($item['format'])) {
            $results[$type]['format'] = $item['format'];
        }
    }

    return $results;
}

function parse_snmp_value($line, $oid = '')
{
    $line = trim((string)$line);

    if ($line === '') {
        return null;
    }

    if (strpos($line, '=') !== false) {
        $line = trim(substr($line, strpos($line, '=') + 1));
    }

    if (preg_match('/^INTEGER: (.*)$/', $line, $m)) {
        return trim($m[1]);
    }

    if (preg_match('/^STRING: "(.*)"$/', $line, $m)) {
        return $m[1];
    }

    if (preg_match('/^[A-Za-z0-9\-]+: (.*)$/', $line, $m)) {
        return trim($m[1]);
    }

    return $line;
}

function ensurePdo(&$pdo, $cache): PDO
{
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = $cache->db_connect();
    return $pdo;
}

function getIntValue(array $array, string $key): ?int
{
    if (!array_key_exists($key, $array)) {
        return null;
    }

    $value = filter_var($array[$key], FILTER_VALIDATE_INT);
    return ($value === false) ? null : (int)$value;
}

function getSwitchFromRequest(array $array, &$pdo, $cache): ?array
{
    $switchId = getIntValue($array, 'id');
    if ($switchId === null || $switchId <= 0) {
        return null;
    }

    $switch = get_switch_data($switchId, $pdo, $cache);
    return (is_array($switch) && !empty($switch['id'])) ? $switch : null;
}

function normalize_types_list(string $types): array
{
    $types = trim($types, ", \t\n\r\0\x0B");
    if ($types === '') {
        return [];
    }

    $items = array_map('trim', explode(',', $types));
    $items = array_filter($items, static function ($item): bool {
        return $item !== '' && preg_match('/^[a-z0-9_]+$/i', $item) === 1;
    });

    return array_values(array_unique($items));
}

function sanitize_oid(string $oid): ?string
{
    $oid = trim($oid);
    if ($oid !== '' && $oid[0] === '.') {
        $oid = substr($oid, 1);
    }
    if ($oid === '' || strlen($oid) > 255) {
        return null;
    }

    if (preg_match('/^[0-9]+(?:\.[0-9]+)*$/', $oid) !== 1) {
        return null;
    }

    return $oid;
}
?>
