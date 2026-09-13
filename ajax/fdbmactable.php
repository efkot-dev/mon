<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
$sql_list_switch = $db->SimpleWhile("SELECT place,id,model,inf FROM switch WHERE monitor = 'yes' AND device = 'olt'");
if (isset($sql_list_switch) && is_array($sql_list_switch)) {
    foreach ($sql_list_switch as $switch) {
        $switch_array[$switch['id']] = [
           'place' => $switch['place'],'id' => $switch['id'],'model' => $switch['model']
        ];
    }        
}
$tplresult = '';
$where_olt = '';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$offset = isset($_POST['offset']) ? Clean::int($_POST['offset']): null;
$query = (isset($_POST["query"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["query"]))))):null);
$orderby = "";
#$where_olt = "fdb_tables.olt = '{$id}'";
$search = "";
if(isset($query) && strlen($query) > 0) {
	$query = strtolower($query);
    if (strlen($query) == 12) {
        $query = implode(':', str_split($query, 2));
    }	
	$search .= "WHERE (fdb_tables.mac LIKE '%$query%' 
    OR fdb_tables.inface LIKE '%$query%' 
    OR onus.mac LIKE '%$query%' 
    OR onus.sn LIKE '%$query%' 
    OR onus.name LIKE '%$query%' 
    OR fdb_tables.vlan LIKE '%$query%')";
}
$records_per_page = 30; 
$limit = "";
$zaput = "SELECT fdb_tables.mac as mac_client, 
fdb_tables.vlan as vlan_client, 
fdb_tables.idonu as ont_id, fdb_tables.olt as olt_id, 
onus.*, onus.mac as onu_mac FROM fdb_tables 
LEFT JOIN onus ON (onus.idonu = fdb_tables.idonu) 
 $where_olt $search $orderby $limit"; // AND onus.olt = '{$id}'
$sqlonus = $db->SimpleWhile($zaput);
$total_records = count($sqlonus);
$tplresult .= '<table class="resp-tab list-onu-olt"><thead><tr>
	<th class="mob_w10" width="4%">'.$lang['status'].'</th>
	<th width="10%">Olt</th>
	<th width="10%">'.$lang['gilka'].'</th>
	<th width="15%">MAC Onu</th>
	<th width="15%">MAC Client</th>
	<th width="5%">Vlan</th>
	<th width="5%">Rx Onu</th>
	<th class="mobile" width="10%">
		<span class="inf_status">
			<span class="tim1">'.$lang['sfp_2'].'</span>
			<span class="tim2">'.$lang['sfp_1'].'</span>
		</span>
	</th>
	<th></th>
	</tr></thead><tbody>';
if($total_records>0){
    $total_pages = ceil($total_records / $records_per_page);
    $start = $offset * $records_per_page;
    $end = min(($offset + 1) * $records_per_page, $total_records);
    for ($i = $start; $i < $end; $i++) {
		$status = statusTermianl($sqlonus[$i]['status']);
		if($sqlonus[$i]['status']==1){
			$get_status = $status['img'];
		}else{
			$get_status = reason_onu($sqlonus[$i]['status'],$sqlonus[$i]['reason']);
		}
		$tplresult .= '<tr class="'.$status['css'].'">';
		$tplresult .= '<td class="status" '.(isset($sqlonus[$i]['reason']) ? 'id="'.$sqlonus[$i]['reason'].'"' : '').'>
			'.$get_status.'
		</td>';
		$tplresult .= '<td class="inface_onu">
			<a href="/?do=detail&act=olt&id='.$switch_array[$sqlonus[$i]['olt']]['id'].'">'.$switch_array[$sqlonus[$i]['olt']]['place'].'</a>
		</td>';		
		$tplresult .= '<td class="inface_onu">
			<a href="/?do=onu&id='.$sqlonus[$i]['idonu'].'">'.$sqlonus[$i]['type'].' '.highlight_word($sqlonus[$i]['inface'],$query).'</a>
		</td>';
		$onukey = (!empty($sqlonus[$i]['onu_mac'])?$sqlonus[$i]['onu_mac']:(!empty($sqlonus[$i]['sn'])?$sqlonus[$i]['sn']:null));
		if(isset($onukey)){
			$datatemponu = getFastOnusData($onukey);
		}
		$tplresult .= '<td class="td_url">
			<a href="/?do=onu&id='.$sqlonus[$i]['idonu'].'">'.highlight_word($onukey,$query).'</a>
		</td>';		
		$tplresult .= '<td class="td_url">
			<img class="getmacinfo" onclick="getmacswitch(\''.$sqlonus[$i]['mac_client'].'\','.$i.')" src="../style/img/information.png"><div style="display:initial;" id="getmac_'.$i.'"><a href="/?do=onu&id='.$sqlonus[$i]['idonu'].'">'.highlight_word($sqlonus[$i]['mac_client'],$query).'</a>
		</td>';		
		$tplresult .= '<td class="td_url">'.$sqlonus[$i]['vlan_client'].'</td>';
		$tplresult .= '<td>';
		$tplresult .=  signalTerminal($sqlonus[$i]['rx']).($sqlonus[$i]['rxstatus']=='up' || $sqlonus[$i]['rxstatus']=='down' ? '<span class="signaldown"><i class="fi fi-rr-angle-small-'.$sqlonus[$i]['rxstatus'].'"></i></span>':'');
		$tplresult .= '</td>';
		$tplresult .= '<td class="mobile">';
			if($sqlonus[$i]['status']==1){
				$tplresult .= '<span class="on_">'.aftertime($sqlonus[$i]['online']).'</span>';
			}else{
				$tplresult .= '<span class="off_">'.aftertime($sqlonus[$i]['offline']).'</span>';
			}
		$tplresult .= '</td>';
		// name
		$onu_name = (!empty($sqlonus[$i]['name'])?'<span class="name-onu">'.highlight_word($sqlonus[$i]['name'],$query).'</span>':'');
		// Tag		
		$tag = (!empty($datatemponu['tag'])?'<span class="terminaltag">'.highlight_word($datatemponu['tag'],$query).'</span>':'');
		// vendor		
		$tplresult .= '<td class="description_name mobile_font">
		'.$onu_name.' '.$tag.' 
		</td>';
		$tplresult .= '</tr>';
    }
	if ($total_records > $records_per_page) {
		$tplresult .= '</tbody></table>';
		$tplresult .= renderPaginationtpl($offset + 1, $total_pages);
	}
} else {
    $tplresult .= '<tr><td colspan="7">'.$lang['emlist'].'</td></tr>';
	$tplresult .= '</tbody></table>';
}
echo $tplresult;
?>
