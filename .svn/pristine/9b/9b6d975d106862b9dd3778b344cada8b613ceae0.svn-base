<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
define('CONFIG', true);
require_once ROOT_DIR . '/inc/database.php';
require ENGINE_DIR . 'init.pmon.php';
require ENGINE_DIR . 'classes/scraper.class.php';
require 'vendor/autoload.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if ($mysqli->connect_error) {
    die("Помилка з'єднання з базою даних: " . $mysqli->connect_error);
}
$update_time = date('Y-m-d H:i:s');
$folder = ROOT_DIR.'/file/';
use Spatie\Async\Pool;

$Scraper = new PMonScraper($mysqli, $folder);
$devices = $Scraper->list_device;

if (!empty($devices)) {
    $pool = Pool::create();    
    foreach ($devices as $olt) {        
        $pool->add(function() use ($olt, $folder) {
            define('PONMONITOR', true);
            require 'inc/database.php';
            require 'inc/classes/scraper.class.php';
            $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
            if ($mysqli->connect_error) {
                die("Помилка з'єднання з базою даних: " . $mysqli->connect_error);
            }
            $scraper = new PMonScraper($mysqli, $folder);
            $data = $scraper->get_scrape($olt);            
            if (!empty($data)) {
                return [
                    'device' => $olt,'ont' => $data,'timestamp' => date('Y-m-d H:i:s')
                ];
            }
            return null;
        });
    }
    $results = $pool->wait();
    $temp_repo = [];
    foreach ($results as $result) {
        if ($result !== null) {
            $temp_repo[] = $result;
        }
    }
    if (!empty($temp_repo)) {
        $temp_file = $folder . 'reger.'.md5(DBNAME).'.pmon';
        file_put_contents($temp_file, json_encode($temp_repo, JSON_PRETTY_PRINT));
    }
}
?>
