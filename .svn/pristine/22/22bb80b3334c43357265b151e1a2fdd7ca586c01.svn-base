<?php
define('AJAX', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -5));
define('ENGINE_DIR', ROOT_DIR . '/inc/');
require_once ENGINE_DIR . 'ajax.php';

header('Content-Type: application/json; charset=utf-8');

function topology_schema_check(PDO $pdo): void {
    try {
        $requiredTables = ['topology_nodes', 'topology_ports', 'topology_links'];
        foreach ($requiredTables as $t) {
            $q = "SHOW TABLES LIKE " . $pdo->quote($t);
            if (!$pdo->query($q)->fetchColumn()) {
                json_fail('Topology DB schema is not installed. Run install/update/topology.database', 500);
            }
        }
        // Required columns (we removed auto-migrations; fail fast with clear message).
        $requiredPortCols = ['node_id', 'port', 'label', 'description', 'snmp_ip', 'snmp_ro', 'snmp_oid', 'bind_switch_id', 'bind_llid', 'last_status', 'last_polled_at'];
        foreach ($requiredPortCols as $c) {
            $q = "SHOW COLUMNS FROM `topology_ports` LIKE " . $pdo->quote($c);
            if (!$pdo->query($q)->fetchColumn()) {
                json_fail('Topology DB schema is outdated (missing topology_ports.' . $c . '). Run install/update/topology.database', 500);
            }
        }
        $requiredNodeCols = ['uid', 'name', 'model', 'ports', 'ports_per_row', 'kind', 'icon_url', 'open_url', 'locked', 'x', 'y'];
        foreach ($requiredNodeCols as $c) {
            $q = "SHOW COLUMNS FROM `topology_nodes` LIKE " . $pdo->quote($c);
            if (!$pdo->query($q)->fetchColumn()) {
                json_fail('Topology DB schema is outdated (missing topology_nodes.' . $c . '). Run install/update/topology.database', 500);
            }
        }
        $requiredLinkCols = ['uid', 'from_node_id', 'from_port', 'to_node_id', 'to_port', 'length_m', 'color', 'description'];
        foreach ($requiredLinkCols as $c) {
            $q = "SHOW COLUMNS FROM `topology_links` LIKE " . $pdo->quote($c);
            if (!$pdo->query($q)->fetchColumn()) {
                json_fail('Topology DB schema is outdated (missing topology_links.' . $c . '). Run install/update/topology.database', 500);
            }
        }
    } catch (Throwable $e) {
        json_fail('Topology schema check failed: ' . $e->getMessage(), 500);
    }
}

