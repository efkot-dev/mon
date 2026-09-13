<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$result = '';
$content = '';
$metatags = [
	'title'=>'Показники одометрів',
	'description'=>'Показники одометрів',
	'page'=>'odometr'
];
switch($act){
	case'zvit':
		$u = isset($_GET['u']) ? Clean::int($_GET['u']) : null;
		$num_car = isset($_GET['c']) ? Clean::text($_GET['c']) : null;
		$start_date = isset($_GET['start_date']) ? Clean::text($_GET['start_date']) : null;
		$end_date = isset($_GET['end_date']) ? Clean::text($_GET['end_date']) : null;
		$usr = $db->Simple("SELECT * FROM users WHERE id = '{$u}' LIMIT 1");
		$where[] = "userid = '" . $u . "'";
		$where[] = "car = '" . $num_car . "'";
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
		if(!empty($usr['id'])){
			$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/?do=zvit"><i class="fi fi-rr-apps"></i>Звітність</a>
				<a class="brmhref" href="/?do=odometr"><i class="fi fi-rr-left"></i>Показники одометрів</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Звіт по '.strtoupper($num_car).' '.$select_date.'</span>
			</div>';
			$price = (isset($_GET['price']) ? floatval(str_replace(',', '.', $_GET['price'])) : 0);
			$content .= '
			<form action="/" method="get">
			<input type="hidden" name="do" value="odometr">
			<input type="hidden" name="act" value="zvit">
			<input type="hidden" name="c" value="'.$num_car.'">
			<input type="hidden" name="u" value="'.$u.'">
				<div class="main_blocks"><div class="lf_1">
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
		}else{
			
		}
	break;	
}
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.$content.'</div>');
$tpl->compile('content');
$tpl->clear();
?>
