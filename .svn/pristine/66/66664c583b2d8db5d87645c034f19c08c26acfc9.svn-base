<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ROOT_DIR.'/inc/init.monitor.php';
if(isset($config['telegramchatid']) && isset($config['telegram']) && isset($config['typestelegram']) && $config['telegram']=='on' && $config['typestelegram']!='off'){
$sqlnotification = $db->SimpleWhile("SELECT * FROM notification WHERE status = '1' ORDER BY added DESC LIMIT 50");
if (is_array($sqlnotification) && count($sqlnotification) > 0) {
    foreach ($sqlnotification as $sms_sql) { 
        if (!empty($sms_sql['message'])) {
            if($config['typestelegram']=='chat'){
                $text = grab_telegram($sms_sql['message']);
                $response = telegram_chat($text);
            } elseif($config['typestelegram']=='bot'){
                $arrayUid = explode(',',$config['userid']);
                if(isset($arrayUid) && count($arrayUid)>0){
                    $text = grab_telegram($sms_sql['message']);
                    $response = telegram_bot($text, $arrayUid);
                }
            } elseif($config['typestelegram']=='groups'){
                $text = grab_telegram($sms_sql['message']);
                $response = telegram_message($text);
            } else {
                $response = false;
            }    
            if($response) {
                $db->SQLupdate('notification',['status'=>2],['id' => $sms_sql['id']]);
            } else {
				$db->query("DELETE FROM notification WHERE id = '{$sms_sql['id']}'");
            }
            usleep(500);
        } else {
            $db->query("DELETE FROM notification WHERE id = '{$sms_sql['id']}'");
        }
    }    
}
}
?>
