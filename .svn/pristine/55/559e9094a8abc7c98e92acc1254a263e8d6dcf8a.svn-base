<?php
if (!defined('PONMONITOR')){
    die('Hacking attempt!');
}

class CacheManager {
    private $cacheType;
    private $memcached;
    private $redis;
    private $cacheDir;
    public $confPMon;

    public function __construct($cacheType, $confPMon, $redis) {
        $this->redis = $redis;
        $this->cacheType = defined('CACHE') ? CACHE : 'file';
        $this->confPMon = $confPMon;        
        if ($cacheType === "memcached") {
            $this->memcached = new Memcached();
            $this->memcached->addServer("127.0.0.1", 11211); 
        } elseif ($cacheType === "redis") {

        } elseif ($cacheType === "file") {
            $this->cacheDir = ROOT_DIR . "/export/cache/";
            if (!file_exists($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
            }
        } else {
            throw new Exception("Unsupported cache type: " . $cacheType);
        }
    }

    public function get($key) {
        if ($this->cacheType === "redis") {
            $cachedData = $this->redis->get($key);
            if ($cachedData) {
                $cachedData = unserialize($cachedData);
                if ($cachedData['expiration'] > time()) {
                    return $cachedData['data'];
                }
                $this->redis->del($key);
            }
        } elseif ($this->cacheType === "memcached") {
            $cachedData = $this->memcached->get($key);
            if ($cachedData) {
                if ($cachedData['expiration'] > time()) {
                    return $cachedData['data'];
                }
                $this->memcached->delete($key);
            }
        } elseif ($this->cacheType === "file") {
            $cacheFile = $this->cacheDir . md5($key);
            if (file_exists($cacheFile)) {
                $data = file_get_contents($cacheFile);
                $cachedData = unserialize($data);
                if ($cachedData['expiration'] > time()) {
                    return $cachedData['data'];
                }
                unlink($cacheFile);
            }
        }
        return null;
    }

    public function set($key, $data, $expiration = 3600) {
        $cachedData = [
            'data' => $data,
            'expiration' => time() + $expiration,
        ];
        if ($this->cacheType === "redis") {
            $this->redis->set($key, serialize($cachedData), 'EX', $expiration);
        } elseif ($this->cacheType === "memcached") {
            $this->memcached->set($key, $cachedData, $expiration);
        } elseif ($this->cacheType === "file") {
            $cacheFile = $this->cacheDir . md5($key);
            $data = serialize($cachedData);
            file_put_contents($cacheFile, $data);
        }
    }

    public function delete($key) {
        if ($this->cacheType === "redis") {
            $this->redis->del($key);
        } elseif ($this->cacheType === "memcached") {
            $this->memcached->delete($key);
        } elseif ($this->cacheType === "file") {
            $cacheFile = $this->cacheDir . md5($key);
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }
}
$cacheManager = new CacheManager('redis', $confPMon, $redis);
?>
