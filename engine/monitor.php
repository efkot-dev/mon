<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
ini_set('memory_limit', '-1');
$starttime = microtime(true);
$pauseIntervalAll = 40;
$pauseIntervalSignal = 30;
$olt = $olt ?? null;
$tempresult = $tempresult ?? null;
$jobid = $jobid ?? null;
$nextcron = $nextcron ?? null;
$gocheck = false;
$tempdata = [];
$supportonu = false;
$time = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $olt = filter_input(INPUT_POST, 'olt', FILTER_SANITIZE_NUMBER_INT);
    $jobid = filter_input(INPUT_POST, 'jobid', FILTER_SANITIZE_NUMBER_INT);
    $access = filter_input(INPUT_POST, 'access', FILTER_SANITIZE_NUMBER_INT);
    if (is_numeric($olt) && is_numeric($jobid)) {
        $olt = intval($olt);
        $jobid = intval($jobid);
        $access = intval($access);
    }
} else {
    $options = getopt("s:j:a:", ["switch:", "jobid:", "access:"]);
    $olt = $options["s"] ?? $options["switch"] ?? null;
    $jobid = $options["j"] ?? $options["jobid"] ?? null;
    $access = $options["a"] ?? $options["access"] ?? null;
    if (empty($olt) && empty($jobid)) {
        die('correct_system_cron');
    }
}
if (is_numeric($olt)) {
    $getswitch = $db->Fast('switch','*',['id' => $olt]);
    if (!empty($getswitch['id'])) {
        if (!empty($getswitch['timecheck'])) {
            ini_set('max_execution_time', $getswitch['timecheck'] + 50);
        } else {
            ini_set('max_execution_time', 900);
        }
        $gocheck = true;
    } else {
        die('unknown_device');
    }
} else {
    die('unknown_device');
}
if (!$gocheck) {
    die('unknown_cmd');
}
if(!empty($getswitch['monitor']) && $getswitch['monitor']=='no'){
	die('switch_off_monitor');	
}
$getmonitor = new Monitor($getswitch['id'],$getswitch['class'], $db, $logger, $classOLT, $cacheManager, $php_class_device);
$supportonu = $getmonitor->getSupportOnu();
if(isset($supportonu)){
	$db->query("UPDATE switch SET status = 'go', updates = '{$time}', jobid = '0' WHERE id = '{$getswitch['id']}'");
	$tempdata = $getmonitor->start();
	if(is_array_empty($tempdata)){
		$nextcron = true;
	}
}else{
	$db->SQLupdate('switch',['updates'=>$time,'jobid'=>0],['id' => $getswitch['id']]);	
}
if(!empty($getswitch['device']) && $getswitch['device']=='switch'){
	$nextcron = true;
}

