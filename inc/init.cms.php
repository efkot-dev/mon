<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(isset($system_navigation) && $system_navigation != false){
	$tpl->load_template('empty_html.tpl');
}else{
	require ENGINE_DIR.'menu.php';
	$tpl->load_template('html.tpl');
}
$vikno_flash = '';
$tpl->set('{html}',$html);
$tpl->set('{vikno_flash}',$vikno_flash);
$tpl->set('{ajax}',$ajax);
$tpl->set('{head}',(isset($htmlhead)?$htmlhead:''));
$tpl->set('{var}',$var);
$tpl->set('{pmon_index}',$pmon_index);
if(isset($system_navigation) && $system_navigation){
	$tpl->set('{menu}','');	
	$tpl->set('{css}',$css);
}else{
	$tpl->set('{menu}',$m_menu);
	$tpl->set('{css}',$css);
}
$tpl->set('{tpl}','/style/');
$tpl->set('{folder}',$tpl->folder);
$tpl->set('{content}',$tpl->result['content']);
$tpl->set('{debug}',($config['debugmysql']=='yes' && !empty($queryList['list'])?$queryList['list']:''));
$tpl->set('{block-right}',(!isset($tpl->result['block-right']) ? '' : $tpl->result['block-right']));
$tpl->compile('main');
echo $tpl->result['main'];
$tpl->global_clear();
if(isset($confPMon['MYSQLDEBUG']) && $confPMon['MYSQLDEBUG']==1 && is_array($mysql_debug = $db->debugSql())){
	echo'<div style="background: #000;color: #fff;font-size: 12px;padding: 10px;">';
	foreach($mysql_debug as $mysql_query){
		echo $mysql_query['time'].'->'.$mysql_query['query'].'<br>';
	}
	echo'</div>';
}
?>
