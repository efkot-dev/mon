<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class Lang implements ArrayAccess {
    private array $lang_system = [];
    private array $languages = [];
    private $pdo;
    public $cacheManager;
    public function __construct($pdo,$config_lang,$cacheManager) {
        $this->pdo = $pdo;
        $this->cacheManager = $cacheManager;
        $sellang = (empty($config_lang) ? 'ua' : $config_lang);
        $this->loadLanguages($sellang);
        @setcookie('lang', $sellang, time() + (86400 * 30), "/");
    }
    private function loadCache($lang) {
		$expiration = 130;
		$data = [];
		$sql = "SELECT name_key, translation FROM translations WHERE lang = :lang";
		if (isset($this->cacheManager->confPMon['CACHE']) && !empty($this->cacheManager->confPMon['CACHE']) 
			&& $this->cacheManager->confPMon['CACHE'] == 1) {
			$cacheKey = "pmon_lang_$lang";        
			$cachedResult = $this->cacheManager->get($cacheKey);
			if ($cachedResult !== null) {
				$data = $cachedResult;
			} else {
				$stmt = $this->pdo->prepare($sql);
				$stmt->execute([':lang' => $lang]);
				$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$this->cacheManager->set($cacheKey, $data, $expiration);
			}
		} else {
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([':lang' => $lang]);
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		return $data;
	}
    private function loadLanguages(string $lang): void {
        $sql = $this->loadCache($lang); 
        if (isset($sql) && count($sql) > 0) {
            foreach ($sql as $id => $mova) {
                $this->lang_system[$mova['name_key']] = $mova['translation'];
            }
        }
    }
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->lang_system[$offset] = $value;
    }
    public function offsetExists(mixed $offset): bool {
        return isset($this->lang_system[$offset]);
    }
    public function offsetUnset(mixed $offset): void {
        unset($this->lang_system[$offset]);
    }
    public function offsetGet(mixed $offset): string {
        return $this->lang_system[$offset] ?? ''.(string) $offset;
    }
}
final class Cooldown {
	private int $cooldown;
	private int $lastRun;
	private bool $allowed;
	public function __construct(?string $lastRun, int $cooldown, bool $allowed)	{
		$this->cooldown = $cooldown;
		$this->lastRun  = $lastRun ? strtotime($lastRun) : 0;
		$this->allowed  = $allowed;
	}
	public function canRun(): bool{
		return $this->allowed && $this->getWait() === 0;
	}
	public function getWait(): int{
		return max(0, ($this->lastRun + $this->cooldown) - time());
	}
	public function waitText(): string{
		$sec = $this->getWait();
		if ($sec <= 0) return 'очікування доступу';
		$min = intdiv($sec, 60);
		$sec = $sec % 60;
		return $min > 0 ? "{$min} хв {$sec} сек" : "{$sec} сек";
	}
}
$lang = new Lang($pdo,($USER['lang'] ?? $config['lang']),$cacheManager);
$global_workids = [
	'system' => [
        18 => $lang['taskers_18'],
        11 => $lang['taskers_11'],
        14 => $lang['taskers_14'],
        19 => $lang['taskers_19'],
        21 => $lang['taskers_21'],
        23 => $lang['taskers_23'],
        26 => $lang['taskers_26'],
		1 => $lang['taskers_1'],
		6 => $lang['taskers_6'],
		9 => $lang['taskers_9'],
		64 => $lang['taskers_64'],
		60 => $lang['taskers_6'],
        28 => $lang['taskers_28'],
        30 => $lang['taskers_30'],
        32 => $lang['taskers_32'],
		53 => $lang['taskers_53'],
        48 => $lang['taskers_48'],
        52 => $lang['taskers_52'],
        59 => $lang['taskers_59'],
		52 => $lang['taskers_54'],
		96 => $lang['taskers_96'],
		58 => $lang['taskers_96'],
		44 => $lang['taskers_44'],
		99 => $lang['taskers_96'],
		55 => $lang['taskers_96']
    ],
	// BDCOM 
    1 => [57 => $lang['taskers_57'],10 => $lang['taskers_10'],1 => $lang['taskers_1'],8 => $lang['taskers_8'],15 => $lang['taskers_15'],20 => $lang['taskers_20'],25 => $lang['taskers_25'],29 => $lang['taskers_29'],31 => $lang['taskers_31'],33 => $lang['taskers_33'],45 => $lang['taskers_45'],7 => $lang['taskers_7']],
	// BDCOM GPON  	
	2 => [57 => $lang['taskers_57'],15 => $lang['taskers_15'],29 => $lang['taskers_29'],31 => $lang['taskers_31'],22 => $lang['taskers_22']],	
	// ZTE2
    3 => [57 => $lang['taskers_57']],
	// PLANET
    4 => [57 => $lang['taskers_57']],
	// D-LINK
    5 => [57 => $lang['taskers_57']],
	// ZTE6
    6 => [57 => $lang['taskers_57'],31 => $lang['taskers_31'],15 => $lang['taskers_15'],36 => $lang['taskers_36'],37 => $lang['taskers_37'],38 => $lang['taskers_38']],
	// ZTE3
    7 => [57 => $lang['taskers_57'],31 => $lang['taskers_31'],29 => $lang['taskers_29'],15 => $lang['taskers_15'],42 => $lang['taskers_42'],43 => $lang['taskers_43'],45 => $lang['taskers_45'],49 => $lang['taskers_49'],8 => $lang['taskers_8'],7 => $lang['taskers_7']],
	// Mikrotik all
    8 => [57 => $lang['taskers_57']],  
	// GCOM
    9 => [57 => $lang['taskers_57'],15 => $lang['taskers_15']],
	// SmartFiber EPON
    10 => [57 => $lang['taskers_57']],
	// SmartFiber GPON
    11 => [57 => $lang['taskers_57'],31 => $lang['taskers_31'],15 => $lang['taskers_15']],
	// CDATA16
    12 => [57 => $lang['taskers_57'],31 => $lang['taskers_31'],45 => $lang['taskers_45'],15 => $lang['taskers_15'],7 => $lang['taskers_7']],
	// CDATA11
    13 => [7 => $lang['taskers_7'],57 => $lang['taskers_57'],31 => $lang['taskers_31'],12 => $lang['taskers_12'],15 => $lang['taskers_15']],
	// HUAWEI56
    14 => [57 => $lang['taskers_57'],31 => $lang['taskers_31'],8 => $lang['taskers_8'],29 => $lang['taskers_29'],15 => $lang['taskers_15'],24 => $lang['taskers_24'],34 => $lang['taskers_34'],45 => $lang['taskers_45'],7 => $lang['taskers_7']],
	// CDATA12
    15 => [57 => $lang['taskers_57'],8 => $lang['taskers_8'],31 => $lang['taskers_31'],13 => $lang['taskers_13'],15 => $lang['taskers_15'],39 => $lang['taskers_39'],40 => $lang['taskers_40'],41 => $lang['taskers_41'],45 => $lang['taskers_45'],7 => $lang['taskers_7']],
	// V-SOL V1600G1
    16 => [57 => $lang['taskers_57'],10 => $lang['taskers_10']],
	// Huawei S2326TP
    17 => [57 => $lang['taskers_57']],
	// GCOM S6100-16X
    18 => [57 => $lang['taskers_57']],
	// N/A
    19 => [57 => $lang['taskers_57']],
	// Cisco Nexus 3000
    20 => [57 => $lang['taskers_57']],
	// MikroTik CRS305
    21 => [57 => $lang['taskers_57']],
	// MikroTik CRS309
    22 => [57 => $lang['taskers_57']],
	// Hioso HA7302
    23 => [ 57 => $lang['taskers_57'],10 => $lang['taskers_10']],
	// Edge-Core ECS4120-28F
    24 => [57 => $lang['taskers_57']],
	// N/A
    25 => [57 => $lang['taskers_57']],
	// N/A
    26 => [57 => $lang['taskers_57']],
	// N/A
    27 => [57 => $lang['taskers_57']],
	// V-SOL V1600D (V1.0)
    28 => [57 => $lang['taskers_57'],10 => $lang['taskers_10']],
	// V-SOL V1600D (V2.03.76R)
    29 => [57 => $lang['taskers_57'],10 => $lang['taskers_10']],
	// Raisecom ISCOM2624G-4GE-AC
    30 => [57 => $lang['taskers_57']],
	// DSN S4600-10P-SI
    31 => [57 => $lang['taskers_57']],
	// Dell X4012
    32 => [57 => $lang['taskers_57']],
	// HUAWEI56
    33 => [57 => $lang['taskers_57'], 31 => $lang['taskers_31'], 15 => $lang['taskers_15'], 34 => $lang['taskers_34'], 45 => $lang['taskers_45'], 8 => $lang['taskers_8'], 7 => $lang['taskers_95']],
	// ZTE3
    34 => [57 => $lang['taskers_57'],8 => $lang['taskers_8'],31 => $lang['taskers_31'],29 => $lang['taskers_29'],15 => $lang['taskers_15'],42 => $lang['taskers_42'],43 => $lang['taskers_43'],45 => $lang['taskers_45'],49 => $lang['taskers_49'],50 => $lang['taskers_50'],7 => $lang['taskers_96']],
	// CDATA FD1608 v3.x
    35 => [57 => $lang['taskers_57'],31 => $lang['taskers_31'],29 => $lang['taskers_29'],7 => $lang['taskers_46'],8 => $lang['taskers_8'],15 => $lang['taskers_15']],
	// GCOM GL5610-04P
    36 => [57 => $lang['taskers_57']],
	// MikroTik CCR1072-1G-8S
    37 => [57 => $lang['taskers_57']],
	// Juniper MX140
    38 => [57 => $lang['taskers_57']],
	// Cisco Catalyst 6500
    39 => [57 => $lang['taskers_57']],
    40 => [57 => $lang['taskers_57']],
	// CDATA17
	41 => [57 => $lang['taskers_57'],8 => $lang['taskers_8'],27 => $lang['taskers_27'],46 => $lang['taskers_46'],29 => $lang['taskers_29'],15 => $lang['taskers_15'],31 => $lang['taskers_31']],
	44 => [57 => $lang['taskers_57'],8 => $lang['taskers_8'],27 => $lang['taskers_27'],46 => $lang['taskers_46'],29 => $lang['taskers_29'],15 => $lang['taskers_15'],31 => $lang['taskers_31']],
	// ELTEX
	42 => [57 => $lang['taskers_57']],	
	// NOKIA
    43 => [57 => $lang['taskers_57'],29 => $lang['taskers_29'],31 => $lang['taskers_31']]
];
?>
