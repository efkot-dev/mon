<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('setup')){
    $go->redirect('main');
}
$metatags = array('title'=>$lang['setup'],'description'=>$lang['setup'],'page'=>'config');
$setup ='';
$select_langate ='';

$select_langate = '<select name="lang" id="lang">';
$langs = $db->SimpleWhile("SELECT DISTINCT lang FROM translations ORDER BY lang");
foreach ($langs as $language) {
	$selected = (isset($config['lang']) && $config['lang'] == $language['lang']) ? ' selected' : '';
	$select_langate .= '<option value="'.$language['lang'].'"'.$selected.'>'.$language['lang'].'</option>';
}		
$select_langate .= '</select>';
$setup .= formpage(['img'=>'font.png','name'=>'Language','descr'=>$lang['lang_descr'],'pole'=>$select_langate]);
$setup .= formpage(['img'=>'addconnect.png','name'=>$lang['cfg_root'],'descr'=>$lang['cfg_root_descr'],'pole'=>'<input style="width:200px;" name="root" class="input1" type="text" value="'.$config['root'].'">']);
$setup .= formpage(['img'=>'www.png','name'=>'URL','descr'=>$lang['cfg_url_descr'],'pole'=>'<input style="width:200px;" name="url" class="input1" type="text" value="'.$config['url'].'">']);
$setup .= formpage(['img'=>'number-20.png','name'=>$lang['cfg_count'],'descr'=>$lang['cfg_count_descr'],'pole'=>'<input style="width:200px;" name="countviewpageonu" class="input1" type="text" value="'.$config['countviewpageonu'].'">']);
$setup .= formpage(['img'=>'number-20.png','name'=>$lang['cfg_count_switch'],'descr'=>$lang['cfg_count_switch_descr'],'pole'=>'<input style="width:200px;" name="countviewpageswitch" class="input1" type="text" value="'.$config['countviewpageswitch'].'">']);
$setup .= formpage(['img'=>'number-20.png','name'=>$lang['cfg_loadport'].' EPON','descr'=>$lang['cfg_loadport_descr'],'pole'=>'<input style="width:50px;display:inline-block;" name="criticonu64" class="input1" type="text" value="'.(!empty($config['criticonu64'])?$config['criticonu64']:2).'"> шт']);
$setup .= formpage(['img'=>'number-20.png','name'=>$lang['cfg_loadport'].' GPON','descr'=>$lang['cfg_loadport_descr'],'pole'=>'<input style="width:50px;display:inline-block;" name="criticonu128" class="input1" type="text" value="'.(!empty($config['criticonu128'])?$config['criticonu128']:10).'"> шт']);
$setup .= formpage(['img'=>'font.png','name'=>'Style','descr'=>$lang['cfg_style_descr'],'pole'=>'<input style="width:200px;" name="skin" class="input1" type="text" value="'.$config['skin'].'">']);
$setup .= formpage(['img'=>'api.png','name'=>'API','descr'=>$lang['cfg_api_descr'],'pole'=>'<input style="width:200px;" name="monitorapi" class="input1" type="text" value="'.$config['monitorapi'].'">']);
$setup .= formpage(['img'=>'map.png','name'=>$lang['cfg_geo_lan'],'descr'=>$lang['cfg_geo_lan_descr'],'pole'=>'<input style="width:400px;" name="geo_lan" class="input1" type="text" value="'.$config['geo_lan'].'">']);
$setup .= formpage(['img'=>'map.png','name'=>$lang['cfg_geo_lon'],'descr'=>$lang['cfg_geo_lan_descr'],'pole'=>'<input style="width:400px;" name="geo_lon" class="input1" type="text" value="'.$config['geo_lon'].'">']);
$setup .= formpage(['img'=>'addconnect.png','name'=>$lang['cfg_comm_onu'],'descr'=>$lang['cfg_comm_onu_decr'],'pole'=>'<select class="select" name="comment"><option value="on" '.($config['comment']=='on'?'selected="selected"':'').'>'.($config['comment']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['comment']=='off'?'selected="selected"':'').'>'.($config['comment']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'eth_na.png','name'=>$lang['cfg_view_ip'],'descr'=>$lang['cfg_view_ip_descr'],'pole'=>'<select class="select" name="viewipswitch"><option value="on" '.($config['viewipswitch']=='on'?'selected="selected"':'').'>'.($config['viewipswitch']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['viewipswitch']=='off'?'selected="selected"':'').'>'.($config['viewipswitch']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'error.png','name'=>$lang['cfg_err_name'],'descr'=>$lang['cfg_err_descr'],'pole'=>'<select class="select" name="errorport"><option value="on" '.($config['errorport']=='on'?'selected="selected"':'').'>'.($config['errorport']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['errorport']=='off'?'selected="selected"':'').'>'.($config['errorport']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'log-format.png','name'=>$lang['cfg_statusport_name'],'descr'=>$lang['cfg_statusport_descr'],'pole'=>'<select class="select" name="statusport"><option value="on" '.($config['statusport']=='on'?'selected="selected"':'').'>'.($config['statusport']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['statusport']=='off'?'selected="selected"':'').'>'.($config['statusport']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'m11.png','name'=>'Tag','descr'=>$lang['marker_cfg'],'pole'=>'<select class="select" name="tag"><option value="on" '.($config['tag']=='on'?'selected="selected"':'').'>'.($config['tag']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['tag']=='off'?'selected="selected"':'').'>'.($config['tag']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'technology.png','name'=>'Pon','descr'=>$lang['cfg_pon_descr'],'pole'=>'<select class="select" name="pon"><option value="on" '.($config['pon']=='on'?'selected="selected"':'').'>'.($config['pon']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['pon']=='off'?'selected="selected"':'').'>'.($config['pon']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'technology.png','name'=>$lang['cfg_rx'],'descr'=>$lang['cfg_pon_onugraph'],'pole'=>'<select class="select" name="onugraph"><option value="on" '.($config['onugraph']=='on'?'selected="selected"':'').'>'.($config['onugraph']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['onugraph']=='off'?'selected="selected"':'').'>'.($config['onugraph']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'open-box.png','name'=>$lang['cfg_tmc_sklad'],'descr'=>$lang['cfg_tmc_sklad_descr'],'pole'=>'<select class="select" name="sklad"><option value="on" '.($config['sklad']=='on'?'selected="selected"':'').'>'.($config['sklad']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['sklad']=='off'?'selected="selected"':'').'>'.($config['sklad']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'switch.png','name'=>$lang['cfg_port'],'descr'=>$lang['cfg_port_descr'],'pole'=>'<select class="select" name="configport"><option value="on" '.($config['configport']=='on'?'selected="selected"':'').'>'.($config['configport']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['configport']=='off'?'selected="selected"':'').'>'.($config['configport']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'database.png','name'=>$lang['cfg_unit'],'descr'=>$lang['cfg_unit_descr'],'pole'=>'<select class="select" name="unit"><option value="on" '.($config['unit']=='on'?'selected="selected"':'').'>'.($config['unit']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['unit']=='off'?'selected="selected"':'').'>'.($config['unit']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'telegram.png','name'=>'Telegram','descr'=>$lang['cfg_telegran_sms'],'pole'=>'<select class="select" name="telegram">
<option value="on" '.($config['telegram']=='on'?'selected="selected"':'').'>'.($config['telegram']=='on'?$lang['ons']:$lang['on']).'</option>
<option value="off" '.($config['telegram']=='off'?'selected="selected"':'').'>'.($config['telegram']=='off'?$lang['offs']:$lang['off']).'</option>
</select>']);
if(isset($config['telegram']) && $config['telegram']=='on'){
$setup .= formpage(['img'=>'telegramtoken.png','name'=>'Token Telegram','descr'=>$lang['cfg_telegran_token'],'pole'=>'<input style="width:200px;" name="telegramtoken" class="input1" type="text" value="'.$config['telegramtoken'].'">']);	
}
/*
$setup .= formpage(['img'=>'telegramchat.png','name'=>'ChatID Telegram','descr'=>$lang['cfg_telegran_chat'],'pole'=>'<input style="width:200px;" name="telegramchatid" class="input1" type="text" value="'.$config['telegramchatid'].'">']);
*/
$setup .= formpage(['img'=>'tag.png','name'=>'Marker','descr'=>$lang['marker_cfg'],'pole'=>'<select class="select" name="marker"><option value="on" '.($config['marker']=='on'?'selected="selected"':'').'>'.($config['marker']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['marker']=='off'?'selected="selected"':'').'>'.($config['marker']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage(['img'=>'m6.png','name'=>$lang['map'],'descr'=>$lang['map_cfg'],'pole'=>'<select class="select" name="map"><option value="on" '.($config['map']=='on'?'selected="selected"':'').'>'.($config['map']=='on'?$lang['ons']:$lang['on']).'</option><option value="off" '.($config['map']=='off'?'selected="selected"':'').'>'.($config['map']=='off'?$lang['offs']:$lang['off']).'</option></select>']);
$setup .= formpage([
	'img'=>'m6.png',
	'name'=>$lang['map'],
	'descr'=>$lang['map_cfg'],
	'pole'=>'
<select class="select" name="typemap">
	<option value="vision" '.($config['typemap']=='vision'?'selected="selected"':'').'>Vision API</option>
	<option value="google" '.($config['typemap']=='google'?'selected="selected"':'').'>Google Map</option>
	<option value="openstreetmap" '.($config['typemap']=='openstreetmap'?'selected="selected"':'').'>OpenStreetMap</option>
	<option value="openstreetmap_fr" '.($config['typemap']=='openstreetmap_fr'?'selected="selected"':'').'>OSM France Fast</option>
	<option value="carto_light" '.($config['typemap']=='carto_light'?'selected="selected"':'').'>Carto Light</option>
	<option value="stamen_terrain" '.($config['typemap']=='stamen_terrain'?'selected="selected"':'').'>Stamen Terrain</option>
	<option value="esri_world" '.($config['typemap']=='esri_world'?'selected="selected"':'').'>ESRI World Imagery</option>
</select>'
]);
$setup .= formpage(['img'=>'laser_2.png','name'=>$lang['cfg_bad_rx'],'descr'=>$lang['cfg_bad_rx_descr'],'pole'=>$lang['vid'].' -<input style="width:50px;display:inline-block;" name="badsignalstart" class="input1" type="text" value="'.(!empty($config['badsignalstart'])?$config['badsignalstart']:28).'"> '.$lang['do'].' -<input style="width:50px;display:inline-block;" name="badsignalend" class="input1" type="text" value="'.(!empty($config['badsignalend']) ? $config['badsignalend'] : 40).'">']);
$setup .= formpage(['img'=>'laser_3.png','name'=>$lang['cfg_bad_rx_log'],'descr'=>$lang['cfg_bad_rx_log_descr'],'pole'=>'<input style="width:50px;display:inline-block;" name="criticsignal" class="input1" type="text" value="'.(!empty($config['criticsignal'])?$config['criticsignal']:2).'"> dBm']);
$setup .= formpage(['img'=>'laser_4.png','name'=>$lang['cfglogsignal'],'descr'=>$lang['cfglogsignaldescr'],'pole'=>'<select class="select" name="logsignal"><option value="on" '.($config['logsignal']=='on'?'selected="selected"':'').'>'.($config['logsignal']=='on'?$lang['rxlog3']:$lang['rxlog3']).'</option><option value="off" '.($config['logsignal']=='off'?'selected="selected"':'').'>'.($config['logsignal']=='off'?$lang['rxlog1']:$lang['rxlog2']).'</option></select>']);
$tpl->load_template('setup/config.tpl');
$tpl->set('{result}','<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="saveconfig">'.$setup.'<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form>');
$tpl->compile('content');
$tpl->clear();
?>