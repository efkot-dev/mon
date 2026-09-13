<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMon{
    private $db;    
    private $telnet;    
    private $olt;    
    private $onu;    
  
    private $types;    
    public function __construct($db) {
        $this->db = $db;
    }
    public function port(string $cmd, array $data) {
        if(is_array($data)){
            return match ($cmd) {
                'shutdown' => $this->shutdown($data),
                'noshutdown' => $this->noshutdown($data),
                default => die('not support'),
            };
        } else {
            die('not support data');
        }
    }    
	public function init(string $cmd, array $data) {
        if(is_array($data)){
            return match ($cmd) {
                'speedprofile' => $this->speedprofile($data),
                'configonu' => $this->configonu($data),
                'configonuzte3' => $this->configonuzte3($data),
                'deletonuhuawei' => $this->deletonuhuawei($data),
                'fdbmac' => $this->fdbmac($data),
                'zte6port' => $this->zte6port($data),
                'disableonuzte3' => $this->disableonuzte3($data),
                'enableonuzte3' => $this->enableonuzte3($data),
                'zte3port' => $this->zte3port($data),
                'reboot_pon' => $this->zte3resetcard($data),
                'reboot_onu' => $this->zte3rebootall($data),
                'rebootonu' => $this->rebootonu($data),
				'bdcomeponportup' => $this->bdcomeponportup($data),
				'bdcomeponportdown' => $this->bdcomeponportdown($data),
				'gcomeponrebootonu' => $this->gcomeponrebootonu($data),
				'deletonugcomepon' => $this->gcomepondeletonu($data),
				'deletonubdcomgpon' => $this->deletonubdcomgpon($data),
				'deletonubdcomepon' => $this->deletonubdcomepon($data),
				'deletonucdata11' => $this->deletonucdata11($data),
				'deletonucdata12' => $this->deletonucdata12($data),
				'deletonuzte6' => $this->deletonuzte6($data),
				'deletonuzte3' => $this->deletonuzte3($data),
				'resetonuzte3' => $this->resetonuzte3($data),
				'blacklist11' => $this->blacklist11data($data),
				'blacklist12' => $this->blacklist12data($data),
                default => die('not support'),
            };
        } else {
            die('not support data');
        }
    }
    private function bdcomeponportup(array $data) {
        $this->dataonuswitch($data);
        return $this->bdcom_epon_portup();
    }    
	private function deletonubdcomepon(array $data) {
        $this->dataonuswitch($data);
        return $this->bdcom_epon_delet_onu();
    }	
	private function deletonubdcomgpon(array $data) {
        $this->dataonuswitch($data);
        return $this->bdcom_gpon_delet_onu();
    }		
	private function gcomeponrebootonu(array $data) {
        $this->dataonuswitch($data);
        return $this->gcom_epon_reboot_onu();
    }		
	private function gcomepondeletonu(array $data) {
        $this->dataonuswitch($data);
        return $this->gcom_epon_delet_onu();
    }	
	private function deletonuhuawei(array $data) {
        $this->dataonuswitch($data);
        return $this->huawei_delet_onu();
    }
	private function deletonucdata11(array $data) {
		$this->dataonuswitch($data);
		return $this->delet_onu_cdata11();
	}
	private function deletonucdata12(array $data) {
		$this->dataonuswitch($data);
		return $this->delet_onu_cdata12();
	}	
	private function zte6port(array $data) {
		$this->dataonuswitch($data);
		if(isset($data['types']) && !empty($data['types']))
			$this->types = $data['types'];
		return $this->port_onu_zte6();
	}	
	private function zte3port(array $data) {
		$this->dataonuswitch($data);
		if(isset($data['types']) && !empty($data['types']))
			$this->types = $data['types'];		
		if(isset($data['port']) && !empty($data['port']))
			$this->port = $data['port'];
		return $this->port_onu_zte3();
	}	
	private function deletonuzte6(array $data) {
		$this->dataonuswitch($data);
		return $this->delet_onu_zte6();
	}	
	private function disableonuzte3(array $data) {
		$this->dataonuswitch($data);
		return $this->disable_onu_zte3();
	}	
	private function enableonuzte3(array $data) {
		$this->dataonuswitch($data);
		return $this->enable_onu_zte3();
	}	
	private function deletonuzte3(array $data) {
		$this->dataonuswitch($data);
		return $this->delet_onu_zte3();
	}	
	private function resetonuzte3(array $data) {
		$this->dataonuswitch($data);
		return $this->reset_onu_zte3();
	}
	private function bdcomeponportdown(array $data) {
        $this->dataonuswitch($data);
        return $this->bdcom_epon_portdown();
    }    
	private function blacklist12data(array $data) {
        $this->dataonuswitch($data);
        return $this->blacklist12();
    }	
	private function blacklist11data(array $data) {
        $this->dataonuswitch($data);
        return $this->blacklist11();
    }
    private function speedprofile(array $data) {
		$this->dataonuswitch($data);
		$oidid = $this->olt['oidid'];
		switch ($oidid) {
			case 1:
				return $this->bdcomepon_speed_profile($data);
			default:
				die('not support fun');
		}
	}     
	private function configonu(array $data) {
		$this->dataonuswitch($data);
		$oidid = $this->olt['oidid'];
		switch ($oidid) {
			case 1:
				return $this->bdcomepon_configonu();
			case 2:
				return $this->bdcomgpon_configonu();
			default:
				die('not support fun');
		}
	}     
	private function shutdown(array $data) {
		$oidid = $data['oidid'];
		switch ($oidid) {
			case 1:
				return $this->portbdcomepon($data);
			case 2:
				return $this->portbdcomgpon($data);
			case 3:
				#return $this->portzte2($data);
			case 7:
			case 34:
				#return $this->portzte3($data);			
			case 6:
				#return $this->portzte6($data);
			case 12:
				return $this->portcdata16($data);
			case 14:
			case 33:
				#return $this->porthuawei($data);
			case 15:
				return $this->portcdata12($data);			
			case 13:
				#return $this->portcdata11($data);
			default:
				die('not support fun');
		}
	} 	
	private function fdbmac(array $data) {
		$this->dataonuswitch($data);
		$oidid = $this->olt['oidid'];
		switch ($oidid) {
			case 1:
				return $this->bdcomeponfdb();
			case 2:
				return $this->bdcomgponfdb();
			case 3:
				return $this->zte2fdb();
			case 7:
			case 34:
				return $this->zte3fdb();			
			case 6:
				return $this->zte6fdb();
			case 12:
			case 35:
				return $this->cdata16fdb();
			case 14:
			case 33:
				return $this->huaweifdb();
			case 15:
				return $this->cdata12fdb();			
			case 13:
				return $this->cdata11fdb();
			default:
				die('not support fun');
		}
	}    
	private function rebootonu(array $data) {
		$this->dataonuswitch($data);
		$oidid = $this->olt['oidid'];
		switch ($oidid) {
			case 1:
				return $this->bdcomeponrebootonu();
			case 9:
				return $this->gcomeponreboot();
			case 2:
				return $this->bdcomgponrebootonu();
			case 3:
				return $this->zte2rebootonu();
			case 6:
				return $this->zte6rebootonu();			
			case 7:
				return $this->zte3rebootonu();			
			case 34:
				return $this->zte3rebootonu_1();
			case 12:
			case 35:
				return $this->cdata16rebootonu();
			case 14:
			case 33:
				return $this->huaweirebootonu();
			case 15:
				return $this->cdata12rebootonu();			
			case 13:
				return $this->cdata11rebootonu();
			default:
				die('not support fun');
		}
	}
	public function zte3resetcard(array $data) {
		$this->dataswitch($data);		
		return $this->zte3_reset_card($data);
	}	
	public function zte3rebootall(array $data) {
		$this->dataswitch($data);		
		return $this->zte3_reboot_onu_card($data);
	}
	### ZTE REBOOT ONU ALL CARD 
   	protected function zte3_reboot_onu_card($data) {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot all onu port');
		}
		$out = $this->telnet->do_comand("conf t\r",true);
		foreach ($data['list'] as $keys => $arr_onu) {			
			$this->telnet->do_comand("pon-onu-mng {$arr_onu['type']}-onu_".$arr_onu['inface']."\r");
			$this->telnet->do_comand("reboot\r");
			$this->telnet->do_comand("exit\r");
		}
		$this->telnet->do_comand("exit\r");
		return $this->resultdata(['type'=>'array','status'=>'ok','result'=> 1]);
	}
	### ZTE RESET CARD 
   	protected function zte3_reset_card($data) {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reset-card rackno');
		}
		preg_match('/(\d+)\/(\d+)\/(\d+)/i',$data['nameport'],$match);
		$commands = array("conf t","reset-card rackno {$match[1]} shelfno {$match[2]} slotno {$match[3]}","yes","exit","exit");
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}
	### CDATA 16 GPON PORT 
   	protected function portcdata16($data) {
		$this->telnet = new PMonTelnet($data);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('port');
		}	
		if(stripos((string)$data['interface'], 'gpon') !== false){
			preg_match('/0\/0\/(\d+)/i',$data['interface'],$match);
			$port = "gpon 0/0";
		}elseif(stripos((string)$data['interface'], 'xge') !== false){
			preg_match('/0\/0\/(\d+)/i',$data['interface'],$match);
			$port = "xge 0/0";
		}elseif(stripos((string)$data['interface'], 'ge') !== false){
			preg_match('/0\/0\/(\d+)/i',$data['interface'],$match);
			$port = "ge 0/0";
		}else{
			die('emp');
		}	
		$status_port = (isset($data['status_port']) && $data['status_port']=='noshutdown'?'no shutdown '.$match[1]:'shutdown '.$match[1]);
		$commands = array(
			"enable",
			"config",
			"interface ".$port,
			$status_port,
			"exit",
			"save"
		);	
		$output = $this->telnet->executeCommands($commands);
		if(preg_match('/%/i',$output)) {
			return true;
		}else{
			return false;
		}
    } 	
	### CDATA 12 EPON PORT 
   	protected function portcdata12($data) {
		$this->telnet = new PMonTelnet($data);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('port');
		}	
		if(stripos((string)$data['interface'], 'epon') !== false){
			preg_match('/0\/(\d+)/i',$data['interface'],$match);
			$port = "epon 0/0";
		}elseif(stripos((string)$data['interface'], 'xge') !== false){
			preg_match('/0\/0\/(\d+)/i',$data['interface'],$match);
			$port = "xge 0/0";
		}elseif(stripos((string)$data['interface'], 'ge') !== false){
			preg_match('/0\/0\/(\d+)/i',$data['interface'],$match);
			$port = "ge 0/0";
		}else{
			die('emp');
		}	
		$status_port = (isset($data['status_port']) && $data['status_port']=='noshutdown'?'no shutdown '.$match[1]:'shutdown '.$match[1]);
		$commands = array(
			"enable",
			"config",
			"interface ".$port,
			$status_port,
			"exit",
			"save"
		);	
		$output = $this->telnet->executeCommands($commands);
		if(preg_match('/%/i',$output)) {
			return true;
		}else{
			return false;
		}
    } 	
	### BDCOM EPON PORT 
   	protected function portbdcomepon($data) {
		$this->telnet = new PMonTelnet($data);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('port');
		}	
		if(preg_match('/EPON/i',$data['interface'])){
			preg_match('/0\/(\d+)/i',$data['interface'],$match);
			$port = "ePON 0/".$match[1];
		}elseif(preg_match('/GigaEthernet/i',$data['interface'])){
			preg_match('/0\/(\d+)/i',$data['interface'],$match);
			$port = "GigaEthernet 0/".$match[1];
		}elseif(preg_match('/TGigaEthernet/i',$data['interface'])){
			preg_match('/0\/(\d+)/i',$data['interface'],$match);
			$port = "TGigaEthernet 0/".$match[1];
		}		
		$status_port = (isset($data['status_port']) && $data['status_port']=='noshutdown'?'no shutdown':'shutdown');
		$commands = array(
			"enable","config","interface ".$port,$status_port,"exit","wr","exit","exit"
		);	
		$output = $this->telnet->executeCommands($commands);
		if(preg_match('/OK!/i',$output)) {
			return true;
		}else{
			return false;
		}
    } 	
	### BDCOM GPON PORT 
   	protected function portbdcomgpon($data) {
		$this->telnet = new PMonTelnet($data);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('port');
		}	
		if(preg_match('/GPON/i',$data['interface'])){
			preg_match('/0\/(\d+)/i',$data['interface'],$match);
			$port = "gPON 0/".$match[1];
		}elseif(preg_match('/GigaEthernet/i',$data['interface'])){
			preg_match('/0\/(\d+)/i',$data['interface'],$match);
			$port = "GigaEthernet 0/".$match[1];
		}elseif(preg_match('/TGigaEthernet/i',$data['interface'])){
			preg_match('/0\/(\d+)/i',$data['interface'],$match);
			$port = "TGigaEthernet 0/".$match[1];
		}		
		$status_port = (isset($data['status_port']) && $data['status_port']=='noshutdown'?'no shutdown':'shutdown');
		$commands = array(
			"enable","config","interface ".$port,$status_port,"exit","wr","exit","exit"
		);	
		$output = $this->telnet->executeCommands($commands);
		if(preg_match('/Sa/i',$output)) {
			return true;
		}else{
			return false;
		}
    } 	
	### CDATA 16
   	protected function cdata16fdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('show mac address-table interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}	
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("enable","config","show mac-address port pon 0/0/".$match[1]." ont ".$match[2]);
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    }    	
	protected function cdata16rebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("enable","config","interface epon 0/0 ","ont reboot ".$match[1]." ".$match[2]."");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    } 	
	### CDATA 12
   	protected function cdata12fdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('show mac address-table interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}	
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("enable","config","show mac-address ont 0/0/".$match[1]." ".$match[2]."");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    } 	
	### CDATA 11
   	protected function cdata11fdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('show mac address-table interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}	
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("show olt ".$match[1]." onu ".$match[2]." mac-address-table");
		$output = $this->telnet->executeCommands($commands);
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    }    	
	protected function cdata12rebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("enable","config","interface epon 0/0 ","ont reboot ".$match[1]." ".$match[2]."");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    }	
	protected function cdata11rebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("olt ".$match[1]."","onu ".$match[2]."","ctc reboot");
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    }
    // C-DATA 11 delet onu
	protected function delet_onu_cdata11() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($err_num){
			$this->telnet->err('add blacklist '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("olt ".$match[1]."","no-bind onu-id ".$match[2],"save");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}
	// ZTE 3 reset onu
	protected function reset_onu_zte3() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($this->onu['type']=='gpon'){
			if($err_num){
				$this->telnet->err('delet onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
			}
			preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
			$commands = array(
				"conf t","pon-onu-mng gpon-onu_".$this->onu['inface'],"yes","exit","exit"
			);
			$output = $this->telnet->executeCommands($commands);
			$this->sleeps(1);
		}elseif($this->onu['type']=='epon'){
			if($err_num){
				$this->telnet->err('delet onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
			}
			preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
			$commands = array("conf t","pon-onu-mng epon-onu_".$this->onu['inface'],"yes","exit","exit");
			$output = $this->telnet->executeCommands($commands);
			$this->sleeps(1);	
		}
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}	
	// ZTE 3 delet onu
	protected function delet_onu_zte3() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($this->onu['type']=='gpon'){
			if($err_num){
				$this->telnet->err('delet onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
			}
			preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
			$commands = array("conf t","","interface gpon-olt_".$match[1]."/".$match[2]."/".$match[3],"no onu ".$match[4],"exit","exit");
			$output = $this->telnet->executeCommands($commands);
			$this->sleeps(1);
			if(preg_match('/Successful/i',$output)) {
				return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
			}else{
				return $this->resultdata(['type'=>'array','status'=>'err','result'=> $this->onu]);
			}
		}else{
			if($err_num){
				$this->telnet->err('delet onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
			}
			preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
			$commands = array("conf t","interface epon-olt_".$match[1]."/".$match[2]."/".$match[3],"no onu ".$match[4],"yes","exit","exit");
			$output = $this->telnet->executeCommands($commands);
			$this->sleeps(1);
			if(preg_match('/Successful/i',$output)) {
				return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
			}else{
				return $this->resultdata(['type'=>'array','status'=>'err','result'=> $this->onu]);
			}			
		}        
	}		
	// ZTE 3 enable onu 
	protected function enable_onu_zte3() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($err_num){
			$this->telnet->err('disable onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		if($this->onu['type']=='gpon'){
			$commands = array(
				"conf t",
				"interface ".$this->onu['type']."-onu_".$this->onu['inface'],
				"no shutdown",
				"exit","exit","exit");
		}else{
			$commands = array(
				"conf t",
				"interface ".$this->onu['type']."-onu_".$this->onu['inface'],
				"admin enable",
				"exit","exit","exit");			
		}
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}	
	// ZTE 3 disable onu 
	protected function disable_onu_zte3() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($err_num){
			$this->telnet->err('disable onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		if($this->onu['type']=='gpon'){
			$commands = array(
				"conf t",
				"interface gpon-onu_".$this->onu['inface'],
				"shutdown","exit","exit");			
		}else{
			$commands = array(
				"conf t",
				"interface epon-onu_".$this->onu['inface'],
				"admin disable",
				"exit",
				"exit");
		}
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}		
	// ZTE 6 delet onu
	protected function delet_onu_zte6() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($err_num){
			$this->telnet->err('delet onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("conf t","interface ".$this->onu['type']."_olt-".$match[1]."/".$match[2]."/".$match[3],"no onu ".$match[4]);
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}	
	// ZTE 6 port onu
	protected function port_onu_zte6() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($err_num){
			$this->telnet->err('port '.$this->types.' '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		$commands = array("conf t","pon-onu-mng ".$this->onu['type']."_onu-".$this->onu['inface'],"interface eth eth_0/1 state ".($this->types=='enable'?'unlock':'lock'));
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}	
	// ZTE 3 port onu
	protected function port_onu_zte3() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($err_num){
			$this->telnet->err('port '.$this->types.' '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		$commands = array("conf t","pon-onu-mng ".$this->onu['type']."_onu-".$this->onu['inface'],"interface eth eth_0/".(isset($this->port) ? $this->port : '1')." state ".($this->types=='enable'?'unlock':'lock'));
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}	
	// C-DATA 12 delet onu
	protected function delet_onu_cdata12() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if($err_num){
			$this->telnet->err('add blacklist '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("enable","config","interface epon 0/0 ","ont del  ".$match[1]." ".$match[2]);
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}
    protected function blacklist11() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
        if($err_num){
            $this->telnet->err('auth blacklist add '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
        }
        preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
        $commands = array("auth blacklist add ".$match[1]." onu  ".str_replace(':', '-',$this->onu['mac']));
        $output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
		if(preg_match('/blacklist\s+successfully/i',$output)) {
			return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
		}else{
			return $this->resultdata(['type'=>'array','status'=>'Not work: auth blacklist add '.$this->onu['type'].' '.$this->onu['inface'],'result'=> $this->onu]);
		}
    }    
	protected function blacklist12() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
        if($err_num){
            $this->telnet->err('blacklist add '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
        }
        preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
        $commands = array(
			"enable",
			"config",
			"interface epon 0/0",
			"ont black-list add ".$match[1]." ".$this->onu['mac']
		);
        $output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
		if(preg_match('/successfully/i',$output)) {
			return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
		}else{
			return $this->resultdata(['type'=>'array','status'=>'Not work: auth blacklist add '.$this->onu['type'].' '.$this->onu['inface'],'result'=> $this->onu]);
		}
    }
	protected function format_config($data) {
		$currentInterfaceConfig = '';
		if($data!=false){
			$lines = explode("\n", $data);
			foreach ($lines as $line) {
				$currentInterfaceConfig .= "<span>".$line . "</span>";
			}
		}
		return $currentInterfaceConfig;	
	}
    protected function bdcomepon_speed_profile($data) {
		$this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
		/*
			config
			interface epon0/X:Y
			epon sla upstream pir 614400 cir 512
			epon sla downstream pir 614400 cir 512
		*/
		$commands = array(
			"enable",
			"config",
			"interface EPON ".$this->onu['inface'],
			"epon sla upstream pir {$data['u_pir']} cir {$data['u_cir']}",
			"epon sla downstream pir {$data['d_pir']} cir {$data['d_cir']}",
			"write all"
		);
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
	}
	protected function bdcomgponfdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		$commands = [];
		if($err_num){	
			$this->telnet->err('show mac address-table interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		$commands = [];
		$out_pass = '';
		if (!empty($this->olt['enablepassword'])) {
			$out_enable = $this->telnet->do_comand("enable\r", true);
			if (preg_match('/password/i', $out_enable)) {
				$out_pass = $this->telnet->do_comand($this->olt['enablepassword'] . "\r", true);
			}
			if (preg_match('/denied|access denied/i', $out_pass)) {
				$this->telnet->err('Access denied! - Verifying or setting an enable password');
			}
		} else {
			$commands[] = 'enable';
		}		
		$commands[] = "show mac address-table interface gpON ".$this->onu['inface'];
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    }
	protected function bdcomeponfdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('show mac address-table interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}
		$commands = [];
		$out_pass = '';
		if (!empty($this->olt['enablepassword'])) {
			$out_enable = $this->telnet->do_comand("enable\r", true);
			if (preg_match('/password/i', $out_enable)) {
				$out_pass = $this->telnet->do_comand($this->olt['enablepassword'] . "\r", true);
			}
			if (preg_match('/denied|access denied/i', $out_pass)) {
				$this->telnet->err('Access denied! - Verifying or setting an enable password');
			}
		} else {
			$commands[] = 'enable';
		}
		$commands[] = "show mac address-table interface EPON".$this->onu['inface'];
		$output = $this->telnet->executeCommands($commands);
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    }
    protected function bdcomepon_configonu() {
		$this->telnet = new PMonTelnet($this->olt);
		if ($this->telnet->err_num) {
			$this->telnet->err('show running-config interface EPON ' .$this->onu['inface'] . ' ' .$this->telnet->descr($this->telnet->err_num));
		}
		$commands = [];
		$out_pass = '';
		if (!empty($this->olt['enablepassword'])) {
			$out_enable = $this->telnet->do_comand("enable\r", true);
			if (preg_match('/password/i', $out_enable)) {
				$out_pass = $this->telnet->do_comand($this->olt['enablepassword'] . "\r", true);
			}
			if (preg_match('/denied|access denied/i', $out_pass)) {
				$this->telnet->err('Access denied! - Verifying or setting an enable password');
			}
		} else {
			$commands[] = 'enable';
		}
		$commands[] = 'config';
		$commands[] = 'show running-config interface EPON ' . $this->onu['inface'];
		if (!empty($commands)) {
			$output = $this->telnet->executeCommands($commands);
			$this->telnet->disconnect('exit');
			$data = $this->format_config($output);
		}
		return $this->resultdata(['type' => 'array', 'result' => $data ?? []]);
	}   
	protected function bdcom_epon_portdown() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
		$numport = (isset($this->onu['numport'])?$this->onu['numport']:1);
        if($err_num){
            $this->telnet->err('epon onu port '.$numport.' ctc shutdown  '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
        }
        $commands = array("enable","config","interface EPON".$this->onu['inface'],"epon onu port ".$numport." ctc shutdown","exit","write","exit","exit");
        $output = $this->telnet->executeCommands($commands);
        $this->telnet->disconnect('exit');
        return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    }    
	protected function bdcom_epon_portup() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
		$numport = (isset($this->onu['numport'])?$this->onu['numport']:1);
        if($err_num){
            $this->telnet->err('epon onu port '.$numport.' ctc shutdown  '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
        }
		$numport = (isset($this->onu['numport'])?$this->onu['numport']:1);
        $commands = array("enable","config","interface EPON".$this->onu['inface'],
		"no epon onu port ".$numport." ctc shutdown","exit","write","exit","exit");
        $output = $this->telnet->executeCommands($commands);
        $this->telnet->disconnect('exit');
        return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    }	
	protected function bdcom_epon_delet_onu() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
        if($err_num){
            $this->telnet->err('no epon bind-onu EPON '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
        }
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$macolt  = rtrim(preg_replace('/(.{4})/','\1.',str_replace(':','',$this->onu['mac']),5),'.');
		$commands = array("enable","config","interface EPON0/".$match[1],"no epon bind-onu mac $macolt","write","exit");
        $output = $this->telnet->executeCommands($commands);
        $this->telnet->disconnect('exit');
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
    }	
	protected function huawei_delet_onu() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
		if($this->onu['type']=='gpon'){
			preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/i',$this->onu['inface'],$match);
			$commands = array(
				"enable",
				"config",
				"display  service-port  port {$match[1]}/{$match[2]}/{$match[3]} ont {$match[4]}",""
			);
			$temp_service_port = $this->telnet->executeCommands($commands);
			preg_match_all('/\b(\d+)\s+(\d+)\s+stacking\s+gpon\b/', $temp_service_port, $matches);
			$sport = trim($matches[1][0]);
			if(empty($sport)){
				preg_match('/\b([0-9]+)\s+([0-9]+)\s+(\w+)\s+gpon/',$temp_service_port,$matches);
				$sport = trim($matches[1]);
			}
			if(isset($sport) && is_numeric($sport)){
				$this->telnet->do_comand("undo service-port {$sport}\r");				
				$command_del = array(
					"interface gpon {$match[1]}/{$match[2]}",
					"ont delet {$match[3]} {$match[4]}",
					"quit",
					"save",
					"",
					"quit"
				);
				$result_delete = $this->telnet->executeCommands($command_del);
				return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
			}			
		}
    }	
	protected function gcom_epon_delet_onu() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
        if($err_num){
            $this->telnet->err('no delete onu '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
        }
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array(
		"enable",
		"configure terminal",
		"onu 0/".$match[1]."/".$match[2],
		"no onu-binding",
		"y",
		"exit",
		"copy running-config startup-config"
		);
        $output = $this->telnet->executeCommands($commands);
        $this->telnet->disconnect('exit');
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
    }	
	protected function bdcom_gpon_delet_onu() {
        $this->telnet = new PMonTelnet($this->olt);
        $err_num = $this->telnet->err_num;
        if($err_num){
            $this->telnet->err('no gpon bind onu sequence '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
        }
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array("enable","config","interface gpon 0/".$match[1],"no gpon bind-onu sequence ".$match[2],"exit");
        $output = $this->telnet->executeCommands($commands);
        $this->telnet->disconnect('exit');
        return $this->resultdata(['type'=>'array','status'=>'ok','result'=> $this->onu]);
    }
	### HUAWEI
   	protected function huaweifdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('display mac-address port '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}	
		preg_match('/^(.+):/', $this->onu['inface'], $dataMatch);
		$commands = array("enable","display mac-address port ".$dataMatch[1]." ont ".$this->onu['keyonu']);
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    }    	
	protected function huaweirebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		preg_match('/^(\d+)\/(\d+)\/(\d+):(\d+)/', $this->onu['inface'], $dataMatch);
		$commands = array("enable","config","interface ".$this->onu['type']." ".$dataMatch[1]."/".$dataMatch[2]."","ont reset ".$dataMatch[3]." ".$dataMatch[4],"y");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    }
	### ZTE6
   	protected function zte6fdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		sleep(1);
		if($err_num){	
			$this->telnet->err('show gpon remote-onu mac '.$this->onu['type'].' onu '.$this->onu['inface'].' ethuni eth_0/1 '.$this->telnet->descr($err_num));
		}	
		$commands = array("show gpon remote-onu mac ".$this->onu['type']."_onu-".$this->onu['inface']." ethuni eth_0/1");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    } 	
	### ZTE3
   	protected function zte3fdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('show mac address-table interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}	
		$commands = array("show mac ".$this->onu['type']." onu ".$this->onu['type']."-onu_".$this->onu['inface']);
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    }    	
	protected function zte3rebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		$commands = array(
			"config t",
			"\n",
			"pon-onu-mng ".$this->onu['type']."-onu_".$this->onu['inface'],
			"reboot",
			"y");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    } 	
	// show gpon onu detail-info  gpon-onu
	protected function detailinfoonuzte3($data) {
		$this->dataonuswitch($data);
		$oidid = $this->olt['oidid'];
		return $this->onu_detail_info_zte3();		
	}	
	// show running-config interface gpon-onu
	protected function configonuzte3($data) {
		$this->dataonuswitch($data);
		$oidid = $this->olt['oidid'];
		return $this->show_configonu_zte3();		
	}
	protected function onu_detail_info_zte3() {
		$this->telnet = new PMonTelnet($this->olt);
		$err_num = $this->telnet->err_num;
		if ($err_num) {
			$this->telnet->err(
				'show gpon onu detail-info ' .
				$this->onu['type'] . ' ' .
				$this->onu['inface'] . ' ' .
				$this->telnet->descr($err_num)
			);
		}
		$iface = preg_replace('/[^0-9\/:]/', '', (string)$this->onu['inface']);
		$output = '';
		if ($this->onu['type'] === 'gpon') {
			$cmd = "show gpon onu detail-info gpon-onu_" . $iface . "\r";
		} else {
			$cmd = "show onu detail-info epon-onu_" . $iface . "\r";
		}
		$output .= $this->telnet->do_comand($cmd, true);
		while (strpos($output, '-More-') !== false) {
			$output = str_replace('-More-', '', $output);
			usleep(100000);
			$output .= $this->telnet->do_comand(" ", true);
		}
		$this->telnet->disconnect('exit');
		$output = $this->cleanCliOutput($output);
		$data = $this->format_config($output);
		return $this->resultdata(['type' => 'array','result' => $data]);
	}
	protected function cleanCliOutput($text) {
		while (strpos($text, "\x08") !== false) {
			$text = preg_replace('/.\x08/', '', $text);
		}
		$text = str_replace(['--More--', '--'], '', $text);
		$text = preg_replace("/\r\n|\r/", "\n", $text);
		return trim($text);
	}	
	protected function show_configonu_zte3() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('show running-config interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}	
		if($this->onu['type']=='gpon'){
			// show running-config interface gpon-onu_1/12/2:6
			$commands = array("conf t","\n","show running-config interface gpon-onu_".$this->onu['inface']);
		}else{
			// show running-config interface epon-onu_1/2/1:45
			$commands = array("conf t","\n","show running-config interface epon-onu_".$this->onu['inface']);
		}
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$data = $this->format_config($output);
        return $this->resultdata(['type'=>'array','result'=> $data]);		
	}
	protected function zte3rebootonu_1() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		$commands = array(
			"config t",
			"\n",
			"pon-onu-mng ".$this->onu['type']."-onu_".$this->onu['inface'],
			"reboot",
			"y");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    } 	
	protected function zte6rebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		$commands = array("conf t","pon-onu-mng ".$this->onu['type']."_onu-".$this->onu['inface'],"reboot","y");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    } 
	### ZTE2
   	protected function zte2fdb() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('show mac address-table interface '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));
		}	
		if($this->onu['type']=='epon'){
			$commands = array("show mac ".$this->onu['type']." onu ".$this->onu['type']."-onu_".$this->onu['inface']);
		}else{
			$commands = array("show mac gpon onu gpon-onu_".$this->onu['inface']);
			#$commands = array("show mac ".$this->onu['type']." onu ".$this->onu['type']."-onu_".$this->onu['inface']);
		}
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$temp = $this->get_data_fdb($output);
		$results = $this->getfdb($temp);
		return $this->resultdata(['type'=>'echo','result'=> $results]);
    }    	
	protected function zte2rebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		$commands = array("config t","\n","pon-onu","pon-onu-mng ".$this->onu['type']."-onu_".$this->onu['inface'],"reboot");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    } 	
	protected function gcomeponreboot() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		preg_match('/0\/(\d+):(\d+)/i',$this->onu['inface'],$match);
		$commands = array(
			"enable",
			"configure terminal",
			"onu 0/".$match[1]."/".$match[2],
			"onu-reboot",
			"y"
		);
		$output = $this->telnet->executeCommands($commands);
		$this->sleeps(1);
		$this->telnet->disconnect('exit');
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    } 
	### BDCOM 
   	protected function bdcomeponrebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		$commands = array("enable","epon reboot onu interface epon ".$this->onu['inface'],"y");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		$this->sleeps(1);
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    }   	
	protected function bdcomgponrebootonu() {
		$this->telnet = new PMonTelnet($this->olt);	
		$err_num = $this->telnet->err_num;
		if($err_num){	
			$this->telnet->err('reboot onu '.$this->onu['type'].' '.$this->onu['inface'].' '.$this->telnet->descr($err_num));		
		}
		$commands = array("enable","gpon reboot onu interface gpON".$this->onu['inface'],"y");
		$output = $this->telnet->executeCommands($commands);
		$this->telnet->disconnect('exit');
		return $this->resultdata(['type'=>'array','result'=> $this->onu]);
    }
	protected function test() {
		$data = "";
		return $data;
	}
	### END CMD FUNC
    private function resultdata(array $data) {
        if($data['type'] === 'echo') {
            return $data;
        }elseif($data['type'] === 'array') {
           return $data;
        }elseif($data['type'] === 'true') {
            return true;
        }
    }
	protected function LoginDataBase($mac,$dataonu) {
		$timer = date('Y-m-d H:i:s');
		$getmac = $this->db->Simple("SELECT * FROM mac_router WHERE mac = '".trim($mac)."' AND onuid = '".$dataonu['idonu']."' AND deviceid = ".$dataonu['olt']." LIMIT 1");
		if(!empty($getmac['id'])){
			$this->db->SQLupdate('mac_router',['update'=>$timer,'onu'=>'MAC_ONU '.$dataonu['mac']],['id' => $getmac['id']]);
		}else{
			$sql_mac_router = array(
				'added'=>$timer,'inface'=>$dataonu['inface'],'mac'=>trim($mac),	
				'deviceid'=>$dataonu['olt'],'onu'=>'MAC_ONU '.$dataonu['mac'],
				'onuid'=>$dataonu['idonu']				
			);
			$this->db->SQLinsert('mac_router', $sql_mac_router);
		}
	}
	protected function getfdb($macAddresses) {
		$result = '';
		if (is_array($macAddresses)) {
			foreach ($macAddresses as $ontmac => $data) {
				if(isset($this->onu) && isset($data['mac']) && isset($data['vlan']) && $data['vlan']>1){
					$this->LoginDataBase($data['mac'],$this->onu);
				}
				$result .= '<div class="ontmac mr5"><span class="v">' . $data['vlan']. '</span><span class="m">' . $data['mac']. '</span></div>';
			}
		}else{
			$result .= '<div class="ontmac">N/A</div>';
		}
		return '<div class="ont-sys">' . $result . '</div>';
	}
	protected function get_data_fdb($input){
		$array = [];
		if(isset($input)){			
			# CDATA 11 xx:xx:xx:xx:xx:xx + vlan
			preg_match_all('/\b([0-9]+)\s+([A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2})\s+(?i)dynamic\b/si', $input, $cdata11);
			$cdata11_mac = $cdata11[2] ?? [];
			$cdata11_vlan = $cdata11[1] ?? [];
			
			# CDATA 12 xx:xx:xx:xx:xx:xx + vlan
			preg_match_all('/\b([A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2})\s+([0-9]+)\s+\b/si', $input, $cdata12);
			$cdata12_mac = $cdata12[1] ?? [];
			$cdata12_vlan = $cdata12[2] ?? [];
			
			# CDATA 16 xx:xx:xx:xx:xx:xx + vlan
			preg_match_all('/\b([A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2})\s+([0-9]+)\s+([0-9-]+)\s+\b/si', $input, $cdata16);
			$cdata16_mac = $cdata16[1] ?? [];
			$cdata16_vlan = $cdata16[2] ?? [];
			
			# BDCOM EPON xxxx.xxxx.xxxx + vlan
			preg_match_all('/\b([0-9]+)\s+([A-F0-9]{4}\.[A-F0-9]{4}\.[A-F0-9]{4})\s+(?i)dynamic\b/si', $input, $bdcom);
			$bdcom_mac = $bdcom[2] ?? [];
			$bdcom_vlan = $bdcom[1] ?? [];
			
			# HUAWEI GPON xxxx-xxxx-xxxx + vlan
			preg_match_all('/\b([0-9]+)\s+-\s+gpon\s+([A-F0-9]{4}\-[A-F0-9]{4}\-[A-F0-9]{4})\s+(?i)dynamic\b/si', $input, $huawei_gpon);
			$huawei_gpon_mac = $huawei_gpon[2] ?? [];
			$huawei_gpon_vlan = $huawei_gpon[1] ?? [];
			
			# HUAWEI EPON xxxx-xxxx-xxxx + vlan
			preg_match_all('/\b([0-9]+)\s+-\s+epon\s+([A-F0-9]{4}\-[A-F0-9]{4}\-[A-F0-9]{4})\s+(?i)dynamic\b/si', $input, $huawei_epon);
			$huawei_epon_mac = $huawei_epon[2] ?? [];
			$huawei_epon_vlan = $huawei_epon[1] ?? [];
			
			# V1600D4 xxxx:xxxx:xxxx + vlan
			preg_match_all('/\b\s+([0-9]+)\s+([A-F0-9]{4}\:[A-F0-9]{4}\:[A-F0-9]{4})\s+(?i)dynamic\b/si', $input, $vsol);
			$vsol_mac = $vsol[2] ?? [];
			$vsol_vlan = $vsol[1] ?? [];
			
			# ZTE 2,3,6 GPON/EPON xxxx.xxxx.xxxx + vlan
			preg_match_all('/\b([A-F0-9]{4}\.[A-F0-9]{4}\.[A-F0-9]{4})\s+([0-9]+)\s+(?i)dynamic\b/si', $input, $zte);
			$zte_mac = $zte[1] ?? [];
			$zte_vlan = $zte[2] ?? [];
			
			# array_merge
			$array_mac = array_merge($zte_mac, $bdcom_mac, $cdata16_mac, $cdata11_mac, $cdata12_mac, $huawei_epon_mac,$huawei_gpon_mac, $vsol_mac);
			$array_vlan = array_merge($zte_vlan, $bdcom_vlan, $cdata16_vlan, $cdata11_vlan, $cdata12_vlan, $huawei_epon_vlan, $huawei_gpon_vlan, $vsol_vlan);
			if(is_array($array_mac) && is_array($array_vlan) && count($array_mac) === count($array_vlan)){
				foreach ($array_mac as $key => $out_mac) {
					$mac = trim($out_mac);
					$vlan = isset($array_vlan[$key]) ? trim($array_vlan[$key]) : '';
					$array[$mac]['mac'] = $this->formatmac($mac,1);
					$array[$mac]['vlan'] = $vlan;
				}
			}
		}
		return $array;
	}
	private function sleeps($time=1){
		sleep($time);
	}
	protected function formatmac($mac,$format){
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
	private function dataswitch($data) {
		$sqlswitch = $this->db->Fast('switch','*',['id'=>$data['olt']]); 
		if(empty($sqlswitch['id'])){
			die('not support empty');
		}
		$this->olt = [
			'id' => $sqlswitch['id'],
			'oidid' => $sqlswitch['oidid'],
			'netip' => $sqlswitch['netip'],
			'username' => $sqlswitch['username'],
			'password' =>  $sqlswitch['password']
		];
	}
	private function dataonuswitch($data) {
		$sqlonus = $this->db->Fast('onus','idonu,keyonu,name,portolt,zte_idport,olt,type,inface,mac,sn',['idonu'=>$data['idonu']]);
		$sqlswitch = $this->db->Fast('switch','*',['id'=>$sqlonus['olt']]); 
		if(empty($sqlswitch['id']) || empty($sqlonus['idonu'])){
			die('not support empty');
		}
		$this->olt = [
			'id' => $sqlswitch['id'],
			'oidid' => $sqlswitch['oidid'],
			'netip' => $sqlswitch['netip'],
			'username' => $sqlswitch['username'],
			'enablepassword' => $sqlswitch['enablepassword'] ?? null,
			'telnet_port' => $sqlswitch['telnet_port'] ?? 23,
			'password' =>  $sqlswitch['password']
		];		
		$this->onu = [
			'numport' => (!empty($data['numport'])?$data['numport']:1),
			'idonu' => $sqlonus['idonu'],
			'keyonu' => $sqlonus['keyonu'],
			'olt' => $sqlonus['olt'],
			'deviceid' => $sqlonus['olt'],
			'mac' => (!empty($sqlonus['mac'])?$sqlonus['mac']:(!empty($sqlonus['sn'])?$sqlonus['sn']:null)),
			'type' => $sqlonus['type'],
			'inface' => $sqlonus['inface']
		];		
	}
}
$pmon = new PMon($db);
?>
