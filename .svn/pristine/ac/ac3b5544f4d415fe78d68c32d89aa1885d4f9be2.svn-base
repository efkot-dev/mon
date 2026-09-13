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
$tplresult = '';
$getswitch = $db->Fast('switch','*',['id'=>$id]);
if(isset($getswitch['id']) && !empty($getswitch['id'])){
$list_sfp = [
	'oid' => '1.3.6.1.4.1.9.9.91.1.1.1.1.1','type' => 'class','deloid' => true,'timecache' => 700,'namecache' => 'list_port_sfp_'.$id,'ip' => $getswitch['netip'],'community'=> $getswitch['snmpro']
];
$resTypeSlot = pmon_walk($list_sfp);
$rSlot = [];
if ($resTypeSlot !== false) {
$PmonSnmp = new PmonSnmp($db, $lang, $config, $confPMon, $cacheManager);
$temp = [];
foreach ($resTypeSlot as $key => $type) {
    if (isset($type['result']) && preg_match("/14/i",$type['result'])){	
        $data_rx_oid = '1.3.6.1.4.1.9.9.91.1.1.1.1.4.' . $key;
        $data_name_oid = '1.3.6.1.2.1.47.1.1.1.1.7.' . $key;
        $data_rx = ['netip' => $getswitch['netip'], 'snmpro' => $getswitch['snmpro'], 'oid' => $data_rx_oid];
        $snmpvalue = $PmonSnmp->get($data_rx);
        $data_name = ['netip' => $getswitch['netip'], 'snmpro' => $getswitch['snmpro'], 'oid' => $data_name_oid];
        $snmpname = $PmonSnmp->get($data_name);
        if (preg_match('/Ethernet(\d+)\/(\d+)L/', $snmpname, $portMatch)) {
            $port = 'Ethernet ' . $portMatch[1] . '/' . $portMatch[2];
            $types_signal = preg_match('/ReceivePower/i', $snmpname) ? 'rx' : 'tx';
            $tempKey = md5($port);
            if (!isset($temp[$tempKey])) {
                $temp[$tempKey] = ['tx' => null, 'name' => $port, 'rx' => null];
            }
            if (abs($snmpvalue) >= 100) { 
				$temp[$tempKey][$types_signal] = sprintf('%.2f', $snmpvalue / 1000);
			} else {
				$temp[$tempKey][$types_signal] = sprintf('%.2f', $snmpvalue / 10);
			}
        }
    }
}

if(isset($temp) && count($temp)>0){
	$tplresult .= '<table class="resp-tab"><thead><tr>
		<th width="25%">Inface</th>
		<th width="10%">Rx Power</th>
		<th width="10%">Tx Power</th>
		</tr></thead><tbody>';
	foreach ($temp as $keymd5 => $port) {
		$olt_rx_color = $port['rx'] < -17 ? "red" : "#36b105";
		$olt_tx_color = ($port['tx'] < -6 || $port['tx'] > 3.6) ? "red" : "#36b105";
		$tplresult .= '<tr>
		<td class="td_url">'.$port['name'].'</td>
		<td class="td_url" style="color:'.$olt_rx_color.';">'.$port['rx'].' dBm</td>
		<td class="td_url" style="color:'.$olt_tx_color.';">'.$port['tx'].' dBm</td>
		</tr>';
	}
	$tplresult .= '</table>';
}
}
}
echo $tplresult; 
die;
?>