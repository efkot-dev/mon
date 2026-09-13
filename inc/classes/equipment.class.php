<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Equipment { 
	public $switches;
	public $switchoidid;
	public $switchoid;	
    private $db;
    private $cacheManager;
	public function __construct($id, $db, $cacheManager){
		$this->db = $db;	
		$this->cacheManager = $cacheManager;	
		$this->switches = [];
		$expiration = 7200;
		$sqlselectswitch = 'SELECT * FROM switch WHERE id = '.$id.' LIMIT 1';
		if (isset($this->cacheManager->confPMon['CACHE']) && $this->cacheManager->confPMon['CACHE'] == 1) {
			$cacheKey = "equipment_" . $id;
			$cachedSwitch = $this->cacheManager->get($cacheKey);
			if ($cachedSwitch !== null) {
				$this->switches[$cachedSwitch['switchid']] = $cachedSwitch;
				$this->switchoidid = $cachedSwitch['switchoidid'];
			} else {
				if (is_numeric($id)) {
					$switch = $this->db->Simple($sqlselectswitch);
					if (!empty($switch['id'])) {
						$this->switches[$switch['id']] = [
							'switchid' => $switch['id'],
							'switchip' => $switch['netip'],
							'switchcommunity' => $switch['snmpro'],
							'switchoidid' => $switch['oidid'],
						];
						$this->switchoidid = $switch['oidid'];
					}
				}
				$this->cacheManager->set($cacheKey, $this->switches[$switch['id']], $expiration);
			}
		} else {
			if (is_numeric($id)) {
				$switch = $this->db->Simple($sqlselectswitch);
				if (!empty($switch['id'])) {
					$this->switches[$switch['id']] = [
						'switchid' => $switch['id'],
						'switchip' => $switch['netip'],
						'switchcommunity' => $switch['snmpro'],
						'switchoidid' => $switch['oidid'],
					];
					$this->switchoidid = $switch['oidid'];
				}
			}
		}
		$this->loadswitchoid($id);
	}
	public function loadswitchoid($id = '') {
		if (!$this->switchoidid || !isset($this->switches[$id])) {
			return;
		}
		$selectoidid = "SELECT * FROM oid WHERE oidid = " . $this->switchoidid;
		$expiration = 14400;
		$cacheKey = "oidid_" . $this->switchoidid;
		if (isset($this->cacheManager->confPMon['CACHE']) && $this->cacheManager->confPMon['CACHE'] == 1) {
			$cachedSwitch = $this->cacheManager->get($cacheKey);
			if ($cachedSwitch === null) {
				$sql = $this->db->SimpleWhile($selectoidid);
				if (count($sql) > 0) {
					foreach ($sql as $sqlid => $conf) {
						$oidEntry = [
							'oid' => $conf['oid']
						];
						if (!empty($conf['result'])) {
							$oidEntry['result'] = $conf['result'];
						}
						$this->switchoid[$id][$conf['inf']][$conf['types']][$conf['pon']] = $oidEntry;
					}
					$this->cacheManager->set($cacheKey, $this->switchoid[$id], $expiration);
				}
			} else {
					$this->switchoid[$id] = $cachedSwitch;
			}
		} else {
			$sql = $this->db->SimpleWhile($selectoidid);
			if (count($sql) > 0) {
				foreach ($sql as $sqlid => $conf) {
					$oidEntry = [
						'oid' => $conf['oid']
					];
					if (!empty($conf['result'])) {
						$oidEntry['result'] = $conf['result'];
					}
					$this->switchoid[$id][$conf['inf']][$conf['types']][$conf['pon']] = $oidEntry;
				}
			}
		}
	}
}
?>