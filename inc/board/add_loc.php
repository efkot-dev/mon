<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if(!$access->get('board_fault_edit')) {
	$go->go('/?do=board');
	exit;	
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['location_id'])) {
	header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$access->get('board_fault_edit')) {
            echo json_encode(['ok'=>false,'error'=>'access_denied']); exit;
        }
        $incident_id = (int)($_POST['incident_id'] ?? 0);
        $location_id = (int)($_POST['location_id'] ?? 0);
        $street_id_raw = trim((string)($_POST['street_id'] ?? ''));
        $new_street = trim((string)($_POST['new_street'] ?? ''));
        $house_numbers = trim((string)($_POST['house_numbers'] ?? ''));
        if ($incident_id <= 0 || $location_id <= 0) {
            echo json_encode(['ok'=>false,'error'=>'bad_params']); exit;
        }
        $pdo->beginTransaction();
        $s = $pdo->prepare("SELECT status FROM incident WHERE id=? FOR UPDATE");
        $s->execute([$incident_id]);
        $inc = $s->fetch(PDO::FETCH_ASSOC);
        if (!$inc) {
            $pdo->rollBack();
            echo json_encode(['ok'=>false,'error'=>'incident_not_found']); exit;
        }
        if ($inc['status'] !== 'open') {
            $pdo->rollBack();
            echo json_encode(['ok'=>false,'error'=>'incident_closed']); exit;
        }
        $street_id = null;
        if ($new_street !== '') {
            $street = $pdo->prepare("SELECT id FROM location_street WHERE locationid = :loc AND name = :name LIMIT 1");
            $street->execute(['loc' => $location_id, 'name' => $new_street]);
            $streetId = (int)$street->fetchColumn();
            if ($streetId > 0) {
                $street_id = $streetId;
            } else {
                $ins = $pdo->prepare("INSERT INTO location_street (locationid, name) VALUES (:loc, :name)");
                $ins->execute(['loc'=>$location_id, 'name'=>$new_street]);
                $street_id = (int)$pdo->lastInsertId();
            }
        } elseif ($street_id_raw !== '' && ctype_digit($street_id_raw)) {
            $street_id = (int)$street_id_raw;
        } else {
            $street_id = null;
        }
        $ins2 = $pdo->prepare("INSERT INTO incident_log_location (incident_id, location_id, street_id, house_numbers, sorc) VALUES (:iid,:loc,:st,:hn,:sorc)");
        $ins2->bindValue(':iid', (int)$incident_id, PDO::PARAM_INT);
        $ins2->bindValue(':loc', (int)$location_id, PDO::PARAM_INT);
        if ($street_id === null) {
            $ins2->bindValue(':st', null, PDO::PARAM_NULL);
        } else {
            $ins2->bindValue(':st', (int)$street_id, PDO::PARAM_INT);
        }
        $ins2->bindValue(':hn', $house_numbers, PDO::PARAM_STR);
        $ins2->bindValue(':sorc', $street_id === null ? 1 : 0, PDO::PARAM_INT);
        $ins2->execute();
        $new_id = (int)$pdo->lastInsertId();
        $locStmt = $pdo->prepare("SELECT name FROM location WHERE id=?");
        $locStmt->execute([$location_id]);
        $locName = (string)($locStmt->fetchColumn() ?: '');
        $strName = '';
        if ($street_id !== null) {
            $stStmt = $pdo->prepare("SELECT name FROM location_street WHERE id=?");
            $stStmt->execute([$street_id]);
            $strName = (string)($stStmt->fetchColumn() ?: '');
        }
        $pdo->commit();
        echo json_encode([
            'ok'=>true,
            'id'=>$new_id,
            'location_id'=>$location_id,
            'street_id'=>$street_id, 
            'house_numbers'=>$house_numbers,
            'location_name'=>$locName,
            'street_name'=>$strName 
        ]); exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['ok'=>false,'error'=>'server_error']); exit;
    }
}
die;
?>
