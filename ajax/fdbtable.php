<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';

$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
$offset = isset($_POST['offset']) ? Clean::int($_POST['offset']) : 0; // Default offset to 0 if not set
$query = isset($_POST['query']) ? str_replace(' ', '', Clean::text(trim(strip_tags(stripslashes($_POST['query']))))) : null;

$orderby = "ORDER BY fdb_tables.added ASC";
$where_olt = "fdb_tables.olt = '{$id}'";
$search = "";

if (isset($query) && strlen($query) > 0) {
    $search = "AND (fdb_tables.mac LIKE '%$query%' OR fdb_tables.inface LIKE '%$query%' OR fdb_tables.vlan LIKE '%$query%')";
}

$records_per_page = 30; 
$limit_start = $offset * $records_per_page;
$limit = "LIMIT $limit_start, $records_per_page";

$zaput = "SELECT 
    fdb_tables.idonu as ont_id, 
    fdb_tables.olt as olt_id, 
    GROUP_CONCAT(fdb_tables.mac ORDER BY fdb_tables.added ASC SEPARATOR ', ') as mac_clients, 
    GROUP_CONCAT(fdb_tables.vlan ORDER BY fdb_tables.added ASC SEPARATOR ', ') as vlan_clients, 
    onus.*, 
    onus.mac as onu_mac 
FROM 
    fdb_tables 
LEFT JOIN 
    onus ON (onus.idonu = fdb_tables.idonu AND onus.olt = '{$id}')
WHERE 
    $where_olt 
    $search 
GROUP BY 
    fdb_tables.idonu, fdb_tables.added
$orderby 
$limit";
$sqlonus = $db->SimpleWhile($zaput);
$total_zaput = "SELECT COUNT(*) as total FROM fdb_tables 
LEFT JOIN onus ON (onus.idonu = fdb_tables.idonu AND onus.olt = '{$id}')
WHERE $where_olt";
if (isset($query) && strlen($query) > 0) {
	$total_records = count($sqlonus);
}else{
	$total_records = $db->Simple($total_zaput)['total'];	
}
echo '<table class="resp-tab list-onu-olt"><thead><tr><th class="mob_w10" width="4%">' . $lang['status'] . '</th><th width="10%">' . $lang['gilka'] . '</th><th width="15%">MAC Onu</th><th width="10%">Rx Onu</th><th></th></tr></thead><tbody>';
if ($total_records > 0) {
    $total_pages = ceil($total_records / $records_per_page);
    $groupedData = [];
    foreach ($sqlonus as $onu) {
        $groupedData[$onu['idonu']][] = $onu;
    }
    foreach ($groupedData as $idonu => $onuses) {
        $onu = $onuses[0]; // Get the first ONU for display purposes
        $status = statusTermianl($onu['status']);
        $get_status = ($onu['status'] == 1) ? $status['img'] : reason_onu($onu['status'], $onu['reason']);
        echo '<tr class="' . $status['css'] . '">';
        echo '<td class="status" ' . (isset($onu['reason']) ? 'id="' . $onu['reason'] . '"' : '') . '>' . $get_status . '</td>';
        echo '<td class="inface_onu"><a href="/?do=onu&id=' . $onu['idonu'] . '">' . $onu['type'] . ' ' . $onu['inface'] . '</a></td>';
        $onukey = (!empty($onu['onu_mac']) ? $onu['onu_mac'] : (!empty($onu['sn']) ? $onu['sn'] : null));
        if (isset($onukey)) {
            $datatemponu = getFastOnusData($onukey);
        }
        echo '<td class="td_url"><a href="/?do=onu&id=' . $onu['idonu'] . '">' . $onukey . '</a></td>';
        echo '<td>' . signalTerminal($onu['rx']) . ($onu['rxstatus'] == 'up' || $onu['rxstatus'] == 'down' ? '<span class="signaldown"><i class="fi fi-rr-angle-small-' . $onu['rxstatus'] . '"></i></span>' : '') . '</td>';
        $onu_name = (!empty($onu['name']) ? '<span class="name-onu">' . $onu['name'] . '</span>' : '');
        $tag = (!empty($datatemponu['tag']) ? '<span class="terminaltag">' . $datatemponu['tag'] . '</span>' : '');
        echo '<td class="description_name mobile_font">';
        echo $onu_name . ' ' . $tag;
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan="6"><div class="list_fdb">';
		$mac_clients = explode(',', $onu['mac_clients']);
		$vlan_clients = explode(',', $onu['vlan_clients']);
		$onu_count = min(count($mac_clients), count($vlan_clients));
		for ($j = 0; $j < $onu_count; $j++) {
			$mac_client = trim($mac_clients[$j]);
			$vlan_client = trim($vlan_clients[$j]);
			echo '<a href="/?do=onu&id=' . $onu['idonu'] . '">' . $mac_client . ' [' . $vlan_client . ']</a>';
		}
        echo '</div></td></tr>';
    }
    echo '</tbody></table>';
    if ($total_records > $records_per_page) {
        echo renderPaginationtpl($offset + 1, $total_pages);
    }
} else {
    echo '<tr><td colspan="7">' . $lang['emlist'] . '</td></tr>';
    echo '</tbody></table>';
}
?>
