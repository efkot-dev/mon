<?php
if (!defined('PONMONITOR') && !defined('VIDEO')) {
    die('Hacking attempt!');
}
$token = isset($_POST['token']) ? Clean::text($_POST['token']) : '123';
$userid = isset($_POST['userid']) ? Clean::text($_POST['userid']) : null;
$chatid_chat = isset($_POST['chatid_chat']) ? Clean::text($_POST['chatid_chat']) : null;
$chatid_groups = isset($_POST['chatid_groups']) ? Clean::text($_POST['chatid_groups']) : null;
$messageid = isset($_POST['messageid']) ? Clean::text($_POST['messageid']) : null;
$type = isset($_POST['type']) ? Clean::text($_POST['type']) : 'off';
$name = 'module_surveillance_telegram';
$stmt = $pdo->prepare("SELECT value FROM config WHERE name = ?");
$stmt->execute([$name]);
$config_json = $stmt->fetchColumn();
$config_board_telegram = [];
if ($config_json) {
    $config_board_telegram = json_decode($config_json, true);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['type' => $type];    
    switch ($type) {
        case 'bot':
            $data['token'] = $token;
            $data['userid'] = $userid ?? '';
            break;
        case 'chat':
            $data['token'] = $token;
            $data['chatid'] = $chatid_chat ?? '';
            break;
        case 'groups':
            $data['token'] = $token;
            $data['chatid'] = $chatid_groups ?? '';
            $data['messageid'] = (int)$messageid ?? '';
            break;
        case 'off':
        default:
            break;
    }
    $json_to_save = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM config WHERE name = ?");
    $stmt->execute([$name]);
    $exists = $stmt->fetchColumn() > 0;
    if ($exists) {
        $stmt = $pdo->prepare("UPDATE config SET value = ? WHERE name = ?");
        $stmt->execute([$json_to_save, $name]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO config (name, value) VALUES (?, ?)");
        $stmt->execute([$name, $json_to_save]);
    }    
    $config_board_telegram = $data;
}
$metatags = array(
	'title'=>'Налаштування',
	'description'=>'Сповіщення в відеспостережені',
	'page'=>'board_view'
);
$speedbar .='
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<a class="brmhref" href="/?do=surveillance"><i class="fi fi-rr-angle-left"></i>Відеоспостереження</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Налаштування Telegram сповіщення</span>
';
$content .= '
	<div id="onu-speedbar">
		'.$speedbar.'
	</div>
';
$content_form = '';
$content_form .= '
<form method="post" id="settingsForm">
<select id="type" name="type" onchange="showForm()">
    <option value="off" '.(($config_board_telegram['type'] ?? '') === 'off' ? 'selected' : '').'>Вимкнути</option>
    <option value="bot" '.(($config_board_telegram['type'] ?? '') === 'bot' ? 'selected' : '').'>Telegram Бот</option>
    <option value="chat" '.(($config_board_telegram['type'] ?? '') === 'chat' ? 'selected' : '').'>Telegram Чат</option>
    <option value="groups" '.(($config_board_telegram['type'] ?? '') === 'groups' ? 'selected' : '').'>Телеграм група, категорія</option>
</select>
<div id="formBot" style="display:none; margin-top:10px;">
    <label>Ід користувачів (через кому)<br>
        <input name="userid" type="text" class="input1" value="'.htmlspecialchars($config_board_telegram['userid'] ?? '').'">
    </label>
</div>
<div id="formChat" style="display:none; margin-top:10px;">
    <label>Chatid<br>
        <input name="chatid_chat" type="text" class="input1" value="'.htmlspecialchars($config_board_telegram['type'] === 'chat' ? ($config_board_telegram['chatid'] ?? '') : '').'">
    </label>
</div>
<div id="formGroups" style="display:none; margin-top:10px;">
    <label>Chatid<br>
        <input name="chatid_groups" type="text" class="input1" value="'.htmlspecialchars($config_board_telegram['type'] === 'groups' ? ($config_board_telegram['chatid'] ?? '') : '').'">
    </label><br>
    <label>MessageID<br>
        <input name="messageid" type="text" class="input1" value="'.htmlspecialchars($config_board_telegram['messageid'] ?? '').'">
    </label>
</div>
<label style="margin-top:10px;">Токен бота<br>
    <input name="token" type="text" class="input1" value="'.htmlspecialchars($config_board_telegram['token'] ?? '').'">
</label>
<button type="submit" style="margin-top:10px;">Зберегти</button>
</form>
<script>
function showForm() {
    var val = document.getElementById("type").value;
    document.getElementById("formBot").style.display = (val === "bot") ? "block" : "none";
    document.getElementById("formChat").style.display = (val === "chat") ? "block" : "none";
    document.getElementById("formGroups").style.display = (val === "groups") ? "block" : "none";
}
window.onload = showForm;
</script>
';
$stmt = $pdo->prepare("SELECT value FROM config WHERE name = ?");
$stmt->execute(['template_down_surveillance_telegram']);
$template_new = $stmt->fetchColumn();
if(empty($template_new)){
$template_message = "
[icon-warning][b]Пристрій не в мережі[/b] 
{name}";
}else{
	$template_message = $template_new;
}
$content_form_new = '
<form action="/?do=board" method="post" id="formadd">
<input name="act" type="hidden" value="template">
<input name="types" type="hidden" value="down">
<b>Шаблон виключення:</b>
<textarea id="content" name="content" class="input_note" style="height:100px;">'.htmlspecialchars($template_message).'
</textarea>
<div class="polebtn">
	<button type="submit" form="formadd" value="submit">'.$lang['update'].'</button>
</div>
</form>
';
$stmt = $pdo->prepare("SELECT value FROM config WHERE name = ?");
$stmt->execute(['template_up_surveillance_telegram']);
$template_end = $stmt->fetchColumn();
if(empty($template_end)){
$template_message_end = "
[icon-super][b]Відновлено роботу[/b]
{name}
";
}else{
	$template_message_end = $template_end;
}
$content_form_end = '
<form action="/?do=board" method="post" id="formedit">
<input name="act" type="hidden" value="template">
<input name="types" type="hidden" value="up">
<b>Шаблон включення</b> <br>
<textarea id="content" name="content" class="input_note" style="height:100px;">'.htmlspecialchars($template_message_end).'
</textarea>
<div class="polebtn">
	<button type="submit" form="formedit" value="submit">'.$lang['update'].'</button>
</div>
</form>
';
$content .= "
	<div class='pmon_block'>
		<div class='pmon_block_left pre50 board_time_details'>
		<div class='block_white'>
		{$content_form}
		</div>
		</div>
		<div class='pmon_block_right pre50'>
			<div class='block_white'>
				{$content_form_new}
			</div>
			<div class='block_white'>
			{$content_form_end}
			</div>
		</div>
	</div>
";
?>
