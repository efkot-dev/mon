<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
$metatags = [
    'title' => $lang['taskman_list_task'],
    'description' => $lang['taskman_list_task'],
    'page' => 'list'
];
$pager_records = 4;
$current_page = max(1, (int)($_GET['pager'] ?? 1));
$offset = ($current_page - 1) * $pager_records;
$type_worker = getListWorker();
$listlocation = getListLocation();
$list_usr = getListUser();
$selectedUser = $_GET['usr'] ?? [];
$selectedLocations = $_GET['location'] ?? [];
$created_at = $_GET['created_at'] ?? '';
$planned_at = $_GET['planned_at'] ?? '';
$typesworker = $_GET['typesworker'] ?? '';
$task_state = $_GET['task_state'] ?? '';
$priority = $_GET['priority'] ?? '';
$description = $_GET['description'] ?? '';
$order_by = $_GET['order_by'] ?? 'created_at';

$where_data = [];
$orderby_data = [];

if (!empty($selectedLocations)) {
    $inLocation = implode(',', array_map('intval', $selectedLocations));
    $where_data[] = "locationid IN ($inLocation)";
}

if ($order_by) {
    $orderby_data[] = taskman_order_by($order_by);
}

if ($priority) {
    $where_data[] = taskman_where_priority($priority);
}

if ($typesworker) {
    $where_data[] = taskman_where_worker($typesworker);
}

if ($planned_at) {
    $where_data[] = "DATE(planned_at) = '{$planned_at}'";
}

if ($created_at) {
    $where_data[] = "DATE(created_at) = '{$created_at}'";
}

if ($task_state) {
    $where_data[] = taskman_where_status($task_state);
}

if ($description) {
    $where_data[] = "story LIKE '%$description%'";
}

$sqlwhere = sql_where($where_data);
$sqlorderby = sql_orderby($orderby_data);

$row_count = $db->NumRows('SELECT COUNT(*) as total FROM task_list ' . $sqlwhere);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $pager_records);

$sql_zaput_v_bazy = "SELECT * FROM task_list {$sqlwhere} {$sqlorderby} LIMIT {$offset}, {$pager_records}";
$sql_taskman = $db->SimpleWhile($sql_zaput_v_bazy);

$speedbar = '<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>' . $lang['taskman_main_task'] . '</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>' . $lang['taskman_list_task'] . '</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';

$tp = '<table class="resp-tab taskmans">';
$tp .= '<thead><tr><th width="5%">' . $lang['status'] . '</th><th>Опис, реєстрація</th><th width="20%">Клієнт</th><th width="15%">Заплановано на</th><th width="10%">Виконавці</th><th></th></tr></thead>';

