<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$sw = array();
$monitor_port_err = array();
$sql_switch = $db->Multi($PMonTables['switch'], 'id,place,monitor');
if (count($sql_switch)) {
    foreach ($sql_switch as $switch) {
        $sqlsw = $db->Simple("select count(id) as count from devicelogs where deviceid = " . $switch['id'] . " and added >=curdate()");
        if (!empty($sqlsw['count'])) {
            $sw[$switch['id']]['count'] = $sqlsw['count'];
            $sw[$switch['id']]['name'] = $switch['place'];
        }
    }
} else {
    echo '<div style="padding:0 20px;font-size: 13px;">' . $lang['firstpmon'] . '</div>';
}

if (is_array($sw)) {
    ?>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <?php foreach ($sw as $item): ?>
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <div style="width: 100px;"><?php echo $item['name']; ?></div>
                <div style="flex-grow: 1; margin-left: 10px;">
                    <div style="height: 20px; background-color: #f0f0f0; border-radius: 10px; overflow: hidden;">
                        <div style="width: <?php echo $item['count']; ?>%; height: 100%; background-color: #46ba39;"></div>
                    </div>
                </div>
                <div style="margin-left: 10px;"><?php echo $item['count']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php }

die;
?>