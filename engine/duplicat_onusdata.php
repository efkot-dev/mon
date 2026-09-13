<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR . '/inc/init.monitor.php';
if(isset($jobid) && $jobid == 14){	
// Скидання індексів
b2($pmon_index);
// Формування списку всіх унікальних ONU
$sqlonu = $db->SimpleWhile("SELECT DISTINCT idonu FROM onus");
$used_onu = [];
if (isset($sqlonu) && count($sqlonu) > 0) {
	foreach ($sqlonu as $onu) {
		$used_onu[$onu['idonu']] = $onu['idonu'];
	}
}
// Очищення сигналів RX_OLT, після видалення OLT або ONU з OLT
$sql_rx_olt_onu = $db->SimpleWhile("SELECT DISTINCT onu FROM rxolt_signal");
$history_used = [];
if (isset($sql_rx_olt_onu) && count($sql_rx_olt_onu) > 0) {
	foreach ($sql_rx_olt_onu as $rx_olt) {
		$history_used[$rx_olt['onu']] = $rx_olt['onu'];
	}
}
$ids_to_delete_rx_olt = array_diff($history_used, $used_onu);
if (!empty($ids_to_delete_rx_olt)) {
	$ids_to_delete_str = implode(',', array_map('intval', $ids_to_delete_rx_olt));
	$delete_query = "DELETE FROM rxolt_signal WHERE onu IN ($ids_to_delete_str)";
	$db->query($delete_query);
}
// Очищення сигналів RX_ONU, після видалення OLT або ONU з OLT
$rx_onu_used = [];
$sql_rx_onu = $db->SimpleWhile("SELECT DISTINCT onu FROM historysignal");
$history_used_rx_onu = [];
if (isset($sql_rx_onu) && count($sql_rx_onu) > 0) {
	foreach ($sql_rx_onu as $rx_olt) {
		$rx_onu_used[$rx_olt['onu']] = $rx_olt['onu'];
	}
}
$ids_to_delete_rx_onu = array_diff($rx_onu_used, $used_onu);
if (!empty($ids_to_delete_rx_onu)) {
	$ids_to_delete_rx_str = implode(',', array_map('intval', $ids_to_delete_rx_onu));
	$delete_query_rx = "DELETE FROM historysignal WHERE onu IN ($ids_to_delete_rx_str)";
	$db->query($delete_query_rx);
}
// Видалити логування комутатора старше 60 днів
$db->query("DELETE FROM devicelogs WHERE added < NOW() - INTERVAL 60 DAY");	
// Видалити вольтаж старше 90 днів
$db->query("DELETE FROM mon_voltage WHERE added < NOW() - INTERVAL 90 DAY");	
// Видалити моніторингу ір старше 90 днів
$db->query("DELETE FROM monitor_ip_log WHERE added < NOW() - INTERVAL 30 DAY");		
// Видалити статистику старше 90 днів
$db->query("DELETE FROM pmonstats WHERE datetime < NOW() - INTERVAL 90 DAY");	
// Видалити моніторинг помилок старше 60 днів
$db->query("DELETE FROM switch_port_err WHERE added < NOW() - INTERVAL 30 DAY");	
}
?>
