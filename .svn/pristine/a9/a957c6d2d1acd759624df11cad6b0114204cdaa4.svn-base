<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');
require_once ENGINE_DIR.'ajax.php';
if(isset($_POST['id'])){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$getONT = $db->Fast('onus','*',['idonu' => $id]);
if(!empty($getONT['idonu'])){
$sql_signal_rx_onu = $db->SimpleWhile('SELECT * FROM `historysignal` WHERE onu = '.$getONT['idonu'].' ORDER BY datetime ASC');
$sql_signal_rx_onu_olt = $db->SimpleWhile('SELECT * FROM `rxolt_signal` WHERE onu = '.$getONT['idonu'].' ORDER BY datetime ASC');
$js_array_full = '';
$js_array_full_olt = '';
if (isset($sql_signal_rx_onu) && count($sql_signal_rx_onu)>0) {
    foreach ($sql_signal_rx_onu as $arr) {
		if(!empty($arr['signal'])){
			$js_array_full .= '{ "date": '.(strtotime($arr['datetime']) * 1000).', "value": '.$arr['signal'].' },';
		}
    }
	if (isset($sql_signal_rx_onu_olt) && count($sql_signal_rx_onu_olt) > 0) {
		foreach ($sql_signal_rx_onu_olt as $res) {
			if (!empty($res['signal'])) {
				$js_array_full_olt .= '{ "date": ' . (strtotime($res['datetime']) * 1000) . ', "value": ' . $res['signal'] . ' },';
			}
		}
	}
echo '<style>#chartdiv {width: 100%; height: 250px; max-width: 100%;}</style>
<script>
    am5.ready(function() {
        var root = am5.Root.new("chartdiv");

        var data1 = [' . $js_array_full . ']; // Data for first series
        var data2 = [' . $js_array_full_olt . ']; // Data for second series

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        var chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: true,
            panY: true,
            wheelX: "panX",
            wheelY: "zoomX",
            pinchZoomX: true
        }));

        var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
            behavior: "none"
        }));
        cursor.lineY.set("visible", false);

        var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
            maxDeviation: 0.2,
            baseInterval: {
                timeUnit: "hour",
                count: 1
            },
            renderer: am5xy.AxisRendererX.new(root, {}),
            tooltip: am5.Tooltip.new(root, {})
        }));

        var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            renderer: am5xy.AxisRendererY.new(root, {})
        }));

        var series1 = chart.series.push(am5xy.LineSeries.new(root, {
            name: "Перший графік",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "value",
			stroke: "#0e7cc7",
            valueXField: "date",
            tooltip: am5.Tooltip.new(root, {
                labelText: "{valueY}"
            })
        }));

        series1.data.setAll(data1);
        series1.appear(1000);

        var series2 = chart.series.push(am5xy.LineSeries.new(root, {
            name: "Другий графік",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "value",
            valueXField: "date",
            stroke: "orange",
            tooltip: am5.Tooltip.new(root, {
                labelText: "{valueY}"
            })
        }));

        series2.data.setAll(data2); // Ensure data is correctly populated
        series2.appear(1000);

        chart.appear(1000, 100);
    });
</script>
<div class="onu-line-chart"><div id="chartdiv"></div></div>';


}else{

}	
}
}
?>