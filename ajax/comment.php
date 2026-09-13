<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$action = '';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;
if($act=='edittag'){
	$dataONT = $db->Fast('onus','*',['idonu'=>$id]);
	$action = '<input name="act" type="hidden" value="savetag"><input name="id" type="hidden" value="'.$id.'">';
	$title = $lang['editmark'].': '.$dataONT['type'].' '.$dataONT['inface'];
	$content = form(['name'=>$lang['marker'],'descr'=>'','pole'=>'<input required name="tag" class="input1" type="text" value="'.$dataONT['tag'].'">']);
	$btn = '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
}elseif($act=='deluid'){
	$dataONT = $db->Fast('onus','*',['idonu'=>$id]);
	$onukey = (!empty($dataONT['mac'])?$dataONT['mac']:(!empty($dataONT['sn'])?$dataONT['sn']:null));
	$datatemponu = $db->Fast('onusdata','*',['onukey'=>$onukey]);
	if(!empty($datatemponu['uid'])){
		$title = $lang['editmark'].': '.$dataONT['type'].' '.$dataONT['inface'];
		$content = form(['name'=>$lang['edituid'],'descr'=>'','pole'=>'Видалено']);
		$db->SQLupdate('onusdata',['uid'=>0],['id'=>$datatemponu['id']]);
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("onus_data_".$onukey);
		}
	}	
	$btn = '<div class="polebtn"><button type="button" form="formadd" id="refreshButton">'.$lang['update'].'</button></div><script>var refreshButton = document.getElementById("refreshButton");refreshButton.addEventListener("click", function() {location.reload();});</script>';	

}elseif($act=='deltag'){	
	$dataONT = $db->Fast('onus','*',['idonu'=>$id]);
	$onukey = (!empty($dataONT['mac'])?$dataONT['mac']:(!empty($dataONT['sn'])?$dataONT['sn']:null));
	$datatemponu = $db->Fast('onusdata','*',['onukey'=>$onukey]);
	if(!empty($datatemponu['tag'])){
		$title = $lang['marker'].': '.$dataONT['type'].' '.$dataONT['inface'];
		$content = form(['name'=>$lang['marker'],'descr'=>'','pole'=>'Видалено']);
		$db->SQLupdate('onusdata',['tag'=>''],['id'=>$datatemponu['id']]);
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$cacheManager->delete("onus_data_".$onukey);
		}
	}	
	$btn = '<div class="polebtn"><button type="button" form="formadd" id="refreshButton">'.$lang['update'].'</button></div><script>var refreshButton = document.getElementById("refreshButton");refreshButton.addEventListener("click", function() {location.reload();});</script>';	
}elseif($act=='edituid'){
	$dataONT = $db->Fast('onus','*',['idonu'=>$id]);
	$onukey = (!empty($dataONT['mac'])?$dataONT['mac']:(!empty($dataONT['sn'])?$dataONT['sn']:null));
	$datatemponu = $db->Fast('onusdata','*',['onukey'=>$onukey]);
	if(!empty($datatemponu['uid'])){
		$action = '<input name="act" type="hidden" value="saveuid"><input name="id" type="hidden" value="'.$id.'">';
		$title = $lang['edituid'].': '.$dataONT['type'].' '.$dataONT['inface'];
		$content = form(['name'=>'UID','descr'=>'','pole'=>'<input required name="uid" class="input1" type="text" value="'.$datatemponu['uid'].'">']);
	}
	$btn = '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></div>';
}else{
		
}
if($content){
	okno_title($title);
	echo'<form action="/?do=send" method="post" id="formadd">';
	echo $action;
	echo $content;
	echo'</form>';
	echo $btn;
	okno_end();
}

