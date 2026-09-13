<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if( $access->get('regonu') && isset($confPMon['REG_GPON_HUAWEI_MOD1']) 
		&& !empty($confPMon['REG_GPON_HUAWEI_MOD1']) && $confPMon['REG_GPON_HUAWEI_MOD1']==1 
			&& $access->get('regonu') && ($dataSwitch['oidid'] == 14 || $dataSwitch['oidid'] == 33) ){
	
$tplRes .='
<script>
	reg_qiuq_huawei('. $dataSwitch['id'].');
</script>
<div id="snmp_reger"></div>';
}
?>