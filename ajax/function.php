<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;
$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']): null;
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$types = isset($_POST['types']) ? Clean::text($_POST['types']): null;
$name = isset($_POST['name']) ? Clean::text($_POST['name']): null;
switch($act){
	case 'fdbmac':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			die('<div id="ajaxablock"><div class="blockmac">'.$dataonu['result'].'</div></div>');
		}
		break;	
	case 'inspector':			
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		if(!empty($sqlonus['idonu'])){
			if(isset($types) && !empty($types)){
				if($types=='connect'){
					$inspector = 2;
				}elseif($types=='disconnect'){
					$inspector = 1;
				}
			}
			if(isset($inspector) && !empty($inspector)){
				$db->query("UPDATE onus SET inspector = '{$inspector}' WHERE idonu  = {$sqlonus['idonu']}");
			}
			if($inspector==2){
				echo '<span class="ont-btn" style="background: red;" onclick="inspector('.$sqlonus['idonu'].',\'disconnect\')">'.$lang['disconnect'].'</span>';
			}else{
				echo '<span class="ont-btn" onclick="inspector('.$sqlonus['idonu'].',\'connect\')">'.$lang['connect'].'</span>';
			}			
		}
	break;	
	case 'reboot_pon':		
		$sqlswitch = $db->Fast('switch','*',['id'=>$olt]);
		if(!empty($sqlswitch['id'])){
			$port = isset($_POST['port']) ? Clean::int($_POST['port']): null;
			$sql_port_data = $db->Fast('switch_port','id,nameport',['deviceid'=>$olt,'llid'=>$port]);
			$dataonu = $pmon->init($act,['olt' => $sqlswitch['id'],'nameport' => $sql_port_data['nameport']]);
			preg_match('/(\d+)\/(\d+)\/(\d+)/i',$sql_port_data['nameport'],$match);
			$logger->init([
				'log'=>'device','type'=>'telnet','descr'=>"reset-card rackno {$match[1]} shelfno {$match[2]} slotno {$match[3]}",
				'deviceid'=>$sqlswitch['id'],'userid'=>$USER['id'],'username'=>$USER['username']]);
		}
		break;		
	case 'reboot_onu':		
		$sqlswitch = $db->Fast('switch','*',['id'=>$olt]);
		if(!empty($sqlswitch['id'])){
			$port = isset($_POST['port']) ? Clean::text($_POST['port']): null;
			preg_match('/(\d+)\/(\d+)\/(\d+)/i',$port,$match);
			$get = array('sw_shelf' => $match[1],'sw_slot' => $match[2],'sw_port' => $match[3],'olt' => $sqlswitch['id']);
			$get_onu = $db->Multi('onus','type,inface',$get);
			if(isset($get_onu)){
				$dataonu = $pmon->init($act,['olt' => $sqlswitch['id'],'list' => $get_onu]);
				$logger->init([
					'log'=>'device','type'=>'telnet',
					'descr'=>"reboot onu rackno {$match[1]} shelfno {$match[2]} slotno {$match[3]}",
					'deviceid'=>$sqlswitch['id'],'userid'=>$USER['id'],'username'=>$USER['username']]);
				echo 'ok';
			}
		}
		break;	
	case 'speedprofile':	
		$speedprofile = isset($_POST['speedprofile']) ? Clean::int($_POST['speedprofile']): null;
		$profile_data = $db->Fast('bdcom_epon_onu_pir','*',['id'=>$speedprofile]);
		$dataonu = $pmon->init($act,['idonu' => $idonu,'u_pir' => $profile_data['u_pir'],'u_cir' => $profile_data['u_cir'],'d_cir' => $profile_data['d_cir'],'d_pir' => $profile_data['d_pir']]);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				$sqlonus = $db->Fast('onus','idonu,olt,mac,inface',['idonu'=>$idonu]);
				echo 'ok';
				$logger->init([
					'log'=>'onu',
					'type'=>'speedprofile',
					'descr'=>$lang['edit_profile_speed'].': '.$profile_data['name'].', '.$sqlonus['inface'],
					'deviceid'=>$sqlonus['olt'],
					'onuid'=>$sqlonus['idonu'],
					'userid'=>$USER['id'],
					'username'=>$USER['username']
				]);
			}else{
				echo $dataonu['status'];
			}			
		break;	
	case 'configonu':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			die('<div id="ajaxablock"><div class="configonu">'.$dataonu['result'].'</div></div>');
		}
		break;		
	case 'configonuzte3':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(isset($dataonu)){
			echo('<div id="ajaxablock"><div class="configonu">'.$dataonu['result'].'</div></div>');
			exit;
		}
		break;		
	case 'detailinfoonuzte3':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(isset($dataonu)){
			echo('<div id="ajaxablock"><div class="configonu">'.$dataonu['result'].'</div></div>');
			exit;
		}
		break;	
	case 'rebootonu':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;	
	case 'blacklist11':
        $dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logger->init(['log'=>'onu','type'=>'addblacklist','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				delete_onu($idonu);
				echo 'ok';
			}else{
				echo $dataonu['status'];
			}
        }
		break;	
	case 'blacklist12':
        $dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logger->init(['log'=>'onu','type'=>'addblacklist','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		break;
	case 'hideonu':
		if(!empty($USER['id'])){
			if($USER['hideonu']=='yes'){
				$hideonu = 'no';
			}else{
				$hideonu = 'yes';
			}
			$db->SQLupdate('users',['hideonu'=>$hideonu],['id'=>$USER['id']]);
		}
		break;
	case 'deletonucdata11':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logger->init(['log'=>'onu','type'=>'addblacklist','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			delete_onu($sqlonus['idonu']);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;
		break;	
	case 'deletonucdata12':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logger->init(['log'=>'onu','type'=>'addblacklist','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			delete_onu($sqlonus['idonu']);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;
		break;	
	case 'deletonuzte6':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logger->init(['log'=>'onu','type'=>'deletonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			delete_onu($sqlonus['idonu']);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;
		break;	
	case 'deletonuzte3':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(isset($dataonu)){
			$logger->init(['log'=>'onu','type'=>'deletonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			if(isset($dataonu['status'])){
				delete_onu($idonu);
				echo 'ok';
			}
        }
		exit;
		break;	
	case 'resetonuzte3':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logger->init(['log'=>'onu','type'=>'addblacklist','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			delete_onu($sqlonus['idonu']);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;
		break;	
	case 'deletsnmponubdcomepon':	
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);	
		if(isset($sqlonus)){
			$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
			$inf = $sqlonus['portolt'].'.'.hex_mac_bdcom($sqlonus['mac']);
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.11.1.1.2.".$inf, 'i', "0");
			sleep(2);
			$logs = [
				'log'=>'onu','type'=>'deletonu',
				'descr'=>'SNMP DELET '.$sqlonus['type'].' '.$sqlonus['inface'].' '.$sqlonus['mac'],
				'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'userid'=>$USER['id'],'username'=>$USER['username']
			];
			$logger->init($logs);
			delete_onu($sqlonus['idonu']);
			$sender = 'SNMP DELET '.$sqlonus['type'].' '.$sqlonus['inface'].' '.$sqlonus['mac'].', Username: '.$USER['username'];
			$db->SQLinsert('notification',['status'=>1,'type'=>27,'system'=>'monitor','message'=>$sender,'added'=>date('Y-m-d H:i:s')]);
			echo 'ok';
        }
		break;	
	case 'deletonubdcomepon':	
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);	
        $dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(isset($dataonu)){
			$logs = [
				'log'=>'onu','type'=>'deletonu',
				'descr'=>$sqlonus['type'].$sqlonus['inface'].''.(!empty($sqlonus['mac'])?$sqlonus['mac']:'--'),
				'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
			];
			$logger->init($logs);
			delete_onu($sqlonus['idonu']);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;
		break;	
	case 'gcomeponrebootonu':			
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);	
        $dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logs = [
				'log'=>'onu','type'=>'deletonu',
				'descr'=>$sqlonus['type'].$sqlonus['inface'].''.(!empty($sqlonus['mac'])?$sqlonus['mac']:'--'),
				'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
			];
			$logger->init($logs);
			delete_onu($sqlonus['idonu']);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;	
		break;	
	case 'deletonubdcomgpon':	
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);	
        $dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logs = [
				'log'=>'onu','type'=>'deletonu',
				'descr'=>$sqlonus['type'].$sqlonus['inface'].''.(!empty($sqlonus['mac'])?$sqlonus['mac']:'--'),
				'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
			];
			$logger->init($logs);
			delete_onu($sqlonus['idonu']);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;
		break;		
	case 'zte6port':			
		$dataonu = $pmon->init($act,['idonu' => $idonu,'types' => $types]);
		if(is_array_empty($dataonu)){
			$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
			$logger->init(['log'=>'onu','type'=>'port_'.$types,'descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			echo 'ok';
        }
		break;	
	case 'disableonuzte3':			
		$dataonu = $pmon->init($act,['idonu' => $idonu,'types' => $types]);
		if(is_array_empty($dataonu)){
			$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
			$logger->init(['log'=>'onu','type'=>'disableonuzteadmin','descr'=>'AdminStatus: disable '.$sqlonus['type'].$sqlonus['inface'].' '.(!empty($sqlonus['sn'])?$sqlonus['sn']:(!empty($sqlonus['mac'])?$sqlonus['mac']:'--')),'deviceid'=>$sqlonus['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			echo 'onu';
        }		
		break;		
	case 'enableonuzte3':			
		$dataonu = $pmon->init($act,['idonu' => $idonu,'types' => $types]);
		if(is_array_empty($dataonu)){
			$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
			$logger->init(['log'=>'onu','type'=>'enableonuzteadmin','descr'=>'AdminStatus: enable '.$sqlonus['type'].$sqlonus['inface'].' '.(!empty($sqlonus['sn'])?$sqlonus['sn']:(!empty($sqlonus['mac'])?$sqlonus['mac']:'--')),'deviceid'=>$sqlonus['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			echo 'onu';
        }	
		break;	
	case 'zte3port':	
		$port = isset($_POST['port']) ? Clean::text($_POST['port']): null;
		$dataonu = $pmon->init($act,['idonu' => $idonu,'types' => $types,'port' => $port]);
		if(isset($dataonu)){
			$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
			$logger->init(['log'=>'onu','type'=>'port_'.$types,'descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--')),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			echo 'ok';
        }
		break;		
	case 'bdcomeponportdown':	
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);	
        $dataonu = $pmon->init($act,['idonu' => $idonu,'numport' => $id]);
		if(isset($sqlonus) && isset($dataonu)){
			$logger->init(['log'=>'onu','type'=>'onuportdown','descr'=>$sqlonus['type'].' '.$sqlonus['inface'].' mac:'.$sqlonus['mac'].' port: DOWN','deviceid'=>$sqlonus['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<script>location.reload();</script>');
        }
		break;
	case 'bdcomeponportup':	
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);		
        $dataonu = $pmon->init($act,['idonu' => $idonu,'numport' => $id]);
		if(isset($sqlonus)){
			$logger->init(['log'=>'onu','type'=>'onuportup','descr'=>$sqlonus['type'].' '.$sqlonus['inface'].' mac:'.$sqlonus['mac'].' port: UP','deviceid'=>$sqlonus['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
            die('<script>location.reload();</script>');
        }
		break;	
	case 'bdcomeponrebootonu':			
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu'])){
			$result = @snmp2_set($sqlswitch['netip'], $sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.10.1.1.29.".$sqlonus['keyonu'],'i', "0");
			$status_reboot = valueStringSnmp($result);
			if($status_reboot==1){
				sleep(3);
				$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$lang['err12'].' '.$sqlonus['inface'].' '.$sqlonus['mac'],'deviceid'=>$sqlswitch['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
				die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
			}
		}	
		break;	
	case 'cdata12eponrebootonu':			
		$sqlonus = $db->Fast('onus','inface,idonu,mac,olt,keyonu',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','id,netip,snmprw,oidid',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu'])){
			$result = @snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'],"1.3.6.1.4.1.17409.2.3.4.1.1.17.".$sqlonus['keyonu'],'i', "1");
			$status_reboot = valueStringSnmp($result);
			if($status_reboot==1){
				sleep(3);
				$logger->init([
					'log'=>'onu','type'=>'rebootonu',
					'descr'=>$lang['err12'].' '.$sqlonus['inface'].' '.$sqlonus['mac'],
					'deviceid'=>$sqlswitch['id'],'onuid'=>$idonu,
					'userid'=>$USER['id'],'username'=>$USER['username']]
				);
				die('<div id="ajaxablock">
					<div class="blockmac">'.$lang['err12'].'</div></div>
						<script>location.reload();</script>');
			}
		}	
		break;		
	case 'zte6rebootonusnmp':		
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu'])){
			$result = snmp2_set($sqlswitch['netip'], $sqlswitch['snmprw'], "1.3.6.1.4.1.3902.1082.500.20.2.1.10.1.1.".$sqlonus['portolt'].".".$sqlonus['keyonu'],'i', "1");
			$status_reboot = valueStringSnmp($result);
			if(isset($status_reboot)){
				sleep(3);
				$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$lang['err12'].' '.$sqlonus['inface'].' '.$sqlonus['sn'],'deviceid'=>$sqlswitch['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
				die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
			}
		}
		break;		
	case 'zte3gponrebootonu':		
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu'])){
			$result = snmp2_set($sqlswitch['netip'], $sqlswitch['snmprw'], "1.3.6.1.4.1.3902.1012.3.50.11.3.1.1.".$sqlonus['portolt'].".".$sqlonus['keyonu'],'i', "1");
			$status_reboot = valueStringSnmp($result);
			if(isset($status_reboot)){
				sleep(3);
				$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$lang['err12'].' '.$sqlonus['inface'].' '.$sqlonus['sn'],'deviceid'=>$sqlswitch['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
				die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
			}
		}
		break;		
	case 'bdcomgponrebootonu':			
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu'])){
			$result = @snmp2_set($sqlswitch['netip'], $sqlswitch['snmprw'], "1.3.6.1.4.1.3320.10.3.2.1.4.".$sqlonus['keyonu'],'i', "1");
			$status_reboot = valueStringSnmp($result);
			if(isset($status_reboot)){
				sleep(3);
				$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$lang['err12'].' '.$sqlonus['inface'].' '.$sqlonus['sn'],'deviceid'=>$sqlswitch['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
				die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
			}
		}	
		break;	
	case 'deletonuhuaweigpon':			
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','netip,snmpro,snmprw,id',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu'])){
			$public = $sqlswitch['snmpro'];
			$privat = $sqlswitch['snmprw'];
			$ip = $sqlswitch['netip'];
			$portid = $sqlonus['zte_idport'];
			$onuid = $sqlonus['keyonu'];
			$onu_vlan = @snmp2_get($ip, $public, '1.3.6.1.4.1.2011.6.128.1.1.2.62.1.7.'.$portid.'.'.$onuid.'.1', 100000, 5);
			if(isset($onu_vlan)){
				$uvlan = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_vlan);
				$uvlan = str_replace([' ', '.', '-', ':'], '', $uvlan);
			}
			if(isset($uvlan)){
				$s_vlan = snmp2_get($ip, $public, '1.3.6.1.4.1.2011.5.14.5.5.1.7.'.$portid.'.4.'.$onuid.'.4294967295.4294967295.1.'.intval($uvlan), 100000, 5);
				$sport = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $s_vlan);
				$serv_port = str_replace([' ', '.', '-', ':'], '', $sport);
			}
			if(isset($serv_port)){
				$result = @snmp2_set($ip,$privat,"1.3.6.1.4.1.2011.5.14.5.2.1.15.".$serv_port,'i', "6");
				$result = @snmp2_set($ip,$privat,"1.3.6.1.4.1.2011.6.128.1.1.2.43.1.10.".$portid.".".$onuid."",'i', "6");
				$logs = [
					'log'=>'onu','type'=>'deletonu',
					'descr'=>$sqlonus['type'].$sqlonus['inface'].''.(!empty($sqlonus['sn'])?$sqlonus['sn']:'--'),
					'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
				];
				delete_onu($sqlonus['idonu']);
				$logger->init($logs);
			}
		}	
		break;	
	case 'deletonuhuawei':			
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){
			$logs = [
				'log'=>'onu','type'=>'deletonu',
				'descr'=>$sqlonus['type'].$sqlonus['inface'].''.(!empty($sqlonus['sn'])?$sqlonus['sn']:'--'),
				'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
			];
			$logger->init($logs);
			delete_onu($idonu);
			if(isset($dataonu['status']) && $dataonu['status']=='ok'){
				echo 'ok';
			}
        }
		exit;
		break;	
	case 'huaweigponrebootonu':			
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu'])){
			$result = @snmp2_set($sqlswitch['netip'], $sqlswitch['snmprw'], "1.3.6.1.4.1.2011.6.128.1.1.2.46.1.2.".$sqlonus['zte_idport'].".".$sqlonus['keyonu'],'i', "1");
			$status_reboot = valueStringSnmp($result);
			if($status_reboot==1){
				sleep(3);
				$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$lang['err12'].' '.$sqlonus['inface'].' '.$sqlonus['sn'],'deviceid'=>$sqlswitch['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
				die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
			}
		}	
		break;
	case 'bdcomeponcatv':			
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		$status = (int)isset($_POST['port']) ? Clean::int($_POST['port']): null;
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && $status && !empty($sqlonus['idonu'])){
			snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.10.30.1.2.".$sqlonus['keyonu'], 'i', $status);
		}
		break;
	case 'cdata12formeditsave':		
		$sqlonus = $db->Fast('onus','idonu,keyonu',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','snmprw,netip,id',['id'=>$sqlonus['olt']]);
		$vlan = (int)isset($_POST['vlan']) ? Clean::int($_POST['vlan']): null;
		$eth = (int)isset($_POST['eth']) ? Clean::int($_POST['eth']): 1;
		$mode = (int)isset($_POST['mode']) ? Clean::int($_POST['mode']): 1;
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && $vlan>0 && !empty($sqlonus['idonu'])){	
			// save vlan
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.17409.2.3.7.3.1.1.7.".$sqlonus['keyonu'].".0.".$eth, 'i', $vlan);
			// wr all
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.17409.2.3.1.1.14.0",'i',1);
		}
		break;
	case 'bdcomrenameport':	
		$sqlswitch = $db->Fast('switch','*',['id'=>$id]);
		$llid = (int)isset($_POST['llid']) ? Clean::int($_POST['llid']): 0;
		$save = (int)isset($_POST['save']) ? Clean::int($_POST['save']): 0;
		$name = transliterateAndSanitize($name);
		if(!empty($sqlswitch['id']) && $sqlswitch['oidid']==1 
			&& !empty($sqlswitch['snmprw']) && isset($name) && !empty($llid) && $llid>0){	
			$result = @snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'],"1.3.6.1.2.1.31.1.1.1.18.".$llid,'s',$name);
			if ($result != false && isset($llid)){
				$db->query("UPDATE switch_port SET descrport = '{$name}' WHERE llid  = '{$llid}' AND deviceid  = '{$sqlswitch['id']}' ");
			}
			if(isset($save) && $save>0){
				@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.20.15.1.1.0", 'i', "1");
			}
			sleep(1);
		}
		exit;
		break;	
	case 'bdcomformeditsave':	
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		$vlan = (int)isset($_POST['vlan']) ? Clean::int($_POST['vlan']): null;
		$eth = (int)isset($_POST['eth']) ? Clean::int($_POST['eth']): 1;
		$mode = (int)isset($_POST['mode']) ? Clean::int($_POST['mode']): null;
		$message = $sqlonus['type'].' '.$sqlonus['inface'].' mac:'.$sqlonus['mac'].' VLAN:'.$vlan.' Eth_'.$eth.' mode_'.$mode;
		$logger->init(['log'=>'onu','type'=>'newvlan','descr'=>$message,'deviceid'=>$sqlonus['olt'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && isset($vlan) && !empty($sqlonus['idonu'])){
			$key = $sqlonus['keyonu'];			
			if(isset($mode) && $mode != false){
				@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.12.1.1.18.".$key.".".$eth,'i',$mode);
			}
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.12.1.1.3.".$key.".".$eth,'i',$vlan);
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.20.15.1.1.0",'i',"1");
			sleep(1);
			#$pvid = @snmp2_get($sqlswitch['netip'],$sqlswitch['snmpro'], "1.3.6.1.4.1.3320.101.12.1.1.3.".$key.".".$eth);
			#$tvlan = explode('INTEGER: ', $pvid);
			#$vlans = end($tvlan);
			echo $lang['editto'].' ['.$vlan.']';
			$sender = str_replace('>', ']',str_replace('<', '[',sprintf($lang['editvlan'],$sqlswitch['place'], $sqlonus['inface'], $vlan).' eth_'.$eth));
			$db->SQLinsert('notification',['status'=>1,'type'=>27,'system'=>'monitor','message'=>$sender,'added'=>date('Y-m-d H:i:s')]);
		}
		die;
		break;		
	case 'bdcomrebootonu':	
		$listonu = '';	
		$sqlswitch = $db->Fast('switch','*',['id'=>$id]);
		$count = 1;
		$result = [];
		if(!empty($sqlswitch['id'])){
			$port = isset($_POST['port']) ? Clean::text($_POST['port']): null;
			if(isset($port)){
				$getport = clearpon($port);
				if($port){
					$result_array = snmp2_real_walk($sqlswitch['netip'],$sqlswitch['snmpro'],'1.3.6.1.2.1.2.2.1.2');
					foreach ($result_array as $keys => $arr_value) {
						if (preg_match('/(EPON[0-9]{1,2}\/'.$getport.':)[0-9]{1,2}/',$arr_value,$matches)){
							preg_match('/2.1.2.2.1.2.([\d]+)$/',$keys,$ont);
							@snmp2_set($sqlswitch['netip'], $sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.10.1.1.29.".$ont[1],'i', "0");
							$listonu .= valueStringSnmp($arr_value).', ';
							$count ++;
						}
					}
				}
				if($count){
					okno_title($lang['rebootallonu']);
					echo'<div class="ajax_success">'.$lang['success_reboot_on'].' '.$port.'<b>'.($count-1).'</b></div><br>'.$listonu;
					okno_end();
				}
			}else{
				$result_array = @snmp2_real_walk($sqlswitch['netip'],$sqlswitch['snmpro'],'1.3.6.1.4.1.3320.101.10.1.1.26');
				if($result_array){
					foreach ($result_array as $keys => $arr_value) {
						$arr_value = strtolower(str_replace('INTEGER:','',str_replace(' ','',str_replace('"','',trim($arr_value)))));
						preg_match('/.1.1.26.([\d]+)$/',$keys,$ont);
						if($ont[1] && $arr_value==3){
							snmp2_set($sqlswitch['netip'], $sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.10.1.1.29.".$ont[1], i, "0");
							$count ++;
						}
					}
				okno_title($lang['rebootallonu']);
				echo'<div class="ajax_success">'.$lang['success_reboot_on'].'<b>'.$count.'</b></div>';
				okno_end();
				}
			}
		}
		die;
	break;		
	case 'getmac':	
		$mac_address = isset($_POST['mac']) ? totranslit($_POST['mac']): null;
		if($mac_address){
			$url = "https://api.macvendors.com/" . urlencode($mac_address);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$response = curl_exec($ch);
			echo $response;
		}
		die;
	break;		
	case 'cdata12formeditonu':	
		$sqlswitch = $db->Fast('switch','id,snmpro,netip',['id'=>$id]);
		$sqlonus = $db->Fast('onus','keyonu,idonu',['idonu'=>$idonu]);
		$eth = (int)isset($_POST['eth']) ? Clean::int($_POST['eth']): 1;
		if(!empty($sqlswitch['id']) && !empty($sqlonus['idonu'])){
			$onu_vlan = @snmp2_get($sqlswitch['netip'], $sqlswitch['snmpro'], '1.3.6.1.4.1.17409.2.3.7.3.1.1.7.' . $sqlonus['keyonu'].'.0.1', 100000, 5);
			if ($onu_vlan !== false) {
				$vlan = intval(preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_vlan));
			}
			if(isset($vlan) && $vlan>0){
				echo'<span class="editvlan">
				<input id="eth-'.$eth.'" type="hidden" value="'.$eth.'">					
				<input id="vlan-'.$eth.'" type="text" value="'.$vlan.'">
				<span class="ont-btn" style="margin:0;" onclick="bdcomvlanonu('.$sqlonus['idonu'].',\'cdata12formeditsave\','.$eth.')">
				'.$lang['save'].'
				</span>
				</span>';
			}
		}
	break;		
	case 'bdcomformeditonu':
		$sqlswitch = $db->Fast('switch','*',['id'=>$id]);
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$eth = (int)isset($_POST['eth']) ? Clean::int($_POST['eth']): 1;
		if(!empty($sqlswitch['id']) && !empty($sqlonus['idonu'])){ 
			$pvid = @snmp2_get($sqlswitch['netip'],$sqlswitch['snmpro'],"1.3.6.1.4.1.3320.101.12.1.1.3.".$sqlonus['keyonu'].".".$eth);
			$ptag = @snmp2_get($sqlswitch['netip'],$sqlswitch['snmpro'],"1.3.6.1.4.1.3320.101.12.1.1.18.".$sqlonus['keyonu'].".".$eth);
			if(isset($pvid ) && $pvid!=false){
				$tpvid = explode('INTEGER: ', $pvid);
				$vlansnmp = end($tpvid);
				$ptag = explode('INTEGER: ', $ptag);
				$tag = end($ptag);
				echo'<span class="editvlan"><input id="eth-'.$eth.'" type="hidden" value="'.$eth.'"><select class="select" name="mode-'.$eth.'" id="mode-'.$eth.'" ><option value="0" '.($tag==0?'selected="selected"':'').'>transparent-mode</option><option value="1" '.($tag==1?'selected="selected"':'').'>tag-mode</option><option value="2" '.($tag==2?'selected="selected"':'').'>translation-mode</option><option value="3" '.($tag==3?'selected="selected"':'').'>aggregation-mode</option><option value="4" '.($tag==4?'selected="selected"':'').'>trunk-mode</option><option value="253" '.($tag==253?'selected="selected"':'').'>stacking-mode</option></select><input id="vlan-'.$eth.'" type="text" value="'.$vlansnmp.'"><span class="ont-btn" style="margin:0;" onclick="bdcomvlanonu('.$sqlonus['idonu'].',\'bdcomformeditsave\','.$eth.')">'.$lang['save'].'</span></span>';
			}
		}
		die;
		break;	
	case 'checkerpon':			
		$sqlswitch = $db->Fast('switch','*',['id'=>$id]);
		
		break;	
	case 'savenamezte6s':		
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['snmprw']) && !empty($sqlonus['idonu']) && $sqlswitch['oidid']==6){ 
			$result = snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3902.1082.500.10.2.3.3.1.2.".$sqlonus['zte_idport'].".".$sqlonus['keyonu'],'s',$name);
			if ($result == 1 && isset($name) && $sqlonus['idonu']>0){
				$db->query("UPDATE onus SET name = '{$name}' WHERE idonu  = {$sqlonus['idonu']}");
				$logs = [
					'log'=>'onu','type'=>'renameonu',
					'descr'=> $lang['edit_note'].' '.$sqlonus['type'].' '.$sqlonus['inface'].' '.$sqlonus['sn'].' '.$name,
					'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
				];
				$logger->init($logs);
			}
		}
		die;
		break;	
	case 'savenamezte':
		$sqlonus = $db->Fast('onus','*',['idonu'=>$id]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlonus['idonu']) && ($sqlswitch['oidid']==7 || $sqlswitch['oidid']==34 ) && $sqlonus['type']=='gpon'){ 
			$result = @snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3902.1012.3.28.1.1.2.".$sqlonus['zte_idport'].".".$sqlonus['keyonu'],'s',$name);
			print_r($result);
		}
		die;
		break;	
	case 'savenamebdcomepon':		
		$idonu = (int)isset($_POST['idonu']) ? Clean::int($_POST['idonu']): false;
		$sqlonus = $db->Fast('onus','idonu,olt,keyonu,type,inface,mac',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','id,netip,snmprw,oidid',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlonus['idonu']) && $sqlswitch['oidid']==1){ 
			$result = @snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.2.1.31.1.1.1.18.".$sqlonus['keyonu'],'s',$name);		
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.20.15.1.1.0", 'i', "1");
			if ($result !== false && isset($name) && $sqlonus['idonu']>0){
				$db->query("UPDATE onus SET name = '{$name}' WHERE idonu  = {$sqlonus['idonu']}");
			}
			$logs = [
				'log'=>'onu','type'=>'renameonu',
				'descr'=> $lang['edit_note'].' '.$sqlonus['type'].' '.$sqlonus['inface'].' '.$sqlonus['mac'].' '.$name,
				'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
			];
			$logger->init($logs);
		}
		die;	
		break;	
	case 'savenamecdata12':
		$idonu = (int)isset($_POST['idonu']) ? Clean::int($_POST['idonu']): false;
		$sqlonus = $db->Fast('onus','idonu,olt,keyonu,type,inface,mac',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','id,netip,snmprw,oidid',['id'=>$sqlonus['olt']]);
		if(!empty($sqlswitch['id']) && !empty($sqlonus['idonu']) && $sqlswitch['oidid']==15){ 
			$result = @snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.17409.2.3.4.1.1.2.".$sqlonus['keyonu'],'s',$name);		
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.17409.2.3.1.1.14.0",'i',1);
			if ($result !== false && isset($name) && $sqlonus['idonu']>0){
				$db->query("UPDATE onus SET name = '{$name}' WHERE idonu  = {$sqlonus['idonu']}");
			}
			$logs = [
				'log'=>'onu','type'=>'renameonu',
				'descr'=> $lang['edit_note'].' '.$sqlonus['type'].' '.$sqlonus['inface'].' '.$sqlonus['mac'].' '.$name,
				'deviceid'=>$sqlonus['olt'],'onuid'=>$sqlonus['idonu'],'who'=>'user',
			];
			$logger->init($logs);
		}
		die;
		break;
}
?>
