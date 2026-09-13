<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}

function SendBoardTelegram($board_config, $message) {
    if (!is_array($board_config) || trim((string)$message) === '') {
        return false;
    }

    $type = isset($board_config['type']) ? (string)$board_config['type'] : 'off';
    if ($type === 'off' || $type === '') {
        return false;
    }

    $token = trim((string)($board_config['token'] ?? ''));
    if ($token === '') {
        return false;
    }

    $text = function_exists('grab_telegram') ? grab_telegram($message) : $message;

    if ($type === 'bot') {
        $uids = array_filter(array_map('trim', explode(',', (string)($board_config['userid'] ?? ''))));
        if (empty($uids)) {
            return false;
        }

        $ok = true;
        foreach ($uids as $uid) {
            $ok = boardTelegramRequest($token, [
                'chat_id' => $uid,
                'text' => $text,
                'parse_mode' => 'HTML'
            ]) && $ok;
        }
        return $ok;
    }

    if ($type === 'chat' || $type === 'groups') {
        $chatId = trim((string)($board_config['chatid'] ?? ''));
        if ($chatId === '') {
            return false;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        if ($type === 'groups') {
            $messageId = (int)($board_config['messageid'] ?? 0);
            if ($messageId > 0) {
                $payload['reply_to_message_id'] = $messageId;
            }
        }

        return boardTelegramRequest($token, $payload);
    }

    return false;
}

function boardTelegramRequest($token, $payload) {
    $apiUrl = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return check_telegram_response($response);
}

function ConfigBoardTelegram($pdo) {
    $name = 'module_border_telegram';
    $stmt = $pdo->prepare('SELECT value FROM config WHERE name = ?');
    $stmt->execute([$name]);
    $config_json = $stmt->fetchColumn();

    if (!$config_json) {
        return [];
    }

    $decoded = json_decode($config_json, true);
    return is_array($decoded) ? $decoded : [];
}

function board_telegram_bot($message, $arrayUid) {
    global $config;
    $token = trim((string)($config['telegramtoken'] ?? ''));
    if ($token === '') {
        return false;
    }

    $ok = true;
    foreach ((array)$arrayUid as $uid) {
        $uid = trim((string)$uid);
        if ($uid === '') {
            continue;
        }
        $ok = boardTelegramRequest($token, [
            'chat_id' => $uid,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]) && $ok;
    }

    return $ok;
}

function board_telegram_chat($message) {
    global $config;
    if (($config['telegram'] ?? '') !== 'on') {
        return false;
    }

    $token = trim((string)($config['telegramtoken'] ?? ''));
    $chatId = trim((string)($config['telegramchatid'] ?? ''));
    if ($token === '' || $chatId === '') {
        return false;
    }

    return boardTelegramRequest($token, [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ]);
}

function board_telegram_message($message) {
    global $config;
    if (($config['telegram'] ?? '') !== 'on') {
        return false;
    }

    $token = trim((string)($config['telegramtoken'] ?? ''));
    $chatId = trim((string)($config['telegramchatid'] ?? ''));
    $messageId = (int)($config['messageid'] ?? 0);
    if ($token === '' || $chatId === '') {
        return false;
    }

    $payload = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    if ($messageId > 0) {
        $payload['reply_to_message_id'] = $messageId;
    }

    return boardTelegramRequest($token, $payload);
}

function check_telegram_response($response) {
    if (!isset($response) || $response === false || $response === '') {
        return false;
    }

    $decoded_response = json_decode($response);
    return (isset($decoded_response->ok) && $decoded_response->ok === true);
}

function boardBuildTemplateData($pdo, $incidents_id) {
    $result = [
        'location' => '',
        'switch' => ''
    ];

    $stmt_loc = $pdo->prepare(
        'SELECT ill.*, l.name as location_name, s.name as street_name
           FROM incident_log_location ill
      LEFT JOIN location l ON ill.location_id = l.id
      LEFT JOIN location_street s ON ill.street_id = s.id
          WHERE ill.incident_id = ?
       ORDER BY ill.id ASC'
    );
    $stmt_loc->execute([$incidents_id]);
    $locations = $stmt_loc->fetchAll(PDO::FETCH_ASSOC);

    $loc_output = [];
    foreach ($locations as $loc) {
        $loc_str = (string)$loc['location_name'];
        if (!empty($loc['street_name'])) {
            $loc_str .= ' ' . shortenStreetName((string)$loc['street_name']);
        }
        if (!empty($loc['house_numbers'])) {
            $loc_str .= ' ' . (string)$loc['house_numbers'];
        }
        $loc_output[] = "\n" . trim($loc_str);
    }
    $result['location'] = trim(implode(' ', $loc_output));

    $stmt_sw = $pdo->prepare(
        'SELECT ils.*, sw.place as switch_name, swpon.pon as name_pon, COALESCE(swport.descrport, "") as descr
           FROM incident_log_switch ils
      LEFT JOIN switch sw ON ils.switch_id = sw.id
      LEFT JOIN switch_pon swpon ON ils.port_id = swpon.id
      LEFT JOIN switch_port swport ON swport.deviceid = swpon.oltid AND swport.llid = swpon.sfpid
          WHERE ils.incident_id = ?
       ORDER BY ils.id ASC'
    );
    $stmt_sw->execute([$incidents_id]);
    $switches = $stmt_sw->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($switches as $sw) {
        $switchName = trim((string)$sw['switch_name']);
        if ($switchName === '') {
            continue;
        }

        $pon = trim((string)$sw['name_pon']);
        $descr = trim((string)$sw['descr']);
        $entry = trim($pon . ' ' . $descr);

        if (!isset($grouped[$switchName])) {
            $grouped[$switchName] = [];
        }

        if ($entry !== '') {
            $grouped[$switchName][$entry] = $entry;
        }
    }

    $switchRows = [];
    foreach ($grouped as $switchName => $ports) {
        if (!empty($ports)) {
            $switchRows[] = $switchName . ' ( ' . implode(', ', array_values($ports)) . ' )';
        } else {
            $switchRows[] = $switchName;
        }
    }
    $result['switch'] = implode("\n", $switchRows);

    return $result;
}

function boardNormalizeDateRange($startRaw, $endRaw) {
    $date_start = '';
    $date_end = '';

    if (!empty($startRaw) && !empty($endRaw)) {
        try {
            $start = new DateTime($startRaw);
            $end = new DateTime($endRaw);
            if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                $date_start = $start->format('H:i');
                $date_end = $end->format('H:i');
            } else {
                $date_start = $start->format('Y-m-d H:i:s');
                $date_end = $end->format('Y-m-d H:i:s');
            }
        } catch (Throwable $e) {
            $date_start = (string)$startRaw;
            $date_end = (string)$endRaw;
        }
    }

    return [$date_start, $date_end];
}

