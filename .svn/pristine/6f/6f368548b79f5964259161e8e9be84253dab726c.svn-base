<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$speedbar = '';
$speedbar_block = '';
$content = '';
$content_head = '';
switch($act){
	case 'zvit3': 
			$metatags = [
			'title'=>'Створення звіту реєстрація ону на комутаторах',
			'description'=>'Створення звіту реєстрація ону на комутаторах',
			'page'=>'zvit'
		];
		$search = false;
		$select_start_date = '';
		$select_end_date = '';
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$formatmac = isset($_POST['formatmac']) ? Clean::int($_POST['formatmac']) : 0;
			$selectolt = isset($_POST['selectolt']) ? Clean::int($_POST['selectolt']) : 0;
			$sortoffline = isset($_POST['sortoffline']) ? Clean::text($_POST['sortoffline']) : null;
			$sortport = isset($_POST['sortport']) ? Clean::text($_POST['sortport']) : null;
			$start_date = isset($_POST['start_date']) ? Clean::text($_POST['start_date']) : null;
			$end_date = isset($_POST['end_date']) ? Clean::text($_POST['end_date']) : null;
			$sql_orderby = ' ORDER BY added DESC';
			$sql_where = '';
			$where = [];
			if ($selectolt > 0) {
				$where[] = "olt = '" . $selectolt."'";
			}					
			if (isset($start_date) && !empty($start_date)) {
				$select_start_date = $start_date;
				$start_date .= ' 00:00:00';				
				$where[] = "added >= '" . $start_date . "'";
			}else{
				$where[] = "added >= '" . date('Y-m-d H:i:s') . "'";
			}
			if(isset($sortoffline) && !empty($sortoffline)){
				$where[] = "status = '2'";	
			}
			if (isset($end_date) && !empty($end_date)) {
				$select_end_date = $end_date;
				$end_date .= ' 23:59:59';				
				$where[] = "added <= '" . $end_date . "'";
			}
			if (!empty($where)) {
				$sql_where = ' WHERE ' . implode(' AND ', $where);
			}
			$selectdata = 'status, olt, inface, type, rx, added, offline, online, mac, sn, dist, zte_idport';
			$zaput = "SELECT {$selectdata} FROM onus $sql_where $sql_orderby";
			$sqlonus = $db->SimpleWhile($zaput);
			if(isset($sortport) && $sortport){
				usort($sqlonus, function($a, $b) {
					return $b['zte_idport'] - $a['zte_idport'];
				});
			}else{
				usort($sqlonus, function($a, $b) {
					return $a['zte_idport'] - $b['zte_idport'];
				});				
			}
			if(isset($sqlonus) && !empty($sqlonus)&& count($sqlonus)>0){
				$zvit .= '<table class="resp-tab" id="dataTable"><thead><tr>';
				if(isset($selectolt) && $selectolt>0){
					$getswitch = $db->Fast('switch','*',['id'=>$selectolt]);
					$zvit .= '<th width="10%">Olt</th>';
				}
				$zvit .= '<th width="10%">Interface</th>';
				$zvit .= '<th width="10%">Mac_Sn</th><th>Signal</th><th>Dist</th><th width="15%">After time added</th><th width="15%">Added</th><th width="15%">Last online</th><th>Name</th></tr></thead><tbody>';
				foreach($sqlonus as $onus) {
					$zvit .= '<tr>'; 
					if(isset($selectolt) && $selectolt>0){
						$zvit .= '<td>'.$getswitch['place'].'</td>'; 
					}
					$zvit .= '<td>'.$onus['type'].' '.$onus['inface'].'</td>'; 
					if(isset($formatmac) && $formatmac>0){
						$onukey = (!empty($onus['mac'])?$onus['mac']:(!empty($onus['sn'])?$onus['sn']:'N/A'));
						$onukey = formatmac($onukey, $formatmac);
					}else{
						$onukey = (!empty($onus['mac'])?$onus['mac']:(!empty($onus['sn'])?$onus['sn']:'N/A'));
					}
					$zvit .= '<td>'.$onukey.'</td><td>'.(!empty($onus['rx'])?$onus['rx']:'-').'</td><td>'.(!empty($onus['dist'])?$onus['dist']:'').'</td><td>'.(!empty($onus['added'])?aftertime($onus['added']):'').'</td><td>'.(!empty($onus['added'])?$onus['added']:'').'</td><td>'.(!empty($onus['online'])?$onus['online']:'').'</td><td>'.(!empty($onus['name'])?$onus['name']:'').'</td></tr>';    
				}
				$zvit .= '</tbody></table>';
				$search = true;
			}			
		}		
		$sqlswitch = getSwitchOlt();
		$select_olt_list = '';
		if (isset($sqlswitch) && !empty($sqlswitch)) {
			foreach ($sqlswitch as $switch) {
				$place = $switch['place'];
				$oltmodel = $switch['inf'] . ' ' . $switch['model'];
				$selecet_olt = (isset($selectolt) && $selectolt==$switch['id'] ? 'selected':'');
				$select_olt_list .= sprintf('<option value="%d" '.$selecet_olt.'>%s %s</option>',$switch['id'],$place,$oltmodel);
			}
		}
		$speedbar .='<a class="brmhref" href="/?do=zvit"><i class="fi fi-rr-apps"></i>Звіти</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Звіт реєстрація ону на комутаторах</span>';
		$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
		$content .= '<div class="main_blocks">
		<div class="lf_1">
		<form action="/?do=zvit" method="post">
		<input type="hidden" name="act" value="zvit3">
		<div class="lf_1">
			<div class="block_4"><select name="selectolt" class="sort icon-arowDown open"><option value="0">'.$lang['allswitch'].'</option>'.$select_olt_list.'</select></div>
			<div class="block_4"><select name="formatmac" class="sort icon-arowDown open">
			<option value="1" '.(isset($formatmac) && $formatmac==1 ? 'selected' : '').'>e0:67:b3:3c:4a:36</option>
			<option value="2" '.(isset($formatmac) && $formatmac==2 ? 'selected' : '').'>e067.b33c.4a36</option>
			<option value="3" '.(isset($formatmac) && $formatmac==3 ? 'selected' : '').'>e067-b33c-4a36</option>
			<option value="4" '.(isset($formatmac) && $formatmac==4 ? 'selected' : '').'>e067:b33c:4a36</option>
			<option value="5" '.(isset($formatmac) && $formatmac==5 ? 'selected' : '').'>e0.67.b3.3c.4a.36</option>
			</select></div>
			<div class="block_4"><label for="start_date">Оффлайн</label>
				<input type="checkbox" name="sortoffline" class="check_box_port" '.(isset($sortoffline) && $sortoffline ? " checked" : "").'></div>
			<div class="block_4"><label for="start_date">По портах:</label>
				<input type="checkbox" name="sortport" class="check_box_port" '.(isset($sortport) && $sortport ? " checked" : "").'></div>
			<div class="block_4"><label for="start_date">Від:</label>
				<input type="date" lang="uk" id="start_date" name="start_date" '.(isset($select_start_date) && $select_start_date ? ' value="'.$select_start_date.'"' : "").'></div>
			<div class="block_4"><label for="end_date">До:</label>
				<input type="date" lang="uk" id="end_date" name="end_date" '.(isset($select_end_date) && $select_end_date ? ' value="'.$select_end_date.'"' : "").'></div>
			<div class="block_4"><input class="go" type="submit" value="'.$lang['search'].'"></div>
		</div>
		</form>
		<div class="lf_1">
			'.($search?'<button style="background-color: #118f16;" onclick="PMonexportToExcel(\'dataTable\',\'onu_offline_'.date('Y-m-d H:i:s').'\')">Експорт в Excel</button>	':'').'
		</div>		
		</div></div>';		
		if($search){
			$content .= '<div id="ajax-zvit">'.$zvit.'<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script></div>';
		}else{
			$content .= '';
		}
	break;		
	case 'zvit2': 	
		$metatags = [
			'title'=>'Створення звітів',
			'description'=>'Створення звітів',
			'page'=>'zvit'
		];
		$search = false;
		$select_start_date = '';
		$select_end_date = '';
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$formatmac = isset($_POST['formatmac']) ? Clean::int($_POST['formatmac']) : 0;
			$selectolt = isset($_POST['selectolt']) ? Clean::int($_POST['selectolt']) : 0;
			$startsignal = isset($_POST['startsignal']) ? Clean::text($_POST['startsignal']) : null;
			$endsignal = isset($_POST['endsignal']) ? Clean::text($_POST['endsignal']) : null;
			$sortoffline = isset($_POST['sortoffline']) ? Clean::text($_POST['sortoffline']) : null;
			$sortport = isset($_POST['sortport']) ? Clean::text($_POST['sortport']) : null;
			$start_date = isset($_POST['start_date']) ? Clean::text($_POST['start_date']) : null;
			$end_date = isset($_POST['end_date']) ? Clean::text($_POST['end_date']) : null;
			$sql_where = '';
			$where = [];
			if ($selectolt > 0) {
				$where[] = "olt = '" . $selectolt."'";
			}	
			if(isset($sortoffline) && !empty($sortoffline)){
				$sql_orderby = ' ORDER BY rx ASC';
			}else{
				$sql_orderby = ' ORDER BY rx DESC';			
			}			
			$where[] = "status = '1'";		
			if(isset($startsignal) && !empty($startsignal)) {
				$startsignal = trim(strip_tags($startsignal));
				$startsignal = str_replace("-", "", $startsignal );
				$sql_startsignal = "-".$startsignal;
				$where[] = "rx <= '" . $sql_startsignal . "'";
			}			
			if(isset($endsignal) && !empty($endsignal)) {
				$endsignal = trim(strip_tags($endsignal));
				$endsignal = str_replace("-", "", $endsignal );
				$sql_endsignal = "-".$endsignal;
				$where[] = "rx >= '" . $sql_endsignal . ".99'";
			}	
			if (isset($start_date) && !empty($start_date)) {
				$select_start_date = $start_date;
				$start_date .= ' 00:00:00';				
				$where[] = "changerx <= '" . $start_date . "'";
			}else{
				$where[] = "changerx <= '" . date('Y-m-d H:i:s') . "'";
			}
			if (isset($end_date) && !empty($end_date)) {
				$select_end_date = $end_date;
				$end_date .= ' 23:59:59';				
				$where[] = "changerx >= '" . $end_date . "'";
			}
			if (!empty($where)) {
				$sql_where = ' WHERE ' . implode(' AND ', $where);
			}
			$selectdata = 'status, olt, inface, type, rx, added, changerx, online, mac, sn, dist, zte_idport';
			$zaput = "SELECT {$selectdata} FROM onus $sql_where $sql_orderby";
			$sqlonus = $db->SimpleWhile($zaput);
			if(isset($sqlonus) && !empty($sqlonus)&& count($sqlonus)>0){
				$zvit .= '<table class="resp-tab" id="dataTable"><thead><tr><th width="10%">Interface</th><th width="10%">Mac_Sn</th><th width="5%">Rx</th><th width="5%">Dist</th><th width="15%">Timer</th><th width="15%">Changerx</th><th width="15%">Added</th><th width="15%">Last online</th><th>information</th></tr></thead><tbody>';
				foreach($sqlonus as $onus) {
					$zvit .= '<tr><td>'.$onus['type'].' '.$onus['inface'].'</td>';
					if(isset($formatmac) && $formatmac>0){
						$onukey = (!empty($onus['mac'])?$onus['mac']:(!empty($onus['sn'])?$onus['sn']:'N/A'));
						$onukey = formatmac($mac, $formatmac);
					}else{
						$onukey = (!empty($onus['mac'])?$onus['mac']:(!empty($onus['sn'])?$onus['sn']:'N/A'));
					}
					$zvit .= '<td>'.$onukey.'</td><td>'.(!empty($onus['rx'])?$onus['rx']:'-').'</td><td>'.(!empty($onus['dist'])?$onus['dist']:'').'</td><td>'.(!empty($onus['changerx'])?aftertime($onus['changerx']):'').'</td><td>'.(!empty($onus['changerx'])?$onus['changerx']:'').'</td><td>'.(!empty($onus['added'])?$onus['added']:'').'</td><td>'.(!empty($onus['online'])?$onus['online']:'').'</td><td>'.(!empty($onus['name'])?$onus['name']:'').'</td></tr>';    
				}
				$zvit .= '</tbody></table>';
				$search = true;
			}
		}
		$sqlswitch = getSwitchOlt();
		$select_olt_list = '';
		if (isset($sqlswitch) && !empty($sqlswitch)) {
			foreach ($sqlswitch as $switch) {
				$place = $switch['place'];
				$oltmodel = $switch['inf'] . ' ' . $switch['model'];
				$selecet_olt = (isset($selectolt) && $selectolt==$switch['id'] ? 'selected':'');
				$select_olt_list .= sprintf('<option value="%d" '.$selecet_olt.'>%s %s</option>',$switch['id'],$place,$oltmodel);
			}
		}
		$start_select_rx = '<select name="startsignal" class="sort icon-arowDown"><option value="20" '.(isset($startsignal) && $startsignal =='20'?'selected':'').'>-20dBm</option><option value="21" '.(isset($startsignal) && $startsignal =='21'?'selected':'').'>-21dBm</option><option value="22" '.(isset($startsignal) && $startsignal =='22'?'selected':'').'>-22dBm</option><option value="23" '.(isset($startsignal) && $startsignal =='23'?'selected':'').'>-23dBm</option><option value="24" '.(isset($startsignal) && $startsignal =='24'?'selected':'').'>-24dBm</option><option value="25" '.(isset($startsignal) && $startsignal =='25'?'selected':'').'>-25dBm</option><option value="26" '.(isset($startsignal) && $startsignal =='26'?'selected':'').'>-26dBm</option><option value="27" '.(isset($startsignal) && $startsignal =='27'?'selected':'').'>-27dBm</option><option value="28" '.(isset($startsignal) && $startsignal =='28'?'selected':'').'>-28dBm</option><option value="29" '.(isset($startsignal) && $startsignal =='29'?'selected':'').'>-29dBm</option><option value="30" '.(isset($startsignal) && $startsignal =='30'?'selected':'').'>-30dBm</option><option value="31" '.(isset($startsignal) && $startsignal =='31'?'selected':'').'>-31dBm</option><option value="32" '.(isset($startsignal) && $startsignal =='32'?'selected':'').'>-32dBm</option><option value="33" '.(isset($startsignal) && $startsignal =='33'?'selected':'').'>-33dBm</option></select>';
		$end_select_rx = '<select name="endsignal" class="sort icon-arowDown"><option value="20" '.(isset($endsignal) && $endsignal =='20'?'selected':'').'>-20dBm</option><option value="21" '.(isset($endsignal) && $endsignal =='21'?'selected':'').'>-21dBm</option><option value="22" '.(isset($endsignal) && $endsignal =='22'?'selected':'').'>-22dBm</option><option value="23" '.(isset($endsignal) && $endsignal =='23'?'selected':'').'>-23dBm</option><option value="24" '.(isset($endsignal) && $endsignal =='24'?'selected':'').'>-24dBm</option><option value="25" '.(isset($endsignal) && $endsignal =='25'?'selected':'').'>-25dBm</option><option value="26" '.(isset($endsignal) && $endsignal =='26'?'selected':'').'>-26dBm</option><option value="27" '.(isset($endsignal) && $endsignal =='27'?'selected':'').'>-27dBm</option><option value="28" '.(isset($endsignal) && $endsignal =='28'?'selected':'').'>-28dBm</option><option value="29" '.(isset($endsignal) && $endsignal =='29'?'selected':'').'>-29dBm</option><option value="30" '.(isset($endsignal) && $endsignal =='30'?'selected':'').'>-30dBm</option><option value="31" '.(isset($endsignal) && $endsignal =='31'?'selected':'').'>-31dBm</option><option value="32" '.(isset($endsignal) && $endsignal =='32'?'selected':'').'>-32dBm</option><option value="33" '.(isset($endsignal) && $endsignal =='33'?'selected':'').'>-33dBm</option></select>';
		$speedbar .='<a class="brmhref" href="/?do=zvit"><i class="fi fi-rr-apps"></i>Звіти</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Список ONU з поганими сигналами</span>';
		$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
		$content .= '<div class="main_blocks">
		<div class="lf_1">
			<form action="/?do=zvit" method="post">
			<input type="hidden" name="act" value="zvit2">
				<div class="lf_1">
					<div class="block_4"><select name="selectolt" class="sort icon-arowDown open"><option value="0">'.$lang['allswitch'].'</option>'.$select_olt_list.'</select></div>
					<div class="block_4"><label for="start_date">Від:</label></div>
					<div class="block_4">'.$start_select_rx.'</div>						
					<div class="block_4"><label for="start_date">До:</label></div>
					<div class="block_4">'.$end_select_rx.'</div>
					<div class="block_4"><label for="start_date">Від більшого:</label><input type="checkbox" name="sortoffline" class="check_box_port" '.(isset($sortoffline) && $sortoffline ? " checked" : "").'></div>
					<div class="block_4"><label for="start_date">Від:</label><input type="date" lang="uk" id="start_date" name="start_date" '.(isset($select_start_date) && $select_start_date ? ' value="'.$select_start_date.'"' : "").'></div>
					<div class="block_4"><label for="end_date">До:</label><input type="date" lang="uk" id="end_date" name="end_date" '.(isset($select_end_date) && $select_end_date ? ' value="'.$select_end_date.'"' : "").'></div>
					<div class="block_4"><input class="go" type="submit" value="'.$lang['search'].'"></div>
				</div>
			</form>
			<div class="lf_1">'.($search?'<button style="background-color: #118f16;" onclick="PMonexportToExcel(\'dataTable\',\'onu_offline_'.date('Y-m-d H:i:s').'\')">Експорт в Excel</button>	':'').'</div>		
		</div>
		</div>';		
		if($search){
			$content .= '<div id="ajax-zvit">'.$zvit.'<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script></div>';
		}else{
			$content .= '';
		}
	break;		
	case 'zvit1': 
		$metatags = [
			'title'=>'Створення звітів',
			'description'=>'Створення звітів',
			'page'=>'zvit'
		];
		$search = false;
		$select_start_date = '';
		$select_end_date = '';
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$formatmac = isset($_POST['formatmac']) ? Clean::int($_POST['formatmac']) : 0;
			$selectolt = isset($_POST['selectolt']) ? Clean::int($_POST['selectolt']) : 0;
			$sortoffline = isset($_POST['sortoffline']) ? Clean::text($_POST['sortoffline']) : null;
			$sortport = isset($_POST['sortport']) ? Clean::text($_POST['sortport']) : null;
			$start_date = isset($_POST['start_date']) ? Clean::text($_POST['start_date']) : null;
			$end_date = isset($_POST['end_date']) ? Clean::text($_POST['end_date']) : null;
			if(isset($sortoffline) && !empty($sortoffline)){
				$sql_orderby = ' ORDER BY offline ASC';
			}else{
				$sql_orderby = ' ORDER BY offline DESC';			
			}
			$sql_where = '';
			$where = [];
			if ($selectolt > 0) {
				$where[] = "olt = '" . $selectolt."'";
			}		
			$where[] = "status = '2'";		
			if (isset($start_date) && !empty($start_date)) {
				$select_start_date = $start_date;
				$start_date .= ' 00:00:00';				
				$where[] = "offline <= '" . $start_date . "'";
			}else{
				$where[] = "offline <= '" . date('Y-m-d H:i:s') . "'";
			}
			if (isset($end_date) && !empty($end_date)) {
				$select_end_date = $end_date;
				$end_date .= ' 23:59:59';				
				$where[] = "offline >= '" . $end_date . "'";
			}
			if (!empty($where)) {
				$sql_where = ' WHERE ' . implode(' AND ', $where);
			}
			$selectdata = 'status, olt, inface, type, rx, added, offline, online, mac, sn, dist, zte_idport';
			$zaput = "SELECT {$selectdata} FROM onus $sql_where $sql_orderby";
			$sqlonus = $db->SimpleWhile($zaput);
			if(isset($sortport) && $sortport){
				usort($sqlonus, function($a, $b) {
					return $b['zte_idport'] - $a['zte_idport'];
				});
			}else{
				usort($sqlonus, function($a, $b) {
					return $a['zte_idport'] - $b['zte_idport'];
				});				
			}
			if(isset($sqlonus) && !empty($sqlonus)&& count($sqlonus)>0){
				$zvit .= '<table class="resp-tab" id="dataTable"><thead><tr><th width="10%">Interface</th><th width="10%">Mac_Sn</th><th width="5%">Rx</th><th width="5%">Dist</th><th width="15%">Timer</th><th width="15%">Offline</th><th width="15%">Added</th><th width="15%">Last online</th><th>information</th></tr></thead><tbody>';
				foreach($sqlonus as $onus) {
					$zvit .= '<tr><td>'.$onus['type'].' '.$onus['inface'].'</td>'; 
					if(isset($formatmac) && $formatmac>0){
						$onukey = (!empty($onus['mac'])?$onus['mac']:(!empty($onus['sn'])?$onus['sn']:'N/A'));
						$onukey = formatmac($onukey, $formatmac);
					}else{
						$onukey = (!empty($onus['mac'])?$onus['mac']:(!empty($onus['sn'])?$onus['sn']:'N/A'));
					}
					$zvit .= '<td>'.$onukey.'</td><td>'.(!empty($onus['rx'])?$onus['rx']:'-').'</td><td>'.(!empty($onus['dist'])?$onus['dist']:'').'</td><td>'.(!empty($onus['offline'])?aftertime($onus['offline']):'').'</td><td>'.(!empty($onus['offline'])?$onus['offline']:'').'</td><td>'.(!empty($onus['added'])?$onus['added']:'').'</td><td>'.(!empty($onus['online'])?$onus['online']:'').'</td><td>'.(!empty($onus['name'])?$onus['name']:'').'</td></tr>';    
				}
				$zvit .= '</tbody></table>';
				$search = true;
			}			
		}		
		$sqlswitch = getSwitchOlt();
		$select_olt_list = '';
		if (isset($sqlswitch) && !empty($sqlswitch)) {
			foreach ($sqlswitch as $switch) {
				$place = $switch['place'];
				$oltmodel = $switch['inf'] . ' ' . $switch['model'];
				$selecet_olt = (isset($selectolt) && $selectolt==$switch['id'] ? 'selected':'');
				$select_olt_list .= sprintf('<option value="%d" '.$selecet_olt.'>%s %s</option>',$switch['id'],$place,$oltmodel);
			}
		}
		$speedbar .='<a class="brmhref" href="/?do=zvit"><i class="fi fi-rr-apps"></i>Звіти</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Звіт ONU які оффлайн</span>';
		$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
		$content .= '<div class="main_blocks">
		<div class="lf_1">
		<form action="/?do=zvit" method="post">
		<input type="hidden" name="act" value="zvit1">
		<div class="lf_1">
			<div class="block_4"><select name="selectolt" class="sort icon-arowDown open"><option value="0">'.$lang['allswitch'].'</option>'.$select_olt_list.'</select></div>
			<div class="block_4"><select name="formatmac" class="sort icon-arowDown open">
			<option value="1" '.(isset($formatmac) && $formatmac==1 ? 'selected' : '').'>e0:67:b3:3c:4a:36</option>
			<option value="2" '.(isset($formatmac) && $formatmac==2 ? 'selected' : '').'>e067.b33c.4a36</option>
			<option value="3" '.(isset($formatmac) && $formatmac==3 ? 'selected' : '').'>e067-b33c-4a36</option>
			<option value="4" '.(isset($formatmac) && $formatmac==4 ? 'selected' : '').'>e067:b33c:4a36</option>
			<option value="5" '.(isset($formatmac) && $formatmac==5 ? 'selected' : '').'>e0.67.b3.3c.4a.36</option>
			</select></div>
			<div class="block_4"><label for="start_date">Від більшого:</label>
				<input type="checkbox" name="sortoffline" class="check_box_port" '.(isset($sortoffline) && $sortoffline ? " checked" : "").'></div>
			<div class="block_4"><label for="start_date">По портах:</label>
				<input type="checkbox" name="sortport" class="check_box_port" '.(isset($sortport) && $sortport ? " checked" : "").'></div>
			<div class="block_4"><label for="start_date">Від:</label>
				<input type="date" lang="uk" id="start_date" name="start_date" '.(isset($select_start_date) && $select_start_date ? ' value="'.$select_start_date.'"' : "").'></div>
			<div class="block_4"><label for="end_date">До:</label>
				<input type="date" lang="uk" id="end_date" name="end_date" '.(isset($select_end_date) && $select_end_date ? ' value="'.$select_end_date.'"' : "").'></div>
			<div class="block_4"><input class="go" type="submit" value="'.$lang['search'].'"></div>
		</div>
		</form>
		<div class="lf_1">
			'.($search?'<button style="background-color: #118f16;" onclick="PMonexportToExcel(\'dataTable\',\'onu_offline_'.date('Y-m-d H:i:s').'\')">Експорт в Excel</button>	':'').'
		</div>		
		</div></div>';		
		if($search){
			$content .= '<div id="ajax-zvit">'.$zvit.'<script lang="javascript" src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script></div>';
		}else{
			$content .= '';
		}
	break;		
	default:	
		$metatags = [
			'title'=>'Створення звітів',
			'description'=>'Створення звітів',
			'page'=>'zvit'
		];
		$content .= '<div class="admin-1"><div class="main-panel"><div class="admin-zvit">';
		$content .= '<a href="/?do=zvit&act=zvit1"><img src="../style/img/zvit1.png"><span>'.$lang['onu_offline_olt'].'</span></a>';
		$content .= '<a href="/?do=zvit&act=zvit2"><img src="../style/img/zvit1.png"><span>'.$lang['bad_signal'].'</span></a>';
		$content .= '<a href="/?do=zvit&act=zvit3"><img src="../style/img/module1.png"><span>'.$lang['newonu'].'</span></a>';
		#$content .= '<a href="/?do=odometr"><img src="../style/img/odometr.png"><span>Показники одметра</span></a>';
		$content .= '</div></div></div>';
}
$tpl->load_template('zvit.tpl');
$tpl->set('{speedbar}',$speedbar_block);
$tpl->set('{content_head}',$content_head);
$tpl->set('{content}',$content);
$tpl->compile('content');
$tpl->clear();
?>