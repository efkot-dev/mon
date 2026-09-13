<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class ZTE_c600_1 { 
    private $logger;
    private $db;
	protected $snmp;
	protected $id;
	protected $ip;
	protected $oidid;
	protected $community;
	protected $deviceoid;
    private $cache_onu_gpon = array();
    private $cache_onu_epon = array();
    private $primarygpon = 'dist,status,reason,name';
    private $pollergpon = 'status,reason,name,rx';
    private $pollerepon = 'status,reason,name';
	private $now;
    private $configapionugpon = 'dist,status,reason,name,note,model,vendor,uptime';
    private $configapionugponget = 'dist,status,reason,name,note,eth,model,vendor,rx,uptime,config,mngtvlan';
	public function Support($check){
		return match ($check){
			'port', 'onu', 'saveonu', 'api', 'poller', 'rxolt', 'fileonu' => true,	default => false,
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
	public function Load(){
		$resultgpon = [];
		$indexonugpon = [];
		$gpon = [
			'oid' => '1.3.6.1.4.1.3902.1082.500.10.2.3.3.1.6',
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];		
		$indexonugpon = pmon_walk($gpon);
		if(isset($indexonugpon) && is_array($indexonugpon)){
			$int_gpon = 1;
			foreach($indexonugpon as $io => $eachsig) {
				if(!empty($eachsig['result'])) {
					preg_match('/(\d+).(\d+)/i',$io,$data_zte);
					if(!empty($data_zte[1]) && !empty($data_zte[2])){
						$resultgpon[$int_gpon] = [
							'do' => 'onu',	'id'=>$this->id,'pon'=>'gpon',
							'sn'=>$this->serialnumber($eachsig['result']),
							'inface'=>$this->inface($data_zte[1],$data_zte[2]),
							'types'=>$this->primarygpon,'keyonu'=> $data_zte[2],'keyport'=> $data_zte[1],'portolt'=> $data_zte[1]
						];
						$int_gpon ++;
					}
				}	
			}
		}
		if (!isset($resultgpon) || !is_array($resultgpon)) {
			$this->logger->init([
				'log'=> 'device','type'=> 'snmp','descr'=> 'empty_snmp_walk','deviceid' => $this->id,'who'=> 'cron'
			]);
			return null;
		}
		$this->FinderOnu($resultgpon);
		return $resultgpon;
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
			default => null,
		};
	}	
	public function ConfigApiOnuGet($data){
		return match (mb_strtolower($data['type'])) {
			'gpon' => [
				'do' => 'onu',
				'types' => $this->configapionugponget,
				'pon' => mb_strtolower($data['type']),
				'keyport' => $data['zte_idport'],
				'keyonu' => $data['keyonu'],
				'id' => $this->id,
			],
			default => null,
		};
	}
	public function Onu($dataPort,$dataOnu){
		$res = array(); 
		if(is_array_empty($dataPort) && isset($dataPort)){
			foreach($dataPort as $type => $value) {
				if(preg_match("/GPON/i",$dataOnu['type']) || preg_match("/gpon/i",$dataOnu['type']))
					$res[$type] = @$this->preparedataGpon($value,$type);
			}
		}
		if(!$dataPort && !$res){
			if(preg_match("/GPON/i",$dataOnu['type']) || preg_match("/gpon/i",$dataOnu['type']))
				$array_separated = explode(',',$this->configapionugpon);
			foreach($array_separated as $type){
				$res[$type] = (!empty($dataOnu[$type])?$dataOnu[$type]:'');
			}
		}
		if(is_array_empty($res)){
			$result = $this->updateonu($dataOnu,$res);
		}else{
			$result = null;
		}
		return (isemptyarray($result) ? null : $result);
	}
	public function updateonu($ont,$getData){
		$result = [];
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
			if(isset($sqlset) && count($sqlset)>0){
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $ont['idonu']]);
			}
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			$result['wan'] = ($getData['eth']==5?'up':'down');;			
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
	public function hexTOdate($hexstring) {
		$hexstring = str_replace(' ', '', trim($hexstring, '"'));
		$dateTimeArray = sscanf($hexstring, '%04x%02x%02x%02x%02x%02x%02x%02x');
		if (count($dateTimeArray) === 8) {
			[$year, $month, $day, $hour, $minute, $second, $dsecond] = $dateTimeArray;
			return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
		}
		return false;
	}
	public function onuRXsignal($signal){
		$signal = $signal*1;
		$signal = str_replace('-', '', trim($signal, '"'));
		if ($signal == 65535) {
			$signal = 0;
		} elseif ($signal > 30000) {
			$signal = ($signal - 65536) * 0.002 - 30;
		} else {
			$signal = $signal * 0.002 - 30;
		}
		return sprintf("%.2f", $signal);
	}
	public function onuTXsignal($signal){
		return sprintf('%.2f',$raw[$tx]*0.002-30);
	}
	public function preparedataGpon($dataApi, $type){
		switch($type){
			case 'status':
				$result = (isset($dataApi) && $dataApi == 1) ? 1 : 2;
				break;
			case 'dist':
				$result = isset($dataApi) ? (int)$dataApi : 0;
				break;
			case 'rx':
				$result = $dataApi ? $this->onuRXsignal($dataApi) : 0;
				break;
			case 'tx':
				$result = $dataApi ? $this->onuRXsignal($dataApi) : 0;
				break;
			case 'sn':
				$result = $dataApi ?: null;
				break;
			case 'model':
			case 'vendor':
			case 'eth':
				$result = $dataApi;
				break;
			case 'name':
				$result = str_replace('$', '', $dataApi);
				break;
			case 'reason':
				$result = $this->reason($dataApi);
				break;
			default:
				$result = null;
		}
		return $result;
	}
	public function savePort($dataPort){
		if(!empty($dataPort['port'])){
			foreach($dataPort['port'] as $value){
				$this->savePortSwitch($value);
			}
		}
		if(!empty($dataPort['pon']) && $this->id){
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
		if(!$row['id'])
			$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['llid'],'nameport' => $data['name'],'descrport' => (!empty($data['descr'])?$data['descr']:''),'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
		}
	}
	public function rep($kogo, $chum, $text) {
		$search = explode(',', $kogo);
		$replace = str_replace($search, $chum, $text);

		return $replace;
	}
	public function Port(){
		$listPon = [];
		$data = [];
		$listoltport = [];
		$port = [
			'oid' => '1.3.6.1.2.1.31.1.1.1.1',
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$portlist = pmon_walk($port);
		if(is_array_empty($portlist)){
			foreach($portlist as $idport => $valueport['result']) {				
				if(!empty($valueport['result'])){
					$oidport = '1.3.6.1.2.1.2.2.1.2.'.trim($idport);
					$oidoperstatus = '1.3.6.1.2.1.2.2.1.8.'.trim($idport);
					$portname = $this->snmp->get($this->ip,$this->community,$oidport);
					$operstatus = $this->snmp->get($this->ip,$this->community,$oidoperstatus);
					$name_real_port = $this->rep('",STRING: ,= ','',$valueport['result']);
					$listoltport[$idport] = [
						'descrport' =>(isset($portname) ? trim($this->rep('",STRING: ,= ,'.$oidport,'',$portname)):''),
						'operstatus' =>(isset($operstatus) ? trim($this->rep('",INTEGER: ,= ,'.$oidoperstatus,'',$operstatus)):''),
						'id' =>trim($idport),
						'typeport' => getTypePort($name_real_port['result']),
						'name' => getNameZteport($name_real_port['result'])
					];
				}				
			}
			$data['port'] = $listoltport;
		}
		if(is_array($data['port'])){
			$sort = 1;
			foreach($data['port'] as $idp => $valuePon){
				if(preg_match('/GPON/i',$valuePon['name'])) {
					$listPon[$idp] = [
						'name' => $valuePon['name'],
						'sort' => $sort,
						'descrport' => $valuePon['descrport'],
						'sfpid' => $valuePon['id'],
						'llid' => $valuePon['id'],
						'typeport' => $valuePon['typeport'],
						'cardcount' => 128
					];
					$sort++;
				}
			}
			if(is_array($listPon)){
				usort($listPon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data['pon'] = $listPon;
			}
		}
		return (is_array($data) ? $data : null);
	}
    protected function savePortSwitch($data) {
		if(!empty($data['id'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(empty($row['id'])){
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'descrport' => (!empty($data['descrport']) ? $data['descrport'] : ''),'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => (isset($data['operstatus']) ? ($data['operstatus']==1?'up':'down') : 'none'),'added' => $this->now]);
			}else{
				$this->db->SQLupdate('switch_port',['operstatus' => (isset($data['operstatus']) ? ($data['operstatus']==1?'up':'down') : 'none'),'descrport' => (!empty($row['descrport']) ? $row['descrport'] : (!empty($data['descrport'])?$data['descrport']:''))],['id' => $row['id']]);
			}
		}
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Simple('SELECT count(id) as count_this FROM `switch_pon` WHERE oltid = '.$this->id.' AND sfpid = '.$dataPort['sfpid']);
		if ($row['count_this'] == 0) {
			$this->db->SQLinsert('switch_pon',['idportolt' => $dataPort['sort'],'support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'added' => $this->now]);
		}
		if(!empty($dataPort['sfpid']) && !empty($dataPort['sort'])){		
			$inf = $this->rep('GPON ,XPON ,EPON ','',$dataPort['name']);
			preg_match('/(\d+)\/(\d+)\/(\d+)/',$inf,$mat);
			if (is_numeric($mat[1]) && is_numeric($mat[2]) && is_numeric($mat[3])) {
				$get = [
					'sw_shelf' => $mat[1],
					'sw_slot' => $mat[2],
					'sw_port' => $mat[3],
					'olt' => $this->id
				];			
				$this->db->SQLupdate('onus',['portolt' => $dataPort['sfpid']],$get);
			}
		}
		if(!empty($dataPort['sfpid'])){
			$onus = $this->db->Simple('SELECT count(*) as count_onus FROM `onus` WHERE olt = '.$this->id.' AND portolt = '.$dataPort['sfpid']);
			$this->db->SQLupdate('switch_pon',['count' =>$onus['count_onus']],['sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
		}
	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $config;
		$savehistor = $savehistor ?? null;
		if(isset($this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])&& !empty($this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])){
			$onu = $this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']];
		}else{
			$onu = $this->db->Fast('onus','status,rx,idonu,inface,mac,sn,changerx,olt',['olt' => $this->id,'portolt' => $dataOnu['keyport'],'keyonu' => $dataOnu['keyonu']]);
		}		
		if(!empty($onu['idonu'])){
			$rx = $rx ?? null;
			if(!empty($dataOnu['rx'])){
				$rx = $this->onuRXsignal($dataOnu['rx']);
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
	public function tempUpdateSignalCheck(){	

	}
	public function tempSaveOnuGpon($dataOnu = array()){	
		$onustatus = (!empty($dataOnu['status']) && $dataOnu['status']==1 ? 1 : 2);
		preg_match('/(\d+)\/(\d+)\/(\d+):/',$dataOnu['inface'],$mat);
		if(!empty($dataOnu['keyonu']) && !empty($dataOnu['keyport'])){
			$arr = $this->db->Fast('onus', '*', [
				'zte_idport' => $dataOnu['keyport'],
				'keyonu'     => $dataOnu['keyonu'],
				'olt'        => $dataOnu['id']
			]);
			if (!empty($arr['idonu'])) {
				$this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']] = $arr;
				$now = $this->now;
				$newData = [
					'sw_shelf'   => $mat[1],
					'sw_slot'    => $mat[2],
					'sw_port'    => $mat[3],
					'olt'        => $dataOnu['id'],
					'keyonu'     => $dataOnu['keyonu'],
					'status'     => $onustatus,
					'sn'         => $dataOnu['sn'],
					'inface'     => $dataOnu['inface'],
					'dist'       => !empty($dataOnu['dist']) ? $dataOnu['dist'] : (!empty($arr['dist']) ? $arr['dist'] : 1),
					'name'       => !empty($dataOnu['name']) ? $dataOnu['name'] : '',
					'reason'     => !empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : '',
					'type'       => $dataOnu['pon'],
					'zte_idport' => !empty($arr['zte_idport']) ? $arr['zte_idport'] : $dataOnu['keyport'],
					'portolt'    => !empty($arr['portolt']) ? $arr['portolt'] : $dataOnu['keyport'],
					'updates'    => $now
				];
				if ($onustatus == 1 && $arr['status'] == 2) {
					$newData['online'] = $now;
				} elseif ($onustatus == 2 && $arr['status'] == 1) {
					$newData['offline'] = $now;
				}
				$sqlset = [];
				foreach ($newData as $key => $value) {
					if ($arr[$key] != $value) {
						$sqlset[$key] = $value;
					}
				}
				if (!empty($sqlset)) {
					$sqlset['updates'] = $now;
					$this->db->SQLupdate('onus', $sqlset, ['idonu' => $arr['idonu']]);
				}
			}
			else{				
				$sqlinsert = [
					'sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],
					'olt' => $dataOnu['id'],'updates' => $this->now,'added' => $this->now, 
					($onustatus==1?'online':'offline') => $this->now,
					'keyonu' => $dataOnu['keyonu'],'status' => $onustatus,
					'sn' => $dataOnu['sn'],'inface' => $dataOnu['inface'],
					'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),
					'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon'],
					'zte_idport' => (!empty($dataOnu['keyport']) ? $dataOnu['keyport'] : ''),
					'portolt' => (!empty($dataOnu['keyport']) ? $dataOnu['keyport'] : '')
				];
				$this->db->SQLinsert('onus',$sqlinsert);
			}
		}
	}
	public function StatisticOLT(){	
		$getPort = $this->db->Multi('switch_pon','*',['oltid' => $this->id]);
		if(count($getPort)){
			foreach($getPort as $port){
			$inf = str_replace('GPON ', '',$port['pon']);
			preg_match('/(\d+)\/(\d+)\/(\d+)/',$inf,$mat);
				$get = array('sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],'olt' => $this->id);
				$geton = array('status' => 1,'sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],'olt' => $this->id);
				$getONUstatusPort = $this->db->Multi('onus','idonu,status',$get);
				$getONUstatusPortOn = $this->db->Multi('onus','idonu,status',$geton);
				$arrayPort[$port['id']] = [
					'port' => $port['pon'],
					'count' => count($getONUstatusPort),
					'online' => count($getONUstatusPortOn),
					'offline' => count($getONUstatusPort) - count($getONUstatusPortOn)
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
		$sqlList = $this->db->Multi('onus', 'keyonu,portolt,type', ['olt' => $this->id, 'status' => 1]);
		$array = array();
		if (is_array($sqlList)) {
			foreach ($sqlList as $key => $value) {
				if (!empty($value['keyonu']) && !empty($value['type'])) {
					if ($value['type'] == 'gpon') {
						$array[$key] = [
							'id' => $this->id, 'keyonu' => $value['keyonu'], 'keyport' => $value['portolt'], 'pon' => $value['type'], 'do' => 'onu', 'types' => 'rx'
						];
					}
				}
			}
		}
		return $array ?: null;
	}
	public function inface($index,$onuid) {
		$port = $index & 0xff;
		$slot = ($index >> 8) & 0xff;	
		return '1/'.$slot.'/'.$port.':'.$onuid;		
	}	
	public function portconvert($index) {
		$port = $index & 0xff;
		$slot = ($index >> 8) & 0xff;	
		return '1/'.$slot.'/'.$port;		
	}
	public function serialnumber($tempsn) {
		$return = '';
		$tempsn = preg_replace('~^.*?( : )~i','',$tempsn);
		$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
		$sn = str_replace(['Hex-STRING', 'STRING', 'Hex-', ': ', '\x'], '', $tempsn);
		if (strlen($sn) === 24){
			$sn = explode(" ", $sn);
			foreach ($sn as $key => $value){
				if ($key < 4) {
					$return.=chr(hexdec($value));   // Первые 4 символа переводятся из HEX->DEC, и подставляется в ASCII таблицу
				}else{
					$return.=$value;                // Остальные символы неизменны
				}
			}
		}else{
			$nosn = substr($sn,4);
			$resn = substr($sn, 0, 4);
			$return = $resn.strtoupper(bin2hex($nosn));
		}
		// [sn] => HWT436A52774322
		if (strlen($return) === 15 || preg_match('/HWT43/i', $return)) {
			$return = substr($return, 0, -2);
			$return = str_replace('HWT43', 'HWTC', $return);
		}
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
	public function reason($err) {
		$err = trim($err);		
		$reasons = [
			'1' => 'err5',
			'2' => 'err6',
			'3' => 'err6',
			'4' => 'err37',
			'5' => 'err16',
			'6' => 'err39',
			'7' => 'err40',
			'8' => 'err22',
			'9' => 'err1',
			'10' => 'err55',
			'11' => 'err56',
			'12' => 'err12',
			'13' => 'err0',
			'14' => 'err57',
			'15' => 'err58',
		];
		return $reasons[$err] ?? 'err0';
	}
	public function getOnuPoller($value){	
		$array = array();
		$types = ($value['type'] === 'gpon') ? $this->pollergpon : $this->pollerepon;
		if ($value['type'] == 'gpon') {
			$array = ['id' => $this->id, 'keyonu' => $value['keyonu'], 'keyport' => $value['zte_idport'], 'pon' => $value['type'], 'do' => 'onu', 'types' => $types
			];
		} elseif ($value['type'] == 'epon') {
			$array = ['id' => $this->id, 'keyonu' => $value['keyonu'], 'pon' => $value['type'], 'do' => 'onu', 'types' => $types
			];
		}
		return $array ?: null;
	}
	public function tempSaveSignalSaveRxOnuGpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->onuoltRXsignal($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}
	public function onuoltRXsignal($signal){
		$signal = $signal*1;
		$signal = str_replace('-', '', trim($signal, '"'));
		if ($signal == 80000) {
			$signal = 0;
		} else {
			$signal = $signal/1000;
		}
		return sprintf("%.2f", $signal);
	}
	public function getOnuRxPoller($value){	
		$array = array();
		if(!empty($value['keyonu']) && !empty($value['type']) && !empty($value['idonu'])){
			$array = [
				'id' => $this->id, 
				'keyonu' => $value['keyonu'], 
				'keyport' => $value['zte_idport'], 
				'pon' => $value['type'], 
				'do' => 'onu', 
				'types' => 'rxolt'
			];			
		}
		return $array ?: null;
	}
public function FinderOnu($data_new_onu){
		$oldonui = [];
		$getonus = $this->db->Multi('onus', 'idonu,sn,keyonu,inface,type,zte_idport,olt', ['olt' => $this->id,'type'=>'gpon']);
		if(isset($getonus) && count($getonus)>0) {
			foreach ($getonus as $io => $eachsig) {
				if(!empty($eachsig['sn'])){
					$oldonui[$eachsig['idonu']] = [
						'idonu' => $eachsig['idonu'],
						'sn' => $eachsig['sn'],
						'inface' => $eachsig['inface'],
						'keyonu' => $eachsig['keyonu'],
						'pon' => $eachsig['type'],
						'keyport' => $eachsig['zte_idport'],
						'olt' => $eachsig['olt']
					];
				}
			}
		}
		if(isset($oldonui) && is_array($oldonui)){
			$getdeletonu = $this->finder_zte_gpon($data_new_onu, $oldonui);
			if(isset($getdeletonu['notMatched']) && is_array($getdeletonu['notMatched'])){
				foreach($getdeletonu['notMatched'] as $io => $each) {
					delete_onu($each['idonu']);
					$logs = [
						'log'=>'onu','type'=>'deletonu','descr'=>'GPON '.$each['inface'],'deviceid'=>$this->id,'onuid'=>$each['idonu'],'who'=>'clear',
					];
					$this->logger->init($logs);
				}
			}
		}
	}
	public function finder_zte_gpon(array $newonu, array $oldonu): array {
		$newIndex = [];
		foreach ($newonu as $onu) {
			$key = strtolower(trim($onu['sn'])) . '|' . intval($onu['keyonu']) . '|' . intval($onu['keyport']) . '|' . trim($onu['inface']);
			$newIndex[$key] = true;
		}
		$matched = [];
		$notMatched = [];
		$seenKeys = [];
		foreach ($oldonu as $onu) {
			$key = strtolower(trim($onu['sn'])) . '|' . intval($onu['keyonu']) . '|' . intval($onu['keyport']) . '|' . trim($onu['inface']);
			if (isset($seenKeys[$key])) {
				$notMatched[] = $onu;
				continue; 
			}
			$seenKeys[$key] = true;
			if (isset($newIndex[$key])) {
				$matched['unique'][] = $onu;
			} else {
				$notMatched[] = $onu;
			}
		}
		return [
			'matched' => $matched,'notMatched' => $notMatched,
		];
	}
}
?>