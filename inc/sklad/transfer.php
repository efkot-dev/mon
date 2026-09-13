<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) { die('Hacking attempt!'); }
if (isset($_GET['this']) && $_GET['this'] === 'get') {
    header('Content-Type: application/json; charset=utf-8');
    $category_id = isset($_GET['catid']) ? (int)$_GET['catid'] : 0;
    $sub_category_id = isset($_GET['sub_catid']) ? (int)$_GET['sub_catid'] : 0;
    $q = trim($_GET['q'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = (int)($_GET['per_page'] ?? 25);
    if ($per_page < 1)  { $per_page = 25; }
    if ($per_page > 100){ $per_page = 100; }
    if ($category_id <= 0 || $sub_category_id <= 0) {
        echo json_encode(['items'=>[], 'page'=>$page, 'per_page'=>$per_page, 'total'=>0], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $where  = "status = 'active' AND category_id = :category_id AND sub_cat_id = :sub_cat_id";
    $params = [':category_id' => $category_id, ':sub_cat_id' => $sub_category_id];
    if ($q !== '') {
        $where .= " AND (inventory_number LIKE :qq OR name LIKE :qq)";
        $params[':qq'] = "%$q%";
    }
    $sql_count = "SELECT COUNT(*) FROM sklad_tovar WHERE $where";
    $stmt = $pdo->prepare($sql_count);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $total = (int)$stmt->fetchColumn();
    $offset = ($page - 1) * $per_page;
    $sql = "SELECT id, name, inventory_number, quantity
            FROM sklad_tovar
            WHERE $where
            ORDER BY name ASC, inventory_number ASC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['items'=>$items, 'page'=>$page, 'per_page'=>$per_page, 'total'=>$total], JSON_UNESCAPED_UNICODE);
    exit;
}
$auto = false;
$id = (int)($_GET['id'] ?? 0);
$metatags = ['title'=> 'Переміщення','description'=>'Переміщення','page'=>'transfer'];
$speedbar  = '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</a>';
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Переміщення</span>';
$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
$stmt_users = $pdo->prepare("SELECT id, username FROM users ORDER BY username ASC");
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
$date = date('d/m/Y');
$usr_list = '<select name="user_id" class="user_id" required><option value=""></option>';
foreach ($users as $user) {
    $usr_list .= '<option value="'.$user['id'].'" '.($id && $id==$user['id'] ? 'selected': '').'>'.htmlspecialchars($user['username']).'</option>';
}
$usr_list .= '</select>';
$stmt_sub = $pdo->prepare("SELECT * FROM sklad_sub_category ORDER BY name ASC");
$stmt_sub->execute();
$sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
$subCatData = [];
foreach ($sub_categories as $sub) {
    $stmt_count = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM sklad_tovar WHERE sub_cat_id = :sub_cat_id AND status = 'active'");
    $stmt_count->execute([':sub_cat_id' => $sub['id']]);
    $total_rows = (float)$stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    if ($total_rows > 0) {
        $subCatData[$sub['cat_id']][] = [
            'id' => (int)$sub['id'],'name' => $sub['name'],'total' => $total_rows,
        ];
    }
}
$subCatJson = json_encode($subCatData, JSON_UNESCAPED_UNICODE);
$stmt = $pdo->prepare("SELECT * FROM sklad_category ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->fetchAll();
$categoryOptions = "<option value=''></option>";
foreach ($categories as $category) {
    $stmt_count_cat = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM sklad_tovar WHERE category_id = :category_id AND status = 'active'");
    $stmt_count_cat->execute([':category_id' => $category['id']]);
    $total_rows_cat = (float)$stmt_count_cat->fetch(PDO::FETCH_ASSOC)['total'];
    if ($total_rows_cat > 0) {
        $categoryOptions .= "<option value='".(int)$category['id']."' data-inv='".htmlspecialchars($category['types'])."'>".htmlspecialchars($category['name'])." (".$total_rows_cat.")</option>";
    }
}
$content .= <<<HTML
<form id='productsForm' class="flex_niz">
  <div class='transfer-header card block_white m10b' style='width: 400px;'>
    <div class='wrap gap-12 align-center'>
      <div class='name_act_lit'>Акт від {$date}</div>
      <div class='flex align-center gap-6'><label>Працівник</label>{$usr_list}</div>
    </div>
  </div>
  <div class='transfer-grid'>
    <div class='catalog-col card block_white'>
      <div class='flex gap-10 wrap m10b'>
        <div class='w100'>
          <label class='m5b block'>Категорія</label>
          <select id='filterCategory' class='w100'>{$categoryOptions}</select>
        </div>
        <div class='w100'>
          <label class='m5b block'>Підкатегорія</label>
          <select id='filterSubcategory' class='w100'><option value=''></option></select>
        </div>
        <div class='w100'>
          <label class='m5b block'>Пошук (назва або інв. №)</label>
          <input id='filterSearch' class='w100' type='text' placeholder='Почніть вводити...'>
        </div>
      </div>
      <div class='flex space-between align-center m10b'>
        <div class='h3 m0'>Каталог</div>
        <div id='catalogMeta' class='color_gray'></div>
      </div>
      <table id='catalogTable' class='table'>
        <thead>
          <tr>
            <th>Назва</th>
            <th width='22%'>Інв. №</th>
            <th width='14%'>Залишок</th>
            <th width='12%'>Дія</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <div class='center m10t'><button type='button' id='loadMore' class='btn_outline' style='display:none'>Показати ще</button></div>
    </div>
    <div class='selected-col card block_white'>
      <div class='flex space-between align-center m10b'>
        <div class='h3 m0'>Вибрані позиції</div>
        <div id='selectedCount' class='color_gray'>0 позицій</div>
      </div>
      <table id='selectedTable' class='table'>
        <thead>
          <tr>
            <th>Назва</th>
            <th width='22%'>Інв. №</th>
            <th width='14%'>Доступно</th>
            <th width='16%'>Кількість</th>
            <th width='10%'>Дія</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
    <div style='margin-left:5px;'>
      <div class='ml-auto'><button type='button' id='saveProducts' class='btn_color1'>Перемістити на працівника</button></div>
  </div>
</form>
HTML;
$content .= "<script>const BASE_URL='".addslashes($url_skald)."'; const subCategories=".$subCatJson.";</script>";
$content .= <<<'JS'
<script>
let cart = new Map();
let page = 1, perPage = 25, total = 0;
let currentCat = '', currentSub = '', currentQ = '';
function formatMeta(){
  const shown = Math.min(page*perPage, total);
  document.getElementById('catalogMeta').textContent = total ? `Показано ${shown} з ${total}` : '';
}
function refreshSubcats(){
  const catId = document.getElementById('filterCategory').value;
  const subSel = document.getElementById('filterSubcategory');
  subSel.innerHTML = "<option value=''></option>";
  if (catId && subCategories[catId]) {
    subCategories[catId].forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id;
      opt.textContent = `${s.name} (${s.total})`;
      subSel.appendChild(opt);
    });
  }
}
function productRowHTML(item){
  const safeName = (item.name||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  const inv = item.inventory_number ? item.inventory_number : '';
  const stock = parseFloat(item.quantity)||0;
  const inCart = cart.has(String(item.id));
  return `
    <tr data-id="${item.id}">
      <td>${safeName}</td>
      <td>${inv}</td>
      <td><span class='badge'>${stock.toFixed(2)}</span></td>
      <td>
        <button type='button' class='btn_outline addBtn' ${inCart?'disabled':''}>${inCart?'Додано':'Додати'}</button>
      </td>
    </tr>`;
}
function renderCatalog(items, append=false){
  const tbody = document.querySelector('#catalogTable tbody');
  if(!append) tbody.innerHTML = '';
  (items||[]).forEach(it=> tbody.insertAdjacentHTML('beforeend', productRowHTML(it)) );
  formatMeta();
  document.getElementById('loadMore').style.display = (page*perPage < total) ? '' : 'none';
}
function loadProducts(reset=false){
  if(reset){ page = 1; total = 0; const tb = document.querySelector('#catalogTable tbody'); if(tb) tb.innerHTML=''; }
  if(!currentCat || !currentSub){
    document.getElementById('catalogMeta').textContent = 'Оберіть категорію та підкатегорію';
    document.getElementById('loadMore').style.display = 'none';
    return;
  }
  const params = new URLSearchParams({ catid: currentCat, sub_catid: currentSub, page: String(page), per_page: String(perPage) });
  if(currentQ) params.append('q', currentQ);
  fetch(`${BASE_URL}&act=transfer&this=get&${params.toString()}`, { credentials: 'same-origin' })
    .then(r=>r.json())
    .then(data=>{ total = data.total||0; renderCatalog(data.items||[], !reset && page>1); })
    .catch(()=>{ document.getElementById('catalogMeta').textContent='Помилка завантаження списку'; });
}
function selectedRowHTML(rec){
  const safeName = (rec.name||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  const leftNow = Math.max(0, rec.stock - rec.qty);
  return `
    <tr data-id='${rec.id}'>
      <td>${safeName}<input type='hidden' name='productid[]' value='${rec.id}'></td>
      <td>${rec.inv}</td>
      <td><span class='badge'>${leftNow.toFixed(2)}</span></td>
      <td><input type='number' class='qty-input cart-qty' name='quantity[]' min='1' max='${rec.stock}' value='${rec.qty}'></td>
      <td><span class='action-link removeRow'>Прибрати</span></td>
    </tr>`;
}
function renderCart(){
  const tbody = document.querySelector('#selectedTable tbody');
  if(tbody){
    tbody.innerHTML = '';
    for(const rec of cart.values()){ tbody.insertAdjacentHTML('beforeend', selectedRowHTML(rec)); }
  }
  const c = cart.size;
  document.getElementById('selectedCount').textContent = `${c} позицій`;
  document.getElementById('saveProducts').style.display = c>0 ? '' : 'none';
}
$(document).on('change', '#filterCategory', function(){
  currentCat = this.value;
  refreshSubcats();
  currentSub = document.getElementById('filterSubcategory').value = '';
  loadProducts(true);
});
$(document).on('change', '#filterSubcategory', function(){
  currentSub = this.value;
  loadProducts(true);
});
let searchTimer = null;
$(document).on('input', '#filterSearch', function(){
  currentQ = this.value.trim();
  clearTimeout(searchTimer);
  searchTimer = setTimeout(()=> loadProducts(true), 300);
});
$(document).on('click', '#loadMore', function(){
  if(page*perPage < total){ page++; loadProducts(false); }
});
$(document).on('click', '#catalogTable .addBtn', function(){
  const tr = this.closest('tr');
  if(!tr) return;
  const id = tr.getAttribute('data-id');
  if(cart.has(id)) return;
  const name = tr.children[0]?.textContent.trim() || '';
  const inv  = tr.children[1]?.textContent.trim() || '';
  const stock= parseFloat(tr.querySelector('.badge')?.textContent || '0') || 0;
  cart.set(id, { id, name, inv, stock, qty: 1 });
  this.textContent = 'Додано';
  this.disabled = true;
  tr.remove();
  renderCart();
  // lock user after first add
  $('.user_id').css({"pointer-events":"none","opacity":"0.6"});
});
$(document).on('input', '#selectedTable .cart-qty', function(){
  const tr = this.closest('tr');
  if(!tr) return;
  const id = tr.getAttribute('data-id');
  const rec = cart.get(id);
  if(!rec) return;
  let v = parseFloat(this.value || '1');
  if(isNaN(v) || v < 1) v = 1;
  if(v > rec.stock) v = rec.stock;
  rec.qty = v;
  const badge = tr.querySelector('.badge');
  if(badge){ badge.textContent = String(Math.max(0, rec.stock - rec.qty).toFixed(2)); }
});
$(document).on('click', '#selectedTable .removeRow', function(){
  const tr = this.closest('tr');
  if(!tr) return;
  const id = tr.getAttribute('data-id');
  const rec = cart.get(id);
  cart.delete(id);
  tr.remove();
  const btn = document.querySelector(`#catalogTable tbody tr[data-id="${id}"] .addBtn`);
  if(btn){ btn.disabled = false; btn.textContent = 'Додати'; }
  const c = cart.size;
  document.getElementById('selectedCount').textContent = `${c} позицій`;
  document.getElementById('saveProducts').style.display = c>0 ? '' : 'none';
});
$(document).on('click', '#saveProducts', function(){
  const userId = document.querySelector('.user_id')?.value || '';
  if(!userId){ return (typeof showNotification==='function' ? showNotification('Оберіть працівника') : alert('Оберіть працівника')); }
  const products = [];
  for(const rec of cart.values()){
    products.push({ user_id: userId, productid: rec.id, quantity: rec.qty });
  }
  fetch(`${BASE_URL}&act=savetransfer`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ products })
  })
  .then(r=>r.json())
  .then(resp=>{
    if(resp && resp.status === 'success'){
      if(typeof showNotification==='function'){ showNotification(resp.message, '/?do=tmc&act=transfer'); }
      else { alert('Успіх: '+(resp.message||'')); location.href='/?do=tmc&act=transfer'; }
    } else {
      if(typeof showNotification==='function'){ showNotification('Помилка: ' + (resp?.message || 'невідома')); }
      else { alert('Помилка: ' + (resp?.message || 'невідома')); }
    }
  })
  .catch(()=>{
    if(typeof showNotification==='function'){ showNotification('Помилка: не вдалося підключитися до сервера.'); }
    else { alert('Помилка: не вдалося підключитися до сервера.'); }
  });
});
$(function(){
  currentCat = document.getElementById('filterCategory')?.value || '';
  refreshSubcats();
  currentSub = document.getElementById('filterSubcategory')?.value || '';
  loadProducts(true);
});
</script>
JS;
?>