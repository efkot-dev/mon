<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$timer = date('Y-m-d H:i:s');
if (isset($confPMon['ONU_MONITOR_SIGNAL']) && !empty($confPMon['ONU_MONITOR_SIGNAL']) && $confPMon['ONU_MONITOR_SIGNAL'] == 1) {
$stmt = $pdo->query("SELECT * FROM switch WHERE monitor = 'yes' AND device = 'olt'");
$sql_list_switch = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($sql_list_switch) && count($sql_list_switch) > 0) {
    if (isset($sql_list_switch) && is_array($sql_list_switch)) {
        foreach ($sql_list_switch as $switch) {
            $switch_array[$switch['id']] = [
                'place' => $switch['place'],'id' => $switch['id'],'model' => $switch['model']
            ];
        }        
    }
	$badsignalstart = '-'.$config['badsignalstart'];
	$badsignalend = '-'.$config['badsignalend'];
	$select_signal_where = " status = '1' AND rx IS NOT NULL AND rx != '' AND rx != '0' AND rx BETWEEN " . (int)$badsignalend.".99 AND " . (int)$badsignalstart . ".00 ";
    $stmt = $pdo->query("SELECT * FROM switch_pon");
    $sqlpon = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (isset($switch_array) && isset($sqlpon) && is_array($sqlpon)) {
        $stmtBadOnu = $pdo->prepare("
            SELECT COUNT(*) AS cnt
            FROM onus
            WHERE zte_idport = :zte_idport
              AND olt = :olt
              AND {$select_signal_where}
        ");
        $stmtTempGet = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file LIMIT 1");
        $stmtNotif = $pdo->prepare("
            INSERT INTO notification (status, type, system, message, added)
            VALUES (:status, :type, :system, :message, :added)
        ");
        $stmtTempUpsert = $pdo->prepare("
            INSERT INTO tempdate (file, data, previous)
            VALUES (:file, :data, :previous)
            ON DUPLICATE KEY UPDATE
                data = VALUES(data),
                updated_at = :updated_at,
                last_processed = :last_processed
        ");

        foreach ($sqlpon as $pon) {
			$count = $pon['support'] - (isset($pon['count']) ? $pon['count'] : 0);
			$filePath = 'bad_signal_' . $pon['id'] . '';

            $stmtBadOnu->execute([
                ':zte_idport' => (int)$pon['sort'],
                ':olt' => (int)$pon['oltid'],
            ]);
            $rowBad = $stmtBadOnu->fetch(PDO::FETCH_ASSOC) ?: ['cnt' => 0];
			$count_damage = (int)$rowBad['cnt'];
			if(isset($count_damage) && $count_damage > 2) {
				$array_pon[$pon['oltid']]['pon'][$pon['id']] = array(
					'id' => $pon['id'],'pon' => $pon['pon'],
					'place' => $switch_array[$pon['oltid']]['place'],
					'olt' => $pon['oltid'],
					'bad' => $count_damage,
					'count' => $pon['count'],
					'offline' => $pon['offline'],
					'online' => $pon['online'],
					'support' => $pon['support']
				);
			}				
			$stmtTempGet->execute([':file' => (string)$filePath]);
            $currentData = (int)($stmtTempGet->fetchColumn() ?: 0);
			if(isset($currentData) && isset($count_damage)) {
				$zinet = abs($currentData - $count_damage);
				if ($currentData != $count_damage && $zinet > 2) {
					if ($count_damage > $currentData) {
						$status_signal = '[icon-up]';
						$status_lang = 'сигнали погіршилися';
						#$status_lang = 'degraded signals';
					} else {
						$status_signal = '[icon-down]';
						$status_lang = 'сигнали покращилися';
						#$status_lang = 'signals have improved';
					}
                    $sender = '[icon-warning][b]' . $pon['pon'] . '[/b] - ' . $switch_array[$pon['oltid']]['place'] . ' [b]'.$status_lang.'[/b], [b]'.$currentData.'[/b] '.$status_signal.' [b]'.$count_damage.'[/b]';
					$stmtNotif->execute([
                        ':status' => 1,
                        ':type' => 1,
                        ':system' => 'monitor',
                        ':message' => (string)$sender,
                        ':added' => (string)$timer,
                    ]);
                }
                $stmtTempUpsert->execute([
                    ':file' => (string)$filePath,
                    ':data' => (int)$count_damage,
                    ':previous' => (int)$currentData,
                    ':updated_at' => (string)$timer,
                    ':last_processed' => (string)$timer,
                ]);
            }
	    }
    }
}
}
?>
