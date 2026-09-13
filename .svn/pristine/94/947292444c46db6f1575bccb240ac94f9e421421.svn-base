<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
// SQL-запит для LOS
	$query_los = "WITH RECURSIVE hours AS (
		SELECT 0 AS hour
		UNION ALL	SELECT hour + 1 FROM hours WHERE hour < 23
	)
	SELECT h.hour, COALESCE(COUNT(o.idonu), 0) AS count
	FROM hours h
	LEFT JOIN onus o ON HOUR(o.offline) = h.hour 
		AND o.status = 2
		AND (o.reason = 'err8' OR o.reason = 'err6')
		AND o.offline >= CURDATE()
	GROUP BY h.hour
	ORDER BY h.hour"; 
	$chart_data_los = $db->SimpleWhile($query_los);  			
	$query_bad_signal = "WITH RECURSIVE hours AS (
	SELECT 0 AS hour
		UNION ALL SELECT hour + 1 FROM hours WHERE hour < 23
	)
	SELECT h.hour, COALESCE(COUNT(o.idonu), 0) AS count
		FROM hours h
		LEFT JOIN onus o ON HOUR(o.online) = h.hour 
		AND o.status = 1
		AND (o.rxstatus='up')
		AND o.online >= CURDATE()
	GROUP BY h.hour
	ORDER BY h.hour";
	$chart_data_bad = $db->SimpleWhile($query_bad_signal);  
	// SQL-запит для OFF
	$query_off = "WITH RECURSIVE hours AS (
	SELECT 0 AS hour
		UNION ALL	SELECT hour + 1 FROM hours WHERE hour < 23
	)
	SELECT h.hour, COALESCE(COUNT(o.idonu), 0) AS count
		FROM hours h
		LEFT JOIN onus o ON HOUR(o.offline) = h.hour 
		AND o.status = 2
		AND o.offline >= CURDATE()
		GROUP BY h.hour
	ORDER BY h.hour";
	$chart_data_off = $db->SimpleWhile($query_off);
	// Обробка даних для LOS
	$full_day_data_los = array_fill(0, 24, ['hour' => 0, 'count' => 0]);
	foreach ($chart_data_los as $data) {
		$full_day_data_los[$data['hour']] = ['hour' => $data['hour'], 'count' => (int)$data['count']];
	}
	$max_value_los = max(array_column($full_day_data_los, 'count')) + 10;  
	$json_data_los = json_encode($full_day_data_los);
	// Обробка даних для BAD
	$full_day_data_bad = array_fill(0, 24, ['hour' => 0, 'count' => 0]);
	foreach ($chart_data_bad as $data) {
		$full_day_data_bad[$data['hour']] = ['hour' => $data['hour'], 'count' => (int)$data['count']];
	}
	$max_value_bad = max(array_column($full_day_data_bad, 'count')) + 10;  
	$json_data_bad = json_encode($full_day_data_bad);
	// Обробка даних для OFF
	$full_day_data_off = array_fill(0, 24, ['hour' => 0, 'count' => 0]);
	foreach ($chart_data_off as $data) {
		$full_day_data_off[$data['hour']] = ['hour' => $data['hour'], 'count' => (int)$data['count']];
	}
	$max_value_off = max(array_column($full_day_data_off, 'count')) + 10;  
	$json_data_off = json_encode($full_day_data_off);
	/**/
$tplresult = '
<script>
setInterval(function() {
	location.reload();
}, 300000);
</script>
<div id="view_block_onu">
    <div id="graph_onu_los" class="graph-container"></div>
    <div id="graph_onu_off" class="graph-container"></div>
    <div id="graph_onu_signal" class="graph-container"></div>
</div>   
<div id="ajax_result_signal"></div>    
<script>
	const losData = ' . $json_data_los . ';
	const offData = ' . $json_data_off . ';
	const signalData = ' . $json_data_bad . ';        
	createChart("graph_onu_los", losData, ' . $max_value_los . ',"red", "LOS");
	createChart("graph_onu_off", offData, ' . $max_value_off . ',"#00699d", "Power off");
	createChart("graph_onu_signal", signalData, ' . $max_value_bad . ',"#6dbb24", "Changes in signals");
</script>';
$navigation = '
<div id="onu-speedbar">
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>ONU Status</span>
</div>';
$metatags = array(
	'title'=>'ONU Status Chart',
	'description'=>'ONU Status Chart',
	'page'=>'onuevents');
	
$result ='<div id="onu-speedbar">'.$navigation.'</div>
		<div style="margin: 0;">
		<div class="page-error">'.$tplresult.'
	</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$result);
$tpl->compile('content');
$tpl->clear();
?>