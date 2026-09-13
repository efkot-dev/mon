<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$auto = true;
$metatags = [
    'title' => 'Підтверджені акти','description' => 'Підтверджені акти','page' => 'notconfirmend'
];
$stmt = $pdo->query("SELECT id, name FROM sklad_jobs");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$jobsById = [];
foreach ($rows as $row) {
    $jobsById[$row['id']] = $row;
}
$users = $pdo->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : '';
$moderator_id = isset($_GET['moderator_id']) ? (int)$_GET['moderator_id'] : '';
$allowedSortColumns = ['id', 'jobs', 'name', 'created_date'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSortColumns) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc']) ? strtoupper($_GET['order']) : 'DESC';
$jobs = isset($_GET['jobs']) ? (int)$_GET['jobs'] : '';
$date_filter = '';
$params = [];
if (!empty($date_from) && !empty($date_to)) {
    $date_filter .= " AND a.created_date BETWEEN :date_from AND :date_to";
    $params[':date_from'] = $date_from;
    $params[':date_to'] = $date_to;
}
if (!empty($user_id)) {
    $date_filter .= " AND a.user_id = :user_id";
    $params[':user_id'] = $user_id;
}
if (!empty($moderator_id)) {
    $date_filter .= " AND a.moderator_id = :moderator_id";
    $params[':moderator_id'] = $moderator_id;
}
if (!empty($jobs)) {
    $date_filter .= " AND a.jobs = :jobs";
    $params[':jobs'] = $jobs;
}
$sql_count = $pdo->prepare("SELECT COUNT(*) FROM sklad_akt a WHERE a.moderation = 'yes' $date_filter");
$sql_count->execute($params);
$total_rows = $sql_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);
$sql = "
    SELECT 
        a.id, 
        a.jobs, 
        a.name, 
        a.note, 
        a.user_id, 
        a.moderator_id, 
        a.created_date, 
        COUNT(t.id) AS count_tovar, 
        SUM(CASE WHEN t.score = 'no' THEN s.price * t.count ELSE 0 END) AS total_sum,
        u.username, 
        u.class,
        m.username AS moderator_name
    FROM sklad_akt a
    LEFT JOIN sklad_akt_tovar t ON a.id = t.akt_id
    LEFT JOIN sklad_tovar s ON t.p_id = s.id
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN users m ON a.moderator_id = m.id
    WHERE a.moderation = 'yes' $date_filter
    GROUP BY a.id
    ORDER BY a.$sort $order
    LIMIT :limit OFFSET :offset
";
$sql_not_ = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $sql_not_->bindValue($key, $val);
}
$sql_not_->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$sql_not_->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$sql_not_->execute();
$not_confirmend = $sql_not_->fetchAll(PDO::FETCH_ASSOC);
$url_module = '/?do=tmc&act=confirmed';
$queryParams = $_GET;
unset($queryParams['page']); // сторінка додається окремо
function buildSortUrl($column, $currentSort, $currentOrder, $queryParams, $url_module) {
    $order = 'ASC';
    if ($currentSort === $column) {
        $order = $currentOrder === 'ASC' ? 'DESC' : 'ASC';
    }
    $queryParams['sort'] = $column;
    $queryParams['order'] = $order;
    $query = http_build_query($queryParams);
    return $url_module . '&' . $query;
}
$content = '<style>
.sort_sklad label {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    height: 32px;
    margin-right: 10px;
}
.sort_sklad {
    display: flex;
    flex-direction: row;
    width: 100%;
    justify-content: flex-start;
    align-items: flex-start;
    padding: 5px 0;
    margin-bottom: 5px;
}
</style>
<form method="GET">'
    . '<input type="hidden" name="do" value="tmc">'  
    . '<input type="hidden" name="act" value="confirmed">'
    . '<div class="sort_sklad">'  
    . '<label>Дата від: <input type="date" name="date_from" value="'.htmlspecialchars($date_from).'"></label>'
    . '<label>Дата до: <input type="date" name="date_to" value="'.htmlspecialchars($date_to).'"></label>'
    . '<label>Автор акт: <select name="user_id"><option value=""></option>';
foreach ($users as $user) {
    $selected = ($user['id'] == $user_id) ? ' selected' : '';
    $content .= '<option value="'.$user['id'].'"'.$selected.'>'.htmlspecialchars($user['username']).'</option>';
}
$content .= '</select></label>'
    . '<label>Ревізор: <select name="moderator_id"><option value=""></option>';
foreach ($users as $user) {
    $selected = ($user['id'] == $moderator_id) ? ' selected' : '';
    $content .= '<option value="'.$user['id'].'"'.$selected.'>'.htmlspecialchars($user['username']).'</option>';
}
$content .= '</select></label>'
    . '<button type="submit">Фільтрувати</button>'
    . '</form>'
    . '</div>';
$content .= '<table class="resp-tab">
<thead>
<tr>
    <th width="15%"><a href="'.buildSortUrl('jobs', $sort, $order, $queryParams, $url_module).'">Тип</a></th>
    <th width="30%"><a href="'.buildSortUrl('name', $sort, $order, $queryParams, $url_module).'">'.$lang['name'].'</a></th>
    <th>Позицій</th>
    <th>Автор</th>
    <th>Звання</th>
    <th><a href="'.buildSortUrl('created_date', $sort, $order, $queryParams, $url_module).'">Дата формування</a></th>
    <th>Сума</th>
</tr>
</thead>
<tbody>';
if (!empty($not_confirmend)) {
    foreach ($not_confirmend as $akt) {
        $suma = number_format($akt['total_sum'], 2, ',', ' ');
        $content .= 
            '<tr>
                <td>'.(isset($jobsById[$akt['jobs']]) ? '<span class="offs_"><a href="'.$url_module.'&jobs='.$akt['jobs'].'">'.htmlspecialchars($jobsById[$akt['jobs']]['name']).'</a></span>' : '--').'</td>
                <td class="td_url mobile txt_left"><a href="/?do=tmc&act=moderation&id='.$akt['id'].'">№ '.$akt['id'].' </a><br>'.htmlspecialchars($akt['note']).'</td>
                <td><span class="signal2">'.(int)$akt['count_tovar'].'</span></td>
                <td>'.htmlspecialchars($akt['username']).'</td>
                <td><span class="signal3">'.htmlspecialchars(getClassUser($akt['class'])).'</span></td>
                <td><span class="on_">'.htmlspecialchars($akt['created_date']).'</span></td>
                <td><span class="on_">'.$suma.' грн</span></td>
            </tr>';
    }
} else {
    $content .= '<tr><td colspan="9">' . $lang['empty'] . '</td></tr>';
}
$content .= '</tbody></table>';
$content .= '<div class="pagination">';
for ($i = 1; $i <= $total_pages; $i++) {
    $queryParams['page'] = $i;
    $pageUrl = $url_module . '&' . http_build_query($queryParams);
    $activeClass = ($i == $page) ? ' class="active"' : '';
    $content .= '<a href="'.$pageUrl.'"'.$activeClass.'>'.$i.'</a> ';
}
$content .= '</div>';
$metatags = ['title'=> 'Архів актів','description'=>'Архів актів','page'=>'confirmed'];
$speedbar = '';
$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .='<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Архів актів</span>';
$speedbar_block = '<div id="onu-speedbar">'.$speedbar.'</div>';
?>
