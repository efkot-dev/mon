function get_editor_pmon(id, type) {
    try {
        const url = root + 'ajax/editorpm.php?act=get&id=' + id + '&type=' + type;
        const response = $.ajax({url: url,type:'POST',async: false,dataType:'json'});
        return response.responseJSON;
    } catch (error) {
        console.error('Error fetching switch data:', error);
        return null;
    }
}
function generatecross1(elementid,act) {
	$.post(root+"ajax/editor.php",{act:act,id:elementid}, function(response){ 
	$(".wrap").html(response);
	}, "html");	
}
function generateOnus(data){
	const svg  = d3.select("#connection-svg");
	const arr  = Array.isArray(data) ? data : [data];
	const W = 80, H = 20, X = -5, Y = 20;
	const PANEL_H = 17, PANEL_OFFSET = 5;
	const onus = svg.selectAll(".onu").data(arr, d => d.id).enter().append("g").attr("class","onu").attr("id", d => d.id).attr("transform", d => `translate(${d.left},${d.top})`).on("click", function(event, d){
        const element = d3.select(this);
        if (!element.classed("active")) {
			element.classed("active", true);
        }
		element.call(d3.drag().on("start", dragstarted).on("drag", dragged).on("end", dragended));
	});
	onus.append("rect").attr("class","onu-body").attr("width", W).attr("height", H).attr("x", X).attr("y", Y).style("fill", "#eceff1").style("stroke", "#1f81c3").style("stroke-width", "1px");
	const connector = onus.append("g").attr("class","connector");
	connector.append("rect").attr("class","conn").attr("id", d => d.input).attr("width", 10).attr("height", 10).attr("x", (W/2) - 5 + X).attr("y", Y - 10).style("fill", "#1f81c3");
	onus.append("text").text(d => d.name).attr("text-anchor","middle").attr("x",(W/2) + X).attr("y",Y + H/2 + 4).style("font-size","10px").style("fill","#222");
	const panelY = Y + H + PANEL_OFFSET;
	const panel = onus.append("g").attr("class","onu-panel").style("cursor","pointer").on("click", function(event, d){
		event.stopPropagation();
		const cleanId = d.idonu.replace(/\D/g, '');
		window.location = `/?do=onu&id=${cleanId}`;
    });
	panel.append("rect").attr("x",X).attr("y",panelY).attr("width",W).attr("height",PANEL_H).attr("rx",3).attr("ry",3).style("fill","#f5f7f8").style("stroke","#c9d3d8");
	panel.append("rect").attr("class","onu-status").attr("x",X+4).attr("y",panelY+3).attr("width",10).attr("height",11).style("fill", d => onuStatusColor(d.status));
	panel.append("text").attr("class","onu-rx").attr("x",X+20+30).attr("y",panelY+PANEL_H/2+3).attr("text-anchor","middle").style("font-size","9px").style("fill","#22343b").text(d => formatRx(d.rx));
	onus.each(function(d){
    const g = d3.select(this);
    const statusRect = g.select(".onu-status");
    const rxText     = g.select(".onu-rx");
    function updateOnce(){
		$.getJSON(`/?do=fiber&act=getstatus`,{idonu:d.idonu},function(resp){
			if (!resp) return;
			if (resp.status !== undefined) statusRect.style("fill", onuStatusColor(resp.status));
			if (resp.rx !== undefined) rxText.text(formatRx(resp.rx));
		});
    }
    updateOnce();
    setInterval(updateOnce, 30000);
	});
	function onuStatusColor(st){
		if (+st === 1) return "#2ecc71";
		if (+st === 2) return "#e74c3c";
    return "#bfc8cc";
	}
	function formatRx(rx){
    if (rx === null || rx === undefined || rx === '') return "RX: —";
    const n = Number(rx);
    return isFinite(n) ? `${n.toFixed(2)}` : `${rx}`;
	}
}
function fitSvgText(textSelection, fullText, maxWidth) {
	const safeText = (fullText === null || fullText === undefined) ? "" : String(fullText);
	textSelection.text(safeText);
	textSelection.selectAll("title").remove();
	if (!safeText) return;
	if (!Number.isFinite(maxWidth) || maxWidth <= 0) {
		textSelection.append("title").text(safeText);
		return;
	}
	const node = textSelection.node();
	if (!node || typeof node.getComputedTextLength !== "function") {
		textSelection.append("title").text(safeText);
		return;
	}
	if (node.getComputedTextLength() <= maxWidth) {
		textSelection.append("title").text(safeText);
		return;
	}
	const ellipsis = "…";
	let low = 0;
	let high = safeText.length;
	let best = ellipsis;
	while (low <= high) {
		const mid = Math.floor((low + high) / 2);
		const candidate = safeText.slice(0, mid).trimEnd() + ellipsis;
		textSelection.text(candidate);
		if (node.getComputedTextLength() <= maxWidth) {
			best = candidate;
			low = mid + 1;
		} else {
			high = mid - 1;
		}
	}
	textSelection.text(best);
	textSelection.append("title").text(safeText);
}
function generatecross(data) {
	if (!Array.isArray(data) || data.length === 0) {
		return;
	}
    const svg = d3.select("#connection-svg");
    data.forEach((d, index) => {
		const crossDatum = {
			...d,
			x: Number.isFinite(+d.left) ? +d.left : 0,
			y: Number.isFinite(+d.top) ? +d.top : 0
		};
        const cross = svg.append("g")
			.datum(crossDatum)
            .attr("class", "cross")
            .attr("id", d.id)
			.attr("data-type", "cross")
			.attr("data-elementid", d.elementid ?? "")
            .attr("transform", `translate(${d.left},${d.top})`)
			.on("click", function(event) {
				const element = d3.select(this);
				if (!element.classed("active")) {
					element.classed("active", true);
				}
				element.call(d3.drag().on("start", dragstarted).on("drag", dragged).on("end", dragended));
			});
        const rectWidth = d.count * 20;
		const crossTitle = [d.name, d.description].filter(v => v && String(v).trim() !== "").join(" | ");
		if (crossTitle) {
			cross.append("title").text(crossTitle);
		}
        cross.append("rect")
            .attr("class", "cross-body")
            .attr("width", rectWidth)
            .attr("height", 45)
            .attr("x", -5)
            .attr("y", 20)
            .style("fill", "#ccc")
            .style("stroke-width", "1px")
            .style("stroke", "#222");
        const crossNameText = cross.append("text")
            .style("font-size", "12px")
            .attr("text-anchor", "middle")
            .attr("x", (rectWidth / 2) - 5)
            .attr("y", 48)
            .style("fill", "#222");
		fitSvgText(crossNameText, d.name || "", Math.max(40, rectWidth - 10));
		if (d.description) {
			const crossDescText = cross.append("text")
				.style("font-size", "10px")
				.attr("text-anchor", "middle")
				.attr("x", (rectWidth / 2) - 5)
				.attr("y", 84)
				.style("fill", "#455a64");
			fitSvgText(crossDescText, d.description, Math.max(40, rectWidth - 10));
		}
        let rectWidthRozetki1 = 0;
        let rectWidthRozetki2 = 0;
        d.rozetki.forEach(rozetka => {
            if (rozetka.input) {
                rozetka.input.forEach(input => {
                    cross.append("text")
                        .attr("x", rectWidthRozetki1 + 5).attr("y", 31)
                        .text(p => input.name).style("font-size", "11px").style("fill", "#407488").attr("text-anchor", "middle");
                    const inputRect = cross.append("rect")
                        .attr("class", "conn")
                        .attr("x", rectWidthRozetki1)
                        .attr("y", 10)
                        .attr("width", 10)
                        .attr("height", 10)
                        .attr("id", input.id)
                        .attr("data-note", input.note || "")
                        .style("fill", "#2a94e7");
					if (input.note) {
						inputRect.classed("has-port-note", true).append("title").text(input.note);
						cross.append("circle")
							.attr("class", "port-note-dot")
							.attr("cx", rectWidthRozetki1 + 9)
							.attr("cy", 9)
							.attr("r", 2.2);
					}
                    rectWidthRozetki1 += 20;
                });
            }
            if (rozetka.output) {
                rozetka.output.forEach(output => {
                    cross.append("text")
                        .attr("x", rectWidthRozetki2 + 5).attr("y", 61)
                        .text(p => output.name).style("font-size", "11px").style("fill", "#407488").attr("text-anchor", "middle");
                    const outputRect = cross.append("rect")
                        .attr("class", "conn")
                        .attr("x", rectWidthRozetki2)
                        .attr("y", 65)
                        .attr("width", 10)
                        .attr("height", 10)
                        .attr("id", output.id)
                        .attr("data-note", output.note || "")
                        .style("fill", "#2a94e7");
					if (output.note) {
						outputRect.classed("has-port-note", true).append("title").text(output.note);
						cross.append("circle")
							.attr("class", "port-note-dot")
							.attr("cx", rectWidthRozetki2 + 9)
							.attr("cy", 64)
							.attr("r", 2.2);
					}
                    rectWidthRozetki2 += 20;
                });
            }
        });
    });
}
function generatePlanars(data) {
    const svg = d3.select("#connection-svg");
    const planars = svg.selectAll(".planars")
        .data(data)
        .enter()
        .append("g").attr("class", "planars").attr("position","top").attr("id", d => d.id).attr("transform", d => `translate(${d.left},${d.top})`)
        .on("click", function(event, d) {
            const element = d3.select(this);
            element.classed("active", !element.classed("active"));
            element.call(d3.drag().on("start", dragstarted).on("drag", dragged).on("end", dragended));
        });
    planars.append("rect").attr("width", d => d.out * 15).attr("height", 30).attr("x", -2).attr("y", 0).style("fill", "#ececec").style("stroke-width", "1px").style("stroke", "#4a4a4aa3");
    planars.each(function(d) {
        const group = d3.select(this);
        const numConnectors = d.out;
        for (let i = 0; i < numConnectors; i++) {
            const connectorData = d.c[i + 1];
            const label = connectorData.n || `Connector ${i + 1}`;
            const name_conn = `${connectorData[`output${i + 1}`]}`;
            const connectorGroup = group.append("g").attr("class", "connector");
            connectorGroup.append("rect").attr("x", i * 15).attr("y", 30).attr("class", "conn").attr("width", 10).attr("height", 10).attr("id", name_conn).style("fill", "#1498fa");
            connectorGroup.append("text").attr("x", i * 15 + 5).attr("y", 25).attr("class", "connector-label").attr("text-anchor", "middle").text(i + 1).style("font-size", "9px").style("fill", "#000");
        }
        const centerX = (numConnectors * 15) / 2 - 5;
        const centerConnectorGroup = group.append("g").attr("class", "connector");
		const conn_planer = d.c[0][`input`];		
        centerConnectorGroup.append("rect").attr("x", centerX).attr("y", -10).attr("id", conn_planer).attr("class", "conn").attr("width", 10).attr("height", 10).style("fill", "#1498fa");
        centerConnectorGroup.append("text").attr("x", centerX + 5).attr("y", 13).attr("text-anchor", "middle").text(d.name).style("font-size", "12px").style("fill", "#000");
    });
}
function generateOptica(data) {
	if (!data || (Array.isArray(data) && data.length === 0) || (typeof data === "object" && !Array.isArray(data) && Object.keys(data).length === 0)) {
		return;
	}
	let elementFiberCounts = {};
    const dataArray = Array.isArray(data) ? data : Object.values(data);
    const svg = d3.select("#connection-svg");
	const fibers = svg.selectAll(".fibers").data(dataArray).enter().append("g").attr("class", "fibers").attr("position", d => d.position).attr("id", d => d.id).attr("data-fiber-node", d => String(d.id)).attr("transform", d => `translate(${d.left},${d.top})`)
	.on("click", function(event, d) {const element = d3.select(this);if (!element.classed("active")) {element.classed("active", true);} element.call(d3.drag().on("start", dragstarted).on("drag", dragged).on("end", dragended));});
	let lockedFiberNode = null;
	function applyFiberFocus(nodeId) {
		const hasFocus = nodeId !== null && nodeId !== undefined && nodeId !== "";
		const key = hasFocus ? String(nodeId) : "";
		fibers
			.classed("fiber-node-dim", function(fd) {
				return hasFocus && String(fd.id) !== key;
			})
			.classed("fiber-node-active", function(fd) {
				return hasFocus && String(fd.id) === key;
			});
		svg.selectAll(".fiber-connector")
			.classed("fiber-conn-dim", function() {
				return hasFocus && this.getAttribute("data-fiber-node") !== key;
			})
			.classed("fiber-conn-active", function() {
				return hasFocus && this.getAttribute("data-fiber-node") === key;
			});
	}
	let count_vols = 0;
    let global_vols = 0;
    fibers.each(function(d) {
		const nodeId = String(d.id);
		const fiberGroup = d3.select(this);
		elementFiberCounts[d.id] = 0;
        let masic_my_dog = 0;
        d.colba.forEach(colba => {
            masic_my_dog += colba.vok.length;
        });
		const fibersTotal = masic_my_dog;
		let r_w = 0;
		let r_x = 0;
		let r_y = 0;
		let blockRect = null;
		if (d.position === 'left') {
			r_w = 40;
			r_x = -5;
			r_y = 20;
			masic_my_dog *= 15;
			blockRect = fiberGroup.append("rect").attr("width",r_w).attr("id", d => d.id).attr("height",masic_my_dog).attr("x",r_x).attr("y",r_y).attr("class","block-optik fiber-block");
		}else if(d.position === 'top'){
			r_w = 40;
			r_x = 20;
			r_y = 20;
			masic_my_dog *= 15;	
			blockRect = fiberGroup.append("rect").attr("width",masic_my_dog).attr("id", d => d.id).attr("height",r_w).attr("x",r_x).attr("y",r_y).attr("class","block-optik fiber-block");
		}else if(d.position === 'right'){
			r_w = 40;
			r_x = 39;
			r_y = 20;
			masic_my_dog *= 15;
			blockRect = fiberGroup.append("rect").attr("width",r_w).attr("id", d => d.id).attr("height",masic_my_dog).attr("x",r_x).attr("y",r_y).attr("class","block-optik fiber-block");
		}
		if (blockRect) {
			const blockTitle = `${d.name || "Волокно"} | Волокон: ${fibersTotal} | Подвійний клік: закріпити фокус`;
			blockRect
				.attr("title", blockTitle)
				.style("cursor", "pointer")
				.on("mouseenter", function() {
					if (!lockedFiberNode) {
						applyFiberFocus(nodeId);
					}
				})
				.on("mouseleave", function() {
					if (!lockedFiberNode) {
						applyFiberFocus(null);
					}
				})
				.on("dblclick", function(event) {
					event.preventDefault();
					event.stopPropagation();
					lockedFiberNode = (lockedFiberNode === nodeId) ? null : nodeId;
					applyFiberFocus(lockedFiberNode);
				});
		}	
		let switchY = 20;
		d.colba.forEach(colba => {
		if (d.position == 'left') {
			colb_w = 20;
			colb_h = colba.vok.length * 15;
			colb_x = 35;
			colb_y = switchY;
			d3.select(this).append("rect").attr("x",colb_x).attr("y",colb_y).attr("width",colb_w).attr("height",colb_h).attr("id",colba.id).style("fill",colba.color);
		}else if(d.position == 'top'){
			colb_w = 20;
			colb_h = colba.vok.length * 15;
			colb_x = switchY;
			colb_y = 60;
			d3.select(this).append("rect").attr("x",colb_x).attr("y",colb_y).attr("width",colb_h).attr("height",colb_w).attr("id",colba.id).style("fill",colba.color);			
		}else if(d.position == 'right'){
			colb_w = 20;
			colb_h = colba.vok.length * 15;
			colb_x = 19;
			colb_y = switchY;
			d3.select(this).append("rect").attr("x",colb_x).attr("y",colb_y).attr("width",colb_w).attr("height",colb_h).attr("id",colba.id).style("fill",colba.color);			
		}        
		// VOLOKNO
        let fiberY = switchY;
        colba.vok.forEach(vok => {
			const connGroup = d3.select(this)
				.append("g")
				.attr("class","connector fiber-connector")
				.attr("data-fiber-node", nodeId)
				.attr("data-fiber-id", String(vok.id))
				.on("mouseenter", function() {
					d3.select(this).classed("fiber-port-hot", true);
					if (!lockedFiberNode) {
						applyFiberFocus(nodeId);
					}
				})
				.on("mouseleave", function() {
					d3.select(this).classed("fiber-port-hot", false);
					if (!lockedFiberNode) {
						applyFiberFocus(null);
					}
				});
			const dirText = (vok.line === 'in') ? 'вхід' : ((vok.line === 'out') ? 'вихід' : 'лінія');
			const connTitle = `Волокно: ${vok.id} | Напрям: ${dirText}` + (vok.note ? ` | Примітка: ${vok.note}` : '');
			connGroup.attr("title", connTitle);
			if (d.position === 'left') {
				vok_x = 55;
				vok_w = 24;
				vok_h = 8;
				vok_y = fiberY + 6;
				connGroup.append("rect")
					.attr("x",vok_x).attr("y",vok_y).attr("class","conn")
					.attr("width",vok_w).attr("height",vok_h).attr("id",vok.id).style("fill",vok.color);
			}else if(d.position === 'top'){
				vok_x = 80;
				vok_w = 24;
				vok_h = 8;
				vok_y = fiberY + 6;
				connGroup.append("rect")
					.attr("x",vok_y).attr("y",vok_x).attr("class","conn")
					.attr("width",vok_h).attr("height",vok_w).attr("id",vok.id).style("fill",vok.color);				
			}else if(d.position === 'right'){
				vok_x = -5;
				vok_w = 24;
				vok_h = 8;
				vok_y = fiberY + 6;	
				connGroup.append("rect")
					.attr("x",vok_x).attr("y",vok_y).attr("class","conn")
					.attr("width",vok_w).attr("height",vok_h).attr("id",vok.id).style("fill",vok.color);				
			}
			elementFiberCounts[d.id] += 1;
			if (vok.line === 'in') {
				if(d.position === 'top'){
					imageUrl = '../style/img/vok-top-in.png';
				}else{
					imageUrl = '../style/img/vok-in.png';
				}
			} else if (vok.line === 'out') {
				if(d.position === 'top'){
					imageUrl = '../style/img/vok-top-out.png';
				}else{
					imageUrl = '../style/img/vok-out.png';
				}
			} else {
				imageUrl = null; 
			}
			if (imageUrl) {
				if (d.position === 'left') {
					vok_x_img = vok_x - 17;
					vok_y_img = vok_y - 3;
				}else if(d.position === 'top'){
					vok_x_img = vok_y-3;
					vok_y_img = vok_x-15;	
					}else if(d.position === 'right'){
					vok_x_img = vok_x + 27;
					vok_y_img = vok_y - 3;					
				}
				connGroup.append("image").attr("xlink:href",imageUrl).attr("x", vok_x_img).attr("y", vok_y_img).attr("width",13).attr("height",13).attr("class","image-block");
			}
			if (d.position === 'left') {
				fiberY += 14;
				count_vols += 1;
			}else if(d.position === 'top'){
				fiberY += 14;
				count_vols += 1;		
			}else if(d.position === 'right'){
				fiberY += 14;
				count_vols += 1;				
			}			
        });
			if (d.position === 'left') {
				switchY += colba.vok.length * 15;
			}else if(d.position === 'top'){
				switchY += colba.vok.length * 15;				
			}else if(d.position === 'right'){
				switchY += colba.vok.length * 15;				
			}        
        });		
    });
	fibers.each(function(d) {
		var wi = (elementFiberCounts[d.id]*15);
        let href_x, href_y, rotate_r;
        if (d.position === 'left') {
            href_x = -23;
            href_y = (wi/2)+10;
			rotate_r = (p, i) => `rotate(0)`;
			img_url = "../style/editorpmon/left.png";
        } else if(d.position === 'top') {
            href_x = 3;
            href_y = -(wi/2)-25;
			rotate_r = (p, i) => `rotate(90)`;
			img_url = "../style/editorpmon/left.png";
        } else if(d.position === 'right') {
            img_url = "../style/editorpmon/right.png";
            href_x = +80;
            href_y = (wi/2)+10;
			rotate_r = (p, i) => `rotate(0)`;
        }
        d3.select(this).append("a").attr("xlink:href", d.href).attr("class", "posilania").append("image")
		.attr("transform", rotate_r)
		.attr("xlink:href",img_url).attr("x", href_x).attr("y", href_y).attr("width", 16).attr("height", 16);
    });	
	fibers.each(function(d) {
		var he = (elementFiberCounts[d.id]*15);
		let maxLabelWidth = 120;
		if (d.position === 'left') {
            text_x = -(he/2)-20;
            text_y = -30;
			rot_text = (p, i) => `rotate(270)`;
			maxLabelWidth = Math.max(70, Math.min(150, he - 10));
        } else if(d.position === 'top') {
            text_x = (he/2)+20;
            text_y = -5;
			rot_text = (p, i) => `rotate(0)`;
			maxLabelWidth = Math.max(70, Math.min(180, he - 10));
        } else if(d.position === 'right') {
            text_x = (he/2)+20;
            text_y = -105;
			rot_text = (p, i) => `rotate(90)`;
			maxLabelWidth = Math.max(70, Math.min(150, he - 10));
        }
		const nameText = (d && d.name) ? d.name : "";
		const label = d3.select(this).append("text")
		.attr("class", "fiber-trace-label")
		.attr("dominant-baseline", "middle")
		.style("font-size","11px")
		.attr("transform", rot_text)
		.attr("x",text_x).attr("y",text_y)
		.style("fill", "#222")
		.style("font-size","11px")
		.attr("text-anchor", "middle");
		fitSvgText(label, nameText, maxLabelWidth);
	});
	applyFiberFocus(null);
	svg.on("click.fiber-focus-clear", function(event) {
		const t = event && event.target ? event.target : null;
		if (!t || typeof t.closest !== "function") {
			return;
		}
		if (!t.closest(".fibers") && !t.closest(".fiber-connector")) {
			lockedFiberNode = null;
			applyFiberFocus(null);
		}
	});
}
function handleTooltip(selector) {
        $(selector).hover(function() {
            var title = $(this).attr('title');
            if (title) {
                $(this).data('tipText', title).removeAttr('title');
                $('<p class="tooltip"></p>').text(title).appendTo('body').fadeIn('slow');
            }
        }, function() {
            var tipText = $(this).data('tipText');
            if (tipText) {
                $(this).attr('title', tipText);
            }
            $('.tooltip').remove();
        }).mousemove(function(e) {
            var mousex = e.pageX + 20;
            var mousey = e.pageY - 30;
            $('.tooltip').css({ top: mousey, left: mousex });
        });
    }
