<?php
if (!defined('PONMONITOR') && !defined('SCHEDULER')) {
    die('Hacking attempt!');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $group = (int)($_POST['group_id'] ?? 0);
    $type = $_POST['run_type'] ?? 'manual';
    $schedule = $_POST['schedule_time'] ?: null;
    $commands = array_filter(array_map('trim', explode("\n", $_POST['commands'] ?? '')));
    $devices = $_POST['devices'] ?? [];
	$stmt = $pdo->prepare("INSERT INTO templates (name, description, group_id, run_type, schedule_time) VALUES (?, ?, ?, ?, ?)");
	$stmt->execute([$name, $desc, $group, $type, $schedule]);
	$tid = (int)$pdo->lastInsertId();
	$cmdStmt = $pdo->prepare("INSERT INTO template_commands (template_id, command, sort_order) VALUES (?, ?, ?)");
	foreach ($commands as $i => $cmd) {
		$cmdStmt->execute([$tid, $cmd, $i]);
	}
	$devStmt = $pdo->prepare("INSERT INTO template_devices (template_id, device_id) VALUES (?, ?)");
	foreach ($devices as $deviceId) {
		$devStmt->execute([$tid, (int)$deviceId]);
	}
	$variables = $_POST['variables'] ?? [];
	$types	= $_POST['var_type'] ?? [];
	$descs	= $_POST['var_desc'] ?? [];
	$enums	= $_POST['var_enum'] ?? [];
	$per_snmt = $pdo->prepare("INSERT INTO template_variables (template_id, name, type, description, enum_values) VALUES (?, ?, ?, ?, ?)");
	foreach ($variables as $n) {
		$type = $types[$n] ?? 'text';
		$desc = trim($descs[$n] ?? '');
		$enum = trim($enums[$n] ?? null);
		if (!in_array($type, ['text', 'number', 'enum'])) {
			$type = 'text';
		}
		$per_snmt->execute([$tid,$n,$type,$desc,$type === 'enum' ? $enum : null]);
	}
	header('Location: ' . URL_SCHEDULER);
    exit;
}
$groups = $pdo->query("SELECT * FROM template_groups")->fetchAll(PDO::FETCH_ASSOC);
$allDevices = $pdo->query("SELECT id, place as name FROM switch")->fetchAll(PDO::FETCH_ASSOC);
$content = "<form method='post'>
	<div class='pmon_block' id='board_fault'>
	<div class='pmon_block_left block_white pre40'>	
		<div class='pole1'>
			<div class='img'><img src='../style/img/service_onu.png'></div>
			<div class='form1'>Назва<b>Короткий опис функцій</b></div>
			<div class='form2'><input type='text' name='name' required class='css-input'></div>
		</div>		
		<div class='pole1'>
			<div class='img'><img src='../style/img/huawei_countmac.png'></div>
			<div class='form1'>Нотатки</div>
			<div class='form2'><textarea name='description' rows='4' cols='60'></textarea></div>
		</div>		
		<div class='pole1'>
			<div class='img'><img src='../style/img/manager_vlan.png'></div>
			<div class='form1'>Група</div>
			<div class='form2'>";
			$content .= '<select name="group_id">';
			foreach ($groups as $g) {
				$content .= '<option value="' . $g['id'] . '">' . htmlspecialchars($g['name']) . '</option>';
			}
			$content .= '</select>';
		$content .= "</div>
		</div>		
		<div class='pole1'>
			<div class='img'><img src='../style/img/addconnect.png'></div>
			<div class='form1'>Тип виконання</div>
			<div class='form2'>
			<select name='run_type'>
				<option value='manual'>Ручний</option>
				<option value='auto'>За розкладом</option>
				<option value='onu_event'>В налаштуванях ONU</option>
			</select>
			</div>
		</div>
		<div class='pole1'>
			<div class='img'><img src='../style/img/uptime.png'></div>
			<div class='form1'>Час виконання<b>Для автоматичного режиму</b></div>
			<div class='form2'><input type='time' name='schedule_time'></div>
		</div>			
		<div class='pole1'>
			<div class='img'><img src='../style/img/m1.png'></div>
			<div class='form1'>Комутатори<b>На яких може застосовуватися</b></div>
			<div class='form2 height_form_200'>";
			foreach ($allDevices as $dev) {
				$content .= '<label><input type="checkbox" name="devices[]" value="' . $dev['id'] . '"> ' . htmlspecialchars($dev['name']) . '</label><br>';
			}
			$content .= "</div>
		</div>	
	</div>	
	<div class='pmon_block_right pre60'>
		<label>[onu_inface] - 0/1:1
		<br>[onu_mac] - xxxx.xxxx.xxxx
		<br>[onu_sn] - xxxxxxxxx
		<br>[pon_inface] - 0/0/1
		<br>[pon_type] - epon або gpon		
		</label>
		<textarea id='text_console' name='commands' rows='10' cols='60'></textarea><br><br>
		<div id='variables_output'></div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.querySelector(\"textarea[name='commands']\");
    const outputDiv = document.getElementById('variables_output');
    function createVariableBlock(variable) {
        const block = document.createElement('div');
        block.classList.add('variable-block');
        block.style.border = '1px solid #ccc';
        block.style.margin = '10px 0';
        block.style.padding = '10px';
        const id = `var_type_\${variable}`;
        block.innerHTML = `
            <strong>Змінна:</strong> [\${variable}\]<br>
            <label>Тип:
                <select name=\"var_type[\${variable}]\" id=\"\${id}\">
                    <option value=\"text\">Текст</option>
                    <option value=\"number\">Число</option>
                    <option value=\"enum\">Вибірка</option>
                </select>
            </label><br>
            <label>Опис:
                <input type=\"text\" name=\"var_desc[\${variable}]\" placeholder=\"Опис змінної\">
            </label><br>
            <div class=\"enum-values\" id=\"enum_\${variable}\" style=\"display:none; margin-top: 5px;\">
                <label>Варіанти вибору (через |):<br>
                    <input type=\"text\" name=\"var_enum[\${variable}]\" placeholder=\"select1|select2|select3\">
                </label>
            </div>
            <input type=\"hidden\" name=\"variables[]\" value=\"\${variable}\">
        `;
        setTimeout(() => {
            const select = document.getElementById(id);
            const enumDiv = document.getElementById(`enum_\${variable}`);
            if (!select || !enumDiv) return;
            select.addEventListener('change', function () {
                enumDiv.style.display = (this.value === 'enum') ? 'block' : 'none';
            });
        }, 10);
        return block;
    }
    textarea.addEventListener('input', function () {
		const text = textarea.value;
		const matches = [...text.matchAll(/\[([a-zA-Z0-9_]+)\]/g)];
		const uniqueVars = [...new Set(matches.map(m => m[1]))];
		const skipVars = ['onu_inface','pon_inface','pon_type','onu_mac'];
		outputDiv.innerHTML = '';
		uniqueVars.forEach(variable => {
			if (skipVars.includes(variable)) return;
			const block = createVariableBlock(variable);
			if (block) outputDiv.appendChild(block);
		});
	});

});
</script>
";
$content .= '<button type="submit">Зберегти</button></form>';
?>
