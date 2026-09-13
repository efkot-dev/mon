<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
$selected_tpl = '';
$ports_per_page = 4;
$nav = isset($_REQUEST['nav']) ? (int)$_REQUEST['nav'] : 1;
$offset = ($nav - 1) * $ports_per_page;
$query = isset($_REQUEST['query']) ? str_replace(' ', '', Clean::text(trim(strip_tags(stripslashes($_REQUEST['query']))))) : null;
$portolt = (int)isset($_REQUEST['portolt']) ? str_replace(' ', '', Clean::text(trim(strip_tags(stripslashes($_REQUEST['portolt']))))) : null;
$orderby = "ORDER BY fdb_tables.added ASC";
$where_olt = "fdb_tables.olt = '{$id}'";
$select = "";
if (isset($query) && strlen($query) > 0) {
    $select .= "AND (fdb_tables.mac LIKE '%$query%' 
    OR onus.inface LIKE '%$query%' 
    OR onus.mac LIKE '%$query%' 
    OR onus.sn LIKE '%$query%' 
    OR onus.name LIKE '%$query%' 
    OR fdb_tables.vlan LIKE '%$query%')";
}
if (isset($portolt) && strlen($portolt) > 0) {
    $select .= "AND onus.zte_idport = '{$portolt}'";
}
$sql_zaput = "SELECT 
    fdb_tables.idonu as ont_id, 
    fdb_tables.olt as olt_id, 
    fdb_tables.mac as mac_client, 
    onus.*, 
    onus.mac as onu_mac, 
    onus.inface as onu_inface, 
    onus.zte_idport as onu_port, 
    onus.portolt as onu_portolt, 
    onus.type as onu_type, 
    onus.name as onu_name, 
    onus.sn as onu_sn 
FROM 
    fdb_tables 
LEFT JOIN 
    onus ON (onus.idonu = fdb_tables.idonu AND onus.olt = '{$id}')
WHERE 
    $where_olt 
    $select 
GROUP BY 
    fdb_tables.idonu, fdb_tables.added
$orderby";
$sql_onu = $pdo->query($sql_zaput);
$current_ont = [];
while ($row = $sql_onu->fetch(PDO::FETCH_ASSOC)) {
	$idport_olt = $row['onu_port'] ?? $row['onu_portolt'];
    $current_ont[$idport_olt][] = $row;
}
$sql_pon = $pdo->query("SELECT type, inface, zte_idport, portolt from onus where olt = '{$id}'");
$data_pon = [];
while ($roq = $sql_pon->fetch(PDO::FETCH_ASSOC)) {
	$idport_olt = $roq['zte_idport'] ?? $roq['portolt'];
	if(isset($roq['type']) && $roq['type']!=false){
		$cleaned_port = preg_replace('/:\d+/', '', $roq['inface']);
		$data_pon[$idport_olt]['name'] = strtoupper($roq['type'].' '.$cleaned_port);
	}
}
$selected_tpl .= '<select name="portolt" class="sort icon-arowDown open">';        
$selected_tpl .= '<option value="">'.$lang['all'].'</option>';
foreach ($data_pon as $id_pon => $port) {
    $selected = ($id_pon == $portolt) ? 'selected' : '';
    $selected_tpl .= '<option value="' . $id_pon . '" ' . $selected . '>' . $port['name'] . '</option>';
}
$selected_tpl .= '</select>';
$total_ports = count($current_ont);
$total_pages = ceil($total_ports / $ports_per_page);
$tplRes .= '
<div class="search-block" id="filtersForm">
<form method="get" action="/" class="search_form">
    <input type="hidden" name="do" value="detail">
    <input type="hidden" name="act" value="olt">
    <input type="hidden" name="page" value="fdbtable">
    <input type="hidden" name="id" value="' . $id . '">
		<div class="search-block" id="filtersForm" style="display: flex;margin: 0;">
			<div class="item-search block-select" style="width:300px;margin-right: 20px;">
				<div class="block">
					<h3>MAC адрес або серійний номер</h3>
					<div>
						<input type="text" class="search" placeholder="Mac or SN" name="query" value="' . htmlspecialchars($query) . '" autocomplete="off" value="">
					</div>
				</div>
			</div>
			<div class="item-search block-select">
				<div class="block">
					<h3>Pon</h3>
					<div>
						'.$selected_tpl.'
					</div>
				</div>
			</div>
		</div>	
		<div class="fdb_search_pole">
			<input type="submit" value="Шукати ONU"> 
			'.(isset($portolt) || isset($query) ? '<a href="/?do=detail&act=olt&page=fdbtable&id='.$id.'" class="clear">Clear search</a>' : '').'
			
		</div>
	</form>
</div>';
$ports_on_page = array_slice($current_ont, $offset, $ports_per_page, true);
$tplRes .= "<div id=\"fdb_tables_list\">";
foreach ($ports_on_page as $sfpid => $data) {
    $tplRes .= '<div class="list_port_fdb_tables">';
    $tplRes .= "<div class=\"name_port_fdb_tables_ont\">" . $data_pon[$sfpid]['name'] . "</div>";
    $tplRes .= '<div class="list_port_fdb_tables_ont">';    
    foreach ($data as $ontid => $ont) {
        $tplRes .= '<div class="ont_fdb_table">';
        $tplRes .= '<div class="onu_inface">' . highlight_word($ont['onu_type'] . ' ' . $ont['onu_inface'],$query) . '</div>';
        $tplRes .= '<div class="onu_sn_mac"><img src="../style/img/lan.png"><a href="/?do=onu&id=">' . highlight_word($ont['onu_sn']??$ont['onu_mac'],$query) . '</a></div>';
        $tplRes .= '<div class="onu_mac_sn"><img src="../style/img/house-user.png">' . highlight_word($ont['mac_client'],$query) . '</div>';
        $tplRes .= '</div>';
    }
    $tplRes .= '</div>';
    $tplRes .= '</div>';
}
$tplRes .= '</div>';
$tplRes .= "<div class=\"nav_port\">";
if ($nav > 1 && $nav != 1 && (($nav - 1) != 1)) {
    $tplRes .= "<a href=\"/?do=detail&act=olt&page=fdbtable&id=" . $id . "&nav=" . ($nav - 1) . "\">&laquo; Previous port</a>";
}
for ($i = 1; $i <= $total_pages; $i++) {
    $tplRes .= "<a href=\"/?do=detail&act=olt&page=fdbtable&id=" . $id . "&nav=$i\">$i</a>";
}
if ($nav < $total_pages) {
    $tplRes .= "<a href=\"/?do=detail&act=olt&page=fdbtable&id=" . $id . "&nav=" . ($nav + 1) . "\">Next port &raquo;</a>";
}
$tplRes .= "</div>";
?>

