<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$act = isset($_POST['act']) ? Clean::text($_POST['act']): null;
$types = isset($_POST['types']) ? Clean::text($_POST['types']): null;
$result = '';
switch($act){
	case'select';
		if(isset($config['telegramchatid']) && isset($config['telegram']) && $config['telegram']=='on'){
			$result .= '<div class="fr"><form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="telegram">';
			if($types=='chat'){
				$result .= '<input name="types" type="hidden" value="chat">';
				$result .= formpage(['img'=>'m11.png','name'=>'ChatID','descr'=>$lang['info_chatid'],'pole'=>'<input style="width:63%;" name="chatid" class="input1" type="text" value="'.$config['telegramchatid'].'">']);
			}elseif($types=='bot'){
				$result .= '<input name="types" type="hidden" value="bot">';
				$result .= formpage(['img'=>'m11.png','name'=>'userID','descr'=>$lang['info_userid'],'pole'=>'<input style="width:99%;" name="userid" class="input1" type="text" value="'.$config['userid'].'">']);					
			}elseif($types=='groups'){
				$result .= formpage(['img'=>'m11.png','name'=>'ChatID','descr'=>$lang['info_chatid'],'pole'=>'<input style="width:63%;" name="chatid" class="input1" type="text" value="'.$config['telegramchatid'].'">']);
				$result .= '<input name="types" type="hidden" value="groups">';
				$result .= formpage(['img'=>'m11.png','name'=>'MessageID','descr'=>$lang['info_messageid'],'pole'=>'<input style="width:63%;" name="messageid" class="input1" type="text" value="'.$config['messageid'].'">']);				
			}else{
				$result .= '<input name="types" type="hidden" value="off">';
			}
			$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form></div>';			
			echo $result;
		}else{
			echo'<div class="block_error_i block_err_inf"><b>'.$lang['off_notifications'].'</b><span>'.$lang['check_settings'].'</span></div>';
		}
	break;
}
?>