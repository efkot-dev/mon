<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
// Р¤СѓРЅРєС†С–СЏ РґР»СЏ С„РѕСЂРјСѓРІР°РЅРЅСЏ РјР°СЃРёРІСѓ РґР°РЅРёС…
function re_name_pliters($name) {
	$rename = "N/A";
	$pattern = '/^(procent|planar)_(\d+)\/(\d+)$/';
	if (preg_match($pattern, $name, $tmp)){
		$rename = $tmp[2]."/".$tmp[3];
	}
	return $rename;
}
function count_pliters($data) {
	$pattern = '/^(\d+)\/(\d+)$/';
	if (preg_match($pattern, $data, $tmp)){
		return array('in'=>$tmp[1],'out'=>$tmp[2]);
	}
	return false;
}
function generate_splitters($id) {
	global $db;
	$getpon = $db->SimpleWhile("SELECT * FROM ponmap_elements WHERE elementid = '{$id}' AND type = 'splitter'");
	$elements = [];
	if(isset($getpon) && count($getpon) > 0){
		foreach ($getpon as $row) {
			$getconnect = $db->SimpleWhile("SELECT * FROM ponmap_connectors WHERE element_id = '{$row['id']}'");		
			$connectors = [];
			foreach ($getconnect as $conn) {
				$connectors[] = array($conn['connected'] => 'splitters' . $conn['id'],'n' => $conn['connector_name'],'p' => $row['position']);
			}
			$name = re_name_pliters($row['name']);
			$elements[] = [
				'id' => $row['id'],
				'elementid' => $row['elementid'],
				'name' => $name,
				'type' => $row['type'],
				'position' => $row['position'],
				'top' => $row['top'],
				'left' => $row['left'],
				'c' => $connectors
			];
		}
	}
	return $elements;	
}
function getOnuId(string $str): int {
    return (int)preg_replace('/\D/', '', $str);
}
function generate_onu(PDO $pdo, int $elementId): array
{
    if ($elementId <= 0) {
        return [];
    }

    // Автоочистка "сиріт": ONU-елементи, яких вже немає в таблиці onus
    $orphanSql = "
        SELECT e.id
        FROM ponmap_elements e
        LEFT JOIN onus u
            ON u.idonu = CAST(SUBSTRING_INDEX(e.name, '_', -1) AS UNSIGNED)
        WHERE e.elementid = :elementid
          AND e.type = 'onu'
          AND u.idonu IS NULL
    ";
    $orphanStmt = $pdo->prepare($orphanSql);
    $orphanStmt->execute([':elementid' => $elementId]);
    $orphanIds = array_map('intval', array_column($orphanStmt->fetchAll(PDO::FETCH_ASSOC), 'id'));

    if (!empty($orphanIds)) {
        $in = implode(',', array_fill(0, count($orphanIds), '?'));

        $delConnectors = $pdo->prepare("DELETE FROM ponmap_connectors WHERE element_id IN ($in)");
        $delConnectors->execute($orphanIds);

        $delLinks = $pdo->prepare("DELETE FROM ponmap_connect WHERE elementid = ? AND (p1 IN ($in) OR p2 IN ($in))");
        $delLinks->execute(array_merge([$elementId], $orphanIds, $orphanIds));

        $delElements = $pdo->prepare("DELETE FROM ponmap_elements WHERE id IN ($in)");
        $delElements->execute($orphanIds);
    }

    $sql = "
        SELECT 
            e.id,
            e.elementid,
            e.name AS element_name,
            e.position,
            e.top,
            e.`left`,
            c.connected,
            u.type as type_pon,
            u.status,
            u.inface,
            u.rx
        FROM ponmap_elements e
        LEFT JOIN (
            SELECT element_id, MAX(connected) AS connected
            FROM ponmap_connectors
            GROUP BY element_id
        ) c ON c.element_id = e.id
        INNER JOIN onus u 
            ON u.idonu = CAST(SUBSTRING_INDEX(e.name, '_', -1) AS UNSIGNED)
        WHERE e.elementid = :elementid
          AND e.type = 'onu'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':elementid' => $elementId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $elements = [];
    foreach ($rows as $r) {
        $isOnline = isset($r['status']) && (int)$r['status'] === 1;
        $rxLabel  = $isOnline && $r['rx'] !== null && $r['rx'] !== '' ? $r['rx'].' dbm' : 'offline';
        $elements[] = [
            'id' => (int)$r['id'],
            'elementid' => (int)$r['elementid'],
            'idonu' => $r['element_name'], 
            'name' => $r['type_pon'].' '.$r['inface'],
            'status' => $r['status'] !== null ? (int)$r['status'] : 0,
            'rx' => $rxLabel,
            'type' => 'onu',
            'position' => $r['position'],
            'top' => is_numeric($r['top'])  ? (float)$r['top']  : $r['top'],
            'left' => is_numeric($r['left']) ? (float)$r['left'] : $r['left'],
            'input' => ($r['connected'] ?? '').'_'.$r['element_name'],
        ];
    }
    return $elements;
}
function generate_planars($id) {
	global $db;
	$getpon = $db->SimpleWhile("SELECT * FROM ponmap_elements WHERE elementid = '{$id}' AND type = 'planar'");
	$elements = [];
	if(isset($getpon) && count($getpon) > 0){
		foreach ($getpon as $row) {
			$getconnect = $db->SimpleWhile("SELECT * FROM ponmap_connectors WHERE element_id = '{$row['id']}'");		
			$connectors = [];
			foreach ($getconnect as $conn) {
				$connectors[] = array($conn['connected'] => 'planar' . $conn['id'],'n' => $conn['connector_name'],'p' => $row['position']);
			}
			$name = re_name_pliters($row['name']);
			$spl = count_pliters($name);
			$elements[] = [
				'id' => $row['id'],
				'elementid' => $row['elementid'],
				'name' => $name,
				'in' => $spl['in'],
				'out' => $spl['out'],
				'type' => $row['type'],
				'position' => $row['position'],
				'top' => $row['top'],
				'left' => $row['left'],
				'c' => $connectors
			];
		}
	}
	return $elements;	
}	
function generate_connect($id) {
    global $db;
    $ftth_array = [];
    $getconn = $db->SimpleWhile("SELECT * FROM ponmap_connect WHERE elementid = '{$id}'");
	if(isset($getconn) && count($getconn) > 0){
		foreach ($getconn as $conn) {
			$ftth_array[] = array(
				'conn1' => $conn['connect1'],'povorot' => (!empty($conn['povorot']) ? $conn['povorot'] : 30),
				'connect' => (!empty($conn['connect']) ? $conn['connect'] : false),
				'note' => (!empty($conn['name']) ? $conn['name'] : false),
				'conn2' => $conn['connect2'],'id' => $conn['id'],'pos1' => $conn['in1'],'pos2' => $conn['in2'],'color' => $conn['color'],'border' => $conn['border']
			);
		}
	}
    return $ftth_array;    
}
function generate_cross($id) {
	global $db;
	$getpon = $db->SimpleWhile("SELECT * FROM ponmap_elements WHERE elementid = '{$id}' AND type = 'cross'");
	$elements = [];
	$notesMap = [];
	$notesRows = $db->SimpleWhile("SELECT vokid, note FROM kabel_connector WHERE elementid = '{$id}'");
	if (is_array($notesRows) && count($notesRows) > 0) {
		foreach ($notesRows as $nr) {
			if (!empty($nr['vokid'])) {
				$notesMap[(string)$nr['vokid']] = (string)($nr['note'] ?? '');
			}
		}
	}
	if (isset($getpon) && count($getpon) > 0) {
		foreach ($getpon as $row) {
			$getconnect = $db->SimpleWhile("SELECT * FROM ponmap_connectors WHERE element_id = '{$row['id']}' ORDER BY id ASC");
			$input = [];
			$output = [];
			foreach ($getconnect as $conn) {
				$connId = !empty($conn['connected']) ? $conn['connected'] : ('cross'.$row['id'].'_'.$conn['id']);
				$port = [
					'name' => $conn['connector_name'],
					'id' => $connId,
					'note' => (string)($notesMap[$connId] ?? '')
				];
				$isInput = ($conn['connector_type'] === 'input' || strpos((string)$conn['connected'], 'in') !== false);
				if ($isInput) {
					$input[] = $port;
				} else {
					$output[] = $port;
				}
			}
			$elements[] = [
				'id' => $row['id'],
				'elementid' => $row['elementid'],
				'type' => 'cross',
				'position' => $row['position'],
				'name' => $row['name'],
				'description' => isset($row['description']) ? $row['description'] : '',
				'top' => $row['top'],
				'left' => $row['left'],
				'count' => max(count($input), count($output)),
				'rozetki' => [
					['input' => $input],
					['output' => $output]
				]
			];
		}
	}
	return $elements;	
}
function generate_vok($id) {
	global $db;
	$sql ="SELECT 
		f.types AS f_types,
		f.unitid AS f_unitid,
		f.id AS conn_id,
		k.id AS kabel_id, 
		k.modules, 
		k.name, 
		k.volokon, 
		k.types, 
		k.color, 
		k.decription, 
		k.km, 
		km.id AS module_id, 
		km.modulecolor, 
		km.moduleid as mid, 
		kp.position AS poza,
		kp.left AS pleft,
		kp.elementid AS pon_element,
		kp.top AS ptop
	FROM 
		fibers f
		JOIN kabel k ON k.id = f.kabel
		LEFT JOIN kabel_position kp ON kp.connectid = f.id AND kp.elementid = {$id} 
		LEFT JOIN kabel_module km ON km.kabelidid = k.id
	WHERE f.conn1 = '{$id}' OR f.conn2 = '{$id}'";
	$data_array = [];
    $vok_array = $db->SimpleWhile($sql);

	if(isset($vok_array) && count($vok_array) > 0){
		foreach ($vok_array as $vok) {
			$conn_id = $vok['conn_id'];
			if($vok['f_types']==1){
				$sql_unit = "SELECT id,name FROM `ponunit` WHERE id = '{$vok['f_unitid']}' LIMIT 1";
				$vols_unit = $db->Simple($sql_unit);
				$data_array[$conn_id]['href'] = '/?do=fiber&act=view&id=';
				$data_array[$conn_id]['name'] = $vols_unit['name'];
			}else{
				$sql_vols_href = "SELECT elementid FROM `kabel_position` WHERE connectid = '{$conn_id}' AND elementid != '{$vok['pon_element']}' LIMIT 1";
				$vols_href = $db->Simple($sql_vols_href);
				$sql_pon_element = "SELECT name FROM ponelement WHERE id = '{$vols_href['elementid']}'";
				$ponelement = $db->Simple($sql_pon_element);
				$data_array[$conn_id]['href'] = '/?do=fiber&act=view&id='.$vols_href['elementid'];
				$data_array[$conn_id]['name'] = $ponelement['name'];
			}
			$module_id = $vok['mid'];
			$data_array[$conn_id]['id'] = $conn_id;
			$data_array[$conn_id]['type'] = 'vok';
			$data_array[$conn_id]['position'] = !empty($vok['poza']) ? $vok['poza'] : 'left';			
			$data_array[$conn_id]['pon_element'] = $vok['pon_element'];
			$data_array[$conn_id]['left'] = !empty($vok['pleft']) ? $vok['pleft'] : 0;
			$data_array[$conn_id]['top'] = !empty($vok['ptop']) ? $vok['ptop'] : 0;
			$data_array[$conn_id]['colba'][$module_id]['id'] = $module_id;
			$data_array[$conn_id]['colba'][$module_id]['color'] = $vok['modulecolor'];
			$sql_vols = "SELECT * FROM `kabel_volokno` WHERE moduleid = '{$module_id}' AND kabelidid = '{$vok['kabel_id']}'";
			$vols_array = $db->SimpleWhile($sql_vols);
			$data_array[$conn_id]['colba'][$module_id]['vok'] = [];
			foreach ($vols_array as $vols) {
				$vol_id = $vols['id'];
				$gen_id = 'v' . $conn_id .'m'. $module_id .'c'. $vols['id'];
				$note_row = $db->Fast('kabel_connector','*',['vokid'=>$gen_id, 'elementid' => $vok['pon_element']]);
				$data_array[$conn_id]['colba'][$module_id]['vok'][$vol_id] = [
					'id' => $gen_id,
					'name' => $vol_id,
					'note' => (!empty($note_row['note']) ? $note_row['note'] : false),
					'line' => (!empty($note_row['line']) ? $note_row['line'] : false),
					'color' => $vols['voloknocolor']
				];
			}
		}
		foreach ($data_array as &$conn) {
			if (isset($conn['colba'])) {
				$conn['colba'] = array_values($conn['colba']);
				foreach ($conn['colba'] as &$colba) {
					if (isset($colba['vok'])) {
						$colba['vok'] = array_values($colba['vok']);
					}
				}
			}
		}
	}
	return $data_array;	
}
function generate_data($id) {
	global $db;
	$getpon = $db->SimpleWhile("SELECT * FROM ponmap_elements WHERE elementid = '{$id}' AND type = 'switch'");
	$elements = [];
	$notesMap = [];
	$notesRows = $db->SimpleWhile("SELECT vokid, note FROM kabel_connector WHERE elementid = '{$id}'");
	if (is_array($notesRows) && count($notesRows) > 0) {
		foreach ($notesRows as $nr) {
			if (!empty($nr['vokid'])) {
				$notesMap[(string)$nr['vokid']] = (string)($nr['note'] ?? '');
			}
		}
	}
	if (isset($getpon) && count($getpon) > 0) {
		foreach ($getpon as $row) {
			$getconnect = $db->SimpleWhile("SELECT * FROM ponmap_connectors WHERE element_id = '{$row['id']}' ORDER BY id ASC");
			$ports = [];
			foreach ($getconnect as $conn) {
				$portId = !empty($conn['connected']) ? $conn['connected'] : ('switch'.$row['id'].'_'.$conn['id']);
				$ports[] = [
					'id' => $portId,
					'name' => $conn['connector_name'],
					'note' => (string)($notesMap[$portId] ?? '')
				];
			}
			$elements[] = [
				'id' => $row['id'],
				'elementid' => $row['elementid'],
				'position' => $row['position'],
				'type' => 'switch',
				'name' => $row['name'],
				'description' => isset($row['description']) ? $row['description'] : '',
				'top' => $row['top'],
				'left' => $row['left'],
				'models' => isset($row['description']) ? $row['description'] : '',
				'ports' => $ports
			];
		}
	}
	return $elements;
}
function re_position($position) {
    switch ($position) {
        case 'left':
            return 'right';
        case 'right':
            return 'left';
        case 'down':
            return 'top';
        case 'top':
            return 'down';
        default:
            return $position; // РџРѕРІРµСЂС‚Р°С” РїРѕС‡Р°С‚РєРѕРІРµ Р·РЅР°С‡РµРЅРЅСЏ, СЏРєС‰Рѕ РїРѕР·РёС†С–СЏ РЅРµ РІС–РґРїРѕРІС–РґР°С” Р¶РѕРґРЅРѕРјСѓ РІРёРїР°РґРєСѓ
    }
}
?>
