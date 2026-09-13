<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class CDATA_1108 { 
    private $logger;
    private $db;
	protected $snmp;
	protected $id;
	protected $ip;
	protected $oidid;
	protected $community;
	protected $deviceoid;
    private $indexdevice;
    private $now = null;
    private $primary = 'dist,status,reason,name';
    private $configapionugpon = 'dist,status,model,vendor';
    private $configapionugponget = 'rx,dist,name,status,model,vendor';
    private $filter = true;
    private $filters = ['/"/','/Hex-/i','/OID: /i','/STRING: /i','/Gauge32: /','/INTEGER: /i','/Counter32: /i','/SNMPv2-SMI::enterprises\./i','/iso\.3\.6\.1\.4\.1\./i'];
	public function Support($check){
		return match ($check){
			'port', 'onu', 'saveonu', 'api', 'fileonu' => true,	default => false,
		};
	}
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
		}
	}  
	public function onumac($data) {
		if($data){
			$data = preg_replace('~^.*?( = )~i','',$data);
			if (strlen($data) === 18){
				$data = strtolower($data);
				$data = str_replace(' ','',$data);
				return preg_replace('/(.{2})/','\1:',$data,5);
			}else{
				$maconu = bin2hex($data);
				return preg_replace('/(.{2})/', '\1:', $maconu, 5);
			}
		}else return '';
	}
	public function Load(){
		$port = [
			'oid' => $this->deviceoid[$this->id]['onu']['listmac']['epon']['oid'],
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$result_epon = [];
		$onulist = pmon_walk($port);
		$init_epon = 1;
		if(is_array_empty($onulist)){
			foreach($onulist as $inface => $eachsig) {
				if(isset($eachsig['result'])) {
					preg_match('/1.(\d+).(\d+)/i',$inface,$datamatch);
					$result_epon[$init_epon] = [
						'do' => 'onu','id'=>$this->id,'pon'=>'epon',
						'mac'=>ClearDataMac($eachsig['result']),					
						'inface'=>'0/'.$datamatch[1].':'.$datamatch[2],
						'checker'=>md5('0/'.$datamatch[1].':'.$datamatch[2]),
						'types'=>$this->primary,
						'keyonu'=> trim($datamatch[2]),
						'keyport'=> trim($datamatch[1])
					];
					$init_epon ++;
				}
			}
		}
		if(is_array_empty($result_epon)){
			checkerONUCdata($result_epon,$this->id);
		}else{
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'empty_snmp_walk'.' '.$this->deviceoid[$this->id]['onu']['listmac']['epon']['oid'],'deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result_epon) ? $result_epon : null);
	}
	public function ConfigApiOnu($data){
		$array = array('do' => 'onu','types' => $this->configapionugpon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'keyport' => $data['zte_idport'],'id' => $this->id);
		return $array;
	}	
	public function ConfigApiOnuGet($data){
		$array = array('do' => 'onu','types' => $this->configapionugponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'keyport' => $data['zte_idport'],'id' => $this->id);
		return $array;
	}
	public function Onu($dataPort,$dataOnu){
		$res = array(); 
		if(is_array_empty($dataPort)){
			foreach($dataPort as $type => $value) {
				$res[$type] = $this->preparedataCDATA($value,$type);
			}
		}
		if(!$dataPort && !$res){
			$array_separated = explode(',',$this->configapionugpon);
			foreach($array_separated as $type) {
				if(isset($dataOnu[$type]) && !empty($dataOnu[$type])){
					$res[$type] = $dataOnu[$type];
				}
			}
		}
		if(is_array_empty($res)){
			$result = $this->updateonu($dataOnu,$res);
		}else{
			$result = false;
		}
		return $result;
	}
	public function updateonu($ont,$getData){
		global $config;
		$sqlset = array();
		$result = array();
		if(is_array_empty($getData)){
			if(!empty($getData['tx'])){
				$sqlset['tx'] = $getData['tx'];
				$result['tx'] = $getData['tx'];
			}
			if(!empty($getData['rx'])){
				$sqlset['rx'] = $getData['rx'];
				$result['rx'] = $getData['rx'];
			}
			if(!empty($getData['name'])) 
				$sqlset['name'] = $getData['name'];			
			if(!empty($getData['mac'])) 
				$sqlset['mac'] = $getData['mac'];			
			if(!empty($getData['vendor'])) 
				$sqlset['vendor'] = $getData['vendor'];
			if(!empty($getData['dist'])) 
				$sqlset['dist'] = $getData['dist'];
			if($ont['status']==2 && $getData['status']==1){
				if(!empty($getData['timeaut']))
					$sqlset['online'] = $this->now;
				if(!empty($getData['offline']))
					$sqlset['online'] = $getData['offline'];
				$sqlset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$sqlset['offline'] = $this->now;
				$sqlset['status'] = 2;
			}
			if(is_array_empty($sqlset))
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $ont['idonu']]);
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			$result['wan'] = (isset($getData['eth']) ? $getData['eth'] :'down');
			if(!empty($getData['name']))	
				$result['name'] = $this->clearData($getData['name']);			
			if(!empty($getData['model']))	
				$result['model'] = $getData['model'];
			if(!empty($getData['reason']))	
				$result['reason'] = $getData['reason'];			
			if(!empty($getData['vendor']))	
				$result['vendor'] = $getData['vendor'];			
			if(!empty($getData['temp']))	
				$result['temp'] = $getData['temp'];
			if(!empty($getData['dist'])) 
				$result['dist'] = $getData['dist'];
			if(!empty($ont['lastrx']))
				$result['lastrx'] = $ont['lastrx'];
			if(!empty($getData['rx'])) 
				$result['rx'] = $getData['rx'];
			if(!empty($getData['tx'])) 
				$result['tx'] = $getData['tx'];
			return $result;
		}
	}	
	public function preparedataCDATA($dataApi,$type){
		$data = $this->clearData($dataApi);
		switch($type){
			case 'status':
				$result = (isset($data) && $data==3 ? 1 : 2);
			break;			
			case 'dist':
				if(isset($data)){
					$result = (int)$data;
				}else{
					$result = 0;
				}
			break;
			case 'tx':
			case 'rx':
				if($data){
					$result = $this->clear_rx($data);
				}else{
					$result = 0;
				}
			break;
			case 'mac':
			case 'vendor':
			case 'name':
			case 'temp':
			case 'model':
			case 'reason':
				$result = $data ?? '';
			break;				
			case 'eth':
				$result = (isset($data) && $data==1?'up':'down');				
			break;			
		}		
		return $result;
	}
	public function clearResult($value){
		return str_replace('/','',trim(str_replace('"','',$value)));
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
				$this->savePonPortSwitch($value);
			}
			$this->db->SQLupdate('switch',['updates_port' => $this->now],['id' => $this->id]);
		}
	}
	protected function savePonPortSwitch($data){
		if(!empty($data['llid'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['llid']]);
			if(!empty($row['id'])){
				
			}else{
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['llid'],'nameport' => $data['name'],'descrport' => (!empty($data['descr'])?$data['descr']:''),'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
			}
		}
	}
	public function Port(){
		$port = [
			'oid' => $this->deviceoid[$this->id]['global']['listport']['port']['oid'],
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$listport = [];
		$listPon = [];
		$portlist = pmon_walk($port);
		if(is_array_empty($portlist)){
			foreach($portlist as $inface => $eachsig) {
				if(!empty($eachsig['result'])){
					$getinf = clearData1108($eachsig['result']);
					$listport[$inface] = [
						'id' =>trim($inface),
						'typeport' => getTypePort($getinf),
						'name' => $getinf
					];
				}				
			}
			$data['ports'] = $listport;
		}
		if(is_array_empty($data['ports'])){
			foreach($data['ports'] as $idPonport => $valuePon){
				if(preg_match('/pon/i',$valuePon['name'])) {
					preg_match('/0\/(\d+)/',$valuePon['name'],$mat);
						$listPon[$idPonport] = [
							'name' => 'EPON 0/' . $mat[1],
							'sort' => $mat[1],
							'typeport' => 'epon',
							'sfpid' => $valuePon['id'],
							'llid' => $valuePon['id'],
							'idportolt' => $valuePon['id'],
							'cardcount' => 64
						];
				}				
				if(preg_match('/sfp/i',$valuePon['typeport'])) {
					preg_match('/0\/(\d+)/',$valuePon['name'],$mat);
						$listPortget[$idPonport] = [
							'name' => 'GE 0/' . $mat[1],
							'sort' => $mat[1],
							'typeport' => 'ge',
							'llid' => $valuePon['id']
						];
				}
			}
			if(is_array_empty($listPon)){
				usort($listPon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data = [
					'pon' => $listPon,
					'port' => $listPortget
				];
			}
		}
		return $data;
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Fast('switch_pon','*',['oltid' => $this->id,'sfpid' => $dataPort['sfpid']]);
		if(!empty($row['id'])){
			$this->db->SQLupdate('onus',['portolt' => $dataPort['sfpid']],['olt' => $this->id,'zte_idport' => $dataPort['sort']]);
		}else{
			$this->db->SQLinsert('switch_pon',['idportolt' => $dataPort['sort'],'support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'added' => $this->now]);
		}
		$allonu = $this->db->Multi('onus','*',['olt' => $this->id,'zte_idport' => $dataPort['sort']]);
		if(!empty($dataPort['sfpid']) && count($allonu))
			$this->db->SQLupdate('switch_pon',['count' =>count($allonu)],['idportolt' =>$dataPort['idportolt'],'sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
	}
	public function tempSaveSignalSaveOnuEpon($dataOnu){	
		global $config;
		$savehistor = $savehistor ?? null;
		$onu = $this->db->Fast('onus','status,rx,idonu,inface,mac,sn,changerx,olt',['olt' => $this->id,'keyonu' => $dataOnu['keyonu'],'zte_idport' => $dataOnu['keyport']]);
		if(!empty($onu['idonu'])){
			$rxValue = $rxValue ?? null;
			if(!empty($dataOnu['rx'])){
				$rxValue = $this->clear_rx($dataOnu['rx']);
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
    protected function savePortSwitch($data) {
		if(!empty($data['llid'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['llid']]);
			if(!empty($row['id'])){
				
			}else{
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['llid'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
			}
		}
	}    
    private function prepareResult(array $data): array {
        if($this->filter){
            $result = array_map(
                function($value) {
                    return preg_replace($this->filters, '', $value);
					},
				$data
			);
        }
        return ($this->filter && isset($result)) ? $result : $data;
    }
	public function clearData($value){
		$value = str_replace('INTEGER:', '',$value);
		$value = str_replace('Hex-STRING:', '',$value);
		$value = str_replace('STRING:', '',$value);
		$value = str_replace('Gauge32:', '',$value);
		$value = str_replace('00000000', '',$value);
		$value = str_replace('"', '',$value);
		$value = str_replace(' ', '',$value);
		$value = trim($value);	
		return $value;
	}
	public function check_signal($rx){
		if ($rx == 0 OR !$rx OR $rx == NULL) {
			return 0;
		} else {
			return sprintf("%.2f",(10 * log10($rx) - 40));  
		}
	}
	public function clear_rx($value){
		if(preg_match('/6553/i',$value)) {
			return 0; 
		}else{
			return $this->check_signal($value);
		}
	}
	public function tempUpdateSignalCheck(){	

	}	
	public function clearNameData($hexString) {
		if (preg_match('/0000/i', $hexString)) {
			$cleanHexString = strtoupper(preg_replace('/[^0-9A-F]/', '', $hexString));
			if (strlen($cleanHexString) % 2 !== 0) {
				$cleanHexString = '0' . $cleanHexString;
			}
			$tmp = hex2bin($cleanHexString);
			return $tmp;
		} elseif ($hexString !== false) {
			return $this->clearData($hexString);
		} else {
			return "";
		}
	}
	public function tempSaveOnuEpon($dataOnu = array()){	
		$sqlset = array();
		$statusONU = (!empty($dataOnu['status']) && $dataOnu['status']==3 ? 1 : 2);
		if(!empty($dataOnu['keyonu']) && !empty($dataOnu['keyport'])){
			$arr = $this->db->Fast('onus','*',['zte_idport' =>$dataOnu['keyport'],'keyonu' =>$dataOnu['keyonu'],'olt' => $dataOnu['id']]); 
			if(!empty($arr['idonu'])){
				if($statusONU==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($statusONU==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}else{
					
				}
				$sqlset['updates'] = $this->now;
				$sqlset['cron'] = 1;
				$sqlset['status'] = $statusONU;
				$sqlset['type'] = $dataOnu['pon'];
				if(!empty($dataOnu['dist']))
					$sqlset['dist'] = $dataOnu['dist'];				
				if(!empty($dataOnu['name']))
					$sqlset['name'] = $this->clearNameData($dataOnu['name']);
				if(!empty($dataOnu['mac']))
					$sqlset['mac'] = $dataOnu['mac'];
				if(!empty($dataOnu['inface']))
					$sqlset['inface'] = $dataOnu['inface'];				
				if(!empty($dataOnu['reason']))
					$sqlset['reason'] = $dataOnu['reason'];	
				if(!empty($dataOnu['keyport'])){
					$sqlset['portolt'] = $dataOnu['keyport'];
					$sqlset['zte_idport'] = $dataOnu['keyport'];
				}
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{				
				$sqlset = array('olt' => $dataOnu['id'],'updates' => $this->now,'added' => $this->now, ($statusONU==1?'online':'offline') => $this->now,'rating' => 1,'keyonu' => $dataOnu['keyonu'],'status' => $statusONU,'mac' => $dataOnu['mac'],'inface' => $dataOnu['inface'],'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),'name' => (!empty($dataOnu['name']) ? $this->clearData($dataOnu['name']) : ''),'reason' => (!empty($dataOnu['reason']) ? $dataOnu['reason'] : ''),'type' => $dataOnu['pon'],'cron' => 1,'zte_idport' => $dataOnu['keyport'],'portolt' => $dataOnu['keyport']);
				$this->db->SQLinsert('onus',$sqlset);
			}
		}
	}
	public function StatisticOLT(){	
		$arrayPort = array();
		$getPort = $this->db->Multi('switch_pon','*',['oltid' => $this->id]);
		if(is_array_empty($getPort)){
			foreach($getPort as $port){
				$getONUstatusPort = $this->db->Multi('onus','idonu,status',['olt' => $this->id,'portolt'=>$port['sfpid']]);
				$getONUstatusPortOn = $this->db->Multi('onus','idonu,status',['status' => 1,'olt' => $this->id,'portolt'=>$port['sfpid']]);
				$arrayPort[$port['id']] = [
					'port' => $port['pon'],
					'count' => count($getONUstatusPort),
					'online' => count($getONUstatusPortOn),
					'offline' => count($getONUstatusPort) - count($getONUstatusPortOn)
				];
			}
		}
		if(is_array_empty($arrayPort)){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getListOnuOnline(){	
		$array = array();
		$sqlList = $this->db->Multi('onus','keyonu,zte_idport,portolt,type',['olt' => $this->id, 'status' => 1]);
		if(is_array($sqlList)){
			foreach($sqlList as $key => $value){
				if(!empty($value['keyonu']) && !empty($value['type']))
					$array[$key] = array('id'=>$this->id,'keyonu'=>$value['keyonu'],'keyport'=>$value['zte_idport'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx');
			}
		}
		return $array ?: null;
	}
}
?>