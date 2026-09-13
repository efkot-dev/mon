<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if(!$access->get('board_fault_edit')) {
	$go->go('/?do=board');
	exit;	
}
$speedbar = '';
$metatags = array(
	'title'=>'Нова аварій','description'=>'Нова аварій','page'=>'board_add'
);
$speedbar .='
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<a class="brmhref" href="/?do=board"><i class="fi fi-rr-angle-left"></i>Дошка аварій</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Нова аварія</span>
';
$content .= '
<link href="../style/css/select2.css" rel="stylesheet">
<script src="../style/js/select2.min.js"></script>
<div id="onu-speedbar">
	'.$speedbar.'
</div>
';
$locations = $pdo->query("SELECT id, name FROM location ORDER BY name ASC")->fetchAll();
$location_options = "<option value=''></option>";
foreach ($locations as $loc) {
    $location_options .= "<option value='" . (int)$loc['id'] . "'>" . $loc['name'] . "</option>";
}
$switches = $pdo->query("SELECT id, place, netip FROM switch ORDER BY place ASC")->fetchAll();
$switch_options = "<option value=''></option>";
foreach ($switches as $sw) {
    $switch_options .= "<option value='" . (int)$sw['id'] . "'>" . $sw['place'] . " [" . $sw['netip'] . "]</option>";
}
$board_start = date('Y-m-d\TH:00');
$content .= "
<div class='pmon_block' id='board_fault'>
	<div class='pmon_block_left block_white pre40'>	
	<div class='pole1'>
		<div class='img'><img src='../style/img/uptime.png'></div>
		<div class='form1'>Початок<b>Час початку аварії/робіт</b></div>
		<div class='form2'>
			<input type='datetime-local' id='start_time' value='{$board_start}' name='start_time' class='css-input'>
		</div>
	</div>
	<div class='pole1'>
		<div class='img'><img src='../style/img/uptime.png'></div>
		<div class='form1'>Відновлення<b>Час відновлення робіт</b></div>
		<div class='form2'>
		  <input type='datetime-local' name='end_time'  id='end_time' class='css-input'>
		</div>
	</div>
	<div class='pole1'>
		<div class='img'><img src='../style/img/pmon_error.png'></div>
		<div class='form1'>Назва робіт<b>Короткий опис робіт</b></div>
		<div class='form2'>
			<input style='width:100%;' id='name'  name='name' class='input1' type='text'>
		</div>
	</div>	
	<div class='pole1'>
		<div class='img'><img src='../style/img/module_profile.png'></div>
		<div class='form1'>Виконання<b>Автоматичне закриття</b></div>
		<div class='form2'>
			<select class='select' name='cron' id='cron'>
			<option value='0'>Manual</option>
			<option value='1'>Auto</option></select>
		</div>
	</div>
	<div class='pole1'>
		<div class='img'><img src='../style/img/uptime.png'></div>
		<div class='form1'>Час відновлення<b>Уточнюється</b></div>
		<div class='form2'>
			<input type='checkbox' id='horns' name='horns' />
		</div>
	</div>		
	<div class='pole1'>
		<div class='img'><img src='../style/img/module_profile.png'></div>
		<div class='form1'>Телеграм<b>Сповіщення в чат Телеграму</b></div>
		<div class='form2'>
			<select class='select' name='telegram' id='telegram'>
			<option value='1'>Сповістити</option>
			<option value='0'>Ні</option></select>
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
		<textarea id='content' name='content' style='width:100%;height:300px;' class='input_note'></textarea>
	</div>	
	<button style=\"margin-top:15px;\" type=\"button\" id=\"saveData\" class=\"m10b\">Зберегти</button>
	<div id=\"saveStatus\" style=\"margin-top:10px;\"></div>
	</div>
	<div class=\"pmon_block_right pre60\">
		<h3> Адреси робіт</h3>
		<table id='locationsTable' class='resp-tab' style='width: 100%;'>
			<thead><tr><th>Локація</th><th>Вулиці</th><th>Дії</th></tr></thead>
			<tbody></tbody>
		</table>
		<button type='button' id='addlocation' class='m10b'>Додати Локацію</button>
		<h3>Комутатори та Порти</h3>
		<table id='switchesTable' class='resp-tab' style='width: 100%;'>
			<thead><tr><th>Комутатор</th><th>Порти</th><th>Дії</th></tr></thead>
			<tbody></tbody>
		</table>
		<button type='button' id='addswitch' class='m10b'>Додати Комутатор</button>
	</div>
