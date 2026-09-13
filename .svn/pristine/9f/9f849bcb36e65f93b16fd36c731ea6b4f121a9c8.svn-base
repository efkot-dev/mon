<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.core.php';
require ROOT_DIR . '/inc/functions/uptime.php';
if (isset($confPMon['CHECK_SNMP']) && !empty($confPMon['CHECK_SNMP']) && $confPMon['CHECK_SNMP'] == 1) {
	$stmt = $pdo->prepare("SELECT id, netip, snmpro, snmp_access, place FROM switch WHERE monitor = 'yes'");
	$stmt->execute();
	$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$processes = [];
	foreach ($devices as $device) {
		$pid = pcntl_fork();
		if ($pid == -1) {
			die("bad_fork");
		} elseif ($pid) {
			$processes[] = $pid;
		} else {
			$childPdo = getNewPDO();
			check_snmp_uptime($device, $childPdo, $confPMon);
			exit(0);
		}
	}
	foreach ($processes as $process) {
		pcntl_waitpid($process, $status);
	}
}
?>
