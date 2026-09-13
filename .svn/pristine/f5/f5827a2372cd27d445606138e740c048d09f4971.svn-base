<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function get_oid_sfp($getswitch) {
    $oidMappings = [
        1 => '1.3.6.1.4.1.3320.101.107.1.3',
        9 => '1.3.6.1.4.1.13464.1.13.2.2.1.6.0',
        10 => '1.3.6.1.4.1.8888.1.13.2.2.1.6.0',
        35 => '1.3.6.1.4.1.34592.1.5.1.1.2.17.2.1.4.1.0',
        41 => '1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0',
        15 => '1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0',
        12 => '1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0',
        11 => '1.3.6.1.4.1.8888.1.14.2.3.3.1.12.2',
        6 => '1.3.6.1.4.1.3902.1082.30.45.2.4.1.2',
		7 => '1.3.6.1.4.1.3902.1015.3.1.13.1.4',
        2 => '1.3.6.1.4.1.3320.10.2.2.1.5',
        14 => [
            'gpon' => '1.3.6.1.4.1.2011.6.128.1.1.2.23.1.4',
            'epon' => '1.3.6.1.4.1.2011.6.128.1.1.2.33.1.4',
        ]
    ];
    if (isset($oidMappings[$getswitch['oidid']])) {
        return $oidMappings[$getswitch['oidid']];
    }
    return false;
}
function render_sfp_value($oidid, $name, $value) {
    $converted = null;
    $unit = '';
    $img = '';
    switch ($oidid) {
        case 1:
            $converted = ($name == 'rx') ? $value / 10 :
                         (($name == 'temp') ? $value / 256 :
                         (($name == 'volt') ? $value / 10000 : null));
            $unit = ($name == 'rx') ? 'dbm' : (($name == 'temp') ? '°C' : 'V');
            break;
        case 6:
        case 7:
            $converted = ($value == 2147483647) ? 0 : $value / 1000;
            $unit = ($name == 'rx') ? 'dbm' : '°C';
            break;
        case 2:
            $converted = $value / 10;
            $unit = ($name == 'rx') ? 'dbm' : (($name == 'temp') ? '°C' : 'V');
            break;
        case 9:
        case 10:
        case 11:
        case 14:
            $converted = ($name == 'rx') ? $value / 100 : (($name == 'temp') ? $value:null);
            $unit = ($name == 'rx') ? 'dbm' : '°C';
            break;
        case 15:
            $converted = $value / 100;
            $unit = 'dbm';
            break;
    }
    $img = ($name == 'rx') ? 'sfpsignal.png' :
       (($name == 'temp') ? 'sfptemp.png' : 'sfpvolt.png');
	$alarmClass = '';
	if ($name == 'temp' && $converted !== null) {
		if ($converted >= 50 && $converted <= 60) {
			$alarmClass = ' alarm_orange';
		} elseif ($converted >= 61  && $converted <= 100) {
			$alarmClass = ' alarm_red';
		}
	}
	if ($converted !== null) {
		printf('<div class="sfp-%s%s"><img src="../style/img/%s"><span>%s</span> %s</div>', 
			$name, $alarmClass, $img, number_format($converted, 2), $unit);
	}
}
function get_data_sfp($oidid, $pon) {
    $oids = [];

    switch ($oidid) {
        case 1:
            $oids = [
                'rx' => "1.3.6.1.4.1.3320.101.107.1.3.".$pon['sfpid'],
                'temp' => "1.3.6.1.4.1.3320.101.107.1.6.".$pon['sfpid'],
                'volt' => "1.3.6.1.4.1.3320.101.107.1.7.".$pon['sfpid']
            ];
            break;
        case 6:
            $oids = [
                'rx' => "1.3.6.1.4.1.3902.1082.30.45.2.4.1.2.".$pon['sfpid'],
                'temp' => "1.3.6.1.4.1.3902.1082.30.45.2.4.1.6.".$pon['sfpid']
            ];
            break;
        case 9:
        case 10:
        case 11:
            if ($pon['sort'] !== null) {
                $base = $oidid == 9 ? '13464.1.13.2.2.1' : ($oidid == 10 ? '8888.1.13.2.2.1' : '8888.1.14.2.3.3.1.12.2');
                $oids = [
                    'rx' => "1.3.6.1.4.1.$base.6.0.".$pon['pon'],
                    'temp' => "1.3.6.1.4.1.$base.5.0.".$pon['pon']
                ];
            }
            break;
        case 12:
        case 15:
            $oids = [
                'rx' => "1.3.6.1.4.1.17409.2.3.3.5.1.6.1.0." . ($pon['sfpid'] + 4)
            ];
            break;
        case 2:
            $oids = [
                'temp' => "1.3.6.1.4.1.3320.10.2.2.1.2.".$pon['sfpid'],
                'volt' => "1.3.6.1.4.1.3320.10.2.2.1.3.$".$pon['sfpid'],
                'rx' => "1.3.6.1.4.1.3320.10.2.2.1.5.".$pon['sfpid']
            ];
            break;
        case 7:
            $oids = [
                'rx' => "1.3.6.1.4.1.3902.1015.3.1.13.1.4.".$pon['sfpid'],
                'temp' => "1.3.6.1.4.1.3902.1015.3.1.13.1.12.".$pon['sfpid']
            ];
            break;
        case 14:
            if (stripos($pon['pon'], 'gpon') !== false) {
                $oids = [
                    'rx' => "1.3.6.1.4.1.2011.6.128.1.1.2.23.1.4.".$pon['sfpid'],
                    'temp' => "1.3.6.1.4.1.2011.6.128.1.1.2.23.1.1.".$pon['sfpid']
                ];
            } elseif (stripos($pon['pon'], 'epon') !== false) {
                $oids = [
                    'rx' => "1.3.6.1.4.1.2011.6.128.1.1.2.33.1.4.".$pon['sfpid'],
                    'temp' => "1.3.6.1.4.1.2011.6.128.1.1.2.33.1.1.".$pon['sfpid']
                ];
            }
            break;
    }

    return $oids;
}

?>