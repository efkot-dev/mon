<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class CDATA_1616V3 { 
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
    private $primary = 'dist,status,reason';
    private $configapionugpon = 'dist,status,model,vendor';
    private $configapionugponget = 'dist,status,model,rx,vendor';
	public function Support($check){
		return match ($check){
			'port', 'onu', 'saveonu', 'api', 'rxolt', 'fileonu' => true,	default => false,
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
	public function inface1616($data){
		$port = floor($data/256)%256/16+1;
		$numonu = ($data % 128);
		return '0/'.$port.':'.$numonu;
	}
	public function Load(){
		$oid = [
			'oid' => $this->deviceoid[$this->id]['onu']['listsn']['gpon']['oid'],'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$listonu = pmon_walk($oid);
		$result = [];
		if(isset($listonu) && is_array($listonu) && count($listonu)>0){
			foreach ($listonu as $io => $eachsig) {
				if (isset($eachsig) && !empty($eachsig['result'])) {
					$result[$io] = array(
						'inface'=>$this->inface1616($io),
						'sn'=>SnHuawei($eachsig['result']),
						'do' => 'onu','id'=>$this->id,'pon'=>'gpon',
						'types'=>$this->primary,'keyonu'=> $io
					);
				}
			}
		}
		if(is_array($result)){
			Checker_Pmon_BDCOM_EPON($result,$this->id);
		}
		return (is_array($result) ? $result : null);
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
		if(is_array($dataPort)){
			foreach($dataPort as $type => $value) {
				$res[$type] = $this->preparedataCDATA($value,$type);
			}
		}
		if(!$dataPort && !$res){
			$array_separated = explode(',',$this->configapionugpon);
			foreach($array_separated as $type) {
				if(!empty($dataOnu[$type])){
					$res[$type] = $dataOnu[$type];
				}
			}
		}
		if(is_array($res)){
			$result = $this->updateonu($dataOnu,$res);
		}else{
			$result = false;
		}
		return $result;
	}
	public function updateonu($ont,$getData){
		$sqlset = array();
		$result = array();
		if(is_array($getData) && isset($getData)){
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
			if(!empty($getData['sn'])) 
				$sqlset['sn'] = $getData['sn'];			
			if(!empty($getData['dist'])) 
				$sqlset['dist'] = $getData['dist'];
			if($ont['status']==2 && $getData['status']==1){
				if(!empty($getData['timeaut']))
					$sqlset['online'] = $this->now;
				if($getData['offline'])
					$sqlset['online'] = $getData['offline'];
				$sqlset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$sqlset['offline'] = $this->now;
				$sqlset['status'] = 2;
			}
			if(is_array($sqlset) && count($sqlset)>0)
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $ont['idonu']]);
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			$result['wan'] = $getData['eth'];			
			if(!empty($getData['name']))	
				$result['name'] = $getData['name'];				
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
			if(!empty($getData['eth'])) 
				$result['eth'] = $getData['eth'];
			return $result;
		}
	}	
	public function preparedataCDATA($dataApi,$type){
		$data = $this->clearData($dataApi);
		switch($type){
			case 'status':
				$result = (isset($data) && $data==1 ? 1 : 2);
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
					$result = $this->clear_rx($data);
				}else{
					$result = 0;
				}
			break;
			case 'sn':
			case 'vendor':
			case 'name':
			case 'model':
			case 'vendor':
				$result = (isset($data)?$data:'');
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
	public function Port(){
		$listport = [];
		$data = [];
		$oid = [
			'oid' => $this->deviceoid[$this->id]['global']['listport']['port']['oid'],'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$portlist = pmon_walk($oid);
		$result = [];
		if(isset($portlist) && is_array($portlist) && count($portlist)>0){
			foreach ($portlist as $io => $eachsig) {
				if (isset($eachsig) && !empty($eachsig['result'])) {
					$getinf = clearData1108($eachsig['result']);
					if(!preg_match('/:/i',$getinf)) {
						$nameport = getNameCdataport($getinf);
						$listport[$io] = array(
							'llid' =>trim($io),
							'id' =>trim($io),
							'typeport' => getTypePortHuawei($nameport),
							'name' => $nameport
						);
					}
				}
			}
			$data['port'] = $listport;
		}
		if(is_array($data['port'])){
			foreach($data['port'] as $idPonport => $valuePon){
				if(preg_match('/pon/i',$valuePon['name'])) {
					preg_match('/0\/0\/(\d+)/',$valuePon['name'],$mat);
					$listPon[$idPonport] = [
						'name' => $valuePon['name'],
						'sort' => $mat[1],
						'typeport' => 'gpon',
						'sfpid' => $valuePon['id'],
						'llid' => $valuePon['id'],
						'cardcount' => 128,
					];
				}				
			}
			if(is_array($listPon)){
				usort($listPon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data['pon'] = $listPon;
			}
		}
		return $data;
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Simple('SELECT count(id) as count_this FROM `switch_pon` WHERE oltid = '.$this->id.' AND sfpid = '.$dataPort['sfpid']);
		if ($row['count_this'] == 0) {
			$this->db->SQLinsert('switch_pon',['idportolt' => $dataPort['sfpid'],'support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'added' => $this->now]);
		}else{
			$this->db->SQLupdate('switch_pon',['pon' => $dataPort['name']],['oltid' => $this->id,'sfpid' => $dataPort['sfpid']]);
		}
		if(!empty($dataPort['sfpid']) && !empty($dataPort['sort'])){		
			$this->db->SQLupdate('onus',['portolt' => $dataPort['sfpid']],['olt' => $this->id,'zte_idport' => $dataPort['sort']]);
			$onus = $this->db->Simple('SELECT count(*) as count_onus FROM `onus` WHERE olt = '.$this->id.' AND zte_idport = '.$dataPort['sort']);
			$this->db->query("UPDATE switch_pon SET count = '{$onus['count_onus']}' WHERE sfpid  = '{$dataPort['sfpid']}' AND oltid = '{$this->id}'");
		}
	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $config;
		$savehistor = false;
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
	public function check_signal($rx){
		if ($rx == 0 OR !$rx OR $rx == NULL) {
			return 0;
		} else {
			return sprintf("%.2f",($rx/100));  
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
	public function tempSaveOnuGpon($dataOnu = array()){	
		$sqlset = array();
		$statusONU = (!empty($dataOnu['status']) && $dataOnu['status']==1 ? 1 : 2);
		if(!empty($dataOnu['inface'])){
			preg_match('/0\/(\d+):(\d+)/i',$dataOnu['inface'],$dataMatch);
			$idport = $dataMatch[1];
		}
		if(!empty($dataOnu['keyonu'])){
			$arr = $this->db->Fast('onus','*',['keyonu' =>$dataOnu['keyonu'],'olt' => $dataOnu['id']]); 
			if(!empty($arr['idonu'])){
				if($statusONU==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($statusONU==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}
				$sqlset['updates'] = $this->now;
				$sqlset['status'] = $statusONU;
				$sqlset['type'] = $dataOnu['pon'];
				if(!empty($dataOnu['dist']))
					$sqlset['dist'] = $dataOnu['dist'];				
				if(!empty($dataOnu['name']))
					$sqlset['name'] = $dataOnu['name'];
				if(!empty($dataOnu['sn'])){
					$sqlset['sn'] = $dataOnu['sn'];
				}
				if(!empty($dataOnu['inface']))
					$sqlset['inface'] = $dataOnu['inface'];				
				if(!empty($dataOnu['reason']))
					$sqlset['reason'] = $this->reason($dataOnu['reason']);	
				if(!empty($dataOnu['rx']))
					$sqlset['rx'] = $dataOnu['rx'];	
				if(isset($idport)){
					$sqlset['portolt'] = $idport;
					$sqlset['zte_idport'] = $idport;
				}
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{				
				$sqlset = array('olt' => $dataOnu['id'],'updates' => $this->now,'added' => $this->now,($statusONU==1?'online':'offline') => $this->now,'rating' => 1,'keyonu' => $dataOnu['keyonu'],'status' => $statusONU,'sn' => $dataOnu['sn'],'inface' => $dataOnu['inface'],'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),'type' => $dataOnu['pon'],'cron' => 1,'zte_idport' => $idport,'portolt' => $idport);
				$this->db->SQLinsert('onus',$sqlset);
			}
		}
	}
	public function reason($status) {
		if (strlen($status) > 7) {
			return '';
		}
		return $status;
	}
	public function Status($status){
		if($status==1){
			return 1;
		}else{
			return 2;
		}
	}	
	public function StatisticOLT(){	
		$getPort = $this->db->Multi('switch_pon','*',['oltid' => $this->id]);
		if(count($getPort)){
			foreach($getPort as $port){
				$getONUstatusPort = $this->db->Multi('onus','idonu,status',['olt' => $this->id,'portolt'=>$port['sfpid']]);
				$getONUstatusPortOn = $this->db->Multi('onus','idonu,status',['status' => 1,'olt' => $this->id,'portolt'=>$port['sfpid']]);
				$portId = $port['id'];
				$portArray = [
					'port' => $port['pon'],
					'count' => count($getONUstatusPort),
					'online' => count($getONUstatusPortOn),
					'offline' => count($getONUstatusPort) - count($getONUstatusPortOn),
				];
				$arrayPort[$portId] = $portArray;
			}
		}
		if(isset($arrayPort) && is_array($arrayPort)){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getOnuRxPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type']) && !empty($value['idonu'])){
			$array = [
				'id'=>$this->id,
				'idonu'=>$value['idonu'],
				'keyonu'=>$value['keyonu'],
				'pon'=>$value['type'],
				'do'=>'onu',
				'types'=>'rxolt'
			];	
		}
		return $array ?: null;
	}
	public function tempSaveSignalSaveRxOnuGpon($dataOnu){	
		global $config;		
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->Signal($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}
	public function Signal($value) {
		if (preg_match('/655/i', $value) || preg_match('/4748/i', $value) ) {
			return '0.00'; 
		}
		$value = trim(str_replace(['"', 'N/A'], ['', 0], $value));
		$value = sprintf('%.2f', $value / 100);
		return $value === '0.00' ? 0 : $value;
	}
	public function getListOnuOnline(){	
		$sqlList = $this->db->Multi('onus','keyonu,portolt,type',['olt' => $this->id, 'status' => 1]);
		if(is_array($sqlList)){
			foreach($sqlList as $key => $value){
				if(!empty($value['keyonu']) && !empty($value['type']))
					$array[$key] = array('id'=>$this->id,'keyonu'=>$value['keyonu'],'pon'=>$value['type'],'do'=>'onu','types'=>'rx');
			}
		}
		return $array;
	}
}
?>