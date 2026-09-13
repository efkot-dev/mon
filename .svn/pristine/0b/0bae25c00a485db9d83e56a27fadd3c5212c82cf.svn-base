<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
date_default_timezone_set('Europe/Kiev');
#date_default_timezone_set('Europe/Tirane');
function isMaliciousRequest(array $requestData): bool
{
    $patterns = [
        /* XSS */
        '/<\s*script\b/i',
        '/<\s*iframe\b/i',
        '/<\s*svg\b/i',
        '/<\s*object\b/i',
        '/<\s*embed\b/i',
        '/<\s*link\b/i',
        '/<\s*style\b/i',
        '/on[a-z]+\s*=/i',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/data\s*:\s*text\/html/i',
        '/document\.cookie/i',
        '/window\.location/i',
        /* LFI / RFI */
        '/etc\/passwd/i',
        '/proc\/self\/environ/i',
        '/php:\/\/(input|filter|expect)/i',
        '/file:\/\//i',
        '/zip:\/\//i',
        '/phar:\/\//i',
        /* RCE */
        '/\/bin\/(sh|bash)/i',
        '/\b(exec|shell_exec|passthru|popen)\b/i',
        '/\b(base64_decode|eval|assert)\b/i',
        /* Encoded */
        '/%0d|%0a/i',
        '/%00/i',
        '/%3c/i', // <
        '/%3e/i', // >
        '/%22|%27/i', // " '
    ];
    foreach ($requestData as $key => $value) {
        if (checkMaliciousValue($key, $patterns)) {
            return true;
        }
        if (is_array($value)) {
            if (isMaliciousRequest($value)) {
                return true;
            }
        } else {
            if (checkMaliciousValue($value, $patterns)) {
                return true;
            }
        }
    }
    return false;
}
function checkMaliciousValue($value, array $patterns): bool
{
    if (!is_scalar($value)) {
        return false;
    }
    $value = urldecode((string)$value);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $value)) {
            return true;
        }
    }
    return false;
}

if (isset($_REQUEST) && isMaliciousRequest($_REQUEST)) {
    http_response_code(403);
    exit('Access is denied in windows cmd. Why?');
}
$time = date('Y-m-d H:i:s');
?>
