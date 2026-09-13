<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_edit')) {
    $go->go('/?do=board');
    exit;
}

$incidents_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = isset($_POST['message']) ? (int)$_POST['message'] : 0;
if ($incidents_id <= 0) {
    $go->go('/?do=board');
    exit;
}

$portMatrix = isset($_POST['port_id']) && is_array($_POST['port_id']) ? $_POST['port_id'] : [];

if (!empty($portMatrix)) {
    try {
        $pdo->beginTransaction();

        $incidentStmt = $pdo->prepare('SELECT id, status FROM incident WHERE id = ? FOR UPDATE');
        $incidentStmt->execute([$incidents_id]);
        $incident = $incidentStmt->fetch(PDO::FETCH_ASSOC);
        if (!$incident || $incident['status'] !== 'open') {
            $pdo->rollBack();
            $go->go('/?do=board&act=view&id=' . $incidents_id);
            exit;
        }

        $checkPort = $pdo->prepare('SELECT COUNT(*) FROM incident_log_switch WHERE incident_id = ? AND switch_id = ? AND port_id = ?');
        $checkNoPort = $pdo->prepare('SELECT COUNT(*) FROM incident_log_switch WHERE incident_id = ? AND switch_id = ? AND port_id IS NULL');
        $ins = $pdo->prepare('INSERT INTO incident_log_switch (incident_id, switch_id, port_id, sorc) VALUES (?, ?, ?, ?)');

        foreach ($portMatrix as $switchIdRaw => $portsRaw) {
            $switch_id = (int)$switchIdRaw;
            if ($switch_id <= 0) {
                continue;
            }

            $ports = is_array($portsRaw) ? $portsRaw : [];
            $normalizedPorts = [];
            foreach ($ports as $portRaw) {
                $port = (int)$portRaw;
                if ($port > 0) {
                    $normalizedPorts[$port] = $port;
                }
            }
            $normalizedPorts = array_values($normalizedPorts);

            if (!empty($normalizedPorts)) {
                foreach ($normalizedPorts as $port_id) {
                    $checkPort->execute([$incidents_id, $switch_id, $port_id]);
                    if ((int)$checkPort->fetchColumn() === 0) {
                        $ins->execute([$incidents_id, $switch_id, $port_id, null]);
                    }
                }
            } else {
                $checkNoPort->execute([$incidents_id, $switch_id]);
                if ((int)$checkNoPort->fetchColumn() === 0) {
                    $ins->execute([$incidents_id, $switch_id, null, 1]);
                }
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

if ($message > 9) {
    $sql_board = $pdo->prepare('SELECT * FROM incident WHERE id = :id');
    $sql_board->execute(['id' => $incidents_id]);
    $data_board = $sql_board->fetch(PDO::FETCH_ASSOC);
    if ($data_board) {
        $data = ['name' => $data_board['reason']];
        $telegramMessage = UpdateTemplateMessage($pdo, $data, $incidents_id);
        $board_config = ConfigBoardTelegram($pdo);
        SendBoardTelegram($board_config, $telegramMessage);
    }
}

$go->go('/?do=board&act=view&id=' . $incidents_id);
exit;
?>
