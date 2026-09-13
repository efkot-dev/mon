<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = array('title'=>$lang['onuvendor'],'description'=>$lang['onuvendor'],'page'=>'vendor');
$result .= '<table class="resp-tab"><thead><tr>
<th>ID Vendor</th>
	<th>Count</th>
	<th></th>
</tr></thead><tbody>';
$sql_total = "SELECT COUNT(*) AS total_count FROM onus WHERE vendor IS NOT NULL OR model IS NOT NULL;";
$total_result = $db->Simple($sql_total);
$total_count = intval($total_result['total_count']);
$sql = "SELECT
	CASE
		WHEN vendor IS NOT NULL AND model IS NOT NULL THEN CONCAT(vendor, ' ', model)
		WHEN vendor IS NOT NULL THEN vendor
		WHEN model IS NOT NULL THEN model
		ELSE 'No Data Available'
	END AS onu_model,
	COUNT(*) AS count
		FROM onus
		WHERE vendor IS NOT NULL OR model IS NOT NULL
		GROUP BY onu_model ORDER BY count DESC";
$onu_vendor = $db->SimpleWhile($sql);
$onu_model = [];
foreach ($onu_vendor as $item) {
	if(preg_match('/[a-zA-Z0-9]+/', $item['onu_model'])) {
		$onu_model[] = [
			'onu_model' => $item['onu_model'],
			'count' => intval($item['count'])
		];
	}
}
foreach ($onu_model as $onu) {
    $percentage = ($onu['count'] / $total_count) * 100;    
    $loadbar = 'style="width:'.$percentage.'%;background-color:green;height:100%;"';    
    $result .= '<tr>
	<td class="inface_onu"><a href="/?do=search&search='.$onu['onu_model'].'&selectolt=0&act=search">'.$onu['onu_model'].'</a></td>
	<td>'.$onu['count'].'</td>
	<td>
	<div style="width:300px;height:20px;background-color:#e0e0e0;">
	<div '.$loadbar.'></div>
	</div>
	</td>
	</tr>';
}
$result .= '</tbody></table>';
$tpl->load_template('vendor.tpl');
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
?>
