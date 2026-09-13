<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('rsyslog')) 
	$go->redirect('main');
$addparam = $addparam ?? null;
$count_log = 0;
if(!$dbrsyslog['password']) 
	$go->redirect('main');
$conn = new mysqli($dbrsyslog['hostname'],$dbrsyslog['username'],$dbrsyslog['password'],$dbrsyslog['database']);
$conn->set_charset('utf8');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$countlog = $conn->query("SELECT count(ID) as countlog FROM SystemEvents");
$logs = $countlog->fetch_assoc();
if($logs['countlog'] > 0){
	list($pagertop, $pagerbottom, $limit, $offset) = pager(30,$logs['countlog'],'/?do=log'.$addparam);
	$result = $conn->query("SELECT * FROM SystemEvents ORDER BY ID DESC LIMIT $limit,$offset");
	$count_log = $result->num_rows;
}
if($count_log > 0){
	while ($log = $result->fetch_assoc()) {
		$tpl->load_template('log/rsysloglist.tpl');
		$tpl->set('{id}',$log['ID']);
		$tpl->set('{devicetime}',$log['DeviceReportedTime']);
		$tpl->set('{host}','');
		$tpl->set('{message}',$log['Message']);
		$tpl->set('{syslog}',$log['SysLogTag']);
		$tpl->compile('rsyslog');
		$tpl->clear();	
	}
}else{
	$tpl->load_template('log/empty.tpl');
	$tpl->compile('rsyslog');
	$tpl->clear();
}
$metatags = array('title'=>'RSyslog','description'=>'RSyslog','page'=>'rsyslog');
$tpl->load_template('log/page.tpl');
$tpl->set('{result}',($tpl->result['rsyslog']?$tpl->result['rsyslog']:$lang['emptyrsyslog']));
$tpl->set('{url}','/');
$tpl->set('{pager}',($logs['countlog']?$pagertop:''));
$tpl->set('{logname}','Rsyslog');
$tpl->compile('content');
$tpl->clear();	
?>