function generateSplitters(data) {
    if (typeof data === 'undefined') {
        console.log("nice_not_splitters");
        return;
    }
    const svg = d3.select("#connection-svg");
    const splitters = svg.selectAll(".splitters").data(data).enter().append("g").attr("class", "splitters").attr("position", d => d.position).attr("id", d => d.id).attr("transform", d => `translate(${d.left},${d.top})`)
        .on("click", function(event, d) {
            const element = d3.select(this);
            if (!element.classed("active")) {
                element.classed("active", true);
            }
            element.call(d3.drag().on("start", dragstarted).on("drag", dragged).on("end", dragended));
        });
		splitters.each(function(d) {
			let data_position;
			let x_name;
			let y_name;
			if (d.position === 'down') {
				data_position = "0,0 40,0 30,60 10,60";
				x_name = 30;
				y_name = 75;
			} else if (d.position === 'left') {
				data_position = "0,10 0,30 60,40 60,0";
				x_name = 15;
				y_name = -5;
			} else if (d.position === 'top') {
				data_position = "10,0 30,0 40,60 0,60";
				x_name = 30;
				y_name = -5;
			} else if (d.position === 'right') {
				data_position = "60,10 60,30 0,40 0,0";
				x_name = 15;
				y_name = -5;
			}
			d3.select(this).append("polygon")
				.attr("points", data_position).style("fill", "#eee").style("stroke-width", "1px").attr("class", "body_splitter");
			d3.select(this).append("text")
				.text(d.name).style("font-size", "12px").attr("x", x_name).attr("y", y_name).style("fill", "#222");
		});
    const connectors = splitters.selectAll(".connector").data(d => d.c).enter().append("g").attr("class", "connector");

    connectors.each(function(connector) {
        const type = Object.keys(connector)[0];
        const id = Object.values(connector)[0];
        const name = Object.values(connector)[1];
		const position =  connector['p'];
		if (position == 'left') {
			// LEFT
			let xn = 3;
			let yn = 23;
			let x = -10;
			let y = 14;
			let fill = "green";
			if (type === "output1") {
				x = 60;
				y = 5;
				xn = 45;
				yn = 15;
				fill = "red";
			} else if (type === "output2") {
				x = 60;
				y = 25;
				xn = 45;
				yn = 32;
				fill = "#2a94e7";
			}
			d3.select(this).append("text").text(name).style("font-size", "10px").attr("x", xn).attr("y", yn).style("fill", fill);
			d3.select(this).append("rect").attr("x", x).attr("y", y).attr("class", "conn").attr("width", 10).attr("height", 10).attr("id", id).style("fill", fill);
		} else if (position == 'right') {
			// RIGHT
			let xn = 47;
			let yn = 23;
			let x = 60;
			let y = 14;
			let fill = "green";
			if (type === "output1") {
				x = -10;
				y = 5;
				xn = 5;
				yn = 15;
				fill = "red";
			} else if (type === "output2") {
				x = -10;
				y = 25;
				xn = 5;
				yn = 32;
				fill = "#2a94e7";
			}			
			d3.select(this).append("text").text(name).style("font-size", "10px").attr("x", xn).attr("y", yn).style("fill", fill);
			d3.select(this).append("rect").attr("x", x).attr("y", y).attr("class", "conn").attr("width", 10).attr("height", 10).attr("id", id).style("fill", fill);
		} else if (position == 'top') {
			// TOP
			let xn = 15;
			let yn = 13;
			let x = 15;
			let y = -11;
			let fill = "green";
			if (type === "output1") {
				x = 5;
				y = 60;
				xn = 5;
				yn = 55;
				fill = "red";
			} else if (type === "output2") {
				x = 25;
				y = 60;
				xn = 23;
				yn = 55;
				fill = "#2a94e7";
			}			
			d3.select(this).append("text").text(name).style("font-size", "10px").attr("x", xn).attr("y", yn).style("fill", fill);
			d3.select(this).append("rect").attr("x", x).attr("y", y).attr("class", "conn").attr("width", 10).attr("height", 10).attr("id", id).style("fill", fill);
		} else if (position == 'down') {
			let xn = 15;
			let yn = 55;
			let x = 15;
			let y = 60;
			let fill = "green";
			if (type === "output1") {
				x = 5;
				y = -10;
				xn = 5;
				yn = 13;
				fill = "red";
			} else if (type === "output2") {
				x = 25;
				y = -10;
				xn = 23;
				yn = 13;
				fill = "#2a94e7";
			}
			d3.select(this).append("text").text(name).style("font-size", "10px").attr("x", xn).attr("y", yn).style("fill", fill);
			d3.select(this).append("rect").attr("x", x).attr("y", y).attr("class", "conn").attr("width", 10).attr("height", 10).attr("id", id).style("fill", fill);
		}

    });
}
function delvols(id,el) {
	$(".popupPonMenu").hide();
	$.post(root + "?do=fiber&act=deleteconnect",{id:id,el:el}, function(response) {
		$("#btn-connect-fibermap").html(response);
		
		location.reload();
	}, "html");
}
function deletelement(id,el) {
	$(".popupPonMenu").hide();
	$.post(root + "?do=fiber&act=deletelement",{id:id,el:el}, function(response) {
		$("#btn-connect-fibermap").html(response);
	location.reload();
	}, "html");
}
function generateSwitches(data) {
	if (typeof data === 'undefined') {
		console.log("nice_not_device");
		return;
	}
	if (!Array.isArray(data) || data.length === 0) {
		return;
	}
    const svg = d3.select("#connection-svg");
    const switches = svg.selectAll(".switch")
        .data(data)
        .enter()
        .append("g")
        .attr("class", "switch")
        .attr("id", d => d.id)
        .attr("transform", d => `translate(${d.left},${d.top})`)
		.on("click", function(event) {
			const element = d3.select(this);
			if (!element.classed("active")) {
				element.classed("active", true);
			}
			element.call(d3.drag().on("start", dragstarted).on("drag", dragged).on("end", dragended));
		});

    switches.each(function (d) {
        let sw_width = 0;
        let sw_height = 0;
        let name_rotar = "rotate(0deg)";
        let model_rotar = "rotate(0deg)";
        let sw_x = 0;
        let sw_y = 0;
        let model_x = 0;
        let model_y = 0;
        let name_x = 0;
        let name_y = 0;

        if (d.position === 'down') {
            sw_width = d.ports.length * 20;
            sw_height = 35;
            sw_x = -5;
            sw_y = 20;
            model_x = 0;
            model_y = 35;
            name_x = 0;
            name_y = 15;
        } else if (d.position === 'left') {			
            sw_width = 35;
            sw_height = d.ports.length * 20;
			name_rotar = "rotate(270deg)";
			model_rotar = "rotate(270deg)";
            sw_x = 0;
            sw_y = -5;
            model_x = - (sw_height-10);
            model_y = 28;
            name_x = - (sw_height-10);
            name_y = 48;			
        } else if (d.position === 'top') {
            sw_width = d.ports.length * 20;
            sw_height = 35;
            sw_x = -5;
            sw_y = 20;
            model_x = 0;
            model_y = 47;
            name_x = 0;
            name_y = 68;
        }

        const switchGroup = d3.select(this);
		const switchLabelMaxWidth = (d.position === 'left' || d.position === 'right')
			? Math.max(40, sw_height - 10)
			: Math.max(40, sw_width - 10);
		const switchTitle = [d.name, d.description].filter(v => v && String(v).trim() !== "").join(" | ");
		if (switchTitle) {
			switchGroup.append("title").text(switchTitle);
		}

        switchGroup.append("rect")
            .attr("class", "switch-body")
            .attr("width", sw_width)
            .attr("height", sw_height)
            .attr("x", sw_x)
            .attr("y", sw_y)
            .style("fill", "#30505f")
            .style("stroke-width", "0px")
            .style("stroke", "#222");

        const switchNameText = switchGroup.append("text")
            .style("font-size", "12px")
            .attr("x", name_x)
            .attr("y", name_y)
			.style("transform", name_rotar)
            .style("fill", "#222");
		fitSvgText(switchNameText, d.name || "", switchLabelMaxWidth);

		const switchDescription = (d.description !== undefined && d.description !== null && String(d.description).trim() !== '')
			? String(d.description)
			: ((d.models !== undefined && d.models !== null) ? String(d.models) : '');
		if (switchDescription.trim() !== '') {
			const switchDescText = switchGroup.append("text")
				.style("font-size", "11px")
				.style("transform", model_rotar)
				.attr("x", model_x)
				.attr("y", model_y)
				.style("fill", "#c4d9e0");
			fitSvgText(switchDescText, switchDescription, switchLabelMaxWidth);
		}

		if (d.position === 'down' || d.position === 'top') {
			var var_translate = (p, i) => `translate(${i * 20}, 50)`;
		}else{
			var var_translate = (p, i) => `translate(${-10}, ${i * 20})`;
		}

		const switchPorts = switchGroup.selectAll(".port")
			.data(d.ports)
			.enter()
			.append("g")
			.attr("class", "port")
			.attr("transform", var_translate);

        switchPorts.each(function(p) {
            let textX = 3;
            let textY = 0;
            let rectX = 0;
            let rectY = 5;

            switch (d.position) {
                case 'top':
                    textY = -17;
                    rectY = -40;
                    break;
                case 'left':
                    textY = 8;
					textX = 20;
					rectY = 0;
					rectX = 0;
                    break;
                case 'right':
                    textY = -7;
                    break;
                default:
                    textY = 0;
            }

            const portGroup = d3.select(this);
			const noteText = (p && p.note) ? String(p.note) : "";

            portGroup.append("text")
                .attr("x", textX)
                .attr("y", textY)
                .text(p => p.name)
                .style("font-size", "11px")
                .style("fill", "#fff")
                .attr("text-anchor", "middle");
            const portRect = portGroup.append("rect")
                .attr("x", rectX)
                .attr("y", rectY)
                .attr("width", 10)
                .attr("height", 10)
                .attr("id", p => p.id)
                .attr("class", "conn")
                .attr("data-note", noteText)
                .style("fill", "#2a94e7");
			if (noteText) {
				portRect.classed("has-port-note", true).append("title").text(noteText);
				portGroup.append("circle")
					.attr("class", "port-note-dot")
					.attr("cx", rectX + 9)
					.attr("cy", rectY - 1)
					.attr("r", 2.2);
			}
        });
    });
}

