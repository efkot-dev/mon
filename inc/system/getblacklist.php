<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if ((isset($confPMon['ENABLE_BLACKLIST_CDATA11']) && !empty($confPMon['ENABLE_BLACKLIST_CDATA11']) && $confPMon['ENABLE_BLACKLIST_CDATA11'] == 1) || (isset($confPMon['ENABLE_BLACKLIST_CDATA12']) && !empty($confPMon['ENABLE_BLACKLIST_CDATA12']) && $confPMon['ENABLE_BLACKLIST_CDATA12'] == 1)) {
	$result = '';
	$sourceDirectory = ROOT_DIR.'/export/cache/';
	$masiv = array();
	$files11 = glob($sourceDirectory . '*.blacklist11');
	if ($files11 !== false) {
		foreach ($files11 as $onuFile) {
			$data = file_get_contents($onuFile);        
			$onuData = unserialize($data);        
			if ($onuData !== false && is_array($onuData)) {
				foreach ($onuData as $onuId => $onuDetails) {
					$olt = $onuDetails['olt'];
					$mac = $onuDetails['mac'];
					$port = $onuDetails['port'];                
					$masiv[$olt][$port]['mac'][md5($mac.$port)] = $mac;
				}
			}
		}
	} 
	$masivs = array();	
	$files12 = glob($sourceDirectory . '*.blacklist12');
	if ($files12 !== false) {
		foreach ($files12 as $onuFile12) {
			$data12 = file_get_contents($onuFile12);        
			$onuData12 = unserialize($data12);		
			if ($onuData12 !== false && is_array($onuData12)) {
				foreach ($onuData12 as $onuId => $onuDetails12) {
					$olt12 = $onuDetails12['olt'];
					$mac12 = $onuDetails12['mac'];
					$port12 = $onuDetails12['port'];                
					$masivs[$olt12][$port12]['mac'][md5($mac12.$port12)] = $mac12;
				}
			}
		}
	} 
	$sqlswitch = $db->SimpleWhile("SELECT * FROM switch");
	$arrayswitch = array();
	if (!empty($sqlswitch) && count($sqlswitch)>0) {
		foreach ($sqlswitch as $switch) {
			$id = $switch['id'];
			$place = $switch['place'];
			$pon = '';

			if (isset($masiv[$id]) && !empty($masiv[$id])) {
				$pon = $masiv[$id];
			} elseif (isset($masivs[$id]) && !empty($masivs[$id])) {
				$pon = $masivs[$id];
			}

			$arrayswitch[$id] = [
				'id' => $id,
				'place' => $place,
				'model' => $switch['inf'].''.$switch['model'],
				'netip' => $switch['netip'],
				'pon' => $pon
			];
		}
	}
	if (!empty($arrayswitch)) {
		foreach ($arrayswitch as $sw) {
			if (!empty($sw['place']) && !empty($sw['pon'])) {
				$result .= '<table class="resp-tab"><thead><tr><th width="10%">Pon</th><th width="20%">Mac</th><th></th></tr></thead><tbody>';
				$result .= '<tr><td class="td_name" colspan="3">' . $sw['place'] . ' - ' . $sw['model'] . '[' . $sw['netip'] . ']</td></tr>';
				foreach ($sw['pon'] as $idpon => $port) {
					$result .= '<tr><td class="td_name" colspan="3">EPON 0/' . $idpon . '</td></tr>';                
					if (!empty($port['mac'])) {
						foreach ($port['mac'] as $mac => $value_mac) {
							$result .= '<tr>';
							$result .= '<td>EPON 0/' . $idpon . '</td>';
							$result .= '<td class="td_url">' . $value_mac . '</td>';
							$result .= '<td></td>';
							$result .= '</tr>';
						}
					}
				}

				$result .= '</tbody></table>';
			}
		}
	}
	$script = '<script></script>';
	$metatags = [
		'title'=>$lang['getblacklist'],
		'description'=>$lang['getblacklist'],
		'page'=>'getblacklist'
	];
	$speedbar = '<a class="brmhref" href="/?do=device"><i class="fi fi-rr-car-battery"></i>'.$lang['alldevice'].'</a>';
	$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['listgetblacklist'].'</span>';
	$tpl->load_template('regonu/main.tpl');
	$tpl->set('{script}',$script);
	$tpl->set('{speedbar}',$speedbar);
	$tpl->set('{result}',$result);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');	
}
?>