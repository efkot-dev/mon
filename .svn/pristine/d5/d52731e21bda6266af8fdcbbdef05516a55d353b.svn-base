<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('setup')) {
    $go->redirect('main');
}
$metatags = array('title'=>$lang['add_new_users'],'description'=>$lang['add_new_users'],'page'=>'users');
$msg = isset($_GET['msg']) ? Clean::text($_GET['msg']) : '';
$err = isset($_GET['err']) ? Clean::text($_GET['err']) : '';
$alert = '';
if($msg !== ''){
	$alert = '<div class="comment" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;margin:10px 0;">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</div>';
}
if($err !== ''){
	$alert = '<div class="comment" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;margin:10px 0;">'.htmlspecialchars($err, ENT_QUOTES, 'UTF-8').'</div>';
}
$selectallusers = $db->Multi('users');
if(count($selectallusers)>0){
	foreach($selectallusers as $user){
		$tpl->load_template('users/list.tpl');
		$tpl->set('{id}',$user['id']);
		$tpl->set('{username}',$user['username']);
		$accessBtn = '';
		if($user['id']!=$USER['id']){
		$accessBtn = ($user['access'] === 'yes')
		? '<a class="btn-access on" href="/?do=core&act=accessuser&id='.$user['id'].'&type=off">ON</a>'
		: '<a class="btn-access off" href="/?do=core&act=accessuser&id='.$user['id'].'&type=on">OFF</a>';
		}
		$tpl->set('{accessbtn}', $accessBtn);
		$tpl->set('{name}',($user['name']?'<span class="subnames">'.$user['name'].'</span>':''));
		$tpl->set('{added}',$user['added']);
		$tpl->set('{page}',$user['url']);
		$tpl->set('{last}',$user['lastactivity']);
		$tpl->set('{ip}',($user['ip']?'<div class="userip">'.$user['ip'].'</div>':''));
		$tpl->set('{onlyip}',($user['onlyip']=='on'?'<div class="onlyip">'.$lang['lock_ip'].' <b>'.$user['setip'].'</b></div>':''));
		$tpl->set('{class}',getClassUser($user['class']));
		$tpl->set('{usermoder}',($access->get('setup')?'
		<a href="/?do=access&id='.$user['id'].'">'.$lang['head_setup_access'].'</a>
		<a href="#" onclick="ajaxcore(\'apk\','.$user['id'].')">'.$lang['apk_setup'].'</a>
		<a href="#" onclick="ajaxcore(\'edituser\','.$user['id'].')">'.$lang['edit'].'</a>
		'.($USER['id']!==$user['id']?'<a href="#" onclick="ajaxcore(\'deletuser\','.$user['id'].')">'.$lang['delet'].'</a>':''):''));
		$tpl->compile('list-users');
		$tpl->clear();			
	}
}else{
		
}
$tpl->load_template('users/main.tpl');
$tpl->set('{alert}',$alert);
$tpl->set('{add}',($access->get('setup')?'<div class="navigation mbottom20"><span class="deviceadd" onclick="ajaxcore(\'newuser\');">'.$lang['add_new_users'].'</span></div>':''));
$tpl->set('{name}',$lang['users']);
$tpl->set('{result}',$tpl->result['list-users']);
$tpl->compile('content');
$tpl->clear();
?>
