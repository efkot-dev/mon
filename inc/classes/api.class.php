<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class MonitorApi{
	public $timer;
    private $filter = true;
    private $lang;
    private $snmp;
    private $timeout = 1000000;
    private $retries = 5;
    private $db;
    private $config;
    private $confPMon;
    private $cache;

    public function __construct($db, $lang, $config, $confPMon, $cacheManager){
		$this->lang = $lang;
		$this->db = $db;
		$this->config = $config;
		$this->cache = $cacheManager;
		$this->confPMon = $confPMon;
		$this->snmp = new SnmpMonitor();
		$this->timer = microtime(true);
    }
    public function apiport($array){
		$data = [];		
		$oidInErrorswitch = '1.3.6.1.2.1.2.2.1.14.'.$array['keyport'];
		#$getIn = $this->snmp->get($array['netip'],$array['snmpro'],$oidInErrorswitch);
		$getIn = @snmp2_get($array['netip'],$array['snmpro'],$oidInErrorswitch,$this->timeout,$this->retries);
		$data['in'] = @$this->trimSNMPOutput($getIn,$oidInErrorswitch);		
		$oidOutErrorswitch = '1.3.6.1.2.1.2.2.1.20.'.$array['keyport'];
		#$getOut = $this->snmp->get($array['netip'],$array['snmpro'],$oidOutErrorswitch);
		$getOut = @snmp2_get($array['netip'],$array['snmpro'],$oidOutErrorswitch,$this->timeout,$this->retries);
		$data['out'] = @$this->trimSNMPOutput($getOut,$oidOutErrorswitch);		
		return $data;
	}
	public function monitorDevice($array){
		$ListOId = $this->db->Multi('oid','types,oid,result',['pon'=>'device','oidid'=>$array['oidid'],'inf' => 'health']);
		$confapi = array();
		if(is_array($ListOId)){
			foreach($ListOId as $conf) {
				$data = '';
				if(!empty($conf['oid'])){
					#$getIn = @$this->snmp->get($array['netip'],$array['snmpro'],$conf['oid']);
					$getIn =  @snmp2_get($array['netip'],$array['snmpro'],$conf['oid'],$this->timeout,$this->retries);
					$res = $this->trimSNMPOutput($getIn,$conf['oid']);	
					if($res){
						$data = $this->getResultFromat($res,$conf['result']);
					}
					$confapi['result'][$conf['types']] = ($data ? $data : 0);					
				}
			}
		}
		return $confapi;
	}
	public function format($array){
		$confapi = array();
		$result = array();
		if (isset($this->confPMon['CACHE']) && !empty($this->confPMon['CACHE']) && $this->confPMon['CACHE'] == 1) {
			$expiration = 14000;
            $cacheKey = "oid_".$array['pon']."_".$array['oidid']."_".$array['global'];
            $cachedSwitch = $this->cache->get($cacheKey);
			if ($cachedSwitch !== null) {
                $confapi = $cachedSwitch;
            } else {
				$sqloid = $this->db->Multi('oid','types,oid,result',['pon'=>$array['pon'],'oidid'=>$array['oidid'],'inf' => $array['global']]);
                foreach($sqloid as $conf){
					$confapi['oid'][$conf['types']] = $conf['oid'];
					if(!empty($conf['result']))
						$confapi['result'][$conf['types']] = $conf['result'];
				}
                $this->cache->set($cacheKey, $confapi, $expiration);
            }
		}else{
			$sqloid = $this->db->Multi('oid','types,oid,result',['pon'=>$array['pon'],'oidid'=>$array['oidid'],'inf' => $array['global']]);
			if(is_array($sqloid)){
				foreach($sqloid as $conf){
					$confapi['oid'][$conf['types']] = $conf['oid'];
					if(!empty($conf['result']))
						$confapi['result'][$conf['types']] = $conf['result'];
				}
			}
		}
		$array_separated = explode(',',$array['types']);
		foreach($array_separated as $type) {
			if(!empty($confapi['oid'][$type])){
				$result[$type]['oid'] = $this->zamina($confapi['oid'][$type],($array['keyonu']??false),($array['keyport']??false));
				$result[$type]['netip'] = $array['netip'];
				$result[$type]['id'] = $array['id'];
				$result[$type]['snmpro'] = $array['snmpro'];
				if(!empty($array['keyonu']))
					$result[$type]['keyonu'] = $array['keyonu'];
				$result[$type]['type'] = $type;
				if(!empty($array['keyport']))
					$result[$type]['keyport'] = $array['keyport'];			
				if(!empty($confapi['result'][$type]))
					$result[$type]['format'] = $confapi['result'][$type];
			}
		}
		return $result;
	}
	public function zamina(string $oid, ?string $keyonu = null, ?string $keyport = null): string {
		if ($keyonu !== null || $keyport !== null) {
			$result = str_replace(['keyonu', 'keyport'], [$keyonu, $keyport], $oid);
			if (str_contains($oid, 's') && $keyonu !== null) {
				$result = str_replace('s', $keyonu, $result);
			}
		} else {
			$result = trim($oid);
		}
		return trim($result);
	}
	public function getResultFromat($data,$format){
		$result = $data;
		if($format){
			if(preg_match('/a:2:/',$format)){
				$res = unserialize($format);
				$result = (isset($res[$data]) && !empty($res[$data])?$res[$data]:'');
			}
			if(preg_match('/FUNC/i',$format)) {
				preg_match('/=(.*)INT(\d+)=/i',$format,$dataMatch);
				if(preg_match('/FUNCT1/',$dataMatch[1])){
					$result = $data / $dataMatch[2];
				}
				if(preg_match('/FUNCT2/',$dataMatch[1])){
					$result = $data * $dataMatch[2];
				}
			}	
		}
		return $result;
	}
	public function snmp_get_re($value) {
		return @snmp2_get($value['netip'], $value['snmpro'], $value['oid'], $this->timeout, $this->retries);
	}

	public function apiget($array) {
		$result = [];

		foreach ($array as $type => $value) {
			if (isset($type) && !empty($value['oid'])) {
				$retriesLeft = 2;
				$get = $this->snmp_get_re($value);
				while (empty($get) && $retriesLeft > 0) {
					sleep(1);
					$get = $this->snmp_get_re($value);
					$retriesLeft--;
				}
				$data = $this->trimSNMPOutput($get, $value['oid']);
				if (isset($value['format']) && !empty($value['format']) && $data !== null) {
					$result[$type] = $this->getResultFromat($data, $value['format']);    
				} else {
					$result[$type] = $data;    
				}                        
			}
		}

		return $result;
	}

	
	public function trimSNMPOutput($snmpData, $oid) {
		$rep = array('INTEGER:', 'Hex-STRING:', 'STRING:', 'Gauge32:', 'Gauge64:', 'Counter32:', 'Counter64:', 'Timeticks:', $oid, '=', '"', ' ');
		$value = str_replace($rep, '', $snmpData);
		return trim($value);
	}		
	public function trimSNMPOut($snmpData) {
		$rep = array('INTEGER:', 'Hex-STRING:', 'STRING:', 'Gauge32:', 'Gauge64:', 'Counter32:', 'Counter64:', 'Timeticks:', '=', '"', ' ');
		$value = str_replace($rep, '', $snmpData);
		return trim($value);
	}	
	public function apigetAll($value){
		$snmp = @new SNMP(SNMP::VERSION_2C,$value['netip'],$value['snmpro']);
		$data = @$snmp->get($value['oid'], true);
		if($data){
			if(!empty($value['format'])){
				$result['result'] = $this->getResultFromat($data,$value['format']);	
			}else{
				if(!empty($value['oidid'])){
					$result['result'] = $this->trimSNMPOut($data);
					#$result['result'] = ($dtats==3?1:2);
				}else{
					$result['result'] = $data;
				}				
			}
		}else{
			$result['result'] = 0;
		}
		return $result;
	}	
	public function snmp_walk($netip,$snmpro,$type,$oid){
		snmp_set_quick_print(1);
		if($type=='snmp'){
			$result = @snmp2_real_walk($netip,$snmpro,$oid);
		}elseif($type=='class'){
			$snmp = @new SNMP(SNMP::VERSION_2C,$netip,$snmpro);
			$result = @$snmp->walk($oid, true);
		}
		if(!$result)
			$result = false;
		return $result;
	}
	private function prepareData($data){
        if( !is_array($data) ) {
            $data = array($data);
        }
        return array_map(function($value){
            return preg_replace('/[^\.A-Z0-9_ !@#$%^&()+={}[\]\',~`\-\'":;\\/*|><?]|\.+$/i', '', $value);
        }, $data);
    }
}
?>