function json_ok($data = []): void {
    echo json_encode(['success' => true] + (array)$data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_fail(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

topology_schema_check($pdo);

$act = isset($_REQUEST['act']) ? Clean::text($_REQUEST['act']) : 'load';
$act = preg_replace('/[^a-z_]/', '', strtolower($act));

switch ($act) {
    case 'switches': {
        // For binding topology ports to existing devices/ports.
        $stmt = $pdo->query("SELECT id, place, model, netip, snmpro FROM switch ORDER BY place ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        json_ok(['switches' => $rows]);
    }
    case 'switch_ports': {
        $id = isset($_GET['id']) ? Clean::int($_GET['id']) : (isset($_POST['id']) ? Clean::int($_POST['id']) : 0);
        if ($id <= 0) json_fail('Missing id');
        $stmt = $pdo->prepare("
            SELECT id, deviceid, llid, nameport, descrport, typeport
            FROM switch_port
            WHERE deviceid = :id
            ORDER BY llid ASC
        ");
        $stmt->execute([':id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        json_ok(['ports' => $rows]);
    }
    case 'load': {
        $nodes = $db->Multi('topology_nodes', '*', null, ['id' => 'ASC']) ?: [];

        $nodeIdToUid = [];
        foreach ($nodes as $n) {
            $nodeIdToUid[(int)$n['id']] = $n['uid'];
        }

        $linksRaw = $db->Multi('topology_links', '*', null, ['id' => 'ASC']) ?: [];
        $links = [];
        foreach ($linksRaw as $l) {
            $fromId = (int)$l['from_node_id'];
            $toId = (int)$l['to_node_id'];
            if (empty($nodeIdToUid[$fromId]) || empty($nodeIdToUid[$toId])) {
                continue;
            }
            $links[] = [
                'uid' => $l['uid'],
                'from' => ['sw' => $nodeIdToUid[$fromId], 'port' => (int)$l['from_port']],
                'to' => ['sw' => $nodeIdToUid[$toId], 'port' => (int)$l['to_port']],
                // DB layer can't reliably store NULL for ints; 0 means "not set".
                'length_m' => (isset($l['length_m']) && (int)$l['length_m'] > 0) ? (int)$l['length_m'] : null,
                'color' => (isset($l['color']) && (string)$l['color'] !== '') ? (string)$l['color'] : null,
                'description' => $l['description'] ?? null,
            ];
        }

        $portsRaw = $db->Multi('topology_ports', '*') ?: [];
        $ports = [];
        foreach ($portsRaw as $p) {
            $nodeId = (int)$p['node_id'];
            if (empty($nodeIdToUid[$nodeId])) {
                continue;
            }
            $key = $nodeIdToUid[$nodeId] . ':' . (int)$p['port'];
            $ports[$key] = [
                'label' => $p['label'] ?? null,
                'description' => $p['description'],
                'snmp_ip' => $p['snmp_ip'],
                'snmp_ro' => $p['snmp_ro'],
                'snmp_oid' => $p['snmp_oid'],
                // DB layer forces quoting and can't insert NULL reliably; we store 0 as "no binding".
                'bind_switch_id' => (isset($p['bind_switch_id']) && (int)$p['bind_switch_id'] > 0) ? (int)$p['bind_switch_id'] : null,
                'bind_llid' => (isset($p['bind_llid']) && (int)$p['bind_llid'] > 0) ? (int)$p['bind_llid'] : null,
                'last_status' => isset($p['last_status']) ? (int)$p['last_status'] : null,
                'last_polled_at' => $p['last_polled_at'],
            ];
        }

        $outNodes = [];
        foreach ($nodes as $n) {
            $outNodes[] = [
                'uid' => $n['uid'],
                'name' => $n['name'],
                'model' => $n['model'] ?? null,
                'x' => (int)$n['x'],
                'y' => (int)$n['y'],
                'ports' => (int)$n['ports'],
                'ports_per_row' => isset($n['ports_per_row']) ? (int)$n['ports_per_row'] : 16,
                'kind' => isset($n['kind']) ? (string)$n['kind'] : 'switch',
                'icon_url' => $n['icon_url'] ?? null,
                'open_url' => $n['open_url'] ?? null,
                'locked' => isset($n['locked']) ? (int)$n['locked'] : 0,
            ];
        }

        json_ok(['nodes' => $outNodes, 'links' => $links, 'ports' => $ports]);
    }

    case 'add_node': {
        $name = isset($_POST['name']) ? Clean::text($_POST['name']) : 'Switch';
        $model = isset($_POST['model']) ? Clean::text($_POST['model']) : null;
        $ports = isset($_POST['ports']) ? Clean::int($_POST['ports']) : 12;
        $ports_per_row = isset($_POST['ports_per_row']) ? Clean::int($_POST['ports_per_row']) : 16;
        $kind = isset($_POST['kind']) ? Clean::text($_POST['kind']) : 'switch';
        $icon_url = isset($_POST['icon_url']) ? Clean::text($_POST['icon_url']) : null;
        $open_url = isset($_POST['open_url']) ? Clean::text($_POST['open_url']) : null;
        $locked = isset($_POST['locked']) ? Clean::int($_POST['locked']) : 0;
        $x = isset($_POST['x']) ? Clean::int($_POST['x']) : 100;
        $y = isset($_POST['y']) ? Clean::int($_POST['y']) : 100;
        $kind = ($kind === 'element') ? 'element' : 'switch';
        if ($ports < 1) $ports = 1;
        if ($ports > 96) $ports = 96;
        if ($ports_per_row < 1) $ports_per_row = 1;
        if ($ports_per_row > 32) $ports_per_row = 32;
        $locked = ($locked ? 1 : 0);

        $uid = 'sw' . substr(md5(uniqid('', true)), 0, 10);
        $db->SQLinsert('topology_nodes', [
            'uid' => $uid,
            'name' => $name,
            'model' => ($model !== '' ? $model : null),
            'ports' => $ports,
            'ports_per_row' => $ports_per_row,
            'kind' => $kind,
            'icon_url' => ($icon_url !== '' ? $icon_url : null),
            'open_url' => ($open_url !== '' ? $open_url : null),
            'locked' => $locked,
            'x' => $x,
            'y' => $y,
        ]);
        json_ok(['uid' => $uid]);
    }

    case 'update_node': {
        $uid = isset($_POST['uid']) ? Clean::text($_POST['uid']) : '';
        $name = isset($_POST['name']) ? Clean::text($_POST['name']) : '';
        $model = isset($_POST['model']) ? Clean::text($_POST['model']) : null;
        $ports = isset($_POST['ports']) ? Clean::int($_POST['ports']) : null;
        $ports_per_row = isset($_POST['ports_per_row']) ? Clean::int($_POST['ports_per_row']) : null;
        $kind = isset($_POST['kind']) ? Clean::text($_POST['kind']) : null;
        $icon_url = isset($_POST['icon_url']) ? Clean::text($_POST['icon_url']) : null;
        $open_url = isset($_POST['open_url']) ? Clean::text($_POST['open_url']) : null;
        $locked = isset($_POST['locked']) ? Clean::int($_POST['locked']) : null;
        if ($uid === '') json_fail('Missing uid');
        if ($name === '') json_fail('Missing name');
        $data = [
            'name' => $name,
            'model' => ($model !== '' ? $model : null),
        ];
        if ($ports !== null) {
            if ($ports < 1) $ports = 1;
            if ($ports > 96) $ports = 96;
            $data['ports'] = $ports;
        }
        if ($ports_per_row !== null) {
            if ($ports_per_row < 1) $ports_per_row = 1;
            if ($ports_per_row > 32) $ports_per_row = 32;
            $data['ports_per_row'] = $ports_per_row;
        }
        if ($locked !== null) {
            $data['locked'] = ($locked ? 1 : 0);
        }
        if ($kind !== null) {
            $data['kind'] = ($kind === 'element') ? 'element' : 'switch';
        }
        if ($icon_url !== null) {
            $data['icon_url'] = ($icon_url !== '' ? $icon_url : null);
        }
        if ($open_url !== null) {
            $data['open_url'] = ($open_url !== '' ? $open_url : null);
        }
        $db->SQLupdate('topology_nodes', $data, ['uid' => $uid]);
        json_ok();
    }

    case 'save_node_pos': {
        $uid = isset($_POST['uid']) ? Clean::text($_POST['uid']) : '';
        $x = isset($_POST['x']) ? Clean::int($_POST['x']) : 0;
        $y = isset($_POST['y']) ? Clean::int($_POST['y']) : 0;
        if ($uid === '') json_fail('Missing uid');
        $node = $db->Fast('topology_nodes', 'locked', ['uid' => $uid]);
        if (!empty($node) && isset($node['locked']) && (int)$node['locked'] === 1) {
            json_ok(); // locked: ignore move
        }
        $db->SQLupdate('topology_nodes', ['x' => $x, 'y' => $y], ['uid' => $uid]);
        json_ok();
    }

    case 'delete_node': {
        $uid = isset($_POST['uid']) ? Clean::text($_POST['uid']) : '';
        if ($uid === '') json_fail('Missing uid');
        $db->SQLdelete('topology_nodes', ['uid' => $uid]);
        json_ok();
    }

    case 'upsert_port': {
        $sw = isset($_POST['sw']) ? Clean::text($_POST['sw']) : '';
        $port = isset($_POST['port']) ? Clean::int($_POST['port']) : 0;
        $label = isset($_POST['label']) ? Clean::text($_POST['label']) : null;
        $description = isset($_POST['description']) ? Clean::text($_POST['description']) : null;
        $snmp_ip = isset($_POST['snmp_ip']) ? Clean::text($_POST['snmp_ip']) : null;
        $snmp_ro = isset($_POST['snmp_ro']) ? Clean::text($_POST['snmp_ro']) : null;
        $snmp_oid = isset($_POST['snmp_oid']) ? Clean::text($_POST['snmp_oid']) : null;
        $bind_switch_id = isset($_POST['bind_switch_id']) ? Clean::int($_POST['bind_switch_id']) : 0;
        $bind_llid = isset($_POST['bind_llid']) ? Clean::int($_POST['bind_llid']) : 0;
        if ($sw === '' || $port < 1) json_fail('Missing sw/port');

        $node = $db->Fast('topology_nodes', '*', ['uid' => $sw]);
        if (empty($node['id'])) json_fail('Unknown switch', 404);

        $nodeId = (int)$node['id'];
        $existing = $db->Fast('topology_ports', '*', ['node_id' => $nodeId, 'port' => $port]);

        // NOTE: DB::prepareData() always quotes values and turns null into empty string.
        // For integer columns we must persist "no value" as 0 (not NULL / not empty string),
        // otherwise MariaDB strict mode errors with "Incorrect integer value: ''".
        $data = [
            'label' => ($label !== '' ? $label : null),
            'description' => $description ?: null,
            'snmp_ip' => ($snmp_ip !== '' ? $snmp_ip : null),
            'snmp_ro' => ($snmp_ro !== '' ? $snmp_ro : null),
            'snmp_oid' => ($snmp_oid !== '' ? $snmp_oid : null),
            'bind_switch_id' => ($bind_switch_id > 0 ? $bind_switch_id : 0),
            'bind_llid' => ($bind_switch_id > 0 && $bind_llid > 0 ? $bind_llid : 0),
        ];

        if (!empty($existing['id'])) {
            $db->SQLupdate('topology_ports', $data, ['id' => (int)$existing['id']]);
        } else {
            $db->SQLinsert('topology_ports', ['node_id' => $nodeId, 'port' => $port] + $data);
        }
        json_ok();
    }

    case 'add_link': {
        $from_sw = isset($_POST['from_sw']) ? Clean::text($_POST['from_sw']) : '';
        $from_port = isset($_POST['from_port']) ? Clean::int($_POST['from_port']) : 0;
        $to_sw = isset($_POST['to_sw']) ? Clean::text($_POST['to_sw']) : '';
        $to_port = isset($_POST['to_port']) ? Clean::int($_POST['to_port']) : 0;
        if ($from_sw === '' || $to_sw === '' || $from_port < 1 || $to_port < 1) {
            json_fail('Missing link params');
        }

        $fromNode = $db->Fast('topology_nodes', '*', ['uid' => $from_sw]);
        $toNode = $db->Fast('topology_nodes', '*', ['uid' => $to_sw]);
        if (empty($fromNode['id']) || empty($toNode['id'])) json_fail('Unknown switch', 404);
        $fromId = (int)$fromNode['id'];
        $toId = (int)$toNode['id'];
        if ($fromId === $toId && $from_port === $to_port) json_fail('Same port');

        $busy1 = $db->Fast('topology_links', '*', ['from_node_id' => $fromId, 'from_port' => $from_port]);
        $busy2 = $db->Fast('topology_links', '*', ['to_node_id' => $fromId, 'to_port' => $from_port]);
        $busy3 = $db->Fast('topology_links', '*', ['from_node_id' => $toId, 'from_port' => $to_port]);
        $busy4 = $db->Fast('topology_links', '*', ['to_node_id' => $toId, 'to_port' => $to_port]);
        if ($busy1 || $busy2 || $busy3 || $busy4) {
            json_fail('Port already connected');
        }

        $uid = 'l' . substr(md5(uniqid('', true)), 0, 10);
        $db->SQLinsert('topology_links', [
            'uid' => $uid,
            'from_node_id' => $fromId,
            'from_port' => $from_port,
            'to_node_id' => $toId,
            'to_port' => $to_port,
        ]);
        json_ok(['uid' => $uid]);
    }

    case 'delete_link': {
        $uid = isset($_POST['uid']) ? Clean::text($_POST['uid']) : '';
        if ($uid === '') json_fail('Missing uid');
        $db->SQLdelete('topology_links', ['uid' => $uid]);
        json_ok();
    }

    case 'update_link': {
        $uid = isset($_POST['uid']) ? Clean::text($_POST['uid']) : '';
        $length_m = isset($_POST['length_m']) ? Clean::int($_POST['length_m']) : null;
        $color = isset($_POST['color']) ? Clean::text($_POST['color']) : null;
        $description = isset($_POST['description']) ? Clean::text($_POST['description']) : null;
        if ($uid === '') json_fail('Missing uid');
        $data = [
            'description' => ($description !== '' ? $description : null),
        ];
        if ($length_m !== null) {
            $data['length_m'] = ($length_m > 0) ? $length_m : 0;
        }
        if ($color !== null) {
            $color = trim((string)$color);
            if ($color === '') {
                $data['color'] = null;
            } elseif (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                $data['color'] = $color;
            } else {
                json_fail('Invalid color. Use #RRGGBB', 400);
            }
        }
        $db->SQLupdate('topology_links', $data, ['uid' => $uid]);
        json_ok();
    }

    case 'status': {
        $portsRaw = $db->Multi('topology_ports', 'node_id,port,last_status,last_polled_at') ?: [];
        $nodes = $db->Multi('topology_nodes', 'id,uid') ?: [];
        $nodeIdToUid = [];
        foreach ($nodes as $n) $nodeIdToUid[(int)$n['id']] = $n['uid'];
        $ports = [];
        foreach ($portsRaw as $p) {
            $nodeId = (int)$p['node_id'];
            if (empty($nodeIdToUid[$nodeId])) continue;
            $ports[$nodeIdToUid[$nodeId] . ':' . (int)$p['port']] = [
                'last_status' => isset($p['last_status']) ? (int)$p['last_status'] : null,
                'last_polled_at' => $p['last_polled_at'],
            ];
        }
        json_ok(['ports' => $ports]);
    }

    case 'events': {
        $lastId = isset($_POST['last_id']) ? Clean::int($_POST['last_id']) : 0;
        if ($lastId < 0) $lastId = 0;

        $events = [];
        try {
            if (isset($redis)) {
                $rows = $redis->lrange('topology:events', -200, -1);
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        if (!is_string($row) || $row === '') continue;
                        $ev = json_decode($row, true);
                        if (!is_array($ev)) continue;
                        $id = isset($ev['id']) ? (int)$ev['id'] : 0;
                        if ($id > $lastId) $events[] = $ev;
                    }
                }
            }
        } catch (Throwable $e) {
            // ignore redis errors
        }

        usort($events, static function ($a, $b) {
            return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
        });

        $newLast = $lastId;
        foreach ($events as $ev) {
            $id = isset($ev['id']) ? (int)$ev['id'] : 0;
            if ($id > $newLast) $newLast = $id;
        }

        json_ok(['events' => $events, 'last_id' => $newLast]);
    }

    default:
        json_fail('Unknown act', 404);
}
