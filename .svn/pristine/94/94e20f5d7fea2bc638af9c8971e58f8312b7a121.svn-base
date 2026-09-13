<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_edit')) {
    $go->go('/?do=board');
    exit;
}
if (isset($_POST['types'], $_POST['content'])) {
    $types = Clean::text($_POST['types']);
    $new_content = trim((string)$_POST['content']);
    $allowed_types = [
        'new' => 'template_new_border_telegram',
        'end' => 'template_end_border_telegram'
    ];
    if (isset($allowed_types[$types]) && $new_content !== '') {
        $name = $allowed_types[$types];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM config WHERE name = ?");
        $stmt->execute([$name]);
        $exists = $stmt->fetchColumn();
		if ($exists) {
            $stmt = $pdo->prepare("UPDATE config SET value = ? WHERE name = ?");
            $stmt->execute([$new_content, $name]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO config (name, value) VALUES (?, ?)");
            $stmt->execute([$name, $new_content]);
        }
    }
}
$go->go('/?do=board&act=config');
exit;
?>
