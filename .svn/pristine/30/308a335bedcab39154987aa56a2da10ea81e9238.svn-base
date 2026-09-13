<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = [
	'title'=>'profile',
	'description'=>'profile',
	'page'=>'profile'
];
$select_form = '';
$result = '';
$pager = '
<div class="mainadmin">
<div id="onu-speedbar">
    <a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
	<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Налаштування</span>
    </div>
</div>
<div id="calendar">
    <div class="calendar-menu">
        '.$select_form.'
    </div>
    <div class="calendar-right">
        '.$result.'
    </div>
</div>';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}',$pager);
$tpl->compile('content');
$tpl->clear();
?>