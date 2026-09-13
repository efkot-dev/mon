<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class DellX4012 { 
    private $logger;
    private $db;
	private $snmp;
	private $id;
	private $ip;
	private $oidid;
	private $community;
	private $deviceoid;
    private $now;
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
			'oid' => '1.3.6.1.2.1.2.2.1.2','cache' => true,'timecache' => 3600,'namecache' => 'dell_list_port_'.$this->id,
			'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$inface_vlan = [];
		$inface = [];
		$tempinface = pmon_walk($oid);
		if(is_array($tempinface)){
			foreach($tempinface as $pi1 => $type) {
				$port = valueStringSnmp($type['result']);
				if(!empty($type['result']) && preg_match("/tengigabitethernet1/i", $type['result'])){
					$portid = intval(trim($pi1));
					preg_match('/tengigabitethernet1\/0\/(\d+)/',$type['result'],$inface_id);					
					$oid_ = '1.3.6.1.2.1.2.2.1.8.'.$portid;
					$temp_status = $this->snmp->get($this->ip,$this->community,$oid_,true);
					$status = valueStringSnmp(str_replace($oid_,'',$temp_status));
					$inface[$portid] = array(
						'name' => 'TenGigabit 0/'.$portid,
						'idport' => $inface_id[1],
						'llid' => $portid,
						'status' => (isset($status) && $status==2 ? 'down' : 'up'),					
						'typeport' => 'sfp'					
					);
				}
				if(is_numeric($port) && $port > 0) {
					$inface_vlan[$port] = $port;
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
				$this->db->query("UPDATE switch_port SET operstatus = '{$data['status']}'	WHERE id  = '{$row['id']}'");
			}else{
				$sql ="INSERT INTO switch_port (deviceid, llid, nameport, typeport, descrport, operstatus, added) VALUES ('{$this->id}','{$data['llid']}','{$data['name']}','{$data['typeport']}', " . (!empty($data['descrport']) ? "'" . $data['descrport'] . "'" : 'NULL') . ",'{$data['status']}','{$this->now}')";
				$this->db->query($sql);
			}
		}
	}
}
?>