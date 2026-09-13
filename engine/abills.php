<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}

$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';

$abills_api_url = isset($confPMon['ABILLS_API_URL']) && !empty($confPMon['ABILLS_API_URL']) 
    ? $confPMon['ABILLS_API_URL'] 
    : false;
if (isset($abills_api_url) && !empty($abills_api_url) && isset($confPMon['ABILLS_ISP_IMPORT']) && !empty($confPMon['ABILLS_ISP_IMPORT']) && $confPMon['ABILLS_ISP_IMPORT'] == 1) {
    $sql_onus = $pdo->prepare("SELECT olt, idonu, mac, sn, uid FROM onus WHERE apiget IS NULL LIMIT 30");
    $sql_onus->execute();
    $count = $sql_onus->rowCount();
    if ($count == 0) {
        $pdo->exec("UPDATE onus SET apiget = 0");
    } else {
        while ($row = $sql_onus->fetch(PDO::FETCH_ASSOC)) {
            $onukey = trim(preg_replace('/\s+/', '', !empty($row['mac']) ? $row['mac'] : (!empty($row['sn']) ? $row['sn'] : '')));
            $idonu = $row['idonu'];
            $curl = curl_init($abills_api_url .'='. $onukey);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $resp = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $updateStmt = $pdo->prepare("UPDATE onus SET apiget = 1 WHERE idonu = :idonu");
            $updateStmt->execute([':idonu' => $idonu]);
            if ($http_code == 200 && !empty($resp)) {
                $user = json_decode($resp, true);                
                if (json_last_error() === JSON_ERROR_NONE && !empty($user) && !empty($user['uid'])) {
                    $stmt = $pdo->prepare("SELECT id FROM billing_usr WHERE onuid = :onuid AND onumac = :onumac LIMIT 1");
                    $stmt->execute([
                        ':onuid' => $idonu,
                        ':onumac' => $onukey
                    ]);
                    $db_result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (empty($db_result['id']) && $idonu > 0) {
                        $sql = "INSERT INTO billing_usr (onumac, onuid, uid, city, street, houser, nomer, pib, added, deviceid, device_type)
                                VALUES (:onumac, :onuid, :uid, :city, :street, :houser, :nomer, :pib, :added, :deviceid, :device_type)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            ':onumac' => $onukey,
                            ':onuid' => $idonu,
                            ':uid' => $user['uid'],
                            ':city' => $user['addressDistrict'],
                            ':street' => $user['addressStreet'],
                            ':houser' => $user['addressBuild'],
                            ':nomer' => $user['addressFlat'],
                            ':pib' => $user['fio'],
                            ':added' => $timer,
                            ':deviceid' => $row['olt'],
                            ':device_type' => 'onu'
                        ]);
                    }
                }
            }
        }
    }
}
?>
