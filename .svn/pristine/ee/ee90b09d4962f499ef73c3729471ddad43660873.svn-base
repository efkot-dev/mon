<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class HUAWEI_5608t { 
    private $logger;
    private $db;
	protected $snmp;
	protected $id;
	protected $ip;
	protected $oidid;
	protected $community;
	protected $deviceoid;
    private $indexdevice;
    public $temp_onu_gpon = array();
    private $temp_onu_epon = array();
    private $cache_onu_gpon = array();
    private $cache_onu_epon = array();
    private $now;
    private $pollergpon = 'status,rx';
    private $pollerepon = 'status,rx';
    private $primarygpon = 'status,dist,name,reason';
    private $primaryepon = 'status,dist,name,reason';
    private $configapionuepon = 'dist,status,reason,name,temp,tx,rx';
    private $configapionueponget = 'dist,status,reason,name,temp,tx,rx';
    private $configapionugpon = 'dist,model,name,status,reason';
    private $configapionugponget = 'rx,dist,tx,model,name,status,reason';
    public function __construct($swid,$equipment, $db, $logger){
		$this->logger = $logger;
		$this->db = $db;
		if(is_numeric($swid)){
			$this->snmp = new SnmpMonitor();
			$this->now = date('Y-m-d H:i:s');
			$this->id = $equipment->switches[$swid]['switchid'];
			$this->ip = $equipment->switches[$swid]['switchip'];
			$this->community = $equipment->switches[$swid]['switchcommunity'];
			$this->oidid = $equipment->switches[$swid]['switchoidid'];
			$this->deviceoid = $equipment->switchoid;
			$this->list_onu();
		}
	} 
	public function Support($check){
		return match ($check){
			'port', 'onu', 'saveonu', 'api', 'rxolt', 'poller', 'fileonu' => true,	default => false,
		};
	}
	public function huawei_ifindex($ifIndex){
		$return['olt'] = ($ifIndex & 16252928) >> 19;
		$return['slot'] = ($ifIndex & 253952) >> 13;
		$return['port'] = ($ifIndex & 3840) >> 8;
		return $return['olt'].'/'.$return['slot'].'/'.$return['port'];
	}	
	public function array_huawei_ifindex($ifIndex){
		$return['olt'] = ($ifIndex & 16252928) >> 19;
		$return['slot'] = ($ifIndex & 253952) >> 13;
		$return['port'] = ($ifIndex & 3840) >> 8;
		return $return;
	}
	public function getwalk($oids){
		$result = [];
		if($oids){
			snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
            $session = @new SNMP(SNMP::VERSION_2C, $this->ip, $this->community);
            $raw = @$session->walk($oids);
			sleep(3);
			@$session->close();
			if(!empty($raw)) {
				foreach ($raw as $oid => $value) {
					$arraytemp = str_replace('.'.$oids.'.','',$oid);
					$result[trim($arraytemp)]['result'] = $value;
				}
			} 
			return $result;
		}else{
			return $result;
		}
	}
	public function list_onu() {
		$getonu = $this->db->SimpleWhile("SELECT * FROM onus WHERE olt = '{$this->id}'");
		if ($getonu) {
			foreach ($getonu as $ont) {
				if ($ont['type'] === 'epon') {
					$this->temp_onu_epon[$ont['zte_idport']][$ont['keyonu']] = $ont;
				} elseif ($ont['type'] === 'gpon') {
					$this->temp_onu_gpon[$ont['zte_idport']][$ont['keyonu']] = $ont;
				}
			}
		}
	}
	public function Load(){
		$resultgpon = [];
		$checkerGpon = [];
		$result = [];
		$resultepon = [];
		$checkerEpon = [];
		$int_epon = 1;
		$int_gpon = 1;
		$mac = '';
		$gpon_onu = [
			'oid' => $this->deviceoid[$this->id]['onu']['listsn']['gpon']['oid'],'type' => 'class',
			'deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$listgpononu = pmon_walk($gpon_onu);
		if (isset($listgpononu) && is_array($listgpononu)) {
			foreach ($listgpononu as $io => $eachsig) {
				$sn = '';
				preg_match('/(\d+).(\d+)/',$io,$mat);
				$sn = SnHuawei($eachsig['result']);
				if(ctype_digit($mat[1]) && ctype_digit($mat[2]) && isset($sn)) {
					$resultgpon[$int_gpon] = [
						'inface' => $this->huawei_ifindex(trim($mat[1])) . ':' . trim($mat[2]),	'sn' => $sn,'do' => 'onu','id' => $this->id,'pon' => 'gpon','types' => $this->primarygpon,'keyonu' => trim($mat[2]),'keyport' => trim($mat[1])
					];
					$checkerGpon[trim($mat[1])][trim($mat[2])] = [
						'keyonu' => trim($mat[2]),'keyport'=> trim($mat[1])
					];
					$int_gpon ++;
				}
			}
		}
		if(!empty($this->deviceoid[$this->id]['onu']['listmac']['epon']['oid'])){
			$listinfaceepon = $this->deviceoid[$this->id]['onu']['listmac']['epon']['oid'];
			$listepononu = $this->getwalk($listinfaceepon);
			if (isset($listepononu) && is_array($listepononu)) {
				foreach($listepononu as $ioss => $eachsigs) {
					preg_match('/(\d+).(\d+)/',$ioss,$mats);
					$keyonus = (!empty($mats[2]) || ctype_digit($mats[2]) ? trim($mats[2]) : null);
					$keyports = (!empty($mats[1]) || ctype_digit($mats[1]) ? trim($mats[1]) : null);
					if(isset($keyonus) && isset($keyports) && !empty($eachsigs['result'])){
						$mac = $this->MacHuawei($eachsigs['result']);
						if(ctype_digit($keyonus) && ctype_digit($keyports) && isset($mac)){
							$resultepon[$int_epon] = [
								'inface'=>$this->huawei_ifindex($keyports).':'.$keyonus,'mac'=>$mac,
								'do' => 'onu','id'=>$this->id,'pon'=>'epon','types'=>$this->primaryepon,'keyonu'=> $keyonus,'keyport'=> $keyports
							];
							$checkerEpon[$keyports][$keyonus] = [
								'keyonu' => $keyonus,'keyport'=>$keyports
							];
							$int_epon ++;
						}
					}
				}
			}
		}
		// merge
		if (isemptyarray($resultepon) && isemptyarray($resultgpon)){
			$result = null;
		} else {
			$result = (isemptyarray($resultepon)) ? $resultgpon : ((isemptyarray($resultgpon)) ? $resultepon : array_merge($resultgpon, $resultepon));
		}
		// checker gpon onu
		if(is_array_empty($checkerGpon))
			checkerHuaweiGpon($checkerGpon,$this->id,'gpon');
		// checker epon onu
		if(is_array_empty($checkerEpon))
			checkerHuaweiGpon($checkerEpon,$this->id,'epon');
		// log empty snmp
		if(isemptyarray($result))
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'empty_snmp_walk','deviceid'=>$this->id,'who'=>'cron']);
		return (is_array_empty($result) ? $result : null);
	}
	public function SnOnuHuawei($raw) {
		$right = preg_replace('~^.*?\s=\s~', '', $raw);
		if ($right === null) return '';
		$right = trim(preg_replace('~\s+~', ' ', $right));
		if (stripos($right, 'Hex-STRING') !== false) {
			if (!preg_match_all('~([A-Fa-f0-9]{2})~', $right, $m)) return '';
			$hexBytes = $m[1];
			if (count($hexBytes) < 8) return '';
			$vendor = '';
			for ($i = 0; $i < 4; $i++) {
				$b = hexdec($hexBytes[$i]);
				if ($b >= 0x20 && $b <= 0x7E) {
					$vendor .= chr($b);
				} else {
					$vendor .= strtoupper(sprintf('%02X', $b));
				}
			}
			$serialHex = '';
			for ($i = 4; $i < 8; $i++) {
				$serialHex .= strtoupper(sprintf('%02X', hexdec($hexBytes[$i])));
			}
			$sn = strtoupper($vendor . $serialHex);
			$sn = preg_replace('~[^A-Z0-9]~', '', $sn);
			return $sn;
		}
		if (stripos($right, 'STRING') !== false) {
			$s = trim(preg_replace('~^STRING:~i', '', $right));
			$s = trim($s, " \t\n\r\0\x0B\"'");
			$asciiOnly = preg_replace('~[^\x20-\x7E]~', '', $s);
			if ($asciiOnly !== $s) {
				$hex = '';
				for ($i = 0, $n = strlen($s); $i < $n; $i++) {
					$hex .= strtoupper(sprintf('%02X', ord($s[$i])));
				}
				$s = $hex;
			}
			$s = strtoupper($s);
			$s = preg_replace('~[^A-Z0-9]~', '', $s);
			return $s;
		}
		return '';
	}
	public function MacHuawei($type) {
		if (preg_match("/Hex/i", $type)) {
			$re_z_z = explode('Hex-STRING: ', $type);
			$re_z = end($re_z_z);
			$re_z = str_replace('"', '',$re_z);
			$re_z = trim($re_z);
			$onu = preg_replace("/\s+/","",mb_strtolower($re_z));
			return preg_replace('/(.{2})/','\1:',$onu,5);
		}elseif(preg_match("/STRING/i", $type)) {
			$re_ze_mac = explode('STRING: ', $type);
			$re_mac = end($re_ze_mac);
			$re_mac = str_replace('"', '',$re_mac);
			$re_mac = trim($re_mac);
			$onu = bin2hex($re_mac);
			return preg_replace('/(.{2})/','\1:',$onu,5);
		}else{
			return false;
		}
	}
	public function ConfigApiOnu($data){
		return match (mb_strtolower($data['type'])) {
			'gpon' => [
				'do' => 'onu',
				'types' => $this->configapionugpon,
				'pon' => mb_strtolower($data['type']),
				'keyport' => $data['zte_idport'],
				'keyonu' => $data['keyonu'],
				'id' => $this->id,
			],
			'epon' => [
				'do' => 'onu',
				'types' => $this->configapionuepon,
				'pon' => mb_strtolower($data['type']),
				'keyport' => $data['zte_idport'],
				'keyonu' => $data['keyonu'],
				'id' => $this->id,
			],
			default => null,
		};
	}	
	public function ConfigApiOnuGet($data){
		$type = mb_strtolower($data['type']);
		return match ($type) {
			'gpon' => [
				'do' => 'onu',
				'types' => $this->configapionugponget,
				'pon' => $type,
				'keyport' => $data['zte_idport'],
				'keyonu' => $data['keyonu'],
				'id' => $this->id,
			],
			'epon' => [
				'do' => 'onu',
				'types' => $this->configapionueponget,
				'pon' => $type,
				'keyport' => $data['zte_idport'],
				'keyonu' => $data['keyonu'],
				'id' => $this->id,
			],
			default => null,
		};
	}
	public function Onu($dataPort,$dataOnu){
		$res = array();
		if(is_array_empty($dataPort)){
			foreach($dataPort as $type => $value) {
				if (preg_match("/gpon/i", $dataOnu['type'])) 
					$res[$type] = $this->preparedataGpon($value,$type);
				if (preg_match("/epon/i", $dataOnu['type'])) 
					$res[$type] = $this->preparedataEpon($value,$type);
			}
		}
		if(!$dataPort && !$res){
			if (preg_match("/gpon/i", $dataOnu['type'])) 
				$array_separated = explode(',',$this->configapionugpon);
			if (preg_match("/epon/i", $dataOnu['type'])) 
				$array_separated = explode(',',$this->configapionuepon);
			foreach($array_separated as $type){
				$res[$type] = (!empty($dataOnu[$type])?$dataOnu[$type]:'');
			}
		}
		if(is_array_empty($res)){
			$result = $this->updateonu($dataOnu,$res);
		}else{
			$result = false;
		}
		return (isemptyarray($result) ? null : $result);
	}
	public function updateonu($ont, $getData) {
		$sqlset = [];
		$where = [
			'idonu' => $ont['idonu']
		];
		$result = [
			'type' => $ont['type'],
			'status' => $getData['status'],
			'wan' => (isset($getData['eth'])?$getData['eth']:''),
		];
		if (isset($getData['tx'])) {
			$sqlset['tx'] = $result['tx'] = $getData['tx'];
		}
		if (isset($getData['rx'])) {
			$sqlset['rx'] = $result['rx'] = $getData['rx'];
		}
		if (isset($getData['dist'])) {
			$sqlset['dist'] = $result['dist'] = $getData['dist'];
		}
		if(empty($sqlset['dist'])){
			$sqlset['dist'] = 0;
		}
		if (isset($getData['model'])) {
			$sqlset['model'] = $result['model'] = $getData['model'];
		}
		if (isset($getData['reason'])) {
			$sqlset['reason'] = $result['reason'] = $getData['reason'];
		}
		if ($ont['status'] == 2 && $getData['status'] == 1) {
			if (isset($getData['timeaut'])) {
				$sqlset['online'] = $this->now;
			}
			if (isset($getData['offline'])) {
				$sqlset['online'] = $getData['offline'];
			}
			$sqlset['status'] = $result['status'] = 1;
		}
		if ($ont['status'] == 1 && $getData['status'] == 2) {
			$sqlset['offline'] = $this->now;
			$sqlset['status'] = $result['status'] = 2;
		}
		if (isset($sqlset)) {
			$this->db->SQLupdate('onus', $sqlset, $where);
		}
		foreach ([
			'name', 'bias', 'linepro', 'lastrx', 'service',
			'countmacport', 'uservlan', 'onuerror', 'olttx',
			'oltrx', 'temp', 'status'
		] as $key) {
			if (isset($getData[$key])) {
				$result[$key] = $getData[$key];
			}
		}
		return $result;
	}
	public function preparedataGpon($dataApi,$type): mixed
	{
		$data = $this->clearData($dataApi);
		return match ($type) {
			'status' => ($data == 1 ? 1 : 2),
			'dist' => (isset($data)?(int) str_replace('-1', '0', $data):0),
			'rx', 'tx' => $data ? $this->clear_rx($data) : null,
			'service', 'model', 'uservlan', 'countmacport', 'name', 'onuerror', 'linepro', 'temp' => $data ? $data : null,
			'reason' => reasonGponHuawei($data),
			'bias' => isset($data) ? ceil($data / 1000) : 0,
			'eth' => in_array($data, [34, 24, 2], true) ? 'up' : 'down',
			default => null,
		};
	}
	public function preparedataEpon($dataApi,$type): mixed
	{
		$data = $this->clearData($dataApi);
		return match ($type) {
			'status' => ($data == 2 ? 1 : 2),
			'dist' => (isset($data)?(int) str_replace('-1', '0', $data):0),
			'rx', 'tx', 'olttx', 'oltrx' => $data ? $this->clear_rx($data) : null,
			'name', 'temp' => $data?$data:null,
			'reason' => reasonGponHuawei($data),
			'eth' => ($data === 2 ? 'down' : 'up'),
			default => null,
		};
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
			$this->db->SQLupdate('switch',['updates_port' => $this->now],['id' => $this->id]);
		}
	}
	public function Port(){
		$data = [];
		$list_huawei_port = [];
		$gpon_onu = [
			'oid' => $this->deviceoid[$this->id]['global']['listport']['port']['oid'],'cache' => true,'timecache' => 10000,
			'namecache' => 'list_port_'.$this->id,'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$listgpononu = pmon_walk($gpon_onu);
		if(is_array_empty($listgpononu)){
			foreach ($listgpononu as $huawei_idport => $huawei_data) {	
				if (isset($huawei_data['result']) && preg_match("/pon/i", $huawei_data['result']) || preg_match("/ethernet/i", $huawei_data['result'])) {			
					if(!empty($huawei_idport) && !empty($huawei_data['result'])){
						$oid_ = '1.3.6.1.2.1.2.2.1.8.'.$huawei_idport;
						$temp_status = $this->snmp->get($this->ip,$this->community,$oid_,true);
						$portstatusres = $this->clearData($temp_status);
						$nameport = getNameHuaweiport($huawei_data['result']);
						$list_huawei_port[$huawei_idport] = array(
							'operstatus' =>($portstatusres ? $this->portstatus($portstatusres):'none'),
							'id' => $huawei_idport,
							'typeport' => getTypePortHuawei($nameport),
							'name' => $nameport
						);
					}	
				}
			}
			$data['port'] = $list_huawei_port;
		}
		if(is_array_empty($data['port'])){
			$i = 1;
			foreach($data['port'] as $idPonport => $valuePon){
				if(preg_match('/pon/i',$valuePon['typeport'])){
					$listPonGpon[$idPonport]['name'] = $valuePon['name'];
					$listPonGpon[$idPonport]['operstatus'] = $valuePon['operstatus'];
					$listPonGpon[$idPonport]['sort'] = $i;
					$listPonGpon[$idPonport]['sfpid'] = $valuePon['id'];
					if(preg_match('/gpon/i',$valuePon['typeport']))
						$listPonGpon[$idPonport]['cardcount'] = 128;
					if(preg_match('/epon/i',$valuePon['typeport']))
						$listPonGpon[$idPonport]['cardcount'] = 64;
					$i++;
				}				
			}
			if(is_array_empty($listPonGpon)){
				usort($listPonGpon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data['pon'] = $listPonGpon;
			}
		}
		return (is_array_empty($data) ? $data : null);
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Multi('switch_pon','*',['oltid' => $this->id,'sfpid' => $dataPort['sfpid']]);
		if(!count($row))
			$this->db->SQLinsert('switch_pon',['support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'added' => $this->now]);
		if(!empty($dataPort['sfpid']) && !empty($dataPort['sort'])){		
			$this->db->SQLupdate('onus',['portolt' => $dataPort['sfpid']],['olt' => $this->id,'zte_idport' => $dataPort['sfpid']]);
		}
		$allonu = $this->db->Multi('onus','*',['olt' => $this->id,'zte_idport' => $dataPort['sfpid']]);
		if(!empty($dataPort['sfpid'])){
			$SQLportset['count'] = count($allonu);
			$this->db->SQLupdate('switch_pon',$SQLportset,['sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
		}
	}
    protected function savePortSwitch($data) {
		if(!empty($data['id'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(!empty($row['id'])){
				
			}else{
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => $data['operstatus'],'added' => $this->now]);
			}
		}
	}
	public function tempUpdateSignalCheck(){	
		global $db;

	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $config;
		$savehistor = false;
		if(isset($this->temp_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])&& !empty($this->temp_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])){
			$onu = $this->temp_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']];
		}else{
			$onu = $this->db->Simple("SELECT * FROM onus WHERE olt = '{$this->id}' AND zte_idport = '{$dataOnu['keyport']}' AND keyonu = '{$dataOnu['keyonu']}' LIMIT 1");
		}
		if (!empty($onu['idonu'])) {
			if (!empty($dataOnu['rx'])) {
				$rxValue = $this->clear_rx($dataOnu['rx']);
				if($rxValue){
					$this->db->query("UPDATE onus SET rx = '{$rxValue}' WHERE idonu  = {$onu['idonu']}");
				}
			}
			if ($config['logsignal'] == 'on') {
				if (!empty($rxValue)) {
					$savehistor = SignalMonitor($onu['status'], $rxValue, $onu['rx'], $onu['idonu'], $onu);
				}
			} else {
				$savehistor = true;
			}
			if (!empty($config['onugraph']) && $config['onugraph'] == 'on' && $savehistor && !empty($rxValue)) {
				$this->db->query("INSERT INTO historysignal (`device`, `onu`, `signal`, `datetime`) VALUES ('{$this->id}','{$onu['idonu']}', '{$rxValue}', '{$this->now}')");
			}
		}
	}	
	public function tempSaveSignalSaveOnuEpon($dataOnu){	
		global $config;
		if(empty($this->cache_onu_epon[$dataOnu['keyport']][$dataOnu['keyonu']])){
			$onu = $this->db->Simple("SELECT status,rx,idonu,inface,mac,sn,changerx,olt FROM onus WHERE olt = '{$this->id}' AND keyonu = '{$dataOnu['keyonu']}' LIMIT 1");
		}else{
			$onu = $this->cache_onu_epon[$dataOnu['keyport']][$dataOnu['keyonu']];
		}
		if (!empty($onu['idonu'])) {
			$rx = $dataOnu['rx'] ?? null;
			if (!empty($rx)) {
				$rxValue = $this->clear_rx($rx);
				if($rxValue){
					$this->db->query("UPDATE onus SET rx = '{$rxValue}' WHERE idonu  = {$onu['idonu']}");
				}
			}
			if ($config['logsignal'] == 'on') {
				if (!empty($rxValue)) {
					$savehistor = SignalMonitor($onu['status'], $rxValue, $onu['rx'], $onu['idonu'], $onu);
				}
			} else {
				$savehistor = true;
			}
			if (!empty($config['onugraph']) && $config['onugraph'] == 'on' && $savehistor && !empty($rxValue)) {
				$this->db->query("INSERT INTO historysignal (`device`, `onu`, `signal`, `datetime`) VALUES ('{$this->id}','{$onu['idonu']}', '{$rxValue}', '{$this->now}')");
			}
		}
	}	
	public function portstatus($value){
		if(preg_match('/1/i',$value) || preg_match('/4/i',$value)){
			return 'up';	
		}else{
			return 'down';	
		}
	}
	public function clearData($value){
		$value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|["\s]/', '', $value);
		return trim($value);
	}
	public function clear_rx($value){
		if(preg_match('/214748/i',$value)) {
			$value = 0; 
		}else{
			$value = str_replace('"', '',$value);
			$value = trim($value);
			$value = str_replace('N/A',0,$value);
			$value = $value/100;
			$value = sprintf('%.2f',$value);
			return str_replace('0.00',0,$value);
		}
	}
	public function tempSaveOnuGpon($dataOnu){	
		$sqlset = array();
		$now = $this->now;
		$dataOnu['dist'] = (isset($dataOnu['dist']) ? str_replace('-1', 0,$dataOnu['dist']) : '');
		$dataOnu['status'] = (!empty($dataOnu['status']) ? ($dataOnu['status']==1 ? 1 : 2 ) : (!empty($dataOnu['dist']) ? 1 : 2));
		if(is_numeric($dataOnu['keyport'])){
			$onucard = $this->array_huawei_ifindex($dataOnu['keyport']);
			if(is_array_empty($onucard)){
				$sqlset['sw_shelf'] = $onucard['olt'];
				$sqlset['sw_slot'] = $onucard['slot'];
			}
		}
		if (
			is_numeric($dataOnu['keyport']) &&
			is_numeric($dataOnu['keyonu']) &&
			!empty($dataOnu['sn'])
		) {
			$keyPort = (int)$dataOnu['keyport'];
			$keyOnu = (int)$dataOnu['keyonu'];
			$temp = $this->temp_onu_gpon[$keyPort][$keyOnu] ?? null;
			if (!empty($temp['idonu'])) {
				$sqlset = [];
				$idOnu = $temp['idonu'];
				if ($dataOnu['status'] == 1 && $temp['status'] == 2) {
					$sqlset['online'] = $now;
					$log_status = 2;
				} elseif ($dataOnu['status'] == 2 && $temp['status'] == 1) {
					$sqlset['offline'] = $now;
					$log_status = 1;
				}
				$sqlset = array_merge($sqlset, [
					'status' => $dataOnu['status'],	'type' => $dataOnu['pon'],
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : $temp['name']),
					'sn' => (!empty($dataOnu['sn']) ? $dataOnu['sn'] : $temp['sn']),
				]);
				if (!empty($dataOnu['dist'])) {
					$sqlset['dist'] = $dataOnu['dist'];
				}
				if (!empty($dataOnu['inface'])) {
					$sqlset['inface'] = $dataOnu['inface'];
				}
				if (!empty($dataOnu['reason'])) {
					$sqlset['reason'] = reasonGponHuawei($dataOnu['reason']);
				}
				if (!empty($dataOnu['keyport'])) {
					$sqlset['zte_idport'] = $dataOnu['keyport'];
				}
				if (!empty($dataOnu['portolt'])) {
					$sqlset['portolt'] = $dataOnu['portolt'];
				}
				$changes = [];
				foreach ($sqlset as $key => $value) {
					if (!isset($temp[$key]) || $temp[$key] != $value) {
						$changes[$key] = $value;
					}
				}
				if (!empty($changes)) {
					$changes['updates'] = $now;
					$setClause = implode(', ', array_map(function($key, $value) {
						return "$key = '$value'";
					}, array_keys($changes), $changes));
					$sql = "UPDATE onus SET $setClause WHERE idonu = '$idOnu'";
					$this->db->query($sql);
				}
			} else{			
				$sqlset['olt'] = $dataOnu['id'];
				$sqlset['added'] = $now;
				$sqlset[($dataOnu['status']==1?'online':'offline')] = $now;
				$sqlset['updates'] = $now;
				$sqlset['keyonu'] = $dataOnu['keyonu'];
				$sqlset['status'] = $dataOnu['status'];
				$sqlset['sn'] = $dataOnu['sn'];
				if(!empty($dataOnu['name']))
					$sqlset['name'] = $dataOnu['name'];				
				if(!empty($dataOnu['reason']))
					$sqlset['reason'] = reasonGponHuawei($dataOnu['reason']);				
				if(!empty($dataOnu['rx']))
					$sqlset['rx'] = $dataOnu['rx'];
				$sqlset['inface'] = $dataOnu['inface'];
				if(!empty($dataOnu['keyport']))
					$sqlset['zte_idport'] = $dataOnu['keyport'];
				if(!empty($dataOnu['keyport']))
					$sqlset['portolt'] = $dataOnu['keyport'];
				$sqlset['type'] = $dataOnu['pon'];
				$sqlset['dist'] = (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0);				
				$this->db->SQLinsert('onus',$sqlset);
				$idonu = $this->db->getInsertId();
				$this->logger->init(['log'=>'onu','type'=>'addonu','descr'=>$dataOnu['inface'].' '.(!empty($dataOnu['sn'])?$dataOnu['sn']:'---'),'deviceid'=>$dataOnu['id'],'onuid'=>$idonu,'who'=>'cron']);
			}
		}
	}	
	public function tempSaveOnuEpon($dataOnu){
		if(!empty($dataOnu['dist'])){
			$dataOnu['dist'] = str_replace('-1', 0, $dataOnu['dist']);
		}else{
			$dataOnu['dist'] = 0;
		}
		// EPON:STATUS 1 -offline 2 - online
		$dataOnu['status'] = (!empty($dataOnu['status']) ? ($dataOnu['status']==1 ? 2 : 1 ) : (!empty($dataOnu['dist']) ? 1 : 2));
		if(is_numeric($dataOnu['keyport'])){
			$onucard = $this->array_huawei_ifindex($dataOnu['keyport']);
			if(is_array_empty($onucard)){
				$SQLset['sw_shelf'] = $onucard['olt'];
				$SQLinsert['sw_shelf'] = $onucard['olt'];
				$SQLset['sw_slot'] = $onucard['slot'];
				$SQLinsert['sw_slot'] = $onucard['slot'];
				$SQLset['portolt'] = $onucard['slot'];
				$SQLinsert['portolt'] = $onucard['slot'];
			}
		}
		if(is_numeric($dataOnu['keyonu'])){
			$arr = $this->db->Simple("SELECT * FROM onus WHERE olt = '{$this->id}' AND zte_idport = '{$dataOnu['keyport']}' AND keyonu = '{$dataOnu['keyonu']}' LIMIT 1"); 
			$this->cache_onu_epon[$dataOnu['keyport']][$dataOnu['keyonu']] = $arr;
			if(!empty($arr['idonu'])){
				$SQLset['updates'] = $this->now;
				if($dataOnu['status']==1 && $arr['status']==2){
					$SQLset['online'] = $this->now;
				}elseif($dataOnu['status']==2 && $arr['status']==1){
					$SQLset['offline'] = $this->now;
				}else{
					
				}
				$SQLset['status'] = $dataOnu['status'];
				$SQLset['type'] = $dataOnu['pon'];
				if(!empty($dataOnu['dist']))
					$SQLset['dist'] = $dataOnu['dist'];				
				if(!empty($dataOnu['name']))
					$SQLset['name'] = $dataOnu['name'];
				if(!empty($dataOnu['mac']))
					$SQLset['mac'] = $dataOnu['mac'];
				if(!empty($dataOnu['inface']))
					$SQLset['inface'] = $dataOnu['inface'];				
				if(!empty($dataOnu['reason']))
					$SQLset['reason'] = reasonGponHuawei($dataOnu['reason']);					
				if(!empty($dataOnu['rx']))
					$SQLset['rx'] = $dataOnu['rx'];	
				$SQLset['zte_idport'] = $dataOnu['keyport'];
				if(empty($dataOnu['portolt']))
					$SQLset['portolt'] = $dataOnu['keyport'];
				$this->db->SQLupdate('onus',$SQLset,['idonu' => $arr['idonu']]);
			}else{			
				$SQLinsert['olt'] = $dataOnu['id'];
				$SQLinsert['added'] = $this->now;
				$SQLinsert[($dataOnu['status']==1?'online':'offline')] = $this->now;
				$SQLinsert['updates'] = $this->now;
				$SQLinsert['keyonu'] = $dataOnu['keyonu'];
				$SQLinsert['status'] = $dataOnu['status'];
				if(!empty($dataOnu['mac']))
					$SQLinsert['mac'] = $dataOnu['mac'];					
				if(!empty($dataOnu['name']))
					$SQLinsert['name'] = $dataOnu['name'];				
				if(!empty($dataOnu['reason']))
					$SQLinsert['reason'] = reasonGponHuawei($dataOnu['reason']);				
				if(!empty($dataOnu['rx']))
					$SQLinsert['rx'] = $dataOnu['rx'];
				$SQLinsert['inface'] = $dataOnu['inface'];
				$SQLinsert['zte_idport'] = $dataOnu['keyport'];
				$SQLinsert['cron'] = 1;
				$SQLinsert['type'] = $dataOnu['pon'];
				$SQLinsert['dist'] = (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0);
				$this->db->SQLinsert('onus',$SQLinsert);
				$idonu = $this->db->getInsertId();
				$logs = ['log'=>'onu','type'=>'addonu','descr'=>$dataOnu['inface'].' '.(!empty($dataOnu['mac'])?$dataOnu['mac']:''),'deviceid'=>$dataOnu['id'],'onuid'=>$idonu,'who'=>'cron'];
				$this->logger->init($logs);
			}
		}
	}
	public function StatisticOLT(){	
		$switch_pon = $this->db->SimpleWhile("SELECT * FROM switch_pon WHERE oltid = '{$this->id}'");
		
		if(!empty($getPort)){
			foreach($getPort as $port){
				$getonu = $this->db->Multi('onus','idonu,status',['olt' => $this->id,'zte_idport'=>$port['sfpid']]);
				$getstat = $this->db->Multi('onus','idonu,status',['status' => 1,'olt' => $this->id,'zte_idport'=>$port['sfpid']]);
				$arrayPort[$port['id']] = [
					'port' => $port['pon'],
					'count' => count($getonu),
					'online' => count($getstat),
					'offline' => count($getonu) - count($getstat)
				];
			}
		}
		if(isset($arrayPort)){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getListOnuOnline(){	
		$sqlonus = $this->db->SimpleWhile("SELECT keyonu,zte_idport,idonu,type FROM onus WHERE olt = '{$this->id}' AND status = '1'");
		$array = array();
		if(is_array_empty($sqlonus)){
			foreach($sqlonus as $key => $value){
				if(is_numeric($value['keyonu']) && !empty($value['type']))
					$array[$key] = array('id'=>$this->id,'keyport'=>$value['zte_idport'],'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx');
			}
		}
		return $array;
	}	
	public function getOnuPoller($value){
		$array = array();
		if (is_numeric($value['keyonu']) && !empty($value['type'])) {
			$types = ($value['type'] === 'gpon') ? $this->pollergpon : $this->pollerepon;
			$array = [
				'id' => $this->id,
				'keyport' => $value['zte_idport'],
				'keyonu' => $value['keyonu'],
				'pon' => $value['type'],
				'types' => $types,
				'do' => 'onu'
			];			
			return $array;
		}
		return null;
	}	
	public function getOnuRxPoller($value){
		$array = array();
		if (is_numeric($value['keyonu']) && !empty($value['type'])) {
			$array = [
				'id'=>$this->id,
				'idonu' => $value['idonu'],
				'keyport' => $value['zte_idport'],
				'keyonu' => $value['keyonu'],
				'pon' => $value['type'],
				'types' => 'rxolt',
				'do' => 'onu'
			];			
			return $array;
		}
		return $array ?: null;
	}
	public function SignalRxOLT($temp) {
		if (isset($temp) && $temp !== false) {
			if (strlen((string)$temp) > 6) {
				return 0;
			}
			return number_format(-(10000 - floatval($temp)) / 100, 2);
		}
		return false;
	}	
	public function tempSaveSignalSaveRxOnuGpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->SignalRxOLT($dataOnu['rxolt']);
			if($rxolt!=false){
				$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
				$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
			}
		}
	}
	public function tempSaveSignalSaveRxOnuEpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->SignalRxOLT($dataOnu['rxolt']);
			if($rxolt!=false){
				$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
				$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
			}		
		}
	}
}
?>