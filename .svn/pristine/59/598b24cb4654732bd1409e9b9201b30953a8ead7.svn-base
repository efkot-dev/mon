<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) {
    die('Hacking attempt!');
}

$catid = isset($_GET['catid']) ? (int)$_GET['catid'] : 0;
$stmt_cat = $pdo->prepare("SELECT * FROM sklad_category WHERE id = :id LIMIT 1");
$stmt_cat->bindParam(':id', $catid, PDO::PARAM_INT);
$stmt_cat->execute();
$categoria = $stmt_cat->fetch(PDO::FETCH_ASSOC);

if ($categoria) {
    echo "<li><img src='/style/img/cat-list.svg'>
    <a href='/?do=tmc&act=get' style='color:#222;'>Всі категорії</a>
    </li>";
    echo "<li><img src='/style/img/fm.svg'>
    <a href='/?do=tmc&act=list&catid={$catid}' style='color:#222;'>".htmlspecialchars($categoria['name'])."</a>
    </li>";

    $stmt = $pdo->prepare("SELECT * FROM sklad_sub_category WHERE cat_id = :cat_id ORDER BY name ASC");
    $stmt->execute([':cat_id' => (int)$categoria['id']]);
    $sub_cat = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sub_cat as $category) {
        $stmt_count = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM sklad_tovar WHERE sub_cat_id = :sub_cat_id AND status = 'active'");
        $stmt_count->execute([':sub_cat_id' => (int)$category['id']]);
        $total_rows = (float)$stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

        echo "<li class='category-link' data-category-id='" . (int)$category['id'] . "'>
            <img src='/style/img/sub-cat.svg'>
            <a href='#' ".($total_rows > 0 ? "" : "style='color:grey;'").">" . htmlspecialchars($category['name']) .
            ($total_rows > 0 ? " <b>({$total_rows})</b>" : "")." </a>
        </li>";
    }
}

die;
?>
