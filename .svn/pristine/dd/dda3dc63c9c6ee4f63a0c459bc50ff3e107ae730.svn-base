<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$date_today = date('Y-m-d');
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$catid = isset($_GET['catid']) ? (int)$_GET['catid'] : null;
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$select = $catid ?? $category_id;
$sql_conditions = [];
$sql_conditions_ = [];
$params_[':date_today'] = $date_today;
$params = [
    ':date_today' => $date_today,
    ':limit' => $limit,
    ':offset' => $offset
];
if (!is_null($select) && $select > 0) {
    $sql_conditions[] = "category_id = :category_id";
    $sql_conditions_[] = "category_id = :category_id";
    $params[':category_id'] = $select;
    $params_[':category_id'] = $select;
}
$sql_conditions[] = "status = 'active'";
$sql_conditions_[] = "status = 'active'";
$sql_conditions[] = "DATE(date_added) = :date_today";
$sql_conditions_[] = "DATE(date_added) = :date_today";
$sql_products = "SELECT * FROM sklad_tovar WHERE " . implode(" AND ", $sql_conditions) . " LIMIT :limit OFFSET :offset";
$stmt_products = $pdo->prepare($sql_products);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
if (!empty($products)) {
    $stmt_count = $pdo->prepare("SELECT COUNT(id) AS total FROM sklad_tovar WHERE " . implode(" AND ", $sql_conditions_));
    $stmt_count->execute($params_);
    $total_rows = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $total_pages = ceil($total_rows['total'] / $limit);
    echo "<table id='products-column' class='resp-tab pmon_skald'><thead><tr><th>Матеріал / Обладнання</th><th>Кількість</th></tr></thead><tbody>";
    foreach ($products as $product) {
        echo "<tr>
            <td class='sklad_name_tovar new'><h2>{$product['name']}</h2></td>
            <td class='new'>{$product['quantity']}</td>
        </tr>";
    }
    echo "</table>";
    if ($total_pages > 1) {
        echo '<div class="pagination">';
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='#' class='page-new' data-page='{$i}'>{$i}</a> ";
        }
        echo '</div>';
    }
} else {
    echo "<div class='empty_tovar'>
		<img src='../style/img/empty_tovar.svg'>
		<span>Вибачте, Немає нових товарів.</span></div>";
}

exit;
?>
