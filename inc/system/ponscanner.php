<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

$act = isset($_REQUEST['act']) ? totranslit((string)$_REQUEST['act']) : '';

if ($act === 'scan' || $act === 'json') {
    @ini_set('display_errors', '0');
    @ini_set('html_errors', '0');
    if (function_exists('set_time_limit')) {
        @set_time_limit(30);
    }

    $errorHandler = static function (int $severity, string $message, string $file, int $line): bool {
        error_log('[PONSCANNER PHP] ' . $message . ' in ' . $file . ':' . $line);
        return true;
    };
    set_error_handler($errorHandler);

    if (ob_get_level() === 0) {
        ob_start();
    }

    $payload = null;
    try {
        $params = ponscanner_read_params($_REQUEST);
        $payload = ponscanner_build_report($params);
    } catch (Throwable $e) {
        http_response_code(200);
        error_log('[PONSCANNER] ' . $e->getMessage());
        $payload = array(
            'ok' => false,
            'error' => 'ponscanner_runtime_error',
            'message' => $e->getMessage()
        );
    } finally {
        restore_error_handler();
    }

    $noise = trim((string)ob_get_clean());
    if ($noise !== '') {
        error_log('[PONSCANNER NOISE] ' . substr($noise, 0, 1000));
        if (!is_array($payload)) {
            $payload = array('ok' => false, 'error' => 'ponscanner_output_noise');
        } else {
            $payload['debug_noise'] = 'suppressed';
        }
    }

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!headers_sent()) {
    @ini_set('default_charset', 'UTF-8');
    header('Content-Type: text/html; charset=utf-8');
}

$metatags = array(
    'title' => 'PON Діагностика',
    'description' => 'PON Діагностика',
    'page' => 'ponscanner'
);

