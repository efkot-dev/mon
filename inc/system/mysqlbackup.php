<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if ($access->get('setup')) {
	if(isset($_GET['n'])) {
		$fileName = $_GET['n'];      
		$filePath = ROOT_DIR . '/file/backup/backup_'.$fileName.'.sql';
		if(isset($_GET['act'])) {
			if($_GET['act'] == 'x') {
				if(file_exists($filePath)) {
					@unlink($filePath);
				}
			} elseif($_GET['act'] == 'd') {
				if(file_exists($filePath) && is_readable($filePath)) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="[pmon_backup]_' . $fileName . '.sql"');
					readfile($filePath);
					exit;
				} else {
					header("HTTP/1.0 404 Not Found");
					echo "Файл не знайдено";
					exit;
				}
			}
		}
	} 
	$metatags = [
		'title'=>'Dumping Data in SQL Format with mysqldump',
		'description'=>'Dumping Data in SQL Format with mysqldump',
		'page'=>'mysqlbackup'
	];
	$dumping_file = findDumping();
	$page = '';
	$page .= '<div class="list_dump_panel">';
		$page .= '<span onclick="sendpoller();">Запустити процес</span>';
	$page .= '</div>';
	if(isset($dumping_file) && count($dumping_file)>0){
		$page .= '<div class="list_dump">';
		foreach($dumping_file as $fid => $file_data){
			$page .= '<div class="db openPopup" data-popup-id="backup_panel_'.md5($file_data).'">';
			$page .= '<img src="../style/img/mysql_db.png"><span>Бекап бази за:<br>'.$file_data.'</span>';
			$page .= '</div>';
		}		
		foreach($dumping_file as $fid => $file_data){
			$page .= '
			<div id="backup_panel_'.md5($file_data).'" class="popupContainer">
				<div class="popupContent">
					<div class="panel_mysql">
						<a href="/?do=mysqlbackup&n='.$file_data.'&act=d" class="color_green">Завантажити</a>
						<a href="/?do=mysqlbackup&n='.$file_data.'&act=x" class="color_red">Видалити</a>
					</div>
				</div>
			</div>';
		}
		$page .= '</div>';
	}
	$result ='<div id="onu-speedbar"><a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a><a class="brmhref" href="/?do=operator"><i class="fi fi-rr-angle-left"></i>Обслуговування</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Dumping Data in SQL Format with mysqldump</span>
	</div>'.$page.'';
	$tpl->load_template('main/main.tpl');
	$content_page = '
	<div class="mainadmin">
		'.$result.'
	</div>';
	$tpl->set('{block-main}',$content_page);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');	
}
?>