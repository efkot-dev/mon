<?php
if (!defined('PONMONITOR')) { die('Hacking attempt!'); }

$metatags = ['title' => $lang['onuerror'], 'description' => $lang['onuerror'], 'page' => 'onuerror'];
$result = '';

$idolt = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$allSwitch = getPMonSwitch();
$switches = [];

if (is_array($allSwitch)) {
    foreach ($allSwitch as $swid => $sw) {
        $sid = (int)$swid;
        if ($sid > 0 && $access->get('dev' . $sid)) {
            $switches[$sid] = $sw;
        }
    }
}

if (empty($switches)) {
    $result .= '<div class="block_white p20">' . $lang['empty'] . '</div>';
    $tpl->load_template('onuerror.tpl');
    $tpl->set('{result}', $result);
    $tpl->compile('content');
    $tpl->clear();
    return;
}

if ($idolt > 0 && !isset($switches[$idolt])) {
    $idolt = 0;
}

$oltIds = array_keys($switches);
$in = implode(',', array_fill(0, count($oltIds), '?'));
$stmtTotal = $pdo->prepare("SELECT olt, COUNT(*) AS total_onu FROM onus WHERE olt IN ($in) GROUP BY olt");
$stmtTotal->execute($oltIds);
$totalsByOlt = [];
foreach ($stmtTotal->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $totalsByOlt[(int)$r['olt']] = (int)$r['total_onu'];
}
$sql = "
    SELECT
        o.idonu, o.olt, o.dist, o.status, o.keyonu, o.name,
        COALESCE(o.sn, o.mac) AS sn,
        o.rx, o.inface,
        COALESCE(oe_last.error, 0) AS last_error,
        COALESCE(oe_day.delta_today, 0) AS delta_today
    FROM onus o
    LEFT JOIN (
        SELECT e.idonu, e.error
        FROM onus_error e
        INNER JOIN (
            SELECT idonu, MAX(id) AS max_id
            FROM onus_error
            GROUP BY idonu
        ) mx ON mx.idonu = e.idonu AND mx.max_id = e.id
    ) oe_last ON oe_last.idonu = o.idonu
    LEFT JOIN (
        SELECT idonu, SUM(GREATEST(riznica, 0)) AS delta_today
        FROM onus_error
        WHERE added >= CURRENT_DATE()
        GROUP BY idonu
    ) oe_day ON oe_day.idonu = o.idonu
    WHERE o.olt IN ($in)
