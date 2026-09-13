<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$result = '';
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/classes/telnet.class.php';
require ROOT_DIR.'/inc/classes/system.class.php';
$tempcache = ROOT_DIR.'/export/cache/';
if (isset($confPMon['ENABLE_BLACKLIST_CDATA12']) && !empty($confPMon['ENABLE_BLACKLIST_CDATA12']) && $confPMon['ENABLE_BLACKLIST_CDATA12'] == 1) {
$timecdata = (isset($confPMon['TIMER_BLACKLIST_CDATA12']) && !empty($confPMon['TIMER_BLACKLIST_CDATA12']) ? $confPMon['TIMER_BLACKLIST_CDATA12']:1);
if(isset($olt) && $olt>0){
	$id_device = intval($olt);
}
if(isset($id_device) && $id_device>0){
	$switch = $db->Simple("SELECT * FROM switch WHERE id = '{$id_device}' LIMIT 1");
	if(empty($switch['id'])){
		die('not_support');
	}
}	
if(!empty($switch['monitor']) && $switch['monitor']=='yes'){
	if(!empty($switch['username']) && !empty($switch['password'])){
		$telnet = new PMonTelnet($switch);	
		$err_num = $telnet->err_num;
		if($err_num){	
			$telnet->err('error '.$telnet->descr($err_num));
		}
		$commands = [
			"enable",
			"config",
			"interface epon 0/0",
		];
		$selectallpon = $db->Multi('switch_pon','*',['oltid'=>$switch['id']]);
		foreach($selectallpon as $idport => $datapon){
			preg_match('/0\/(\d+)/i',$datapon['pon'],$datamatch);
				$command = "show ont black-list ".$datamatch[1]." all";
				$commands[] = $command;
			}
			usleep(2000);
			$result = $telnet->executeCommands($commands);
		}
		$dataonu = array();
		if($result){
			$result = str_replace('0/0', 'p', $result);
			preg_match_all('/\b(.?)\s+\s+p\s+([0-9]+)\s+([A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2})\b/si',$result, $cdata12);
			foreach ($cdata12[3] as $key => $value) {
				$dataonu[md5(strtolower(trim($value)) . trim($cdata12[2][$key]))] = [
					'port' => trim($cdata12[2][$key]),
					'olt' => $switch['id'],
					'ont' => (null !== $cdata12[1][$key] ? trim($cdata12[1][$key]) : ''),
					'mac' => strtolower(trim($value))
				];
			}
		}
	}
	if(isset($dataonu) && is_array($dataonu)){
		$filePath = $tempcache.'switch_'.$switch['id'].'.blacklist12';
		if(file_exists($filePath)) {
			unlink($filePath);
		}
		file_put_contents($filePath, serialize($dataonu));
	}
}
?>
