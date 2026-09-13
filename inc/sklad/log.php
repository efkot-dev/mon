<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$metatags = array(
'title'=>$lang['log'].' складу',
'description'=>$lang['log'],
'page'=>'log');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = min(100, max(5, (int)($_GET['per_page'] ?? 20)));
$filterAction = isset($_GET['action']) && $_GET['action']     !== '' ? $_GET['action'] : null;
$filterProduct  = isset($_GET['product_id']) && (int)$_GET['product_id'] > 0 ? (int)$_GET['product_id'] : null;
$filterUser = isset($_GET['user_id']) && (int)$_GET['user_id'] > 0    ? (int)$_GET['user_id']    : null;
$filterAkt = isset($_GET['akt_id']) && (int)$_GET['akt_id']  > 0    ? (int)$_GET['akt_id']     : null;
$where  = [];
$params = [];
if ($filterAction) { $where[] = 'l.action = :action';       $params[':action']     = $filterAction; }
if ($filterProduct) { $where[] = 'l.product_id = :product_id';$params[':product_id'] = $filterProduct; }
if ($filterAkt) { $where[] = 'l.akt_id = :akt_id';        $params[':akt_id']     = $filterAkt; }
if ($filterUser) {
    $where[] = '(l.to_user_id = :uid OR l.from_user_id = :uid OR l.actor_id = :uid)';
    $params[':uid'] = $filterUser;
}
$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';
$sqlCount = "SELECT COUNT(*) AS c FROM sklad_log l $whereSql";
$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages  = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;
$sql = "
SELECT 
  l.id, l.action, l.product_id, l.from_user_id, l.to_user_id, l.quantity, l.actor_id, l.created_at,
  l.akt_id,
  p.name AS product_name,
  a.name AS akt_name, a.note AS akt_note,
  uf.username AS from_username, uf.name AS from_name,
  ut.username AS to_username,   ut.name AS to_name,
  ua.username AS actor_username,ua.name AS actor_name
FROM sklad_log l
LEFT JOIN sklad_tovar p ON p.id = l.product_id
LEFT JOIN users uf ON uf.id = l.from_user_id
LEFT JOIN users ut ON ut.id = l.to_user_id
LEFT JOIN users ua ON ua.id = l.actor_id
LEFT JOIN sklad_akt a ON a.id = l.akt_id
$whereSql
ORDER BY l.created_at DESC, l.id DESC
LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$actLabel = [
  'add' => 'Додано',
  'transfer' => 'Переміщено',
  'writeoff' => 'Списано',
  'return' => 'Повернення',
  'move' => 'Переміщено як неробоче',
  'edit' => 'Редагування акта',
  'akt' => 'Акт',
];
function userLabel($id, $name, $username) {
    if ($id === null || (int)$id === 0) return 'Склад';
    $n = trim((string)$name);
    $u = trim((string)$username);
    if ($n !== '') return $n . ' (ID ' . (int)$id . ')';
    if ($u !== '') return $u . ' (ID ' . (int)$id . ')';
    return 'Користувач ID ' . (int)$id;
}
function buildUrl(array $overrides = []) {
    $q = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null) { unset($q[$k]); }
        else { $q[$k] = $v; }
    }
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    return $baseUrl . '?' . http_build_query($q);
}
$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['log'].' складу</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';

