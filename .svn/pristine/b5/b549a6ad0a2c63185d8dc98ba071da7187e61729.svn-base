<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

if (!$confPMon['FIBERMAP'] && empty($confPMon['FIBERMAP'])) {
    $go->redirect('main');
}

$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
$act = isset($act) ? (string)$act : '';
$msg = isset($_GET['msg']) ? Clean::text($_GET['msg']) : '';
$err = isset($_GET['err']) ? Clean::text($_GET['err']) : '';
$userId = isset($USER['id']) ? (int)$USER['id'] : 0;
$canManage = ($access->get('setup') || $access->get('setupdevice') || ((int)($USER['class'] ?? 0) >= 4));

unit_ensure_schema($db, $time);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canManage) {
        unit_go('/?do=unit&err=' . urlencode('Недостатньо прав'));
    }

    if ($act === 'save_node') {
        $title = trim((string)Clean::text($_POST['title'] ?? ''));
        $code = trim((string)Clean::text($_POST['code'] ?? ''));
        $address = trim((string)Clean::text($_POST['address'] ?? ''));
        $lat = unit_parse_float($_POST['lat'] ?? '');
        $lon = unit_parse_float($_POST['lon'] ?? '');
        $note = trim((string)Clean::text($_POST['note'] ?? ''));

        if ($title === '') {
            unit_go('/?do=unit&act=add&err=' . urlencode('Вкажіть назву вузла'));
        }

        $upload = unit_handle_upload('node_photo', 'unit_node_');
        if (!empty($upload['error'])) {
            unit_go('/?do=unit&act=add&err=' . urlencode($upload['error']));
        }

        $db->SQLinsert('unit_node', array(
            'title' => $title,
            'code' => $code,
            'address' => $address,
            'lat' => unit_db_decimal($lat),
            'lon' => unit_db_decimal($lon),
            'photo' => $upload['name'] ?? '',
            'note' => $note,
            'status' => 'active',
            'userid' => $userId,
            'added' => $time,
            'updated' => $time
        ));
        $nodeId = (int)$db->getInsertId();
        unit_go('/?do=unit&act=view&id=' . $nodeId . '&msg=' . urlencode('Вузол створено'));
    }

    if ($act === 'update_node') {
        $nodeId = Clean::int($_POST['id'] ?? 0);
        $node = $db->Fast('unit_node', '*', array('id' => $nodeId));
        if (empty($node['id'])) {
            unit_go('/?do=unit&err=' . urlencode('Вузол не знайдено'));
        }

        $title = trim((string)Clean::text($_POST['title'] ?? ''));
        $code = trim((string)Clean::text($_POST['code'] ?? ''));
        $address = trim((string)Clean::text($_POST['address'] ?? ''));
        $lat = unit_parse_float($_POST['lat'] ?? '');
        $lon = unit_parse_float($_POST['lon'] ?? '');
        $note = trim((string)Clean::text($_POST['note'] ?? ''));

        if ($title === '') {
            unit_go('/?do=unit&act=edit&id=' . $nodeId . '&err=' . urlencode('Вкажіть назву вузла'));
        }

        $upload = unit_handle_upload('node_photo', 'unit_node_');
        if (!empty($upload['error'])) {
            unit_go('/?do=unit&act=edit&id=' . $nodeId . '&err=' . urlencode($upload['error']));
        }

        $sql = array(
            'title' => $title,
            'code' => $code,
            'address' => $address,
            'lat' => unit_db_decimal($lat),
            'lon' => unit_db_decimal($lon),
            'note' => $note,
            'updated' => $time
        );
        if (!empty($upload['name'])) {
            $sql['photo'] = $upload['name'];
        }

        $db->SQLupdate('unit_node', $sql, array('id' => $nodeId));
        unit_go('/?do=unit&act=view&id=' . $nodeId . '&msg=' . urlencode('Дані вузла оновлено'));
    }

    if ($act === 'save_object') {
        $nodeId = Clean::int($_POST['node_id'] ?? 0);
        $node = $db->Fast('unit_node', '*', array('id' => $nodeId));
        if (empty($node['id'])) {
            unit_go('/?do=unit&err=' . urlencode('Вузол не знайдено'));
        }

        $categoryId = Clean::int($_POST['category_id'] ?? 0);
        $title = trim((string)Clean::text($_POST['title'] ?? ''));
        $model = trim((string)Clean::text($_POST['model'] ?? ''));
        $serial = trim((string)Clean::text($_POST['serial_number'] ?? ''));
        $inventory = trim((string)Clean::text($_POST['inventory_no'] ?? ''));
        $qty = max(1, (int)Clean::int($_POST['qty'] ?? 1));
        $switchId = Clean::int($_POST['switch_id'] ?? 0);
        $customIcon = trim((string)Clean::text($_POST['custom_icon'] ?? ''));
        $iconPreset = trim((string)Clean::text($_POST['icon_preset'] ?? ''));
        $note = trim((string)Clean::text($_POST['note'] ?? ''));

        if ($title === '') {
            unit_go('/?do=unit&act=view&id=' . $nodeId . '&err=' . urlencode('Вкажіть назву обладнання'));
        }

        $category = $db->Fast('unit_object_category', '*', array('id' => $categoryId));
        if (empty($category['id'])) {
            $category = $db->Fast('unit_object_category', '*', array('slug' => 'other'));
            $categoryId = (int)($category['id'] ?? 0);
        }

        $icon = $customIcon !== '' ? $customIcon : $iconPreset;
        if ($icon === '') {
            $icon = (string)($category['icon'] ?? '');
        }

        $upload = unit_handle_upload('object_photo', 'unit_obj_');
        if (!empty($upload['error'])) {
            unit_go('/?do=unit&act=view&id=' . $nodeId . '&err=' . urlencode($upload['error']));
        }

        $db->SQLinsert('unit_object', array(
            'node_id' => $nodeId,
            'category_id' => $categoryId,
            'title' => $title,
            'model' => $model,
            'serial_number' => $serial,
            'inventory_no' => $inventory,
            'qty' => $qty,
            'switch_id' => ($switchId > 0 ? $switchId : 0),
            'icon' => $icon,
            'photo' => ($upload['name'] ?? ''),
            'note' => $note,
            'userid' => $userId,
            'added' => $time,
            'updated' => $time
        ));

        $db->SQLupdate('unit_node', array('updated' => $time), array('id' => $nodeId));
        unit_go('/?do=unit&act=view&id=' . $nodeId . '&msg=' . urlencode('Обладнання додано'));
    }

    if ($act === 'update_object') {
        $objectId = Clean::int($_POST['id'] ?? 0);
        $object = $db->Fast('unit_object', '*', array('id' => $objectId));
        if (empty($object['id'])) {
            unit_go('/?do=unit&err=' . urlencode('Обʼєкт не знайдено'));
        }

        $nodeId = (int)$object['node_id'];
        $categoryId = Clean::int($_POST['category_id'] ?? 0);
        $title = trim((string)Clean::text($_POST['title'] ?? ''));
        $model = trim((string)Clean::text($_POST['model'] ?? ''));
        $serial = trim((string)Clean::text($_POST['serial_number'] ?? ''));
        $inventory = trim((string)Clean::text($_POST['inventory_no'] ?? ''));
        $qty = max(1, (int)Clean::int($_POST['qty'] ?? 1));
        $switchId = Clean::int($_POST['switch_id'] ?? 0);
        $customIcon = trim((string)Clean::text($_POST['custom_icon'] ?? ''));
        $iconPreset = trim((string)Clean::text($_POST['icon_preset'] ?? ''));
        $note = trim((string)Clean::text($_POST['note'] ?? ''));

        if ($title === '') {
            unit_go('/?do=unit&act=edit_object&id=' . $objectId . '&err=' . urlencode('Вкажіть назву обладнання'));
        }

        $category = $db->Fast('unit_object_category', '*', array('id' => $categoryId));
        if (empty($category['id'])) {
            $category = $db->Fast('unit_object_category', '*', array('slug' => 'other'));
            $categoryId = (int)($category['id'] ?? 0);
        }

        $icon = $customIcon !== '' ? $customIcon : $iconPreset;
        if ($icon === '') {
            $icon = (string)($category['icon'] ?? '');
        }

        $upload = unit_handle_upload('object_photo', 'unit_obj_');
        if (!empty($upload['error'])) {
            unit_go('/?do=unit&act=edit_object&id=' . $objectId . '&err=' . urlencode($upload['error']));
        }

        $sql = array(
            'category_id' => $categoryId,
            'title' => $title,
            'model' => $model,
            'serial_number' => $serial,
            'inventory_no' => $inventory,
            'qty' => $qty,
            'switch_id' => ($switchId > 0 ? $switchId : 0),
            'icon' => $icon,
            'note' => $note,
            'updated' => $time
        );
        if (!empty($upload['name'])) {
            $sql['photo'] = $upload['name'];
        }

        $db->SQLupdate('unit_object', $sql, array('id' => $objectId));
        $db->SQLupdate('unit_node', array('updated' => $time), array('id' => $nodeId));
        unit_go('/?do=unit&act=view&id=' . $nodeId . '&msg=' . urlencode('Обладнання оновлено'));
    }
}

