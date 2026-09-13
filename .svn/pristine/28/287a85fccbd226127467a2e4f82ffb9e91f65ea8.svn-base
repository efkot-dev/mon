function sendreger(usr) {
    var postData = {};

    var houseId = $('#house_id').val();
    if (houseId) postData.user_house_id = houseId;

    var streetId = $('#street_id').val();
    if (streetId) postData.user_street_id = streetId;

    var userMob = $('#mob_' + usr).val();
    if (userMob) postData.user_mob = userMob;

    var userPib = $('#pib_' + usr).val();
    if (userPib) postData.user_pib = userPib;

    var userBuild = $('#build_' + usr).val();
    if (userBuild) postData.user_build = userBuild;

    var userKv = $('#kv_' + usr).val();
    if (userKv) postData.user_kv = userKv;

    var userMac = $('#mac_' + usr).val();
    if (userMac) postData.user_mac = userMac;

    // Створюємо форму для відправки POST-запиту
    var form = $('<form>', {
        action: '/?do=taskman&act=add',
        method: 'POST'
    });

    // Додаємо поля з даними до форми
    $.each(postData, function(key, value) {
        form.append($('<input>', {
            type: 'hidden',
            name: key,
            value: value
        }));
    });

    // Додаємо форму до тіла документа і відправляємо її
    form.appendTo('body').submit();
}


$(document).ready(function() {
    $('#street_name').on('input', function() {
        var query = $(this).val();
        if (query.length > 2) {
            $.ajax({
                url: '/?do=taskman&act=abills_getstreet',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var suggestions = data.filter(street => street.streetName.toLowerCase().includes(query.toLowerCase()));
                    var suggestionList = $('<ul class="suggestions"></ul>');
                    suggestions.forEach(function(street) {
                        suggestionList.append('<li data-id="' + street.id + '">' + street.streetName + '</li>');
                    });
                    $('.suggestions').remove();
                    $('#street_name').after(suggestionList);
                }
            });
        }
    });
    $(document).on('click', '.suggestions li', function() {
        var streetName = $(this).text();
        var streetId = $(this).data('id');
        $('#street_name').val(streetName);
        $('#street_id').val(streetId);
        $('.suggestions').remove();
        $.ajax({
            url: '/?do=taskman&act=abills_gethouse',
            method: 'POST',
            data: { id: streetId },
            dataType: 'json',
            success: function(data) {
                var suggestionList = $('<ul class="house-suggestions"></ul>');
                data.forEach(function(house) {
                    suggestionList.append('<li data-id="' + house.id + '">' + house.number + '</li>');
                });
                $('#house_name').after(suggestionList);
            }
        });
    });
    $('#house_name').on('input', function() {
        var query = $(this).val();
        if (query.length > 0) {
            var streetId = $('#street_id').val();
            $.ajax({
                url: '/?do=taskman&act=abills_gethouse',
                method: 'POST',
                data: { id: streetId },
                dataType: 'json',
                success: function(data) {
                    var suggestions = data.filter(function(house) {
                var houseNumber = house.number !== null && house.number !== undefined ? house.number.toString() : '';
                return houseNumber.toLowerCase().includes(query.toLowerCase());
            });
            var suggestionList = $('<ul class="house-suggestions"></ul>');
                    suggestions.forEach(function(house) {
                        suggestionList.append('<li data-id="' + house.id + '">' + house.number + '</li>');
                    });
                    $('.house-suggestions').remove();
                    $('#house_name').after(suggestionList);
                }
            });
        }
    });
    $(document).on('click', '.house-suggestions li', function() {
        var houseNumber = $(this).text();
        var houseId = $(this).data('id');
        $('#house_name').val(houseNumber);
        $('#house_id').val(houseId);
        $('.house-suggestions').remove();
		$('#list_users').html(LoadingBar);
        $.post('/?do=taskman&act=abills_getuser', { streetid: $('#street_id').val(), houseid: houseId }, 
            function(response) {
                $('#list_users').html(response);
            }, 'html'
        );
    });
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.input1, .suggestions, .house-suggestions').length) {
            $('.suggestions').remove();
            $('.house-suggestions').remove();
        }
    });
});
