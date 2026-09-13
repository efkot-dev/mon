<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function deleteFiles($directory) {
    $files = scandir($directory);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && $file !== '.htaccess' && $file !== 'index.html') {
            $filePath = $directory . $file;
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
}
function listFiles($directory) {
    if (is_dir($directory)) {
		$files = scandir($directory);
		$data = "<h3>Файли у директорії Cache:</h3>";
		$data = "<div class=\"css_form_cache\">";
		$data .= "<ul>";
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$filePath = $directory . DIRECTORY_SEPARATOR . $file;
				if (is_file($filePath)) {
					$fileSize = filesize($filePath);
					$formattedSize = formatSize($fileSize);
					$creationTime = filectime($filePath);
					$formattedDate = date('Y-m-d H:i:s', $creationTime);
					$data .= "<li>" . md5($file) . " - $formattedSize - $formattedDate</li>";
				}
			}
		}
		$data .= "</ul>";
		$data .= "</div>";
	}else{
		$data .= "access";
	}
	return $data;
}
function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}
function getRedisMemory($redis) {
    $info = $redis->info('memory');
    $usedMemory = $info['used_memory_human'] ?? 'N/A';
    $maxMemory = $info['maxmemory_human'] ?? 'No limit';
    return "<p>Used Memory: $usedMemory</p><p>Max Memory: $maxMemory</p>";
}
function mksize($bytes) {
    if ($bytes < 1000 * 1024)
        return number_format($bytes / 1024, 2) . ' kB'; elseif ($bytes < 1000 * 1048576)
        return number_format($bytes / 1048576, 2) . ' MB';
    elseif ($bytes < 1000 * 1073741824)
        return number_format($bytes / 1073741824, 2) . ' GB';
    else
        return number_format($bytes / 1099511627776, 2) . ' TB';
}
function getRedisStats($redis) {
    $result = '';
    $keys = $redis->keys('*');
	$result .= "<div class=\"block_redis\">";
    foreach ($keys as $key) {
        $type = $redis->type($key);
        $result .= "<div class=\"block_redis_name\">Key: $key, Type: $type</div>\n";
		$result .= "<div class=\"block_redis_data\"><div>";
        switch ($type) {
            case 'string':
                $value = $redis->get($key);
                $result .= "Value: $value\n";
                break;
            case 'list':
                $values = $redis->lrange($key, 0, -1);
                $result .= "Values: " . implode(', ', $values) . "\n";
                break;
            case 'set':
                $values = $redis->smembers($key);
                $result .= "Values: " . implode(', ', $values) . "\n";
                break;
            case 'hash':
                $values = $redis->hgetall($key);
                $result .= "Values: " . print_r($values, true) . "\n";
                break;
            case 'zset':
                $values = $redis->zrange($key, 0, -1, ['withscores' => true]);
                $result .= "Values: " . print_r($values, true) . "\n";
                break;
            default:
                $result .= "Unsupported type\n";
                break;
				
        }
		$result .= "</div>";
		$result .= "</div>";
    }   
	$result .= "</div>";	
    return $result;
}

function main_setup_pmon() {
    global $confPMon, $GLOBAL_MODULE, $lang;	
    $shablon = '
		<div class="pmon_block" id="board_fault">
		<div class="pmon_block_left block_white pre20">
			'.generateTagFilter().'
		</div>
		<div class="pmon_block_right pre80">
		<div id="module-list-pmon">';
    foreach ($GLOBAL_MODULE as $module_key => $module) {
        $tags = implode(' ', $module['tags']);
        $is_enabled = true;
        foreach ($module['module'] as $key) {
            if (empty($confPMon[$key]) || $confPMon[$key] != 1) {
                $is_enabled = false;
                break;
            }
        }
        $action = $is_enabled ? 'disable' : 'enable';
        $button_text = $is_enabled ? $lang['disable'] : $lang['enable'];
        $shablon .= '<div class="module-item ' . $tags . '" data-module="' . $module_key . '">';
        $shablon .= '<div id="pole-pmon">';
        $shablon .= '<label class="checkbox-green"><input type="checkbox" ' . ($is_enabled ? 'checked' : '') . ' onclick="toggleModule(\'' . $module_key . '\', this.checked)"> ';
        $shablon .= '<span class="checkbox-green-switch" data-label-on="On" data-label-off="Off"></span></label>';
        if(isset($module['icon']) && !empty($module['icon'])){
			$shablon .= '<div class="pole-img">';
			$shablon .= '<img src="../style/img/'.$module['icon'].'">';
			$shablon .= '</div>';
		}
		$shablon .= '<div class="pole-sobol">';
        $shablon .= '<span class="name">' . $module['name'] . '</span>';
        $shablon .= '<span class="title">' . $module['title'] . '</span>';
        $shablon .= '</div>';
        $shablon .= '</div>';
        $shablon .= '</div>';
    }
    $shablon .= '</div></div>
</div>';
	
	$isValidgps = false;
	if (isset($GLOBAL_MODULE['gps_monitoring'])) {
		$isValidgps = validateConfig($GLOBAL_MODULE['gps_monitoring']);
	}

	if (!$isValidgps) {
		$shablon .= 'неправильно налаштовано';
	}
	if(isset($GLOBAL_MODULE['gps_traccar'])){  	
		$isValidgps = validateConfig($GLOBAL_MODULE['gps_traccar']);
	}
	if (!$isValidgps) {
		$shablon .= 'неправильно налаштовано';
	}    
    return $shablon;
}
function validateConfig($variables) {
    global $confPMon;
    if (
		isset($variables['module'][0]) &&
		isset($confPMon[$variables['module'][0]]) &&
		$confPMon[$variables['module'][0]] == 1
	) {
        $allVariablesPresent = true;
        foreach ($variables['variables'] as $variable) {
            if (empty($confPMon[$variable])) {
                $allVariablesPresent = false;
                break;
            }
        }
        return $allVariablesPresent;
    }
    return true; 
}
function generateTagFilter() {
    global $all_tags;
    $html = '<div class="tag-filter">';
    foreach ($all_tags as $tag) {
        $html .= '<label><input type="checkbox" class="tag-checkbox" value="' . $tag . '"> ' . $tag . '</label> ';
    }
    $html .= '<label><a href="#" onclick="resetFilter()">Show All</a></label>';
    $html .= '</div>';
    return $html;
}

?>