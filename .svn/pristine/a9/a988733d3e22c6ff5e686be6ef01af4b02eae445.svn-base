<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

define('VIDEO', true);
define('MODULE_VIDEO', ROOT_DIR . '/inc/surveillance/');
define('URL_VIDEO', '/?do=surveillance');

$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
$url_video = URL_VIDEO;
$actCurrent = isset($act) ? (string)$act : (string)($_GET['act'] ?? '');
$content = '';
$metatags = [];

if (!function_exists('surveillance_h')) {
    function surveillance_h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('surveillance_l')) {
    function surveillance_l($lang, string $key, string $fallback): string
    {
        $value = null;

        if (is_array($lang) && array_key_exists($key, $lang)) {
            $value = $lang[$key];
        } elseif ($lang instanceof ArrayAccess && isset($lang[$key])) {
            $value = $lang[$key];
        } elseif (is_object($lang)) {
            if (isset($lang->$key)) {
                $value = $lang->$key;
            } elseif (method_exists($lang, 'get')) {
                try {
                    $value = $lang->get($key);
                } catch (Throwable $e) {
                    $value = null;
                }
            }
        }

        $value = trim((string)$value);
        return $value !== '' ? $value : $fallback;
    }
}

if (!function_exists('surveillance_columns')) {
    function surveillance_columns(PDO $pdo, string $table): array
    {
        static $cache = [];
        $table = trim($table);
        if ($table === '') {
            return [];
        }
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $result = [];
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `" . str_replace('`', '', $table) . "`");
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($row['Field'])) {
                        $result[strtolower((string)$row['Field'])] = (string)$row['Field'];
                    }
                }
            }
        } catch (Throwable $e) {
            $result = [];
        }

        $cache[$table] = $result;
        return $result;
    }
}

if (!function_exists('surveillance_has_column')) {
    function surveillance_has_column(PDO $pdo, string $table, string $column): bool
    {
        $cols = surveillance_columns($pdo, $table);
        return isset($cols[strtolower($column)]);
    }
}

if (!function_exists('surveillance_real_column')) {
    function surveillance_real_column(PDO $pdo, string $table, string $column): ?string
    {
        $cols = surveillance_columns($pdo, $table);
        $key = strtolower($column);
        return $cols[$key] ?? null;
    }
}

if (!function_exists('surveillance_detect_onu_binding_column')) {
    function surveillance_detect_onu_binding_column(PDO $pdo): ?string
    {
        foreach (['idonu', 'onu_id', 'onuid'] as $candidate) {
            $real = surveillance_real_column($pdo, 'surveillance', $candidate);
            if ($real !== null) {
                return $real;
            }
        }
        return null;
    }
}

if (!function_exists('surveillance_detect_onu_name_column')) {
    function surveillance_detect_onu_name_column(PDO $pdo): ?string
    {
        foreach (['name', 'description', 'descr', 'abon_name', 'fio'] as $candidate) {
            $real = surveillance_real_column($pdo, 'onus', $candidate);
            if ($real !== null) {
                return $real;
            }
        }
        return null;
    }
}

if (!function_exists('surveillance_parse_netip')) {
    function surveillance_parse_netip(string $raw): array
    {
        $value = trim($raw);
        if ($value === '') {
            return ['ok' => false, 'error' => 'IP не вказано'];
        }

        if (substr_count($value, ':') === 1) {
            [$ip, $portRaw] = explode(':', $value, 2);
            $ip = trim($ip);
            $portRaw = trim($portRaw);
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return ['ok' => false, 'error' => 'Некоректна IP-адреса'];
            }
            if (!ctype_digit($portRaw)) {
                return ['ok' => false, 'error' => 'Некоректний порт'];
            }
            $port = (int)$portRaw;
            if ($port < 1 || $port > 65535) {
                return ['ok' => false, 'error' => 'Порт має бути в межах 1..65535'];
            }
            return ['ok' => true, 'netip' => $ip . ':' . $port, 'ip' => $ip, 'port' => $port];
        }

        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            return ['ok' => false, 'error' => 'Некоректна IP-адреса'];
        }

        return ['ok' => true, 'netip' => $value, 'ip' => $value, 'port' => null];
    }
}

if (!function_exists('surveillance_trim_text')) {
    function surveillance_trim_text($value, int $maxLen = 255): string
    {
        $v = trim(strip_tags((string)$value));
        if (function_exists('mb_substr')) {
            return mb_substr($v, 0, $maxLen);
        }
        return substr($v, 0, $maxLen);
    }
}

if (!function_exists('surveillance_fetch_groups')) {
    function surveillance_fetch_groups(PDO $pdo): array
    {
        $rows = [];
        try {
            $stmt = $pdo->query("SELECT id, name FROM groups WHERE group_types = 3 ORDER BY name ASC");
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $rows = [];
        }
        return is_array($rows) ? $rows : [];
    }
}

if (!function_exists('surveillance_fetch_locations')) {
    function surveillance_fetch_locations(PDO $pdo): array
    {
        if (!surveillance_has_column($pdo, 'location', 'id')) {
            return [];
        }

        $rows = [];
        try {
            $stmt = $pdo->query("SELECT id, name FROM location ORDER BY name ASC");
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $rows = [];
        }
        return is_array($rows) ? $rows : [];
    }
}

