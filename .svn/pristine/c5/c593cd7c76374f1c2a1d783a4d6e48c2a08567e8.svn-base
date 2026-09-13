<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ENGINE_DIR.'functions/regonu.php';
require ENGINE_DIR.'classes/telnet.class.php';
require ENGINE_DIR.'classes/system.class.php';
if(!$access->get('blacklist') && $getSwitch['oidid']!=15 && !$access->get('delblacklist')){
	$go->redirect('main');		
}else{
$result = '';
$navigation = '';
switch($act){
	case 'del';	
		$mac = isset($_POST['data']['mac'])?Clean::text($_POST['data']['mac']) : null;
		$keyport = isset($_POST['data']['keyport'])?Clean::text($_POST['data']['keyport']) : null;
		$keyonu = isset($_POST['data']['keyonu'])?Clean::int($_POST['data']['keyonu']) : null;
		$keyport = isset($_POST['data']['keyport'])?Clean::int($_POST['data']['keyport']) : null;
		$olt = isset($_POST['data']['olt'])?Clean::int($_POST['data']['olt']) : null;
		$getswitch = $db->Fast('switch','*',['id'=>$olt]);
		if(!empty($getswitch['username']) && !empty($getswitch['password'])){
			$telnet = new PMonTelnet($getswitch);	
			$err_num = $telnet->err_num;
			if($err_num){	
				$telnet->err('ont black-list del '.$telnet->descr($err_num));
			}
			$commands = [
			"enable",
			"config",
			"interface epon 0/0",
			"ont black-list del $keyport ".strtolower($mac)
			];
			$result = $telnet->executeCommands($commands);
		}
		die;
	break;
	case 'getlist';	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		$result = [];	
		$getswitch = $db->Fast('switch','*',['id'=>$id]);
		$selectallpon = $db->Multi('switch_pon','*',['oltid'=>$id]);
		if(!empty($getswitch['username']) && !empty($getswitch['password'])){
			$telnet = new PMonTelnet($getswitch);	
			$err_num = $telnet->err_num;
			if($err_num){	
				$telnet->err('error '.$telnet->descr($err_num));
			}
			$commands = [
				"enable",
				"config",
				"interface epon 0/0",
			];
			foreach($selectallpon as $idport => $datapon){
			preg_match('/0\/(\d+)/i',$datapon['pon'],$datamatch);
				$command = "show ont black-list ".$datamatch[1]." all";
				$commands[] = $command;
			}
			usleep(2000);
			$result = $telnet->executeCommands($commands);
		}
		$dataonu = [];
		if($result){
			$result = str_replace('0/0', 'p', $result);
			preg_match_all('/\b(.?)\s+\s+p\s+([0-9]+)\s+([A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2})\b/si',$result, $cdata12);
			foreach($cdata12[3] as $key => $value){
				$dataonu[md5(strtolower(trim($value)).trim($cdata12[2][$key]))] =[
					'port' => trim($cdata12[2][$key]),				
					'onu' => trim($cdata12[1][$key]),				
					'mac' => strtolower(trim($value))
				];
			}
		}
		if(is_array($dataonu)){
			echo'<div class="head_noreg"><div class="sn">MAC</div><div class="key">Інтерфейс</div><div class="knopka"></div></div>';
			foreach($dataonu as $mac => $valueonu){
				echo'<form id="blacklist-'.$valueonu['onu'].$valueonu['port'].'">';
				echo'<input type="hidden" id="act" name="act" value="del">';
				echo'<input type="hidden" id="do" name="do" value="blacklist12">';
				echo'<input type="hidden" id="mac" name="mac" value="'.$valueonu['mac'].'">';
				echo'<input type="hidden" id="keyport" name="keyport" value="'.$valueonu['port'].'">';
				echo'<input type="hidden" id="keyonu" name="keyonu" value="'.$valueonu['onu'].'">';
				echo'<input type="hidden" id="olt" name="olt" value="'.$getswitch['id'].'">';
				echo'<div class="head_noreg_list"><div class="sn">'.$valueonu['mac'].'</div>';
				echo'<div class="key">EPON 0/'.$valueonu['port'].''.(isset($valueonu['onu']) && !empty($valueonu['onu'])?':'.$valueonu['onu']:'').'</div>';
				echo'<div class="knopka">';
				echo'<span class="regonu" onclick="blacklist('.$valueonu['onu'].$valueonu['port'].')">Активувати</span></div></div>';
				echo'</form>';
			}
		}else{
			echo'--';
		}
		die;
	break;
	default:
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(!$id){
			$go->redirect('main');	
		}
		$dataSwitch = $db->Fast('switch','*',['id'=>$id]);
		if(!$dataSwitch['id']){
			$go->redirect('main');	
		}
		$script = '<script>blacklist12('.$id.');</script>';	
}
	$metatags = [
		'title'=>$lang['blacklist'],
		'description'=>'BlackList C-DATA 12',
		'page'=>'blacklist12'
	];
$tpl->load_template('blacklist12.tpl');
$tpl->set('{script}',$script);
$tpl->set('{place}',$dataSwitch['place']);
$tpl->set('{id}',$dataSwitch['id']);
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
}
?>
