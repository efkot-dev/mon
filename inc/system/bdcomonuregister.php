<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$templatereg = ROOT_DIR . '/file/template/';
if($act=='register'){
	$mac = isset($_POST['mac']) ? Clean::text($_POST['mac']): null;
	$port = isset($_POST['port']) ? Clean::int($_POST['port']): null;
	$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;
	$filePath = $templatereg . 'register_onu.bdcom';
	if (file_exists($filePath) && isset($olt)) {
		$currentTemp = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
		// Виведення масиву рядків
		print_r($currentTemp);
		die;
	}
	echo'<td class="td_name" colspan="3">Готово</td>';
	die;
}
$sqlswitch = $db->SimpleWhile("SELECT * FROM switch");
$arrayswitch = [];
if (!empty($sqlswitch) && count($sqlswitch) > 0) {
    foreach ($sqlswitch as $switch) {
        $id = $switch['id'];
        $arrayswitch[$id] = [
            'id' => $id, 'place' => $switch['place'], 'model' => $switch['inf'] . '' . $switch['model'], 'netip' => $switch['netip']
        ];
    }
}
$metatags = ['title' => $lang['onuerror'], 'description' => $lang['onuerror'], 'page' => 'bdcomeponregister'];
$directory = ROOT_DIR . '/export/cache/';
if (file_exists($directory) && is_dir($directory)) {
    $resultArray = [];
    $files = glob($directory . '*.rejected');
    foreach ($files as $file) {
        $id = pathinfo($file, PATHINFO_FILENAME);
        if (array_key_exists($id, $arrayswitch)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data !== null) {
                $arrayswitch[$id]['onu'] = $data;
                $resultArray[$id] = $arrayswitch[$id];
            } else {
                echo "Помилка розшифрування JSON-даних у файлі: $file";
            }
        }
    }
} else {
    echo "Папка не існує або не є директорією";
}
if(isset($resultArray) && count($resultArray)>0){
$result .= '<table class="resp-tab"><thead><tr>
<th width="20%">Pon</th>
<th width="25%">Mac</th>
<th>Tools</th>
</tr></thead><tbody>';
foreach($resultArray as $ontkey => $data){
	$result .= '<tr><td class="td_name" colspan="3">' . $data['place'] . '</td></tr>';
	foreach($data['onu'] as $mac_onu => $value){
		$md5 = md5($value['mac']);
		$mac = str_ireplace(array('-', '+', 'false', 'null', '.'), '', $value['mac']);
		$result .= '<tr id="mac-'.$md5.'">
		<td>Epon 0/'.$value['port'].'</td>
		<td>'.$value['mac'].'</td>
		<td><span class="registerbdcom" onclick="registerbdcom(\'' . $md5 . '\', \'' . $value['port'] . '\', \'' . $mac . '\', \'' . $data['id'] . '\')">Реєстрація ONU</span>
</td>
		</tr>';
	}
}
}else{
	$result .= '<tr><td class="td_name" colspan="3">'.$lang['empty_search'].'</td></tr>';
}
$result .= '</table>';
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['register'].' ONU BDCOM Epon</span></div>'.$result.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>
