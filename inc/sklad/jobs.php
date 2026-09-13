<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$auto = false;
$metatags = ['title' => 'Список категорій', 'description' => 'Список категорій', 'page' => 'category'];
$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Типи робіт</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';
// ДОДАВАННЯ
if ($_GET['types'] == 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO sklad_jobs (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
    }
    header("Location: ?do=tmc&act=jobs");
    exit;
}
// РЕДАГУВАННЯ
if ($_GET['types'] == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $stmt = $pdo->prepare("UPDATE sklad_jobs SET name = :name WHERE id = :id");
        $stmt->execute([':name' => $name, ':id' => $id]);
        header("Location: ?do=tmc&act=jobs");
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM sklad_jobs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    $content .= "<h3>Редагування категорії</h3>
    <form method='post'>
        <input type='text' name='name' value='" . htmlspecialchars($item['name']) . "' required>
        <button type='submit'>Зберегти</button>
    </form>";
    return;
}
// ВИДАЛЕННЯ
if ($_GET['types'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM sklad_jobs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: ?do=tmc&act=jobs");
    exit;
}
// СПИСОК
$stmt = $pdo->query("SELECT * FROM sklad_jobs ORDER BY id ASC");
$listworker = $stmt->fetchAll(PDO::FETCH_ASSOC);
$content .= "
<div class='pmon_block' id='board_fault'>
	<div class='pmon_block_left pre60'>
<table class='resp-tab list-onu-olt' style='width:100%'>
    <thead>
        <tr>
            <th>ID</th>
            <th width='30%'>Назва</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>";
foreach ($listworker as $row) {
    $content .= "<tr>
        <td>{$row['id']}</td>
        <td class='description_name mobile_font'>" . htmlspecialchars($row['name']) . "</td>
        <td>
            <a class='ajax_update_btn' href='/?do=tmc&act=jobs&types=edit&id={$row['id']}'>Редагувати</a>
        </td><td>
			<a class='panel_house rr_1' href='/?do=tmc&act=jobs&types=delete&id={$row['id']}' onclick=\"return confirm('Видалити категорію?');\">Видалити</a>
        </td>
    </tr>";
}

$content .= "</tbody></table>

</div>
	<div class='pmon_block_right pre40'>
	
<div class='menu_olt_left'>
    <form method='post' action='?do=tmc&act=jobs&types=add'>
        <input type='text' name='name' placeholder='Нова категорія' required><br>
        <button type='submit' class='reger_btn'>Додати категорію</button>
    </form>
</div>
</div>
</div>
";
?>
