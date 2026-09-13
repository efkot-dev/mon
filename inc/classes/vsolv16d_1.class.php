<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Vsolv16_Epon_1{ 
    private $logger;
    private $db;
	protected $id;
	protected $ip;
	protected $oidid;
	protected $community;
	protected $deviceoid;
    private $indexdevice;
    private $now;
    private $primary = 'status,name,dist';
    private $configapionuepon = 'status,vendor,model';
    private $configapionueponget = 'rx,tx,dist,status,mac,inface,vendor,model,volt';
	
    public function __construct($swid,$equipment, $db, $logger){
		$this->logger = $logger;
		$this->db = $db;
		if(is_numeric($swid)){
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
	public function Load(){
		$result = [];
		$epon = [
			'oid' => '1.3.6.1.4.1.37950.1.1.5.12.1.25.1.5','type' => 'class','ip' => $this->ip,'community'=> $this->community
		];
		$indexonuepon = pmon_walk($epon);
		if(is_array_empty($indexonuepon)){
			$io = 1;
			foreach($indexonuepon as $infacex => $data_epon) {
				preg_match('/(\d+).(\d+)/i',$infacex,$pononu);
				if(!empty($data_epon['result'])) {
					$mac = $this->macVsol($data_epon['result']);
					if(isset($mac)){
						$result[$io] = [
							'do' => 'onu','id' => $this->id,
							'mac' => $mac,
							'inface'=>'0/'.$pononu[1].':'.$pononu[2],
							'checker'=>md5('0/'.$pononu[1].':'.$pononu[2]),
							'pon' => 'epon','types' => $this->primary,
							'keyonu'=> trim($pononu[2]),
							'keyport'=> trim($pononu[1])
						];
						$io ++;
					}
				}
			}
		}
		if(is_array_empty($result)){
			checkerONUCdata($result,$this->id);
		}else{
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'empty_snmp_walk','deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result) ? $result : null);
	}
	public function ConfigApiOnu($data){
		return array('do' => 'onu','types' => $this->configapionuepon,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'keyport' => $data['zte_idport'],'id' => $this->id);
	}	
	public function ConfigApiOnuGet($data){
		return array('do' => 'onu','types' => $this->configapionueponget,'pon' => mb_strtolower($data['type']),'keyonu' => $data['keyonu'],'keyport' => $data['zte_idport'],'id' => $this->id);
	}
	public function signalVsol($status) {
		if (preg_match('/\(([-+]?\d*\.?\d+)\s*dBm\)/', $status, $matches)) {
			$status = trim($matches[1]);
			return $status;
		}
		return 0;
	}	
	public function nameVsol($name) {
		$name = str_replace('NULL', '',$name);
		if (preg_match('/"([^"]+)"/', $name, $matches)) {
			return trim($matches[1]);
		}
		return $name;
	}	
	public function macVsol($status) {
		if (preg_match('/"([^"]+)"/', $status, $matches)) {
			return trim($matches[1]);
		}
		return $status;
	}	
	public function infaceVsol($status) {
		if (preg_match('/"([^"]+)"/', $status, $matches)) {
			return trim($matches[1]);
		}
		return $status;
	}
	public function statusVsol($status) {
		if (preg_match('/\((\d+)\)/', $status, $matches)) {
			return trim($matches[1]);
		}		
		return $status;
	}	
	public function Onu($dataPort,$dataOnu){
		$res = array();    
		if(is_array_empty($dataPort)){
			foreach($dataPort as $type => $value) {
				$res[$type] = $this->preparedataVSOL($value,$type);
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
		global $db, $lang, $config;
		$result = array();	
		if(is_array_empty($getData)){
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
			$SQLset['status'] = $this->statusVsol($getData['status']);			
			if(!empty($getData['mac'])) 
				$SQLset['mac'] = $getData['mac'];
			if(!empty($getData['dist'])) 
				$SQLset['dist'] = $getData['dist'];
			if($ont['status']==2 && $getData['status']==1){
				$SQLset['online'] = $this->now;
				$SQLset['status'] = 1;
			}elseif($ont['status']==1 &&  $getData['status']==2){
				$SQLset['offline'] = $this->now;
				$SQLset['status'] = 2;
			}
			if(is_array_empty($SQLset)){
				$SQLwhere['idonu'] = $ont['idonu'];
				$this->db->SQLupdate('onus',$SQLset,$SQLwhere);
			}			
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			if(!empty($getData['sn']))			
				$result['sn'] = $getData['sn'];			
			if(!empty($getData['model']))	
				$result['model'] = $getData['model'];
			if(!empty($getData['vendor']))	
				$result['vendor'] = $getData['vendor'];
			if(!empty($getData['dist'])) 
				$result['dist'] = $getData['dist'];			
			if(!empty($getData['pvid'])) 
				$result['pvid'] = $getData['pvid'];
			if(!empty($ont['lastrx']))
				$result['lastrx'] = $ont['lastrx'];
			if(!empty($getData['rx'])) 
				$result['rx'] = $getData['rx'];
			if(!empty($getData['tx'])) 
				$result['tx'] = $getData['tx'];			
			if(!empty($ont['volt'])) 
				$result['volt'] = $ont['volt'];				
			return $result;
		}
	}	
	public function preparedataVSOL($dataApi, $type){
		$data = $this->clearData($dataApi);
		switch($type){
			case 'status':
				return $this->statusVsol($data);
			case 'dist':
				return isset($data) ? (int) $data : null;			
			case 'rx':
			case 'tx':
				return $data ? $this->signalVsol($data) : null;
			case 'mac':
				return $dataApi ? $this->macVsol($dataApi) : null;			
			case 'name':
				return $dataApi ? $this->nameVsol($dataApi) : null;
			case 'model':
			case 'inface':
			case 'vendor':
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
			$this->db->SQLupdate('switch',['updates_port' => $this->now],['id' => $this->id]);
		}
	}
	public function Port(){		
		$data_oid = ['oid' => '1.3.6.1.2.1.31.1.1.1.1','type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community];
		$listport = [];
		$data = [];
		$listportolt = pmon_walk($data_oid);
		if(is_array_empty($listportolt)){
			foreach ($listportolt as $portid => $value) {				
				if(!empty($value['result'])){
					$dataIndexPort = $this->clearData($value['result']);
					$patterns = '/^(GE0\/[1-8]|PON0\/[1-8]|EPON0\/[1-8])$/i';
					if (preg_match($patterns, $dataIndexPort) && strpos($dataIndexPort, ':') === false) {
						$listport[$portid] = [
							'id' => trim($portid),
							'typeport' => (getTypePortHuawei($dataIndexPort) ?? 'pon'),
							'name' => getNameVSolport($dataIndexPort)
						];
					}
				}				
			}
			$data['port'] = $listport;
		}
		if(is_array_empty($data['port'])){
			$index = 1;
			$maxIndex = 8;
			$listPon = [];
			foreach ($data['port'] as $idPonport => $valuePon) {
				if (preg_match('/^(PON\s0\/[1-8]|PON0\/[1-8]|EPON0\/[1-8]|EPON\s0\/[1-8])$/i', $valuePon['name'])) {
					if ($index > $maxIndex) {
						break;
					}
					preg_match('/(PON\s0\/[1-8]|PON0\/[1-8]|EPON0\/[1-8]|EPON\s\/[1-8])/', $valuePon['name'], $mat);
					$listPon[$idPonport]['name'] = 'EPON 0/' . $index;
					$listPon[$idPonport]['sort'] = $index;
					$listPon[$idPonport]['sfpid'] = $valuePon['id'];
					$listPon[$idPonport]['cardcount'] = 64;
					$index++;
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
			$row = $this->db->Fast('switch_pon','*',['oltid' => $this->id,'sfpid' => $dataPort['sfpid']]);
			if(!$row)
				$this->db->SQLinsert('switch_pon',['support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'added' => $this->now]);
			$allonu = $this->db->Simple('Select count(idonu) as count FROM onus WHERE olt = '.$this->id.'
			AND zte_idport = '.$dataPort['sort']);
			if(!empty($dataPort['sfpid']) && isset($allonu['count'])){
				$SQLportset['count'] = $allonu['count'];
				$this->db->SQLupdate('switch_pon',$SQLportset,['sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
				$this->db->SQLupdate('onus',['portolt' => $dataPort['sfpid']],['olt' => $this->id,'zte_idport' => $dataPort['sort']]);
			}
		}else{
			
		}
	}
    protected function savePortSwitch($data) {
		if(!empty($data['id'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(!empty($row['id'])){
				
			}else{
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
			}
		}
	}
	public function tempUpdateSignalCheck(){	
		global $db;

	}
	public function tempSaveSignalSaveOnuEpon($dataOnu){	
		global $config;
		$savehistor = $savehistor ?? null;
		$onu = $this->db->Fast('onus','status,rx,idonu,inface,mac,sn,changerx,olt',['olt' => $this->id,'keyonu' => $dataOnu['keyonu'],'zte_idport' => $dataOnu['keyport']]);
		if(!empty($onu['idonu'])){
			$rx = $rx ?? null;
			if(!empty($dataOnu['rx'])){
				$rx = $this->signalVsol($dataOnu['rx']);
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
		$value = str_replace('NULL', '',$value);
		$value = str_replace(' ', '',$value);
		$value = trim($value);	
		return $value;
	}
	public function tempSaveOnuEpon($dataOnu){	
		$dataOnu['status'] = (!empty($dataOnu['status']) ? $this->statusVsol($dataOnu['status']): 2);
		$dataOnu['name'] = (!empty($dataOnu['name']) ? $this->nameVsol($dataOnu['name']):"");
		$dataOnu['inface'] = (!empty($dataOnu['inface']) ? $this->infaceVsol($dataOnu['inface']):"");
		if(!empty($dataOnu['keyonu']) && !empty($dataOnu['keyport'])){
			$arr = $this->db->Fast('onus','*',['zte_idport' =>$dataOnu['keyport'],'keyonu' =>$dataOnu['keyonu'],'olt' => $dataOnu['id']]); 
			if(!empty($arr['idonu'])){
				$SQLset['updates'] = $this->now;
				if($dataOnu['status']==1 && $arr['status']==2){
					$SQLset['online'] = $this->now;
				}elseif($dataOnu['status']==2 && $arr['status']==1){
					$SQLset['offline'] = $this->now;
				}else{
					
				}
				$SQLset['mac'] = $dataOnu['mac'];				
				$SQLset['status'] = $dataOnu['status'];
				$SQLset['type'] = $dataOnu['pon'];
				if(!empty($dataOnu['dist']))
					$SQLset['dist'] = $dataOnu['dist'];
				if(!empty($dataOnu['inface']))
					$SQLset['inface'] = $dataOnu['inface'];
				if(!empty($dataOnu['name']))
					$SQLset['name'] = $dataOnu['name'];
				if(!empty($arr['portolt'])){
					$SQLset['portolt'] = $arr['portolt'];
				}else{					
					$SQLset['portolt'] = $dataOnu['keyport'];
				}
				if(!empty($arr['zte_idport'])){
					$SQLset['zte_idport'] = $arr['zte_idport'];
				}
				$this->db->SQLupdate('onus',$SQLset,['idonu' => $arr['idonu']]);
			}else{			
				$SQLinsert['olt'] = $dataOnu['id'];
				if(!empty($dataOnu['inface']))
					$SQLinsert['inface'] = $dataOnu['inface'];
				$SQLinsert['added'] = $this->now;
				$SQLinsert[($dataOnu['status']==1?'online':'offline')] = $this->now;
				$SQLinsert['updates'] = $this->now;
				$SQLinsert['keyonu'] = $dataOnu['keyonu'];
				$SQLinsert['status'] = $dataOnu['status'];
				$SQLinsert['mac'] = $dataOnu['mac'];				
				$SQLinsert['portolt'] = $dataOnu['keyport'];
				$SQLinsert['zte_idport'] = $dataOnu['keyport'];
				$SQLinsert['type'] = $dataOnu['pon'];
				$SQLinsert['dist'] = (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0);
				$this->db->SQLinsert('onus',$SQLinsert);
				$idonu = $this->db->getInsertId();
				$this->logger->init(['log'=>'onu','type'=>'addonu','descr'=>$dataOnu['inface'].' '.(!empty($dataOnu['sn'])?$dataOnu['sn']:'--'),'deviceid'=>$dataOnu['id'],'onuid'=>$idonu,'who'=>'cron']);
			}
		}
	}
	public function StatisticOLT(){	
		$getPort = $this->db->Multi('switch_pon','*',['oltid' => $this->id]);
		if(isset($getPort) && count($getPort)>0){
			foreach($getPort as $port){
				$getONUstatusPort = $this->db->Multi('onus','idonu,status',['olt' => $this->id,'portolt'=>$port['sfpid']]);
				$getONUstatusPortOn = $this->db->Multi('onus','idonu,status',['status' => 1,'olt' => $this->id,'portolt'=>$port['sfpid']]);
				$arrayPort[$port['id']]['port'] = $port['pon'];
				$arrayPort[$port['id']]['count'] = count($getONUstatusPort);
				$arrayPort[$port['id']]['online'] = count($getONUstatusPortOn);
				$arrayPort[$port['id']]['offline'] = count($getONUstatusPort) - count($getONUstatusPortOn);
			}
		}
		if(isset($arrayPort) && count($arrayPort)>0){
			foreach($arrayPort as $idport => $value){
				$this->db->SQLupdate('switch_pon',['count' => ($value['count'] ?? 0),'online' => ($value['online'] ?? 0),'offline' => ($value['offline'] ?? 0)],['id' => $idport]);
			}
		}
	}
	public function getListOnuOnline(){	
		$sqlList = $this->db->Multi('onus','keyonu,idonu,type,zte_idport',['olt' => $this->id, 'status' => 1]);
		$array = [];
		if(is_array_empty($sqlList)){
			foreach($sqlList as $key => $value){
				if(!empty($value['keyonu']))
					$array[$key] = [
						'id'=>$this->id,
						'keyonu'=>$value['keyonu'],
						'keyport'=>$value['zte_idport'],
						'pon'=>$value['type'],
						'do'=>'onu',
						'types'=>'rx'
					];
			}
		}
		return $array;
	}	
}
?>