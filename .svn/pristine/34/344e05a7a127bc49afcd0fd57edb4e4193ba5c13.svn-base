<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('QUEUE',true);
define('CURL',true);
require ROOT_DIR.'/inc/init.monitor.php';
require_once ENGINE_DIR . 'init.queue.php';
require ROOT_DIR.'/inc/classes/curl.class.php';
$starttime = microtime(true);
$time = date('Y-m-d H:i:s');
$supportonu = false;
$tempresult = [];
if (!is_numeric($olt)) {
	die('unknown_device');
}
$sql_switch = ['sql' => "SELECT * FROM switch WHERE id = '{$olt}' LIMIT 1",'key' => "switch_{$olt}",'time' => 60];
$getswitch = cache_simple_sql($sql_switch);
if (empty($getswitch['id'])) {
	die('unknown_device');
}
$db->query("UPDATE switch SET status = 'go', updates = '{$time}' WHERE id = '{$getswitch['id']}'");
$getmonitor = new Monitor($getswitch['id'], $getswitch['class'], $db, $logger, $classOLT, $cacheManager, $php_class_device);
$supportonu = $getmonitor->getSupportOnu();
if ($supportonu){	
	$tempdata = $getmonitor->start();
}
$config_curl_pool = (isset($getswitch['curl_pool']) && !empty($getswitch['curl_pool']) ? intval($getswitch['curl_pool']) : 3);
if (isset($tempdata) && $tempdata != false) {
	$pauseIntervalAll = 40;
	$tempresult = array_map(function ($getdata) use (&$counterall, $pauseIntervalAll, $config) {
	$result = api__($config['monitorapi'], $getdata);
	$counterall++;
	if ($counterall % $pauseIntervalAll === 0) {
		$currentLoad = sys_getloadavg()[0];
		$sleepTime = max(1, min(6, (int)($currentLoad / 2)));
		sleep($sleepTime);
	}
	return is_array($result) && is_array($getdata) ? array_merge($getdata, $result) : $getdata;
	}, $tempdata);	
    if (is_array_empty($tempresult)) {
        array_map(function ($getdata) use ($getmonitor) {
            match ($getdata['pon']) {
                'epon' => $getmonitor->tempSaveEpon($getdata),'gpon' => $getmonitor->tempSaveGpon($getdata)
            };
        }, $tempresult);
    }
}
if ($getmonitor->getSupportPort()) {
	$indexport = $getmonitor->getPort();
	if (is_array_empty($indexport)) {
		$getmonitor->savePort($indexport);
	}
}
if (is_array_empty($tempresult)) {
	$getmonitor->UpdateInformationOlt();
}
if (!empty($getswitch['id'])) {
	if (is_array_empty($tempresult)) {
		$task_data = [
			'workid' => 56,'device_id' => (int)$getswitch['id'],'type' => 'monitor'
		];
		$message = $context->createMessage(json_encode($task_data));
		$context->createProducer()->send($queue, $message);
	}
	$executionTime = round(microtime(true)-$starttime, 4);
	$db->query("UPDATE switch SET status = 'no', timecheck = '".$executionTime."', timechecklast = '".($getswitch['timecheck'] ?? 0)."' WHERE id = '{$getswitch['id']}'");
	$logger->init([
		'log' => 'device',
		'type' => 'monitor_status',
		'descr' => 'sec:[' . $executionTime . '] ' . $lang['monitorfinish'] . ' ' . (isset($tempresult) ? is_array($tempresult) : ($getswitch['device'] == 'switch' ? 'switch' : 'need_check')),
		'deviceid' => $getswitch['id'],
		'who' => 'cron'
	]);
}
?>
