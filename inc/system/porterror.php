<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
if (!$access->get('porterror')) {
    $go->redirect('main');
}

$metatags = [
    'title'       => $lang['page_title_stats'],
    'description' => $lang['page_title_descr'],
    'page'        => 'porterror'
];

$js_boot = [
    'allDevices'    => $lang['alldevice'] ?? 'Пристрої',
    'statsPage'     => $lang['statspage'] ?? 'Статистика',
    'searchSwitch'  => 'Пошук комутатора…',
    'searchPort'    => 'Фільтр портів…',
    'noSwitches'    => 'Немає комутаторів з моніторингом.',
    'loadFail'      => 'Помилка завантаження',
    'noPorts'       => 'На цьому комутаторі немає портів у моніторингу.',
    'switchHint'    => 'Оберіть комутатор зліва — графіки всіх портів завантажаться автоматично.',
    'loadingPorts'  => 'Завантаження графіків портів…',
    'pickSwitch'    => 'Комутатор не вибрано',
    'chartsTitle'   => 'Графіки помилок портів',
    'btnToday'      => 'Сьогодні',
    'btnYesterday'  => 'Вчора',
    'btn7d'         => '7 днів',
    'btn30d'        => '30 днів',
    'legend'        => 'IN errors • OUT errors',
    'errorToday'    => 'Сьогодні',
    'errorTotal'    => 'Всього',
    'status'        => 'Статус',
    'emptySeries'   => 'Немає даних за обраний період'
];

$js_json = json_encode($js_boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$asset_v = time();

$page_html = <<<HTML
<div id="onu-speedbar">
  <a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>{$lang['main']}</a>
  <span class="brmspan"><i class="fi fi-rr-angle-left"></i>{$js_boot['statsPage']}</span>
</div>

<div class="porterror-app">
  <div class="porterror-layout">
    <div class="porterror-col-switch">
      <div class="card porterror-search-card">
        <input id="switchSearch" class="porterror-input porterror-input-switch" placeholder="{$js_boot['searchSwitch']}">
      </div>
      <div id="switchList" class="card porterror-switch-list"></div>
    </div>

    <div class="porterror-col-content">
      <div class="card porterror-toolbar">
        <strong id="switchTitle" class="porterror-switch-title">{$js_boot['pickSwitch']}</strong>
        <div class="porterror-ranges">
          <button data-range="today" class="btn-range btn-active">{$js_boot['btnToday']}</button>
          <button data-range="yesterday" class="btn-range">{$js_boot['btnYesterday']}</button>
          <button data-range="7d" class="btn-range">{$js_boot['btn7d']}</button>
          <button data-range="30d" class="btn-range">{$js_boot['btn30d']}</button>
        </div>
        <input id="portSearch" class="porterror-input porterror-input-port" placeholder="{$js_boot['searchPort']}">
      </div>

      <div id="chartsHint" class="card porterror-hint">{$js_boot['switchHint']}</div>
      <div id="portChartsGrid" class="porterror-grid"></div>
    </div>
  </div>
</div>

<link href="/style/css/porterror.css?v={$asset_v}" type="text/css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1"></script>
<script>window.PMON_PORTERROR = {$js_json};</script>
<script src="/style/js/porterror.js?v={$asset_v}" defer></script>
HTML;

$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', '<div class="mainadmin">' . $page_html . '</div>');
$tpl->compile('content');
$tpl->clear();
?>
