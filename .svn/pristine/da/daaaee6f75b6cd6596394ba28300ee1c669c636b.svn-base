<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$select_olt = $select_olt ?? null;
$zapros = (isset($_POST["zapros"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["zapros"]))))):null);
$typedevice = (isset($_POST["typedevice"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["typedevice"]))))):null);
$pattern = '/^.{4}:/';
if(preg_match($pattern, $zapros)){
	$zapros = str_replace(' ','',$zapros);
}
if(!empty($USER['id']) && isset($zapros) && $USER['id']>0){
$where = "(locationname LIKE '%".$zapros."%' OR place LIKE '%".$zapros."%' OR netip LIKE '%".$zapros."%' OR inf LIKE '%".$zapros."%' OR model LIKE '%".$zapros."%')";
if($typedevice=='pon'){
	$getdevice='olt';
}else{
	$getdevice='switch';	
}
$sql = "SELECT id,ping,monitor,status,typecheck,oidid,inf,model,netip,class,device,name,firmware,olt_descr,
uptime,place,updates,img,locationname FROM switch WHERE device = '{$getdevice}' AND $where";
$sqlolt = $db->SimpleWhile($sql);
if(isset($sqlolt) && count($sqlolt)>0){
	echo'<div id="device"><table cellspacing="0" cellpadding="3" width="100%" id="page_device">';
	foreach($sqlolt as $ontid => $olt){
		$getStatistic = getOltStats($olt['id']);
	echo'<tr><td>
	<table class="olt-dev-olt ">
	<tbody><tr>
	<td class="olt-img">
		<div class="lg">
			<a href="/?do=detail&act=olt&id='.$olt['id'].'"><img src="../style/device/'.$olt['img'].'"></a>
		</div>
	</td>
	<td class="olt-n">
		<div class="olt-nam"><a class="olt-name" href="/?do=detail&act=olt&id='.$olt['id'].'"><img src="../style/img/link.png">'.$olt['place'].'</a></div>
		<div class="olt-notes">			
			<span class="olt-location">'.$olt['locationname'].'</span>
			<span class="olt-model">'.$olt['inf'].' '.$olt['model'].'</span>
				
			<span class="timer"><img src="../style/img/refresh.png">'.aftertime($olt['updates']).'</span>			
		</div>
	</td>';
	echo'
	<td class="olt-info">
	';
	if($olt['device']=='olt'){
		$count_bad_signal = get_bad_rx_olt($olt['id']);
		echo'<div class="onu_stats">'.(isset($count_bad_signal) && !empty($count_bad_signal) && $count_bad_signal>1?'<div class="berr">'.$count_bad_signal.' '.$lang['listrxdescr'].'</div>':'').'</div>';
	}
	echo'
		<span class="timer"><img src="../style/img/on-time.png"><span>'.$olt['uptime'].'</span></span>
	</td>
	';
	if($olt['device']=='olt'){
		$offline = intval($getStatistic['sql_count_onu'] ? $getStatistic['sql_count_onu'] : 0) - intval($getStatistic['sql_count_onu_on'] ? $getStatistic['sql_count_onu_on'] : 0);
		echo'
		<td class="olt-subs">
			<div class="olt-flex olt-pon">
				<span class="n">'.$lang['count_port_pons'].'</span>
				<span class="c">'.(!empty($getStatistic['sql_count_pon']) ? $getStatistic['sql_count_pon']:0).'</span>
			</div>		
			<div class="olt-flex olt-port">
				<span class="n">'.$lang['ports'].'</span>
				<span class="c">'.(!empty($getStatistic['sql_count_port']) ? $getStatistic['sql_count_port']:0).'</span>
			</div>		
			<div class="olt-flex olt-onu">
				<span class="n">'.$lang['allonus'].'</span>
				<span class="c">'.intval($getStatistic['sql_count_onu'] ? $getStatistic['sql_count_onu'] : 0).'</span>
			</div>		
			<div class="olt-flex olt-onuon">
				<span class="n">'.$lang['online'].'</span>
				<span class="c">'.intval($getStatistic['sql_count_onu_on'] ? $getStatistic['sql_count_onu_on'] : 0).'</span>
			</div>				
			<div class="olt-flex olt-onuoff">
				<span class="n">'.$lang['offline'].'</span>
				<span class="c">'.$offline.'</span>
			</div>
		</td>
		';
	}else{
		echo'
		<td class="olt-subs">
			<div class="olt-flex olt-port">
				<span class="n">'.$lang['port'].'</span>
				<span class="c">'.(!empty($getStatistic['sql_count_port']) ? $getStatistic['sql_count_port']:0).'</span>
			</div>		
		</td>
		';
	}
	echo'
</tr>
</tbody></table></td></tr>
	';
	}
	echo'
</table></div>';
}
}
?>
