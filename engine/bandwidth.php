<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR . '/inc/init.monitor.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if ($mysqli->connect_error) {
    die("Помилка з'єднання з базою даних: " . $mysqli->connect_error);
}
function min_zabix_code($mysqli, $mon){
	$oid_in = str_replace('port', $mon['llid'], $mon['oid_in']);
	$oid_out = str_replace('port',$mon['llid'], $mon['oid_out']);
	$current_in_result = @snmp2_get($mon['netip'], $mon['snmpro'], $oid_in);
	$current_out_result = @snmp2_get($mon['netip'], $mon['snmpro'], $oid_out);
	if ($current_in_result !== false && $current_out_result !== false) {
		$current_in_bytes = intval(str_replace("Counter64: ", "", $current_in_result));
		$current_out_bytes = intval(str_replace("Counter64: ", "", $current_out_result));
		$portid = $mon['id'];
		$sql = "SELECT in_bytes, out_bytes, timestamp FROM snmp_data WHERE portid = '$portid' ORDER BY timestamp DESC LIMIT 1";
		$result = $mysqli->query($sql);
		if ($result && $result->num_rows > 0) {
			$previous_data = $result->fetch_assoc();
			$previous_in_bytes = intval($previous_data['in_bytes']);
			$previous_out_bytes = intval($previous_data['out_bytes']);
			$previous_timestamp = strtotime($previous_data['timestamp']);
			$current_timestamp = time();
			$time_difference = $current_timestamp - $previous_timestamp;
			if ($time_difference > 0) {
				$in_bytes_difference = $current_in_bytes - $previous_in_bytes;
				$out_bytes_difference = $current_out_bytes - $previous_out_bytes;
				if ($in_bytes_difference < 0) {
					$in_bytes_difference = ($current_in_bytes + (2**64 - 1) - $previous_in_bytes);
				}
				if ($out_bytes_difference < 0) {
					$out_bytes_difference = ($current_out_bytes + (2**64 - 1) - $previous_out_bytes);
				}
				$in_bps = ($in_bytes_difference * 8) / $time_difference;
				$out_bps = ($out_bytes_difference * 8) / $time_difference;
				$sql_insert = "INSERT INTO snmp_data (portid, in_bytes, out_bytes, in_bps, out_bps, timestamp) VALUES ('$portid', '$current_in_bytes', '$current_out_bytes', '$in_bps', '$out_bps', NOW())";
				$mysqli->query($sql_insert);
			}
		} else {
			$sql_insert = "INSERT INTO snmp_data (portid, in_bytes, out_bytes, timestamp) VALUES ('$portid', '$current_in_bytes', '$current_out_bytes', NOW())";
			$mysqli->query($sql_insert);
		}
	}
}

$result_list = $mysqli->query("select * from traff_monitor");
if ($result_list->num_rows > 0) {
    while ($mon = $result_list->fetch_assoc()) {
		min_zabix_code($mysqli, $mon);
	}
}
$mysqli->close();
?>
