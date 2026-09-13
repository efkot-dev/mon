<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$input_lang = array(
	'input_pppoe_login'=>'PPPoE login',
	'input_pppoe_password'=>'PPPoE password',
	'input_voip_user'=>'VoIP User',
	'input_voip_password'=>'VoIP password',
	'input_ip_onu'=>'IP-Address',
);
function get_service_port($host, $community){
	$temp_service_port = snmp2_walk($host, $community,'1.3.6.1.4.1.2011.5.14.5.1', 100000, 5);
	$s_port = preg_replace('~^.*?(?=INTEGER:)~i','',$temp_service_port[0]);
	return trim(preg_replace ('/INTEGER:/','',$s_port));
}
function addLineBreaks($inputString) {
    // Розділяємо рядок на масив рядків за допомогою переносу рядка або \n
    $lines = explode("\n", $inputString);
    // Додаємо тег <br> в кінець кожного рядка
    $result = '';
    foreach ($lines as $line) {
        $result .= $line . "<br>";
    }

    return $result;
}
function free_number($index, $device, $type, $result = null){
	global $db;
	$free_space_real = [];
	$free_space = [];
	preg_match('/(\d+)\/(\d+)\/(\d+)/', $index, $inface);
	$get = array('type' => $type, 'sw_shelf' => $inface[1], 'sw_slot' => $inface[2], 'sw_port' => $inface[3], 'olt' => $device);
	$get_list = $db->Multi('onus', 'inface', $get);
	if(isset($get_list)){
		$free_space_real = [];
		foreach ($get_list as $index => $data) {
			preg_match('/(\d+)\/(\d+)\/(\d+):(\d+)/', $data['inface'], $infaces);
			if (isset($infaces[4])) {
				$free_space_real[$infaces[4]] = $infaces[4];
			}
		}
		if(isset($free_space_real)){
			$taken_numbers = array_keys($free_space_real); // Отримуємо всі ключі, які є в масиві
			if($type == 'epon'){
				$max_numbers = 64;
			} else {
				$max_numbers = 128;
			}
			$free_space = [];
			for ($i = 1; $i <= $max_numbers; $i++) {
				if (!in_array($i, $taken_numbers)) {
					$free_space[] = $i;
				}
			}
		}
	}
	if(isset($result) && $result == 'select'){
		$html_options = '<select name="free" id="free" class="inputonuselect">';
		foreach ($free_space as $value) {
			$html_options .= '<option value="' . $value . '">' . $value . '</option>';
		}
		$html_options .= '</select>';
		return $html = array('select'=>$html_options,'free_space'=>$free_space);
	}
	return $free_space;
}
function replaceEnterWithSpace(array $array): array {
    // Замінюємо '[enter]' у кожному елементі масиву
    return array_map(function($item) {
        return str_replace('[enter]', ' ', $item);
    }, $array);
}
function processPMon($content, $replacements) {
    foreach ($replacements as $key => $values) {
        $pattern = '/\{\{' . preg_quote($key) . '\}\}/';
        $content = preg_replace($pattern, $values, $content);
    }    
    $telnet_commands = explode("\n", $content);
    $data = implode("\n", $telnet_commands);
    preg_match_all('/\{\{([^}]+)\}\}/', $data, $matches);
    if (!empty($matches[1])) {
        #die('err_value');
    }
    return $data;
}
function standart() {
return'<div class="form1">{{type_pon}}</div><div class="form2">Тип Pon (epon, gpon)</div></div>
<div class="pole3"><div class="form1">{{interface_pon}} </div><div class="form2"> 1/2/3</div></div>
<div class="pole3"><div class="form1">{{shlef_index}} </div><div class="form2">1</div></div>
<div class="pole3"><div class="form1">{{slot_index}} </div><div class="form2">2</div></div>
<div class="pole3"><div class="form1">{{port_index}} </div><div class="form2">3</div></div>
<div class="pole3"><div class="form1">{{onu_index}} </div><div class="form2">4</div></div>
<div class="pole3"><div class="form1">{{interface_onu}} </div><div class="form2"> 1/2/3:4</div></div>
<div class="pole3"><div class="form1">{{onu_sn}} </div><div class="form2"> SerialNumber</div></div>
<div class="pole3"><div class="form1">{{onu_mac}} </div><div class="form2"> MAC</div></div>
<div class="pole3"><div class="form1">{{onu_name}} </div><div class="form2"> ONU name</div></div>
<div class="pole3"><div class="form1">{{onu_description}} </div><div class="form2"> ONU description</div>';		
}
function findFilesWithId($id) {
    $directory = ROOT_DIR . '/file/template/';
    $result = [];
    if (file_exists($directory) && is_dir($directory)) {
        $files = glob($directory . '*.register');
        foreach ($files as $file) {            
            if (file_exists($file)) {
				$fileContents = file_get_contents($file);
				$keyValuePairs = explode(';', trim($fileContents, ";\n"));
				foreach ($keyValuePairs as $keyValuePair) {
					if (isset($keyValuePair) && strpos($keyValuePair, ':') !== false) {
						list($key, $values) = explode(':', $keyValuePair, 2);
						$values = explode(',', $values);					
						if ($key === 'dozvoleno' && in_array($id, $values)) {
							$result[] = basename($file);
						}
					}
				}
			}
        }
    }    
    return $result;
}

