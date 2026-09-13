<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
require_once ENGINE_DIR.'functions/editor.php';
$elementid = $id;
$name_pon = '';
$get = isset($_POST['get']) ? Clean::text($_POST['get']): 'connection';
$js = "";

switch($get) {
    case 'connection':
		$get_ponelement = $db->Simple("SELECT * FROM ponelement where id = '{$elementid}' LIMIT 1");
		$getpon = $db->Simple("SELECT * FROM ponmap where id = '{$elementid}' LIMIT 1");
		$get_tree = $db->Simple("SELECT * FROM pontree where id = '{$get_ponelement['tree']}' LIMIT 1");
		$get_unit = $db->Simple("SELECT * FROM ponunit where id = '{$get_ponelement['unit_id']}' LIMIT 1");
$js .= "
const temp_vok = get_editor_pmon({$elementid},'vok');
generateOptica(temp_vok);

const temp_sp = get_editor_pmon({$elementid},'splitter');
generateSplitters(temp_sp);

const temp_pl = get_editor_pmon({$elementid},'planar');
generatePlanars(temp_pl);

const temp_onu = get_editor_pmon({$elementid},'onu');
generateOnus(temp_onu);

const temp_cross = get_editor_pmon({$elementid},'cross');
generatecross(temp_cross);

const temp_switch = get_editor_pmon({$elementid},'switch');
generateSwitches(temp_switch);

const temp_conn = get_editor_pmon({$elementid},'connect');	
searchElement(temp_conn);

";
$name_pon .= '<div class="pon_name_editor"><a href="?do=fiber&act=viewunit&id='.$get_unit['id'].'"><i class="fi fi-rr-globe"></i>'.$get_unit['name'].'</a></div>';
$name_pon .= '<div class="pon_name_editor"><a href="/?do=fiber&act=viewtree&id='.$get_ponelement['tree'].'"><i class="fi fi-rr-chart-tree"></i>'.$get_tree['name'].'</a></div>';
$name_pon .= '<div class="pon_name_editor"><a href="/?do=fiber&act=details&id='.$get_ponelement['tree'].'"><i class="fi fi-rr-info"></i>'.$lang['btn_olt_allinfo'].' '.$get_ponelement['name'].'</a></div>';
$name_pon .= '<div class="pon_name_editor"><i class="fi fi-rr-angle-right"></i>'.$get_ponelement['name'].'</div>';
        break;
		case 'added':
			
        break;
    default:

        break;
}

$metatags = array('title'=> $lang['volsmeraja'].' '.$get_ponelement['name'],'description'=>'Editor','page'=>'pmonvols');

$get = md5(time());
$resutltpl .= <<<HTML
<link href="../style/editorpmon/style.css?f={$get}" type="text/css" rel="stylesheet">
<div id="datas" data-element="{$elementid}"></div>
	<div id="pon-grid">
		<button id="show-menu" onclick="toggleMenu()"><i class="fi fi-rr-menu-burger"></i></button>
		{$name_pon}
    </div>	
    <div id="pon-menu" class="menu-pon hidden">
        <p><a href="/"><i class="fi fi-rr-bank"></i>{$lang['main']}</a></p>
        <p><a href="/?do=fiber&act=unit"><i class="fi fi-rr-chart-network"></i>{$lang['volsmeraja']}</a></p>
        <p><a href="#" onclick="insert_ponmap('planar')"><i class="fi fi-rr-plus-small"></i>{$lang['addeds']} Planar</a></p>
        <p><a href="#" onclick="insert_ponmap('splitter')"><i class="fi fi-rr-plus-small"></i>{$lang['addeds']} Spliter</a></p>
        <p><a href="#" onclick="insert_ponmap('switch')"><i class="fi fi-rr-plus-small"></i>{$lang['addeds']} Switch</a></p>
        <p><a href="#" onclick="insert_ponmap('cross')"><i class="fi fi-rr-plus-small"></i>{$lang['addeds']} Cross</a></p>
        <p><a href="#" onclick="insert_ponmap('onu')"><i class="fi fi-rr-plus-small"></i>{$lang['addeds']} ONU</a></p>
        <p><a href="/?do=fiber&act=edit&id={$elementid}" ><i class="fi fi-rr-edit"></i>{$lang['btn_menu_conf']}</a></p>
    </div>
<div id="vok-editor">
<svg id="ekran-svg"style="display:block;width:100%;height:100vh;">
    <g id="connection-group">
		<svg id="connection-svg">
            <rect width="100%" height="100%" fill="#e7edf3" />
        </svg>
    </g>
</svg>
<div class="popupPonMenu"><div class="popupContent"><a href="#" class="pmonmenuclose"><img src="../style/img/close.png"></a><div id="ponmemueditor"></div></div></div>
</div>
<script src="../style/editorpmon/manual.js?f={$get}"></script>

<script>
{$js}
</script>
HTML;
