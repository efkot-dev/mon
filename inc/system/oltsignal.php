<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$portid = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$types = isset($_GET['types']) ? Clean::int($_GET['types']) : null;
if(is_valid_id($portid)){
if(isset($types) && $types==1){
$tplresult = '<div id="chart" style="width:100%;height:420px;padding:5px;margin-bottom:10px;"></div><div id="loadingBar" style="display: none; text-align: center;"><img src="../style/img/load_clock_pmon.gif"></div>
	<script>
		document.getElementById("loadingBar").style.display = "block";
		document.getElementById("chart").innerHTML = "";
		fetch("/?do=core&act=treesignal&tree='.$portid.'")
			.then(response => response.json())
			.then(data => {
				document.getElementById("loadingBar").style.display = "none";
				renderChart(data);
			});
	</script>';
	echo$tplresult;
	exit;	
}else{
	$pon = $db->Simple("SELECT * FROM switch_pon WHERE id = '{$portid}'");
	$switch = $db->Simple("SELECT id,place FROM switch WHERE id = '{$pon['oltid']}'");
	$tplresult = '<div id="chart" style="width: 100%; height: 400px;"></div><div id="loadingBar" style="display: none; text-align: center;"><img src="../style/img/load_clock_pmon.gif"></div>
	<script>
		document.getElementById("loadingBar").style.display = "block";
		document.getElementById("chart").innerHTML = "";
		fetch("/?do=core&act=ponsignal&olt='.$pon['oltid'].'&portolt='.$pon['sfpid'].'")
			.then(response => response.json())
			.then(data => {
				document.getElementById("loadingBar").style.display = "none";
				renderChartPon(data);
			});
	</script>';
$current_date = date('Y-m-d');
$last_month_date = date('Y-m-d', strtotime('-1 month'));
$table = '';

$table .= '<table class="table_diagnostic"><tr><td>';
	$sql = "SELECT * FROM onus WHERE olt = '{$pon['oltid']}' AND portolt = '{$pon['sfpid']}' ORDER by rx ASC";
	$list_ont = $db->SimpleWhile($sql);
	if (isset($list_ont) && count($list_ont) > 0) {
		$table .= '<table class="pon_diagnostic"><thead class="blue"><tr><th>Status</th>
		<th>Sn_Mac</th><th>Inface</th><th>Rx Olt</th><th>Dist</th><th>Last Rx Onu</th></tr></thead><tbody>';
		foreach ($list_ont as $onu) {
			$table .= '<tr id="onu-row-' . $onu['idonu'] . '" data-onu-id="' . $onu['idonu'] . '" class="pon_onu">';
			$table .= '<td><span class="statusonu st_'.$onu['status'].'"</span></td>';
			$table .= '<td>' . $onu['mac'] . '</td>';
			$table .= '<td>' . $onu['inface'] . '</td>';
			$table .= '<td>'.(!empty($onu['rxolt'])?signalTerminal($onu['rxolt']):'N/A').'</td>';
			$table .= '<td>'.($onu['dist'] ? metersToKilometers($onu['dist']) : '').'</td>';
			$table .= '<td>'.($onu['status']==2?'N/A':signalTerminal($onu['lastrx'])).'</td>';
			$table .= '</tr>';
		}
		$table .= '</tbody></table>';
	}
$table .= '</td><td>';
	// BAD SIGNAL
	$sql = "SELECT o.idonu, o.mac, o.sn, o.olt, o.portolt, o.inface, COUNT(h.id) AS bad_signal_count FROM onus o LEFT JOIN rxolt_signal h ON o.idonu = h.onu AND h.signal < -24 AND h.datetime >= '{$last_month_date}' WHERE o.olt = '{$pon['oltid']}' AND o.portolt = '{$pon['sfpid']}' GROUP BY o.idonu HAVING bad_signal_count > 0 ORDER BY bad_signal_count DESC";
	$bad_signals = $db->SimpleWhile($sql);
	if (isset($bad_signals) && count($bad_signals) > 0) {
		$total_bad_signals = array_sum(array_column($bad_signals, 'bad_signal_count'));
		$table .= '<table class="pon_diagnostic"><thead class="black"><tr><th>ONU</th><th>Sn_Mac</th><th>Inface</th><th>Count bad</th><th>Progress</th><th>%</th></tr></thead><tbody>';
		foreach ($bad_signals as $onu) {
			$percent = ($total_bad_signals > 0) ? round(($onu['bad_signal_count'] / $total_bad_signals) * 100, 2) : 0;
			$progress_bar = '<div style="width: 100%; background-color: #e0e0e0; border-radius: 5px; height: 20px;">';
			$progress_bar .= '<div style="width: ' . $percent . '%; background-color: #76c7c0; height: 100%; border-radius: 5px;"></div>';
			$progress_bar .= '</div>';
			$table .= '<tr id="onu-row-' . $onu['idonu'] . '" data-onu-id="' . $onu['idonu'] . '" class="pon_onu">';
			$table .= '<td>' . $onu['idonu'] . '</td>';
			$table .= '<td>' . $onu['mac'] . '</td>';
			$table .= '<td>' . $onu['inface'] . '</td>';
			$table .= '<td>' . $onu['bad_signal_count'] . '</td>';
			$table .= '<td>' . $progress_bar . '</td>';
			$table .= '<td>' . $percent . '%</td>';
			$table .= '</tr>';
		}
		$table .= '</tbody></table>';
	}
	$table .= '</td></tr></table>';
}
$tplresult .= $table;
$navigation = '
<div id="onu-speedbar">
	<a class="brmhref" href="/?do=pon"><i class="fi fi-rr-apps"></i>'.$lang['alldevice'].'</a>
	<a class="brmhref" href="/?do=detail&act=olt&id='.$switch['id'].'"><i class="fi fi-rr-angle-left"></i>'.$switch['place'].'</a>
	<a class="brmhref" href="/?do=terminal&id='.$switch['id'].'&port='.$pon['id'].'"><i class="fi fi-rr-angle-left"></i>'.$pon['pon'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>PON Signal Diagnostics Rx Olt</span>
</div>';
$metatags = array('title'=>''.$pon['pon'].' Signal Diagnostics','description'=>''.$pon['pon'].' Signal Diagnostics','page'=>'ponsignal');
$result ='<div id="onu-speedbar">'.$navigation.'</div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$result);
$tpl->compile('content');
$tpl->clear();
}
?>