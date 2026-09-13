<?php
if (!defined('PONMONITOR') && !defined('VIDEO')) {
    die('Hacking attempt!');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'save') {
    $oidid = filter_input(INPUT_POST, 'oidid', FILTER_VALIDATE_INT);
    $monitor = ($_POST['monitor'] === 'yes') ? 'yes' : 'no';
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $snmpro = filter_input(INPUT_POST, 'snmpro', FILTER_SANITIZE_STRING);
    $group = filter_input(INPUT_POST, 'group', FILTER_VALIDATE_INT);
    $location = filter_input(INPUT_POST, 'location', FILTER_VALIDATE_INT);
    $netip_raw = trim($_POST['netip']);
    $netip = '';
    $port = null;
    if (strpos($netip_raw, ':') !== false) {
        [$ip, $port] = explode(':', $netip_raw, 2);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            die('Невірна IP-адреса');
        }
        if (!filter_var($port, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 65535]])) {
            die('Невірний порт');
        }
        $netip = $ip . ':' . $port;
    } else {
        if (!filter_var($netip_raw, FILTER_VALIDATE_IP)) {
            die('Невірна IP-адреса');
        }
        $netip = $netip_raw;
    }

    // Готуємо SQL
    $stmt = $pdo->prepare("
        INSERT INTO surveillance (locations, groups, monitor, oidid, netip, snmpro, place, status)
        VALUES (:locations, :groups, :monitor, :oidid, :netip, :snmpro, :place, :status)
    ");

    $success = $stmt->execute([
        ':locations' => $location,
        ':groups' => $group,
        ':monitor' => $monitor,
        ':oidid' => $oidid,
        ':netip' => $netip,
        ':snmpro' => $snmpro,
        ':place' => $name,
        ':status' => 1
    ]);

    if ($success) {
        echo "Пристрій успішно додано з ID: " . $pdo->lastInsertId();
    } else {
        echo "Помилка збереження пристрою";
    }
} else {
    die('Некоректний запит');
}
?>
