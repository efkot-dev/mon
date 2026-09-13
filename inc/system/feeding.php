<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function type_device($type){
	$res = [
		1 => "Вузол",
		2 => "OLT",
		3 => "Свіч",
		4 => "ONU",
		5 => "Багатоповерхівка",
		6 => "Тип 1",
		7 => "Тип 2",
	];
	return $res[$type] ?? null;	
}
$sqllocation = getLocation();
$location_array = [];
if(isset($sqllocation) && count($sqllocation)>0){
	foreach($sqllocation as $loc){
		$location_array[$loc['id']] = array('id'=>$loc['id'],'name'=>$loc['name']);
	}
}
$sqlinsert = [];
$speedbar = '';
$templates = '';
switch($act){
	case 'add': 
		$metatags = array('title'=>'Додати живлення','description'=>'Додати живлення','page'=>'addfeeding');
		$speedbar .='<a class="brmhref" href="/?do=feeding"><i class="fi fi-rr-apps"></i>Список живлення</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати живлення</span>';
		$templates .= '<div class="nav-fiber p10"><form action="/?do=feeding" method="post"><input name="act" type="hidden" value="savefeeding">';	
		$templates .='<label for="name">П.І.Б:</label><input type="text" id="pib" name="pib" required="" autocomplete="off" style="width:50%;"><br>';
		$templates .='<label for="name">Мобільний номер:</label><input type="text" id="mobtel" name="mobtel" required="" autocomplete="off" style="width:50%;"><br>';
		$templates .='<label for="type">Локація:</label>';
		$location = getListLocations();
		if(count($location)>0){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'" >'.$loc['name'].'</option>';
			}
		}
		$templates .='<select class="select" name="locationid" id="locationid"><option value="0"></option>'.$listlocation.'</select><br>';
		$templates .='<label for="name">Адреса вулиця номер буинку, квартира:</label><input type="text" id="street" name="street" required="" autocomplete="off" style="width:50%;"><br>';
		$templates .='<label for="type">Тип обладання:</label>';
		$listtypedevice .= '<option value="1">Вузол</option>';
		$listtypedevice .= '<option value="2">OLT</option>';
		$listtypedevice .= '<option value="3">Свіч</option>';
		$listtypedevice .= '<option value="4">ONU</option>';
		$listtypedevice .= '<option value="5">Багатоповерхівка</option>';
		$listtypedevice .= '<option value="6">Тип 1</option>';
		$listtypedevice .= '<option value="7">Тип 2</option>';
		$templates .='<select class="select" name="devicetype" id="devicetype"><option value="0"></option>'.$listtypedevice.'</select><br>';
		$templates .='<input type="submit" value="'.$lang['add'].'">';
		$templates .='</div></form>';
	break;
	case 'delet': 	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){
			$feeding = $db->Fast('feeding','*',['id'=>$id]);
			if(isset($feeding['id']) && !empty($feeding['id']) ){
				$db->query("DELETE FROM feeding WHERE id = '{$feeding['id']}'");
			}
		}		
		$go->go('/?do=feeding');
	break;
	case 'savefeeding': 	
		if(isset($_POST['pib']) && isset($_POST['mobtel']) && isset($_POST['locationid']) && isset($_POST['street'])){
			$sqlinsert['pib'] = Clean::text($_POST['pib']);
			$sqlinsert['mobtel'] = Clean::text($_POST['mobtel']);
			$sqlinsert['devicetype'] = Clean::int($_POST['devicetype']);
			$sqlinsert['locationid'] = Clean::int($_POST['locationid']);
			$sqlinsert['street'] = Clean::text($_POST['street']);
			$sqlinsert['added'] = date('Y-m-d H:i:s');
			$data_location = $db->Fast('location','*',['id'=>$sqlinsert['locationid']]);
			$sqlinsert['locatoioname'] = $data_location['name'];
			$db->SQLinsert('feeding',$sqlinsert);
		}
		$go->go('/?do=feeding');
	break;		
	default:
		$where = '';
		$metatags = array('title'=>'Список точок живлення','description'=>'Список точок живлення','page'=>'listfeeding');
		$speedbar .='<a class="brmhref" href="/?do=feeding"><i class="fi fi-rr-apps"></i>Живлення</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Список активного живлення</span>';
		$templates .= '<table class="resp-tab"><thead><tr>
			<th width="15%">Локація</th>
			<th width="15%">Тип обладнання</th>			
			<th width="15%">П.І.Б.</th>			
			<th width="15%">Адреса</th>
			<th>Дані</th><th></th></tr></thead><tbody>';
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($_GET['sort']) && isset($id)){
			$where .="WHERE ";
			if($_GET['sort']=='location'){
				$where .=" locationid = '{$id}'";
			}
		}
		$feeding = $db->SimpleWhile("SELECT * from feeding {$where}");
		if(count($feeding)>0){
			foreach($feeding as $jiv){
			$templates .= '<tr>
						<td class="td_url"><a href="/?do=feeding&sort=location&id='.$jiv['locationid'].'">'.$location_array[$jiv['locationid']]['name'].'</a></td>
						<td class="td_url"><a href="/">'.type_device($jiv['devicetype']).'</a></td>
						<td class="td_names">'.$jiv['pib'].'</td>
						<td>'.$jiv['street'].'</td>
						<td>'.$jiv['mobtel'].'</td>
						<td><a class="panel_house rr_1" href="/?do=feeding&act=delet&id='.$jiv['id'].'">Видалити</a></td>
					</tr>';
			}
		}else{
			$templates .= '<tr><td colspan="4">'.$lang['empty'].'</td></tr>';
		}
		$templates .= '</table>';
		$templates .= '<div class="pole">
		'.(isset($_GET['sort']) && isset($id) ? '<a href="/?do=feeding" class="urlelelement">Очистити сортування</a>' : '' ).'
		<a href="/?do=feeding&act=add" class="urlelelement">Додати живлення</a>
		</div>';
}
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin"><div id="onu-speedbar">'.$speedbar.'</div>'.$templates.'</div>');
$tpl->compile('content');
$tpl->clear();
?>