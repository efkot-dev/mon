<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

if (!function_exists('sklad_h')) {
    function sklad_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$filter_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$query = "
    SELECT sl.id, sl.sale_date, sl.quantity_sold,
           st.name AS product_name,
           u.username
      FROM sales_log sl
      JOIN sklad_tovar st ON sl.product_id = st.id
      JOIN users u ON sl.user_id = u.id
";

$params = [];
if ($filter_user_id > 0) {
    $query .= ' WHERE sl.user_id = :user_id';
    $params[':user_id'] = $filter_user_id;
}
$query .= ' ORDER BY sl.sale_date DESC LIMIT 500';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$content .= '<h2 align="center">Лог списань товарів</h2>';
$content .= '<table border="1" width="80%" align="center">
                <tr>
                    <th>№</th>
                    <th>Товар</th>
                    <th>Кількість</th>
                    <th>Тип операції</th>
                    <th>Дата</th>
                    <th>Користувач</th>
                </tr>';

if (!empty($sales_logs)) {
    foreach ($sales_logs as $index => $log) {
        $content .= '<tr>
                        <td align="center">' . ($index + 1) . '</td>
                        <td>' . sklad_h($log['product_name']) . '</td>
                        <td align="center">' . sklad_h($log['quantity_sold']) . '</td>
                        <td align="center">Списання</td>
                        <td align="center">' . sklad_h($log['sale_date']) . '</td>
                        <td align="center">' . sklad_h($log['username']) . '</td>
                    </tr>';
    }
} else {
    $content .= '<tr><td colspan="6" align="center">' . $lang['empty'] . '</td></tr>';
}

$content .= '</table>';
?>
