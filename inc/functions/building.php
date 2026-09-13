<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
function generate_building_table($cellNumbers, $floorNumbers, $house, $index, $kvarturu, $countonu){
	global $lang, $access, $db;
    $width = count($cellNumbers);
    $height = count($floorNumbers) - 1; 
	$checker_onu = '';
    $table_html = '<table class="css_house">';
	if(isset($countonu) && $countonu>0 ){
		$checker_onu .= '<img onclick="collectDataIds('.$house['id'].',\''.$lang['checker_onu'].'\')" src="../style/img/refresh_house.png">';
	}
    $table_html .= '<tr><td class="house_nomer_pidizd first" style="width: 5%;">'.$checker_onu.'</td>';
	$sum = 20.1/$width;
	$calc_width = (100/$width)-$sum;
    for ($i = 1; $i <= $width; $i++) {
        $table_html .= '<td class="house_nomer_pidizd" style="width:'.$calc_width.'%;">'.$lang['house_pidizd'].' ' . $i . '</td>';
    }
	$house_comm = list_house_comm($house['id']);
    //$table_html .= '<td class="house_nomer_pidizd" style="width: 15%;">Додатково</td></tr>';    
    $table_html .= '</tr>';  
    for ($floor = $height; $floor >= 0; $floor--){
		if(isset($floorNumbers[$floor]) && intval($floorNumbers[$floor]) || $floorNumbers[$floor]==0){
			if($floorNumbers[$floor]==0){
				$named = $lang['house_pidval'];
			}else{
				$named = $lang['house_poverx'].' ' . $floorNumbers[$floor];
			}
			$table_html .= '<tr class="line"><td class="house_nomer_poverx" >'.$named.'</td>';
			for ($entrance = 1; $entrance <= $width; $entrance++) {
				$cellIndex = $entrance + (($height - $floor) * $width);
				$indexkvartura = ($floor+1).$cellIndex;
				$table_html .= '<td class="house_kvartura" id="kv_'.$indexkvartura.'_'.$cellIndex.'" '.(isset($index) && $index==$indexkvartura?'style="background: #ece6ac;"':'').'><div class="house_kvs"><div class="kv_list">';
				$table_html .= get_onu_house($indexkvartura,$kvarturu);	
				if(isset($house_comm[$cellIndex])){
					foreach($house_comm[$cellIndex] as $jid => $data_poverx){
						$table_html .= '<span class="house_comm" onclick="house_inf('.$jid.')">'.$data_poverx['img'].'</span>';
					}
				}
				$table_html .= '</div>';
				if($index!=$indexkvartura){
				$table_html .= '<a href="/?do=house&act=view&id='.$house['id'].'&index='.$indexkvartura.'" class="connect_kv"><img src="../style/img/min_add.png"></a>';
				$table_html .= '<span class="fob_house" data-popup-id="backup_panel" onclick="house_comm('.$house['id'].','.$cellIndex.')"><img id="comm-'.$cellIndex.'" src="../style/img/add-comment.png"></span>';
				}
				$table_html .= '</div></td>';
			}
			//$table_html .= '<td class="house_kvartura">аа</td></tr>';// ' . $floorNumbers[$floor] . '
			$table_html .= '</tr>';
		}
    }
    $table_html .= '<tr><td class="house_opis" colspan="' . ($width + 2) . '">';
	$table_html .= '<div class="blocker">';
	if(!empty($house['photo'])){
		$table_html .= '<div class="blocker_photo">';
		$table_html .= '<a href="/?do=thumb&type=photo&img=' . $house['photo'] . '&s=2" target="_blank">';
		$table_html .= '<img src="/?do=thumb&type=photo&img=' . $house['photo'] . '&s=1">';
		$table_html .= '</a>';
		$table_html .= '</div>';
	}
	$edit = '';
	if($access->get('house_edit')){
		$edit .= '<a href="#" onclick="connecthouse('.$house['id'].');"><img class="edit" src="../style/img/addswitch.png"></a>';
		$edit .= '<a href="#" onclick="edithouse('.$house['id'].');"><img class="edit" src="../style/img/edit.png"></a>';
	}
	if(!empty($house['lan'])){
		$gps = '<a href="/?do=house&act=geo&id='.$house['id'].'"><img class="edit" src="../style/img/nogps.png"></a>';
	}else{
		$gps = '<a href="/?do=house&act=geo&id='.$house['id'].'"><img class="edit" src="../style/img/gps.png"></a>';
	}
	$table_html .= '<div class="blocker_x">';
	$table_html .= '<h2>'.$house['name'].$edit.$gps.'</h2>';
	if(isset($house['note'])){
		$table_html .= '<div>'.$house['note'].'</div>';
	}
	$table_html .= '<h3>'.$house['locationname'].' '.$house['street'].' '.$house['nomer'].' </h3>';
	$table_html .= '<h4>ONU: '.(isset($countonu)?$countonu:0).'</h4>';
	$table_html .= '</div>';
	$table_html .= '<div class="blocker_device">';
	$sqldevice = $db->SimpleWhile("SELECT skyscraper_device.device_id, switch.id , switch.device, switch.place, switch.inf, switch.model, switch.img FROM skyscraper_device LEFT JOIN switch ON switch.id = skyscraper_device.device_id WHERE skyscraper_device.build_id = " . $house['id'] . "");
	if(isset($sqldevice) && count($sqldevice)>0){
		foreach($sqldevice as $id => $device){
			$table_html .= '
			<div class="house_device">
				<div class="device_photo"><img src="../style/device/'.$device['img'].'"></div>
				<div class="device_model">'.$device['inf'].' '.$device['model'].'</div>
				<div class="device_place"><a class="aswitch" href="/?do=detail&act='.$device['device'].'&id='.$device['id'].'">'.$device['place'].'</a></div>
			</div>
			';
		}		
	}
	$table_html .= '</div>';
	$table_html .= '</div>';
	$table_html .= '</td></tr>';   
    $table_html .= '</table>';    
    return $table_html;
}
function get_onu_house($indexkvartura,$kvarturu){
	$res = '';
	if(isset($kvarturu[$indexkvartura]['kv']) && count($kvarturu[$indexkvartura]['kv'])>0){
		foreach($kvarturu[$indexkvartura]['kv'] as $kvid => $kv){
		$res .= '<div class="onu_kv"><div class="signal"><div class="'.(isset($kv['status']) && $kv['status'] == 1?'online':'offline').'_vk"></div></div>
		<div class="nomers">'.$kv['kvnomer'].'</div></div>';
		}
	}
	return $res;
}
function gen_sequence_not($poverxiv){
	$sequence = '';
	for ($i = 1; $i <= $poverxiv; $i++) {
		$sequence .= $i;
		if ($i < $poverxiv) {
			$sequence .= ',';
		}
	}
    if (substr($sequence, -1) === ',') {
        $sequence = substr($sequence, 0, -1);
    }
    return $sequence;	
}
function gen_sequence($poverxiv){
    $sequence = '';
    for ($i = 0; $i < $poverxiv; $i++) {
        $sequence .= $i;
        if ($i < $poverxiv - 1) {
            $sequence .= ',';
        }
    }
    if (substr($sequence, -1) === ',') {
        $sequence = substr($sequence, 0, -1);
    }
    return $sequence;
}
function create_house($latitude, $longitude, $name, $id, $onus, $online, $offline, $photo, $data) {
    $photoUrl = $photo ? '/?do=thumb&type=photo&img=' . $photo . '&s=1' : "../style/img/nohouse.jpg";
    $blockonu = info_house($onus, $online, $offline);
    return "
    var marker = L.marker([$latitude,$longitude], {
        icon: L.divIcon({
            className: 'mapper',
            html: `$blockonu<div class=\"circle-zoom\" style=\"display:none;\">
                      <img src=\"$photoUrl\" style=\"width: 42px; height: 42px; object-fit: cover; border-radius: 50%;\">
                   </div>`
        })
    }).bindPopup('<div id=\"popup-content-$id\" class=\"div-l\"><strong>Loading...</strong></div>')
      .on('click', function() {
          loadhouse('$id', '$name');
      }).addTo(map);
    markers.push(marker);
    ";
}
function info_house($onus, $online, $offline) {
    if ($onus <= 0) {
        return "";
	}
    $blockonu = "";
    if ($online > 0) {
        $blockonu .= "<span class=\"maponline\">$online</span>";
    }
    if ($offline > 0) {
        $class = $online > 0 ? "mapoff" : "mapoffs";
        $blockonu .= "<span class=\"$class\">$offline</span>";
    }
    $blockonu .= "<span class=\"mapstats\">$onus</span>";
    return $blockonu;
}
function generateJavaScript($id,$index) {
	global $lang, $confPMon;
	$config_uid = (!empty($confPMon['PON_HIGH_RISE_UID']) && $confPMon['PON_HIGH_RISE_UID'] == 1 ? true : false);
    $javascript = '
		<div class="nav-fiber p10" style="width:50%;">
			'.$lang['numer_kv'].':
			<br><input type="text" class="input1 f_css" id="kvnomer" name="kvnomer" required autocomplete="off">
			'.($config_uid?''.$lang['uidbilling'].':<br><input type="text" class="input1 f_css" id="dogovir" name="dogovir" required autocomplete="off">':'').'
			Тип підключення:<br>
			<select class="select" name="types" id="types">
				<option value="0"></option>
				<option value="1">ONU</option>
				<option value="2">Port</option>
			</select>
			<div id="getonu"></div>
	</div>';
    $javascript .= "
        <script>
            $('#types').on('change', function() {
				var types = $(this).val();
				$.post(root + 'ajax/house.php',{act:'select',types:types,id:{$id},index:{$index}},function(response){
					$('#getonu').html(response);
				},'html');
            });
        </script>
    ";
    return $javascript;
}

?>