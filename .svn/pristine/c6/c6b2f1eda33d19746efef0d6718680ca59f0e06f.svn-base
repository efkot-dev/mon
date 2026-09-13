<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR . '/inc/init.core.php';
require ROOT_DIR . '/inc/functions/sql_pdo.php';
require ROOT_DIR . '/inc/functions/core.php';
require ROOT_DIR . '/inc/classes/onu_error.class.php';
$id_device = isset($olt) ? (int)$olt : (isset($_GET['olt']) ? (int)$_GET['olt'] : 0);
$onus_masiv = [];
if ($id_device > 0) {
    $sql_switch = $pdo->prepare("SELECT id, oidid, netip, snmpro FROM switch WHERE id = :id LIMIT 1");
    $sql_switch->execute([':id' => $id_device]);
    $switch = $sql_switch->fetch(PDO::FETCH_ASSOC);
    if ($switch && !empty($switch['id'])) {
        $OnuError = new Onu_Error((int)$switch['id'], $pdo, $switch, $confPMon);
        $temp_error = $OnuError->get_error($switch);
        if ($temp_error && !empty($temp_error)) {
			$stmt = $pdo->prepare("SELECT idonu, sw_shelf, sw_slot, sw_port, portolt, sw_ont, keyonu, zte_idport FROM onus WHERE olt = :olt");
			$stmt->execute([':olt' => $switch['id']]);
			$array_onu = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($array_onu as $onu) {
				$onus_masiv[] = $OnuError->format($onu);
			}
		}
		if ($onus_masiv && !empty($onus_masiv)) {
			$temp_error = $OnuError->save($onus_masiv);
		}
    }
}else{
	$sql_old = "DELETE FROM onus_error WHERE added < (NOW() - INTERVAL 30 DAY)";
	$count_old = $pdo->prepare($sql_old);
	$count_old->execute();
	$sql_null = "DELETE FROM onus_error WHERE error = 0";
	$count_null = $pdo->prepare($sql_null);
	$count_null->execute();
}
?>