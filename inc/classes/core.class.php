<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Monitor{
	private int $id;
	private Equipment $device;
	private $getModel;
    private $db;
    private $logger;
    private $php_class_device;	
    private $classMap;	
    private $cache;	
	public function __construct($id, $phpclass, $db, $logger, $classMap, $cacheManager, $php_class_device){
		if(is_numeric($id)){
			$this->db = $db;	
			$this->classMap = $classMap;
			$this->logger = $logger;	
			$this->cache = $cacheManager;	
			$this->php_class_device = $php_class_device;	
			$this->id = $id;	
			$this->device = new Equipment($id,$db,$this->cache);	
			$this->getModel = $this->initclass($id, $phpclass, $this->device);
		}
	}
	protected function loadfile($phpclass) {	
		if (isset($this->php_class_device[$phpclass]) && isset($this->classMap[$phpclass])) {
			$className = $this->classMap[$phpclass];
			$get_file = ENGINE_DIR . 'classes/' . $this->php_class_device[$phpclass];
			if (file_exists($get_file) && !class_exists($className, false)) {
				require $get_file;
			}
		}
	}
	protected function initclass($id, $phpclass, $oiddevice) {
		$this->loadfile($phpclass);
		if(is_numeric($id)){
			if (isset($this->classMap[$phpclass])) {
				$className = $this->classMap[$phpclass];
				if (class_exists($className)) {
					return new $className($id, $oiddevice, $this->db, $this->logger);
				} else {
					die('Class not found: ' . $className);
				}
			} else {
				die('Unsupported class: ' . $phpclass);
			}
		}else{
			die('Unsupported id: ' . $phpclass);
		}
    }
	public function start() {
		return $this->getModel->Load();
	}	
	public function tempSaveEpon($dataOnu){
		$this->getModel->tempSaveOnuEpon($dataOnu);	
	}		
	public function savePort($dataPort){
		$this->getModel->savePort($dataPort);	
	}	
	public function tempSaveGpon($dataOnu){
		$this->getModel->tempSaveOnuGpon($dataOnu);	
	}	
	public function updateSignalCheck(){
		$this->getModel->tempUpdateSignalCheck($this->id);	
	}		
	public function tempSaveSignalEpon($dataOnu){
		$this->getModel->tempSaveSignalSaveOnuEpon($dataOnu);	
	}	
	public function tempSaveSignalGpon($dataOnu){
		$this->getModel->tempSaveSignalSaveOnuGpon($dataOnu);	
	}		
	public function RxOltSaveSignalEpon($dataOnu){
		$this->getModel->tempSaveSignalSaveRxOnuEpon($dataOnu);	
	}	
	public function RxOltSaveSignalGpon($dataOnu){
		$this->getModel->tempSaveSignalSaveRxOnuGpon($dataOnu);	
	}	
	public function getSupportPort(){
		return $this->getModel->Support('port');
	}	
	public function getPollerRxOlt(){
		return $this->getModel->Support('rxolt');
	}	
	public function getSupportOnu(){
		return $this->getModel->Support('onu');
	}	
	public function getPollerOnu(){
		return $this->getModel->Support('poller');
	}	
	public function getSupportSaveOnu(){
		return $this->getModel->Support('saveonu');
	}		
	public function saveOnuPmon(){
		return $this->getModel->saveOnuCommands();
	}	
	public function getPort(){
		return $this->getModel->Port();
	}
	public function getListSignal(){
		return $this->getModel->getListOnuOnline();	
	}	
	public function UpdateInformationOlt(){
		return $this->getModel->StatisticOLT();	
	}		
	public function Poller($dataOnu){
		return $this->getModel->PollerOnu($dataOnu);	
	}		
	public function getDataPoller($dataOnu){
		return $this->getModel->getOnuPoller($dataOnu);	
	}		
	public function getDataRxPoller($dataOnu){
		return $this->getModel->getOnuRxPoller($dataOnu);	
	}	
}
?>
