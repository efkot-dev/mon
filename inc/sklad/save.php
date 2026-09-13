<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

header('Content-Type: application/json; charset=utf-8');

$ctype = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($ctype, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Некоректний JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    $payload = $_POST;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Невірний метод запиту'], JSON_UNESCAPED_UNICODE);
    exit;
}

$products_array = $payload['products'] ?? null;
if (empty($products_array) || !is_array($products_array)) {
    echo json_encode(['status' => 'error', 'message' => 'Немає даних для збереження'], JSON_UNESCAPED_UNICODE);
    exit;
}

$uadded = isset($USER['id']) ? (int)$USER['id'] : 0;

$funUnitName = static function ($type_id) {
    if ((int)$type_id === 1) return 'метраж';
    if ((int)$type_id === 2) return 'кількість';
    if ((int)$type_id === 3) return 'інвертарний номер';
    return 'кількість';
};

$toFloat = static function ($v): float {
    if (is_string($v)) {
        $v = str_replace(',', '.', trim($v));
    }
    return (float)$v;
};

$stmtCat = $pdo->prepare('SELECT id, types FROM sklad_category WHERE id = :category_id LIMIT 1');
$stmtFindBySn = $pdo->prepare("SELECT id FROM sklad_tovar WHERE inventory_number = :sn LIMIT 1 FOR UPDATE");
$stmtFindByMac = $pdo->prepare("SELECT id FROM sklad_tovar WHERE mac = :mac LIMIT 1 FOR UPDATE");
$stmtFindSame = $pdo->prepare("
    SELECT id
      FROM sklad_tovar
     WHERE name = :name
       AND category_id = :category_id
       AND sub_cat_id = :sub_cat_id
       AND unit = :unit
       AND oblik = :oblik
       AND COALESCE(mac, '') = COALESCE(:mac, '')
       AND COALESCE(inventory_number, '') = COALESCE(:inventory_number, '')
     LIMIT 1
     FOR UPDATE
");

$stmtUpdateQty = $pdo->prepare("UPDATE sklad_tovar SET quantity = quantity + :quantity, status = 'active' WHERE id = :id");
$stmtInsert = $pdo->prepare("
    INSERT INTO sklad_tovar
      (status, uadded, name, category_id, unit, quantity, sub_cat_id, description, price_pdv, price, inventory_number, date_added, oblik, mac)
    VALUES
      ('active', :uadded, :name, :category_id, :unit, :quantity, :sub_cat_id, :description, :price_pdv, :price, :inventory_number, NOW(), :oblik, :mac)
");
$stmtLogAdd = $pdo->prepare("
    INSERT INTO sklad_log (action, product_id, akt_id, from_user_id, to_user_id, quantity, actor_id)
    VALUES ('add', :pid, 0, 0, 0, :q, :actor_id)
");

$inserted = 0;
$updated = 0;

try {
    $pdo->beginTransaction();

    foreach ($products_array as $row) {
        $name = isset($row['name']) ? Clean::text($row['name']) : '';
        $catid = (int)($row['category_id'] ?? 0);
        $subid = (int)($row['sub_id'] ?? 0);
        $qty = $toFloat($row['quantity'] ?? 0);
        $price = $toFloat($row['price'] ?? 0);

        $snRaw = $row['sn'] ?? ($row['inventory_number'] ?? '');
        $sn = trim((string)$snRaw) !== '' ? Clean::text((string)$snRaw) : null;

        $macRaw = $row['mac'] ?? '';
        $mac = trim((string)$macRaw) !== '' ? Clean::text((string)$macRaw) : null;

        if ($name === '' || $catid <= 0 || $subid <= 0 || $qty <= 0) {
            throw new RuntimeException('Некоректні дані товару');
        }

        $stmtCat->execute([':category_id' => $catid]);
        $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
        if (!$catRow) {
            throw new RuntimeException("Категорія #{$catid} не знайдена");
        }

        $oblik = (int)$catRow['types'];
        $unit = $funUnitName($oblik);

        $sameId = null;

        if ($sn !== null) {
            $stmtFindBySn->execute([':sn' => $sn]);
            $bySn = $stmtFindBySn->fetch(PDO::FETCH_ASSOC);
            if ($bySn) {
                $sameId = (int)$bySn['id'];
            }
        }

        if ($mac !== null) {
            $stmtFindByMac->execute([':mac' => $mac]);
            $byMac = $stmtFindByMac->fetch(PDO::FETCH_ASSOC);
            if ($byMac) {
                $byMacId = (int)$byMac['id'];
                if ($sameId !== null && $sameId !== $byMacId) {
                    throw new RuntimeException('MAC або S/N вже привʼязані до інших позицій');
                }
                $sameId = $byMacId;
            }
        }

        if ($sameId === null) {
            $stmtFindSame->execute([
                ':name' => $name,
                ':category_id' => $catid,
                ':sub_cat_id' => $subid,
                ':unit' => $unit,
                ':oblik' => $oblik,
                ':mac' => $mac,
                ':inventory_number' => $sn,
            ]);
            $same = $stmtFindSame->fetch(PDO::FETCH_ASSOC);
            if ($same) {
                $sameId = (int)$same['id'];
            }
        }

        if ($sameId !== null) {
            $stmtUpdateQty->execute([
                ':quantity' => $qty,
                ':id' => $sameId,
            ]);
            $productId = $sameId;
            $updated++;
        } else {
            $stmtInsert->execute([
                ':uadded' => $uadded,
                ':name' => $name,
                ':category_id' => $catid,
                ':unit' => $unit,
                ':quantity' => $qty,
                ':sub_cat_id' => $subid,
                ':description' => null,
                ':price_pdv' => 0,
                ':price' => $price,
                ':inventory_number' => $sn,
                ':oblik' => $oblik,
                ':mac' => $mac,
            ]);
            $productId = (int)$pdo->lastInsertId();
            $inserted++;
        }

        $stmtLogAdd->execute([
            ':pid' => $productId,
            ':q' => $qty,
            ':actor_id' => $uadded,
        ]);
    }

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Всі товари успішно збережено',
        'inserted' => $inserted,
        'updated' => $updated,
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'Внутрішня помилка: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
