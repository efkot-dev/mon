<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');
require_once ENGINE_DIR.'ajax.php';
if(isset($_POST['id'])){
$id = isset($_POST['id']) ? (int)Clean::int($_POST['id']) : 0;           // FIX: int
if ($id <= 0) { exit; } 
$sql_ont = $pdo->prepare("SELECT * FROM onus WHERE idonu = :idonu");
$sql_ont->execute(['idonu' => $id]);
$data_ont = $sql_ont->fetch(PDO::FETCH_ASSOC);
if(!empty($data_ont['idonu'])){
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $data_ont['olt']]);
$data_olt = $sql_olt->fetch(PDO::FETCH_ASSOC);
$access_olt = snmp_access($data_olt);
if($access_olt==true && !empty($data_olt['netip']) && !empty($data_olt['class'])){
$btn_update_info = '<span class="ajax_update_btn" onclick="Realontdata('.$id.')">'.$lang['update'].'</span>';
$OnuClass = new Ont($data_olt['id'],$data_olt['class'], $db, $logger, $config, $cacheManager, $php_class_device);
$support = $OnuClass->Support();	
if($support){
	$resultONT = $OnuClass->getApi($data_ont);
	// SIGNAL ONT
	if(isset($resultONT['status']) && $resultONT['status']==1){
		if (isset($confPMon['ONU_TRAFFIC']) && !empty($confPMon['ONU_TRAFFIC']) && $confPMon['ONU_TRAFFIC'] == 1) {
			if($data_olt['oidid']==1 || $data_olt['oidid']==2 ){
				$onu_get_traffic = "trafficport('".$data_ont['keyonu']."','".$data_ont['olt']."')";
			}elseif($data_olt['oidid']==7 || $data_olt['oidid']==34 ){				
				$onu_get_traffic = "trafficport('".$data_ont['idonu']."','".$data_ont['olt']."')";	
			}elseif($data_olt['oidid']==14 || $data_olt['oidid']==33 || $data_olt['oidid']==6 ){
				$onu_get_traffic = "trafficport('".$data_ont['idonu']."','".$data_ont['olt']."')";	
			}else{
				$onu_get_traffic = "";
			}
			echo'<script type="text/javascript">'.$onu_get_traffic.'</script>';
		}		
	}
	if (!empty($data_ont['inspector']) && $data_ont['inspector']==2 && isset($confPMon['BANDWIDTH']) && !empty($confPMon['BANDWIDTH']) && $confPMon['BANDWIDTH'] == 1 && $access->get('bandwidth_monitor')){
		$sql_traff_monitor = "SELECT id FROM traff_monitor WHERE idonu = :idonu AND types = 'onu' AND deviceid = :deviceid LIMIT 1";
		$sql_traff = $pdo->prepare($sql_traff_monitor);
		$sql_traff->execute([':idonu'  => $data_ont['idonu'],':deviceid' => $data_ont['olt']]);
		$bandwidth_monitor = $sql_traff->fetch(PDO::FETCH_ASSOC);
	}
	if(isset($resultONT['status']) && ($data_olt['oidid']==7 || $data_olt['oidid']==34) && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxzte320('.$id.');
		</script>
		<div id="ajaxzte320"></div>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==6){
		echo'<script type="text/javascript">
		ajaxzte610('.$id.');
		</script>
		<div id="ajaxzte610"></div>';
	}elseif(isset($resultONT['status']) && ($data_olt['oidid']==14 || $data_olt['oidid']==33)){
		echo'<script type="text/javascript">
		ajaxhuawei5608('.$id.');
		</script>
		<div id="onthuawei5608"></div>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==1 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxbdcomeponsignal('.$id.');
		'.($access->get('edit_pir_sla') ? 'ajaxbdcomeponspeed('.$data_ont['idonu'].');' : '').'
		</script>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==2 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxbdcomgponsignal('.$id.');
		</script>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==3 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxzteponsignal('.$id.');
		</script>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==12 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxcdata16signal('.$id.');
		</script>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==15 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxcdata12signal('.$id.');
		</script>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==9 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxgcomepon('.$id.');
		ajaxgcomeponsignal('.$id.');
		</script>';
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==35 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxcdata16signalv3('.$id.');
		</script>';		
	}elseif(isset($resultONT['status']) && ($data_olt['oidid']==28 || $data_olt['oidid']==29) && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxvsolepon('.$id.');
		</script>';			
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==44 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxcdata17v3('.$id.');
		</script>';			
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==41 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxcdata17('.$id.');
		</script>';		
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==16 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxvsolgpon('.$id.');
		</script>';			
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==36 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxgcomgpon('.$id.');
		</script>';		
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==43 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxnokia73('.$id.');
		</script>';	
	}elseif(isset($resultONT['status']) && $data_olt['oidid']==13 && $resultONT['status']==1){
		echo'<script type="text/javascript">
		ajaxcdata11('.$id.');
		ajaxcdata11signal('.$id.');
		</script>';
	}	
	echo'<div id="ajaxsignal"></div>';
	// SIGNAL ONT	
	if (!empty($resultONT['reason'])) {
		if ($resultONT['status'] == 2) {
			echo '<div class="onu_reasons view_onu_'.$resultONT['reason'].'"><b>'.$lang['epon_reason_onu'].'</b>'.$lang[$resultONT['reason']].(!empty($data_ont['offline']) ? ' <span>'.aftertime($data_ont['offline']).'</span>':'').' (<span>'.$data_ont['offline'].'</span>)</div>';
		} elseif ($resultONT['status'] == 1) {
			echo ont_label($lang['rereason'], tplreason($lang[$resultONT['reason']], $resultONT['reason']));
		} else {
			echo ont_label($lang['rereas'], tplreason($lang[$resultONT['reason']], $resultONT['reason']));
		}
	}else{
		if (isset($resultONT['status']) && $resultONT['status'] == 2) {
			echo '<div class="onu_reasons view_onu_none"><b>'.$lang['epon_reason_onu'].'</b>'.$lang['epon_reason_none'].(!empty($data_ont['offline']) ? ' <span>'.aftertime($data_ont['offline']).'</span>':'').' (<span>'.$data_ont['offline'].'</span>)</div>';
		}
	}
	if(is_array($resultONT)){
		if($data_olt['oidid']==9 && $access->get('dev' . $data_ont['olt']) && isset($data_ont['status']) && !empty($data_ont['status']) && $access->get('vlan_edit')){
			echo ont_label('Change VLAN','<div id="btn-connect-inspector"><span class="ont-btn-vlan" onclick="change_vlan('.$data_ont['idonu'].','.$data_ont['olt'].',\'gcomepon\')">'.$lang['edit'].' Vlan</span></div>','house-ethernet.png');
		}
		if(!empty($resultONT['adminstatus'])){
			$adminstatus =  str_replace('down', 'off',str_replace('up', 'on',$resultONT['adminstatus']));
			echo ont_label('Admin Status','<div class="ajax_ont_status_'.$adminstatus.'">'.$resultONT['adminstatus'].'</div>');
		}		
		if (isset($confPMon['DELETE_ONU']) && !empty($confPMon['DELETE_ONU']) 
			&& $confPMon['DELETE_ONU'] == 1 && isset($USER['class']) && $USER['class']>=4){
			echo ont_label($lang['add_functions'],'<a class="ajax_update_btn" onclick="deletonu('.$data_ont['idonu'].','.$data_ont['olt'].')" href="#"><img style="vertical-align:sub;" src="../style/img/house-ban.png"> '.$lang['delet_onu'].'</a>');
		}	
		echo ont_label($lang['global_signal'],'<a class="ajax_update_btn" href="/?do=signal&id='.$data_ont['idonu'].'"><img style="vertical-align:sub;" src="../style/img/sfpsignal.png"> '.mb_strtoupper($data_ont['type'].' '.$data_ont['inface']).'</a>');
		if (isset($confPMon['BANDWIDTH']) && !empty($confPMon['BANDWIDTH']) 
			&& $confPMon['BANDWIDTH'] == 1 && $access->get('bandwidth_monitor')){
			if(isset($bandwidth_monitor['id']) && !empty($bandwidth_monitor['id']) ){
				echo ont_label($lang['info_onu_traffic'],'<a class="ont-btn"  style="background: #0089db;" href="/?do=bandwidth&act=view&id='.$bandwidth_monitor['id'].'">Переглянути</a>');
			}else{
				echo ont_label($lang['onu_traffic'],'<a class="ont-btn" href="/?do=bandwidth&act=addonu&idonu='.$data_ont['idonu'].'&olt='.$data_ont['olt'].'">'.$lang['connect'].'</a>');
			}
		}
		if(!empty($data_ont['inspector']) && $data_ont['inspector']==2){
			echo ont_label($lang['insector_functions'],'<div id="btn-connect-inspector"><span class="ont-btn" style="background: red;"  onclick="inspector('.$data_ont['idonu'].',\'disconnect\')">'.$lang['disconnect'].'</span></div>');
		}else{
			echo ont_label($lang['insector_functions'],'<div id="btn-connect-inspector"><span class="ont-btn" onclick="inspector('.$data_ont['idonu'].',\'connect\')">'.$lang['connect'].'</span></div>');
		}
		if(!empty($resultONT['operstatus'])){
			$operstatus =  str_replace('down', 'off',str_replace('up', 'on',$resultONT['operstatus']));
			echo ont_label('Operation Status','<div class="ajax_ont_status_'.$operstatus.'">'.$resultONT['operstatus'].'</div>');
		}	
		// BDCOM EPON 
		#if($data_olt['oidid']==1 && isset($data_ont['mac']) && !empty($data_ont['mac'])){
		#	echo ont_label('MAC convert IP','<div id="resname">'.$data_ont['portolt'].'.'.hex_mac_bdcom($data_ont['mac']).'</div>');
		#}		
		// BDCOM GPON 
		if($data_olt['oidid']==2 && $access->get('dev' . $data_ont['olt']) && isset($data_ont['status']) && !empty($data_ont['status']) && $access->get('vlan_edit')){
			echo ont_label('Change VLAN','<div id="btn-connect-inspector"><span class="ont-btn-vlan" onclick="change_vlan('.$data_ont['idonu'].','.$data_ont['olt'].',\'bdcomgpon\')">'.$lang['edit'].' Vlan</span></div>','house-ethernet.png');
			echo ont_label('Basic ONU Information','<div id="btn-connect-inspector"><span class="ont-btn-vlan" onclick="basic_info('.$data_ont['idonu'].','.$data_ont['olt'].')">Show information</span></div>','settings.png');
		}		
		// ZTE GPON 
		if($data_olt['oidid']==34 && $access->get('dev' . $data_ont['olt']) && isset($data_ont['status']) && !empty($data_ont['status']) && $access->get('vlan_edit')){
			echo ont_label('Change VLAN','<div id="btn-connect-inspector"><span class="ont-btn-vlan" onclick="change_vlan('.$data_ont['idonu'].','.$data_ont['olt'].',\'zte3gpon\')">'.$lang['edit'].' Vlan</span></div>','house-ethernet.png');
		}	
		// C-DATA 16xx
		if($data_olt['oidid']==12 && isset($resultONT['name']) && !empty($resultONT['name'])){
			echo ont_label($lang['oid_gpon_descr'],'<div id="resname">'.$resultONT['name'].'</div>');
		}		
		if(!empty($resultONT['status'])){
			$status = ($resultONT['status']==1?'<div class="ajax_ont_status_on">'.$lang['online'].'</div>':'<div class="ajax_ont_status_off">'.$lang['offline'].'</div>');
			echo ont_label($lang['status'],$status.$btn_update_info);
		}	
		if(!empty($data_ont['added'])){
			echo ont_label($lang['registers'],$data_ont['added']);
		}			
		if($resultONT['status']==1 && !empty($resultONT['online'])){
			echo ont_label($lang['online'],aftertime($resultONT['online']));
		}			
		// CDATA 16
		if($data_olt['oidid']==12){	
			if(!empty($resultONT['status']) && !empty($resultONT['wan']) && $resultONT['status']==1){
				$ethstatus = 'onueth_'.(!empty($resultONT['wan']) && $resultONT['wan']=='up' ? 'up' : 'down'); 
				if(!empty($resultONT['wan']))				
					echo ont_label($lang['localport'],'<span class="position-flex"><img style= "height: 26px;" src="../style/img/'.$ethstatus.'.png"><span class="port-'.$ethstatus.'">'.$lang['descr_'.$ethstatus].'</span></span>');
			}
		}	
		// CDATA 12		
		if($data_olt['oidid']==15){		

		}		
		// CDATA 11
		if($data_olt['oidid']==13){		

		}
		// HUAWEI 56xx
		if($data_olt['oidid']==14){
			if(!empty($resultONT['onuerror']))
				echo ont_label($lang['coutporterr'],'<font color=red>'.$resultONT['onuerror'].'</font>');			
			if(!empty($resultONT['name'])){
				$editpenhuawei = ' <span class="zte_edit_name" onclick="showblockform1();"><img src="../style/img/edit.png"></span>';
				$formpenhuawei = '<div id="form_rename"><textarea name="nameonu" id="nameonu" class="namezteonu">'.$resultONT['name'].'</textarea><input type="submit" class="saverenzte" onclick="snmpsetsave('.$id.',\'huaweisavename\');"  value="'.$lang['edit'].'"></div>';
				echo ont_label($lang['opis'],'<div id="resname">'.$resultONT['name'].'</div>'.$editpenhuawei.$formpenhuawei);
			}			
		}		
		if(!empty($resultONT['mac'])){
			echo ont_label('MAC',$resultONT['mac']);	
		}
		// BDCOM GPON
		if($data_olt['oidid']==2){
		// status ETH
		if(!empty($resultONT['status']) && !empty($resultONT['wan']) && $resultONT['status']==1){
			$ethstatus = 'onueth_'.(!empty($resultONT['wan']) && $resultONT['wan']=='down' ? 'off' : 'up'); 
			if(!empty($resultONT['wan']))				
				echo ont_label($lang['localport'],'<span class="position-flex"><img style= "height: 26px;" src="../style/img/'.$ethstatus.'.png"><span class="port-'.$ethstatus.'">'.$lang['descr_'.$ethstatus].'</span></span>');
		}
		if(!empty($resultONT['name']))
			echo ont_label($lang['opis'],$resultONT['name']);				
			if(!empty($resultONT['uptime'])){
				if(preg_match('/^\d+$/', $resultONT['uptime'])) {
					$valuetime = formatUptime($resultONT['uptime']);
				}
				elseif(preg_match('/^\(\d+\)/', $resultONT['uptime'], $matches)) {
					$value = preg_replace('/[^\d]/', '', $matches[0]); // Вилучаємо зайві символи, залишаємо тільки цифри
					$valuetime = formatUptime($value);
				}
				else {
					$valuetime = "Err \$uptime";
				}
				echo ont_label($lang['uptimeport'].' ONU',$valuetime);
			}				
		}		
		// ZTE C3xx		
		if($data_olt['oidid']==34 || $data_olt['oidid']==7 ){
			if(!empty($resultONT['wan']) && $resultONT['status']==1){
				echo ont_label($lang['wanport'],'<img src="../style/img/'.(!empty($resultONT['wanportzte']['txt']) ? $resultONT['wanportzte']['img'] : 'eth'.$resultONT['wan']).'.png" class="onueth">');
				if(is_array($resultONT['wanportzte']) && !empty($resultONT['wanportzte']['txt']))
					echo ont_label($lang['typeport'],$resultONT['wanportzte']['txt']);
			}
			if(!empty($resultONT['mngtvlan'])){
				$editpenztevlan = ' <span class="zte_edit_vlan" onclick="showblockform2();"><img src="../style/img/edit.png"></span>';
				$formpenztevlan = '<div id="formeditvlan"><input type="text" name="vlan" id="vlan" class="namezteonu" value="'.$resultONT['mngtvlan'].'"><input type="submit" class="saverenzte" onclick="snmpsetsave('.$id.',\'savevlanzte\');" value="'.$lang['edit'].'"></div>';
				echo ont_label('Vlan',$resultONT['mngtvlan'].$editpenztevlan.$formpenztevlan);
			}
			if(!empty($resultONT['name'])){
				$editpenzte = ' <span class="zte_edit_name" onclick="showblockform1();"><img src="../style/img/edit.png"></span>';
				$formpenzte = '<div id="form_rename"><textarea name="nameonu" id="nameonu" class="namezteonu">'.$resultONT['name'].'</textarea><input type="submit" class="saverenzte" onclick="snmpsetsave('.$id.',\'savenamezte\');" value="'.$lang['edit'].'"></div>';
				echo ont_label($lang['opis'],'<div id="resname">'.$resultONT['name'].'</div>'.$editpenzte.$formpenzte);	
			}	
			if(!empty($resultONT['note'])){
				$editpenzte = '<span class="zte_edit_note" onclick="showblockform3();"><img src="../style/img/edit.png"></span>';
				$formpenzte = '<div id="form_renote"><textarea name="noteonu" id="noteonu" class="namezteonu">'.$resultONT['note'].'</textarea><input type="submit" class="saverenzte" onclick="snmpsetsave('.$id.',\'savedescrzte\');" value="'.$lang['edit'].'"></div>';
				echo ont_label($lang['opis'],'<div id="resnote">'.$resultONT['note'].'</div>'.$editpenzte.$formpenzte);	
			}
			if(!empty($resultONT['config'])){
				echo ont_label($lang['cfgonuinf'],$resultONT['config']);
			}				
			if(!empty($resultONT['regtime'])){
				echo ont_label('OLT Register Time',$resultONT['regtime']);
			}				
		}
		// SMARTFIBER
		if($data_olt['oidid']==15){
			if(!empty($resultONT['name'])){
				echo ont_label($lang['opis'],'<div id="resname">'.$resultONT['name'].'</div>');	
			}
		}
		if($data_olt['oidid']==10){
			if(!empty($resultONT['name']))
				echo ont_label($lang['opis'],'<div id="resname">'.$resultONT['name'].'</div>');			
			if(!empty($resultONT['wan']))
				echo ont_label('Eth','<img src="../style/img/unit/epon_'.$resultONT['wan'].'.png">');
		}
		// GCOM EPON
		if($data_olt['oidid']==9){
			if(!empty($resultONT['vlan']))
				echo ont_label('Vlan','<div id="resname">'.$resultONT['vlan'].'</div>');			
			if(!empty($resultONT['name']))
				echo ont_label($lang['opis'],'<div id="resname">'.$resultONT['name'].'</div>');
			if(!empty($resultONT['volt']))
				echo ont_label($lang['volt'],$resultONT['volt'].' В');
			if(!empty($resultONT['regtime'])){
				preg_match('/(\d+)\/(\d+)\/(\d+):(\d+):(\d+)/',$resultONT['regtime'],$timeZTE);
				$clockoff = $timeZTE[1].'-'.$timeZTE[2].'-'.mb_substr($timeZTE[3],0,2).' '.mb_substr($timeZTE[3],2,4).':'.$timeZTE[4].':'.$timeZTE[5];
				echo ont_label($lang['regtime'],$clockoff);
			}
			$clientmac = '';
			$clientmac = GCOM_client_mac($data_olt['netip'],$data_olt['snmpro'],$data_ont['zte_idport'],$data_ont['keyonu']);
			if($clientmac){
				echo ont_label($lang['allmac'],$clientmac);
			}
		}
		// BDCOM EPON
		if($data_olt['oidid']==3){
			if(!empty($resultONT['name']))
				echo ont_label($lang['opis'],$resultONT['name']);	
			if(!empty($resultONT['vlanmode']))
				echo ont_label('VlanMode',$resultONT['vlanmode']);			
			if(!empty($resultONT['offline'])){
				preg_match('/(\d+)\/(\d+)\/(\d+):(\d+):(\d+)/',$resultONT['offline'],$timeZTE);
				$clockoff = $timeZTE[1].'-'.$timeZTE[2].'-'.mb_substr($timeZTE[3],0,2).' '.mb_substr($timeZTE[3],2,4).':'.$timeZTE[4].':'.$timeZTE[5];
				echo ont_label($lang['lastoffline'],$clockoff);	
				if($resultONT['status']==2)
					echo ont_label($lang['onuoffline'], aftertime($clockoff));	
			}				
			if(!empty($resultONT['device']))
				echo ont_label($lang['models'],$resultONT['device']);			
		}	
		if(!empty($resultONT['dist'])){
			echo ont_label($lang['dist'],$resultONT['dist'].' '.$lang['metr']);
		}
		$sql_check_history = $db->Simple("SELECT count(id) as count FROM historysignal WHERE onu = {$id} AND device = ".$data_ont['olt']);
		if(isset($resultONT['rx']) && !empty($resultONT['rx']) && $resultONT['status']==1 && $resultONT['rx']>0){
			echo ont_label('RX ',signalTerminal(!empty($resultONT['rx'])?$resultONT['rx']:0).(!empty($sql_check_history['count'])?'<a href="/?do=signal&id='.$id.'" class="ont-graph-rx">'.$lang['keygraphsignal'].'</a>':''));
		}else{
			if(!empty($resultONT['rx']))
				echo ont_label('RX ',signalTerminal(!empty($resultONT['rx'])?$resultONT['rx']:0).(!empty($sql_check_history['count'])?'<a href="/?do=signal&id='.$id.'" class="ont-graph-rx">'.$lang['keygraphsignal'].'</a>':''));
		}
		if(!empty($resultONT['lastrx']))
			echo ont_label($lang['lastrx'],signalTerminal($resultONT['lastrx']));		
		if($data_ont['rxstatus']=='up' || $data_ont['rxstatus']=='down')
			echo ont_label($lang['changerx'],$data_ont['changerx']);
		$add_monitor_temp = '';
		if(!empty($resultONT['temp']) && $resultONT['status']==1){
			if($data_olt['oidid']==1){
				$oid = '1.3.6.1.4.1.3320.101.10.5.1.2.'.$data_ont['keyonu'];
			}
			if (isset($confPMon['TEMPERATURE_MONITOR']) && !empty($confPMon['TEMPERATURE_MONITOR']) && $confPMon['TEMPERATURE_MONITOR']==1) {
				$add_monitor_temp = '<a href="/?do=temp&act=add&d='.$data_ont['olt'].'&y='.$data_ont['idonu'].'&t=onu&o='.$oid.'">
					<img style="vertical-align: sub;" src="/style/img/add.png"></a>';
			}
			echo ont_label($lang['temp'],($resultONT['temp']>=50?'<font color=red>':'<font color="#0060e0">').$resultONT['temp'].'</font> °C'.$add_monitor_temp);
		}
		if(!empty($resultONT['model']) || !empty($resultONT['vendor'])){
			echo ont_label($lang['model'],(!empty($resultONT['vendor']) ? $resultONT['vendor']:'').' '.(!empty($resultONT['model'])?$resultONT['model']:''));
		}
		if (isset($confPMon['FIBERMAP']) && !empty($confPMon['FIBERMAP']) && $confPMon['FIBERMAP'] == 1) {
			$onukey = !empty($data_ont['mac']) 
			? $data_ont['mac'] 
			: (!empty($data_ont['sn']) ? $data_ont['sn'] : null);
			if ($onukey !== null) {
				$onusdata = $pdo->prepare("SELECT * FROM onusdata WHERE onukey = :onukey LIMIT 1");
				$onusdata->execute([':onukey' => $onukey]);
				$data = $onusdata->fetch(PDO::FETCH_ASSOC);
			} else {
				$data = false;
			}
			if(empty($data['pontree'])){
				echo ont_label($lang['fiber_pon'],'<div id="btn-connect-fibermap"><span class="ont-btn" onclick="fibermap('.$data_ont['idonu'].',\'connectpon\')">'.$lang['connect'].'</span></div>');
			}
		}
	}else{
		#delete_onu($data_ont['idonu']);
		#die('<script>location.reload();</script>');
	}
}
}else{
	div_err_snmp();
}
}
}
?>