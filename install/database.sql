CREATE TABLE `apikey` (
  `id` int(10) UNSIGNED NOT NULL,
  `added` datetime DEFAULT NULL,
  `apikey` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipaccess` varchar(20) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `types` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `apikey` (`id`, `added`, `apikey`, `ipaccess`, `count`, `types`) VALUES
(1, NULL, 'regthy76rtfig8g', NULL, 10, 'monitor'),
(2, NULL, 'rt325ye6irei65e', NULL, NULL, 'ont');
CREATE TABLE `checkaccess` (
  `id` int(11) UNSIGNED NOT NULL,
  `uid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `types` varchar(255) NOT NULL DEFAULT '',
  `added` datetime NOT NULL DEFAULT '1970-01-01 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `checkaccess` (`id`, `uid`, `types`, `added`) VALUES
(2, 1, 'setup', '1970-01-01 00:00:00'),
(3, 1, 'rebootonu', '1970-01-01 00:00:00'),
(4, 1, 'addmaponu', '1970-01-01 00:00:00'),
(6, 1, 'porterror', '1970-01-01 00:00:00'),
(13, 1, 'addbillingonu', '1970-01-01 00:00:00'),
(15, 1, 'setupdevice', '1970-01-01 00:00:00'),
(18, 1, 'runmonitor', '1970-01-01 00:00:00'),
(19, 1, 'globaldevice', '1970-01-01 00:00:00'),
(60, 1, 'location', '1970-01-01 00:00:00'),
(22, 1, 'fdbmac', '1970-01-01 00:00:00'),
(43, 1, 'pondog', '1970-01-01 00:00:00'),
(59, 1, 'addcommonu', '1970-01-01 00:00:00'),
(46, 1, 'group', '1970-01-01 00:00:00'),
(47, 1, 'addtagonu', '1970-01-01 00:00:00'),
(48, 1, 'connectport', '1970-01-01 00:00:00'),
(49, 1, 'gallerydevice', '1970-01-01 00:00:00'),
(50, 1, 'logdevice', '1970-01-01 00:00:00'),
(58, 1, 'statusport', '1970-01-01 00:00:00'),
(95, 1, 'sklad', '1970-01-01 00:00:00'),
(80, 1, 'monitordevice', '1970-01-01 00:00:00'),
(103, 1, 'deletonu', '1970-01-01 00:00:00'),
(106, 1, 'rsyslog', '1970-01-01 00:00:00');

CREATE TABLE `config` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` text,
  `value` text,
  `types` text,
  `update` datetime NOT NULL DEFAULT '1970-01-01 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `config` (`id`, `name`, `value`, `types`, `update`) VALUES
(1, 'root', '/', 'test', '1970-01-01 00:00:00'),
(2, 'securityipst', NULL, 'ip', '2022-07-27 16:08:49'),
(3, 'countviewpageonu', '40', 'int', '2023-06-29 10:19:45'),
(4, 'url', 'http://176.124.130.177:888', 'url', '2023-06-29 10:19:45'),
(5, 'skin', 'pmon', 'text', '1970-01-01 00:00:00'),
(6, 'billingapikey', 'keyus', 'text', '1970-01-01 00:00:00'),
(7, 'telegramtoken', 'писати_без_бот', 'text', '2023-05-04 14:22:03'),
(8, 'telegramchatid', '-1001397572923', 'text', '2023-05-04 14:37:14'),
(9, 'security', 'off', 'enum', '1970-01-01 00:00:00'),
(10, 'api', 'on', 'enum', '1970-01-01 00:00:00'),
(11, 'map', 'on', 'enum', '2023-04-22 22:22:18'),
(12, 'task', 'on', 'enum', '1970-01-01 00:00:00'),
(13, 'marker', 'off', 'enum', '2023-04-22 22:18:31'),
(14, 'telegram', 'on', 'enum', '2023-01-06 13:54:45'),
(15, 'billing', 'on', 'enum', '1970-01-01 00:00:00'),
(16, 'unit', 'off', 'enum', '2023-01-03 17:14:52'),
(17, 'comment', 'on', 'enum', '2023-03-06 16:51:46'),
(18, 'lon', '', 'int', '1970-01-01 00:00:00'),
(19, 'lan', '', 'int', '1970-01-01 00:00:00'),
(20, 'criticsignal', '2', 'int', '2023-06-29 10:19:45'),
(21, 'countlistsitelog', '20', 'int', '1970-01-01 00:00:00'),
(22, 'critictemp', '70', 'int', '2022-07-27 15:58:53'),
(23, 'criticcpuolt', '20', 'int', '1970-01-01 00:00:00'),
(24, 'root_pmon', '/', 'test', '1970-01-01 00:00:00'),
(25, 'billingurl', 'http://192.168.1.4/api.php', 'url', '1970-01-01 00:00:00'),
(26, 'configport', 'off', 'enum', '2022-07-27 16:03:10'),
(27, 'pathwalk', 'snmpwalk', 'text', '1970-01-01 00:00:00'),
(28, 'pathget', 'snmpget', 'text', '1970-01-01 00:00:00'),
(29, 'snmpmode', 'native', 'text', '1970-01-01 00:00:00'),
(30, 'background', 'false', 'text', '1970-01-01 00:00:00'),
(31, 'cachetime', '60', 'int', '1970-01-01 00:00:00'),
(32, 'debug', 'false', 'text', '1970-01-01 00:00:00'),
(33, 'monitorapi', 'http://192.168.1.3/api.php', 'url', '2023-06-04 11:33:54'),
(34, 'sklad', 'on', 'enum', '1970-01-01 00:00:00'),
(35, 'pon', 'on', 'enum', '2023-05-10 15:30:27'),
(36, 'billingtype', 'mikrobill', 'enum', '1970-01-01 00:00:00'),
(37, 'tag', 'on', 'enum', '2023-03-07 11:25:17'),
(38, 'comment', 'on', 'enum', '2023-03-06 16:51:46'),
(39, 'currentdevice', '3', 'int', '1970-01-01 00:00:00'),
(40, 'geo_lan', '48.309652', 'text', '1970-01-01 00:00:00'),
(41, 'geo_lon', '25.918261', 'text', '1970-01-01 00:00:00'),
(42, 'onugraph', 'on', 'enum', '1970-01-01 00:00:00'),
(43, 'debugmysql', 'no', 'text', '1970-01-01 00:00:00'),
(44, 'statusport', 'on', 'enum', '1970-01-01 00:00:00'),
(45, 'errorport', 'on', 'enum', '1970-01-01 00:00:00'),
(46, 'viewipswitch', 'off', NULL, '2023-04-14 15:46:52'),
(47, 'badsignalstart', '26', NULL, '2023-06-29 10:19:45'),
(48, 'badsignalend', '40', NULL, '2023-06-29 10:19:45'),
(49, 'logsignal', 'on', NULL, '2023-03-30 17:13:50'),
(50, 'countviewpageswitch', '20', NULL, '2023-06-29 10:19:45'),
(51, 'rsyslog', 'off', NULL, '2023-04-25 21:16:34'),
(52, 'typemap', 'openstreetmap', NULL, '2023-05-03 14:19:26');
CREATE TABLE `connect_port` (
  `id` int(11) NOT NULL,
  `types` varchar(5) DEFAULT NULL,
  `cursfp` int(11) DEFAULT NULL,
  `curp` int(11) NOT NULL,
  `curd` int(11) NOT NULL,
  `connsfp` int(11) DEFAULT NULL,
  `connp` int(11) NOT NULL,
  `connd` int(11) NOT NULL,
  `note` text,
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `devicelogs` (
  `id` int(11) NOT NULL,
  `log` varchar(255) DEFAULT NULL,
  `deviceid` int(11) DEFAULT NULL,
  `onuid` int(11) DEFAULT NULL,
  `portid` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `descr` text,
  `who` varchar(255) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
CREATE TABLE `equipment` (
  `id` int(11) UNSIGNED NOT NULL,
  `cat` int(11) DEFAULT NULL,
  `sort` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `oidid` int(11) DEFAULT NULL,
  `model` text,
  `device` varchar(100) DEFAULT NULL,
  `name` text,
  `phpclass` text,
  `work` varchar(5) NOT NULL,
  `photo` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `fibers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `note` varchar(150) DEFAULT NULL,
  `geo` text,
  `kabel` int(11) DEFAULT NULL,
  `km` int(11) DEFAULT NULL,
  `location1` int(11) DEFAULT NULL,
  `tree1` int(11) DEFAULT NULL,
  `conn1` int(11) DEFAULT NULL,
  `location2` int(11) DEFAULT NULL,
  `tree2` int(11) DEFAULT NULL,
  `conn2` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` text,
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `historysignal` (
  `id` int(11) NOT NULL,
  `device` int(11) UNSIGNED DEFAULT NULL,
  `onu` int(11) DEFAULT NULL,
  `signal` varchar(16) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `kabel` (
  `id` int(11) UNSIGNED NOT NULL,
  `modules` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `volokon` int(11) NOT NULL,
  `types` int(11) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `decription` varchar(200) DEFAULT NULL,
  `km` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
CREATE TABLE `kabel_connector` (
  `id` int(11) NOT NULL,
  `typesid` int(11) DEFAULT NULL,
  `connectstart` int(11) DEFAULT NULL,
  `connectend` int(11) DEFAULT NULL,
  `color` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `kabel_module` (
  `id` int(11) UNSIGNED NOT NULL,
  `modulecolor` varchar(20) DEFAULT NULL,
  `moduleid` int(11) DEFAULT NULL,
  `kabelidid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `kabel_position` (
  `id` int(11) NOT NULL,
  `typesid` int(11) DEFAULT NULL,
  `xtop` int(11) DEFAULT NULL,
  `xleft` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `kabel_volokno` (
  `id` int(11) UNSIGNED NOT NULL,
  `voloknocolor` varchar(20) DEFAULT NULL,
  `voloknoid` int(11) DEFAULT NULL,
  `moduleid` int(11) DEFAULT NULL,
  `kabelidid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `kvarturu` (
  `id` int(10) UNSIGNED NOT NULL,
  `nomer` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idbydunok` int(11) DEFAULT NULL,
  `idkvarturu` int(11) DEFAULT NULL,
  `keyonu` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idonu` int(11) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('up','down','none') COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `location` (
  `id` int(11) NOT NULL,
  `name` text,
  `note` text,
  `photo` varchar(150) DEFAULT NULL,
  `lan` text,
  `lon` text,
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `monitor` (
  `id` int(11) NOT NULL,
  `deviceid` int(11) DEFAULT NULL,
  `name` varchar(500) DEFAULT NULL,
  `status` enum('none','up','down') NOT NULL DEFAULT 'none',
  `types` enum('olt','switch','onu','none','port') DEFAULT NULL,
  `time_online` datetime DEFAULT NULL,
  `time_offline` datetime DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `checker` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `monitoring` (
  `id` int(11) UNSIGNED NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `types` text,
  `values` text,
  `deviceid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `oid` (
  `id` int(11) UNSIGNED NOT NULL,
  `oidid` int(11) DEFAULT NULL,
  `types` text,
  `model` text,
  `pon` varchar(10) DEFAULT NULL,
  `oid` text,
  `format` varchar(50) DEFAULT NULL,
  `descr` text,
  `inf` varchar(10) DEFAULT NULL,
  `result` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `onus` (
  `idonu` int(11) NOT NULL,
  `olt` int(11) UNSIGNED DEFAULT NULL,
  `sw_shelf` int(11) DEFAULT NULL,
  `sw_slot` int(11) DEFAULT NULL,
  `sw_port` int(11) DEFAULT NULL,
  `portolt` bigint(22) DEFAULT NULL,
  `sw_ont` int(11) DEFAULT NULL,
  `keyonu` int(11) UNSIGNED DEFAULT NULL,
  `zte_idport` bigint(22) DEFAULT NULL,
  `status` int(11) UNSIGNED DEFAULT NULL,
  `wan` varchar(10) DEFAULT NULL,
  `inface` varchar(30) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `mac` varchar(20) DEFAULT NULL,
  `name` varchar(300) DEFAULT NULL,
  `descr` text,
  `sn` varchar(40) DEFAULT NULL,
  `rx` varchar(7) DEFAULT '0',
  `lastrx` varchar(7) DEFAULT NULL,
  `tx` varchar(7) DEFAULT NULL,
  `reason` varchar(10) DEFAULT NULL,
  `dist` int(11) DEFAULT NULL,
  `rating` int(11) UNSIGNED DEFAULT NULL,
  `updates` datetime DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `offline` datetime DEFAULT NULL,
  `online` datetime DEFAULT NULL,
  `changerx` datetime DEFAULT NULL,
  `rxstatus` varchar(7) DEFAULT NULL,
  `cron` int(11) DEFAULT NULL,
  `tag` varchar(300) DEFAULT NULL,
  `vendor` varchar(50) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `inspector` int(11) NOT NULL DEFAULT '1',
  `monitor` int(11) DEFAULT NULL,
  `comments` text,
  `apiget` int(11) DEFAULT NULL,
  `lan` text,
  `lon` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `onusdata` (
  `id` int(11) UNSIGNED NOT NULL,
  `onukey` varchar(70) NOT NULL,
  `tag` text,
  `uid` int(11) DEFAULT NULL,
  `name` text,
  `lan` text,
  `lon` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
CREATE TABLE `onus_comm` (
  `id` int(10) UNSIGNED NOT NULL,
  `added` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `userid` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `idonu` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `comm` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `pingstats` (
  `id` int(11) UNSIGNED NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `time` text,
  `system` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `pmonstats` (
  `id` int(10) UNSIGNED NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `badsignal` int(11) DEFAULT NULL,
  `countonu` int(11) DEFAULT NULL,
  `online` int(11) DEFAULT NULL,
  `offline` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `ponelement` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `tree` int(11) DEFAULT NULL,
  `types` int(11) DEFAULT NULL,
  `description` text,
  `location` int(11) DEFAULT NULL,
  `lan` varchar(100) DEFAULT NULL,
  `lon` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `pontree` (
  `id` int(11) NOT NULL,
  `location` int(11) DEFAULT NULL,
  `name` text,
  `lan` text,
  `lon` text,
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `rules` (
  `id` int(11) UNSIGNED NOT NULL,
  `types` varchar(255) NOT NULL DEFAULT '',
  `descr` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `rules` (`id`, `types`, `descr`) VALUES
(1, 'fdbmac', 'rules_viewfdb'),
(2, 'deletonu', 'rules_deletonu'),
(3, 'rebootonu', 'rules_rebootonu'),
(4, 'addmaponu', 'rules_addmap'),
(5, 'addcommonu', 'rules_addcomm'),
(6, 'porterror', 'rules_vewporterr'),
(7, 'pondog', 'rules_addpondog'),
(8, 'statusport', 'rules_viewstatusport'),
(9, 'location', 'rules_location'),
(10, 'sklad', 'rules_sklad'),
(11, 'group', 'rules_group'),
(12, 'addtagonu', 'rules_addtag'),
(13, 'addbillingonu', 'rules_addbilling'),
(14, 'connectport', 'rules_connectport'),
(15, 'setupdevice', 'rules_setupdevice'),
(16, 'gallerydevice', 'rules_gallerydevice'),
(17, 'logdevice', 'rules_logdevice'),
(18, 'runmonitor', 'rules_runmonitor'),
(19, 'globaldevice', 'rules_globaldevice'),
(20, 'monitordevice', 'rules_monitordevice'),
(21, 'setup', 'rules_pmonsetup'),
(22, 'rsyslog', 'descr_rsyslog');
CREATE TABLE `sessions` (
  `sid` varchar(32) NOT NULL DEFAULT '',
  `uid` int(10) NOT NULL DEFAULT '0',
  `username` varchar(40) NOT NULL DEFAULT '',
  `class` tinyint(4) NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `time` bigint(30) NOT NULL DEFAULT '0',
  `url` varchar(150) NOT NULL DEFAULT '',
  `useragent` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `sfp` (
  `id` int(11) UNSIGNED NOT NULL,
  `model` varchar(500) DEFAULT NULL,
  `cat` int(11) DEFAULT NULL,
  `sort` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `types` text,
  `wavelength` varchar(100) DEFAULT NULL,
  `connector` text,
  `dist` text,
  `speed` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `sfp` (`id`, `model`, `cat`, `sort`, `types`, `wavelength`, `connector`, `dist`, `speed`) VALUES
(1, 'SFP Alistar Модуль SFP 1000BASE-BX 1SM WDM LC 3KM', NULL, 0, 'sm', '1310', 'lc', '3', '1'),
(2, 'SFP Alistar Модуль SFP 1000BASE-BX 1SM WDM LC 3KM', NULL, 0, 'sm', '1550', 'lc', '3', '1');
CREATE TABLE `swcron` (
  `id` int(11) NOT NULL,
  `status` enum('yes','no','go') NOT NULL DEFAULT 'no',
  `oltid` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `switch` (
  `id` int(11) NOT NULL,
  `ping` enum('up','down') NOT NULL DEFAULT 'up',
  `monitor` enum('yes','no') DEFAULT NULL,
  `status` enum('yes','no','go') NOT NULL DEFAULT 'no',
  `gallery` enum('yes','no') NOT NULL DEFAULT 'no',
  `connect` enum('yes','no') NOT NULL DEFAULT 'no',
  `jobid` int(11) NOT NULL DEFAULT '0',
  `typecheck` varchar(10) DEFAULT NULL,
  `oidid` int(11) DEFAULT NULL,
  `inf` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `netip` varchar(50) DEFAULT NULL,
  `mac` varchar(100) DEFAULT NULL,
  `sn` varchar(200) DEFAULT NULL,
  `class` varchar(50) DEFAULT NULL,
  `snmpro` varchar(50) DEFAULT NULL,
  `snmprw` varchar(50) DEFAULT NULL,
  `countonu` int(11) UNSIGNED DEFAULT NULL,
  `device` enum('olt','switch','switchl2','switchl3','router','antena','ups','battery','other') DEFAULT NULL,
  `name` text,
  `firmware` text,
  `olt_descr` text,
  `note` text,
  `uptime` varchar(200) DEFAULT NULL,
  `timecheck` int(11) DEFAULT NULL,
  `timechecklast` int(11) DEFAULT NULL,
  `place` varchar(200) DEFAULT NULL,
  `updates` datetime DEFAULT NULL,
  `updates_rx` datetime DEFAULT NULL,
  `updates_port` datetime DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `offonu` int(11) DEFAULT NULL,
  `ononu` int(11) DEFAULT NULL,
  `losonu` int(11) DEFAULT NULL,
  `maxonu` int(11) DEFAULT NULL,
  `allonu` int(11) DEFAULT NULL,
  `todayonu` int(11) NOT NULL DEFAULT '0',
  `img` varchar(100) DEFAULT NULL,
  `photo` text,
  `skladid` int(11) DEFAULT NULL,
  `username` varchar(40) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `location` int(11) DEFAULT NULL,
  `locationname` text,
  `groups` int(11) NULL,
  `timeping` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `switch_log` (
  `id` int(11) NOT NULL,
  `deviceid` int(11) NOT NULL DEFAULT '0',
  `types` enum('cron','system','user','switch','deletonu','addonu') DEFAULT 'system',
  `added` datetime DEFAULT NULL,
  `message` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `switch_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `deviceid` int(11) NOT NULL,
  `logtext` text COLLATE utf8_unicode_ci,
  `logtime` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `switch_photo` (
  `id` int(11) NOT NULL,
  `deviceid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(300) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `photo` varchar(300) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `switch_pon` (
  `id` int(10) UNSIGNED NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `oltid` text,
  `pon` text,
  `card` varchar(15) DEFAULT NULL,
  `type` text,
  `sfpid` varchar(40) DEFAULT NULL,
  `idportolt` bigint(22) DEFAULT NULL,
  `support` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `online` int(11) DEFAULT NULL,
  `offline` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `switch_port` (
  `id` int(11) NOT NULL,
  `monitor` enum('yes','no') NOT NULL DEFAULT 'no',
  `sms` enum('yes','no') NOT NULL DEFAULT 'no',
  `log` enum('yes','no') NOT NULL DEFAULT 'no',
  `error` enum('yes','no') NOT NULL DEFAULT 'no',
  `deviceid` int(11) NOT NULL,
  `llid` bigint(22) DEFAULT NULL,
  `operstatus` enum('up','down','none') DEFAULT NULL,
  `nameport` varchar(100) DEFAULT NULL,
  `descrport` varchar(200) DEFAULT NULL,
  `typeport` varchar(50) DEFAULT NULL,
  `speedport` varchar(10) DEFAULT NULL,
  `note` text,
  `added` datetime DEFAULT NULL,
  `updates` datetime DEFAULT NULL,
  `timedown` datetime DEFAULT NULL,
  `timeup` datetime DEFAULT NULL,
  `information` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `switch_port_err` (
  `id` int(11) UNSIGNED NOT NULL,
  `llid` bigint(22) DEFAULT NULL,
  `deviceid` int(11) DEFAULT NULL,
  `status_outerror` varchar(5) DEFAULT 'no',
  `status_inerror` varchar(5) DEFAULT 'no',
  `inerror` bigint(22) DEFAULT NULL,
  `newin` int(11) NOT NULL DEFAULT '0',
  `outerror` bigint(22) DEFAULT '0',
  `newout` int(11) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `swlogport` (
  `id` int(11) NOT NULL,
  `deviceid` int(11) NOT NULL DEFAULT '0',
  `portid` int(11) NOT NULL DEFAULT '0',
  `status` enum('up','down') DEFAULT 'down',
  `added` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `class` int(11) NOT NULL DEFAULT '1',
  `ip` varchar(200) DEFAULT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(200) NOT NULL,
  `added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastactivity` datetime DEFAULT NULL,
  `port` text,
  `setip` varchar(50) DEFAULT NULL,
  `onlyip` enum('on','off') NOT NULL DEFAULT 'off',
  `url` varchar(200) DEFAULT NULL,
  `hideonu` enum('yes','no') DEFAULT 'no',
  `viewlist` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `users` (`id`, `username`, `name`, `class`, `ip`, `password`, `email`, `added`, `lastactivity`, `port`, `setip`, `onlyip`, `url`, `hideonu`, `viewlist`) VALUES
(1, 'user', 'alex', 7, '193.8.47.124', '20ccbe71c69cb25e4e0095483cb63bd394a12b23', 'user@email.com', '2022-07-24 17:01:20', '2023-06-29 10:42:52', 'a:8:{i:52;s:4:\"show\";i:510;s:4:\"show\";i:57;s:4:\"show\";i:610;s:4:\"show\";i:62;s:4:\"show\";i:1710;s:4:\"show\";i:1510;s:4:\"show\";i:153;s:4:\"show\";}', '127.0.0.2', 'off', '/ajax/ajaxstats.php', 'no', 'yes'),
(7, 'user1', 'alex', 1, '176.124.128.30', 'a483569ff59977fc3aa7127307ac7dffb0b21082', 'user@email.com', '2023-01-09 17:57:47', '2023-06-08 11:52:54', NULL, '', 'off', '/?doexit', 'yes', 'yes');

ALTER TABLE `apikey`  ADD PRIMARY KEY (`id`);
ALTER TABLE `checkaccess`  ADD PRIMARY KEY (`id`);
ALTER TABLE `config`  ADD PRIMARY KEY (`id`);
ALTER TABLE `connect_port`  ADD PRIMARY KEY (`id`);
ALTER TABLE `devicelogs`  ADD PRIMARY KEY (`id`);
ALTER TABLE `equipment`  ADD PRIMARY KEY (`id`);
ALTER TABLE `fibers`  ADD PRIMARY KEY (`id`);
ALTER TABLE `groups`  ADD PRIMARY KEY (`id`);
ALTER TABLE `historysignal`  ADD PRIMARY KEY (`id`);
ALTER TABLE `kabel`  ADD PRIMARY KEY (`id`);
ALTER TABLE `kabel_connector`  ADD PRIMARY KEY (`id`);
ALTER TABLE `kabel_module`  ADD PRIMARY KEY (`id`);
ALTER TABLE `kabel_position`  ADD PRIMARY KEY (`id`);
ALTER TABLE `kabel_volokno`  ADD PRIMARY KEY (`id`);
ALTER TABLE `kvarturu`  ADD PRIMARY KEY (`id`);
ALTER TABLE `location`  ADD PRIMARY KEY (`id`);
ALTER TABLE `monitor`  ADD PRIMARY KEY (`id`);
ALTER TABLE `monitoring`  ADD PRIMARY KEY (`id`);
ALTER TABLE `oid`  ADD PRIMARY KEY (`id`);
ALTER TABLE `onus`  ADD PRIMARY KEY (`idonu`);
ALTER TABLE `onusdata`  ADD PRIMARY KEY (`id`),  ADD UNIQUE KEY `onukey` (`onukey`);
ALTER TABLE `onus_comm`  ADD PRIMARY KEY (`id`);
ALTER TABLE `pingstats`  ADD PRIMARY KEY (`id`);
ALTER TABLE `pmonstats`  ADD PRIMARY KEY (`id`);
ALTER TABLE `ponelement`  ADD PRIMARY KEY (`id`);
ALTER TABLE `pontree`  ADD PRIMARY KEY (`id`);
ALTER TABLE `rules`  ADD PRIMARY KEY (`id`);
ALTER TABLE `sfp`  ADD PRIMARY KEY (`id`);
ALTER TABLE `swcron`  ADD PRIMARY KEY (`id`);
ALTER TABLE `switch`  ADD PRIMARY KEY (`id`);
ALTER TABLE `switch_log`  ADD PRIMARY KEY (`id`);
ALTER TABLE `switch_logs`  ADD PRIMARY KEY (`id`),  ADD KEY `deviceid_index` (`deviceid`);
ALTER TABLE `switch_photo`  ADD PRIMARY KEY (`id`);
ALTER TABLE `switch_pon`  ADD PRIMARY KEY (`id`);
ALTER TABLE `switch_port`  ADD PRIMARY KEY (`id`);
ALTER TABLE `switch_port_err`  ADD PRIMARY KEY (`id`);
ALTER TABLE `swlogport`  ADD PRIMARY KEY (`id`);
ALTER TABLE `users`  ADD PRIMARY KEY (`id`);
ALTER TABLE `apikey`  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `checkaccess`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;
ALTER TABLE `config`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
ALTER TABLE `connect_port`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `devicelogs`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `fibers`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `groups`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `historysignal`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `kabel`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `kabel_connector`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `kabel_module`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `kabel_position`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `kabel_volokno`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `kvarturu`  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `location`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `monitor`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `monitoring`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `oid`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `onus`  MODIFY `idonu` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `onusdata`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `onus_comm`  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `pingstats`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `pmonstats`  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ponelement`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `pontree`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `rules`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
ALTER TABLE `sfp`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `swcron`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `switch`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `switch_log`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `switch_logs`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `switch_photo`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `switch_pon`  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `switch_port`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `switch_port_err`  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
ALTER TABLE `swlogport`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;
CREATE TABLE battery_history (
id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
deviceid INT(11),
batteryid INT(11),
mon_types TEXT,
history TEXT,
added DATETIME
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE battery_used (
id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
mon_types TEXT,
batteryid INT(11),
deviceid INT(11),
added DATETIME
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `config` (`id`, `name`, `value`, `types`, `update`) VALUES (54, 'criticonu64', '2', 'int', '2023-06-29 10:19:45');
INSERT INTO `config` (`id`, `name`, `value`, `types`, `update`) VALUES (55, 'criticonu128', '2', 'int', '2023-06-29 10:19:45');
TRUNCATE `equipment`;
INSERT INTO `equipment` (`id`, `cat`, `sort`, `oidid`, `model`, `device`, `name`, `phpclass`, `work`, `photo`) VALUES
(1,1,1,1,'P3310B','olt','BDCOM','bdcomepon','yes','P3310B.png'),
(2,1,2,1,'P3310C','olt','BDCOM','bdcomepon','yes','P3310C.png'),
(3,1,3,1,'P3310D','olt','BDCOM','bdcomepon','yes','P3310D.png'),
(4,1,4,1,'P3608-2TE','olt','BDCOM','bdcomepon','yes','P3608-2TE.png'),
(5,1,5,1,'P3616-2TE','olt','BDCOM','bdcomepon','yes','P3608-2TE.png'),
(6,1,6,2,'GP3600-08','olt','BDCOM','bdcomgpon','yes','GP3600-08.png'),
(7,1,7,2,'GP3600-16','olt','BDCOM','bdcomgpon','yes','GP3600-16.png'),
(8,1,8,1,'P3608B','olt','BDCOM','bdcomepon','yes','P3608B.png'),
(9,4,9,3,'C220','olt','ZTE','zte220_2','yes','ztec220.png'),
(10,4,10,7,'C320 (2.1)','olt','ZTE','zte320_2','yes','ztec320.png'),
(11,4,11,6,'C610','olt','ZTE','zte600_1','yes','ztec610.png'),
(38,4,15,6,'C620','olt','ZTE','zte620_1','yes','ztec620.png'),
(12,4,12,7,'C300 (2.1)','olt','ZTE','zte300_2','yes','ztec300.png'),
(13,4,13,NULL,'C300','olt','ZTE','zte3','no',NULL),
(16,2,16,13,'FD1104','olt','C-DATA','cdata1108','yes','cdata1108.png'),
(14,7,1,8,'(all)','switch','MikroTik','mkt2011rm','yes','mikrotik2011rm.png'),
(15,8,21,9,'EL5610-04P','olt','GCOM','gcomeponel5610','yes','gcomel561004.png'),
(17,2,17,13,'FD1108','olt','C-DATA','cdata1108','yes','cdata1108.png'),
(18,2,18,15,'FD1216','olt','C-DATA','cdataf1216s','yes','cdata1208.png'),
(19,2,19,15,'FD1208','olt','C-DATA','cdata1208sr2dap','yes','cdata1208.png'),
(20,2,20,12,'FD1616SN','olt','C-DATA','cdataf1616sn','yes','cdataf1616sn.png'),
(21,8,22,9,'EL5610-08P','olt','GCOM','gcomeponel5610','yes','gcomel561008.png'),
(22,8,23,9,'EL5610-16P','olt','GCOM','gcomeponel5610','yes','gcomel561016.png'),
(23,9,1,10,'E9004','olt','SmartFiber','smartfiberepon','yes','sm4.png'),
(24,9,2,10,'E9008','olt','SmartFiber','smartfiberepon','yes','sm8.png'),
(25,5,25,NULL,'EL5610-16P','olt','GCOM','gcomepon','no',NULL),
(26,5,26,NULL,'EL5610-08P','olt','GCOM','gcomgpon','no',NULL),
(27,5,27,NULL,'EL5610-04P','olt','GCOM','gcomgpon','no',NULL),
(28,6,30,5,'(all)','switch','D-Link','dlinkdgs1106','yes','dlink1106me.png'),
(29,9,3,10,'E9016','olt','SmartFiber','smartfiberepon','yes','sm16.png'),
(30,3,30,14,'MA5683T','olt','Huawei','huawei5608t','yes','huawei5683t.png'),
(31,3,31,17,'S2326TP','switch','Huawei ','huawei2326switch','yes','huaweis2326tp.png'),
(32,3,32,14,'MA5608T','olt','Huawei','huawei5608t','yes','huawei5608t.png'),
(33,9,4,11,'G9008','olt','SmartFiber','smartfibergpon','yes','sm16.png'),
(34,9,4,11,'G9016','olt','SmartFiber','smartfibergpon','yes','sm16.png'),
(35,2,15,15,'1204','olt','C-DATA','cdata1204','yes','cdata1208.png'),
(36,11,1,16,'V1600G1','olt','V-SOL','vsolv16','yes','vsolv16g1.png'),
(37,8,2,18,'S6100-16X','switch','GCOM','gcoms610016x','yes','gcoms6100.png'),
(40,10,40,4,'SGSW-24240','switch','PLANET ','planet2424','yes','SGSW-24240.png'),
(50,2,19,12,'FD1608SN-2AC','olt','C-DATA','cdatafd1608','yes','cdatafd1608sn.png');
CREATE TABLE `geodevice` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` text,
  `iconmapper` text,
  `device` text,
  `deviceid` int(11) DEFAULT NULL,
  `iconup` text,
  `icondown` text,
  `added` datetime DEFAULT NULL,
  `offline` datetime DEFAULT NULL,
  `online` datetime DEFAULT NULL,
  `status` text,
  `lan` text,
  `lon` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `ipaddress` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `blockid` int(11) DEFAULT NULL,
  `idgroups` int(11) DEFAULT NULL,
  `status` text,
  `ip` text,
  `name` text,
  `note` text,
  `vlan` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `ipblock` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ipblock` text,
  `vlans` text,
  `name` text,
  `note` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `ipgroups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` text,
  `note` text,
  `color` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `ipvlans` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` text,
  `vlan` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE battery (
id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
voltage ENUM('12', '24', '48', '72') DEFAULT '12',
amper TEXT,
types TEXT,
model TEXT,
name TEXT,
photo TEXT,
added DATETIME,
services DATETIME
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE mon_device (
id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
dc TEXT,
name TEXT,
netip TEXT,
snmpro TEXT,
typebattery ENUM('12', '24', '48', '72') DEFAULT '12',
oidvolt TEXT NULL,
resultoidvolt TEXT NULL,
oidenergy TEXT NULL,
resultoidenergy TEXT NULL,
volt TEXT NULL,
energystatus ENUM('yes', 'no') DEFAULT 'no',
energy VARCHAR(5) NULL,
status0 VARCHAR(10) NULL,
status20 VARCHAR(10) NULL,
status40 VARCHAR(10) NULL,
status60 VARCHAR(10) NULL,
status80 VARCHAR(10) NULL,
status100 VARCHAR(10) NULL,
channel INT(11) NULL,
energytime DATETIME,
locationid INT(11) NULL,
groups INT(11) NULL,
types VARCHAR(30) NULL,
sendtelegram ENUM('yes', 'no') DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE mon_ping3 (
id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
name TEXT,
netip TEXT,
snmpro TEXT,
typebattery ENUM('12', '24', '48', '72') DEFAULT '12',
calibrations VARCHAR(10) NULL,
energytime DATETIME,
volt VARCHAR(11) NULL,
energystatus ENUM('yes', 'no') DEFAULT 'no',
energy VARCHAR(5) NULL,
status0 VARCHAR(10) NULL,
status20 VARCHAR(10) NULL,
status40 VARCHAR(10) NULL,
status60 VARCHAR(10) NULL,
status80 VARCHAR(10) NULL,
status100 VARCHAR(10) NULL,
channel INT(11) NULL,
locationid INT(11) NULL,
groups INT(11) NULL,
types VARCHAR(30) NULL,
sendtelegram ENUM('yes', 'no') DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE mon_voltage (
id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
energy TEXT,
volt TEXT,
deviceid INT(11),
mon_types TEXT,
added DATETIME
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
TRUNCATE `oid`;
INSERT INTO `oid` (`id`, `oidid`, `types`, `model`, `pon`, `oid`, `format`, `descr`, `inf`, `result`) VALUES
(1,1,'uptime','bdcom','device','1.3.6.1.2.1.1.3.0','integer','oid_uptime','health',NULL),
(2,1,'cpu','bdcom','device','1.3.6.1.4.1.3320.9.109.1.1.1.1.3.1','integer','oid_cpu','health',NULL),
(3,1,'temp','bdcom','device','1.3.6.1.4.1.3320.9.181.1.1.7.1','integer','oid_temp','health',NULL),
(4,1,'ifinerrors','bdcom','port','1.3.6.1.2.1.2.2.1.14.keyport','integer','oid_ifinerrors','monitor',NULL),
(5,1,'ifouterrors','bdcom','port','1.3.6.1.2.1.2.2.1.20.keyport','integer','oid_ifouterrors','monitor',NULL),
(6,3,'listmac','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.2.1.1.6','hex','oid_list_mac','onu',NULL),
(7,3,'mac','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.2.1.1.6.keyonu','integer','oid_epon_mac','onu',NULL),
(8,3,'eth','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.5.1.2.keyonu.1','integer','oid_epon_wan','onu',NULL),
(9,3,'status','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.2.1.1.1.keyonu','integer','oid_epon_status','onu',NULL),
(10,3,'reason','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.17.keyonu','integer','oid_epon_reason','onu',NULL),
(11,3,'rx','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.5.keyonu','string','oid_epon_rx','onu',NULL),
(12,3,'tx','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.4.keyonu','string','oid_epon_tx','onu',NULL),
(13,3,'dist','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.2.1.1.10.keyonu','integer','oid_epon_dist','onu',NULL),
(14,3,'model','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.1.1.5.keyonu','string','oid_epon_model','onu',NULL),
(15,3,'device','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.1.1.2.keyonu','string','oid_epon_device','onu',NULL),
(16,3,'cpu','zte','device','1.3.6.1.4.1.3902.1015.2.1.1.3.1.9.1.1.1','integer','oid_cpu','health',NULL),
(17,3,'temp','zte','device','1.3.6.1.4.1.3902.1015.2.1.3.2.0','integer','oid_temp','health',NULL),
(18,3,'uptime','zte','device','1.3.6.1.2.1.1.3.0','string','oid_uptime','health',NULL),
(21,3,'volt','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.2.keyonu','string','oid_epon_volt','onu',NULL),
(20,3,'bias','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.3.keyonu','string','oid_epon_bias_curent','onu',NULL),
(19,3,'listport','zte','port','1.3.6.1.2.1.31.1.1.1.1','string','oid_list_port','global',NULL),
(22,3,'temp','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.1.keyonu','string','oid_epon_volt','onu',NULL),
(23,3,'offline','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.14.keyonu','string','oid_epon_time_offline','onu',NULL),
(24,3,'config','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.5.keyonu','string','oid_epon_vendor','onu',NULL),
(195,12,'adminstatus','cdata','gpon','1.3.6.1.4.1.17409.2.8.5.1.1.4.keyonu.0.1',NULL,'','onu','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(26,3,'vlanmode','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.10.1.1.1.keyonu.1','string','oid_epon_lan_mode','onu',NULL),
(29,4,'status','planet','port','1.3.6.1.2.1.2.2.1.8.keyport','integer','oid_olt_port_status','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(30,5,'status','dlink','port','1.3.6.1.2.1.2.2.1.8.keyport','integer','oid_olt_port_status','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(31,6,'status','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.2.keyport.keyonu','integer','oid_gpon_status','onu',NULL),
(32,6,'dist','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.10.1.2.keyport.keyonu','string','oid_gpon_dist','onu',NULL),
(33,6,'name','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.3.1.2.keyport.keyonu','string','oid_gpon_name','onu',NULL),
(34,6,'descr','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.3.1.3.keyport.keyonu','string','oid_gpon_descr','onu',NULL),
(35,6,'reason','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.7.keyport.keyonu','integer','oid_gpon_reason','onu',NULL),
(36,6,'timeup','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.5.keyport.keyonu','string','oid_gpon_timeup','onu',NULL),
(37,6,'timedown','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.6.keyport.keyonu','string','oid_gpon_timedown','onu',NULL),
(38,6,'rxolt','zte','gpon','1.3.6.1.4.1.3902.1082.500.1.2.4.2.1.2.keyport.keyonu','string','oid_gpon_rxolt','onu',NULL),
(39,6,'rx','zte','gpon','1.3.6.1.4.1.3902.1082.500.20.2.2.2.1.10.keyport.keyonu.1','string','oid_gpon_rx','onu',NULL),
(40,6,'tx','zte','gpon','1.3.6.1.4.1.3902.1082.500.20.2.2.2.1.14.keyport.keyonu.1','string','','onu',NULL),
(41,6,'eth','zte','gpon','1.3.6.1.4.1.3902.1082.500.20.2.3.2.1.6.keyport.keyonu.1','string','','onu',NULL),
(42,6,'model','zte','gpon','1.3.6.1.4.1.3902.1082.500.20.2.1.2.1.8.keyport.keyonu','string','oid_gpon_model','onu',NULL),
(43,6,'oper','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.3.keyport.keyonu','integer','oid_gpon_operstatus','onu',NULL),
(44,6,'countmac','zte','gpon','1.3.6.1.4.1.3902.1082.500.20.2.4.15.1.10.keyport.keyonu.1','string','oid_gpon_count_mac','onu',NULL),
(45,6,'typereg','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.3.keyport.keyonu','string','oid_gpon_type_reg','onu',NULL),
(46,6,'admin','zte','gpon','1.3.6.1.4.1.3902.1082.500.10.2.3.8.1.1.keyport.keyonu','integer','oid_gpon_admin_status','onu',NULL),
(47,1,'listname','bdcom','epon','1.3.6.1.2.1.2.2.1.2','string','oid_epon_all_name','onu',NULL),
(48,1,'mac','bdcom','epon','1.3.6.1.4.1.3320.101.10.1.1.3.keyonu','hex-string','oid_epon_mac','onu','macbdcom'),
(49,1,'dist','bdcom','epon','1.3.6.1.4.1.3320.101.10.1.1.27.keyonu','string','oid_epon_dist','onu',NULL),
(50,1,'status','bdcom','epon','1.3.6.1.4.1.3320.101.10.1.1.26.keyonu','integer','oid_epon_status','onu',''),
(51,1,'rx','bdcom','epon','1.3.6.1.4.1.3320.101.10.5.1.5.keyonu','integer','oid_epon_rx','onu','rxbdcom'),
(52,1,'temp','bdcom','epon','1.3.6.1.4.1.3320.101.10.5.1.2.keyonu','integer','oid_epon_temp','onu','tempbdcom'),
(53,1,'tx','bdcom','epon','1.3.6.1.4.1.3320.101.10.5.1.6.keyonu','string','oid_epon_tx','onu','rxbdcom'),
(54,1,'inface','bdcom','epon','1.3.6.1.2.1.2.2.1.2.keyonu','string','oid_epon_inface','onu',NULL),
(55,1,'vendor','bdcom','epon','1.3.6.1.4.1.3320.101.10.1.1.1.keyonu','string','oid_epon_vendor','onu',NULL),
(56,1,'model','bdcom','epon','1.3.6.1.4.1.3320.101.10.1.1.2.keyonu','string','oid_epon_model','onu',NULL),
(57,1,'eth','bdcom','epon','1.3.6.1.4.1.3320.101.12.1.1.8.keyonu.1','','oid_epon_eth','onu','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(58,1,'pvid','bdcom','epon','1.3.6.1.4.1.3320.101.12.1.1.3.keyonu.1','integer','oid_epon_volt','onu','voltbdcom'),
(59,1,'rxolt','bdcom','epon','1.3.6.1.4.1.3320.101.108.1.3.keyonu','string','oid_epon_rxolt','onu','rxbdcom'),
(60,1,'listport','bdcom','epon','1.3.6.1.4.1.3320.101.107.1.1','string','oid_epon_list_port','global',NULL),
(61,1,'status','bdcom','port','1.3.6.1.2.1.2.2.1.8.keyport','integer','oid_epon_port_satus','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(62,1,'temp','bdcom','device','1.3.6.1.4.1.3320.9.181.1.1.7.1','integer','oid_epon_switch_temp','health',NULL),
(63,50,'status','equicom','device','1.3.6.1.4.1.35160.1.26.0','integer','oid_status_220','health','a:2:{i:1;s:2:\"up\";i:0;s:4:\"down\";}'),
(64,50,'volt','equicom','device','1.3.6.1.4.1.35160.1.16.1.13.3','string','oid_volt_battery','health','=FUNCT1INT10='),
(65,2,'sn','bdcom','gpon','1.3.6.1.4.1.3320.10.3.1.1.4.keyonu','hex','oid_gpon_sn','onu',NULL),
(66,2,'listname','bdcom','gpon','1.3.6.1.2.1.2.2.1.2','hex','oid_gpon_list_inface','onu',NULL),
(67,2,'dist','bdcom','gpon','1.3.6.1.4.1.3320.10.3.1.1.33.keyonu','integer','oid_gpon_onu_dist','onu','=FUNCT1INT10='),
(68,2,'status','bdcom','gpon','1.3.6.1.2.1.2.2.1.8.keyonu','integer','oid_gpon_onu_status','onu',NULL),
(69,2,'rx','bdcom','gpon','1.3.6.1.4.1.3320.10.3.4.1.2.keyonu','auto','','onu','=FUNCT1INT10='),
(70,2,'tx','bdcom','gpon','1.3.6.1.4.1.3320.10.3.4.1.3.keyonu','auto','','onu','=FUNCT1INT10='),
(71,2,'reason','bdcom','gpon','1.3.6.1.4.1.3320.10.3.1.1.35.keyonu','auto','','onu',NULL),
(72,2,'admin','bdcom','gpon','1.3.6.1.4.1.3320.10.4.1.1.3.keyonu.1','auto','','onu',NULL),
(73,2,'vendor','bdcom','gpon','1.3.6.1.4.1.3320.10.3.1.1.9.keyonu','string','','onu',NULL),
(74,2,'uptime','bdcom','gpon','1.3.6.1.2.1.2.2.1.9.keyonu','auto','','onu',NULL),
(75,2,'name','bdcom','gpon','1.3.6.1.2.1.31.1.1.1.18.keyonu','auto','','onu',NULL),
(76,2,'eth','bdcom','gpon','1.3.6.1.4.1.3320.10.4.1.1.3.keyonu.1','auto','','onu',NULL),
(77,2,'ifinerrors','bdcom','port','1.3.6.1.2.1.2.2.1.14.keyport','integer','oid_ifinerrors','monitor','monitor'),
(78,2,'status','bdcom','port','1.3.6.1.2.1.2.2.1.8.keyport','integer','oid_gpon_port_satus','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(79,5,'listport','dlink','port','1.3.6.1.2.1.31.1.1.1.1','auto','oid_get_list_port','global',NULL),
(80,5,'status','dlink','port','1.3.6.1.2.1.2.2.1.8.keyport','auto','','global','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(81,5,'ifinerrors','dlink','port','1.3.6.1.2.1.2.2.1.14.keyport','auto','','global',NULL),
(82,5,'ifouterrors','dlink','port','1.3.6.1.2.1.2.2.1.20.keyport','auto','','global',NULL),
(83,5,'uptime','dlink','device','1.3.6.1.2.1.1.3.0','auto','','health',NULL),
(84,2,'cpu','bdcom','device','1.3.6.1.2.1.25.3.3.1.2.1','auto','oid_cpu','health',NULL),
(85,2,'uptime','bdcom','device','1.3.6.1.2.1.1.3.0','auto','','health',NULL),
(86,2,'firmware','bdcom','device','1.3.6.1.2.1.1.1.0','auto','','health',NULL),
(87,15,'rx','c-data','epon','1.3.6.1.4.1.17409.2.3.4.2.1.4.keyonu.0.0','auto','','onu','=FUNCT1INT100='),
(88,15,'dist','c-data','epon','1.3.6.1.4.1.17409.2.3.4.1.1.15.keyonu','auto','','onu',NULL),
(89,15,'name','c-data','epon','1.3.6.1.4.1.17409.2.3.4.1.1.2.keyonu','auto','','onu',NULL),
(90,15,'listname','c-data','epon','1.3.6.1.4.1.17409.2.3.4.1.1.7','auto','','onu',NULL),
(91,15,'status','c-data','epon','1.3.6.1.4.1.17409.2.3.4.1.1.8.keyonu','auto','','onu',NULL),
(92,15,'listporteth','c-data','port','1.3.6.1.4.1.17409.2.3.2.1.1.4.1.0','auto','','global',NULL),
(93,15,'listportpon','c-data','port','1.3.6.1.4.1.17409.2.3.3.1.1.21.1.0','auto','','global',NULL),
(94,15,'tx','c-data','epon','1.3.6.1.4.1.17409.2.3.4.2.1.5.keyonu.0.0','auto','','onu','=FUNCT1INT100='),
(95,15,'vendor','c-data','epon','1.3.6.1.4.1.17409.2.3.4.1.1.26.keyonu','auto','','onu',NULL),
(96,15,'model','c-data','epon','1.3.6.1.4.1.17409.2.3.4.1.1.25.keyonu','auto','','onu',NULL),
(97,15,'eth','c-data','epon','1.3.6.1.4.1.17409.2.3.5.1.1.5.keyonu.0.1','auto','','onu',NULL),
(98,15,'temp','c-data','device','1.3.6.1.4.1.34592.1.3.100.1.8.6.0','auto','','health','=FUNCT1INT10='),
(99,15,'uptime','c-data','device','1.3.6.1.4.1.17409.2.3.1.2.1.1.5.1','auto','','health',NULL),
(100,15,'cpu','c-data','device','1.3.6.1.4.1.34592.1.3.100.1.8.1.0','auto','','health',NULL),
(101,15,'name','c-data','device','1.3.6.1.4.1.17409.2.3.1.2.1.1.2.1','auto','','health',NULL),
(102,15,'reason','c-data','epon','1.3.6.1.4.1.34592.1.3.100.12.3.1.1.7.keyonu','auto','','onu','a:2:{s:4:\"losi\";s:4:\"err6\";s:10:\"dying-gasp\";s:4:\"err1\";}'),
(103,15,'status','c-data','portsfp','1.3.6.1.4.1.17409.2.3.2.1.1.6.1.0.keyport','auto','','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(104,15,'status','c-data','portepon','1.3.6.1.4.1.17409.2.3.3.1.1.5.1.0.keyport','auto','','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(105,13,'rx','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.36.1.keyport.keyonu','auto','','onu',NULL),
(106,13,'tx','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.37.1.keyport.keyonu','auto','','onu',NULL),
(107,13,'reason','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.45.1.keyport.keyonu','auto','','onu','a:2:{s:4:\"losi\";s:4:\"err6\";s:10:\"dying-gasp\";s:4:\"err1\";}'),
(108,13,'status','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.11.1.keyport.keyonu','auto','','onu',NULL),
(109,13,'dist','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.13.1.keyport.keyonu','auto','','onu',NULL),
(110,13,'model','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.6.1.keyport.keyonu','auto','','onu',NULL),
(111,13,'vendor','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.5.1.keyport.keyonu','auto','','onu',NULL),
(112,13,'eth','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.14.1.keyport.keyonu','auto','','onu',NULL),
(113,13,'uptime','c-data','device','1.3.6.1.4.1.34592.1.3.1.5.2.1.1.8.1','auto','','health',NULL),
(114,13,'cpu','c-data','device','1.3.6.1.4.1.34592.1.3.1.1.8.0','auto','','health',NULL),
(115,13,'temp','c-data','device','1.3.6.1.4.1.34592.1.3.1.3.4.0','auto','','health',NULL),
(116,13,'listmac','c-data','epon','1.3.6.1.4.1.34592.1.3.4.1.1.7','auto','','onu',NULL),
(117,13,'listport','c-data','port','1.3.6.1.2.1.2.2.1.2','auto','','global',NULL),
(118,13,'temp','c-data','epon','1.3.6.1.4.1.34592.1.3.3.4.5.1.1.1.keyport.keyonu','auto','','onu','=FUNCT1INT100='),
(119,7,'listport','zte','port','1.3.6.1.2.1.31.1.1.1.1','auto','','global',NULL),
(120,7,'sn','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.1.1.5.keyport.keyonu','auto','','onu',NULL),
(121,7,'listsn','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.1.1.5','auto','','onu',NULL),
(122,7,'dist','zte','gpon','1.3.6.1.4.1.3902.1012.3.11.4.1.2.keyport.keyonu','auto','','onu',NULL),
(123,7,'name','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.1.1.2.keyport.keyonu','auto','','onu',NULL),
(124,7,'note','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.1.1.3.keyport.keyonu','auto','','onu',NULL),
(125,7,'eth','zte','gpon','1.3.6.1.4.1.3902.1012.3.50.14.1.1.7.keyport.keyonu.1','auto','','onu',NULL),
(126,7,'status','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.2.1.4.keyport.keyonu','auto','','onu',NULL),
(127,7,'rx','zte','gpon','1.3.6.1.4.1.3902.1015.1010.11.2.1.2.keyport.keyonu','auto','','onu',NULL),
(128,7,'tx','zte','gpon','1.3.6.1.4.1.3902.1012.3.50.12.1.1.14.keyport.keyonu.1','auto','','onu',NULL),
(129,7,'reason','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.2.1.7.keyport.keyonu','auto','','onu',NULL),
(130,7,'model','zte','gpon','1.3.6.1.4.1.3902.1012.3.50.11.2.1.17.keyport.keyonu','auto','','onu',NULL),
(131,7,'vendor','zte','gpon','1.3.6.1.4.1.3902.1012.3.50.11.2.1.1.keyport.keyonu','auto','','onu',NULL),
(132,7,'uptime','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.2.1.5.keyport.keyonu','auto','oid_uptime','onu',NULL),
(133,7,'uptime','zte','device','1.3.6.1.2.1.1.3.0','auto','oid_uptime','health',NULL),
(134,7,'name','zte','device','1.3.6.1.2.1.1.5.0','auto','','health',NULL),
(135,7,'typereg','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.1.1.12.keyport.keyonu','auto','','onu',NULL),
(136,7,'status','zte','port','1.3.6.1.2.1.2.2.1.8.keyport','auto','','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(137,7,'ifouterrors','zte','port','1.3.6.1.2.1.2.2.1.20.keyport','auto','','monitor',NULL),
(138,7,'ifinerrors','zte','port','1.3.6.1.2.1.2.2.1.14.keyport','auto','','monitor',NULL),
(139,7,'config','zte','gpon','1.3.6.1.4.1.3902.1012.3.28.1.1.1.keyport.keyonu','auto','','onu',NULL),
(140,7,'mngtvlan','zte','gpon','1.3.6.1.4.1.3902.1015.1010.5.9.1.4.keyport.keyonu','auto','','onu',NULL),
(141,3,'vendor','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.1.1.6.keyonu','auto','','onu',NULL),
(142,12,'eth','cdata','gpon','3.6.1.4.1.34592.1.5.1.1.2.19.1.1.1.1.0.8keyonu.1',NULL,NULL,'onu',NULL),
(143,12,'status','cdata','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,NULL,'monitor',NULL),
(144,12,'ifinerrors','cdata','port','1.3.6.1.2.1.2.2.1.14.keyport',NULL,NULL,'global',NULL),
(145,12,'ifouterrors','cdata','port','1.3.6.1.2.1.2.2.1.20.keyport',NULL,NULL,'global',NULL),
(146,12,'listport','cdata','port','1.3.6.1.2.1.31.1.1.1.1',NULL,NULL,'global',NULL),
(147,12,'model','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.5.keyonu',NULL,NULL,'onu',NULL),
(148,12,'vendor','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.6.keyonu',NULL,NULL,'onu',NULL),
(149,12,'rx','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.4.1.4.keyonu.0.0',NULL,NULL,'onu',NULL),
(150,12,'tx','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.4.1.5.keyonu.0.0',NULL,NULL,'onu',NULL),
(151,12,'dist','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.9.keyonu',NULL,NULL,'onu',NULL),
(152,12,'reason','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.103.keyonu',NULL,NULL,'onu',NULL),
(153,12,'listsn','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.3',NULL,NULL,'onu',NULL),
(154,12,'status','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.7.keyonu',NULL,NULL,'onu',NULL),
(155,12,'name','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.2.keyonu',NULL,NULL,'onu',NULL),
(156,12,'uptime','cdata','device','1.3.6.1.4.1.17409.2.3.1.2.1.1.5.1',NULL,'oid_uptime','health',NULL),
(157,12,'cpu','cdata','device','1.3.6.1.4.1.34592.1.3.100.1.8.1.0',NULL,NULL,'health',NULL),
(158,12,'temp','cdata','device','1.3.6.1.4.1.34592.1.3.100.1.8.6.0',NULL,NULL,'health','=FUNCT1INT10='),
(159,14,'dist','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.46.1.20.keyport.keyonu',NULL,NULL,'onu',NULL),
(160,14,'status','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15.keyport.keyonu',NULL,NULL,'onu',NULL),
(161,14,'listsn','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',NULL,NULL,'onu',NULL),
(162,14,'rx','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4.keyport.keyonu',NULL,NULL,'onu',NULL),
(163,14,'tx','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.51.1.3.keyport.keyonu',NULL,NULL,'onu',NULL),
(164,14,'reason','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.46.1.24.keyport.keyonu',NULL,NULL,'onu',NULL),
(165,14,'bias','huawei','gpon','1.3.6.1.4.1.2011.6.158.1.1.1.2.1.12.keyport.keyonu',NULL,NULL,'onu',NULL),
(166,14,'name','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.43.1.9.keyport.keyonu',NULL,NULL,'onu',NULL),
(167,14,'eth','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.62.1.21.keyport.keyonu.1',NULL,NULL,'onu',NULL),
(168,14,'linepro','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.43.1.7.keyport.keyonu',NULL,NULL,'onu',NULL),
(169,14,'service','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.43.1.8.keyport.keyonu',NULL,NULL,'onu',NULL),
(170,14,'temp','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.51.1.1.keyport.keyonu',NULL,NULL,'onu',NULL),
(171,14,'macport','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.46.1.21.keyport.keyonu',NULL,NULL,'onu',NULL),
(172,14,'listport','huawei','port','1.3.6.1.2.1.31.1.1.1.1',NULL,NULL,'global',NULL),
(173,14,'status','huawei','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,NULL,'global',NULL),
(174,14,'countmacport','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.46.1.21.keyport.keyonu',NULL,NULL,'onu',NULL),
(175,14,'uservlan','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.62.1.7.keyport.keyonu.1',NULL,NULL,'onu',NULL),
(176,14,'model','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.2.45.1.4.keyport.keyonu',NULL,NULL,'onu',NULL),
(177,14,'onuerror','huawei','gpon','1.3.6.1.4.1.2011.6.128.1.1.4.27.1.2.keyport.keyonu',NULL,NULL,'onu',NULL),
(178,14,'ifinerrors','huawei','port','1.3.6.1.2.1.2.2.1.14.keyport',NULL,NULL,'global',NULL),
(179,14,'ifouterrors','huawei','port','1.3.6.1.2.1.2.2.1.20.keyport',NULL,NULL,'global',NULL),
(180,14,'oltrx','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.104.1.1.keyport.keyonu',NULL,NULL,'onu',NULL),
(181,14,'temp','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.104.1.2.keyport.keyonu',NULL,NULL,'onu',NULL),
(182,14,'reason','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.103.1.8.keyport.keyonu.9',NULL,NULL,'onu',NULL),
(183,14,'name','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.53.1.9.keyport.keyonu',NULL,NULL,'onu',NULL),
(184,14,'tx','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.104.1.4.keyport.keyonu',NULL,NULL,'onu',NULL),
(185,14,'rx','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.104.1.5.keyport.keyonu',NULL,NULL,'onu',NULL),
(186,14,'eth','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.81.1.31.keyport.keyonu.1',NULL,NULL,'onu',NULL),
(187,14,'dist','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.57.1.19.keyport.keyonu',NULL,NULL,'onu',NULL),
(188,14,'status','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.57.1.18.keyport.keyonu',NULL,NULL,'onu',NULL),
(189,14,'listmac','huawei','epon','1.3.6.1.4.1.2011.6.128.1.1.2.53.1.3',NULL,NULL,'onu',NULL),
(190,14,'uptime','huawei','device','1.3.6.1.2.1.1.3.0',NULL,'oid_uptime','health',NULL),
(191,14,'status','huawei','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,NULL,'monitor',NULL),
(192,3,'ifinerrors','zte','port','1.3.6.1.2.1.2.2.1.14.keyport','auto','oid_ifinerrors','monitor',NULL),
(193,3,'ifouterrors','zte','port','1.3.6.1.2.1.2.2.1.20.keyport','auto','oid_ifouterrors','monitor',NULL),
(194,3,'status','zte','port','1.3.6.1.2.1.2.2.1.8.keyport','auto','oid_epon_port_satus','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(196,12,'operstatus','cdata','gpon','1.3.6.1.4.1.17409.2.8.5.1.1.4.keyonu.0.1',NULL,'','onu','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(197,12,'temp','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.4.1.8.keyonu.0.0',NULL,NULL,'onu','=FUNCT1INT100='),
(198,12,'regtime','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.103.keyonu',NULL,NULL,'onu','a:2:{s:4:\"losi\";s:4:\"err6\";s:10:\"dying-gasp\";s:4:\"err1\";}'),
(199,12,'time','cdata','gpon','1.3.6.1.4.1.17409.2.8.4.1.1.102.keyonu',NULL,NULL,'onu',NULL),
(200,8,'volt','mikrotik','device','1.3.6.1.4.1.14988.1.1.3.8.0',NULL,NULL,'health','=FUNCT1INT10='),
(201,8,'temp','mikrotik','device','1.3.6.1.4.1.14988.1.1.3.10.0',NULL,NULL,'health','=FUNCT1INT10='),
(202,8,'uptime','mikrotik','device','1.3.6.1.2.1.1.3.0',NULL,NULL,'health',NULL),
(203,8,'uptimes','mikrotik','device','1.3.6.1.2.1.1.3.0',NULL,'','health',NULL),
(204,8,'listport','mikrotik','port','1.3.6.1.2.1.31.1.1.1.1',NULL,NULL,'global',NULL),
(205,8,'status','mikrotik','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,'','monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(206,8,'ifinerrors','mikrotik','port','1.3.6.1.2.1.2.2.1.14.keyport',NULL,NULL,'global',NULL),
(207,8,'ifouterrors','mikrotik','port','1.3.6.1.2.1.2.2.1.20.keyport',NULL,NULL,'global',NULL),
(208,9,'cpu','gcom','device','1.3.6.1.4.1.13464.1.2.1.1.2.11.0',NULL,NULL,'health',NULL),
(209,9,'status','gcom','epon','1.3.6.1.4.1.13464.1.13.3.1.1.4.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(210,9,'dist','gcom','epon','1.3.6.1.4.1.13464.1.13.3.1.1.18.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(211,9,'name','gcom','epon','1.3.6.1.4.1.13464.1.13.3.1.1.5.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(212,9,'rx','gcom','epon','1.3.6.1.4.1.13464.1.13.3.3.1.8.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(213,9,'vlan','gcom','epon','1.3.6.1.4.1.13464.1.13.3.16.1.7.0.keyport.keyonu.1',NULL,'','onu',NULL),
(214,9,'eth','gcom','epon','1.3.6.1.4.1.13464.1.13.4.1.1.6.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(215,9,'tx','gcom','epon','1.3.6.1.4.1.13464.1.13.3.3.1.7.0.keyport.keyonu',NULL,'','onu',NULL),
(216,9,'regtime','gcom','epon','1.3.6.1.4.1.13464.1.13.3.1.1.19.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(217,9,'temp','gcom','epon','1.3.6.1.4.1.13464.1.13.3.3.1.4.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(218,9,'vendor','gcom','epon','1.3.6.1.4.1.13464.1.13.3.1.1.10.0.keyport.keyonu',NULL,'','onu',NULL),
(219,9,'volt','gcom','epon','1.3.6.1.4.1.13464.1.13.3.3.1.5.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(220,9,'model','gcom','epon','1.3.6.1.4.1.13464.1.13.3.1.1.7.0.keyport.keyonu',NULL,'','onu',NULL),
(221,9,'client','gcom','epon','1.3.6.1.4.1.13464.1.13.3.16.1.5.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(222,9,'operstatus','gcom','epon','1.3.6.1.4.1.13464.1.13.3.1.1.4.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(223,9,'uptime','gcom','device','1.3.6.1.2.1.1.3.0',NULL,NULL,'health',NULL),
(224,9,'status','gcom','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,NULL,'monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(225,9,'ifinerrors','gcom','port','1.3.6.1.2.1.2.2.1.14.keyport',NULL,NULL,'monitor',NULL),
(226,9,'ifouterrors','gcom','port','1.3.6.1.2.1.2.2.1.20.keyport',NULL,NULL,'monitor',NULL),
(227,2,'temp','bdcom','device','1.3.6.1.4.1.3320.9.181.1.1.7.0','auto','','health',NULL),
(228,10,'uptime','smartfiber','device','1.3.6.1.2.1.1.3.0',NULL,NULL,'health',NULL),
(229,10,'listmac','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.9.0',NULL,'','onu',NULL),
(230,10,'status','smartfiber','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,NULL,'monitor',NULL),
(231,10,'ifinerrors','smartfiber','port','1.3.6.1.2.1.2.2.1.14.keyport',NULL,NULL,'global',NULL),
(232,10,'ifouterrors','smartfiber','port','1.3.6.1.2.1.2.2.1.20.keyport',NULL,NULL,'global',NULL),
(233,10,'listport','smartfiber','port','1.3.6.1.2.1.31.1.1.1.1',NULL,NULL,'global',NULL),
(234,10,'status','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.4.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(235,10,'dist','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.18.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(236,10,'name','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.5.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(237,10,'rx','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.3.1.8.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(238,10,'vlan','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.16.1.7.0.keyport.keyonu.1',NULL,'','onu',NULL),
(239,10,'eth','smartfiber','epon','1.3.6.1.4.1.8888.1.13.4.1.1.6.0.keyport.keyonu.1',NULL,'','onu',NULL),
(240,10,'tx','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.3.1.7.0.keyport.keyonu',NULL,'','onu',NULL),
(241,10,'regtime','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.19.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(242,10,'temp','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.3.1.4.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(243,10,'vendor','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.10.0.keyport.keyonu',NULL,'','onu',NULL),
(244,10,'volt','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.3.1.5.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(245,10,'model','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.7.0.keyport.keyonu',NULL,'','onu',NULL),
(246,10,'client','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.16.1.5.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(247,10,'operstatus','smartfiber','epon','1.3.6.1.4.1.8888.1.13.3.1.1.4.0.keyport.keyonu',NULL,NULL,'onu',NULL),
(248,1,'admineth','bdcom','epon','1.3.6.1.4.1.3320.101.12.1.1.7.keyonu.1','auto','','onu',NULL),
(249,11,'uptime','smartfiber','device','1.3.6.1.2.1.1.3.0',NULL,'','health',NULL),
(250,11,'listmac','smartfiber','gpon','1.3.6.1.4.1.8888.1.13.3.1.1.9.0',NULL,NULL,'onu',NULL),
(251,11,'status','smartfiber','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,NULL,'monitor',NULL),
(252,11,'ifinerrors','smartfiber','port','1.3.6.1.2.1.2.2.1.14.keyport',NULL,NULL,'global',NULL),
(253,11,'ifouterrors','smartfiber','port','1.3.6.1.2.1.2.2.1.20.keyport',NULL,NULL,'global',NULL),
(254,11,'listport','smartfiber','port','1.3.6.1.2.1.31.1.1.1.1',NULL,NULL,'global',NULL),
(255,11,'status','smartfiber','gpon','1.3.6.1.4.1.8888.1.14.2.4.1.1.1.6.2.keyport.keyonu',NULL,'','onu',NULL),
(256,11,'dist','smartfiber','gpon','1.3.6.1.4.1.8888.1.14.2.4.1.1.1.7.2.keyport.keyonu',NULL,'','onu',NULL),
(257,11,'name','smartfiber','gpon','1.3.6.1.4.1.8888.1.14.2.4.1.1.1.4.2.keyport.keyonu',NULL,'','onu',NULL),
(258,11,'rx','smartfiber','gpon','1.3.6.1.4.1.8888.1.14.2.4.1.4.1.5.2.keyport.keyonu',NULL,'','onu',NULL),
(260,11,'eth','smartfiber','gpon','1.3.6.1.4.1.8888.1.13.4.1.1.6.0.keyport.keyonu.1',NULL,NULL,'onu',NULL),
(261,11,'tx','smartfiber','gpon','1.3.6.1.4.1.8888.1.14.2.4.1.4.1.4.2.keyport.keyonu',NULL,'','onu',NULL),
(276,11,'temp','smartfiber','device','1.3.6.1.4.1.8888.1.14.4.4.0','auto','','health',NULL),
(277,16,'uptime','v-sol','device','1.3.6.1.2.1.1.3.0','auto','oid_uptime','health',NULL),
(264,11,'vendor','smartfiber','gpon','1.3.6.1.4.1.8888.1.14.2.4.1.1.1.9.2.keyport.keyonu',NULL,'','onu',NULL),
(279,16,'cpu','v-sol','device','1.3.6.1.4.1.37950.1.1.5.10.12.3.0','auto','','health',NULL),
(266,11,'model','smartfiber','gpon','1.3.6.1.4.1.8888.1.14.2.4.1.1.1.8.2.keyport.keyonu',NULL,'','onu',NULL),
(278,16,'name','v-sol','device','1.3.6.1.2.1.1.1.0','auto','','health',NULL),
(269,10,'cpu','smartfiber','device','1.3.6.1.4.1.8888.1.2.1.1.2.25.0','auto','','health',NULL),
(270,10,'temp','smartfiber','device','1.3.6.1.4.1.8888.1.13.1.2.1.8.0','auto','','health',NULL),
(271,10,'firmware','smartfiber','device','1.3.6.1.4.1.8888.1.2.1.1.2.2.0','auto','','health',NULL),
(272,9,'cpu','gcom','device','1.3.6.1.4.1.13464.1.2.1.1.2.25.0','auto','','health',NULL),
(273,9,'firmware','gcom','device','1.3.6.1.4.1.13464.1.2.1.1.2.2.0','auto','','health',NULL),
(274,9,'temp','gcom','device','1.3.6.1.4.1.13464.1.2.1.1.2.25.0','auto','','health',NULL),
(275,2,'model','bdcom','gpon','1.3.6.1.4.1.3320.10.3.1.1.2.keyonu','auto','','onu',NULL),
(280,16,'temp','v-sol','device','1.3.6.1.4.1.37950.1.1.5.10.12.5.9.0','auto','','health',NULL),
(281,16,'memory','v-sol','device','1.3.6.1.4.1.37950.1.1.5.10.12.4.0','auto','','health',NULL),
(282,16,'volt','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.3.1.4.keyport.keyonu','auto','','onu',NULL),
(283,16,'bias','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.1.keyport.keyonu','auto','','onu',NULL),
(284,16,'dist','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.12.1.3.keyport.keyonu','auto','','onu',NULL),
(285,16,'vendor','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.2.keyport.keyonu','auto','','onu',NULL),
(286,16,'model','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.4.1.17.keyport.keyonu','auto','','onu',NULL),
(287,16,'inface','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.4.1.24.keyport.keyonu','auto','','onu',NULL),
(288,16,'temp','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.3.1.3.keyport.keyonu','auto','','onu',NULL),
(289,16,'tx','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.3.1.6.keyport.keyonu','auto','','onu',NULL),
(290,16,'rx','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.3.1.7.keyport.keyonu','auto','','onu',NULL),
(291,16,'sn','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.2.1.5.keyport.keyonu','auto','','onu',NULL),
(292,16,'status','v-sol','gpon','1.3.6.1.4.1.37950.1.1.6.1.1.1.1.5.keyport.keyonu','auto','','onu',NULL),
(293,16,'oid_ifouterrors','v-sol','port','1.3.6.1.2.1.2.2.1.20.keyport','auto','','monitor',NULL),
(294,16,'oid_ifinerrors','v-sol','port','1.3.6.1.2.1.2.2.1.14.keyport','auto','','monitor',NULL),
(295,16,'status','v-sol','port','1.3.6.1.2.1.2.2.1.8.keyport','auto','','monitor',NULL),
(296,17,'listport','huawei ','device','1.3.6.1.2.1.2.2.1.2','auto','','global',NULL),
(297,17,'allmac','huawei ','device','1.3.6.1.2.1.17.4.3.1.2','auto','','global',NULL),
(298,17,'allvlan','huawei ','device','1.3.6.1.2.1.17.7.1.4.3.1.1','auto','','global',NULL),
(299,17,'cpu','huawei ','device','1.3.6.1.4.1.2011.6.3.4.1.3.0.0.0','auto','','health',NULL),
(300,17,'uptime','huawei ','device','1.3.6.1.2.1.1.3.0','auto','','health',NULL),
(301,18,'uptime','gcom ','device','1.3.6.1.2.1.1.3.0','auto','','health',NULL),
(302,18,'name','gcom ','device','1.3.6.1.4.1.13464.1.2.1.1.2.2.0','auto','','health',NULL),
(303,18,'status','gcom','port','1.3.6.1.2.1.2.2.1.8.keyport',NULL,NULL,'monitor','a:2:{i:1;s:2:\"up\";i:2;s:4:\"down\";}'),
(304,18,'ifinerrors','gcom','port','1.3.6.1.2.1.2.2.1.14.keyport',NULL,NULL,'monitor',NULL),
(305,18,'ifouterrors','gcom','port','1.3.6.1.2.1.2.2.1.20.keyport',NULL,NULL,'monitor',NULL),
(306,18,'cpu','gcom ','device','1.3.6.1.4.1.13464.1.2.1.1.2.11.0','auto','','health',NULL),
(307,7,'eth','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.3.2.1.6.keyonu','auto','','onu',NULL),
(308,7,'regtime','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.14.keyonu','auto','','onu',NULL),
(309,7,'auttime','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.12.keyonu','auto','','onu',NULL),
(310,7,'tx','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.4.keyonu','auto','','onu',NULL),
(311,7,'vendor','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.1.1.2.keyonu','auto','','onu',NULL),
(312,7,'model','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.5.keyonu','auto','','onu',NULL),
(313,7,'adminstatus','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.6.keyonu','auto','','onu',NULL),
(314,7,'dist','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.2.1.1.10.keyonu','auto','','onu',NULL),
(315,7,'rx','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.1.1.29.1.5.keyonu','auto','','onu',NULL),
(316,7,'status','zte','epon','1.3.6.1.4.1.3902.1015.1010.1.7.4.1.17.keyonu','auto','','onu',NULL),
(317,1,'typeport','bdcom','epon','1.3.6.1.4.1.3320.101.12.1.1.10.keyonu.1','auto','','onu',NULL);
CREATE TABLE `onus_log` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idonu` int(11) DEFAULT NULL,
  `idolt` int(11) DEFAULT NULL,
  `iduser` int(11) DEFAULT NULL,
  `types` enum('cron','system','user','switch') DEFAULT 'system',
  `info` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `sender` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `jobid` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE onus ADD module1 INT NULL DEFAULT NULL AFTER idonu;
ALTER TABLE switch ADD pinger INT NULL DEFAULT NULL AFTER status;
ALTER TABLE onus ADD stikers INT NULL DEFAULT NULL AFTER idonu;
ALTER TABLE onus ADD addstikers INT NULL DEFAULT NULL AFTER idonu;
ALTER TABLE onus ADD clockstikers DATETIME AFTER idonu;
