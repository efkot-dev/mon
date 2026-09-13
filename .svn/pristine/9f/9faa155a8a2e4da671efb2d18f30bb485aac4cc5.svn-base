<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (!defined('AJAX')){
	die('Hacking attempt!');
}	
define('CONFIG',true);
require ROOT_DIR . '/inc/autoload.php';
require ENGINE_DIR.'init.time.php';
require ROOT_DIR.'/inc/init.license.php';
require ROOT_DIR.'/inc/database.php';
if(defined('SNMP')){
	require ENGINE_DIR.'classes/data.class.php';	
}
if(defined('CALENDAR')){
	require ENGINE_DIR.'functions/calendar.php';	
}
require ENGINE_DIR.'init.pmon.php';
require ROOT_DIR.'/inc/init.sql.php';
require ENGINE_DIR.'classes/cache.class.php';
require ENGINE_DIR.'classes/monitor.db.class.php';
require ROOT_DIR.'/inc/init.config.php';
require ENGINE_DIR.'classes/clean.class.php';
if (is_file(ENGINE_DIR.'classes/users.class.php')) {
	require ENGINE_DIR.'classes/users.class.php';
} else {
	error_log('[PMON AJAX] Missing required file: ' . ENGINE_DIR.'classes/users.class.php');
	http_response_code(500);
	die('Authentication bootstrap failed');
}
$USER = $auth->requireAuth();
require ENGINE_DIR.'init.lang.php';
require ENGINE_DIR.'classes/log.class.php';
require ENGINE_DIR.'classes/access.class.php';
if(!empty($USER['id'])){
	require ENGINE_DIR.'classes/telnet.class.php';
	require ENGINE_DIR.'classes/system.class.php';
	if(defined('TPL')){
		require ENGINE_DIR.'classes/tpl.class.php';
		$tpl =  new TemplateMonitor;		
	}
	if(defined('ONT')){
		require ENGINE_DIR.'classes/equipment.class.php';
		require ENGINE_DIR.'classes/snmp.class.php';
		require ENGINE_DIR.'init.olt.php';
		require ENGINE_DIR.'classes/ont.class.php';
	}
	require ENGINE_DIR.'functions/monitor.php';
	require ENGINE_DIR.'functions/core.php';
	if(defined('REGONU')){
		require ENGINE_DIR.'functions/regonu.php';
	}
}else{
	die('Authentication failed');
}
?>
