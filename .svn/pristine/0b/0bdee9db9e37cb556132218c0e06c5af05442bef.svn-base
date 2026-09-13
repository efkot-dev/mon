<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$llid = isset($_POST['llid']) ? Clean::int($_POST['llid']): null;
$core = isset($_POST['core']) ? Clean::int($_POST['core']): null;
switch($act){
	case 'descr':
		if($access->get('lock_port')){	
			okno_title($lang['add_descr']);
			echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveportdescr"><input name="id" type="hidden" value="'.$id.'">'.form(['name'=>$lang['note_port'],'descr'=>$lang['descr_note_port'],'pole'=>'<textarea class="textarea1" rows="7" name="descrport"></textarea>']);
			if($access->get('lock_port')){
				$sqlswitchport = $db->Fast('switch_port','lockport',['id'=>$id]);				
				$port = '<select class="select" name="lock" id="format">
				<option value="no"></option>
				<option value="yes" '.($sqlswitchport['lockport']=='yes'?'selected=""':'').'>'.$lang['lock'].'</option>
				<option value="no" '.($sqlswitchport['lockport']=='no'?'selected=""':'').'>'.$lang['unlock'].'</option>
				</select>';
				echo form(['name'=>$lang['lock_port'],'descr'=>$lang['descr_lock_port'],'pole'=>$port]);
			}
			echo'<span class="js_replace"></span>			
			</form>';			
			echo'<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
			okno_end();
	}
	break;	
	case 'monitorport':		
		if($access->get('connectport') && isset($id) && $id>0){
			$port = $db->Simple("SELECT * FROM switch_port WHERE deviceid = '{$id}' AND llid = '{$llid}' LIMIT 1");
			if(isset($port['signal']) && $port['signal']=='yes'){
				$signal = 2;
			}else{
				$signal = 1;
			}
			if(isset($port['id']) && !empty($port['id'])){
				$db->query("UPDATE switch_port SET `signal` = '{$signal}' WHERE id  = {$port['id']}");
			}			
		}	
		echo'ok';
		die();		
	break;	
	case 'bdcomeponportedit':	
		if($access->get('note_port') && isset($id) && $id>0){
			$sqlswitchport = $db->Fast('switch_port','*',['id'=>$id]);
			$sql_switch = $db->Fast('switch','id,snmpro,oidid',['id'=>$sqlswitchport['deviceid']]);
			okno_title($lang['add_descr']);
			$llid = htmlspecialchars($sqlswitchport['llid']);
			$deviceid = htmlspecialchars($sqlswitchport['deviceid']);
			$port = '<select class="select" name="port_save" id="port_save" >
					<option value="0">no save</option>
					<option value="1">save</option>
					</select>';
			echo '<form action="/" id="formadd">
				<input name="act" type="hidden" value="saveportdescr">
				<input name="id" type="hidden" value="' . $deviceid . '">
				<input name="llid" id="port_llid" type="hidden" value="' . $llid . '">' .
				form([
					'name' => $lang['opis'],
					'descr' => $lang['descr_note_port'],
					'pole' => '<input required="" name="name" id="port_name" class="input1" type="text">'
				]) .
				form(['name'=>'Write All','descr'=>'BDCOM EPON','pole'=>$port]).				
			'</form>';			
			echo '<div class="polebtn">
				<button type="button" form="formadd" value="submit" onclick="rename_port_bdcom(' . $deviceid . ',\'bdcomrenameport\')">' . $lang['add'] . '</button>
			</div>';
			okno_end();
		}
	break;	
	case 'control':	
		if($access->get('shutdown_port')){
			$port = isset($_POST['port']) ? Clean::int($_POST['port']): null;
			$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;
			if(isset($port) && isset($olt) && $olt>0 && $port>0){
				$sql_switch = $db->Fast('switch','id,snmpro,oidid',['id'=>$olt]);
				if(isset($sql_switch) && !empty($sql_switch['oidid']) && $sql_switch['oidid']==7){
					echo'<div class="zte_pon" id="olt-'.$port.'">';
					echo'<span onclick="console_pmon(\'reboot_pon\','.$olt.','.$port.');">'.$pmonimg['svg']['refresh'].'Reboot Pon</span>';
					#echo'<span onclick="console_pmon(\'reboot_onu\','.$olt.','.$port.');">'.$pmonimg['svg']['refresh'].'Reboot Onu</span>';
					echo'</div>';
				}
			}
		}
	break;	
	case 'port':
		if($access->get('shutdown_port')){	
			$port = isset($_POST['port']) ? Clean::int($_POST['port']): null;
			$sqlswitchport = $db->Fast('switch_port','*',['id'=>$port]);
			$switch = isset($_POST['switch']) ? Clean::int($_POST['switch']): null;
			$sqlswitch = $db->Fast('switch','*',['id'=>$switch]);
			$status = isset($_POST['status']) ? Clean::text($_POST['status']): null;
			$data = array(
				'id'=>$sqlswitch['id'],
				'username'=>$sqlswitch['username'],
				'netip'=>$sqlswitch['netip'],
				'password'=>$sqlswitch['password'],
				'oidid'=>$sqlswitch['oidid'],
				'interface'=>$sqlswitchport['nameport'],
				'typeport'=>$sqlswitchport['typeport'],
				'status_port'=>$status,
			);
			$datastatus = $pmon->port('shutdown',$data);
			if($datastatus){
				sendData('poller','curl',['olt'=>$sqlswitch['id'],'jobid'=>104]);
				echo 'ok';
			}else{
				echo'err';
			}
		}else{
			echo'err';
		}
	break;		
	case 'status':	
		$port = isset($_POST['port']) ? Clean::int($_POST['port']): null;
		$device = isset($_POST['device']) ? Clean::int($_POST['device']): null;
		$types = isset($_POST['types']) ? Clean::text($_POST['types']): null;
		$sqlswitch = $db->Fast('switch','*',['id'=>$device]);
		$sqlswitchport = $db->Fast('switch_port','*',['id'=>$port]);
		$llidport = $sqlswitchport['llid'];
		$snmp_status = @snmp2_get($sqlswitch['netip'], $sqlswitch['snmpro'], '1.3.6.1.2.1.2.2.1.7.'.$llidport, $timeout, $retries);
		$status = strtolower(str_replace('INTEGER:', '', str_replace(' ', '', str_replace('"', '', trim($snmp_status)))));
		if(isset($status)){
		echo '
			<label class="checkbox-ya">
				<input type="checkbox" '.($status==1?'checked':'').'  onclick="sendDataOnChange(this)">
					<span class="checkbox-ya-switch">
						<span class="checkbox-ya-feature" data-label-on="Вкл" data-label-off="Викл" data-switch="'.$sqlswitch['id'].'" data-status="'.($status==1?'shutdown':'noshutdown').'" data-port="'.$port.'"></span>
				</span>
			</label>';
		}
	break;	
	case 'edit':
		if($access->get('note_port')){
			$getPort = $db->Fast('switch_port','lockport,deviceid,descrport',['id'=>$id]);
			if(!empty($getPort['descrport'])){
				okno_title($lang['edit_descr']);
				echo'<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveportdescr"><input name="id" type="hidden" value="'.$id.'">';
				$textresult = '<textarea class="textarea1" rows="7" name="descrport">'.$getPort['descrport'].'</textarea>';
				echo form(['name'=>$lang['note_port'],'descr'=>'','pole'=>$textresult]);
				if($access->get('lock_port')){
					$port = '<select class="select" name="lock" id="format">
					<option value="no"></option>
					<option value="yes" '.($getPort['lockport']=='yes'?'selected=""':'').'>'.($getPort['lockport']=='yes'?$lang['lockend']:$lang['lock']).'</option>
					<option value="no" '.($getPort['lockport']=='no'?'selected=""':'').'>'.$lang['unlock'].'</option>
					</select>';
					echo form(['name'=>$lang['lock_port'],'descr'=>$lang['descr_lock_port'],'pole'=>$port]);
				}
				echo'<span class="js_replace"></span></form><div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
				okno_end();
			}
		}
	break;	
}
?>