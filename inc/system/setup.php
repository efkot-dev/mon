<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$listgroup = '';
if(!$access->get('setupdevice')){
	$go->redirect('main');
}
$metatags = array('title'=>$lang['setup'],'description'=>$lang['setup'],'page'=>'setup');
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if(!$id){
	$go->redirect('main');	
}
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $id]);
$data_olt = $sql_olt->fetch(PDO::FETCH_ASSOC);
if(!$data_olt || !$access->get('dev'.$data_olt['id'])){
	$go->redirect('main');
}
$sql = "SELECT switch.id, switch.netip, switch.snmpro, switch.place, switch.oidid, oid.types, oid.oid FROM switch INNER JOIN oid ON switch.oidid = oid.oidid WHERE switch.id = :id AND oid.inf = 'health' AND oid.types = 'temp'";
$sql_oid = $pdo->prepare($sql);
$sql_oid->execute(['id' => $id]);
$data_oid = $sql_oid->fetch(PDO::FETCH_ASSOC);
$setup = '<form action="/?do=send" method="post" id="formadd">
	<input name="act" type="hidden" value="savesetup">
	<input name="id" type="hidden" value="'.$data_olt['id'].'">';
$group = getListGroup();
if(is_array($group)){
	foreach($group as $gr){
		$listgroup .= '<option value="'.$gr['id'].'" '.($data_olt['groups']==$gr['id']?' selected':'').'>'.$gr['name'].'</option>';
	}
	$setup .= formpage([
		'img'=>'folders.png',
		'name'=>$lang['group'],
		'descr'=>$lang['title_group'],
		'pole'=>'<select class="select" name="group" id="group"><option value="0"></option>'.$listgroup.'</select>'
	]);
}
$listlocation = '';
$location = getListLocations();
if(is_array($location)){
	foreach($location as $loc){
		$listlocation .= '<option value="'.$loc['id'].'" '.($data_olt['location']==$loc['id']?' selected':'').'>'.$loc['name'].'</option>';
	}
	$setup .= formpage([
		'img'=>'m6.png',
		'name'=>$lang['location'],
		'descr'=>$lang['getlocation'],
		'pole'=>'<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select>'
	]);
}
$setup .= formpage([
	'img'=>'addconnect.png',
	'name'=>$lang['inputnamedevice'],
	'descr'=>$lang['curname'],
	'pole'=>'<input type="text" name="place" class="input1" value="'.$data_olt['place'].'">'
]);
$monitor ='<select class="select" name="monitor" id="format">
	<option value="no"></option>
	<option value="yes" '.($data_olt['monitor']=='yes'?'selected':'').'>'.($data_olt['monitor']=='yes'?$lang['ons']:$lang['on']).'</option>
	<option value="no" '.($data_olt['monitor']=='no'?'selected':'').'>'.($data_olt['monitor']=='no'?$lang['offs']:$lang['off']).'</option>
	</select>';
$setup .= formpage([
	'img'=>'no-internet.png',
	'name'=>$lang['edit_monitor'],
	'descr'=>$lang['edit_monitor_descr'],
	'pole'=>$monitor
]);
$connect ='<select class="select" name="connect" id="format">
	<option value="no"></option>
	<option value="yes" '.($data_olt['connect']=='yes'?'selected':'').'>'.($data_olt['connect']=='yes'?$lang['ons']:$lang['on']).'</option>
	<option value="no" '.($data_olt['connect']=='no'?'selected':'').'>'.($data_olt['connect']=='no'?$lang['offs']:$lang['off']).'</option>
	</select>';
$setup .= formpage([
	'img'=>'servers.png',
	'name'=>$lang['connects'],
	'descr'=>$lang['connectsports'],
	'pole'=>$connect
]);
$gallery ='<select class="select" name="gallery" id="format">
	<option value="no"></option>
	<option value="yes" '.($data_olt['gallery']=='yes'?'selected':'').'>'.($data_olt['gallery']=='yes'?$lang['ons']:$lang['on']).'</option>
	<option value="no" '.($data_olt['gallery']=='no'?'selected':'').'>'.($data_olt['gallery']=='no'?$lang['offs']:$lang['off']).'</option>
	</select>';
$setup .= formpage([
	'img'=>'photo-gallery.png',
	'name'=>$lang['photo'],
	'descr'=>$lang['photodescr'],
	'pole'=>$gallery
]);
$setup .= formpage([
	'img'=>'img1.png',
	'name'=>$lang['ip'],
	'descr'=>$lang['ipdescr'],
	'pole'=>'<input type="text" name="netip" class="input1" value="'.$data_olt['netip'].'">'
]);
$setup .= formpage([
	'img'=>'img1.png',
	'name'=>$lang['mac'],
	'descr'=>$lang['ipdescr'],
	'pole'=>'<input type="text" name="mac" class="input1" value="'.$data_olt['mac'].'">'
]);
$setup .= formpage([
	'img'=>'m5.png',
	'name'=>$lang['sn'],
	'descr'=>$lang['supporttmc'],
	'pole'=>'<input type="text" name="sn" class="input1" value="'.$data_olt['sn'].'">'
]);
$setup .= formpage([
	'img'=>'m5.png',
	'name'=>'CURL Pool',
	'descr'=>'Number of threads when collecting information',
	'pole'=>'<input type="number" name="curl_pool" class="input1" min="1" max="10" style="width: 50px;" value="'.$data_olt['curl_pool'].'">'
]);
if(!empty($data_oid['oid'])){
	$setup .= formpage([
		'img'=>'err-inf.png',
		'name'=>'Critical temprature',
		'descr'=>'Monitor critical temperatura',
		'pole'=>'<input type="number" name="temp_cpu" class="input1" min="35" max="100" style="width: 50px;" value="'.($data_olt['temp_cpu'] ?? 60).'">'
	]);
}
$setup .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form>';
$tpl->load_template('setup/main.tpl');
$tpl->set('{langsetup}',$lang['setup']);
$tpl->set('{id}',$id);
$tpl->set('{place}',$data_olt['place']);
$tpl->set('{langlist}',$lang['alldevice']);
$tpl->set('{result}',$setup);
$tpl->compile('content');
$tpl->clear();
?>
