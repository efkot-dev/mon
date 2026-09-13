<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$active_stickers = false;
$pmon_billing = '';
$column = $column ?? null;
$url_terminal = $url_terminal ?? null;
$oldlinks = $oldlinks ?? null;
$orderby = $orderby ?? null;
$rxstatus = $rxstatus ?? null;
$whereoltandpon = $whereoltandpon ?? null;
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$port = isset($_GET['port']) ? Clean::int($_GET['port']) : null;
$sort = isset($_GET['sort']) ? Clean::int($_GET['sort']) : null;
$type = isset($_GET['type']) ? Clean::text($_GET['type']) : null;
$rxstatus = isset($_GET['rxstatus']) ? Clean::text($_GET['rxstatus']) : null;
if ($sort && $type) {
switch($sort){
    case '1':
        $column = 'name';
    break;
    case '2':
        $column = 'rx';
    break;       
	case '3':
		$column = 'dist';
    break;		
	case '4':
		$column = 'added';
    break;		
	case '5':
		$column = 'online';
    break;		
	case '6':
		$column = 'offline';
    break;	
	case '7':
		$column = 'uid';
    break;	
	case '8':
		$column = 'tag';
    break;		
	case '9':
		$column = 'inface';
    break;		
	case '10':
		$column = 'wan';
    break;	
    default:
        $column = 'added';
    break;	
}
switch($type){
    case 'asc':
        $ascdesc = 'ASC';
        $linkascdesc = 'asc';
    break;
    case 'desc':
        $ascdesc = 'DESC';
        $linkascdesc = 'desc';

    break;
    default:
        $ascdesc = 'DESC';
        $linkascdesc = 'desc';
    break;
}
if($column && $ascdesc)
	$orderby[$column] = $ascdesc;
}
if(!$id){
	$go->redirect('main');
}
if(!$access->get('dev'.$id)){
	$go->redirect('main');	
}
$link1 = '';$link2 = '';$link3 = '';$link4 = '';$link5 = '';$link6 = '';$link7 = '';$link8 = '';$link9 = '';$link10 = '';$sortbtn = '';
$count_get = 0;
	$oldlink = null;
	foreach ($_GET as $get_name => $get_value) {
		$get_name = strip_tags(str_replace(array("\"","'"),array('',''),$get_name));
		$get_value = strip_tags(str_replace(array("\"","'"),array('',''),$get_value));
		if ($get_name != 'sort' && $get_name != 'type') {
			if ($count_get > 0) {
				$oldlink = $oldlink . "&" . $get_name . "=" . $get_value;
			} else {
				$oldlink = $oldlink . $get_name . "=" . $get_value;
			}
			$count_get++;
		}
	}
	if ($count_get > 0) {
        $oldlink = $oldlink . "&";
    }