if (!empty($sql_taskman)) {
    foreach ($sql_taskman as $taskm) {
        $res_us = '';
        $get_progress = get_progress($taskm['created_at'], $taskm['planned_at'], $taskm['status']);
        $row_comm = $db->NumRows("SELECT COUNT(*) as total FROM task_list_comment WHERE taskid = '{$taskm['id']}'");
        $total_comm = $row_comm['total'];
        if ($taskm['status'] != 2) {
            $usmasa = getListVikonavciArray($taskm['id']);
            foreach ($usmasa as $us) {
                $res_us .= (!empty($us['name']) ? '<a href="/" class="task_usr"><i class="fi fi-rr-user"></i>' . formatPib($us['name']) . '</a>' : $us['username']) . ', ';
            }
        }
        $clientid = $db->Fast('task_list_client', '*', ['id' => $taskm['clientid']]);
        $tp .= '
        <tr class="fon_' . ($get_progress['status'] == 'good' ? $get_progress['status'] : 'end_work') . '">
            <td>
                <div class="cubebl">
                    ' . ($taskm['status'] == 2 ?
                '<div class="cube creat"><i class="fi fi-rr-check"></i></div>' :
                '<div class="cube" ' . color_worker($type_worker[$taskm['typesworker']]['color']) . '><i class="fi fi-rr-time-quarter-to"></i></div>') . '
                </div>
            </td>
            <td class="taskman_detail_list">
                <a class="work" href="/?do=taskman&act=view&id=' . $taskm['id'] . '">' . $type_worker[$taskm['typesworker']]['name'] . '</a>
                ' . (isset($listlocation[$taskm['locationid']]['id']) ? '
                <a class="work_location" href="/?do=taskman' . $listlocation[$taskm['locationid']]['id'] . '">
                <i class="fi fi-rr-marker"></i><i>' . $listlocation[$taskm['locationid']]['name'] . ' ' . (isset($clientid['client_street']) ? $clientid['client_street'] : '') . ' ' . (isset($clientid['client_house']) ? $clientid['client_house'] : '') . '</i></a>
                ' : '') . '
                ' . (!empty($taskm['story']) ? '<div class="cut_task_block"><div class="cut_task">' . cut_text($taskm['story'], 300) . '</div></div>' : '') . '
                ' . (isset($total_comm) && $total_comm > 0 ? '<span class="comment-count"><i class="fi fi-rr-comment"></i><span>' . $total_comm . '</span></span>' : '') . '
            </td>
            <td><div class="block_taskman_left">
            ' . PaidType($taskm) . '
             ' . (!empty($clientid['id']) ? '<font color="#222">' . $clientid['client_pib'] . '</font>
             ' . (isset($clientid['client_mobil']) ? '<font color="blue">' . $clientid['client_mobil'] . '</font>' : '') : '') . '
            </div></td>
            <td class="calendar-task"><span class="time">' . $taskm['planned_at'] . '</span>' . ($taskm['status'] != 2 ? $get_progress['bar'] : '') . '</td>
            <td>' . $res_us . '</td>
            <td></td>
        </tr>';
    }
} else {
    $tp .= '<tr><td colspan="6">' . $lang['empty'] . '</td></tr>';
}
$tp .= '</table>';
$select_worker = '';
foreach ($type_worker as $work) {
    $select_worker .= '<option value="' . $work['id'] . '" ' . selected($typesworker, $work['id']) . '>' . $work['name'] . '</option>';
}
$form_search = '
<form action="/?do=taskman&act=list" method="get" id="form_search">
    <input type="hidden" name="do" value="taskman">
    <input type="hidden" name="act" value="list">
    <div class="block_flex_down">
        <div class="task-block">
            <label for="description" class="col-form-label">Пошук</label>
            <div class="col-sm-10 flex-right-p10">
                <input type="text" class="form-control form-control-sm" id="description" value="' . $description . '" name="description">
            </div>
            <label for="typesworker" class="col-form-label">Тип робіт</label>
            <div class="col-sm-10">
                <select name="typesworker" id="typesworker" class="form-select form-select-sm">
                    <option value="0" ' . selected($typesworker, 0) . '>Всі</option>
                    ' . $select_worker . '
                </select>
            </div>
            <label for="created_at" class="col-form-label">Дата реєстрації</label>
            <div class="col-sm-10">
                <input type="date" name="created_at" id="created_at" class="css-input" value="' . $created_at . '">
            </div>
            <label for="planned_at" class="col-form-label">Дата Виконання</label>
            <div class="col-sm-10">
                <input type="date" name="planned_at" id="planned_at" class="css-input" value="' . $planned_at . '">
            </div>
        </div>
        <div class="task-block">
            <label for="location" class="col-form-label">Локації</label>
            <div class="col-sm-10">
                <div class="checkbox-container">';
