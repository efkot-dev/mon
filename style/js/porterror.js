(function () {
  const cfg = window.PMON_PORTERROR || {};
  const t = cfg.i18n || cfg || {};
  const $ = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));

  const nf = (n) => (n || 0).toLocaleString("uk-UA");
  const esc = (v) =>
    String(v ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;");

  const state = {
    range: "today",
    deviceId: null,
    deviceName: "",
    ports: [],
    series: {},
    switches: [],
  };

  const portsCache = new Map();
  const seriesCache = new Map();
  const chartMap = new Map();

  function destroyCharts() {
    chartMap.forEach((ch) => {
      try {
        ch.destroy();
      } catch (_) {}
    });
    chartMap.clear();
  }

  function setHint(text, isError) {
    const box = $("#chartsHint");
    if (!box) return;
    box.classList.remove("is-hidden", "is-error");
    if (isError) box.classList.add("is-error");
    box.textContent = text || "";
  }

  function hideHint() {
    const box = $("#chartsHint");
    if (box) box.classList.add("is-hidden");
  }

  async function loadSwitches() {
    const box = $("#switchList");
    if (!box) return;
    box.innerHTML = "";

    try {
      const r = await fetch("ajax/ajaxerror.php?act=list_switches");
      const j = await r.json();
      if (!j.ok) {
        box.innerHTML = `<div class="porterror-msg-error">${t.loadFail || "Помилка завантаження"}</div>`;
        return;
      }

      state.switches = (j.switches || []).filter((sw) => (+sw.sum_err_today || 0) > 0);
      renderSwitches("");

      if (!state.deviceId && state.switches.length > 0) {
        selectSwitch(state.switches[0]);
      }
    } catch (e) {
      console.error(e);
      box.innerHTML = `<div class="porterror-msg-error">${t.loadFail || "Помилка завантаження"}</div>`;
    }
  }

  function renderSwitches(filterText) {
    const box = $("#switchList");
    if (!box) return;
    box.innerHTML = "";

    const q = (filterText || "").toLowerCase();
    const rows = state.switches.filter((sw) => String(sw.place || "").toLowerCase().includes(q));

    if (!rows.length) {
      box.innerHTML = `<div class="porterror-msg">${t.noSwitches || "Немає комутаторів з моніторингом."}</div>`;
      destroyCharts();
      const grid = $("#portChartsGrid");
      if (grid) grid.innerHTML = "";
      setHint(t.noSwitches || "Немає комутаторів з моніторингом.", false);
      return;
    }

    rows.forEach((sw) => {
      const active = Number(sw.id) === Number(state.deviceId);
      const el = document.createElement("button");
      el.type = "button";
      el.className = active ? "sw-card sw-card-active" : "sw-card";
      el.innerHTML = `
        <div class="sw-title">${esc(sw.place)}</div>
        <div class="sw-sub">${esc((sw.inf || "") + " " + (sw.model || ""))}</div>
        <div class="sw-metrics">
          <span>Порти: <b>${nf(+sw.ports_monitored || 0)}</b></span>
          <span>З помилками: <b>${nf(+sw.ports_with_err_today || 0)}</b></span>
          <span class="sw-err">+${nf(+sw.sum_err_today || 0)}</span>
        </div>
      `;
      el.addEventListener("click", () => selectSwitch(sw));
      box.appendChild(el);
    });
  }

  async function fetchPorts(deviceId) {
    if (portsCache.has(deviceId)) return portsCache.get(deviceId);

    const r = await fetch(`ajax/ajaxerror.php?act=ports_by_switch&deviceid=${deviceId}`);
    const j = await r.json();
    if (!j.ok) throw new Error("ports_by_switch failed");

    const ports = (j.ports || []).map((p) => ({
      ...p,
      llid: Number(p.llid),
      error_today: Number(p.error_today || 0),
      error_count: Number(p.error_count || 0),
    }));

    ports.sort((a, b) => b.error_today - a.error_today || a.nameport.localeCompare(b.nameport));
    portsCache.set(deviceId, ports);
    return ports;
  }

  async function fetchSeriesBySwitch(deviceId, range) {
    const key = `${deviceId}|${range}`;
    if (seriesCache.has(key)) return seriesCache.get(key);

    const r = await fetch(`ajax/ajaxerror.php?act=series_by_switch&deviceid=${deviceId}&range=${encodeURIComponent(range)}`);
    const j = await r.json();
    if (!j.ok) throw new Error("series_by_switch failed");

    const series = j.series || {};
    seriesCache.set(key, series);
    return series;
  }

  async function selectSwitch(sw) {
    state.deviceId = Number(sw.id);
    state.deviceName = sw.place || "";

    const switchTitle = $("#switchTitle");
    if (switchTitle) {
      switchTitle.textContent = `${state.deviceName} • ${t.chartsTitle || "Графіки помилок портів"}`;
    }

    renderSwitches($("#switchSearch")?.value || "");
    setHint(t.loadingPorts || "Завантаження графіків портів…", false);

    try {
      const [ports, series] = await Promise.all([fetchPorts(state.deviceId), fetchSeriesBySwitch(state.deviceId, state.range)]);
      state.ports = ports;
      state.series = series;
      renderPortCharts($("#portSearch")?.value || "");
    } catch (e) {
      console.error(e);
      destroyCharts();
      const grid = $("#portChartsGrid");
      if (grid) grid.innerHTML = "";
      setHint(t.loadFail || "Помилка завантаження", true);
    }
  }

  function createChart(canvas, points) {
    const inData = (points || []).map((p) => ({ x: p.ts, y: p.in_sum || 0 }));
    const outData = (points || []).map((p) => ({ x: p.ts, y: p.out_sum || 0 }));

    return new Chart(canvas.getContext("2d"), {
      type: "line",
      data: {
        datasets: [
          {
            label: "IN",
            data: inData,
            borderColor: "#2f80ed",
            borderWidth: 1.8,
            pointRadius: 0,
            tension: 0.25,
          },
          {
            label: "OUT",
            data: outData,
            borderColor: "#d14a28",
            borderWidth: 1.8,
            pointRadius: 0,
            tension: 0.25,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        parsing: false,
        animation: false,
        interaction: { mode: "nearest", intersect: false },
        plugins: { legend: { display: false } },
        scales: {
          x: {
            type: "time",
            time: { tooltipFormat: "dd.LL.yyyy HH:mm" },
            grid: { color: "rgba(0,0,0,0.05)" },
            ticks: { maxTicksLimit: 6 },
          },
          y: {
            beginAtZero: true,
            ticks: { precision: 0, maxTicksLimit: 5 },
            grid: { color: "rgba(0,0,0,0.05)" },
          },
        },
      },
    });
  }

  function renderPortCharts(filterText) {
    const grid = $("#portChartsGrid");
    if (!grid) return;

    destroyCharts();
    grid.innerHTML = "";

    if (!state.deviceId) {
      setHint(t.switchHint || "Оберіть комутатор зліва — графіки всіх портів завантажаться автоматично.", false);
      return;
    }

    if (!state.ports.length) {
      setHint(t.noPorts || "На цьому комутаторі немає портів у моніторингу.", false);
      return;
    }

    const q = (filterText || "").toLowerCase();
    const rows = state.ports.filter((p) => (`${p.nameport} ${p.descrport || ""}`).toLowerCase().includes(q));

    if (!rows.length) {
      setHint(t.noPorts || "На цьому комутаторі немає портів у моніторингу.", false);
      return;
    }

    hideHint();

    const frag = document.createDocumentFragment();
    rows.forEach((p) => {
      const llid = Number(p.llid);
      const points = state.series[String(llid)] || state.series[llid] || [];
      const hasData = Array.isArray(points) && points.length > 0;

      const card = document.createElement("div");
      card.className = "card porterror-card";

      const canvasId = `pe_chart_${state.deviceId}_${llid}`;
      card.innerHTML = `
        <div class="porterror-card-head">
          <div class="porterror-card-title-wrap">
            <div class="porterror-card-title">${esc(p.nameport)}</div>
            <div class="porterror-card-sub">${esc(p.descrport || "")}</div>
          </div>
          <div class="porterror-card-meta">
            <div>${t.status || "Статус"}: <b class="p-s-${esc(p.operstatus)}">${esc(p.operstatus)}</b></div>
            <div>${t.errorToday || "Сьогодні"}: <b class="porterror-val-red">+${nf(p.error_today)}</b></div>
            <div>${t.errorTotal || "Всього"}: <b>${nf(p.error_count)}</b></div>
          </div>
        </div>
        <div class="porterror-card-chart-wrap">
          <canvas id="${canvasId}" height="160"></canvas>
          ${hasData ? "" : `<div class="porterror-card-empty">${esc(t.emptySeries || "Немає даних за обраний період")}</div>`}
        </div>
        <div class="porterror-card-legend">${esc(t.legend || "IN errors • OUT errors")}</div>
      `;

      frag.appendChild(card);

      if (hasData) {
        queueMicrotask(() => {
          const cv = document.getElementById(canvasId);
          if (!cv) return;
          const ch = createChart(cv, points);
          chartMap.set(canvasId, ch);
        });
      }
    });

    grid.appendChild(frag);
  }

  async function reloadSeries() {
    if (!state.deviceId) return;
    setHint(t.loadingPorts || "Завантаження графіків портів…", false);

    try {
      state.series = await fetchSeriesBySwitch(state.deviceId, state.range);
      renderPortCharts($("#portSearch")?.value || "");
    } catch (e) {
      console.error(e);
      setHint(t.loadFail || "Помилка завантаження", true);
    }
  }

  $$("#switchSearch").forEach((el) => {
    el.addEventListener("input", (e) => renderSwitches(e.target.value));
  });

  $$("#portSearch").forEach((el) => {
    el.addEventListener("input", (e) => renderPortCharts(e.target.value));
  });

  $$(".btn-range").forEach((btn) => {
    btn.addEventListener("click", () => {
      $$(".btn-range").forEach((b) => b.classList.remove("btn-active"));
      btn.classList.add("btn-active");
      state.range = btn.dataset.range || "today";
      reloadSeries();
    });
  });

  loadSwitches();
})();
