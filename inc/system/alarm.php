<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

if (empty($confPMon['PING3']) && empty($confPMon['SECURITY_PING3'])) {
    $go->redirect('main');
}
$sqllocation = getLocation();
$location_array = [];
if (!empty($sqllocation)) {
    foreach ($sqllocation as $loc) {
        $location_array[$loc['id']] = ['id' => $loc['id'], 'name' => $loc['name']];
    }
}
$sqlinsert = [];
$speedbar = '';
$templates = '';
switch ($act) {
    case 'add':
        $metatags = [
            'title' => 'Додати об’єкт для охорони','description' => 'Додати об’єкт для охорони','page' => 'addalarmping3'
        ];
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=alarm"><i class="fi fi-rr-angle-left"></i>Об’єкти під охороною</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати об’єкт для охорони</span>';
        $templates .= '<div class="nav-fiber p10"><form action="/?do=alarm" method="post">';
        $templates .= '<input name="act" type="hidden" value="savealarm">';
        $templates .= '<label for="name">Назва:</label><input type="text" id="name" name="name" required autocomplete="off" style="width:50%;"><br>';
        $templates .= '<label for="type">Локація:</label>';
        $listlocation = '';
        $location = getListLocations();
        foreach ($location as $loc) {
            $listlocation .= '<option value="' . $loc['id'] . '">' . $loc['name'] . '</option>';
        }
        $templates .= '<select class="select" name="locationid" id="locationid"><option value="0"></option>' . $listlocation . '</select><br>';
        $listtypedevice = '';
        $stmt = $pdo->query("SELECT * FROM mon_ping3");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ping3) {
            $listtypedevice .= '<option value="' . $ping3['id'] . '">' . $ping3['name'] . '</option>';
        }
        $templates .= '<label for="type">PING 3:</label>';
        $templates .= '<select class="select" name="ping3id" id="ping3id">' . $listtypedevice . '</select><br>';
        $templates .= '<input type="submit" value="' . $lang['add'] . '">';
        $templates .= '</div></form>';
        break;
    case 'view':
        $list_history = '';
        $id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
        if ($id && $id > 0) {
            $stmtSelect = $pdo->prepare("SELECT * FROM alarm_ping3 WHERE id = :id LIMIT 1");
            $stmtSelect->execute(['id' => $id]);
            $alarmRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
            $speedbar .= '<a class="brmhref" href="/?do=alarm"><i class="fi fi-rr-angle-left"></i>Об’єкти під охороною</a>';
            $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . htmlspecialchars($alarmRow['name']) . '</span>';
            $stmtHistory = $pdo->prepare("SELECT * FROM alarm_ping3_history WHERE alarm_id = :alarm_id ORDER BY event_time DESC LIMIT 50");
            $stmtHistory->execute(['alarm_id' => $id]);
            $historyList = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
            $list_history .= '<table class="resp-tab"><thead><tr><th width="20%">Дата</th><th width="30%">Опис</th><th>Статус</th></tr></thead><tbody>';
            if (!empty($historyList)) {
                foreach ($historyList as $row) {
                    $statusText = match ((int)$row['new_status']) {
                        1 => 'Увімкнено',0 => 'Вимкнено',default => 'Інше'
                    };
                    $list_history .= '<tr><td>' . htmlspecialchars($row['event_time']) . '</td><td>' . htmlspecialchars($row['note']) . '</td><td>' . htmlspecialchars($statusText) . '</td></tr>';
                }
            } else {
                $list_history .= '<tr><td colspan="3">Відсутні спрацювання</td></tr>';
            }
            $list_history .= '</tbody></table>';
            $templates .= '<div class="container"><div class="left-column"><div class="block_white battery_info">';
            $templates .= '<div class="battery_img"><img src="../style/img/' . ($alarmRow['status'] == 1 ? 'ok_lock.png' : 'no_lock.png') . '"></div>';
            $templates .= '<div class="b_pole"><span>' . $lang['name'] . '</span><h2>' . htmlspecialchars($alarmRow['name']) . '</h2></div>';
            $templates .= '<div class="b_pole"><span>PING3</span><h2>' . htmlspecialchars($alarmRow['netip']) . '</h2></div>';
            $templates .= '<div class="b_pole"><a class="urlelelement" href="/?do=alarm&act=edit&id=' . $alarmRow['id'] . '">Редагувати</a> <a class="urlelelement" href="/?do=alarm&act=delet&id=' . $alarmRow['id'] . '" onclick="return confirm(\'Видалити об’єкт?\')">Видалити</a></div>';
            $templates .= '</div></div><div class="right-column">' . $list_history . '</div></div>';
        }
        break;
    case 'edit':
        $id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
        if ($id && $id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM alarm_ping3 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $alarm = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$alarm) {
                $go->go('/?do=alarm');
				exit;
            }
            $metatags = [
                'title' => 'Редагувати об’єкт охорони','description' => 'Редагування параметрів об’єкта охорони','page' => 'editalarmping3'
            ];
            $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
            $speedbar .= '<a class="brmhref" href="/?do=alarm"><i class="fi fi-rr-angle-left"></i>Об’єкти під охороною</a>';
            $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагування</span>';
            $listlocation = '';			
            foreach ($sqllocation as $loc) {
                $selected = ($alarm['locationid'] == $loc['id']) ? 'selected' : '';
                $listlocation .= '<option value="' . $loc['id'] . '" ' . $selected . '>' . $loc['name'] . '</option>';
            }
            $listtypedevice = '';
            $stmt = $pdo->query("SELECT * FROM mon_ping3");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ping3) {
                $selected = ($alarm['ping3id'] == $ping3['id']) ? 'selected' : '';
                $listtypedevice .= '<option value="' . $ping3['id'] . '" ' . $selected . '>' . $ping3['name'] . '</option>';
            }
            $templates .= '<div class="nav-fiber p10"><form action="/?do=alarm" method="post">';
            $templates .= '<input type="hidden" name="act" value="updatealarm">';
            $templates .= '<input type="hidden" name="id" value="' . (int)$alarm['id'] . '">';
            $templates .= '<label for="name">Назва:</label><input type="text" id="name" name="name" value="' . htmlspecialchars($alarm['name']) . '" required style="width:50%;"><br>';
            $templates .= '<label for="locationid">Локація:</label><select class="select" name="locationid" id="locationid"><option value="0"></option>' . $listlocation . '</select><br>';
            $templates .= '<label for="ping3id">PING 3:</label><select class="select" name="ping3id" id="ping3id">' . $listtypedevice . '</select><br>';
            $templates .= '<input type="submit" value="' . $lang['save'] . '">';
            $templates .= '</div></form>';
        }
        break;
    case 'updatealarm':
        if (isset($_POST['id'], $_POST['name'], $_POST['ping3id'], $_POST['locationid'])) {
            $id = Clean::int($_POST['id']);
            $sqlupdate['name'] = Clean::text($_POST['name']);
            $ping3id = Clean::int($_POST['ping3id']);
            $locationid = Clean::int($_POST['locationid']);
            $stmt = $pdo->prepare("SELECT * FROM mon_ping3 WHERE id = :id");
            $stmt->execute(['id' => $ping3id]);
            $data_ping3 = $stmt->fetch(PDO::FETCH_ASSOC);
            $sqlupdate['netip'] = $data_ping3['netip'];
            $sqlupdate['snmpro'] = $data_ping3['snmpro'];
            $sqlupdate['ping3id'] = $ping3id;
            $sqlupdate['locationid'] = $locationid;
            $db->SQLupdate('alarm_ping3', $sqlupdate, ['id' => $id]);
        }
        $go->go('/?do=alarm');
        break;
    case 'delet':
        $id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
        if ($id && $id > 0) {
            $stmtDelete = $pdo->prepare("DELETE FROM alarm_ping3 WHERE id = :id");
            $stmtDelete->execute(['id' => $id]);
            $pdo->prepare("DELETE FROM alarm_ping3_history WHERE alarm_id = :id")->execute(['id' => $id]);
        }
        $go->go('/?do=alarm');
        break;
    case 'savealarm':
        if (isset($_POST['name'], $_POST['ping3id'], $_POST['locationid'])) {
            $name = Clean::text($_POST['name']);
            $ping3id = Clean::int($_POST['ping3id']);
            $locationid = Clean::int($_POST['locationid']);
            $stmt = $pdo->prepare("SELECT * FROM mon_ping3 WHERE id = :id");
            $stmt->execute(['id' => $ping3id]);
            $data_ping3 = $stmt->fetch(PDO::FETCH_ASSOC);
            $sql = "INSERT INTO alarm_ping3 (name, netip, snmpro, monitor) VALUES (:name, :netip, :snmpro, :monitor)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([
				':name' => $name,':netip' => $data_ping3['netip'],':snmpro' => $data_ping3['snmpro'],':monitor' => 'yes'
			]);
        }
        $go->go('/?do=alarm');
		exit;
        break;
    default:
        $metatags = [
            'title' => 'Об’єкти під охороною',
            'description' => 'Об’єкти під охороною',
            'page' => 'listalarm'
        ];
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Об’єкти під охороною</span>';
        $templates .= '<table class="resp-tab"><thead><tr><th width="10%">' . $lang['status'] . '</th><th width="30%">' . $lang['name'] . '</th><th width="10%">Активність</th><th>Відповідальні особи</th></tr></thead><tbody>';
        $stmt = $pdo->query("SELECT * FROM alarm_ping3");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $jiv) {
            #if ($access->get('alarm_ping3_' . $jiv['id'])) {
                $status = alarm_ping3($jiv['status']);
                $last_alarm = $db->Simple("SELECT * FROM alarm_ping3_history WHERE alarm_id = '{$jiv['id']}' ORDER BY id ASC LIMIT 1");
                $templates .= '<tr><td class="td_table_r">' . $status . '</td><td class="td_table_left"><a href="/?do=alarm&act=view&id=' . $jiv['id'] . '">' . $jiv['name'] . '</a></td><td><span class="off_">' . aftertime($last_alarm['event_time']) . '</span></td><td></td></tr>';
            #}
        }
        $templates .= '</tbody></table>';
        if ($access->get('monitordevice')) {
            $templates .= '<div class="pole"><a href="/?do=alarm&act=add" class="urlelelement">Додати об’єкт</a></div>';
        }
        break;
}

$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', '<div class="mainadmin"><div id="onu-speedbar">' . $speedbar . '</div>' . $templates . '</div>');
$tpl->compile('content');
$tpl->clear();
?>