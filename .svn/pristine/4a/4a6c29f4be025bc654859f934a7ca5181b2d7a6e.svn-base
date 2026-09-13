<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

$pages = '';
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;

switch ($act) {
    case 'addgroups':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['group_name']) ? Clean::text($_POST['group_name']) : '';
            $zona = isset($_POST['zona']) ? Clean::text($_POST['zona']) : '';
            if ($name !== '' && in_array($zona, array('global', 'device'), true)) {
                $stmt = $pdo->prepare("INSERT INTO users_groups (name, zona) VALUES (:name, :zona)");
                $stmt->execute(array('name' => $name, 'zona' => $zona));
                $go->go('/?do=groups');
                exit;
            }
        }

        $speedbar_block = '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=groups"><i class="fi fi-rr-angle-left"></i>' . $lang['group'] . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['added'] . '</span>
        </div>';

        $pages .= '
        <div class="card"><div class="namebattery">New groups</div>
            <form action="/?do=groups&act=addgroups" method="post" id="formadd">' .
        formpage(array(
            'img' => 'addconnect.png',
            'name' => $lang['name'],
            'descr' => '',
            'pole' => '<input style="width:69%;" name="group_name" class="input1" type="text" required>'
        )) .
        formpage(array(
            'img' => 'm5.png',
            'name' => $lang['zona'],
            'descr' => '',
            'pole' => '<select class="select" name="zona" id="format">
                <option value="global" selected="">' . $lang['global'] . '</option>
                <option value="device">' . $lang['device'] . '</option>
            </select>'
        )) .
        '<div class="polebtn"><button type="submit" form="formadd" value="submit">' . $lang['addeds'] . '</button></div>
            </form>
        </div>';
        break;

    case 'editgoups':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupName = isset($_POST['group_name']) ? Clean::text($_POST['group_name']) : '';
            if ($groupName !== '' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE users_groups SET name = :name WHERE id = :id");
                $stmt->execute(array('name' => $groupName, 'id' => $id));
                $go->go('/?do=groups');
                exit;
            }
        }

        $stmt = $pdo->prepare("SELECT * FROM users_groups WHERE id = :id");
        $stmt->execute(array('id' => $id));
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$group) {
            $go->go('/?do=groups');
            exit;
        }

        $speedbar_block = '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=groups"><i class="fi fi-rr-angle-left"></i>' . $lang['group'] . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['edit'] . ': ' . groups_h($group['name']) . '</span>
        </div>';

        $pages .= '<div class="card"><div class="namebattery">' . $lang['edit'] . ' ' . groups_h($group['name']) . '</div>
            <form action="/?do=groups&act=editgoups&id=' . (int)$id . '" method="post" id="formadd">' .
            formpage(array(
                'img' => 'addconnect.png',
                'name' => $lang['name'],
                'descr' => '',
                'pole' => '<input type="text" name="group_name" value="' . groups_h($group['name']) . '" required>'
            )) .
            '<div class="polebtn"><button type="submit" form="formadd" value="submit">' . $lang['update'] . '</button></div>
            </form>
        </div>';
        break;

    case 'groupsaccess':
        $speedbar_block = '<div id="onu-speedbar">
            <a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=groups"><i class="fi fi-rr-angle-left"></i>' . $lang['group'] . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['access'] . '</span>
        </div>';

        if ($id <= 0) {
            $go->go('/?do=groups');
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, name, zona FROM users_groups WHERE id = :id");
        $stmt->execute(array('id' => $id));
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$group) {
            $go->go('/?do=groups');
            exit;
        }

        $rulesBySection = groups_collect_allowed_types_grouped($pdo, $confPMon, $lang, (string)$group['zona']);
        $allowedFlat = array();
        foreach ($rulesBySection as $sectionRules) {
            foreach ($sectionRules as $row) {
                $allowedFlat[$row['type']] = true;
            }
        }

        $stmt = $pdo->prepare("SELECT types FROM groupsaccess WHERE gid = :gid");
        $stmt->execute(array('gid' => $id));
        $currentRows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $selectedMap = array();
        foreach ($currentRows as $type) {
            if (isset($allowedFlat[$type])) {
                $selectedMap[$type] = true;
            }
        }

        $tabs = '';
        $panes = '';
        $first = true;
        foreach ($rulesBySection as $sectionKey => $sectionRules) {
            if (empty($sectionRules)) {
                continue;
            }
            $isActive = $first ? ' active' : '';
            $tabs .= '<span class="tab-button' . $isActive . '" data-tab="sec_' . groups_h($sectionKey) . '">' . groups_section_title($sectionKey, $lang) . ' <span class="offes">' . count($sectionRules) . '</span></span>';
            $panes .= '<div class="tab-pane" id="sec_' . groups_h($sectionKey) . '" style="' . ($first ? '' : 'display:none;') . '">';
            foreach ($sectionRules as $rule) {
                $type = (string)$rule['type'];
                $checked = isset($selectedMap[$type]) ? 'checked' : '';
                $panes .= '<label><input type="checkbox" name="access[]" value="' . groups_h($type) . '" ' . $checked . '> ' . $rule['description'] . '</label>';
            }
            $panes .= '</div>';
            $first = false;
        }

        $pages = '<form method="post" action="/?do=groups&act=saveaccess&id=' . (int)$id . '">
            <div class="tabs">' . $tabs . '</div>
            <div class="tab-content">' . $panes . '</div>
            <button type="submit" name="save_access">' . $lang['save'] . '</button>
        </form>
        <script>
        $(document).ready(function(){
            $(".tab-button").click(function(){
                var tabId = $(this).data("tab");
                $(".tab-pane").hide();
                $("#" + tabId).show();
                $(".tab-button").removeClass("active");
                $(this).addClass("active");
            });
        });
        </script>';
        break;

    case 'saveaccess':
        if (isset($_POST['save_access']) && $id > 0) {
            $postedAccess = isset($_POST['access']) && is_array($_POST['access']) ? $_POST['access'] : array();

            $stmt = $pdo->prepare("SELECT zona FROM users_groups WHERE id = :id");
            $stmt->execute(array('id' => $id));
            $group = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($group) {
                $allowed = groups_collect_allowed_types($pdo, $confPMon, $lang, (string)$group['zona']);
                $newMap = array();
                foreach ($postedAccess as $rawType) {
                    $type = Clean::text((string)$rawType);
                    if ($type !== '' && isset($allowed[$type])) {
                        $newMap[$type] = true;
                    }
                }

                $pdo->prepare("DELETE FROM groupsaccess WHERE gid = :gid")->execute(array('gid' => $id));
                if (!empty($newMap)) {
                    $ins = $pdo->prepare("INSERT INTO groupsaccess (gid, types) VALUES (:gid, :types)");
                    foreach (array_keys($newMap) as $type) {
                        $ins->execute(array('gid' => $id, 'types' => $type));
                    }
                }

                $validDeviceGroups = groups_get_device_group_ids($pdo);
                $tag = 'access' . $id;
                $usersStmt = $pdo->prepare("SELECT DISTINCT uid FROM checkaccess WHERE types = :tag");
                $usersStmt->execute(array('tag' => $tag));
                $uids = $usersStmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($uids as $uidRaw) {
                    $uid = (int)$uidRaw;
                    if ($uid <= 0) {
                        continue;
                    }
                    $currentTags = groups_get_user_group_tags($pdo, $uid);
                    groups_sync_user_group_access($pdo, $uid, $currentTags, $validDeviceGroups, $cacheManager, $confPMon);
                }
            }
        }
        $go->go('/?do=groups');
        exit;
        break;

    case 'granted':
        $uid = isset($_GET['uid']) ? Clean::int($_GET['uid']) : 0;
        $cleanGid = isset($_GET['gid']) ? Clean::text($_GET['gid']) : '';
        $gid = (int)str_replace('access', '', $cleanGid);
        $type = isset($_GET['type']) ? Clean::text($_GET['type']) : '';
        if ($uid <= 0 || $gid <= 0 || ($type !== 'add' && $type !== 'del')) {
            $go->go('/?do=groups&act=mangroups');
            exit;
        }

        $validDeviceGroups = groups_get_device_group_ids($pdo);
        $currentTags = groups_get_user_group_tags($pdo, $uid);
        $targetTag = 'access' . $gid;
        $nextMap = array_fill_keys($currentTags, true);
        if ($type === 'add') {
            $nextMap[$targetTag] = true;
        } else {
            unset($nextMap[$targetTag]);
        }
        groups_sync_user_group_access($pdo, $uid, array_keys($nextMap), $validDeviceGroups, $cacheManager, $confPMon);

        $go->go('/?do=groups&act=mangroups');
        exit;
        break;

    case 'updates':
        $data = json_decode(file_get_contents("php://input"), true);
        $uid = isset($data['uid']) ? Clean::int($data['uid']) : 0;
        $groups = isset($data['groups']) && is_array($data['groups']) ? $data['groups'] : array();
        if ($uid > 0) {
            $validDeviceGroups = groups_get_device_group_ids($pdo);
            groups_sync_user_group_access($pdo, $uid, $groups, $validDeviceGroups, $cacheManager, $confPMon);
        }
        exit;
        break;

    case 'getgroups':
        $uid = isset($_GET['uid']) ? Clean::int($_GET['uid']) : 0;
        $html = '';
        if ($uid > 0) {
            $deviceGroups = groups_get_device_groups_map($pdo);
            $selectedTags = groups_get_user_group_tags($pdo, $uid);
            $selectedMap = array_fill_keys($selectedTags, true);

            $html .= '<div class="group-list" data-uid="' . (int)$uid . '">
                <select class="access-select" name="groups[' . (int)$uid . '][]" multiple="multiple" data-uid="' . (int)$uid . '">';
            foreach ($deviceGroups as $tag => $name) {
                $selected = isset($selectedMap[$tag]) ? 'selected' : '';
                $html .= '<option value="' . groups_h($tag) . '" ' . $selected . '>' . groups_h($name) . '</option>';
            }
            $html .= '</select><button class="btn_save_access" data-uid="' . (int)$uid . '">' . $lang['save'] . '</button></div>';
        }
        echo $html;
        exit;
        break;

    case 'mangroups':
        $speedbar_block = '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=groups"><i class="fi fi-rr-angle-left"></i>' . $lang['group'] . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['man_groups'] . '</span>
        </div>';

        $deviceGroups = groups_get_device_groups_map($pdo);
        $us = $pdo->prepare("SELECT id, username, name, class FROM users");
        $us->execute();
        $listUsers = $us->fetchAll(PDO::FETCH_ASSOC);

        $pages .= '<link href="../style/css/select2.css" rel="stylesheet"><script src="../style/js/select2.min.js"></script>';
        $pages .= '<table class="resp-tab module_battery"><tr>
            <th width="15%">' . $lang['users'] . '</th>
            <th width="15%">' . $lang['type_groups'] . '</th>
            <th>' . $lang['accessgood'] . '</th>
        </tr>';

        foreach ($listUsers as $userRow) {
            $selectedTags = groups_get_user_group_tags($pdo, (int)$userRow['id']);
            $selectedMap = array_fill_keys($selectedTags, true);

            $pages .= '<tr>
                <td style="text-align: left;"><font color="#1483e2"><b>' . groups_h($userRow['username']) . '</b></font> ' .
                (!empty($userRow['name']) ? '<br><font color="#222">' . groups_h($userRow['name']) . '</font>' : '') . '</td>
                <td>' . getClassUser($userRow['class']) . '</td>
                <td style="text-align: left;"><div class="td_granted">';

            foreach ($deviceGroups as $tag => $name) {
                if (!isset($selectedMap[$tag])) {
                    continue;
                }
                $pages .= '<a href="/?do=groups&act=granted&type=del&uid=' . (int)$userRow['id'] . '&gid=' . groups_h($tag) . '"><div class="granted">' . groups_h($name) . '</div></a>';
            }

            $pages .= '</div><img id="btn-' . (int)$userRow['id'] . '" class="btn_add_access" src="../style/img/add_connect_line.png" data-uid="' . (int)$userRow['id'] . '">
                <div class="group-list" data-uid="' . (int)$userRow['id'] . '" style="display:none;"></div>
            </td></tr>';
        }

        $pages .= '</table>';
        $pages .= '<script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".btn_add_access").forEach(function(button) {
                button.addEventListener("click", function () {
                    var uid = this.getAttribute("data-uid");
                    var tr = this.closest("tr");
                    var list = tr.querySelector(".group-list[data-uid=\'" + uid + "\']");
                    if (!list) { return; }
                    $(".td_granted").hide();
                    $("#btn-" + uid).hide();
                    if (list.style.display === "none") {
                        fetch("/?do=groups&act=getgroups&uid=" + uid)
                            .then(function(response){ return response.text(); })
                            .then(function(html){
                                list.innerHTML = html;
                                $(list).find(".access-select").select2({
                                    placeholder: "Виберіть доступи",
                                    width: "100%"
                                });
                                list.style.display = "block";
                                list.querySelector(".btn_save_access").addEventListener("click", function () {
                                    var select = list.querySelector(".access-select[data-uid=\'" + uid + "\']");
                                    var selected = $(select).val() || [];
                                    fetch("/?do=groups&act=updates", {
                                        method: "POST",
                                        headers: { "Content-Type": "application/json" },
                                        body: JSON.stringify({ uid: uid, groups: selected })
                                    }).then(function () {
                                        list.style.display = "none";
                                        location.reload();
                                    });
                                });
                            });
                    } else {
                        list.style.display = "none";
                    }
                });
            });
        });
        </script>';
        break;

    case 'addusers':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedUserIds = isset($_POST['user_ids']) && is_array($_POST['user_ids']) ? $_POST['user_ids'] : array();
            $groupId = isset($_POST['group_id']) ? Clean::int($_POST['group_id']) : 0;
            if ($groupId > 0) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE groups = :group_id");
                $stmt->execute(array('group_id' => $groupId));
                $currentUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $cleanSelected = array();
                foreach ($selectedUserIds as $rawUid) {
                    $uid = (int)Clean::int($rawUid);
                    if ($uid > 0) {
                        $cleanSelected[$uid] = true;
                    }
                }

                $usersToAdd = array_diff(array_keys($cleanSelected), $currentUsers);
                $usersToRemove = array_diff($currentUsers, array_keys($cleanSelected));

                if (!empty($usersToAdd)) {
                    $up = $pdo->prepare("UPDATE users SET groups = :group_id WHERE id = :user_id");
                    foreach ($usersToAdd as $uid) {
                        $up->execute(array('group_id' => $groupId, 'user_id' => (int)$uid));
                    }
                }

                if (!empty($usersToRemove)) {
                    $up = $pdo->prepare("UPDATE users SET groups = NULL WHERE id = :user_id");
                    foreach ($usersToRemove as $uid) {
                        $up->execute(array('user_id' => (int)$uid));
                    }
                }
            }
            $go->go('/?do=groups');
            exit;
        }

        $groupId = isset($_GET['group_id']) ? Clean::int($_GET['group_id']) : 0;
        $stmt = $pdo->prepare("SELECT id, username, groups FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT id, name FROM users_groups");
        $stmt->execute();
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $speedbar_block = '
        <div id="onu-speedbar">
            <a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=groups"><i class="fi fi-rr-angle-left"></i>' . $lang['group'] . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['edit'] . '</span>
        </div>';

        $pages .= '<div class="card"><div class="namebattery">' . $lang['edit'] . '</div>
            <form action="/?do=groups&act=addusers" method="post" id="formadd">';

        $usersList = '<div class="us_list">';
        foreach ($users as $user) {
            $checked = ((int)$user['groups'] === (int)$groupId) ? 'checked' : '';
            $disabled = (!is_null($user['groups']) && (int)$user['groups'] !== (int)$groupId) ? 'disabled' : '';
            $usersList .= '<div><input type="checkbox" name="user_ids[]" value="' . (int)$user['id'] . '" ' . $checked . ' ' . $disabled . '> ' . groups_h($user['username']) . '</div>';
        }
        $usersList .= '</div>';
        $pages .= formpage(array('img' => 'addconnect.png', 'name' => $lang['users'], 'descr' => '', 'pole' => $usersList));

        $groupsList = '<select name="group_id" required>';
        foreach ($groups as $group) {
            $groupsList .= '<option value="' . (int)$group['id'] . '" ' . ((int)$groupId === (int)$group['id'] ? 'selected' : '') . '>' . groups_h($group['name']) . '</option>';
        }
        $groupsList .= '</select>';
        $pages .= formpage(array('img' => 'addconnect.png', 'name' => $lang['group'], 'descr' => '', 'pole' => $groupsList));

        $pages .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">' . $lang['update'] . '</button></div>
            </form>
        </div>';
        break;

    default:
        $speedbar_block = '<div id="onu-speedbar">
            <a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['group'] . '</span>
        </div>';

        $stmt = $pdo->prepare("SELECT id, name, zona FROM users_groups ORDER BY id DESC");
        $stmt->execute();
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pages .= '<table class="resp-tab module_battery"><tr>
            <th>' . $lang['name'] . '</th>
            <th width="15%">' . $lang['type_groups'] . '</th>
            <th>' . $lang['functions'] . '</th>
            <th></th>
        </tr>';

        foreach ($groups as $group) {
            $pages .= '<tr>
                <td class="grops-table" style="text-align:left;">' . ($group['zona'] === 'device' ? '<img src="../style/img/settings.png" style="vertical-align:text-bottom;"> ' : '') . groups_h($group['name']) . '</td>
                <td class="grops-table">' . (isset($lang['gr_' . $group['zona']]) ? $lang['gr_' . $group['zona']] : groups_h($group['zona'])) . '</td>
                <td class="grops-table"><a href="/?do=groups&act=groupsaccess&id=' . (int)$group['id'] . '">' . $lang['setups'] . '</a></td>
                <td class="grops-table"><a href="/?do=groups&act=editgoups&id=' . (int)$group['id'] . '">' . $lang['edit'] . '</a></td>
            </tr>';

            $usrStmt = $pdo->prepare("SELECT id, username FROM users WHERE groups = :gid");
            $usrStmt->execute(array('gid' => (int)$group['id']));
            $users = $usrStmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($users)) {
                $pages .= '<tr><td class="grops-users" colspan="4">';
                foreach ($users as $user) {
                    $pages .= '<span class="users"><img src="../style/img/add-user.png"><a href="/">' . groups_h($user['username']) . '</a></span>';
                }
                $pages .= '<a class="addeds_user" href="/?do=groups&act=addusers&group_id=' . (int)$group['id'] . '">' . $lang['addeds'] . '</a>';
                $pages .= '</td></tr>';
            } elseif ($group['zona'] === 'global') {
                $pages .= '<tr><td colspan="4"><a href="/?do=groups&act=addusers">' . $lang['addeds'] . '</a></td></tr>';
            }
        }

        $pages .= '</table>';
        $pages .= '<table><tbody><tr><td>
            <a href="/?do=groups&act=addgroups" class="obladd">' . $lang['add_groups'] . '</a>
            <a href="/?do=groups&act=mangroups" class="obladd">' . $lang['man_groups'] . '</a>
        </td></tr></tbody></table>';
        break;
}

