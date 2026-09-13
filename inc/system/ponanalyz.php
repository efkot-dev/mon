<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$navigation = '
<div id="onu-speedbar">
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Детальний аналіз PON портів</span>
</div>';
$metatags = array('title'=>'Детальний аналіз PON портів','description'=>'Детальний аналіз PON портів','page'=>'current');
switch($act) {
	case 'find_problem_ports':
		$select_countday  = (isset($_POST['countday']) && !empty($_POST['countday']) ? intval($_POST['countday']) : 7);
		$sw = $db->SimpleWhile("SELECT s.* FROM checkaccess a JOIN switch s ON CONCAT('dev', s.id) = a.types WHERE a.uid = '{$USER['id']}' AND s.device = 'olt' AND s.monitor = 'yes'");
		$list_switch = [];
		$select_ids = [];
		foreach ($sw as $s) {
			$select_ids[] = $s['id'];
			$list_switch[$s['id']]['place'] = $s['place'];
		}	
		$ids = implode(',', $select_ids);
		$swport = $db->SimpleWhile("SELECT id, oltid, pon, sfpid FROM switch_pon WHERE oltid IN ({$ids})");
		$list_switch_port = [];
		foreach ($swport as $pon) {
			$list_switch_port[$pon['oltid']][$pon['sfpid']] = ['id' => $pon['id'], 'name' => $pon['pon']];
		}
		$threshold_db  = 1.5;
		$sql = "SELECT o.olt, o.portolt, COUNT(*) AS bad_onu_count 
		FROM onus AS o JOIN (SELECT onu, MAX(`signal`) AS maxsig, MIN(`signal`) AS minsig 
		FROM historysignal 
		WHERE datetime >= NOW() - INTERVAL {$select_countday} DAY 
		AND `signal` BETWEEN -40 AND -15 
		GROUP BY onu HAVING ROUND(minsig - maxsig, 2) > {$threshold_db} ) 
		AS agg ON agg.onu = o.idonu WHERE o.status = '1' 
		AND o.olt IN ({$ids}) GROUP BY o.olt, o.portolt ORDER BY bad_onu_count DESC ";
		$rows = $db->SimpleWhile($sql);
		$ports_to_check = [];
		foreach ($rows as $r) {
			if ($r['bad_onu_count'] >= 10) {
				$ports_to_check[$r['olt']][] = $r['portolt'];
			}
		}
		echo "<br><div class='problem-port-list'>";
		if (empty($ports_to_check)) {
			echo "<p>Проблемних портів не знайдено.</p>";
		} else {
			echo "<div class='menu_olt_left'>";
			foreach ($ports_to_check as $olt => $ports) {
				$place = $list_switch[$olt]['place'];
				echo "<span><strong>{$place}</strong>: ";
				echo "<div class='list_pon_pon_any'>";
				foreach ($ports as $pon) {
					if (!empty($list_switch_port[$olt][$pon]['id'])) {
						$name = $list_switch_port[$olt][$pon]['name'];
						echo "<a href='#' class='problem-port-link bad_name_onu' data-olt='{$olt}' data-portolt='{$pon}'>{$name}</a> ";
					}
				}
				echo "</div>";
				echo "</span>";
			}
			echo "</div>";
		}
		echo "</div>";
		die;
	break;
	case 'port':
		$olt     = intval($_POST['olt']);
		$select_countday  = (isset($_POST['countday']) && !empty($_POST['countday']) ? intval($_POST['countday']) : 7);
		$step  = (isset($_POST['step']) && !empty($_POST['step']) ? intval($_POST['step']) : 150);
		$portolt = intval($_POST['portolt']);
		if($olt && $portolt){
		$sql = "
			SELECT 
				o.idonu,
				o.inface,
				o.dist,
				COALESCE(o.mac, o.sn) AS onukey,
				od.pontree AS pontree_id,
				od.ponelement AS ponelem_id,
				pe.name AS ponname,
				pt.name AS treename
			FROM onus AS o
			LEFT JOIN onusdata AS od ON od.onukey = COALESCE(o.mac, o.sn)
			LEFT JOIN ponelement AS pe ON pe.id = od.ponelement
			LEFT JOIN pontree AS pt ON pt.id = od.pontree
			WHERE o.olt = '{$olt}' AND o.portolt = '{$portolt}' AND o.status = '1'
		";
		$meta = $db->SimpleWhile($sql);
		$onu_ids = [];
		$temp_inface = [];
		foreach ($meta as $r) {
			$onu_ids[] = $r['idonu'];
			$temp_inface[$r['idonu']] = [
				'idonu' => $r['idonu'],'dist' => $r['dist'],
				'inface' => $r['inface'],
				'mac' => $r['onukey'],
				'ponelem_id' => $r['ponelem_id'] ?? '',
				'ponname' => $r['ponname'] ?? '',
				'treename' => $r['treename'] ?? ''
			];
		}
		if (empty($onu_ids)) {
			exit('<pre>Немає активних ONU для цього порту.</pre>');
		}
		$ids = implode(',', $onu_ids);
		$signals = $db->SimpleWhile("SELECT onu, datetime, `signal` FROM historysignal WHERE onu IN ({$ids}) AND datetime >= NOW() - INTERVAL {$select_countday} DAY ORDER BY onu, datetime ASC ");
		$signal_history = [];
		foreach ($signals as $row) {
			$sig = floatval($row['signal']);
			if ($sig > -40 && $sig < -15) {
				$signal_history[$row['onu']][] = ['datetime' => $row['datetime'],'signal' => $sig];
			}
		}
		$worsening_report = [];
		$dist_worsening = [];
		$pon_tree = [];
		$pon_tree_global = [];
		foreach ($signal_history as $onu_id => $entries) {
			$start_index = 0;
			$trend_length = 1;
			for ($i = 1, $count = count($entries); $i < $count; $i++) {
				$delta = 2;
				$current_signal = $entries[$i]['signal'];
				$prev_signal = $entries[$i - 1]['signal'];
				if ($current_signal <= $prev_signal + $delta) {
					$trend_length++;
					$first_signal = $entries[$start_index]['signal'];
					$diff = $current_signal - $first_signal;
					if ($diff <= -0.4) {
						$tmp = $temp_inface[$onu_id];
						$seq = array_slice($entries, $start_index, $trend_length);
						$dist_round = round($tmp['dist'] / $step) * $step;
						if (!isset($dist_worsening[$dist_round])) {
							$dist_worsening[$dist_round] = [];
						}
						$dist_worsening[$dist_round][] = abs($diff);
						$worsening_report[$onu_id] = [
							'idonu' => $tmp['idonu'],
							'mac' => $tmp['mac'],
							'inface' => $tmp['inface'],
							'dist' => $tmp['dist'],
							'ponname' => $tmp['ponname'],
							'treename' => $tmp['treename'],
							'trend' => $seq,
							'delta_db' => round($diff, 4),
							'description' => "Погіршення на " . round($diff, 4) . " dB"
						];
						if (!empty($tmp['ponelem_id'])) {
							$pon_tree_global[$tmp['ponelem_id']]['name'] = $tmp['ponname'];
							if (!isset($pon_tree[$tmp['ponelem_id']])) {
								$pon_tree[$tmp['ponelem_id']] = 0;
							}
							$pon_tree[$tmp['ponelem_id']]++;
						}
						break;
					}
				} else {
					$start_index = $i;
					$trend_length = 1;
				}
			}
		}

		if (empty($worsening_report)) {
			echo "Зміни відбулися в межах норми, очікуємо погіршення.\n";
		} else {
			ksort($dist_worsening);
			$chart_data = [];
			foreach ($dist_worsening as $dist => $values) {
				$avg_loss = round(array_sum($values) / count($values), 3);
				$chart_data[] = [
					'x' => $dist,'y' => $avg_loss,'count' => count($values)
				];
			}
			$json_chart_data = json_encode($chart_data);
			$uniqueId = 'olt_' . $olt;
			echo "<div id='d3chart_{$uniqueId}' style='width: 100%; height: 300px;'></div>";
echo "<script>
(function() {
    const data = {$json_chart_data};
    const margin = {top: 20, right: 0, bottom: 40, left: 50};
    const width = 1000 - margin.left - margin.right;
    const height = 290 - margin.top - margin.bottom;
    const svg = d3.select('#d3chart_{$uniqueId}')
        .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

    const x = d3.scaleLinear()
        .domain([d3.min(data, d => d.x) - 100, d3.max(data, d => d.x) + 100])
        .range([0, width]);

    const y = d3.scaleLinear()
        .domain([0, d3.max(data, d => d.y) + 1])
        .range([height, 0]);

    svg.append('g')
        .attr('transform', 'translate(0,' + height + ')')
        .call(d3.axisBottom(x));

    svg.append('g')
        .call(d3.axisLeft(y));

    svg.append('text')
        .attr('x', width / 2)
        .attr('y', height + margin.bottom - 5)
        .attr('text-anchor', 'middle')
        .text('Довжина волокна (м)');

    svg.append('text')
        .attr('transform', 'rotate(-90)')
        .attr('x', -height / 2)
        .attr('y', -40)
        .attr('text-anchor', 'middle')
        .text('Втрати сигналу (dB)');

    const tooltip = d3.select('#d3chart_{$uniqueId}')
        .append('div')
        .style('position', 'absolute')
        .style('background', '#f8f8f8')
        .style('padding', '6px')
        .style('border', '1px solid #222')
        .style('border-radius', '4px')
        .style('pointer-events', 'none')
        .style('opacity', 0);

    svg.selectAll('circle')
    .data(data)
    .enter()
    .append('circle')
    .attr('cx', d => x(d.x))
    .attr('cy', d => y(d.y))
    .attr('r', d => d.count > 3 ? 5 : 3) // більший радіус, якщо більше 3
    .attr('fill', d => d.count > 3 ? 'rgba(255, 0, 0, 0.8)' : 'rgba(255, 99, 132, 0.7)') // червоний якщо більше 3
    .on('mouseover', function(event, d) {
        tooltip.transition().duration(200).style('opacity', .9);
        tooltip.html('Довжина: ' + d.x + ' м<br>Втрати: ' + d.y + ' dB<br>Вимірювань: ' + d.count)
            .style('left', (event.pageX + 10) + 'px')
            .style('top', (event.pageY - 28) + 'px');
    })
    .on('mouseout', function() {
        tooltip.transition().duration(500).style('opacity', 0);
    });

})();
</script>";
	if(isset($dist_worsening)){
		echo'<div class="block_analyzer_dist">';
		foreach ($dist_worsening as $dist => $values) {
			$avg_loss = array_sum($values) / count($values);
			$avg_loss = round($avg_loss, 3);
			if($avg_loss >= 0.1 && $avg_loss <= 0.9){
				$class="b_blue";
			}elseif($avg_loss >= 1 && $avg_loss <= 1.9){
				$class = "b_orange";
			}elseif($avg_loss >= 2){
				$class="b_red";
			}
			echo "
			<div class='block_dist_pa {$class}'>
			<div class='dist_pa'>{$dist} м</div>
			<div class='rx_pa'>-{$avg_loss} dB</div>
			</div>
			";
		}	
		echo'</div>';
	}else{
		echo'дані вісутні';
	}
	/*
echo'<div class="pmon_block" id="board_fault"><div class="pmon_block_left pre50 pon_analyzer_dist">';

	echo'</div><div class="pmon_block_right pre50 pon_any_flex">';
	if(isset($pon_tree_global)){
		foreach ($pon_tree_global as $idpon => $box_name) {
			echo '<div class="count_usr">'.$box_name['name'].'<span '.($pon_tree[$idpon]>3 ? 'class="b_red"': '').'>'.$pon_tree[$idpon].'</span></div>';
		}
	}
	echo'</div></div>';
	*/
	usort($worsening_report, function($a, $b) {
		return $a['dist'] <=> $b['dist'];
	});
	echo'<table id="board_fault">
        <thead>
            <tr>
			    <th width="10%"><center>Зміна dBm</center></th>
                <th width="7%"><center>Волокно</center></th>
                <th width="7%"><center>Inface</center></th>
                <th width="15%" class="text_center">MAC_SN</th>
                <th>PON бокс</th>
            </tr>
        </thead>
        <tbody>';
		foreach ($worsening_report as $onu) {
			echo'<tr>
				<td><center>'.signalTerminalPonAnalyzer($onu['delta_db']).' </center></td>
				<td><center>'.$onu['dist'].'</center></td>
				<td><center>'.$onu['inface'].'</center></td>
				<td class="td_url"><a href="/?do=onu&id='.$onu['idonu'].'">'.$onu['mac'].' <img class="links_" src="../style/img/chevrons.png"></a></td>
				<td>';
				if ($onu['treename'] || $onu['ponname']) {
					echo "{$onu['treename']}, {$onu['ponname']}\n";
				}
				echo'</td>				
			</tr>';
		}
		echo'</tbody></table>';
			
		}
		die;
	break;
}
}
$result = "
<div id='onu-speedbar'>{$navigation}</div>
<div class='pmon_block' id='board_fault'>
	<div class='pmon_block_left pre20'>
	Кількість днів <input style='width: 80px;' id='countday' name='countday' class='input1' type='number' min='1' max='30' step='1' value='7' required oninput=\"validity.valid||(value='');\"/>
	Середній метраж включення <input style='width: 80px;' id='step' name='step' class='input1' type='number' min='10' max='1000' step='10' value='150' required oninput=\"validity.valid||(value='');\"/>
	<div class='load_menu_list'>Завантаження...</div>
	</div>
	<div class='pmon_block_right pre80'></div>
