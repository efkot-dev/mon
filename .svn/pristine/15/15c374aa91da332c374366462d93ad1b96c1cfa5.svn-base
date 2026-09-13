<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (!defined('QUEUE')){
	die('Hacking attempt!');
}
function bdcom_gpon_basic_info($data) {
	return "show gpon interface gpoN ".$data['inface']." onu basic-info";
}
function format_time_bfcom_gpon_time($result) {
	$start_pos = strpos($result, "The current time:");
	if ($start_pos !== false) {
		$history = substr($result, $start_pos);
		$lines = explode("\n", $history);
		echo "<ul>\n";
		foreach ($lines as $line) {
			if (trim($line) == "" || strpos($line, "--More--") !== false) {
				continue;
			}
			echo "<li>" . htmlspecialchars($line) . "</li>\n";
		}
		echo "</ul>\n";
	} else {
		echo "Історія подій не знайдена.";
	}
}
function extract_name($data) {
	if (preg_match('/enable(.*?)#/', $data, $matches)) {
		return trim($matches[1]); 
	}
	if (preg_match('/(.*?)#/', $data, $matches)) {
		return trim($matches[1]); 
	}
	return '';
}
function bdcom_gpon_vlan($data) {
	$commands = array(
		"conf",
		"interface gpoN ".$data['onus']['inface'],
		"gpon onu flow-mapping-profile T".$data['vlan'],
		"gpon onu uni 1 vlan-profile T".$data['vlan'],
		"exit",
		"exit"
	);
	return $commands;
}
function gcom_epon_vlan($data) {
	$inface = str_replace(':', '/',$data['onus']['inface']);
    $commands = array(
        "enable",
        "configure terminal",
        "onu ".$inface,
        "active unique",
        "interface ethernet 0/1",
        isset($data['gvan'])
            ? "onu-vlan-mode tag ".$data['gvan']
            : "onu-vlan-mode tag vlan ".$data['vlan'],
        "exit",
        "exit",
        "exit",
        "copy running-config startup-config",
        "y"
    );
    return $commands;
}
function template_change_vlan($data) {
    switch ($data['switch']['oidid']) {
        case 1:
			return bdcom_gpon_vlan($data);
        case 2:
		case 7:
        case 9:
        case 10:
        case 11:
        case 9:
			return gcom_epon_vlan($data);
		case 35:
		case 41:
        case 15:
        case 12:
		case 6:
    }
}
?>