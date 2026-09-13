<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.core.php';
$onus = $pdo->query("SELECT idonu FROM onus")->fetchAll(PDO::FETCH_COLUMN, 0);
// DELET rxolt_signal
$history_onus = $pdo->query("SELECT DISTINCT onu FROM rxolt_signal")->fetchAll(PDO::FETCH_COLUMN, 0);
$missing_onus = array_diff($history_onus, $onus);
if (!empty($missing_onus)) {
    foreach (array_chunk($missing_onus, 1000) as $chunk) {
        $placeholders = rtrim(str_repeat('?,', count($chunk)), ',');
		$stmt = $pdo->prepare("DELETE FROM historysignal WHERE onu IN ($placeholders)");
        $stmt->execute($chunk);
    }
}
// DELETE - comment
$comm_onus = $pdo->query("SELECT DISTINCT idonu FROM onus_comm")->fetchAll(PDO::FETCH_COLUMN, 0);
$missing_onus_comm = array_diff($comm_onus, $onus);
if (!empty($missing_onus_comm)) {
    foreach (array_chunk($missing_onus_comm, 100) as $chunk_comm) {
        $id_onu_comm = rtrim(str_repeat('?,', count($chunk_comm)), ',');
		$stmt = $pdo->prepare("DELETE FROM onus_comm WHERE idonu IN ($id_onu_comm)");
        $stmt->execute($chunk_comm);
    }
}
// DELETE - onus_monitor
$onus_monitor_onus = $pdo->query("SELECT DISTINCT idonu FROM onus_monitor")->fetchAll(PDO::FETCH_COLUMN, 0);
$monitor_onus = array_diff($onus_monitor_onus, $onus);
if (!empty($monitor_onus)) {
    foreach (array_chunk($monitor_onus, 50) as $chunk_mon) {
        $id_onu_mon = rtrim(str_repeat('?,', count($chunk_mon)), ',');
		$stmt = $pdo->prepare("DELETE FROM onus_monitor WHERE idonu IN ($id_onu_mon)");
        $stmt->execute($chunk_mon);
    }
}
// TRAFF
$device_ids = $pdo->query("SELECT deviceid FROM traff_monitor WHERE types = 'onu'")->fetchAll(PDO::FETCH_COLUMN, 0);
$invalid_devices = array_diff($device_ids, $onus);
if (!empty($invalid_devices)) {
    foreach (array_chunk($invalid_devices, 1000) as $packet) {
        $portid = rtrim(str_repeat('?,', count($packet)), ',');
        $stmt = $pdo->prepare("DELETE FROM snmp_data WHERE portid IN ($portid)");
        $stmt->execute($packet);
        $stmt2 = $pdo->prepare("DELETE FROM bandwidth_daily WHERE portid IN ($portid)");
        $stmt2->execute($packet);
    }
}
?>
