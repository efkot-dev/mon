<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
ini_set('display_startup_errors', 1); ini_set('display_errors', 1); error_reporting(E_ALL);
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/functions/pmon.php';
$update_time = date('Y-m-d H:i:s');
if (isset($confPMon['PON_HIGH_RISE']) && !empty($confPMon['PON_HIGH_RISE']) && $confPMon['PON_HIGH_RISE'] == 1) {
	$result = $db->SimpleWhile("SELECT * from skyscraper_kv");
	if(isset($result) && !empty($result)){
		foreach($result as $kv){
			$getonu = $db->Simple("SELECT rx, status FROM onus WHERE idonu = '{$kv['idonu']}' LIMIT 1");
			if(!empty($getonu['status'])){
				$updateset[] = "status = ".sql_log($getonu['status'])."";
				if (isset($getonu['status']) && $getonu['status'] == 1) {
					$updateset[] = "`signal` = ".sql_log($getonu['rx'])."";
				}
				$db->query("UPDATE skyscraper_kv SET " . implode(", ", $updateset) . " WHERE id = '{$kv['id']}'");
			}else{
				$db->query("DELETE FROM skyscraper_kv WHERE id = '" . $kv['id'] . "'");
			}
		}
	}
	$result_stats = $db->SimpleWhile("SELECT * FROM skyscraper");
	if(isset($result_stats) && !empty($result_stats)){
		foreach($result_stats as $no){
			$sql_count = $db->Simple("SELECT COUNT(id) AS cont_onu FROM skyscraper_kv WHERE skyscraperid = '{$no['id']}'");
			$sql_on = $db->Simple("SELECT COUNT(id) AS cont_onu_on FROM skyscraper_kv WHERE status = 1 AND skyscraperid = '{$no['id']}' LIMIT 1");
			$on_onu = $sql_on['cont_onu_on'];
			$all_onu = $sql_count['cont_onu'];
			$riznucia = $all_onu - $on_onu;
			$db->query("UPDATE skyscraper SET `update` = '{$update_time}', 
			offline = '{$riznucia}', 
			onus = '{$all_onu}', 
			online = '{$on_onu}' 
			WHERE id = '{$no['id']}'");
		}
	}
	$result_sk = $db->SimpleWhile("SELECT * FROM skyscraper");
	if(isset($result_sk) && !empty($result_sk)){
		foreach($result_sk as $row){
			if ($row['onus'] == $row['offline'] && $row['lastoffline'] != $row['offline']) {
				$sender = '' . $lang['skyscraper'] . ' [b]' . $row['name'] . '[/b] ' . $lang['allonus'] . ' - ' . $row['onus'] . ' ' . $lang['alloffile'] . ' - [b]' . $row['offline'] . '[/b]';
				$db->query("INSERT INTO notification (status, type, system, message, added) VALUES ('1','21','pinger','".$sender."','".$update_time. "')");
				$_list = 'Виникли проблеми ' . $row['name'] . ', ' . $lang['allonus'] . ': ' . $row['onus'] . ', офлайн:  '.$row['offline'].'';
				if (isset($confPMon['PMON_LOG']) && !empty($confPMon['PMON_LOG']) && $confPMon['PMON_LOG'] == 1) {
					if($_list!=false){
						$pmon_log = ['types' => 'skyscraper' , 'message' => $_list, 'status' => 'warning'];
						$sql = PMon($pmon_log,$update_time);
						if($sql){
							$db->query($sql);
						}
					}
				}
			}
			$db->query("UPDATE skyscraper SET offline = ".sql_log($row['offline']).",	lastoffline = ".sql_log($row['offline'])." WHERE id = ".sql_log($row['id']));
		}
	}
}
?>
