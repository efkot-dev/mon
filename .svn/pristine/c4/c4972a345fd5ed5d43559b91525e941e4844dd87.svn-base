<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class JuniperMX140{ 
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
    public function support(string $check): bool {
        return match ($check) {
            'port' => true,
            default => false,
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
	public function IfType($portTypeId) {
		$portTypes = [
			1 => 'UNKNOWN',
			53 => 'Virtual',
			24 => 'Loopback',
			6 => 'FE',
			23 => 'USER',
			117 => 'GE',
			135 => 'VLAN',
			161 => 'LACP',
			136 => 'VLAN',
			137 => 'VLAN',
			207 => 'PON',
			208 => 'PON',
			209 => 'BRIDGE',
			250 => 'GPON',
			266 => 'ONU',
			142 => 'IP',
			300 => 'EPON'
		];
		if (array_key_exists($portTypeId, $portTypes)) {
			if ($portTypes[$portTypeId] !== 'Virtual' && $portTypes[$portTypeId] !== 'VLAN') {
				return ['type' => ($portTypeId==6 ? 'physical':'mikrotik'),'port' => $portTypes[$portTypeId]];
			} else {
				return ['type' => 'mikrotik','port' => $portTypes[$portTypeId]];
			}
		}
		return ['type' => 'invalid','port' => null];
	}
	public function Load(){
		global $db;	
	
	}	
	public function clearResult(string $value): string {
		return trim(str_replace('"', '',str_replace('/', '', $value)));
	}
	public function Port(): ?array{
		$temp_inface = array();
		$dataport = array();
		$olt_port = [
			'oid' => '1.3.6.1.2.1.2.2.1.3','cache' => true,'timecache' => 1000,'namecache' => 'list_port_'.$this->id,'type' => 'class','deloid' => true,'ip' => $this->ip,'community'=> $this->community
		];
		$index_port = pmon_walk($olt_port);
		if (isset($index_port) && is_array($index_port)) {
			foreach ($index_port as $tmp => $value) {
				$iftype = $this->snmpData($value['result']);
				$tmp_port = $this->IfType($iftype);
				$temp_inface[$tmp] = array(
					'port_llid' => $tmp,'port_type' => $tmp_port['type'],'port_inface' => $tmp_port['port']
				);
			}
		}
		if (isset($temp_inface) && is_array($temp_inface)) {
			foreach ($temp_inface as $idport => $data) {
				if($data['port_type']=='physical'){
					$oid_name = '1.3.6.1.2.1.2.2.1.2.'.$data['port_llid'];
					$oid_desr = '1.3.6.1.2.1.31.1.1.1.18.'.$data['port_llid'];
					$oid_status = '1.3.6.1.2.1.2.2.1.8.'.$data['port_llid'];
					$snmp_name = $this->snmp->get($this->ip,$this->community,$oid_name, true);
					$snmp_descr = $this->snmp->get($this->ip,$this->community,$oid_desr, true);
					$snmp_status = $this->snmp->get($this->ip,$this->community,$oid_status, true);
					$dataport[$idport]['id'] = $data['port_llid'];
					$dataport[$idport]['name'] = $this->extractValue($snmp_name);
					$dataport[$idport]['typeport'] = 'mikrotik';
					$status = $this->extractValue($snmp_status);
					$dataport[$idport]['status'] = ($status==1 ? 'up' : 'down');
					$dataport[$idport]['descrport'] = $this->extractValue($snmp_descr);
				}
			}
		}
		return $dataport ?: null;
	}
	public function extractValue($input) {
		if (preg_match('/:\s*"?(.*?)"?$/', $input, $matches)) {
			return $matches[1];
		}
		return null;
	}
	public function savePort($dataPort){
		if(!empty($dataPort)){
			foreach($dataPort as $value){
				$this->savePortSwitch($value);
			}
		}
	}
    protected function savePortSwitch($data) {
		$up = [];
		if(!empty($data['id'])){	
			$row = $this->db->Simple("SELECT * FROM switch_port WHERE deviceid = '{$this->id}' AND llid = '{$data['id']}' LIMIT 1");
			if(!empty($row['id'])){
				if (isset($data['descrport']) && !empty($data['descrport'])) {
					$up[] = "descrport = '{$data['descrport']}'";
				}
				$up[] = "operstatus = '{$data['status']}'";
				$update = implode(', ', $up);
				$this->db->query("UPDATE switch_port SET {$update} WHERE id = '{$row['id']}'");
			}else{
				$sql ="INSERT INTO switch_port (deviceid, llid, nameport, typeport, descrport, operstatus, added) VALUES ('{$this->id}','{$data['id']}','{$data['name']}','{$data['typeport']}', " . (!empty($data['descrport']) ? "'" . $data['descrport'] . "'" : 'NULL') . ",'{$data['status']}','{$this->now}')";
				$this->db->query($sql);
			}
		}
	}
	public function status(int $status): string {
		return ($status == 2) ? 'down' : 'up';
	}	
	public function snmpData(string $value): string {
		$value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|\s|=|"/', '', $value);
		return trim($value);
	}
}
?>