</div>
<script>
$(function() {
	function loadProblemPorts(days) {
		$.post('/?do=ponanalyz&act=find_problem_ports', { countday: days}, function(data) {
			$('#board_fault .load_menu_list').html(data);
			$('#board_fault .pmon_block_right').empty();
		}).fail(function() {
			$('#board_fault .load_menu_list').html('<pre>Помилка завантаження</pre>');
		});
	}
	loadProblemPorts(7);
	$('#countday').on('input change', function() {
		let val = parseInt($(this).val());
		if (!val || val < 1) val = 1;
		if (val > 30) val = 30;
		$(this).val(val);
		loadProblemPorts(val);
	});
	$('#board_fault').on('click', 'a.problem-port-link', function(e) {
		e.preventDefault();
		const olt = $(this).data('olt');
		const portolt = $(this).data('portolt');
		const countday = parseInt($('#countday').val()) || 7;
		const step = parseInt($('#step').val()) || 150;
		$('#board_fault .pmon_block_right').html('<pre>Завантаження...</pre>');
		$.post('/?do=ponanalyz&act=port', { step: step, olt: olt, portolt: portolt, countday: countday }, function(response) {
			$('#board_fault .pmon_block_right').html(response);
		}).fail(function() {
			$('#board_fault .pmon_block_right').html('<pre>Помилка завантаження</pre>');
		});
	});	
});
</script>";
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', $result);
$tpl->compile('content');
$tpl->clear();
?>
