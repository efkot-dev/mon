<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
$llid = isset($_GET['llid']) ? Clean::int($_GET['llid']) : null;
$selectdey = "AND added >= CURDATE() AND added < DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
$sql = "SELECT added, newin, newout FROM switch_port_err WHERE deviceid = '{$id}' AND llid = '{$llid}' {$selectdey} ORDER BY added ASC"; 
$result = $db->SimpleWhile($sql);
$data = [
    'dates' => [],
    'in_errors' => [],
    'out_errors' => []
];
if (isset($result) && count($result) > 0) {
    foreach ($result as $row) {
        $data['dates'][] = date('H:i', strtotime($row['added']));
        $data['in_errors'][] = $row['newin']; 
        $data['out_errors'][] = $row['newout'];
    }
}
echo json_encode($data);
?>

