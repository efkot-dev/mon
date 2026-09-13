<?php
if (!defined('PMONSERVER')) {
    die('Hacking attempt!');
}

$pdo = null;

define('CACHE_TTL', 7200);
define('CACHE_FILE_NAME', md5('config_Momotiuk'));
define('CACHE_FILE_CONFIG', md5('config_Oleksiy'));

require_once PMON_DIR . 'database.php';
require_once SERVER_DIR . 'cache.class.php';
require_once SERVER_DIR . 'functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    send_json_response(405, ['error' => 'method_not_allowed']);
}

$cacheType = defined('CACHE') ? CACHE : 'file';
$redisIp = defined('REDIS_IP') ? REDIS_IP : '127.0.0.1';
$redisPort = defined('REDIS_PORT') ? REDIS_PORT : 6379;

$webServerConfig = [
    'cache' => true,
    'charset' => 'utf8mb4',
    'base' => 'mysql',
    'redis_ip' => $redisIp,
    'redis_port' => $redisPort,
    'typecache' => $cacheType,
];

$cache = new Cache($webServerConfig);

if (function_exists('server_profiler_bind_cache')) {
    server_profiler_bind_cache($cache);
}
if (function_exists('server_profiler_bootstrap')) {
    server_profiler_bootstrap($_POST);
}

$configData = $cache->getConfig(CACHE_FILE_NAME);
if (empty($configData['API_SECRET'])) {
    $fallbackConfig = $cache->getConfig(CACHE_FILE_CONFIG);
    if (is_array($fallbackConfig)) {
        $configData = array_merge((array)$fallbackConfig, (array)$configData);
    }
}

if (empty($configData['API_SECRET'])) {
    $dbConfig = $cache->getConfigFromDb(['API_SECRET']);
    if (is_array($dbConfig)) {
        $configData = array_merge((array)$configData, $dbConfig);
    }
}

if (empty($configData['API_SECRET'])) {
    error_log('[API CONFIG FAIL] API_SECRET not found in cache and DB');
    send_json_response(500, ['error' => 'api_secret_missing']);
}

#api_auth_check((string)$configData['API_SECRET'], false);

$sendData = normalize_request_data($_POST);
if (empty($sendData)) {
    send_json_response(400, ['error' => 'empty_request']);
}

$response = handleRequest($sendData, $pdo, $cache);
if ($response === null) {
    send_json_response(400, ['error' => 'not_support']);
}

send_json_response(200, $response);

function normalize_request_data(array $source): array
{
    $result = [];

    $intFields = ['id', 'keyonu', 'keyport', 'idonu', 'idport', 'oidid', 'hours', 'top', 'limit', 'profile'];
    $stringFields = ['do', 'types', 'pon', 'oid', 'netip', 'snmpro'];

    foreach ($intFields as $field) {
        if (!array_key_exists($field, $source)) {
            continue;
        }

        $value = filter_var($source[$field], FILTER_VALIDATE_INT);
        if ($value !== false) {
            $result[$field] = (int)$value;
        }
    }

    foreach ($stringFields as $field) {
        if (!array_key_exists($field, $source)) {
            continue;
        }

        $value = trim((string)$source[$field]);
        if ($value === '') {
            continue;
        }

        if ($field === 'types') {
            $value = trim($value, ',');
        }

        $result[$field] = $value;
    }

    return $result;
}

function send_json_response(int $statusCode, $payload): void
{
    if (function_exists('server_profiler_finalize')) {
        $payload = server_profiler_finalize($statusCode, $payload);
    }
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
?>
