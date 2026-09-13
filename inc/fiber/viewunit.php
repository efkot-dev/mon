<?php
if (!defined('PONMONITOR') && !defined('FIBER')) {
    die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if (isset($id) && $id > 0) {
	$sql_pon_unit = $pdo->prepare('SELECT id, name, lan FROM ponunit WHERE id = ' . (int)$id);
    $sql_pon_unit->execute();
    $sql_unit = $sql_pon_unit->fetch(PDO::FETCH_ASSOC);	
    $metatags = ['title' => $lang['volsmeraja'] . ' ' . $sql_unit['name'], 'description' => $lang['list'], 'page' => 'viewunit'];
    $bar_tpl .= '
    <div class="nav-bar">
		<a href="/"><i class="fi fi-rr-apps"></i>' . $lang['volsmeraja'] . '</a>
		<a href="?do=fiber&act=unit"><i class="fi fi-rr-angle-left"></i>' . $lang['volsmeraja'] . '</a>
		<span class="active"><i class="fi fi-rr-angle-left"></i>' . $sql_unit['name'] . '</span>
    </div>';
    $sql_pon_tree = "SELECT id, name, mereja, onu, onu_online, onu_offline, device, port_error_today, port_error_devices FROM pontree WHERE unit_id = '" . (int)$sql_unit['id'] . "' AND mereja = 'pon'";
    $sql_pon = $pdo->prepare($sql_pon_tree);
    $sql_pon->execute();
    $pon_tree = $sql_pon->fetchAll(PDO::FETCH_ASSOC);
    $degradeByTree = [];
    $degradeUnitOnu = 0;
    $degradeUnitTrees = 0;
    if (!empty($pon_tree)) {
        $ponTreeIdsForDeg = array_map(static fn($r) => (int)$r['id'], $pon_tree);
        $makePh = static function (array $ids, string $prefix): array {
            $ph = [];
            $params = [];
            foreach ($ids as $i => $val) {
                $key = ':' . $prefix . $i;
                $ph[] = $key;
                $params[$key] = (int)$val;
            }
            return [$ph, $params];
        };
        [$tPh1, $tParams1] = $makePh($ponTreeIdsForDeg, 'pt1_');
        [$tPh2, $tParams2] = $makePh($ponTreeIdsForDeg, 'pt2_');
        $sqlTreeOnu = "
            SELECT od.pontree, o.idonu
            FROM onusdata od
            JOIN onus o ON o.mac = od.onukey
            WHERE od.pontree IN (" . implode(',', $tPh1) . ")
            UNION
            SELECT od.pontree, o.idonu
            FROM onusdata od
            JOIN onus o ON o.sn = od.onukey
            WHERE od.pontree IN (" . implode(',', $tPh2) . ")
        ";
        $stmt = $pdo->prepare($sqlTreeOnu);
        $stmt->execute($tParams1 + $tParams2);
        $treeOnuRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $onuTrees = [];
        $allOnuIds = [];
        foreach ($treeOnuRows as $m) {
            $treeId = (int)($m['pontree'] ?? 0);
            $onuId = (int)($m['idonu'] ?? 0);
            if ($treeId <= 0 || $onuId <= 0) {
                continue;
            }
            $onuTrees[$treeId][$onuId] = true;
            $allOnuIds[$onuId] = true;
        }
        $onuIds = array_keys($allOnuIds);
        $histByOnu = [];
        if (!empty($onuIds)) {
            [$oPh, $oParams] = $makePh($onuIds, 'onu_');
            $sqlHist = "
                SELECT `onu`, `signal`, `datetime`
                FROM `historysignal`
                WHERE `onu` IN (" . implode(',', $oPh) . ")
                  AND `datetime` >= (NOW() - INTERVAL 24 HOUR)
                ORDER BY `onu` ASC, `datetime` ASC
            ";
            $stmt = $pdo->prepare($sqlHist);
            $stmt->execute($oParams);
            $histRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($histRows as $hr) {
                $onuId = (int)($hr['onu'] ?? 0);
                if ($onuId <= 0) {
                    continue;
                }
                $signalRaw = str_replace(',', '.', (string)($hr['signal'] ?? ''));
                $signalVal = is_numeric($signalRaw) ? (float)$signalRaw : null;
                if ($signalVal === null) {
                    continue;
                }
                $histByOnu[$onuId][] = $signalVal;
            }
        }
        $degradeOnu = [];
        foreach ($histByOnu as $onuId => $vals) {
            $n = count($vals);
            if ($n < 8) {
                continue;
            }
            $window = max(3, (int)floor($n * 0.25));
            $firstPart = array_slice($vals, 0, $window);
            $lastPart = array_slice($vals, -$window);
            $firstAvg = array_sum($firstPart) / max(1, count($firstPart));
            $lastAvg = array_sum($lastPart) / max(1, count($lastPart));
            $drop = $firstAvg - $lastAvg;
            $downSteps = 0;
            $upSteps = 0;
            for ($i = 1; $i < $n; $i++) {
                $delta = $vals[$i - 1] - $vals[$i];
                if ($delta >= 0.25) {
                    $downSteps++;
                } elseif ($delta <= -0.25) {
                    $upSteps++;
                }
            }
            $trendRatio = ($n > 1) ? ($downSteps / ($n - 1)) : 0;
            $recentRecovery = false;
            if ($n >= 6) {
                $prev3 = array_slice($vals, -6, 3);
                $last3 = array_slice($vals, -3, 3);
                $prev3Avg = array_sum($prev3) / max(1, count($prev3));
                $last3Avg = array_sum($last3) / max(1, count($last3));
                $recentRecovery = (($last3Avg - $prev3Avg) >= 0.8);
            }
            $hasStrongDrop = ($drop >= 2.0 && $lastAvg <= -20.5);
            $hasStableTrend = ($drop >= 1.4 && $trendRatio >= 0.60 && $downSteps >= 5 && $upSteps <= $downSteps);
            $hasDeepTail = ($drop >= 1.2 && $lastAvg <= -23.0);
            if (($hasStrongDrop || $hasStableTrend || $hasDeepTail) && !$recentRecovery) {
                $degradeOnu[$onuId] = true;
            }
        }
        foreach ($onuTrees as $treeId => $onuMap) {
            $total = count($onuMap);
            $degr = 0;
            foreach ($onuMap as $onuId => $v) {
                if (isset($degradeOnu[$onuId])) {
                    $degr++;
                }
            }
            $isDeg = (($total >= 5 && $degr >= 3 && ($degr / max(1, $total)) >= 0.30) || $degr >= 5) ? 1 : 0;
            $degradeByTree[(int)$treeId] = [
                'total' => (int)$total,
                'degraded' => (int)$degr,
                'is_degraded' => (int)$isDeg
            ];
            $degradeUnitOnu += (int)$degr;
            if ($isDeg) {
                $degradeUnitTrees++;
            }
        }
    }
	$resutltpl .= '
    <div id="boks">
        <div class="pontree">
            <div class="block_tree">
                <div class="tt_tree">
                    <h2><a href="/?do=fiber&act=map&unit=' . (int)$sql_unit['id'] . '">' . $sql_unit['name'] . '</a></h2>
                    <div class="add_pon">'
                        . ($access->get('edit_ponbox') ? '<a href="/?do=fiber&act=editunit&id=' . $sql_unit['id'] . '">'.$lang['fiber_setup'].'</a>' : '') .
                        ($access->get('edit_ponbox') ? '<a href="/?do=fiber&act=addtree&unit=' . $sql_unit['id'] . '">'.$lang['fiber_add_network'].'</a>' : '') .
                        '<a href="/?do=fiber&act=map&unit=' . (int)$sql_unit['id'] . '">'.$lang['fiber_map_network'].'</a>
                    </div>
                </div>
            </div>
        ';
    if (!$sql_unit['lan'] && $access->get('edit_ponbox')) {
        $resutltpl .= '<div class="empty_map"><img src="../style/img/dev_mess.png">'.$lang['fiber_edit_geo_inf'].' <a href="/?do=fiber&act=addmapper&id=' . (int)$sql_unit['id'] . '&unit=' . (int)$sql_unit['id'] . '">'.$lang['fiber_edit_geo'].'</a></div>';
    }
    if (!$pon_tree || count($pon_tree) === 0) {
        $resutltpl .= '';
    } else {
		$resutltpl .= '<style>
		.fiber-tree-badge{display:inline-flex;align-items:center;gap:4px;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;margin-left:6px;vertical-align:middle}
		.fiber-tree-badge.ok{background:#dcfce7;color:#166534}
		.fiber-tree-badge.warn{background:#fef3c7;color:#92400e}
		.fiber-tree-badge.alert{background:#fee2e2;color:#b91c1c}
		.fiber-tree-meta{margin-top:0px;font-size:11px;color:#64748b}
		.fiber-unit-table-wrap{width:100%;overflow:auto;-webkit-overflow-scrolling:touch}
		@media (max-width: 900px){
			#boks .add_pon{display:grid;grid-template-columns:1fr 1fr;gap:6px}
			#boks .add_pon a{font-size:12px;line-height:1.2;padding:8px 10px;text-align:center}
		}
		@media (max-width: 680px){
			#boks .resp-tab{border-collapse:separate;border-spacing:0 8px}
			#boks .resp-tab thead{display:none}
			#boks .resp-tab tbody tr{display:block;border:1px solid #dbe5f2;border-radius:10px;background:#fff;padding:6px}
			#boks .resp-tab tbody td{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;width:100%!important;border:none;padding:6px 4px}
			#boks .resp-tab tbody td::before{content:attr(data-label);font-size:11px;color:#64748b;min-width:105px;max-width:105px}
			#boks .resp-tab tbody td.name_pon{display:block}
			#boks .resp-tab tbody td.name_pon::before{content:none}
			#boks .resp-tab tbody td.name_pon a{line-height:1.3}
			#boks .min_desc_gpon span{display:flex;align-items:center;gap:6px}
		}
		</style>';
		$resutltpl .= '<div class="fiber-unit-table-wrap"><table class="resp-tab"><thead><tr><th>Types</th><th>' . $lang['name'] . '</th><th>' . $lang['pon_count'] . '</th><th>' . $lang['count'] . '</th><th>' . $lang['online'] . '</th><th>' . $lang['offline'] . '</th><th>'.$lang['fiber_nework_quality'].'</th></tr></thead><tbody>';
        $ponTreeIds = array_map(static fn($r) => (int)$r['id'], $pon_tree);
        $ph = implode(',', array_fill(0, count($ponTreeIds), '?'));
        $sqlCounts = "SELECT tree AS pontree, COUNT(*) AS count_id FROM ponelement WHERE tree IN ($ph) GROUP BY tree";
        $stmt = $pdo->prepare($sqlCounts);
        $stmt->execute($ponTreeIds);
        $countRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countByTree = [];
        foreach ($countRows as $r) {
            $countByTree[$r['pontree']] = $r['count_id'];
        }
        $sqlPorts = "
            SELECT x.pontree, x.olt, x.portolt, sp.pon AS port_name, COUNT(*) AS onu_count
            FROM (
                SELECT od.pontree, o.olt, o.portolt
                FROM onusdata od
                JOIN onus o ON o.mac = od.onukey
                WHERE od.pontree IN ($ph)
                UNION ALL
                SELECT od.pontree, o.olt, o.portolt
                FROM onusdata od
                JOIN onus o ON o.sn = od.onukey
                WHERE od.pontree IN ($ph)
            ) AS x
            LEFT JOIN switch_pon sp ON sp.oltid = x.olt AND sp.sfpid = x.portolt
            GROUP BY x.pontree, x.olt, x.portolt, sp.pon
            ORDER BY x.pontree, x.olt, x.portolt
        ";
        $stmt = $pdo->prepare($sqlPorts);
        $stmt->execute(array_merge($ponTreeIds, $ponTreeIds));
        $portsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $portsByTree = [];
        foreach ($portsRows as $r) {
            $portsByTree[(int)$r['pontree']][] = $r;
        }
        $sqlLosCounts = "
            SELECT
                z.pontree,
                COUNT(DISTINCT CASE WHEN z.status = 2 THEN z.idonu END) AS offline_fact,
                COUNT(DISTINCT CASE WHEN z.status = 2 AND z.offline >= (NOW() - INTERVAL 12 HOUR) THEN z.idonu END) AS offline_recent,
                COUNT(DISTINCT CASE WHEN z.status = 2 AND z.reason = 'err6' THEN z.idonu END) AS err6_count,
                COUNT(DISTINCT CASE WHEN z.status = 2 AND z.reason = 'err8' THEN z.idonu END) AS err8_count,
                COUNT(DISTINCT CASE WHEN z.status = 2 AND z.reason = 'err6' AND z.offline >= (NOW() - INTERVAL 12 HOUR) THEN z.idonu END) AS err6_recent,
                COUNT(DISTINCT CASE WHEN z.status = 2 AND z.reason = 'err8' AND z.offline >= (NOW() - INTERVAL 12 HOUR) THEN z.idonu END) AS err8_recent,
                COUNT(DISTINCT CASE WHEN z.status = 2 AND (z.reason = 'err6' OR z.reason = 'err8') THEN z.idonu END) AS los_count,
                COUNT(DISTINCT CASE WHEN z.status = 2 AND (z.reason = 'err6' OR z.reason = 'err8') AND z.offline >= (NOW() - INTERVAL 12 HOUR) THEN z.idonu END) AS los_recent
            FROM (
                SELECT od.pontree, o.olt, o.portolt, o.idonu, o.status, o.reason, o.offline
                FROM onusdata od
                JOIN onus o ON o.mac = od.onukey
                WHERE od.pontree IN ($ph)
                UNION
                SELECT od.pontree, o.olt, o.portolt, o.idonu, o.status, o.reason, o.offline
                FROM onusdata od
                JOIN onus o ON o.sn = od.onukey
                WHERE od.pontree IN ($ph)
            ) z
            GROUP BY z.pontree
        ";
        $stmt = $pdo->prepare($sqlLosCounts);
        $stmt->execute(array_merge($ponTreeIds, $ponTreeIds));
        $losCountRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $losByTree = [];
        foreach ($losCountRows as $r) {
            $losByTree[(int)$r['pontree']] = [
                'offline_fact' => (int)($r['offline_fact'] ?? 0),
                'offline_recent' => (int)($r['offline_recent'] ?? 0),
                'err6_count' => (int)($r['err6_count'] ?? 0),
                'err8_count' => (int)($r['err8_count'] ?? 0),
                'err6_recent' => (int)($r['err6_recent'] ?? 0),
                'err8_recent' => (int)($r['err8_recent'] ?? 0),
                'los_count' => (int)($r['los_count'] ?? 0),
                'los_recent' => (int)($r['los_recent'] ?? 0),
                'los_group_recent' => 0,
                'los_group_critical' => 0,
            ];
        }
        $sqlLosGroups = "
            SELECT t.pontree,
                   COUNT(*) AS los_group_recent,
                   SUM(CASE WHEN t.los_cnt >= 4 THEN 1 ELSE 0 END) AS los_group_critical
            FROM (
                SELECT x.pontree, x.olt, x.portolt, COUNT(DISTINCT x.idonu) AS los_cnt
                FROM (
                    SELECT od.pontree, o.olt, o.portolt, o.idonu, o.status, o.reason, o.offline
                    FROM onusdata od
                    JOIN onus o ON o.mac = od.onukey
                    WHERE od.pontree IN ($ph)
                    UNION
                    SELECT od.pontree, o.olt, o.portolt, o.idonu, o.status, o.reason, o.offline
                    FROM onusdata od
                    JOIN onus o ON o.sn = od.onukey
                    WHERE od.pontree IN ($ph)
                ) x
                WHERE x.status = 2
                  AND (x.reason = 'err6' OR x.reason = 'err8')
                  AND x.offline >= (NOW() - INTERVAL 12 HOUR)
                  AND x.portolt IS NOT NULL
                GROUP BY x.pontree, x.olt, x.portolt
                HAVING COUNT(DISTINCT x.idonu) >= 2
            ) t
            GROUP BY t.pontree
        ";
        $stmt = $pdo->prepare($sqlLosGroups);
        $stmt->execute(array_merge($ponTreeIds, $ponTreeIds));
        $losGroupRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($losGroupRows as $r) {
            $treeLosId = (int)$r['pontree'];
            if (!isset($losByTree[$treeLosId])) {
                $losByTree[$treeLosId] = [
                    'offline_fact' => 0,
                    'offline_recent' => 0,
                    'err6_count' => 0,
                    'err8_count' => 0,
                    'err6_recent' => 0,
                    'err8_recent' => 0,
                    'los_count' => 0,
                    'los_recent' => 0,
                    'los_group_recent' => 0,
                    'los_group_critical' => 0,
                ];
            }
            $losByTree[$treeLosId]['los_group_recent'] = (int)($r['los_group_recent'] ?? 0);
            $losByTree[$treeLosId]['los_group_critical'] = (int)($r['los_group_critical'] ?? 0);
        }
        $vyzol = [];
		if(!empty($pon_tree)){
        foreach ($pon_tree as $tree) {
            $treeId = (int)$tree['id'];
            $nameSafe = $tree['name'];
            $merejaSafe = $tree['mereja'];
            $onu_total = (int)($tree['onu'] ?? 0);
            $onu_online  = (int)($tree['onu_online'] ?? 0);
            $onu_offline = (int)($tree['onu_offline'] ?? 0);
            $offlineFact = (int)($losByTree[$treeId]['offline_fact'] ?? $onu_offline);
            $offlineRecent = (int)($losByTree[$treeId]['offline_recent'] ?? 0);
            $err6Count = (int)($losByTree[$treeId]['err6_count'] ?? 0);
            $err8Count = (int)($losByTree[$treeId]['err8_count'] ?? 0);
            $err6Recent = (int)($losByTree[$treeId]['err6_recent'] ?? 0);
            $err8Recent = (int)($losByTree[$treeId]['err8_recent'] ?? 0);
            $losRecent = (int)($losByTree[$treeId]['los_recent'] ?? 0);
            $losGroupRecent = (int)($losByTree[$treeId]['los_group_recent'] ?? 0);
            $losGroupCritical = (int)($losByTree[$treeId]['los_group_critical'] ?? 0);
            $allFact = $onu_online + $offlineFact;
            $offlinePct = $allFact > 0 ? (($offlineFact / $allFact) * 100) : 0.0;
            $statusClass = 'ok';
            $statusText = 'Стабільно';
            $isAlert = ($losGroupCritical >= 2) || ($losGroupRecent >= 3 && $losRecent >= 8) || ($offlinePct >= 60 && $losRecent >= 5);
            $isWarn = (!$isAlert) && (($losGroupRecent >= 1 && $losRecent >= 2) || ($losRecent >= 3) || ($offlinePct >= 30 && $losRecent >= 1));
            if ($isAlert) {
                $statusClass = 'alert';
                $statusText = 'Аварія LOS';
            } elseif ($isWarn) {
                $statusClass = 'warn';
                $statusText = 'Потрібна увага';
            }
            $degTree = $degradeByTree[$treeId] ?? ['total' => 0, 'degraded' => 0, 'is_degraded' => 0];
            $losReasonsRecent = $err6Recent + $err8Recent;
            if ($losRecent === 0 && $losGroupRecent === 0 && $losGroupCritical === 0) {
                $metaLosText = '';
            } else {
                $metaLosText = 'За 12 год: ONU з LOS — ' . $losRecent . ', групові відключення — ' . $losGroupRecent . ', критичні порти — ' . $losGroupCritical;
                if ($losReasonsRecent > 0) {
                    $metaLosText .= ' (події LOS: ' . $losReasonsRecent . ')';
                }
            }
            $metaDegText = ((int)$degTree['degraded'] > 0)
                ? ('Деградація RX за 24 год: ONU — ' . (int)$degTree['degraded'] . (!empty($degTree['is_degraded']) ? ' (ризик по гілці)' : ''))
                : '';
            $list_pon = '';
            if (!empty($portsByTree[$treeId])) {
                foreach ($portsByTree[$treeId] as $p) {
                    $portName = $p['port_name'] ?? '';
                    if ($portName === '' || $portName === null) {
                        $portName = 'OLT ' . (int)$p['olt'] . ' / P' . (int)$p['portolt'];
                    }
                    $list_pon .= '<span><img src="../style/img/port.png">' . $portName . '</span>';
                }
            }
            $list_pon_port = $list_pon ? '<div class="min_desc_gpon">' . $list_pon . '</div>' : '';
            $portErrorToday = (int)($tree['port_error_today'] ?? 0);
            $portErrorDevices = trim((string)($tree['port_error_devices'] ?? ''));
            $portErrorText = '';
            if (!empty($confPMon['FIBERMAP_ERROR']) && (int)$confPMon['FIBERMAP_ERROR'] === 1 && $portErrorToday > 0) {
                $portErrorText = '<div class="fiber-tree-meta" style="color:red;font-weight:700;">Помилок за сьогодні +' . $portErrorToday . '</div>';
                if ($portErrorDevices !== '') {
                    $portErrorText .= '<div class="fiber-tree-meta" style="color:red;">' . $portErrorDevices . '</div>';
                }
            }
            $count_ponelement = $countByTree[$treeId] ?? 0;
            $resutltpl .= '
                <tr>
                    <td data-label="Тип">' . typesMereja($merejaSafe) . '</td>
                    <td data-label="Назва" class="name_pon' . ($statusClass === 'alert' ? ' color_red' : '') . '">
                        <a href="/?do=fiber&act=viewtree&id=' . $treeId . '">' . $nameSafe . '</a>
                        <span class="fiber-tree-badge ' . $statusClass . '">' . $statusText . '</span>
                        ' . ($metaLosText !== '' ? '<div class="fiber-tree-meta">' . $metaLosText . '</div>' : '') . '
                        ' . ($metaDegText !== '' ? '<div class="fiber-tree-meta" style="color:#9a3412;font-weight:600;">' . $metaDegText . '</div>' : '') . '
                        ' . $portErrorText . '
                        ' . $list_pon_port . '
                    </td>
                    <td data-label="Елементів">' . ($count_ponelement > 0 ? '<div>' . $count_ponelement . '</div>' : '') . '</td>
                    <td data-label="ONU"><font color="#19a5f9">' . $onu_total   . '</font></td>
                    <td data-label="Online"><font color="#069d0c">' . $onu_online  . '</font></td>
                    <td data-label="Offline"><font color="red">' . $offlineFact . '</font></td>
                    <td data-label="Якість"><div id="ration-' . $treeId . '"></div></td>
                </tr>';

            if (isset($tree['device'])) {
                $vyzol[(int)$tree['device']]['olt'] = (int)$tree['device'];
            }
        }
		}
        $resutltpl .= '</tbody></table></div>';
    }
    $sql_list_network = "SELECT id, name, mereja FROM pontree WHERE unit_id = '" . (int)$sql_unit['id'] . "' AND mereja != 'pon'";
	$sql_list_nt = $pdo->prepare($sql_list_network);
    $sql_list_nt->execute();
	$list_element = $sql_list_nt->fetchAll(PDO::FETCH_ASSOC); 
	if (!empty($list_element)) {
        $otherIds = array_map(static fn($r) => (int)$r['id'], $list_element);
        $ph2 = implode(',', array_fill(0, count($otherIds), '?'));
        $sqlCountsOther = "SELECT tree AS pontree, COUNT(*) AS count_id FROM ponelement WHERE tree IN ($ph2) GROUP BY tree";
        $stmt = $pdo->prepare($sqlCountsOther);
        $stmt->execute($otherIds);
        $cntOtherRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cntOther = [];
        foreach ($cntOtherRows as $r) {
            $cntOther[(int)$r['pontree']] = (int)$r['count_id'];
        }
        $resutltpl .= '<div class="fiber-unit-table-wrap"><table class="resp-tab list-onu-olt"><thead><tr><th width="10%">Types</th><th>' . $lang['name'] . '</th><th>' . $lang['count'] . '</th></tr></thead><tbody>';
        foreach ($list_element as $ftth) {
            $ftthId = (int)$ftth['id'];
            $mereja = $ftth['mereja'];
            $name = $ftth['name'];
            $cnt = $cntOther[$ftthId] ?? 0;
            $resutltpl .= '
                <tr>
                    <td data-label="Тип">' . typesMereja($mereja) . '</td>
                    <td data-label="Назва" class="name_pon">
                        <a href="/?do=fiber&act=viewtree&id=' . $ftthId . '">' . $name . '</a>
                    </td>
                    <td data-label="Елементів">' . ($cnt > 0 ? '<div>' . $cnt . '</div>' : 'n/a') . '</td>
                </tr>';
        }

        $resutltpl .= '</tbody></table></div>';
    }
    $resutltpl .= '
    <script>
		getRatioTree();
	</script>
    </div>
		<div class="device">'.get_fevice_fiber_map($vyzol ?? [], $db).'</div>
	</div>';
} else {
    $go->go('/?do=fiber&act=unit');
}
?>
