<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
if (!$access->get('setup')) {
    $go->redirect('main');
}

if (!function_exists('upd_h')) {
    function upd_h($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('upd_run_cmd')) {
    function upd_run_cmd($command, &$returnCode = null)
    {
        $output = [];
        exec($command . ' 2>&1', $output, $code);
        $returnCode = (int)$code;
        return implode("\n", $output);
    }
}
if (!function_exists('upd_extract_field')) {
    function upd_extract_field($svnInfoText, $field)
    {
        $lines = preg_split('/\r?\n/', (string)$svnInfoText);
        foreach ($lines as $line) {
            if (strpos($line, $field) === 0) {
                return trim(substr($line, strlen($field)));
            }
        }
        return null;
    }
}
if (!function_exists('upd_remove_tree')) {
    function upd_remove_tree($path)
    {
        $real = realpath($path);
        if ($real === false || !is_dir($real)) {
            return;
        }
        $root = realpath(ROOT_DIR);
        if ($root === false || strpos($real, $root) !== 0) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        @rmdir($real);
    }
}
if (!function_exists('upd_perm_hint')) {
    function upd_perm_hint($siteDir)
    {
        $safe = escapeshellarg((string)$siteDir);
        return implode("\n", [
            'Permission denied during svn update.',
            'Fix ownership/permissions from console (example for Apache www-data):',
            '  sudo chown -R www-data:www-data ' . $safe,
            '  sudo find ' . $safe . ' -type d -exec chmod 755 {} \\;',
            '  sudo find ' . $safe . ' -type f -exec chmod 644 {} \\;',
            '  sudo -u www-data svn cleanup ' . $safe . ' --non-interactive',
            'Then run update again.'
        ]);
    }
}
if (!function_exists('upd_token')) {
    function upd_token()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (empty($_SESSION['pmon_update_token'])) {
            try {
                $_SESSION['pmon_update_token'] = bin2hex(random_bytes(16));
            } catch (Throwable $e) {
                $_SESSION['pmon_update_token'] = md5(uniqid('pmon_upd_', true));
            }
        }
        return $_SESSION['pmon_update_token'];
    }
}
if (!function_exists('upd_sql_skip_codes')) {
    function upd_sql_skip_codes()
    {
        return [1050, 1051, 1054, 1060, 1061, 1062, 1265, 1064, 1068, 1091, 1136, 1146, 1813];
    }
}
if (!function_exists('upd_sql_should_skip')) {
    function upd_sql_should_skip($stmt, PDOException $e, $code)
    {
        if (in_array((int)$code, upd_sql_skip_codes(), true)) {
            return true;
        }

        $sql = strtolower(trim((string)$stmt));
        $msg = strtolower((string)$e->getMessage());

        $skipStmtPatterns = [
            '/\bimport\s+tablespace\b/',
            '/\bdiscard\s+tablespace\b/',
            '/\bchange\s+`?start_time`?\s+`?start_time`?\s+dat\b/',
            '/\bchange\s+`?[a-z0-9_]+`?\s+`?[a-z0-9_]+`?\s+dat\b/'
        ];
        foreach ($skipStmtPatterns as $rx) {
            if (preg_match($rx, $sql)) {
                return true;
            }
        }

        $skipMsgPatterns = [
            'tablespace for table',
            'please discard the tablespace before import',
            'already exists',
            'duplicate column name',
            'unknown column'
        ];
        foreach ($skipMsgPatterns as $needle) {
            if (strpos($msg, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
if (!function_exists('upd_validate_sql_path')) {
    function upd_validate_sql_path($file, $baseDir)
    {
        $real = realpath($file);
        $base = realpath($baseDir);
        if (!$real || !$base || strpos($real, $base) !== 0) {
            throw new RuntimeException('Invalid SQL path');
        }
        return $real;
    }
}
if (!function_exists('upd_sql_exec_file')) {
    function upd_sql_exec_file(PDO $pdo, $file, $baseDir, &$skipped, array &$dbLogs)
    {
        $path = upd_validate_sql_path($file, $baseDir);
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Cannot read SQL file: ' . basename($file));
        }
        $statements = array_filter(array_map('trim', preg_split('/;\s*(\r?\n|$)/', $sql)));
        $executed = 0;
        foreach ($statements as $stmt) {
            if ($stmt === '' || preg_match('/^\s*(#|--)/', $stmt)) {
                continue;
            }
            try {
                $pdo->exec($stmt);
                $executed++;
            } catch (PDOException $e) {
                $code = (int)($e->errorInfo[1] ?? 0);
                if (upd_sql_should_skip($stmt, $e, $code)) {
                    $skipped++;
                    $dbLogs[] = 'SQL SKIP (' . $code . '): ' . preg_replace('/\s+/', ' ', substr($stmt, 0, 240));
                    continue;
                }
                $dbLogs[] = 'SQL ERROR (' . $code . '): ' . $e->getMessage();
                throw $e;
            }
        }
        return $executed;
    }
}
if (!function_exists('upd_apply_db_updates')) {
    function upd_apply_db_updates(PDO $pdo, &$executed, &$skipped, array &$dbLogs)
    {
        $sqlDir = ROOT_DIR . '/install/update/';
        if (!is_dir($sqlDir)) {
            $dbLogs[] = 'No install/update directory found, DB migration skipped.';
            return true;
        }

        $executed = 0;
        $skipped = 0;
        foreach (glob($sqlDir . '*.database') ?: [] as $file) {
            $executed += upd_sql_exec_file($pdo, $file, $sqlDir, $skipped, $dbLogs);
        }
        foreach (glob($sqlDir . '*.insert') ?: [] as $file) {
            $executed += upd_sql_exec_file($pdo, $file, $sqlDir, $skipped, $dbLogs);
        }

        $deleteNames = [
            'FEEDING','TIMER_SPEEDPMON','ENABLE_SPEEDPMON','COUNT_SPEEDPMON','DISABLE_SPEEDPMON',
            'PMON_SUPPORT','PMON_TECH_UID','PMON_TECH_API','TIMER_COUNT_CHANGE_RX',
            'ENABLE_CRITICAL_CHANGE_RX','REASONONUHUAWEIG','SERVERLOAD','STATUSONUHUAWEI',
            'STATUSONUBDCOM','REASONONUBDCOM','COUNTCHECKER','TIME_REASONONUBDCOM',
            'POWERDCBDCOM','PORTCMD','PORTPHP','TYPEPING'
        ];
        $stmt = $pdo->prepare('DELETE FROM pmonini WHERE name = ?');
        foreach (array_unique($deleteNames) as $name) {
            $stmt->execute([$name]);
        }
        $pdo->exec("UPDATE pmonini SET value='1' WHERE name='CACHE'");
        $pdo->exec("DELETE FROM pmonini WHERE value='0' AND name='CACHE'");
        foreach (['sgnal_sfp','feeding','switch_status','pmon_progress','onu_message','panel_client','read_messages','appmessage'] as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
        return true;
    }
}

$page = '';
$metatags = [
    'title' => $lang['updatepmon'] ?? 'Update PMON',
    'description' => $lang['updatepmon'] ?? 'Update PMON',
    'page' => 'update',
];

$enabled = (int)($confPMon['UPDATE'] ?? 0) === 1;
if (!$enabled) {
    $page .= '<div class="comment">Оновлення вимкнено в конфігурації (UPDATE=0).</div>';
    $tpl->load_template('update.tpl');
    $tpl->set('{result}', $page);
    $tpl->compile('content');
    $tpl->clear();
    return;
}

$repoUrl = trim((string)($confPMon['SVNREPO'] ?? ''));
$svnUser = trim((string)($confPMon['SVNLOGIN'] ?? ''));
$svnPass = (string)($confPMon['SVNPASSWORD'] ?? '');
$siteDir = ROOT_DIR;
$lockFile = sys_get_temp_dir() . '/pmon_update_web.lock';
$csrf = upd_token();

$repoEsc = escapeshellarg($repoUrl);
$siteEsc = escapeshellarg($siteDir);

$svnAuth = ' --non-interactive --trust-server-cert';
if ($svnUser !== '') {
    $svnAuth .= ' --username ' . escapeshellarg($svnUser);
}
if ($svnPass !== '') {
    $svnAuth .= ' --password ' . escapeshellarg($svnPass) . ' --no-auth-cache';
}

if ($repoUrl === '') {
    $page .= '<div class="comment">Не задано SVNREPO у конфігурації.</div>';
    $tpl->load_template('update.tpl');
    $tpl->set('{result}', $page);
    $tpl->compile('content');
    $tpl->clear();
    return;
}

if ($act === 'update') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $page .= '<div class="comment">Некоректний метод запиту.</div>';
    } elseif (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $page .= '<div class="comment">CSRF token mismatch.</div>';
    } else {
        $lockFp = @fopen($lockFile, 'c+');
        if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
            $page .= '<div class="comment">Оновлення вже виконується іншим процесом.</div>';
        } else {
            $logs = [];
            $ok = true;

            $logs[] = '== SVN cleanup ==';
            $out = upd_run_cmd('cd ' . $siteEsc . ' && svn cleanup --non-interactive', $rc);
            $logs[] = $out;
            if ($rc !== 0) {
                $ok = false;
                $logs[] = 'ERROR: svn cleanup failed (' . $rc . ')';
            }

            if ($ok) {
                if (is_dir(ROOT_DIR . '/.svn')) {
                    $logs[] = '== SVN update ==';
                    $out = upd_run_cmd('cd ' . $siteEsc . ' && svn update' . $svnAuth, $rc);
                } else {
                    $logs[] = '== SVN checkout ==';
                    $out = upd_run_cmd('svn checkout' . $svnAuth . ' ' . $repoEsc . ' ' . $siteEsc, $rc);
                }
                $logs[] = $out;
                if ($rc !== 0) {
                    $ok = false;
                    $logs[] = 'ERROR: svn operation failed (' . $rc . ')';
                    if (stripos($out, 'Permission denied') !== false || stripos($out, 'E000013') !== false) {
                        $logs[] = upd_perm_hint($siteDir);
                    }
                }
            }

            if ($ok) {
                $logs[] = '== DB migration ==';
                try {
                    if (!isset($pdo) || !($pdo instanceof PDO)) {
                        throw new RuntimeException('PDO not initialized');
                    }
                    $dbExecuted = 0;
                    $dbSkipped = 0;
                    $dbLogs = [];
                    upd_apply_db_updates($pdo, $dbExecuted, $dbSkipped, $dbLogs);
                    $logs[] = 'DB executed: ' . $dbExecuted;
                    $logs[] = 'DB skipped: ' . $dbSkipped;
                    if (!empty($dbLogs)) {
                        $logs = array_merge($logs, $dbLogs);
                    }
                } catch (Throwable $e) {
                    $ok = false;
                    $logs[] = 'ERROR: DB migration failed: ' . $e->getMessage();
                }
            }

            if ($ok) {
                upd_remove_tree(ROOT_DIR . '/install');
                @unlink(ROOT_DIR . '/install.php');
                @unlink(ROOT_DIR . '/update.php');
                $logs[] = 'Install artifacts removed.';
            }

            $page .= '<div class="comment"><b>' . ($ok ? 'Оновлення завершено успішно' : 'Оновлення завершено з помилками') . '</b></div>';
            $page .= '<pre style="white-space:pre-wrap;max-height:420px;overflow:auto;background:#f8f9fb;padding:10px;border:1px solid #e5e7eb;">' . upd_h(implode("\n", $logs)) . '</pre>';

            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }
    }
}

$remoteOut = upd_run_cmd('svn info' . $svnAuth . ' ' . $repoEsc, $remoteRc);
$localOut = upd_run_cmd('cd ' . $siteEsc . ' && svn info .', $localRc);

$remoteRev = ($remoteRc === 0) ? upd_extract_field($remoteOut, 'Last Changed Rev:') : null;
$localRev = ($localRc === 0) ? upd_extract_field($localOut, 'Revision:') : null;

$page .= '<div class="comment"><b>SVN репозиторій:</b> ' . upd_h($repoUrl) . '</div>';
$page .= '<div class="comment"><b>Локальна директорія:</b> ' . upd_h($siteDir) . '</div>';

if ($remoteRc !== 0) {
    $page .= '<div class="comment">Помилка отримання ревізії з SVN.</div>';
    $page .= '<pre style="white-space:pre-wrap;max-height:240px;overflow:auto;background:#f8f9fb;padding:10px;border:1px solid #e5e7eb;">' . upd_h($remoteOut) . '</pre>';
}
if ($localRc !== 0) {
    $page .= '<div class="comment">Помилка отримання локальної ревізії.</div>';
    $page .= '<pre style="white-space:pre-wrap;max-height:240px;overflow:auto;background:#f8f9fb;padding:10px;border:1px solid #e5e7eb;">' . upd_h($localOut) . '</pre>';
}

if ($remoteRev !== null && $localRev !== null) {
    $page .= '<div class="comment">Остання ревізія в SVN: <b>' . upd_h($remoteRev) . '</b></div>';
    $page .= '<div class="comment">Локальна ревізія: <b>' . upd_h($localRev) . '</b></div>';

    if ((int)$remoteRev > (int)$localRev) {
        $page .= '<div class="comment">Доступне оновлення.</div>';
    } elseif ((int)$remoteRev === (int)$localRev) {
        $page .= '<div class="comment">Система вже на актуальній ревізії.</div>';
    } else {
        $page .= '<div class="comment">Локальна ревізія новіша за віддалену (перевірте гілку).</div>';
    }
}

$page .= '<form method="post" action="/?do=update&act=update" style="margin-top:12px;">';
$page .= '<input type="hidden" name="csrf" value="' . upd_h($csrf) . '">';
$page .= '<button type="submit" class="deviceadd" onclick="return confirm(\'Запустити оновлення зараз?\');">' . upd_h($lang['update'] ?? 'Оновити') . '</button>';
$page .= '</form>';

$tpl->load_template('update.tpl');
$tpl->set('{result}', $page);
$tpl->compile('content');
$tpl->clear();
?>