foreach ($listlocation as $id_location => $city) {
    $checked = in_array($id_location, $selectedLocations) ? 'checked' : '';
    $form_search .= '<div class="check_location"><input type="checkbox" name="location[]" value="' . $id_location . '" ' . $checked . '> ' . $city['name'] . '</div>';
}
$form_search .= '
                </div>
            </div>
        </div>
        <div class="task-block">
            <label for="task_state" class="col-form-label">Статус завдання</label>
            <div class="col-sm-10">
                <select name="task_state" id="task_state" class="form-select form-select-sm">
                    <option value="0" ' . selected($task_state, 0) . '>Всі</option>
                    <option value="1" ' . selected($task_state, 1) . '>Не виконано</option>
                    <option value="2" ' . selected($task_state, 2) . '>Виконано</option>
                    <option value="3" ' . selected($task_state, 3) . '>Виконується</option>
                    <option value="4" ' . selected($task_state, 4) . '>Відкладено</option>
                    <option value="5" ' . selected($task_state, 5) . '>Нове завдання</option>
                    <option value="6" ' . selected($task_state, 6) . '>Скасовано</option>
                    <option value="7" ' . selected($task_state, 7) . '>Всі крім виконаних</option>
                    <option value="8" ' . selected($task_state, 8) . '>Всі крім виконаних та відмінених</option>
                    <option value="9" ' . selected($task_state, 9) . '>Всі крім виконаних та відкладених</option>
                    <option value="10" ' . selected($task_state, 10) . '>Не виконані та виконуються</option>
                    <option value="11" ' . selected($task_state, 11) . '>Всі крім відмінених</option>
                </select>
            </div>
            <label for="priority" class="col-form-label">Пріоритет завдання</label>
            <div class="col-sm-10">
                <select name="priority" id="priority" class="form-select form-select-sm">
                    <option value="0" ' . selected($priority, 0) . '></option>
                    <option value="1" ' . selected($priority, 1) . '>Високий</option>
                    <option value="2" ' . selected($priority, 2) . '>Звичайний</option>
                    <option value="3" ' . selected($priority, 3) . '>Низький</option>
                </select>
            </div>
            <label for="order_by" class="col-form-label">Сортування завдання</label>
            <div class="col-sm-10">
                <select name="order_by" id="order_by" class="form-select form-select-sm">
                    <option value="none" ' . selected($order_by, 'none') . '>Виберіть</option>
                    <option value="created_at" ' . selected($order_by, 'created_at') . '>Дата створення</option>
                    <option value="updated_at" ' . selected($order_by, 'updated_at') . '>Дата оновлення</option>
                    <option value="planned_at" ' . selected($order_by, 'planned_at') . '>Дата робіт</option>
                    <option value="do_before_at" ' . selected($order_by, 'do_before_at') . '>Виконати до</option>
                    <option value="finish_at" ' . selected($order_by, 'finish_at') . '>Виконані</option>
                    <option value="priority" ' . selected($order_by, 'priority') . '>Пріоритет</option>
                </select>
            </div>
        </div>
        <div class="task-block">
            <label for="usr" class="col-form-label">Працівники</label>
            <div class="col-sm-10">
                <div class="checkbox-container">';
foreach ($list_usr as $id_usr => $usr) {
    $checked_usr = in_array($usr['userid'], $selectedUser) ? 'checked' : '';
    $form_search .= '<div class="check_location"><input type="checkbox" name="usr[]" value="' . $usr['userid'] . '" ' . $checked_usr . '>' . (!empty($usr['name']) ? formatPib($usr['name']) : $usr['username']) . '</div>';
}
$form_search .= '
                </div>
            </div>
        </div>
    </div>
    <div class="polebtn mt10 class-list">
        <a href="/?do=taskman&act=add" class="new_task">Нова</a>
        <button type="submit" form="form_search" value="submit">Фільтр</button>
    </div>
</form>';
$pager_url = "/?do=taskman&act=list";
$paginator = '<div class="pagination">';
if ($current_page > 1) {
    $paginator .= '<a href="' . $pager_url . '&pager=1">&laquo; Перша</a>';
    $paginator .= '<a href="' . $pager_url . '&pager=' . ($current_page - 1) . '">‹ Попередня</a>';
}
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == $current_page) {
        $paginator .= '<span class="current">' . $i . '</span>';
    } else {
        $paginator .= '<a href="' . $pager_url . '&pager=' . $i . '">' . $i . '</a>';
    }
}
if ($current_page < $total_pages) {
    $paginator .= '<a href="' . $pager_url . '&pager=' . ($current_page + 1) . '">Наступна ›</a>';
    $paginator .= '<a href="' . $pager_url . '&pager=' . $total_pages . '">Остання &raquo;</a>';
}
$paginator .= '</div>';

$content = '
<div class="taskman_module">
    <div class="taskman_menu">
        <div class="mb-1 block-flex-1">
            ' . $form_search . '
        </div>
    </div>
    <div class="taskman_list">
        ' . $tp . '
        ' . $paginator . '
    </div>
</div>
<div id="ajax"></div>
<div class="col-pager"></div>';
?>