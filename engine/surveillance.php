<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.core.php';
require ROOT_DIR . '/inc/functions/surveillance.php';
if (isset($confPMon['IPCAM']) && !empty($confPMon['IPCAM']) && $confPMon['IPCAM'] == 1) {
$sql_dev = $pdo->prepare("SELECT id, oidid, netip, snmpro,status FROM surveillance WHERE monitor = 'yes'");
$sql_dev->execute();
$surveillance = $sql_dev->fetchAll(PDO::FETCH_ASSOC);
$processes = [];
$maxChildren = 10;
$running = 0;
foreach ($surveillance as $dev) {
    while ($running >= $maxChildren) {
        pcntl_wait($status);
        $running--;
    }
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("bad_fork");
    } elseif ($pid) {
        $running++;
        $processes[] = $pid;
    } else {
        $childPdo = getNewPDO();
        $data = get_oid_surveillance($dev['oidid']);
		if ($data !== false) {
            result_surveillance($data, $dev, $childPdo);
        }
        exit(0);
    }
}

// Очікуємо завершення решти процесів
while ($running > 0) {
    pcntl_wait($status);
    $running--;
}
}
?>
