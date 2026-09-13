<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMonReger {
    private $device;
    private $db;
    private $list_device = array();
    public function __construct($db) {
        $this->db = $db;
		$this->fetchDeviceList();
    }
	public function fetchDeviceList(){
        $res = $this->db->Multi('switch','*',['device'=>'olt']);
        if(isset($res) && count($res)>0){
            foreach($res as $value){
                $this->list_device[$value['id']] = [
                    'id' => $value['id'],
                    'place' => $value['place'],
                    'oidid' => $value['oidid'],
                    'model' => $value['inf'] . ' ' . $value['model'],
                ];
            }
        }
    }
	public function free_number($index, $device, $type, $result = null){
		$free_space = [];
		preg_match('/(\d+)\/(\d+)\/(\d+)/', $index, $inface);
		$get = array('sw_shelf' => $inface[1], 'sw_slot' => $inface[2], 'sw_port' => $inface[3], 'olt' => $device);
		$get_list = $this->db->Multi('idonu, onus', 'keyonu', $get);
		if(isset($get_list)){
			$taken_numbers = array_column($get_list, 'keyonu');
			if($type == 'epon'){
				$max_numbers = 64;
			} else {
				$max_numbers = 128;
			}
			for ($i = 1; $i <= $max_numbers; $i++) {
				if (!in_array($i, $taken_numbers)) {
					$free_space[] = $i;
				}
			}
		}
		if(isset($result) && $result == 'option'){
			$html_options = '<select name="free" id="free" class="inputonuselect">';
			foreach ($free_space as $value) {
				$html_options .= '<option value="' . $value . '">' . $value . '</option>';
			}
			$html_options .= '</select>';
			return $html_options;
		}
		return $free_space;
	}
	public function zte_c220(){
		
	}	
	public function zte_c320_all_vlan(){
		
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

		if (!empty($response)) {
			return $this->get_result($response);
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
		if (!empty($response)) {
			return $this->get_result($response);
		}
		return NULL;
	}	
	public function zte_c620(){
		$response = [];		
		$response_gpon = $this->zte_6_gpon();		
		if (is_array($response_gpon)) {
			$response = $response_gpon;
		}
		if (!empty($response)) {
			return $this->get_result($response);
		}
		return NULL;
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
		$snmpwalk = @snmp2_real_walk($this->device['netip'], $this->device['snmpro'], '1.3.6.1.4.1.2011.6.128.1.1.2.48.1.2');
		$data = [
			'oid' => '1.3.6.1.4.1.2011.6.128.1.1.2.48.1.2',
			'type' => 'exec','ip' => $this->device['netip'],'community'=> $this->device['snmpro']
		];
		$snmpwalk = pmon_walk_m($data);	
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
		$snmpwalk = pmon_walk_m($data);		
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
	public function zte_6_gpon() {
		$data = [
			'oid' => '1.3.6.1.4.1.3902.1082.500.2.2.11.2.1.2',
			'type' => 'exec','ip' => $this->device['netip'],'community'=> $this->device['snmpro']
		];
		$snmpwalk = pmon_walk_m($data);
		if (!$snmpwalk) {
			return array();
		}
		$int = 1;
		$result = array();
		foreach($snmpwalk as $key => $temp){
			$data = $this->getdata($temp['result']);
			preg_match('/11.2.1.2.(\d+).(\d+)/',$data['oid'],$m);
			if(isset($m[1]) && isset($m[2])){	
				$result['gpon'][] = [
					'port' => $m[1],'ont' => $m[2],'sn' => $this->zte3_sn_onu($data['result']),
					'index' => $this->zte6_inface_gpon_onu($m[1],$m[2])
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
				#$result = 'not support reger device ' . $switch['place'];
				break;
		}
		return isset($result) ? $result : '';
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
		$lx = sprintf("%08x",$llid);
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
	public function zte6_inface_gpon_onu($llid,$on) {
		$lx = sprintf("%08x",$llid);
		switch ($lx[0]) {
		case '1':
			$sh = hexdec($lx[1]);
			$sl = hexdec($lx[4].$lx[5]);
			$ol = hexdec($lx[6].$lx[7]);
		break;
		case '2':
			$sh = hexdec($lx[3]);
			$sl = hexdec($lx[4].$lx[5]);
			$ol = hexdec($lx[6].$lx[7]);
			if ($cl>16) {
				$cl -= 16; $sl++;
			}
			$ol1 = $ol;
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
	public function zte6_sn_onu($tempsn) {
		$return = '';
		$tempsn = preg_replace('~^.*?( : )~i','',$tempsn);
		$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
		$sn = str_replace(['Hex-STRING', 'STRING', 'Hex-', ': ', '\x'], '', $tempsn);
		if (strlen($sn) === 24){
			$sn = explode(" ", $sn);
			foreach ($sn as $key => $value){
				if ($key < 4) {
					$return.=chr(hexdec($value)); 
				}else{
					$return.=$value;
				}
			}
		}else{
			$nosn = substr($sn,4);
			$resn = substr($sn, 0, 4);
			$return = $resn.strtoupper(bin2hex($nosn));
		}
		if (strlen($return) === 15){
			$return = substr($return, 0, -2);
			$return = str_replace('HWT43', 'HWTC', $return);
		}
        return $return;
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
	public function get_result($response){
		if(!empty($response['gpon']) || !empty($response['epon'])){
			$resp ='<table class="resp-tab none"><thead><tr><th width="15%">Olt</th><th width="5%">Pon</th><th width="10%">Interface</th><th width="30%">Sn/Mac ONU</th><th>Function</th></tr></thead><tbody>';
			if(isset($response['gpon']) && count($response['gpon'])>0){
				foreach ($response['gpon'] as $key => $value) {
				$idblock = $this->number();
				$idreger = (isset($value['idport'])?$value['idport']:1).$value['ont'];
				$resp .='<tr id="reger_'.$idblock.'"><td class="td_name" style="line-height: 14px;">
				'.$this->device['place'].'<br>'.$this->list_device[$this->device['id']]['model'].'
				</td><td style="background: #75cd75;"><font color="#222">GPON</font></td><td><font color="#1f7bc3">'.$value['index'].'</font></td>
				<td><font color="#0f73c3">'.$value['sn'].'</font></td><td>';
				$resp .='<div id="list_key">';
				if(isset($this->list_device[$this->device['id']]['oidid']) && ($this->list_device[$this->device['id']]['oidid']==34 || $this->list_device[$this->device['id']]['oidid']==6 || $this->list_device[$this->device['id']]['oidid']==7)){	
				$resp .='<div class="name_descr_block">Vlan</div>';				
				$resp .='<div id="vlan_select">';				
				$resp .='<select class="select" name="vlan_select" id="vlan_select_'.$idblock.'">
				<option value="1">List Olt</option>
				<option value="2">Manual</option>
				</select>';
				$resp .='</div>';
				}
				$resp .='<div id="reger_key">';
				$resp .="<span onclick=\"regonu('".$this->device['id']."','".$idreger."','gpon','".$value['ont']."','".$value['index']."','".$value['sn']."','".$idblock."')\" class=\"reger_btn\">Register ONU</span>";
				$resp .='</div>';
				$resp .='</div>';
				$resp .='</td></tr>';
				}
			}			
			if(isset($response['epon']) && count($response['epon'])>0){
				foreach ($response['epon'] as $key => $value) {
				$idblock = $this->number();
				$idreger = (isset($value['idport'])?$value['idport']:1).$value['ont'];
				$resp .='<tr id="reger_'.$idblock.'"><td class="td_name" style="line-height: 14px;">'.$this->device['place'].'<br>'.$this->list_device[$this->device['id']]['model'].'</td><td style="background:#f4c426;"><font color="#222">EPON</font></td><td><font color="#1f7bc3">'.$value['index'].'</font></td>
				<td><font color="#0f73c3">'.$value['mac'].'</font></td><td>';
				$resp .='<div id="list_key">';
				if(isset($this->list_device[$this->device['id']]['oidid']) && ($this->list_device[$this->device['id']]['oidid']==34 || $this->list_device[$this->device['id']]['oidid']==7)){				
				$resp .='<div class="name_descr_block">Vlan</div>';				
				$resp .='<div id="vlan_select">';				
				$resp .='<select class="select" name="vlan_select" id="vlan_select_'.$idblock.'">
				<option value="1">List Olt</option>
				<option value="2">Manual</option>
				</select>';
				$resp .='</div>';
				}
				$resp .='<div id="reger_key">';
				$resp .="<span onclick=\"regonu('".$this->device['id']."','".$idreger."','epon','".$value['ont']."','".$value['index']."','".$value['mac']."','".$idblock."')\" class=\"reger_btn\">Register ONU</span>";
				$resp .='</div>';
				$resp .='</div>';
				$resp .='</td></tr>';
				}
			}
			$resp .='</tbody></table>';
			$resp .='';
			return $resp;
		}
	}
}
$Reger = new PMonReger($db);
?>
