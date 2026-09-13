<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

class EasySklad {
    private string $repl = '/[^a-zA-Z0-9@#$!%а-щьєі-їйк-юяА-ЩЬЄІ-ЇЙК-ЮЯ\s\-\.,?!\'=]/u';
    private PDO $pdo;
    private array $config;
    private $my_user;
    private $users;
    private $salt = '#@()DIJK#)(F#&*()DS#@JKS)@(I()#@DU)*(&@#)(#U)J';
    private $clock;
    private $minpass = 5;
    public function __construct($pdo, $config, $time) {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->clock = $time;
    }
    public function SelectUsers($login, $pass) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :login");
        $stmt->execute(['login' => $login]);
        $data_usr = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data_usr ?: null;
    }
    public function cleanText(string $input): string {
        $cleaned = preg_replace($this->repl, '', $input);
        return preg_replace('/\s+/', ' ', trim($cleaned));
    }
	private function checkPassword($password, $hash) {
		if($this->encrypt($password) === $hash){
			return true;
		}else{
			return false;
		}
	}
	public function encrypt($value) {
		$enc = md5($this->salt . md5($value));
		return sha1($enc);
	}
    public function cleanLogin(string $input): string {
        return preg_replace('/[^a-zA-Z0-9@#$!%_\-]/', '', $input);
    }
    public function Login($decodedata) {
        if (!class_exists('Auth') && defined('ENGINE_DIR')) {
            $authClassFile = ENGINE_DIR . 'classes/users.class.php';
            if (is_file($authClassFile)) {
                require_once $authClassFile;
            }
        }

        $usr = $this->DecodeLoginPassword($decodedata);
        if (!$usr) {
            return ['id' => 0];
        }

        $login = trim(mb_strtolower((string)($usr['login'] ?? '')));
        $pass = (string)($usr['password'] ?? '');
        if ($login === '' || $pass === '') {
            return ['id' => 0];
        }

        if (class_exists('Auth')) {
            global $auth;
            if (!isset($auth) || !($auth instanceof Auth)) {
                $setup = isset($GLOBALS['config']) && is_array($GLOBALS['config']) ? $GLOBALS['config'] : [];
                $conf = isset($GLOBALS['confPMon']) && is_array($GLOBALS['confPMon']) ? $GLOBALS['confPMon'] : [];
                $auth = new Auth($this->pdo, $setup, $conf);
            }

            if ($auth->mobileapp($login, $pass)) {
                $data = $auth->app_getuser();
                if (!empty($data['id'])) {
                    $this->myUsers($data);
                    return array(
                        'id' => $data['id'],
                        'username' => $data['username'] ?? $login,
                        'class' => $data['class'] ?? null,
                        'android' => $data['android'] ?? '',
                        'name' => $data['name'] ?? null,
                    );
                }
            }
            return ['id' => 0];
        }

        if (strlen($pass) < $this->minpass) {
            return ['id' => 0];
        }
        $data = $this->SelectUsers($login, $pass);
        if (!$data) {
            return ['id' => 0];
        }
		if (isset($data['password']) && $this->checkPassword($pass, $data['password'])) {
			$this->myUsers($data);
			return array(
				'id' => $data['id'],
				'username' => $data['username'],
				'class' => $data['class'],
				'android' => $data['android'],
				'name' => $data['name'] ?? null,
			);
		}
		return ['id' => 0];
    }
    public function Go($key, $default = null) {
		$source = $_REQUEST;    
		if (isset($source[$key])) {
			$value = trim(strip_tags(stripcslashes($source[$key])));
			return !empty($value) ? $value : $default;
		}
		return $default;
	}
    public function Json($response) {
		header('Content-Type: application/json');
		echo json_encode($response);	
		die;
	}
    public function getUsers(){
		$sql_list_usr = $this->pdo->prepare("SELECT id, username, class FROM users");
		$sql_list_usr->execute();
		$users = $sql_list_usr->fetchAll(PDO::FETCH_ASSOC);
		return $users;		
	}      
	public function getListJob(){
		$sql_list_job = $this->pdo->prepare("SELECT id, name FROM sklad_jobs");
		$sql_list_job->execute();
		$data = $sql_list_job->fetchAll(PDO::FETCH_ASSOC);
		return $data;		
	}  	
	public function getAkt($akt_id) {
		$stmt_akt = $this->pdo->prepare("
			SELECT a.id, a.name, a.note, a.jobs, a.user_id, a.created_date, 
				   a.moderator_id, a.moderation, a.checked_date,
				   u.username, u.class,
				   j.name AS jobs_name,
				   mu.username AS moderator_name, mu.class AS moderator_class
			FROM sklad_akt a
			LEFT JOIN users u ON a.user_id = u.id
			LEFT JOIN sklad_jobs j ON a.jobs = j.id
			LEFT JOIN users mu ON a.moderator_id = mu.id
			WHERE a.id = ?
		");
		$stmt_akt->execute([$akt_id]);
		$akt = $stmt_akt->fetch(PDO::FETCH_ASSOC);
		$stmt_tovars = $this->pdo->prepare("
			SELECT t.p_id, t.count, t.score, 
				   s.name AS tovar_name, s.category_id, s.sub_cat_id, s.oblik, s.unit, 
				   s.quantity AS quantity,  -- ← ключова зміна
				   s.description, s.mac, s.price, 
				   s.date_added, s.inventory_number, s.status
			FROM sklad_akt_tovar t
			LEFT JOIN sklad_tovar s ON t.p_id = s.id
			WHERE t.akt_id = ?
		");
		$stmt_tovars->execute([$akt_id]);
		$tovars = $stmt_tovars->fetchAll(PDO::FETCH_ASSOC);
		return json_encode([
			'akt' => $akt,
			'tovars' => $tovars
		]);
	}   
	public function ReturnTovar($temp) {
		$tovar_nomer = (!empty($temp['tovar_nomer']) ? $this->cleanText($temp['tovar_nomer']) : false);
		$name = (!empty($temp['tovar_name']) ? $this->cleanText($temp['tovar_name']) : false);
		$tovar_mac_address = (!empty($temp['tovar_mac_address']) ? $this->cleanText($temp['tovar_mac_address']) : false);
		$description = (!empty($temp['tovar_description']) ? $this->cleanText($temp['tovar_description']) : false);
		$quantity = (!empty($temp['tovar_quantity']) ? $this->cleanText($temp['tovar_quantity']) : false);
		$status = (!empty($temp['tovar_status']) ? $this->cleanText($temp['tovar_status']) : false);
		$condition = (!empty($temp['tovar_condition']) ? $this->cleanText($temp['tovar_condition']) : false);
		$temp_image = (!empty($temp['tovar_img']) ?  $temp['tovar_img'] : false);
		if (!empty($temp_image)) {
			$targetDir = ROOT_DIR . "/file/product/";
			if (!file_exists($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
				error_log('Не вдалося створити каталог: ' . $targetDir);
				return false;
			}
			$images = explode(',', $temp_image);
			$response = ['images' => []];
			foreach ($images as $base64Image) {
				if (strpos($base64Image, 'data:image/') === 0) {
					$base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
				}
				$image = base64_decode($base64Image);
				if ($image === false) {
					error_log('Не вдалося декодувати Base64');
					continue;
				}
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mimeType = finfo_buffer($finfo, $image);
				finfo_close($finfo);
				if (strpos($mimeType, 'image/') === false) {
					error_log('Отримано не зображення: ' . $mimeType);
					continue;
				}
				$ext = str_replace('image/', '', $mimeType);
				if (!in_array($ext, ['jpeg', 'png', 'gif', 'webp'])) {
					error_log('Непідтримуваний формат зображення: ' . $ext);
					continue;
				}
				$filename = $targetDir . uniqid('img_', true) . '.' . $ext;
				if (file_put_contents($filename, $image) === false) {
					error_log('Не вдалося записати файл: ' . $filename);
					continue;
				}
				$response['images'][] = $filename;
			}
			return $response;
		}
		return true;
	}

	public function getSearchNomer($search) {
		$sql = "SELECT id, name, status FROM sklad_tovar 
        WHERE (inventory_number LIKE :search_in OR mac LIKE :search_mac)";
		$stmt = $this->pdo->prepare($sql);
		$searchTerm = "%{$search}%";
		$stmt->bindParam(':search_in', $searchTerm, PDO::PARAM_STR);
		$stmt->bindParam(':search_mac', $searchTerm, PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}
	public function getSearchName($search) {
		$sql = "SELECT id, name, status FROM sklad_tovar 
				WHERE name LIKE :search";
		$stmt = $this->pdo->prepare($sql);
		$searchTerm = "%{$search}%";
		$stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}  
	public function myUsers($usr){
		$this->my_user = $usr;		
	}
	public function getProductById($usr, $product_id) {
		$query = "
			SELECT 
				sa.id as ac_id, 
				sa.product_id, 
				sa.user_id, 
				sa.quantity, 
				sa.status, 
				sa.action_date, 
				st.name AS tovar_name, 
				st.category_id, 
				st.sub_cat_id, 
				ssc.name AS sub_category_name, 
				st.oblik, 
				st.unit, 
				st.price, 
				st.price_pdv, 
				st.inventory_number as sn, 
				st.mac, 
				st.status AS product_status
			FROM sklad_accounting sa
			JOIN sklad_tovar st ON sa.product_id = st.id
			LEFT JOIN sklad_sub_category ssc ON st.sub_cat_id = ssc.id
			WHERE sa.user_id = :user_id 
			  AND sa.product_id = :product_id
			  AND sa.status = 'enable'
			LIMIT 1
		";
		$sql = $this->pdo->prepare($query);
		$sql->execute(['user_id' => $usr['id'], 'product_id' => $product_id]);
		$product = $sql->fetch(PDO::FETCH_ASSOC);
		return $product;
	}
    public function getListTovar($usr){
		$query = "
			SELECT 
				sa.id as ac_id, 
				sa.product_id, 
				sa.user_id, 
				sa.quantity, 
				sa.status, 
				sa.action_date, 
				st.name AS tovar_name, 
				st.category_id, 
				st.sub_cat_id, 
				ssc.name AS sub_category_name, 
				st.oblik, 
				st.unit, 
				st.price, 
				st.inventory_number as sn, 
				st.mac, 
				st.status AS product_status
			FROM sklad_accounting sa
			JOIN sklad_tovar st ON sa.product_id = st.id
			LEFT JOIN sklad_sub_category ssc ON st.sub_cat_id = ssc.id
			WHERE sa.user_id = :user_id AND sa.status = 'enable'
		";
		$sql_list = $this->pdo->prepare($query);
		$sql_list->execute(['user_id' => $usr['id']]);
		$products = $sql_list->fetchAll(PDO::FETCH_ASSOC);
		return $products;
	}
	/*
		action: Отримуємо кількість записів, які не підтверджені
		sql: my_akt
	*/
    public function getMyStatus() {
		$user_id = $this->my_user['id'];
		$sql_not_conf = $this->pdo->prepare("SELECT COUNT(id) FROM sklad_akt WHERE moderation = 'no' AND user_id = :user_id");
		$sql_not_conf->execute(['user_id' => $user_id]);
		return [
			'not_conf' => $sql_not_conf->fetchColumn()
		];
	}
	public function getAktTovar($usr,$data,$jobs_id,$note_text) {
		try {
			$this->pdo->beginTransaction();
			$akt_id = $this->getNewAkt($usr,$jobs_id,$note_text);
			if ($akt_id <= 0) {
				$this->pdo->rollBack();
				return false;
			}
			$update = [];
			foreach ($data as $tovar) {
				$count = self::parseQuantity($tovar['count'] ?? 0);
				$pid = (int)($tovar['pid'] ?? 0);
				$id = (int)($tovar['id'] ?? 0);
				if (!self::isPositiveQuantity($count) || $pid <= 0 || $id <= 0) {
					continue;
				}
				$update[$id] = array('pid' => $pid, 'id' => $id, 'count' => $count);
				$input_temp = $this->pdo->prepare("INSERT INTO sklad_akt_tovar (p_id, t_id, akt_id, count, score, created_date) VALUES (:p_id, :t_id, :akt_id, :count, :score, :created_date)");
				$input_temp->execute([
					':p_id' => $pid,
					':t_id' => $id,
					':akt_id' => $akt_id,
					':count' => $count,
					':score' => ((!empty($tovar['score']) && $tovar['score'] === 'yes') || !empty($tovar['calc'])) ? 'yes' : 'no',
					':created_date' => $this->clock
				]);
			}
			if (empty($update) || $this->UpdateTovar($update, $usr, $akt_id) === false) {
				$this->pdo->rollBack();
				return false;
			}
			$this->pdo->commit();
			return true;
		} catch (Throwable $e) {
			if ($this->pdo->inTransaction()) {
				$this->pdo->rollBack();
			}
			return false;
		}
	}
	/*
		action: Створення АКТу
		sql: sklad_akt
	*/
    public function getNewAkt($usr, $jobsid, $note){
		$query = "INSERT INTO sklad_akt (name, jobs, user_id, created_date, note, moderation) VALUES 
		(:name, :jobs, :user_id, :created_date, :note, :moderation)";
        $added = $this->pdo->prepare($query);
        $added->execute([
			':name' => 'app', 
			':jobs' => $jobsid, 
			':moderation' => 'yes', 
			':note' => $note, 
			':user_id' => $usr['id'], 
			':created_date' => $this->clock]);
		return $this->pdo->lastInsertId();
	}
	/*
		action: Запис товар в таблицю для модерації складовщиком
		sql: sklad_temp
	*/
	public function UpdateTovar($update, $usr, $akt_id){
		$user_id  = (int)$usr['id'];
		$akt_id   = (int)$akt_id;
		$stmtLogWriteoff = $this->pdo->prepare("
			INSERT INTO sklad_log (action, product_id, from_user_id, to_user_id, akt_id, quantity, actor_id)
			VALUES ('akt', :pid, :from_uid, 0, :akt_id, :q, :actor_id)
		");
		$stmtUpdateQuantity = $this->pdo->prepare("
			UPDATE sklad_accounting
			   SET quantity = :quantity,
			       status = CASE WHEN :quantity_status <= 0.000001 THEN 'disable' ELSE 'enable' END
			 WHERE user_id = :user_id AND id = :id
		");
		foreach ($update as $id_tovar => $data) {
			$product_id = (int)$data['pid'];
			$id         = (int)$data['id'];
			$quantity   = self::parseQuantity($data['count'] ?? 0);
			if (!self::isPositiveQuantity($quantity)) {
				continue;
			}
			$sql_check_count = $this->pdo->prepare("SELECT quantity FROM sklad_accounting WHERE user_id = :user_id AND id = :id");
			$sql_check_count->execute([':user_id' => $user_id, ':id' => $id]);
			$row = $sql_check_count->fetch(PDO::FETCH_ASSOC);
			$available = self::parseQuantity($row['quantity'] ?? 0);
			if (!$row || ($available + 0.000001) < $quantity) {
				return false;
			}
			$new_quantity = round(max(0, $available - $quantity), 2);
			$stmtUpdateQuantity->execute([
				':quantity' => $new_quantity,
				':quantity_status' => $new_quantity,
				':user_id' => $user_id,
				':id' => $id
			]);
			$stmtLogWriteoff->execute([
				':pid'      => $product_id,
				':from_uid' => $user_id,
				':akt_id'   => $akt_id,
				':q'        => $quantity,
				':actor_id' => $user_id 
			]);
		}
	}
	public function Transfer($data) {
		$i_am = (int)$this->my_user['id'];
		$stmtLog = $this->pdo->prepare("INSERT INTO sklad_log (action, product_id, from_user_id, to_user_id, quantity, actor_id) VALUES ('transfer', :pid, :from_uid, :to_uid, :q, :actor_id)");
		$stmtUpdate = $this->pdo->prepare("UPDATE sklad_accounting SET quantity = :quantity, status = CASE WHEN :quantity_status <= 0.000001 THEN 'disable' ELSE 'enable' END WHERE id = :id");
		$stmtIns = $this->pdo->prepare("INSERT INTO sklad_accounting (status, product_id, user_id, quantity, action_type) VALUES ('enable', :product_id, :user_id, :quantity, 'assigned')");
		foreach ($data as $tovar) {
			$pid = (int)($tovar['pid'] ?? 0);
			$srcAccId = (int)($tovar['id'] ?? 0);
			$qtyToMove = self::parseQuantity($tovar['count'] ?? 0);
			$dstUserId = (int)($tovar['user_id'] ?? 0);
			if ($pid > 0 && $dstUserId > 0 && $srcAccId > 0 && self::isPositiveQuantity($qtyToMove)) {
				$sql_select_acc = $this->pdo->prepare("SELECT id, product_id, user_id, quantity FROM sklad_accounting WHERE id = :id AND status = 'enable'");
				$sql_select_acc->execute([':id' => $srcAccId]);
				$src = $sql_select_acc->fetch(PDO::FETCH_ASSOC);
				if (!$src) {
					continue;
				}
				$available = self::parseQuantity($src['quantity'] ?? 0);
				if (($available + 0.000001) < $qtyToMove) {
					continue;
				}
				$new_quantity = round(max(0, $available - $qtyToMove), 2);
				$stmtUpdate->execute([
					':id' => $srcAccId,
					':quantity' => $new_quantity,
					':quantity_status' => $new_quantity
				]);
				$stmtIns->execute([
					':product_id' => $pid,
					':user_id' => $dstUserId,
					':quantity' => $qtyToMove
				]);
				$stmtLog->execute([
					':pid'      => $pid,
					':from_uid' => (int)$src['user_id'], // хто був власником джерела
					':to_uid'   => $dstUserId,           // кому передали
					':q'        => $qtyToMove,           // кількість
					':actor_id' => $i_am,                // хто виконав
				]);
			}
		}
	}
	public function getListArchive($users, $start, $end) {
		$i_am = $this->my_user['id'];
		$query = "SELECT 
			a.id, 
			a.name, 
			a.user_id, 
			a.created_date, 
			a.note, 
			COUNT(t.id) AS count_tovar, 
			SUM(CASE WHEN t.score = 'no' THEN s.price * t.count ELSE 0 END) AS total_sum, 
			u.username, 
			u.class 
		FROM sklad_akt a
		LEFT JOIN sklad_akt_tovar t ON a.id = t.akt_id
		LEFT JOIN sklad_tovar s ON t.p_id = s.id
		LEFT JOIN users u ON a.user_id = u.id
		WHERE a.moderation = 'yes' 
		  AND a.user_id = :user_id
		  AND DATE(a.created_date) BETWEEN :start AND :end
		GROUP BY a.id 
		ORDER BY a.created_date DESC";
		$sql_list = $this->pdo->prepare($query);
		$sql_list->execute([
			'user_id' => $i_am,
			'start'   => $start,
			'end'     => $end
		]);
		$products = $sql_list->fetchAll(PDO::FETCH_ASSOC);
		return $products;		
	}	
	public function getMyModeration() {
		$i_am = $this->my_user['id'];
		$query = "SELECT 
			a.id, 
			a.name, 
			a.user_id, 
			a.created_date, 
			a.note, 
			COUNT(t.id) AS count_tovar, 
			SUM(CASE WHEN t.score = 'no' THEN s.price * t.count ELSE 0 END) AS total_sum, 
			u.username, 
			u.class 
		FROM sklad_akt a
		LEFT JOIN sklad_akt_tovar t ON a.id = t.akt_id
		LEFT JOIN sklad_tovar s ON t.p_id = s.id
		LEFT JOIN users u ON a.user_id = u.id
		WHERE a.moderation = 'yes' 
		  AND a.user_id = :user_id
		  AND DATE(a.created_date) = CURDATE()
		GROUP BY a.id ORDER by a.created_date DESC";
		$sql_list = $this->pdo->prepare($query);
		$sql_list->execute(['user_id' => $i_am]);
		$products = $sql_list->fetchAll(PDO::FETCH_ASSOC);
		return $products;		
	}
    public function getUser($usr){
		if (!is_array($usr) || empty($usr['id'])) {
			return array(
				'status' => 'error',
				'user_id' => 0,
				'user_ip' => 'N/A',
				'access' => '',
				'user_name' => '',
				'username' => '',
				'user_class' => 1,
				'user_class_id' => 1,
				'apikey' => ''
			);
		}
		$appaccess = (isset($usr['android']) ? $usr['android']:'searchonu,odometr');
		$result_api = array(
			'status'=>'connect','user_id'=>$usr['id'],'user_ip'=>'N/A','access'=>$appaccess,
			'user_name'=>(isset($usr['name']) && $usr['name'] !== '' ? $usr['name'] : $usr['username']),
			'username'=>(isset($usr['username']) ? $usr['username'] : ''),
			'user_class'=>(isset($usr['class']) ? $usr['class'] : 1 ),
			'user_class_id'=>(isset($usr['class']) ? $usr['class'] : 1 ),
			'apikey'=>'pmon_app_'.md5($this->clock)
		);
		return $result_api;
	}
	public function DecodeLoginPassword(string $encoded): array|false {
        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return false;
        }
        $parts = explode(":", $decoded, 2);
        if (count($parts) === 2) {
            return [
                'login' => $parts[0],
                'password' => $parts[1]
            ];
        }
        return false;
    }
	public static function parseQuantity($value): float
	{
		if ($value === null || $value === '') {
			return 0.0;
		}
		if (is_string($value)) {
			$value = str_replace(',', '.', trim($value));
		}
		if (!is_numeric($value)) {
			return 0.0;
		}
		$quantity = round((float)$value, 2);
		return $quantity > 0 ? $quantity : 0.0;
	}
	public static function isPositiveQuantity($value): bool
	{
		return self::parseQuantity($value) > 0.000001;
	}
	public static function text(string $value, string $default = ''): string
	{
		$value = self::_prepare($value);
		$value = str_replace("\t", ' ', $value);
		$patterns = [
			'/<!--.*?-->/s',
			'/\/\*.*?\*\//s',
			'/<([\?\%]).*?\1>/s',
			'/<!\[CDATA\[.*?\]\]>/s',
			'/<\!\[.*?\]>.*?<\!\[.*?\]>/s',
			'/\s--.*$/m',
			'/<script[^>]*>.*?<\/script>/si',
			'/<style[^>]*>.*?<\/style>/si',
			'/<\/?[a-z][^>]*>/i',
		];
		$value = preg_replace($patterns, ' ', $value);
		$value = strip_tags($value);
		$value = preg_replace('/\s+/', ' ', $value);
		$value = trim($value);
		$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		return $value === '' ? $default : $value;
	}
	private static function _prepare($value) {
		if (!is_string($value)) {
			$value = strval($value);
		}
		$value = stripslashes($value);
		$value = preg_replace('/[\x00\x07\x08\x0B\x1B\x0C]/', ' ', $value);
		$value = htmlspecialchars_decode($value, ENT_QUOTES);
		return $value;
	}
}
?>
