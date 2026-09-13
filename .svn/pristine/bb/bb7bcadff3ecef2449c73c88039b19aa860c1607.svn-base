<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class GCOMs6100_16x { 
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
			'oid' => '1.3.6.1.2.1.2.2.1.2',
			'type' => 'exec','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$inface = [];
		$tempinface = pmon_walk($oid);
		if(is_array($tempinface)){
			foreach($tempinface as $pi1 => $type) {
				if(!empty($type['result']) && preg_match("/e0/i", $type['result'])){
					preg_match('/(\d+) =/',$type['result'],$mat);
					$portid = trim($mat[1]);
					if(isset($portid)){
						$oid_ = '1.3.6.1.2.1.2.2.1.8.'.$portid;
						$temp_status = $this->snmp->get($this->ip,$this->community,$oid_,true);
						$operstatus = valueStringSnmp(str_replace($oid_,'',$temp_status));
						$inface[$portid]['llid'] = $portid;
						preg_match('/0\/(\d+)\/(\d+)/',$type['result'],$infaceif);
						$inface[$portid]['name'] = 'Ethernet 0/'.$infaceif[1].'/'.$infaceif[2];
						$inface[$portid]['typeport'] = 'sfp';
						$inface[$portid]['operstatus'] = (isset($operstatus) && $operstatus==1 ? 'up' : 'down');
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
					'operstatus' => $data['operstatus'],
					($data['operstatus']=='down'?'timedown':'timeup')=>$this->now],
					['id' => $row['id']
				]);
			}else{
				$this->db->SQLinsert('switch_port',[
					'deviceid' => $this->id,
					'llid' => $data['llid'],
					'nameport' => $data['name'],
					'typeport' => $data['typeport'],
					'operstatus' => $data['operstatus'],
					'added' => $this->now,($data['operstatus']=='down'?'timedown':'timeup')=>$this->now]
				);
			}
		}
	}
}
?>