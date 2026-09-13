<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$directory = ROOT_DIR . '/file/backup/';
if($access->get('view_backup')){
	$id = (isset($_GET['id']) ? intval($_GET['id']) : null);
	if(isset($_GET['t']) && isset($id) && $id!=false) {
		$fileName = (isset($_GET['n']) ? intval($_GET['n']) : '');
		$filePath = ROOT_DIR . '/file/backup/olt_'.$id.'_time_'.$fileName.'.backup';
		if($_GET['t'] == 'x') {
			if(file_exists($filePath)) {
				@unlink($filePath);
			}
		} elseif($_GET['t'] == 'd') {
			if(file_exists($filePath) && is_readable($filePath)) {
				header('Content-Disposition: attachment; filename="[pmon_backup]_' . $fileName . '.backup"');
				readfile($filePath);
				exit;
			} else {
				header("HTTP/1.0 404 Not Found");
				echo "Файл не знайдено";
				exit;
			}
		}
	}
	$pattern = $directory . 'olt_' . $id . '_time_*.backup';
	$files = glob($pattern);
	$fileNames = [];
	foreach ($files as $file) {
		$fileName = basename($file);
		$fileNames[] = $fileName;
	}
	$list_backup = [];
	if (isset($fileNames) && count($fileNames) > 0) {
		foreach ($fileNames as $fileName) {
			$pattern = '/olt_' . preg_quote($id, '/') . '_time_(\d+)\.backup/';
			if (preg_match($pattern, $fileName, $matches)) {
				$timestamp = $matches[1];
				$date = date('Y-m-d H:i:s', $timestamp);				
				$filePath = $directory . $fileName;
				$fileSize = file_exists($filePath) ? filesize($filePath) : 0;
				$formattedSize = formatSizeUnits($fileSize);
				$list_backup[md5($date)] = [
					'id' => $id,'file' => $fileName,'date' => $date,'size' => $formattedSize
				];
			}
		}
		usort($list_backup, 'compareDates');		
		if (!empty($list_backup)) {
			$tplRes .= '<table class="resp-tab" width="100%"><thead><tr><th width="15%">' . $lang['file_added'] . '</th><th>' . $lang['file_name'] . '</th><th width="10%">' . $lang['file_size'] . '</th><th width="10%">' . $lang['file_load'] . '</th><th width="10%">' . $lang['file_delet'] . '</th></tr></thead><tbody>';
			foreach ($list_backup as $idmd => $filebackup) {
				$chars = ['olt_'.$id.'_time_', '.backup', '>', '<'];
				$filename = str_replace($chars, '', $filebackup['file']);
				$tplRes .= '<tr>
					<td class="td_url"><span class="signal2">' . $filebackup['date'] . '</span></td>
					<td class="td_names">' . md5($filename) . '.backup</td>
					<td class="td_url">' . $filebackup['size'] . '</td>
					<td>';
				if ($access->get('download_backup')) {
					$tplRes .= '<a href="/?do=detail&act=olt&page=backup&id=' . $id . '&t=d&n=' . $filename . '">
						<img src="../style/img/download.png">
					</a>';
				}
				$tplRes .= '</td><td>';
				if ($access->get('delet_backup')) {
					$tplRes .= '<a href="/?do=detail&act=olt&page=backup&id=' . $id . '&t=x&n=' . $filename . '">
						<img src="../style/img/close.png">
					</a>';
				}
				$tplRes .= '</td>
				</tr>';
			}
			$tplRes .= '</tbody></table>';
		}
	} else {
		$tpl->load_template('pole_empty.tpl');
		$tpl->compile('rsyslog');
		$tpl->clear();
		$tplRes .= $tpl->result['rsyslog'];
	}
}
?>