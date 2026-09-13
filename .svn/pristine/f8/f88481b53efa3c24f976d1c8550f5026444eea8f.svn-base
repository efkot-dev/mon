function filterConsecutiveChanges(points) {
        const out = []; let prev = null;
        for (const p of points) {
          const v = +p.y;
          if (prev === null || v !== prev) {
            out.push({ date: new Date(p.x), value: v });
            prev = v;
          }
        }
        return out;
      }
      function filterUniqueValues(points) {
        const seen = new Set(); const out = [];
        for (const p of points) {
          const v = +p.y;
          if (!seen.has(v)) {
            out.push({ date: new Date(p.x), value: v });
            seen.add(v);
          }
        }
        return out;
      }
      function renderVoltage(mode = "consecutive") {
        const el = document.getElementById("d3-voltage");
        el.innerHTML = "";
        const rect = el.getBoundingClientRect();
        const margin = { top: 10, right: 10, bottom: 36, left: 40 };
        const w = Math.max(300, rect.width) - margin.left - margin.right;
        const h = Math.max(220, (rect.height || 360)) - margin.top - margin.bottom;
        const svg = d3.select(el).append("svg")
          .attr("width", w + margin.left + margin.right)
          .attr("height", h + margin.top + margin.bottom);
        const g = svg.append("g").attr("transform", `translate(${margin.left},${margin.top})`);
        const series = (voltageSeries || []).map((s, i) => {
          const pts = (s.data || []).slice().sort((a,b)=>new Date(a.x)-new Date(b.x));
          const filtered = (mode === "unique") ? filterUniqueValues(pts) : filterConsecutiveChanges(pts);
          return { label: s.label, color: d3.schemeTableau10[i % 10], data: filtered };
        }).filter(s => s.data.length > 0);
        const allDates = series.flatMap(s => s.data.map(d => d.date));
        const allVals  = series.flatMap(s => s.data.map(d => d.value));
        if (!allDates.length) {
          g.append("text").attr("x", w/2).attr("y", h/2).attr("text-anchor","middle")
           .style("fill","#888").text("Немає даних для відображення");
          return;
        }
        const x = d3.scaleUtc().domain(d3.extent(allDates)).range([0, w]);
        const y = d3.scaleLinear().domain(d3.extent(allVals)).nice().range([h, 0]);
        g.append("g").attr("transform", `translate(0,${h})`).call(d3.axisBottom(x).ticks(6));
        g.append("g").call(d3.axisLeft(y).ticks(6))
          .append("text").attr("x",-36).attr("y",-10).attr("fill","currentColor")
          .attr("text-anchor","start").style("font-size","12px").text("V");
        const line = d3.line().x(d=>x(d.date)).y(d=>y(d.value)).curve(d3.curveStepAfter);
        const tooltip = d3.select(el).append("div")
          .style("position","absolute").style("pointer-events","none")
          .style("padding","6px 8px").style("background","rgba(0,0,0,0.75)")
          .style("color","#fff").style("font-size","12px").style("border-radius","6px")
          .style("opacity",0);
        function showTip(html, x0, y0) {
          tooltip.html(html).style("left", (x0 + 12) + "px").style("top", (y0 - 10) + "px")
            .transition().duration(120).style("opacity",1);
        }
        function hideTip() { tooltip.transition().duration(120).style("opacity",0); }
        const seriesG = g.selectAll(".serie").data(series).enter().append("g").attr("class","serie");
        seriesG.append("path").attr("fill","none").attr("stroke", s=>s.color).attr("stroke-width",2)
               .attr("d", s=>line(s.data));
        seriesG.selectAll("circle")
          .data(s => s.data.map(d => ({...d, color:s.color, label:s.label})))
          .enter().append("circle").attr("r",3)
          .attr("cx", d=>x(d.date)).attr("cy", d=>y(d.value)).attr("fill", d=>d.color)
          .on("mouseenter", function (event, d) {
            const box = svg.node().getBoundingClientRect();
            const html = `<div><b>${d.label}</b></div>
                          <div>${d.date.toLocaleString()}</div>
                          <div><b>${d.value.toFixed(2)} V</b></div>`;
            showTip(html, event.clientX - box.left, event.clientY - box.top);
          })
          .on("mouseleave", hideTip);
        const legend = g.append("g").attr("transform", `translate(0, -8)`);
        const items = legend.selectAll(".l-item").data(series).enter().append("g")
          .attr("class","l-item").attr("transform", (d,i)=>`translate(${i*180},0)`);
        items.append("rect").attr("width",14).attr("height",14).attr("rx",3).attr("ry",3).attr("fill", d=>d.color);
        items.append("text").attr("x",20).attr("y",11).style("font-size","12px").text(d=>d.label);
      }