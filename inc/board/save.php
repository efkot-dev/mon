<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_edit')) {
    $go->go('/?do=board');
    exit;
}

$startInput = trim((string)($_POST['start_time'] ?? ''));
$endInput = trim((string)($_POST['end_time'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$name = trim((string)($_POST['name'] ?? ''));
$cron = (int)($_POST['cron'] ?? 0);
$telegram = (int)($_POST['telegram'] ?? 0);
$messageFlag = ($telegram === 1 && $cron === 3) ? 3 : 0;

if ($name === '' || $startInput === '' || $endInput === '' || empty($USER['id'])) {
    http_response_code(422);
    echo 'missing_required_fields';
    exit;
}

try {
    $startDate = new DateTime($startInput);
    $endDate = new DateTime($endInput);
} catch (Throwable $e) {
    http_response_code(422);
    echo 'bad_datetime';
    exit;
}

$startTime = $startDate->format('Y-m-d H:i:s');
$endTime = $endDate->format('Y-m-d H:i:s');
$status = 'open';

$locationsRaw = json_decode((string)($_POST['locations'] ?? '[]'), true);
$switchesRaw = json_decode((string)($_POST['switches'] ?? '[]'), true);
$locations = is_array($locationsRaw) ? $locationsRaw : [];
$switches = is_array($switchesRaw) ? $switchesRaw : [];

$incidents_id = 0;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO incident (message, cron, start_time, description, reason, restore_time, status, created_at, user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$messageFlag, $cron, $startTime, $description, $name, $endTime, $status, $time, $USER['id']]);
    $incidents_id = (int)$pdo->lastInsertId();

    $insLocation = $pdo->prepare(
        "INSERT INTO incident_log_location (location_id, incident_id, street_id, house_numbers, sorc)
         VALUES (?, ?, ?, ?, ?)"
    );
    $seenLocations = [];

    foreach ($locations as $loc) {
        $location_id = (int)($loc['location_id'] ?? 0);
        if ($location_id <= 0) {
            continue;
        }

        $streetIdsRaw = isset($loc['street_ids']) && is_array($loc['street_ids']) ? $loc['street_ids'] : [];
        $streetIds = [];
        foreach ($streetIdsRaw as $sid) {
            $sid = (int)$sid;
            if ($sid > 0) {
                $streetIds[$sid] = $sid;
            }
        }
        $streetIds = array_values($streetIds);
        $houseNumbers = isset($loc['house_numbers']) && is_array($loc['house_numbers']) ? $loc['house_numbers'] : [];

        if (!empty($streetIds)) {
            foreach ($streetIds as $street_id) {
                $house_number = isset($houseNumbers[$street_id]) ? trim((string)$houseNumbers[$street_id]) : null;
                $key = $location_id . ':' . $street_id . ':' . (string)$house_number;
                if (isset($seenLocations[$key])) {
                    continue;
                }
                $seenLocations[$key] = true;
                $insLocation->execute([$location_id, $incidents_id, $street_id, $house_number, 0]);
            }
        } else {
            $key = $location_id . ':0:';
            if (isset($seenLocations[$key])) {
                continue;
            }
            $seenLocations[$key] = true;
            $insLocation->execute([$location_id, $incidents_id, null, null, 1]);
        }
    }

    $insSwitch = $pdo->prepare(
        "INSERT INTO incident_log_switch (incident_id, switch_id, port_id, sorc)
         VALUES (?, ?, ?, ?)"
    );
    $seenSwitches = [];

    foreach ($switches as $sw) {
        $switch_id = (int)($sw['switch_id'] ?? 0);
        if ($switch_id <= 0) {
            continue;
        }

        $portIdsRaw = isset($sw['port_ids']) && is_array($sw['port_ids']) ? $sw['port_ids'] : [];
        $portIds = [];
        foreach ($portIdsRaw as $pid) {
            $pid = (int)$pid;
            if ($pid > 0) {
                $portIds[$pid] = $pid;
            }
        }
        $portIds = array_values($portIds);

        if (!empty($portIds)) {
            foreach ($portIds as $port_id) {
                $key = $switch_id . ':' . $port_id;
                if (isset($seenSwitches[$key])) {
                    continue;
                }
                $seenSwitches[$key] = true;
                $insSwitch->execute([$incidents_id, $switch_id, $port_id, null]);
            }
        } else {
            $key = $switch_id . ':0';
            if (isset($seenSwitches[$key])) {
                continue;
            }
            $seenSwitches[$key] = true;
            $insSwitch->execute([$incidents_id, $switch_id, null, 1]);
        }
    }

    LogBoard($pdo, $incidents_id, 'Зареєстровано аварію', $USER['id'], $time);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo 'save_failed';
    exit;
}

if ($telegram === 1 && $incidents_id > 0) {
    $data = [
        'name' => $name,
        'description' => $description,
        'end_time' => $endTime,
        'start_time' => $startTime,
        'cron' => $cron
    ];
    $stmt = $pdo->prepare('SELECT value FROM config WHERE name = ?');
    $stmt->execute(['template_new_border_telegram']);
    $template_new = $stmt->fetchColumn();
    $message = TemplateMessage($pdo, $data, $incidents_id, $template_new);
    $board_config = ConfigBoardTelegram($pdo);
    SendBoardTelegram($board_config, $message);
}

echo 'ok';
exit;
?>
