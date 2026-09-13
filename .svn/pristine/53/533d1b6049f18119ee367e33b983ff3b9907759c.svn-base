<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ENGINE_DIR.'functions/charts.php';
$output = '';
switch ($act) {
	case 'add':
		$select = 'other';
		$metatags = ['title' => 'Додати пристрій','description' => 'Додати новий SNMP пристрій','page' => 'add_device'];
		$auto_device = $snmpro = $netip = $name = $get_oid = $info_name = '';
		$types = Clean::text($_GET['t'] ?? '');
		$deviseid = Clean::int($_GET['d'] ?? 0);
		$devisey = Clean::int($_GET['y'] ?? 0);
		$oid = Clean::text($_GET['o'] ?? '');
		switch ($types) {
			case 'sfp':
				$select = 'sfp';
				$info_name = 'SFP';
				break;
			case 'onu':
				$select = 'onu';
				$stmt = $pdo->prepare("SELECT type, inface FROM onus WHERE idonu = :idonu");
				$stmt->execute([':idonu' => $devisey]);
				if ($onu = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$info_name = $onu['type'].' '.$onu['inface'];
				}
				break;
		}
		if (isValidOID($oid) && $deviseid > 0) {
			$stmt = $pdo->prepare("SELECT id, netip, snmpro, place FROM switch WHERE id = :id");
			$stmt->execute([':id' => $deviseid]);
			if ($device = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$auto_device = $devisey;
				$get_oid = $oid;
				$netip = $device['netip'];
				$name = trim($info_name . ' ' . $device['place']);
				$snmpro = $device['snmpro'];
			}
		}
		$oid_ping3 = '1.3.6.1.4.1.35160.1.16.1.13.1';
		$device_types = ['olt', 'ping3', 'oid', 'sfp', 'onu', 'switch', 'other'];
		$device_options = '';
		foreach ($device_types as $type) {
			$selected = ($select === $type) ? 'selected' : '';
			$device_options .= "<option value=\"$type\" $selected>" . strtoupper($type) . "</option>";
		}
		$output .= <<<HTML
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=temp"><i class="fi fi-rr-angle-left"></i>Список пристроїв</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Новий пристрій</span>
			</div>
			<form method="post" action="/?do=temp&act=save">
				<input type="hidden" name="auto_device" value="{$auto_device}">
				<table class="main-table">
					<tr>
						<td class="forminputcalc" style="width: 350px;">
							<div class="body-td">
								<label>Тип пристрою:</label>
								<select name="device" id="device_type">
									{$device_options}
								</select>
								<label>Назва пристрою:</label>
								<input type="text" name="name" required value="{$name}">
								<label>IP адреса:</label>
								<input type="text" name="netip" required value="{$netip}">
								<label>SNMP доступ (community):</label>
								<input type="text" name="snmpro" value="{$snmpro}">
								<label>OID температури:</label>
								<input type="text" name="oidtemp" id="oidtemp_field" value="{$get_oid}">
								<label>Нормальна температура:</label>
								<input type="text" name="normal_temp" value="60">
								<label>Критична температура:</label>
								<input type="text" name="critical_temp" value="80">
								<label>Формула (напр. bdcom onu \$temp/256)</label>
								<input type="text" name="formula">
								<label>Сповіщати в Telegram:</label>
								<select name="sendtelegram">
									<option value="no">Ні</option>
									<option value="yes">Так</option>
								</select>
							</div>
						</td>
					</tr>
				</table>
				<div class="flex-right mtop10">
					<button type="submit">{$lang['save']}</button>
				</div>
			</form>
			<script>
				document.addEventListener('DOMContentLoaded', function () {
					const deviceType = document.getElementById('device_type');
					const oidField = document.getElementById('oidtemp_field');
					if (deviceType && oidField) {
						deviceType.addEventListener('change', function () {
							if (deviceType.value === 'ping3') {
								oidField.value = '1.3.6.1.4.1.35160.1.16.1.13.1';
							}
						});
					}
				});
				</script>
			HTML;

		break;
	case 'update':
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
		if (!isset($id) || !is_numeric($id)) {
			header("Location: /?do=temp");
			exit;
		}
		$stmt = $pdo->prepare("SELECT * FROM monitor_temp WHERE id = :id");
		$stmt->execute([':id' => $id]);
		$device = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$device) {
			header("Location: /?do=temp");
			exit;
		}
		$lan = isset($_POST['lan']) ? Clean::float($_POST['lan']) : null;
		$lon = isset($_POST['lon']) ? Clean::float($_POST['lon']) : null;
		if ($lan !== null && $lon !== null) {
			$updateStmt = $pdo->prepare("UPDATE monitor_temp SET lan = :lan, lon = :lon WHERE id = :id");
			$updateStmt->execute([
				':lan' => $lan,':lon' => $lon,':id'  => $id
			]);
		}
		header("Location: /?do=temp&act=view&id=" . $id);
		exit;
	case 'addmap':
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if (!isset($id) || !is_numeric($id)) {
			header("Location: /?do=temp");
			exit;
		}
		$stmt = $pdo->prepare("SELECT * FROM monitor_temp WHERE id = :id");
		$stmt->execute([':id' => $id]);
		$device = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$device) {
			header("Location: /?do=temp");
			exit;
		}
		$metatags = [
			'title' => 'Інформація про пристрій',
			'description' => 'Інформація про пристрій',
			'page' => 'view_device'
		];
		$output .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<a class="brmhref" href="/?do=temp&act=list"><i class="fi fi-rr-angle-left"></i>Список пристроїв</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагування пристрою</span>
		</div>';
		$output .= '
		<link rel="stylesheet" href="../style/map/leaflet.css" />
		<script src="../style/map/leaflet.js"></script>
		<script src="../style/map/mymarker.js"></script>
		<form action="/?do=temp" method="post" id="formadd">
			<input name="act" type="hidden" value="update">
			<input name="id" type="hidden" value="' . $id . '">
			<div id="map" style="height: 500px;"></div>';
		$geo_lan = $device['lan'] ?: $config['geo_lan'];
		$geo_lon = $device['lon'] ?: $config['geo_lon'];
		$marker = '';
		if (!empty($device['lan']) && !empty($device['lon'])) {
			$marker = 'L.marker([' . $device['lan'] . ',' . $device['lon'] . '], {icon: maplocation}).addTo(map);';
		}
		$script = <<<HTML
		<script>
		var lat = '{$geo_lan}';
		var lon = '{$geo_lon}';
		var map = L.map('map');
		map.setView([lat, lon], 18);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
		{$marker}
		var popup = L.popup();
		function onMapClick(e) {
			var lat = e.latlng.lat.toFixed(6);
			var lon = e.latlng.lng.toFixed(6);
			popup
				.setLatLng(e.latlng)
				.setContent('<b>{$lang['add_geo_base']}</b> <br>' +
							'<input name="lan" type="hidden" value="' + lat + '">' +
							'<input name="lon" type="hidden" value="' + lon + '">' +
							'<span class="koomap"><b>{$lang['geo']}</b>: ' + lat + ' ' + lon + '</span><br>' +
							'<button type="submit" class="cssadd">{$lang['save']}</button>')
				.openOn(map);
		}
		map.on('click', onMapClick);
		</script>
		HTML;
		$output .= $script . '</form>';
		break;
	case 'view':		
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if (!isset($id) || !is_numeric($id)) {
			header("Location: /?do=temp");
			exit;
		}
		$stmt = $pdo->prepare("SELECT * FROM monitor_temp WHERE id = :id");
		$stmt->execute([':id' => $id]);
		$device = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$device) {
			header("Location: /?do=temp");
			exit;
		}
		$metatags = [
			'title' => 'Інформація про пристрій','description' => 'Інформація про пристрій','page' => 'view_device'
		];
		$output .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<a class="brmhref" href="/?do=temp&act=list"><i class="fi fi-rr-angle-left"></i>Список пристроїв</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Детальна інформація</span>
		</div>';
		if(!empty($device['lan']) && !empty($device['lon']) ){
			$lan = $device['lan'];
			$lon = $device['lon'];
			$output .= get_weather_hourly_open_meteo($lan, $lon);
		}
		$output .= get_charts_temp_device($id,$device['critical_temp']).'<br>';
		if(empty($device['lan']) && empty($device['lon'])){
			$output .= '<a href="/?do=temp&act=addmap&id='.$device['id'].'">Додати координати</a>';			
		}else{
			$output .= '<a href="/?do=temp&act=addmap&id='.$device['id'].'">Редагувати координати</a>';			
		}
		break;
	case 'edit':
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if (!isset($id) || !is_numeric($id)) {
			header("Location: /?do=temp");
			exit;
		}
		$stmt = $pdo->prepare("SELECT * FROM monitor_temp WHERE id = :id");
		$stmt->execute([':id' => $id]);
		$device = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$device) {
			header("Location: /?do=temp");
			exit;
		}
		$metatags = [
			'title' => 'Редагування пристрою','description' => 'Редагування SNMP пристрою','page' => 'edit_device'
		];
		$output .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<a class="brmhref" href="/?do=temp"><i class="fi fi-rr-angle-left"></i>Список пристроїв</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагування пристрою</span>
		</div>
		<form method="post" action="/?do=temp&act=save">
		<input type="hidden" name="id" value="' . $device['id'] . '">
		<table class="main-table"><tr><td class="forminputcalc"  style="width: 350px;">
		<div class="body-td">
			<label>Назва пристрою:</label>
			<input type="text" name="name" value="' . $device['name'] . '" required>

			<label>IP адреса:</label>
			<input type="text" name="netip" value="' . $device['netip'] . '" required>

			<label>SNMP доступ (community):</label>
			<input type="text" name="snmpro" value="' . $device['snmpro'] . '">

			<label>Тип пристрою:</label>
			<select name="device">
				<option value="olt" ' . ($device['device'] == 'olt' ? 'selected' : '') . '>OLT</option>
				<option value="ping3" ' . ($device['device'] == 'ping3' ? 'selected' : '') . '>PING3</option>
				<option value="oid" ' . ($device['device'] == 'oid' ? 'selected' : '') . '>OID</option>
				<option value="switch" ' . ($device['device'] == 'switch' ? 'selected' : '') . '>Switch</option>
				<option value="other" ' . ($device['device'] == 'other' ? 'selected' : '') . '>Інше</option>
			</select>

			<label>OID температури:</label>
			<input type="text" name="oidtemp" value="' . $device['oidtemp'] . '">

			<label>Нормальна температура:</label>
			<input type="text" name="normal_temp" value="' . $device['normal_temp'] . '">

			<label>Критична температура:</label>
			<input type="text" name="critical_temp" value="' . $device['critical_temp'] . '">

			<label>Формула (напр. ($temp/256)/1000):</label>
			<input type="text" name="formula" value="' . $device['formula'] . '">

			<label>Сповіщати в Telegram:</label>
			<select name="sendtelegram">
				<option value="no" ' . ($device['sendtelegram'] == 'no' ? 'selected' : '') . '>Ні</option>
				<option value="yes" ' . ($device['sendtelegram'] == 'yes' ? 'selected' : '') . '>Так</option>
			</select>
		</div>
		</td></tr></table>
		<div class="flex-right mtop10">
			<button type="submit">Оновити</button>
		</div>
		</form>';
		break;
	case 'save':
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$data = [
				':name' => Clean::text($_POST['name']) ?? '',
				':netip' => Clean::text($_POST['netip']) ?? '',
				':snmpro' => Clean::text($_POST['snmpro']) ?? '',
				':device' => Clean::text($_POST['device']) ?? 'other',
				':oidtemp' => Clean::text($_POST['oidtemp']) ?? '',
				':normal_temp' => (int)$_POST['normal_temp'] ?? '',
				':critical_temp' => (int)$_POST['critical_temp'] ?? '',
				':sendtelegram' => Clean::text($_POST['sendtelegram']) ?? 'no',
				':formula' => Clean::text($_POST['formula']) ?? ''
			];
			if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
				$data[':id'] = (int)$_POST['id'];
				$stmt = $pdo->prepare("UPDATE monitor_temp SET name = :name, netip = :netip, snmpro = :snmpro, device = :device, oidtemp = :oidtemp, normal_temp = :normal_temp, critical_temp = :critical_temp, sendtelegram = :sendtelegram, formula = :formula WHERE id = :id");
			} else {
				$stmt = $pdo->prepare("INSERT INTO monitor_temp (name, netip, snmpro, device, oidtemp, normal_temp, critical_temp, sendtelegram, formula)VALUES (:name, :netip, :snmpro, :device, :oidtemp, :normal_temp, :critical_temp, :sendtelegram, :formula)");
			}
			$stmt->execute($data);
		}
		header("Location: /?do=temp");
		exit;
		break;
	case 'delete':
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if (!isset($id) || !is_numeric($id)) {
			header("Location: /?do=temp");
			exit;
		}
		$stmt = $pdo->prepare("DELETE FROM monitor_temp WHERE id = :id");
		$stmt->execute([':id' => $id]);
		header("Location: /?do=temp");
		exit;
		break;
	case 'list':
		$stmt = $pdo->query("SELECT * FROM monitor_temp ORDER BY temp DESC");
		$sql_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (empty($sql_list)) {
			header("Location: /?do=temp&act=add");
			exit;
		}
		$metatags = [
			'title' => 'Моніторинг температури','description' => 'Список SNMP пристроїв','page' => 'device_list'
		];	
		$output .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Температурний моніторинг</span>
			</div>';
		$output .= '<div class="list_block">';		
		$output .= '<div class="list_block_temp">';	
		foreach ($sql_list as $sensor) {
			$output .= '<a href="/?do=temp&act=view&id='.$sensor['id'].'" class="bl_temp">';	
			$output .= '<div class="'.($sensor['temp']>=$sensor['critical_temp'] ? 'critical_sensor_temp':'sensor_temp').'">
			'.$sensor['temp'].'<span>°C</span>
			</div>';
			$output .= '<div class="sensor_name">'.$sensor['name'].'</div>';	
			$output .= '</a>';	
		}
		$output .= '</div>';			
		$output .= '</div>';			
		break;
	default:
		$metatags = [
			'title' => 'Моніторинг температури','description' => 'Список SNMP пристроїв','page' => 'device_list'
		];
		$output .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Температурний моніторинг</span>
		</div>
		<div class="container">
		<div class="left-column">
			<div class="menu_olt_left">
				<a class="menu-sub" href="/?do=temp&act=add">
				<img src="../style/img/add.png">Новий пристрій</a>
				<a class="menu-sub" href="/?do=temp&act=list">
				<img src="../style/img/edit.png">Панель моніторингу</a>
			</div>
		</div>
		<div class="right-column">

		<table class="resp-tab" cellpadding="5">
		<thead>
		<tr>
			<th>Назва</th><th>IP</th><th>Тип</th><th>Температура</th>
			<th>Критично</th><th>Сповіщення</th><th>Позиція</th>
			<th>Дії</th>
		</tr>
		</thead><tbody>';
		$stmt = $pdo->query("SELECT * FROM monitor_temp ORDER BY id DESC");
		$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($devices as $d) {
			$output .= '<tr>
				<td class="td_url text_1">' . $d['name'] . '</td>
				<td><span class="offs_">' . $d['netip'] . '</span></td>
				<td>' . $d['device'] . '</td>
				<td>' . $d['temp'] . '</td>
				<td>' . $d['critical_temp'] . '</td>
				<td>' . ($d['sendtelegram']=='yes' ? '<img style="height:16px;vertical-align: sub;" src="../style/img/telegram.png">' : '') . '</td>
				<td>' . (!empty($d['lan']) ? '<img style="height:16px;vertical-align: sub;" src="../style/img/savegeo.png">' : '<a class="panel_house rr_2" href="/?do=temp&act=addmap&id=' . $d['id'] . '">додати</a>') . '</td>
				<td>
					<a class="panel_house rr_2" href="/?do=temp&act=edit&id=' . $d['id'] . '">Налаштувати</a>
					<a class="panel_house rr_1" href="/?do=temp&act=delete&id=' . $d['id'] . '" onclick="return confirm(\'Видалити?\')">Видалити</a>
				</td>
			</tr>';
		}
		$output .= '</tbody></table>';
		$output .= '</div></div>';
		break;
}
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', $output);
$tpl->compile('content');
$tpl->clear();
?>
