<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$sqlselectswitch = $db->Multi('switch');
$array_device = array();
if(count($sqlselectswitch) > 0) {
    foreach ($sqlselectswitch as $arr) {
		$array_device[$arr['id']] = array(
			'place' => $arr['place'], 'netip' => $arr['netip']
		);
	}
}
$zapros = (isset($_POST["mac"]) ? str_replace(' ','',Clean::text(trim(strip_tags(stripcslashes($_POST["mac"]))))):null);
if(!empty($USER['id']) && $zapros){
$where_onusdata = "(mac LIKE '%".$zapros."%')";
#$orderby = " ORDER BY update ASC";
$limit = " LIMIT 30";
$sqlmac = $db->SimpleWhile("SELECT * FROM mac_router WHERE $where_onusdata $orderby $limit");
echo '<br><table class="resp-tab"><thead><tr>
<th width="2%">ID</th>
<th width="10%">Mac</th>
<th width="10%">Inface</th>
<th width="15%">Device</th>
<th width="10%">Added</th>
<th width="10%">Udate</th>
<th>information</th>
</tr></thead><tbody>';
foreach ($sqlmac as $datamac) {  
echo '<tr>';    
echo '<td>'.$datamac['id'].'</td>';    
echo '<td><font color="#1f7bc3"><b>'.$datamac['mac'].'</b></font></td>';    
echo '<td><font color="#1f7bc3">'.$datamac['inface'].'</font></td>';    
echo '<td><font color="#222">'.$array_device[$datamac['deviceid']]['place'].'</font></td>';    
echo '<td>'.$datamac['added'].'</td>';    
echo '<td>'.$datamac['update'].'</td>';    
echo '<td>'.(isset($datamac['onu'])?$datamac['onu']:'').'</td>';    
echo '</tr>';
}
echo '</tbody></table>';
}
?>