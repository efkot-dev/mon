<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$valid_options_car = [
	'oil', 'koleso', 'kyzol', 'brakes', 'engine', 'lights',	'battery', 'coolant', 'suspension'
];
$name_options_car = [
    'oil' => 'Замінити масло',
    'koleso' => 'Замінити трансмісію',
    'kyzol' => 'Кузовні роботи',
    'brakes' => 'Перевірка гальмівної системи',
    'engine' => 'Перевірка двигуна',
    'tyres' => 'Перевірка шин',
    'lights' => 'Перевірка освітлення',
    'battery' => 'Перевірка акумулятора',
    'coolant' => 'Перевірка системи охолодження',
    'suspension' => 'Перевірка підвіски'
];
$imageMapping = [
    'oil' => '../style/img/car_oil.png',
    'koleso' => '../style/img/car_koleso.png',
    'kyzol' => '../style/img/car_bodywork.png',
    'brakes' => '../style/img/car_brakes.png',
    'engine' => '../style/img/car_engine.png',
    'lights' => '../style/img/car_lights.png',
    'battery' => '../style/img/car_battery.png',
    'coolant' => '../style/img/car_coolant.png',
    'suspension' => '../style/img/car_suspension.png'
];
function status_to($status) {
    $statusMapping = [
        1 => ['status' => 'car_driver', 'img' => '../style/img/uponu.png'],
        2 => ['status' => 'car_alarm', 'img' => '../style/img/car_alarm.png'],
        3 => ['status' => 'car_success', 'img' => '../style/img/success.png']
    ];
    return $statusMapping[$status] ?? null;
}
function list_cto() {
return '
<div id="to_car_list">
<label>
            <input type="checkbox" name="inspection[]" value="oil">
            <span>Замінити масло</span>
</label>
<label>
            <input type="checkbox" name="inspection[]" value="koleso">
            <span>Замінити трансмісію</span>
</label>
<label>
            <input type="checkbox" name="inspection[]" value="kyzol">
            <span>Кузовні роботи</span>
</label>
<label>
<input type="checkbox" name="inspection[]" value="brakes">
            <span>Перевірка гальмівної системи</span>
</label>
<label>
		<input type="checkbox" name="inspection[]" value="engine">
		<span>Перевірка двигуна</span>
 </label>
<label>
            <input type="checkbox" name="inspection[]" value="tyres">
            <span>Перевірка шин</span>
</label>
<label>
            <input type="checkbox" name="inspection[]" value="lights">
            <span>Перевірка освітлення</span>
</label>
<label>
            <input type="checkbox" name="inspection[]" value="battery">
            <span>Перевірка акумулятора</span>
</label>
<label>
            <input type="checkbox" name="inspection[]" value="coolant">
            <span>Перевірка системи охолодження</span>
</label>
<label>
            <input type="checkbox" name="inspection[]" value="suspension">
            <span>Перевірка підвіски</span>
</label>
</div>';	
}
function calculateMileage($carId, $toId, $startOdometer, $kmToBeDriven) {
    global $db;
	$toData = array();
		$sqlOdometer = "SELECT finish_odometr FROM users_odometr 
			WHERE car_id = '$carId' 
				AND finish_odometr IS NOT NULL 
					AND start_odometr >= '$startOdometer' AND status = '2'
						ORDER BY start_date DESC 
							LIMIT 1";
        $odometerResult = $db->Simple($sqlOdometer);
		if (isset($odometerResult['finish_odometr'])) {
            $lastOdometer = $odometerResult['finish_odometr'];
            $mileage = $lastOdometer - $startOdometer;
            $progress = ($kmToBeDriven > 0) ? min(100, ($mileage / $kmToBeDriven) * 100) : 0;
            $status = ($mileage >= $kmToBeDriven) ? 2 : 1;
            $toData = [
                    'toId' => $toId,
                    //'name' => $toName,
                    'startOdometer' => $startOdometer,
                    'lastOdometer' => $lastOdometer,
                    'mileage' => $mileage,
                    'progress' => $progress,
                    'curent' => $odometerResult['finish_odometr'],
                    'status' => $status
            ];
			$updateSql = "UPDATE car_cto SET status = '$status', progress = '$progress',  mileage = '$mileage', 
			`update` = NOW() WHERE id = '$toId'";
            $db->query($updateSql);
        }
    return $toData;
}

?>