<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$select_olt_list = $select_olt_list ?? null;
$select_olt = $select_olt ?? null;
$zapros = (isset($_POST["zapros"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["zapros"]))))):null);
if(preg_match('/^.{4}:/', $zapros)){
	$zapros = str_replace(' ','',$zapros);
}
if (preg_match('/^([0-9a-fA-F]{4}\.){2}[0-9a-fA-F]{4}$/', $zapros)) {
    $hex = str_replace('.', '', strtolower($zapros));
    $zapros = implode(':', str_split($hex, 2));
} 
if(!empty($USER['id'])){
$sql = "SELECT id, place, model, inf FROM switch WHERE device = :device";
$stmt = $pdo->prepare($sql);
$stmt->execute([':device' => 'olt']);
$sql_device = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($sql_device) {
    foreach ($sql_device as $Device) {
        $select_olt_list .= '<option value="'.$Device['id'].'"';
        if (!empty($select_olt) && $Device['id'] == $select_olt) {
            $select_olt_list .= ' selected';
        }
        $select_olt_list .= '>'
            .$Device['place']
            .($USER['class'] >= 4
                ? ' '.$Device['inf'].' '.$Device['model']
                : '')
            .'</option>';
        $DATAolt[$Device['id']] = [
            'swid'  => $Device['id'],
            'place' => $Device['place'],
            'model' => $Device['inf'].' '.$Device['model'],
        ];
    }
}
	$orderby = " ORDER BY idonu ASC";
	$limit = " LIMIT 30";
	$where_onusdata = "(onusdata.tag LIKE '%".$zapros."%' OR onusdata.name LIKE '%".$zapros."%' OR onusdata.uid LIKE '%".$zapros."%')";
	$where_onus = "(onus.sn LIKE '%".$zapros."%' OR onus.name LIKE '%".$zapros."%' OR onus.mac LIKE '%".$zapros."%')";
	$sqlonus = $db->SimpleWhile("
		SELECT onusdata.*, onus.*
		FROM onusdata
		LEFT JOIN onus ON (onus.mac = onusdata.onukey OR onus.sn = onusdata.onukey)
		LEFT JOIN checkaccess a ON CONCAT('dev', onus.olt) = a.types AND a.uid = '{$USER['id']}'
		WHERE $where_onusdata 
		AND (a.uid IS NOT NULL OR onus.idonu IS NULL)
		$orderby 
		$limit");
	if (empty($sqlonus)) {
		$sqlonus = $db->SimpleWhile("SELECT * FROM onus 
		LEFT JOIN checkaccess a ON CONCAT('dev', olt) = a.types AND a.uid = '{$USER['id']}'		
		WHERE $where_onus 
		AND (a.uid IS NOT NULL OR idonu IS NULL)
		$orderby $limit");
	} else {
		$sqlonus_from_onus = $db->SimpleWhile("SELECT * FROM onus WHERE (onus.name LIKE '%".$zapros."%' OR onus.mac LIKE '%".$zapros."%' OR onus.sn LIKE '%".$zapros."%') $orderby $limit");
		$sqlonus = array_merge($sqlonus, $sqlonus_from_onus);
	}
	if(!empty($USER['id'])){
		$tplresult = '<div id="form_result">';
		if(count($sqlonus)){
			$tplresult .= '<table cellspacing="0" cellpadding="3" width="100%" id="page_seacrh">';
			foreach($sqlonus as $ontid => $ont){
				$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
				if($onukey){
				$datatemponu = $db->Fast('onusdata','*',['onukey'=>$onukey]);
				$inface = $ont['type'].' '.$ont['inface'].'<br>'.($zapros ? highlight_word($ont['mac'].$ont['sn'],$zapros):$ont['mac'].$ont['sn']);
				$tplresult .= '<tr id="terminals">';
				// olt
				$tplresult .= '<td class="device_icon '.($ont['status']==1?'green':'red').'"><div class="site-nav-dropdown-icon-container">';
				if($ont['status']==1){
					$tplresult .= '<img src="../style/img/online.png">';
				}else{
					$tplresult .= '<img src="../style/img/offline.png">';
				}
				$tplresult .= '</div></td>';
				$tplresult .= '<td width="15%" class="device_olt" align="left">';
				$tplresult .= '<a href="/?do=onu&id='.$ont['idonu'].'"><img src="../style/img/link.png">'.$inface.'</a>';
				$tplresult .= '</td>';	
				// olt
				$tplresult .= '<td width="15%" class="device_olt" align="left">';
				$tplresult .= '<a href="/?do=detail&act=olt&id='.$DATAolt[$ont['olt']]['swid'].'">'.$DATAolt[$ont['olt']]['place'].'<br>'.$DATAolt[$ont['olt']]['model'].'</a>';
				$tplresult .= '</td>';
				// ont
				$tplresult .= '<td width="5%" class="device_rx" align="left">';
				$tplresult .= ''.styleRxMap($ont['rx']).'';
				$tplresult .= '</td>';	
				// сигнал
				$tplresult .= '<td width="5%" class="device_dist" align="left">';
				$tplresult .= '<span>'.$ont['dist'].'м</span>';
				$tplresult .= '</td>';
				// статус
				$tplresult .= '<td width="15%" class="device_active '.($ont['status']==1?'geton':'getoff').'" align="left">';
				#if($ont['status']==1 && !empty($ont['online']))
					#$tplresult .= '<span class="ont-online-serach"><img src="../style/img/uptime.png">онлайн з<br>'.$ont['online'].'</span>';				
				if($ont['status']==2 && !empty($ont['offline']))
					$tplresult .= '<span class="ont-offline-serach"><img src="../style/img/uptime.png">оффлайн <br>'.aftertime($ont['offline']).'</span>';
				$tplresult .= '</td>';				
				$tplresult .= '<td class="bl-all">';
					if(!empty($ont['name']))
						$tplresult .= '<div class="search-tag"><img src="../style/img/conn1.png">'.$ont['name'].'</div>';					
					if(!empty($datatemponu['name']))
						$tplresult .= '<div class="search-tag">'.$datatemponu['name'].'</div>';
					if(!empty($datatemponu['tag']))
						$tplresult .= '<div class="search-tag">'.$datatemponu['tag'].'</div>';					
					if(!empty($datatemponu['uid']))
						$tplresult .= '<div class="search-uid">'.$datatemponu['uid'].'</div>';					
					if(!empty($datatemponu['reason']) && $ont['status']==2)
						$tplresult .= '<span class="reason_'.$ont['reason'].'"></span>';
				$tplresult .= '</td>';
				#$tplresult .= '<td class="function" width="2%">';
					#$tplresult .= '<input type="checkbox" name="" value=""/>';
				#$tplresult .= '</td>';
				$tplresult .= '</tr>';
				}
			}
			$tplresult .= '</table>';
		}else{
			$tplresult .= '<div class="empty_search">'.$lang['empty_search'].': <b>'.$zapros.'</b></div>';
		}
		$tplresult .= '</div>';
	}
	echo $tplresult;
}
?>