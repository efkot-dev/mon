<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$carid = isset($_POST['carid']) ? Clean::int($_POST['carid']): null;
switch($act){
	case 'add': 
		$type_motor = '';
		$getusr = $db->SimpleWhile("SELECT id, username FROM users");
		if(isset($getusr) && count($getusr)>0){
			foreach($getusr as $id => $data) {
			$get_check_usr = $db->Simple("SELECT id FROM users_car WHERE car_id = '{$carid}' AND moder_id = '{$data['id']}' LIMIT 1");
				if(empty($get_check_usr['id'])){
					$type_motor .= '
						<option value="'.$data['id'].'">'.$data['username'].'</option>
					';
				}
			}
			echo'
			<form action="/" method="post" class="form_center">
				<input type="hidden" name="do" value="car">
				<input type="hidden" name="act" value="adduser">
				<input type="hidden" name="carid" value="'.$carid.'">
				<select class="select" name="userid" id="usrid">'.$type_motor.'</select>
				<input type="submit" value="'.$lang['add'].'">
			</form>
			';
		}
	break;	
	case 'del': 	
	
	break;	
	case 'driver':	
		$getusr = $db->SimpleWhile("SELECT * FROM users_odometr WHERE status = 1 AND DATE(added) = CURDATE();");
		if(isset($getusr) && count($getusr)>0){
			echo'<div class="us_online"><div class="block-driver">';
			foreach($getusr as $id => $car) {
				echo'<div class="car-driver"><img src="../style/img/time_driver.png"><span class="timer_checkend">'.aftertime($car['start_date']).'</span><span class="car_checkend">'.$car['car'].'</span></div>';
			}
			echo'</div></div>';
		}		
	break;
}
?>