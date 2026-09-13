<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
if($access->get('board_fault_view')){
$stmt = $pdo->query("SELECT * FROM incident WHERE status = 'open' AND start_time >= NOW() - INTERVAL 36 HOUR ORDER BY created_at DESC");
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($incidents as $incident) {
    echo "<div class='boad_line'>";
    echo "<h3><span class='board_status_kvadrat'></span>{$incident['reason']}</h3>";
    echo "<span class='greens'>Початок аварії:</span> ".$incident['start_time']."<br>";
    echo "<span class='offes'>Час відновлення:</span> ".$incident['restore_time'];
    /*
	$incident_id = $incident['id'];
    $stmt_loc = $pdo->prepare("
        SELECT ill.*, l.name as location_name, s.name as street_name 
        FROM incident_log_location ill
        LEFT JOIN location l ON ill.location_id = l.id
        LEFT JOIN location_street s ON ill.street_id = s.id
        WHERE ill.incident_id = ?
    ");
    $stmt_loc->execute([$incident_id]);
    $locations = $stmt_loc->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($locations)) {
        $loc_output = [];
        foreach ($locations as $loc) {
            $loc_str = "{$loc['location_name']}, {$loc['street_name']}";
            if (!empty($loc['house_numbers'])) {
                $loc_str .= " {$loc['house_numbers']}";
            }
            $loc_output[] = $loc_str;
        }
        echo implode("; ", $loc_output);
        echo "<br>";
    }
    $stmt_sw = $pdo->prepare("
        SELECT ils.*, sw.place as switch_name 
        FROM incident_log_switch ils
        LEFT JOIN switch sw ON ils.switch_id = sw.id
        WHERE ils.incident_id = ?
    ");
    $stmt_sw->execute([$incident_id]);
    $switches = $stmt_sw->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($switches)) {
        $sw_output = [];
        foreach ($switches as $sw) {
            $sw_str = "{$sw['switch_name']} {$sw['port_id']}";
            $sw_output[] = $sw_str;
        }
        echo implode("; ", $sw_output);
        echo "<br>";
    }
	*/
    echo "</div>";
}
}
?>