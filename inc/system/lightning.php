<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = ['title'=>'Блискавки онлайн','description'=>'Онлайн-мапа блискавок','page'=>'lightning'];
$page_html = <<<HTML
<div style="margin:0;">
<iframe src="https://map.blitzortung.org" 
        style="width:100%;height:90vh;border:0;border-radius:10px"
        loading="lazy" referrerpolicy="no-referrer"></iframe></div>
HTML;
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', '<div class="mainadmin">'.$page_html.'</div>');
$tpl->compile('content');
$tpl->clear();
