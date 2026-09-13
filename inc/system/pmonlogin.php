<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if (isset($USER['class']) && $USER['class']>=4 && $access->get('setup')){
	if($act=='del' && isset($_GET['ip'])){
		$ip = isset($_GET['ip']) ? $_GET['ip'] : '';
		if (filter_var($ip, FILTER_VALIDATE_IP)) {
			$db->query("DELETE FROM login_attempts WHERE ip_address = '{$ip}'");
			$go->go('/?do=pmonlogin');
			exit;
		}
	}	
	if($act=='ban' && isset($_GET['ip'])){
		$ip = isset($_GET['ip']) ? $_GET['ip'] : '';
		if (filter_var($ip, FILTER_VALIDATE_IP)) {
			$db->query("DELETE FROM login_attempts WHERE ip_address = '{$ip}'");
			$db->query("INSERT INTO block_ip (ip_address, userid, types) VALUES ('{$ip}','".$USER['id']."','user_block')");
			$go->go('/?do=pmonlogin');
			exit;
		}
	}
	$speedbar = '';
	$templates = '';
	$access_denied_array = array();
	$sql_access_denied = $db->SimpleWhile("SELECT * from  login_attempts");
	if(isset($sql_access_denied) && count($sql_access_denied)>0){
		foreach($sql_access_denied as $idaccess => $data_denied){
			$access_denied_array[$data_denied['ip_address']][$data_denied['id']]['added'] = $data_denied['last_attempt'];
		}
	}
	$metatags = array('title'=>'Захист від підбору пароля до системи','description'=>'Захист від підбору пароля до системи','page'=>'pmonlogin');
	$speedbar .='<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
				<a class="brmhref" href="/?do=operator"><i class="fi fi-rr-angle-left"></i>Обслуговування</a>';
	$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Захист від підбору пароля до системи</span>';		
	$templates .= '<table class="resp-tab"><thead><tr>
		<th width="15%">IP</th>
		<th width="10%">Керування</th>			
		<th width="10%">Чорний список</th>			
		<th width="15%">Кількість спроб</th>			
		<th>Дата авторизації</th></tr></thead><tbody>';
	if(isset($access_denied_array) && count($access_denied_array)>0){
		foreach($access_denied_array as $id_access => $access_value){
		$count_access_denied = count($access_value);
		$templates .= '<tr '.($count_access_denied==5 ? 'class="access_denied"' : '').'>
			<td class="td_url ip_access">'.$id_access.'</td>
			<td class="td_url">
				'.($count_access_denied == 5 ? '<a href="/?do=pmonlogin&act=del&ip='.trim($id_access).'">Розблокувати</a>'
				:
				'<a href="/?do=pmonlogin&act=del&ip='.trim($id_access).'">Видалити спроби</a>').'
			</td>
			<td><a href="/?do=pmonlogin&act=ban&ip='.trim($id_access).'">Додати</a></td>
			<td>'.$count_access_denied.'</td>
			<td class="time_access_denied">';
			foreach($access_value as $id_time => $time_value){
				$templates .= '<span><img src="../style/img/uptime.png">'.$time_value['added'].'</span>';
			}
			$templates .= '</td>
			</tr>';
		}
	}else{
		$templates .= '<tr><td colspan="4">'.$lang['empty'].'</td></tr>';
	}
	$templates .= '</table>';
	$tpl->load_template('main/main.tpl');
	$tpl->set('{block-main}','<div class="mainadmin"><div id="onu-speedbar">'.$speedbar.'</div>'.$templates.'</div>');
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->redirect('main');
}
?>