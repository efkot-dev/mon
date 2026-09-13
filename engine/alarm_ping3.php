<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$clock_ping3 = date('Y-m-d H:i:s');
$currentTimestamp = time();
$sql_save = [];
if(isset($confPMon['SECURITY_PING3']) && $confPMon['SECURITY_PING3']== 1) {
$stmt = $pdo->prepare("SELECT id, name, netip, snmpro, status FROM alarm_ping3");
$stmt->execute();
$alarm_ping3 = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($alarm_ping3) && count($alarm_ping3) > 0) {
    foreach ($alarm_ping3 as $ping3) {
        $id = $ping3['id'];
        $ip = $ping3['netip'];
        $old_status = $ping3['status'];
        $snmp_community = $ping3['snmpro'];
        $snmp_value = @snmpget($ip, $snmp_community,"1.3.6.1.4.1.35160.1.11.1.4.1",1000000,5);
		if ($snmp_value !== false) {
            preg_match('/INTEGER:\s(\d+)/', $snmp_value, $matches);
            $new_status = isset($matches[1]) ? (int)$matches[1] : null;
			if ($new_status !== null && $new_status !== $old_status) {
				$stmt = $pdo->prepare("UPDATE alarm_ping3 SET status = ? WHERE id = ?");
				$stmt->execute([$new_status, $id]);                
				$note = '';
                if ($old_status == 1 && $new_status == 0) {
                    $note = $lang['alarm_active'];
					$temp_message[] = "[icon-alarm] Спрацювала сигналізація: [b]{$ping3['name']}[/b]\n";
                } elseif ($old_status == 2 && $new_status == 0) {
					$note = $lang['alarm_enable'];					
					$temp_message[] = "[icon-lock] Обєкт під охороню: [b]{$ping3['name']}[/b]\n";
                } elseif ($old_status == 0 && $new_status == 1) {
                    $note = $lang['alarm_enable'];
					$temp_message[] = "[icon-lock] Обєкт під охороню: [b]{$ping3['name']}[/b]\n";
                } elseif ($old_status != $new_status) {
                    $note = $lang['alarm_change'];
                }
                $stmt = $pdo->prepare("INSERT INTO alarm_ping3_history (alarm_id, old_status, new_status, event_time, note) VALUES (?, ?, ?, NOW(), ?)");
				$stmt->execute([$id, $old_status, $new_status, $note]);
            }
        } else {
			$stmt = $pdo->prepare("INSERT INTO alarm_ping3_history (alarm_id, old_status, new_status, event_time, note) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
			$stmt->execute([$id, $old_status, -1,$lang['alarm_not_work']]);
			$stmt = $pdo->prepare("UPDATE alarm_ping3 SET status = ? WHERE id = ?");
			$stmt->execute([-1, $id]);
        }
    }
	if (isset($temp_message) && count($temp_message) > 0) {
		$stmt = $pdo->prepare("INSERT INTO notification (status, type, system, message, added) VALUES (:status, :type, :system, :message, :added)");
		foreach ($temp_message as $id => $data) {
			$stmt->execute([
				':status' => 1,':type' => 64,':system' => 'alarm_ping3',':message' => $data,':added' => $clock_ping3
			]);
		}
	}
}
}
?>