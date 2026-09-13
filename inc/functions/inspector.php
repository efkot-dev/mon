<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function status_onu_bdcom_epon($status) {
	$statuses = ["0" => 1,"1" => 1,"2" => 2,"3" => 1,"4" => 2];		
	return isset($statuses[$status]) ? $statuses[$status] : 2;
}
function status_onu_huawei_gpon($status) {
	return ($status==1 ? 1 : 2);
}
?>
