<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

if (!function_exists('sklad_h')) {
    function sklad_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$auto = false;
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql_users = $pdo->prepare("SELECT id, username, name FROM users WHERE id = :user_id LIMIT 1");
$sql_users->execute([':user_id' => $user_id]);
$get_users = $sql_users->fetch(PDO::FETCH_ASSOC);

if (!$get_users) {
    $go->go('/?do=tmc&act=listusr');
    exit;
}

$metatags = ['title' => 'Плюшкін ' . $get_users['username'], 'description' => 'Плюшкін', 'page' => 'usr'];
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc&act=listusr"><i class="fi fi-rr-angle-left"></i>Список працівників</a>';
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Плюшкін ' . sklad_h($get_users['username']) . ' ' . (!empty($get_users['name']) ? sklad_h($get_users['name']) : '') . '</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';

$query = "
    SELECT
        sa.id,
        sa.product_id,
        sa.user_id,
        sa.quantity,
        sa.status,
        sa.action_date,
        st.name AS product_name,
        st.category_id,
        st.sub_cat_id,
        ssc.name AS sub_category_name,
        ssc.code AS sub_code,
        st.oblik,
        st.unit,
        st.price,
        st.price_pdv,
        st.inventory_number AS sn,
        st.mac,
        st.status AS product_status
    FROM sklad_accounting sa
    JOIN sklad_tovar st ON sa.product_id = st.id
    LEFT JOIN sklad_sub_category ssc ON st.sub_cat_id = ssc.id
    WHERE sa.user_id = :user_id
      AND sa.status = 'enable'
    ORDER BY st.name ASC
";

$sql_list = $pdo->prepare($query);
$sql_list->execute([':user_id' => $user_id]);
$products = $sql_list->fetchAll(PDO::FETCH_ASSOC);

$print_list = '';
if ($products) {
    $print_list .= "<table style='width:100%;' id='products-column' class='resp-tab pmon_skald'><thead><tr>
    <th>Категорія</th>
    <th>Обладнання</th>
    <th>Ціна</th>
    <th>Кількість</th>
    <th>S/N</th>
    <th>Повернення</th>
    <th>Керування</th>
    </tr></thead><tbody>";

    foreach ($products as $product) {
        $id = (int)$product['id'];
        $count_tovar = (float)$product['quantity'];
        $code = '';
        $img_list = '';

        if (!empty($product['sn'])) {
            $code = (string)$product['sn'];
        } elseif (!empty($product['sub_code'])) {
            $code = (string)$product['sub_code'];
        }

        if ($code !== '') {
            $img_list = "<br><img src='/?do=tmc&act=barcode&text=" . rawurlencode($code) . "' />";
        }

        $print_list .= "<tr>";
        $print_list .= "<td class='sklad_name_tovar'><a href='/' class='sub'><img src='/style/img/fm.svg'>" . sklad_h($product['sub_category_name']) . "</a></td>";
        $print_list .= "<td class='sklad_name_tovar'><div class='sort_2_list'><h2>" . cut_text($product['product_name'], 200) . "</h2><span class='on_'>" . sklad_h($product['action_date']) . "</span></div></td>";
        $print_list .= "<td class='sklad_price'>" . number_format((float)$product['price'], 2) . "</td>";
        $print_list .= "<td><b>{$count_tovar}</b></td>";

        $print_list .= "<td>";
        if (!empty($product['mac'])) {
            $safeMac = sklad_h($product['mac']);
            $print_list .= "<span class='copy' onclick='enableCopyOnClick(\"{$safeMac}\")'>{$safeMac}<img src='../style/img/copy.png'></span>";
        }
        if (!empty($product['sn'])) {
            $safeSn = sklad_h($product['sn']);
            $print_list .= "<span class='copy' onclick='enableCopyOnClick(\"{$safeSn}\")'>{$safeSn}<img src='../style/img/copy.png'></span>";
        }
        $print_list .= $img_list;
        $print_list .= "</td>";

        $print_list .= "<td><center><input type='number' class='input1' id='qty_{$id}' min='0.01' step='0.01' max='{$count_tovar}' style='width:80px;text-align:center;' value='{$count_tovar}'></center></td>";
        $print_list .= "<td><a href='#' onclick='returnToStock({$id}, {$user_id}); return false;' class='usr_sklad_go'>Повернути на склад</a></td>";
        $print_list .= "</tr>";
    }

    $print_list .= "</tbody></table>";
} else {
    $print_list = "<div class='empty_tovar'><img src='../style/img/empty-filter.svg'><span>{$lang['empty']}</span></div>";
}

$content .= '<div class="container">'.$print_list.'</div>
<script>
function returnToStock(id, userId) {
    const qtyInput = document.getElementById("qty_" + id);
    const qty = qtyInput ? parseFloat(qtyInput.value || "0") : 0;
    if (!qty || qty <= 0) {
        if (typeof showNotification === "function") {
            showNotification("������ �������� ������� ����������");
        } else {
            alert("������ �������� ������� ����������");
        }
        return false;
    }

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/?do=tmc&act=return";

    const i1 = document.createElement("input");
    i1.type = "hidden";
    i1.name = "id";
    i1.value = String(id);

    const i2 = document.createElement("input");
    i2.type = "hidden";
    i2.name = "userid";
    i2.value = String(userId);

    const i3 = document.createElement("input");
    i3.type = "hidden";
    i3.name = "qty";
    i3.value = String(qty);

    form.appendChild(i1);
    form.appendChild(i2);
    form.appendChild(i3);
    document.body.appendChild(form);
    form.submit();
    return false;
}
</script>';
?>
