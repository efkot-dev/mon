// ================== INIT (залишаємо твої перші 3 рядки) ==================
var element   = document.getElementById('datas');
var elementid = element.getAttribute('data-element');
const svgWrap = d3.select("#vok-editor");

// ================== РОЗШИРЮЄМО ПОЛОТНО #connection-svg ==================
const CANVAS_SIZE = 50000;                     // велике «поле»
const HALF        = CANVAS_SIZE / 2;

const connSvg = d3.select("#connection-svg").attr("x", -HALF).attr("y", -HALF).attr("width",  CANVAS_SIZE).attr("height", CANVAS_SIZE).attr("viewBox", `${-HALF} ${-HALF} ${CANVAS_SIZE} ${CANVAS_SIZE}`).attr("preserveAspectRatio", "xMidYMid meet");

// збільшимо фон усередині #connection-svg
(function ensureBigBackground(){
	const bg = d3.select("#connection-svg > rect");
	if (!bg.empty()) {
		bg.attr("x", -HALF).attr("y", -HALF).attr("width",  CANVAS_SIZE).attr("height", CANVAS_SIZE).attr("fill", "#e7edf3");
	} else {
		d3.select("#connection-svg").insert("rect", ":first-child").attr("x", -HALF).attr("y", -HALF).attr("width",  CANVAS_SIZE).attr("height", CANVAS_SIZE).attr("fill", "#e7edf3");
	}
})();
const svgRoot = d3.select("#ekran-svg");
const stageRoot = d3.select("#connection-group");
const panOnly = d3.zoom()
	.scaleExtent([1, 1])
	.on("zoom", (ev) => {
		stageRoot.attr("transform", ev.transform);
	});
