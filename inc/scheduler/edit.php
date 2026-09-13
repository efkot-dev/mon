<?php
if (!defined('PONMONITOR') && !defined('SCHEDULER')) {
    die('Hacking attempt!');
}

$template_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($template_id <= 0) {
    die('Invalid template ID');
}

$template = $pdo->prepare("SELECT * FROM templates WHERE id = ?");
$template->execute([$template_id]);
$template = $template->fetch(PDO::FETCH_ASSOC);
if (!$template) {
    die('Template not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $group = (int)($_POST['group_id'] ?? 0);
    $type = $_POST['run_type'] ?? 'manual';
    $schedule = $_POST['schedule_time'] ?: null;
    $commands = array_filter(array_map('trim', explode("\n", $_POST['commands'] ?? '')));
    $devices = $_POST['devices'] ?? [];

    $pdo->prepare("UPDATE templates SET name=?, description=?, group_id=?, run_type=?, schedule_time=? WHERE id=?")
        ->execute([$name, $desc, $group, $type, $schedule, $template_id]);

    $pdo->prepare("DELETE FROM template_commands WHERE template_id = ?")->execute([$template_id]);
    $cmdStmt = $pdo->prepare("INSERT INTO template_commands (template_id, command, sort_order) VALUES (?, ?, ?)");
    foreach ($commands as $i => $cmd) {
        $cmdStmt->execute([$template_id, $cmd, $i]);
    }

    $pdo->prepare("DELETE FROM template_devices WHERE template_id = ?")->execute([$template_id]);
    $devStmt = $pdo->prepare("INSERT INTO template_devices (template_id, device_id) VALUES (?, ?)");
    foreach ($devices as $deviceId) {
        $devStmt->execute([$template_id, (int)$deviceId]);
    }

    $pdo->prepare("DELETE FROM template_variables WHERE template_id = ?")->execute([$template_id]);
    $variables = $_POST['variables'] ?? [];
    $types = $_POST['var_type'] ?? [];
    $descs = $_POST['var_desc'] ?? [];
    $enums = $_POST['var_enum'] ?? [];
    $per_snmt = $pdo->prepare("INSERT INTO template_variables (template_id, name, type, description, enum_values) VALUES (?, ?, ?, ?, ?)");
    foreach ($variables as $n) {
        $type = $types[$n] ?? 'text';
        $desc = trim($descs[$n] ?? '');
        $enum = trim($enums[$n] ?? null);
        if (!in_array($type, ['text', 'number', 'enum'])) {
            $type = 'text';
        }
        $per_snmt->execute([$template_id, $n, $type, $desc, $type === 'enum' ? $enum : null]);
    }

    header('Location: ' . URL_SCHEDULER);
    exit;
}

$groups = $pdo->query("SELECT * FROM template_groups")->fetchAll(PDO::FETCH_ASSOC);
$allDevices = $pdo->query("SELECT id, place as name FROM switch")->fetchAll(PDO::FETCH_ASSOC);
$usedDevices = $pdo->prepare("SELECT device_id FROM template_devices WHERE template_id = ?");
$usedDevices->execute([$template_id]);
$usedDeviceIds = array_column($usedDevices->fetchAll(PDO::FETCH_ASSOC), 'device_id');

$commandsData = $pdo->prepare("SELECT command FROM template_commands WHERE template_id = ? ORDER BY sort_order");
$commandsData->execute([$template_id]);
$commandLines = implode("\n", array_column($commandsData->fetchAll(PDO::FETCH_ASSOC), 'command'));

$variables = $pdo->prepare("SELECT * FROM template_variables WHERE template_id = ?");
$variables->execute([$template_id]);
$vars = $variables->fetchAll(PDO::FETCH_ASSOC);

// --- HTML в змінній ---
$content = "<form method='post'>
<div class='pmon_block' id='board_fault'>
<div class='pmon_block_left block_white pre40'>    
    <div class='pole1'>
        <div class='img'><img src='../style/img/service_onu.png'></div>
        <div class='form1'>Назва<b>Короткий опис функцій</b></div>
        <div class='form2'><input type='text' name='name' required class='css-input' value=\"" . htmlspecialchars($template['name']) . "\"></div>
    </div>        
    <div class='pole1'>
        <div class='img'><img src='../style/img/huawei_countmac.png'></div>
        <div class='form1'>Нотатки</div>
        <div class='form2'><textarea name='description' rows='4'>" . htmlspecialchars($template['description']) . "</textarea></div>
    </div>        
    <div class='pole1'>
        <div class='img'><img src='../style/img/manager_vlan.png'></div>
        <div class='form1'>Група</div>
        <div class='form2'>
            <select name='group_id'>";
            foreach ($groups as $g) {
                $selected = ($g['id'] == $template['group_id']) ? 'selected' : '';
                $content .= "<option value='{$g['id']}' {$selected}>" . htmlspecialchars($g['name']) . "</option>";
            }
$content .= "</select>
        </div>
    </div>        
    <div class='pole1'>
        <div class='img'><img src='../style/img/addconnect.png'></div>
        <div class='form1'>Тип виконання</div>
        <div class='form2'>
            <select name='run_type'>
                <option value='manual'" . ($template['run_type'] === 'manual' ? ' selected' : '') . ">Ручний</option>
                <option value='auto'" . ($template['run_type'] === 'auto' ? ' selected' : '') . ">За розкладом</option>
                <option value='onu_event'" . ($template['run_type'] === 'onu_event' ? ' selected' : '') . ">В налаштуванях ONU</option>
            </select>
        </div>
    </div>
    <div class='pole1'>
        <div class='img'><img src='../style/img/uptime.png'></div>
        <div class='form1'>Час виконання<b>Для автоматичного режиму</b></div>
        <div class='form2'><input type='time' name='schedule_time' value=\"" . htmlspecialchars($template['schedule_time']) . "\"></div>
    </div>            
    <div class='pole1'>
        <div class='img'><img src='../style/img/m1.png'></div>
        <div class='form1'>Комутатори<b>На яких може застосовуватися</b></div>
        <div class='form2 height_form_200'>";
            foreach ($allDevices as $dev) {
                $checked = in_array($dev['id'], $usedDeviceIds) ? 'checked' : '';
                $content .= "<label><input type='checkbox' name='devices[]' value='{$dev['id']}' {$checked}> " . htmlspecialchars($dev['name']) . "</label><br>";
            }
$content .= "</div>
    </div>    
</div>    
<div class='pmon_block_right pre60'>
    <label>Команди (по рядку):</label><br>
    <textarea style='height:300px;' name='commands' rows='10'>" . htmlspecialchars($commandLines) . "</textarea><br><br>
    <div id='variables_output'>";
    foreach ($vars as $v) {
        $showEnum = $v['type'] === 'enum' ? '' : 'display:none;';
        $content .= "
        <div class='variable-block' style='border:1px solid #ccc; margin:10px 0; padding:10px;'>
            <strong>Змінна:</strong> [{$v['name']}]<br>
            <label>Тип:
                <select name='var_type[{$v['name']}]' onchange=\"document.getElementById('enum_{$v['name']}').style.display = (this.value === 'enum') ? 'block' : 'none';\">
                    <option value='text'" . ($v['type'] === 'text' ? ' selected' : '') . ">Текст</option>
                    <option value='number'" . ($v['type'] === 'number' ? ' selected' : '') . ">Число</option>
                    <option value='enum'" . ($v['type'] === 'enum' ? ' selected' : '') . ">Вибірка</option>
                </select>
            </label><br>
            <label>Опис:
                <input type='text' name='var_desc[{$v['name']}]' value='" . htmlspecialchars($v['description']) . "'>
            </label><br>
            <div class='enum-values' id='enum_{$v['name']}' style='margin-top: 5px; {$showEnum}'>
                <label>Варіанти вибору (через |):<br>
                    <input type='text' name='var_enum[{$v['name']}]' value='" . htmlspecialchars($v['enum_values']) . "'>
                </label>
            </div>
            <input type='hidden' name='variables[]' value='{$v['name']}'>
        </div>";
    }
$content .= "</div>
</div>
</div>
<button type='submit'>Зберегти</button>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.querySelector(\"textarea[name='commands']\");
    const outputDiv = document.getElementById('variables_output');
    function createVariableBlock(variable) {
        if (document.querySelector(\"input[name='variables[]'][value='\" + variable + \"']\")) return;
        const block = document.createElement('div');
        block.classList.add('variable-block');
        block.style.border = '1px solid #ccc';
        block.style.margin = '10px 0';
        block.style.padding = '10px';
        const id = `var_type_\${variable}`;
        block.innerHTML = `
            <strong>Змінна:</strong> [\${variable}]<br>
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
        const matches = [...text.matchAll(/\\[([a-zA-Z0-9_]+)\\]/g)];
        const uniqueVars = [...new Set(matches.map(m => m[1]))];
        const skipVars = ['onu_inface', 'pon_inface'];
        uniqueVars.forEach(variable => {
            if (skipVars.includes(variable)) return;
            const block = createVariableBlock(variable);
            if (block) outputDiv.appendChild(block);
        });
    });
});
</script>";