function generateSwitches2(data) {
	let sw_width = 0;
    let sw_height = 0;
    let sw_x = 0;
	let sw_y = 0;
	let model_x = 0;
	let model_y = 0;
	let name_x = 0;
	let name_y = 0;
    const svg = d3.select("#connection-svg");
    const switches = svg.selectAll(".switch")
        .data(data)
        .enter()
        .append("g")
        .attr("class", "switch")
		.attr("id", d => d.id)
        .attr("transform", d => `translate(${d.left},${d.top})`)
        //.call(d3.drag().on("start", dragstarted).on("drag", dragged).on("end", dragended))
		; 
	if(data[0].position=='down'){
		sw_width = data[0].ports.length * 20;
		sw_height = 35;
		sw_x = -5;
		sw_y = 20;
		model_x = 0;
		model_y = 35;
		name_x = 0;
		name_y = 15;
	}else if(data[0].position=='right'){
		
	}else if(data[0].position=='left'){
		
	}else if(data[0].position=='top'){
		sw_width = data[0].ports.length * 20;
		sw_height = 35;
		sw_x = -5;
		sw_y = 20;
		model_x = 0;
		model_y = 47;
		name_x = 0;
		name_y = 68;
	}
	
	switches.append("rect")
		.attr("width", d => sw_width).attr("height", sw_height).attr("x",sw_x).attr("y",sw_y)
		.style("fill", "#30505f").style("stroke-width", "0px").style("stroke", "#222");
	switches.append("text").attr("x",name_x).attr("y", name_y)
		.style("fill", "#222").text(d => d.name).style("font-size", "12px");
	switches.append("text").attr("x", model_x).attr("y",model_y)
		.text(d => d.models).style("font-size", "11px").style("fill", "#c4d9e0");
	
    switches.each(function (d) {
        const switchPorts = d3.select(this).selectAll(".port").data(d.ports).enter().append("g").attr("class", "port")
			.attr("transform", (p, i) => `translate(${i * 20}, 50)`);    
		switch (d.position) {
			case 'top':
				switchPorts.append("text")
					.attr("x", 3).attr("y", -17)
					.text(p => p.name).style("font-size", "11px").style("fill", "#fff").attr("text-anchor", "middle");
				switchPorts.append("rect")
					.attr("x",0).attr("y",-40).attr("width",10).attr("height",10)
					.attr("class", "conn").attr("id", p => p.id).style("fill","#2a94e7");
			break;
			case 'left':
				switchPorts.append("text").text(p => p.name).style("font-size", "11px").style("fill", "#fff").attr("text-anchor", "middle")
					.attr("x", 3).attr("y", -7);
				switchPorts.append("rect").attr("class", "conn")
					.attr("x",0).attr("y",0).attr("width",10).attr("height",10)
					.attr("id", p => p.id).style("fill","#2a94e7");
			break;
			case 'right':
				switchPorts.append("text").text(p => p.name).style("font-size", "11px").style("fill", "#fff").attr("text-anchor", "middle")
					.attr("x", 3).attr("y", -7);
				switchPorts.append("rect").attr("class", "conn")
					.attr("x",0).attr("y",0).attr("width",10)
					.attr("height",10).attr("id", p => p.id).style("fill","#2a94e7");
			break;
			default:
				switchPorts.append("text").text(p => p.name).style("font-size", "11px").style("fill", "#fff").attr("text-anchor", "middle")
					.attr("x", 3).attr("y", 0);
				switchPorts.append("rect").attr("class", "conn")
					.attr("x",0).attr("y",5).attr("width",10).attr("height",10)
					.attr("id", p => p.id).style("fill","#2a94e7");
			}		  
	});
}

