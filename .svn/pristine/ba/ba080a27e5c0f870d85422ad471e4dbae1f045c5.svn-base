<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class IpPMonAccess {
    private $lang;
    private $goodip = ROOT_DIR . '/export/good.ip';
    private $badip = ROOT_DIR . '/export/bad.ip';
    private $db;
    private $goodbaza = [];
    private $badbaza = [];

    public function __construct($lang, $db) {
        $this->db = $db;
        $this->lang = $lang;
        $this->loadIpLists();
    }

    private function loadIpLists() {
        $this->goodbaza = $this->loadIpListFromFile($this->goodip);
        $this->badbaza = $this->loadIpListFromFile($this->badip);
    }

    private function loadIpListFromFile($filePath) {
        $ipList = [];
        if (file_exists($filePath)) {
            $ipList = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        return $ipList;
    }

    private function genmasiv($ipList) {
        $ip_masiv = [];
        foreach ($ipList as $range) {
            if (strpos($range, ',') !== false) {
                $ranges = explode(',', $range);
                $ip_masiv = array_merge($ip_masiv, $ranges);
            } else {
                $ip_masiv[] = $range;
            }
        }
        return $ip_masiv;
    }

    private function checkIpaccess($allowed_ips, $ip) {
        if (in_array($ip, $allowed_ips)) {
            return true; // Якщо IP-адреса належить до списку дозволених, повертаємо true.
        }

        return false; // Якщо IP-адреса не належить до списку дозволених, то доступ заборонено.
    }

    private function isIpInRange($ip, $range) {
        list($subnet, $mask) = explode('/', $range);
        if (strpos($mask, '-') !== false) {
            list($start, $end) = explode('-', $mask);
            $start_ip = ip2long($subnet . '.' . $start);
            $end_ip = ip2long($subnet . '.' . $end);
            $ip = ip2long($ip);

            return ($ip >= $start_ip && $ip <= $end_ip);
        } else {
            $subnet_ip = ip2long($subnet);
            $ip = ip2long($ip);
            $mask = ~((1 << (32 - $mask)) - 1);

            return (($ip & $mask) === ($subnet_ip & $mask));
        }
    }

    public function isIpAllowed($ip) {
        if (!empty($this->goodbaza)) {
            $allowed_ips = $this->genmasiv($this->goodbaza);
            if (!$this->checkIpaccess($allowed_ips, $ip)) {
                return false;
            }
        }

        if (!empty($this->badbaza)) {
            $blocked_ips = $this->genmasiv($this->badbaza);
            if ($this->checkIpaccess($blocked_ips, $ip)) {
                return false;
            }
        }

        return true;
    }

    public function get_user_ip() {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? null;
        if ($ip) {
            $ip = explode(',', $ip)[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
/*
$ipaccess = new IpPMonAccess($lang, $db);
$user_ip = $ipaccess->get_user_ip();
if ($ipaccess->isIpAllowed($user_ip)) {
    echo "Доступ дозволено";
} else {
    echo "Доступ заборонено";
}
*/
?>
