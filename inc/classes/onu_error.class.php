<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
class Onu_Error {
    private int $id;
    private array $data_switch;
    public string $clock;
    private PDO $pdo;
    private array $confPMon;
    private array $errors = [];
    public function __construct(int $id, PDO $pdo, array $switch, array $confPMon){
        $this->pdo = $pdo;
        $this->id = $id;
        $this->clock = date('Y-m-d H:i:s');
        $this->data_switch = $switch;
        $this->confPMon = $confPMon;
    }
	public function format(array $onu): array {
		if ($this->data_switch['oidid'] == 1) {
			$onukey = $this->resolveOnuKey($onu);
			$errorRaw = $this->errors[$onukey]['error'] ?? null;
			$error = ($errorRaw === null || $errorRaw === '') ? null : (int)$errorRaw;
			return [
				'idonu' => (int)($onu['idonu'] ?? 0),'onukey' => $onukey,'error' => $error
			];
		}elseif($this->data_switch['oidid'] == 14){
			$slot = isset($onu['zte_idport']) ? (int)$onu['zte_idport'] : 0;
			$idx  = isset($onu['keyonu']) ? (int)$onu['keyonu'] : 0;
			$errorRaw = $this->errors[$slot][$idx]['error'] ?? null;
			$error = ($errorRaw === null || $errorRaw === '') ? null : (int)$errorRaw;
			$onukeyStr = $slot . '.' . $idx;
			return [
				'idonu' => (int)($onu['idonu'] ?? 0),'onukey' => $onukeyStr,'error' => $error
			];			
		}
	}
    public function result_clear_snmp(string $value): string {
        $v = trim($value);
        $pos = strpos($v, '=');
        if ($pos !== false) {
            $v = trim(substr($v, $pos + 1));
        }
        $v = preg_replace('/^(?:STRING|Counter64|Gauge64|Counter32|Gauge32|INTEGER)\s*:\s*/i', '', $v);
        $v = str_ireplace('EPON','', $v);
        $v = trim($v," \t\n\r\0\x0B\":");
        return $v;
    }
    public function get_error_huawei_gpon(): array {
		$oiderr = [
			'oid' => '1.3.6.1.4.1.2011.6.128.1.1.4.27.1.2','type' => 'exec','deloid' => true,'ip' => $this->data_switch['netip'],'community'=> $this->data_switch['snmpro']
		];
		$erronu = pmon_walk_m($oiderr);
				
		$array_error = [];
		if(is_array($erronu)){
			foreach($erronu as $inface => $type) {
				preg_match('/(\d+).(\d+)\s*=\s*(.*?)\s*$/',$type['result'],$temp);
				$array_error[$temp[1]][$temp[2]]['error'] = $this->result_clear_snmp($temp[3]);
			}
		}
        $this->errors = $array_error;
        return $array_error;
	}
    public function get_error_bdocm_epon(): array {
        $oiderr = [
            'oid' => '1.3.6.1.2.1.2.2.1.14','type' => 'real','deloid' => true,'ip' => $this->data_switch['netip'],'community' => $this->data_switch['snmpro']
        ];
        $temp_error = pmon_walk_m($oiderr);
        $array_error = [];
        if (!empty($temp_error) && is_array($temp_error)) {
            $pattern = '/^\s*(\d+)\s*=\s*(.*?)\s*$/';
            foreach ($temp_error as $iface => $row) {
                $result = isset($row['result']) ? (string)$row['result'] : '';
                if (preg_match($pattern, $result, $m)) {
                    $key = (string)$m[1];
                    $value = $m[2];
                } else {
                    $key = (string)$iface;
                    $value = $result;
                }
                $array_error[$key] = [
                    'error'  => $this->result_clear_snmp($value),'onukey' => $key
                ];
            }
        }
        $this->errors = $array_error;
        return $array_error;
    }
    public function get_error(array $switch): array {
        if (!empty($this->data_switch['oidid']) && (int)$this->data_switch['oidid'] == 1) {
            return $this->get_error_bdocm_epon();
        }elseif(!empty($this->data_switch['oidid']) && (int)$this->data_switch['oidid'] == 14){
            return $this->get_error_huawei_gpon();			
		}
        $this->errors = [];
        return $this->errors;
    }
	private function bdcom_epon_save(array $onu): string {
		if (empty($onu)) return "0";
		$stmtLast = $this->pdo->prepare("SELECT error FROM onus_error WHERE idonu = :idonu ORDER BY id DESC LIMIT 1");
		$stmtInsert = $this->pdo->prepare("INSERT INTO onus_error (idonu, error, riznica, added)	VALUES (:idonu, :error, :riznica, CURRENT_TIMESTAMP)");
		$inserted = 0;
		foreach ($onu as $row) {
			$idonu = (int)($row['idonu'] ?? 0);
			if ($idonu <= 0) continue;
			if (!array_key_exists('error', $row) || $row['error'] === null || $row['error'] === '') continue;
			$curr = (int)$row['error'];
			if ($curr === 0) continue;
			$stmtLast->execute([':idonu' => $idonu]);
			$prevRow = $stmtLast->fetch(PDO::FETCH_ASSOC);
			$prev = $prevRow !== false ? (int)$prevRow['error'] : null;
			if ($prev === null || $prev !== $curr) {
				$diff = ($prev === null) ? 0 : ($curr - $prev);
				if ($prev !== null && $diff < 0) {
					$diff = $curr;
				}
				$stmtInsert->execute([
					':idonu' => $idonu,':error' => $curr,':riznica' => $diff
				]);
				$inserted += (int)$stmtInsert->rowCount();
			}
		}
		return (string)$inserted;
	}	
	private function huawei_gpon_save(array $onu): string {
		if (empty($onu)) return "0";
		$stmtLast = $this->pdo->prepare("SELECT error FROM onus_error WHERE idonu = :idonu ORDER BY id DESC LIMIT 1");
		$stmtInsert = $this->pdo->prepare("INSERT INTO onus_error (idonu, error, riznica, added)	VALUES (:idonu, :error, :riznica, CURRENT_TIMESTAMP)");
		$inserted = 0;
		foreach ($onu as $row) {
			$idonu = (int)($row['idonu'] ?? 0);
			if ($idonu <= 0) continue;
			if (!array_key_exists('error', $row) || $row['error'] === null || $row['error'] === '') continue;
			$curr = (int)$row['error'];
			if ($curr === 0) continue;
			$stmtLast->execute([':idonu' => $idonu]);
			$prevRow = $stmtLast->fetch(PDO::FETCH_ASSOC);
			$prev = $prevRow !== false ? (int)$prevRow['error'] : null;
			if ($prev === null || $prev !== $curr) {
				$diff = ($prev === null) ? 0 : ($curr - $prev);
				if ($prev !== null && $diff < 0) {
					$diff = $curr;
				}
				$stmtInsert->execute([
					':idonu' => $idonu,':error' => $curr,':riznica' => $diff
				]);
				$inserted += (int)$stmtInsert->rowCount();
			}
		}
		return (string)$inserted;
	}
	public function db_clear(): void {
		$sql_old = "DELETE FROM onus_error WHERE added < (NOW() - INTERVAL 30 DAY)";
		$count_old = $this->pdo->exec($sql_old);
		$sql_null = "DELETE FROM onus_error WHERE error = 0";
		$count_null = $this->pdo->exec($sql_null);
	}
	public function save(array $onu): string {
		if ($this->data_switch['oidid'] == 1) {
			return $this->bdcom_epon_save($onu);
		}elseif($this->data_switch['oidid'] == 14){	
			return $this->huawei_gpon_save($onu);
		}
		return "0";
	}
    private function resolveOnuKey(array $onu): string {
        if (!empty($onu['keyonu'])) {
            return (string)$onu['keyonu'];
        }
        if (!empty($onu['zte_idport'])) {
            return (string)$onu['zte_idport'];
        }
        if (!empty($onu['sw_port'])) {
            return (string)$onu['sw_port'];
        }
        return (string)($onu['idonu'] ?? '');
    }
}
?>