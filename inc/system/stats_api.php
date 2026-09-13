<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require_once ENGINE_DIR.'functions/stats_api.php';
header('Content-Type: application/json; charset=utf-8');
const STATS_API_DEFAULT_EVENTS_KEY = 'pmon:api:profile:events';
const STATS_API_DEFAULT_SLOW_KEY = 'pmon:api:profile:slow';
const STATS_API_DEFAULT_SLOW_MS = 300;
try {
    $params = stats_api_read_params($_REQUEST);
    $redis = stats_api_get_redis();
    if ($redis === null) {
        stats_api_respond_error('redis_unavailable', 503, $params);
    }
    $key = $params['slow_only'] ? $params['slow_key'] : $params['events_key'];
    $rows = stats_api_load_rows($redis, $key, $params['limit']);
    $report = stats_api_build_report($rows, $params);
    if ($params['format'] === 'html') {
        header('Content-Type: text/html; charset=utf-8');
        echo stats_api_render_html($report, $params);
        exit;
    }
    echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Throwable $e) {
    stats_api_respond_error('internal_error', 500, ['message' => $e->getMessage()]);
}

?>
