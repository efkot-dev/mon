<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('setupdevice')){
	$go->redirect('main');
}
$selectport = '';
$setup = '';
if(isset($_POST['act']) && $_POST['act']=='nextmigration'){
	$migrationolt = (isset($_POST['migrationolt']) ? Clean::int($_POST['migrationolt']) : null);
	$pontport = (isset($_POST['pontport']) ? Clean::int($_POST['pontport']) : null);
	$olt = (isset($_POST['olt']) ? Clean::int($_POST['olt']) : null);
	$types = (isset($_POST['types']) ? Clean::text($_POST['types']) : null);
	$position = (isset($_POST['position']) ? Clean::text($_POST['position']) : null);
	$uid = (isset($_POST['uid']) ? Clean::text($_POST['uid']) : null);
	$marker = (isset($_POST['marker']) ? Clean::text($_POST['marker']) : null);
	$comment = (isset($_POST['comment']) ? Clean::text($_POST['comment']) : null);
	$history = (isset($_POST['history']) ? Clean::text($_POST['history']) : null);
	$deletolt = (isset($_POST['deletolt']) ? Clean::text($_POST['deletolt']) : null);
	if(is_numeric($migrationolt) && is_numeric($olt) && preg_match('/mac|sn/', $types)) {
		$where['olt']=$olt;
		if($pontport>0){
			$dataponport = $db->Fast('switchpon','*',['id'=>$pontport,'oltid'=>$olt]);
			$where['zte_idport'] = $dataponport['sfpid'];
		}
		$getonu = $db->Multi('onus', '*', $where);
		$generatetmasiv = [];
		if(is_array_empty_masiv($getonu)){
			foreach ($getonu as $ont) {
				$unikeyonu = md5($ont[$types]);
				$generatetmasiv[$unikeyonu][$types] = $ont[$types];

				if ($position == 'on' && !empty($ont['lan']) && !empty($ont['lon'])) {
					$generatetmasiv[$unikeyonu]['geo']['lan'] = $ont['lan'];
					$generatetmasiv[$unikeyonu]['geo']['lon'] = $ont['lon'];
				} else {
					$generatetmasiv[$unikeyonu]['geo']['lan'] = null;
					$generatetmasiv[$unikeyonu]['geo']['lon'] = null;
				}

				$generatetmasiv[$unikeyonu]['stariy_idonu'] = ($uid == 'on' && !empty($ont['idonu'])) ? $ont['idonu'] : null;
				$generatetmasiv[$unikeyonu]['uid'] = ($uid == 'on' && !empty($ont['uid'])) ? $ont['uid'] : null;
				$generatetmasiv[$unikeyonu]['tag'] = ($marker == 'on' && !empty($ont['tag'])) ? $ont['tag'] : null;
				$generatetmasiv[$unikeyonu]['comments'] = ($comment == 'on' && !empty($ont['comments'])) ? $ont['comments'] : null;

				if ($history == 'on') {
					// $getrx = $db->Multi('historysignal', '*', ['onu' => $ont['idonu']]);
				}
			}
		}
		// куди їдемо
		$getonutohome = $db->Multi('onus','idonu,mac,sn',['olt'=>$migrationolt]);
		$homemasiv = array();
		if(is_array_empty_masiv($getonutohome)){
			foreach($getonutohome as $onuh => $onth){
				if(!empty($onth[$types])){
					$newunikeyonu = md5($onth[$types]);
					$homemasiv[$newunikeyonu][$types] = $onth[$types];
					$homemasiv[$newunikeyonu]['idonu'] = $onth['idonu'];
				}
			}
		}
		if (is_array_empty_masiv($generatetmasiv) && is_array_empty_masiv($homemasiv)) {
			$importonu = [];
			foreach ($generatetmasiv as $getonu => $staraonu) {
				if (!empty($homemasiv[$getonu][$types]) && $homemasiv[$getonu][$types] === $staraonu[$types]) {
					$uidonu = $homemasiv[$getonu]['idonu'];
					$importonu[$uidonu] = [
						'idonu' => $homemasiv[$getonu]['idonu'],
						$types => $staraonu[$types],
					];
					if (!empty($generatetmasiv[$getonu]['tag'])) {
						$importonu[$uidonu]['tag'] = $generatetmasiv[$getonu]['tag'];
					}
					if (!empty($staraonu['uid'])) {
						$importonu[$uidonu]['uid'] = $staraonu['uid'];
					}					
					if (!empty($staraonu['stariy_idonu'])) {
						$importonu[$uidonu]['stariy_idonu'] = $staraonu['stariy_idonu'];
					}
					if (!empty($staraonu['comments'])) {
						$importonu[$uidonu]['comments'] = $staraonu['comments'];
					}
					if (!empty($staraonu['geo']['lan']) && !empty($staraonu['geo']['lon'])) {
						$importonu[$uidonu]['lan'] = $staraonu['geo']['lan'];
						$importonu[$uidonu]['lon'] = $staraonu['geo']['lon'];
					}
				}
			}
		}
		$countimport = 0;
		if(is_array_empty_masiv($importonu)){
			foreach ($importonu as $idonu => $data) {
				if(!empty($data['uid']))				
					$sqlupdate['uid'] = $data['uid'];
				if(!empty($data['tag']))				
					$sqlupdate['tag'] = $data['tag'];
				if(!empty($data['comments']))				
					$sqlupdate['comments'] = $data['comments'];
				if(!empty($data['lan']) && !empty($data['lan'])) {				
					$sqlupdate['lan'] = $data['lan'];
					$sqlupdate['lon'] = $data['lon'];
				}
				$db->SQLupdate($PMonTables['onus'],$sqlupdate,['idonu' => $idonu]);
				if($deletolt=='on'){
					delete_onu($data['stariy_idonu']);
				}
				$countimport ++;
			}
		}
		$go->go('/?do=detail&act=olt&id='.$migrationolt);
	}
	die;
}else{
	$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
	$dataswitch = $db->Fast('switch','*',['id'=>$id]);
	if($dataswitch['device']!=='olt'){
		$go->redirect('main');		
	}
	if(!$dataswitch['id']){
		$go->redirect('main');	
	}else{
		$sqlpon = $db->Multi('switch_pon','*',['oltid'=>$dataswitch['id']]);
		$selectport .= '<select name="pontport">';
		$selectport .= '<option value="0">Всі onu</option>';
		foreach($sqlpon as $idpon => $pon){
			$selectport .= '<option value="'.$idpon.'">'.$pon['pon'].',onu:'.$pon['count'].'</option>';
		}
		$selectport .= '</select>';
	}
	$setup .= '<form method="post" action="/?do=migration"><input name="olt" type="hidden" value="'.$dataswitch['id'].'"><input name="act" type="hidden" value="nextmigration"><div class="migration">';
	$setup .= '<legend>Комутатор</legend>';
	$setup .= '<fieldset><label for="olt">'.$dataswitch['place'].' '.$dataswitch['netip'].'</label></fieldset>';	
	$setup .= '<legend>Порт з якого переносимо ONU</legend><fieldset><label for="port">'.$selectport.'</label></fieldset>';
	$setup .= '<legend>Вибрати до чого привязуємо міграцію</legend>';
	$setup .= '<fieldset>';
	$setup .= '<label for="mac"><span>MAC</span><input type="radio" name="types" value="mac" id="mac"></label>';
	$setup .= '<label for="sn"><span>SN</span><input type="radio" name="types" value="sn" id="sn"></label>';
	$setup .= '</fieldset>';
	$setup .= '<legend>Вибрати дані які переносимо</legend>';
	// координати
	$setup .= '<label for="coordinates"><span>Координати</span><input type="checkbox" name="position" id="position"></label>';
	// маркери	
	$setup .= '<label for="coordinates"><span>Маркери</span><input type="checkbox" name="marker" id="marker"></label>';
	// білінг дані
	$setup .= '<label for="coordinates"><span>UID</span><input type="checkbox" name="uid" id="uid"></label>';
	// історію сигналів
	$setup .= '<label for="coordinates"><span>Історію сигналів</span><input type="checkbox" name="history" id="history"></label>';
	// всі коментарі ону
	$setup .= '<label for="coordinates"><span>Коментарі</span><input type="checkbox" name="comment" id="comment"></label>';
	// комутатор куди переносимо
	$setup .= '<br><legend>Комутатор на який мігрують onu</legend>';
	$setup .= '<fieldset><label for="toolt">';
	$setup .= '<select name="migrationolt">';
	$sqlolt = $db->Multi('switch','*',['device'=>'olt']);
	foreach($sqlolt as $idolt => $olt){
		if($olt['id']!=$id)
			$setup .= '<option value="'.$olt['id'].'">'.$olt['place'].'</option>';
	}
	$setup .= '</select>';
	$setup .= '</label></fieldset>';
	// Видалити onu з старого олта
	$setup .= '<label for="coordinates"><span>З старого видалити onu які мігрували</span><input type="checkbox" name="deletolt" id="deletolt"></label>';	
	$setup .= '<br><input type="submit" name="submit" value="Міграція"></div></form>';
	$metatags = array('title'=>$lang['setupmigration'],'description'=>$lang['setupmigration'],'page'=>'migration');
	$tpl->load_template('setup/main.tpl');
	$tpl->set('{langsetup}',$lang['setupmigration']);
	$tpl->set('{id}',$id);
	$tpl->set('{place}',$dataswitch['place'].' '.$dataswitch['inf'].''.$dataswitch['model']);
	$tpl->set('{langlist}',$lang['btn_menu_list']);
	$tpl->set('{result}',$setup);
	$tpl->compile('content');
	$tpl->clear();
}
?>