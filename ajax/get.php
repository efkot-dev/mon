<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$idonu = isset($_POST['idonu']) ? Clean::int($_POST['idonu']): null;
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
switch($act){
	case 'bdcomeponrebootonu':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;	
	case 'bdcomgponrebootonu':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['sn'])?$dataonu['result']['sn']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;			
	case 'bdcomeponportup':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$logger->init(['log'=>'onu','type'=>'upportonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;	
	case 'bdcomeponportdown':	
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$logger->init(['log'=>'onu','type'=>'downportonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;			
	case 'bdcomepondeletonu':	
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			delete_onu($idonu);
			$logger->init(['log'=>'onu','type'=>'deletonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;	
	case 'bdcomeponfdb':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			die('<div id="ajaxablock"><div class="blockmac">'.$dataonu['result'].'</div></div>');
		}
		break;		
	case 'bdcomgponfdb':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			die('<div id="ajaxablock"><div class="blockmac">'.$dataonu['result'].'</div></div>');
		}
		break;	
	case 'bdcomformeditsave':	
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		$sqlswitch = $db->Fast('switch','*',['id'=>$sqlonus['olt']]);
		$vlan = (int)isset($_POST['vlan']) ? Clean::int($_POST['vlan']): null;
		if(!empty($sqlswitch['id']) && !empty($sqlswitch['snmprw']) && $vlan && !empty($sqlonus['idonu'])){	
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.12.1.1.18.".$sqlonus['keyonu'].".1", 'i', "1");
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.101.12.1.1.3.".$sqlonus['keyonu'].".1", 'i', $vlan);
			@snmp2_set($sqlswitch['netip'],$sqlswitch['snmprw'], "1.3.6.1.4.1.3320.20.15.1.1.0", 'i', "1");
			sleep(1);
			$pvid = @snmp2_get($sqlswitch['netip'],$sqlswitch['snmpro'], "1.3.6.1.4.1.3320.101.12.1.1.3.".$sqlonus['keyonu'].".1");
			$tvlan = explode('INTEGER: ', $pvid);
			$vlans = end($tvlan);
			echo' -> '.$vlans;
		}
		die;
		break;	
	case 'bdcomformeditonu':
		$sqlswitch = $db->Fast('switch','*',['id'=>$id]);
		$sqlonus = $db->Fast('onus','*',['idonu'=>$idonu]);
		if(!empty($sqlswitch['id']) && !empty($sqlonus['idonu'])){ 
			$pvid = snmp2_get($sqlswitch['netip'],$sqlswitch['snmpro'], "1.3.6.1.4.1.3320.101.12.1.1.3.".$sqlonus['keyonu'].".1");
			if($pvid){
				$tpvid =explode('INTEGER: ', $pvid);
				$vlansnmp = end($tpvid);
				echo'<span class="editvlan"><input id="vlan" type="text" value="'.$vlansnmp.'"><span class="ont-btn" style="margin:0;" onclick="bdcomvlanonu('.$sqlonus['idonu'].',\'bdcomformeditsave\')">'.$lang['save'].'</span></span>';
			}
		}
		die;
		break;
	case 'zte3gponfdb':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			die('<div id="ajaxablock"><div class="blockmac">'.$dataonu['result'].'</div></div>');
		}
		break;
	case 'ztegponrebootonu':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;	
	// CDATA 12
	case 'cdata12eponfdb':
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			die('<div id="ajaxablock"><div class="blockmac">'.$dataonu['result'].'</div></div>');
		}
		break;
	case 'cdata12eponblacklistdel':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$db->SQLupdate('onus',['module1'=>1],['idonu'=>$idonu]);
			$logger->init(['log'=>'onu','type'=>'blacklistadd','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['blacklistdel'].'</div></div><script>location.reload();</script>');
		}
		break;		
	case 'cdata12eponblacklistadd':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$db->SQLupdate('onus',['module1'=>666],['idonu'=>$idonu]);
			$logger->init(['log'=>'onu','type'=>'blacklistadd','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['blacklistadd'].'</div></div><script>location.reload();</script>');
		}
		break;	
	case 'cdata12eponreboot':		
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			$logger->init(['log'=>'onu','type'=>'rebootonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;
	case 'cdata12epondelet':	
		$dataonu = $pmon->init($act,['idonu' => $idonu]);
		if(is_array_empty($dataonu)){ 
			delete_onu($idonu);
			$logger->init(['log'=>'onu','type'=>'deletonu','descr'=>$dataonu['result']['type'].$dataonu['result']['inface'].' '.(!empty($dataonu['result']['mac'])?$dataonu['result']['mac']:'--'),'deviceid'=>$dataonu['result']['id'],'onuid'=>$idonu,'userid'=>$USER['id'],'username'=>$USER['username']]);
			die('<div id="ajaxablock"><div class="blockmac">'.$lang['err12'].'</div></div><script>location.reload();</script>');
		}
		break;			
}
?>