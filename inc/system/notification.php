<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if($access->get('view_notification')) {
	if(isset($act) && $act=='clear'){
		$db->query('TRUNCATE notification;');
		$go->go('/?do=notification');
		exit;
	}
	$result = '';
	$result .= '<table class="resp-tab"><thead><tr><th width="5%">'.$lang['status'].'</th>
	<th>'.$lang['message_title'].'
		<a href="/?do=notification&act=clear"><img style="vertical-align: text-bottom;" src="../style/img/history.png"></a>
	</th>
	
	<th width="5%">'.$lang['type_job'].'</th><th width="15%">'.$lang['added'].'</th></tr></thead><tbody>';
	$url_terminal = '?do=notification';
	$select_telegram = $db->Simple("SELECT COUNT(id) AS count_idonu FROM notification");
	list($pagertop, $pagerbottom, $limit, $offset) = pager(30,$select_telegram['count_idonu'],$url_terminal);
	$sql_orderby = "ORDER BY added DESC";
	$sql = "SELECT * FROM notification {$sql_orderby} LIMIT {$limit},{$offset}";
	$sql_notification = $db->SimpleWhile($sql);
	if(isset($sql_notification) && count($sql_notification)>0){
		foreach($sql_notification as $messid => $mess){
			$result .= '<tr>
				<td><span class="statusonu st_'.$mess['status'].'"</span></td>
				<td style="text-align: left;">'.grab_telegram($mess['message']).'</td>
				<td><font color="#1f7bc3">'.$mess['type'].'</font></td>
				<td><font color="4CAF50">'.aftertime($mess['added']).'</font></td>
				</tr>';
		}
	}else{
		$result .= '<tr><td class="td_name" colspan="8">'.$lang['empty_search'].'</td></tr>';
	}
	$metatags = ['title'=>$lang['view_notification'],'description'=>$lang['view_notification'],'page'=>'notification'];
	$result .= '</table>';
	$result .= $pagertop;
	$result ='<div id="onu-speedbar"><a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a><a class="brmhref" href="/?do=operator"><i class="fi fi-rr-angle-left"></i>Обслуговування</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['view_notification'].'</span></div>'.$result.'';
	$tpl->load_template('main/main.tpl');
	$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');		
}
?>