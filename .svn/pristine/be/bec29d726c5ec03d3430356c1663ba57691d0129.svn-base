<?php
define('REGONU', true);
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(isset($id) && $id>0){
$dataswitch = $db->Fast('switch','*',['id' => $id]);
if($access->get('regonu') && isset($confPMon['REG_GPON_HUAWEI_MOD1']) 
		&& !empty($confPMon['REG_GPON_HUAWEI_MOD1']) && $confPMon['REG_GPON_HUAWEI_MOD1']==1 
			&& $access->get('regonu') && ($dataswitch['oidid'] == 14 || $dataswitch['oidid'] == 33) ){
	$response = huawei_gpon_get_list($dataswitch['netip'],$dataswitch['snmpro']);
	$lineprofile = huawei_get_gpon_lineprofile($dataswitch['netip'],$dataswitch['snmpro'], $dataswitch['id']);
	$servprofile = huawei_get_gpon_srvprofile($dataswitch['netip'],$dataswitch['snmpro'], $dataswitch['id']);
	$resp ='<table class="resp-tab none"><thead><tr>
	<th width="5%">Pon</th>
	<th width="8%">Interface</th>
	<th width="5%">Ont</th>
	<th width="15%">Sn</th>
	<th width="15%">Description onu</th>
	<th width="10%">Vlan</th>
	<th width="10%">InnerVlan</th>
	<th>Line profile</th>
	<th>Service profile</th>
	<th width="10%">Function</th></tr></thead><tbody>';
	if(isset($response['gpon']) && count($response['gpon'])>0){
		foreach ($response['gpon'] as $key => $value) {
			$idreger = (isset($value['idport'])?$value['idport']:1).$value['ont'];
			$idonu = md5($value['sn']);
			$resp .='
			<tr id="reger_'.$idonu.'">
			<td style="background: #75cd75;"><font color="#222">GPON</font></td>
			<td><font color="#1f7bc3">'.$value['index'].'</font></td>
			<td>
			
			<input name="onu_id" class="input1" type="text" id="onu_id_'.$idonu.'" >
			<input name="onu_inface" class="input1" type="hidden" id="onu_inface_'.$idonu.'" value="'.$value['index'].'">
			<input name="onu_sn" class="input1" type="hidden" id="onu_sn_'.$idonu.'" value="'.$value['sn'].'">
			<input name="onu_sn" class="input1" type="hidden" id="onu_ifindex_'.$idonu.'" value="'.$value['idport'].'">
			</td>
			<td><font color="#0f73c3">'.$value['sn'].'</font></td>
			<td><input name="onu_name" class="input1" type="text" id="onu_name_'.$idonu.'" placeholder="Description"></td>
			<td>
				<center>
					<input name="onu_vlan" style="width: 70px;" class="input1" type="text" id="onu_vlan_'.$idonu.'" placeholder="Vlan">
				</center>
			</td>
			<td>
				<center>
					<input name="onu_invlan" style="width: 70px;" class="input1" type="text" id="onu_invlan_'.$idonu.'" placeholder="Vlan">
				</center>
			</td>
			<td>';
			// line profile
			$resp .='<select name="onu_lp" id="onu_lp_'.$idonu.'" class="inputonuselect"><option value="0"></option>';
			foreach ($lineprofile as $lfileName) {
				$resp .='<option value="'.$lfileName['lp'].'">'.$lfileName['lp'].'</option>';
			}
			$resp .='</select>';
			$resp .='</td>';
			$resp .='<td>';
			// service profile
			$resp .='<select name="onu_sp" id="onu_sp_'.$idonu.'" class="inputonuselect"><option value="0"></option>';
			foreach ($servprofile as $sfileName) {
				$resp .='<option value="'.$sfileName['sp'].'">'.$sfileName['sp'].'</option>';
			}
			$resp .='</select>';			
			$resp .='</td>';
			$resp .='<td id="reger_key">';
			$resp .='<span class="reger_btn" onclick="register_quiq_huawei(\''.$idonu.'\',\''.$dataswitch['id'].'\')">Реєструвати</span>';
			$resp .='</td>';
			
			$resp .='</tr>';
		}
	}
echo $resp;	
}
}
?>
