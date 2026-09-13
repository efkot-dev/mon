<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class CDATA_1204 { 
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
    private $primary = 'dist,name,status,reason';
    private $configapionugpon = 'dist,name,mac,status,rx,tx,eth,model,vendor,reason';
    private $configapionugponget = 'dist,name,mac,status,rx,tx,eth,model,vendor,reason';
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
	public function portnametextCdata($data){
		$port = floor($data/256)%256-6;
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
	public function Load(){
		$listinface = $this->deviceoid[$this->id]['onu']['listname']['epon']['oid'];
		if(!empty($this->deviceoid[$this->id]['onu']['listname']['epon']['oid']))		
			$listonu = $this->snmp->walk($this->ip,$this->community,$listinface,true);
		if(isset($listonu)){
			$this->indexdevice = $listonu;
			$indexOnu = explodeRows(str_replace('.'.$listinface.'.','',$listonu));
			if(is_array_empty($indexOnu)){
				foreach($indexOnu as $io => $eachsig) {
					$line = explode('=', $eachsig);
					if(isset($line[0]) && isset($line[1])) {
						$TempName['name'] = $line[1];
						$result[$io] = array('do' => 'onu','id'=>$this->id,'mac'=>ClearDataMac($this->prepareResult($TempName)['name']),'pon'=>'epon','inface'=>$this->portnametextCdata(trim($line[0])),'types'=>$this->primary,'keyonu'=> trim($line[0]));
					}	
				}
			}
		}
		if(is_array_empty($result)){
			checkerONUBDcom($result,$this->id);
		}else{
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>$lang['emptysnmpwalk'].' '.$listinface,'deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result) ? $result : null);
	}
	public function ConfigApiOnu($data){
		return array('do' => 'onu','types' => $this->configapionugpon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
	}	
	public function ConfigApiOnuGet($data){
		return array('do' => 'onu','types' => $this->configapionugponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
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
				$res[$type] = $dataOnu[$type];
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
		global $db, $config, $PMonTables;
		if(is_array($getData)){
			if(!empty($getData['tx'])){
				$SQLset['tx'] = $getData['tx'];
				$result['tx'] = $getData['tx'];
			}
			if(!empty($getData['rx'])){
				$SQLset['rx'] = $getData['rx'];
				$result['rx'] = $getData['rx'];
			}
			if(!empty($getData['name'])) 
				$SQLset['name'] = $getData['name'];			
			if(!empty($getData['mac'])) 
				$SQLset['mac'] = $getData['mac'];			
			if(!empty($getData['vendor'])) 
				$SQLset['vendor'] = $getData['vendor'];
			if(!empty($getData['dist'])) 
				$SQLset['dist'] = $getData['dist'];
			if($ont['status']==2 && $getData['status']==1){
				if(!empty($getData['timeaut']))
					$SQLset['online'] = $this->now;
				if($getData['offline'])
					$SQLset['online'] = $getData['offline'];
				$SQLset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$SQLset['offline'] = $this->now;
				$SQLset['status'] = 2;
			}
			if(is_array_empty($SQLset))
				$this->db->SQLupdate($PMonTables['onus'],$SQLset,['idonu' => $ont['idonu']]);
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
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
				if($data){
					$result = $this->clear_rx($data);
				}else{
					$result = 0;
				}
			break;			
			case 'tx':
				if($data){
					$result = $this->clear_rx($data);
				}else{
					$result = 0;
				}
			break;
			case 'mac':
				if($dataApi)
					$result = $dataApi;
			break;
			case 'vendor':
				$result = $data;
			break;				
			case 'name':
				$result = $data;
			break;				
			case 'model':
				$result = $data;
			break;			
			case 'reason':
				$result = $data;
			break;				
			case 'eth':
				$result = ($data==1?'up':'down');				
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
			if(is_array_empty($IndexGePort)){
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
			if(is_array_empty($IndexEponPort)){
				$listPort = array();
				$ipon = 1;
				foreach ($IndexEponPort as $idPort => $ValuePort) {				
					$infPort = explode('=', $ValuePort);
					if(!empty($infPort[0]) && !empty($infPort[0])){
						$dataIndexPort = $this->clearResult($infPort[1]);
						$listPort[$idPort] = array('cardcount' =>64,'sort' =>$ipon,'idportolt' =>$ipon,'sfpid' =>trim($infPort[0]),'llid' =>trim($infPort[0]),'typeport' => 'epon','name' => 'EPON 0/'.$ipon,'descr' => $this->clearData($infPort[1]));
						$ipon++;
					}				
				}
				$data['pon'] = $listPort;
			}
		}
		return is_array_empty($data) ? $data : null;
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Fast('switch_pon','*',['oltid' => $this->id,'idportolt' => $dataPort['idportolt']]);
		if(!empty($row['id'])){
			$this->db->SQLupdate('onus',['portolt' => $dataPort['sfpid']],['olt' => $this->id,'zte_idport' => $dataPort['idportolt']]);
		}else{
			$this->db->SQLinsert('switch_pon',['support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'idportolt' => $dataPort['idportolt'],'added' => $this->now]);
		}
		$allonu = $this->db->Multi('onus','*',['olt' => $this->id,'zte_idport' => $dataPort['idportolt']]);
		if(!empty($dataPort['sfpid']) && count($allonu))
			$this->db->SQLupdate('switch_pon',['count' =>count($allonu)],['sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
	}
	public function tempSaveSignalSaveOnuEpon($dataOnu){	
		global $config;
		$savehistor = $savehistor ?? null;
		$onu = $this->db->Fast('onus','status,rx,idonu,inface,mac,sn,changerx,olt',['olt' => $this->id,'keyonu' => $dataOnu['keyonu']]);
		if(!empty($onu['idonu'])){
			$rx = $rx ?? null;
			if(!empty($dataOnu['rx'])){
				$rx = $this->clear_rx($dataOnu['rx']);
				if($rx){
					$this->db->SQLupdate('onus',['rx' => $rx,'rating' => 1],['idonu' => $onu['idonu']]);
				}
			}
			if($config['logsignal']=='on'){
				if($rx){
					$savehistor = SignalMonitor($onu['status'], $rx, $onu['rx'], $onu['idonu'], $onu);
				}
			}else{
				$savehistor = true;
			}
			if(!empty($config['onugraph']) && $config['onugraph']=='on' && $savehistor && $rx){
				$this->db->SQLInsert('historysignal',['device' => $this->id,'onu' => $onu['idonu'],'signal' => $rx,'datetime' => $this->now]);
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
		$value = str_replace('"', '',$value);
		$value = str_replace(' ', '',$value);
		$value = trim($value);	
		return $value;
	}
	public function clear_rx($value){
		if(preg_match('/6553/i',$value)) {
			$value = 0; 
		}else{
			$value = str_replace('"', '',$value);
			$value = trim($value);
			$value = str_replace('N/A',0,$value);
			$value = sprintf('%.2f',$value);
			$value = str_replace('0.00',0,$value);
		}
		return $value;
	}
	public function tempUpdateSignalCheck(){	
		global $db, $PMonTables;

	}
	public function tempSaveOnuEpon($dataOnu){	
		$sqlset = [];
		if(!empty($dataOnu['inface'])){
			preg_match('/0\/(\d+):(\d+)/i',$dataOnu['inface'],$dataMatch);
			$indexPortOlt = $dataMatch[1];
		}
		$dataOnu['status'] = (!empty($dataOnu['status']) ? $dataOnu['status'] : (!empty($dataOnu['dist']) ? 1 : 2));
		if(!empty($dataOnu['keyonu']) && $indexPortOlt){
			$arr = $this->db->Fast('onus','*',['keyonu' =>$dataOnu['keyonu'],'olt' => $dataOnu['id']]); 
			if(!empty($arr['idonu'])){
				if($dataOnu['status']==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($dataOnu['status']==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}else{
					
				}
				$sqlset['updates'] = $this->now;
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
				$sqlinsert = array('olt' => $dataOnu['id'],'updates' => $this->now, 'added' => $this->now, ($dataOnu['status']==1?'online':'offline') => $this->now, 'rating' => 1,'keyonu' => $dataOnu['keyonu'],'status' => $dataOnu['status'],'zte_idport' => $indexPortOlt,'mac' => $dataOnu['mac'],'inface' => $dataOnu['inface'],'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),'reason' => (!empty($dataOnu['reason']) ? $dataOnu['reason'] : ''),'type' => $dataOnu['pon'],'cron' => 1,'portolt' => $indexPortOlt);
				$this->db->SQLinsert('onus',$sqlinsert);
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
				$arrayPort[$port['id']]['port'] = $port['pon'];
				$arrayPort[$port['id']]['count'] = count($getONUstatusPort);
				$arrayPort[$port['id']]['online'] = count($getONUstatusPortOn);
				$arrayPort[$port['id']]['offline'] = count($getONUstatusPort) - count($getONUstatusPortOn);
			}
		}
		if(is_array_empty($arrayPort)){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
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
}
?>