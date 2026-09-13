<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require_once ENGINE_DIR.'functions/taskers.php';
$tplresult = '';
$taskerTable = '';
$metatags = [
    'title' => ''.$lang['syspmon'].' PMon',
    'description' => 'Taskers Management',
    'page' => 'process'
];
$switch_array = [];
$switch = $db->SimpleWhile("SELECT id, place, oidid, inf, model FROM switch");
if (!empty($switch)) {
    foreach ($switch as $res) {
        $switch_array[$res['id']] = $res;
    }
}
$taskers_array = [];
$sql = "SELECT * FROM taskers";
$result = $db->SimpleWhile($sql);
$start = false;
if (!empty($result)) {
    foreach ($result as $row) {
        $types = (!empty($row['deviceid']) ? $row['deviceid'] : 'system');
			$taskers_array[$types][$row['workid']] = $row;
			if (!isset($task_counts[$types])) {
				$task_counts[$types] = 0;
			}
        $task_counts[$types]++;
    }
}else{
	$start = true;
}
$tplresult .= '
<div id="onu-speedbar">
    <a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['syspmon'].' PMon</span>
    </div>
</div>';
$tplresult .= '<div class="container">
    <div class="left-column">';
$tplresult .= '<input type="text" id="search-devices" placeholder="Search devices..." style="width: 100%; padding: 5px; margin-bottom: 10px;">';
$tplresult .= '<form id="device-tasks-form">';
$task_counts_count = isset($task_counts['system']) ? '<span class="signal3">'.$task_counts['system'].'</span>' : '<span class="signal4">0</span>';
$tplresult .= "<div class='device-item'>
	<input type='checkbox' class='device-checkbox' data-device-id='system' checked> 
	<a href='/?do=taskers&id=system'>System</a>
	{$task_counts_count}</div>";
