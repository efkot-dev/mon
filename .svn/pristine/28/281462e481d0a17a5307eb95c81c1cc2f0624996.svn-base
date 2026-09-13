<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
$speedbar = '';
$templates = '';
switch($act){
    case 'save':
        $name = isset($_POST['name']) ? Clean::text($_POST['name']) : '';
        $nodes_ = $_POST['nodes'];
        $edges_ = $_POST['edges'];
		if (!$nodes_ || !$edges_) {
			die("Невірний формат даних для полів nodes або edges.");
		}
		if (!isvalidtext($nodes_) || !isvalidtext($edges_)) {
			$db->query("INSERT INTO poncalculator (name, nodes, edges) VALUES ('$name', '$nodes_', '$edges_')");
		}
		die();
		break;    
    case 'update':                
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
		if(isset($id) && $id>0){
			$pc = $db->Fast('poncalculator','*',['id'=>$id]);
			if(isset($pc['id']) && !empty($pc['id']) ){
				$name = isset($_POST['name']) ? Clean::text($_POST['name']) : '';
				$nodes_ = isset($_POST['nodes']) ? $_POST['nodes'] : '';
				$edges_ = isset($_POST['edges']) ? $_POST['edges'] : '';
				if (empty($name) || empty($nodes_) || empty($edges_)) {
					die("Невірні дані для оновлення.");
				}
				if (!isvalidtext($nodes_) || !isvalidtext($edges_)) {
					$db->query("UPDATE poncalculator SET name = '{$name}', nodes = '{$nodes_}', edges = '{$edges_}' WHERE id = '{$id}'");
				}
			}
		}
		die();
	break;    
    case 'delet':        
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){
			$pc = $db->Fast('poncalculator','*',['id'=>$id]);
			if(isset($pc['id']) && !empty($pc['id']) ){
				$db->query("DELETE FROM poncalculator WHERE id = '{$pc['id']}'");
			}
		}		
		$go->go('/?do=poncalc');	
		break;    
    case 'new':		
		$nodesJson = '[]';
		$edgesJson = '[]';
		$metatags = [
			'title' => 'Пон калькулятор', 'description' => 'Пон калькулятор', 'page' => 'poncalc'
		];
		$speedbar .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			<a class="brmhref" href="/?do=poncalc"><i class="fi fi-rr-angle-left"></i>PON planning</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Нова калькуляція</span>
		</div>
		';
		$templates .= "
		<script>
			var savedNodes = {$nodesJson};
			var savedEdges = {$edgesJson};
		</script>";
		$templates .= pon_calc_js().'		
		<div class="card-body">
		<div class="buttons">
			<img src="/style/img/pon_olt.svg" title="OLT" alt="olt" id="add_olt">
			<img src="/style/img/sp.svg" title="Дільник" alt="splitter" id="add_splitter">
			<img src="/style/img/18.svg" title="Відгалужувач" alt="divider" id="add_divider">
			<img src="/style/img/fm.svg" id="save_scheme" title="Save" >
			<input name="name" id="pon_name" type="text" value="PON planning" autocapitalize="off">
		</div>
		<div class="info-table table-sm"><table class="table"></table></div>
		<div id="cy"></div>
		</div>';
		break; 
    case 'updategr':			
	
		break; 
    case 'savegroups':		
		$location = isset($_POST['location']) ? Clean::int($_POST['location']) : 0;
		$group = isset($_POST['group']) ? Clean::int($_POST['group']) : 0;
		$name = isset($_POST['name']) ? Clean::text($_POST['name']) : '';
		if(isset($name)){
			$db->query("INSERT INTO poncalculator_gr (name, locationid, groupsid) VALUES ('$name', '$location', '$group')");
		}	
		$go->go('/?do=poncalc');
		die();		
		break; 
    case 'groups':
		$metatags = [
			'title' => 'Пон калькулятор', 'description' => 'Пон калькулятор', 'page' => 'groups'
		];
		$speedbar .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			<a class="brmhref" href="/?do=poncalc"><i class="fi fi-rr-angle-left"></i>PON planning</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Нова група</span>
		</div>
		';	
		$templates .= '<div class="card" style="margin: 0;">
			<form action="/?do=poncalc" method="post" id="formadd">
			<input name="act" type="hidden" value="savegroups">';
		$listgroup = '';
		$listlocation = '';
		$location = getListLocations();
		if(is_array($location)){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
			}
		}
		$group = $db->SimpleWhile("SELECT * FROM groups");
		if(is_array($group)){
			foreach($group as $gr){
				$listgroup .= '<option value="'.$gr['id'].'">'.$gr['name'].'</option>';
			}
		}
		$templates .= formpage(['img'=>'addconnect.png','name'=>$lang['location'],'descr'=>'Привязка до локації','pole'=>'<select class="select" mame="location" id="location"><option value="0"></option>'.$listlocation.'</select>']);
		$templates .= formpage(['img'=>'addconnect.png','name'=>$lang['groups'],'descr'=>'Для загального групування','pole'=>'<select class="select" name="group" id="group"><option value="0"></option>'.$listgroup.'</select>']);
		$templates .= formpage(['img'=>'addconnect.png','name'=>'Назва','descr'=>'Короткий опис групи','pole'=>'<input style="width:99%;" name="name" class="input1" type="text">']);
		$templates .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['addeds'].'</button></form></div>';
		break; 
    case 'getgoups':		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		if(isset($id) && $id>0){
			echo'<form action="/?do=poncalc" method="post" id="formadd">
			<input name="id" type="hidden" value="'.$id.'">
			<input name="act" type="hidden" value="updatevlan">';
			echo form(['name'=>'Vlan','descr'=>'1-4096','pole'=>'<input autocomplete="off" required name="vlan" class="input1" type="text" value="'.$getvlan['vlan'].'">']);
			echo form(['name'=>$lang['opis'],'descr'=>$lang['addbattery_8'],'pole'=>'<input autocomplete="off" required name="name" class="input1" type="text" value="'.$getvlan['name'].'">']);
			echo form(['name'=>$lang['descronus'],'descr'=>'','pole'=>$count_vlan['count_vlan']]);
			echo'</form>
			<div class="polebtn block-flex">		
				<button type="button" class="closePopup color-b">'.$lang['close'].'</button>
				<button type="submit" form="formadd" value="submit">'.$lang['update'].'</button>
			</div>';
		}
		die;
		break; 
    case 'open':
		$id = intval($_GET['id']) ?? null;
		$ho = $db->Simple("SELECT * FROM poncalculator WHERE id = $id LIMIT 1"); 
		if ($ho) {
			$nodesJson = $ho['nodes'];
			$edgesJson = $ho['edges'];
		} else {
			$go->go('/?do=poncalc');
		}
		$metatags = [
			'title' => 'Пон калькулятор', 'description' => 'Пон калькулятор', 'page' => 'poncalc'
		];
		$speedbar .= '
		<div id="onu-speedbar">
			<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>
			<a class="brmhref" href="/?do=poncalc"><i class="fi fi-rr-angle-left"></i>PON planning</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Перегляд схеми</span>
		</div>
		';
		$templates .= "
		<script>
			var savedNodes = {$nodesJson};
			var savedEdges = {$edgesJson};
		</script>". pon_calc_js().'
		<div class="card-body">
		<div class="buttons">
			<img src="/style/img/pon_olt.svg" title="OLT" alt="olt" id="add_olt">
			<img src="/style/img/sp.svg" title="Дільник" alt="splitter" id="add_splitter">
			<img src="/style/img/18.svg" title="Відгалужувач" alt="divider" id="add_divider">
			<img src="/style/img/fm.svg" id="update_scheme" title="Update save" >
			<a href="/?do=poncalc&act=delet&id='.$id.'"><img src="/style/img/delete-trash.svg" title="Delete" ></a>
			<input name="name" id="pon_name" type="text" value="'.$ho['name'].'" autocapitalize="off">
			<input name="id" id="pon_id" type="hidden" value="'.$id.'">
		</div>
		<div class="info-table table-sm"><table class="table"></table></div>
		<div id="cy"></div>
		</div>';
		break;    
    default:
		if(isset($_GET['id'])){
			$id = intval($_GET['id']) ?? null;
		}
        $where = '';
		if(isset($id) && $id>0){
			$where = "WHERE groupid = '{$id}'";
		}
		$metatags = array(
			'title'=>'Optical network PON planning',
			'description'=>'Optical network PON planning',
			'page'=>'poncalc');
		$speedbar ='<a class="brmhref" href="/?do=main"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Optical network PON planning</span>';
		$menu_list = '';
		$menu_list .= '<a class="menu-sub" href="/?do=poncalc&act=new"><img src="../style/img/add.png">Новий розрахунок</a>';
		$menu_list .= '<a class="menu-sub" href="/?do=poncalc&act=groups"><img src="../style/img/edit.png">Додати групу</a>';
		$group = $db->SimpleWhile("SELECT * FROM poncalculator_gr");
		if(is_array($group)){
			foreach($group as $gr){
				$menu_list .= '<a class="menu-sub" href="/?do=poncalc&id='.$gr['id'].'">'.$gr['name'].'</a>';
			}			
		}
		$templates .= '<div class="container">
		<div class="left-column">
			<div class="menu_olt_left">
				'.$menu_list.'
			</div>
		</div>
		<div class="right-column">';
		$templates .='<div id="backup_panel" class="popupContainer">
				<div class="popupContent">
					<div id="result_ajax"></div>
				</div>
			</div>';		
		$templates .= '<div class="pontree"><table class="resp-tab"><thead><tr>
			<th width="3%">#</th>
			<th>'.$lang['name'].'</th>
			<th width="10%">PON</th>
			<th width="15%">'.$lang['group'].'</th>
			<th width="15%">'.$lang['added'].'</th></tr></thead><tbody>';
		$feeding = $db->SimpleWhile("SELECT * from poncalculator {$where} ORDER BY created_at DESC");
		if(count($feeding)>0){
			foreach($feeding as $jiv){
				$name_groups = '';
				if($jiv['groupid']){
					$gr = $db->Simple("SELECT id,name from poncalculator_gr where id = '{$jiv['groupid']}'");
					$name_groups = '<a href="/?do=poncalc&id='.$gr['id'].'">'.$gr['name'].'</a>';
				}
				preg_match_all('/"id"\s*:\s*"/', $jiv['nodes'], $matches);
			$templates .= '<tr>
				<td class="manager_href"><span class="db openPopup" data-popup-id="backup_panel" onclick="get_groups(\''.$jiv['id'].'\');"><img src="../style/img/edit.png"></span></td>
				<td class="name_pon "><a href="/?do=poncalc&act=open&id='.$jiv['id'].'">
				'.($jiv['name']?$jiv['name']:'N/A').'
				</a></td>
				<td>'.count($matches[0]).'</td>
				<td>'.$name_groups.'</td>
				<td>'.$jiv['created_at'].'</td>
			</tr>';
			}
		}else{
			$templates .= '<tr><td colspan="2">'.$lang['empty'].'</td></tr>';
		}
		$templates .= '</table></div></div></div>';
}
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin"><div id="onu-speedbar">'.$speedbar.'</div>'.$templates.'</div>');
$tpl->compile('content');
$tpl->clear();
?>
