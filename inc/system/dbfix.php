<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$metatags = [
	'title'=>'DB Fix',
	'description'=>'DB Fix',
	'page'=>'dbfix'
];
function mksize($bytes) {
    if ($bytes < 1000 * 1024)
        return number_format($bytes / 1024, 2) . ' kB'; elseif ($bytes < 1000 * 1048576)
        return number_format($bytes / 1048576, 2) . ' MB';
    elseif ($bytes < 1000 * 1073741824)
        return number_format($bytes / 1073741824, 2) . ' GB';
    else
        return number_format($bytes / 1099511627776, 2) . ' TB';
}
$infos = '';
		$result = $db->SimpleWhile("SHOW TABLES FROM `".DBNAME."`");			
		foreach ($result as $name) {			
			$content .= "<option value=\"" . $name['Tables_in_'.DBNAME] . "\" selected>" . $name['Tables_in_'.DBNAME] . "</option>";
		}
		$infos .="<table class=mysql border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">" . "<form method=\"post\" action=\"\">" . "<tr><td><select name=\"datatable[]\" size=\"10\" multiple=\"multiple\" style=\"width:400px\">" . $content . "</select></td><td>" . "<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\">" . "<tr><td valign=\"top\"><input type=\"radio\" name=\"type\" value=\"Optimize\" checked></td><td>Оптимизация базы данных<br /><font class=\"small\">Производя оптимизацию базы данных, Вы уменьшаете её размер и соответственно с этим ускоряете её работу. Рекомендуется использовать данную функцию минимум один раз в неделю.</font></td></tr>" . "<tr><td valign=\"top\"><input type=\"radio\" name=\"type\" value=\"Repair\"></td><td>Ремонт базы данных<br /><font class=\"small\">При неожиданной остановке MySQL сервера, во время выполнения каких-либо действий, может произойти повреждение структуры таблиц базы данных, использование этой функции произведёт ремонт повреждённых таблиц.</font></td></tr></table>" . "</td></tr>" . "<input type=\"hidden\" name=\"op\" value=\"StatusDB\">" . "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Выполнить действие\"></td></tr></form></table>";
		if ($_POST ['type'] == "Optimize") {
			$result = $db->SimpleWhile( "SHOW TABLE STATUS FROM `".DBNAME. "`");
			$tables = array ();
			foreach ($result as $row) {
				$total = $row ['Data_length'] + $row ['Index_length'];
				$totaltotal += $total;
				$free = ($row ['Data_free']) ? $row ['Data_free'] : 0;
				$totalfree += $free;
				$i ++;
				$otitle = (! $free) ? "<font color=\"#FF0000\">Не нуждается</font>" : "<font color=\"#009900\">Оптимизирована</font>";
				$tables [] = $row['Name'];
				$content3 .= "<tr class=\"bgcolor1\"><td align=\"center\">" . $i . "</td><td>".$row['Name']."</td><td>".mksize($total)."</td><td align=\"center\">" . $otitle . "</td><td align=\"center\">" . mksize ( $free ) . "</td></tr>";
			}
			$db->query ( "OPTIMIZE TABLE " . implode ( ", ", $tables ) ) ;
			$infos .="<center><font class=\"option\">Оптимизация базы данных: " . DBNAME . "<br />Общий размер базы данных: " . mksize ( $totaltotal ) . "<br />Общие накладные расходы: " . mksize ( $totalfree ) . "<br /><br />" . "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr><td class=\"colhead\" align=\"center\">№</td><td class=\"colhead\">Таблица</td><td class=\"colhead\">Размер</td><td class=\"colhead\">Статус</td><td class=\"colhead\">Накладные расходы</td></tr>" . "" . $content3 . "</table>";
		} elseif ($_POST ['type'] == "Repair") {
			$result = $db->SimpleWhile ( "SHOW TABLE STATUS FROM `" . DBNAME . "`" ) ;
			foreach ($result as $row) {
				$total = $row ['Data_length'] + $row ['Index_length'];
				$totaltotal += $total;
				$i++;
				$rresult = $db->query ( "REPAIR TABLE " . $row['Name'] . "" ) ;
				$otitle = (! $rresult) ? "<font color=\"#FF0000\">Ошибка</font>" : "<font color=\"#009900\">OK</font>";
				$content4 .="<tr class=\"bgcolor1\"><td align=\"center\">" . $i . "</td><td>" . $row['Name'] . "</td><td>" . mksize ( $total ) . "</td><td align=\"center\">" . $otitle . "</td></tr>";
			}
			$infos .="<center><font class=\"option\">Ремонт базы данных: " . DBNAME . "<br />Общий размер базы данных: " . mksize ( $totaltotal ) . "<br /><br />" . "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr><td class=\"colhead\" align=\"center\">№</td><td class=\"colhead\">Таблица</td><td class=\"colhead\">Размер</td><td class=\"colhead\">Статус</td></tr>" . "" . $content4 . "</table>";
		}

$result .= '<div class="nav-fiber p10"><form action="/?do=test" method="post"><label for="description">Message:</label><textarea id="message" name="message"></textarea><br><input type="submit" value="Send"></form></div>';
$result ='<div id="onu-speedbar"><a class="brmhref" href="/"><i class="fi fi-rr-apps"></i>'.$lang['main'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Test Telegram</span></div>'.$infos.'';
$tpl->load_template('main/main.tpl');
$tpl->set('{block-main}','<div class="mainadmin">'.$result.'</div>');
$tpl->compile('content');
$tpl->clear();
?>