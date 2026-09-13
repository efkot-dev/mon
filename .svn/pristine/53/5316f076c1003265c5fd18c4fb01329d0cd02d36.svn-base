<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = [
	'title'=>'Test telegram',
	'description'=>'Test telegram',
	'page'=>'test'
];
if(isset($_POST['message'])){
	$message = isset($_POST['message']) ? Clean::text($_POST['message']): null;
	if(isset($message)){
		$sender = 'TEST-'.$message;
		$db->SQLinsert('notification',['status'=>1,'type'=>777,'system'=>'monitor','message'=>$sender,'added'=>date('Y-m-d H:i:s')]);
		$go->go('/?do=test');
	}
}
$result .= '<div class="nav-fiber p10"><form action="/?do=test" method="post"><label for="description">Message:</label><textarea id="message" name="message"></textarea><br><input type="submit" value="Send"></form></div>';
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Test Telegram</span></div>'.$result.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>