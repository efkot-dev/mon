console.warn = function () {};
var nodes = [];
var olt_data = JSON.parse('{"7":"SFP C+++","4":"SFP C++","3":"SFP C+","1.5":"SFP B+","4.00":"SFP"}');
var splitter_data = JSON.parse('{"3.17;3.19":"50/50","4.1;3.1":"45/55","4.7;2.7":"40/60","4.56;1.93":"35/65","5.39;1.56":"30/70","6.9;1.6":"25/75","7.11;1.6":"20/80","8.16;0.76":"15/85","11.3;0.6":"10/90","13.5;0.32":"5/95"}');
var divider_data = JSON.parse('{"4.3":"1*2","7.4":"1*4","9.5":"1*6","10.7":"1*8","14":"1*16"}');
var connector_data = JSON.parse('{"0.1":"SIGNAL_LOSS"}');

var keys_array = Object.keys(splitter_data);
keys_array.sort(function(a, b) {
    return parseFloat(a) - parseFloat(b);
});

document.addEventListener('DOMContentLoaded', function () {
    var makeTippy = function (node, text) {
        return tippy(node.popperRef(), {
            content: function () {
                var div = document.createElement('div');
                div.innerHTML = text;
                return div;
            },
            arrow: true,
            placement: 'bottom',
            hideOnClick: false,
            sticky: true,
            flip: false,
            boundary: document.querySelector('#cy'),
        });
    };
    var cy = window.cy = cytoscape({
        container: document.getElementById('cy'),
        layout: {
            name: 'breadthfirst',
            fit: true,
            avoidOverlap: true,
            avoidOverlapPadding: 50,
            animate: false,
            padding: 50,
            spacingFactor: 0.9,
            nodeDimensionsIncludeLabels: true,
            minNodeSpacing: 10,
        },
        style: [
        {
			selector: 'node',
			style: {
			'content': 'data(name)',
			'shape': 'roundrectangle',
			'text-halign': 'center',
			'font-size': '10px',
			'color': '#000000',
			'background-image': function (e) {
			  switch (e.data().type) {
				case "olt":
				  return '/style/img/pon_olt.svg';
				case "splitter":
				  return '/style/img/sp.svg';
				case "divider":
				  return '/style/img/18.svg';
			  }
			},
			'background-width': '90%',
			'background-height': '90%',
			'width': '50',
			'height': '50',
			'background-color': '#ffffff',
			'border-width': 1,
			'border-color': '#bfd0df',
			'shadow-blur': 25,
			'shadow-color': 'rgba(0, 0, 0, 0.3)',
			'shadow-offset-x': 0,
			'shadow-offset-y': 0
		  }
		},
		{selector: 'edge',style: {'line-color': '#FFA500','width': 2,'target-arrow-color': '#FFA500','target-arrow-shape': 'triangle'}},
        {selector: 'node[type="connector"]',style: {'content': 'data(name)','shape': 'ellipse','width': '20','height': '20'}},
        {selector: '.eh-handle',style: {'background-color': 'red','width': 12,'height': 12,'shape': 'rectangle','overlay-opacity': 0,'border-width': 12,'border-opacity': 0,'background-image': 'none'}},
        {selector: '.eh-hover',style: {'background-color': 'red'}},
        {selector: '.eh-source',style: {'border-width': 2,'border-color': 'red'}},
        {selector: '.eh-target',style: {'border-width': 2,'border-color': 'red'}},
        {selector: '.eh-preview, .eh-ghost-edge',style: {'background-color': 'red','line-color': 'red','target-arrow-color': 'red','source-arrow-color': 'red'}},
        {selector: '.eh-ghost-edge.eh-preview-active',style: {'opacity': 0}}
      ],
        elements: {
            nodes: nodes,
            edges: []
        }
    });
    if (savedNodes && savedEdges) {
        savedNodes.forEach(function(node) {
            cy.add({
                group: 'nodes',
                data: {
                    id: node.id,type: node.type,name: node.name,info: node.info
                },
                position: node.position
            });
        });
        savedEdges.forEach(function(edge) {
            cy.add({
                group: 'edges',
                data: {
                    id: edge.id,source: edge.source,target: edge.target
                }
            });
        });
        //cy.layout({ name: 'breadthfirst' }).run();
		calculate();
    }
    var eh = cy.edgehandles();
    eh.disableDrawMode();
    jQuery(document).keyup(function (e) {
        if(e.keyCode === 46) {
            jQuery.each(cy.elements(':visible'), function (k, v) {
                if(v.data("tippy") !== undefined)
                    v.data("tippy").destroy();
            });
            cy.nodes(':selected').remove();
            cy.edges(':selected').remove();
            calculate();
        }
    });
    jQuery('#save_scheme').click(function () {
        var schemeData = collectData();
		var name = $("#pon_name").val();
        jQuery.ajax({
            url: root + '?do=poncalc',
            method: 'POST',
            data: {
                act: 'save',name: name,nodes: JSON.stringify(schemeData.nodes),edges: JSON.stringify(schemeData.edges)
            },
            success: function(response) {
                window.location.href = root + '?do=poncalc';
            }
        });
    });    
	jQuery('#update_scheme').click(function () {
        var schemeData = collectData();
		var name = $("#pon_name").val();
		var id = $("#pon_id").val();
        jQuery.ajax({
            url: root + '?do=poncalc',
            method: 'POST',
            data: {
                act: 'update',name: name,id: id,nodes: JSON.stringify(schemeData.nodes),edges: JSON.stringify(schemeData.edges)
            },
            success: function(response) {
                if (response.success) {
                    alert('Update');
                }
            }
        });
    });
    jQuery('#add_olt').click(function () {
        cy.add({
            group: 'nodes',
            data: { type: 'olt', name: 'SWITCH',
                info: [
                    {name: 'Name', type: 'name', data:'SWITCH'},
                    {name: 'Type', type: 'select', onChange: "typeSelect(this)", data:olt_data},
                    {name: 'Signal', data:Object.keys(olt_data)[0], type:"signal"}
                ]},
            position: { x: cy.width()/2, y: cy.height()/2}
        });
    });
    jQuery('#add_connector').click(function () {
        cy.add({
            group: 'nodes',
            data: { type: 'connector', name: '',  info: [
                    {name: 'Name', type: 'name', data:''},
                    {name: "Attenuation", data:Object.keys(connector_data)[0], type:"signal"}
                ]},
            position: { x: cy.width()/2, y: cy.height()/2}
        });
    });
    jQuery('#add_splitter').click(function () {
        cy.add({
            group: 'nodes',
            data: { type: 'splitter', name: 'Дільник',
                info: [
                    {name: 'Name', type: 'name', data:'Дільник'},
                    {name: 'Type', type: 'range', onChange: 'splitterChange(this)'},
                    {name: 'Attenuation', data:keys_array[parseInt((keys_array.length-1)/2)], type:"signal"}
                ]},
            position: { x: cy.width()/2, y: cy.height()/2}
        });
    });
    jQuery('#add_divider').click(function () {
        cy.add({
            group: 'nodes',
            data: { type: 'divider', name: 'Відгалужувач',
                info: [
                    {name: 'Name', type: 'name', data:'Відгалужувач'},
                    {name: 'Type', type: 'select', onChange: "typeSelect(this)", data:divider_data},
                    {name: 'Attenuation', data:Object.keys(divider_data)[0], type:"signal"}
                ]},
            position: { x: cy.width()/2, y: cy.height()/2}
        });
    });
    cy.on('tapunselect', function (e) {
        var node = e.target;
        jQuery('.info-table').css('display', 'none');
        jQuery.each(jQuery('.info-table input, .info-table select'), function (key, value) {
            var val = jQuery(value).val();
            if(node.data("info")[key].type === "signal") {
                node.data("info")[key].data = val;
            } else if(node.data("info")[key].type === "select"){
                node.data("info")[key].selected = val;
            } else if(node.data("info")[key].type === "range"){
                node.data("info")[key].selected = val;
            } else if(node.data("info")[key].type === "name") {
                node.data("info")[key].data = val;
                node.data("name", val);
            }
        });
        calculate();
        jQuery('.info-table .table').html("");
    });
    cy.on('remove', 'node', function (e) {
        jQuery('.info-table').css('display', 'none');
        jQuery('.info-table .table').html("");
    });
    cy.on('tapselect', 'node', function (e) {
        var node = e.target;
        jQuery.each(node.data("info"), function (key, value) {
            if(value.type === "signal") {
                jQuery('.info-table .table').append(
                    '<tr>' +
                    '<td>' + value.name + '</td>' +
                    '<td><input class="signal form-control form-control-sm" value="' + value.data + '" /></td>' +
                    '</tr>');
            } else if(value.type === "select"){
                var select = '<select onChange="'+value.onChange+'" class="select2 form-control-sm col-md-12">';
                jQuery.each(value.data, function (k, v) {
                    if(value.selected === k) {
                        select += '<option selected value="'+k+'">'+v+'</option>'
                    } else
                    select += '<option value="'+k+'">'+v+'</option>'
                });
                select += '</select>';
                jQuery('.info-table .table').append(
                    '<tr>' +
                    '<td>' + value.name + '</td>' +
                    '<td>'+select+'</td></tr>');
            } else if(value.type === "range"){
                var lenth = Object.keys(splitter_data).length-1;
                var selected = value.selected ? value.selected : (parseInt((keys_array.length-1)/2));
                jQuery('.info-table .table').append(
                    '<tr>' +
                    '<td>' + value.name + '<span class="splitter_type">('+splitter_data[keys_array[selected]]+')</span></td>' +
                    '<td><input onChange="'+value.onChange+'" type="range" min="0" max="'+lenth+'" value="'+selected+'" class="slider form-range w-100""></td>' +
                    '</tr>');
            } else if(value.type === "text"){
                jQuery('.info-table .table').append(
                    '<tr>' +
                    '<td>' + value.name + '</td>' +
                    '<td>' + value.data + '</td>' +
                    '</tr>');
            } else if(value.type === "name"){
                jQuery('.info-table .table').append(
                    '<tr>' +
                    '<td>' + value.name + '</td>' +
                    '<td><input class="name form-control form-control-sm" value="' + value.data + '" /></td>' +
                    '</tr>');
            }
            jQuery(".select2").select2({width: '100%', dropdownAutoWidth: true});
        });
        jQuery('.info-table').css('display', 'block');
    });
    cy.on('remove', 'edge:visible', function (e) {
        if(e.target.data().tippy !== undefined)
        e.target.data().tippy.destroy();
    });
    cy.on('ehcomplete', function (e, source, target, eles) {
        var connections;
        if(target.data("type") === "olt"){
            eles.remove();
        }else if(source.data("type") === "splitter"){
            connections = cy.edges('[source = "'+source.id()+'"]:visible"');
            if(connections.length > 2){
                eles.remove();
            }
        }
        if(target.data("type") === "splitter"){
            connections = cy.edges('[target = "'+target.id()+'"]:visible"');
            if(connections.length > 1){
                eles.remove();
            }
        }
        calculate();
    });
	function calculate(start, signal) {
  if (start === undefined) {
    const edges = cy.edges(':visible');
    jQuery.each(edges, function (_, edge) {
      const source = cy.getElementById(edge.data('source'));
      if (source.data('type') === 'olt') {
        const info = (source.data('info') || []).find(a => a.type === 'signal');
        if (!info || info.data == null) return; // захист
        calculate(source, info.data);
      }
    });
    return;
  }

  // виправлено селектор (прибрана зайва лапка)
  const connections = cy.edges('[source = "' + start.id() + '"]:visible');

  jQuery.each(connections, function (key, connection) {
    // прибираємо старі підказки на ребрі
    if (connection.data('tippy')) connection.data('tippy').destroy();

    if (connection.target().data('type') === 'olt') return;

    // підготовка сигналу
    let calculated_signal = signal;
    if (typeof calculated_signal === 'string' && calculated_signal.includes(';')) {
      calculated_signal = calculated_signal.split(';')[key];
    }

    // перетворення в число + захист від NaN
    let sigNum = Number(calculated_signal);
    if (!Number.isFinite(sigNum)) sigNum = 0;

    // показуємо tippy на ребрі
    let tippy = makeTippy(connection, sigNum.toFixed(2));
    tippy.show();
    connection.data('tippy', tippy);

    // дістаємо info цільового вузла
    const infoArr = connection.target().data('info') || [];
    const info = infoArr.find(a => a.type === 'signal');
    const infoData = info ? info.data : 0;

    let new_signal;

    const targetType = connection.target().data('type');
    if (targetType === 'divider') {
      const divider = connection.target();
      if (divider.data('tippy')) divider.data('tippy').destroy();

      const out = Number(sigNum) - Number(infoData);
      const outSafe = Number.isFinite(out) ? out : 0;

      tippy = makeTippy(divider, outSafe.toFixed(2));
      tippy.show();
      divider.data('tippy', tippy);

      new_signal = outSafe; // divider має один вихід
    } else if (targetType === 'splitter') {
      // очікуємо два значення втрат у info.data, розділені ;
      const losses = String(infoData).split(';');
      const l0 = Number(losses[0]) || 0;
      const l1 = Number(losses[1]) || 0;
      const s0 = (sigNum - l0);
      const s1 = (sigNum - l1);
      new_signal = s0 + ';' + s1;
    } else {
      // звичайний вузол із однією втратою
      const next = sigNum - Number(infoData || 0);
      new_signal = Number.isFinite(next) ? next : 0;
    }

    // рекурсія далі по графу
    calculate(cy.getElementById(connection.target().id()), new_signal);
  });
}

    function calculate2(start, signal) {
      if(start === undefined) {
        var edges = cy.edges(":visible");
        jQuery.each(edges, function (key, value) {
          var source = cy.getElementById(value.data().source);
          if(source.data().type === "olt"){
            var info = source.data("info").find(a => a.type === "signal");
            calculate(source, info.data);
          }
        });
      } else {
        var connections = cy.edges('[source = "'+start.id()+'"]:visible"');
        jQuery.each(connections, function (key, connection) {
          if(connection.data().tippy !== undefined)
            connection.data().tippy.destroy();

          if(connection.target().data("type") === "olt")
            return;

          var calculated_signal = signal;

          if(typeof signal === "string" && signal.includes(';')){
            calculated_signal = signal.split(';')[key];
          }
          var tippy = makeTippy(connection, parseFloat(calculated_signal).toFixed(2));
          tippy.show();
          connection.data().tippy = tippy;
          var info = connection.target().data("info").find(a => a.type === "signal");
          var new_signal;
          if(connection.target().data("type") === "divider"){
            var divider = connection.target();
            if(divider.data().tippy !== undefined)
              divider.data().tippy.destroy();

            calculated_signal = parseFloat(calculated_signal) - parseFloat(info.data);
            tippy = makeTippy(divider, calculated_signal.toFixed(2));
            tippy.show();
            divider.data().tippy = tippy;
          }
          if(connection.target().data("type") === "splitter"){
            var signals = info.data.split(';');
            new_signal = (parseFloat(calculated_signal) - parseFloat(signals[0]))+
              ";"+(parseFloat(calculated_signal) - parseFloat(signals[1]));

          } else {
            new_signal = parseFloat(calculated_signal) - parseFloat(info.data);
          }
          calculate(cy.getElementById(connection.target().id()), new_signal);
        })
      }
    }
    jQuery('#calculator_types').submit(function (e) {
        var result = {};
        jQuery.each(jQuery('#calculator_types input.v_input'), function (k, v) {
            var name = jQuery(v).parent().parent().find('input:not("v_input"):eq(0)');
            if (name.val() != '') {
                var path_string = jQuery(v).attr('name');
                path_string = path_string.replace('*', name.val());
                var path = path_string.split('^');
                var value = jQuery(v).val();
                result = generate(path, result, value);
            }
        });
        var json_string = JSON.stringify(result, null, 2);
        jQuery('input[name="new_types"]').val(json_string);
    });
});
function typeSelect(select) {
    var signal_input = jQuery(select).closest('table').find('.signal');
    if(signal_input.length > 0){
      signal_input.val(jQuery(select).val());
    }
}
function collectData() {
    var nodes = cy.nodes().map(function (node) {
        return {
            id: node.id(),
            type: node.data('type'),name: node.data('name'),info: node.data('info'),position: node.position()
        };
    });
    var edges = cy.edges().map(function (edge) {
        return {
            id: edge.id(),
            source: edge.data('source'),target: edge.data('target')
        };
    });
    return { nodes: nodes, edges: edges };
}
function splitterChange(input){
    var value = parseInt(jQuery(input).val());
    var signal_input = jQuery(input).closest('table').find('.signal');
    if(signal_input.length > 0){
        jQuery(".splitter_type").html('('+splitter_data[keys_array[value]]+')');
        signal_input.val(keys_array[value]);
    }
}
function add_row(type) {
    var table = jQuery('#'+type.toUpperCase()+'_');
    if (table.find("tbody").length === 0) {
        table.append('<tbody></tbody>')
    }
    table.find("tbody").append('<tr><td><input type="text" class="form-control""></td><td><input type="text" name="'+type+'^*" class="form-control v_input" ></td></tr>')
}
function generate(arr, result, value) {
    for (var i = 0; i < arr.length; i++) {
        if (arr.length === 1) {
            result[arr[i]] = value;
        } else {
            if (!result[arr[i]]) result[arr[i]] = {};
            var old_arr = arr.slice(0);
            arr.shift();
            result[old_arr[i]] = generate(arr, result[old_arr[i]], value);
        }
    }
    return result;
}