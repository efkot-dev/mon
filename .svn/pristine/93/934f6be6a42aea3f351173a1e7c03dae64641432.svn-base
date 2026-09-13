<?php
if (!defined('PONMONITOR') && !defined('SCHEDULER')) {
    die('Hacking attempt!');
}
$oltid = $_POST['oltid'] ?? null;
$onuid = $_POST['onuid'] ?? null;
$templates = getAllTemplates($pdo);
if (!$templates) {
    echo '<p>Шаблонів не знайдено.</p>';
    exit;
}
echo '<div class="list-shabloniv">';
foreach ($templates as $tpl) {
    echo '<span class="template-item" 
	data-olt="'.$oltid.'" 
	data-onu="'.$onuid.'"
	data-template-id="' . $tpl['id'] . '"><i class="fi fi-rr-layers"></i>'
         . $tpl['name'] . '</span>';
}
echo '</div>';
die;
?>
