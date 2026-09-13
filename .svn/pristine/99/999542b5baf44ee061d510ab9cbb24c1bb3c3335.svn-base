<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
$metatags = array('title' => 'API Statistics', 'description' => 'API Statistics', 'page' => 'stats_api_view');
$result_tpl = <<<'HTML'
<style>
.stats-wrap {
  --bg:#f4f7fb;
  --ink:#0f172a;
  --muted:#64748b;
  --line:#dce4ef;
  --panel:#ffffff;
  --brand:#0f7bff;
  --ok:#10b981;
  --warn:#f59e0b;
  --bad:#ef4444;
  --cold:#06b6d4;
  padding:0px;
}
.stats-layout {display:grid;grid-template-columns:290px 1fr;gap:14px;align-items:start;}
.stats-sidebar {
  background:linear-gradient(180deg, #ffffff, #f8fbff);
  border:1px solid var(--line);
  border-radius:12px;
  padding:12px;
  position:sticky;
  top:10px;
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}
.stats-side-title {font-size:13px;font-weight:800;color:var(--ink);margin:0 0 8px;letter-spacing:.01em;}
.stats-side-group {border-top:1px dashed #d9e3f0;padding-top:8px;margin-top:8px;}
.stats-side-label {display:block;font-size:12px;color:var(--muted);margin:7px 0 4px;}
.stats-side-input {width:100%;height:36px;padding:0 10px;border:1px solid #ced9e8;border-radius:9px;background:#fff;box-sizing:border-box;outline:none;}
.stats-side-input:focus {border-color:#8ab4ff;box-shadow:0 0 0 2px rgba(59,130,246,.12);}
.stats-side-check {display:flex;align-items:center;gap:7px;font-size:12px;color:#334155;margin:5px 0;}
.stats-side-actions {display:flex;gap:8px;margin-top:10px;}
.stats-side-actions button {flex:1;height:36px;border:0;border-radius:10px;cursor:pointer;font-weight:700;}
.stats-btn-primary {background:linear-gradient(135deg, #0f7bff, #2563eb);color:#fff;}
.stats-btn-secondary {background:#1e293b;color:#fff;}
.stats-btn-loading {opacity:.75;pointer-events:none;}
.stats-main {min-width:0;}
.stats-grid {display:grid;grid-template-columns:repeat(4,minmax(150px,1fr));gap:10px;margin-bottom:12px;}
.stats-card {
  background:var(--panel);
  border:1px solid var(--line);
  border-radius:12px;
  padding:10px;
  box-shadow:0 6px 12px rgba(15,23,42,.03);
}
.stats-card .k {font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;}
.stats-card .v {font-size:23px;font-weight:800;color:var(--ink);line-height:1.2;}
.stats-health,
.stats-panel,
.stats-block {
  background:var(--panel);
  border:1px solid var(--line);
  border-radius:12px;
  padding:11px;
  box-shadow:0 6px 12px rgba(15,23,42,.03);
}
.stats-health {margin-bottom:12px;}
.stats-health-head {display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:8px;}
.stats-health-title {font-size:13px;font-weight:800;color:var(--ink);}
.stats-health-note {font-size:12px;color:var(--muted);}
.stats-meter {margin-bottom:7px;}
.stats-meter-row {display:flex;justify-content:space-between;gap:8px;font-size:12px;color:#334155;margin-bottom:4px;}
.stats-track {height:10px;border-radius:999px;background:#edf2f8;overflow:hidden;border:1px solid #e0e7f1;}
.stats-fill {height:100%;width:0;background:#94a3b8;transition:width .28s ease;}
.stats-fill.ok {background:linear-gradient(90deg,#34d399,#10b981);}
.stats-fill.warn {background:linear-gradient(90deg,#fbbf24,#f59e0b);}
.stats-fill.bad {background:linear-gradient(90deg,#fb7185,#ef4444);}
.stats-fill.cold {background:linear-gradient(90deg,#22d3ee,#0891b2);}
.stats-panels {display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:10px;margin-bottom:12px;}
.stats-overview {display:grid;grid-template-columns:repeat(5,minmax(140px,1fr));gap:8px;margin-bottom:12px;}
.stats-over-card {background:#fbfdff;border:1px solid #e3eaf4;border-radius:10px;padding:9px;}
.stats-over-k {font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;}
.stats-over-v {font-size:18px;font-weight:800;color:#0f172a;line-height:1.2;margin-top:2px;}
.stats-title {font-size:12px;color:var(--muted);margin-bottom:6px;}
.stats-chart {width:100%;height:140px;display:block;background:transparent;}
.stats-hot-grid {display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;}
.stats-hot-list {display:grid;gap:8px;}
.stats-hot-item {border:1px solid #e6ecf4;border-radius:10px;padding:8px;background:#fbfdff;}
.stats-hot-top {display:flex;justify-content:space-between;gap:8px;font-size:12px;color:#0f172a;margin-bottom:5px;}
.stats-hot-key {font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.stats-hot-meta {color:#64748b;white-space:nowrap;}
.stats-hot-bars {display:grid;gap:4px;}
.stats-hot-bar {height:7px;border-radius:999px;background:#ecf1f8;overflow:hidden;}
.stats-hot-bar > span {display:block;height:100%;width:0;}
.stats-hot-bar.load > span {background:linear-gradient(90deg,#60a5fa,#2563eb);}
.stats-hot-bar.latency > span {background:linear-gradient(90deg,#f59e0b,#ea580c);}
.stats-hot-bar.risk > span {background:linear-gradient(90deg,#fb7185,#e11d48);}
.stats-tables {display:grid;grid-template-columns:1fr;gap:12px;}
.stats-block h3 {margin:0 0 6px;font-size:14px;color:var(--ink);}
.stats-note {font-size:12px;color:var(--muted);margin-bottom:8px;}
.stats-err {display:none;background:#fff1f2;border:1px solid #fecdd3;color:#9f1239;border-radius:8px;padding:8px;margin-bottom:10px;}
.stats-empty {color:#94a3b8;font-size:12px;}
.stats-table-wrap {overflow:auto;}

@media (max-width:1380px){
  .stats-layout{grid-template-columns:260px 1fr;}
  .stats-grid{grid-template-columns:repeat(3,minmax(140px,1fr));}
}
@media (max-width:1040px){
  .stats-layout{grid-template-columns:1fr;}
  .stats-sidebar{position:static;}
  .stats-grid{grid-template-columns:repeat(2,minmax(140px,1fr));}
  .stats-panels,
  .stats-hot-grid{grid-template-columns:1fr;}
  .stats-overview{grid-template-columns:repeat(2,minmax(120px,1fr));}
}
</style>

<div class="stats-wrap">
  <div id="stats-api-error" class="stats-err"></div>

  <div class="stats-layout">
    <aside class="stats-sidebar">
      <h3 class="stats-side-title">Панель діагностики</h3>

      <label class="stats-side-label" for="stats-hours">Період (годин)</label>
      <input class="stats-side-input" type="number" id="stats-hours" min="1" max="168" value="24">

      <label class="stats-side-label" for="stats-top">Top елементів</label>
      <input class="stats-side-input" type="number" id="stats-top" min="1" max="200" value="20">

      <label class="stats-side-label" for="stats-limit">Ліміт читання Redis</label>
      <input class="stats-side-input" type="number" id="stats-limit" min="200" max="200000" value="20000">

      <label class="stats-side-label" for="stats-bucket">Bucket (сек)</label>
      <input class="stats-side-input" type="number" id="stats-bucket" min="10" max="3600" value="60">

      <label class="stats-side-label" for="stats-id">Device ID (опц.)</label>
      <input class="stats-side-input" type="number" id="stats-id" min="1" placeholder="напр. 7">

      <div class="stats-side-actions">
        <button type="button" id="stats-load" class="stats-btn-primary">Оновити</button>
        <button type="button" id="stats-refresh" class="stats-btn-secondary" data-active="0">Авто: OFF</button>
      </div>

      <div class="stats-side-group">
        <div class="stats-side-title">Видимі блоки</div>
        <label class="stats-side-check"><input type="checkbox" data-block="cards" checked> Картки метрик</label>
        <label class="stats-side-check"><input type="checkbox" data-block="health" checked> Прогрес-бари</label>
        <label class="stats-side-check"><input type="checkbox" data-block="overview" checked> Загальне навантаження</label>
        <label class="stats-side-check"><input type="checkbox" data-block="chart-count" checked> Графік запитів</label>
        <label class="stats-side-check"><input type="checkbox" data-block="chart-avg" checked> Графік latency</label>
        <label class="stats-side-check"><input type="checkbox" data-block="chart-load" checked> Графік load index</label>
        <label class="stats-side-check"><input type="checkbox" data-block="hotspots-do" checked> Навантаження по діях</label>
        <label class="stats-side-check"><input type="checkbox" data-block="hotspots-id" checked> Навантаження по девайсах</label>
        <label class="stats-side-check"><input type="checkbox" data-block="recent" checked> Останні запити</label>
        <label class="stats-side-check"><input type="checkbox" data-block="slow" checked> Найповільніші</label>
        <label class="stats-side-check"><input type="checkbox" data-block="bydo" checked> Група по діях</label>
        <label class="stats-side-check"><input type="checkbox" data-block="byid" checked> Група по ID</label>
      </div>

      <div class="stats-side-group">
        <div class="stats-side-title">Поля запитів</div>
        <label class="stats-side-check"><input type="checkbox" data-col-group="req" data-col="ts" checked> TS</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="req" data-col="do" checked> DO</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="req" data-col="id" checked> ID</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="req" data-col="status" checked> Status</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="req" data-col="ms" checked> ms</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="req" data-col="ip" checked> IP</label>
      </div>

      <div class="stats-side-group">
        <div class="stats-side-title">Поля агрегатів</div>
        <label class="stats-side-check"><input type="checkbox" data-col-group="grp" data-col="count" checked> count</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="grp" data-col="avg_ms" checked> avg_ms</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="grp" data-col="max_ms" checked> max_ms</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="grp" data-col="slow_count" checked> slow_count</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="grp" data-col="error_count" checked> error_count</label>
        <label class="stats-side-check"><input type="checkbox" data-col-group="grp" data-col="slow_rate_percent" checked> slow_%</label>
      </div>
    </aside>

    <section class="stats-main">
      <div id="block-cards" class="stats-grid">
        <div class="stats-card"><div class="k">Requests</div><div class="v" id="s-count">0</div></div>
        <div class="stats-card"><div class="k">Success</div><div class="v" id="s-success">0</div></div>
        <div class="stats-card"><div class="k">Errors</div><div class="v" id="s-errors">0</div></div>
        <div class="stats-card"><div class="k">Slow</div><div class="v" id="s-slow">0</div></div>
        <div class="stats-card"><div class="k">Avg ms</div><div class="v" id="s-avg">0</div></div>
        <div class="stats-card"><div class="k">P95 ms</div><div class="v" id="s-p95">0</div></div>
        <div class="stats-card"><div class="k">P99 ms</div><div class="v" id="s-p99">0</div></div>
        <div class="stats-card"><div class="k">Max ms</div><div class="v" id="s-max">0</div></div>
      </div>

      <div id="block-health" class="stats-health">
        <div class="stats-health-head">
          <div class="stats-health-title">Стан продуктивності</div>
          <div class="stats-health-note" id="slow-threshold-note">Поріг slow: 300 ms</div>
        </div>

        <div class="stats-meter">
          <div class="stats-meter-row"><span>Успішні запити</span><span id="m-success-txt">0%</span></div>
          <div class="stats-track"><div class="stats-fill ok" id="m-success"></div></div>
        </div>

        <div class="stats-meter">
          <div class="stats-meter-row"><span>Помилки</span><span id="m-error-txt">0%</span></div>
          <div class="stats-track"><div class="stats-fill bad" id="m-error"></div></div>
        </div>

        <div class="stats-meter">
          <div class="stats-meter-row"><span>Швидкі запити</span><span id="m-fast-txt">0%</span></div>
          <div class="stats-track"><div class="stats-fill cold" id="m-fast"></div></div>
        </div>

        <div class="stats-meter">
          <div class="stats-meter-row"><span>Повільні запити</span><span id="m-slow-txt">0%</span></div>
          <div class="stats-track"><div class="stats-fill warn" id="m-slow"></div></div>
        </div>

        <div class="stats-meter">
          <div class="stats-meter-row"><span>Avg відносно порогу</span><span id="m-avg-txt">0%</span></div>
          <div class="stats-track"><div class="stats-fill" id="m-avg"></div></div>
        </div>
      </div>

      <div id="block-overview" class="stats-overview">
        <div class="stats-over-card"><div class="stats-over-k">Req/hour</div><div class="stats-over-v" id="ov-rph">0</div></div>
        <div class="stats-over-card"><div class="stats-over-k">Req/min</div><div class="stats-over-v" id="ov-rpm">0</div></div>
        <div class="stats-over-card"><div class="stats-over-k">Peak bucket</div><div class="stats-over-v" id="ov-peak">0</div></div>
        <div class="stats-over-card"><div class="stats-over-k">Active switches</div><div class="stats-over-v" id="ov-active">0</div></div>
        <div class="stats-over-card"><div class="stats-over-k">Processing score</div><div class="stats-over-v" id="ov-proc">0%</div></div>
      </div>

      <div class="stats-panels">
        <div id="block-chart-count" class="stats-panel">
          <div class="stats-title">Кількість запитів по bucket</div>
          <canvas id="chart-count" class="stats-chart" width="1000" height="140"></canvas>
        </div>
        <div id="block-chart-avg" class="stats-panel">
          <div class="stats-title">Середній час відповіді (ms)</div>
          <canvas id="chart-avg" class="stats-chart" width="1000" height="140"></canvas>
        </div>
        <div id="block-chart-load" class="stats-panel">
          <div class="stats-title">Load index (count + slow + error)</div>
          <canvas id="chart-load" class="stats-chart" width="1000" height="140"></canvas>
        </div>
      </div>

      <div class="stats-hot-grid">
        <div id="block-hotspots-do" class="stats-panel">
          <div class="stats-title">Карта навантаження по діях</div>
          <div id="spot-do" class="stats-hot-list"></div>
        </div>
        <div id="block-hotspots-id" class="stats-panel">
          <div class="stats-title">Карта навантаження по девайсах</div>
          <div id="spot-id" class="stats-hot-list"></div>
        </div>
      </div>

      <div class="stats-tables">
        <div id="block-recent" class="stats-block">
          <h3>Останні запити</h3>
          <div class="stats-note">Останні події API з Redis</div>
          <div class="stats-table-wrap">
            <table class="resp-tab" id="tbl-recent">
              <thead>
                <tr>
                  <th data-col="ts">TS</th>
                  <th data-col="do">DO</th>
                  <th data-col="id">ID</th>
                  <th data-col="status">Status</th>
                  <th data-col="ms">ms</th>
                  <th data-col="ip">IP</th>
                </tr>
              </thead>
              <tbody><tr><td colspan="6" class="stats-empty">No data</td></tr></tbody>
            </table>
          </div>
        </div>

        <div id="block-slow" class="stats-block">
          <h3>Найповільніші</h3>
          <div class="stats-note">Топ повільних запитів за latency</div>
          <div class="stats-table-wrap">
            <table class="resp-tab" id="tbl-slow">
              <thead>
                <tr>
                  <th data-col="ts">TS</th>
                  <th data-col="do">DO</th>
                  <th data-col="id">ID</th>
                  <th data-col="status">Status</th>
                  <th data-col="ms">ms</th>
                  <th data-col="ip">IP</th>
                </tr>
              </thead>
              <tbody><tr><td colspan="6" class="stats-empty">No data</td></tr></tbody>
            </table>
          </div>
        </div>

        <div id="block-bydo" class="stats-block">
          <h3>Групування по діях</h3>
          <div class="stats-table-wrap">
            <table class="resp-tab" id="tbl-do">
              <thead>
                <tr>
                  <th>DO</th>
                  <th data-col="count">count</th>
                  <th data-col="avg_ms">avg_ms</th>
                  <th data-col="max_ms">max_ms</th>
                  <th data-col="slow_count">slow_count</th>
                  <th data-col="error_count">error_count</th>
                  <th data-col="slow_rate_percent">slow_%</th>
                </tr>
              </thead>
              <tbody><tr><td colspan="7" class="stats-empty">No data</td></tr></tbody>
            </table>
          </div>
        </div>

        <div id="block-byid" class="stats-block">
          <h3>Групування по device ID</h3>
          <div class="stats-table-wrap">
            <table class="resp-tab" id="tbl-id">
              <thead>
                <tr>
                  <th>Комутатор</th>
                  <th data-col="count">count</th>
                  <th data-col="avg_ms">avg_ms</th>
                  <th data-col="max_ms">max_ms</th>
                  <th data-col="slow_count">slow_count</th>
                  <th data-col="error_count">error_count</th>
                  <th data-col="slow_rate_percent">slow_%</th>
                </tr>
              </thead>
              <tbody><tr><td colspan="7" class="stats-empty">No data</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<script>
(function() {
  var autoTimer = null;
  var CFG_KEY = 'stats_api_view_cfg_v3';
  var cfg = {
    blocks: {
      cards: true,
      health: true,
      overview: true,
      'chart-count': true,
      'chart-avg': true,
      'chart-load': true,
      'hotspots-do': true,
      'hotspots-id': true,
      recent: true,
      slow: true,
      bydo: true,
      byid: true
    },
    reqCols: {ts:true, do:true, id:true, status:true, ms:true, ip:true},
    grpCols: {count:true, avg_ms:true, max_ms:true, slow_count:true, error_count:true, slow_rate_percent:true}
  };

  function qs(id){ return document.getElementById(id); }
  function qsa(sel){ return document.querySelectorAll(sel); }
  function esc(v){
    return String(v === null || typeof v === 'undefined' ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function toNum(v){ var n = Number(v); return Number.isFinite(n) ? n : 0; }
  function clamp(n, min, max){ return Math.max(min, Math.min(max, n)); }
  function fmtTs(ts){
    var n = Number(ts);
    if (!Number.isFinite(n) || n <= 0) return '';
    var d = new Date(n * 1000);
    return d.toLocaleString();
  }

  function loadCfg(){
    try {
      var raw = localStorage.getItem(CFG_KEY);
      if (!raw) return;
      var parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') return;
      if (parsed.blocks) Object.assign(cfg.blocks, parsed.blocks);
      if (parsed.reqCols) Object.assign(cfg.reqCols, parsed.reqCols);
      if (parsed.grpCols) Object.assign(cfg.grpCols, parsed.grpCols);
    } catch (e) {}
  }

  function saveCfg(){
    try { localStorage.setItem(CFG_KEY, JSON.stringify(cfg)); } catch (e) {}
  }

  function showError(msg){
    var box = qs('stats-api-error');
    box.style.display = 'block';
    box.textContent = msg;
  }

  function clearError(){
    var box = qs('stats-api-error');
    box.style.display = 'none';
    box.textContent = '';
  }

  function drawLine(canvasId, points, lineColor, fillColor){
    var c = qs(canvasId);
    var ctx = c.getContext('2d');
    var w = c.width;
    var h = c.height;

    ctx.clearRect(0,0,w,h);

    if (!points || !points.length) {
      ctx.fillStyle = '#94a3b8';
      ctx.font = '12px Segoe UI';
      ctx.fillText('No data', 12, 18);
      return;
    }

    var max = 0;
    for (var i=0; i<points.length; i++) if (points[i] > max) max = points[i];
    if (max <= 0) max = 1;

    var left = 16, right = w - 16, top = 10, bottom = h - 14;
    var step = points.length > 1 ? (right - left) / (points.length - 1) : 0;

    ctx.strokeStyle = '#e6edf7';
    ctx.lineWidth = 1;
    for (var g=0; g<4; g++) {
      var gy = top + ((bottom - top) / 3) * g;
      ctx.beginPath();
      ctx.moveTo(left, gy);
      ctx.lineTo(right, gy);
      ctx.stroke();
    }

    ctx.beginPath();
    for (var p=0; p<points.length; p++) {
      var x = left + p * step;
      var y = bottom - (points[p] / max) * (bottom - top);
      if (p === 0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
    }
    var lastX = left + (points.length - 1) * step;
    ctx.lineTo(lastX, bottom);
    ctx.lineTo(left, bottom);
    ctx.closePath();
    ctx.fillStyle = fillColor;
    ctx.fill();

    ctx.strokeStyle = lineColor;
    ctx.lineWidth = 2;
    ctx.beginPath();
    for (var p2=0; p2<points.length; p2++) {
      var x2 = left + p2 * step;
      var y2 = bottom - (points[p2] / max) * (bottom - top);
      if (p2 === 0) ctx.moveTo(x2,y2); else ctx.lineTo(x2,y2);
    }
    ctx.stroke();
  }

  function setSummary(s){
    qs('s-count').textContent = toNum(s.count).toFixed(0);
    qs('s-success').textContent = toNum(s.success_count).toFixed(0);
    qs('s-errors').textContent = toNum(s.error_count).toFixed(0);
    qs('s-slow').textContent = toNum(s.slow_count).toFixed(0);
    qs('s-avg').textContent = toNum(s.avg_ms).toFixed(2);
    qs('s-p95').textContent = toNum(s.p95_ms).toFixed(2);
    qs('s-p99').textContent = toNum(s.p99_ms).toFixed(2);
    qs('s-max').textContent = toNum(s.max_ms).toFixed(2);
  }

  function setMeter(fillId, txtId, percent, text, mode){
    var fill = qs(fillId);
    var txt = qs(txtId);
    var p = clamp(toNum(percent), 0, 100);
    fill.style.width = p.toFixed(2) + '%';
    fill.className = 'stats-fill ' + (mode || '');
    txt.textContent = text;
  }

  function setHealth(summary, storage){
    var slowMs = toNum(storage.slow_ms || 300);
    var avg = toNum(summary.avg_ms);
    var avgShare = slowMs > 0 ? clamp((avg * 100) / slowMs, 0, 160) : 0;

    qs('slow-threshold-note').textContent = 'Поріг slow: ' + slowMs.toFixed(0) + ' ms';

    setMeter('m-success', 'm-success-txt', toNum(summary.success_rate_percent), toNum(summary.success_rate_percent).toFixed(2) + '%', 'ok');
    setMeter('m-error', 'm-error-txt', toNum(summary.error_rate_percent), toNum(summary.error_rate_percent).toFixed(2) + '%', 'bad');
    setMeter('m-fast', 'm-fast-txt', toNum(summary.fast_rate_percent), toNum(summary.fast_rate_percent).toFixed(2) + '%', 'cold');
    setMeter('m-slow', 'm-slow-txt', toNum(summary.slow_rate_percent), toNum(summary.slow_rate_percent).toFixed(2) + '%', 'warn');

    var avgMode = avgShare < 65 ? 'ok' : (avgShare < 100 ? 'warn' : 'bad');
    setMeter('m-avg', 'm-avg-txt', avgShare, avg.toFixed(2) + ' ms (' + avgShare.toFixed(1) + '%)', avgMode);
  }

  function setOverview(o){
    qs('ov-rph').textContent = toNum(o.requests_per_hour).toFixed(2);
    qs('ov-rpm').textContent = toNum(o.requests_per_minute).toFixed(3);
    qs('ov-peak').textContent = toNum(o.peak_bucket_requests).toFixed(0);
    qs('ov-active').textContent = toNum(o.active_switches).toFixed(0);
    qs('ov-proc').textContent = toNum(o.processing_score).toFixed(2) + '%';
  }

  function fillRows(tableId, rows, cols, mapFn){
    var tbody = qs(tableId).querySelector('tbody');
    if (!rows || !rows.length) {
      tbody.innerHTML = '<tr><td colspan="'+cols+'" class="stats-empty">No data</td></tr>';
      return;
    }

    var html = '';
    for (var i=0; i<rows.length; i++) {
      var r = mapFn(rows[i]);
      html += '<tr>';
      for (var j=0; j<r.length; j++) html += '<td>' + esc(r[j]) + '</td>';
      html += '</tr>';
    }
    tbody.innerHTML = html;
  }

  function renderHotspots(targetId, rows, keyField){
    var box = qs(targetId);
    if (!rows || !rows.length) {
      box.innerHTML = '<div class="stats-empty">No data</div>';
      return;
    }

    var maxCount = 0;
    var maxAvg = 0;
    for (var i=0; i<rows.length; i++) {
      if (toNum(rows[i].count) > maxCount) maxCount = toNum(rows[i].count);
      if (toNum(rows[i].avg_ms) > maxAvg) maxAvg = toNum(rows[i].avg_ms);
    }
    if (maxCount <= 0) maxCount = 1;
    if (maxAvg <= 0) maxAvg = 1;

    var html = '';
    for (var j=0; j<rows.length; j++) {
      var r = rows[j];
      var key = keyField === 'id'
        ? String(r.switch_name || ('#' + toNum(r.id).toFixed(0)))
        : String(r.do || 'unknown');
      var loadPct = clamp((toNum(r.count) * 100) / maxCount, 0, 100);
      var avgPct = clamp((toNum(r.avg_ms) * 100) / maxAvg, 0, 100);
      var riskPct = clamp(toNum(r.slow_rate_percent), 0, 100);

      html += '<div class="stats-hot-item">'
        + '<div class="stats-hot-top">'
        + '<div class="stats-hot-key">' + esc(key) + '</div>'
        + '<div class="stats-hot-meta">count: ' + esc(r.count) + ' | avg: ' + esc(r.avg_ms) + ' ms</div>'
        + '</div>'
        + '<div class="stats-hot-bars">'
        + '<div class="stats-hot-bar load"><span style="width:' + loadPct.toFixed(2) + '%"></span></div>'
        + '<div class="stats-hot-bar latency"><span style="width:' + avgPct.toFixed(2) + '%"></span></div>'
        + '<div class="stats-hot-bar risk"><span style="width:' + riskPct.toFixed(2) + '%"></span></div>'
        + '</div>'
        + '</div>';
    }

    box.innerHTML = html;
  }

  function applyBlockVisibility(){
    var map = {
      cards: 'block-cards',
      health: 'block-health',
      overview: 'block-overview',
      'chart-count': 'block-chart-count',
      'chart-avg': 'block-chart-avg',
      'chart-load': 'block-chart-load',
      'hotspots-do': 'block-hotspots-do',
      'hotspots-id': 'block-hotspots-id',
      recent: 'block-recent',
      slow: 'block-slow',
      bydo: 'block-bydo',
      byid: 'block-byid'
    };

    Object.keys(map).forEach(function(k){
      var el = qs(map[k]);
      if (!el) return;
      el.style.display = cfg.blocks[k] ? '' : 'none';
    });
  }

  function applyTableColumnVisibility(tableId, groupName){
    var tbl = qs(tableId);
    if (!tbl) return;

    var set = groupName === 'req' ? cfg.reqCols : cfg.grpCols;
    var heads = tbl.querySelectorAll('thead th[data-col]');

    heads.forEach(function(th){
      var key = th.getAttribute('data-col');
      var idx = th.cellIndex;
      var show = set[key] !== false;

      th.style.display = show ? '' : 'none';

      var rows = tbl.querySelectorAll('tbody tr');
      rows.forEach(function(tr){
        if (tr.cells[idx]) tr.cells[idx].style.display = show ? '' : 'none';
      });
    });
  }

  function applyAllVisibility(){
    applyBlockVisibility();
    applyTableColumnVisibility('tbl-recent', 'req');
    applyTableColumnVisibility('tbl-slow', 'req');
    applyTableColumnVisibility('tbl-do', 'grp');
    applyTableColumnVisibility('tbl-id', 'grp');
  }

  function syncControlsFromCfg(){
    qsa('input[data-block]').forEach(function(el){
      var key = el.getAttribute('data-block');
      el.checked = cfg.blocks[key] !== false;
    });

    qsa('input[data-col-group="req"]').forEach(function(el){
      var key = el.getAttribute('data-col');
      el.checked = cfg.reqCols[key] !== false;
    });

    qsa('input[data-col-group="grp"]').forEach(function(el){
      var key = el.getAttribute('data-col');
      el.checked = cfg.grpCols[key] !== false;
    });
  }

  function bindControlEvents(){
    qsa('input[data-block]').forEach(function(el){
      el.addEventListener('change', function(){
        var key = this.getAttribute('data-block');
        cfg.blocks[key] = !!this.checked;
        saveCfg();
        applyAllVisibility();
      });
    });

    qsa('input[data-col-group="req"]').forEach(function(el){
      el.addEventListener('change', function(){
        var key = this.getAttribute('data-col');
        cfg.reqCols[key] = !!this.checked;
        saveCfg();
        applyAllVisibility();
      });
    });

    qsa('input[data-col-group="grp"]').forEach(function(el){
      el.addEventListener('change', function(){
        var key = this.getAttribute('data-col');
        cfg.grpCols[key] = !!this.checked;
        saveCfg();
        applyAllVisibility();
      });
    });
  }

  function query(){
    var params = new URLSearchParams();
    params.set('do', 'stats_api');
    params.set('format', 'json');
    params.set('hours', qs('stats-hours').value || '24');
    params.set('top', qs('stats-top').value || '20');
    params.set('limit', qs('stats-limit').value || '20000');
    params.set('bucket', qs('stats-bucket').value || '60');

    var id = (qs('stats-id').value || '').trim();
    if (id !== '') params.set('id', id);

    return '/?' + params.toString();
  }

  function setLoadState(isLoading){
    var btn = qs('stats-load');
    if (isLoading) {
      btn.classList.add('stats-btn-loading');
      btn.textContent = 'Завантаження...';
    } else {
      btn.classList.remove('stats-btn-loading');
      btn.textContent = 'Оновити';
    }
  }

  function load(){
    clearError();
    setLoadState(true);

    fetch(query(), {credentials:'same-origin'})
      .then(function(res){ return res.json(); })
      .then(function(data){
        if (!data || data.ok !== true) {
          showError('API returned error');
          return;
        }

        setSummary(data.summary || {});
        setHealth(data.summary || {}, data.storage || {});
        setOverview(data.overview || {});

        fillRows('tbl-recent', data.recent || [], 6, function(r){
          return [fmtTs(r.ts), r.do, r.id === null ? '-' : r.id, r.status, r.ms, r.ip];
        });

        fillRows('tbl-slow', data.slowest || [], 6, function(r){
          return [fmtTs(r.ts), r.do, r.id === null ? '-' : r.id, r.status, r.ms, r.ip];
        });

        fillRows('tbl-do', data.by_do || [], 7, function(r){
          return [r.do, r.count, r.avg_ms, r.max_ms, r.slow_count, r.error_count, r.slow_rate_percent];
        });

        fillRows('tbl-id', data.by_id || [], 7, function(r){
          var sw = r.switch_name ? (r.switch_name + ' [#' + r.id + ']') : ('#' + r.id);
          return [sw, r.count, r.avg_ms, r.max_ms, r.slow_count, r.error_count, r.slow_rate_percent];
        });

        renderHotspots('spot-do', data.by_do || [], 'do');
        renderHotspots('spot-id', data.by_id || [], 'id');

        var series = data.series || [];
        var counts = [];
        var avgms = [];
        var loadIndex = [];
        for (var i=0; i<series.length; i++) {
          counts.push(toNum(series[i].count));
          avgms.push(toNum(series[i].avg_ms));
          loadIndex.push(toNum(series[i].count) + (toNum(series[i].slow_count) * 2) + (toNum(series[i].error_count) * 3));
        }

        drawLine('chart-count', counts, '#2563eb', 'rgba(37,99,235,.12)');
        drawLine('chart-avg', avgms, '#ea580c', 'rgba(234,88,12,.12)');
        drawLine('chart-load', loadIndex, '#7c3aed', 'rgba(124,58,237,.12)');

        applyAllVisibility();
      })
      .catch(function(err){
        showError('Fetch error: ' + err.message);
      })
      .finally(function(){
        setLoadState(false);
      });
  }

  qs('stats-load').addEventListener('click', load);
  qs('stats-refresh').addEventListener('click', function(){
    var active = this.getAttribute('data-active') === '1';
    if (active) {
      this.setAttribute('data-active', '0');
      this.textContent = 'Авто: OFF';
      if (autoTimer) clearInterval(autoTimer);
      autoTimer = null;
      return;
    }

    this.setAttribute('data-active', '1');
    this.textContent = 'Авто: ON';
    load();
    autoTimer = setInterval(load, 10000);
  });

  loadCfg();
  syncControlsFromCfg();
  bindControlEvents();
  applyAllVisibility();
  load();
})();
</script>
HTML;

$result = '<div id="onu-speedbar">'
    . '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>'
    . '<a class="brmhref" href="/?do=operator"><i class="fi fi-rr-angle-left"></i>Система</a>'
    . '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Статистика API</span>'
    . '</div>'
    . $result_tpl;

if (isset($tpl) && is_object($tpl)) {
    $tpl->result['content'] = $result;
}