</div>";
$content .= <<<HTML
<script>
$(document).ready(function () {
    $('#addlocation').click(function () {
        let newRow = `
        <tr>
            <td width="25%">
                <select name='location_id[]' class='location-select' style='width: 100%;'>
                    $location_options
                </select>
            </td>
            <td width="65%">
				<select name='street_id[]' class='sub-location-select' multiple style='width: 100%;'></select>
				<div class="house-numbers-wrap" style="margin-top:5px;"></div>
			</td>
            <td width="5%"><span class="remove-row">❌</span></td>
        </tr>`;
        $('#locationsTable tbody').append(newRow);
        $('.location-select, .sub-location-select').select2();
    });
    $(document).on('change', '.location-select', function () {
        let locationId = $(this).val();
        let subSelect = $(this).closest('tr').find('.sub-location-select');
        subSelect.empty().select2('destroy');        
        if (locationId) {
            $.ajax({
                url: '{$url_border}&act=get_street',
                method: 'POST',
                dataType: 'json',
                data: { location_id: locationId },
                success: function (streets) {
                    if (!Array.isArray(streets)) {
                        return;
                    }
                    streets.forEach(function (street) {
                        subSelect.append('<option value="' + street.id + '">' + street.name + '</option>');
                    });
                    subSelect.select2({
                        tags: true,placeholder: "Виберіть або додайте вулицю"
                    });
                }
            });
        } else {
            subSelect.select2({
                tags: true,placeholder: "Виберіть або додайте вулицю"
            });
        }
    });
	$(document).on('select2:select', '.sub-location-select', function (e) {
		let subSelect = $(this);
		let streetId = e.params.data.id;
		let streetname = e.params.data.text;
		let houseWrap = subSelect.closest('td').find('.house-numbers-wrap');
		console.log(streetId);
		if (isNaN(streetId)) {
			handleNewStreet(subSelect, houseWrap, streetname);
		} else {
			handleExistingStreet(subSelect, houseWrap, streetId, streetname);
		}
	});
	function handleNewStreet(subSelect, houseWrap, streetname) {
		let locationId = subSelect.closest('tr').find('.location-select').val();
		$.ajax({
				url: '{$url_border}&act=add_street',
				method: 'POST',
				dataType: 'json',
				data: {
					new_street: streetname, location_id: locationId
				},
				success: function (result) {
					console.log(result);
					if (result.status === 'added' || result.status === 'exists') {
						let streetId = result.id;
						if (subSelect.find('option[value="' + streetname + '"]').length === 0) {
							subSelect.append('<option value="' + streetId + '" selected>' + streetname + '</option>');
						}
						subSelect.trigger('change');
						addHouseNumberBlock(houseWrap, streetId, streetname);
					}
				}
		});
	}
	function handleExistingStreet(subSelect, houseWrap, streetId, streetname) {
		addHouseNumberBlock(houseWrap, streetId, streetname);
	}
	function addHouseNumberBlock(houseWrap, streetId, streetname) {
		console.log(streetId);
		if (houseWrap.find('[data-street-id="' + streetId + '"]').length === 0) {
			let houseNumberItem = $('<div>', {
				'class': 'house-number-item',
				'data-vyluk-id': streetId,
				'style': 'margin: 3px 0;'
			});
			let label = $('<label>', {
				'text': streetname + ':'
			});
			let input = $('<input>', {
				'type': 'text',
				'class': 'house-numbers-input',
				'data-street-id': streetId,
				'placeholder': 'номери будинків',
				'style': 'width: 55%; display: inline-block; margin-left:5px;'
			});
			houseNumberItem.append(label).append(input);
			houseWrap.append(houseNumberItem);
		}
	}
	$(document).on('click', '.remove-row', function () {
		let row = $(this).closest('tr');
		row.find('.location-select, .sub-location-select, .switch-select, .ports-select').select2('destroy');
		row.remove();
	});
	$('#saveData').click(function () {
		let locationsData = [];
		let name = $('#name').val().trim();
		let description = $('#content').val().trim();
		let start_time = $('#start_time').val().trim();
		let end_time = $('#end_time').val().trim();
		let cron = $('#cron').val();
		let telegram = $('#telegram').val();
		if (!name) {
			alert('Будь ласка, заповніть поле "name".');
			$('#name').focus();
			return;
		}
		if (!start_time) {
			alert('Будь ласка, заповніть поле "start_time".');
			$('#start_time').focus();
			return;
		}
		if (!end_time) {
			alert('Будь ласка, заповніть поле "end_time".');
			$('#end_time').focus();
			return;
		}
		$('#locationsTable tbody tr').each(function () {
			let locationId = $(this).find('.location-select').val();
			let streetIds = [];
			$(this).find('.house-number-item').each(function() {
				streetIds.push($(this).data('vyluk-id'));
			});
			let houseNumbers = {};
			$(this).find('.house-numbers-input').each(function () {
				houseNumbers[$(this).data('street-id')] = $(this).val();
			});
			locationsData.push({
				location_id: locationId,
				street_ids: streetIds,
				house_numbers: houseNumbers
			});
		});
		let switchesData = [];
		$('#switchesTable tbody tr').each(function () {
			let switchId = $(this).find('.switch-select').val();
			let portIds = $(this).find('.ports-select').val() || [];
			switchesData.push({
				switch_id: switchId,
				port_ids: portIds
			});
		});
		$.ajax({
			url: '{$url_border}&act=save',
			method: 'POST',
			data: {
				name: name,
				description: description,
				start_time: start_time,
				end_time: end_time,
				telegram: telegram,
				cron: cron,
				locations: JSON.stringify(locationsData),
				switches: JSON.stringify(switchesData)
			},
			success: function (response) {
				window.location.href = '/?do=board';
			},
			error: function () {
				$('#saveStatus').html('<span style="color:red;">Error.</span>');
			}
		});
	});
    $('#addswitch').click(function () {
        let newRow = `
        <tr>
            <td width="25%">
                <select name='switch_id[]' class='switch-select'>
                    $switch_options
                </select>
            </td>
            <td width="65%">
                <select name='port_id[]' class='ports-select' multiple style='width: 100%;'>
                    <option value=''></option>
                </select>
            </td>
            <td  width="5%"><span class="remove-row">❌</span></td>
        </tr>`;
        $('#switchesTable tbody').append(newRow);
        $('.switch-select, .ports-select').select2();
    });
    $(document).on('change', '.switch-select', function () {
        let switchId = $(this).val();
        let portsSelect = $(this).closest('tr').find('.ports-select');
        portsSelect.empty();
        if (switchId) {
            $.ajax({
                url: '{$url_border}&act=get_ports',
                method: 'GET',
                dataType: 'json',
                data: { switch_ids: [switchId] },
                success: function (ports) {
                    if (!Array.isArray(ports)) {
                        return;
                    }
                    ports.forEach(function (port) {
                        portsSelect.append('<option value=\"' + port.id + '\">' + port.pon + ' ' + port.descr + '</option>');
                    });
                    portsSelect.trigger('change');
                }
            });
        }
    });
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });
});
</script>
HTML;
?>
