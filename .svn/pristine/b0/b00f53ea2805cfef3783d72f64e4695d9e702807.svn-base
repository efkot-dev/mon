<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
define('PMONAPP',true);
function cleanUsername($input) {
    return preg_replace('/[^a-zA-Z0-9@#$!$%_\\-]/', '', (string)$input);
}
function cleanBase64Token($input) {
    $token = trim((string)$input);
    if ($token === '') {
        return '';
    }
    // Allow both standard and URL-safe base64 alphabet.
    return preg_replace('/[^A-Za-z0-9+\\/=\\-_]/', '', $token);
}
function decodeLoginPassword($encoded) {
    $encoded = cleanBase64Token($encoded);
    if ($encoded === '') {
        return false;
    }
    // Support URL-safe base64 (`-`/`_`) as well.
    $normalized = strtr($encoded, '-_', '+/');
    $decoded = base64_decode($normalized, true);
    if ($decoded === false) {
        return false;
    }
    $parts = explode(":", $decoded, 2);
    if (count($parts) == 2) {
        $login = $parts[0];
        $password = $parts[1];
        if ($login !== '' && $password !== '') {
            return ['login' => $login,'password' => $password];
        }
    }
    return false;
}
function cleanText($input) {
    $cleaned = preg_replace('/[^a-zA-Z0-9@#$!%а-щьєі-їйк-юяА-ЩЬЄІ-ЇЙК-ЮЯ\s\-\.,?!\'=]/u', '', $input);
    return preg_replace('/\s+/', ' ', $cleaned);
}
$decodedata = (isset($_REQUEST['m']) ? cleanBase64Token($_REQUEST['m']) : '');
$int = isset($_REQUEST['int']) && is_numeric($_REQUEST['int']) ? (int)$_REQUEST['int'] : null;
$sid = isset($_REQUEST['sid']) && is_numeric($_REQUEST['sid']) ? (int)$_REQUEST['sid'] : null;
$comment = isset($_REQUEST['comment']) ? cleanText($_REQUEST['comment']) : null;
if (isset($_REQUEST['do']) && $_REQUEST['do'] === 'test') {	
	header('Content-Type: text/plain; charset=utf-8');
	echo 'ok';
	exit;
}
if ($decodedata === '') {
	die('Checking access settings');	
}
require ROOT_DIR.'/inc/init.get.php';
$pmon_clock = date('Y-m-d H:i:s');
$do = getInput("do");	
$data = getInput("data");
if(isset($do)){
	$do = totranslit($do);
}else{
	$response = array('success'=>false,'message'=>$lang['app_error_1']);
	detectError($response);
}
$result_api = [];
if(isset($confPMon['PMONAPP']) && !empty($confPMon['PMONAPP']) && $confPMon['PMONAPP'] == 1) {
	if(isset($USER['ip']) && isset($USER['id']) && $USER['id']>0 ){
		$stmt = $pdo->prepare("UPDATE users SET lastactivity = :lastactivity, status = :status, ip = :ip WHERE id = :id");
		$stmt->execute([':lastactivity' => date('Y-m-d H:i:s'),':status'=> 2,':ip'=> $USER['ip'],':id'=> $USER['id']]);
	}	
	switch($do){
		case 'onu_search': 
			$zapros = (isset($_GET["query"]) ? trim(strip_tags(stripcslashes($_GET["query"]))) : null);
			if (isValidContentSql($zapros)) {
				$response = array('success' => false, 'message' => $lang['app_error_1']);
				detectError($response);
			} else {
				$zapros = str_replace(["'", '"', "\\", ";", "--", "%"], "", $zapros);
				$switch_app = APP_getSwitchOlt();
				$location_app = APP_getLocation();
				$orderby = " ORDER BY onus.idonu ASC";
				$limit = " LIMIT 100";
				$like = "%{$zapros}%";
				$get_info = 'onus.idonu, onus.olt, onus.lastrx, onus.changerx, onus.status, onus.inface, onus.type, onus.mac, onus.name, onus.sn, onus.rx, onus.dist, onus.offline, onus.online, onus.vendor, onus.model';
				$sql = "
					SELECT onusdata.*, {$get_info}
					FROM onusdata
					LEFT JOIN onus 
						ON (onus.mac = onusdata.onukey OR onus.sn = onusdata.onukey)
					LEFT JOIN checkaccess a 
						ON CONCAT('dev', onus.olt) = a.types AND a.uid = :uid
					WHERE 
						(onusdata.name LIKE :s1 OR onusdata.tag LIKE :s2 OR onusdata.uid LIKE :s3)
						AND (a.uid IS NOT NULL OR onus.idonu IS NULL)
					$orderby $limit
				";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([':uid' => $USER['id'], ':s1' => $like, ':s2' => $like, ':s3' => $like]);
				$sqlonus = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if (empty($sqlonus)) {
					$get_info = 'idonu, olt, status, inface, type, mac, name, sn, rx, dist, offline, online, vendor, model, lastrx, changerx';
					$sql = "
						SELECT {$get_info}
						FROM onus
						JOIN checkaccess a 
							ON CONCAT('dev', onus.olt) = a.types AND a.uid = :uid
						WHERE (onus.sn LIKE :s1 OR onus.name LIKE :s2 OR onus.mac LIKE :s3)
						$orderby $limit
					";
					$stmt = $pdo->prepare($sql);
					$stmt->execute([':uid' => $USER['id'], ':s1' => $like, ':s2' => $like, ':s3' => $like]);
					$sqlonus = $stmt->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$stmt = $pdo->prepare("SELECT {$get_info} FROM onus JOIN checkaccess a ON CONCAT('dev', onus.olt) = a.types AND a.uid = :uid WHERE (onus.mac LIKE :s1 OR onus.sn LIKE :s2) $orderby $limit");
					$stmt->execute([':uid' => $USER['id'], ':s1' => $like, ':s2' => $like]);
					$sqlonus_from_onus = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$sqlonus = array_merge($sqlonus, $sqlonus_from_onus);
				}
				if (isset($sqlonus) && count($sqlonus) > 0) {
					foreach ($sqlonus as $onu) {
						if (!empty($onu['idonu'])) {
							$onukey = (!empty($onu['mac']) ? $onu['mac'] : (!empty($onu['sn']) ? $onu['sn'] : null));
							$result_api[$onu['idonu']] = [
								'onu' => array_merge($onu, [
									'onukey' => $onukey,'active' => (isset($onu['status']) && $onu['status'] == 1 ? 'online' : 'offline'),'signal' => (isset($onu['lastrx']) && !empty($onu['lastrx']) ? 1 : 0)
								]),
								'olt' => [
									'place' => $switch_app[$onu['olt']]['place'] ?? '','location' => $location_app[$switch_app[$onu['olt']]['location']]['name'] ?? '','netip' => $switch_app[$onu['olt']]['netip'] ?? '',
								],
							];
						}
					}
				}
			}
			break;
		case 'scanner': 
			$zapros = (isset($_GET["query"]) ? trim(strip_tags(stripcslashes($_GET["query"]))) : null);
			if (isValidContentSql($zapros)) {
				$response = array('success' => false, 'message' => $lang['app_error_1']);
				detectError($response);
			} else {
				$zapros = str_replace(["'", '"', "\\", ";", "--", "%"], "", $zapros);
				$zapros = preg_replace('/[^A-Fa-f0-9]/', '', $zapros);	
				if(isset($zapros) && !empty($zapros)){
					if (strlen($zapros) === 12) {
						$macRaw = strtoupper($zapros);
						$zapros = app_format_mac($macRaw, 1);
					}
					$switch_app = APP_getSwitchOlt();
					$location_app = APP_getLocation();
					$get_info = 'onus.idonu, onus.olt, onus.lastrx, onus.changerx, onus.status, onus.inface, onus.type, onus.mac, onus.name, onus.sn, onus.rx, onus.dist, onus.offline, onus.online, onus.vendor, onus.model';
					$sql_sn = "SELECT {$get_info} FROM onus JOIN checkaccess a ON CONCAT('dev', onus.olt) = a.types AND a.uid = :uid WHERE onus.sn = :zapros LIMIT 1";
					$stmt = $pdo->prepare($sql_sn);
					$stmt->execute([':uid' => $USER['id'], ':zapros' => $zapros]);
					$onu = $stmt->fetch(PDO::FETCH_ASSOC);
					if (!$onu) {
						$sql_mac = "SELECT {$get_info} FROM onus JOIN checkaccess a ON CONCAT('dev', onus.olt) = a.types AND a.uid = :uid WHERE onus.mac = :zapros LIMIT 1";
						$stmt = $pdo->prepare($sql_mac);
						$stmt->execute([':uid' => $USER['id'], ':zapros' => $zapros]);
						$onu = $stmt->fetch(PDO::FETCH_ASSOC);
					}
					if (!$onu) {
						$sql_name = "SELECT {$get_info} FROM onus JOIN checkaccess a ON CONCAT('dev', onus.olt) = a.types AND a.uid = :uid WHERE onus.name LIKE :search LIMIT 1";
						$like = "%{$zapros}%";
						$stmt = $pdo->prepare($sql_name);
						$stmt->execute([':uid' => $USER['id'], ':search' => $like]);
						$onu = $stmt->fetch(PDO::FETCH_ASSOC);
					}
					if (!empty($onu['idonu'])) {
						$onukey = $onu['mac'] ?: $onu['sn'] ?? null;
						$result_api[$onu['idonu']] = [
							'onu' => array_merge($onu, [
								'onukey' => $onukey,'active' => $onu['status'] == 1 ? 'online' : 'offline','signal' => !empty($onu['lastrx']) ? 1 : 0
							]),
							'olt' => [
								'place' => $switch_app[$onu['olt']]['place'] ?? '','location' => $location_app[$switch_app[$onu['olt']]['location']]['name'] ?? '','netip' => $switch_app[$onu['olt']]['netip'] ?? '',
							],
						];
					}
				}
			}
			break;	
		case 'unique_signals':
			$unique_signals = [];
			$idonu = intval(getInput("idonu"));
			if ($idonu > 0) {
				$sql = "SELECT idonu, olt, rx, mac, sn, inface, type, status FROM onus WHERE idonu = :idonu";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([':idonu' => $idonu]);
				$getonu = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!empty($getonu) && isset($getonu['idonu'])) {
					$sql_rx = "SELECT `signal`, `datetime`
						FROM historysignal
						WHERE onu = :idonu
						AND datetime >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
						ORDER BY datetime DESC";
					$stmt_rx = $pdo->prepare($sql_rx);
					$stmt_rx->execute([':idonu' => $getonu['idonu']]);
					$all_signals = $stmt_rx->fetchAll(PDO::FETCH_ASSOC);
					$seen = [];
					foreach ($all_signals as $s) {
						$key = $s['signal'];
						if (!isset($seen[$key])) {
							$seen[$key] = true;
							$unique_signals[] = ['rx' => $s['signal'],'time' => $s['datetime']];
						}
					}
					$result_api['history'] = $unique_signals;
					$result_api['ont'] = $getonu;
				}
			}
			break;			
		case 'history':		
			$history_signal = [];		
			$idonu = intval(getInput("idonu"));
			if ($idonu > 0) {
				$sql = "SELECT idonu, olt, rx, mac, sn, inface, type, status FROM onus WHERE idonu = :idonu";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([':idonu' => $idonu]);
				$getonu = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!empty($getonu) && isset($getonu['idonu'])) {
					$sql_rx = "SELECT 
						curr.id,
						curr.device,
						curr.onu,
						curr.signal,
						curr.datetime,
						prev.signal AS prev_signal,
						ABS(curr.signal - prev.signal) AS diff
					FROM historysignal curr
					JOIN historysignal prev 
						ON curr.onu = prev.onu
						AND prev.datetime = (
							SELECT MAX(h.datetime)
							FROM historysignal h
							WHERE h.onu = curr.onu AND h.datetime < curr.datetime
						)
					WHERE 
						curr.onu = :idonu
						AND MONTH(curr.datetime) = MONTH(CURDATE())
						AND YEAR(curr.datetime) = YEAR(CURDATE())
						AND ABS(curr.signal - prev.signal) > 1
					ORDER BY curr.datetime DESC";

					$stmt_rx = $pdo->prepare($sql_rx);
					$stmt_rx->execute([':idonu' => $getonu['idonu']]);
					$historysignal = $stmt_rx->fetchAll(PDO::FETCH_ASSOC);
					if (!empty($historysignal)) {
						foreach ($historysignal as $h) {
							$history_signal[] = [
								'rx' => $h['signal'],'time' => $h['datetime'],'prev_rx' => $h['prev_signal'],'diff' => $h['diff']
							];
						}
					}
					$result_api['history'] = $history_signal;
					$result_api['ont'] = $getonu;
				}
			}
			break;
		case 'onu_los': 
			$oltid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			$baseWhere = "o.status = '2' AND o.offline >= CURDATE() AND (o.reason IN ('err6','err8'))";
			$sql = "SELECT o.idonu, o.olt, o.mac, o.sn, o.name, o.type, o.inface, o.dist, o.offline, o.reason, o.rx, o.status, o.lastrx, s.place, s.model, s.inf, s.netip FROM checkaccess a JOIN onus o ON a.types = CONCAT('dev', o.olt) JOIN switch s ON s.id = o.olt WHERE a.uid = :uid AND $baseWhere ORDER BY o.olt ASC, o.offline DESC LIMIT 2000";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':uid', $USER['id'], PDO::PARAM_INT);
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $r) {
				$oltId = (int)$r['olt'];
				if (!isset($result['data'][$oltId])) {
					$result['data'][$oltId] = [
						'olt' => ['id' => $oltId,'place' => (string)$r['place'],'model' => trim(($r['inf'] ?? '').' '.($r['model'] ?? '')),'netip' => (string)$r['netip'],],
						'onus' => []
					];
				}
				$onukey = !empty($r['mac']) ? $r['mac'] : (!empty($r['sn']) ? $r['sn'] : null);
				$result['data'][$oltId]['onus'][] = [
					'idonu' => (int)$r['idonu'],
					'key' => $onukey,
					'name' => (string)($r['name'] ?? ''),
					'iface' => strtoupper((string)($r['type'] ?? '')).' '.(string)($r['inface'] ?? ''),
					'dist' => is_null($r['dist']) ? null : (float)$r['dist'],
					'offline' => (string)$r['offline'],
					'reason' => (string)($r['reason'] ?? ''),
					'rx' => (string)($r['rx'] ?? ''),
					'status'  => (int)$r['status'],
					'signal'  => !empty($r['lastrx']) ? 1 : 0
				];
			}
			if($rows){
				$result_api['result'] = $result;
			} else {
				$result_api['result'] = [];
			}
			break;
		case 'stickers':
			$switch_app = APP_getSwitchOlt();
			$location_app = APP_getLocation();
			$sql = "SELECT o.* FROM checkaccess a JOIN onus o ON CONCAT('dev', o.olt) = a.types WHERE a.uid = :uid AND o.inspector = '2'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':uid' => $USER['id']]);
			$sqlonus = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($sqlonus)) {
				foreach ($sqlonus as $onu) {
					if (!empty($onu['idonu'])) {
						$onukey = !empty($onu['mac']) ? $onu['mac'] : (!empty($onu['sn']) ? $onu['sn'] : null);
						$result_api[$onu['idonu']] = [
							'onu' => array_merge($onu, [
								'onukey' => $onukey,'active' => (isset($onu['status']) && $onu['status'] == 1) ? 'online' : 'offline','signal' => (!empty($onu['lastrx'])) ? 1 : 0
							]),
							'olt' => ['place' => $switch_app[$onu['olt']]['place'] ?? '','location' => $location_app[$switch_app[$onu['olt']]['location']]['name'] ?? '','netip'    => $switch_app[$onu['olt']]['netip'] ?? '']
						];
					}
				}
			}		
		break;	
		case 'editpillar_get':
			$pillarid = (int)getInput('pillarid');
			$out = ['pillar'=>[], 'tp_list'=>[], 'obl_list'=>[], 'isp_list'=>[], 'line_list'=>[]];
			if ($pillarid > 0) {
				$pillar = $db->Fast('oblenergo_pillar','*',['id'=>$pillarid]);
				if ($pillar) {
					$out['pillar'] = [
						'id' => $pillar['id'],
						'nomer_pillar' => $pillar['nomer_pillar'],
						'lan' => $pillar['lan'],
						'lon' => $pillar['lon'],
						'tpid' => $pillar['tpid'],
						'oblenergoid' => $pillar['oblenergoid']
					];
					$tplist = $db->SimpleWhile("SELECT id, subname, nomer_tp FROM oblenergo_tp WHERE oblenergoid = ".(int)$pillar['oblenergoid']);
					if ($tplist) foreach($tplist as $tp) {
						$out['tp_list'][] = ['id'=>$tp['id'], 'title'=>$tp['subname'].' '.$tp['nomer_tp']];
					}
					$obllist = $db->SimpleWhile("SELECT id, name FROM oblenergo");
					if ($obllist) foreach($obllist as $obl) {
						$out['obl_list'][] = ['id'=>$obl['id'], 'title'=>$obl['name']];
					}
					$isp_all = $db->SimpleWhile("SELECT id, name FROM oblenergo_concurent");
					$isp_sel = $db->SimpleWhile("SELECT providerid FROM oblenergo_subprovider WHERE pillarid=".$pillarid);
					$isp_sel_ids = [];
					if ($isp_sel) foreach($isp_sel as $r) $isp_sel_ids[$r['providerid']] = true;
					if ($isp_all) foreach($isp_all as $isp) {
						$out['isp_list'][] = ['id'=>$isp['id'], 'name'=>$isp['name'], 'checked'=>isset($isp_sel_ids[$isp['id']])];
					}
					$line_all = $db->SimpleWhile("SELECT nomer_liniy as id FROM oblenergo_countliniy WHERE tpid = ".(int)$pillar['tpid']);
					$line_sel = $db->SimpleWhile("SELECT lineid FROM oblenergo_pillarliniy WHERE pillarid=".$pillarid." AND tpid=".(int)$pillar['tpid']);
					$line_sel_ids = [];
					if ($line_sel) foreach($line_sel as $r) $line_sel_ids[$r['lineid']] = true;
					if ($line_all) foreach($line_all as $ln) {
						$out['line_list'][] = ['id'=>$ln['id'], 'name'=>'лінія '.$ln['id'], 'checked'=>isset($line_sel_ids[$ln['id']])];
					}
				}
			}
			$result_api = ['data' => $out];
			break;
		case 'editpillar_save':
			$pillarid = (int)getInput('pillarid');
			$tpid_new = (int)getInput('tpid');
			$oblid_new = (int)getInput('oblenergoid');
			$numberpillar = getInput('numberpillar');
			$geoPillar = filter_input(INPUT_GET, 'geopillar', FILTER_SANITIZE_STRING);
			if ($pillarid <= 0) { $result_api = ['ok'=>false,'error'=>'bad pillarid']; break; }
			$upd = [
				'nomer_pillar' => $numberpillar,'tpid' => $tpid_new,'oblenergoid'  => $oblid_new
			];
			if (!empty($geoPillar)) {
				$coordinates = explode(',', $geoPillar);
				if (count($coordinates) === 2) {
					$lan = substr(trim($coordinates[0]), 0, 9);
					$lon = substr(trim($coordinates[1]), 0, 9);
					$upd['lan'] = $lan;
					$upd['lon'] = $lon;
				}
			}
			$db->SQLupdate('oblenergo_pillar', $upd, ['id'=>$pillarid]);
			$tp = $db->Fast('oblenergo_tp', '*', ['id'=>$tpid_new]);
			$locationid = $tp ? (int)$tp['locationid'] : 0;
			$selectedISP = [];
			$selectedLine = [];
			foreach ($_GET as $key => $value) {
				$cleanKey = filter_var($key, FILTER_SANITIZE_STRING);
				$cleanValue = filter_var($value, FILTER_SANITIZE_STRING);
				if (strpos($cleanKey, 'selectedISP_') === 0) {
					$selectedISP[(int)str_replace('selectedISP_', '', $cleanKey)] = $cleanValue;
				} elseif (strpos($cleanKey, 'selectedLine_') === 0) {
					$selectedLine[(int)str_replace('selectedLine_', '', $cleanKey)] = $cleanValue;
				}
			}
			$db->query("DELETE FROM oblenergo_pillarliniy WHERE pillarid = {$pillarid}");
			if (!empty($selectedLine)) {
				foreach ($selectedLine as $lineid => $name) {
					$db->SQLinsert('oblenergo_pillarliniy', [
						'lineid' => (int)$lineid,'pillarid' => $pillarid,'locationid' => $locationid,'tpid' => $tpid_new,'oblenergoid'=> $oblid_new
					]);
				}
			}
			$db->query("DELETE FROM oblenergo_subprovider WHERE pillarid = {$pillarid}");
			if (!empty($selectedISP)) {
				foreach ($selectedISP as $providerid => $name) {
					$db->SQLinsert('oblenergo_subprovider', [
						'providerid' => (int)$providerid,'pillarid' => $pillarid,'locationid' => $locationid,'tpid' => $tpid_new,'oblenergoid'=> $oblid_new
					]);
				}
			}
			$db->SQLupdate('oblenergo_pillar', ['count_concurrent'=>count($selectedISP)], ['id'=>$pillarid]);
			$result_api = ['ok'=>true];
			break;
		case 'oblenergo':	
			$new_masiv = [];
			$i = 1;			
			$sql_oblenergo = "SELECT * FROM `oblenergo`";
			$sql_obl = $db->SimpleWhile($sql_oblenergo);
			if (isset($sql_obl) && count($sql_obl) > 0) {
				foreach ($sql_obl as $obl) {
					$count_tp = $db->Simple("SELECT count(id) as count FROM oblenergo_tp where oblenergoid = ".$obl['id']);
					$count_pl = $db->Simple("SELECT count(id) as count FROM oblenergo_pillar where oblenergoid = ".$obl['id']);
					$new_masiv[$i] = [
						'id' => $obl['id'],'name' => $obl['name'],'counttp' => $count_tp['count'],'countpillar' => $count_pl['count']
					];
					$i++;
				}
				$result_api['result'] = $new_masiv;
			} else {
				$result_api['result'] = [];
			}
		break;		
		case 'savepillar':		
			$tpid = getInput("tpid");
			$numberpillar = getInput("numberpillar");
			$typepillar = getInput("typepillar");
			$geoPillar = filter_input(INPUT_GET, 'geopillar', FILTER_SANITIZE_STRING);
			$selectedISP = [];
			$selectedLine = [];
			foreach ($_GET as $key => $value) {
				$cleanKey = filter_var($key, FILTER_SANITIZE_STRING);
				$cleanValue = filter_var($value, FILTER_SANITIZE_STRING);				
				if (strpos($cleanKey, 'selectedISP_') === 0) {
					$selectedISP[str_replace('selectedISP_', '', $cleanKey)] = $cleanValue;
				} elseif (strpos($cleanKey, 'selectedLine_') === 0) {
					$selectedLine[str_replace('selectedLine_', '', $cleanKey)] = $cleanValue;
				}
			}	
			$sqlinsert['nomer_pillar'] = $numberpillar;
			$sqlinsert['type_pillar'] = (isset($typepillar) ? $typepillar : 1 );	
			$sqlinsert['tpid'] = $tpid;	
			$data_oblenergo_tp = $db->Fast('oblenergo_tp','*',['id'=>$tpid]);
			$sqlinsert['oblenergoid'] = $data_oblenergo_tp['oblenergoid'];
			if(isset($geoPillar)){
				$coordinates = explode(',', $geoPillar);
			}
			$sqlinsert['added'] = $pmon_clock;
			if (count($coordinates) === 2) {
				$lan = trim($coordinates[0]); 
				$lon = trim($coordinates[1]);
				$sqlinsert['lan'] = substr($lan, 0, 9);
				$sqlinsert['lon'] = substr($lon, 0, 9);
			}	
			$db->SQLinsert('oblenergo_pillar',$sqlinsert);	
			$pillarid = $db->getInsertId();
			if(isset($selectedLine) && count($selectedLine)>0){
				foreach($selectedLine as $keyline => $valueisp) {
					$db->SQLinsert('oblenergo_pillarliniy',[
						'lineid'=>(int)$keyline,'pillarid'=>$pillarid,'locationid'=>$data_oblenergo_tp['locationid'],'tpid'=>$sqlinsert['tpid'],'oblenergoid'=>$sqlinsert['oblenergoid']
					]);
				}	
			}
			if(isset($selectedISP) && count($selectedISP)>0){
				foreach($selectedISP as $keyisp => $valueisp) {
					$db->SQLinsert('oblenergo_subprovider',[
						'providerid'=>(int)$keyisp,'pillarid'=>$pillarid,'locationid'=>$data_oblenergo_tp['locationid'],'tpid'=>$sqlinsert['tpid'],'oblenergoid'=>$sqlinsert['oblenergoid']
					]);
				}
			}
			$db->SQLupdate('oblenergo_pillar',['count_concurrent'=>count($selectedISP)],['id'=>$pillarid]);
		break;		
		case 'viewpillar':		
			$providerlist = [];
			$sql_obl_con = $db->SimpleWhile("SELECT id, name, locationid from oblenergo_concurent");
			if(isset($sql_obl_con) && count($sql_obl_con)>0) {
				foreach($sql_obl_con as $oblid => $concurent) {
					$providerlist[$concurent['id']] = array(
						'id' => $concurent['id'],'name' => $concurent['name'],'locationid' => $concurent['locationid']
					);
				}
			}
			$tpid = getInput("tpid");
			$masiv = array();
			if(isset($tpid) && $tpid>0){
				$sql_tp = $db->Fast('oblenergo_tp','*',['id'=>$tpid]);
				$i = 1;
				$sql_obl_pillar = $db->SimpleWhile("SELECT * from oblenergo_pillar WHERE tpid = '{$sql_tp['id']}'");
				if(isset($sql_obl_pillar) && count($sql_obl_pillar)>0) {
					foreach ($sql_obl_pillar as $pid => $pillar) {
						$liner = '';
						$obliner = $db->SimpleWhile("SELECT lineid from oblenergo_pillarliniy WHERE pillarid = '".$pillar['id']."' and tpid = ".$sql_tp['id']);
						foreach($obliner as $lin){
							$liner .= 'л-'.$lin['lineid'].', ';
						}
						$liner = rtrim($liner, ', ');
						$provider = '';
						$obprov = $db->SimpleWhile("SELECT providerid from oblenergo_subprovider WHERE pillarid = ".$pillar['id']);
						foreach($obprov as $prov){
							$provider .= ''.$providerlist[$prov['providerid']]['name'].', ';
						}
						$provider = rtrim($provider, ', ');
						$masiv['pl'][$i] = array(
							'id' => $pillar['id'],
							'tid' => $sql_tp['tpid'],
							'oblid' => $sql_tp['oblenergoid'],
							'count_concurrent'=>$provider,
							'type'=>$pillar['type_pillar'],
							'descr'=>$pillar['descr'],
							'line'=>$liner,
							'gps'=>(!empty($pillar['lan']) ? 'yes' : 'no'),
							'nomer'=>(isset($pillar['nomer_pillar'])?$pillar['nomer_pillar']:'wtf')
						);
						$i++;
					}
				}
				$masiv['tp'] = array(
					'id'=>$sql_tp['id'],'line' => isset($sql_tp['countliniy']) ? $sql_tp['countliniy'] : 0,'pillar' => $i,'nomer'=>$sql_tp['subname'].' '.$sql_tp['nomer_tp']
				);
			}	
			$result_api = $masiv;			
		break;		
		case 'addpillar':			
			$tpid = getInput("tpid");
			$masiv = array();
			if(isset($tpid) && $tpid>0){				
				$sql_obl_isp = $db->SimpleWhile("SELECT * from oblenergo_concurent");
				if(isset($sql_obl_isp) && count($sql_obl_isp)>0) {
					foreach ($sql_obl_isp as $ispid => $isp) {
						$masiv['isp'][$isp['id']] = array('id'=>$isp['id'],'name'=>$isp['name']);
					}
				}
				$sql_obl_line = $db->SimpleWhile("SELECT * from oblenergo_countliniy where tpid = ".$tpid);
				if(isset($sql_obl_line) && count($sql_obl_line)>0) {
					foreach ($sql_obl_line as $lineid => $line) {
						$masiv['line'][$line['nomer_liniy']] = array('id'=>$line['nomer_liniy'],'name'=>'лінія '.$line['nomer_liniy']);
					}
				}
			}
			$result_api = $masiv;
		break;		
		case 'oblenergotp':			
			$oblid = getInput("oblid");
			$new_masiv = [];
			$tp_array = [];
			$i = 1;
			if(isset($oblid) && $oblid>0){
				$tpview = $db->Fast('oblenergo','*',['id'=>$oblid]);
				if(!empty($tpview['id'])){
					$where['oblenergoid'] = $tpview['id'];
					$sql_tp = $db->Multi('oblenergo_tp','*',$where);
					if(isset($sql_tp) && count($sql_tp)>0){
						foreach($sql_tp as $tp){							
							$count_tp = $db->Simple("SELECT count(id) as count FROM oblenergo_pillar where tpid = ".$tp['id']);
							$count_sym = $db->Simple("SELECT providerid, COUNT(*) as count FROM oblenergo_subprovider WHERE tpid = '{$tp['id']}' AND providerid > 1 GROUP BY providerid ORDER BY count DESC LIMIT 1;");								
							$count_sym_ = $db->Simple("SELECT COUNT(DISTINCT providerid) AS count, COUNT(DISTINCT tpid) AS unique_tpid_count FROM oblenergo_subprovider WHERE tpid = '{$tp['id']}';");								
							$new_masiv['tp'][$i] = [
								'id' => $tp['id'],
								'name' => isset($tp['name_tp']) ? $tp['subname'].' '.$tp['name_tp'] : 'n/a','location' => $tp['locationname'],'nomer' => $tp['subname'].' '.$tp['nomer_tp'],'oblid' => $oblid,
								'countliniy' => isset($tp['countliniy']) ? $tp['countliniy'] : 0,
								'countpillar' => isset($count_tp['count']) ? $count_tp['count'] : 0,
								'countisp' => isset($count_sym_['count']) ? $count_sym_['count'] : 0,
								'countsympillar' => isset($count_sym['count']) ? $count_sym['count'] : 0,
							];
							$i++;
						}						
					}							
					$new_masiv['ob'] = array(
						'id'=>$tpview['id'],'name'=>$tpview['name'],
					);
				}
				$result_api = $new_masiv;						
			}else{
				$result_api = [];	
			}	
		break;	
		case 'getponunit':	
			$unit_res = [];		
			$stmt = $pdo->query("SELECT u.id, u.name, COALESCE(SUM(t.onu_online),  0) AS count_online, COALESCE(SUM(t.onu_offline), 0) AS count_offline, COUNT(t.id) AS count_tree FROM ponunit AS u LEFT JOIN pontree AS t ON t.unit_id = u.id GROUP BY u.id, u.name ORDER BY u.name ASC");
			$units = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
			if (!empty($units)) {
				foreach ($units as $unit) {
					$uid = $unit['id'];
					$uname = $unit['name'];
					$online = $unit['count_online'] ?? 0;
					$offline = $unit['count_offline'] ?? 0;
					$ponCount = $unit['count_tree'] ?? 0;
					$allPon = $online + $offline;
					$unit_res[] = array('id' => $unit['id'],'name' => $unit['name'],'onu_all' => $allPon,'onu_online' => $online,'onu_offline' => $offline,'pon_element' => $ponCount,);
				}
				$result_api = $unit_res;	
			}else{
				$result_api = [];	
			}
			break;	
		case 'getpontrees':
		  $unitId = (int)getInput('unit_id', 0);
		  if ($unitId <= 0) { echo json_encode(['app'=>true,'data'=>[]]); exit; }
		  $stmt = $pdo->prepare("SELECT id, name, unit_id FROM pontree WHERE unit_id = :uid ORDER BY name ASC");
		  $stmt->execute([':uid' => $unitId]);
		  $trees = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  if (!$trees) {
			echo json_encode(['app'=>true,'data'=>[]], JSON_UNESCAPED_UNICODE);
			exit;
		  }
		  $ids = array_map(static fn($t) => (int)$t['id'], $trees);
		  $ph  = implode(',', array_fill(0, count($ids), '?'));
		  $sqlAgg = "
			SELECT
			  od.pontree AS tree_id,
			  COUNT(DISTINCT od.ponelement) AS boxes,
			  COUNT(u.idonu) AS onus,
			  SUM(CASE WHEN u.status = 1 THEN 1 ELSE 0 END) AS online,
			  SUM(CASE WHEN u.status <> 1 OR u.status IS NULL THEN 1 ELSE 0 END) AS offline
			FROM onusdata od
			LEFT JOIN (
			  SELECT mac AS onukey, idonu, status FROM onus WHERE mac IS NOT NULL
			  UNION ALL
			  SELECT sn  AS onukey, idonu, status FROM onus WHERE sn  IS NOT NULL
			) AS u
			  ON u.onukey = od.onukey
			WHERE od.pontree IN ($ph)
			GROUP BY od.pontree
		  ";
		  $stAgg = $pdo->prepare($sqlAgg);
		  $stAgg->execute($ids);
		  $map = [];
		  foreach ($stAgg->fetchAll(PDO::FETCH_ASSOC) as $a) {
			$tid = (int)$a['tree_id'];
			$map[$tid] = ['boxes' => (int)$a['boxes'],'onus' => (int)$a['onus'],'online' => (int)$a['online'],'offline' => (int)$a['offline']];
		  }
		  $out = [];
		  foreach ($trees as $t) {
			$tid = (int)$t['id'];
			$out[] = ['id' => $tid,'name' => $t['name'],'unit_id' => isset($t['unit_id']) ? (int)$t['unit_id'] : null,'stats' => $map[$tid] ?? ['boxes'=>0,'onus'=>0,'online'=>0,'offline'=>0],];
		  }
		  echo json_encode(['app'=>true,'data'=>$out], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
		  exit;
		case 'pontreeview':
		  $treeId = (int)getInput('tree', 0);
		  if ($treeId<=0) die('tree required');
		  $stmt = $pdo->prepare("SELECT id, name, unit_id FROM pontree WHERE id=:id LIMIT 1");
		  $stmt->execute([':id'=>$treeId]);
		  $tree = $stmt->fetch(PDO::FETCH_ASSOC);
		  if (!$tree) die('pontree not found');
		  $boxes = $pdo->prepare("SELECT id, name, types, status, lan, lon FROM ponelement	WHERE tree=:t ORDER BY name ASC");
		  $boxes->execute([':t'=>$treeId]);
		  $rowsBoxes = $boxes->fetchAll(PDO::FETCH_ASSOC);
		  if (!$rowsBoxes) ok(['tree'=>$tree, 'boxes'=>[]]);
		  $sqlAgg = "
			SELECT
			  od.ponelement AS box_id,
			  COUNT(o.idonu) AS cnt,
			  SUM(CASE WHEN o.status=1 THEN 1 ELSE 0 END) AS online_cnt,
			  SUM(CASE WHEN o.status<>1 OR o.status IS NULL THEN 1 ELSE 0 END) AS offline_cnt,
			  AVG(NULLIF(o.rx,0)) AS rx_avg,
			  MIN(NULLIF(o.rx,0)) AS rx_min,
			  MAX(NULLIF(o.rx,0)) AS rx_max
			FROM onusdata od
			LEFT JOIN onus o
				   ON (o.mac = od.onukey OR o.sn = od.onukey)
			WHERE od.pontree = :t
			GROUP BY od.ponelement
		  ";
		  $agg = $pdo->prepare($sqlAgg);
		  $agg->execute([':t'=>$treeId]);
		  $mapAgg = [];
		  foreach($agg->fetchAll(PDO::FETCH_ASSOC) as $a){
			$mapAgg[(int)$a['box_id']] = [
			  'count' => (int)$a['cnt'],
			  'online' => (int)$a['online_cnt'],
			  'offline' => (int)$a['offline_cnt'],
			  'rx_avg' => isset($a['rx_avg'])? round((float)$a['rx_avg'],1) : null,
			  'rx_min' => isset($a['rx_min'])? (int)$a['rx_min'] : null,
			  'rx_max' => isset($a['rx_max'])? (int)$a['rx_max'] : null,
			];
		  }
		  $includeOnu = (int)getInput('include_onu', 1) === 1;
		  $limitOnu = max(0, (int)getInput('limit_onu', 200));
		  $statusF = getInput('status','all');
		  $boxesOut = [];
		  foreach ($rowsBoxes as $b) {
			$box = [
			  'id' => (int)$b['id'],
			  'name' => $b['name'],
			  'types' => (int)$b['types'],
			  'status' => (int)$b['status'],
			  'lan' => $b['lan']!==null? (float)$b['lan'] : null,
			  'lon' => $b['lon']!==null? (float)$b['lon'] : null,
			  'stats' => $mapAgg[(int)$b['id']] ?? ['count'=>0,'online'=>0,'offline'=>0,'rx_avg'=>null,'rx_min'=>null,'rx_max'=>null],
			];
			if ($includeOnu) {
			  $whereStatus = '';
			  if ($statusF==='in')  $whereStatus = ' AND COALESCE(o.status,0)=1 ';
			  if ($statusF==='off') $whereStatus = ' AND (COALESCE(o.status,0)<>1) ';
			  $sqlOnu = "SELECT
				  o.idonu, od.onukey, od.tag as onu_tag, o.name as onu_name, o.status, o.inface, o.rx, o.rxolt, o.dist, o.reason, o.online, o.offline
				FROM onusdata od
				LEFT JOIN onus o
					   ON (o.mac = od.onukey OR o.sn = od.onukey)
				WHERE od.pontree = :t AND od.ponelement = :pe
				$whereStatus
				ORDER BY (COALESCE(o.status,0)=1) DESC, od.onukey ASC
				".($limitOnu>0? "LIMIT $limitOnu" : "")." ";
			  $stOnu = $pdo->prepare($sqlOnu);
			  $stOnu->execute([':t'=>$treeId, ':pe'=>(int)$b['id']]);
			  $box['onus'] = [];
			  foreach ($stOnu->fetchAll(PDO::FETCH_ASSOC) as $r){
				if (empty($r['idonu'])) continue;
				$box['onus'][] = [
				  'idonu' => (int)$r['idonu'],
				  'onu_name' => $r['onu_name'],
				  'inface' => $r['inface'],
				  'onu_tag' => $r['onu_tag'],
				  'onukey' => $r['onukey'],
				  'status' => isset($r['status'])? (int)$r['status'] : 0,
				  'rx' => isset($r['rx'])? (int)$r['rx'] : null,
				  'rxolt' => isset($r['rxolt'])? (int)$r['rxolt'] : null,
				  'dist' => isset($r['dist'])? (int)$r['dist'] : null,
				  'reason' => $r['status'] == 2 ? $lang[$r['reason']] : "",
				  'online' => isset($r['online'])? (int)$r['online'] : 0,
				  'offline' => isset($r['offline'])? (int)$r['offline'] : 0,
				];
			  }
			}
			$boxesOut[] = $box;
		  }
		  $result_api = $boxesOut;
		  break;		
		case 'onu_mapper':
			$array_switch = [];		
			$locationid = getInput("locationid");
			if(isset($locationid) && $locationid>0){
				$location = $db->Fast('location','*',['id'=>$locationid]);				
				$sqlountdev = $db->SimpleWhile("SELECT id, name, location, place, inf, model FROM switch WHERE `location` = ".$location['id']." AND device = 'olt'");
				if(isset($sqlountdev) && count($sqlountdev)>0){
					foreach($sqlountdev as $sw){
						if(!empty($sw['location'])){
							$array_count[$sw['location']][$sw['id']] = $sw['id'];
						}
						$array_switch[$sw['id']]['place'] = $sw['place'];
						$array_switch[$sw['id']]['model'] = $sw['inf'].' '.$sw['model'];
					}	
				}
			}	
			$select_onu = 'idonu,olt,portolt,keyonu,zte_idport,status,inface,type,mac,name,descr,sn,rx,reason,dist,offline,online';
			if(!empty($array_count[$locationid]) && is_array($array_count)){
				$olt_array = array_unique(array_values($array_count[$locationid]));
				if(count($olt_array) == 1){
					$sqlmaponu = "SELECT {$select_onu} FROM onus WHERE `olt` = ".$olt_array[0];
				} else {
					$olt_values = implode(',', $olt_array);
					$sqlmaponu = "SELECT {$select_onu} FROM onus WHERE `olt` IN ($olt_values)";
				}
			}
			$new_masiv = [];
			if(isset($locationid) && !empty($location['id'])){
				$getmaponu = $db->SimpleWhile($sqlmaponu);
				if (isset($getmaponu) && count($getmaponu) > 0) {
					$i = 1;
					foreach ($getmaponu as $onu) {
						$onukey = (!empty($onu['mac']) ? $onu['mac'] : (!empty($onu['sn']) ? $onu['sn'] : null));
						$datatemponu = getFastOnusData($onukey);
						if (isset($onukey) && !empty($datatemponu['lan']) && !empty($datatemponu['lon'])) {
							$latitude = $datatemponu['lan'];
							$longitude = $datatemponu['lon'];
							$new_masiv[$i] = array(
								'id' => $onu['idonu'],'name' => (!empty($onu['name']) ? $onu['name']:'n/a'),
								'inface' => $onu['inface'],
								'olt' => $array_switch[$onu['olt']]['place'],
								'status' => $onu['status'],
								'mac' => $onukey,'lan' => $latitude,'lon' => $longitude,
								'color' => ($onu['status'] == 1 ? getColorBySignal($onu['rx']) : '#222'),
								'rx' => (!empty($onu['rx']) ? $onu['rx'] : 'n/a')
							);
							$i++;
						}
					}
					$result_api['location'] = array('lan'=>$location['lan'],'lon'=>$location['lon'],'name'=>$location['name']);	
					$result_api['onu'] = $new_masiv;	
				}else{
					$result_api = [];
				}
			}
		break;		
		case 'validator': 
			$status = false;    
			$get_usr = $db->Simple("SELECT * FROM users_odometr WHERE id = '{$sid}' LIMIT 1");
			if (!empty($get_usr['start_odometr'])) {
				$start_odometr = $get_usr['start_odometr'];
				if ($int !== null) {
					if ($int >= $start_odometr) {
						$valid = $int - $start_odometr;
						if ($valid <= 1000) {
							$status = true;
						} else {
							$status = false;
						}
					} else {
						$status = false;
					}
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}
			$result_api = array(
				'status' => $status,'message' => $status ? 'Validation successful' : 'Validation failed'
			);	
		break;		
		case 'alarmping3': 		
			if (isset($confPMon['SECURITY_PING3']) && !empty($confPMon['SECURITY_PING3'])) {
			$sql_list = $db->SimpleWhile("SELECT s.id, s.name, s.status,
					(
						SELECT h.event_time
						FROM alarm_ping3_history h
						WHERE h.alarm_id = s.id
						ORDER BY h.id DESC
						LIMIT 1
					) AS last_event_time
				FROM checkaccess a
				JOIN alarm_ping3 s ON CONCAT('alarm_ping3_', s.id) = a.types
				WHERE a.uid = '" . $USER['id'] . "' ORDER BY s.id;");			
				if(isset($sql_list) && count($sql_list)>0){
					foreach ($sql_list as $p) {
						if (!empty($p['id'])) {
							$result_api[$p['id']] = ['name' => $p['name'],
							'status' => $p['status'],
							'event_time' => aftertime($p['last_event_time'])];
						}
					}
				}
			}else{
				$result_api = [];
			}			
		break;	
	case 'login':
		$uid = (int)($USER['id'] ?? 0);
		$alarm_active = 0;
		$html_enable  = false;
		$sql_los = "SELECT COUNT(o.idonu) AS los_onu	FROM onus o LEFT JOIN checkaccess a ON CONCAT('dev', o.olt) = a.types AND a.uid = :uid	WHERE o.offline >= CURDATE()  AND o.status = '2'  AND o.reason IN ('err6','err8')  AND a.uid IS NOT NULL";
		$stmt = $pdo->prepare($sql_los);
		$stmt->execute([':uid' => $uid]);
		$result_los_onu = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['los_onu' => 0];				
		$stmt = $pdo->query("SELECT COUNT(idonu) AS cnt FROM onus WHERE added >= CURDATE()");
		$result_new_onu = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['cnt' => 0];
		$sql_task = "SELECT COUNT(t.id) AS cnt FROM task_list t WHERE t.status = 1 AND EXISTS (SELECT 1	FROM task_list_vikonavci v WHERE v.taskid = t.id AND v.userid = :uid )";
		$stmt = $pdo->prepare($sql_task);
		$stmt->execute([':uid' => $uid]);
		$result_task = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['cnt' => 0];
		if (!empty($confPMon['SECURITY_PING3'])) {
			$stmt = $pdo->query("SELECT COUNT(id) AS cnt FROM alarm_ping3");
			$alarm_enable = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['cnt' => 0];
			$html_enable = ((int)$alarm_enable['cnt'] > 0);
		}
		$result_alarm_ping3 = ['status_enable' => 0, 'status_disable' => 0];
		if ($html_enable) {
			$alarm_active = 1;
			$sql_alarm_ping3 = "SELECT  SUM(CASE WHEN s.status = 0 THEN 1 ELSE 0 END) AS status_enable, SUM(CASE WHEN s.status = 2 THEN 1 ELSE 0 END) AS status_disable FROM checkaccess a JOIN alarm_ping3 s ON a.types = CONCAT('alarm_ping3_', s.id) WHERE a.uid = :uid";
			$stmt = $pdo->prepare($sql_alarm_ping3);
			$stmt->execute([':uid' => $uid]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				$result_alarm_ping3['status_enable'] = (int)($row['status_enable'] ?? 0);
				$result_alarm_ping3['status_disable'] = (int)($row['status_disable'] ?? 0);
			}
		}
		$appaccess = $USER['android'] ?? 'searchonu,odometr';
		$result_api = [
			'pmon' => $pmon_index, // 2.4
			'status' => 'connect',
			'user_id' => $uid,
			'user_ip' => 'N/A',
			'access' => $appaccess,
			'user_name' => $USER['name'] ?? ($USER['username'] ?? 'N/A'),
			'user_class' => isset($USER['class']) ? getClassUser($USER['class']) : 'N/A',
			'user_class_id' => (int)($USER['class'] ?? 666),
			'alarm_active' => $alarm_active,
			'alarm_ping3_enable' => $result_alarm_ping3['status_enable'],
			'alarm_ping3_disable' => $result_alarm_ping3['status_disable'],
			'onu_today' => (int)$result_new_onu['cnt'],
			'onu_today_los' => (int)$result_los_onu['los_onu']
		];
		break;	
	case 'porterror':
		$uid = (int)$USER['id'];
		if (!empty($confPMon['ERRORSFP']) && $confPMon['ERRORSFP'] == 1) {
			$sql = "SELECT p.id, p.deviceid, p.llid, p.error_count, p.error_today, p.operstatus, p.nameport, p.descrport FROM switch_port p LEFT JOIN checkaccess a ON a.types = CONCAT('dev', p.deviceid) AND a.uid = :uid WHERE p.monitor = 'yes' AND p.error_today > 0 AND a.uid IS NOT NULL";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':uid' => $uid]);
			$ports = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$deviceIds = [];
			foreach ($ports as $row) {
				$deviceIds[(int)$row['deviceid']] = true;
			}
			$devicesMeta = [];
			if (!empty($deviceIds)) {
				$ids = implode(',', array_keys($deviceIds));
				$sqlD = "SELECT id, place, model, inf FROM switch WHERE id IN ($ids)";
				$devicesMeta = $pdo->query($sqlD)->fetchAll(PDO::FETCH_ASSOC);
				$byId = [];
				foreach ($devicesMeta as $d) {
					$byId[(int)$d['id']] = $d;
				}
				foreach ($ports as $row) {
					$did = (int)$row['deviceid'];
					if (!isset($result_api['devices'][$did])) {
						$meta = $byId[$did] ?? ['id'=>$did,'place'=>'','model'=>'','inf'=>''];
						$result_api['devices'][$did] = [
							'device' => ['id'    => $did,'place' => (string)$meta['place'],'model' => (string)$meta['model'],'inf'   => (string)$meta['inf'],],'ports' => []
						];
					}
					$result_api['devices'][$did]['ports'][] = [
						'id' => (int)$row['id'],'nameport' => (string)$row['nameport'],'descrport' => (string)($row['descrport'] ?? ''),'operstatus'  => (string)$row['operstatus'],'error_count' => (int)$row['error_count'],'error_today' => (int)$row['error_today'],'llid' => (string)($row['llid'] ?? '')
					];
				}
			}
		}
		break;
	case 'olt_cecker':		
			$oltid = getInput("oltid");
			if(isset($oltid) && $oltid>0){
				$olt = $db->Fast('switch','*',['id'=>$oltid]);
			}			
			if(!empty($olt['id']) && strtotime($olt['updates']) < strtotime(date('Y-m-d H:i:s').' - 5min')){

			}	
			$result_api = [];	
		break;		
		case 'new_onu':	
			$i = 1;		
			$new_masiv = [];
			$sql_new_onu = "SELECT * FROM `onus` WHERE added  >= curdate()";
			$sql_onus = $db->SimpleWhile($sql_new_onu);
			if(isset($sql_onus) && count($sql_onus)>0){
				foreach($sql_onus as $onu){
					$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:null));
					$switch = $db->Fast('switch','id,place,model,inf',['id'=>$onu['olt']]);
					$new_masiv[$i] = [
						'olt_place' => $switch['place'],
						'olt_model' => $switch['inf'].' '.$switch['model'],
						'idonu' => $onu['idonu'],'oltid' => $onu['olt'],'pon' => $onu['type'],'inface' => $onu['inface'],'name' => $onu['name'],
						'status' => reason_onu_app($onu['status'],$onu['reason']),
						'mac' => $onukey,'color' => getColorBySignal($onu['rx']),
						'dist' => (isset($onu['dist']) ? $onu['dist'] : 0),
						'rx' => (isset($onu['rx']) ? $onu['rx'] : 0)
					];
					$i++;
				}
				$result_api['onu'] = $new_masiv;						
			}else{
				$result_api = [];	
			}
		break;		
		case 'pon_view':		
			$ponid = getInput("ponid");
			if(isset($ponid) && $ponid>0){
				$pon = $db->Fast('switch_pon','*',['id'=>$ponid]);
				$port = $db->Fast('switch_port','descrport',['deviceid'=>$pon['oltid'],'llid'=>$pon['sfpid']]);
			}			
			$i = 1;
			if(!empty($pon['id'])){
				$result_api_item = ['oltid' => $pon['oltid'],'id' => $pon['id'],'name' => $pon['pon'],'descr' => $port['descrport']];
				$result_api['pon'] = $result_api_item;
				$where['olt'] = $pon['oltid'];
				$where['portolt'] = $pon['sfpid'];
				$sql_onus = $db->Multi('onus','*',$where);
				if(isset($sql_onus) && count($sql_onus)>0){
					foreach($sql_onus as $onu){
						$onukey = (!empty($onu['mac'])?$onu['mac']:(!empty($onu['sn'])?$onu['sn']:null));
						$new_masiv[$i] = [
							'idonu' => $onu['idonu'],
							'oltid' => $pon['oltid'],
							'pon' => $onu['type'],
							'inface' => $onu['inface'],
							'name' => (isset($onu['name']) ? $onu['name'] : null),
							'status' => reason_onu_app($onu['status'],$onu['reason']),
							'reason' => (isset($onu['reason']) && $onu['status']==2 ? $lang[$onu['reason']] : null),
							'offlinetime' => ($onu['status']==2 ? aftertime($onu['offline']) : null),
							'onlinetime' => ($onu['status']==1 ? aftertime($onu['online']) : null),
							'mac' => $onukey,
							'color' => getColorBySignal($onu['rx']),
							'dist' => (isset($onu['dist']) ? $onu['dist'] : 0),
							'rx' => (isset($onu['rx']) ? $onu['rx'] : 0)
						];
						$i++;
					}
					$result_api['onu'] = $new_masiv;						
				}else{
					$result_api = [];	
				}
			}else{
				$result_api = [];	
			}
			
		break;		
		case 'olt_view':		
			$oltid = getInput("oltid");
			if(isset($oltid) && $oltid>0){
				$olt = $db->Fast('switch','*',['id'=>$oltid]);
			}			
			if(!empty($olt['id'])){
				$count_onu = $db->Simple("SELECT count(idonu) as cont_onu FROM onus WHERE olt = ".$olt['id']);
				$count_onu_online = $db->Simple("SELECT count(idonu) as cont_onu FROM onus WHERE status = 1 AND olt = ".$olt['id']);
				$status = 2;
				if(strtotime($olt['updates']) < strtotime(date('Y-m-d H:i:s').' - 5min')){
					$status = 1;
				}
				$result_api_item = [
					'id' => $olt['id'],
					'logo' => $olt['class'],
					'place' => $olt['place'],
					'model' => $olt['inf'] .' '. $olt['model'],
					'locationname' => $olt['locationname'],
					'status' => $status,
					'checker' => aftertime($olt['updates']),
					'uptime' => (isset($olt['uptime'])?$olt['uptime']:'not_work'),
					'onu_all' => $count_onu['cont_onu'],
					'onu_online' => $count_onu_online['cont_onu'],
					'onu_offline' => intval($count_onu['cont_onu']-$count_onu_online['cont_onu'])
				];
				$filteredResults = [];
				$result_api['olt'] = $result_api_item;	
				$switch_pon = $db->SimpleWhile("SELECT * FROM switch_pon WHERE oltid = ".$olt['id']." ORDER BY sort ASC");
				if(count($switch_pon)>0){
					$ponid = 1;
					foreach ($switch_pon as $id => $pon) {
						$result_api['sfp'][$ponid]['id'] = $pon['id'];
						$result_api['sfp'][$ponid]['name'] = $pon['pon'];
						$sqlpon = $db->Fast('switch_port','*',['deviceid'=>$olt['id'],'llid'=>$pon['sfpid']]);
						$result_api['sfp'][$ponid]['descr'] = (!empty($sqlpon['descrport'])?$sqlpon['descrport']:'--');
						$result_api['sfp'][$ponid]['typeport'] = $sqlpon['typeport'];
						$result_api['sfp'][$ponid]['support'] = $pon['support'] ?? 64;
						$result_api['sfp'][$ponid]['online'] = $pon['online'] ?? 0;
						$result_api['sfp'][$ponid]['offline'] = $pon['offline'] ?? 0;
						$result_api['sfp'][$ponid]['count'] = $pon['count'];
						$sqlnewonu = $db->Simple('SELECT count(idonu) as cont_onu  FROM `onus` WHERE added  >= curdate() AND olt = '.$olt['id'].' AND  '.($olt['oidid']==14?'zte_idport':'portolt').' = '.$pon['sfpid']);
						$result_api['sfp'][$ponid]['today'] = (isset($sqlnewonu['cont_onu']) && $sqlnewonu['cont_onu']>0 ? $sqlnewonu['cont_onu']:0);
						$ponid ++;
					}
				}
			}else{
				$result_api = [];
			}	
			
		break;			
		case 'ponlist':
			$sql = "SELECT s.* FROM checkaccess a	JOIN switch s ON CONCAT('dev', s.id) = a.types	WHERE a.uid = :uid AND s.device = 'olt'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':uid' => $USER['id']]);
			$sql_olt = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($sql_olt)) {
				foreach ($sql_olt as $olt) {
					$queries = [
						'pon' => "SELECT COUNT(id) as total FROM switch_pon WHERE oltid = :id",
						'port' => "SELECT COUNT(id) as total FROM switch_port WHERE deviceid = :id",
						'onu' => "SELECT COUNT(idonu) as total FROM onus WHERE olt = :id",
						'onu_online' => "SELECT COUNT(idonu) as total FROM onus WHERE status = 1 AND olt = :id",
						'onu_los' => "SELECT COUNT(idonu) as total FROM onus WHERE olt = :id AND (status = 2 AND (reason = 'err8' OR reason = 'err6'))"
					];
					$counts = [];
					foreach ($queries as $key => $query) {
						$stmt = $pdo->prepare($query);
						$stmt->execute([':id' => $olt['id']]);
						$counts[$key] = $stmt->fetchColumn();
					}
					$result_api_item = [
						'id' => $olt['id'],
						'place' => $olt['place'],
						'model' => $olt['inf'] . ' ' . $olt['model'],
						'locationname'  => $olt['locationname'],
						'pon' => $counts['pon'],
						'port' => intval($counts['port'] - $counts['pon']),
						'checker' => aftertime($olt['updates']),
						'uptime' => $olt['uptime'] ?? 'not_work',
						'onu_all' => $counts['onu'],
						'onu_online' => $counts['onu_online'],
						'onu_los' => $counts['onu_los'],
						'onu_offline' => intval($counts['onu'] - $counts['onu_online'])
					];
					$result_api[$olt['id']]['ponlist'] = $result_api_item;
				}
			} else {
				$result_api = [];
			}
			break;		
		case 'onu_rx': 		
			$idonu = getInput("idonu");
			$expiration = 300;
			if(isset($idonu) && $idonu>0){
				$idonu = intval($idonu);
				$getonu = $db->Fast('onus','*',['idonu'=>$idonu]);
				if(!empty($getonu['idonu'])){
					$select = $db->SimpleWhile('SELECT * FROM `historysignal` WHERE onu = '.$getonu['idonu'].' ORDER BY datetime ASC');
					if(isset($select) && count($select)>0){
						$number_id = 1;
						foreach ($select as $id => $sig) {
							$result_api['signal'][$number_id]['rx'] = $arr['signal'];
							$number_id ++ ;
						}
					}
				}
			}		
		break;		
		case 'onu_view_mapper': 		
			$idonu = getInput("idonu");
			if(isset($idonu) && $idonu>0){
				$idonu = intval($idonu);
				$onu = $db->Fast('onus','*',['idonu'=>$idonu]);
				if(!empty($onu['idonu'])){
					$onukey = (!empty($onu['mac']) ? $onu['mac'] : (!empty($onu['sn']) ? $onu['sn'] : null));
					$datatemponu = getFastOnusData($onukey);
					$result_ = array('idonu' => $onu['idonu'],'pon' => $onu['type'],'inface' => $onu['inface'],'name' => $onu['name'],'status' => reason_onu_app($onu['status'],$onu['reason']),'mac' => $onukey,'color' => getColorBySignal($onu['rx']),'dist' => (isset($onu['dist']) ? $onu['dist'] : 0),'rx' => (isset($onu['rx']) ? $onu['rx'] : 0)
					);
					if (!empty($datatemponu['lan']) && !empty($datatemponu['lon'])){						
						$result_['lan'] = $datatemponu['lan'];
						$result_['lon'] = $datatemponu['lon'];
					}	
					$result_api['onu'] = $result_;
				}
			}
		break;		
		case 'onu_send_comment': 		
			$idonu = getInput("idonu");
			if(isset($idonu) && $idonu>0){
				$idonu = intval($idonu);
				$getonu = $db->Fast('onus','mac,sn,olt,idonu',['idonu'=>$idonu]);
				if(isset($getonu['idonu']) && !empty($getonu['idonu'])){
					if(isset($comment)){
						$db->SQLupdate('onus',['comments'=>$comment],['idonu'=>$getonu['idonu']]);
					}
				}
			}			
		break;		
		case 'onu_send_mapper': 		
			$idonu = getInput("idonu");
			$lan = getInput("lan");
			$lon = getInput("lon");
			if(isset($lan)){
				$lan = Clean::str($lan);
			}			
			if(isset($lon)){
				$lon = Clean::str($lon);
			}
			if(isset($idonu) && $idonu>0 && isset($lon) && isset($lan)){
				$idonu = intval($idonu);
				$getonu = $db->Fast('onus','idonu,mac,sn,olt',['idonu'=>$idonu]);
				if(isset($getonu['idonu']) && !empty($getonu['idonu'])){
					$onukey = trim(!empty($getonu['mac']) ? $getonu['mac'] : (!empty($getonu['sn']) ? $getonu['sn'] : null));
					$datatemponu = getFastOnusData($onukey);
					if(!empty($datatemponu['id'])){
						$db->SQLupdate('onusdata',['lon'=>$lon,'lan'=>$lan],['id'=>$datatemponu['id']]);
					}else{
						$db->SQLinsert('onusdata',['onukey'=>$onukey,'lan'=>$lan,'lon'=>$lon]);
					}
					if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
						$cacheManager->delete("onus_data_".$onukey);
					}
				}
			}				
		break;		
		case 'onu_view': 
			$idonu = getInput("idonu");
			$expiration = 300;
			if(isset($idonu) && $idonu>0){
				$idonu = intval($idonu);
				$getonu = $db->Fast('onus','*',['idonu'=>$idonu]);
				if(!empty($getonu['idonu'])){
					$getolt = $db->Fast('switch','*',['id' => $getonu['olt']]);
					$getlocation = $db->Fast('location','id,name',['id' => $getolt['location']]);
					if(snmp_access($getolt)){
						if(!empty($getolt['id'])){
							$OnuClass = new Ont($getolt['id'],$getolt['class'], $db, $logger, $config, $cacheManager, $php_class_device);
							$support = $OnuClass->Support();
								if(isset($support)){
									$result_ = $OnuClass->getResult($getonu,false);
								}
								if(!empty($getonu['mac']))
									$result_['mac'] = $getonu['mac'];										
								if(isset($getonu['name']) && !empty($getonu['name'])) {
									$name = trim(str_replace(['--', ',', '.'], '', $getonu['name']));
									$result_['name'] = (isset($name) ? $name : "N/A");
								}										
								if(!empty($getonu['reason']))
									$result_['reason'] = $lang[$getonu['reason']];			
								if(!empty($getonu['sn']))
									$result_['sn'] = $getonu['sn'];			
								if(!empty($getonu['inface']))
									$result_['inface'] = $getonu['inface'];			
								if(!empty($getonu['type']))
									$result_['pon'] = $getonu['type'];					
								if(!empty($getolt['id'])){
									$result_['olt_name'] = $getolt['place'];
									$result_['olt_model'] = $getolt['inf'] .' '. $getolt['model'];
									$result_['olt_netip'] = $getolt['netip'];						
									$result_['olt_cron'] = $getolt['updates'];
									if(isset($getlocation['name']) && !empty($getlocation['name']))									
										$result_['olt_location'] = $getlocation['name'];	
								}					
							$onukey = (!empty($getonu['mac'])?$getonu['mac']:(!empty($getonu['sn'])?$getonu['sn']:null));
							if(isset($onukey)){
								$datatemp = getFastOnusData($onukey);
								$result_['lan'] = (!empty($datatemp['lan']) ? $datatemp['lan'] : '');
								$result_['lon'] = (!empty($datatemp['lon']) ? $datatemp['lon'] : '');
								$result_['tag'] = (!empty($datatemp['tag'])?$datatemp['tag']:'');								
							}
							$result_['comment'] = (!empty($getonu['comments'])?$getonu['comments']:'');
							$result_['idonu'] = $getonu['idonu'];
							$result_['oltid'] = $getonu['olt'];
							$result_api['onu'] = $result_;
						}
					}else{
						$response = array('success' => true, 'message' => 'check_switch');
						detectError($response);
					}
				}
			}
		break;		
		case 'ping3_list': 
			$sql_ping3 = $db->SimpleWhile("SELECT id, name, volt, typebattery, energytime, energy FROM mon_ping3 ORDER BY energy DESC, energytime DESC");
			if (isset($sql_ping3) && count($sql_ping3) > 0) {
				$device_ids = array_column($sql_ping3, 'id');
				$device_ids_str = implode(',', array_map('intval', $device_ids));
				$sql_voltage = $db->SimpleWhile("
					SELECT deviceid, id, added
					FROM mon_voltage WHERE energy = 2 AND mon_types = 'ping3' AND deviceid IN ($device_ids_str) ORDER BY deviceid, id ASC");
				$voltage_map = [];
				foreach ($sql_voltage as $voltage) {
					$voltage_map[$voltage['deviceid']] = $voltage;
				}
				$result_api = [];
				foreach ($sql_ping3 as $ping3) {
					if ($access->get('ping3_' . $ping3['id'])) {
						$latest_voltage = isset($voltage_map[$ping3['id']]) ? $voltage_map[$ping3['id']] : null;
						$result_api_item = [
							'img' => ($ping3['energy'] == 'yes' && $ping3['energy'] == 2 ? 'no' : '') . getVolt($ping3['volt'], $ping3['typebattery']),
							'idping' => $ping3['id'],'name' => $ping3['name'] ?? 'n/a','energy' => $ping3['energy'] ?? 2,
							'volt' => $ping3['volt'] ?? 0,'typebattery' => $ping3['typebattery'] ?? '12',
							'energytime' => ($ping3['energy'] == 2 ? aftertime($ping3['energytime']) : 
							(isset($voltage_map[$ping3['id']]['added']) ? aftertime($voltage_map[$ping3['id']]['added']) : "Very good"))
						];
						$result_api[$ping3['id']]['ping3'] = $result_api_item;
					}
				}
			}else{
				$result_api = [];
			}
		break;			
		case 'odometr_save': 		
			$sqlinsert = [];
			$pokaznikid = (int)isset($_REQUEST['id']) ? $_REQUEST['id'] : (isset($_REQUEST['id']) ? $_REQUEST['id'] : null);
			$pokaznik = (int)isset($_REQUEST['pokaznik']) ? $_REQUEST['pokaznik'] : (isset($_REQUEST['pokaznik']) ? $_REQUEST['pokaznik'] : null);
			$data = isset($_REQUEST['date']) ? $_REQUEST['date'] : (isset($_REQUEST['date']) ? $_REQUEST['date'] : null);
			$formattedDate = convertDate($data);
			if ($formattedDate !== false && isset($USER['id']) && $USER['id'] > 0 && isset($pokaznikid) && $pokaznikid>0 && isset($pokaznik) && $pokaznik>0){
				$db->SQLupdate('users_odometr',['status'=>2,'finish_date' => $formattedDate,'finish_odometr' => $pokaznik],['id'=>$pokaznikid]);
				$result_api = true; 
			}else{
				$result_api = false; 
			}	
		break;			
		case 'odometr_added': 
			$sqlinsert = [];
			$pokaznik = (int)isset($_REQUEST['pokaznik']) ? $_REQUEST['pokaznik'] : (isset($_REQUEST['pokaznik']) ? $_REQUEST['pokaznik'] : null);
			$data = isset($_REQUEST['date']) ? $_REQUEST['date'] : (isset($_REQUEST['date']) ? $_REQUEST['date'] : null);
			$formattedDate = convertDate($data);
			if ($formattedDate !== false && isset($USER['id']) && $USER['id'] > 0 && isset($pokaznik) && $pokaznik>0){
				$car_nomer = isset($_REQUEST['car']) ? cleanUsername($_REQUEST['car']) : (isset($_REQUEST['car']) ? cleanUsername($_REQUEST['car']) : '');
				$select_car = $db->Simple("SELECT * FROM `car` WHERE nomer = '{$car_nomer}' LIMIT 1");
				$sqlinsert = array(
					'car_id' => $select_car['id'], 'start_date' => $formattedDate,'start_odometr' => $pokaznik,'status' => 1,'km' => 1,'suma' => 0,'userid' => $USER['id'],'car' => $car_nomer,'added' => $pmon_clock
				);
				$sql_re_check = $db->Simple("SELECT id FROM users_odometr WHERE start_odometr = '".$pokaznik."' AND userid = '".$USER['id']."' AND car = '".$car_nomer."' AND status = '1' LIMIT 1");
				if(empty($sql_re_check['id'])){
					$db->SQLinsert('users_odometr',$sqlinsert);
					$result_api = true; 
				}else{
					$result_api = false; 
				}
			}else{
				$result_api = false; 
			}
		break;			
		case 'list_car': 		
			$sql_car_odometr = "SELECT car.* FROM users_car JOIN car ON car.id = users_car.car_id WHERE users_car.moder_id = '".$USER['id']."'";
			$car = [];
			$result_odometr = $db->SimpleWhile($sql_car_odometr);
			if(isset($result_odometr) && count($result_odometr) > 0) {
				foreach($result_odometr as $c){					
					$car[$c['id']]['nomer'] = $c['nomer'];
					$car[$c['id']]['id'] = $c['id'];
				}
			}
			$result_api = $car;			
		break;			
		case 'odometr_list': 
			$sql_today_odometr = "SELECT * FROM users_odometr WHERE userid = " . $USER['id'] . " AND MONTH(added) = MONTH(CURDATE()) AND YEAR(added) = YEAR(CURDATE()) ORDER BY id, status DESC";
			$odometrs = [];
			$result_odometr = $db->SimpleWhile($sql_today_odometr);
			if(isset($result_odometr) && count($result_odometr) > 0) {
				foreach($result_odometr as $odometr){
					if(isset($odometr['status']) && $odometr['status']==2){
						$km = $odometr['finish_odometr'] - $odometr['start_odometr'];
					}else{
						$km = 1;
					}
					$result_api_item = [
						'id' => $odometr['id'],
						'start_date' => $odometr['start_date'] ?? null,
						'finish_date' => $odometr['finish_date'] ?? null,
						'start_odometr' => $odometr['start_odometr'] ?? null,
						'finish_odometr' => $odometr['finish_odometr'] ?? null,
						'km' => $km ?? 1,
						'price' => $odometr['price'] ?? 1,
						'car' => mb_strtoupper($odometr['car'] ?? 1),
						'status' => $odometr['status'] ?? 1,
						'time_status' => (isset($odometr['status']) && $odometr['status']==1 ? aftertime($odometr['start_date']) : ''),
					];
					$odometrs[$odometr['id']]['odometr'] = $result_api_item;	
				}
			}
			$result_api = $odometrs;
		break;	
		case 'contry_list': 		
			$sqllocation = getLocation();	
			if(isset($sqllocation) && count($sqllocation)>0){
				$id = 1;
				foreach($sqllocation as $location){
					$count_olt = $db->Simple("SELECT count(id) as cont_pon FROM switch WHERE location = ".$location['id']." AND device = 'olt'");
					$locations = array(
						'id' => $location['id'],'lan' => $location['lan'],'lon' => $location['lon'],'count' => (isset($count_olt['cont_pon'])?$count_olt['cont_pon']:0),'name' => $location['name']
					);
					$location_[$id]['country'] = $locations;
					$id ++ ;
				}
				$result_api = $location_;	
			}else{
				$result_api = [];
			}				
		break;
		case 'ping3_view': 		
			$idping = getInput("idping");
			$result_data = array();
			$charging = 0;
			$discharged = 0;
			$charging_time = "";
			$discharged_time = "";
			if (isset($idping) && $idping > 0) {
				$idping = intval($idping);
				$mon_ping3 = $db->Fast('mon_ping3', '*', ['id' => $idping]);
				if (empty($mon_ping3)) {
					return false;
				}
				$sql = "
					SELECT id, energy, volt, deviceid, mon_types, added
					FROM mon_voltage
					WHERE deviceid = {$idping} AND DATE(added) = CURDATE()
					ORDER BY added ASC;
				";
				$results = $db->SimpleWhile($sql);
				$lightPeriods = [];
				$currentPeriod = null;
				$previousVoltage = null;
				$dayEnd = strtotime('today 23:59:59');
				$intervals = array_fill(1, 48, 'grey'); // Кожний інтервал 30 хвилин (48 інтервалів в день)
				$currentTime = time();
				$currentHour = date('G', $currentTime); // Поточна година
				$currentMinute = date('i', $currentTime); // Поточна хвилина
				if (isset($results) && count($results) > 2) {
					foreach ($results as $row) {
						$status = (int) $row['energy'];
						$timestamp = strtotime($row['added']);
						$voltage = (float) $row['volt'];    
						if ($status == 1) {
							if ($currentPeriod === null) {
								$currentPeriod = ['start' => $timestamp, 'end' => null, 'startVoltage' => $voltage, 'endVoltage' => null, 'status' => 'on'];
							} elseif ($currentPeriod['status'] === 'off') {
								$currentPeriod['end'] = $timestamp;
								$currentPeriod['endVoltage'] = $previousVoltage;
								$lightPeriods[] = $currentPeriod;
								$currentPeriod = ['start' => $timestamp, 'end' => null, 'startVoltage' => $voltage, 'endVoltage' => null, 'status' => 'on'];
							}
						} elseif ($status == 2) {
							if ($currentPeriod === null) {
								$currentPeriod = ['start' => $timestamp, 'end' => null, 'startVoltage' => $voltage, 'endVoltage' => null, 'status' => 'off'];
							} elseif ($currentPeriod['status'] === 'on') {
								$currentPeriod['end'] = $timestamp;
								$currentPeriod['endVoltage'] = $previousVoltage;
								$lightPeriods[] = $currentPeriod;
								$currentPeriod = ['start' => $timestamp, 'end' => null, 'startVoltage' => $voltage, 'endVoltage' => null, 'status' => 'off'];
							}
						}
						$previousVoltage = $voltage;
					}
				}
				if ($currentPeriod !== null) {
					$currentPeriod['end'] = $dayEnd;
					$currentPeriod['endVoltage'] = $previousVoltage;
					$lightPeriods[] = $currentPeriod;
				}							
				$count_posistion = 1;
				$totalTime = 0;
				$totalOnTime = 0;
				$totalOffTime = 0;
				if (isset($lightPeriods) && count($lightPeriods) > 1) {
					foreach ($lightPeriods as $period) {
						$start = date('H:i', $period['start']);
						$end = date('H:i', $period['end']);
						$duration = $period['end'] - $period['start'];
						$hours = floor($duration / 3600);
						$minutes = floor(($duration % 3600) / 60);
						$startVoltage = $period['startVoltage'];
						$endVoltage = $period['endVoltage'];
						$totalTime += $duration;
						if ($period['status'] === 'on') {
							$totalOnTime += $duration;
							$periodPercentage = ($duration / 86400) * 100;  
							$used = $endVoltage - $startVoltage;
							$result_data['list'][$count_posistion] = array(
							'time_start' => $start, 'time_end' => $end, 'worker' => "$hours г $minutes хв", 'energy' => 'energy', 'used' => number_format($used, 1), 'volt_start' => $startVoltage, 'volt_end' => $endVoltage, 'percent' => number_format($periodPercentage, 2),
										);
						} else {
							$used = $startVoltage - $endVoltage;
							$totalOffTime += $duration;
							$periodPercentage = ($duration / 86400) * 100; 
							$result_data['list'][$count_posistion] = array(
							'time_start' => $start, 'time_end' => $end, 'worker' => "$hours г $minutes хв", 'energy' => 'noenergy', 'used' => number_format($used, 1), 'volt_start' => $startVoltage, 'volt_end' => $endVoltage, 'percent' => number_format($periodPercentage, 2),
							);
						}
						$count_posistion++;
					}
				}
				if (isset($result_data) && count($result_data) > 1) {
					$onPercentage = ($totalOnTime / $totalTime) * 100;
					$offPercentage = ($totalOffTime / $totalTime) * 100;
					$charging_hours = floor($totalOnTime / 3600);
					$charging_minutes = floor(($totalOnTime % 3600) / 60);
					$discharged_hours = floor($totalOffTime / 3600);
					$discharged_minutes = floor(($totalOffTime % 3600) / 60);
					$charging_time = "$charging_hours г $charging_minutes хв";
					$discharged_time = "$discharged_hours г $discharged_minutes хв";
					$charging = number_format($onPercentage, 2);
					$discharged = number_format($offPercentage, 2);
				}
			}
			$result_data['hours'] = get_timed_ping3($idping);
			$result_data['global'] = array(
				'name' => $mon_ping3['name'],'type' => $mon_ping3['typebattery'],'charging' => $charging,'charging_time' => $charging_time,'discharged_time' => $discharged_time,'discharged' => $discharged
			);  
			$result_api = $result_data;			
		break;
		case 'widget':
			$list_ping3 = ''; 		
			$uid = (int)$USER['id'];
			if(!$uid){
				die('Error null uid');
			}
			$stmt = $pdo->prepare("SELECT COUNT(idonu) AS onu FROM checkaccess a JOIN onus o ON CONCAT('dev', o.olt) = a.types WHERE a.uid = :uid  AND o.offline >= CURDATE()  AND o.status = '2'  AND (o.reason = 'err1' OR o.reason = 'err0')");
			$stmt->execute(['uid' => $uid]);
			$result_data['onu_power'] = $stmt->fetch()['onu'];
			$stmt = $pdo->prepare("SELECT COUNT(idonu) AS onu FROM checkaccess a JOIN onus o ON CONCAT('dev', o.olt) = a.types WHERE a.uid = :uid  AND o.offline >= CURDATE() AND o.status = '2'  AND (o.reason = 'err8' OR o.reason = 'err6')");
			$stmt->execute(['uid' => $uid]);
			$result_data['onu_los'] = $stmt->fetch()['onu'];
			$stmt = $pdo->prepare("SELECT COUNT(idonu) AS onu FROM checkaccess a JOIN onus o ON CONCAT('dev', o.olt) = a.types	WHERE a.uid = :uid  AND o.added >= CURDATE()");
			$stmt->execute(['uid' => $uid]);
			$result_data['onu_new'] = $stmt->fetch()['onu'];
			$stmt = $pdo->prepare("SELECT p.id, p.name, p.volt	FROM checkaccess a JOIN mon_ping3 p ON CONCAT('ping3_', p.id) = a.types WHERE a.uid = :uid AND p.energy = 2 AND p.monitor = 'yes'");
			$stmt->execute(['uid' => $uid]);
			$results = $stmt->fetchAll();
			if ($results && count($results) > 0) {
				$result_data['ping3'] = 1;
				$list_ping3 = '';
				foreach ($results as $row) {
					$list_ping3 .= $row['name'] . ': ' . $row['volt'] . ', ';
				}
			} else {
				$result_data['ping3'] = 2;
				$list_ping3 = '';
			}
			$result_data['ping3list'] = $list_ping3;
			$result_data['datepmon'] = 'PMON: '.$pmon_clock;
			$result_api = $result_data;	
		break;
		case 'onu_geo':
			$lat = isset($_GET['lan']) ? (float)$_GET['lan'] : (isset($_GET['lat']) ? (float)$_GET['lat'] : 0.0);
			$lon = isset($_GET['lon']) ? (float)$_GET['lon'] : 0.0;
			$radius = isset($_GET['radius']) ? (int)$_GET['radius'] : 500; // м
			if ($lat === 0.0 && $lon === 0.0) {
				echo json_encode(['ok'=>false,'err'=>'coords missing']); break;
			}
			$radius = max(10, min($radius, 50000));
			$switch_app   = APP_getSwitchOlt();
			$location_app = APP_getLocation();
			$latDelta = $radius / 111320.0;
			$lonDelta = $radius / (111320.0 * max(0.001, cos(deg2rad($lat))));
			$latMin = $lat - $latDelta;
			$latMax = $lat + $latDelta;
			$lonMin = $lon - $lonDelta;
			$lonMax = $lon + $lonDelta;
			$get_info = 'onus.idonu, onus.olt, onus.status, onus.inface, onus.type, onus.mac, onus.name, onus.sn, onus.rx, onus.dist, onus.offline, onus.online, onus.vendor, onus.model, onus.lastrx, onus.changerx';
			$sql = "
			  SELECT 
				od.id AS onusdata_id,
				od.onukey AS onukey,
				od.lan AS lan,
				od.lon AS lon,
				od.tag, od.name AS od_name,
				{$get_info},
				(
				  6371000 * ACOS(
					COS(RADIANS({$lat})) * COS(RADIANS(od.lan)) *
					COS(RADIANS(od.lon) - RADIANS({$lon})) +
					SIN(RADIANS({$lat})) * SIN(RADIANS(od.lan))
				  )
				) AS distance_m
			  FROM onusdata od
			  LEFT JOIN onus 
				ON (onus.mac = od.onukey OR onus.sn = od.onukey)
			  LEFT JOIN checkaccess a 
				ON (onus.olt IS NOT NULL AND CONCAT('dev', onus.olt) = a.types AND a.uid = {$USER['id']})
			  WHERE 
				od.lan BETWEEN {$latMin} AND {$latMax}
				AND od.lon BETWEEN {$lonMin} AND {$lonMax}
				AND (a.uid IS NOT NULL OR onus.idonu IS NULL)
			  HAVING distance_m <= {$radius}
			  ORDER BY distance_m ASC
			  LIMIT 200
			";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$items = [];
			if ($rows) {
				foreach ($rows as $r) {
					$onukey = $r['onukey'] ?: ($r['mac'] ?: ($r['sn'] ?: null));
					$active = (isset($r['status']) && (int)$r['status'] === 1) ? 'online' : 'offline';
					$hasSignal = (!empty($r['lastrx']) ? 1 : 0);
					$oltPlace = $r['olt'] !== null ? ($switch_app[$r['olt']]['place'] ?? '') : '';
					$oltLocation = $r['olt'] !== null ? ($location_app[$switch_app[$r['olt']]['location']]['name'] ?? '') : '';
					$oltIp = $r['olt'] !== null ? ($switch_app[$r['olt']]['netip'] ?? '') : '';
					if($onukey){
					$items[] = [
						'idonu' => isset($r['idonu']) ? (int)$r['idonu'] : null,
						'onukey' => $onukey,
						'lan' => (float)$r['lan'],
						'lon' => (float)$r['lon'],
						'distance_m' => round((float)$r['distance_m']),
						'status' => isset($r['status']) ? (int)$r['status'] : 2,
						'active' => $active,
						'signal' => $hasSignal,
						'rx' => isset($r['rx']) ? (float)$r['rx'] : 0,
						'name' => $r['name'] ?? ($r['od_name'] ?? ''),
						'mac' => $r['mac'] ?? '',
						'sn' => $r['sn'] ?? '',
						'vendor' => $r['vendor'] ?? '',
						'model' => $r['model'] ?? '',
						'olt' => [
							'id' => isset($r['olt']) ? (int)$r['olt'] : null,
							'place' => $oltPlace,
							'location' => $oltLocation,
							'netip' => $oltIp,
						],
					];
					}
				}
			}
			echo json_encode([
				'ok' => true,
				'center' => ['lan'=>$lat, 'lon'=>$lon, 'radius_m'=>$radius],
				'count' => count($items),
				'items' => $items
			], JSON_UNESCAPED_UNICODE);
		break;
	}	
}else{
	$response = array('success' => false, 'message' => $lang['app_error_1']);
	detectError($response);
}
if(isset($result_api) && !empty($result_api)){
    header('Last-Modified: ' . gmdate('r'));
    header('Content-Type: application/json; charset=UTF-8');
    header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1 
    header("Pragma: no-cache");
	$response = array('app' => true, 'data' => $result_api);
	detectError($response);
}
?>