function searchElement(array_list) {
	if (!Array.isArray(array_list) || array_list.length === 0) {
		return;
	}
	const svg = d3.select("#connection-svg");
	const pairIdx = new Map();
	const STEP = 1;
	function nextDeltaForPair(a, b, base) {
    const key = [String(a), String(b)].sort().join("||");
    const i = pairIdx.get(key) || 0;
    pairIdx.set(key, i + 1);
    const zig = (i % 2 === 0 ? +1 : -1) * Math.ceil((i + 1) / 2);
    return base + zig * STEP;
	}
	array_list.forEach(connection => {
    const volid = connection.id;
    const note = connection.note;
    const connect1 = connection.conn1;
    const connect2 = connection.conn2;
    const connect = connection.connect;
    const elementid = connection.id;
    const border = connection.border;
    const baseTurn = parseFloat(connection.povorot) || 30;     // базовий поворот
    const pos1 = connection.pos1 || 'right';
    const pos2 = connection.pos2 || 'left';
    const c1 = d3.select(`#${connect1}`).node();
    const c2 = d3.select(`#${connect2}`).node();
    if (!c1 || !c2) return;
    const c1r = c1.getBoundingClientRect();
    const c2r = c2.getBoundingClientRect();
    let startX = c1r.left + c1r.width  / 2;
    let startY = c1r.top  + c1r.height / 2;
    let endX = c2r.left + c2r.width  / 2;
    let endY = c2r.top  + c2r.height / 2;
    if (isNaN(startX) || isNaN(startY) || isNaN(endX) || isNaN(endY)) return;
    let bend1X = startX, bend1Y = startY;
    let bend2X = endX,   bend2Y = endY;
    let pathData = [];
    if (connect && connect !== false) {
		const coords = connect.match(/([ML])([0-9]+),([0-9]+)/g);
		if (coords) {
        pathData = coords.map(coord => {
			const [_, __, x, y] = coord.match(/([ML])([0-9]+),([0-9]+)/);
			return [parseFloat(x), parseFloat(y)];
        });
      }
    }
    if (pathData.length === 0) {
		const delta = nextDeltaForPair(connect1, connect2, baseTurn);
		pathData.push([startX, startY]);
		switch (pos1) {
        case 'top': bend1Y -= delta; break;
        case 'bottom': bend1Y += delta; break;
        case 'left': bend1X -= delta; break;
        default: bend1X += delta; break; // 'right'
		}
		switch (pos2) {
        case 'top': bend2Y -= delta; break;
        case 'bottom': bend2Y += delta; break;
        case 'left': bend2X -= delta; break;
        default: bend2X += delta; break; // 'right'
		}
		pathData.push([bend1X, bend1Y]);
		if (pos1 !== pos2) {
        pathData.push([bend1X, bend2Y]);
        pathData.push([bend2X, bend2Y]);
		}
		pathData.push([endX, endY]);
		} else {
		pathData.unshift([startX, startY]);
		pathData.push([endX, endY]);
		}
    pathData = pathData.filter((value, index, self) =>
		index === self.findIndex(t => t[0] === value[0] && t[1] === value[1])
    );
    const group = svg.append("g").attr("class", "connection-group").attr("id", `group-${elementid}`);
    const path = group.append("path")
      .datum(pathData)
      .attr("fill", "none")
      .attr("id", elementid)
      .attr("class", "vols_connect")
      .attr("stroke", connection.color || "#999")
      .attr("stroke-width", border)
      .attr("stroke-dasharray", connection.type === "dashed" ? "5, 5" : "none")
      .attr("d", d3.line().curve(d3.curveLinear))
      .on("click", function() {
        d3.select(this).classed("actimel", function() {
          return !d3.select(this).classed("actimel");
        });
        editLine(d3.select(this), volid);
      });

    if (note !== false) path.attr("title", note);
  });
  function editLine(selectedPath, volid) {
    const pathData = selectedPath.datum();
    const drag = d3.drag().on("drag", function(event) {
      const [newX, newY] = d3.pointer(event);
      d3.select(this).attr("cx", newX).attr("cy", newY);
      const pointIndex = +d3.select(this).attr("data-index");
      pathData[pointIndex] = [newX, newY];
      saveCoordinates(pathData, volid);
      selectedPath.attr("d", d3.line().curve(d3.curveLinear)(pathData));
    });
    svg.selectAll(".control-point").remove();
    pathData.forEach((point, i) => {
      if (i > 0 && i < pathData.length - 1) {
        svg.append("circle")
          .attr("cx", point[0]).attr("cy", point[1]).attr("r", 6)
          .attr("fill", "rgb(255, 220, 46)").attr("stroke", "black").attr("stroke-width", 1)
          .attr("class", "control-point").attr("data-index", i).call(drag);
      }
    });
    selectedPath.on("click", function(event) {
      const [newX, newY] = d3.pointer(event);
      let minDistance = Infinity, insertIndex = 1;
      for (let i = 0; i < pathData.length - 1; i++) {
        const [x1, y1] = pathData[i];
        const [x2, y2] = pathData[i + 1];
        const distance = Math.hypot((newX - (x1 + x2) / 2), (newY - (y1 + y2) / 2));
        if (distance < minDistance) { minDistance = distance; insertIndex = i + 1; }
      }
      pathData.splice(insertIndex, 0, [newX, newY]);
      selectedPath.attr("d", d3.line().curve(d3.curveLinear)(pathData));
      saveCoordinates(pathData, volid);
      editLine(selectedPath, volid);
    });
  }

  function saveCoordinates(newCoordinates, volid) {
    if (!newCoordinates || !newCoordinates.length) return;
    const pathString = newCoordinates.map((pt, i) => {
      if (!Array.isArray(pt) || pt.length !== 2) return null;
      const x = parseFloat(pt[0].toFixed(2));
      const y = parseFloat(pt[1].toFixed(2));
      return `${i === 0 ? 'M' : 'L'}${x},${y}`;
    }).filter(Boolean).join('');
    if (!pathString) return;
    $.post("/?do=fiber&act=updatevols", { volid: volid, geo: pathString });
  }
}
function showPopupInside(containerSel, e) {
	const cont = document.querySelector(containerSel);
	const r = cont.getBoundingClientRect();
	const left = e.clientX - r.left;
	const top  = e.clientY - r.top;
	$(".popupPonMenu").appendTo(cont).css({ left: left+"px", top: top+"px" }).fadeIn();
}
function drawLine(x1,y1,x2,y2) {
	return d3.select("#connection-svg").append("line").attr("x1", x1).attr("y1", y1).attr("x2", x2).attr("y2", y2).attr("stroke", "#ffd400").attr("stroke-width", 3).attr("stroke-linecap", "round").attr("class", "preview-connect-line").node();
}
function normalizeDragDatum(element, d) {
	const sel = d3.select(element);
	let datum = (d && typeof d === "object") ? d : sel.datum();
	if (!datum || typeof datum !== "object") {
		datum = {};
	}
	const tr = sel.attr("transform") || "";
	const m = tr.match(/translate\(\s*([-0-9.]+)\s*,\s*([-0-9.]+)\s*\)/);
	if (!Number.isFinite(+datum.x)) {
		datum.x = m ? parseFloat(m[1]) : 0;
	}
	if (!Number.isFinite(+datum.y)) {
		datum.y = m ? parseFloat(m[2]) : 0;
	}
	if (!datum.type) {
		datum.type = sel.attr("data-type") || ((sel.attr("class") || "").split(" ")[0] || "");
	}
	if (datum.elementid === undefined || datum.elementid === null || datum.elementid === "") {
		const rawElementId = sel.attr("data-elementid");
		if (rawElementId !== null && rawElementId !== undefined && rawElementId !== "") {
			const parsedElementId = parseInt(rawElementId, 10);
			datum.elementid = Number.isNaN(parsedElementId) ? rawElementId : parsedElementId;
		}
	}
	sel.datum(datum);
	return datum;
}
function dragstarted(event, d) {
	normalizeDragDatum(this, d);
	d3.select(this).raise().classed("active", true);
}
function dragged(event, d) {
	const nodeData = normalizeDragDatum(this, d);
	nodeData.x = event.x;
	nodeData.y = event.y;
	d3.select(this).attr("transform", `translate(${nodeData.x},${nodeData.y})`);
}
function dragended(event, d) {
	const nodeData = normalizeDragDatum(this, d);
	const id = d3.select(this).attr("id");
	const elementid = nodeData.elementid ?? null;
	const type = nodeData.type ?? "";
    const left = nodeData.x;
    const top = nodeData.y;
    sendposition(id, type, elementid, left, top);
}
function insert_ponmap(action,type) {	
	var element = document.getElementById('datas');
	var elementid = element.getAttribute('data-element');	
	var mouseX = 200;
    var mouseY = 60;
	$(".popupPonMenu").css({top: mouseY + "px",left: mouseX + "px"}).fadeIn();
    event.preventDefault();
    $.ajax({url: root + 'ajax/editorpm.php?act=element',method: 'POST',data: {action: action,id: elementid,type: type,top: mouseY,left: mouseX
	},success: function(response) {
		$("#ponmemueditor").html(response);
    }});
}
function sendposition(id, type, elementid, left, top) {	
	var element = document.getElementById('datas');
	var elementid = element.getAttribute('data-element');	
    if (left !== undefined && top !== undefined) {
        const params = new URLSearchParams({
            id, d: type, el: elementid, l: left, t: top
        });        
        fetch("?do=fiber&act=up",{
            method: "POST",body: params
        });

    }	
}
function toggleMenu() {
	var menu = document.getElementById('pon-menu');
	var showButton = document.getElementById('show-menu');
	if (menu.style.display === 'none' || menu.style.display === '') {
		menu.style.display = 'block';
	} else {
		menu.style.display = 'none';
	}
}
document.addEventListener('click', function(event) {
    var menu = document.getElementById('pon-menu');
    var showButton = document.getElementById('show-menu');
    if (menu && showButton) {
        if (!menu.contains(event.target) && !showButton.contains(event.target)) {
            menu.style.display = 'none';
            showButton.style.display = 'block';
        }
    } else {
        //console.error('Menu or show button not found in the DOM.');
    }
});
$(document).on('click', '.dir-btn', function (e) {
	$(".popupPonMenu").hide();
	e.preventDefault();
	e.stopPropagation();
	const $btn = $(this);
	const dir = $btn.data('dir');
	const id  = $btn.closest('.dir-chooser').data('id');
	if (!id || !dir) return;
	$.post(root + '?do=fiber&act=updatespliters', { id: id, direction: dir })
    .always(function () {
		location.reload();
    });
});
// ТРАСУВАННЯ ВОЛОКНА, САМОПИСНИЙ МОЖУТЬ БУТИ БАГИ
const traceTipCss = `
.trace-tip{
  position:absolute; z-index:100000; background:#1f2a33; color:#e8f1f5;
  padding:8px 10px; border-radius:6px; box-shadow:0 6px 20px rgba(0,0,0,.25);
  font-size:12px; line-height:1.35; max-width:320px; pointer-events:none; white-space:nowrap;
}
.trace-tip b{ color:#ffdf70; }
.trace-tip .end{ color:#ff8a4d; }
`;
(function addTipCss(){
	if (!document.getElementById('trace-tip-css')){
    const st = document.createElement('style');
    st.id = 'trace-tip-css';
    st.textContent = traceTipCss;
    document.head.appendChild(st);
	}
})();
function formatTraceTooltip(json){
	if (!json || !json.ok) return 'Немає даних.';
	const lines = [];
	if (Array.isArray(json.paths) && json.paths.length){
    json.paths.forEach((path, idx) => {
		const head = (json.paths.length>1) ? `Підключення ${idx+1}:` : 'Маршрут:';
		lines.push(`<b>${head}</b>`);
		const items = path.map(n => {
        const el = (n.element || '(елемент ?)');
        return el;
		});
		lines.push(items.join(' → '));
		const endConn = path.length ? path[path.length-1].connector : '';
		const isEnd = (json.endpoints || []).includes(endConn);
		lines.push(`<span class="end">${isEnd ? 'немає комутації' : 'невідомо'}</span>`);
		lines.push('');
		});
	} else {
    lines.push('Маршрут не знайдено.');
	}
	return lines.join('<br>');
}
function showTraceTip(html, x, y){
	let el = document.querySelector('.trace-tip');
	if (!el){
    el = document.createElement('p');
    el.className = 'trace-tip';
    document.body.appendChild(el);
	}
	el.innerHTML = html;
	el.style.left = (x + 16) + 'px';
	el.style.top  = (y + 12) + 'px';
}
function hideTraceTip(){
  const el = document.querySelector('.trace-tip');
  if (el) el.remove();
}
function escapeHtml(value){
	const str = value === null || value === undefined ? '' : String(value);
	return str
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}
$(document).on('mouseenter', '#connection-svg .conn', function(e){
	const id = this.id;
	if (!id) return;
	const localNote = (this.getAttribute('data-note') || '').trim();

	$.post(root + 'ajax/editorpm.php?act=trace', { conn:id }, function(resp){
    if (!resp || !resp.ok || resp.has_commutation === false || !Array.isArray(resp.paths) || !resp.paths.length) {
		if (localNote) {
			showTraceTip(`<b>Коментар порту:</b><br>${escapeHtml(localNote)}`, e.pageX, e.pageY);
		} else {
			hideTraceTip();
		}
		return;
    }
    const firstPath = resp.paths[0];
    if (!Array.isArray(firstPath) || firstPath.length < 2) {
		if (localNote) {
			showTraceTip(`<b>Коментар порту:</b><br>${escapeHtml(localNote)}`, e.pageX, e.pageY);
		} else {
			hideTraceTip();
		}
		return;
    }
    const html = (localNote ? `<b>Коментар порту:</b> ${escapeHtml(localNote)}<br><br>` : '') + formatTraceTooltip(resp);
    showTraceTip(html, e.pageX, e.pageY);
	}, 'json');
});
$(document).on('mousemove', '#connection-svg .conn', function(e){
	const tip = document.querySelector('.trace-tip');
	if (tip){
    tip.style.left = (e.pageX + 16) + 'px';
    tip.style.top  = (e.pageY + 12) + 'px';
	}
});
$(document).on('mouseleave', '#connection-svg .conn', function(){
	hideTraceTip();
});
