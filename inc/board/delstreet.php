<?php
if (!defined('PONMONITOR') && !defined('BOARD')) { die('Hacking attempt!'); }
header('Content-Type: application/json; charset=utf-8');
$incident_id = isset($_POST['incident_id']) ? (int)$_POST['incident_id'] : 0;
$ill_id = isset($_POST['ill_id']) ? (int)$_POST['ill_id'] : 0;
if ($incident_id <= 0 || $ill_id <= 0) {
    echo json_encode(['ok'=>0,'err'=>'bad_params']); exit;
}
try {
    $stmt = $pdo->prepare("DELETE FROM incident_log_location WHERE id = ? AND incident_id = ?");
    $stmt->execute([$ill_id, $incident_id]);
    echo json_encode(['ok'=>1]);
} catch (Exception $e) {
    echo json_encode(['ok'=>0,'err'=>'sql']);
}
?>