<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('bandwidth_monitor')){
	$go->go('/?do=board');
	exit;
}
$statMode = isset($_GET['stat']) ? Clean::text($_GET['stat']) : 'avg';
if (!in_array($statMode, ['avg','max'], true)) {
    $statMode = 'avg';
}
$preferUnits = (isset($_GET['t']) && Clean::int($_GET['t']) === 100) ? 'Mbps' : 'auto';
$tplresult = '';
$metatags = array('title'=>$lang['monitor_bandwidth'],'description'=>$lang['monitor_bandwidth'],'page'=>'bandwidth');
switch($act){
	case 'real':
		$portid = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		$typesport = isset($_GET['t'])  ? Clean::int($_GET['t'])  : null; // 100 => Mbps
		if (!$portid) { 
			$go->redirect('bandwidth'); 
			break; 
		}
		$d = $db->Simple("SELECT * FROM traff_monitor WHERE id = '{$portid}'");
		if (!$d) { 
			$go->redirect('bandwidth'); 
			break; 
		}
		if ($d['types'] === 'onu') {
			$mon_data = $db->Simple("SELECT inface, type FROM onus WHERE olt = '{$d['deviceid']}' AND idonu = '{$d['idonu']}'");
			$inface = $mon_data ? ($mon_data['type'].' '.$mon_data['inface']) : '';
		} else {
			$mon_data = $db->Simple("SELECT * FROM switch_port WHERE deviceid = '{$d['deviceid']}' AND llid = '{$d['llid']}'");
			$inface = $mon_data ? $mon_data['nameport'] : '';
		}
		$mon_switch = $db->Simple("SELECT place FROM switch WHERE id = '{$d['deviceid']}'");
		$datesRows = $db->SimpleWhile(" SELECT DATE(`date`) AS d FROM bandwidth_daily WHERE portid = '{$d['id']}' AND `date` >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND `date` <= CURDATE() ORDER BY `date` ASC");
		$availableDates = [];
		if ($datesRows) foreach ($datesRows as $r) { $availableDates[] = $r['d']; }
		$today = new DateTime('today');
		$todayYMD = $today->format('Y-m-d');
		$startRange = (clone $today)->modify('-29 days');
		$endRange = (clone $today);
		$calStart = (clone $startRange);
		$dow = (int)$calStart->format('N'); // 1..7
		if ($dow > 1) { $calStart->modify('-'.($dow-1).' days'); }
		$calEnd = (clone $endRange);
		$dow = (int)$calEnd->format('N');
		if ($dow < 7) { $calEnd->modify('+'.(7-$dow).' days'); }
		$calendarHtml  ='';
		$calendarHtml .='<div class="calendar30">';
		$calendarHtml .='<table class="cal-table" cellspacing="0" cellpadding="0">';
		$calendarHtml .='<thead><tr>';
		$calendarHtml .='<th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th>Сб</th><th>Нд</th>';
		$calendarHtml .='</tr></thead>';
		$calendarHtml .='<tbody>';
		$cursor = clone $calStart;
		while ($cursor <= $calEnd) {
			$calendarHtml .= '<tr>';
			for ($i=0; $i<7; $i++) {
				$ymd = $cursor->format('Y-m-d');
				$label = $cursor->format('d');
				$month = $cursor->format('M');
				$inRange = ($cursor >= $startRange && $cursor <= $endRange);
				$hasData = in_array($ymd, $availableDates, true);
				$isToday = ($ymd === $todayYMD);
				$cls = ['cal-td'];
				if (!$inRange) $cls[] = 'muted';
				if ($hasData) $cls[] = 'hasdata';
				if ($isToday) $cls[] = 'today';
				$classes = implode(' ', $cls);
				$calendarHtml .= '<td class="'.$classes.'" data-date="'.htmlspecialchars($ymd,ENT_QUOTES).'" title="'.htmlspecialchars($ymd,ENT_QUOTES).'"'.($inRange?'':' data-disabled="1"').'>';
				$calendarHtml .= '<div class="d">'.$label.'</div>';
				$calendarHtml .= '<div class="m">'.($label==='01' ? $month : '').'</div>';
				$calendarHtml .= '</td>';
				$cursor->modify('+1 day');
			}
			$calendarHtml .= '</tr>';
		}
		$calendarHtml .= '</tbody>';
		$calendarHtml .= '</table>';
		$calendarHtml .= '<div class="cal-legend"><span><i class="b b1"></i>Є дані</span><span><i class="b b2"></i>Сьогодні</span></div>';
		$calendarHtml .= '</div>';
		$submenu = '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.($mon_switch['place'] ?? '').' '.$inface.'</span>';
		$unitLabel = ($typesport === 100) ? 'Mb' : 'Gb';
		$tplresult .= '
		<div id="trafficStatsBlock" class="pmon_block" style="display:flex;gap:12px;align-items:flex-start">
			<div class="pmon_block_left block_white pre20" style="width:300px;min-width:300px">
				'.$calendarHtml.'
				<div class="traffic-panel" >
					<a class="color1 '.($unitLabel==="Gb"?'active':'').'" href="#" data-unit="Gb">Gbps</a>
					<a class="color2 '.($unitLabel==="Mb"?'active':'').'" href="#" data-unit="Mb">Mbps</a>
					<a class="color3" href="/?do=bandwidth&act=delet&id='.$portid.'">'.$lang['delet'].'</a>
				</div>
			</div>
			<div class="pmon_block_right pre80">
				<div class="connect-list-head">
					<h2>'.$mon_switch['place'].' <b>'.($inface ?? "").'</b> 
						<small class="sel-day">(<span id="selDayLabel"></span>)</small>
					</h2>
				</div>
				<div id="chartContainer" style="height: 420px; width: 100%;">
					<svg id="trafficChart"></svg>
				</div>
			</div>
		</div>
		<script>
		(function(){
			const portId = '.(int)$portid.';
			let unit = "'.($unitLabel==='Mb'?'Mb':'Gb').'";
			const todayYMD = "'.$todayYMD.'";
			let selected = todayYMD;
			const svgEl = document.getElementById("trafficChart");
			const container = document.getElementById("chartContainer");
			svgEl.setAttribute("width", container.clientWidth);
			svgEl.setAttribute("height", 420);
			const selDayLabel = document.getElementById("selDayLabel");
			function fmtDMY(ymd){
				const p = ymd.split("-");
				return p[2]+"."+p[1]+"."+p[0];
			}
			function setSelLabel(ymd){ if(selDayLabel){ selDayLabel.textContent = fmtDMY(ymd); } }
			document.querySelectorAll(".cal-table td.cal-td").forEach(td=>{
				td.addEventListener("click", ()=>{
					if (td.classList.contains("muted") || td.getAttribute("data-disabled") === "1") return;
					document.querySelectorAll(".cal-table td.cal-td.active").forEach(x=>x.classList.remove("active"));
					td.classList.add("active");
					selected = td.dataset.date; // Y-m-d
					setSelLabel(selected);
					loadDay(selected, unit);
				});
			});
			document.querySelectorAll(".traffic-panel a[data-unit]").forEach(a=>{
				a.addEventListener("click", (e)=>{
					e.preventDefault();
					document.querySelectorAll(".traffic-panel a[data-unit]").forEach(x=>x.classList.remove("active"));
					a.classList.add("active");
					unit = a.dataset.unit;
					loadDay(selected, unit);
				});
			});
			const todayTd = document.querySelector(\'.cal-table td.cal-td[data-date="'.$todayYMD.'"]\');
			if (todayTd){ todayTd.classList.add("active"); }
			setSelLabel(selected);
			loadDay(selected, unit);
			let lastData = null;
			window.addEventListener("resize", ()=>{
				svgEl.setAttribute("width", container.clientWidth);
				if (lastData) draw(lastData);
			}, {passive:true});
			function loadDay(ymd, unitMode){
				if (ymd === todayYMD) {
					const url = "ajax/bandwidth.php?id="+portId+"&real=1"+(unitMode==="Mb"?"&t=100":"");
					fetch(url).then(r=>r.json()).then(json=>{ lastData=json; draw(json); });
					return;
				}
				const p = ymd.split("-");
				const dm = p[2]+"."+p[1];
				const url2 = "ajax/bandwidth.php?id="+portId+"&d="+encodeURIComponent(dm)+(unitMode==="Mb"?"&t=100":"");
				fetch(url2).then(r=>r.json()).then(json=>{ lastData=json; draw(json); });
			}
			function draw(data){
				const svg = d3.select("#trafficChart");
				svg.selectAll("*").remove();
				const margin = {top: 20, right: 30, bottom: 50, left: 70};
				const width  = +svg.attr("width")  - margin.left - margin.right;
				const height = +svg.attr("height") - margin.top  - margin.bottom;
				const g = svg.append("g").attr("transform", `translate(${margin.left},${margin.top})`);
				const allDates = data.dates || [];
				const yMax = Math.max(
					(d3.max(data.in_data || [0]) || 0),
					(d3.max(data.out_data || [0]) || 0)
				);
				const x = d3.scaleBand().domain(allDates).range([0, width]).padding(0.1);
				const y = d3.scaleLinear().domain([0, yMax]).nice().range([height, 0]);
				const defs = svg.append("defs");
				const inG = defs.append("linearGradient").attr("id","inGradient").attr("x1","0%").attr("x2","0%").attr("y1","0%").attr("y2","100%");
				inG.append("stop").attr("offset","0%").attr("stop-color","#007ab6").attr("stop-opacity",1);
				inG.append("stop").attr("offset","100%").attr("stop-color","#8cd9ff").attr("stop-opacity",0.6);
				const outG = defs.append("linearGradient").attr("id","outGradient").attr("x1","0%").attr("x2","0%").attr("y1","0%").attr("y2","100%");
				outG.append("stop").attr("offset","0%").attr("stop-color","#9d05ac").attr("stop-opacity",1);
				outG.append("stop").attr("offset","100%").attr("stop-color","#ff75d3").attr("stop-opacity",0.1);
				const hoursToShow = [];
				for (let h = 0; h < 24; h++) {
					for (let m of [0, 30]) {
						const hh = String(h).padStart(2, "0");
						const mm = String(m).padStart(2, "0");
						hoursToShow.push(`${hh}:${mm}`);
					}
				}
				const filtered = allDates.filter(h => hoursToShow.includes(h));
				g.append("path")
					.datum(data.in_data || [])
					.attr("fill","url(#inGradient)")
					.attr("stroke","none")
					.attr("d", d3.area()
						.curve(d3.curveBasis)
						.x((d,i)=> x(allDates[i]) + (x.bandwidth()/3))
						.y0(height)
						.y1(d=> y(d))
					);
				g.append("path")
					.datum(data.out_data || [])
					.attr("fill","url(#outGradient)")
					.attr("stroke","none")
					.attr("d", d3.area()
						.curve(d3.curveBasis)
						.x((d,i)=> x(allDates[i]) + (x.bandwidth()/3))
						.y0(height)
						.y1(d=> y(d))
					);
				g.append("g")
					.attr("class","axis axis--x")
					.attr("transform",`translate(0,${height})`)
					.call(d3.axisBottom(x).tickValues(filtered))
					.selectAll("text")
						.attr("transform","rotate(-90)")
						.attr("dy","-5").attr("dx","-8")
						.attr("fill","#222").style("text-anchor","end");
				g.append("g")
					.attr("class","axis axis--y")
					.call(
						d3.axisLeft(y).ticks(10).tickFormat(d => d + " " + unit)
					);
			}		
		})();
		</script>
		';
		break;
	case 'addonu': 	
		$idonu = isset($_GET['idonu']) ? Clean::int($_GET['idonu']) : null;
		$olt = isset($_GET['olt']) ? Clean::int($_GET['olt']) : null;
		if(is_valid_id($idonu) && is_valid_id($olt)){
		$dataonu = $db->Fast('onus','*',['idonu'=>$idonu]);
		$dataolt = $db->Fast('switch','place,oidid,netip',['id'=>$dataonu['olt']]);
		if($dataolt['oidid']==1){
			$llid = $dataonu['keyonu'];
			$oidin = '1.3.6.1.2.1.31.1.1.1.6.'.$llid;
			$oidout = '1.3.6.1.2.1.31.1.1.1.10.'.$llid;
		}elseif($dataolt['oidid']==41){
			$llid = $dataonu['keyonu'];
			$oidin = '1.3.6.1.4.1.34592.1.3.100.12.7.1.4.'.$llid;
			$oidout = '1.3.6.1.4.1.34592.1.3.100.12.7.1.5.'.$llid;				
		}elseif($dataolt['oidid']==2){
			$llid = $dataonu['keyonu'];
			$oidin = '1.3.6.1.2.1.31.1.1.1.6.'.$llid;
			$oidout = '1.3.6.1.2.1.31.1.1.1.10.'.$llid;			
		}elseif($dataolt['oidid']==14 && $dataonu['type']=='gpon'){
			$llid = $dataonu['zte_idport'].'.'.$dataonu['keyonu'];
			$oidin = '1.3.6.1.4.1.2011.6.128.1.1.4.23.1.3.'.$llid;
			$oidout = '1.3.6.1.4.1.2011.6.128.1.1.4.23.1.4.'.$llid;			
		}
		$submenu = '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$dataolt['place'].' / '.$dataonu['type'].' '.$dataonu['inface'].'</span>';
		$tplresult .= '<div class="card"><form action="/?do=bandwidth" method="post" id="formadd"><input name="act" type="hidden" value="connectonu"><input name="llid" type="hidden" value="'.$llid.'"><input name="idonu" type="hidden" value="'.$idonu.'"><input name="olt" type="hidden" value="'.$dataonu['olt'].'">';
		$tplresult .= formpage(['img'=>'addconnect.png','name'=>'ONT','descr'=>$lang['monitor_device'],'pole'=>$dataonu['type'].' '.$dataonu['inface']]);
		$tplresult .= formpage(['img'=>'addconnect.png','name'=>'Input','descr'=>'OID ifHCInOctets','pole'=>'<input style="width:50%;" name="oidin" class="input1" type="text" value="'.$oidin.'">']);
		$tplresult .= formpage(['img'=>'addconnect.png','name'=>'Output','descr'=>'OID ifHCOutOctets','pole'=>'<input style="width:50%;" name="oidout" class="input1" type="text" value="'.$oidout.'">']);
		$tplresult .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['addeds'].'</button></form></div>';
		}
	break;
	case 'connectonu':	
		$oidout = isset($_POST['oidout']) ? Clean::text($_POST['oidout']) : null;
		$oidin = isset($_POST['oidin']) ? Clean::text($_POST['oidin']) : null;
		$llid = isset($_POST['llid']) ? Clean::int($_POST['llid']) : null;
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;
		$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']) : null;
		if(is_valid_id($llid) && is_valid_id($idonu) && is_valid_id($olt)){			
			$switch = $db->Simple("SELECT id, netip, snmpro, oidid FROM switch WHERE id = '{$olt}' LIMIT 1");
			$oid_out = $oidout;
			$oid_in = $oidin;
			if (isset($oid_in) && isset($oid_out)) {
				$sql = "INSERT INTO traff_monitor (idonu, deviceid, types, llid, netip, snmpro, oid_in, oid_out) VALUES ('{$idonu}','{$switch['id']}', 'onu', '{$llid}', '{$switch['netip']}', '{$switch['snmpro']}', '{$oid_in}', '{$oid_out}')";
			}
			if(isset($sql)){
				$db->query($sql);
				del_cache_simple_sql('temp_traff_monitor_');			
				del_cache_simple_sql('traff_monitor');	
				$go->go('/?do=onu&id='.$idonu);
				exit;
			}
			$go->redirect('device');
			exit;
		}
		$go->redirect('device');
		exit;
	break;
	case 'connect': 		
		$portid = isset($_POST['portid']) ? Clean::int($_POST['portid']) : null;
		$types = isset($_POST['types']) ? Clean::text($_POST['types']) : null;
		$active = isset($_POST['active']) ? Clean::text($_POST['active']) : null;
		if(isset($types) && $types=='manual'){
			
		}elseif(isset($types) && $types=='onu'){			
			
		}elseif(isset($types) && ($types=='ethernet' || $types=='port' || $types=='mikrotik' || $types=='ge' || $types=='pon' || $types=='xge' || $types=='gei' || $types=='ge' || $types=='xgei' || $types=='sfp' || $types=='gepon' || $types=='gpon' || $types=='pon' || $types=='epon')){
			$port = $db->Simple("SELECT deviceid, llid FROM switch_port WHERE id = '{$portid}' LIMIT 1");
			$switch = $db->Simple("SELECT netip, snmpro, oidid FROM switch WHERE id = '{$port['deviceid']}' LIMIT 1");
			switch ($switch['oidid']) {
				case 1:
				case 2:
				case 7:
				case 6:
				case 9:
				case 18:
				case 33:
				case 14:
				case 12:
				case 15:
				case 16:
				case 20:
				case 35:
				case 37:
				case 36:
				case 42:
				case 32:
				case 28:
				case 24:
				case 34:
				case 38:
				case 39:
				case 40:
					$oid_out = "1.3.6.1.2.1.31.1.1.1.10.port";
					$oid_in = "1.3.6.1.2.1.31.1.1.1.6.port";
					break;
				case 3:

					break;
				default:

					break;
			}
			if (isset($oid_in) && isset($oid_out)) {
				$sql = "INSERT INTO traff_monitor (deviceid, types, llid, netip, snmpro, oid_in, oid_out) 
						VALUES ('{$port['deviceid']}', '{$types}', '{$port['llid']}', '{$switch['netip']}', '{$switch['snmpro']}', '{$oid_in}', '{$oid_out}')";
			}
		}
		if(isset($sql)){
			$db->query($sql);
			del_cache_simple_sql('temp_traff_monitor_');			
			del_cache_simple_sql('traff_monitor');	
		}
		exit;
	break;
	case 'delet': 	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id)){
			$d = $db->Simple("SELECT * FROM traff_monitor WHERE id = '{$id}' LIMIT 1");
			$db->query("DELETE FROM traff_monitor WHERE id = '{$id}'");
			$db->query("DELETE FROM snmp_data WHERE portid = '{$id}'");	
			del_cache_simple_sql('temp_traff_monitor_');			
			del_cache_simple_sql('traff_monitor');			
		}
		$go->go('/?do=bandwidth');
		exit;
	break;		
	default:
		$olt = isset($_GET['olt']) ? Clean::int($_GET['olt']) : null;
		$sql_traff_monitor = [
			'sql' => 'SELECT DISTINCT deviceid FROM traff_monitor','type' => 'while','key' => 'traff_monitor','time' => 3600
		];
		$sql_monitor = [
			'sql' => 'SELECT * FROM traff_monitor','type' => 'while','uniq' => 'deviceid','key' => 'temp_traff_monitor_','time' => 7200
		];
		$monitor_grouped = cache_simple_sql($sql_traff_monitor);
		$traff_monitor = sql__($sql_monitor);
		$todaydays = date('d.m');
		$tplresult .= "<div class='pmon_block' id='board_fault'>";
		$tplresult .= "<div class='pmon_block_left block_white pre20 menu_switch'>";
		$sql = "SELECT s.* FROM checkaccess a JOIN switch s ON CONCAT('dev', s.id) = a.types WHERE a.uid = :uid AND s.device = 'olt'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':uid' => $USER['id']]);
		$sql_list_olt = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($sql_list_olt) && !empty($monitor_grouped)) {
			$monitored_ids = array_column($monitor_grouped, 'deviceid');
			$tplresult .= "<div class='switch_a'>";
			foreach ($sql_list_olt as $sw) {
				if (in_array($sw['id'], $monitored_ids)) {
					$ports_count = isset($traff_monitor[$sw['id']]) ? count($traff_monitor[$sw['id']]) : 0;
					$tplresult .= "<a href='/?do=bandwidth&olt={$sw['id']}'>
						<img src='../style/img/code.png'>
						{$sw['place']} <span style='color: #888;'>($ports_count)</span>
					</a>";
				}
			}
			$tplresult .= "</div>";
		}
		$tplresult .= "</div>";
		$tplresult .= "<div class='pmon_block_right pre80'>";
		if (isset($monitor_grouped) && count($monitor_grouped) > 0) {
			$array_switch = [];
			foreach ($monitor_grouped as $mon) {
				$mon_switch = $db->Simple("SELECT place,model,inf,id FROM switch WHERE id = '{$mon['deviceid']}'");
				$array_switch[$mon['deviceid']] = $mon_switch;
			}
			$total_switches = count($monitor_grouped);
			$tplresult .= "
				<table id='board_fault'><thead><tr>
				<th width='5%'></th>
				<th width='15%'>Interface</th>
				<th></th>
				<th></th>
				<th></th>
				<th width='8%'>Interval</th>				
				".($access->get('bandwidth_monitor') ? "<th width='5%'></th>" : "")."
				</tr></thead>
			";
			foreach ($monitor_grouped as $mon) {
				if (isset($olt) && $olt > 0 && $mon['deviceid'] != $olt) continue;
				if ($access->get('dev'.$mon['deviceid'])) {
					$tplresult .= "
						<tr><td colspan='10' class='name_inface_traffic'> 
							<a href='/?do=bandwidth&olt={$mon['deviceid']}'>".$array_switch[$mon['deviceid']]['place']."</a>
						</td></tr>
					";
					$ports_count = 0;
					if (isset($traff_monitor[$mon['deviceid']]) && count($traff_monitor[$mon['deviceid']]) > 0) {
						foreach ($traff_monitor[$mon['deviceid']] as $d) {
							$stats = bandwidth_today_stats((int)$d['id'], $pdo, 'both', 60);
							if ($statMode === 'max') {
    #$cellText = 'MAX за сьогодні: '
    #    . 'IN '  . formatBitsPerSecond($stats['max_in_bps'],  $preferUnits)
    #    . ' / OUT ' . formatBitsPerSecond($stats['max_out_bps'], $preferUnits);
} else { // avg
    $cellText = 'СЕРЕДНЄ за сьогодні: '
        . 'IN '  . formatBitsPerSecond($stats['avg_in_bps'],  $preferUnits)
        . ' / OUT ' . formatBitsPerSecond($stats['avg_out_bps'], $preferUnits);
}
							$ports_count++;
							$mon_descr = '';
							$mon_day = snmp_data_day($d['id']);
							if ($d['types'] == 'onu') {
								$mon_data = $db->Simple("SELECT inface, type FROM onus WHERE olt = '{$d['deviceid']}' AND idonu = '{$d['idonu']}'");
								$inface = $mon_data['type'].' '.$mon_data['inface'];
							} else {
								$mon_data = $db->Simple("SELECT descrport, nameport FROM switch_port WHERE deviceid = '{$d['deviceid']}' AND llid = '{$d['llid']}'");
								$mon_descr = ($mon_data['descrport']??'n/a');
								$inface = $mon_data['nameport'];
							}
							$device_types = match($d['types']) {
								'onu' => '<span class="signal3">ONU</span>',
								'pon','epon','gpon' => '<span class="signal1">PON</span>',
								'mikrotik' => '<span class="signal4">PORT</span>',
								'sfp' => '<span class="signal2">SFP</span>',
								default => '<span class="signal5">SFP</span>'
							};
							$tplresult .= "
    <tr class='olt_row_{$d['deviceid']}'>
        <td style='text-align: center;'>$device_types</td>
        <td class='name_port_traffic'>
            <a href='/?do=bandwidth&act=real&id={$d['id']}&d=$todaydays'>$inface</a>
        </td>
		        <td style='width: 120px;background:#cffdcf;text-align:center;color:green;'>
				"  . formatBitsPerSecond($stats['max_in_bps'],  $preferUnits)."
				</td>
				<td style='width: 120px;background:#ffeeee;text-align:center;color:red;'>
					".formatBitsPerSecond($stats['max_out_bps'], $preferUnits)."
				</td>
        <td class='name_port_opis'>$mon_descr</td>
        <td>{$mon_day['days']} {$lang['day']}</td>
        ".($access->get('bandwidth_monitor') ? "<td><a class='panel_house rr_1' href='/?do=bandwidth&act=delet&id={$d['id']}'>{$lang['delet']}</a></td>" : "")."
    </tr>
";
						}
					}
					$tplresult .= "
						<tr><td colspan='10' style='background: #eee; font-weight: bold; padding:5px 10px;'>
							".$lang['all'].": $ports_count
						</td></tr>
					";
				}
			}
			$tplresult .= "</table>";
		}
		
	$submenu = '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['all_ports'].'</span>';
}
$result ='<div id="onu-speedbar">
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<a class="brmhref" href="/?do=bandwidth"><i class="fi fi-rr-angle-left"></i>'.$lang['monitor_bandwidth'].'</a>
	'.$submenu.'</div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$result);
$tpl->compile('content');
$tpl->clear();
?>