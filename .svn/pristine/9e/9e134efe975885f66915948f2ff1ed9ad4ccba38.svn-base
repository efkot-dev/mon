<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$resp = '';
switch($act){
	case 'list': 
		$resp .='<table class="resp-tab none"><thead><tr>
			<th>Назва <a href="/?do=detail&act=olt&page=note&id='.$id.'&view=add"><img src="../style/img/add.png" style="vertical-align: sub;"></a></th>
			<th width="10%">'.$lang['autor'].'</th>
			<th width="15%">'.$lang['added'].'</th></tr></thead><tbody>';
		$sql = "SELECT * FROM notes WHERE deviceid = {$id}";
		$results = $db->SimpleWhile($sql);
		if (isset($results) && count($results) > 0) {
			foreach ($results as $row) {
				$resp .='
				<tr>
				<td class="td_name td_url cuttext">
					<a href="/?do=detail&act=olt&page=note&id='.$id.'&view=detail&notes='.$row['id'].'">'.$row['title'].'</a>
					<span class="text">'.cut_text($row['message'],100).'</span></td>
				<td class="td_name">'.$row['userid'].'</td>
				<td class="td_name">'.$row['added'].'</td>
				</tr>
				';
			}
		}else{
			$resp .='
			<tr>
			<td colspan="3">'.$lang['empty'].'</td>
			</tr>
			';
		}
		echo $resp;
	break;
}
?>