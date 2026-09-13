<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function connect_line_pillar($pillarid) {
	global $db, $config;
	$data = '';
	$sql_line = $db->SimpleWhile("SELECT * FROM `oblenergo_connect_pillar` where pillar1 = '".$pillarid."'");
	if(isset($sql_line) && count($sql_line)>0) {
		foreach ($sql_line as $idid => $pillar) {
			$data_pillar = $db->Fast('oblenergo_pillar','id,nomer_pillar',['id'=>$pillar['pillar2']]);
			$data .='<img class="link_pillar" src="../style/img/connect_pillar.png">'.$data_pillar['nomer_pillar'].'';
			$data .='<a href="/?do=oblenergo&act=delline&id='.$pillar['id'].'" class="del_line"><img src="../style/img/close.png"></a>';
		}
	}
	return $data;
}
function get_cout_pillar_tp($tpid) {
	global $db, $config;
	$data = $db->Simple("SELECT count(id) as count FROM oblenergo_pillar where tpid = '{$tpid}' ");
	return $data;
}
function get_cout_pillar_tp_symis($tpid) {
	global $db, $config;
	$data = $db->Simple("SELECT COUNT(id) AS count FROM oblenergo_pillar WHERE tpid = '{$tpid}' AND count_concurrent > 1 AND count_concurrent IS NOT NULL");
	return $data;
}
function getTPMap($oblenergoid){
	global $db;
	$mapper = '';
	$where = '';
	if(!empty($oblenergoid['id'])){
		$where = "WHERE oblenergoid = '".$oblenergoid['id']."'";
	}
	$oblenergo_pillar = $db->SimpleWhile("SELECT * FROM oblenergo_tp {$where}");
	if(isset($oblenergo_pillar) && count($oblenergo_pillar)>0){
		foreach($oblenergo_pillar as $data){
			if(!empty($data['lan']) && !empty($data['lon'])){
				$iconHtml = '<div class="oblenergo_tp"><img src="../style/img/oblenergo_tp.png"></div>';
				$tooltip = $data['locationname']."<br>".$data['oblenergoname']."<br>TP ".$data['name_tp']." ".$data['nomer_tp'];
				$mapper .= "L.marker([".$data['lan'].",".$data['lon']."],{icon: L.divIcon({className: 'mapper', html: ".json_encode($iconHtml)."})})";
				$mapper .= ".bindTooltip(".json_encode($tooltip).")";
				$mapper .= ".addTo(map);";
			}
		}
	}
	return $mapper;
}
function getPillarMap($data_oblenergo_tp,$pillar){
	global $db;
	$markers_tp = '';
	$oblenergo_pillar = $db->SimpleWhile("SELECT * FROM oblenergo_pillar where tpid = '{$data_oblenergo_tp['id']}' ");
	if(isset($oblenergo_pillar) && count($oblenergo_pillar)>0){
		foreach($oblenergo_pillar as $ho){
			if(!empty($ho['lan']) && !empty($ho['lon'])){
				$iconFile = (isset($ho['id']) && isset($pillar['id']) && $ho['id'] == $pillar['id']) ? 'map_los' : 'prov_0';
				$iconHtml = '<div class="pillaricon"><img src="../style/img/'.$iconFile.'.png"></div>';
				$tooltip = $data_oblenergo_tp['locationname']."<br>".$data_oblenergo_tp['oblenergoname']."<br>TP ".$data_oblenergo_tp['name_tp']." ".$data_oblenergo_tp['nomer_tp']."<br>Pillar: ".$ho['nomer_pillar']."<br>";
				$markers_tp .= "L.marker([".$ho['lan'].",".$ho['lon']."],{icon: L.divIcon({className: 'mapper', html: ".json_encode($iconHtml)."})})";
				$markers_tp .= ".bindTooltip(".json_encode($tooltip).")";
				$markers_tp .= ".addTo(map);";
			}
		}
	}
	return $markers_tp;
}
function countProvider($tpid){
	global $db;
	$data = [];
	$sqlprovider = $db->SimpleWhile("SELECT providerid FROM oblenergo_subprovider where tpid = ".$tpid);
	if(isset($sqlprovider) && count($sqlprovider)>0) {
		foreach ($sqlprovider as $oblid => $concurent) {
			$data[$concurent['providerid']] = array(
				'providerid' => $concurent['providerid']
			);
		}
	}
	return $data;
}
function listProvider(){
	global $db;
	$data = [];
	$sql_obl_con = $db->SimpleWhile("SELECT * from oblenergo_concurent");
	if(isset($sql_obl_con) && count($sql_obl_con)>0) {
		foreach($sql_obl_con as $oblid => $concurent) {
			$data[$concurent['id']] = array(
				'id' => $concurent['id'],
				'name' => $concurent['name'],
				'locationid' => $concurent['locationid']
			);
		}
	}
	return $data;
}
function type_pillar($type){
	$res = [
		1 => "<span class=\"obl_04\">0.4 kV</span>",
		2 => "10 kV",
		3 => "30 kV",
	];
	return $res[$type] ?? null;
}
?>
