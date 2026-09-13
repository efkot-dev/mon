<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
if(empty($id)){
	$go->redirect('main');
}
$metatags = array('title'=>$lang['onuvendor'],'description'=>$lang['onuvendor'],'page'=>'onuvendor');
$result = '';
$getSwitch = $db->Fast('switch','*',['id' => $id]);
$sqlonu = $db->SimpleWhile("SELECT inface,idonu,type,model,vendor,portolt,keyonu FROM onus WHERE olt = ".$id);
$temponu = [];
if (!empty($sqlonu)) {
    foreach ($sqlonu as $onu) {
		$temponu[$onu['portolt']][$onu['keyonu']] = $onu;
    }
}
$sqlpon = $db->SimpleWhile("SELECT * FROM switch_pon WHERE oltid = ".$id);
$arrayonus = [];
if (!empty($temponu) && !empty($sqlpon)) {
    foreach ($sqlpon as $pon) {
        $arrayonus[$pon['sfpid']] = [
            'sqlid' => $pon['id'],'llid' => $pon['sfpid'],'pon' => $pon['pon'],'onu' => isset($temponu[$pon['sfpid']]) ? $temponu[$pon['sfpid']] : []
        ];
    }
}
$result .= '<table class="resp-tab"><thead><tr><th>Model Vendor</th></tr></thead><tbody>';
foreach ($arrayonus as $inface) {    
    if (!empty($inface['onu'])) {
        $result .= '<tr><td class="td_name">'.$inface['pon'].'</td></tr><tr><td><div class="lf_1">';
        foreach ($inface['onu'] as $keyonu => $onus) {   
			if(!empty($onus['model'])){		
				$result .= '<a href="/?do=onu&id='.$onus['idonu'].'">'.(isset($onus['vendor']) &&!empty($onus['vendor']) ? $onus['vendor'].' ':'').''.$onus['model'].'</a> ';  
			}
        }
		$result .= '</div></td></tr>';
    }
}
$result .= '</tbody></table>';
if(!empty($getSwitch['netip']) && $getSwitch['monitor']=='yes') {
	$tpl->load_template('onuvendor.tpl');
	$tpl->set('{result}',$result);
	$tpl->set('{place}',$getSwitch['place']);
	$tpl->set('{id}',$getSwitch['id']);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');	
}
?>
