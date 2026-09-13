<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}

function day_color($color) {
    if(isset($color)){
		$colors = explode('|', $color);
		if (count($colors) === 2) {
			$background_color = trim($colors[0]);
			$text_color = trim($colors[1]);
			$color_pattern = '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$|^[a-zA-Z]+$/';
			if (preg_match($color_pattern, $background_color) && preg_match($color_pattern, $text_color)) {
				return 'style="background:' . $background_color . ';color:' . $text_color . ';"';
			}
		}
	}
	return 'style="color:#222;background-color:#eee;"';
}

function generateCalendar($month, $year, $db, $selectedCategories = [], $selectedEmployees = []) {
	global $lang;
	
    $daysInMonth = date('t', strtotime("$year-$month-01"));
    $firstDayOfWeek = date('N', strtotime("$year-$month-01"));
    $calendar = '<table border="0" width="100%" cellpadding="0" cellspacing="0" class="calendar">';
    $calendar .= '<tr>';

    $daysOfWeek = [$lang['week_1'], $lang['week_2'], $lang['week_3'], $lang['week_4'], $lang['week_5'], $lang['week_6'], $lang['week_7']];
    foreach ($daysOfWeek as $day) {
        $calendar .= "<th>$day</th>";
    }
    $calendar .= '</tr><tr>';

    if ($firstDayOfWeek > 1) {
        $calendar .= str_repeat('<td></td>', $firstDayOfWeek - 1);
    }

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $dayOfWeek = date('N', strtotime($date));

        $class = '';
        if ($dayOfWeek == 6) {
            $class = 'saturday';
        } elseif ($dayOfWeek == 7) {
            $class = 'sunday';
        }

        $categoryFilter = !empty($selectedCategories) ? 'AND ev.category_id IN (' . implode(',', $selectedCategories) . ')' : '';
        $employeeFilter = !empty($selectedEmployees) ? 'AND ev.employee_id IN (' . implode(',', $selectedEmployees) . ')' : '';
        $sql_events = $db->SimpleWhile("SELECT e.name, e.username, e.id, ev.icon as ei, c.name AS category, 
			c.color AS color, ev.id as ev_id, ev.description, ev.start_time, ev.end_time
            FROM calendar_events ev 
            JOIN users e ON ev.employee_id = e.id 
            JOIN calendar_categories c ON ev.category_id = c.id 
            WHERE '$date' BETWEEN ev.start_date AND COALESCE(ev.end_date, ev.start_date)
            $categoryFilter
            $employeeFilter
        ");
        
        $curent = ($day == date('d') && $month == date('m') ? 'curent' : 'not');
        $calendar .= "<td data-date='$date' class='color-default $class day_$curent'>";
        $calendar .= "<div class='calendar-day'>";
        $calendar .= "<div class='panel'>";
        $calendar .= "<div class='num $curent'><span onclick=\"scheduler('scheduler','$day','$month','$year')\">$day</span></div>";
        $calendar .= "</div>";
        $calendar .= "<div class='list-day'>";        
        foreach ($sql_events as $event) {
			$color = day_color($event['color']);
			$icon = (!empty($event['ei']) ? '<img src="../style/img/house-'.$event['ei'].'.png">' : '');
            $calendar .= "<div onclick=\"scheduler('view',".$event['ev_id'].")\" class='get-day' $color>{$icon}{$event['category']} ".(empty($event['name']) ? $event['username'] : formatPib($event['name']));
			if (!empty($event['start_time'])) {
				$endTime = !empty($event['end_time']) ? $event['end_time'] : null;
				$hoursWorked = fucntion_timed_work($event['start_time'], $endTime);
				$calendar .= '<div class="worker_time">' . $hoursWorked . ' год</div>';
			}
			$calendar .= "</div>";
        }
        $calendar .= "</div></div>";
        $calendar .= "</td>";

        if (($day + $firstDayOfWeek - 1) % 7 == 0) {
            $calendar .= '</tr><tr>';
        }
    }

    $calendar .= '</tr></table>';
    return $calendar;
}
?>