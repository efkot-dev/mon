<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMonAccess {
    private $lang;
    private $pdo;
    private $user;
    private $dostyp;
	private $confPMon;
	private $cacheManager;
    public function __construct($lang, $pdo, $USER, $confPMon, $cacheManager) {
        $this->pdo = $pdo;
		$this->confPMon = $confPMon; 
		$this->cacheManager = $cacheManager; 
        $this->lang = $lang;
		if(isset($USER) && !empty($USER['id'])){
			$this->user = $USER;	
			$this->dostyp = $this->getaccess();
		}	
    }
	public function list_access_switch(){
		$rules = [];
		if (isset($this->user['id'])) {
			$r = $this->get_sql();
			if(isset($r) && count($r) > 0){
				foreach($r as $t) {
					if(!empty($t['types']) && preg_match('/dev(\d+)/', $t['types'], $portMatch)) {
						$rules[$portMatch[1]] = $portMatch[1];
					}
				}
			}else{
				return false;
			}
		}
		return $rules;	
	}
	private function get_sql() {
		$expiration = 360;
		$data = [];
		if (
			isset($this->confPMon['CACHE']) &&
			!empty($this->confPMon['CACHE']) &&
			$this->confPMon['CACHE'] == 1
		) {
			$cacheKey = "user_access_" . $this->user['id'];
			$cachedResult = $this->cacheManager->get($cacheKey);
			if ($cachedResult !== null) {
				$data = $cachedResult;
			} else {
				$data = $this->getUserAccessFromDb($this->user['id']);
				$this->cacheManager->set($cacheKey, $data, $expiration);
			}
		} else {
			$data = $this->getUserAccessFromDb($this->user['id']);
		}
		return $data;
	}
	private function getUserAccessFromDb($userId) {
		$stmt = $this->pdo->prepare("SELECT * FROM checkaccess WHERE uid = :uid");
		$stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	private function getaccess(){
		$rules = array();
		if (isset($this->user['id'])) {
			$r = $this->get_sql();
			if(isset($r) && count($r) > 0){
				foreach($r as $t) {
					if(!empty($t['types'])) {
						$rules[$t['types']] = true;
					}
				}
			}else{
				return false;
			}
		}
		return $rules;
	}	
	public function getaccessapi($userid) {
		$rules = array();
		if (!empty($userid)) {
			$stmt = $this->pdo->prepare("SELECT * FROM checkaccess WHERE uid = :uid");
			$stmt->bindParam(':uid', $userid, PDO::PARAM_INT);
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rows && count($rows) > 0) {
				foreach ($rows as $t) {
					if (!empty($t['types'])) {
						$rules[$t['types']] = true;
					}
				}
			} else {
				return false;
			}
		}
		return $rules;
	}
	public function getapidostyp($commands,$userid){
		$data = $this->getaccessapi($userid);
		if (!empty($data[$commands])) {
			return true;
		} else {
			return false;
		}	
	}
	public function get($commands){
		if (!empty($this->dostyp[$commands])) {
			return true;
		} else {
			return false;
		}
	}
	public function check_access($user, $device_id, $user_id) {
		$allowed_devices = explode(',', $user[$user_id]);
		if (in_array($device_id, $allowed_devices)) {
			return true;
		} else {
			return false;
		}
	}
}
$access = new PMonAccess($lang, $pdo, $USER, $confPMon, $cacheManager);
?>