if (!function_exists('surveillance_fetch_onu_list')) {
    function surveillance_fetch_onu_list(PDO $pdo, int $limit = 400): array
    {
        if (!surveillance_has_column($pdo, 'onus', 'idonu')) {
            return [];
        }

        $limit = max(50, min($limit, 1200));
        $nameCol = surveillance_detect_onu_name_column($pdo);
        $select = "idonu";
        if (surveillance_has_column($pdo, 'onus', 'inface')) {
            $select .= ", inface";
        }
        if (surveillance_has_column($pdo, 'onus', 'status')) {
            $select .= ", status";
        }
        if (surveillance_has_column($pdo, 'onus', 'rx')) {
            $select .= ", rx";
        }
        if ($nameCol !== null) {
            $select .= ", `{$nameCol}` AS onu_name";
        }

        $sql = "SELECT {$select} FROM onus ORDER BY idonu DESC LIMIT {$limit}";
        try {
            $stmt = $pdo->query($sql);
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            return is_array($rows) ? $rows : [];
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('surveillance_fetch_onu_by_id')) {
    function surveillance_fetch_onu_by_id(PDO $pdo, int $idonu): ?array
    {
        if ($idonu <= 0 || !surveillance_has_column($pdo, 'onus', 'idonu')) {
            return null;
        }

        $nameCol = surveillance_detect_onu_name_column($pdo);
        $select = "idonu";
        if (surveillance_has_column($pdo, 'onus', 'inface')) {
            $select .= ", inface";
        }
        if (surveillance_has_column($pdo, 'onus', 'status')) {
            $select .= ", status";
        }
        if (surveillance_has_column($pdo, 'onus', 'rx')) {
            $select .= ", rx";
        }
        if ($nameCol !== null) {
            $select .= ", `{$nameCol}` AS onu_name";
        }

        $sql = "SELECT {$select} FROM onus WHERE idonu = :id LIMIT 1";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $idonu]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('surveillance_flash')) {
    function surveillance_flash(): string
    {
        $msg = isset($_GET['msg']) ? trim((string)$_GET['msg']) : '';
        $err = isset($_GET['err']) ? trim((string)$_GET['err']) : '';

        $html = '';
        if ($msg !== '') {
            $html .= '<div class="signal3" style="margin-bottom:10px;">' . surveillance_h($msg) . '</div>';
        }
        if ($err !== '') {
            $html .= '<div class="signal1" style="margin-bottom:10px;">' . surveillance_h($err) . '</div>';
        }
        return $html;
    }
}

if (!function_exists('surveillance_device_type_map')) {
    function surveillance_device_type_map(): array
    {
        return [
            1 => 'Hikvision NVR',
            2 => 'Hikvision IP camera',
            3 => 'Dahua NVR',
            4 => 'Dahua IP camera',
        ];
    }
}

if (!function_exists('surveillance_validate_payload')) {
    function surveillance_validate_payload(PDO $pdo, array $post, ?int $excludeId = null): array
    {
        $types = surveillance_device_type_map();
        $oidid = isset($post['oidid']) ? (int)$post['oidid'] : 0;
        if (!isset($types[$oidid])) {
            return [false, [], 'Оберіть тип пристрою'];
        }

        $monitor = (isset($post['monitor']) && $post['monitor'] === 'no') ? 'no' : 'yes';

        $name = surveillance_trim_text($post['name'] ?? '', 190);
        if ($name === '') {
            return [false, [], 'Вкажіть назву/опис пристрою'];
        }

        $snmpro = surveillance_trim_text($post['snmpro'] ?? '', 190);
        $parsedIp = surveillance_parse_netip((string)($post['netip'] ?? ''));
        if (empty($parsedIp['ok'])) {
            return [false, [], (string)($parsedIp['error'] ?? 'Некоректна IP-адреса')];
        }

        $group = isset($post['group']) ? (int)$post['group'] : 0;
        $location = isset($post['location']) ? (int)$post['location'] : 0;
        if ($group < 0) {
            $group = 0;
        }
        if ($location < 0) {
            $location = 0;
        }

        $dupSql = "SELECT id FROM surveillance WHERE netip = :netip";
        $dupParams = [':netip' => $parsedIp['netip']];
        if (!empty($excludeId)) {
            $dupSql .= " AND id <> :id";
            $dupParams[':id'] = (int)$excludeId;
        }
        $dupSql .= " LIMIT 1";

        try {
            $dup = $pdo->prepare($dupSql);
            $dup->execute($dupParams);
            if ($dup->fetchColumn()) {
                return [false, [], 'Пристрій з такою IP-адресою вже існує'];
            }
        } catch (Throwable $e) {
            // no-op
        }

        $data = [
            'monitor' => $monitor,
            'oidid' => $oidid,
            'netip' => $parsedIp['netip'],
            'snmpro' => $snmpro,
            'place' => $name,
            'groups' => $group,
            'locations' => $location,
        ];

        $bindCol = surveillance_detect_onu_binding_column($pdo);
        if ($bindCol !== null) {
            $bindOnu = isset($post['bind_onu']) ? (int)$post['bind_onu'] : 0;
            if ($bindOnu < 0) {
                $bindOnu = 0;
            }
            if ($bindOnu > 0) {
                $onu = surveillance_fetch_onu_by_id($pdo, $bindOnu);
                if ($onu === null) {
                    return [false, [], 'ONU з таким ID не знайдено'];
                }
                $data[$bindCol] = $bindOnu;
            } else {
                $data[$bindCol] = 0;
            }
        }

        return [true, $data, ''];
    }
}

if (!function_exists('surveillance_render_form')) {
    function surveillance_render_form(array $params): array
    {
        $lang = $params['lang'];
        $url = $params['url'];
        $mode = $params['mode'];
        $values = $params['values'];
        $groups = $params['groups'];
        $locations = $params['locations'];
        $onuOptions = $params['onuOptions'];
        $bindCol = $params['bindCol'];

        $types = surveillance_device_type_map();
        $oidid = (int)($values['oidid'] ?? 1);
        $monitor = ($values['monitor'] ?? 'yes') === 'no' ? 'no' : 'yes';
        $groupCurrent = (int)($values['groups'] ?? 0);
        $locationCurrent = (int)($values['locations'] ?? 0);
        $boundOnuCurrent = 0;
        if ($bindCol !== null && isset($values[$bindCol])) {
            $boundOnuCurrent = (int)$values[$bindCol];
        }

        $titleModule = 'Відеоспостереження';
        $titlePage = $mode === 'edit' ? 'Редагування пристрою' : 'Новий пристрій';

        $speedbar = '';
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . surveillance_h(surveillance_l($lang, 'main', 'Головна')) . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=surveillance"><i class="fi fi-rr-angle-left"></i>' . surveillance_h($titleModule) . '</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . surveillance_h($titlePage) . '</span>';

        $formId = $mode === 'edit' ? 'form_surveillance_edit' : 'form_surveillance_add';
        $actName = $mode === 'edit' ? 'update' : 'save';

        $typeSelect = '<select class="select" name="oidid">';
        foreach ($types as $k => $label) {
            $sel = ((int)$k === $oidid) ? ' selected' : '';
            $typeSelect .= '<option value="' . (int)$k . '"' . $sel . '>' . surveillance_h($label) . '</option>';
        }
        $typeSelect .= '</select>';

        $monitorSelect = '<select class="select" name="monitor">';
        $monitorSelect .= '<option value="yes"' . ($monitor === 'yes' ? ' selected' : '') . '>' . surveillance_h(surveillance_l($lang, 'monitor_on', 'Моніторинг увімкнено')) . '</option>';
        $monitorSelect .= '<option value="no"' . ($monitor === 'no' ? ' selected' : '') . '>' . surveillance_h(surveillance_l($lang, 'monitor_off', 'Моніторинг вимкнено')) . '</option>';
        $monitorSelect .= '</select>';

        $result = '';
        $result .= '<div class="card"><form action="' . surveillance_h($url) . '" method="post" id="' . surveillance_h($formId) . '">';
        $result .= '<input name="act" type="hidden" value="' . surveillance_h($actName) . '">';
        if ($mode === 'edit') {
            $result .= '<input name="id" type="hidden" value="' . (int)($values['id'] ?? 0) . '">';
        }

        $result .= formpage(['img' => 'addconnect.png', 'name' => 'Device', 'descr' => 'Select', 'pole' => $typeSelect]);
        $result .= formpage(['img' => 'addconnect.png', 'name' => surveillance_l($lang, 'monitor_on', 'Моніторинг'), 'descr' => surveillance_l($lang, 'monitor_device', 'Моніторинг пристрою'), 'pole' => $monitorSelect]);

        $result .= formpage([
            'img' => 'addconnect.png',
            'name' => surveillance_l($lang, 'ip', 'IP'),
            'descr' => surveillance_l($lang, 'ipdescr', 'IP адреса або IP:порт'),
            'pole' => '<input style="width:33%;" name="netip" class="input1" type="text" value="' . surveillance_h($values['netip'] ?? '') . '">'
        ]);

        $result .= formpage([
            'img' => 'addconnect.png',
            'name' => 'Community',
            'descr' => 'SNMP community',
            'pole' => '<input style="width:33%;" name="snmpro" class="input1" type="text" value="' . surveillance_h($values['snmpro'] ?? '') . '">'
        ]);

        $result .= formpage([
            'img' => 'addconnect.png',
            'name' => surveillance_l($lang, 'oid_gpon_name', 'Назва'),
            'descr' => surveillance_l($lang, 'oid_gpon_name_desc', 'Опис або назва точки'),
            'pole' => '<input style="width:99%;" name="name" class="input1" type="text" value="' . surveillance_h($values['place'] ?? '') . '">'
        ]);

        if (!empty($groups)) {
            $groupSelect = '<select class="select" name="group" id="group"><option value="0"></option>';
            foreach ($groups as $group) {
                $gid = (int)($group['id'] ?? 0);
                $gname = (string)($group['name'] ?? '');
                $sel = $gid === $groupCurrent ? ' selected' : '';
                $groupSelect .= '<option value="' . $gid . '"' . $sel . '>' . surveillance_h($gname) . '</option>';
            }
            $groupSelect .= '</select>';
            $result .= formpage(['img' => 'folders.png', 'name' => surveillance_l($lang, 'group', 'Група'), 'descr' => surveillance_l($lang, 'title_group', 'Групування пристрою'), 'pole' => $groupSelect]);
        }

        if (!empty($locations)) {
            $locationSelect = '<select class="select" name="location" id="location"><option value="0"></option>';
            foreach ($locations as $location) {
                $lid = (int)($location['id'] ?? 0);
                $lname = (string)($location['name'] ?? '');
                $sel = $lid === $locationCurrent ? ' selected' : '';
                $locationSelect .= '<option value="' . $lid . '"' . $sel . '>' . surveillance_h($lname) . '</option>';
            }
            $locationSelect .= '</select>';
            $result .= formpage(['img' => 'm6.png', 'name' => surveillance_l($lang, 'location', 'Локація'), 'descr' => surveillance_l($lang, 'getlocation', 'Оберіть локацію'), 'pole' => $locationSelect]);
        }

        if ($bindCol !== null) {
            $bindInput = '<input list="surveillance_onu_list" name="bind_onu" class="input1" style="width:33%;" type="number" min="0" value="' . (int)$boundOnuCurrent . '">';
            if (!empty($onuOptions)) {
                $bindInput .= '<datalist id="surveillance_onu_list">';
                foreach ($onuOptions as $onu) {
                    $idonu = (int)($onu['idonu'] ?? 0);
                    if ($idonu <= 0) {
                        continue;
                    }
                    $labelParts = [];
                    if (!empty($onu['inface'])) {
                        $labelParts[] = $onu['inface'];
                    }
                    if (isset($onu['status'])) {
                        $labelParts[] = ((int)$onu['status'] === 1 ? 'online' : 'offline');
                    }
                    if (isset($onu['rx']) && $onu['rx'] !== '') {
                        $labelParts[] = 'RX ' . $onu['rx'];
                    }
                    if (!empty($onu['onu_name'])) {
                        $labelParts[] = (string)$onu['onu_name'];
                    }
                    $bindInput .= '<option value="' . $idonu . '">' . surveillance_h(implode(' | ', $labelParts)) . '</option>';
                }
                $bindInput .= '</datalist>';
            }
            $result .= formpage([
                'img' => 'addconnect.png',
                'name' => 'ONU ID',
                'descr' => 'Прив\'язка камери до ONU (необов\'язково)',
                'pole' => $bindInput,
            ]);
        }

        $result .= '</form></div>';
        $result .= '<div class="polebtn">';
        $result .= '<button type="submit" form="' . surveillance_h($formId) . '" value="submit">' . surveillance_h(surveillance_l($lang, 'save', 'Зберегти')) . '</button>';
        $result .= '<a class="knopkagreen" href="/?do=surveillance" style="margin-left:8px;">' . surveillance_h(surveillance_l($lang, 'cancel', 'Скасувати')) . '</a>';
        $result .= '</div>';

        $content = '<div id="onu-speedbar">' . $speedbar . '</div>';
        $content .= surveillance_flash();
        $content .= $result;

        $metatags = [
            'title' => $titleModule,
            'description' => $titleModule,
            'page' => 'board_main',
        ];

        return ['content' => $content, 'metatags' => $metatags];
    }
}

switch ($actCurrent) {
    case 'config':
        require MODULE_VIDEO . 'config.php';
        break;

    case 'add':
        $defaultValues = [
            'oidid' => 1,
            'monitor' => 'yes',
            'netip' => '',
            'snmpro' => 'public',
            'place' => '',
            'groups' => 0,
            'locations' => 0,
        ];
        $bindCol = surveillance_detect_onu_binding_column($pdo);
        if ($bindCol !== null) {
            $defaultValues[$bindCol] = 0;
        }

        $render = surveillance_render_form([
            'lang' => $lang,
            'url' => $url_video,
            'mode' => 'add',
            'values' => $defaultValues,
            'groups' => surveillance_fetch_groups($pdo),
            'locations' => surveillance_fetch_locations($pdo),
            'onuOptions' => surveillance_fetch_onu_list($pdo),
            'bindCol' => $bindCol,
        ]);

        $content = $render['content'];
        $metatags = $render['metatags'];
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $go->redirect('/?do=surveillance&act=add&err=' . urlencode('Некоректний запит'));
            break;
        }

        [$ok, $payload, $error] = surveillance_validate_payload($pdo, $_POST);
        if (!$ok) {
            $go->redirect('/?do=surveillance&act=add&err=' . urlencode($error));
            break;
        }

        $survCols = surveillance_columns($pdo, 'surveillance');
        $insert = [];
        foreach (['locations', 'groups', 'monitor', 'oidid', 'netip', 'snmpro', 'place'] as $col) {
            if (isset($survCols[strtolower($col)]) && isset($payload[$col])) {
                $insert[$survCols[strtolower($col)]] = $payload[$col];
            }
        }

        $bindCol = surveillance_detect_onu_binding_column($pdo);
        if ($bindCol !== null && isset($payload[$bindCol])) {
            $insert[$bindCol] = $payload[$bindCol];
        }
        if (isset($survCols['status'])) {
            $insert[$survCols['status']] = 1;
        }

        if (empty($insert)) {
            $go->redirect('/?do=surveillance&act=add&err=' . urlencode('Немає даних для збереження'));
            break;
        }

        $fields = array_keys($insert);
        $placeholders = [];
        $params = [];
        foreach ($fields as $f) {
            $ph = ':v_' . preg_replace('/[^a-z0-9_]/i', '_', $f);
            $placeholders[] = $ph;
            $params[$ph] = $insert[$f];
        }

        try {
            $sql = "INSERT INTO surveillance (`" . implode('`,`', $fields) . "`) VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $newId = (int)$pdo->lastInsertId();
            if ($newId > 0) {
                $go->redirect('/?do=surveillance&act=view&id=' . $newId . '&msg=' . urlencode('Пристрій додано'));
            } else {
                $go->redirect('/?do=surveillance&msg=' . urlencode('Пристрій додано'));
            }
        } catch (Throwable $e) {
            $go->redirect('/?do=surveillance&act=add&err=' . urlencode('Помилка збереження: ' . $e->getMessage()));
        }
        break;

    case 'edit':
        if ($id <= 0) {
            $go->redirect('/?do=surveillance');
            break;
        }

        $stmt = $pdo->prepare("SELECT * FROM surveillance WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device) {
            $go->redirect('/?do=surveillance&err=' . urlencode('Пристрій не знайдено'));
            break;
        }

        $bindCol = surveillance_detect_onu_binding_column($pdo);
        $render = surveillance_render_form([
            'lang' => $lang,
            'url' => $url_video,
            'mode' => 'edit',
            'values' => $device,
            'groups' => surveillance_fetch_groups($pdo),
            'locations' => surveillance_fetch_locations($pdo),
            'onuOptions' => surveillance_fetch_onu_list($pdo),
            'bindCol' => $bindCol,
        ]);

        $content = $render['content'];
        $metatags = $render['metatags'];
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $go->redirect('/?do=surveillance&err=' . urlencode('Некоректний запит'));
            break;
        }

        $updateId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($updateId <= 0) {
            $go->redirect('/?do=surveillance&err=' . urlencode('Некоректний ID'));
            break;
        }

        $exists = $pdo->prepare("SELECT id FROM surveillance WHERE id = :id LIMIT 1");
        $exists->execute([':id' => $updateId]);
        if (!$exists->fetchColumn()) {
            $go->redirect('/?do=surveillance&err=' . urlencode('Пристрій не знайдено'));
            break;
        }

        [$ok, $payload, $error] = surveillance_validate_payload($pdo, $_POST, $updateId);
        if (!$ok) {
            $go->redirect('/?do=surveillance&act=edit&id=' . $updateId . '&err=' . urlencode($error));
            break;
        }

        $survCols = surveillance_columns($pdo, 'surveillance');
        $update = [];
        foreach (['locations', 'groups', 'monitor', 'oidid', 'netip', 'snmpro', 'place'] as $col) {
            if (isset($survCols[strtolower($col)]) && isset($payload[$col])) {
                $update[$survCols[strtolower($col)]] = $payload[$col];
            }
        }

        $bindCol = surveillance_detect_onu_binding_column($pdo);
        if ($bindCol !== null && isset($payload[$bindCol])) {
            $update[$bindCol] = $payload[$bindCol];
        }

        if (empty($update)) {
            $go->redirect('/?do=surveillance&act=edit&id=' . $updateId . '&err=' . urlencode('Немає змін для збереження'));
            break;
        }

        $setParts = [];
        $params = [':id' => $updateId];
        foreach ($update as $field => $value) {
            $ph = ':v_' . preg_replace('/[^a-z0-9_]/i', '_', $field);
            $setParts[] = "`{$field}` = {$ph}";
            $params[$ph] = $value;
        }

        try {
            $sql = "UPDATE surveillance SET " . implode(', ', $setParts) . " WHERE id = :id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $go->redirect('/?do=surveillance&act=view&id=' . $updateId . '&msg=' . urlencode('Зміни збережено'));
        } catch (Throwable $e) {
            $go->redirect('/?do=surveillance&act=edit&id=' . $updateId . '&err=' . urlencode('Помилка оновлення: ' . $e->getMessage()));
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $go->redirect('/?do=surveillance&err=' . urlencode('Видалення доступне тільки через POST'));
            break;
        }

        $deleteId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($deleteId <= 0) {
            $go->redirect('/?do=surveillance&err=' . urlencode('Некоректний ID для видалення'));
            break;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM surveillance WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $deleteId]);
            if ($stmt->rowCount() > 0) {
                $go->redirect('/?do=surveillance&msg=' . urlencode('Пристрій видалено'));
            } else {
                $go->redirect('/?do=surveillance&err=' . urlencode('Пристрій не знайдено'));
            }
        } catch (Throwable $e) {
            $go->redirect('/?do=surveillance&err=' . urlencode('Помилка видалення: ' . $e->getMessage()));
        }
        break;

    case 'view':
        if ($id <= 0) {
            $go->redirect('/?do=surveillance');
            break;
        }

        $bindCol = surveillance_detect_onu_binding_column($pdo);
        $bindJoin = '';
        $bindSelect = '';

        if ($bindCol !== null && surveillance_has_column($pdo, 'onus', 'idonu')) {
            $onuNameCol = surveillance_detect_onu_name_column($pdo);
            $bindSelect = ", s.`{$bindCol}` AS bound_onu_id, o.idonu AS onu_id, o.inface AS onu_inface, o.status AS onu_status, o.rx AS onu_rx";
            if ($onuNameCol !== null) {
                $bindSelect .= ", o.`{$onuNameCol}` AS onu_name";
            }
            $bindJoin = " LEFT JOIN onus o ON o.idonu = s.`{$bindCol}` ";
        } elseif ($bindCol !== null) {
            $bindSelect = ", s.`{$bindCol}` AS bound_onu_id";
        }

        $sql = "
            SELECT s.*, g.name AS group_name, l.name AS location_name {$bindSelect}
            FROM surveillance s
            LEFT JOIN groups g ON g.id = s.groups
            LEFT JOIN location l ON l.id = s.locations
            {$bindJoin}
            WHERE s.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $dev = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dev) {
            $go->redirect('/?do=surveillance&err=' . urlencode('Пристрій не знайдено'));
            break;
        }

        $typeMap = surveillance_device_type_map();
        $deviceType = $typeMap[(int)($dev['oidid'] ?? 0)] ?? ('Type #' . (int)($dev['oidid'] ?? 0));

        $monitorBadge = (($dev['monitor'] ?? 'no') === 'yes')
            ? '<span class="signal3">' . surveillance_h(surveillance_l($lang, 'monitor_on', 'Моніторинг увімкнено')) . '</span>'
            : '<span class="signal1">' . surveillance_h(surveillance_l($lang, 'monitor_off', 'Моніторинг вимкнено')) . '</span>';

        $statusBadge = ((int)($dev['status'] ?? 0) === 1)
            ? '<span class="signal3">' . surveillance_h(surveillance_l($lang, 'active', 'Активний')) . '</span>'
            : '<span class="signal1">' . surveillance_h(surveillance_l($lang, 'inactive', 'Неактивний')) . '</span>';

        $boundOnuBlock = '-';
        if ($bindCol !== null) {
            $boundOnuId = (int)($dev['bound_onu_id'] ?? 0);
            if ($boundOnuId > 0) {
                $onuLabel = 'ONU #' . $boundOnuId;
                if (!empty($dev['onu_inface'])) {
                    $onuLabel .= ' [' . $dev['onu_inface'] . ']';
                }
                if (!empty($dev['onu_name'])) {
                    $onuLabel .= ' ' . $dev['onu_name'];
                }
                $extra = [];
                if (isset($dev['onu_status'])) {
                    $extra[] = ((int)$dev['onu_status'] === 1 ? 'online' : 'offline');
                }
                if (isset($dev['onu_rx']) && $dev['onu_rx'] !== '') {
                    $extra[] = 'RX ' . $dev['onu_rx'];
                }
                if (!empty($extra)) {
                    $onuLabel .= ' (' . implode(', ', $extra) . ')';
                }
                $boundOnuBlock = '<a href="/?do=onu&id=' . $boundOnuId . '">' . surveillance_h($onuLabel) . '</a>';
            }
        }

        $speedbar = '';
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . surveillance_h(surveillance_l($lang, 'main', 'Головна')) . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=surveillance"><i class="fi fi-rr-angle-left"></i>Відеоспостереження</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Перегляд пристрою #' . (int)$dev['id'] . '</span>';

        $deviceName = surveillance_h($dev['place'] ?? '');
        $deviceIp = surveillance_h($dev['netip'] ?? '');
        $community = surveillance_h($dev['snmpro'] ?? '');
        $groupName = !empty($dev['group_name']) ? surveillance_h($dev['group_name']) : '—';
        $locationName = !empty($dev['location_name']) ? surveillance_h($dev['location_name']) : '—';
        $lastUpdate = '—';
        if (!empty($dev['updates']) && function_exists('aftertime')) {
            $lastUpdate = surveillance_h(aftertime($dev['updates']));
        }
        $equipmentDetails = trim((string)($dev['inf'] ?? '') . ' ' . (string)($dev['model'] ?? '') . ' ' . (string)($dev['firmware'] ?? '') . ' ' . (string)($dev['uptime'] ?? ''));
        if ($equipmentDetails === '') {
            $equipmentDetails = '—';
        } else {
            $equipmentDetails = surveillance_h($equipmentDetails);
        }

        $boundOnuIdCurrent = (int)($dev['bound_onu_id'] ?? 0);
        $onuOpenBtn = '';
        if ($boundOnuIdCurrent > 0) {
            $onuOpenBtn = '<a class="knopkagreen" href="/?do=onu&id=' . $boundOnuIdCurrent . '">Відкрити ONU</a>';
        }

        $result = '';
        $result .= '<style>
            .surv-view-top{display:flex;gap:12px;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;}
            .surv-view-title{font-size:20px;font-weight:700;line-height:1.2;margin:0;}
            .surv-view-sub{color:#607d8b;font-size:13px;margin-top:4px;}
            .surv-view-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;}
            .surv-view-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:10px;margin-top:12px;}
            .surv-view-card{border:1px solid #e7edf3;border-radius:12px;padding:10px 12px;background:#fff;}
            .surv-view-card h4{margin:0 0 8px;font-size:14px;color:#1f2d3a;}
            .surv-view-row{display:flex;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px dashed #edf2f7;}
            .surv-view-row:last-child{border-bottom:0;}
            .surv-view-label{color:#607d8b;}
            .surv-view-val{font-weight:600;color:#263746;text-align:right;word-break:break-word;}
        </style>';

        $result .= '<div class="card1"><div class="block_white">';
        $result .= '<div class="surv-view-top">';
        $result .= '<div>';
        $result .= '<h3 class="surv-view-title">' . $deviceName . '</h3>';
        $result .= '<div class="surv-view-sub">Пристрій #' . (int)$dev['id'] . ' • ' . surveillance_h($deviceType) . '</div>';
        $result .= '<div class="surv-view-badges">' . $monitorBadge . $statusBadge . '</div>';
        $result .= '</div>';

        $result .= '<div class="polebtn" style="display:flex;gap:8px;flex-wrap:wrap;">';
        $result .= '<a class="knopkaor" href="/?do=surveillance&act=edit&id=' . (int)$dev['id'] . '">' . surveillance_h(surveillance_l($lang, 'edit', 'Редагувати')) . '</a>';
        $result .= '<a class="knopkagreen" href="/?do=surveillance">' . surveillance_h(surveillance_l($lang, 'back', 'Назад')) . '</a>';
        $result .= '<form method="post" action="/?do=surveillance&act=delete" onsubmit="return confirm(\'Видалити пристрій #' . (int)$dev['id'] . '?\');">';
        $result .= '<input type="hidden" name="id" value="' . (int)$dev['id'] . '">';
        $result .= '<button type="submit" class="knopkared" style="border:0;cursor:pointer;">Видалити</button>';
        $result .= '</form>';
        $result .= '</div>';
        $result .= '</div>';

        $result .= '<div class="surv-view-grid">';

        $result .= '<div class="surv-view-card">';
        $result .= '<h4>Мережа</h4>';
        $result .= '<div class="surv-view-row"><span class="surv-view-label">IP</span><span class="surv-view-val">' . $deviceIp . '</span></div>';
        $result .= '<div class="surv-view-row"><span class="surv-view-label">Community</span><span class="surv-view-val">' . $community . '</span></div>';
        $result .= '<div class="surv-view-row"><span class="surv-view-label">Оновлено</span><span class="surv-view-val">' . $lastUpdate . '</span></div>';
        $result .= '</div>';

        $result .= '<div class="surv-view-card">';
        $result .= '<h4>Розміщення</h4>';
        $result .= '<div class="surv-view-row"><span class="surv-view-label">Група</span><span class="surv-view-val">' . $groupName . '</span></div>';
        $result .= '<div class="surv-view-row"><span class="surv-view-label">Локація</span><span class="surv-view-val">' . $locationName . '</span></div>';
        $result .= '</div>';

        $result .= '<div class="surv-view-card">';
        $result .= '<h4>Обладнання</h4>';
        $result .= '<div class="surv-view-row"><span class="surv-view-label">SNMP/система</span><span class="surv-view-val">' . $equipmentDetails . '</span></div>';
        $result .= '</div>';

        if ($bindCol !== null) {
            $result .= '<div class="surv-view-card">';
            $result .= '<h4>Прив\'язка ONU</h4>';
            $result .= '<div class="surv-view-row"><span class="surv-view-label">ONU</span><span class="surv-view-val">' . $boundOnuBlock . '</span></div>';
            if ($onuOpenBtn !== '') {
                $result .= '<div class="polebtn" style="margin-top:8px;">' . $onuOpenBtn . '</div>';
            }
            $result .= '</div>';
        }

        $result .= '</div>';
        $result .= '</div></div>';

        $content = '<div id="onu-speedbar">' . $speedbar . '</div>';
        $content .= surveillance_flash();
        $content .= $result;

        $metatags = [
            'title' => 'Відеоспостереження',
            'description' => 'Відеоспостереження',
            'page' => 'board_main',
        ];
        break;

    default:
        $titleModule = 'Відеоспостереження';

        $speedbar = '';
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . surveillance_h(surveillance_l($lang, 'main', 'Головна')) . '</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . surveillance_h($titleModule) . '</span>';

        $bindCol = surveillance_detect_onu_binding_column($pdo);

        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $where = '';
        $params = [];
        if ($q !== '') {
            $where = "WHERE (s.place LIKE :q OR s.netip LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $bindSelect = '';
        $bindJoin = '';
        if ($bindCol !== null) {
            $bindSelect .= ", s.`{$bindCol}` AS bound_onu_id";
            if (surveillance_has_column($pdo, 'onus', 'idonu')) {
                $onuNameCol = surveillance_detect_onu_name_column($pdo);
                $bindSelect .= ", o.idonu AS onu_id, o.inface AS onu_inface";
                if ($onuNameCol !== null) {
                    $bindSelect .= ", o.`{$onuNameCol}` AS onu_name";
                }
                $bindJoin .= " LEFT JOIN onus o ON o.idonu = s.`{$bindCol}` ";
            }
        }

        $sql = "
            SELECT s.id, s.oidid, s.status, s.netip, s.place, s.inf, s.model, s.firmware, s.uptime, s.monitor, s.updates
                   {$bindSelect}
            FROM surveillance s
            {$bindJoin}
            {$where}
            ORDER BY s.place ASC, s.id DESC
        ";

        $rows = [];
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $rows = [];
        }

        $typeMap = surveillance_device_type_map();
        $tableRows = '';
        foreach ($rows as $device) {
            $dtype = $typeMap[(int)($device['oidid'] ?? 0)] ?? ('Type #' . (int)($device['oidid'] ?? 0));
            $monitorBadge = (($device['monitor'] ?? 'no') === 'yes')
                ? '<span class="signal3">ON</span>'
                : '<span class="signal1">OFF</span>';
            $statusClass = ((int)($device['status'] ?? 0) === 1) ? 'st_1' : 'st_2';

            $onuCell = '-';
            if ($bindCol !== null) {
                $boundId = (int)($device['bound_onu_id'] ?? 0);
                if ($boundId > 0) {
                    $label = 'ONU #' . $boundId;
                    if (!empty($device['onu_inface'])) {
                        $label .= ' [' . $device['onu_inface'] . ']';
                    }
                    if (!empty($device['onu_name'])) {
                        $label .= ' ' . $device['onu_name'];
                    }
                    $onuCell = '<a href="/?do=onu&id=' . $boundId . '">' . surveillance_h($label) . '</a>';
                }
            }

            $tableRows .= '<tr class="olt-dev-olt">';
            $tableRows .= '<td class="text_center">' . surveillance_h($dtype) . '</td>';
            $tableRows .= '<td><span class="statusonu ' . $statusClass . '"></span></td>';
            $tableRows .= '<td>' . $monitorBadge . '</td>';
            $tableRows .= '<td><span class="net_ip">' . surveillance_h($device['netip'] ?? '') . '</span></td>';
            $tableRows .= '<td class="td_url"><a href="/?do=surveillance&act=view&id=' . (int)$device['id'] . '">' . surveillance_h($device['place'] ?? '') . '</a>';
            if (!empty($device['updates']) && function_exists('aftertime')) {
                $tableRows .= '<span class="timer"><img src="../style/img/refresh.png"><span>' . surveillance_h(aftertime($device['updates'])) . '</span></span>';
            }
            $tableRows .= '</td>';

            $details = trim((string)($device['inf'] ?? '') . ' ' . (string)($device['model'] ?? '') . ' ' . (string)($device['firmware'] ?? '') . ' ' . (string)($device['uptime'] ?? ''));
            $tableRows .= '<td>' . surveillance_h($details) . '</td>';
            if ($bindCol !== null) {
                $tableRows .= '<td>' . $onuCell . '</td>';
            }

            $tableRows .= '<td style="white-space:nowrap;">';
            $tableRows .= '<a class="knopkaor" href="/?do=surveillance&act=edit&id=' . (int)$device['id'] . '">Ред.</a> ';
            $tableRows .= '<form method="post" action="/?do=surveillance&act=delete" style="display:inline;" onsubmit="return confirm(\'Видалити пристрій #' . (int)$device['id'] . '?\');">';
            $tableRows .= '<input type="hidden" name="id" value="' . (int)$device['id'] . '">';
            $tableRows .= '<button type="submit" class="knopkared" style="border:0; cursor:pointer;">X</button>';
            $tableRows .= '</form>';
            $tableRows .= '</td>';
            $tableRows .= '</tr>';
        }

        if ($tableRows === '') {
            $colspan = $bindCol !== null ? 8 : 7;
            $tableRows = '<tr><td colspan="' . $colspan . '" class="text_center">Немає пристроїв</td></tr>';
        }

        $content = '';
        $content .= '<div id="onu-speedbar">' . $speedbar . '</div>';
        $content .= surveillance_flash();

        $content .= "<div class='pmon_block' id='board_fault'>
            <div class='pmon_block_left block_white pre20'>
                <div class='pole'>
                    <a href='/?do=surveillance&act=add' class='urlelelement'>Додати новий</a>
                    <a href='/?do=surveillance&act=config' class='urlelelement'>Налаштування</a>
                </div>
                <form method='get' action='/' style='margin-top:10px;'>
                    <input type='hidden' name='do' value='surveillance'>
                    <input class='input1' style='width:100%;' type='text' name='q' value='" . surveillance_h($q) . "' placeholder='Пошук по назві або IP'>
                </form>
            </div>
            <div class='pmon_block_right pre80'>
                <div class='table-wrapper'>
                    <table id='board_fault'>
                        <thead>
                            <tr>
                                <th width='12%'><center>Тип</center></th>
                                <th width='5%'><center>Статус</center></th>
                                <th width='6%'><center>Мон.</center></th>
                                <th width='12%'>IP</th>
                                <th width='20%'>Опис</th>
                                <th>Обладнання</th>";
        if ($bindCol !== null) {
            $content .= "<th width='16%'>ONU</th>";
        }
        $content .= "
                                <th width='8%'><center>Дії</center></th>
                            </tr>
                        </thead>
                        <tbody>
                            {$tableRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";

        $metatags = [
            'title' => $titleModule,
            'description' => $titleModule,
            'page' => 'board_main',
        ];
        break;
}

$tpl->load_template('html_devices.tpl');
$tpl->set('{speedbar}', $speedbar_block);
$tpl->set('{content_head}', $content_head);
$tpl->set('{content}', $content);
$tpl->compile('content');
$tpl->clear();
?>
