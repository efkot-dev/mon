<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function get_custom_bar($interval, $last_run_time) {
    $current_time = time();
    if (empty($last_run_time) || strtotime($last_run_time) === false) {
        return "<div class='progress-bar-container'>
                    <div class='progress-bar' style='width: 0%; background-color: #ccc;'></div>
                    <div class='progress-text'>No data available</div>
                </div>";
    }
    $last_run_time = strtotime($last_run_time);
    $next_run_time = $last_run_time + $interval;
    $time_remaining = $next_run_time - $current_time;
    if ($time_remaining > 0) {
        $progress = 100 - (($time_remaining / $interval) * 100);
    } else {
        $progress = 100;
    }
    return "<div class='progress-bar-container'>
                <div class='progress-bar' style='width: {$progress}%;'></div>
            </div>";
}

function get_daily_bar($last_run_time, $daily_time) {
    $current_time = time(); // Поточний час
    $last_run_time = strtotime($last_run_time); // Останній запуск у форматі timestamp
    $daily_time_today = strtotime(date('Y-m-d') . ' ' . $daily_time); // Час запуску на сьогодні
    $daily_time_tomorrow = strtotime('tomorrow ' . $daily_time); // Час запуску на завтра
    if ($last_run_time >= $daily_time_today && $last_run_time < $daily_time_tomorrow) {
        return "<div class='task-complete'>Finish</div>";
    } 
    $next_run_time = ($current_time >= $daily_time_today) 
        ? $daily_time_tomorrow 
        : $daily_time_today;

    $time_remaining = max(0, $next_run_time - $current_time); // Час, що залишився
    $total_time = $next_run_time - $last_run_time; // Загальний час для прогресу
    $progress = ($total_time > 0) 
        ? (($total_time - $time_remaining) / $total_time) * 100 
        : 0;
    $bar_color = $progress <= 20 ? 'red' : ($progress <= 50 ? 'orange' : ($progress <= 80 ? 'yellow' : '#02bc02'));
    return "<div class='progress-bar-container'>
                <div class='progress-bar' style='width: {$progress}%; background-color: {$bar_color};'></div>
            </div>";
}

function get_daily_remaining_time($last_run_time, $daily_time) {
    $current_time = time();
    $last_run_time = strtotime($last_run_time);
    $daily_time = strtotime($daily_time);
    $daily_time_today = strtotime(date('Y-m-d') . ' ' . $daily_time);
    $daily_time_tomorrow = strtotime('tomorrow ' . $daily_time);
    if ($last_run_time >= $daily_time_today && $last_run_time < $daily_time_tomorrow) {
        return '';
    }
    if ($last_run_time > $current_time) {
        $next_run_time = $last_run_time + (24 * 60 * 60);
    } else {
        $next_run_time = strtotime('tomorrow', $current_time);
        $next_run_time = strtotime(date('Y-m-d', $next_run_time) . ' ' . date('H:i', $daily_time));
    }    
    $time_remaining = $next_run_time - $current_time;
    $hours_remaining = floor($time_remaining / 3600); // годин
    $minutes_remaining = floor(($time_remaining % 3600) / 60); // хвилин
    $seconds_remaining = $time_remaining % 60; // секунд
    if ($hours_remaining > 0) {
        return "{$hours_remaining} год. {$minutes_remaining} хв.";
    } elseif ($minutes_remaining > 0) {
        return "{$minutes_remaining} хв. {$seconds_remaining} сек.";
    } else {
        return "{$seconds_remaining} сек.";
    }
}
function get_custom_remaining_time($interval, $last_run_time) {
    $current_time = time();

    if (empty($last_run_time) || strtotime($last_run_time) === false) {
        return "No data available";
    }

    $last_run_time = strtotime($last_run_time);
    $next_run_time = $last_run_time + $interval;
    $time_remaining = $next_run_time - $current_time;
    if ($time_remaining <= 0) {
        return "0 sec.";
    }
    $hours_remaining = floor($time_remaining / 3600);
    $minutes_remaining = floor(($time_remaining % 3600) / 60);
    $seconds_remaining = $time_remaining % 60;

    if ($hours_remaining > 0) {
        return "{$hours_remaining} hor. {$minutes_remaining} min.";
    } elseif ($minutes_remaining > 0) {
        return "{$minutes_remaining} min. {$seconds_remaining} sec.";
    } else {
        return "{$seconds_remaining} src.";
    }
}

function get_hourly_remaining_time($custom_time, $last_run_time) {
    $current_time = time();
    $start_time = strtotime($custom_time);
    $last_run_time = strtotime($last_run_time);
    if ($last_run_time >= $current_time) {
        $next_run_time = strtotime("+1 hour", $last_run_time);
    } else {
        $next_run_time = strtotime("+1 hour", $current_time);
    }
    $time_remaining = $next_run_time - $current_time;
    $hours_remaining = floor($time_remaining / 3600);
    $minutes_remaining = floor(($time_remaining % 3600) / 60);
    return "{$hours_remaining} hour {$minutes_remaining} min";
}
function get_hourly_bar($custom_time, $last_run_time) {
    $current_time = time();
    $start_time = strtotime($custom_time);
    $last_run_time = strtotime($last_run_time);
    if ($last_run_time >= $current_time) {
        $next_run_time = strtotime("+1 hour", $last_run_time);
    } else {
        $next_run_time = strtotime("+1 hour", $current_time);
    }
    $time_remaining = $next_run_time - $current_time;
    $progress = 100 - (($time_remaining / 3600) * 100); // Прогрес
    return "<div class='progress-bar-container'>
                <div class='progress-bar' style='width: {$progress}%;'></div>
            </div>";
}

?>