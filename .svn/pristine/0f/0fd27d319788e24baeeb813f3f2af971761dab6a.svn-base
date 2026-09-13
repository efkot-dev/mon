<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
require_once ENGINE_DIR.'functions/editor.php';
$elementid = isset($_POST['id']) ? intval($_POST['id']) : false;
$get = isset($_POST['get']) ? Clean::text($_POST['get']): null;
$js = "";
switch($get) {
    case 'connection':
		$get_ponelement = $db->Simple("SELECT * FROM ponelement where id = '{$elementid}' LIMIT 1");
		$getpon = $db->Simple("SELECT * FROM ponmap where id = '{$elementid}' LIMIT 1");
		$get_tree = $db->Simple("SELECT * FROM pontree where id = '{$get_ponelement['tree']}' LIMIT 1");
		$get_unit = $db->Simple("SELECT * FROM ponunit where id = '{$get_ponelement['unit_id']}' LIMIT 1");
$js .= "
const temp_vok = get_editor_pmon({$elementid},'vok');
generateOptica(temp_vok);

const temp_sp = get_editor_pmon({$elementid},'splitter');
generateSplitters(temp_sp);

const temp_pl = get_editor_pmon({$elementid},'planar');
generatePlanars(temp_pl);

const temp_conn = get_editor_pmon({$elementid},'connect');	
searchElement(temp_conn);

";
$name_pon .= '<div class="pon_name_editor"><a href="?do=fiber&act=viewunit&id='.$get_unit['id'].'"><i class="fi fi-rr-chart-tree"></i>'.$get_unit['name'].'</a></div>';
$name_pon .= '<div class="pon_name_editor"><a href="/?do=fiber&act=viewtree&id='.$get_ponelement['tree'].'"><i class="fi fi-rr-chart-tree"></i>'.$get_tree['name'].'</a></div>';
$name_pon .= '<div class="pon_name_editor"><i class="fi fi-rr-chart-tree"></i>'.$get_ponelement['name'].'</div>';
$name_pon .= '<div class="pon_name_added" onclick="insert_ponmap(\'splitter\')">+ '.$lang['splitters'].'</div>';
#$name_pon .= '<div id="saveSvgButton" class="pon_name_added">Зберегти картинку</div>';
        break;
		case 'added':
			
        break;
    default:

        break;
}

?>
<link href="../style/editorpmon/style.css" type="text/css" rel="stylesheet">
<div id="datas" data-element="<?=$elementid;?>"></div>
	<div id="pon-grid">
		<button id="show-menu" onclick="toggleMenu()"><i class="fi fi-rr-grid"></i></button>
		<?=$name_pon;?>
    </div>	
    <div id="pon-menu" class="menu-pon hidden">
        <p><a href="/"><i class="fi fi-rr-bank"></i><?=$lang['main'];?></a></p>
        <p><a href="/?do=fiber&act=unit"><i class="fi fi-rr-chart-network"></i><?=$lang['volsmeraja'];?></a></p>
    </div>
<div id="vok-editor">
<svg id="ekran-svg"style="display: block; width: 100%;height: 100vh;">
    <g id="connection-group">
		<svg id="connection-svg">
            <rect width="100%" height="100%" fill="#e7edf3" />
        </svg>
    </g>
</svg>
<div class="popupPonMenu"><div class="popupContent"><a href="#" class="pmonmenuclose"><img src="../style/img/close.png"></a><div id="ponmemueditor"></div></div></div>
</div>
<script>
var element = document.getElementById('datas');
var elementid = element.getAttribute('data-element');
const svg = d3.select("#vok-editor");
<?=$js;?>
/*

const data_cross = get_editor_pmon(1,'cross');
generatecross(data_cross);

const data_switch = get_editor_pmon(1, 'switch');
generateSwitches(data_switch);
*/
/*Виклик меню*/

// Check if the content exceeds the viewport
const editor = document.getElementById('vok-editor');
editor.addEventListener('wheel', function(event) {
    // Only scroll if the content overflows
    if (editor.scrollHeight > editor.clientHeight || editor.scrollWidth > editor.clientWidth) {
        event.preventDefault();
        editor.scrollTop += event.deltaY;
        editor.scrollLeft += event.deltaX;
    }
});
document.getElementById('saveSvgButton').addEventListener('click', function() {
    const svgElement = document.getElementById('connection-svg');
    const svgData = new XMLSerializer().serializeToString(svgElement);
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    const img = new Image();
    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);
    img.onload = function() {
		canvas.width = svgElement.clientWidth;
		canvas.height = svgElement.clientHeight;
		context.drawImage(img, 0, 0);
		URL.revokeObjectURL(url); // Звільнити пам'ять
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
		let startX, startY, startConnectorId, parentId;
		let currentLine = null;
	$(".connector .conn").on("click", function(event) {
    const parentIds = $(this).parents().filter('[id]').map(function() {
        return $(this).attr('id');
    }).get();
    const currentId = $(this).attr('id');
    if (!isDrawing) {
        startConnectorId = currentId;
        startParentIds = parentIds;
        const rect = this.getBoundingClientRect();
        const svgRect = document.getElementById("connection-svg").getBoundingClientRect();
        startX = rect.left - svgRect.left + rect.width / 2;
        startY = rect.top - svgRect.top + rect.height / 2;
        isDrawing = true;
    } else {
        const endConnectorId = currentId;
        const endParentIds = parentIds;
        const rect = this.getBoundingClientRect();
        const svgRect = document.getElementById("connection-svg").getBoundingClientRect();
        const endX = rect.left - svgRect.left + rect.width / 2;
        const endY = rect.top - svgRect.top + rect.height / 2;
        currentLine = drawLine(startX, startY, endX, endY);
        event.preventDefault();
        $(".popupPonMenu").css({ top: endY + "px", left: endX + "px" }).fadeIn();
        $.post(root + "ajax/editorpm.php?act=connect", {
			c1: startConnectorId,
			c2: endConnectorId,
			p1: startParentIds[0],
			p2: endParentIds[0],
			id: <?=$elementid;?>
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
	$(document).on("contextmenu", "#connection-svg .planars", function(event) {
		var mouseX = event.pageX;
        var mouseY = event.pageY;
        $(".popupPonMenu").css({top: mouseY + "px",left: mouseX + "px"}).fadeIn();
        event.preventDefault();
		const id = $(this).attr('id');
        $.post(root + "ajax/editorpm.php?act=editelement",{id:id,ponid:<?=$elementid;?>}, function(response) {
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
</script>