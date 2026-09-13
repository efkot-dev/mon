<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.monitor.php';
if (!empty($confPMon['FIBERMAP']) && (int)$confPMon['FIBERMAP'] === 1) {
    $tempbackup = ROOT_DIR . '/file/backup/';
    if (!is_dir($tempbackup)) {
        @mkdir($tempbackup, 0775, true);
    }
    $sqlOrphan = "SELECT p.* FROM ponelement p LEFT JOIN pontree t ON p.tree = t.id WHERE t.id IS NULL";
    $orphanRows = $pdo->query($sqlOrphan)->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($orphanRows)) {
        $safeTs = date('Ymd_His');
        $filePath = $tempbackup . 'pon_element_' . $safeTs . '.data';
        file_put_contents($filePath, serialize($orphanRows));
        $orphanIds = array_values(array_unique(array_map(static fn($r) => (int)($r['id'] ?? 0), $orphanRows)));
        $orphanIds = array_values(array_filter($orphanIds, static fn($v) => $v > 0));
        if (!empty($orphanIds)) {
            $ph = implode(',', array_fill(0, count($orphanIds), '?'));
            $stmtDeleteOrphan = $pdo->prepare("DELETE FROM ponelement WHERE id IN ($ph)");
            $stmtDeleteOrphan->execute($orphanIds);
        }
    }
}
$sqlBrokenOnu = "SELECT o.idonu  FROM onus o LEFT JOIN switch s ON s.id = o.olt WHERE o.olt IS NULL OR o.olt = 0 OR s.id IS NULL";
$brokenOnuIds = $pdo->query($sqlBrokenOnu)->fetchAll(PDO::FETCH_COLUMN);
if (!empty($brokenOnuIds)) {
    $brokenOnuIds = array_values(array_unique(array_map('intval', $brokenOnuIds)));
    foreach ($brokenOnuIds as $idonu) {
        if ($idonu > 0) {
            delete_onu($idonu);
        }
    }
}
?>
