<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('house_view')){
	$go->redirect('main');
}
require_once ENGINE_DIR.'functions/building.php';
$config_uid = (!empty($confPMon['PON_HIGH_RISE_UID']) && $confPMon['PON_HIGH_RISE_UID'] == 1 ? true : false);
$result_billing  = '';
$tplresult = '';
$speedbar = '';
$location = isset($_POST['location']) ? Clean::text($_POST['location']): null;
switch($act){
	case 'add':
		$metatags = array(
			'title' => $lang['newhouse'],
			'description' => $lang['newhouse'],
			'page' => 'addhouse'
		);	
		$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
		$speedbar .='<a class="brmhref" href="/?do=house"><i class="fi fi-rr-angle-left"></i>'.$lang['list_house'].'</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['newhouse'].'</span>';
		$tplresult .= '<script>gethouse(\'add\');</script><div id="skyscraper"></div>';	
	break;		
	case 'save': 
		$house = array();
		$street = array();
		$house['name'] = isset($_POST['data']['name']) ? Clean::text($_POST['data']['name']): null;
		$house['nomer'] = isset($_POST['data']['nomer']) ? Clean::text($_POST['data']['nomer']): null;
		$house['locationid'] = isset($_POST['data']['location']) ? Clean::int($_POST['data']['location']): null;
		$location = $db->Fast('location','*',['id'=>$house['locationid']]);
		$house['locationname'] = $location['name'];	
		$pidval = isset($_POST['data']['pidval']) ? Clean::int($_POST['data']['pidval']): null;		
		$poverxiv = isset($_POST['data']['poverxiv']) ? Clean::int($_POST['data']['poverxiv']): 1;
		$house['kvartur_int'] = isset($_POST['data']['kvartur']) ? Clean::int($_POST['data']['kvartur']): 1;
		$house['pidizdiv_int'] = isset($_POST['data']['pidizdiv']) ? Clean::int($_POST['data']['pidizdiv']): 1;
		if(is_valid_id($pidval)){
			$house['poverxiv_int'] = $poverxiv;
			$house['kvartur'] = ($poverxiv*$house['kvartur_int'])*$house['pidizdiv_int'];
			$house['poverxiv'] = gen_sequence($poverxiv + $pidval);
		}else{
			$house['poverxiv_int'] = $poverxiv;
			$house['kvartur'] = ($poverxiv*$house['kvartur_int'])*$house['pidizdiv_int'];
			$house['poverxiv'] = gen_sequence_not($poverxiv);	
		}
		$street['name'] = isset($_POST['data']['streetname']) ? Clean::text($_POST['data']['streetname']): null;
		$street['locationid'] = $house['locationid'];
		$house['view'] = 'yes';
		$house['street'] = $street['name'];
		$house['added'] = date('Y-m-d H:i:s');
		$street['added'] = $house['added'];
		$sky_scraper_street = $db->Fast('location_street','*',['locationid'=>$street['locationid'],'name'=>$street['name']]);
		if(isset($sky_scraper_street['id'])){
			$house['streetid'] = $sky_scraper_street['id'];	
		}else{
			$db->SQLinsert('location_street',$street);
			$streetid = $db->getInsertId();
			$house['streetid'] = $streetid;			
		}
		$db->SQLinsert('skyscraper',$house);
		$houseid = $db->getInsertId();
		die;
	break;		
	case 'savegeo': 	
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
		if(empty($id)){
			$go->go('/?do=house');
		}else{
			$lan = isset($_POST['lan']) ? Clean::text($_POST['lan']): null;
			$lon = isset($_POST['lon']) ? Clean::text($_POST['lon']): null;
			if(isset($lan) && isset($lon)){				
				$db->SQLupdate('skyscraper',['lan'=>substr($lan, 0, 9),'lon'=>substr($lon, 0, 9)],['id'=>$id]);
			}
			$go->go('/?do=house&act=view&id='.$id);
		}
	break;		
	case 'geo': 
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
		if(empty($id)){
			$go->go('/?do=house');
		}
		$skyscraper = $db->Fast('skyscraper','*',['id'=>$id]);
		if(empty($skyscraper['id'])){
			$go->go('/?do=house');
		}
		$location = $db->Fast('location','*',['id'=>$skyscraper['locationid']]);
		$tplresult .='<link rel="stylesheet" href="../style/map/leaflet.css" />';
		$tplresult .='<script src="../style/map/leaflet.js"></script>
		<script src="../style/map/mymarker.js"></script>
		<form action="/?do=house" method="post" id="formadd">
		<input name="act" type="hidden" value="savegeo">
		<input name="id" type="hidden" value="'.$id.'">';
		$tplresult .='<div id="map" style="height: 500px;"></div>';
		$geo_lan = (!empty($skyscraper['lan'])?$skyscraper['lan']:$location['lan']);
		$geo_lon = (!empty($skyscraper['lon'])?$skyscraper['lon']:$location['lon']);
$tplresult .= <<<HTML
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
.setContent('<b>{$lang['add_geo_house']}</b> <br>' +
'<input name="lan" type="hidden" value="' + lat + '"><input name="lon" type="hidden" value="' + lon + '"><span class="koomap"><b>{$lang['geo']}</b>: ' + lat + ' ' + lon + '</span><br><button type="submit" class="cssadd">{$lang['save']}</button>')
.openOn(map);
}
map.on('click', onMapClick);
</script>
HTML;
	echo $script.'</form>';
	break;		
	case 'info': 
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;	
		if(is_valid_id($id)){
			$ho = $db->Simple("SELECT * FROM skyscraper WHERE id = $id LIMIT 1");    
			if ($ho && count($ho) > 0) {
				echo json_encode([
					'photo' => $ho['photo'] ?? '',
					'onus' => $ho['onus'],
					'online' => $ho['online'],
					'offline' => $ho['offline']
				]);
			} else {
				echo json_encode(['error' => 'House not found']);
			}
			exit;
		}
	break;		
	case 'savecomm': 		
		$note = isset($_POST['note']) ? Clean::text($_POST['note']): null;
		$icon = isset($_POST['icon']) ? Clean::text($_POST['icon']): 'default';
		$kv = isset($_POST['kv']) ? Clean::int($_POST['kv']): null;
		$house = isset($_POST['house']) ? Clean::int($_POST['house']): null;
		$added = date('Y-m-d H:i:s');
		$us = $USER['id'];
		if(is_valid_id($house) && is_valid_id($kv) && isset($note)){
			$db->query("INSERT INTO skyscraper_comm (`note`,`info`,`house`,`kv`,`us`,`added`) VALUES ('{$note}','{$icon}','{$house}','{$kv}','{$us}','{$added}')");
			$go->go('/?do=house&act=view&id='.$house);
		}	
		exit;
	break;		
	case 'formcomm': 	
		$house = isset($_POST['house']) ? Clean::int($_POST['house']): null;
		$kv = isset($_POST['kv']) ? Clean::int($_POST['kv']): null;
		if(is_valid_id($house) && is_valid_id($kv)){
			okno_title($lang['add_marker_house']);
			echo '<div style="width: 100%;">
				<form action="/?do=house" method="post">
					<input name="act" type="hidden" value="savecomm">
					<input name="kv" type="hidden" value="'.$kv.'">
					<input name="house" type="hidden" value="'.$house.'">
					<label for="type">'.$lang['selected'].':</label><br>
					<input type="radio" id="icon1" name="icon" value="user">
					<label for="icon1"><img src="../style/img/house-user.png" alt="Icon 1"></label>&nbsp;&nbsp;        
					<input type="radio" id="icon1" name="icon" value="success">
					<label for="icon1"><img src="../style/img/house-success.png" alt="Icon 1"></label> &nbsp;&nbsp;  
					<input type="radio" id="icon1" name="icon" value="optics">
					<label for="icon1"><img src="../style/img/house-optics.png" alt="Icon 1"></label> &nbsp;&nbsp;       
					<input type="radio" id="icon1" name="icon" value="ethernet">
					<label for="icon1"><img src="../style/img/house-ethernet.png" alt="Icon 1"></label>&nbsp;&nbsp;        
					<input type="radio" id="icon1" name="icon" value="comm">
					<label for="icon1"><img src="../style/img/house-comm.png" alt="Icon 1"></label>&nbsp;&nbsp;
					<input type="radio" id="icon1" name="icon" value="ban">
					<label for="icon1"><img src="../style/img/house-ban.png" alt="Icon 1"></label>&nbsp;&nbsp;
					<input type="radio" id="icon1" name="icon" value="cammera">
					<label for="icon1"><img src="../style/img/house-camera.png" alt="Icon 1"></label>&nbsp;&nbsp;        
					<textarea id="note" name="note" style="width:100%;"></textarea><br>		
					<input type="submit" value="'.$lang['addeds'].'">	
				</form>
			</div>';
			okno_end();
			exit;
		}
	exit;
	break;		
	case 'delcomm': 	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
		$house = isset($_GET['house']) ? Clean::int($_GET['house']): null;
		if(is_valid_id($id) && $access->get('house_edit')){
			$db->SQLdelete('skyscraper_comm',['id' => $id]);
			$go->go('/?do=house&act=view&id='.$house);
			exit;			
		}
		$go->go('/?do=house');
		exit;
	break;		
	case 'comm': 
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
		if(is_valid_id($id)){
			$comm = $db->Simple("SELECT * FROM skyscraper_comm WHERE id = $id LIMIT 1"); 
			okno_title($lang['information']);
			echo '<div style="width: 100%;">	
			'.$comm['note'].'
			'.($access->get('house_edit') ? '<a href="/?do=house&act=delcomm&id='.$id.'&house='.$comm['house'].'">
				<img src="../style/img/close.png">
			</a>' : '').'
			</div>';
			okno_end();
			exit;
		}	
	break;		
	case 'view': 
		$sqlorderby ='';
		$sort = isset($_GET['sort']) ? Clean::text($_GET['sort']): null;
		$types = isset($_GET['types']) ? Clean::text($_GET['types']): null;
		$index = isset($_GET['index']) ? Clean::int($_GET['index']): null;
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
		if(empty($id)){
			$go->go('/?do=house');
		}	
		$form_add ='';		
		if(isset($index) && $access->get('house_edit')){
			$form_add .='<div class="pole"><a href="/?do=house&act=view&id='.$id.'" class="urlelelement">'.$lang['close'].'</a></div>';
			$form_add .=''.generateJavaScript($id,$index);
		}
		$skyscraper = $db->Fast('skyscraper','*',['id'=>$id]);
		if(empty($skyscraper['id'])){
			$go->go('/?do=house');
		}
		if(isset($sort) && isset($types)){
			$sql_sort = ($types=='asc'?' ASC':' DESC');
			if($sort=='kv'){
				#$orderby .='`kvnomer`';
				$orderby .='CAST(kvnomer AS SIGNED)';
				$sort1 = ($types=='asc'?'desc':'asc');
			}elseif($sort=='status'){
				$orderby .='`status`';
				$sort2 = ($types=='asc'?'desc':'asc');	
			}elseif($sort=='signal'){
				$orderby .='`signal`';
				$sort3 = ($types=='asc'?'desc':'asc');	
			}elseif($sort=='onu'){
				$orderby .='`onukey`';
				$sort4 = ($types=='asc'?'desc':'asc');
			}				
			if(isset($orderby)){
				$sqlorderby = 'ORDER BY '.$orderby.$sql_sort;
			}
		}
		$metatags = array('title'=>$skyscraper['locationname'].' '.$skyscraper['street'].' '.$skyscraper['name'],'description'=>$skyscraper['name'],'page'=>'skyscraper');
		$kvarturu = [];
		$sql_kv = $db->SimpleWhile("SELECT * from skyscraper_kv where skyscraperid = {$id} {$sqlorderby}");
		if(count($sql_kv)>0){
			foreach ($sql_kv as $kvid => $kv) {
				$kvarturu[$kv['kvindex']]['kv'][$kvid] = $kv;
			}
		}
		$switch = getListOlt();
		$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
		$speedbar .='<a class="brmhref" href="/?do=house"><i class="fi fi-rr-angle-left"></i>'.$lang['list_house'].'</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$skyscraper['locationname'].' '.$skyscraper['street'].' '.$skyscraper['name'].'</span>';
		$num_pidizd = gen_sequence_not($skyscraper['pidizdiv_int']);
		$cellNumbers = explode(',', $num_pidizd);
		$floorNumbers = explode(',', $skyscraper['poverxiv']);
		$tplresult .= $form_add.generate_building_table($cellNumbers, $floorNumbers, $skyscraper,$index,$kvarturu,count($sql_kv));
		if(count($sql_kv)>0){
			$tplresult .= '<table class="resp-tab bulding"><thead><tr>
			<th width="5%">
				<a class="sort_s" href="/?do=house&act=view&id='.$id.'&sort=kv&types='.(isset($sort1)?$sort1:'asc').'">'.$lang['kvartura'].'<i class="fi fi-rr-caret-'.(isset($sort1)?'up':'down').'"></i></a>
			</th>
			<th width="5%">
				<a class="sort_s" href="/?do=house&act=view&id='.$id.'&sort=status&types='.(isset($sort2)?$sort2:'asc').'">'.$lang['status'].'<i class="fi fi-rr-caret-'.(isset($sort2)?'up':'down').'"></i></a>
			</th>
			<th width="12%">
				<a class="sort_s" href="/?do=house&act=view&id='.$id.'&sort=onu&types='.(isset($sort4)?$sort4:'asc').'">Mac_Sn<i class="fi fi-rr-caret-'.(isset($sort4)?'up':'down').'"></i></a>
			</th>
			<th width="8%">
				<a class="sort_s" href="/?do=house&act=view&id='.$id.'&sort=signal&types='.(isset($sort3)?$sort3:'asc').'">'.$lang['signal'].'<i class="fi fi-rr-caret-'.(isset($sort3)?'up':'down').'"></i></a>
			</th>	
			<th width="10%">
				<span class="inf_status">
					<span class="tim1">'.$lang['offline'].'</span>
					<span class="tim2">'.$lang['online'].'</span>
				</span>
			</th>
			<th width="10%">'.$lang['device'].'</th>';
			if (isset($confPMon['FIBERMAP']) && !empty($confPMon['FIBERMAP']) && $confPMon['FIBERMAP'] == 1) {
				$tplresult .= '<th width="10%">PON</th>';
			}
			$tplresult .= '			
			'.($config_uid ? '<th width="5%">'.$lang['uidbilling'].'</th>':'').'
			<th></th>'.(($access->get('house_edit'))?'<th width="5%"></th>':'').'</tr></thead><tbody>';
			$select = 'mac,sn,status,type,inface,reason,olt,comments,online,offline';
			foreach ($sql_kv as $kvid => $onu) {
				$getonu = $db->Fast('onus',$select,['idonu' => $onu['idonu']]);
				$onukey = (!empty($getonu['mac'])?$getonu['mac']:(!empty($getonu['sn'])?$getonu['sn']:null));
				$datatemponu = $db->Fast('onusdata','*',['onukey'=>$onukey]);

				if($getonu['status']==1){
					$get_status = '<img class="house_status" src="../style/img/online.png">';
				}else{
					$get_status = reason_onu($getonu['status'],$getonu['reason']);
				}				
				$tplresult .= '<tr>
					<td data-id="'.$onu['idonu'].'">'.$onu['kvnomer'].'</td>
					<td class="status">'.$get_status.'</td>
					<td class="td_url">
					<a href="/?do=onu&id='.$onu['idonu'].'" '.($onu['status']==2?'class="colorgrey"':'').'>'.$onu['onukey'].'</a></td>
				<td>'.($getonu['status']==2 ? 'N/A':signalTerminal($onu['signal'])).'</td>
				<td>'.($getonu['status']==2 ? 
					'<span class="off_">'.aftertime($getonu['offline']).'</span>' : 
					'<span class="signal3">'.aftertime($getonu['online'])).'</span>
				</td>
				<td class="td_url"><a href="/?do=detail&act=olt&id='.$getonu['olt'].'">'.$switch[$getonu['olt']]['place'].'</a></td>';
				if (isset($confPMon['FIBERMAP']) && !empty($confPMon['FIBERMAP']) && $confPMon['FIBERMAP'] == 1) {
					$tplresult .= '<td class="td_url pon_building">';
					if(isset($datatemponu['ponelement']) && is_valid_id($datatemponu['ponelement'])){
						$ponelement = $db->Fast('ponelement','*',['id'=>$datatemponu['ponelement']]);
						$tplresult .= '<a href="/?do=fiber&act=viewtree&id='.$datatemponu['pontree'].'">
					<div class="'.($ponelement['status']==1 ? 'on' : 'off').'pon"></div>'.$ponelement['name'].'<img class="link_" src="../style/img/chevrons.png"></a>';
					}
					$tplresult .= '</td>';
					
				}
				$tplresult .= ($config_uid ? '<td >'.$onu['dogovir'].'</td>':'').'
				<td class="text_left flex-down">';
				if(isset($onu['comments']) && !empty($onu['comments'])){
					$tplresult .= '<span class="comments">'.$onu['comments'].'</span>';
				}				
				if(!empty($datatemponu['tag'])){
					$tplresult .= '<span class="tags">'.$datatemponu['tag'].'</span>';
				}
				if (isset($confPMon['PMON_BILLING']) && !empty($confPMon['PMON_BILLING']) && $confPMon['PMON_BILLING'] == 1) {
					$pmon_billing = PmonBillingData($getonu);
					$result_billing = PmonBillingTemplate($pmon_billing);
				}
				$tplresult .= $result_billing;
				$tplresult .= '</td>';
				if($access->get('house_edit')){
				$tplresult .= '<td><a class="panel_house rr_1" href="/?do=house&act=delkv&id='.$id.'&kv='.$onu['id'].'">X</a></td>';
				}
				$tplresult .= '</tr>';
			}
			$tplresult .= '</table>';
		}
	break;
	case 'delet':	
		if($access->get('house_edit')){
			$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
			if(isset($id)){
				$db->SQLdelete('skyscraper_kv',['skyscraperid' => $id]);
				$db->SQLdelete('skyscraper',['id' => $id]);
			}
		}
		$go->go('/?do=house');
		exit;		
	break;
	case 'delkv':
		if($access->get('house_edit')){	
			$kv = isset($_GET['kv']) ? Clean::int($_GET['kv']): null;
			$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
			if(isset($id) && isset($kv)){
				$db->SQLdelete('skyscraper_kv',['skyscraperid' => $id,'id' => $kv]);
				$go->go('/?do=house&act=view&id='.$id);
			}
		}
		$go->go('/?do=house');
	break;	
	default:
		$sqlorderby = '';
		$result  = '';
		$locationid = isset($_GET['lc']) ? Clean::int($_GET['lc']): null;
		$streetid = isset($_GET['st']) ? Clean::int($_GET['st']): null;
		$sort = isset($_GET['sort']) ? Clean::text($_GET['sort']): null;
		$types = isset($_GET['types']) ? Clean::text($_GET['types']): null;
		if(isset($sort) && isset($types)){
			$sql_sort = ($types=='asc'?' ASC':' DESC');
			if($sort=='offline'){
				$orderby .='`offline`';
				$sort1 = ($types=='asc'?'desc':'asc');
			}elseif($sort=='online'){
				$orderby .='`online`';
				$sort2 = ($types=='asc'?'desc':'asc');	
			}elseif($sort=='onus'){
				$orderby .='`onus`';
				$sort3 = ($types=='asc'?'desc':'asc');	
			}elseif($sort=='name'){
				$orderby .='`name`';
				$sort4 = ($types=='asc'?'desc':'asc');	
			}				
			if(isset($orderby)){
				$sqlorderby = 'ORDER BY '.$orderby.$sql_sort;
			}
		}
		$where = [];
		if(isset($streetid) || isset($locationid)){
			if($locationid){
				$where[] = 'locationid = '.$locationid;
			}            
			if($streetid){
				$where[] = 'streetid = '.$streetid;
			}
			$sql_where = 'WHERE ' . implode(' AND ', $where);
		} else {
			$sql_where = '';
		}
		$metatags = array(
			'title' => $lang['ponhouse'],
			'description' => $lang['ponhouse'],
			'page' => 'house'
		);
		$speedbar .='
			<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['list_house'].'</span>
		';
		// house
		$sql_skyscraper = $db->SimpleWhile("SELECT * FROM skyscraper ".$sql_where." ".$sqlorderby);
		$array_house = [];
		if(isset($sql_skyscraper) && count($sql_skyscraper)>0){
			foreach($sql_skyscraper as $sk){
				$array_house[$sk['locationid']]['h'][$sk['streetid']]['s'][$sk['id']] = $sk;
			}
		}
		$sql_skyscraper_str = $db->SimpleWhile("SELECT * FROM location_street");
		$array_house_str = [];
		$array_house_str_name = [];
		if(isset($sql_skyscraper_str) && count($sql_skyscraper_str)>0){
			foreach($sql_skyscraper_str as $st){
				$array_house_str[$st['locationid']][$st['id']]['n'] = $st['name'];
				$array_house_str_name[$st['id']]['n'] = $st['name'];
			}
		}
		$sql_location = $db->SimpleWhile("SELECT * FROM location");
		if(isset($sql_location) && count($sql_location)>0){
			$result .='<table class="resp-tab skyscraper"><thead><tr>
			<th width="5%">'.$lang['status'].'</th>
			<th><a class="sort_s" href="/?do=house&sort=name&types='.(isset($sort4)?$sort4:'asc').'">'.$lang['name'].'<i class="fi fi-rr-caret-'.(isset($sort4)?'up':'down').'"></i></th>
			<th width="10%"><a class="sort_s" href="/?do=house&sort=onus&types='.(isset($sort3)?$sort3:'asc').'">ONT<i class="fi fi-rr-caret-'.(isset($sort3)?'up':'down').'"></i></th>
			<th width="10%"><a class="sort_s" href="/?do=house&sort=online&types='.(isset($sort2)?$sort2:'asc').'">ONT '.$lang['online'].'<i class="fi fi-rr-caret-'.(isset($sort2)?'up':'down').'"></i></th>
			<th width="10%"><a class="sort_s" href="/?do=house&sort=offline&types='.(isset($sort1)?$sort1:'asc').'">ONT '.$lang['offline'].'<i class="fi fi-rr-caret-'.(isset($sort1)?'up':'down').'"></i></th>';
			if($access->get('house_edit')){
				$result .='<th width="10%"></th>';
			}
			$result .='</tr></thead><tbody>';
			foreach($sql_location as $location){				
				if(isset($array_house[$location['id']]['h']) && count($array_house[$location['id']]['h'])>0){
				$result .='<tr>
						<td colspan="8" class="h_location">
							<a href="/?do=house&lc='.$location['id'].'" class="link"><font size="2" color="#ccc">['.$lang['location'].']</font> '.$location['name'].'</a>
							<a href="/?do=map&location='.$location['id'].'" class="link"><font size="2" color="#ccc">['.$lang['map'].']</font> </a>
						</td>
					</tr>';
					foreach($array_house[$location['id']]['h'] as $str => $street){
						$result .='<tr>
							<td colspan="8" class="h_location_st">
								<a href="/?do=house&st='.$str.'" class="link"><font color="grey">[адреса]</font> '.$array_house_str_name[$str]['n'].'</a>
							</td>
						</tr>';
						foreach($street['s'] as $houseids => $house){
							
							$off = (isset($house['offline'])?$house['offline']:0);
							$on = (isset($house['online'])?$house['online']:0);
							$count = (isset($house['onus'])?$house['onus']:0);
							$result .='
							<tr>	
								<td class="status_house">
									<a href="/?do=house&act=view&id='.$house['id'].'">
										<img src="../style/img/house_'.($count==$off && $off>0?'down':'up').'.png">
									</a>
								</td>				
								<td class="nomer '.($count==$off && $off>0?'alarm':'').'">
									<a class="link" href="/?do=house&act=view&id='.$house['id'].'">'.$house['name'].'</a> № '.$house['nomer'].'';
									if(isset($house['note'])){
										$result .='<div class="house_note">'.$house['note'].'</div>';
									}
									
								$result .='</td>
								<td>
									<font color="#32abdf">'.$count.'</font>
								</td>
								<td>
									<font color="#46ba39">'.$on.'</font>
								</td>
								<td>
									<font color=red>'.$off.'</font>
								</td>';
								$result .='
									<td>';
								if($access->get('house_edit') && $count==0){
									$result .='<a class="panel_house rr_1" href="/?do=house&act=delet&id='.$house['id'].'">'.$lang['delet'].'</a>';
								}
								$result .='
									</td>';
							$result .='</tr>';	
						}					
					}
				}
			}
			$result .='</tbody></table>';
		}else{
			
		}
		// <script>gethouse(\'list\');</script>
		$tplresult .= '<div id="skyscraper">'.$result.'</div>';
}
	
$result ='<div id="onu-speedbar">'.$speedbar.'</div>'.$tplresult.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>