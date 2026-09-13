<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$elementid = isset($_GET['id']) ? intval($_GET['id']) : 0;
$metatags = array('title'=>'editor','description'=>'editor','page'=>'editor');
$result = '<script>$.post(root+"ajax/editor.php",{a:1,id:'.$elementid.'}, function(response){ 
	$(".wrap").html(response);
	}, "html");</script>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>