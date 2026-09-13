<?php
if (!defined('PONMONITOR') && !defined('VIDEO')) {
    die('Hacking attempt!');
}
function type_surveillance($oidid){
    switch ($oidid) {
        case 1:
            return '<span class="signal5">NVR</span>';
        case 2:
            return '<span class="signal3">CAM</span>';
        case 3:
            return '<span class="signal2">NVR</span>';
        default:
            return false;
    }
}
$content = '';
$speedbar = '';
$metatags = array(
	'title'=>'Відеоспостереження',
	'description'=>'Відеоспостереження',
	'page'=>'board_main'
);
$speedbar .='
	<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Відеоспостереження</span>
';
$content .= '
<div id="onu-speedbar">
	'.$speedbar.'
</div>
';
$surveillance_list = '';
$sql_surveillance = $pdo->prepare("SELECT * FROM surveillance");
$sql_surveillance->execute();
$surveillance = $sql_surveillance->fetchAll(PDO::FETCH_ASSOC);
foreach ($surveillance as $device) {
$surveillance_list .= '
	<tr class="olt-dev-olt">
	<td class="text_center">'.type_surveillance($device['oidid']).'</td>
	<td><span class="statusonu st_'.$device['status'].'"></span></td>
	<td><span class="net_ip">'.$device['netip'].'</span>
	</td>
	<td class="td_url">
	<a href="'.$url_video.'&act=view&id='.$device['id'].'">'.$device['place'].'</a>
	<span class="timer"><img src="../style/img/refresh.png"><span>'.aftertime($device['updates']).'</span></span>
	</td>
	<td>'.$device['inf'].' 
	'.$device['model'].' 
	'.$device['firmware'].'
	'.$device['uptime'].'
	</td>
	</tr>
';
}
$surveillance_main = '';
$content .= "<div class='pmon_block' id='board_fault'>
	<div class='pmon_block_left block_white pre20'>
		<div class='pole'>		
			<a href='{$url_video}&act=add' class='urlelelement'>Додати новий</a>
			<a href='{$url_video}&act=config' class='urlelelement'>Налаштування</a>
		</div>
		{$surveillance_main}
	</div>
	<div class='pmon_block_right pre80'>
    <div class='table-wrapper'>
    <table id='board_fault'>
        <thead>
            <tr>
                <th width='5%'><center>Тип</center></th>
                <th width='5%'><center>Статус</center></th>
                <th width='15%'>IP</th>
                <th width='25%'>Опис</th>
                <th class='text_center'>Обладнання</th>
            </tr>
        </thead>
		{$surveillance_list}
        <tbody>
    </tbody></table>
	</div>
</div>
</div>";
?>
