<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$oblenergo_clock = date('Y-m-d H:i:s');
$tplresult = '';
$speedbar = '';
$sqlinsert = [];
function oblEnsurePillarPhotoTable($pdo) {
	try {
		$chk = $pdo->query("SHOW TABLES LIKE 'oblenergo_pillar_photo'")->fetchColumn();
		if ($chk) return;
		$pdo->exec("
			CREATE TABLE IF NOT EXISTS oblenergo_pillar_photo (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				pillarid INT UNSIGNED NOT NULL,
				photo VARCHAR(255) NOT NULL,
				note TEXT NULL,
				added DATETIME NOT NULL,
				user_id INT UNSIGNED NULL,
				PRIMARY KEY (id),
				KEY idx_pillarid (pillarid)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
		");
	} catch (Throwable $e) {
		// Optional feature: ignore if DB permissions are limited.
	}
}
switch($act){
	case 'selectcase':
		$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;
		$metatags = ['title'=>'Select OBLENERGO','description'=>'Select OBLENERGO','page'=>'oblenergo_select'];
		$speedbar .= '<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>OBLENERGO</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Select OBLENERGO, TP and pillars</span>';
		$tplresult .= '
		<div class="block_flex">
		<div class="block_white pre40">
			<form id="oblForm" onsubmit="return false;">
				<label>Search OBLENERGO:</label><br>
				<input type="text" id="searchObl" autocomplete="off" placeholder="Enter name..." style="width:40%;">
				<div id="oblenergoList" class="select-list"></div>
				<label>TP:</label>
				<select id="tpList" class="select" style="width:40%;" disabled>
					<option value="">--</option>
				</select>
				<label>Pillars:</label><br>
				<select id="pillarList" class="select" style="width:40%;" disabled>
					<option value="">--</option>
				</select>
				<br>
				<button type="button" id="confirmBtn" class="btn_new" disabled>Confirm</button>
			</form>
		</div>
			<div class="pre60">1111</div>
		</div>
		<script>
		document.getElementById("searchObl").addEventListener("input", function() {
			const val = this.value.trim();
			const list = document.getElementById("oblenergoList");
			list.innerHTML = "";
			if (val.length < 2) return;
			fetch("/?do=oblenergo&act=search_oblenergo&q=" + encodeURIComponent(val))
				.then(r => r.json())
				.then(data => {
					list.innerHTML = data.map(o => `<div class="obl-item" data-id="${o.id}">${o.name}</div>`).join("");
				});
		});
		document.addEventListener("click", function(e) {
			if (e.target.classList.contains("obl-item")) {
				const id = e.target.dataset.id;
				const name = e.target.textContent;
				document.getElementById("searchObl").value = name;
				document.getElementById("oblenergoList").innerHTML = "";
				const tpSel = document.getElementById("tpList");
				tpSel.innerHTML = "<option value=\'\'>Loading...</option>";
				tpSel.disabled = false;
				fetch("/?do=oblenergo&act=get_tp&obl=" + id)
					.then(r => r.json())
					.then(data => {
						tpSel.innerHTML = data.map(t => `<option value="${t.id}">${t.subname} ${t.nomer_tp}</option>`).join("");
					});
			}
		});
		document.getElementById("tpList").addEventListener("change", function() {
			const val = this.value;
			const pillarSel = document.getElementById("pillarList");
			pillarSel.innerHTML = "<option value=\'\'>Loading...</option>";
			pillarSel.disabled = true;
			if (!val) return;
			fetch("/?do=oblenergo&act=get_pillars&tpid=" + val)
				.then(r => r.json())
				.then(data => {
					pillarSel.innerHTML = data.map(p => `<option value="${p.id}">Pillar ${p.nomer_pillar}</option>`).join("");
					pillarSel.disabled = false;
					document.getElementById("confirmBtn").disabled = false;
				});
		});
		</script>
		<style>
			.select-list {max-height:180px; overflow-y:auto; width:70%; margin-top:5px;}
			.obl-item {padding:5px; cursor:pointer;}
			.obl-item:hover {background:#f0f0f0;}
		</style>
		';
	break;
	case 'search_oblenergo':
        $q = trim($_GET['q'] ?? '');
        $st = $pdo->prepare("SELECT id,name FROM oblenergo WHERE name LIKE :q ORDER BY name LIMIT 20");
        $st->execute([':q' => "%$q%"]);
        echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
		exit;
        break;
    case 'get_tp':
        $obl = (int)($_GET['obl'] ?? 0);
        $st = $pdo->prepare("SELECT id,name_tp,subname,nomer_tp FROM oblenergo_tp WHERE oblenergoid = :id ORDER BY name_tp");
        $st->execute([':id' => $obl]);
        echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
		exit;
        break;
    case 'get_pillars':
        $tpid = (int)($_GET['tpid'] ?? 0);
        $st = $pdo->prepare("SELECT id,nomer_pillar FROM oblenergo_pillar WHERE tpid = :id ORDER BY nomer_pillar");
        $st->execute([':id' => $tpid]);
        echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
		exit;
        break;
	case 'add': 
		$location = getListLocations();
		if(count($location)>0){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'" >'.$loc['name'].'</option>';
			}
		}else{
			$go->go('/?do=add&act=all&info=location');
			exit();
		}
		$metatags = array('title'=>'Додати обленерго','description'=>'Додати обленерго','page'=>'oblenergo');
		$speedbar .='<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати обленерго</span>';
		$tplresult .= '<div class="nav-fiber p10"><form action="/?do=oblenergo" method="post"><input name="act" type="hidden" value="save">';	
		$tplresult .='<label for="name">Назва районого обленерго:</label><input type="text" id="name" name="name" required="" autocomplete="off" style="width:30%;"><br>';
		$tplresult .='<label for="type">Локація:</label>';
		$tplresult .='<select class="select" name="location" id="location">'.$listlocation.'</select><br>';
		$tplresult .='<input type="submit" value="'.$lang['add'].'">';
		$tplresult .='</div></form>';
	break;
	case 'connect': 
		$sqlinsert = [];
		$pillarid = isset($_POST['pillarid']) ? Clean::int($_POST['pillarid']) : null;	
		$pillar2 = isset($_POST['connect_pillar']) ? Clean::int($_POST['connect_pillar']) : null;	
		$tpid = isset($_POST['tpid']) ? Clean::int($_POST['tpid']) : null;
		if(isset($pillarid) && isset($pillar2) && isset($tpid) && $pillarid>0 && $pillar2>0 && $tpid>0){
			$data_oblenergo_tp = $db->Fast('oblenergo_tp','*',['id'=>$tpid]);
			$sqlinsert['pillar1'] = $pillarid;
			$sqlinsert['pillar2'] = $pillar2;
			$sqlinsert['tpid'] = $tpid;
			$sqlinsert['locationid'] = $data_oblenergo_tp['locationid'];
			$sqlinsert['oblenergoid'] = $data_oblenergo_tp['oblenergoid'];
			$checker = $db->Fast('oblenergo_connect_pillar','id',$sqlinsert);
			if(empty($checker['id'])){
				$sqlinsert['added'] = $oblenergo_clock;
				$db->SQLinsert('oblenergo_connect_pillar',$sqlinsert);
			}
		}		
		$go->go('/?do=oblenergo&act=tp&id='.$tpid);
		exit();
	break;
	case 'import':
		$metatags = ['title'=>'Імпорт ТП','description'=>'Імпорт ТП','page'=>'oblenergo_import'];
		$speedbar .= '<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a>'
				   . '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Імпорт ТП</span>';

		$tplresult .= '
		<div class="nav-fiber p10">
		  <form action="/?do=oblenergo" method="post" enctype="multipart/form-data">
			<input type="hidden" name="act" value="doimport">
			<label>Виберіть текстовий файл (.txt):</label><br>
			<input type="file" name="tpfile" accept=".txt,text/plain" required><br><br>
			<button type="submit" class="btn_new">Завантажити</button>
		  </form>
		</div>';
		break;
	case 'doimport':
		if (empty($_FILES['tpfile']) || $_FILES['tpfile']['error'] !== UPLOAD_ERR_OK) {
			$go->go('/?do=oblenergo&act=import'); exit;
		}
		$tmp  = $_FILES['tpfile']['tmp_name'];
		$size = (int)($_FILES['tpfile']['size'] ?? 0);
		if ($size <= 0 || $size > 2*1024*1024) { // 2MB ліміт
			$go->go('/?do=oblenergo&act=import'); exit;
		}
		$raw = file_get_contents($tmp);
		if ($raw === false) { $go->go('/?do=oblenergo&act=import'); exit; }

		$text = preg_replace('/\s+/', ' ', $raw);
		$getBlock = function(string $key) use ($text): ?string {
			$re = sprintf('/\[%s\s*:\s*(.*?)\]/i', preg_quote($key,'/'));
			if (preg_match($re, $text, $m)) return trim($m[1]);
			return null;
		};
		$tpname = $getBlock('tpname') ?? '';
		if ($tpname === '') { $go->go('/?do=oblenergo&act=import'); exit; }
		$lineBlock = $getBlock('line') ?? '';
		$lineIds = [];
		if ($lineBlock !== '') {
			if (preg_match('/^\d+$/', $lineBlock)) {
				$lineIds = range(1, (int)$lineBlock);
			}
			elseif (preg_match('/^\d+(?:\s*,\s*\d+)+$/', $lineBlock)) {
				$lineIds = array_values(array_unique(array_filter(
					array_map('intval', explode(',', $lineBlock)),
					fn($v)=>$v>0
				)));
				sort($lineIds, SORT_NUMERIC);
			}
		}
		$lineIds = $lineIds ?: [1];
		$lineToPillars = [];
		foreach ($lineIds as $ln) {
			$blk = $getBlock('line'.$ln);
			if ($blk && preg_match('/^\d+(?:\s*,\s*\d+)*$/', $blk)) {
				$pillars = array_values(array_unique(array_filter(
					array_map('intval', explode(',', $blk)), fn($v)=>$v>0
				)));
				$lineToPillars[$ln] = $pillars;
			} else {
				$lineToPillars[$ln] = [];
			}
		}
		$subname = null; $nomer_tp = null;
		if (preg_match('/^\s*(КТП|ТП)\s*[-\s]*([A-Za-zА-Яа-я0-9._-]+)\s*$/u', $tpname, $m)) {
			$subname  = strtoupper($m[1]);
			$nomer_tp = $m[2];
		}
		$pillarProviders = []; // номер_опори => [providerId...]
		if (preg_match_all('/\[pillar_(\d+)\s*:\s*([^\]]+)\]/i', $text, $mm, PREG_SET_ORDER)) {
			foreach ($mm as $one) {
				$pnum = (int)$one[1];
				$ids = [];
				foreach (preg_split('/\s*,\s*/', trim($one[2])) as $tok) {
					if (preg_match('/^isp_(\d+)$/i', $tok, $m2)) $ids[] = (int)$m2[1];
					elseif (preg_match('/^\d+$/', $tok))         $ids[] = (int)$tok;
				}
				$ids = array_values(array_unique(array_filter($ids, fn($v)=>$v>0)));
				if ($pnum>0 && $ids) $pillarProviders[$pnum] = $ids;
			}
		}
		$metatags = ['title'=>'Перевірка імпорту','description'=>'Перевірка імпорту','page'=>'oblenergo_import_preview'];
		$speedbar .= '<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a>'
				   . '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Перевірка імпорту</span>';
		$listobl = '';
		$stmt = $pdo->query('SELECT id, name FROM oblenergo ORDER BY name');
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $obl) {
			$listobl .= '<option value="'.(int)$obl['id'].'">'.$obl['name'].'</option>';
		}
		$listloc = '';
		$stmt = $pdo->query('SELECT id, name FROM location ORDER BY name');
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $loc) {
			$listloc .= '<option value="'.(int)$loc['id'].'">'.$loc['name'].'</option>';
		}
		$rowsHtml = '';
		foreach ($lineIds as $ln) {
			$nums = $lineToPillars[$ln];
			$rowsHtml .= '<tr><td>Лінія '.$ln.'</td><td>'.count($nums).'</td><td>'.($nums ? implode(', ', $nums) : '<i>—</i>').'</td></tr>';
		}
		$provPreview = '';
		if ($pillarProviders) {
			$allProvIds = array_values(array_unique(array_merge(...array_map(fn($a)=>$a, $pillarProviders))));
			$names = [];
			if ($allProvIds) {
				$ph = implode(',', array_fill(0, count($allProvIds), '?'));
				$st = $pdo->prepare("SELECT id,name FROM oblenergo_concurent WHERE id IN ($ph)");
				$st->execute($allProvIds);
				while ($r = $st->fetch(PDO::FETCH_ASSOC)) $names[(int)$r['id']] = $r['name'];
			}
			$provRows = '';
			foreach ($pillarProviders as $pnum => $ids) {
				$labels = array_map(fn($i)=>($names[$i] ?? ('#'.$i)), $ids);
				$provRows .= '<tr><td>Опора '.$pnum.'</td><td>'.implode(', ', array_map('htmlspecialchars',$labels)).'</td></tr>';
			}
			$provPreview = '<br><h4>Прив’язка провайдерів</h4>
				<table class="resp-tab"><thead><tr><th>Опора</th><th>Провайдери</th></tr></thead><tbody>'.$provRows.'</tbody></table>';
		}
		$payload = ['tpname' => $tpname,'subname' => $subname,'nomer' => $nomer_tp,'lines' => $lineIds,'pillars' => $lineToPillars,'pillar_providers' => $pillarProviders];
		$payloadJson = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), ENT_QUOTES);
		$tplresult .= '
		<div>
		  <h3>Попередній перегляд</h3>
		  <p><b>Назва ТП:</b> '.htmlspecialchars($tpname,ENT_QUOTES).'</p>
		  <p><b>Абревіатура:</b> '.htmlspecialchars($subname ?? '—',ENT_QUOTES).' &nbsp; <b>Номер:</b> '.htmlspecialchars($nomer_tp ?? '—',ENT_QUOTES).'</p>
		  <table class="resp-tab"><thead><tr><th>Лінія</th><th>К-сть опор</th><th>Номери опор</th></tr></thead><tbody>'.$rowsHtml.'</tbody></table>
		  '.$provPreview.'
		  <br>
		  <form action="/?do=oblenergo" method="post">
			<input type="hidden" name="act" value="saveimport">
			<input type="hidden" name="data" value="'.$payloadJson.'">
			<label>Обленерго:</label>
			<select class="select" name="oblenergo" required><option value="">—</option>'.$listobl.'</select>
			&nbsp;&nbsp;
			<label>Локація:</label>
			<select class="select" name="location" required><option value="">—</option>'.$listloc.'</select>
			<br><br>
			<button type="submit" class="btn_new">Занести в базу</button>
		  </form>
		</div>';
	break;
	case 'saveimport':
		$json = $_POST['data'] ?? '';
		$oblenergoId = isset($_POST['oblenergo']) ? (int)$_POST['oblenergo'] : 0;
		$locationId  = isset($_POST['location'])  ? (int)$_POST['location']  : 0;
		if ($json === '' || $oblenergoId <= 0 || $locationId <= 0) {
			$go->go('/?do=oblenergo&act=import'); exit;
		}
		$data = json_decode($json, true);
		if (!is_array($data) || empty($data['tpname']) || empty($data['lines']) || empty($data['pillars'])) {
			$go->go('/?do=oblenergo&act=import'); exit;
		}
		$tpname = (string)$data['tpname'];
		$subname = $data['subname'] ?? null;
		$nomer = $data['nomer'] ?? null;
		$lines = array_map('intval', (array)$data['lines']);
		$pillars = (array)$data['pillars'];
		$pillarProviders = isset($data['pillar_providers']) && is_array($data['pillar_providers']) ? $data['pillar_providers'] : [];
		$oblRow = $pdo->prepare('SELECT name FROM oblenergo WHERE id = :id');
		$oblRow->execute([':id'=>$oblenergoId]);
		$oblName = (string)$oblRow->fetchColumn();
		$locRow = $pdo->prepare('SELECT name FROM location WHERE id = :id');
		$locRow->execute([':id'=>$locationId]);
		$locName = (string)$locRow->fetchColumn();
		$pdo->beginTransaction();
		// 1) ТП
		$insTp = $pdo->prepare("
			INSERT INTO oblenergo_tp
			  (nomer_tp, name_tp, subname, oblenergoid, oblenergoname, locationid, locationname, countliniy, added)
			VALUES
			  (:nomer, :name_tp, :subname, :oblid, :oblname, :locid, :locname, :cnt, :added)
		");
		$insTp->execute([':nomer' => $nomer,':name_tp' => $tpname,':subname' => $subname,':oblid' => $oblenergoId,':oblname' => $oblName,':locid' => $locationId,':locname' => $locName,':cnt' => count($lines),':added' => date('Y-m-d H:i:s')]);
		$tpid = (int)$pdo->lastInsertId();
		$insLine = $pdo->prepare('INSERT INTO oblenergo_countliniy (nomer_liniy, locationid, tpid, oblenergoid) VALUES (:n, :locid, :tpid, :oblid)');
		foreach ($lines as $n) {
			$insLine->execute([':n' => (int)$n,':locid' => $locationId,':tpid' => $tpid,':oblid' => $oblenergoId]);
		}
		$insPillar = $pdo->prepare('INSERT INTO oblenergo_pillar (nomer_pillar, tpid, oblenergoid, type_pillar, count_concurrent, added) VALUES (:nomer_pillar, :tpid, :oblid, :type_pillar, :cc, :added)');
		$insLink = $pdo->prepare('INSERT INTO oblenergo_pillarliniy (tpid, locationid, oblenergoid, lineid, pillarid) VALUES (:tpid, :locid, :oblid, :lineid, :pillarid)');
		$pillarIdByNumber = [];
		foreach ($lines as $lineN) {
			$nums = isset($pillars[$lineN]) ? array_map('intval', (array)$pillars[$lineN]) : [];
			foreach ($nums as $nomer_pillar) {
				if ($nomer_pillar <= 0) continue;
				$insPillar->execute([
					':nomer_pillar' => (string)$nomer_pillar,
					':tpid' => $tpid,
					':oblid' => $oblenergoId,
					':type_pillar' => 1,
					':cc' => 0,
					':added' => date('Y-m-d H:i:s')
				]);
				$pillarId = (int)$pdo->lastInsertId();
				$pillarIdByNumber[$nomer_pillar] = $pillarId;
				$insLink->execute([
					':tpid' => $tpid,
					':locid' => $locationId,
					':oblid' => $oblenergoId,
					':lineid' => (int)$lineN,
					':pillarid' => $pillarId
				]);
			}
		}
		$validProv = [];
		if ($pillarProviders) {
			$allProvIds = array_values(array_unique(array_merge(...array_map(fn($a)=>(array)$a, $pillarProviders))));
			if ($allProvIds) {
				$ph = implode(',', array_fill(0, count($allProvIds), '?'));
				$st = $pdo->prepare("SELECT id FROM oblenergo_concurent WHERE id IN ($ph)");
				$st->execute($allProvIds);
				while ($r = $st->fetch(PDO::FETCH_ASSOC)) $validProv[(int)$r['id']] = true;
			}
		}
		if ($pillarProviders) {
			$insProv = $pdo->prepare('
				INSERT INTO oblenergo_subprovider (providerid, pillarid, locationid, tpid, oblenergoid)
				VALUES (:pid, :pillarid, :locid, :tpid, :oblid)
			');
			$updCC = $pdo->prepare('UPDATE oblenergo_pillar SET count_concurrent = :cc WHERE id = :pid');
			foreach ($pillarProviders as $pillarNumber => $provIds) {
				$pillarNumber = (int)$pillarNumber;
				if (empty($pillarIdByNumber[$pillarNumber])) continue; // такої опори не створено
				$pillarId = $pillarIdByNumber[$pillarNumber];

				$provIds = array_values(array_unique(array_filter(
					array_map('intval', (array)$provIds),
					fn($v)=>$v>0 && isset($validProv[$v])
				)));
				if (!$provIds) continue;

				foreach ($provIds as $pid) {
					$insProv->execute([
						':pid' => $pid,
						':pillarid' => $pillarId,
						':locid' => $locationId,
						':tpid' => $tpid,
						':oblid' => $oblenergoId,
					]);
				}
				$updCC->execute([':cc'=>count($provIds), ':pid'=>$pillarId]);
			}
		}

		$pdo->commit();
		$go->go('/?do=oblenergo&act=tp&id='.$tpid);
		exit;
	break;

	case 'saveimport_':
		$json = $_POST['data'] ?? '';
		$oblenergoId = isset($_POST['oblenergo']) ? (int)$_POST['oblenergo'] : 0;
		$locationId  = isset($_POST['location'])  ? (int)$_POST['location']  : 0;
		if ($json === '' || $oblenergoId <= 0 || $locationId <= 0) {
			$go->go('/?do=oblenergo&act=import'); exit;
		}
		$data = json_decode($json, true);
		if (!is_array($data) || empty($data['tpname']) || empty($data['lines']) || empty($data['pillars'])) {
			$go->go('/?do=oblenergo&act=import'); exit;
		}
		$tpname = (string)$data['tpname'];
		$subname = $data['subname'] ?? null;
		$nomer = $data['nomer'] ?? null;
		$lines = array_map('intval', (array)$data['lines']);
		$pillars = (array)$data['pillars'];
		$oblRow = $pdo->prepare('SELECT name FROM oblenergo WHERE id = :id');
		$oblRow->execute([':id'=>$oblenergoId]);
		$oblName = (string)$oblRow->fetchColumn();
		$locRow = $pdo->prepare('SELECT name FROM location WHERE id = :id');
		$locRow->execute([':id'=>$locationId]);
		$locName = (string)$locRow->fetchColumn();
		$insTp = $pdo->prepare("
			INSERT INTO oblenergo_tp
			  (nomer_tp, name_tp, subname, oblenergoid, oblenergoname, locationid, locationname, countliniy, added)
			VALUES
			  (:nomer, :name_tp, :subname, :oblid, :oblname, :locid, :locname, :cnt, :added)
		");
		$insTp->execute([':nomer' => $nomer,':name_tp' => $tpname,':subname' => $subname,':oblid' => $oblenergoId,':oblname' => $oblName,':locid' => $locationId,':locname' => $locName,':cnt'=> count($lines),':added' => date('Y-m-d H:i:s')]);
		$tpid = (int)$pdo->lastInsertId();
		$insLine = $pdo->prepare('INSERT INTO oblenergo_countliniy (nomer_liniy, locationid, tpid, oblenergoid)	VALUES (:n, :locid, :tpid, :oblid)');
		foreach ($lines as $n) {
			$insLine->execute([':n' => (int)$n,':locid' => $locationId,':tpid' => $tpid,':oblid' => $oblenergoId]);
		}
		$insPillar = $pdo->prepare('INSERT INTO oblenergo_pillar (nomer_pillar, tpid, oblenergoid, type_pillar, count_concurrent, added) VALUES (:nomer_pillar, :tpid, :oblid, :type_pillar, :cc, :added)');
		$insLink = $pdo->prepare('INSERT INTO oblenergo_pillarliniy (tpid, locationid, oblenergoid, lineid, pillarid) VALUES (:tpid, :locid, :oblid, :lineid, :pillarid) ');
		foreach ($lines as $lineN) {
			$nums = isset($pillars[$lineN]) ? array_map('intval', (array)$pillars[$lineN]) : [];
			foreach ($nums as $nomer_pillar) {
				if ($nomer_pillar <= 0) continue;
				$insPillar->execute([':nomer_pillar' => (string)$nomer_pillar,':tpid' => $tpid,':oblid' => $oblenergoId,':type_pillar'  => 1,':cc' => 1,':added' => date('Y-m-d H:i:s')]);
				$pillarId = (int)$pdo->lastInsertId();
				$insLink->execute([':tpid' => $tpid,':locid' => $locationId,':oblid' => $oblenergoId,':lineid'  => (int)$lineN,':pillarid'=> $pillarId]);
			}
		}
		$go->go('/?do=oblenergo&act=tp&id='.$tpid);
		exit;
	break;
	case 'saveconcurent': 		
		if(isset($_POST['name'])){
			$sqlinsert['name'] = Clean::text($_POST['name']);
			$sqlinsert['added'] = $oblenergo_clock;
			if(isset($_POST['location'])){
				$sqlinsert['locationid'] = Clean::int($_POST['location']);
			}
			$db->SQLinsert('oblenergo_concurent',$sqlinsert);
			$go->go('/?do=oblenergo&act=listconcurent');
			exit();
		}
		$go->go('/?do=oblenergo');
	break;
	case 'deletprovider': 		
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){
			$data_provider = $db->Fast('oblenergo_concurent','*',['id'=>$id]);
			if(isset($data_provider['id']) && !empty($data_provider['id']) ){
				$db->query("DELETE FROM oblenergo_concurent WHERE id = '{$data_provider['id']}'");
			}
		}		
		$go->go('/?do=oblenergo&act=listconcurent');	
	break;
	case 'listconcurent': 		
		$metatags = array('title'=>'Додати обленерго','description'=>'Додати обленерго','page'=>'oblenergo');
		$speedbar .='<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Список провайдерів, статистика сумісного підвісу</span>';
		$tplresult .= '<table class="resp-tab"><thead><tr>
			<th width="5%">ID</th><th width="15%">Назва</th><th width="25%">К-ть опор сум. підвісу</th><th width="15%">Додано</th><th></th></tr></thead><tbody>';
			$sql_obl_con = $db->SimpleWhile("SELECT * from oblenergo_concurent");
			if(isset($sql_obl_con) && count($sql_obl_con)>0) {
				foreach ($sql_obl_con as $oblid => $concurent) {
					$tplresult .= '<tr>
						<td>isp_'.$concurent['id'].'</td>
						<td>'.$concurent['name'].'</td>
						<td>'.$concurent['locationid'].'</td>
						<td>'.$concurent['added'].'</td>
						<td><a class="panel_house rr_1" href="/?do=oblenergo&act=deletprovider&id='.$concurent['id'].'">'.$lang['delolt'].'</a></td>
					</tr>';
				}
			}else{
				$tplresult .= '<tr><td colspan="5">'.$lang['empty'].'</td></tr>';
			}
			
		$tplresult .= '</table>';
		$tplresult .= '<div class="pole"><a href="/?do=oblenergo&act=concurent" class="urlelelement">Додати провайдера</a></div>';
	break;
	case 'concurent': 	
		$metatags = array('title'=>'Додати конкурента','description'=>'Додати конкурента','page'=>'oblenergo');
		$speedbar .='<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати обленерго</span>';
		$tplresult .= '<div class="nav-fiber p10"><form action="/?do=oblenergo" method="post"><input name="act" type="hidden" value="saveconcurent">';	
		$tplresult .='<label for="name">Назва провайдера:</label><input type="text" id="name" name="name" required="" autocomplete="off" style="width:30%;"><br>';
		$tplresult .='<label for="type">Локація:</label>';
		$location = getListLocations();
		if(count($location)>0){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'" >'.$loc['name'].'</option>';
			}
		}
		$tplresult .='<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select><br>';
		$tplresult .='<input type="submit" value="'.$lang['add'].'">';
		$tplresult .='</div></form>';	
	break;
	case 'editpillar': 
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		$data_oblenergo_pillar = $db->Fast('oblenergo_pillar','*',['id'=>$id]);
		$data_oblenergo_tp = $db->Fast('oblenergo_tp','*',['id'=>$data_oblenergo_pillar['tpid']]);
		$data_oblenergo = $db->Fast('oblenergo','*',['id'=>$data_oblenergo_tp['oblenergoid']]);
		$data_location = $db->Fast('location','*',['id'=>$data_oblenergo_tp['locationid']]);
		$line_tp = [];
		$sql_obl_line_curent = $db->SimpleWhile("SELECT * from oblenergo_pillarliniy where pillarid = ".$data_oblenergo_pillar['id']);
		if(isset($sql_obl_line_curent) && count($sql_obl_line_curent)>0) {
			foreach ($sql_obl_line_curent as $curenrlineid => $curentline) {
				$line_tp[$curentline['lineid']]['id'] = $curentline['lineid'];
			}
		}		
		$line_provider = [];
		$sql_obl_provide = $db->SimpleWhile("SELECT providerid from oblenergo_subprovider where pillarid = ".$data_oblenergo_pillar['id']);
		if(isset($sql_obl_provide) && count($sql_obl_provide)>0) {
			foreach ($sql_obl_provide as $provid => $provider) {
				$line_provider[$provider['providerid']]['id'] = $provider['providerid'];
			}
		}
		$metatags = array('title'=>'Редагувати Опору','description'=>'Редагувати Опору','page'=>'edtoblenergopillar');
		$speedbar .='
		<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a><a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-angle-left"></i>Список обленерго</a>
		<a class="brmhref" href="/?do=oblenergo&act=view&id='.$data_oblenergo['id'].'"><i class="fi fi-rr-angle-left"></i>'.$data_oblenergo['name'].'</a>
		<a class="brmhref" href="/?do=oblenergo&act=tp&id='.$data_oblenergo_tp['id'].'"><i class="fi fi-rr-angle-left"></i>Підстанція '.$data_oblenergo_tp['subname'].' '.$data_oblenergo_tp['nomer_tp'].'</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагування даних по опорі</span>';
		$tplresult .= '<div class="block_flex"><div class="class1">';		
		$tplresult .= '<div class="nav-fiber p10"><form action="/?do=oblenergo" method="post"><input name="tpid" type="hidden" value="'.$data_oblenergo_pillar['oblenergoid'].'"><input name="pillarid" type="hidden" value="'.$id.'"><input name="act" type="hidden" value="updatepillar">
		<input type="hidden" id="lan" name="lan" '.(isset($data_oblenergo_pillar['lan']) ? 'value="'.$data_oblenergo_pillar['lan'].'"':'').'>
		<input type="hidden" id="lon" name="lon" '.(isset($data_oblenergo_pillar['lon']) ? 'value="'.$data_oblenergo_pillar['lon'].'"':'').'>';	
		$tplresult .='<label for="name">Номер опори:</label><input type="text" id="nomer" name="nomer" value="'.$data_oblenergo_pillar['nomer_pillar'].'" autocomplete="off" style="width:50%;"><br>';
		$sql_obl_line = $db->SimpleWhile("SELECT * from oblenergo_countliniy where tpid = ".$data_oblenergo_pillar['tpid']);
		if(isset($sql_obl_line) && count($sql_obl_line)>0) {
			foreach ($sql_obl_line as $lineid => $line) {
				$tplresult .= '<input class="checkcss" name="line[]" value="'.$line['nomer_liniy'].'" type="checkbox"
				'.(isset($line_tp[$line['nomer_liniy']]['id']) && $line_tp[$line['nomer_liniy']]['id'] == $line['nomer_liniy']?'checked':'').'
				>Л-'.$line['nomer_liniy'].'<br>';
			}
		}
		$tplresult .='<br><label for="type">Тип опори:</label>';
		$listtype_pillar .= '<option value="1" '.($data_oblenergo_pillar['type_pillar']==1?'selected':'').'>0.4 кВ</option>
			<option value="2" '.($data_oblenergo_pillar['type_pillar']==2?'selected':'').'>10 кВ</option>
				<option value="3" '.($data_oblenergo_pillar['type_pillar']==3?'selected':'').'>35 кВ</option>';
		$tplresult .='<select class="select" name="type_pillar" id="type_pillar"><option value="0"></option>'.$listtype_pillar.'</select><br>';
		$sql_obl_con = $db->SimpleWhile("SELECT * from oblenergo_concurent");
		if(isset($sql_obl_con) && count($sql_obl_con)>0) {
			foreach ($sql_obl_con as $oblid => $concurent) {
				$tplresult .= '<input class="checkcss" name="concurent[]" value="'.$concurent['id'].'" type="checkbox"
				'.(isset($line_provider[$concurent['id']]['id']) && $line_provider[$concurent['id']]['id'] == $concurent['id']?'checked':'').'
				>'.$concurent['name'].'<br>';
			}
		}else{
			$tplresult .='<label for="name">Кількість провайдерів на опорі:</label><input type="text" id="count" name="count" required="" autocomplete="off" style="width:10%;"><br>';
		}		
		$tplresult .='<br><label for="name">Опис:</label><textarea name="descr" id="descr" class="comm">'.$data_oblenergo_pillar['descr'].'</textarea><br>';
		$tplresult .='<input type="submit" value="'.$lang['add'].'"></form></div>';		
		$tplresult .= '</div><div class="class1">';
		$gpslan = (!empty($data_oblenergo_pillar['lan'])?$data_oblenergo_pillar['lan']:(!empty($data_oblenergo_tp['lan'])?$data_oblenergo_tp['lan']:$config['geo_lan']));
		$gpslon = (!empty($data_oblenergo_pillar['lon'])?$data_oblenergo_pillar['lon']:(!empty($data_oblenergo_tp['lon'])?$data_oblenergo_tp['lon']:$config['geo_lon']));
		$zoom = '16';
		$pillar = getPillarMap($data_oblenergo_tp,$data_oblenergo_pillar);
		$mapper = getMap();
		$mapjs = <<<HTML
		<script>
		var lat = '$gpslan'; 
		var lon = '$gpslon';
		var map = L.map('divmap');
		map.setView([lat, lon], {$zoom});
		{$mapper}{$pillar}
		map.on('click', function(event) {
			var clickedLatLng = event.latlng;
			L.popup().setLatLng(clickedLatLng).setContent("Latitude: " + clickedLatLng.lat + "<br>Longitude: " + clickedLatLng.lng).openOn(map);
			document.getElementById('lan').value = clickedLatLng.lat;
			document.getElementById('lon').value = clickedLatLng.lng;
		});
		</script>
		HTML;
		$tplresult .= '
		<link rel="stylesheet" href="../style/map/leaflet.css" />
		<script src="../style/map/leaflet.js"></script>
		<script src="../style/map/mymarker.js"></script>
		<div id="divmap"></div>
		'.$mapjs;		
		// Photos (multiple per pillar)
		oblEnsurePillarPhotoTable($pdo);
		$photosHtml = '';
		try {
			$ps = $pdo->prepare('SELECT id, photo, note, added FROM oblenergo_pillar_photo WHERE pillarid = :pid ORDER BY id DESC');
			$ps->execute([':pid' => (int)$id]);
			$rows = $ps->fetchAll(PDO::FETCH_ASSOC);
			if ($rows) {
				$photosHtml .= '<div style="display:flex;gap:10px;flex-wrap:wrap;">';
				foreach ($rows as $ph) {
					$img = htmlspecialchars((string)$ph['photo'], ENT_QUOTES, 'UTF-8');
					$note = htmlspecialchars((string)($ph['note'] ?? ''), ENT_QUOTES, 'UTF-8');
					$added = htmlspecialchars((string)($ph['added'] ?? ''), ENT_QUOTES, 'UTF-8');
					$photosHtml .= '<div style="width:170px;border:1px solid #eee;border-radius:10px;padding:8px;background:#fff;">'
						.'<a href="/file/photo/'.$img.'" target="_blank"><img style="width:100%;height:120px;object-fit:cover;border-radius:8px;" src="/?do=thumb&type=photo&img='.$img.'&s=1"></a>'
						.'<div style="font-size:12px;opacity:.7;margin-top:6px;">'.$added.'</div>'
						.($note !== '' ? '<div style="font-size:12px;margin-top:4px;">'.$note.'</div>' : '')
						.'<div style="margin-top:6px;"><a class="panel_house rr_1" href="/?do=oblenergo&act=pillar_photo_del&photo_id='.(int)$ph['id'].'&pillarid='.(int)$id.'">Delete</a></div>'
						.'</div>';
				}
				$photosHtml .= '</div>';
			}
		} catch (Throwable $e) {
			$photosHtml = '';
		}
		$tplresult .= '<div class="block_white m10t"><h3 style="margin:0 0 8px;">Photos</h3>'
			.$photosHtml
			.'<form method="post" action="/?do=oblenergo&act=pillar_photo_add" enctype="multipart/form-data" style="margin-top:10px;">'
			.'<input type="hidden" name="pillarid" value="'.(int)$id.'">'
			.'<input type="file" name="files[]" multiple accept="image/jpeg,image/png"> '
			.'<input type="text" name="note" class="input1" placeholder="note (optional)" style="width:260px;"> '
			.'<button type="submit" class="btn_new">Upload</button>'
			.'</form></div>';

		$tplresult .= '</div></div>';		
 	break;
	case 'addpillar': 	
		$markers_tp = '';
		$tpid = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		$data_oblenergo_tp = $db->Fast('oblenergo_tp','*',['id'=>$tpid]);
		$data_oblenergo = $db->Fast('oblenergo','*',['id'=>$data_oblenergo_tp['oblenergoid']]);
		$data_location = $db->Fast('location','*',['id'=>$data_oblenergo_tp['locationid']]);
		$metatags = array('title'=>'Додати Опору','description'=>'Додати Опору','page'=>'oblenergo');
		$speedbar .='<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a><a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-angle-left"></i>Список обленерго</a><a class="brmhref" href="/?do=oblenergo&act=view&id='.$data_oblenergo['id'].'"><i class="fi fi-rr-angle-left"></i>'.$data_oblenergo['name'].'</a><a class="brmhref" href="/?do=oblenergo&act=tp&id='.$data_oblenergo_tp['id'].'"><i class="fi fi-rr-angle-left"></i>'.$data_oblenergo_tp['subname'].' '.$data_oblenergo_tp['nomer_tp'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Нова опора</span>';
		$tplresult .= '<div class="block_flex"><div class="class1">';		
		$tplresult .= '<div class="nav-fiber p10"><form onsubmit="return validateForm();" action="/?do=oblenergo" method="post"><input name="tpid" type="hidden" value="'.$data_oblenergo_tp['id'].'"><input name="act" type="hidden" value="savepillar"><input type="hidden" id="lan" name="lan"><input type="hidden" id="lon" name="lon">';	
		$tplresult .='<label for="name">Номер опори:</label><input type="text" id="nomer" name="nomer" required="" autocomplete="off" style="width:50%;"><br>';
		$sql_obl_line = $db->SimpleWhile("SELECT * from oblenergo_countliniy where tpid = ".$data_oblenergo_tp['id']);
		if(isset($sql_obl_line) && count($sql_obl_line)>0) {
			foreach ($sql_obl_line as $lineid => $line) {
				$tplresult .= '<input class="checkcss" name="line[]" value="'.$line['nomer_liniy'].'" type="checkbox">Лінія '.$line['nomer_liniy'].'<br>';
			}
		}
		$tplresult .='<br><label for="type">Тип опори:</label>';
		$listtype_pillar = '<option value="1">0.4 кВ</option><option value="2">10 кВ</option><option value="3">35 кВ</option>';
		$tplresult .='<select class="select" name="type_pillar" id="type_pillar">'.$listtype_pillar.'</select><br>';
		$sql_obl_con = $db->SimpleWhile("SELECT * from oblenergo_concurent");
		if(isset($sql_obl_con) && count($sql_obl_con)>0) {
			foreach ($sql_obl_con as $oblid => $concurent) {
				$tplresult .= '<input class="checkcss" name="concurent[]" value="'.$concurent['id'].'" type="checkbox">'.$concurent['name'].'<br>';
			}
		}else{
			$tplresult .='<label for="name">Кількість провайдерів на опорі:</label><input type="text" id="count" name="count" required="" autocomplete="off" style="width:10%;"><br>';
		}		
		$tplresult .='<br><label for="name">Опис:</label><textarea name="descr" id="descr" class="comm"></textarea><br>';
		$tplresult .='<input type="submit" value="'.$lang['add'].'"></form></div>';		
		$tplresult .= '</div><div class="class1">';
		$gpslan = (float)(!empty($data_oblenergo_tp['lan']) ? $data_oblenergo_tp['lan'] : (!empty($data_location['lan']) ? $data_location['lan'] : $config['geo_lan']));
		$gpslon = (float)(!empty($data_oblenergo_tp['lon']) ? $data_oblenergo_tp['lon'] : (!empty($data_location['lon']) ? $data_location['lon'] : $config['geo_lon']));
		$zoom = 17;
		$oblenergo_pillar = $db->SimpleWhile("SELECT * FROM oblenergo_pillar where tpid = ".$data_oblenergo_tp['id']);		
		if(isset($oblenergo_pillar) && count($oblenergo_pillar)>0){
			foreach($oblenergo_pillar as $ho){
				if(!empty($ho['lan']) && !empty($ho['lon'])){
					$tipPillar = $data_oblenergo_tp['locationname']."<br>".$data_oblenergo_tp['oblenergoname']."<br>".$data_oblenergo_tp['subname']." ".$data_oblenergo_tp['name_tp']." ".$data_oblenergo_tp['nomer_tp']."<br>Опора: ".$ho['nomer_pillar']."<br>";
					$markers_tp .= "L.marker([".(float)$ho['lan'].",".(float)$ho['lon']."],{icon: L.divIcon({className: 'mapper', html: '<div class=\"pillaricon\"><img src=\"../style/img/".(isset($ho['count_concurrent']) && $ho['count_concurrent']>1?'prov_1':'prov_0').".png\"></div>'})})";
					$markers_tp .= ".bindTooltip(".json_encode($tipPillar, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).")";
					$markers_tp .= ".addTo(map);";
				}
			}
		}
		if(!empty($data_oblenergo_tp['lan']) && !empty($data_oblenergo_tp['lon'])){
			$tipTp = $data_oblenergo_tp['locationname']."<br>".$data_oblenergo_tp['oblenergoname']."<br>".$data_oblenergo_tp['subname']." ".$data_oblenergo_tp['name_tp']." ".$data_oblenergo_tp['nomer_tp'];
			$markers_tp .= "L.marker([".(float)$data_oblenergo_tp['lan'].",".(float)$data_oblenergo_tp['lon']."],{icon: L.divIcon({className: 'mapper', html: '<div class=\"oblenergo_tp\"><img src=\"../style/img/oblenergo_tp.png\"></div>'})})";
			$markers_tp .= ".bindTooltip(".json_encode($tipTp, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).")";
			$markers_tp .= ".addTo(map);";
		};
		$mapper = getMap();
		$mapjs = <<<HTML
		<script>
		var lat = {$gpslan}; 
		var lon = {$gpslon};
		var map = L.map('divmap');
		map.setView([lat, lon], {$zoom});
		{$mapper}
		{$markers_tp}
		map.on('click', function(event) {
			var clickedLatLng = event.latlng;
			L.popup().setLatLng(clickedLatLng).setContent("Latitude: " + clickedLatLng.lat + "<br>Longitude: " + clickedLatLng.lng).openOn(map);
			document.getElementById('lan').value = clickedLatLng.lat;
			document.getElementById('lon').value = clickedLatLng.lng;
		});
		</script>
		HTML;
		$tplresult .= '
		<link rel="stylesheet" href="../style/map/leaflet.css" />
		<script src="../style/map/leaflet.js"></script>
		<script src="../style/map/mymarker.js"></script>
		<div id="divmap"></div>
		'.$mapjs;		
		$tplresult .= '</div></div>';		
	break;
	case 'pillar_photo_add':
		$pillarId = isset($_POST['pillarid']) ? (int)$_POST['pillarid'] : 0;
		if ($pillarId <= 0) { $go->go('/?do=oblenergo'); exit; }
		if (empty($_FILES['files']) || !is_array($_FILES['files']['tmp_name'] ?? null)) {
			$go->go('/?do=oblenergo&act=editpillar&id='.$pillarId); exit;
		}
		oblEnsurePillarPhotoTable($pdo);
		require ENGINE_DIR.'classes/resize.class.php';
		require ENGINE_DIR.'functions/photo.php';

		$note = isset($_POST['note']) ? Clean::text($_POST['note']) : '';
		$added = date('Y-m-d H:i:s');
		$userId = isset($USER['id']) ? (int)$USER['id'] : null;

		$ins = $pdo->prepare('INSERT INTO oblenergo_pillar_photo (pillarid, photo, note, added, user_id) VALUES (:pid, :ph, :note, :added, :uid)');
		$names = (array)$_FILES['files']['name'];
		$tmpNames = (array)$_FILES['files']['tmp_name'];
		$errors = (array)($_FILES['files']['error'] ?? []);
		$sizes = (array)($_FILES['files']['size'] ?? []);
		$max = min(count($tmpNames), 8);
		for ($i=0; $i<$max; $i++) {
			$err = isset($errors[$i]) ? (int)$errors[$i] : UPLOAD_ERR_NO_FILE;
			if ($err !== UPLOAD_ERR_OK) continue;
			$size = isset($sizes[$i]) ? (int)$sizes[$i] : 0;
			if ($size <= 0 || $size > 8*1024*1024) continue; // 8MB
			$f = [
				'name' => (string)($names[$i] ?? 'img'),
				'tmp_name' => (string)$tmpNames[$i],
			];
			$saved = savePhoto($f, 'pillar_'.$pillarId);
			if (!$saved) continue;
			$ins->execute([
				':pid' => $pillarId,
				':ph' => (string)$saved,
				':note' => $note,
				':added' => $added,
				':uid' => $userId
			]);
		}
		$go->go('/?do=oblenergo&act=editpillar&id='.$pillarId);
		exit;
	break;
	case 'pillar_photo_del':
		$pillarId = isset($_GET['pillarid']) ? (int)$_GET['pillarid'] : 0;
		$photoId = isset($_GET['photo_id']) ? (int)$_GET['photo_id'] : 0;
		if ($pillarId <= 0 || $photoId <= 0) { $go->go('/?do=oblenergo'); exit; }
		oblEnsurePillarPhotoTable($pdo);
		try {
			$st = $pdo->prepare('SELECT id, pillarid, photo FROM oblenergo_pillar_photo WHERE id = :id LIMIT 1');
			$st->execute([':id' => $photoId]);
			$row = $st->fetch(PDO::FETCH_ASSOC);
			if ($row && (int)$row['pillarid'] === $pillarId) {
				$del = $pdo->prepare('DELETE FROM oblenergo_pillar_photo WHERE id = :id');
				$del->execute([':id' => $photoId]);
				$fn = (string)$row['photo'];
				if ($fn !== '') {
					@unlink(ROOT_DIR.'/file/photo/'.$fn);
					@unlink(ROOT_DIR.'/file/photo/small/'.$fn);
				}
			}
		} catch (Throwable $e) {}
		$go->go('/?do=oblenergo&act=editpillar&id='.$pillarId);
		exit;
	break;
	case 'delettp': 	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){
			$data_oblenergo_tp = $db->Fast('oblenergo_tp','*',['id'=>$id]);
			if(isset($data_oblenergo_tp['id']) && !empty($data_oblenergo_tp['id']) ){
				$db->query("DELETE FROM oblenergo_tp WHERE id = '{$data_oblenergo_tp['id']}'");
				$go->go('/?do=oblenergo&act=view&id='.$data_oblenergo_tp['oblenergoid']);
			}
		}		
		$go->go('/?do=oblenergo');
	break;
	case 'deletoblenergo': 		
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){
			$data_oblenergo = $db->Fast('oblenergo','*',['id'=>$id]);
			if(isset($data_oblenergo['id']) && !empty($data_oblenergo['id']) ){
				$db->query("DELETE FROM oblenergo_pillar WHERE oblenergoid = '{$data_oblenergo['id']}'");
				$db->query("DELETE FROM oblenergo_tp WHERE oblenergoid = '{$data_oblenergo['id']}'");
				$db->query("DELETE FROM oblenergo_subprovider WHERE oblenergoid = '{$data_oblenergo['id']}'");
				$db->query("DELETE FROM oblenergo_pillarliniy WHERE oblenergoid = '{$data_oblenergo['id']}'");
				$db->query("DELETE FROM oblenergo_countliniy WHERE oblenergoid = '{$data_oblenergo['id']}'");
				$db->query("DELETE FROM oblenergo WHERE id = '{$data_oblenergo['id']}'");
			}
		}
		$go->go('/?do=oblenergo');
	break;
	case 'deletpillar': 		
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){
			$data_oblenergo_pillar = $db->Fast('oblenergo_pillar','*',['id'=>$id]);
			if(isset($data_oblenergo_pillar['id']) && !empty($data_oblenergo_pillar['id']) ){
				$db->query("DELETE FROM oblenergo_pillarliniy WHERE pillarid = '{$data_oblenergo_pillar['id']}'");
				$db->query("DELETE FROM oblenergo_pillar WHERE id = '{$data_oblenergo_pillar['id']}'");
				$db->query("DELETE FROM oblenergo_subprovider WHERE pillarid = '{$data_oblenergo_pillar['id']}'");
				$go->go('/?do=oblenergo&act=tp&id='.$data_oblenergo_pillar['tpid']);
			}
		}		
		$go->go('/?do=oblenergo');
	break;
	case 'updatepillar': 		
		if(isset($_POST['nomer']) && isset($_POST['pillarid'])){
			$pillarid = Clean::text($_POST['pillarid']);
			$data_pillar = $db->Fast('oblenergo_pillar','*',['id'=>$pillarid]);
			$data_tp = $db->Fast('oblenergo_tp','*',['id'=>$data_pillar['tpid']]);
			$sqlinsert['nomer_pillar'] = Clean::text($_POST['nomer']);
			////// descr
			$sqlinsert['descr'] = (isset($_POST['descr'])?Clean::text($_POST['descr']):null);
			$sqlinsert['type_pillar'] = Clean::int($_POST['type_pillar']);
			if(isset($_POST['lan']) && isset($_POST['lon'])){
				$lan = Clean::text($_POST['lan']);
				$lon = Clean::text($_POST['lon']);
				$lan = substr($lan, 0, strpos($lan, '.') + 7);
				$lon = substr($lon, 0, strpos($lon, '.') + 7);
				$sqlinsert['lan'] = $lan;
				$sqlinsert['lon'] = $lon;
				$sqlinsert['updates'] = $oblenergo_clock;
			}
			$db->SQLupdate('oblenergo_pillar',$sqlinsert,['id'=>$pillarid]);
			$prov_tp_new = [];
			foreach ($_POST['concurent'] as $prov_id) {
				$prov_id = (int)$prov_id;
				$prov_tp_new[$prov_id]['id'] = $prov_id;
			}
			$prov_tp = [];
			$sql_obl_prov_current = $db->SimpleWhile("SELECT * FROM oblenergo_subprovider WHERE pillarid = '{$pillarid}'");
			if ($sql_obl_prov_current && count($sql_obl_prov_current) > 0) {
				foreach ($sql_obl_prov_current as $current_line) {
					$prov_tp[$current_line['providerid']]['id'] = $current_line['providerid'];
				}
			}	
			foreach ($prov_tp as $prov_id => $prov) {
				if (!isset($prov_tp_new[$prov_id])) {
					$db->query("DELETE FROM oblenergo_subprovider WHERE pillarid = '{$pillarid}' AND providerid = '{$prov_id}'");
				}
			}	
			foreach ($prov_tp_new as $prov_id => $pro) {
				if (!isset($prov_tp[$prov_id])) {
					$db->SQLinsert('oblenergo_subprovider',[
							'providerid'=>$prov_id,
							'pillarid'=>$pillarid,
							'locationid'=>$data_tp['locationid'],
							'tpid'=>$data_pillar['tpid'],
							'oblenergoid'=>$data_pillar['oblenergoid']
						]);
				}
			}			
			$linr_tp_new = [];
			foreach ($_POST['line'] as $line_id) {
				$line_id = (int)$line_id;
				$linr_tp_new[$line_id]['id'] = $line_id;
			}
			$linr_tp = [];
			$sql_obl_line_current = $db->SimpleWhile("SELECT * FROM oblenergo_pillarliniy WHERE pillarid = '{$pillarid}'");
			if ($sql_obl_line_current && count($sql_obl_line_current) > 0) {
				foreach ($sql_obl_line_current as $current_line) {
					$linr_tp[$current_line['lineid']]['id'] = $current_line['lineid'];
				}
			}
			$db->SQLupdate('oblenergo_pillar',['count_concurrent'=>count($prov_tp_new)],['id'=>$pillarid]);			
			foreach ($linr_tp as $line_id => $line) {
				if (!isset($linr_tp_new[$line_id])) {
					$db->query("DELETE FROM oblenergo_pillarliniy WHERE pillarid = '{$pillarid}' AND lineid = '{$line_id}'");
				}
			}
			foreach ($linr_tp_new as $line_id => $line) {
				if (!isset($linr_tp[$line_id])) {
					$db->SQLinsert('oblenergo_pillarliniy', ['tpid' => $data_pillar['tpid'],'locationid' => $data_tp['locationid'],'oblenergoid' => $data_pillar['oblenergoid'],'lineid' => $line_id, 'pillarid' => $pillarid]);
				}
			}
			$go->go('/?do=oblenergo&act=editpillar&id='.$pillarid);
		}
		$go->go('/?do=oblenergo');
	break;
	case 'savepillar': 	
		if(isset($_POST['nomer']) && isset($_POST['type_pillar']) && isset($_POST['tpid']) && count($_POST['line'])>0){
			$sqlinsert['nomer_pillar'] = Clean::text($_POST['nomer']);
			$sqlinsert['type_pillar'] = Clean::int($_POST['type_pillar']);
			if(isset($_POST['concurent'])){
				
			}else{			
				$sqlinsert['count_concurrent'] = (isset($_POST['count']) ? Clean::int($_POST['count']) : 0);
			}
			$sqlinsert['tpid'] = Clean::int($_POST['tpid']);
			$data_oblenergo_tp = $db->Fast('oblenergo_tp','*',['id'=>$sqlinsert['tpid']]);
			$sqlinsert['oblenergoid'] = $data_oblenergo_tp['oblenergoid'];	
			if(isset($_POST['descr'])){
				$sqlinsert['descr'] = Clean::text($_POST['descr']);
			}
			if(isset($_POST['lan']) && isset($_POST['lon'])){
				$lan = Clean::text($_POST['lan']);
				$lon = Clean::text($_POST['lon']);
				$lan = substr($lan, 0, strpos($lan, '.') + 7);
				$lon = substr($lon, 0, strpos($lon, '.') + 7);
				$sqlinsert['lan'] = $lan;
				$sqlinsert['lon'] = $lon;
				$sqlinsert['added'] = $oblenergo_clock;
			}
			$db->SQLinsert('oblenergo_pillar',$sqlinsert);
			$pillarid = $db->getInsertId();
			for($j = 0; $j <= count($_POST['line']); $j++) {
				if(isset($_POST['line'][$j])){
					$db->SQLinsert('oblenergo_pillarliniy',[
						'lineid'=>(int)$_POST['line'][$j],
						'pillarid'=>$pillarid,
						'locationid'=>$data_oblenergo_tp['locationid'],
						'tpid'=>$sqlinsert['tpid'],
						'oblenergoid'=>$sqlinsert['oblenergoid']
					]);
				}
			}	
			if(isset($_POST['concurent']) && count($_POST['concurent'])>0){
				for($s = 0; $s <= count($_POST['concurent']); $s++) {
					if(isset($_POST['concurent'][$s])){
						$db->SQLinsert('oblenergo_subprovider',[
							'providerid'=>(int)$_POST['concurent'][$s],
							'pillarid'=>$pillarid,
							'locationid'=>$data_oblenergo_tp['locationid'],
							'tpid'=>$sqlinsert['tpid'],
							'oblenergoid'=>$sqlinsert['oblenergoid']
						]);
					}
				}
				$db->SQLupdate('oblenergo_pillar',['count_concurrent'=>count($_POST['concurent'])],['id'=>$pillarid]);
			}				
		}
		$go->go('/?do=oblenergo&act=tp&id='.$sqlinsert['tpid']);
	break;
	case 'edittp':{
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if (!$id) { 
			$go->go('/?do=oblenergo'); 
			exit; 
		}
		$stmt = $pdo->prepare('SELECT * FROM oblenergo_tp WHERE id = :id LIMIT 1');
		$stmt->execute([':id' => $id]);
		$data_oblenergo_tp = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$data_oblenergo_tp) { 
			$go->go('/?do=oblenergo'); 
			exit; 
		}
		$listobl = '';
		$stmt = $pdo->query('SELECT id, name FROM oblenergo ORDER BY name');
		$oblenergoRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($oblenergoRows as $obl) {
			$sel = (!empty($data_oblenergo_tp['oblenergoid']) && (int)$data_oblenergo_tp['oblenergoid'] === (int)$obl['id']) ? 'selected' : '';
			$listobl .= '<option value="'.(int)$obl['id'].'" '.$sel.'>'.$obl['name'].'</option>';
		}
		$listlocation = '';
		$stmt = $pdo->query('SELECT id, name, lan, lon FROM location ORDER BY name');
		$locationRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($locationRows as $loc) {
			$sel = (!empty($data_oblenergo_tp['locationid']) && (int)$data_oblenergo_tp['locationid'] === (int)$loc['id']) ? 'selected' : '';
			$listlocation .= '<option value="'.(int)$loc['id'].'" '.$sel.'>'.$loc['name'].'</option>';
		}
		$gpslan = (float)($config['geo_lan'] ?? 0);
		$gpslon = (float)($config['geo_lon'] ?? 0);
		if (!empty($data_oblenergo_tp['locationid'])) {
			$stmt = $pdo->prepare('SELECT lan, lon FROM location WHERE id = :id LIMIT 1');
			$stmt->execute([':id' => (int)$data_oblenergo_tp['locationid']]);
			if ($loc = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if (is_numeric($loc['lan'])) $gpslan = (float)$loc['lan'];
				if (is_numeric($loc['lon'])) $gpslon = (float)$loc['lon'];
			}
		}
		$metatags = ['title' => 'Редагувати', 'description' => 'Редагувати', 'page' => 'edittpoblenergo'];
		$speedbar .= '<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a>'
				   . '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагувати</span>';
		$countliniyOpts = '<option value="0"></option>';
		$curCount = (int)($data_oblenergo_tp['countliniy'] ?? 0);
		for ($i = 1; $i <= 20; $i++) {
			$sel = ($curCount === $i) ? 'selected' : '';
			$countliniyOpts .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
		}
		$nomerVal = $data_oblenergo_tp['nomer_tp'];
		$subnameVal = $data_oblenergo_tp['subname'];
		$lanVal = isset($data_oblenergo_tp['lan']) ? (float)$data_oblenergo_tp['lan'] : '';
		$lonVal = isset($data_oblenergo_tp['lon']) ? (float)$data_oblenergo_tp['lon'] : '';
		$tplresult .= '<div class="block_flex"><div class="class1">';
		$tplresult .= '<div class="nav-fiber p10">
			<form action="/?do=oblenergo" method="post">
				<input name="act" type="hidden" value="updatetp">
				<input type="hidden" id="lan" name="lan" value="'.$lanVal.'">
				<input type="hidden" id="lon" name="lon" value="'.$lonVal.'">
				<label for="nomer">Номер:</label>
				<input type="text" id="nomer" name="nomer" value="'.$nomerVal.'" autocomplete="off" style="width:50%;"><br>
				<label for="subname">Абревіатура (ТП,КТП):</label>
				<input type="text" id="subname" name="subname" value="'.$subnameVal.'" required autocomplete="off" style="width:20%;"><br>
				<label for="oblenergo">Обленерго:</label>
				<select class="select" name="oblenergo" id="oblenergo">'.$listobl.'</select><br>
				<label for="countliniy">Кількість ліній:</label>
				<select class="select" name="countliniy" id="countliniy">'.$countliniyOpts.'</select><br>
				<label for="location">Локація:</label>
				<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select><br>
				<input type="hidden" id="tpid" name="tpid" value="'.(int)$data_oblenergo_tp['id'].'">
				<input type="submit" value="'.$lang['save'].'">
			</form>
		</div>';
		$tplresult .= '</div><div class="class1">';
		$mapper = getMap();
		$gpslanJs = json_encode((float)$gpslan);
		$gpslonJs = json_encode((float)$gpslon);
		$zoom = 15;
		$tplresult .= '<link rel="stylesheet" href="../style/map/leaflet.css" />'
					. '<script src="../style/map/leaflet.js"></script>'
					. '<script src="../style/map/mymarker.js"></script>'
					. '<div id="divmap" style="height:420px;"></div>'
					. "<script>
						var lat = {$gpslanJs};
						var lon = {$gpslonJs};
						var map = L.map('divmap').setView([lat, lon], {$zoom});
						{$mapper}
						map.on('click', function(event) {
							var p = event.latlng;
							L.popup().setLatLng(p).setContent('Latitude: ' + p.lat + '<br>Longitude: ' + p.lng).openOn(map);
							document.getElementById('lan').value = p.lat.toFixed(6);
							document.getElementById('lon').value = p.lng.toFixed(6);
						});
					  </script>";
		$tplresult .= '</div></div>';
	}
	break;
	case 'updatetp':{
		if (!isset($_POST['tpid'], $_POST['nomer'])) { 
			$go->go('/?do=oblenergo'); 
			exit; 
		}
		$tpid = Clean::int($_POST['tpid']);
		$nomer = Clean::text($_POST['nomer']);
		$subname = isset($_POST['subname']) ? Clean::text($_POST['subname']) : null;
		$oblId = isset($_POST['oblenergo']) ? Clean::int($_POST['oblenergo']) : null;
		$locId = isset($_POST['location']) ? Clean::int($_POST['location'])  : null;
		$countLiniy  = isset($_POST['countliniy'])? Clean::int($_POST['countliniy']): 0;
		$lan = isset($_POST['lan']) ? (float)$_POST['lan'] : null;
		$lon = isset($_POST['lon']) ? (float)$_POST['lon'] : null;
		if ($lan !== null) $lan = (float)number_format($lan, 6, '.', '');
		if ($lon !== null) $lon = (float)number_format($lon, 6, '.', '');
		$oblName = null;
		if ($oblId) {
			$stmt = $pdo->prepare('SELECT name FROM oblenergo WHERE id = :id');
			$stmt->execute([':id' => $oblId]);
			$oblName = $stmt->fetchColumn() ?: null;
		}
		$locName = null;
		if ($locId) {
			$stmt = $pdo->prepare('SELECT name FROM location WHERE id = :id');
			$stmt->execute([':id' => $locId]);
			$locName = $stmt->fetchColumn() ?: null;
		}
		$stmt = $pdo->prepare('SELECT countliniy FROM oblenergo_tp WHERE id = :id');
		$stmt->execute([':id' => $tpid]);
		$oldCount = (int)($stmt->fetchColumn() ?: 0);
		$stmt = $pdo->prepare("
			UPDATE oblenergo_tp SET
				nomer_tp = :nomer,
				subname = :subname,
				oblenergoid = :oblid,
				oblenergoname = :oblname,
				locationid = :locid,
				locationname = :locname,
				countliniy = :cnt,
				lan = :lan,
				lon = :lon,
				added = :added
			WHERE id = :id
		");
		$stmt->execute([
			':nomer'  => $nomer, ':subname'=> $subname, ':oblid'  => $oblId, ':oblname'=> $oblName, ':locid'  => $locId, ':locname'=> $locName, ':cnt' => $countLiniy, ':lan' => $lan, ':lon' => $lon, ':added' => $oblenergo_clock, ':id' => $tpid
		]);
		if ($countLiniy !== $oldCount) {
			if ($countLiniy > $oldCount) {
				$ins = $pdo->prepare('INSERT INTO oblenergo_countliniy (nomer_liniy, locationid, tpid, oblenergoid) VALUES (:n, :locid, :tpid, :oblid)');
				for ($n = $oldCount + 1; $n <= $countLiniy; $n++) {
					$ins->execute([
						':n'=> $n,':locid' => $locId,':tpid'  => $tpid,':oblid' => $oblId
					]);
				}
			}
			if ($countLiniy < $oldCount) {
				$del = $pdo->prepare('DELETE FROM oblenergo_countliniy WHERE tpid = :tpid AND nomer_liniy > :n');
				$del->execute([':tpid' => $tpid, ':n' => $countLiniy]);
			}
			$upd = $pdo->prepare('UPDATE oblenergo_countliniy SET locationid = :locid, oblenergoid = :oblid WHERE tpid = :tpid');
			$upd->execute([':locid' => $locId, ':oblid' => $oblId, ':tpid' => $tpid]);
		}
		$go->go('/?do=oblenergo&act=tp&id=' . $tpid);
		exit;
	}
	break;
	case 'addtp': 	
		$obid = isset($_GET['obid']) ? Clean::int($_GET['obid']) : null;
		$data_oblenergo = $db->Fast('oblenergo','*',['id'=>$obid]);
		$metatags = array('title'=>'Додати обленерго','description'=>'Додати обленерго','page'=>'oblenergo');
		$speedbar .='<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати</span>';
		$tplresult .= '<div class="nav-fiber p10"><form action="/?do=oblenergo" method="post"><input name="act" type="hidden" value="savetp">';	
		$tplresult .='<label for="name">Номер:</label><input type="text" id="nomer" name="nomer" required="" autocomplete="off" style="width:50%;"><span id="nomer-result" style="color:red; margin-left:10px;"></span><br>';
		$tplresult .='<label for="name">Абревіатура (ТП,КТП):</label><input type="text" id="subname" name="subname" required="" autocomplete="off" style="width:20%;" value="ТП"><br><label for="type">Обленерго:</label>';
		$oblenergo = $db->SimpleWhile("SELECT * from oblenergo");
		$listobl = '';
		if(isset($oblenergo) && count($oblenergo)>0){
			foreach($oblenergo as $obl){
				$listobl .= '<option value="'.$obl['id'].'" '.(isset($data_oblenergo['id']) && $data_oblenergo['id']==$obl['id']?'selected':'').'>'.$obl['name'].'</option>';
			}
		}
		$tplresult .='<select class="select" name="oblenergo" id="oblenergo">'.$listobl.'</select><br>';
		$tplresult .='<label for="type">Кількість ліній:</label>';
		$countliniy = '<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		<option value="9">9</option>		
		<option value="10">10</option>
		<option value="11">11</option>
		<option value="12">12</option>
		<option value="13">13</option>
		<option value="14">14</option>
		<option value="15">15</option>
		<option value="16">16</option>
		<option value="17">17</option>
		<option value="18">18</option>
		<option value="19">19</option>
		<option value="20">20</option>
		
		';
		$tplresult .='<select class="select" name="countliniy" id="countliniy">'.$countliniy.'</select><br>';
		$tplresult .='<label for="type">Локація:</label>';
		$location = getListLocations();
		$listlocation = '';
		if(isset($location) && count($location)>0){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'" '.(isset($data_oblenergo['locationid']) && $data_oblenergo['locationid']==$loc['id']?'selected':'').'>'.$loc['name'].'</option>';
			}
		}
		$tplresult .='<select class="select" name="location" id="location">'.$listlocation.'</select><br>';
		$tplresult .='<input type="submit" value="'.$lang['add'].'">';
		$tplresult .='</form></div>';
		$tplresult .= <<<EOT
		<script>
		$(function () {
			$('#nomer').on('input', function () {
				var nomerValue = $.trim($(this).val());
				var \$resultSpan = $('#nomer-result');
				var oblenergo = $('#oblenergo').val();
				if (nomerValue.length < 2) {
					\$resultSpan.text('');
					return;
				}
				$.post('/?do=oblenergo&act=checker&obl=' + encodeURIComponent(oblenergo) + '&name=' + encodeURIComponent(nomerValue), function (result) {
					if (result === 'exists') {
						\$resultSpan.text('Номер вже існує!');
					} else {
						\$resultSpan.text('');
					}
				}).fail(function () {
					\$resultSpan.text('Помилка перевірки');
				});
			});
		});
		</script>
		EOT;
	break;
	case 'checker':
		if(isset($_GET['name'])){	
			$name = Clean::text($_GET['name']);
			$oblenergoid = Clean::int($_GET['obl']);
			if ($name === '') {
				exit('empty');
			}		
			$stmt = $pdo->prepare("SELECT COUNT(*) FROM oblenergo_tp 
				WHERE nomer_tp = :name AND oblenergoid = :oblenergoid");
			$stmt->execute([
				':name' => $name,':oblenergoid' => $oblenergoid
			]);
			$count = $stmt->fetchColumn();
			echo ($count > 0) ? 'exists' : 'ok';
		}
		exit;
	break;
	case 'savetp': 		
		if(isset($_POST['nomer'])){
			$sqlinsert['nomer_tp'] = Clean::text($_POST['nomer']);
			$sqlinsert['subname'] = Clean::text($_POST['subname']);
			$sqlinsert['oblenergoid'] = Clean::int($_POST['oblenergo']);
			$data_obl = $db->Fast('oblenergo','*',['id'=>$sqlinsert['oblenergoid']]);
			$sqlinsert['oblenergoname'] = $data_obl['name'] ?? "Обленерго";
			$sqlinsert['locationid'] = Clean::int($_POST['location']);
			$sqlinsert['countliniy'] = Clean::int($_POST['countliniy']);
			$data_location = $db->Fast('location','*',['id'=>$sqlinsert['locationid']]);
			$sqlinsert['locationname'] = $data_location['name'];
			$db->SQLinsert('oblenergo_tp',$sqlinsert);
			$tpid = $db->getInsertId();
			for ($j = 1; $j <= $sqlinsert['countliniy']; $j++) {
				$db->SQLinsert('oblenergo_countliniy',[
					'nomer_liniy'=>$j,
					'locationid'=>$sqlinsert['locationid'],
					'tpid'=>$tpid,
					'oblenergoid'=>$sqlinsert['oblenergoid']
				]);
			}
			$go->go('/?do=oblenergo&act=view&id='.$sqlinsert['oblenergoid']);
			exit();
		}
	break;
	case 'view': 	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		$data_oblenergo = $db->Fast('oblenergo','*',['id'=>$id]);
		$sqlcounttp = $db->Simple("SELECT count(id) as count FROM oblenergo_tp where oblenergoid = ".$data_oblenergo['id']);
		$sqlcountpilar = $db->Simple("SELECT count(id) as count FROM oblenergo_pillar where oblenergoid = ".$data_oblenergo['id']);
		$metatags = array('title'=>''.$data_oblenergo['name'].'','description'=>''.$data_oblenergo['name'].'','page'=>'viewoblenergo');
		$speedbar .='<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a>
		<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-angle-left"></i>Список обленерго</a>
		<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$data_oblenergo['name'].'</span>';
		$tplresult .= '<table class="css_house "><td class="house_opis" colspan="5"><h2>'.$data_oblenergo['name'].'</h2>
		<h3>ТП: <b>'.$sqlcounttp['count'].'</b> Опор: <b>'.$sqlcountpilar['count'].'</b> </h3>';
		if(empty($sqlcounttp['count']) && $sqlcounttp['count']==0){
		$tplresult .= '<a class="panel_house rr_1" href="/?do=oblenergo&act=deletoblenergo&id='.$data_oblenergo['id'].'">'.$lang['delolt'].'</a>';
		}
		$tplresult .= '</td><td class="house_opis">';
		$tplresult .= '<a href="/?do=oblenergo&act=addtp&obid='.$data_oblenergo['id'].'" class="obladd">Додати</a>';
		$tplresult .= '</td></table>';
		$tplresult .= '<table class="resp-tab"><thead><tr><th width="15%">Локація</th><th width="10%">Номер</th><th width="10%">Ліній</th><th width="10%">Опор</th><th>Провайдери</th><th width="15%"></th></tr></thead><tbody>';
			$sql_obl = $db->SimpleWhile("SELECT * from oblenergo_tp where oblenergoid = ".$id."");
			if(isset($sql_obl) && count($sql_obl)>0) {
				foreach ($sql_obl as $oblid => $tp) {
					$sqlpilar = $db->Simple("SELECT count(id) as count_pilar FROM oblenergo_pillar where tpid = ".$tp['id']);
					$data_prov = countProvider($tp['id']);
					$tplresult .= '<tr>
						<td><span class="obl_location">'.$tp['locationname'].'</span></td>
						<td class="td_url npon"><a href="/?do=oblenergo&act=tp&id='.$tp['id'].'">'.$tp['subname'].' '.$tp['nomer_tp'].'<img class="link_fiber" src="../style/img/link.png"></a></td>						
						<td><span class="obl_line">'.$tp['countliniy'].'</span></td>
						<td><span class="obl_line">'.$sqlpilar['count_pilar'].'</span></td>
						<td><span class="obl_line">'.count($data_prov).'</span></td>
						<td>'.(count($data_prov)==0 ? '<a class="panel_house rr_1" href="/?do=oblenergo&act=delettp&id='.$tp['id'].'">'.$lang['delolt'].'</a>':'').'</td>
					</tr>';
				}
			}else{
				$tplresult .= '<tr><td colspan="5">'.$lang['empty'].'</td></tr>';
			}
			
		$tplresult .= '</table>';
	break;
	case 'delline': 		
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){			
			$oblenergo_connect_pillar = $db->Fast('oblenergo_connect_pillar','*',['id'=>$id]);
			if(isset($oblenergo_connect_pillar['id']) && !empty($oblenergo_connect_pillar['id'])){
				$db->query("DELETE FROM oblenergo_connect_pillar WHERE id = '{$oblenergo_connect_pillar['id']}'");
				$go->go('/?do=oblenergo&act=tp&id='.$oblenergo_connect_pillar['tpid']);
				exit();
			}
		}
		$go->go('/?do=oblenergo');
		exit();
	break;
	case 'tp': 	
		$providerlist = listProvider();	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		$data_tp = $db->Fast('oblenergo_tp','*',['id'=>$id]);
		$data_oblenergo = $db->Fast('oblenergo','*',['id'=>$data_tp['oblenergoid']]);
		$tpOptions = '';
		if (!empty($data_tp['oblenergoid']) && !empty($data_tp['id'])) {
			$st = $pdo->prepare('SELECT id, subname, name_tp, nomer_tp FROM oblenergo_tp WHERE oblenergoid = :obl AND id <> :cur ORDER BY subname, CAST(nomer_tp AS SIGNED) ASC, id ASC');
			$st->execute([':obl' => (int)$data_tp['oblenergoid'], ':cur' => (int)$data_tp['id']]);
			foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
				$title = trim(((string)$r['subname']).' '.((string)$r['name_tp']).' '.((string)$r['nomer_tp']));
				$tpOptions .= '<option value="'.(int)$r['id'].'">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</option>';
			}
		}
		$block_stats = '';
		$oblpillar_stats = $db->SimpleWhile("SELECT lineid, COUNT(*) AS total_elements FROM `oblenergo_pillarliniy` WHERE tpid = '".$data_tp['id']."' GROUP BY lineid;");
		$metatags = array(
			'title'=>'Список опор',
			'description'=>'Список опор',
			'page'=>'viewtpoblenergo'
		);
		$speedbar .='
			<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>Обленерго</a>
			<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-angle-left"></i>Список обленерго</a>
			<a class="brmhref" href="/?do=oblenergo&act=view&id='.$data_oblenergo['id'].'"><i class="fi fi-rr-angle-left"></i>'.$data_oblenergo['name'].'</a>
			<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$data_tp['subname'].' №'.$data_tp['nomer_tp'].'</span>';		
		$tplresult .= '<table class="obl_global"><tr><td class="obl_info">';		
		$all_pillar_sql = get_cout_pillar_tp($data_tp['id']);
		$symis_pillar_sql = get_cout_pillar_tp_symis($data_tp['id']);
		if(!empty($all_pillar_sql['count'])){
			$block_stats .= 'Опор в ТП: <b>'.$all_pillar_sql['count'].'</b><br>';
		}
		if(!empty($symis_pillar_sql['count'])){
			$block_stats .= 'Всього опор (сумісний підвіс): <b>'.$symis_pillar_sql['count'].'</b><br>';
		}		
		if(isset($symis_pillar_sql['count']) && $symis_pillar_sql['count']>0 
			&& isset($all_pillar_sql['count']) && $all_pillar_sql['count']>0){
			$real = intval( $all_pillar_sql['count'] - $symis_pillar_sql['count'] );
			$block_stats .= 'Всього опор (без конкурентів): <b>'.$real.'</b><br>';
		}
		$export = '';
		if (isset($oblpillar_stats) && count($oblpillar_stats) > 0) {
			foreach ($oblpillar_stats as $stats) {
				$lineid = $stats['lineid'];
				$block_stats .= 'Лінія '.$lineid.' опор: <b>'.$stats['total_elements'].'</b><br>';
				$export .= 'Лінія '.$lineid.', опори №';
				$sql_export = $db->SimpleWhile("SELECT p.id as pid, p.nomer_pillar, p.count_concurrent, p.type_pillar  
					FROM oblenergo_pillarliniy pl	JOIN oblenergo_pillar p ON pl.pillarid = p.id
					WHERE pl.tpid = '".$data_tp['id']."' AND pl.lineid = '".$lineid."' ORDER BY pl.pillarid ASC");
				$count1[$lineid] = 0; // 0,4 Кв
				$count2[$lineid] = 0; // 10 Кв
				$free_pillar[$lineid] = ['count' => 0, 'pid' => array()]; // вільні
				$symis_pillar[$lineid] = ['count' => 0, 'pid' => array()]; // сумісні
				if (!empty($sql_export)) {
					foreach ($sql_export as $pl) {
						$export .= $pl['nomer_pillar'] . ', ';						
						switch ($pl['type_pillar']) {
							case 1:
								$count1[$lineid]++;
								break;
							case 2:
								$count2[$lineid]++;
								break;
						}						
						if ($pl['count_concurrent'] == 1) {
							$free_pillar[$lineid]['count']++;
							$free_pillar[$lineid]['pid'][$pl['pid']] = $pl['nomer_pillar'];
						} elseif ($pl['count_concurrent'] > 1) {
							$symis_pillar[$lineid]['count']++;
							$symis_pillar[$lineid]['pid'][$pl['pid']] = $pl['nomer_pillar'];
						}
					}
				}
			}
		}
		if(isset($count1) && count($count1)>0){
			foreach($count1 as $lineid => $count) {
				if($count > 0) {
					$block_stats .= 'Лінія '.$lineid.': Задіяно опор 0.4 кВ: <b>'.$count.'</b><br>';
				}
			}
		}
		if(isset($count2) && count($count2)>0){
			foreach($count2 as $lineid => $count) {
				if ($count > 0) {
					$block_stats .= 'Лінія '.$lineid.': Задіяно опор 10 кВ: <b>'.$count.'</b><br>';
				}
			}
		}
		$export = rtrim($export, ', ');
		$export_int = '';
		foreach($oblpillar_stats as $pl) {
			$lineid = $pl['lineid'];
			if (!empty($free_pillar[$lineid]['count'])) {
				$export_int .= 'Лінія '.$lineid.', без сумісного підвісу: '.$free_pillar[$lineid]['count'].' шт., ';
				$export_int .= ' (№'.implode(', ', $free_pillar[$lineid]['pid']).'). ';
			} 
			if (!empty($symis_pillar[$lineid]['count'])) {
				$export_int .= 'Лінія '.$lineid.', Сумісний підвіс: '.$symis_pillar[$lineid]['count'].' шт., ';
				$export_int .= ' (№'.implode(', ', $symis_pillar[$lineid]['pid']).'). ';
			}

		}
		$tplresult .= '<table class="css_house ">
			<tr>
				<td class="house_opis" colspan="5">
				<h2>'.$data_tp['oblenergoname'].'</h2>
				<h3>'.$data_tp['subname'].' '.$data_tp['nomer_tp'].'</h3>
				</td>
			</tr><tr>
				<td class="house_opis">
				'.$block_stats.'
				</td>
			</tr>
			<tr>			
			<td class="house_opis">
			'.(!empty($export) ? '
			Загальна список опор ТП
				<textarea style="height:150px;"> '.$export.'</textarea>
			' : '').'
				 
				'.(!empty($export_int) ? 
				'Підвіс
				<textarea style="height:200px;"> '.$export_int.'</textarea>' : '').'
			</td>
			</tr>
		</table>';		
		$tplresult .= '</td><td class="obl_content">';
		$fromTp = (int)$data_tp['id'];
		$tplresult .= <<<HTML
		<form id="movePillarsForm" method="post" action="/?do=oblenergo&act=move_pillars" class="m10b">
		  <input type="hidden" name="from_tpid" value="{$fromTp}">
		  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
			<b>Move selected pillars:</b>
			<select name="to_tpid" class="select" style="min-width:320px;">
			  <option value="">-- select TP --</option>
			  {$tpOptions}
			</select>
			<button type="submit" class="btn_new">Move</button>
			<button type="button" class="btn_export" onclick="oblSelectAllPillars(true)">Select all</button>
			<button type="button" class="btn_export" onclick="oblSelectAllPillars(false)">Clear</button>
		  </div>
		</form>
		<script>
		function oblSelectAllPillars(on){
		  document.querySelectorAll("input[data-obl-pillar='1']").forEach(function(cb){ cb.checked = !!on; });
		}
		(function(){
		  var form = document.getElementById('movePillarsForm');
		  if (!form) return;
		  form.addEventListener('submit', function(e){
			var to = this.querySelector('select[name=to_tpid]').value;
			var cnt = document.querySelectorAll("input[data-obl-pillar='1']:checked").length;
			if (!to || cnt <= 0) {
			  e.preventDefault();
			  alert('Select TP and pillars');
			  return;
			}
			if (!confirm('Move pillars: ' + cnt + ' pcs?')) {
			  e.preventDefault();
			}
		  });
		})();
		</script>
		HTML;
		$tplresult .= '
		<div class="lf_1 m10b">
			<a class="btn_new" href="/?do=oblenergo&act=addpillar&id='.$data_tp['id'].'"><i class="fi fi-rr-plus"></i>Додати опору</a>
			<a class="btn_export" href="#" onclick="exportToExcel()"><i class="fi fi-rr-download"></i>Експортувати в Excel</a>
			<a class="btn_edit" href="/?do=oblenergo&act=edittp&id='.$data_tp['id'].'" ><i class="fi fi-rr-settings"></i>'.$lang['edit'].'</a>
		</div>';
		$tplresult .= '
		<table class="resp-tab"><thead><tr><th width="3%"></th>
			<th width="20%">Номер опори</th>
			<th width="15%">Ліній</th>			
			<th width="15%">Тип опори</th>			
			<th width="20%">Провайдери</th>
			<th>Координати</th><th>'.$lang['added'].'</th><th></th></tr></thead><tbody>';
				$oblpillar = $db->SimpleWhile("SELECT * from oblenergo_pillar WHERE tpid = ".$data_tp['id']." ORDER BY CAST(nomer_pillar AS SIGNED) ASC");
				$linesByPillar = [];
				$provByPillar = [];
				$connectByPillar = [];
				if(isset($oblpillar) && count($oblpillar)>0){
					// Batch-load related data to avoid N+1 queries per pillar.
					$pillarIds = [];
					foreach ($oblpillar as $p){
						$pid = (int)($p['id'] ?? 0);
						if($pid>0) $pillarIds[] = $pid;
					}
					if(!empty($pillarIds)){
						$in = implode(',', array_map('intval', $pillarIds));

						$sqlLines = $db->SimpleWhile("SELECT pillarid, lineid FROM oblenergo_pillarliniy WHERE tpid = ".$data_tp['id']." AND pillarid IN ($in)");
						if(isset($sqlLines) && count($sqlLines)>0){
							foreach($sqlLines as $r){
								$pid = (int)$r['pillarid'];
								$linesByPillar[$pid][] = (int)$r['lineid'];
							}
						}

						$sqlProv = $db->SimpleWhile("SELECT pillarid, providerid FROM oblenergo_subprovider WHERE tpid = ".$data_tp['id']." AND pillarid IN ($in)");
						if(isset($sqlProv) && count($sqlProv)>0){
							foreach($sqlProv as $r){
								$pid = (int)$r['pillarid'];
								$provByPillar[$pid][] = (int)$r['providerid'];
							}
						}

						$sqlConn = $db->SimpleWhile("SELECT id, pillar1, pillar2 FROM oblenergo_connect_pillar WHERE tpid = ".$data_tp['id']." AND pillar1 IN ($in)");
						$p2ids = [];
						if(isset($sqlConn) && count($sqlConn)>0){
							foreach($sqlConn as $r){
								$p2 = (int)$r['pillar2'];
								if($p2>0) $p2ids[$p2] = $p2;
							}
						}
						$pillarNoById = [];
						if(!empty($p2ids)){
							$in2 = implode(',', array_map('intval', array_values($p2ids)));
							$sqlP2 = $db->SimpleWhile("SELECT id, nomer_pillar FROM oblenergo_pillar WHERE id IN ($in2)");
							if(isset($sqlP2) && count($sqlP2)>0){
								foreach($sqlP2 as $r){
									$pillarNoById[(int)$r['id']] = $r['nomer_pillar'];
								}
							}
						}
						if(isset($sqlConn) && count($sqlConn)>0){
							foreach($sqlConn as $r){
								$p1 = (int)$r['pillar1'];
								$p2 = (int)$r['pillar2'];
								$p2no = $pillarNoById[$p2] ?? ('#'.$p2);
								$connectByPillar[$p1] = ($connectByPillar[$p1] ?? '')
									.'<img class="link_pillar" src="../style/img/connect_pillar.png">'.$p2no.''
									.'<a href="/?do=oblenergo&act=delline&id='.(int)$r['id'].'" class="del_line"><img src="../style/img/close.png"></a>';
							}
						}
					}

					foreach($oblpillar as $pillar){
						$pid = (int)($pillar['id'] ?? 0);
						$liner = '';
						if(!empty($linesByPillar[$pid])){
							foreach($linesByPillar[$pid] as $lineid){
								$liner .= '<span class="obl_line" id="line-'.$lineid.'">л-'.$lineid.'</span>';
							}
						}
						$provider = '';
						if(!empty($provByPillar[$pid])){
							foreach($provByPillar[$pid] as $provId){
								if(!isset($providerlist[$provId])) continue;
								$provider .= '<span class="obl_prov"  id="prov-'.$providerlist[$provId]['id'].'">'.$providerlist[$provId]['name'].'</span>';
							}
						}
						$connect_pillar = $connectByPillar[$pid] ?? connect_line_pillar($pillar['id']);
				$tplresult .= '<tr>
						<td class="text_center"><input data-obl-pillar="1" type="checkbox" name="pillar_ids[]" form="movePillarsForm" value="'.(int)$pillar['id'].'"></td>
						<td>'.$pillar['nomer_pillar'].'<img onclick="connect_pillar('.$data_tp['id'].','.$pillar['id'].');" class="add_connect" src="../style/img/add_connect_line.png">
						<div id="tpid-'.$pillar['id'].'"></div>
						'.$connect_pillar.'
						</td>
						<td>'.$liner.'</td><td>'.type_pillar($pillar['type_pillar']).'</td>						
						<td>'.$provider.'</td><td class="td_url npon">'.(isset($pillar['lan']) && !empty($pillar['lan']) ? '
						<a href="https://www.google.com/maps?client=opera&q='.$pillar['lan'].','.$pillar['lon'].'" target="_blank">
						<img class="link_fiber" src="../style/img/addgps.png"> '.$pillar['lan'].','.$pillar['lon'].'
						</a>':'').'
						</td><td>
						'.$pillar['added'].'
						</td><td>
							<a class="panel_house rr_2" href="/?do=oblenergo&act=editpillar&id='.$pillar['id'].'">Налаштувати</a><a class="panel_house rr_1" href="/?do=oblenergo&act=deletpillar&id='.$pillar['id'].'">'.$lang['delolt'].'</a>
						</td>
					</tr>';
			}
		}else{
			$tplresult .= '<tr><td colspan="4">'.$lang['empty'].'</td></tr>';
		}
		$tplresult .= '</table>';		
		$tplresult .= '</td></tr></table>';
	break;
	case 'move_pillars':
		$fromTpid = isset($_POST['from_tpid']) ? (int)$_POST['from_tpid'] : 0;
		$toTpid = isset($_POST['to_tpid']) ? (int)$_POST['to_tpid'] : 0;
		$pillarIdsRaw = isset($_POST['pillar_ids']) ? (array)$_POST['pillar_ids'] : [];
		$pillarIds = [];
		foreach ($pillarIdsRaw as $pid) {
			$pid = (int)$pid;
			if ($pid > 0) $pillarIds[$pid] = $pid;
		}
		$pillarIds = array_values($pillarIds);
		if (count($pillarIds) > 2000) {
			$go->go('/?do=oblenergo&act=tp&id='.$fromTpid); exit;
		}
		if ($fromTpid <= 0 || $toTpid <= 0 || empty($pillarIds)) {
			$go->go('/?do=oblenergo'); exit;
		}

		$tpTo = $pdo->prepare('SELECT id, oblenergoid, locationid FROM oblenergo_tp WHERE id = :id LIMIT 1');
		$tpTo->execute([':id' => $toTpid]);
		$tpToRow = $tpTo->fetch(PDO::FETCH_ASSOC);
		if (!$tpToRow) {
			$go->go('/?do=oblenergo&act=tp&id='.$fromTpid); exit;
		}
		$newOblId = (int)$tpToRow['oblenergoid'];
		$newLocId = (int)$tpToRow['locationid'];

		try {
			$pdo->beginTransaction();

			$ph = implode(',', array_fill(0, count($pillarIds), '?'));
			$params = array_merge([$toTpid, $newOblId, $fromTpid], $pillarIds);
			$upd = $pdo->prepare("UPDATE oblenergo_pillar SET tpid = ?, oblenergoid = ? WHERE tpid = ? AND id IN ($ph)");
			$upd->execute($params);

			$params2 = array_merge([$toTpid, $newLocId, $newOblId, $fromTpid], $pillarIds);
			$upd2 = $pdo->prepare("UPDATE oblenergo_pillarliniy SET tpid = ?, locationid = ?, oblenergoid = ? WHERE tpid = ? AND pillarid IN ($ph)");
			$upd2->execute($params2);

			$params3 = array_merge([$toTpid, $newLocId, $newOblId, $fromTpid], $pillarIds);
			$upd3 = $pdo->prepare("UPDATE oblenergo_subprovider SET tpid = ?, locationid = ?, oblenergoid = ? WHERE tpid = ? AND pillarid IN ($ph)");
			$upd3->execute($params3);

			// Cross-TP connections become ambiguous: remove any connections that reference moved pillars.
			$del = $pdo->prepare("DELETE FROM oblenergo_connect_pillar WHERE tpid = ? AND (pillar1 IN ($ph) OR pillar2 IN ($ph))");
			$del->execute(array_merge([$fromTpid], $pillarIds, $pillarIds));

			$pdo->commit();
		} catch (Throwable $e) {
			if ($pdo->inTransaction()) $pdo->rollBack();
		}

		$go->go('/?do=oblenergo&act=tp&id='.$toTpid);
		exit;
	break;
	case 'save': 	
		if(isset($_POST['name']) && isset($_POST['location'])){
			$sqlinsert['name'] = Clean::text($_POST['name']);
			$sqlinsert['locationid'] = Clean::int($_POST['location']);
			$data_location = $db->Fast('location','*',['id'=>$sqlinsert['locationid']]);
			$sqlinsert['locationname'] = $data_location['name'];
			if(isset($sqlinsert['name']) && isset($sqlinsert['locationid']) && isset($sqlinsert['locationname'])){
				$db->SQLinsert('oblenergo',$sqlinsert);
			}
		}
		$go->go('/?do=oblenergo');
	break;	
	case 'load': 	
		$action = $_GET['a'] ?? 'bootstrap';
		function jexit($ok, $data=null, $msg=''){
			echo json_encode(['ok'=>$ok,'data'=>$data,'message'=>$msg], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			exit;
		}
		if ($action === 'bootstrap') {
			$obls = $pdo->query('SELECT id, name FROM oblenergo ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
			$locs = $pdo->query('SELECT id, name FROM location ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
			$oblCounts = $pdo->query('SELECT oblenergoid AS id, COUNT(*) AS cnt FROM oblenergo_tp GROUP BY oblenergoid')->fetchAll(PDO::FETCH_KEY_PAIR);
			$locCounts = $pdo->query('SELECT locationid AS id, COUNT(*) AS cnt FROM oblenergo_tp GROUP BY locationid')->fetchAll(PDO::FETCH_KEY_PAIR);
			jexit(true, [
				'obls' => array_map(fn($o)=>['id'=>$o['id'],'name'=>$o['name'],'count'=> (int)($oblCounts[$o['id']]??0)], $obls),
				'locations' => array_map(fn($l)=>['id'=>$l['id'],'name'=>$l['name'],'count'=> (int)($locCounts[$l['id']]??0)], $locs),
			]);
		}
		if ($action === 'bbox') {
			$south = (float)($_GET['south'] ?? 0);
			$west = (float)($_GET['west']  ?? 0);
			$north = (float)($_GET['north'] ?? 0);
			$east = (float)($_GET['east']  ?? 0);
			$zoom = (int)($_GET['zoom']  ?? 7);
			if ($north < $south) { $tmp=$south; $south=$north; $north=$tmp; }
			if ($east < $west) { $tmp=$west; $west=$east; $east=$tmp; }
			$selObls = isset($_GET['oblenergo']) ? array_values(array_filter(array_map('intval', (array)$_GET['oblenergo']))) : [];
			$selLocs = isset($_GET['location'])  ? array_values(array_filter(array_map('intval', (array)$_GET['location'])))  : [];
			$selTps = isset($_GET['tp']) ? array_values(array_filter(array_map('intval', (array)$_GET['tp'])))        : [];
			$wantTP = isset($_GET['want_tp']) ? (bool)$_GET['want_tp'] : true;
			$wantPillar = isset($_GET['want_pillar']) ? (bool)$_GET['want_pillar'] : ($zoom >= 16);
			$where = ['lan BETWEEN :south AND :north', 'lon BETWEEN :west AND :east'];
			$params = [':south'=>$south, ':north'=>$north, ':west'=>$west, ':east'=>$east];
			if ($selObls) {
				$ph=[]; 
				foreach($selObls as $i=>$v){ $ph[]=":obl{$i}"; $params[":obl{$i}"]=$v; }
				$where[] = 'oblenergoid IN ('.implode(',', $ph).')';
			}
			if ($selLocs) {
				$ph=[]; 
				foreach($selLocs as $i=>$v){ $ph[]=":loc{$i}"; $params[":loc{$i}"]=$v; }
				$where[] = 'locationid IN ('.implode(',', $ph).')';
			}
			if ($selTps) {
				$ph=[]; 
				foreach($selTps as $i=>$v){ $ph[]=":tp{$i}"; $params[":tp{$i}"]=$v; }
				$where[] = 'id IN ('.implode(',', $ph).')';
			}
			$whereSql = $where ? (' WHERE '.implode(' AND ',$where)) : '';
			$result = ['tps'=>[], 'pillars'=>[]];
			if ($wantTP) {
				$sql = 'SELECT id, nomer_tp, name_tp, subname, oblenergoid, oblenergoname, locationid, locationname, lan, lon FROM oblenergo_tp '.$whereSql.' ORDER BY id DESC LIMIT 20000';
				$stmt = $pdo->prepare($sql);
				$stmt->execute($params);
				$result['tps'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			if ($wantPillar) {
				$tpIds = [];
				if (!empty($result['tps'])) {
					foreach ($result['tps'] as $t) { $tpIds[] = (int)$t['id']; }
				} else {
					$stmt = $pdo->prepare('SELECT id FROM oblenergo_tp '.$whereSql.' LIMIT 20000');
					$stmt->execute($params);
					$tpIds = array_map(fn($r)=>(int)$r['id'], $stmt->fetchAll(PDO::FETCH_ASSOC));
				}
				if ($tpIds) {
					$inPh=[]; $pp=[]; foreach ($tpIds as $i=>$tid){ $inPh[]=":t{$i}"; $pp[":t{$i}"]=$tid; }
					$pp[':south']=$south; $pp[':north']=$north; $pp[':west']=$west; $pp[':east']=$east;
					$pillarLimit = ($zoom >= 17) ? 80000 : 40000;
					$sqlP = 'SELECT id, tpid, lan, lon, nomer_pillar, count_concurrent, type_pillar FROM oblenergo_pillar WHERE tpid IN ('.implode(',', $inPh).') AND lan BETWEEN :south AND :north AND lon BETWEEN :west AND :east LIMIT '.$pillarLimit;
					$sP = $pdo->prepare($sqlP);
					$sP->execute($pp);
					$result['pillars'] = $sP->fetchAll(PDO::FETCH_ASSOC);
				}
			}
			jexit(true, $result);
		}
		if ($action === 'tp_list') {
			$locationId = (int)($_GET['locationid'] ?? 0);
			if ($locationId <= 0) jexit(true, ['tps' => []]);
			$st = $pdo->prepare("\n\t\t\t\tSELECT id, nomer_tp, name_tp, subname, oblenergoname, lan, lon\n\t\t\t\t  FROM oblenergo_tp\n\t\t\t\t WHERE locationid = :loc\n\t\t\t\t ORDER BY subname ASC, nomer_tp ASC, id ASC\n\t\t\t\t LIMIT 30000\n\t\t\t");
			$st->execute([':loc' => $locationId]);
			jexit(true, ['tps' => $st->fetchAll(PDO::FETCH_ASSOC)]);
		}
		if ($action === 'map_data') {
			$locationId = (int)($_GET['locationid'] ?? 0);
			if ($locationId <= 0) jexit(true, ['tps' => [], 'pillars' => []]);

			$selTps = isset($_GET['tp']) ? array_values(array_filter(array_map('intval', (array)$_GET['tp']))) : [];
			$selTps = array_slice($selTps, 0, 5000);

			$where = ['locationid = :loc', 'lan IS NOT NULL', 'lon IS NOT NULL'];
			$params = [':loc' => $locationId];
			if (!empty($selTps)) {
				$ph = [];
				foreach ($selTps as $i => $v) { $ph[] = ":tp{$i}"; $params[":tp{$i}"] = $v; }
				$where[] = 'id IN ('.implode(',', $ph).')';
			}
			$whereSql = ' WHERE '.implode(' AND ', $where);

			$sqlTp = 'SELECT id, nomer_tp, name_tp, subname, oblenergoname, locationid, locationname, lan, lon FROM oblenergo_tp '.$whereSql.' ORDER BY id DESC LIMIT 30000';
			$stTp = $pdo->prepare($sqlTp);
			$stTp->execute($params);
			$tps = $stTp->fetchAll(PDO::FETCH_ASSOC);

			$tpIds = array_map(fn($r) => (int)$r['id'], $tps);
			if (empty($tpIds)) jexit(true, ['tps' => [], 'pillars' => []]);

			$inPh = [];
			$pp = [];
			foreach ($tpIds as $i => $tid) { $inPh[] = ":pt{$i}"; $pp[":pt{$i}"] = $tid; }
			$sqlP = 'SELECT id, tpid, lan, lon, nomer_pillar, count_concurrent, type_pillar FROM oblenergo_pillar WHERE tpid IN ('.implode(',', $inPh).') AND lan IS NOT NULL AND lon IS NOT NULL LIMIT 150000';
			$stP = $pdo->prepare($sqlP);
			$stP->execute($pp);
			$pillars = $stP->fetchAll(PDO::FETCH_ASSOC);
			jexit(true, ['tps' => $tps, 'pillars' => $pillars]);
		}
		jexit(false, null, 'Unknown action');
	break;
	case 'map': {
		$visicomkey = (string)($config['visicom_key'] ?? '');
		$metatags = ['title'=>'Map OBLENERGO','description'=>'Map OBLENERGO','page'=>'mapoblenergo'];
		$locations = $pdo->query("\n\t\t\tSELECT l.id, l.name, l.lan, l.lon, COUNT(tp.id) AS tp_count\n\t\t\t  FROM location l\n\t\t\t  LEFT JOIN oblenergo_tp tp ON tp.locationid = l.id\n\t\t\t WHERE l.lan IS NOT NULL AND l.lon IS NOT NULL\n\t\t\t GROUP BY l.id, l.name, l.lan, l.lon\n\t\t\t ORDER BY l.name\n\t\t")->fetchAll(PDO::FETCH_ASSOC);
		$centerLan = (float)($config['geo_lan'] ?? 49.0);
		$centerLon = (float)($config['geo_lon'] ?? 31.0);
		$zoom = 7;
		$DATA = [
			'locations' => $locations,
			'center' => ['lan'=>$centerLan, 'lon'=>$centerLon, 'zoom'=>$zoom],
			'api' => [
				'tp_list' => '/?do=oblenergo&act=load&a=tp_list',
				'map_data' => '/?do=oblenergo&act=load&a=map_data'
			]
		];
		$jsonDATA = json_encode($DATA, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		$jsonCurMap  = json_encode((string)($config['typemap'] ?? 'openstreetmap'));
		$jsonVisiKey = json_encode($visicomkey);
		$tplresult .= <<<HTML
		  <style>
			#obl-map-wrap { display:flex; gap:12px; width:100%; min-height: calc(100vh - 120px); }
			#obl-map-sidebar { width:360px; min-width:320px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:12px; overflow:auto; }
			#obl-map-sidebar h3 { margin:0 0 10px; }
			#obl-map-sidebar .row { margin-bottom:10px; }
			#obl-map-sidebar label { display:block; margin-bottom:4px; font-weight:600; }
			#obl-map-sidebar select, #obl-map-sidebar input { width:10%; box-sizing:border-box; padding:8px; border:1px solid #d1d5db; border-radius:8px; }
			#obl-map-sidebar .tp-list { height:320px; overflow:auto; border:1px solid #e5e7eb; border-radius:8px; padding:6px; }
			#obl-map-sidebar .tp-item { display:flex; align-items:flex-start; gap:6px; padding:6px 4px; border-bottom:1px dashed #f1f5f9; }
			#obl-map-sidebar .tp-item:last-child { border-bottom:none; }
			#obl-map-sidebar .tp-title { font-size:13px; line-height:1.25; }
			#obl-map-sidebar .btns { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
			#obl-map-sidebar button {border: 0;background: #4f7ca8;border-radius: 8px;padding: 3px 7px;padding: 8px 9px;}
			#obl-map-sidebar .hint { color:#64748b; font-size:12px; margin-top:6px; }
			#mapper { position: relative;   top: 0;flex:1; min-height: calc(100vh - 120px); border:1px solid #e5e7eb; border-radius:12px; }
			@media (max-width: 980px) {
				#obl-map-wrap { flex-direction:column; }
				#obl-map-sidebar { width:100%; min-width:100%; }
			}
		  </style>
		  <div id="obl-map-wrap">
			<div id="obl-map-sidebar">
			  <div class="row">
				<label for="locationSelect">Локація</label>
				<select id="locationSelect">
				  <option value="">Вибарти</option>
				</select>
			  </div>
			  <div class="row">
				<label for="tpSearch">Підстанція</label>
				<input id="tpSearch" type="text" placeholder="Номер опори/тп">
			  </div>
			  <div class="row">
				<div class="tp-list" id="tpList"></div>
				<div class="hint">Вибарти локацію</div>
			  </div>
			  <div class="btns">
				<button type="button" id="tpSelectAll">Вибрати всі ТП</button>
				<button type="button" id="tpClear">Очистити ТП</button>
				<button type="button" id="applyMap">Показати на карті</button>
				<button type="button" id="resetMap">Очистити</button>
			  </div>
			  <div class="row" style="margin-top:10px;">
				<label><input type="checkbox" id="showTp" checked> Показати ТП</label>
				<label><input type="checkbox" id="showPillar" checked> Показати Опори</label>
			  </div>
			  <div class="hint" id="mapSummary">Відсутні дані</div>
			</div>
			<div id="mapper" style="position: relative;top: 0;"></div>
		  </div>
		  <link rel="stylesheet" href="/style/map/leaflet.css" />
		  <script src="/style/map/leaflet.js"></script>
		  <script>
			window.CFG = {$jsonDATA};
			window.CURRENT_MAP = {$jsonCurMap};
			window.VISICOM_KEY = {$jsonVisiKey};
		  </script>
		  <script src="/style/js/oblenergo_map.js?v=2"></script>
		HTML;
	}
	break;
	default:
		$metatags = array('title'=>'Oblenergo list','description'=>'Oblenergo list','page'=>'oblenergo');
		$speedbar .='<a class="brmhref" href="/?do=oblenergo"><i class="fi fi-rr-apps"></i>OBLENERGO</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Oblenergo list</span>';
		$tplresult .= '<div id="list-add" style="padding:0;">';
		$oblenergo = $db->SimpleWhile("SELECT * from oblenergo");
		if(count($oblenergo)>0){
			foreach($oblenergo as $obl){
				$tplresult .= '<div class="add-dev-one"><a href="/?do=oblenergo&act=view&id='.$obl['id'].'" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/oblenergo.png"></div></div><div class="add-name">'.$obl['name'].' / '.$obl['locationname'].'</div></div>';
			}
			$tplresult .= '<div class="add-dev-one"><a href="/?do=oblenergo&act=map" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/mappillar.png"></div></div><div class="add-name">TP map</div></div>';
			$tplresult .= '<div class="add-dev-one"><a href="/?do=oblenergo&act=import" class="add-url"></a><div class="add-img"><div class="add-sub-img"><img src="../style/img/import_exel.png"></div></div><div class="add-name">Import data</div></div>';
		}else{
			$go->go('/?do=oblenergo&act=add');
		}
		$tplresult .= '</div>';
}	
$result ='<div id="onu-speedbar">'.$speedbar.'</div>'.$tplresult.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>
