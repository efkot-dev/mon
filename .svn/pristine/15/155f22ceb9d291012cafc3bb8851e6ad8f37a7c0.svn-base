<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

if (!$access->get('setup') || ($confPMon['IPMAN'] == 0 && !empty($confPMon['IPMAN']))) {
    $go->redirect('main');
}

$idblock = isset($_GET['idblock']) ? Clean::int($_GET['idblock']) : 0;
$actPost = isset($_POST['ipm_act']) ? Clean::str($_POST['ipm_act']) : '';
$msg = isset($_GET['msg']) ? Clean::text($_GET['msg']) : '';
$err = isset($_GET['err']) ? Clean::text($_GET['err']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($actPost === 'create_block') {
        $name = trim((string)Clean::text($_POST['name'] ?? ''));
        $ipblock = trim((string)Clean::str($_POST['ipblock'] ?? ''));
        $note = trim((string)Clean::text($_POST['note'] ?? ''));

        if ($name === '' || $ipblock === '') {
            ipman_go('/?do=ipman&err=' . urlencode('Вкажіть назву і діапазон блоку'));
        }
        if (!ipman_validate_block($ipblock)) {
            ipman_go('/?do=ipman&err=' . urlencode('Некоректний формат блоку. Приклад: 10.10.0.0/24 або 10.10.0.1-10.10.0.254'));
        }

        $db->SQLinsert('ipblock', array(
            'name' => $name,
            'ipblock' => $ipblock,
            'note' => $note,
            'added' => $time
        ));
        ipman_go('/?do=ipman&msg=' . urlencode('IP блок створено'));
    }

    if ($actPost === 'delete_block') {
        $blockId = Clean::int($_POST['blockid'] ?? 0);
        if ($blockId > 0) {
            $db->SQLdelete('ipaddress', array('blockid' => $blockId));
            $db->SQLdelete('ipblock', array('id' => $blockId));
            ipman_go('/?do=ipman&msg=' . urlencode('IP блок видалено'));
        }
        ipman_go('/?do=ipman&err=' . urlencode('Блок не знайдено'));
    }

    if ($actPost === 'create_group') {
        $name = trim((string)Clean::text($_POST['name'] ?? ''));
        $color = trim((string)Clean::str($_POST['color'] ?? ''));
        $note = trim((string)Clean::text($_POST['note'] ?? ''));
        if ($name === '' || $color === '') {
            ipman_go('/?do=ipman&err=' . urlencode('Вкажіть назву і колір групи'));
        }
        $db->SQLinsert('ipgroups', array(
            'name' => $name,
            'color' => $color,
            'note' => $note,
            'added' => $time
        ));
        ipman_go('/?do=ipman&msg=' . urlencode('Групу створено'));
    }

    if ($actPost === 'occupy_ip') {
        $blockId = Clean::int($_POST['blockid'] ?? 0);
        $ip = trim((string)Clean::str($_POST['ip'] ?? ''));
        $deviceName = trim((string)Clean::text($_POST['device_name'] ?? ''));
        $deviceType = trim((string)Clean::text($_POST['device_type'] ?? ''));
        $vlan = trim((string)Clean::text($_POST['vlan'] ?? ''));
        $groupId = Clean::int($_POST['group_id'] ?? 0);
        $switchId = Clean::int($_POST['switch_id'] ?? 0);

        if ($blockId <= 0 || $ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            ipman_go('/?do=ipman&err=' . urlencode('Некоректні дані IP'));
        }

        $block = $db->Fast('ipblock', '*', array('id' => $blockId));
        if (empty($block['id'])) {
            ipman_go('/?do=ipman&err=' . urlencode('Блок не знайдено'));
        }

        if ($deviceName === '' && $switchId > 0) {
            $sw = $db->Fast('switch', '*', array('id' => $switchId));
            if (!empty($sw['id'])) {
                $deviceName = trim((string)$sw['place']);
                if ($deviceName === '') {
                    $deviceName = trim((string)$sw['netip']);
                }
            }
        }
        if ($deviceName === '') {
            $deviceName = 'Без назви';
        }

        $nameLabel = $deviceName;
        if ($deviceType !== '') {
            $nameLabel = '[' . $deviceType . '] ' . $deviceName;
        }

        $exists = $db->Fast('ipaddress', '*', array('blockid' => $blockId, 'ip' => $ip));
        $data = array(
            'name' => $nameLabel,
            'vlan' => $vlan,
            'idgroups' => ($groupId > 0 ? $groupId : 0)
        );
        if (!empty($exists['id'])) {
            $db->SQLupdate('ipaddress', $data, array('id' => (int)$exists['id']));
        } else {
            $data['blockid'] = $blockId;
            $data['ip'] = $ip;
            $data['added'] = $time;
            $db->SQLinsert('ipaddress', $data);
        }

        ipman_go('/?do=ipman&idblock=' . $blockId . '&msg=' . urlencode('IP позначено як зайнятий'));
    }

    if ($actPost === 'release_ip') {
        $blockId = Clean::int($_POST['blockid'] ?? 0);
        $ipId = Clean::int($_POST['ip_id'] ?? 0);
        if ($blockId > 0 && $ipId > 0) {
            $db->SQLdelete('ipaddress', array('id' => $ipId));
            ipman_go('/?do=ipman&idblock=' . $blockId . '&msg=' . urlencode('IP звільнено'));
        }
        ipman_go('/?do=ipman&err=' . urlencode('Не вдалося звільнити IP'));
    }
}

