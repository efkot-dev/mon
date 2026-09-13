<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
$lan = isset($_POST['lan']) ? (float)$_POST['lan']  : null;
		$lon = isset($_POST['lon']) ? (float)$_POST['lon']  : null;
		$unit_id = isset($_POST['unit']) ? (int)$_POST['unit']   : 0;
		if ($unit_id > 0 && $lan !== null) {
			del_cache_simple_sql('unit_' . $unit_id);
			$stmt = $pdo->prepare('UPDATE ponunit SET lan = :lan, lon = :lon WHERE id = :id');
			$stmt->execute([':lan' => $lan,':lon' => $lon,':id'  => $unit_id]);
			$go->go('/?do=fiber&act=viewunit&id=' . $unit_id);
		} else {
			$go->go('/?do=fiber&act=unit');
		}
?>