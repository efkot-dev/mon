<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$result_form = '';
$result_setup = '';
$script = '';
$select = '';
if(!$access->get('telegram')) {
    $go->redirect('main');
}
$metatags = array('title'=>$lang['telegram_title'],'description'=>$lang['telegram_title'],'page'=>'teleram');
if(isset($config['telegramchatid']) && isset($config['telegram']) && $config['telegram']=='on' && isset($config['typestelegram']) && $config['typestelegram']!='off'){
	$script .='<script>loadsetuptelegram(\''.$config['typestelegram'].'\');</script>';
}
$select .='<option value="off" '.($config['typestelegram']=='off'?'selected="selected"':'').'>'.($config['typestelegram']=='off'?$lang['not_sender']:$lang['disable']).'</option>
<option value="bot" '.($config['typestelegram']=='bot'?'selected="selected"':'').'>'.$lang['telegram_bot'].'</option>
    <option value="chat" '.($config['typestelegram']=='chat'?'selected="selected"':'').'>'.$lang['telegram_chat'].'</option>
    <option value="groups" '.($config['typestelegram']=='groups'?'selected="selected"':'').'>'.$lang['telegram_groups'].'</option>';
$tpl->load_template('billing/telegram.tpl');
$tpl->set('{select}',$select);
$tpl->set('{script}',$script);
$tpl->set('{result_setup}',$result_setup);
$tpl->set('{result_form}',$result_form);
$tpl->compile('content');
$tpl->clear();
?>