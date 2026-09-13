<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
ini_set('memory_limit', '-1');
define('CONFIG', true);
if (!file_exists(ROOT_DIR . '/inc/database.php')) {
    die('Not supported MYSQL');
}
if (version_compare(phpversion(), '8.0.0', '<')) {
    die('Not supported PHP version');
}
require_once ROOT_DIR . '/inc/database.php';
require ENGINE_DIR.'init.pmon.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if ($mysqli->connect_error) {
    die("Error connecting to the database: " . $mysqli->connect_error);
}
$backupDir = ROOT_DIR . '/file/backup/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
$backupFile = $backupDir . 'backup_' . date('Y_m_d-H_i_s') . '.sql';
$command = "mysqldump -h" . DBHOST . " -u" . DBUSER . " -p" . DBPASS . " " . DBNAME . " > $backupFile";
exec($command, $output, $returnValue);
if ($returnValue === 0) {
    echo "ok";
} else{
	echo "err";
}
$mysqli->close();
?>