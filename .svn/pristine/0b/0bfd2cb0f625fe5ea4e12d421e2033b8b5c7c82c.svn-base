<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
$block_inform = '';
$content_edit = '';
$content_view = '';
$content_location = '';
$content_switch = '';
$moderator = '';
$content = '';
$speedbar = '';

$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;
$type = isset($_GET['type']) ? Clean::text($_GET['type']) : null;
if (!isset($id) || $id <= 0) {
    $go->go('/?do=board'); exit;
}
$sql_board = $pdo->prepare("SELECT * FROM incident WHERE id = :id");
$sql_board->execute(['id' => $id]);
$data_board = $sql_board->fetch(PDO::FETCH_ASSOC);
if (!$data_board) { $go->go('/?do=board'); exit; }
$metatags = [
    'title'=> $data_board['reason'].' - Дошка аварій','description' => 'Дошка аварій','page'=> 'board_view'
];
$speedbar .= '
    <a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
    <a class="brmhref" href="/?do=board"><i class="fi fi-rr-angle-left"></i>Дошка аварій</a>
    <span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$data_board['reason'].'</span>
';
$content .= '<div id="onu-speedbar">'.$speedbar.'</div>';
if ($access->get('board_fault_edit') && $data_board['status'] === 'open' && $type === 'edit') {
    $content_edit .= "
    <form name='comment' id='form-comment' action='/?do=board' method='post'>
        <input type='hidden' name='id' value='{$data_board['id']}'>
        <input type='hidden' name='do' value='board'>
        <input type='hidden' name='act' value='edit'>
        <div class='pole1'>
            <div class='img'><img src='../style/img/uptime.png'></div>
            <div class='form1'>Початок<b>Час початку аварії/робіт</b></div>
            <div class='form2'>
                <input type='datetime-local' id='start_time' name='start_time' class='css-input' value='{$data_board['start_time']}'>
            </div>
        </div>
        <div class='pole1'>
            <div class='img'><img src='../style/img/uptime.png'></div>
            <div class='form1'>Відновлення<b>Час відновлення робіт</b></div>
            <div class='form2'>
                <input type='datetime-local' name='end_time' id='end_time' class='css-input' value='{$data_board['restore_time']}'>
            </div>
        </div>
        <div class='pole1'>
            <div class='img'><img src='../style/img/pmon_error.png'></div>
            <div class='form1'>Назва робіт<b>Короткий опис робіт</b></div>
            <div class='form2'>
                <input style='width:100%;' id='name' name='name' class='input1' type='text' value='".htmlspecialchars($data_board['reason'], ENT_QUOTES)."'>
            </div>
        </div>
        <div class='pole1'>
            <div class='img'><img src='../style/img/module_profile.png'></div>
            <div class='form1'>Виконання<b>Автоматичне закриття</b></div>
            <div class='form2'>
                <select class='select' name='cron' id='cron'>
                    <option value='0'>Manual</option>
                    <option value='1' ".($data_board['cron']==1 ? 'selected' : '').">Auto</option>
                </select>
            </div>
        </div>
        <div class='polebtn'>
            <div class='bbdcode'>
                <div onclick=\"insertTag('b')\"><img src='../style/bbcodes/b.png'></div>
                <div onclick=\"insertTag('code')\"><img src='../style/bbcodes/code.png'></div>
                <div onclick=\"insertTag('u')\"><img src='../style/bbcodes/u.png'></div>
                <div onclick=\"insertTag('i')\"><img src='../style/bbcodes/i.png'></div>
                <div onclick=\"insertTag('s')\"><img src='../style/bbcodes/s.png'></div>
            </div>
            <textarea id='content' name='content' style='width:100%;height:200px;' class='input_note'>".$data_board['description']."</textarea>
        </div>
		<div style='text-align:right;margin-top:10px;display: flex;'>
        <button class='m10b' type='submit' id='saveData'  id='btnAddLoc'>{$lang['update']}</button>
        <button class='m10b' id='btnCancelAdd' style='margin-left:6px;'>Скасувати</button>
      </div>
    </form>";
}elseif ($access->get('board_fault_edit') && $type === 'end') {
	$content_edit .= "
    <form name='comment' id='form-comment' action='/?do=board' method='post'>
        <input type='hidden' name='id' value='{$data_board['id']}'>
        <input type='hidden' name='do' value='board'>
        <input type='hidden' name='act' value='end'>
        <div class='pole1'>
            <div class='img'><img src='../style/img/uptime.png'></div>
            <div class='form1'>Відновлення<b>Час відновлення робіт</b></div>
            <div class='form2'>
                <input type='datetime-local' name='end_time' id='end_time' class='css-input' value='{$data_board['restore_time']}'>
            </div>
        </div>
        <div class='polebtn'>
            <div class='bbdcode'>
                <div onclick=\"insertTag('b')\"><img src='../style/bbcodes/b.png'></div>
                <div onclick=\"insertTag('code')\"><img src='../style/bbcodes/code.png'></div>
                <div onclick=\"insertTag('u')\"><img src='../style/bbcodes/u.png'></div>
                <div onclick=\"insertTag('i')\"><img src='../style/bbcodes/i.png'></div>
                <div onclick=\"insertTag('s')\"><img src='../style/bbcodes/s.png'></div>
            </div>
            <textarea id='content' name='content' style='width:100%;height:200px;' class='input_note'>".$data_board['description']."</textarea>
        </div>
		<div style='text-align:right;margin-top:10px;display: flex;'>
        <button class='m10b' type='submit' id='saveData' style='background: green;' id='btnAddLoc'>Виконано</button>
        <button class='m10b' id='btnCancelAdd' style='margin-left:6px;'>Скасувати</button>
      </div>
    </form>";
} else {
    if ($access->get('board_fault_edit') && $data_board['status'] === 'open' && $type !== 'edit') {
        $content_edit .= '
        <div class="board_panel">
            <a href="/?do=board&act=view&id='.$data_board['id'].'&type=edit" class="edit">'.$lang['edit'].'</a>
            '.($data_board['status'] == 'closed' ? '' : '<a href="/?do=board&act=view&id='.$data_board['id'].'&type=end" class="end">'.$lang['close'].'</a>').'
            '.( $access->get('board_fault_delet') ? '<a href="/?do=board&act=delet&id='.$data_board['id'].'" class="del">'.$lang['delet'].'</a>' : '' ).'
            <a href="/?do=board&act=message&id='.$data_board['id'].'" class="mess">Сповістити повторно</a>
        </div>';
    }
}
$start_time = new DateTime($data_board['start_time']);
$end_time = ($data_board['status'] === 'closed' && !empty($data_board['restore_time']))
    ? new DateTime($data_board['restore_time'])
    : new DateTime();

