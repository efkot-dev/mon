<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class CiscoNX3000 { 
    private $logger;
    private $db;
	private $snmp;
	private $id;
	private $ip;
	private $oidid;
	private $community;
	private $deviceoid;
    private $now;
    private $inface = [];
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
		$oid = [
			'oid' => '1.3.6.1.2.1.31.1.1.1.1',
			'cache' => true,'timecache' => 15000,'namecache' => 'port_'.$this->id,
			'type' => 'exec','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$inface = [];
		$tempinface = pmon_walk($oid);
		if(is_array($tempinface)){			
		foreach($tempinface as $pi1 => $type) {
			if(!empty($type['result']) && preg_match("/Ethernet/i", $type['result'])){
				preg_match('/(\d+)\s*=\s*(.*?)\s*$/', $type['result'],$temp);
				$portid = (int)trim($temp[1]);
				if (isset($portid)){
					if(preg_match('/Ethernet1/i',$temp[2])){
						preg_match('/Ethernet1\/(\d+)/', $temp[2], $infacetemp);
					}elseif(preg_match('/Gi0/i',$temp[2])){
						preg_match('/Gi0\/(\d+)/', $temp[2], $infacetemp);
					}else{
						die('error_check_port');
					}
					$interface =(int)$infacetemp[1];					
					if(isset($interface)){
						$inface[$portid]['name'] = 'Ethernet 1/'.$interface;
						$inface[$portid]['typeport'] = 'sfp';
						$inface[$portid]['llid'] = $portid;	
						// operstatus
						$oid_ = '1.3.6.1.2.1.10.7.2.1.19.'.$portid;
						$temp_status = $this->snmp->get($this->ip,$this->community,$oid_,true);
						$status = valueStringSnmp(str_replace($oid_,'',$temp_status));
						if(isset($status))
							$inface[$portid]['operstatus'] = (isset($status) && $status==3 ? 'up' : 'down');
						// descr port
						$oid_descr_ = '1.3.6.1.2.1.31.1.1.1.18.'.$portid;
						$temp_descr = $this->snmp->get($this->ip,$this->community,$oid_descr_,true);
						$descrport = valueStringSnmp(str_replace($oid_descr_,'',$temp_descr));
						$inface[$portid]['descrport'] = (isset($descrport)?$descrport:'');
					}
				}
			}
		}
		}
		return (is_array_empty($inface) ? $inface : null);
	}
	public function savePort($dataPort){
		if(is_array($dataPort)){
			foreach($dataPort as $value){
				$this->savePortSwitch($value);
			}
		}
	}
    protected function savePortSwitch($data) {
		/*
		//'operstatus' => $data['operstatus'],
					//($data['operstatus']=='down'?'timedown':'timeup')=>$this->now],
		*/
		if(!empty($data['llid'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['llid']]);
			if(!empty($row['id'])){
				$this->db->SQLupdate('switch_port',[
					'descrport' => (isset($data['descrport']) && $data['descrport']!=false ? $data['descrport'] : '')],['id' => $row['id']
				]);
			}else{
				$this->db->SQLinsert('switch_port',[
					'deviceid' => $this->id,
					'llid' => $data['llid'],
					'descrport' => $data['descrport'],
					'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => $data['operstatus'],'added' => $this->now,($data['operstatus']=='down'?'timedown':'timeup')=>$this->now]
				);
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
		$value = trim($value);	
		return $value;
	}
}
?>