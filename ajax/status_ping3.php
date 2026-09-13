<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
$result = '';
$array_ping3 = [];
$ping3_list = $db->SimpleWhile("SELECT * FROM mon_ping3");
$groups_raw = $db->SimpleWhile("SELECT * FROM groups WHERE group_types = 3");
$groups = [];
foreach ($groups_raw as $g) {
    $groups[$g['id']] = $g;
}
foreach ($ping3_list as $p) {
    if (!$access->get('ping3_' . $p['id'])) continue;
    $status = ($p['energy'] == 2 ? 'no' : 'yes');
    $item = [
        'id' => $p['id'],
        'name' => $p['name'],
        'volt' => $p['volt'],
        'temp' => $p['temp'],
        'monitor' => $p['monitor'],
        'data' => $p
    ];
    if (!empty($p['groups'])) {
        $array_ping3['groups'][$status][$p['groups']]['list'][$p['id']] = $item;
    } else {
        $array_ping3['nogroups'][$status][$p['id']] = $item;
    }
}
function render_ping3_block($title, $list, $color = '#06a706') {
    $html = '<div class="name_region">'.$title.' <font color="'.$color.'">Все ок</font></div>';
    $html .= '<div id="ping3">';
    foreach ($list as $it) {
        $html .= '
        <a href="/?do=ping3&act=view&id='.$it['id'].'" class="battery '.($it['monitor']=='no'?'nominotor':'').'">
            <div class="battery-img">
                <span class="ping3_uptime">'.aftertime_cut($it['data']['energytime']).'</span>
                '.monitorPing3img($it['data']).'
                <div class="battery-volt">'.($it['volt'] ?: 0).'v</div>
            </div>
            <div class="battery-name">'.$it['name'].'</div>
            '.get_connect_battery($it['id']).'
        </a>';
    }
    $html .= '</div>';
    return $html;
}
function render_ping3_block_red($title, $list) {
    $html = '<div class="name_region"><font color="red">'.$title.'</font></div>';
    $html .= '<div id="ping3">';
    foreach ($list as $it) {
        $html .= '
        <a href="/?do=ping3&act=view&id='.$it['id'].'" class="battery '.($it['monitor']=='no'?'nominotor':'').'">
            <div class="battery-img">
                <span class="ping3_uptime_red">'.aftertime_cut($it['data']['energytime']).'</span>
                '.monitorPing3img($it['data']).'
                <div class="battery-volt">'.($it['volt'] ?: 0).'v</div>
            </div>
            <div class="battery-name red">'.$it['name'].'</div>
            '.get_connect_battery($it['id']).'
        </a>';
    }
    $html .= '</div>';
    return $html;
}
$result .= '<div class="work_region">';
$result .= '<div class="block_ping3" style="margin: 0;">';
if (!empty($array_ping3['groups']['yes'])) {
    foreach ($groups as $gid => $g) {
        if (!empty($array_ping3['groups']['yes'][$gid]['list'])) {
            $result .= render_ping3_block($g['name'], $array_ping3['groups']['yes'][$gid]['list']);
        }
    }
}
if (!empty($array_ping3['nogroups']['yes'])) {
    $result .= render_ping3_block('Без груп', $array_ping3['nogroups']['yes']);
}
$result .= '</div><div class="block_ping3_border"></div>';
$result .= '<div class="block_ping3" style="margin: 0;">';
if (!empty($array_ping3['groups']['no'])) {
    foreach ($groups as $gid => $g) {
        if (!empty($array_ping3['groups']['no'][$gid]['list'])) {
            $result .= render_ping3_block_red('Увага! Відсутність живлення '.$g['name'], $array_ping3['groups']['no'][$gid]['list']);
        }
    }
} else {
    $result .= '<div class="zariadka"><img style="height: 60px;" src="../style/img/zariadka.gif"></div>';
}
if (!empty($array_ping3['nogroups']['no'])) {
    $result .= render_ping3_block_red('Без груп', $array_ping3['nogroups']['no']);
}
$result .= '</div></div>';
echo $result;
