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
$akt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post_id = isset($_POST['akt_id']) ? (int)$_POST['akt_id'] : 0;
$reject = isset($_POST['reject']) && $_POST['reject'] === 'yes';
$confirm = isset($_POST['confirm']) && $_POST['confirm'] === 'yes';
$moderator_id = isset($USER['id']) ? (int)$USER['id'] : 0;
$canModerate = (isset($USER['class']) && (int)$USER['class'] >= 6) || (isset($access) && $access->get('manager_sklad'));

if ($post_id > 0 && ($reject || $confirm) && $canModerate) {
    try {
        $pdo->beginTransaction();

        $cat = $pdo->prepare('SELECT * FROM sklad_akt WHERE id = :id LIMIT 1 FOR UPDATE');
        $cat->execute([':id' => $post_id]);
        $result_akt = $cat->fetch(PDO::FETCH_ASSOC);

        if (!$result_akt) {
            throw new RuntimeException('Акт не знайдено');
        }

        if ($reject) {
            $recover_user_id = (int)$result_akt['user_id'];

            $itemsStmt = $pdo->prepare('SELECT id, t_id, p_id, count FROM sklad_akt_tovar WHERE akt_id = :akt_id FOR UPDATE');
            $itemsStmt->execute([':akt_id' => (int)$result_akt['id']]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $findAccById = $pdo->prepare('SELECT id, quantity FROM sklad_accounting WHERE id = :id AND user_id = :user_id AND product_id = :product_id LIMIT 1 FOR UPDATE');
            $findAccByProduct = $pdo->prepare("SELECT id, quantity FROM sklad_accounting WHERE user_id = :user_id AND product_id = :product_id AND action_type = 'assigned' LIMIT 1 FOR UPDATE");
            $updateAcc = $pdo->prepare("UPDATE sklad_accounting SET quantity = :quantity, status = 'enable' WHERE id = :id");
            $insertAcc = $pdo->prepare("INSERT INTO sklad_accounting (status, product_id, user_id, quantity, action_type) VALUES ('enable', :product_id, :user_id, :quantity, 'assigned')");

            foreach ($items as $item) {
                $qty = (float)$item['count'];
                $product_id = (int)$item['p_id'];
                $acc_id = (int)$item['t_id'];

                if ($qty <= 0 || $product_id <= 0) {
                    continue;
                }

                $row = null;
                if ($acc_id > 0) {
                    $findAccById->execute([
                        ':id' => $acc_id,
                        ':user_id' => $recover_user_id,
                        ':product_id' => $product_id,
                    ]);
                    $row = $findAccById->fetch(PDO::FETCH_ASSOC);
                }

                if (!$row) {
                    $findAccByProduct->execute([
                        ':user_id' => $recover_user_id,
                        ':product_id' => $product_id,
                    ]);
                    $row = $findAccByProduct->fetch(PDO::FETCH_ASSOC);
                }

                if ($row) {
                    $new_quantity = (float)$row['quantity'] + $qty;
                    $updateAcc->execute([
                        ':quantity' => $new_quantity,
                        ':id' => (int)$row['id'],
                    ]);
                } else {
                    $insertAcc->execute([
                        ':product_id' => $product_id,
                        ':user_id' => $recover_user_id,
                        ':quantity' => $qty,
                    ]);
                }
            }

            $delItems = $pdo->prepare('DELETE FROM sklad_akt_tovar WHERE akt_id = :akt_id');
            $delItems->execute([':akt_id' => (int)$result_akt['id']]);

            $delAkt = $pdo->prepare('DELETE FROM sklad_akt WHERE id = :id');
            $delAkt->execute([':id' => (int)$result_akt['id']]);
        }

        if ($confirm) {
            $sql_confirm = $pdo->prepare("
                UPDATE sklad_akt
                   SET moderation = 'yes',
                       moderator_id = :moderator_id,
                       checked_date = :checked_date
                 WHERE id = :id
            ");
            $sql_confirm->execute([
                ':moderator_id' => $moderator_id,
                ':checked_date' => date('Y-m-d H:i:s'),
                ':id' => (int)$result_akt['id'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    $go->go('/?do=tmc&act=notconfirmed');
    exit;
}

if ($akt_id <= 0) {
    die('Невірний ідентифікатор акта.');
}

$stmt_akt = $pdo->prepare("
    SELECT a.id, a.name, a.note, a.jobs, a.user_id, a.created_date, a.moderator_id, a.moderation, a.checked_date,
           u.username, u.class
      FROM sklad_akt a
 LEFT JOIN users u ON a.user_id = u.id
     WHERE a.id = :id
     LIMIT 1
");
$stmt_akt->execute([':id' => $akt_id]);
$akt = $stmt_akt->fetch(PDO::FETCH_ASSOC);

if (!$akt) {
    die('Акт не знайдено.');
}

$stmt_tovars = $pdo->prepare("
    SELECT t.p_id, t.count, t.score,
           s.name, s.category_id, s.sub_cat_id, s.oblik, s.unit,
           s.quantity, s.description, s.mac, s.price,
           s.date_added, s.inventory_number, s.status
      FROM sklad_akt_tovar t
 LEFT JOIN sklad_tovar s ON t.p_id = s.id
     WHERE t.akt_id = :akt_id
");
$stmt_tovars->execute([':akt_id' => $akt_id]);
$tovars = $stmt_tovars->fetchAll(PDO::FETCH_ASSOC);

$sql_jobs = $pdo->prepare('SELECT name FROM sklad_jobs WHERE id = :id LIMIT 1');
$sql_jobs->execute([':id' => (int)$akt['jobs']]);
$data_jobs = $sql_jobs->fetch(PDO::FETCH_ASSOC);
$jobName = $data_jobs ? $data_jobs['name'] : '-';

$content = "<h2>Модерація акту: №" . (int)$akt['id'] . "</h2>
            <p><b>Автор:</b> <font color='#222'>" . sklad_h($akt['username']) . "</font> (" . getClassUser($akt['class']) . ")</p>
            <p><b>Дата створення:</b> <span class='on_'>" . sklad_h($akt['created_date']) . "</span></p>
            <p><b>Тип робіт:</b> <span class='off_'>" . sklad_h($jobName) . "</span></p>
            <p><b>Опис:</b> <span class='on_'>" . sklad_h($akt['note']) . "</span></p>
            <p><b>Статус:</b> <strong>" . (($akt['moderation'] === 'yes') ? '<span class="greens">Підтверджено' : '<span class="currx">Не підтверджено') . "</span></strong></p>";

if ($akt['moderation'] === 'yes' && !empty($akt['moderator_id'])) {
    $sql_usr = $pdo->prepare('SELECT username, class FROM users WHERE id = :id LIMIT 1');
    $sql_usr->execute([':id' => (int)$akt['moderator_id']]);
    $us = $sql_usr->fetch(PDO::FETCH_ASSOC);
    if ($us) {
        $content .= "<p><b>Списання підтвердив:</b> <font color='#222'>" . sklad_h($us['username']) . "</font> (" . getClassUser($us['class']) . ")</p>";
    }
    $content .= "<p><b>Дата затвердження:</b> <span class='blues'>" . sklad_h($akt['checked_date']) . "</span></p>";
}

$content .= '<table class="resp-tab mt10 pmon_skald ">
<thead>
<tr>
    <th>№</th>
    <th>Назва</th>
    <th>Категорія</th>
    <th>К-ть</th>
    <th>Одиниця</th>
    <th>Ціна</th>
</tr>
</thead>
<tbody>';

$total_sum = 0;
if (!empty($tovars)) {
    foreach ($tovars as $index => $tovar) {
        $sum = (float)$tovar['price'] * (float)$tovar['count'];
        if (($tovar['score'] ?? '') === 'no') {
            $total_sum += $sum;
        }

        $content .= '<tr>
                <td>' . ($index + 1) . '</td>
                <td class="sklad_name_tovar"><h2>' . sklad_h($tovar['name']) . '</h2></td>
                <td>' . (int)$tovar['category_id'] . ' / ' . (int)$tovar['sub_cat_id'] . '</td>
                <td>' . (float)$tovar['count'] . '</td>
                <td>' . sklad_h($tovar['unit']) . '</td>
                <td class="sklad_price">' . number_format((float)$tovar['price'], 2) . ' грн</td>
            </tr>';
    }

    $content .= '<tr><td colspan="5" style="text-align:right;">Загальна сума:</td><td colspan="3"><strong>' . number_format($total_sum, 2) . ' грн</strong></td></tr>';
} else {
    $content .= '<tr><td colspan="10">' . $lang['empty'] . '</td></tr>';
}

$content .= '</tbody></table>';
$content .= '<form method="post" class="flex">';
$content .= '<input type="hidden" name="akt_id" value="' . (int)$akt['id'] . '">';
if ($canModerate && $akt['moderation'] !== 'yes') {
    $content .= '<button type="submit" name="confirm" class="knopkagreen" value="yes">Підтвердити</button>';
    $content .= '<button type="submit" name="reject" class="knopkared" value="yes">Відхилити</button>';
}
$content .= '</form>';

$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';

if ($akt['moderation'] === 'yes' && !empty($akt['moderator_id'])) {
    $speedbar .= '<a class="brmhref" href="/?do=tmc&act=confirmed"><i class="fi fi-rr-angle-left"></i>Архів актів</a>';
} else {
    $speedbar .= '<a class="brmhref" href="/?do=tmc&act=notconfirmed"><i class="fi fi-rr-angle-left"></i>Не підтверджені акти</a>';
}

$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>АКТ №' . (int)$akt['id'] . '</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';

$metatags = [
    'title' => 'Aкт ' . $jobName . ', №' . (int)$akt['id'],
    'description' => (string)$akt['note'],
    'page' => 'moderation',
];
?>
