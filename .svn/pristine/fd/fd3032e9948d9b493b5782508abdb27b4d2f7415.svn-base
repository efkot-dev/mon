<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

$user_id = isset($USER['id']) ? (int)$USER['id'] : 0;
if ($user_id <= 0) {
    echo 'Некоректний користувач';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tovar'], $_POST['qty']) || !is_array($_POST['tovar']) || !is_array($_POST['qty'])) {
    echo 'Некоректний запит!';
    exit;
}

$toFloat = static function ($v): float {
    if (is_string($v)) {
        $v = str_replace(',', '.', trim($v));
    }
    return (float)$v;
};

try {
    $pdo->beginTransaction();

    $qSelectAcc = $pdo->prepare("SELECT id, quantity FROM sklad_accounting WHERE user_id = :user_id AND product_id = :product_id AND status = 'enable' LIMIT 1 FOR UPDATE");
    $qUpdateAcc = $pdo->prepare("UPDATE sklad_accounting SET quantity = quantity - :quantity WHERE id = :id");
    $qDisableAcc = $pdo->prepare("UPDATE sklad_accounting SET status = 'disable' WHERE id = :id");

    $qSelectTovar = $pdo->prepare("SELECT id, quantity FROM sklad_tovar WHERE id = :product_id LIMIT 1 FOR UPDATE");
    $qUpdateTovar = $pdo->prepare("UPDATE sklad_tovar SET quantity = quantity - :quantity WHERE id = :product_id");
    $qDisableTovar = $pdo->prepare("UPDATE sklad_tovar SET status = 'inactive' WHERE id = :product_id");

    $qInsertLog = $pdo->prepare("INSERT INTO sales_log (product_id, user_id, quantity_sold) VALUES (:product_id, :user_id, :quantity)");

    foreach ($_POST['tovar'] as $index => $product_id_raw) {
        $product_id = (int)$product_id_raw;
        $quantity = isset($_POST['qty'][$index]) ? $toFloat($_POST['qty'][$index]) : 0;

        if ($product_id <= 0 || $quantity <= 0) {
            continue;
        }

        $qSelectAcc->execute([':user_id' => $user_id, ':product_id' => $product_id]);
        $acc = $qSelectAcc->fetch(PDO::FETCH_ASSOC);
        if (!$acc) {
            throw new RuntimeException('Позиція не знайдена в обліку працівника');
        }

        $accQty = (float)$acc['quantity'];
        if ($accQty < $quantity) {
            throw new RuntimeException('Недостатньо товару для списання');
        }

        $qUpdateAcc->execute([':quantity' => $quantity, ':id' => (int)$acc['id']]);
        if (($accQty - $quantity) <= 0) {
            $qDisableAcc->execute([':id' => (int)$acc['id']]);
        }

        $qSelectTovar->execute([':product_id' => $product_id]);
        $tovar = $qSelectTovar->fetch(PDO::FETCH_ASSOC);
        if (!$tovar) {
            throw new RuntimeException('Товар не знайдено на складі');
        }

        $tovarQty = (float)$tovar['quantity'];
        if ($tovarQty < $quantity) {
            throw new RuntimeException('Некоректний стан обліку складу');
        }

        $qUpdateTovar->execute([':quantity' => $quantity, ':product_id' => $product_id]);
        if (($tovarQty - $quantity) <= 0) {
            $qDisableTovar->execute([':product_id' => $product_id]);
        }

        $qInsertLog->execute([
            ':product_id' => $product_id,
            ':user_id' => $user_id,
            ':quantity' => $quantity,
        ]);
    }

    $pdo->commit();
    $go->go('/?do=tmc&act=panel');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo 'Помилка списання: ' . $e->getMessage();
    exit;
}
?>
