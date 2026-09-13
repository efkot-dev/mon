<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$listgroup = '';
$listlocation = '';
$result .= '
<div class="card">
	<form action="'.$url_video.'" method="post" id="form_add">
		<input name="act" type="hidden" value="save">';
$monitor  = '
	<select class="select" name="monitor">
		<option value="yes">'.$lang['monitor_on'].'</option>
		<option value="no">'.$lang['monitor_off'].'</option>
	</select>';		
$typedevice  = '
	<select class="select" name="oidid">
		<option value="1">Hikvision NVR</option>
		<option value="2">Hikvision IP camera</option>
		<option value="3">Dahua NVR</option>
		<option value="4">Dahua IP camera</option>
	</select>';
$result .= formpage(['img'=>'addconnect.png','name'=>'Device','descr'=>'Select ','pole'=>$typedevice]);
$result .= formpage(['img'=>'addconnect.png','name'=>$lang['monitor_on'],'descr'=>$lang['monitor_device'],'pole'=>$monitor]);
$result .= formpage(['img'=>'addconnect.png','name'=>$lang['ip'],'descr'=>$lang['ipdescr'],'pole'=>'<input style="width:33%;" name="netip" class="input1" type="text">']);
$result .= formpage(['img'=>'addconnect.png','name'=>'Community','descr'=>'Snmp community PING3','pole'=>'<input style="width:33%;" name="snmpro" class="input1" type="text">']);
$result .= formpage(['img'=>'addconnect.png','name'=>$lang['oid_gpon_name'],'descr'=>$lang['oid_gpon_name_desc'],'pole'=>'<input style="width:99%;" name="name" class="input1" type="text">']);
$group = $db->SimpleWhile("SELECT * FROM groups WHERE group_types = 3");
if(is_array($group)){
	foreach($group as $gr){
		$listgroup .= '<option value="'.$gr['id'].'">'.$gr['name'].'</option>';
	}
	$result .= formpage(['img'=>'folders.png','name'=>$lang['group'],'descr'=>$lang['title_group'],'pole'=>'<select class="select" name="group" id="group"><option value="0"></option>'.$listgroup.'</select>']);
}
$location = getListLocations();
if(is_array($location)){
	foreach($location as $loc){
		$listlocation .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
	}
	$result .= formpage(['img'=>'m6.png','name'=>$lang['location'],'descr'=>$lang['getlocation'],'pole'=>'<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select>']);
}
$result .= '
	</form>
	</div>
	<div class="polebtn">
		<button type="submit" form="form_add" value="submit">'.$lang['save'].'</button>
	</div>

';
$content = '';
$speedbar = '';
$metatags = array(
	'title'=>'Відеоспостереження',
	'description'=>'Відеоспостереження',
	'page'=>'board_main'
);
$speedbar .='
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<a class="brmhref" href="/?do=surveillance"><i class="fi fi-rr-angle-left"></i>Відеоспостереження</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Новий пристрій</span>
';
$content .= '
<div id="onu-speedbar">
	'.$speedbar.'
</div>
'.$result;
?>