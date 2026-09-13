<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array('title'=>$lang['pl_device'],'description'=>$lang['pd_device'],'page'=>'pon');
$url_sort = '';
$sort_group = '';
$sort_location = '';
$addparam = '';
$cssgroups = '';
$orderby = $orderby ?? null;
$orderby['name'] =  'DESC';
$where = array();
$time = (isset($_GET['time']) ? Clean::str($_GET['time']) : null);
if(isset($time)){
	if($time=='desc')
		$orderby['updates'] =  'DESC';		
	if($time=='asc')
		$orderby['updates'] =  'ASC';	
}
$name = (isset($_GET['name']) ? Clean::str($_GET['name']) : null);
if(isset($name)){
	if($name=='desc')
		$orderby['place'] =  'DESC';		
	if($name=='asc')
		$orderby['place'] =  'ASC';	
}
$ip = (isset($_GET['ip']) ? Clean::str($_GET['ip']) : null);
if(isset($ip)){
	if($ip=='desc')
		$orderby['netip'] =  'DESC';		
	if($ip=='asc')
		$orderby['netip'] =  'ASC';	
}
$addparam .= '&act=olt';
$where['device'] = 'olt';
$group = isset($_GET['group']) ? Clean::int($_GET['group']) : null;
if(isset($group)){
	$addparam .= '&group='.$group;
	$where['groups'] = $group;		
}
$location = isset($_GET['location']) ? Clean::int($_GET['location']) : null;
if(isset($location)){
	$addparam .= '&location='.$location;
	$where['location'] = $location;		
}
$pagerlink = '';
if (!empty($addparam)) {
    if (!empty($pagerlink))
        $addparam = $addparam . $pagerlink;
} else {
    $addparam = $pagerlink;
}
$url_sort = '/?do=pon'.$addparam;
$sqllocation = getLocation();
if(isset($sqllocation) && count($sqllocation)>0){
	$sort_location .= '<div class="div_sort_name"></div>';
	foreach($sqllocation as $loc){
		$sort_location .= '<a href="'.$url_sort.'&location='.$loc['id'].'" '.(isset($location) && $location==$loc['id']?'class="sel_loc"':'').'>'.$pmonimg['svg']['globe'].''.$loc['name'].'</a>';
	}
}
$sqlgroups = getGroupsAll(); 
$arraygroups = array();
if (!empty($sqlgroups)) {
		$sort_group .= '<div class="div_sort_name"></div>';
    $cssgroups = '<div class="list_gropus_css">';
    foreach ($sqlgroups as $groups) {
        $groupId = $groups['id'];
        $groupName = $groups['name'];
        $cssgroups .= sprintf(
            '<a class="link_group" href="/?do=pon&group=%s"><i class="fi fi-rr-hastag"></i>%s</a>',
            $groupId,
            $groupName
        );
        $sort_group .= sprintf(
            '<a href="/?do=pon&group=%s">'.$pmonimg['svg']['groups'].'%s</a>',
            $groupId,
            $groupName
        );
        $arraygroups[$groupId] = array('name' => $groupName, 'id' => $groupId);
    }
    $cssgroups .= '</div>';
}
$sql_where = '';
if(isset($where) && is_array($where) && !empty($where)) {
    $sql_where .= ' WHERE ';
    $conditions = [];
    foreach($where as $s => $v) {
        $conditions[] = "{$s} = '{$v}'";
    }
    $sql_where .= implode(' AND ', $conditions);
}
$sql_orderby = '';
if(isset($orderby) && is_array($orderby) && !empty($orderby)) {
    $sql_orderby .= ' ORDER BY ';
    $orders = [];
    foreach($orderby as $b => $d) {
        $orders[] = "{$b} {$d}";
    }
    $sql_orderby .= implode(', ', $orders);
}
$not_access = $access->list_access_switch();
$not_id = "";
if(isset($not_access) && !empty($not_access)){
    $not_id = (!empty($sql_orderby) || !empty($sql_where)) ? " AND id IN (" . implode(',', $not_access) . ")" : " WHERE id NOT IN (" . implode(',', $not_access) . ")";
}
$select_switch = $db->Simple("SELECT COUNT(id) AS count_switch FROM switch {$sql_where} {$not_id}");
$sqlcount = $select_switch['count_switch'];
$pagecount = $config['countviewpageswitch'];
list($pagertop, $pagerbottom, $limit, $offset) = pager($pagecount,$sqlcount,$url_sort);
$sql = "SELECT * FROM switch {$sql_where} {$not_id} {$sql_orderby} LIMIT {$limit},{$offset}";
$sqldevice = $db->SimpleWhile($sql);
if(isset($sqldevice) && count($sqldevice)>0){
	foreach($sqldevice as $Device){
		if($access->get('dev'.$Device['id'])){
			//$bar_signal = signal_bar($Device['id']);
			$count_bad_signal = get_bad_rx_olt($Device['id']);
			$tpl->load_template('device/list_olt.tpl');
			$tpl->set('{url}','/?do=detail&act=olt&id='.$Device['id']);
			$getStatistic = getOltStats($Device['id']);
			$tpl->set('{info}',infstatus($Device['monitor'],(isset($getStatistic['sql_count_onu'])?$getStatistic['sql_count_onu']:'')));
			$tpl->set('{imgmodel}',$Device['img']);
			$tpl->set('{todayonu}',(!empty($arraynewonutoday[$Device['id']]['count'])?'<span class="todayonu">+'.$arraynewonutoday[$Device['id']]['count'].'</span>':''));
			$tpl->set('{place}',$Device['place']);
			$tpl->set('{location}',(!empty($Device['locationname'])?$Device['locationname']:''));
			if($Device['groups']>0 && is_array($arraygroups) && !empty($arraygroups[$Device['groups']]['id'])){
				$tpl->set('{group}','<a href="/?do=device&group='.$arraygroups[$Device['groups']]['id'].'">'.$arraygroups[$Device['groups']]['name'].'</a>');
			}else{
				$tpl->set('{group}','');
			}
			$tpl->set('{new_onu}',(!empty($Device['todayonu']) && $Device['todayonu']>0 ? '<div class="new_onu_block">+'.$Device['todayonu'].'</div>':''));
			$tpl->set('{netip}',($USER['class']>=4 && !empty($USER['class']) ?($config['viewipswitch']=='on' ? '<span class="netip"><img src="../style/img/network.png">'.$Device['netip'].'</span>' : ''):''));
			$onuoffline = intval($getStatistic['sql_count_onu'] ? $getStatistic['sql_count_onu'] : 0)-(isset($getStatistic['sql_count_onu_on']) ? $getStatistic['sql_count_onu_on'] : 0);
			$tpl->set('{badrx}',(!empty($getStatistic['sql_count_onu']) ? '<div class="onu_stats">'.(isset($count_bad_signal) && !empty($count_bad_signal) && $count_bad_signal>1?'<div class="berr">'.$count_bad_signal.' '.$lang['listrxdescr'].'</div>':'').'</div>':''));
			//$tpl->set('{badrx}',$bar_signal);
			$tpl->set('{monitor}',($Device['monitor']=='yes'?'<span class="olt-mon">'.$lang['descron'].'</span>':''));
			$tpl->set('{time}','<span>'.aftertime($Device['updates']).'</span>');	
			$tpl->set('{uptime}','<span class="timer"><img src="../style/img/on-time.png"><span>'.$Device['uptime'].'</span></span>');				
			#$tpl->set('{notsnmp}',(isset($confPMon['PINGER']) && !empty($confPMon['PINGER']) && $confPMon['PINGER'] == 1 && !empty($Device['pinger']) && $Device['pinger']==2 ? 'notsnmp':''));
			$tpl->set('{notsnmp}','');
			$tpl->set('{pon_port}',(!empty($getStatistic['sql_count_pon']) ? $getStatistic['sql_count_pon']:0));
			$tpl->set('{count}',intval($getStatistic['sql_count_onu'] ? $getStatistic['sql_count_onu'] : 0));
			$tpl->set('{online}',intval($getStatistic['sql_count_onu_on'] ? $getStatistic['sql_count_onu_on'] : 0));
			$tpl->set('{offline}',$onuoffline);				
			$tpl->set('{model}',$Device['inf'].' '.$Device['model']);
			$tpl->compile('device');
			$tpl->clear();
		}
	}
}else{
	$tpl->load_template('terminal/empty.tpl');
	$tpl->set('{result}','<div class="emlist">'.$lang['emptydevice'].'</div>');
	$tpl->compile('device');
	$tpl->clear();	
}
$tpl->load_template('device/main-list.tpl');
$tpl->set('{sort_location}',$sort_location.$sort_group);
$tpl->set('{sort_all}',(isset($name) || isset($location) || isset($time) || isset($group)?'<a href="/?do=pon">'.$pmonimg['svg']['back'].''.$lang['viewall'].'</a>':''));
$tpl->set('{sort_name}',$url_sort.'&name='.(isset($name) && $name=='asc' ? 'desc' : 'asc'));
$tpl->set('{sort_time}',$url_sort.'&time='.(isset($time) && $time=='asc' ? 'desc' : 'asc'));
$tpl->set('{sort_ip}',$url_sort.'&ip='.(isset($ip) && $ip=='asc' ? 'desc' : 'asc'));
$tpl->set('{clear}',(isset($name) || isset($location) || isset($time) || isset($group)?'<a href="/?do=pon" class="search_list" >'.$pmonimg['svg']['search_clear'].'</a>':''));
$tpl->set('{groups}',$cssgroups);
$tpl->set('{typedevice}','pon');
$tpl->set('{view_list}','<span class="search_list" id="view_list">'.$pmonimg['svg']['config_olt'].'</span>');
$tpl->set('{result}',((isset($sqldevice) && count($sqldevice)>0) ? $tpl->result['device']:''));
$tpl->set('{pagerbottom}',((isset($sqldevice) && $pagecount<$sqlcount) ? $pagertop : ''));
$tpl->compile('block-main');
$tpl->clear();
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$tpl->result['block-main']);
$tpl->compile('content');
$tpl->clear();
?>