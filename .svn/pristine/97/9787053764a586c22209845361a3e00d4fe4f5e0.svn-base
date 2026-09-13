<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
if (!checkAccess(4)) {
    $go->redirect('main');
}
try {
    switch ($act) {
        case 'porterror':
            $pdo->exec('TRUNCATE TABLE switch_port_err');
            $pdo->exec("UPDATE `switch_port` SET `error_count` = '0', `error_today` = '0'");
            break;
        case 'logger':
            $pdo->exec('TRUNCATE TABLE pmonstats');
            $pdo->exec('TRUNCATE TABLE swlogport');
            $pdo->exec('TRUNCATE TABLE pingstats');
            break;
        case 'voltage':
            $pdo->exec('TRUNCATE TABLE mon_voltage');
            break;
        case 'historysignal':
            $pdo->exec('TRUNCATE TABLE historysignal');
            break;
        case 'historysignals':
            $pdo->exec('TRUNCATE TABLE rxolt_signal');
            break;
        case 'sender':
            $pdo->exec('TRUNCATE TABLE sender');
            break;
        case 'devicelogs':
            $pdo->exec('TRUNCATE TABLE devicelogs');
            break;
    }
} catch (PDOException $e) {
    error_log('[PDO ERROR] ' . $e->getMessage());

}
$go->redirect('operator');
?>