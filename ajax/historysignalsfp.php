<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');
require_once ENGINE_DIR.'ajax.php';
if(isset($_POST['id'])){
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$switch_port = $db->Fast('switch_port','*',['id' => $id]);
if(!empty($switch_port['id'])){
$history_signal = $db->SimpleWhile('SELECT * FROM `signal_sfp` WHERE sfpid = '.$switch_port['id'].' ORDER BY datetime ASC');
$js_array_full = '';
$js_array_full_olt = '';
if (isset($history_signal) && count($history_signal)>0) {
	if (!empty($history_signal)) {
		foreach ($history_signal as $arr) {
			$timestamp = strtotime($arr['datetime']) * 1000;
			
			if (!empty($arr['rx'])) {
				$js_array_full .= '{ "date": ' . $timestamp . ', "value": ' . $arr['rx'] . ' },';
			}
			if (!empty($arr['tx'])) {
				$js_array_full_olt .= '{ "date": ' . $timestamp . ', "value": ' . $arr['tx'] . ' },';
			}
		}
	}
echo '<style>#chartdiv {width: 100%; height: 250px; max-width: 100%;}</style>
<script>
    am5.ready(function() {
        var root = am5.Root.new("chartdiv");
        var data1 = [' . $js_array_full . ']; 
        var data2 = [' . $js_array_full_olt . '];
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
            name: "RX",
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
            name: "TX",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "value",
            valueXField: "date",
            stroke: "orange",
            tooltip: am5.Tooltip.new(root, {
                labelText: "{valueY}"
            })
        }));
        series2.data.setAll(data2);
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