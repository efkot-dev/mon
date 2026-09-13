<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/functions/inspector.php';
$timer = date('Y-m-d H:i:s');
$sql ="SELECT 
    onus.keyonu as o_keyonu, 
    onus.status as o_status, 
    onus.online as o_online, 
    onus.offline as o_offline, 
    onus.idonu as o_idonu, 
    onus.portolt as o_portolt, 
    onus.olt as o_olt, 
    onus.zte_idport as o_zte_idport, 
    onus.inface as o_inface, 
    onus.type as o_type,
    onus.name as o_name,
    switch.netip as s_ip, 
    switch.id as s_id, 
    switch.snmpro as s_snmp, 
    switch.oidid as s_oidid, 
    switch.class as s_class, 
    switch.place as s_place
FROM 
    onus
INNER JOIN 
    switch ON onus.olt = switch.id
WHERE 
    onus.inspector = 2;";
$inspector = [];
$sql_onus = $db->SimpleWhile($sql);
if (isset($sql_onus) && is_array($sql_onus) && count($sql_onus) > 0) {
    foreach ($sql_onus as $o) {
        $inspector[$o['o_olt']]['olt'] = array(
            'id' => $o['s_id'],
            'oidid' => $o['s_oidid'],
            'snmp' => $o['s_snmp'],
            'class' => $o['s_class'],
            'netip' => $o['s_ip']
        );
        $inspector[$o['o_olt']]['ont'][$o['o_idonu']] = array(
            'idonu' => $o['o_idonu'],
            'olt' => $o['o_olt'],
            'place' => $o['s_place'],
            'ip' => $o['s_ip'],
            'oidid' => $o['s_oidid'],
            'name' => $o['o_name'],
            'keyonu' => $o['o_keyonu'],
            'portolt' => $o['o_portolt'],
            'zte_idport' => $o['o_zte_idport'],
            'online' => $o['o_online'],
            'offline' => $o['o_offline'],
            'status' => $o['o_status'],
            'inface' => $o['o_inface'],
            'type' => $o['o_type']
        );
    }		
}
$telegram = [];
foreach ($inspector as $ins_id => $olt) {
	if (isset($olt['ont']) && is_array($olt['ont']) && count($olt['ont']) > 0) {
		foreach ($olt['ont'] as $ont_id => $ont) {
			if($ont['oidid']==1){
				$getdata = array('id' => $ont['olt'] ,'keyonu' => $ont['keyonu'],'pon' => 'epon','do' => 'onu','types' => 'status');
			}elseif($ont['oidid']==14){
				if($ont['type']=='gpon'){
					$getdata = array('id' => $ont['olt'],'keyport' => $ont['zte_idport'],'keyonu' => $ont['keyonu'],'pon' => 'gpon','do' => 'onu','types' => 'status');
				}elseif($ont['type']=='epon'){
					
				}
			}
			if(isset($getdata) && $getdata!=false){
				$getapidataont = api__($config['monitorapi'],$getdata);
				if(isset($getapidataont) && !empty($getapidataont['status'])){
					if($ont['oidid']==1){
						$status = status_onu_bdcom_epon($getapidataont['status']);
					}elseif($ont['oidid']==14){
						if($ont['type']=='gpon'){
							$status = status_onu_huawei_gpon($getapidataont['status']);
						}elseif($ont['type']=='epon'){
							
						}
					}
				}
			}
			$setClause = [];
			$edit = 0;
			#print_R("{$ont['name']} {$ont['status']} -> {$status}");
			if(isset($status) && $status!=false){
				$setClause[] = "updates = '{$timer}'";	
				if($ont['status']==1 && $status==1){
					// була онлайн і є онлайн
				}elseif($ont['status']==2 && $status==2){
					// була офлайн і є офлайн
				}elseif($ont['status']==2 && $status==1){
					$setClause[] = "status = '1'";
					$setClause[] = "online = '{$timer}'";
					$edit = 1;
					$telegram[$ont['idonu']]['telegram'] = "[icon-online] [b]{$ont['place']}[/b] {$ont['ip']} [b]{$ont['type']} {$ont['inface']}[/b] ".(!empty($ont['name']) ? "[".$ont['name']."]": "")." - UP [".aftertime($ont['offline'])."]";
				}elseif($ont['status']==1 && $status==2){
					$setClause[] = "status = '2'";
					$setClause[] = "offline = '{$timer}'";
					$edit = 2;	
					$telegram[$ont['idonu']]['telegram'] = "[icon-offline] [b]{$ont['place']}[/b] {$ont['ip']} [b]{$ont['type']} {$ont['inface']}[/b] ".(!empty($ont['name']) ? "[".$ont['name']."]": "")." - DOWN [".aftertime($ont['online'])."]";					
				}
				if(isset($edit) && $edit>0){
					$db->query("INSERT INTO onus_monitor (`idonu`,`status`,`added`) VALUES ('{$ont['idonu']}','{$status}','{$timer}')");
				}
				$setClause = implode(', ', $setClause);
				$db->query("UPDATE onus SET $setClause WHERE idonu = '{$ont['idonu']}'");
			}
		}
	}
}
if(isset($telegram) && count($telegram)>0){
	foreach($telegram as $detali => $data) {
		$db->SQLinsert('notification', ['status' => 1, 'type' => 6, 'system' => 'pinger', 'message' => $data['telegram'], 'added' => $timer]);
	}
}
?>
