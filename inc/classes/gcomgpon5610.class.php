<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class GCOM_Gpon{ 
    private $logger;
    private $db;
	protected $id;
	protected $ip;
	protected $oidid;
	protected $community;
	protected $deviceoid;
    private $indexdevice;
    private $listpoweroff = array();
    private $description = array();
    private $temp_onu = array();
    public $status_onu = array();
    private $now;
    private $primary = 'status,dist';
    private $poller = 'status,inface,rx';
    private $configapionuepon = 'dist,model,vendor,status';
    private $configapionueponget = 'rx,tx,rxolt,dist,model,vendor,status';
	public function __construct(int $swid, $equipment, $db, $logger) {
		$this->logger = $logger;
		$this->db = $db;
		if (empty($swid) || !isset($equipment->switchoid) || 
			empty($equipment->switches[$swid]['switchid']) || 
			empty($equipment->switches[$swid]['switchoidid']) || 
			empty($equipment->switches[$swid]['switchcommunity'])) {			
			die('check_switch');
		}
		$this->now = date('Y-m-d H:i:s');
		$this->id = $equipment->switches[$swid]['switchid'];
		$this->ip = $equipment->switches[$swid]['switchip'];
		$this->community = $equipment->switches[$swid]['switchcommunity'];
		$this->oidid = $equipment->switches[$swid]['switchoidid'];
		$this->deviceoid = $equipment->switchoid;
		$this->snmp = new SnmpMonitor();
		$this->list_onu();
	}   
	public function Support($check){
		return match ($check){
			'port','onu','saveonu','rxolt','api','poller','fileonu' => true,default => false,
		};
	}
	public function sngcom(?string $data): ?string {
		if ($data !== null) {
			return str_replace(['STRING:', ' ', '-', '"'], '',$data);
		} else {
			return null;
		}
	}
	public function Load(){
		$gpon = [
			'oid' => '1.3.6.1.4.1.13464.1.14.2.4.1.1.1.8','type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$i = 1;
		$gponlist = pmon_walk($gpon);
		$result = [];
		if(isset($gponlist) && count($gponlist)>0){
			foreach ($gponlist as $io => $eachsig) {
				if (!empty($eachsig['result'])) {
					preg_match('/(\d+).(\d+).(\d+)/', $io, $match);
					$result[$i] = [
						'do' => 'onu','id' => $this->id,'sn' => $this->sngcom($eachsig['result']),'pon' => 'gpon','types' => $this->primary,'inface' => '0/'.$match[2].':'.$match[3],'keyonu' => $match[3],'keyport' => $match[2]
					];
					$i++;
				}
			}
		}
		if (empty($result)) {
			$this->logger->init([
				'log' => 'device','type' => 'snmp','descr' => 'Empty Snmp Walk ' . $epon['oid'],'deviceid' => $this->id,'who' => 'cron'
			]);
		}
		return $result ?? false;
	}
	public function ConfigApiOnu($data){
		return array('do' => 'onu','types' => $this->configapionuepon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'keyport' => $data['zte_idport'],'id' => $this->id);
	}	
	public function ConfigApiOnuGet($data){
		return array('do' => 'onu','types' => $this->configapionueponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'keyport' => $data['zte_idport'],'id' => $this->id);
	}
	public function statusGcom($status) {
		$statuses = ["1" => 1,"2" => 2];		
		return isset($statuses[$status]) ? $statuses[$status] : 2;
	}	
	public function Onu($dataPort,$dataOnu){
		$res = array(); 
		if(is_array_empty($dataPort)){
			foreach($dataPort as $type => $value) {
				$res[$type] = $this->preparedataBDCOM($value,$type);
			}
		} elseif(!$dataPort) {
			$array_separated = explode(',',$this->configapionuepon);
			foreach($array_separated as $type) {
				if(!empty($dataOnu[$type]))
					$res[$type] = $dataOnu[$type];
			}
		}
		$result = is_array_empty($res) ? $this->updateonu($dataOnu,$res) : false;
		return $result;
	}
	public function updateonu($ont,$getData){
		$sqlset = array();	
		$result = array();	
		if(is_array_empty($getData)){
			if(!empty($getData['model'])) 
				$sqlset['model'] = $getData['model'];
			if(!empty($getData['vendor'])) 
				$SQLset['vendor'] = $getData['vendor'];		
			if(!empty($getData['status']))
				$sqlset['status'] = $this->statusGcom($getData['status']);			
			if(!empty($getData['dist'])) 
				$sqlset['dist'] = $getData['dist'];
			if($ont['status']==2 && $getData['status']==1){
				$sqlset['online'] = $this->now;
				$sqlset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$sqlset['offline'] = $this->now;
				$sqlset['status'] = 2;
			}
			if(is_array_empty($sqlset)){
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $ont['idonu']]);
			}			
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			if(!empty($getData['model']))	
				$result['model'] = $getData['model'];			
			if(!empty($getData['vendor']))	
				$result['vendor'] = $getData['vendor'];
			if(!empty($getData['dist'])) 
				$result['dist'] = $getData['dist'];			
			if(!empty($ont['lastrx']))
				$result['lastrx'] = $ont['lastrx'];
			if(!empty($getData['rx'])) 
				$result['rx'] = $getData['rx'];
			if(!empty($getData['tx'])) 
				$result['tx'] = $getData['tx'];			
			if(!empty($ont['reason'])) 
				$result['reason'] = $ont['reason'];				
			return $result;
		}
	}	
	public function preparedataBDCOM($dataApi, $type){
		$data = $this->clearData($dataApi);
		switch($type){
			case 'temp':
				if(isset($data) && is_numeric($data)){
					$n = $data / 256;
					return round($n, 0);
				} else {
					return null;
				}
			break;
			case 'status':
				return $this->statusGcom($data);
			case 'dist':
				return isset($data) ? (int) $data : null;			
			case 'admineth':
				return $data==1  ? 'up' : 'down';
			case 'rx':
			case 'rxolt':
			case 'tx':
				return $data ? $this->Signal($data) : null;
			case 'mac':
				return $dataApi ? ClearDataMac($dataApi) : null;			
			case 'model':
			case 'vendor':
			case 'device':
			case 'pvid':
			case 'name':
			case 'eth':
				return isset($data) ? $data : '';
			case 'eth':
				return $data == 'up' ? 'up' : 'down';
			default:
				return null;
		}
	}
	public function clearResult($value){
		return trim(str_replace('/', '', str_replace('"', '', $value)));
	}
	public function savePort($dataPort){
		if(!empty($dataPort['port'])){
			foreach($dataPort['port'] as $value){
				$this->savePortSwitch($value);
			}
		}
		if(!empty($dataPort['pon'])){
			foreach($dataPort['pon'] as $value){
				$this->savePonSwitch($value);
			}
			$this->db->query("UPDATE switch SET updates_port = '{$this->now}' WHERE id  = '{$this->id}'");
		}
	}
	public function statusport(int $status): string {
		return ($status == 1) ? 'up' : 'down';
	}
	public function getnameportgcom($str){
		$str = str_replace('g0', 'GPON 0',$str);	
		$str = str_replace('e1', 'GigaEthernet 1',$str);	
		$str = str_replace('e2', 'TGigaEthernet 2',$str);		
		return $str;
	}
	public function gettypeportgcom($str){
		if(preg_match('/g0/i',$str)){
			return 'pon';	
		}elseif(preg_match('/e1/i',$str)){
			return 'sfp';	
		}elseif(preg_match('/e2/i',$str)){
			return'sfp';	
		}else{
			return'port';		
		}
	}
	public function Port(){
		$data = array();
		$listPon = array();
		$olt_port = [
			'oid' => '1.3.6.1.2.1.2.2.1.2','cache' => true,'timecache' => 1000,'namecache' => 'list_port_'.$this->id,'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$index_port = pmon_walk($olt_port);	
		if(is_array_empty($index_port)){
			$listport = array();
			foreach($index_port as $idport => $value) {	
				if(!empty($idport) && !empty($value['result']) 
					&& (preg_match('/g0/i',$value['result']) || preg_match('/e1/i',$value['result']) || preg_match('/e2/i',$value['result']))){
					$dataport = $this->clearData($value['result']);
					$oid = "1.3.6.1.2.1.2.2.1.8.".$idport;
					$res_tmp = $this->snmp->get($this->ip, $this->community, $oid, true);
					$resultclear = $this->clearData(str_replace([$oid,'=','  '],'',$res_tmp));
					$listport[$idport] = array(
						'name' => $this->getnameportgcom($dataport),
						'operstatus' =>$this->statusport($resultclear),
						'typeport' => $this->gettypeportgcom($dataport),
						'id' => $idport
					);
				}				
			}
			$data['port'] = $listport;
		}
		if(isset($data['port']) && count($data['port'])>0){
			foreach($data['port'] as $idPonport => $valuePon){
				if(preg_match('/pon/i',$valuePon['name'])) {
					preg_match('/GPON 0\/(\d+)/',$valuePon['name'],$mat);
					if(isset($mat[1]) && !empty($mat[1])){
						$listPon[$idPonport] = [
							'name' => 'GPON 0/' . $mat[1],'sort' => $mat[1],'sfpid' => $valuePon['id'],
							'descrport' => (isset($valuePon['descrport'])?$valuePon['descrport']:''),'cardcount' => 128
						];
					}
				}
			}
			if(is_array_empty($listPon)){
				usort($listPon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data['pon'] = $listPon;
			}
		}
		return (is_array_empty($data) ? $data : null);
	}
    protected function savePonSwitch($dataPort) {
		if(!empty($dataPort['sfpid'])){
			$row = $this->db->Simple("SELECT * FROM switch_pon WHERE oltid = '{$this->id}' AND sfpid = '{$dataPort['sfpid']}' LIMIT 1");
			if(isset($row['id']) && !empty($row['id'])){
				$this->db->query("UPDATE switch_pon SET pon = '{$dataPort['name']}' WHERE sfpid  = '{$dataPort['sfpid']}' AND oltid = '{$this->id}'");
			}else{
				$sql ="INSERT INTO switch_pon (`support`, `sort`, `oltid`, `pon`, `sfpid`, `added`) VALUES ('{$dataPort['cardcount']}','{$dataPort['sort']}','{$this->id}','{$dataPort['name']}','{$dataPort['sfpid']}','{$this->now}')";
				$this->db->query($sql);
			}
			$allonu = $this->db->Simple('Select count(idonu) as count FROM onus WHERE olt = '.$this->id.' AND zte_idport = '.$dataPort['sfpid']);
			if(!empty($dataPort['sfpid']) && isset($allonu['count'])){
				$this->db->query("UPDATE switch_pon SET count = '{$allonu['count']}' WHERE sfpid  = '{$dataPort['sfpid']}' AND oltid = '{$this->id}'");				
				$this->db->query("UPDATE onus SET portolt = '{$dataPort['sfpid']}' WHERE olt  = '{$this->id}' AND zte_idport = '{$dataPort['sfpid']}'");
			}
		}
	}
    protected function savePortSwitch($data) {
		$sqlupdate = [];
		if(!empty($data['id'])){	
			$row = $this->db->Simple("SELECT * FROM switch_port WHERE deviceid = '{$this->id}' AND llid = '{$data['id']}' LIMIT 1");
			if(!empty($row['id'])){
				if(isset($this->logger->pmon_config['PMON_BEDP']) && $this->logger->pmon_config['PMON_BEDP']!=1 &&
					isset($data['descrport']) && !empty($data['descrport'])){
						$name_clear = $this->clearName($data['descrport']);
						$this->db->query("UPDATE switch_port SET descrport = '{$name_clear}' WHERE id = '{$row['id']}'");
				}				
			}else{
				$sql ="INSERT INTO switch_port (deviceid, llid, nameport, typeport, descrport, operstatus, added) VALUES ('{$this->id}','{$data['id']}','{$data['name']}','{$data['typeport']}', " . (!empty($data['descrport']) ? "'" . $data['descrport'] . "'" : 'NULL') . ",'none','{$this->now}')";
				$this->db->query($sql);
			}
		}
	}
	public function tempUpdateSignalCheck(){	
		global $db;

	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $config;
		$savehistor = $savehistor ?? null;
		if(isset($this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']]) && !empty($this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']])){
			$onu = $this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']];
		} else {
			$onu = $this->db->Simple("SELECT status,rx,idonu,inface,mac,sn,changerx,olt FROM onus WHERE olt = '{$this->id}' AND keyonu = '{$dataOnu['keyonu']}'  AND keyport = '{$dataOnu['keyport']}' LIMIT 1");
		}
		if (!empty($onu['idonu'])) {
			$rx = $dataOnu['rx'] ?? null;
			if (!empty($rx)) {
				$rxValue = $this->Signal($rx);
			}
			if ($config['logsignal'] == 'on') {
				if (!empty($rxValue)) {
					$savehistor = SignalMonitor($onu['status'], $rxValue, $onu['rx'], $onu['idonu'], $onu);
				}
			} else {
				if (!empty($rxValue)) {
					$this->db->query("UPDATE onus SET rx = '{$rxValue}' WHERE idonu  = {$onu['idonu']}");
				}
				$savehistor = true;
			}
			if ($onu['status'] == 1 && isSignalChanged($rxValue, $onu['rx'])) {
				$logont = [
					'log' => 'ont','type' => 'signal',
					'idonu' => $onu['idonu'],'olt' => $this->id, 'time' => $this->now,
					'curent' => $rxValue ?? 0,'last' => $onu['rx'] ?? 0
				];
				$this->logger->init($logont);
			}
			if (!empty($config['onugraph']) && $config['onugraph'] == 'on' && $savehistor && !empty($rxValue)) {
				$this->db->query("INSERT INTO historysignal (`device`, `onu`, `signal`, `datetime`) VALUES ('{$this->id}','{$onu['idonu']}', '{$rxValue}', '{$this->now}')");
			}
		}
	}	
	public function clearName($value) {
		return str_replace(['/', '\\', '"', ' '], '', $value);
	}
	public function clearData($value) {
		$unwanted_strings = ['INTEGER:', 'Hex-STRING:', 'STRING:', 'Gauge32:', '"', ' '];
		$value = str_replace($unwanted_strings, '', $value);
		return trim($value);
	}
	public function list_onu() {
		$getonu = $this->db->SimpleWhile("SELECT * FROM onus WHERE olt = '{$this->id}'");
		if (!empty($getonu)) {
			$this->temp_onu = [];
			foreach ($getonu as $onu) {
				$this->temp_onu[$onu['portolt']][$onu['keyonu']] = $onu;
			}
		}
	}
	public function Signal($value){
		if(preg_match('/2147483/i',$value) || preg_match('/655/i',$value)) {
			return 0; 
		}else{
			$value = str_replace('"', '',$value);
			$value = trim($value);
			$value = str_replace('N/A',0,$value);
			$value = sprintf('%.2f',$value);
			return str_replace('0.00',0,$value);
		}
	}
	public function tempSaveOnuGpon($dataOnu){	
		$sqlset = [];
		$now = $this->now;
		$dataOnu['status'] = (!empty($dataOnu['status']) ? $this->statusGcom($dataOnu['status']): 2);
		if(!empty($dataOnu['keyonu']) && !empty($dataOnu['sn'])){
			if(isset($this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']]['idonu']) && !empty($this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']]['idonu'])){
				if ($dataOnu['status'] == 1 && $this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']]['status'] == 2) {
					$sqlset['online'] = $now;
					$log_status = 2;
				} elseif ($dataOnu['status'] == 2 && $this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']]['status'] == 1) {
					$sqlset['offline'] = $now;
					$log_status = 1;
				}				
				$sqlset = array_merge($sqlset, [
					'status' => $dataOnu['status'],'inface' => $dataOnu['inface'],'updates' => $now,'dist' => $dataOnu['dist'] ?? 0,'sn' => $dataOnu['sn'],'portolt' => $dataOnu['keyport'],'zte_idport' => $dataOnu['keyport'],
				]);				
				$setClause = implode(', ', array_map(function($key, $value) {
					return "$key = '$value'";
				}, array_keys($sqlset), $sqlset));
				$this->db->query("UPDATE onus SET $setClause WHERE idonu = '{$this->temp_onu[$dataOnu['keyport']][$dataOnu['keyonu']]['idonu']}'");
			}else{			
				$name = !empty($dataOnu['name']) ? $dataOnu['name'] : '';
				$reason = !empty($dataOnu['reason']) ? $dataOnu['reason'] : '';
				$pole = ($dataOnu['status'] == 1 ? 'online' : 'offline');
				$dist = (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0);
				$sql = "INSERT INTO onus (`olt`,`added`,`updates`,`keyonu`,`status`,`sn`,`inface`,`portolt`,`zte_idport`,`type`,`dist`,`name`,`reason`,`{$pole}`) VALUES ('{$this->id}','{$this->now}', '{$now}','{$dataOnu['keyonu']}', '{$dataOnu['status']}','{$dataOnu['sn']}', '{$dataOnu['inface']}','{$dataOnu['keyport']}', '{$dataOnu['keyport']}','gpon', '{$dist}','{$name}','{$reason}','{$now}')";
				$this->db->query($sql);
				$idonu = $this->db->getInsertId();
				$this->logger->init([
					'log'=>'onu', 'type'=>'addonu','who'=>'cron','onuid'=>$idonu,'descr'=>$dataOnu['inface'].' '.(!empty($dataOnu['sn'])?$dataOnu['sn']:'n/a'),'deviceid'=>$dataOnu['id']
				]);
			}
		}
	}
	public function StatisticOLT(){	
		$array_pon = array();
		$switch_pon = $this->db->SimpleWhile("SELECT * FROM switch_pon WHERE oltid = '{$this->id}'");
		if(isset($switch_pon) && count($switch_pon)>0){
			foreach($switch_pon as $port){
				$getONUstatusPort = $this->db->Multi('onus','idonu,status',['olt' => $this->id,'portolt'=>$port['sfpid']]);
				$getONUstatusPortOn = $this->db->Multi('onus','idonu,status',['status' => 1,'olt' => $this->id,'portolt'=>$port['sfpid']]);
				$count = count($getONUstatusPort);
				if (!empty($port['count']) && $port['count']>50 && $port['count'] != $count) {
					$mess = '[icon-work][b]'.$this->ip.'[/b] [b]'.$port['pon'].'[/b] '.$count.'(onu)';
					$this->db->SQLinsert('notification',['status'=>1,'type'=>3,'system'=>'monitor','message'=>$mess,'added'=>date('Y-m-d H:i:s')]);
				}
				$array_pon[$port['id']] = ['port' => $port['pon'],'count' => $count,'online' => count($getONUstatusPortOn),'offline' => count($getONUstatusPort) - count($getONUstatusPortOn)
				];
			}
		}
		if(is_array_empty($array_pon)){
			foreach($array_pon as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getListOnuOnline(){	
		$sqlonus = $this->db->SimpleWhile("SELECT keyonu, portolt, idonu, type FROM onus WHERE olt = '{$this->id}' AND status = '1'");
		$array = array();
		if(is_array_empty($sqlonus)){
			foreach($sqlonus as $key => $value){
				if(!empty($value['keyonu']) && !empty($value['type']))
					$array[$key] = [
						'id'=>$this->id,'keyonu'=>$value['keyonu'],'keyport'=>$value['portolt'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx'
					];
			}
		}
		return $array ?: null;
	}	
	public function getOnuPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type'])){
			$array = [
				'id'=>$this->id,'keyport'=>$value['portolt'],'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>$this->poller
			];	
		}
		return $array ?: null;
	}
	public function getOnuRxPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type']) && !empty($value['idonu'])){
			$array = [
				'id'=>$this->id,'idonu'=>$value['idonu'],'keyport'=>$value['portolt'],'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rxolt'
			];	
		}
		return $array ?: null;
	}	
	public function tempSaveSignalSaveRxOnuGpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->Signal($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}	
}
?>
