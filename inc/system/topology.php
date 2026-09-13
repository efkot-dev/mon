<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
$metatags = ['title' => 'Topology','description' => 'Topology editor','page' => 'topology'];
$speedbar = '';
$html = $speedbar . <<<'HTML'
<div class="mainadmin">
  <div class="nav-fiber p10" style="display:none;"><button id="topoResetZoom" class="cssadd">Reset zoom</button> </div>
  <div id="topology-wrap" style="position:relative;width:100%;height:calc(100vh - 80px);min-height:520px;background:#fff;border:1px solid #dcdcdc;border-radius:10px;overflow:hidden">
    <div id="topoControls" style="position:absolute;left:12px;top:12px;z-index:30;display:flex;gap:8px;">
      <button id="topoThemeToggle" class="cssadd" style="padding:6px 10px;">Тема: день</button>
      <button id="topoFullscreenToggle" class="cssadd" style="padding:6px 10px;">На весь екран</button>
      <button id="topoColorsToggle" class="cssadd" style="padding:6px 10px;">Кольори</button>
    </div>
    <svg id="topology-canvas" style="width:100%;height:100%;display:block;background:#fff;"></svg>

    <div id="topoPortModal" style="display:none;position:absolute;right:14px;top:14px;width:360px;max-width:calc(100% - 28px);max-height:calc(100% - 28px);background:#fff;border:1px solid #dcdcdc;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.12);z-index:20;overflow:hidden">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid #eee;">
        <div id="topoPortTitle" style="font-weight:700">Порт</div>
        <button id="topoClosePortModal" class="cssadd" style="padding:4px 8px;">×</button>
      </div>
      <div style="padding:12px;overflow:auto;max-height:calc(100% - 48px);">
        <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Назва порту (label)</label>
        <input id="topoPortLabel" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;margin-bottom:10px;" placeholder="Напр. GE1/0/1">

        <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Прив'язка SNMP</label>
        <select id="topoBindMode" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
          <option value="manual">Вручну (IP/RO/OID)</option>
          <option value="switch">З існуючого комутатора (switch_port)</option>
        </select>

        <div id="topoBindSwitchBlock" style="display:none;margin-top:10px;">
          <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Комутатор</label>
          <select id="topoBindSwitch" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;"></select>
          <label style="display:block;font-size:12px;opacity:.8;margin:10px 0 6px;">Порт</label>
          <select id="topoBindPort" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;"></select>
          <div id="topoBindHint" style="margin-top:8px;font-size:12px;opacity:.75;"></div>
        </div>

        <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Опис</label>
        <textarea id="topoPortDesc" style="width:100%;height:64px;resize:vertical;padding:8px;border:1px solid #cfcfcf;border-radius:8px;"></textarea>

        <div style="display:grid;grid-template-columns:1fr;gap:8px;margin-top:10px;">
          <div style="display:flex;gap:8px;">
            <div style="flex:1">
              <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">SNMP IP</label>
              <input id="topoSnmpIp" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="192.168.1.1">
            </div>
            <div style="flex:1">
              <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">SNMP RO</label>
              <input id="topoSnmpRo" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="public">
            </div>
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">OID</label>
            <input id="topoSnmpOid" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="1.3.6.1.2.1.2.2.1.8.<ifIndex>">
          </div>
        </div>

        <div style="display:flex;gap:8px;margin-top:12px;">
          <button id="topoSavePort" class="cssadd" style="flex:1;">Зберегти</button>
        </div>
        <div id="topoPortMeta" style="margin-top:10px;font-size:12px;opacity:.75;"></div>
      </div>
    </div>

    <div id="topoSwitchModal" style="display:none;position:absolute;left:14px;top:14px;width:360px;max-width:calc(100% - 28px);max-height:calc(100% - 28px);background:#fff;border:1px solid #dcdcdc;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.12);z-index:20;overflow:hidden">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid #eee;">
        <div style="font-weight:700">Свіч</div>
        <button id="topoCloseSwitchModal" class="cssadd" style="padding:4px 8px;">×</button>
      </div>
      <div style="padding:12px;overflow:auto;max-height:calc(100% - 48px);">
        <div style="display:grid;grid-template-columns:1fr;gap:8px;">
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Тип</label>
            <select id="topoEditNodeKind" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
              <option value="switch">Свіч</option>
              <option value="element">Елемент</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Назва</label>
            <input id="topoEditSwitchName" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Модель</label>
            <input id="topoEditSwitchModel" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Іконка/картинка (URL)</label>
            <input id="topoEditNodeIconUrl" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="/style/img/cam.png або https://...">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Посилання при кліку (URL)</label>
            <input id="topoEditNodeOpenUrl" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="http://camera.local/">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Порти</label>
            <input id="topoEditSwitchPorts" type="number" min="1" max="96" style="width:120px;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Портів в ряд</label>
            <input id="topoEditSwitchPortsPerRow" type="number" min="1" max="32" style="width:120px;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
          </div>
          <div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
            <input id="topoEditSwitchLocked" type="checkbox" style="width:18px;height:18px;">
            <label for="topoEditSwitchLocked" style="font-size:13px;opacity:.9;cursor:pointer;">Закріпити (заборонити переміщення)</label>
          </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;">
          <button id="topoSaveSwitch" class="cssadd" style="flex:1;">Зберегти</button>
          <button id="topoDeleteSwitchInModal" class="cssadd" style="flex:1;background:#ffecec;border-color:#f3b3b3;">Видалити</button>
        </div>
        <div id="topoSwitchMeta" style="margin-top:10px;font-size:12px;opacity:.75;"></div>
      </div>
    </div>

    <div id="topoLinkModal" style="display:none;position:absolute;left:14px;bottom:14px;width:360px;max-width:calc(100% - 28px);max-height:calc(100% - 28px);background:#fff;border:1px solid #dcdcdc;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.12);z-index:20;overflow:hidden">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid #eee;">
        <div style="font-weight:700">Волокно</div>
        <button id="topoCloseLinkModal" class="cssadd" style="padding:4px 8px;">×</button>
      </div>
      <div style="padding:12px;overflow:auto;max-height:calc(100% - 48px);">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
          <div style="font-size:13px;opacity:.9">Колір волокна (UP↔UP)</div>
          <input id="topoLinkColor" type="color" value="#0a8f3c" style="width:56px;height:32px;border:1px solid #cfcfcf;border-radius:8px;padding:2px;background:#fff;">
        </div>
        <div>
          <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Довжина (м)</label>
          <input id="topoLinkLen" type="number" min="0" step="1" style="width:140px;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="наприклад 250">
        </div>
        <div style="margin-top:10px;">
          <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Опис</label>
          <textarea id="topoLinkDesc" style="width:100%;height:64px;resize:vertical;padding:8px;border:1px solid #cfcfcf;border-radius:8px;"></textarea>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;">
          <button id="topoSaveLink" class="cssadd" style="flex:1;">Зберегти</button>
          <button id="topoDeleteLink" class="cssadd" style="flex:1;background:#ffecec;border-color:#f3b3b3;">Видалити</button>
        </div>
        <div id="topoLinkMeta" style="margin-top:10px;font-size:12px;opacity:.75;"></div>
      </div>
    </div>

    <div id="topoAddSwitchModal" style="display:none;position:absolute;left:14px;top:14px;width:360px;max-width:calc(100% - 28px);max-height:calc(100% - 28px);background:#fff;border:1px solid #dcdcdc;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.12);z-index:30;overflow:hidden">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid #eee;">
        <div style="font-weight:700">Додати елемент</div>
        <button id="topoCloseAddSwitchModal" class="cssadd" style="padding:4px 8px;">×</button>
      </div>
      <div style="padding:12px;overflow:auto;max-height:calc(100% - 48px);">
        <div style="display:grid;grid-template-columns:1fr;gap:8px;">
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Тип</label>
            <select id="topoNewNodeKind" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
              <option value="switch">Свіч</option>
              <option value="element">Елемент</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Назва</label>
            <input id="topoNewSwitchName" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" value="Switch">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Модель</label>
            <input id="topoNewSwitchModel" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" value="">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Іконка/картинка (URL)</label>
            <input id="topoNewNodeIconUrl" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="/style/img/ups.png або https://...">
          </div>
          <div>
            <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Посилання при кліку (URL)</label>
            <input id="topoNewNodeOpenUrl" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;" placeholder="http://camera.local/">
          </div>
          <div style="display:flex;gap:8px;">
            <div style="flex:1">
              <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Порти</label>
              <input id="topoNewSwitchPorts" type="number" min="1" max="96" value="12" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
            </div>
            <div style="flex:1">
              <label style="display:block;font-size:12px;opacity:.8;margin-bottom:6px;">Портів в ряд</label>
              <input id="topoNewSwitchPortsPerRow" type="number" min="1" max="32" value="16" style="width:100%;padding:8px;border:1px solid #cfcfcf;border-radius:8px;">
            </div>
          </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;">
          <button id="topoCreateSwitch" class="cssadd" style="flex:1;">Створити</button>
        </div>
        <div id="topoAddSwitchMeta" style="margin-top:10px;font-size:12px;opacity:.75;"></div>
      </div>
    </div>

    <div id="topoColorsModal" style="display:none;position:absolute;left:14px;top:14px;width:320px;max-width:calc(100% - 28px);max-height:calc(100% - 28px);background:#fff;border:1px solid #dcdcdc;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.12);z-index:30;overflow:hidden">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid #eee;">
        <div style="font-weight:700">Кольори</div>
        <button id="topoCloseColorsModal" class="cssadd" style="padding:4px 8px;">×</button>
      </div>
      <div style="padding:12px;overflow:auto;max-height:calc(100% - 48px);">
        <div style="display:grid;grid-template-columns:1fr;gap:10px;">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
            <div style="font-size:13px;opacity:.9">Активний (UP)</div>
            <input id="topoColorUp" type="color" value="#00c853" style="width:56px;height:32px;border:1px solid #cfcfcf;border-radius:8px;padding:2px;background:#fff;">
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
            <div style="font-size:13px;opacity:.9">Волокно (UP↔UP)</div>
            <input id="topoColorLinkUp" type="color" value="#0a8f3c" style="width:56px;height:32px;border:1px solid #cfcfcf;border-radius:8px;padding:2px;background:#fff;">
          </div>
          <div style="font-size:12px;opacity:.75">Неактивний (DOWN) завжди червоний.</div>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;">
          <button id="topoSaveColors" class="cssadd" style="flex:1;">Зберегти</button>
          <button id="topoResetColors" class="cssadd" style="flex:1;">Скинути</button>
        </div>
      </div>
    </div>
  </div>
</div>

HTML;
// External assets (CSS/JS) for topology page.
$cssPath = dirname(__DIR__, 2) . '/style/css/topology.css';
$jsPath  = dirname(__DIR__, 2) . '/style/js/topology.js';
$assetV = 1;
if (is_file($cssPath) && is_file($jsPath)) {
    $assetV = (int) max(@filemtime($cssPath), @filemtime($jsPath));
}
$html .= "\n" . '<link rel="stylesheet" href="/style/css/topology.css?v=' . $assetV . '">' . "\n";
$html .= '<script src="/style/js/topology.js?v=' . $assetV . '"></script>' . "\n";
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', $html);
$tpl->compile('content');
$tpl->clear();

