<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$getallrx = '';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$port = isset($_POST['port']) ? Clean::int($_POST['port']): null;
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
if(isset($id)){
	$getswitch = $db->Fast('switch','*',['id'=>$id]);
}
if(!empty($getswitch['device']) && $id && $port && $getswitch['device']=='olt'){
	$getallrx = $db->Multi('onus','rx, status',['portolt'=>$port,'status'=>1,'olt'=>$getswitch['id']]);	
}elseif(!empty($getswitch['id']) && empty($port)){
	$sql_data = ['sql' => "SELECT rx, status FROM onus WHERE status = '1' AND olt = '{$getswitch['id']}'",'key' => 'main_char_signal_'.$getswitch['id'],'type' => 'while','time' => 1200];
	$getallrx = cache_simple_sql($sql_data);
}else{
	$and_access = "AND (a.uid IS NOT NULL OR idonu IS NULL)";
	$access_sql = "LEFT JOIN checkaccess a ON CONCAT('dev', olt) = a.types AND a.uid = '{$USER['id']}'";
	$sql_data = [
	  'sql' => "SELECT rx, status 
				FROM onus 
				{$access_sql}
				WHERE status = '1' {$and_access}",
	  'key' => 'main_char_sig_'.$USER['id'],
	  'type' => 'while',
	  'time' => 12
	];
	$getallrx = cache_simple_sql($sql_data);
}
if(isset($getallrx) && count($getallrx)>5){
$signals = array();
$counts = array();
foreach($getallrx as $item) {
	$rx = str_replace("-","",intval($item['rx']));
	if ($rx != 0 && $item['status'] == 1) {
		if (array_key_exists($rx,$signals)) {
			$counts[$rx]++;
		} else {
			$signals[$rx] = $item;
			$counts[$rx] = 1;
		}
	}
}
if(is_array($signals)){
	if(count($counts)>=5){
	$count = count($counts);
	if ($count <= 5) {
		$with =  intval(30/count($counts));
	} elseif ($count > 5 && $count < 10) {
		$with =  intval(60/count($counts));		
	} elseif ($count >= 10 && $count <= 14) {
		$with =  intval(80/count($counts));		
	} else {
		$with = intval(150 / $count);
	}
$maxvalue = max($counts);
ksort($signals);
echo'<div class="main_blocks">';
echo'<div class="classh">';
foreach ($signals as $rxs => $signal) {
	if($rxs>5){
		echo'<div class="load-onu-stats" style="width: '.$with.'%;background: #fff;" '.($act=='olt'?'onclick="showbadsignal(\''. $id.'\',\''. $rxs.'\');"':'onclick="badsignal(\''. $rxs.'\');"').'>';
		echo'<div class="sig">-'. $rxs.'</div><div class="load-onu"><div class="load-sig ';
		$minbad = (!empty($config['badsignalstart'])?$config['badsignalstart']:26);
		$maxbad = (!empty($config['badsignalend'])?$config['badsignalend']:31);
		if ($rxs < 15) {
			echo'color_sig_5';
		}elseif ($rxs < 18 && $rxs < $minbad) {
			echo'color_sig_4';
		}elseif ($rxs < $minbad) {
			echo'color_sig_1';
		} elseif ($rxs >= $minbad && $rxs < $maxbad) {
			echo'color_sig_2';
		} else {
			echo'color_sig_3';
		}
		echo'" style="height:'.get_heght($counts[$rxs],$maxvalue).'%;"></div>';
		echo'</div>';
		echo'<div class="count-sig">'. $counts[$rxs].'</div>';
		echo'</div>';
	}
}
}
echo'</div>';
echo'</div>';
}
}else{
	if(!empty($getswitch['id'])){
		if(!empty($getswitch['device']) && $getswitch['device']=='olt'){
			echo'<div style="padding:0px;font-size: 13px;">'.$lang['firstpmon'].'</div>';
		}else{ 
			if($getswitch['device']!='switch'){
				echo'<div style="padding:0px;font-size: 13px;">'.$lang['emptystats'].'</div>';
			}elseif($getswitch['oidid']==24){
				echo'<div id="switch_ajax"></div>';
			}
			
		}
	}
}
die;
?>