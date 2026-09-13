<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
require_once ENGINE_DIR.'functions/taskman.php';
$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;
if($act=='addnote'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		$getLoca = $db->Fast($PMonTables['unit'],'id, note',['id'=>$id]);
		if(!empty($getLoca['id'])){
			okno_title($lang['unitnote']);
			echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="savenoteunit"><input name="id" type="hidden" value="'.$id.'">';
			echo form(['name'=>$lang['note'],'descr'=>'','pole'=>'<textarea class="textarea1" rows="7" name="note">'.($getLoca['note']?$getLoca['note']:'').'</textarea>']);
			echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
			okno_end();	
		}
	}	
}elseif($act=='apk' && $access->get('setup')){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	$getuser = $db->Fast('users','id, android',['id'=>$id]);
	if($id>1 && isset($getuser)){
		okno_title($lang['edit_users']);
		echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$getuser['id'].'"><input name="act" type="hidden" value="updateuserapk">';
			if(isset($getuser['android'])){
				$dataapk = explode(',', $getuser['android']);
			}
			echo form([
				'name' => $lang['btn_menu_search_ont'],
				'descr' => '',
				'pole' => '<input type="checkbox" id="post_searchonu" ' . (isset($getuser['android']) && in_array('searchonu', $dataapk) ? 'checked' : '') . ' name="post_searchonu">'
			]);			
			echo form([
				'name' => $lang['new_onu'],
				'descr' => $lang['new_ont_info'],
				'pole' => '<input type="checkbox" id="post_newonu" ' . (isset($getuser['android']) && in_array('newonu', $dataapk) ? 'checked' : '') . ' name="post_newonu">'
			]);
			echo form([
				'name' => $lang['vols_ponmap'],
				'descr' => '',
				'pole' => '<input type="checkbox" id="post_ponmap" ' . (isset($getuser['android']) && in_array('ponmap', $dataapk) ? 'checked' : '') . ' name="post_ponmap">'
			]);
			echo form([
				'name' => $lang['oblenergo'],
				'descr' => '',
				'pole' => '<input type="checkbox" id="post_oblenergo" ' . (isset($getuser['android']) && in_array('oblenergo', $dataapk) ? 'checked' : '') . ' name="post_oblenergo">'
			]);
			echo form([
				'name' => $lang['setups'],
				'descr' => '',
				'pole' => '<input type="checkbox" id="post_admin" ' . (isset($getuser['android']) && in_array('admin', $dataapk) ? 'checked' : '') . ' name="post_admin">'
			]);
			echo form([
				'name' => 'OLT',
				'descr' => '',
				'pole' => '<input type="checkbox" id="post_olt" ' . (isset($getuser['android']) && in_array('olt', $dataapk) ? 'checked' : '') . ' name="post_olt">'
			]);
			if (isset($confPMon['CAR']) && !empty($confPMon['CAR']) && $confPMon['CAR'] == 1){
				echo form([
					'name' => 'Odometer',
					'descr' => '',
					'pole' => '<input type="checkbox" id="post_odometr" ' . (isset($getuser['android']) && in_array('odometr', $dataapk) ? 'checked' : '') . ' name="post_odometr">'
				]);
			}
			if (isset($confPMon['PING3']) && !empty($confPMon['PING3'])) {
				echo form([
					'name' => 'PING3',
					'descr' => '',
					'pole' => '<input type="checkbox" id="post_ping3" ' . (isset($getuser['android']) && in_array('ping3', $dataapk) ? 'checked' : '') . ' name="post_ping3">'
				]);
			}			
			if (isset($confPMon['SECURITY_PING3']) && !empty($confPMon['SECURITY_PING3'])) {
				echo form([
					'name' => 'ALARM PING3',
					'descr' => '',
					'pole' => '<input type="checkbox" id="post_alarmping3" ' . (isset($getuser['android']) && in_array('alarmping3', $dataapk) ? 'checked' : '') . ' name="post_alarmping3">'
				]);
			}
		echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
		okno_end();	
	}
}elseif($act=='beps'){
	$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']): null;	
	if(isset($idonu) && $idonu>0){
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		if(isset($sqlonus['olt'])){
			$select_ = '';
			$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
			if(isset($sqlswitch['id'])){
				$get = false;
				$profile_speed = $db->SimpleWhile("SELECT id, name FROM bdcom_epon_onu_pir WHERE olt = '{$sqlswitch['id']}'");
				if(isset($profile_speed) && count($profile_speed)>0){
					foreach($profile_speed as $idonus => $profile){
						$select_ .="<option value=\"{$profile['id']}\">{$profile['name']}</option>";
					}
					$get = true;
				}else{
					$profile_speed = $db->SimpleWhile("SELECT id, name FROM bdcom_epon_onu_pir WHERE olt = '0'");
					if(isset($profile_speed) && count($profile_speed)>0){
						foreach($profile_speed as $idonus => $profile){
							$select_ .="<option value=\"{$profile['id']}\">{$profile['name']}</option>";
						}
						$get = true;						
					}
				}
				if($get==true){
					echo'<div class="onu-olt efect1 m10b mobile"><div class="ont-block"><div class="ont-content">
					<form action="" method="post" id="formadd" class="w100 flav">
					<input id="olt" name="id" type="hidden" value="'.$sqlswitch['id'].'">';
					$types  = '<select class="select" id="speedprofile" name="speedprofile"><option value="0"></option>'.$select_.'</select>';
					echo formpage([
						'img'=>'img2.png',
						'name'=>$lang['speed_profiles'],
						'descr'=>$lang['speed_profiles_descr'],
						'pole'=>$types]
					);
					echo'<span class="connect_fiber_btn hide_btn" onclick="runprofile('.$idonu.');">'.$lang['connect'].'</span></form></div></div></div>';?><script>$('#speedprofile').on('change', function() {$(".hide_btn").show();});</script><?php
				}
			}
		}
	}
}elseif($act=='addunit'){
	okno_title($lang['newobj']);
	echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveunit">';
	$location = getListLocations();
	if(isset($location) && count($location)>0){
		foreach($location as $loc){
			$listlocation .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
		}
	}			
	echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text" value="'.$getpmon['name'].'">']);
	echo form(['name'=>$lang['location'],'descr'=>$lang['getlocation'],'pole'=>'<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select>']);
	echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['addeds'].'</button></div>';
	okno_end();		
}elseif($act=='editpmonini'){
	if(!empty($USER['class']) && $USER['class']>=3){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getpmon = $db->Fast('pmonini','id, note, name, value',['id'=>$id]);
			if(!empty($getpmon['id'])){
				okno_title('PMon');
				echo'<form action="/?do=pmon" method="post" id="formadd"><input name="id" type="hidden" value="'.$getpmon['id'].'"><input name="act" type="hidden" value="update">';
				echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text" value="'.$getpmon['name'].'">']);	
				echo form(['name'=>'Value','descr'=>'','pole'=>'<input required name="value" class="input1" type="text" value="'.$getpmon['value'].'">']);	
				echo form(['name'=>$lang['commentar'],'descr'=>'','pole'=>'<textarea class="textarea1" rows="7" name="note">'.$getpmon['note'].'</textarea>']);
				echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['update'].'</button></div>';
				okno_end();
			}
		}		
	}
}elseif($act=='pmonini'){
	if(!empty($USER['class']) && $USER['class']>=3){
		okno_title('PMon');
		echo'<form action="/?do=pmon" method="post" id="formadd"><input name="act" type="hidden" value="save">';
		echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
		echo form(['name'=>'Value','descr'=>'','pole'=>'<input required name="value" class="input1" type="text">']);	
		echo form(['name'=>$lang['commentar'],'descr'=>'','pole'=>'<textarea class="textarea1" rows="7" name="note"></textarea>']);
		echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
		okno_end();	
	}
}elseif($act=='editpaid'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if(isset($id) && $id>0){
		$taskview = $db->Fast('task_list','*',['id'=>$id]);
		if(!empty($taskview['id'])){
			$getlistworker = getListWorker();
			okno_title($getlistworker[$taskview['typesworker']]['name']);
			$paidworker = '<option value="1" '.($taskview['payment_type']==1?'selected':'').'>'.$lang['type_paid'].'</option><option value="2" '.($taskview['payment_type']==2?'selected':'').'>'.$lang['type_free_charge'].'</option><option value="3" '.($taskview['payment_type']==3?'selected':'').'>'.$lang['type_service'].'</option>';
			$pole = '<div id="moneyField" '.($taskview['payment_type']==1?'':'class="hidden"').'><label for="money" class="labels">'.$lang['input_suma'].': </label><input type="text" name="money" value="'.$taskview['money'].'"></div><div id="dogNumberField" '.($taskview['payment_type']==3?'':'class="hidden"').'><label class="labels" for="dog_number">'.$lang['input_dogovir'].': </label><input type="text" name="dog_number" value="'.$taskview['dogovir'].'"></div>';
			echo '<form action="/?do=taskman" method="post" id="formadd"><select class="select" name="type_paid" id="type_paid" onchange="showHideFields()" required><option value="0"></option>'.$paidworker.'</select>'.$pole;
			echo'<input name="act" type="hidden" value="updatepaidtask"><input name="id" type="hidden" value="'.$id.'"><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div></form>';
			okno_end();
		}
	}
}elseif($act=='taskfinish'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if(isset($id) && $id>0){
		$taskview = $db->Fast('task_list','*',['id'=>$id]);
		if(!empty($taskview['id'])){
			$getlistworker = getListWorker();
			okno_title($getlistworker[$taskview['typesworker']]['name']);
			echo'<form action="/?do=taskman" method="post" id="formadd"><input name="act" type="hidden" value="finishedtask"><input name="id" type="hidden" value="'.$id.'">';
			echo PaidType($taskview);
			echo '<span class="iaccess"><span>'.$lang['i_confirm'].'</span> <input class="checkcss" name="vpered" type="checkbox"></span>';
			echo '<div class="note_task">'.$taskview['story'].'</div><br>';
			echo form(['name'=>$lang['commentar'],'descr'=>'','pole'=>'<textarea class="textarea1" rows="7" name="comment"></textarea>']);
			echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
			okno_end();
		}
	}
}elseif($act=='taskedit'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if(isset($id) && $id>0){
		$taskview = $db->Fast('task_list','*',['id'=>$id]);
		if(!empty($taskview['id'])){
			$getlistworker = getListWorker();
			okno_title($getlistworker[$taskview['typesworker']]['name']);
			echo'<form action="/?do=taskman" method="post" id="formadd"><input name="act" type="hidden" value="updatetask"><input name="taskid" type="hidden" value="'.$id.'">';
			$listworker = getListWorker();
			if(is_array($listworker)){
				foreach($listworker as $worker){
					$listworker .= '<option value="'.$worker['id'].'" '.(isset($taskview['typesworker']) && $taskview['typesworker']==$worker['id'] ? 'selected' : '').'>'.$worker['name'].'</option>';
				}
				echo form(['name'=>$lang['listworker'],'descr'=>$lang['listworkerdescr'],'pole'=>'<select class="select" name="typesworker" id="typesworker"><option value="0"></option>'.$listworker.'</select>']);
			}
			$location = getListLocations();
			if(is_array($location)){
				foreach($location as $loc){
					$listlocation .= '<option value="'.$loc['id'].'" '.(isset($taskview['locationid']) && $taskview['locationid']==$loc['id'] ? 'selected' : '').'>'.$loc['name'].'</option>';
				}
			}			
			echo form(['name'=>'Дата виконання','descr'=>'Запланована дата виконання','pole'=>'<input type="datetime-local" name="deadline" id="deadline" required class="css-input" value="'.$taskview['finishday'].'">']);
			echo form(['name'=>$lang['location'],'descr'=>$lang['getlocation'],'pole'=>'<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select>']);
			$client = $db->Fast('task_list_client','*',['id'=>$taskview['clientid']]);
			echo form(['name'=>'Вулиця','descr'=>'Вулиця клієнта','pole'=>'<input name="client_street" class="input1" type="text" value="'.$client['client_street'].'">']);
			echo form(['name'=>'Номер будинку','descr'=>'Номер будинку: 24а кв.1, 23/1, 23','pole'=>'<input name="client_num_house" class="input1" type="text" value="'.$client['client_house'].'">']);
			echo form(['name'=>'П.І.Б','descr'=>'Інформація про клієнта','pole'=>'<input name="client_pib" class="input1" type="text" value="'.$client['client_pib'].'">']);
			echo form(['name'=>'Контакті номери','descr'=>'Номери клієнта: 0993119999, 0500502222','pole'=>'<input name="client_mobil" class="input1" type="text" value="'.$client['client_mobil'].'">']);
			echo form(['name'=>$lang['commentar'],'descr'=>'','pole'=>'<textarea class="textarea1" rows="7" name="story">'.$taskview['story'].'</textarea>']);
			echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
			okno_end();
		}
	}	
}elseif($act=='taskcomment'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		if(isset($id) && $id>0){
			okno_title($lang['add'].' '.$lang['commentar']);
			echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="savetaskcomment"><input name="id" type="hidden" value="'.$id.'">';
			echo form(['name'=>$lang['commentar'],'descr'=>'','pole'=>'<textarea class="textarea1" rows="7" name="comment"></textarea>']);
			echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
			okno_end();	
		}
	}	
}elseif($act=='maplocation'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
	$getLoca = $db->Fast('location','*',['id'=>$id]);
	if(!empty($getLoca['id'])){
	okno_title($lang['getlocation']);
	echo'<link rel="stylesheet" href="../style/map/leaflet.css" />';
	echo'<script src="../style/map/leaflet.js"></script><script src="../style/map/mymarker.js"></script><form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="savegeolocation"><input name="id" type="hidden" value="'.$id.'">';
	echo'<div id="map" style="height: 500px;"></div>';
	$geo_lan = ($getLoca['lan']?$getLoca['lan']:$config['geo_lan']);
	$geo_lon = ($getLoca['lon']?$getLoca['lon']:$config['geo_lon']);
	if(!empty($getLoca['lon']) && !empty($getLoca['lan']))
		$marker = 'L.marker(['.$getLoca['lan'].','.$getLoca['lon'].'],{icon:maplocation}).addTo(map);';
$script = <<<HTML
<script>
var lat = '{$geo_lan}'; 
var lon = '{$geo_lon}';
var map = L.map('map');
map.setView([lat, lon], 18);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
{$marker}
var popup = L.popup();	
function onMapClick(e) {
var lat = e.latlng.lat.toFixed(6);
var lon = e.latlng.lng.toFixed(6);
popup
.setLatLng(e.latlng)
.setContent('<b>{$lang['add_geo_base']}</b> <br>' +
'<input name="lan" type="hidden" value="' + lat + '"><input name="lon" type="hidden" value="' + lon + '"><span class="koomap"><b>{$lang['geo']}</b>: ' + lat + ' ' + lon + '</span><br>' +
'<button type="submit" class="cssadd">{$lang['save']}</button>')
.openOn(map);
}
map.on('click', onMapClick);
</script>
HTML;
	echo $script.'</form>';
	okno_end();	
	}
	}
}elseif($act=='editlocation'){
	if(!empty($USER['class']) && $USER['class']>=5){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getLoca = $db->Fast('location','*',['id'=>$id]);
			if(!empty($getLoca['id'])){
				okno_title($lang['editlocation'].' '.$getLoca['name']);
				echo'<form action="/?do=send" method="post" id="formadd" enctype="multipart/form-data"><input name="act" type="hidden" value="saveeditlocation"><input name="id" type="hidden" value="'.$getLoca['id'].'">';
				echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text" value="'.$getLoca['name'].'">']);	
				echo form(['name'=>'','descr'=>'','pole'=>'<textarea name="note" class="input1" rows="7" style="height: 100px;">'.$getLoca['note'].'</textarea>']);	
				echo form(['name'=>$lang['photo'],'descr'=>$lang['photo_info'],'pole'=>'<input type="file" id="file" name="file" multiple>']);	
				echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
				okno_end();	
			}
		}
	}
}elseif($act=='edit_taskman_list'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if(isset($id) && $id>0 && !empty($USER['class']) && $USER['class']>=3){
		$task_list_worker = $db->Fast('task_list_worker','*',['id'=>$id]);
		if(!empty($task_list_worker['id'])){
			okno_title($lang['taskman_type_worker']);
			echo'<form action="/?do=taskman" method="post" id="formadd"><input name="act" type="hidden" value="updatelistworker"><input name="id" type="hidden" value="'.$task_list_worker['id'].'">';
			echo form(['name'=>$lang['taskman_type_worker'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text" value="'.$task_list_worker['name'].'">']);	
			echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
			okno_end();	
		}
	}	
}elseif($act=='add_taskman_list'){
	if(!empty($USER['class']) && $USER['class']>=3){
		okno_title($lang['taskman_type_worker']);
		echo'<form action="/?do=taskman" method="post" id="formadd"><input name="act" type="hidden" value="savelistworker"><input name="types" type="hidden" value="save">';
		echo form(['name'=>$lang['taskman_type_worker'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
		echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
		okno_end();	
	}	
}elseif($act=='editgroup'){
	if(!empty($USER['class']) && $USER['class']>=5){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getLoca = $db->Fast($PMonTables['gr'],'*',['id'=>$id]);
			if(!empty($getLoca['id'])){
				okno_title($getLoca['name']);
				echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveeditgroup"><input name="id" type="hidden" value="'.$getLoca['id'].'">';
				echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text" value="'.$getLoca['name'].'">']);	
				echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
				okno_end();	
			}
		}
	}
}elseif($act=='delgroup'){
	if(!empty($USER['class']) && $USER['class']>=5){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getGR = $db->Fast($PMonTables['gr'],'*',['id'=>$id]);
			if(!empty($getGR['id'])){
				okno_title($lang['delet']);
				echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$getGR['id'].'"><input name="act" type="hidden" value="delgroup">';
				echo'<div class="redton">Group delet?</div>';
				echo'</form><div class="polebtn"><button type="submit" form="formadd"  style="background: tomato;" value="submit">'.$lang['delet'].'</button></div>';
				okno_end();	
			}
		}
	}
}elseif($act=='transport_add'){
	#if(isset($confPMon['TRANSPORT_ONU']) && !empty($confPMon['TRANSPORT_ONU']) && $confPMon['TRANSPORT_ONU']==1){	
			if(isset($_POST['id'])){
				$id = Clean::int($_POST['id']);
			}
			if(isset($id) && $id > 0){
				okno_title("Додати ONU");
				echo '
				<form method="post" action="/?do=transport" class="form-inline">
                    <input type="hidden" name="act" value="transport_add_onu">
                    <input type="hidden" name="isp_id" value="'.$id.'">
                    <div class="form-row">
                        <label>ID ONU</label>
                        <input type="number" name="idonu" min="1" placeholder="наприклад 123">
                    </div>
                    <div class="form-row">
                        <label>MAC</label>
                        <input type="text" name="mac" placeholder="BC:60:6B:ED:47:25">
                    </div>
                    <div class="form-row">
                        <label>SN</label>
                        <input type="text" name="sn" placeholder="HWTCC1234567">
                    </div>
                    <div class="form-row" style="flex:1">
                        <label>Коментар</label>
                        <input type="text" name="comment" placeholder="маршрут / вузол / поличка">
                    </div>
                    <div class="form-actions" style="margin-top: 12px;">
                        <button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i> Додати</button>
                    </div>
                </form>
				';
				okno_end();
			}
	#}	
	exit;
}elseif($act=='delgroupdev'){
	if(!empty($USER['class']) && $USER['class']>=5){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getGRswitch = $db->Fast('switch','*',['id'=>$id]);
			if(!empty($getGRswitch['id'])){
				okno_title($lang['delet']);
				echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$getGRswitch['id'].'"><input name="act" type="hidden" value="delgroupdev">';
				echo'<div class="redton">Group delet?</div>';
				echo'</form><div class="polebtn"><button type="submit" form="formadd"  style="background: tomato;" value="submit">'.$lang['delet'].'</button></div>';
				okno_end();	
			}
		}
	}
}elseif($act=='deletlocation'){
	if(!empty($USER['class']) && $USER['class']>=5){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getLoca = $db->Fast('location','*',['id'=>$id]);
			if(!empty($getLoca['id'])){
				okno_title($lang['delet']);
				echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$getLoca['id'].'"><input name="act" type="hidden" value="deletlocation">';
				echo'<div class="redton">'.$lang['delet_location'].'</div>';
				echo'</form><div class="polebtn"><button type="submit" form="formadd"  style="background: tomato;" value="submit">'.$lang['delet'].'</button></div>';
				okno_end();	
			}
		}
	}
}elseif($act=='deletuser'){
	if(!empty($USER['class']) && $USER['class']>=6){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getUsers = $db->Fast('users','*',['id'=>$id]);
			if(!empty($getUsers['id'])){
				okno_title($lang['delet_us'].' '.$getUsers['username']);
				echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$getUsers['id'].'"><input name="act" type="hidden" value="deletuser"><div class="redton">'.$lang['delet_users'].'</div></form><div class="polebtn"><button type="submit" form="formadd"  style="background: tomato;" value="submit">'.$lang['delet'].'</button></div>';
				okno_end();	
			}
		}
	}
}elseif($act=='delmonitor'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		if(!empty($USER['class']) && $USER['class']){
		okno_title($lang['delet_onu_mon']);
		echo'<form action="/?do=send" method="post" id="formadd"><input name="idonu" type="hidden" value="'.$id.'"><input name="act" type="hidden" value="delmonitor"><div class="redton">Delet device</div></form><div class="polebtn"><button type="submit" form="formadd"  style="background: tomato;" value="submit">'.$lang['delet'].'</button></div>';
		okno_end();
		}
	}
}elseif($act=='addmonitor'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if(isset($id) && $id>0){
		okno_title($lang['madd']);
		echo'<form action="/?do=send" method="post" id="formadd" ><input name="act" type="hidden" value="addmonitor"><input name="idonu" type="hidden" value="'.$id.'">';
		echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
		echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
		okno_end();	
	}
}elseif($act=='nameonuzte6'){
	$oidid = isset($_POST['oidid']) ? Clean::int($_POST['oidid']): null;	
	$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;	
	$onu = isset($_POST['onu']) ? Clean::int($_POST['onu']): null;	
	$getonu = $db->Fast('onus','*',['idonu'=>$onu]);
	if($olt>0 && $onu>0 && $oidid>0 && !empty($getonu['idonu'])){
		okno_title($lang['add_descr'].' '.$getonu['inface']);
		echo'
			<textarea name="descr" id="descr" class="input1" rows="5" style="height: 70px;">'.(!empty($getonu['name']) ? $getonu['name'] : '').'</textarea>
			<div class="polebtn">
			<button type="button" form="formadd" value="submit" onclick="funcore(\'savenamezte6s\','.$getonu['idonu'].','.$olt.');">'.$lang['add'].'</button>
			</div>';
		okno_end();	
	}	
}elseif($act=='notebdcomepon'){
	$oidid = isset($_POST['oidid']) ? Clean::int($_POST['oidid']): null;	
	$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;	
	$onu = isset($_POST['onu']) ? Clean::int($_POST['onu']): null;	
	$getonu = $db->Fast('onus','*',['idonu'=>$onu]);
	if($olt>0 && $onu>0 && $oidid>0 && !empty($getonu['idonu'])){
		okno_title($lang['add_descr'].' '.$getonu['inface']);
		echo'
			<textarea name="descr" id="descr" class="input1" rows="5" style="height: 70px;">'.(!empty($getonu['name']) ? $getonu['name'] : '').'</textarea>
			<div class="polebtn">
			<button type="button" form="formadd" value="submit" onclick="funcore(\'savenamebdcomepon\','.$getonu['idonu'].','.$olt.');">'.$lang['add'].'</button>
			</div>';
		okno_end();	
	}	
}elseif($act=='notecdata12'){
	$oidid = isset($_POST['oidid']) ? Clean::int($_POST['oidid']): null;	
	$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;	
	$onu = isset($_POST['onu']) ? Clean::int($_POST['onu']): null;	
	$getonu = $db->Fast('onus','*',['idonu'=>$onu]);
	if($olt>0 && $onu>0 && $oidid>0 && !empty($getonu['idonu'])){
		okno_title($lang['add_descr'].' '.$getonu['inface']);
		echo'
			<textarea name="descr" id="descr" class="input1" rows="5" style="height: 70px;">'.(!empty($getonu['name']) ? $getonu['name'] : '').'</textarea>
			<div class="polebtn">
			<button type="button" form="formadd" value="submit" onclick="funcore(\'savenamecdata12\','.$getonu['idonu'].','.$olt.');">'.$lang['add'].'</button>
			</div>';
		okno_end();	
	}	
}elseif($act=='decription'){
	$oidid = isset($_POST['oidid']) ? Clean::int($_POST['oidid']): null;	
	$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;	
	$onu = isset($_POST['onu']) ? Clean::int($_POST['onu']): null;	
	$getonu = $db->Fast('onus','*',['idonu'=>$onu]);
	if($olt && $onu && $oidid && !empty($getonu['idonu'])){
		okno_title($lang['add_descr'].' '.$getonu['inface']);
		echo'
		<form action="/?do=telnet" method="post" id="formadd">
		<input name="act" type="hidden" value="savedescription">
		<textarea name="description" class="input1" rows="5" style="height: 70px;">'.(!empty($getonu['name']) ? $getonu['name'] : '').'</textarea>
		<input name="oidid" type="hidden" value="'.$oidid.'">
		<input name="olt" type="hidden" value="'.$olt.'">
		<input name="onu" type="hidden" value="'.$onu.'">
		</form>
		<div class="polebtn">
		<button type="submit" form="formadd" value="submit">'.$lang['add'].'</button>
		</div>';
		okno_end();	
	}
}elseif($act=='newlocation'){
	okno_title($lang['add_descr'].' '.$getLoca['name']);
	echo'<form action="/?do=send" method="post" id="formadd" enctype="multipart/form-data"><input name="act" type="hidden" value="newlocation">';
	echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
	echo form(['name'=>'','descr'=>'','pole'=>'<textarea name="note" class="input1" rows="7" style="height: 100px;"></textarea>']);	
	echo form(['name'=>$lang['photo'],'descr'=>$lang['photo_info'],'pole'=>'<input type="file" id="file" name="file" multiple>']);	
	echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
	okno_end();	
}elseif($act=='addgroup'){
	$selectclass = '<select class="select" name="groups" id="groups">';
	$selectclass .= '<option value="1">Olt</option>';
	$selectclass .= '<option value="2">Switch</option>';
	$selectclass .= '<option value="3">Ping3</option>';
	//$selectclass .= '<option value="4">House</option>';
	//$selectclass .= '<option value="5">Map</option>';
	//$selectclass .= '<option value="6">Other</option>';
	$selectclass .= '</select>';
	okno_title($lang['addlocation'].' '.$getLoca['name']);
	echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="addgroup">';
	echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
	echo form(['name'=>$lang['typesgroups'],'descr'=>'','pole'=>$selectclass]);
	echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
	okno_end();	
}elseif($act=='newuser'){
	$select_langate ='';
	$select_langate = '<select name="lang" id="lang">';
	$langs = $db->SimpleWhile("SELECT DISTINCT lang FROM translations ORDER BY lang");
	foreach ($langs as $language) {
		$select_langate .= '<option value="'.$language['lang'].'">'.$language['lang'].'</option>';
	}		
	$select_langate .= '</select>';
	$selectclass = '<select class="select" name="class" id="class">';
	$selectclass .= '<option value="1">'.$lang['class1'].'</option>';
	$selectclass .= '<option value="2">'.$lang['class2'].'</option>';
	$selectclass .= '<option value="3">'.$lang['class3'].'</option>';
	$selectclass .= '<option value="4">'.$lang['class4'].'</option>';
	$selectclass .= '<option value="5">'.$lang['class5'].'</option>';
	$selectclass .= '<option value="6">'.$lang['class6'].'</option>';
	$selectclass .= '<option value="7">'.$lang['class7'].'</option>';
	$selectclass .= '</select>';
	okno_title($lang['add_new_users']);
	echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="newuser">';
	echo form(['name'=>'Login','descr'=>'','pole'=>'<input autocomplete="off" required name="username" class="input1" type="text">']);	
	echo form(['name'=>'Language','descr'=>$lang['lang_descr'],'pole'=>$select_langate]);	
	echo form(['name'=>$lang['class'],'descr'=>'','pole'=>$selectclass]);	
	echo form(['name'=>'Password','descr'=>'','pole'=>'<input autocomplete="off" required name="password" class="input1" type="text">']);	
	echo form(['name'=>'Mail','descr'=>'','pole'=>'<input autocomplete="off" required name="mail" class="input1" type="text">']);	
	echo form(['name'=>$lang['ip_lock'],'descr'=>'','pole'=>'<input type="checkbox" id="onlyip" name="onlyip"><label for="onlyip">'.$lang['enable_binding'].'</label>']);	
	echo form(['name'=>$lang['ip_users'],'descr'=>'','pole'=>'<input name="setip" class="input1" type="text">']);	
	echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
	okno_end();	
}elseif($act=='edituser'){
		if(!empty($USER['class']) && $USER['class']>=6){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if (isset($id) && is_numeric($id) && $id>0) {
			$getUsers = $db->Fast('users','*',['id'=>$id]);
			if(!empty($getUsers['id'])){
				$select_langate ='';
				$select_langate = '<select name="lang" id="lang">';
				$langs = $db->SimpleWhile("SELECT DISTINCT lang FROM translations ORDER BY lang");
				foreach ($langs as $language) {
					$select_langate .= '<option value="'.$language['lang'].'" '.(isset($USER['lang']) && $config['lang'] == $language['lang'] ? 'selected' :'') .'>'.$language['lang'].'</option>';
				}		
				$select_langate .= '</select>';
				$selectclass = '<select class="select" name="class" id="class">';
				$selectclass .= '<option value="1" '.($getUsers['class']==1?'selected':'').'>'.$lang['class1'].'</option>';
				$selectclass .= '<option value="2" '.($getUsers['class']==2?'selected':'').'>'.$lang['class2'].'</option>';
				$selectclass .= '<option value="3" '.($getUsers['class']==3?'selected':'').'>'.$lang['class3'].'</option>';
				$selectclass .= '<option value="4" '.($getUsers['class']==4?'selected':'').'>'.$lang['class4'].'</option>';
				$selectclass .= '<option value="5" '.($getUsers['class']==5?'selected':'').'>'.$lang['class5'].'</option>';
				$selectclass .= '<option value="6" '.($getUsers['class']==6?'selected':'').'>'.$lang['class6'].'</option>';
				$selectclass .= '<option value="7" '.($getUsers['class']==7?'selected':'').'>'.$lang['class7'].'</option>';
				$selectclass .= '</select>';
				okno_title($lang['edit_users']);
				echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$getUsers['id'].'"><input name="act" type="hidden" value="updateuser">';
				echo form(['name'=>$lang['username'],'descr'=>'','pole'=>'<input autocomplete="off" name="username" class="input1" type="text" value="'.$getUsers['username'].'">']);	
				echo form(['name'=>$lang['usname'],'descr'=>'','pole'=>'<input autocomplete="off" name="name" class="input1" type="text" value="'.$getUsers['name'].'">']);	
				echo form(['name'=>$lang['class'],'descr'=>'','pole'=>$selectclass]);	
				echo form(['name'=>$lang['edit_password'],'descr'=>'','pole'=>'<input type="checkbox" id="editpass" name="editpass"><label for="onlyip">'.$lang['change_password'].'</label>']);	
				echo form(['name'=>$lang['newpassword'],'descr'=>'','pole'=>'<input autocomplete="off" name="newpassword" class="input1" type="text">']);	
				echo form(['name'=>'Mail','descr'=>'','pole'=>'<input autocomplete="off" name="mail" class="input1" type="text" value="'.$getUsers['email'].'">']);	
				echo form(['name'=>$lang['ip_lock'],'descr'=>'','pole'=>'<input type="checkbox" id="onlyip" name="onlyip" '.($getUsers['onlyip']=='on'?'checked':'').'><label for="onlyip">'.($getUsers['onlyip']=='on' ? $lang['binding_included'] : $lang['enable_binding']).'</label>']);	
				echo form(['name'=>$lang['ip_users'],'descr'=>'','pole'=>'<input name="setip" class="input1" type="text"  value="'.$getUsers['setip'].'">']);	
				echo form(['name'=>'Language','descr'=>'','pole'=>$select_langate]);	
				echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
				okno_end();	
			}
		}
	}
}elseif($act=='monitor'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		$device = $db->Fast('switch','*',['id'=>$id]);
		okno_title($lang['addssl'].' '.$device['place']);
		echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveaccess"><input name="id" type="hidden" value="'.$device['id'].'">';
		echo form(['name'=>'IP','descr'=>'','pole'=>'<input required name="netip" class="input1" type="text" value="'.$device['netip'].'">']);
		echo'<div class="subpole">SNMPv2 connection</div>';
		echo form(['name'=>'Community','descr'=>'SNMP Community "public"','pole'=>'<input name="public" class="input1" type="text" value="'.$device['snmpro'].'">']);
		echo form(['name'=>'Rewrite','descr'=>'SNMP Rewrite "private"','pole'=>'<input name="private" class="input1" type="text" value="'.$device['snmprw'].'">']);		
		echo'<div class="subpole">Telnet connection</div>';
		echo form(['name'=>'Username ','descr'=>'Authentication for Telnet "Username"','pole'=>'<input name="username" class="input1" type="text" value="'.$device['username'].'">']);
		echo form(['name'=>'Password','descr'=>'Authentication for Telnet "Password"','pole'=>'<input name="password" class="input1" type="text" value="'.$device['password'].'">']);
		if($device['oidid']==1 || $device['oidid']==2){
		echo form(['name'=>'Enable Password','descr'=>'Authentication for Telnet "Enable"','pole'=>'<input name="enablepassword" class="input1" type="text" value="'.$device['enablepassword'].'">']);
		}
		echo form(['name'=>'Telnet Port','descr'=>'If using port forwarding','pole'=>'<input name="telnet_port" class="input1" style="width:90px;" type="text" value="'.$device['telnet_port'].'">']);
		echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
		okno_end();	
	}
}elseif($act=='delete'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		$device = $db->Fast('switch','*',['id'=>$id]);
		okno_title($device['place']);
		echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$device['id'].'"><input name="act" type="hidden" value="deletdevice"><div class="redton">'.$lang['delete_pmon'].'</div></form><div class="polebtn"><button type="submit" form="formadd" style="background: tomato;" value="submit">'.$lang['delet'].'</button></div>';
		okno_end();
	}
}elseif($act=='deletevlan'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		$getvlan = $db->Fast('ipvlans','*',['id'=>$id]);
		if(isset($getvlan['id']) && $getvlan['id']>0){
		$count_vlan = $db->SimpleWhile("SELECT COUNT(idonu) as count_vlan FROM onus WHERE wan = '{$getvlan['vlan']}'")[0];
		echo'<form action="/?do=send" method="post" id="formadd">
		<input name="id" type="hidden" value="'.$getvlan['id'].'">
			<div class="redton">'.$lang['delete_vlan'].'</div>
		<input name="act" type="hidden" value="deletvlan">';
		echo'</form>
		<div class="polebtn block-flex">		
			<button type="button" class="closePopup color-b">'.$lang['close'].'</button>
			<button type="submit" form="formadd" style="background: tomato;" value="submit">'.$lang['delet'].'</button>
		</div>';
		
		}
	}	
}elseif($act=='getvlan'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		$getvlan = $db->Fast('ipvlans','*',['id'=>$id]);
		if(isset($getvlan['id']) && $getvlan['id']>0){
		$count_vlan = $db->SimpleWhile("SELECT COUNT(idonu) as count_vlan FROM onus WHERE wan = {$getvlan['vlan']}")[0];
		echo'<form action="/?do=send" method="post" id="formadd">
		<input name="id" type="hidden" value="'.$getvlan['id'].'">
		<input name="act" type="hidden" value="updatevlan">';
		echo form(['name'=>'Vlan','descr'=>'1-4096','pole'=>'<input autocomplete="off" required name="vlan" class="input1" type="text" value="'.$getvlan['vlan'].'">']);
		echo form(['name'=>$lang['opis'],'descr'=>$lang['addbattery_8'],'pole'=>'<input autocomplete="off" required name="name" class="input1" type="text" value="'.$getvlan['name'].'">']);
		echo form(['name'=>$lang['descronus'],'descr'=>'','pole'=>$count_vlan['count_vlan']]);
		echo'</form>
		<div class="polebtn block-flex">		
			<button type="button" class="closePopup color-b">'.$lang['close'].'</button>
			<button type="submit" form="formadd" value="submit">'.$lang['update'].'</button>
		</div>';		
		}
	}
}elseif($act=='reset'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if (isset($id) && is_numeric($id) && $id>0) {
		$device = $db->Fast('switch','*',['id'=>$id]);
		okno_title($device['place']);
		echo'<form action="/?do=send" method="post" id="formadd"><input name="id" type="hidden" value="'.$device['id'].'"><input name="act" type="hidden" value="resetdevice"><div class="redton">'.$lang['reset_pmon'].'</div></form><div class="polebtn"><button type="submit" form="formadd"  style="background: tomato;" value="submit">'.$lang['reset'].'</button></div>';
		okno_end();
	}
}
?>