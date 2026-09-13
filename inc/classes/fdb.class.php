<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMon_FDB{
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
	protected function logging($message, $type) {	
		$add_query = "INSERT INTO devicelogs (`type`, deviceid, descr, who, added) VALUES ('{$type}', '".$this->id."', '".$message."', 'cron', '".$this->clock."')";
		$this->pdo->query($add_query);
	}
	protected function get_onu_zte3() {	
		$temp_onu = [];
		$row = $this->pdo->query("SELECT sw_shelf, sw_slot, sw_port, idonu, keyonu, type, inface FROM onus WHERE olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			foreach ($getswitchonu as $on) {
				preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/',$on['inface'],$mat);
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]][$mat[4]]['idonu'] = $on['idonu'];
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]][$mat[4]]['inface'] = $on['inface'];
			}
		}
		return $temp_onu;		
	}
	protected function format_Mac($mac,$format){
		$mac = str_replace([' ', '.', '-', ':'], '', $mac);
		$mac = strtolower($mac);
		return match($format) {
			1 => preg_replace('/(.{2})/', '\1:', $mac, 5),
			2 => preg_replace('/(.{4})/', '\1.', $mac, 2),
			3 => preg_replace('/(.{4})/', '\1-', $mac, 2),
			4 => preg_replace('/(.{4})/', '\1:', $mac, 2),
			5 => preg_replace('/(.{2})/', '\1.', $mac, 5),
			default => $mac,
		};
	}
	protected function get_pon_bdcom_epon() {	
		$row = $this->pdo->query("SELECT id, pon FROM switch_pon WHERE oltid = '".$this->id."'");
		$getswitchpon = $row->fetchAll(PDO::FETCH_ASSOC);
		return $getswitchpon;
	}	
	protected function get_pon_cdata12() {	
		$row = $this->pdo->query("SELECT id, pon FROM switch_pon WHERE oltid = '".$this->id."'");
		$getswitchpon = $row->fetchAll(PDO::FETCH_ASSOC);
		return $getswitchpon;
	}
	protected function get_pon_zte6() {	
		$cmd = [];
		$row = $this->pdo->query("SELECT id, pon FROM switch_pon WHERE oltid = '".$this->id."'");
		$getswitchpon = $row->fetchAll(PDO::FETCH_ASSOC);
		if (isset($getswitchpon) && count($getswitchpon) > 0) {
			foreach ($getswitchpon as $pon) {
				$data = str_replace('olt-', '', $pon['pon']);
				if (stripos($data, 'gpon') !== false) {
					$cmd[] = "show mac gpon olt gpon-olt_".trim(substr($data, 5));
				} 
				if (stripos($data, 'epon') !== false) {
					$cmd[] = "show mac epon olt epon-olt_".trim(substr($data, 5));
				}
			}
		}
		return $cmd;
	}	
	protected function get_pon_zte3() {	
		$cmd = [];
		$row = $this->pdo->query("SELECT id, pon FROM switch_pon WHERE oltid = '".$this->id."'");
		$getswitchpon = $row->fetchAll(PDO::FETCH_ASSOC);
		if (isset($getswitchpon) && count($getswitchpon) > 0) {
			foreach ($getswitchpon as $pon) {
				$data = $pon['pon'];
				if (stripos($data, 'gpon') !== false) {
					$cmd[] = "show mac gpon olt gpon-olt_".trim(substr($data, 5));
				} 
				if (stripos($data, 'epon') !== false) {
					$cmd[] = "show mac epon olt epon-olt_".trim(substr($data, 5));
				}
			}
		}
		return $cmd;
	}
	protected function extract_name($data) {
		if (preg_match('/enable(.*?)#/', $data, $matches)) {
			return trim($matches[1]); 
		}
		if (preg_match('/(.*?)#/', $data, $matches)) {
			return trim($matches[1]); 
		}
		return '';
	}	
	protected function get_onu_bdcom() {
		$temp_onu = [];
		$row = $this->pdo->query("SELECT sw_shelf, sw_slot, sw_port, idonu, keyonu, type, inface FROM onus WHERE olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			foreach ($getswitchonu as $on) {
				preg_match('/(\d+)\/(\d+):(\d+)/',$on['inface'],$mat);
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['idonu'] = $on['idonu'];
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['inface'] = $on['inface'];
			}
		}
		return $temp_onu;		
	}		
	protected function get_onu_cdata16_v3() {
		$temp_onu = [];
		$row = $this->pdo->query("SELECT sw_shelf, sw_slot, sw_port, idonu, keyonu, type, inface FROM onus WHERE olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			foreach ($getswitchonu as $on) {
				preg_match('/(\d+)\/(\d+):(\d+)/',$on['inface'],$mat);
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['idonu'] = $on['idonu'];
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['inface'] = $on['inface'];
			}
		}
		return $temp_onu;		
	}	
	protected function get_onu_cdata11() {
		$temp_onu = [];
		$row = $this->pdo->query("SELECT sw_shelf, sw_slot, sw_port, idonu, keyonu, type, inface FROM onus WHERE olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			foreach ($getswitchonu as $on) {
				preg_match('/(\d+)\/(\d+):(\d+)/',$on['inface'],$mat);
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['idonu'] = $on['idonu'];
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['inface'] = $on['inface'];
			}
		}
		return $temp_onu;		
	}	
	protected function get_onu_cdata12() {
		$temp_onu = [];
		$row = $this->pdo->query("SELECT sw_shelf, sw_slot, sw_port, idonu, keyonu, type, inface FROM onus WHERE olt = '".$this->id."'");
		$getswitchonu = $row->fetchAll(PDO::FETCH_ASSOC);
		if (isset($getswitchonu) && count($getswitchonu) > 0) {
			foreach ($getswitchonu as $on) {
				preg_match('/(\d+)\/(\d+):(\d+)/',$on['inface'],$mat);
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['idonu'] = $on['idonu'];
				$temp_onu[$on['type']][$mat[1]][$mat[2]][$mat[3]]['inface'] = $on['inface'];
			}
		}
		return $temp_onu;		
	}
	protected function CData12_Parser() {
		$temp_onu = $this->get_onu_cdata12();
		if($this->result_fdb!=false){
			$pattern = '/\b([0-9A-Fa-f:]{17})\s+([0-9]+)\s+pon0\/([0-9]+)\/([0-9]+)\s+([0-9]+)\s+(dynamic|static)\b/i';
			preg_match_all($pattern, $this->result_fdb, $cdata12);
		}	
		if(!empty($cdata12)){
			foreach ($cdata12[1] as $index => $fullMatch) {
				$mac = trim($cdata12[1][$index]);
				$idonu = $temp_onu['epon'][0][$cdata12[4][$index]][$cdata12[5][$index]]['idonu'];
				$temp_fdb_array[] = array(
					'idonu'=> $idonu,'olt'=> $this->id,'vlan'=> trim($cdata12[2][$index]),'mac' => $this->format_Mac($mac,1),'type'=> 'epon','inface'=> "0/{$cdata12[4][$index]}:{$cdata12[5][$index]}",'port'=> trim($cdata12[4][$index]),'onu'=> trim($cdata12[5][$index])				
				);
			}		
		}
		return $temp_fdb_array;
	}	
	protected function CData11_Parser() {
        $temp_onu = $this->get_onu_cdata11();
        $temp_fdb_array = [];
        if ($this->result_fdb != false) {
            $pattern = '/show olt (\d+) mac-address-table\s+.*?MAC Address Table =+\s+(.*?)===========/s';
			preg_match_all($pattern, $this->result_fdb, $matches, PREG_SET_ORDER);
			$olt_data = [];
			foreach ($matches as $match) {
				$olt_number = $match[1];
				$section = $match[2];
				preg_match_all('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})\s+(\d+)\s+(\d+)\s+(\d+)/i', $section, $mac_matches, PREG_SET_ORDER);
				foreach ($mac_matches as $mac) {
					$port = $olt_number;
					$onu_id = intval($mac[2]);
					$idonu = $temp_onu['epon'][0][$port][$onu_id]['idonu'];
					$temp_fdb_array[] = [
                        'idonu' => $onu_id,
                        'olt' => $this->id,
                        'vlan' => $mac[3],
                        'mac' => $this->format_Mac($mac[1], 1),
                        'type' => 'epon',
                        'inface' => "0/{$port}:{$onu_id}",
                        'port' => $port,
                        'onu' => $onu_id
                    ];
				}
			}
        }
        return $temp_fdb_array;
    }
	protected function Cdata16_v3_Parser() {
		$temp_onu = $this->get_onu_cdata16_v3();
		$pattern = '/\b([0-9A-Fa-f:]{17})\s+([0-9]+)\s+([0-9]+|-)\s+([0-9]+|-)\s+gpon0\/([0-9]+)\/([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(dynamic|static)\b/i';
		if($this->result_fdb!=false){
			preg_match_all($pattern, $this->result_fdb, $cdata16);
		}
		if(!empty($cdata16)){
			foreach ($cdata16[1] as $index => $fullMatch) {
				$mac = trim($cdata16[1][$index]);
				$idonu = $temp_onu['gpon'][0][$cdata16[6][$index]][$cdata16[7][$index]]['idonu'];
					$temp_fdb_array[] = array(
					'idonu'=> $idonu,'olt'=> $this->id,'vlan'=> trim($cdata16[2][$index]),'svlan'=> trim($cdata16[3][$index]),'mac'=> $this->format_Mac($mac,1),'inface'=> "0/{$cdata16[6][$index]}:{$cdata16[7][$index]}",'pon'=> 'gpon','port'=> trim($cdata16[6][$index]),'onu'=> trim($cdata16[7][$index])		
				);
			}
		}
		return $temp_fdb_array;
	}
	protected function BDCOM_Epon_Parser() {
		$temp_onu = $this->get_onu_bdcom();
		if($this->result_fdb!=false){
			$pattern = '/\b([0-9]+)\s+([A-F0-9]{4}\.[A-F0-9]{4}\.[A-F0-9]{4})\s+(dynamic|static)\s+epon([0-9]+)\/([0-9]+):([0-9]+)\b/i';
			preg_match_all($pattern, $this->result_fdb, $bdcom);
		}
		if(!empty($bdcom)){
			foreach ($bdcom[0] as $index => $fullMatch) {
				$mac = trim($bdcom[2][$index]);
				$idonu = $temp_onu['epon'][0][$bdcom[5][$index]][$bdcom[6][$index]]['idonu'];
				$temp_fdb_array[] = array(
					'idonu'=> $idonu,'olt'=> $this->id,'vlan'=> trim($bdcom[1][$index]),'mac' => $this->format_Mac($mac,1),'type'=> trim($bdcom[3][$index]),'inface'=> "0/{$bdcom[5][$index]}:{$bdcom[6][$index]}",'port'=> trim($bdcom[5][$index]),'onu'=> trim($bdcom[6][$index])			
				);
			}		
		}
		return $temp_fdb_array;
	}
	protected function ZTE3_Parser() {
		$temp_fdb_array = [];
		$temp_onu = $this->get_onu_zte3();		
		if($this->result_fdb!=false){
			$pattern = '/\b([A-F0-9]{4}\.[A-F0-9]{4}\.[A-F0-9]{4})\s+([0-9]+)\s+Dynamic\s+([a-z]+)-onu_([0-9]+)\/([0-9]+)\/([0-9]+):([0-9]+)/i';
			preg_match_all($pattern, $this->result_fdb, $zte);
		}
		if(!empty($zte)){
			foreach ($zte[1] as $key => $mac) {
				$inface = $temp_onu[$zte[3][$key]][$zte[4][$key]][$zte[5][$key]][$zte[6][$key]][$zte[7][$key]]['idonu'];
				if(isset($inface)){
					$temp_fdb_array[] = array(
						'idonu' => $inface,'olt' => $this->id,'sw_shelf' => $zte[4][$key],'sw_slot' => $zte[5][$key],'sw_port' => $zte[6][$key],'type' => $zte[3][$key],'keyonu' => $zte[7][$key],'inface' => "{$zte[4][$key]}/{$zte[5][$key]}/{$zte[6][$key]}:{$zte[7][$key]}",'mac' => $this->format_Mac($mac,1),'vlan' => intval($zte[2][$key])
					);
				}
			}
		}
		return $temp_fdb_array;
	}		
	public function BDCOM_Epon_Save($temp) {
		$current_records = [];
		$sql_onu = $this->pdo->query("SELECT mac, idonu FROM fdb_tables WHERE olt = '" . $this->id . "'");
		while ($row = $sql_onu->fetch(PDO::FETCH_ASSOC)) {
			$current_records[$row['idonu']][] = $row;
		}
		$new_records = [];
		foreach ($temp as $oltid => $ont) {
			if (isset($ont['idonu'])) {
				$mac = $ont['mac'];
				$new_records[$ont['idonu']][] = [
					'mac' => $ont['mac'],'idonu' => $ont['idonu'],'inface' => $ont['inface'],'vlan' => $ont['vlan']
				];
			}
		}
		$to_add = [];
		$to_update = [];
		$to_delete = [];
		foreach ($new_records as $idonu => $new_data_array) {
			if (isset($current_records[$idonu])) {
				foreach ($new_data_array as $new_data) {
					$found = false;
					foreach ($current_records[$idonu] as $current_data) {
						if ($current_data['idonu'] === $new_data['idonu']) {
							if ($current_data['mac'] !== $new_data['mac']) {
								$to_update[$new_data['mac']] = [
									'old' => $current_data,'new' => $new_data
								];
							}
							$found = true;
							break;
						}
					}

					if (!$found) {
						$to_add[] = $new_data;
					}
				}
			} else {
				foreach ($new_data_array as $new_data) {
					$to_add[] = $new_data;
				}
			}
		}
		foreach ($current_records as $idonu => $current_data_array) {
			foreach ($current_data_array as $current_data) {
				$found = false;
				if(isset($new_records[$idonu])){
					foreach ($new_records[$idonu] as $new_data) {
						if ($current_data['mac'] === $new_data['mac']) {
							$found = true;
							break;
						}
					}
				}
				if (!$found) {
					$to_delete[] = $current_data;
				}
			}
		}
		if (!empty($to_delete)) {
			$escaped_values = array_map(function ($value) {
				return "'" . addslashes($value['mac']) . "'";
			}, $to_delete);
			$placeholders = implode(',', $escaped_values);
			$delete_query = "DELETE FROM fdb_tables WHERE mac IN ($placeholders) AND olt = '" . addslashes($this->id) . "'";
			$this->pdo->query($delete_query);
		}
		if (!empty($to_add)) {
			foreach ($to_add as $data) {
				$add_query = "INSERT INTO fdb_tables (mac, inface, olt, idonu, vlan, added) VALUES ('" . $data['mac'] . "', '" . $data['inface'] . "', '" . $this->id . "', '" . $data['idonu'] . "', '" . $data['vlan'] . "', '" . $this->clock . "')";
				$this->pdo->query($add_query);
			}
		}
		if (!empty($to_update)) {
			foreach ($to_update as $mac => $data) {
				$update_query = "UPDATE fdb_tables SET 
				inface = '" . $data['new']['inface'] . "', 
				idonu = '" . $data['new']['idonu'] . "', 
				mac = '" . $data['new']['mac'] . "', 
				vlan = '" . $data['new']['vlan'] . "', 
				added = '" . $this->clock . "' 
				WHERE 
				mac = '" . $data['old']['mac'] . "' 
				AND olt = '" . $this->id . "'
				AND idonu = '" . $data['new']['idonu'] . "'
				";
				$this->pdo->query($update_query);
				if(isset($this->confPMon['FDB_CHANGED_LOG']) && !empty($this->confPMon['FDB_CHANGED_LOG']) && $this->confPMon['FDB_CHANGED_LOG'] == 1){
					$this->ONT_log($mac, $data['old'], $data['new']);
				}
				if(isset($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && !empty($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && $this->confPMon['FDB_CHANGED_NOTIFiCATION'] == 1){
					$this->Notification($mac, $data['old'], $data['new']);
				}
			}
		}
	}	
	public function CData12_Save($temp) {
		$current_records = [];
		$sql_onu = $this->pdo->query("SELECT mac, idonu FROM fdb_tables WHERE olt = '" . $this->id . "'");
		while ($row = $sql_onu->fetch(PDO::FETCH_ASSOC)) {
			$current_records[$row['idonu']][] = $row;
		}
		$new_records = [];
		foreach ($temp as $oltid => $ont) {
			if (isset($ont['idonu'])) {
				$mac = $ont['mac'];
				$new_records[$ont['idonu']][] = [
					'mac' => $ont['mac'],
					'idonu' => $ont['idonu'],
					'inface' => $ont['inface'],
					'vlan' => $ont['vlan']
				];
			}
		}
		$to_add = [];
		$to_update = [];
		$to_delete = [];
		foreach ($new_records as $idonu => $new_data_array) {
			if (isset($current_records[$idonu])) {
				foreach ($new_data_array as $new_data) {
					$found = false;
					foreach ($current_records[$idonu] as $current_data) {
						if ($current_data['idonu'] === $new_data['idonu']) {
							if ($current_data['mac'] !== $new_data['mac']) {
								$to_update[$new_data['mac']] = [
									'old' => $current_data,
									'new' => $new_data
								];
							}
							$found = true;
							break;
						}
					}

					if (!$found) {
						$to_add[] = $new_data;
					}
				}
			} else {
				foreach ($new_data_array as $new_data) {
					$to_add[] = $new_data;
				}
			}
		}
		foreach ($current_records as $idonu => $current_data_array) {
			foreach ($current_data_array as $current_data) {
				$found = false;
				if(isset($new_records[$idonu])){
					foreach ($new_records[$idonu] as $new_data) {
						if ($current_data['mac'] === $new_data['mac']) {
							$found = true;
							break;
						}
					}
				}
				if (!$found) {
					$to_delete[] = $current_data;
				}
			}
		}
		if (!empty($to_delete)) {
			$escaped_values = array_map(function ($value) {
				return "'" . addslashes($value['mac']) . "'";
			}, $to_delete);
			$placeholders = implode(',', $escaped_values);
			$delete_query = "DELETE FROM fdb_tables WHERE mac IN ($placeholders) AND olt = '" . addslashes($this->id) . "'";
			$this->pdo->query($delete_query);
		}
		if (!empty($to_add)) {
			foreach ($to_add as $data) {
				$add_query = "INSERT INTO fdb_tables (mac, inface, olt, idonu, vlan, added) VALUES ('" . $data['mac'] . "', '" . $data['inface'] . "', '" . $this->id . "', '" . $data['idonu'] . "', '" . $data['vlan'] . "', '" . $this->clock . "')";
				$this->pdo->query($add_query);
			}
		}
		if (!empty($to_update)) {
			foreach ($to_update as $mac => $data) {
				$update_query = "UPDATE fdb_tables SET 
				inface = '" . $data['new']['inface'] . "', 
				idonu = '" . $data['new']['idonu'] . "', 
				mac = '" . $data['new']['mac'] . "', 
				vlan = '" . $data['new']['vlan'] . "', 
				added = '" . $this->clock . "' 
				WHERE 
				mac = '" . $data['old']['mac'] . "' 
				AND olt = '" . $this->id . "'
				AND idonu = '" . $data['new']['idonu'] . "'
				";
				$this->pdo->query($update_query);
				if(isset($this->confPMon['FDB_CHANGED_LOG']) && !empty($this->confPMon['FDB_CHANGED_LOG']) && $this->confPMon['FDB_CHANGED_LOG'] == 1){
					$this->ONT_log($mac, $data['old'], $data['new']);
				}
				if(isset($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && !empty($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && $this->confPMon['FDB_CHANGED_NOTIFiCATION'] == 1){
					$this->Notification($mac, $data['old'], $data['new']);
				}
			}
		}
	}	
	public function CData11_Save($temp) {
		$current_records = [];
		$sql_onu = $this->pdo->query("SELECT mac, idonu FROM fdb_tables WHERE olt = '" . $this->id . "'");
		while ($row = $sql_onu->fetch(PDO::FETCH_ASSOC)) {
			$current_records[$row['idonu']][] = $row;
		}
		$new_records = [];
		foreach ($temp as $oltid => $ont) {
			if (isset($ont['idonu'])) {
				$mac = $ont['mac'];
				$new_records[$ont['idonu']][] = [
					'mac' => $ont['mac'],
					'idonu' => $ont['idonu'],
					'inface' => $ont['inface'],
					'vlan' => $ont['vlan']
				];
			}
		}
		$to_add = [];
		$to_update = [];
		$to_delete = [];
		foreach ($new_records as $idonu => $new_data_array) {
			if (isset($current_records[$idonu])) {
				foreach ($new_data_array as $new_data) {
					$found = false;
					foreach ($current_records[$idonu] as $current_data) {
						if ($current_data['idonu'] === $new_data['idonu']) {
							if ($current_data['mac'] !== $new_data['mac']) {
								$to_update[$new_data['mac']] = [
									'old' => $current_data,
									'new' => $new_data
								];
							}
							$found = true;
							break;
						}
					}

					if (!$found) {
						$to_add[] = $new_data;
					}
				}
			} else {
				foreach ($new_data_array as $new_data) {
					$to_add[] = $new_data;
				}
			}
		}
		foreach ($current_records as $idonu => $current_data_array) {
			foreach ($current_data_array as $current_data) {
				$found = false;
				if(isset($new_records[$idonu])){
					foreach ($new_records[$idonu] as $new_data) {
						if ($current_data['mac'] === $new_data['mac']) {
							$found = true;
							break;
						}
					}
				}
				if (!$found) {
					$to_delete[] = $current_data;
				}
			}
		}
		if (!empty($to_delete)) {
			$escaped_values = array_map(function ($value) {
				return "'" . addslashes($value['mac']) . "'";
			}, $to_delete);
			$placeholders = implode(',', $escaped_values);
			$delete_query = "DELETE FROM fdb_tables WHERE mac IN ($placeholders) AND olt = '" . addslashes($this->id) . "'";
			$this->pdo->query($delete_query);
		}
		if (!empty($to_add)) {
			foreach ($to_add as $data) {
				$add_query = "INSERT INTO fdb_tables (mac, inface, olt, idonu, vlan, added) VALUES ('" . $data['mac'] . "', '" . $data['inface'] . "', '" . $this->id . "', '" . $data['idonu'] . "', '" . $data['vlan'] . "', '" . $this->clock . "')";
				$this->pdo->query($add_query);
			}
		}
		if (!empty($to_update)) {
			foreach ($to_update as $mac => $data) {
				$update_query = "UPDATE fdb_tables SET 
				inface = '" . $data['new']['inface'] . "', 
				idonu = '" . $data['new']['idonu'] . "', 
				mac = '" . $data['new']['mac'] . "', 
				vlan = '" . $data['new']['vlan'] . "', 
				added = '" . $this->clock . "' 
				WHERE 
				mac = '" . $data['old']['mac'] . "' 
				AND olt = '" . $this->id . "'
				AND idonu = '" . $data['new']['idonu'] . "'
				";
				$this->pdo->query($update_query);
				if(isset($this->confPMon['FDB_CHANGED_LOG']) && !empty($this->confPMon['FDB_CHANGED_LOG']) && $this->confPMon['FDB_CHANGED_LOG'] == 1){
					$this->ONT_log($mac, $data['old'], $data['new']);
				}
				if(isset($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && !empty($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && $this->confPMon['FDB_CHANGED_NOTIFiCATION'] == 1){
					$this->Notification($mac, $data['old'], $data['new']);
				}
			}
		}
	}
	public function ZTE3_Save($temp) {
		$current_records = [];
		$sql_onu = $this->pdo->query("SELECT mac, idonu FROM fdb_tables WHERE olt = '" . $this->id . "'");
		while ($row = $sql_onu->fetch(PDO::FETCH_ASSOC)) {
			$current_records[$row['idonu']][] = $row;
		}
		$new_records = [];
		foreach ($temp as $oltid => $ont) {
			if (isset($ont['idonu'])) {
				$mac = $ont['mac'];
				$new_records[$ont['idonu']][] = [
					'mac' => $ont['mac'],
					'idonu' => $ont['idonu'],
					'inface' => $ont['inface'],
					'vlan' => $ont['vlan']
				];
			}
		}
		$to_add = [];
		$to_update = [];
		$to_delete = [];
		foreach ($new_records as $idonu => $new_data_array) {
			if (isset($current_records[$idonu])) {
				foreach ($new_data_array as $new_data) {
					$found = false;
					foreach ($current_records[$idonu] as $current_data) {
						if ($current_data['idonu'] === $new_data['idonu']) {
							if ($current_data['mac'] !== $new_data['mac']) {
								$to_update[$new_data['mac']] = [
									'old' => $current_data,
									'new' => $new_data
								];
							}
							$found = true;
							break;
						}
					}

					if (!$found) {
						$to_add[] = $new_data;
					}
				}
			} else {
				foreach ($new_data_array as $new_data) {
					$to_add[] = $new_data;
				}
			}
		}
		foreach ($current_records as $idonu => $current_data_array) {
			foreach ($current_data_array as $current_data) {
				$found = false;
				if(isset($new_records[$idonu])){
					foreach ($new_records[$idonu] as $new_data) {
						if ($current_data['mac'] === $new_data['mac']) {
							$found = true;
							break;
						}
					}
				}
				if (!$found) {
					$to_delete[] = $current_data;
				}
			}
		}
		if (!empty($to_delete)) {
			$escaped_values = array_map(function ($value) {
				return "'" . addslashes($value['mac']) . "'";
			}, $to_delete);
			$placeholders = implode(',', $escaped_values);
			$delete_query = "DELETE FROM fdb_tables WHERE mac IN ($placeholders) AND olt = '" . addslashes($this->id) . "'";
			$this->pdo->query($delete_query);
		}
		if (!empty($to_add)) {
			foreach ($to_add as $data) {
				$add_query = "INSERT INTO fdb_tables (mac, inface, olt, idonu, vlan, added) VALUES ('" . $data['mac'] . "', '" . $data['inface'] . "', '" . $this->id . "', '" . $data['idonu'] . "', '" . $data['vlan'] . "', '" . $this->clock . "')";
				$this->pdo->query($add_query);
			}
		}
		if (!empty($to_update)) {
			foreach ($to_update as $mac => $data) {
				$update_query = "UPDATE fdb_tables SET 
				inface = '" . $data['new']['inface'] . "', 
				idonu = '" . $data['new']['idonu'] . "', 
				mac = '" . $data['new']['mac'] . "', 
				vlan = '" . $data['new']['vlan'] . "', 
				added = '" . $this->clock . "' 
				WHERE 
				mac = '" . $data['old']['mac'] . "' 
				AND olt = '" . $this->id . "'
				AND idonu = '" . $data['new']['idonu'] . "'
				";
				$this->pdo->query($update_query);
				if(isset($this->confPMon['FDB_CHANGED_LOG']) && !empty($this->confPMon['FDB_CHANGED_LOG']) && $this->confPMon['FDB_CHANGED_LOG'] == 1){
					$this->ONT_log($mac, $data['old'], $data['new']);
				}
				if(isset($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && !empty($this->confPMon['FDB_CHANGED_NOTIFiCATION']) && $this->confPMon['FDB_CHANGED_NOTIFiCATION'] == 1){
					$this->Notification($mac, $data['old'], $data['new']);
				}
			}
		}
	}
	public function Notification($mac, $old_data, $new_data) {
		$message = $this->escape_sql("OLT [b]{$this->data_switch['place']}[/b] ONT [b]{$new_data['inface']}[/b] changed client's MAC from [b]{$old_data['mac']}[/b] to [b]{$new_data['mac']}[/b]");
		$notification = "INSERT INTO notification (status, type, system, message, added) VALUES (1, '777', 'fdbparser', '".$message."','" . $this->clock . "')";
		$this->pdo->query($notification);
	}	
	public function ONT_log($mac, $old_data, $new_data) {
		$message = $this->escape_sql("Сhanged client's MAC from {$old_data['mac']} to {$new_data['mac']}");
		$logger = "INSERT INTO onus_log (idonu, idolt, types, message, added) VALUES ('{$new_data['idonu']}', '" . $this->id . "', 'mac', '{$message}','" . $this->clock . "');";
		$this->pdo->query($logger);
		$this->logging("ONT {$new_data['inface']}:".$message, 'fdb_table_changed');
	}
	protected function escape_sql($string) {
		return str_replace(
			["\\", "\x00", "\n", "\r", "'", '"', "\x1a"], 
			["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"], 
			$string
		);
	}
	public function telnet_bdcom($command, $subname) {
		$collectedData = '';  // Збирає всі отримані дані
		$lastMatch = '';      // Останній виявлений шаблон
		$repeatCount = 0;     // Лічильник повторюваних шаблонів
		$repeatThreshold = 2; // Поріг повторень для завершення збору даних
		$moreFound = true;    // Прапорець для контролю циклу
		$initialData = $this->telnet->do_lite("$command\r", true);
		$collectedData .= $initialData;
		while ($moreFound) {
			$responseData = $this->telnet->do_lite("\r", true);
			$collectedData .= $responseData;
			$foundEnd = stripos($responseData, '#') !== false;
			$foundConfig = stripos($responseData, "{$subname}#") !== false;        
			if ($foundEnd || $foundConfig) {
				$currentMatch = $foundEnd ? '#' : "{$subname}#";
				if ($currentMatch === $lastMatch) {
					$repeatCount++;
				} else {
					$lastMatch = $currentMatch;
					$repeatCount = 1;
					usleep(100000);
				}
				if ($repeatCount >= $repeatThreshold) {
					$moreFound = false;
				}
			} else {
				$repeatCount = 0;
			}
			usleep(100000);
		}
		return $collectedData;	
	}
	public function Cdata16() {
		$this->telnet = new PMonTelnet($this->data_switch);
		$temp = $this->telnet->do_comand("enable\r", true);
		$temp .= $this->telnet->do_comand("config\r", true);
		$subname = $this->extract_name($temp);
		$this->result_fdb = '';		
		if(isset($subname) && $subname!=false){
			$sqlpon = $this->get_pon_bdcom_epon();
			foreach($sqlpon as $pon) {
				$gpon = str_replace('gpon', 'pon', trim(strtolower($pon['pon'])));
				$command = "show mac-address port {$gpon}";
				$startTime = time();
				$timeout = 10;
				$result_fdb = '';
				try {
					$result_fdb .=  $this->telnet_bdcom($command, $subname);
					$this->result_fdb .= $result_fdb;
				} catch (Exception $e) {
					$this->logging("CDATA16_{$subname}: Error for command: $comanda\n", 'fdb_parser_error');
				}
				if ((time() - $startTime) > $timeout) {
					$this->logging("CDATA16_{$subname}: Timeout exceeded for command: $comanda\n", 'fdb_parser_error');
					continue;
				}
				usleep(2000000);
			}
		}
		return $this->Cdata16_v2_Parser();								
	}	
	protected function Cdata16_v2_Parser() {
		$temp_onu = $this->get_onu_cdata16_v3();
		$pattern = '/\b([0-9A-Fa-f:]{17})\s+([0-9]+)\s+([0-9]+|-)\s+([0-9]+|-)\s+gpon0\/([0-9]+)\/([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(dynamic|static)\b/i';
		if($this->result_fdb!=false){
			preg_match_all($pattern, $this->result_fdb, $cdata16);
		}
		if(!empty($cdata16)){
			foreach ($cdata16[1] as $index => $fullMatch) {
				$mac = trim($cdata16[1][$index]);
				$idonu = $temp_onu['gpon'][0][$cdata16[6][$index]][$cdata16[7][$index]]['idonu'];
					$temp_fdb_array[] = array(
					'idonu'=> $idonu,'olt'=> $this->id,
					'vlan'=> trim($cdata16[2][$index]),
					'svlan'=> trim($cdata16[3][$index]),
					'mac'=> $this->format_Mac($mac,1),
					'inface'=> "0/{$cdata16[6][$index]}:{$cdata16[7][$index]}",
					'pon'=> 'gpon',
					'port'=> trim($cdata16[6][$index]),
					'onu'=> trim($cdata16[7][$index])		
				);
			}
		}
		return $temp_fdb_array;
	}
	public function Cdata16_v3() {
		$this->telnet = new PMonTelnet($this->data_switch);
		$temp = $this->telnet->do_comand("enable\r", true);
		$temp .= $this->telnet->do_comand("config\r", true);
		$subname = $this->extract_name($temp);
		$this->result_fdb = '';		
		if(isset($subname) && $subname!=false){
			$sqlpon = $this->get_pon_bdcom_epon();
			foreach($sqlpon as $pon) {
				$gpon = str_replace('gpon', 'pon', trim(strtolower($pon['pon'])));
				$command = "show mac-address port {$gpon}";
				$startTime = time();
				$timeout = 10;
				$result_fdb = '';
				try {
					$result_fdb .=  $this->telnet_bdcom($command, $subname);
					$this->result_fdb .= $result_fdb;
				} catch (Exception $e) {
					$this->logging("CDATA16_v3_{$subname}: Error for command: $comanda\n", 'fdb_parser_error');
				}
				if ((time() - $startTime) > $timeout) {
					$this->logging("CDATA16_v3_{$subname}: Timeout exceeded for command: $comanda\n", 'fdb_parser_error');
					continue;
				}
				usleep(2000000);
			}
		}
		return $this->Cdata16_v3_Parser();						
	}
	public function BDCOM_Epon() {
		$this->telnet = new PMonTelnet($this->data_switch);
		$temp = $this->telnet->do_comand("enable\r", true);
		$subname = $this->extract_name($temp);
		$this->result_fdb = '';		
		if(isset($subname) && $subname!=false){
			$sqlpon = $this->get_pon_bdcom_epon();
			foreach($sqlpon as $pon) {
				$epon = trim(strtolower(str_replace(' ', '', $pon['pon'])));
				$command = "show mac address-table interface {$epon}";
				$startTime = time();
				$timeout = 10;
				$result_fdb = '';
				try {
					$result_fdb .=  $this->telnet_bdcom($command, $subname);
					$this->result_fdb .= $result_fdb;
				} catch (Exception $e) {
					$this->logging("BDCOM_{$subname}: Error for command: $comanda\n", 'fdb_parser_error');
				}
				if ((time() - $startTime) > $timeout) {
					$this->logging("BDCOM_{$subname}: Timeout exceeded for command: $comanda\n", 'fdb_parser_error');
					continue;
				}
				usleep(2000000);
			}
		}
		return $this->BDCOM_Epon_Parser();		
	}	
	public function Cdata12() {
		$this->telnet = new PMonTelnet($this->data_switch);
		$temp = $this->telnet->do_comand("enable\r", true);
		$temp .= $this->telnet->do_comand("config\r", true);
		$subname = $this->extract_name($temp);
		$this->result_fdb = '';		
		if(isset($subname) && $subname!=false){
			$sqlpon = $this->get_pon_cdata12();
			foreach($sqlpon as $pon) {
				$epon = trim(strtolower(str_replace('0/', '0/0/', $pon['pon'])));
				$command = "show mac-address port {$epon} with-ont-location";
				$startTime = time();
				$timeout = 10;
				$result_fdb = '';
				try {
					$result_fdb .=  $this->telnet_bdcom($command, $subname);
					$this->result_fdb .= $result_fdb;
				} catch (Exception $e) {
					$this->logging("CDATA12_{$subname}: Error for command: $comanda\n", 'fdb_parser_error');
				}
				if ((time() - $startTime) > $timeout) {
					$this->logging("CDATA12_{$subname}: Timeout exceeded for command: $comanda\n", 'fdb_parser_error');
					continue;
				}
				usleep(2000000);
			}
		}
		return $this->CData12_Parser();	
	}	
	public function Cdata11() {
		$this->telnet = new PMonTelnet($this->data_switch);
		$temp = $this->telnet->do_comand("\r", true);
		$subname = $this->extract_name($temp);
		$this->result_fdb = '';		
		if(isset($subname) && $subname!=false){
			$sqlpon = $this->get_pon_cdata12();
			foreach($sqlpon as $pon) {
				$epon = trim(strtolower(str_replace(' ', '', str_replace('0/', '', $pon['pon']))));
				$command = "show olt {$epon} mac-address-table";
				$startTime = time();
				$timeout = 10;
				$result_fdb = '';
				try {
					$result_fdb .=  $this->telnet_bdcom($command, $subname);
					$this->result_fdb .= $result_fdb;
				} catch (Exception $e) {
					$this->logging("CDATA11_{$subname}: Error for command: $comanda\n", 'fdb_parser_error');
				}
				if ((time() - $startTime) > $timeout) {
					$this->logging("CDATA11_{$subname}: Timeout exceeded for command: $comanda\n", 'fdb_parser_error');
					continue;
				}
				usleep(2000000);
			}
		}
		return $this->CData11_Parser();	
	}
	public function ZTE6() {
		$this->telnet = new PMonTelnet($this->data_switch);
		$temp = $this->telnet->do_comand("config\r", true);
		$subname = $this->extract_name($temp);
		$list_pon = $this->get_pon_zte6();
		$this->result_fdb = '';
		if (isset($list_pon) && count($list_pon) > 0) {
			$timeout = 10;
			foreach ($list_pon as $comanda) {
				$startTime = time();
				$result_fdb = '';
				try {
					$result = $this->telnet->do_comand("{$comanda}\r", true);
					while (strpos($result, "--More--") !== false) {
						$result_fdb .= $this->telnet->do_lite("\r", true);
						print_R($result_fdb);
						usleep(1000);
						if ((time() - $startTime) > $timeout) {
							$this->logging("ZTE6_{$subname}: Timeout exceeded for command: $comanda\n", 'fdb_parser_error');
							break;
						}
						if (preg_match('/\bend\b/i', $result_fdb) || strpos($result_fdb, '#') !== false	|| strpos($subname, '#') !== false || empty($result_fdb)) {
							break;
						}
					}
					if (empty($result_fdb)) {
						$this->logging("ZTE6_{$subname}: Command returned no result: $comanda\n", 'fdb_parser_error');
					}
					$this->result_fdb .= $result_fdb;
				} catch (Exception $e) {
					$this->logging('Not connect', 'fdb_parser_error');
					continue;
				}
			}
		}
		die;
		return $this->ZTE3_Parser();
	}
	public function ZTE3() {
		$this->telnet = new PMonTelnet($this->data_switch);
		$temp = $this->telnet->do_comand("config\r", true);
		$subname = $this->extract_name($temp);
		$list_pon = $this->get_pon_zte3();
		$this->result_fdb = '';
		if (isset($list_pon) && count($list_pon) > 0) {
			$timeout = 10;
			foreach ($list_pon as $comanda) {
				$startTime = time();
				$result_fdb = '';
				try {
					$result = $this->telnet->do_comand("{$comanda}\r", true);
					while (strpos($result, "--More--") !== false) {
						$result_fdb .= $this->telnet->do_lite("\r", true);
						usleep(1000);
						if ((time() - $startTime) > $timeout) {
							$this->logging("ZTE3_{$subname}: Timeout exceeded for command: $comanda\n", 'fdb_parser_error');
							break;
						}
						if (preg_match('/\bend\b/i', $result_fdb) || strpos($result_fdb, '#') !== false	|| strpos($subname, '#') !== false || empty($result_fdb)) {
							break;
						}
					}
					if (empty($result_fdb)) {
						$this->logging("ZTE3_{$subname}: Command returned no result: $comanda\n", 'fdb_parser_error');
					}
					$this->result_fdb .= $result_fdb;
				} catch (Exception $e) {
					$this->logging('Not connect', 'fdb_parser_error');
					continue;
				}
			}
		}
		return $this->ZTE3_Parser();
	}

}
?>
