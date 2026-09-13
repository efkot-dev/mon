<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(isset($id) && $id>0){
	$sql = "
	SELECT battery_used.connectd, battery_used.batteryid as bat_id, battery_used.deviceid, battery.name AS bat_name,battery.model AS bat_model, battery.types AS bat_types, battery.amper AS bat_amper, battery.voltage AS bat_volt
		FROM battery_used
			JOIN battery ON battery.id = battery_used.batteryid
				WHERE battery_used.connectd = 'olt' AND battery_used.deviceid = {$id}";
	$sql_data_battery = $db->SimpleWhile($sql);
}
if(isset($sql_data_battery) && count($sql_data_battery)>0){
echo '<div class="getbattery">
<div class="getname">Підключені акумулятори</div>
	<div class="getlist">';
	foreach($sql_data_battery as $battery){	
	$type_akb = str_replace('lifepo4', 'LP4', $battery['bat_types']);
	$sql_data_volt = "SELECT p.id, p.volt as ping3_voltage FROM mon_ping3 p JOIN battery_used bu ON bu.deviceid = p.id WHERE bu.connectd = 'ping3' AND bu.batteryid = {$battery['bat_id']} LIMIT 1";
	$get_volta = '<span class="volta"><span class="a">13.3V</span></span>';
		echo'
		<div class="getakb">		
				<span class="techimg">
					<img src="../style/img/getbattery.png">
				</span>
				<span class="tech">
					<span class="v">'.$battery['bat_volt'].'V</span>
					<span class="a">'.$battery['bat_amper'].'Ah</span>
				</span>
				<span class="volta">
					<span class="a">'.$type_akb.'</span>
				</span>					

				<span class="install">
					<span class="i"><b>'.$battery['bat_name'].'</b></span>
				</span>
			</div>';
	}
	
echo'</div>
</div>';
}
?>