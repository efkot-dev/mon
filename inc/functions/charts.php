<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
function get_weather_hourly_open_meteo($lat = 50.45, $lon = 30.52) {
    $url = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&hourly=temperature_2m,weathercode&forecast_days=1&timezone=Europe/Kiev";
    $response = @file_get_contents($url);
    if (!$response) return "Немає даних про погоду";
    $data = json_decode($response, true);
    if (!$data || !isset($data['hourly']['time'])) {
        return "Помилка отримання прогнозу";
    }
    $times = $data['hourly']['time'];
    $temps = $data['hourly']['temperature_2m'];
    $codes = $data['hourly']['weathercode'];
    $weatherDescriptions = [
        0 => "Ясно",
        1 => "Переважно ясно",
        2 => "Мало хмар",
        3 => "Похмуро",
        45 => "Туман",
        48 => "Туман з дрібним снігом",
        51 => "Дрібний дощ",
        53 => "Помірний дощ",
        55 => "Сильний дрібний дощ",
        61 => "Дрібний дощ",
        63 => "Помірний дощ",
        65 => "Сильний дощ",
        71 => "Дрібний сніг",
        73 => "Помірний сніг",
        75 => "Сильний сніг",
        80 => "Зливовий дощ",
        81 => "Сильний зливовий дощ",
        82 => "Інтенсивний зливовий дощ",
    ];
	$weatherIcons = [
		0  => "01d@2x.png", // Ясно (сонце)
		1  => "02d@2x.png", // Переважно ясно (сонце з хмаркою)
		2  => "03d@2x.png", // Мало хмар (кілька хмар)
		3  => "04d@2x.png", // Похмуро (багато хмар)
		45 => "50d@2x.png", // Туман
		48 => "50d@2x.png", // Туман з дрібним снігом (туман)
		51 => "09d@2x.png", // Дрібний дощ
		53 => "09d@2x.png", // Помірний дощ
		55 => "10d@2x.png", // Сильний дрібний дощ
		61 => "09d@2x.png", // Дрібний дощ
		63 => "10d@2x.png", // Помірний дощ
		65 => "10d@2x.png", // Сильний дощ
		71 => "13d@2x.png", // Дрібний сніг
		73 => "13d@2x.png", // Помірний сніг
		75 => "13d@2x.png", // Сильний сніг
		80 => "09d@2x.png", // Зливовий дощ
		81 => "09d@2x.png", // Сильний зливовий дощ
		82 => "11d@2x.png", // Інтенсивний зливовий дощ (гроза/злива)
	];
    $html = '<div class="weather-hourly" style="width:99%;">';
    $limit = min(24, count($times));
	$now = new DateTime();
    for ($i = 0; $i < $limit; $i++) {
        $blockTime = new DateTime($times[$i]);
        $timeLabel = $blockTime->format('H:i');
        $temp = round($temps[$i], 1);
        $code = $codes[$i];
        $desc = isset($weatherDescriptions[$code]) ? $weatherDescriptions[$code] : "Невідомо";
        $icon = isset($weatherIcons[$code]) ? $weatherIcons[$code] : "nn";
        if ($blockTime->format('Y-m-d H') == $now->format('Y-m-d H')) {
            $class = "pogoda_current";
        } elseif ($blockTime > $now) {
            $class = "pogoda_soon";
        } else {
            $class = "pogoda_finis";
        }
        $html .= '<div class="hour-block ' . $class . '">
		<div class="time">' . $timeLabel . '</div>
		<div class="temp">
		<img src="../style/pogoda/'.$icon.'">
		<span>' . $temp . '°C<span></div>
		<div class="tooltip">' . htmlspecialchars($desc) . '</div></div>';
    }
    $html .= '</div>';
    return $html;
}