if ($sort==1) {
	if ($type=='desc') {
		$link1='asc';
	} else {
		$link1='desc';
	}
}else{
	if (!$link1)
		$link1='asc';
}
if ($sort==2) {
	if ($type=='desc') {
		$link2='asc';
	} else {
		$link2='desc';
	}
}else{
	if (!$link2)
		$link2='asc';
}
if ($sort==3) {
	if ($type=='desc') {
		$link3='asc';
	} else {
		$link3='desc';
	}
}else{
	if (!$link3)
		$link3='asc';
}
if ($sort==4) {
	if ($type=='desc') {
		$link4='asc';
	} else {
		$link4='desc';
	}
}else{
	if (!$link4)
		$link4='asc';
}
if ($sort==5) {
	if ($type=='desc') {
		$link5='asc';
	} else {
		$link5='desc';
	}
}else{
	if (!$link5)
		$link5='asc';
}
if ($sort==6) {
	if ($type=='desc') {
		$link6='asc';
	} else {
		$link6='desc';
	}
}else{
	if (!$link6)
		$link6='asc';
}
if ($sort==7) {
	if ($type=='desc') {
		$link7='asc';
	} else {
		$link7='desc';
	}
}else{
	if (!$link7)
		$link7='asc';
}
if ($sort==8) {
	if ($type=='desc') {
		$link8='asc';
	} else {
		$link8='desc';
	}
}else{
	if (!$link8)
		$link8='asc';
}
if ($sort==9) {
	if ($type=='desc') {
		$link9='asc';
	} else {
		$link9='desc';
	}
}else{
	if (!$link9)
		$link9='asc';
}
if ($sort==10) {
	if ($type=='desc') {
		$link10='asc';
	} else {
		$link10='desc';
	}
}else{
	if (!$link10)
		$link10='asc';
}
if($USER['hideonu']=='yes'){
	$whereoltandpon['status'] = 1;
}
if(isset($column) && $column == 'offline'){
	$whereoltandpon['status'] = 2;	
	$orderby['offline'] =  'DESC';
}
if($rxstatus=='up'){
	$whereoltandpon['rxstatus'] = 'up';
	$orderby['rxstatus'] =  'DESC';
}elseif($rxstatus=='down'){
	$whereoltandpon['rxstatus'] = 'down';
	$orderby['rxstatus'] =  'DESC';
}
if(isset($confPMon['STICKERS']) && !empty($confPMon['STICKERS']) && $confPMon['STICKERS']==1){
	$orderby['stikers'] =  'DESC';
	$active_stickers = true;
}
if(!empty($orderby[$column])){
	$oldlinks = '&sort='.(int)$sort.'&type='.$type;
}
$selectportolt = '';
$dataSwitch = $db->Fast('switch','*',['id'=>$id]);
if(!$dataSwitch['id']){
	$go->redirect('main');		
}
if($port){
	$dataPon = $db->Fast('switch_pon','*',['id'=>$port]);
	$dataPort = $db->Fast('switch_port','*',['deviceid'=>$id,'llid'=>$dataPon['sfpid']]);
	$whereoltandpon['olt'] = $id;
	$whereoltandpon['portolt'] = $dataPon['sfpid'];
	$url_terminal = '/?do=terminal&id='.$id.'&port='.$port.$oldlinks;
	$metatags = array('title'=>$dataPon['pon'].' '.$lang['pt_onu'],'description'=>$lang['pd_onu'],'page'=>'terminal');
	$selectportolt .= '<script>ajaxsfp('.$dataPon['id'].');</script>';
	$selectportolt .= '<div class="pon-sfp-detail">';
	$selectportolt .= '<div class="inform"><h2>'.$dataPon['pon'].'</h2>';
	if(isset($dataPort['descrport']) && !empty($dataPort['descrport'])){
		$selectportolt .= '<div class="description_port">'.$dataPort['descrport'].'</div>';	
	}
		$selectportolt .= '<div id="ajaxsfp"></div>';
		$selectportolt .= '<div class="pon-sfp-stats">';
		if(!empty($dataPon['online']))
			$selectportolt .= '<span>'.$lang['sfp_1'].'</span><span class="cl1">'.$dataPon['online'].'</span>';
		if(!empty($dataPon['offline']))
			$selectportolt .= '<span>'.$lang['sfp_2'].'</span><span class="cl2">'.$dataPon['offline'].'</span>';
		if(!empty($dataPon['count']))
			$selectportolt .= '<span>'.$lang['sfp_3'].'</span><span class="cl3">'.$dataPon['count'].'</span>';
		if(!empty($dataPon['support']))
			$selectportolt .= '<span>'.$lang['sfp_4'].'</span><span class="cl4">'.$dataPon['support'].'</span>';
		$selectportolt .= '</div>';
	$selectportolt .= '</div>';
	$selectportolt .= '</div>';
	$selectportolt .= '<div class="pon-sfp-detail"><div class="dashboard_pon">';
	if(!empty($dataSwitch['snmprw']) && $dataSwitch['oidid']==1 && $access->get('rebootonu')){
		$selectportolt .= '<a href="#" onclick="rebootallonubdcom('.$id.',\''.$dataPon['pon'].'\')" class="reboot_pon">'.$lang['success_reboot_check'].' '.$dataPon['pon'].'</a>';
	}elseif(!empty($dataSwitch['username']) && $dataSwitch['oidid']==7 && $access->get('rebootonu')){
		$selectportolt .= '<a href="#" onclick="console_pmon(\'reboot_onu\','.$id.',\''.$dataPon['pon'].'\')" class="reboot_pon">'.$lang['success_reboot_check'].' '.$dataPon['pon'].'</a>';
	}
	$selectportolt .= '<a href="#" onclick="checkerpon('.$id.',\''.$dataPon['sfpid'].'\')" class="snmp_pon">'.$lang['snmp_check_pon'].' '.$dataPon['pon'].'</a>';
	if($access->get('bandwidth_monitor')){
		$todaydays = date('d.m');
		$bandwidth_monitor = $db->Simple("SELECT id FROM traff_monitor WHERE deviceid = '{$dataPort['deviceid']}' AND llid = '{$dataPort['llid']}' LIMIT 1");
		if(!empty($bandwidth_monitor['id'])){
			$selectportolt .= '<a href="/?do=bandwidth&act=view&id='.$bandwidth_monitor['id'].'&d='.$todaydays.'" class="snmp_pon_traff">'.$lang['view_bandwidth'].'</a>';
		}else{
			$selectportolt .= '<a href="#" class="snmp_pon_rx" onclick="funbandwidth(\''.$dataPort['id'].'\',\'active\',\'pon\')">'.$lang['enable_bandwidth'].'</a>';
		}
	}
	$selectportolt .= '<a href="/?do=ponsignal&id='.$dataPon['id'].'" class="snmp_pon_vz" >PON Diagnostics Rx Onu</a>';
	$selectportolt .= '<a href="/?do=oltsignal&id='.$dataPon['id'].'" class="snmp_pon_fc" >PON Diagnostics Rx Olt</a>';
	if($access->get('deletonu')){ 
		$selectportolt .= '<a href="/?do=terminal&id='.$id.'&port='.$dataPon['id'].'&act=delet" class="snmp_pon_red" >'.$lang['delet'].' ONT</a>';
	}
	$selectportolt .= '</div></div>';
	#$selectportolt .= '<script>ajaxstatsport('.$id.','.$dataPon['sfpid'].');</script>';
}else{
	$whereoltandpon['olt'] = $id;
	$url_terminal = '/?do=terminal&id='.$id.$oldlinks;
	$metatags = array('title'=>$lang['pt_onu'],'description'=>$lang['pd_onu'],'page'=>'terminal');
	#$selectportolt .= '<script>ajaxstatsport('.$id.');</script>';
}
$updates = '';
$sql_orderby = '';
if(isset($orderby) && is_array($orderby) && !empty($orderby)) {
    $sql_orderby .= ' ORDER BY ';
    $orders = [];
    foreach($orderby as $b => $d) {
		if(isset($b) && $b=='inface'){
			$orders[] = "CAST(SUBSTRING_INDEX({$b}, ':', -1) AS SIGNED) {$d}";
		}else {
			$orders[] = "{$b} {$d}";
		}
    }
    $sql_orderby .= implode(', ', $orders);
}
$sql_where = '';
if(isset($whereoltandpon) && is_array($whereoltandpon) && !empty($whereoltandpon)) {
    $sql_where .= ' WHERE ';
    $conditions = [];
    foreach($whereoltandpon as $s => $v) {
        $conditions[] = "{$s} = '{$v}'";
    }
    $sql_where .= implode(' AND ', $conditions);
}
$terminal = '';
$select_onus = $db->Simple("SELECT COUNT(idonu) AS count_idonu FROM onus {$sql_where}");
list($pagertop, $pagerbottom, $limit, $offset) = pager($config['countviewpageonu'],$select_onus['count_idonu'],$url_terminal);
// SORT KEY status
$sort_inface = '<a class="sorta" href="/?'.$oldlink.'&sort=9&type='.$link9.'">'.$lang['gilka'].$pmonimg['svg']['sort_text_'.$link9].'</a>';
$sort_distance = '<a class="sorta" href="/?'.$oldlink.'&sort=3&type='.$link3.'">'.$lang['dists'].$pmonimg['svg']['sort_text_'.$link3].'</a>';
$sort_rx ='<a class="sortb" href="/?'.$oldlink.'&sort=2&type='.$link2.'"><span class="sig2">RX ONU</span>'.$pmonimg['svg']['sort_text_'.$link2].'</a>';
$sort_vlan ='<a class="sortb" href="/?'.$oldlink.'&sort=10&type='.$link10.'"><span class="sig2">Vlan</span>'.$pmonimg['svg']['sort_text_'.$link10].'</a>';
#$sortbtn .='<a class="sort table4" href="/?'.$oldlink.'&sort=4&type='.$link4.'"><img src="../style/img/sort/'.$link4.'.png">'.$lang['register'].'</a>';
#$sortbtn .='<a class="sort table5" href="/?'.$oldlink.'&sort=5&type='.$link5.'"><img src="../style/img/sort/'.$link5.'.png">'.$lang['online'].'</a>';
#$sortbtn .='<a class="sort" href="/?'.$oldlink.'&sort=6&type='.$link6.'"><img src="../style/img/sort/'.$link6.'.png">'.$lang['offline'].'</a>';
#$sortbtn .='<a class="sort" href="/?'.$oldlink.'&sort=7&type='.$link7.'"><img src="../style/img/sort/'.$link7.'.png">UID</a>';
$sortbtn .='<a class="sort table1" href="/?'.$oldlink.'&sort=8&type='.$link8.'"><img src="../style/img/sort/'.$link8.'.png">Marker/Tag</a>';
$sortbtn .='<a class="sort table3" href="/?'.$oldlink.'&sort=1&type='.$link1.'"><img src="../style/img/sort/'.$link1.'.png">Description</a>';
// SORT KEY
$sql = "SELECT * FROM onus {$sql_where} {$sql_orderby} LIMIT {$limit},{$offset}";
#print_R($sql);
$sqlonus = $db->SimpleWhile($sql);
	$terminal .= '<table class="resp-tab list-onu-olt"><thead><tr>';
	if(isset($act) && $act == 'delet'){
	$terminal .= '<th width="4%"><span class="select_all" onclick="selectall()">'.$lang['all'].'</span></th>';
	}
	$terminal .= '<th class="mob_w10 list_terminal" width="4%">'.$lang['status'].'</th>
		<th width="15%">'.$sort_inface.'</th>
		<th width="10%">MAC Serial</th>
		<th width="7%">
			<span class="inf_signal">
				'.$sort_rx.'
			</span>
		</th>';
		if(isset($confPMon['ONU_RX_OLT_SIGNAL']) && !empty($confPMon['ONU_RX_OLT_SIGNAL']) && $confPMon['ONU_RX_OLT_SIGNAL']==1){		
		$terminal .= '
		<th width="6%">
			<span class="inf_signal"><span class="sig1">RX OLT</span></span>
		</th>';
		}
		$terminal .= '
		<th class="mobile" width="7%">'.$sort_distance.'</th>
		<th class="mobile" width="10%">
			<span class="inf_status">
				<span class="tim1">'.$lang['sfp_2'].'</span>
				<span class="tim2">'.$lang['sfp_1'].'</span>
			</span>
		</th>';
		if(isset($confPMon['VIEW_ONU_VENDOR']) && !empty($confPMon['VIEW_ONU_VENDOR']) && $confPMon['VIEW_ONU_VENDOR']==1){
			$terminal .= '
			<th class="mobile" width="10%">Vendor</th>';
		}
		if (isset($confPMon['ONU_VLAN']) && !empty($confPMon['ONU_VLAN']) && $confPMon['ONU_VLAN'] == 1 ) {
			$terminal .= '
			<th width="5%" class="mobile">'.$sort_vlan.'</th>';
		}		
		if (isset($confPMon['ONU_UID']) && !empty($confPMon['ONU_UID']) && $confPMon['ONU_UID'] == 1 ) {
			$terminal .= '
			<th width="5%" class="mobile">UID</th>';
		}
		#if (isset($confPMon['ERRORONUBDCOM']) && !empty($confPMon['ERRORONUBDCOM']) && $confPMon['ERRORONUBDCOM'] == 1 ) {
		#	$terminal .= '
		#	<th width="7%" class="mobile">'.$lang['onuerr'].'</th>';
		#}
		$terminal .= '<th>Other</th>';
		$terminal .= '</tr></thead><tbody>';
