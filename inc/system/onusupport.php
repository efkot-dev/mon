<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (isset($confPMon['PMON_SUPPORT']) && !empty($confPMon['PMON_SUPPORT']) && $confPMon['PMON_SUPPORT'] == 1) {
	$page = '';
	$metatags = array('title'=>'ONU Support','description'=>'ONU Support','page'=>'onusuppport');
	if($act == 'message'){
		$url_page = '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Messages for technical support</span>';	
		$page = '';
	}elseif($act == 'setting'){
		if ($access->get('setup') ){
			$pmon_api = (isset($confPMon['PMON_TECH_API_GET']) && !empty($confPMon['PMON_TECH_API_GET']) ? $confPMon['PMON_TECH_API_GET']:false);
			$api_key = (isset($confPMon['PMON_TECH_API']) && !empty($confPMon['PMON_TECH_API']) ? $confPMon['PMON_TECH_API']:false);
			$uid_key = (isset($confPMon['PMON_TECH_UID']) && !empty($confPMon['PMON_TECH_UID']) ? $confPMon['PMON_TECH_UID']:false);
			$url_page = '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>API Settings</span>';	
			$page = '<div class="page_free"><form action="/?do=savesetup" method="post" id="formadd">
				<input name="module" type="hidden" value="added"><input name="action" type="hidden" value="onusupport">';
			$page .= formpage(['img'=>'img1.png','name'=>'API Key','descr'=>'Key provided by pmon.tech service',
				'pole'=>'<input types style="width: 300px;" name="api_key" class="input1" type="text" value="'.$api_key.'">']);	
			$page .= formpage(['img'=>'img1.png','name'=>'UID ISP','descr'=>'UID provided by pmon.tech service',
				'pole'=>'<input style="width: 100px;" types name="uid_isp" class="input1" value="'.$uid_key.'" type="text">']);
			$page .= formpage(['img'=>'img1.png','name'=>'API Pmon key','descr'=>'Internal key for using the service',
				'pole'=>'<input style="width: 300px;" types name="pmon_api" class="input1" value="'.$pmon_api.'" type="text">']);
			$page .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form></div>';
		}else{
			
		}
	}else{
		$url_page = '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>System billing for customers who are provided with ONU transport</span>';
		$page = '';
		$sql = "SELECT onus.*, switch.place
			FROM onus
			JOIN onu_support ON onus.idonu = onu_support.idonu
			JOIN switch ON onus.olt = switch.id;";
		$list_ont = $db->SimpleWhile($sql);
		if(isset($list_ont) && count($list_ont)>0){
			$page ='<table class="resp-tab" width="100%"><thead><tr>
			<th width="5%">'.$lang['status'].'</th>
			<th width="15%">'.$lang['device'].'</th>
			<th width="10%">MAC Serial</th>
			<th width="10%">'.$lang['gilka'].'</th>
			<th width="6%">
				<span class="inf_signal">
					<span class="sig2">RX ONU</span>
				</span>
			</th>';
			$page .='<th width="6%">'.$lang['dists'].'</th>
			<th>Info</th>
			</tr></thead><tbody>';
			foreach($list_ont as $idonu => $ont){
				$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
				$page .= '<tr>
					<td>
						<span class="statusonu st_'.$ont['status'].'"></span>
					</td>
					<td class="td_url">
						<a href="/?do=onu&id='.$ont['idonu'].'">'.$ont['place'].'</a>
					</td>
					<td class="td_url">
						<a href="/?do=onu&id='.$ont['idonu'].'" '.($ont['status']==2?'class="colorgrey"':'').'>'.(isset($ont['mac'])?$ont['mac']:(isset($ont['sn'])?$ont['sn']:'n/a')).'</a>
					</td>
					<td class="td_url">
						<a href="/?do=onu&id='.$ont['idonu'].'" '.($ont['status']==2?'class="colorgrey"':'').'>'.$ont['type'].' '.$ont['inface'].'</a>
					</td>
					<td>
						'.($ont['status']==1?signalTerminal($ont['rx']):'n/a').'
					</td>';
					// name
					$onu_name = (!empty($ont['name'])?'<span class="name-onu">'.$ont['name'].'</span>':'');
					// vendor
					$page .= '
					<td class="dist mobile">'.($ont['dist'] ? metersToKilometers($ont['dist']) : '').'</td>
					
					<td class="description_name mobile_font stikers">
						'.($ont['status']==2?(!empty($ont['reason'])?'<span class="reason_'.$ont['reason'].'"></span>':''):'').'
						'.($ont['status']==2?(!empty($ont['offline'])?'<span class="off_">'.aftertime($ont['offline']).'</span> ':''):'').'
						'.$onu_name.' '.$tag.'
					</td>
				</tr>';
			}
			$page .='</tbody></table>';
		}
	}
	$tplresult .= '
	<div class="container">
		<div class="left-column">
			<div class="menu_olt_left">
				<a class="menu-sub" href="/?do=onusupport">ONTs List</a>
				'.($access->get('setup') ? '<a class="menu-sub" href="/?do=onusupport&act=message">Technical support</a>' : '').'
				<a class="menu-sub" href="/?do=onusupport&act=setting">API Settings</a>
			</div>
		</div>
		<div class="right-column">
		'.$page.'
		</div>
	</div>';
	$result ='<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			'.$url_page.'
		</div>
	'.$tplresult.'';
	$tpl->load_template('main/main.tpl');
	$tpl->set('{block-main}',$result);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');	
}
?>