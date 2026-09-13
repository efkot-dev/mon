<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$sql_list_onus = $db->SimpleWhile("SELECT o.idonu, o.mac, o.olt, o.sn, o.olt, o.type, o.inface, o.status 
        FROM onus o
        LEFT JOIN billing_usr b ON o.mac = b.onumac OR o.sn = b.onumac
        WHERE b.onumac IS NULL ");
		// AND o.olt = 46
$data_array = json_encode($sql_list_onus);
$tplRes .="
<ul id=\"queue\">
    </ul>
    <div id=\"log\"></div>

    <script>
        $(document).ready(function() {
            let queue = [];
            let sql_list_onus = {$data_array};
            sql_list_onus.forEach(dataonu => {
                let onukey = dataonu.mac ? dataonu.mac : dataonu.sn;
                queue.push({
                    idonu: dataonu.idonu,
                    mac: dataonu.mac,
                    sn: dataonu.sn,
                    olt: dataonu.olt,
                    key: onukey
                });
            });
            function processNext() {
                if (queue.length === 0) {
                    $('#log').append('<p>All requests processed.</p>');
                    return;
                }
                let item = queue.shift();
                $('#queue').append('<li id=\"item-' + item.idonu + '\">Processing IDONU: ' + item.idonu + '</li>');
                $.post('ajax/abills.php', item, function(response) {
                    let result = JSON.parse(response);
                    let statusText = result.status === 'success' ? 'Success' : 'Failed';
                    $('#item-' + result.idonu).append(' - ' + statusText);
                    processNext();
                });
            }
            processNext();
        });
    </script>
";
$tpl->load_template('location/main.tpl');
$tpl->set('{result}',$tplRes);
$tpl->set('{speedbar}',$speedbar);
$tpl->compile('content');
$tpl->clear();