<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;	
if(empty($id)){
	$go->go('/?do=taskman');
}
$taskview = $db->Fast('task_list','*',['id'=>$id]);
if(empty($taskview['id'])){
$go->go('/?do=taskman');
}
$moderation = '';
$getlistworker = getListWorker();	
$listlocation = getListLocation();
$metatags = ['title'=>$lang['taskman_list_task'],'description'=>$lang['taskman_list_task'],'page'=>'list'];
$speedbar .='
	<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>'.$lang['taskman_main_task'].'</a>
	<a class="brmhref" href="/?do=taskman&act=list"><i class="fi fi-rr-apps"></i>'.$lang['taskman_list_task'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$getlistworker[$taskview['typesworker']]['name'].'</span>
';	
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';	
if(!empty($taskview['status']) && $taskview['status']==1){
	$moderation .= '<a class="moderation_a colors_1" href="#" onclick="ajaxcore(\'taskfinish\','.$id.')">'.$lang['taskman_finish'].'</a>';
	if(isset($USER['class']) && $USER['class'] >= 3 ){
		$moderation .= '
			<a class="moderation_a colors_2" href="#" onclick="ajaxcore(\'taskedit\','.$id.')">'.$lang['edit'].'</a>
			<a class="moderation_a colors_3" href="/?do=taskman&act=deltask&id='.$id.'">'.$lang['delet'].'</a>
		';
	}
}
$content .= '<div class="pmon_onu">
	<div class="pmon_onu_left">
		<div class="main_taskman man0">
			<h1>'.$getlistworker[$taskview['typesworker']]['name'].'</h1>		
			<div class="moderation_task">			
				<div class="finishday">'.$taskview['planned_at'].'</div>';
		if(!empty($taskview['status']) && $taskview['status']==2){		
			$content .= '<div class="closetask">'.$taskview['finish_at'].'</div>';	
		}		
$content .= $moderation.'
		</div>
		<div class="note_task">'.bb_code($taskview['story']).'</div>
	</div>
	<div class="client_card">';		
	$content .= PaidType($taskview);
		if(!empty($taskview['status']) && $taskview['status']==1 && isset($USER['class']) && $USER['class'] >= 3 ){
			$content .= '<span class="edit_paid" onclick="ajaxcore(\'editpaid\','.$id.')">Редагувати</span>';
		}
$content .= '
	</div>
		<div class="client_card">';		
$clientid = $db->Fast('task_list_client','*',['id'=>$taskview['clientid']]);
if(!empty($clientid['client_pib'])){
	$content .= '<div class="line"><b>'.$lang['taskman_us_pib'].': </b><span>'.$clientid['client_pib'].'</span></div>';	
}
if(!empty($clientid['client_mobil'])){
	$content .= '<div class="line"><b>'.$lang['taskman_us_nom'].': </b><span>'.$clientid['client_mobil'].'</span></div>';
}
if(!empty($clientid['client_locationname'])){
	$content .= '<div class="line"><b>'.$lang['taskman_us_location'].': </b><span>'.$clientid['client_locationname'].'</span></div>';
}
if(!empty($clientid['client_street'])){
	$content .= '<div class="line"><b>'.$lang['taskman_us_address'].': </b><span>'.$clientid['client_street'].' '.(isset($clientid['client_house'])?$clientid['client_house']:'').'<span></div>';
}
$content .= '</div>';			
if(!empty($taskview['status']) && $taskview['status']==1){		
$content .= '<div><div class="pole"><a href="#" class="urlelelement" onclick="ajaxcore(\'taskcomment\','.$taskview['id'].')">'.$lang['taskman_us_add_comm'].'</a></div></div>';	
}
$listcomm = $db->SimpleWhile("SELECT * FROM task_list_comment Where taskid = ".$id." ORDER BY added DESC");
if(isset($listcomm) && count($listcomm) > 0){
	foreach($listcomm as $ipid => $comm){		
		$content .= resultTplComment($comm,$taskview);		
	}
}			
$content .= '
	</div>
	<div class="pmon_w_left">';
$content .= getListVikonavci($id,$taskview);
if(isset($_GET['types']) && $_GET['types']=='addvikonavci'){			
	$user_task = getListVikonavciArray($id);
	$user_pmon = getListUser();
	if(isset($user_pmon) && count($user_pmon) > 0){
		$content .= '<form action="/?do=taskman" method="post" style="width: 100%;"><input name="id" type="hidden" value="'.$id.'"><input name="act" type="hidden" value="savevikonavci"><table class="resp-tab"><thead><tr><th></th><th>'.$lang['taskman_us_add_login'].'</th><th>login</th><th>Class</th></tr></thead><tbody>';
		foreach($user_pmon as $uid => $user){	
			$selected = (isset($user_task[$user['userid']]['userid']) && $user['userid']==$user_task[$user['userid']]['userid']?'checked':'');
			$content .= '<tr><td><input class="checkcss" name="us[]" value="'.$user['userid'].'" type="checkbox" '.$selected.'></td><td>'.(isset($user['name']) && !empty($user['name']) ? '<b>'.$user['name'].'</b>' : 'n/a').'</td><td>'.$user['username'].'</td><td>'.$user['uclass'].'</td></tr>';
		}
		$content .= '</table>';				
		if(isset($USER['class']) && $USER['class'] >= 3 && $_GET['types']=='addvikonavci'){
			$content .= '<div class="pole"><input type="submit" value="'.$lang['add'].'"></div>';
		}
		$content .= '</form>';
	}
	$content .= '<div></div>';
}
$content .= '</div></div>';
?>