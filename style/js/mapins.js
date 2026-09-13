(function () {
  'use strict';
  const BOOT = window.PON_MAP_BOOT || {};
  const CUR_LOC_ID = BOOT.curLocId || 0;
  const BAD_START  = (BOOT.thresholds && BOOT.thresholds.badStart) || 26;
  const BAD_END    = (BOOT.thresholds && BOOT.thresholds.badEnd)   || 39;
  const DEFAULT_LAYER = (BOOT.defaults && BOOT.defaults.baseLayer) || 'openstreetmap';
  const VISICOM_KEY = BOOT.visicomKey || 'pmon';
  const I18N = BOOT.i18n || { SIGNAL:'Signal', DIST:'Distance', ONLINE:'Online', OFFLINE:'Offline' };
  const API_URL = BOOT.apiUrl || root + '/?do=map_api';
  const START_CENTER = (BOOT.center) ? BOOT.center : {lat:50.4501, lon:30.5234, zoom:13};
  window.markers   = [];
  window.onuLabels = [];
  window.onuIcons  = [];
  window.minZoomLevel   = 11;
  window.signalBadStart = BAD_START;
  window.signalBadEnd   = BAD_END;
  let LOCATIONS  = [];
  let OLT_BY_LOC = {};
  let locMarkers = {};
  let oltMarkers = {};
  let locGroup, oltGroup;
  function initMap() {
    window.map = L.map('mapper', {
      preferCanvas: true,
      markerZoomAnimation: false,
      zoomAnimation: true
    }).setView([START_CENTER.lat, START_CENTER.lon], START_CENTER.zoom);
    const baseBadge = L.DomUtil.create('div','base-cycle-badge', map.getContainer());
    const updateBaseBadge = (name) => { baseBadge.textContent = name; };
    const baseDefs = [
      { key:'openstreetmap', name:'OSM',   layer: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}) },
      { key:'cartoLight',    name:'Light', layer: L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',{maxZoom:20,subdomains:['a','b','c','d']}) },
      { key:'cartoDark',     name:'Dark',  layer: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',{maxZoom:20,subdomains:['a','b','c','d']}) },
      { key:'esriImagery',   name:'Sat',   layer: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{maxZoom:19}) },
      { key:'esriTopo',      name:'Topo',  layer: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',{maxZoom:19}) },
      { key:'vision',        name:'Visi',  layer: L.tileLayer('https://{s}.visicom.ua/2.0.0/planet3/base/{z}/{x}/{y}.png?key='+VISICOM_KEY,{subdomains:['tms0','tms1','tms2','tms3'],maxZoom:19,tms:true}) },
      { key:'google',        name:'Google',layer: L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{maxZoom:19,subdomains:['mt0','mt1','mt2','mt3']}) }
    ];
    let currentBaseKey   = localStorage.getItem('pon_baseLayer') || DEFAULT_LAYER || 'openstreetmap';
    let currentBaseLayer = null;
    function setBaseByKey(key) {
      const def = baseDefs.find(d => d.key === key) || baseDefs[0];
      if (!def) return;
      if (currentBaseLayer && map.hasLayer(currentBaseLayer)) map.removeLayer(currentBaseLayer);
      currentBaseLayer = def.layer.addTo(map);
      currentBaseKey = def.key;
      localStorage.setItem('pon_baseLayer', currentBaseKey);
      updateBaseBadge(def.name);
      document.querySelectorAll('.basemap-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.key === currentBaseKey);
      });
      currentBaseLayer.once && currentBaseLayer.once('tileerror', function(){
        if (currentBaseKey !== 'openstreetmap') setBaseByKey('openstreetmap');
      });
    }
    const bar = L.DomUtil.create('div','basemap-bar', map.getContainer());
    baseDefs.forEach(b => {
      const el = L.DomUtil.create('div','basemap-btn', bar);
      el.textContent = b.name;
      el.dataset.key = b.key;
      L.DomEvent.disableClickPropagation(el);
      el.addEventListener('click', e => { e.preventDefault(); setBaseByKey(b.key); });
    });
    setBaseByKey(currentBaseKey);
    const LabelCanvas = L.Layer.extend({
      initialize: function (getDataFn) { this._getData = getDataFn; this._anim = null; },
      onAdd: function (map) {
        this._map = map;
        this._canvas = L.DomUtil.create('canvas', 'leaflet-canvas-labels');
        this._canvas.style.position = 'absolute';
        this._canvas.style.pointerEvents = 'none';
        this._ctx = this._canvas.getContext('2d');
        const size = map.getSize();
        this._canvas.width = size.x; this._canvas.height = size.y;
        map.getPanes().overlayPane.appendChild(this._canvas);
        this._reset = this._reset.bind(this);
        this._updatePosition = this._updatePosition.bind(this);
        map.on('move zoom', this._updatePosition, this);
        map.on('moveend zoomend resize', this._reset, this);
        this._reset();
      },
      onRemove: function (map) {
        map.off('move zoom', this._updatePosition, this);
        map.off('moveend zoomend resize', this._reset, this);
        L.DomUtil.remove(this._canvas);
      },
      _updatePosition: function () {
        const topLeft = this._map.containerPointToLayerPoint([0, 0]);
        L.DomUtil.setPosition(this._canvas, topLeft);
        this._requestRedraw();
      },
      _reset: function () {
        const topLeft = this._map.containerPointToLayerPoint([0, 0]);
        L.DomUtil.setPosition(this._canvas, topLeft);
        const size = this._map.getSize();
        if (this._canvas.width !== size.x)  this._canvas.width  = size.x;
        if (this._canvas.height !== size.y) this._canvas.height = size.y;
        this._requestRedraw();
      },
      _requestRedraw: function(){
        if (this._anim) return;
        const self = this;
        this._anim = requestAnimationFrame(function(){ self._anim = null; self._redraw(); });
      },
      _redraw: function () {
        const ctx = this._ctx, map = this._map;
        ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);
        const data = (this._getData() || []);
        const show = map.getZoom() >= (window.minZoomLevel || 17);
        if (!show) return;
        ctx.font = '11px sans-serif';
        ctx.textBaseline = 'middle';
        ctx.textAlign = 'center';
        for (let i=0;i<data.length;i++) {
          const d = data[i];
          const p = map.latLngToContainerPoint([d.lat, d.lon]);
          const text = d.text;
          const metrics = ctx.measureText(text);
          const padX=6, bw=Math.ceil(metrics.width)+padX*2, bh=18, x=p.x, y=p.y, rx=6;
          ctx.beginPath();
          const minR = Math.min(bw, bh)/2; 
          const rr = Math.min(rx, minR);
          ctx.moveTo(x - bw/2 + rr, y - bh/2);
          ctx.arcTo(x + bw/2, y - bh/2, x + bw/2, y + bh/2, rr);
          ctx.arcTo(x + bw/2, y + bh/2, x - bw/2, y + bh/2, rr);
          ctx.arcTo(x - bw/2, y + bh/2, x - bw/2, y - bh/2, rr);
          ctx.arcTo(x - bw/2, y - bh/2, x + bw/2, y - bh/2, rr);
          ctx.closePath();
          ctx.fillStyle = d.bg; ctx.fill();
          ctx.lineWidth = 1; ctx.strokeStyle = d.border; ctx.stroke();
          ctx.fillStyle = d.color; ctx.fillText(text, x, y);
        }
      }
    });
    const OFFLINE_ICON_LEAFLET = {
      'err1' : (typeof map_onu_err1    !== 'undefined') ? map_onu_err1    : null,
      'err61': (typeof map_red_box      !== 'undefined') ? map_red_box     : null,
      'err34': (typeof map_onu_err34    !== 'undefined') ? map_onu_err34   : null,
      'err0' : (typeof map_onu_err59    !== 'undefined') ? map_onu_err59   : null,
      'err6' : (typeof map_onu_err6     !== 'undefined') ? map_onu_err6    : null,
      'err8' : (typeof map_onu_err6     !== 'undefined') ? map_onu_err6    : null,
      'err59': (typeof map_onu_err59    !== 'undefined') ? map_onu_err59   : null,
      'default': (typeof map_onu_offline!== 'undefined') ? map_onu_offline : null
    };
    const OFFLINE_ICON_CACHE = {};
    function iconMetaFromLeafletIcon(leafIcon) {
      if (!leafIcon || !leafIcon.options) return null;
      const url = leafIcon.options.iconUrl || leafIcon.options.icon || null;
      if (!url) return null;
      const size = leafIcon.options.iconSize || [24,24];
      const anchor = leafIcon.options.iconAnchor || [size[0]/2, size[1]];
      return { url, w:size[0], h:size[1], ax:anchor[0], ay:anchor[1] };
    }
    function getOfflineCanvasIcon(reason) {
      const key = (reason || '').toLowerCase();
      if (!OFFLINE_ICON_CACHE[key]) {
        const leaf = OFFLINE_ICON_LEAFLET[key] || OFFLINE_ICON_LEAFLET['default'];
        const meta = iconMetaFromLeafletIcon(leaf);
        if (!meta) return null;
        const img = new Image(); img.src = meta.url;
        OFFLINE_ICON_CACHE[key] = { img, w:meta.w, h:meta.h, ax:meta.ax, ay:meta.ay };
      }
      return OFFLINE_ICON_CACHE[key];
    }
    const IconCanvas = L.Layer.extend({
      initialize: function (getDataFn) { this._getData = getDataFn; this._anim = null; },
      onAdd: function (map) {
        this._map = map;
        this._canvas = L.DomUtil.create('canvas', 'leaflet-canvas-icons');
        this._canvas.style.position = 'absolute';
        this._canvas.style.pointerEvents = 'none';
        this._ctx = this._canvas.getContext('2d');
        const size = map.getSize();
        this._canvas.width = size.x; this._canvas.height = size.y;
        map.getPanes().overlayPane.appendChild(this._canvas);
        this._reset = this._reset.bind(this);
        this._updatePosition = this._updatePosition.bind(this);
        map.on('move zoom', this._updatePosition, this);
        map.on('moveend zoomend resize', this._reset, this);
        this._reset();
      },
      onRemove: function (map) {
        map.off('move zoom', this._updatePosition, this);
        map.off('moveend zoomend resize', this._reset, this);
        L.DomUtil.remove(this._canvas);
      },
      _updatePosition: function () {
        const topLeft = this._map.containerPointToLayerPoint([0, 0]);
        L.DomUtil.setPosition(this._canvas, topLeft);
        this._requestRedraw();
      },
      _reset: function () {
        const topLeft = this._map.containerPointToLayerPoint([0, 0]);
        L.DomUtil.setPosition(this._canvas, topLeft);
        const size = this._map.getSize();
        if (this._canvas.width !== size.x)  this._canvas.width  = size.x;
        if (this._canvas.height !== size.y) this._canvas.height = size.y;
        this._requestRedraw();
      },
      _requestRedraw: function(){
        if (this._anim) return;
        const self = this;
        this._anim = requestAnimationFrame(function(){ self._anim = null; self._redraw(); });
      },
      _redraw: function () {
        const ctx = this._ctx, map = this._map;
        ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);
        const data = this._getData() || [];
        for (let i=0;i<data.length;i++) {
          const d = data[i];
          const p = map.latLngToContainerPoint([d.lat, d.lon]);
          const meta = getOfflineCanvasIcon(d.reason);
          if (!meta) continue;
          if (!meta.img.complete) { (function(layer){ meta.img.onload = function(){ layer._requestRedraw(); }; })(this); continue; }
          const x = p.x - meta.ax, y = p.y - meta.ay;
          ctx.drawImage(meta.img, x, y, meta.w, meta.h);
        }
      }
    });
    window.labelLayer = new LabelCanvas(function(){ return window.onuLabels; }).addTo(map);
    window.iconLayer  = new IconCanvas(function(){ return window.onuIcons;  }).addTo(map);
    locGroup = L.layerGroup().addTo(map);
    oltGroup = L.layerGroup().addTo(map);
    map.on('movestart', function(){ map.closePopup(); });
    map.on('zoom move', function(){
      if (labelLayer && labelLayer._requestRedraw) labelLayer._requestRedraw();
      if (iconLayer  && iconLayer._requestRedraw)  iconLayer._requestRedraw();
    });
    const canvasRenderer = L.canvas({ padding: 0.5 });
    function colorSetByRx(rx) {
      const v = Math.abs(parseFloat(rx));
      if (!isFinite(v)) return { bg:'#ffe6d5', border:'#e67e22', color:'#7a3f0c' };
      const s = Math.trunc(v);
      if (s>=1 && s<=12)  return { bg:'#f8e71c', border:'#f8e71c', color:'#222' };
      if (s>=13 && s<=19) return { bg:'#278dd3', border:'#278dd3', color:'#fff' };
      if (s>=20 && s<BAD_START) return { bg:'#7ef66a', border:'#7ef66a', color:'#222' };
      if (s>=BAD_START && s<=BAD_END) return { bg:'red', border:'tomato', color:'#fff' };
      return { bg:'#ffe6d5', border:'#e67e22', color:'#7a3f0c' };
    }
    window.makeOnuMarker = function(lat, lon, data) {
      const isOnline = parseInt(data.status || 0, 10) === 1;
      const reason   = (data.reason || '').toLowerCase();
      const m = L.circleMarker([lat, lon], {
        renderer: canvasRenderer,
        radius: 10,
        stroke: false,
        fillOpacity: 0.001,
        fillColor: '#000'
      }).addTo(map);
      window.markers.push(m);
      if (isOnline) {
        const pal = colorSetByRx(data.rx);
        window.onuLabels.push({ lat:lat, lon:lon, text:String(data.rx), bg:pal.bg, border:pal.border, color:pal.color });
        if (labelLayer && labelLayer._requestRedraw) labelLayer._requestRedraw();
      } else {
        window.onuIcons.push({ lat:lat, lon:lon, reason:reason });
        if (iconLayer && iconLayer._requestRedraw) iconLayer._requestRedraw();
      }
      let hoverTimer = null;
      m.on('mouseover', function () {
        if (m.getTooltip()) return;
        hoverTimer = setTimeout(function () {
          if (!m.getTooltip()) {
            const idkey = data.mac || data.sn || '';
            const ttl = (data.type||'') + ' ' + (data.inface||'') +
                        '<br><b>' + idkey + '</b>' +
                        '<br><b>'+I18N.SIGNAL+':</b> ' + (isOnline ? data.rx : 0) + ' dBm' +
                        '<br><b>'+I18N.DIST+':</b> ' + (data.dist || '') +
                        (isOnline
                          ? '<br><b>'+I18N.ONLINE+':</b> '  + (data.online || '')
                          : '<br><b>'+I18N.OFFLINE+':</b> ' + (data.offline || '') +
                            (reason ? '<br><b>Reason:</b> ' + reason : '')
                        );
            m.bindTooltip(ttl, { direction:'top', opacity:0.9 }).openTooltip();
          }
        }, 100);
      });
      m.on('mouseout', function () { if (hoverTimer) { clearTimeout(hoverTimer); hoverTimer = null; } });
      m.on('click', function () {
        if (!m.getPopup()) {
          const idkey = data.mac || data.sn || '';
          let nameTag = '';
          if (data.name) nameTag += "<br><b style='color:#d100ff;'>" + data.name + "</b>";
          if (data.tag)  nameTag += "<br><b style='color:#006dff;'>" + data.tag  + "</b>";
          const html =
            '<div class="div-l">' +
              '<a href="/?do=onu&id=' + (data.idonu||'') + '">' + idkey + '</a><br>' +
              (data.type||'') + ' ' + (data.inface||'') + '<br>' +
              '<b>RX ONU:</b> ' + (isOnline ? data.rx : 0) + ' dBm<br>' +
              '<b>'+I18N.DIST+':</b> ' + (data.dist || '') +
              (isOnline
                ? '<br><b>'+I18N.ONLINE+':</b> '  + (data.online || '')
                : '<br><b>'+I18N.OFFLINE+':</b> ' + (data.offline || '') +
                  (reason ? '<br><b>Reason:</b> ' + reason : '')
              ) +
              nameTag +
            '</div>';
          m.bindPopup(html).openPopup();
        }
      });
      return m;
    };
  }
  async function loadMeta() {
    const res = await fetch(API_URL, { method:'GET' });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'meta error');
    LOCATIONS  = json.locations || [];
    OLT_BY_LOC = json.oltsByLocation || {};
  }
