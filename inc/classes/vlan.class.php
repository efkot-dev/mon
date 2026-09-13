<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMon_VLAN{
	private int $id;
    private $data_switch;	
    private $telnet;	
    public $clock;	
    private $pdo;	
    private $temp;	
    private $result_fdb;	
    private $confPMon;	
	public function __construct($id, $pdo, $switch, $confPMon){
		if(is_numeric($id)){
			$this->pdo = $pdo;	
			$this->id = $id;
			$this->clock = date('Y-m-d H:i:s');
			$this->data_switch = $switch;	
			$this->confPMon = $confPMon;	
		}
	}
	public function Huawei_Vlan_Gpon(){
		$row = $this->pdo->query("SELECT idonu, zte_idport, keyonu, olt, inface FROM onus WHERE type = 'gpon' AND status = '1' AND olt = '".$this->id."'");
		$get_gpon = $row->fetchAll(PDO::FETCH_ASSOC);
		return $get_gpon;
	}
	public function Huawei_Vlan(){
		$pauseInterval = 80;
		$counter = 0;
		$ont_gpon = $this->Huawei_Vlan_Gpon();
		$vlan_array_gpon = array();
		$vlan_temp_all_gpon = array();
		if(isset($ont_gpon) && count($ont_gpon) > 0){
			foreach ($ont_gpon as $onu){
				$onu_vlan = @snmp2_get($this->data_switch['netip'],$this->data_switch['snmpro'], '1.3.6.1.4.1.2011.6.128.1.1.2.62.1.7.' . $onu['zte_idport'].'.' . $onu['keyonu'].'.1', 100000, 5);
				if(isset($onu_vlan)){
					$vlan = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_vlan);
					$vlan_array_gpon[$onu['idonu']]['vlan'] = $vlan;
					$vlan_temp_all_gpon[$vlan] = $vlan;
				}
				$counter++;
				if ($counter % $pauseInterval === 0) {
					sleep(rand(1,4));
				}
			}
		}
		$this->ONU_Vlan_Epon_update($vlan_array_gpon);
		$this->SQL_Update_Vlan($vlan_temp_all_gpon);
	}
	public function BDCOM_Vlan_Epon(){
		$vlan_temp = [];
		$vlan_temp_all = [];
		$row = $this->pdo->query("SELECT idonu, mac, keyonu, olt, inface FROM onus WHERE status = '1' AND olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		$pauseInterval = 40;
		$counter = 0;
		
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			$session = new SNMP(SNMP::VERSION_2c,$this->data_switch['netip'],$this->data_switch['snmpro']);
			foreach ($getswitchonu as $onu){
				$onu_vlan = $session->get('1.3.6.1.4.1.3320.101.12.1.1.3.' . $onu['keyonu'].'.1');
				if(isset($onu_vlan) && $onu_vlan!=false){
					$vlan = preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_vlan);
					$vlan_temp[$onu['idonu']]['vlan'] = $vlan;
					$vlan_temp_all[$vlan] = $vlan;
				}
				$counter++;
				if ($counter % $pauseInterval === 0) {
					sleep(rand(1,2));
				}
			}
			$this->ONU_Vlan_Epon_update($vlan_temp);
			$this->SQL_Update_Vlan($vlan_temp_all);
		}		
	}
	public function CDATA16v3_Vlan_Gpon(){
		$list_vlan_gpon = [
			'oid' => '1.3.6.1.4.1.34592.1.3.100.9.2.1.4.1.0','type' => 'class','deloid' => true,'ip' => $this->data_switch['netip'],'community'=> $this->data_switch['snmpro']
		];
		$vlan_temp_all = [];
		$tmp_vlan = pmon_walk($list_vlan_gpon);
		if(isset($tmp_vlan) && count($tmp_vlan)>0){
			foreach($tmp_vlan as $pi1 => $type) {
				preg_match('/:\s*(.*?)\s*$/', $type['result'],$temp);
				$vlan = (int)$temp[1];
				$vlan_temp_all[$vlan] = $vlan;
			}
		}
		$this->SQL_Update_Vlan($vlan_temp_all);
	}
	public function ZTE6_Vlan_decode($inface){
		preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/',$inface,$ont);
		$index_type = str_pad(decbin(1), 4, '0', STR_PAD_LEFT);
		$rack = str_pad(decbin(1), 4, '0', STR_PAD_LEFT);
		$shelf = str_pad(decbin($ont[1]), 8, '0', STR_PAD_LEFT);
		$slot = str_pad(decbin($ont[2]), 8, '0', STR_PAD_LEFT);
		$olt = str_pad(decbin($ont[3]), 8, '0', STR_PAD_LEFT);
		$port_index = bindec($index_type . $rack . $shelf . $slot  . $olt);
		return $port_index;		
	}
	public function ZTE6_Vlan_Gpon(){
		$vlan_temp = [];
		$vlan_temp_all = [];
		$row = $this->pdo->query("SELECT idonu, sn, keyonu, olt, inface FROM onus WHERE status = '1' AND olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		$pauseInterval = 40;
		$counter = 0;
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			foreach ($getswitchonu as $onu){
				$hex_inface = $this->ZTE6_Vlan_decode($onu['inface']);
				$onu_vlan = @snmp2_get($this->data_switch['netip'],$this->data_switch['snmpro'],'1.3.6.1.4.1.3902.1082.500.20.2.4.63.1.4.'.$hex_inface.'.' . $onu['keyonu'].'.1', 100000, 5);
				if(isset($onu_vlan) && $onu_vlan!=false){
					$vlan = (int)preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_vlan);
					$vlan_temp[$onu['idonu']]['vlan'] = $vlan;
					$vlan_temp_all[$vlan] = $vlan;
				}
				$counter++;
				if ($counter % $pauseInterval === 0) {
					sleep(rand(1,2));
				}
			}
			$this->ONU_Vlan_Epon_update($vlan_temp);
			$this->SQL_Update_Vlan($vlan_temp_all);
		}
	}	
	public function CDATA12_Vlan_Epon(){
		$vlan_temp = [];
		$vlan_temp_all = [];
		$row = $this->pdo->query("SELECT idonu, mac, keyonu, olt, inface FROM onus WHERE status = '1' AND olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		$pauseInterval = 40;
		$counter = 0;
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			foreach ($getswitchonu as $onu){
				$onu_vlan = @snmp2_get($this->data_switch['netip'],$this->data_switch['snmpro'], '1.3.6.1.4.1.17409.2.3.7.3.1.1.7.' . $onu['keyonu'].'.0.1', 100000, 5);
				if(isset($onu_vlan) && $onu_vlan!=false){
					$vlan = (int)preg_replace('/^.*?(INTEGER:)|"|N\/A/i', '', $onu_vlan);
					$vlan_temp[$onu['idonu']]['vlan'] = $vlan;
					$vlan_temp_all[$vlan] = $vlan;
				}
				$counter++;
				if ($counter % $pauseInterval === 0) {
					sleep(rand(1,2));
				}
			}
			$this->ONU_Vlan_Epon_update($vlan_temp);
			$this->SQL_Update_Vlan($vlan_temp_all);
		}
	}
	public function SQL_Update_Vlan($vlan_array) {
		$get_olt = "get_list_olt_vlan_" . $this->id;
		if (!empty($vlan_array) && is_array($vlan_array)) { 
			foreach ($vlan_array as &$inner_array) {
				if (is_array($inner_array)) {
					ksort($inner_array);
				}
			}
			$json_data = json_encode($vlan_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			if (!empty($json_data)) {
				$sql = "INSERT INTO tempdate (file, last_processed, data) 
						VALUES (:file, :last_processed, :data) 
						ON DUPLICATE KEY UPDATE 
						data = VALUES(data), 
						last_processed = VALUES(last_processed)";
				$stmt = $this->pdo->prepare($sql);
				$stmt->execute([
					':file' => $get_olt,
					':last_processed' => $this->clock,
					':data' => $json_data
				]);
			}
		}
	}
	public function ONU_Vlan_Epon_update($vlan_array){
		if(isset($vlan_array) && !empty($vlan_array)){
			foreach ($vlan_array as $onu_id => $value) {
				if(isset($value['vlan'])){
					$this->pdo->query("UPDATE onus SET wan = '{$value['vlan']}' WHERE idonu  = '{$onu_id}' AND olt = '{$this->id}'");
				}
			}
		}
	}
}
?>
