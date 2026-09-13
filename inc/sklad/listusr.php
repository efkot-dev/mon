<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

if (!function_exists('sklad_h')) {
    function sklad_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$metatags = ['title'=> 'Список працівників','description'=>'Список працівників','page'=>'usr'];
$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Список працівників</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';

$sql_usr = $pdo->prepare('SELECT id, username, name, lastactivity FROM users ORDER BY username ASC');
$sql_usr->execute();
$list_users = $sql_usr->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $pdo->query("SELECT user_id, COUNT(id) AS total FROM sklad_accounting WHERE status = 'enable' GROUP BY user_id");
$userTotals = [];
foreach ($countStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $userTotals[(int)$row['user_id']] = (int)$row['total'];
}

$list_tpl = "<table id='products-column' class='resp-tab pmon_skald'><thead><tr>
<th>#UID</th>
<th>Працівник</th>
<th>Позицій у складі</th>
<th>Склад</th>
<th>Керування</th>
<th>Активність</th></tr></thead><tbody>";

if ($list_users) {
    foreach ($list_users as $usr) {
        $uid = (int)$usr['id'];
        $total = $userTotals[$uid] ?? 0;

        $list_tpl .= "<tr>
            <td><div class='us_sklad'><img src='../style/img/ava_3.png'></div></td>
            <td class='sklad_name_tovar h15'><span class='usr'>" . sklad_h($usr['username']) . "</span>
            " . (!empty($usr['name']) ? '<h4>' . sklad_h($usr['name']) . '</h4>' : '') . "</td>
            <td>" . ($total > 0 ? "<span class='on_'>{$total}</span>" : '') . "</td>
            <td>" . ($total > 0 ? "<a href='/?do=tmc&act=backpack&id={$uid}' class='usr_sklad'>Залишки матеріалів</a>" : '') . "</td>
            <td><a href='/?do=tmc&act=transfer&id={$uid}' class='usr_sklad_go'>Переміщення</a></td>
            <td>" . sklad_h($usr['lastactivity']) . "</td>
        </tr>";
    }
}

$list_tpl .= '</tbody></table>';
$content .= '<div class="container">'.$list_tpl.'</div>';
?>
