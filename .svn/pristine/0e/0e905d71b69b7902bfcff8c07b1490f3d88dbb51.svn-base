<?php
if (!defined('SCHEDULER')) {
	die('Hacking attempt!');
}
function getAllTemplates(PDO $pdo): array {
	$stmt = $pdo->prepare("
		SELECT t.*, g.name AS group_name 
		FROM templates t 
		LEFT JOIN template_groups g ON t.group_id = g.id
	");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getTemplate(PDO $pdo, int $id): ?array {
	$stmt = $pdo->prepare("SELECT * FROM templates WHERE id = ?");
	$stmt->execute([$id]);
	return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getOLTData(PDO $pdo, int $id): ?array {
	$stmt = $pdo->prepare("SELECT * FROM switch WHERE id = ?");
	$stmt->execute([$id]);
	return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getTemplateCommands(PDO $pdo, int $templateId): array {
	$stmt = $pdo->prepare("SELECT * FROM template_commands WHERE template_id = ? ORDER BY sort_order");
	$stmt->execute([$templateId]);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getTemplateDevices(PDO $pdo, int $templateId): array {
	$stmt = $pdo->prepare("SELECT device_id FROM template_devices WHERE template_id = ?");
	$stmt->execute([$templateId]);
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
function deleteTemplate(PDO $pdo, int $id): void {
	$pdo->beginTransaction();
	$stmt1 = $pdo->prepare("DELETE FROM template_commands WHERE template_id = ?");
	$stmt1->execute([$id]);
	$stmt2 = $pdo->prepare("DELETE FROM template_devices WHERE template_id = ?");
	$stmt2->execute([$id]);
	$stmt3 = $pdo->prepare("DELETE FROM templates WHERE id = ?");
	$stmt3->execute([$id]);
	$pdo->commit();
}
?>
