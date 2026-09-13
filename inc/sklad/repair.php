<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

if (!function_exists('sklad_h')) {
    function sklad_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim((string)($_POST['name'] ?? ''));
    $cat_id = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
    $sub_cat_id = isset($_POST['sub_cat_id']) ? (int)$_POST['sub_cat_id'] : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    $services_id = isset($_POST['services_id']) ? (int)$_POST['services_id'] : 0;
    $tovar_id = isset($_POST['tovar_id']) ? (int)$_POST['tovar_id'] : 0;
    $quantity = (float)($_POST['quantity'] ?? 0);
    $description = trim((string)($_POST['description'] ?? ''));
    $date_added = date('Y-m-d');
    $inventory_number = trim((string)($_POST['inventory_number'] ?? ''));

    if ($name === '' || $quantity <= 0 || $services_id <= 0) {
        header('Location: /?do=tmc&act=repair&add');
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE sklad_tovar_ser SET name=?, cat_id=?, sub_cat_id=?, status=?, services_id=?, tovar_id=?, quantity=?, description=?, inventory_number=? WHERE id=?");
        $stmt->execute([$name, $cat_id, $sub_cat_id, $status, $services_id, $tovar_id, $quantity, $description, $inventory_number, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO sklad_tovar_ser (name, cat_id, sub_cat_id, status, services_id, tovar_id, quantity, description, date_added, inventory_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $cat_id, $sub_cat_id, $status, $services_id, $tovar_id, $quantity, $description, $date_added, $inventory_number]);
    }

    header('Location: /?do=tmc&act=repair');
    exit;
}

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM sklad_tovar_ser WHERE id=?');
        $stmt->execute([$id]);
    }
    header('Location: /?do=tmc&act=repair');
    exit;
}

$editTovar = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM sklad_tovar_ser WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $editTovar = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!isset($_GET['add']) && !isset($_GET['edit'])) {
    $stmt = $pdo->query("
        SELECT ts.*, ss.name AS service_name
          FROM sklad_tovar_ser ts
     LEFT JOIN sklad_services ss ON ss.id = ts.services_id
      ORDER BY ts.date_added DESC, ts.id DESC
    ");
    $tovars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $content .= '<table class="resp-tab"><thead><tr>
    <th>Назва</th>
    <th>Категорія</th>
    <th>Кількість</th>
    <th>Сервісний центр</th>
    <th>Дата відправки</th>
    <th>На сервісі</th>
    <th>Інвентарний номер</th>
    <th>Керування</th>
    </tr></thead><tbody>';

    if (!empty($tovars)) {
        foreach ($tovars as $tovar) {
            $serviceName = !empty($tovar['service_name']) ? $tovar['service_name'] : '-';
            $content .= '<tr>
                <td width="35%" class="name_pon text_left"><a href="#">' . sklad_h($tovar['name']) . '</a></td>
                <td>' . (int)$tovar['cat_id'] . ' / ' . (int)$tovar['sub_cat_id'] . '</td>
                <td>' . (float)$tovar['quantity'] . '</td>
                <td><span class="inf_signal"><span class="sig2" style="font-size:13px;">' . sklad_h($serviceName) . '</span></span></td>
                <td width="11%">' . sklad_h($tovar['date_added']) . '</td>
                <td width="11%"><span class="off_">' . aftertime($tovar['date_added'] . ' 00:00:00') . '</span></td>
                <td>' . sklad_h($tovar['inventory_number']) . '</td>
                <td>
                    <a href="/?do=tmc&act=repair&edit=' . (int)$tovar['id'] . '"><img src="../style/img/edit.png"></a>
                    <a href="/?do=tmc&act=repair&del=' . (int)$tovar['id'] . '" onclick="return confirm(\'Видалити цей товар із сервісного центру?\')">
                        <img src="../style/img/delet.png">
                    </a>
                </td>
            </tr>';
        }
    } else {
        $content .= '<tr><td colspan="8">Немає товарів у сервісному центрі.</td></tr>';
    }

    $content .= '</tbody></table>';
    $content .= '<div class="pole"><a href="/?do=tmc&act=repair&add"><span class="reger_btn">Надіслати у сервіс</span></a></div>';
    $name_bar = 'Обладнання на сервісі';
} else {
    $stmt = $pdo->prepare('SELECT id, name FROM sklad_services ORDER BY name ASC');
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $serviceOptions = "<option value=''></option>";
    foreach ($services as $service) {
        $selected = ($editTovar && (int)$editTovar['services_id'] === (int)$service['id']) ? 'selected' : '';
        $serviceOptions .= "<option value='" . (int)$service['id'] . "' {$selected}>" . sklad_h($service['name']) . "</option>";
    }

    $name_bar = ($editTovar ? 'Редагування товару' : 'Додати товар у сервісний центр');

    $content .= '<form action="/?do=tmc" method="post">
        <input name="act" type="hidden" value="repair">
        <input type="hidden" name="id" value="' . ($editTovar ? (int)$editTovar['id'] : '') . '">
        <input type="hidden" name="cat_id" value="' . ($editTovar ? (int)$editTovar['cat_id'] : 0) . '">
        <input type="hidden" name="sub_cat_id" value="' . ($editTovar ? (int)$editTovar['sub_cat_id'] : 0) . '">
        <input type="hidden" name="tovar_id" value="' . ($editTovar ? (int)$editTovar['tovar_id'] : 0) . '">
        <div class="main_provider">';

    $content .= '<div class="form-provider">
        <label for="name">Назва товару: </label>
        <input class="wi300" type="text" name="name" required value="' . sklad_h($editTovar['name'] ?? '') . '">
    </div>';

    $content .= '<div class="form-provider">
        <label for="quantity">Кількість: </label>
        <input class="wi300" type="number" name="quantity" step="0.01" min="0.01" required value="' . sklad_h($editTovar['quantity'] ?? '') . '">
    </div>';

    $content .= '<div class="form-provider">
        <label for="services_id">Сервісний центр: </label>
        <select name="services_id" class="category-select" required>' . $serviceOptions . '</select>
    </div>';

    $content .= '<div class="form-provider">
        <label for="inventory_number">Інвентарний номер: </label>
        <input class="wi300" type="text" name="inventory_number" value="' . sklad_h($editTovar['inventory_number'] ?? '') . '">
    </div>';

    $content .= '<div class="form-provider">
        <label for="description">Опис проблеми: </label>
        <textarea name="description" class="input1" rows="4">' . sklad_h($editTovar['description'] ?? '') . '</textarea>
    </div>';

    $content .= '<div class="form-provider">
        <label for="status">Статус: </label>
        <input type="checkbox" name="status" ' . (!empty($editTovar['status']) ? 'checked' : '') . '> В сервісі
    </div>';

    $content .= '</div><input type="submit" value="Зберегти"></form>';
}

$metatags = ['title' => 'Сервісні центри', 'description' => 'Сервісні центри', 'page' => 'services'];
$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
if (isset($_GET['add']) || isset($_GET['edit'])) {
    $speedbar .= '<a class="brmhref" href="/?do=tmc&act=repair"><i class="fi fi-rr-angle-left"></i>Обладнання на сервісі</a>';
}
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$name_bar.'</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';
?>
