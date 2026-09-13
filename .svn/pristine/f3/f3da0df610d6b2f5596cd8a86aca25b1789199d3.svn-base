<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
define('GENERATOR' , true);
require ENGINE_DIR.'functions/generator.php';
switch ($act) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Clean::text($_POST['name'] ?? '');
            $fuel_type = $_POST['fuel_type'] ?? 'diesel';
            $power = Clean::int($_POST['power'] ?? 0);
            $fuel_consumption = (float)$_POST['fuel_consumption'] ?? 0;
            if ($power < 1 || $power > 60) {
                $msg = "Потужність має бути в діапазоні 1..60";
            } elseif ($fuel_consumption <= 0) {
                $msg = "Вкажіть середній розхід";
            } else {
                addGenerator($pdo, $name, $fuel_type, $power, $fuel_consumption);
                header("Location: {$url_generator}&act=list");
                exit;
            }
        }
		$metatags = array('title'=>'Новий генератор','description'=>'Новий генератор','page'=>'newgenerator');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator&act=list"><i class="fi fi-rr-angle-left"></i>Генератори</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Новий генератор</span>';
        $templates .= "<div class='block_white pre40'>
			" . (!empty($msg) ? "<div class='generator_error'>{$msg}</div>" : '') . "
			<form method='post'>
				Назва: <input type='text' name='name' required><br>
				Тип палива:
				<select name='fuel_type'>
					<option value='diesel'>Дизель</option>
					<option value='gasoline'>Бензин</option>
					<option value='gas'>Газ</option>
				</select><br>
				Потужність (кВт 1-60): <input type='number' name='power' min='1' max='60' required><br>
				Середній розхід (л/год): <input type='text' name='fuel_consumption' required placeholder='наприклад 2.9'><br>
				<button type='submit'>Додати</button>
			</form>
			</div>
		";
		break;
    case 'edit':
        $id = Clean::int($_GET['id'] ?? 0);
        $gen = getGenerator($pdo, $id);
        if(!$gen){
            $templates .= "Генератор не знайдено";
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = Clean::text($_POST['name'] ?? '');
            $fuel_type = $_POST['fuel_type'] ?? $gen['fuel_type'];
            $power = Clean::int($_POST['power'] ?? $gen['power']);
            $fuel_consumption = (float)$_POST['fuel_consumption'] ?? $gen['fuel_consumption'];
            updateGenerator($pdo, $id, $name, $fuel_type, $power, $fuel_consumption);
            header("Location: {$url_generator}&act=list");
            exit;
        }
		$metatags = array('title'=>'Новий генератор','description'=>'Новий генератор','page'=>'newgenerator');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator&act=list"><i class="fi fi-rr-angle-left"></i>Генератори</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Редагувати генератор ' . $gen['name'] . '</span>';
        $templates .= "<div class='block_white pre40'>
            " . (!empty($msg) ? "<div class='generator_error'>{$msg}</div>" : '') . "
            <form method='post'>
                Назва: <input type='text' name='name' value='" . $gen['name'] . "' required><br>
                Тип палива:
                <select name='fuel_type'>
                    <option value='diesel' " . ($gen['fuel_type'] === 'diesel' ? 'selected' : '') . ">Дизель</option>
                    <option value='gasoline' " . ($gen['fuel_type'] === 'gasoline' ? 'selected' : '') . ">Бензин</option>
                    <option value='gas' " . ($gen['fuel_type'] === 'gas' ? 'selected' : '') . ">Газ</option>
                </select><br>
                Потужність (кВт): <input type='number' name='power' min='1' max='60' value='{$gen['power']}' required><br>
                Середній розхід (л/год): <input type='text' name='fuel_consumption' value='{$gen['fuel_consumption']}' required><br>
                <button type='submit'>Зберегти</button>
            </form><br>
			<form style='display:inline' method='post' action='{$url_generator}&act=del' onsubmit=\"return confirm('Видалити?');\">
                            <input type='hidden' name='id' value='{$gen['id']}'><button type='submit'>Видалити генератор</button>
                        </form> 
                        </div> 
        ";
		break;
    case 'del':
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $id = Clean::int($_POST['id'] ?? 0);
            if($id){
                deleteGenerator($pdo, $id);
            }
        }
        header("Location: {$url_generator}&act=list");
        exit;
		break;
    case 'addpaluvo':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fuel_type = $_POST['fuel_type'] ?? 'gasoline';
            $quantity = (float)$_POST['quantity'] ?? 0;
            if($quantity <= 0){
                $msg = "Кількість має бути більше 0";
            } else {
                addFuelStock($pdo, $fuel_type, $quantity);
                header("Location: {$url_generator}&act=listpaluvo");
                exit;
            }
        }
		$metatags = array('title'=>'Додати паливо','description'=>'Додати паливо','page'=>'addfuel');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator&act=listpaluvo"><i class="fi fi-rr-angle-left"></i>Паливо</a>';
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Додати паливо</span>';
        $templates .= "<div class='block_white pre40'>
			" . (!empty($msg) ? "<div class='generator_error'>{$msg}</div>" : '') . "
			<form method='post'>
				Тип палива:
				<select name='fuel_type'>
					<option value='diesel'>Дизель</option>
					<option value='gasoline'>Бензин</option>
					<option value='gas'>Газ</option>
				</select><br>
				Кількість (л): <input type='text' name='quantity' required style='width: 100px;'><br>
				<button type='submit'>Додати паливо</button>
			</form>
			</div>
		";
		break;
    case 'start':
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$generator_id = Clean::int($_POST['generator_id'] ?? 0);
			$location = Clean::text($_POST['location'] ?? '');
			$start_at = toDBDatetime($_POST['start_at'] ?? date('Y-m-d\TH:i'));
			$res = startGenerator($pdo, $generator_id, $location, $start_at);
			if ($res['ok']) {
				header("Location: {$url_generator}&act=active");
				exit;
			} else {
				$msg = $res['msg'];
				$templates .= "<div class='generator_error'>{$msg}</div>";
			}
		}
		$generators = listGeneratorsStart($pdo);
		$metatags = array('title'=>'Запуск генератора','description'=>'Запуск генератора','page'=>'start');
		$speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
		$speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Запуск генератора</span>';
		$templates .= "<div class='block_white pre40'>";
		$templates .= "<form method='post'>
			Генератор:
			<select name='generator_id' id='generator_id'>";
		foreach ($generators as $g) {
			$templates .= "<option value='{$g['id']}'>" . $g['name']. " (" . $types[$g['fuel_type']]['label'] . ", {$g['power']}кВт)</option>";
		}
		$templates .= "</select><br>
			(примітка): <input type='text' name='location'><br>
			Дата та час запуску: <input type='datetime-local' name='start_at' value='" . date('Y-m-d\TH:i') . "'><br>
			<br><button type='submit'>Запустити</button>
		</form>";
		$templates .= "</div>";
		break;
		case 'stop':
			$metatags = ['title'=>'Зупинити генератор','description'=>'','page'=>'stop'];
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$run_id = Clean::int($_POST['run_id'] ?? 0);
				$end_at = toDBDatetime($_POST['end_at'] ?? date('Y-m-d\TH:i'));
				$res = stopGenerator($pdo, $run_id, $end_at);
				if ($res['ok']) {
					$_SESSION['msg'] = "Генератор зупинено";
					header("Location: {$url_generator}");
					exit;
				}
				$msg = $res['msg'];
			}
			$active = listActiveRuns($pdo);
			$templates .= "<div class='block_white pre40'>";
			if (!empty($msg)) {
				$templates .= "<div class='generator_error'>{$msg}</div>";
			}
			if (empty($active)) {
				$templates .= "<p>Немає активних генераторів.</p>";
			} else {
				$templates .= "<form method='post'>
				Активний генератор:
				<select name='run_id'>";
				foreach ($active as $a) {
					$templates .= "<option value='{$a['id']}'>".$a['generator_name']." — {$a['start_at']}</option>";
				}
				$templates .= "</select><br>
				Дата та час зупинки:
				<input type='datetime-local' name='end_at' value='".date('Y-m-d\TH:i')."'><br>
				<button type='submit'>Зупинити</button>
				</form>";
			}
			$templates .= "</div>";
		break;
	case 'stop2':
		$metatags = array('title'=>'Зупинити генератор','description'=>'Зупинити генератор','page'=>'stop');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Зупинити генератор</span>';
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $run_id = Clean::int($_POST['run_id'] ?? 0);
            $end_at = toDBDatetime($_POST['end_at'] ?? date('Y-m-d\TH:i'));
            $total_available = $pdo->prepare("
					SELECT SUM(quantity) FROM fuel_stock WHERE fuel_type=?
				");
				$total_available->execute([$run['fuel_type']]);
				$total = (float)$total_available->fetchColumn();

				if ($total < $fuel_needed) {
					return ['ok'=>false, 'msg'=>'Недостатньо палива на складі'];
				}
			$res = stopGenerator($pdo, $run_id, $end_at);
            if($res['ok']){
                header("Location: {$url_generator}&act=active");
                exit;
            } else {
                $msg = $res['msg'];
            }
        }
        $active = listActiveRuns($pdo);
		$templates .= "<div class='block_white pre40'>";
        $templates .= (!empty($msg) ? "<div class='generator_error'>{$msg}</div>" : '');
		if (empty($active)) {
			$templates .= "<p>Немає активних генераторів.</p>";
		} else {
			$templates .= "<form method='post'>Активний генератор:	<select name='run_id'>";
			foreach ($active as $a) {
				$templates .= "<option value='{$a['id']}'>" . $a['generator_name'] . " — старт: {$a['start_at']}</option>";
			}
			$templates .= "
					</select><br>
					Дата та час зупинки: <input type='datetime-local' name='end_at' value='" . date('Y-m-d\TH:i') . "'><br>
					<br><button type='submit'>Зупинити</button>
				</form>
			";
		}
		$templates .= "</div>";
		break;
    case 'gena':
        $generator_id = Clean::int($_GET['id'] ?? 0);
        $gen = getGenerator($pdo, $generator_id);
        if(!$gen){ $templates .= "Генератор не знайдено"; exit; }
        $runs = runsByGenerator($pdo, $generator_id);
		$metatags = array('title'=>'Зупинити генератор','description'=>'Зупинити генератор','page'=>'stop');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator&act=list"><i class="fi fi-rr-angle-left"></i>Генератори</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Історія генератора: '.$gen['name'].'</span>';
        $templates .= "<table class='resp-tab' cellpadding=6 cellspacing=0 style='margin-top:10px;'><tr><th>ID</th><th>Паливо</th><th>Початок</th><th>Кінець</th><th>Годин</th><th>Витрачено (л)</th></tr>";
        $total_hours = 0; $total_fuel = 0;
        foreach($runs as $r){
            $templates .= "<tr>
                    <td>{$r['id']}</td>
                    <td>{$types[$r['fuel_type']]['label']} (Заправка № {$r['fuel_stock_id']})</td>
                    <td>{$r['start_at']}</td>
                    <td>".($r['end_at'] ?? '---')."</td>
                    <td>".formatHoursMinutes($r['hours_run'])."</td>
                    <td>{$r['fuel_used']}</td>
                  </tr>";
            $total_hours += (float)$r['hours_run'];
            $total_fuel += (float)$r['fuel_used'];
        }
		#показує 17 хв але то не правильно
        $templates .= "<tr style='font-weight:bold'><td colspan='4'>Всього</td><td>{$total_hours}</td><td>{$total_fuel}</td></tr>";
        $templates .= "</table>";
		break;
    case 'fuelhist':
		$metatags = array('title'=>'Історія палива','description'=>'Історія палива','page'=>'fuelhist');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator&act=listpaluvo"><i class="fi fi-rr-angle-left"></i>Паливо</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Історія палива</span>';
        $fuel_id = Clean::int($_GET['id'] ?? 0);
        $f = getFuelStock($pdo, $fuel_id);
        $hist = fuelHistory($pdo, $fuel_id);
        #$templates .= "<h3>Історія пакету ID {$fuel_id} ({$f['fuel_type']})</h3>";
        $templates .= "<table class='resp-tab' cellpadding=6 cellspacing=0><tr>
		<th>Дата</th><th>Дія</th>
		<th>Кількість (л)</th>
		<th>Використання палива</th></tr>";
        foreach($hist as $h){
            $templates .= "<tr><td>{$h['created_at']}</td>
			<td>{$h['action']}</td>
			<td>{$h['quantity']}</td><td>{$h['related_run']}</td></tr>";
        }
        $templates .= "</table>";
		break;
	case 'recalcgen':
		$id=Clean::int($_GET['id']);
		$res=recalcGeneratorFuel($pdo,$id);
		header("Location: {$url_generator}&act=gena&id=".$id);
		exit;
		break;
    case 'listpaluvo':
		$metatags = array('title'=>'Паливо','description'=>'Паливо','page'=>'listpaluvo');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Паливо</span>';
		$templates .= '
			<div class="pon-sfp-detail">
				<div class="dashboard_pon tag-filter">
				<a class="snmp_pon_rx" href="'.$url_generator.'&act=addpaluvo">Додати паливо</a>
				<a class="snmp_pon mlcp" href="'.$url_generator.'&act=active">Активні генератори</a>
				<a class="snmp_pon_fc" href="'.$url_generator.'&act=list">Генератори</a>
				<a class="snmp_pon_fc" href="'.$url_generator.'&act=recalc">Перерахувати склад</a>
				</div>
			</div>
		';
        $fuels = listFuelStock($pdo);
		if($fuels){
			$templates .= "<table class='resp-tab' cellpadding=6 cellspacing=0><tr>
			<th>ID</th>
			<th>Паливо</th>
			<th>Залишок</th>
			<th>Додано</th>
			<th>Дії</th></tr>";
			foreach($fuels as $f){
				$templates .= "</div>";
				$templates .= "<tr>
                    <td>{$f['id']}</td>
                    <td>{$types[$f['fuel_type']]['label']}</td>
                    <td>".round($f['quantity'],2)."</td>
                    <td>{$f['added_at']}</td>
                    <td>
                        <a href='{$url_generator}&act=fuelhist&id={$f['id']}'>Історія використання</a>
                    </td>
                  </tr>";
			}
		}else{
			$templates .= "Порожній склад";
		}
		$templates .= "</table>";
		break;
    case 'list':
		$metatags = array('title'=>'Генератори','description'=>'Генератори','page'=>'generatorlist');
        $speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<a class="brmhref" href="/?do=generator"><i class="fi fi-rr-angle-left"></i>Активні генератори</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Генератори</span>';
		$generators = listGenerators($pdo);
        $templates .= '
			<div class="pon-sfp-detail">
				<div class="dashboard_pon tag-filter">
				<a class="snmp_pon_vz" href="'.$url_generator.'&act=add">Додати генератор</a>
				<a class="snmp_pon_rx" href="'.$url_generator.'&act=addpaluvo">Додати паливо</a>
				<a class="snmp_pon_fc" href="'.$url_generator.'&act=listpaluvo">Історія палива</a>
				<a class="snmp_pon mlcp" href="'.$url_generator.'&act=active">Активні генератори</a>
				</div>
			</div>
		';
        $templates .= "<table class='resp-tab' cellpadding=6 cellspacing=0 style='margin-top:10px; width:100%'><tr><th>ID</th><th>Назва</th><th>Паливо</th><th>Потужність(кВт)</th><th>Розхід (л/год)</th><th>Додано</th><th>Дії</th></tr>";
        foreach($generators as $g){
            $templates .= "<tr>
                    <td>{$g['id']}</td>
                    <td>{$g['name']}</td>
                    <td>{$types[$g['fuel_type']]['label']}</td>
                    <td>{$g['power']}</td>
                    <td>{$g['fuel_consumption']}</td>
                    <td>{$g['added_at']}</td>
                    <td>
                        <a href='{$url_generator}&act=edit&id={$g['id']}'>Редагувати</a> |
                        <a href='{$url_generator}&act=gena&id={$g['id']}'>Історія</a>
                        <a href='{$url_generator}&act=recalcgen&id={$g['id']}'>Перерахувати витрати</a>
                    </td>
                  </tr>";
        }
        $templates .= "</table>";
		break;
    default:
		$metatags = array('title'=>'Активні генератори','description'=>'Активні генератори','page'=>'generator');
		$templates_main = '';
		$totals = totalFuelByType($pdo);
        $active = listActiveRuns($pdo);
		$speedbar .= '<a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>' . $lang['main'] . '</a>';
        $speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Активні генератори</span>';
        $templates_menu .= '
			<a class="menu-sub gena-text" href="'.$url_generator.'&act=start"><img class="gena-img" src="../style/img/gena-start.png">Запустити</a>
			<a class="menu-sub gena-text" href="'.$url_generator.'&act=list"><img class="gena-img" src="../style/img/gena-all.png">Генератори</a>
			<a class="menu-sub gena-text" href="'.$url_generator.'&act=stop"><img class="gena-img" src="../style/img/gena-stop.png">Зупинити</a>
			<a class="menu-sub gena-text" href="'.$url_generator.'&act=listpaluvo"><img class="gena-img" src="../style/img/gena-fuel.png">Паливо</a>
		';	
		if(empty($active)){ 
			$templates_main .= "<p>Немає активних запусків.</p>";
		}else{
			$templates_main .= "<table class='resp-tab' cellpadding=6 cellspacing=0><tr><th>Генератор</th><th>Початок</th><th>Місце</th><th>Генерування</th><th>Витрати палива</th><th>Дія</th></tr>";
			foreach($active as $a){
				$start = new DateTime($a['start_at']);
				$now = new DateTime();
				$hours_now = round(($now->getTimestamp() - $start->getTimestamp())/3600, 2);
				$fuel_estimated = round($hours_now * (float)$a['fuel_consumption'], 2);
				$over = $fuel_estimated > $a['fuel_packet_quantity'];
				$color = $over ? " style='color:red; font-weight:bold'" : "";
				$templates_main .= "<tr>
						<td><a href='{$url_generator}&act=gena&id={$a['generator_id']}'>".$a['generator_name']."</a></td>
						<td><span class='signal2'> {$a['start_at']}</span></td>
						<td>".$a['location']."</td>
						<td>{$hours_now} год</td>
						<td {$color}>{$fuel_estimated} л (залишок: ".round($a['fuel_packet_quantity'],2)."л)</td>
						<td> <a class='panel_house rr_1'  href='{$url_generator}&act=stop'>Зупинити</a></td>
					  </tr>";
			}
			$templates_main .= "</table>";
		}
		$azs_bak = "<div class='fuel_back'><table cellpadding='10'><tr>";
		foreach($types as $k => $info){
			$val = isset($totals[$k]) ? round($totals[$k],2) : 0.00;
			if($val){
			$azs_bak .= "<td style='text-align:center;padding: 10px;'>
							<img src='{$info['img']}' alt='{$info['label']}' style='width:40px;height:auto;display:block;margin:0 auto;'>
							<div style='margin-top:5px;font-weight:bold;'>{$val}</div>
						  </td>";
			}
		}
		$azs_bak .= "</tr></table></div>";		
		$templates .= '<div class="container">
			<div class="menu_olt_left pre20">
				' . $azs_bak . '
				' . $templates_menu . '
			</div>
			<div class="right-column pre80">
				' . $templates_main . '
			</div>
		</div>';
   break;
}
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}', '<div class="mainadmin">
<div id="onu-speedbar">' . $speedbar . '</div>' . $templates . '');
$tpl->compile('content');
$tpl->clear();
?>