$metatags = array('title' => $lang['group'], 'description' => $lang['group'], 'page' => 'groups');
$tpl->load_template('pages.tpl');
$tpl->set('{result}', $pages);
$tpl->set('{speedbar}', $speedbar_block);
$tpl->compile('content');
$tpl->clear();

function groups_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function groups_section_title($sectionKey, $lang) {
    if ($sectionKey === 'system') {
        return isset($lang['rules_pmonsetup']) ? $lang['rules_pmonsetup'] : 'System';
    }
    if ($sectionKey === 'switch') {
        return isset($lang['allswitch']) ? $lang['allswitch'] : 'Switch';
    }
    if ($sectionKey === 'ping') {
        return 'PING3';
    }
    if ($sectionKey === 'alarm_ping3') {
        return 'Alarm PING3';
    }
    if ($sectionKey === 'building') {
        return 'Building';
    }
    return $sectionKey;
}

function groups_collect_allowed_types($pdo, $confPMon, $lang, $zona) {
    $grouped = groups_collect_allowed_types_grouped($pdo, $confPMon, $lang, $zona);
    $flat = array();
    foreach ($grouped as $sectionRows) {
        foreach ($sectionRows as $rule) {
            $flat[(string)$rule['type']] = true;
        }
    }
    return $flat;
}

