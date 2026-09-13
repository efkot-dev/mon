<?php
if (!defined('PONMONITOR')) {
    die('System Billing Error Attempt!');
}

class Auth
{
    private PDO $db;
    private array $confPMon = [];
    private array $cfg = [];
    private array $app_userid = [];
    private array $errors = [];
    private string $timer;
    public string $u_name;
    private int $minpass = 6;
    private string $legacySalt = '#@()DIJK#)(F#&*()DS#@JKS)@(I()#@DU)*(&@#)(#U)J';

    public function __construct(PDO $db, $setup = [], $confPMon = [])
    {
        $this->db = $db;
        $this->timer = date('Y-m-d H:i:s');
        $this->confPMon = is_array($confPMon) ? $confPMon : [];
        $this->cfg = is_array($setup) ? $setup : [];
        $this->securityAttempts();
    }
    public function login(string $username, string $password, bool $remember = true): bool
    {
        $username = trim(mb_strtolower($username));
        if (strlen($password) < $this->minpass) {
            $this->errors[] = 'Пароль занадто короткий';
            $this->loginAttempts();
            return false;
        }
        $stmt = $this->db->prepare("SELECT id, username, password, name, class, access  FROM users  WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->u_name = $username;
        if (!$user) {
            $this->errors[] = 'Невірний логін або пароль';
            $this->loginAttempts();
            return false;
        }
        if (!$this->verifyPassword($password, $user)) {
            $this->errors[] = 'Невірний логін або пароль';
            $this->loginAttempts();
            return false;
        }
        $this->checkAccessSystem($user);  
        $this->deleteLoginAttempts();
        $this->createSession($user, $remember);
        return true;
    }
	public function mobileapp(string $username, string $password): bool {
		$username = trim(mb_strtolower($username));
		if ($username === '' || strlen($password) < $this->minpass) {
			$this->loginAttempts();
			return false;
		}
		try {
			$stmt = $this->db->prepare("SELECT id, username, password, android, name, class, access FROM users WHERE username = :username LIMIT 1");
			$stmt->execute(['username' => $username]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!$user || !$this->verifyPassword($password, $user)) {
				$this->loginAttempts();
				return false;
			}
			if (($user['access'] ?? 'no') === 'no') {
				return false;
			}
			$this->deleteLoginAttempts();
			$this->app_userid = [
				'id' => (int)($user['id'] ?? 0),
				'ip' => $this->getIp(),
				'username' => (string)($user['username'] ?? ''),
				'android' => $user['android'] ?? '',
				'class' => isset($user['class']) ? (int)$user['class'] : null,
				'name' => $user['name'] ?? ''
			];
			return true;
		} catch (Throwable $e) {
			error_log('mobileapp error: ' . $e->getMessage());
			return false;
		}
	}
    public function app_getuser(): array    {
        return $this->app_userid;
    }
    private function verifyPassword(string $password, array $user): bool    {
        $storedHash = $user['password'];
        if (password_verify($password, $storedHash)) {
            if (password_needs_rehash($storedHash, PASSWORD_ARGON2ID)) {
                $this->rehashPassword((int)$user['id'], $password);
            }
            return true;
        }
        if ($this->legacyHash($password) === $storedHash) {
            $this->rehashPassword((int)$user['id'], $password);
            return true;
        }
        return false;
    }
	    private function rehashPassword(int $userId, string $password): void    {
	        $newHash = password_hash($password, PASSWORD_ARGON2ID);
	        if (!is_string($newHash) || $newHash === '') {
	            $newHash = password_hash($password, PASSWORD_BCRYPT);
	        }
	        if (!is_string($newHash) || $newHash === '') {
	            return;
	        }
	        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
	        $stmt->execute(['password' => $newHash,'id' => $userId]);
	    }
    private function legacyHash(string $password): string {
        return sha1(md5($this->legacySalt . md5($password)));
    }
	    public function hashPassword(string $password): string {
	        $hash = password_hash($password, PASSWORD_ARGON2ID);
	        if (!is_string($hash) || $hash === '') {
	            $hash = password_hash($password, PASSWORD_BCRYPT);
	        }
	        if (!is_string($hash) || $hash === '') {
	            throw new RuntimeException('Unable to hash password');
	        }
	        return $hash;
	    }
    private function createSession(array $user, bool $remember): void   {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expireTime = $remember ? time() + 86400 : time() + 3600;
        $expireDate = date('Y-m-d H:i:s', $expireTime);
        $stmt = $this->db->prepare("INSERT INTO user_sessions (user_id, token_hash, expires_at, ip_address, user_agent, created_at) VALUES (:user_id, :token_hash, :expires_at, :ip, :ua, :created)");
        $stmt->execute(['user_id' => $user['id'],'token_hash' => $tokenHash,'expires_at' => $expireDate,'ip' => $this->getIp(),'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '','created' => $this->timer]);
        setcookie('pmon_session', $token, ['expires' => $expireTime,'path' => '/','httponly' => true,'secure' => !empty($_SERVER['HTTPS']),'samesite' => 'Lax']);
    }
    public function isLogged(): bool    {
        return $this->getuser() !== null;
    }
    public function getuser(): ?array   {
        if (empty($_COOKIE['pmon_session'])) {
            return null;
        }
        $tokenHash = hash('sha256', $_COOKIE['pmon_session']);
        $stmt = $this->db->prepare("SELECT u.* FROM user_sessions s JOIN users u ON u.id = s.user_id WHERE s.token_hash = :token AND s.expires_at > NOW() LIMIT 1");
        $stmt->execute(['token' => $tokenHash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return null;
        }
        $this->checkAccessSystem($user);
        $stmt = $this->db->prepare("UPDATE users SET lastactivity = :lastactivity,  status = 3, ip = :ip  WHERE id = :id");
        $stmt->execute(['lastactivity' => $this->timer,'ip' => $this->getIp(),'id' => $user['id']]);
        return $user;
    }
    public function requireAuth(): ?array    {
        return $this->getuser();
    }
    public function logout(): void    {
        if (!empty($_COOKIE['pmon_session'])) {
            $tokenHash = hash('sha256', $_COOKIE['pmon_session']);
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE token_hash = :token");
            $stmt->execute(['token' => $tokenHash]);
        }
        setcookie('pmon_session', '', time() - 3600, '/');
        header('Location: /?do=login');
        exit;
    }
    public function error(): void    {
        if (!empty($this->errors)) {
            echo '<div class="errorlogin">';
            foreach ($this->errors as $error) {
                echo htmlspecialchars($error) . '<br>';
            }
            echo '</div>';
        }
    }
    private function securityAttempts(): void    {
        $ip = $this->getIp();
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(attempts), 0) FROM login_attempts WHERE ip_address = :ip");
        $stmt->execute(['ip' => $ip]);
        $count = (int)$stmt->fetchColumn();
        if ($count > 5) {
            http_response_code(429);
            die('Too many login attempts');
        }
    }
    private function loginAttempts(): void    {
        $stmt = $this->db->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (:ip, 1) ON DUPLICATE KEY UPDATE attempts = attempts + 1");
        $stmt->execute(['ip' => $this->getIp()]);
    }
    private function deleteLoginAttempts(): void   {
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE ip_address = :ip");
        $stmt->execute(['ip' => $this->getIp()]);
    }
    public function sendNotification(): void {
        $message = 'Authorization in PMon: [b]'.$this->u_name.'[/b] => ' .$this->getIp();
        $stmt = $this->db->prepare("INSERT INTO notification (status, type, system, message, added) VALUES ('1', '1', 'login', :message, :added)");
        $stmt->execute(['message' => $message,'added' => $this->timer]);
    }
    public function checkAccessSystem(array $user): void {
        if (($user['access'] ?? 'no') === 'no') {
            http_response_code(403);
            die('Access denied');
        }
    }
    private function getIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
global $auth, $pdo, $config, $confPMon;
if (!isset($auth) || !($auth instanceof Auth)) {
    $auth = new Auth($pdo, $config ?? [], $confPMon ?? []);
}
if (
    isset($_POST['login']) &&
    isset($_POST['username']) &&
    isset($_POST['password'])
) {
    if ($auth->login($_POST['username'], $_POST['password'], true)) {
		
		$auth->sendNotification();
        header('Location: /?do=main');
        exit();
    }
}
?>
