<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/functions/pmon.php';
if (isset($confPMon['ONU_LOS']) && !empty($confPMon['ONU_LOS']) && $confPMon['ONU_LOS'] == 1) {
	$allswitch = $db->SimpleWhile("SELECT id, place, model, inf FROM switch WHERE device = 'olt'");
	if(count($allswitch)>0){
		foreach($allswitch as $switch){
			$arrayswitch[$switch['id']] = [
				'swid' => $switch['id'],'place' => $switch['place'],'model' => $switch['model'] . $switch['inf']
			];
		}
	}
	$currentTime = strtotime($timer);
	$tempNewOnu = $db->Simple("SELECT * FROM tempdate WHERE file = 'los_onu'");
	$lastProcessed = $tempNewOnu ? strtotime($tempNewOnu['last_processed']) : strtotime('-10 minutes');
	$lastProcessedDate = date('Y-m-d H:i:s', $lastProcessed);
	$where_onus = "WHERE offline > '{$lastProcessedDate}'";
	$orderby = "AND (status = '2' AND (reason = 'err8' OR reason = 'err6')) ORDER BY offline DESC";
	$sql = ("SELECT inface, olt, type, mac, sn, reason, offline FROM onus $where_onus $orderby");
	$sql_reason_onu = $db->SimpleWhile($sql);
	if(isset($sql_reason_onu) && count($sql_reason_onu)>0){
		foreach($sql_reason_onu as $ont){
			$sender = '[b]'.$arrayswitch[$ont['olt']]['place'].'[/b] [b]'.$ont['type'].' '.$ont['inface'].'[/b]: '.$lang[$ont['reason']].' [icon-time]'.$ont['offline'].' ('.aftertime($ont['offline']).')';
			$db->SQLinsert('notification',['status'=>1,'type'=>$ont['olt'],'system'=>'pinger','message'=>$sender,'added'=>date('Y-m-d H:i:s')]);
		}
	}
	$db->query("INSERT INTO tempdate (file, last_processed) VALUES ('los_onu', '".date('Y-m-d H:i:s', $currentTime)."')  ON DUPLICATE KEY UPDATE last_processed = '".date('Y-m-d H:i:s', $currentTime)."'");
}
$old_dublicat_data = $db->Simple("SELECT data FROM tempdate WHERE file = 'dublicat_onu'");
$old_dublicat = json_decode($old_dublicat_data['data'] ?? '[]', true);
$sql_dubl = $db->SimpleWhile("
    SELECT mac, sn, COUNT(*) as count 
    FROM onus 
    WHERE 
        ((mac IS NOT NULL AND mac != '--' AND mac != '00:00:00:00:00:00') OR 
        (sn IS NOT NULL AND sn != '--' AND sn != '00:00:00:00:00:00')) 
        AND olt > 0
    GROUP BY mac, sn 
    HAVING count > 1
");
$new_duplicates = [];
$removed_duplicates = [];
$old_keys = [];
foreach ($old_dublicat as $item) {
    $key = ($item['mac'] ?: '') . '_' . ($item['sn'] ?: '');
    $old_keys[$key] = $item;
}
$current_keys = [];
foreach ($sql_dubl as $item) {
    $key = ($item['mac'] ?: '') . '_' . ($item['sn'] ?: '');
    $current_keys[$key] = $item;
    if (!isset($old_keys[$key])) {
        $new_duplicates[] = $item;
    }
}
foreach ($old_keys as $key => $item) {
    if (!isset($current_keys[$key])) {
        $removed_duplicates[] = $item;
    }
}
$all_duplicates = $sql_dubl;
$data = json_encode($all_duplicates);
$timer = date('Y-m-d H:i:s');
$db->query("INSERT INTO tempdate (file, data) VALUES ('dublicat_onu', '" . $data . "')	ON DUPLICATE KEY UPDATE	data = '" . $data . "',	updated_at = '{$timer}',	last_processed = '{$timer}'");
if(isset($new_duplicates) && !empty($new_duplicates)){
	foreach($new_duplicates as $new_ont){
		$where_ont = " mac = '{$new_ont['mac']}' OR sn = '{$new_ont['mac']}'";
		$sqlonu = $db->SimpleWhile("SELECT inface, type, status, olt FROM onus WHERE {$where_ont}");
		$_list = '';
		foreach ($sqlonu as $ont) {
			$olt = $db->Simple("SELECT place FROM switch WHERE id = '{$ont['olt']}' limit 1");
			$_list .= '[b]'.$olt['place'].'[/b]: '.$ont['type'].' '.$ont['inface'].' '.($ont['status']==1 ? 'online' : 'offline').', ';
		}
		$sender_new = '[icon-plan][b]Нові дублікати[/b]: '.$_list .'';
		$db->SQLinsert('notification',['status'=>1,'type'=>1,'system'=>'newdublicat','message'=>$sender_new,'added'=>date('Y-m-d H:i:s')]);
		if (isset($confPMon['PMON_LOG']) && !empty($confPMon['PMON_LOG']) && $confPMon['PMON_LOG'] == 1) {
			if($_list!=false){
				$pmon_log = ['types' => 'duplicates' , 'message' => $_list];
				$sql = PMon($pmon_log,$timer);
				if($sql){
					$db->query($sql);
				}
			}
		}
	}
}
if(isset($removed_duplicates) && !empty($removed_duplicates)){
	foreach($removed_duplicates as $new_ont){

	}
}
?>
