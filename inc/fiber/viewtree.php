<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
if(!$id){
	$go->redirect('fiber');
}
$stmt = $pdo->prepare("SELECT * FROM pontree WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$pontree = $stmt->fetch(PDO::FETCH_ASSOC);
if (empty($pontree['id'])) {
    $go->redirect('fiber');
}
$conditions = [];
$params = [];
if (isset($types)) {
    $conditions[] = "types = :types";
    $params[':types'] = $types;
}
if (!empty($pontree['id'])) {
    $conditions[] = "tree = :tree";
    $params[':tree'] = $pontree['id'];
}
$whereSql = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
$sql = "SELECT id, name, tree, types, description, lan, status, myicon, perevirka FROM ponelement $whereSql ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sqlponelement = $stmt->fetchAll(PDO::FETCH_ASSOC);
$used = false;
$stmt = $pdo->prepare("SELECT * FROM ponunit WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $pontree['unit_id']]);
$unit = $stmt->fetch(PDO::FETCH_ASSOC);
if(isset($sqlponelement) && count($sqlponelement)>0){
	$used = true;
}
$metatags = array('title'=>$lang['vols_viewtree'].' '.$unit['name'],'description'=>$lang['viewtree'],'page'=>'viewtree');
$bar_tpl .= generateAutoLoadScript();
$bar_tpl .= '<div class="nav-bar">';
$bar_tpl .= '<a href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$bar_tpl .= '<a href="/?do=fiber&act=unit"><i class="fi fi-rr-angle-left"></i>'.$lang['volsmeraja'].'</a>';
$bar_tpl .= '<a href="/?do=fiber&act=viewunit&id='.$unit['id'].'"><i class="fi fi-rr-angle-left"></i>'.$unit['name'].'</a>';
$bar_tpl .= '<span class="active"><i class="fi fi-rr-angle-left"></i>'.$pontree['name'].'</span>';
$bar_tpl .= '</div>';
//// MENU PON TREE
$resutltpl .= '
<div class="pon-sfp-detail">
    <div class="dashboard_pon tag-filter">';
	$resutltpl .= '
    <a class="snmp_pon_vz" href="/?do=fiber&act=add&id=' . $id . '">' . $lang['add'] . '</a>';

if ($used) {
    if (isset($types)) {
        $resutltpl .= '<a class="reboot_pon" href="/?do=fiber&act=viewtree&id=' . $id . '">' . $lang['fiber_show_all'] . '</a>';
    }
    $resutltpl .= '
        <a class="snmp_pon_rx" href="/?do=fiber&act=map&unit=' . $pontree['unit_id'] . '">
            ' . $lang['map'] . ' ' . $unit['name'] . '
        </a>
        '; // <a class="snmp_pon_vz" href="/?do=fiber&act=map&unit=' . $pontree['unit_id'] . '&tree=' . $id . '">Переглянути карту ' . htmlspecialchars($pontree['name']) . '</a>
}
if (!$used && $access->get('edit_ponbox')) {
    $resutltpl .= '
        <a class="snmp_pon_vz" href="/?do=fiber&act=del&id=' . $id . '">' . $lang['delet'] . '</a>';
}
if ($used) {
    $resutltpl .= '
        <div id="dataload">
            <a class="snmp_pon_fc" href="#" onclick="collectDataFiber(' . $id . ')">' . $lang['snmp_check_pon'] . ' ' . $pontree['name'] . '</a>
        </div>
        <a class="snmp_pon mlcp" onclick="diagnostic(' . $id . ')">' . $lang['fiber_pon_test'] . '</a>';
}
$resutltpl .= '
    </div>
</div>';

$resutltpl .= '<div id="diagnostic"></div>';
if($used){
	$elementIds = array_map(static function ($r) {
		return (int)$r['id'];
	}, $sqlponelement);
	$connectionsByElement = connFelementBulk($pdo, $elementIds);
	$degradeByElement = [];
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
	if (!empty($elementIds)) {
		[$pePh1, $peParams1] = $makePh($elementIds, 'pe1_');
		[$pePh2, $peParams2] = $makePh($elementIds, 'pe2_');
		$sqlElementOnu = "
			SELECT od.ponelement, o.idonu
			FROM onusdata od
			INNER JOIN onus o ON o.mac = od.onukey
			WHERE od.ponelement IN (" . implode(',', $pePh1) . ")
			UNION
			SELECT od.ponelement, o.idonu
			FROM onusdata od
			INNER JOIN onus o ON o.sn = od.onukey
			WHERE od.ponelement IN (" . implode(',', $pePh2) . ")
		";
		$stmt = $pdo->prepare($sqlElementOnu);
		$stmt->execute($peParams1 + $peParams2);
		$mapRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$onuByElement = [];
		$allOnuIds = [];
		foreach ($mapRows as $m) {
			$peId = (int)($m['ponelement'] ?? 0);
			$onuId = (int)($m['idonu'] ?? 0);
			if ($peId <= 0 || $onuId <= 0) {
				continue;
			}
			$onuByElement[$peId][$onuId] = true;
			$allOnuIds[$onuId] = true;
		}
		$onuIds = array_keys($allOnuIds);
		$histByOnu = [];
		if (!empty($onuIds)) {
			$onuIds = array_map('intval', $onuIds);
			$placeholders = implode(',', array_fill(0, count($onuIds), '?'));
			$sqlHistory = "
				SELECT `onu`, `signal`, `datetime`
				FROM `historysignal`
				WHERE `onu` IN ($placeholders)
				  AND `datetime` >= (NOW() - INTERVAL 24 HOUR)
				ORDER BY `onu` ASC, `datetime` ASC
			";
			$stmt = $pdo->prepare($sqlHistory);
			$stmt->execute($onuIds);
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
		foreach ($onuByElement as $peId => $onuMap) {
			$total = count($onuMap);
			$degr = 0;
			foreach ($onuMap as $onuId => $v) {
				if (isset($degradeOnu[$onuId])) {
					$degr++;
				}
			}
			$degradeByElement[$peId] = [
				'total' => $total,
				'degraded' => $degr,
				'is_degraded' => (($total >= 5 && $degr >= 3 && ($degr / max(1, $total)) >= 0.30) || $degr >= 5) ? 1 : 0
			];
		}
	}
	$countElements = count($sqlponelement);
	$countOnline = 0;
	$countOffline = 0;
	$countUnknown = 0;
	$countLinks = 0;
	foreach ($sqlponelement as $rowStat) {
		$st = (int)($rowStat['status'] ?? 0);
		if ($st === 1) {
			$countOnline++;
		} elseif ($st === 2) {
			$countOffline++;
		} else {
			$countUnknown++;
		}
		$rowIdStat = (int)$rowStat['id'];
		if (!empty($connectionsByElement[$rowIdStat])) {
			$countLinks += count($connectionsByElement[$rowIdStat]);
		}
	}
	$resutltpl .= '
	<style>
	.fiber-tree-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:8px 0 10px}
	.fiber-tree-kpi{display:flex;gap:6px;flex-wrap:wrap}
	.fiber-tree-kpi .kpi{font-size:11px;padding:4px 8px;border-radius:6px;border:1px solid #dbe5f2;background:#fff;color:#0f172a}
	.fiber-tree-kpi .ok{background:#ecfdf3;color:#166534;border-color:#bbf7d0}
	.fiber-tree-kpi .off{background:#fff1f2;color:#b91c1c;border-color:#fecdd3}
	.fiber-tree-kpi .unk{background:#f8fafc;color:#475569;border-color:#e2e8f0}
	.fiber-tree-search{height:30px;min-width:240px;border:1px solid #dbe5f2;border-radius:7px;padding:0 9px;font-size:12px}
	.fiber-degrade{display:inline-flex;align-items:center;font-size:10px;font-weight:700;border-radius:999px;padding:1px 6px;margin-left:6px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;vertical-align:middle}
	.fiber-ok{display:inline-flex;align-items:center;font-size:10px;font-weight:700;border-radius:999px;padding:1px 8px;background:#ecfdf3;border:1px solid #bbf7d0;color:#166534;vertical-align:middle}
	.fiber-tree-table-wrap{width:100%;overflow:auto;-webkit-overflow-scrolling:touch}
	@media (max-width: 900px){
		.dashboard_pon.tag-filter{display:grid;grid-template-columns:1fr 1fr;gap:6px}
		.dashboard_pon.tag-filter a,.dashboard_pon.tag-filter #dataload a{font-size:12px;padding:8px 10px;line-height:1.2;text-align:center}
		.fiber-tree-tools{display:block}
		.fiber-tree-kpi{margin-bottom:8px}
		.fiber-tree-search{min-width:100%;width:100%}
	}
	@media (max-width: 680px){
		#ponbox_list{border-collapse:separate;border-spacing:0 8px}
		#ponbox_list thead{display:none}
		#ponbox_list tbody tr{display:block;border:1px solid #dbe5f2;border-radius:10px;background:#fff;padding:6px}
		#ponbox_list tbody td{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;width:100%!important;border:none;padding:6px 4px}
		#ponbox_list tbody td::before{content:attr(data-label);font-size:11px;color:#64748b;min-width:96px;max-width:96px}
		#ponbox_list tbody td:first-child::before{content:"Статус"}
		#ponbox_list tbody td:first-child center{width:100%;text-align:left}
		#ponbox_list .npon{display:block}
		#ponbox_list .npon::before{content:none}
		#ponbox_list .npon a b{line-height:1.3}
		#ponbox_list .ponbox_info::before{content:"Керування"}
		#ponbox_list .list-fiber-conn a{display:flex;align-items:center;gap:6px}
		#ponbox_list .fiberpanel{margin-top:6px}
		#ponbox_list .fiberpanel a{display:inline-flex;padding:5px 8px;border-radius:6px}
	}
	</style>
	<div class="fiber-tree-tools">
		<div class="fiber-tree-kpi">
			<span class="kpi">Елементів: '.$countElements.'</span>
			<span class="kpi ok">Online: '.$countOnline.'</span>
			<span class="kpi off">Offline: '.$countOffline.'</span>
			<span class="kpi unk">Невідомо: '.$countUnknown.'</span>
			<span class="kpi">З\'єднань: '.$countLinks.'</span>
		</div>
		<input type="text" id="fiberTreeSearch" class="fiber-tree-search" placeholder="Пошук елемента по назві...">
	</div>';
	$resutltpl .= '<div class="fiber-tree-table-wrap"><table id="ponbox_list" class="resp-tab"><thead><tr>
	<th width="40px"></th>
	<th>'.$lang['fiber-name'].'</th>
	<th>'.$lang['btn_menu_device'].'</th>
	<th>'.$lang['connecti'].'</th>
	<th>RX деградація</th>
	<th>'.$lang['fiber_average_rx'].'</th>
	</tr></thead><tbody>';
	foreach($sqlponelement as $row){
		$element = $connectionsByElement[(int)$row["id"]] ?? [];
		$rowNameSafe = htmlspecialchars((string)$row["name"], ENT_QUOTES, 'UTF-8');
		$rowNameSearch = function_exists('mb_strtolower') ? mb_strtolower($rowNameSafe, 'UTF-8') : strtolower($rowNameSafe);
		$degrData = $degradeByElement[(int)$row["id"]] ?? ['total' => 0, 'degraded' => 0, 'is_degraded' => 0];
		$degrBadge = '';
		if (!empty($degrData['is_degraded'])) {
			$degrBadge = '<span class="fiber-degrade" title="На елементі фіксується спад RX у ' . (int)$degrData['degraded'] . ' з ' . (int)$degrData['total'] . ' ONU за 24г">RX↓ деградація</span>';
		}
		if($row["status"]==1){
			$status = 'i-map-on';
		}elseif($row["status"]==2){
			$status = 'i-map-off';
		}else{
			$status = 'i-map-on';
		}
		if(!empty($row["myicon"])){
			$imgElement = '<img src="../style/ponmap/'.$row["myicon"].'">';
		}else{
			$imgElement = img_types_element($row["types"]);
		}
		$resutltpl .= "<tr data-name=\"".$rowNameSearch."\"><td data-label=\"Статус\" class=\"i-map {$status}\"><center><a class=img_ponbox href=\"/?do=fiber&act=viewtree&id=".$id."&types=".$row["types"]."\">".$imgElement."</a>";
		if (strpos($row["perevirka"], '1970') === false){
			$resutltpl .= '<span class="repevirka">'.aftertime($row["perevirka"]).'</span>';
		}else{
			$resutltpl .= '--/--';
		}
		$resutltpl .= "</center></td>";
		$resutltpl .= "<td data-label=\"Елемент\" class=\"npon\"><a href=\"/?do=fiber&act=details&id=" . $row["id"]. "\"><b>" . $row["name"]. "<img class=\"link_fiber\" src=\"../style/img/link.png\"></b></a>".$degrBadge;
		$resutltpl .= (isset($row["description"])?$row["description"]:'')."";
		$resutltpl .="<div class=\"fiberpanel\">";
		$resutltpl .= '<a class="edit_ponbox blue" href="/?do=fiber&act=view&id=' . $row["id"]. '">'.$lang['connects'].'</a>';
		$resutltpl .= "</div></td>";		
		$resutltpl .= "<td data-label=\"Керування\" class=\"ponbox_info\">".(!empty($row["lan"])?"":"<a class=\"addgeo\" href='/?do=fiber&act=mapper&id=".$row["id"]."'><img src=\"../style/img/gps.png\"></a>")."";
		$resutltpl .= "<div class=\"addelement_". $row["id"]."\">".
		"<div id=\"res_". $row["id"]."\" data-id=\"". $row["id"]."\" data-tree=\"". $id."\"></div>".
		#"<span class=\"addelement\" onclick=\"addelement(". $row["id"].",". $row["types"].",".$id.")\">[додати]</span></div>";
		"</div>";
		$resutltpl .= "</td>";	
		$resutltpl .= "<td data-label=\"З'єднання\">";
		if(isset($element) && count($element)>0){
			$resutltpl .= "<div class=\"list-fiber-conn\">";
			foreach($element as $conn){
				$resutltpl .= "
					<a href=\"/?do=fiber&act=view&id=".$conn['id']."\"><img src=\"../style/img/conn2.png\"><span>".$conn['name']."</span><span class=\"fiber_dist\">(".calculateLineDistance($conn['geo'])."".$lang['metric'].")</span></a>
					".(empty($conn['del']) ? "<a href=\"?do=fiber&act=delconnkabel&p=".$id."&id=".$conn['fiberid']."&t=view\"><img class=\"del_fiber\" src=\"../style/img/close.png\"></a>":"")."
				";
			}
			$resutltpl .= "</div>";
		}
		if (isset($confPMon['OBL_ENERGO']) && !empty($confPMon['OBL_ENERGO']) && $confPMon['OBL_ENERGO'] == 1) {
			$listpillar = connPillar($pdo,$row["id"]);
			if(!empty($listpillar)){			
				#$resutltpl .= $listpillar;
			}
		}
		#$resutltpl .= '<a href="/?do=oblenergo&act=selectcase&id='.$row["id"].'" class="addpillar"><img src="../style/img/add_connect_line.png">Привязати опору</a>';
		$resutltpl .= "</td>";
		$degrCell = '<span class="fiber-ok">OK</span>';
		if (!empty($degrData['is_degraded'])) {
			$degrCell = '<span class="fiber-degrade" style="margin-left:0;" title="На елементі фіксується спад RX у ' . (int)$degrData['degraded'] . ' з ' . (int)$degrData['total'] . ' ONU за 24г">ризик: ' . (int)$degrData['degraded'] . '/' . (int)$degrData['total'] . '</span>';
		} elseif ((int)$degrData['degraded'] > 0) {
			$degrCell = '<span style="color:#92400e;font-size:11px;">тренд: ' . (int)$degrData['degraded'] . '/' . (int)$degrData['total'] . '</span>';
		}
		$resutltpl .= "<td data-label=\"RX деградація\">" . $degrCell . "</td>";
		// Rx Signal
		$resutltpl .= "<td data-label=\"Середній RX\"><div id=\"signal_". $row["id"]."\" data-xd=\"". $row["id"]."\" data-xtree=\"". $id."\"></div></td>";
		$resutltpl .= "</tr>";
	}
	$resutltpl .= '</tbody></table></div>';
	$resutltpl .= '<script>
	(function(){
		var input = document.getElementById("fiberTreeSearch");
		var table = document.getElementById("ponbox_list");
		if(!input || !table){ return; }
		var body = table.querySelector("tbody");
		if(!body){ return; }
		function norm(v){ return String(v || "").toLowerCase().trim(); }
		function applyFilter(){
			var q = norm(input.value);
			var rows = body.querySelectorAll("tr");
			rows.forEach(function(row){
				var n = norm(row.getAttribute("data-name"));
				row.style.display = (q === "" || n.indexOf(q) !== -1) ? "" : "none";
			});
		}
		input.addEventListener("input", applyFilter);
	})();
	</script>';
}
?>
