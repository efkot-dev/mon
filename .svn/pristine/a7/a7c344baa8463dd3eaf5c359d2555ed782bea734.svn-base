<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class DlinkDGS3420 { 
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
    private $filter = true;
    private $filters = ['/"/','/Hex-/i','/OID: /i','/STRING: /i','/Gauge32: /','/INTEGER: /i','/Counter32: /i','/SNMPv2-SMI::enterprises\./i','/iso\.3\.6\.1\.4\.1\./i'];
	public function Support($check){
		switch ($check) {
			case 'port' : 
				return true;
			break;			
		}	
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
		global $db;		
	}	
	public function explodeRows($data) {
		$result = explode("\n", $data);
		return ($result);
	}
	public function clearResult($value){
		$value = str_replace('"','',$value);
		$value = trim($value);
		$value = str_replace('/','',$value);
		return $value;
	}
	public function Port(){
		$d = array();		
		$OIdPortList = $this->deviceoid[$this->id]['global']['listport']['port']['oid'];
		if(!$this->indexdevice){
			$ListPortSw = $this->snmp->walk($this->ip,$this->community,$OIdPortList,true);
		}else{
			$ListPortSw = $this->indexdevice;
		}
		$ListPortSwitch = str_replace('.'.$OIdPortList.'.','',$ListPortSw);
		$IndexPort = $this->explodeRows($ListPortSwitch);	
		if(is_array($IndexPort)){
			$list_port = array();
			foreach ($IndexPort as $idport => $ValuePort) {				
                $infPort = explode('=', $ValuePort);
				if(!empty($infPort[0]) && !empty($infPort[0]) && $infPort[0] <= 26){
					$dataIndexPort = $this->clearResult($infPort[1]);
					if(!preg_match('/1\/(\d+)/i',$dataIndexPort) AND !preg_match('/System/i',$dataIndexPort) AND !preg_match('/802/i',$dataIndexPort)){
						$list_port[$idport] = array(
							'id' =>trim($infPort[0]),
							'typeport' => getTypePortDlink1106(trim($infPort[0])),
							'name' => getNamesDlink1106(trim($infPort[0]))
						);
					}
				}				
			}
			$data['port'] = $list_port;
			if(is_array($data['port'])){
				foreach($data['port'] as $id => $v){
					$d[$id]['id'] = $v['id'];
					$d[$id]['typeport'] = $v['typeport'];
					$d[$id]['name'] = $v['name'];		
					$oid_ = '1.3.6.1.2.1.2.2.1.8.'.$v['id'];
					$oid_descr_ = '1.3.6.1.2.1.31.1.1.1.18.'.$v['id'];
					$temp_status = $this->snmp->get($this->ip,$this->community,$oid_,true);
					$temp_descr = $this->snmp->get($this->ip,$this->community,$oid_descr_,true);
					$status = valueStringSnmp(str_replace($oid_,'',$temp_status));
					$descr = valueStringSnmp(str_replace($oid_descr_,'',$temp_descr));
					$d[$id]['status'] = $this->Status($status);
					$d[$id]['descrport'] = $this->clearData($descr);
				}
			}
		}
		return (is_array($d) ? $d : null);
	}
	public function savePort($dataPort){
		if($dataPort){
			foreach($dataPort as $value){
				$this->savePortSwitch($value);
			}
		}
	}
    protected function savePortSwitch($data) {
		if(!empty($data['id'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(!empty($row['id'])){
				$this->db->SQLupdate('switch_port',[
						'operstatus' => $data['status'],
						'descrport' => (isset($data['descrport'])?$data['descrport']:''),
						($data['status']=='down'?'timedown':'timeup') => $this->now
					],
					['id' => $row['id']]);
			}else{
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => $data['status'],'added' => $this->now,($data['status']=='down'?'timedown':'timeup')=>$this->now]);
			}
		}
	}
	public function Status($status){
		if($status==2){
			return 'down';
		}else{
			return 'up';
		}
	}	
	public function clearData($value){
		$value = str_replace('INTEGER:', '',$value);
		$value = str_replace('Hex-STRING:', '',$value);
		$value = str_replace('STRING:', '',$value);
		$value = str_replace('Gauge32:', '',$value);
		$value = str_replace('"', '',$value);
		$value = str_replace(' ', '',$value);
		$value = str_replace('\\', '',$value);
		$value = trim($value);	
		return $value;
	}
}
?>