<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;
$location = isset($_POST['location']) ? Clean::int($_POST['location']): null;
$result ='';
switch($act){
	case 'add': 
		$result .='<div class="nav-fiber p10" style="width:50%;">
		<form action="/?do=house" method="post" id="house">
		<input name="act" type="hidden" value="savehouse">
		<label for="name">'.$lang['name_house'].':</label>
		<input type="text" id="name" name="name" required="" autocomplete="off" style="width:50%;"><br>
		<label for="type">'.$lang['taskman_us_location'].':</label>';
		$location = getListLocations();
		if(isset($location) && count($location)>0){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'" >'.$loc['name'].'</option>';
			}
		}
		$result .='<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select><br>';
		$result .='<div id="setup_skyscraper"></div></form></div>';
		$result .= "<script>	
		$('#location').on('change',function(){
			var location = $(this).val();
			$.post(root + 'ajax/house.php',{act:'setuphouse',location:location},function(response){
				$('#setup_skyscraper').html(response);
			},'html');
		});			
		</script>";	
		echo $result;
	break;	
	case 'select': 
		$types = isset($_POST['types']) ? Clean::int($_POST['types']): null;	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		$index = isset($_POST['index']) ? Clean::int($_POST['index']): null;
		if(is_valid_id($index) && is_valid_id($id)){
			if($types==1){
				echo'
				ONU:<br><input type="text" style="width:300px;" class="input1" id="onu" name="onu" required autocomplete="off">
				<div id="searchonu"></div>
				<span onclick="kninsert('.$id.','.$index.')" class="connect_fiber_btn jsadded" style="display:none;top: 8px;position: relative;">'.$lang['add'].'</span>
				';
				echo"
				<script>
					$(document).ready(function() {
						$('#onu').on('input', function() {
							var searchText = $(this).val();
							$.ajax({
								method: 'POST',
								url: root + 'ajax/house.php',
								data: {act: 'getonu', onu: searchText},
								success: function(response) {
									$('#searchonu').html(response);
								}
							});
						});
						$(document).on('click', '.select-onu', function(e) {
							$('.jsadded').show();
							e.preventDefault();
							var id = $(this).data('id');
							var onuField = $('#onu');
							onuField.val(id);
							$('#searchonu').empty();
						});
					});
				</script>
				";
			}
		}
	break;	
	case 'connect': 	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if(is_valid_id($id)){
			$skyscraper = $db->Fast('skyscraper','*',['id'=>$id]);
			if(is_valid_id($skyscraper['id'])){
				okno_title($lang['btn_menu_device']);
				$sql_switch = $db->SimpleWhile("SELECT * FROM switch WHERE monitor = 'yes'");
				foreach($sql_switch as $id => $device) {
					$option .= '<option value="'.$device['id'].'">'.$device['place'].', '.$device['inf'].' '.$device['model'].'</option>';
				}
				echo'<form action="/?do=send" method="post" id="formadd">
					<input name="act" type="hidden" value="connecthouse">
					<input name="id" type="hidden" value="'.$skyscraper['id'].'">'
					. form([
						'name'=>$lang['btn_menu_device'],'descr'=>$lang['install_house'],
						'pole'=>'<select class="select" name="device" id="device">'.$option.'</select>']
					).'
					</form>
					<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button>
					</div>';
				okno_end();	
			}
		}
	break;	
	case 'setup': 	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
		if(is_valid_id($id)){
			$skyscraper = $db->Fast('skyscraper','*',['id'=>$id]);
			if(is_valid_id($skyscraper['id'])){
				$option = '';
				$location_street = $db->SimpleWhile("SELECT * FROM location_street");
				foreach($location_street as $id => $location) {
					$option .= '<option value="'.$location['id'].'" '.
					(isset($location['id']) && $location['id'] == $skyscraper['streetid']? 'selected' : '')
					.'>'.$location['name'].'</option>';
				}
				okno_title($lang['edit']);
				echo'
				<form action="/?do=send" method="post" id="formadd" enctype="multipart/form-data">
				<input name="act" type="hidden" value="updatehouse">
				<input name="id" type="hidden" value="'.$skyscraper['id'].'">'
				. form([
					'name'=>$lang['name'],'descr'=>$lang['short_name_house'],
					'pole'=>'<input required name="name" class="input1" type="text" value="'.$skyscraper['name'].'">']
				). form([
					'name'=>$lang['street'],'descr'=>$lang['street_name_house'],
					'pole'=>'<select class="select" name="streetid" id="streetid">'.$option.'</select>']
				). form([
					'name'=>$lang['photo_house'],'descr'=>$lang['photo_name_house'],
					'pole'=>'<input type="file" id="file" name="file" multiple>']
				). form([
					'name'=>$lang['opis'],'descr'=>'',
					'pole'=>'<textarea class="textarea1" rows="7" name="note">'.$skyscraper['note'].'</textarea>']
				). form([
					'name'=>$lang['edit_pidizd'],'descr'=>$lang['edit_up_pidizd'],
					'pole'=>'<input required name="pidizd" class="input1" type="text" style="width:10%;" value="'.$skyscraper['pidizdiv_int'].'">']
				). form([
					'name'=>$lang['edit_poverx'],'descr'=>$lang['edit_up_poverx'],
					'pole'=>'<input required name="poverx" class="input1" type="text" style="width:10%;" value="'.$skyscraper['poverxiv_int'].'">']
				).'
				</form>
				<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button>
				</div>';
				okno_end();	
			}
		}
		die;
	break;	
	case 'insertonu': 
		$data = array();	
		$data['onukey'] = (isset($_POST['onu']) ? Clean::text($_POST['onu']): null);
		$data['skyscraperid'] = (isset($_POST['houseid']) ? Clean::int($_POST['houseid']): null);
		$data['kvnomer'] = (isset($_POST['kvnomer']) ? Clean::int($_POST['kvnomer']): null);
		$data['kvindex'] = (isset($_POST['knindex']) ? Clean::int($_POST['knindex']): null);
		$data['dogovir'] = (isset($_POST['dogovir']) ? Clean::int($_POST['dogovir']): null);
		$data['added'] = date('Y-m-d H:i:s');
		$sqlonus = $db->Simple("SELECT * FROM onus WHERE mac = '".$data['onukey']."' OR sn = '".$data['onukey']."' LIMIT 1");
		if(isset($sqlonus['idonu']) && isset($data['kvindex'])&& isset($data['kvnomer']) && isset($data['skyscraperid'])){
			$data['status'] = $sqlonus['status'];
			$data['signal'] = $sqlonus['rx'];
			$data['idonu'] = $sqlonus['idonu'];
			$db->SQLinsert('skyscraper_kv',$data);
			echo'success';
		}
	break;	
	case 'getonu': 		
		if (isset($_POST['onu'])) {
		$zapros = (isset($_POST['onu']) ? Clean::text($_POST['onu']): null);				
		$orderby = " ORDER BY idonu ASC";
		$limit = " LIMIT 10";
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
		if(count($sqlonus)>0){
			echo '<ul class="resultpoiskonu">';
			foreach ($sqlonus as $ont) {
				$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
				if(isset($onukey)){
				echo '<li><a href="#" class="select-onu" data-id="' . $onukey . '">';
				echo'<span>'.$ont['inface'].'</span> ' . $onukey . '';
				echo'</a></li>';
				}
			}
			echo '</ul>';
		}else{
			echo $lang['empty'];
		}
		}	
	break;	
	case 'setuphouse': 	
		if($access->get('house_edit')){
		echo"<script>
		$('#streetname').on('input', function() {
			var location = $('#location').val();
			var streetname = $('#streetname').val();
			$.post(root + 'ajax/house.php',{act:'street',location:location,streetname:streetname},function(response){
				$('#select_street').html(response);
			},'html');
		});	
		</script>";
		echo'
		<label for="name">'.$lang['name_street_house'].':</label>
		<input type="text" id="streetname" name="streetname" required="" autocomplete="off" style="width:30%;">
		<div id="select_street"></div><br>
		<label for="name">'.$lang['name_nomer_house'].':</label>
		<input type="text" id="nomer" name="nomer" required="" autocomplete="off" style="width:10%;"><br>
		<label for="name">'.$lang['name_count_kv_house'].':</label>
		<input type="text" id="nomer" name="poverxiv" required="" autocomplete="off" style="width:10%;"><br>
		<label for="name">'.$lang['name_count_kv_house_pidizd'].':</label>
		<input type="text" id="nomer" name="pidizdiv" required="" autocomplete="off" style="width:10%;"><br>
		<input type="hidden" id="nomer" name="kvartur" value="1">
		<label for="name"><label for="pidvalCheckbox">'.$lang['int_pidval'].' 
		<input type="checkbox" id="pidvalCheckbox" onclick="togglePidvalInput();"></label></br></br>
		<div id="pidvalInput" style="display: none;">
		<label for="name">'.$lang['rivni_pidval'].':</label>
		<input type="text" id="pidval" name="pidval" required="" autocomplete="off" style="width: 5%;">
		</div></br>
		<span class="connect_fiber_btn" onclick="addedhouse(\''.$lang['alert_empty'].'\');">'.$lang['addeds'].'</span>';
		}
	break;	
	case 'street': 
		$streetname = (isset($_POST['streetname']) ? Clean::text($_POST['streetname']): null);
		if(isset($streetname)){
			$sql = "SELECT * FROM `location_street` WHERE locationid = '$location' AND name LIKE '%".$streetname."%'";
			$sql_street = $db->SimpleWhile($sql);
			if(isset($sql_street) && count($sql_street) > 0) {
				echo '<ul class="resultpoiskonu">';
				foreach ($sql_street as $str) {
					if(isset($str['name'])) {
						echo "<li><a href=\"#\" class=\"select-onu\" 
						onclick='insert_pole(\"{$str['name']}\",\"#streetname\")'>{$str['name']}</a></li>";
					}
				}
				echo '</ul>';
			}
		}
	break;	
	default:
		echo'';
}
?>
