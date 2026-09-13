<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_delet')) {
    $go->go('/?do=board');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    try {
        $pdo->beginTransaction();

        $incident_log_switch = $pdo->prepare('DELETE FROM incident_log_switch WHERE incident_id = :incident_id');
        $incident_log_switch->execute([':incident_id' => $id]);

        $incident_log_location = $pdo->prepare('DELETE FROM incident_log_location WHERE incident_id = :incident_id');
        $incident_log_location->execute([':incident_id' => $id]);

        $incident_log = $pdo->prepare('DELETE FROM incident_log WHERE incident_id = :incident_id');
        $incident_log->execute([':incident_id' => $id]);

        $incident = $pdo->prepare('DELETE FROM incident WHERE id = :id');
        $incident->execute([':id' => $id]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

$go->go('/?do=board');
exit;
?>
