<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';

$oltid = isset($_POST['oltid']) ? Clean::int($_POST['oltid']) : null;
$portid = isset($_POST['portid']) ? Clean::int($_POST['portid']) : null;

if ($oltid && $portid) {
    $getswitch = $db->Fast('switch', '*', ['id' => $oltid]);

    if (!empty($getswitch['device']) && !empty($getswitch['netip']) && !empty($getswitch['snmpro']) && $getswitch['oidid'] != 9) {
?>
        <div class="traffic_css">
            <canvas id="trafficChart" width="800" height="400"></canvas>
        </div>


        <script>
            let ctx = document.getElementById('trafficChart').getContext('2d');
			let trafficChart = new Chart(ctx, {
				type: 'line',
				data: {
					labels: [],
					datasets: [{
							label: '<?=$lang['traffic_in'];?> Mbps',
							data: [],
							backgroundColor: '#9bddb5',
							borderColor: '#1db85b',
							borderWidth: 1,
							pointRadius: 0, // Відключення відображення точок
							fill: true // Заповнення простору під графіком
						},
						{
							label: '<?=$lang['traffic_out'];?> Mbps',
							data: [],
							backgroundColor: '#f54a4a4f',
							borderColor: 'red',
							borderWidth: 1,
							pointRadius: 0, // Відключення відображення точок
							fill: true // Заповнення простору під графіком
						}
					]
				},
				options: {
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: function(value, index, values) {
									const units = ['Byte/s', 'Kb/s', 'Mb/s', 'Gb/s'];
									let unitIndex = 0;
									while (value >= 1024 && unitIndex < units.length - 1) {
										value /= 1024;
										unitIndex++;
									}
									return value.toFixed(2) + ' ' + units[unitIndex];
								}
							}
						},xAxes: [{ // Використання xAxes замість x
                display: false // Приховати мітки на осі x
            }]
					}
				}
			});
            setInterval(updateChart, 3000);
            function updateChart() {
				$.ajax({
					url: root + 'ajax/traffic.php',
					method: 'POST',
					data: {
						oltid: <?=$oltid;?>,portid: <?=$portid;?>
					},
					success: function(data) {
						try {
							if (typeof data === 'string') {
								data = JSON.parse(data);
							}
							if (data && typeof data.in !== 'undefined' && typeof data.out !== 'undefined') {
								trafficChart.data.labels.push(new Date().toLocaleTimeString());
								trafficChart.data.datasets[0].data.push(Math.max(0, data.in / (1024 * 1024)));
								trafficChart.data.datasets[1].data.push(Math.max(0, data.out / (1024 * 1024)));
								trafficChart.update();
							}
						} catch (err) {
							console.error('Error: ', err, data);
						}
					}
				});
			}
        </script>
<?php
    }
}
?>
