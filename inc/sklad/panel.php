<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

if (!function_exists('sklad_h')) {
    function sklad_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$user_id = isset($USER['id']) ? (int)$USER['id'] : 0;
if ($user_id <= 0) {
    $content .= '<div class="empty_tovar"><span>Не вдалося визначити користувача.</span></div>';
    return;
}

$query = "
    SELECT st.id as tovar_id, st.name, sa.quantity as us_quantity, st.unit, st.inventory_number
      FROM sklad_tovar st
      JOIN sklad_accounting sa ON st.id = sa.product_id
     WHERE sa.user_id = :user_id
       AND sa.status = 'enable'
  ORDER BY st.name ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$content .= '<form action="/?do=tmc&act=spisannya" method="POST">';
$content .= '<table border="1" width="80%" align="center">
                <tr>
                    <th>№</th>
                    <th>Матеріал / Обладнання</th>
                    <th>Залишок</th>
                    <th>К-ть</th>
                    <th>Одиниця виміру</th>
                    <th>S/N</th>
                </tr>';

foreach ($products as $index => $product) {
    $maxQty = (float)$product['us_quantity'];
    $content .= '<tr>
                    <td align="center">' . ($index + 1) . '</td>
                    <td>' . sklad_h($product['name']) . '</td>
                    <td align="center">' . $maxQty . '</td>
                    <td align="center">
                        <input type="hidden" name="tovar[]" value="' . (int)$product['tovar_id'] . '">
                        <input type="number" name="qty[]" min="0" step="0.01" max="' . $maxQty . '" id="spisannya_' . (int)$product['tovar_id'] . '">
                    </td>
                    <td align="center">' . sklad_h($product['unit']) . '</td>
                    <td align="center">' . (!empty($product['inventory_number']) ? sklad_h($product['inventory_number']) : '—') . '</td>
                </tr>';
}

$content .= '</table>';
$content .= '<p align="center"><input type="submit" value="Записати"></p>';
$content .= '</form>';
?>
