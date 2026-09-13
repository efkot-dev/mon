<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function get_api_mikbill($folder, $url, $name) {
    $data = '';
    $file = $folder . $name;
    $updateInterval = 24 * 60 * 60;
    if (!file_exists($file) || (time() - filemtime($file)) > $updateInterval) {
        $data = file_get_contents($url);
        if ($data !== false) {
            file_put_contents($file, $data);
        } else {
            return null;
        }
    } else {
        $data = file_get_contents($file);
    }
    $data_array = json_decode($data, true);
    if ($data_array === null) {
        echo "err: " . json_last_error_msg() . "\n";
    }
    return $data_array;
}
$timer = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
$foldercache = ROOT_DIR.'/export/cache/';
if (isset($confPMon['MIKBILL_ISP_IMPORT']) 
	&& !empty($confPMon['MIKBILL_ISP_IMPORT']) 
	&& $confPMon['MIKBILL_ISP_IMPORT'] == 1) {
$mikbill_api_url = '';
if(isset($config['billingapikey']) && !empty($config['billingapikey'])){
	$mikbill_api_url = $config['billingurl']."/api/index/pmon?key=".$config['billingapikey']."&request=get_user_list&customer_id=";
	$get_house_list = $config['billingurl']."/api/index/pmon?key=".$config['billingapikey']."&cat=module&request=get_house_list";
	$data_get_house_list = get_api_mikbill($foldercache, $get_house_list, 'get_house_list');
	$get_city_list = $config['billingurl']."/api/index/pmon?key=".$config['billingapikey']."&cat=module&request=get_city_list";
	$data_get_city_list = get_api_mikbill($foldercache, $get_city_list, 'get_city_list');
	$get_street_list = $config['billingurl']."/api/index/pmon?key=".$config['billingapikey']."&cat=module&request=get_street_list";
	$data_get_street_list = get_api_mikbill($foldercache, $get_street_list, 'get_street_list');
}
if (isset($mikbill_api_url) && !empty($mikbill_api_url) 
	&& isset($confPMon['MIKBILL_ISP_IMPORT']) 
	&& !empty($confPMon['MIKBILL_ISP_IMPORT']) 
	&& $confPMon['MIKBILL_ISP_IMPORT'] == 1) {
    $sql_onus = $pdo->prepare("SELECT olt, idonu, mac, sn, uid FROM onus WHERE apiget IS NULL OR apiget = '0' LIMIT 30");
    $sql_onus->execute();
    $count = $sql_onus->rowCount();
    if ($count == 0) {
        $pdo->exec("UPDATE onus SET apiget = 0");
    } else {
        while ($row = $sql_onus->fetch(PDO::FETCH_ASSOC)) {
			$onukey = trim(preg_replace('/\s+/', '', !empty($row['mac']) ? $row['mac'] : (!empty($row['sn']) ? $row['sn'] : '')));
            if (empty($onukey)) {
				continue;
			}
			$idonu = $row['idonu'];
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL,$mikbill_api_url.$row['uid']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			$out = curl_exec($curl);
			curl_close($curl);
			$user = json_decode($out, true);
			if (isset($user[$row['uid']]) && !empty($user[$row['uid']])) {
				$data = $user[$row['uid']];
				$full_name = $data['full_name'];
				$login = $data['login'];
				$balance = $data['balance'];
				$house_id = $data['address'][0]['house_id'] ?? $data['address']['house_id'] ?? null;
				$data_house = $data_get_house_list[$house_id];
				$data_street = $data_get_street_list[$data_house['street_id']];
				$data_city = $data_get_city_list[$data_street['city_id']];
				$stmt = $pdo->prepare("SELECT id FROM billing_usr WHERE onuid = :onuid AND uid = :uid AND onumac = :onumac LIMIT 1");
				$stmt->execute([':onuid' => $idonu, ':uid' => $row['uid'], ':onumac' => $onukey]);
				$onus_update = $pdo->prepare("UPDATE onus SET apiget = 1 WHERE idonu = :idonu");
				$onus_update->execute([':idonu' => $idonu]);
				$db_result = $stmt->fetch(PDO::FETCH_ASSOC);
				if (empty($db_result['id']) && $idonu > 0) {
					$sql = "INSERT INTO billing_usr (onumac, onuid, uid, city, street, houser, nomer, pib, added, deviceid, device_type)
							VALUES (:onumac, :onuid, :uid, :city, :street, :houser, :nomer, :pib, :added, :deviceid, :device_type)";
					$stmt = $pdo->prepare($sql);
					$stmt->execute([
						':onumac' => $onukey,
						':onuid' => $idonu,
						':uid' => $row['uid'],
						':city' => $data_city['name'],
						':street' => $data_street['full_name'],
						':houser' => null,
						':nomer' => $data_house['number'],
						':pib' => $full_name,
						':added' => $timer,
						':deviceid' => $row['olt'],
						':device_type' => 'onu'
					]);
				} elseif (!empty($db_result['id'])) {
					$sql = "UPDATE billing_usr SET
						uid = :uid,
						city = :city,
						street = :street,
						houser = :houser,
						nomer = :nomer,
						pib = :pib,
						added = :added,
						deviceid = :deviceid,
						device_type = :device_type
					WHERE id = :id";
					$stmt = $pdo->prepare($sql);
					$stmt->execute([
						':uid' => $row['uid'],
						':city' => $data_city['name'],
						':street' => $data_street['full_name'],
						':houser' => null,
						':nomer' => $data_house['number'],
						':pib' => $full_name,
						':added' => $timer,
						':deviceid' => $row['olt'],
						':device_type' => 'onu',
						':id' => $db_result['id']
					]);
				}
			}
        }
    }
}
	}
?>
