<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_view')) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
if ($limit <= 0) {
    $limit = 10;
}
if ($limit > 100) {
    $limit = 100;
}

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
if ($page <= 0) {
    $page = 1;
}

$all = trim((string)($_POST['all'] ?? ''));
$start_date = trim((string)($_POST['start_date'] ?? ''));
$end_date = trim((string)($_POST['end_date'] ?? ''));
$status = trim((string)($_POST['status'] ?? ''));

$whereParts = [];
$params = [];

if ($start_date !== '') {
    $whereParts[] = 'start_time >= ?';
    $params[] = $start_date . ' 00:00:00';
} elseif ($all === '' || $all === '0') {
    $whereParts[] = 'start_time >= NOW() - INTERVAL 36 HOUR';
}

if ($end_date !== '') {
    $whereParts[] = 'start_time <= ?';
    $params[] = $end_date . ' 23:59:59';
}

if ($status === 'closed') {
    $whereParts[] = "status = 'closed'";
} elseif ($status === 'open') {
    $whereParts[] = "status = 'open'";
}

$where = !empty($whereParts) ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

$totalSql = $pdo->prepare("SELECT COUNT(*) FROM incident {$where}");
$totalSql->execute($params);
$total = (int)$totalSql->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $limit;

$listSql = "SELECT * FROM incident {$where} ORDER BY start_time DESC LIMIT :limit OFFSET :offset";
$sql = $pdo->prepare($listSql);
foreach ($params as $idx => $param) {
    $sql->bindValue($idx + 1, $param);
}
$sql->bindValue(':limit', $limit, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$list = $sql->fetchAll(PDO::FETCH_ASSOC);

$content = '';
$now = time();

$switchByIncident = [];
$locationByIncident = [];

if (!empty($list)) {
    $incidentIds = array_map(static function ($row) {
        return (int)$row['id'];
    }, $list);

    $placeholders = implode(',', array_fill(0, count($incidentIds), '?'));

    $sqlSwitch = "
        SELECT
            ils.incident_id,
            ils.id,
            sw.place AS switch_name,
            swpon.pon AS name_pon,
            COALESCE(swport.descrport, '') AS descr
        FROM incident_log_switch ils
        LEFT JOIN switch sw ON ils.switch_id = sw.id
        LEFT JOIN switch_pon swpon ON ils.port_id = swpon.id
        LEFT JOIN switch_port swport
            ON swport.deviceid = swpon.oltid
           AND swport.llid = swpon.sfpid
        WHERE ils.incident_id IN ($placeholders)
        ORDER BY ils.id ASC
    ";
    $stmtSw = $pdo->prepare($sqlSwitch);
    $stmtSw->execute($incidentIds);
    foreach ($stmtSw->fetchAll(PDO::FETCH_ASSOC) as $sw) {
        $iid = (int)$sw['incident_id'];
        if (!isset($switchByIncident[$iid])) {
            $switchByIncident[$iid] = [];
        }
        $switchByIncident[$iid][] = $sw;
    }

    $sqlLoc = "
        SELECT
            ill.incident_id,
            ill.id,
            l.name AS location_name,
            s.name AS street_name,
            ill.house_numbers
        FROM incident_log_location ill
        LEFT JOIN location l ON ill.location_id = l.id
        LEFT JOIN location_street s ON ill.street_id = s.id
        WHERE ill.incident_id IN ($placeholders)
        ORDER BY ill.id ASC
    ";
    $stmtLoc = $pdo->prepare($sqlLoc);
    $stmtLoc->execute($incidentIds);
    foreach ($stmtLoc->fetchAll(PDO::FETCH_ASSOC) as $loc) {
        $iid = (int)$loc['incident_id'];
        if (!isset($locationByIncident[$iid])) {
            $locationByIncident[$iid] = [];
        }
        $locationByIncident[$iid][] = $loc;
    }

    foreach ($list as $board) {
        $boardId = (int)$board['id'];
        $start_time = !empty($board['start_time']) ? strtotime($board['start_time']) : 0;
        $restore_time = !empty($board['restore_time']) ? strtotime($board['restore_time']) : 0;
        $soon = $start_time > $now;

        $status_class = '';
        $row_class = '';
        if ($soon) {
            $status_class = "<div class='status_soon'><i class='fi fi-rr-clock'></i></div>";
        } elseif ($board['status'] === 'open') {
            $status_class = "<div class='status_open'><i class='fi fi-rr-settings'></i></div>";
            $row_class = ' class="active"';
        } else {
            $hours_ago = $restore_time > 0 ? (($now - $restore_time) / 3600) : 999;
            $status_class = ($hours_ago <= 6)
                ? "<div class='status_closed_fresh'><i class='fi fi-rr-check'></i></div>"
                : "<div class='status_closed_old'><i class='fi fi-rr-check'></i></div>";
        }

        $date_start = '';
        $date_end = '';
        if (!empty($board['start_time']) && !empty($board['restore_time'])) {
            $start = new DateTime($board['start_time']);
            $end = new DateTime($board['restore_time']);
            if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                $date_start = $start->format('H:i');
                $date_end = $end->format('H:i');
            } else {
                $date_start = $start->format('H:i d.m');
                $date_end = $end->format('H:i d.m');
            }
        }

        $start_display = $soon
            ? "<span class='el_soon'>{$date_start}</span>"
            : "<span class='off_'>{$date_start}</span>";
        $restore_display = $soon ? '' : "<span class=\"on_\">{$date_end}</span>";

        $content .= "<tr{$row_class}>";
        $content .= "<td class='text_center'>{$status_class}</td>";
        $content .= "<td class='text_center'>{$start_display}<br>{$restore_display}</td>";

        $reason = htmlspecialchars((string)$board['reason'], ENT_QUOTES, 'UTF-8');
        $content .= "<td class='board_href'><a href='/?do=board&act=view&id={$boardId}'>{$reason}</a>";
        $content .= loadBarTime($board['start_time'], $board['restore_time'], $board['status']);
        $content .= '</td>';

        $board_switch = '';
        if (!empty($switchByIncident[$boardId])) {
            foreach ($switchByIncident[$boardId] as $sw) {
                $switchName = htmlspecialchars((string)$sw['switch_name'], ENT_QUOTES, 'UTF-8');
                $ponName = htmlspecialchars((string)$sw['name_pon'], ENT_QUOTES, 'UTF-8');
                $descr = htmlspecialchars((string)$sw['descr'], ENT_QUOTES, 'UTF-8');
                $board_switch .= "<span class='text_locat_port'>{$switchName} {$ponName} {$descr}</span>";
            }
        }
        $content .= "<td>{$board_switch}</td>";

        $board_main = '';
        if (!empty($locationByIncident[$boardId])) {
            $loc_output = [];
            foreach ($locationByIncident[$boardId] as $loc) {
                $location_name = htmlspecialchars((string)$loc['location_name'], ENT_QUOTES, 'UTF-8');
                $loc_str = "<span class='text_locat_under'>{$location_name}";
                if (!empty($loc['street_name'])) {
                    $shortened_name = shortenStreetName((string)$loc['street_name'], 1);
                    $loc_str .= ' ' . htmlspecialchars((string)$shortened_name, ENT_QUOTES, 'UTF-8');
                }
                if (!empty($loc['house_numbers'])) {
                    $loc_str .= ' ' . htmlspecialchars((string)$loc['house_numbers'], ENT_QUOTES, 'UTF-8');
                }
                $loc_str .= '</span>';
                $loc_output[] = $loc_str;
            }
            $board_main .= implode(' ', $loc_output) . '<br>';
        }
        $content .= "<td>{$board_main}</td>";
        $content .= '</tr>';
    }
} else {
    $content = "<tr><td colspan='7'>" . $lang['empty'] . '</td></tr>';
}

$pagination_html = '';
for ($i = 1; $i <= $pages; $i++) {
    $active = ($i === $page) ? 'select_page' : '';
    $pagination_html .= "<a href='#' class='page-link {$active}' data-page='{$i}'>{$i}</a> ";
}

echo json_encode([
    'tbody' => $content,
    'pagination' => $pagination_html,
    'total' => $total,
    'page' => $page,
    'pages' => $pages
], JSON_UNESCAPED_UNICODE);
exit;
?>
