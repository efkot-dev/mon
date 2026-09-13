    <style>
        #chartdiv {
            width: 100%;
            height: 250px;
            max-width: 100%;
        }
    </style>
 <script>
        am5.ready(function() {
            var root = am5.Root.new("chartdiv");
            var data = [
				{js_array_full}
            ]; 
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
            var series = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Серія",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "value",
                valueXField: "date",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{valueY}"
                })
            }));

            series.data.setAll(data);
            series.appear(1000);
            chart.appear(1000, 100);
        });
    </script>
<div class="onu-line-chart">
<div id="chartdiv"></div>
</div>
