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
        $id = (int)($_POST['id'] ?? 0);
        $location_id = (int)($_POST['location_id'] ?? 0);
        $street_id_raw = trim((string)($_POST['street_id'] ?? ''));
        $new_street = trim((string)($_POST['new_street'] ?? ''));
        $house_numbers = trim((string)($_POST['house_numbers'] ?? ''));
        if ($id <= 0 || $location_id <= 0) {
            echo json_encode(['ok'=>false,'error'=>'bad_params']); exit;
        }
        $pdo->beginTransaction();
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
        $s = $pdo->prepare("SELECT i.status FROM incident_log_location ill JOIN incident i ON i.id = ill.incident_id WHERE ill.id = ? FOR UPDATE");
        $s->execute([$id]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            $pdo->rollBack();
            echo json_encode(['ok'=>false,'error'=>'not_found']); exit;
        }
        if ($r['status'] !== 'open') {
            $pdo->rollBack();
            echo json_encode(['ok'=>false,'error'=>'incident_closed']); exit;
        }
        $upd = $pdo->prepare("UPDATE incident_log_location SET location_id=:loc, street_id=:st, house_numbers=:hn WHERE id=:id");
        if ($street_id === null) {
            $upd->bindValue(':st', null, PDO::PARAM_NULL);
        } else {
            $upd->bindValue(':st', (int)$street_id, PDO::PARAM_INT);
        }
        $upd->bindValue(':loc', (int)$location_id, PDO::PARAM_INT);
        $upd->bindValue(':hn', $house_numbers, PDO::PARAM_STR);
        $upd->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $upd->execute();
        $pdo->commit();
        echo json_encode(['ok'=>true,'street_id'=>$street_id]); exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['ok'=>false,'error'=>'server_error']); exit;
    }
}
exit;
?>
