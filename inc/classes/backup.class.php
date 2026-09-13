<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class BackUp {
    private $logger;
    private $db;
	private $conf = array(); 	
	private $device = array(); 	
    private $BackupDate;
    private $BackupCount = 30;
	public function __construct($db, $logger, $confPMon) {
		if (empty($confPMon['BACKUP_DIR']) || empty($confPMon['TFTP_SERVER'])) {

		} else {
			$this->db = $db;
			$this->logger = $logger; 
			$this->BackupDate = date("Y-m-d");
			$this->conf = $confPMon;
		}
	}
	public function bdcom_epon($name, $netip) {

    }
    public function bdcom_gpon($name, $netip) {
	}
	public function gen_name_device($name) {
		$translit = array(
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'є' => 'ie', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'і' => 'i', 'ї' => 'yi', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm',
			'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
			'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ь' => '', 'ю' => 'iu', 'я' => 'ia',
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Є' => 'IE', 'Ж' => 'ZH',
			'З' => 'Z', 'И' => 'I', 'І' => 'I', 'Ї' => 'YI', 'Й' => 'I', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
			'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
			'Х' => 'KH', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHCH', 'Ь' => '', 'Ю' => 'IU', 'Я' => 'IA'
		);
		$name = strtr($name, $translit);
		$name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
		$name = str_replace(' ', '_', $name);
		return $name;
	}   
    public function go_backup($name, $netip, $model) {
        if ($model === "bdcomepon") {
            $this->bdcom_epon($name, $netip);
        } elseif ($model === "bdcomgpon") {
            $this->bdcom_gpon($name, $netip);
        }
    }
    public function get_backup($switch) {
		$pinger = true;
		$this->device = $switch;
		$filename = $this->gen_name_device($switch['place']);
		if(isset($this->conf['PING']) && !empty($this->conf['PING']) && $this->conf['PING'] == 1){
			$ping_command = (isset($this->conf['SUDO'])?$this->conf['SUDO']:'') . ' ' . $this->conf['PING'] . ' -i '.(isset($this->conf['PINGTIME'])?$this->conf['PINGTIME']:'0.01').' -c 1 ' . $host;
			$ping_result = @shell_exec($ping_command);
			if ($ping_result !== null && strpos((string) $ping_result, 'ttl') !== false) {
				$pinger = true;
			} else {
				$pinger = false;
			}
		}
		if($pinger)
			$this->go_backup($filename,$switch['netip'],$switch['class']);
    }
}

?>
