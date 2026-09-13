<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('logdevice')){
	$go->redirect('main');
}
$addparam = $addparam ?? null;
$sqllog = $sqllog ?? null;
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$type = isset($_GET['type']) ? Clean::text($_GET['type']) : null;
$sql_select_onu = "";
if(isset($type) && $type=='onu'){
	$addparam = '&type=onu';
	$sql_select_onu = " AND log = 'onu'";
}
if(!$id){
	$go->redirect('main');	
}
$dataSwitch = $db->Fast('switch','*',['id'=>$id]);
if(empty($dataSwitch['id'])){
	$go->redirect('main');	
}
$log_result = '';
if(isset($type) && $type=='onu'){
	$where = ['deviceid'=>$dataSwitch['id'],'log'=>'onu'];	
}else{
	$where = ['deviceid'=>$dataSwitch['id']];
}
$order = array('id'=>'DESC');
$sqlcount = $db->Simple('Select count(id) as countlog FROM devicelogs WHERE deviceid = '.$dataSwitch['id'].' '.$sql_select_onu);
if(isset($sqlcount['countlog']) && $sqlcount['countlog']>0){
	list($pagertop, $pagerbottom, $limit, $offset) = pager(40,$sqlcount['countlog'],'/?do=switchlog&id='.$id.''.$addparam);
	$sqllog = $db->Multi('devicelogs','*',$where,$order,$offset,$limit);
}
$log_result .= '
'.(isset($type) ? '<a class="log_button" href="/?do=switchlog&id='.$dataSwitch['id'].'">All</a>' : '').'
<a class="log_button" href="/?do=switchlog&id='.$dataSwitch['id'].'&type=onu">Ont</a>
<a class="log_button" href="/?do=switchlog&id='.$dataSwitch['id'].'&type=user">User</a>

<table class="resp-tab sw_log"><thead><tr>
	<th width="10%">'.$lang['oid_types'].'</th>
	<th>'.$lang['oid_epon_device'].'</th>
	<th width="10%">'.$lang['added'].'</th>
	<th width="10%">System</th>
	</tr></thead><tbody>';
if(isset($sqlcount['countlog']) && $sqlcount['countlog']>0){
	foreach($sqllog as $log){
		$log_result .= '<tr>
		<td class="types_log'.'_'.(!empty($log['type'])?$log['type']:'all').'">
			'.$lang['log_'.(!empty($log['type'])?$log['type']:'global')].'
		</td>
		<td class="text_left">'.$log['descr'].'</td>
		<td>'.$log['added'].'</td>
		<td>'.(!empty($log['userid']) && !empty($log['username'])? '<b>'.$log['username'].'</b>':$log['who']).'</td>
		</tr>';
	}
}else{
	$result .= '<tr><td class="td_name" colspan="3">'.$lang['empty_search'].'</td></tr>';	
}
$log_result .= '</table>';
$speedbar = '
<a class="brmhref" href="/?do='.($dataSwitch['device']=='olt'?'pon':'switch').'"><i class="fi fi-rr-apps"></i>'.$lang['alldevice'].'</a>
<a class="brmhref" href="/?do=detail&act='.($dataSwitch['device']=='olt'?'olt':'switch').'&id='.$dataSwitch['id'].'"><i class="fi fi-rr-angle-left"></i>'.$dataSwitch['place'].'</a>
<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['log'].'</span>
';
$metatags = array('title'=>$lang['log'].' '.$dataSwitch['place'],'description'=>$dataSwitch['place'].' '.$dataSwitch['inf'].' '.$dataSwitch['model'],'page'=>'switchlog');
$tpl->load_template('log/page.tpl');
$tpl->set('{speedbar}',$speedbar);
#$tpl->set('{result}',$tpl->result['log-result']);
$tpl->set('{result}',$log_result);
$tpl->set('{url}','/?do=detail&act='.$dataSwitch['device'].'&id='.$dataSwitch['id']);
$tpl->set('{pager}',($sqlcount['countlog']?$pagertop:''));
$tpl->set('{logdevice}',$dataSwitch['place']);
$tpl->set('{logname}',$lang['log']);
$tpl->compile('content');
$tpl->clear();	
?>