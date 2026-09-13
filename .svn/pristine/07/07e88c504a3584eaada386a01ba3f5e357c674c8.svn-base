<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('rsyslog')) 
	$go->redirect('main');
if(isset($confPMon['RSYSLOG']) && !empty($confPMon['RSYSLOG']) && $confPMon['RSYSLOG']==1){
	$addparam = $addparam ?? null;
	$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
	if(!$id){
		$go->redirect('main');	
	}
	$dataswitch = $db->Fast($PMonTables['switch'],'*',['id'=>$id]);
	if(!$dataswitch['id']){
		$go->redirect('main');	
	}
	$count_log = 0;
	$conn = new mysqli($dbrsyslog['hostname'],$dbrsyslog['username'],$dbrsyslog['password'],$dbrsyslog['database']);
	$conn->set_charset('utf8');
	if ($conn->connect_error) {
		die('Connection failed: ' . $conn->connect_error);
	}
	$rsyslogip = $dataswitch['netip'];
	$countlog = $conn->query("SELECT count(ID) as countlog FROM SystemEvents WHERE Fromhost = '$rsyslogip'");
	$logs = $countlog->fetch_assoc();
	if($logs['countlog'] > 0){
		list($pagertop, $pagerbottom, $limit, $offset) = pager(30,$logs['countlog'],'/?do=rsyslog&id='.$id.''.$addparam);
		$result = $conn->query("SELECT * FROM SystemEvents WHERE Fromhost = '$rsyslogip' ORDER BY ID DESC LIMIT $limit,$offset");
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
		$tpl->set('{result}',$lang['emptyrsyslog']);
		$tpl->compile('rsyslog');
		$tpl->clear();
	}
	$metatags = array('title'=>'RSyslog '.$dataswitch['place'],'description'=>'RSyslog '.$dataswitch['place'],'page'=>'rsyslog');
	$tpl->load_template('log/page.tpl');
	$tpl->set('{result}',($tpl->result['rsyslog']?$tpl->result['rsyslog']:$lang['emptyrsyslog']));
	$tpl->set('{url}','.?do=detail&act=olt&id='.$dataswitch['id']);
	$tpl->set('{pager}',($logs['countlog']?$pagertop:''));
	$tpl->set('{logdevice}',$dataswitch['place']);
	$tpl->set('{logname}','Rsyslog');
	$tpl->compile('content');
	$tpl->clear();	
}else{
	$go->redirect('main');		
}
?>