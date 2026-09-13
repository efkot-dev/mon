<?php
if (!defined('PONMONITOR')) die('Hacking attempt!');
define('SCHEDULER', true);
define('MODULE_SCHEDULER', ROOT_DIR . '/inc/scheduler/');
define('URL_SCHEDULER', '/?do=scheduler');
require ENGINE_DIR.'functions/scheduler.php';
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$speedbar_block = '';
$content_head = '';
$content = '';
switch ($act) {
	case 'add':
		require MODULE_SCHEDULER . 'add.php';
		break;	
	case 'get':
		require MODULE_SCHEDULER . 'get.php';
		break;	
	case 'load':
		require MODULE_SCHEDULER . 'load.php';
		break;
	case 'edit':
		require MODULE_SCHEDULER . 'edit.php';
		break;	
	case 'telnet':
		require ENGINE_DIR.'classes/telnet.class.php';
		require MODULE_SCHEDULER . 'telnet.php';
		break;
	case 'delete':
		require MODULE_SCHEDULER . 'delete.php';
		header('Location: ' . URL_SCHEDULER);
		exit;
		break;
	default:
		require MODULE_SCHEDULER . 'main.php';
}
$tpl->load_template('board_fault.tpl');
$tpl->set('{speedbar}',$speedbar_block);
$tpl->set('{content_head}',$content_head);
$tpl->set('{content}',$content);
$tpl->compile('content');
$tpl->clear();
?>
