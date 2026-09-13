<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$offset = isset($_POST['offset']) ? Clean::int($_POST['offset']): null;
$query = (isset($_POST["query"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["query"]))))):null);
$orderby ="ORDER BY billing_usr.added ASC";
if(isset($id) && $id>0){
	$where_olt = "billing_usr.deviceid = '{$id}'";
}else{
	$where_olt = "billing_usr.onuid IS NOT NULL";
}
$search = "";
if(isset($query) && strlen($query) > 0) {

	$search = "AND billing_usr.onumac LIKE '%$query%' 
		OR billing_usr.pib LIKE '%$query%' 
			OR billing_usr.city LIKE '%$query%' 
				OR billing_usr.street LIKE '%$query%'";
}
$records_per_page = 30; 
$limit = "";
$zaput = "SELECT billing_usr.* , billing_usr.uid as usr_uid, billing_usr.deviceid as olt_id, onus.*, onus.status as onu_status, onus.reason as onu_reason, onus.added as onu_added, onus.mac as onu_mac FROM billing_usr 
LEFT JOIN onus ON (onus.idonu = billing_usr.onuid) WHERE $where_olt $search $orderby $limit";

$sqlonus = $db->SimpleWhile($zaput);
$total_records = count($sqlonus);
echo'<table class="resp-tab list-onu-olt"><thead><tr>
	<th class="mob_w10" width="4%">'.$lang['status'].'</th>
	<th>П.І.Б</th>
	<th width="15%">Місто, вул, вуб, кв</th>
	<th width="15%">ONU</th>
	<th width="6%"><span class="inf_signal"><span class="sig2">RX ONU</span></span></th>
	<th width="15%">OLT</th>
	</tr>
	</thead><tbody>';
if($total_records>0){
    $total_pages = ceil($total_records / $records_per_page);
    $start = $offset * $records_per_page;
    $end = min(($offset + 1) * $records_per_page, $total_records);
    for ($i = $start; $i < $end; $i++) {
		$status = statusTermianl($sqlonus[$i]['onu_status']);
		$added = checkWhenAdded($sqlonus[$i]['onu_added']);
		if($sqlonus[$i]['onu_status']==1){
			$get_status = $status['img'];
		}else{
			$get_status = reason_onu($sqlonus[$i]['onu_status'],$sqlonus[$i]['onu_reason']);
		}
		echo'<tr class="'.$status['css'].''.$added.'">';
		echo'<td class="status">'.$get_status.'</td>';
		echo'<td class="td_names">'.$sqlonus[$i]['pib'].'</td>';
		echo'<td class="description_name">';		
		$pmon_billing = PmonBillingData($sqlonus[$i]);
		echo PmonBillingTemplate($pmon_billing);		
		echo'</td>';
		echo'<td class="td_url"><a href="/?do=onu&id='.$sqlonus[$i]['onuid'].'">'.$sqlonus[$i]['onumac'].'</a></td>';
		echo'<td>';		
		echo signalTerminal($sqlonus[$i]['rx']).($sqlonus[$i]['rxstatus']=='up' || $sqlonus[$i]['rxstatus']=='down' ? '<span class="signaldown"><i class="fi fi-rr-angle-small-'.$sqlonus[$i]['rxstatus'].'"></i></span>':'');
		echo'</td>';		
		echo'<td>'.$sqlonus[$i]['deviceid'].'</td>';
		echo'</tr>';
    }
	if ($total_records > $records_per_page) {
		echo '</tbody></table>';
		renderPagination($offset + 1, $total_pages);
	}
} else {
    echo'<tr><td colspan="7">'.$lang['emlist'].'</td></tr>';
	echo'</tbody></table>';
}

?>
