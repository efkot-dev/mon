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
define('PONMONITOR',true);
define('ROOT_DIR',dirname(__FILE__));
define('API_DIR',ROOT_DIR.'/engine/');
define('ENGINE_DIR',ROOT_DIR.'/inc/');
if (version_compare(phpversion(), '8.0.0', '<')) {
    die('Not supported PHP version');
}
require_once API_DIR.'/get.php';
?>