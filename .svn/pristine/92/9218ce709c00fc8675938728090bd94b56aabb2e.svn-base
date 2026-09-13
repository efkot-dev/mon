<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if(!$access->get('board_fault_edit')) {
	$go->go('/?do=board');
	exit;	
}
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'error' => 'method_not_allowed']);
    exit;
}

$locationId = (int)($_POST['location_id'] ?? 0);
$streetName = trim((string)($_POST['new_street'] ?? ''));
if ($locationId <= 0 || $streetName === '') {
    echo json_encode(['status' => 'error', 'error' => 'bad_params']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM location_street WHERE name = :name AND locationid = :locationid LIMIT 1");
$stmt->execute(['name' => $streetName, 'locationid' => $locationId]);
$existingStreet = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existingStreet) {
    echo json_encode(['status' => 'exists', 'id' => (int)$existingStreet['id']], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO location_street (name, locationid) VALUES (?, ?)");
$stmt->execute([$streetName, $locationId]);
echo json_encode(['status' => 'added', 'id' => (int)$pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
exit;
?>
