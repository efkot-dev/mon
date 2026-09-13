
document.getElementById('saveSvgButton').addEventListener('click', function() {
        const svgElement = document.getElementById('connection-svg');
        
        // Отримати вміст SVG
        const svgData = new XMLSerializer().serializeToString(svgElement);
        
        // Створити зображення
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        const img = new Image();
        const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(svgBlob);

        img.onload = function() {
            // Налаштувати розміри полотна
            canvas.width = svgElement.clientWidth;
            canvas.height = svgElement.clientHeight;
            context.drawImage(img, 0, 0);
            URL.revokeObjectURL(url); // Звільнити пам'ять

            // Завантажити зображення
            const link = document.createElement('a');
            link.download = 'mySVGImage.png'; // Назва файлу
            link.href = canvas.toDataURL('image/png');
            link.click();
        };
        img.src = url;
    });
	$(document).ready(function() {
		handleTooltip('.vols_connect');
		handleTooltip('.connector');
	});
	$(document).ready(function() {
		let isDrawing = false;
		let startX, startY, startConnectorId;
		let currentLine = null;
		$(".connector .conn").on("click", function(event) {
		const id = $(this).attr('id');    
		if (!isDrawing) {
			startConnectorId = id;
			console.log(startConnectorId);
			const rect = this.getBoundingClientRect();
			const svgRect = document.getElementById("connection-svg").getBoundingClientRect(); // Отримуємо розміри SVG
			startX = rect.left - svgRect.left + rect.width / 2;
			startY = rect.top - svgRect.top + rect.height / 2;
			isDrawing = true;
		} else {
			const endConnectorId = id;
			const rect = this.getBoundingClientRect();
			const svgRect = document.getElementById("connection-svg").getBoundingClientRect(); // Отримуємо розміри SVG
			const endX = rect.left - svgRect.left + rect.width / 2;
			const endY = rect.top - svgRect.top + rect.height / 2;
			currentLine = drawLine(startX, startY, endX, endY);        
			event.preventDefault();
			$(".popupPonMenu").css({top: endY + "px", left: endX + "px"}).fadeIn();
			$.post(root + "ajax/editorpm.php?act=connect", {
				c1: startConnectorId,c2: endConnectorId,id: <?=$elementid;?>
			}, function(response) {
				$("#ponmemueditor").html(response);
			}, "html");

			isDrawing = false;
		}
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
            method: 'POST',data: formData,success: function(response) {
                location.reload();
            }
        });
    });
   $("#vok-editor111").dblclick(function(event) {
        event.preventDefault();
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        $(".popupPonMenu").css({top: mouseY + "px",left: mouseX + "px"}).fadeIn();
        $.post(root + "ajax/editorpm.php?act=menu",{id:elementid}, function(response) {
            $("#ponmemueditor").html(response);
        }, "html");
    });   
	$("#connection-svg .vols_connect").on("contextmenu", function(event) {
		const id = $(this).attr('id');
        event.preventDefault();
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        $(".popupPonMenu").css({top: mouseY + "px",left: mouseX + "px"}).fadeIn();
        $.post(root + "ajax/editorpm.php?act=vols",{id:id,elementid:elementid}, function(response) {
            $("#ponmemueditor").html(response);
        }, "html");
    });
	$("#connector .block-optik").on("contextmenu", function(event) {
		const element = $(this).attr('id');
        event.preventDefault();
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        $(".popupPonMenu").css({top: mouseY + "px",left: mouseX + "px"}).fadeIn();
        $.post(root + "ajax/editorpm.php?act=vok",{element:element,elementid:elementid}, function(response) {
            $("#ponmemueditor").html(response);
        }, "html");	
    });
	$("#connector .conn").on("contextmenu", function(event) {
		const element = $(this).attr('id');
        event.preventDefault();
        var mouseX = event.pageX;
        var mouseY = event.pageY;
        $(".popupPonMenu").css({top: mouseY + "px",left: mouseX + "px"}).fadeIn();
        $.post(root + "ajax/editorpm.php?act=voke",{element:element,elementid:elementid}, function(response) {
            $("#ponmemueditor").html(response);
        }, "html");
    });
    $(document).on("click", ".pmonmenuclose", function(event) {
        event.preventDefault();
        $(".popupPonMenu").fadeOut();
    });    
	$(document).on("contextmenu", "#connection-svg .splitters", function(event) {
		var mouseX = event.pageX;
        var mouseY = event.pageY;
        $(".popupPonMenu").css({top: mouseY + "px",left: mouseX + "px"}).fadeIn();
        event.preventDefault();
		const id = $(this).attr('id');
        $.post(root + "ajax/editorpm.php?act=editelement",{id:id}, function(response) {
            $("#ponmemueditor").html(response);
        }, "html");
    });
    $(document).on("mousedown", function(event) {
        if (!$(event.target).closest(".popupPonMenu").length && !$(event.target).closest("#vok-editor").length) {
            $(".popupPonMenu").fadeOut();
        }
    });
    $(document).on("click", "#ponmemueditor a", function(event) {
		var mouseX = event.pageX;
        var mouseY = event.pageY;
        event.preventDefault();
        var action = $(this).data("action");
        var id = $(this).data("id");
        var type = $(this).data("get");
        $.ajax({url: root + 'ajax/editorpm.php?act=element',method: 'POST',data: {action: action,id: id,type: type,top: mouseY,left: mouseX
		},success: function(response) {
			$("#ponmemueditor").html(response);
        }});
    });
});