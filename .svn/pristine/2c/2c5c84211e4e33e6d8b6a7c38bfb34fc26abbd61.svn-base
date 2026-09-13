<?php
define('AJAX',true);
define('ONT',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(isset($id) && $id>0 && $access->get('edit_pir_sla')) {
	$getonu = $db->Fast('onus','*',['idonu' => $id]);
	if(!empty($getonu['idonu'])){
		$getolt = $db->Fast('switch','id,netip,snmpro',['id' => $getonu['olt']]);
		$snmp_return_status = @snmp2_get($getolt['netip'], $getolt['snmpro'],'1.3.6.1.2.1.2.2.1.8.'.$getonu['keyonu'], 100000, 5);
		$real_onu_status = @getsnmp_integer($snmp_return_status);
		if(isset($real_onu_status) && $real_onu_status == 1){
			// upstream pir
			$tmp_upstream_status = @snmp2_get($getolt['netip'], $getolt['snmpro'],'1.3.6.1.4.1.3320.101.9.1.1.11.'.$getonu['keyonu'], 100000, 5);
			$upstream_status = getsnmp_integer($tmp_upstream_status);
			$tmp_upstream_pir = @snmp2_get($getolt['netip'], $getolt['snmpro'],'1.3.6.1.4.1.3320.101.9.1.1.8.'.$getonu['keyonu'], 100000, 5);
			$upstream_pir = getsnmp_integer($tmp_upstream_pir);
			$ups_pir = ($upstream_pir/1000);
			// upstream cir
			$tmp_downstream_pir = @snmp2_get($getolt['netip'], $getolt['snmpro'],'1.3.6.1.4.1.3320.101.9.1.1.17.'.$getonu['keyonu'], 100000, 5);
			$downstream_pir = getsnmp_integer($tmp_downstream_pir);
			$tmp_downstream_pir = @snmp2_get($getolt['netip'], $getolt['snmpro'],'1.3.6.1.4.1.3320.101.9.1.1.20.'.$getonu['keyonu'], 100000, 5);
			$downstream_status = getsnmp_integer($tmp_downstream_pir);
			// downstream pir
			// downstream cir
echo'
	<div class="pir_speed">
	<div class="upstream">
		<div class="upstream_name"><img src="../style/img/'.(isset($upstream_status) && $upstream_status == 2 ? "lock.png" : "check-box.png").'">Upstream<span>Вихідна швидкість</span></div>
		<div class="upstream_pir">
			<span class="speeds1">Maximum</span>'.$ups_pir.'
			<span class="mbps">Mbps</span>
			<span class="epon_sla" onclick="epon_sla('.$upstream_pir.','.$getonu['idonu'].',\'upir\');">
				<img src="../style/img/edit.png">
			</span>		
		</div>
		<!--<div class="upstream_cir"><span class="speeds2">Minimal</span>10<span class="mbps">Mbps</span></div>-->
	</div>		
	<div class="downstream">
		<div class="downstream_name"><img src="../style/img/'.(isset($downstream_status) && $downstream_status == 2 ? "lock.png" : "check-box.png").'">Downstream<span>Вхідна швидкість</span></div>
				<div class="downstream_pir"><span class="speeds3">Maximum</span>'.($downstream_pir/1000).'
				<span class="mbps">Mbps</span>
				<span class="epon_sla" onclick="epon_sla('.$downstream_pir.','.$getonu['idonu'].',\'dpir\');">
					<img src="../style/img/edit.png">
				</span>	
					</div>
					<!--<div class="downstream_cir"><span class="speeds4">Minimal</span>10<span class="mbps">Mbps</span></div>-->		
				</div>				
			</div>	
			';
		}
	}
}
?>
			
			