if ($act === 'delete_object' && $canManage) {
    $objectId = isset($_GET['object_id']) ? Clean::int($_GET['object_id']) : 0;
    $object = $db->Fast('unit_object', '*', array('id' => $objectId));
    if (!empty($object['id'])) {
        $nodeId = (int)$object['node_id'];
        $db->SQLdelete('unit_object', array('id' => $objectId));
        $db->SQLupdate('unit_node', array('updated' => $time), array('id' => $nodeId));
        unit_go('/?do=unit&act=view&id=' . $nodeId . '&msg=' . urlencode('Обладнання видалено'));
    }
    unit_go('/?do=unit&err=' . urlencode('Обʼєкт не знайдено'));
}

if ($act === 'delete_node' && $canManage) {
    $nodeId = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
    $node = $db->Fast('unit_node', '*', array('id' => $nodeId));
    if (!empty($node['id'])) {
        $db->SQLdelete('unit_object', array('node_id' => $nodeId));
        $db->SQLdelete('unit_node', array('id' => $nodeId));
        unit_go('/?do=unit&msg=' . urlencode('Вузол видалено'));
    }
    unit_go('/?do=unit&err=' . urlencode('Вузол не знайдено'));
}

$speedbar = '<a class="brmhref" href="/?do=unit"><i class="fi fi-rr-boxes"></i>Вузли</a>';
$result = '';

