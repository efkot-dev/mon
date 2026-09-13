<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt system!');
}
$all_tags = ['system', 'map','sklad','bdcom','telegram', 'onu','network','huawei','olt','gps','battery','car'];

$GLOBAL_MODULE = [
'signal_onu' => ['icon'=>'oltsfp.png','name' => $lang['taskers_1'],'title' => $lang['info_module_signal'],'tags' => ['system'],'module' => ['ONU_MONITOR_SIGNAL'],'variables' => ['ONU_MONITOR_SIGNAL']],
'ipcam' => ['icon'=>'oltsfp.png','name' => $lang['main_module_surveillance'],'title' => $lang['info_module_module_surveillance'],'tags' => ['system'],'module' => ['IPCAM'],'variables' => ['IPCAM']],
'monitor_cpu_switch' => ['icon'=>'err-inf.png','name' => $lang['taskers_61'],'title' => $lang['info_module_temp'],'tags' => ['system'],'module' => ['SWITCH_TEMPERATURE_MONITORING'],'variables' => ['SWITCH_TEMPERATURE_MONITORING']],
'switch_eq' => ['icon'=>'module_additional.png','name' => $lang['module_switch_eq'],'title' => $lang['info_module_switch_eq'],'tags' => ['system'],'module' => ['ONU_EQUIPMENT'],'variables' => ['ONU_EQUIPMENT']],
'sklad' => ['icon' => 'module_storage.png','name' => $lang['module_sklad'],'title' => $lang['info_module_sklad'],'tags' => ['system'],'module' => ['SKLAD'],'variables' => ['SKLAD']],
'onu_analiz' => ['icon' => 'module_failure.png','name' => $lang['module_onu_analiz'],'title' => $lang['info_module_onu_analiz'],'tags' => ['system'],'module' => ['ENABLE_ONU_LOSS'],'variables' => ['ENABLE_ONU_LOSS']],
'onu_traff' => [
    'icon' => 'module_speedometer.png',
    'name' => $lang['module_onu_traff'],
    'title' => $lang['info_module_onu_traff'],
    'tags' => ['system'],
    'module' => ['ONU_TRAFFIC'],
    'variables' => ['ONU_TRAFFIC']
],
'generator' => ['icon' => 'module_generator.png','name' => $lang['module_generator'],'title' => $lang['info_module_generator'],'tags' => ['system'],'module' => ['GENERATOR'],'variables' => ['GENERATOR']],
'ping_3' => [
    'icon' => 'module_ping3.png',
    'name' => $lang['module_ping_3'],
    'title' => $lang['info_module_ping_3'],
    'tags' => ['system'],
    'module' => ['PING3'],
    'variables' => ['PING3']
],
'module_temp' => [
    'icon' => 'timer.png',
    'name' => 'Temperature',
    'title' => 'SNMP Temperature Monitor',
    'tags' => ['system'],
    'module' => ['TEMPERATURE_MONITOR'],
    'variables' => ['TEMPERATURE_MONITOR']
],
'module_temp' => [
    'icon' => 'module_failure.png',
    'name' => 'ONU error',
    'title' => 'Error monitoring with ONU',
    'tags' => ['system'],
    'module' => ['ONU_ERROR'],
    'variables' => ['ONU_ERROR']
],
'module_pon_error' => [
    'icon' => 'module_failure.png',
    'name' => 'View PON error',
    'title' => 'Відображення помилок в PON мережі',
    'tags' => ['system'],
    'module' => ['FIBERMAP_ERROR'],
    'variables' => ['FIBERMAP_ERROR']
],
'module_topology' => [
    'icon' => 'unit.png',
    'name' => 'Network topology',
    'title' => 'Is the physical or logical layout of devices (nodes) and connections (links)',
    'tags' => ['system'],
    'module' => ['NETWORK_TOPOLOGY'],
    'variables' => ['NETWORK_TOPOLOGY']
],
'ping_3_alarm' => [
    'icon' => 'module_ping3_alarm.png',
    'name' => $lang['module_ping_3_alarm'],
    'title' => $lang['info_module_ping_3_alarm'],
    'tags' => ['system'],
    'module' => ['SECURITY_PING3'],
    'variables' => ['SECURITY_PING3']
],
'register' => [
    'icon' => 'module_checklist.png',
    'name' => $lang['module_register'],
    'title' => $lang['info_module_register'],
    'tags' => ['system'],
    'module' => ['TEMPLATE_REGISTER'],
    'variables' => ['TEMPLATE_REGISTER']
],

'online' => [
    'icon' => 'm8.png',
    'name' => $lang['module_online'],
    'title' => $lang['info_module_online'],
    'tags' => ['system'],
    'module' => ['USER_ONLINE'],
    'variables' => ['USER_ONLINE']
],

'online' => [
    'icon' => 'm8.png',
    'name' => $lang['module_block_signal'],
    'title' => $lang['info_module_block_signal'],
    'tags' => ['system'],
    'module' => ['BLOCK_SIGNAL'],
    'variables' => ['BLOCK_SIGNAL']
],

'onu_sfp' => [
    'icon' => 'oltsfp.png',
    'name' => $lang['module_onu_sfp'],
    'title' => $lang['info_module_onu_sfp'],
    'tags' => ['bdcom'],
    'module' => ['BDCOM_EPON_ONU_SFP'],
    'variables' => ['BDCOM_EPON_ONU_SFP']
],

'error' => [
    'icon' => 'sfperr.png',
    'name' => $lang['module_error'],
    'title' => $lang['info_module_error'],
    'tags' => ['system'],
    'module' => ['ERRORSFP'],
    'variables' => ['ERRORSFP']
],

'bdcom_epon_fdb' => [
    'icon' => 'fdb_table.png',
    'name' => $lang['module_bdcom_epon_fdb'],
    'title' => $lang['info_module_bdcom_epon_fdb'],
    'tags' => ['bdcom'],
    'module' => ['BDCOM_EPON_ONU_MAC'],
    'variables' => ['BDCOM_EPON_ONU_MAC']
],

'billing_abills' => [
    'icon' => 'module_profile.png',
    'name' => $lang['module_billing_abills'],
    'title' => $lang['info_module_billing_abills'],
    'tags' => ['billing'],
    'module' => ['ABILLS_ISP_IMPORT'],
    'variables' => ['ABILLS_ISP_IMPORT']
],

'billing_mikbill' => [
    'icon' => 'module_profile.png',
    'name' => $lang['module_billing_mikbill'],
    'title' => $lang['info_module_billing_mikbill'],
    'tags' => ['billing'],
    'module' => ['MIKBILL_ISP_IMPORT'],
    'variables' => ['MIKBILL_ISP_IMPORT']
],

'pmon_log' => [
    'name' => $lang['module_pmon_log'],
	'icon' => 'module_pmon_log.png',
    'title' => $lang['info_module_pmon_log'],
    'tags' => ['pmon'],
    'module' => ['PMON_LOG'],
    'variables' => ['PMON_LOG']
],
'pmon_onu_transport' => ['name' => $lang['transport_onu'],'icon' => 'unit.png','title' => $lang['transport_onu_descr'],'tags' => ['onu','pmon','system'],'module' => ['TRANSPORT_ONU'],'variables' => ['TRANSPORT_ONU']],
'pmon_pon_los' => [
    'name' => $lang['module_pmon_pon_los'],
	'icon' => 'module_onu_los.png',
    'title' => $lang['info_module_pmon_pon_los'],
    'tags' => ['billing'],
    'module' => ['MONITOR_PON_LOS'],
    'variables' => ['MONITOR_PON_LOS']
],

'fdb_tables' => [
    'name' => $lang['module_fdb_tables'],
	'icon' => 'module_fdb_devices.png',
    'title' => $lang['info_module_fdb_tables'],
    'tags' => ['fdb'],
    'module' => ['FDB_TABLE'],
    'variables' => ['FDB_TABLE']
],

'fdb_tables_changed' => [
    'name' => $lang['module_fdb_tables_changed'],
	'icon' => 'module_fdb_devices_changed.png',
    'title' => $lang['info_module_fdb_tables_changed'],
    'tags' => ['fdb', 'telegram'],
    'module' => ['FDB_CHANGED_NOTIFiCATION'],
    'variables' => ['FDB_CHANGED_NOTIFiCATION']
],

'fdb_tables_log' => [
    'name' => $lang['module_fdb_tables_log'],
	'icon' => 'module_fdb_devices_log.png',
    'title' => $lang['info_module_fdb_tables_log'],
    'tags' => ['fdb', 'log'],
    'module' => ['FDB_CHANGED_LOG'],
    'variables' => ['FDB_CHANGED_LOG']
],

'concurent_map' => [
    'name' => $lang['module_concurent_map'],
	'icon' => 'module_concurent_map.png',
    'title' => $lang['info_module_concurent_map'],
    'tags' => ['network'],
    'module' => ['CONCURENT_MAP'],
    'variables' => ['CONCURENT_MAP']
],
'building_uid' => [
    'name' => $lang['module_building_uid'],
	'icon' => 'module_building_uid.png',
    'title' => $lang['info_module_building_uid'],
    'tags' => ['network'],
    'module' => ['PON_HIGH_RISE_UID'],
    'variables' => ['PON_HIGH_RISE_UID']
],

'building' => [
    'name' => $lang['module_building'],
	'icon' => 'module_building.png',
    'title' => $lang['info_module_building'],
    'tags' => ['network'],
    'module' => ['PON_HIGH_RISE'],
    'variables' => ['PON_HIGH_RISE']
],

'monitor_ip' => [
    'name' => $lang['module_monitor_ip'],
	'icon' => 'module_monitor_ip.png',
    'title' => $lang['info_module_monitor_ip'],
    'tags' => ['network'],
    'module' => ['MONITOR_IP'],
    'variables' => ['MONITOR_IP']
],

'onu_vendor' => [
    'name' => $lang['module_onu_vendor'],
	'icon' => 'module_onu_vendor.png',
    'title' => $lang['info_module_onu_vendor'],
    'tags' => ['onu'],
    'module' => ['VIEW_ONU_VENDOR'],
    'variables' => ['VIEW_ONU_VENDOR']
],

'onu_los' => [
    'name' => $lang['module_onu_los'],
	'icon' => 'module_onu_los.png',
    'title' => $lang['info_module_onu_los'],
    'tags' => ['onu'],
    'module' => ['ONU_LOS'],
    'variables' => ['ONU_LOS']
],

'onu_new' => [
    'name' => $lang['module_onu_new'],
	'icon' => 'module_onu_new.png',
    'title' => $lang['info_module_onu_new'],
    'tags' => ['onu'],
    'module' => ['ONU_NEW'],
    'variables' => ['ONU_NEW']
],

'onu_off' => [
    'name' => $lang['module_onu_off'],
	'icon' => 'module_onu_off.png',
    'title' => $lang['info_module_onu_off'],
    'tags' => ['onu'],
    'module' => ['ONU_OFF'],
    'variables' => ['ONU_OFF']
],

'taffic' => [
    'name' => $lang['module_taffic'],
	'icon' => 'module_taffic.png',
    'title' => $lang['info_module_taffic'],
    'tags' => ['network'],
	'icon' => 'module_onu_off.png',
    'module' => ['BANDWIDTH'],
    'variables' => ['BANDWIDTH']
],

'calendar' => [
    'name' => $lang['module_calendar'],
	'icon' => 'module_calendar.png',
    'title' => $lang['info_module_calendar'],
    'tags' => ['system'],
    'module' => ['CALENDAR'],
    'variables' => ['CALENDAR', 'CALENDAR_MAIN']
],

'cache' => [
    'name' => $lang['module_cache'],
	'icon' => 'module_cache.png',
    'title' => $lang['info_module_cache'],
    'tags' => ['system'],
    'module' => ['CACHE'],
    'variables' => ['CACHE']
],

'signal' => [
    'name' => $lang['module_signal'],
	'icon' => 'module_signal.png',
    'title' => $lang['info_module_signal'],
    'tags' => ['signal'],
    'module' => ['ONU_RX_OLT_SIGNAL'],
    'variables' => ['ONU_RX_OLT_SIGNAL']
],

'manager_vlan' => [
    'name' => $lang['module_manager_vlan'],
	'icon' => 'module_manager_vlan.png',
    'title' => $lang['info_module_manager_vlan'],
    'tags' => ['system'],
    'module' => ['MANAGER_VLAN'],
    'variables' => ['MANAGER_VLAN']
],

'view_vla' => [
    'name' => $lang['module_view_vla'],
	'icon' => 'module_view_vla.png',
    'title' => $lang['info_module_view_vla'],
    'tags' => ['system'],
    'module' => ['VIEW_OLT_VLAN'],
    'variables' => ['VIEW_OLT_VLAN']
],

'ponmap' => [
    'name' => $lang['module_ponmap'],
	'icon' => 'module_ponmap.png',
    'title' => $lang['info_module_ponmap'],
    'tags' => ['system'],
    'module' => ['FIBERMAP'],
    'variables' => ['FIBERMAP']
],

'vendor' => [
    'name' => $lang['module_vendor'],
	'icon' => 'module_vendor.png',
    'title' => $lang['info_module_vendor'],
    'tags' => ['system'],
    'module' => ['VIEW_ONU_VENDOR'],
    'variables' => ['VIEW_ONU_VENDOR']
],

'car' => [
    'name' => $lang['module_car'],
	'icon' => 'module_car.png',
    'title' => $lang['info_module_car'],
    'tags' => ['car'],
    'module' => ['CAR'],
    'variables' => ['CAR']
],

'snmp_checker' => [
    'name' => $lang['module_snmp_checker'],
	'icon' => 'module_snmp_checker.png',
    'title' => $lang['info_module_snmp_checker'],
    'tags' => ['olt'],
    'module' => ['CHECK_SNMP'],
    'variables' => ['CHECK_SNMP']
],

'mysql_debug' => [
    'name' => $lang['module_mysql_debug'],
    'title' => $lang['info_module_mysql_debug'],
	'icon' => 'module_mysql_debug.png',
    'tags' => ['debug'],
    'module' => ['MYSQLDEBUG'],
    'variables' => ['MYSQLDEBUG']
],

'pin_code' => [
    'name' => $lang['module_pin_code'],
	'icon' => 'module_pin_code.png',
    'title' => $lang['info_module_pin_code'],
    'tags' => ['debug'],
    'module' => ['PIN'],
    'variables' => ['PIN', 'PINCODE']
],

'onu_map' => [
    'name' => $lang['module_onu_map'],
	'icon' => 'module_onu_map.png',
    'title' => $lang['info_module_onu_map'],
    'tags' => ['onu', 'map'],
    'module' => ['ONU_MAP'],
    'variables' => ['ONU_MAP']
],

'bdcom_integration' => [
    'name' => $lang['module_bdcom_integration'],
	'icon' => 'module_bdcom_integration.png',
    'title' => $lang['info_module_bdcom_integration'],
    'tags' => ['bdcom', 'epon'],
    'module' => ['BDCOM_EPON_ONU_SFP'],
    'variables' => ['BDCOM_EPON_ONU_SFP']
],

'battery' => [
    'name' => $lang['module_battery'],
	'icon' => 'module_battery.png',
    'title' => $lang['info_module_battery'],
    'tags' => ['sklad', 'battery'],
    'module' => ['BATTERY'],
    'variables' => ['BATTERY']
],

'oblenergo' => [
    'name' => $lang['module_oblenergo'],
	'icon' => 'module_oblenergo.png',
    'title' => $lang['info_module_oblenergo'],
    'tags' => ['map', 'sklad'],
    'module' => ['OBL_ENERGO'],
    'variables' => ['OBL_ENERGO']
],

'stikers' => [
    'name' => $lang['module_stikers'],
	'icon' => 'module_stikers.png',
    'title' => $lang['info_module_stikers'],
    'tags' => ['onu'],
    'module' => ['STICKERS'],
    'variables' => ['STICKERS']
],

'onu_vlan' => [
    'name' => $lang['module_onu_vlan'],
	'icon' => 'module_onu_vlan.png',
    'title' => $lang['info_module_onu_vlan'],
    'tags' => ['onu,bdcom,epon'],
    'module' => ['ONU_VLAN'],
    'variables' => ['ONU_VLAN']
],

'onu_uid' => [
    'name' => $lang['module_onu_uid'],
	'icon' => 'module_onu_uid.png',
    'title' => $lang['info_module_onu_uid'],
    'tags' => ['billing'],
    'module' => ['ONU_UID'],
    'variables' => ['ONU_UID']
],

'gps_traccar' => [
    'name' => $lang['module_gps_traccar'],
    'title' => $lang['info_module_gps_traccar'],
    'tags' => ['gps', 'car'],
	'icon' => 'module_gps_traccar.png',
    'module' => ['GPS_TRACCAR'],
    'variables' => ['GPS_TRACCAR', 'GPS_TRACCAR_LOGIN', 'GPS_TRACCAR_URL', 'GPS_TRACCAR_PASSWORD']
],

'pmon_api' => [
    'name' => $lang['module_pmon_api'],
    'title' => $lang['info_module_pmon_api'],
	'icon' => 'module_pmon_api.png',
    'tags' => ['api', 'apk'],
    'module' => ['PMONAPI'],
    'variables' => ['PMONAPI']
],

'pmon_app' => [
    'name' => $lang['module_pmon_app'],
    'title' => $lang['info_module_pmon_app'],
    'tags' => ['api', 'apk'],
	'icon' => 'module_pmon_app.png',
    'module' => ['PMONAPP'],
    'variables' => ['PMONAPP']
],

'gps_tracker_com_ua' => [
    'name' => $lang['module_gps_tracker_com_ua'],
    'title' => $lang['info_module_gps_tracker_com_ua'],
	'icon' => 'module_gps_traccar.png',
    'tags' => ['gps', 'car'],
    'module' => ['GPS_TRACKER_COM_UA'],
    'variables' => ['GPS_TRACKER_COM_UA']
],

'pmon_board' => [
    'name' => $lang['module_pmon_board'],
    'title' => $lang['info_module_pmon_board'],
    'tags' => ['system'],
	'icon' => 'module_pmon_board.png',
    'module' => ['BOARD_FAULT'],
    'variables' => ['BOARD_FAULT']
]
];

?>