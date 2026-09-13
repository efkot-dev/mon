<?php
if (!defined('PONMONITOR')){
    die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
require ROOT_DIR.'/inc/functions/pmon.php';
$DISCHARGE_LEVELS = [
	12 => [12.9, 12.4, 12.0, 11.8],
	24 => [25.8, 24.8, 24.0, 23.6],
	48 => [51.6, 49.6, 48.0, 47.2],
];
function ping3_to_float($value, $fallback = 0.0) {
    if (is_numeric($value)) {
        $v = (float)$value;
        if (is_finite($v)) {
            return $v;
        }
    }
    return (float)$fallback;
}
function ping3_default_full_voltage($typeBattery) {
    $typeBattery = (int)$typeBattery;
    switch ($typeBattery) {
        case 24: return 28.4;
        case 48: return 56.8;
        case 72: return 85.2;
        case 12:
        default: return 14.2;
    }
}
function ping3_nocharge_float_gate($typeBattery) {
    $full = ping3_default_full_voltage($typeBattery);
    $k = max(1, ((int)$typeBattery / 12));
    return $full - (0.5 * $k);
}
function ping3_min_daily_charge_gain($typeBattery) {
    switch ((int)$typeBattery) {
        case 24: return 0.90;
        case 48: return 1.80;
        case 72: return 2.70;
        case 12:
        default: return 0.45;
    }
}
function ping3_signed_voltage($value, $precision = 2) {
    $v = round((float)$value, $precision);
    if ($v > 0) {
        return '+' . $v;
    }
    return (string)$v;
}
function ping3_current_charge_segment($db, int $deviceId, int $hours = 12): array {
    $deviceId = (int)$deviceId;
    $hours = max(2, (int)$hours);
    if ($deviceId <= 0) {
        return [];
    }
    $rows = $db->SimpleWhile("
        SELECT added, volt, energy
        FROM mon_voltage
        WHERE deviceid = {$deviceId}
          AND mon_types = 'ping3'
          AND added >= DATE_SUB(NOW(), INTERVAL {$hours} HOUR)
        ORDER BY added ASC
    ");
    if (!is_array($rows) || count($rows) === 0) {
        return [];
    }
    $segment = [];
    for ($i = count($rows) - 1; $i >= 0; $i--) {
        $energy = (int)($rows[$i]['energy'] ?? 0);
        if ($energy === 1) {
            $segment[] = $rows[$i];
        } elseif (!empty($segment)) {
            break;
        }
    }
    return array_reverse($segment);
}
function ping3_slow_drop_threshold($typeBattery) {
    $typeBattery = (int)$typeBattery;
    switch ($typeBattery) {
        case 24: return 1.20;
        case 48: return 2.40;
        case 72: return 3.60;
        case 12:
        default: return 0.60;
    }
}
function ping3_has_recent_alert($db, $deviceId, $code, $hours = 12) {
    $deviceId = (int)$deviceId;
    $hours = max(1, (int)$hours);
    $code = addslashes((string)$code);
    $marker = "[PING3:{$code}:{$deviceId}]";
    $sql = "SELECT id FROM notification WHERE system = 'pinger' AND added >= DATE_SUB(NOW(), INTERVAL {$hours} HOUR) AND message LIKE '%{$marker}%' LIMIT 1";
    $row = $db->Simple($sql);
    return !empty($row['id']);
}
function ping3_apply_alert_marker($alert, $deviceId) {
    if (!is_array($alert) || empty($alert['code'])) {
        return $alert;
    }
    $deviceId = (int)$deviceId;
    if ($deviceId <= 0) {
        return $alert;
    }
    $marker = '[PING3:' . $alert['code'] . ':' . $deviceId . ']';
    $alert['marker'] = $marker;
    if (isset($alert['message']) && is_string($alert['message']) && strpos($alert['message'], $marker) === false) {
        $alert['message'] .= ' ' . $marker;
    }
    return $alert;
}
function ping3_analyze_no_charge($db, array $device, $currentVoltage) {
    $deviceId = (int)$device['id'];
    $typeBattery = (int)$device['typebattery'];
    $currentVoltage = ping3_to_float($currentVoltage, 0.0);
    if ($deviceId <= 0 || $currentVoltage <= 0) {
        return null;
    }
    // Беремо лише поточний безперервний сегмент зарядки (щоб не склеювати старі періоди).
    $chargeRows = ping3_current_charge_segment($db, $deviceId, 12);
    if (!is_array($chargeRows) || count($chargeRows) < 8) {
        return null;
    }
    $startTs = strtotime($chargeRows[0]['added']);
    $endTs = strtotime($chargeRows[count($chargeRows) - 1]['added']);
    if (!$startTs || !$endTs) {
        return null;
    }
    $spanHours = ($endTs - $startTs) / 3600;
    if ($spanHours < 6) {
        return null;
    }

    // Якщо напруга вже на "float" рівні, це нормальна поведінка — ріст майже відсутній.
    if ($currentVoltage >= ping3_nocharge_float_gate($typeBattery)) {
        return null;
    }

    $startV = ping3_to_float($chargeRows[0]['volt'], $currentVoltage);
    $lastV = ping3_to_float($chargeRows[count($chargeRows) - 1]['volt'], $currentVoltage);
    $endV = max($lastV, $currentVoltage);
    $gain = $endV - $startV;

    $dailyRows = $db->SimpleWhile("
        SELECT DATE(added) AS d, MAX(volt) - MIN(volt) AS gain
        FROM mon_voltage
        WHERE deviceid = {$deviceId}
          AND mon_types = 'ping3'
          AND energy = 1
          AND added >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(added)
        HAVING gain > 0.20
        ORDER BY d DESC
        LIMIT 14
    ");
    if (!is_array($dailyRows) || count($dailyRows) < 5) {
        return null;
    }
    $baselineGain = 0.0;
    foreach ($dailyRows as $r) {
        $baselineGain += ping3_to_float($r['gain'], 0.0);
    }
    $baselineGain = $baselineGain / count($dailyRows);
    // Якщо історичний приріст дуже слабкий — нема надійної бази для "аномалії".
    if ($baselineGain < ping3_min_daily_charge_gain($typeBattery)) {
        return null;
    }

    $expectedWindowGain = $baselineGain * ($spanHours / 24);
    $minExpectedWindow = max(0.10 * max(1, ($typeBattery / 12)), $expectedWindowGain * 0.30);
    if ($expectedWindowGain < $minExpectedWindow) {
        return null;
    }

    $criticalGain = max(0.05 * max(1, ($typeBattery / 12)), $expectedWindowGain * 0.20);
    if ($gain <= $criticalGain) {
        return [
            'code' => 'NOCHARGE',
            'hours_cooldown' => 24,
            'message' =>
                'icon_battery_2 [b]' . $device['name'] . '[/b] ' .
                'АКБ не набирає заряд: ' . ping3_signed_voltage($gain, 2) . 'V за ' . round($spanHours, 1) .
                ' год (очікувано ~+' . round($expectedWindowGain, 2) . 'V). Перевірте зарядку АКБ'
        ];
    }
    return null;
}
function ping3_analyze_slow_drop($db, array $device) {
    $deviceId = (int)$device['id'];
    $typeBattery = (int)$device['typebattery'];
    if ($deviceId <= 0) {
        return null;
    }
    $rows = $db->SimpleWhile("
        SELECT DATE_FORMAT(added, '%Y-%m-%d %H:00:00') AS h, AVG(volt) AS volt
        FROM mon_voltage
        WHERE deviceid = {$deviceId}
          AND mon_types = 'ping3'
          AND energy = 2
          AND added >= DATE_SUB(NOW(), INTERVAL 72 HOUR)
        GROUP BY h
        ORDER BY h ASC
    ");
    if (!is_array($rows) || count($rows) < 12) {
        return null;
    }
    $firstTs = strtotime($rows[0]['h']);
    $lastTs = strtotime($rows[count($rows) - 1]['h']);
    if (!$firstTs || !$lastTs) {
        return null;
    }
    $spanHours = ($lastTs - $firstTs) / 3600;
    if ($spanHours < 48) {
        return null;
    }
    $window = max(3, min(6, (int)floor(count($rows) / 3)));
    $startAvg = 0.0;
    $endAvg = 0.0;
    for ($i = 0; $i < $window; $i++) {
        $startAvg += ping3_to_float($rows[$i]['volt'], 0.0);
        $endAvg += ping3_to_float($rows[count($rows) - $window + $i]['volt'], 0.0);
    }
    $startAvg /= $window;
    $endAvg /= $window;
    $drop = $startAvg - $endAvg;
    $threshold = ping3_slow_drop_threshold($typeBattery);
    if ($drop < $threshold) {
        return null;
    }
    $epsilon = 0.05 * max(1, ((float)$typeBattery / 12));
    $increaseCount = 0;
    for ($i = 1; $i < count($rows); $i++) {
        $prev = ping3_to_float($rows[$i - 1]['volt'], 0.0);
        $curr = ping3_to_float($rows[$i]['volt'], 0.0);
        if (($curr - $prev) > $epsilon) {
            $increaseCount++;
        }
    }
    if ($increaseCount > floor((count($rows) - 1) * 0.35)) {
        return null;
    }
    return [
        'code' => 'SLOWDROP',
        'hours_cooldown' => 24,
        'message' =>
            'icon_battery_2 [b]' . $device['name'] . '[/b] ' .
            'Плавна просадка АКБ: -' . round($drop, 2) . 'V за ' . round($spanHours, 0) .
            ' год у режимі батареї. Перевірте навантаження/стан АКБ. '
    ];
}
$timeout = 1000000;
$retries = 5;
$clock_ping3 = date('Y-m-d H:i:s');
$currentTimestamp  = time();
$sql_save = [];
$send_telegram = [];
$critic_voltage = [];
$battery_used = false;
if (isset($confPMon['BATTERY']) && !empty($confPMon['BATTERY']) && $confPMon['BATTERY'] == 1) {
    $battery_used = true;
}
if (isset($confPMon['PING3']) && $confPMon['PING3'] == 1) {
    $sql_ping3 = $db->SimpleWhile("SELECT * FROM mon_ping3 WHERE monitor = 'yes' AND typedevice = '1'");
    if (!empty($sql_ping3)) {
        foreach ($sql_ping3 as $ping3_device) {
            $timeDifference = $currentTimestamp - strtotime($ping3_device['energytime']);
            if (empty($ping3_device['id']) || $timeDifference <= 3) {
                continue;
            }
            $data_value = [];
            $data_value['poller'] = $clock_ping3;
            if ($ping3_device['typebattery'] == 12) {
                $voltage = get_volt12_ping3($ping3_device['netip'], $ping3_device['snmpro'], $ping3_device['channel']);
			} elseif ($ping3_device['typebattery'] == 24) {
                $voltage = get_volt24_ping3($ping3_device['netip'], $ping3_device['snmpro'], $ping3_device['channel']);
            } elseif ($ping3_device['typebattery'] == 48 || $ping3_device['typebattery'] == 72) {
                $voltage = get_volt48_ping3($ping3_device['netip'], $ping3_device['snmpro'], $ping3_device['channel']);
            } else {
                $voltage = (float)$ping3_device['volt'];
            }
            $voltage = ping3_to_float($voltage, (float)$ping3_device['volt']);
            if ($voltage <= 0 && ping3_to_float($ping3_device['volt'], 0.0) > 0) {
                $voltage = ping3_to_float($ping3_device['volt'], 0.0);
            }
            $volt220   = get_volt220_ping3($ping3_device['netip'], $ping3_device['snmpro']);
            $getvolt220 = (!empty($volt220) && $volt220 == 1) ? 1 : 2; // 1=є 220, 2=нема 220
            if($ping3_device['id']==1){
				#$getvolt220 = 2;
			}
            $days_ago = date('Y-m-d H:i:s', strtotime('-4 days'));
            $max_voltage_data = $db->Simple("SELECT MAX(volt) as maxvolt FROM mon_voltage WHERE deviceid = '{$ping3_device['id']}' AND added >= '{$days_ago}' AND energy = 1");
            $float_voltage = (!empty($max_voltage_data['maxvolt']) ? (float)$max_voltage_data['maxvolt'] : ping3_default_full_voltage($ping3_device['typebattery']));
            $previous_voltage = ping3_to_float($ping3_device['volt'], $voltage);
			/*
			if ($getvolt220 == 2) {
				$type = (int)$ping3_device['typebattery'];
				foreach ($DISCHARGE_LEVELS[$type] as $threshold) {
					if ($previous_voltage > $threshold	&& $voltage <= $threshold) {
						print_R("Розряд АКБ: напруга впала нижче {$threshold}V");
						break;
					}
				}
			}
			*/
            if ($getvolt220 == 2 && $ping3_device['energy'] == 1) {
                $data_value['energy'] = 2;
                $data_value['energytime'] = $clock_ping3;
                $send_telegram[$ping3_device['id']]['telegram'] =
                    '[icon-power] '.$lang['power_down'].' [Ping3] [b]' . $ping3_device['name'] . '[/b] ' . $voltage . 'V ' . $clock_ping3;
                if ($battery_used) {
                    $battery_row = $db->Simple("SELECT * FROM battery_used WHERE connectd = 'ping3' AND deviceid = '{$ping3_device['id']}' LIMIT 1");
                    if (!empty($battery_row['batteryid'])) {
                        $active_charge = $db->Simple("SELECT * FROM battery_sessions WHERE deviceid = {$ping3_device['id']} AND connectd = 'ping3' AND type = 'charge' AND status = 'in_progress' ORDER BY time_start DESC LIMIT 1");
                        if ($active_charge) {
                            $duration = $currentTimestamp - strtotime($active_charge['time_start']);
                            $db->SQLupdate('battery_sessions',[ 'voltage_end' => $voltage, 'time_end' => $clock_ping3, 'duration' => $duration, 'status' => 'finished'], ['id' => $active_charge['id']]);
                        }
                        $active_discharge = $db->Simple("SELECT id FROM battery_sessions WHERE deviceid = {$ping3_device['id']} AND connectd = 'ping3' AND type = 'discharge' AND status = 'in_progress' ORDER BY time_start DESC LIMIT 1");
                        if (!$active_discharge) {
                            $db->SQLinsert('battery_sessions', [ 'deviceid' => $ping3_device['id'], 'connectd' => 'ping3', 'type' => 'discharge', 'voltage_start' => $voltage, 'time_start' => $clock_ping3, 'status' => 'in_progress']);
                        }
                    }
                }
            } elseif ($getvolt220 == 1 && $ping3_device['energy'] == 2) {
                $data_value['energy'] = 1;
                $data_value['energytime']= $clock_ping3;
                $send_telegram[$ping3_device['id']]['telegram'] =
                    'icon_battery_2 '.$lang['power_up'].' [Ping3] [b]' . $ping3_device['name'] . '[/b] ' . $voltage . 'V ' . $clock_ping3;
                if ($battery_used) {
                    $battery_row = $db->Simple("SELECT * FROM battery_used WHERE connectd = 'ping3' AND deviceid = '{$ping3_device['id']}' LIMIT 1");
                    if (!empty($battery_row['batteryid'])) {
                        $active_discharge = $db->Simple("SELECT * FROM battery_sessions WHERE deviceid = {$ping3_device['id']} AND connectd = 'ping3' AND type = 'discharge' AND status = 'in_progress' ORDER BY time_start DESC LIMIT 1");
                        if ($active_discharge) {
                            $duration = $currentTimestamp - strtotime($active_discharge['time_start']);
                            $db->SQLupdate('battery_sessions',['voltage_end' => $voltage,'time_end' => $clock_ping3,'duration' => $duration,'status' => 'finished'], ['id' => $active_discharge['id']]);
                        }
                        $active_charge = $db->Simple("SELECT id FROM battery_sessions WHERE deviceid = {$ping3_device['id']} AND connectd = 'ping3' AND type = 'charge' AND status = 'in_progress'  ORDER BY time_start DESC LIMIT 1");
                        if (!$active_charge) {
                            $db->SQLinsert('battery_sessions',['deviceid' => $ping3_device['id'],'connectd' => 'ping3','type' => 'charge','voltage_start' => $voltage,'time_start' => $clock_ping3,'status' => 'in_progress']);
                        }
                    }
                }
            } elseif ($getvolt220 == 2 && $ping3_device['energy'] == 2) {
                $data_value['energy'] = 2;
            } elseif ($getvolt220 == 1 && $ping3_device['energy'] == 1) {
                $data_value['energy'] = 1;
            }
			if(!$ping3_device['energy'] && $getvolt220 == 1){
				$data_value['energy'] = 1;
			}
            if ($getvolt220 == 1 && $voltage >= $float_voltage) {
                $finished_charge = $db->Simple(" SELECT * FROM battery_sessions WHERE deviceid = {$ping3_device['id']} AND status = 'in_progress' AND type = 'charge' ORDER BY time_start DESC LIMIT 1");
                if ($finished_charge) {
                    $duration = $currentTimestamp - strtotime($finished_charge['time_start']);
                    $db->SQLupdate('battery_sessions',['voltage_end' => $voltage,'time_end' => $clock_ping3, 'duration' => $duration,'status' => 'finished'], ['id' => $finished_charge['id']]);
                    if (!isset($send_telegram[$ping3_device['id']]['telegram'])) {
                        $send_telegram[$ping3_device['id']]['telegram'] = '';
                    }
                    $send_telegram[$ping3_device['id']]['telegram'] .=
                        'icon_battery_2 [b]'.$ping3_device['name'].'[/b] [b]Full charge[/b] ' . $voltage . 'V';
                }
            }
            #if (abs($voltage - $previous_voltage) >= 0.1) {
                $data_value['volt'] = $voltage;
                $effectiveEnergy = isset($data_value['energy']) ? (int)$data_value['energy'] : (int)$ping3_device['energy'];
                $sql_save[$ping3_device['id']] = [
                    'ping3id' => $ping3_device['id'],'volt' => $voltage,'energy' => isset($data_value['energy']) ? (int)$data_value['energy'] : (int)$ping3_device['energy']
                ];
            #}
            if (!isset($effectiveEnergy)) {
                $effectiveEnergy = isset($data_value['energy']) ? (int)$data_value['energy'] : (int)$ping3_device['energy'];
            }
            if ($effectiveEnergy === 1) {
                $noChargeAlert = ping3_analyze_no_charge($db, $ping3_device, $voltage);
                $noChargeAlert = ping3_apply_alert_marker($noChargeAlert, (int)$ping3_device['id']);
                if ($noChargeAlert && !ping3_has_recent_alert($db, $ping3_device['id'], $noChargeAlert['code'], $noChargeAlert['hours_cooldown'])) {
                    if (!isset($critic_voltage[$ping3_device['id']]['telegram'])) {
                        $critic_voltage[$ping3_device['id']]['telegram'] = '';
                    }
                    $critic_voltage[$ping3_device['id']]['telegram'] .=
                        ($critic_voltage[$ping3_device['id']]['telegram'] !== '' ? "\n" : '') . $noChargeAlert['message'];
                }
            } elseif ($effectiveEnergy === 2) {
                $slowDropAlert = ping3_analyze_slow_drop($db, $ping3_device);
                $slowDropAlert = ping3_apply_alert_marker($slowDropAlert, (int)$ping3_device['id']);
                if ($slowDropAlert && !ping3_has_recent_alert($db, $ping3_device['id'], $slowDropAlert['code'], $slowDropAlert['hours_cooldown'])) {
                    if (!isset($critic_voltage[$ping3_device['id']]['telegram'])) {
                        $critic_voltage[$ping3_device['id']]['telegram'] = '';
                    }
                    $critic_voltage[$ping3_device['id']]['telegram'] .=
                        ($critic_voltage[$ping3_device['id']]['telegram'] !== '' ? "\n" : '') . $slowDropAlert['message'];
                }
            }
            if (!empty($data_value)) {
                $db->SQLupdate('mon_ping3', $data_value, ['id' => $ping3_device['id']]);
            }
        }
    }
}
if (!empty($send_telegram)) {
    foreach ($send_telegram as $iping => $data) {
        if (!empty($data['telegram'])) {
            $db->SQLinsert('notification',['status' => 1,'type' => 6,'system' => 'pinger','message' => $data['telegram'],'added'=> $clock_ping3]);
        }
    }
}
if (!empty($critic_voltage)) {
    foreach ($critic_voltage as $iping => $data) {
        if (!empty($data['telegram'])) {
            $db->SQLinsert('notification',['status' => 1,'type' => 6,'system' => 'pinger','message' => $data['telegram'],'added'   => $clock_ping3]);
        }
    }
}
if (!empty($sql_save)) {
    foreach ($sql_save as $iping => $data_value) {
        $db->SQLinsert('mon_voltage',['deviceid' => $data_value['ping3id'], 'mon_types' => 'ping3', 'volt' => $data_value['volt'], 'energy' => $data_value['energy'], 'added' => $clock_ping3]);
    }
}
?>