function groups_collect_allowed_types_grouped($pdo, $confPMon, $lang, $zona) {
    $out = array(
        'system' => array(),
        'ping' => array(),
        'alarm_ping3' => array(),
        'switch' => array(),
        'building' => array(),
    );

    if ($zona === 'global') {
        $stmt = $pdo->prepare("SELECT types, descr FROM rules");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $rule) {
            $descr = isset($lang[$rule['descr']]) ? $lang[$rule['descr']] : $rule['descr'];
            $out['system'][] = array('type' => $rule['types'], 'description' => groups_h($descr));
        }
    }

    $stmt = $pdo->prepare("SELECT id, device, place FROM switch");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $dev) {
        $type = 'dev' . (int)$dev['id'];
        $descr = ($dev['device'] === 'olt' ? '<span class="signal3">PON</span>' : '<span class="signal5">Switch</span>') . ' ' . groups_h($dev['place']);
        $out['switch'][] = array('type' => $type, 'description' => $descr);
    }

    if (isset($confPMon['PING3']) && (int)$confPMon['PING3'] === 1) {
        $stmt = $pdo->prepare("SELECT id, name FROM mon_ping3");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ping) {
            $out['ping'][] = array('type' => 'ping3_' . (int)$ping['id'], 'description' => 'PING3, ' . groups_h($ping['name']));
        }
    }

    if (isset($confPMon['SECURITY_PING3']) && (int)$confPMon['SECURITY_PING3'] === 1) {
        $stmt = $pdo->prepare("SELECT id, name FROM alarm_ping3");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $alarm) {
            $out['alarm_ping3'][] = array('type' => 'alarm_ping3_' . (int)$alarm['id'], 'description' => 'Alarm, ' . groups_h($alarm['name']));
        }
    }

    if ($zona === 'global' && isset($confPMon['PON_HIGH_RISE']) && (int)$confPMon['PON_HIGH_RISE'] === 1) {
        $stmt = $pdo->prepare("SELECT id, name FROM skyscraper");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $home) {
            $out['building'][] = array('type' => 'building_' . (int)$home['id'], 'description' => groups_h($home['name']));
        }
    }

    return $out;
}