$interval = $start_time->diff($end_time);
$elapsed_time = '';
if ($interval->d > 0) $elapsed_time .= $interval->d . ' д ';
if ($interval->h > 0) $elapsed_time .= $interval->h . ' год ';
if ($interval->i > 0) $elapsed_time .= $interval->i . ' хв';
if ($elapsed_time === '') $elapsed_time = 'менше хвилини';
$block_inform = '';
if ($data_board['status'] === 'open' && !empty($data_board['restore_time'])) {
    $now = new DateTime();
    $restore_time = new DateTime($data_board['restore_time']);
    if ($now > $restore_time) {
        $block_inform = '
        <div class="block_inform">
            <h4>УВАГА</h4>
            <h5>Потрібно перевірити кінцеву дату аварії</h5>
        </div>';
    }
}
$date_start = $date_end = '';
if (!empty($data_board['start_time']) && !empty($data_board['restore_time'])) {
    $start = new DateTime($data_board['start_time']);
    $end = new DateTime($data_board['restore_time']);
    if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
        $date_start = $start->format('H:i');
        $date_end = $end->format('H:i');
    } else {
        $date_start = $start->format('H:i d.m');
        $date_end = $end->format('H:i d.m');
    }
}
if ($type !== 'edit') {
    $content_view .= '
        '.$block_inform.'
        '.loadBarTime($data_board['start_time'], $data_board['restore_time'], $data_board['status'], true).'
        <div class="board_time_view">
            <div class="board_time_start">'.$date_start.'</div>
            <div class="board_time_progress">'.$elapsed_time.'</div>
            <div class="board_time_end">'.$date_end.'</div>
        </div>
        <h2>'.$data_board['reason'].'</h2>
        '.(!empty($data_board['description']) ? '<div class="board_description">'.nl2br($data_board['description']).'</div>' : '').'
    ';
}
$stmt_loc = $pdo->prepare("SELECT ill.*, ill.id as log_id, l.name as location_name, s.name as street_name FROM incident_log_location ill LEFT JOIN location l ON ill.location_id = l.id LEFT JOIN location_street s ON ill.street_id = s.id WHERE ill.incident_id = ?");
$stmt_loc->execute([$data_board['id']]);
$locations = $stmt_loc->fetchAll(PDO::FETCH_ASSOC);
$locations_all = $pdo->query("SELECT id, name FROM location ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$location_options_view = "<option value=''></option>";
foreach ($locations_all as $loc) {
    $location_options_view .= "<option value='".(int)$loc['id']."'>".$loc['name']."</option>";
}
if (!empty($locations)) {
    $content_location .= '<div class="block_white" id="board-location-list">';
    foreach ($locations as $loc) {
        $shortened_name = !empty($loc['street_name']) ? shortenStreetName($loc['street_name']) : '';
        $display = htmlspecialchars((string)$loc['location_name'], ENT_QUOTES, 'UTF-8')
                 . ($shortened_name ? ' '.htmlspecialchars((string)$shortened_name, ENT_QUOTES, 'UTF-8') : '')
                 . (!empty($loc['house_numbers']) ? " ".htmlspecialchars((string)$loc['house_numbers'], ENT_QUOTES, 'UTF-8') : "");

        $content_location .= "<p>
            <span id='in_{$loc['log_id']}' class='text_locat_under loc-item'
                  data-iloc-id='{$loc['log_id']}'
                  data-location-id='{$loc['location_id']}'
                  data-street-id='".($loc['street_id'] ?? '')."'
                  data-street-name='".htmlspecialchars((string)$loc['street_name'], ENT_QUOTES, 'UTF-8')."'
                  data-house='".htmlspecialchars((string)$loc['house_numbers'], ENT_QUOTES, 'UTF-8')."'>
                {$display}
            </span> ";
        if ($access->get('board_fault_edit') && $data_board['status'] === 'open') {
            $content_location .= "
                <a href='#' class='panel_house rr_2 edit-loc' title='Редагувати' style='margin-left:8px;cursor:pointer;'>Редагувати</a>
                <a href='#' class='panel_house rr_1' onclick='del_board_city({$loc['log_id']})' title='Видалити' style='margin-left:6px;cursor:pointer;'>
                    Видалити
                </a>
            ";
        }
        $content_location .= "</p>";
    }
    $content_location .= '</div>';
}

$stmt_sw = $pdo->prepare("SELECT ils.*, sw.place as switch_name, swpon.pon as name_pon, swpon.sfpid as sfpid_pon, swport.descrport as descr FROM incident_log_switch ils LEFT JOIN switch sw ON ils.switch_id = sw.id LEFT JOIN switch_pon swpon ON ils.port_id = swpon.id LEFT JOIN switch_port swport ON swport.deviceid = swpon.oltid AND swport.llid = swpon.sfpid WHERE ils.incident_id = ?");
$stmt_sw->execute([$data_board['id']]);
$switches = $stmt_sw->fetchAll(PDO::FETCH_ASSOC);
if (!empty($switches)) {
    $content_switch .= '<table class="resp-tab list-onu-olt" style="width:100%"><thead><tr>
    <th width="20%">Комутатор</th>
    <th width="20%"><center>PON</center></th>
    <th></th>';
    if ($access->get('board_fault_edit') && $data_board['status'] === 'open') {
        $content_switch .= '<th width="5%"></th>';
    }
    $content_switch .= '</tr></thead><tbody>';
    foreach ($switches as $sw) {
        $selectportolt = '1';
        $content_switch .= "<tr id='switch_{$sw['id']}'>";
        $content_switch .= "<td><a href='/' class='text_locat_under'>".$sw['switch_name']."</a></td>";
        $content_switch .= "<td><a href='/'>{$sw['name_pon']} ".$sw['descr']."</a></td>";
        $content_switch .= "<td>{$selectportolt}</td>";
        if ($access->get('board_fault_edit') && $data_board['status'] === 'open') {
            $content_switch .= "<td><a class='panel_house rr_1' href='#' onclick='del_board_switch({$sw['id']})'>Видалити</a></td>";
        }
        $content_switch .= "</tr>";
    }
    $content_switch .= '</tbody></table>';
}
if ($access->get('board_fault_edit') && $data_board['status'] === 'open') {
    $moderator .= '
        <div class="battery-panel">
            <span class="knopkaor" id="btnAddAddress">Додати адресу</span>
            <span class="knopkagreen" onclick="added_board(\''.$data_board['id'].'\', \'switch\');">Додати комутатор</span>
        </div>
    ';
}
$content .= "
    <div class='pmon_block'>
        <div class='pmon_block_left pre50 board_time_details'>
            <div class='block_white'>
                {$content_view}
                {$content_edit}
            </div>
            {$content_location}
        </div>
        <div class='pmon_block_right pre50'>
            {$moderator}
            {$content_switch}
        </div>
    </div>
";
$content .= <<<HTML
<link href="../style/css/board.css" rel="stylesheet">
<link href="../style/css/select2.css" rel="stylesheet">
<script src="../style/js/select2.min.js"></script>
<div id="editLocModal" style="display:none;">
  <div style="padding:10px 0;">
    <label>Локація</label>
    <select id="el_location" style="width:100%;">{$location_options_view}</select>
  </div>
  <div style="padding:10px 0;">
    <label>Вулиця (необов'язково)</label>
    <div>
      <label style="font-size:12px;">
        <input type="checkbox" id="el_new_street_toggle"> Нова вулиця
      </label>
    </div>
    <select id="el_street" style="width:100%;"></select>
    <input type="text" id="el_new_street" class="input1" style="width:100%; display:none; margin-top:6px;" placeholder="Введіть назву нової вулиці">
    <small style="color:#777;display:block;margin-top:4px;">Можете залишити порожнім, щоб зберегти тільки місто.</small>
  </div>
  <div style="padding:10px 0;">
    <label>Номери будинків</label>
    <input type="text" id="el_houses" class="input1" style="width:100%;" placeholder="напр. 1, 3-7, 10">
  </div>
</div>
<div id="addLocModal" style="display:none;">
  <div style="padding:10px 0;">
    <label>Локація</label>
    <select id="al_location" style="width:100%;">{$location_options_view}</select>
  </div>
  <div style="padding:10px 0;">
    <label>Вулиця (необов'язково)</label>
    <div>
      <label style="font-size:12px;">
        <input type="checkbox" id="al_new_street_toggle"> Нова вулиця
      </label>
    </div>
    <select id="al_street" style="width:100%;"></select>
    <input type="text" id="al_new_street" class="input1" style="width:100%; display:none; margin-top:6px;" placeholder="Введіть назву нової вулиці">
    <small style="color:#777;display:block;margin-top:4px;">Можете залишити порожнім, щоб додати тільки місто.</small>
  </div>
  <div style="padding:10px 0;">
    <label>Номери будинків</label>
    <input type="text" id="al_houses" class="input1" style="width:100%;" placeholder="напр. 1, 3-7, 10">
  </div>
</div>
<script>
(function(){
  var urlGetStreet = '{$url_border}&act=get_street';
  var urlUpdateLoc = '{$url_border}&act=update_loc';
  var urlAddLoc = '{$url_border}&act=add_loc';
  function loadStreets(\$select, locationId, selectedId) {
    \$select.empty().trigger('change');
    if (!locationId) return;
    $.ajax({
      url: urlGetStreet,
      method: 'POST',
      dataType: 'json',
      data: { location_id: locationId },
      success: function(streets) {
        if (!Array.isArray(streets)) {
          return;
        }
        streets.forEach(function(st){
          \$select.append('<option value=\"'+st.id+'\">'+st.name+'</option>');
        });
        if (selectedId) { \$select.val(String(selectedId)).trigger('change'); }
      },
      error: function() {}
    });
  }
  function openEditDialog(rowData) {
    var \$wrap = $('#editLocModal').clone().attr('id','').show();
    var \$loc = \$wrap.find('#el_location');
    var \$st = \$wrap.find('#el_street');
    var \$newT = \$wrap.find('#el_new_street_toggle');
    var \$newS = \$wrap.find('#el_new_street');
    var \$hn = \$wrap.find('#el_houses');
    \$loc.select2({width:'100%', placeholder:'Виберіть локацію'});
    \$st.select2({width:'100%'}); // без tags:true
    if (rowData.location_id) { \$loc.val(String(rowData.location_id)).trigger('change'); }
    \$loc.on('change', function(){ loadStreets(\$st, \$loc.val(), null); });
    if (rowData.street_id) { loadStreets(\$st, \$loc.val(), rowData.street_id); }
    else if (rowData.location_id) { loadStreets(\$st, \$loc.val(), null); }
    if (rowData.house_numbers) { \$hn.val(rowData.house_numbers); }
    \$newT.on('change', function(){
      var on = this.checked;
      \$st.closest('.select2-container').toggle(!on);
      \$st.toggle(!on);
      \$newS.toggle(on);
      if (!on && rowData.street_id) { \$st.val(String(rowData.street_id)).trigger('change'); }
    });
    var \$dlg = $('<div class="board-dialog"></div>').append(\$wrap);
    $('body').append(\$dlg);
    var \$btns = $('<div style="text-align:right;margin-top:10px;display: flex;">' +
        '<button class="m10b" id="btnSaveLoc">Зберегти</button>' +
        '<button class="m10b" id="btnCancelLoc" style="margin-left:6px;">Скасувати</button>' +
      '</div>');
    \$wrap.append(\$btns);
    \$wrap.on('click','#btnCancelLoc',function(){ \$dlg.remove(); });
    \$wrap.on('click','#btnSaveLoc',function(){
      var payload = {
        id: rowData.iloc_id,
        location_id: \$loc.val(),
        street_id: \$st.val() || '',
        new_street: \$newT.is(':checked') ? \$newS.val().trim() : '',
        house_numbers: \$hn.val()
      };
      if (!payload.location_id) { alert('Оберіть локацію'); return; }
      $.ajax({
		  url: urlUpdateLoc,method: 'POST',data: payload,dataType: 'json',
		  success: function (r) {
			if (r && r.ok) {
			  var \$item = $('.loc-item[data-iloc-id="'+rowData.iloc_id+'"]');
			  var city  = $('#el_location option:selected', \$wrap).text();
			  var streetTxt = '';
			  if (\$newT.is(':checked') && \$newS.val().trim()) {
				streetTxt = \$newS.val().trim();
			  } else {
				streetTxt = $('#el_street option:selected', \$wrap).text() || '';
			  }
			  var houses = \$hn.val();
			  var shown = city + (streetTxt ? ' ' + streetTxt : '') + (houses ? ' ' + houses : '');

			  \$item.text(shown)
				.attr('data-location-id', payload.location_id)
				.attr('data-street-id', (r.street_id != null) ? r.street_id : '')
				.attr('data-street-name', streetTxt)
				.attr('data-house', houses);

			  \$dlg.remove();
			  window.location.reload();
			} else {
			  alert(r && r.error ? r.error : 'Помилка збереження');
			}
		  },
		  error: function () {
			alert('Помилка мережі');
		  }
		});

    });
  }
  $(document).on('click', '.edit-loc', function() {
    var \$row = $(this).closest('p').find('.loc-item');
    var data = {
      iloc_id: \$row.data('iloc-id'),
      location_id: \$row.data('location-id'),
      street_id: \$row.data('street-id'),
      street_name: \$row.data('street-name'),
      house_numbers: \$row.data('house')
    };
    openEditDialog(data);
  });
  function openAddDialog() {
    var \$wrap = $('#addLocModal').clone().attr('id','').show();
    var \$loc = \$wrap.find('#al_location');
    var \$st = \$wrap.find('#al_street');
    var \$newT = \$wrap.find('#al_new_street_toggle');
    var \$newS = \$wrap.find('#al_new_street');
    var \$hn = \$wrap.find('#al_houses');
    \$loc.select2({width:'100%', placeholder:'Виберіть локацію'});
    \$st.select2({width:'100%'});
    \$loc.on('change', function(){ loadStreets(\$st, \$loc.val(), null); });
    \$newT.on('change', function(){
      var on = this.checked;
      \$st.closest('.select2-container').toggle(!on);
      \$st.toggle(!on);
      \$newS.toggle(on);
    });
    var \$dlg = $('<div class="board-dialog"></div>').append(\$wrap);
    $('body').append(\$dlg);
    var \$btns = $('<div style="text-align:right;margin-top:10px;display: flex;">' +
        '<button class="m10b" id="btnAddLoc">Додати</button>' +
        '<button class="m10b" id="btnCancelAdd" style="margin-left:6px;">Скасувати</button>' +
      '</div>');
    \$wrap.append(\$btns);
    \$wrap.on('click','#btnCancelAdd',function(){ \$dlg.remove(); });
    \$wrap.on('click','#btnAddLoc',function(){
      var payload = {
        incident_id: {$data_board['id']},
        location_id: \$loc.val(),
        street_id: \$st.val() || '',
        new_street: \$newT.is(':checked') ? \$newS.val().trim() : '',
        house_numbers: \$hn.val()
      };
      if (!payload.location_id) { alert('Оберіть локацію'); return; }
      $.ajax({
			url: urlAddLoc,
			method: 'POST',
			data: payload,
			dataType: 'json', // хай jQuery сам парсить JSON
			success: function(r){
				if (r && r.ok) {
					var shown = (r.location_name || '')
							  + (r.street_name ? ' ' + r.street_name : '')
							  + (r.house_numbers ? ' ' + r.house_numbers : '');
					var html = '<p>'
					  + '<span class="text_locat_under loc-item"'
					  + ' data-iloc-id="'+r.id+'"'
					  + ' data-location-id="'+r.location_id+'"'
					  + ' data-street-id="'+(r.street_id === null ? '' : r.street_id)+'"'
					  + ' data-street-name="'+(r.street_name||'').replace(/"/g,'&quot;')+'"'
					  + ' data-house="'+(r.house_numbers||'').replace(/"/g,'&quot;')+'">'
					  + $('<div/>').text(shown).html()
					  + '</span> ';
					html += '<span class="panel_house rr_1 edit-loc" title="Редагувати" style="margin-left:8px;cursor:pointer;">✎</span>';
					html += '<span class="panel_house rr_1" onclick="del_board_city('+r.id+')" title="Видалити" style="margin-left:6px;cursor:pointer;">'
					  + '<img style="vertical-align: sub;" src="../style/img/close.png"></span>';
					html += '</p>';
					$('#board-location-list').append(html);
					setTimeout(function(){
						window.location.reload();
					}, 200);
				} else {
					alert(r && r.error ? r.error : 'Помилка збереження');
				}
			},
			error: function(){
				alert('Помилка мережі');
			}
		});
    });
  }
  $('#btnAddAddress').on('click', openAddDialog);
})();
</script>
HTML;
