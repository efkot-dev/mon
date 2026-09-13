<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$checkLicenseSwitch = getSwitchAll();
$select_id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
$metatags = array('title'=>'Bad ONTs','description'=>'Bad ONTs','page'=>'badont');
$array_ont_olt = [];
$array_olt = [];
$tplresult = '';
if(isset($checkLicenseSwitch) && count($checkLicenseSwitch) > 0){
	foreach($checkLicenseSwitch as $switch){
		if($access->get('dev'.$switch['id'])){
			$array_olt[$switch['id']] = [
				'olt'   => $switch['id'],'place'  => $switch['place'],'model'  => $switch['model'] . $switch['inf'],
			];
		}
	}
}
$selectswitch = isset($selectswitch) ? $selectswitch : '';
$where_onus = empty($selectswitch) ? "WHERE" : "WHERE olt = '$selectswitch' AND ";
$orderby = "(reason = 'err34' OR reason = 'err47') ORDER BY added DESC";
$array_ont = [];
$sqltemponu = $db->SimpleWhile("SELECT * FROM onus $where_onus $orderby");
if(isset($sqltemponu) && count($sqltemponu) > 0){
	foreach($sqltemponu as $ont){
		if($access->get('dev'.$ont['olt'])){
			$array_ont_olt[$ont['olt']] = $array_olt[$ont['olt']];
			$array_ont[$ont['olt']]['ont'][$ont['idonu']] = $ont;
		}
	}
}
$tplresult .= '
<div class="container">
    <div class="left-column">';
$tplresult .= "<div class='menu_olt_left'>";
if(isset($array_ont_olt) && count($array_ont_olt) > 0){
	if (isset($select_id) && $select_id > 0) {
		$tplresult .= "<a class='menu-sub' href='/?do=badont'><img src='../style/img/pmon_return.png'>View all <span class=\"badont\">".count($sqltemponu)."</span></a>"; 
	}
	foreach($array_ont_olt as $olt){
		$tplresult .= "<a class='menu-sub' href='/?do=badont&id=".$olt['olt']."'>
			".$olt['place']."
			<span class=\"badont\">".count($array_ont[$olt['olt']]['ont'])."</span>
			</a>";
	}	
}else{
	$tplresult .= "Відсутні";
}
$tplresult .= '</div>';
$tplresult .= '</div>';
$tplresult .= '<div class="right-column">';
$tplresult .= '<table class="resp-tab"><thead><tr>
	<th width="5%">'.$lang['status'].'</th>
	<th>Mac_SN</th>
	<th width="10%">'.$lang['inface'].'</th>
	<th width="8%">'.$lang['dist'].'</th>
	<th width="8%">'.$lang['signal'].'</th>
	<th width="10%">Vendor ID</th>
	<th width="10%">'.$lang['change_time'].'</th>
	<th width="7%">'.$lang['last_signal'].'</th>
	<th class="mobile" width="15%">
		<span class="inf_status">
			<span class="tim1">'.$lang['online'].'</span>
			<span class="tim2">'.$lang['offline'].'</span>
		</span>
	</th>
	</tr></thead><tbody>';
if(isset($array_ont_olt) && count($array_ont_olt) > 0){
foreach ($array_ont_olt as $oltid => $olt) {
    if (isset($select_id) && $select_id != $oltid) {
        continue;
    }
    $tplresult .= '<td colspan="11" class="td_url"> 
        <a href="/?do=badont&id=' . $olt['swid'] . '">' . 
        (isset($olt['place']) ? $olt['place'] : '') . '</a>
    </td>';
    if (!isset($array_ont[$oltid]['ont']) || empty($array_ont[$oltid]['ont'])) {
        continue;
    }
    foreach ($array_ont[$oltid]['ont'] as $ontid => $onu) {
        $onukey = !empty($onu['mac']) ? $onu['mac'] : (!empty($onu['sn']) ? $onu['sn'] : 'n/a');
        $onuname = !empty($onu['name']) ? '<span class="bad_name_onu">' . $onu['name'] . '</span>' : '';
        
        $tplresult .= '<tr>
            <td><span class="statusonu st_' . $onu['status'] . '"></span></td>
            <td class="td_url">
                <a href="/?do=onu&id=' . $onu['idonu'] . '">' . 
                ($onu['status'] == 2 ? '<font color="grey">' : '') . $onukey . ' ' . $onuname . '</a>
            </td>
            <td class="td_url"><font color="grey">' . strtoupper($onu['type']) . ' ' . $onu['inface'] . '</td>
            <td class="td_url">' . ($onu['dist'] ? metersToKilometers($onu['dist']) : '') . '</td>
            <td class="bad_list">' . 
                ($onu['status'] == 2 ? 'N/A' : '' . signalTerminal($onu['rx']) . '') . 
            '</td>
            <td><font color="#222">' . $onu['vendor'] . ' ' . $onu['model'] . '</font></td>
            <td><font color="#2196F3">' . $onu['changerx'] . '</font></td>
            <td>' . 
                ($onu['status'] == 2 ? 'N/A' : signalTerminal($onu['lastrx'])) . 
            '</td>';
			$tplresult .= '<td class="mobile">';
				if($onu['status']==1){
					$tplresult .= '<span class="on_">'.aftertime($onu['online']).'</span>';
				}else{
					$tplresult .= '<span class="off_">'.aftertime($onu['offline']).'</span>';
				}
			$tplresult .= '</td>';
        $tplresult .= '</tr>';
    }
}
}else{
	$tplresult .= '<tr><td colspan="11">'.$lang['empty'].'</td></tr>';
}
$tplresult .= '</table>';
$tplresult .= '</div>';
$result ='<div id="onu-speedbar">
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Bad ONTs</span>
	</div>
	'.$tplresult.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$result);
$tpl->compile('content');
$tpl->clear();
?>