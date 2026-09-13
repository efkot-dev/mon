<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$speedbar = '';
$result = '';
$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
if (!$confPMon['PING3'] && empty($confPMon['PING3'])) {
	$go->redirect('main');
}
$speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
switch($act){
	case 'add': 
		$metatags = array('title'=>$lang['addping3'],'description'=>$lang['addping3'],'page'=>'appping3');
		$speedbar .= '<a class="brmhref" href="/?do=ping3"><i class="fi fi-rr-angle-left"></i>'.$lang['list_ping3'].'</a>';
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['newping3'].'</span>';
		$listgroup = '';
		$listlocation = '';
		$result .= '<div class="card"><form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveping3">';
		$monitor  = '
			<select class="select" name="monitor">
				<option value="yes">'.$lang['monitor_on'].'</option>
				<option value="no">'.$lang['monitor_off'].'</option>
			</select>';		
		$typedevice  = '
			<select class="select" name="typedevice">
				<option value="1">Ping3</option>
				<option value="2">Модуль NMC SNMP</option>
			</select>';
		$result .= formpage(['img'=>'addconnect.png','name'=>'Device','descr'=>'Select ','pole'=>$typedevice]);
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['monitor_on'],'descr'=>$lang['monitor_device'],'pole'=>$monitor]);
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['ip'],'descr'=>$lang['ipdescr'],'pole'=>'<input style="width:33%;" name="netip" class="input1" type="text">']);
		$result .= formpage(['img'=>'addconnect.png','name'=>'Community','descr'=>'Snmp community PING3','pole'=>'<input style="width:33%;" name="snmpro" class="input1" type="text">']);
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['oid_gpon_name'],'descr'=>$lang['oid_gpon_name_desc'],'pole'=>'<input style="width:99%;" name="name" class="input1" type="text">']);
		$typebattery = '<select class="select" name="typebattery"><option value="12">12</option><option value="24">24</option><option value="48">48</option><option value="72">72</option></select>';
		$typean = '<select class="select" name="channel"><option value="1">AN1 - Ping3</option><option value="2">AN2 - Ping3</option><option value="3">AN3 - Ping3</option><option value="4">AN4 - Ping3</option></select>';
		$result .= formpage(['img'=>'addconnect.png','name'=>'AN','descr'=>$lang['channel_ping3'],'pole'=>$typean]);
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['types_battery'],'descr'=>$lang['types_battery_descr'],'pole'=>$typebattery]);
		$energystatus  = '<select class="select" name="energystatus"><option value="yes">'.$lang['monitor_on'].'</option><option value="no">'.$lang['monitor_off'].'</option></select>';
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['types_220'],'descr'=>$lang['types_220i'],'pole'=>$energystatus]);
		$group = $db->SimpleWhile("SELECT * FROM groups WHERE group_types = 3");
		if(is_array($group)){
			foreach($group as $gr){
				$listgroup .= '<option value="'.$gr['id'].'">'.$gr['name'].'</option>';
			}
			$result .= formpage(['img'=>'folders.png','name'=>$lang['group'],'descr'=>$lang['title_group'],'pole'=>'<select class="select" name="group" id="group"><option value="0"></option>'.$listgroup.'</select>']);
		}
		$location = getListLocations();
		if(is_array($location)){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
			}
			$result .= formpage(['img'=>'m6.png','name'=>$lang['location'],'descr'=>$lang['getlocation'],'pole'=>'<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select>']);
		}
		/*
		$result .= '<div class="batterystatus">
			<div class="batblock"><span class="col0">0%</span><span><input name="status0" type="text"></span></div>		
			<div class="batblock"><span class="col20">20%</span><span><input name="status20" type="text"></span></div>		
			<div class="batblock"><span class="col40">40%</span><span><input name="status40" type="text"></span></div>		
			<div class="batblock"><span class="col60">60%</span><span><input name="status60" type="text"></span></div>		
			<div class="batblock"><span class="col80">80%</span><span><input name="status80" type="text"></span></div>		
			<div class="batblock"><span class="col100">100%</span><span><input name="status100" type="text"></span></div>
		</div>';
		*/
		$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form></div>';
	break;		
	case 'stats': 
		$metatags = array('title'=>'Статистика виключень','description'=>'Статистика виключень','page'=>'stats');
		$speedbar .= '<a class="brmhref" href="/?do=ping3"><i class="fi fi-rr-angle-left"></i>'.$lang['list_ping3'].'</a>';
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Статистика виключень</span>';	
		$sql = "
			SELECT * FROM mon_ping3;
		";
		$results = $db->SimpleWhile($sql);
		if (isset($results) && count($results) > 2) {
			foreach ($results as $row) {			
				$intervals = get_timed_ping3($row['id']);
				$result .='<div class="line-ping3">';
				$result .='<div class="name-ping3">'.$row['name'].'<a href="/?do=ping3&act=view&id='.$row['id'].'"><img src="../style/img/link.png"></a></div>';
				$result .='<div class="grid-container">';
				foreach ($intervals as $idtimed => $color) {
					if($idtimed>0 && $idtimed<=14 && $color=='green'){
						$color = 'min-green';
					}elseif($idtimed>=15 && $idtimed<=26 && $color=='green'){
						$color = 'max-green';
					}		
					$hour = floor($idtimed / 2);
					$minute = ($idtimed % 2 == 0) ? '00' : '30';
					$timeLabel = sprintf("%02d:%s", $hour, $minute);					
					$result .='<div class="grid-item '.$color.'">'.$timeLabel.'</div>';
				}
				$result .='</div>';
				$result .='</div>';
			}
		}		
	break;		
	case 'clear': 	
		if($access->get('monitordevice')){
			$sqlinsert['deviceid'] = (isset($_GET['deviceid']) ? Clean::int($_GET['deviceid']) : null);
			$sqlinsert['connectd'] = 'ping3';
			if(!empty($sqlinsert['deviceid'])){
				$sql = "DELETE FROM mon_voltage WHERE deviceid = {$sqlinsert['deviceid']}";
				$db->query($sql);
				$go->go('/?do=ping3&act=view&id='.$sqlinsert['deviceid']);
				exit;
			}
		}	
	break;		
	case 'del': 	
		if($access->get('monitordevice')){
			$sqlinsert['deviceid'] = (isset($_GET['deviceid']) ? Clean::int($_GET['deviceid']) : null);
			$sqlinsert['connectd'] = 'ping3';
			if(!empty($sqlinsert['deviceid'])){
				$db->SQLdelete('mon_ping3',['id' => $sqlinsert['deviceid']]);
				$db->SQLdelete('battery_used',$sqlinsert);
			}
		}
		$go->redirect('ping3');
	break;		
	case 'view': 
		$view = (isset($_GET['view']) ? Clean::int($_GET['view']) : 1);
		$result .='<div id="ajaxping3"></div>';
		$getMonitorPIng3 = $db->Fast('mon_ping3','*',['id'=>$id]);
		$metatags = array('title'=>'Ping3 '.$getMonitorPIng3['name'],'description'=>'Ping3 '.$getMonitorPIng3['name'],'page'=>'viewping3ping3');
		if(!empty($getMonitorPIng3['id']) ?? $access->get('ping3_' . $getMonitorPIng3['id'])){
			$speedbar .= '<a class="brmhref" href="/?do=ping3"><i class="fi fi-rr-angle-left"></i>'.$lang['list_ping3'].'</a>';
			$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$getMonitorPIng3['name'].'</span>';
			$result .='<div id="viewping3"></div>';
			$result .='<script type="text/javascript">
				history_voltage(\''.$id.'\',\'ping3\',\''.$view.'\');
			</script>';
			$result .= '<div class="view-ping3-wrap">';
			$result .= '<div class="ping3-left">';
			$img = '../style/img/ping3.png';
			if ($getMonitorPIng3['typedevice'] == 2) {
				$img = '../style/img/nmc.png';
			}
			$result .= '<center><img src="'.$img.'" alt=""></center>';
			$result .= '<h2>'.$getMonitorPIng3['name'].'</h2>';
			if (!empty($getMonitorPIng3['locationid'])) {
				$getLocation = $db->Fast('location','*',['id'=>$getMonitorPIng3['locationid']]);
				if (!empty($getLocation['name'])) {
					$result .= '<b>'.$lang['locations'].':</b> '.$getLocation['name'].'<br>';
				}
			}
			if (!empty($getMonitorPIng3['groups'])) {
				$getGroups = $db->Fast('groups','*',['id'=>$getMonitorPIng3['groups']]);
				if (!empty($getGroups['name'])) {
					$result .= '<b>'.$lang['groups'].':</b> '.$getGroups['name'].'<br>';
				}
			}
			$energy = ($getMonitorPIng3['energy']==2
				? '<font color="red">'.$lang['no220'].'</font>'
				: '<font color="#15a56e">'.$lang['on220'].'</font>');
			$result .= '<b>'.$lang['220'].':</b> '.$energy.'<br>';
			$result .= '<b>IP:</b> '.$getMonitorPIng3['netip'].'<br>';
			if ($getMonitorPIng3['typedevice']==1) {
				$result .= '<b>'.$lang['channel'].'</b> <font color="blue">AN'.$getMonitorPIng3['channel'].'</font><br>';
			}
			elseif ($getMonitorPIng3['typedevice']==2) {
				$result .= '<b>Battery Temperature:</b> <font color="orange">'.$getMonitorPIng3['temp'].'°C</font><br>';
				$result .= '<b>Battery Capacity:</b> <font color="blue">'.$getMonitorPIng3['charger'].'%</font><br>';
			}
			$result .= '<b>'.$lang['v'].':</b> '.$getMonitorPIng3['volt'].'V<br>';
			$result .= '</div>';
			$result .= '<div class="ping3-right">';


// Перевірка доступу до PING3
if (isset($confPMon['PING3']) && !empty($confPMon['PING3']) && $confPMon['PING3'] == 1) {

    $sqllistping3 = $db->SimpleWhile('SELECT * FROM mon_ping3 WHERE monitor = "yes" AND energy = 2');

    if ($sqllistping3) {
        // Перевіряємо доступ користувача
        $hasAccess = false;
        foreach ($sqllistping3 as $ping3) {
            if ($access->get('ping3_' . $ping3['id'])) {
                $hasAccess = true;
                break;
            }
        }

        if ($hasAccess) {
            $result .= '<div class="main_blocks">
                            <div class="css-warning">
                                <div class="icon"><i class="fi fi-rr-bell"></i></div>
                                <div class="txt">'.$lang['notvolt'].'</div>
                            </div>
                            <div id="ping3">';

            foreach ($sqllistping3 as $ping3) {
                if ($access->get('ping3_' . $ping3['id'])) {
                    $result .= '<a href="/?do=ping3&act=view&id='.$ping3['id'].'" class="battery">
                                    <div class="battery-img">'.monitorPing3img($ping3).'<div class="battery-volt">'.(!empty($ping3['volt'])?$ping3['volt']:0).'v</div></div>
                                    <div class="battery-name">'.$ping3['name'].'</div>
                               </a>';
                }
            }

            $result .= '</div></div>';
        }
    }
}
$sqlused = $db->SimpleWhile("SELECT * FROM battery_used WHERE deviceid = {$id}");
if (is_array($sqlused) && count($sqlused) > 0) {
    $result .= '<script src="https://d3js.org/d3.v7.min.js"></script>';
    $result .= '<div class="battery-wrap">';
    foreach ($sqlused as $row) {
        $b = $db->Fast('battery', '*', ['id' => $row['batteryid']]);
        $chartData = [];
        $sqlVoltageTrend = "
            SELECT DATE_FORMAT(added, '%Y-%m-%d %H:00:00') AS dt, AVG(volt) AS avgv
            FROM mon_voltage
            WHERE deviceid = {$id}
              AND mon_types = 'ping3'
              AND energy = 2
              AND added >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE_FORMAT(added, '%Y-%m-%d %H:00:00')
            ORDER BY dt ASC
        ";
        $voltageTrendRows = $db->SimpleWhile($sqlVoltageTrend);
        if (is_array($voltageTrendRows) && count($voltageTrendRows) > 0) {
            foreach ($voltageTrendRows as $vr) {
                $chartData[] = ['date' => $vr['dt'], 'voltage' => round((float)$vr['avgv'], 3)];
            }
        } else {
            $sqlSessions = "SELECT created_at, voltage_start, voltage_end FROM battery_sessions WHERE deviceid = {$id} AND type='discharge' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at ASC";
            $sessions = $db->SimpleWhile($sqlSessions);
            if ($sessions) {
                foreach ($sessions as $s) {
                    $minV = min(floatval($s['voltage_start']), floatval($s['voltage_end']));
                    $chartData[] = ['date' => $s['created_at'], 'voltage' => $minV];
                }
            }
        }

        $degradationText = 'Недостатньо даних';
        $degradationColor = '#607d8b';
        $degradationHint = '';
        $sqlDeg72 = "
            SELECT DATE_FORMAT(added, '%Y-%m-%d %H:00:00') AS dt, AVG(volt) AS avgv
            FROM mon_voltage
            WHERE deviceid = {$id}
              AND mon_types = 'ping3'
              AND energy = 2
              AND added >= DATE_SUB(NOW(), INTERVAL 72 HOUR)
            GROUP BY DATE_FORMAT(added, '%Y-%m-%d %H:00:00')
            ORDER BY dt ASC
        ";
        $degRows = $db->SimpleWhile($sqlDeg72);
        if (is_array($degRows) && count($degRows) >= 8) {
            $firstV = (float)$degRows[0]['avgv'];
            $lastV = (float)$degRows[count($degRows) - 1]['avgv'];
            $firstT = strtotime($degRows[0]['dt']);
            $lastT = strtotime($degRows[count($degRows) - 1]['dt']);
            $hours = ($firstT && $lastT && $lastT > $firstT) ? (($lastT - $firstT) / 3600) : 0;
            if ($hours > 0) {
                $drop = $firstV - $lastV;
                $dropPerDay = ($drop / $hours) * 24;
                if ($drop <= 0.15) {
                    $degradationText = 'Стан АКБ: стабільний';
                    $degradationColor = '#1b8f5a';
                } elseif ($dropPerDay < 0.35) {
                    $degradationText = 'Стан АКБ: легка деградація';
                    $degradationColor = '#d19a00';
                } elseif ($dropPerDay < 0.70) {
                    $degradationText = 'Стан АКБ: помірна деградація';
                    $degradationColor = '#e67e22';
                } else {
                    $degradationText = 'Стан АКБ: критична деградація';
                    $degradationColor = '#d64242';
                }
                $degradationHint = 'Деградація за 72г: ' . ($drop > 0 ? '-' : '+') . round(abs($drop), 2) . 'V, ~' . round(abs($dropPerDay), 2) . 'V/добу';
            }
        }

        $chartId = "chart_" . $row['batteryid'] . "_" . rand(1000, 9999);
        $power = round($b['amper'] * $b['voltage'], 1);
        $result .= '
        <div class="battery-card">
            <div class="battery-left">
                <div class="model-title">
                    <i class="fa-solid fa-car-battery"></i>
                    '.$b['model'].' ('.$b['name'].')
                </div>
                <div class="param">
                    <i class="fi fi-rr-bolt"></i>
                    '.$b['voltage'].' V
                </div>
                <div class="param">
                    <i class="fi fi-rr-car-battery"></i>
                    '.$b['amper'].' Ah
                </div>
                <div class="param">
                    <i class="fi fi-rr-car-battery"></i>
                    '.$power.' W
                </div>
                <div class="param">
                    <i class="fi fi-rr-activity"></i>
                    <span style="color:'.$degradationColor.';font-weight:600;">'.$degradationText.'</span>
                </div>
                '.(!empty($degradationHint) ? '
                <div class="param">
                    <i class="fi fi-rr-chart-line-up"></i>
                    '.$degradationHint.'
                </div>' : '').'
            </div>
            <div class="chart-box" id="'.$chartId.'"></div>
        </div>
        <script>
		(function(){
			const data = '.json_encode($chartData).';
			const container = document.getElementById("'.$chartId.'");
			if (!container) return;
			if (!Array.isArray(data) || data.length === 0) {
				container.innerHTML = "<div style=\"padding:12px;color:#607d8b;font-size:12px;\">Немає даних для графіка</div>";
				return;
			}
			const margin = {top: 0, right: 10, bottom: 10, left: 40},
			width = container.clientWidth - margin.left - margin.right,
			height = container.clientHeight - margin.top - margin.bottom;
			const svg = d3.select(container).append("svg").attr("width", width + margin.left + margin.right).attr("height", height + margin.top + margin.bottom).append("g").attr("transform","translate(" + margin.left + "," + margin.top + ")");
			const x = d3.scaleLinear().domain([0, Math.max(1, data.length-1)]).range([0, width]);
			let minY = d3.min(data, d => d.voltage);
			let maxY = d3.max(data, d => d.voltage);
			if (minY === maxY) {
				minY -= 0.1;
				maxY += 0.1;
			}
			const y = d3.scaleLinear().domain([minY*0.98, maxY*1.02]).range([height, 0]);
			const line = d3.line().x((d,i)=>x(i)).y(d=>y(d.voltage)).curve(d3.curveMonotoneX);
			svg.append("path").datum(data).attr("fill","none").attr("stroke","orange").attr("stroke-width",2).attr("d", line);
			if (data.length >= 2) {
				const n = data.length;
				let sx = 0, sy = 0, sxy = 0, sx2 = 0;
				for (let i = 0; i < n; i++) {
					const xv = i;
					const yv = Number(data[i].voltage);
					sx += xv; sy += yv; sxy += xv*yv; sx2 += xv*xv;
				}
				const div = (n * sx2 - sx * sx);
				const slope = div !== 0 ? ((n * sxy - sx * sy) / div) : 0;
				const intercept = (sy - slope * sx) / n;
				const tStart = intercept;
				const tEnd = intercept + slope * (n - 1);
				svg.append("line")
					.attr("x1", x(0)).attr("y1", y(tStart))
					.attr("x2", x(n - 1)).attr("y2", y(tEnd))
					.attr("stroke", "#d64242")
					.attr("stroke-width", 1.5)
					.attr("stroke-dasharray", "4,3");
			}
			const tooltip = d3.select("body").append("div").attr("class","tooltip").style("position","absolute").style("z-index","9999").style("background","#fff").style("padding","6px 10px").style("border","1px solid #ccc").style("border-radius","4px").style("pointer-events","none").style("display","none").style("font-size","12px");
			svg.selectAll("circle").data(data).enter().append("circle").attr("cx",(d,i)=>x(i)).attr("cy",d=>y(d.voltage)).attr("r",3).attr("fill","#2b71ff")
				.on("mouseover", function(event,d){
					d3.select(this).attr("r",5);
					tooltip.style("display","block")
						.html(
							"<b>Дата:</b> "+d.date+"<br>"+
							"<b>Вольтаж:</b> "+d.voltage+"V<br>")
						.style("left",(event.pageX+5)+"px")
						.style("top",(event.pageY-30)+"px");
				})
				.on("mouseout", function(event,d){
					d3.select(this).attr("r",3);
					tooltip.style("display","none");
				});
			svg.append("g").call(d3.axisLeft(y).ticks(4));
		})();
		</script>';
    }
    $result .= '</div>';
}
			if ($access->get('monitordevice')) {
				$result .= '<br><div class="battery-panel">';
				$result .= '<span class="knopkagreen" onclick="ajaxping3('.$id.',\'edit\')">'.$lang['edit'].'</span>';
				$result .= '<a class="knopkared" href="/?do=ping3&act=clear&deviceid='.$id.'">Очистити вольтажі</a>';
				$result .= '<a class="knopkared" href="/?do=ping3&act=del&deviceid='.$id.'">'.$lang['svalka'].'</a>';
				$result .= '</div>';
			}
			$result .= '</div>';
			$result .= '</div>';
		}else{
			$go->redirect('ping3');
		}
	break;	
	default:
		$sql_work_tasker = $db->Simple("SELECT * FROM mon_ping3 WHERE energy = '1' AND energystatus = 'yes' LIMIT 1");
		$metatags = array('title'=>$lang['mon_list_ping3'],'description'=>$lang['list_ping3'],'page'=>'battery');
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Моніторинг акумуляторів, відбулося оновлення '.aftertime($sql_work_tasker['poller']).' тому <a href="/?do=ping3&act=stats">Статистика</a></span>';		
		$result = '<script>status_ping3();</script>
		<div id="status_ping3"></div>';
	
}
$tpl->load_template('battery/page.tpl');
$tpl->set('{speedbar}',$speedbar);
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
?>
