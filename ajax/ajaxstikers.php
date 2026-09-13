<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';

$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']): null;

$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;

if($idonu){
	
$getont = $db->Fast('onus','*',['idonu' => $idonu]);

if(!empty($getont['idonu'])){
	if($act=='add'){
		$db->SQLupdate('onus',['stikers' =>1 ,'clockstikers' => $time],['idonu'=>$getont['idonu']]);
		echo '<div class="onu-olt efect1 m20b mobile"><div class="ont-block-bookmarks"><img src="../style/img/reviews.png"><span>'.$lang['stikers'].'</span><time>'.$time.'</time><a class="delstikers" href="#" onclick="ajaxstikers('.$getont['idonu'].',\'del\')">'.$lang['delstikers'].'</a></div></div>';
	}elseif($act=='del'){
		$db->SQLupdate('onus',['stikers' => 0,'clockstikers'=> $time],['idonu'=>$getont['idonu']]);
		echo'<div class="onu-olt efect1 m20b mobile"><div class="ont-block-bookmarks"><img src="../style/img/reviews.png"><a class="addstikers" href="#" onclick="ajaxstikers('.$getont['idonu'].',\'add\')">'.$lang['addstikers'].'</a></div>		</div>';	
	}else{
		
	}
}	

}
?>
