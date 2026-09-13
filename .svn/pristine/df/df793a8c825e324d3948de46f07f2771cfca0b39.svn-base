<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class CDATA_1216s { 
    private $logger;
    private $db;
	protected $snmp;
	protected $id;
	protected $ip;
	protected $oidid;
	protected $community;
	protected $deviceoid;
    private $indexdevice;
    private $cache_onu = array();
    private $now;
    private $primary = 'dist,name,status,reason';
	private $poller = 'status,name,rx';
    private $configapionugpon = 'dist,name,mac,status,model,vendor,reason';
    private $configapionugponget = 'dist,name,mac,status,rx,tx,eth,model,vendor,reason';

	public function Support($check){
		return match ($check){
			'port','onu','saveonu','api','poller','rxolt' => true,	default => false,
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
	public function portnametext1216($data){
		$port = floor($data/256)%256-10;
		$numonu = ($data%64);
		$index = '0/'.$port.':'.$numonu;
		return str_replace(':0',':64',$index);
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
	public function MacHuawei($type) {
		if(empty($type)){
			return false;
		}
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
			$re_mac = str_replace('"', '',$type);
			$re_mac = trim($re_mac);
			$onu = bin2hex($re_mac);
			return preg_replace('/(.{2})/','\1:',$onu,5);
		}
	}
	public function Load(){	
		$epon = [
			'oid' => $this->deviceoid[$this->id]['onu']['listname']['epon']['oid'],
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$eponlist = pmon_walk($epon);
		$result = [];
		if(isset($eponlist) && count($eponlist)>0){
			foreach($eponlist as $io => $eachsig) {
				if (!empty($eachsig['result'])) {
					$mac = $this->MacHuawei($eachsig['result']);
					if($mac){
						$result[$io] = array(
							'do' => 'onu',
							'id'=>$this->id,
							'mac'=>$mac,
							'pon'=>'epon',
							'inface'=>$this->portnametext1216(trim($io)),
							'types'=>$this->primary,
							'keyonu'=> trim($io)
						);
					}
				}	
			}
		}
		if(isset($result) && count($result)>0){
			checkerONUCdata12($result,$this->id);
		}else{
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'empty_snmp_walk'.' '.$this->deviceoid[$this->id]['onu']['listname']['epon']['oid'],'deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result) ? $result : null);
	}
	public function ConfigApiOnu($data){
		$array = array('do' => 'onu','types' => $this->configapionugpon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
		return $array;
	}	
	public function ConfigApiOnuGet($data){
		$array = array('do' => 'onu','types' => $this->configapionugponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
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
		$sqlset = [];
		if(is_array($getData)){
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
				$sqlset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$sqlset['status'] = 2;
			}
			$sqlset['cron'] = 1;
			if(is_array($sqlset))
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $ont['idonu']]);
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			if(!empty($getData['eth']))
				$result['wan'] = $getData['eth'];			
			if(!empty($getData['name']))	
				$result['name'] = $getData['name'];			
			if(!empty($getData['model']))	
				$result['model'] = $getData['model'];
			if(!empty($getData['reason']))	
				$result['reason'] = $getData['reason'];			
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
			return $result;
		}
	}	
	public function preparedataCDATA($dataApi,$type){
		$data = $this->clearData($dataApi);
		switch($type){
			case 'status':
				if(isset($data)){
					$result = ($data==1 ? 1 : 2);
				}else{
					$result = 2;
				}
			break;			
			case 'dist':
				if(isset($data)){
					$result = (int)$data;
				}else{
					$result = 0;
				}
			break;
			case 'rx':
			case 'tx':
				if($data){
					$result = $this->Signal($data);
				}else{
					$result = 0;
				}
			break;
			case 'mac':
			case 'vendor':
			case 'name':
			case 'model':
			case 'reason':
				$result = (isset($data)?$data:'');
			break;				
			case 'eth':
				$result = (isset($data)?($data == 1) ? 'up' : 'down':'down');		
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
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['llid'],'nameport' => $data['name'],'descrport' => $data['descr'],'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
			}
		}
	}
	public function Port(){
		$data = array();		
		$OIdPortEth = $this->deviceoid[$this->id]['global']['listporteth']['port']['oid'];
		$OIdPortPon = $this->deviceoid[$this->id]['global']['listportpon']['port']['oid'];
		if(isset($OIdPortEth) && isset($OIdPortPon)){
			$ListPortTempEth = $this->snmp->walk($this->ip,$this->community,$OIdPortEth,true);
			$ListPortTempPon = $this->snmp->walk($this->ip,$this->community,$OIdPortPon,true);
		}
		if(isset($ListPortTempEth)){
			$EponListPort = str_replace('.'.$OIdPortEth.'.','',$ListPortTempEth);
			$IndexGePort = explodeRows($EponListPort);	
			if(is_array($IndexGePort)){
				$listPortge = array();
				$iport = 1;
				foreach ($IndexGePort as $idPort => $ValuePort) {				
					$infPort = explode('=', $ValuePort);
					if(!empty($infPort[0]) && !empty($infPort[0])){
						$listPortge[$idPort] = array('sort' =>$iport,'llid' =>trim($infPort[0]),'typeport' => 'sfp','name' => $this->clearData($infPort[1]));
						$iport++;
					}				
				}
				$data['port'] = $listPortge;
			}
		}
		if(isset($ListPortTempPon)){
			$EponListPort = str_replace('.'.$OIdPortPon.'.','',$ListPortTempPon);
			$IndexEponPort = explodeRows($EponListPort);	
			if(is_array($IndexEponPort)){
				$listPort = array();
				$ipon = 1;
				foreach ($IndexEponPort as $idPort => $ValuePort) {				
					$infPort = explode('=', $ValuePort);
					if(!empty($infPort[0]) && !empty($infPort[0])){
						$dataIndexPort = $this->clearResult($infPort[1]);
						$listPort[$idPort] = array(
							'cardcount' =>64,'sort' =>$ipon,
							'idportolt' =>$ipon,
							'sfpid' =>trim($infPort[0]),
							'llid' =>trim($infPort[0]),'typeport' => 'epon',
							'name' => 'EPON 0/'.$ipon,
							'descr' => $this->clearData($infPort[1])
						);
						$ipon++;
					}				
				}
				$data['pon'] = $listPort;
			}
		}
		return $data;
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Fast('switch_pon','*',['oltid' => $this->id,'idportolt' => $dataPort['idportolt']]);
		if(!empty($row['id'])){
			$this->db->SQLupdate('onus',
				['portolt' => $dataPort['sfpid']],
				['olt' => $this->id,'zte_idport' => $dataPort['idportolt']]
			);
		}else{
			$this->db->SQLinsert('switch_pon',['support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'idportolt' => $dataPort['idportolt'],'added' => $this->now]);
		}
		$allonu = $this->db->Multi('onus','*',['olt' => $this->id,'zte_idport' => $dataPort['idportolt']]);
		if(!empty($dataPort['sfpid']) && count($allonu)>0){
			$this->db->SQLupdate('switch_pon',['count' =>count($allonu)],['idportolt' =>$dataPort['idportolt'],'sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
		}
	}
	public function tempSaveSignalSaveOnuEpon($dataOnu){	
		global $config;
		$savehistor = $savehistor ?? null;
		if(isset($this->cache_onu[$dataOnu['keyonu']]) && !empty($this->cache_onu[$dataOnu['keyonu']])){
			$onu = $this->cache_onu[$dataOnu['keyonu']];
		} else {
			$onu = $this->db->Fast('onus', 'status,rx,idonu,inface,mac,sn,changerx,olt', ['olt' => $this->id, 'keyonu' => $dataOnu['keyonu']]);
		}
		if (!empty($onu['idonu'])) {
			$rx = $dataOnu['rx'] ?? null;
			if (!empty($rx)) {
				$rxValue = $this->Signal($rx);
				if ($rxValue) {
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
    protected function savePortSwitch($data) {
		if(!empty($data['llid'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['llid']]);
			if(!empty($row['id'])){
				
			}else{
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['llid'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
			}
		}
	}   
	public function clearData($value){
		if(empty($value)){
			return '';
		}
		$value = str_replace('INTEGER:', '',$value);
		$value = str_replace('Hex-STRING:', '',$value);
		$value = str_replace('STRING:', '',$value);
		$value = str_replace('Gauge32:', '',$value);
		$value = str_replace('"', '',$value);
		$value = str_replace(' ', '',$value);
		$value = trim($value);	
		return $value;
	}
	public function Signal($value){
		if(empty($value)){
			return 0;
		}
		if(preg_match('/6553/i',$value)) {
			$value = 0; 
		}else{
			$value = str_replace('"', '',$value);
			$value = trim($value);
			$value = str_replace('N/A',0,$value);
			$value = sprintf('%.2f',$value);
			return str_replace('0.00',0,$value);
		}
	}
	public function RxOltSignal($value){
		if(empty($value)){
			return 0;
		}
		if(preg_match('/6553/i',$value)) {
			$value = 0; 
		}else{
			$value = str_replace('"', '',$value);
			$value = trim($value)/100;
			$value = str_replace('N/A',0,$value);
			$value = sprintf('%.2f',$value);
			return str_replace('0.00',0,$value);
		}
	}
	public function tempUpdateSignalCheck(){	

	}
	public function tempSaveOnuEpon($dataOnu){	
		$sqlset = [];
		if(!empty($dataOnu['inface'])){
			preg_match('/0\/(\d+):(\d+)/i',$dataOnu['inface'],$dataMatch);
			$indexPortOlt = $dataMatch[1];
		}
		$dataOnu['status'] = (!empty($dataOnu['status']) ? $dataOnu['status'] : (!empty($dataOnu['dist']) ? 1 : 2));
		if(!empty($dataOnu['keyonu']) && $indexPortOlt){
			$arr = $this->db->Fast('onus','*',['keyonu' =>$dataOnu['keyonu'],'olt' => $this->id]); 
			if(!empty($arr['idonu'])){
				$this->cache_onu[$dataOnu['keyonu']] = $arr;
				if($dataOnu['status']==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($dataOnu['status']==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}
				$sqlset['updates'] = $this->now;
				$sqlset['cron'] = 1;
				$sqlset['status'] = $dataOnu['status'];
				$sqlset['type'] = $dataOnu['pon'];
				if(!empty($dataOnu['dist']))
					$sqlset['dist'] = $dataOnu['dist'];				
				if(!empty($dataOnu['name']))
					$sqlset['name'] = $dataOnu['name'];
				if(!empty($dataOnu['mac']))
					$sqlset['mac'] = $dataOnu['mac'];
				if(!empty($dataOnu['inface']))
					$sqlset['inface'] = $dataOnu['inface'];				
				if(!empty($dataOnu['reason']))
					$sqlset['reason'] = $dataOnu['reason'];	
				if(!empty($arr['portolt'])){
					$sqlset['portolt'] = $arr['portolt'];
				}else{					
					$sqlset['portolt'] = $indexPortOlt;
				}
				if(isset($indexPortOlt)){
					$sqlset['zte_idport'] = $indexPortOlt;
				}
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{				
				$sqlset = array('olt' => $this->id,'updates' => $this->now, 'added' => $this->now, ($dataOnu['status']==1?'online':'offline') => $this->now, 'rating' => 1,'keyonu' => $dataOnu['keyonu'],'status' => $dataOnu['status'],'zte_idport' => $indexPortOlt,'mac' => $dataOnu['mac'],'inface' => $dataOnu['inface'],'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),'reason' => (!empty($dataOnu['reason']) ? $dataOnu['reason'] : ''),'type' => $dataOnu['pon'],'cron' => 1,'portolt' => $indexPortOlt);
				$this->db->SQLinsert('onus',$sqlset);
			}
		}
	}
	public function Status($status){
		if($status==1){
			return 1;
		}else{
			return 2;
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
					'offline' => count($getONUstatusPort) - count($getONUstatusPortOn),
				];
			}
		}
		if(is_array_empty($arrayPort)){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getOnuPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type'])){
			$array = [
				'id'=>$this->id,
				'keyonu'=>$value['keyonu'],
				'pon'=>$value['type'],
				'do'=>'onu',
				'types'=>$this->poller
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
	public function getListOnuOnline(){	
		$sqlList = $this->db->Multi('onus','keyonu,type',['olt' => $this->id, 'status' => 1]);
		if(is_array_empty($sqlList)){
			foreach($sqlList as $key => $value){
				if(!empty($value['keyonu']) && !empty($value['type']))
					$array[$key] = array('id'=>$this->id,'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx');
			}
		}
		return $array;
	}
	public function tempSaveSignalSaveRxOnuEpon($dataOnu){	
		global $config;		
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->RxOltSignal($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}
}
?>