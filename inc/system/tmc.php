<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (isset($confPMon['SKLAD']) && !empty($confPMon['SKLAD']) && $confPMon['SKLAD'] == 1 && $access->get('sklad')) {
$clock_pmon = date('Y-m-d H:i:s');
define('SKLAD',true);
define('MODULE_SKLAD', ROOT_DIR . '/inc/sklad/');
$url_skald = "/?do=tmc";
$id = isset($_GET['id'])?Clean::int($_GET['id']):null;
$types = isset($_GET['types'])?Clean::text($_GET['types']):null;
$speedbar = '';
$speedbar_block = '';
$content = '';
$content_head = '';
$auto = true;
switch($act){
	case 'import':
		require MODULE_SKLAD.'import.php';
		break;		
	case 'log':
		require MODULE_SKLAD.'log.php';
		break;		
	case 'menu':
		require MODULE_SKLAD.'menu.php';
		break;		
	case 'savescaner':
		require MODULE_SKLAD.'savescaner.php';
		break;		
	case 'temp':
		require MODULE_SKLAD.'temp.php';
		break;		
	case 'editsubcategory':
		require MODULE_SKLAD.'editsubcategory.php';
		break;		
	case 'panel':
		require MODULE_SKLAD.'panel.php';
		break;		
	case 'category':
		require MODULE_SKLAD.'category.php';
		break;		
	case 'jobs':
		require MODULE_SKLAD.'jobs.php';
		break;		
	case 'scaner':
		require MODULE_SKLAD.'scaner.php';
		break;		
	case 'addsubcat':
		require MODULE_SKLAD.'addsubcat.php';
		break;	
	case 'notconfirmed':
		require MODULE_SKLAD.'notconfirmed.php';
		break;		
	case 'confirmed':
		require MODULE_SKLAD.'confirmed.php';
		break;		
	case 'savesubcat':
		require MODULE_SKLAD.'savesubcat.php';
		break;		
	case 'addcategory':
		require MODULE_SKLAD.'addcategory.php';
		break;		
	case 'savecatalog':
		require MODULE_SKLAD.'savecatalog.php';
		break;		
	case 'editcategory':
		require MODULE_SKLAD.'editcategory.php';
		break;	
	case 'updatecategory':
		require MODULE_SKLAD.'updatecategory.php';
		break;		
	case 'updatesubcategory':
		require MODULE_SKLAD.'updatesubcategory.php';
		break;		
	case 'delsubcategory':
		require MODULE_SKLAD.'delsubcategory.php';
		break;	
	case 'delcategory':
		require MODULE_SKLAD.'delcategory.php';
		break;		
	case 'stats':
		require MODULE_SKLAD.'stats.php';
		break;		
	case 'spisannya':
		require MODULE_SKLAD.'spisannya.php';
		break;		
	case 'save':
		require MODULE_SKLAD.'save.php';
		break;		
	case 'load_categories':
        require MODULE_SKLAD.'load_categories.php';
        break;	
	case 'transfer':
        require MODULE_SKLAD.'transfer.php';
        break;	
	case 'get_tovar':
        require MODULE_SKLAD.'get_tovar.php';
        break;	
	case 'get_inventory':
        require MODULE_SKLAD.'get_inventory.php';
        break;	
	case 'get_sn':
        require MODULE_SKLAD.'get_sn.php';
        break;	
	case 'savetransfer':
        require MODULE_SKLAD.'savetransfer.php';
        break;
    case 'load_products':
        require MODULE_SKLAD.'load_products.php';
        break;    
	case 'load_today':
        require MODULE_SKLAD.'load_today.php';
        break;	
	case 'return':
        require MODULE_SKLAD.'return.php';
        break;
    case 'get':
		require MODULE_SKLAD.'get.php';
        break;	
	case 'add':		
		require MODULE_SKLAD.'add.php';
		break;		
	case 'saveimport':		
		require MODULE_SKLAD.'saveimport.php';
		break;		
	case 'backpack':		
		require MODULE_SKLAD.'backpack.php';
		break;		
	case 'barcode':		
		require MODULE_SKLAD.'barcode.php';
		break;		
	case 'list':		
		require MODULE_SKLAD.'list.php';
		break;		
	case 'repair':		
		require MODULE_SKLAD.'repair.php';
		break;		
	case 'deletecategory':		
		require MODULE_SKLAD.'deletecategory.php';
		break;		
	case 'moderation':		
		require MODULE_SKLAD.'moderation.php';
		break;	
	case 'listusr':		
		require MODULE_SKLAD.'listusr.php';
		break;		
	case 'services':		
		require MODULE_SKLAD.'services.php';		
		break;		
	default:	
		$metatags = array('title'=>'Склад, обладнання','description'=>'Склад, обладнання','page'=>'tmc');
		$speedbar .='<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Склад, обладнання</span>';
		$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
		$content .= '<div class="admin-1"><div class="main-panel"><div class="admin-zvit">';
		$content .= '<a href="/?do=tmc&act=get">
			<img src="../style/img/main_tmc_sklad.png"><span>Матеріали обладання</span></a>';
		if ($access->get('manager_sklad')) {
		$content .= '<a href="/?do=tmc&act=add">
			<img src="../style/img/main_tmc_pruxid.png"><span>Додати товар</span></a>';
		}
		$content .= '<a href="/?do=tmc&act=listusr">
			<img src="../style/img/taskman_archiv.png"><span>Список працівників</span></a>';
		if ($access->get('manager_sklad')) {
		$content .= '<a href="/?do=tmc&act=transfer">
			<img src="../style/img/main_tmc_zdav.png"><span>Переміщення</span></a>';
		}
		$count_not_conf = '';
		$sql_not_conf = $pdo->prepare("SELECT COUNT(id) FROM sklad_akt WHERE moderation = 'no'");
		$sql_not_conf->execute();
		$count = $sql_not_conf->fetchColumn();
		if(isset($count) && $count>0){
			$count_not_conf = '<span class="greens">'.$count.'</span>';
		}
		$content .= '<a href="/?do=tmc&act=notconfirmed">
			<img src="../style/img/catalog.png"><span>Очікуються '.$count_not_conf.'</span></a>';
		$content .= '<a href="/?do=tmc&act=confirmed">
			<img src="../style/img/zvit.png"><span>Архів актів</span></a>';
		if ($access->get('manager_sklad')) {
		$content .= '<a href="/?do=tmc&act=import">
			<img src="../style/img/import_exel.png"><span>Імпорт з Exel</span></a>';
		$content .= '<a href="/?do=tmc&act=scaner">
			<img src="../style/img/code_scaner.png"><span>Сканування</span></a>';
		}
		$content .= '<a href="/?do=tmc&act=category">
			<img src="../style/img/main_tmc_vidatu.png"><span>Список категорій</span></a>';		
		$content .= '<a href="/?do=tmc&act=scaner">
			<img src="../style/img/print_code.png"><span>Друкувати код</span></a>';	
		if ($access->get('manager_sklad')) {			
		$content .= '<a href="/?do=tmc&act=listusr">
			<img src="../style/img/bad_device_sklad.png"><span>Повернення</span></a>';		
			$content .= '<a href="/?do=tmc&act=log">
			<img src="../style/img/zvit1.png"><span>Журнал складу</span></a>';
		}			
		$count_repair = '';
		$sql_not_conf = $pdo->prepare("SELECT COUNT(id) FROM sklad_tovar_ser");
		$sql_not_conf->execute();
		$count_r = $sql_not_conf->fetchColumn();
		if(isset($count_r) && $count_r>0){
			$count_repair = '<span class="loses">'.$count_r.'</span>';
		}			
		$content .= '<a href="/?do=tmc&act=repair">
			<img src="../style/img/services_sklad.png"><span>Обладнання на сервісі '.$count_repair.'</span></a>';		
		$content .= '<a href="/?do=tmc&act=services">
			<img src="../style/img/services_sklad.png"><span>Сервісний центр</span></a>';		
		$content .= '<a href="/?do=tmc&act=jobs">
			<img src="../style/img/setup_pmon.png"><span>Типи робіт</span></a>';		
		$content .= '</div></div></div>';
}
$tpl->load_template('tmc.tpl');
$tpl->set('{speedbar}',$speedbar_block);
$tpl->set('{content_head}',$content_head);
$tpl->set('{content}',(isset($auto) && $auto == true ? '<div class="content_auto">'.$content.'</div>':'<div class="content">'.$content.'</div>'));
$tpl->compile('content');
$tpl->clear();
}else{
	$go->go('/?do=main');
}
?>
