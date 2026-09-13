<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ENGINE_DIR.'functions/functions_abills.php';
require ENGINE_DIR.'functions/taskman.php';
$current_money = (isset($confPMon['CURRENT_MONEY']) && !empty($confPMon['CURRENT_MONEY']) ? $confPMon['CURRENT_MONEY']:'грн');
$speedbar = '';
$speedbar_block = '';
$content = '';
$content_head = '';
$taskman_clock = date('Y-m-d H:i:s');
switch($act){
	case 'savetaskman':
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$deadline = isset($_POST['deadline']) ? Clean::text($_POST['deadline']) : null;		
			if(isset($deadline) && $deadline){
				$deadline = str_replace("T", " ", $deadline);
				$deadline = $deadline.':00';
			}else{
				$deadline = $taskman_clock;
			}
			$money = isset($_POST['money']) ? Clean::text($_POST['money']) : 0;		
			$dog_number = isset($_POST['dog_number']) ? Clean::text($_POST['dog_number']) : 0;		
			$payment_type = isset($_POST['type_paid']) ? Clean::int($_POST['type_paid']) : 0;		
			$clientid = isset($_POST['clientid']) ? Clean::text($_POST['clientid']) : null;		
			$client_mobil = isset($_POST['client_mobil']) ? Clean::text($_POST['client_mobil']) : null;		
			$client_pib = isset($_POST['client_pib']) ? Clean::text($_POST['client_pib']) : null;			
			$client_street = isset($_POST['client_street']) ? Clean::text($_POST['client_street']) : null;		
			$client_house = isset($_POST['client_num_house']) ? Clean::text($_POST['client_num_house']) : null;			
			$story = isset($_POST['story']) ? Clean::text($_POST['story']) : null;	
			$listworker = isset($_POST['listworker']) ? Clean::int($_POST['listworker']) : null;
			$location = isset($_POST['location']) ? Clean::int($_POST['location']) : null;
			if(isset($location) && isset($listworker) && isset($story) && isset($client_pib) && isset($deadline) && empty($clientid)){
				$location = $db->Fast('location','*',['id'=>$location]);
				$db->SQLinsert('task_list_client',['client_locationname'=>$location['name'],'client_mobil'=>$client_mobil,'client_pib'=>$client_pib,'added'=>$taskman_clock,'client_street'=>$client_street,'client_house'=>$client_house]);
				$clientid = $db->getInsertId();
			}	
			if(isset($clientid) && $clientid>0){
				$sql_insert = array(
				'payment_type'=>$payment_type,
				'story'=>$story,
				'clientid'=>$clientid,
				'locationid'=>$location['id'],
				'typesworker'=>$listworker,'status'=>1,
				'operator'=>$USER['id'],
				'planned_at'=>$deadline,
				'created_at'=>$taskman_clock);
				if(isset($dog_number) && !empty($dog_number)){
					$sql_insert['dogovir'] = $dog_number;
				}			
				if(isset($money) && !empty($money)){
					$sql_insert['money'] = $money;
				}
				$db->SQLinsert('task_list',$sql_insert);
			}
		}
		$go->go('/?do=taskman&act=list');	
	break;	
	// ABILLS INTEGRATION
	case 'abills_getstreet':     
        $result = get_data_street_abills();
        echo json_encode($result);
		die;
        break;  
    case 'abills_getinuser':        
		$uid = isset($_POST['uid']) ? Clean::text($_POST['uid']) : 0;
		if ($uid > 0) {
            $sender['uid'] = $uid;
        }
		$result = get_data_user_uid_abills($integration,$sender);

		break;  
    case 'abills_gethouse':     
        $id = isset($_POST['id']) ? Clean::text($_POST['id']) : 0;    
        $result = get_data_house_abills($id);
        echo json_encode($result);
		die;
        break;  
    case 'abills_getuser':     
        $sender = array();
        $streetid = isset($_POST['streetid']) ? Clean::text($_POST['streetid']) : 0;
        if ($streetid > 0) {
            $sender['streetid'] = $streetid;
        }        
        $houseid = isset($_POST['houseid']) ? Clean::text($_POST['houseid']) : 0;
        if ($houseid > 0) {
            $sender['houseid'] = $houseid;
        }    
        $result = get_data_nomer_abills($integration, $sender);
		$usersString = $result[0]['users'];
		$usersArray = explode(',', $usersString);		
		echo'<br><table class="resp-tab list-onu-olt"><thead><tr>
			<th class="mob_w10" width="4%">UID</th>
			<th width="4%">Статус</th>
			<th width="5%">Пристрій</th>
			<th width="15%">П.І.Б</th>
			<th width="7%">Тариф</th>
			<th width="7%">monthFee</th>
			<th width="10%">Моб</th>
			<th width="10%">Місто</th>
			<th width="10%">Вулиця</th>
			<th width="5%">Будинок</th>
			<th width="5%">Квартира</th>
			<th></th>
		</tr></thead><tbody>';		
		foreach ($usersArray as $user) {
			$user = trim($user);
			if (!empty($user)) {
				$sender['uid'] = $user;
				$tmp_usr = get_data_user_uid_abills($sender);
				$pmon_usr = pmon_get_data_user_uid($user,$tmp_usr);
				$billing = get_full_data_user_uid_abills($sender);
				$type = 'n/a';
				$get_status = 'n/a';
				$tr_status = '';
				if(isset($pmon_usr['device_type']) && $pmon_usr['device_type']=='onu'){
					$type = '<a href="/">
					'.($pmon_usr['status']==2 ? '<font color="red">':'').'
					'.$pmon_usr['onumac'].''.($pmon_usr['status']==2?'</font>':'').'</a>';
					$get_status = ($pmon_usr['status']==2 ? reason_onu(2,$pmon_usr['reason']) : '<img src="../style/img/online.png">');
					$tr_status = " class='".($pmon_usr['status']==2 ? 'down':'up')."'";
				}
				echo'
				'.(isset($pmon_usr['onumac']) && !empty($pmon_usr['onumac']) ? '
				<input name="mac_'.$user.'" id="mac_'.$user.'" type="hidden" value="'.$pmon_usr['onumac'].'">
				': '').'
				<input name="mob_'.$user.'" id="mob_'.$user.'" type="hidden" value="'.$tmp_usr['myMobile'].'">
				<input name="pib_'.$user.'" id="pib_'.$user.'" type="hidden" value="'.$tmp_usr['fio'].'">
				<input name="build_'.$user.'" id="build_'.$user.'" type="hidden" value="'.$tmp_usr['addressBuild'].'">
				<input name="kv_'.$user.'" id="kv_'.$user.'" type="hidden" value="'.$tmp_usr['addressFlat'].'">
				<tr'.$tr_status.' id="form_'.$user.'">
				<td>'.$user.'</td>
				<td class="status">'.$get_status.'</td>
				<td class="td_url">'.$type.'</td>
				<td class="td_names">'.$tmp_usr['fio'].'</td>
				<td>'.$billing['tpName'].'</td>
				<td>'.$billing['monthFee'].'</td>
				<td>'.$tmp_usr['myMobile'].'</td>
				<td>'.$tmp_usr['addressDistrict'].'</td>
				<td>'.$tmp_usr['addressStreet'].'</td>
				<td>'.$tmp_usr['addressBuild'].'</td>
				<td>'.$tmp_usr['addressFlat'].'</td>
				<td><a href="#" class="task_reger_abills" onclick="sendreger('.$user.')">Реєструємо</a></td>				
				</tr>';
			}
		}	
		echo'</tbody></table>';
		die;
        break; 
	case 'next':        
		$taskman_js = '<script src="../style/js/taskman.js?do='.md5(time()).'"></script>';
		$content = '
		<div class="search-page">
		<div class="search-block" id="filtersForm">
		<form>			
			<div class="item-search block-select">			
				<div class="block" style="width: 300px;">
					<h3>Назва вулиці</h3>
					<div>
						<input type="text" name="street_name" id="street_name" class="input1" type="text">
						<input name="street_id" type="hidden" id="street_id">
					</div>
				</div>
				<div class="block" style="width: 300px;">
					<h3>Номер будинку</h3>
					<div>
						<input name="house_name" class="input1" type="text" id="house_name">
						<input name="house_id" type="hidden" id="house_id">
					</div>
				</div>				
				<div style="width:10px;border-left: 1px solid #eee;"></div>
				<div class="block" style="width: 200px;">
					<h3>Логін</h3>
					<div>
						<input name="house_name" class="input1" type="text" id="house_name">
					</div>
				</div>				
				<div class="block" style="width: 200px;">
					<h3>Платіжний</h3>
					<div>
						<input name="house_name" class="input1" type="text" id="house_name">
					</div>
				</div>
			</div>
		</form>
		</div>
		</div>
		<div id="list_users"></div>
		' . $taskman_js;
		break;  
	case 'add': 	
		$sql_task_pager = $db->Simple("SELECT count(id) as count FROM task_list_worker");
		$metatags = [
			'title'=>$lang['taskman_new'],
			'description'=>$lang['taskman_new'],
			'page'=>'addtaskman'
		];
		$user_house_id = isset($_POST['user_house_id']) ? Clean::int($_POST['user_house_id']) : 0;	
		$street_name_billing = '';
		$location_name = '';
		$post_pib = '';
		$post_mob_tel = '';
		$post_build = '';
		$user_street_id = isset($_POST['user_street_id']) ? Clean::int($_POST['user_street_id']) : 0;	
		if(is_valid_id($user_street_id)){
			$sql_street_name = $db->Simple("SELECT * FROM location_street WHERE billing_streetid = ".$user_street_id." LIMIT 1");
			$street_name_billing = 'value="'.$sql_street_name['name'].'"';
		}
		$user_mob = isset($_POST['user_mob']) ? Clean::text($_POST['user_mob']) : null;	
		if(isset($user_mob)){
			$post_mob_tel = 'value="'.$user_mob.'"';
		}
		$user_pib = isset($_POST['user_pib']) ? Clean::text($_POST['user_pib']) : null;	
		if(isset($user_pib)){
			$post_pib = 'value="'.$user_pib.'"';
		}
		$user_build = isset($_POST['user_build']) ? Clean::text($_POST['user_build']) : null;	
		$user_kv = isset($_POST['user_kv']) ? Clean::text($_POST['user_kv']) : null;
		if(isset($user_kv)){
			$post_build = 'value="'.$user_kv.'"';
		}		
		$user_mac = isset($_POST['user_mac']) ? Clean::text($_POST['user_mac']) : null;	
		if(isset($sql_task_pager) && $sql_task_pager['count']>0){	
			$listworker = getListWorker();
			foreach($listworker as $worker){
				$listworker .= '<option value="'.$worker['id'].'">'.$worker['name'].'</option>';
			}
			$location = getListLocations();
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'" '.
				(isset($sql_street_name['locationid']) && $sql_street_name['locationid']==$loc['id'] ? "selected" : "")
				.'>'.$loc['name'].'</option>';
			}		
			$speedbar_block .= '
				<div id="onu-speedbar">
					<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>'.$lang['taskman_main_task'].'</a>
					<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['taskman_new'].'</span>
				</div>';
			$content .= '
			<form action="/?do=taskman" method="post">
			<input name="act" type="hidden" value="savetaskman">
			<div class="main_taskman">';
			$paidworker = '
				<option value="1">'.$lang['type_paid'].'</option>
				<option value="2">'.$lang['type_free_charge'].'</option>
				<option value="3">'.$lang['type_service'].'</option>
				<option value="4">'.$lang['type_remont'].'</option>
			';
			$pole = '<div id="moneyField" class="hidden">
			<label for="money" class="labels">'.$lang['input_suma'].': </label>
			<input type="text" name="money"></div><div id="dogNumberField" class="hidden">
			<label class="labels" for="dog_number">'.$lang['input_dogovir'].': </label>
			<input type="text" name="dog_number"></div>';
			/*
			$content .= formpage([
				'img'=>'m1.png','name'=>$lang['paid_task'],'descr'=>$lang['paid_task_descr'],
				'class'=>'main_w100',	
				'pole'=>'
				<select class="select" name="type_paid" id="type_paid" onchange="showHideFields()" required>
				<option value="0"></option>
				'.$paidworker.'
				</select>'.$pole
			]);
			*/
			$content .= formpage([
				'img'=>'m1.png','name'=>$lang['listworker'],'descr'=>$lang['listworkerdescr'],'class'=>'main_w100',	
				'pole'=>'
				<select class="select" name="listworker" id="listworker">
				<option value="0"></option>
				'.$listworker.'
				</select>'
			]). 
			formpage([
				'img'=>'m11.png','name'=>$lang['listworkertime'],'descr'=>$lang['listworkertimedescr'],'class'=>'main_w100','pole'=>'<input type="datetime-local" name="deadline" id="deadline" required class="css-input">'
			]).
			formpage([
				'img'=>'m6.png','name'=>$lang['location'],'descr'=>$lang['getlocation'],'class'=>'main_w100',
				'pole'=>'
				<select class="select" name="location" id="location" required>
				<option value="0"></option>
				'.$listlocation.'
				</select>'
			]).
			formpage([
				'class'=>'main_w100','img'=>'m6.png','name'=>'Вулиця','descr'=>"Вулиця клієнта",
				'pole'=>'<input name="client_street" class="input1" type="text" style="width:50%;" '.$street_name_billing.'>'
			]).
			formpage([
				'class'=>'main_w100','img'=>'m6.png','name'=>'Номер будинку',
				'descr'=>"Номер будинку: 24а кв.1, 23/1, 23",
				'pole'=>'<input name="client_num_house" class="input1" type="text" style="width:20%;" '.$post_build.'>'
			]).
			formpage([
				'class'=>'main_w100','img'=>'addconnect.png','name'=>''.$lang['taskman_us_pib'].'.',
				'descr'=>"Інформація про клієнта ",
				'pole'=>'<input name="client_pib" class="input1" type="text" '.$post_pib.'>'
			]).
			formpage([
				'class'=>'main_w100','img'=>'addconnect.png','name'=>'Контакті номери',
				'descr'=>"Номери клієнта: 0993119999, 0500502222",
				'pole'=>'<input name="client_mobil" class="input1" type="text" '.$post_mob_tel.'>'
			]).
			formpage([
				'class'=>'main_w100','img'=>'addconnect.png','name'=>$lang['taskman_opis'],'descr'=>"",
				'pole'=>'<textarea class="story" id="story" name="story"></textarea>'
			]);
			$content .='<input type="submit" value="'.$lang['save'].'"></div></form>';
		}else{
			$go->go('/?do=taskman&act=listworker');
		}	
	break;		
	case 'updatetask': 
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$user_update = [];	
			$task_update = [];	
			$deadline = isset($_POST['deadline']) ? Clean::text($_POST['deadline']) : null;		
			$taskid = isset($_POST['taskid']) ? Clean::int($_POST['taskid']) : null;
			if($taskid>0){
				$taskview = $db->Fast('task_list','*',['id'=>$taskid]);
				$taskuser = $db->Fast('task_list_client','*',['id'=>$taskview['clientid']]);
				if(empty($taskview['id'])){
					$go->go('/?do=taskmant');
				}
			}		
			if(isset($deadline) && $deadline){
				$deadline = str_replace("T", " ", $deadline);
				$deadline = $deadline.':00';
			}else{
				$deadline = $taskman_clock;
			}
			$task_update['planned_at'] = $deadline;
			$location = isset($_POST['location']) ? Clean::int($_POST['location']) : null;
			if(isset($location)){
				$task_update['locationid'] = $location;
				$user_update['client_locationid'] = $location;
			}		
			$story = isset($_POST['story']) ? Clean::text($_POST['story']) : null;
			if(isset($story)){
				$task_update['story'] = $story;
			}					
			$client_street = isset($_POST['client_street']) ? Clean::text($_POST['client_street']) : null;
			if(isset($client_street)){
				$user_update['client_street'] = $client_street;
			}
			$client_house = isset($_POST['client_num_house']) ? Clean::text($_POST['client_num_house']) : null;		
			if(isset($client_house)){
				$user_update['client_house'] = $client_house;
			}		
			$client_pib = isset($_POST['client_pib']) ? Clean::text($_POST['client_pib']) : null;		
			if(isset($client_pib)){
				$user_update['client_pib'] = $client_pib;
			}		
			$client_mobil = isset($_POST['client_mobil']) ? Clean::text($_POST['client_mobil']) : null;		
			if(isset($client_mobil)){
				$user_update['client_mobil'] = $client_mobil;
			}		
			$typesworker = isset($_POST['typesworker']) ? Clean::int($_POST['typesworker']) : null;
			if(isset($typesworker)){
				$task_update['typesworker'] = $typesworker;			
			}
			if(!empty($taskview['id'])){
				$db->SQLupdate('task_list_client',$user_update,['id'=>$taskuser['id']]);
				$db->SQLupdate('task_list',$task_update,['id'=>$taskview['id']]);
				$go->go('/?do=taskman&act=view&id='.$taskview['id']);
			}
		}
		$go->go('/?do=taskman&act=list');
	break;		
	case 'updatelistworker': 
		if($_SERVER['REQUEST_METHOD'] == 'POST'){	
			$name = isset($_POST['name']) ? Clean::text($_POST['name']) : null;
			$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
			if(isset($id) && $id>0 && isset($name) && $name){
				$task_list_worker = $db->Fast('task_list_worker','*',['id'=>$id]);
				if(isset($task_list_worker['id']) && !empty($task_list_worker['id']) ){
					$db->SQLupdate('task_list_worker',['name'=>$name],['id'=>$task_list_worker['id']]);
					if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
						$cacheManager->delete("task_list_worker");
					}
				}
			}
		}
		$go->go('/?do=taskman&act=listworker');	
	break;		
	case 'deltask': 	
		if(isset($USER['class']) && $USER['class'] >= 3 ){
			$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
			if(isset($id) && $id>0){
				$task_list = $db->Fast('task_list','*',['id'=>$id]);
				$db->query("DELETE FROM task_list WHERE id = '{$task_list['id']}'");
				$db->query("DELETE FROM task_list_comment WHERE taskid = '{$task_list['id']}'");
				$db->query("DELETE FROM task_list_vikonavci WHERE taskid = '{$task_list['id']}'");
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("task_list_worker");
				}
			}
		}
		$go->go('/?do=taskman&act=listworker');			
	break;		
	case 'deletlistworker': 	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
		if(isset($id) && $id>0){
			$task_list_worker = $db->Fast('task_list_worker','*',['id'=>$id]);
			if(isset($task_list_worker['id']) && !empty($task_list_worker['id']) ){
				$db->query("DELETE FROM task_list_worker WHERE id = '{$task_list_worker['id']}'");
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("task_list_worker");
				}
			}
		}
		$go->go('/?do=taskman&act=listworker');	
	break;		
	case 'savelistworker': 	
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$types = isset($_POST['types']) ? Clean::text($_POST['types']): null;
			$name = isset($_POST['name']) ? Clean::text($_POST['name']): null;
			if($types=='save' && isset($name) && !empty($name)){
				$db->SQLinsert('task_list_worker',['name'=>$name]);	
				if (isset($confPMon['CACHE']) && !empty($confPMon['CACHE']) && $confPMon['CACHE'] == 1) {
					$cacheManager->delete("task_list_worker");
				}			
			}
		}	
		$go->go('/?do=taskman&act=listworker');		
	break;		
	case 'listworker': 	
		$metatags = ['title'=>$lang['taskman_type_task'],'description'=>$lang['taskman_type_task'],'page'=>'listworker'];
		$speedbar .='<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>'.$lang['taskman_main_task'].'</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['taskman_type_task'].'</span>';
		$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
		$listworker = getListWorker();
		$content .= '<table class="resp-tab"><thead><tr><th width="60%">'.$lang['name'].'</th><th></th></tr></thead><tbody>';
		if(isset($listworker) && count($listworker) > 0){
			foreach($listworker as $ipid => $taskm){
				$content .= '<tr><td class="td_url"><a href="/?do=taskman&act=list">'.$taskm['name'].'</a></td><td><a class="panel_house rr_2" href="#" onclick="ajaxcore(\'edit_taskman_list\','.$taskm['id'].')">'.$lang['cfg'].'</a><a class="panel_house rr_1" href="/?do=taskman&act=deletlistworker&id='.$taskm['id'].'">'.$lang['delolt'].'</a></td></tr>';
			}
		}else{
			$content .= '<tr><td colspan="2">'.$lang['empty'].'</td></tr>';
		}
		$content .= '</table><div id="ajax"></div><div class="pole"><a href="#" onclick="ajaxcore(\'add_taskman_list\',1)" class="urlelelement">'.$lang['new_taskman'].'</a></div>';
	break;		
	case 'finishedtask':
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$sqlinsert = []; 	
			$sqlinserts = []; 	
			$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
			$vpered = isset($_POST['vpered']) ? Clean::text($_POST['vpered']): null;
			if(isset($id) && $id>0){
				$taskview = $db->Fast('task_list','*',['id'=>$id]);
				$comment = isset($_POST['comment']) ? Clean::text($_POST['comment']): null;
				if(!empty($taskview['id']) && isset($comment)){
					$sqlinsert['finish_at'] = date('Y-m-d H:i:s');
					$sqlinsert['status'] = 2;
					$sqlinsert['story'] = $taskview['story'].'[sql_hr]'.$comment.'[sql_hr]'.$lang['pidpus_taskman'].' '.$USER['username'];
					$db->SQLupdate('task_list',$sqlinsert,['id'=>$taskview['id']]);
				}
				if(isset($vpered) && $vpered=='on'){			
					if($taskview['payment_type']==1){					
						$sqlinserts['money'] = $taskview['money'];
						$sqlinserts['dogovir'] = 0;					
					}elseif($taskview['payment_type']==2){
						$sqlinserts['money'] = 0;
						$sqlinserts['dogovir'] = 0;		
					}elseif($taskview['payment_type']==3){
						$sqlinserts['money'] = 0;
						$sqlinserts['dogovir'] = $taskview['dogovir'];						
					}
					$sqlinserts['payment_type'] = $taskview['typesworker'];				
					$sqlinserts['added'] = date('Y-m-d H:i:s');
					$sqlinserts['taskid'] = $taskview['id'];
					$sqlinserts['userid'] = $USER['id'];
					$db->SQLinsert('task_money',$sqlinserts);
				}
			}
		}
		$go->go('/?do=taskman&act=list');
	break;		
	case 'delcomm': 	
		$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;	
		if(isset($id) && $id>0){
			$task_list_comment = $db->Fast('task_list_comment','*',['id'=>$id]);
			if(!empty($task_list_comment['id'])){
				$db->SQLdelete('task_list_comment',['id' =>$task_list_comment['id']]);
				$go->go('/?do=taskman&act=view&id='.$task_list_comment['taskid']);
			}
		}
		$go->go('/?do=taskman');
	break;		
	case 'view': 
		require MODULE_TASK.'view.php';
	break;	
	case 'updatepaidtask': 
		if($_SERVER['REQUEST_METHOD'] == 'POST'){	
			$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
			$sqlinsert = [];
			$dog_number = isset($_POST['dog_number']) ? Clean::int($_POST['dog_number']) : null;
			$type_paid = isset($_POST['type_paid']) ? Clean::int($_POST['type_paid']) : null;
			$money = isset($_POST['money']) ? Clean::text($_POST['money']) : null;
			if(isset($type_paid) && $type_paid>0){
				$taskview = $db->Fast('task_list','*',['id'=>$id]);
				if(!empty($taskview['id'])){				
					if($type_paid==1){
						$sqlinsert['payment_type'] = 1;
						$sqlinsert['money'] = $money;
						$sqlinsert['dogovir'] = 0;					
					}elseif($type_paid==2){
						$sqlinsert['payment_type'] = 2;
						$sqlinsert['money'] = 0;
						$sqlinsert['dogovir'] = 0;		
					}elseif($type_paid==3){
						$sqlinsert['payment_type'] = 3;
						$sqlinsert['money'] = 0;
						$sqlinsert['dogovir'] = $dog_number;						
					}
					if(isset($sqlinsert) && count($sqlinsert)>0){
						$db->SQLupdate('task_list',$sqlinsert,['id'=>$taskview['id']]);	
					}
					$go->go('?do=taskman&act=view&id='.$id);
				}			
			}
		}
		$go->go('/?do=taskman');
	break;		
	case 'savevikonavci': 
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$id = isset($_POST['id']) ? Clean::int($_POST['id']) : null;
			$list_users = getListUser();	
			$user_task = getListVikonavciArray($id);	
			$linr_tp_new = [];		
			if(isset($_POST['us']) && count($_POST['us'])>0 && isset($USER['class']) && $USER['class'] >= 3 ){
				foreach ($_POST['us'] as $line_id) {
					$line_id = (int)$line_id;
					$linr_tp_new[$line_id]['userid'] = $line_id;
				}
				if(isset($linr_tp_new) && count($linr_tp_new)>0){
					foreach ($linr_tp_new as $usid => $userid) {
						if(!isset($user_task[$userid['userid']]['userid'])) {
							$db->SQLinsert('task_list_vikonavci',[
							'userid' => $userid['userid'],
							'username' => $list_users[$userid['userid']]['username'],
							'name' => (!empty($list_users[$userid['userid']]['name']) ? $list_users[$userid['userid']]['name']:''),
							'userclass' => $list_users[$userid['userid']]['userlass'],
							'taskid' => $id,
							'added' => date('Y-m-d H:i:s')
							]);
						}					
					}				
				}			
				foreach ($user_task as $ussid => $usersid) {
					if(!isset($linr_tp_new[$usersid['userid']])){
						$db->query("DELETE FROM task_list_vikonavci WHERE userid = '{$usersid['userid']}' AND taskid = '{$id}'");
					}
				}
				$go->go('/?do=taskman&act=view&id='.$id);
			}		
		}
		$go->go('/?do=taskman');
	break;		
	case 'archiv': 	
		$getlistworker = getListWorker();	
		$listlocation = getListLocation();	
		$metatags = ['title'=>$lang['taskman_list_task'],'description'=>$lang['taskman_list_task'],'page'=>'list'];
		$speedbar .='<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>'.$lang['taskman_main_task'].'</a>';
		$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['taskman_us_add_arhive'].'</span>';	
		$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
		$taskman_status = 2;
		$listworker = $db->SimpleWhile("SELECT * FROM task_list Where status = ".$taskman_status." ORDER BY added ASC");
		$content .= '<table class="resp-tab"><thead><tr><th>'.$lang['register'].'</th><th>'.$lang['taskman_finish'].'</th><th>'.$lang['location'].'</th><th>'.$lang['taskman_type_task'].'</th><th>'.$lang['autor'].'</th></tr></thead><tbody>';
		if(isset($listworker) && count($listworker) > 0){
			foreach($listworker as $ipid => $taskm){
				$get_progress = get_progress($taskm['added'],$taskm['planned_at']);				
				$clientid = $db->Fast('task_list_client','*',['id'=>$taskm['clientid']]);
				$manager = $db->Fast('users','*',['id'=>$taskm['autor']]);
				$content .= '<tr>
				<td><font color="tomato">'.$taskm['added'].'</font></td><td><font color="#04bc0b">'.$taskm['finish_at'].'</font></td>				
				<td class="td_url"><a href="/?do=taskman'.$listlocation[$taskm['locationid']]['id'].'">'.$listlocation[$taskm['locationid']]['name'].'</a>'.(isset($clientid['client_street']) || isset($clientid['client_house']) ? '<br>':'').'	'.(isset($clientid['client_street'])?$clientid['client_street']:'').' '.(isset($clientid['client_house'])?$clientid['client_house']:'').'</td>
				<td class="td_url">
				<a href="/?do=taskman&act=view&id='.$taskm['id'].'">'.$getlistworker[$taskm['typesworker']]['name'].'</a>
				'.(!empty($clientid['id']) ? '<br><font color="#00a1ef">'.$clientid['client_pib'].'</font> '.(isset($clientid['client_mobil'])?$clientid['client_mobil']:''):'').'
				</td>
				
				<td><div class="block_online" style="display: block;"><div class="op"><span class="nam">'.$manager['username'].'</span><span class="class'.$manager['class'].'">'.getClassUser($manager['class']).'</span></div></div></td>
				
				</tr>';
			}
		}else{
			$content .= '<tr><td colspan="8">'.$lang['empty'].'</td></tr>';
		}
		$content .= '</table><div id="ajax"></div>';	
	break;		
	case 'list': 
		require MODULE_TASK.'list.php';
	break;		
	case 'vikonavci': 	
		$sql_task_pager = $db->Simple("SELECT count(id) as count FROM task_list WHERE status = 1");
		if(isset($sql_task_pager) && $sql_task_pager['count']>0){
			$metatags = ['title'=>$lang['taskman_us_loaded'],'description'=>$lang['taskman_us_loaded'],'page'=>'vikonavci'];
			$getlistuser = getListUser();	
			$taskman_status = 1;
			$new_masiv_task = [];
			$new_masiv_users = [];
			$new_masiv_users_count = [];
			$sql_task = $db->SimpleWhile("SELECT id, typesworker FROM task_list Where status = ".$taskman_status." ORDER BY added ASC");
			if(isset($sql_task) && count($sql_task) > 0){
				foreach($sql_task as $tid => $row){
					$new_masiv_task[$row['id']] = array(
						'id'=>$row['id'],'type'=>$row['typesworker']
					);
				}
			}
			$inConditions = implode(',', array_map('intval', array_keys($new_masiv_task)));
			$sql_task_us = $db->SimpleWhile("SELECT userid, taskid FROM task_list_vikonavci WHERE taskid IN (".$inConditions.")");
			if(isset($sql_task_us) && count($sql_task_us) > 0){
				foreach($sql_task_us as $tuid => $res){
					$new_masiv_users[$res['userid']] = array(
						'userid'=>$res['userid'],'taskid'=>$res['taskid']
					);				
					$new_masiv_users_count[$res['userid']][$res['taskid']] = array('taskid'=>$res['taskid']);
				}
			}
			$speedbar .='<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>'.$lang['taskman_main_task'].'</a>';
			$speedbar .='<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['taskman_us_loaded'].'</span>';
			$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
			$content .= '<div class="main_taskman">';
			if(isset($new_masiv_users) && count($new_masiv_users) > 0){
				$content .= '<div class="vikonavec-list">';
				foreach($new_masiv_users as $taskuid => $task){
					$content .= '<div class="vikonavec">';
					$content .= '<div class="uname">'.$getlistuser[$task['userid']]['username'].' / '.$getlistuser[$task['userid']]['uclass'].'</div>';
					$content .= '<div>'.$lang['alls'].': <span class="uconun">'.count($new_masiv_users_count[$task['userid']]).'</span></div>';
					$content .= '</div>';
				}
				$content .= '</div>';
			}else{
				
			}
			$content .= '</div>';
		}else{
			$go->go('/?do=taskman');
		}		
	break;		
	case 'current': 	
		$metatags = ['title'=>$lang['taskman_zvit_miss'],'description'=>$lang['taskman_zvit_miss'],'page'=>'current'];
		$speedbar .='<a class="brmhref" href="/?do=taskman"><i class="fi fi-rr-apps"></i>'.$lang['taskman_main_task'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['taskman_zvit_miss'].'</span>';
		$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
		$content .= '<div class="main_taskman">';
		$getlistworker = getListWorker();
		$zvit = [];
		if(isset($getlistworker) && count($getlistworker) > 0){
			$content .= '<div class="vikonavec-list">';
			foreach($getlistworker as $taskuid => $worker){
				$content .= '<div class="zvit-m">';
				$content .= '<div class="uname">'.$worker['name'].'</div>';
				$money = $db->Simple("SELECT SUM(money) AS suma_mo FROM task_money WHERE payment_type = '".$worker['id']."' AND YEAR(added) = YEAR(CURDATE()) AND MONTH(added) = MONTH(CURDATE())");
				$task = $db->Simple("SELECT COUNT(id) AS count FROM task_money WHERE payment_type = '".$worker['id']."' AND YEAR(added) = YEAR(CURDATE()) AND MONTH(added) = MONTH(CURDATE())");
				$zvit[] = $money['suma_mo'];
				$content .= '<div>'.$lang['taskman_count'].': '.(isset($task['count']) ? '<span class="uconun">'.$task['count'].'</span>' : 'N/A').'</div>';
				$content .= '<div>'.$lang['taskman_suma_money'].': '.(isset($money['suma_mo']) ? '<span class="uconun">'.$money['suma_mo'].'</span> '.$current_money.'' : 'N/A').'</div>';
				$content .= '</div>';
			}
			$content .= '</div>';
			$sum = array_sum($zvit);
			$content .= '<div class="taskzvit"><h1>'.$lang['alls'].': <b>'.$sum.'</b> грн</h1></div>';
		}else{
			
		}
		$content .= '</div>';
	break;		
	case 'getlist': 	
		echo'sdfsdfsdf';die;
	break;		

	default:	
		$metatags = ['title'=>''.$lang['taskman_main_task'].'','description'=>''.$lang['taskman_main_task'].'','page'=>'taskman'];
		$taskman_mapper = '';
		$sql_task = $db->Simple("SELECT count(id) as count FROM task_list WHERE status = 1");
		$content .= '<div class="admin-1"><div class="main-panel"><div class="admin-zvit">';
		$content .= '<a href="/?do=taskman&act=list"><img src="../style/img/taskman_list.png"><span>'.$lang['taskman_list_task'].' '.(isset($sql_task['count']) && $sql_task['count']>0?'<span class="task_active">'.$sql_task['count'].'</span>':'').'</span></a>';
		$content .= '<a href="/?do=taskman&act=add"><img src="../style/img/taskman_add.png"><span>'.$lang['taskman_new'].'</span></a>';
		#$content .= '<a href="/?do=taskman&act=vikonavci"><img src="../style/img/taskman_man.png"><span>'.$lang['taskman_us_loaded'].'</span></a>';
		#$content .= '<a href="/?do=taskman&act=archiv"><img src="../style/img/taskman_archiv.png"><span>'.$lang['taskman_closed'].'</span></a>';
		#$content .= '<a href="/?do=taskman&act=listworker"><img src="../style/img/taskman_archiv.png"><span>'.$lang['taskman_type_task'].'</span></a>';
		#$content .= '<a href="/?do=taskman&act=current"><img src="../style/img/miss_zvit.png"><span>'.$lang['taskman_zvit_main'].'</span></a>';
		$content .= '</div></div></div>';		
		/*
		$sql_location = $db->Multi('location');
		if(isset($sql_location) && count($sql_location)>0){
			foreach($sql_location as $loc){
				$sql_task_count = $db->Simple("SELECT count(id) as count FROM task_list WHERE status = 1 AND locationid = ".$loc['id']);
				if(isset($sql_task_count['count']) && $sql_task_count['count'] > 0) {
					$taskman_mapper .= "
						var marker = L.marker([" . $loc['lan'] . "," . $loc['lon'] . "], {
							icon: L.divIcon({html: '<div class=\"task_count\">" . $sql_task_count['count'] . "</div>'})
						}).addTo(map);
						
					";
				}
			}
		}
		
		$gpslan = $config['geo_lan'];
		$gpslon = $config['geo_lon'];
		$zoom = '12';
		$mapper = getMap();
		$mapjs = <<<HTML
		<script>
		var layers = {};
		var currentMap; 
		var lat = '{$gpslan}';
		var lon = '{$gpslon}';
		var map = L.map('taskmap');
		map.setView([lat, lon], {$zoom});
		{$mapper}{$taskman_mapper}
		layers['openstreetmap'] = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19
		});
		layers['vision'] = L.tileLayer('https://{s}.visicom.ua/2.0.0/planet3/base/{z}/{x}/{y}.png?key={$visicomkey}', {
			subdomains:['tms0','tms1','tms2','tms3'],
			maxZoom: 19,
			tms: true
		});
		layers['google'] = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
			maxZoom: 19,
			subdomains:['mt0','mt1','mt2','mt3']
		});
		currentMap = '{$config['typemap']}';
		layers[currentMap].addTo(map);

		</script>
		HTML;
		$content .= '<div style="height: 450px;position: relative;outline: none;width: 500px;"><div id="taskmap"></div></div>'.$mapjs;
		*/
}
$tpl->load_template('taskman.tpl');
$tpl->set('{speedbar}',$speedbar_block);
$tpl->set('{content_head}',$content_head);
$tpl->set('{content}',$content);
$tpl->compile('content');
$tpl->clear();
?>