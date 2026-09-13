<?php
if (!defined('PONMONITOR')){
	header('HTTP/1.1 403 Forbidden');
	header('Location: ../');
	die('Hacking attempt!');
}
$uploaddir = ROOT_DIR."/file/photo/";
require ENGINE_DIR.'init.time.php';	
require ENGINE_DIR.'init.license.php';
define('CONFIG',true);
require ENGINE_DIR.'database.php';
require ENGINE_DIR.'init.pmon.php';
require ENGINE_DIR.'classes/cache.class.php';
require ENGINE_DIR.'init.sql.php';
require ENGINE_DIR.'classes/monitor.db.class.php';
require ENGINE_DIR.'classes/users.class.php';
$USER = $auth->requireAuth();
if(empty($USER) || !$USER) {
    require MODULE_PMON.'login.php';
}
require ENGINE_DIR.'init.lang.php';
require ENGINE_DIR.'classes/log.class.php';
require ENGINE_DIR.'classes/message.class.php';
require ENGINE_DIR.'classes/access.class.php';
require ENGINE_DIR.'functions/core.php';
require ENGINE_DIR.'classes/clean.class.php';
require ENGINE_DIR.'classes/core.class.php';
require ENGINE_DIR.'classes/tpl.class.php';
require ENGINE_DIR.'classes/route.class.php';
$go =  new Route();
$tpl =  new TemplateMonitor;
$SQLListlocation = getLocation();
$get_info = isset($_GET['i']) ? Clean::text($_GET['i']) : null;
require ROOT_DIR.'/inc/init.module.php';
require ROOT_DIR.'/inc/init.html.php';
require ROOT_DIR.'/inc/init.cms.php';
?>