<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('BACKUP',true);
$timer = date('Y-m-d H:i:s');
$timer_backup = date('Y-m-d');
$starttime = microtime(true);
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/classes/telnet.class.php';
require ROOT_DIR.'/inc/functions/backup.php';
$tempfolder = ROOT_DIR.'/file/backup/';
$loger = false;
if(isset($olt) && $olt>0){
	$id_device = intval($olt);
}
$zaput = 1;
$result_backup = '';
if(isset($id_device) && $id_device>0) {	
	$getswitch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	if (isset($getswitch['oidid']) && $getswitch['oidid']>0) {
		if ($getswitch['oidid'] == 1) {
			$loger = backup_bdcom_epon($db, $getswitch, $timer, $tempfolder);
		}elseif($getswitch['oidid'] == 12){
			$loger = backup_cdata16_gpon($db, $getswitch, $timer, $tempfolder);
		}elseif($getswitch['oidid'] == 6){		
			$loger = backup_zte6($db, $getswitch, $timer, $tempfolder);		
		}elseif(($getswitch['oidid'] == 7) || ($getswitch['oidid'] == 34)){
			$loger = backup_zte3($db, $getswitch, $timer, $tempfolder);		
		}elseif(($getswitch['oidid'] == 14) || ($getswitch['oidid'] == 33)){
			#$loger = backup_huawei56($db, $getswitch, $timer, $tempfolder);
		}elseif($getswitch['oidid'] == 15){
			$loger = backup_cdata12_epon($db, $getswitch, $timer, $tempfolder);
		}
	}
	// HUAWEI	
	if (isset($getswitch['oidid']) && ($getswitch['oidid'] == 14 || $getswitch['oidid'] == 33)) {
		$filePath = $tempfolder.'olt_'.$getswitch['id'].'_time_'.strtotime($timer_backup).'.backup';
		$telnet = new PMonTelnet($getswitch);   
		$err_num = $telnet->err_num;
		$result = $telnet->do_comand("enable\r",true);
		$result .= $telnet->do_comand("config\r",true);
		$result .= $telnet->do_comand("display current-configuration\r",true);
		if(preg_match('/\bport\b/i', $result)) {
			$result .= $telnet->do_comand("\r",true);
		}else{
			$backup_error = false;
		}
		while (stripos($result, "more") !== false) {
            $result_backup .= $telnet->do_lite("\r", true);
            $zaput++;
            usleep(100000);
            if (stripos($result, "return") !== false) {
				break;
			}
            if (preg_match('/<source-interface>/i', $result_backup) || empty($result_backup)) {
				break;
			}
        }
		$result_backup = clean_telnet_result($result_backup);
		if(file_exists($filePath)) {
			@unlink($filePath);
		}
		file_put_contents($filePath, $result_backup);
		$loger = true;
	}
	if($loger == true){
		$executiontime = (int)microtime(true) - $starttime;
		$logger->init([
			'log'=>'device','type'=>'backup',
			'descr'=>'Backup '.$timer_backup.' completed in '.sprintf('%.2f',$executiontime).' seconds',
			'deviceid'=>$id_device, 'who'=>'cron']);
	}else{
		$logger->init(['log'=>'device','type'=>'backup','descr'=>'Error Backup','deviceid'=>$id_device,'who'=>'cron']);			
	}
}
exit();
?>
