<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
require ROOT_DIR . '/inc/init.monitor.php';
$starttime = microtime(true);
$timer = date('Y-m-d H:i:s');
if (isset($olt) && is_numeric($olt)) {
    $id_device = (int)$olt;
}
const ZTE_EPON_BASE_INDEX = 268765952;
const ZTE_EPON_PORT_STEP = 256;
function decodeZteEponPortIndex(int $idx): array
{
    $hex = sprintf('%08X', $idx);

    if ($idx < ZTE_EPON_BASE_INDEX) {
        return [
            'raw' => $idx,
            'hex' => $hex,
            'type' => hexdec(substr($hex, 0, 2)),
            'shelf' => 1,
            'slot' => 2,
            'port' => 0,
            'valid' => false,
            'interface'  => 'unknown',
        ];
    }
    $delta = $idx - ZTE_EPON_BASE_INDEX;
    if ($delta % ZTE_EPON_PORT_STEP !== 0) {
        return [
            'raw' => $idx,
            'hex' => $hex,
            'type' => hexdec(substr($hex, 0, 2)),
            'shelf' => 1,
            'slot' => 2,
            'port' => 0,
            'valid' => false,
            'interface'  => 'unknown',
        ];
    }
    $port = (int)($delta / ZTE_EPON_PORT_STEP) + 1;
    return [
        'raw' => $idx,
        'hex' => $hex,
        'type' => hexdec(substr($hex, 0, 2)),
        'shelf' => 1,
        'slot' => 2,
        'port' => $port,
        'valid' => true,
        'interface'  => sprintf('EPON 1/2/%d', $port),
    ];
}
function decodeZteEponPortOnu(int $portIdx, int $onuId): array
{
    $port = decodeZteEponPortIndex($portIdx);
    return [
        'raw' => $portIdx . '.' . $onuId,
        'shelf' => $port['shelf'],
        'slot' => $port['slot'],
        'port' => $port['port'],
        'onu' => $onuId,
        'valid' => $port['valid'],
        'interface'  => $port['interface'],
        'name' => sprintf(
            'epon-onu_%d/%d/%d:%d',
            $port['shelf'],
            $port['slot'],
            $port['port'],
            $onuId
        ),
        'debug'      => [
            'hex'  => $port['hex'],
            'type' => $port['type'],
        ],
    ];
}
function encodeZteEponPortIndex(int $port): int
{
    if ($port < 1) {
        return 0;
    }
    return ZTE_EPON_BASE_INDEX + (($port - 1) * ZTE_EPON_PORT_STEP);
}
function zteSignalToDbm(int $rawSignal): float
{
    return $rawSignal / 1000;
}
if (isset($id_device) && $id_device > 0) {
    $switch = $db->Simple("SELECT * FROM switch WHERE id = '" . (int)$id_device . "' LIMIT 1");
    if (empty($switch)) {
        die('Switch not found');
    }
    $status_epon = [
        'oid' => '1.3.6.1.4.1.3902.1015.1010.11.2.1.2',
        'type' => 'exec',
        'deloid' => true,
        'ip' => $switch['netip'],
        'community' => $switch['snmpro'],
    ];
    $tmp_epon = pmon_walk($status_epon);
    $decodedList = [];
    $byInterface = [];
    if (is_array($tmp_epon) && count($tmp_epon) > 0) {
        foreach ($tmp_epon as $row) {
            if (!isset($row['result']) || !is_string($row['result'])) {
                continue;
            }
            if (!preg_match('/^(\d+)\.(\d+)\s*=\s*INTEGER:\s*(-?\d+)$/', trim($row['result']), $m)) {
                continue;
            }
            $portIndex = (int)$m[1];
            $onuId = (int)$m[2];
            $snmpValue = (int)$m[3];
            $decoded = decodeZteEponPortOnu($portIndex, $onuId);
            $item = [
                'source' => $row['result'],
                'portIndex' => $portIndex,
                'onuId' => $onuId,
                'valueRaw' => $snmpValue,
                'valueDbm' => zteSignalToDbm($snmpValue),
                'decoded' => $decoded,
            ];
            $decodedList[] = $item;
            if ($decoded['valid']) {
                $ifName = $decoded['interface'];
                if (!isset($byInterface[$ifName])) {
                    $byInterface[$ifName] = [];
                }
                $byInterface[$ifName][] = [
                    'onu' => $decoded['onu'],
                    'name' => $decoded['name'],
                    'signal' => zteSignalToDbm($snmpValue),
                    'raw' => $snmpValue,
                    'portIndex'=> $portIndex,
                ];
            }
        }
    }
}
?>