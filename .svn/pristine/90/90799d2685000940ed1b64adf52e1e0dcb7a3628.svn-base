<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ENGINE_DIR.'functions/charts.php';
$blockonline = null;
$blockoffline = null;
$getpage = $getpage ?? null;
$tplRes ='';
$addjs ='';
$port_this_device = false;
$page = isset($_GET['page']) ? Clean::str($_GET['page']) : null;
$port = isset($_GET['port']) ? Clean::str($_GET['port']) : null;
$view = isset($_GET['view']) ? Clean::str($_GET['view']) : null;
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if(is_valid_id($id)){

}else{	
	$go->redirect('main');	
}
$sql_olt = $pdo->prepare("SELECT * FROM switch WHERE id = :id");
$sql_olt->execute(['id' => $id]);
$dataSwitch = $sql_olt->fetch(PDO::FETCH_ASSOC);
if(!$dataSwitch['id']){
	$go->redirect('main');	
}
if(!$access->get('dev'.$dataSwitch['id'])){
	$go->redirect('main');	
}
$NewDataPort = [];
if($dataSwitch['device']=='olt'){
	$stmt = $pdo->prepare("SELECT * FROM switch_pon WHERE oltid = :id");
	$stmt->execute(['id' => $id]);
	$SQLPon = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$sql_switch_port = $pdo->prepare("SELECT * FROM switch_port WHERE deviceid = :id");
$sql_switch_port->execute(['id' => $id]);
$SQLPortDevice = $sql_switch_port->fetchAll(PDO::FETCH_ASSOC);
$sql_onus = $pdo->prepare("SELECT changerx, status, rxstatus, added, rx FROM onus WHERE olt = :id");
$sql_onus->execute(['id' => $id]);
$sql_data_onu = $sql_onus->fetchAll(PDO::FETCH_ASSOC);
$onutchemgerx = $onutchemgerxd = $counttoday = $onuadded = $viewsignal = $onuoffline = 0;
if (!empty($sql_data_onu)) {
    foreach ($sql_data_onu as $tonu) {
        $changerx_valid = !empty($tonu['changerx']) && checkRXDay($tonu['changerx']);
        $added_valid = !empty($tonu['added']) && checkRXDay($tonu['added']);
        if ($changerx_valid) {
            if ($tonu['rxstatus'] === 'up') {
                $onutchemgerx++;
            } elseif ($tonu['rxstatus'] === 'down') {
                $onutchemgerxd++;
            }
        }
        if ($added_valid) {
            $onuadded++;
        }
        if (!empty($tonu['rx']) && $tonu['status'] == 1) {
            $viewsignal++;
        }
        if (!empty($tonu['status']) && $tonu['status'] == 2) {
            $onuoffline++;
        }
    }
}
$metatags = array('title'=>$lang['pt_detail'].' '.$dataSwitch['place'],'description'=>$lang['pd_detail'],'page'=>'detail');
$panel  = '
<script>
ajaxstatsolt('.$id.',\'olt\');
'.($dataSwitch['oidid']==24?'switch_ajax('.$id.');':'').'
</script>';
if($viewsignal>5 && $dataSwitch['device']=='olt'){
	if($page != 'temp' && $page != 'note' && $page != 'admin' && $page != 'backup' && $page != 'nomdu' && $page != 'connect' && $page != 'viewgallery' && $page != 'gallery' && $page != 'fdbtable'){
		$tplRes .='<div id="graph_signal"></div>';
		$addjs .= 'graph_signal('.$id.');';
	}
}
if($page=='temp'){	
	$lan = $dataSwitch['lan'];
	$lon = $dataSwitch['lon'];
	if(!empty($lon) && !empty($lan)){
		$tplRes .= get_weather_hourly_open_meteo($lan, $lon);
	}
	$tplRes .= get_charts_temp($id).'<br>';
}
require MODULE_PMON.'olt_menu.php';
$tplRes .='<div id="result_graph_signal"></div>';
require MODULE_PMON.'olt_port.php';
require MODULE_PMON.'olt_gallery.php';
if ($page == 'traffic' && $dataSwitch['monitor'] == 'yes') {
	require MODULE_PMON.'olt_traffic.php';	
}elseif($page=='admin' && $dataSwitch['monitor']=='yes'){	
	$tplRes .= getPanelSwitch($dataSwitch);
}elseif($page=='note'){	
	require ENGINE_DIR.'functions/bbcode.php';
	require MODULE_PMON.'note.php';
}elseif($page=='mod1'){	
	require MODULE_PMON.'reg_qinq_huawei.php';
}elseif($page=='backup'){	
	require MODULE_PMON.'backup.php';
}elseif($page=='fdbtable'){
	require MODULE_PMON.'fdbtable.php';
}elseif($page=='regerok'){
	require MODULE_PMON.'regerok.php';
}elseif($page=='monitoring'){
	require MODULE_PMON.'monitoring.php';
}
require MODULE_PMON.'olt_connect.php';
$tplRes .='</div>';
$tpl->load_template('olt/block.tpl');
$tpl->set('{id}',$dataSwitch['id']);
$tpl->set('{monitorstatus}',$panel);
$tpl->set('{script_device_ajax}','
<script>
ajaxdevicestatus('.$id.');
checkStatusCron('.$id.');
loadponsignal('.$id.');
details_taskers('.$id.');
'.$addjs.'
const menuTrigger = document.querySelector(".menu-trigger");
const sideMenu = document.getElementById("sideMenu");
menuTrigger.addEventListener("click", () => {
    sideMenu.classList.toggle("open");
    menuTrigger.classList.toggle("right");
});
document.addEventListener("DOMContentLoaded", function() {
    var menuLinkOlt = document.querySelector(\'.menu_main_olt\');
    var menu_olt = document.querySelector(\'.dr_menu_olt\');
    var menuPositionLeft = menuLinkOlt.getBoundingClientRect().left - 40;
    var menuPositionTop = menuLinkOlt.getBoundingClientRect().top - 40;
    menuLinkOlt.addEventListener("mouseenter", function() {
        menu_olt.style.display = "block";
        menu_olt.style.left = menuPositionLeft + \'px\';
        menu_olt.style.top = menuPositionTop + \'px\';
    });
    menu_olt.addEventListener("mouseleave", function() {
        menu_olt.style.display = "none";
    });
});
</script>');
$sql_switch_photo = $pdo->prepare("SELECT * FROM switch_photo WHERE deviceid = :id LIMIT 1");
$sql_switch_photo->execute(['id' => $id]);
$sql_gallery_photo = $sql_switch_photo->fetch(PDO::FETCH_ASSOC);if(isset($sql_gallery_photo) && !empty($sql_gallery_photo['photo'])){
	$cover_photo = '<a class="hw100" href="/?do=detail&act=olt&id='.$id.'">
			<div class="block-cover"><div class="cover_photo">
				<img src="/?do=thumb&type=photo&img=' . $sql_gallery_photo['photo'] . '&s=1">
			</div>
		</div>
	</a>';
}else{
$cover_photo = '<div class="block-olt-img"><a href="/?do=detail&act=olt&id='.$id.'"><img src="../style/device/'.$dataSwitch['img'].'"></a></div>';
}
$tpl->set('{blockstatsonu}',blockStatsONU($id));
$tpl->set('{countonu}',($dataSwitch['allonu']?'<span><img src="../style/img/online.png" style="height: 16px;">'.$lang['count_port'].'<b>'.$dataSwitch['allonu'].'</b></span>':''));
$tpl->set('{countport}',(isset($SQLPon)?'<span><img src="../style/img/port.png" style="height: 16px;">'.$lang['count_port_pon'].'<b>'.count($SQLPon).'</b></span>':''));
$tpl->set('{sn}',($dataSwitch['sn']?'<span><img src="../style/img/code.png">'.$lang['serial'].'<b>'.$dataSwitch['sn'].'</b></span>':''));
$tpl->set('{mac}',($dataSwitch['mac']?'<span><img src="../style/img/mac.png">MAC<b>'.$dataSwitch['mac'].'</b></span>':''));
$tpl->set('{interval}',($dataSwitch['typecheck']?'<span><img src="../style/img/checker.png">'.$lang['timeinterval'].':<b>'.$lang[$dataSwitch['typecheck']].'</b></span>':''));
$tpl->set('{updates}',($dataSwitch['updates']?'<span><img src="../style/img/on-time.png">'.$lang['timecheck'].':<b>'.$dataSwitch['updates'].'</b></span>':''));
$tpl->set('{updates_port}',($dataSwitch['updates_port']?'<span><img src="../style/img/on-time.png">'.$lang['timecheckport'].':<b>'.$dataSwitch['updates_port'].'</b></span>':''));
$tpl->set('{updates_rx}',($dataSwitch['updates_rx']?'<span><img src="../style/img/on-time.png">'.$lang['timecheckrx'].':<b>'.$dataSwitch['updates_rx'].'</b></span>':''));
$tpl->set('{timecheck}',($dataSwitch['timecheck']?'<span><img src="../style/img/on-time.png">'.$lang['timework'].':<b>'.$dataSwitch['timecheck'].'</b> '.$lang['sec'].'</span>':''));
$tpl->set('{timechecklast}',($dataSwitch['timechecklast']?'<span><img src="../style/img/on-time.png">'.$lang['timeworklast'].':<b>'.$dataSwitch['timechecklast'].'</b> '.$lang['sec'].'</span>':''));
$tpl->set('{update}',(!empty($dataSwitch['update'])?$dataSwitch['update']:''));
$tpl->set('{updaterx}',(!empty($dataSwitch['update_rx'])?$dataSwitch['update_rx']:''));
$tpl->set('{place}',$dataSwitch['place']);
$tpl->set('{netip}',($USER['class']>=4?($config['viewipswitch']=='on'?'<span><img src="../style/img/netip.png">IP<b>'.$dataSwitch['netip'].'</b></span>':''):''));
$tpl->set('{typesdevice}',$dataSwitch['device']);
$tpl->set('{cover_photo}',$cover_photo);
$tpl->set('{firmware}',$dataSwitch['firmware']);
$tpl->set('{blockonline}',($blockonline?$blockonline:''));
$tpl->set('{blockoffline}',($blockoffline?$blockoffline:''));
$tpl->compile('block-content');
$tpl->clear();
$tpl->load_template('block/block-olt.tpl');
$control = '';
$droplist = '';
if($access->get('setupdevice') && $USER['class']>=3){
	$checker_btn = '';
	$droplist = '<div class="dropList dr_menu_olt">
	<div class="gr-menu">
	<a href="/?do=setup&id='.$dataSwitch['id'].'" ><i class="fi fi-rr-settings"></i>PMon config</a>
	<a href="#" onclick="ajaxcore(\'monitor\','.$dataSwitch['id'].');"><i class="fi fi-rr-settings"></i>Access settings</a>
	<a href="/?do=operator&act=restart&id='.$dataSwitch['id'].'"><i class="fi fi-rr-shuffle"></i>Restart taskers</a>
	<a href="/?do=change&id='.$dataSwitch['id'].'"><i class="fi fi-rr-shuffle"></i>Change Template</a>
	<a href="#" onclick="ajaxcore(\'reset\','.$dataSwitch['id'].');"><i class="fi fi-rr-broom"></i>Erase All data</a>
	<a href="#" onclick="ajaxcore(\'delete\','.$dataSwitch['id'].');"><i class="fi fi-rr-delete"></i>Delete Device</a>
	</div></div>';
	$control .='
	<div class="panel_switch">
		<a class="a_settings menu_main_olt" ><img src="../style/img/settings.svg"></a>
	</div>'.$droplist.'
	';
}
$tpl->set('{name}','<div class="style_model"><span>'.$dataSwitch['inf'].'</span>'.$dataSwitch['model'].'</div>'.$control);
$tpl->set('{result}',$tpl->result['block-content']);
$tpl->set('{pagerbottom}','');
$tpl->compile('block-olt');
$tpl->clear();
$tpl->load_template('olt/main.tpl');
$tpl->set('{listdevice}',$lang['alldevice']);
$tpl->set('{back_page}',($dataSwitch['device']=='olt'?'pon':'switch'));
$tpl->set('{model}',$dataSwitch['place']);
$tpl->set('{result}',$tplRes);
$tpl->set('{block-content}',$tpl->result['block-olt']);
$tpl->set('{droplist}','');
$tpl->set('{pagerbottom}','');
$tpl->compile('content');
$tpl->clear();
?>