$blocks = $db->SimpleWhile("SELECT * FROM ipblock ORDER BY id DESC");
if (!is_array($blocks)) {
    $blocks = array();
}

$groups = getIPManGroups();
$vlans = $db->SimpleWhile("SELECT * FROM ipvlans ORDER BY CAST(vlan AS SIGNED) ASC");
if (!is_array($vlans)) {
    $vlans = array();
}

$switchRows = $db->SimpleWhile("SELECT id, place, netip, model, inf FROM switch ORDER BY place ASC");
if (!is_array($switchRows)) {
    $switchRows = array();
}

$switchByIp = array();
foreach ($switchRows as $sw) {
    $ip = trim((string)($sw['netip'] ?? ''));
    if ($ip !== '') {
        $switchByIp[$ip] = $sw;
    }
}

$selectedBlock = array();
if ($idblock > 0) {
    $selectedBlock = $db->Fast('ipblock', '*', array('id' => $idblock));
    if (empty($selectedBlock['id'])) {
        $selectedBlock = array();
        $idblock = 0;
    }
}
if (empty($selectedBlock) && !empty($blocks)) {
    $selectedBlock = $blocks[0];
    $idblock = (int)$selectedBlock['id'];
}

$allocMap = array();
$usedRows = array();
$ipRange = array();
$blockSize = 0;
if (!empty($selectedBlock['id'])) {
    $usedRows = $db->SimpleWhile("SELECT * FROM ipaddress WHERE blockid = " . (int)$selectedBlock['id'] . " ORDER BY INET_ATON(ip) ASC");
    if (!is_array($usedRows)) {
        $usedRows = array();
    }
    foreach ($usedRows as $row) {
        $allocMap[(string)$row['ip']] = $row;
    }
    $ipRange = generateIPRange((string)$selectedBlock['ipblock']);
    if (!is_array($ipRange)) {
        $ipRange = array();
    }
    $blockSize = count($ipRange);
    if ($blockSize > 4096) {
        $ipRange = array_slice($ipRange, 0, 4096);
    }
}

$usedCount = count($allocMap);
$freeCount = max(0, count($ipRange) - $usedCount);

$tableblock = '';
$tablegroup = '';
$ipaddress = '';

