<?php
if (!defined('PONMONITOR')) { 
	define('PONMONITOR', true); 
}
require ROOT_DIR.'/inc/init.monitor.php';
function deltaCounter(int|float $prev, int|float $curr): int {
    if ($curr < $prev) { 
        return 0;
    }
    return (int)max(0, $curr - $prev);
}
function saveErrPort(PDO $pdo, int $deviceid, int $llid, array $counters, string $ts): void {
    $in = isset($counters['in'])  ? (int)str_replace('4294967295','0',$counters['in'])  : 0;
    $out = isset($counters['out']) ? (int)str_replace('4294967295','0',$counters['out']) : 0;
    $stmt = $pdo->prepare("SELECT inerror, outerror FROM switch_port_err WHERE deviceid=:d AND llid=:l ORDER BY added DESC LIMIT 1");
    $stmt->execute([':d'=>$deviceid, ':l'=>$llid]);
    $prev = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['inerror'=>0,'outerror'=>0];
    $newin = deltaCounter((int)$prev['inerror'],  $in);
    $newout = deltaCounter((int)$prev['outerror'], $out);
    $ins = $pdo->prepare("INSERT INTO switch_port_err (deviceid, llid, inerror, outerror, added, newin, newout) VALUES (:d,:l,:inerr,:outerr,:added,:newin,:newout)");
    $ins->execute([':d'=>$deviceid, ':l'=>$llid, ':inerr'=>$in, ':outerr'=>$out,':added'=>$ts, ':newin'=>$newin, ':newout'=>$newout]);
}
$now = date('Y-m-d H:i:s');
$sw = $pdo->query("SELECT id, place, oidid, monitor FROM switch WHERE monitor='yes'")->fetchAll(PDO::FETCH_ASSOC);
if (!$sw) { 
	die("no_switches\n"); 
}
$swById = [];
foreach ($sw as $s) $swById[(int)$s['id']] = $s;
$ports = $pdo->query("SELECT id, deviceid, llid, nameport, descrport FROM switch_port WHERE monitor='yes' AND error='yes'")->fetchAll(PDO::FETCH_ASSOC);
$resultErr = [];
if ($ports) {
    $i = 0;
    foreach ($ports as $p) {
        $did = (int)$p['deviceid']; $llid = (int)$p['llid'];
        if (!isset($swById[$did]) || $swById[$did]['monitor']!=='yes') continue;
        $r = api__($config['monitorapi'], [
            'do'=>'port','types'=>'error','keyport'=>$llid,'id'=>$did
        ]);
        if (is_array($r)) {
            $resultErr["$did:$llid"] = $r;
        }
        if ((++$i % 10) === 0) usleep(300000);
    }
    foreach ($ports as $p) {
        $did = (int)$p['deviceid']; $llid = (int)$p['llid'];
        $key = "$did:$llid";
        if (!isset($resultErr[$key])) continue;
        saveErrPort($pdo, $did, $llid, $resultErr[$key], $now);
    }
    $updDay = $pdo->prepare("SELECT COALESCE(SUM(newin),0) AS s_in, COALESCE(SUM(newout),0) AS s_out  FROM switch_port_err  WHERE deviceid=:d AND llid=:l AND added >= CURDATE()");
    $updSnap = $pdo->prepare("SELECT COALESCE(inerror,0) AS inerror, COALESCE(outerror,0) AS outerror FROM switch_port_err WHERE deviceid=:d AND llid=:l ORDER BY added DESC LIMIT 1");
    $updPort = $pdo->prepare("UPDATE switch_port  SET error_count = :cnt, error_today = :today WHERE deviceid = :d AND llid = :l LIMIT 1");
    foreach ($ports as $p) {
        $did = (int)$p['deviceid']; $llid = (int)$p['llid'];
        $key = "$did:$llid";
        if (!isset($resultErr[$key])) continue;
        $updDay->execute([':d' => $did, ':l' => $llid]);
        $day = $updDay->fetch(PDO::FETCH_ASSOC) ?: ['s_in' => 0, 's_out' => 0];
        $today = (int)$day['s_in'] + (int)$day['s_out'];
        $updSnap->execute([':d' => $did, ':l' => $llid]);
        $snap = $updSnap->fetch(PDO::FETCH_ASSOC) ?: ['inerror' => 0, 'outerror' => 0];
        $cnt = (int)$snap['inerror'] + (int)$snap['outerror'];
        $updPort->execute([':cnt' => $cnt,':today' => $today,':d' => $did,':l' => $llid]);
    }
}
?>
