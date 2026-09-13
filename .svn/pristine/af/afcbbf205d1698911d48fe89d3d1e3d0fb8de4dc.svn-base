<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
define('SNMP',true);
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
if(!$id){
	die('');	
}
$getswitch = $db->Fast('switch','*',['id'=>$id]);
if(isset($getswitch['id']) && !empty($getswitch['id'])){
$olt_port = [
	'oid' => '1.3.6.1.4.1.14988.1.1.19.1.1','cache' => true,'timecache' => 360,'namecache' => 'list_port_temp_'.$getswitch['id'],
	'type' => 'class','deloid' => true,'ip' => $getswitch['netip'],'community'=> $getswitch['snmpro']
];
$tmp_data = array();
$index_port = pmon_walk($olt_port);
if (isset($index_port) && is_array($index_port)) {
	foreach ($index_port as $tmp => $value) {
		preg_match('/(\d+).(\d+)/i',$tmp,$oid_res);
		$tmp_value = preg_replace('/(INTEGER|Hex-STRING|STRING|Gauge32):|\s|=|"/', '', $value['result']);
		$tmp_data[$oid_res[1]][$oid_res[2]] = trim($tmp_value);
	}
}
}
$tplresult = '';
	$tplresult .= '<table class="resp-tab" width="100%"><thead><tr>
		<th class="pad020">Interface</th>
		<th class="pad020">Tx Power</th>
		<th class="pad020">Rx Power</th>
		<th class="pad020">Voltage</th>
		<th class="pad020">Wavelength</th>
		<th class="pad020">Temperature</th>';
		if($access->get('connectport')){
			$tplresult .= '<th class="pad020">Monitor</th>';
		}
		$tplresult .= '<th class="pad020">History</th>';
		$tplresult .= '</tr></thead><tbody>';
	if (isset($tmp_data[2]) && is_array($tmp_data[2])) {
		foreach ($tmp_data[2] as $oid_id => $value_oid) {
			if($tmp_data[6][$oid_id]>0){			
				$rx_signal = $tmp_data[10][$oid_id];
				$rx_signal = intval($rx_signal) / 1000;
				$tx_signal = $tmp_data[9][$oid_id];
				$tx_signal = intval($tx_signal) / 1000;
				$rx_color = $rx_signal < -17 ? "red" : "#36b105";
				$tx_color = ($tx_signal < -6 || $tx_signal > 3.6) ? "red" : "#36b105";
				$type_signal = ($tmp_data[5][$oid_id] / 100);
				$type_voltage = ($tmp_data[7][$oid_id] / 1000);
				$type_temp = ($tmp_data[6][$oid_id]);
				$tplresult .= '<tr>
				<td class="td_url">'.$value_oid.'</td>
				<td class="td_url" style="color:'.$rx_color.';">
					'.($type_signal > 0 ? sprintf('%.2f',$tx_signal).' dBm':' ').'
				</td>
				<td class="td_url" style="color:'.$tx_color.';">
					'.($type_signal > 0 ? sprintf('%.2f',$rx_signal).' dBm':' ').'
				</td>
				<td class="td_url">
					'.($type_signal > 0 ? sprintf('%.2f',$type_voltage).' v':' ').'
				</td>
				<td class="td_url">'.$type_signal.'</td>
				<td class="td_url">
					'.($type_signal > 0 ? $type_temp.' °C':' ').'
				</td>	';	
				if($access->get('connectport')){
					$port = $db->Simple("SELECT * FROM switch_port WHERE deviceid = '{$id}' AND llid = '{$oid_id}' LIMIT 1");
					if(isset($port['signal']) && $port['signal']=='yes'){
						$btn_connect = '<span class="btn_disable" onclick="monitorport('.$id.','.$oid_id.',\'2\')">'.$lang['disable'].'</span>';							
					}else{
						$btn_connect = '<span class="btn_enable" onclick="monitorport('.$id.','.$oid_id.',\'1\')">'.$lang['enable'].'</span>';
					}
					$tplresult .= '<td class="td_url">'.$btn_connect.'</td>';
					$signal = $db->Simple("SELECT * FROM signal_sfp WHERE sfpid = '{$port['id']}' LIMIT 1");
				}
				$tplresult .= '<td class="td_url">'.(!empty($signal['id']) ? '<img class="signalsfp" onclick="viewsignalsfp(\''.$port['id'].'\')" src="../style/img/bar-graph.png">' : '').'</td>';
				$tplresult .= '</tr>';
			}
		}
	}
	$tplresult .= '</table>';

echo $tplresult; 
die;
?>