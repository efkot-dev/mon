<?php
/*
=====================================================
 Powered by PMon
-----------------------------------------------------
 Autor: Momotiuk Oleksiy
-----------------------------------------------------
 Telegram: momotiuk88
-----------------------------------------------------
 Site: https://pmon.com.ua
-----------------------------------------------------
 Copyright (c) 2023 PMon
=====================================================
 This code is protected by copyright
=====================================================
*/
define('PONMONITOR',true);
define('ROOT_DIR',dirname(__FILE__));
define('API_DIR',ROOT_DIR.'/engine/');
define('ENGINE_DIR',ROOT_DIR.'/inc/');
if (php_sapi_name() === 'cli') {
	die('Not support');
}
$appdata = $_REQUEST['appdata'] ?? null;
if ($appdata === 'sklad') {
    require_once API_DIR . '/sklad.app.php';
} else {
    require_once API_DIR . '/api.app.php';
}
?>