$content .= '
<div class="pmon_block" id="board_fault">
<div class="pmon_block_left pre20">';
$content .= '<form method="get" style="margin-bottom:8px">';
foreach (['do','act'] as $keep) { if (isset($_GET[$keep])) $content .= '<input type="hidden" name="'.h($keep).'" value="'.h($_GET[$keep]).'">'; }
$content .= 'Товар ID: <input type="number" name="product_id" value="'.h($filterProduct).'"> ';
$content .= 'Користувач ID: <input type="number" name="user_id" value="'.h($filterUser).'"> ';
$content .= 'Акт ID: <input type="number" name="akt_id" value="'.h($filterAkt).'"> ';
$content .= 'Дія: <select name="action">';
$actions = ['' => '— всі —', 'add'=>'Додано', 'transfer'=>'Переміщено', 'writeoff'=>'Списано', 'return'=>'Повернення', 'move'=>'Переміщено як неробоче', 'edit'=>'Редагування акта', 'akt'=>'Акт'];
foreach ($actions as $val=>$labelText) {
  $sel = ($filterAction === $val) ? ' selected' : '';
  $content .= '<option value="'.h($val).'"'.$sel.'>'.h($labelText).'</option>';
}
$content .= '</select> ';
$content .= 'На сторінці: <input type="number" style="width:80px;" name="per_page" min="5" max="100" value="'.h($perPage).'"> ';
$content .= '<br><button type="submit">Фільтрувати</button>';
$content .= '</form>';
$content .= '
</div>
<div class="pmon_block_right pre80">';
/*
$content .= '<div class="quick-links" style="margin-bottom:8px">';
$content .= '<a href="'.h(buildUrl(['product_id'=>null,'user_id'=>null,'action'=>null,'akt_id'=>null,'page'=>1])).'">Весь склад</a> • ';
if ($filterProduct) {
    $content .= '<a href="'.h(buildUrl(['action'=>null,'akt_id'=>null,'page'=>1])).'">Вся історія товару</a> • ';
}
$content .= '<a href="'.h(buildUrl(['action'=>'transfer','page'=>1])).'">Тільки переміщення</a> • ';
$content .= '<a href="'.h(buildUrl(['action'=>'writeoff','page'=>1])).'">Тільки списання</a> • ';
$content .= '<a href="'.h(buildUrl(['action'=>'add','page'=>1])).'">Тільки прихід</a> • ';
$content .= '<a href="'.h(buildUrl(['action'=>'akt','page'=>1])).'">Тільки акти</a>';
$content .= '</div>';
*/
#$content .= '<div class="log-summary">Знайдено: '.h($total).'</div>';
$content .= '<table class="table log-table" border="0" cellspacing="0" cellpadding="6">';
$content .= '<thead><tr>
        <th>ID</th>
        <th>Дата/час</th>
        <th>Дія</th>
        <th>Товар</th>
        <th>Звідки → Куди</th>
        <th>К-сть</th>
        <th>Акт</th>
        <th>Виконав</th>
      </tr></thead><tbody>';
if (!$rows) {
    $content .= '<tr><td colspan="8" style="text-align:center">Порожньо</td></tr>';
} else {
    foreach ($rows as $r) {
        $from = userLabel($r['from_user_id'], $r['from_name'], $r['from_username']);
        $to = userLabel($r['to_user_id'], $r['to_name'], $r['to_username']);
        $who = userLabel($r['actor_id'], $r['actor_name'], $r['actor_username']);
        $act = $actLabel[$r['action']] ?? $r['action'];
        $prod = ($r['product_name'] !== null && $r['product_name'] !== '')
              ? $r['product_name'].' (ID '.$r['product_id'].')'
              : (($r['product_id']>0) ? 'Товар ID '.$r['product_id'] : '—');
        $prodLink = ($r['product_id']>0)
            ? '<a href="'.h(buildUrl(['product_id'=>$r['product_id'],'page'=>1])).'">'.h($prod).'</a>'
            : h($prod);
        $aktCell = $r['akt_id'] ? (
            '<a href="'.h(buildUrl(['akt_id'=>$r['akt_id'],'page'=>1])).'">Акт #'.h($r['akt_id']).'</a>'
            .($r['akt_name'] ? ' — '.h($r['akt_name']) : '')
        ) : '—';
        $content .= '<tr>';
        $content .= '<td>'.h($r['id']).'</td>';
        $content .= '<td>'.h($r['created_at']).'</td>';
        $content .= '<td>'.h($act).'</td>';
        $content .= '<td>'.$prodLink.'</td>';
        $content .= '<td>'.h($from).' &nbsp;→&nbsp; '.h($to).'</td>';
        $content .= '<td>'.h($r['quantity']).'</td>';
        $content .= '<td>'.$aktCell.'</td>';
        $content .= '<td>'.h($who).'</td>';
        $content .= '</tr>';
    }
}
$content .= '</tbody></table>';
$q = $_GET; 
unset($q['page']);
$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
$qs = http_build_query($q);
$content .= '<div class="pagination">';
if ($page > 1) {
    $prev = $page - 1;
    $linkPrev = $baseUrl.'?'.($qs ? $qs.'&' : '').'page='.$prev.'&per_page='.$perPage;
    $content .= '<a href="'.h($linkPrev).'">« Назад</a> ';
} else {
    $content .= '<span class="muted">« Назад</span> ';
}
$content .= ' Сторінка '.h($page).' з '.h($pages).' ';
if ($page < $pages) {
    $next = $page + 1;
    $linkNext = $baseUrl.'?'.($qs ? $qs.'&' : '').'page='.$next.'&per_page='.$perPage;
    $content .= ' <a href="'.h($linkNext).'">Вперед »</a>';
} else {
    $content .= ' <span class="muted">Вперед »</span>';
}
$content .= '</div>

</div></div>';
?>
