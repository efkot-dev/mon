<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$clock = date('Y-m-d H:i:s');
$template = '';
$speedbar = '';
$listlocation = '';
switch($act){			
	case 'add':
		$speedbar .= '
			<div id="onu-speedbar">
				<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>'.$lang['taskman_main_task'].'</a>
				<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['taskman_new'].'</span>
			</div>';
		$location = getListLocations();
		foreach($location as $loc){
			$listlocation .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
		}
		$template .= '<div class="w60_">
			<form action="/?do=billing" method="post">
				<input name="act" type="hidden" value="saveusr">
					<div class="main_taskman">'.
		formpage([
			'img'=>'m6.png','name'=>$lang['location'],'descr'=>$lang['getlocation'],'class'=>'main_w100',
			'pole'=>'<select class="select" name="location" id="location" required><option value="0"></option>'.$listlocation.'</select>']).
		formpage([
			'class'=>'main_w100','img'=>'m6.png','name'=>'Вулиця','descr'=>"Вулиця клієнта",
			'pole'=>'<input name="client_street" class="input1" type="text" style="width:50%;">']).
		formpage([
			'class'=>'main_w100','img'=>'m6.png','name'=>'Номер будинку',
			'descr'=>"Номер будинку: 24а кв.1, 23/1, 23",
			'pole'=>'<input name="client_num_house" class="input1" type="text" style="width:50%;">']).
		formpage([
			'class'=>'main_w100','img'=>'usedsystem.png','name'=>'ONU',
			'descr'=>"Прикріплення ONU до клієнта",
			'pole'=>'<input id="onu" name="onu" class="input1" type="text" style="width:50%;">
			<div id="searchonu"></div><input type="hidden" id="idonu" name="idonu">']).
		formpage([
			'class'=>'main_w100','img'=>'m8.png','name'=>''.$lang['taskman_us_pib'].'.',
			'descr'=>"Інформація про клієнта ",
			'pole'=>'<input name="client_pib" class="input1" type="text">']).
		formpage([
			'class'=>'main_w100','img'=>'addconnect.png','name'=>'Контакті номери',
			'descr'=>"Номери клієнта: 0993115500, 0500502222",
			'pole'=>'<input name="client_mobil" class="input1 " type="text">']);
		$template .='<input type="submit" value="'.$lang['save'].'"></div></form></div>';
		$template .="
		<script>
		$(document).ready(function() {
			$('#onu').on('input', function() {
				var searchText = $(this).val();
				$.post(root,{do:'billing',act:'searchonu',onu:searchText},function(response) {
					$('#searchonu').html(response);
				});
			});
		});	
		$(document).on('click', '.select-onu', function(e) {
			$('.jsadded').show();
			e.preventDefault();
			var id = $(this).data('id');
			var onuField = $('#onu');
			onuField.val(id);
			var idonu = $(this).data('idonu');
			var onuFieldidonu = $('#idonu');
			onuFieldidonu.val(idonu);
			$('#searchonu').empty();
		});		
		</script>";
		break;
	case 'usrtable':
		if($access->get('addmaponu')){
			$edit = true;
		}else{
			$edit = false;
		}	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
		$offset = isset($_POST['offset']) ? Clean::int($_POST['offset']): null;
		$query = (isset($_POST["query"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["query"]))))):null);
		$orderby ="ORDER BY billing_usr.added ASC";
		if(isset($id) && $id>0){
			$where_olt = "billing_usr.deviceid = '{$id}'";
		}else{
			$where_olt = "billing_usr.onuid IS NOT NULL";
		}
		$search = "";
		if(isset($query) && strlen($query) > 0) {
			$search = "AND billing_usr.onumac LIKE '%$query%' 
				OR billing_usr.pib LIKE '%$query%' 
					OR billing_usr.city LIKE '%$query%' 
						OR billing_usr.street LIKE '%$query%'";
		}
		$records_per_page = 30; 
		$limit = "";
		$zaput = "SELECT billing_usr.* , billing_usr.id as bill_uid, billing_usr.uid as usr_uid, billing_usr.deviceid as olt_id, onus.*, onus.status as onu_status, onus.reason as onu_reason, onus.added as onu_added, onus.mac as onu_mac FROM billing_usr 
		LEFT JOIN onus ON (onus.idonu = billing_usr.onuid) WHERE $where_olt $search $orderby $limit";
		$sqlonus = $db->SimpleWhile($zaput);
		$olt = '';
		foreach ($sqlonus as $ont) {
			$olt .= $ont['olt'].',';
		}
		$where_olt = pmon_implode(',', $olt);
		$sql_switch = [
			'sql' => "SELECT id, place, inf, model FROM switch WHERE id IN ($where_olt)",
			'type' => 'while',
			'unique' => 'id',
			'key' => 'billing_switch_id_'.md5($where_olt),
			'time' => 3600
		];
		$data_switch = cache_simple_sql($sql_switch);
		$total_records = count($sqlonus);
		echo'<table class="resp-tab list-onu-olt"><thead><tr>
			<th class="mob_w10" width="4%">'.$lang['status'].'</th>
			<th>П.І.Б</th>
			<th width="20%">Адреса</th>
			<th width="15%">ONU</th>
			<th width="6%"><span class="inf_signal"><span class="sig2">Rx ONU</span></span></th>
			<th width="10%">Device</th>
			<th width="10%">Model</th>
			'.($edit  ? '<th width="5%">Action</th>' : '').'
			</tr>
			</thead><tbody>';
		if($total_records>0){
			$total_pages = ceil($total_records / $records_per_page);
			$start = $offset * $records_per_page;
			$end = min(($offset + 1) * $records_per_page, $total_records);
			for ($i = $start; $i < $end; $i++) {
				$status = statusTermianl($sqlonus[$i]['onu_status']);
				$added = checkWhenAdded($sqlonus[$i]['onu_added']);
				if($sqlonus[$i]['onu_status']==1){
					$get_status = $status['img'];
				}else{
					$get_status = reason_onu($sqlonus[$i]['onu_status'],$sqlonus[$i]['onu_reason']);
				}
				echo'<tr class="'.$status['css'].''.$added.'">';
				echo'<td class="status">'.$get_status.'</td>';
				echo'<td class="td_names">'.$sqlonus[$i]['pib'].'</td>';
				echo'<td class="description_name">';		
				$pmon_billing = PmonBillingData($sqlonus[$i]);
				echo PmonBillingTemplate($pmon_billing);		
				echo'</td>';
				echo'<td class="td_url"><a href="/?do=onu&id='.$sqlonus[$i]['onuid'].'">'.$sqlonus[$i]['onumac'].'</a></td>';
				echo'<td>';		
				echo signalTerminal($sqlonus[$i]['rx']).($sqlonus[$i]['rxstatus']=='up' || $sqlonus[$i]['rxstatus']=='down' ? '<span class="signaldown"><i class="fi fi-rr-angle-small-'.$sqlonus[$i]['rxstatus'].'"></i></span>':'');
				echo'</td>';		
				echo'<td><a href="/?do=detail&act=olt&id='.$sqlonus[$i]['deviceid'].'">'.$data_switch[$sqlonus[$i]['deviceid']]['place'].'</a></td>';
				echo'<td><a href="/?do=detail&act=olt&id='.$sqlonus[$i]['deviceid'].'">'.$data_switch[$sqlonus[$i]['deviceid']]['inf'].' '.$data_switch[$sqlonus[$i]['deviceid']]['model'].'</a></td>';
				if($edit){
					echo'<td><a class="panel_house rr_1" href="/?do=billing&act=delete&id='.$sqlonus[$i]['bill_uid'].'">Видалити</a></td>';
				}
				echo'</tr>';
			}
			if ($total_records > $records_per_page) {
				echo '</tbody></table>';
				renderPagination($offset + 1, $total_pages);
			}
		} else {
			echo'<tr><td colspan="7">'.$lang['emlist'].'</td></tr>';
			echo'</tbody></table>';
		}
		exit;
		break;
	case 'delete':			
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
		if(isset($id) && $id>0){
			$db->SQLdelete('billing_usr',['id' => $id]);
			$go->go('/?do=billing');	
		}
		exit;
		break;
	case 'searchonu':	
		if (isset($_POST['onu'])) {
			$zapros = (isset($_POST["onu"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["onu"]))))):null);
			if(empty($zapros)){
				exit;
			}
			$cleanZapros = str_replace('.', '', $zapros);
			if (preg_match('/^[a-fA-F0-9]{12}$/', $cleanZapros)) {
				$zapros = implode(':', str_split($cleanZapros, 2));
			}
			$orderby = " ORDER BY idonu ASC";
			$limit = " LIMIT 15";
			$where_onusdata = "(onusdata.tag LIKE '%".$zapros."%' OR onusdata.name LIKE '%".$zapros."%' OR onusdata.uid LIKE '%".$zapros."%')";
			$where_onus = "(onus.sn LIKE '%".$zapros."%' OR onus.name LIKE '%".$zapros."%' OR onus.mac LIKE '%".$zapros."%')";
			$sqlonus = $db->SimpleWhile("SELECT onusdata.*, onus.* FROM onusdata
				LEFT JOIN onus ON (onus.mac = onusdata.onukey OR onus.sn = onusdata.onukey)
				WHERE $where_onusdata $orderby $limit");
			if (empty($sqlonus)) {
				$sqlonus = $db->SimpleWhile("SELECT * FROM onus WHERE $where_onus $orderby $limit");
			} else {
				$sqlonus_from_onus = $db->SimpleWhile("SELECT * FROM onus WHERE (onus.name LIKE '%".$zapros."%' OR onus.mac LIKE '%".$zapros."%' OR onus.sn LIKE '%".$zapros."%') $orderby $limit");
				$sqlonus = array_merge($sqlonus, $sqlonus_from_onus);
			}
			if(isset($sqlonus) && count($sqlonus)>0){
				echo '<ul class="resultpoiskonu">';
				foreach ($sqlonus as $ont) {
					$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
					if($onukey!=false){
						echo '<li><span class="status-'.$ont['status'].'"></span>
						<a href="#" '.($ont['status']==2 ? 'style="color:#a0a0a0;"' : '').' class="select-onu" data-id="' . $onukey . '" data-idonu="' . $ont['idonu'] . '">';
						echo'<span '.($ont['status']==2 ? 'style="background:#9d9d9d;"' : '').'>'.$ont['inface'].'</span> ' . $onukey . '';
						echo'</a></li>';
					}
				}
				echo '</ul>';
			}else{
				echo'Давай по іншому';
			}
		}	
		exit;
		break;			
	case 'saveusr':	
		$uid = sqlesc(isset($_POST['uid']) ? Clean::text($_POST['uid']) : 0);
		$client_pib = sqlesc(isset($_POST['client_pib']) ? Clean::text($_POST['client_pib']) : null);
		$mobile = sqlesc(isset($_POST['client_mobil']) ? Clean::text($_POST['client_mobil']) : null);
		$client_street = sqlesc(isset($_POST['client_street']) ? Clean::text($_POST['client_street']) : null);
		$client_num_house = sqlesc(isset($_POST['client_num_house']) ? Clean::text($_POST['client_num_house']) : null);
		$locationid = sqlesc(isset($_POST['location']) ? Clean::int($_POST['location']) : null);
		$idonu = sqlesc(isset($_POST['idonu']) ? Clean::int($_POST['idonu']) : null);
		$onumac = sqlesc(isset($_POST['onu']) ? Clean::text($_POST['onu']) : null);
		$location = $db->Fast('location','*',['id'=>$locationid]);
		$olt = $db->Fast('onus','olt,inface',['idonu'=>$idonu]);
		$city_name = sqlesc($location['name']);
		$deviceid = sqlesc($olt['olt']);
		$added = sqlesc($clock);
		$sql ="INSERT INTO billing_usr (uid,pib,mobile,city,street,nomer,locationid,onuid,onumac,deviceid,device_type,added) VALUE ({$uid},{$client_pib},{$mobile},{$city_name},{$client_street},{$client_num_house},{$locationid},{$idonu},{$onumac},{$deviceid},'olt',{$added})";
		if(isset($idonu) && $idonu>0 
			&& isset($locationid) && $locationid>0
			&& isset($deviceid) && $deviceid>0
			&& isset($onumac) && !empty($onumac)
			&& isset($onumac) && !empty($onumac)
			&& isset($client_pib) && !empty($client_pib)		
		){
			$db->query($sql);
		}
		$go->go('/?do=billing');
		break;
	default:
		$select_count = $db->Simple("SELECT COUNT(id) AS count_id FROM billing_usr");	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
		$metatags = array('title'=>'Billing','description'=>'Billing','page'=>'biiling');
		$count_usr = $select_count['count_id'];		
		$template .="
		<script>
		var id = '{$id}';
		var searchText = '';
		var offset = 0;
		function loadPage(page) {
			offset = page - 1;
		   sendRequest();
		}
		function sendRequest() {
			$.post(root,{do: 'billing',act: 'usrtable',id: id,query: searchText,offset: offset},function(response) {
				$('#result').html(response);
			});
		}
		$(document).ready(function(){    
			sendRequest();
			$('#search').keyup(function(){
				searchText = $(this).val();
				offset = 0; 
				if (searchText.length >= 2) {
					sendRequest();
				}
			});
			$('#nextPage').click(function(){
				event.preventDefault();
				offset++;
				sendRequest();
			});
			$('#prevPage').click(function(){
				event.preventDefault();
				if (offset > 0) {
					offset--;
					sendRequest();
				}
			});
		});
		</script>
		<div id=\"searcformhmac\"><form class='form_billing'><input type=\"text\" id=\"search\" placeholder=\"Пошук клієнта \" autocomplete=\"off\"></form><a class=added href=\"/?do=billing&act=add\">{$lang['addeds']}</a><div class=\"count_usr\">Всього в базі: <span>{$count_usr}</span></div></div><div id=\"result\"></div>";
}
$tpl->load_template('location/main.tpl');
$tpl->set('{result}',$template);
$tpl->set('{speedbar}',$speedbar);
$tpl->compile('content');
$tpl->clear();
?>
