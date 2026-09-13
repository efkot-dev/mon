<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class NOKIA_ISAM_7300{ 
    private $logger;
    private $db;
	protected $id;
	protected $ip;
	protected $community;
    private $indexdevice;
    private $listpoweroff = array();
    private $description = array();
    private $temp_onu = array();
    public $status_onu = array();
    private $now;
    private $primarygpon = 'status,name,dist';
    private $poller = 'status,inface,rx';
    private $configapionugpon = 'dist,model,vendor,status,temp';
    private $configapionugponget = 'rx,tx,rxolt,dist,model,vendor,status,temp,vlan';
	public function __construct(int $swid, $equipment, $db, $logger) {
		$this->logger = $logger;
		$this->db = $db;
		if (empty($swid) || 
			empty($equipment->switches[$swid]['switchid']) || 
			empty($equipment->switches[$swid]['switchoidid']) || 
			empty($equipment->switches[$swid]['switchcommunity'])) {			
			die('check_switch');
		}
		$this->now = date('Y-m-d H:i:s');
		$this->id = $equipment->switches[$swid]['switchid'];
		$this->ip = $equipment->switches[$swid]['switchip'];
		$this->community = $equipment->switches[$swid]['switchcommunity'];
		$this->listNOKIAonu();
	} 
	public function listNOKIAonu() {
		$getonu = $this->db->SimpleWhile("SELECT * FROM onus WHERE olt = '{$this->id}'");
		if (!empty($getonu)) {
			$this->temp_onu = array_column($getonu, null, 'keyonu');
		}
	}	
	public function Support($check){
		return match ($check){
			'port','onu','saveonu','rxolt','api','poller','fileonu' => true, default => false,
		};
	}
	public function decodeNokiaIfIndexONT($ifIndex) {
		$shelf = 1;
		$slot  = 1;
		$subrack = 1;
		$b3 = ($ifIndex >> 24) & 0xFF;
		$pon = (($ifIndex >> 16) & 0xFF) - 191;
		$b1 = ($ifIndex >> 8) & 0xFF;
		$b0 = $ifIndex & 0xFF;
		if($b1 ==0){
			$onuId = 1;
		}else{
			$onuId = ($b1/2) +1;
		}
		return "$shelf/$slot/$subrack/$pon/$onuId";
	}	
	public function decodeNokiaIfIndexPon($ifIndex) {
		$shelf = 1;
		$slot  = 1;
		$subrack = 1;
		$b3 = ($ifIndex >> 24) & 0xFF;
		$pon = (($ifIndex >> 16) & 0xFF) - 159;
		$b0 = ($ifIndex & 0xFF) + 1;
		return "$shelf/$slot/$b0/$pon";
	}
	public function Load(){
		$gpon = [
			'oid' => '1.3.6.1.4.1.637.61.1.35.10.1.1.5','type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$gponlist = pmon_walk($gpon);
		$result_gpon = [];
		if(isset($gponlist) && !empty($gponlist)){
			foreach ($gponlist as $io => $eachsig) {
				if (!empty($eachsig['result'])) {
					$sn = $this->valueToSerial($eachsig['result']);
					$result_gpon[$io] = [
						'do' => 'onu','id' => $this->id,'pon' => 'gpon','types' => $this->primarygpon,'sn' => $sn,'inface' => $this->decodeNokiaIfIndexONT($io),'keyonu' => $io
					];
				}
			}
		}		
		if(is_array_empty($result_gpon)){
			checkerONUNokiaGPON($result_gpon,$this->id);
		}else{
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'empty_snmp_walk','deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result_gpon) ? $result_gpon : null);
	}
	public function ConfigApiOnu($data){
		return array('do' => 'onu','types' => $this->configapionugpon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
	}	
	public function ConfigApiOnuGet($data){
		return array('do' => 'onu','types' => $this->configapionugponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
	}
	public function statusNOKIA($status) {
		$statuses = ["0" => 1,"1" => 1,"2" => 2,"3" => 1,"4" => 2];		
		return isset($statuses[$status]) ? $statuses[$status] : 2;
	}	
	public function Onu($dataPort,$dataOnu){
		$res = array(); 
		if(is_array_empty($dataPort)){
			foreach($dataPort as $type => $value) {
				$res[$type] = $this->preparedataBDCOM($value,$type);
			}
		} elseif(!$dataPort) {
			$array_separated = explode(',',$this->configapionugpon);
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
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			if(!empty($getData['model']))	
				$result['model'] = $getData['model'];			
			if(!empty($getData['temp']))	
				$result['temp'] = $getData['temp'];
			if(!empty($getData['vendor']))	
				$result['vendor'] = $getData['vendor'];
			if(!empty($getData['dist'])) 
				$result['dist'] = $getData['dist'];			
			if(!empty($getData['pvid'])) 
				$result['pvid'] = $getData['pvid'];
			if(!empty($ont['lastrx']))
				$result['lastrx'] = $ont['lastrx'];
			if(!empty($getData['rxolt'])) 
				$result['rxolt'] = $getData['rxolt'];
			if(!empty($getData['rx'])) 
				$result['rx'] = $getData['rx'];
			if(!empty($getData['tx'])) 
				$result['tx'] = $getData['tx'];			
			if(!empty($ont['reason'])) 
				$result['reason'] = $ont['reason'];				
			if(!empty($ont['name'])) 
				$result['name'] = $ont['name'];			
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
				return $this->statusNOKIA($data);
			case 'dist':
				return isset($data) ? (int) $data : null;			
			case 'admineth':
				return $data==1  ? 'up' : 'down';
			case 'rx':
			case 'rxolt':
			case 'tx':
				return $data ? $this->Signal($data) : null;
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
				print_R($value);
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
	public function Port(){
		$data = array();
		$temp_pon = array();
		$temp_port = array();
		$olt_port = [
			'oid' => '1.3.6.1.2.1.31.1.1.1.1','cache' => true,'timecache' => 1000,'namecache' => 'nokia_list_port_'.$this->id,'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$index_port = pmon_walk($olt_port);	
		if(is_array_empty($index_port)){			
			foreach($index_port as $idport => $value) {				
				$pon_tmp = $this->clearsnmp($value['result']);
				if($idport > 50000000 && preg_match("/PON/i",$pon_tmp)){
					$inface = $this->decodeNokiaIfIndexPon($idport);
					preg_match('/(\d+)\/(\d+)\/(\d+)\/(\d+)/',$inface,$mat);
					$temp_pon[$idport] = array(
						'name' => 'GPON '.$inface,
						'sort' => $mat[4],
						'typeport' => 'gpon',
						'sfpid' => $idport,
						'llid' => $idport,
						'cardcount' => 128
					);	
					$temp_port[$idport] = array(
						'id' => $idport,
						'name' => 'GPON '.$inface,
						'typeport' => 'gpon',
						'llid' => $idport
					);
				}				
			}
			$data['pon'] = $temp_pon;
			$data['port'] = $temp_port;
		}
		return (is_array_empty($data) ? $data : null);
	}
	public function clearsnmp(string $value): string {
		$value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|\s|=|"/', '', $value);
		return trim($value);
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
			$allonu = $this->db->Simple('Select count(idonu) as count FROM onus WHERE olt = '.$this->id.' AND zte_idport = '.$dataPort['sort']);
			if(!empty($dataPort['sfpid']) && isset($allonu['count'])){
				$this->db->query("UPDATE switch_pon SET count = '{$allonu['count']}' WHERE sfpid  = '{$dataPort['sfpid']}' AND oltid = '{$this->id}'");				
				$this->db->query("UPDATE onus SET portolt = '{$dataPort['sfpid']}' WHERE olt  = '{$this->id}' AND zte_idport = '{$dataPort['sort']}'");
			}
		}
	}
    protected function savePortSwitch($data) {
		if(!empty($data['id'])){	
			$row = $this->db->Simple("SELECT * FROM switch_port WHERE deviceid = '{$this->id}' AND llid = '{$data['id']}' LIMIT 1");
			if(empty($row['id'])){
				$sql ="INSERT INTO switch_port (deviceid, llid, nameport, typeport, operstatus, added) VALUES ('{$this->id}','{$data['id']}','{$data['name']}','{$data['typeport']}','none','{$this->now}')";
				$this->db->query($sql);
			}else{
				$this->db->query("UPDATE switch_port SET nameport = '{$data['name']}' WHERE id = '{$row['id']}'");
			}
		}
	}
	public function tempUpdateSignalCheck(){	
		global $db;

	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $config;
		$savehistor = $savehistor ?? null;
		if(isset($this->temp_onu[$dataOnu['keyonu']]) && !empty($this->temp_onu[$dataOnu['keyonu']])){
			$onu = $this->temp_onu[$dataOnu['keyonu']];
		} else {
			$onu = $this->db->Simple("SELECT status,rx,idonu,inface,mac,sn,changerx,olt FROM onus WHERE olt = '{$this->id}' AND keyonu = '{$dataOnu['keyonu']}' LIMIT 1");
		}
		if (!empty($onu['idonu'])) {
			$rx = $dataOnu['rx'] ?? null;
			if ($rx !=false ) {
				$rxValue = $this->Signal($rx);
			}
			if ($config['logsignal'] == 'on') {
				if (isset($rxValue) && $rxValue !=false ) {
					$savehistor = SignalMonitor($onu['status'], $rxValue, $onu['rx'], $onu['idonu'], $onu);
				}
			} else {
				$savehistor = true;
			}
			if (isset($rxValue) && $rxValue !=false ) {
				$this->db->query("UPDATE onus SET rx = '{$rxValue}' WHERE idonu  = {$onu['idonu']}");
			}
			if ($onu['status'] == 1 && isset($rxValue) && $rxValue !=false && isSignalChanged($rxValue, $onu['rx'])) {
				$logont = [
					'log' => 'ont','type' => 'signal',
					'idonu' => $onu['idonu'],'olt' => $this->id, 'time' => $this->now,
					'curent' => $rxValue ?? 0,'last' => $onu['rx'] ?? 0
				];
				$this->logger->init($logont);
			}
			if (!empty($config['onugraph']) && $config['onugraph'] == 'on' && $savehistor && isset($rxValue)  && $rxValue !=false ) {
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
	public function Signal($value) {
		if (preg_match('/655/i', $value) || preg_match('/4748/i', $value) ) {
			return '0.00'; 
		}
		$value = trim(str_replace(['"', 'N/A'], ['', 0], $value));
		$value = sprintf('%.2f', $value * 0.002);
		return $value === '0.00' ? 0 : $value;
	}	
	public function SignalRXOlt($value) {
		if (preg_match('/655/i', $value) || preg_match('/4748/i', $value) ) {
			return '0.00'; 
		}
		$value = trim(str_replace(['"', 'N/A'], ['', 0], $value));
		$value = sprintf('%.2f', $value / 10);
		return $value === '0.00' ? 0 : $value;
	}	
	public function Dist($value) {
		if (preg_match('/655/i', $value) || preg_match('/4748/i', $value) ) {
			return 0; 
		}else{
			return intval($value * 0.1);
		}
	}
	public function tempSaveOnuGpon($dataOnu){	
		$sqlset = [];
		$now = $this->now;
		if (!empty($dataOnu['inface'])) {
			preg_match('/(\d+)\/(\d+)\/(\d+)\/(\d+)\/(\d+)/i', $dataOnu['inface'], $dataMatch);
			$indexPortOlt = $dataMatch[4];
		}
		$inface = $dataOnu['inface'];
		$dataOnu['status'] = (!empty($dataOnu['status']) ? $this->statusNOKIA($dataOnu['status']): 2);
		if(!empty($dataOnu['keyonu']) && !empty($dataOnu['sn'])){
			if(isset($this->temp_onu[$dataOnu['keyonu']]['idonu']) && !empty($this->temp_onu[$dataOnu['keyonu']]['idonu'])){
				if ($dataOnu['status'] == 1 && $this->temp_onu[$dataOnu['keyonu']]['status'] == 2) {
					$sqlset['online'] = $now;
					$log_status = 2;
				} elseif ($dataOnu['status'] == 2 && $this->temp_onu[$dataOnu['keyonu']]['status'] == 1) {
					$sqlset['offline'] = $now;
					$log_status = 1;
				}
				if (!empty($dataOnu['reason'])) {
					$sqlset['reason'] = $dataOnu['reason'];
				}

				if (!empty($indexPortOlt)) {
					$sqlset['zte_idport'] = $indexPortOlt;
				}
				$dist = (isset($dataOnu['dist']) ? $this->Dist($dataOnu['dist']) : $this->temp_onu[$dataOnu['keyonu']]['dist']);
				$sqlset = array_merge($sqlset, [
					'status' => $dataOnu['status'],
					'inface' => $inface ?? $this->temp_onu[$dataOnu['keyonu']]['inface'],
					'dist' => $dist,
					'name' => ($dataOnu['name'] ? $this->clearName($dataOnu['name']) : $this->temp_onu[$dataOnu['keyonu']]['name']),
					'sn' => $dataOnu['sn'],
					'portolt' => $this->temp_onu[$dataOnu['keyonu']]['portolt'] ?? $indexPortOlt
				]);
				$changes = [];
				foreach ($sqlset as $key => $value) {
					if (!isset($this->temp_onu[$dataOnu['keyonu']][$key]) || $this->temp_onu[$dataOnu['keyonu']][$key] != $value) {
						$changes[$key] = $value;
					}
				}
				if (!empty($changes)) {
					$changes['updates'] = $now;
					$setClause = implode(', ', array_map(function($key, $value) {
						return "$key = '$value'";
					}, array_keys($changes), $changes));
					$this->db->query("UPDATE onus SET $setClause WHERE idonu = '{$this->temp_onu[$dataOnu['keyonu']]['idonu']}'");
				}
			}else{			
				$name = !empty($dataOnu['name']) ? $dataOnu['name'] : '';
				$reason = !empty($dataOnu['reason']) ? $dataOnu['reason'] : '';
				$pole = ($dataOnu['status'] == 1 ? 'online' : 'offline');
				$dist = (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0);
				$sql = "INSERT INTO onus (`olt`,`added`,`updates`,`keyonu`,`status`,`sn`,
				`inface`,`portolt`,`zte_idport`,`type`,`dist`,`name`,
				`reason`,`{$pole}`) VALUES ('{$this->id}','{$this->now}', '{$now}',
				'{$dataOnu['keyonu']}', '{$dataOnu['status']}','{$dataOnu['sn']}', 
				'{$inface}','{$indexPortOlt}', '{$indexPortOlt}','gpon', '{$dist}', '{$name}','{$reason}','{$now}')";
				$this->db->query($sql);
				$idonu = $this->db->getInsertId();
				$this->logger->init([
					'log'=>'onu', 'type'=>'addonu','who'=>'cron','onuid'=>$idonu,'descr'=>$dataOnu['inface'].' '.(!empty($dataOnu['mac'])?$dataOnu['mac']:'--'),'deviceid'=>$dataOnu['id']
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
		$sqlonus = $this->db->SimpleWhile("SELECT keyonu, idonu, type FROM onus WHERE olt = '{$this->id}' AND status = '1'");
		$array = array();
		if(is_array_empty($sqlonus)){
			foreach($sqlonus as $key => $value){
				if(!empty($value['keyonu']) && !empty($value['type']))
					$array[$key] = [
						'id'=>$this->id,'keyonu'=>$value['keyonu'],'pon'=>'gpon','do'=>'onu','types'=>'rx'
					];
			}
		}
		return $array ?: null;
	}	
	public function getOnuPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type'])){
			$array = [
				'id'=>$this->id,'keyonu'=>$value['keyonu'],'pon'=>'gpon','do'=>'onu','types'=>$this->poller
			];	
		}
		return $array ?: null;
	}
	public function getOnuRxPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['idonu'])){
			$array = [
				'id'=>$this->id,'idonu'=>$value['idonu'],
				'keyonu'=>$value['keyonu'],'pon'=>'gpon',
				'do'=>'onu','types'=>'rxolt'
			];	
		}
		return $array ?: null;
	}	
	public function tempSaveSignalSaveRxOnuGpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->SignalRXOlt($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}
	public function valueToSerial($tempsn) {
		$return = '';
		$tempsn = preg_replace('~^.*?( : )~i','',$tempsn);
		$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
		$sn = str_replace(['Hex-STRING', 'STRING', 'Hex-', ': ', '\x'], '', $tempsn);
		if (strlen($sn) === 24){
			$sn = explode(" ", $sn);
			foreach ($sn as $key => $value){
				if ($key < 4) {
					$return.=chr(hexdec($value));
				}else{
					$return.=$value;
				}
			}
		}else{
			$nosn = substr($sn,4);
			$resn = substr($sn, 0, 4);
			$return = $resn.strtoupper(bin2hex($nosn));
		}
		if (strlen($return) === 15){
			$return = substr($return, 0, -2);
		}
		$return = str_replace('HWT43', 'HWTC', $return);
        return $this->cleanString($return);
	}
	public function cleanString($input) {
		if (isset($input)) {
			$allowed_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789:-.';
			$cleaned_string = '';
			$input_length = strlen($input);
			for ($i = 0; $i < $input_length; $i++) {
				$char = $input[$i];
				if (strpos($allowed_chars, $char) !== false) {
					$cleaned_string .= $char;
				}
			}
			return $cleaned_string;
		}
		return '';
	}	
}
?>
