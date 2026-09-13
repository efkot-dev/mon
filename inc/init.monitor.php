<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('CONFIG',true);
require ROOT_DIR.'/inc/init.support.php';
require ROOT_DIR.'/inc/init.sql.php';
require ROOT_DIR.'/inc/database.php';
require ENGINE_DIR.'init.pmon.php';
require ENGINE_DIR.'classes/data.class.php';
require ENGINE_DIR.'classes/cache.class.php';
require ENGINE_DIR.'classes/monitor.db.class.php';
require ROOT_DIR.'/inc/init.config.php';
require ENGINE_DIR.'init.lang.php';
require ENGINE_DIR.'classes/log.class.php';
require ENGINE_DIR.'classes/clean.class.php';
require ENGINE_DIR.'functions/core.php';
require ENGINE_DIR.'functions/monitor.php';
require ENGINE_DIR.'classes/snmp.class.php';
require ENGINE_DIR.'init.olt.php';
require ENGINE_DIR.'classes/core.class.php';
require ENGINE_DIR.'classes/equipment.class.php';
?>
