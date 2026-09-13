<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

$tplRes = '';

function transport_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function transport_norm_mac($mac)
{
    $mac = trim((string)$mac);
    if ($mac === '') {
        return '';
    }
    $hex = preg_replace('~[^0-9a-fA-F]~', '', $mac);
    if (strlen($hex) !== 12) {
        return '';
    }
    return strtoupper(implode(':', str_split(strtoupper($hex), 2)));
}

function transport_logo_url($logo)
{
    $logo = trim((string)$logo);
    if ($logo === '' || !filter_var($logo, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $logo)) {
        return '';
    }
    $path = (string)parse_url($logo, PHP_URL_PATH);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
    return in_array($ext, $allowed, true) ? $logo : '';
}

function transport_find_onu($db, $idonu, $mac, $sn)
{
    if (is_valid_id($idonu)) {
        $row = $db->Fast('onus', 'idonu,olt,mac,sn,status,reason,added', array('idonu' => (int)$idonu));
        if ($row) {
            return $row;
        }
    }
    if ($mac !== '') {
        $row = $db->Fast('onus', 'idonu,olt,mac,sn,status,reason,added', array('mac' => $mac));
        if ($row) {
            return $row;
        }
    }
    if ($sn !== '') {
        $row = $db->Fast('onus', 'idonu,olt,mac,sn,status,reason,added', array('sn' => $sn));
        if ($row) {
            return $row;
        }
    }
    return null;
}

