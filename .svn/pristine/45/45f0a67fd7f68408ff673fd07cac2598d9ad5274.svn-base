var real_img_x1=0;
var real_img_y1=0;

function mouse_coord_reload(ps_newpath) {
	real_img_x1 = mousex + $("#canvas").scrollLeft();
	real_img_y1 = mousey - $("#canvas").scrollTop();
	real_img_scroll_left = $("#canvas").scrollLeft();
	real_img_scroll_top = $("#canvas").scrollTop();
	ps_reload_str = "&x1=" + real_img_x1 + "&y1=" + real_img_y1 + "&code=1101&scale=" + ps_scale + "&scrolltop=" + real_img_scroll_top + "&scrollleft=" + real_img_scroll_left;
	console.log(ps_reload_str);
	if (typeof ps_newpath === "undefined") {
		
	} else {
		if (ps_newpath != "") {
			ps_newpath = ps_newpath + ps_reload_str;
			hide_dialog();
			console.log(ps_newpath);
			$("#vols_div_id").load(ps_newpath);
		}
	}
	return;
}
function linked(type, link_id, voc_id, mod_id, x, y) {

	c_type1=c_type;
	c_type=type;
	
	c_linkid1=c_linkid;
	c_vocid1=c_vocid;
	c_modid1=c_modid;
	
	c_linkid=link_id;
	c_vocid=voc_id;
	c_modid=mod_id;

	c_x1 = c_x;
	c_y1 = c_y;

	c_x = x;
	c_y = y;
	
    draw_line_connect();
}

function remove_link(id, idt) {
	if (confirm("Удалить связь?")) {

		
		$.post("komut.php", { action: "remove_link", id: id})

		.done(function( data ) {
			//alert(data);
			//alert("удалили");
			$( "#canvas" ).load( "komm_ajax.php", { "idt": idt } );	
			})
	}
}

function draw_line_connect() {

    if ( 
	(c_x1 && c_y1) &&
	(c_x1!=c_x || c_y1!=c_y)
	) {
        pi_x = (c_x + c_x1) * 0.5;
        pi_y = (c_y + c_y1) * 0.5;
        ps_path = "M" + c_x + "," + c_y + " L" + pi_x + "," + pi_y;
		//console.log(ps_path);
        connect_temp_line.attr({"path": ps_path});
        ps_path = "M" + pi_x + "," + pi_y + " L" + c_x1 + "," + c_y1;
        //console.log(ps_path);
		connect_temp_line2.attr({"path": ps_path});
        connect_line_img.attr({"x": (pi_x - 8), "y": (pi_y - 8)});

    } else {
        connect_temp_line.attr({"path": "M-1,-1 L-1,-1"});
        connect_temp_line2.attr({"path": "M-1,-1 L-1,-1"});
        connect_line_img.attr({"x": -20, "y": 1});
    }
    setTimeout(function () {
        connect_line_img.show();
        return;
    }, 100);
}


function save_connect(idt) {
//console.log("1 link "+c_type+" "+c_linkid+" "+c_vocid);
//console.log("2 link "+c_type1+" "+c_linkid1+" "+c_vocid1);

		$.post("komut.php", { action: "set_link", mod_id: c_modid,mod_id1: c_modid1,idt: idt, t1: c_type1, l1: c_linkid1, id1: c_vocid1, t: c_type, l: c_linkid, id: c_vocid})

		.done(function( data ) {
			//alert(data);
		connect_temp_line.attr({"path": "M-1,-1 L-1,-1"});
		connect_temp_line2.attr({"path": "M-1,-1 L-1,-1"});
		connect_line_img.attr({"x": -20, "y": 1});
			$( "#canvas" ).load( "komm_ajax.php", { "idt": idt } );	
			})
			
}


function showel(id) {
    //console.log("showel: " + id + document.getElementById(id));
    if (document.getElementById(id) !== null) {
        document.getElementById(id).style.display = '';
    }
}

$(document).mousemove(function (e) {
    mousex = e.pageX;
    mousey = e.pageY;
});

function hide_ttip() {
    //console.log("timer");
    pi_ttip_timer = 0;
    hideel("ttip");
}

function hideel(id, ps_typer) {
    if (document.getElementById(id)) {
        document.getElementById(id).style.display = 'none';
        if (ps_typer == 1) {
            document.cookie = "us_el_" + id + "=0";
        }
    }
    return true;
}

