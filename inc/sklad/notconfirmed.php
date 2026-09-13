<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

$auto = true;
$metatags = [
    'title' => 'Не підтверджені акти на списання',
    'description' => 'Не підтверджені акти на списання',
    'page' => 'notconfirmend'
];

$sql_not_ = $pdo->query("
    SELECT 
        a.id, 
        a.name, 
        a.user_id, 
        a.created_date, 
        COUNT(t.id) AS count_tovar, 
        SUM(CASE WHEN t.score = 'no' THEN s.price * t.count ELSE 0 END) AS total_sum, 
        SUM(CASE WHEN t.score = 'no' THEN s.price_pdv * t.count ELSE 0 END) AS total_sum_pdv,
        u.username, 
        u.class 
    FROM sklad_akt a
    LEFT JOIN sklad_akt_tovar t ON a.id = t.akt_id
    LEFT JOIN sklad_tovar s ON t.p_id = s.id
    LEFT JOIN users u ON a.user_id = u.id
    WHERE a.moderation = 'no'
    GROUP BY a.id
");

$not_confirmend = $sql_not_->fetchAll(PDO::FETCH_ASSOC);

$content = '<table class="resp-tab">
<thead>
<tr>
    <th width="4%">№</th>
    <th width="60%">' . $lang['name'] . '</th>
    <th>К-ть</th>
    <th>Працівник</th>
    <th>Звання</th>
    <th>Дата формування</th>
    <th>Сума без ПДВ</th>
    <th>Сума з ПДВ</th>
</tr>
</thead>
<tbody>';

if (!empty($not_confirmend)) {
    foreach ($not_confirmend as $akt) {
        $suma = number_format($akt['total_sum'], 2, ',', ' ');
        $suma_pdv = number_format($akt['total_sum_pdv'], 2, ',', ' ');

        $content .= 
            '<tr>
                <td>'.$akt['id'].'</td>
                <td class="td_url mobile txt_left"><a href="/?do=tmc&act=moderation&id='.$akt['id'].'">№ '.$akt['id'].'</a></td>
                <td><span class="signal2">'.$akt['count_tovar'].'</span></td>
                <td>'.$akt['username'].'</td>
                <td><span class="signal3">'.getClassUser($akt['class']).'</span></td>
                <td><span class="on_">'.$akt['created_date'].'</span></td>
                <td><span class="on_">'.$suma.' грн</span></td>
                <td><span class="on_">'.$suma_pdv.' грн</span></td>
            </tr>';
    }
} else {
    $content .= '<tr><td colspan="8">' . $lang['empty'] . '</td></tr>';
}

$content .= '</tbody></table>';

$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>
            <a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>
            <span class="brmspan"><i class="fi fi-rr-angle-left"></i>Не підтверджені акти на списання</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';
?>
