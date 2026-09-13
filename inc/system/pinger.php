<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (isset($confPMon['PINGER']) && !empty($confPMon['PINGER']) && $confPMon['PINGER'] == 1) {
	$metatags = array('title'=>$lang['page_title_stats'],'description'=>$lang['page_title_descr'],'page'=>'pinger');
	$sqlpinger = $db->SimpleWhile('SELECT * FROM switch');
	if(is_array($sqlpinger) && count($sqlpinger)>0){
		$tplresult .= '<div class="admin-1"><div class="main-panel grid8">';
		foreach($sqlpinger as $dev){
			$tplresult .= '<a href="/?do=pon"><img src="../style/device/'.$dev['img'].'"><span>'.$dev['place'].'</span></a>';
		}
		$tplresult .= '</div></div>';
	}
	$result ='<div style="margin: 0;"><div class="page-error">'.$tplresult.'</div>';
	$tpl->load_template('main/main.tpl');
	$tpl->set('{block-main}',''.$result.'');
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');	
}
?>