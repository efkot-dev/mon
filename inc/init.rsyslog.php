<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$icon = '';
$namedev = '';
$timecheck_functions = 600;
$telegramchatid = '';
$telegramtoken = '';
date_default_timezone_set('Europe/Kiev');
$time = date('Y-m-d H:i:s');
define('CONFIG',true);
require ENGINE_DIR.'init.lang.php';
require ROOT_DIR.'/inc/database.php';
if(!$dbrsyslog['password']){
	die('connection_failed_rsyslog');
}
$conn = new mysqli($dbrsyslog['hostname'],$dbrsyslog['username'],$dbrsyslog['password'],$dbrsyslog['database']);
$conn->set_charset('utf8');
if($conn->connect_error){
    die('connection_failed_'.$conn->connect_error);
}
function search_check($message){
	if($message){
		if (preg_match("/OAM Operational Status: Linkfault/i", $message)) {
			return 'linkfault';
		} elseif (preg_match("/Rx Power low/i", $message)) {
			return 'rxlow';
		} else {
			return false;
		}
	}else{
		return false;
	}	
}
function clean_input($input) {
	$cleaned_input = stripslashes(trim($input));
	$cleaned_input = htmlspecialchars($cleaned_input);
	return "'".$cleaned_input."'";
}
function telegram_sms($type,$telegramchatid,$telegramtoken) {
    if ($type) {
        $url = 'https://api.telegram.org/bot' . $telegramtoken . '/sendmessage';
        $data = array(
            'chat_id' => $telegramchatid,
            'text' => $type,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
$pmon_db = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
$pmon_db->set_charset('utf8');
if ($pmon_db->connect_error) {
    die("pmon_connection_failed_".$pmon_db->connect_error);
}
$array_switch = array();
$switch_db = $pmon_db->query("SELECT * FROM switch");
if ($switch_db->num_rows > 0) {
	while ($sw = $switch_db->fetch_assoc()) {
		$array_switch[$sw['netip']]['id'] = $sw['id'];
		$array_switch[$sw['netip']]['place'] = (!empty($sw['locationname'])?'<b>'.$sw['locationname'].'</b> ':'').$sw['place'];
		$array_switch[$sw['netip']]['netip'] = $sw['netip']; 
		$array_switch[$sw['netip']]['model'] = $sw['inf'].' '.$sw['model'];
	}
}
?>