<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class BDCOM_Gpon { 
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
    private $primary = 'status,dist,sn,name,reason';
	private $poller = 'status,reason,rx';
    private $configapionugpon = 'dist,name,uptime,vendor,model,admin,status,sn,reason';
    private $configapionugponget = 'rx,tx,eth,dist,name,uptime,vendor,model,admin,status,sn,reason,rxolt';
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
			'port', 'onu', 'saveonu', 'api', 'rxolt', 'poller', 'fileonu' => true,	default => false,
		};
	}
	public function Load(){	
		$result = [];	
		$gpon = [
			'oid' => $this->deviceoid[$this->id]['onu']['listname']['gpon']['oid'],
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$indexonugpon = pmon_walk($gpon);
		if(isset($indexonugpon) && is_array($indexonugpon)){
			foreach($indexonugpon as $io => $eachsig) {
				if (!empty($eachsig['result'])) {
					if(preg_match('/(GPON[0-9]{1,2}\/[0-9]{1,2}:)[0-9]{1,3}/',$eachsig['result'],$rexname)
					|| 
						preg_match('/(gpon[0-9]{1,2}\/[0-9]{1,2}:)[0-9]{1,3}/',$eachsig['result'],$rexname)				
					){	
						if(!empty($rexname[0])){
							$inface = str_replace('gpon','', strtolower(str_replace(' ', '',trim($rexname[0]))));
							$result[$io] = [
								'do' => 'onu','id'=>$this->id,'pon'=>'gpon','types'=>$this->primary,'keyonu'=> $io,
								'inface'=>$inface, 'checker'=>md5($io.$inface)							
							];
						}
					}
				}	
			}
		}
		if(is_array_empty($result)){
			perevirka_ONU_bdcom_gpon($result,$this->id);
		}else{
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'empty_snmp_walk '.$this->deviceoid[$this->id]['onu']['listname']['gpon']['oid'],'deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result) ? $result : null);
	}
	public function ConfigApiOnu($data){
		$cnf = array('do' => 'onu','types' => $this->configapionugpon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
		return $cnf;
	}	
	public function ConfigApiOnuGet($data){
		$cnf = array('do' => 'onu','types' => $this->configapionugponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'id' => $this->id);
		return $cnf;
	}
	public function Onu($dataPort,$dataOnu){
		$res = array();			
		if(is_array_empty($dataPort)){
			foreach($dataPort as $type => $value) {
				$res[$type] = $this->preparedataBDCOM($value,$type);
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
		}
		return (is_array_empty($result) ? $result : null);
	}
	public function updateonu($ont,$getData){
		$result = array();	
		if(is_array_empty($getData)){
			if(!empty($getData['tx'])){
				$SQLset['tx'] = $getData['tx'];
			}
			if(!empty($getData['rx'])){
				$SQLset['rx'] = $getData['rx'];
			}
			if(!empty($getData['reason'])) 
				$SQLset['reason'] = $getData['reason'];				
			if(!empty($getData['name'])) 
				$SQLset['name'] = $getData['name'];			
			if(!empty($getData['vendor'])) 
				$SQLset['vendor'] = $getData['vendor'];
			if(!empty($getData['dist'])) 
				$SQLset['dist'] = $getData['dist'];
			if($ont['status']==2 && $getData['status']==1){
				if(!empty($getData['timeaut']))
					$SQLset['online'] = $this->now;
				if(!empty($getData['offline']))
					$SQLset['online'] = $getData['offline'];
				$SQLset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$SQLset['offline'] = $this->now;
				$SQLset['status'] = 2;
			}
			if(is_array_empty($SQLset)){
				$this->db->SQLupdate('onus',$SQLset,['idonu' => $ont['idonu']]);
			}			
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
			if(!empty($getData['uptime']))	
				$result['uptime'] = $getData['uptime'];
			if(!empty($getData['vendor']))	
				$result['vendor'] = $getData['vendor'];
			if(!empty($getData['dist'])) 
				$result['dist'] = $getData['dist'];
			if(!empty($ont['lastrx']))
				$result['lastrx'] = $ont['lastrx'];
			if(!empty($getData['rx'])) 
				$result['rx'] = $getData['rx'];			
			if(!empty($getData['rxolt'])) 
				$result['rxolt'] = $getData['rxolt'];
			if(!empty($getData['tx'])) 
				$result['tx'] = $getData['tx'];			
			if(!empty($getData['admin'])) 
				$result['adminstatus'] = $getData['admin'];
			return (is_array_empty($result) ? $result : null);
		}
	}	
	public function preparedataBDCOM($dataApi,$type){
		$result = '';
		$data = $this->clearData($dataApi);
		switch($type){
			case 'status':
				if(isset($data)){
					$result = ($data==1 ? 1 : 2);
				}else{
					$result = 2;
				}
			break;			
			case 'admin':
				$result = (isset($data) && $data==1 ? 'up' : 'down');
			break;			
			case 'dist':
				if(isset($data)){
					$result = (int)$data;
				}else{
					$result = 0;
				}
			break;
			case 'rx':
			case 'rxolt':
			case 'tx':
				if(isset($data)){
					$result = $this->clear_rx($data);
				}else{
					$result = 0;
				}
			break;
			case 'sn':
				if($dataApi)
					$result = $dataApi;
			break;
			case 'vendor':
				$result = $data;
			break;				
			case 'reason':
				$result = $this->reason($data);
			break;			
			case 'name':
			case 'uptime':
			case 'admin':
			case 'model':
				$result = $data;
			break;			
			case 'eth':
				$result = ($data==1?'up':'down');				
			break;			
		}		
		return $result;
	}
	public function clearResult($value){
		$value = str_replace('"','',$value);
		$value = trim($value);
		$value = str_replace('/','',$value);
		return $value;
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
		}
	}
	public function Port(){
		$data = [];	
		$listport = [];		
		$port = [
			'oid' => $this->deviceoid[$this->id]['onu']['listname']['gpon']['oid'],
			'type' => 'class','ip' => $this->ip,'community'=> $this->community
		];
		$indexport = pmon_walk($port);
		if(is_array_empty($indexport)){
			foreach ($indexport as $idport => $dataport) {				
				if(!empty($dataport['result'])){
					$inface_name = $this->clearResult($dataport['result']);
					if(!preg_match('/gpon0(\d+):(\d+)/i',$inface_name) AND !preg_match('/GPON0(\d+):(\d+)/i',$inface_name) AND !preg_match('/VLAN/i',$inface_name) AND !preg_match('/Null/i',$inface_name)){
						$listport[$idport] = [
							'id' =>$idport,
							'typeport' => getTypePort($inface_name),
							'name' => getNameBdcomport($inface_name)
						];
					}
				}				
			}
			$data['port'] = $listport;
		}
		if(is_array_empty($data['port'])){
			foreach($data['port'] as $idPonport => $valuePon){
				if(preg_match('/GPON/i',$valuePon['name']) || preg_match('/gpon/i',$valuePon['name']) ) {
					preg_match('/GPON 0\/(\d+)/',$valuePon['name'],$mat);
					$listPon[$idPonport]['name'] = 'GPON 0/'.$mat[1].'';
					$listPon[$idPonport]['sort'] = $mat[1];
					$listPon[$idPonport]['sfpid'] = $valuePon['id'];
					$listPon[$idPonport]['cardcount'] = 128;
				}
			}
			if(is_array_empty($listPon)){
				usort($listPon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data['pon'] = $listPon;
			}
		}
		return $data;
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
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(empty($row['id']))
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
		}
	}
	public function tempUpdateSignalCheck(){	

	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
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
			if(!empty($config['onugraph']) && $config['onugraph']=='on' && $savehistor){
				$this->db->SQLInsert('historysignal',['device' => $this->id,'onu' => $onu['idonu'],'signal' => $rx,'datetime' => $this->now]);
			}
		}
	}	
	public function clearSN($value){
		$value = str_replace('"', '',$value);
		$value = str_replace(' ', '',$value);
		$value = str_replace(':', '',$value);
		$value = trim($value);	
		return $value;
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
			$value = $value;
			$value = sprintf('%.2f',$value);
			$value = str_replace('0.00',0,$value);
		}
		return $value;
	}
	public function tempSaveOnuGpon($dataOnu){	
		$sqlset = [];
		if(!empty($dataOnu['inface'])){
			preg_match('/0\/(\d+):(\d+)/i',$dataOnu['inface'],$dataMatch);
			$indexportolt = $dataMatch[1];
		}
		if(isset($dataOnu['sn']) && !empty($dataOnu['sn'])){
			$dataOnu['sn'] = $this->clearSN($dataOnu['sn']);
		}
		$dataOnu['status'] = (!empty($dataOnu['status']) ? $dataOnu['status'] : (!empty($dataOnu['dist']) ? 1 : 2));
		if(!empty($dataOnu['keyonu'])){
			$arr = $this->db->Fast('onus','*',['keyonu' =>$dataOnu['keyonu'],'olt' => $this->id]); 
			if(!empty($arr['idonu'])){
				$this->cache_onu[$dataOnu['keyonu']] = $arr;
				$sqlset['updates'] = $this->now;
				$sqlset['cron'] = 1;
				if($dataOnu['status']==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($dataOnu['status']==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}
				$sqlset['status'] = $dataOnu['status'];
				$sqlset['type'] = $dataOnu['pon'];
				if(!empty($dataOnu['dist']))
					$sqlset['dist'] = (int)$dataOnu['dist'];				
				if(!empty($dataOnu['reason']))
					$sqlset['reason'] = $this->reason($dataOnu['reason']);
				$sqlset['sn'] = (!empty($dataOnu['sn'])?$dataOnu['sn']:'--');				
				if(!empty($dataOnu['name']))
					$sqlset['name'] = $dataOnu['name'];
				if(!empty($dataOnu['inface']))
					$sqlset['inface'] = $dataOnu['inface'];	
				if(isset($indexportolt)){
					$sqlset['zte_idport'] = $indexportolt;
				}
				if(!empty($arr['portolt'])){
					$sqlset['portolt'] = $arr['portolt'];
				}else{					
					$sqlset['portolt'] = $indexportolt;
				}
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{	
				$sqlset = array(
					'olt' => $dataOnu['id'],($dataOnu['status']==1?'online':'offline') => $this->now,'added' => $this->now,'updates' => $this->now,
					'keyonu' => $dataOnu['keyonu'],'status' => $dataOnu['status'],'sn' => $dataOnu['sn'],'inface' => $dataOnu['inface'],
					'dist' => (!empty($dataOnu['dist']) ? (int)$dataOnu['dist'] : 0),
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),'reason' => (!empty($dataOnu['reason']) && $dataOnu['status']==2 ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon'],'cron' => 1,'zte_idport' => $indexportolt, 'portolt' => $indexportolt
				);
				$this->db->SQLinsert('onus',$sqlset);
				$idonu = $this->db->getInsertId();
				$logs = ['log'=>'onu','type'=>'addonu','descr'=>$dataOnu['pon'].' '.$dataOnu['inface'].' '.(!empty($dataOnu['sn'])?$dataOnu['sn']:'--'),'deviceid'=>$dataOnu['id'],'onuid'=>$idonu,'who'=>'cron'];
				$this->logger->init($logs);
			}
		}
	}
	public function saveOnuCommands(){

	}
	public function StatisticOLT(){	
		$getPort = $this->db->Multi('switch_pon','*',['oltid' => $this->id]);
		if(count($getPort)){
			foreach($getPort as $port){
				$getONUstatusPort = $this->db->Multi('onus','idonu,status',['olt' => $this->id,'portolt'=>$port['sfpid']]);
				$getONUstatusPortOn = $this->db->Multi('onus','idonu,status',['status' => 1,'olt' => $this->id,'portolt'=>$port['sfpid']]);
				$count = count($getONUstatusPort);
				if (!empty($port['count']) && $port['count']>50 && $port['count'] != $count) {
					$mess = '[icon-work][b]'.$this->ip.'[/b] [b]'.$port['pon'].'[/b] '.$count.'(onu)';
					$this->db->SQLinsert('notification',['status'=>1,'type'=>3,'system'=>'monitor','message'=>$mess,'added'=>date('Y-m-d H:i:s')]);
				}
				$arrayPort[$port['id']]['port'] = $port['pon'];
				$arrayPort[$port['id']]['count'] = $count;
				$arrayPort[$port['id']]['online'] = count($getONUstatusPortOn);
				$arrayPort[$port['id']]['offline'] = $count - count($getONUstatusPortOn);
			}
		}
		if(is_array_empty($arrayPort)){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getListOnuOnline(){	
		global $PMonTables;
		$sqlList = $this->db->Multi('onus','keyonu,idonu,type',['olt' => $this->id, 'status' => 1]);
		$array = array();
		if(is_array_empty($sqlList)){
			foreach($sqlList as $key => $value){
				if(!empty($value['keyonu']) && !empty($value['type']))
					$array[$key] = array('id'=>$this->id,'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx');
			}
		}
		return $array;
	}
	public function reason($check){
		return match ($check){
			"0" => 'err22',
			"1" => 'err1',
			"2" => 'err2',
			"3" => 'err27',
			"4" => 'err28',
			"5" => 'err5',
			"6" => 'err6',
			"7" => 'err7',
			"8" => 'err8',
			"9" => 'err9',
			"10" => 'err29',
			"11" => 'err12',
			"12" => 'err13',
			default => 'err20'
		};
	}
	public function getOnuPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type'])){
			$array = [
				'id'=>$value['olt'],
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
	public function tempSaveSignalSaveRxOnuGpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->clear_rx($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}	
}
?>