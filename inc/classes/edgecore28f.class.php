<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Edge_Core_28f { 
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
			'type' => 'exec','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$inface = [];
		$tempinface = pmon_walk($oid);
		if(is_array($tempinface)){
			foreach($tempinface as $pi1 => $type) {
				if(!empty($type['result']) && preg_match("/Port/i", $type['result'])){
					preg_match('/(\d+) =/',$type['result'],$mat);
					$portid = trim($mat[1]);
					if(isset($portid)){
						$oid_ = '1.3.6.1.2.1.31.1.1.1.17.'.$portid;
						$oid_descr_ = '1.3.6.1.2.1.31.1.1.1.18.'.$portid;
						$temp_status = $this->snmp->get($this->ip,$this->community,$oid_,true);
						$temp_descr = $this->snmp->get($this->ip,$this->community,$oid_descr_,true);
						$inface[$portid]['llid'] = $portid;
						preg_match('/Port(\d+)/',$type['result'],$infaceif);
						if( preg_match("/Port/i", $type['result'])){
							$inface[$portid]['name'] = 'SFP 0/'.$infaceif[1];
							$inface[$portid]['typeport'] = 'sfp';
							$temp_descr = valueStringSnmp(str_replace($oid_descr_,'',$temp_descr));
							$inface[$portid]['descrport'] = $temp_descr;
						}
						$status = valueStringSnmp(str_replace($oid_,'',$temp_status));
						$inface[$portid]['status'] = (isset($status) && $status==2 ? 'down' : 'up');	
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
		if(!empty($data['llid'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['llid']]);
			if(!empty($row['id'])){
				$this->db->SQLupdate('switch_port',[
					'operstatus' => $data['status'],
					'descrport' => $data['descrport'],
					($data['status']=='down'?'timedown':'timeup')=>$this->now],
					['id' => $row['id']
				]);
			}else{
				$this->db->SQLinsert('switch_port',[
					'deviceid' => $this->id,
					'llid' => $data['llid'],
					'nameport' => $data['name'],
					'descrport' => $data['descrport'],
					'typeport' => $data['typeport'],
					'operstatus' => $data['status'],
					'added' => $this->now,($data['status']=='down'?'timedown':'timeup')=>$this->now]
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