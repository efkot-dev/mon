<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ENGINE_DIR.'functions/regonu.php';
require ENGINE_DIR.'classes/telnet.class.php';
require ENGINE_DIR.'classes/system.class.php';
if(!$access->get('blacklist') && $getSwitch['oidid']!=13 && !$access->get('delblacklist')){
	$go->redirect('main');		
}else{
$result = '';
$navigation = '';
switch($act){
	case 'del';	
		$mac = isset($_POST['data']['mac'])?Clean::text($_POST['data']['mac']) : null;
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
				"auth blacklist del $keyport onu  ".strtolower($mac)
			];
			$result = $telnet->executeCommands($commands);
			if(preg_match('/successfully/i',$result)) {
				echo'<div class="block-info berr3">Delete ONU ('.$mac.') from slot 1 PON '.$keyport.' blacklist successfully</div>';
			}else{
				echo'<div class="block-info berr1">Error NOt Delete ONU ('.$mac.') from slot 1 PON '.$keyport.'</div>';
			}
		}
		die;
	break;
	case 'getlist';	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		$result = [];	
		$getswitch = $db->Fast('switch','*',['id'=>$id]);
		$selectallpon = $db->Multi('switch_pon','*',['oltid'=>$id]);
		if(!empty($getswitch['username']) && !empty($getswitch['password'])){
			$telnet = @new PMonTelnet($getswitch);	
			$err_num = $telnet->err_num;
			if($err_num){	
				$telnet->err('error '.$telnet->descr($err_num));
			}
			$commands = [
				"show running-config auth",
			];
			$result = $telnet->executeCommands($commands);
			if(isset($result)){
				$result = str_replace('show running-config auth','',$result);
				$result = str_replace('auth blacklist enable','',$result);
			}
		}
		$dataonu = [];
		if($result){
			preg_match_all('/add (\d+) onu ([A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2}-[A-F0-9]{2})\b/si', $result, $matches, PREG_SET_ORDER);
			foreach($matches as $value){
				$dataonu[md5(trim($value[2]).trim(trim($value[1])))] =[
					'port' => trim(trim($value[1])),				
					'mac' => strtolower(trim($value[2]))
				];
			}
		}
		if(is_array($dataonu)){
			echo'<div class="head_noreg"><div class="sn">MAC</div><div class="key">Інтерфейс</div><div class="knopka"></div></div>';
			foreach($dataonu as $mac => $valueonu){
				echo'<form id="blacklist-'.$mac.'">';
				echo'<input type="hidden" id="act" name="act" value="del">';
				echo'<input type="hidden" id="do" name="do" value="blacklist11">';
				echo'<input type="hidden" id="md5" name="md5" value="'.$mac.'">';
				echo'<input type="hidden" id="mac" name="mac" value="'.$valueonu['mac'].'">';
				echo'<input type="hidden" id="keyport" name="keyport" value="'.$valueonu['port'].'">';
				echo'<input type="hidden" id="olt" name="olt" value="'.$getswitch['id'].'">';
				echo'<div class="head_noreg_list"><div class="sn">'.$valueonu['mac'].'</div>';
				echo'<div class="key">EPON 0/'.$valueonu['port'].'</div>';
				echo'<div class="knopka">';
				echo'<span id="bts-'.$mac.'" class="regonu" onclick="blacklistdel11(\''.$mac.'\')">Активувати</span></div></div>';
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
		$script = '<script>blacklist11('.$id.');</script>';	
}
	$metatags = [
		'title'=>$lang['blacklist'],
		'description'=>'BlackList C-DATA 11',
		'page'=>'blacklist12'
	];
$tpl->load_template('blacklist11.tpl');
$tpl->set('{script}',$script);
$tpl->set('{place}',$dataSwitch['place']);
$tpl->set('{id}',$dataSwitch['id']);
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
}
?>