/*ГРАФІК ТЕМПЕРАТУРИ DEVICE*/
function get_charts_temp_device($id,$critical) {
global $pdo;
$critical_temp = $critical ?? 60;
$stmt = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file LIMIT 1");
$stmt->execute(['file' => 'temp_device_' . (int)$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) return "";
$data = json_decode($row['data'], true);
if (!isset($data['history']) || !is_array($data['history'])) return "";
$today = date('Y-m-d');
$historyToday = array_filter($data['history'], function($entry) use ($today) {
return isset($entry['time']) && strpos($entry['time'], $today) === 0 && isset($entry['value']);
});
$chartData = [];
foreach ($historyToday as $entry) {
$chartData[] = [
'time' => date('H:i', strtotime($entry['time'])),
'value' => (float)$entry['value']
];
}
if (empty($chartData)) return "";
$output = '<div id="tempChart" style="width:99%; height:300px;"></div>
<script>
const chartData = ' . json_encode($chartData) . ';
const svgWidth = document.getElementById("tempChart").clientWidth;
const svgHeight = 300;
const margin = { top: 20, right: 30, bottom: 50, left: 50 };
const width = svgWidth - margin.left - margin.right;
const height = svgHeight - margin.top - margin.bottom;
const svgEl = d3.select("#tempChart").append("svg").attr("width", svgWidth).attr("height", svgHeight);
const svg = svgEl.append("g").attr("transform", `translate(${margin.left},${margin.top})`);
const parseTime = d3.timeParse("%H:%M");
chartData.forEach(d => d.timeParsed = parseTime(d.time));
const x = d3.scaleTime().domain(d3.extent(chartData, d => d.timeParsed)).range([0, width]);
const y = d3.scaleLinear().domain([0, 100]).range([height, 0]);
const xAxis = d3.axisBottom(x).ticks(12).tickFormat(d3.timeFormat("%H:%M"));
const yAxis = d3.axisLeft(y).ticks(10);
svg.append("g").attr("transform", `translate(0,${height})`).call(xAxis);
svg.append("g").call(yAxis);
for (let i = 0; i < chartData.length - 1; i++) {
    const segment = [chartData[i], chartData[i + 1]];
    const isHot = segment[0].value >= '.$critical_temp.' || segment[1].value >= '.$critical_temp.';
    svg.append("path").datum(segment).attr("fill", "none").attr("stroke", isHot ? "red" : "steelblue").attr("stroke-width", 2).attr("d", d3.line().x(d => x(d.timeParsed)).y(d => y(d.value)).curve(d3.curveMonotoneX));
}
svg.selectAll("circle").data(chartData).enter().append("circle").attr("cx", d => x(d.timeParsed)).attr("cy", d => y(d.value)).attr("r", 0).attr("fill", "red");
svg.append("text").attr("x", width / 2).attr("y", height + margin.bottom - 5).attr("text-anchor", "middle").text("Час (год:хв)");
svg.append("text").attr("transform", "rotate(-90)").attr("x", -height / 2).attr("y", -margin.left + 15).attr("text-anchor", "middle").text("Температура (°C)");
</script>';
return $output;
}
/*ГРАФІК ТЕМПЕРАТУРИ OLT*/
function get_charts_temp($id) {
global $pdo;
$stmt = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file LIMIT 1");
$stmt->execute(['file' => 'temp_olt_' . (int)$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) return "";
$data = json_decode($row['data'], true);
if (!isset($data['history']) || !is_array($data['history'])) return "";
$today = date('Y-m-d');
$historyToday = array_filter($data['history'], function($entry) use ($today) {
return isset($entry['time']) && strpos($entry['time'], $today) === 0 && isset($entry['value']);
});
$chartData = [];
foreach ($historyToday as $entry) {
$chartData[] = [
'time' => date('H:i', strtotime($entry['time'])),
'value' => (float)$entry['value']
];
}
if (empty($chartData)) return "";
$output = '<div id="tempChart" style="width:100%; height:300px;"></div>
<script>
const chartData = ' . json_encode($chartData) . ';
const svgWidth = document.getElementById("tempChart").clientWidth;
const svgHeight = 300;
const margin = { top: 20, right: 30, bottom: 50, left: 50 };
const width = svgWidth - margin.left - margin.right;
const height = svgHeight - margin.top - margin.bottom;
const svgEl = d3.select("#tempChart").append("svg").attr("width", svgWidth).attr("height", svgHeight);
const svg = svgEl.append("g").attr("transform", `translate(${margin.left},${margin.top})`);
const parseTime = d3.timeParse("%H:%M");
chartData.forEach(d => d.timeParsed = parseTime(d.time));
const x = d3.scaleTime().domain(d3.extent(chartData, d => d.timeParsed)).range([0, width]);
const y = d3.scaleLinear().domain([0, 100]).range([height, 0]);
const xAxis = d3.axisBottom(x).ticks(12).tickFormat(d3.timeFormat("%H:%M"));
const yAxis = d3.axisLeft(y).ticks(10);
svg.append("g").attr("transform", `translate(0,${height})`).call(xAxis);
svg.append("g").call(yAxis);
for (let i = 0; i < chartData.length - 1; i++) {
    const segment = [chartData[i], chartData[i + 1]];
    const isHot = segment[0].value >= 60 || segment[1].value >= 60;
    svg.append("path").datum(segment).attr("fill", "none").attr("stroke", isHot ? "red" : "steelblue").attr("stroke-width", 2).attr("d", d3.line().x(d => x(d.timeParsed)).y(d => y(d.value)).curve(d3.curveMonotoneX));
}
svg.selectAll("circle").data(chartData).enter().append("circle").attr("cx", d => x(d.timeParsed)).attr("cy", d => y(d.value)).attr("r", 0).attr("fill", "red");
svg.append("text").attr("x", width / 2).attr("y", height + margin.bottom - 5).attr("text-anchor", "middle").text("Час (год:хв)");
svg.append("text").attr("transform", "rotate(-90)").attr("x", -height / 2).attr("y", -margin.left + 15).attr("text-anchor", "middle").text("Температура (°C)");
</script>';
return $output;
}
function get_charts_temp_($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT data FROM tempdate WHERE file = :file LIMIT 1");
    $stmt->execute(['file' => 'temp_olt_' . (int)$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return "";
    }
    $data = json_decode($row['data'], true);
    if (!isset($data['history']) || !is_array($data['history'])) {
        return "";
    }
    $today = date('Y-m-d');
    $historyToday = array_filter($data['history'], function($entry) use ($today) {
        return isset($entry['time']) && strpos($entry['time'], $today) === 0 && isset($entry['value']);
    });
    $chartData = [];
    foreach ($historyToday as $entry) {
        $chartData[] = [
            'time' => date('H:i', strtotime($entry['time'])),
            'value' => (float)$entry['value']
        ];
    }
    if (empty($chartData)) {
        return "";
    }
    $labels = array_column($chartData, 'time');
    $values = array_column($chartData, 'value');
    $output = '
    <canvas id="tempChart" style="width: 100%; height: 300px;"></canvas>
    <script>
    (function(){
        const ctx = document.getElementById("tempChart").getContext("2d");
        const gradientStroke = ctx.createLinearGradient(0, 0, ctx.canvas.width, 0);
        gradientStroke.addColorStop(0, "blue");
        gradientStroke.addColorStop(1, "red");
        const gradientFill = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
        gradientFill.addColorStop(0, "rgba(255, 0, 0, 0.1)");
        gradientFill.addColorStop(1, "rgba(0, 0, 255, 0.1)");
        const data = {
            labels: ' . json_encode($labels) . ',
            datasets: [{
                label: "Temp CPU",
                data: ' . json_encode($values, JSON_NUMERIC_CHECK) . ',
                fill: true,
                borderColor: gradientStroke,
                backgroundColor: gradientFill,
                tension: 0.2,
                pointRadius: 1,
                pointHoverRadius: 2,
                borderWidth: 2
            }]
        };

        const options = {
            responsive: true,
            interaction: {
                mode: "nearest",
                axis: "x",
                intersect: false
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: "Час (год:хв)"
                    },
                    ticks: {
                        maxRotation: 90,
                        minRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 12
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: "Значення"
                    },
                    beginAtZero: false,
                    suggestedMin: Math.min(...' . json_encode($values) . ') - 1,
                    suggestedMax: Math.max(...' . json_encode($values) . ') + 1
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: "top"
                },
                tooltip: {
                    enabled: true,
                    mode: "nearest"
                }
            }
        };

        new Chart(ctx, {
            type: "line",
            data: data,
            options: options
        });
    })();
    </script>
    ';

    return $output;
}
function viewGraphPing3($id, $device, $view = 1) {
    global $db;
    $hoursMap = [1 => 24, 2 => 48, 3 => 72, 4 => 168];
    $hours = $hoursMap[$view] ?? 48;
    $selectsql = "SELECT * FROM mon_voltage WHERE deviceid = " . (int)$id . " AND mon_types = '" . addslashes($device) . "' ";
    $rows = $db->SimpleWhile($selectsql . " AND `added` >= DATE_SUB(NOW(), INTERVAL {$hours} HOUR) ORDER BY id ASC");
    $data = [];
    if (is_array($rows)) {
        foreach ($rows as $r) {
            $data[] = [
                'date'   => strtotime($r['added']) * 1000, // мс
                'volt'   => (float)$r['volt'],
                'energy' => (int)$r['energy'] // 1=Є 220В, 2=Немає 220В
            ];
        }
    }
    $a1 = ($view==1?' class="active"':'');
    $a2 = ($view==2?' class="active"':'');
    $a3 = ($view==3?' class="active"':'');
    $a4 = ($view==4?' class="active"':'');
    $dataJson = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $graph = <<<HTML
<style>
  .select_ping3_time a {padding:.35rem .6rem; border:1px solid #ddd; border-radius:.5rem; text-decoration:none; font:500 13px/1.2 system-ui, -apple-system, Segoe UI, Roboto, Arial}
  .select_ping3_time a.active {background:#0e7cc7; color:#fff; border-color:#0e7cc7}
  .onu-line-chart {width:100%; max-width:100%}
  #chartdiv {width: 100%; height: 320px}
  .select_ping3_time {display: flex;flex-direction: row;width: 100%;justify-content: flex-start;align-items: center;padding-bottom: 14px;}
  .d3-axis text {font: 12px system-ui, -apple-system, Segoe UI, Roboto, Arial}
  .d3-axis path, .d3-axis line {stroke: #adb5bd}
  .volt-line {fill:none; stroke-width:2; stroke:#0e7cc7}
  .pwr-line  {fill:none; stroke-width:3; stroke-linecap:round} /* плоскі відрізки внизу */
  .tooltip-d3 {
    position: absolute; pointer-events: none; background: rgba(0,0,0,.75); color:#fff;
    padding:6px 8px; border-radius:6px; font: 12px system-ui, -apple-system, Segoe UI, Roboto, Arial;
    transform: translate(-50%, -120%); white-space: nowrap;
  }
</style>

<div class="onu-line-chart"><div id="chartdiv"></div></div>
<div class="select_ping3_time">
  <a href="/?do=ping3&act=view&id={$id}&device=ping3&view=1"{$a1}>Last 24 Hours</a>
  <a href="/?do=ping3&act=view&id={$id}&device=ping3&view=2"{$a2}>Last 48 Hours</a>
  <a href="/?do=ping3&act=view&id={$id}&device=ping3&view=3"{$a3}>За 3 дні</a>
  <a href="/?do=ping3&act=view&id={$id}&device=ping3&view=4"{$a4}>За тиждень</a>
</div>
<script>
(function(){
  function ensureD3(cb){
    if (window.d3 && d3.version && d3.version.split('.')[0] >= 7) { cb(); return; }
    var s = document.createElement('script');
    s.src = "https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js";
    s.onload = cb;
    document.head.appendChild(s);
  }
  ensureD3(function(){
    const data = {$dataJson};
    const container = document.getElementById('chartdiv');
    if (!container) return;
    container.innerHTML = '';
    const margin = {top: 24, right: 40, bottom: 30, left: 48};
    const rect = container.getBoundingClientRect();
    const width = Math.max(300, rect.width) - margin.left - margin.right;
    const height = Math.max(220, 320) - margin.top - margin.bottom;
    const svg = d3.select(container)
      .append('svg')
      .attr('viewBox', `0 0 \${width+margin.left+margin.right} \${height+margin.top+margin.bottom}`)
      .attr('preserveAspectRatio', 'xMinYMin')
      .append('g')
      .attr('transform', `translate(\${margin.left},\${margin.top})`);
    const parseX = d => new Date(d.date);
    const volt = d => +d.volt;
    const energy = d => +d.energy;
    const x = d3.scaleTime().domain(d3.extent(data, parseX) || [new Date(Date.now()-48*3600e3), new Date()]).range([0, width]);
    const vMin = d3.min(data, volt), vMax = d3.max(data, volt);
    const y = d3.scaleLinear().domain([(vMin != null ? Math.floor(vMin*10)/10 - 0.2 : 0),(vMax != null ? Math.ceil(vMax*10)/10 + 0.2 : 1)]).range([height, 0]).nice();
    const xAxis = d3.axisBottom(x).ticks(6);
    const yAxis = d3.axisLeft(y).ticks(6);
    svg.append('g').attr('class','d3-axis x-axis').attr('transform', `translate(0,\${height})`).call(xAxis);
    svg.append('g').attr('class','d3-axis').call(yAxis);
    const lineVolt = d3.line().x(d => x(parseX(d))).y(d => y(volt(d))).defined(d => Number.isFinite(volt(d)));
    svg.append('path').datum(data).attr('class','volt-line').attr('d', lineVolt);
    const yBase = height - 1.5;
    const segments = [];
    if (data.length) {
      let curState = null, start = null;
      for (let i = 0; i < data.length; i++) {
        const s = (energy(data[i]) === 1 || energy(data[i]) === 2) ? energy(data[i]) : null;
        if (s == null) continue;
        const t = parseX(data[i]);
        if (curState === null) { curState = s; start = t; continue; }
        if (s !== curState) {
          const end = t;
          segments.push({state: curState, x1: start, x2: end});
          curState = s; start = t;
        }
      }
      const lastT = parseX(data[data.length-1]);
      if (curState !== null && start !== null) {
        const x2 = (+lastT === +start) ? new Date(+lastT + 60*1000) : lastT;
        segments.push({state: curState, x1: start, x2});
      }
    }
    const pwrLayer = svg.append('g').attr('class','pwr-layer');
    const barHeight = 15;
	pwrLayer.selectAll('rect').data(segments).join('rect').attr('class','pwr-bar').attr('x', d => x(d.x1)).attr('width', d => x(d.x2) - x(d.x1)).attr('y', yBase - barHeight).attr('height', barHeight).attr('fill', d => d.state === 1 ? '#16a34a' : '#dc2626').attr('opacity', 0.7);
    const legend = svg.append('g').attr('transform', 'translate(0,-10)');
    legend.append('circle').attr('r',4).attr('cx',0).attr('cy',0).attr('fill','#0e7cc7');
    legend.append('text').attr('x',10).attr('y',4).text('Вольтажі').attr('font-size','12px');
    legend.append('rect').attr('x',80).attr('y',-3).attr('width',12).attr('height',6).attr('fill','#16a34a');
    legend.append('text').attr('x',98).attr('y',4).text('Є 220В').attr('font-size','12px');
    legend.append('rect').attr('x',150).attr('y',-3).attr('width',12).attr('height',6).attr('fill','#dc2626');
    legend.append('text').attr('x',168).attr('y',4).text('Немає 220В').attr('font-size','12px');
    const parent = container.parentElement || container;
    parent.style.position = parent.style.position || 'relative';
    const tooltip = d3.select(parent).append('div').attr('class','tooltip-d3').style('opacity', 0);
    const bisect = d3.bisector(d => parseX(d)).left;
    svg.append('rect')
      .attr('fill','transparent').attr('pointer-events','all')
      .attr('width', width).attr('height', height)
      .on('mousemove', onMove)
      .on('mouseleave', () => tooltip.style('opacity', 0));
    function onMove(event){
      if (!data.length) return;
      const [mx] = d3.pointer(event, this);
      const xTime = x.invert(mx);
      let i = bisect(data, xTime, 1);
      if (i >= data.length) i = data.length - 1;
      const d0 = data[i-1] || data[i], d1 = data[i];
      const d = (!d1 ? d0 : (+xTime - +parseX(d0) > +parseX(d1) - +xTime ? d1 : d0));
      const dt = new Date(d.date);
      const pad = n => n < 10 ? ('0'+n) : n;
      const stamp = dt.getFullYear()+"-"+pad(dt.getMonth()+1)+"-"+pad(dt.getDate())+" "+pad(dt.getHours())+":"+pad(dt.getMinutes());
      const statusTxt = d.energy === 1 ? 'Є 220В' : (d.energy === 2 ? 'Немає 220В' : d.energy);
      const p = d3.pointer(event, container);
      tooltip.style('opacity', 1).style('left', p[0]+'px').style('top', p[1]+'px')
        .html(`⏱ \${stamp}<br>🔌 Статус: \${statusTxt}<br>⚡ Вольтаж: \${(+d.volt).toFixed(2)} В`);
    }
    const zoom = d3.zoom().scaleExtent([1, 20]).translateExtent([[0,0],[width,height]]).extent([[0,0],[width,height]])
      .on('zoom', (event) => {
        const zx = event.transform.rescaleX(x);
        svg.select('.x-axis').call(d3.axisBottom(zx).ticks(6));
        svg.select('.volt-line').attr('d',
          d3.line().x(d=>zx(parseX(d))).y(d=>y(volt(d))).defined(lineVolt.defined())(data)
        );
        pwrLayer.selectAll('line')
          .attr('x1', d => zx(d.x1))
          .attr('x2', d => zx(d.x2));
      });
    svg.call(zoom);
  });
})();
</script>
HTML;

    return $graph;
}


?>