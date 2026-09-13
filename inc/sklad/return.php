<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

$src = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;
$id = isset($src['id']) ? (int)$src['id'] : 0;
$userid = isset($src['userid']) ? (int)$src['userid'] : 0;
$quantity = isset($src['qty']) ? (float)str_replace(',', '.', (string)$src['qty']) : 0;
$actor_id = isset($USER['id']) ? (int)$USER['id'] : 0;

if ($id <= 0 || $userid <= 0 || $quantity <= 0) {
    $go->go('/?do=tmc&act=backpack&id=' . $userid);
    exit;
}

try {
    $pdo->beginTransaction();

    $sqlUser = $pdo->prepare("SELECT id FROM users WHERE id = :user_id LIMIT 1");
    $sqlUser->execute([':user_id' => $userid]);
    if (!$sqlUser->fetch(PDO::FETCH_ASSOC)) {
        throw new RuntimeException('Працівника не знайдено');
    }

    $sqlAcc = $pdo->prepare("SELECT * FROM sklad_accounting WHERE id = :id AND user_id = :user_id AND status = 'enable' LIMIT 1 FOR UPDATE");
    $sqlAcc->execute([':id' => $id, ':user_id' => $userid]);
    $acc = $sqlAcc->fetch(PDO::FETCH_ASSOC);
    if (!$acc) {
        throw new RuntimeException('Позиція обліку не знайдена');
    }

    $accQty = (float)$acc['quantity'];
    if ($quantity > $accQty) {
        throw new RuntimeException('Кількість повернення перевищує залишок у працівника');
    }

    if ($quantity < $accQty) {
        $updateAcc = $pdo->prepare("UPDATE sklad_accounting SET quantity = quantity - :qty WHERE id = :id");
        $updateAcc->execute([':qty' => $quantity, ':id' => $id]);
    } else {
        $deleteAcc = $pdo->prepare("DELETE FROM sklad_accounting WHERE id = :id");
        $deleteAcc->execute([':id' => $id]);
    }

    $sqlTovar = $pdo->prepare("SELECT id, quantity, status FROM sklad_tovar WHERE id = :id LIMIT 1 FOR UPDATE");
    $sqlTovar->execute([':id' => (int)$acc['product_id']]);
    $tovar = $sqlTovar->fetch(PDO::FETCH_ASSOC);
    if (!$tovar) {
        throw new RuntimeException('Товар не знайдено');
    }

    $updateTovar = $pdo->prepare("UPDATE sklad_tovar SET quantity = quantity + :quantity, status = 'active' WHERE id = :id");
    $updateTovar->execute([
        ':quantity' => $quantity,
        ':id' => (int)$tovar['id'],
    ]);

    $stmtLogReturn = $pdo->prepare("
        INSERT INTO sklad_log (action, product_id, from_user_id, to_user_id, akt_id, quantity, actor_id)
        VALUES ('return', :pid, :from_uid, :to_uid, :akt_id, :q, :actor_id)
    ");
    $stmtLogReturn->execute([
        ':pid' => (int)$acc['product_id'],
        ':from_uid' => $userid,
        ':to_uid' => 0,
        ':akt_id' => null,
        ':q' => $quantity,
        ':actor_id' => $actor_id
    ]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

$go->go('/?do=tmc&act=backpack&id=' . $userid);
exit;
?>