if(is_array_empty($tempdata)){
	$tempresult = [];
	$counterall = 0;
	$tempresult = array_map(function ($getdata) use (&$counterall, $pauseIntervalAll, $config) {
		$result = api__($config['monitorapi'],$getdata);	
		$counterall++;
		if ($counterall % $pauseIntervalAll === 0) {
			sleep(rand(1,6));
		}
		return is_array($result) && is_array($getdata) ? array_merge($getdata, $result) : $getdata;
	}, $tempdata);
	if(is_array_empty($tempresult)){
		array_map(function($getdata) use ($getmonitor) {
			match($getdata['pon']) {
				'epon' => $getmonitor->tempSaveEpon($getdata),
				'gpon' => $getmonitor->tempSaveGpon($getdata)
			};
		}, $tempresult);
	}
	$resultrxarray = [];
	$counters = 0;
	$getlistrxcheck = $getmonitor->getListSignal();
	if (is_array_empty($getlistrxcheck)) {
		$resultrxarray = array_map(function ($getrxdata) use (&$counters, $pauseIntervalSignal, $config) {
		$resrxapi = api__($config['monitorapi'],$getrxdata);
		$counters++;
		if ($counters % $pauseIntervalSignal === 0) {
			sleep(rand(1,6));
		}
		return is_array($resrxapi) && is_array($getrxdata) ? array_merge($getrxdata, $resrxapi) : $getrxdata;
		}, $getlistrxcheck);
	}
	if(is_array_empty($resultrxarray)){
		array_map(function($getdatarxont) use ($getmonitor) {
			match($getdatarxont['pon']) {
				'epon' => $getmonitor->tempSaveSignalEpon($getdatarxont),
				'gpon' => $getmonitor->tempSaveSignalGpon($getdatarxont)
				};
		}, $resultrxarray);
	}
}
/*
if (is_array_empty($tempdata)) {
	
    $counterall = 0;
	
    $tempresult = array_map(function ($getdata) use (&$counterall, $pauseIntervalAll, $config, $getmonitor) {
		
		$result = api__($config['monitorapi'], $getdata);
		
		$counterall++;
		
		if ($counterall % $pauseIntervalAll === 0) {
			sleep(rand(1, 4));
		}
		
		return is_array($result) && is_array($getdata) ? array_merge($getdata, $result) : $getdata;
		
	}, $tempdata);
	
    if (is_array_empty($tempresult)) {
		
        array_walk($tempresult, function ($getdata) use ($getmonitor) {
			
            $getdata['pon'] === 'epon' ? $getmonitor->tempSaveEpon($getdata) : $getmonitor->tempSaveGpon($getdata);
			
        });
		
    }
    $counters = 0;
	
    $getlistrxcheck = $getmonitor->getListSignal();   
	
	if (is_array_empty($getlistrxcheck)) {
		
		$resultrxarray = [];
		
		$resultrxarray = array_map(function ($getrxdata) use (&$counters, $pauseIntervalSignal, $config, $getmonitor) {
			
			$resrxapi = api__($config['monitorapi'], $getrxdata);
			
			$counters++;

			if ($counters % $pauseIntervalSignal === 0) {
				sleep(rand(1, 4));
			}

			return is_array($resrxapi) && is_array($getrxdata) ? array_merge($getrxdata, $resrxapi) : $getrxdata;
			
		}, $getlistrxcheck);
		
		if (is_array_empty($resultrxarray)) {
			
			array_walk($resultrxarray, function ($getdatarxont) use ($getmonitor) {
				
				$getdatarxont['pon'] === 'epon' ? $getmonitor->tempSaveSignalEpon($getdatarxont) : $getmonitor->tempSaveSignalGpon($getdatarxont);
				
			});
			
		}
	}
}
*/
$supportport = $getmonitor->getSupportPort();
if($supportport && $nextcron){
	$indexport = $getmonitor->getPort();
	if(is_array_empty($indexport)){
		$getmonitor->savePort($indexport);
	}
}
if(is_array_empty($tempresult)){
	$getmonitor->UpdateInformationOlt();
}
if(!empty($getswitch['id'])){
	$executionTime = (int)microtime(true) - $starttime;
	$db->SQLupdate('switch',['status'=>'no','timecheck'=>$executionTime,'timechecklast'=>(!empty($getswitch['timecheck'])?$getswitch['timecheck']:0)],['id'=>$getswitch['id']]);
	$logger->init(['log'=>'device','type'=>'monitor','descr'=>'sec:['.intval($executionTime).'] '.$lang['monitorfinish'].(isset($tempresult)?' '.count($tempresult).'':($getswitch['device']=='switch'?' switch':' need_check')),'deviceid'=>$getswitch['id'],'who'=>'cron']);
}
if (is_array($mysql_debug = $db->debugSql()) && isset($access) && $access==777) {
    $log_file = ROOT_DIR.'/expor/log/'.$olt.'_mysql_debug.log';
    $handle = fopen($log_file, 'a');
    if ($handle) {
        foreach ($mysql_debug as $mysql_query) {
            $log_entry = $mysql_query['time'].'->'.$mysql_query['query']."\n";
            fwrite($handle, $log_entry);
        }
        fclose($handle);
    }
}
?>
