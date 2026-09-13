<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
header('Content-Type: application/json; charset=utf-8');
$real = isset($_GET['real']) ? Clean::int($_GET['real']) : 0;
$portid = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
$types = isset($_GET['types']) ? Clean::int($_GET['types']) : (isset($_GET['t']) ? Clean::int($_GET['t']) : 0);
$dayInput = isset($_GET['d']) ? Clean::text($_GET['d']) : null;
if ($portid <= 0) {
    echo json_encode(['dates'=>[], 'in_data'=>[], 'out_data'=>[]], JSON_UNESCAPED_UNICODE);
    exit;
}
$unitIsMbps = ($types === 100);
$divisor = $unitIsMbps ? 1e6 : 1e9;// Mbps або Gbps
$max_change = $unitIsMbps ? 1110 : 5;
function hhmm_from_timestamp($ts) {
    if (!$ts) return null;
    if (is_numeric($ts)) $t = (int)$ts; else $t = strtotime($ts);
    if ($t === false) return null;
    return date('H:i', $t);
}
function out_json($dates, $in, $out) {
    echo json_encode(['dates'=> array_values($dates),'in_data' => array_values($in),'out_data'=> array_values($out)], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($real === 1) {
    $rows = $db->SimpleWhile("SELECT `timestamp`, `in_bps`, `out_bps` FROM snmp_data WHERE portid = '{$portid}' AND DATE(`timestamp`) = CURDATE() ORDER BY `timestamp` ASC");
    $dates = []; $in_data = []; $out_data = [];
    $prev_in = null; $prev_out = null;
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $dates[] = hhmm_from_timestamp($row['timestamp']) ?? '';
            $in_bps  = round(((float)$row['in_bps'])  / $divisor, 3);
            $out_bps = round(((float)$row['out_bps']) / $divisor, 3);
            if ($prev_in !== null  && abs($in_bps  - $prev_in)  > $max_change)  $in_bps  = $prev_in;
            if ($prev_out !== null && abs($out_bps - $prev_out) > $max_change)  $out_bps = $prev_out;
            $in_data[]  = $in_bps;
            $out_data[] = $out_bps;
            $prev_in  = $in_bps;
            $prev_out = $out_bps;
        }
    }
    out_json($dates, $in_data, $out_data);
}
$selectDaySql = '';
if ($dayInput !== null && $dayInput !== '') {
    $dt = false;
    $dt = DateTime::createFromFormat('Y-m-d', $dayInput);
    if (!$dt) {
        $dt = DateTime::createFromFormat('d.m.Y', $dayInput);
    }
    if (!$dt) {
        $dtDM = DateTime::createFromFormat('d.m', $dayInput);
        if ($dtDM) {
            $dt = new DateTime();
            $dt->setDate((int)date('Y'), (int)$dtDM->format('n'), (int)$dtDM->format('j'));
            $dt->setTime(0,0,0);
        }
    }
    if ($dt) {
        $start = $dt->format('Y-m-d 00:00:00');
        $end   = $dt->format('Y-m-d 23:59:59');
        $selectDaySql = "AND `date` >= '{$start}' AND `date` <= '{$end}'";
    } else {
        out_json([], [], []);
    }
} else {
    $selectDaySql = "AND `date` = CURDATE()";
}
$sql = "SELECT `date`, `traffic_day` FROM bandwidth_daily WHERE portid = '{$portid}' {$selectDaySql} ORDER BY `date` DESC LIMIT 1";
$rec = $db->Simple($sql);
$dates = []; $in_data = []; $out_data = [];
$prev_in = null; $prev_out = null;
if (!empty($rec) && !empty($rec['traffic_day'])) {
    $traffic = json_decode($rec['traffic_day'], true);
    if (is_array($traffic)) {
        foreach ($traffic as $row) {
            $time = isset($row['time']) ? $row['time'] : null;
            if (!$time && isset($row['timestamp'])) {
                $time = hhmm_from_timestamp($row['timestamp']);
            }
            if (!$time) $time = '';
            $dates[]  = $time;
            $in_bps   = round(((float)$row['in_bps'])  / $divisor, 3);
            $out_bps  = round(((float)$row['out_bps']) / $divisor, 3);
            if ($prev_in !== null  && abs($in_bps  - $prev_in)  > $max_change)  $in_bps  = $prev_in;
            if ($prev_out !== null && abs($out_bps - $prev_out) > $max_change)  $out_bps = $prev_out;
            $in_data[]  = $in_bps;
            $out_data[] = $out_bps;
            $prev_in  = $in_bps;
            $prev_out = $out_bps;
        }
    }
}
out_json($dates, $in_data, $out_data);
?>
