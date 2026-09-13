<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$ajaxtransportonu = '';
$ajaxstikers = '';
$marker = '';
$add = '';
$addmonitor = '';
$bookmarks = '';
$formaddname = '';
$addonumap = '';
$ajaxponbox = '';
$templates_sheduler = '';
$url_photo_onu = '';
$viewmap = false;
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if(!$id){
	$go->redirect('main');	
}
$sql_ont = $pdo->prepare("SELECT * FROM onus WHERE idonu = :idonu");
$sql_ont->execute(['idonu' => $id]);
$data_ont = $sql_ont->fetch(PDO::FETCH_ASSOC);
$metatags = array('title'=>$data_ont['type'].' '.$data_ont['inface'].' '.$lang['pt_onu'],'description'=>$lang['pd_onu'],'page'=>'onu');
if(!$data_ont['idonu']){
	$go->redirect('main');
}	
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $data_ont['olt']]);
$data_olt = $sql_olt->fetch(PDO::FETCH_ASSOC);
if(!$access->get('dev'.$data_olt['id'])){
	$go->redirect('main');	
}
if(!empty($data_ont['idonu'])){	
	$onukey = (!empty($data_ont['mac'])?$data_ont['mac']:(!empty($data_ont['sn'])?$data_ont['sn']:null));
	if ($onukey !== '') {
        $datatemponu = getFastOnusData($onukey);
    }	
	if(!empty($datatemponu['id'])){
		$ajaxponbox = "ponbox('".$data_ont['idonu']."','".$datatemponu['id']."');";
	}
}
if(!$data_olt['id']){
	$go->redirect('main');	
}
#$url_photo_onu = '<div id="photo_onu_button" data-olt="'.$data_ont['olt'].'" data-onu="'.$data_ont['idonu'].'"><img src="../style/img/photo-gallery.png">Фото ONU</div>';	
if ($access->get('regonu')) {
$templates_sheduler .= '	
	<div id="scheduler_button" data-olt="'.$data_ont['olt'].'" data-onu="'.$data_ont['idonu'].'"><i class="fi fi-rr-settings"></i>Налаштування</div>
	<div id="scheduler_popup">
		<div id="vikno_box">
		<div class="vikno_head">
		<span id="popup_close" style="float:right;"><img style="height: 13px;" src="/style/img/multiply.png"></span>
		</div>
		<div id="popup_content"></div>
		</div>
	</div>
	<div id="popup_overlay"></div>	
';
}
$templates_sheduler ='';
if($config['tag']=='on'){
	if(!empty($datatemponu['tag'])){
		$tpl->load_template('onu/edittag.tpl');
		$tpl->set('{id}',$id);
		$tpl->set('{edit}',$lang['edit']);
		$tpl->set('{marker}',$lang['marker']);
		$tpl->set('{tag}',$datatemponu['tag']);
		$tpl->compile('tag');
		$tpl->clear();	
	}else{
		if($access->get('addtagonu')){	
			$tpl->load_template('onu/addtag.tpl');
			$tpl->set('{id}',$id);
			$tpl->set('{save}',$lang['save']);
			$tpl->set('{marker}',$lang['marker']);
			$tpl->compile('tag');
			$tpl->clear();
		}		
	}
}
$ajaxbilling = '';
if ($config['billing'] == 'on') {
    if (!empty($datatemponu['id'])) {
        $ajaxbilling = 'ajaxbilling(' . $datatemponu['id'] . ');';
	} else {
        $ajaxbilling = 'billing(' . $id . ');';
    }
    if (!empty($datatemponu['uid'])) {
        $tpl->load_template('onu/editbilling.tpl');
        $tpl->set('{id}', $id);
        $tpl->set('{dogovor}', $lang['dogovor']);
        $tpl->set('{edit}', $lang['edit']);
        $tpl->set('{uid}', $datatemponu['uid']);
        $tpl->compile('billing');
        $tpl->clear();
    } elseif ($access->get('addbillingonu')) {
        $tpl->load_template('onu/addbilling.tpl');
        $tpl->set('{id}', $id);
        $tpl->set('{dogovor}', $lang['dogovor']);
        $tpl->set('{save}', $lang['save']);
        $tpl->compile('billing');
        $tpl->clear();
    }
}
$block_list_signal = '';
if (!empty($confPMon['BLOCK_SIGNAL']) && $confPMon['BLOCK_SIGNAL'] == 1) {

$block_list_signal = <<<HTML
<div class="signal-box"><div id="signalList"></div><div id="pagination"></div></div>
<script>
const idOnu = "{$id}";
let currentPage = 1;
const limit = 15;
function loadPage(page) {
    const offset = (page - 1) * limit;
    $.post(root + "?do=service&act=history&idonu=" + idOnu, { offset: offset, limit: limit }, function(res){
        const box = $("#signalList");
        box.empty();
        box.append(
            '<div class="signal-row signal-header">' +
                '<div>RX ONU</div>' +
                '<div>RX OLT</div>' +
                '<div>Дата і час</div>' +
            '</div>'
        );
        if(!res.data || !res.data.length){
            box.append('<div class="signal-row empty">Немає даних</div>');
        } else {
            res.data.forEach(function(item){
				box.append(
					'<div class="signal-row">' +
						'<div class="rx-onu">' + item.onu + '</div>' +
						'<div class="rx-olt">' + item.olt + '</div>' +
						'<div class="time">' + item.time + '</div>' +
					'</div>'
				);
			});
        }
        renderPagination(page, Math.ceil(res.total / limit));
        currentPage = page;
    }, "json");
}
function renderPagination(current, total){
    const pag = $("#pagination");
    pag.empty();
    if(total <= 1) return;
    for(let i = 1; i <= total; i++){
        if(i === 1 || i === total || Math.abs(i-current) <= 1){
            pag.append('<button class="page-btn ' + (i===current?'active':'') + '" data-p="'+i+'">'+i+'</button>');
        } else if(i === current - 2 || i === current + 2){
            pag.append('<span>…</span>');
        }
    }
}
$(document).on("click", ".page-btn", function(){
    const page = parseInt($(this).data("p"));
    if(page) loadPage(page);
});
loadPage(currentPage);
</script>
HTML;
}
if(isset($confPMon['TRANSPORT_ONU']) && !empty($confPMon['TRANSPORT_ONU']) && $confPMon['TRANSPORT_ONU'] == 1) {
$ajaxtransportonu = 'gettransportonu(' . $id . ');';	
}
if(isset($confPMon['STICKERS']) && !empty($confPMon['STICKERS']) && $confPMon['STICKERS'] == 1) {
	if(!empty($data_ont['stikers']) && $data_ont['stikers']==1){
		$bookmarks .= '<div class="onu-olt efect1 m20b mobile"><div class="ont-block-bookmarks"><img src="../style/img/reviews.png"><span>'.$lang['stikers'].'</span><time>'.$data_ont['clockstikers'].'</time><a class="delstikers"  href="#" onclick="ajaxstikers('.$id.',\'del\')">'.$lang['delstikers'].'</a></div></div>';	
	}else{
		$bookmarks .= '<div class="onu-olt efect1 m20b mobile"><div class="ont-block-bookmarks"><img src="../style/img/reviews.png"><a class="addstikers" href="#" onclick="ajaxstikers('.$id.',\'add\')">'.$lang['addstikers'].'</a></div>		</div>';	
	}
	$ajaxstikers = '';
}
if($config['comment']=='on'){
	if(isset($data_ont['comments']) && !empty($data_ont['comments'])){
		$tpl->load_template('onu/editcomm.tpl');
		$tpl->set('{id}',$id);
		$tpl->set('{commentar}',$lang['commentar']);
		$tpl->set('{comments}',$data_ont['comments']);
		$tpl->set('{save}',$lang['save']);
		$tpl->set('{historyurl}','<a class="historycomm" href="#" onClick="historycomment('.$id.'); return false;"><img src="../style/img/history.png">'.$lang['archiv'].'</a>');
		$tpl->set('{editcomm}',$lang['edit']);
		$tpl->compile('comments');
		$tpl->clear();
	}else{
		if($access->get('addcommonu')){	
			$tpl->load_template('onu/addcomm.tpl');
			$tpl->set('{id}',$id);
			$tpl->set('{commentar}',$lang['commentar']);
			$tpl->set('{save}',$lang['save']);
			$tpl->compile('comments');
			$tpl->clear();
		}
	}
}
$curent_position_montajnika = "";
if($access->get('addmaponu')){	
	if(isset($_GET['act']) && $_GET['act']=='del' && $id){
		if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
			$onukey = (!empty($data_ont['mac'])?$data_ont['mac']:(!empty($data_ont['sn'])?$data_ont['sn']:null));
			$cacheManager->delete("onus_data_".$onukey);
			$cacheManager->delete("map_onu_olt_".$data_ont['olt']);
		}
		$stmt = $pdo->prepare("UPDATE onusdata SET lan = '', lon = '' WHERE id = :id");
		$stmt->execute([':id' => $datatemponu['id']]);
		$go->go('/?do=onu&id='.$id);
	}
	if($act=='addmap'){
		$addmonitor .= '<a class="delmonitor" href="/?do=onu&id='.$id.'">'.$lang['closemap'].'</a>';
		if(!empty($datatemponu['lan']) && !empty($datatemponu['lon'])){
			$addmonitor .= '<a class="delmonitor" href="/?do=onu&id='.$id.'&act=delmap">'.$lang['delet'].'</a>';
		}
$curent_position_montajnika = "if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
        L.marker([position.coords.latitude, position.coords.longitude]).bindPopup('SuperMan').addTo(map);
    });
}";
	}else{
		$addmonitor .= '<a class="addmonitor" href="/?do=onu&id='.$id.'&act=addmap">'.$lang['addmap'].'</a>';	
	}
}
if(!empty($datatemponu['lan']) && !empty($datatemponu['lon'])){
	$marker .= "L.marker([".$datatemponu['lan'].",".$datatemponu['lon']."],{icon: L.divIcon({html:'<div class=\"curentmap\">".MapRxTerminal($data_ont['rx'])."</div>'})}).addTo(map);
";
}
if($act=='del_device'){
	$stmt = $pdo->prepare("DELETE FROM onus_equipment WHERE idonu = :idonu");
	$stmt->execute([':idonu' => $id]);
	$go->go('/?do=onu&id='.$id);
}elseif($act=='addmap'){
	$marker .= get_map_onu_olt($data_ont,$id);
	if($act=='addmap'){
		$addonumap = "map.on('click', onMapClick);";
	}
	$viewmap = true;
}elseif(!$act && !empty($datatemponu['lan']) && !empty($datatemponu['lon'])){
	$marker .= get_map_onu_olt($data_ont,$id);
	$viewmap = true;
}else{
	
}
$mapper = getMap();
if($viewmap && !empty($config['geo_lon']) && !empty($config['geo_lan'])){
	if(!empty($data_olt['location'])){
		$datalocation = $db->Fast('location','*',['id'=>$data_olt['location']]);
	}
	$editor = '';
	if($access->get('addmaponu')){
		if(!empty($datatemponu['lan']) && $act!='addmap'){
			$editor .= '<a class="editmapper" href="/?do=onu&id='.$id.'&act=addmap">'.$lang['edit'].'</a>';
			$editor .= '<a class="editmapper" href="/?do=onu&id='.$id.'&act=del">'.$lang['delet'].'</a>';
		}else{
			$editor .= '<a class="editmapper" href="/?do=onu&id='.$id.'">'.$lang['close'].'</a>';
		}
	}
	$tpl->load_template('onu/onumapper.tpl');
	$tpl->set('{id}',$id);
	$tpl->set('{maplan}',(!empty($datatemponu['lan']) ? $datatemponu['lan'] : (!empty($datalocation['lan'])?$datalocation['lan']:$config['geo_lan'])));
	$tpl->set('{maplon}',(!empty($datatemponu['lon']) ? $datatemponu['lon'] : (!empty($datalocation['lon'])?$datalocation['lon']:$config['geo_lon'])));
	$tpl->set('{mapper}',$mapper);
	$tpl->set('{editor}',$editor);
	$tpl->set('{marker}',$marker.$curent_position_montajnika);
	$tpl->set('{visotakartu}',(isset($confPMon['HEIGHT_MAP_ONU']) && !empty($confPMon['HEIGHT_MAP_ONU']) && $confPMon['HEIGHT_MAP_ONU'] >260 ? $confPMon['HEIGHT_MAP_ONU'] : 260));
	$tpl->set('{addonumap}',$addonumap);
	$tpl->compile('onumapper');
	$tpl->clear();
}else{
	if(isset($confPMon['ONU_MAP']) && !empty($confPMon['ONU_MAP']) && $confPMon['ONU_MAP'] == 1) {
		if($access->get('addmaponu')){	
			$add .= '<div class="add_onu_maps"><a href="/?do=onu&id='.$id.'&act=addmap">'.$lang['add_geo_onu'].'</a></div>';
		}
	}
}
$tpl->load_template('onu/main.tpl');
$tpl->set('{id}',$id);
$serialonu = '';
if(!empty($data_ont['mac']))
	$serialonu = '<span class="n">MAC</span><span class="m" id="macAddress">'.$data_ont['mac'].'</span>';
