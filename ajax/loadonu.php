<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$tplont = '';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$sqlponswitch = $db->Fast('switch_port','*',['id'=>$id]);
if(!empty($sqlponswitch['id'])){
$sqlswitch = $db->Fast('switch','*',['id'=>$sqlponswitch['deviceid']]);
if(!empty($sqlswitch['id'])){
$getpon = $db->Fast('switch_pon','*',['sfpid'=>$sqlponswitch['llid'],'oltid'=>$sqlponswitch['deviceid']]);	
$sqlonus = $db->SimpleWhile('SELECT * FROM onus WHERE `portolt` = '.$sqlponswitch['llid'].' AND `olt` = '.$sqlponswitch['deviceid'].' ORDER BY status ASC, rx DESC');
if(is_array_empty($sqlonus)){
foreach($sqlonus as $idonu => $ont){
	$tplont .= '<div class="optic-terminal onu-'.$ont['status'].'">';
	$tplont .= '<div class="bdf">';
		$tplont .= styleRxMap($ont['rx']);
		$tplont .= '<img src="../style/img/terminal.png">';
	$tplont .= '</div>';
	$tplont .= '<div class="sgf">';
		$tplont .= '<span class="inface"><a href="/?do=onu&id='. $ont['idonu'].'">'. $ont['type'].' '. $ont['inface'].'</a></span>';
		$tplont .= '<span class="mac_sn">'. $ont['mac'].' '. $ont['sn'].'</span>';
	$tplont .= '</div>';
	$tplont .= '</div>';
}
}else{
	$tplont .= '--';
}
}
echo '<script>ajaxsfp('.$getpon['id'].');</script>';
echo '<div class="ajax-load-onu">';
echo '<div class="ajax-sfp-onu"><h2>'.$getpon['pon'].'</h2><div id="ajaxsfp"></div></div>';
echo $tplont;
echo '</div>';
}
?>