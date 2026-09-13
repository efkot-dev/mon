<?php
if (!defined('PONMONITOR') && !defined('SKLAD')) { die('Hacking attempt!'); }
if (isset($_GET['this']) && $_GET['this'] === 'names') {
    header('Content-Type: application/json; charset=utf-8');
    $q = trim($_GET['q'] ?? '');
    $catid = (int)($_GET['catid'] ?? 0);
    $subid = (int)($_GET['subid'] ?? 0);
    $limit = min(25, max(5, (int)($_GET['limit'] ?? 12)));
    $where  = "1=1";
    $params = [];
    if ($q !== '') {
        $where .= " AND name LIKE :q";
        $params[':q'] = "%{$q}%";
    }
    if ($catid > 0) {
        $where .= " AND category_id = :catid";
        $params[':catid'] = $catid;
    }
    if ($subid > 0) {
        $where .= " AND sub_cat_id = :subid";
        $params[':subid'] = $subid;
    }
    $sql = "
        SELECT
            name,
            MIN(category_id) AS category_id,
            MIN(sub_cat_id)  AS sub_cat_id,
            COUNT(*)         AS cnt
        FROM sklad_tovar
        WHERE {$where}
        GROUP BY name
        ORDER BY cnt DESC, name ASC
        LIMIT :limit
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k=>$v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $items = [];
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $items[] = [
            'name' => (string)$r['name'],
            'category_id' => (int)$r['category_id'],
            'sub_cat_id' => (int)$r['sub_cat_id'],
            'count' => (int)$r['cnt'],
        ];
    }
    echo json_encode(['items'=>$items], JSON_UNESCAPED_UNICODE);
    exit;
}
$auto = false;
$metatags = ['title' => $lang['sklad_add_tovar'], 'description' => $lang['sklad_add_tovar'], 'page' => 'sklad'];
$speedbar  = '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .= '<a class="brmhref" href="/?do=tmc"><i class="fi fi-rr-angle-left"></i>'.$lang['sklad_main'].'</a>';
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['sklad_add_tovar'].'</span>';
$speedbar_block = '<div id="onu-speedbar">'.$speedbar.'</div>';
$stmt = $pdo->prepare("SELECT id, name, types FROM sklad_category ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt_sub = $pdo->prepare("SELECT id, name, cat_id FROM sklad_sub_category ORDER BY name ASC");
$stmt_sub->execute();
$sub_categories = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
$subCatData = [];
foreach ($sub_categories as $sub) {
    $subCatData[$sub['cat_id']][] = [
        'id'   => (int)$sub['id'],
        'name' => (string)$sub['name']
    ];
}
$subCatJson = json_encode($subCatData, JSON_UNESCAPED_UNICODE);
$categoryOptions = "<option value=''>".$lang['sklad_select_catogory']."</option>";
foreach ($categories as $category) {
    $categoryOptions .= "<option value='".(int)$category['id']."' data-inv='".htmlspecialchars($category['types'])."'>".htmlspecialchars($category['name'])."</option>";
}
$content = <<<HTML
<form id="productsForm" class="card block_white">
  <div class="flex space-between align-center m10b">
    <div class="flex gap-8">
      <button type="button" id="addRow" class="btn_outline">+ Додати рядок</button>
      <button type="button" id="addFiveRows" class="btn_outline">+5</button>
    </div>
  </div>
  <table id="productsTable" class="table">
    <thead>
      <tr>
        <th width="22%">{$lang['sklad_materian_obj']}</th>
        <th width="16%">{$lang['sklad_category']}</th>
        <th width="16%">{$lang['sklad_sub_category']}</th>
        <th width="10%">{$lang['sklad_count']}</th>
        <th width="10%">{$lang['sklad_price']}</th>
        <th width="12%">S/N</th>
        <th width="12%">MAC <span class="mac_sup">{$lang['sklad_in_base']}</span></th>
        <th width="8%">Дія</th>
      </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
      <tr>
        <td colspan="8" class="color_gray">
          Підказка: подвійний клік по «Кількість» — заповнить значення 1.00. Дублікати MAC/SN підсвічуються. Поле «Назва» має підказки з бази.
        </td>
      </tr>
    </tfoot>
  </table>

  <div class="flex space-between align-center m10t">
    <div id="formStatus" class="color_gray"></div>
    <button type="button" id="saveProducts" class="btn_color1" disabled>{$lang['sklad_add_oblick']}</button>
  </div>
</form>

<style>
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #eee;padding:8px 10px;vertical-align:middle}
.table tbody tr:hover{background:#fafafa}
input[type="text"], input[type="number"], select { width:100%; box-sizing:border-box }
.btn_outline{border:1px solid #ccc;background:#fff;padding:8px 12px;border-radius:6px;cursor:pointer}
.btn_color1{border:0;background:#2f7dff;color:#fff;padding:10px 16px;border-radius:6px;cursor:pointer}
.btn_color1[disabled]{opacity:.5; cursor:not-allowed}
.bad{outline:2px solid #ff6b6b; background:#fff4f4}
.dup-hint{font-size:12px;color:#b30000}
.small{font-size:12px}
.autocomplete{
  position:absolute; z-index:20; background:#fff; border:1px solid #ddd; border-radius:6px;
  box-shadow:0 6px 16px rgba(0,0,0,0.08); width:100%; max-height:240px; overflow:auto;
}
.autocomplete .item{ padding:8px 10px; cursor:pointer }
.autocomplete .item:hover, .autocomplete .item.active{ background:#f2f6ff }
.ac-wrap{ position:relative }
.ac-meta{ font-size:12px; color:#666; margin-left:6px }
</style>
HTML;

$BASE_URL = addslashes($url_skald);
$content .= "<script>
const BASE_URL='{$BASE_URL}'; 
const SUBCATS={$subCatJson}; 
const CATEGORY_OPTIONS=`".addslashes($categoryOptions)."`;
</script>";
$content .= <<<'JS'
<script>
(function(){
  const tbody   = document.querySelector('#productsTable tbody');
  const saveBtn = document.getElementById('saveProducts');
  const statusEl= document.getElementById('formStatus');
  const buildUrl = (query) => `${BASE_URL}${BASE_URL.includes('?') ? '&' : '?'}${query}`;
  function debounce(fn, ms=200){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }
  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

  function makeRow(){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><div class="ac-wrap"><input type="text" name="name[]" class="name" placeholder="Назва *" autocomplete="off" required></div></td>
      <td>
        <select name="category_id[]" class="cat" required>
          ${CATEGORY_OPTIONS}
        </select>
      </td>
      <td>
        <select name="sub_id[]" class="sub" required>
          <option value="">Обрати підкатегорію</option>
        </select>
      </td>
      <td><input type="number" name="quantity[]" class="qty" min="1" step="0.01" placeholder="1.00" required></td>
      <td><input type="number" name="price[]" class="price" min="0" step="0.01" placeholder="0.00"></td>
      <td><input type="text" name="sn[]"  class="sn small"  placeholder="необов'язково"></td>
      <td><input type="text" name="mac[]" class="mac small" placeholder="необов'язково"></td>
      <td><button type="button" class="rowDel btn_outline">✕</button><div class="dup-hint" style="display:none">Дублікат!</div></td>
    `;
    attachNameAutocomplete(tr.querySelector('.name'));
    return tr;
  }

  function fillSubcats(row){
    const catSel = row.querySelector('.cat');
    const subSel = row.querySelector('.sub');
    const catId  = catSel.value;
    subSel.innerHTML = `<option value="">Обрати підкатегорію</option>`;
    if (catId && SUBCATS[catId]) {
      SUBCATS[catId].forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name;
        subSel.appendChild(opt);
      });
    }
  }

  function validateRow(row){
    const name = row.querySelector('.name');
    const cat  = row.querySelector('.cat');
    const sub  = row.querySelector('.sub');
    const qty  = row.querySelector('.qty');
    let ok = true;

    [name, cat, sub, qty].forEach(el=> el.classList.remove('bad'));

    if (!name.value.trim()) { name.classList.add('bad'); ok = false; }
    if (!cat.value)         { cat.classList.add('bad');  ok = false; }
    if (!sub.value)         { sub.classList.add('bad');  ok = false; }
    const qv = parseFloat(qty.value || '0');
    if (!(qv >= 1))         { qty.classList.add('bad');  ok = false; }

    return ok;
  }

  function checkDuplicates(){
    const macs = new Map(), sns = new Map();
    tbody.querySelectorAll('tr').forEach(tr=>{
      const mac  = tr.querySelector('.mac')?.value.trim().toLowerCase() || '';
      const sn   = tr.querySelector('.sn') ?.value.trim().toLowerCase() || '';
      const hint = tr.querySelector('.dup-hint');
      let dup = false;
      if (mac) { if (macs.has(mac)) dup = true; else macs.set(mac,true); }
      if (sn)  { if (sns.has(sn))  dup = true; else sns.set(sn,true); }
      if (dup) { hint.style.display=''; tr.classList.add('bad'); }
      else     { hint.style.display='none'; tr.classList.remove('bad'); }
    });
  }

  function refreshSaveState(){
    const rows = [...tbody.querySelectorAll('tr')];
    if (!rows.length) {
      saveBtn.disabled = true;
      statusEl.textContent = 'Додайте хоча б один рядок.';
      return;
    }
    let allValid = true;
    rows.forEach(tr=>{ if(!validateRow(tr)) allValid = false; });
    checkDuplicates();
    const hasDup = rows.some(tr=> tr.querySelector('.dup-hint')?.style.display === '');
    saveBtn.disabled = !(allValid && !hasDup);
    statusEl.textContent = saveBtn.disabled ? 'Заповніть обовʼязкові поля та усуньте дублікати.' : '';
  }

  /* -------------------- Autocomplete: names -------------------- */
  async function fetchNames(q, catId, subId){
    const p = new URLSearchParams();
    p.set('this', 'names');
    if (q)     p.set('q', q);
    if (catId) p.set('catid', String(catId));
    if (subId) p.set('subid', String(subId));
    p.set('limit','12');
    const url = buildUrl(`act=add&${p.toString()}`);
    const r = await fetch(url, {credentials:'same-origin'});
    if (!r.ok) return {items:[]};
    return r.json();
  }
  function removeList(wrap){
    wrap?.querySelector('.autocomplete')?.remove();
  }
  function renderList(wrap, items, onPick){
    removeList(wrap);
    if (!items?.length) return;
    const box = document.createElement('div');
    box.className = 'autocomplete';
    items.forEach((it, idx)=>{
      const d = document.createElement('div');
      d.className = 'item';
      d.dataset.idx = String(idx);
      d.innerHTML = `<b>${escapeHtml(it.name)}</b> <span class="ac-meta">(${it.count})</span>`;
      d.addEventListener('mousedown', e=>{ e.preventDefault(); onPick(it); });
      box.appendChild(d);
    });
    wrap.appendChild(box);
    let active = 0;
    box.firstChild?.classList.add('active');
    const keyHandler = (e)=>{
      const items = [...box.querySelectorAll('.item')];
      if (!items.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); items[active]?.classList.remove('active'); active=(active+1)%items.length; items[active].classList.add('active'); items[active].scrollIntoView({block:'nearest'}); }
      if (e.key === 'ArrowUp') { e.preventDefault(); items[active]?.classList.remove('active'); active=(active-1+items.length)%items.length; items[active].classList.add('active'); items[active].scrollIntoView({block:'nearest'}); }
      if (e.key === 'Enter') { e.preventDefault(); items[active]?.dispatchEvent(new MouseEvent('mousedown')); }
      if (e.key === 'Escape') { e.preventDefault(); removeList(wrap); wrap.removeEventListener('keydown', keyHandler); }
    };
    wrap.addEventListener('keydown', keyHandler, {once:true});
  }
  const onType = debounce(async (input)=>{
    const tr    = input.closest('tr');
    const catId = tr.querySelector('.cat')?.value || '';
    const subId = tr.querySelector('.sub')?.value || '';
    const term  = input.value.trim();
    const wrap = input.closest('.ac-wrap');
    if (!wrap) return;
    if (!term || term.length < 2) { removeList(wrap); return; }
    try{
      const data = await fetchNames(term, catId, subId);
      renderList(wrap, data.items || [], (pick)=>{
        input.value = pick.name;
        const catSel = tr.querySelector('.cat');
        const subSel = tr.querySelector('.sub');
        if (catSel && !catSel.value && pick.category_id) {
          catSel.value = String(pick.category_id);
          fillSubcats(tr);
        }
        if (subSel && !subSel.value && pick.sub_cat_id) {
          subSel.value = String(pick.sub_cat_id);
        }
        removeList(wrap);
        validateRow(tr);
        refreshSaveState();
      });
    } catch(e){
      removeList(wrap);
    }
  }, 200);
  function attachNameAutocomplete(input){
    const wrapParent = input.parentElement;
    if (!wrapParent.classList.contains('ac-wrap')) {
      const wrap = document.createElement('div');
      wrap.className = 'ac-wrap';
      wrapParent.insertBefore(wrap, input);
      wrap.appendChild(input);
    }
    input.addEventListener('input', ()=> onType(input));
    input.addEventListener('focus', ()=> onType(input));
    input.addEventListener('blur',  ()=> setTimeout(()=> removeList(input.closest('.ac-wrap')), 120));
  }
  document.getElementById('addRow').addEventListener('click', ()=>{
    tbody.appendChild(makeRow());
    refreshSaveState();
  });
  document.getElementById('addFiveRows').addEventListener('click', ()=>{
    for(let i=0;i<5;i++) tbody.appendChild(makeRow());
    refreshSaveState();
  });
  tbody.addEventListener('change', (e)=>{
    const tr = e.target.closest('tr');
    if (!tr) return;
    if (e.target.classList.contains('cat')) {
      fillSubcats(tr);
      validateRow(tr);
    } else if (e.target.classList.contains('sub')) {
      validateRow(tr);
    } else if (e.target.classList.contains('qty') || e.target.classList.contains('name')) {
      validateRow(tr);
    } else if (e.target.classList.contains('mac') || e.target.classList.contains('sn')) {
      checkDuplicates();
    }
    refreshSaveState();
  });
  tbody.addEventListener('dblclick', (e)=>{
    if (e.target.classList.contains('qty')) {
      if (!e.target.value) { e.target.value = '1.00'; }
      refreshSaveState();
    }
  });
  tbody.addEventListener('click', (e)=>{
    if (e.target.classList.contains('rowDel')) {
      e.target.closest('tr').remove();
      refreshSaveState();
    }
  });
  document.getElementById('saveProducts').addEventListener('click', ()=>{
    const rows = [...tbody.querySelectorAll('tr')];
    const products = [];
    let anyInvalid = false;
    rows.forEach(tr=>{ if (!validateRow(tr)) anyInvalid = true; });
    checkDuplicates();
    if (anyInvalid) { refreshSaveState(); return; }
    rows.forEach(tr=>{
      const name = tr.querySelector('.name').value.trim();
      const cat = tr.querySelector('.cat').value;
      const sub = tr.querySelector('.sub').value;
      const qty = parseFloat(tr.querySelector('.qty').value || '0');
      const price = parseFloat(tr.querySelector('.price').value || '0');
      const sn = tr.querySelector('.sn').value.trim();
      const mac = tr.querySelector('.mac').value.trim();
      products.push({ name, category_id: cat, sub_id: sub, quantity: qty, price, sn, mac });
    });
    saveBtn.disabled  = true;
    statusEl.textContent = 'Збереження...';
    fetch(buildUrl('act=save'), {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ products })
    })
    .then(r=>r.json())
    .then(resp=>{
      if (resp?.status === 'success') {
        statusEl.textContent = 'Успіх: ' + (resp.message || 'Додано.');
        tbody.innerHTML = '';
        refreshSaveState();
      } else {
        saveBtn.disabled = false;
        statusEl.textContent = 'Помилка: ' + (resp?.message || 'невідома');
      }
    })
    .catch(()=>{
      saveBtn.disabled = false;
      statusEl.textContent = 'Помилка мережі або сервера.';
    });
  });
  tbody.appendChild(makeRow());
  refreshSaveState();
})();
</script>
JS;

?>