if(!empty($data_ont['sn']))
	$serialonu = '<span class="n">SN</span><span class="m" id="macAddress">'.$data_ont['sn'].'</span>';
$tpl->set('{number_ont}',$serialonu);
$tpl->set('{ajaxsignal}','historysignal(' . $id . ');
log_onu(' . $data_ont['idonu'] . ');
');
$tpl->set('{ajaxbilling}',$ajaxbilling);
$tpl->set('{templates_sheduler}',$url_photo_onu.$templates_sheduler);
$tpl->set('{ajaxponbox}',$ajaxponbox);
$tpl->set('{comments}',isset($tpl->result['comments']) ? $tpl->result['comments'] : '');
$tpl->set('{bookmarks}',isset($bookmarks) ? $bookmarks : '');
$tpl->set('{tag}',isset($tpl->result['tag']) ? $tpl->result['tag'] : '');
$tpl->set('{billing}',isset($tpl->result['billing']) ? $tpl->result['billing'] : '');
$tpl->set('{logonu}','');
$tpl->set('{ajaxstikers}',$ajaxstikers);
$tpl->set('{langcountonu}',$lang['langcountonu']);
$tpl->set('{olt_id}',$data_olt['id']);
$tpl->set('{inface_ont}',mb_strtoupper(trim($data_ont['type']).' '.$data_ont['inface']));
$tpl->set('{olt_place}',$data_olt['place']);
$tpl->set('{olt_updates}',$data_olt['updates']);
$tpl->set('{type_ont}',$data_ont['type']);
$tpl->set('{inface}',$data_ont['inface']);
if ($access->get('rebootonu') && $data_olt['oidid'] == 1 && !empty($data_olt['username'])) {
    $buttonText = empty($data_ont['name']) ? $lang['addopis'] : $lang['editopis'];
    $formaddname .= '<span class="knopka_css" onclick="addopisonu(\'decription\',\'' . $data_olt['oidid'] . '\',\'' . $data_ont['olt'] . '\',\'' . $data_ont['idonu'] . '\')">' . $buttonText . ' [T]</span>';
}
if ($access->get('rebootonu') && $data_olt['oidid'] == 1 && !empty($data_olt['snmprw'])) {
    $buttonText = !empty($data_ont['name']) ? $lang['editopis'] : $lang['addopis'];
    $formaddname .= '
		<span class="knopka_css" onclick="addopisonu(\'notebdcomepon\',\'' . $data_olt['oidid'] . '\',\'' . $data_ont['olt'] . '\',\'' . $data_ont['idonu'] . '\')">' . $buttonText . ' [S]</span>';
}
if ($access->get('rebootonu') && in_array($data_olt['oidid'], [33, 14]) && !empty($data_olt['snmprw'])) {
    $buttonText = !empty($data_ont['name']) ? $lang['editopis'] : $lang['addopis'];
    $formaddname .= '<span class="knopka_css" onclick="addopisonu(\'decription\',\'' . $data_olt['oidid'] . '\',\'' . $data_ont['olt'] . '\',\'' . $data_ont['idonu'] . '\')">' . $buttonText . '</span>';
}
if ($access->get('rebootonu') && $data_olt['oidid'] == 6 && !empty($data_olt['snmprw'])) {
    $buttonText = !empty($data_ont['name']) ? $lang['editopis'] : $lang['addopis'];
    $formaddname .= '<span class="knopka_css" onclick="addopisonu(\'nameonuzte6\',\'' . $data_olt['oidid'] . '\',\'' . $data_ont['olt'] . '\',\'' . $data_ont['idonu'] . '\')">' . $buttonText . '</span>';
}
if ($access->get('rebootonu') && $data_olt['oidid'] == 15 && !empty($data_olt['snmprw'])) {
    $buttonText = !empty($data_ont['name']) ? $lang['editopis'] : $lang['addopis'];
    $formaddname .= '
		<span class="knopka_css" onclick="addopisonu(\'notecdata12\',\'' . $data_olt['oidid'] . '\',\'' . $data_ont['olt'] . '\',\'' . $data_ont['idonu'] . '\')">' . $buttonText . '</span>';
}
$bandwidth = '';
if (!empty($data_ont['inspector']) && $data_ont['inspector']==2 && isset($confPMon['BANDWIDTH']) && !empty($confPMon['BANDWIDTH']) && $confPMon['BANDWIDTH'] == 1 && $access->get('bandwidth_monitor')){
	$sql_traff_monitor = "SELECT id FROM traff_monitor WHERE idonu = :idonu AND types = 'onu' AND deviceid = :deviceid LIMIT 1";
	$sql_traff = $pdo->prepare($sql_traff_monitor);
	$sql_traff->execute([':idonu'  => $data_ont['idonu'],':deviceid' => $data_ont['olt']]);
	$data_traff_monitor = $sql_traff->fetch(PDO::FETCH_ASSOC);
	if(!empty($data_traff_monitor['id'])){
		$bandwidth = monitor_traffic_onu($data_traff_monitor['id'],100);
	}
}
$tpl->set('{nameonu}',(!empty($data_ont['name'])?'<div class="name_onu_olt">'.$data_ont['name'].'</div>':'').$formaddname);
$tpl->set('{bandwidth}','<div id="ont-sys" class="ont-sys">'.$bandwidth.'</div>');
$tpl->set('{map}',isset($tpl->result['onumapper']) ? $tpl->result['onumapper']: $add);
$tpl->set('{olt}',$lang['olt']);
$data_ontPort = $db->Fast('switch_pon','*',['sfpid'=>$data_ont['portolt'],'oltid'=>$data_ont['olt']]);
$tpl->set('{port_id}',(!empty($data_ontPort['id'])?$data_ontPort['id']:''));
#$tpl->set('{countonuport}',(!empty($data_ontPort['count'])?$data_ontPort['count']:''));
#$tpl->set('{supportonuport}',(!empty($data_ontPort['support'])?$data_ontPort['support']:''));
$tpl->set('{ajaxtransportonu}',$ajaxtransportonu);
$tpl->set('{block_list_signal}',$block_list_signal);
$tpl->set('{model}',$lang['model']);
$tpl->set('{port}',$lang['port']);
$tpl->set('{uptimeolt}',$lang['uptimeolt']);
$tpl->set('{netip}',($USER['class']>=4?($config['viewipswitch']=='on'?'<div class="olt-data"><span class="name">IP:</span><span class="data">'.$data_olt['netip'].'</span></div>':''):''));
$tpl->set('{olt_model}',trim($data_olt['inf']).' '.$data_olt['model']);
$tpl->set('{olt_port_ont}',mb_strtoupper($data_ont['type'].' '.cl_inface($data_ont['inface'])));
$tpl->set('{olt_uptime}',($data_olt['uptime']?$data_olt['uptime']:'---'));
$tpl->set('{telnet}',$addmonitor);
$tpl->compile('content');
$tpl->clear();
?>