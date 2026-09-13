<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('view_car')) {
    $go->redirect('main');
}
if (isset($confPMon['CAR']) && !empty($confPMon['CAR']) && $confPMon['CAR'] == 1 && $access->get('view_car')) {
require ENGINE_DIR.'functions/car.php';
$result = '';
$content = '';
$metatags = [
	'title'=>'Автомобільний парк провайдера',
	'description'=>'Автомобільний парк провайдера',
	'page'=>'car'
];
switch($act){
	case'zvit':
		$select_car = '';
		$num_car = isset($_GET['select_car']) ? Clean::int($_GET['select_car']) : false;
		$start_date = isset($_GET['start_date']) ? Clean::text($_GET['start_date']) : null;
		$end_date = isset($_GET['end_date']) ? Clean::text($_GET['end_date']) : null;
		if(isset($num_car) && $num_car > 0){
			$where[] = "car_id = '" . $num_car . "'";
		}
		if (isset($end_date) && !empty($end_date)) {
			$select_end_date = $end_date;
			$end_date .= ' 23:59:59';				
			$where[] = "added <= '" . $end_date . "'";
		}
		if (isset($start_date) && !empty($start_date)) {
			$select_start_date = $start_date;
			$start_date .= ' 00:00:00';				
			$where[] = "start_date >= '" . $start_date . "'";
		}else{
			$where[] = "start_date >= '" . date('Y-m-d H:i:s') . "'";
		}
		if (!empty($where)) {
			$sql_where = ' WHERE ' . implode(' AND ', $where);
		}
		if (isset($start_date) && isset($end_date)) {
			$select_date = ' від '.$start_date.' до '.$end_date;
		}else{
			$select_date = 'за поточний день '.date('Y-m-d H:i:s');
		}
		$sql = "SELECT * FROM car";
		$sql_car = $db->SimpleWhile($sql);
		if(isset($sql_car) && count($sql_car) > 0){
			$select_car .= '<select class="select_car" name="select_car">';	
			$select_car .= '<option value="0">'.$lang['all'].'</option>';			
			foreach($sql_car as $dataid => $car){ 
				$select_car .= '<option value="'.$car['id'].'" '.(isset($num_car) && $num_car==$car['id']?'selected="selected"':'').'>'.$car['nomer'].'</option>';
			}
			$select_car .= '</select>';
		}
		if(isset($sql_where)){
			$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Звіт '.strtoupper($num_car).' '.$select_date.'</span>
			</div>';
			$price = (isset($_GET['price']) ? floatval(str_replace(',', '.', $_GET['price'])) : 0);
			$content .= '
			<form action="/" method="get">
			<input type="hidden" name="do" value="car">
			<input type="hidden" name="act" value="zvit">
			<input type="hidden" name="c" value="'.$num_car.'">
				<div class="main_blocks"><div class="lf_1">
					<div class="block_4">
						<label for="start_date">Автомобіль:</label>
					</div>
					<div class="block_4">	
						'.$select_car.'	
					</div>						
					<div class="block_4">
						<label for="start_date">Ціна км/грн:</label>
					</div>
					<div class="block_4">	
						<input style="width:100px;" name="price" class="input1" type="text" value="'.$price.'">	
					</div>			
					<div class="block_4"><label for="start_date">Відкриття наряду:</label>
						<input type="date" lang="uk" id="start_date" name="start_date" '.(isset($select_start_date) && $select_start_date ? ' value="'.$select_start_date.'"' : "").'>
					</div>
					<div class="block_4"><label for="end_date">Закриття наряду:</label>
						<input type="date" lang="uk" id="end_date" name="end_date" '.(isset($select_end_date) && $select_end_date ? ' value="'.$select_end_date.'"' : "").'>
					</div>
					<div class="block_4">
						<input class="go" type="submit" value="Сформувати звіт">	
					</div>					
					<div class="block_4">			
						<button style="background-color: #118f16;" onclick="PMonexportToExcel(\'dataTable\',\'num_'.$num_car.'_'.date('Y-m-d H:i:s').'\')">Експорт в Excel</button>
					</div>	
					</div>
				</div>
			</form>';
			$sql = "SELECT * FROM users_odometr {$sql_where}";
			$sql_usr = $db->SimpleWhile($sql);
			if(isset($sql_usr) && count($sql_usr) > 0){
			$content .= '<table class="resp-tab" id="dataTable"><thead><tr>
				<th>Держ.знак</th>
				<th width="15%">Виїзд</th>
				<th width="10%">Показник</th>
				<th width="15%">Заїзд</th>
				<th width="10%">Показник</th>
				<th width="10%">Всього (км)</th>
				<th>Амортизація</th>
				</tr></thead><tbody>';
				$count_km = 0;
				$count_suma = 0;
				foreach($sql_usr as $datid => $value){
					$sum = 0;
					$amortuzaciy = 0;
					if(!empty($value['finish_odometr'])){
						$sum = intval($value['finish_odometr']) - intval($value['start_odometr']);
						if(isset($sum)){
							$count_km = $count_km + $sum;
						}
						if(isset($price)){
							$amortuzaciy = ($sum * $price);
							$count_suma = $count_suma + $amortuzaciy;
						}
					}
					$content .= '<tr>
						<td>'.strtoupper($value['car']).'</td>
						<td>'.$value['start_date'].'</td>
						<td>'.$value['start_odometr'].'</td>
						<td>'.(isset($value['finish_date'])?$value['finish_date']:'--').'</td>
						<td>'.(isset($value['finish_odometr'])?$value['finish_odometr']:'--').'</td>
						<td>'.(isset($sum) && $sum>0 ? $sum.'':'').'</td>
						<td>'.$amortuzaciy.'</td>
					</tr>';
				}
				$content .= '<tr class="top_blue">
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><b>'.$count_km.'</b></td>
						<td><b>'.$count_suma.'</b></td>
					</tr>';
				$content .='</table>
				<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
				'; 
			}
		}
	break;
	case'adduser':	
		$userid = isset($_POST['userid']) ? Clean::int($_POST['userid']) : 0;	
		$carid = isset($_POST['carid']) ? Clean::int($_POST['carid']) : 0;
		if(isset($carid) && $carid>0 && isset($userid) && $userid>0 ){
			$added = date('Y-m-d H:i:s');
			$sql = "INSERT INTO users_car (`car_id`, `moder_id`, `added`) VALUES ('{$carid}','{$userid}','{$added}')";
			$db->query($sql);
		}
		$go->go('/?do=car&act=car');
	break;	
	case'savecar':
		$to = isset($_POST['to']) ? Clean::int($_POST['to']) : 0;
		$odometr = isset($_POST['odometr']) ? Clean::int($_POST['odometr']) : 0;
		$type_motor = isset($_POST['type_motor']) ? Clean::int($_POST['type_motor']) : 1;
		$derjnomer = isset($_POST['derjnomer']) ? Clean::text($_POST['derjnomer']) : null;	
		$modelauto = isset($_POST['modelauto']) ? Clean::text($_POST['modelauto']) : null;	
		$added = date('Y-m-d H:i:s');
		$sql = "INSERT INTO car (`name`, `nomer`, `odometr`, `zamina_masla`, `type_motor`, `added`) VALUES ('{$modelauto}','{$derjnomer}', '{$odometr}', '{$to}', '{$type_motor}', '{$added}')";
		if(isset($odometr) && isset($derjnomer) && isset($modelauto)){
			$db->query($sql);
		}
		$go->go('/?do=car&act=car');
	break;	
	case'updateodometr':	
		$odometrid = isset($_POST['odometrid']) ? Clean::int($_POST['odometrid']) : 0;
		$finish_odometr = isset($_POST['odometr']) ? Clean::int($_POST['odometr']) : 0;
		$first_odometr = isset($_POST['first_odometr']) ? Clean::int($_POST['first_odometr']) : 0;
		if($finish_odometr>0 && $first_odometr>0 && $odometrid>0){
			$formattedDate = date('Y-m-d H:i:s');
			$db->SQLupdate('users_odometr',['status'=>2,
			'finish_date' => $formattedDate,
			'start_odometr' => $first_odometr,'finish_odometr' => $finish_odometr],['id'=>$odometrid]);
		}
		$go->go('/?do=car&act=list');	
	break;	
	case'finishodometr':	
		$odometrid = isset($_POST['odometrid']) ? Clean::int($_POST['odometrid']) : 0;
		$odometr = isset($_POST['odometr']) ? Clean::int($_POST['odometr']) : 0;
		if($odometr>0 && $odometrid>0){
			$formattedDate = date('Y-m-d H:i:s');
			$db->SQLupdate('users_odometr',['status'=>2,'finish_date' => $formattedDate,'finish_odometr' => $odometr],['id'=>$odometrid]);
		}
		$go->go('/?do=car&act=list');
	break;	
	case'edit':		
		$carid = isset($_GET['carid']) ? Clean::int($_GET['carid']) : 0;
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
		$get_car = $db->Simple("SELECT * FROM car WHERE id = '{$carid}' LIMIT 1");	
		if(!empty($get_car['id'])){
		$get_usr = $db->Simple("SELECT * FROM users_odometr WHERE id = '{$id}' LIMIT 1");		
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Закриття наряду</span>
			</div>
		';	
		$content .= '
			<div class="nav-fiber p10">
				<form action="/?do=car" method="post">
				<input name="odometrid" type="hidden" value="'.$get_usr['id'].'">
				<input name="start_odometr" type="hidden" value="'.$get_usr['start_odometr'].'">
				<input name="act" type="hidden" value="updateodometr">';
			$content .='
				<label for="type">Автомобіль:<br> <b>'.$get_car['nomer'].'</b></label>
				<br>';			
			$content .='
				<label for="type">Початковий показник:<br></label>
				<input name="first_odometr" type="text" value="'.$get_usr['start_odometr'].'" required="" autocomplete="off" style="width:10%;"><br>';		
			$content .='
				<label for="type">Кінцевий показник одометра:</label>
				<input name="odometr" type="text" value="'.$get_usr['finish_odometr'].'" required="" autocomplete="off" style="width:10%;"><div id="sum"></div><br>';			
		$content .='
				<input type="submit" value="'.$lang['save'].'">
				</form>
			</div>';
		}	
	break;	
	case'finish':		
		$carid = isset($_GET['carid']) ? Clean::int($_GET['carid']) : 0;
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
		$get_car = $db->Simple("SELECT * FROM car WHERE id = '{$carid}' LIMIT 1");	
		if(!empty($get_car['id'])){
		$get_usr = $db->Simple("SELECT * FROM users_odometr WHERE id = '{$id}' LIMIT 1");
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Закриття наряду</span>
			</div>
		';	
		$content .= '
			<div class="nav-fiber p10">
				<form action="/?do=car" method="post">
				<input name="odometrid" type="hidden" value="'.$get_usr['id'].'">
				<input name="start_odometr" type="hidden" value="'.$get_usr['start_odometr'].'">
				<input name="act" type="hidden" value="finishodometr">';
			$content .='
				<label for="type">Автомобіль:<br> <b>'.$get_car['nomer'].'</b></label>
				<br>';			
			$content .='
				<label for="type">Початковий показник:<br> <b>'.$get_usr['start_odometr'].'</b></label>
				<br>';		
			$content .='
				<label for="type">Кінцевий показник одометра:</label>
				<input name="odometr" type="text" pattern="[0-9]+" required="" autocomplete="off" style="width:10%;"><div id="sum"></div><br>';			
		$content .='
				<input type="submit" value="'.$lang['add'].'">
				</form>
			</div>';
		}
	break;	
	case'addcar':	
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Новий автомобіль</span>
			</div>
		';
		$content .= '
			<div class="nav-fiber p10">
				<form action="/?do=car" method="post">
				<input name="act" type="hidden" value="savecar">';
			$type_motor = '
				<option value="1">Бензин</option>
				<option value="2">Дизель</option>
				<option value="3">Газ</option>
				<option value="4">Електро</option>
				<option value="5">Педалі</option>';
			$content .='
				<label for="type">Тип палива:</label>
				<select class="select" name="type_motor" id="type_motor">'.$type_motor.'</select><br>';		
			$content .='
				<label for="type">Державний номер:</label>
				<input name="derjnomer" type="text" pattern="[a-zA-Z0-9]+" required="" autocomplete="off" style="width:10%;"><br>';			
			$content .='
				<label for="type">Модель автомобіля:</label>
				<input name="modelauto" type="text" required="" autocomplete="off" style="width:30%;"><br>';			
			$content .='
				<label for="type">Початковий показник одометра:</label>
				<input name="odometr" type="text" required="" autocomplete="off" style="width:10%;"><br>';			
		$content .='
				<input type="submit" value="'.$lang['add'].'">
				</form>
			</div>';				
	break;	
	case'pokaznuk':
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
		$day = isset($_GET['day']) ? Clean::int($_GET['day']) : 0;
		if(isset($id) && $id>0 && $day>0){
			$db->SQLdelete('users_odometr',['id' => $id]);
		}
		$go->go('/?do=car&act=list&day='.$day);	
	break;	
	case'deletuser':	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
		if(isset($id) && $id>0){
			$db->SQLdelete('users_car',['id' => $id]);
		}
		$go->go('/?do=car&act=car');
	break;	
	case'list':
		$where = '';
		$result_key = '';
		$carid = isset($_GET['carid']) ? Clean::int($_GET['carid']) : 0;
		$currentDate = new DateTime();
		$cur_day = $currentDate->format('d');
		$select_day = isset($_GET['day']) ? intval($_GET['day']) : intval($cur_day);
		$firstDayOfMonth = new DateTime($currentDate->format('Y-m-01'));
		$currentMonth = $currentDate->format('m');
		if ($currentMonth == $firstDayOfMonth->format('m')) {
			$dates = [];
			for ($i = 0; $i < $cur_day; $i++) {
				$dates[] = $currentDate->format('d');
				$currentDate->sub(new DateInterval('P1D'));
			}
			$result_key .= '<div class="select_day">';
			foreach (array_reverse($dates) as $date) {
				$class = ($select_day == $date) ? ' class="select"' : '';
				$selectedDate = $currentDate->format('Y').'-'.$currentMonth.'-'.$date;
				$sql = "SELECT COUNT(id) as count_id FROM users_odometr WHERE DATE(start_date) = '{$selectedDate}' AND status = 1 LIMIT 1";
				$get_car = $db->Simple($sql);
				$result_key .= '<a '.($get_car['count_id']>0?'style="color:#fff;background:red;"':'').' href="/?do=car&act=list&day=' . $date .(isset($carid) && $carid>0 ? '&carid='.$carid : '') . '"'.$class.'>' . $date . '.' . $currentMonth . '</a>';
			}
			$result_key .= '</div>';
		}
		if (isset($select_day) || $carid>0) {
			$where = ' WHERE ';
			if (isset($select_day)) {
				$where .= 'DAY(added) = ' . $select_day . ' AND MONTH(added) = ' . $currentMonth . ' AND YEAR(added) = ' . date("Y");
			}
			if (isset($carid) && $carid>0) {
				$where .= (isset($select_day) ? ' AND ' : '') . "car_id = '{$carid}'";
			}
		}
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Показники за '.$select_day.'.'.$currentMonth.'</span>
			</div>
		';
		$content .= '<div class="select_panel">
			<div class="name_day">Сформувати за:</div>
			'.$result_key.'
		</div>';	
		if (isset($carid) && $carid>0) {
			$get_car = $db->Simple("SELECT * FROM car WHERE id = '{$carid}' LIMIT 1");	
			$content .= '<div class="select_panel">
				<div class="name_day">Автомобіль:</div>
				<b>'.$get_car['nomer'].'</b>
			</div>';
		}
		$content .= '<table class="resp-tab" id="dataTable"><thead><tr>
			<th width="5%">Статус</th>
			<th width="10%">Держ.знак</th>
			<th width="10%">Дата початку</th>
			<th width="15%">Показник виїзд</th>
			<th width="10%">Дата заїзду</th>
			<th width="15%">Показник заїзд</th>
			<th width="15%">Всього</th>
			<th width="15%">Керування</th>
			</tr></thead><tbody>';
		$getusr = $db->SimpleWhile("SELECT * FROM users_odometr {$where}");
		if(isset($getusr) && count($getusr)>0){
			$count_sum = 0;
			foreach($getusr as $id => $usr) {
				$sum = 0;
				if(isset($usr['start_odometr']) && isset($usr['finish_odometr'])){				
					$sum = intval($usr['finish_odometr']) - intval($usr['start_odometr']); 
					$count_sum = $count_sum + $sum;
				}
				$content .= '<tr '.($usr['status']==1?'class="worker"':'').'>
					<td class="lock_odometr">'.($usr['status']==1?'<img src="../style/img/uponu.png">':'<img src="../style/img/lock.png">').'</td>
					<td><a href="#" class="car_nomer">'.$usr['car'].'</a></td>
					<td>'.$usr['start_date'].'</td>
					<td class="start_driver"><b>'.$usr['start_odometr'].'</b></td>
					<td>'.(isset($usr['finish_date'])?$usr['finish_date']:
						'<a href="/?do=car&act=finish&carid='.$usr['car_id'].'&id='.$usr['id'].'" class="lock_odometr"><img src="../style/img/add.png"></a>'
					).'</td>
					<td class="'.($usr['status']==1?'':'end_').'driver">'.(isset($usr['finish_odometr'])?
					'<b>'.$usr['finish_odometr'].'</b>'
					:
					'<b>В дорозі '.aftertime($usr['added']).'</b>').'</td>
					<td>'.($sum>0?$sum:'').'</td>
					<td>
					'.($usr['status']==2?'<a href="/?do=car&act=edit&id='.$usr['id'].'&carid='.$usr['car_id'].'" class="lock_odometr"><img src="../style/img/min_edit.png"> редагувати</a>':'').'
					<a href="/?do=car&act=pokaznuk&id='.$usr['id'].'&day=' . $select_day.'" class="lock_odometr"><img src="../style/img/close.png"> видалити</a>
					</td>
					</tr>';
			}
			$content .= '<tr class="top_blue">
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td><b>Всього</b></td>
				<td><b>'.$count_sum.'</b></td>
				<td></td>
				</tr>';
		}else{
			$content .= '<tr class="top_blue">
				<td colspan="7">'.$lang['empty'].'</td>
				</tr>';
		}	
		$content .='</table>';		
	break;	
	case'addodometr':	
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Подати показники</span>
			</div>
		';
		$select_car = '';
		$sql_car = $db->SimpleWhile("SELECT * FROM car");
		if(isset($sql_car) && count($sql_car) > 0){
			foreach($sql_car as $dataid => $car){ 
				$select_car .= '<option value="'.$car['id'].'">'.$car['nomer'].'</option>';
			}
		}
		$content .= '
			<div class="nav-fiber p10">
				<form action="/?do=car" method="post">
				<input name="act" type="hidden" value="saveodometr">';
			$content .='
				<label for="type">Автомобіль:</label>
				<select class="select" name="select_car" id="select_car">'.$select_car.'</select><br>';		
			$content .='
				<label for="type">Показник одометра:</label>
				<input name="odometr" type="text" pattern="[0-9]+" required="" autocomplete="off" style="width:10%;"><br>';			
		$content .='
				<input type="submit" value="'.$lang['add'].'">
				</form>
			</div>';		
	break;	
	case'saveodometr':	
		$odometr = isset($_POST['odometr']) ? Clean::int($_POST['odometr']) : 0;
		$select_car = isset($_POST['select_car']) ? Clean::int($_POST['select_car']) : 0;
		if(isset($odometr) && isset($select_car) && $odometr > 0 && $select_car > 0){
			$formattedDate = date('Y-m-d H:i:s');
			$pmon_clock = date('Y-m-d H:i:s');
			$sql_select_car = $db->Simple("SELECT * FROM `car` WHERE id = '{$select_car}' LIMIT 1");
			$sqlinsert = array(
				'car_id' => $select_car, 'start_date' => $formattedDate,'start_odometr' => $odometr,'status' => 1,'km' => 1,'suma' => 0,'userid' => $USER['id'],'car' => $sql_select_car['nomer'],'added' => $pmon_clock
			);
			$db->SQLinsert('users_odometr',$sqlinsert);
		}
		$go->go('/?do=car&act=list');
	break;	
	case'updatecar':		
		$id = isset($_POST['carid']) ? Clean::int($_POST['carid']) : 0;
		$to = isset($_POST['to']) ? Clean::int($_POST['to']) : 0;
		$odometr = isset($_POST['odometr']) ? Clean::int($_POST['odometr']) : 0;
		$type_motor = isset($_POST['type_motor']) ? Clean::int($_POST['type_motor']) : 1;
		$derjnomer = isset($_POST['derjnomer']) ? Clean::text($_POST['derjnomer']) : null;	
		$modelauto = isset($_POST['modelauto']) ? Clean::text($_POST['modelauto']) : null;	
		if(isset($odometr) && isset($derjnomer) && isset($modelauto) && $id>0){
			$update[] = "`type_motor` = '{$type_motor}'";
			$update[] = "`zamina_masla` = '{$to}'";
			$update[] = "`odometr` = '{$odometr}'";
			$update[] = "`nomer` = '{$derjnomer}'";
			$update[] = "`name` = '{$modelauto}'";
			$sql = "UPDATE car SET " . implode(',', $update) . " WHERE id = '{$id}'";
			$db->query($sql);
		}
		$go->go('/?do=car&act=car');	
	break;	
	case'editcar':	
		$carid = isset($_GET['carid']) ? Clean::int($_GET['carid']) : 0;
		if($carid>0){
		$car = $db->Simple("SELECT * FROM `car` WHERE id = '{$carid}' LIMIT 1");
			if(!empty($car['id'])){
				$result .= '
					<div id="onu-speedbar">
						<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
						<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
						<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагувати автомобіль</span>
					</div>
				';
				$content .= '
					<div class="nav-fiber p10">
						<form action="/?do=car" method="post">
						<input name="carid" type="hidden" value="'.$car['id'].'">
						<input name="act" type="hidden" value="updatecar">';
				$type_motor = '
					<option value="1" '.($car['type_motor']==1?'selected':'').'>Бензин</option>
					<option value="2" '.($car['type_motor']==2?'selected':'').'>Дизель</option>
					<option value="3" '.($car['type_motor']==3?'selected':'').'>Газ</option>
					<option value="4" '.($car['type_motor']==4?'selected':'').'>Електро</option>';
				$content .='
					<label for="type">Тип палива:</label>
					<select class="select" name="type_motor" id="type_motor">'.$type_motor.'</select><br>';		
				$content .='
					<label for="type">Державний номер:</label>
					<input name="derjnomer" type="text" pattern="[a-zA-Z0-9]+" required="" autocomplete="off" style="width:10%;" value="'.$car['nomer'].'"><br>';			
				$content .='
					<label for="type">Модель автомобіля:</label>
					<input name="modelauto" type="text" required="" autocomplete="off" style="width:30%;" value="'.$car['name'].'"><br>';			
				$content .='
					<label for="type">Початковий показник одометра:</label>
					<input name="odometr" type="text" required="" autocomplete="off" style="width:10%;" value="'.$car['odometr'].'"><br>';			
				$content .='
					<input type="submit" value="'.$lang['update'].'">
					</form>
				</div>';	
			}
		}		
	break;	
	case'addcto':		
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<a class="brmhref" href="/?do=car&act=cto"><i class="fi fi-rr-angle-left"></i>Технічний огляд автомобіля</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати технічний огляд</span>
			</div>
		';	
		$type_motor = '';
		$content .= '
			<div class="nav-fiber p10">
				<form action="/?do=car" method="post">
				<input name="act" type="hidden" value="savetocar">';
		$sql_car = $db->SimpleWhile("SELECT * FROM car");
		if(isset($sql_car) && count($sql_car) > 0){
			foreach($sql_car as $datid => $car){			
				$type_motor .= '<option value="'.$car['id'].'">'.$car['name'].' - '.$car['nomer'].'</li></option>';
			}
		}
		$content .='
			<label for="type">Автомобіль:</label>
			<select class="select" name="carid" id="carid">'.$type_motor.'</select><br>';		
		$content .='
			<label for="type">Назва ТО:</label>
			<input name="name" type="text" required="" autocomplete="off" style="width:50%;"><br>';	
		$content .= list_cto();
		$content .='
			<label for="type">Детальний опис технічного огляду:</label>
			<textarea id="note" name="note" style="width:50%;"></textarea><br>';	
		$content .='
			<label for="type">Початковий показник одометра:</label>
			<input name="odometr" type="text" required="" autocomplete="off" style="width:10%;"><br>';		
		$content .='
			<label for="type">Кількість кілометрів:</label>
			<input name="km" type="text" required="" autocomplete="off" style="width:10%;"><br>';			
		$content .='
			<input type="submit" value="'.$lang['added'].'">
			</form>
		</div>';
	break;	
	case'savetocar':
		$typesto = '';	
		$carid = isset($_POST['carid']) ? Clean::int($_POST['carid']) : 0;
		$start_odometer = isset($_POST['odometr']) ? Clean::int($_POST['odometr']) : 0;
		$km = isset($_POST['km']) ? Clean::int($_POST['km']) : 0;
		$name = isset($_POST['name']) ? Clean::text($_POST['name']) : null;	
		$note = isset($_POST['note']) ? Clean::text($_POST['note']) : null;	
		if ($_POST['inspection']) {			
			$selected_items = [];
			if (is_array($_POST['inspection'])) {
				foreach ($_POST['inspection'] as $item) {
					$item = filter_var($item, FILTER_SANITIZE_STRING);
					if (in_array($item, $valid_options_car)) {
						$selected_items[] = $item;
					}
				}
			}
			$typesto = implode(',', $selected_items);
		}		
		if(isset($carid) && isset($start_odometer) && isset($km) && isset($name) && $carid>0 && $start_odometer>0 && $km>0){
			$added = date('Y-m-d H:i:s');
			$sql = "INSERT INTO car_cto (`typesto`,`status`,`added`,`start_odometer`,`km`,`name`,`note`,`car_id`) VALUES 
			('$typesto','1','{$added}','{$start_odometer}','{$km}','{$name}','{$note}','{$carid}')";
			$db->query($sql);
		}
		$go->go('/?do=car&act=cto');
	break;	
	case'getto':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		if(isset($id) && $id>0){
			$car_to = $db->Simple("SELECT * FROM `car_cto` WHERE id = '{$id}' LIMIT 1");
			if(isset($car_to['id']) && $car_to['id'] > 0){
				if (!empty($car_to['typesto'])) {
							$content = '<div class="to_img_car_block">';
							$types = explode(',', $car_to['typesto']);
							foreach ($types as $type) {
								if (isset($imageMapping[$type])) {
									$content .= '<div class="block_to">';
									$content .= '<img src="'.$imageMapping[$type].'">'.$name_options_car[$type].'';
									$content .= '</div>';
								}
							}
							$content .= '</div>';
						}
				echo'
				<div class="to_information">
				'.($car_to['status']==2 ? '<div class="alarm_to">Необхідно виконати ТО автомобіля</div>' : '').'
				<h1>'.$car_to['name'].'</h1>
				<div class="panel_to">
					<span class="to_finish" onclick="car(\'tofinish\',\''.$car_to['id'].'\')">Виконано</span>
					<span class="to_edit" onclick="car(\'toedit\',\''.$car_to['id'].'\')">Редагувати</span>
					<span class="to_del" onclick="car(\'todelet\',\''.$car_to['id'].'\')">Видалити</span>
				</div>
				<div id="ajax_tocar"></div>
				'.(!empty($car_to['note']) ? '<div class="note">'.$car_to['note'].'</div>' : '').'
				'.$content.'				
				</div>';
			}
		}
		die;
	break;	
	case'tofinish':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		if(is_valid_id($id)){
			$car_to = $db->Simple("SELECT * FROM `car_cto` WHERE id = '{$id}' LIMIT 1");
			if(is_valid_id($car_to['id'])){
				echo '<label for="type">Дата виконання:</label><br><input type="datetime-local" name="deadline" id="deadline" required  style="width:30%;"><br>';
				echo '<label for="type">Показник одометра:</label><input id="odometr" name="odometr" type="text" required="" autocomplete="off" style="width:30%;">';				
				echo '<label for="type">Сума за виконане ТО:</label><input id="money" name="money" type="text" required="" autocomplete="off" style="width:30%;margin-bottom: 10px;">';
				echo'<div class="panel_to_s"><span class="to_finish" onclick="car(\'takefinish\',\''.$car_to['id'].'\')">Закрити ТО</span></div>';
			}
		}
		exit;
	break;
	case'takefinish':
		$sqlinsert = array();		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		$odometr = isset($_POST['odometr']) ? Clean::int($_POST['odometr']) : 0;
		if(is_valid_id($odometr)){
			$sqlinsert['close_odometr'] = $odometr;
		}
		$sqlinsert['status'] = 3;
		$money = isset($_POST['money']) ? Clean::int($_POST['money']) : 0;
		if(is_valid_id($money)){
			$sqlinsert['close_money'] = $money;
		}
		$deadline = isset($_POST['deadline']) ? Clean::text($_POST['deadline']) : 0;
		if(isset($deadline)){
			$deadline = str_replace("T", " ", $deadline);
			$sqlinsert['close'] = $deadline.':00';
		}else{
			$sqlinsert['close'] = date('Y-m-d H:i:s');
		}
		if(is_valid_id($id) && is_valid_id($odometr) && is_valid_id($money) && isset($sqlinsert)){
			$db->SQLupdate('car_cto',$sqlinsert,['id'=>$id]);
		}
		exit;
	break;
	case'toedit':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		if(is_valid_id($id)){
			print_R($id);
		}
		exit;	
	break;
	case'todelet':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		if(is_valid_id($id)){
			$car_to = $db->Simple("SELECT * FROM `car_cto` WHERE id = '{$id}' LIMIT 1");
			if(is_valid_id($car_to['id'])){
				$db->SQLdelete('car_cto',['id' => $car_to['id']]);
			}
		}
		exit;
	break;	
	case'cto':	
		$select_car = array();
		$sql_car = $db->SimpleWhile("SELECT * FROM car");
		if(isset($sql_car) && count($sql_car) > 0){
			foreach($sql_car as $dataid => $car){ 
				$select_car[$car['id']] = $car;
			}
		}
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Технічний огляд автомобіля</span>
			</div>
		';
		$content .= '<table class="m10b"><tbody><tr><td><a href="/?do=car&act=addcto" class="obladd">Додати ТО</a></td></tr></tbody></table><div id="backup_panel" class="popupContainer"><div class="popupContent"><div id="result_ajax"></div></div></div>		';
		$content .= '<table class="resp-tab" id="dataTable"><thead><tr>
			<th width="5%">Статус</th>
			<th>Назва ТО</th>
			<th width="10%">Автомобіль</th>
			<th width="10%">Держ.знак</th>
			<th width="10%">Початковий</th>
			<th width="7%">ТО</th>
			<th width="7%">Виїхав</th>
			<th width="10%">Запланований</th>
			<th width="10%">Актуальний</th>			
			</tr></thead><tbody>
			';
		$get_car_cto = $db->SimpleWhile("SELECT * FROM car_cto WHERE status != '3' ORDER BY status DESC");
		if(isset($get_car_cto) && count($get_car_cto)>0){
			foreach($get_car_cto as $id => $car) {				
				$to = calculateMileage($car['car_id'], $car['id'], $car['start_odometer'], $car['km']);
				$car_to = status_to($car['status']);
				$content .= '<tr class="'.$car_to['status'].'">
					<td class="lock_odometr"><img src="'.$car_to['img'].'"></td>
					<td class="td_names popup_href">
						<a href="#" class="db openPopup" data-popup-id="backup_panel" onclick="infoto(\''.$car['id'].'\');"><img src="../style/img/edit.png">'.$car['name'].'</a>';
						if(!empty($car['note'])){
							$content .=  '<div class="min_to_note">'.cut_text($car['note'], 100).'</div>';
						}						
						if(isset($to['progress']) && $to['progress']<100){
							$content .= '<div class="to_status_bar">
								<div class="to_prog_bar" style="width:'.$to['progress'].'%;">
							</div>';
						}
						$content .= '</div>';
						if (!empty($car['typesto'])) {
							$content .= '<div class="to_img_car">';
							$types = explode(',', $car['typesto']);
							foreach ($types as $type) {
								if (isset($imageMapping[$type])) {
									$content .= '<img src="'.$imageMapping[$type].'">';
								}
							}
							$content .= '</div>';
						}
				$content .= '</td>
					<td class="transport">
						<img src="../style/img/transport.png">'.$select_car[$car['car_id']]['name'].'
					</td>
					<td><div class="num_car">'.$select_car[$car['car_id']]['nomer'].'</div></td>
					<td><span class="signal2">'.$car['start_odometer'].'</span></td>
					<td><span class="signal3">'.$car['km'].'</span></td>
					<td>'.$car['mileage'].'</td>
					<td><span class="signal2">'.$car['start_odometer']+$car['km'].'</span></td>					
					<td><span class="signal2">'.$to['curent'].'</span></td>
				</tr>';
			}
		}else{
			$content .= '<tr class="top_blue">
				<td colspan="7">'.$lang['empty'].'</td>
				</tr>';
		}
		$content .='</table>';
	break;	
	case'car':
		$usr_array = array();
		$getusr = $db->SimpleWhile("SELECT id, username FROM users");
		if(isset($getusr) && count($getusr)>0){
			foreach($getusr as $id => $usr) {
				$usr_array[$usr['id']]['username'] = $usr['username'];
			}			
		}
		$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
				<a class="brmhref" href="/?do=car"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Список автомобілів</span>
			</div>
		';
		$sql_where = '';
		$content .= '<table class="resp-tab" id="dataTable"><thead><tr>
				<th width="30%">Автомобіль</th>
				<th width="10%">Держ.знак</th>
				<th width="10%">Показник</th>
				<th width="15%">Тип палива</th>
				<th width="15%">Працівник</th>
				</tr></thead><tbody>';
		$sql = "SELECT * FROM car {$sql_where}";
		$sql_car = $db->SimpleWhile($sql);
		if(isset($sql_car) && count($sql_car) > 0){
			foreach($sql_car as $datid => $car){
				$to_list = '';
				$used = '';
				$get_use_usr = $db->SimpleWhile("SELECT * FROM users_car WHERE car_id = '{$car['id']}'");
				if(isset($get_use_usr) && count($get_use_usr)>0){
					foreach($get_use_usr as $idid => $used_car) {
						if(isset($usr_array[$used_car['moder_id']]['username'])){
						$used .= ''.$usr_array[$used_car['moder_id']]['username'].'<a href="/?do=car&act=deletuser&id='.$used_car['id'].'"><img class="img_sub" src="../style/img/close.png"></a>, ';
						}
					}
				}
				$get_to_car = $db->SimpleWhile("SELECT * FROM car_cto WHERE car_id = '{$car['id']}' AND status = 3");
				if(isset($get_to_car) && count($get_to_car)>0){
					$to_list .= '<div class="finish_to_list">';
					foreach($get_to_car as $toid => $to) {
						$to_list .= '<div class="min_to">';
						$to_list .= '<img src="../style/img/service_car.png">
						<span class="dates">'.$to['close'].'</span>
						<span class="dates">'.$to['close_odometr'].'</span>
						<span>'.$to['name'].'</span>';
						$to_list .= '</div>';
					}
					$to_list .= '</div>';
				}
				
				$used .= '<div id="car_'.$car['id'].'" ><img onclick="get_user_car('.$car['id'].')" src="../style/img/add.png"></div>';
				$content .= '<tr>
					<td class="td_names_car">
					<a href="#">'.$car['name'].'</a>
					<a href="/?do=car&act=editcar&carid='.$car['id'].'"><img src="../style/img/edit.png"></a>
					'.$to_list.'
					</td>
					<td>'.$car['nomer'].'</td>
					<td>'.$car['odometr'].'</td>
					<td>'.getMotorTypeName($car['type_motor']).'</td>
					<td>'.$used.'</td>
					</tr>';
			}
		}else{
			$content .= '<tr class="top_blue">
					<td colspan="4">'.$lang['empty'].'</td>
					</tr>';
		}
		$content .='</table>';
		$content .= '
			<table><td>
				<a href="/?do=car&act=addcar" class="obladd">Додати автомобіль</a>
			</td></table>';
	break;
	default:
		$result .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>Головна сторінка</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Автомобільний парк провайдера</span>
		</div>
		';
		$content .= '
			<div class="admin-1">
				<div class="main-panel">
					<div class="admin-zvit">';		
		$content .= '<a href="/?do=car&act=car"><img src="../style/img/list_driver.png"><span>Список автомобілів</span></a>';
		$content .= '<a href="/?do=car&act=list"><img src="../style/img/time_driver.png"><span>Список показників</span></a>';
		$content .= '<a href="/?do=car&act=addodometr"><img src="../style/img/start_car.png"><span>Подати показник</span></a>';
		$content .= '<a href="/?do=car&act=zvit"><img src="../style/img/zvit_driver.png"><span>Формування звітів</span></a>';
		$content .= '<a href="/?do=car&act=cto"><img src="../style/img/sto_car.png"><span>ТО автомобіля</span></a>';
		
		$content .= '
					</div>
				</div>
			</div>
			<div class="admin-1">
				<div class="main-panel">
					<div class="admin-zvit">';
					
		$sql = "SELECT * FROM car";
		$sql_car = $db->SimpleWhile($sql);
		if(isset($sql_car) && count($sql_car) > 0){
			foreach($sql_car as $datid => $car){			
				$content .= '<a href="/?do=car&act=list&carid='.$car['id'].'"><img src="../style/img/list_car.png">
					<span><div class="num_car">'.$car['nomer'].'</div></span>
				</a>';
			}
		}
		$content .= '
					</div>
				</div>
			</div>';
}
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.$content.'</div>');
$tpl->compile('content');
$tpl->clear();
}else{
	$go->redirect('main');
}
?>