function transport_redirect($go, $url)
{
    $go->go($url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAct = isset($_POST['act']) ? Clean::text($_POST['act']) : '';

    if ($postAct === 'transport_save_isp') {
        $location = (int)($_POST['location'] ?? 0);
        $name = Clean::text($_POST['name'] ?? '');
        $logo = transport_logo_url(Clean::text($_POST['logo'] ?? ''));
        $phone = Clean::text($_POST['phone'] ?? '');
        $address = Clean::text($_POST['address'] ?? '');
        if ($name === '') {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $db->SQLinsert('transport_isp', array(
            'location' => $location > 0 ? $location : 0,
            'name' => $name,
            'logo' => $logo !== '' ? $logo : null,
            'phone' => $phone !== '' ? $phone : null,
            'address' => $address !== '' ? $address : null
        ));
        transport_redirect($go, '/?do=transport&view=list');
    }

    if ($postAct === 'transport_update_isp') {
        $ispId = Clean::int($_POST['isp_id'] ?? 0);
        $location = (int)($_POST['location'] ?? 0);
        $name = Clean::text($_POST['name'] ?? '');
        $logo = transport_logo_url(Clean::text($_POST['logo'] ?? ''));
        $phone = Clean::text($_POST['phone'] ?? '');
        $address = Clean::text($_POST['address'] ?? '');
        if (!is_valid_id($ispId)) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        if ($name === '') {
            transport_redirect($go, '/?do=transport&view=isp_edit&id=' . (int)$ispId);
        }
        $db->SQLupdate('transport_isp', array(
            'location' => $location > 0 ? $location : 0,
            'name' => $name,
            'logo' => $logo !== '' ? $logo : null,
            'phone' => $phone !== '' ? $phone : null,
            'address' => $address !== '' ? $address : null
        ), array('id' => (int)$ispId));
        transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
    }

    if ($postAct === 'transport_delete_isp') {
        $ispId = Clean::int($_POST['isp_id'] ?? 0);
        $withOnu = (int)($_POST['with_onu'] ?? 0);
        if (!is_valid_id($ispId)) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $cnt = $db->Simple("SELECT COUNT(*) AS c FROM transport_onu WHERE isp_id = " . (int)$ispId);
        if ((int)$withOnu !== 1 && (int)($cnt['c'] ?? 0) > 0) {
            transport_redirect($go, '/?do=transport&view=isp_delete&id=' . (int)$ispId);
        }
        if ((int)($cnt['c'] ?? 0) > 0) {
            $db->SQLdelete('transport_onu', array('isp_id' => (int)$ispId));
        }
        $db->SQLdelete('transport_isp', array('id' => (int)$ispId));
        transport_redirect($go, '/?do=transport&view=list');
    }

    if ($postAct === 'transport_add_onu') {
        $ispId = Clean::int($_POST['isp_id'] ?? 0);
        if (!is_valid_id($ispId)) {
            transport_redirect($go, '/?do=transport&view=list');
        }

        $idonuInput = Clean::int($_POST['idonu'] ?? 0);
        $macInput = transport_norm_mac(Clean::text($_POST['mac'] ?? ''));
        $snInput = Clean::text($_POST['sn'] ?? '');
        $comment = Clean::text($_POST['comment'] ?? '');

        if (!is_valid_id($idonuInput) && $macInput === '' && $snInput === '') {
            transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
        }

        $onu = transport_find_onu($db, $idonuInput, $macInput, $snInput);
        $idonu = $onu ? (int)$onu['idonu'] : null;
        $olt = $onu ? (int)$onu['olt'] : null;
        $mac = $onu ? (string)$onu['mac'] : ($macInput !== '' ? $macInput : null);
        $sn = $onu ? (string)$onu['sn'] : ($snInput !== '' ? $snInput : null);
        $status = $onu ? ((int)$onu['status'] === 1 ? 'online' : 'offline') : 'manual';
        $reason = $onu ? (string)$onu['reason'] : null;

        if ($idonu) {
            $dup = $db->Fast('transport_onu', 'id', array('isp_id' => (int)$ispId, 'idonu' => (int)$idonu));
            if ($dup) {
                transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
            }
        } else {
            if ($mac) {
                $dup = $db->Fast('transport_onu', 'id', array('isp_id' => (int)$ispId, 'mac' => $mac));
                if ($dup) {
                    transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
                }
            }
            if ($sn) {
                $dup = $db->Fast('transport_onu', 'id', array('isp_id' => (int)$ispId, 'sn' => $sn));
                if ($dup) {
                    transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
                }
            }
        }

        $db->SQLinsert('transport_onu', array(
            'isp_id' => (int)$ispId,
            'olt' => $olt ?: null,
            'idonu' => $idonu ?: null,
            'mac' => $mac ?: null,
            'sn' => $sn ?: null,
            'comment' => $comment !== '' ? $comment : null,
            'status' => $status,
            'reason' => $reason ?: null
        ));
        transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
    }

    if ($postAct === 'transport_delete_onu') {
        $ispId = Clean::int($_POST['isp_id'] ?? 0);
        $linkId = Clean::int($_POST['link_id'] ?? 0);
        if (is_valid_id($linkId)) {
            $db->SQLdelete('transport_onu', array('id' => (int)$linkId));
        }
        if (is_valid_id($ispId)) {
            transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
        }
        transport_redirect($go, '/?do=transport&view=list');
    }

    if ($postAct === 'transport_delete_all_onu') {
        $ispId = Clean::int($_POST['isp_id'] ?? 0);
        if (!is_valid_id($ispId)) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $db->SQLdelete('transport_onu', array('isp_id' => (int)$ispId));
        transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispId);
    }
}

$view = isset($_GET['view']) ? Clean::text($_GET['view']) : 'list';

switch ($view) {
    default:
    case 'list':
        $rows = $db->SimpleWhile("SELECT * FROM transport_isp ORDER BY id DESC");
        $metatags = array('title' => 'Групи ONU', 'description' => 'Групи ONU', 'page' => 'transport_list');
        $tplRes .= '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>' . $lang['main'] . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>Групи ONU</span>
        </div>';

        $tplRes .= '<div class="m0">';
        if ($rows) {
            $tplRes .= '<div class="table mt10"><table class="resp-tab" width="100%"><thead><tr><th>Назва</th><th>Телефон</th><th>Адреса</th><th>ONU</th><th>Дії</th></tr></thead><tbody>';
            foreach ($rows as $row) {
                $id = (int)$row['id'];
                $cnt = $db->Simple("SELECT COUNT(*) AS c FROM transport_onu WHERE isp_id = " . $id);
                $locationName = '';
                if (!empty($row['location'])) {
                    $loc = $db->Simple("SELECT name FROM location WHERE id = " . (int)$row['location']);
                    $locationName = isset($loc['name']) ? transport_h($loc['name']) . '<br>' : '';
                }
                $nameHtml = $row['logo']
                    ? '<img src="' . transport_h($row['logo']) . '" alt="logo" style="display:block;height:40px;vertical-align:middle;border-radius:4px;padding:5px;">'
                    : transport_h($row['name']);
                $tplRes .= '<tr>
                    <td><center><a href="/?do=transport&view=isp&id=' . $id . '">' . $nameHtml . '</a></center></td>
                    <td>' . transport_h($row['phone']) . '</td>
                    <td>' . $locationName . transport_h($row['address']) . '</td>
                    <td>' . (int)($cnt['c'] ?? 0) . '</td>
                    <td class="nowrap-btn" align="center">
                        <a class="btn-small btn-green" href="/?do=transport&view=isp_edit&id=' . $id . '"><i class="fa fa-edit"></i> Редагувати</a>
                        <a class="btn-small btn-danger" href="/?do=transport&view=isp_delete&id=' . $id . '"><i class="fa fa-trash"></i> Видалити</a>
                    </td>
                </tr>';
            }
            $tplRes .= '</tbody></table></div>';
        }
        $tplRes .= '</div>
        <div class="dashboard_pon tag-filter">
            <a class="snmp_pon_vz" href="/?do=transport&view=isp_add">Нова група</a>
        </div>';
        break;

    case 'delete_onu':
        $idonu = Clean::int($_GET['id'] ?? 0);
        $ispid = Clean::int($_GET['ispid'] ?? 0);
        if (is_valid_id($idonu)) {
            if (is_valid_id($ispid)) {
                $db->SQLdelete('transport_onu', array('idonu' => (int)$idonu, 'isp_id' => (int)$ispid));
            } else {
                $db->SQLdelete('transport_onu', array('idonu' => (int)$idonu));
            }
        }
        if (is_valid_id($ispid)) {
            transport_redirect($go, '/?do=transport&view=isp&id=' . (int)$ispid);
        }
        if (is_valid_id($idonu)) {
            transport_redirect($go, '/?do=onu&id=' . (int)$idonu);
        }
        transport_redirect($go, '/?do=transport&view=list');
        break;

    case 'isp_add':
        $metatags = array('title' => 'Нова група ONU', 'description' => 'Нова група ONU', 'page' => 'transport_add_group');
        $tplRes .= '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=transport"><i class="fi fi-rr-angle-left"></i>Групи ONU</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>Нова група ONU</span>
        </div>';

        $locationSelect = '';
        $locationList = getListLocations();
        if (is_array($locationList) && count($locationList) > 0) {
            $options = '<option value="0"></option>';
            foreach ($locationList as $loc) {
                $options .= '<option value="' . (int)$loc['id'] . '">' . transport_h($loc['name']) . '</option>';
            }
            $locationSelect = '<div class="form-row"><label>' . $lang['location'] . '</label><select class="select" name="location" id="location">' . $options . '</select></div>';
        }

        $tplRes .= '<div class="card m0">
            <form method="post" action="/?do=transport">
                <input type="hidden" name="act" value="transport_save_isp">
                <div class="form-row"><label>Назва *</label><input type="text" name="name" required></div>
                <div class="form-row"><label>Лого (URL)</label><input type="text" name="logo" placeholder="https://.../logo.png"></div>
                <div class="form-row"><label>Телефон</label><input type="text" name="phone"></div>
                ' . $locationSelect . '
                <div class="form-row"><label>Адреса</label><input type="text" name="address"></div>
                <div class="form-actions mt10"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Зберегти</button></div>
            </form>
        </div>';
        break;

    case 'isp_edit':
        $id = Clean::int($_GET['id'] ?? 0);
        if (!is_valid_id($id)) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $row = $db->Fast('transport_isp', '*', array('id' => (int)$id));
        if (!$row) {
            transport_redirect($go, '/?do=transport&view=list');
        }

        $metatags = array('title' => 'Редагувати групу: ' . transport_h($row['name']), 'description' => 'Редагувати групу', 'page' => 'transport_edit_group');
        $tplRes .= '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=transport"><i class="fi fi-rr-angle-left"></i>Групи ONU</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагувати: ' . transport_h($row['name']) . '</span>
        </div>';

        $locationSelect = '';
        $locationList = getListLocations();
        if (is_array($locationList) && count($locationList) > 0) {
            $options = '<option value="0"></option>';
            foreach ($locationList as $loc) {
                $selected = ((int)$row['location'] === (int)$loc['id']) ? ' selected' : '';
                $options .= '<option value="' . (int)$loc['id'] . '"' . $selected . '>' . transport_h($loc['name']) . '</option>';
            }
            $locationSelect = '<div class="form-row"><label>' . $lang['location'] . '</label><select class="select" name="location" id="location">' . $options . '</select></div>';
        }

        $tplRes .= '<div class="card m0">
            <div class="name_h1">Редагувати групу: ' . transport_h($row['name']) . '</div>
            <form method="post" action="/?do=transport">
                <input type="hidden" name="act" value="transport_update_isp">
                <input type="hidden" name="isp_id" value="' . (int)$row['id'] . '">
                <div class="form-row"><label>Назва *</label><input type="text" name="name" value="' . transport_h($row['name']) . '" required></div>
                <div class="form-row"><label>Лого (URL)</label><input type="text" name="logo" value="' . transport_h($row['logo']) . '"></div>
                <div class="form-row"><label>Телефон</label><input type="text" name="phone" value="' . transport_h($row['phone']) . '"></div>
                ' . $locationSelect . '
                <div class="form-row"><label>Адреса</label><input type="text" name="address" value="' . transport_h($row['address']) . '"></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Зберегти</button></div>
            </form>
        </div>';
        break;

    case 'isp_delete':
        $id = Clean::int($_GET['id'] ?? 0);
        if (!is_valid_id($id)) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $row = $db->Fast('transport_isp', '*', array('id' => (int)$id));
        if (!$row) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $cnt = $db->Simple("SELECT COUNT(*) AS c FROM transport_onu WHERE isp_id = " . (int)$id);
        $metatags = array('title' => 'Видалити групу', 'description' => 'Видалити групу', 'page' => 'transport_delete_group');
        $tplRes .= '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=transport"><i class="fi fi-rr-angle-left"></i>Групи ONU</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>Видалити групу</span>
        </div>
        <div class="card m0">
            <div class="name_h1">Видалити групу</div>
            <div class="p10">Група: <b>' . transport_h($row['name']) . '</b><br>Прив\'язаних ONU: <b>' . (int)($cnt['c'] ?? 0) . '</b></div>
            <form method="post" action="/?do=transport" class="mt10">
                <input type="hidden" name="act" value="transport_delete_isp">
                <input type="hidden" name="isp_id" value="' . (int)$row['id'] . '">
                <div class="form-row"><label><input type="checkbox" name="with_onu" value="1"> Видалити разом з усіма ONU</label></div>
                <div class="form-actions"><button class="btn btn-danger" type="submit"><i class="fa fa-trash"></i> Видалити</button></div>
            </form>
        </div>';
        break;

    case 'isp':
        $id = Clean::int($_GET['id'] ?? 0);
        if (!is_valid_id($id)) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $isp = $db->Fast('transport_isp', '*', array('id' => (int)$id));
        if (!$isp) {
            transport_redirect($go, '/?do=transport&view=list');
        }

        $onus = $db->SimpleWhile("SELECT * FROM transport_onu WHERE isp_id = " . (int)$id . " ORDER BY id DESC");
        if (!is_array($onus)) {
            $onus = array();
        }

        $locationName = '';
        if (!empty($isp['location'])) {
            $loc = $db->Simple("SELECT name FROM location WHERE id = " . (int)$isp['location']);
            $locationName = isset($loc['name']) ? transport_h($loc['name']) : '';
        }

        $metatags = array('title' => transport_h($isp['name']), 'description' => transport_h($isp['name']), 'page' => 'transport_group');
        $rightMenu = '<div class="card m0">
            <div class="name_detail">' . ($isp['logo'] ? '<center><img src="' . transport_h($isp['logo']) . '" alt="logo" style="width:200px;vertical-align:middle;border-radius:4px"></center>' : '') . '</div>
            <div class="ponbox_details"><center>
                <h2>' . transport_h($isp['name']) . ' <a href="/?do=transport&view=isp_edit&id=' . (int)$isp['id'] . '"><img style="vertical-align:bottom;height:17px;" src="../style/img/settings.svg"></a></h2>
                ' . $locationName . '
            </center></div>
        </div>
        <div class="dashboard_pon tag-filter mt10">
            <a class="snmp_pon_vz" href="/?do=transport&view=isp_add_onu&id=' . (int)$isp['id'] . '">Додати ONU</a>
        </div>';

        $table = '<div class="table mt10">';
        if (!empty($onus)) {
            $table .= '<table class="resp-tab list-onu-olt"><thead><tr><th>Статус</th><th>Комутатор</th><th>MAC/SN</th><th>Розташування</th><th>Додано</th><th>Коментар</th><th></th></tr></thead><tbody>';
            foreach ($onus as $o) {
                $statusValue = (string)$o['status'];
                $reasonValue = (string)$o['reason'];
                if (!empty($o['idonu'])) {
                    $live = $db->Simple("SELECT status, reason FROM onus WHERE idonu = " . (int)$o['idonu'] . " LIMIT 1");
                    if ($live) {
                        $statusValue = ((int)$live['status'] === 1) ? 'online' : 'offline';
                        $reasonValue = (string)$live['reason'];
                    }
                }
                $statusHtml = ($statusValue === 'online')
                    ? (statusTermianl(1)['img'] ?? '<span class="up">online</span>')
                    : reason_onu($statusValue, $reasonValue);

                $switchData = null;
                if (!empty($o['olt'])) {
                    $switchData = $db->Simple("SELECT id, place, locationname FROM switch WHERE id = " . (int)$o['olt'] . " LIMIT 1");
                }

                $linkValue = $o['mac'] ? transport_h($o['mac']) : ($o['sn'] ? transport_h($o['sn']) : 'n/a');
                $onuLink = !empty($o['idonu']) ? '/?do=onu&id=' . (int)$o['idonu'] : '#';
                $switchPlace = !empty($switchData['place']) ? transport_h($switchData['place']) : 'n/a';
                $switchLocation = !empty($switchData['locationname']) ? transport_h($switchData['locationname']) : 'n/a';
                $rowClass = ($statusValue === 'online') ? 'up' : 'down';

                $table .= '<tr class="' . $rowClass . '">
                    <td class="status">' . $statusHtml . '</td>
                    <td class="inface_onu">' . $switchPlace . '</td>
                    <td class="inface_onu"><a href="' . $onuLink . '">' . $linkValue . '</a></td>
                    <td>' . $switchLocation . '</td>
                    <td>' . transport_h($o['added']) . '</td>
                    <td>' . transport_h($o['comment']) . '</td>
                    <td class="nowrap">
                        <form method="post" action="/?do=transport" onsubmit="return confirm(\'Видалити прив\\\'язку ONU?\')" style="display:inline;">
                            <input type="hidden" name="act" value="transport_delete_onu">
                            <input type="hidden" name="isp_id" value="' . (int)$isp['id'] . '">
                            <input type="hidden" name="link_id" value="' . (int)$o['id'] . '">
                            <button class="btn btn-small btn-danger" type="submit"><i class="fa fa-times"></i></button>
                        </form>
                    </td>
                </tr>';
            }
            $table .= '</tbody></table>';
        } else {
            $table .= '<div class="card p10">У цій групі поки немає ONU.</div>';
        }
        $table .= '</div>';

        $tplRes .= '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=main"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=transport"><i class="fi fi-rr-angle-left"></i>Групи ONU</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . transport_h($isp['name']) . '</span>
        </div>
        <div class="container">
            <div class="left-column left-colon">' . $rightMenu . '</div>
            <div class="right-column">' . $table . '</div>
        </div>';
        break;

    case 'isp_add_onu':
        $id = Clean::int($_GET['id'] ?? 0);
        if (!is_valid_id($id)) {
            transport_redirect($go, '/?do=transport&view=list');
        }
        $isp = $db->Fast('transport_isp', '*', array('id' => (int)$id));
        if (!$isp) {
            transport_redirect($go, '/?do=transport&view=list');
        }

        $metatags = array('title' => 'Додати ONU', 'description' => 'Додати ONU', 'page' => 'transport_add_onu');
        $tplRes .= '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=main"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=transport"><i class="fi fi-rr-angle-left"></i>Групи ONU</a>
            <a class="brmhref" href="/?do=transport&view=isp&id=' . (int)$isp['id'] . '"><i class="fi fi-rr-angle-left"></i>' . transport_h($isp['name']) . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати ONU</span>
        </div>';

        $tplRes .= '<div class="card m0">
            <div class="name_h1">Додати ONU до групи: ' . transport_h($isp['name']) . '</div>
            <form method="post" action="/?do=transport">
                <input type="hidden" name="act" value="transport_add_onu">
                <input type="hidden" name="isp_id" value="' . (int)$isp['id'] . '">
                <div class="form-row">
                    <label>ID ONU</label>
                    <input type="number" min="1" name="idonu" placeholder="Наприклад: 12345">
                </div>
                <div class="form-row">
                    <label>MAC</label>
                    <input type="text" name="mac" placeholder="AA:BB:CC:DD:EE:FF">
                </div>
                <div class="form-row">
                    <label>SN</label>
                    <input type="text" name="sn" placeholder="Серійний номер ONU">
                </div>
                <div class="form-row">
                    <label>Коментар</label>
                    <input type="text" name="comment" placeholder="Додаткова інформація">
                </div>
                <div class="form-actions mt10">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Додати</button>
                    <a class="btn btn-default" href="/?do=transport&view=isp&id=' . (int)$isp['id'] . '">Скасувати</a>
                </div>
            </form>
        </div>';
        break;
}

$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', $tplRes);
$tpl->compile('content');
$tpl->clear();
?>