function gettemplateolt($name) {
	global $db, $lang;
    $templatereg = ROOT_DIR . '/file/template/' . $name . '.register';
    $result = '';
    if (file_exists($templatereg)) {
		$currentData = file_get_contents($templatereg);
		$dataByKeys = [];
		$keyValuePairs = explode(';', trim($currentData, ";\n"));
		foreach ($keyValuePairs as $keyValuePair) {
			$keyValue = explode(':', $keyValuePair);
			if (count($keyValue) === 2) {
				$key = trim($keyValue[0]);
				$values = explode(',', trim($keyValue[1]));
				if (!empty($key) && !empty($values) && isset($values) && isset($key)) {
					$dataByKeys[$key] = $values;
				}
			}
		}
		$arrayolt = array();
		$getOLT = $db->Multi('switch');
        foreach ($getOLT as $id => $switch) {
			$arrayolt[$switch['id']]['place'] = $switch['place'];
			$arrayolt[$switch['id']]['id'] = $switch['id'];
		}
        foreach ($dataByKeys as $key => $values) {
			if(isset($key)){
				$result .='<img style="height:16px;position:relative;top:2px;" src="../style/img/database.png"> ';
				foreach ($values as $value) {
					if(isset($arrayolt[$value]['place']) && !empty($arrayolt[$value]['place'])){
					$nameolt = $arrayolt[$value]['place'];
					$result .= '<span class="value_temp" id="key-'.$name.'-'.$value.'">' . $nameolt . '<a href="#" onclick="deletunregistrolt(\''.$name.'\',\''.$key.'\',\''.$value.'\')";><img src="../style/img/close.png"></a></span>';
					}
				}
				$result .= '<br>';
			}
        }
    }
    return $result;
}
function arraytemplatevalue($name) {
    $templatereg = ROOT_DIR . '/file/template/' . $name . '.service';
    $dataByKeys = array();
    if (file_exists($templatereg)) {
		$currentData = file_get_contents($templatereg);
		$dataByKeys = [];
		$keyValuePairs = explode(';', trim($currentData, ";\n"));
		foreach ($keyValuePairs as $keyValuePair) {
			$keyValue = explode(':', $keyValuePair);
			if (count($keyValue) === 2) {
				$key = trim($keyValue[0]);
				$values = explode(',', trim($keyValue[1]));
				if (!empty($key) && !empty($values) && isset($values) && isset($key)) {
					$dataByKeys[$key] = $values;
				}
			}
		}
    }
    return $dataByKeys;
}
function gettemplatevalue($name) {
    $templatereg = ROOT_DIR . '/file/template/' . $name . '.service';
    $result = '';
    if (file_exists($templatereg)) {
		$currentData = file_get_contents($templatereg);
		$dataByKeys = [];
		$keyValuePairs = explode(';', trim($currentData, ";\n"));
		foreach ($keyValuePairs as $keyValuePair) {
			$keyValue = explode(':', $keyValuePair);
			if (count($keyValue) === 2) {
				$key = trim($keyValue[0]);
				$values = explode(',', trim($keyValue[1]));
				if (!empty($key) && !empty($values) && isset($values) && isset($key)) {
					$dataByKeys[$key] = $values;
				}
			}
		}
        foreach ($dataByKeys as $key => $values) {
			if(isset($key)){
				$result .= $key . ' ';
				foreach ($values as $value) {
					$result .= '<span class="value_temp" id="key-'.$name.'-'.$value.'">' . $value . '<a href="#" onclick="deletunregistr(\''.$name.'\',\''.$key.'\',\''.$value.'\')";><img src="../style/img/close.png"></a></span>';
				}
				$result .= '<br>';
			}
        }
    }
    return $result;
}
function gettemplatevalueview($name) {
    $templatereg = ROOT_DIR . '/file/template/' . $name . '.service';
    $result = '';
    if (file_exists($templatereg)) {
		$currentData = file_get_contents($templatereg);
		$dataByKeys = [];
		$keyValuePairs = explode(';', trim($currentData, ";\n"));
		foreach ($keyValuePairs as $keyValuePair) {
			$keyValue = explode(':', $keyValuePair);
			if (count($keyValue) === 2) {
				$key = trim($keyValue[0]);
				$values = explode(',', trim($keyValue[1]));
				if (!empty($key) && !empty($values) && isset($values) && isset($key)) {
					$dataByKeys[$key] = $values;
				}
			}
		}
        foreach ($dataByKeys as $key => $values) {
			if(isset($key)){
				$result .= '<div class="pole3"><div class="form1">{{'.$key . '}} </div><div class="form2">';
				foreach ($values as $value) {
					$result .= '' . $value . ' ';
				}
				$result .= '</div></div>';
			}
        }
    }
    return $result;
}
function getlelectTP($formid,$data,$olt) {
$scr =  "<script>
    $(document).ready(function () {
        $('#templateSelect').on('change', function () {
			$('#loadtemplate').html('<div style=\"padding:2px;text-align:center;\"><img style=\"height:9px;\" src=\"../style/img/ajax-loader-big.gif\" /></div>');
            var selectedTemplate = $(this).val();
            $.ajax({
                type: 'POST',
                url: '/?do=regonu&act=loadtemplate&formid=".$formid."&data=".$data."&olt=".$olt."',
                data: {template: selectedTemplate},
                success: function (response) {
                    $('#loadtemplate').html(response);
                }
            });
			$(\".viewblock\").show();
        });
    });
</script>";	
return $scr;
}
function parseDataInface($data) {
    $pattern = '/(\d+)\/(\d+)\/(\d+)/';
    if (preg_match($pattern, $data, $matches)) {
        $inf = array(
            'shlef' => $matches[1],
            'slot' => $matches[2],
            'port' => $matches[3]
        );
		return $inf;
    } else {
        return false; 
    }
}
function parseDataInfaceOnu($data) {
    $pattern = '/^(\d+)\/(\d+)\/(\d+):(\d+)/';
    if (preg_match($pattern, $data, $matches)) {
        $inf = array(
            'shlef' => $matches[1],
            'slot' => $matches[2],
            'port' => $matches[3],
            'onu' => $matches[4]
        );
		return $inf;
    } else {
        return false; 
    }
}
function getnametpl($value) {
	$value = str_replace('.pmon', '',$value);
	return trim($value);	
}
function isValidContent(string $content): bool {
    $blacklist = [
        '/SELECT/i',
        '/UNION/i',
        '/fopen/i',
        '/file_get_contents/i',
        '/root/i',
        '/<\?php/i',
        '/<\?/i',
        '/\?>/i',
        '/exec/i',
        '/shell_exec/i',
        '/passthru/i',
        '/eval/i'
    ];

    foreach ($blacklist as $command) {
        if (preg_match($command, $content)) {
            return false;
        }
    }

    return true;
}

function isPmonFile(string $file): bool {
    return pathinfo($file, PATHINFO_EXTENSION) === 'pmon';
}
function sanitizeInput(string $input): string {
    return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
}

function isValidTemplateFile(string $template_folder, string $file): bool {
    return in_array($file, getTemplateFiles($template_folder), true);
}
function getTemplateFiles(string $template_folder): array {
    return array_filter(
        scandir($template_folder),
        static fn($file) => is_file($template_folder. $file) && isPmonFile($file)
    );
}
function openTemplateFile(string $template_folder, string $file): string {
    $file_path = $template_folder. $file;
    return file_get_contents($file_path);
}
function saveTemplateFile(string $template_folder, string $file, string $content): void {
    $file_path = $template_folder. $file;
    file_put_contents($file_path, $content);
    echo "File saved successfully.\n";
}

function zte3_reinface_onu($llid) {
	$lx=sprintf("%08x",$llid);
	switch ($lx[0]) {
	case '1':
		$sh=hexdec($lx[1])+1;
		$sl=hexdec($lx[2].$lx[3]);
		$ol=hexdec($lx[4].$lx[5]);
	break;
	case '2':
		$sh=hexdec($lx[3]);
		$sl=hexdec($lx[4].$lx[5]);
		$ol=hexdec($lx[6].$lx[7]);
		if ($cl>16) {
			$cl-=16; $sl++;
		}
			$ol1=$ol;
		break;
	case '3':
		$sh=hexdec($lx[1])+1;
		$sl=hexdec($lx[2].$lx[3]);
		$ol=($sl&0x07)+1;
		$sl=$sl>>3;
		$on = hexdec($lx[4].$lx[5]);
	break;
	case '6':
		$sh=hexdec($lx[1])+1;
		$sl=hexdec($lx[2].$lx[3]);
		$ol=0;
	break;
	}
	$data['shlef'] = $sh;
	$data['slot'] = $sl;
	$data['port'] = $ol;
	return $data;
}
function zte3_get_all_vlan($result) {
	$vlans = [];
    $out = explode("following:", $result);
    $out = trim(end($out));
    $vlan = explode(",", $out);
	if(is_array($vlan)){
		foreach($vlan as $key => $tem){
			if (preg_match('/-/i',$tem)){
				preg_match('/(\d+)-(\d+)/',$tem,$mat);
				for ($i = $mat[1]; $i <= $mat[2]; $i++) {
					$vlans[$i]['vlan'] = $i;
				}
			}else{
				$vlans[$tem]['vlan'] = $tem;
			}
		}
	}
	return isset($vlans) ? $vlans : null;
}
function zte3_get_gpon_all_vlan($netip, $snmpro, $idolt) {
    $cacheFile = ROOT_DIR . '/file/template/list_vlan_' . $idolt . '.data';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 600)) {
        $cachedData = file_get_contents($cacheFile);
        $profileValues = unserialize($cachedData);
    } else {
        $result = @snmp2_real_walk($netip, $snmpro, "1.3.6.1.4.1.3902.1015.20.3.1.8");
        $profileValues = [];
        if ($result) {
            foreach ($result as $key => $value) {
                if (preg_match('/20.3.1.8.(\d+)/', $key, $m)) {
                    $value = str_replace(['STRING:', '"', ' '], '', $value);
                    if (strpos($value, ',') !== false) {
                        $tempVlans = explode(',', $value);
                        foreach ($tempVlans as $tempVlan) {
                            if (strpos($tempVlan, '-') === false) {
                                $profileValues[$tempVlan]['vlan'] = $tempVlan;
                            }
                        }
                    } else if (strpos($value, '-') !== false) {
                        preg_match('/(\d+)-(\d+)/', $value, $matches);
                        $start = intval($matches[1]);
                        $end = intval($matches[2]);
                        for ($i = $start; $i <= $end; $i++) {
                            $profileValues[$i]['vlan'] = $i;
                        }
                    } else {
                        $vlan = intval($value);
                        if (strpos($vlan, '-') === false) {
                            $profileValues[$vlan]['vlan'] = $vlan;
                        }
                    }
                }
            }
            file_put_contents($cacheFile, serialize($profileValues));
        }
    }
    return $profileValues ?: null;
}
function zte6_get_gpon_all_vlan($netip, $snmpro, $idolt) {
	$array_vlan = array();
    $cacheFile = ROOT_DIR . '/file/template/list_vlan_' . $idolt . '.data';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 600)) {
        $cachedData = file_get_contents($cacheFile);
        $profileValues = unserialize($cachedData);
    } else {
        $result = @snmp2_real_walk($netip,$snmpro,"1.3.6.1.4.1.3902.3.102.1.1.1.1");
		if($result!=false) {
			foreach ($result as $key => $value) {
				if (preg_match('/102.1.1.1.1.(\d+)/', $key, $m)) {
					$value = str_replace(['INTEGER:', '"', ' '], '', $value);
					if($value > 0) {
						$array_vlan[$value]['vlan'] = $value;
					}
				}
			}
            file_put_contents($cacheFile, serialize($array_vlan));
        }
    }
    return $array_vlan ?: null;
}
function huawei_get_gpon_lineprofile($netip, $snmpro, $idolt) {
	$cacheFile = ROOT_DIR . '/file/template/list_line_profile_' . $idolt . '.data';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 300)) {
        $cachedData = file_get_contents($cacheFile);
        $lineprofile = unserialize($cachedData);
    } else {   
        $result = @snmp2_real_walk($netip, $snmpro, "1.3.6.1.4.1.2011.6.128.1.1.2.43.1.7");
        $lineprofile = [];
            foreach ($result as $key => $value) {
                if (preg_match('/STRING:/',$value)) {					
                    $pr = trim(str_replace(['STRING:', '"', ' '], '', $value));
                    $lineprofile[strtolower($pr)]['lp'] = $pr;
                }
            }
		file_put_contents($cacheFile, serialize($lineprofile));
	}
	return $lineprofile ?: null;
}
function huawei_get_gpon_srvprofile($netip, $snmpro, $idolt) {
	$cacheFile = ROOT_DIR . '/file/template/list_srv_profile_' . $idolt . '.data';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 300)) {
        $cachedData = file_get_contents($cacheFile);
        $lineprofile = unserialize($cachedData);
    } else {   
        $result = @snmp2_real_walk($netip, $snmpro, "1.3.6.1.4.1.2011.6.128.1.1.2.43.1.8");
        $lineprofile = [];
            foreach ($result as $key => $value) {
                if (preg_match('/STRING:/',$value)) {					
                    $pr = trim(str_replace(['STRING:', '"', ' '], '', $value));
                    $lineprofile[strtolower($pr)]['sp'] = $pr;
                }
            }
		file_put_contents($cacheFile, serialize($lineprofile));
	}
	return $lineprofile ?: null;
}
function zte3_get_profile($netip, $snmpro) {
	$result = '';
    $data = [];
    #$result = @snmp2_real_walk($netip, $snmpro, "1.3.6.1.4.1.3902.1012.3.28.1.1.1");
		$result = [
		'iso.3.6.1.4.1.3902.1012.3.28.1.1.1.268501248.15' => 'STRING: "ZTE-F601"',
		'iso.3.6.1.4.1.3902.1012.3.28.1.1.1.268501248.17' => 'STRING: "ZTE-F601"',
		'iso.3.6.1.4.1.3902.1012.3.28.1.1.1.268501248.18' => 'STRING: "ZTE-F601"',
		'iso.3.6.1.4.1.3902.1012.3.28.1.1.1.268501248.22' => 'STRING: "vs"',
		'iso.3.6.1.4.1.3902.1012.3.28.1.1.1.268501248.23' => 'STRING: "vs"'
	];
    if($result){
        foreach ($result as $key => $value) {
            if (preg_match('/1012.3.28.1.1.1.(\d+)/', $key, $m)) {
                $profileId = $m[1];
                $profileValue = str_replace(['STRING:', '"', ' '], '', $value);
                $data[trim($profileValue)] = [
                    'profileid' => $profileId,
                    'profile' => trim($profileValue)
                ];
            }
        }
    }
    return $data ?: null;
}
function zte3_get_tcont_profile($netip, $snmpro) {
    $data = [];
    #$result = @snmp2_real_walk($netip, $snmpro, "1.3.6.1.4.1.3902.1012.3.26.1.1.2");
	$result = [
		'iso.3.6.1.4.1.3902.1012.3.26.1.1.2.1879048194' => 'STRING: "UP-1000mb"',
		'iso.3.6.1.4.1.3902.1012.3.26.1.1.2.1879048195' => 'STRING: "UP-100mb'
	];
    if ($result) {
        foreach ($result as $key => $value) {
            if (preg_match('/1012.3.26.1.1.2.(\d+)/', $key, $m)) {
                $idTariff = $m[1];
                $tariffValue = str_replace(['STRING:', ' ', '"'], '', $value);
                $data[$idTariff] = [
                    'idtariff' => $idTariff,
                    'tariff' => str_replace('UP-','',$tariffValue)
                ];
            }
        }
    }
    return $data ?: null;
}
function huawei_gpon_get_list($netip,$snmpro) {
	$data = [
		'oid' => '1.3.6.1.4.1.2011.6.128.1.1.2.48.1.2',
		'type' => 'exec','ip' => $netip,'community'=> $snmpro
	];
	$snmpwalk = pmon_walk_m($data);	
	if (!$snmpwalk) {
		return array();
	}
	$result = array();
	foreach($snmpwalk as $key => $temp){
		$data = explode_getdata($temp['result']);
		preg_match('/.48.1.2.(\d+).(\d+)/',$data['oid'],$m);
		if(isset($m[1]) && isset($m[2])){
			$port = huawei_decode_ifIndex_gpon($m[1]);
			if (isset($port['type']) && $port['type']=='gpon') {
				$result['gpon'][] = array(
					'idport' => $m[1],'port' => $port['type'],'ont' => $m[2],'sn' => huawei_sn_onu_gpon($data['result']),
					'index' => $port['shelf'] . '/' . $port['slot'] . '/' . $port['port']
				);
			}
		}
	}
	return $result;
}
function huawei_decode_ifIndex_gpon($ifIndex) {
	$board_type = ( $ifIndex & bindec('11111110000000000000000000000000') ) >> 25 ;
	switch($board_type) {
		case 126:
		$port_type="epon";
		$shelf_no       = ( $ifIndex & bindec('00000001111110000000000000000000') ) >> 19 ;
		$slot_no        = ( $ifIndex & bindec('00000000000001111110000000000000') ) >> 13 ;
		$port_no        = ( $ifIndex & bindec('00000000000000000001111100000000') ) >> 8  ;
		return(array("type"=>$port_type,"shelf"=>$shelf_no,"slot"=>$slot_no,"port"=>$port_no));
		break;
		case 125:
		$port_type = "gpon";
		$shelf_no       = ( $ifIndex & bindec('00000001111110000000000000000000') ) >> 19 ;
		$slot_no        = ( $ifIndex & bindec('00000000000001111110000000000000') ) >> 13 ;
		$port_no        = ( $ifIndex & bindec('00000000000000000001111100000000') ) >> 8  ;
		return(array("type"=>$port_type,"shelf"=>$shelf_no,"slot"=>$slot_no,"port"=>$port_no));
		break;
	}
}

function explode_getdata($temp) {
	$parts = explode('=', $temp);
	if(isset($parts[0]) && isset($parts[1])){
		return array('oid'=>trim($parts[0]),'result'=>trim($parts[1]));
	}else{
		return false;
	}
}
function huawei_sn_onu_gpon($tempsn) {
	if (strpos($tempsn,'Hex-STRING') !== false){
		$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
		$tempsn = preg_replace('/Hex-STRING/','',$tempsn);
		$tempsn = str_replace(':', '',$tempsn);
		$tempsn = str_replace('"', '',$tempsn);
		$tempsn = str_replace(' ', '',$tempsn);
		return trim($tempsn);
	}else{
		$tempsn = preg_replace('~^.*?( = )~i','',$tempsn);
		$onu_snc1 = preg_replace ('/STRING:/','',$tempsn);
		$tmpv = explode(" ","$onu_snc1");
		$tmpe = str_split($tmpv[1]);
		return $tmpe[1].$tmpe[2].$tmpe[3].$tmpe[4].strtoupper(dechex(ord($tmpe[5]))).strtoupper(dechex(ord($tmpe[6]))).strtoupper(dechex(ord($tmpe[7]))).strtoupper(dechex(ord($tmpe[8])));
	}
}
?>