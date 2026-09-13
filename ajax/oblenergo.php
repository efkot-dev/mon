<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$types = isset($_POST['types']) ? Clean::text($_POST['types']): null;
$name = isset($_POST['name']) ? Clean::text($_POST['name']): null;
switch($act){
	case 'listtp':		
		$oblenergo = $db->SimpleWhile("SELECT * from oblenergo_tp where oblenergoid = ".$id);
		if(isset($oblenergo) && count($oblenergo)>0){
			echo'<div class="list-tp-checkbox">';
			foreach($oblenergo as $obl){
				echo'<span>'.$pmonimg['svg']['pillar'].''.$obl['nomer_tp'].'<input class="checkcss" name="tp[]" value="'.$obl['id'].'" type="checkbox"></span>';
			}
			
			echo'</div>';			
			echo'<span class="getpoisk" onclick="showtpmap('.$id.')">Показати</span>';
			echo '<label><input type="checkbox" id="selectAll"> Вибрати всі</label>';
		}else{
			echo 'not_support';
		}
		?>
		<script>
		document.getElementById('selectAll').addEventListener('change', function() {
			var checkboxes = document.querySelectorAll('.checkcss');
			for (var i = 0; i < checkboxes.length; i++) {
				checkboxes[i].checked = this.checked;
			}
		});
		</script>
		<?php
	break;		
	case 'connect':	
	$tpid = isset($_POST['tpid']) ? Clean::int($_POST['tpid']): null;
	$pillarid = isset($_POST['pillarid']) ? Clean::int($_POST['pillarid']): null;
	if(isset($tpid) && $tpid>0 && isset($pillarid) && $pillarid>0){
		$oblenergopillar = $db->SimpleWhile("SELECT * from oblenergo_pillar where tpid = '{$tpid}'");
		if (isset($oblenergopillar) && count($oblenergopillar) > 0) {
			$list_pillar = '';
			echo'<form action="/?do=oblenergo" method="post"><input name="act" type="hidden" value="connect">';
			echo'<div class="form_conn_pillar">';
			echo'<input name="pillarid" type="hidden" value="'.$pillarid.'">';
			echo'<input name="tpid" type="hidden" value="'.$tpid.'">';
			echo'<h2>зєднати з</h2>';
			foreach ($oblenergopillar as $pillar) {
				if($pillar['id']!=$pillarid){
					$list_pillar .='<option value="'.$pillar['id'].'">Опора № '.$pillar['nomer_pillar'].'</option>';
				}
			}
			echo'<div class="conn_pillar"><select class="select" name="connect_pillar" id="connect_pillar">'.$list_pillar.'</select>';
			echo'<input class="connect" type="submit" value="Зєднати"></div>';
			echo'</div>';
			echo'</form>';
		}
	}
	break;		
	case 'markertp':
		$datatp = [];	
		if(isset($_POST['selecttp']) && count($_POST['selecttp'])>0){
			for($j = 0; $j <= count($_POST['selecttp']); $j++) {
				if(isset($_POST['selecttp'][$j])){
					$datatp[] = (int)$_POST['selecttp'][$j];
				}
			}
		}
		if (isset($datatp) && count($datatp) > 0) {
			$sql_where_tp_line = "tpid IN (" . implode(",", array_map('intval', $datatp)) . ")";
			$sql_where = "AND tpid IN (" . implode(",", array_map('intval', $datatp)) . ")";
			$sql_where_tp = "AND id IN (" . implode(",", array_map('intval', $datatp)) . ")";
			$oblenergotp = $db->SimpleWhile("SELECT * from oblenergo_tp where oblenergoid = '{$id}' " . $sql_where_tp);
			$markers = [];
			$tmp_tp = [];
			if (isset($oblenergotp) && count($oblenergotp) > 0) {
				foreach ($oblenergotp as $tp) {
					if (!empty($tp['lan']) && !empty($tp['lon'])) {
						$markers['pillar'][] = [
							'lan' => $tp['lan'],
							'lon' => $tp['lon'],
							'classicon' => 'oblenergo_tp',
							'mappericon' => "oblenergo_tp.png",
							'nomer_pillar' => 'TP'
						];
						$tmp_tp[$tp['id']]['n'] = $tp['nomer_tp'];
						$tmp_tp[$tp['id']]['l'] = $tp['locationname'];
						$tmp_tp[$tp['id']]['o'] = $tp['oblenergoname'];
					}
				}
			}
			$masiv_geo = [];
			$oblenergopillar = $db->SimpleWhile("SELECT * from oblenergo_pillar where oblenergoid = '{$id}' " . $sql_where);
			if (isset($oblenergopillar) && count($oblenergopillar) > 0) {
				foreach ($oblenergopillar as $pillar) {
					if (!empty($pillar['lan']) && !empty($pillar['lon'])) {
						$masiv_geo[$pillar['id']] = array('lan' => $pillar['lan'],'lon' => $pillar['lon']);
						$markers['pillar'][] = [
							'lan' => $pillar['lan'],
							'lon' => $pillar['lon'],
							'classicon' => 'pillaricon',
							'count_concurrent' => isset($pillar['count_concurrent']) ? $pillar['count_concurrent'] : 0,
							'mappericon' => ($pillar['type_pillar']!=1?'l_':'').(isset($pillar['count_concurrent']) && $pillar['count_concurrent']>1?'prov_1':'prov_0').".png",
							'nomer_pillar' => "{$tmp_tp[$pillar['tpid']]['n']}<br>{$tmp_tp[$pillar['tpid']]['l']}<br>{$tmp_tp[$pillar['tpid']]['o']}<br><b>{$pillar['nomer_pillar']}</b>"
						];
					}
				}
			}			
			$oblenergo_connect_pillar = $db->SimpleWhile("SELECT * from oblenergo_connect_pillar where " . $sql_where_tp_line);
			if (isset($oblenergo_connect_pillar) && count($oblenergo_connect_pillar) > 0) {
				foreach ($oblenergo_connect_pillar as $line) {
					if(isset($masiv_geo[$line['pillar1']]) && isset($masiv_geo[$line['pillar2']])){
						$markers['line'][] = [
							'name'=> 'line',
							'id'=> $line['id'],
							'start_1'=> $masiv_geo[$line['pillar1']]['lan'],
							'end_1'=> $masiv_geo[$line['pillar1']]['lon'],
							'start_2'=> $masiv_geo[$line['pillar2']]['lan'],
							'end_2'=> $masiv_geo[$line['pillar2']]['lon']
						];
					}
				}				
			}
			echo json_encode($markers);
		}
	break;		
}
?>
