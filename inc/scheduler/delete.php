<?php
if (!defined('PONMONITOR') && !defined('SCHEDULER')) {
    die('Hacking attempt!');
}
$template_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($template_id <= 0) {
    die('Invalid template ID');
}
$stmt = $pdo->prepare("SELECT id FROM templates WHERE id = ?");
$stmt->execute([$template_id]);
if (!$stmt->fetch()) {
    die('Template not found');
}
$pdo->prepare("DELETE FROM template_commands WHERE template_id = ?")->execute([$template_id]);
$pdo->prepare("DELETE FROM template_devices WHERE template_id = ?")->execute([$template_id]);
$pdo->prepare("DELETE FROM template_variables WHERE template_id = ?")->execute([$template_id]);
$pdo->prepare("DELETE FROM templates WHERE id = ?")->execute([$template_id]);
header('Location: ' . URL_SCHEDULER);
exit;
