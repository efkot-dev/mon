<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$result = '';
$sqlswitch = $db->SimpleWhile("SELECT * FROM switch");
$arrayswitch = array();
if (!empty($sqlswitch) && count($sqlswitch)>0) {
	foreach ($sqlswitch as $switch) {
		$id = $switch['id'];
		$arrayswitch[$id] = [
			'id' => $id,'place' => $switch['place'],'model' => $switch['inf'].''.$switch['model'],'netip' => $switch['netip']
		];
	}
}
// 00:00:00:00:00:00
$sqlonus = $db->SimpleWhile("SELECT mac, sn, COUNT(*) as count 
FROM onus 
WHERE ((mac IS NOT NULL AND mac != '--' AND mac != '00:00:00:00:00:00' AND mac != 'HWTC0000') OR 
       (sn IS NOT NULL AND sn != '--' AND sn != '00:00:00:00:00:00' AND sn != 'HWTC0000'))
GROUP BY mac, sn 
HAVING count > 1;
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
$result .= '<table class="resp-tab"><thead><tr><th width="15%">Olt</th><th width="5%">Satus</th><th width="15%">Mac/Sn</th><th width="10%">Inface</th><th>Added</th><th>Online</th><th>Offline</th><th>Checker</th></tr></thead><tbody>';
if(isset($onukey) && count($onukey)>0){
foreach($onukey as $ontkey => $ont){
	$result .= '<tr><td class="td_name" colspan="8">' . $ontkey . '[' . $ont['count'] . ']</td></tr>';
	$sqlonu = $db->SimpleWhile("SELECT * FROM onus WHERE " . $ont['sql'] . " '" . $ontkey . "'");
	foreach($sqlonu as $idonu => $onu){
		$result .= '<tr>
		<td>'.$arrayswitch[$onu['olt']]['place'].' '.$arrayswitch[$onu['olt']]['model'].'</td>
		<td><span class="statusonu st_'.$onu['status'].'"</span></td>
		<td class="td_url"><a href="/?do=onu&id='.$onu['idonu'].'" '.($onu['status']==2?'class="colorgrey"':'').'>'.$ontkey.'</a></td>
		<td>'.$onu['type'].' '.$onu['inface'].'</td>
		<td><font color="#1f7bc3">'.$onu['added'].'</font></td>
		<td><font color="#4CAF50">'.$onu['online'].'</font></td>
		<td><font color="tomato">'.$onu['offline'].'</font></td>
		<td><font color="orange">'.aftertime($onu['updates']).'</font></td>
		</tr>';
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