if(isset($sqlonus) && count($sqlonus)>0){
	foreach($sqlonus as $ont){
		$result_billing = '';
		$status = statusTermianl($ont['status']);
		if($ont['status']==1){
			$get_status = $status['img'];
		}else{
			$get_status = reason_onu($ont['status'],$ont['reason']);
		}
		$added = checkWhenAdded($ont['added']);
		$stikers = ($ont['stikers']==1 && $active_stickers && !empty($ont['stikers'])?' stikers':'');
		$terminal .= '<tr id="ont_'.$ont['idonu'].'" class="'.$status['css'].''.$stikers.' '.$added.'">';
		if(isset($act) && $act == 'delet'){
		$terminal .= '<td align="center">
			<div class="delete_ont">
				<input type="checkbox" name="delete[]" value="'.$ont['idonu'].'" />
			</div>
		</td>';
		}
		$terminal .= '<td class="status" '.(isset($ont['reason']) ? 'id="'.$ont['reason'].'"' : '').'>'.$get_status.'</td>';
		$delta = getOnusErrorDeltaToday($pdo, $dataSwitch, $ont['idonu'],$confPMon,$cacheManager);
		$tpl_error = $delta > 0 ? '<sup>+' . $delta . '</sup>' : '';
		$terminal .= '<td class="inface_onu onu_error"><a href="/?do=onu&id='.$ont['idonu'].'">'.$ont['type'].' '.$ont['inface'].'</a> '.$tpl_error.'</td>';
		$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
		if(isset($onukey)){
			$datatemponu = getFastOnusData($onukey);
		}
		$terminal .= '<td class="td_url"><a href="/?do=onu&id='.$ont['idonu'].'">'.$onukey.'</a></td>';
		// Signal Rx Onu
		$terminal .= '<td '.($ont['status']==2 ? 'style="filter:grayscale(80%);"':'').'>';
		if(isset($ont['rx']) && $ont['rx']!=false){
			$terminal .=  signalTerminal($ont['rx']).($ont['rxstatus']=='up' || $ont['rxstatus']=='down' ? '<span class="signaldown"><i class="fi fi-rr-angle-small-'.$ont['rxstatus'].'"></i></span>':'');
		}
		$terminal .= '</td>';
		// Signal Rx Olt Onu
		if(isset($confPMon['ONU_RX_OLT_SIGNAL']) && !empty($confPMon['ONU_RX_OLT_SIGNAL']) && $confPMon['ONU_RX_OLT_SIGNAL']==1){
			$terminal .= '<td>';
			if(isset($ont['rxolt']) && $ont['status']==1){
				$terminal .=  signalTerminalRx($ont['rxolt'],29,39);
			}
			$terminal .= '</td>';
		}
		// Довжина волокна
		$terminal .= '<td class="dist mobile">'.($ont['dist'] ? metersToKilometers($ont['dist']) : '').'</td>';
		// Онлайн / Оффлайн
		$terminal .= '<td class="mobile">';
			if($ont['status']==1){
				$terminal .= '<span class="on_">'.aftertime_cut($ont['online']).'</span>';
			}else{
				$terminal .= '<span class="off_">'.aftertime_cut($ont['offline']).'</span>';
			}
		$terminal .= '</td>';
		// МОДЕЛЬ ONU
		if(isset($confPMon['VIEW_ONU_VENDOR']) && !empty($confPMon['VIEW_ONU_VENDOR']) && $confPMon['VIEW_ONU_VENDOR']==1){
			$model = (!empty($ont['vendor']) || !empty($ont['model'])?'<span class="search-model">'.$ont['model'].' '.$ont['vendor'].'</span>':'');
			$terminal .= '<td class="dist mobile">'.$model.'</td>';
		}
		// name
		$onu_name = (!empty($ont['name'])?'<span class="name-onu">'.$ont['name'].'</span>':'');
		// Tag		
		$tag = (!empty($datatemponu['tag'])?'<span class="terminaltag">'.$datatemponu['tag'].'</span>':'');
		// vendor
		if (isset($confPMon['ONU_VLAN']) && !empty($confPMon['ONU_VLAN']) && $confPMon['ONU_VLAN'] == 1 ) {
			$vlan = (!empty($ont['wan'])?'<span class="terminalvlan">'.$ont['wan'].'</span>':'');
			$terminal .= '<td class="mobile getvlans">';
			$terminal .= '<a href="/?do=search&search=' . $ont['wan'] . '&act=search&types=vlan"><span id="get_vlan_' . $ont['idonu'] . '" '.(isset($ont['wan']) ? 'onmouseover="sendVlan(\'' . $ont['wan'] . '\',\'' . $ont['idonu'] . '\');"' : '' ).'>'.$vlan.'</span></a>'; 
			$terminal .= '</td>';
		}
		if (isset($confPMon['ONU_UID']) && !empty($confPMon['ONU_UID']) && $confPMon['ONU_UID'] == 1 ) {
			$uid = (!empty($datatemponu['uid'])?'<span class="terminalvlan">'.$datatemponu['uid'].'</span>':'');
			$terminal .= '<td class="mobile getvlans">';
			$terminal .= '<span>'.$uid.'</span>'; 
			$terminal .= '</td>';
		}
		#if (isset($confPMon['ERRORONUBDCOM']) && !empty($confPMon['ERRORONUBDCOM']) && $confPMon['ERRORONUBDCOM'] == 1 ) {
		#	$terminal .= '<td class="mobile">';
		#	$terminal .= '<font color=grey>'.$ont['lasterr'].'</font>'.(isset($ont['err']) && $ont['err']>0?' <font color=red>+'.$ont['err'].'</font>':'').''; 
		#	$terminal .= '</td>';
		#}
		if (isset($confPMon['PMON_BILLING']) && !empty($confPMon['PMON_BILLING']) && $confPMon['PMON_BILLING'] == 1) {
			$pmon_billing = PmonBillingData($ont);
			$result_billing = PmonBillingTemplate($pmon_billing);
		}
		$terminal .= '<td class="description_name mobile_font">
		'.$onu_name.' '.$tag.' '.$result_billing.'
		</td>';
		$terminal .= '</tr>';			
	}
	if($access->get('deletonu') && isset($act) && $act == 'delet'){
	$terminal .= '<tr><td onclick=\'delete_marker_ont("'.$id.'","'.$lang['delet'].' ONT")\' class="delet_browse" colspan="13" align="left">
	<span class="marker">'.$lang['delet'].' ONT</span>
	</td></tr>';
	}
}else{
	$terminal .='<tr><td colspan="7">'.$lang['emlist'].'</td></tr>';	
}
$terminal .='</tbody></table><div id="vlanPopup"></div>';
// SPEEDBAR
$tpl->load_template('terminal/speedbar.tpl');
$tpl->set('{inface}','');
$tpl->set('{id}',$id);
$tpl->set('{olt_model}',$dataSwitch['inf'].' '.$dataSwitch['model']);
$tpl->set('{olt_place}',$dataSwitch['place']);
if(!empty($dataPon['pon'])){
	$tpl->set('{inface}','<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$dataPon['pon'].'</span>');
}elseif(isset($rxstatus) && $rxstatus == 'down'){	
	$tpl->set('{inface}','<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['list_ont_good_signal'].'</span>');	
}elseif(isset($rxstatus) && $rxstatus == 'up'){
	$tpl->set('{inface}','<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['list_ont_bad_signal'].'</span>');
}elseif(isset($column) && $column == 'offline'){
	$tpl->set('{inface}','<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['list_ont_offline'].'</span>');	
}else{
	$tpl->set('{inface}','<span class="brmspan"><i class="fi fi-rr-angle-left"></i>ONU</span>');	

}
$tpl->compile('block-speedbar');
$tpl->clear();	
// SPEEDBAR
$viewbtn = '<div class="switch-btn-right">
<label class="checkbox-ios">'.($USER['hideonu']=='no'?$lang['hide']:$lang['show']).' '.$lang['onuonlinelist'].'
<input id="hideonu" name="hideonu" type="checkbox" '.($USER['hideonu']=='no'?'checked="checked"':'').'><span class="checkbox-ios-switch" onclick="ajaxhideonu();"></span></label>
</div>';
if(!empty($dataPon['pon'])){
	$tpl->load_template('olt/right.tpl');
	$tpl->set('{viewbtn}',$viewbtn);
	$tpl->set('{listpon}',getlistPonTpl(['deviceid'=>$id,'ponid'=>$dataPon['id']]));
	$tpl->compile('block-right');
	$tpl->clear();	
}else{
	$tpl->load_template('olt/right-all.tpl');
	$tpl->set('{viewbtn}',$viewbtn);
	$tpl->set('{listpon}',getlistPonTpl(['deviceid'=>$id]));
	$tpl->compile('block-right');
	$tpl->clear();	
}
$tpl->load_template('terminal/main.tpl');
$tpl->set('{pon}',($selectportolt?'<div id="sfp">'.$selectportolt.'</div>':'').'');
$tpl->set('{result}',$terminal);
$tpl->set('{block-content}',$tpl->result['block-speedbar']);
$tpl->set('{pagerbottom}',$pagertop);
$tpl->compile('content');
$tpl->clear();
?>