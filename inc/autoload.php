<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt system!');
}
require_once ROOT_DIR . '/vendor/autoload.php';

use Predis\Client;

$redis = new Client();
?>