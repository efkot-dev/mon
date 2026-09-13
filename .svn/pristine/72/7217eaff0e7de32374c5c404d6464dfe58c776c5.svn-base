<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('VIDEO' , true);surveillance
define('MODULE_VIDEO', ROOT_DIR . '/inc/surveillance/');
define('URL_VIDEO', '/?do=surveillance');
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$url_video = URL_VIDEO;
switch($act){
	case 'add':
		require MODULE_VIDEO . 'add.php';
		break;	
	case 'save':
		require MODULE_VIDEO . 'save.php';
		break;
	default:	
		require MODULE_VIDEO . 'main.php';

}
$tpl->load_template('html_devices.tpl');
$tpl->set('{speedbar}',$speedbar_block);
$tpl->set('{content_head}',$content_head);
$tpl->set('{content}',$content);
$tpl->compile('content');
$tpl->clear();
?>