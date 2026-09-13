<?php
if (!defined('PONMONITOR') && !defined('SCHEDULER')) {
    die('Hacking attempt!');
}
$content = '';
$templates_name = getAllTemplates($pdo);
if(!$templates_name){
	header('Location: ' . URL_SCHEDULER . '&act=add');
    exit;
}
$content .= '<a href="' . URL_SCHEDULER . '&act=add">Додати шаблон</a><br><br>';
$content .= '<table class="resp-tab" width="100%"><tr>
	<th>Назва</th>
	<th>Тип запуску</th>
	<th>Група</th>
	<th>Дії</th>
</tr>';
foreach ($templates_name as $t) {
	$content .= '<tr>';
	$content .= '<td>' . $t['name'] . '</td>';
	$content .= '<td>' . $t['run_type'] . '</td>';
	$content .= '<td>' . $t['group_name'] . '</td>';
	$content .= '<td>
		<a href="' . URL_SCHEDULER . '&act=edit&id=' . $t['id'] . '">Редагувати</a> |
		<a href="' . URL_SCHEDULER . '&act=delete&id=' . $t['id'] . '" onclick="return confirm(\'Видалити?\')">Видалити</a>
	</td>';
	$content .= '</tr>';
}
$content .= '</table>';
?>