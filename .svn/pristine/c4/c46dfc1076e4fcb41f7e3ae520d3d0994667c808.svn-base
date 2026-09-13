<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class TelegramBot{
    var $config;
    var $token;
    function __construct($config)
    {
        $this->config = $config;
        $this->token = $config['telegramtoken'];
    }
	function sendMessage($chat,$text)
    {
        $args = ['chat_id' => $chat,'text' => $text];
        return $this->sendRequest('sendMessage', $args);
    }
	function sendRequest($method, $args = [], $response_type = false)
    {
        $args = http_build_query($args);
        $request = curl_init('https://api.telegram.org/' . $this->token . '/' . $method);
        curl_setopt_array($request, array(
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => 'cURL request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $args
        ));
        curl_setopt($request, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $result = curl_exec($request);
				print_R($result);
        curl_close($request);
        if ($response_type == "object") {
            return new response(json_decode($result, true));
        } else {
            return $result;
        }
    }
}
?>
