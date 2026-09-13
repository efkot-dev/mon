<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

$timer = date('Y-m-d H:i:s');
require ROOT_DIR . '/inc/init.monitor.php';

if (empty($confPMon['FIBERMAP']) || (int)$confPMon['FIBERMAP'] !== 1) {
    return;
}

function fetchOnusByKeys(PDO $pdo, array $keys): array
{
    $result = ['mac' => [], 'sn' => []];
    if (empty($keys)) {
        return $result;
    }
    $chunkSize = 500;
    foreach (array_chunk($keys, $chunkSize) as $chunk) {
        $ph = implode(',', array_fill(0, count($chunk), '?'));
        $sql = "SELECT idonu, status, olt, portolt, mac, sn  FROM onus WHERE mac IN ($ph) OR sn IN ($ph)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($chunk, $chunk));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $mac = (string)($row['mac'] ?? '');
            $sn = (string)($row['sn'] ?? '');
            if ($mac !== '') {
                $result['mac'][$mac] = $row;
            }
            if ($sn !== '') {
                $result['sn'][$sn] = $row;
            }
        }
    }
    return $result;
}
function ensureFiberErrorColumns(PDO $pdo): void
{
    $stmt = $pdo->query("SHOW COLUMNS FROM pontree LIKE 'port_error_today'");
    $hasToday = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$hasToday) {
        $pdo->exec("ALTER TABLE pontree ADD port_error_today BIGINT NOT NULL DEFAULT 0");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM pontree LIKE 'port_error_devices'");
    $hasDevices = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$hasDevices) {
        $pdo->exec("ALTER TABLE pontree ADD port_error_devices TEXT NULL");
    }
}
$stmtFrog = $pdo->query("SELECT id, onukey, pontree, ponelement FROM onusdata WHERE ponelement != '0'");
$frogOnus = $stmtFrog->fetchAll(PDO::FETCH_ASSOC);
if (!$frogOnus) {
    return;
}
$keys = [];
foreach ($frogOnus as $row) {
    $key = trim((string)($row['onukey'] ?? ''));
    if ($key !== '') {
        $keys[$key] = true;
    }
}
$onusIndex = fetchOnusByKeys($pdo, array_keys($keys));
$statusOnus = [];
$onuOnline = [];
$onuOffline = [];
$onuNone = [];
$onuDevice = [];
$treePorts = [];
foreach ($frogOnus as $row) {
    $onukey = trim((string)($row['onukey'] ?? ''));
    $pontreeId = (int)($row['pontree'] ?? 0);
    $ponelementId = (int)($row['ponelement'] ?? 0);
    if ($onukey === '' || $pontreeId <= 0 || $ponelementId <= 0) {
        continue;
    }
    $onu = $onusIndex['mac'][$onukey] ?? ($onusIndex['sn'][$onukey] ?? null);
    if (!$onu || !isset($onu['idonu'], $onu['status'])) {
        continue;
    }
    $status = (int)$onu['status'];
    $idonu = (int)$onu['idonu'];
    $olt = (int)($onu['olt'] ?? 0);
    $portolt = (int)($onu['portolt'] ?? 0);
    $statusOnus[$ponelementId][$idonu] = $status;
    $onuOnline[$pontreeId] = $onuOnline[$pontreeId] ?? 0;
    $onuOffline[$pontreeId] = $onuOffline[$pontreeId] ?? 0;
    $onuNone[$pontreeId] = $onuNone[$pontreeId] ?? 0;
    if ($olt > 0) {
        $onuDevice[$pontreeId][$olt] = true;
    }
    if ($olt > 0 && $portolt > 0) {
        $pairKey = $olt . ':' . $portolt;
        $treePorts[$pontreeId][$pairKey] = ['olt' => $olt, 'portolt' => $portolt];
    }
    if ($status === 1) {
        $onuOnline[$pontreeId]++;
    } elseif ($status === 2) {
        $onuOffline[$pontreeId]++;
    } else {
        $onuNone[$pontreeId]++;
    }
}

$stmtUpdatePontreeCounts = $pdo->prepare("UPDATE pontree  SET onu = :onu, onu_online = :online, onu_offline = :offline  WHERE id = :id");
foreach ($onuOnline as $treeId => $onlineCount) {
    $offlineCount = (int)($onuOffline[$treeId] ?? 0);
    $stmtUpdatePontreeCounts->execute([':onu' => (int)$onlineCount + $offlineCount,':online' => (int)$onlineCount,':offline' => $offlineCount,':id' => (int)$treeId]);
}

