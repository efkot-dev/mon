<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class SmartFiber_Gpon{ 
    private $logger;
    private $db;
	protected $snmp;
	protected $id;
	protected $ip;
	protected $oidid;
	protected $community;
	protected $deviceoid;
    private $indexdevice;
    private $now;
    private $primary = 'status,dist,name';
    private $configapionugpon = 'rx,operstatus,model,eth,vendor,temp,status,dist,name,volt,tx,vlan'; // regtime
    private $configapionugponget = 'rx,operstatus,model,eth,vendor,temp,status,dist,name,volt,tx,vlan'; // regtime
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
	public function Support($check){
		return match ($check){
			'port', 'onu', 'saveonu', 'api', 'fileonu' => true,	default => false,
		};
	}
	public function snsmartfiber(?string $powerblock): ?string {
		if ($powerblock !== null) {
			return str_replace(['STRING:', ' ', '-', '"'], '',$powerblock);
		} else {
			return null;
		}
	}
	public function Load(){
		$result = array();
		$listinface = '1.3.6.1.4.1.8888.1.14.2.4.1.1.1.8.2';
		$listonu = $this->snmp->walk($this->ip,$this->community,$listinface,false);
		if($listonu){
			$indexonu = @explodeRows(str_replace('.'.$listinface.'.','',$listonu));				
			if(is_array_empty($indexonu)){
				foreach($indexonu as $io => $eachsig){
					$line = explode('=', $eachsig);
					if(isset($line[0]) && isset($line[1])){
						$sn = $this->snsmartfiber($line[1]);
						preg_match('/(\d+)\.(\d+)/', $line[0], $match);
						if($sn && $match[1] && $match[2]){
							$result[$io] = array('do' => 'onu','id'=>$this->id,'inface'=>'0/'.trim($match[1]).':'.trim($match[2]).'','pon'=>'gpon','sn'=>$sn,'types'=>$this->primary,'keyport'=>trim($match[1]),'keyonu'=> trim($match[2]));
						}
					}	
				}
			}
		}
		if(is_array_empty($result)){
			checkerONUHuaweiZte($result,$this->id);
		}else{
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'emptysnmpwalk'.' '.$listinface,'deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result) ? $result : null);
	}
	public function ConfigApiOnu($data){
		return array('do' => 'onu','types' => $this->configapionugpon,'pon' => mb_strtolower($data['type']),'keyport' => $data['zte_idport'],'keyonu' => $data['keyonu'],'id' => $this->id);
	}	
	public function ConfigApiOnuGet($data){
		return array('do' => 'onu','types' => $this->configapionugponget,'pon' => mb_strtolower($data['type']),'keyport' => $data['zte_idport'],'keyonu' => $data['keyonu'],'id' => $this->id);
	}
	public function statusgcom($status) {
		$statuses = ["1" => 1,"0" => 2,"2" => 2,"3" => 1,"4" => 2];		
		return isset($statuses[$status]) ? $statuses[$status] : 2;
	}
	public function Onu($dataPort,$dataOnu){
		$res = array(); 
		if(is_array($dataPort)){
			foreach($dataPort as $type => $value) {
				$res[$type] = $this->preparedataGCOM($value,$type);
			}
		} elseif(!$dataPort) {
			$array_separated = explode(',',$this->configapionugpon);
			foreach($array_separated as $type) {
				if(!empty($dataOnu[$type]))
					$res[$type] = $dataOnu[$type];
			}
		}
		$result = is_array($res) ? $this->updateonu($dataOnu,$res) : false;
		return $result;
	}
	public function updateonu($ont,$getData){
		global $db, $lang, $config;
		$result = array();	
		if(is_array($getData)){
			if(!empty($getData['tx'])){
				$SQLset['tx'] = $getData['tx'];
				$result['tx'] = $getData['tx'];
			}
			if(!empty($getData['rx'])){
				$SQLset['rx'] = $getData['rx'];
				$result['rx'] = $getData['rx'];
			}
			if(!empty($getData['model'])) 
				$SQLset['model'] = $getData['model'];
			if(!empty($getData['vendor'])) 
				$SQLset['vendor'] = $getData['vendor'];			
			$SQLset['status'] = $this->statusgcom($getData['status']);			
			if(!empty($getData['sn'])) 
				$SQLset['sn'] = $getData['sn'];
			if(!empty($getData['dist'])) 
				$SQLset['dist'] = $getData['dist'];
			if($ont['status']==2 && $getData['status']==1){
				$SQLset['online'] = $this->now;
				$SQLset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$SQLset['offline'] = $this->now;
				$SQLset['status'] = 2;
			}
			if(is_array($SQLset)){
				$SQLwhere['idonu'] = $ont['idonu'];
				$this->db->SQLupdate('onus',$SQLset,$SQLwhere);
			}			
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			if(!empty($getData['eth']))
				$result['wan'] = $getData['eth'];
			if(!empty($getData['sn']))			
				$result['sn'] = $getData['sn'];			
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
			if(!empty($getData['temp'])) 
				$result['temp'] = $getData['temp'];			
			if(!empty($getData['name'])) 
				$result['name'] = $getData['name'];			
			if(!empty($getData['operstatus'])) 
				$result['operstatus'] = $getData['operstatus'];			
			if(!empty($getData['regtime'])) 
				$result['regtime'] = $getData['regtime'];
			return $result;
		}
	}	
	public function volot_css($value){
		return (int)$value;
	}
	public function preparedataGCOM($dataApi, $type){
		$data = $this->clearData($dataApi);
		switch($type){
			case 'status':
				return $this->statusgcom($data);
			case 'dist':
				return isset($data) ? (int) $data : null;
			case 'rx':
				return isset($data) ? $this->clear_rx($data) : null;
			case 'sn':
				return $dataApi ? ClearDataMac($dataApi) : null;
			case 'model':
			case 'volt':
			case 'vendor':
			case 'name':
			case 'device':
			case 'temp':
			case 'vlan':
				return $data ? $data : null;			
			case 'regtime':
				return $data === '-' ? null : $data;			
			case 'tx':
				return $data ? $this->clear_rx($data) : null;	
			case 'eth':
				return $data == 1 ? 'up' : 'down';			
			case 'operstatus':
				return $data == 1 ? 'up' : 'down';
			default:
				return null;
		}
	}
	public function clearResult($value){
		return trim(str_replace(' ', '', str_replace('STRING:', '', str_replace('"', '', $value))));
	}
	public function savePort($dataPort){
		global $db, $PMonTables;
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
	public function gettypeportsm($str){
		if(preg_match('/gpon/i',$str)){
			return 'pon';	
		}elseif(preg_match('/e0/i',$str)){
			return 'ethernet';	
		}elseif(preg_match('/e1/i',$str)){
			return'sfp';	
		}else{
			return'port';		
		}
	}	
	public function getnameportgcom($str){
		$str = str_replace('gpon0/2', 'GPON 0',$str);	
		$str = str_replace('e0', 'Ethernet 0',$str);	
		$str = str_replace('e1', 'Ethernet 1',$str);		
		return $str;
	}
	public function statusport(int $status): string {
		return ($status == 1) ? 'up' : 'down';
	}
	public function Port(){
		$data = array();
		$oidportswitch = '1.3.6.1.2.1.2.2.1.2';
		$gponlistport = $this->snmp->walk($this->ip,$this->community,$oidportswitch,true);
		$gponlistport = str_replace('.'.$oidportswitch.'.','',$gponlistport);
		$indexport = explodeRows($gponlistport);
		if(is_array($indexport)){
			$listPort = array();
			foreach ($indexport as $idport => $valueport) {				
                $infport = explode('=', $valueport);
				if(!empty($infport[1]) && !empty($infport[0])){
					$dataindexport = $this->clearResult($infport[1]);
					if(preg_match('/gpon/i',$dataindexport) || preg_match('/e0/i',$dataindexport)){
						$oidportoperstatus = "1.3.6.1.2.1.2.2.1.8.".trim($infport[0]);
						$resultsnmpdata = $this->snmp->get($this->ip, $this->community, $oidportoperstatus, true);
						$resultclear = (int)$this->clearData(str_replace($oidportoperstatus,'',$resultsnmpdata));
						$listPort[$idport] = array('operstatus' =>$this->statusport($resultclear),'id' =>trim($infport[0]),'typeport' => $this->gettypeportsm($dataindexport),'name' => $this->getnameportgcom($dataindexport));
					}
				}				
			}
			$data['port'] = $listPort;
		}
		if(is_array($data['port'])){
			foreach($data['port'] as $idponport => $valuepon){
				if($valuepon['typeport']=='pon') {
					preg_match('/0\/(\d+)/',$valuepon['name'],$mat);
					$listpon[$idponport]['name'] = $valuepon['name'];
					$listpon[$idponport]['sort'] = $mat[1];
					$listpon[$idponport]['sfpid'] = $valuepon['id'];
					$listpon[$idponport]['cardcount'] = 128;
				}
			}
			if(is_array($listpon)){
				usort($listpon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data['pon'] = $listpon;
			}
		}
		return (is_array($data) ? $data : null);
	}
    protected function savePonSwitch($dataport) {
		global $db, $PMonTables;
		$row = $this->db->Fast($PMonTables['switchpon'],'*',['oltid' => $this->id,'sfpid' => $dataport['sfpid']]);
		if(!empty($row['id'])){
			
		}else{
			$allonu = $this->db->Multi('onus','*',['olt' => $this->id,'portolt' => $dataport['sort']]);
			$this->db->SQLinsert($PMonTables['switchpon'],['count' => count($allonu),'support' => $dataport['cardcount'],'sort' => $dataport['sort'],'oltid' => $this->id,'pon' => $dataport['name'],'sfpid' => $dataport['sfpid'],'added' => $this->now]);
		}
		if(!empty($dataport['sfpid']) && !empty($dataport['sort'])){		
			$this->db->SQLupdate($PMonTables['onus'],['portolt' => $dataport['sfpid']],['olt' => $this->id,'zte_idport' => $dataport['sort']]);
		}
		if(!empty($row['id'])){
			$allonu = $this->db->Multi($PMonTables['onus'],'*',['olt' => $this->id,'zte_idport' => $dataport['sort']]);
			$SQLportset['count'] = count($allonu);
			$SQLportset['sort'] = $dataport['sort'];
			$this->db->SQLupdate($PMonTables['switchpon'],$SQLportset,['id' =>$row['id']]);
		}
	}
    protected function savePortSwitch($data) {
		global $db, $PMonTables;
		if(!empty($data['id'])){	
			$row = $this->db->Fast($PMonTables['switchport'],'*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(!empty($row['id'])){
				$this->db->SQLupdate($PMonTables['switchport'],['operstatus' => $data['operstatus'],($data['operstatus']=='down'?'timedown':'timeup')=>$this->now],['id' => $row['id']]);
			}else{
				$this->db->SQLinsert($PMonTables['switchport'],['deviceid' => $this->id,'llid' => $data['id'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => $data['operstatus'],'added' => $this->now]);
			}
		}
	}
	public function tempUpdateSignalCheck(){	
		global $db;

	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $db, $config;
		$savehistor = $savehistor ?? null;
		$onu = $this->db->Fast('onus','status,rx,idonu,inface,sn,changerx,olt',['olt' => $this->id,'zte_idport' => $dataOnu['keyport'],'keyonu' => $dataOnu['keyonu']]);
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
	public function clearData(string $value): string {
		$value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|\s|=|"/', '', $value);
		return trim($value);
	}
	public function clear_rx($value){
		if (preg_match('/dBm/i',$value)){
			preg_match('/\((-?\d+\.\d+)dBm\)/', $value, $matches);
			if(isset($matches[1])) {
				return sprintf('%.2f',$matches[1]);
			} else{
				return 0;
			}
		} elseif(preg_match('/65535/i',$value)) {
			return 0;
		} else{
			return $value;			
		}	
	}
	public function tempSaveOnuGpon($dataOnu){	
		global $db, $config, $lang, $PMonTables;
		$dataOnu['status'] = (!empty($dataOnu['status']) ? $this->statusgcom($dataOnu['status']): 2);
		if(!empty($dataOnu['keyonu'])){
			$arr = $this->db->Fast('onus','*',['keyonu' =>$dataOnu['keyonu'],'zte_idport' =>$dataOnu['keyport'],'olt' => $dataOnu['id']]); 
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
				if(!empty($dataOnu['sn']))
					$SQLset['sn'] = $dataOnu['sn'];				
				if(!empty($dataOnu['name']))
					$SQLset['name'] = $dataOnu['name'];
				$SQLset['inface'] = $dataOnu['inface'];					
				if(!empty($arr['portolt'])){
					$SQLset['portolt'] = $arr['portolt'];
				}else{
					$SQLset['portolt'] = $dataOnu['keyport'];	
				}
				if(!empty($arr['zte_idport'])){
					$SQLset['zte_idport'] = (!empty($arr['zte_idport'])?$arr['zte_idport']:$dataOnu['keyport']);
				}
				$this->db->SQLupdate('onus',$SQLset,['idonu' => $arr['idonu']]);
			}else{			
				$sqlinsert['olt'] = $dataOnu['id'];
				$sqlinsert['added'] = $this->now;
				$sqlinsert[($dataOnu['status']==1?'online':'offline')] = $this->now;
				$sqlinsert['updates'] = $this->now;
				$sqlinsert['rating'] = 1;
				$sqlinsert['keyonu'] = $dataOnu['keyonu'];
				$sqlinsert['status'] = $dataOnu['status'];
				$sqlinsert['sn'] = $dataOnu['sn'];
				if(!empty($dataOnu['name']))				
					$sqlinsert['name'] = $dataOnu['name'];				
				$sqlinsert['inface'] = $dataOnu['inface'];	
				$sqlinsert['portolt'] = $dataOnu['keyport'];
				$sqlinsert['zte_idport'] = $dataOnu['keyport'];
				$sqlinsert['cron'] = 1;
				$sqlinsert['type'] = $dataOnu['pon'];
				$sqlinsert['dist'] = (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0);
				$this->db->SQLinsert('onus',$sqlinsert);
				$idonu = $this->db->getInsertId();
				$logs = ['log'=>'onu','type'=>'addonu','descr'=>$dataOnu['inface'].' '.($dataOnu['sn']?$dataOnu['sn']:'--'),'deviceid'=>$dataOnu['id'],'onuid'=>$idonu,'who'=>'cron'];
				$this->logger->init($logs);
			}
		}
	}
	public function StatisticOLT(){	
		global $db, $PMonTables;
		$getPort = $this->db->Multi($PMonTables['switchpon'],'*',['oltid' => $this->id]);
		if(count($getPort)){
			foreach($getPort as $port){
				$getONUstatusPort = $this->db->Multi($PMonTables['onus'],'idonu,status',['olt' => $this->id,'portolt'=>$port['sfpid']]);
				$getONUstatusPortOn = $this->db->Multi($PMonTables['onus'],'idonu,status',['status' => 1,'olt' => $this->id,'portolt'=>$port['sfpid']]);
				$arrayPort[$port['id']]['port'] = $port['pon'];
				$arrayPort[$port['id']]['count'] = count($getONUstatusPort);
				$arrayPort[$port['id']]['online'] = count($getONUstatusPortOn);
				$arrayPort[$port['id']]['offline'] = count($getONUstatusPort) - count($getONUstatusPortOn);
			}
		}
		if(isset($arrayPort)){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate($PMonTables['switchpon'],['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getListOnuOnline(){	
		global $db, $PMonTables;
		$sqlList = $this->db->Multi('onus','keyonu,portolt,idonu,type,zte_idport',['olt' => $this->id, 'status' => 1]);
		$array = array();
		if(is_array($sqlList)){
			foreach($sqlList as $key => $value){
				if(!empty($value['keyonu']) && !empty($value['zte_idport']))
					$array[$key] = array('id'=>$this->id,'keyport'=>$value['zte_idport'],'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx');
			}
		}
		return $array;
	}	
}
?>