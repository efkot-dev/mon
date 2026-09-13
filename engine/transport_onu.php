<?php
if (!defined('PONMONITOR')) { 
	define('PONMONITOR', 1); 
}
require_once ROOT_DIR . '/inc/init.monitor.php';
$stmt = $pdo->prepare("SELECT DISTINCT idonu FROM transport_onu WHERE idonu IS NOT NULL");
$stmt->execute();
$idRows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
if (!$idRows) {
    exit(0);
}
$idRows = array_values(array_filter(array_map('intval', $idRows), fn($v) => $v > 0));
if (!$idRows) {
    exit(0);
}
$chunks  = array_chunk($idRows, 500);
$updated = 0;
foreach ($chunks as $chunk) {
	$placeholders = implode(',', array_fill(0, count($chunk), '?'));
	$sqlFetch = "SELECT idonu, status FROM onus WHERE idonu IN ($placeholders)";
	$fetchStmt = $pdo->prepare($sqlFetch);
	foreach ($chunk as $i => $val) {
		$fetchStmt->bindValue($i + 1, (int)$val, PDO::PARAM_INT);
	}
	$fetchStmt->execute();
	$rows = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);
	if (!$rows) {
		continue;
	}
	$onlineIds  = [];
	$offlineIds = [];
	foreach ($rows as $r) {
		$id = (int)$r['idonu'];
            if ($id <= 0) continue;
            if ((int)$r['status'] === 1) {
                $onlineIds[] = $id;
            } else {
                $offlineIds[] = $id;
            }
	}
	if ($onlineIds) {
		$ph = implode(',', array_fill(0, count($onlineIds), '?'));
		$sqlUp = "UPDATE transport_onu SET status = 'online' WHERE idonu IN ($ph)";
		$upStmt = $pdo->prepare($sqlUp);
            foreach ($onlineIds as $i => $val) {
                $upStmt->bindValue($i + 1, (int)$val, PDO::PARAM_INT);
            }
		$upStmt->execute();
		$updated += $upStmt->rowCount();
	}
	if ($offlineIds) {
		$ph = implode(',', array_fill(0, count($offlineIds), '?'));
		$sqlUp = "UPDATE transport_onu SET status = 'offline' WHERE idonu IN ($ph)";
		$upStmt = $pdo->prepare($sqlUp);
		foreach ($offlineIds as $i => $val) {
			$upStmt->bindValue($i + 1, (int)$val, PDO::PARAM_INT);
		}
		$upStmt->execute();
		$updated += $upStmt->rowCount();
	}
}
?>