function boardRenderTemplate($template_message, $data, $templateData) {
    list($date_start, $date_end) = boardNormalizeDateRange($data['start_time'] ?? '', $data['end_time'] ?? '');

    $replace = [
        '{name}' => (string)($data['name'] ?? ''),
        '{start_time}' => $date_start,
        '{end_time}' => $date_end,
        '{description}' => (string)($data['description'] ?? ''),
        '{cron}' => ((int)($data['cron'] ?? 0) === 1 ? 'автоматичне' : 'відповідальна особа'),
        '{location}' => (string)($templateData['location'] ?? ''),
        '{switch}' => (string)($templateData['switch'] ?? ''),
    ];

    $final_message = (string)$template_message;
    foreach ($replace as $key => $value) {
        if (trim($value) === '') {
            $final_message = preg_replace('/^.*' . preg_quote($key, '/') . '.*\n?/m', '', $final_message);
        } else {
            $final_message = str_replace($key, $value, $final_message);
        }
    }

    return trim($final_message);
}

function TemplateMessage($pdo, $data, $incidents_id, $template_message) {
    if (!$template_message) {
        $template_message = "\n[icon-warning][b]Аварія[/b] {name}\n{description}\n";
    }

    $templateData = boardBuildTemplateData($pdo, $incidents_id);
    return boardRenderTemplate($template_message, $data, $templateData);
}

function RepeatTemplateMessage($pdo, $data, $incidents_id, $template_message) {
    $templateData = boardBuildTemplateData($pdo, $incidents_id);
    $template_head = "[b]Повторне оповіщення про Аварію[/b]\n";
    return boardRenderTemplate($template_head . $template_message, $data, $templateData);
}

function shortenStreetName($name, $bbcode = false) {
    $types = [
        'Вулиця' => 'вул.',
        'Провулок' => 'пров.',
        'Площа' => 'пл.',
        'Проспект' => 'просп.',
        'Бульвар' => 'бул.',
        'Узвіз' => 'узвіз',
        'Тупик' => 'тупик',
    ];

    foreach ($types as $full => $short) {
        $replace = $bbcode ? '[b]' . $short . '[/b]' : $short;
        $pattern = '/(?<![а-яА-ЯіІїЇєЄ])' . preg_quote($full, '/') . '(?=[\s\.,]|$)/iu';
        $name = preg_replace($pattern, $replace, $name);
    }

    $name = preg_replace('/\.{2,}/', '.', $name);
    if ($bbcode) {
        $name = str_replace(['[b]', '[/b]'], '', $name);
    }

    return $name;
}

function UpdateTemplateMessage($pdo, $data, $incidents_id) {
    $template_message = "\n[icon-warning] Внесено зміни в [b]{name}[/b] :\n{switch}\n{location}\n";
    $templateData = boardBuildTemplateData($pdo, $incidents_id);
    return boardRenderTemplate($template_message, $data, $templateData);
}

function LogBoard($pdo, $incident_id, $message, $user_id, $added) {
    $stmt = $pdo->prepare('INSERT INTO incident_log (incident_id, action, user, timestamp) VALUES (?, ?, ?, ?)');
    $stmt->execute([$incident_id, $message, $user_id, $added]);
}

function loadBarTime($start, $end, $status, $bottom = '') {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $now = time();
    $total_time = $end_time - $start_time;
    $elapsed_time = $now - $start_time;

    if ($elapsed_time < 0) {
        $elapsed_time = 0;
    }
    if ($elapsed_time > $total_time) {
        $elapsed_time = $total_time;
    }

    $progress_percent = ($total_time > 0) ? ($elapsed_time / $total_time) * 100 : 0;
    $progress_percent = round($progress_percent, 2);
    $isExpired = ($now >= $end_time);

    $bar = '
        <div class="board_time_progress">
            <div class="progress_bar">
                <div class="progress_fill ' . ($isExpired ? 'progress_fill_expired' : 'progress_fill_normal') . '"
                     style="width: ' . $progress_percent . '%;"></div>
            </div>
        </div>
    ' . ($bottom == 1 ? '<div class="mb5"></div>' : '');

    if ($status === 'open') {
        return $bar;
    }

    return '';
}
?>
