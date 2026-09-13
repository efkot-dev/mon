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
define('ROOT_DIR', dirname(__FILE__));
define('API_DIR', ROOT_DIR . '/engine/');
define('ENGINE_DIR', ROOT_DIR . '/inc/');
if (php_sapi_name() === 'cli') {
	require_once ENGINE_DIR . '/database.php';
	require_once API_DIR . '/worker.php';
}
?>
