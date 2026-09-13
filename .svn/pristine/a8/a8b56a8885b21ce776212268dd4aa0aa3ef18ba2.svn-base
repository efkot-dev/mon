<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$speedbar = '';
$result = '';
$types = isset($_GET['types'])?Clean::text($_GET['types']) : null;
if(isset($act) && $act=='add'){
	$metatags = array('title'=>$lang['monitoringip'],'description'=>$lang['monitoringip'],'page'=>'monitoringip');
	$speedbar = '<a class="brmhref" href="/?do=monitorip"><i class="fi fi-rr-list"></i>'.$lang['monitoringip'].'</a>';				
	$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['newip'].'</span>';
	$monitor  = '<select class="select" name="monitor"><option value="yes">'.$lang['monitor_on'].'</option><option value="no">'.$lang['monitor_off'].'</option></select>';
	$result .= '<div class="card" style="margin: 0;"><form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="savemonitorip">';
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['monitor_on'],'descr'=>$lang['monitor_device'],'pole'=>$monitor]);
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['ip'],'descr'=>$lang['ipdescr'],'pole'=>'<input style="width:33%;" name="ip" class="input1" type="text">']);
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['oid_gpon_name'],'descr'=>$lang['oid_gpon_name_desc'],'pole'=>'<input style="width:99%;" name="name" class="input1" type="text">']);
	$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form></div>';
}elseif(isset($act) && $act=='edit'){
		$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
	if(empty($id)){
		$go->go('/?do=monitorip');		
	}
	$data_ip = $db->Fast('monitor_ip','*',['id'=>$id]);
	if(!empty($data_ip['id'])){
		$metatags = array('title'=>$lang['monitoringip'],'description'=>$lang['monitoringip'],'page'=>'monitoringip');
		$speedbar = '<a class="brmhref" href="/?do=monitorip"><i class="fi fi-rr-list"></i>'.$lang['monitoringip'].'</a>';				
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Змінити паметри ІР '.$data_ip['name'].'</span>';
		$monitor  = '<select class="select" name="monitor"><option value="yes">'.$lang['monitor_on'].'</option><option value="no">'.$lang['monitor_off'].'</option></select>';
		$result .= '<div class="card" style="margin: 0;"><form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="updatemonitorip"><input name="id" class="input1" type="hidden"  value="'.$data_ip['id'].'">';
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['monitor_on'],'descr'=>$lang['monitor_device'],'pole'=>$monitor]);
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['ip'],'descr'=>$lang['ipdescr'],'pole'=>'<input style="width:33%;" name="ip" class="input1" type="text" value="'.$data_ip['ip'].'">']);
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['oid_gpon_name'],'descr'=>$lang['oid_gpon_name_desc'],'pole'=>'<input style="width:99%;" name="name" class="input1" type="text"  value="'.$data_ip['name'].'">']);
		$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['update'].'</button></form></div>';	
	}else{
		$go->go('/?do=monitorip');	
		exit;		
	}
}elseif(isset($act) && $act=='delet'){
	$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
	if(empty($id)){
		$go->go('/?do=monitorip');		
	}
	$data_ip = $db->Fast('monitor_ip','*',['id'=>$id]);
	if(!empty($data_ip['id'])){
		$db->SQLdelete('monitor_ip',['id' => $data_ip['id']]);
		$db->SQLdelete('monitor_ip_log',['ip_id' => $data_ip['id']]);
	}	
	$go->go('/?do=monitorip');	
	exit;	
}elseif(isset($act) && $act=='view'){
	$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
	if(empty($id)){
		$go->go('/?do=monitorip');		
	}
	$data_ip = $db->Fast('monitor_ip','*',['id'=>$id]);
	if(empty($data_ip['id'])){
		$go->go('/?do=monitorip');			
	}
	$speedbar = '<a class="brmhref" href="/?do=monitorip"><i class="fi fi-rr-list"></i>'.$lang['monitoringip'].'</a>';		
	$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$data_ip['name'].'</span>';
	$gpslan = (!empty($data_ip['lan'])?$data_ip['lan']:$config['geo_lan']);
	$gpslon = (!empty($data_ip['lon'])?$data_ip['lon']:$config['geo_lon']);
	$zoom = '15';
	$markers = '';
	$mapper = getMap();
	if($types=='addmaper'){
$markers .= <<<HTML
var popup = L.popup();	
function onMapClick(e) {
var lat = e.latlng.lat.toFixed(6);
var lon = e.latlng.lng.toFixed(6);
popup
.setLatLng(e.latlng)
.setContent('<b>{$lang['add_geo_base']}</b><br><input id="lan" name="lan" type="hidden" value="' + lat + '"><input name="lon" id="lon" type="hidden" value="' + lon + '"><span class="koomap"><b>{$lang['geo']}</b>: ' + lat + ' ' + lon + '</span><br><button type="submit" class="cssadd" onclick="addmapperip({$id})">{$lang['save']}</button>')
.openOn(map);
}
map.on('click', onMapClick);
HTML;
	}elseif($types=='del'){
		$markers .= '';		
	}else{
		$info_ip = "<b>".$data_ip['name']."</b><br>".$data_ip['ip']."<br>".(isset($data_ip['status']) && $data_ip['status']==1 ? "<b>".$lang['online']."</b>: ".$data_ip['online']:"<b>".$lang['offline']."</b>: ".$data_ip['offline']);
		$markers .= "
		var ip_".$data_ip['id']." = L.marker([".$data_ip['lan'].",".$data_ip['lon']."],{
            icon: L.divIcon({
                className: 'mapper',
                html: '<div class=\"mappericon\"><img src=\"../style/img/ip_".$data_ip['status'].".png\"></div>'
            })
        }).bindTooltip('".$info_ip."').addTo(map);		
		";
	}
	$sqltempip = $db->SimpleWhile("SELECT * FROM monitor_ip");
	if(isset($sqltempip) && count($sqltempip) > 0){
		foreach($sqltempip as $ipid => $dataip){
		if(!empty($dataip['lon'])){
			$info_ip = "<b>".$dataip['name']."</b><br>".$dataip['ip']."<br>".(isset($dataip['status']) && $dataip['status']==1 ? "<b>".$lang['online']."</b>: ".$dataip['online']:"<b>".$lang['offline']."</b>: ".$data_ip['offline']);
			$markers .= "
			var ip_".$dataip['id']." = L.marker([".$dataip['lan'].",".$dataip['lon']."],{
				icon: L.divIcon({
					className: 'mapper',
					html: '<div class=\"mappericon\"><img src=\"../style/img/ip_".$dataip['status'].".png\"></div>'
				})
			}).bindTooltip('".$info_ip."').addTo(map);		
			";	
		}
		}		
	}
	$results = '<link rel="stylesheet" href="../style/map/leaflet.css" /><script src="../style/map/leaflet.js"></script><script src="../style/map/mymarker.js"></script>';    
	$results .= '<canvas id="pingChart" width="800px" height="200px"></canvas><script>    
    async function drawPingChart() {
        const pingData = await getPingData('.$id.');        
        var ctx = document.getElementById(\'pingChart\').getContext(\'2d\');
        var chart = new Chart(ctx, {type: \'bar\',
		data: {
        labels: Array.from(Array(pingData.length).keys()),
        datasets: [{
            label: \'Пінг '.$data_ip['ip'].'\',
            data: pingData,
            backgroundColor: \'red\', 
            borderColor: \'#222\', 
            borderWidth: 0 
        }]
    }
        });
    }
    drawPingChart();</script>';	

	$mapjs = '<script>
	var lat = "'.$gpslan.'"; 
	var lon = "'.$gpslon.'";
	var map = L.map("mapperip");
	map.setView([lat, lon], '.$zoom.');
	'.$mapper.$markers.'
	</script>';	
	$editor = '<div>';
	$editor .= '<a class="ping_edit" href="/?do=monitorip&act=edit&id='.$id.'">'.$lang['setups'].'</a>';
	$editor .= '<a class="ping_del" href="/?do=monitorip&act=delet&id='.$id.'">'.$lang['delet'].'</a>';
	$editor .= '</div>';	
	$editor .= '<div>';
	$editor .= '<a class="mapp_edit" href="/?do=monitorip&act=view&id='.$id.'&types=addmaper">'.(isset($data_ip['lan']) ? $lang['editmapper'] : $lang['addmapper']).'</a>';
	$editor .= '</div>';
	$result .= '<div class="box_monitor_ip"><div class="information_ip"><div class="more_info"><h1>'.$data_ip['name'].'</h1><h2>'.$data_ip['ip'].'</h2>'.$editor.'</div></div><div class="information_charts m20b mobile">'.$results.'</div></div><div id="mapperip" class="box_map" style="width: 100%; height: 500px;"></div>'.$mapjs;
}else{
	$orderby = 'ORDER BY CAST(timer AS UNSIGNED) DESC';
	$where = '';
	$sqltempip = $db->SimpleWhile("SELECT * FROM monitor_ip $where $orderby");
	$result .= '<table class="resp-tab"><thead><tr><th width="5%">'.$lang['status'].'</th><th width="10%">'.$lang['log_monitor'].'</th><th width="5%">Time</th><th width="10%">IP</th><th>'.$lang['name'].'</th><th width="15%">'.$lang['pmonchecker'].'</th><th width="15%">'.$lang['added'].'</th></tr></thead><tbody>';
	if(isset($sqltempip) && count($sqltempip) > 0){
		foreach($sqltempip as $ipid => $rem){
			$status = ($rem['status'] == 1) ? 1 : 3;
			$timer = $status == 1 ? $rem['timer'] : 'n/a';
			if ($status == 1) {
				if ($rem['timer'] < 3.00) {
					$color = '#49b0ff';
				} elseif ($rem['timer'] >= 3.00 && $rem['timer'] <= 6.00) {
					$color = 'orange';
				} else {
					$color = 'red';
				}
			} else {
				$color = '';
			}
			$colorClass = ($status == 1 && isset($color)) ? '<font color="'.$color.'">'.$timer.'</font>' : $timer;
			$result .= '<tr>
			<td><span class="statusonu st_'.$status.'"</span></td>
			<td>'.($status==1?'<font color="#4CAF50">':'<font color="red">').''.($status==1?aftertime($rem['online']):aftertime($rem['offline'])).'</font></td>
			<td>'.$colorClass.'</td>
			<td class="td_url"><a href="/?do=monitorip&act=view&id='.$rem['id'].'">'.$rem['ip'].'</a></td>
			<td class="td_url text_1">'.$rem['name'].'</td>
			<td><font color="#4CAF50">'.aftertime($rem['updates']).'</font></td>
			<td><font color="#4CAF50">'.$rem['added'].'</font></td>
			</tr>';
		}
	}else{

	}
	$result .= '</table>';
	if($access->get('setupdevice')){
		$result .= '<div class="pole"><a href="/?do=monitorip&act=add" class="urlelelement">'.$lang['addedip'].'</a></div>';
	}
	$metatags = array('title'=>$lang['monitoringip'],'description'=>$lang['monitoringip'],'page'=>'monitoringip');
	$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['monitoringip'].'</span>';				
}
$results ='<div id="onu-speedbar">
	<a class="brmhref" href="/?do=main"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>'.$speedbar.'</div>
		<div style="margin: 0;">
			<div class="page-error">'.$result.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$results.'');
$tpl->compile('content');
$tpl->clear();
?>