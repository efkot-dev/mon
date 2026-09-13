<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
require_once ENGINE_DIR.'functions/editor.php';
$act = isset($_GET['act'])?Clean::text($_GET['act']):null;
$type = isset($_GET['type'])?Clean::text($_GET['type']):null;
$id = isset($_REQUEST['id'])?Clean::int($_REQUEST['id']):null;
$ponid = isset($_REQUEST['ponid'])?Clean::int($_REQUEST['ponid']):null;
$top = isset($_REQUEST['top'])?Clean::int($_REQUEST['top']):40;
$left = isset($_REQUEST['left'])?Clean::int($_REQUEST['left']):40;
$elementid = isset($_REQUEST['elementid'])?Clean::int($_REQUEST['elementid']):10;
$element = isset($_REQUEST['element'])?Clean::str($_REQUEST['element']):false;
$action = isset($_REQUEST['action'])?Clean::str($_REQUEST['action']):false;
switch($act){
	case 'get': 
		if($type=='switch'){
			echo json_encode(generate_data($id));
		}elseif($type=='onu'){
			echo json_encode(generate_onu($pdo,$id));
		}elseif($type=='vok'){
			echo json_encode(generate_vok($id));
		}elseif($type=='connect'){
			echo json_encode(generate_connect($id));
		}elseif($type=='cross'){
			echo json_encode(generate_cross($id));
		}elseif($type=='planar'){
			echo json_encode(generate_planars($id));
		}elseif($type=='splitter'){
			echo json_encode(generate_splitters($id));
		}
	break;	
	case 'voke': 	
		if(!empty($element)){
			$note = $db->Fast('kabel_connector','*',['vokid'=>$element, 'elementid' => $elementid]);
			$isFiberConnector = (bool)preg_match('~^v\d+m\d+c\d+$~', (string)$element);
			if ($isFiberConnector) {
				echo'<div class="title_block">Редагування волокна</div>';
				echo'<form autocomplete="off" autofill="false" >			
					<input type="hidden" name="do" value="fiber">
					<input type="hidden" name="act" value="vokconnect">
					<input type="hidden" name="element" value="'.$element.'">
					<input type="hidden" name="elementid" value="'.$elementid.'">
					<div class="form_pon_map">
						<div class="colona">
							<div class="alt">
								Out <input type="radio" class="btn-check" name="signal" value="out" '.($note['line']=='out' ? 'checked':'').'>
							</div>
							<div class="alt">
								In <input type="radio" class="btn-check" name="signal" value="in" '.($note['line']=='in' ? 'checked':'').'>
							</div>						
							<div class="alt">
								Clear <input type="radio" class="btn-check" name="signal" value="none" '.($note['line']=='none' ? 'checked':'').'>
							</div>
						</div>
						<div class="colona">
							<textarea name="note" class="input1" rows="7" style="height: 70px;">'.$note['note'].'</textarea>
						</div>
						<div class="colona"><button type="submit" class="btn"><i class="bi bi-save"></i>'.$lang['save'].'</button></div>
					</div>			
				</form>';
			} else {
				echo'<div class="title_block">Коментар порту</div>';
				echo'<form autocomplete="off" autofill="false" >			
					<input type="hidden" name="do" value="fiber">
					<input type="hidden" name="act" value="vokconnect">
					<input type="hidden" name="element" value="'.$element.'">
					<input type="hidden" name="elementid" value="'.$elementid.'">
					<input type="hidden" name="signal" value="none">
					<div class="form_pon_map">
						<div class="colona" style="width:100%;">
							<textarea name="note" class="input1" rows="7" style="height: 90px;" placeholder="Наприклад: аплінк на OLT / резерв / абонентський порт">'.$note['note'].'</textarea>
						</div>
						<div class="colona"><button type="submit" class="btn"><i class="bi bi-save"></i>'.$lang['save'].'</button></div>
					</div>			
				</form>';
			}
			
		}
	break;	
	case 'vols': 		
		$vols = $db->Fast('ponmap_connect','*',['id'=>$id]);
		if(!empty($vols['id'])){
			echo'<div class="title_block">Редагування волокна</div>';
			echo'<a href="#" onclick="delvols('.$vols['elementid'].','.$vols['id'].')" class="disconnect"><img src="../style/img/close.png">Розірвати комутацію</a>';
			echo'<form autocomplete="off" autofill="false" >			
				<input type="hidden" name="do" value="fiber">
				<input type="hidden" name="act" value="updateconnect">
				<input type="hidden" name="id" value="'.$vols['id'].'">
				<input type="hidden" name="c1" value="'.$vols['connect1'].'">
				<input type="hidden" name="c2" value="'.$vols['connect2'].'">
				<div class="form_pon_map">
					<div class="colona">
						<div class="alt-n">Колір волокна</div>
						<div class="alt-s"><input type="color" name="color" id="colorPicker" autocomplete="off" value="'.$vols['color'].'"></div>
					</div>
					<div class="colona">
					<div class="alt-n">Тивщина вол</div>
						<select class="form-select form-select-sm aiform"  id="border" name="border">
							<option value="2" '.($vols['border']==2 ? 'selected':'').'>2dp</option>
							<option value="3" '.($vols['border']==3 ? 'selected':'').'>3dp</option>
							<option value="4" '.($vols['border']==4 ? 'selected':'').'>4dp</option>
							<option value="5" '.($vols['border']==5 ? 'selected':'').'>5dp</option>
							<option value="6" '.($vols['border']==6 ? 'selected':'').'>6dp</option>
						</select>
					</div>
					<div class="colona"><button type="submit" class="btn"><i class="bi bi-save"></i>'.$lang['save'].'</button></div>
				</div>			
			</form>';
		}
	break;	
	case 'vok': 
		$vols = $db->Fast('kabel_position','*',['connectid'=>$element,'elementid'=>$elementid]);
		if(!empty($vols['id'])){
			echo'<div class="title_block">Редагувати ВОК</div>';
			echo'<form autocomplete="off" autofill="false" >			
				<input type="hidden" name="do" value="fiber">
				<input type="hidden" name="act" value="updatevok">
				<input type="hidden" name="id" value="'.$vols['id'].'">
				<div class="form_pon_map">
					<div class="colona">
						<div class="alt">
							<img src="../style/img/vok-out.png"><input type="radio" class="btn-check" name="direction" value="left" '.($vols['position']=='left' ? 'checked':'').'>
						</div>
						<div class="alt">
							<img src="../style/img/vok-in.png"><input type="radio" class="btn-check" name="direction" value="right" '.($vols['position']=='right' ? 'checked':'').'>
						</div>					
						<div class="alt">
							<img src="../style/img/vok-top-in.png"><input type="radio" class="btn-check" name="direction" value="top" '.($vols['position']=='top' ? 'checked':'').'>
						</div>
					</div>
					<div class="colona">
						<button type="submit" class="btn"><i class="bi bi-save"></i>'.$lang['save'].'</button>
					</div>
				</div>			
			</form>';			
		}
	break;	
	case 'connect': 	
		$c1 = isset($_REQUEST['c1'])?Clean::text($_REQUEST['c1']):null;
		$c2 = isset($_REQUEST['c2'])?Clean::text($_REQUEST['c2']):null;
		$p1 = isset($_REQUEST['p1'])?Clean::int($_REQUEST['p1']):null;
		$p2 = isset($_REQUEST['p2'])?Clean::int($_REQUEST['p2']):null;
		echo'<div class="title_block">Зварка</div>';
		echo'<form autocomplete="off" autofill="false" >			
			<input type="hidden" name="do" value="fiber">
			<input type="hidden" name="act" value="addconnect">
			<input type="hidden" name="id" value="'.$id.'">
			<input type="hidden" name="c1" value="'.$c1.'">
			<input type="hidden" name="p1" value="'.$p1.'">
			<input type="hidden" name="p2" value="'.$p2.'">
			<input type="hidden" name="c2" value="'.$c2.'">
			<div class="form_pon_map">
				<div class="colona">
					<div class="alt-n">Колір волокна</div>
					<div class="alt-s"><input type="color" name="color" id="colorPicker" autocomplete="off" value="#ffd400"></div>
				</div>
				<div class="colona">
					<select class="form-select form-select-sm" id="border" name="border">
						<option value="3">товщина 3мм</option>
						<option value="4">товщина 4мм</option>
						<option value="5">товщина 5мм</option>
						<option value="6">товщина 6мм</option>
					</select>
				</div>
				<div class="colona"><button type="submit" class="btn"><i class="bi bi-save"></i>'.$lang['save'].'</button></div>
			</div>			
		</form>';
	break;	
	case 'editelement':
		$stmt = $pdo->prepare("SELECT * FROM ponmap_elements WHERE id = :id LIMIT 1");
		$stmt->execute([':id' => $id]);
		$element = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!empty($element['id'])) {
			$elementName = htmlspecialchars((string)($element['name'] ?? ''), ENT_QUOTES, 'UTF-8');
			$elementDesc = htmlspecialchars((string)($element['description'] ?? ''), ENT_QUOTES, 'UTF-8');
			echo '<div class="dir-chooser" data-id="'.(int)$element['id'].'">';
			#if ($element['type'] === 'splitter') {
				echo '
					<a href="#" class="dir-btn" data-dir="top"><i class="fi fi-rr-arrow-up"></i></a>
					<a href="#" class="dir-btn" data-dir="down"><i class="fi fi-rr-arrow-down"></i></a>
					<a href="#" class="dir-btn" data-dir="left"><i class="fi fi-rr-arrow-left"></i></a>
					<a href="#" class="dir-btn" data-dir="right"><i class="fi fi-rr-arrow-right"></i></a>';
			#}elseif($action=='planar'){
			#}
			echo '<a href="#" onclick="deletelement('.(int)$element['id'].','.(int)$ponid.')"><i class="fi fi-rr-cross"></i></a>';
			echo '</div>';
			if ($element['type'] === 'switch' || $element['type'] === 'cross') {
				echo '
				<form autocomplete="off" autofill="false">
					<input type="hidden" name="do" value="fiber">
					<input type="hidden" name="act" value="updateelementmeta">
					<input type="hidden" name="id" value="'.(int)$element['id'].'">
					<div class="form_pon_map">
						<div class="colona" style="width:100%;">
							<div class="alt-n">Підпис елемента</div>
							<input class="input1" type="text" name="element_name" value="'.$elementName.'" autocomplete="off" style="margin-bottom:6px;height: 30px;">
							<div class="alt-n">Коментар</div>
							<textarea class="input1" name="element_description" rows="4" style="height:80px;">'.$elementDesc.'</textarea>
						</div>
						<div class="colona"><button type="submit" class="btn"><i class="bi bi-save"></i>'.$lang['save'].'</button></div>
					</div>
				</form>';
			}
	  }
	  exit;
	break;
	case 'element':
		$types_added = ( $action=='splitter' || $action=='planar' ? 'addspliters' : ($action=='switch' || $action=='cross' ? 'adddevice' : 'addonus'));	
		echo'<form autocomplete="off" autofill="false" >			
			<input type="hidden" name="do" value="fiber">
			<input type="hidden" name="act" value="'.$types_added.'">
			<input type="hidden" name="id" value="'.$id.'">
			<input type="hidden" name="top" value="'.$top.'">
			<input type="hidden" name="left" value="'.$left.'">
			<div class="form_pon_map">
				<div class="colona">';
				if($action=='splitter'){
				echo'<select class="form-select form-select-sm" id="splitter" name="splitter">
					<option value="procent_50/50">50/50</option>
					<option value="procent_45/55">45/55</option>
					<option value="procent_40/60">40/60</option>
					<option value="procent_35/65">35/65</option>
					<option value="procent_30/70">30/70</option>
					<option value="procent_25/75">25/75</option>
					<option value="procent_20/80">20/80</option>
					<option value="procent_15/85">15/85</option>
					<option value="procent_10/90">10/90</option>
					<option value="procent_5/95">5/95</option>
					</select>';
			}elseif ($action=='onu') {
					$sql = "SELECT d.*, o.idonu, o.status, o.rx, o.dist, o.offline, o.online, o.inface, o.type FROM onusdata d LEFT JOIN onus o ON (o.mac = d.onukey OR o.sn = d.onukey) WHERE d.ponelement = :ponelement ORDER BY d.onukey";
					$stmt = $pdo->prepare($sql);
					$stmt->execute([':ponelement' => $id]);
					$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
					echo '<div class="onu-picker">';
					if ($rows && count($rows)) {
						echo '<div class="list-onus">';
						foreach ($rows as $r) {
							$onukey = htmlspecialchars($r['onukey']);
							$label = $onukey !== '' ? $onukey : 'ONU';
							$status = htmlspecialchars($r['status']);
							$rx = htmlspecialchars($r['rx']);
							$dist = htmlspecialchars($r['dist']);
							$keyPon = 'onu_id_'.$r['idonu'];
							$stmt = $pdo->prepare("SELECT id FROM ponmap_elements WHERE type='onu' AND name=:name LIMIT 1");
							$stmt->execute([':name' => $keyPon]);
							$pon = $stmt->fetch(PDO::FETCH_ASSOC);
							echo '<label class="onu-row" style="display:flex;align-items:center;gap:8px;margin:4px 0;">';
							echo '<input type="checkbox" name="onus[]" value="'.$r['idonu'].'" '.($pon['id'] > 0 ? 'checked disabled' : '').'>';
							echo '<span class="onu-key" style="min-width:160px;">'.($pon['id'] > 0 ? '<font color="green">' : '').''.$label.''.($pon['id'] > 0 ? '</font>' : '').'</span>';
							echo '<span class="onu-meta" style="font-size:12px;color:#6b7b87;">'
								. 'status: ' . ($status !== '' ? $status : '-') 
								. ' · rx: ' . ($rx !== '' ? $rx : '-') 
								. ' · dist: ' . ($dist !== '' ? $dist : '-') 
								. '</span>';
							echo '</label>';
						}
						echo '</div>';
					} else {
						echo '<div class="empty">ONU не знайдені</div>';
					}
					echo '</div>';
				}elseif($action=='planar'){
				echo'<select class="form-select form-select-sm" id="splitter" name="splitter">
					<option value="planar_1/2">1/2</option>
					<option value="planar_1/3">1/3</option>
					<option value="planar_1/4">1/4</option>
					<option value="planar_1/6">1/6</option>
					<option value="planar_1/8">1/8</option>
					<option value="planar_1/12">1/12</option>
					<option value="planar_1/16">1/16</option>
					<option value="planar_1/24">1/24</option>
					<option value="planar_1/32">1/32</option>
					<option value="planar_1/64">1/64</option>
					<option value="planar_1/128">1/128</option>
					<option value="planar_1/256">1/256</option>
					</select>';
				}elseif($action=='switch' || $action=='cross'){
					echo '<input type="hidden" name="device_type" value="'.($action=='switch' ? 'switch' : 'cross').'">';
					echo '<input class="input1" type="text" name="device_name" placeholder="'.($action=='switch' ? 'Switch' : 'Cross').'" value="'.($action=='switch' ? 'Switch' : 'Cross').'" autocomplete="off" style="margin-bottom:6px;height: 30px;">';
					echo '<textarea class="input1" name="device_description" rows="3" style="height:60px;margin-bottom:6px;" placeholder="Коментар"></textarea>';
					echo '<input class="input1" type="number" min="2" max="128" name="ports_count" value="'.($action=='switch' ? '24' : '12').'" autocomplete="off">';
				}
				echo'</div>
				<input type="hidden" name="direction" value="left" autocomplete="off" disable>
				<div class="colona"><button type="submit" class="btn"><i class="bi bi-save"></i>'.$lang['save'].'</button></div>
			</div>			
		</form>';
	break;
	case 'trace': {
		header('Content-Type: application/json; charset=utf-8');
		$start = isset($_POST['conn']) ? trim($_POST['conn']) : '';
		$max = isset($_POST['max'])  ? (int)$_POST['max']   : 200;
		if ($start === '') { echo json_encode(['ok'=>false,'error'=>'empty connector id']); exit; }
		$getEdges = function (PDO $pdo, string $connId) {
			$sql = "SELECT id, p1, p2, connect1, connect2 FROM ponmap_connect WHERE connect1=? OR connect2=?";
			$st = $pdo->prepare($sql);
			$st->execute([$connId, $connId]);
			return $st->fetchAll(PDO::FETCH_ASSOC);
		};
		$getElem = function (PDO $pdo, int $raw) {
			$st = $pdo->prepare("SELECT id, elementid, name, type FROM ponmap_elements WHERE elementid=? LIMIT 1");
			$st->execute([$raw]);
			$row = $st->fetch(PDO::FETCH_ASSOC);
			if ($row) return $row;
			$st = $pdo->prepare("SELECT id, elementid, name, type FROM ponmap_elements WHERE id=? LIMIT 1");
			$st->execute([$raw]);
			return $st->fetch(PDO::FETCH_ASSOC) ?: null;
		};
		$getFiberSides = function (PDO $pdo, int $fiberId) {
			$sql = "SELECT kp.elementid, pe.name FROM kabel_position kp JOIN ponelement pe ON pe.id = kp.elementid WHERE kp.connectid=? ORDER BY kp.id ASC";
			$st = $pdo->prepare($sql);
			$st->execute([$fiberId]);
			return $st->fetchAll(PDO::FETCH_ASSOC);
		};
		$startEdges = $getEdges($pdo, $start);
		if (empty($startEdges)) {
			echo json_encode([
				'ok' => true,
				'start' => $start,
				'has_commutation' => false,
				'endpoints' => [],
				'paths' => []
			], JSON_UNESCAPED_UNICODE);
			exit;
		}
		$buildNode = function(PDO $pdo, string $connId, ?int $preferNotElemId) use ($getEdges, $getElem, $getFiberSides) {
			if (preg_match('~^v(\d+)m\d+c\d+$~', $connId, $m)) {
				$fiberId = (int)$m[1];
				if ($fiberId > 0) {
					$sides = $getFiberSides($pdo, $fiberId);
					if ($sides) {
						$row = null;
						if ($preferNotElemId !== null) {
							foreach ($sides as $s) {
								if ((int)$s['elementid'] !== $preferNotElemId) { $row = $s; break; }
							}
						}
						if (!$row) $row = $sides[0];
						return ['connector' => $connId,'elementid' => (int)$row['elementid'],'element' => (string)$row['name'],'type' => 'ponbox'];
					}
				}
			}
			$edges = $getEdges($pdo, $connId);
			if ($edges) {
				$e = $edges[0];
				$raw = ((string)$e['connect1'] === (string)$connId) ? (int)$e['p1'] : (int)$e['p2'];
				if ($raw) {
					if ($elem = $getElem($pdo, $raw)) {
						return ['connector'=> $connId,'elementid'=> (int)$elem['elementid'],'element'=> (string)$elem['name'],'type'=> (string)$elem['type']];
					}
				}
			}
			return [
				'connector'=> $connId,'elementid'=> null,'element'=> '(n/a)','type'=> '',
			];
		};
		$pathConns = [];
		$visitedConn = [];
		$prevConn = null;
		$prevElemId = null;
		$cur = $start;
		for ($i=0; $i<$max; $i++) {
			if (isset($visitedConn[$cur])) break;
			$visitedConn[$cur] = true;
			$pathConns[] = $cur;
			$node = $buildNode($pdo, $cur, $prevElemId);
			if ($node['type'] === 'splitter' || $node['type'] === 'planar') {
				break;
			}
			if ($node['elementid'] !== null) { $prevElemId = (int)$node['elementid']; }
			$edges = $getEdges($pdo, $cur);
			if (!$edges) break;
			$neighbors = [];
			foreach ($edges as $e) {
				$a = (string)$e['connect1']; $b = (string)$e['connect2'];
				$neighbors[] = ($a === $cur) ? $b : $a;
			}
			$neighbors = array_values(array_filter($neighbors, fn($x)=>$x!==''));
			if (!$neighbors) break;
			$next = null;
			foreach ($neighbors as $n) { if ($n !== $prevConn) { $next = $n; break; } }
			if ($next === null) break;
			$prevConn = $cur;
			$cur = $next;
		}
		$nice = [];
		$prevElemId = null;
		foreach ($pathConns as $cid) {
			$n = $buildNode($pdo, $cid, $prevElemId);
			$nice[] = $n;
			if ($n['elementid'] !== null) $prevElemId = (int)$n['elementid'];
		}
		$hasCommutation = count($pathConns) > 1;
		echo json_encode([
			'ok' => true,
			'start' => $start,
			'has_commutation' => $hasCommutation,
			'endpoints' => ($hasCommutation ? [ end($pathConns) ?: $start ] : []),
			'paths' => ($hasCommutation ? [ $nice ] : [])
		], JSON_UNESCAPED_UNICODE);
		exit;
	}	
}
exit;
?>
