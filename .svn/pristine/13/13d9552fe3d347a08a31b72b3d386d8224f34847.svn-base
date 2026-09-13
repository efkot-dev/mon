<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class BDCOM_Epon{ 
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
    private $primary = 'status,dist,inface';
    private $poller = 'status,inface,rx';
    private $configapionuepon = 'dist,model,vendor,status,temp';
    private $configapionueponget = 'rx,tx,rxolt,dist,model,vendor,status,temp,vlan';
	public function __construct(int $swid, $equipment, $db, $logger) {
		$this->logger = $logger;
		$this->db = $db;
		if (empty($swid) ||
			empty($equipment->switches[$swid]['switchoidid']) || 
			empty($equipment->switches[$swid]['switchcommunity'])) {			
			die('check_switch');
		}
		if(isset($logger->pmon_config['BDCOM_EPON_ONU_API']) && !empty($logger->pmon_config['BDCOM_EPON_ONU_API'])){
			
		}
		$this->now = date('Y-m-d H:i:s');
		$this->id = $equipment->switches[$swid]['switchid'];
		$this->ip = $equipment->switches[$swid]['switchip'];
		$this->community = $equipment->switches[$swid]['switchcommunity'];
		$this->oidid = $equipment->switches[$swid]['switchoidid'];
		$this->deviceoid = $equipment->switchoid;
		$this->list_onu();
	}   
	public function Support($check){
		return match ($check){
			'port','onu','saveonu','rxolt','api','poller','fileonu' => true,default => false,
		};
	}
	public function checker_mac($pi1){
		if($pi1){
			$ps_iface = explode('.', $pi1);
			$ps_temp = sizeof($ps_iface);			   
			$ps_mac  = substr('0'.dechex($ps_iface[($ps_temp - 6)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 5)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 4)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 3)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 2)]), -2).substr('0'.dechex($ps_iface[($ps_temp - 1)]), -2);
			return preg_replace('/(.{2})/','\1:',$ps_mac,5);
		}
	}
	public function getDescriptionONT_() {
		$name_epon = [
			'oid' => '1.3.6.1.4.1.3320.101.11.1.1.4','type' => 'class','deloid' => true,'ip' => $this->ip,'community' => $this->community,
		];
		$indexonu = pmon_walk($name_epon);
		if (isset($indexonu) && is_array($indexonu)) {
			foreach ($indexonu as $pi1 => $type) {
				if (!empty($type['result']) && isset($pi1)) {
					$name = !empty($type['result']) ? clearDataMacRe($type['result']) : null;
					$mac = $this->checker_mac($pi1);					
					if ($name && $mac) {
						$this->description[$mac] = $name;
					}
				}
			}
		}
	}	
	public function getReasonOnu() {
		$name_epon = ['oid' => '1.3.6.1.4.1.3320.101.11.1.1.11','type' => 'class','deloid' => true,'ip' => $this->ip,'community' => $this->community,];
		$indexonu = pmon_walk($name_epon);
		if (isset($indexonu) && is_array($indexonu)) {
			foreach ($indexonu as $pi1 => $type) {
				if (!empty($type['result']) && isset($pi1)) {
					$st = clearDataMacRe($type['result']);
					$mac = $this->checker_mac($pi1);
					if (isset($st) && isset($mac)) {
						$this->listpoweroff[$mac] = $this->ReasonstatusBdcom($st);
					}
				}
			}
		}
	}	
	public function getDescriptionONT(){
		$name_epon = [
			'oid' => '1.3.6.1.2.1.31.1.1.1.18','type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$indexonu = pmon_walk($name_epon);
		if(is_array($indexonu)){
			foreach($indexonu as $pi1 => $type) {
				if(!empty($type['result'])){
					$this->description[trim($pi1)] = $this->clearData($type['result']);
				}
			}
		}
	}
	private function logSnmpError(string $message): void {
		$this->logger->init(['log' => 'device','type' => 'snmp','descr' => $message,'deviceid' => $this->id,'who' => 'cron']);
	}	
	public function bdcom_mac($type) {
		if (empty($type)) {
			return false;
		}
		$type = str_replace('"', '', trim($type));
		if (preg_match("/Hex/i", $type)) {
			$onu = explode('Hex-STRING: ', $type);
			$onu = mb_strtolower(end($onu));
		} elseif (preg_match("/STRING/i", $type)) {
			$onu = bin2hex(explode('STRING: ', $type)[1]);
		} else {
			$onu = bin2hex($type);
		}
		return preg_replace('/(.{2})/','\1:', preg_replace("/\s+/", "", $onu), 5);
	}
	public function Load(): array|false
    {
        $walk = pmon_walk(['oid' => '1.3.6.1.4.1.3320.101.10.1.1.3', 'type' => 'class', 'deloid' => true, 'ip' => $this->ip, 'community' => $this->community,]);
        if (empty($walk)) {
			$this->logSnmpError('Empty SNMP Walk ' . $epon['oid']);
			return false;
		}
        $result = [];
        foreach ($walk as $keyonu => $row) {
            if (empty($row['result'])) {
                continue;
            }
            $mac = $this->bdcom_mac($row['result']);
            if (!$mac) {
                continue;
            }
            $result[$keyonu] = ['do' => 'onu', 'id' => $this->id, 'mac' => $mac, 'pon' => 'epon', 'types'  => $this->primary, 'keyonu' => (string)$keyonu];
        }
        if (empty($result)) {
			$this->logSnmpError('No valid ONU after SNMP ' . $epon['oid']);
			return false;
		}
        $this->getReasonOnu();
        ($this->logger->pmon_config['PMON_BEDO'] ?? 0) != 1 ? $this->getDescriptionONT() : $this->getDescriptionONT_();
        $this->BDCOM_checker_ONT($result, $this->id);
		return $result;
    }
	public function BDCOM_checker_ONT(array $snmpData, int $olt): void {
		$dbOnu = $this->db->SimpleWhile("SELECT idonu, keyonu, mac, inface, type AS pon, olt FROM onus WHERE olt = '{$olt}'");
		if (empty($dbOnu)) return;
		$snmpIndex = [];
		foreach ($snmpData as $onu) {
			if (!empty($onu['keyonu']) && !empty($onu['mac'])) {
				$key = $onu['keyonu'] . '|' . strtolower($onu['mac']);
				$snmpIndex[$key] = $onu; // зберігаємо оригінальні дані
			}
		}
		$processed = [];
		foreach ($dbOnu as $onu) {
			$key = $onu['keyonu'] . '|' . strtolower($onu['mac']);
			if (!isset($processed[$key])) {
				$processed[$key] = $onu['idonu'];
			} else {
				$this->db->query("DELETE FROM onus WHERE idonu = '{$onu['idonu']}'");
				$this->logger->init([
					'log' => 'onu',
					'type' => 'deletonu',
					'descr' => "Duplicate {$onu['pon']} {$onu['inface']} {$onu['mac']}",
					'deviceid' => $onu['olt'],
					'onuid' => $onu['idonu'],
					'who' => 'clear'
				]);
			}
			if (!isset($snmpIndex[$key])) {
				$this->db->query("DELETE FROM onus WHERE idonu = '{$onu['idonu']}'");
				$this->logger->init([
					'log' => 'onu',
					'type' => 'deletonu',
					'descr' => "Removed {$onu['pon']} {$onu['inface']} {$onu['mac']}",
					'deviceid' => $onu['olt'],
					'onuid' => $onu['idonu'],
					'who' => 'clear'
				]);
			}
		}
	}
	public function getName($dataOnu){
		if(isset($this->logger->pmon_config['PMON_BEDO']) && $this->logger->pmon_config['PMON_BEDO']!=1){
			return (!empty($this->description[$dataOnu['keyonu']]) ? $this->description[$dataOnu['keyonu']] : '');
		}else{
			return (!empty($this->description[$dataOnu['mac']]) ? $this->description[$dataOnu['mac']] : '');			
		}		
	}
	public function ConfigApiOnu($data){
		return array('do' => 'onu','types' => $this->configapionuepon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
	}	
	public function ConfigApiOnuGet($data){
		return array('do' => 'onu','types' => $this->configapionueponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
	}
	public function statusBdcom($status): int
    {
        return [0 => 1, 1 => 1, 2 => 2, 3 => 1, 4 => 2][$status] ?? 2;
    }	
	public function ReasonstatusBdcom($status): string
    {
        return [8 => 'err8',3 => 'err30',4 => 'err31',5 => 'err32',6 => 'err33',7 => 'err34',9 => 'err1'][$status] ?? 'err0';
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
				return $this->statusBdcom($data);
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
	public function Port(){
		$data = array();
		$listPon = array();
		$olt_port = [
			'oid' => '1.3.6.1.2.1.2.2.1.2','cache' => true,'timecache' => 1000,'namecache' => 'list_port_'.$this->id,
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$index_port = pmon_walk($olt_port);	
		if(is_array_empty($index_port)){
			$listport = array();
			foreach($index_port as $idport => $value) {				
				if(!empty($idport) && !empty($value['result'])){
					$dataindexport = $this->clearResult($value['result']);
					$name_port = getNameBdcomport($dataindexport);						
					if(!preg_match('/epon0\/(\d+):(\d+)/i',$dataindexport)
						AND !preg_match('/(\d+):(\d+)/i',$dataindexport) 
							AND !preg_match('/VLAN/i',$dataindexport) 
								AND !preg_match('/Null/i',$dataindexport)){									
						$types = getTypePort($dataindexport);							
						if($types && isset($name_port)){							
							$listport[$idport] = array(
								'id' =>trim($idport),
									'typeport' => $types,
										'name' => $name_port);
							if(isset($this->logger->pmon_config['PMON_BEDP']) && $this->logger->pmon_config['PMON_BEDP']!=1){
								if (isset($this->description) && isset($this->description[$idport])) {
									$listport[$idport]['descrport'] = $this->description[$idport];
								}
							}
						}
					}
				}				
			}
			$data['port'] = $listport;
		}
		if(isset($data['port']) && count($data['port'])>0){
			foreach($data['port'] as $idPonport => $valuePon){
				if(preg_match('/pon/i',$valuePon['name'])) {
					preg_match('/EPON 0\/(\d+)/',$valuePon['name'],$mat);
					if(isset($mat[1]) && !empty($mat[1])){
						$listPon[$idPonport] = [
							'name' => 'EPON 0/' . $mat[1],'sort' => $mat[1],'sfpid' => $valuePon['id'],
							'descrport' => (isset($valuePon['descrport'])?$valuePon['descrport']:''),'cardcount' => 64
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
			$allonu = $this->db->Simple('Select count(idonu) as count FROM onus WHERE olt = '.$this->id.' AND zte_idport = '.$dataPort['sort']);
			if(!empty($dataPort['sfpid']) && isset($allonu['count'])){
				$this->db->query("UPDATE switch_pon SET count = '{$allonu['count']}' WHERE sfpid  = '{$dataPort['sfpid']}' AND oltid = '{$this->id}'");				
				$this->db->query("UPDATE onus SET portolt = '{$dataPort['sfpid']}' WHERE olt  = '{$this->id}' AND zte_idport = '{$dataPort['sort']}'");
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
	public function tempSaveSignalSaveOnuEpon($dataOnu){	
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
		return str_replace(['/', '\\', '"', "'", ' '], '', $value);
	}
	public function clearData($value) {
		$unwanted_strings = ['INTEGER:', 'Hex-STRING:', 'STRING:', 'Gauge32:', '"', ' '];
		$value = str_replace($unwanted_strings, '', $value);
		return trim($value);
	}
	public function list_onu(): void
    {
        $rows = $this->db->SimpleWhile(
            "SELECT * FROM onus WHERE olt = '{$this->id}'"
        );
        if ($rows) {
            $this->temp_onu = array_column($rows, null, 'keyonu');
        }
    }
	public function Signal($value): float	{
		if ($value === null || $value === '' || $value === 'N/A') {
			return 0.0;
		}
		if (strpos((string)$value, '655') !== false || strpos((string)$value, '4748') !== false) {
			return 0.0;
		}
		$value = str_replace('"', '', $value);
		return round(((float)$value) / 10, 2);
	}
	public function tempSaveOnuEpon(array $onu): void {
		if (empty($onu['keyonu']) || empty($onu['mac'])) {
			return;
		}
		$now = $this->now;
		$key = $onu['keyonu'];
		$old = $this->temp_onu[$key] ?? null;
		[$inface, $port] = $this->parseInface($onu['inface'] ?? '');
		$onu['status'] = !empty($onu['status']) ? $this->statusBdcom($onu['status']) : 2;
		$onu['reason'] = $this->listpoweroff[$onu['mac']] ?? null;
		$onu['name'] = $this->getName($onu);
		$onu['dist'] = $onu['dist'] ?? ($old['dist'] ?? 0);
		if ($old && !empty($old['idonu'])) {
			$update = [];
			if ($onu['status'] != $old['status']) {
				$update[$onu['status'] == 1 ? 'online' : 'offline'] = $now;
			}
			$fields = [
				'status'  => $onu['status'],
				'mac' => $onu['mac'],
				'inface' => $inface ?: $old['inface'],
				'dist' => $onu['dist'],
				'name' => $onu['name'] ? $this->clearName($onu['name']) : $old['name'],
				'reason' => $onu['reason'],
				'zte_idport' => $port ?: $old['zte_idport'],
				'portolt' => $old['portolt'] ?? $port
			];
			foreach ($fields as $k => $v) {
				if ($v !== null && (!isset($old[$k]) || $old[$k] != $v)) {
					$update[$k] = $v;
				}
			}
			if ($update) {
				$update['updates'] = $now;
				$this->db->SQLupdate('onus', $update, ['idonu' => $old['idonu']]);
			}
			return;
		}
		$pole = $onu['status'] == 1 ? 'online' : 'offline';
		$insert = [
			'olt' => $this->id,
			'added' => $now,
			'updates' => $now,
			'keyonu' => $key,
			'status' => $onu['status'],
			'mac' => $onu['mac'],
			'inface' => $inface,
			'portolt' => $port,
			'zte_idport' => $port,
			'type' => 'epon',
			'dist' => $onu['dist'],
			'name' => $onu['name'] ? $this->clearName($onu['name']) : '',
			'reason' => $onu['reason'],
			$pole => $now
		];
		$this->db->SQLinsert('onus', $insert);
		$idonu = $this->db->getInsertId();
		$this->logger->init(['log' => 'onu','type' => 'addonu','who' => 'cron','onuid' => $idonu,'descr' => ($onu['inface'] ?? '') . ' ' . $onu['mac'],'deviceid' => $this->id]);
	}
	private function parseInface(string $inface): array
	{
		$inf = strtolower(trim($inface));
		$inf = str_replace(['epon', ' '], '', $inf);
		if (preg_match('/0\/(\d+):(\d+)/', $inf, $m)) {
			return [$inf, (int)$m[1]];
		}
		return [$inf ?: null, null];
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
						'id'=>$this->id,'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx'
					];
			}
		}
		return $array ?: null;
	}	
	public function getOnuPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type'])){
			$array = [
				'id'=>$this->id,'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>$this->poller
			];	
		}
		return $array ?: null;
	}
	public function getOnuRxPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type']) && !empty($value['idonu'])){
			$array = [
				'id'=>$this->id,'idonu'=>$value['idonu'],'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rxolt'
			];	
		}
		return $array ?: null;
	}	
	public function tempSaveSignalSaveRxOnuEpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->Signal($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}	
}
?>
