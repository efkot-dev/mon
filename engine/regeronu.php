<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR . '/inc/init.core.php';
require ROOT_DIR . '/inc/functions/sql_pdo.php';
if (isset($confPMon['TEMPLATE_REGISTER']) && !empty($confPMon['TEMPLATE_REGISTER']) && $confPMon['TEMPLATE_REGISTER'] == 1) {
    $query = "SELECT * FROM switch_temp WHERE created_note = :created_note AND status = :status";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':created_note' => 'module_register',':status'=> 2]);
    $temp_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($temp_list as $ont) {
        $temp_ont = unserialize($ont['tempdata']);
		if (empty($temp_ont['zte_idport'])) {
			$inface = "{$temp_ont['sw_shelf']}/{$temp_ont['sw_slot']}/{$temp_ont['sw_port']}";
			$pattern = '(^| )' . preg_quote($inface, '/') . '($| )';
			$query = "SELECT * FROM switch_pon WHERE oltid = :oltid AND pon REGEXP :pattern LIMIT 1";
			$stmt = $pdo->prepare($query);
			$stmt->execute([
				':pattern' => $pattern,
				':oltid' => $temp_ont['olt']
			]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!empty($result['sfpid'])){
				$temp_ont['zte_idport'] = $result['sfpid'];
			}
		}		
        $onu = pdoFast($pdo, 'onus', 'idonu', [
            'olt'       => $temp_ont['olt'],
            'sw_shelf'  => $temp_ont['sw_shelf'],
            'sw_slot'   => $temp_ont['sw_slot'],
            'sw_port'   => $temp_ont['sw_port'],
            'keyonu'    => $temp_ont['keyonu']
        ]);
        if (empty($onu['idonu'])) {
			pdoInsert($pdo, 'onus', $temp_ont);
        }
		$updateQuery = "UPDATE switch_temp SET status = 1 WHERE id = :id";
		$updateStmt = $pdo->prepare($updateQuery);
		$updateStmt->execute([':id' => $ont['id']]);
    }
}
exit;
?>