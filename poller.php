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
define('API_DIR', ROOT_DIR . '/engine/');
define('ENGINE_DIR', ROOT_DIR . '/inc/');
$jobid = $jobid ?? 0;
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jobid'])) {
    $olts = filter_input(INPUT_POST, 'olt', FILTER_SANITIZE_NUMBER_INT);
	$switch = filter_input(INPUT_POST, 'switch', FILTER_SANITIZE_NUMBER_INT);
	$olt = $switch !== null ? $switch : $olts;
    $jobid = filter_input(INPUT_POST, 'jobid', FILTER_SANITIZE_NUMBER_INT);
    $get_result = filter_input(INPUT_POST, 'result', FILTER_SANITIZE_NUMBER_INT);
}else{
	$options = getopt("s:j:a:", ["switch:", "jobid:", "access:"]);
	$olt = $options["s"] ?? $options["switch"] ?? null;
	$jobid = (int)($options["j"] ?? $options["jobid"] ?? 0);
}
require_once API_DIR . '/core.php';
?>
