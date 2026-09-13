<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$oltid = (isset($_POST['oltid']) ? Clean::int($_POST['oltid']) : null);
if(isset($oltid) && $oltid > 0){	
	$frog_onus = array();
	$sql_frog_onu = $db->SimpleWhile('SELECT id, onukey, pontree, ponelement FROM `onusdata`');
	if(isset($sql_frog_onu) && count($sql_frog_onu) > 0) {
		foreach ($sql_frog_onu as $ont_frog) {
			$frog_onus[$ont_frog['onukey']] = array('onukey' => $ont_frog['onukey'], 'pontree' => $ont_frog['pontree'], 'ponelement' => $ont_frog['ponelement']);
		}
	}
	$sql_this_onu = $db->SimpleWhile('SELECT status, idonu, mac, sn, olt, inface, type, rx, dist, added, name FROM `onus` WHERE olt ="' . $oltid . '"');
	if(isset($sql_this_onu) && count($sql_this_onu) > 0) {
		echo '<br><table class="resp-tab"><thead><tr>
			<th>Mac_SN</th>
			<th>'.$lang['inface'].'</th>
			<th width="10%">'.$lang['dist'].'</th>
			<th width="10%">'.$lang['signal'].'</th>
			<th>'.$lang['added'].'</th>
			</tr></thead><tbody>';
		foreach ($sql_this_onu as $onu) {
			$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:null));
			if(isset($frog_onus[$onukey])){
				
			}else{
			echo'<tr><td class="td_url"><a href="/?do=onu&id='.$onu['idonu'].'">'.($onu['status']==2?'<font color="grey">':'').''.$onukey.'
			'.(isset($onu['name']) ? ' '.$onu['name']:'').'
			</a></td>
				<td class="td_url"><font color="grey">'.strtoupper($onu['type']).' '.$onu['inface'].'</td>
				<td class="td_url">'.$onu['dist'].'</td>
				<td class="bad_list">'.$onu['rx'].' dBm</td>
				<td class="td_url">'.$onu['added'].'</td>
				</tr>';
			}
		}
		echo '</table>';
	}
}
?>
