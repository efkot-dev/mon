<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;
$type = isset($_POST['type']) ? Clean::str($_POST['type']): null;
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if ($access->get('calendar') ){
switch($act){
	case 'category': 
		#if ($access->get('edit_calendar') ){
			okno_title('New');
				echo '<form action="/?do=send" method="post" id="formadd">
				<input name="act" type="hidden" value="savecalendar">'.
				form([
					'name'=>$lang['name'],'descr'=>'',
					'pole'=>'<input required name="name" class="input1" type="text" value="'.$getpmon['name'].'">']).			
				form([
					'name'=>$lang['colot_text'],'descr'=>'',
					'pole'=>'<input type="color" id="colorPicker" name="color_text" value="#222222">']).			
				form([
					'name'=>$lang['colot_body'],'descr'=>'',
					'pole'=>'<input type="color" id="colorPicker" name="color_fon" value="#eeeeee">']).
				'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
			okno_end();	
		#}
	break;	
	case 'start':	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
		if(is_valid_id($id)) {
			$sql_event = $db->Simple("SELECT * FROM calendar_events WHERE id = '{$id}' LIMIT 1");
			echo'<div class="block_start_time">
			<input type="datetime-local" name="form_start_time" id="form_start_time" value="'.$sql_event['start_date'].' 09:00" class="css-input">
			<button type="botton" form="formadd" value="submit" onclick="form_start_time(\'start\','.$id.')">'.$lang['edit'].'</button>
			<div>';
			}
		die;
	break;		
	case 'end':	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
		$act = isset($_POST['act']) ? Clean::text($_POST['act']) : null;
		if(is_valid_id($id)) {
			$sql_event = $db->Simple("SELECT * FROM calendar_events WHERE id = '{$id}' LIMIT 1");
			echo'<div class="block_'.$act.'_time">
			<input type="datetime-local" name="form_end_time" id="form_end_time" value="'.$sql_event['end_date'].' 18:00" class="css-input">
			<button type="botton" form="formadd" value="submit" onclick="form_start_time(\'end\','.$id.')">'.$lang['edit'].'</button>
			<div>';
			}
		die;
	break;	
	case 'delete':	
		if ($access->get('edit_calendar') ){	
			$id  = isset($_POST['id'])  ? Clean::int($_POST['id'])  : null;
			$day = isset($_POST['day']) ? Clean::int($_POST['day']) : null;
			$delete_id = null;
			if (is_valid_id($id)) {
				$delete_id = $id;
			} elseif (is_valid_id($day)) {
				$delete_id = $day;
			}
			if ($delete_id !== null) {
				$stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = :id");
				$stmt->execute([':id' => $delete_id]);
			}
		}			
	break;	
	case 'delet':
		if ($access->get('edit_calendar') ){	
			$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
			if(is_valid_id($id)) {
			$stmt1 = $pdo->prepare("DELETE FROM calendar_events WHERE category_id = :id");
			$stmt1->execute([':id' => $id]);
			$stmt2 = $pdo->prepare("DELETE FROM calendar_categories WHERE id = :id");
			$stmt2->execute([':id' => $id]);
			}
		}
	break;	
	case 'edit_e':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
		$timed = isset($_POST['timed']) ? Clean::text($_POST['timed']) : null;
		if(is_valid_id($id)) {
			$sql_event = $db->Simple("SELECT * FROM calendar_events WHERE id = '{$id}' LIMIT 1");
			if(isset($timed) && !empty($timed)){
				$timed = str_replace("T", " ", $timed);
				$timed = $timed.':00';
				$db->query("UPDATE  calendar_events  SET end_time = '{$timed}' WHERE id = '{$id}'");
			}
		}
		echo $timed;
		die;
	break;		
	case 'edit_s':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
		$timed = isset($_POST['timed']) ? Clean::text($_POST['timed']) : null;
		if(is_valid_id($id)) {
			$sql_event = $db->Simple("SELECT * FROM calendar_events WHERE id = '{$id}' LIMIT 1");
			if(isset($timed) && !empty($timed)){
				$timed = str_replace("T", " ", $timed);
				$timed = $timed.':00';
				$db->query("UPDATE  calendar_events  SET start_time = '{$timed}' WHERE id = '{$id}'");
			}
		}
		echo $timed;
		die;
	break;	
	case 'view':	
		$day = isset($_POST['day']) ? Clean::int($_POST['day']) : null;
		if(is_valid_id($day)) {
			$sql_event = $db->Simple("SELECT * FROM calendar_events WHERE id = '{$day}' LIMIT 1");
			$access = ($access->get('edit_calendar')  ? '<img class="scheduler_delet" onclick="scheduler(\'delete\','.$sql_event['id'].')" src="../style/img/close.png">':'');
			okno_title($lang['task_planning']);
			$sql_cat = $db->Simple("SELECT * FROM calendar_categories WHERE id = '{$sql_event['category_id']}' LIMIT 1");
			$sql_usr = $db->Simple("SELECT * FROM users WHERE id = '{$sql_event['employee_id']}' LIMIT 1");
			echo form(['name'=>$lang['start_date'],'descr'=>$lang['planing'],'pole'=>'<div id="s_time">'.$sql_event['start_date'].'</div><div id="start_time"></div><img class="scheduler_delet edit_start" onclick="start_time(\'start\','.$sql_event['id'].')" src="../style/img/edit.png">']);
			echo form(['name'=>$lang['end_date'],'descr'=>$lang['planing_use'],'pole'=>'<div id="e_time">'.$sql_event['end_date'].'</div><div id="end_time"></div><img class="scheduler_delet edit_end" onclick="start_time(\'end\','.$sql_event['id'].')" src="../style/img/edit.png">']);
			echo form(['name'=>$lang['calendar_usr'],'descr'=>$lang['use_work'],'pole'=>(empty($sql_usr['name']) ? $sql_usr['username'] : $sql_usr['name'])]);
			echo form(['name'=>$lang['pmon_taskman'],'descr'=>$lang['listworker'],'pole'=>$sql_cat['name'].''.$access]);
			okno_end();
		}
	break;	
	case 'scheduler':
		if ($access->get('edit_calendar') ){
			$day = isset($_POST['day']) ? Clean::int($_POST['day']) : null;
			$month = isset($_POST['month']) ? Clean::int($_POST['month']) : null;
			$year = isset($_POST['year']) ? Clean::int($_POST['year']) : null;
			if (is_valid_id($year) && is_valid_id($month) && is_valid_id($day)) {
				okno_title($lang['task_planning']);
				$startDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
				$endDate = $startDate;
				echo '<form action="/?do=send" method="post" id="formadd">';
				echo '<input type="hidden" name="act" value="scheduler">';
				echo '<input type="hidden" name="day" value="'.$day.'">';
				echo '<input type="hidden" name="month" value="'.$month.'">';
				echo '<input type="hidden" name="year" value="'.$year.'">';
				$sql_calendar_categories = $db->SimpleWhile("SELECT * FROM calendar_categories");
				$select_form = '<select class="select" name="category" id="category">';
				foreach ($sql_calendar_categories as $category) {
					$select_form .= '<option value="'.$category['id'].'">'.$category['name'].'</option>';
				}
				$select_form .= '</select>';
				$sql_calendar_employees = $db->SimpleWhile("SELECT id, username, name FROM users");
				$select_usr = '<select class="select" name="user" id="user">';
				foreach ($sql_calendar_employees as $usr) {
					$select_usr .= '<option value="'.$usr['id'].'">'.(empty($usr['name']) ? $usr['username'] : $usr['name']).'</option>';
				}
				$select_usr .= '</select>';
				echo form(['name'=>$lang['calendar_usr'],'descr'=>'','pole'=>$select_usr]);
				echo form(['name'=>$lang['oid_types'],'descr'=>'','pole'=>$select_form]);
				echo form(['name'=>$lang['start'],'descr'=>'','pole'=>'<input type="date" name="start_date" value="'.$startDate.'">']);
				echo form(['name'=>$lang['end'],'descr'=>'','pole'=>'<input type="date" name="end_date" value="'.$endDate.'">']);
				$icon = '<input type="radio" id="icon1" name="icon" value="user">
					<label for="icon1"><img src="../style/img/house-user.png" alt="Icon 1"></label>&nbsp;&nbsp;        
					<input type="radio" id="icon1" name="icon" value="success">
					<label for="icon1"><img src="../style/img/house-success.png" alt="Icon 1"></label> &nbsp;&nbsp;  
					<input type="radio" id="icon1" name="icon" value="medicine">
					<label for="icon1"><img src="../style/img/house-medicine.png" alt="Icon 1"></label> &nbsp;&nbsp;       
					<input type="radio" id="icon1" name="icon" value="briefcase">
					<label for="icon1"><img src="../style/img/house-briefcase.png" alt="Icon 1"></label>&nbsp;&nbsp;        
					<input type="radio" id="icon1" name="icon" value="instrument">
					<label for="icon1"><img src="../style/img/house-instrument.png" alt="Icon 1"></label>&nbsp;&nbsp;
					<input type="radio" id="icon1" name="icon" value="ban">
					<label for="icon1"><img src="../style/img/house-ban.png" alt="Icon 1"></label>&nbsp;&nbsp;
					<input type="radio" id="icon1" name="icon" value="car">
					<label for="icon1"><img src="../style/img/house-car.png" alt="Icon 1"></label>&nbsp;&nbsp;';
				echo form(['name'=>'Icon','descr'=>'','pole'=>$icon]);
				echo form(['name'=>$lang['note_port'],'descr'=>'','pole'=>'<textarea class="textarea1" rows="7" name="note"></textarea>']);
				echo '</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
				okno_end();
			}
		}
	break;	
	case 'auto': 	
		okno_title($lang['task_planning']);
		echo '<form action="/?do=calendar" method="post" id="formadd">';
		echo '<input type="hidden" name="act" value="generator">';
		$sql_calendar_categories = $db->SimpleWhile("SELECT * FROM calendar_categories");
		$select_form = '<select class="select" name="category" id="category">';
		foreach ($sql_calendar_categories as $category) {
			$select_form .= '<option value="'.$category['id'].'">'.$category['name'].'</option>';
		}
		$select_form .= '</select>';
		echo form(['name'=>$lang['sklad_category'],'descr'=>'','pole'=>$select_form]);
		$sql_calendar_employees = $db->SimpleWhile("SELECT id, username, name FROM users");
		$select_employees = '<div class="form_input_list gena">';
		foreach ($sql_calendar_employees as $usr) {
			$name = (empty($usr['name']) ? $usr['username'] : formatPib($usr['name']));
			$select_employees .= '<span class="form_input"><input type="checkbox" name="employees[]" value="'.$usr['id'].'"> '.$name.'</span>';
		}
		$select_employees .= '</div>';
		echo form(['name'=>$lang['calendar_usr'],'descr'=>$lang['use_work_list'],'pole'=>$select_employees]);
		$max_per_day_options = '<select class="select" name="max_per_day">';
		for ($i = 2; $i <= 10; $i++) {
			$max_per_day_options .= '<option value="'.$i.'">'.$i.'</option>';
		}
		$max_per_day_options .= '</select>';
		echo form(['name'=>$lang['count_day'],'descr'=>$lang['maximun_user_day'],'pole'=>$max_per_day_options]);
		$max_consecutive_days_options = '<select class="select" name="max_consecutive_days">';
		for ($i = 1; $i <= 3; $i++) {
			$max_consecutive_days_options .= '<option value="'.$i.'">'.$i.'</option>';
		}
		$max_consecutive_days_options .= '</select>';
		echo form(['name'=>$lang['max_day_work'],'descr'=>$lang['max_day_work_inf'],'pole'=>$max_consecutive_days_options]);
		echo form(['name'=>$lang['start'],'descr'=>$lang['start_day'],'pole'=>'<input type="date" name="start_date">']);
		echo form(['name'=>$lang['end'],'descr'=>$lang['end_day'],'pole'=>'<input type="date" name="end_date" >']);
		echo '</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
		okno_end();	
		break;	
	case 'delet': 
		if ($access->get('edit_calendar') ){	
			if($type=='category'){
				$getcal = $db->Fast('calendar_categories','*',['id'=>$id]);	
				if(!empty($getcal['id'])){
					$db->SQLdelete('calendar_events',['category_id' => $getcal['id']]);
				}
			}
		}
	break;	
}
}
?>