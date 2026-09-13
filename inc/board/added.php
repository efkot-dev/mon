<?php
if (!defined('PONMONITOR') && !defined('BOARD')) {
    die('Hacking attempt!');
}
if(!$access->get('board_fault_edit')) {
	$go->go('/?do=board');
	exit;	
}
$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
if ($id > 0) {
    $switches = $pdo->query("SELECT id, place FROM switch")->fetchAll();
    $switch_options = "<option value=''></option>";
    foreach ($switches as $sw) {
        $switch_options .= "<option value='" . (int)$sw['id'] . "'>" . $sw['place'] . "</option>";
    }
    okno_title('Додати комутатор');
    echo "
    <form id='switch-form' method='POST' action='/?do=board&act=update&id={$id}'>
		<input class='checkcss' name='message' value='10' type='checkbox'>
		Оповіщення оператора про зміну
		<br>
		<div id='switch-container'>
            <div class='switch-row' style='margin-bottom: 10px;'>
                <select name='switch_id[]' class='switch-select' style='width: 30%;'>
                    $switch_options
                </select>
                <div class='ports-container'></div>
            </div>
        </div>
        <div class='battery-panel'>
        <button type='button' id='addswitch' class='m10b'>Додати комутатор</button>
        <button type='submit' class='m10b'>Надіслати дані</button>
		</div>
    </form>
    <script>
    $(document).ready(function(){
        function loadPorts(selectElement, switchId) {
            let portsContainer = selectElement.closest('.switch-row').find('.ports-container');
            portsContainer.empty();
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
                        let existingPortIds = [];
                        $('.ports-container input[type=\"checkbox\"]').each(function () {
                            existingPortIds.push($(this).val());
                        });
                        ports.forEach(function (port) {
                            if (existingPortIds.indexOf(port.id.toString()) === -1) {
                                let checkbox = '<span class=\"port\" style=\"margin-right:5px;\">' +
                                    '<input type=\"checkbox\" name=\"port_id[' + switchId + '][]\" value=\"' + port.id + '\"> ' +
                                    port.pon + ' ' + port.descr +
                                    '</span>';
                                portsContainer.append(checkbox);
                            }
                        });
                    }
                });
            }
        }
        $(document).on('change', '.switch-select', function () {
            let switchId = $(this).val();
            loadPorts($(this), switchId);
        });
        $('#addswitch').on('click', function(){
            let newRow = $('.switch-row:first').clone();
            newRow.find('.switch-select').val('');
            newRow.find('.ports-container').empty();
            $('#switch-container').append(newRow);
        });
    });
    </script>";
    okno_end();
}
exit;
?>
