<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$result = '';
$foldercache = ROOT_DIR.'/export/cache/';
$serializedData = file_get_contents($foldercache.'kitsman_planovi.data');  
if(isset($serializedData)){
	$decodedArray = unserialize($serializedData);
	$result .= '<div class="command-panel"><div class="commands"><img src="../style/img/danger.png" alt="" class="default-icon">Кіцманський район</div></div>
	<table class="resp-tab"><thead><tr><th>Район</th><th>Нас.пункт</th><th>Вулиця</th><th width="10%">Початок</th><th width="10%">Кінець</th></tr></thead><tbody>';
	foreach ($decodedArray as $key => $value) {
		if(isset($value['name']) && isset($value['data'][0][0])){
			$result .= '<tr><td colspan="5">'.$value['name'].'</td></tr>';	
			foreach ($value['data'] as $subArray) {
				$result .= '<tr><td>'.$subArray[0].'</td><td>'.$subArray[1].'</td><td>'.$subArray[2].'</td><td><font color="#c200c3">'.$subArray[3].'</font></td><td><font color="tomato">'.$subArray[4].'</font></td></tr>';
			}	
		}
	}
	$result .= '</table>';
}
$planovi = [];
$ZalserializedData = file_get_contents($foldercache.'zal_planovi.data');  
if(isset($ZalserializedData)){
	$decodedZal = unserialize($ZalserializedData);	
	foreach($decodedZal as $zkey => $zvalue) {
		if(isset($zvalue[0]['id'])){
			$planovi[$zvalue[0]['id']] = array(	'id'=>$zvalue[0]['id'],'el_ust'=>$zvalue[0]['el_ust'],'list'=>$zvalue[0]['list'],'reason'=>$zvalue[0]['reason'],'time_z'=>$zvalue[0]['time_z'],'time_to'=>$zvalue[0]['time_to']);
		}
	}
}
if(isset($planovi)){
	$result .= '<div><div class="command-panel"><div class="commands"><img src="../style/img/danger.png" alt="" class="default-icon">Заліщицький район</div></div><table class="resp-tab"><thead><tr><th>Список</th><th width="10%">Причина</th><th width="10%">Початок</th><th width="10%">Кінець</th></tr></thead><tbody>';
	foreach($planovi as $fkey => $fvalue) {
		$list = '';
		preg_match_all('/(с\.|смт\.|м\.)\s([^\:]+):/u', $fvalue['list'], $matches);
		$settlements = $matches[2];
		foreach ($settlements as $settlement) {
			$list .= $settlement . ", ";
		}
	$result .= '<tr><td>'.$list.'</td><td>'.$fvalue['reason'].'</td><td><font color="#c200c3">'.date('Y-m-d').' '.$fvalue['time_z'].'</font></td><td><font color="tomato">'.date('Y-m-d').' '.$fvalue['time_to'].'</font></td></tr>';
	}
	$result .= '</table></div>';
}


$metatags = [
	'title'=>'Планові роботи на сьогодні',
	'description'=>'Планові роботи на сьогодні',
	'page'=>'rem'
];

$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Планові роботи на сьогодні</span></div>'.$result.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>