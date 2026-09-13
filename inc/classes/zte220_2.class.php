<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class ZTE_c220_2 { 
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
    private $listpoweroff = array();
    private $zte_list_port = array();
    private $now;
    private $primarygpon = 'dist,status,reason,name';
    private $primaryepon = 'dist,status,reason';
    private $configapionugpon = 'status,dist,reason,model,eth,tempendor';
    private $pollergpon = 'status,reason,rx,inface';
    private $configapionugponget = 'rx,tx,status,dist,reason,model,eth,tempendor';
    private $configapionuepon = 'status,dist,config,reason,model,eth,temp,offline,vendor,device,vlanmode';
	private $pollerepon = 'status,reason,rx';
    private $configapionueponget= 'rx,status,dist,config,reason,model,eth,temp,offline,vendor,device,vlanmode';
    private $filter = true;
	public function Support($check){
		return match ($check){
			'port', 'onu', 'saveonu', 'poller', 'api', 'fileonu' => true,	default => false,
		};
	}
    public function __construct($swid,$equipment, $db, $logger){
		$this->db = $db;
		$this->logger = $logger;
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
	public function epon_mac($tempmac) {
		if (strpos($tempmac,'Hex') !== false){
			$tempmac = preg_replace('~^.*?( = )~i','',$tempmac);
			$tempmac = preg_replace('/Hex-STRING/','',$tempmac);
			$tempmac = preg_replace('/ : /','',$tempmac);
			$tempmac = preg_replace('/ /','',$tempmac);
			$tempmac = trim($tempmac, " \"");
			$tempmac = trim($tempmac, '"');
			$tempmac = stripslashes($tempmac);
			$tempmac = strtolower($tempmac);
			return substr(preg_replace('/(.{2})/','\1:',$tempmac,6), 0, -1);
		}else{
			$tempmac = preg_replace('~^.*?( = )~i','',$tempmac);
			$tempmac = preg_replace('/STRING/','',$tempmac);
			$tempmac = preg_replace('/ : /','',$tempmac);
			$tempmac = trim($tempmac, " \"");
			$tempmac = trim($tempmac, '"');
			$tempmac = stripslashes($tempmac);	
			$tempmac = bin2hex($tempmac);
			$tempmac = strtolower($tempmac);
			return substr(preg_replace('/(.{2})/','\1:',$tempmac,6), 0, -1);
		}
	}
	public function epon_convert($index) {
		$ifIndex = str_pad(decbin($index), 32, '0', STR_PAD_LEFT);
		$shelf_no  = bindec(substr($ifIndex, 4, 4));
		$slot_no = bindec(substr($ifIndex, 8, 5));
		$port_no = bindec(substr($ifIndex, 13, 3))+1;
		$ont_no = bindec(substr($ifIndex, 16, 8));    
		return $shelf_no.'/'.$slot_no.'/'.$port_no.':'.$ont_no;
	}
	public function epon_convert2($llid) {
		if(is_numeric($llid)){
			$lx = sprintf("%08x",$llid);
			switch ($lx[0]) {
				case '1':
					$sh = hexdec($lx[1])+1;
					$sl = hexdec($lx[2].$lx[3]);
					$ol = hexdec($lx[4].$lx[5]);
				break;
				case '2':
					$sh = hexdec($lx[3]);
					$sl = hexdec($lx[4].$lx[5]);
					$ol = hexdec($lx[6].$lx[7]);
					if($cl>16){
						$cl-=16; $sl++;
					}
					$ol1 = $ol;
				break;
				case '3':
					$sh = hexdec($lx[1])+1;
					$sl = hexdec($lx[2].$lx[3]);
					$ol = ($sl&0x07)+1;
					$sl = $sl>>3;
					$on = hexdec($lx[4].$lx[5]);
				break;
				case '6':
					$sh = hexdec($lx[1])+1;
					$sl = hexdec($lx[2].$lx[3]);
					$ol = 0;
				break;
			}
			if(is_numeric($on)){
				return "{$sh}/{$sl}/{$ol}:{$on}";
			}
		}
	}
	public function MacEpon($type) {
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
		$result = [];		
		$resultepon = [];		
		$resultgpon = [];	
		$port = [
			'oid' => '1.3.6.1.2.1.31.1.1.1.1','type' => 'class',
			'cache' => true,'timecache' => 3600,'namecache' => 'list_port_'.$this->id,
			'deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$this->zte_list_port = pmon_walk($port);
		sleep(1);		
		$epon = [
			'oid' => $this->deviceoid[$this->id]['onu']['listmac']['epon']['oid'],
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$eponlist = pmon_walk($epon);
		if (is_array_empty($eponlist)) {
			foreach ($eponlist as $io => $eachsig) {
				if (isset($eachsig['result']) && !empty($eachsig['result'])) {
					$mac = $this->MacEpon($eachsig['result']);
					if (isset($mac) && !empty($mac)) {
						$resultepon[$io] = [
							'do' => 'onu',
							'id' => $this->id,'checker' => md5($this->epon_convert(trim($io))),'mac' => $mac,
							'pon' => 'epon','inface' => $this->epon_convert(trim($io)),
							'types' => $this->primaryepon,'keyonu' => trim($io)
						];
					}
				}
			}
		}
		$gpon = [
			'oid' => '1.3.6.1.4.1.3902.1012.3.28.1.1.5',
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];	
		$indexonugpon = pmon_walk($gpon);
		if(is_array($indexonugpon) && isset($indexonugpon) && count($indexonugpon)>=1){
			$int_gpon = 1;
			foreach($indexonugpon as $io => $eachsig) {
				if(!empty($eachsig['result']) && isset($eachsig)) {
					preg_match('/(\d+).(\d+)/i',$io,$data_zte);
					if(!empty($data_zte[1]) && !empty($data_zte[2])){
						$resultgpon[$int_gpon] = [
							'do' => 'onu',
							'id'=>$this->id,	
							'sn'=>$this->valueToSerial($eachsig['result']),
							'pon'=>'gpon',
							'inface'=>$this->llid2s($data_zte[1],$data_zte[2]),
							'checker'=>md5($this->llid2s($data_zte[1],$data_zte[2])),
							'types'=>$this->primarygpon,
							'keyonu'=> $data_zte[2],
							'keyport'=> $data_zte[1],
							'portolt'=> $data_zte[1]
						];
						$int_gpon ++;
					}
				}	
			}
		}		
		if(is_array_empty($resultgpon)){
			checkerONUHuaweiZteGpon($resultgpon,$this->id);
		}		
		if(is_array_empty($resultepon)){
			checkerONUHuaweiZteEpon($resultepon,$this->id);
		}
		if (isemptyarray($resultepon) && isemptyarray($resultgpon)){
			$result = null;
		} else {
			$result = (isemptyarray($resultepon)) ? $resultgpon : ((isemptyarray($resultgpon)) ? $resultepon : array_merge($resultgpon, $resultepon));
		}
		if(isemptyarray($result)){
			$this->logger->init(['log'=>'device','type'=>'snmp','descr'=>'empty_snmp_walk','deviceid'=>$this->id,'who'=>'cron']);
		}
		return (is_array_empty($result) ? $result : null);
	}
	public function valueToSerial($tempsn) {
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
	public function llid2s($llid,$on) {
		$lx=sprintf("%08x",$llid);
		switch ($lx[0]) {
			case '1':
				$sh=hexdec($lx[1]);
				$sl=hexdec($lx[2].$lx[3]);
				$ol=hexdec($lx[4].$lx[5]);
				break;
			case '2':
				$sh=hexdec($lx[3]);
				$sl=hexdec($lx[4].$lx[5]);
				$ol=hexdec($lx[6].$lx[7]);
				if ($cl>16) {
					$cl-=16; $sl++;
				}
				$ol1=$ol;
				break;
			case '3':
				$sh=hexdec($lx[1])+1;
				$sl=hexdec($lx[2].$lx[3]);
				$ol=($sl&0x07)+1;
				$sl=$sl>>3;
				$on=hexdec($lx[4].$lx[5]);
				break;
			case '6':
				$sh=hexdec($lx[1])+1;
				$sl=hexdec($lx[2].$lx[3]);
				$ol=0;
				break;
		}
		return "{$sh}/{$sl}/{$ol}:{$on}";
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
			'epon' => [
				'do' => 'onu',
				'types' => $this->configapionueponget,
				'pon' => mb_strtolower($data['type']),
				'keyonu' => $data['keyonu'],
				'id' => $this->id,
			],
			default => null,
		};
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
				if(preg_match("/GPON/i",$dataOnu['type']) || preg_match("/gpon/i",$dataOnu['type']))
					$res[$type] = @$this->preparedataGpon($value,$type);
				if(preg_match("/EPON/i",$dataOnu['type']) || preg_match("/epon/i",$dataOnu['type']))
					$res[$type] = @$this->preparedataEpon($value,$type);
			}
		}
		if(!$dataPort && !$res){
			if(preg_match("/GPON/i",$dataOnu['type']) || preg_match("/gpon/i",$dataOnu['type']))
				$array_separated = explode(',',$this->configapionugpon);
			if(preg_match("/EPON/i",$dataOnu['type']) || preg_match("/epon/i",$dataOnu['type']))
				$array_separated = explode(',',$this->configapionuepon);
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
		$result = array();
		$sqlset = array();
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
			if(is_array($sqlset) && count($sqlset)>0)
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $ont['idonu']]);
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			$result['wan'] = (isset($getData['eth']) && $getData['eth']==2?'up':'down');;			
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
			if(!empty($getData['temp'])) 
				$result['temp'] = $getData['temp'];				
			if(!empty($getData['device'])) 
				$result['device'] = $getData['device'];				
			if(!empty($getData['config'])) 
				$result['config'] = $getData['config'];			
			if(!empty($getData['volt'])) 
				$result['volt'] = $getData['volt'];			
			if(!empty($getData['vlanmode'])) 
				$result['vlanmode'] = $getData['vlanmode'];			
			if(!empty($getData['bias'])) 
				$result['bias'] = $getData['bias'];
			if(!empty($getData['offline'])) 
				$result['offline'] = $getData['offline'];
			return $result;
		}
	}
	public function preparedataGpon($dataApi, $type){
		switch($type){
			case 'status':
				$result = (isset($dataApi) && $dataApi == 3) ? 1 : 2;
				break;
			case 'dist':
				$result = isset($dataApi) ? (int)$dataApi : 0;
				break;
			case 'rx':
				$result = $dataApi ? $this->clear_rx($dataApi) : 0;
				break;
			case 'tx':
				$result = $dataApi ? sprintf('%.2f', ($dataApi == 65535 ? 0 : (intval($dataApi) - 15000) * 0.002)) : 0;
				break;
			case 'sn':
			case 'vendor':
			case 'config':
			case 'mngtvlan':
			case 'model':
			case 'eth':
				$result = $dataApi ?: null;
				break;
			case 'typereg':
				$result = isset($dataApi) ? $this->typereg($dataApi) : null;
				break;
			case 'name':
			case 'note':
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
	public function preparedataEpon($dataApi,$type){
		$data = $this->clearData($dataApi);
		switch($type){
			case 'status':
				$result = (isset($data) && $data==1 ? 1 : 2);
			break;			
			case 'dist':
				$result = (isset($data)? $data : 0);
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
					$result = sprintf('%.2f',$data);
				}else{
					$result = 0;
				}
			break;
			case 'volt':
				if(preg_match('/N/i',$data)) {
					$result = 0;	
				}else{
					$result = sprintf('%.2f',$data);
				}
			break;				
			case 'bias':
				if($data){
					$result = sprintf('%.2f',$data);
				}else{
					$result = 0;
				}
			break;			
			case 'temp':
				if($data){
					$result = sprintf('%.2f',$data);
				}else{
					$result = 0;
				}
			break;	
			case 'reason':
				$result = $this->reason($data);
			break;				
			case 'vlanmode':
			case 'model':
			case 'device':
			case 'offline':
			case 'eth':
			case 'mac':
			case 'vendor':
			case 'config':
				$result = (isset($data)? $data : '');
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
			if(empty($row['id'])){
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['llid'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => $data['operstatus'],'added' => $this->now]);
			}else{
				$this->db->SQLupdate('switch_port',['nameport' => $data['name']],['id' => $row['id']]);
			}
		}
	}
	public function Port(){
		$data = array();
		$listport = array();
		if(empty($this->zte_list_port)){
			$port = [
				'oid' => '1.3.6.1.2.1.31.1.1.1.1',
				'type' => 'real','deloid' => true,'ip' => $this->ip,'community'=> $this->community
			];
			sleep(5);
			$indexarrayport = pmon_walk($port);	
		}else{
			$indexarrayport = $this->zte_list_port;
		}
		if(is_array($indexarrayport) && isset($indexarrayport) && count($indexarrayport)>=0){
			foreach ($indexarrayport as $idport => $dataport) {
				if (!empty($dataport['result'])) {
					$idport = trim($idport);
					$portstatus = $this->snmp->get($this->ip, $this->community, '1.3.6.1.2.1.2.2.1.8.' . $idport);
					$status = '';
					if (preg_match('/INTEGER/i', $portstatus)) {
						preg_match('/INTEGER(.*)/', $portstatus, $mat);
						$status = trim(str_replace('"', '', str_replace(':', '', $mat[1])));
					} else {
						$status = trim(str_replace('"', '', str_replace('INTEGER:', '', $portstatus)));
					}
					$portswitch = trim(str_replace('"', '', str_replace('STRING:', '', $dataport['result'])));
					$listport[$idport] = [
						'operstatus' => ($status == 1 ? 'up' : 'down'),
						'id' => $idport,
						'typeport' => getTypePort($portswitch),
						'name' => (isset($portswitch) ? $portswitch : '')
					];
				}
			}
			$data['port'] = $listport;
		}
		$listpon = [];
		if(isset($listport) && is_array($listport)){
			$sort_epon = 1;
			$sort_gpon = 1;
			foreach($listport as $idponport => $datapon){
				if(preg_match('/epon/i',mb_strtolower($datapon['name']))) {
					preg_match('/_(\d+)\/(\d+)\/(\d+)/',$datapon['name'],$mat);
						$listpon[$idponport] = [
							'name' => 'EPON '.$mat[1].'/'.$mat[2].'/'.$mat[3],
							'sort' => $sort_epon,
							'operstatus' => $datapon['operstatus'],
							'sfpid' => $datapon['id'],
							'llid' => $datapon['id'],
							'typeport' => 'epon',
							'cardcount' => 64
						];
					$sort_epon ++;
				}elseif(preg_match('/gpon/i',mb_strtolower($datapon['name']))){
					preg_match('/_(\d+)\/(\d+)\/(\d+)/',$datapon['name'],$mat);
					$slot = $mat[1];
						$listpon[$idponport] = [
							'name' => 'GPON '.$slot.'/'.$mat[2].'/'.$mat[3],
							'sort' => $sort_gpon,
							'operstatus' => $datapon['operstatus'],
							'sfpid' => $datapon['id'],
							'llid' => $datapon['id'],
							'typeport' => 'gpon',
							'cardcount' => 128
						];
					$sort_gpon ++;	
				}
			}
			if(is_array_empty($listpon)){
				usort($listpon, function($arr, $brr){
					return ($arr['sort'] - $brr['sort']);	
				});
				$data['pon'] = $listpon;
			}
		}
		return (is_array_empty($data) ? $data : null);
	}
    protected function savePortSwitch($data) {
		global $db;
		if(!empty($data['id'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(empty($row['id'])){
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'operstatus' => (!empty($data['operstatus']) ? $data['operstatus'] : ''),'nameport' => $data['name'],'typeport' => $data['typeport'],'added' => $this->now]);
			}else{
				$this->db->SQLupdate('switch_port',['operstatus' => (!empty($data['operstatus']) ? $data['operstatus'] : '')],['id' => $row['id']]);
			}
		}
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Fast('switch_pon','*',['oltid' => $this->id,'sfpid' => $dataPort['sfpid']]);
		if(!empty($row['id'])){
			$this->db->SQLupdate('switch_pon',['pon' => $dataPort['name']],['id' => $row['id']]);
		}else{
			$this->db->SQLinsert('switch_pon',['idportolt' => $dataPort['sort'],'type' => $dataPort['typeport'],'support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'added' => $this->now]);
		}
		if(!empty($dataPort['sfpid']) && !empty($dataPort['sort'])){		
			$inf = str_replace('EPON ', '',$dataPort['name']);
			preg_match('/(\d+)\/(\d+)\/(\d+)/',$inf,$mat);
			$get = array('sw_shelf' => ($mat[1]),'sw_slot' => $mat[2],'sw_port' => $mat[3],'olt' => $this->id);			
			$this->db->SQLupdate('onus',['portolt' => $dataPort['sfpid']],$get);
		}
		$allonu = $this->db->Multi('onus','*',['olt' => $this->id,'portolt' => $dataPort['sfpid']]);
		if(!empty($dataPort['sfpid'])){
			$this->db->SQLupdate('switch_pon',['count' =>count($allonu),'type' => $dataPort['typeport']],['sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
		}
	}
	public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $config;
		$savehistor = false;
		if(isset($this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])&& !empty($this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])){
			$onu = $this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']];
		}else{
			$onu = $this->db->Fast('onus','status,rx,idonu,inface,mac,sn,changerx,olt',['olt' => $this->id,'portolt' => $dataOnu['keyport'],'keyonu' => $dataOnu['keyonu']]);
		}
		if(!empty($onu['idonu'])){			
			$rx = $rx ?? null;
			if(!empty($dataOnu['rx'])){
				$rx = $this->clearGponrx($dataOnu['rx']);
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
	public function tempSaveSignalSaveOnuEpon($dataOnu){	
		global $config;
		$savehistor = false;	
		if(isset($this->cache_onu_epon[$dataOnu['keyonu']])&& !empty($this->cache_onu_epon[$dataOnu['keyonu']])){
			$onu = $this->cache_onu_epon[$dataOnu['keyonu']];
		}else{
			$onu = $this->db->Fast('onus','status,rx,idonu,inface,mac,sn,changerx,olt',['olt' => $this->id,'keyonu' => $dataOnu['keyonu']]);
		}	
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
	public function check_signal($value){
		if (preg_match('/\b214748\b/i', $value)) {
			return 0;
		} elseif(preg_match('/65535/i',$value)) {
			return 0;
		} else{
			return sprintf("%.2f",$value);			
		}
	}	
	public function checkGponsignal($value){
		if (preg_match('/\b214748\b/i', $value)) {
			return 0;
		} elseif(preg_match('/65535/i',$value)) {
			return 0;
		} else{
			return $value ? sprintf('%.2f', ($value == 65535 ? 0 : (intval($value) - 15000) * 0.002)) : 0;		
		}
	}
	public function clear_rx2($onu_rx){
		if ($onu_rx*1 <30001) {
			$onu_rx = $onu_rx*0.002 - 30.0;
		} else {
			if ($onu_rx*1 < 665535)
				$onu_rx=($onu_rx-65535)*0.002 - 30.0;
			else $onu_rx = 0;
		}
		return sprintf("%.2f",$onu_rx);	
	}
	public function clear_rx($value){
		if(preg_match('/\b214748\b/i', $value)) {
			return 0; 
		}else{
			return $this->check_signal($value);
		}
	}	
	public function clearGponrx($value){
		if(preg_match('/6553/i',$value)) {
			return 0; 
		}else{
			return $this->checkGponsignal($value);
		}
	}
	public function tempUpdateSignalCheck(){	


	}
	public function reason($check1) {
		$reasons = [
			0 => 'err1',
			1 => 'err5',
			2 => 'err6',
			3 => 'err6',
			7 => 'err25',
			9 => 'err1',
			12 => 'err0'
		];

		return $reasons[$check1] ?? null;
	}
	public function tempSaveOnuGpon($dataOnu = array()){
		$sqlset = array();		
		$onustatus = (!empty($dataOnu['status']) && $dataOnu['status']==3 ? 1 : 2);
		preg_match('/(\d+)\/(\d+)\/(\d+):/',$dataOnu['inface'],$mat);
		if(!empty($dataOnu['keyonu']) && !empty($dataOnu['keyport'])){
			$arr = $this->db->Fast('onus','*',['zte_idport' =>$dataOnu['keyport'],'keyonu' =>$dataOnu['keyonu'],'olt' => $this->id]); 
			if(!empty($arr['idonu'])){
				$this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']] = $arr;
				$sqlset = [
					'sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],
					'olt' => $this->id,'updates' => $this->now,
					'keyonu' => $dataOnu['keyonu'],'status' => $onustatus,
					'sn' => $dataOnu['sn'],	'inface' => $dataOnu['inface'],
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),
					'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon'],'zte_idport' => (!empty($arr['zte_idport']) ? $arr['zte_idport'] : $dataOnu['keyport']),
					'portolt' => (!empty($arr['portolt']) ? $arr['portolt'] : $dataOnu['keyport'])
				];
				if($dataOnu['status']==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($dataOnu['status']==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}else{
					
				}
				if(!empty($dataOnu['dist'])){
					$sqlset['dist'] = $dataOnu['dist'];
				}
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{				
				$sqlset = [
					'sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],
					'olt' => $this->id,'updates' => $this->now,'added' => $this->now, 
					($onustatus==1?'online':'offline') => $this->now,
					'keyonu' => $dataOnu['keyonu'],'status' => $onustatus,'sn' => $dataOnu['sn'],
					'inface' => $dataOnu['inface'],	'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),
					'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon'],'zte_idport' => (!empty($dataOnu['keyport']) ? $dataOnu['keyport'] : ''),
					'portolt' => (!empty($dataOnu['keyport']) ? $dataOnu['keyport'] : '')
				];
				$this->db->SQLinsert('onus',$sqlset);
			}
		}
	}	
	public function tempSaveOnuEpon($dataOnu = array()){	
		$sqlset = array();	
		$statusONU = (!empty($dataOnu['status']) && $dataOnu['status']==1 ? 1 : 2);
		preg_match('/(\d+)\/(\d+)\/(\d+):/',$dataOnu['inface'],$mat);
		if(is_numeric($dataOnu['keyonu'])){
			$arr = $this->db->Fast('onus','*',['keyonu' =>$dataOnu['keyonu'],'olt' => $this->id]); 
			if(!empty($arr['idonu'])){
				$this->cache_onu_epon[$dataOnu['keyonu']] = $arr;
				$sqlset = [
					'sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],
					'updates' => $this->now,'status' => $statusONU,'type' => $dataOnu['pon'],
					'mac' => (!empty($dataOnu['mac']) && !preg_match('/00:00:00/i', $dataOnu['mac'])) ? $dataOnu['mac'] : $dataOnu['mac'],
					'reason' => !empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : '',
					'inface' => !empty($arr['inface']) ? $arr['inface'] : $dataOnu['inface']
				];
				if(!empty($arr['portolt']))
					$sqlset['portolt'] = $arr['portolt'];	
				if(!empty($arr['zte_idport']))
					$sqlset['zte_idport'] = $arr['zte_idport'];	
				if($statusONU==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($statusONU==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}else{
					
				}
				if(!empty($dataOnu['dist'])){
					$sqlset['dist'] = $dataOnu['dist'];
				}				
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{				
				$sqlset = [
					'sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],
					'olt' => $this->id,'updates' => $this->now,'added' => $this->now, ($statusONU==1?'online':'offline') => $this->now,
					'keyonu' => $dataOnu['keyonu'],'status' => $statusONU,'mac' => $dataOnu['mac'],'inface' => $dataOnu['inface'],
					'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),
					'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon']
				];
				$this->db->SQLinsert('onus',$sqlset);
			}
		}
	}
	public function StatisticOLT(){	
		$arrayPort = [];
		$getPort = $this->db->Multi('switch_pon','*',['oltid' => $this->id]);
		if(count($getPort)){
			foreach($getPort as $port){
				$inf = str_replace('GPON ', '',$port['pon']);
				preg_match('/(\d+)\/(\d+)\/(\d+)/',$inf,$mat);
				$get = array('sw_shelf' => ($mat[1]),'sw_slot' => $mat[2],'sw_port' => $mat[3],'olt' => $this->id);
				$geton = array('status' => 1,'sw_shelf' => ($mat[1]),'sw_slot' => $mat[2],'sw_port' => $mat[3],'olt' => $this->id);
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
		$sqlList = $this->db->Multi('onus', 'keyonu,portolt,type,zte_idport', ['olt' => $this->id, 'status' => 1]);
		$array = array();
		if (is_array($sqlList)) {
			foreach ($sqlList as $key => $value) {
				if (!empty($value['keyonu']) && !empty($value['type'])) {
					if ($value['type'] == 'gpon') {
						$array[$key] = [
							'id' => $this->id, 'keyonu' => $value['keyonu'], 'keyport' => $value['zte_idport'], 'pon' => $value['type'], 'do' => 'onu', 'types' => 'rx'
						];
					} elseif ($value['type'] == 'epon') {
						$array[$key] = [
							'id' => $this->id, 'keyonu' => $value['keyonu'], 'pon' => $value['type'], 'do' => 'onu', 'types' => 'rx'
						];
					}
				}
			}
		}
		return $array ?: null;
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
}
?>