svgRoot.call(panOnly).on("dblclick.zoom", null);
window.addEventListener("keydown", (e) => {
	const step = 200;
	const t = d3.zoomTransform(svgRoot.node());
	let x = t.x, y = t.y;
	if (e.key === "ArrowLeft")  x += step;
	if (e.key === "ArrowRight") x -= step;
	if (e.key === "ArrowUp")    y += step;
	if (e.key === "ArrowDown")  y -= step;
	if (x !== t.x || y !== t.y) {
    svgRoot.transition().duration(100).call(panOnly.transform, d3.zoomIdentity.translate(x, y).scale(1));
	}
});
function clientToStage(clientX, clientY) {
	const svgEl = svgRoot.node();
	const pt = svgEl.createSVGPoint();
	pt.x = clientX;
	pt.y = clientY;
	const stageCTM = stageRoot.node().getScreenCTM();
	return pt.matrixTransform(stageCTM.inverse());
}
function getConnectorCenters(el) {
	const r = el.getBoundingClientRect();
	const clientX = r.left + r.width  / 2;
	const clientY = r.top  + r.height / 2;
	const stagePt = clientToStage(clientX, clientY);
	return { clientX, clientY, stageX: stagePt.x, stageY: stagePt.y };
}
(function centerView(){
	svgRoot.call(panOnly.transform, d3.zoomIdentity.translate(0, 0).scale(1));
})();
const editor = document.getElementById('vok-editor');
$(document).ready(function() {
	handleTooltip('.vols_connect');
	handleTooltip('.connector');
	handleTooltip('.fiber-block');
});
$(document).ready(function() {
	let isDrawing = false;
	let startStage = null;
	let startConnectorId, startParentIds;
	let currentLine = null;
	function openPortCommentEditor(element, pageX, pageY) {
		if (!element) return;
		$(".popupPonMenu").css({ top: pageY + "px", left: pageX + "px" }).fadeIn();
		$.post(root + "ajax/editorpm.php?act=voke", { element: element, elementid: elementid }, function(response) {
			$("#ponmemueditor").html(response);
		}, "html");
	}
	$(document).on("click", "#connection-svg .conn", function(event) {
		const parentIds = $(this).parents().filter('[id]').map(function() {
			return $(this).attr('id');
		}).get();
		const currentId = $(this).attr('id');
		if (!currentId || !parentIds.length) {
			return;
		}
		const centers = getConnectorCenters(this);
		if (!isDrawing) {
			startConnectorId = currentId;
			startParentIds = parentIds;
			startStage = { x: centers.stageX, y: centers.stageY };
			isDrawing = true;
			return;
		}
		if (currentId === startConnectorId) {
			isDrawing = false;
			return;
		}
		const endConnectorId = currentId;
		const endParentIds = parentIds;
		const endStage = { x: centers.stageX, y: centers.stageY };
		currentLine = drawLine(startStage.x, startStage.y, endStage.x, endStage.y);
		showPopupInside("#vok-editor", event);
		$.post(root + "ajax/editorpm.php?act=connect", {
			c1:startConnectorId,c2:endConnectorId,p1:startParentIds[0],p2:endParentIds[0],id:elementid
		}, function(response) {
			$("#ponmemueditor").html(response);
		}, "html");
		isDrawing = false;
	});
	$(document).on("dblclick", "#connection-svg .conn", function(event) {
		const element = $(this).attr('id');
		if (!element) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		isDrawing = false;
		if (currentLine) {
			d3.select(currentLine).remove();
			currentLine = null;
		}
		openPortCommentEditor(element, event.pageX, event.pageY);
	});
	$(document).on("click", ".pmonmenuclose", function(event) {
    event.preventDefault();
    $(".popupPonMenu").fadeOut();
    if (currentLine) {
		d3.select(currentLine).remove();
		currentLine = null;
    }
	});
	$(document).on("submit", "#ponmemueditor form", function(event) {
    event.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
		url: root + '?do=fiber',
		method: 'POST',
		data: formData,
		success: function(response) {
			location.reload();
		}
    });
	});
	$("#vok-editor111").dblclick(function(event) {
    event.preventDefault();
    $(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
    $.post(root + "ajax/editorpm.php?act=menu", { id: elementid }, function(response) {
		$("#ponmemueditor").html(response);
    }, "html");
	});
	$(document).on("contextmenu", "#connection-svg .vols_connect", function(event) {
    const id = $(this).attr('id');
    event.preventDefault();
    $(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
    $.post(root + "ajax/editorpm.php?act=vols", { id:id, elementid:elementid }, function(response) {
		$("#ponmemueditor").html(response);
    }, "html");
	});
	$(document).on("contextmenu", "#connection-svg .block-optik", function(event) {
    const element = $(this).attr('id');
    event.preventDefault();
    $(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
    $.post(root + "ajax/editorpm.php?act=vok", { element:element, elementid:elementid }, function(response) {
		$("#ponmemueditor").html(response);
    }, "html");
	});
	$(document).on("contextmenu", "#connection-svg .conn", function(event) {
    const element = $(this).attr('id');
    event.preventDefault();
    openPortCommentEditor(element, event.pageX, event.pageY);
	});
	$(document).on("contextmenu", "#connection-svg .splitters", function(event) {
    event.preventDefault();
    const id = $(this).attr('id');
    $(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
    $.post(root + "ajax/editorpm.php?act=editelement", { id:id, ponid:elementid }, function(response) {
		$("#ponmemueditor").html(response);
    }, "html");
	});
	$(document).on("contextmenu", "#connection-svg .planars", function(event) {
		event.preventDefault();
		const id = $(this).attr('id');
		$(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
		$.post(root + "ajax/editorpm.php?act=editelement", { id:id, ponid:elementid }, function(response) {
			$("#ponmemueditor").html(response);
		}, "html");
	});
	$(document).on("contextmenu", "#connection-svg .switch", function(event) {
		event.preventDefault();
		const id = $(this).attr('id');
		$(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
		$.post(root + "ajax/editorpm.php?act=editelement", { id:id, ponid:elementid }, function(response) {
			$("#ponmemueditor").html(response);
		}, "html");
	});
	$(document).on("contextmenu", "#connection-svg .cross", function(event) {
		event.preventDefault();
		const id = $(this).attr('id');
		$(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
		$.post(root + "ajax/editorpm.php?act=editelement", { id:id, ponid:elementid }, function(response) {
			$("#ponmemueditor").html(response);
		}, "html");
	});
	$(document).on("contextmenu", "#connection-svg .onu", function(event) {
		event.preventDefault();
		const id = $(this).attr('id');
		$(".popupPonMenu").css({top: event.pageY + "px", left: event.pageX + "px"}).fadeIn();
		$.post(root + "ajax/editorpm.php?act=editelement", { id:id, ponid:elementid }, function(response) {
			$("#ponmemueditor").html(response);
		}, "html");
	});
	$(document).on("mousedown", function(event) {
		if (!$(event.target).closest(".popupPonMenu").length && !$(event.target).closest("#vok-editor").length) {
		  $(".popupPonMenu").fadeOut();
		}
	});
	$(document).on("click", "#ponmemueditor a", function(event) {
		event.preventDefault();
		var action = $(this).data("action");
		var id = $(this).data("id");
		var type = $(this).data("get");
		$.ajax({
			url: root + 'ajax/editorpm.php?act=element',
			method: 'POST',
			data: { action, id, type, top: event.pageY, left: event.pageX },
			success: function(response) {
				$("#ponmemueditor").html(response);
			}
		});
	});
});
