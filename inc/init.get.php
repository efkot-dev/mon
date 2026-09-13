<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('CONFIG',true);
require ENGINE_DIR.'init.time.php';
require ROOT_DIR.'/inc/database.php';
require ENGINE_DIR.'init.pmon.php';
require ENGINE_DIR.'classes/monitor.db.class.php';
require ENGINE_DIR.'classes/cache.class.php';
require ENGINE_DIR.'init.lang.php';
if(!defined('PMONAPP')){
	
}else{	
	require ENGINE_DIR.'functions/app.php';
	require ENGINE_DIR.'classes/users.class.php';	
	if(isset($decodedata)){
		$decodedData = decodeLoginPassword($decodedata);	
	}else{
		die('err_2');	
	}
	if (!is_array($decodedData) || !isset($decodedData['login'], $decodedData['password'])) {
		die('err_2');
	}
	$username  = cleanUsername($decodedData['login']);
	$userpass =	cleanUsername($decodedData['password']);
	if(empty($username) || empty($userpass)){
		die('err_3');		
	}
	$auth->mobileapp($username,$userpass);	
	$USER = $auth->app_getuser();
	if (!empty($USER['id'])) {
		require ENGINE_DIR . 'classes/access.class.php';
	}
	if(isset($_REQUEST['do']) && $_REQUEST['do']=='onu_view'){
		require ROOT_DIR.'/inc/init.support.php';
		require ROOT_DIR.'/inc/init.olt.php';
		require ENGINE_DIR.'classes/equipment.class.php';
		require ENGINE_DIR.'classes/snmp.class.php';
		require ENGINE_DIR.'classes/ont.class.php';
	}
}
require ROOT_DIR.'/inc/init.config.php';
require ENGINE_DIR.'classes/log.class.php';
require ENGINE_DIR.'classes/clean.class.php';
if(defined('TELEGRAM')){
	require ENGINE_DIR.'classes/telegram.class.php';	
}else{
	require ENGINE_DIR.'classes/telnet.class.php';
	require ENGINE_DIR.'classes/system.class.php';
}
if(isset($act) && $act=='onu'){
	require ROOT_DIR.'/inc/init.support.php';
	require ROOT_DIR.'/inc/init.olt.php';
	require ENGINE_DIR.'classes/equipment.class.php';
	require ENGINE_DIR.'classes/snmp.class.php';
	require ENGINE_DIR.'classes/ont.class.php';
}		
require ENGINE_DIR.'functions/monitor.php';
require ENGINE_DIR.'functions/core.php';

?>
