<?php
if (!defined('PONMONITOR')){
    die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if (!$id) {
    $go->redirect('main');
}
$dataONT = $db->Fast('onus','*',['idonu'=>$id]);
if (!$dataONT['idonu']) {
    $go->redirect('main');
}
$dataOLT = $db->Fast('switch','*',['id'=>$dataONT['olt']]);
if (!$dataOLT['id']) {
    $go->redirect('main');
}
$metatags = array('title'=>$dataONT['inface'].' '.$lang['global_signal'],'description'=>$lang['global_signal'],'page'=>'global_signal');

$tpl->load_template('onu/mainsignal.tpl');
$tpl->set('{id}', $id);
$tpl->set('{olt_id}', $dataOLT['id']);
$tpl->set('{olt_place}', $dataOLT['place']);
$tpl->set('{type_ont}', trim($dataONT['type']));
$tpl->set('{inface}', $dataONT['inface']);
$tpl->set('{inface_ont}', mb_strtoupper($dataONT['type'].' '.$dataONT['inface']));
$tpl->set('{olt_port_ont}', mb_strtoupper($dataONT['type'].' '.cl_inface($dataONT['inface'])));
$dataONTPort = $db->Fast('switch_pon','*',['sfpid'=>$dataONT['portolt'],'oltid'=>$dataONT['olt']]);
$tpl->set('{port_id}', $dataONTPort['id'] ?? '');
$mac_sn = '';
if (!empty($dataONT['mac'])) {
    $mac_sn .= '<span class="n">MAC</span><span class="m">'.$dataONT['mac'].'</span>';
}
if (!empty($dataONT['sn'])) {
    $mac_sn .= '<span class="n">SN</span><span class="m">'.$dataONT['sn'].'</span>';
}
$tpl->set('{number_ont}', $mac_sn);
$rowsOnu = $db->SimpleWhile('SELECT * FROM historysignal WHERE onu='.$id.' ORDER BY datetime ASC');
$chartOnu = [];
if ($rowsOnu) {
    foreach ($rowsOnu as $r) {
        $chartOnu[] = [
            'x' => strtotime($r['datetime'].' +2 hour') * 1000,'y' => (float)$r['signal']
        ];
    }
}
$rowsRxOlt = $db->SimpleWhile('SELECT * FROM rxolt_signal WHERE onu='.$id.' ORDER BY datetime ASC');
$chartRxOlt = [];
if ($rowsRxOlt) {
    foreach ($rowsRxOlt as $r) {
        $chartRxOlt[] = [
            'x' => strtotime($r['datetime'].' +2 hour') * 1000,'y' => (float)$r['signal']
        ];
    }
}

$jsonOnu   = json_encode($chartOnu, JSON_UNESCAPED_UNICODE);
$jsonRxOlt = json_encode($chartRxOlt, JSON_UNESCAPED_UNICODE);
$graph = <<<HTML
<div class="logsignalcss">
    <canvas id="signalChart"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
<script src="https://cdn.jsdelivr.net/npm/luxon@3"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1"></script>
<script>
const dataOnu   = $jsonOnu;
const dataRxOlt = $jsonRxOlt;
const ctx = document.getElementById('signalChart');
new Chart(ctx, {
    type: 'line',
    data: {
        datasets: [
            {
                label: 'ONU RX Power',
                data: dataOnu,
                borderColor: '#1f77b4', // синій
                borderWidth: 1,
                stepped: true,
                pointRadius: 0,
                spanGaps: false
            },
            {
                label: 'OLT RX Power',
                data: dataRxOlt,
                borderColor: '#d9534f', // червоний
                borderWidth: 1,
                stepped: true,
                pointRadius: 0,
                spanGaps: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
		interaction: {
			mode: 'nearest',
			axis: 'x',
			intersect: false
		},
        plugins: {
			legend: {
				display: true,
				position: 'top'
			},
			tooltip: {
				mode: 'nearest',
				intersect: false,
				callbacks: {
					title: items => {
						// беремо реальний час точки
						const ts = items[0].parsed.x;
						return luxon.DateTime.fromMillis(ts).toFormat('yyyy-MM-dd HH:mm');
					},
					label: ctx => {
						return ctx.dataset.label + ': ' + ctx.parsed.y + ' dBm';
					}
				}
			}
		},
        scales: {
            x: {
                type: 'time',
                time: {
                    tooltipFormat: 'yyyy-MM-dd HH:mm'
                },
                grid: {
                    color: '#ededed'
                }
            },
            y: {
                min: -35,
                max: -5,
                title: {
                    display: true,
                    text: 'Signal (dBm)'
                },
                grid: {
                    color: '#ededed'
                }
            }
        }
    }
});
</script>
<style>
.logsignalcss {
    height: 380px;
    background: #ffffff;
    border: 1px solid #cfcfcf;
	margin-top: 10px;
}
</style>
HTML;
$tpl->set('{jsgraph}', $graph);
$tpl->compile('content');
$tpl->clear();
?>
