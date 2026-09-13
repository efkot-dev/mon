<?php
if (!defined('PONMONITOR') && !defined('SCHEDULER')) {
    die('Hacking attempt!');
}
$onuid = (int)($_GET['onuid'] ?? 0);
$oltid = (int)($_GET['oltid'] ?? 0);
if(empty($oltid) && empty($oltid)){
    echo '<p>Некоректний дані.</p>';
    exit;	
}
$template_id = isset($_POST['tid']) ? (int)$_POST['tid'] : 0;
if ($template_id <= 0) {
    die('Invalid template ID');
}
$commandsRaw = getTemplateCommands($pdo, $template_id);
if (!$commandsRaw) {
    die('No commands found for template.');
}
$commands = array_map(fn($row) => $row['command'], $commandsRaw);
$variables = $_POST;
unset($variables['tid']);
function replaceVars($command, $vars) {
    foreach ($vars as $key => $value) {
        $command = str_replace("[$key]", $value, $command);
    }
    return $command;
}
echo "Підключення до пристрою...\n";
$getswitch = getOLTData($pdo,$oltid);
if(!empty($getswitch['username']) && !empty($getswitch['password'])){
	$telnet = new PMonTelnet($getswitch);	
	if($telnet->err_num){	
		echo $telnet->descr($telnet->err_num);
		exit;
	}	
}
foreach ($commands as $cmd) {
    $cmd_with_vars = replaceVars($cmd, $variables);
    echo "-> $cmd_with_vars\n";
    usleep(300000);
}
echo "Виконано.\n";
die;
?>