<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Logger {
    private $db;    
    public $pmon_config;    
    public $lang;    
    public function __construct($db, $confPMon, $lang) {
        $this->db = $db;
        $this->pmon_config = $confPMon;
        $this->lang = $lang;
    }
    public function init(array $data): void {
        $logType = $data['log'] ?? '';
        switch ($logType) {
            case 'onu':
                $this->logOnu($data);
                break;            
			case 'ont':
                #$this->logOnt($data);
                break;
            case 'device':
                $this->logDevice($data);
                break;
            case 'user':
                $this->logUser($data);
                break;
            default:
                throw new Exception('Invalid log type.');
        }
    }
	private function logOnu(array $data): void {
		$type = $data['type'] ?? '';
		match ($type) {
			'deletonu' => $this->otherOnu($data),
			default => $this->otherOnu($data),
		};
	}	
	private function logOnt(array $data): void {
		$type = $data['type'] ?? '';
		match ($type) {
			'status' => $this->statusOnt($data),
			'signal' => $this->signalOnt($data)
		};
	}
	private function statusOnt(array $data): void {
		$sql = "INSERT INTO onus_log 
			(`idonu`,`idolt`,`status`,`message`,`types`,`added`) VALUES 
				('{$data['idonu']}','{$data['olt']}','{$data['status']}','{$data['message']}','status','{$data['time']}')";
		$this->db->query($sql);
	}	
	private function replace(array $data): string {
		$message = $data['message'];		
		foreach ($data as $key => $value) {
			if ($key !== 'message') {
				$message = str_replace("{" . $key . "}", $value, $message);
			}
		}		
		return $message;
	}
	private function signalOnt(array $data): void {		
		$temp = array(
			'message' => $this->lang['change_signal'],
			'last' => $data['last'],
			'curent' => $data['curent']
		);
		$message = $this->replace($temp);		
		$sql = "INSERT INTO onus_log 
			(`idonu`,`idolt`,`status`,`message`,`types`,`added`) VALUES 
				('{$data['idonu']}','{$data['olt']}','1','{$message}','signal','{$data['time']}')";
		$this->db->query($sql);
	}
	private function logDevice(array $data): void {
		$type = $data['type'] ?? '';
		match ($type) {
			'telnet' => $this->device($data),
			'backup' => $this->device($data),
			'ssh' => $this->device($data),
			'snmp' => $this->device($data),
			default => $this->device($data),
		};
	}
    private function device(array $data): void {
		if (!isset($data['descr'], $data['deviceid'])) {
			return;
		}		
		$sql = ['deviceid' => $data['deviceid'],'descr' => $data['descr'],'type' => $data['type'] ? $data['type']:'check','added' => date('Y-m-d H:i:s')];
		if (!empty($data['userid'])) {
			$sql['who'] = 'users';
			$sql['userid'] = $data['userid'];
			$sql['username'] = $data['username'];
		}else{
			$sql['who'] = $data['who'];
		}
		$this->db->SQLinsert('devicelogs', $sql);
    }
	private function otherOnu(array $data): void {
		if (!isset($data['descr'], $data['deviceid'], $data['onuid'])) {
			return;
		}		
		$sql = ['log' => 'onu','deviceid' => $data['deviceid'],'onuid' => $data['onuid'],'descr' => $data['descr'],'type' => $data['type'] ?? 'check','added' => date('Y-m-d H:i:s')];
		if (!empty($data['userid'])) {
			$sql['who'] = 'users';
			$sql['userid'] = $data['userid'];
			$sql['username'] = $data['username'];
		}else{
			$sql['who'] = $data['who'];
		}
		$this->db->SQLinsert('devicelogs', $sql);
	}
}
$logger = new Logger($db,$confPMon,$lang);
?>