function show_ttip(ps_text) {
    showel('ttip');
    ps_el = document.getElementById('ttip');
    if (ps_text != '' && ps_text !== undefined) {
        ps_el.innerHTML = ps_text;
    }
    elWidth = $("#ttip").width();
    pi_temp_x = mousex - $(window).scrollLeft();
    var dpt = window.devicePixelRatio;
    var screenWidth = screen.width / dpt;

    pi_temp_y = mousey + 25 - $(window).scrollTop();
    if (pi_temp_x > (screenWidth - 300)) {
        pi_temp_x = screenWidth - 300;
    }
    if (pi_temp_y > screen.height) {
        pi_temp_y = screen.height - 250;
    }
    ps_el.style.left = pi_temp_x + 'px';
    ps_el.style.top = pi_temp_y + 'px';
    if (pi_ttip_timer > 0) {
        clearTimeout(pi_ttip_timer);
    }
    pi_ttip_timer = setTimeout(hide_ttip, 5000);
}


function saveimg()
{
	//alert("export");
	$("#canvas").hide();
	$("#imgsave").show();
	
	  //Use raphael.export to fetch the SVG from the paper
       var svg = paper.toSVG();

       //Use canvg to draw the SVG onto the empty canvas
       canvg(document.getElementById('myCanvas'), svg);


       //I had to use setTimeout to wait for the canvas to fully load before setting the img.src,
       //will probably not be needed for a simple drawing like this but i ended up with a blank image
       setTimeout(function() {
           //fetch the dataURL from the canvas and set it as src on the image
           var dataURL = document.getElementById('myCanvas').toDataURL("image/png");
           document.getElementById('myImg').src = dataURL;
       }, 100);
}


//===========================================================================================
function move_device(ps_typerdev, ps_code) {
	    mouse_coord_reload();
    ps_el_box = eval(ps_typerdev + "_" + ps_code + "_box");
    ps_el_circle = eval("sp_drag_" + ps_typerdev + "_" + ps_code);
    ps_el_box_width = ps_el_box.attr("width");
    ps_el_box_height = ps_el_box.attr("height");
    if (1 > ps_el_box_width) {
        pmas_temp = ps_el_box.attr("path");
        ps_el_box_width = pmas_temp[2][1] - pmas_temp[0][1];
        ps_el_box_height = pmas_temp[3][2] - pmas_temp[1][2];
    }
    ps_el_box_x1 = ps_el_box.attr("x");
    ps_el_box_y1 = ps_el_box.attr("y");
    ps_el_box_x2 = ps_el_box_x1 + ps_el_box_width;
    ps_el_box_y2 = ps_el_box_y1;
    ps_el_box_x3 = ps_el_box_x1;
    ps_el_box_y3 = ps_el_box_y2 + ps_el_box_height;
    ps_el_box_x4 = ps_el_box_x2;
    ps_el_box_y4 = ps_el_box_y3;
    //console.log(el_border_img.attr());
    //Ширина и высота полотна
    pi_max_x = el_border_img.attr("width");
    pi_max_y = el_border_img.attr("height");
    drag_box.attr({
        "width": ps_el_box_width, 
        "height": ps_el_box_height
    });
    if (real_img_x1 < 1) {
        real_img_x1 = 1;
    }
    if (real_img_y1 < 1) {
        real_img_y1 = 1;
    }
    if (real_img_x1 > (pi_max_x - ps_el_box_width)) {
        real_img_x1 = pi_max_x - ps_el_box_width;
    }
    if (real_img_y1 > (pi_max_y - ps_el_box_height)) {
        real_img_y1 = pi_max_y - ps_el_box_height;
    }
    //New position
    if ("vols" == ps_typerdev) {
        //Correct xy to vols
        if (pi_max_x == ps_el_box_x2) {
            real_img_x1 = ps_el_box_x1;
        }
        if (ps_el_box_x1 <= 1) {
            real_img_x1 = 1;
        }
        if (ps_el_box_y1 <= 1) {
            real_img_y1 = 1;
        }
        //console.log(pi_max_y + "|" + ps_el_box_y1 + "|" + ps_el_box_y4);
        if (pi_max_y == ps_el_box_y4) {
            real_img_y1 = ps_el_box_y1;
        }
    }
    drag_box.transform("t" + real_img_x1 + ", " + real_img_y1);
    ps_el_circle.attr({
        "cx": real_img_x1, 
        "cy": real_img_y1
    });
    return;
}
function save_device_pos(type, iddevice) {
    mouse_coord_reload("editor.php?type=device&act=save_pos&id=" + iddevice + "&devtype=" + type);
    return;
}
function hide_dialog() {
    document.body.style.overflow = 'auto';
    hideel('dialog-box'); 
    hideel('dialog-overlay');
    return;
}
