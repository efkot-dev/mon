<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$tplRes .= "
<STYLE>
.traffic .graph-container canvas{
    height: 300px;
    width: 100%;	
}	
</STYLE>";
$tplRes .= "<script>";
    $sql_traffic = $db->SimpleWhile("SELECT * FROM switch_traffic WHERE deviceid = '{$id}'");
    foreach ($sql_traffic as $row) {
       $tplRes .= "displayGraph({$row['id']});";
    }
	$tplRes .= "</script>";	
    foreach ($sql_traffic as $row) {
        $sql_port_monitor = $db->Simple("SELECT nameport FROM switch_port WHERE id = '{$row['portid']}' LIMIT 1");
        $tplRes .= "<div class=\"traffic\">";
        $tplRes .= "<div id=\"graph{$row['id']}\" class=\"graph-container\"></div>"; // Додаємо контейнер для графіка
        $tplRes .= "</div>";
    }
?>