foreach ($switch_array as $device_id => $device) {
	if(isset($device_id) && ($access->get('dev'.$device_id))){
    $is_checked = isset($taskers_array[$device_id]) ? 'checked' : '';
    $device_name = htmlspecialchars($device['place']);
	$count = isset($task_counts[$device_id]) ? '<span class="signal3">'.$task_counts[$device_id].'</span>' : '<span class="signal4">0</span>';
    $tplresult .= "<div class='device-item'>
	<input type='checkbox' class='device-checkbox' data-device-id='{$device_id}' {$is_checked}> 
	<a ".(isset($task_counts[$device_id]) ? '' : 'style="color:red;"')." href='/?do=taskers&id={$device_id}'>{$device_name}</a>
	{$count} ".(isset($task_counts[$device_id]) ? "" : "<span onclick='taskers({$device_id})'><img src='../style/img/taskers_add.png'></span> <span onclick='taskers_wizard({$device_id})'><img src='../style/img/taskers_wizard.png'></span>")."
	</div>";
	
	}
}
$tplresult .= '</form>';
$tplresult .= '</div>';
$tplresult .= '<div class="right-column">';
$device_id_from_url = isset($_GET['id']) ? $_GET['id'] : null;
if($device_id_from_url!=false){
	$tplresult .= "<div class='back-undo'><a href='/?do=taskers'><img src='../style/img/taskers_undo.png'>View list</a></div>";
}else{
	$tplresult .= "<span class=\"btn_ajax btn_blue\" onclick=\"taskmasters();\">Setup</span>";
}
$js = "";
if($start){
	$tplresult .= "<span class=\"btn_ajax btn_green\" onclick=\"startmasters();\">First setup</span>";
}
$tplresult .= "<div class='ajax-result'>";
$current_time = time();
$tplresult .= "<table class='resp-tab'><thead><tr>
<th></th>
<th>Process</th>
<th>Interval (s)</th>
<th>Last run</th>
<th>Bar remaining</th>
<th>Time</th>
<th>Actions</th>
</tr></thead><tbody>";
foreach ($taskers_array as $id_device => $task) {
    if ($device_id_from_url && $id_device != $device_id_from_url) {
        continue;
    }
	if (isset($id_device) && $id_device == 'system') {
		$model = 'system';
		$place = 'PMON';
		$button = "<span class='taskers-add' onclick='taskers(0)'>ADD</span>";
		$button_delete = '';
	} elseif (isset($switch_array[$id_device])) {
		$model = $switch_array[$id_device]['inf'].' '.$switch_array[$id_device]['model'];
		$place = '<span class="task_device">'.$switch_array[$id_device]['place'].'</span>';
		$button = "<span class='taskers-add' onclick='taskers({$id_device})'>{$lang['addeds']}</span>";
		$button_delete = "<span class='taskers-del' onclick='taskers_delete({$id_device},\"{$lang['fiber_del_conn']}\")'>{$lang['delet']}</span>";
	} else {
		$model = 'невідомий пристрій';
		$place = 'невідомо';
		$button = '';
		$button_delete = '';
	}
	$tplresult .= "<tr id='tasker-{$id_device}'>
        <td colspan='7' class='head-tasker'>
		{$button}
            <span class=\"device-block-place\">{$place}</span>            
            <a href='/?do=taskers&id={$id_device}'><span class='blus'>{$model}</span></a>
{$button_delete}			
        </td></tr>";
    foreach ($task as $id_task => $detail) {
		$task_id = $lang['taskers_' . $detail['workid']];
		$work_id = $detail['workid'];
		$id = $detail['id'];        
		$status = ucfirst($detail['status']);
		$last_run_time = aftertime($detail['last_run_time']) ?? 'Never';		
		$interval = '';
		$bar_time = '';
		$time_remaining = '';
		if (isset($detail['type_scheduler']) && $detail['type_scheduler'] == 'hourly') {
			$interval = 'Hourly';
			$bar_time = get_hourly_bar($detail['custom_time'], $detail['last_run_time']);
			$time_remaining = get_hourly_remaining_time($detail['custom_time'], $detail['last_run_time']);
		} elseif (isset($detail['type_scheduler']) && $detail['type_scheduler'] == 'daily') {
			$interval = 'Daily';
			$bar_time = get_daily_bar($detail['last_run_time'], $detail['daily_time']);
			$time_remaining = get_daily_remaining_time($detail['last_run_time'], $detail['daily_time']);
		} else {
			$interval = 'Manual';
			$bar_time = get_custom_bar($detail['interval'], $detail['last_run_time']);
			$time_remaining = get_custom_remaining_time($detail['interval'], $detail['last_run_time']);
		}
		if (isset($detail['pmon']) && $detail['pmon'] == 'work') {
			$img_status = "";
			$pmon_work = "<span class=\"panel_taskers\" onclick=\"pauseTask('{$id_device}','{$id}',event)\"><img src='../style/img/taskers_pause.png'></span>";
		} else {            
			$pmon_work = "<span class=\"panel_taskers\" onclick=\"playTask('{$id_device}','{$id}',event)\"><img src='../style/img/taskers_play.png'></span>";
			$img_status = "<img class='notwork' src='../style/img/taskers_exclamation-mark.png'>";
		}
		$tplresult .= "
		<tr class='task-row jobid-{$id_device}-{$id}' id='tasker-{$id_device}'>
			<td>
				<div class='delete_ont'>
					<input type='checkbox' name='delete_taskers[]' value='{$id}' />
				</div>
			</td>
			<td class='description_name mobile_font stikers'>
				{$img_status}				
				<span class='name-onu'>{$task_id}</span>
				<span class='signal5'>{$work_id}</span>
			</td>
			<td><span class='taskers_interval'>{$interval}</span></td>
			<td><span class='on_'>{$last_run_time}</span></td>
			<td class='time-remaining'>{$bar_time}</td>
			<td>{$time_remaining}</td>
			<td>
				{$pmon_work}
				<span class=\"panel_taskers\" onclick=\"deleteTask('{$id_device}','{$id}',event)\"><img src='../style/img/delet.png'></span>
			</td>
		</tr>";
	}
}
	$tplresult .= '<tr><td onclick=\'delete_taskers("'.$lang['delet'].'")\' colspan="8" >
	<span class="marker">'.$lang['delet'].'</span>
	</td></tr>';
$tplresult .= "</tbody></table>";
$tplresult .= $taskerTable;
$tplresult .= "<div id=\"overlapsoverlaps\"></div>
<div id=\"adjustments\"></div>
<div id=\"queries\"></div></div></div></div></div>";
$tplresult .= "<script>
{$js}
document.getElementById('search-devices').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.device-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? 'block' : 'none';
    });
});
document.querySelectorAll('.device-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const deviceId = this.dataset.deviceId;
        const taskRow = document.querySelectorAll('#tasker-' + deviceId);
         taskRow.forEach(row => {
            row.style.display = this.checked ? 'table-row' : 'none';
        });
    });
});
</script>";
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', $tplresult);
$tpl->compile('content');
$tpl->clear();
?>