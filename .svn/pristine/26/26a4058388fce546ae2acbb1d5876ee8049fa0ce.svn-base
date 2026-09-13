<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

if (!function_exists('sklad_h')) {
    function sklad_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($_GET['category_id'], $_GET['cat_id'])) {
    die;
}

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

if ($category_id <= 0 || $cat_id <= 0) {
    echo "<div class='empty_tovar'><img src='../style/img/empty-filter.svg'><span>Вибачте, немає товарів.</span></div>";
    die;
}

$limit = 20;
$offset = ($page - 1) * $limit;

$cat = $pdo->prepare("SELECT id, number FROM sklad_category WHERE id = :category_id LIMIT 1");
$cat->execute([':category_id' => $category_id]);
$result_category = $cat->fetch(PDO::FETCH_ASSOC);

$sub_cat = $pdo->prepare("SELECT id, code FROM sklad_sub_category WHERE id = :cat_id LIMIT 1");
$sub_cat->execute([':cat_id' => $cat_id]);
$result_sub_category = $sub_cat->fetch(PDO::FETCH_ASSOC);

if (!$result_category || !$result_sub_category) {
    echo "<div class='empty_tovar'><img src='../style/img/empty-filter.svg'><span>Вибачте, немає товарів.</span></div>";
    die;
}

$stmt = $pdo->prepare("
    SELECT st.*, u.username, u.name
      FROM sklad_tovar st
 LEFT JOIN users u ON u.id = st.uadded
     WHERE st.sub_cat_id = :cat_id
       AND st.category_id = :category_id
       AND st.status = 'active'
  ORDER BY st.hide ASC, st.id DESC
     LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
$stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($products) {
    echo "<table id='products-column' class='resp-tab pmon_skald'><thead><tr>";
    echo "<th>Прихід</th>";
    if (($result_category['number'] ?? '') === 'no') {
        echo "<th>ШтрихКод</th>";
    }
    echo "<th>Обладнання</th>";
    echo "<th>Ціна</th><th>Кількість</th><th>Од.виміру</th>";
    if (($result_category['number'] ?? '') === 'yes') {
        echo "<th>Серійний номер</th>";
    }
    echo "<th>Додав</th><th></th>";
    echo "</tr></thead><tbody>";

    foreach ($products as $product) {
        $rowStyle = (($product['hide'] ?? '') === 'yes') ? " style='background: #eee'" : '';
        $barcodeText = !empty($result_sub_category['code']) ? sklad_h($result_sub_category['code']) : '';
        $mac = trim((string)($product['mac'] ?? ''));
        $inventory = trim((string)($product['inventory_number'] ?? ''));
        $uname = 'Auto';
        if (!empty($product['uadded'])) {
            $uname = !empty($product['name']) ? $product['name'] : (!empty($product['username']) ? $product['username'] : 'Auto');
        }

        echo "<tr{$rowStyle}>";
        echo '<td>' . sklad_h($product['date_added']) . '</td>';

        if (($result_category['number'] ?? '') === 'no') {
            echo '<td>';
            if ($barcodeText !== '') {
                echo "<img src='/?do=tmc&act=barcode&text={$barcodeText}' />";
            } else {
                echo 'n/a';
            }
            echo '</td>';
        }

        echo "<td class='sklad_name_tovar'><h2>" . cut_text($product['name'], 70) . "</h2>";
        if ($mac !== '') {
            $safeMac = sklad_h($mac);
            echo "<br><span class='copy' onclick='enableCopyOnClick(\"{$safeMac}\")'>{$safeMac}<img src='../style/img/copy.png'></span>";
        }
        echo '</td>';

        echo "<td class='sklad_price'>" . number_format((float)$product['price'], 2) . "</td>";
        echo '<td><b>' . (float)$product['quantity'] . '</b></td>';
        echo '<td>' . sklad_h($product['unit']) . '</td>';

        if (($result_category['number'] ?? '') === 'yes') {
            $class_sn = ($inventory !== '') ? " class='sklad_sn_tovar'" : '';
            echo "<td{$class_sn}>";
            if ($inventory !== '') {
                $safeInv = sklad_h($inventory);
                echo "<span class='copy' onclick='enableCopyOnClick(\"{$safeInv}\")'>{$safeInv}<img src='../style/img/copy.png'></span>";
                echo "<br><img src='/?do=tmc&act=barcode&text={$safeInv}' />";
            } else {
                echo 'n/a';
            }
            echo '</td>';
        }

        echo '<td>' . sklad_h($uname) . '</td>';
        echo "<td><span class='edit_tovar' data-id='" . (int)$product['id'] . "' class='edit_tovar'><i class=\"fi fi-rr-settings\"></i></span></td>";
        echo '</tr>';

        $cols = (($result_category['number'] ?? '') === 'yes') ? 8 : 7;
        echo "<tr id='tr_tovar_" . (int)$product['id'] . "' style='display:none;'><td colspan='{$cols}'>";
        echo "<div id='tovar_result_" . (int)$product['id'] . "'></div><div id='tovar_" . (int)$product['id'] . "'></div>";
        echo '</td></tr>';
    }

    echo '</tbody></table>';
} else {
    echo "<div class='empty_tovar'><img src='../style/img/empty-filter.svg'><span>Вибачте, немає товарів.</span></div>";
}

$stmtCount = $pdo->prepare("
    SELECT COUNT(*) AS total
      FROM sklad_tovar
     WHERE sub_cat_id = :cat_id
       AND category_id = :category_id
       AND status = 'active'
");
$stmtCount->execute([':cat_id' => $cat_id, ':category_id' => $category_id]);
$total = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = (int)ceil($total / $limit);

if ($total_pages > 1) {
    echo "<div class='pagination'>";
    for ($i = 1; $i <= $total_pages; $i++) {
        echo "<a href='#' class='page-link' data-page='" . (int)$i . "' data-cat='" . (int)$cat_id . "'>" . (int)$i . "</a> ";
    }
    echo '</div>';
}

die;
?>
