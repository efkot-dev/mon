<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(isset($act) && $act=='generator'){
    $category_id = intval($_POST['category']);
	$icon = isset($_POST['icon']) ? Clean::text($_POST['icon']): '';
    $employees = isset($_POST['employees']) ? $_POST['employees'] : [];
    $max_per_day = intval($_POST['max_per_day']);
    $max_consecutive_days = intval($_POST['max_consecutive_days']);
    $start_date = new DateTime($_POST['start_date']);
    $end_date = new DateTime($_POST['end_date']);
    if (empty($employees) || $max_per_day < 2 || $max_consecutive_days < 1) {
        die('Помилка: неповні дані.');
    }
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));
    $assignments = [];
    foreach ($employees as $employee) {
        $assignments[$employee] = 0;
    }
    foreach ($period as $date) {
        $assigned_today = 0;
        shuffle($employees);
        foreach ($employees as $employee_id) {
            if ($assignments[$employee_id] < $max_consecutive_days && $assigned_today < $max_per_day) {
                $db->query("INSERT INTO calendar_events 
                    (`employee_id`, `category_id`, `start_date`, `end_date`, `icon`) VALUES 
                    ('{$employee_id}', '{$category_id}', '{$date->format('Y-m-d')}', '{$date->format('Y-m-d')}', '{$icon}');");
                $assignments[$employee_id]++;
                $assigned_today++;
                if ($assigned_today >= $max_per_day) break;
            } else {
                $assignments[$employee_id] = 0;
            }
        }
    }
	$go->redirect('calendar');
	exit;
}
if ($access->get('calendar')){
require ENGINE_DIR.'functions/calendar.php';
$metatags = ['title'=>$lang['calendar'],'description'=>$lang['calendar'],'page'=>'calendar'];
$selected_cat = isset($_GET['categories']) && is_array($_GET['categories']) ? array_map('intval', $_GET['categories']) : [];
$selected_emp = isset($_GET['employees']) && is_array($_GET['employees']) ? array_map('intval', $_GET['employees']) : [];
$breadcrumb = '';
$result = '';
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$breadcrumb = '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['calendar'].'</span>';
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
$navigation = '<div class="calendar-navigation"><span>'.$year.'-'.$month.'</span><a href="?do=calendar&month='.$prevMonth.'&year='.$prevYear.'">'.$lang['last_m'].'</a> |
<a href="?do=calendar&month='.$nextMonth.'&year='.$nextYear.'">'.$lang['next_m'].'</a></div>';
$calendar = generateCalendar($month, $year, $db, $selected_cat, $selected_emp);		
$result = $navigation . $calendar . $navigation;
$sql_calendar_categories = $db->SimpleWhile("SELECT * FROM calendar_categories");
$sql_calendar_employees = $db->SimpleWhile("SELECT id, username, name, class FROM users");
$select_form = '<form method="get" id="filterForm">';
$select_form .= '<label>'.$lang['calendar_cat'].':';
if ($access->get('edit_calendar') ){
	$select_form .= '<span onclick="calendar(\'category\')">['.$lang['add_calendar'].']</span>';
}
$select_form .= '</label> ';
$select_form .= '<div class="pole-user"><center>';
$select_form .= '<a href="#" class="data" id="selectAllCategories">Вибрати всі</a> ';
$select_form .= '<a href="#" class="data" id="deselectAllCategories">Зняти всі</a>';
$select_form .= '</center></div>
<div class="form_input_list">';
foreach ($sql_calendar_categories as $category) {
    $isChecked = empty($selected_cat) ? 'checked' : (in_array($category['id'], $selected_cat) ? 'checked' : '');
    $select_form .= '<span class="form_input"><input type="checkbox" name="categories[]" value="' . $category['id'] . '" ' . $isChecked . '><span '.day_color($category['color']).'>' . $category['name'] . '</span>';
	if ($access->get('edit_calendar') ){
		$select_form .= '<img onclick="deletecalendar(\'category\',' . $category['id'] . ',\'Ви дійсно хочете видалити категорію?\')" src="../style/img/close.png">';
	}
	$select_form .= '</span>';
}
$select_form .= '</div>';
$select_form .= '<label>'.$lang['calendar_usr'].':';
if ($access->get('edit_calendar') ){
	$select_form .= '<span onclick="calendar(\'auto\')">['.$lang['add_calendar'].']</span>';
	$select_form .= '';
}
$select_form .= '</label> ';
$select_form .= '<div class="pole-user"><center>';
$select_form .= '<a href="#" class="data" id="selectAllEmployees">Вибрати всі</a> ';
$select_form .= '<a href="#" class="data" id="deselectAllEmployees">Зняти всі</a>';
$select_form .= '</center></div>';
$select_form .= '<div class="form_input_list">';
foreach ($sql_calendar_employees as $employee) {
    $isChecked = empty($selected_emp) ? 'checked' : (in_array($employee['id'], $selected_emp) ? 'checked' : '');
    $select_form .= '<span class="form_input"><input type="checkbox" name="employees[]" value="' . $employee['id'] . '" ' . $isChecked . '> ' . (empty($employee['name']) ? $employee['username'] : formatPib($employee['name'])) . '';
	if ($access->get('edit_calendar') ){
		$select_form .= '<i>Оператор</i>';
	}
	$select_form .= '</span>';
}
$select_form .= '</div>';
$select_form .= '<input type="hidden" name="do" value="calendar">';
$select_form .= '<input type="submit" class="mt20" value="'.$lang['calendar_search'].'">';
if((isset($selected_cat) && !empty($selected_cat))  || (isset($selected_emp) && !empty($selected_emp))){
$select_form .= '<br><a href="/?do=calendar"><img src="../style/img/close.png">'.$lang['reset6'].'</a>';	
}
$select_form .= '</form>';
$pager = '
<div class="mainadmin">
<div id="onu-speedbar">
    <a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>'.$breadcrumb.'
    </div>
</div>
<div id="calendar">
    <div class="calendar-menu">
        '.$select_form.'
    </div>
    <div class="calendar-right">
        '.$result.'
    </div>
</div>
<script>
document.getElementById(\'selectAllCategories\').addEventListener(\'click\', function() {
    document.querySelectorAll(\'input[name="categories[]"]\').forEach(el => el.checked = true);
});
document.getElementById(\'deselectAllCategories\').addEventListener(\'click\', function() {
    document.querySelectorAll(\'input[name="categories[]"]\').forEach(el => el.checked = false);
});
document.getElementById(\'selectAllEmployees\').addEventListener(\'click\', function() {
    document.querySelectorAll(\'input[name="employees[]"]\').forEach(el => el.checked = true);
});
document.getElementById(\'deselectAllEmployees\').addEventListener(\'click\', function() {
    document.querySelectorAll(\'input[name="employees[]"]\').forEach(el => el.checked = false);
});
</script>
';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$pager);
$tpl->compile('content');
$tpl->clear();
}else{
	$go->redirect('main');
}
?>