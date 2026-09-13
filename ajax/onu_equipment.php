<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if(isset($confPMon['ONU_EQUIPMENT']) && !empty($confPMon['ONU_EQUIPMENT']) && $confPMon['ONU_EQUIPMENT'] == 1) {
	if($_POST['id']){
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
		if($id>0){
			$get_ont = $db->Fast('onus','*',['idonu' => $id]);
		}
		if(!empty($get_ont['idonu'])){			
			$sqldev = $db->SimpleWhile("SELECT * FROM onus_equipment WHERE idonu = ".$get_ont['idonu']);
			if (!empty($sqldev)) {
				echo '<table class="resp-tab"><thead><tr>
					<th>'.$lang['oid_types'].'</th>
					<th>'.$lang['modeldevice'].'</th>
					<th width="20%">Mac, Sn</th>
					<th width="20%">Ip</th>
					</tr></thead><tbody>';
				foreach ($sqldev as $device) { 
					echo '<tr>
						<td>'.type_device_after_onu($device['device']).' <a href="/?do=onu&id='.$get_ont['idonu'].'&act=del_device"><img class="delet_img" src="../style/img/close.png"></a></td>
						<td class="text_left td_url line-height-15">
							<font color="#222">'.$device['name'].'</font><br>'.(isset($device['device_id']) && $device['device_id']>0 ? '<a class="linker" href="/?do=detail&act=switch&id='.$device['device_id'].'">' : '').$device['model'].'';
					if(isset($device['device_id']) && $device['device_id']>0){
						echo'<img class="link_fiber" src="../style/img/link.png"></a>';
					}
					echo '</td>
						<td class="line-height-15">';
					if (isset($device['mac']) && $device['port'] > 0) {
						echo '<font color="#3096e7">'.$device['mac'].'</font><br>';
					}
					if ($device['port'] > 0) {
						echo '<font color="#222">'.$lang['count_port_ports'].':</font> '.$device['port'];
					}
					echo '</td><td>';
						if (isset($device['netip'])) {
							echo '<font color="#222">'.$device['netip'].'</font>';
						}
					echo '</td>';
					echo '</tr>';
				}
				echo '</tbody></table>';
			}			
			echo'<div class="pole-add">';
			echo'<span class="padded" onclick="connect_to_onu('.$get_ont['idonu'].')"><img src="../style/img/menu_down.png">'.$lang['connect'].'<span>';
			echo'</div>';		
		}
	}
}
?>

