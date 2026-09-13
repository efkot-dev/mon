<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$tplresult = '';
$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
if (isset($confPMon['FDB_TABLE']) && !empty($confPMon['FDB_TABLE']) && $confPMon['FDB_TABLE'] == 1){
$tplresult .="
<script>
var id = '{$id}';
var searchText = '';
var offset = 0;
function loadPage(page) {
    offset = page - 1;
   sendRequest();
}
function sendRequest() {
    $.ajax({
        url: 'ajax/fdbmactable.php',
        method: 'post',
        data: {id: id, query: searchText, offset: offset},
        success: function(response){
            $('#result').html(response);
        }
    });
}
$(document).ready(function(){    
    sendRequest();
    $('#search').keyup(function(){
        searchText = $(this).val();
        offset = 0; 
        if (searchText.length >= 3) {
			sendRequest();
		}
    });
    $('#nextPage').click(function(){
		event.preventDefault();
        offset++;
        sendRequest();
    });
    $('#prevPage').click(function(){
		event.preventDefault();
        if (offset > 0) {
            offset--;
            sendRequest();
        }
    });
});
</script>
<div id=\"searcformhmac\">
    <form style=\"width: 300px;margin: 0 0 19px 0px;\">
        <input type=\"text\" id=\"search\" placeholder=\"{$lang['input_search_fdb']}\" autocomplete=\"off\">
    </form>
</div>
<div id=\"result\"></div>
";
	
	
$metatags = array('title'=>'MAC Address Table','description'=>'MAC Address Table','page'=>'fdbmacaddress');

$result ='<div id="onu-speedbar"><a class="brmhref" href="/?do=fdbmacaddress"><i class="fi fi-rr-apps"></i>MAC Address Table</a></div><div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',''.$result.'');
$tpl->compile('content');
$tpl->clear();
}else{
	$go->redirect('main');
}
?>