$stmtUpdatePontreeDevice = $pdo->prepare("UPDATE pontree SET device = :device WHERE id = :id");
foreach ($onuDevice as $treeId => $olts) {
    $deviceCsv = implode(',', array_keys($olts));
    $stmtUpdatePontreeDevice->execute([':device' => $deviceCsv,':id' => (int)$treeId]);
}
$stmtUpdatePonelement = $pdo->prepare("UPDATE ponelement SET status = :status, perevirka = :timer WHERE id = :id");
foreach ($statusOnus as $ponelementId => $statuses) {
    $status = 2;
    foreach ($statuses as $s) {
        if ((int)$s === 1) {
            $status = 1;
            break;
        }
    }
    $stmtUpdatePonelement->execute([':status' => $status,':timer' => $timer,':id' => (int)$ponelementId]);
}
if (!empty($confPMon['FIBERMAP_ERROR']) && (int)$confPMon['FIBERMAP_ERROR'] === 1) {
    ensureFiberErrorColumns($pdo);
    $allTreeIds = [];
    foreach (array_keys($onuOnline) as $treeId) {
        $allTreeIds[(int)$treeId] = true;
    }
    foreach (array_keys($onuOffline) as $treeId) {
        $allTreeIds[(int)$treeId] = true;
    }
    foreach (array_keys($onuNone) as $treeId) {
        $allTreeIds[(int)$treeId] = true;
    }
    $allPairs = [];
    $allOltIds = [];
    foreach ($treePorts as $ports) {
        foreach ($ports as $pairKey => $pair) {
            $allPairs[$pairKey] = $pair;
            $allOltIds[$pair['olt']] = true;
        }
    }
    $oltNames = [];
    if (!empty($allOltIds)) {
        $oltIds = array_keys($allOltIds);
        $ph = implode(',', array_fill(0, count($oltIds), '?'));
        $stmtOlts = $pdo->prepare("SELECT id, place FROM switch WHERE id IN ($ph)");
        $stmtOlts->execute($oltIds);
        foreach ($stmtOlts->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $oltNames[(int)$row['id']] = (string)($row['place'] ?? '');
        }
    }
    $errorsByPair = [];
    if (!empty($allOltIds)) {
        $oltIds = array_keys($allOltIds);
        $ph = implode(',', array_fill(0, count($oltIds), '?'));
        $sqlErr = "
            SELECT deviceid, llid, COALESCE(SUM(newin + newout), 0) AS err_today
            FROM switch_port_err
            WHERE added >= CURDATE()
              AND deviceid IN ($ph)
            GROUP BY deviceid, llid
        ";
        $stmtErr = $pdo->prepare($sqlErr);
        $stmtErr->execute($oltIds);
        foreach ($stmtErr->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pairKey = (int)$row['deviceid'] . ':' . (int)$row['llid'];
            $errorsByPair[$pairKey] = (int)($row['err_today'] ?? 0);
        }
    }
    $stmtReadTreeErrors = $pdo->prepare("SELECT port_error_today, port_error_devices FROM pontree WHERE id = :id LIMIT 1");
    $stmtUpdateTreeErrors = $pdo->prepare("
        UPDATE pontree
        SET port_error_today = :err_today, port_error_devices = :devices
        WHERE id = :id
    ");
    foreach (array_keys($allTreeIds) as $treeId) {
        $ports = $treePorts[$treeId] ?? [];
        $sumErrors = 0;
        $devices = [];
        foreach ($ports as $pairKey => $pair) {
            $err = (int)($errorsByPair[$pairKey] ?? 0);
            if ($err <= 0) {
                continue;
            }
            $sumErrors += $err;
            $oltName = $oltNames[$pair['olt']] ?? ('OLT ' . $pair['olt']);
            $devices[] = $oltName . ' / P' . $pair['portolt'] . ' (+' . $err . ')';
        }
        $devicesText = implode(', ', $devices);

        $stmtReadTreeErrors->execute([':id' => (int)$treeId]);
        $current = $stmtReadTreeErrors->fetch(PDO::FETCH_ASSOC) ?: ['port_error_today' => 0, 'port_error_devices' => ''];
        $currentErr = (int)($current['port_error_today'] ?? 0);
        $currentDevices = (string)($current['port_error_devices'] ?? '');

        if ($currentErr !== $sumErrors || $currentDevices !== $devicesText) {
            $stmtUpdateTreeErrors->execute([
                ':err_today' => $sumErrors,
                ':devices' => $devicesText !== '' ? $devicesText : null,
                ':id' => (int)$treeId,
            ]);
        }
    }
}
?>
