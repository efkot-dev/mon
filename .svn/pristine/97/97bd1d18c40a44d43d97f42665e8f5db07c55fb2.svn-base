<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/classes/telnet.class.php';
require ROOT_DIR.'/inc/classes/system.class.php';
$tempcache = ROOT_DIR.'/export/cache/';
if (isset($confPMon['ENABLE_BLACKLIST_CDATA11']) && !empty($confPMon['ENABLE_BLACKLIST_CDATA11']) && $confPMon['ENABLE_BLACKLIST_CDATA11'] == 1) {
	if(isset($olt) && $olt>0){
		$id_device = intval($olt);
	}
	if(isset($id_device) && $id_device>0){
		$tempcache = ROOT_DIR.'/export/cache/';
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
			$commands = ["show running-config auth"];
			$result = $telnet->executeCommands($commands);
		}
		$dataonu = array();
		if(isset($result)){
			preg_match_all('/add (\d+) onu ([A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2})\b/si', $result, $matches, PREG_SET_ORDER);
			$dataonu = array();
			foreach($matches as $value){
				$dataonu[md5(trim($value[2]).trim($value[1]))] = [
					'olt' => $switch['id'],
					'port' => trim($value[1]),
					'mac' => str_replace('-',':',strtolower(trim($value[2])))
				];
			}
		}
		if(isset($dataonu) && is_array($dataonu)){
			$filePath = $tempcache.'switch_'.$switch['id'].'.blacklist11';
			if(file_exists($filePath)) {
				unlink($filePath);
			}
			file_put_contents($filePath, serialize($dataonu));
		}
	}
}
?>
