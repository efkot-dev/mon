<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('setup')) {
    $go->redirect('main');
}
require ENGINE_DIR.'functions/sql_pdo.php';
require ENGINE_DIR.'functions/silent.php';
$result = '';
$content = '';
$metatags = [
	'title'=>'PMon '.$lang['services'].'',
	'description'=>'PMon '.$lang['services'].'',
	'page'=>'service'
];
switch($act){
	case'vlan':	
		$result .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
			<a class="brmhref" href="/?do=service"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['list_vlan'].'</span>
		</div>
		';
		$sql_where = "WHERE device = 'olt'";
		$sql_orderby = "ORDER BY netip ASC";
		$sql = "SELECT * FROM switch {$sql_where} {$sql_orderby}";
		$sql_bdcom = $db->SimpleWhile($sql);
		$content ='<table class="resp-tab" width="100%"><thead><tr><th width="5%">ID</th><th width="15%">'.$lang['name'].'</th><th width="10%">'.$lang['ip'].'</th><th>Vlan</th></tr></thead><tbody>';
		if(isset($sql_bdcom) && count($sql_bdcom)>0){
			foreach($sql_bdcom as $idonu => $bdcom){
				$content .= '<tr><td><font color="#1f7bc3">#'.$bdcom['id'].'</font></td>	<td class="name_pon text_left"><font color="#222">'.$bdcom['inf'].' '.$bdcom['model'].'</font><br><a href="/?do=detail&act=olt&id='.$bdcom['id'].'">'.$bdcom['place'].'</a> </td><td><font color="#0eb3f5">'.$bdcom['netip'].'</font></td><td class="text_left"><div id="olt-'.$bdcom['id'].'" class="class-list"></div></td></tr>';
			}
		}else{
			
		}
		$content .='</tbody></table><script>
		getvlanolt();
		</script>';		
	break;
	case 'manyport':
		$result .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
			<a class="brmhref" href="/?do=service"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Multi-port ONT</span>
		</div>
		';	
		$stmt = $pdo->prepare("SELECT value FROM config WHERE name = 'support_port_onu'");
		$stmt->execute();
		$row = $stmt->fetch();
		$models = $row ? json_decode($row['value'], true) : [];
		$content = '<form id="onuForm"><div id="onuList">';
		foreach ($models as $model) {
			$model_safe = htmlspecialchars($model);
			$content .= "
			<div class='onuItem'>
				<input type='text' name='onu_models[]' value='{$model_safe}' />
				<span class=\"remove-row remove\">❌</span>
			</div>";
		}
		$content .= '</div>
		<div class="lf_1 m10b">
		<button type="button" id="addOnu">'.$lang['addeds'].'</button>
		<button type="submit">'.$lang['save'].'</button>
		</div>
	</form>
	<div id="resultmsg" style="margin-top:10px; color:green;"></div>
	<script>
	$("#addOnu").on("click", function () {
		$("#onuList").append(`
			<div class=\"onuItem\">
				<input type=\"text\" name=\"onu_models[]\" value=\"\" />
				<span class="remove-row remove">❌</span>
			</div>
		`);
	});
	$(document).on("click", ".remove", function () {
		$(this).closest(".onuItem").remove();
	});
	$("#onuForm").on("submit", function(e) {
		e.preventDefault();
		$.ajax({
			method: "POST",
			url: "/?do=service&act=savemanyport",
			data: $(this).serialize(),
			success: function(response) {
				$("#resultmsg").text(response);
			},
			error: function() {
				$("#resultmsg").text("Помилка при збереженні.").css("color", "red");
			}
		});
	});
	</script>';
	break;
	case 'savemanyport':	
		if (isset($_POST['onu_models']) && is_array($_POST['onu_models'])) {
			$models = array_filter(array_map('trim', $_POST['onu_models']));
			$json = json_encode(array_values($models), JSON_UNESCAPED_UNICODE);
			$stmt = $pdo->prepare("SELECT id FROM config WHERE name = 'support_port_onu' LIMIT 1");
			$stmt->execute();
			$existing = $stmt->fetchColumn();
			if ($existing) {
				$stmt = $pdo->prepare("UPDATE config SET value = ?, types = 'onu' WHERE id = ?");
				$stmt->execute([$json, $existing]);
			} else {
				$stmt = $pdo->prepare("INSERT INTO config (name, value, types) VALUES ('support_port_onu', ?, 'onu')");
				$stmt->execute([$json]);
			}
			echo 'Збережено успішно!';
		} else {
			echo 'Немає даних для збереження.';
		}
		exit;
		break;
	case 'getsfp':
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']) : null;
		if (is_valid_id($olt)) {
			$switch = getSwitchById($pdo, $olt);
			$stmt = $pdo->query("SELECT * FROM switch_pon WHERE oltid = '{$switch['id']}'");
			$list_sfp = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($list_sfp) {
				echo '<div id="service_sfp_data_list">';
				foreach ($list_sfp as $pon) {
					echo '<div class="service_sfp_global">';
					$oids = get_data_sfp($switch['oidid'],$pon);
					echo '<div class="service_sfp_name">'.$pon['pon'].'</div>';
					echo '<div class="service_sfp_data">';
					foreach ($oids as $name => $oid) {
						$value = @snmp2_get($switch['netip'], $switch['snmpro'], $oid);
						$sfp_value = csfp($value);
						if ($sfp_value) {
							render_sfp_value($switch['oidid'], $name, $sfp_value);
						}
					}
					echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			}
		}
		die;
		break;
	case'status':	
		$result .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
			<a class="brmhref" href="/?do=service"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Моніторинг параметрів обладнання</span>
		</div>
		';
		$sql_where = " WHERE device = 'olt'";
		$sql_orderby = "ORDER BY netip ASC";
		$sql = "SELECT * FROM switch {$sql_where} {$sql_orderby}";
		$sql_bdcom = $db->SimpleWhile($sql);
		$content ='
		<script>
		$(document).ready(function() {
			$("tr").each(function() {
				var id = $(this).data("id");
				if (id) {
					get_status_sfp(id);
				}
			});
		});
		</script>
		<table class="resp-tab" width="100%"><thead><tr>
			<th width="5%">ID</th>
			<th width="15%">'.$lang['name'].'</th>';
		if(isset($config['viewipswitch']) && $config['viewipswitch']=='on'){
			$content .='<th width="10%">'.$lang['ip'].'</th>';
		}
		$content .='<th>Параметри</th>
			</tr></thead><tbody>';
		if(isset($sql_bdcom) && count($sql_bdcom)>0){
			foreach($sql_bdcom as $idonu => $bdcom){
				$content .= '<tr data-id="' . $bdcom['id'] . '">
				<td><font color="#1f7bc3">#'.$bdcom['id'].'</font></td>	
				<td class="name_pon text_left">
					<font color="#222">'.$bdcom['inf'].' '.$bdcom['model'].'</font>
						<br>
							<a href="/?do=detail&act=olt&id='.$bdcom['id'].'">'.$bdcom['place'].'</a> </td>';
				if(isset($config['viewipswitch']) && $config['viewipswitch']=='on'){
					$content .= '<td><font color="#0eb3f5">'.$bdcom['netip'].'</font></td>';
				}
				$content .= '
				<td class="text_left">
					<div id="status_'.$bdcom['id'].'"></div>
				</td>
				</tr>';
			}
		}else{
			$content .= '<tr><td colspan="6">'.$lang['empty'].'</td></tr>';
		}
		$content .='</tbody></table>';	
	break;
	case'speed':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
		$speed = isset($_POST['speed']) ? Clean::int($_POST['speed']): null;
		$type = isset($_POST['type']) ? Clean::text($_POST['type']): null;	
		$writeall = isset($_POST['writeall']) ? Clean::text($_POST['writeall']): null;
		$sqlonus = $db->Fast('onus','*',['idonu'=>$id]);
		if(isset($sqlonus['keyonu'])){
			$sqlswitch = $db->Fast('switch','place,netip,snmpro,snmprw',['id'=>$sqlonus['olt']]);
		}
		if(isset($id) && $id>0 && isset($speed) && $access->get('edit_pir_sla') && isset($sqlonus['keyonu']) && isset($sqlswitch['snmprw'])){		
			if($type=='upir'){
				echo $type;
				// LLID port configuration row status = off(2)
				snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.9.1.1.11.".$sqlonus['keyonu'], 'i',"2");
				// llidIfPIR - LLID port peak bandwidth.Notes:dba mode=1,2(12144/cycle-size(ms) to MIN(1000000,1000000/cycle-size(ms) ), dba mode =3,4(512 to1000000)
				snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.9.1.1.8.".$sqlonus['keyonu'], 'i',$speed);
				// LLID port configuration row status = off(1)
				snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.9.1.1.11.".$sqlonus['keyonu'], 'i',"1");
			}elseif($type=='dpir'){
				// LLID port down-stream configuration row status.That effects to llidDownStreamPir, llidDownStreamCir, llidDownStreamFir.
				snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.9.1.1.20.".$sqlonus['keyonu'], 'i',"2");
				// LLID port down-stream peak bandwidth. Notes:dba mode=1,2(12144/cycle-size(ms) to MIN(1000000,1000000/cycle-size(ms) ), dba mode =3,4(512 to1000000).
				snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.9.1.1.17.".$sqlonus['keyonu'], 'i',$speed);
				// LLID port down-stream configuration row status.That effects to llidDownStreamPir, llidDownStreamCir, llidDownStreamFir.
				snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.9.1.1.20.".$sqlonus['keyonu'], 'i',"1");
			}
			if(isset($writeall) && $writeall=='on'){
				@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.20.15.1.1.0", 'i', "1");
			}
			$go->go('/?do=onu&id='.$id);
			exit;
		}
	break;
	case'snmpcreatedvlan':		
		if(isset($confPMon['CREATED_VLAN']) && !empty($confPMon['CREATED_VLAN']) && $confPMon['CREATED_VLAN']==1){
			if(isset($olt)){
				$getolt = $db->Fast('switch','place,netip,snmpro,snmprw',['id'=>$olt]);
			}
			if(!empty($getolt['snmprw']) && $access->get('vlan_edit')){
				// log_register(__("Created vlan") . " " . $vlan . " " . __("on switch") . " " . $ip);
			}
		}	
		die;
	break;
	case'createdvlan':		
		if(isset($confPMon['CREATED_VLAN']) && !empty($confPMon['CREATED_VLAN']) && $confPMon['CREATED_VLAN']==1){
			$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
				<a class="brmhref" href="/?do=service"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Created vlan</span>
			</div>
			';
			$content .= '<form action="/" method="post" id="formadd"><input type="hidden" name="do" value="service"><input type="hidden" name="act" value="snmpcreatedvlan">';	
		$content .= '<div class="card">';		
		$content .= formpage(['img'=>'addconnect.png','name'=>'VLAN','descr'=>'Created vlan bdcom']);		
		$content .= formpage(['name'=>'<input style="width: 200px;" name="name" class="input1" type="number" min="1" max="4096" step="1" placeholder="Введіть число від 1 до 4096">']);	
		$switch_olt = $db->SimpleWhile("SELECT id, place FROM switch WHERE oidid = '1'");
		if(is_array($switch_olt)){
			foreach($switch_olt as $switch){
				$listolt .= '<option value="'.$switch['id'].'">'.$switch['place'].'</option>';
			}
			$content .= formpage(['img'=>'folders.png','name'=>'Pon','descr'=>'OLT']);
			$content .= formpage(['name'=>'<select class="select" name="olt" id="olt"><option value="0"></option>'.$listolt.'</select>']);
		}			
		$content .= '</div>';		
		$content .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button>
		</form>';
		}		
	break;
	case 'history':
		$idonu = isset($_GET['idonu']) ? (int)$_GET['idonu'] : 0;
		if ($idonu <= 0){
			$go->go('/?do=main');
		}
		$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
		$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 15;
		$stmtTotal = $pdo->prepare("SELECT COUNT(*) AS cnt FROM historysignal WHERE onu = :idonu");
		$stmtTotal->execute(['idonu' => $idonu]);
		$total = (int)$stmtTotal->fetchColumn();
		$stmt = $pdo->prepare("SELECT * FROM historysignal WHERE onu = :idonu ORDER BY datetime DESC  LIMIT :limit OFFSET :offset");
		$stmt->bindValue(':idonu', $idonu, PDO::PARAM_INT);
		$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
		$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
		$stmt->execute();
		$rows = $stmt->fetchAll();
		$data_rx = [];
		if ($rows) {
			$minDate = $rows[count($rows)-1]['datetime'];
			$maxDate = $rows[0]['datetime'];
			$stmtRx = $pdo->prepare("SELECT * FROM rxolt_signal WHERE onu = :idonu AND datetime BETWEEN :min AND :max");
			$stmtRx->execute([':idonu' => $idonu,':min' => $minDate,':max' => $maxDate]);
			$rxRows = $stmtRx->fetchAll();
			foreach ($rxRows as $rx) {
				$key = date('Y-m-d H', strtotime($rx['datetime']));
				$data_rx[$key] = round((float)$rx['signal'], 2);
			}
		}
		$data = [];
		$lastRx = null;
		foreach ($rows as $row) {
			$key = date('Y-m-d H', strtotime($row['datetime']));
			if (isset($data_rx[$key])) {
				$lastRx = $data_rx[$key];
			}
			$data[] = [
				'time' => date('Y-m-d H:i', strtotime($row['datetime'])),'onu' => signalTerminal($row['signal']),'olt' => signalTerminal($lastRx)
			];
		}
		header('Content-Type: application/json');
		echo json_encode(['data'  => $data,'total' => $total]);
		exit;
		break;
	case'addspeed':	
		if($access->get('add_profile_speed')){
		$result .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
			<a class="brmhref" href="/?do=service"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</a>
			<a class="brmhref" href="/?do=service&act=managerspeed"><i class="fi fi-rr-angle-left"></i>'.$lang['speedpirtmp'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['new_pir'].'</span>
		</div>
		';
		$listolt = '';			
		$content .= '<form action="/" method="post" id="formadd"><input type="hidden" name="do" value="service"><input type="hidden" name="act" value="savespeed">';	
		$content .= '<div class="card">';		
		$content .= formpage(['img'=>'addconnect.png','name'=>'Назва шаблону','descr'=>'Використовується для обліку в системі']);		
		$content .= formpage(['name'=>'<input types name="name" class="input1" type="text">']);	
		$switch_olt = $db->SimpleWhile("SELECT id, place FROM switch WHERE oidid = '1'");
		if(is_array($switch_olt)){
			foreach($switch_olt as $switch){
				$listolt .= '<option value="'.$switch['id'].'">'.$switch['place'].'</option>';
			}
			$content .= formpage(['img'=>'folders.png','name'=>'Pon','descr'=>'Оптичний концетратор']);
			$content .= formpage(['name'=>'<select class="select" name="olt" id="olt"><option value="0"></option>'.$listolt.'</select>']);
		}			
		$content .= '
		<div class="speed_temp">
			<div class="block_temp bord1">
				<div class="name_temp">
					<span class="down">Downstream</span>
				</div>
				<div class="pole_m">
					<b>Pir — пікова швидкість передачі/прийому в Kbit</b>
					<div class="pole_s"><input types name="d_pir" class="input1" type="text"></div>
				</div>
				<div class="pole_m">
					<b>Cir — гарантована швидкість передачі/прийому в Kbit</b>
					<div class="pole_s"><input types name="d_cir" class="input1" type="text"></div>
				</div>	
			</div>			
			<div class="block_temp bord2">
				<div class="name_temp">
					<span class="ups">Upstream</span>
				</div>
				<div class="pole_m">
					<b>Pir — пікова швидкість передачі/прийому в Kbit</b>
					<div class="pole_s"><input types name="u_pir" class="input1" type="text"></div>
				</div>
				<div class="pole_m">
					<b>Cir — гарантована швидкість передачі/прийому в Kbit</b>
					<div class="pole_s"><input types name="u_cir" class="input1" type="text"></div>
				</div>	
			</div>
		</div>		
		';
		$content .= '</div>';		
		$content .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button>
		</form>';
		}else{
			
		}
	break;
	case'savespeed':
		if($access->get('add_profile_speed')){
			$tempfolder = ROOT_DIR.'/export/temp/';
			if(isset($_POST['olt'])){
				$olt = Clean::int($_POST['olt']);
			}		
			if(isset($_POST['d_pir'])){
				$d_pir = Clean::int($_POST['d_pir']);
			}		
			if(isset($_POST['d_cir'])){
				$d_cir = Clean::int($_POST['d_cir']);
			}		
			if(isset($_POST['u_pir'])){
				$u_pir = Clean::int($_POST['u_pir']);
			}		
			if(isset($_POST['u_cir'])){
				$u_cir = Clean::int($_POST['u_cir']);
			}		
			if(isset($_POST['name'])){
				$name = Clean::text($_POST['name']);
			}
			if (isset($name) && isset($u_cir) && isset($u_pir) && isset($d_cir) && isset($d_pir) && isset($olt)) {
				$sqlinsert = [
					'olt'=>$olt,
					'u_cir'=>$u_cir,
					'u_pir'=>$u_pir,
					'd_cir'=>$d_cir,
					'd_pir'=>$d_pir,
					'name'=>$name,
					'added'=>$time
				];
				$db->SQLinsert('bdcom_epon_onu_pir',$sqlinsert);	
			}
		}
		$go->go('/?do=service');
		die;
	break;
	case'delspeed':
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;	
		if($access->get('edit_profile_speed') && isset($id) && $id>0 && isset($confPMon['BDCOM_EPON_PIR']) && !empty($confPMon['BDCOM_EPON_PIR']) && $confPMon['BDCOM_EPON_PIR']==1){
			$db->SQLdelete('bdcom_epon_onu_pir',['id' => $id]);
			$go->go('/?do=service&act=managerspeed');
			exit;
		}
		$go->go('/?do=service&act=managerspeed');
		exit;
	break;
	case'managerspeed':	
		if($access->get('add_profile_speed')){
			$arra_list_olt = array();
			$list_olt_pmon = $db->SimpleWhile("SELECT id, place, inf, model FROM switch WHERE oidid = '1'");
			if(isset($list_olt_pmon) && count($list_olt_pmon)>0){
				foreach($list_olt_pmon as $olt => $data_olt){
					$arra_list_olt[$data_olt['id']] = $data_olt;
				}
			}
			$result .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
				<a class="brmhref" href="/?do=service"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['new_pir'].'</span>
			</div>
			';
			$content ='<div class="mb20"><a href="/?do=service&act=addspeed" class="addedr">'.$lang['addeds'].'</a></div><table class="resp-tab" width="100%"><thead><tr><th>'.$lang['name'].'</th><th width="15%">'.$lang['olt'].'</th><th width="10%">D/Pir</th><th width="10%">D/Cir</th><th width="10%">U/Pir</th><th width="10%">U/Cir</th>
			<th width="10%">'.$lang['added'].'</th>
			'.($access->get('edit_profile_speed')?'<th width="10%">'.$lang['btn_menu_conf'].'</th>':'').'
			</tr></thead><tbody>';	
			$profile_speed = $db->SimpleWhile("SELECT * FROM bdcom_epon_onu_pir");
			if(isset($profile_speed) && count($profile_speed)>0){
				foreach($profile_speed as $idonu => $bdcom){
				$content .= '<tr>
					<td>'.$bdcom['name'].'</td>
					<td class="td_url">
					'.(isset($arra_list_olt[$bdcom['olt']]['place'])?
					'<a href="/?do=detail&act=olt&id='.$bdcom['olt'].'">'.$arra_list_olt[$bdcom['olt']]['place'].'</a>' :
					$lang['all']).'
					</td>
					<td class="u_pir">'.$bdcom['u_pir'].' Kbit</td>
					<td class="u_cir">'.$bdcom['u_cir'].' Kbit</td>
					<td class="d_pir">'.$bdcom['d_pir'].' Kbit</td>
					<td class="d_cir">'.$bdcom['d_cir'].' Kbit</td>
					<td>'.$bdcom['added'].'</td>';
					if($access->get('edit_profile_speed')){
						$content .= '<td>';						
						$content .= '<a class="panel_house rr_1" href="/?do=service&act=delspeed&id='.$bdcom['id'].'">Видалити</a>';
						$content .= '</td>';
					}
					$content .= '</tr>';
				}
			}else{
				$content .= '<tr><td rowspan="8">'.$lang['empty'].'</td>
				</tr>';
			}
			$content .='</tbody></table>';
		}
	break;
	case'viewvlan':	
		if(isset($confPMon['VIEW_OLT_VLAN']) && !empty($confPMon['VIEW_OLT_VLAN']) && $confPMon['VIEW_OLT_VLAN']==1){
			if(isset($_POST['id'])){
				$id = Clean::int($_POST['id']);
			}
			if(isset($id) && $id > 0){
				$templatvlan = 'get_list_olt_vlan_' . $id;
				$sql = "SELECT data FROM tempdate WHERE file = :file";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([':file' => $templatvlan]);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!empty($result['data'])){
					$decoded_data = json_decode($result['data'], true);
				}
				if (!empty($decoded_data)) {
					foreach ($decoded_data as $vlan => $vl) {
						$vlan = intval($vlan);
						if (is_array($vl)) {
							foreach ($vl as $item) {
								echo '<span class="l-vlan" data-id="v-' . $vlan . '-' . $item . '">' . htmlspecialchars($item) . '</span>';
							}
						} else {
							echo '<span class="l-vlan" data-id="v-' . $vlan . '">' . htmlspecialchars($vl) . '</span>';
						}
					}
				} else {
					echo "";
				}
			}
		}
		die;
	break;	
	case'transport_add':		
		
	break;	
	case'getvlan':	
		if(isset($confPMon['VIEW_OLT_VLAN']) && !empty($confPMon['VIEW_OLT_VLAN']) && $confPMon['VIEW_OLT_VLAN']==1){	
			
		}
	break;
	default:
		$result .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</span>
		</div>
		';
		$content .= '
			<div class="admin-1">
				<div class="main-panel">
					<div class="admin-zvit">';
		if($access->get('add_profile_speed') && isset($confPMon['BDCOM_EPON_PIR']) && !empty($confPMon['BDCOM_EPON_PIR']) && $confPMon['BDCOM_EPON_PIR']==1){				
			$content .= '<a href="/?do=service&act=managerspeed"><img src="../style/img/bdcom_speed.png"><span>'.$lang['speedpir'].'</span></a>';
		}
		if(isset($confPMon['VIEW_OLT_VLAN']) && !empty($confPMon['VIEW_OLT_VLAN']) && $confPMon['VIEW_OLT_VLAN']==1){
			$content .= '<a href="/?do=service&act=vlan"><img src="../style/img/bdcom_vlan.png"><span>'.$lang['list_vlan'].'</span></a>';
		}	
		if(isset($confPMon['VIEW_ONU_VENDOR']) && !empty($confPMon['VIEW_ONU_VENDOR']) && $confPMon['VIEW_ONU_VENDOR']==1){
			$content .='<a href="/?do=vendor"><img src="../style/img/onu_vendor.png"><span>ONU Vendor ID</span></a>';
		}	
		if (isset($confPMon['IPMAN']) && !empty($confPMon['IPMAN']) && $confPMon['IPMAN'] == 1 && $access->get('setup')) {
			$content .= '<a href="/?do=ipman">
				<img src="../style/img/ipman.png">
					<span>'.$lang['modipman'].'</span></a>';
		}		
		if($access->get('vlan_edit')){
			$content .= '<a href="/?do=service&act=createdvlan">
				<img src="../style/img/bdcom_vlan.png">
					<span>Created Vlan BDCOM Epon</span></a>';
		}
		if ($access->get('manager_vlan') && isset($confPMon['MANAGER_VLAN']) && !empty($confPMon['MANAGER_VLAN']) && $confPMon['MANAGER_VLAN'] == 1) {
			$content .= m_url('vlan','?do=vlan','bdcom_vlan.png','Manager Vlan');
		}		
		$content .= m_url('badont','?do=badont','badont.png','Damaged ONU');
		$content .= '<a href="/?do=service&act=status"><img src="../style/img/monitor_status.png"><span>SFP Temp</span></a>';
		
		$content .= '<a href="/?do=onuerror"><img src="../style/img/pagerroronu.png"><span>'.$lang['onuerror'].'</span></a>';	
		$content .= '<a href="/?do=service&act=manyport"><img src="../style/img/many_port_ony.png"><span>Multi-port ONT</span></a>';	
		if (isset($confPMon['TEMPERATURE_MONITOR']) && !empty($confPMon['TEMPERATURE_MONITOR']) && $confPMon['TEMPERATURE_MONITOR']==1) {
			$content .='<a href="/?do=temp"><img src="../style/img/monitor_temp.png"><span>Менеджер</span></a>';
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
?>