<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMonScraper {
    private $device;
    private $scrape = false;
    private $db;
    public $list_device = array();
    public $folder;
    public function __construct($db, $folder) {
        $this->db = $db;
		$this->fetchDeviceList();
		$this->folder = $folder;
    }
	public function fetchDeviceList(){
        $sql_switch = "SELECT * FROM switch WHERE device = 'olt' AND monitor = 'yes'";
		$result = $this->db->query($sql_switch);
		if ($result->num_rows > 0) {
			while ($value = $result->fetch_assoc()) {
                $this->list_device[$value['id']] = [
                    'id' => $value['id'],'place' => $value['place'],'netip' => $value['netip'],'snmpro' => $value['snmpro'],'oidid' => $value['oidid'],'model' => $value['inf'] . ' ' . $value['model'],
                ];
            }
        }
    }
	public function zte_c220(){
		
	}	
	public function zte_c320(){
		$response = [];		
		$response_epon = $this->zte_epon();
		$response_gpon = $this->zte_gpon();		
		if (is_array($response_epon)) {
			$response = $response_epon;
		}		
		if (is_array($response_gpon)) {
			if (empty($response)) {
				$response = $response_gpon;
			} else {
				$response = array_merge($response, $response_gpon);
			}
		}
		if($this->scrape==true){
			return $response;
		}
		return NULL;
	}		
	public function huawei_5600(){
		$response = [];		
		$response_epon = $this->huawei_epon();
		$response_gpon = $this->huawei_gpon();		
		if (is_array($response_epon)) {
			$response = $response_epon;
		}		
		if (is_array($response_gpon)) {
			if (empty($response)) {
				$response = $response_gpon;
			} else {
				$response = array_merge($response, $response_gpon);
			}
		}
		if($this->scrape==true){
			return $response;
		}
		return NULL;
	}	
	public function zte_c620(){
		
	}	
	public  function decode_ifIndex($ifIndex) {
        $shelf_no = (( $ifIndex & hexdec("ff000000")) >> 24 ) - 15;
        $slot_no  = (( $ifIndex & hexdec("00ff0000")) >> 16 ) ;
        $port_no  = (( $ifIndex & hexdec("0000ff00")) >> 8 );
        return $shelf_no."/".$slot_no."/".$port_no;
	}
	public function zte_epon() {
		$snmpwalk = @snmp2_real_walk($this->device['netip'], $this->device['snmpro'], '1.3.6.1.4.1.3902.1015.1010.1.7.14.1.2');
		if (!$snmpwalk) {
			return array();
		}
		$int = 1;
		$result = array();
		foreach($snmpwalk as $key => $mac){
			preg_match('/1015.1010.1.7.14.1.2.(\d+).(\d+)/',$key,$m);
			if(isset($m[1]) && isset($m[2])){	
				$mac_temp = $this->huawei_mac_epon($mac);
				$inface = $this->zte3_inface_epon_onu($m[1]);
				$mac_pre = preg_replace('/(.{4})/', '\1.',$mac_temp, 2);
				$result['epon'][] = [
					'port' => $m[1], 'ont' => $m[2],'mac' => $mac_pre,'index' => $this->decode_ifIndex($m[1])
				];
				$int++;
			}
		}
		return $result;
	}	
	public function onusn($type){
		if (preg_match("/(Hex-STRING: )([0-9A-F ]{3})([0-9A-F ]{3})([0-9A-F ]{3})([0-9A-F ]{3})([0-9A-F ]{3})([0-9A-F ]{3})([0-9A-F ]{3})([0-9A-F ]{2})$/",$type)){	
			if (preg_match("/Hex/i", $type)) {
				$re_z_z = explode('Hex-STRING:', $type);
				$re_z = end($re_z_z);
				$onu = preg_replace("/\s+/","",mb_strtolower($re_z));
			}elseif(preg_match("/STRING/i", $type)) {
				$re_ze_mac = explode('STRING:', $type);
				$re_mac = end($re_ze_mac);
				$onu = bin2hex($re_mac);
			}
		}else{
			$onu = bin2hex(str_replace('"', '', str_replace('STRING: ', '', trim($type))));
		}
		return $onu;
	}
	public function huawei_gpon() {
		$data = [
			'oid' => '1.3.6.1.4.1.2011.6.128.1.1.2.48.1.2','type' => 'exec','ip' => $this->device['netip'],'community'=> $this->device['snmpro']
		];
		$snmpwalk = $this->pmon_walk_m($data);	
		if (!$snmpwalk) {
			return array();
		}
		$result = array();
		foreach($snmpwalk as $key => $temp){
			$data = $this->getdata($temp['result']);
			preg_match('/.48.1.2.(\d+).(\d+)/',$data['oid'],$m);
			if(isset($m[1]) && isset($m[2])){
				$port = $this->huawei_decode_ifIndex($m[1]);
				if (isset($port['type']) && $port['type']=='gpon') {
					$result['gpon'][] = array(
						'idport' => $m[1],'port' => $port['type'],'ont' => $m[2],'sn' => $this->huawei_sn_onu($data['result']),
						'index' => $port['shelf'] . '/' . $port['slot'] . '/' . $port['port']
					);
				}
			}
		}
		return $result;
	}
	public function getdata($temp) {
		$parts = explode('=', $temp);
		if(isset($parts[0]) && isset($parts[1])){
			return array('oid'=>trim($parts[0]),'result'=>trim($parts[1]));
		}else{
			return false;
		}
	}
	public function zte_gpon() {
		$data = [
			'oid' => '1.3.6.1.4.1.3902.1012.3.13.3.1.2',
			'type' => 'exec','ip' => $this->device['netip'],'community'=> $this->device['snmpro']
		];
		$snmpwalk = $this->pmon_walk_m($data);		
		if (!$snmpwalk) {
			return array();
		}
		$int = 1;
		$result = array();
		foreach($snmpwalk as $key => $temp){
			$data = $this->getdata($temp['result']);
			preg_match('/1012.3.13.3.1.2.(\d+).(\d+)/',$data['oid'],$m);
			if(isset($m[1]) && isset($m[2])){	
				$result['gpon'][] = [
					'port' => $m[1],'ont' => $m[2],'sn' => $this->zte3_sn_onu($data['result']),
					'index' => $this->zte3_inface_gpon_onu($m[1],$m[2])
				];
				$int++;
			}
		}
		return $result;
	}
	public function huawei_epon() {
		$snmpwalk = @snmp2_real_walk($this->device['netip'], $this->device['snmpro'], '1.3.6.1.4.1.2011.6.128.1.1.2.58.1.2');
		if (!$snmpwalk) {
			return array();
		}
		$result = array();
		foreach ($snmpwalk as $key => $value) {
			if (preg_match('/128.1.1.2.58.1.2.(\d+).(\d+)/i', $key, $dataMatch)) {
				$port = $this->huawei_decode_ifIndex($dataMatch[1]);
				if (!empty($dataMatch[2]) && !empty($port['shelf']) && !empty($port['slot']) && !empty($port['port'])) {
					$result['epon'][] = array(
						'port' => $port,'ont' => $dataMatch[2],'mac' => $this->huawei_epon_mac($value),
						'index' => $port['shelf'] . '/' . $port['slot'] . '/' . $port['port']
					);
				}
			}
		}
		return $result;
	}
	public function get_scrape($switch) {
		$this->device = $switch;
		$this->scrape = true;
		$oidid = $switch['oidid'] ?? null;
		if ($oidid !== null) {
			switch ($oidid) {
				case 34:
				case 7:
					return $this->zte_c320();
				case 33:
				case 14:
					return $this->huawei_5600();
				default:
					return null;
			}
		}
		return null;
	}	
	public function get_onu($switch) {
		$this->device = $switch;
		$result = '';
		switch ($switch['oidid']) {
			case 3:
				$result = $this->zte_c220();
				break;
			case 6:
				$result = $this->zte_c620();
				break;
			case 34:
			case 7:
				$result = $this->zte_c320();
				break;
			case 33:
			case 14:
				$result = $this->huawei_5600();
				break;
			default:
				$result = 'not support reger device ' . $switch['place'];
				break;
		}
		return isset($result) ? $result : '';
	}
	public function pmon_walk_m($data) {
		$result = [];
		$raw = '';
		if(empty($data['community']) || empty($data['ip']) || empty($data['oid'])){
			die('check get snmpwalk parametr');
		}
		if ($data['oid']) {
			switch ($data['type']) {
				case "exec":
					$command = "snmpwalk -v2c -c " . $data['community'] . " " . $data['ip'] . " " . $data['oid'];
					$raw = shell_exec($command);
					$rawlines = explode(PHP_EOL, trim($raw));
					$raw = array_filter($rawlines, 'strlen');
					break;
				case "class":
					$session = new SNMP(SNMP::VERSION_2C, $data['ip'], $data['community']);
					$session->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
					$raw = @$session->walk($data['oid']);
					if (isset($session))
						unset($session);
					break;
				case "real":
					snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
					$raw = @snmp2_real_walk($data['ip'], $data['community'], $data['oid']);
					break;
			}	
			if (is_array($raw) && count($raw) > 0) {
				foreach ($raw as $oid => $value) {
					$value = str_replace('iso','1', $value);
					$arraytemp = str_replace('.'.$data['oid'].'.','', $oid);
					$arraytemp = str_replace($data['oid'].'.','', $arraytemp);
					if(!empty($data['deloid']) && $data['deloid']==true){
						$value = str_replace('.'.$data['oid'].'.','', $value);
						$value = str_replace($data['oid'].'.','', $value);					
					}
					$result[trim($arraytemp)]['result'] = $value;
				}
			}
		}
		return $result;
	}
	public function zte3_inface_epon_onu($index) {
		$ifIndex = str_pad(decbin($index), 32, '0', STR_PAD_LEFT);
		$shelf_no  = bindec(substr($ifIndex, 4, 4))+1;
		$slot_no = bindec(substr($ifIndex, 8, 5));
		$port_no = bindec(substr($ifIndex, 13, 3))+1;
		$ont_no = bindec(substr($ifIndex, 16, 8));    
		return $shelf_no.'/'.$slot_no.'/'.$port_no.':'.$ont_no;
	}
	public function zte3_inface_gpon_onu($llid,$on) {
		$lx=sprintf("%08x",$llid);
		switch ($lx[0]) {
		case '1':
			$sh=hexdec($lx[1])+1;
			$sl=hexdec($lx[2].$lx[3]);
			$ol=hexdec($lx[4].$lx[5]);
		break;
		case '2':
			$sh=hexdec($lx[3]);
			$sl=hexdec($lx[4].$lx[5]);
			$ol=hexdec($lx[6].$lx[7]);
			if ($cl>16) {
				$cl-=16; $sl++;
			}
			$ol1=$ol;
			break;
		case '3':
			$sh=hexdec($lx[1])+1;
			$sl=hexdec($lx[2].$lx[3]);
			$ol=($sl&0x07)+1;
			$sl=$sl>>3;
			$on = hexdec($lx[4].$lx[5]);
		break;
		case '6':
			$sh=hexdec($lx[1])+1;
			$sl=hexdec($lx[2].$lx[3]);
			$ol=0;
		break;
		}
		return "{$sh}/{$sl}/{$ol}:{$on}";
	}
	public function huawei_sn_onu($tempsn) {
		if (strpos($tempsn,'Hex-STRING') !== false){
			$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
			$tempsn = preg_replace('/Hex-STRING/','',$tempsn);
			$tempsn = str_replace(':', '',$tempsn);
			$tempsn = str_replace('"', '',$tempsn);
			$tempsn = str_replace(' ', '',$tempsn);
			return trim($tempsn);
		}else{
			$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
			$onu_snc1 = preg_replace ('/STRING:/','',$tempsn);
			$tmpv = explode(" ","$onu_snc1");
			$tmpe = str_split($tmpv[1]);
			return $tmpe[1].$tmpe[2].$tmpe[3].$tmpe[4].strtoupper(dechex(ord($tmpe[5]))).strtoupper(dechex(ord($tmpe[6]))).strtoupper(dechex(ord($tmpe[7]))).strtoupper(dechex(ord($tmpe[8])));
		}
	}	
	public function zte3_sn_onu($tempsn) {
		if (strpos($tempsn,'Hex-STRING') !== false){
			$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
			$tempsn = preg_replace('/Hex-STRING/','',$tempsn);
			$tmpv = explode(" ",$tempsn);
			$val1 = hexdec($tmpv[1]);
			$val2 = hexdec($tmpv[2]);
			$val3 = hexdec($tmpv[3]);
			$val4 = hexdec($tmpv[4]);
			$val5 = $tmpv[5];
			$val6 = $tmpv[6];
			$val7 = $tmpv[7];
			$val8 = $tmpv[8];
			return chr($val1).chr($val2).chr($val3).chr($val4).$val5.$val6.$val7.$val8;
		}else{
			$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
			$onu_snc1 = preg_replace ('/STRING:/','',$tempsn);
			$tmpv = explode(" ","$onu_snc1");
			$tmpe = str_split($tmpv[1]);
			return $tmpe[1].$tmpe[2].$tmpe[3].$tmpe[4].strtoupper(dechex(ord($tmpe[5]))).strtoupper(dechex(ord($tmpe[6]))).strtoupper(dechex(ord($tmpe[7]))).strtoupper(dechex(ord($tmpe[8])));
		}
	}
	public function huawei_mac_epon($value){
		$value = str_replace('Hex-STRING:', '',$value);
		$value = str_replace('STRING:', '',$value);
		$value = str_replace('"', '',$value);
		$value = str_replace(' ', '',$value);
		return trim($value);
	}
	public function number() {
		$number = random_int(1, 1000);
		$number = str_pad($number, 4, '0', STR_PAD_LEFT);	
		return $number;
	}
	public function huawei_decode_ifIndex($ifIndex) {
		$board_type = ( $ifIndex & bindec('11111110000000000000000000000000') ) >> 25 ;
		switch($board_type) {
			case 126:
			$port_type="epon";
			$shelf_no       = ( $ifIndex & bindec('00000001111110000000000000000000') ) >> 19 ;
			$slot_no        = ( $ifIndex & bindec('00000000000001111110000000000000') ) >> 13 ;
			$port_no        = ( $ifIndex & bindec('00000000000000000001111100000000') ) >> 8  ;
			return(array("type"=>$port_type,"shelf"=>$shelf_no,"slot"=>$slot_no,"port"=>$port_no));
			break;
			case 125:
			$port_type = "gpon";
			$shelf_no       = ( $ifIndex & bindec('00000001111110000000000000000000') ) >> 19 ;
			$slot_no        = ( $ifIndex & bindec('00000000000001111110000000000000') ) >> 13 ;
			$port_no        = ( $ifIndex & bindec('00000000000000000001111100000000') ) >> 8  ;
			return(array("type"=>$port_type,"shelf"=>$shelf_no,"slot"=>$slot_no,"port"=>$port_no));
			break;
		}
	}
}

?>