$iconPresets = unit_icon_presets();
$categories = $db->SimpleWhile("SELECT * FROM unit_object_category ORDER BY sort ASC, name ASC");
if (!is_array($categories)) {
    $categories = array();
}

$switchList = $db->SimpleWhile("SELECT id, place, netip FROM switch ORDER BY place ASC LIMIT 500");
if (!is_array($switchList)) {
    $switchList = array();
}

$result .= '<style>
.unit-wrap{padding:8px 0;}
.unit-actions{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap;}
.unit-btn{display:inline-flex;align-items:center;gap:6px;background:#0f7bff;color:#fff;border:0;border-radius:8px;padding:8px 12px;text-decoration:none;cursor:pointer;}
.unit-btn.gray{background:#475569;}
.unit-btn.red{background:#b91c1c;}
.unit-alert{padding:10px 12px;border-radius:8px;margin-bottom:10px;font-size:13px;}
.unit-alert.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;}
.unit-alert.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
.unit-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;}
.unit-card{background:#fff;border:1px solid #dbe5f1;border-radius:12px;overflow:hidden;display:flex;flex-direction:column;}
.unit-photo{height:150px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;}
.unit-photo img{max-width:100%;max-height:100%;object-fit:cover;}
.unit-body{padding:10px;}
.unit-title{font-size:16px;font-weight:700;color:#0f172a;margin:0 0 6px;}
.unit-meta{font-size:12px;color:#475569;line-height:1.5;}
.unit-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.unit-chip{font-size:11px;padding:3px 8px;border-radius:999px;background:#e2e8f0;color:#1e293b;}
.unit-view{padding:10px;background:#fff;border:1px solid #dbe5f1;border-radius:12px;margin-bottom:12px;}
.unit-view-head{display:grid;grid-template-columns:240px 1fr;gap:12px;}
.unit-view-photo{min-height:180px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.unit-view-photo img{max-width:100%;max-height:100%;object-fit:cover;}
.unit-view-name{font-size:22px;font-weight:700;color:#0f172a;margin:0 0 6px;}
.unit-form{background:#fff;border:1px solid #dbe5f1;border-radius:12px;padding:12px;margin-bottom:12px;}
.unit-form h3{margin:0 0 10px;font-size:16px;color:#0f172a;}
.unit-form-grid{display:grid;grid-template-columns:repeat(2,minmax(180px,1fr));gap:10px;}
.unit-form-grid .full{grid-column:1/-1;}
.unit-form label{display:block;font-size:12px;color:#475569;margin-bottom:4px;}
.unit-input,.unit-select,.unit-text{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:8px;background:#fff;}
.unit-text{min-height:74px;resize:vertical;}
.unit-obj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;}
.unit-obj{background:#fff;border:1px solid #dbe5f1;border-radius:12px;padding:10px;display:flex;flex-direction:column;min-height:180px;}
.unit-obj-thumb{height:72px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin-bottom:8px;overflow:hidden;}
.unit-obj-thumb img{max-width:100%;max-height:100%;object-fit:contain;}
.unit-obj-name{font-size:14px;font-weight:700;color:#0f172a;margin:0 0 4px;}
.unit-obj-meta{font-size:12px;color:#475569;line-height:1.35;}
.unit-obj-foot{margin-top:auto;padding-top:8px;display:flex;gap:6px;}
.unit-obj-foot a{font-size:12px;text-decoration:none;color:#0f7bff;}
@media(max-width:900px){.unit-view-head{grid-template-columns:1fr;}.unit-form-grid{grid-template-columns:1fr;}}
</style>';

if ($msg !== '') {
    $result .= '<div class="unit-alert ok">' . unit_h($msg) . '</div>';
}
if ($err !== '') {
    $result .= '<div class="unit-alert err">' . unit_h($err) . '</div>';
}

if ($act === 'add' && $canManage) {
    $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Новий вузол</span>';
    $result .= '<div class="unit-wrap">';
    $result .= '<form class="unit-form" action="/?do=unit&act=save_node" method="post" enctype="multipart/form-data">';
    $result .= '<h3>Створити вузол / бокс</h3>';
    $result .= '<div class="unit-form-grid">';
    $result .= '<div><label>Назва вузла *</label><input class="unit-input" type="text" name="title" required></div>';
    $result .= '<div><label>Код / № боксу</label><input class="unit-input" type="text" name="code"></div>';
    $result .= '<div class="full"><label>Адреса</label><input class="unit-input" type="text" name="address"></div>';
    $result .= '<div><label>Широта (lat)</label><input class="unit-input" type="text" name="lat" placeholder="49.123456"></div>';
    $result .= '<div><label>Довгота (lon)</label><input class="unit-input" type="text" name="lon" placeholder="24.123456"></div>';
    $result .= '<div class="full"><label>Загальне фото вузла</label><input class="unit-input" type="file" name="node_photo" accept="image/jpeg,image/png,image/webp,image/gif"></div>';
    $result .= '<div class="full"><label>Нотатка</label><textarea class="unit-text" name="note"></textarea></div>';
    $result .= '</div>';
    $result .= '<div class="unit-row" style="margin-top:12px;"><button class="unit-btn" type="submit">Зберегти вузол</button><a class="unit-btn gray" href="/?do=unit">Скасувати</a></div>';
    $result .= '</form></div>';
} elseif ($act === 'edit' && $canManage && $id > 0) {
    $node = $db->Fast('unit_node', '*', array('id' => $id));
    if (empty($node['id'])) {
        unit_go('/?do=unit&err=' . urlencode('Вузол не знайдено'));
    }
    $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . unit_h($node['title']) . '</span>';
    $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагування</span>';
    $result .= '<div class="unit-wrap">';
    $result .= '<form class="unit-form" action="/?do=unit&act=update_node" method="post" enctype="multipart/form-data">';
    $result .= '<input type="hidden" name="id" value="' . (int)$node['id'] . '">';
    $result .= '<h3>Редагувати вузол</h3>';
    $result .= '<div class="unit-form-grid">';
    $result .= '<div><label>Назва вузла *</label><input class="unit-input" type="text" name="title" value="' . unit_h($node['title']) . '" required></div>';
    $result .= '<div><label>Код / № боксу</label><input class="unit-input" type="text" name="code" value="' . unit_h($node['code']) . '"></div>';
    $result .= '<div class="full"><label>Адреса</label><input class="unit-input" type="text" name="address" value="' . unit_h($node['address']) . '"></div>';
    $result .= '<div><label>Широта (lat)</label><input class="unit-input" type="text" name="lat" value="' . unit_h(unit_coord_value($node['lat'] ?? null)) . '"></div>';
    $result .= '<div><label>Довгота (lon)</label><input class="unit-input" type="text" name="lon" value="' . unit_h(unit_coord_value($node['lon'] ?? null)) . '"></div>';
    $result .= '<div class="full"><label>Заміна фото вузла</label><input class="unit-input" type="file" name="node_photo" accept="image/jpeg,image/png,image/webp,image/gif"></div>';
    $result .= '<div class="full"><label>Нотатка</label><textarea class="unit-text" name="note">' . unit_h($node['note']) . '</textarea></div>';
    $result .= '</div>';
    $result .= '<div class="unit-row" style="margin-top:12px;"><button class="unit-btn" type="submit">Оновити</button><a class="unit-btn gray" href="/?do=unit&act=view&id=' . (int)$node['id'] . '">Назад</a></div>';
    $result .= '</form></div>';
} elseif ($act === 'edit_object' && $canManage) {
    $objectId = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
    $object = $db->Fast('unit_object', '*', array('id' => $objectId));
    if (empty($object['id'])) {
        unit_go('/?do=unit&err=' . urlencode('Обʼєкт не знайдено'));
    }
    $node = $db->Fast('unit_node', '*', array('id' => (int)$object['node_id']));
    if (empty($node['id'])) {
        unit_go('/?do=unit&err=' . urlencode('Вузол не знайдено'));
    }
    $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . unit_h($node['title']) . '</span>';
    $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагування обладнання</span>';
    $result .= '<div class="unit-wrap">';
    $result .= '<form class="unit-form" action="/?do=unit&act=update_object" method="post" enctype="multipart/form-data">';
    $result .= '<input type="hidden" name="id" value="' . (int)$object['id'] . '">';
    $result .= '<h3>Редагувати обʼєкт</h3>';
    $result .= unit_object_form_html($categories, $switchList, $iconPresets, $object);
    $result .= '<div class="unit-row" style="margin-top:12px;"><button class="unit-btn" type="submit">Оновити</button><a class="unit-btn gray" href="/?do=unit&act=view&id=' . (int)$node['id'] . '">Назад</a></div>';
    $result .= '</form></div>';
} elseif ($act === 'view' && $id > 0) {
    $node = $db->Fast('unit_node', '*', array('id' => $id));
    if (empty($node['id'])) {
        unit_go('/?do=unit&err=' . urlencode('Вузол не знайдено'));
    }
    $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . unit_h($node['title']) . '</span>';
    $photo = unit_file_src($node['photo']);
    $mapLink = '';
    if (unit_has_coords($node['lat'] ?? null, $node['lon'] ?? null)) {
        $mapLink = '<a class="unit-btn gray" target="_blank" href="https://www.google.com/maps?q=' . unit_h($node['lat']) . ',' . unit_h($node['lon']) . '">Відкрити на карті</a>';
    }
    $objects = $db->SimpleWhile("SELECT o.*, c.name AS category_name, c.icon AS category_icon, s.place AS switch_place
                                 FROM unit_object o
                                 LEFT JOIN unit_object_category c ON c.id = o.category_id
                                 LEFT JOIN switch s ON s.id = o.switch_id
                                 WHERE o.node_id = " . (int)$node['id'] . "
                                 ORDER BY o.id DESC");
    if (!is_array($objects)) {
        $objects = array();
    }
    $result .= '<div class="unit-wrap">';
    $result .= '<div class="unit-actions"><div class="unit-row">';
    $result .= '<a class="unit-btn gray" href="/?do=unit">До списку вузлів</a>';
    if ($canManage) {
        $result .= '<a class="unit-btn" href="/?do=unit&act=edit&id=' . (int)$node['id'] . '">Редагувати вузол</a>';
        $result .= '<a class="unit-btn red" href="/?do=unit&act=delete_node&id=' . (int)$node['id'] . '" onclick="return confirm(\'Видалити вузол і все обладнання?\')">Видалити вузол</a>';
    }
    $result .= $mapLink . '</div></div>';
    $result .= '<div class="unit-view"><div class="unit-view-head">';
    $result .= '<div class="unit-view-photo">' . ($photo !== '' ? '<img src="' . unit_h($photo) . '">' : '<img src="../style/img/box.png">') . '</div>';
    $result .= '<div><h2 class="unit-view-name">' . unit_h($node['title']) . '</h2>';
    $result .= '<div class="unit-meta"><b>Код/бокс:</b> ' . unit_h($node['code']) . '<br>';
    $result .= '<b>Адреса:</b> ' . unit_h($node['address']) . '<br>';
    $result .= '<b>Координати:</b> ' . unit_h(unit_coord_text($node['lat'] ?? null, $node['lon'] ?? null)) . '<br>';
    $result .= '<b>Оновлено:</b> ' . unit_h($node['updated']) . '<br>';
    $result .= '<b>Нотатка:</b> ' . unit_h($node['note']) . '</div>';
    $result .= '<div class="unit-row" style="margin-top:8px;"><span class="unit-chip">Обʼєктів: ' . count($objects) . '</span></div></div></div></div>';
    $result .= '<div class="unit-form"><h3>Обладнання на вузлі</h3>';
    if (count($objects) > 0) {
        $result .= '<div class="unit-obj-grid">';
        foreach ($objects as $obj) {
            $thumb = unit_object_thumb($obj);
            $result .= '<div class="unit-obj"><div class="unit-obj-thumb">' . ($thumb !== '' ? '<img src="' . unit_h($thumb) . '">' : '<img src="../style/img/default.png">') . '</div>';
            $result .= '<h4 class="unit-obj-name">' . unit_h($obj['title']) . '</h4><div class="unit-obj-meta">';
            $result .= '<b>Категорія:</b> ' . unit_h($obj['category_name']) . '<br>';
            $result .= '<b>Кількість:</b> ' . (int)$obj['qty'] . '<br>';
            $result .= (!empty($obj['model']) ? '<b>Модель:</b> ' . unit_h($obj['model']) . '<br>' : '');
            $result .= (!empty($obj['serial_number']) ? '<b>SN:</b> ' . unit_h($obj['serial_number']) . '<br>' : '');
            $result .= (!empty($obj['inventory_no']) ? '<b>Інв.№:</b> ' . unit_h($obj['inventory_no']) . '<br>' : '');
            $result .= (!empty($obj['switch_place']) ? '<b>Комутатор:</b> ' . unit_h($obj['switch_place']) . '<br>' : '');
            $result .= (!empty($obj['note']) ? '<b>Нотатка:</b> ' . unit_h($obj['note']) : '') . '</div>';
            if ($canManage) {
                $result .= '<div class="unit-obj-foot"><a href="/?do=unit&act=edit_object&id=' . (int)$obj['id'] . '">Редагувати</a><a href="/?do=unit&act=delete_object&object_id=' . (int)$obj['id'] . '" onclick="return confirm(\'Видалити обʼєкт?\')">Видалити</a></div>';
            }
            $result .= '</div>';
        }
        $result .= '</div>';
    } else {
        $result .= '<div class="unit-meta">Поки що немає обʼєктів у цьому вузлі.</div>';
    }
    $result .= '</div>';
    if ($canManage) {
        $result .= '<form class="unit-form" action="/?do=unit&act=save_object" method="post" enctype="multipart/form-data">';
        $result .= '<input type="hidden" name="node_id" value="' . (int)$node['id'] . '"><h3>Додати обʼєкт у вузол</h3>';
        $result .= unit_object_form_html($categories, $switchList, $iconPresets, array());
        $result .= '<div class="unit-row" style="margin-top:12px;"><button class="unit-btn" type="submit">Додати обʼєкт</button></div></form>';
    }
    $result .= '</div>';
} else {
    $rows = $db->SimpleWhile("SELECT n.*, COUNT(o.id) AS obj_count
                             FROM unit_node n
                             LEFT JOIN unit_object o ON o.node_id = n.id
                             GROUP BY n.id
                             ORDER BY n.updated DESC, n.id DESC");
    if (!is_array($rows)) {
        $rows = array();
    }
    $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Список вузлів</span>';
    $result .= '<div class="unit-wrap"><div class="unit-actions"><div class="unit-meta">Вузли/бокси для монтажників і обліку обладнання</div>';
    if ($canManage) {
        $result .= '<a class="unit-btn" href="/?do=unit&act=add">Створити вузол</a>';
    }
    $result .= '</div>';
    if (count($rows) > 0) {
        $result .= '<div class="unit-grid">';
        foreach ($rows as $n) {
            $photo = unit_file_src($n['photo']);
            $result .= '<a class="unit-card" href="/?do=unit&act=view&id=' . (int)$n['id'] . '">';
            $result .= '<div class="unit-photo">' . ($photo !== '' ? '<img src="' . unit_h($photo) . '">' : '<img src="../style/img/box.png">') . '</div>';
            $result .= '<div class="unit-body"><h3 class="unit-title">' . unit_h($n['title']) . '</h3><div class="unit-meta">' . unit_h($n['address']) . '</div><div class="unit-row">';
            $result .= '<span class="unit-chip">Обʼєктів: ' . (int)$n['obj_count'] . '</span>';
            if (!empty($n['code'])) {
                $result .= '<span class="unit-chip">Бокс: ' . unit_h($n['code']) . '</span>';
            }
            $result .= '</div><div class="unit-meta" style="margin-top:8px;">Оновлено: ' . unit_h($n['updated']) . '</div></div></a>';
        }
        $result .= '</div>';
    } else {
        $result .= '<div class="unit-alert err">Поки що вузли не створені.</div>';
        if ($canManage) {
            $result .= '<a class="unit-btn" href="/?do=unit&act=add">Створити перший вузол</a>';
        }
    }
    $result .= '</div>';
}

$metatags = array('title' => 'Вузли', 'description' => 'Облік вузлів', 'page' => 'unit');
$tpl->load_template('battery/page.tpl');
$tpl->set('{speedbar}', $speedbar);
$tpl->set('{result}', $result);
$tpl->compile('content');
$tpl->clear();

function unit_h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function unit_go(string $url): void {
    global $go;
    $go->go($url);
    exit;
}

function unit_parse_float($value): ?float {
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $value = str_replace(',', '.', $value);
    if (!is_numeric($value)) {
        return null;
    }
    return (float)$value;
}

function unit_db_decimal(?float $value): string {
    if ($value === null) {
        return '0.0000000';
    }
    return number_format((float)$value, 7, '.', '');
}

function unit_has_coords($lat, $lon): bool {
    $latN = unit_parse_float($lat);
    $lonN = unit_parse_float($lon);
    if ($latN === null || $lonN === null) {
        return false;
    }
    return (abs($latN) > 0.000001 || abs($lonN) > 0.000001);
}

function unit_coord_text($lat, $lon): string {
    if (!unit_has_coords($lat, $lon)) {
        return '-';
    }
    return (string)$lat . ', ' . (string)$lon;
}

function unit_coord_value($value): string {
    $n = unit_parse_float($value);
    if ($n === null || abs($n) <= 0.000001) {
        return '';
    }
    return rtrim(rtrim(number_format($n, 7, '.', ''), '0'), '.');
}

function unit_handle_upload(string $fieldName, string $prefix): array {
    if (empty($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
        return array('name' => null, 'error' => null);
    }
    $file = $_FILES[$fieldName];
    if (!isset($file['error']) || (int)$file['error'] !== UPLOAD_ERR_OK) {
        return array('name' => null, 'error' => 'Помилка завантаження файлу');
    }
    $tmpFile = (string)$file['tmp_name'];
    if (!is_uploaded_file($tmpFile)) {
        return array('name' => null, 'error' => 'Некоректний файл завантаження');
    }
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
    $mime = $finfo ? (string)finfo_file($finfo, $tmpFile) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif');
    if (!isset($allowed[$mime])) {
        return array('name' => null, 'error' => 'Дозволено лише JPG/PNG/WEBP/GIF');
    }
    $uploadDir = ROOT_DIR . '/file/photo/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        return array('name' => null, 'error' => 'Не вдалося створити директорію для файлів');
    }
    $newName = strtolower($prefix . substr(md5(uniqid((string)mt_rand(), true)), 0, 14) . '.' . $allowed[$mime]);
    if (!move_uploaded_file($tmpFile, $uploadDir . $newName)) {
        return array('name' => null, 'error' => 'Не вдалося зберегти файл');
    }
    return array('name' => $newName, 'error' => null);
}

function unit_file_src($file): string {
    $file = trim((string)$file);
    if ($file === '') {
        return '';
    }
    if (preg_match('~^(https?://|/)~i', $file)) {
        return $file;
    }
    return '/file/photo/' . rawurlencode($file);
}

function unit_object_thumb(array $obj): string {
    $photo = trim((string)($obj['photo'] ?? ''));
    if ($photo !== '') {
        return unit_file_src($photo);
    }
    $icon = trim((string)($obj['icon'] ?? ''));
    if ($icon !== '') {
        if (preg_match('~^(https?://|/)~i', $icon) || strpos($icon, '../') === 0) {
            return $icon;
        }
        return '/' . ltrim($icon, '/');
    }
    $catIcon = trim((string)($obj['category_icon'] ?? ''));
    if ($catIcon !== '') {
        return $catIcon;
    }
    return '../style/img/default.png';
}

function unit_icon_presets(): array {
    return array(
        '../style/img/dev-switch.png' => 'Комутатор',
        '../style/img/devmonitor.png' => 'Маршрутизатор',
        '../style/img/connect.png' => 'Мережевий фільтр',
        '../style/img/battery.png' => 'Акумулятор',
        '../style/img/addconnect.png' => 'UPS/Живлення',
        '../style/img/default.png' => 'Інше'
    );
}

function unit_object_form_html(array $categories, array $switchList, array $iconPresets, array $object): string {
    $categoryId = (int)($object['category_id'] ?? 0);
    $title = unit_h($object['title'] ?? '');
    $model = unit_h($object['model'] ?? '');
    $serial = unit_h($object['serial_number'] ?? '');
    $inventory = unit_h($object['inventory_no'] ?? '');
    $qty = (int)($object['qty'] ?? 1);
    $switchId = (int)($object['switch_id'] ?? 0);
    $icon = (string)($object['icon'] ?? '');
    $note = unit_h($object['note'] ?? '');
    $html = '<div class="unit-form-grid">';
    $html .= '<div><label>Категорія *</label><select class="unit-select" name="category_id" required>';
    foreach ($categories as $cat) {
        $html .= '<option value="' . (int)$cat['id'] . '"' . (((int)$cat['id'] === $categoryId) ? ' selected' : '') . '>' . unit_h($cat['name']) . '</option>';
    }
    $html .= '</select></div>';
    $html .= '<div><label>Назва обʼєкта *</label><input class="unit-input" type="text" name="title" value="' . $title . '" required></div>';
    $html .= '<div><label>Модель</label><input class="unit-input" type="text" name="model" value="' . $model . '"></div>';
    $html .= '<div><label>Серійний номер</label><input class="unit-input" type="text" name="serial_number" value="' . $serial . '"></div>';
    $html .= '<div><label>Інвентарний номер</label><input class="unit-input" type="text" name="inventory_no" value="' . $inventory . '"></div>';
    $html .= '<div><label>Кількість</label><input class="unit-input" type="number" min="1" name="qty" value="' . max(1, $qty) . '"></div>';
    $html .= '<div class="full"><label>Привʼязка до комутатора (необовʼязково)</label><select class="unit-select" name="switch_id"><option value="0">-</option>';
    foreach ($switchList as $sw) {
        $html .= '<option value="' . (int)$sw['id'] . '"' . (((int)$sw['id'] === $switchId) ? ' selected' : '') . '>' . unit_h($sw['place']) . ' (' . unit_h($sw['netip']) . ')</option>';
    }
    $html .= '</select></div>';
    $html .= '<div><label>Міні-іконка (пресет)</label><select class="unit-select" name="icon_preset"><option value="">-</option>';
    foreach ($iconPresets as $src => $name) {
        $html .= '<option value="' . unit_h($src) . '"' . (($icon === $src) ? ' selected' : '') . '>' . unit_h($name) . '</option>';
    }
    $html .= '</select></div>';
    $html .= '<div><label>Або своя іконка (URL/шлях)</label><input class="unit-input" type="text" name="custom_icon" value="' . unit_h($icon) . '" placeholder="/file/photo/icon.png"></div>';
    $html .= '<div class="full"><label>Фото обʼєкта</label><input class="unit-input" type="file" name="object_photo" accept="image/jpeg,image/png,image/webp,image/gif"></div>';
    $html .= '<div class="full"><label>Нотатка</label><textarea class="unit-text" name="note">' . $note . '</textarea></div></div>';
    return $html;
}

function unit_ensure_schema($db, string $time): void {
    static $ready = false;
    if ($ready) {
        return;
    }
    $db->query("CREATE TABLE IF NOT EXISTS unit_node (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(150) NOT NULL,
        code VARCHAR(80) DEFAULT NULL,
        address VARCHAR(255) DEFAULT NULL,
        lat DECIMAL(10,7) DEFAULT NULL,
        lon DECIMAL(10,7) DEFAULT NULL,
        photo VARCHAR(255) DEFAULT NULL,
        note TEXT,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        userid INT UNSIGNED DEFAULT NULL,
        added DATETIME NOT NULL,
        updated DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_status (status),
        KEY idx_updated (updated)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS unit_object_category (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(120) NOT NULL,
        slug VARCHAR(80) NOT NULL,
        icon VARCHAR(255) DEFAULT NULL,
        sort INT NOT NULL DEFAULT 100,
        added DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS unit_object (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        node_id INT UNSIGNED NOT NULL,
        category_id INT UNSIGNED DEFAULT NULL,
        title VARCHAR(150) NOT NULL,
        model VARCHAR(150) DEFAULT NULL,
        serial_number VARCHAR(120) DEFAULT NULL,
        inventory_no VARCHAR(120) DEFAULT NULL,
        qty INT NOT NULL DEFAULT 1,
        switch_id INT UNSIGNED DEFAULT NULL,
        icon VARCHAR(255) DEFAULT NULL,
        photo VARCHAR(255) DEFAULT NULL,
        note TEXT,
        userid INT UNSIGNED DEFAULT NULL,
        added DATETIME NOT NULL,
        updated DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_node (node_id),
        KEY idx_category (category_id),
        KEY idx_switch (switch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $count = $db->Simple("SELECT COUNT(*) AS c FROM unit_object_category");
    if ((int)($count['c'] ?? 0) === 0) {
        $defaults = array(
            array('name' => 'Комутатор', 'slug' => 'switch', 'icon' => '../style/img/dev-switch.png', 'sort' => 10),
            array('name' => 'Маршрутизатор', 'slug' => 'router', 'icon' => '../style/img/devmonitor.png', 'sort' => 20),
            array('name' => 'Мережевий фільтр', 'slug' => 'filter', 'icon' => '../style/img/connect.png', 'sort' => 30),
            array('name' => 'Акумулятор', 'slug' => 'battery', 'icon' => '../style/img/battery.png', 'sort' => 40),
            array('name' => 'UPS/Живлення', 'slug' => 'power', 'icon' => '../style/img/addconnect.png', 'sort' => 50),
            array('name' => 'Інше', 'slug' => 'other', 'icon' => '../style/img/default.png', 'sort' => 99)
        );
        foreach ($defaults as $row) {
            $db->SQLinsert('unit_object_category', array(
                'name' => $row['name'],
                'slug' => $row['slug'],
                'icon' => $row['icon'],
                'sort' => $row['sort'],
                'added' => $time
            ));
        }
    }
    $ready = true;
}
?>
