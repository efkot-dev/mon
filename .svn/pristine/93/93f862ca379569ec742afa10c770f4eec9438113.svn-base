<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array(
	'title'=>$lang['list_sfp_page'],
		'description'=>$lang['list_sfp_page'],
			'page'=>'ponsignal');
$temp_port = [];
$sql_port = $db->SimpleWhile("SELECT id,deviceid,llid,nameport,descrport,typeport FROM switch_port WHERE typeport = 'epon' OR typeport = 'gpon' ");
if(isset($sql_port) && count($sql_port) > 0){
	foreach ($sql_port as $portid => $port) {
		if($access->get('dev'.$port['deviceid'])){
			$temp_port[$port['deviceid']][$port['llid']] = array(
				'nameport'=>$port['nameport'],'descrport'=>$port['descrport'],'typeport'=>$port['typeport'],
			);
		}
	}
}
$sql_olt = $db->SimpleWhile("SELECT id, place, netip, snmpro, oidid, inf, model FROM switch WHERE device = 'olt' AND monitor = 'yes'");
$temp_pon = [];
if (isset($sql_olt) && count($sql_olt) > 0) {	
    foreach ($sql_olt as $id => $olt) {
		if($access->get('dev'.$olt['id'])){
			$oidid = $olt['oidid'];
			if ($oidid == 14) {
				$temp_pon[$olt['id']] = processSignalData('epon', $olt['id']);        
				$temp_pon[$olt['id']] = processSignalData('gpon', $olt['id']);
			} else {
				$temp_pon[$olt['id']] = processSignalData('pon', $olt['id']);
			}
		}
	}
}
$tplresult = '';
if(isset($sql_olt) && count($sql_olt) > 0){
	$tplresult .='<div id="main-port">';
	foreach ($sql_olt as $id => $get_olt) {
	if($access->get('dev'.$get_olt['id'])){
	$tplresult .='<div class="olt-style">
		<div class="main-switch-name">
			<span class="m">'.$get_olt['inf'].'</span>
			<span class="s">'.$get_olt['model'].'</span>
			<span class="n">'.$get_olt['place'].'</span>	
		</div>
	<div class="main-switch-port"><div class="load-sfp-data">';		
		if ($get_olt['oidid'] == 15) {
			if(isset($temp_pon[$get_olt['id']]['pon'])){
				foreach($temp_pon[$get_olt['id']]['pon'] as $llid => $pon_olt) {
					if (strpos($pon_olt, '1216') !== false){
						$llid = $llid + 4;
					}else{
						$llid = $llid;
					}
					if(isset($temp_pon[$get_olt['id']]['pon'][$llid])){
						$tplresult .= tpl_sfp($temp_port,$llid,$get_olt,$temp_pon,'pon');
					}
				}
			}	
		}elseif ($get_olt['oidid'] == 14) {
			if(isset($temp_pon[$get_olt['id']]['gpon'])){
				foreach($temp_pon[$get_olt['id']]['gpon'] as $llid => $pon_olt) {
					if(isset($temp_pon[$get_olt['id']]['gpon'][$llid])){
						$tplresult .= tpl_sfp($temp_port,$llid,$get_olt,$temp_pon,'gpon');
					}
				}
			}
		}else{
			if(isset($temp_pon[$get_olt['id']]['pon'])){
				foreach($temp_pon[$get_olt['id']]['pon'] as $llid => $pon_olt) {
					if(isset($temp_pon[$get_olt['id']]['pon'][$llid])){
						$tplresult .= tpl_sfp($temp_port,$llid,$get_olt,$temp_pon,'pon');
					}
				}
			}	
		}	
		$tplresult .='</div></div></div>';	
	}		
	}
	$tplresult .='</div>';
}
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['list_sfp_page'].'</span></div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>