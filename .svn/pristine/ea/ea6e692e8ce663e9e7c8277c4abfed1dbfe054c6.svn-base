<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

if (!function_exists('sklad_h')) {
    function sklad_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$auto = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim((string)($_POST['name'] ?? ''));
    $iban = trim((string)($_POST['iban'] ?? ''));
    $code = trim((string)($_POST['code'] ?? ''));
    $codefo = trim((string)($_POST['codefo'] ?? ''));
    $usrpib = trim((string)($_POST['usrpib'] ?? ''));
    $mob = trim((string)($_POST['mob'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $site = trim((string)($_POST['site'] ?? ''));
    $mail = trim((string)($_POST['mail'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));
    $status = isset($_POST['status']) ? 1 : 0;

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE sklad_services SET name=?, iban=?, code=?, codefo=?, usrpib=?, mob=?, address=?, site=?, mail=?, note=?, status=? WHERE id=?");
        $stmt->execute([$name, $iban, $code, $codefo, $usrpib, $mob, $address, $site, $mail, $note, $status, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO sklad_services (name, iban, code, codefo, usrpib, mob, address, site, mail, note, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $iban, $code, $codefo, $usrpib, $mob, $address, $site, $mail, $note, $status]);
    }

    header('Location: /?do=tmc&act=services');
    exit;
}

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM sklad_services WHERE id = ?');
        $stmt->execute([$id]);
    }
    header('Location: /?do=tmc&act=services');
    exit;
}

$stmt = $pdo->query('SELECT * FROM sklad_services ORDER BY name ASC');
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
$editService = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM sklad_services WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $editService = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!isset($_GET['add']) && !isset($_GET['edit'])) {
    $countStmt = $pdo->prepare('SELECT COUNT(id) FROM sklad_tovar_ser WHERE services_id = :sid');

    $content .= '<table class="resp-tab"><thead><tr>
    <th width="35%">Назва</th>
    <th>Телефон</th>
    <th width="20%">Адреса</th>
    <th>На сервісі</th>
    <th>Статус</th>
    <th>Керування</th>
    </tr></thead><tbody>';

    if (!empty($services)) {
        foreach ($services as $service) {
            $status = !empty($service['status']) ? '<span class="greens">Активний</span>' : '<span class="loses">Неактивний</span>';

            $countStmt->execute([':sid' => (int)$service['id']]);
            $count = (int)$countStmt->fetchColumn();
            $count_not_conf = '';
            if ($count === 1) {
                $count_not_conf = '<span class="greens">1</span>';
            } elseif ($count > 1) {
                $count_not_conf = '<span class="loses">' . $count . '</span>';
            }

            $content .= '<tr>
                <td><b>' . sklad_h($service['name']) . '</b></td>
                <td>' . sklad_h($service['mob']) . '</td>
                <td>' . sklad_h($service['address']) . '</td>
                <td>' . $count_not_conf . '</td>
                <td>' . $status . '</td>
                <td>
                    <a href="/?do=tmc&act=services&edit=' . (int)$service['id'] . '"><img src="../style/img/edit.png"></a>
                    <a href="/?do=tmc&act=services&del=' . (int)$service['id'] . '" onclick="return confirm(\'Видалити цей сервісний центр?\')">
                        <img src="../style/img/delet.png">
                    </a>
                </td>
            </tr>';
        }
    } else {
        $content .= '<tr><td colspan="6">' . $lang['empty'] . '</td></tr>';
    }

    $content .= '</tbody></table>';
    $content .= '<div class="pole"><a href="/?do=tmc&act=services&add"><span class="reger_btn">Додати сервісний центр</span></a></div>';
    $name_bar = 'Сервісні центри';
} else {
    $name_bar = ($editService ? 'Редагування сервісного центру' : 'Додати сервісний центр');

    $content .= '<form action="/?do=tmc&act=services" method="post">
        <input type="hidden" name="id" value="' . ($editService ? (int)$editService['id'] : '') . '">
        <div class="main_provider">';

    $fields = [
        'name' => 'Назва',
        'iban' => 'IBAN',
        'code' => 'Код',
        'codefo' => 'Код ФО',
        'usrpib' => 'ФО-ПІБ',
        'mob' => 'Телефон',
        'address' => 'Адреса',
        'site' => 'Сайт',
        'mail' => 'Email',
    ];

    foreach ($fields as $field => $label) {
        $val = $editService[$field] ?? '';
        $content .= '<div class="form-provider">
            <label for="' . $field . '">' . $label . ': </label>
            <input class="wi300" type="text" name="' . $field . '" value="' . sklad_h($val) . '">
        </div>';
    }

    $content .= '<div class="form-provider">
        <label for="note">Примітка: </label>
        <textarea name="note" class="input1" rows="7" style="width: 50%;">' . sklad_h($editService['note'] ?? '') . '</textarea>
    </div>';

    $content .= '<div class="form-provider">
        <label for="status">Статус: </label>
        <input type="checkbox" name="status" ' . (!empty($editService['status']) ? 'checked' : '') . '> Активний
    </div>';

    $content .= '</div><input type="submit" value="' . ($editService ? 'Зберегти' : 'Додати') . '"></form>';
}

$metatags = ['title' => 'Сервісні центри', 'description' => 'Сервісні центри', 'page' => 'services'];
$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
if (isset($_GET['add']) || isset($_GET['edit'])) {
    $speedbar .= '<a class="brmhref" href="/?do=tmc&act=services"><i class="fi fi-rr-angle-left"></i>Сервісні центри</a>';
}
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$name_bar.'</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';
?>
