<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
$db->query("DELETE FROM historysignal WHERE datetime < curdate() - interval 40 day");
$db->query("DELETE FROM rxolt_signal WHERE datetime < curdate() - interval 40 day");
$db->query("DELETE FROM switch_port_err WHERE added < curdate() - interval 60 day");
$db->query("DELETE FROM mon_voltage WHERE added < curdate() - interval 30 day");
$db->query("DELETE FROM monitor_ip_log WHERE added < curdate() - interval 30 day");
$db->query('DELETE FROM pmonstats WHERE datetime < curdate() - interval 10 day');
$db->query('DELETE FROM pingstats WHERE datetime < curdate() - interval 7 day');
$db->query("DELETE r1 FROM rules r1 JOIN (  SELECT types, MIN(id) AS min_id  FROM rules  GROUP BY types  HAVING COUNT(*) > 1) r2 ON r1.types = r2.types AND r1.id <> r2.min_id;");
?>
