$(document).ready(function () {
    $('#addlocation').click(function () {
        let newRow = `
        <tr>
            <td width="25%">
                <select name='location_id[]' class='location-select' style='width: 100%;'>
                    <?php echo $location_options;?>
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
                method: 'POST',data: { location_id: locationId },
                success: function (response) {
                    let streets = JSON.parse(response);
                    streets.forEach(function (street) {
                        subSelect.append('<option value="' + street.id + '">' + street.name + '</option>');
                    });
                    subSelect.select2({
                        tags: true,
                        placeholder: "Виберіть або додайте вулицю"
                    });
                }
            });
        } else {
            subSelect.select2({
                tags: true,
                placeholder: "Виберіть або додайте вулицю"
            });
        }
    });
	$(document).on('select2:select', '.sub-location-select', function (e) {
		let subSelect = $(this);
		let streetId = e.params.data.id;
		let streetname = e.params.data.text;
		let houseWrap = subSelect.closest('td').find('.house-numbers-wrap');
		if (isNaN(streetId)) {
			let locationId = subSelect.closest('tr').find('.location-select').val();
			$.ajax({
				url: '{$url_border}&act=add_street',
				method: 'POST',
				data: {
					new_street: streetname,
					location_id: locationId
				},
				success: function (response) {
					let result = JSON.parse(response);
					if (result.status === 'added' || result.status === 'exists') {
						streetId = result.id;
						if (subSelect.find('option[value="' + streetId + '"]').length === 0) {
							subSelect.append('<option value="' + streetId + '" selected>' + streetname + '</option>');
							subSelect.trigger('change');
							if (houseWrap.find('[data-street-id="' + streetId + '"]').length === 0) {
								let houseNumberItem = $('<div>', {
									'class': 'house-number-item',
									'data-street-id': streetId,
									'style': 'margin: 3px 0;'
								});
								let label = $('<label>', {
									'style': 'font-size:12px;',
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
					}
				}
			});
		} else {
			if (houseWrap.find('[data-street-id="' + streetId + '"]').length === 0) {
				let houseNumberItem = $('<div>', {
					'class': 'house-number-item', 
					'data-street-id': streetId, 
					'style': 'margin: 3px 0;'
				});
				let label = $('<label>', {
					'style': 'font-size:12px;',
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
	});
	$(document).on('click', '.remove-row', function () {
		let row = $(this).closest('tr');
		row.find('.location-select, .sub-location-select, .switch-select, .ports-select').select2('destroy');
		row.remove();
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
                data: { switch_ids: [switchId] },
                success: function (response) {
                    let ports = JSON.parse(response);
                    ports.forEach(function (port) {
                        portsSelect.append('<option value=\"' + port.id + '\">' + port.pon + ' (' + port.oltid + ')</option>');
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