function buildUI() {
  const toggle = L.DomUtil.create('div','filter-toggle', map.getContainer());
  toggle.textContent = 'Фільтри ▸';

  const panel  = L.DomUtil.create('div','filter-panel', map.getContainer());
  panel.innerHTML = ''
    + '<div class="panel-header"><b>Швидкі фільтри</b><span id="f-close" class="ctl" role="button" aria-label="Закрити" tabindex="0">✕</span></div>'
    + '<div class="panel-section">'
    + '  <div class="panel-toolbar">'
    + '    <input id="f-search" type="search" placeholder="Пошук локації або OLT...">'
    + '    <label class="only-sel"><input type="checkbox" id="f-only-sel"> лише вибрані</label>'
    + '    <div class="toolbar-buttons">'
    + '      <span id="f-expand"   class="ctl" role="button" tabindex="0">Розгорнути</span>'
    + '      <span id="f-collapse" class="ctl" role="button" tabindex="0">Згорнути</span>'
    + '    </div>'
    + '  </div>'
    + '  <div id="f-tree" class="tree"></div>'
    + '</div>'
    + '<div class="panel-actions">'
    + '  <button id="f-clear">Скинути</button>'
    + '  <button id="f-apply" class="primary">Показати вибране</button>'
    + '</div>';

  L.DomEvent.disableClickPropagation(toggle);
  L.DomEvent.disableScrollPropagation(panel);

  // Хелпер: активувати по кліку та Enter/Space
  function onActivate(el, handler){
    el.addEventListener('click', (e)=>{ e.preventDefault(); handler(e); });
    el.addEventListener('keydown', (e)=>{ if (e.key==='Enter' || e.key===' ') { e.preventDefault(); handler(e); }});
  }

  onActivate(toggle, ()=>{
    panel.classList.toggle('open');
    toggle.textContent = panel.classList.contains('open') ? 'Фільтри ◂' : 'Фільтри ▸';
  });
  onActivate(panel.querySelector('#f-close'), ()=>{
    panel.classList.remove('open');
    toggle.textContent = 'Фільтри ▸';
  });

  // ======= локальний стан
  const selectedLocs = new Set();
  const selectedOlts = new Set();
  const expandedLocs = new Set();
  if (CUR_LOC_ID) { selectedLocs.add(CUR_LOC_ID); expandedLocs.add(CUR_LOC_ID); }

  const tree = panel.querySelector('#f-tree');
  const search = panel.querySelector('#f-search');
  const onlySel = panel.querySelector('#f-only-sel');

  const oltsOf = (locId) => (OLT_BY_LOC[locId] || []);
  const oltIdsOf = (locId) => oltsOf(locId).map(o => parseInt(o.id,10));

  function setLocationOnly(locId) {
    selectedLocs.clear();
    selectedOlts.clear();
    selectedLocs.add(locId);
    oltIdsOf(locId).forEach(id => selectedOlts.add(id));
  }
  function countSelInLoc(locId) {
    let total=0, sel=0;
    oltsOf(locId).forEach(o => { total++; if (selectedOlts.has(parseInt(o.id,10))) sel++; });
    return {sel,total};
  }
  function updateOltMarkersVisibility() {
    Object.keys(oltMarkers).forEach(idStr => {
      const id = parseInt(idStr,10);
      const m = oltMarkers[idStr];
      const show = selectedOlts.has(id);
      if (show) { if (!oltGroup.hasLayer(m)) oltGroup.addLayer(m); }
      else      { if (oltGroup.hasLayer(m))  oltGroup.removeLayer(m); }
    });
  }

  function renderLocationBlock(l) {
    const locId = parseInt(l.id,10);
    const wrap = document.createElement('div');
    wrap.className = 'tree-loc';
    wrap.dataset.loc = String(locId);

    const {sel,total} = countSelInLoc(locId);
    const isOpen = expandedLocs.has(locId);

    const head = document.createElement('div');
    head.className = 'tree-head';
    head.innerHTML =
      `<span class="twisty" role="button" tabindex="0" aria-expanded="${isOpen?'true':'false'}">${isOpen?'▾':'▸'}</span>`
    + ` <div class="tree-title"><label><input type="checkbox" class="chk-loc" value="${locId}" ${selectedLocs.has(locId)?'checked':''}> <span class="name">${l.name || ('Локація '+locId)}</span></label></div>`
    + ' <div class="badges">'
    + `   <span class="badge" title="Вибрано/всього OLT">${sel}/${total}</span>`
    + `   <span class="ctl mini sel-all" role="button" tabindex="0" data-loc="${locId}">усі OLT</span>`
    + ' </div>';
    wrap.appendChild(head);

    const list = document.createElement('div');
    list.className = 'tree-olts' + (isOpen ? ' open' : '');
    oltsOf(locId).forEach(o => {
      const id = parseInt(o.id,10);
      const row = document.createElement('label');
      row.innerHTML =
        `<input type="checkbox" class="chk-olt" data-loc="${locId}" value="${id}" ${selectedOlts.has(id)?'checked':''}>`
      + ` <span>${o.name || ('OLT #'+id)}</span>`;
      list.appendChild(row);
    });
    wrap.appendChild(list);

    const twisty = head.querySelector('.twisty');
    const title  = head.querySelector('.tree-title');
    const chkLoc = head.querySelector('.chk-loc');
    const btnAll = head.querySelector('.sel-all');

    onActivate(twisty, ()=>{
      const open = !list.classList.contains('open');
      list.classList.toggle('open', open);
      twisty.textContent = open ? '▾' : '▸';
      twisty.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) expandedLocs.add(locId); else expandedLocs.delete(locId);
    });
    title.addEventListener('click', (e)=>{ if (e.target.tagName.toLowerCase()!=='input') twisty.click(); });

    chkLoc.addEventListener('change', ()=>{
      if (chkLoc.checked) {
        selectedLocs.add(locId);
        oltIdsOf(locId).forEach(id => selectedOlts.add(id));
      } else {
        selectedLocs.delete(locId);
        oltIdsOf(locId).forEach(id => selectedOlts.delete(id));
      }
      updateOltMarkersVisibility();
      list.querySelectorAll('.chk-olt').forEach(i => { i.checked = chkLoc.checked; });
      head.querySelector('.badge').textContent = `${countSelInLoc(locId).sel}/${countSelInLoc(locId).total}`;
    });

    onActivate(btnAll, ()=>{
      const st = countSelInLoc(locId);
      const mark = !(st.sel===st.total && st.total>0);
      if (mark) {
        oltIdsOf(locId).forEach(id => selectedOlts.add(id));
        selectedLocs.add(locId);
        list.querySelectorAll('.chk-olt').forEach(i => i.checked = true);
        chkLoc.checked = true;
      } else {
        oltIdsOf(locId).forEach(id => selectedOlts.delete(id));
        selectedLocs.delete(locId);
        list.querySelectorAll('.chk-olt').forEach(i => i.checked = false);
        chkLoc.checked = false;
      }
      updateOltMarkersVisibility();
      const st2 = countSelInLoc(locId);
      head.querySelector('.badge').textContent = `${st2.sel}/${st2.total}`;
    });

    list.querySelectorAll('.chk-olt').forEach(inp => {
      inp.addEventListener('change', ()=>{
        const id = parseInt(inp.value,10);
        if (inp.checked) selectedOlts.add(id); else selectedOlts.delete(id);
        const st = countSelInLoc(locId);
        chkLoc.checked = st.sel>0;
        if (st.sel===0) selectedLocs.delete(locId); else selectedLocs.add(locId);
        head.querySelector('.badge').textContent = `${st.sel}/${st.total}`;
        updateOltMarkersVisibility();
      });
    });

    return wrap;
  }

  function renderTree() {
    const q = (search.value||'').trim().toLowerCase();
    const only = onlySel.checked;
    tree.innerHTML = '';
    const locs = [...LOCATIONS].sort((a,b)=>String(a.name||'').localeCompare(String(b.name||'')));

    locs.forEach(l=>{
      const id = parseInt(l.id,10);
      if (only) {
        const {sel} = countSelInLoc(id);
        if (!selectedLocs.has(id) && sel===0) return;
      }
      if (q) {
        const locMatch = (l.name||'').toLowerCase().includes(q);
        const oltMatch = (OLT_BY_LOC[id]||[]).some(o => (o.name||('OLT #'+o.id)).toLowerCase().includes(q));
        if (!locMatch && !oltMatch) return;
        if (oltMatch) expandedLocs.add(id);
      }
      tree.appendChild(renderLocationBlock(l));
    });
  }

  onActivate(panel.querySelector('#f-expand'),   ()=>{ LOCATIONS.forEach(l=>expandedLocs.add(parseInt(l.id,10))); renderTree(); });
  onActivate(panel.querySelector('#f-collapse'), ()=>{ expandedLocs.clear(); renderTree(); });
  search.addEventListener('input', renderTree);
  onlySel.addEventListener('change', renderTree);

  // Маркери-орієнтири + клік по маркеру локації = автоселекція і завантаження
  locGroup = L.layerGroup().addTo(map);
  oltGroup = L.layerGroup().addTo(map);

  LOCATIONS.forEach(l=>{
    if(!l.lan || !l.lon) return;
    const m = L.marker([l.lan, l.lon]).bindTooltip(l.name, {direction:'top'});
    locMarkers[l.id] = m; 
    locGroup.addLayer(m);
    m.on('click', async ()=>{
      const locId = parseInt(l.id,10);
      setLocationOnly(locId);
      renderTree();
      updateOltMarkersVisibility();
      await fetchDataAndRender({ locations:[locId], olts: oltIdsOf(locId), limit:0 });
      tryCenterByLocations([locId]);
    });
  });

  Object.keys(OLT_BY_LOC).forEach(locId=>{
    (OLT_BY_LOC[locId]||[]).forEach(o=>{
      if(!o.lan || !o.lon) return;
      const m = L.marker([o.lan, o.lon]).bindTooltip((o.name||('OLT #'+o.id)), {direction:'top'});
      oltMarkers[o.id] = m; // додаємо/ховаємо через updateOltMarkersVisibility()
    });
  });

  // Нижні кнопки — єдині справжні <button>, як і просили
  panel.querySelector('#f-clear').addEventListener('click', ()=>{
    selectedLocs.clear(); selectedOlts.clear();
    if (CUR_LOC_ID) { selectedLocs.add(CUR_LOC_ID); oltIdsOf(CUR_LOC_ID).forEach(id => selectedOlts.add(id)); }
    updateOltMarkersVisibility();
    renderTree();
  });

  panel.querySelector('#f-apply').addEventListener('click', async ()=>{
    const selLoc = [...selectedLocs].map(Number);
    const selOlt = [...selectedOlts].map(Number);
    updateOltMarkersVisibility();
    try {
      await fetchDataAndRender({ locations: selLoc, olts: selOlt, limit: 0 });
      if (selLoc.length) tryCenterByLocations(selLoc);
    } catch(e) {
      console.error(e);
      if (selLoc.length) tryCenterByLocations(selLoc);
    }
  });

  renderTree();
  updateOltMarkersVisibility();
}


  function tryCenterByLocations(locIds) {
    if (!locIds || !locIds.length) return;
    const pts = [];
    locIds.forEach(id => {
      const l = LOCATIONS.find(x => x.id===id);
      if (l && l.lan && l.lon) pts.push([l.lan, l.lon]);
    });
    if (pts.length) map.fitBounds(pts, {padding:[30,30]});
  }

  function clearOnuLayers() {
    try { window.markers.forEach(m => map.removeLayer(m)); } catch(e) {}
    window.markers = [];
    window.onuLabels = [];
    window.onuIcons  = [];
  }

  function applyMapData(json) {
    clearOnuLayers();
    (json.onu || []).forEach(o => {
      window.makeOnuMarker(o.lan, o.lon, o);
    });
    if (labelLayer && labelLayer._requestRedraw) labelLayer._requestRedraw();
    if (iconLayer  && iconLayer._requestRedraw)  iconLayer._requestRedraw();

    const points = [];
    (json.onu || []).forEach(o => { points.push([o.lan, o.lon]); });
    if (!points.length && json.houses) json.houses.forEach(h => { points.push([h.lan, h.lon]); });
    if (points.length) map.fitBounds(points, {padding:[30,30]});
  }

  let lastAbort = null;
  async function fetchDataAndRender(payload) {
    if (lastAbort) lastAbort.abort();
    lastAbort = new AbortController();
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload || {}),
      signal: lastAbort.signal
    });
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'API error');
    applyMapData(json);
    return true;
  }

  // старт
  async function boot() {
    initMap();
    await loadMeta();
    buildUI();

    const initialLoc = CUR_LOC_ID ? [CUR_LOC_ID] : [];
    try {
      await fetchDataAndRender({ locations: initialLoc });
      if (!initialLoc.length && LOCATIONS.length) {
        const pts = LOCATIONS.filter(l => l.lan && l.lon).map(l => [l.lan, l.lon]);
        if (pts.length) map.fitBounds(pts, {padding:[30,30]});
      }
    } catch(e) {
      console.error(e);
      if (initialLoc.length) tryCenterByLocations(initialLoc);
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
