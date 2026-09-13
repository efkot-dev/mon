<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

if (!function_exists('dt_port_key')) {
    function dt_port_key(int $oltId, string $sfpid): string {
        return $oltId . ':' . trim((string)$sfpid);
    }

    function dt_placeholder_map(array $values, string $prefix = 'p'): array {
        $placeholders = [];
        $params = [];
        foreach (array_values($values) as $index => $value) {
            $name = ':' . $prefix . $index;
            $placeholders[] = $name;
            $params[$name] = $value;
        }
        return [$placeholders, $params];
    }

    function dt_user_is_admin(array $user): bool {
        return isset($user['class']) && (int)$user['class'] >= 4;
    }

    function dt_to_float($value): ?float {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }
        if (!is_numeric($value)) {
            return null;
        }
        return (float)$value;
    }

    function dt_fetch_accessible_olts(PDO $pdo, array $user): array {
        if (dt_user_is_admin($user)) {
            $stmt = $pdo->query("SELECT id, place, model, oidid, location, netip, device, groups FROM switch WHERE device = 'olt' ORDER BY place ASC, id ASC");
        } else {
            $stmt = $pdo->prepare("SELECT s.id, s.place, s.model, s.oidid, s.location, s.netip, s.device, s.groups
                FROM switch s
                INNER JOIN checkaccess a ON a.types = CONCAT('dev', s.id)
                WHERE s.device = 'olt' AND a.uid = :uid
                ORDER BY s.place ASC, s.id ASC");
            $stmt->execute([':uid' => (int)$user['id']]);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $row['id'] = (int)$row['id'];
            $row['location'] = (int)($row['location'] ?? 0);
            $row['oidid'] = (int)($row['oidid'] ?? 0);
            $row['groups'] = (int)($row['groups'] ?? 0);
            $result[$row['id']] = $row;
        }
        return $result;
    }

    function dt_capacity_profile(?string $type, ?string $ponName = ''): array {
        $type = strtolower(trim((string)$type));
        $ponName = strtolower(trim((string)$ponName));
        if (strpos($type, 'xgs') !== false || strpos($ponName, 'xgs') !== false) {
            return ['down_mbps' => 10000.0, 'up_mbps' => 10000.0, 'safe_mbps' => 8500.0, 'label' => 'XGS-PON'];
        }
        if (strpos($type, 'xg') !== false || strpos($ponName, 'xg') !== false) {
            return ['down_mbps' => 10000.0, 'up_mbps' => 2500.0, 'safe_mbps' => 2125.0, 'label' => 'XG-PON'];
        }
        if (strpos($type, 'gpon') !== false || strpos($ponName, 'gpon') !== false || strpos($ponName, 'pon') !== false) {
            return ['down_mbps' => 2488.0, 'up_mbps' => 1244.0, 'safe_mbps' => 1057.4, 'label' => 'GPON'];
        }
        if (strpos($type, 'epon') !== false || strpos($ponName, 'epon') !== false) {
            return ['down_mbps' => 1250.0, 'up_mbps' => 1250.0, 'safe_mbps' => 1062.5, 'label' => 'EPON'];
        }
        return ['down_mbps' => 1000.0, 'up_mbps' => 1000.0, 'safe_mbps' => 850.0, 'label' => strtoupper($type ?: 'PON')];
    }

    function dt_fetch_pon_summary(PDO $pdo, array $oltIds): array {
        if (empty($oltIds)) {
            return [];
        }
        [$ph, $params] = dt_placeholder_map(array_values($oltIds), 'olt');
        $sql = "SELECT
                    sp.id,
                    CAST(sp.oltid AS UNSIGNED) AS oltid,
                    sp.pon,
                    sp.type,
                    sp.sfpid,
                    sp.sort,
                    sp.support,
                    sp.count AS stored_count,
                    s.place,
                    s.location,
                    s.groups,
                    s.model,
                    COALESCE(cnt.total_onu, 0) AS total_onu,
                    COALESCE(cnt.online_onu, 0) AS online_onu,
                    COALESCE(cnt.offline_onu, 0) AS offline_onu,
                    COALESCE(los.los_24h, 0) AS los_24h
                FROM switch_pon sp
                INNER JOIN switch s ON s.id = CAST(sp.oltid AS UNSIGNED)
                LEFT JOIN (
                    SELECT
                        olt,
                        portolt,
                        COUNT(*) AS total_onu,
                        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS online_onu,
                        SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS offline_onu
                    FROM onus
                    GROUP BY olt, portolt
                ) cnt ON cnt.olt = CAST(sp.oltid AS UNSIGNED) AND cnt.portolt = CAST(sp.sfpid AS UNSIGNED)
                LEFT JOIN (
                    SELECT
                        olt,
                        portolt,
                        COUNT(*) AS los_24h
                    FROM onus
                    WHERE status = 2
                      AND offline >= NOW() - INTERVAL 24 HOUR
                      AND reason IN ('err6', 'err8')
                    GROUP BY olt, portolt
                ) los ON los.olt = CAST(sp.oltid AS UNSIGNED) AND los.portolt = CAST(sp.sfpid AS UNSIGNED)
                WHERE CAST(sp.oltid AS UNSIGNED) IN (" . implode(',', $ph) . ")
                ORDER BY s.place ASC, sp.sort ASC, sp.pon ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $oltid = (int)$row['oltid'];
            $sfpid = trim((string)$row['sfpid']);
            $key = dt_port_key($oltid, $sfpid);
            $capacity = dt_capacity_profile($row['type'] ?? '', $row['pon'] ?? '');
            $support = max(1, (int)($row['support'] ?? 0));
            $totalOnu = (int)($row['total_onu'] ?? 0);
            $row['id'] = (int)$row['id'];
            $row['oltid'] = $oltid;
            $row['sfpid'] = $sfpid;
            $row['sort'] = (int)($row['sort'] ?? 0);
            $row['support'] = $support;
            $row['total_onu'] = $totalOnu;
            $row['online_onu'] = (int)($row['online_onu'] ?? 0);
            $row['offline_onu'] = (int)($row['offline_onu'] ?? 0);
            $row['los_24h'] = (int)($row['los_24h'] ?? 0);
            $row['location'] = (int)($row['location'] ?? 0);
            $row['groups'] = (int)($row['groups'] ?? 0);
            $row['free_slots'] = max(0, $support - $totalOnu);
            $row['split_ratio'] = $support > 0 ? round($totalOnu / $support, 4) : 0.0;
            $row['capacity'] = $capacity;
            $row['current_peak_mbps'] = 0.0;
            $row['current_avg_mbps'] = 0.0;
            $row['traffic_samples'] = 0;
            $row['risk_score'] = 0;
            $row['risk_level'] = 'low';
            $row['risk_reasons'] = [];
            $result[$key] = $row;
        }
        return $result;
    }

    function dt_fetch_port_traffic_stats(PDO $pdo, array $pons, int $hours = 24): array {
        if (empty($pons)) {
            return [];
        }
        $deviceIds = [];
        foreach ($pons as $port) {
            $deviceIds[(int)$port['oltid']] = (int)$port['oltid'];
        }
        [$ph, $params] = dt_placeholder_map(array_values($deviceIds), 'dev');
        $sql = "SELECT
                    tm.deviceid,
                    tm.llid,
                    COUNT(sd.id) AS samples,
                    MAX(COALESCE(sd.in_bps, 0)) AS max_in_bps,
                    MAX(COALESCE(sd.out_bps, 0)) AS max_out_bps,
                    AVG(COALESCE(sd.in_bps, 0)) AS avg_in_bps,
                    AVG(COALESCE(sd.out_bps, 0)) AS avg_out_bps
                FROM traff_monitor tm
                INNER JOIN snmp_data sd ON sd.portid = tm.id
                WHERE tm.deviceid IN (" . implode(',', $ph) . ")
                  AND sd.timestamp >= NOW() - INTERVAL " . max(1, (int)$hours) . " HOUR
                GROUP BY tm.deviceid, tm.llid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $key = dt_port_key((int)$row['deviceid'], (string)$row['llid']);
            $maxIn = ((float)$row['max_in_bps']) / 1000000;
            $maxOut = ((float)$row['max_out_bps']) / 1000000;
            $avgIn = ((float)$row['avg_in_bps']) / 1000000;
            $avgOut = ((float)$row['avg_out_bps']) / 1000000;
            $result[$key] = [
                'samples' => (int)$row['samples'],
                'peak_mbps' => round(max($maxIn, $maxOut), 2),
                'avg_mbps' => round(max($avgIn, $avgOut), 2),
            ];
        }
        return $result;
    }

    function dt_fetch_tree_summary(PDO $pdo, array $oltIds): array {
        if (empty($oltIds)) {
            return [];
        }
        [$ph, $params] = dt_placeholder_map(array_values($oltIds), 'tree');
        $sql = "SELECT
                    x.pontree,
                    pt.name AS tree_name,
                    x.olt,
                    x.portolt,
                    COUNT(DISTINCT x.idonu) AS onu_count,
                    s.place,
                    s.location,
                    sp.pon,
                    sp.sfpid
                FROM (
                    SELECT od.pontree, o.idonu, o.olt, o.portolt
                    FROM onusdata od
                    INNER JOIN onus o ON o.mac = od.onukey
                    WHERE od.pontree IS NOT NULL AND od.pontree > 0
                    UNION ALL
                    SELECT od.pontree, o.idonu, o.olt, o.portolt
                    FROM onusdata od
                    INNER JOIN onus o ON o.sn = od.onukey
                    WHERE od.pontree IS NOT NULL AND od.pontree > 0
                ) x
                INNER JOIN pontree pt ON pt.id = x.pontree
                INNER JOIN switch s ON s.id = x.olt
                LEFT JOIN switch_pon sp ON CAST(sp.oltid AS UNSIGNED) = x.olt AND CAST(sp.sfpid AS UNSIGNED) = x.portolt
                WHERE x.olt IN (" . implode(',', $ph) . ")
                GROUP BY x.pontree, x.olt, x.portolt, pt.name, s.place, s.location, sp.pon, sp.sfpid
                ORDER BY pt.name ASC, s.place ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $treeId = (int)$row['pontree'];
            if (!isset($result[$treeId])) {
                $result[$treeId] = [
                    'id' => $treeId,
                    'name' => (string)$row['tree_name'],
                    'location' => (int)($row['location'] ?? 0),
                    'onu_count' => 0,
                    'ports' => [],
                ];
            }
            $portKey = dt_port_key((int)$row['olt'], (string)$row['sfpid']);
            $onuCount = (int)$row['onu_count'];
            $result[$treeId]['onu_count'] += $onuCount;
            $result[$treeId]['ports'][$portKey] = [
                'port_key' => $portKey,
                'oltid' => (int)$row['olt'],
                'sfpid' => trim((string)$row['sfpid']),
                'pon' => (string)($row['pon'] ?? ''),
                'place' => (string)($row['place'] ?? ''),
                'onu_count' => $onuCount,
            ];
        }
        $stmt->closeCursor();
        return $result;
    }

    function dt_fetch_signal_trends(PDO $pdo, array $oltIds, int $days = 7): array {
        if (empty($oltIds)) {
            return [];
        }
        [$ph, $params] = dt_placeholder_map(array_values($oltIds), 'sig');
        $sql = "SELECT o.idonu, o.olt, o.portolt, h.signal
                FROM onus o
                INNER JOIN historysignal h ON h.onu = o.idonu
                WHERE o.status = 1
                  AND o.olt IN (" . implode(',', $ph) . ")
                  AND h.signal IS NOT NULL
                  AND h.signal <> ''
                  AND CAST(h.signal AS DECIMAL(10,2)) BETWEEN -40 AND -10
                  AND h.datetime >= NOW() - INTERVAL " . max(1, (int)$days) . " DAY
                ORDER BY o.idonu ASC, h.datetime ASC";
        $driver = '';
        try {
            $driver = (string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (Throwable $e) {
            $driver = '';
        }
        if ($driver === 'mysql' && defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
            $stmt = $pdo->prepare($sql, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false));
        } else {
            $stmt = $pdo->prepare($sql);
        }
        $stmt->execute($params);
        $result = [];
        $currentOnuId = 0;
        $currentOltId = 0;
        $currentPortOlt = '';
        $firstSignal = null;
        $lastSignal = null;
        $sampleCount = 0;

        $flushCurrentOnu = static function () use (&$result, &$currentOnuId, &$currentOltId, &$currentPortOlt, &$firstSignal, &$lastSignal, &$sampleCount) {
            if ($currentOnuId <= 0 || $sampleCount < 3 || $firstSignal === null || $lastSignal === null) {
                return;
            }
            $drop = round($lastSignal - $firstSignal, 2);
            if ($drop >= -0.8) {
                return;
            }
            $key = dt_port_key((int)$currentOltId, (string)$currentPortOlt);
            if (!isset($result[$key])) {
                $result[$key] = [
                    'affected_onu' => 0,
                    'sum_drop_db' => 0.0,
                    'worst_drop_db' => 0.0,
                ];
            }
            $result[$key]['affected_onu']++;
            $result[$key]['sum_drop_db'] += abs($drop);
            $result[$key]['worst_drop_db'] = max($result[$key]['worst_drop_db'], abs($drop));
        };

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $signal = dt_to_float($row['signal']);
            if ($signal === null || $signal > -10 || $signal < -40) {
                continue;
            }
            $onuId = (int)$row['idonu'];
            if ($currentOnuId !== 0 && $onuId !== $currentOnuId) {
                $flushCurrentOnu();
                $firstSignal = null;
                $lastSignal = null;
                $sampleCount = 0;
            }
            if ($onuId !== $currentOnuId) {
                $currentOnuId = $onuId;
                $currentOltId = (int)$row['olt'];
                $currentPortOlt = trim((string)$row['portolt']);
            }
            if ($firstSignal === null) {
                $firstSignal = $signal;
            }
            $lastSignal = $signal;
            $sampleCount++;
        }
        $flushCurrentOnu();
        $stmt->closeCursor();

        foreach ($result as $key => $item) {
            $result[$key]['avg_drop_db'] = $item['affected_onu'] > 0 ? round($item['sum_drop_db'] / $item['affected_onu'], 2) : 0.0;
        }
        return $result;
    }

    function dt_fetch_crc_risks(PDO $pdo, array $pons, int $hours = 24): array {
        if (empty($pons)) {
            return [];
        }
        $deviceIds = [];
        foreach ($pons as $port) {
            $deviceIds[(int)$port['oltid']] = (int)$port['oltid'];
        }
        [$ph, $params] = dt_placeholder_map(array_values($deviceIds), 'crc');
        $sql = "SELECT deviceid, llid, COUNT(*) AS samples,
                    COALESCE(SUM(COALESCE(newin, 0) + COALESCE(newout, 0)), 0) AS crc_24h,
                    COALESCE(MAX(COALESCE(newin, 0) + COALESCE(newout, 0)), 0) AS peak_spike
                FROM switch_port_err
                WHERE deviceid IN (" . implode(',', $ph) . ")
                  AND added >= NOW() - INTERVAL " . max(1, (int)$hours) . " HOUR
                GROUP BY deviceid, llid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $key = dt_port_key((int)$row['deviceid'], (string)$row['llid']);
            $result[$key] = [
                'samples' => (int)$row['samples'],
                'crc_24h' => (int)$row['crc_24h'],
                'peak_spike' => (int)$row['peak_spike'],
            ];
        }
        return $result;
    }

    function dt_fetch_temp_risks(PDO $pdo, array $olts): array {
        if (empty($olts)) {
            return [];
        }
        $netipToOlt = [];
        foreach ($olts as $olt) {
            if (!empty($olt['netip'])) {
                $netipToOlt[(string)$olt['netip']] = (int)$olt['id'];
            }
        }
        if (empty($netipToOlt)) {
            return [];
        }
        $stmt = $pdo->query("SELECT mt.id, mt.netip, mt.temp, mt.critical_temp, td.data
            FROM monitor_temp mt
            LEFT JOIN tempdate td ON td.file = CONCAT('temp_device_', mt.id)
            WHERE mt.device IN ('olt', 'sfp', 'switch', 'other')");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $netip = (string)($row['netip'] ?? '');
            if ($netip === '' || !isset($netipToOlt[$netip])) {
                continue;
            }
            $oltId = $netipToOlt[$netip];
            $critical = dt_to_float($row['critical_temp']);
            if ($critical === null || $critical <= 0) {
                $critical = 65.0;
            }
            $current = dt_to_float($row['temp']);
            if ($current === null) {
                $current = 0.0;
            }
            $history = [];
            if (!empty($row['data'])) {
                $decoded = json_decode($row['data'], true);
                if (isset($decoded['history']) && is_array($decoded['history'])) {
                    foreach ($decoded['history'] as $point) {
                        $value = dt_to_float($point['value'] ?? null);
                        if ($value !== null) {
                            $history[] = $value;
                        }
                    }
                }
            }
            $delta = count($history) >= 2 ? round(end($history) - reset($history), 2) : 0.0;
            $score = 0;
            $reasons = [];
            if ($current >= ($critical - 5)) {
                $score += 2;
                $reasons[] = 'SFP/температура біля критичної межі';
            }
            if ($delta >= 5) {
                $score += 2;
                $reasons[] = 'температура зростає';
            }
            if ($score === 0) {
                continue;
            }
            if (!isset($result[$oltId])) {
                $result[$oltId] = ['score' => 0, 'max_temp' => 0.0, 'reasons' => []];
            }
            $result[$oltId]['score'] += $score;
            $result[$oltId]['max_temp'] = max($result[$oltId]['max_temp'], $current);
            $result[$oltId]['reasons'] = array_values(array_unique(array_merge($result[$oltId]['reasons'], $reasons)));
        }
        return $result;
    }

    function dt_attach_traffic(array $pons, array $traffic): array {
        foreach ($pons as $key => $port) {
            if (!isset($traffic[$key])) {
                continue;
            }
            $pons[$key]['current_peak_mbps'] = $traffic[$key]['peak_mbps'];
            $pons[$key]['current_avg_mbps'] = $traffic[$key]['avg_mbps'];
            $pons[$key]['traffic_samples'] = $traffic[$key]['samples'];
        }
        return $pons;
    }

    function dt_predict_risks(PDO $pdo, array $olts, array $pons, int $days = 7): array {
        $signal = dt_fetch_signal_trends($pdo, array_keys($olts), $days);
        $crc = dt_fetch_crc_risks($pdo, $pons, 24);
        $temp = dt_fetch_temp_risks($pdo, $olts);
        foreach ($pons as $key => $port) {
            $score = 0;
            $reasons = [];
            if ((int)$port['los_24h'] >= 5) {
                $score += 3;
                $reasons[] = 'багато LOS/offline за 24 години';
            } elseif ((int)$port['los_24h'] > 0) {
                $score += 1;
                $reasons[] = 'є LOS/offline за 24 години';
            }
            if (isset($signal[$key])) {
                if ((int)$signal[$key]['affected_onu'] >= 3) {
                    $score += 2;
                    $reasons[] = 'групове падіння RX ONU';
                }
                if ((float)$signal[$key]['avg_drop_db'] >= 1.5) {
                    $score += 2;
                    $reasons[] = 'середній дрейф сигналу > 1.5 dB';
                }
                $pons[$key]['signal_risk'] = $signal[$key];
            }
            if (isset($crc[$key])) {
                if ((int)$crc[$key]['crc_24h'] >= 1000) {
                    $score += 3;
                    $reasons[] = 'сплеск CRC/помилок порту';
                } elseif ((int)$crc[$key]['crc_24h'] >= 100) {
                    $score += 2;
                    $reasons[] = 'помилки порту зростають';
                }
                $pons[$key]['crc_risk'] = $crc[$key];
            }
            if (isset($temp[(int)$port['oltid']])) {
                $score += (int)$temp[(int)$port['oltid']]['score'];
                $reasons = array_merge($reasons, $temp[(int)$port['oltid']]['reasons']);
                $pons[$key]['temp_risk'] = $temp[(int)$port['oltid']];
            }
            if ($port['split_ratio'] >= 0.9) {
                $score += 2;
                $reasons[] = 'порт майже вичерпав спліт';
            } elseif ($port['split_ratio'] >= 0.8) {
                $score += 1;
                $reasons[] = 'порт близький до ліміту спліту';
            }
            if ($port['current_peak_mbps'] >= $port['capacity']['safe_mbps']) {
                $score += 3;
                $reasons[] = 'поточне навантаження перевищує безпечний поріг';
            } elseif ($port['current_peak_mbps'] >= ($port['capacity']['safe_mbps'] * 0.8)) {
                $score += 1;
                $reasons[] = 'високе поточне навантаження';
            }
            $pons[$key]['risk_score'] = $score;
            $pons[$key]['risk_level'] = $score >= 7 ? 'high' : ($score >= 4 ? 'medium' : 'low');
            $pons[$key]['risk_reasons'] = array_values(array_unique($reasons));
        }
        uasort($pons, static function ($a, $b) {
            if ($a['risk_score'] === $b['risk_score']) {
                return strcmp((string)$a['pon'], (string)$b['pon']);
            }
            return $b['risk_score'] <=> $a['risk_score'];
        });
        return $pons;
    }

    function dt_build_dashboard(array $pons, array $trees): array {
        $summary = ['total_pons' => count($pons),'total_trees' => count($trees),'total_onu' => 0,'high_risk' => 0,'medium_risk' => 0,'capacity_hotspots' => 0,'bandwidth_hotspots' => 0,'los_hotspots' => 0];
        foreach ($pons as $port) {
            $summary['total_onu'] += (int)$port['total_onu'];
            if ($port['risk_level'] === 'high') {
                $summary['high_risk']++;
            } elseif ($port['risk_level'] === 'medium') {
                $summary['medium_risk']++;
            }
            if ($port['split_ratio'] >= 0.8) {
                $summary['capacity_hotspots']++;
            }
            if ($port['current_peak_mbps'] >= ($port['capacity']['safe_mbps'] * 0.8)) {
                $summary['bandwidth_hotspots']++;
            }
            if ((int)$port['los_24h'] > 0) {
                $summary['los_hotspots']++;
            }
        }
        return $summary;
    }

    function dt_simulate_failure(array $olts, array $pons, array $trees, array $params): array {
        $type = trim((string)($params['entity_type'] ?? 'pon'));
        $perOnuMbps = max(1, (int)($params['per_onu_mbps'] ?? 30));
        $affectedCount = 0;
        $affectedLabel = '';
        $affectedPorts = [];
        $location = 0;
        if ($type === 'olt') {
            $oltId = (int)($params['olt_id'] ?? 0);
            if (!isset($olts[$oltId])) {
                return ['error' => 'OLT не знайдено для симуляції.'];
            }
            $affectedLabel = 'OLT ' . ($olts[$oltId]['place'] ?: ('#' . $oltId));
            $location = (int)$olts[$oltId]['location'];
            foreach ($pons as $key => $port) {
                if ((int)$port['oltid'] !== $oltId) {
                    continue;
                }
                $affectedCount += (int)$port['total_onu'];
                $affectedPorts[$key] = $port;
            }
        } elseif ($type === 'tree') {
            $treeId = (int)($params['tree_id'] ?? 0);
            if (!isset($trees[$treeId])) {
                return ['error' => 'PON дерево не знайдено для симуляції.'];
            }
            $tree = $trees[$treeId];
            $affectedLabel = 'Гілка ' . $tree['name'];
            $location = (int)$tree['location'];
            $affectedCount = (int)$tree['onu_count'];
            foreach ($tree['ports'] as $portKey => $portInfo) {
                if (isset($pons[$portKey])) {
                    $affectedPorts[$portKey] = $pons[$portKey];
                }
            }
        } else {
            $portKey = trim((string)($params['port_key'] ?? ''));
            if (!isset($pons[$portKey])) {
                return ['error' => 'PON порт не знайдено для симуляції.'];
            }
            $port = $pons[$portKey];
            $affectedLabel = 'PON ' . $port['pon'] . ' / ' . $port['place'];
            $location = (int)$port['location'];
            $affectedCount = (int)$port['total_onu'];
            $affectedPorts[$portKey] = $port;
        }
        $candidates = [];
        foreach ($pons as $key => $port) {
            if (isset($affectedPorts[$key])) {
                continue;
            }
            $sameLocation = $location > 0 && (int)$port['location'] === $location;
            $sameOlt = false;
            foreach ($affectedPorts as $affectedPort) {
                if ((int)$affectedPort['oltid'] === (int)$port['oltid']) {
                    $sameOlt = true;
                    break;
                }
            }
            if (!$sameLocation && !$sameOlt) {
                continue;
            }
            $port['assign_onu'] = 0;
            $port['projected_onu'] = (int)$port['total_onu'];
            $port['projected_peak_mbps'] = (float)$port['current_peak_mbps'];
            $candidates[$key] = $port;
        }
        uasort($candidates, static function ($a, $b) {
            if ($a['free_slots'] === $b['free_slots']) {
                return $b['capacity']['safe_mbps'] <=> $a['capacity']['safe_mbps'];
            }
            return $b['free_slots'] <=> $a['free_slots'];
        });
        $remaining = $affectedCount;
        foreach ($candidates as $key => $candidate) {
            if ($remaining <= 0) {
                break;
            }
            $assign = min((int)$candidate['free_slots'], $remaining);
            $candidates[$key]['assign_onu'] = $assign;
            $candidates[$key]['projected_onu'] = (int)$candidate['total_onu'] + $assign;
            $candidates[$key]['projected_peak_mbps'] = round((float)$candidate['current_peak_mbps'] + ($assign * $perOnuMbps), 2);
            $remaining -= $assign;
        }
        $overloads = [];
        foreach ($candidates as $candidate) {
            if ($candidate['assign_onu'] <= 0) {
                continue;
            }
            $splitExceeded = $candidate['projected_onu'] > (int)$candidate['support'];
            $bwExceeded = $candidate['projected_peak_mbps'] > (float)$candidate['capacity']['safe_mbps'];
            if ($splitExceeded || $bwExceeded) {
                $overloads[] = ['pon' => $candidate['pon'],'place' => $candidate['place'],'reason' => $splitExceeded && $bwExceeded ? 'спліт і пропускна здатність' : ($splitExceeded ? 'спліт' : 'пропускна здатність')];
            }
        }
        return ['entity_label' => $affectedLabel,'affected_onu' => $affectedCount,'candidates' => $candidates,'overloads' => $overloads,'unplaced_onu' => $remaining];
    }

    function dt_plan_capacity(array $pons, array $params): array {
        $portKey = trim((string)($params['port_key'] ?? ''));
        $extraOnu = max(1, (int)($params['extra_onu'] ?? 200));
        $perOnuMbps = max(1, (int)($params['per_onu_mbps'] ?? 25));
        if (!isset($pons[$portKey])) {
            return ['error' => 'PON порт не знайдено для планування.'];
        }
        $port = $pons[$portKey];
        $projectedOnu = (int)$port['total_onu'] + $extraOnu;
        $projectedSplit = round($projectedOnu / max(1, (int)$port['support']), 4);
        $projectedPeak = round((float)$port['current_peak_mbps'] + ($extraOnu * $perOnuMbps), 2);
        $splitExceeded = $projectedOnu > (int)$port['support'];
        $bandwidthExceeded = $projectedPeak > (float)$port['capacity']['safe_mbps'];
        $needNewPort = $splitExceeded || $bandwidthExceeded;
        $sameOltReserves = [];
        foreach ($pons as $key => $candidate) {
            if ($key === $portKey || (int)$candidate['oltid'] !== (int)$port['oltid'] || (int)$candidate['free_slots'] <= 0) {
                continue;
            }
            $sameOltReserves[$key] = $candidate;
        }
        uasort($sameOltReserves, static function ($a, $b) {
            return $b['free_slots'] <=> $a['free_slots'];
        });
        return ['port' => $port,'projected_onu' => $projectedOnu,'projected_split' => $projectedSplit,'projected_peak_mbps' => $projectedPeak,'split_exceeded' => $splitExceeded,'bandwidth_exceeded' => $bandwidthExceeded,'need_new_port' => $needNewPort,'reserve_ports' => $sameOltReserves];
    }

    function dt_build_ai_summary(array $riskPorts): string {
        if (empty($riskPorts)) {
            return 'Суттєвих аномалій не виявлено: великі кластери offline/LOS, температурні сплески та масове падіння RX зараз не домінують.';
        }
        $top = reset($riskPorts);
        $parts = ['Найбільш підозрілий кластер зараз: PON ' . $top['pon'] . ' на ' . $top['place'] . '.'];
        $los = (int)($top['los_24h'] ?? 0);
        if ($los > 0) {
            $parts[] = $los . ' ONU вже мали LOS/offline за останні 24 години.';
        }
        if (isset($top['signal_risk']['affected_onu'])) {
            $parts[] = (int)$top['signal_risk']['affected_onu'] . ' ONU показують групове падіння RX, середній дрейф близько ' . number_format((float)$top['signal_risk']['avg_drop_db'], 2, '.', '') . ' dB.';
        }
        if (!empty($top['temp_risk']['max_temp'])) {
            $parts[] = 'Температура SFP/OLT доходила до ' . number_format((float)$top['temp_risk']['max_temp'], 1, '.', '') . '°C.';
        }
        if (!empty($top['crc_risk']['crc_24h'])) {
            $parts[] = 'CRC/портові помилки також ростуть: +' . (int)$top['crc_risk']['crc_24h'] . ' за 24 години.';
        }
        $parts[] = 'Ймовірна причина: деградація оптичного тракту або проблемний PON/SFP; перевірити магістраль цієї гілки, сплітер і SFP порту.';
        return implode(' ', $parts);
    }

    function dt_level_badge(string $level): string {
        $map = ['high' => '<span class="dt-badge dt-badge-high">High</span>','medium' => '<span class="dt-badge dt-badge-medium">Medium</span>','low' => '<span class="dt-badge dt-badge-low">Low</span>'];
        return $map[$level] ?? $map['low'];
    }

    function dt_yes_no(bool $value): string {
        return $value ? 'Так' : 'Ні';
    }

    function dt_fmt_mbps($value): string {
        return number_format((float)$value, 2, '.', ' ') . ' Mbps';
    }

    function dt_fmt_percent($value): string {
        return number_format(((float)$value) * 100, 1, '.', ' ') . '%';
    }

    function dt_fmt_db($value): string {
        return number_format((float)$value, 2, '.', ' ') . ' dB';
    }

    function dt_port_action_text(array $port): string {
        if (!empty($port['temp_risk']['max_temp'])) {
            return 'Перевірити SFP/температуру OLT і вентиляцію.';
        }
        if (!empty($port['crc_risk']['crc_24h']) && (int)$port['crc_risk']['crc_24h'] >= 100) {
            return 'Перевірити порт, CRC, патчкорди та uplink до PON.';
        }
        if (!empty($port['signal_risk']['affected_onu']) && (int)$port['signal_risk']['affected_onu'] >= 3) {
            return 'Перевірити магістраль, зварки та сплітер по цій гілці.';
        }
        if ((int)($port['los_24h'] ?? 0) > 0) {
            return 'Подивитись історію LOS/offline та проблемні ONU на порту.';
        }
        if ((float)($port['split_ratio'] ?? 0) >= 0.9) {
            return 'Планувати розвантаження порту або новий PON.';
        }
        if ((float)($port['current_peak_mbps'] ?? 0) >= (float)($port['capacity']['safe_mbps'] ?? 0)) {
            return 'Порт упирається у пропускну здатність, потрібне розвантаження.';
        }
        return 'Перевірити порт детальніше.';
    }

    function dt_find_reserve_for_port(array $pons, array $sourcePort, int $limit = 3): array {
        $candidates = [];
        foreach ($pons as $key => $candidate) {
            if ($key === dt_port_key((int)$sourcePort['oltid'], (string)$sourcePort['sfpid'])) {
                continue;
            }
            if ((int)$candidate['free_slots'] <= 0) {
                continue;
            }
            $sameOlt = (int)$candidate['oltid'] === (int)$sourcePort['oltid'];
            $sameLocation = (int)$candidate['location'] > 0 && (int)$candidate['location'] === (int)$sourcePort['location'];
            if (!$sameOlt && !$sameLocation) {
                continue;
            }
            $candidate['_same_olt'] = $sameOlt ? 1 : 0;
            $candidate['_same_location'] = $sameLocation ? 1 : 0;
            $candidates[] = $candidate;
        }
        usort($candidates, static function ($a, $b) {
            if ($a['_same_olt'] !== $b['_same_olt']) {
                return $b['_same_olt'] <=> $a['_same_olt'];
            }
            if ($a['free_slots'] !== $b['free_slots']) {
                return $b['free_slots'] <=> $a['free_slots'];
            }
            return $b['capacity']['safe_mbps'] <=> $a['capacity']['safe_mbps'];
        });
        return array_slice($candidates, 0, max(1, $limit));
    }

    function dt_reserve_capacity_sum(array $ports): int {
        $sum = 0;
        foreach ($ports as $port) {
            $sum += (int)($port['free_slots'] ?? 0);
        }
        return $sum;
    }

    function dt_fetch_onu_error_summary(PDO $pdo, array $oltIds, int $hours = 24): array {
        if (empty($oltIds)) {
            return [];
        }

        [$ph, $params] = dt_placeholder_map(array_values($oltIds), 'oe');
        $sql = "SELECT
                    o.olt,
                    o.portolt,
                    COUNT(DISTINCT CASE
                        WHEN COALESCE(oe_last.error, 0) > 10 OR COALESCE(oe_day.delta_period, 0) > 0
                        THEN o.idonu ELSE NULL END
                    ) AS bad_onu_count,
                    COALESCE(SUM(COALESCE(oe_day.delta_period, 0)), 0) AS error_delta,
                    COALESCE(MAX(COALESCE(oe_last.error, 0)), 0) AS max_last_error
                FROM onus o
                LEFT JOIN (
                    SELECT e.idonu, e.error
                    FROM onus_error e
                    INNER JOIN (
                        SELECT idonu, MAX(id) AS max_id
                        FROM onus_error
                        GROUP BY idonu
                    ) mx ON mx.idonu = e.idonu AND mx.max_id = e.id
                ) oe_last ON oe_last.idonu = o.idonu
                LEFT JOIN (
                    SELECT idonu, SUM(GREATEST(riznica, 0)) AS delta_period
                    FROM onus_error
                    WHERE added >= NOW() - INTERVAL " . max(1, (int)$hours) . " HOUR
                    GROUP BY idonu
                ) oe_day ON oe_day.idonu = o.idonu
                WHERE o.olt IN (" . implode(',', $ph) . ")
                GROUP BY o.olt, o.portolt
                HAVING bad_onu_count > 0 OR error_delta > 0 OR max_last_error > 0";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = dt_port_key((int)$row['olt'], (string)$row['portolt']);
            $result[$key] = [
                'bad_onu_count' => (int)($row['bad_onu_count'] ?? 0),
                'error_delta' => (int)($row['error_delta'] ?? 0),
                'max_last_error' => (int)($row['max_last_error'] ?? 0),
            ];
        }
        $stmt->closeCursor();
        return $result;
    }

    function dt_fetch_sfp_signal_summary(PDO $pdo, array $oltIds, int $hours = 24): array {
        if (empty($oltIds)) {
            return [];
        }

        [$ph, $params] = dt_placeholder_map(array_values($oltIds), 'sfp');
        $sql = "SELECT
                    sp.deviceid AS oltid,
                    sp.llid,
                    COUNT(*) AS cnt,
                    AVG(CASE
                        WHEN ss.datetime >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                        THEN CAST(REPLACE(ss.rx, ',', '.') AS DECIMAL(10,2))
                    END) AS avg_2h,
                    AVG(CASE
                        WHEN ss.datetime < DATE_SUB(NOW(), INTERVAL 2 HOUR)
                         AND ss.datetime >= DATE_SUB(NOW(), INTERVAL 4 HOUR)
                        THEN CAST(REPLACE(ss.rx, ',', '.') AS DECIMAL(10,2))
                    END) AS avg_prev2h,
                    MIN(CAST(REPLACE(ss.rx, ',', '.') AS DECIMAL(10,2))) AS min_rx,
                    MAX(CAST(REPLACE(ss.rx, ',', '.') AS DECIMAL(10,2))) AS max_rx,
                    MAX(ss.datetime) AS last_sample
                FROM signal_sfp ss
                INNER JOIN switch_port sp ON sp.id = ss.sfpid
                WHERE sp.deviceid IN (" . implode(',', $ph) . ")
                  AND ss.datetime >= DATE_SUB(NOW(), INTERVAL " . max(4, (int)$hours) . " HOUR)
                  AND ss.rx REGEXP '[-+]?[0-9]+([.,][0-9]+)?'
                GROUP BY sp.deviceid, sp.llid
                HAVING cnt >= 3";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } catch (Throwable $e) {
            return [];
        }

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = dt_port_key((int)$row['oltid'], (string)$row['llid']);
            $avg2h = dt_to_float($row['avg_2h'] ?? null);
            $avgPrev2h = dt_to_float($row['avg_prev2h'] ?? null);
            $currentRx = ($avg2h !== null) ? $avg2h : dt_to_float($row['min_rx'] ?? null);
            $delta2h = ($avg2h !== null && $avgPrev2h !== null) ? round($avg2h - $avgPrev2h, 2) : null;
            $result[$key] = [
                'current_rx' => $currentRx,
                'delta_2h' => $delta2h,
                'min_rx' => dt_to_float($row['min_rx'] ?? null),
                'max_rx' => dt_to_float($row['max_rx'] ?? null),
                'samples' => (int)($row['cnt'] ?? 0),
                'last_sample' => (string)($row['last_sample'] ?? ''),
            ];
        }
        $stmt->closeCursor();
        return $result;
    }

    function dt_sfp_signal_state($value): array {
        $rx = dt_to_float($value);
        if ($rx === null) {
            return ['level' => 'unknown', 'label' => 'n/a'];
        }

        if ($rx <= -24 || ($rx > 0 && $rx < 2)) {
            return ['level' => 'critical', 'label' => dt_fmt_db($rx)];
        }
        if ($rx <= -20 || ($rx > 0 && $rx < 4)) {
            return ['level' => 'high', 'label' => dt_fmt_db($rx)];
        }
        if ($rx <= -16 || ($rx > 0 && $rx < 6)) {
            return ['level' => 'medium', 'label' => dt_fmt_db($rx)];
        }

        return ['level' => 'normal', 'label' => dt_fmt_db($rx)];
    }

    function dt_case_level_badge(string $level): string {
        $map = [
            'critical' => '<span class="dt-badge dt-badge-critical">Critical</span>',
            'high' => '<span class="dt-badge dt-badge-high">High</span>',
            'medium' => '<span class="dt-badge dt-badge-medium">Medium</span>',
            'low' => '<span class="dt-badge dt-badge-low">Low</span>',
        ];
        return $map[$level] ?? $map['low'];
    }

    function dt_detect_problem_cause(array $case): string {
        if ((int)$case['group_onu_affected'] >= 3 && in_array($case['sfp_state']['level'], ['critical', 'high'], true)) {
            return 'Ймовірно проблема в PON-порту, SFP або на магістралі до спліттера.';
        }
        if ((int)$case['group_onu_affected'] >= 3 && ((int)$case['los_24h'] >= 3 || (int)$case['onu_error_count'] >= 3)) {
            return 'Ймовірно проблема на магістралі, спліттері або зварках цієї гілки.';
        }
        if ((int)$case['port_crc_24h'] >= 100 && in_array($case['sfp_state']['level'], ['critical', 'high', 'medium'], true)) {
            return 'Ймовірно проблема самого порту або SFP-модуля.';
        }
        if ((int)$case['onu_error_count'] >= 3 && (int)$case['group_onu_affected'] < 3) {
            return 'Є помилки на кількох ONU без явної групової просадки — перевірити абонентські відгалуження.';
        }
        if (!empty($case['temp_alarm'])) {
            return 'Можливий перегрів SFP або порту.';
        }
        if ((int)$case['los_24h'] > 0) {
            return 'На порту є offline/LOS, потрібна перевірка цієї PON-гілки.';
        }
        return 'Потрібна додаткова перевірка порту та пов’язаних ONU.';
    }

    function dt_detect_problem_action(array $case): string {
        if ((int)$case['group_onu_affected'] >= 3 && in_array($case['sfp_state']['level'], ['critical', 'high'], true)) {
            return 'Перевірити SFP, патчкорд, розʼєм порту та оптичний тракт до першого спліттера.';
        }
        if ((int)$case['group_onu_affected'] >= 3) {
            return 'Перевірити магістраль, муфти, зварки та спліттер по цій гілці.';
        }
        if ((int)$case['port_crc_24h'] >= 100) {
            return 'Перевірити CRC, конектори, патчкорд, порт та uplink до PON.';
        }
        if ((int)$case['onu_error_count'] >= 3) {
            return 'Відкрити ONU error і перевірити ONU з найбільшими помилками.';
        }
        if (!empty($case['temp_alarm'])) {
            return 'Перевірити температуру, живлення, вентиляцію та сам SFP.';
        }
        return 'Перевірити порт детальніше та переглянути історію сигналів ONU.';
    }

    function dt_build_incident_cases(array $pons, array $signalRisks, array $onuErrors, array $portErrors, array $sfpSignals, array $tempRisks): array {
        $cases = [];

        foreach ($pons as $key => $port) {
            $signal = $signalRisks[$key] ?? ['affected_onu' => 0, 'avg_drop_db' => 0.0, 'worst_drop_db' => 0.0];
            $onuError = $onuErrors[$key] ?? ['bad_onu_count' => 0, 'error_delta' => 0, 'max_last_error' => 0];
            $portError = $portErrors[$key] ?? ['crc_24h' => 0, 'peak_spike' => 0];
            $sfp = $sfpSignals[$key] ?? ['current_rx' => null, 'delta_2h' => null, 'min_rx' => null, 'max_rx' => null, 'samples' => 0];
            $temp = $tempRisks[(int)$port['oltid']] ?? null;

            $totalOnu = max(0, (int)($port['total_onu'] ?? 0));
            $groupAffected = (int)($signal['affected_onu'] ?? 0);
            $groupPercent = $totalOnu > 0 ? round(($groupAffected / $totalOnu) * 100, 1) : 0.0;
            $sfpState = dt_sfp_signal_state($sfp['current_rx'] ?? null);
            $score = 0;
            $symptoms = [];

            if ($groupAffected >= 3) {
                $score += 25;
                $symptoms[] = 'групова просадка RX';
            }
            if ($groupPercent >= 25) {
                $score += 20;
                $symptoms[] = 'просадка у значної частини ONU';
            }
            if ((float)($signal['avg_drop_db'] ?? 0) >= 1.5) {
                $score += 15;
                $symptoms[] = 'середній drift RX > 1.5 dB';
            }
            if ((int)($port['los_24h'] ?? 0) >= 3) {
                $score += 15;
                $symptoms[] = 'є LOS/offline по порту';
            } elseif ((int)($port['los_24h'] ?? 0) > 0) {
                $score += 8;
                $symptoms[] = 'поодинокі LOS/offline';
            }
            if ((int)($onuError['bad_onu_count'] ?? 0) >= 3) {
                $score += 15;
                $symptoms[] = 'є ONU з помилками';
            } elseif ((int)($onuError['bad_onu_count'] ?? 0) > 0) {
                $score += 8;
                $symptoms[] = 'окремі ONU помилки';
            }
            if ((int)($onuError['error_delta'] ?? 0) >= 100) {
                $score += 10;
                $symptoms[] = 'ростуть ONU error';
            }
            if ((int)($portError['crc_24h'] ?? 0) >= 1000) {
                $score += 20;
                $symptoms[] = 'великий CRC на порту';
            } elseif ((int)($portError['crc_24h'] ?? 0) >= 100) {
                $score += 12;
                $symptoms[] = 'CRC на порту';
            }
            if ($sfpState['level'] === 'critical') {
                $score += 25;
                $symptoms[] = 'критичний SFP RX';
            } elseif ($sfpState['level'] === 'high') {
                $score += 16;
                $symptoms[] = 'поганий SFP RX';
            } elseif ($sfpState['level'] === 'medium') {
                $score += 8;
                $symptoms[] = 'SFP RX просідає';
            }
            if (($sfp['delta_2h'] ?? null) !== null && (float)$sfp['delta_2h'] <= -0.5) {
                $score += 12;
                $symptoms[] = 'SFP RX падає за 2 години';
            }
            if ($temp && (int)($temp['score'] ?? 0) > 0) {
                $score += 8;
                $symptoms[] = 'температура SFP/OLT';
            }

            if ($score <= 0) {
                continue;
            }

            $level = 'low';
            if ($score >= 70) {
                $level = 'critical';
            } elseif ($score >= 45) {
                $level = 'high';
            } elseif ($score >= 20) {
                $level = 'medium';
            }

            $case = $port;
            $case['score'] = $score;
            $case['level'] = $level;
            $case['symptoms'] = array_values(array_unique($symptoms));
            $case['group_onu_affected'] = $groupAffected;
            $case['group_onu_percent'] = $groupPercent;
            $case['avg_drop_db'] = round((float)($signal['avg_drop_db'] ?? 0), 2);
            $case['worst_drop_db'] = round((float)($signal['worst_drop_db'] ?? 0), 2);
            $case['onu_error_count'] = (int)($onuError['bad_onu_count'] ?? 0);
            $case['onu_error_delta'] = (int)($onuError['error_delta'] ?? 0);
            $case['onu_max_error'] = (int)($onuError['max_last_error'] ?? 0);
            $case['port_crc_24h'] = (int)($portError['crc_24h'] ?? 0);
            $case['port_crc_peak'] = (int)($portError['peak_spike'] ?? 0);
            $case['sfp_state'] = $sfpState;
            $case['sfp_rx'] = $sfp['current_rx'] ?? null;
            $case['sfp_delta_2h'] = $sfp['delta_2h'] ?? null;
            $case['temp_alarm'] = $temp ? 1 : 0;
            $case['cause'] = dt_detect_problem_cause($case);
            $case['action'] = dt_detect_problem_action($case);
            $cases[$key] = $case;
        }

        uasort($cases, static function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return strcmp((string)$a['pon'], (string)$b['pon']);
            }
            return $b['score'] <=> $a['score'];
        });

        return $cases;
    }

    function dt_build_incident_summary(array $cases, int $totalPorts): array {
        $summary = [
            'total_ports' => $totalPorts,
            'critical_ports' => 0,
            'group_ports' => 0,
            'onu_error_ports' => 0,
            'port_problem_ports' => 0,
            'sfp_problem_ports' => 0,
        ];

        foreach ($cases as $case) {
            if (in_array($case['level'], ['critical', 'high'], true)) {
                $summary['critical_ports']++;
            }
            if ((int)$case['group_onu_affected'] >= 3) {
                $summary['group_ports']++;
            }
            if ((int)$case['onu_error_count'] > 0 || (int)$case['onu_error_delta'] > 0) {
                $summary['onu_error_ports']++;
            }
            if ((int)$case['port_crc_24h'] > 0) {
                $summary['port_problem_ports']++;
            }
            if (in_array($case['sfp_state']['level'], ['critical', 'high', 'medium'], true)) {
                $summary['sfp_problem_ports']++;
            }
        }

        return $summary;
    }

    function dt_collect_incident_data(PDO $pdo, array $user, int $selectedOltId = 0): array {
        $olts = dt_fetch_accessible_olts($pdo, $user);
        if ($selectedOltId > 0 && !isset($olts[$selectedOltId])) {
            $selectedOltId = 0;
        }

        $analysisOlts = $olts;
        if ($selectedOltId > 0 && isset($olts[$selectedOltId])) {
            $analysisOlts = array($selectedOltId => $olts[$selectedOltId]);
        }

        if (empty($analysisOlts)) {
            return [
                'olts' => $olts,
                'selected_olt_id' => $selectedOltId,
                'cases' => [],
                'summary' => dt_build_incident_summary([], 0),
                'top_case' => null,
            ];
        }

        $pons = dt_fetch_pon_summary($pdo, array_keys($analysisOlts));
        $signalRisks = dt_fetch_signal_trends($pdo, array_keys($analysisOlts), 7);
        $onuErrors = dt_fetch_onu_error_summary($pdo, array_keys($analysisOlts), 24);
        $portErrors = dt_fetch_crc_risks($pdo, $pons, 24);
        $sfpSignals = dt_fetch_sfp_signal_summary($pdo, array_keys($analysisOlts), 24);
        $tempRisks = dt_fetch_temp_risks($pdo, $analysisOlts);
        $cases = dt_build_incident_cases($pons, $signalRisks, $onuErrors, $portErrors, $sfpSignals, $tempRisks);
        $summary = dt_build_incident_summary($cases, count($pons));
        $topCase = !empty($cases) ? reset($cases) : null;

        return [
            'olts' => $olts,
            'selected_olt_id' => $selectedOltId,
            'cases' => $cases,
            'summary' => $summary,
            'top_case' => $topCase,
        ];
    }

    function dt_render_main_widget(PDO $pdo, array $user, int $limit = 5): string {
        $data = dt_collect_incident_data($pdo, $user, 0);
        $cases = array_slice($data['cases'], 0, max(1, min($limit, 3)), true);
        $topCase = $data['top_case'];
        $html = '<div class="mainblock dt-main-ai">';
        $html .= '<h3>Проблеми PON</h3>';
        if ($topCase) {
            $topLevelClass = in_array($topCase['level'], ['critical', 'high', 'medium', 'low'], true) ? $topCase['level'] : 'low';
            $topTitle = htmlspecialchars($topCase['place'].' / '.$topCase['pon'], ENT_QUOTES, 'UTF-8');
            $topBadge = htmlspecialchars(ucfirst($topLevelClass), ENT_QUOTES, 'UTF-8');
            $html .= '
            <div class="dt-main-focus dt-main-focus-'.$topLevelClass.'">
                <div class="dt-main-ai-top">
                    <span class="dt-main-ai-badge dt-main-ai-badge-'.$topLevelClass.'">'.$topBadge.'</span>
                    <div class="dt-main-focus-title">'.$topTitle.'</div>
                </div>
                <div class="dt-main-focus-text">'.htmlspecialchars($topCase['cause'], ENT_QUOTES, 'UTF-8').'</div>
                <div class="dt-main-focus-subtext">'.htmlspecialchars($topCase['action'], ENT_QUOTES, 'UTF-8').'</div>
                <div class="dt-main-focus-links">
                    <a href="/?do=portsfpcrc">Порти / SFP / CRC</a>
                    <a href="/?do=signaldegradation">Групові сигнали ONU</a>
                    <a href="/?do=onuerror">ONU error</a>
                </div>
            </div>';
        }
        if (!$topCase) {
            $html .= '
            <div class="dt-main-focus dt-main-focus-low">
                <div class="dt-main-focus-title">Стан PON</div>
                <div class="dt-main-focus-text">Явних групових проблем, критичних CRC або деградації SFP зараз не виявлено.</div>
                <div class="dt-main-focus-links">
                    <a href="/?do=digitaltwin">Відкрити Digital Twin ISP</a>
                </div>
            </div>';
        }
        $html .= '<div class="dt-main-ai-caption">ONU / CRC / SFP</div>';
        $printed = 0;
        foreach ($cases as $case) {
            if ($topCase && (string)$case['place'] === (string)$topCase['place'] && (string)$case['pon'] === (string)$topCase['pon']) {
                continue;
            }
            $facts = [];
            if ((int)$case['group_onu_affected'] > 0) {
                $facts[] = $case['group_onu_affected'].' ONU із просадкою RX';
            }
            if ((int)$case['onu_error_count'] > 0) {
                $facts[] = 'ONU error: '.(int)$case['onu_error_count'];
            }
            if ((int)$case['port_crc_24h'] > 0) {
                $facts[] = 'CRC: '.(int)$case['port_crc_24h'];
            }
            if (!empty($case['sfp_state']['label']) && $case['sfp_state']['label'] !== 'n/a') {
                $facts[] = 'SFP RX: '.$case['sfp_state']['label'];
            }

            $levelClass = in_array($case['level'], ['critical', 'high', 'medium', 'low'], true) ? $case['level'] : 'low';
            $title = htmlspecialchars($case['place'].' / '.$case['pon'], ENT_QUOTES, 'UTF-8');
            $summaryParts = [];
            if (!empty($facts)) {
                $summaryParts[] = implode(', ', $facts);
            }
            $summaryParts[] = $case['cause'];
            $text = htmlspecialchars(implode('. ', $summaryParts), ENT_QUOTES, 'UTF-8');
            $html .= '
            <div class="dt-main-ai-item dt-main-ai-'.$levelClass.'">
                <div class="dt-main-ai-top">
                    <span class="dt-main-ai-badge dt-main-ai-badge-'.$levelClass.'">'.htmlspecialchars(ucfirst($levelClass), ENT_QUOTES, 'UTF-8').'</span>
                    <span class="dt-main-ai-title">'.$title.'</span>
                </div>
                <div class="dt-main-ai-text">'.$text.'</div>
            </div>';
            $printed++;
            if ($printed >= 2) {
                break;
            }
        }
        $html .= '<div class="dt-main-ai-link"><a href="/?do=digitaltwin">Детально</a></div>';
        $html .= '</div>';
        return $html;
    }

    function dt_render_dashboard_block(PDO $pdo, array $user, int $selectedOltId = 0): string {
        $data = dt_collect_incident_data($pdo, $user, $selectedOltId);
        $cases = $data['cases'];
        $summary = $data['summary'];
        $topCase = $data['top_case'];

        $summaryText = 'Явних групових проблем по доступних PON зараз не виявлено.';
        if ($topCase) {
            $summaryText = 'Найбільш підозрілий порт зараз: '.$topCase['place'].' / '.$topCase['pon'].'. '.$topCase['cause'].' '.$topCase['action'];
        }

        $topRows = '';
        $groupRows = '';
        $portRows = '';

        foreach ($cases as $case) {
            $symptomsText = !empty($case['symptoms']) ? implode('; ', $case['symptoms']) : '—';
            $sfpText = $case['sfp_state']['label'];
            $sfpDeltaText = ($case['sfp_delta_2h'] !== null) ? dt_fmt_db($case['sfp_delta_2h']) : '—';
            $groupText = ($case['group_onu_affected'] > 0)
                ? $case['group_onu_affected'].' ONU / '.number_format((float)$case['group_onu_percent'], 1, '.', ' ').'%'
                : '—';

            $topRows .= '<tr>
                <td>'.htmlspecialchars($case['place'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($case['pon'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.dt_case_level_badge($case['level']).'</td>
                <td>'.htmlspecialchars($symptomsText, ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($groupText, ENT_QUOTES, 'UTF-8').'</td>
                <td>'.(int)$case['onu_error_count'].' / '.(int)$case['onu_error_delta'].'</td>
                <td>'.(int)$case['port_crc_24h'].'</td>
                <td>'.htmlspecialchars($sfpText, ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($case['cause'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($case['action'], ENT_QUOTES, 'UTF-8').'</td>
            </tr>';

            if ((int)$case['group_onu_affected'] >= 3 || (float)$case['avg_drop_db'] >= 1.0) {
                $groupRows .= '<tr>
                    <td>'.htmlspecialchars($case['place'], ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.htmlspecialchars($case['pon'], ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.(int)$case['total_onu'].'</td>
                    <td>'.(int)$case['group_onu_affected'].'</td>
                    <td>'.number_format((float)$case['group_onu_percent'], 1, '.', ' ').'%</td>
                    <td>'.dt_fmt_db($case['avg_drop_db']).'</td>
                    <td>'.dt_fmt_db($case['worst_drop_db']).'</td>
                    <td>'.(int)$case['los_24h'].'</td>
                    <td>'.(int)$case['onu_error_count'].'</td>
                    <td>'.htmlspecialchars($case['action'], ENT_QUOTES, 'UTF-8').'</td>
                </tr>';
            }

            if ((int)$case['port_crc_24h'] > 0 || in_array($case['sfp_state']['level'], ['critical', 'high', 'medium'], true) || !empty($case['temp_alarm'])) {
                $portRows .= '<tr>
                    <td>'.htmlspecialchars($case['place'], ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.htmlspecialchars($case['pon'], ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.(int)$case['port_crc_24h'].'</td>
                    <td>'.(int)$case['port_crc_peak'].'</td>
                    <td>'.htmlspecialchars($sfpText, ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.htmlspecialchars($sfpDeltaText, ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.(!empty($case['temp_alarm']) ? 'Так' : 'Ні').'</td>
                    <td>'.htmlspecialchars($case['cause'], ENT_QUOTES, 'UTF-8').'</td>
                    <td>'.htmlspecialchars($case['action'], ENT_QUOTES, 'UTF-8').'</td>
                </tr>';
            }
        }

        if ($topRows === '') {
            $topRows = '<tr><td colspan="10">По доступних OLT зараз немає явних інцидентів за сигналами, ONU error, CRC і SFP.</td></tr>';
        }
        if ($groupRows === '') {
            $groupRows = '<tr><td colspan="10">Групових погіршень сигналу зараз не видно.</td></tr>';
        }
        if ($portRows === '') {
            $portRows = '<tr><td colspan="9">Критичних проблем по CRC / SFP / температурі зараз не видно.</td></tr>';
        }

        return '
        <div class="dt-card">
            <div class="dt-kpis dt-kpis-5">
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$summary['total_ports'].'</span><span class="dt-kpi-label">PON проаналізовано</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$summary['critical_ports'].'</span><span class="dt-kpi-label">Критичних / High</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$summary['group_ports'].'</span><span class="dt-kpi-label">Групові просадки RX</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$summary['onu_error_ports'].'</span><span class="dt-kpi-label">Порти з ONU error</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$summary['sfp_problem_ports'].'</span><span class="dt-kpi-label">Проблемні SFP RX</span></div>
            </div>
            <div class="dt-note">'.htmlspecialchars($summaryText, ENT_QUOTES, 'UTF-8').'</div>
        </div>

        <div class="dt-card">
            <div class="dt-card-title">Де ймовірно проблема зараз</div>
            <div class="dt-help">Тут зведені всі основні ознаки: групове падіння RX у ONU, ONU error, CRC на порту, SFP RX та температура. Якщо одночасно є кілька ознак — це найпідозріліший PON.</div>
            <table class="dt-table">
                <thead><tr><th>OLT</th><th>PON</th><th>Рівень</th><th>Ознаки</th><th>Група RX</th><th>ONU error</th><th>Port CRC</th><th>SFP RX</th><th>Висновок</th><th>Що перевірити</th></tr></thead>
                <tbody>'.$topRows.'</tbody>
            </table>
        </div>

        <div class="dt-grid">
            <div class="dt-card">
                <div class="dt-card-title">Групові погіршення сигналу ONU</div>
                <div class="dt-help">Показано порти, де на кількох ONU одночасно падає RX. Це найчастіше вказує на проблему гілки, муфти, зварки або спліттера, а не однієї ONU.</div>
                <table class="dt-table">
                    <thead><tr><th>OLT</th><th>PON</th><th>ONU</th><th>Погіршилось</th><th>% групи</th><th>Avg drift</th><th>Worst drift</th><th>LOS 24h</th><th>ONU error</th><th>Що перевірити</th></tr></thead>
                    <tbody>'.$groupRows.'</tbody>
                </table>
            </div>

            <div class="dt-card">
                <div class="dt-card-title">Проблеми порту / SFP / CRC</div>
                <div class="dt-help">Тут видно, де є помилки на портах, деградація сигналу SFP або перегрів. Якщо це збігається з погіршенням ONU — проблема вже добре локалізується.</div>
                <table class="dt-table">
                    <thead><tr><th>OLT</th><th>PON</th><th>CRC 24h</th><th>Peak spike</th><th>SFP RX</th><th>SFP Δ 2h</th><th>Temp</th><th>Висновок</th><th>Що перевірити</th></tr></thead>
                    <tbody>'.$portRows.'</tbody>
                </table>
            </div>
        </div>';
    }

    function dt_render_group_signal_block(PDO $pdo, array $user, int $selectedOltId = 0): string {
        $data = dt_collect_incident_data($pdo, $user, $selectedOltId);
        $cases = $data['cases'];
        $rows = '';
        $count = 0;

        foreach ($cases as $case) {
            if ((int)$case['group_onu_affected'] < 3 && (float)$case['avg_drop_db'] < 1.0) {
                continue;
            }
            $count++;
            $rows .= '<tr>
                <td>'.htmlspecialchars($case['place'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($case['pon'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.dt_case_level_badge($case['level']).'</td>
                <td>'.(int)$case['total_onu'].'</td>
                <td>'.(int)$case['group_onu_affected'].'</td>
                <td>'.number_format((float)$case['group_onu_percent'], 1, '.', ' ').'%</td>
                <td>'.dt_fmt_db($case['avg_drop_db']).'</td>
                <td>'.dt_fmt_db($case['worst_drop_db']).'</td>
                <td>'.(int)$case['los_24h'].'</td>
                <td>'.(int)$case['onu_error_count'].'</td>
                <td>'.htmlspecialchars($case['cause'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($case['action'], ENT_QUOTES, 'UTF-8').'</td>
            </tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="12">Групових погіршень сигналу зараз не виявлено.</td></tr>';
        }

        return '
        <div class="dt-card">
            <div class="dt-kpis dt-kpis-4">
                <div class="dt-kpi"><span class="dt-kpi-value">'.$count.'</span><span class="dt-kpi-label">Портів з груповою просадкою</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$data['summary']['group_ports'].'</span><span class="dt-kpi-label">Групових кейсів</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$data['summary']['critical_ports'].'</span><span class="dt-kpi-label">Critical / High</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$data['summary']['onu_error_ports'].'</span><span class="dt-kpi-label">З ONU error</span></div>
            </div>
        </div>
        <div class="dt-card">
            <div class="dt-card-title">Групові погіршення сигналу ONU</div>
            <div class="dt-help">Сторінка показує тільки ті PON, де на кількох ONU одночасно просів RX. Це дає чіткий список проблемних гілок без зайвого шуму.</div>
            <table class="dt-table">
                <thead><tr><th>OLT</th><th>PON</th><th>Рівень</th><th>ONU всього</th><th>Погіршилось</th><th>% групи</th><th>Avg drift</th><th>Worst drift</th><th>LOS 24h</th><th>ONU error</th><th>Причина</th><th>Що перевірити</th></tr></thead>
                <tbody>'.$rows.'</tbody>
            </table>
        </div>';
    }

    function dt_render_port_sfp_crc_block(PDO $pdo, array $user, int $selectedOltId = 0): string {
        $data = dt_collect_incident_data($pdo, $user, $selectedOltId);
        $cases = $data['cases'];
        $rows = '';
        $count = 0;

        foreach ($cases as $case) {
            if ((int)$case['port_crc_24h'] <= 0 && !in_array($case['sfp_state']['level'], ['critical', 'high', 'medium'], true) && empty($case['temp_alarm'])) {
                continue;
            }
            $count++;
            $rows .= '<tr>
                <td>'.htmlspecialchars($case['place'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($case['pon'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.dt_case_level_badge($case['level']).'</td>
                <td>'.(int)$case['port_crc_24h'].'</td>
                <td>'.(int)$case['port_crc_peak'].'</td>
                <td>'.htmlspecialchars($case['sfp_state']['label'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars(($case['sfp_delta_2h'] !== null ? dt_fmt_db($case['sfp_delta_2h']) : '—'), ENT_QUOTES, 'UTF-8').'</td>
                <td>'.(!empty($case['temp_alarm']) ? 'Так' : 'Ні').'</td>
                <td>'.(int)$case['onu_error_count'].'</td>
                <td>'.htmlspecialchars($case['cause'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($case['action'], ENT_QUOTES, 'UTF-8').'</td>
            </tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="11">Проблем по портах, SFP або CRC зараз не виявлено.</td></tr>';
        }

        return '
        <div class="dt-card">
            <div class="dt-kpis dt-kpis-4">
                <div class="dt-kpi"><span class="dt-kpi-value">'.$count.'</span><span class="dt-kpi-label">Проблемних портів</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$data['summary']['port_problem_ports'].'</span><span class="dt-kpi-label">З CRC</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$data['summary']['sfp_problem_ports'].'</span><span class="dt-kpi-label">З проблемним SFP</span></div>
                <div class="dt-kpi"><span class="dt-kpi-value">'.(int)$data['summary']['critical_ports'].'</span><span class="dt-kpi-label">Critical / High</span></div>
            </div>
        </div>
        <div class="dt-card">
            <div class="dt-card-title">Проблеми порту / SFP / CRC</div>
            <div class="dt-help">Сторінка показує тільки проблеми порту: CRC, SFP RX, деградацію SFP за 2 години та перегрів. Інтерфейс строгий і без зайвих блоків.</div>
            <table class="dt-table">
                <thead><tr><th>OLT</th><th>PON</th><th>Рівень</th><th>CRC 24h</th><th>Peak spike</th><th>SFP RX</th><th>SFP Δ 2h</th><th>Temp</th><th>ONU error</th><th>Причина</th><th>Що перевірити</th></tr></thead>
                <tbody>'.$rows.'</tbody>
            </table>
        </div>';
    }

    function dt_build_ai_explanations(array $riskPorts, int $limit = 5): array {
        if (empty($riskPorts)) {
            return [];
        }

        $items = [];
        foreach ($riskPorts as $port) {
            if ((int)($port['risk_score'] ?? 0) <= 0) {
                continue;
            }

            $facts = [];
            if ((int)($port['los_24h'] ?? 0) > 0) {
                $facts[] = 'LOS 24г: ' . (int)$port['los_24h'];
            }
            if (!empty($port['signal_risk']['avg_drop_db'])) {
                $facts[] = 'RX drift: ' . dt_fmt_db($port['signal_risk']['avg_drop_db']);
            }
            if (!empty($port['crc_risk']['crc_24h'])) {
                $facts[] = 'CRC: ' . (int)$port['crc_risk']['crc_24h'];
            }
            if (!empty($port['temp_risk']['max_temp'])) {
                $facts[] = 'Temp: ' . number_format((float)$port['temp_risk']['max_temp'], 1, '.', ' ') . '°C';
            }
            if ((float)($port['current_peak_mbps'] ?? 0) > 0) {
                $facts[] = 'Peak: ' . dt_fmt_mbps($port['current_peak_mbps']);
            }

            $reason = '';
            if (!empty($port['risk_reasons'][0])) {
                $reason = (string)$port['risk_reasons'][0];
            }

            $text = '';
            if (!empty($facts)) {
                $text .= implode(', ', $facts) . '. ';
            }
            if ($reason !== '') {
                $text .= 'Ймовірно: ' . rtrim($reason, '. ') . '. ';
            }
            $text .= 'Що робити: ' . dt_port_action_text($port);

            $items[] = [
                'level' => (string)($port['risk_level'] ?? 'low'),
                'score' => (int)($port['risk_score'] ?? 0),
                'place' => (string)($port['place'] ?? ''),
                'pon' => (string)($port['pon'] ?? ''),
                'title' => trim((string)($port['place'] ?? '') . ' / ' . (string)($port['pon'] ?? ''), ' /'),
                'text' => trim($text),
            ];

            if (count($items) >= max(1, $limit)) {
                break;
            }
        }

        return $items;
    }
}
