<?php
if (!defined('PMONSERVER')) {
    die('Hacking attempt!');
}

require_once ROOT_DIR . '/vendor/autoload.php';

if (!class_exists('Predis\\Client')) {
    throw new Exception('Predis\\Client class not found.');
}

use Predis\Client;

if (!defined('SERVER_PDO_PERSISTENT')) {
    define('SERVER_PDO_PERSISTENT', true);
}

final class Cache
{
    private Client $redis;
    private ?PDO $pdo = null;
    private string $configPrefix = 'config:';
    public function __construct(array $configCache) {
        $redisHost = (string)($configCache['redis_ip'] ?? '127.0.0.1');
        $redisPort = (int)($configCache['redis_port'] ?? 6379);
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => $redisHost,
            'port' => $redisPort,
            'persistent' => true,
            'timeout' => 0.7,
            'read_write_timeout' => 0.7,
        ]);
    }
    public function getConfig(string $cacheKey): ?array {
        $cached = $this->safeRedisGet($this->configPrefix . $cacheKey);
        if ($cached === null || $cached === '') {
            return null;
        }
        $decoded = $this->decodeArrayPayload($cached);
        return is_array($decoded) ? $decoded : null;
    }
    public function getConfigFromDb(array $keys = []): ?array {
        $pdo = $this->db_connect();
        $result = [];
        if (empty($keys)) {
            $stmt = $pdo->query('SELECT name, value FROM pmonini');
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result[(string)$row['name']] = $row['value'];
            }
            return $result;
        }
        $cleanKeys = array_values(array_filter(array_map(static fn($v) => preg_replace('/[^A-Z0-9_]/i', '', (string)$v),$keys)));
        if (empty($cleanKeys)) {
            return null;
        }
        $placeholders = implode(',', array_fill(0, count($cleanKeys), '?'));
        $stmt = $pdo->prepare("SELECT name, value FROM pmonini WHERE name IN ($placeholders)");
        $stmt->execute($cleanKeys);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[(string)$row['name']] = $row['value'];
        }
        return empty($result) ? null : $result;
    }
    public function get(string $key) {
        $cachedData = $this->safeRedisGet((string)$key);
        if ($cachedData === null || $cachedData === '') {
            return null;
        }
        $decoded = $this->decodeGenericPayload($cachedData);
        if (
            is_array($decoded)
            && array_key_exists('data', $decoded)
            && isset($decoded['expiration'])
            && is_numeric($decoded['expiration'])
            && count($decoded) <= 3
			) {
            if ((int)$decoded['expiration'] > time()) {
                return $decoded['data'];
            }
            $this->delete((string)$key);
            return null;
        }
        return $decoded;
    }

    public function set(string $key, $data, int $expiration = 3600): void {
        $expiration = max(1, $expiration);
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            $payload = serialize($data);
        }
        $this->safeRedisSetEx($key, $expiration, $payload);
    }
    public function delete(string $key): void {
        try {
            $this->redis->del([(string)$key]);
        } catch (Throwable $e) {
            error_log('[CACHE] Redis delete failed: ' . $e->getMessage());
        }
    }
    public function redisClient(): Client {
        return $this->redis;
    }
    public function db_connect(): PDO {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }
        try {
            $dsn = 'mysql:host=' . DBHOST . ';dbname=' . DBNAME . ';charset=utf8mb4';
            $this->pdo = new PDO($dsn, DBUSER, DBPASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => SERVER_PDO_PERSISTENT,
            ]);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
        return $this->pdo;
    }
    private function safeRedisGet(string $key): ?string {
        try {
            $value = $this->redis->get($key);
            return ($value === null) ? null : (string)$value;
        } catch (Throwable $e) {
            error_log('[CACHE] Redis get failed: ' . $e->getMessage());
            return null;
        }
    }
    private function safeRedisSetEx(string $key, int $expiration, string $payload): void {
        try {
            $this->redis->setex($key, $expiration, $payload);
        } catch (Throwable $e) {
            error_log('[CACHE] Redis set failed: ' . $e->getMessage());
        }
    }
    private function decodeArrayPayload(string $payload): ?array {
        $json = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }
        $legacy = $this->safeUnserialize($payload);
        if (is_array($legacy) && isset($legacy['data']) && is_array($legacy['data'])) {
            return $legacy['data'];
        }
        return is_array($legacy) ? $legacy : null;
    }
    private function decodeGenericPayload(string $payload) {
        $json = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }
        $legacy = $this->safeUnserialize($payload);
        if ($legacy !== null) {
            return $legacy;
        }
        return $payload;
    }
    private function safeUnserialize(string $payload) {
        if ($payload === '') {
            return null;
        }
        $value = @unserialize($payload, ['allowed_classes' => false]);
        if ($value === false && $payload !== 'b:0;') {
            return null;
        }
        return $value;
    }
}
?>