function groups_get_device_groups_map($pdo) {
    $stmt = $pdo->prepare("SELECT id, name FROM users_groups WHERE zona = 'device' ORDER BY id DESC");
    $stmt->execute();
    $map = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map['access' . (int)$row['id']] = (string)$row['name'];
    }
    return $map;
}

function groups_get_device_group_ids($pdo) {
    $stmt = $pdo->prepare("SELECT id FROM users_groups WHERE zona = 'device'");
    $stmt->execute();
    $ids = array();
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $intId = (int)$id;
        if ($intId > 0) {
            $ids[$intId] = true;
        }
    }
    return $ids;
}

function groups_get_user_group_tags($pdo, $uid) {
    $stmt = $pdo->prepare("SELECT types FROM checkaccess WHERE uid = :uid AND types LIKE 'access%'");
    $stmt->execute(array('uid' => (int)$uid));
    $tags = array();
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $tag) {
        $tag = (string)$tag;
        if ($tag !== '') {
            $tags[$tag] = true;
        }
    }
    return array_keys($tags);
}

function groups_sync_user_group_access($pdo, $uid, $selectedTags, $validDeviceGroups, $cacheManager, $confPMon) {
    $uid = (int)$uid;
    if ($uid <= 0) {
        return;
    }

    $selectedDeviceTagMap = array();
    if (is_array($selectedTags)) {
        foreach ($selectedTags as $rawTag) {
            $tag = Clean::text((string)$rawTag);
            if (strpos($tag, 'access') !== 0) {
                continue;
            }
            $gid = (int)str_replace('access', '', $tag);
            if ($gid > 0 && isset($validDeviceGroups[$gid])) {
                $selectedDeviceTagMap['access' . $gid] = true;
            }
        }
    }

    $stmt = $pdo->prepare("SELECT types FROM checkaccess WHERE uid = :uid AND types LIKE 'access%'");
    $stmt->execute(array('uid' => $uid));
    $currentTags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $currentDeviceTagMap = array();
    foreach ($currentTags as $tag) {
        $tag = (string)$tag;
        $gid = (int)str_replace('access', '', $tag);
        if ($gid > 0 && isset($validDeviceGroups[$gid])) {
            $currentDeviceTagMap['access' . $gid] = true;
        }
    }

    $insertTag = $pdo->prepare("INSERT INTO checkaccess (uid, types) VALUES (:uid, :types)");
    foreach (array_keys($selectedDeviceTagMap) as $tag) {
        if (!isset($currentDeviceTagMap[$tag])) {
            $insertTag->execute(array('uid' => $uid, 'types' => $tag));
        }
    }

    $deleteTag = $pdo->prepare("DELETE FROM checkaccess WHERE uid = :uid AND types = :types");
    foreach (array_keys($currentDeviceTagMap) as $tag) {
        if (!isset($selectedDeviceTagMap[$tag])) {
            $deleteTag->execute(array('uid' => $uid, 'types' => $tag));
        }
    }

    $allManagedTypeMap = array();
    foreach (array_keys($validDeviceGroups) as $gid) {
        $stmtRules = $pdo->prepare("SELECT types FROM groupsaccess WHERE gid = :gid");
        $stmtRules->execute(array('gid' => (int)$gid));
        foreach ($stmtRules->fetchAll(PDO::FETCH_COLUMN) as $type) {
            $type = (string)$type;
            if ($type !== '') {
                $allManagedTypeMap[$type] = true;
            }
        }
    }

    $desiredTypeMap = array();
    foreach (array_keys($selectedDeviceTagMap) as $tag) {
        $gid = (int)str_replace('access', '', $tag);
        if ($gid <= 0) {
            continue;
        }
        $stmtRules = $pdo->prepare("SELECT types FROM groupsaccess WHERE gid = :gid");
        $stmtRules->execute(array('gid' => $gid));
        foreach ($stmtRules->fetchAll(PDO::FETCH_COLUMN) as $type) {
            $type = (string)$type;
            if ($type !== '') {
                $desiredTypeMap[$type] = true;
            }
        }
    }

    $stmtUser = $pdo->prepare("SELECT types FROM checkaccess WHERE uid = :uid");
    $stmtUser->execute(array('uid' => $uid));
    $userTypeMap = array();
    foreach ($stmtUser->fetchAll(PDO::FETCH_COLUMN) as $type) {
        $type = (string)$type;
        if ($type !== '') {
            $userTypeMap[$type] = true;
        }
    }

    $insertType = $pdo->prepare("INSERT INTO checkaccess (uid, types) VALUES (:uid, :types)");
    foreach (array_keys($desiredTypeMap) as $type) {
        if (!isset($userTypeMap[$type])) {
            $insertType->execute(array('uid' => $uid, 'types' => $type));
        }
    }

    $deleteType = $pdo->prepare("DELETE FROM checkaccess WHERE uid = :uid AND types = :types");
    foreach (array_keys($allManagedTypeMap) as $type) {
        if (!isset($desiredTypeMap[$type]) && isset($userTypeMap[$type])) {
            $deleteType->execute(array('uid' => $uid, 'types' => $type));
        }
    }

    groups_clear_user_cache($uid, $cacheManager, $confPMon);
}

function groups_clear_user_cache($uid, $cacheManager, $confPMon) {
    if (isset($confPMon['CACHE']) && (int)$confPMon['CACHE'] === 1) {
        $cacheManager->delete("user_access_" . (int)$uid);
        del_cache_simple_sql('checkaccess_' . (int)$uid);
    }
}
?>
