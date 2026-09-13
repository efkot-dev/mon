<?php
if (!defined('PONMONITOR')){
    die('Hacking attempt!');
}

class DeleteManager {
    private $pdo;
    public $confPMon;
    public $logger;
    public $metod;
    public $telnet;
    public $device;
    public $clock;
    public $onu;
    public $usr;
    public $lang;
    public function __construct($pdo, $confPMon, $logger, $USER, $lang) {
		$this->pdo = $pdo;
		$this->confPMon = $confPMon;
		$this->logger = $logger;
		$this->usr = $USER;
		$this->lang = $lang;
		$this->clock = date('Y-m-d H:i:s');
	}
    public function delete_pmon($idonu) {
		delete_onu($idonu);
		$message = $this->lang['log_deletonu'].' '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->onu['mac'];
		$this->logger->init(['log'=>'onu','type'=>'deletonu','descr'=>$message,'deviceid'=>$this->onu['olt'],'onuid'=>$this->onu['idonu'],'userid'=>$this->usr['id'],'username'=>$this->usr['username']]);
		$this->pdo->prepare("INSERT INTO notification (status, type, system, message, added) VALUES (1, 27, 'monitor', ?, ?)")->execute(['[icon-stop] [b]'.$this->device['place'].'[/b] '.$message.', [b]'.$this->lang['class_3'].'[/b]: '.$this->usr['username'], $this->clock]);
	}
    public function bdcom_epon_delete_snmp($idonu) {
		$this->onu = getOntById($this->pdo,$idonu);
		if(isset($this->onu['portolt']) && !empty($this->onu['portolt'])){
			$snmp_oid_infx = $this->onu['portolt'].'.'.$this->epon_hex_mac_bdcom($this->onu['mac']);
			@snmp2_set($this->device['netip'],$this->device['snmprw'],"1.3.6.1.4.1.3320.101.11.1.1.2.".$snmp_oid_infx,'i',"0");
			$this->delete_pmon($idonu);
		}
	}
    public function delet_snmp($idonu) {
		if($this->device['oidid']==1){
			$this->bdcom_epon_delete_snmp($idonu);
		}elseif($this->device['oidid']==2){
			
		}		
	}    
	public function delet_telnet($idonu) {
		if($this->device['oidid']==1){
			$this->bdcom_epon_delete_telnet($idonu);
		}elseif($this->device['oidid']==2){
			
		}		
	}
    public function delete_ont($idonu) {
		if($this->metod=='snmp'){
			$this->delet_snmp($idonu);
		}else{
			$this->delet_telnet($idonu);
		}
	}
    public function epon_hex_mac_bdcom($mac){
		$mac = trim($mac);
		$mac_clean = str_replace(':', '', $mac);
		$ip_parts = [];
		for ($i = 0; $i < strlen($mac_clean); $i += 2) {
			$ip_parts[] = hexdec(substr($mac_clean, $i, 2));
		}
		$ip_string = implode('.', $ip_parts);
		return $ip_string;
	}
    public function signature($switch){ 
		$this->device = $switch;	
		if(!empty($this->device['snmprw'])){
			$this->metod = 'snmp';
		}else{
			$this->telnet = new PMonTelnet($switch);	
			$this->metod = 'telnet';
		}		
    }
}
$DeleteManager = new DeleteManager($pdo, $confPMon, $logger, $USER, $lang);
?>
