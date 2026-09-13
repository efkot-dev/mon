<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($_POST['id']){
$result = array();
$resulttype  = array();
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(empty($id)){
	die('Err:1');
}
$getOLT = $db->Fast('switch','*',['id' => $id]);
$sqlonu = $db->SimpleWhile("SELECT * FROM onus WHERE olt = ".$id);
$temponu = [];
if (!empty($sqlonu)) {
    foreach ($sqlonu as $onu) {
		if($onu['type']=='gpon'){
			$temponu[$onu['zte_idport']][$onu['keyonu']] = [
				'dist' => $onu['dist'],
				'status' => $onu['status'],
				'idonu' => $onu['idonu'],
				'keyonu' => $onu['keyonu'],
				'name' => $onu['name'],
				'sn' => $onu['sn'],
				'rx' => $onu['rx'],
				'lasterr' => $onu['lasterr'],
				'err' => $onu['err'],
				'inface' => $onu['inface']
			];
		}
    }
}

$sqlpon = $db->SimpleWhile("SELECT * FROM switch_pon WHERE oltid = ".$id);
$arrayonus = [];
if (!empty($temponu) && !empty($sqlpon)) {
    foreach ($sqlpon as $pon) {
        $arrayonus[$pon['sfpid']] = [
            'sqlid' => $pon['id'],
            'llid' => $pon['sfpid'],
            'pon' => $pon['pon'],
            'onu' => isset($temponu[$pon['sfpid']]) ? $temponu[$pon['sfpid']] : []
        ];
    }
}
echo '<table class="resp-tab"><thead><tr><th width="2%"></th><th width="10%">interface</th><th width="10%">error</th><th width="5%">rx</th><th width="5%">dist</th><th></th></tr></thead><tbody>';
foreach ($arrayonus as $inface) {    
    if (!empty($inface['onu'])) {
        echo '<tr><td class="td_name" colspan="6">'.$inface['pon'].'</td></tr>';
        foreach ($inface['onu'] as $keyonu => $onus) {   
			if(!empty($onus['lasterr']) && $onus['lasterr']>5){		
				echo '<tr>';    
				echo '<td><span class="statusonu st_'.$onus['status'].'"</span></td>';    
				echo '<td class="td_url"><a href="/?do=onu&id='.$onus['idonu'].'" '.($onus['status']==2?'class="colorgrey"':'').'>'.$onus['inface'].'</a></td>';    
				echo '<td><font color=grey>'.$onus['lasterr'].'</font>'.(isset($onus['err']) && $onus['err']>0?' <font color=red>+'.$onus['err'].'</font>':'').'</td>';    
				echo '<td>'.signalTerminal($onus['rx']).'</td>';    
				echo '<td><font color="#1f7bc3">'.$onus['dist'].'</font></td>';    
				echo '<td class="typesmac">'.$onus['name'].' '.$onus['sn'].'</td>';    
				echo '</tr>';  
			}
        }
    }
}

echo '</tbody></table>';
	
echo'</tbody></table>'; 
}
?>

