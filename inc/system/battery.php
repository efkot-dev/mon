<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$speedbar = '';
$result = '';
$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
$d = (isset($_GET['d']) ? Clean::int($_GET['d']) : null);
if (!$confPMon['BATTERY'] && empty($confPMon['BATTERY'])) {
	$go->redirect('main');
}
$photo = '';
switch($act){
case 'viewbattery':
    $id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
    if (!$id) { $go->redirect('battery'); break; }
    $stmt = $pdo->prepare("SELECT * FROM battery WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $getBattery = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$getBattery) { $go->redirect('battery'); break; }
    $speedbar .= '<a class="brmhref" href="/?do=battery"><i class="fi fi-rr-car-battery"></i>'.$lang['sklad_battery'].'</a>'
               . '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['battery'].' '.htmlspecialchars($getBattery['name']).'</span>';
    $result   .= '<div class="viewbattery"></div>';
    $photo = '';
    if (!empty($getBattery['photo'])) {
        $photo = '<div class="battery_img"><img src="/?do=thumb&type=photo&img=' . htmlspecialchars($getBattery['photo']) . '&s=1"></div>';
    }
    $stmt = $pdo->prepare("SELECT * FROM battery_used WHERE batteryid = :id ORDER BY added DESC, id DESC");
    $stmt->execute([':id' => $id]);
    $battery_used = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT * FROM battery_unit WHERE batteryid = :id ORDER BY added DESC, id DESC");
    $stmt->execute([':id' => $id]);
    $battery_unit = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (is_array($battery_used)) {
        foreach ($battery_used as $k => $row) {
            $battery_used[$k]['src'] = 'used';
        }
    }
    if (is_array($battery_unit)) {
        foreach ($battery_unit as $unitRow) {
            $battery_used[] = [
                'id' => (int)$unitRow['id'],
                'batteryid' => (int)$unitRow['batteryid'],
                'deviceid' => (int)$unitRow['unitid'],
                'connectd' => 'unit',
                'added' => $unitRow['added'],
                'src' => 'unit',
            ];
        }
    }
    usort($battery_used, static function ($a, $b) {
        $ta = strtotime((string)($a['added'] ?? '')) ?: 0;
        $tb = strtotime((string)($b['added'] ?? '')) ?: 0;
        if ($ta === $tb) {
            return ((int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0));
        }
        return ($tb <=> $ta);
    });
    $ping3Ids = [];
    $switchIds = [];
    $unitIds = [];
    foreach ($battery_used as $bu) {
        if ($bu['connectd'] === 'ping3') $ping3Ids[]  = (int)$bu['deviceid'];
        if ($bu['connectd'] === 'olt') $switchIds[] = (int)$bu['deviceid'];
        if ($bu['connectd'] === 'unit') $unitIds[] = (int)$bu['deviceid'];
    }
    $q = $pdo->prepare("SELECT DISTINCT deviceid FROM battery_history WHERE batteryid = ? AND connectd = 'ping3' AND deviceid > 0");
    $q->execute([(int)$id]);
    while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
        $ping3Ids[] = (int)$r['deviceid'];
    }
    $ping3Ids = array_values(array_unique(array_filter($ping3Ids)));
    $switchIds = array_values(array_unique(array_filter($switchIds)));
    $unitIds = array_values(array_unique(array_filter($unitIds)));
    $ping3Map  = [];
    $switchMap = [];
    $unitMap = [];
    if ($ping3Ids) {
        $in  = implode(',', array_fill(0, count($ping3Ids), '?'));
        $q   = $pdo->prepare("SELECT id, name, netip FROM mon_ping3 WHERE id IN ($in)");
        $q->execute($ping3Ids);
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $ping3Map[(int)$r['id']] = ['name'=>$r['name'], 'netip'=>$r['netip']];
        }
    }
    if ($switchIds) {
        $in  = implode(',', array_fill(0, count($switchIds), '?'));
        $q   = $pdo->prepare("SELECT id, place, netip FROM switch WHERE id IN ($in)");
        $q->execute($switchIds);
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $switchMap[(int)$r['id']] = ['place'=>$r['place'], 'netip'=>$r['netip']];
        }
    }
    if ($unitIds) {
        $in  = implode(',', array_fill(0, count($unitIds), '?'));
        $q   = $pdo->prepare("SELECT id, name FROM ponunit WHERE id IN ($in)");
        $q->execute($unitIds);
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $unitMap[(int)$r['id']] = ['name'=>$r['name']];
        }
    }
    $since = date('Y-m-d H:i:s', strtotime('-7 days'));
    $voltageSeries = [];
    if ($ping3Ids) {
        $in  = implode(',', array_fill(0, count($ping3Ids), '?'));
        $sql = "SELECT v.deviceid, v.added, v.volt, p.name
                  FROM mon_voltage v
                  JOIN mon_ping3 p ON p.id = v.deviceid
                 WHERE v.mon_types = 'ping3' AND v.deviceid IN ($in) AND v.added >= ?
                 ORDER BY v.added ASC
                 LIMIT 15000";
        $bind = $ping3Ids; $bind[] = $since;
        $q = $pdo->prepare($sql);
        $q->execute($bind);
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $name = $row['name'] ?: ('Device#'.$row['deviceid']);
            $voltageSeries[$name][] = ['t' => $row['added'], 'v' => (float)$row['volt']];
        }
    }
    $chargeCount = 0;
    $dischCount = 0;
    if ($ping3Ids) {
        $in = implode(',', array_fill(0, count($ping3Ids), '?'));
        $q3 = $pdo->prepare("SELECT type, COUNT(*) AS cnt FROM battery_sessions WHERE status = 'finished' AND connectd = 'ping3' AND deviceid IN ($in) GROUP BY type");
        $q3->execute($ping3Ids);
        while ($row = $q3->fetch(PDO::FETCH_ASSOC)) {
            if ($row['type'] === 'charge') {
                $chargeCount = (int)$row['cnt'];
            } elseif ($row['type'] === 'discharge') {
                $dischCount = (int)$row['cnt'];
            }
        }
    }
    $jsVoltageSeries = [];
    foreach ($voltageSeries as $devName => $points) {
        $jsVoltageSeries[] = [
            'label' => $devName,
            'data' => array_map(function($p){ return ['x'=>$p['t'],'y'=>$p['v']]; }, $points),
        ];
    }
    $fullCycleCount = min($chargeCount, $dischCount);
    $maxCount = max(1, $chargeCount, $dischCount);
    $chargePct = (int)round($chargeCount / $maxCount * 100);
    $dischPct = (int)round($dischCount  / $maxCount * 100);
    $currentPlaceLabel = '<span class="off_">'.$lang['free'].'</span>';
    if (!empty($battery_used[0])) {
        $b0 = $battery_used[0];
        if ($b0['connectd'] === 'ping3') {
            $p = $ping3Map[(int)$b0['deviceid']] ?? null;
            $currentPlaceLabel = '<span class="signal3">PING3</span><br>'.htmlspecialchars((string)($p['name'] ?? ('#'.$b0['deviceid'])));
        } elseif ($b0['connectd'] === 'olt') {
            $s = $switchMap[(int)$b0['deviceid']] ?? null;
            $currentPlaceLabel = '<span class="signal3">OLT</span><br>'.htmlspecialchars((string)($s['place'] ?? ('#'.$b0['deviceid'])));
        } elseif ($b0['connectd'] === 'unit') {
            $u = $unitMap[(int)$b0['deviceid']] ?? null;
            $currentPlaceLabel = '<span class="signal3">UNIT</span><br>'.htmlspecialchars((string)($u['name'] ?? ('#'.$b0['deviceid'])));
        } else {
            $currentPlaceLabel = '<span class="signal3">'.htmlspecialchars((string)$b0['connectd']).'</span><br>#'.(int)$b0['deviceid'];
        }
    }
    $battery_info  = '<div class="block_white battery_info">'.$photo;
    $battery_info .= '<div class="b_pole"><span>'.$lang['name'].'</span><h2>'.htmlspecialchars($getBattery['name']).'</h2></div>';
    $battery_info .= '<div class="b_pole"><span>'.$lang['model'].'</span><h2>'.htmlspecialchars($getBattery['model']).'</h2></div>';
    $battery_info .= '<div class="b_pole"><span>'.$lang['addbattery_1'].'</span><h2>'.htmlspecialchars((string)$getBattery['amper']).'Ah</h2></div>';
    $battery_info .= '<div class="b_pole"><span>'.$lang['v'].'</span><h2>'.htmlspecialchars((string)$getBattery['voltage']).'V</h2></div>';
    $battery_info .= '<div class="b_pole"><span>Current Place</span><h2>'.$currentPlaceLabel.'</h2></div>';
    if (!empty($getBattery['sn'])) {
        $battery_info .= '<div class="b_pole"><span>SN</span><h2>'.htmlspecialchars((string)$getBattery['sn']).'</h2></div>';
    }
	$battery_info .= '<table class="cycles-table">
            <tr>
              <td class="bar-label">Charge</td>
              <td width="40%">
                <div class="bar-wrap" aria-label="charge cycles '.(int)$chargeCount.'">
                  <div class="bar green" style="width: '.(int)$chargePct.'%;"></div>
                </div>
              </td>
              <td class="bar-val">'.(int)$chargeCount.'</td>
            </tr>
            <tr>
              <td class="bar-label">Discharge</td>
              <td width="40%">
                <div class="bar-wrap" aria-label="discharge cycles '.(int)$dischCount.'">
                  <div class="bar red" style="width: '.(int)$dischPct.'%;"></div>
                </div>
              </td>
              <td class="bar-val">'.(int)$dischCount.'</td>
            </tr>
            <tr>
              <td class="bar-label">Full Cycles</td>
              <td colspan="2" class="bar-val">'.(int)$fullCycleCount.'</td>
            </tr>
          </table>';
    $battery_info .= '</div>';
    $battery_connect_list  = '<table class="resp-tab module_battery stikers"><thead><tr>';
    $battery_connect_list .= '<th width="15%">'.$lang['doohickey'].'</th><th width="10%">IP-Address</th><th>'.$lang['btn_olt_allinfo'].'</th><th width="10%">'.$lang['time_connect'].'</th><th width="10%">'.$lang['functions'].'</th>';
    $battery_connect_list .= '</tr></thead><tbody>';
    if ($battery_used) {
        foreach ($battery_used as $b) {
            $device = $device_info = $ip = 'N/A';
            $link = '/';
            if ($b['connectd']=='ping3') {
                $devRow = $ping3Map[(int)$b['deviceid']] ?? null;
                $device = 'PING3';
                $device_info = $devRow['name'] ?? '';
                $ip = $devRow['netip'] ?? 'N/A';
                $link = '/?do=ping3&act=view&id='.(int)$b['deviceid'];
            } elseif ($b['connectd']=='pmon') {
                $device = 'PMon Sensor';
                $device_info = 'PMon Sensor';
                $ip = 'N/A';
            } elseif ($b['connectd']=='unit') {
                $u = $unitMap[(int)$b['deviceid']] ?? null;
                $device = 'UNIT';
                $device_info = $u['name'] ?? '';
                $ip = 'N/A';
            } elseif ($b['connectd']=='olt') {
                $devRow = $switchMap[(int)$b['deviceid']] ?? null;
                $device = 'OLT';
                $device_info = $devRow['place'] ?? '';
                $ip = $devRow['netip'] ?? 'N/A';
                $link = '/?do=detail&act=olt&id='.(int)$b['deviceid'];
            }
            $disconnectUrl = '/?do=battery&act=disconnect&id='.(int)$b['batteryid'].'&d='.(int)$b['deviceid'].'&rid='.(int)$b['id'].'&src='.(($b['src'] ?? 'used') === 'unit' ? 'unit' : 'used');
            $deviceInfoCell = htmlspecialchars((string)$device_info);
            if($link !== '/'){
                $deviceInfoCell = '<a href="'.$link.'" class="name-onu">'.$deviceInfoCell.'<img class="link_fiber" src="../style/img/link.png"></a>';
            }else{
                $deviceInfoCell = '<span class="name-onu">'.$deviceInfoCell.'</span>';
            }
            $battery_connect_list .= '<tr>'
               . '<td><span class="signal3">'.$device.'</span></td>'
               . '<td>'.htmlspecialchars((string)$ip).'</td>'
               . '<td class="description_name mobile_font akb">'.$deviceInfoCell.'</td>'
               . '<td><span class="on_">'.htmlspecialchars((string)$b['added']).'</span></td>'
               . '<td><a class="panel_house rr_1" href="'.$disconnectUrl.'">'.(!empty($lang['delete']) ? $lang['delete'] : 'Delete').'</a></td>'
               . '</tr>';
        }
    } else {
        $battery_connect_list .= '<tr><td colspan="5">'.$lang['empty'].'</td></tr>';
    }
    $battery_connect_list .= '</tbody></table>';
    $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 20;
    $offset   = ($page - 1) * $per_page;
    $journal_html = '<div>';
    #$journal_html .= '<h3 style="margin:10px 0;">Charge / discharge journal</h3>';
    if ($ping3Ids) {
        $in  = implode(',', array_fill(0, count($ping3Ids), '?'));
        $qCnt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM battery_sessions WHERE connectd='ping3' AND deviceid IN ($in)");
        $qCnt->execute($ping3Ids);
        $total = (int)$qCnt->fetchColumn();
        $qJ = $pdo->prepare("
            SELECT s.id, s.type, s.status, s.time_start, s.time_end, s.voltage_start, s.voltage_end, s.duration, p.name AS device_name FROM battery_sessions s JOIN mon_ping3 p ON p.id = s.deviceid WHERE s.connectd='ping3' AND s.deviceid IN ($in) ORDER BY s.time_start DESC LIMIT $per_page OFFSET $offset
        ");
        $qJ->execute($ping3Ids);
        $rows = $qJ->fetchAll(PDO::FETCH_ASSOC);
        $journal_html .= '<table class="resp-tab module_battery stikers"><thead><tr>'
                       . '<th>Device</th><th>Type</th><th>Status</th><th>Start</th><th>End</th><th>Start V</th><th>End V</th><th>Duration</th>'
                       . '</tr></thead><tbody>';
        if ($rows) {
            foreach ($rows as $r) {
                $tDur = $r['duration'] ? gmdate('H:i:s', (int)$r['duration']) : '-';
                $journal_html .= '<tr>'
                . '<td>'.htmlspecialchars((string)$r['device_name']).'</td>'
                . '<td><span class="'.($r['type']=='charge'?'signal3':'signal1').'">'.$r['type'].'</span></td>'
                . '<td>'.htmlspecialchars((string)$r['status']).'</td>'
                . '<td>'.htmlspecialchars((string)$r['time_start']).'</td>'
                . '<td>'.htmlspecialchars((string)($r['time_end'] ? $r['time_end']: '-')).'</td>'
                . '<td>'.(is_null($r['voltage_start']) ? '-':(float)$r['voltage_start']).'</td>'
                . '<td>'.(is_null($r['voltage_end']) ? '-':(float)$r['voltage_end']).'</td>'
                . '<td>'.$tDur.'</td>'
                . '</tr>';
            }
        } else {
            $journal_html .= '<tr><td colspan="8">'.$lang['empty'].'</td></tr>';
        }
        $journal_html .= '</tbody></table>';
        if ($total > $per_page) {
            $last = (int)ceil($total / $per_page);
            $base = '/?do=battery&act=viewbattery&id='.$id;
            $journal_html .= '<div class="pagin" style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap;">';
            if ($page > 1) {
                $journal_html .= '<a class="knopkagreen" href="'.$base.'&page='.($page-1).'">&laquo;</a>';
            }
            $start = max(1, $page - 2);
            $end   = min($last, $page + 2);
            if ($start > 1) $journal_html .= '<a class="knopkagreen" href="'.$base.'&page=1">1</a><span>...</span>';
            for ($i=$start; $i<=$end; $i++) {
                if ($i == $page) {
                    $journal_html .= '<span class="knopkaor" style="pointer-events:none;">'.$i.'</span>';
                } else {
                    $journal_html .= '<a class="knopkagreen" href="'.$base.'&page='.$i.'">'.$i.'</a>';
                }
            }
            if ($end < $last) $journal_html .= '<span>...</span><a class="knopkagreen" href="'.$base.'&page='.$last.'">'.$last.'</a>';
            if ($page < $last) {
                $journal_html .= '<a class="knopkagreen" href="'.$base.'&page='.($page+1).'">&raquo;</a>';
            }
            $journal_html .= '</div>';
        }
    } else {
        $journal_html .= '<div>'.$lang['empty'].'</div>';
    }
    $journal_html .= '</div>';
    $battery_istoriya = $db->SimpleWhile("SELECT * FROM battery_history WHERE batteryid = '{$id}' ORDER BY id DESC LIMIT 300");
    $battery_history = '<table class="resp-tab module_battery stikers "><thead><tr>
        <th>'.$lang['log'].'</th>
        <th width="10%">'.$lang['doohickey'].'</th>
        <th width="25%">'.$lang['device'].'</th>
        <th width="15%">'.$lang['change_time'].'</th></tr></thead><tbody>';
    if(isset($battery_istoriya) && count($battery_istoriya) > 0){
        foreach($battery_istoriya as $idh => $history){
            $hType = strtoupper((string)$history['connectd']);
            $hNode = '#'.(int)$history['deviceid'];
            if($history['connectd'] === 'ping3'){
                $hNode = (isset($ping3Map[(int)$history['deviceid']]['name']) ? $ping3Map[(int)$history['deviceid']]['name'] : $hNode);
            }elseif($history['connectd'] === 'olt'){
                $hNode = (isset($switchMap[(int)$history['deviceid']]['place']) ? $switchMap[(int)$history['deviceid']]['place'] : $hNode);
            }elseif($history['connectd'] === 'unit'){
                $hNode = (isset($unitMap[(int)$history['deviceid']]['name']) ? $unitMap[(int)$history['deviceid']]['name'] : $hNode);
            }elseif((int)$history['deviceid'] === 0){
                $hNode = '-';
            }
            $battery_history .= '
            <tr>
            <td class="description_name mobile_font akb"><span class="name-onu">'.htmlspecialchars((string)$history['history']).'</span></td>
            <td><span class="signal3">'.htmlspecialchars((string)$hType).'</span></td>
            <td>'.htmlspecialchars((string)$hNode).'</td>
            <td>'.htmlspecialchars((string)$history['added']).'</td>
            </tr>
            ';
        }
    }else{
        $battery_history .= '<tr><td colspan="4">'.$lang['empty'].'</td></tr>';
    }
    $battery_history .= '</tbody></table>';
    $battery_pannel = '';
    if ($access->get('monitordevice')) {
        $battery_pannel .= '<div class="battery-panel">';
        $battery_pannel .= '<span class="knopkagreen" onclick="ajaxbattery('.$id.',\'photo\')">'.$lang['added_photo'].'</span>';
        $battery_pannel .= '<span class="knopkagreen" onclick="ajaxbattery('.$id.',\'connect\')">Move/Connect</span>';
        $battery_pannel .= '<span class="knopkaor" onclick="ajaxbattery('.$id.',\'edit\')">'.$lang['edit'].'</span>';
        $stm = $pdo->prepare("SELECT id FROM battery_used WHERE batteryid = :id LIMIT 1");
        $stm->execute([':id' => $id]);
        $stmUnit = $pdo->prepare("SELECT id FROM battery_unit WHERE batteryid = :id LIMIT 1");
        $stmUnit->execute([':id' => $id]);
        if (!$stm->fetch() && !$stmUnit->fetch()) {
            $battery_pannel .= '<a class="knopkared" onclick="return confirmpmon(\''.$lang['install_dump'].'\')" href="/?do=battery&act=delbattery&batteryid='.$id.'">'.$lang['svalka'].'</a>';
        }
        $battery_pannel .= '</div><div id="ajaxbattery"></div>';
    }
    $result .= '
    <div class="polka">
      <div class="block-monitor-olt mr20">'.$battery_info.'</div>
      <div class="block-olt-content full80">
        '.$battery_connect_list.'
        '.$battery_pannel.'
        <div class="block_white">
          <div style="display:flex; gap:16px; align-items:center; margin:6px 0 10px;">
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
              <input type="radio" name="vfilter" value="consecutive" checked> Consecutive changes
            </label>
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
              <input type="radio" name="vfilter" value="unique"> Unique values
            </label>
          </div>
          <div id="d3-voltage" style="width:100%; height:360px; position:relative;"></div>
        </div>

          <style>
            .cycles-table { width:100%; border-collapse:separate; border-spacing:0 8px; }
            .cycles-table td { vertical-align:middle; }
            .bar-wrap { height:14px; background:#eee; border-radius:8px; overflow:hidden; width:100%; }
            .bar { height:100%; }
            .bar.green { background:linear-gradient(90deg,#3ecf5f,#2aa84a); }
            .bar.red   { background:linear-gradient(90deg,#ff6b6b,#e03131); }
            .bar-val { white-space:nowrap; padding-left:10px; font-weight:600; }
            .bar-label {font-weight:600; }
          </style>

        '.$journal_html.'
        '.$battery_history.'
      </div>
    </div>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="../style/js/battery.js"></script>
    <script>
      const voltageSeries = '.json_encode($jsVoltageSeries, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).';
      renderVoltage("consecutive");
      document.querySelectorAll(\'input[name="vfilter"]\').forEach(r=>{
        r.addEventListener("change", e => renderVoltage(e.target.value === "unique" ? "unique" : "consecutive"));
      });
    </script>';
    break;
	case 'viewbattery2':
		$go->go('/?do=battery&act=viewbattery&id='.(int)$id);
		exit;
	break;
	case 'photobattery':
		if(!$access->get('monitordevice')){
			$go->redirect('battery');
			exit;
		}
		$id = (isset($_POST['id']) ? Clean::int($_POST['id']) : null);
		$allowedBatteryTypes = [
			'image/jpeg' => 'jpg',
			'image/jpg'  => 'jpg',
			'image/png'  => 'png',
		];
		$file = true;
		if(isset($id) && $id>0 && !empty($_FILES['file']['name'])) {
			if (!isset($_FILES['file']['type']) || !array_key_exists($_FILES['file']['type'], $allowedBatteryTypes))
				$file = false;
			if (!preg_match('/^(.+)\.(jpg|jpeg|png)$/si', $_FILES['file']['name']))
				$file = false;
			if($file!=false){
				$newname = 'battery_'.substr(md5(uniqid(rand(), true)), 0, rand(7, 13)).'.'.$allowedBatteryTypes[$_FILES['file']['type']];
				$ifile = $_FILES['file']['tmp_name'];
				$copy = (is_uploaded_file($ifile) ? @move_uploaded_file($ifile, $uploaddir.$newname) : @copy($ifile, $uploaddir.$newname));
				if($copy){
					$update['photo'] = $newname;
					$db->SQLupdate('battery',$update,['id'=>$id]);
					$db->SQLinsert('battery_history',[
						'batteryid'=>$id,
						'deviceid'=>0,
						'connectd'=>'photo',
						'history'=>'Photo updated',
						'added'=>date('Y-m-d H:i:s')
					]);
				}
				$go->go('/?do=battery&act=viewbattery&id='.(int)$id);
				exit;
			}
		}
		$go->go('/?do=battery&act=viewbattery&id='.(int)$id);
		exit;
		break;		
	case 'updatebattery': 	
		$sqlinsert = array();
		if(isset($_POST['id']))
			$id = Clean::int($_POST['id']);
		if(isset($_POST['name']))
			$sqlinsert['name'] = Clean::str($_POST['name']);		
		if(isset($_POST['sn']))
			$sqlinsert['sn'] = Clean::text($_POST['sn']);			
		if(isset($_POST['model']))
			$sqlinsert['model'] = Clean::str($_POST['model']);		
		if(isset($_POST['types']))
			$sqlinsert['types'] = Clean::str($_POST['types']);		
		if(isset($_POST['typebattery']))
			$sqlinsert['voltage'] = Clean::int($_POST['typebattery']);		
		if(isset($_POST['amper']))
			$sqlinsert['amper'] = Clean::int($_POST['amper']);
		if(isset($id) && $id>0 && !empty($sqlinsert['name']) && !empty($sqlinsert['model']) && !empty($sqlinsert['types']) && !empty($sqlinsert['voltage'])){
			$db->SQLupdate('battery',$sqlinsert,['id'=>$id]);
			$db->SQLinsert('battery_history',[
				'batteryid'=>$id,
				'deviceid'=>0,
				'connectd'=>'battery',
				'history'=>'Battery profile updated',
				'added'=>date('Y-m-d H:i:s')
			]);
		}
		$go->go('/?do=battery&act=viewbattery&id='.(int)$id);
		die;
	break;		
	case 'delbattery': 
		if($access->get('monitordevice')){
			$sqlinsert['batteryid'] = (isset($_GET['batteryid']) ? Clean::int($_GET['batteryid']) : null);
			if(!empty($sqlinsert['batteryid'])){
				$db->SQLdelete('battery',['id' => $sqlinsert['batteryid']]);
				$db->SQLdelete('battery_unit',$sqlinsert);
				$db->SQLdelete('battery_used',$sqlinsert);
				$db->SQLdelete('battery_history',$sqlinsert);
			}
		}
		$go->redirect('battery');
	break;		
	case 'disconnect1':
	case 'disconnect': 
		if($access->get('monitordevice')){
			$rid = (isset($_GET['rid']) ? Clean::int($_GET['rid']) : 0);
			$src = (isset($_GET['src']) ? Clean::text($_GET['src']) : 'used');
			$historyConnect = 'disconnect';
			$historyDeviceId = (isset($d) ? (int)$d : 0);
			$historyText = 'Disconnected from node';

			if(isset($id) && $id>0){
				if($rid > 0 && $src === 'unit'){
					$unit = $db->Simple("SELECT * FROM battery_unit WHERE id = '{$rid}' AND batteryid = '{$id}' LIMIT 1");
					if(!empty($unit['id'])){
						$historyConnect = 'unit';
						$historyDeviceId = (int)$unit['unitid'];
						$unitInfo = $db->Fast('ponunit','*',['id'=>$unit['unitid']]);
						$historyText = $lang['battery_2'].', UNIT, '.($unitInfo['name'] ?? ('#'.$unit['unitid']));
						$db->SQLdelete('battery_unit',['id'=>(int)$unit['id']]);
					}
				}else{
					$battery = [];
					if($rid > 0){
						$battery = $db->Simple("SELECT * FROM battery_used WHERE id = '{$rid}' AND batteryid = '{$id}' LIMIT 1");
					}elseif(isset($d) && $d>0){
						$battery = $db->Simple("SELECT * FROM battery_used WHERE batteryid = '{$id}' AND deviceid = '{$d}' ORDER BY id DESC LIMIT 1");
					}
					if(!empty($battery['id'])){
						$device = 'N/A';
						$device_info = '';
						$ip = 'N/A';
						$historyConnect = (string)$battery['connectd'];
						$historyDeviceId = (int)$battery['deviceid'];
						if($battery['connectd']=='ping3'){
							$getDevice = $db->Fast('mon_ping3','*',['id'=>$battery['deviceid']]);
							$device = 'PING3';
							$device_info = ''.$getDevice['name'].'';
							$ip = $getDevice['netip'];
						}elseif($battery['connectd']=='pmon'){
							$device = 'PMon Sensor';
							$device_info = 'PMon Sensor';
						}elseif($battery['connectd']=='olt'){
							$getDevice = $db->Fast('switch','*',['id'=>$battery['deviceid']]);
							$device = 'OLT';
							$device_info = ''.$getDevice['place'].'';
							$ip = $getDevice['netip'];
						}
						$db->SQLdelete('battery_used',['id'=>(int)$battery['id']]);
						$historyText = $lang['battery_2'].', '.$lang['device'].' '.$device.', '.$battery['added'].', '.$device_info.', ip '.$ip.'';
					}
				}
				$upd = [];
				$upd['added'] = date('Y-m-d H:i:s');
				$upd['history'] = $historyText;
				$upd['batteryid'] = $id;
				$upd['deviceid'] = $historyDeviceId;
				$upd['connectd'] = $historyConnect;
				$db->SQLinsert('battery_history',$upd);
			}
		}
		$go->go('/?do=battery&act=viewbattery&id='.(int)$id);
	break;		
	case 'list':
		$sqlbattery = getAllBattery();
		$speedbar .= '<a class="brmhref" href="/?do=battery"><i class="fi fi-rr-car-battery"></i>'.$lang['sklad_battery'].'</a>';
		if(is_array($sqlbattery)){
			$result .='<div id="sklad-battery-list">';
			$result .='<div id="sklad-battery">';
			$result .='<div class="sklad-battery-use">Р’РёРєРѕСЂРёСЃС‚РѕРІСѓСЋС‚СЊСЃСЏ</div>';
			$time_work = '';
			if(isset($sqlbattery['used']) && count($sqlbattery['used']) > 0){
				foreach($sqlbattery['used'] as $idbat => $battery){
					$b_status = '<span class="statusfree">
					'.$lang['actives'].'
					</span>';
					if($battery['unit']=='yes'){
						$time_work = '<div class="unit_added">'.aftertime($battery['unit_added']).'</div>';
					}
					$result .='<div class="pmonbattery ">
						<a href="/?do=battery&act=viewbattery&id='.$battery['batteryid'].'" >
							<div class="size_battery pmon'.$battery['types'].'"> </div>	
						</a>						
						<div class="pmonname">
							<div class="pmon_nomer">#'.$battery['name'].' ';
						if($battery['unit']=='yes'){	
							$result .='
							<img src="../style/img/lanslot.png">
							<a href="/">'.$battery['device_unit'].'</a>
							';
						}
						if(isset($battery['device_ping3']) && !empty($battery['device_ping3'])){
						$result .='<img src="../style/img/code.png">
							<a href="/">'.$battery['device_ping3'].'</a>
							';
						}
						$result .='</div>
							<a href="/?do=battery&act=viewbattery&id='.$battery['batteryid'].'" class="model_akb">'.$battery['model'].'</a>
						</div>
						<div class="pmonttx">
							<div class="pmonamper">'.$battery['amper'].'Ah</div>
							<div class="pmonvolt">'.$battery['voltage'].'V</div>
							'.$b_status.'
							'.$time_work.'
						</div>
					</div>';
				}
			}
			$result .='</div>';
			$result .='<div id="sklad-battery">';
			$result .='<div class="sklad-battery-sklad">РќР° СЃРєР»Р°РґС–</div>';			
			if(isset($sqlbattery['sklad']) && count($sqlbattery['sklad']) > 0){
				foreach($sqlbattery['sklad'] as $idbat => $battery_sklad){
					$b_status_s = '<span class="'.($battery_sklad['used']=='yes'?'statusfree':'statusact').'">
					'.($battery_sklad['used']=='yes'?$lang['actives']:$lang['free']).'
					</span>';
					$result .='<div class="pmonbattery ">
						<a href="/?do=battery&act=viewbattery&id='.$battery_sklad['batteryid'].'">
							<div class="size_battery pmon'.$battery_sklad['types'].'"></div>
						</a>						
						<div class="pmonname">
							<div class="pmon_nomer">'.$battery_sklad['name'].'</div>
							'.$battery_sklad['model'].'
						</div>
						<div class="pmonttx">
							<div class="pmonamper">'.$battery_sklad['amper'].'Ah</div>
							<div class="pmonvolt">'.$battery_sklad['voltage'].'V</div>
							'.$b_status_s.'
						</div>
					</div>';
				}
			}
			$result .='</div>';
			$result .='</div>';
		}else{
			$result .='<div class="namebattery">'.$lang['sklad_battery'].'</div>';
			$result .='<div id="sklad-battery"></div>';
		}
	break;	
	case 'addbattery':
		if($access->get('monitordevice')){
			$result .='<div class="card">';
			$speedbar .= '<a class="brmhref" href="/?do=battery"><i class="fi fi-rr-car-battery"></i>'.$lang['sklad_battery'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['add_battery'].'</span>';
			$result .='<div class="namebattery">'.$lang['add_battery'].'</div><form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="savebattery">';
			$result .= formpage(['img'=>'addconnect.png','name'=>$lang['name'],'descr'=>$lang['addbattery_8'],'pole'=>'<input style="width:69%;" name="name" class="input1" type="text">']);
			$result .= formpage(['img'=>'addconnect.png','name'=>$lang['addbattery_7'],'descr'=>$lang['addbattery_6'],'pole'=>'<input style="width:69%;" name="model" class="input1" type="text">']);
			$result .= formpage(['img'=>'addconnect.png','name'=>'SN','descr'=>$lang['sn'],'pole'=>'<input style="width:69%;" name="sn" class="input1" type="text">']);
			$typebattery = '<select class="select" name="typebattery"><option value="12">12</option><option value="24">24</option><option value="48">48</option><option value="72">72</option></select>';
			$result .= formpage(['img'=>'addconnect.png','name'=>$lang['addbattery_5'],'descr'=>$lang['addbattery_4'],'pole'=>$typebattery]);
			$types  = '<select class="select" name="types">
				<option value="lifepo4">LifePo4</option>
				<option value="agm">AGM</option>
				<option value="sbs">SBS</option>
				<option value="gel">GEL</option>
				<option value="default">Default</option>
			</select>';
			$result .= formpage(['img'=>'addconnect.png','name'=>$lang['addbattery_3'],'descr'=>$lang['addbattery_4'],'pole'=>'<input style="width:15%;" name="amper" class="input1" type="text">']);
			$result .= formpage(['img'=>'addconnect.png','name'=>$lang['addbattery_1'],'descr'=>$lang['addbattery_1'],'pole'=>$types]);
			$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form>';
			$result .= '</div>';
		}else{
			$go->redirect('battery');
		}
	break;	
	default:
		$speedbar .= '
			<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['sklad_battery'].'</span>
		';
		$array_battery = $db->SimpleWhile("SELECT * FROM battery ORDER BY id DESC");

		$latestUsedRows = $db->SimpleWhile("
			SELECT bu.*
			FROM battery_used bu
			INNER JOIN (
				SELECT batteryid, MAX(id) AS max_id
				FROM battery_used
				GROUP BY batteryid
			) x ON x.max_id = bu.id
		");
		$usedByBattery = [];
		$ping3Ids = [];
		$oltIds = [];
		if(is_array($latestUsedRows)){
			foreach($latestUsedRows as $row){
				$bid = (int)$row['batteryid'];
				$usedByBattery[$bid] = $row;
				if($row['connectd'] === 'ping3'){
					$ping3Ids[] = (int)$row['deviceid'];
				}elseif($row['connectd'] === 'olt'){
					$oltIds[] = (int)$row['deviceid'];
				}
			}
		}

		$latestUnitRows = $db->SimpleWhile("
			SELECT bu.*
			FROM battery_unit bu
			INNER JOIN (
				SELECT batteryid, MAX(id) AS max_id
				FROM battery_unit
				GROUP BY batteryid
			) x ON x.max_id = bu.id
		");
		$unitByBattery = [];
		$unitIds = [];
		if(is_array($latestUnitRows)){
			foreach($latestUnitRows as $row){
				$bid = (int)$row['batteryid'];
				$unitByBattery[$bid] = $row;
				$unitIds[] = (int)$row['unitid'];
			}
		}
		$ping3Map = [];
		$switchMap = [];
		$unitMap = [];
		$toInList = function(array $ids){
			$ids = array_values(array_unique(array_map('intval', $ids)));
			$ids = array_filter($ids, function($v){ return $v > 0; });
			if(empty($ids)){
				return '';
			}
			return implode(',', $ids);
		};
		$inPing = $toInList($ping3Ids);
		if($inPing !== ''){
			$rows = $db->SimpleWhile("SELECT id, name, netip FROM mon_ping3 WHERE id IN ($inPing)");
			if(is_array($rows)){
				foreach($rows as $r){
					$ping3Map[(int)$r['id']] = $r;
				}
			}
		}
		$inOlt = $toInList($oltIds);
		if($inOlt !== ''){
			$rows = $db->SimpleWhile("SELECT id, place, netip FROM switch WHERE id IN ($inOlt)");
			if(is_array($rows)){
				foreach($rows as $r){
					$switchMap[(int)$r['id']] = $r;
				}
			}
		}
		$inUnit = $toInList($unitIds);
		if($inUnit !== ''){
			$rows = $db->SimpleWhile("SELECT id, name FROM ponunit WHERE id IN ($inUnit)");
			if(is_array($rows)){
				foreach($rows as $r){
					$unitMap[(int)$r['id']] = $r;
				}
			}
		}

		$result .= '<table class="resp-tab module_battery" style="width:auto;"><thead><tr>
		<th>'.$lang['status'].'</th>
		<th>'.$lang['device'].'</th>
		<th>'.$lang['device'].'</th>
				<th>'.$lang['photo'].'</th>
		<th>'.$lang['name'].'/'.$lang['model'].'</th><th>'.$lang['v'].'</th><th>'.$lang['addbattery_3'].'</th><th>'.$lang['sn'].'</th><th>'.$lang['record_with'].'</th></tr></thead><tbody>';		
		$count_list = 1;
		if(isset($array_battery) && count($array_battery) > 0){
			foreach($array_battery as $idbat => $battery){
				$img = '';
				$device = '';
				$device_info = '';
				$batteryId = (int)$battery['id'];
				$getcount = (isset($usedByBattery[$batteryId]) ? $usedByBattery[$batteryId] : null);
				$getunit = (isset($unitByBattery[$batteryId]) ? $unitByBattery[$batteryId] : null);
				if(isset($getcount['id']) && $getcount['id']){
					$used = '<span class="signal3">'.$lang['actives'].'</span>';
					if($getcount['connectd']=='ping3'){
						$device = 'PING3';
						$getDevice = (isset($ping3Map[(int)$getcount['deviceid']]) ? $ping3Map[(int)$getcount['deviceid']] : []);
						$device_info = (isset($getDevice['name']) ? $getDevice['name'] : '#'.(int)$getcount['deviceid']);
					}elseif($getcount['connectd']=='pmon'){
						$device = 'PMon Sensor';
						$device_info = 'PMon Sensor';
					}elseif($getcount['connectd']=='olt'){
						$device = 'OLT';
						$getDevice = (isset($switchMap[(int)$getcount['deviceid']]) ? $switchMap[(int)$getcount['deviceid']] : []);
						$device_info = (isset($getDevice['place']) ? $getDevice['place'] : '#'.(int)$getcount['deviceid']);
					}else{
						$device = strtoupper((string)$getcount['connectd']);
						$device_info = '#'.(int)$getcount['deviceid'];
					}
				}elseif(isset($getunit['id']) && $getunit['id']){
					$used = '<span class="signal3">'.$lang['actives'].'</span>';
					$device = 'UNIT';
					$getUnit = (isset($unitMap[(int)$getunit['unitid']]) ? $unitMap[(int)$getunit['unitid']] : []);
					$device_info = (isset($getUnit['name']) ? $getUnit['name'] : '#'.(int)$getunit['unitid']);
				}else{
					$used = '<span class="off_">'.$lang['free'].'</span>';
					$device_info = '';
					$device = '';
				}
				if(!empty($battery['photo'])){
					$img = '<img src="/?do=thumb&type=photo&img=' . htmlspecialchars((string)$battery['photo']) . '&s=4">';
				}
				$result .= '<tr>';
				$result .= '<td>'.$used.'</td>';
				$result .= '<td>'.htmlspecialchars((string)$device).'</td>';
				$result .= '<td>'.htmlspecialchars((string)$device_info).'</td>';
				$result .= '<td>'.$img.'</td>';
				$result .= '<td class="description_name td_url akb"><a href="/?do=battery&act=viewbattery&id='.(int)$battery['id'].'">'.htmlspecialchars((string)$battery['name']).'<img class="link_fiber" src="../style/img/link.png"><span class="battery_model">'.htmlspecialchars((string)$battery['model']).'</span></a></td>';
				$result .= '<td><span class="signal1">'.htmlspecialchars((string)$battery['voltage']).'</span></td>';
				$result .= '<td><span class="signal3">'.htmlspecialchars((string)$battery['amper']).'</span></td>';
				$result .= '<td style="color: blue;">'.htmlspecialchars((string)$battery['sn']).'</td>';
				$result .= '<td>'.htmlspecialchars((string)$battery['added']).'</td>';
				$result .= '</tr>';
				$count_list ++;
			}
		}else{
			$result .= '<tr><td colspan="5">'.$lang['empty'].'</td></tr>';
		}
		$result .= '</table>';		
}
$metatags = array('title'=>$lang['sklad_battery'],'description'=>$lang['sklad_battery'],'page'=>'battery');
$tpl->load_template('battery/page.tpl');
$tpl->set('{speedbar}',$speedbar);
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
?>

