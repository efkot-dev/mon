<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMonSnmp {
    public $timer;
    private $lang;
    private $timeout = 1000000;
    private $retries = 5;
    private $db;
    private $config;
    private $confPMon;
    private $cache;
    public function __construct($db, $lang, $config, $confPMon, $cacheManager) {
        $this->lang = $lang;
        $this->db = $db;
        $this->config = $config;
        $this->cache = $cacheManager;
        $this->confPMon = $confPMon;
        $this->timer = microtime(true);
    }
    public function snmp_get_re($value) {
        return @snmp2_get($value['netip'], $value['snmpro'], $value['oid'], $this->timeout, $this->retries);
    }
    public function get($array) {
        $result = [];
		$data = '';
        $retriesLeft = 3;
        while ($retriesLeft > 0) {
            $get = $this->snmp_get_re($array);
            if (!empty($get)) {
                break;
            }
            sleep(1);
            $retriesLeft--;
        }
        $data = $this->trimSNMPOutput($get, $array['oid']);
        return $data;
    }
    public function trimSNMPOutput($snmpData, $oid) {
        $rep = array('INTEGER:', 'Hex-STRING:', 'STRING:', 'Gauge32:', 'Gauge64:', 'Counter32:', 'Counter64:', 'Timeticks:', $oid, '=', '"', ' ');
        $value = str_replace($rep, '', $snmpData);
        return trim($value);
    }
    private function prepareData($data) {
        if (!is_array($data)) {
            $data = array($data);
        }
        return array_map(function ($value) {
            return preg_replace('/[^\.A-Z0-9_ !@#$%^&()+={}[\]\',~`\-\'":;\\/*|><?]|\.+$/i', '', $value);
        }, $data);
    }
}
?>
