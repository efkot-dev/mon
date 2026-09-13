<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Rasisecom2600g { 
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
	public function Port(){
		$oid = [
			'oid' => '1.3.6.1.2.1.31.1.1.1.1',
			'cache' => true,'timecache' => 11000,'namecache' => 'list_port_'.$this->id,
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$inface = [];
		$tempinface = pmon_walk($oid);
		if(is_array($tempinface)){
			foreach($tempinface as $pi1 => $type) {
				if(!empty($type['result']) && preg_match("/Ethernet/i", $type['result'])){
					$portid = trim($pi1);
					if(isset($portid)){
						$oid_ = '1.3.6.1.2.1.2.2.1.8.'.$portid;
						$temp_status = $this->snmp->get($this->ip,$this->community,$oid_,true);
						$inface[$portid]['llid'] = $portid;
						preg_match('/1\/1\/(\d+)/',$type['result'],$infaceif);
						if(preg_match("/gigaethernet/i", $type['result'])){
							$inface[$portid]['name'] = 'GigabitEthernet 1/1/'.$infaceif[1];
							$inface[$portid]['typeport'] = 'combosfp';	
						}elseif( preg_match("/Ethernet/i", $type['result'])){
							$inface[$portid]['name'] = 'Ethernet 1/1/'.$infaceif[1];
							$inface[$portid]['typeport'] = 'eth';
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
					($data['status']=='down'?'timedown':'timeup')=>$this->now],
					['id' => $row['id']
				]);
			}else{
				$this->db->SQLinsert('switch_port',[
					'deviceid' => $this->id,
					'llid' => $data['llid'],
					'nameport' => $data['name'],
					'typeport' => $data['typeport'],
					'operstatus' => $data['status'],
					'added' => $this->now,($data['status']=='down'?'timedown':'timeup')=>$this->now]
				);
			}
		}
	}
}
?>