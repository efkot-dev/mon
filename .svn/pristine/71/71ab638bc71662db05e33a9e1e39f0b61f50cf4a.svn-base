<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$monitor = isset($_POST['monitor']) ? Clean::text($_POST['monitor']): null;
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
switch($act){
	case 'connect': 
		echo'<form action="/?do=send" method="post" id="formadd">
		<input name="act" type="hidden" value="connectbattery">
		<input name="batteryid" type="hidden" value="'.$id.'">
		<div class="block_white"><div class="polebtn">';
		echo'<select class="select" name="monitor" id="monitor"><option value="0"></option>';
		echo'<option value="ping3">Ping3</option>';
		echo'<option value="olt">Olt</option>';
		#echo'<option value="pmon">PMon Sensor</option>';
		echo'<option value="unit">'.$lang['cfg_unit'].'</option>';
		echo'<option value="other">'.$lang['other'].'</option>';
		echo'</select><br>';
		echo'<div class="js_replace"></div><div style="margin-top:8px;color:#7a7a7a;">Підключення працює як переміщення: стара привʼязка буде замінена.</div></div>';
		?><script>
			$('#monitor').on('change', function() {
			$('.knopkagreen').hide();
			var selected = $(this).val();$.post(root+'ajax/battery.php',{
				act:'selectdevice',monitor:selected},function(response){
					$('.js_replace').html(response);
				},'html');
			});
		</script><?php
		echo'</div></form>';
	break;	
	case 'photo': 	
		if(isset($id) && $id>0){
		echo'
		<form action="/?do=battery" method="post" id="formadd" enctype="multipart/form-data">
			<input name="act" type="hidden" value="photobattery">
			<input name="id" type="hidden" value="'.$id.'">
			<div class="block_white">
				<div class="polebtn">
					<label for="photo">'.$lang['choose_upload'].':</label><input type="file" id="file" name="file" accept="image/*" required>
					<br><br><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button>
				</div>
			</div>
		</form>';
		}
		break;	
	case 'vyzol': 		
		echo'
			<form action="/?do=send" method="post" id="formadd">
			<input name="act" type="hidden" value="sendbattery">
			<input name="batteryid" type="hidden" value="'.$id.'">
			<div class="polebtn">
			<select class="select" name="unitid" id="unitid">
			<option value="0"></option>';
			$sqlselect = $db->Multi('ponunit');	
			if(is_array($sqlselect)){
				foreach($sqlselect as $select){
					echo'<option value="'.$select['id'].'">'.$select['name'].'</option>';
				}
			}
		echo'
			</select>
			<br>
			<button type="submit" form="formadd" value="submit">'.$lang['connect'].'</button><br>
			</form>';	
	break;	
	case 'edit':
		if(isset($id) && $id>0){
			$getBattery = $db->Fast('battery','*',['id'=>$id]);
		}
		if($access->get('monitordevice') && !empty($getBattery['id'])){
			$result .='<div class="block_white"><form action="/?do=battery" method="post" id="formadd">
			<input name="id" type="hidden" value="'.$getBattery['id'].'">
			<input name="act" type="hidden" value="updatebattery">';
			$result .= formpage(['img'=>'addconnect.png',
				'name'=>$lang['name'],'descr'=>$lang['addbattery_8'],
				'pole'=>'<input style="width:69%;" name="name" class="input1" type="text" value="'.$getBattery['name'].'">']);
			$result .= formpage(['img'=>'addconnect.png',
				'name'=>'SN','descr'=>$lang['sn_number'],
				'pole'=>'<input style="width:69%;" name="sn" class="input1" type="text" value="'.$getBattery['sn'].'">']);
			$result .= formpage(['img'=>'addconnect.png',
				'name'=>$lang['addbattery_7'],'descr'=>$lang['addbattery_6'],
				'pole'=>'<input style="width:69%;" name="model" class="input1" type="text" value="'.$getBattery['model'].'">']);
			$typebattery = '<select class="select" name="typebattery">
				<option value="12" '.(isset($getBattery['voltage']) && $getBattery['voltage']=='12' ? 'selected' : '').'>12</option>
				<option value="24" '.(isset($getBattery['voltage']) && $getBattery['voltage']=='24' ? 'selected' : '').'>24</option>
				<option value="48" '.(isset($getBattery['voltage']) && $getBattery['voltage']=='48' ? 'selected' : '').'>48</option>
				<option value="72" '.(isset($getBattery['voltage']) && $getBattery['voltage']=='72' ? 'selected' : '').'>72</option>
			</select>';
			$result .= formpage(['img'=>'addconnect.png','name'=>$lang['addbattery_5'],'descr'=>$lang['addbattery_4'],'pole'=>$typebattery]);
			$types  = '<select class="select" name="types">
				<option value="lifepo4" '.(isset($getBattery['types']) && $getBattery['types']=='lifepo4' ? 'selected' : '').'>LifePo4</option>
				<option value="agm" '.(isset($getBattery['types']) && $getBattery['types']=='agm' ? 'selected' : '').'>AGM</option>
				<option value="sbs" '.(isset($getBattery['types']) && $getBattery['types']=='sbs' ? 'selected' : '').'>SBS</option>
				<option value="gel" '.(isset($getBattery['types']) && $getBattery['types']=='gel' ? 'selected' : '').'>GEL</option>
				<option value="default" '.(isset($getBattery['types']) && $getBattery['types']=='default' ? 'selected' : '').'>Default</option>
			</select>';
			$result .= formpage(['img'=>'addconnect.png',
				'name'=>$lang['addbattery_3'],'descr'=>$lang['addbattery_4'],
				'pole'=>'<input style="width:15%;" name="amper" class="input1" type="text" value="'.$getBattery['amper'].'">']);
			$result .= formpage(['img'=>'addconnect.png',
				'name'=>$lang['addbattery_1'],'descr'=>$lang['addbattery_1'],
				'pole'=>$types]);
			$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['update'].'</button></form></div>';
		}
		echo $result;
	break;		
	case 'selectdevice': 
		if($monitor=='olt'){
			$sqlselect = $db->Multi('switch');
		}elseif($monitor=='unit'){
			$sqlselect = $db->Multi('ponunit');
		}elseif($monitor=='other'){
			echo'<input class="input1" name="deviceid" type="number" min="1" placeholder="ID">';
			echo'<br><button type="submit" form="formadd" value="submit">'.$lang['connect'].'</button><br>';
			break;
		}else{
			$sqlselect = $db->Multi('mon_ping3');
		}		
		echo'<select class="select" name="deviceid" id="deviceid"><option value="0"></option>';
		if(is_array($sqlselect)){
			foreach($sqlselect as $select){
				echo'<option value="'.$select['id'].'">'.(isset($select['name']) ? $select['name'] : (isset($select['place'])?$select['place']:'n\a')).'</option>';
			}
		}
		echo'</select><br><button type="submit" form="formadd" value="submit">'.$lang['connect'].'</button><br>';
	break;
}
?>
