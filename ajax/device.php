<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
$getType = $getTypeForm = $getsql = null;
$typeMap = [
    1  => 'olt',
    2  => 'switch',
    3  => 'switchl2',
    4  => 'ups',
    11 => 'switch',
    12 => 'battery',
    13 => 'ups',
    14 => 'sfp'
];
if (isset($typeMap[$id])) {
    if ($id >= 11) {
        $getTypeForm = $typeMap[$id];
        $getsql = ($id == 14) ? 'sfp' : 'other';
    } else {
        $getType = $typeMap[$id];
    }
}
$listDevice = getListDevice($getType, $pmon_license);
if (!empty($listDevice)) {
    $nomac = false;
    okno_title($lang['addnewdevice']);
    echo '<form action="/?do=send" method="post" id="formadd">';
    if ($getType) {
        echo '<input name="act" type="hidden" value="savedevice">';

        $pole_device = '<select class="select" name="deviceid" id="device">';
        foreach ($listDevice as $Device) {
            $pole_device .= '<option value="' . $Device['id'] . '">' . $Device['name'] . ' ' . $Device['model'] . '</option>';
        }
        $pole_device .= '</select>';
    } else {
        $pole_device = '<input name="devicemodel" class="input1" id="devicemodel" type="text" value="">';
    }
    echo form(['name' => $lang['model'], 'descr' => $lang['selectdevice'], 'pole' => $pole_device]);
    $group = getListGroup();
    if (is_array($group)) {
        $listgroup = '<select class="select" name="group" id="group">';
        $listgroup .= '<option value="0"></option>';
        foreach ($group as $gr) {
            $listgroup .= '<option value="' . $gr['id'] . '">' . $gr['name'] . '</option>';
        }
        $listgroup .= '</select>';
        echo form(['name' => $lang['group'], 'descr' => '', 'pole' => $listgroup]);
    }
    if ($getType) {
        echo form([
            'name' => $lang['inputnamedevice'],
            'descr' => '',
            'pole' => '<input required name="name" class="input1" id="name" placeholder="Nazva ID" type="text" value="">'
        ]);
        echo form([
            'name' => $lang['ip'],
            'descr' => $lang['ipdescr'],
            'pole' => '<input required name="ip" class="input1" id="ip" type="text" value="">'
        ]);
    }
    if ($getTypeForm === 'switch') {
        echo '<input name="act" type="hidden" value="saveswitch">';
        echo form([
            'name' => $lang['port'],
            'descr' => $lang['countport'],
            'pole' => '<input required id="w80px" name="port" class="input1" placeholder="8" type="text" value="">'
        ]);
        $nomac = true;
    }
    if (!$nomac) {
        echo form([
            'name' => $lang['mac'],
            'descr' => $lang['ipdescr'],
            'pole' => '<input name="mac" class="input1" id="ip" type="text" value="">'
        ]);
    }
    echo form([
        'name' => $lang['sn'],
        'descr' => $lang['supporttmc'],
        'pole' => '<input name="sn" class="input1" id="sn" type="text" value="">'
    ]);
    if ($getType) {
        echo '<div class="subpole">' . $lang['snmp'] . '</div>';
        echo form([
            'name' => 'Community',
            'pole' => '<input required name="community" class="input1" id="ip" placeholder="public" type="text" value="">'
        ]);
    }
    echo '</form>';
    echo '<div class="polebtn"><button type="submit" form="formadd" value="submit">' . $lang['add'] . '</button></div>';
    okno_end();
} else {
    okno_title("PMon Support");
    echo '<div class="subpole">
You have limited rights to use the system. <br>
Contact technical support.<br>
@momotuk88
</div>';
    okno_end();
}
?>