$tableblock .= '<style>
.ipm-wrap{display:grid;grid-template-columns:320px 1fr;gap:12px;align-items:start;}
.ipm-side,.ipm-mainbox{background:#fff;border:1px solid #dbe5f1;border-radius:12px;padding:10px;}
.ipm-title{font-size:14px;font-weight:700;color:#0f172a;margin:0 0 8px;}
.ipm-form{border:1px dashed #cbd5e1;border-radius:10px;padding:8px;margin-bottom:10px;}
.ipm-form .row{margin-bottom:8px;}
.ipm-label{display:block;font-size:12px;color:#475569;margin-bottom:4px;}
.ipm-input,.ipm-select,.ipm-text{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:7px;background:#fff;}
.ipm-text{min-height:60px;resize:vertical;}
.ipm-btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;padding:7px 10px;cursor:pointer;text-decoration:none;background:#0f7bff;color:#fff;font-size:12px;}
.ipm-btn.gray{background:#475569;}
.ipm-btn.red{background:#b91c1c;}
.ipm-msg{padding:8px 10px;border-radius:8px;margin-bottom:8px;font-size:12px;}
.ipm-msg.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;}
.ipm-msg.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
.ipm-block-list a{display:block;padding:7px 8px;border-radius:8px;text-decoration:none;color:#0f172a;border:1px solid #e2e8f0;margin-bottom:6px;}
.ipm-block-list a.active{background:#eff6ff;border-color:#93c5fd;}
.ipm-legend{display:flex;gap:8px;flex-wrap:wrap;}
.ipm-tag{display:inline-flex;align-items:center;gap:6px;font-size:11px;padding:4px 8px;border-radius:999px;background:#f1f5f9;color:#334155;}
.ipm-dot{width:10px;height:10px;border-radius:99px;display:inline-block;}
.ipm-main-top{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;}
.ipm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(165px,1fr));gap:8px;}
.ipm-cell{border:1px solid #dbe5f1;border-radius:10px;padding:8px;background:#fff;}
.ipm-cell.free{background:#f8fafc;}
.ipm-cell.used{background:#fff7ed;border-color:#fdba74;}
.ipm-cell.system{background:#eff6ff;border-color:#93c5fd;}
.ipm-ip{font-weight:700;font-size:13px;color:#0f172a;margin-bottom:6px;}
.ipm-meta{font-size:11px;color:#475569;min-height:40px;}
.ipm-actions{margin-top:7px;display:flex;gap:6px;flex-wrap:wrap;}
.ipm-hidden{display:none;}
.ipm-quick{border-top:1px dashed #cbd5e1;margin-top:8px;padding-top:8px;}
.ipm-table{width:100%;border-collapse:collapse;}
.ipm-table th,.ipm-table td{border-bottom:1px solid #e2e8f0;padding:6px;text-align:left;font-size:12px;}
@media (max-width: 1100px){.ipm-wrap{grid-template-columns:1fr;}}
</style>';

if ($msg !== '') {
    $tableblock .= '<div class="ipm-msg ok">' . ipman_h($msg) . '</div>';
}
if ($err !== '') {
    $tableblock .= '<div class="ipm-msg err">' . ipman_h($err) . '</div>';
}

$tableblock .= '<div class="ipm-wrap"><aside class="ipm-side">';
$tableblock .= '<h3 class="ipm-title">IP блоки</h3>';

$tableblock .= '<form class="ipm-form" method="post" action="/?do=ipman">';
$tableblock .= '<input type="hidden" name="ipm_act" value="create_block">';
$tableblock .= '<div class="row"><label class="ipm-label">Назва блоку</label><input class="ipm-input" type="text" name="name" required></div>';
$tableblock .= '<div class="row"><label class="ipm-label">Діапазон</label><input class="ipm-input" type="text" name="ipblock" placeholder="10.10.0.0/24 або 10.10.0.1-10.10.0.254" required></div>';
$tableblock .= '<div class="row"><label class="ipm-label">Нотатка</label><textarea class="ipm-text" name="note"></textarea></div>';
$tableblock .= '<button class="ipm-btn" type="submit">Створити блок</button>';
$tableblock .= '</form>';

$tableblock .= '<div class="ipm-block-list">';
if (!empty($blocks)) {
    foreach ($blocks as $b) {
        $isActive = ((int)$b['id'] === (int)$idblock) ? ' active' : '';
        $tableblock .= '<a class="' . trim($isActive) . '" href="/?do=ipman&idblock=' . (int)$b['id'] . '">';
        $tableblock .= '<b>' . ipman_h($b['name']) . '</b><br><span style="font-size:11px;color:#64748b;">' . ipman_h($b['ipblock']) . '</span>';
        $tableblock .= '</a>';
    }
} else {
    $tableblock .= '<div class="ipm-msg err">Немає блоків. Створіть перший блок.</div>';
}
$tableblock .= '</div>';

if (!empty($selectedBlock['id'])) {
    $tableblock .= '<form method="post" action="/?do=ipman" onsubmit="return confirm(\'Видалити блок і всі його IP записи?\');" style="margin-top:8px;">';
    $tableblock .= '<input type="hidden" name="ipm_act" value="delete_block"><input type="hidden" name="blockid" value="' . (int)$selectedBlock['id'] . '">';
    $tableblock .= '<button class="ipm-btn red" type="submit">Видалити обраний блок</button>';
    $tableblock .= '</form>';
}

$tableblock .= '<h3 class="ipm-title" style="margin-top:12px;">Групи/кольори</h3>';
$tableblock .= '<form class="ipm-form" method="post" action="/?do=ipman">';
$tableblock .= '<input type="hidden" name="ipm_act" value="create_group">';
$tableblock .= '<div class="row"><label class="ipm-label">Назва групи</label><input class="ipm-input" type="text" name="name" required></div>';
$tableblock .= '<div class="row"><label class="ipm-label">Колір</label><input class="ipm-input" type="color" name="color" value="#3b82f6" required></div>';
$tableblock .= '<div class="row"><label class="ipm-label">Нотатка</label><textarea class="ipm-text" name="note"></textarea></div>';
$tableblock .= '<button class="ipm-btn" type="submit">Додати групу</button>';
$tableblock .= '</form>';

$tableblock .= '</aside><section class="ipm-mainbox">';

$tableblock .= '<div class="ipm-legend" style="margin-bottom:10px;">';
$tableblock .= '<span class="ipm-tag"><span class="ipm-dot" style="background:#94a3b8;"></span>Вільний</span>';
$tableblock .= '<span class="ipm-tag"><span class="ipm-dot" style="background:#f59e0b;"></span>Зайнятий вручну</span>';
$tableblock .= '<span class="ipm-tag"><span class="ipm-dot" style="background:#3b82f6;"></span>IP системного пристрою</span>';
if (is_array($groups) && count($groups) > 0) {
    foreach ($groups as $gr) {
        $tableblock .= '<span class="ipm-tag"><span class="ipm-dot" style="background:' . ipman_h($gr['color']) . ';"></span>' . ipman_h($gr['name']) . '</span>';
    }
}
$tableblock .= '</div>';

if (!empty($selectedBlock['id'])) {
    $tableblock .= '<div class="ipm-main-top">';
    $tableblock .= '<div><h3 class="ipm-title" style="margin:0;">' . ipman_h($selectedBlock['name']) . ' <span style="font-size:12px;color:#64748b;">(' . ipman_h($selectedBlock['ipblock']) . ')</span></h3>';
    $tableblock .= '<div style="font-size:12px;color:#64748b;">Всього виведено: ' . count($ipRange) . ' | Зайнято: ' . $usedCount . ' | Вільно: ' . $freeCount;
    if ($blockSize > 4096) {
        $tableblock .= ' | <span style="color:#b45309;">Показано перші 4096 IP</span>';
    }
    $tableblock .= '</div></div></div>';

    $ipaddress .= '<div class="ipm-grid">';
    foreach ($ipRange as $idx => $ip) {
        $alloc = $allocMap[$ip] ?? null;
        $sys = $switchByIp[$ip] ?? null;
        $class = 'free';
        if (is_array($alloc)) {
            $class = 'used';
        } elseif (is_array($sys)) {
            $class = 'system';
        }

        $deviceTitle = '-';
        $groupBadge = '';
        if (is_array($alloc)) {
            $deviceTitle = trim((string)($alloc['name'] ?? '-'));
            $gid = (int)($alloc['idgroups'] ?? 0);
            if ($gid > 0 && isset($groups[$gid])) {
                $groupBadge = '<span class="ipm-tag" style="margin-top:4px;background:#f8fafc;border:1px solid ' . ipman_h($groups[$gid]['color']) . ';"><span class="ipm-dot" style="background:' . ipman_h($groups[$gid]['color']) . ';"></span>' . ipman_h($groups[$gid]['name']) . '</span>';
            }
        } elseif (is_array($sys)) {
            $deviceTitle = '[Система] ' . trim((string)$sys['place']);
        }

        $cellId = 'ipm_cell_' . $idx;
        $tableColor = '';
        if (is_array($alloc)) {
            $gid = (int)($alloc['idgroups'] ?? 0);
            if ($gid > 0 && isset($groups[$gid]['color'])) {
                $tableColor = ' style="border-left:4px solid ' . ipman_h($groups[$gid]['color']) . ';"';
            }
        }

        $ipaddress .= '<div class="ipm-cell ' . $class . '"' . $tableColor . '>';
        $ipaddress .= '<div class="ipm-ip">' . ipman_h($ip) . '</div>';
        $ipaddress .= '<div class="ipm-meta">' . ipman_h($deviceTitle);
        if (is_array($alloc) && !empty($alloc['vlan'])) {
            $ipaddress .= '<br>VLAN: ' . ipman_h($alloc['vlan']);
        } elseif (is_array($sys) && !empty($sys['netip'])) {
            $ipaddress .= '<br>' . ipman_h($sys['inf'] . ' ' . $sys['model']);
        }
        $ipaddress .= '</div>' . $groupBadge;
        $ipaddress .= '<div class="ipm-actions">';

        if (is_array($alloc)) {
            $ipaddress .= '<form method="post" action="/?do=ipman&idblock=' . (int)$idblock . '">';
            $ipaddress .= '<input type="hidden" name="ipm_act" value="release_ip">';
            $ipaddress .= '<input type="hidden" name="blockid" value="' . (int)$idblock . '">';
            $ipaddress .= '<input type="hidden" name="ip_id" value="' . (int)$alloc['id'] . '">';
            $ipaddress .= '<button class="ipm-btn red" type="submit">Звільнити</button></form>';
            $ipaddress .= '<button class="ipm-btn gray" type="button" onclick="ipmToggle(\'' . $cellId . '\')">Редагувати</button>';
        } else {
            $ipaddress .= '<button class="ipm-btn" type="button" onclick="ipmToggle(\'' . $cellId . '\')">Позначити</button>';
        }
        $ipaddress .= '</div>';

        $prefName = is_array($alloc) ? (string)$alloc['name'] : (is_array($sys) ? (string)$sys['place'] : '');
        $prefVlan = is_array($alloc) ? (string)$alloc['vlan'] : '';
        $prefGroup = is_array($alloc) ? (int)$alloc['idgroups'] : 0;

        $ipaddress .= '<div id="' . $cellId . '" class="ipm-quick ipm-hidden">';
        $ipaddress .= '<form method="post" action="/?do=ipman&idblock=' . (int)$idblock . '">';
        $ipaddress .= '<input type="hidden" name="ipm_act" value="occupy_ip">';
        $ipaddress .= '<input type="hidden" name="blockid" value="' . (int)$idblock . '">';
        $ipaddress .= '<input type="hidden" name="ip" value="' . ipman_h($ip) . '">';
        $ipaddress .= '<div class="row"><label class="ipm-label">Тип пристрою</label><select class="ipm-select" name="device_type">';
        $types = array('Комутатор', 'Маршрутизатор', 'OLT', 'Сервер', 'Камера', 'Абонент', 'Резерв', 'Інше');
        foreach ($types as $t) {
            $sel = (strpos($prefName, '[' . $t . ']') === 0) ? ' selected' : '';
            $ipaddress .= '<option value="' . ipman_h($t) . '"' . $sel . '>' . ipman_h($t) . '</option>';
        }
        $ipaddress .= '</select></div>';
        $ipaddress .= '<div class="row"><label class="ipm-label">Назва/опис</label><input class="ipm-input" name="device_name" value="' . ipman_h($prefName) . '"></div>';
        $ipaddress .= '<div class="row"><label class="ipm-label">VLAN/примітка</label><input class="ipm-input" name="vlan" value="' . ipman_h($prefVlan) . '"></div>';
        $ipaddress .= '<div class="row"><label class="ipm-label">Група</label><select class="ipm-select" name="group_id"><option value="0">-</option>';
        if (is_array($groups) && count($groups) > 0) {
            foreach ($groups as $gr) {
                $sel = ((int)$prefGroup === (int)$gr['id']) ? ' selected' : '';
                $ipaddress .= '<option value="' . (int)$gr['id'] . '"' . $sel . '>' . ipman_h($gr['name']) . '</option>';
            }
        }
        $ipaddress .= '</select></div>';
        $ipaddress .= '<div class="row"><label class="ipm-label">Привʼязка до комутатора (необовʼязково)</label><select class="ipm-select" name="switch_id"><option value="0">-</option>';
        foreach ($switchRows as $sw) {
            $ipaddress .= '<option value="' . (int)$sw['id'] . '">' . ipman_h($sw['place'] . ' (' . $sw['netip'] . ')') . '</option>';
        }
        $ipaddress .= '</select></div>';
        $ipaddress .= '<div class="ipm-actions"><button class="ipm-btn" type="submit">Зберегти</button><button class="ipm-btn gray" type="button" onclick="ipmToggle(\'' . $cellId . '\')">Закрити</button></div>';
        $ipaddress .= '</form></div>';

        $ipaddress .= '</div>';
    }
    $ipaddress .= '</div>';

    $tableblock .= '<h3 class="ipm-title" style="margin-top:12px;">Зайняті адреси в блоці</h3>';
    if (!empty($usedRows)) {
        $tableblock .= '<table class="ipm-table"><thead><tr><th>IP</th><th>Пристрій</th><th>VLAN</th><th>Група</th><th></th></tr></thead><tbody>';
        foreach ($usedRows as $u) {
            $gid = (int)($u['idgroups'] ?? 0);
            $gname = ($gid > 0 && isset($groups[$gid])) ? $groups[$gid]['name'] : '-';
            $tableblock .= '<tr><td>' . ipman_h($u['ip']) . '</td><td>' . ipman_h($u['name']) . '</td><td>' . ipman_h($u['vlan']) . '</td><td>' . ipman_h($gname) . '</td><td>';
            $tableblock .= '<form method="post" action="/?do=ipman&idblock=' . (int)$idblock . '" onsubmit="return confirm(\'Звільнити IP?\');">';
            $tableblock .= '<input type="hidden" name="ipm_act" value="release_ip"><input type="hidden" name="blockid" value="' . (int)$idblock . '"><input type="hidden" name="ip_id" value="' . (int)$u['id'] . '">';
            $tableblock .= '<button class="ipm-btn red" type="submit">Звільнити</button></form></td></tr>';
        }
        $tableblock .= '</tbody></table>';
    } else {
        $tableblock .= '<div class="ipm-msg err">У цьому блоці немає зайнятих IP.</div>';
    }
} else {
    $tableblock .= '<div class="ipm-msg err">Оберіть або створіть IP блок.</div>';
}

$tableblock .= '</section></div>';

$tableblock .= '<script>
function ipmToggle(id){
  var el=document.getElementById(id);
  if(!el){return;}
  if(el.classList.contains("ipm-hidden")){el.classList.remove("ipm-hidden");}
  else{el.classList.add("ipm-hidden");}
}
</script>';

$metatags = array('title' => $lang['moduleipman'], 'description' => $lang['moduleipman'], 'page' => 'ipman');
$tpl->load_template('ipman.tpl');
$tpl->set('{tableblock}', $tableblock);
$tpl->set('{tablegroup}', '');
$tpl->set('{speedbar}', '<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['manager_ip'] . '</span></div>');
$tpl->set('{result}', '');
$tpl->set('{ipaddress}', $ipaddress);
$tpl->compile('content');
$tpl->clear();

function ipman_h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function ipman_go(string $url): void {
    global $go;
    $go->go($url);
    exit;
}

function ipman_validate_block(string $block): bool {
    $block = trim($block);
    if ($block === '') {
        return false;
    }
    if (strpos($block, '/') !== false) {
        $parts = explode('/', $block);
        if (count($parts) !== 2) {
            return false;
        }
        if (!filter_var(trim($parts[0]), FILTER_VALIDATE_IP)) {
            return false;
        }
        $mask = (int)trim($parts[1]);
        return ($mask >= 16 && $mask <= 32);
    }
    if (strpos($block, '-') !== false) {
        $parts = explode('-', $block);
        if (count($parts) !== 2) {
            return false;
        }
        return (filter_var(trim($parts[0]), FILTER_VALIDATE_IP) && filter_var(trim($parts[1]), FILTER_VALIDATE_IP));
    }
    return false;
}
?>
