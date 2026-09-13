<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class ZTE_c320_1 { 
    private $logger;
    private $db;
	protected $snmp;
	protected $id;
	protected $ip;
	protected $oidid;
    private $cache_onu_gpon = array();
    private $cache_onu_epon = array();
	protected $community;
	protected $deviceoid;
    private $primarygpon = 'dist,status,reason,name';
    private $primaryepon = 'dist,status,name';
	private $now;
    private $configapionugpon = 'dist,status,reason,name,note,model,vendor,uptime,typereg,config,mngtvlan';
	private $pollergpon = 'status,dist,rx,reason';
    private $configapionugponget = 'dist,status,reason,name,note,tx,rx,model,vendor,uptime,typereg,config,mngtvlan';
    private $configapionuepon = 'dist,status,vendor,model,reason';
	private $pollerepon = 'status,dist,rx';
    private $configapionueponget = 'dist,status,rx,adminstatus,vendor,model,tx,regtime,eth';
    private $filter = true;
    private $filters = ['/"/','/Hex-/i','/OID: /i','/STRING: /i','/Gauge32: /','/INTEGER: /i','/Counter32: /i','/SNMPv2-SMI::enterprises\./i','/iso\.3\.6\.1\.4\.1\./i'];
	public function Support($check){
		return match ($check){
			'port', 'onu', 'saveonu', 'api','rxolt', 'poller', 'fileonu' => true,	default => false,
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
	public function llid2s($llid,$on) {
		$lx=sprintf("%08x",$llid);
		switch ($lx[0]) {
			case '1':
				$sh=hexdec($lx[1])+1;
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
		if (strlen($return) === 15){
			$return = substr($return, 0, -2);
		}
		$return = str_replace('HWT43', 'HWTC', $return);
        return $this->cleanString($return);
	}
	private function valueToSerial2($tempsn) {
		if (strpos($tempsn,'Hex-STRING') !== false){
			$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
			$tempsn = preg_replace('/Hex-STRING/','',$tempsn);
			$tmpv = explode(" ",$tempsn);
			$val1 = hexdec($tmpv[1]);
			$val2 = hexdec($tmpv[2]);
			$val3 = hexdec($tmpv[3]);
			$val4 = hexdec($tmpv[4]);
			$val5 = $tmpv[5];
			$val6 = $tmpv[6];
			$val7 = $tmpv[7];
			$val8 = $tmpv[8];
			return chr($val1).chr($val2).chr($val3).chr($val4).$val5.$val6.$val7.$val8;
		}else{
			$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
			$onu_snc1 = preg_replace ('/STRING:/','',$tempsn);
			$tmpv = explode(" ","$onu_snc1");
			$tmpe = str_split($tmpv[1]);
			return @$tmpe[1].$tmpe[2].$tmpe[3].$tmpe[4].strtoupper(dechex(ord($tmpe[5]))).strtoupper(dechex(ord($tmpe[6]))).strtoupper(dechex(ord($tmpe[7]))).strtoupper(dechex(ord($tmpe[8])));
		}
	}	
	public function epon_convert($index) {
		$ifIndex = str_pad(decbin($index), 32, '0', STR_PAD_LEFT);
		$shelf_no = bindec(substr($ifIndex, 4, 4))+1;
		$slot_no = bindec(substr($ifIndex, 8, 5));
		$port_no = bindec(substr($ifIndex, 13, 3))+1;
		$ont_no = bindec(substr($ifIndex, 16, 8));    
		return $shelf_no.'/'.$slot_no.'/'.$port_no.':'.$ont_no;
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
		$resultgpon = [];
		$resultepon = [];
		$indexonugpon = [];
		$indexonuepon = [];
		$gpon = [
			'oid' => $this->deviceoid[$this->id]['onu']['listsn']['gpon']['oid'],
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];		
		$epon = [
			'oid' => '1.3.6.1.4.1.3902.1015.1010.1.1.1.1.1.4',
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$indexonuepon = pmon_walk($epon);
		if(is_array($indexonuepon) && isset($indexonuepon) && count($indexonuepon)>1){
			$int_epon = 1;
			foreach($indexonuepon as $infacex => $data_epon) {
				$mac = $this->MacEpon($data_epon['result']);
				if($infacex && !empty($data_epon['result']) && $mac) {
					$resultepon[$int_epon] = [
						'do' => 'onu','id' => $this->id,'pon' => 'epon',
						'types' => $this->primaryepon,'mac' => $mac,
						'inface' => $this->epon_convert(trim($infacex)),
						'keyonu' => trim($infacex)
					];
					$int_epon ++;
				}
			}
		}
		$indexonugpon = pmon_walk($gpon);
		if(is_array($indexonugpon) && isset($indexonugpon) && count($indexonugpon)>1){
			$int_gpon = 1;
			foreach($indexonugpon as $io => $eachsig) {
				if(!empty($eachsig['result'])) {
					preg_match('/(\d+).(\d+)/i',$io,$data_zte);
					if(!empty($data_zte[1]) && !empty($data_zte[2])){
						$resultgpon[$int_gpon] = [
							'do' => 'onu',
							'id'=>$this->id,	
							'sn'=>$this->valueToSerial($eachsig['result']),
							'pon'=>'gpon',
							'inface'=>$this->llid2s($data_zte[1],$data_zte[2]),
							'types'=>$this->primarygpon,
							'keyonu'=> $data_zte[2],
							'keyport'=> $data_zte[1],
							'portolt'=> $data_zte[1]
						];
						$int_gpon++;
					}
				}	
			}
		}
		if(is_array_empty($resultgpon)){
			perevirka_ONU_zte_gpon($resultgpon,$this->id);
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
		$sqlset = array();
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
				$sqlset['dist'] = $this->dist($getData['dist']);
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
			if(is_array($sqlset))
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $ont['idonu']]);
			$result['type'] = $ont['type'];
			$result['status'] = $getData['status'];
			if(!empty($getData['eth'])){
				$result['wan'] = ($getData['eth']==5?'up':'down');;	
				$result['wanportzte'] = $this->type_speed_zte($getData['eth']);
			}			
			if(!empty($getData['name']))	
				$result['name'] = $getData['name'];			
			if(!empty($getData['note']))	
				$result['note'] = $getData['note'];			
			if(!empty($getData['model']))	
				$result['model'] = $getData['model'];
			if(!empty($getData['reason']))	
				$result['reason'] = $getData['reason'];			
			if(!empty($getData['vendor']))	
				$result['vendor'] = $getData['vendor'];				
			if(!empty($getData['mngtvlan']))	
				$result['mngtvlan'] = $getData['mngtvlan'];			
			if(!empty($getData['typereg']))	
				$result['typereg'] = $getData['typereg'];			
			if(!empty($getData['config']))	
				$result['config'] = $getData['config'];
			if(!empty($getData['dist'])) 
				$result['dist'] = $this->dist($getData['dist']);
			if(!empty($ont['lastrx']))
				$result['lastrx'] = $ont['lastrx'];
			if(!empty($getData['rx'])) 
				$result['rx'] = $getData['rx'];
			if(!empty($getData['tx'])) 
				$result['tx'] = $getData['tx'];			
			if(!empty($getData['adminstatus'])) 
				$result['adminstatus'] = $getData['adminstatus'];			
			if(!empty($getData['auttime'])) 
				$result['auttime'] = $getData['auttime'];			
			if(!empty($getData['regtime'])) 
				$result['regtime'] = $getData['regtime'];
			return $result;
		}
	}
	public function type_speed_zte($type){
		$types = [
			6 => ['img' => 'eth1g','txt' => '1 Gbps','st' => 'enable','st_tx' => 'enable','status' => 'up'],
			3 => ['img' => 'ethup','txt' => '10 Mbps','st' => 'enable',	'st_tx' => 'enable','status' => 'up'],
			5 => ['img' => 'ethup',	'txt' => '100 Mbps','st' => 'enable','st_tx' => 'enable','status' => 'up'],
			65535 => ['img' => 'ethdown','txt' => 'Offline','st' => 'disable','status' => 'down'],
			1 => ['img' => 'eth_na','txt' => 'Down','st' => 'down','st_tx' => 'Down','status' => 'down'],
			0 => ['img' => 'ethdown','txt' => 'Down','st' => 'disable',	'st_tx' => 'disable','status' => 'down'	]
		];	
		return $types[$type] ?? [];
	}
	public function preparedataEpon($dataApi,$type){
		switch($type){
			case 'status':
				$result = (isset($dataApi) && $dataApi == 3) ? 1 : 2;
				break;
			case 'dist':
				$result = isset($dataApi) ? $this->dist($dataApi) : 0;
				break;
			case 'rx':
				$result = $dataApi ? $this->clearEponrx($dataApi) : 0;
				break;				
			case 'tx':
				$result = $dataApi ? $this->clearEponrx($dataApi) : 0;
				break;			
			case 'regtime':
				if(isset($dataApi)){
					$pattern = "/(\d{4}\/\d{2}\/\d{2})(\d{2}:\d{2}:\d{2})/";
					preg_match($pattern, $dataApi, $matches);
					$date = $matches[1]; // Отримуємо дату: 2019/09/11
					$time = $matches[2]; // Отримуємо час: 11:18:19
					$result = str_replace("/", "-", $date).' '.$time;
				}else{
					$result = null;
				}
				break;			
			case 'adminstatus':
				$result = $dataApi==1 ? 'on' : 'off';
				break;
			case 'vendor':
			case 'eth':
			case 'model':
				$result = isset($dataApi) ? $dataApi : null;
				break;
			default:
				$result = null;
		}
		return $result;
	}
	public function preparedataGpon($dataApi, $type){
		switch($type){
			case 'status':
				$result = (isset($dataApi) && $dataApi == 3) ? 1 : 2;
				break;
			case 'dist':
				$result = isset($dataApi) ? $this->dist($dataApi) : 0;
				break;
			case 'rx':
				$result = $dataApi ? $this->checkGponsignal($dataApi) : 0;
				break;
			case 'tx':
				$result = $dataApi ? sprintf('%.2f', ($dataApi == 65535 ? 0 : (intval($dataApi) - 15000) * 0.002)) : 0;
				break;
			case 'sn':
				$result = $dataApi ?: null;
				break;
			case 'vendor':
			case 'config':
			case 'mngtvlan':
			case 'model':
			case 'eth':
				$result = $dataApi;
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
	public function typereg($type){
		$res = [
			1 => "regModeSn",
			2 => "regModePw",
			3 => "regModeSnPlusPw",
			4 => "regModeRegisterId",
			5 => "regModeRegisterIdPlus8021x",
			6 => "regModeRegisterIdPlusMutual",
			7 => "regModeTefPw",
			8 => "regModeSnPlusTefPw",
			9 => "regModeLoid",
			10 => "regModeLoidPlusPw"
		];
		return $res[$type] ?? null;
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
			if(empty($row['id'])){
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['llid'],'nameport' => $data['name'],'descrport' => (!empty($data['descr'])?$data['descr']:''),'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
			}
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
			'oid' => $this->deviceoid[$this->id]['global']['listport']['port']['oid'],
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$portlist = pmon_walk($port);
		if(is_array_empty($portlist)){
			foreach($portlist as $idport => $valueport['result']) {				
				if(!empty($valueport['result'])){
					$oidport = '1.3.6.1.2.1.2.2.1.2.'.trim($idport);
					$portname = $this->snmp->get($this->ip,$this->community,$oidport);
					$name_real_port = $this->rep('",STRING: ,= ','',$valueport['result']);
					$listoltport[$idport] = [
						'descrport' =>(isset($portname) ? trim($this->rep('",STRING: ,= ,'.$oidport,'',$portname)):''),
						'id' =>trim($idport),
						'typeport' => getTypePort($name_real_port['result']),
						'name' => getNameZteport($name_real_port['result'])
					];
				}				
			}
			$data['port'] = $listoltport;
		}
		if(isset($data['port']) && is_array($data['port'])){
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
				}elseif(preg_match('/epon/i',$valuePon['name'])) {
					$listPon[$idp] = [
						'name' => str_replace('epon_', 'EPON ', $valuePon['name']),
						'sort' => $sort,
						'descrport' => $valuePon['descrport'],
						'sfpid' => $valuePon['id'],
						'llid' => $valuePon['id'],
						'typeport' => $valuePon['typeport'],
						'cardcount' => 64
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
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'descrport' => (!empty($data['descrport']) ? $data['descrport'] : ''),'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => 'none','added' => $this->now]);
			}else{
				$this->db->SQLupdate('switch_port',['descrport' => (!empty($row['descrport']) ? $row['descrport'] : (!empty($data['descrport'])?$data['descrport']:''))],['id' => $row['id']]);
			}
		}
	}
    protected function savePonSwitch($dataPort) {
		$row = $this->db->Simple('SELECT count(id) as count_this FROM `switch_pon` WHERE oltid = '.$this->id.' AND sfpid = '.$dataPort['sfpid']);
		if ($row['count_this'] == 0) {
			$this->db->SQLinsert('switch_pon',['idportolt' => $dataPort['sort'],'type' => $dataPort['typeport'],'support' => $dataPort['cardcount'],'sort' => $dataPort['sort'],'oltid' => $this->id,'pon' => $dataPort['name'],'sfpid' => $dataPort['sfpid'],'added' => $this->now]);
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
			$this->db->SQLupdate('switch_pon',['count' =>$onus['count_onus'],'type' => $dataPort['typeport']],['sfpid' =>$dataPort['sfpid'],'oltid' => $this->id]);
		}
	}
	public function tempSaveOnuEpon($dataOnu = array()){	
		$onustatus = (!empty($dataOnu['status']) && $dataOnu['status']==3 ? 1 : 2);
		preg_match('/(\d+)\/(\d+)\/(\d+):/',$dataOnu['inface'],$mat);
		if(!empty($dataOnu['keyonu'])){
			$arr = $this->db->Fast('onus','*',['keyonu' =>$dataOnu['keyonu'],'olt' => $this->id]); 
			if(!empty($arr['idonu'])){
				$this->cache_onu_epon[$dataOnu['keyonu']] = $arr;
				$sqlset = [
					'sw_shelf' => $mat[1],
					'sw_slot' => $mat[2],
					'sw_port' => $mat[3],
					'updates' => $this->now,
					'status' => $onustatus,
					'type' => $dataOnu['pon'],
					'name' => !empty($dataOnu['name']) ? $dataOnu['name'] : '',
					'descr' => !empty($dataOnu['descr']) ? $dataOnu['descr'] : '',
					'mac' => !empty($dataOnu['mac']) ? $dataOnu['mac'] : '',
					'inface' => !empty($dataOnu['inface']) ? $dataOnu['inface'] : '',
					'reason' => !empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''
				];
				if($onustatus==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($onustatus==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}else{
					
				}
				if(!empty($dataOnu['dist'])){
					$sqlset['dist'] = $this->dist($dataOnu['dist']);
				}
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{				
				$sqlinsert = [
					'sw_shelf' => $mat[1],'sw_slot' => $mat[2],'sw_port' => $mat[3],'olt' => $this->id,
					'updates' => $this->now,
					'added' => $this->now, 
					($onustatus==1?'online':'offline') => $this->now,
					'keyonu' => $dataOnu['keyonu'],
					'status' => $onustatus,
					'mac' => $dataOnu['mac'],
					'inface' => $dataOnu['inface'],
					'dist' => (!empty($dataOnu['dist']) ? $this->dist($dataOnu['dist']) : 0),
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),
					'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon']
				];
				$this->db->SQLinsert('onus',$sqlinsert);
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
				$rx = $this->clearEponrx($dataOnu['rx']);
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
public function tempSaveSignalSaveOnuGpon($dataOnu){	
		global $config;
		$savehistor = null;
		if(isset($this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])&& !empty($this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']])){
			$onu = $this->cache_onu_gpon[$dataOnu['keyport']][$dataOnu['keyonu']];
		}else{
			$onu = $this->db->Simple("SELECT status,rx,idonu,inface,sn,changerx,olt FROM onus WHERE olt = '{$this->id}' AND zte_idport = '{$dataOnu['keyport']}' AND keyonu = '{$dataOnu['keyonu']}' LIMIT 1");
		}	
		if(!empty($onu['idonu'])){
			if(!empty($dataOnu['rx'])){
				$rxValue = $this->checkGponsignal($dataOnu['rx']);
				if(!empty($rxValue)){
					$this->db->query("UPDATE onus SET rx = '{$rxValue}' WHERE idonu  = {$onu['idonu']}");
				}
			}
			if($config['logsignal']=='on'){
				if(!empty($rxValue)){
					$savehistor = SignalMonitor($onu['status'], $rxValue, $onu['rx'], $onu['idonu'], $onu);
				}
			}else{
				$savehistor = true;
			}
			if ($onu['status'] == 1 && isSignalChanged($rxValue, $onu['rx'])) {
				$logont = [
					'log' => 'ont','type' => 'signal','idonu' => $onu['idonu'],'olt' => $this->id, 'time' => $this->now,'curent' => $rxValue ?? 0,'last' => $onu['rx'] ?? 0
				];
				$this->logger->init($logont);
			}
			if (!empty($config['onugraph']) && $config['onugraph'] == 'on' && $savehistor && !empty($rxValue)) {
				$this->db->SQLInsert('historysignal',['device' => $this->id,'onu' => $onu['idonu'],'signal' => $rxValue,'datetime' => $this->now]);
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
	public function checkGponsignal($onu_txc1){
		$onu_txc1 = preg_replace('~^.*?(?=INTEGER:)~i','',$onu_txc1);
		$onu_txc1 = preg_replace ('/INTEGER:/','',$onu_txc1);
		$onu_txc1 = trim($onu_txc1);
		if ($onu_txc1*1 < 30001) {
			$onu_txc1 = $onu_txc1*0.002 - 30.0;
			$onu_txc1 = sprintf("%.2f",$onu_txc1);
		} else {
			if ($onu_txc1*1 < 665535){
				$onu_txc1 = ($onu_txc1-65535)*0.002 - 30.0;
				$onu_txc1 = sprintf("%.2f",$onu_txc1);
			}else{
				$onu_txc1 = 0;
			}
		}
		return $onu_txc1;
	}	
	public function checkEponsignal($value){
		if (preg_match('/-80000/i',$value)){
			return 0;
		} elseif(preg_match('/65535/i',$value)) {
			return 0;
		} else{
			return number_format($value, 2);		
		}
	}
	public function clearEponrx($value){
		if (preg_match('/\b214748\b/i', $value)) {
			return 0; 
		}elseif(preg_match('/N/i',$value)){
			return 0;
		}else{
			return $this->checkEponsignal($value);
		}
	}
	public function tempUpdateSignalCheck(){	

	}
	public function reason($reason) {
		$reason  = trim($reason);
		$errorMap = [
			0 => 'err1',
			1 => 'err5',
			2 => 'err6',
			3 => 'err6',
			7 => 'err25',
			9 => 'err1',
			12 => 'err0',
		];
		return isset($errorMap[$reason]) ? $errorMap[$reason] : 'err0';
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
					'sw_shelf' => $mat[1],
					'sw_slot' => $mat[2],
					'sw_port' => $mat[3],
					'olt' => $this->id,
					'updates' => $this->now,
					'keyonu' => $dataOnu['keyonu'],'status' => $onustatus,
					'inface' => $dataOnu['inface'],
					'dist' => (!empty($dataOnu['dist']) ? $dataOnu['dist'] : 0),
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),
					'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon'],
					'zte_idport' => (!empty($arr['zte_idport']) ? $arr['zte_idport'] : $dataOnu['keyport']),
					'portolt' => (!empty($arr['portolt']) ? $arr['portolt'] : $dataOnu['keyport'])
				];
				if($onustatus==1 && $arr['status']==2){
					$sqlset['online'] = $this->now;
				}elseif($onustatus==2 && $arr['status']==1){
					$sqlset['offline'] = $this->now;
				}else{
					
				}
				if(!empty($dataOnu['dist'])){
					$sqlset['dist'] = $this->dist($dataOnu['dist']);
				}
				$this->db->SQLupdate('onus',$sqlset,['idonu' => $arr['idonu']]);
			}else{				
				$sqlset = [
					'sw_shelf' => $mat[1],
					'sw_slot' => $mat[2],
					'sw_port' => $mat[3],
					'olt' => $this->id,
					'updates' => $this->now,
					'added' => $this->now, 
					($onustatus==1?'online':'offline') => $this->now,
					'keyonu' => $dataOnu['keyonu'],
					'status' => $onustatus,
					'sn' => $dataOnu['sn'],
					'inface' => $dataOnu['inface'],
					'dist' => (!empty($dataOnu['dist']) ? $this->dist($dataOnu['dist']) : 0),
					'name' => (!empty($dataOnu['name']) ? $dataOnu['name'] : ''),
					'reason' => (!empty($dataOnu['reason']) ? $this->reason($dataOnu['reason']) : ''),
					'type' => $dataOnu['pon'],
					'zte_idport' => (!empty($dataOnu['keyport']) ? $dataOnu['keyport'] : ''),
					'portolt' => (!empty($dataOnu['keyport']) ? $dataOnu['keyport'] : '')
				];
				$this->db->SQLinsert('onus',$sqlset);
			}
		}
	}
	public function Signal($value) {
		if (preg_match('/655/i', $value) || preg_match('/4748/i', $value) ) {
			return '0.00'; 
		}
		$value = trim(str_replace(['"', 'N/A'], ['', 0], $value));
		$value = sprintf('%.2f', $value / 1000);
		return $value === '0.00' ? 0 : $value;
	}
	public function StatisticOLT(){	
		$arrayPort = array();
		$getPort = $this->db->Multi('switch_pon','*',['oltid' => $this->id]);
		if(count($getPort)){
			foreach($getPort as $port){
				$inf = str_replace('GPON ', '',$port['pon']);
				$inf = str_replace('EPON ', '',$inf);
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
	public function getOnuRxPoller($value){    
		$array = array();
		$types = 'rxolt';
		if ($value['type'] == 'gpon') {
			$array = ['id' => $this->id, 'keyonu' => $value['keyonu'], 'keyport' => $value['zte_idport'], 'pon' => $value['type'], 'do' => 'onu', 'types' => $types
			];
		} elseif ($value['type'] == 'epon') {
			$array = ['id' => $this->id, 'keyonu' => $value['keyonu'], 'pon' => $value['type'], 'do' => 'onu', 'types' => $types
			];
		}
		return $array ?: null;
	}
	public function tempSaveSignalSaveRxOnuEpon($dataOnu){	
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->Signal($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}
	public function tempSaveSignalSaveRxOnuGpon($dataOnu){
		if(!empty($dataOnu['idonu']) && isset($dataOnu['rxolt']) && !empty($dataOnu['rxolt'])){
			$rxolt = $this->Signal($dataOnu['rxolt']);
			$this->db->query("UPDATE onus SET rxolt = '{$rxolt}' WHERE idonu  = {$dataOnu['idonu']}");
			$this->db->query("INSERT INTO rxolt_signal (`onu`, `signal`, `datetime`) VALUES ('{$dataOnu['idonu']}', '{$rxolt}', '{$this->now}')");
		}
	}
	public function dist($dist){ 
		$volokno = 0;
		if(isset($dist)){		
			$dist = str_replace(['INTEGER:', 'STRING:', ' ', '"'], ['', '', '', ''], $dist);
			if(is_numeric($dist)){
				$volokno = $dist * 1.635;
			}		
		}
		return intval($volokno);
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

}
?>