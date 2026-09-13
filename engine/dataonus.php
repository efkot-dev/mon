<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('CONFIG', true);
require_once ROOT_DIR . '/inc/database.php';
require ENGINE_DIR.'init.pmon.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if ($mysqli->connect_error) {
    die("Помилка з'єднання з базою даних: " . $mysqli->connect_error);
}
$update_time = date('Y-m-d H:i:s');
$ids_to_delete = [];
$unique_onukeys = [];
$sql_delet = 'SELECT id, onukey FROM onusdata';
$result = $mysqli->query($sql_delet);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $current_id = $row["id"];
        $current_onukey = $row["onukey"];
        if (isset($unique_onukeys[$current_onukey])) {
            $ids_to_delete[] = $current_id;
        } else {
            $unique_onukeys[$current_onukey] = true;
        }
    }
}
if (!empty($ids_to_delete)) {
    $ids_str = implode(",", $ids_to_delete);
    $delete_query = "DELETE FROM onusdata WHERE id IN ($ids_str)";
    $mysqli->query($delete_query);
}
$sql_onusdata = 'SELECT id, onukey, uid FROM onusdata';
$result_onusdata = $mysqli->query($sql_onusdata);
$onusdata = [];
if ($result_onusdata && $result_onusdata->num_rows > 0) {
    while ($row = $result_onusdata->fetch_assoc()) {
        $onusdata[] = [
            'id' => $row['id'],
            'onukey' => $row['onukey'],
            'uid' => $row['uid']
        ];
    }
} else {
    echo "No records found in onusdata.\n";
    exit;
}
$sql_onus = 'SELECT idonu, mac, sn, uid FROM onus';
$result_onus = $mysqli->query($sql_onus);
$onus = [];
if ($result_onus && $result_onus->num_rows > 0) {
    while ($row = $result_onus->fetch_assoc()) {
        $onus[] = [
            'idonu' => $row['idonu'],
            'mac' => $row['mac'],
            'sn' => $row['sn'],
            'uid' => $row['uid']
        ];
    }
} else {
    echo "No records found in onus.\n";
    exit;
}
foreach ($onusdata as $data) {
    foreach ($onus as $onu) {
        if ($data['onukey'] === $onu['mac'] || $data['onukey'] === $onu['sn']) {
            if ($data['uid'] !== $onu['uid']) {
				if (isset($data['uid'])) {
					$idonu = $mysqli->real_escape_string($onu['idonu']);
					$uid_data = $mysqli->real_escape_string($data['uid']);
					$sql_update = "UPDATE onus SET uid = '$uid_data' WHERE idonu = '$idonu'";
					$mysqli->query($sql_update);
				}				}
        }
    }
}
$mysqli->close();
?>
