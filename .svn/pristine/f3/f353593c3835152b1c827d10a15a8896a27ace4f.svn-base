<?php
/*
=====================================================
 Powered by PMon
-----------------------------------------------------
 Autor: Momotiuk Oleksiy
-----------------------------------------------------
 Telegram: momotuk88
-----------------------------------------------------
 Site: https://pmon.com.ua
-----------------------------------------------------
 Copyright (c) 2023 PMon
=====================================================
 This code is protected by copyright
=====================================================
*/
define('PONMONITOR', true);
define('ROOT_DIR', __DIR__);
define('ENGINE_DIR', ROOT_DIR . '/inc/');
define('MODULE_PMON', ROOT_DIR . '/inc/system/');
define('MODULE_FIBER', ROOT_DIR . '/inc/fiber/');
define('MODULE_TASK', ROOT_DIR . '/inc/task/');
if (!file_exists(ROOT_DIR . '/inc/database.php')) {
    header('Location: /install.php');
    exit();
}
if (version_compare(phpversion(), '8.0.0', '<')) {
    die('Not supported PHP version');
}
if (php_sapi_name() === 'cli') {
	die('Not supported PHP version Fack You Bich@');
}else{
	require_once ENGINE_DIR . 'load.php';
}
?>
