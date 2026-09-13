<?php
if (!defined('PONMONITOR') && !defined('SCHEDULER')) {
    die('Hacking attempt!');
}
$template_id = (int)($_POST['tid'] ?? 0);
$onuid = (int)($_POST['onuid'] ?? 0);
$oltid = (int)($_POST['oltid'] ?? 0);
if(empty($oltid) && empty($oltid)){
    echo '<p>Некоректний дані.</p>';
    exit;	
}
if (!$template_id) {
    echo '<p>Некоректний шаблон.</p>';
    exit;
}
$commands = getTemplateCommands($pdo, $template_id);
if (!$commands) {
    echo '<p>Команди не знайдені.</p>';
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM template_variables WHERE template_id = ?");
$stmt->execute([$template_id]);
$variables = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<form id='template_run_form'>";
foreach ($variables as $v) {
    $name = htmlspecialchars($v['name']);
    $desc = htmlspecialchars($v['description']);
    $input = '';
    switch ($v['type']) {
        case 'text':
        case 'number':
            $type = $v['type'] === 'number' ? 'number' : 'text';
            $input = "<input type='$type' name='$name' class='template-var-input' data-var='$name'>";
            break;
        case 'enum':
            $input = "<select name='$name' class='template-var-input' data-var='$name'>";
            foreach (explode('|', $v['enum_values']) as $option) {
                $option = htmlspecialchars(trim($option));
                $input .= "<option value='$option'>$option</option>";
            }
            $input .= "</select>";
            break;
    }
    echo "<div style='margin-bottom:10px;'>
            <label><b>[$name]</b> — $desc</label><br>
            $input
          </div>";
}
echo "
<button type='button' id='execute_template' style='padding:10px 20px; background:#27ae60; color:white; border:none; cursor:pointer;'>Виконати</button>
</form>
<div id='execution_result' style='padding:10px 20px;'></div>
<script>
$(document).ready(function () {
    $('#execute_template').on('click', function () {
        const form = $('#template_run_form')[0];
		$('#template_run_form').hide();
        const formData = new FormData(form);
        formData.append('tid', '$template_id');
        $('#execution_result').hide().html('<span style=\"color:blue;\">Виконується...</span>').show();
		$.ajax({
            url: '?do=scheduler&act=telnet&onuid={$onuid}&oltid={$oltid}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#execution_result').html(response);
            },
            error: function () {
                $('#execution_result').html('<span style=\"color:red;\">Помилка виконання</span>');
            }
        });
    });
});
</script>";
die;
?>