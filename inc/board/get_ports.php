<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if (!$access->get('board_fault_edit')) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$rawSwitchIds = isset($_GET['switch_ids']) ? (array)$_GET['switch_ids'] : [];
$switchIds = [];
foreach ($rawSwitchIds as $switchId) {
    if (is_numeric($switchId) && (int)$switchId > 0) {
        $switchIds[(int)$switchId] = (int)$switchId;
    }
}
$switchIds = array_values($switchIds);

if (empty($switchIds)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($switchIds), '?'));
$sql = "
    SELECT
        sp.id,
        sp.pon,
        COALESCE(spt.descrport, '') AS descr
    FROM switch_pon sp
    LEFT JOIN switch_port spt
        ON spt.deviceid = sp.oltid
       AND spt.llid = sp.sfpid
    WHERE sp.oltid IN ($placeholders)
    ORDER BY sp.pon ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($switchIds);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
exit;
?>