$result_tpl = <<<'HTML'
<style>
.ponscan-layout{display:grid;grid-template-columns:290px 1fr;gap:12px;align-items:start;}
.ponscan-side{background:#fff;border:1px solid #dbe5f1;border-radius:10px;padding:10px;position:sticky;top:10px;}
.ponscan-title{font-size:13px;font-weight:700;color:#0f172a;margin:0 0 8px;}
.ponscan-label{display:block;font-size:12px;color:#64748b;margin:7px 0 4px;}
.ponscan-input{width:100%;height:34px;padding:0 10px;border:1px solid #d5dde8;border-radius:8px;background:#fff;box-sizing:border-box;}
.ponscan-btn-row{display:flex;gap:8px;margin-top:10px;}
.ponscan-btn{flex:1;height:34px;border:0;border-radius:8px;cursor:pointer;}
.ponscan-btn-main{background:#0f7bff;color:#fff;}
.ponscan-btn-sub{background:#64748b;color:#fff;}
.ponscan-main{min-width:0;}
.ponscan-progress{display:none;background:#fff;border:1px solid #dfe7f0;border-radius:10px;padding:10px;margin-bottom:10px;}
.ponscan-progress-head{display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#334155;margin-bottom:6px;}
.ponscan-progress-track{height:8px;background:#e2e8f0;border-radius:999px;overflow:hidden;}
.ponscan-progress-bar{height:8px;width:0;background:linear-gradient(90deg,#0f7bff,#06b6d4);border-radius:999px;transition:width .25s ease;}
.ponscan-cards{display:grid;grid-template-columns:repeat(6,minmax(120px,1fr));gap:10px;margin-bottom:12px;}
.ponscan-card{background:#fff;border:1px solid #dfe7f0;border-radius:10px;padding:10px;}
.ponscan-card .k{font-size:12px;color:#64748b;}
.ponscan-card .v{font-size:22px;font-weight:700;color:#0f172a;line-height:1.2;}
.ponscan-health{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;}
.ponscan-meter-box{background:#fff;border:1px solid #dfe7f0;border-radius:10px;padding:10px;}
.ponscan-meter-title{font-size:13px;font-weight:700;color:#0f172a;margin-bottom:8px;}
.ponscan-meter{margin-bottom:7px;}
.ponscan-meter-row{display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#334155;margin-bottom:4px;}
.ponscan-track{height:8px;background:#e5ecf5;border-radius:999px;overflow:hidden;}
.ponscan-fill{height:8px;width:0;background:#94a3b8;transition:width .2s ease;border-radius:999px;}
.ponscan-fill.critical{background:linear-gradient(90deg,#7f1d1d,#dc2626);}
.ponscan-fill.high{background:linear-gradient(90deg,#b91c1c,#ef4444);}
.ponscan-fill.medium{background:linear-gradient(90deg,#f59e0b,#fbbf24);}
.ponscan-fill.coverage{background:linear-gradient(90deg,#0f7bff,#06b6d4);}
.ponscan-fill.quality{background:linear-gradient(90deg,#10b981,#34d399);}
.ponscan-block{background:#fff;border:1px solid #dfe7f0;border-radius:10px;padding:10px;margin-bottom:12px;}
.ponscan-block h3{margin:0 0 8px;font-size:14px;color:#0f172a;}
.ponscan-note{font-size:12px;color:#64748b;margin-bottom:8px;}
.ponscan-err{display:none;background:#fff1f2;border:1px solid #fecdd3;color:#9f1239;border-radius:8px;padding:8px;margin-bottom:10px;}
.sev{display:inline-block;padding:2px 7px;border-radius:3px;font-size:11px;font-weight:700;text-transform:uppercase;}
.sev-critical{background:red;color:#fff;}
.sev-high{background:tomato;color:#fff;}
.sev-medium{background:#f59e0b;color:#111827;}
.sev-low{background:#93c5fd;color:#0f172a;}
.sev-info{background:#e2e8f0;color:#334155;}
.ponscan-small{font-size:12px;color:#64748b;}
.ponscan-empty{font-size:12px;color:#94a3b8;}
@media (max-width:1320px){.ponscan-layout{grid-template-columns:240px 1fr;}.ponscan-cards{grid-template-columns:repeat(3,minmax(120px,1fr));}}
@media (max-width:900px){.ponscan-layout{grid-template-columns:1fr;}.ponscan-side{position:static;}.ponscan-cards{grid-template-columns:repeat(2,minmax(120px,1fr));}.ponscan-health{grid-template-columns:1fr;}}
</style>
<div class="ponscan-wrap">
  <div id="ponscan-error" class="ponscan-err"></div>
  <div id="ponscan-progress" class="ponscan-progress">
    <div class="ponscan-progress-head"><span id="ponscan-progress-label">Запуск аналізу...</span><span id="ponscan-progress-value">0%</span></div>
    <div class="ponscan-progress-track"><div id="ponscan-progress-bar" class="ponscan-progress-bar"></div></div>
  </div>
  <div class="ponscan-layout">
    <aside class="ponscan-side">
      <h3 class="ponscan-title">PON Діагностика</h3>
      <label class="ponscan-label" for="ponscan-hours">Історія (годин)</label>
      <input class="ponscan-input" type="number" id="ponscan-hours" min="6" max="336" value="24">
      <label class="ponscan-label" for="ponscan-min-samples">Мінімум семплів</label>
      <input class="ponscan-input" type="number" id="ponscan-min-samples" min="6" max="400" value="18">
      <label class="ponscan-label" for="ponscan-top">Топ записів</label>
      <input class="ponscan-input" type="number" id="ponscan-top" min="5" max="300" value="60">
      <label class="ponscan-label" for="ponscan-olt-id">OLT ID (необовʼязково)</label>
      <input class="ponscan-input" type="number" id="ponscan-olt-id" min="1">
      <label class="ponscan-label" for="ponscan-onu-id">ONU ID (необовʼязково)</label>
      <input class="ponscan-input" type="number" id="ponscan-onu-id" min="1">
      <div class="ponscan-btn-row"><button type="button" id="ponscan-run" class="ponscan-btn ponscan-btn-main">Аналізувати</button><button type="button" id="ponscan-auto" class="ponscan-btn ponscan-btn-sub" data-active="0">Авто: ВИМК</button></div>
      <div class="ponscan-btn-row"><button type="button" id="ponscan-refresh" class="ponscan-btn ponscan-btn-sub">Оновити</button></div>
      <div class="ponscan-small" id="ponscan-updated" style="margin-top:8px;">Оновлено: -</div>
    </aside>
    <section class="ponscan-main">
      <div class="ponscan-cards">
        <div class="ponscan-card"><div class="k">OLT у вибірці</div><div class="v" id="pc-olts">0</div></div>
        <div class="ponscan-card"><div class="k">ONU у видимості</div><div class="v" id="pc-onu-visible">0</div></div>
        <div class="ponscan-card"><div class="k">ONU проаналізовано</div><div class="v" id="pc-onu-analyzed">0</div></div>
        <div class="ponscan-card"><div class="k">Проблемних PON портів</div><div class="v" id="pc-problem-ports">0</div></div>
        <div class="ponscan-card"><div class="k">ONU з деградацією</div><div class="v" id="pc-degraded-onu">0</div></div>
        <div class="ponscan-card"><div class="k">Критичних + Високих</div><div class="v" id="pc-critical-high">0</div></div>
      </div>
      <div class="ponscan-health">
        <div class="ponscan-meter-box">
          <div class="ponscan-meter-title">Розподіл ризиків</div>
          <div class="ponscan-meter"><div class="ponscan-meter-row"><span>Критичний</span><span id="pm-critical-v">0%</span></div><div class="ponscan-track"><div id="pm-critical" class="ponscan-fill critical"></div></div></div>
          <div class="ponscan-meter"><div class="ponscan-meter-row"><span>Високий</span><span id="pm-high-v">0%</span></div><div class="ponscan-track"><div id="pm-high" class="ponscan-fill high"></div></div></div>
          <div class="ponscan-meter"><div class="ponscan-meter-row"><span>Середній</span><span id="pm-medium-v">0%</span></div><div class="ponscan-track"><div id="pm-medium" class="ponscan-fill medium"></div></div></div>
        </div>
        <div class="ponscan-meter-box">
          <div class="ponscan-meter-title">Якість опрацювання</div>
          <div class="ponscan-meter"><div class="ponscan-meter-row"><span>Покриття ONU</span><span id="pm-cov-v">0%</span></div><div class="ponscan-track"><div id="pm-cov" class="ponscan-fill coverage"></div></div></div>
          <div class="ponscan-meter"><div class="ponscan-meter-row"><span>Індекс стану</span><span id="pm-quality-v">0%</span></div><div class="ponscan-track"><div id="pm-quality" class="ponscan-fill quality"></div></div></div>
        </div>
      </div>
      <div class="ponscan-block"><h3>Проблемні PON порти</h3><div class="ponscan-note">Локалізація рахується за відстанями ONU з деградацією: якщо вони зібрані в вузькій зоні, проблема ймовірно саме там.</div><table class="resp-tab" id="ponscan-port"><thead><tr><th>Рівень</th><th>OLT</th><th>PON порт</th><th>ONU деград./всього</th><th>Деградація %</th><th>Сер. Δ6h</th><th>Сер. RX 1h</th><th>Зона (км)</th><th>Локалізація</th><th>Бал</th></tr></thead><tbody><tr><td colspan="10" class="ponscan-empty">Немає даних</td></tr></tbody></table></div>
      <div class="ponscan-block"><h3>PON дерево по OLT</h3><table class="resp-tab" id="ponscan-tree"><thead><tr><th>OLT</th><th>Всього портів</th><th>Проблемних портів</th><th>ONU з деградацією</th><th>Топ проблемний порт</th></tr></thead><tbody><tr><td colspan="5" class="ponscan-empty">Немає даних</td></tr></tbody></table></div>
      <div class="ponscan-block"><h3>Деградація ONU</h3><table class="resp-tab" id="ponscan-onu"><thead><tr><th>Рівень</th><th>ONU</th><th>OLT</th><th>PON порт</th><th>Поточний RX</th><th>Середній RX</th><th>Std</th><th>Δ 6h</th><th>Дистанція, км</th><th>Прогноз</th><th>Бал</th></tr></thead><tbody><tr><td colspan="11" class="ponscan-empty">Немає даних</td></tr></tbody></table></div>
      <div class="ponscan-block"><h3>Стан пристроїв</h3><table class="resp-tab" id="ponscan-device"><thead><tr><th>Рівень</th><th>OLT</th><th>Статус</th><th>Темп.</th><th>Ліміт темп.</th><th>Остання перевірка</th><th>Останній RX</th><th>Бал</th><th>Нотатки</th></tr></thead><tbody><tr><td colspan="9" class="ponscan-empty">Немає даних</td></tr></tbody></table></div>
    </section>
  </div>
</div>
<script>
(function(){
  var autoTimer=null;
  var progressTimer=null;
  var progressValue=0;
  var scanToken=0;
  function qs(id){return document.getElementById(id);} 
  function esc(v){return String(v===null||typeof v==='undefined'?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}
  function toNum(v,d){var n=Number(v);return Number.isFinite(n)?n:(typeof d==='number'?d:0);} 
  function fmt(v,d){var n=Number(v);if(!Number.isFinite(n))return '';return n.toFixed(typeof d==='number'?d:2);} 
  function fmtPred(h){var n=Number(h);if(!Number.isFinite(n)||n<=0)return '-';if(n<1)return '<1h';return n.toFixed(1)+'h';}
  function sevClass(s){s=String(s||'').toLowerCase();if(s==='critical')return 'sev-critical';if(s==='high')return 'sev-high';if(s==='medium')return 'sev-medium';if(s==='low')return 'sev-low';return 'sev-info';}
  function sevBadge(s){var k=String(s||'info').toLowerCase();var names={critical:'КРИТИЧНИЙ',high:'ВИСОКИЙ',medium:'СЕРЕДНІЙ',low:'НИЗЬКИЙ',info:'ІНФО'};var x=names[k]||k.toUpperCase();return '<span class="sev '+sevClass(s)+'">'+esc(x)+'</span>';}
  function showError(msg){var box=qs('ponscan-error');box.style.display='block';box.textContent=msg;}
  function clearError(){var box=qs('ponscan-error');box.style.display='none';box.textContent='';}
  function setProgress(value,label){
    var wrap=qs('ponscan-progress'),bar=qs('ponscan-progress-bar'),txt=qs('ponscan-progress-value'),lbl=qs('ponscan-progress-label');
    if(!wrap||!bar||!txt||!lbl){return;}
    var v=Math.max(0,Math.min(100,toNum(value,0)));
    wrap.style.display='block';
    bar.style.width=v.toFixed(0)+'%';
    txt.textContent=v.toFixed(0)+'%';
    if(label){lbl.textContent=label;}
    progressValue=v;
  }
  function startProgress(){
    stopProgress();
    setProgress(5,'Підготовка аналізу...');
    progressTimer=setInterval(function(){
      if(progressValue<88){
        setProgress(progressValue+Math.max(1,(88-progressValue)/10),'Завантаження даних...');
      }
    },250);
  }
  function stopProgress(){
    if(progressTimer){clearInterval(progressTimer);progressTimer=null;}
  }
  function finishProgress(ok){
    stopProgress();
    if(ok){
      setProgress(100,'Готово');
      setTimeout(function(){
        var wrap=qs('ponscan-progress');
        if(wrap){wrap.style.display='none';}
      },450);
    }else{
      setProgress(progressValue<95?95:progressValue,'Помилка завантаження');
    }
  }
  function buildUrl(force){var p=new URLSearchParams();p.set('do','ponscanner');p.set('act','scan');p.set('hours',qs('ponscan-hours').value||'24');p.set('min_samples',qs('ponscan-min-samples').value||'18');p.set('top',qs('ponscan-top').value||'60');if(qs('ponscan-olt-id').value)p.set('olt_id',qs('ponscan-olt-id').value);if(qs('ponscan-onu-id').value)p.set('onu_id',qs('ponscan-onu-id').value);if(force)p.set('force','1');return '/?'+p.toString();}
  function fillSimpleTable(tableId,rows,cols,mapper){var tbody=qs(tableId).querySelector('tbody');if(!rows||!rows.length){tbody.innerHTML='<tr><td colspan="'+cols+'" class="ponscan-empty">Немає даних</td></tr>';return;}var html='';for(var i=0;i<rows.length;i++){var arr=mapper(rows[i]);html+='<tr>';for(var j=0;j<arr.length;j++)html+='<td>'+arr[j]+'</td>';html+='</tr>';}tbody.innerHTML=html;}
  function setMeter(id, txtId, val){var p=Math.max(0,Math.min(100,toNum(val,0)));var bar=qs(id),txt=qs(txtId);if(bar){bar.style.width=p.toFixed(2)+'%';}if(txt){txt.textContent=p.toFixed(2)+'%';}}
  function render(data){var s=data.summary||{},onu=data.onu||{},pp=data.pon_ports||{};qs('pc-olts').textContent=toNum(s.olt_count,0).toFixed(0);qs('pc-onu-visible').textContent=toNum(onu.visible_count,0).toFixed(0);qs('pc-onu-analyzed').textContent=toNum(onu.analyzed_count,0).toFixed(0);qs('pc-problem-ports').textContent=toNum(pp.problem_port_count,0).toFixed(0);qs('pc-degraded-onu').textContent=toNum(pp.degraded_onu_total,0).toFixed(0);qs('pc-critical-high').textContent=(toNum(s.critical_count,0)+toNum(s.high_count,0)).toFixed(0);setMeter('pm-critical','pm-critical-v',toNum(s.critical_rate_percent,0));setMeter('pm-high','pm-high-v',toNum(s.high_rate_percent,0));setMeter('pm-medium','pm-medium-v',toNum(s.medium_rate_percent,0));setMeter('pm-cov','pm-cov-v',toNum(s.onu_coverage_percent,0));var health=Math.max(0,100-(toNum(s.critical_rate_percent,0)*1.4)-(toNum(s.high_rate_percent,0)*0.7)-(toNum(s.medium_rate_percent,0)*0.3));setMeter('pm-quality','pm-quality-v',health);
    fillSimpleTable('ponscan-port',(pp.ports||[]),10,function(r){var conf=(r.localization_confidence==='high'?'висока':(r.localization_confidence==='medium'?'середня':'низька'));return [sevBadge(r.severity),esc(r.olt_place||('#'+(r.olt_id||''))),esc(r.port_name||r.port_id||''),esc(r.degraded_onu+'/'+r.onu_total),esc(fmt(r.degraded_rate_percent,2)),esc(fmt(r.avg_delta_6h,3)),esc(fmt(r.avg_rx_1h,2)),esc(r.cluster_zone||'-'),esc((r.localization||'-')+' ('+conf+')'),esc(fmt(r.score,0))];});
    fillSimpleTable('ponscan-tree',(pp.tree||[]),5,function(r){var top=(r.ports&&r.ports.length)?(r.ports[0].port_name+' ('+r.ports[0].degraded_onu+'/'+r.ports[0].onu_total+')'):'-';return [esc(r.olt_place||('#'+(r.olt_id||''))),esc(r.port_count),esc(r.problem_port_count),esc(r.degraded_onu_total),esc(top)];});
    fillSimpleTable('ponscan-onu',(onu.top||[]),11,function(r){return [sevBadge(r.severity),'<a href="/?do=onu&id='+esc(r.idonu)+'">'+esc(r.idonu)+'</a>',esc(r.olt_place||('#'+(r.olt_id||''))),esc(r.port_name||r.portolt||''),esc(fmt(r.current_rx,2)),esc(fmt(r.avg_rx,2)),esc(fmt(r.std_rx,2)),esc(fmt(r.delta_6h,2)),esc(r.distance_km===null||typeof r.distance_km==='undefined'?'-':fmt(r.distance_km,2)),esc(fmtPred(r.predict_hours)),esc(fmt(r.score,0))];});
    fillSimpleTable('ponscan-device',(data.devices&&data.devices.items)?data.devices.items:[],9,function(r){return [sevBadge(r.severity),esc(r.olt_place||('#'+(r.olt_id||''))),esc(r.status||''),esc(r.temp_now===null||typeof r.temp_now==='undefined'?'-':fmt(r.temp_now,1)),esc(r.temp_limit===null||typeof r.temp_limit==='undefined'?'-':fmt(r.temp_limit,0)),esc(r.last_main_check||'-'),esc(r.last_rx_check||'-'),esc(fmt(r.score,0)),esc(r.notes||'')];});
    qs('ponscan-updated').textContent='Оновлено: '+(data.generated_at||'-');
  }
  function runScan(force){
    var token=++scanToken;
    clearError();
    startProgress();
    setProgress(12,'Формування запиту...');
    fetch(buildUrl(!!force),{credentials:'same-origin'})
      .then(function(r){
        setProgress(45,'Отримання відповіді...');
        return r.text().then(function(t){return {ok:r.ok,status:r.status,text:t};});
      })
      .then(function(resp){
        if(token!==scanToken){return;}
        setProgress(72,'Обробка даних...');
        var data=null;
        try{data=JSON.parse(resp.text);}catch(e){data=null;}
        if(!resp.ok){
          showError('HTTP '+resp.status);
          finishProgress(false);
          return;
        }
        if(!data||data.ok!==true){
          showError((data&&data.message)?data.message:'помилка_сканування');
          finishProgress(false);
          return;
        }
        setProgress(92,'Побудова таблиць...');
        render(data);
        finishProgress(true);
      })
      .catch(function(err){
        if(token!==scanToken){return;}
        showError('Помилка запиту: '+(err&&err.message?err.message:'мережа/сервер'));
        finishProgress(false);
      });
  }
  function toggleAuto(){var btn=qs('ponscan-auto');var active=btn.getAttribute('data-active')==='1';if(active){btn.setAttribute('data-active','0');btn.textContent='Авто: ВИМК';if(autoTimer){clearInterval(autoTimer);autoTimer=null;}}else{btn.setAttribute('data-active','1');btn.textContent='Авто: УВІМК (60с)';if(autoTimer)clearInterval(autoTimer);autoTimer=setInterval(function(){runScan(false);},60000);}}
  qs('ponscan-run').addEventListener('click',function(){runScan(false);});qs('ponscan-refresh').addEventListener('click',function(){runScan(true);});qs('ponscan-auto').addEventListener('click',toggleAuto);runScan(false);
})();
</script>
HTML;

$speedbar = '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>PON Діагностика</span>';
$result = '<div id="onu-speedbar">'.$speedbar.'</div>'.$result_tpl;
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', $result);
$tpl->compile('content');
$tpl->clear();

function ponscanner_read_params(array $src): array {
    return array(
        'hours' => ponscanner_int($src['hours'] ?? 24, 24, 6, 336),
        'min_samples' => ponscanner_int($src['min_samples'] ?? 18, 18, 6, 400),
        'top' => ponscanner_int($src['top'] ?? 60, 60, 5, 300),
        'olt_id' => ponscanner_int($src['olt_id'] ?? 0, 0, 0, 10000000),
        'onu_id' => ponscanner_int($src['onu_id'] ?? 0, 0, 0, 100000000),
        'force' => ponscanner_int($src['force'] ?? 0, 0, 0, 1),
    );
}

function ponscanner_int($value, int $default, int $min, int $max): int {
    $v = filter_var($value, FILTER_VALIDATE_INT);
    if ($v === false) {
        return $default;
    }
    return max($min, min($max, (int)$v));
}

function ponscanner_try_rows(string $sql): array {
    global $db;
    $rows = $db->SimpleWhile($sql);
    if (!is_array($rows)) {
        return array();
    }
    return $rows;
}

function ponscanner_parse_numeric($value): ?float {
    if ($value === null) return null;
    if (is_int($value) || is_float($value)) return (float)$value;
    if (is_string($value)) {
        $value = str_replace(',', '.', trim($value));
        if ($value === '') return null;
        if (is_numeric($value)) return (float)$value;
        if (preg_match('/-?[0-9]+(?:\.[0-9]+)?/', $value, $m)) return (float)$m[0];
    }
    return null;
}

function ponscanner_round_distance_bin(float $distanceKm, float $step = 0.5): float {
    if ($step <= 0) {
        $step = 0.5;
    }
    return floor($distanceKm / $step) * $step;
}

function ponscanner_localize_port_issue(array $degradedDistances, int $degradedOnu, int $onuTotal): array {
    if ($degradedOnu < 2 || empty($degradedDistances)) {
        return array(
            'distance_min_km' => null,
            'distance_max_km' => null,
            'distance_avg_km' => null,
            'distance_spread_km' => null,
            'cluster_zone' => '-',
            'cluster_share_percent' => 0.0,
            'localization' => 'Недостатньо даних',
            'localization_confidence' => 'low'
        );
    }

    sort($degradedDistances);
    $count = count($degradedDistances);
    $min = (float)$degradedDistances[0];
    $max = (float)$degradedDistances[$count - 1];
    $sum = 0.0;
    $bins = array();

    foreach ($degradedDistances as $d) {
        $sum += $d;
        $binStart = ponscanner_round_distance_bin((float)$d, 0.5);
        $binKey = sprintf('%0.1f', $binStart);
        if (!isset($bins[$binKey])) {
            $bins[$binKey] = 0;
        }
        $bins[$binKey]++;
    }

    arsort($bins);
    $bestKey = (string)array_key_first($bins);
    $bestCount = (int)reset($bins);
    $bestStart = (float)$bestKey;
    $bestEnd = $bestStart + 0.5;
    $avg = $sum / max(1, $count);
    $spread = $max - $min;
    $clusterShare = ($bestCount * 100.0) / max(1, $count);
    $degradedRate = ($degradedOnu * 100.0) / max(1, $onuTotal);

    $localization = 'Перевірка спліттерів/муфт';
    $confidence = 'medium';

    if ($clusterShare >= 70.0 || $spread <= 0.8) {
        $localization = 'Проблема в зоні деградації';
        $confidence = 'high';
    } elseif ($spread >= 3.0 && $degradedRate >= 45.0) {
        $localization = 'Проблема ближче до OLT';
        $confidence = 'high';
    } elseif ($spread >= 2.0 && $degradedOnu >= 3) {
        $localization = 'Перевірте магістраль';
        $confidence = 'medium';
    }

    return array(
        'distance_min_km' => round($min, 2),
        'distance_max_km' => round($max, 2),
        'distance_avg_km' => round($avg, 2),
        'distance_spread_km' => round($spread, 2),
        'cluster_zone' => sprintf('%0.1f-%0.1f км', $bestStart, $bestEnd),
        'cluster_share_percent' => round($clusterShare, 2),
        'localization' => $localization,
        'localization_confidence' => $confidence
    );
}

function ponscanner_normalize_port_id($value): string {
    if ($value === null) {
        return '';
    }
    if (is_int($value)) {
        return (string)$value;
    }
    if (is_float($value)) {
        return (string)(int)round($value);
    }
    $s = trim((string)$value);
    if ($s === '') {
        return '';
    }
    if (is_numeric($s)) {
        return (string)(int)round((float)$s);
    }
    return $s;
}

function ponscanner_load_port_name_map(array $oltIds): array {
    if (empty($oltIds)) {
        return array();
    }

    $safeIds = array();
    foreach ($oltIds as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $safeIds[] = $id;
        }
    }
    if (empty($safeIds)) {
        return array();
    }

    $idsSql = implode(',', $safeIds);
    $map = array();

    $rowsPon = ponscanner_try_rows("SELECT oltid, sfpid, idportolt, sort, pon FROM switch_pon WHERE oltid IN ($idsSql)");
    foreach ($rowsPon as $r) {
        $oltId = (int)($r['oltid'] ?? 0);
        if ($oltId <= 0) {
            continue;
        }
        $name = trim((string)($r['pon'] ?? ''));
        if ($name === '') {
            continue;
        }

        $candidates = array(
            ponscanner_normalize_port_id($r['sfpid'] ?? ''),
            ponscanner_normalize_port_id($r['idportolt'] ?? ''),
            ponscanner_normalize_port_id($r['sort'] ?? '')
        );
        foreach ($candidates as $portId) {
            if ($portId === '') {
                continue;
            }
            $key = $oltId . '|' . $portId;
            if (!isset($map[$key]) || $map[$key] === '') {
                $map[$key] = $name;
            }
        }
    }

    $rowsPort = ponscanner_try_rows("SELECT deviceid, llid, nameport FROM switch_port WHERE deviceid IN ($idsSql)");
    foreach ($rowsPort as $r) {
        $oltId = (int)($r['deviceid'] ?? 0);
        if ($oltId <= 0) {
            continue;
        }
        $portId = ponscanner_normalize_port_id($r['llid'] ?? '');
        if ($portId === '') {
            continue;
        }
        $name = trim((string)($r['nameport'] ?? ''));
        if ($name === '') {
            continue;
        }
        $key = $oltId . '|' . $portId;
        if (!isset($map[$key]) || $map[$key] === '') {
            $map[$key] = $name;
        }
    }

    return $map;
}

function ponscanner_age_seconds(?string $date): ?int {
    if (empty($date)) return null;
    $ts = strtotime($date);
    if ($ts === false) return null;
    return max(0, time() - $ts);
}

function ponscanner_severity(float $score): string {
    if ($score >= 85) return 'critical';
    if ($score >= 65) return 'high';
    if ($score >= 45) return 'medium';
    if ($score >= 30) return 'low';
    return 'info';
}

function ponscanner_cache_get($cacheManager, string $key) {
    if (!is_object($cacheManager) || !method_exists($cacheManager, 'get')) {
        return null;
    }
    try {
        return $cacheManager->get($key);
    } catch (Throwable $e) {
        error_log('[PONSCANNER CACHE GET] ' . $e->getMessage());
        return null;
    }
}

function ponscanner_cache_set($cacheManager, string $key, $value, int $ttl): void {
    if (!is_object($cacheManager) || !method_exists($cacheManager, 'set')) {
        return;
    }
    try {
        $cacheManager->set($key, $value, $ttl);
    } catch (Throwable $e) {
        error_log('[PONSCANNER CACHE SET] ' . $e->getMessage());
    }
}

function ponscanner_visible_olts(int $filterOltId = 0): array {
    global $access, $USER;

    $sql = "SELECT id, place, netip, status, updates, updates_rx, timecheck, timechecklast, temp_cpu, model, inf
            FROM switch
            WHERE device = 'olt' AND monitor = 'yes'";
    if ($filterOltId > 0) {
        $sql .= " AND id = " . (int)$filterOltId;
    }
    $sql .= " ORDER BY place ASC";

    $rows = ponscanner_try_rows($sql);
    $isAdmin = isset($USER['class']) && (int)$USER['class'] >= 6;
    $result = array();

    foreach ($rows as $row) {
        $id = (int)($row['id'] ?? 0);
        if ($id <= 0) {
            continue;
        }
        if (!$isAdmin && !$access->get('dev'.$id)) {
            continue;
        }
        $result[$id] = $row;
    }

    return $result;
}

function ponscanner_load_temp_by_olt(array $oltIds): array {
    if (empty($oltIds)) {
        return array();
    }

    $files = array();
    foreach ($oltIds as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $files[] = "'temp_olt_".$id."'";
        }
    }
    if (empty($files)) {
        return array();
    }

    $sql = "SELECT file, data, updated_at, last_processed
            FROM tempdate
            WHERE file IN (" . implode(',', $files) . ")";

    $rows = ponscanner_try_rows($sql);
    $out = array();
    foreach ($rows as $row) {
        $file = (string)($row['file'] ?? '');
        if (!preg_match('/temp_olt_(\d+)/', $file, $m)) {
            continue;
        }
        $oltId = (int)$m[1];
        $decoded = json_decode((string)($row['data'] ?? ''), true);
        if (!is_array($decoded)) {
            $decoded = array();
        }
        $out[$oltId] = array(
            'last' => ponscanner_parse_numeric($decoded['last'] ?? null),
            'prev' => ponscanner_parse_numeric($decoded['prev'] ?? null),
            'status' => (string)($decoded['status'] ?? ''),
            'updated_at' => (string)($row['updated_at'] ?? ''),
            'last_processed' => (string)($row['last_processed'] ?? '')
        );
    }

    return $out;
}

function ponscanner_analyze_onu(array $oltMap, array $params): array {
    if (empty($oltMap)) {
        return array(
            'visible_count' => 0,
            'analyzed_count' => 0,
            'alerts' => array(),
            'top' => array(),
            'by_olt' => array()
        );
    }

    $oltIds = array_map('intval', array_keys($oltMap));
    $idsSql = implode(',', $oltIds);
    $onuFilterSql = ($params['onu_id'] > 0) ? (" AND o.idonu = " . (int)$params['onu_id']) : '';

    $sqlCurrent = "SELECT o.idonu, o.olt, o.portolt, o.inface, o.status, o.rx, o.lastrx, o.dist, o.reason, o.name, o.mac, o.sn
                   FROM onus o
                   WHERE o.olt IN ($idsSql)" . $onuFilterSql;

    $currentRows = ponscanner_try_rows($sqlCurrent);
    $currentMap = array();
    foreach ($currentRows as $row) {
        $onuId = (int)($row['idonu'] ?? 0);
        if ($onuId > 0) {
            $currentMap[$onuId] = $row;
        }
    }

    $hours = (int)$params['hours'];
    $minSamples = (int)$params['min_samples'];

    $sqlHistory = "SELECT
                      h.onu AS onu_id,
                      COUNT(*) AS cnt,
                      AVG(CAST(h.signal AS DECIMAL(10,2))) AS avg_rx,
                      STDDEV_POP(CAST(h.signal AS DECIMAL(10,2))) AS std_rx,
                      MIN(CAST(h.signal AS DECIMAL(10,2))) AS min_rx,
                      MAX(CAST(h.signal AS DECIMAL(10,2))) AS max_rx,
                      AVG(CASE WHEN h.datetime >= DATE_SUB(NOW(), INTERVAL 6 HOUR) THEN CAST(h.signal AS DECIMAL(10,2)) END) AS avg_6h,
                      AVG(CASE WHEN h.datetime < DATE_SUB(NOW(), INTERVAL 6 HOUR) AND h.datetime >= DATE_SUB(NOW(), INTERVAL 12 HOUR) THEN CAST(h.signal AS DECIMAL(10,2)) END) AS avg_prev6h,
                      MAX(h.datetime) AS last_sample
                   FROM historysignal h
                   INNER JOIN onus o ON o.idonu = h.onu
                   WHERE o.olt IN ($idsSql)
                     $onuFilterSql
                     AND h.datetime >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
                     AND h.signal REGEXP '^-?[0-9]+(\\\\.[0-9]+)?$'
                     AND CAST(h.signal AS DECIMAL(10,2)) BETWEEN -45 AND -5
                   GROUP BY h.onu
                   HAVING cnt >= $minSamples";

    $historyRows = ponscanner_try_rows($sqlHistory);
    $alerts = array();
    $byOlt = array();

    foreach ($historyRows as $row) {
        $onuId = (int)($row['onu_id'] ?? 0);
        if ($onuId <= 0 || !isset($currentMap[$onuId])) {
            continue;
        }

        $cur = $currentMap[$onuId];
        $oltId = (int)($cur['olt'] ?? 0);
        $avgRx = ponscanner_parse_numeric($row['avg_rx'] ?? null);
        $stdRx = ponscanner_parse_numeric($row['std_rx'] ?? null);
        $curRx = ponscanner_parse_numeric($cur['rx'] ?? null);
        $avg6h = ponscanner_parse_numeric($row['avg_6h'] ?? null);
        $avgPrev6h = ponscanner_parse_numeric($row['avg_prev6h'] ?? null);

        if ($avgRx === null || $curRx === null) {
            continue;
        }

        $std = ($stdRx !== null && $stdRx > 0.05) ? $stdRx : 0.15;
        $z = ($curRx - $avgRx) / $std;
        $delta6h = ($avg6h !== null && $avgPrev6h !== null) ? ($avg6h - $avgPrev6h) : null;
        $predictHours = null;

        if ($avg6h !== null) {
            if ($avg6h <= -24) {
                $predictHours = 0.0;
            } elseif ($delta6h !== null && $delta6h < -0.05) {
                $slopePerHour = $delta6h / 6.0;
                if ($slopePerHour < -0.0001) {
                    $pred = (-24 - $avg6h) / $slopePerHour;
                    if ($pred > 0 && $pred <= 720) {
                        $predictHours = round($pred, 1);
                    }
                }
            }
        }

        $score = 0.0;
        $details = array();

        if ($curRx <= -27) {
            $score += 40; $details[] = 'current_rx<=-27';
        } elseif ($curRx <= -24) {
            $score += 25; $details[] = 'current_rx<=-24';
        }

        if ($delta6h !== null) {
            if ($delta6h <= -1.2) {
                $score += 25; $details[] = 'delta6h<=-1.2';
            } elseif ($delta6h <= -0.7) {
                $score += 15; $details[] = 'delta6h<=-0.7';
            } elseif ($delta6h <= -0.35) {
                $score += 8; $details[] = 'delta6h<=-0.35';
            }
        }

        if ($z <= -3.0) {
            $score += 18; $details[] = 'z<=-3';
        } elseif ($z <= -2.0) {
            $score += 10; $details[] = 'z<=-2';
        }

        if ($std >= 1.6) {
            $score += 14; $details[] = 'std>=1.6';
        } elseif ($std >= 1.0) {
            $score += 8; $details[] = 'std>=1.0';
        }

        if ((int)($cur['status'] ?? 0) === 2) {
            $score += 12; $details[] = 'onu_offline';
        }

        if ($predictHours !== null) {
            if ($predictHours <= 6) {
                $score += 30; $details[] = 'predict<=6h';
            } elseif ($predictHours <= 24) {
                $score += 20; $details[] = 'predict<=24h';
            } elseif ($predictHours <= 48) {
                $score += 10; $details[] = 'predict<=48h';
            }
        }

        if ($score < 30) {
            continue;
        }

        $severity = ponscanner_severity($score);
        $oltPlace = isset($oltMap[$oltId]['place']) ? (string)$oltMap[$oltId]['place'] : ('#'.$oltId);
        $entityKey = (string)($cur['mac'] ?: ($cur['sn'] ?: ('ONU '.$onuId)));
        $trendText = ($delta6h === null) ? '-' : sprintf('%0.2f dB/6h', $delta6h);
        $predictText = ($predictHours === null) ? '-' : (($predictHours <= 0) ? 'already<-24' : ($predictHours . 'h'));

        $item = array(
            'type' => 'onu_signal',
            'severity' => $severity,
            'score' => round($score, 2),
            'idonu' => $onuId,
            'olt_id' => $oltId,
            'olt_place' => $oltPlace,
            'portolt' => (string)($cur['portolt'] ?? ''),
            'entity' => $entityKey,
            'current_value' => sprintf('%0.2f', $curRx),
            'baseline_value' => sprintf('%0.2f', $avgRx),
            'trend' => $trendText,
            'predict' => $predictText,
            'details' => implode(', ', $details),
            'current_rx' => round($curRx, 2),
            'avg_rx' => round($avgRx, 2),
            'std_rx' => round($std, 3),
            'delta_6h' => ($delta6h === null ? null : round($delta6h, 3)),
            'predict_hours' => $predictHours,
            'distance_km' => ponscanner_parse_numeric($cur['dist'] ?? null)
        );
        $alerts[] = $item;

        if (!isset($byOlt[$oltId])) {
            $byOlt[$oltId] = array('score' => 0.0, 'alerts' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0);
        }
        $byOlt[$oltId]['score'] += $score;
        $byOlt[$oltId]['alerts']++;
        if ($severity === 'critical') $byOlt[$oltId]['critical']++;
        if ($severity === 'high') $byOlt[$oltId]['high']++;
        if ($severity === 'medium') $byOlt[$oltId]['medium']++;
    }

    usort($alerts, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });

    return array(
        'visible_count' => count($currentMap),
        'analyzed_count' => count($historyRows),
        'alerts' => $alerts,
        'top' => array_slice($alerts, 0, (int)$params['top']),
        'by_olt' => $byOlt
    );
}

function ponscanner_analyze_sfp(array $oltMap, array $params): array {
    if (empty($oltMap)) {
        return array('analyzed_count' => 0, 'alerts' => array(), 'top' => array(), 'by_olt' => array());
    }

    $oltIds = array_map('intval', array_keys($oltMap));
    $idsSql = implode(',', $oltIds);
    $hours = (int)$params['hours'];
    $rxExpr = "CAST(REPLACE(ss.rx, ',', '.') AS DECIMAL(10,2))";

    $sql = "SELECT
              ss.sfpid,
              sp.id AS port_id,
              sp.deviceid AS olt_id,
              sp.nameport,
              sp.llid,
              s.place AS olt_place,
              COUNT(*) AS cnt,
              AVG($rxExpr) AS avg_rx,
              STDDEV_POP($rxExpr) AS std_rx,
              MIN($rxExpr) AS min_rx,
              MAX($rxExpr) AS max_rx,
              AVG(CASE WHEN ss.datetime >= DATE_SUB(NOW(), INTERVAL 2 HOUR) THEN $rxExpr END) AS avg_2h,
              AVG(CASE WHEN ss.datetime < DATE_SUB(NOW(), INTERVAL 2 HOUR) AND ss.datetime >= DATE_SUB(NOW(), INTERVAL 4 HOUR) THEN $rxExpr END) AS avg_prev2h,
              MAX(ss.datetime) AS last_sample
            FROM signal_sfp ss
            INNER JOIN switch_port sp ON sp.id = ss.sfpid
            INNER JOIN switch s ON s.id = sp.deviceid
            WHERE sp.deviceid IN ($idsSql)
              AND ss.datetime >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
              AND ss.rx REGEXP '[-+]?[0-9]+([.,][0-9]+)?'
            GROUP BY ss.sfpid
            HAVING cnt >= 6";

    $rows = ponscanner_try_rows($sql);
    $alerts = array();
    $ranked = array();
    $byOlt = array();

    foreach ($rows as $row) {
        $oltId = (int)($row['olt_id'] ?? 0);
        $avgRx = ponscanner_parse_numeric($row['avg_rx'] ?? null);
        $stdRx = ponscanner_parse_numeric($row['std_rx'] ?? null);
        $avg2h = ponscanner_parse_numeric($row['avg_2h'] ?? null);
        $avgPrev2h = ponscanner_parse_numeric($row['avg_prev2h'] ?? null);
        $currentRx = ($avg2h !== null) ? $avg2h : $avgRx;
        if ($currentRx === null || $avgRx === null) {
            continue;
        }

        $std = ($stdRx !== null && $stdRx > 0.05) ? $stdRx : 0.15;
        $z = ($currentRx - $avgRx) / $std;
        $delta2h = ($avg2h !== null && $avgPrev2h !== null) ? ($avg2h - $avgPrev2h) : null;
        $ageSec = ponscanner_age_seconds((string)($row['last_sample'] ?? ''));
        $ageMin = ($ageSec === null) ? null : (int)floor($ageSec / 60);

        $score = 0.0;
        $details = array();

        if ($currentRx <= -24) {
            $score += 45; $details[] = 'rx<=-24';
        } elseif ($currentRx <= -20) {
            $score += 30; $details[] = 'rx<=-20';
        } elseif ($currentRx <= -16) {
            $score += 15; $details[] = 'rx<=-16';
        } elseif ($currentRx < 2) {
            $score += 45; $details[] = 'rx<2';
        } elseif ($currentRx < 4) {
            $score += 30; $details[] = 'rx<4';
        } elseif ($currentRx < 6) {
            $score += 15; $details[] = 'rx<6';
        }

        if ($delta2h !== null) {
            if ($delta2h <= -1.0) {
                $score += 25; $details[] = 'delta2h<=-1.0';
            } elseif ($delta2h <= -0.5) {
                $score += 15; $details[] = 'delta2h<=-0.5';
            } elseif ($delta2h <= -0.25) {
                $score += 8; $details[] = 'delta2h<=-0.25';
            }
        }

        if ($z <= -2.5) {
            $score += 15; $details[] = 'z<=-2.5';
        } elseif ($z <= -2.0) {
            $score += 8; $details[] = 'z<=-2.0';
        }

        if ($std >= 1.2) {
            $score += 10; $details[] = 'std>=1.2';
        }

        if ($ageMin !== null) {
            if ($ageMin > 90) {
                $score += 30; $details[] = 'sample_age>90m';
            } elseif ($ageMin > 30) {
                $score += 20; $details[] = 'sample_age>30m';
            }
        }

        $severity = ponscanner_severity($score);
        $portName = trim((string)($row['nameport'] ?? ''));
        if ($portName === '') {
            $portName = 'llid:'.(string)($row['llid'] ?? '');
        }
        $item = array(
            'type' => 'sfp_signal',
            'severity' => $severity,
            'score' => round($score, 2),
            'olt_id' => $oltId,
            'olt_place' => (string)($row['olt_place'] ?? ('#'.$oltId)),
            'port_id' => (int)($row['port_id'] ?? 0),
            'port_name' => $portName,
            'current_value' => sprintf('%0.2f', $currentRx),
            'baseline_value' => sprintf('%0.2f', $avgRx),
            'trend' => ($delta2h === null ? '-' : sprintf('%0.2f dB/2h', $delta2h)),
            'predict' => '-',
            'details' => implode(', ', $details),
            'current_rx' => round($currentRx, 2),
            'avg_rx' => round($avgRx, 2),
            'std_rx' => round($std, 3),
            'delta_2h' => ($delta2h === null ? null : round($delta2h, 3)),
            'last_sample' => (string)($row['last_sample'] ?? '')
        );
        $ranked[] = $item;

        if ($score < 30) {
            continue;
        }
        $alerts[] = $item;

        if (!isset($byOlt[$oltId])) {
            $byOlt[$oltId] = array('score' => 0.0, 'alerts' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0);
        }
        $byOlt[$oltId]['score'] += $score;
        $byOlt[$oltId]['alerts']++;
        if ($severity === 'critical') $byOlt[$oltId]['critical']++;
        if ($severity === 'high') $byOlt[$oltId]['high']++;
        if ($severity === 'medium') $byOlt[$oltId]['medium']++;
    }

    usort($alerts, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });
    usort($ranked, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });

    return array(
        'analyzed_count' => count($rows),
        'alerts' => $alerts,
        'top' => array_slice($ranked, 0, (int)$params['top']),
        'by_olt' => $byOlt
    );
}

function ponscanner_analyze_devices(array $oltMap): array {
    if (empty($oltMap)) {
        return array('items' => array(), 'alerts' => array(), 'by_olt' => array());
    }

    $tempByOlt = ponscanner_load_temp_by_olt(array_keys($oltMap));
    $items = array();
    $alerts = array();
    $byOlt = array();

    foreach ($oltMap as $oltId => $olt) {
        $score = 0.0;
        $notes = array();

        $status = (string)($olt['status'] ?? '');
        if ($status !== 'yes') {
            $score += 35;
            $notes[] = 'switch_status!=' . $status;
        }

        $ageMain = ponscanner_age_seconds((string)($olt['updates'] ?? ''));
        $ageRx = ponscanner_age_seconds((string)($olt['updates_rx'] ?? ''));

        if ($ageMain !== null) {
            if ($ageMain > 1800) {
                $score += 25; $notes[] = 'updates>30m';
            } elseif ($ageMain > 900) {
                $score += 12; $notes[] = 'updates>15m';
            }
        }
        if ($ageRx !== null) {
            if ($ageRx > 2400) {
                $score += 25; $notes[] = 'updates_rx>40m';
            } elseif ($ageRx > 1200) {
                $score += 12; $notes[] = 'updates_rx>20m';
            }
        }

        $tempNow = null;
        $tempPrev = null;
        $tempState = '';
        if (isset($tempByOlt[$oltId])) {
            $tempNow = $tempByOlt[$oltId]['last'];
            $tempPrev = $tempByOlt[$oltId]['prev'];
            $tempState = (string)$tempByOlt[$oltId]['status'];
        }

        $tempLimit = (int)($olt['temp_cpu'] ?? 60);
        if ($tempLimit < 35) {
            $tempLimit = 60;
        }

        if ($tempNow !== null) {
            if ($tempNow >= ($tempLimit + 8)) {
                $score += 40; $notes[] = 'temp>limit+8';
            } elseif ($tempNow >= $tempLimit) {
                $score += 20; $notes[] = 'temp>=limit';
            }
            if ($tempPrev !== null && ($tempNow - $tempPrev) >= 2.0) {
                $score += 10; $notes[] = 'temp_rising';
            }
        }
        if ($tempState === 'critical') {
            $score += 12; $notes[] = 'temp_state=critical';
        }

        $severity = ponscanner_severity($score);
        $item = array(
            'type' => 'device_health',
            'severity' => $severity,
            'score' => round($score, 2),
            'olt_id' => (int)$oltId,
            'olt_place' => (string)($olt['place'] ?? ('#'.$oltId)),
            'status' => $status,
            'temp_now' => ($tempNow === null ? null : round($tempNow, 2)),
            'temp_limit' => $tempLimit,
            'last_main_check' => (string)($olt['updates'] ?? ''),
            'last_rx_check' => (string)($olt['updates_rx'] ?? ''),
            'notes' => implode(', ', $notes),
            'entity' => (string)($olt['place'] ?? ('OLT '.$oltId)),
            'current_value' => ($tempNow === null ? '-' : (string)round($tempNow, 2)),
            'baseline_value' => 'temp_limit:'.$tempLimit,
            'trend' => ($tempNow !== null && $tempPrev !== null ? sprintf('%0.2f', ($tempNow - $tempPrev)) : '-'),
            'predict' => '-',
            'details' => implode(', ', $notes)
        );
        $items[] = $item;

        if ($score >= 30) {
            $alerts[] = $item;
            $byOlt[$oltId] = $byOlt[$oltId] ?? array('score' => 0.0, 'alerts' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0);
            $byOlt[$oltId]['score'] += $score;
            $byOlt[$oltId]['alerts']++;
            if ($severity === 'critical') $byOlt[$oltId]['critical']++;
            if ($severity === 'high') $byOlt[$oltId]['high']++;
            if ($severity === 'medium') $byOlt[$oltId]['medium']++;
        }
    }

    usort($items, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });
    usort($alerts, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });

    return array(
        'items' => $items,
        'alerts' => $alerts,
        'by_olt' => $byOlt
    );
}

function ponscanner_analyze_events(array $oltMap): array {
    if (empty($oltMap)) {
        return array('alerts' => array(), 'by_olt' => array());
    }

    $oltIds = array_map('intval', array_keys($oltMap));
    $idsSql = implode(',', $oltIds);
    $sql = "SELECT
              o.olt AS olt_id,
              SUM(CASE WHEN m.added >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND m.status = 2 THEN 1 ELSE 0 END) AS down_24h,
              SUM(CASE WHEN m.added < DATE_SUB(NOW(), INTERVAL 24 HOUR) AND m.added >= DATE_SUB(NOW(), INTERVAL 48 HOUR) AND m.status = 2 THEN 1 ELSE 0 END) AS down_prev24h,
              SUM(CASE WHEN m.added >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND m.status = 1 THEN 1 ELSE 0 END) AS up_24h
            FROM onus_monitor m
            INNER JOIN onus o ON o.idonu = m.idonu
            WHERE o.olt IN ($idsSql)
              AND m.added >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
            GROUP BY o.olt";

    $rows = ponscanner_try_rows($sql);
    $alerts = array();
    $byOlt = array();

    foreach ($rows as $row) {
        $oltId = (int)($row['olt_id'] ?? 0);
        $down24 = (int)($row['down_24h'] ?? 0);
        $downPrev = (int)($row['down_prev24h'] ?? 0);
        $up24 = (int)($row['up_24h'] ?? 0);

        $score = 0.0;
        $details = array();
        $ratio = ($downPrev > 0) ? ($down24 / $downPrev) : ($down24 > 0 ? 99.0 : 0.0);

        if ($down24 >= 10 && $ratio >= 1.8) {
            $score += 70; $details[] = 'down_spike>=1.8x';
        } elseif ($down24 >= 5 && $ratio >= 1.4) {
            $score += 45; $details[] = 'down_spike>=1.4x';
        } elseif ($down24 >= 3 && $ratio >= 1.2) {
            $score += 30; $details[] = 'down_spike>=1.2x';
        }

        if ($up24 < $down24 && $down24 >= 4) {
            $score += 12; $details[] = 'recover_rate_low';
        }

        if ($score < 30) {
            continue;
        }

        $severity = ponscanner_severity($score);
        $oltPlace = isset($oltMap[$oltId]['place']) ? (string)$oltMap[$oltId]['place'] : ('#'.$oltId);

        $item = array(
            'type' => 'event_anomaly',
            'severity' => $severity,
            'score' => round($score, 2),
            'olt_id' => $oltId,
            'olt_place' => $oltPlace,
            'entity' => $oltPlace,
            'current_value' => 'down24='.$down24,
            'baseline_value' => 'downPrev24='.$downPrev,
            'trend' => 'ratio='.round($ratio, 2),
            'predict' => '-',
            'details' => implode(', ', $details)
        );
        $alerts[] = $item;

        $byOlt[$oltId] = $byOlt[$oltId] ?? array('score' => 0.0, 'alerts' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0);
        $byOlt[$oltId]['score'] += $score;
        $byOlt[$oltId]['alerts']++;
        if ($severity === 'critical') $byOlt[$oltId]['critical']++;
        if ($severity === 'high') $byOlt[$oltId]['high']++;
        if ($severity === 'medium') $byOlt[$oltId]['medium']++;
    }

    usort($alerts, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });

    return array(
        'alerts' => $alerts,
        'by_olt' => $byOlt
    );
}

function ponscanner_merge_olt_scores(array $oltMap, array ...$parts): array {
    $merge = array();
    foreach ($parts as $set) {
        foreach ($set as $oltId => $row) {
            $oltId = (int)$oltId;
            if (!isset($merge[$oltId])) {
                $merge[$oltId] = array(
                    'olt_id' => $oltId,
                    'olt_place' => isset($oltMap[$oltId]['place']) ? (string)$oltMap[$oltId]['place'] : ('#'.$oltId),
                    'score_total' => 0.0,
                    'alert_count' => 0,
                    'critical_count' => 0,
                    'high_count' => 0,
                    'medium_count' => 0
                );
            }
            $merge[$oltId]['score_total'] += (float)($row['score'] ?? 0);
            $merge[$oltId]['alert_count'] += (int)($row['alerts'] ?? 0);
            $merge[$oltId]['critical_count'] += (int)($row['critical'] ?? 0);
            $merge[$oltId]['high_count'] += (int)($row['high'] ?? 0);
            $merge[$oltId]['medium_count'] += (int)($row['medium'] ?? 0);
        }
    }

    $list = array_values($merge);
    usort($list, static function (array $a, array $b): int {
        return ($b['score_total'] <=> $a['score_total']) ?: ($b['alert_count'] <=> $a['alert_count']);
    });
    return $list;
}

function ponscanner_build_status_history(array $oltMap, array $params): array {
    if (empty($oltMap)) {
        return array();
    }

    $oltIds = array_map('intval', array_keys($oltMap));
    $idsSql = implode(',', $oltIds);
    $onuFilterSql = ((int)$params['onu_id'] > 0) ? (" AND o.idonu = " . (int)$params['onu_id']) : '';
    $hours = max(6, (int)$params['hours']);

    $sql = "SELECT
              DATE_FORMAT(m.added, '%Y-%m-%d %H:00:00') AS bucket,
              SUM(CASE WHEN m.status = 1 THEN 1 ELSE 0 END) AS online_count,
              SUM(CASE WHEN m.status = 2 THEN 1 ELSE 0 END) AS offline_count,
              COUNT(*) AS total_count
            FROM onus_monitor m
            INNER JOIN onus o ON o.idonu = m.idonu
            WHERE o.olt IN ($idsSql)
              $onuFilterSql
              AND m.added >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
            GROUP BY bucket
            ORDER BY bucket ASC";

    $rows = ponscanner_try_rows($sql);
    $out = array();
    foreach ($rows as $r) {
        $online = (int)($r['online_count'] ?? 0);
        $offline = (int)($r['offline_count'] ?? 0);
        $total = max(1, (int)($r['total_count'] ?? 0));
        $out[] = array(
            'bucket' => (string)($r['bucket'] ?? ''),
            'online_count' => $online,
            'offline_count' => $offline,
            'total_count' => (int)($r['total_count'] ?? 0),
            'offline_rate_percent' => round(($offline * 100.0) / $total, 2)
        );
    }

    return $out;
}

function ponscanner_build_signal_history(array $oltMap, array $params): array {
    if (empty($oltMap)) {
        return array();
    }

    $oltIds = array_map('intval', array_keys($oltMap));
    $idsSql = implode(',', $oltIds);
    $onuFilterSql = ((int)$params['onu_id'] > 0) ? (" AND o.idonu = " . (int)$params['onu_id']) : '';
    $hours = max(6, (int)$params['hours']);

    $sql = "SELECT
              DATE_FORMAT(h.datetime, '%Y-%m-%d %H:00:00') AS bucket,
              AVG(CAST(h.signal AS DECIMAL(10,2))) AS avg_rx,
              MIN(CAST(h.signal AS DECIMAL(10,2))) AS min_rx,
              MAX(CAST(h.signal AS DECIMAL(10,2))) AS max_rx,
              COUNT(*) AS samples,
              SUM(CASE WHEN CAST(h.signal AS DECIMAL(10,2)) <= -27 THEN 1 ELSE 0 END) AS bad_count
            FROM historysignal h
            INNER JOIN onus o ON o.idonu = h.onu
            WHERE o.olt IN ($idsSql)
              $onuFilterSql
              AND h.datetime >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
              AND h.signal REGEXP '^-?[0-9]+(\\\\.[0-9]+)?$'
              AND CAST(h.signal AS DECIMAL(10,2)) BETWEEN -45 AND -5
            GROUP BY bucket
            ORDER BY bucket ASC";

    $rows = ponscanner_try_rows($sql);
    $out = array();
    foreach ($rows as $r) {
        $samples = max(1, (int)($r['samples'] ?? 0));
        $bad = (int)($r['bad_count'] ?? 0);
        $out[] = array(
            'bucket' => (string)($r['bucket'] ?? ''),
            'avg_rx' => round((float)($r['avg_rx'] ?? 0), 2),
            'min_rx' => round((float)($r['min_rx'] ?? 0), 2),
            'max_rx' => round((float)($r['max_rx'] ?? 0), 2),
            'samples' => (int)($r['samples'] ?? 0),
            'bad_rate_percent' => round(($bad * 100.0) / $samples, 2)
        );
    }

    return $out;
}

function ponscanner_analyze_pon_ports(array $oltMap, array $params): array {
    if (empty($oltMap)) {
        return array(
            'ports' => array(),
            'tree' => array(),
            'problem_port_count' => 0,
            'degraded_onu_total' => 0,
            'alerts' => array()
        );
    }

    $oltIds = array_map('intval', array_keys($oltMap));
    $idsSql = implode(',', $oltIds);
    $onuFilterSql = ((int)$params['onu_id'] > 0) ? (" AND o.idonu = " . (int)$params['onu_id']) : '';
    $hours = max(12, (int)$params['hours']);

    $portNameMap = ponscanner_load_port_name_map($oltIds);

    $sql = "SELECT
              o.olt AS olt_id,
              o.portolt AS port_id,
              o.idonu AS onu_id,
              o.dist AS onu_dist,
              sw.place AS olt_place,
              AVG(CASE WHEN h.datetime >= DATE_SUB(NOW(), INTERVAL 6 HOUR) THEN CAST(h.signal AS DECIMAL(10,2)) END) AS avg_6h,
              AVG(CASE WHEN h.datetime < DATE_SUB(NOW(), INTERVAL 6 HOUR) AND h.datetime >= DATE_SUB(NOW(), INTERVAL 12 HOUR) THEN CAST(h.signal AS DECIMAL(10,2)) END) AS avg_prev6h,
              AVG(CASE WHEN h.datetime >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN CAST(h.signal AS DECIMAL(10,2)) END) AS avg_1h,
              MAX(h.datetime) AS last_sample
            FROM onus o
            LEFT JOIN switch sw ON sw.id = o.olt
            LEFT JOIN historysignal h
              ON h.onu = o.idonu
              AND h.datetime >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
              AND h.signal REGEXP '^-?[0-9]+(\\\\.[0-9]+)?$'
              AND CAST(h.signal AS DECIMAL(10,2)) BETWEEN -45 AND -5
            WHERE o.olt IN ($idsSql)
              $onuFilterSql
            GROUP BY o.olt, o.portolt, o.idonu, sw.place";

    $rows = ponscanner_try_rows($sql);
    $ports = array();

    foreach ($rows as $r) {
        $oltId = (int)($r['olt_id'] ?? 0);
        $portId = ponscanner_normalize_port_id($r['port_id'] ?? '');
        $onuId = (int)($r['onu_id'] ?? 0);
        if ($oltId <= 0 || $portId === '' || $onuId <= 0) {
            continue;
        }

        $key = $oltId . '|' . $portId;
        if (!isset($ports[$key])) {
            $ports[$key] = array(
                'olt_id' => $oltId,
                'olt_place' => trim((string)($r['olt_place'] ?? '')) ?: ('#'.$oltId),
                'port_id' => $portId,
                'onu_total' => 0,
                'degraded_onu' => 0,
                'severe_onu' => 0,
                'stable_onu' => 0,
                'avg_delta_6h' => 0.0,
                'worst_delta_6h' => 0.0,
                'avg_rx_1h' => 0.0,
                'last_sample' => '',
                'degraded_distances' => array(),
                'score' => 0.0,
                'severity' => 'info'
            );
        }

        $avg6 = ponscanner_parse_numeric($r['avg_6h'] ?? null);
        $avgPrev6 = ponscanner_parse_numeric($r['avg_prev6h'] ?? null);
        $avg1 = ponscanner_parse_numeric($r['avg_1h'] ?? null);
        $delta = null;
        if ($avg6 !== null && $avgPrev6 !== null) {
            $delta = $avg6 - $avgPrev6;
        }

        $ports[$key]['onu_total']++;
        if ($avg1 !== null) {
            $ports[$key]['avg_rx_1h'] += $avg1;
        }
        if (!empty($r['last_sample'])) {
            $ports[$key]['last_sample'] = max((string)$ports[$key]['last_sample'], (string)$r['last_sample']);
        }

        $isDegraded = false;
        $isSevere = false;
        if ($delta !== null && $delta <= -0.7) {
            $isDegraded = true;
        }
        if ($delta !== null && $delta <= -1.2) {
            $isSevere = true;
        }
        if ($avg6 !== null && $avg6 <= -25.0) {
            $isDegraded = true;
        }
        if ($avg6 !== null && $avg6 <= -27.0) {
            $isSevere = true;
        }

        if ($delta !== null) {
            $ports[$key]['avg_delta_6h'] += $delta;
            if ($delta < $ports[$key]['worst_delta_6h']) {
                $ports[$key]['worst_delta_6h'] = $delta;
            }
        }

        if ($isSevere) {
            $ports[$key]['severe_onu']++;
        }
        if ($isDegraded) {
            $ports[$key]['degraded_onu']++;
            $d = ponscanner_parse_numeric($r['onu_dist'] ?? null);
            if ($d !== null && $d >= 0.0) {
                $ports[$key]['degraded_distances'][] = $d;
            }
        } else {
            $ports[$key]['stable_onu']++;
        }
    }

    $list = array();
    $alerts = array();
    $treeMap = array();
    $problemPortCount = 0;
    $degradedOnuTotal = 0;

    foreach ($ports as $key => $p) {
        $total = max(1, (int)$p['onu_total']);
        $degradedRate = ((float)$p['degraded_onu'] * 100.0) / $total;
        $severeRate = ((float)$p['severe_onu'] * 100.0) / $total;
        $avgDelta = $p['avg_delta_6h'] / $total;
        $avgRx1h = $p['avg_rx_1h'] / $total;

        $score = 0.0;
        if ($p['degraded_onu'] >= 2) $score += 25;
        if ($p['degraded_onu'] >= 4) $score += 18;
        if ($degradedRate >= 30) $score += 22;
        if ($degradedRate >= 50) $score += 18;
        if ($p['severe_onu'] >= 1) $score += 10;
        if ($severeRate >= 20) $score += 12;
        if ($avgDelta <= -0.7) $score += 12;
        if ($avgDelta <= -1.2) $score += 12;
        if ($avgRx1h <= -24) $score += 12;
        if ($avgRx1h <= -26) $score += 12;

        $severity = ponscanner_severity($score);
        $portNameKey = (int)$p['olt_id'] . '|' . (string)$p['port_id'];
        $portName = isset($portNameMap[$portNameKey]) ? trim((string)$portNameMap[$portNameKey]) : '';
        if ($portName === '') {
            $portName = 'PON ' . (string)$p['port_id'];
        }
        $localization = ponscanner_localize_port_issue(
            (array)($p['degraded_distances'] ?? array()),
            (int)$p['degraded_onu'],
            (int)$p['onu_total']
        );

        $item = array(
            'olt_id' => (int)$p['olt_id'],
            'olt_place' => (string)$p['olt_place'],
            'port_id' => (string)$p['port_id'],
            'port_name' => $portName,
            'onu_total' => (int)$p['onu_total'],
            'degraded_onu' => (int)$p['degraded_onu'],
            'severe_onu' => (int)$p['severe_onu'],
            'stable_onu' => (int)$p['stable_onu'],
            'degraded_rate_percent' => round($degradedRate, 2),
            'severe_rate_percent' => round($severeRate, 2),
            'avg_delta_6h' => round($avgDelta, 3),
            'worst_delta_6h' => round((float)$p['worst_delta_6h'], 3),
            'avg_rx_1h' => round($avgRx1h, 2),
            'last_sample' => (string)$p['last_sample'],
            'distance_min_km' => $localization['distance_min_km'],
            'distance_max_km' => $localization['distance_max_km'],
            'distance_avg_km' => $localization['distance_avg_km'],
            'distance_spread_km' => $localization['distance_spread_km'],
            'cluster_zone' => $localization['cluster_zone'],
            'cluster_share_percent' => $localization['cluster_share_percent'],
            'localization' => $localization['localization'],
            'localization_confidence' => $localization['localization_confidence'],
            'score' => round($score, 2),
            'severity' => $severity
        );
        $list[] = $item;

        if ($item['degraded_onu'] >= 2 || $item['score'] >= 30) {
            $problemPortCount++;
            $degradedOnuTotal += (int)$item['degraded_onu'];
            $alerts[] = array(
                'type' => 'pon_port_degradation',
                'severity' => $severity,
                'score' => $item['score'],
                'olt_id' => $item['olt_id'],
                'olt_place' => $item['olt_place'],
                'entity' => $item['olt_place'] . ' / ' . $item['port_name'],
                'current_value' => 'degraded=' . $item['degraded_onu'] . '/' . $item['onu_total'],
                'baseline_value' => 'stable=' . $item['stable_onu'],
                'trend' => 'avg_delta_6h=' . $item['avg_delta_6h'],
                'predict' => ($item['degraded_rate_percent'] >= 50 ? 'critical_segment' : '-'),
                'details' => 'avg_rx_1h=' . $item['avg_rx_1h'] . ', severe=' . $item['severe_onu'] . ', zone=' . $item['cluster_zone']
            );
        }

        $oltId = (int)$item['olt_id'];
        if (!isset($treeMap[$oltId])) {
            $treeMap[$oltId] = array(
                'olt_id' => $oltId,
                'olt_place' => $item['olt_place'],
                'port_count' => 0,
                'problem_port_count' => 0,
                'degraded_onu_total' => 0,
                'ports' => array()
            );
        }
        $treeMap[$oltId]['port_count']++;
        if ($item['degraded_onu'] >= 2 || $item['score'] >= 30) {
            $treeMap[$oltId]['problem_port_count']++;
            $treeMap[$oltId]['degraded_onu_total'] += (int)$item['degraded_onu'];
        }
        $treeMap[$oltId]['ports'][] = $item;
    }

    usort($list, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']) ?: ($b['degraded_onu'] <=> $a['degraded_onu']);
    });

    $tree = array_values($treeMap);
    usort($tree, static function (array $a, array $b): int {
        return ($b['problem_port_count'] <=> $a['problem_port_count']) ?: ($b['degraded_onu_total'] <=> $a['degraded_onu_total']);
    });
    foreach ($tree as &$node) {
        usort($node['ports'], static function (array $a, array $b): int {
            return ($b['score'] <=> $a['score']) ?: ($b['degraded_onu'] <=> $a['degraded_onu']);
        });
    }
    unset($node);

    usort($alerts, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });

    return array(
        'ports' => $list,
        'tree' => $tree,
        'problem_port_count' => $problemPortCount,
        'degraded_onu_total' => $degradedOnuTotal,
        'alerts' => $alerts
    );
}

function ponscanner_attach_onu_port_names(array $onuTop, array $ponPorts): array {
    if (empty($onuTop) || empty($ponPorts)) {
        return $onuTop;
    }

    $portMap = array();
    foreach ($ponPorts as $p) {
        $oltId = (int)($p['olt_id'] ?? 0);
        $portId = ponscanner_normalize_port_id($p['port_id'] ?? '');
        if ($oltId <= 0 || $portId === '') {
            continue;
        }
        $portMap[$oltId . '|' . $portId] = (string)($p['port_name'] ?? '');
    }

    foreach ($onuTop as &$row) {
        $oltId = (int)($row['olt_id'] ?? 0);
        $portId = ponscanner_normalize_port_id($row['portolt'] ?? '');
        if ($oltId <= 0 || $portId === '') {
            $row['port_name'] = (string)($row['portolt'] ?? '');
            continue;
        }
        $key = $oltId . '|' . $portId;
        $name = isset($portMap[$key]) ? trim((string)$portMap[$key]) : '';
        $row['port_name'] = ($name !== '') ? $name : $portId;
    }
    unset($row);

    return $onuTop;
}

function ponscanner_build_report(array $params): array {
    global $cacheManager, $confPMon, $USER;

    $cacheEnabled = isset($confPMon['CACHE']) && (int)$confPMon['CACHE'] === 1;
    $cacheKey = 'ponscanner_v1_' . (int)($USER['id'] ?? 0) . '_' . md5(json_encode($params));

    if ($cacheEnabled && empty($params['force'])) {
        $cached = ponscanner_cache_get($cacheManager, $cacheKey);
        if (is_array($cached)) {
            $cached['cache'] = array('hit' => true, 'ttl_sec' => 45);
            return $cached;
        }
    }

    $oltMap = ponscanner_visible_olts((int)$params['olt_id']);
    if (empty($oltMap)) {
        return array(
            'ok' => true,
            'query' => $params,
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => array(
                'olt_count' => 0,
                'alert_count' => 0,
                'critical_count' => 0,
                'high_count' => 0,
                'medium_count' => 0,
                'critical_rate_percent' => 0,
                'high_rate_percent' => 0,
                'medium_rate_percent' => 0,
                'onu_coverage_percent' => 0
            ),
            'alerts' => array(),
            'hotspots' => array(),
            'pon_ports' => array('ports' => array(), 'tree' => array(), 'problem_port_count' => 0, 'degraded_onu_total' => 0),
            'onu' => array('visible_count' => 0, 'analyzed_count' => 0, 'top' => array()),
            'devices' => array('items' => array()),
            'anomalies' => array(),
            'history' => array('status' => array(), 'signal' => array()),
            'cache' => array('hit' => false, 'ttl_sec' => 45)
        );
    }

    $ponPorts = ponscanner_analyze_pon_ports($oltMap, $params);
    $onu = ponscanner_analyze_onu($oltMap, $params);
    $onuTopNamed = ponscanner_attach_onu_port_names($onu['top'], $ponPorts['ports']);
    $devices = ponscanner_analyze_devices($oltMap);
    $allAlerts = array_merge($ponPorts['alerts'], $onu['alerts'], $devices['alerts']);
    usort($allAlerts, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score']);
    });

    $critical = 0; $high = 0; $medium = 0;
    foreach ($allAlerts as $a) {
        if (($a['severity'] ?? '') === 'critical') $critical++;
        if (($a['severity'] ?? '') === 'high') $high++;
        if (($a['severity'] ?? '') === 'medium') $medium++;
    }

    $hotspots = ponscanner_merge_olt_scores(
        $oltMap,
        array(),
        $onu['by_olt'],
        $devices['by_olt']
    );
    $statusHistory = ponscanner_build_status_history($oltMap, $params);
    $signalHistory = ponscanner_build_signal_history($oltMap, $params);

    $visibleOnu = (int)$onu['visible_count'];
    $analyzedOnu = (int)$onu['analyzed_count'];
    $coverageOnu = ($visibleOnu > 0) ? round(($analyzedOnu * 100.0) / $visibleOnu, 2) : 0.0;
    $totalAlerts = max(1, count($allAlerts));
    $criticalRate = round(($critical * 100.0) / $totalAlerts, 2);
    $highRate = round(($high * 100.0) / $totalAlerts, 2);
    $mediumRate = round(($medium * 100.0) / $totalAlerts, 2);

    $report = array(
        'ok' => true,
        'query' => array(
            'hours' => (int)$params['hours'],
            'min_samples' => (int)$params['min_samples'],
            'top' => (int)$params['top'],
            'olt_id' => ((int)$params['olt_id'] > 0 ? (int)$params['olt_id'] : null),
            'onu_id' => ((int)$params['onu_id'] > 0 ? (int)$params['onu_id'] : null)
        ),
        'generated_at' => date('Y-m-d H:i:s'),
        'summary' => array(
            'olt_count' => count($oltMap),
            'alert_count' => count($allAlerts),
            'problem_port_count' => (int)($ponPorts['problem_port_count'] ?? 0),
            'degraded_onu_total' => (int)($ponPorts['degraded_onu_total'] ?? 0),
            'critical_count' => $critical,
            'high_count' => $high,
            'medium_count' => $medium,
            'critical_rate_percent' => $criticalRate,
            'high_rate_percent' => $highRate,
            'medium_rate_percent' => $mediumRate,
            'onu_coverage_percent' => $coverageOnu
        ),
        'alerts' => array_slice($allAlerts, 0, (int)$params['top']),
        'hotspots' => array_slice($hotspots, 0, (int)$params['top']),
        'pon_ports' => array(
            'ports' => array_slice($ponPorts['ports'], 0, (int)$params['top']),
            'tree' => $ponPorts['tree'],
            'problem_port_count' => (int)($ponPorts['problem_port_count'] ?? 0),
            'degraded_onu_total' => (int)($ponPorts['degraded_onu_total'] ?? 0)
        ),
        'onu' => array(
            'visible_count' => (int)$onu['visible_count'],
            'analyzed_count' => (int)$onu['analyzed_count'],
            'top' => array_slice($onuTopNamed, 0, (int)$params['top'])
        ),
        'devices' => array(
            'items' => array_slice($devices['items'], 0, (int)$params['top'])
        ),
        'anomalies' => array('top' => array()),
        'history' => array(
            'status' => $statusHistory,
            'signal' => $signalHistory
        ),
        'cache' => array(
            'hit' => false,
            'ttl_sec' => 45
        ),
        'model_info' => array(
            'self_learning' => 'Adaptive baseline from local history (mean/std + short-term trend windows)',
            'predictive_logic' => 'Threshold forecast based on recent slope (6h/2h deltas)'
        )
    );

    if ($cacheEnabled) {
        ponscanner_cache_set($cacheManager, $cacheKey, $report, 45);
    }

    return $report;
}


?>




