<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
$sqlinsert['tree'] = isset($_POST['gettree']) ? Clean::int($_POST['gettree']): null;
		$sqlinsert['types'] = isset($_POST['gettypes']) ? Clean::int($_POST['gettypes']): null;
		$sqlinsert['lan'] = isset($_POST['lan']) ? Clean::text($_POST['lan']): null;
		$sqlinsert['lon'] = isset($_POST['lon']) ? Clean::text($_POST['lon']): null;
		$sqlinsert['name'] = isset($_POST['name']) ? Clean::text($_POST['name']): null;
		$sqlinsert['unit_id'] = isset($_POST['unit']) ? Clean::int($_POST['unit']): null;
		if(!empty($sqlinsert['unit_id']) && !empty($sqlinsert['types']) && !empty($sqlinsert['name']) && !empty($sqlinsert['tree'])){
			$ponunit = $db->Fast('ponunit','location',['id'=>$sqlinsert['unit_id']]);
			$sqlinsert['location'] = $ponunit['location'];
			$db->SQLinsert('ponelement',$sqlinsert);
		}
		exit;
?>