function load_system_log(olt) {
    $("#logolt").html("Авторизація...");
    $.post(root + "?do=telnet&act=systemlog", { olt: olt }, function(response) {
        $("#logolt").html("Завантаження даних...");
        $("#logolt").html(response);
    }, "html")
    .fail(function() {
        $("#logolt").html("<span style='color:red'>Error</span>");
    });
}
