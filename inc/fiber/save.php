<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
$sqlinsert['tree'] = isset($_POST['tree']) ? Clean::int($_POST['tree']): null;
		$sqlinsert['types'] = isset($_POST['types']) ? Clean::int($_POST['types']): null;
		$sqlinsert['lan'] = isset($_POST['lan']) ? Clean::text($_POST['lan']): null;
		$sqlinsert['lon'] = isset($_POST['lon']) ? Clean::text($_POST['lon']): null;
		$sqlinsert['description'] = isset($_POST['description']) ? Clean::text($_POST['description']): null;
		$sqlinsert['unit_id'] = isset($_POST['unit']) ? Clean::int($_POST['unit']): null;
		$sqlinsert['name'] = isset($_POST['name']) ? Clean::text($_POST['name']): null;
		if(!empty($sqlinsert['types']) && !empty($sqlinsert['name']) && !empty($sqlinsert['unit_id'])){			
			$db_unit = ['sql'=>'SELECT * FROM ponunit WHERE id = '.$sqlinsert['unit_id'],'key' => 'unit_'.$sqlinsert['unit_id'],'time' => 600];
			$stmt = $pdo->prepare('SELECT * FROM ponunit WHERE id = :id');
			$stmt->execute([':id' => (int)$sqlinsert['unit_id']]);
			$sql_unit = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
			$sqlinsert['mereja'] = $sql_unit['mereja'] ?? 'pon';
			$sqlinsert['myicon'] = null;
			$sqlinsert['location'] = $sql_unit['location'];
			$db->SQLinsert('ponelement',$sqlinsert);
			$go->go('/?do=fiber&act=viewtree&id='.$sqlinsert['tree']);
		}
		$go->redirect('fiber');	
?>