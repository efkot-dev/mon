<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$timeupdates = date('Y-m-d H:i:s');
require ROOT_DIR.'/inc/init.monitor.php';
if(isset($jobid)){
	$db->SQLupdate('sender',['added' => date('Y-m-d H:i:s')],['jobid' => $jobid]);
}
$foldercache = ROOT_DIR.'/export/cache/';
if (isset($confPMon['REM_PLANOVI']) && !empty($confPMon['REM_PLANOVI']) && $confPMon['REM_PLANOVI'] == 1) {
### Кіцманський район
$locationArray = [
    'Кліводин', 'Суховерхів', 'Іванківці', 'Ленківці', 'Ошихліби', 'Шишківці', 'Шипинці', 'Витилівка', 'Кіцмань',
    'Мамаївці', 'Лашківка', 'Хлівище', 'Ставчани', 'Киселів', 'Давидівці', 'Валяво', 'Лужани'
];
$dataArray = [];
foreach ($locationArray as $location) {
    $postData = [
        'v_type' => 1,
        'v_diln' => 6,
        'v_city' => $location,
        'v_street' => '',
        'v_house' => '',
        'v_date' => date('d.m.Y'),
        'v_sort_field' => 4,
        'v_sort_type' => 0,
        'v_page' => 1,
    ];
    $url = 'https://oblenergo.cv.ua/shutdowns/planovi_avarijni_result.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        die('Помилка виконання запиту: ' . curl_error($ch));
    }
    curl_close($ch);
    $data = json_decode($response, true);
    if ($data === null) {
        die('Помилка розбору JSON.');
    }
    $html = $data['data'];
    $pattern = '/<tr>(.*?)<\/tr>/s'; // Повний рядок <tr> ... </tr>
    preg_match_all($pattern, $html, $matches);
    $locationData = [];
    foreach ($matches[1] as $row) {
        $cellPattern = '/<td>(.*?)<\/td>/s'; // Повний рядок <td> ... </td>
        preg_match_all($cellPattern, $row, $rowMatches);
        $locationData[] = $rowMatches[1];
    }
    if (isset($locationData) && count($locationData) > 0) {
        $dataArray[] = [
            'name' => $location,
            'data' => $locationData,
        ];
    }
}
if(isset($dataArray)){
$fileContents = serialize($dataArray);
file_put_contents($foldercache . 'kitsman_planovi.data', $fileContents);
$sender = '[icon-plan][b]Планові роботи Кіцманський РЕМ[/b]';
$db->SQLinsert('notification',['status'=>1,'type'=>103,'system'=>'monitor','message'=>$sender,'added'=>$timeupdates]);
}
sleep(1);
$lists_zal = array(
    'Товсте', 'Заліщики', 'Бедриківці', 'Зелений Гай', 'Касперівці', 'Угриньківці', 'Голігради', 'Лисівці', 'Гиньківці', 'Ворвулинці', 'Лисичники'
);
$results = array();
$url = 'https://api.toe.com.ua/api/content/breakPlan';
$data = array('type' => 'Planning','date' => date('Y-m-d'),'remId' => 6);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
foreach ($lists_zal as $list) {
    $data['list'] = $list;
    $jsonData = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Помилка cURL: ' . curl_error($ch);
    }
	$dataArray = json_decode($response, true);
    $results[$list] = $dataArray;
}
if(isset($results)){
	curl_close($ch);
	$fileContentsZal = serialize($results);
	file_put_contents($foldercache . 'zal_planovi.data', $fileContentsZal);
	$sender = '[icon-plan][b]Планові роботи Заліщицький РЕМ[/b]';
	$db->SQLinsert('notification',['status'=>1,'type'=>103,'system'=>'monitor','message'=>$sender,'added'=>$timeupdates]);
}
}
?>
