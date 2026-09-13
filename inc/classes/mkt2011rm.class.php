<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
/*
private $cpuTemperatureFarenheit = '1.3.6.1.4.1.14988.1.1.3.10.0';
		private $boardVoltageVolts = '1.3.6.1.4.1.14988.1.1.3.8.0';
		private $boardPowerValueWatts = '1.3.6.1.4.1.14988.1.1.3.12.0';
		private $boardCurrentValuemA = '1.3.6.1.4.1.14988.1.1.3.13.0';
		private $processorFrequencyMHz = '1.3.6.1.4.1.14988.1.1.3.14.0';
		private $usedMemory = '1.3.6.1.2.1.25.2.3.1.6.65536';
*/
class Mikrotik2011RM{ 
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
	public function Load(){
		global $db;	
	
	}	
	public function explodeRows(string $data): array {
		return explode("\n", $data);
	}
	public function clearResult(string $value): string {
		return trim(str_replace('"', '',str_replace('/', '', $value)));
	}
	public function Port(): ?array{
		$snmpdata = $this->snmp->walk($this->ip, $this->community, '1.3.6.1.2.1.2.2.1.2', true);
		if (!$snmpdata) {
			die('empty');
		}		
		$infaceport = $this->explodeRows($snmpdata);
		if (!is_array($infaceport)) {
			return null;
		}		
		$dataport = [];
		foreach ($infaceport as $keyport => $valuedata) {
			$match = explode('=', $valuedata);
			if (!empty($match[0]) && !empty($match[0])) {
				$dataIndexPort = $this->clearData($match[1]);
				$keyidport = str_replace('.1.3.6.1.2.1.2.2.1.2.', '', trim($match[0]));
				$listarrayport[$keyport] = [
					'id' => $keyidport,
					'typeport' => 'mikrotik',
					'name' => $dataIndexPort
				];
			}
		}		
		foreach ($listarrayport as $idports => $valuedataport) {
			$idport = $valuedataport['id'];
			$dataport[$idport]['id'] = $valuedataport['id'];
			$dataport[$idport]['typeport'] = $valuedataport['typeport'];
			$dataport[$idport]['name'] = $valuedataport['name'];
			$oidportoperstatus = "1.3.6.1.2.1.2.2.1.7.{$valuedataport['id']}";
			$resultsnmpdata = $this->snmp->get($this->ip, $this->community, $oidportoperstatus, true);
			$resultclear = (int)$this->clearData(str_replace($oidportoperstatus,'',$resultsnmpdata));
			$dataport[$idport]['status'] = $this->status($resultclear);
		}		
		return $dataport ?: null;
	}
	public function savePort(array $dataport): void {
		$rowdata = $this->db->Multi('switch_port','*',['deviceid' => $this->id]);
		if(count($rowdata)>0 && count($dataport)>0){
			foreach($rowdata as $val) {
				$data[$this->id][$val['llid']]['llid'] = $val['llid'];
				$data[$this->id][$val['llid']]['id'] = $val['id'];
			}
			foreach($rowdata as $value) {			
				if(!empty($dataport[$value['llid']]['id'])){
					$this->savePortSwitch($dataport[$value['llid']]);
				}else{
					$this->db->query('DELETE FROM switch_port WHERE id = '.$value['id']);
				}
			}
		}else{
			foreach($dataport as $value) {			
				$this->savePortSwitch($value);
			}
		}
	}
    protected function savePortSwitch($data) {
		if(!empty($data['id'])){	
			$row = $this->db->Fast('switch_port','*',['deviceid' => $this->id, 'llid' => $data['id']]);
			if(!empty($row['id'])){
				$this->db->SQLupdate('switch_port',['operstatus' => $data['status'],($data['status']=='down'?'timedown':'timeup')=>$this->now],['id' => $row['id']]);
			}else{
				$this->db->SQLinsert('switch_port',['deviceid' => $this->id,'llid' => $data['id'],'nameport' => $data['name'],'typeport' => $data['typeport'],'operstatus' => $data['status'],'added' => $this->now,($data['status']=='down'?'timedown':'timeup')=>$this->now]);
			}
		}
	}
	public function status(int $status): string {
		return ($status == 2) ? 'down' : 'up';
	}	
	public function clearData(string $value): string {
		$value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|\s|=|"/', '', $value);
		return trim($value);
	}
}
?>