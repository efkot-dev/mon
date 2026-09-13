<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('setup')){
	$go->redirect('main');
	exit;
}
$checkLicenseSwitch = getSwitchAll();
$metatags = array('title'=>$lang['pl_device'],'description'=>$lang['pd_device'],'page'=>'device');
$url_sort = '';
$sort_group = '';
$sort_location = '';
$addparam = '';
$cssgroups = '';
$orderby = $orderby ?? null;
// sort name asc, desc
$time = (isset($_GET['time']) ? Clean::str($_GET['time']) : null);
if(isset($time)){
	if($time=='desc')
		$orderby['updates'] =  'DESC';		
	if($time=='asc')
		$orderby['updates'] =  'ASC';	
}
// sort name asc, desc
$name = (isset($_GET['name']) ? Clean::str($_GET['name']) : null);
if(isset($name)){
	if($name=='desc')
		$orderby['place'] =  'DESC';		
	if($name=='asc')
		$orderby['place'] =  'ASC';	
}
// sort ip asc, desc
$ip = (isset($_GET['ip']) ? Clean::str($_GET['ip']) : null);
if(isset($ip)){
	if($ip=='desc')
		$orderby['netip'] =  'DESC';		
	if($ip=='asc')
		$orderby['netip'] =  'ASC';	
}
if($act=='olt'){
	$addparam .= '&act=olt';
	$where = ['device'=>'olt'];
}elseif($act=='switch'){
	$addparam .= '&act=switch';
	$where = ['device'=>'switch'];	
}elseif($act=='ups'){
	$addparam .= '&act=ups';
	$where = ['device'=>'ups'];	
}else{
	$urlPager = '';
	$where = null;	
}
// select group
$group = isset($_GET['group']) ? Clean::int($_GET['group']) : null;
if(isset($group)){
	$addparam .= '&group='.$group;
	$where = ['groups'=>$group];		
}
// select location
$location = isset($_GET['location']) ? Clean::int($_GET['location']) : null;
if(isset($location)){
	$addparam .= '&location='.$location;
	$where = ['location'=>$location];		
}
$pagerlink = '';
if (!empty($addparam)) {
    if (!empty($pagerlink))
        $addparam = $addparam . $pagerlink;
} else {
    $addparam = $pagerlink;
}
$url_sort = '/?do=device'.$addparam;
// sort location
$sqllocation = getLocation();
if(isset($sqllocation) && count($sqllocation)>0){
	foreach($sqllocation as $loc){
		$sort_location .= '<a href="'.$url_sort.'&location='.$loc['id'].'"><i class="fi fi-rr-layers"></i>'.$loc['name'].'</a>';
	}
}
// sort group
$arraygroups = array();
if (!empty($SQLListlocation)) {
    $cssgroups = '<div class="list_gropus_css">';
    foreach ($SQLListlocation as $groups) {
        $groupId = $groups['id'];
        $groupName = $groups['name'];
        $cssgroups .= sprintf(
            '<a class="link_group" href="/?do=device&group=%s"><i class="fi fi-rr-hastag"></i>%s</a>',
            $groupId,
            $groupName
        );
        $sort_group .= sprintf(
            '<a href="/?do=device&group=%s"><i class="fi fi-rr-text"></i>%s</a>',
            $groupId,
            $groupName
        );
        $arraygroups[$groupId] = array('name' => $groupName, 'id' => $groupId);
    }
    $cssgroups .= '</div>';
}
if (isset($where)) {
    $sqlcount = count($db->Multi('switch', '*', $where));
} else {
    $sqlcount = isset($checkLicenseSwitch) ? count($checkLicenseSwitch) : 0;
}
$result = '';
$pagecount = $config['countviewpageswitch'];
list($pagertop, $pagerbottom, $limit, $offset) = pager($pagecount,$sqlcount,$url_sort);
$sql_device = $db->Multi('switch','*',$where,$orderby,$offset,$limit);
$result .= '
<table class="resp-tab list-onu-olt">
	<thead><tr>
		<th class="mob_w10" width="4%">'.$lang['status'].'</th>
		<th width="10%">IP-адреса</th>
		<th width="10%">Модель</th>
		<th width="20%">Назва</th>
		<th width="10%">Uptime</th>
		<th width="10%">Моніторинг</th>
		<th width="10%">Тип</th>
		<th width="10%">Портів</th>
		<th>Other</th>
		</tr>
	</thead><tbody>';
if(isset($sql_device) && count($sql_device)>0){
	foreach($sql_device as $device){
		if(!empty($device['device']) && $access->get('dev'.$device['id'])){
			$result .= '<tr>';
			if($device['monitor']=='yes'){
				$get_status = '<img src="../style/img/online.png">';
			}else{
				$get_status = '<img src="../style/img/online.png">';
			}
			$result .= '<td class="status">'.$get_status.'</td>';
			$result .= '<td class="name_pon"><font color="#222">'.$device['netip'].'</font></td>';
			$result .= '<td class="mobile"><span class="on_">'.$device['inf'].' '.$device['model'].'</span></td>';
			$result .= '<td class="description_name mobile_font"><a href="/?do=detail&act='.$device['device'].'&id='.$device['id'].'" class="name-onu">'.$device['place'].''.($device['status']=='go'?'<img style="vertical-align: sub;" src="../style/img/onu_success.png">':'').'</a></td>';
			$result .= '<td class="name_pon"><font color="#222">'.$device['uptime'].'</font></td>';
			$result .= '<td class="name_pon">'.aftertime($device['updates']).'</td>';
			$result .= '<td class="name_pon"><font color="#222">'.$device['device'].'</font></td>';
			$result .= '<td class="name_pon">'.$device['device'].'</td>';
			$result .= '<td class="description_name mobile_font"></td>';
			$result .= '</tr>';	
		}
	}
}
$result .= '</table>';
#if(!$tpl->result['device']){
	#$go->redirect('main');	
#}
$tpl->load_template('device/main-list.tpl');
$tpl->set('{sort_location}',$sort_location.$sort_group);
$tpl->set('{sort_all}',(isset($name) || isset($location) || isset($time) || isset($group)?'<a href="/?do=device"><i class="fi fi-rr-refresh"></i>'.$lang['viewall'].'</a>':''));
$tpl->set('{sort_name}',$url_sort.'&name='.(isset($name) && $name=='asc' ? 'desc' : 'asc'));
$tpl->set('{sort_time}',$url_sort.'&time='.(isset($time) && $time=='asc' ? 'desc' : 'asc'));
$tpl->set('{sort_ip}',$url_sort.'&ip='.(isset($ip) && $ip=='asc' ? 'desc' : 'asc'));
$tpl->set('{clear}','');
$tpl->set('{view_list}','');
$tpl->set('{groups}','');
$tpl->set('{result}',$result);
$tpl->set('{pagerbottom}',$pagertop);
$tpl->compile('block-main');
$tpl->clear();
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$tpl->result['block-main']);
$tpl->compile('content');
$tpl->clear();
?>