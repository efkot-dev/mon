<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('QUEUE',true);
require ROOT_DIR.'/inc/init.monitor.php';
require_once ENGINE_DIR . 'init.queue.php';
$starttime = microtime(true);
$time = date('Y-m-d H:i:s');
$supportonu = false;
$tempresult = [];
$nextcron = false;
$pauseIntervalAll = 40;
if (!is_numeric($olt)) {
	die('unknown_device');
}
$getswitch = $db->Fast('switch', '*', ['id' => $olt]);
if (empty($getswitch['id'])) {
	die('unknown_device');
}
$db->query("UPDATE switch SET status = 'go', updates = '{$time}', jobid = '0' WHERE id = '{$getswitch['id']}'");
$getmonitor = new Monitor($getswitch['id'], $getswitch['class'], $db, $logger, $classOLT, $cacheManager, $php_class_device);
$supportonu = $getmonitor->getSupportOnu();
if ($supportonu){	
	$tempdata = $getmonitor->start();
}
$nextcron = true;
if (isset($tempdata) && !empty($tempdata)) {
	$tempresult = array_map(function ($getdata) use (&$counterall, $pauseIntervalAll, $config) {
		$result = api__($config['monitorapi'], $getdata);
		$counterall++;
		if ($counterall % $pauseIntervalAll === 0) {
			$currentLoad = sys_getloadavg()[0];
			sleep(max(1, min(6, intdiv($currentLoad, 2))));
		}
		return is_array($result) && is_array($getdata) ? array_merge($getdata, $result) : $getdata;
	}, $tempdata);	
	if (is_array_empty($tempresult)) {
		array_map(function ($getdata) use ($getmonitor) {
			match ($getdata['pon']) {
				'epon' => $getmonitor->tempSaveEpon($getdata),
				'gpon' => $getmonitor->tempSaveGpon($getdata)
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
			'data' => [56],
			'switch' => $getswitch['id'],
			'type' => 'signal',
			'properties' => [1],
			'headers' => [1]
		];
		$message = $context->createMessage(json_encode($task_data));
		$context->createProducer()->send($queue, $message);
	}
	$executionTime = (int)microtime(true) - $starttime;
	$db->SQLupdate('switch', [
		'status' => 'no',
		'timecheck' => $executionTime,
		'timechecklast' => $getswitch['timecheck'] ?? 0
	], ['id' => $getswitch['id']]);
	$logger->init([
		'log' => 'device',
		'type' => 'monitor_status',
		'descr' => 'sec:[' . intval($executionTime) . '] ' . $lang['monitorfinish'] . ' ' . (isset($tempresult) ? count($tempresult) : ($getswitch['device'] == 'switch' ? 'switch' : 'need_check')),
		'deviceid' => $getswitch['id'],
		'who' => 'cron'
	]);
}
?>
