<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function zaput_count($id) {
	global $db;
	$sql = "SELECT COUNT(*) as total FROM task_list WHERE typesworker = '{$id}' AND status != 2";
	$row_count = $db->NumRows($sql);
	$total_records = $row_count['total'];
	return $total_records;
}
function color_worker($color) {
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
	return '';
}
function sql_where($where_data) {
	$sqlwhere = '';
	if (!empty($where_data)) {
        $sqlwhere = 'WHERE ' . implode(' AND ', $where_data);
    }
	return $sqlwhere;
}
function sql_orderby($orderby_data) {
	$sqlorderby = '';
	if (!empty($orderby_data)) {
        $sqlorderby = ' ORDER BY ' . implode(', ', $orderby_data);
    }
	return $sqlorderby;
}
function getListWorker() {
	global $db, $config, $confPMon, $cacheManager;
	$expiration = 3600;
	$task_list_worker = [];
	$data = [];
	if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
		$cacheKey = "tasklistworker";		
		$cachedResult = $cacheManager->get($cacheKey);
		if ($cachedResult !== null) {
			$data = $cachedResult;
		} else {
			$task_list_worker = $db->SimpleWhile("SELECT * FROM `task_list_worker`");
			if(isset($task_list_worker) && count($task_list_worker)>0){
				foreach($task_list_worker as $temp){	
					$data[$temp['id']] = array(
						'id'=>$temp['id'], 'name'=>$temp['name'], 'color'=>$temp['color']
					);
				}
			}
			if(isset($data) && count($data)>0){
				$cacheManager->set($cacheKey,$data,$expiration);
			}
		}
	}else{
		$data = $db->Multi('task_list_worker');
	}
	return $data;
}
function getDateStatusMessage($inputDate, $status) {
    if ($status == 2) {
        return '';
		die;
    }
    $currentDate = new DateTime();
    $currentDate->setTime(0, 0);
    $inputDate = new DateTime($inputDate);
    $inputDate->setTime(0, 0); 
    $interval = $currentDate->diff($inputDate);
    $daysDiff = (int)$interval->format('%r%a');
    $message = '';
    switch ($daysDiff) {
        case 0:
            $message = '<div class="today_task">Сьогодні</div>';
            break;
        case 1:
            $message = '<div class="task_next_day">Завтра</div>';
            break;
        case 2:
            $message = '<div class="today_task">Післязавтра</div>';
            break;
        case 3:
            #$message = '<div class="today_task">Через 3 дн</div>';
            break;
        case 4:
            #$message = '<div class="today_task">Через 4 дн</div>';
            break;
        case 5:
            #$message = '<div class="today_task">Через 5 дн</div>';
            break;
        case 6:
            #$message = '<div class="today_task">Через 6 дн</div>';
            break;
        case 7:
            #$message = '<div class="today_task">Через 7 дн</div>';
            break;
        default:
            if ($daysDiff < 0) {
                $message = '<div class="termin_task">Вийшов термін</div>';	
            } else {
                #$message = '<div class="today_task">Залишилось ' . $daysDiff . ' дн</div>';
            }
            break;
    }

    return $message;
}
function taskman_order_by($order_by) {
    switch ($order_by) {
        case 'created_at': 
            return '`created_at` ASC';
        case 'updated_at': 
            return '`update` ASC';    
        case 'planned_at': 
            return '`worker` ASC';    
        case 'do_before_at': 
            return '`before_at` ASC';
        default:
            return '`id` DESC';
    }
}
function taskman_where_priority($priority) {
    if (in_array($priority, [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15])) {
        return "priority = '$priority'";
    }
    return '';
}
function taskman_where_worker($worker) {
    if (in_array($worker, [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15])) {
        return "typesworker = '$worker'";
    }
    return '';
}
function taskman_where_status($status) {
    if (in_array($status, [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15])) {
        return "status = '$status'";
    }
    return '';
}
function getListVikonavciArray($dataid) {
	global $db;	
	$data = [];
	$listus = $db->SimpleWhile("SELECT * FROM task_list_vikonavci Where taskid = ".$dataid."");
	if(isset($listus) && count($listus) > 0){
		foreach($listus as $uid => $vikonavci){
			$data[$vikonavci['userid']] = array(
				'userid' => $vikonavci['userid'],
				'username' => $vikonavci['username'],
				'name' => $vikonavci['name'],
				'userlass' => $vikonavci['userclass'],
				'taskid' => $vikonavci['taskid'],
				'added' => $vikonavci['added']
			);
		}
	}
	return $data;	
}
function selected($order_by, $value) {
    return $order_by == $value ? 'selected' : '';
}
function get_progress($added, $inputDate, $status) {
	$enddata = $inputDate;
	if($status==2){
		$data['date'] = $enddata;
		$data['status'] = 'good';
		$data['bar'] = '';
		return $data;
	}
    $currentDate = new DateTime();
    $currentDate->setTime(0, 0);
    $inputDate = new DateTime($inputDate);
    $inputDate->setTime(0, 0); 
    $interval = $currentDate->diff($inputDate);
    $daysDiff = (int)$interval->format('%r%a');
    $message = '';
	$status = 'okey';
    switch ($daysDiff) {
        case 0:
            $message = '<span class="task_time_today">Сьогодні</div>';
            break;
        case 1:
            $message = '<span class="task_next">Завтра</div>';
            break;
        case 2:
            $message = '<span class="task_time_slim">Післязавтра</div>';
            break;
        case 3:
            $message = '<span class="task_time_very">Через 3 дн</div>';
            break;
        case 4:
            $message = '<span class="task_time_good">Через 4 дн</div>';
            break;
        case 5:
            $message = '<span class="task_time_good">Через 5 дн</div>';
            break;
        case 6:
            $message = '<span class="task_time_good">Через 6 дн</div>';
            break;
        case 7:
            $message = '<span class="task_time_good">Через 7 дн</div>';
            break;
        default:
            if ($daysDiff < 0) {
                $message = '<div class="clocker-bar-end">Вийшов термін</div>';	
				$status = 'end';
            } else {
                $message = '<span class="task_time_good">Залишилось ' . $daysDiff . ' дн</div>';
            }
            break;
    }
	$data['date'] = $enddata;
	$data['status'] = $status;
	$data['bar'] = $message;
    return $data;
}
function PaidType($types) {
	global $lang;
	$types_masiv = [
		1 => '<span class="ty_paid type_paid"><div>'.$lang['type_paid'].'</div>'.(!empty($types['money']) ? '<span class="money">'.$types['money'].' грн</span>' : '').'</span>',
		2 => '<span class="ty_paid type_free_charge"><div>'.$lang['type_free_charge'].'</div></span>',
		3 => '<span class="ty_paid type_service"><div>'.$lang['type_service'].'</div>'.(!empty($types['dogovir']) ? '<span class="dogovir">№ '.$types['dogovir'].'</span>' : '').'</span>'
	];
	return isset($types_masiv[$types['payment_type']]) ? $types_masiv[$types['payment_type']] : '';	
}
function getListVikonavci($dataid,$taskview) {
	global $db, $lang, $USER;	
	$result = '<table class="resp-tab"><thead><tr><th>Працівник</th><th>Група</th><th>Додано</th></tr></thead><tbody>';
	$listcomm = $db->SimpleWhile("SELECT * FROM task_list_vikonavci Where taskid = ".$dataid." ORDER BY added DESC");
		if(isset($listcomm) && count($listcomm) > 0){
			foreach($listcomm as $ipid => $used){	
				$result .= '<tr><td>'.$used['username'].'</td><td>'.getClassUser($used['userclass']).'</td><td>'.$used['added'].'</td></tr>';
			}
		}else{
			$result .= '<tr><td colspan="6">'.$lang['empty'].'</td></tr>';
		}	
	$result .= '</table>';	
	if(isset($USER['class']) && $USER['class'] >= 3 && $_GET['types']!='addvikonavci' && !empty($taskview['status']) && $taskview['status']==1){
		$result .= '<div class="pole"><a href="/?do=taskman&act=view&id='.$dataid.'&types=addvikonavci"  class="urlelelement">Додати виконавців</a></div>';
	}
	return $result;
}
function resultTplComment($data,$taskview) {
	global $lang, $USER, $db;
	$panel = '';	
	if(isset($USER['class']) && $USER['class'] >= 3 && !empty($taskview['status']) && $taskview['status']==1){
		$panel = '<a class="del_comment" href="/?do=taskman&act=delcomm&id='.$data['id'].'"><img src="../style/img/close.png"></a>';
	}
	$getUser = isset($data['autorid']) ? $db->Fast('users', 'username, class', ['id' => $data['autorid']]) : null;
	if($getUser) {
		$username = $getUser['username'];
		$class = getClassUser($getUser['class']);
	} else {
		$username = '';
		$class = '';
	}	
	$autor = isset($getUser['username']) ? $getUser['username'].' ('.$class.')' : '';
	$billdate = isset($data['added']) ? $data['added'] : '';
	$comment = isset($data['comment']) ? $data['comment'] : '';
	return '<div class="task_comm"><div class="utask"><span>'.$lang['autor'].':</span> '.$autor.' <span>'.$lang['billdate'].':</span> '.$billdate.''.$panel.'</div><div class="utext">'.$comment.'</div></div>';	
}

?>
