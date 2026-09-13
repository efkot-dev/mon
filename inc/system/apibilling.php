<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('setup')) {
    $go->redirect('main');
}
$metatags = array('title'=>$lang['pt_apibilling'],'description'=>$lang['pd_apibilling'],'page'=>'apibilling');
$selbill = '<select class="select_billing" name="billingtype">';
$selbill .= '<option value="mikbill" '.($config['billingtype']=='mikbill'?'selected="selected"':''). '>MikBill</option>';
$selbill .= '<option value="mikrobill" '.($config['billingtype']=='mikrobill'?'selected="selected"':''). '>MikroBill</option>';
$selbill .= '<option value="abills" '.($config['billingtype']=='abills'?'selected="selected"':''). '>ABillS</option>';
$selbill .= '<option value="userside" '.($config['billingtype']=='userside'?'selected="selected"':''). '>UserSide</option>';
$selbill .= '<option value="nodeny" '.($config['billingtype']=='nodeny'?'selected="selected"':''). '>NoDeny Plus</option>';
$selbill .= '<option value="ubilling" '.($config['billingtype']=='ubilling'?'selected="selected"':''). '>Ubilling</option>';
$selbill .= '</select>';
$selstbill = '<select class="select_billing" name="billing">';
$selstbill .= '<option value="on" '.($config['billing']=='on'?'selected="selected"':''). '>'.($config['billing']=='on'?$lang['ons']:$lang['on']).'</option>';
$selstbill .= '<option value="off" '.($config['billing']=='off'?'selected="selected"':''). '>'.($config['billing']=='off'?$lang['offs']:$lang['off']).'</option>';
$selstbill .= '</select>';
$tpl->load_template('billing/page.tpl');
$tpl->set('{selbill}',$selbill);
$tpl->set('{selstbill}',$selstbill);
$tpl->set('{billingapikey}',$config['billingapikey']);
$tpl->set('{billingurl}',$config['billingurl']);
$tpl->set('{countuid}','');
$tpl->compile('content');
$tpl->clear();
?>