<?php
if (!defined('PONMONITOR') || !defined('BACKUP')) {
    die('Hacking attempt!');
}
function backup_zte3($db, $getswitch, $timer_backup, $tempfolder) {
	$filePath = $tempfolder.'olt_'.$getswitch['id'].'_time_'.strtotime($timer_backup).'.backup';
	if(!empty($getswitch['id'])){	
		$telnet = new PMonTelnet($getswitch);   
		$err_num = $telnet->err_num;
		$result = $telnet->do_comand("conf t\r",true);
		$backup_error = true;
		if(preg_match('/\bEnter\b/i', $result)) {
			$result .= $telnet->do_comand("show run\r",true);
		}else{
			$backup_error = false;
		}
		if($backup_error===true){
			if(isset($result)){
				while(strpos($result, "--More--") !== false) {
					$result_backup .= $telnet->do_lite("\r", true);
					$zaput++;
					if(preg_match('/\bend\b/i', $result_backup) || strpos($result_backup, '#') !== false || empty($result_backup)) {
						break;
					}
					#echo $id_device . "-".$zaput."\r";
				}
				$telnet->do_lite("exit\r",true);
				sleep(1);
				return backup_save($result_backup, $filePath);
				/*
				$result_backup = preg_replace('/ --More--/','',$result_backup);
				$result_backup = preg_replace('/ /','',$result_backup);
				if(file_exists($filePath)) {
					unlink($filePath);
				}
				file_put_contents($filePath, $result_backup);
				$telnet->do_lite("exit\r",true);					
				$loger = true;
				*/
			}
		}
	}	
}
function backup_huawei56($db, $getswitch, $timer_backup, $tempfolder) {
	
}
function backup_cdata16_gpon($db, $getswitch, $timer_backup, $tempfolder) {
	$filePath = $tempfolder.'olt_'.$getswitch['id'].'_time_'.strtotime($timer_backup).'.backup';	
	$telnet = new PMonTelnet($getswitch);   
	$backup = '';
	$tmp = $telnet->do_comand("enable\r",true);
	$tmp .= $telnet->do_comand("config\r",true);	
	$tmp .= $telnet->do_comand("show current-config\r",true);
    $lastMatch = '';
    $repeatCount = 0;
    $repeatThreshold = 2;
    $moreFound = true;
	$backup .= $tmp;
    while ($moreFound) {
		$tmp = $telnet->do_lite("\r", true);
		$backup .= $tmp;
        $foundEnd = stripos($tmp, 'end') !== false;
        $foundConfig = stripos($tmp, '(config)#') !== false;
        if ($foundEnd || $foundConfig) {
            $currentMatch = $foundEnd ? 'end' : '(config)#';
            if ($currentMatch === $lastMatch) {
                $repeatCount++;
            } else {
                $lastMatch = $currentMatch;
                $repeatCount = 1;
            }
            if ($repeatCount >= $repeatThreshold) {
                $moreFound = false;
            }
        } else {
            $repeatCount = 0; 
        }
        usleep(100000);
    }
	$telnet->do_lite("exit\r");
	$telnet->do_lite("exit\r");
	return backup_save($backup, $filePath);
}
function backup_cdata12_epon($db, $getswitch, $timer_backup, $tempfolder) {
	$zaput = 1;
	$result_backup = '';		
	$filePath = $tempfolder.'olt_'.$getswitch['id'].'_time_'.strtotime($timer_backup).'.backup';	
	$telnet = new PMonTelnet($getswitch);   
	$result = $telnet->do_comand("enable\r",true);
	$result .= $telnet->do_comand("config\r",true);	
	$result .= $telnet->do_comand("show current-config\r",true);
	while (stripos($result, "more") !== false) {
        $result_backup .= $telnet->do_lite("\r", true);
        $zaput++;
        usleep(100000);		
		if (strpos($result_backup, '#') !== false 
			|| strpos($result_backup, 'configurations') !== false 
				|| strpos($result_backup, 'end') !== false) {
			break;
		}
    }
	$result .= $result_backup;
	$telnet->do_lite("exit\r");
	$telnet->do_lite("exit\r");
	return backup_save($result, $filePath);
}
function backup_save($result_backup, $filePath) {
    if (empty($result_backup)) {
        error_log("No data to save.");
        return false;
    }    
    $result_backup = clean_telnet_result_($result_backup);
    $dir = dirname($filePath);
    if (!is_writable($dir)) {
        error_log("Cannot write to directory: $dir. Permission denied.");
        return false;
    }
    if (file_put_contents($filePath, $result_backup) === false) {
        error_log("Failed to write to file: $filePath.");
        return false;
    }
    return true;
}
function backup_bdcom_epon($db, $getswitch, $timer_backup, $tempfolder) {
	$zaput = 1;
	$result_backup = '';		
	$filePath = $tempfolder.'olt_'.$getswitch['id'].'_time_'.strtotime($timer_backup).'.backup';	
	$telnet = new PMonTelnet($getswitch);   
	$result = $telnet->do_comand("enable\r",true);
	$result .= $telnet->do_comand("config\r",true);	
	$result .= $telnet->do_comand("show configuration\r",true);	
	while (stripos($result, "more") !== false) {
        $result_backup .= $telnet->do_lite("\r", true);
        $zaput++;
        usleep(100000);
		if (strpos($result_backup, '#') !== false 
			|| strpos($result_backup, 'configurations') !== false 
				|| strpos($result_backup, 'linecards') !== false) {
			break;
		}
    }
	$result .= $result_backup;
	$telnet->do_lite("exit\r");
	$telnet->do_lite("exit\r");
	return backup_save($result, $filePath);
}
function cpu_load() {
	$currentLoad = sys_getloadavg()[0];
	$sleepTime = max(1, min(200000, intval($currentLoad * 100000)));
	usleep($sleepTime);	
}
function backup_zte6($db, $getswitch, $timer_backup, $tempfolder) {
	$zaput = 1;
	$result = '';
	$result_backup = '';		
	$filePath = $tempfolder.'olt_'.$getswitch['id'].'_time_'.strtotime($timer_backup).'.backup';	
	$telnet = new PMonTelnet($getswitch);   
	$result .= $telnet->do_comand("conf t\r",true);	
	$result .= $telnet->do_comand("show running-config\r",true);
	while (stripos($result, "more") !== false) {
        $result_backup .= $telnet->do_lite("\r", true);
        $zaput++;
		usleep(100000);
		if (strpos($result_backup, '#') !== false 
			|| strpos($result_backup, 'configurations') !== false 
				|| strpos($result_backup, 'linecards') !== false) {
			break;
		}
    }
	$result .= $result_backup;
	$telnet->do_lite("exit\r");
	$telnet->do_lite("exit\r");
	return backup_save($result, $filePath);
}

function clean_telnet_result_($result_backup) {
    if (empty($result_backup)) {
        return false;
    }
    $result_backup = mb_convert_encoding($result_backup, 'UTF-8', 'UTF-8');
    $result_backup = preg_replace('/[^\P{C}\n]+/u', '', $result_backup);
    $result_backup = preg_replace('/\t{2,}/', '', $result_backup);
    $patterns = [
        '/!!/',
        '/--More-- /',
        '/---- More \( Press \'Q\' to break \) ----/',
        '/--More \( Press \'Q\' to quit \)--/',
        '/\[37D/'
    ];
    $result_backup = preg_replace($patterns, '', $result_backup);
    #$result_backup = preg_replace('/^[^#]*#/m', '', $result_backup);    
    $lines = explode("\n", $result_backup);
    foreach ($lines as &$line) {
        $line = preg_replace('/\s{2,}/', ' ', $line);
        $line = trim($line);
    }
    $result_backup = implode("\n", $lines);
    $result_backup = preg_replace('/(\n!)+\n/', "\n!\n", $result_backup);
    return $result_backup;
}

?>