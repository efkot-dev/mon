<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$tplresult = '';
$metatags = array('title'=>'Manager vlan','description'=>'Manager vlan','page'=>'vlan');
$vlansip = $db->SimpleWhile("SELECT * FROM ipvlans ORDER BY CAST(vlan AS SIGNED) ASC");
$tplresult .= '<div class="pole"><a href="#" onclick="ajaxipman(1,1,\'addvlan\');"  class="urlelelement">'.$lang['add'].' Vlan</a></div>';
if(is_array($vlansip) && count($vlansip)>0){
		$tplresult .='<div id="backup_panel" class="popupContainer">
		<div class="popupContent">
			<div id="result_ajax"></div>
		</div>
	</div>';
	$tplresult .= '<div id="ontbdcomepon" style="width: 100%;padding: 0;" class="elen">
	<table class="resp-tab"><thead><tr>
	<th width="5%">Vlan</th>
	<th width="30%">Name</th>
	<th width="10%">Manager</th>
	<th>Device</th>
	</thead><tbody>';
	$tplresult .='';
	foreach($vlansip as $vlan){		
		$count_vlan = $db->SimpleWhile("SELECT COUNT(idonu) AS count_vlan
FROM onus
WHERE wan = '{$vlan['vlan']}' OR 
      (CHAR_LENGTH(wan) > CHAR_LENGTH('{$vlan['vlan']}') AND wan LIKE '%{$vlan['vlan']}%');")[0];
		
		$tplresult .='<tr>';	
		$tplresult .='<td class="mobile">';	
		$tplresult .='<span class="signal3">'.$vlan['vlan'].'</span>';	

		$tplresult .='</td>';	
		$tplresult .='<td class="mobile txt_left">';	
		$tplresult .='<span class="on_">'.$vlan['name'].'</span>';	
		$tplresult .='</td>';	
		$tplresult .='<td class="manager_href">';
			$tplresult .='<span class="db openPopup" data-popup-id="backup_panel" onclick="get_vlan(\''.$vlan['id'].'\');"><img src="../style/img/edit.png"></span>';		
			$tplresult .='<span class="db openPopup" data-popup-id="backup_panel" onclick="del_vlan(\''.$vlan['id'].'\');"><img src="../style/img/delet.png"></span>';		
		$tplresult .='</td>';	
		$tplresult .='<td class="txt_left typesmac td_url">';
		if($count_vlan['count_vlan']>0){
			$tplresult .='<img src="../style/img/code.png">ONU: <a href="/?do=search&search='.$vlan['vlan'].'&act=search&types=vlan">'.$count_vlan['count_vlan'].'</a>';
		}
		$tplresult .='</td>';	
		$tplresult .='</tr>';	
	}
	$tplresult .='</tbody></table></div>';	
	
}
$result ='
	<div id="onu-speedbar">
		<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Manager Vlan</span>
	</div>
	<div style="margin: 0;">
	<div class="page-error">
		'.$tplresult.'
	</div>
';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
?>