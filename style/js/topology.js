(function(){
  const apiUrl = '/ajax/ajaxtopology.php';
  const svg = d3.select('#topology-canvas');
  const root = svg.append('g');
  const switchLayer = root.append('g');
  const linkLayer = root.append('g');
  const defs = svg.append('defs');

  const VIEW_KEY = 'topology_view_v1';
  const zoom = d3.zoom().scaleExtent([0.2, 3]).on('zoom', (event) => {
    root.attr('transform', event.transform);
    // Persist viewport.
    try{
      localStorage.setItem(VIEW_KEY, JSON.stringify({ k: event.transform.k, x: event.transform.x, y: event.transform.y }));
    }catch(e){}
  });
  svg.call(zoom);

  const portSize = 30;
  const portGap = 12;
  const marginX = 18;
  const marginY = 64;
  const defaultPortsPerRow = 16;

  // Color customization (UP is configurable; DOWN is always red)
  const COLOR_UP_KEY = 'topology_color_up_v1';
  const COLOR_UP_STRONG_KEY = 'topology_color_up_strong_v1'; // legacy (kept)
  const COLOR_LINK_UP_KEY = 'topology_color_link_up_v1';
  const DEFAULT_UP = '#00c853';
  const DEFAULT_UP_STRONG = '#0a8f3c';
  const COLOR_DOWN = '#d32f2f';
  const COLOR_UNKNOWN = '#9e9e9e';

  function applyColorsFromStorage(){
    let up = DEFAULT_UP;
    let upStrong = DEFAULT_UP_STRONG;
    let linkUp = DEFAULT_UP_STRONG;
    try{
      const v = localStorage.getItem(COLOR_UP_KEY);
      if (v && /^#[0-9a-f]{6}$/i.test(v)) up = v;
      const vs = localStorage.getItem(COLOR_UP_STRONG_KEY);
      if (vs && /^#[0-9a-f]{6}$/i.test(vs)) upStrong = vs;
      const vl = localStorage.getItem(COLOR_LINK_UP_KEY);
      if (vl && /^#[0-9a-f]{6}$/i.test(vl)) linkUp = vl;
    }catch(e){}
    document.documentElement.style.setProperty('--topo-up', up);
    document.documentElement.style.setProperty('--topo-up-strong', upStrong);
    document.documentElement.style.setProperty('--topo-link-up', linkUp);
    document.documentElement.style.setProperty('--topo-down', COLOR_DOWN);
    document.documentElement.style.setProperty('--topo-unknown', COLOR_UNKNOWN);
  }

  let nodes = [];
  let links = [];
  let portMeta = {}; // key: "swUid:port"
  let selectedPort = null; // { sw, port }
  let selectedSwitchUid = null;
  let activePortForEdit = null;
  let activeSwitchForEdit = null;
  let activeLinkUid = null;
  const locateLinks = new Set(); // link uid => blinking highlight (local only)
  let addSwitchPos = null;
  let switchListCache = null;
  let switchPortsCache = new Map(); // switch_id -> ports[]
  let lastPointer = { clientX: 0, clientY: 0 };

  function keyFor(sw, port){ return sw + ':' + port; }
  function getNode(uid){ return nodes.find(n => n.uid === uid); }

  function portsPerRow(sw){
    const v = sw && sw.ports_per_row ? parseInt(sw.ports_per_row, 10) : defaultPortsPerRow;
    if(!Number.isFinite(v) || v < 1) return defaultPortsPerRow;
    return Math.min(32, v);
  }

  function switchWidth(sw){
    if (sw && sw.kind === 'element') return 160;
    const cols = Math.min(sw.ports, portsPerRow(sw));
    return marginX * 2 + cols * portSize + (cols - 1) * portGap;
  }
  function switchHeight(sw){
    if (sw && sw.kind === 'element') return 96;
    const rows = Math.ceil(sw.ports / portsPerRow(sw));
    return marginY + rows * (portSize + 22) + 16;
  }

  function nodePortOffsets(sw){
    if (sw && sw.kind === 'element') return { x0: 70, y0: 48 };
    return { x0: marginX, y0: marginY };
  }

  function portPosition(swUid, portNumber){
    const sw = getNode(swUid);
    const index = portNumber - 1;
    const off = nodePortOffsets(sw);
    let perRow = portsPerRow(sw);
    if (sw && sw.kind === 'element') perRow = Math.min(perRow || 1, sw.ports || 1) || 1;
    const col = index % perRow;
    const row = Math.floor(index / perRow);
    return {
      x: sw.x + off.x0 + col * (portSize + portGap) + portSize / 2,
      y: sw.y + off.y0 + row * (portSize + 22) + portSize / 2
    };
  }

  function portAnchor(swUid, portNumber, otherPoint){
    // Force link to "plug" only from top or bottom (no left/right).
    const sw = getNode(swUid);
    const index = portNumber - 1;
    const off = nodePortOffsets(sw);
    let perRow = portsPerRow(sw);
    if (sw && sw.kind === 'element') perRow = Math.min(perRow || 1, sw.ports || 1) || 1;
    const col = index % perRow;
    const row = Math.floor(index / perRow);
    const portX = sw.x + off.x0 + col * (portSize + portGap);
    const portY = sw.y + off.y0 + row * (portSize + 22);
    const cx = portX + portSize / 2;
    const cy = portY + portSize / 2;
    const exitTop = otherPoint ? (otherPoint.y < cy) : true;
    return { x: cx, y: exitTop ? (portY) : (portY + portSize), exitTop };
  }

  function switchExitY(swUid, exitTop){
    // Push link "forks" farther away from the switch body for readability.
    const sw = getNode(swUid);
    const clearance = 34;
    if(!sw) return exitTop ? 0 : 0;
    const topY = sw.y - clearance;
    const bottomY = sw.y + switchHeight(sw) + clearance;
    return exitTop ? topY : bottomY;
  }
  function isPortConnected(swUid, port){
    return links.some(l =>
      (l.from.sw === swUid && l.from.port === port) ||
      (l.to.sw === swUid && l.to.port === port)
    );
  }
  function samePort(a, b){ return a && b && a.sw === b.sw && a.port === b.port; }
  function canConnect(a, b){
    if(!a || !b) return false;
    if(samePort(a,b)) return false;
    if(isPortConnected(a.sw, a.port)) return false;
    if(isPortConnected(b.sw, b.port)) return false;
    return true;
  }

  function portStatusColor(sw, port){
    const meta = portMeta[keyFor(sw, port)];
    const hasSnmp = meta && meta.snmp_oid && meta.snmp_ip && meta.snmp_ro;
    if(!hasSnmp) return '#bdbdbd'; // grey
    const st = meta && meta.last_status != null ? meta.last_status : null;
    if(st === 1) return '#00c853';
    if(st === 2) return '#111111';
    return '#bdbdbd';
  }

  function portState(sw, port){
    const meta = portMeta[keyFor(sw, port)] || {};
    const bound = ((meta.bind_switch_id && meta.bind_llid) || (meta.snmp_oid && meta.snmp_ip && meta.snmp_ro)) ? true : false;
    if(!bound) return { bound:false, state:'unbound' };
    const st = meta.last_status != null ? meta.last_status : null;
    if(st === 1) return { bound:true, state:'active' };
    if(st === 2) return { bound:true, state:'inactive' };
    return { bound:true, state:'unknown' };
  }

  function makeLinkStroke(link){
    const s1 = portState(link.from.sw, link.from.port);
    const s2 = portState(link.to.sw, link.to.port);
    // Rules:
    // - if any side is inactive -> red
    // - if both active -> green
    // - otherwise -> grey
    if((s1.bound && s1.state === 'inactive') || (s2.bound && s2.state === 'inactive')) return COLOR_DOWN;
    if(s1.bound && s2.bound && s1.state === 'active' && s2.state === 'active') {
      if (link && link.color && /^#[0-9a-f]{6}$/i.test(String(link.color))) return String(link.color);
      const v = getComputedStyle(document.documentElement).getPropertyValue('--topo-link-up').trim()
        || getComputedStyle(document.documentElement).getPropertyValue('--topo-up-strong').trim();
      return v || DEFAULT_UP_STRONG;
    }
    return '#777';
  }

  // Debug helper: uncomment to see computed link state.
  // window.__topo_link_color = (l) => ({ from: portState(l.from.sw,l.from.port), to: portState(l.to.sw,l.to.port), color: makeLinkStroke(l) });

  function orthogonalPathTopBottom(a1, a2){
    const stub = 18;
    const p1 = { x: a1.x, y: a1.y };
    const p2 = { x: a2.x, y: a2.y };
    const dirY1 = a1.exitTop ? -1 : 1;
    const dirY2 = a2.exitTop ? -1 : 1;
    const b = { x: p1.x, y: p1.y + dirY1 * stub };
    const d = { x: p2.x, y: p2.y + dirY2 * stub };
    const c = { x: d.x, y: b.y };
    return `M${p1.x},${p1.y} L${b.x},${b.y} L${c.x},${c.y} L${d.x},${d.y} L${p2.x},${p2.y}`;
  }

  function routedPathTopBottom(a1, a2, laneY, trunkX, exitY1, exitY2){
    const p1 = { x: a1.x, y: a1.y };
    const p2 = { x: a2.x, y: a2.y };
    const b = { x: p1.x, y: (exitY1 != null ? exitY1 : p1.y) };
    const d = { x: p2.x, y: (exitY2 != null ? exitY2 : p2.y) };
    const y = (laneY != null) ? laneY : (b.y + d.y) / 2;
    const x = (trunkX != null) ? trunkX : b.x;
    // Each link gets its own "trunk" X -> different turn points, less overlaps.
    return `M${p1.x},${p1.y} L${b.x},${b.y} L${x},${b.y} L${x},${y} L${x},${d.y} L${d.x},${d.y} L${p2.x},${p2.y}`;
  }

  // --- Simple trunk routing (few bends) ---
  function simplifyPath(points){
    if(points.length <= 2) return points;
    const out = [points[0]];
    for(let i=1;i<points.length-1;i++){
      const a = out[out.length-1];
      const b = points[i];
      const c = points[i+1];
      const abx = b.x - a.x, aby = b.y - a.y;
      const bcx = c.x - b.x, bcy = c.y - b.y;
      if((abx === 0 && bcx === 0) || (aby === 0 && bcy === 0)) continue;
      out.push(b);
    }
    out.push(points[points.length-1]);
    return out;
  }

  function routeLinkBundle(a1, a2, exitY1, exitY2, busY){
    const pts = [
      { x: a1.x, y: a1.y },
      { x: a1.x, y: exitY1 },
      { x: a1.x, y: busY },
      { x: a2.x, y: busY },
      { x: a2.x, y: exitY2 },
      { x: a2.x, y: a2.y },
    ];
    return simplifyPath(pts);
  }

  function pathToD(points){
    if(!points || !points.length) return '';
    let d = `M${points[0].x},${points[0].y}`;
    for(let i=1;i<points.length;i++) d += ` L${points[i].x},${points[i].y}`;
    return d;
  }

  async function apiPost(params){
    const form = new URLSearchParams();
    Object.entries(params).forEach(([k,v]) => form.append(k, v == null ? '' : String(v)));
    const res = await fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, body: form.toString() });
    return res.json();
  }
  async function apiGet(params){
    const u = new URL(apiUrl, window.location.origin);
    Object.entries(params).forEach(([k,v]) => u.searchParams.set(k, v));
    const res = await fetch(u.toString(), { method:'GET' });
    return res.json();
  }

  function clampModal(modalId){
    const modal = document.getElementById(modalId);
    const wrap = document.getElementById('topology-wrap');
    if(!modal || !wrap) return;
    if(modal.style.display === 'none') return;
    // Convert right/bottom anchored modals into left/top so we can clamp.
    const wrapRect = wrap.getBoundingClientRect();
    const rect = modal.getBoundingClientRect();
    // Desired left/top relative to wrap.
    let left = rect.left - wrapRect.left;
    let top = rect.top - wrapRect.top;
    // Clamp inside wrap with margin.
    const margin = 8;
    const maxLeft = Math.max(margin, wrapRect.width - rect.width - margin);
    const maxTop = Math.max(margin, wrapRect.height - rect.height - margin);
    left = Math.min(Math.max(margin, left), maxLeft);
    top = Math.min(Math.max(margin, top), maxTop);
    modal.style.left = left + 'px';
    modal.style.top = top + 'px';
    modal.style.right = 'auto';
    modal.style.bottom = 'auto';
  }

  function placeModalNearPointer(modalId){
    // Modals are fixed+centered via CSS. No-op, kept for compatibility.
    // (This avoids off-screen issues on small PCs.)
    return;
  }

  function openPortModal(sw, port){
    activePortForEdit = { sw, port };
    const meta = portMeta[keyFor(sw, port)] || {};
    document.getElementById('topoPortTitle').textContent = 'Порт ' + sw + ':' + port;
    document.getElementById('topoPortLabel').value = meta.label || '';
    const bindSwitchId = meta.bind_switch_id != null ? String(meta.bind_switch_id) : '';
    const bindLlid = meta.bind_llid != null ? String(meta.bind_llid) : '';
    const bindMode = (bindSwitchId && bindLlid) ? 'switch' : 'manual';
    document.getElementById('topoBindMode').value = bindMode;
    document.getElementById('topoPortDesc').value = meta.description || '';
    document.getElementById('topoSnmpIp').value = meta.snmp_ip || '';
    document.getElementById('topoSnmpRo').value = meta.snmp_ro || '';
    document.getElementById('topoSnmpOid').value = meta.snmp_oid || '';
    document.getElementById('topoPortMeta').textContent = meta.last_polled_at ? ('Останній опит: ' + meta.last_polled_at + ', статус: ' + (meta.last_status === 1 ? 'UP' : meta.last_status === 2 ? 'DOWN' : 'N/A')) : '';
    document.getElementById('topoPortModal').style.display = 'block';
    placeModalNearPointer('topoPortModal');
    applyBindModeUI();
    if(bindMode === 'switch'){
      ensureSwitchesLoaded().then(() => {
        document.getElementById('topoBindSwitch').value = bindSwitchId;
        onBindSwitchChanged().then(() => {
          document.getElementById('topoBindPort').value = bindLlid;
          updateBindHint();
        });
      });
    }
  }
  function closePortModal(){
    document.getElementById('topoPortModal').style.display = 'none';
    activePortForEdit = null;
  }

  function applyBindModeUI(){
    const mode = document.getElementById('topoBindMode').value;
    const isSwitch = mode === 'switch';
    document.getElementById('topoBindSwitchBlock').style.display = isSwitch ? 'block' : 'none';
    // In switch binding mode we show manual fields but they are ignored by poller.
  }

  async function ensureSwitchesLoaded(){
    if(switchListCache) return switchListCache;
    const res = await apiGet({ act:'switches' });
    if(!res || !res.success) throw new Error('switches');
    switchListCache = res.switches || [];
    const sel = document.getElementById('topoBindSwitch');
    sel.innerHTML = '';
    sel.appendChild(new Option('— вибрати —', ''));
    switchListCache.forEach(sw => {
      const text = `${sw.place || ('ID ' + sw.id)}${sw.model ? ('  [' + sw.model + ']') : ''}${sw.netip ? ('  ' + sw.netip) : ''}`;
      sel.appendChild(new Option(text, String(sw.id)));
    });
    return switchListCache;
  }

  async function onBindSwitchChanged(){
    const id = parseInt(document.getElementById('topoBindSwitch').value, 10);
    const portSel = document.getElementById('topoBindPort');
    portSel.innerHTML = '';
    portSel.appendChild(new Option('— вибрати —', ''));
    if(!Number.isFinite(id) || id <= 0){
      updateBindHint();
      return;
    }
    let ports = switchPortsCache.get(id);
    if(!ports){
      const res = await apiGet({ act:'switch_ports', id: String(id) });
      if(!res || !res.success) throw new Error('switch_ports');
      ports = res.ports || [];
      switchPortsCache.set(id, ports);
    }
    ports.forEach(p => {
      const label = p.nameport || ('llid ' + p.llid);
      const text = `${label} (llid ${p.llid})${p.descrport ? (' - ' + p.descrport) : ''}`;
      portSel.appendChild(new Option(text, String(p.llid)));
    });
    updateBindHint();
  }

  function updateBindHint(){
    const mode = document.getElementById('topoBindMode').value;
    const hint = document.getElementById('topoBindHint');
    if(mode !== 'switch'){
      hint.textContent = '';
      return;
    }
    const sid = parseInt(document.getElementById('topoBindSwitch').value, 10);
    const llid = parseInt(document.getElementById('topoBindPort').value, 10);
    const sw = switchListCache ? switchListCache.find(s => parseInt(s.id,10) === sid) : null;
    if(!sw || !Number.isFinite(llid) || llid <= 0){
      hint.textContent = 'Вибери комутатор і порт. OID буде ifOperStatus (1.3.6.1.2.1.2.2.1.8.<llid>)';
      return;
    }
    const oid = `1.3.6.1.2.1.2.2.1.8.${llid}`;
    hint.textContent = `SNMP: ${sw.netip || ''} / ${sw.snmpro ? 'RO задано' : 'RO пусто'} / OID: ${oid}`;

    // Auto-fill manual fields from selected switch/port for convenience.
    document.getElementById('topoSnmpIp').value = sw.netip || '';
    document.getElementById('topoSnmpRo').value = sw.snmpro || '';
    document.getElementById('topoSnmpOid').value = oid;
  }

  async function onBindModeChanged(){
    applyBindModeUI();
    const mode = document.getElementById('topoBindMode').value;
    if(mode === 'switch'){
      try{
        await ensureSwitchesLoaded();
        await onBindSwitchChanged();
      }catch(e){}
    }
    updateBindHint();
  }

  function openSwitchModal(swUid){
    const sw = getNode(swUid);
    if(!sw) return;
    activeSwitchForEdit = swUid;
    const kindEl = document.getElementById('topoEditNodeKind');
    if (kindEl) kindEl.value = (sw.kind === 'element') ? 'element' : 'switch';
    document.getElementById('topoEditSwitchName').value = sw.name || '';
    document.getElementById('topoEditSwitchModel').value = sw.model || '';
    const iconEl = document.getElementById('topoEditNodeIconUrl');
    if (iconEl) iconEl.value = sw.icon_url || '';
    const openEl = document.getElementById('topoEditNodeOpenUrl');
    if (openEl) openEl.value = sw.open_url || '';
    document.getElementById('topoEditSwitchPorts').value = sw.ports || 12;
    document.getElementById('topoEditSwitchPortsPerRow').value = sw.ports_per_row || defaultPortsPerRow;
    const lockEl = document.getElementById('topoEditSwitchLocked');
    if (lockEl) lockEl.checked = !!(sw.locked && parseInt(sw.locked, 10) === 1);
    document.getElementById('topoSwitchMeta').textContent = swUid;
    document.getElementById('topoSwitchModal').style.display = 'block';
    placeModalNearPointer('topoSwitchModal');
  }
  function closeSwitchModal(){
    document.getElementById('topoSwitchModal').style.display = 'none';
    activeSwitchForEdit = null;
  }

  async function saveActiveSwitch(){
    if(!activeSwitchForEdit) return;
    const uid = activeSwitchForEdit;
    const name = document.getElementById('topoEditSwitchName').value || '';
    const model = document.getElementById('topoEditSwitchModel').value || '';
    const kind = document.getElementById('topoEditNodeKind') ? (document.getElementById('topoEditNodeKind').value || 'switch') : 'switch';
    const icon_url = document.getElementById('topoEditNodeIconUrl') ? (document.getElementById('topoEditNodeIconUrl').value || '') : '';
    const open_url = document.getElementById('topoEditNodeOpenUrl') ? (document.getElementById('topoEditNodeOpenUrl').value || '') : '';
    const ports = parseInt(document.getElementById('topoEditSwitchPorts').value, 10);
    const portsPerRow = parseInt(document.getElementById('topoEditSwitchPortsPerRow').value, 10);
    const locked = document.getElementById('topoEditSwitchLocked') ? (document.getElementById('topoEditSwitchLocked').checked ? 1 : 0) : 0;
    const res = await apiPost({
      act:'update_node', uid, name, model,
      ports: (Number.isFinite(ports) ? ports : 12),
      ports_per_row: (Number.isFinite(portsPerRow) ? portsPerRow : defaultPortsPerRow),
      kind,
      icon_url,
      open_url,
      locked
    });
    if(res && res.success){
      const sw = getNode(uid);
      if(sw){
        sw.name = name;
        sw.model = model || null;
        sw.ports = Number.isFinite(ports) ? ports : sw.ports;
        sw.ports_per_row = Number.isFinite(portsPerRow) ? portsPerRow : sw.ports_per_row;
        sw.kind = (kind === 'element') ? 'element' : 'switch';
        sw.icon_url = icon_url || null;
        sw.open_url = open_url || null;
        sw.locked = locked ? 1 : 0;
      }
      render();
      closeSwitchModal();
    }else{
      alert((res && res.message) ? res.message : 'Помилка збереження');
    }
  }

  async function saveActivePort(){
    if(!activePortForEdit) return;
    const sw = activePortForEdit.sw;
    const port = activePortForEdit.port;
    const bindMode = document.getElementById('topoBindMode').value;
    const bindSwitchId = bindMode === 'switch' ? parseInt(document.getElementById('topoBindSwitch').value, 10) : 0;
    const bindLlid = bindMode === 'switch' ? parseInt(document.getElementById('topoBindPort').value, 10) : 0;
    if(bindMode === 'switch' && (!(Number.isFinite(bindSwitchId) && bindSwitchId > 0) || !(Number.isFinite(bindLlid) && bindLlid > 0))){
      alert('Вибери комутатор і порт');
      return;
    }
    const payload = {
      act: 'upsert_port',
      sw, port,
      label: document.getElementById('topoPortLabel').value || '',
      description: document.getElementById('topoPortDesc').value || '',
      snmp_ip: document.getElementById('topoSnmpIp').value || '',
      snmp_ro: document.getElementById('topoSnmpRo').value || '',
      snmp_oid: document.getElementById('topoSnmpOid').value || '',
      bind_switch_id: (Number.isFinite(bindSwitchId) ? bindSwitchId : 0),
      bind_llid: (Number.isFinite(bindLlid) ? bindLlid : 0),
    };
    const res = await apiPost(payload);
    if(res && res.success){
      portMeta[keyFor(sw, port)] = portMeta[keyFor(sw, port)] || {};
      portMeta[keyFor(sw, port)].label = payload.label || null;
      portMeta[keyFor(sw, port)].description = payload.description || null;
      portMeta[keyFor(sw, port)].snmp_ip = payload.snmp_ip || null;
      portMeta[keyFor(sw, port)].snmp_ro = payload.snmp_ro || null;
      portMeta[keyFor(sw, port)].snmp_oid = payload.snmp_oid || null;
      portMeta[keyFor(sw, port)].bind_switch_id = payload.bind_switch_id > 0 ? payload.bind_switch_id : null;
      portMeta[keyFor(sw, port)].bind_llid = (payload.bind_switch_id > 0 && payload.bind_llid > 0) ? payload.bind_llid : null;
      render();
      closePortModal();
    } else {
      alert((res && res.message) ? res.message : 'Помилка збереження');
    }
  }

  async function handlePortClick(event, swUid, port){
    event.stopPropagation();
    lastPointer = { clientX: event.clientX, clientY: event.clientY };
    selectedSwitchUid = swUid;
    openPortModal(swUid, port);

    const clicked = { sw: swUid, port };
    if(!selectedPort){
      if(!isPortConnected(swUid, port)) selectedPort = clicked;
      render();
      return;
    }
    if(samePort(selectedPort, clicked)){
      selectedPort = null;
      render();
      return;
    }
    if(canConnect(selectedPort, clicked)){
      const res = await apiPost({
        act:'add_link',
        from_sw: selectedPort.sw,
        from_port: selectedPort.port,
        to_sw: clicked.sw,
        to_port: clicked.port
      });
      if(res && res.success){
        links.push({ uid: res.uid, from: selectedPort, to: clicked });
      }else{
        alert((res && res.message) ? res.message : 'Помилка створення лінка');
      }
    }
    selectedPort = null;
    render();
  }

  async function deleteLink(uid){
    if(!confirm('Видалити комутацію (лінк)?')) return;
    const res = await apiPost({ act:'delete_link', uid });
    if(res && res.success){
      links = links.filter(l => l.uid !== uid);
      selectedPort = null;
      render();
    } else {
      alert((res && res.message) ? res.message : 'Помилка видалення');
    }
  }

  function openLinkModal(uid){
    const l = links.find(x => x.uid === uid);
    if(!l) return;
    activeLinkUid = uid;
    document.getElementById('topoLinkMeta').textContent = l.from.sw + ':' + l.from.port + ' ↔ ' + l.to.sw + ':' + l.to.port;
    const linkColorEl = document.getElementById('topoLinkColor');
    if (linkColorEl) {
      const fallback = getComputedStyle(document.documentElement).getPropertyValue('--topo-link-up').trim() || '#0a8f3c';
      linkColorEl.value = (l.color && /^#[0-9a-f]{6}$/i.test(String(l.color))) ? String(l.color) : fallback;
    }
    document.getElementById('topoLinkLen').value = (l.length_m != null ? String(l.length_m) : '');
    document.getElementById('topoLinkDesc').value = l.description || '';
    document.getElementById('topoLinkModal').style.display = 'block';
    placeModalNearPointer('topoLinkModal');
  }
  function closeLinkModal(){
    document.getElementById('topoLinkModal').style.display = 'none';
    activeLinkUid = null;
  }
  async function saveActiveLink(){
    if(!activeLinkUid) return;
    const len = parseInt(document.getElementById('topoLinkLen').value, 10);
    const desc = document.getElementById('topoLinkDesc').value || '';
    const color = document.getElementById('topoLinkColor') ? (document.getElementById('topoLinkColor').value || '') : '';
    const res = await apiPost({
      act:'update_link',
      uid: activeLinkUid,
      length_m: (Number.isFinite(len) ? len : 0),
      color: color,
      description: desc
    });
    if(res && res.success){
      const l = links.find(x => x.uid === activeLinkUid);
      if(l){
        l.length_m = (Number.isFinite(len) && len > 0) ? len : null;
        l.color = (color && /^#[0-9a-f]{6}$/i.test(String(color))) ? String(color) : null;
        l.description = desc || null;
      }
      render();
      closeLinkModal();
    }else{
      alert((res && res.message) ? res.message : 'Помилка збереження');
    }
  }
  async function deleteActiveLink(){
    if(!activeLinkUid) return;
    const uid = activeLinkUid;
    await deleteLink(uid);
    closeLinkModal();
  }

  function openAddSwitchModal(clientX, clientY, x, y){
    addSwitchPos = { x, y };
    const modal = document.getElementById('topoAddSwitchModal');
    const wrap = document.getElementById('topology-wrap');
    const r = wrap.getBoundingClientRect();
    modal.style.left = Math.max(14, Math.min(r.width - 380, clientX - r.left)) + 'px';
    modal.style.top = Math.max(14, Math.min(r.height - 320, clientY - r.top)) + 'px';
    document.getElementById('topoAddSwitchMeta').textContent = `X: ${Math.round(x)}, Y: ${Math.round(y)}`;
    const kindEl = document.getElementById('topoNewNodeKind');
    if (kindEl) kindEl.value = 'switch';
    const iconEl = document.getElementById('topoNewNodeIconUrl');
    if (iconEl) iconEl.value = '';
    const openEl = document.getElementById('topoNewNodeOpenUrl');
    if (openEl) openEl.value = '';
    modal.style.display = 'block';
    clampModal('topoAddSwitchModal');
  }
  function closeAddSwitchModal(){
    document.getElementById('topoAddSwitchModal').style.display = 'none';
    addSwitchPos = null;
  }
  async function createSwitchFromModal(){
    if(!addSwitchPos) return;
    const kind = document.getElementById('topoNewNodeKind') ? (document.getElementById('topoNewNodeKind').value || 'switch') : 'switch';
    const name = (document.getElementById('topoNewSwitchName').value || 'Switch').trim();
    const model = (document.getElementById('topoNewSwitchModel').value || '').trim();
    const icon_url = document.getElementById('topoNewNodeIconUrl') ? (document.getElementById('topoNewNodeIconUrl').value || '').trim() : '';
    const open_url = document.getElementById('topoNewNodeOpenUrl') ? (document.getElementById('topoNewNodeOpenUrl').value || '').trim() : '';
    let ports = parseInt(document.getElementById('topoNewSwitchPorts').value, 10);
    let ppr = parseInt(document.getElementById('topoNewSwitchPortsPerRow').value, 10);
    if(!Number.isFinite(ports) || ports < 1) ports = 12;
    if(ports > 96) ports = 96;
    if(!Number.isFinite(ppr) || ppr < 1) ppr = defaultPortsPerRow;
    if(ppr > 32) ppr = 32;
    if(kind === 'element'){
      if (!Number.isFinite(ports) || ports < 1) ports = 1;
      if (ports > 8) ports = 8;
      ppr = Math.min(ppr, ports) || ports;
    }
    const x = addSwitchPos.x;
    const y = addSwitchPos.y;
    const res = await apiPost({ act:'add_node', name, model, ports, ports_per_row: ppr, kind, icon_url, open_url, x, y });
    if(res && res.success){
      nodes.push({ uid: res.uid, name, model: (model || null), ports, ports_per_row: ppr, kind: (kind === 'element' ? 'element' : 'switch'), icon_url: (icon_url || null), open_url: (open_url || null), locked: 0, x, y });
      render();
      closeAddSwitchModal();
    }else{
      alert((res && res.message) ? res.message : 'Помилка додавання');
    }
  }

  async function deleteSelectedSwitch(){
    const uid = activeSwitchForEdit || selectedSwitchUid;
    if(!uid){
      alert('Не вибрано свіч');
      return;
    }
    if(!confirm('Видалити свіч ' + uid + ' і всі його з\\u0027єднання?')) return;
    const res = await apiPost({ act:'delete_node', uid });
    if(res && res.success){
      nodes = nodes.filter(n => n.uid !== uid);
      links = links.filter(l => l.from.sw !== uid && l.to.sw !== uid);
      Object.keys(portMeta).forEach(k => { if(k.startsWith(uid + ':')) delete portMeta[k]; });
      selectedSwitchUid = null;
      selectedPort = null;
      closePortModal();
      closeSwitchModal();
      render();
    }else{
      alert((res && res.message) ? res.message : 'Помилка видалення');
    }
  }

  function resetZoom(){
    svg.transition().duration(200).call(zoom.transform, d3.zoomIdentity);
  }

  function getNodesBounds(){
    if(!nodes.length) return null;
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    nodes.forEach(n => {
      const w = switchWidth(n);
      const h = switchHeight(n);
      minX = Math.min(minX, n.x);
      minY = Math.min(minY, n.y);
      maxX = Math.max(maxX, n.x + w);
      maxY = Math.max(maxY, n.y + h);
    });
    return { minX, minY, maxX, maxY, w: maxX - minX, h: maxY - minY };
  }

  function centerToNodes(){
    const b = getNodesBounds();
    if(!b) return;
    const box = svg.node().getBoundingClientRect();
    const vw = box.width || 800;
    const vh = box.height || 600;
    const pad = 60;
    const k = Math.max(0.2, Math.min(3, Math.min((vw - pad) / b.w, (vh - pad) / b.h)));
    const cx = b.minX + b.w / 2;
    const cy = b.minY + b.h / 2;
    const tx = vw / 2 - cx * k;
    const ty = vh / 2 - cy * k;
    svg.call(zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(k));
  }

  function restoreViewOrCenter(){
    let saved = null;
    try{ saved = JSON.parse(localStorage.getItem(VIEW_KEY) || 'null'); }catch(e){}
    if(saved && typeof saved.k === 'number' && typeof saved.x === 'number' && typeof saved.y === 'number'){
      svg.call(zoom.transform, d3.zoomIdentity.translate(saved.x, saved.y).scale(saved.k));
      return;
    }
    centerToNodes();
  }

  function renderLinks(mode = 'full'){
    const sorted = [...links].sort((a,b) => String(a.uid).localeCompare(String(b.uid)));
    const routeByUid = new Map();
    const linkIndex = new Map();
    sorted.forEach((l, i) => linkIndex.set(l.uid, i));

    if(mode === 'fast'){
      // Minimal routes during drag: very cheap.
      for(const l of sorted){
        const fromCenter = portPosition(l.from.sw, l.from.port);
        const toCenter = portPosition(l.to.sw, l.to.port);
        const a1 = portAnchor(l.from.sw, l.from.port, toCenter);
        const a2 = portAnchor(l.to.sw, l.to.port, fromCenter);
        const e1 = switchExitY(l.from.sw, a1.exitTop);
        const e2 = switchExitY(l.to.sw, a2.exitTop);
        const midY = (e1 + e2) / 2;
        routeByUid.set(l.uid, simplifyPath([
          { x: a1.x, y: a1.y },
          { x: a1.x, y: e1 },
          { x: a1.x, y: midY },
          { x: a2.x, y: midY },
          { x: a2.x, y: e2 },
          { x: a2.x, y: a2.y },
        ]));
      }
    } else {

      // Group links by switch-pair to share a clean trunk and use per-link offsets.
      const groups = new Map(); // key -> links[]
      for(const l of sorted){
        const key = [l.from.sw, l.to.sw].sort().join('|');
        const arr = groups.get(key) || [];
        arr.push(l);
        groups.set(key, arr);
      }

      for(const [key, arr] of groups.entries()){
        const center = (arr.length - 1) / 2;
        const busSpacing = 16;

        const exits = arr.map(l => {
          const fromCenter = portPosition(l.from.sw, l.from.port);
          const toCenter = portPosition(l.to.sw, l.to.port);
          const a1 = portAnchor(l.from.sw, l.from.port, toCenter);
          const a2 = portAnchor(l.to.sw, l.to.port, fromCenter);
          const e1 = switchExitY(l.from.sw, a1.exitTop);
          const e2 = switchExitY(l.to.sw, a2.exitTop);
          return { l, a1, a2, e1, e2, bothTop: a1.exitTop && a2.exitTop, bothBottom: (!a1.exitTop) && (!a2.exitTop) };
        });

        const anyTop = exits.some(x => x.bothTop);
        const anyBottom = exits.some(x => x.bothBottom);
        let baseBusY;
        if(anyTop && !anyBottom){
          baseBusY = Math.min(...exits.map(x => Math.min(x.e1, x.e2))) - 80;
        } else if(anyBottom && !anyTop){
          baseBusY = Math.max(...exits.map(x => Math.max(x.e1, x.e2))) + 80;
        } else {
          baseBusY = exits.reduce((s,x) => s + (x.e1 + x.e2)/2, 0) / Math.max(1, exits.length);
        }

        exits.forEach((x, i) => {
          const idxOff = (i - center);
          const busY = baseBusY + idxOff * busSpacing;
          routeByUid.set(x.l.uid, routeLinkBundle(x.a1, x.a2, x.e1, x.e2, busY));
        });
      }
    }

    const paths = linkLayer.selectAll('path.link').data(links, d => d.uid);
    paths.enter()
      .append('path')
      .attr('class','link')
      .on('click', (event, d) => { event.stopPropagation(); lastPointer = { clientX: event.clientX, clientY: event.clientY }; openLinkModal(d.uid); })
      .on('contextmenu', (event, d) => {
        event.preventDefault();
        event.stopPropagation();
        if (locateLinks.has(d.uid)) locateLinks.delete(d.uid);
        else locateLinks.add(d.uid);
        renderLinks(); // re-class
      })
      .merge(paths)
      .attr('id', d => 'link_' + d.uid)
      .attr('d', d => pathToD(routeByUid.get(d.uid) || []))
      .style('stroke', d => makeLinkStroke(d))
      .classed('blink-red', d => makeLinkStroke(d) === '#d32f2f')
      .classed('locate', d => locateLinks.has(d.uid))
      .style('stroke-width', d => locateLinks.has(d.uid) ? 9 : 6)
      .each(function(d){
        const el = d3.select(this);
        el.selectAll('title').remove();
        const parts = [];
        if(d.length_m != null && d.length_m > 0) parts.push('Довжина: ' + d.length_m + ' м');
        if(d.description) parts.push('Опис: ' + d.description);
        if(parts.length) el.append('title').text(parts.join('\\n'));
      });
    paths.exit().remove();

    const labelsData = (mode === 'fast') ? [] : links.filter(l => l.length_m != null && l.length_m > 0);
    const labels = linkLayer.selectAll('text.link-label').data(labelsData, d => d.uid);
    labels.enter()
      .append('text')
      .attr('class', 'link-label')
      .style('fill', '#1565c0')
      .style('font-size', '12px')
      .style('font-weight', '600')
      .style('pointer-events', 'none')
      .merge(labels)
      .attr('x', d => {
        const path = document.getElementById('link_' + d.uid);
        if(path && path.getTotalLength){
          const p = path.getPointAtLength(path.getTotalLength() / 2);
          return p.x;
        }
        const p1 = portPosition(d.from.sw, d.from.port);
        const p2 = portPosition(d.to.sw, d.to.port);
        return (p1.x + p2.x) / 2;
      })
      .attr('y', d => {
        const path = document.getElementById('link_' + d.uid);
        if(path && path.getTotalLength){
          const len = path.getTotalLength();
          const p = path.getPointAtLength(len / 2);
          const p2 = path.getPointAtLength(Math.min(len, len/2 + 1));
          const dx = p2.x - p.x;
          const dy = p2.y - p.y;
          const l = Math.sqrt(dx*dx + dy*dy) || 1;
          const oy = (dx / l) * 10;
          const i = linkIndex.get(d.uid) || 0;
          const dir = (i % 2 === 0) ? 1 : -1;
          const step = Math.ceil((i + 1) / 2);
          return p.y + oy + dir * step * 10;
        }
        const p1 = portPosition(d.from.sw, d.from.port);
        const p2 = portPosition(d.to.sw, d.to.port);
        return (p1.y + p2.y) / 2;
      })
      .text(d => d.length_m + 'м');
    labels.exit().remove();
  }

  function renderSwitches(){
    const groups = switchLayer.selectAll('g.switch').data(nodes, d => d.uid);
    const enter = groups.enter().append('g')
      .attr('class','switch')
      .on('click', (event, d) => { event.stopPropagation(); lastPointer = { clientX: event.clientX, clientY: event.clientY }; selectedSwitchUid = d.uid; openSwitchModal(d.uid); render(); })
      .on('dblclick', (event, d) => {
        event.stopPropagation();
        if (d && d.open_url) {
          try { window.open(String(d.open_url), '_blank', 'noopener'); } catch(e) {}
        }
      })
      .call(d3.drag()
        .on('start', function(event, d){
          if (d && d.locked && parseInt(d.locked, 10) === 1) return;
          d3.select(this).raise();
        })
        .on('drag', function(event, d){
          if (d && d.locked && parseInt(d.locked, 10) === 1) return;
          d.x += event.dx;
          d.y += event.dy;
          d3.select(this).attr('transform', `translate(${d.x},${d.y})`);
          renderLinks('fast');
        })
        .on('end', async function(event, d){
          if (d && d.locked && parseInt(d.locked, 10) === 1) return;
          await apiPost({ act:'save_node_pos', uid:d.uid, x:Math.round(d.x), y:Math.round(d.y) });
          render();
        })
      );
    enter.append('rect').attr('class','switch-body');
    enter.append('image').attr('class','node-icon').attr('preserveAspectRatio', 'xMidYMid meet');
    enter.append('text').attr('class','switch-title');
    enter.append('g').attr('class','ports-layer');

    const merged = enter.merge(groups);
    merged.attr('transform', d => `translate(${d.x},${d.y})`);
    merged.select('rect.switch-body')
      .attr('width', d => switchWidth(d))
      .attr('height', d => switchHeight(d))
      .attr('stroke', d => {
        if (d && d.kind === 'element' && (d.ports || 0) === 1) {
          const st = portState(d.uid, 1);
          if (st.bound && st.state === 'inactive') return '#d32f2f';
          if (st.bound && st.state === 'active') return '#0a8f3c';
          return (d.uid === selectedSwitchUid ? '#ff8f00' : '#111');
        }
        return (d.uid === selectedSwitchUid ? '#ff8f00' : '#111');
      })
      .attr('fill', d => {
        if (d && d.kind === 'element' && (d.ports || 0) === 1) {
          const st = portState(d.uid, 1);
          if (st.bound && st.state === 'inactive') return '#ffebee';
          if (st.bound && st.state === 'active') return '#e8f5e9';
          return '#fafafa';
        }
        return null;
      });

    merged.select('image.node-icon')
      .attr('x', 12)
      .attr('y', 34)
      .attr('width', 40)
      .attr('height', 40)
      .attr('opacity', d => (d.kind === 'element' && d.icon_url) ? 1 : 0)
      .attr('href', d => (d.kind === 'element' ? (d.icon_url || '') : ''));

    merged.select('text.switch-title')
      .attr('x', 16)
      .attr('y', 24)
      .each(function(d){
        const text = d3.select(this);
        text.selectAll('*').remove();
        if (d.kind === 'element') {
          text.append('tspan').text(d.name || '');
        } else {
          text.append('tspan').text(d.name || '');
        }
        if(d.model){
          text.append('tspan')
            .attr('dx', 8)
            .attr('class', 'switch-model')
            .text(String(d.model));
        }
      });

    merged.each(function(sw){
      const portData = d3.range(1, sw.ports + 1).map(port => ({ swUid: sw.uid, port }));
      const portsLayer = d3.select(this).select('g.ports-layer');
      const portGroups = portsLayer.selectAll('g.port-group').data(portData, d => d.port);

      const pe = portGroups.enter().append('g').attr('class','port-group')
        .on('click', (event, d) => handlePortClick(event, d.swUid, d.port))
        .on('mouseover', function(){ d3.select(this).select('rect.port').classed('hover', true); })
        .on('mouseout', function(){ d3.select(this).select('rect.port').classed('hover', false); });

      pe.append('text').attr('class','port-label');
      pe.append('rect').attr('class','port port-shell');
      pe.append('rect').attr('class','port-side');

      const pm = pe.merge(portGroups);
      pm.attr('transform', d => {
        const swObj = getNode(d.swUid);
        let perRow = portsPerRow(swObj);
        const i = d.port - 1;
        if (swObj && swObj.kind === 'element') perRow = Math.min(perRow || 1, swObj.ports || 1) || 1;
        const col = i % perRow;
        const row = Math.floor(i / perRow);
        const x0 = (swObj && swObj.kind === 'element') ? 70 : marginX;
        const y0 = (swObj && swObj.kind === 'element') ? 48 : marginY;
        const x = x0 + col * (portSize + portGap);
        const y = y0 + row * (portSize + 22);
        return `translate(${x},${y})`;
      });
      pm.select('text.port-label').attr('x', portSize/2).attr('y', -8);
      pm.select('rect.port-shell')
        .attr('width', portSize)
        .attr('height', portSize)
        .attr('rx', 3)
        .attr('ry', 3)
        .attr('class', d => {
          const ps = portState(d.swUid, d.port);
          const sel = samePort(selectedPort, { sw: d.swUid, port: d.port });
          const base = ['port', 'port-shell'];
          if(ps.bound) base.push('bound');
          if(ps.state === 'active') base.push('active');
          if(ps.state === 'inactive') base.push('inactive');
          if(sel) base.push('selected');
          return base.join(' ');
        })
        .attr('fill', d => {
          const ps = portState(d.swUid, d.port);
          if(!ps.bound) return '#bdbdbd';
          if(ps.state === 'active') return '#e8f5e9';
          if(ps.state === 'inactive') return '#ffebee';
          return '#eeeeee';
        });

      pm.select('rect.port-side')
        .attr('width', 10)
        .attr('height', 10)
        .attr('x', (portSize - 10) / 2)
        .attr('y', (portSize - 10) / 2)
        .attr('rx', 2)
        .attr('ry', 2)
        .attr('class', d => {
          const ps = portState(d.swUid, d.port);
          const cls = ['port-side'];
          if(ps.bound && ps.state === 'active') cls.push('active');
          if(ps.bound && ps.state === 'inactive') cls.push('inactive');
          return cls.join(' ');
        })
        .attr('fill', d => {
          const ps = portState(d.swUid, d.port);
          if(!ps.bound) return 'transparent';
          if(ps.state === 'active') return (getComputedStyle(document.documentElement).getPropertyValue('--topo-up').trim() || DEFAULT_UP);
          if(ps.state === 'inactive') return COLOR_DOWN;
          return COLOR_UNKNOWN;
        });

      pm.select('text.port-label')
        .text(d => {
          const meta = portMeta[keyFor(d.swUid, d.port)];
          const label = meta && meta.label ? String(meta.label) : String(d.port);
          return label;
        });

      portGroups.exit().remove();
    });

    groups.exit().remove();
  }

  function render(){
    renderLinks('full');
    renderSwitches();
  }

  async function loadAll(){
    const res = await apiGet({ act:'load' });
    if(!res || !res.success){
      alert((res && res.message) ? res.message : 'Не вдалося завантажити топологію');
      return;
    }
    nodes = res.nodes || [];
    links = res.links || [];
    portMeta = res.ports || {};
    render();
    restoreViewOrCenter();
  }

  async function refreshStatus(){
    const res = await apiGet({ act:'status' });
    if(res && res.success && res.ports){
      Object.keys(res.ports).forEach(k => {
        portMeta[k] = portMeta[k] || {};
        portMeta[k].last_status = res.ports[k].last_status;
        portMeta[k].last_polled_at = res.ports[k].last_polled_at;
      });
      render();
    }
  }

  // --- Redis-backed events (via AJAX ring-buffer)
  let eventsLastId = 0;
  const EVENTS_KEY = 'topology_events_last_id_v1';
  let toastHost = null;

  function ensureToastHost(){
    if(toastHost) return toastHost;
    const wrap = document.getElementById('topology-wrap');
    if(!wrap) return null;
    toastHost = document.createElement('div');
    toastHost.className = 'topo-toasts';
    wrap.appendChild(toastHost);
    return toastHost;
  }

  function swNameByUid(uid){
    const n = nodes && nodes.find(x => x.uid === uid);
    return n ? (n.name || uid) : uid;
  }

  function toast(status, text, meta){
    const host = ensureToastHost();
    if(!host) return;
    const el = document.createElement('div');
    const cls = status === 1 ? 't-up' : status === 2 ? 't-down' : 't-unk';
    el.className = 'topo-toast ' + cls;
    el.innerHTML = `<div>${escapeHtml(text)}</div>` + (meta ? `<div class="t-muted">${escapeHtml(meta)}</div>` : '');
    host.appendChild(el);
    // Keep last 6
    while(host.children.length > 6) host.removeChild(host.firstChild);
    setTimeout(() => { try{ el.remove(); }catch(e){} }, 7000);
  }

  function escapeHtml(s){
    return String(s || '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  async function pollEvents(){
    // Avoid extra work when tab is hidden.
    if (document.hidden) return;
    try{
      const res = await apiPost({ act:'events', last_id: eventsLastId });
      if(!res || !res.success) return;
      if(Array.isArray(res.events)){
        res.events.forEach(ev => {
          if(ev && ev.type === 'port_status'){
            const sw = String(ev.sw || '');
            const port = parseInt(ev.port, 10) || 0;
            const status = parseInt(ev.status, 10) || 0;
            const prev = parseInt(ev.prev, 10) || 0;
            const ts = String(ev.ts || '');
            if(sw && port > 0 && (status === 1 || status === 2) && (prev === 1 || prev === 2)){
              const name = swNameByUid(sw);
              toast(status, `${name} порт ${port}: ${status === 1 ? 'UP' : 'DOWN'}`, ts);
              // Update local meta for immediate redraw.
              const key = keyFor(sw, port);
              portMeta[key] = portMeta[key] || {};
              portMeta[key].last_status = status;
              portMeta[key].last_polled_at = ts;
            }
          }
        });
      }
      if(typeof res.last_id === 'number') eventsLastId = res.last_id;
      if(res.last_id != null) eventsLastId = parseInt(res.last_id, 10) || eventsLastId;
      try{ localStorage.setItem(EVENTS_KEY, String(eventsLastId)); }catch(e){}
    }catch(e){}
  }

  svg.on('click', () => { selectedPort = null; render(); });
  svg.on('contextmenu', (event) => {
    event.preventDefault();
    // Convert screen coords to SVG world coords under current zoom.
    const t = d3.zoomTransform(svg.node());
    const [sx, sy] = d3.pointer(event, svg.node());
    const x = (sx - t.x) / t.k;
    const y = (sy - t.y) / t.k;
    openAddSwitchModal(event.clientX, event.clientY, x, y);
  });

  document.getElementById('topoResetZoom').addEventListener('click', resetZoom);
  document.getElementById('topoClosePortModal').addEventListener('click', closePortModal);
  document.getElementById('topoSavePort').addEventListener('click', saveActivePort);
  document.getElementById('topoBindMode').addEventListener('change', onBindModeChanged);
  document.getElementById('topoBindSwitch').addEventListener('change', () => { onBindSwitchChanged(); });
  document.getElementById('topoBindPort').addEventListener('change', updateBindHint);
  document.getElementById('topoCloseSwitchModal').addEventListener('click', closeSwitchModal);
  document.getElementById('topoSaveSwitch').addEventListener('click', saveActiveSwitch);
  document.getElementById('topoDeleteSwitchInModal').addEventListener('click', deleteSelectedSwitch);
  document.getElementById('topoCloseLinkModal').addEventListener('click', closeLinkModal);
  document.getElementById('topoSaveLink').addEventListener('click', saveActiveLink);
  document.getElementById('topoDeleteLink').addEventListener('click', deleteActiveLink);
  document.getElementById('topoCloseAddSwitchModal').addEventListener('click', closeAddSwitchModal);
  document.getElementById('topoCreateSwitch').addEventListener('click', createSwitchFromModal);

  // Theme toggle (day/night)
  const THEME_KEY = 'topology_theme_v1';
  const themeBtn = document.getElementById('topoThemeToggle');
  function setTheme(mode){
    const isDark = mode === 'dark';
    document.body.classList.toggle('topo-theme-dark', isDark);
    try{ localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light'); }catch(e){}
    if(themeBtn){
      themeBtn.textContent = isDark ? 'Тема: ніч' : 'Тема: день';
      themeBtn.title = 'Перемкнути тему';
    }
  }
  if(themeBtn){
    themeBtn.addEventListener('click', () => {
      const next = document.body.classList.contains('topo-theme-dark') ? 'light' : 'dark';
      setTheme(next);
    });
  }
  try{
    const saved = localStorage.getItem(THEME_KEY);
    setTheme(saved === 'dark' ? 'dark' : 'light');
  }catch(e){
    setTheme('light');
  }

  // Fullscreen (topology only)
  const fsBtn = document.getElementById('topoFullscreenToggle');
  const fsEl = document.getElementById('topology-wrap');
  function updateFsLabel(){
    if(!fsBtn) return;
    fsBtn.textContent = document.fullscreenElement ? 'Вийти з екрану' : 'На весь екран';
  }
  if(fsBtn && fsEl){
    fsBtn.addEventListener('click', async () => {
      try{
        if(document.fullscreenElement){
          await document.exitFullscreen();
        }else{
          await fsEl.requestFullscreen();
        }
      }catch(e){}
      updateFsLabel();
    });
    document.addEventListener('fullscreenchange', updateFsLabel);
    updateFsLabel();
  }

  // Colors (UP customizable)
  applyColorsFromStorage();
  const colorsBtn = document.getElementById('topoColorsToggle');
  const colorsModal = document.getElementById('topoColorsModal');
  function openColorsModal(){
    if (!colorsModal) return;
    const up = getComputedStyle(document.documentElement).getPropertyValue('--topo-up').trim() || DEFAULT_UP;
    const linkUp = getComputedStyle(document.documentElement).getPropertyValue('--topo-link-up').trim() || DEFAULT_UP_STRONG;
    const upEl = document.getElementById('topoColorUp');
    if (upEl) upEl.value = up;
    const linkEl = document.getElementById('topoColorLinkUp');
    if (linkEl) linkEl.value = linkUp;
    colorsModal.style.display = 'block';
    clampModal('topoColorsModal');
  }
  function closeColorsModal(){
    if (!colorsModal) return;
    colorsModal.style.display = 'none';
  }
  async function saveColors(){
    const upEl = document.getElementById('topoColorUp');
    const up = upEl ? String(upEl.value || '').trim() : '';
    const linkEl = document.getElementById('topoColorLinkUp');
    const linkUp = linkEl ? String(linkEl.value || '').trim() : '';
    if (up && /^#[0-9a-f]{6}$/i.test(up)) { try{ localStorage.setItem(COLOR_UP_KEY, up); }catch(e){} }
    if (linkUp && /^#[0-9a-f]{6}$/i.test(linkUp)) { try{ localStorage.setItem(COLOR_LINK_UP_KEY, linkUp); }catch(e){} }
    applyColorsFromStorage();
    render();
    closeColorsModal();
  }
  function resetColors(){
    try{ localStorage.removeItem(COLOR_UP_KEY); localStorage.removeItem(COLOR_UP_STRONG_KEY); }catch(e){}
    try{ localStorage.removeItem(COLOR_LINK_UP_KEY); }catch(e){}
    applyColorsFromStorage();
    render();
    closeColorsModal();
  }
  // keep helper (may be useful later)
  function darkenHex(hex, amount){
    const h = String(hex || '').replace('#','');
    if (h.length !== 6) return DEFAULT_UP_STRONG;
    const r = parseInt(h.slice(0,2),16);
    const g = parseInt(h.slice(2,4),16);
    const b = parseInt(h.slice(4,6),16);
    const f = (v) => Math.max(0, Math.min(255, Math.round(v*(1-amount))));
    const to = (v) => v.toString(16).padStart(2,'0');
    return '#' + to(f(r)) + to(f(g)) + to(f(b));
  }
  if (colorsBtn) colorsBtn.addEventListener('click', openColorsModal);
  const closeColorsBtn = document.getElementById('topoCloseColorsModal');
  if (closeColorsBtn) closeColorsBtn.addEventListener('click', closeColorsModal);
  const saveColorsBtn = document.getElementById('topoSaveColors');
  if (saveColorsBtn) saveColorsBtn.addEventListener('click', saveColors);
  const resetColorsBtn = document.getElementById('topoResetColors');
  if (resetColorsBtn) resetColorsBtn.addEventListener('click', resetColors);

  window.addEventListener('resize', () => {
    clampModal('topoPortModal');
    clampModal('topoSwitchModal');
    clampModal('topoLinkModal');
    clampModal('topoAddSwitchModal');
    clampModal('topoColorsModal');
  });

  // Clamp modals after any scroll inside the wrap (small screens / touchpads).
  document.getElementById('topology-wrap').addEventListener('scroll', () => {
    clampModal('topoPortModal');
    clampModal('topoSwitchModal');
    clampModal('topoLinkModal');
    clampModal('topoAddSwitchModal');
    clampModal('topoColorsModal');
  }, { passive: true });

  loadAll();
  setInterval(refreshStatus, 15000);
  try{
    eventsLastId = parseInt(localStorage.getItem(EVENTS_KEY) || '0', 10) || 0;
  }catch(e){ eventsLastId = 0; }
  setInterval(pollEvents, 2000);
})();
