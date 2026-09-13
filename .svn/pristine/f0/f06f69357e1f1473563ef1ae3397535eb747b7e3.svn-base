<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if($page != 'note' && $page != 'backup' && $page != 'nomdu' && $page != 'connect' && $page != 'viewgallery' && $page != 'gallery' && $page != 'fdbtable'){
$tplRes .='<div class="list_pon_url">';
if($dataSwitch['device']=='olt' && isset($sql_data_onu) && count($sql_data_onu)>0){
	$tplRes .='<div class="style_pon"><a href="/?do=terminal&id='.$id.'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/pon/onu.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">ONU <span class="olt-count">'.count($sql_data_onu).'</span><br><span class="sub-descr">'.$lang['btn_olt_allonu'].'</span></div></div>';	
	if (isset($confPMon['FDB_TABLE']) && !empty($confPMon['FDB_TABLE']) && $confPMon['FDB_TABLE'] == 1){
		$sql_fdb_tables = $pdo->prepare("SELECT COUNT(id) AS count_mac FROM fdb_tables WHERE olt = :id");
		$sql_fdb_tables->execute(['id' => $id]);
		$count_mac = $sql_fdb_tables->fetch(PDO::FETCH_ASSOC);
		$a13 = array(
			'a_href' => '/?do=detail&act='.$dataSwitch['device'].'&page=fdbtable&id=' . $id ,
			'onclick' => '',
			'count' => (isset($count_mac['count_mac']) && $count_mac['count_mac']>0 ? '<span class="olt-count">'.$count_mac['count_mac'].'</span>':''),
			'title' =>$lang['fdb_table'],'descr' => $lang['fdb_table_descr'],'img' => 'fdb_table.png'
		);
		if(isset($count_mac['count_mac'])){
			$tplRes .= olt_url($a13);
		}
	}		
	$tplRes .='<div class="style_pon"><a href="/?do=onuvendor&id='.$id.'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/pon/onu.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['listonunoreg'].'<br><span class="sub-descr">Model Vendor</span></div></div>';
	if (isset($confPMon['SWITCH_TEMPERATURE_MONITORING']) && !empty($confPMon['SWITCH_TEMPERATURE_MONITORING']) && $confPMon['SWITCH_TEMPERATURE_MONITORING']==1) {
		$sql = "SELECT switch.id, switch.oidid, oid.types, oid.oid 
			FROM switch 
			INNER JOIN oid ON switch.oidid = oid.oidid 
			WHERE switch.id = :id AND oid.inf = 'health' AND oid.types = 'temp'";
		$sql_oid = $pdo->prepare($sql);
		$sql_oid->execute(['id' => $id]);
		$data_oid = $sql_oid->fetch(PDO::FETCH_ASSOC);	
		if(!empty($data_oid['oid'])){
			$tplRes .='<div class="style_pon"><a href="/?do=detail&act=olt&page=temp&id='.$id.'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/pon/timer.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">CPU Monitor<br><span class="sub-descr">Monitor temperature</span></div></div>';
		}
	}	
	if($onuoffline>0){
		$tplRes .='<div class="style_pon"><a href="/?do=terminal&id='.$id.'&sort=6&type=desc" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/onu_error.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['listonunoreg'].'<span class="olt-onu-off">'.$onuoffline.'</span><br><span class="sub-descr">'.$lang['listnotactivity'].' ONU</span></div></div>';
	}
}
if(!empty($dataSwitch['location']) && $dataSwitch['device']=='olt'){
	$tplRes .='<div class="style_pon"><a href="/?do=map&location='.$dataSwitch['location'].'&type='.$dataSwitch['device'].'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/place.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['map'].'<br><span class="sub-descr">'.$lang['maponu'].'</span></div></div>';
}	
/*
if($dataSwitch['monitor']=='yes'){
	$getmapper = $db->Fast('geodevice','*',['deviceid' => $id]);
	if(empty($getmapper['id'])){
		$tplRes .='<div class="style_pon"><a href="/?do=mapper&act=add&id='.$id.'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/place.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['addmapper'].'<br><span class="sub-descr">'.$lang['addmapperdescr'].'</span></div></div>';
	}else{
		$tplRes .='<div class="style_pon"><a href="/?do=mapper&act=add&id='.$id.'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/place.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['editmapper'].'<br><span class="sub-descr">'.$lang['addmapperdescr'].'</span></div></div>';
	}
}
*/
if($onutchemgerx>=1 && $dataSwitch['device']=='olt'){
	$tplRes .='<div class="style_pon"><a href="/?do=terminal&id='.$id.'&rxstatus=up" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/badrx.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['editrx'].'<span class="bad-olt-count">'.$onutchemgerx.'</span><br><span class="sub-descr">'.$lang['editrxbad'].'</span></div></div>';
}				
if($onutchemgerxd>=1 && $dataSwitch['device']=='olt'){
	$tplRes .='<div class="style_pon"><a href="/?do=terminal&id='.$id.'&rxstatus=down" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/goodrx.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['editrx'].'<span class="bad-olt-count">'.$onutchemgerxd.'</span><br><span class="sub-descr">'.$lang['editrxgood'].'</span></div></div>';
}
if ($dataSwitch['gallery'] === 'yes' && $access->get('gallerydevice')) {
	$a33 = array(
		'a_href' => '/?do=detail&act='.$dataSwitch['device'].'&page=gallery&id=' . $id ,
		'onclick' => '',
		'title' => $lang['photo_switch'],'descr' => $lang['photo_switch_descr'] ,'img' => 'gallery.png'
	);
	$tplRes .= olt_url($a33);
}
if (isset($confPMon['PMON_BILLING']) && !empty($confPMon['PMON_BILLING']) && $confPMon['PMON_BILLING'] == 1  && $dataSwitch['device']=='olt'){
	$a13 = array(
			'a_href' => '/?do=billing&id=' . $id ,
			'onclick' => '',
			'title' =>'Список клієнтів','descr' => 'Інформація з білінга','img' => 'usr_billing.png'
		);
	#$tplRes .= olt_url($a13);
}
if($dataSwitch['connect']=='yes' && $access->get('connectport') && count($SQLPortDevice)>0){
	$tplRes .='<div class="style_pon"><a href="/?do=detail&act='.$dataSwitch['device'].'&page=connect&id='.$id.'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/dev_cable.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['btn_olt_allconn'].'<br><span class="sub-descr">'.$lang['btn_olt_allconn_'].'</span></div></div>';
}
if($access->get('logdevice') && count($SQLPortDevice)>0){
	$tplRes .='<div class="style_pon"><a href="/?do=switchlog&id='.$dataSwitch['id'].'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/dev_log.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['log'].'<br><span class="sub-descr">'.$lang['log_descr'].'</span></div></div>';
}
if($access->get('monitordevice') && $dataSwitch['monitor']=='yes' && count($SQLPortDevice)>0){
	$tplRes .='<div class="style_pon"><a href="/?do=detail&act='.$dataSwitch['device'].'&page=monitoring&id='.$id.'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/dev_mon.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['edit_monitor'].'<br><span class="sub-descr">'.$lang['edit_monitor_descr_'].'</span></div></div>';
}
if($dataSwitch['monitor']=='yes' && !empty($dataSwitch['snmprw']) && $dataSwitch['oidid'] == 1 && $access->get('reboot')){
	$tplRes .='<div class="style_pon"><a href="#" onclick="rebootallonubdcom('.$dataSwitch['id'].')" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/dev_check.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['reboot'].'<br><span class="sub-descr">'.$lang['rebootallonu'].'</span></div></div>';
}	
if($dataSwitch['monitor']=='yes' && $dataSwitch['oidid'] == 15 && $access->get('blacklist') && $access->get('delblacklist')){
	$tplRes .='<div class="style_pon"><a href="/?do=blacklist12&id='.$dataSwitch['id'].'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/blacklist.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">BlackList<br><span class="sub-descr">'.$lang['blacklist'].'</span></div></div>';
}	
if($dataSwitch['monitor']=='yes' && $dataSwitch['oidid'] == 13 && $access->get('blacklist') && $access->get('delblacklist')){
	$tplRes .='<div class="style_pon"><a href="/?do=blacklist11&id='.$dataSwitch['id'].'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/blacklist.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">BlackList<br><span class="sub-descr">'.$lang['blacklist'].'</span></div></div>';
}	
if($dataSwitch['oidid'] == 14 || $dataSwitch['oidid'] == 33) {
	$tplRes .='<div class="style_pon"><a href="/?do=huaweionuerror&id='.$dataSwitch['id'].'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/onu_error.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['onuerror'].'<br><span class="sub-descr">'.$lang['sys_erroronuhuaweimin'].'</span></div></div>';
}	
if(($dataSwitch['oidid'] == 1 || $dataSwitch['oidid'] == 2) && isset($confPMon['ERRORONUBDCOM'])) {
	$tplRes .='<div class="style_pon"><a href="/?do=bdcomonuerror&id='.$dataSwitch['id'].'" class="sc-psedN fLDHlO"></a><div class="sc-qQWDO frpbEt"><img src="../style/img/onu_error.png" class="pon-onu"></div><div class="sc-qZtVr brvuoL">'.$lang['onuerror'].'<br><span class="sub-descr">'.$lang['sys_erroronuhuaweimin'].'</span></div></div>';
}
	$a33 = array(
		'a_href' => '/?do=detail&act='.$dataSwitch['device'].'&page=note&id=' . $id ,
		'onclick' => '',
		'title' => $lang['sys_note'],'descr' => $lang['sys_note_descr'],'img' => 'notesys.png'
	);
	$tplRes .= olt_url($a33);
if($access->get('panel_olt')){
	$a4 = array(
		'a_href' => '/?do=detail&act='.$dataSwitch['device'].'&page=admin&id=' . $id ,
		'onclick' => '',
		'title' => $lang['functions'],
		'descr' => $lang['add_functions'],
		'img' => 'admin_olt.png'
	);
	$tplRes .= olt_url($a4);
}
if (isset($confPMon['FIBERMAP']) && !empty($confPMon['FIBERMAP']) && $confPMon['FIBERMAP'] == 1 && $dataSwitch['device']=='olt'){
	$a3 = array(
		'a_href' => '/?do=detail&act='.$dataSwitch['device'].'&page=nomdu&id=' . $id ,'onclick' => '','title' => $lang['pon_not_attached'],'descr' => $lang['pon_network'],'img' => 'free_onu.png'
	);
	$tplRes .= olt_url($a3);
}	
if ($dataSwitch['device']=='olt' && isset($confPMon['TEMPLATE_REGISTER']) && !empty($confPMon['TEMPLATE_REGISTER']) 
	&& $confPMon['TEMPLATE_REGISTER'] == 1 && ($dataSwitch['oidid'] == 14 || $dataSwitch['oidid'] == 33)){
	$a2 = array(
		'a_href' => '/?do=detail&act=olt&page=regerok&id=' . $id ,'onclick' => '','title' => 'Register onu','descr' => 'Successful register','img' => 'success_reger.png'
	);
	$tplRes .= olt_url($a2);
}	
if ($dataSwitch['device']=='olt' && $dataSwitch['oidid'] == 1 && isset($confPMon['ONU_ERROR']) && !empty($confPMon['ONU_ERROR']) && $confPMon['ONU_ERROR']==1){
	$a1 = array(
		'a_href' => '/?do=onuerror&id=' . $id . '','onclick' => '','title' => 'ONU Error','descr' => 'List of ONU Port Errors','img' => 'onu_error.png'
	);
	$tplRes .= olt_url($a1);	
}
if ($dataSwitch['device']=='olt' && $access->get('view_backup') && isset($confPMon['BACKUP_OLT']) && !empty($confPMon['BACKUP_OLT']) && $confPMon['BACKUP_OLT']==1){
	$a1 = array(
		'a_href' => '/?do=detail&act=olt&page=backup&id=' . $id . '','onclick' => '','title' => 'Backup config','descr' => 'Olt Configuration File','img' => 'olt_backup.png'
	);
	$tplRes .= olt_url($a1);
}
if ($dataSwitch['device']=='olt' && $access->get('regonu') && isset($confPMon['REG_GPON_HUAWEI_MOD1']) 
	&& !empty($confPMon['REG_GPON_HUAWEI_MOD1']) && $confPMon['REG_GPON_HUAWEI_MOD1']==1 
		&& $access->get('regonu') && ($dataSwitch['oidid'] == 14 || $dataSwitch['oidid'] == 33) ){
	$are = array(
		'a_href' => '/?do=detail&act=olt&page=mod1&id=' . $id . '','onclick' => '','title' => 'Реєстрація ONU','descr' => 'Snmp Qinq ONU','img' => 'sfp-port.png'
	);
	$tplRes .= olt_url($are);
}
$tplRes .='</div>';
}
?>