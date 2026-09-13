<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = (isset($_POST['id']) ? (int)$_POST['id'] : null);
$ping3 = $db->Fast('mon_ping3','*',['id'=>$id]);
if(!empty($ping3['id'])){
	$listgroup = '';
	$listlocation = '';
	okno_title($lang['edit']);
	$result .= '<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="updateping3"><input name="id" type="hidden" value="'.$id.'">';
	$monitor  = '<select class="select" name="monitor"><option value="yes" '.($ping3['monitor']=='yes'?'selected':'').'>'.$lang['monitor_on'].'</option><option value="no" '.($ping3['monitor']=='no'?'selected':'').'>'.$lang['monitor_off'].'</option></select>';
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['monitor_on'],'descr'=>$lang['monitor_device'],'pole'=>$monitor]);
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['ip'],'descr'=>$lang['ipdescr'],'pole'=>'<input style="width:33%;" name="netip" class="input1" type="text" value="'.$ping3['netip'].'">']);
	$result .= formpage(['img'=>'addconnect.png','name'=>'Community','descr'=>'Snmp community PING3','pole'=>'<input style="width:33%;" name="snmpro" class="input1" type="text" value="'.$ping3['snmpro'].'">']);
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['oid_gpon_name'],'descr'=>$lang['oid_gpon_name_desc'],'pole'=>'<input style="width:99%;" name="name" class="input1" type="text"  value="'.$ping3['name'].'">']);
	$typebattery = '<select class="select" name="typebattery">
		<option value="12" '.($ping3['typebattery']==12?'selected':'').'>12</option>
		<option value="24"'.($ping3['typebattery']==24?'selected':'').'>24</option>
		<option value="48"'.($ping3['typebattery']==48?'selected':'').'>48</option>
		<option value="72"'.($ping3['typebattery']==72?'selected':'').'>72</option>
		<option value="72"'.($ping3['typebattery']==76?'selected':'').'>76</option>
		</select>';
	$typean = '<select class="select" name="channel">
		<option value="1" '.($ping3['channel']==1?'selected':'').'>AN1</option>
		<option value="2" '.($ping3['channel']==2?'selected':'').'>AN2</option>
		<option value="3" '.($ping3['channel']==3?'selected':'').'>AN3</option>
		<option value="4" '.($ping3['channel']==4?'selected':'').'>AN4</option>
		</select>';
	$result .= formpage(['img'=>'addconnect.png','name'=>'AN','descr'=>$lang['channel_ping3'],'pole'=>$typean]);
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['types_battery'],'descr'=>$lang['types_battery_descr'],'pole'=>$typebattery]);
	$energystatus  = '<select class="select" name="energystatus"><option value="yes" '.($ping3['energystatus']=='yes'?'selected':'').'>'.$lang['monitor_on'].'</option><option value="no" '.($ping3['energystatus']=='no'?'selected':'').'>'.$lang['monitor_off'].'</option></select>';
	$result .= formpage(['img'=>'addconnect.png','name'=>$lang['types_220'],'descr'=>$lang['types_220i'],'pole'=>$energystatus]);
	$group = $db->SimpleWhile("SELECT * FROM groups WHERE group_types = '3'");
	if(is_array($group)){
		foreach($group as $gr){
			$listgroup .= '<option value="'.$gr['id'].'" '.($ping3['groups']==$gr['id']?'selected':'').'>'.$gr['name'].'</option>';
		}
	$result .= formpage(['img'=>'folders.png','name'=>$lang['group'],'descr'=>$lang['title_group'],'pole'=>'<select class="select" name="group" id="group"><option value="0"></option>'.$listgroup.'</select>']);
	}
	$location = getListLocations();
	if(is_array($location)){
		foreach($location as $loc){
			$listlocation .= '<option value="'.$loc['id'].'" '.($ping3['locationid']==$loc['id']?'selected':'').'>'.$loc['name'].'</option>';
		}
	$result .= formpage(['img'=>'m6.png','name'=>$lang['location'],'descr'=>$lang['getlocation'],'pole'=>'<select class="select" mame="location" id="location"><option value="0"></option>'.$listlocation.'</select>']);
	}
	/*
	$result .= '<div class="batterystatus">
		<div class="batblock"><span class="col0">0%</span><span><input name="status0" type="text"></span></div>		
		<div class="batblock"><span class="col20">20%</span><span><input name="status20" type="text"></span></div>		
		<div class="batblock"><span class="col40">40%</span><span><input name="status40" type="text"></span></div>		
		<div class="batblock"><span class="col60">60%</span><span><input name="status60" type="text"></span></div>		
		<div class="batblock"><span class="col80">80%</span><span><input name="status80" type="text"></span></div>		
		<div class="batblock"><span class="col100">100%</span><span><input name="status100" type="text"></span></div>
	</div>';
	*/
	$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form>';
	echo $result;
	okno_end();	
}
?>