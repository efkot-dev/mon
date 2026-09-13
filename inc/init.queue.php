<?php
if (!defined('PONMONITOR') && !defined('QUEUE')){
	die('Hacking attempt!');
}
use Enqueue\Redis\RedisConnectionFactory;
if(defined('QUEUE')){
	$redis_ip = defined('REDIS_IP') ? REDIS_IP : '127.0.0.1';
	$redis_port = defined('REDIS_PORT') ? REDIS_PORT : 6379;
	$connectionFactory = new RedisConnectionFactory([
		'host' => $redis_ip,'port' => $redis_port,
	]);
	$context = $connectionFactory->createContext();	
	$queue = $context->createQueue('pmon_queue');
}
?>
