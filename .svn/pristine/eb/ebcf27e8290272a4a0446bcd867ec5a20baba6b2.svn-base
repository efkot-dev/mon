<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ENGINE_DIR.'init.time.php';
$corefile = [
	// Статус комутаторів: pdo
    1 => 'health.php',
    2 => 'dataonus.php', //ok
	// Моніторинг доступності SNMP, IP : pdo
    3 => 'ping.php',
	// Моніторинг портів: pdo
    4 => 'port_status.php',
    5 => 'telegram.php', //ok
    6 => 'ping3.php', //ok
    7 => 'parserfdb.php',
	// Парсинг VLAN: config
    8 => 'parservlan.php',
	// Моніторинг критичних точок: pdo
    9 => 'transport_onu.php',
	// Моніторинг зміни сигналів: pdo
    10 => 'onu_changerx.php',
	// Моніторинг ПОН поксів, відбраження помилок
    11 => 'fibers.php',
    12 => 'blacklistcdata11.php', //ok
    13 => 'blacklistcdata12.php', //ok
    14 => 'duplicat_onusdata.php', //ok
    15 => 'olt_sfp.php', //ok
	// Переписано логіку та переведено все на pdo
    16 => 'tempdataonu.php',
    ///17 => 'mysqldump.php', видалено!!!
	// Парсинг даних: pdo
    18 => 'mikbill.php',
	// Моінторинг ONU, POWEROFF, LOS: pdo
    19 => 'port.php',
    20 => 'onu_bdcom_epon_reason.php', //ok
    21 => 'skyscraper_onu.php', //ok
	22 => 'onu_bdcom_gpon_reason.php', //ok
	23 => 'onu.php', //ok 
	24 => 'onu_huawei_gpon_reason.php', //ok 
	25 => 'onu_bdcom_epon_time_reason.php', //ok 
	26 => 'bandwidth.php', //ok 
	27 => 'onu_cdata1700_status.php', //ok
	// Моніторинг помилок на портах: pdo
	28 => 'port_error.php',
	29 => 'onu_vendor.php', //ok 
	30 => 'monitorip.php', //ok  
	31 => 'onu_rx_olt.php', //ok 	
	32 => 'onu_new.php',#sql
	33 => 'onu_bdcom_epon_status.php', //ok 		
	34 => 'onu_huawei_gpon_status.php', //ok  
	35 => 'onu_health.php', //ok  	
	36 => 'onu_zte6_gpon_time_reason.php', //ok  	
	37 => 'onu_zte6_gpon_status.php', //ok  	
	38 => 'onu_zte6_gpon_reason.php', //ok 	
	39 => 'onu_cdata1216_status.php', //ok 
	40 => 'onu_cdata1216_reason.php', //ok 	
	41 => 'onu_cdata1216_time_reason.php', //ok	
	42 => 'onu_zte3_gpon_status.php', //ok 
	43 => 'onu_zte3_gpon_reason.php', //ok 	
	44 => 'rezerv.php', //ok		
	45 => 'backup.php', //ok  
	46 => 'onu_cdata1700_reason.php', //ok 
	47 => 'board_fault.php',  
	48 => 'regeronu.php', //ok 
	49 => 'getvlangponzte3.php', //ok 	
	50 => 'getvlaneponzte3.php', //ok  	
	51 => 'getvlangponhuawei.php', //ok 
	52 => 'gps.php', // TRACCAR, TRACKER.COM.UA
	53 => 'olt_uptime.php',  //ok 
    54 => 'alarm_ping3.php', //ok 	
	55 => 'port_pon_monitor.php',	
	56 => 'onu_signal.php',  //ok 	
	57 => 'equipment.php',  //ok 	
	58 => 'sfp_signal.php',  //ok 
	59 => 'tasker.php', //ok 
    60 => 'abills.php', //  - отримання даних з білінга Abills
    61 => 'switch_parameters.php',
    62 => 'surveillance.php',
	// Clear DB
    63 => 'tech.php',
	// Monitor Temp switch
    64 => 'temp.php',
    65 => 'onu_error.php',
    66 => 'onu_zte3_epon_status.php',
    67 => 'onu_zte3_epon_signal.php',
    96 => 'rezerv.php', //ok 
    99 => 'system.php', //ok	
	100 => 'onu_checker.php',################pmon
	104 => 'port_checker.php',################pmon
	// Топологія мережі, монтіоринг: pdo
	105 => 'topology_port_status.php',
	107 => 'reger_ont.php'
];
if (array_key_exists($jobid, $corefile)) {
    $apiFile = API_DIR . $corefile[$jobid];
    if (file_exists($apiFile)) {
        require_once $apiFile;
    }
}
?>