";
$params = $oltIds;
if ($idolt > 0) {
    $sql .= " AND o.olt = ?";
    $params[] = $idolt;
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$onuByOlt = [];
$aggByOlt = [];
foreach ($rows as $onu) {
    $oltid = (int)$onu['olt'];
    $last = (float)($onu['last_error'] ?? 0);
    $delta = (float)($onu['delta_today'] ?? 0);
    if ($last <= 10 && $delta <= 0) {
        continue;
    }
    $rxRaw = trim((string)($onu['rx'] ?? ''));
    $rxNum = is_numeric($rxRaw) ? (float)$rxRaw : 0.0;
    $rxAbs = abs($rxNum);
    $degPct = ($delta > 0 && $rxAbs > 0) ? round(($delta / $rxAbs) * 100, 1) : 0.0;
    $onuByOlt[$oltid][] = [
        'idonu' => (int)$onu['idonu'],
        'status' => (int)$onu['status'],
        'inface' => (string)$onu['inface'],
        'last_error' => $last,
        'delta_today' => $delta,
        'deg_pct' => $degPct,
        'rx' => $onu['rx'],
        'dist' => $onu['dist'],
        'name' => (string)$onu['name'],
        'sn' => (string)$onu['sn']
    ];
    if (!isset($aggByOlt[$oltid])) {
        $aggByOlt[$oltid] = [
            'degraded' => 0,
            'sum_delta' => 0.0,
            'sum_pct' => 0.0
        ];
    }
    $aggByOlt[$oltid]['degraded']++;
    $aggByOlt[$oltid]['sum_delta'] += $delta;
    $aggByOlt[$oltid]['sum_pct'] += $degPct;
}
$menu = "<div class='menu_olt_left'>";
if ($idolt > 0) {
    $menu .= "<a class='menu-sub' href='/?do=onuerror'><img src='../style/img/pmon_return.png'>Всі OLT</a>";
}

foreach ($switches as $sid => $sw) {
    $degraded = isset($aggByOlt[$sid]['degraded']) ? (int)$aggByOlt[$sid]['degraded'] : 0;
    $total = isset($totalsByOlt[$sid]) ? (int)$totalsByOlt[$sid] : 0;
    if ($degraded <= 0) {
        continue;
    }
    $degRatio = ($total > 0) ? round(($degraded / $total) * 100) : 0;
    $bar = "<div class=\"port_precent\"><div class='load' style='width:{$degRatio}%;'></div></div>";
    $label = htmlspecialchars((string)$sw['place'], ENT_QUOTES, 'UTF-8');
    $menu .= "<a class='menu-sub' href='/?do=onuerror&id={$sid}'>
        {$label}
        <span class=\"badont\">{$degraded}/{$total}</span>
        {$bar}
    </a>";
}
$menu .= "</div>";
$result .= '<div class="container"><div class="left-column">' . $menu . '</div><div class="right-column">';
if ($idolt > 0 && isset($switches[$idolt])) {
    $result .= '<div class="pon_list_olt" style="margin-bottom:8px;">Фільтр OLT: ' . htmlspecialchars((string)$switches[$idolt]['place'], ENT_QUOTES, 'UTF-8') . '</div>';
}
$result .= '<table class="resp-tab"><thead><tr>
    <th width="2%">Status</th>
    <th width="12%">Interface</th>
    <th width="8%">Error</th>
    <th width="8%">RX</th>
    <th width="8%">Dist</th>
    <th width="10%">Погіршення</th>
    <th width="10%">Деградація</th>
    <th>Інформація</th>
</tr></thead><tbody>';
$hasRows = false;
foreach ($switches as $sid => $sw) {
    if ($idolt > 0 && $sid !== $idolt) {
        continue;
    }
    if (empty($onuByOlt[$sid])) {
        continue;
    }
    $hasRows = true;
    $degraded = (int)$aggByOlt[$sid]['degraded'];
    $total = isset($totalsByOlt[$sid]) ? (int)$totalsByOlt[$sid] : 0;
    $degRatio = ($total > 0) ? round(($degraded / $total) * 100, 1) : 0;
    $avgDelta = ($degraded > 0) ? round($aggByOlt[$sid]['sum_delta'] / $degraded, 2) : 0;
    $avgPct = ($degraded > 0) ? round($aggByOlt[$sid]['sum_pct'] / $degraded, 1) : 0;

    $swTitle = htmlspecialchars((string)$sw['place'], ENT_QUOTES, 'UTF-8');
    $result .= '<tr><td class="pon_list_olt" colspan="8">
        <a href="/?do=onuerror&id=' . $sid . '">' . $swTitle . '</a>
        <span style="margin-left:8px;color:#5d6f7d;">Погіршення: ' . $degraded . '/' . $total . ' (' . $degRatio . '%), середнє +' . $avgDelta . ' dBm (' . $avgPct . '%)</span>
    </td></tr>';

    foreach ($onuByOlt[$sid] as $r) {
        $name = htmlspecialchars((string)$r['name'], ENT_QUOTES, 'UTF-8');
        $sn = htmlspecialchars((string)$r['sn'], ENT_QUOTES, 'UTF-8');
        $inface = htmlspecialchars((string)$r['inface'], ENT_QUOTES, 'UTF-8');
        $deltaTxt = ($r['delta_today'] > 0 ? '+' : '') . round($r['delta_today'], 2);
        $degTxt = round($r['deg_pct'], 1) . '%';

        $result .= '<tr>
            <td><span class="statusonu st_' . $r['status'] . '"></span></td>
            <td class="td_url"><a href="/?do=onu&id=' . $r['idonu'] . '" ' . ($r['status'] == 2 ? 'class="colorgrey"' : '') . '>' . $inface . '</a></td>
            <td><font color="grey">' . (int)$r['last_error'] . '</font></td>
            <td>' . signalTerminal($r['rx']) . '</td>
            <td><font color="#1f7bc3">' . $r['dist'] . '</font></td>
            <td><font color="' . ($r['delta_today'] > 0 ? '#c0392b' : '#607d8b') . '">' . $deltaTxt . ' dBm</font></td>
            <td><font color="' . ($r['deg_pct'] >= 15 ? '#c0392b' : ($r['deg_pct'] >= 8 ? '#e67e22' : '#607d8b')) . '">' . $degTxt . '</font></td>
            <td class="typesmac">' . $name . ' ' . $sn . '</td>
        </tr>';
    }
}

if (!$hasRows) {
    $result .= '<tr><td colspan="8">' . $lang['empty'] . '</td></tr>';
}
$result .= '</tbody></table>';
$result .= '</div></div>';
$tpl->load_template('onuerror.tpl');
$tpl->set('{result}', $result);
$tpl->compile('content');
$tpl->clear();
?>
