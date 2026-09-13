<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
error_reporting(E_ALL);
ini_set('display_errors', '1');
$time = date('Y-m-d H:i:s');
define('PMONAPP',true);
define('CONFIG',true);
require ENGINE_DIR.'database.php';
require ENGINE_DIR.'init.time.php';
require ENGINE_DIR.'init.pmon.php';
require ENGINE_DIR.'classes/sklad.class.php';
if(isset($confPMon['PMONAPP']) && !empty($confPMon['PMONAPP']) && $confPMon['PMONAPP'] != 1) {
	die("Not_support_access");
}
$ApiData = [];
$decodedata = $_REQUEST['m'] ?? null;
if(!$decodedata){
	die("Not_support_access_usr");	
}
$EasySklad = new EasySklad($pdo, $confPMon, $time);
$GetUsers = $EasySklad->Login($decodedata);
if(isset($GetUsers) && empty($GetUsers['id'])){
	die("Not_support_access_api");		
}
$action = $EasySklad->Go('do');
switch($action){
	case 'list': 
		$ApiData = $EasySklad->getListTovar($GetUsers);
		break;		
	case 'archive': 
		$startRaw = $_GET['timestart'] ?? '';
		$endRaw = $_GET['timeend'] ?? '';
		function isValidDate($date) {
			$d = DateTime::createFromFormat('Y-m-d', $date);
			return $d && $d->format('Y-m-d') === $date;
		}
		if (isValidDate($startRaw) && isValidDate($endRaw)) {
			$start = $startRaw;
			$end = $endRaw;
			if ($start > $end) {
				list($start, $end) = [$end, $start];
			}
		} else {
			$start = date('Y-m-01');
			$end = date('Y-m-t');
		}
		$ApiData = $EasySklad->getListArchive($GetUsers,$start,$end);
		break;	
	case 'saveakt': 
		if (!empty($_REQUEST['data'])) {
			$apk_data = json_decode($_REQUEST['data'], true);
			$jobs_id = (int)$_REQUEST['jobs'];
			$note_text = (!empty($_REQUEST['note']) ? $EasySklad->text($_REQUEST['note']) : 'app_note');
			if (!empty($apk_data)) {
				foreach ($apk_data as $k => $row) {
					$apk_data[$k]['count'] = (int)round((float)($row['count'] ?? 0));
				}
				$saved = $EasySklad->getAktTovar($GetUsers,$apk_data,$jobs_id,$note_text);
				$ApiData = $saved ? true : ['status' => 'error', 'msg' => 'Помилка списання'];
			}
		}
		break;	
	case 'getusers': 
		$ApiData = $EasySklad->getUsers();
		break;		
	case 'getakt': 
		if (isset($_REQUEST['id']) && (int)$_REQUEST['id'] > 0) {
			$id = (int)$_REQUEST['id'];
			$ApiData = $EasySklad->getAkt($id);
		}
		break;
	case 'getcounttovar': 		
		if (!isset($_REQUEST['id']) || !isset($_REQUEST['data'])) {
			$ApiData = false;
		}
		$tovarid = (int)$_REQUEST['id'];
		$ApiData = $EasySklad->getProductById($GetUsers,$tovarid);		
		break;
	case 'aktdelete': 		
		if (!isset($_REQUEST['id'])) {
			$ApiData = ['status' => 'error', 'msg' => 'Невірні параметри'];
			break;
		}
		$post_id = (int)$_REQUEST['id'];
		try {
			$pdo->beginTransaction();
			$cat = $pdo->prepare("SELECT * FROM sklad_akt WHERE id = :id AND user_id = :user_id LIMIT 1");
			$cat->execute([':id' => $post_id, ':user_id' => (int)$GetUsers['id']]);
			$result_akt = $cat->fetch(PDO::FETCH_ASSOC);
			if (!$result_akt) {
				$pdo->rollBack();
				$ApiData = ['status' => 'error', 'msg' => 'Акт не знайдено'];
				break;
			}

			$recover_user_id = (int)$result_akt['user_id'];
			$stmtAktTovar = $pdo->prepare("SELECT id, t_id, p_id, count FROM sklad_akt_tovar WHERE akt_id = :akt_id");
			$stmtAktTovar->execute([':akt_id' => (int)$result_akt['id']]);
			$aktTovars = $stmtAktTovar->fetchAll(PDO::FETCH_ASSOC);

			$stmtAcc = $pdo->prepare("SELECT id, quantity FROM sklad_accounting WHERE user_id = :user_id AND id = :id AND product_id = :product_id LIMIT 1");
			$stmtAccUpdate = $pdo->prepare("UPDATE sklad_accounting SET quantity = :quantity, status = 'enable' WHERE id = :id AND user_id = :user_id");
			$stmtAccInsert = $pdo->prepare("INSERT INTO sklad_accounting (status, product_id, user_id, quantity, action_type) VALUES ('enable', :product_id, :user_id, :quantity, 'assigned')");
			$stmtDelAktTovar = $pdo->prepare("DELETE FROM sklad_akt_tovar WHERE id = :id");

			foreach ($aktTovars as $tovar) {
				$tId = (int)($tovar['t_id'] ?? 0);
				$pId = (int)($tovar['p_id'] ?? 0);
				$countToReturn = (int)round(EasySklad::parseQuantity($tovar['count'] ?? 0));
				if ($pId <= 0 || $countToReturn <= 0) {
					$stmtDelAktTovar->execute([':id' => (int)$tovar['id']]);
					continue;
				}

				$stmtAcc->execute([':user_id' => $recover_user_id, ':id' => $tId, ':product_id' => $pId]);
				$row = $stmtAcc->fetch(PDO::FETCH_ASSOC);
				if ($row) {
					$currentQuantity = (int)round(EasySklad::parseQuantity($row['quantity']));
					$newQuantity = $currentQuantity + $countToReturn;
					$stmtAccUpdate->execute([
						':quantity' => $newQuantity,
						':id' => (int)$row['id'],
						':user_id' => $recover_user_id
					]);
				} else {
					$stmtAccInsert->execute([
						':product_id' => $pId,
						':user_id' => $recover_user_id,
						':quantity' => $countToReturn
					]);
				}
				$stmtDelAktTovar->execute([':id' => (int)$tovar['id']]);
			}

			$sql_akt = $pdo->prepare("DELETE FROM sklad_akt WHERE id = :id");
			$sql_akt->execute([':id' => (int)$result_akt['id']]);
			$sql_akt_log = $pdo->prepare("DELETE FROM sklad_log WHERE action = 'akt' AND akt_id = :akt_id");
			$sql_akt_log->execute([':akt_id' => $post_id]);
			$pdo->commit();
			$ApiData = true;
		} catch (Throwable $e) {
			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}
			$ApiData = ['status' => 'error', 'msg' => 'Помилка видалення акта'];
		}
		break;
	case 'updateakt':
		if (!isset($_REQUEST['id']) || !isset($_REQUEST['data'])) {
			$ApiData = ['status' => 'error', 'msg' => 'Невірні параметри'];
			break;
		}
		$akt_id = (int)$_REQUEST['id'];
		$data = json_decode($_REQUEST['data'], true);
		if (!$data || empty($data['akt']) || empty($data['tovars'])) {
			$ApiData = ['status' => 'error', 'msg' => 'Порожні дані'];
			break;
		}
		$akt    = $data['akt'];
		$tovars = $data['tovars'];
		$user_id = (int)$GetUsers['id'];
		$stmtUpdateAkt = $pdo->prepare("UPDATE sklad_akt SET name = :name, note = :note WHERE id = :id");
		$stmtUpdateAkt->execute([
			':name' => EasySklad::text($akt['name']),':note' => EasySklad::text($akt['note']),':id'   => $akt_id
		]);
		$stmtOld = $pdo->prepare("SELECT t_id, p_id, count FROM sklad_akt_tovar WHERE akt_id = :akt_id");
		$stmtOld->execute([':akt_id' => $akt_id]);
		$oldTovars = [];
		while ($row = $stmtOld->fetch(PDO::FETCH_ASSOC)) {
			$oldTovars[(int)$row['t_id']] = [
				'count' => (int)round(EasySklad::parseQuantity($row['count'])),
				'p_id' => (int)$row['p_id']
			];
		}
		$stmtGetAccounting = $pdo->prepare("SELECT id, quantity FROM sklad_accounting WHERE product_id = :p_id AND user_id = :user_id	LIMIT 1");
		$newTovars = [];
		foreach ($tovars as $tovar) {
			$p_id = (int)($tovar['product_id'] ?? $tovar['p_id'] ?? 0);
			if ($p_id <= 0) continue;
			$stmtGetAccounting->execute([':p_id' => $p_id, ':user_id' => $user_id]);
			$accounting = $stmtGetAccounting->fetch(PDO::FETCH_ASSOC);
			if (!$accounting) continue;
			$t_id = (int)$accounting['id'];
			$count = (int)round(EasySklad::parseQuantity($tovar['count'] ?? 0));
			if ($count <= 0.000001) continue;
			$newTovars[$t_id] = [
				'count'      => $count,
				'p_id'       => $p_id,
				'score'      => (!empty($tovar['score']) && $tovar['score'] === 'yes') ? 'yes' : 'no',
				'currentQty' => EasySklad::parseQuantity($accounting['quantity'])
			];
		}
		$deletedTovars = array_diff(array_keys($oldTovars), array_keys($newTovars));
		$stmtDelSingle = $pdo->prepare("DELETE FROM sklad_akt_tovar WHERE akt_id = :akt_id AND t_id = :t_id");
		$stmtCheckExist = $pdo->prepare("SELECT id FROM sklad_akt_tovar WHERE akt_id = :akt_id AND t_id = :t_id LIMIT 1");
		$stmtInsert = $pdo->prepare("INSERT INTO sklad_akt_tovar (p_id, akt_id, t_id, count, score, created_date) VALUES (:p_id, :akt_id, :t_id, :count, :score, :created_date)");
		$stmtUpdateAktTovar = $pdo->prepare("UPDATE sklad_akt_tovar SET count = :count, score = :score WHERE akt_id = :akt_id AND t_id = :t_id");
		$stmtUpdateQty = $pdo->prepare("UPDATE sklad_accounting SET quantity = :qty, status = CASE WHEN :qty2 <= 0.000001 THEN 'disable' ELSE 'enable' END WHERE id = :id AND user_id = :user_id");
		$stmtGetQty = $pdo->prepare("SELECT quantity FROM sklad_accounting WHERE id = :id AND user_id = :user_id LIMIT 1");
		$stmtInsertQty = $pdo->prepare("INSERT INTO sklad_accounting (status, product_id, user_id, quantity, action_type) VALUES ('enable', :product_id, :user_id, :quantity, 'assigned')");
		foreach ($deletedTovars as $deleted_t_id) {
			$oldItem = $oldTovars[$deleted_t_id] ?? ['count' => 0, 'p_id' => 0];
			$countToReturn = (int)round(EasySklad::parseQuantity($oldItem['count'] ?? 0));
			$pIdToReturn = (int)($oldItem['p_id'] ?? 0);
			$stmtGetQty->execute([':id' => $deleted_t_id, ':user_id' => $user_id]);
			$currentQtyRaw = $stmtGetQty->fetchColumn();
			if ($currentQtyRaw === false) {
				if ($pIdToReturn > 0 && $countToReturn > 0) {
					$stmtInsertQty->execute([':product_id' => $pIdToReturn, ':user_id' => $user_id, ':quantity' => $countToReturn]);
				}
			} else {
				$currentQty = (int)round(EasySklad::parseQuantity($currentQtyRaw));
				$newQty = $currentQty + $countToReturn;
				$stmtUpdateQty->execute([':qty'=> $newQty,':qty2'=> $newQty,':id'=> $deleted_t_id,':user_id' => $user_id]);
			}
			$stmtDelSingle->execute([':akt_id' => $akt_id,':t_id'   => $deleted_t_id]);
		}
		foreach ($newTovars as $t_id => $d) {
			$newCount   = (int)round(EasySklad::parseQuantity($d['count']));
			$p_id       = (int)$d['p_id'];
			$score      = $d['score'];
			$currentQty = EasySklad::parseQuantity($d['currentQty']);
			$oldCount   = (int)round(EasySklad::parseQuantity(($oldTovars[$t_id]['count'] ?? 0)));
			$diff = round($newCount - $oldCount, 2);
			if ($diff > 0.000001 && $diff > ($currentQty + 0.000001)) {
				$ApiData = [
					'status' => 'error','msg'=> "Недостатньо товару product_id=$p_id. Потрібно списати $diff, є $currentQty"
				];
				break;
			}
			$stmtCheckExist->execute([':akt_id' => $akt_id, ':t_id' => $t_id]);
			$exists = $stmtCheckExist->fetch(PDO::FETCH_ASSOC);
			if ($exists) {
				$stmtUpdateAktTovar->execute([
					':count'  => $newCount,':score'  => $score,':akt_id' => $akt_id,':t_id'   => $t_id
				]);
			} else {
				$stmtInsert->execute([
					':p_id'=> $p_id,':akt_id'=> $akt_id,':t_id'=> $t_id,':count'=> $newCount,':score'=> $score,':created_date'=> $time 
				]);
			}
			$newQty = round(max(0, $currentQty - $diff), 2);
			$stmtUpdateQty->execute([
				':qty'=> $newQty,':qty2'=> $newQty,':id'=> $t_id,':user_id' => $user_id
			]);
		}
		$stmtDelLogs = $pdo->prepare("DELETE FROM sklad_log WHERE action = 'akt' AND akt_id = :akt_id");
		$stmtDelLogs->execute([':akt_id' => $akt_id]);
		//$stmtLogAkt = $pdo->prepare("INSERT INTO sklad_log (action, product_id, from_user_id, to_user_id, akt_id, quantity, actor_id) VALUES ('akt', 0, :from_uid, 0, :akt_id, 0, :actor_id)");
		//$stmtLogAkt->execute([':from_uid' => $user_id,':akt_id'  => $akt_id,':actor_id' => $user_id]);
		$stmtLogWriteoff = $pdo->prepare("INSERT INTO sklad_log (action, product_id, from_user_id, to_user_id, akt_id, quantity, actor_id) VALUES ('akt', :pid, :from_uid, 0, :akt_id, :q, :actor_id)");
		foreach ($newTovars as $t_id => $d) {
			$stmtLogWriteoff->execute([
				':pid'=> (int)$d['p_id'],':from_uid' => $user_id,':akt_id'   => $akt_id,':q'=> EasySklad::parseQuantity($d['count']),':actor_id' => $user_id
			]);
		}
		if (!isset($ApiData)) {
			$ApiData = ['status' => 'success', 'msg' => 'Акт оновлено'];
		}
		break;
	case 'moderation': 
		$ApiData = $EasySklad->getMyModeration();
		break;	
	case 'return': 		
		$ApiData = $EasySklad->ReturnTovar($_REQUEST);
		break;	
	case 'getnomer': 		
			if(!empty($_REQUEST['search'])) {
				$search = $EasySklad->cleanLogin($_REQUEST['search']);
				if(!empty($search)){
					$ApiData = $EasySklad->getSearchNomer($search);
				}
			}
		break;	
	case 'gettovar': 		
			if(!empty($_REQUEST['search'])) {
				$search = $EasySklad->cleanText($_REQUEST['search']);
				if(!empty($search)){
					$ApiData = $EasySklad->getSearchName($search);
				}
			}
		break;		
	case 'getjob': 		
		$ApiData = $EasySklad->getListJob();
		break;		
	case 'activity': 		
		$ApiData = $EasySklad->getMyStatus();
		break;	
	case 'transfer': 
		if (!empty($_REQUEST['data'])) {
			$apk_data = json_decode($_REQUEST['data'], true);
			if (!empty($apk_data)) {
				$EasySklad->Transfer($apk_data);
				$ApiData = true;
			}
		}
	break;
	case 'login': 		
		$ApiData = $EasySklad->getUser($GetUsers);			
	break;
}
$response = array('app' => true, 'data' => $ApiData);
$EasySklad->Json($response);
?>
