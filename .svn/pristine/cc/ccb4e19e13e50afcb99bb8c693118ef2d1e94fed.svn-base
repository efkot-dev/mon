<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('board_fault_view')) {
	$go->go('/?do=main');
	exit;	
}
/* 
	The person who submitted it is going to develop it 
	and motivated to work on it: @AndrySkuridin
	2025/10/05
*/	
$speedbar_block = '';
$content_head = '';
$content = '';
define('BOARD' , true);
define('MODULE_BOARD', ROOT_DIR . '/inc/board/');
define('URL_BOARD', '/?do=board');
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$url_border = URL_BOARD;
require ENGINE_DIR.'functions/board.php';
switch($act){
	// форма налаштування телеграму
	case 'config':
		require MODULE_BOARD . 'config.php';
		break;	
	case 'delstreet':
		require MODULE_BOARD . 'delstreet.php';
		break;	
	case 'update_loc':
		require MODULE_BOARD . 'update_loc.php';
		break;	
	case 'add_loc':
		require MODULE_BOARD . 'add_loc.php';
		break;
	// форма додавання робіт
	case 'add':
		require MODULE_BOARD . 'add.php';
		break;		
	// повторне оповіщення
	case 'message':
		require MODULE_BOARD . 'message.php';
		break;		
	// шаблони оновлення
	case 'template':
		require MODULE_BOARD . 'template.php';
		break;		
	// форма update робіт
	case 'update':
		require MODULE_BOARD . 'update.php';
		break;	
	// форма нових
	case 'added':
		require MODULE_BOARD . 'added.php';
		break;
	// отримати вулиці по ід
	case 'get_street':
		require MODULE_BOARD . 'get_street.php';
		break;	
	// іидалення свіча з аварії
	case 'delswitch':
		require MODULE_BOARD . 'delswitch.php';
		break;	
	// видалення міста з аварії
	case 'delcity':
		require MODULE_BOARD . 'delcity.php';
		break;
	// отримати всі порти
	case 'get_ports':
		require MODULE_BOARD . 'get_ports.php';
		break;	
	// детальна інформація
	case 'view':
		require MODULE_BOARD . 'view.php';
		break;	
	// збереження вулиці і отримання ід
	case 'add_street':
		require MODULE_BOARD . 'add_street.php';
		break;
	// зберігаємо роботи в базу
	case 'save':
		require MODULE_BOARD . 'save.php';
		break;
	// редагування робіт
	case 'edit':
		require MODULE_BOARD . 'edit.php';
		break;
    // завершення робіт		
	case 'end':
		require MODULE_BOARD . 'end.php';
		break;	    
	// завантаження	
	case 'load':
		require MODULE_BOARD . 'load.php';
		break;	
	// видалення робіт
	case 'delet':
		require MODULE_BOARD . 'delet.php';
		break;
	// надсилання втелеграм сповіщенні: завершення, закінчення		
	case 'send':
		if (is_file(MODULE_BOARD . 'send.php')) {
			require MODULE_BOARD . 'send.php';
		} else {
			require MODULE_BOARD . 'message.php';
		}
		break;
	// список всіх робіт за 36 годин
	default:	
		require MODULE_BOARD . 'main.php';

}
$tpl->load_template('board_fault.tpl');
$tpl->set('{speedbar}',$speedbar_block);
$tpl->set('{content_head}',$content_head);
$tpl->set('{content}',$content);
$tpl->compile('content');
$tpl->clear();
?>
