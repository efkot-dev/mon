<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$result = '';
$sqlswitch = $db->SimpleWhile("SELECT s.* FROM checkaccess a JOIN switch s ON CONCAT('dev', s.id) = a.types	WHERE a.uid = '{$USER['id']}' AND s.device = 'olt'");
$arrayswitch = array();
if (!empty($sqlswitch) && count($sqlswitch)>0) {
	foreach ($sqlswitch as $switch) {
		$id = $switch['id'];
		$arrayswitch[$id] = [
			'id' => $id,'place' => $switch['place'],'model' => $switch['inf'].''.$switch['model'],'netip' => $switch['netip']
		];
	}
}
// Фільтр доступу для користувача
$and_access = "AND (a.uid IS NOT NULL OR onus.idonu IS NULL)";
$access_sql = "LEFT JOIN checkaccess a ON CONCAT('dev', onus.olt) = a.types AND a.uid = '{$USER['id']}'";

// Основний SQL-запит
$sqlonus = $db->SimpleWhile("
    SELECT 
        mac, 
        sn, 
        COUNT(*) AS count 
    FROM 
        onus
        {$access_sql}
    WHERE 
        (
            (mac IS NOT NULL AND mac NOT IN ('--', '00:00:00:00:00:00', 'HWTC0000'))
            OR 
            (sn IS NOT NULL AND sn NOT IN ('--', '00:00:00:00:00:00', 'HWTC0000'))
        )
        AND olt > 0
        {$and_access}
    GROUP BY 
        mac, sn
    HAVING 
        count > 1
");

$onukey = array();
if(count($sqlonus)>0){
	foreach($sqlonus as $ontid => $ont){
		$onu = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
		$onukey[$onu]['mac'] = (!empty($ont['mac'])?$ont['mac']:'');
		$onukey[$onu]['sn'] = (!empty($ont['sn'])?$ont['sn']:'');
		$onukey[$onu]['count'] = (!empty($ont['count'])?$ont['count']:'');
		$onukey[$onu]['sql'] = (!empty($ont['mac'])?'mac = ':(!empty($ont['sn'])?'sn = ':null)).'';
	}
}
	$result .= '<table class="resp-tab list-onu-olt"><thead><tr>
		<th class="mob_w10" width="4%">'.$lang['status'].'</th>
		<th width="10%">'.$lang['device'].'</th>
		<th width="10%">'.$lang['ip'].'</th>
		<th width="10%">'.$lang['gilka'].'</th>
		<th width="10%">Mac_Sn</th>
		<th width="6%">
			<span class="inf_signal">
				<span class="sig2">RX ONU</span>
			</span>
		</th>
		<th class="mobile" width="10%">'.$lang['dist'].'</th>
		<th class="mobile" width="15%">
			<span class="inf_status">
				<span class="tim1">'.$lang['added'].'</span>
			</span>
		</th>		
		<th class="mobile" width="15%">
			<span class="inf_status">
				<span class="tim1">'.$lang['online'].'</span>
				<span class="tim2">'.$lang['offline'].'</span>
			</span>
		</th>
		<th>Other</th>
		</tr>
		</thead><tbody>';
if(isset($onukey) && count($onukey)>0){
	foreach($onukey as $ontkey => $onu){
		$result .= '<tr><td class="td_name" colspan="10">' . $ontkey . '[' . $onu['count'] . ']</td></tr>';
		$sqlonu = $db->SimpleWhile("SELECT * FROM onus WHERE " . $onu['sql'] . " '" . $ontkey . "'");
		foreach($sqlonu as $idonu => $ont){
			$status = statusTermianl($ont['status']);
			if($ont['status']==1){
				$get_status = $status['img'];
			}else{
				$get_status = reason_onu($ont['status'],$ont['reason']);
			}
			$onuname = (!empty($ont['name'])? '<span class="bad_name_onu">'.$ont['name'].'</span>':'');
			$added = checkWhenAdded($ont['added']);
			$result .= '<tr class="'.$status['css'].''.$added.'">';
			$result .= '<td class="status">'.$get_status.'</td>';
			$result .= '<td class="inface_onu"><a href="/?do=detail&act=olt&id='.$ont['olt'].'">
			'.$arrayswitch[$ont['olt']]['place'].'
			</a></td>';
			$result .= '<td class="inface_onu"><a href="/?do=detail&act=olt&id='.$ont['olt'].'">
			'.$arrayswitch[$ont['olt']]['netip'].'
			</a></td>';
			$result .= '<td class="inface_onu"><a href="/?do=onu&id='.$ont['idonu'].'">'.$ont['type'].' '.$ont['inface'].'</a></td>';
			$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
			if(isset($onukey)){
				$datatemponu = getFastOnusData($onukey);
			}
			$result .= '<td class="td_url"><a href="/?do=onu&id='.$ont['idonu'].'">'.$onukey.' '.$onuname.'</a></td>';
			// Signal Rx Onu
			$result .= '<td>';
			if($ont['status']==1){
				$result .=  signalTerminal($ont['rx']).($ont['rxstatus']=='up' || $ont['rxstatus']=='down' ? '<span class="signaldown"><i class="fi fi-rr-angle-small-'.$ont['rxstatus'].'"></i></span>':'');
			}
			$result .= '</td>';
			// Довжина волокна
			$result .= '<td class="dist mobile">'.($ont['dist'] ? metersToKilometers($ont['dist']) : '').'</td>';
			// Онлайн / Оффлайн
			$result .= '<td class="mobile">';
			$result .= '<span class="on_">'.$ont['added'].'</span>';
			$result .= '</td>';			
			$result .= '<td class="mobile">';
				if($ont['status']==1){
					$result .= '<span class="on_">'.aftertime($ont['online']).'</span>';
				}else{
					$result .= '<span class="off_">'.aftertime($ont['offline']).'</span>';
				}
			$result .= '</td>';
			// name
			$onu_name = (!empty($ont['name'])?'<span class="name-onu">'.$ont['name'].'</span>':'');
			// Tag		
			$tag = (!empty($datatemponu['tag'])?'<span class="terminaltag">'.$datatemponu['tag'].'</span>':'');
			// vendor			
			$result .= '<td class="description_name mobile_font">
			'.$onu_name.' '.$tag.' 
			</td>';
			$result .= '</tr>';
		}
	}
}else{
	$result .= '<tr><td class="td_name" colspan="8">'.$lang['empty_search'].'</td></tr>';
}
$metatags = [
	'title'=>$lang['duble_onu'],
	'description'=>$lang['duble_onu'],
	'page'=>'duplicated'
];
$result .= '</table>';
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['duble_onu'].'</span></div>'.$result.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>