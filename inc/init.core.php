<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('CONFIG',true);
require ROOT_DIR.'/inc/database.php';
require ENGINE_DIR.'init.pmon.php';
require ENGINE_DIR.'classes/cache.class.php';
require ROOT_DIR . '/inc/init.lang.php';
?>
