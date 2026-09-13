<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
$traccar_api = (isset($confPMon['GPS_TRACCAR_URL']) && !empty($confPMon['GPS_TRACCAR_URL']) ? $confPMon['GPS_TRACCAR_URL']:false);
$traccar_login = (isset($confPMon['GPS_TRACCAR_LOGIN']) && !empty($confPMon['GPS_TRACCAR_LOGIN']) ? $confPMon['GPS_TRACCAR_LOGIN']:false);
$traccar_password = (isset($confPMon['GPS_TRACCAR_PASSWORD']) && !empty($confPMon['GPS_TRACCAR_PASSWORD']) ? $confPMon['GPS_TRACCAR_PASSWORD']:false);
if (isset($confPMon['GPS_TRACCAR']) && !empty($confPMon['GPS_TRACCAR']) && $confPMon['GPS_TRACCAR'] == 1 
	&& isset($traccar_api) && isset($traccar_login) && isset($traccar_password) ) {
	$carData = [];
	$apiUrl = $traccar_api;	
	$gps_location = positionTraccarData($apiUrl, $traccar_login, $traccar_password);
	$driver_info = carTraccarData($apiUrl, $traccar_login, $traccar_password);
	$car = [];
	if (!empty($driver_info)) {
		foreach ($driver_info as $info) {
			$car[$info['id']] = $info;
		}
	}
	if (!empty($gps_location)) {
		foreach ($gps_location as $position) {
			$deviceId = $position['deviceId'];
			$carData[$deviceId] = [
				'deviceid' => $deviceId,
				'car_name' => $car[$deviceId]['name'] ?? null,
				'speed' => $position['speed'] ?? 0,
				'ignition' => $position['attributes']['ignition'] ?? false,
				'car_status' => $car[$deviceId]['status'] ?? null,
				'car_update' => $car[$deviceId]['lastUpdate'] ?? null,
				'name' => $position['address'] ?? 'Unknown Device',
				'lan' => $position['latitude'] ?? 0,
				'lon' => $position['longitude'] ?? 0
			];
		}
	}
	if (!empty($carData)) {
		$data_car = serialize($carData);
		$data_car = escape_sql($data_car);
		$sql = "INSERT INTO tempdate (file, data) 
				VALUES ('gps_traccar', '{$data_car}') 
				ON DUPLICATE KEY UPDATE data = '{$data_car}', last_processed = '{$timer}', updated_at = '{$timer}'";
		$db->query($sql);
	}
}
if (isset($confPMon['GPS_TRACKER_COM_UA']) && !empty($confPMon['GPS_TRACKER_COM_UA']) && $confPMon['GPS_TRACKER_COM_UA'] == 1 && isset($confPMon['GPSTCU_LOGIN'])) {
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL            => 'http://gps-tracker.com.ua/login.php',
		CURLOPT_POST           => true,
		CURLOPT_POSTFIELDS     => http_build_query(['login' => $confPMon['GPSTCU_LOGIN'], 'password' => $confPMon['GPSTCU_PASSWORD']]),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIEJAR      => ROOT_DIR.'/export/cache/cookies.txt',
		CURLOPT_COOKIEFILE     => ROOT_DIR.'/export/cache/cookies.txt',
	]);
	$response = curl_exec($curl);
	if ($response === false) {		
		exit;
	}
	curl_setopt_array($curl, [
		CURLOPT_URL            => 'http://gps-tracker.com.ua/loadevents.php?param=icars',
		CURLOPT_HTTPGET        => true,
		CURLOPT_RETURNTRANSFER => true,
	]);
	$carData = curl_exec($curl);
	curl_close($curl);
	$arrayData = json_decode($carData, true);
	if (!empty($arrayData)) {
		$data_car = serialize($arrayData);
		$data_car = escape_sql($data_car);
		$sql = "INSERT INTO tempdate (file, data) 
				VALUES ('gps_tracker', '{$data_car}') 
				ON DUPLICATE KEY UPDATE data = '{$data_car}', last_processed = '{$timer}', updated_at = '{$timer}'";
		$db->query($sql);
	}
}
?>
