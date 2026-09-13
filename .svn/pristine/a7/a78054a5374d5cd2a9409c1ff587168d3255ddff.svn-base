(function(){
  const CFG = window.CFG || {};
  const API = CFG.api || {};
  const currentMap = window.CURRENT_MAP || 'openstreetmap';
  const visicomKey = window.VISICOM_KEY || '';

  const locationSelect = document.getElementById('locationSelect');
  const tpSearch = document.getElementById('tpSearch');
  const tpList = document.getElementById('tpList');
  const mapSummary = document.getElementById('mapSummary');
  const btnSelectAll = document.getElementById('tpSelectAll');
  const btnClear = document.getElementById('tpClear');
  const btnApply = document.getElementById('applyMap');
  const btnReset = document.getElementById('resetMap');
  const showTp = document.getElementById('showTp');
  const showPillar = document.getElementById('showPillar');

  const center = [Number(CFG.center?.lan || 49), Number(CFG.center?.lon || 31)];
  const map = L.map('mapper', { zoomControl: true }).setView(center, Number(CFG.center?.zoom || 7));

  const layers = {};
  layers.openstreetmap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19});
  layers.vision = L.tileLayer('https://{s}.visicom.ua/2.0.0/planet3/base/{z}/{x}/{y}.png?key=' + encodeURIComponent(visicomKey), {
    subdomains:['tms0','tms1','tms2','tms3'], maxZoom:19, tms:true
  });
  layers.google = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {maxZoom:19, subdomains:['mt0','mt1','mt2','mt3']});
  (layers[currentMap] || layers.openstreetmap).addTo(map);

  const groupTP = L.layerGroup().addTo(map);
  const groupPillars = L.layerGroup().addTo(map);

  const state = {
    locationId: 0,
    allTp: [],
    selectedTp: new Set(),
    mapTp: [],
    mapPillar: []
  };

  function buildTpTitle(t){
    return ((t.subname ? t.subname + ' ' : '') + (t.name_tp ? t.name_tp + ' ' : '') + (t.nomer_tp || '')).trim() || ('TP #' + Number(t.id));
  }

  function fillLocations(){
    const rows = Array.isArray(CFG.locations) ? CFG.locations : [];
    rows.forEach(function(loc){
      const opt = document.createElement('option');
      opt.value = String(Number(loc.id));
      const cnt = Number(loc.tp_count || 0);
      opt.textContent = loc.name + (cnt > 0 ? (' (' + cnt + ')') : '');
      locationSelect.appendChild(opt);
    });
  }

  function renderTpList(){
    tpList.innerHTML = '';
    const q = String(tpSearch.value || '').toLowerCase().trim();
    let shown = 0;
    state.allTp.forEach(function(tp){
      const id = Number(tp.id);
      const title = buildTpTitle(tp);
      if (q && title.toLowerCase().indexOf(q) === -1) return;
      shown++;

      const row = document.createElement('label');
      row.className = 'tp-item';

      const cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.checked = state.selectedTp.has(id);
      cb.addEventListener('change', function(){
        if (cb.checked) state.selectedTp.add(id);
        else state.selectedTp.delete(id);
      });

      const t = document.createElement('div');
      t.className = 'tp-title';
      t.textContent = title;

      row.appendChild(cb);
      row.appendChild(t);
      tpList.appendChild(row);
    });
    if (shown === 0) {
      tpList.innerHTML = '<div class="hint">No TP found</div>';
    }
  }

  async function loadTpByLocation(locationId){
    state.allTp = [];
    state.selectedTp.clear();
    renderTpList();
    if (!locationId) {
      mapSummary.textContent = 'Select location';
      return;
    }
    try {
      const url = API.tp_list + '&locationid=' + encodeURIComponent(String(locationId));
      const r = await fetch(url);
      const j = await r.json();
      state.allTp = (j && j.ok && j.data && Array.isArray(j.data.tps)) ? j.data.tps : [];
      mapSummary.textContent = 'TP loaded: ' + state.allTp.length;
      renderTpList();
    } catch (e) {
      mapSummary.textContent = 'Failed to load TP';
      console.warn(e);
    }
  }

  function updateLayers(){
    if (showTp.checked) {
      if (!map.hasLayer(groupTP)) groupTP.addTo(map);
    } else if (map.hasLayer(groupTP)) {
      map.removeLayer(groupTP);
    }

    if (showPillar.checked) {
      if (!map.hasLayer(groupPillars)) groupPillars.addTo(map);
    } else if (map.hasLayer(groupPillars)) {
      map.removeLayer(groupPillars);
    }
  }

  function renderMap(tpRows, pillarRows){
    state.mapTp = tpRows || [];
    state.mapPillar = pillarRows || [];

    groupTP.clearLayers();
    groupPillars.clearLayers();

    state.mapTp.forEach(function(tp){
      const lan = Number(tp.lan);
      const lon = Number(tp.lon);
      if (!isFinite(lan) || !isFinite(lon)) return;
      const title = buildTpTitle(tp);
      const tpIcon = L.divIcon({
        className: 'mapper',
        html: '<div class="oblenergo_tp"><img src="/style/img/oblenergo_tp.png" width="22" height="22" alt="tp"></div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11]
      });
      L.marker([lan, lon], { icon: tpIcon, title: title })
      .bindTooltip((tp.locationname || '') + '<br>' + (tp.oblenergoname || '') + '<br>' + title)
      .bindPopup('<b>' + escapeHtml(title) + '</b><br><a href="/?do=oblenergo&act=tp&id=' + Number(tp.id) + '">Open TP</a>')
      .addTo(groupTP);
    });

    state.mapPillar.forEach(function(p){
      const lan = Number(p.lan);
      const lon = Number(p.lon);
      if (!isFinite(lan) || !isFinite(lon)) return;
      const cc = Number(p.count_concurrent || 0);
      const pillarIconFile = cc > 1 ? 'prov_1.png' : 'prov_0.png';
      const pid = Number(p.id || 0);
      const title = 'Pillar: ' + (p.nomer_pillar || '');
      const pillarIcon = L.divIcon({
        className: 'mapper',
        html: '<div class="pillaricon"><img src="/style/img/' + pillarIconFile + '" width="16" height="16" alt="pillar"></div>',
        iconSize: [16, 16],
        iconAnchor: [8, 8]
      });
      const m = L.marker([lan, lon], { icon: pillarIcon, title: title }).bindTooltip(title);

      if (pid > 0) {
        m.bindPopup('<b>' + escapeHtml(title) + '</b><br><a href="/?do=oblenergo&act=editpillar&id=' + pid + '">Edit</a>');
      }
      m.addTo(groupPillars);
    });

    updateLayers();

    if (state.mapTp.length > 0) {
      const bounds = [];
      state.mapTp.forEach(function(tp){
        const lan = Number(tp.lan);
        const lon = Number(tp.lon);
        if (isFinite(lan) && isFinite(lon)) bounds.push([lan, lon]);
      });
      if (bounds.length > 0) map.fitBounds(bounds, { padding: [20, 20], maxZoom: 17 });
    }

    mapSummary.textContent = 'TP: ' + state.mapTp.length + ', pillars: ' + state.mapPillar.length;
  }

  async function applyFilter(){
    if (!state.locationId) {
      mapSummary.textContent = 'Select location first';
      return;
    }
    const ids = Array.from(state.selectedTp);
    if (ids.length === 0) {
      mapSummary.textContent = 'Select at least one TP';
      return;
    }

    mapSummary.textContent = 'Loading map data...';
    try {
      const qs = new URLSearchParams();
      qs.set('locationid', String(state.locationId));
      ids.forEach(function(id){ qs.append('tp[]', String(id)); });
      const r = await fetch(API.map_data + '&' + qs.toString());
      const j = await r.json();
      if (!j || !j.ok || !j.data) {
        mapSummary.textContent = 'No data';
        renderMap([], []);
        return;
      }
      renderMap(j.data.tps || [], j.data.pillars || []);
    } catch (e) {
      mapSummary.textContent = 'Failed to load map data';
      console.warn(e);
    }
  }

  function resetAll(){
    locationSelect.value = '';
    tpSearch.value = '';
    state.locationId = 0;
    state.allTp = [];
    state.selectedTp.clear();
    renderTpList();
    renderMap([], []);
    map.setView(center, Number(CFG.center?.zoom || 7));
    mapSummary.textContent = 'No data loaded';
  }

  locationSelect.addEventListener('change', function(){
    state.locationId = Number(this.value || 0);
    const loc = (Array.isArray(CFG.locations) ? CFG.locations : []).find(function(x){ return Number(x.id) === state.locationId; });
    if (loc && isFinite(Number(loc.lan)) && isFinite(Number(loc.lon))) {
      map.setView([Number(loc.lan), Number(loc.lon)], 13);
    }
    loadTpByLocation(state.locationId);
  });

  tpSearch.addEventListener('input', function(){
    renderTpList();
  });

  btnSelectAll.addEventListener('click', function(){
    state.allTp.forEach(function(tp){ state.selectedTp.add(Number(tp.id)); });
    renderTpList();
  });

  btnClear.addEventListener('click', function(){
    state.selectedTp.clear();
    renderTpList();
  });

  btnApply.addEventListener('click', function(){
    applyFilter();
  });

  btnReset.addEventListener('click', function(){
    resetAll();
  });

  showTp.addEventListener('change', updateLayers);
  showPillar.addEventListener('change', updateLayers);

  fillLocations();
  updateLayers();

  function escapeHtml(s){
    return String(s || '')
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }
})();
