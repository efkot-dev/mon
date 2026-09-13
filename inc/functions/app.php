<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function reason_onu_app($status, $reason) {
	if(isset($status) && $status==2){
        switch ($reason) {
			case "err1":
				return 'ont_offline';
			break;				
			case "err8":
				return 'ont_offline';
			break;				
			case "err6":
				return 'ont_offline';
			break;					
			case "err0":
				return 'ont_offline';
			break;				
			case 'err59':
				return 'ont_offline';
			break;
			default:
				return 'ont_offline';
		}
	}else{
		return 'ont_online';
	}	
}
function getColorBySignal($signal) {
	$signala = (int)str_replace('-', '', $signal);
    if (!$signal || $signal == '-70') {
        return 'red';
    }
    if ($signala >= 2 && $signala <= 10) {
        return '#1d7dd1';
    } elseif ($signala > 10 && $signala <= 25.99) {
        return '#03be03';
    } elseif ($signala <= 26.00) {
        return 'red';
    } else {
        return 'red';
    }
}

function convertDate($inputDate) {
    $dateParts = explode('/', $inputDate);
    if (count($dateParts) === 3) {
        $day = $dateParts[0];
        $month = $dateParts[1];
        $year = $dateParts[2];
        $currentHour = date('H');
        $currentMinute = date('i');
        $formattedDate = date("Y-m-d H:i:00", mktime($currentHour, $currentMinute, 0, $month, $day, $year));        
        return $formattedDate;
    } else {
        return false;
    }
}
function app_format_mac($mac,$format){
	$mac = str_replace([' ', '.', '-', ':'], '', $mac);
	$mac = strtolower($mac);
	return match($format) {
		1 => preg_replace('/(.{2})/', '\1:', $mac, 5),
		2 => preg_replace('/(.{4})/', '\1.', $mac, 2),
		3 => preg_replace('/(.{4})/', '\1-', $mac, 2),
		4 => preg_replace('/(.{4})/', '\1:', $mac, 2),
		5 => preg_replace('/(.{2})/', '\1.', $mac, 5),
		default => $mac,
	};
}
?>