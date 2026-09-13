<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}
$auto = true;
$metatags = ['title' => 'Список категорій', 'description' => 'Список категорій', 'page' => 'category'];
$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Список категорій</span>';
$speedbar_block = '<div id="onu-speedbar">' . $speedbar . '</div>';
$stmt = $pdo->query("SELECT * FROM sklad_category");
$listworker = $stmt->fetchAll(PDO::FETCH_ASSOC);
$category_ids = array_column($listworker, 'id');
$sub_categories = [];
if (!empty($category_ids)) {
    $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
    $stmt_sub = $pdo->prepare("SELECT * FROM sklad_sub_category WHERE cat_id IN ($placeholders)");
    $stmt_sub->execute($category_ids);
    while ($row = $stmt_sub->fetch(PDO::FETCH_ASSOC)) {
        $sub_categories[$row['cat_id']][] = $row;
    }
}
$content = '<table class="resp-tab"><thead><tr>
<th width="45px">Icon</th>
<th width="60%">' . htmlspecialchars($lang['name']) . '</th>
<th>Всього</th>
<th>Облік</th>
<th>Підкатегорії</th>
<th>Керування</th>
</tr></thead><tbody>';
if (!empty($listworker)) {
    foreach ($listworker as $cat) {
        $stmt_count = $pdo->prepare("SELECT SUM(quantity) AS total_sum FROM sklad_tovar WHERE category_id = ? AND status = 'active'");
        $stmt_count->execute([$cat['id']]);
        $tovar_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
        $content .= '<tr>
            <td>' . (!empty($cat['img']) ? '<img style="height: 38px;" src="../file/photo/' . htmlspecialchars($cat['img']) . '">' : '') . '</td>
            <td class="td_url mobile txt_left">
                <a href="/?do=tmc&act=editcategory&id=' . $cat['id'] . '">' . $cat['name'] . '</a>
                <br>';
			if (!empty($sub_categories[$cat['id']])) {
				$content .= '<span class="sub_pid_cat">';
				foreach ($sub_categories[$cat['id']] as $pid_cat) {
					$content .= '<a href="/?do=tmc&act=editsubcategory&id=' . $pid_cat['id'] . '">' . $pid_cat['name'] . '</a> ';
				}
				$content .= '</span>';
			}	
            $content .= '</td>
            <td class="mobile"><span class="on_">' . ($tovar_count['total_sum'] ?? 0) . '</span></td>
            <td>' . ($cat['number'] === 'yes' ? 'Обліковується по номеру' : 'Кількість або метраж') . '</td>
            <td><a class="add_sub_cat" href="/?do=tmc&act=addsubcat&id=' . (int)$cat['id'] . '">Додати підкатегорію</a></td>
            <td><a href="/?do=tmc&act=delcategory&id=' . (int)$cat['id'] . '"><img src="../style/img/delet.png"></a></td>
        </tr>';
        
    }
} else {
    $content .= '<tr><td colspan="5">' . htmlspecialchars($lang['empty']) . '</td></tr>';
}
$content .= '</tbody></table>
<div class="pole"><a href="?do=tmc&act=addcategory"><span class="reger_btn">Додати категорію</span></a></div>';
?>
