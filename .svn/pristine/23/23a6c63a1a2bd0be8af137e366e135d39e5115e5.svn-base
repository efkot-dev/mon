<?php
if (!defined('PONMONITOR') && !defined('FIBER')) {
    die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if (!$id) {
    $go->redirect('fiber');
    exit;
}
$photo_id = isset($_GET['photo_id']) ? Clean::int($_GET['photo_id']) : null;
$fn = isset($_GET['fn']) ? Clean::text($_GET['fn']) : null;
if ($photo_id && $fn === 'delet') {
    $stmt = $pdo->prepare('SELECT id, ponid, photo FROM ponmap_photo WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $photo_id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($photo['ponid'])) {
        if ((int)$photo['ponid'] === (int)$id) {
            $del = $pdo->prepare('DELETE FROM ponmap_photo WHERE id = :id');
            $del->execute([':id' => (int)$photo['id']]);
            $file = 'file/photo/' . $photo['photo'];
            if ($photo['photo'] && is_file($file)) {
                @unlink($file);
            }
        }
    }
    $go->go('/?do=fiber&act=details&id=' . (int)$id);
    exit;
}
$stmt = $pdo->prepare("SELECT pe.id, pe.name AS pe_name, pe.tree, pe.unit_id, t.name AS tree_name, u.id AS unit_id_real, u.name AS unit_name FROM ponelement pe LEFT JOIN pontree t ON t.id = pe.tree LEFT JOIN ponunit u ON u.id = pe.unit_id WHERE pe.id = :id LIMIT 1");
$stmt->execute([':id' => (int)$id]);
$ponbox = $stmt->fetch(PDO::FETCH_ASSOC);
if (empty($ponbox['id'])) {
    $go->redirect('fiber');
    exit;
}
$pon_element_url = '/?do=fiber&act=details&id=' . (int)$ponbox['id'];
$metatags = [
    'title'       => $ponbox['pe_name'],
    'description' => $ponbox['pe_name'],
    'page'        => 'details'
];
$bar_tpl .= generateAutoLoadScript();
$bar_tpl .= '<div class="nav-bar">';
$bar_tpl .= '<a href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$bar_tpl .= '<a href="/?do=fiber&act=unit"><i class="fi fi-rr-angle-left"></i>' . $lang['volsmeraja'] . '</a>';
$bar_tpl .= '<a href="/?do=fiber&act=viewunit&id=' . (int)$ponbox['unit_id_real'] . '"><i class="fi fi-rr-angle-left"></i>' . htmlspecialchars($ponbox['unit_name'], ENT_QUOTES, 'UTF-8') . '</a>';
$bar_tpl .= '<a href="/?do=fiber&act=viewtree&id=' . (int)$ponbox['tree'] . '"><i class="fi fi-rr-angle-left"></i>' . htmlspecialchars($ponbox['tree_name'], ENT_QUOTES, 'UTF-8') . '</a>';
$bar_tpl .= '<span class="active"><i class="fi fi-rr-angle-left"></i>' . htmlspecialchars($ponbox['pe_name'], ENT_QUOTES, 'UTF-8') . '</span>';
$bar_tpl .= '</div>';
$resutltpl .= '<div class="container"><div class="left-column"><div class="menu_olt_left ponbox_details">';
$resutltpl .= '<h2>' . $ponbox['pe_name'] . '</h2>';
if ($access->get('edit_ponbox')) {
    $resutltpl .= '<div class="ponbox_panel">';
    $resutltpl .= '<a class="edit" href="/?do=fiber&act=edit&id=' . (int)$ponbox['id'] . '">'.$lang['setups'].'</a>';
    $resutltpl .= '<a class="foto" href="#" onclick="ajaxaddphotoponbox(\'' . (int)$ponbox['id'] . '\');">'.$lang['added_photo'].'</a>';
    if (!empty($confPMon['PMON_BILLING']) && (int)$confPMon['PMON_BILLING'] === 1) {
        $resutltpl .= '<a class="usr" href="#" onclick="ajaxaddusrponbox(\'' . (int)$ponbox['id'] . '\');">'.$lang['fibber_connect_client'].'</a>';
    }
    $resutltpl .= '</div>';
    $resutltpl .= '<div id="load_data"></div>';
}
$resutltpl .= '</div></div><div class="right-column">';
$resutltpl .= '<div class="ponbox_info details_ponbox">';

$resutltpl .= '<div class="connect-list-head"><h2>' . $lang['fiber_connect_onu'] . '</h2></div>';
$resutltpl .= list_pon_element($pdo, ['pontree' => (int)$ponbox['tree'], 'ponelement' => (int)$ponbox['id']]);
$resutltpl .= '</div>';
if (!empty($confPMon['PMON_BILLING']) && (int)$confPMon['PMON_BILLING'] === 1) {
    $stmt = $pdo->prepare("
        SELECT bu.*,
               COALESCE(o1.idonu,    o2.idonu)    AS idonu,
               COALESCE(o1.status,   o2.status)   AS o_status,
               COALESCE(o1.rx,       o2.rx)       AS o_rx,
               COALESCE(o1.rxolt,    o2.rxolt)    AS o_rxolt,
               COALESCE(o1.dist,     o2.dist)     AS o_dist,
               COALESCE(o1.rxstatus, o2.rxstatus) AS o_rxstatus,
               COALESCE(o1.reason,   o2.reason)   AS o_reason,
               COALESCE(o1.online,   o2.online)   AS o_online,
               COALESCE(o1.offline,  o2.offline)  AS o_offline
        FROM billing_usr bu
        LEFT JOIN onus o1 ON o1.mac = bu.onumac
        LEFT JOIN onus o2 ON o2.sn  = bu.onumac
        WHERE bu.ponid = :ponid
        ORDER BY bu.id DESC
    ");
    $stmt->execute([':ponid' => (int)$ponbox['id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($users) {
        $resutltpl .= '<table class="resp-tab list-onu-olt ">';
        $resutltpl .= '<thead>
            <th class="mob_w10" width="4%">' . $lang['status'] . '</th>
            <th width="6%"><span class="inf_signal"><span class="sig2">Rx ONU</span></span></th>
            <th>MAC SN</th>
            <th>П.І.Б.</th>
            <th>Номер договору</th>
            <th>Платіжний код</th>
            <th>Адреса</th>
            <th>Action</th>
        </thead><tbody>';
        foreach ($users as $usr) {
            $o_status = isset($usr['o_status']) ? (int)$usr['o_status'] : null;
            $o_reason = $usr['o_reason'] ?? null;
            $o_rx = $usr['o_rx'] ?? null;
            $o_rxolt = $usr['o_rxolt'] ?? null;
            $o_rxstatus = $usr['o_rxstatus'] ?? null;
            $statusInfo = statusTermianl($o_status); // очікується твоя функція
            if ($o_status === 1) {
                $get_status = $statusInfo['img'];
            } else {
                $get_status = reason_onu($o_status, (string)$o_reason);
            }
            $signal = '';
            if ($o_rx !== null && $o_rx !== '') {
                $signal .= '<span class="signal">' . signalTerminal($o_rx) . '</span>';
            }
            if ($o_rxolt !== null && $o_rxolt !== '') {
                $signal .= '<span class="signal">' . signalTerminal($o_rxolt) . '</span>';
            }
            if ($o_rxstatus === 'up' || $o_rxstatus === 'down') {
                $signal .= '<span class="signal' . htmlspecialchars($o_rxstatus, ENT_QUOTES, 'UTF-8') . '"><i class="fi fi-rr-angle-small-' . htmlspecialchars($o_rxstatus, ENT_QUOTES, 'UTF-8') . '"></i></span>';
            }
            $onukeyHtml = $usr['onumac'];
            $pibHtml = $usr['pib'];
            $dogHtml = $usr['dogovir'];
            $platHtml = $usr['platijniy'];
            $tpl_usr = PmonBillingTemplate($usr);
            $onuLinkId = isset($usr['onuid']) ? (int)$usr['onuid'] : (int)($usr['idonu'] ?? 0);
            $resutltpl .= '<tr class="' . htmlspecialchars($statusInfo['css'] ?? '', ENT_QUOTES, 'UTF-8') . '">
                <td class="status">' . $get_status . '</td>
                <td>' . $signal . '</td>
                <td class="td_url"><a href="/?do=onu&id=' . $onuLinkId . '">' . $onukeyHtml . '</a></td>
                <td class="td_names">' . $pibHtml . '</td>
                <td>' . $dogHtml . '</td>
                <td>' . $platHtml . '</td>
                <td>' . $tpl_usr . '</td>
                <td><a class="panel_house rr_1" href="/?do=billing&act=delete&id=">Розділити</a></td>
            </tr>';
        }
        $resutltpl .= '</tbody></table>';
    }
}

$element = connFelement($pdo, (int)$ponbox['id']);
if (!empty($element)) {
    $resutltpl .= '<div class="ponbox_info details_ponbox">';
    $resutltpl .= '<div class="connect-list-head"><h2>' . $lang['fiber_connect_fiber'] . '</h2></div>';
    $resutltpl .= '<div class="list-fiber-conn">';
    foreach ($element as $conn) {
        $nameHtml = htmlspecialchars($conn['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $dist = calculateLineDistance($conn['geo']) . $lang['metric'];
        $resutltpl .= '
            <a href="/?do=fiber&act=view&id=' . (int)$conn['id'] . '">
                <img src="../style/img/conn2.png">
                <span>' . $nameHtml . '</span>
                <span class="fiber_dist">(' . $dist . ')</span>
            </a>
            ' . (empty($conn['del'])
                ? '<a href="?do=fiber&act=delconnkabel&p=' . (int)$id . '&id=' . (int)$conn['fiberid'] . '&t=view"><img class="del_fiber" src="../style/img/close.png"></a>'
                : '');
    }
    $resutltpl .= '</div>';
    $resutltpl .= '</div>';
}
$stmt = $pdo->prepare('SELECT id, photo FROM ponmap_photo WHERE ponid = :ponid ORDER BY added DESC');
$stmt->execute([':ponid' => (int)$id]);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($photos) {
    $resutltpl .= '<div class="ponbox_info details_ponbox">';
    $resutltpl .= '<div class="connect-list-head"><h2>Photo</h2></div>';
    $resutltpl .= '<div id="photo-info"></div>';
    $resutltpl .= '<div class="image-grid">';
    foreach ($photos as $foto) {
        $img = htmlspecialchars($foto['photo'], ENT_QUOTES, 'UTF-8');
        $resutltpl .= '
            <div class="image-container">
                <a href="#" onclick="view_photo(' . (int)$foto['id'] . ')">
                    <img src="/?do=thumb&type=photo&img=' . $img . '">
                    <div class="overlay-view">View</div>
                </a>
            </div>';
    }
    $resutltpl .= '</div>';
    $resutltpl .= '</div>';
}
$resutltpl .= '</div>';
$resutltpl .= '</div>';
$resutltpl .= '<div id="ajax"></div>';
?>
