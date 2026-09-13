<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array('title'=>$lang['lang_page_search'],'description'=>$lang['lang_page_search'],'page'=>'search');
$sql_zaput_v_bazy = '';
$sqlorderby = '';
$sqlwhere = '';
$select_pon = '';
$pagertop = '';
$orderby_data = [];
$sqlonus = '';
if(isset($_GET['act'], $_GET['search']) && $_GET['act'] == 'search') {
$types = (isset($_GET['types']) ? filter_input(INPUT_GET, 'types', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$select_search = (isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$select_uid = (isset($_GET['selectuid']) ? filter_input(INPUT_GET, 'selectuid', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$select_vlan = (isset($_GET['vlan']) ? filter_input(INPUT_GET, 'vlan', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$select_onlyactive = (isset($_GET['onlyactive']) ? filter_input(INPUT_GET, 'onlyactive', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$select_pon = (isset($_GET['selectpon']) ? filter_input(INPUT_GET, 'selectpon', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$selectolt = (isset($_GET['selectolt']) ? filter_input(INPUT_GET, 'selectolt', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$select_dist = (isset($_GET['selectdist']) ? filter_input(INPUT_GET, 'selectdist', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$select_signal = (isset($_GET['selectsignal']) ? filter_input(INPUT_GET, 'selectsignal', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$selectcurday = (isset($_GET['selectcurday']) ? filter_input(INPUT_GET, 'selectcurday', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$selectbadrx = (isset($_GET['badrx']) ? filter_input(INPUT_GET, 'badrx', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
$where_data = [];
if(isset($types) && $types=='vendor'){
	$search_parts = explode(' ', $select_search);
	$vendor = isset($search_parts[0]) ? $search_parts[0] : '';
	$model = isset($search_parts[1]) ? $search_parts[1] : '';
}
if(isset($selectolt) && !empty($selectolt)) {
	$where_data[] = "olt = '".$selectolt."'";
}
if ($select_uid === 'no') {
    $where_data[] = "uid IS NULL";
} elseif ($select_uid === 'yes') {
    $where_data[] = "uid IS NOT NULL";    
}
if($selectcurday === 'today') {
	$where_data[] = 'onus.added >= curdate()';
}
if($select_pon === 'epon') {
	$where_data[] = "type = 'epon'";
} elseif($select_pon === 'gpon') {
	$where_data[] = "type = 'gpon'";
}
if($selectbadrx === 'bad') {
	$where_data[] = "rx BETWEEN '-".$config['badsignalend']."' AND '-".$config['badsignalstart']."'";
	$select_signal = 'small';
}
if($select_onlyactive === 'on') {
	$where_data[] = "status = '1'";
} elseif($select_onlyactive === 'off') {
	$where_data[] = 'status = 2';
	$orderby_data[] = '`offline` DESC';
}
if($select_signal === 'big') {
	$orderby_data[] = '`rx` DESC';
} elseif($select_signal === 'small') {
	$orderby_data[] = '`rx` ASC';
}
if($select_dist === 'big') {
	$orderby_data[] = '`dist` DESC';
} elseif($select_dist === 'small') {
	$orderby_data[] = '`dist` ASC';
}
if (isset($select_search) && $types=='mac') {
	#$select_search = str_replace(' ','',$select_search);
	if(strlen(trim($select_search)) == 12) {
		$select_search = implode(':', str_split($select_search, 2));
	}
}
if(preg_match('/^.{4}:/', $select_search)){
	$select_search = str_replace(':','',$select_search);
}
if (preg_match('/^([0-9a-fA-F]{4}\.){2}[0-9a-fA-F]{4}$/', $select_search)) {
    $hex = str_replace('.', '', strtolower($select_search));
    $select_search = implode(':', str_split($hex, 2));
} 
if (!empty($select_search)) {
	if(isset($types) && $types=='vendor'){
		$where_data[] = "model LIKE '%" . $model . "%' AND vendor LIKE '%" . $vendor . "%'";
	}elseif(isset($types) && $types=='vlan' && isset($select_search)){
		$where_data[] = "wan = " . $select_search . "";
	}else{
		$where_data[] = " (sn LIKE '%" . $select_search . "%' OR mac LIKE '%" . $select_search . "%' OR comments LIKE '%" . $select_search . "%' OR model LIKE '%" . $select_search . "%' OR vendor LIKE '%" . $select_search . "%' OR name LIKE '%" . $select_search . "%' OR tag LIKE '%" . $select_search . "%')";
	}
}
if(is_array($where_data)) {
	$where = implode(' AND ', $where_data);
	if(!empty($where)) {
		$sqlwhere = 'WHERE '.$where;
	}
}
if(is_array($where_data)){
$where = implode(' AND ', $where_data);
if (!empty($where))
$sqlwhere = 'WHERE '.$where;
}
if(is_array($orderby_data)){
$order_by = implode(', ', $orderby_data);
if (!empty($order_by))
$sqlorderby = ' ORDER BY '.$order_by;
}
$and_access = "AND (a.uid IS NOT NULL OR onus.idonu IS NULL)";
$access_sql = "LEFT JOIN checkaccess a ON CONCAT('dev', onus.olt) = a.types AND a.uid = '{$USER['id']}'";
$sql_zaput_v_bazy = "SELECT * FROM onus {$access_sql} {$sqlwhere} {$and_access} {$sqlorderby}";
}
############################
$sqlswitch = getSwitchOlt();
$select_olt_list = '';
$DATAolt = [];
if (!empty($sqlswitch)) {
    $accessDevIds = array_column($sqlswitch, 'id');
    $userClass = $USER['class'];
    foreach ($sqlswitch as $Device) {
        if ($access->get('dev' . $Device['id'])) {
            $selected = (isset($selectolt) && $Device['id'] == $selectolt) ? 'selected' : '';
            $place = $Device['place'];
            $infModel = ($userClass >= 4) ? ' ' . $Device['inf'] . ' ' . $Device['model'] : '';
            $select_olt_list .= sprintf(
                '<option value="%d" %s>%s%s</option>',$Device['id'],$selected,$place,$infModel
            );
            $DATAolt[$Device['id']] = [
                'swid' => $Device['id'],'place' => $place,'model' => $Device['inf'] . ' ' . $Device['model']
            ];
        }
    }
}

$searchform = '<div class="search-page" id="filtersForm">';
$searchform .= '<form action="/" method="get"><input type="hidden" name="do" value="search">';
$searchform .= '<div class="search-block" id="filtersForm"><div class="input-search"><input type="text" name="search" class="search" placeholder="'.$lang['descrsearch'].'" autocomplete="off" value="'.(isset($select_search) && !empty($select_search) ? $select_search:'').'"></div><div class="item-search block-select">';
if($select_olt_list){
	$searchform .= '<div class="block"><h3>'.$lang['device'].'</h3><div><select name="selectolt" class="sort icon-arowDown open"><option value="0">'.$lang['allswitch'].'</option>'.$select_olt_list.'</select></div></div>';
}
	$searchform .= '<div class="block">
		<h3>'.$lang['typepon'].'</h3>
		<div>
			<select name="selectpon" class="sort icon-arowDown open" > 
				<option value="all" '.($select_pon=='all'?'selected':'').'>'.$lang['all'].'</option>
				<option value="gpon" '.($select_pon=='gpon'?'selected':'').'>GPON</option>
				<option value="epon" '.($select_pon=='epon'?'selected':'').'>EPON</option>
				<option value="xgpon" '.($select_pon=='xgpon'?'selected':'').'>XGS-PON</option>
			</select>
		</div>
	</div>
	<div class="block">
		<h3>'.$lang['dist'].'</h3>
		<div>
			<select name="selectdist" class="sort icon-arowDown open" > 
				<option value="all" '.(isset($select_dist) && $select_dist=='all'?'selected':'').'></option>
				<option value="big" '.(isset($select_dist) && $select_dist=='big'?'selected':'').'>'.$lang['firstbig'].'</option>
				<option value="small" '.(isset($select_dist) && $select_dist=='small'?'selected':'').'>'.$lang['firstsmall'].'</option>
			</select>
		</div>
	</div>
	<div class="block">
		<h3>'.$lang['rxsignal'].'</h3>
		<div>
			<select name="selectsignal" class="sort icon-arowDown open" > 
				<option value="all" '.(isset($select_signal) && $select_signal=='all'?'selected':'').'></option>
				<option value="big" '.(isset($select_signal) && $select_signal=='big'?'selected':'').'>'.$lang['firstgood'].'</option>
				<option value="small" '.(isset($select_signal) && $select_signal=='small'?'selected':'').'>'.$lang['firstbad'].'</option>
			</select>
		</div>
	</div>	
	<div class="block">
		<h3>UID</h3>
		<div>
			<select name="selectuid" class="sort icon-arowDown open" > 
				<option value="all"></option>
				<option value="yes" '.(isset($select_uid) && $select_uid=='yes'?'selected':'').'>'.$lang['yessed'].'</option>
				<option value="no" '.(isset($select_uid) && $select_uid=='no'?'selected':'').'>'.$lang['nosed'].'</option>
			</select>
		</div>
	</div>
</div>
<div class="search-footer">
	<input class="go" type="submit" value="'.$lang['search'].'"><input type="hidden" name="act" value="search">
	<div class="items-search">
			<div><input type="checkbox" name="onlyactive" class="check_trailer_lock" '.(isset($select_onlyactive) &&  $select_onlyactive=='on'?'checked':'').'>
			<label class="item__check icon-chekR">'.$lang['onlyonline'].'</label></div>
	</div>
</div>
</form></div>';	
$searchform_result = '';
if($sql_zaput_v_bazy){
	$sqlonusCount = $db->SimpleWhile($sql_zaput_v_bazy);
	if(isset($sqlonusCount) && count($sqlonusCount)===1){
		if(isset($sqlonusCount[0]['idonu']) && !empty($sqlonusCount[0]['idonu'])){
			$go->go('/?do=onu&id='.$sqlonusCount[0]['idonu']); //00:55:b1:1e:59:9d
			die;
		}
	}
	if(count($sqlonusCount)){		
		$count_get = 0;
		$oldlink = null;
		foreach ($_GET as $get_name => $get_value) {
			$get_name = strip_tags(str_replace(array("\"","'"),array('',''),$get_name));
			$get_value = strip_tags(str_replace(array("\"","'"),array('',''),$get_value));
			if ($get_name != 'sort' && $get_name != 'type'&& $get_name != 'page') {
				if ($count_get > 0) {
					$oldlink = $oldlink . "&" . $get_name . "=" . $get_value;
				} else {
					$oldlink = $oldlink . $get_name . "=" . $get_value;
				}
				$count_get++;
			}
		}
		if($count_get > 0)
			$oldlink = $oldlink . "";
		list($pagertop, $pagerbottom, $limit, $offset) = pager(30,count($sqlonusCount),'/?'.$oldlink);
		$get_sql_search = $sql_zaput_v_bazy.' LIMIT '.$limit.','.$offset;
		$sqlonus = $db->SimpleWhile($get_sql_search);
		$searchform_result .= '<div id="form_result">';
		if(count($sqlonus)){
			$searchform_result .= '<table cellspacing="0" cellpadding="3" width="100%" id="page_seacrh">';
			foreach($sqlonus as $ontid => $ont){
			if($access->get('dev'.$ont['olt'])){
				$onukey = (!empty($ont['mac'])?$ont['mac']:(!empty($ont['sn'])?$ont['sn']:null));
				if(isset($onukey)){
					$datatemponu = getFastOnusData($onukey);
				}
				$inface = $ont['type'].' '.$ont['inface'].'<br>'.($select_search ? highlight_word($ont['mac'].$ont['sn'],$select_search):$ont['mac'].$ont['sn']);
				$searchform_result .= '<tr id="ont-'.$ont['idonu'].'">';
				// olt
				$todayonu = checkWhenAdded($ont['added']);
				$searchform_result .= '<td class="device_icon '.($ont['status']==1?'green':'red').' onus'.$todayonu.'"><div class="site-nav-dropdown-icon-container">';
				if($ont['status']==1){
					$searchform_result .= '<img src="../style/img/online.png">';
				}else{
					$searchform_result .= '<img src="../style/img/offline.png">';
				}
				$searchform_result .= '</div></td>';
				$searchform_result .= '<td width="15%" class="device_olt" align="left">';
				$searchform_result .= '<a class="onu" href="/?do=onu&id='.$ont['idonu'].'"><img src="../style/img/link.png">'.$inface.'</a>';
				$searchform_result .= '</td>';	
				// olt
				$searchform_result .= '<td width="15%" class="device_olt" align="left">';
				$searchform_result .= '<a href="/?do=detail&act=olt&id='.$DATAolt[$ont['olt']]['swid'].'">'.$DATAolt[$ont['olt']]['place'].'<br>'.$DATAolt[$ont['olt']]['model'].'</a>';
				$searchform_result .= '</td>';
				if (isset($confPMon['ONU_VLAN']) && !empty($confPMon['ONU_VLAN']) && $confPMon['ONU_VLAN'] == 1 ) {
					$searchform_result .= '<td width="5%" class="device_rx" align="left">';
					$searchform_result .= ''.$ont['wan'].'';
					$searchform_result .= '</td>';	
				}
				// ont
				$searchform_result .= '<td width="5%" class="device_rx" align="left">';
				$searchform_result .= ''.styleRxMap($ont['rx']).'';
				$searchform_result .= '</td>';	
				// сигнал
				$searchform_result .= '<td width="5%" class="device_dist" align="left">';
				$searchform_result .= '<span>'.(isset($ont['dist']) && !empty($ont['dist'])?$ont['dist'].''.$lang['metric'].'</span>':'');
				$searchform_result .= '</td>';
				// статус
				$searchform_result .= '<td width="15%" class="device_active '.($ont['status']==1?'geton':'getoff').'" align="left">';
				if($ont['status']==2 && !empty($ont['offline']))
					$searchform_result .= '<span class="ont-offline-serach"><img src="../style/img/uptime.png">'.$lang['offline'].' <br>'.aftertime($ont['offline']).'</span>';
				$searchform_result .= '</td>';				
				$searchform_result .= '<td class="bl-all">';
					if(!empty($ont['name']))
						$searchform_result .= '<div class="search-tag">'.$ont['name'].'</div>';
					if(!empty($datatemponu['tag']))
						$searchform_result .= '<div class="search-tag">'.$datatemponu['tag'].'</div>';					
					if(!empty($datatemponu['uid']))
						$searchform_result .= '<div class="search-uid">'.$datatemponu['uid'].'</div>';
					if(!empty($ont['reason']) && $ont['status']==2)
						$searchform_result .= '<span class="reason_'.$ont['reason'].'"></span>';					
					
					if(!empty($ont['model']) || !empty($ont['vendor'])){
						$searchform_result .= '<div class="search-model">'.(isset($ont['model'])?$ont['model']:'').' '.(isset($ont['vendor'])?$ont['vendor']:'').'</div>';
					}
					if (isset($confPMon['PMON_BILLING']) && !empty($confPMon['PMON_BILLING']) && $confPMon['PMON_BILLING'] == 1) {
						$pmon_billing = PmonBillingData($ont);
						$searchform_result .=  '<div>'.PmonBillingTemplate($pmon_billing).'</div>';
					}
				$searchform_result .= '</td>';
				$searchform_result .= '</tr>';
			}
			}
			$searchform_result .= '</table>';
		}else{
			
		}
		$searchform_result .= '</div>';
	}else{
		$searchform_result .= '';
	}
}	
$tpl->load_template('terminal/searchmain.tpl');
$tpl->set('{sort}',$searchform.$searchform_result);
$tpl->set('{pagerbottom}',($sqlonus>30 ? $pagertop :''));
$tpl->set('{name}','');
$tpl->set('{result}','');
$tpl->compile('content');
$tpl->clear();
?>