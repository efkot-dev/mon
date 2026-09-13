<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if(isset($confPMon['IPMAN']) && !empty($confPMon['IPMAN']) && $confPMon['IPMAN']==1) {
$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;
$ip = isset($_POST['ip']) ? Clean::str($_POST['ip']): null;
if($act=='add'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if($id){
		$dataip = $db->Fast('ipblock','*',['id'=>$id]);
		okno_title($lang['addeds'].' '.$ip);
		echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="addipipman"><input name="id" type="hidden" value="'.$id.'"><input name="ip" type="hidden" value="'.$ip.'">';
		echo form(['name'=>$lang['group_block'],'descr'=>'','pole'=>'<b>'.$dataip['name'].'</b>']);	
		echo form(['name'=>$lang['ip'],'descr'=>'','pole'=>'<b>'.$ip.'</b>']);	
		echo form(['name'=>$lang['block'],'descr'=>'','pole'=>'<b>'.$dataip['ipblock'].'</b>']);	
		echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
		echo form(['name'=>'Vlan','descr'=>'','pole'=>'<input required name="vlan" class="input1" type="text">']);	
		$group = getIPManGroups();
		if(is_array($group) && count($group)>0){
			foreach($group as $gr){
				$listgroup .= '<option value="'.$gr['id'].'">'.$gr['name'].'</option>';
			}
			echo formpage(['name'=>$lang['group'],'pole'=>'<select class="select" name="group" id="group"><option value="0"></option>'.$listgroup.'</select>']);
		}	
		echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
		okno_end();	
	}
}elseif($act=='addvlan'){
	okno_title($lang['addvlanblockip']);
	echo'<form action="/?do=send" method="post" id="formadd" ><input name="act" type="hidden" value="saveipmanvlan">';
	echo form(['name'=>'Vlan','descr'=>'','pole'=>'<input required name="vlan" class="input1" style="width:60px;" type="text">']);	
	echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);
	echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
	okno_end();	
}elseif($act=='addblockipgroup'){
	okno_title($lang['addcolorblockip']);
	echo'<form action="/?do=send" method="post" id="formadd" ><input name="act" type="hidden" value="saveipmangroup">';
	echo form(['name'=>$lang['colorgroup'],'descr'=>'','pole'=>'<input required name="color" type="color">']);	
	echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
	echo form(['name'=>'','descr'=>'','pole'=>'<textarea name="note" class="input1" rows="7" style="height:100px;"></textarea>']);	
	echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
	okno_end();		
}elseif($act=='addblockip'){
	okno_title($lang['add_blockip']);
	echo'<form action="/?do=send" method="post" id="formadd" ><input name="act" type="hidden" value="saveipman">';
	echo form(['name'=>$lang['ipblock'],'descr'=>'','pole'=>'<input required name="ipblock" class="input1" type="text">']);	
	echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<input required name="name" class="input1" type="text">']);	
	echo form(['name'=>'','descr'=>'','pole'=>'<textarea name="note" class="input1" rows="7" style="height:100px;"></textarea>']);	
	echo'</form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
	okno_end();	
}elseif($act=='useduser'){
	$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;	
	if($id){
	$dataip = $db->Simple("SELECT * FROM ipaddress WHERE ip = '".$ip."' AND blockid = ".$id." LIMIT 1");
	if(!empty($dataip['id'])){
		$blockip = $db->Fast('ipblock','*',['id'=>$id]);
		okno_title($dataip['ip']);
		echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<b>'.(isset($dataip['name'])?$dataip['name']:'--').'</b>']);
		echo form(['name'=>$lang['ip'],'descr'=>'','pole'=>'<b>'.$ip.'</b>']);
		echo form(['name'=>$lang['block'],'descr'=>'','pole'=>'<b>'.$blockip['ipblock'].'</b>']);
		if(!empty($dataip['vlan']))
			echo form(['name'=>'Vlan','descr'=>'','pole'=>'<b>'.(!empty($dataip['vlan'])?$dataip['vlan']:'--').'</b>']);
		echo form(['name'=>'','descr'=>'','pole'=>'<a href="/?do=ipman&idblock='.$id.'&ipaddr='.$dataip['id'].'">'.$lang['delet'].'</a>']);
		okno_end();	
	}else{
		$sqlinsert = array();
		$device = $db->Fast('switch','*',['netip'=>$ip]);
		if(!empty($device['netip'])){
			$sqlinsert['blockid'] = $id;
			$sqlinsert['ip'] = $device['netip'];	
			$sqlinsert['name'] = $device['place'];	
			$sqlinsert['added'] = date('Y-m-d H:i:s');
			if(!empty($sqlinsert['ip']) && !empty($sqlinsert['blockid'])){
				$db->SQLinsert('ipaddress',$sqlinsert);
			}
		}
		$dataip = $db->Simple("SELECT * FROM ipaddress WHERE ip = '".$ip."' AND blockid = ".$id." LIMIT 1");
		$blockip = $db->Fast('ipblock','*',['id'=>$id]);
		okno_title($ip);
		echo form(['name'=>$lang['name'],'descr'=>'','pole'=>'<b>'.(isset($dataip['name'])?$dataip['name']:'--').'</b>']);
		echo form(['name'=>$lang['ip'],'descr'=>'','pole'=>'<b>'.$ip.'</b>']);
		echo form(['name'=>$lang['block'],'descr'=>'','pole'=>'<b>'.$blockip['ipblock'].'</b>']);
		if(!empty($dataip['vlan']))
			echo form(['name'=>'Vlan','descr'=>'','pole'=>'<b>'.(!empty($dataip['vlan'])?$dataip['vlan']:'--').'</b>']);
		echo form(['name'=>'','descr'=>'','pole'=>'<a href="/?do=ipman&idblock='.$id.'&ipaddr='.$dataip['id'].'">'.$lang['delet'].'</a>']);
		okno_end();	
	}
	}
}
}
die;
?>