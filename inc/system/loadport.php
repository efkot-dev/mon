<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$urlswitch = '';
$arrayswitch = [];
$metatags = array('title'=>$lang['loaderport'],'description'=>$lang['loaderport'],'page'=>'loaderport');
$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
$switch_array = [];
$tplresult = '';
$sql = "SELECT s.* FROM checkaccess a	JOIN switch s ON CONCAT('dev', s.id) = a.types	WHERE a.uid = :uid AND s.device = 'olt'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $USER['id']]);
$sql_olt = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!empty($sql_olt)) {
	foreach ($sql_olt as $switch) {
		$switch_array[$switch['id']] = [
			'place' => $switch['place'],
			'id' => $switch['id'],
			'model' => $switch['model']
		];
	}		
}
$arraypoloader = [];
$sqlpon = $db->SimpleWhile("SELECT * FROM switch_pon");
$arraypoloader = [];
if (isset($switch_array) && isset($sqlpon) && is_array($sqlpon)) {
    foreach ($sqlpon as $pon) {
		if($access->get('dev'.$pon['oltid'])){
			$count = $pon['support'] - (isset($pon['count'])?$pon['count']:0); 
			if ($count <= $config['criticonu'.$pon['support']]) {
				$arraypoloader[$pon['oltid']]['switch'] = $pon['oltid'];
				$arraypoloader[$pon['oltid']]['pon'][$pon['id']] = [
					'id' => $pon['id'],
					'name' => $pon['pon'],
					'support' => $pon['support'],
					'real' => $count,
					'count' => $pon['count'],
					'online' => $pon['online'],
					'offline' => $pon['offline']
				];
			}
		}
    }
}
if (isset($switch_array) && isset($arraypoloader) && is_array($arraypoloader)) {
$tplresult .= '<table class="resp-tab"><thead><tr><th width="3%">Id</th><th width="15%">'.$lang['port'].'</th><th width="10%">'.$lang['dilen'].'</th><th width="10%">'.$lang['allonus'].'</th><th width="10%">'.$lang['online'].'</th><th width="10%">'.$lang['offline'].'</th><th></th></tr></thead><tbody>';
	foreach ($arraypoloader as $idswitch => $switchvalue) {
		$tplresult .= '<tr><td class="td_name" colspan="7">'.$switch_array[$switchvalue['switch']]['place'].'</td></tr>';
		foreach ($switchvalue['pon'] as $pon) {
			$tplresult .= '<tr><td class="ethswitch">'.$pon['id'].'</td>
			<td class="td_url"><a href="/?do=terminal&id='.$switchvalue['switch'].'&port='.$pon['id'].'">'.$pon['name'].'</a></td>
			<td><font color="#FF9800">1:'.$pon['support'].'</font></td>
			<td><font color="blue">'.$pon['count'].'</font></td>
			<td><font color="#0f930f">'.$pon['online'].'</font></td>
			<td><font color="red">'.$pon['offline'].'</font></td>
			<td></td></tr>';
		}
	}
$tplresult .= '</tbody></table>';
}
	
$result ='<div id="onu-speedbar"><a class="brmhref" href="/?do=device"><i class="fi fi-rr-apps"></i>'.$lang['alldevice'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['loaderport'].'</span></div><div class="main-gr-tab">'.$urlswitch.'</div>'.$tplresult.'';
$tpl->load_template('main/loadport.tpl');
$tpl->set('{loadport}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>