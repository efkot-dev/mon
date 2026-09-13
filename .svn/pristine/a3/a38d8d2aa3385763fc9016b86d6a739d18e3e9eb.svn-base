<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if(isset($confPMon['ONU_EQUIPMENT']) && !empty($confPMon['ONU_EQUIPMENT']) && $confPMon['ONU_EQUIPMENT'] == 1) {
	$device = isset($_POST['device']) ? Clean::str($_POST['device']): null;
	$act = isset($_POST['act']) ? Clean::str($_POST['act']): null;
	$idonu = isset($_POST['id']) ? Clean::int($_POST['id']): null;
	if($act=='select_type' && $idonu>0){
		echo'<div class="onu-olt efect1 m20b mobile"><div class="ont-block"><div class="ont-content">';
		echo'
		<form action="/?do=send" method="post" id="formadd" class="w100">
			<input name="act" type="hidden" value="onu_equipment">
			<input name="idonu" type="hidden" value="'.$idonu.'">';
		$types  = '
			<select class="select" id="devices" name="device">
				<option value="none"></option>
				<option value="switch">'.$lang['unmanageable'].'</option>
				<option value="rozymnuy">'.$lang['controlled'].'</option>
				<option value="ipcam">'.$lang['ipcam'].'</option>
				<option value="wifi">'.$lang['wifi'].'</option>
				<option value="router">'.$lang['router'].'</option>
				<option value="default">'.$lang['other'].'</option>
			</select>';
		echo formpage([
			'img'=> 'addconnect.png',
			'name'=> $lang['oid_type'],
			'descr'=>'',
			'pole'=>$types]
			);
		echo'
			<div id="select_type"></div>
		</form>';
		echo'</div></div></div>';
			?>
			<script>
			$('#devices').on('change', function() {
				var device = $(this).val();
				$.post('/ajax/connect_to_onu.php',{act:'select_device',device:device,idonu:<?=$idonu;?>},function(response){
				$('#select_type').html(response);
				},'html');
			});		
			</script>
			<?php
	}elseif($act=='select_device'){
		$pole_name = '<input required name="name" class="input1" id="name" placeholder="Switch_One" type="text" value="">';
		echo formpage(['img'=>'addconnect.png','name'=>$lang['name'],'descr'=>'','pole'=>$pole_name]);		
		$pole_model = '<input required name="model" class="input1" id="name" placeholder="Huawei" type="text" value="">';
		echo formpage(['img'=>'addconnect.png','name'=>$lang['model'],'descr'=>'','pole'=>$pole_model]);
		if($device=='rozymnuy'){
			$data = $db->SimpleWhile("SELECT * FROM switch WHERE device = 'switch'");
			$types_current  = '<select class="device_id" id="device_id" name="device_id">
				<option value="0"></option>';
				if(isset($data) && count($data)>0){
					foreach($data as $temp){
						$types_current .= '<option value="'.$temp['id'].'">'.$temp['place'].'</option>';
					}
				}
			$types_current .= '</select>';
			echo formpage([
				'img'=>'addconnect.png',
				'name'=>$lang['controlled'],
				'descr'=>'',
				'pole'=>$types_current]
			);
		}elseif($device=='switch'){

		}elseif($device=='router'){
			
		}elseif($device=='default'){
			
		}elseif($device=='ipcam'){
			
		}elseif($device=='wifi'){
			
		}
		// count port
		if($device=='rozymnuy' || $device=='ipcam' || $device=='wifi' || $device=='switch' || $device=='router'){
			$pole_port = '<input required name="port" class="input1" id="name" placeholder="8" type="text" value="">';
			echo formpage(['img'=>'eth.png','name'=>$lang['count_port_ports'],'descr'=>'','pole'=>$pole_port]);		
		}
		// ip
		if($device=='rozymnuy' || $device=='ipcam' || $device=='wifi' || $device=='router'){
			$pole_netip = '<input required name="netip" class="input1" id="name" placeholder="1.1.1.1" type="text" value="">';
			echo formpage(['img'=>'eth.png','name'=>'IP-Address','descr'=>'192.168.1.88','pole'=>$pole_netip]);			
			$pole_mac = '<input required name="mac" class="input1" id="name" placeholder="c8:3a:35:b4:7d:53" type="text" value="">';
			echo formpage(['img'=>'eth.png','name'=>'MAC-Address','descr'=>'','pole'=>$pole_mac]);		
		}
		// sn
		$pole_sn = '<input required name="sn" class="input1" id="sn" placeholder="AA-PMON-SERIAL" type="text" value="">';
		echo formpage(['img'=>'number-20.png','name'=>$lang['serial'],'descr'=>'','pole'=>$pole_sn]);
		echo'
			<div class="polebtn">
					<button type="submit" form="formadd" value="submit">'.$lang['save'].'</button>
			</div>
		';
	}
}
?>