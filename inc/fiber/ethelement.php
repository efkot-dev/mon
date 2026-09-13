<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
$sqlinsert['types'] = isset($_POST['types']) ? Clean::int($_POST['types']): null;
		$sqlinsert['unit_id'] = isset($_POST['unit']) ? Clean::int($_POST['unit']): null;
		$sqlinsert['lan'] = isset($_POST['lan']) ? Clean::text($_POST['lan']): null;
		$sqlinsert['lon'] = isset($_POST['lon']) ? Clean::text($_POST['lon']): null;
		$sqlinsert['name'] = isset($_POST['name']) ? Clean::text($_POST['name']): null;
		if(!empty($sqlinsert['types']) && !empty($sqlinsert['name']) && !empty($sqlinsert['unit_id'])){
			$ponunit = $db->Fast('ponunit','location',['id'=>$sqlinsert['unit_id']]);
			$sqlinsert['location'] = $ponunit['location'];
			$db->SQLinsert('ethelement',$sqlinsert);
		}
		exit;
?>