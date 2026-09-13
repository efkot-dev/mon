<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

header('Content-Type: application/json; charset=utf-8');

$actor_id = isset($USER['id']) ? (int)$USER['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ctype = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($ctype, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status'=>'error','message'=>'Некоректний JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    $payload = $_POST;
}

$products = $payload['products'] ?? null;
if (!$products || !is_array($products)) {
    echo json_encode(['status'=>'error','message'=>'Порожній або некоректний список products'], JSON_UNESCAPED_UNICODE);
    exit;
}

$toNumber = static function($v): float {
    if (is_string($v)) {
        $v = str_replace(',', '.', trim($v));
    }
    return (float)$v;
};

try {
    $pdo->beginTransaction();

    $stmtCheckUser = $pdo->prepare("SELECT id FROM users WHERE id = :uid LIMIT 1");
    $stmtCheckStock = $pdo->prepare("SELECT quantity FROM sklad_tovar WHERE id = :pid FOR UPDATE");
    $stmtDecStock   = $pdo->prepare("UPDATE sklad_tovar SET quantity = quantity - :q WHERE id = :pid");
    $stmtInactivate = $pdo->prepare("UPDATE sklad_tovar SET status = 'inactive' WHERE id = :pid");
    $stmtActivate   = $pdo->prepare("UPDATE sklad_tovar SET status = 'active' WHERE id = :pid");

    $stmtFindAcct = $pdo->prepare("
        SELECT id, quantity
          FROM sklad_accounting
         WHERE user_id = :uid
           AND product_id = :pid
           AND status = 'enable'
           AND action_type = 'assigned'
         LIMIT 1
         FOR UPDATE
    ");
    $stmtAddQty = $pdo->prepare("UPDATE sklad_accounting SET quantity = quantity + :q WHERE id = :id");
    $stmtInsert = $pdo->prepare("
        INSERT INTO sklad_accounting (status, product_id, user_id, quantity, action_type)
        VALUES ('enable', :pid, :uid, :q, 'assigned')
    ");

    $stmtLogTransfer = $pdo->prepare("
        INSERT INTO sklad_log (action, product_id, from_user_id, to_user_id, akt_id, quantity, actor_id)
        VALUES ('transfer', :pid, :from_uid, :to_uid, :akt_id, :q, :actor_id)
    ");

    foreach ($products as $row) {
        $user_id = (int)($row['user_id'] ?? 0);
        $product_id = (int)($row['productid'] ?? 0);
        $quantity = $toNumber($row['quantity'] ?? 0);

        if ($product_id <= 0 || $user_id <= 0 || $quantity <= 0) {
            throw new RuntimeException("Некоректні дані переміщення (product_id={$product_id}, user_id={$user_id}, quantity={$quantity})");
        }

        $stmtCheckUser->execute([':uid' => $user_id]);
        if (!$stmtCheckUser->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException("Працівник #{$user_id} не знайдений");
        }

        $stmtCheckStock->execute([':pid' => $product_id]);
        $stock = $stmtCheckStock->fetch(PDO::FETCH_ASSOC);
        $have = $stock ? $toNumber($stock['quantity']) : 0;

        if ($have < $quantity) {
            throw new RuntimeException("Товар ID {$product_id}: недостатньо на складі (є {$have}, потрібно {$quantity})");
        }

        $stmtDecStock->execute([':pid' => $product_id, ':q' => $quantity]);

        if (($have - $quantity) <= 0) {
            $stmtInactivate->execute([':pid' => $product_id]);
        } else {
            $stmtActivate->execute([':pid' => $product_id]);
        }

        $stmtFindAcct->execute([':uid' => $user_id, ':pid' => $product_id]);
        $dst = $stmtFindAcct->fetch(PDO::FETCH_ASSOC);
        if ($dst) {
            $stmtAddQty->execute([':q' => $quantity, ':id' => (int)$dst['id']]);
        } else {
            $stmtInsert->execute([':pid' => $product_id, ':uid' => $user_id, ':q' => $quantity]);
        }

        $stmtLogTransfer->execute([
            ':pid' => $product_id,
            ':from_uid' => 0,
            ':to_uid' => $user_id,
            ':akt_id' => null,
            ':q' => $quantity,
            ':actor_id' => $actor_id
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Переміщення завершено успішно'], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
