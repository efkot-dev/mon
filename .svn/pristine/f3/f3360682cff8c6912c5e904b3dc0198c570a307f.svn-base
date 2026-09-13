<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function getNewPDO(): PDO {
    $dsn = "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8";
    return new PDO($dsn, DBUSER, DBPASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}
function result_surveillance(array $data, array $switch, PDO $pdo): void {
    $status = 2;
    $snmp_result = false;
    try {
        $session = new SNMP(SNMP::VERSION_2c, $switch['netip'], $switch['snmpro'], 500000, 2);
        if ($data['type'] === 'snmpwalk') {
            $snmp_result = @$session->walk($data['oid']);
        }
        if ($snmp_result !== false) {
            formatedSnmp($snmp_result, $switch, $pdo);
            $status = 1;
        } else {
            setStatusUnavailable($pdo, $switch['id']);
        }
        $session->close();
    } catch (SNMPException $e) {
        setStatusUnavailable($pdo, $switch['id']);
    }
    if ($switch['status'] === 1 && $status === 2) {
        logStatusChange($pdo, $switch['id'], 1, 2); // виключився
    } elseif ($switch['status'] === 2 && $status === 1) {
        logStatusChange($pdo, $switch['id'], 2, 1); // включився
    }
}
function setStatusUnavailable(PDO $pdo, int $id): void {
    $stmt = $pdo->prepare("UPDATE surveillance SET status = '2' WHERE id = :id");
    $stmt->execute([':id' => $id]);
}
function logStatusChange(PDO $pdo, int $id, int $from, int $to): void {
    #$stmt = $pdo->prepare("INSERT INTO surveillance_logs (device_id, old_status, new_status, changed_at) VALUES (:id, :from, :to, NOW())");
    #$stmt->execute([':id' => $id, ':from' => $from, ':to' => $to]);
}
function formatedSnmp(array $snmp, array $switch, PDO $pdo): void {
    $data = ['inf' => null,'model' => null,'firmware' => null,'mac' => null,'uptime' => null,'status' => 1];
    switch ($switch['oidid']) {
        case 1:
            // Реєстратор Hikvision
            foreach ($snmp as $oid => $value) {
				$data['inf'] = 'HikVision';
                if (preg_match('/3\.6\.1\.2\.1\.1\.1\.0$/', $oid)) {
                    $data['model'] = trim(str_replace('STRING: ', '', $value), '" ');
                } elseif (preg_match('/3\.6\.1\.2\.1\.1\.3\.0$/', $oid)) {
                    $data['uptime'] = trim(str_replace('Timeticks: ', '', $value), '" ');
                }
            }
            break;
        case 2:
            // Камера Hikvision
            foreach ($snmp as $oid => $value) {
                if (preg_match('/3\.6\.1\.4\.1\.39165\.1\.1\.0$/', $oid)) {
                    $data['inf'] = 'HikVision';
                    $data['model'] = trim(str_replace('STRING: ', '', $value), '" ');
                } elseif (preg_match('/3\.6\.1\.4\.1\.39165\.1\.3\.0$/', $oid)) {
                    $data['firmware'] = trim(str_replace('STRING: ', '', $value), '" ');
                } elseif (preg_match('/3\.6\.1\.4\.1\.39165\.1\.4\.0$/', $oid)) {
                    $data['mac'] = trim(str_replace('STRING: ', '', $value), '" ');
                } elseif (preg_match('/3\.6\.1\.4\.1\.39165\.1\.20\.0$/', $oid)) {
                    $intVal = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                    $data['status'] = $intVal === 1 ? 1 : 0;
                }
            }
            break;

        case 3:
            // DAHUA
            break;
    }
    $stmt = $pdo->prepare("UPDATE surveillance SET inf = :inf, model = :model, firmware = :firmware, mac = :mac, uptime = :uptime, status = :status, updates = :time WHERE id = :id");
    $stmt->execute([':inf' => $data['inf'],':model' => $data['model'],':firmware' => $data['firmware'],':mac' => $data['mac'],':uptime' => $data['uptime'],':status' => $data['status'],':time' => date('Y-m-d H:i:s'),':id' => $switch['id']]);
}
function get_oid_surveillance(int $id): array|false {
    switch ($id) {
        case 1:
            return ['type' => 'snmpwalk', 'oid' => '1.3.6.1.2.1'];
        case 2:
            return ['type' => 'snmpwalk', 'oid' => '1.3.6.1.4.1.39165.1'];
        case 3:
            return ['type' => 'snmpwalk', 'oid' => ''];
        default:
            return false;
    }
}
?>