<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
require ENGINE_DIR.'functions/regonu.php';
require ENGINE_DIR.'classes/telnet.class.php';
require ENGINE_DIR.'classes/reger.class.php';
$time = date('Y-m-d H:i:s');
$templatereg = ROOT_DIR.'/file/template/';
$tempfolder = ROOT_DIR.'/export/temp/';
$result = '';
$script = '';
$speedbar = '';
switch($act){
	case 'saveservice';
		$request = $_REQUEST;
		if (isValidContent($request['name']) && isValidContent($request['type'])) {
			$template = $request['temp'];
			$name = $request['name'];
			$type = $request['type'];
			$filePath = $templatereg . $template . '.service';
			if (file_exists($filePath)) {
				$currentData = file_get_contents($filePath);
				$dataByKeys = [];
				if(isset($currentData)){
					$keyValuePairs = explode(';', trim($currentData, ";\n"));
					foreach ($keyValuePairs as $keyValuePair) {
						$keyValue = explode(':', $keyValuePair);
						$dataByKeys[trim($keyValue[0])] = trim($keyValue[1]);
					}
				}
			}
			if (isset($type) && $type === 'del') {
				if (isset($dataByKeys[$name])) {
					$valueToRemove = $request['value'];
					$dataByKeys[$name] = str_replace($valueToRemove, '', $dataByKeys[$name]);
					$newData = '';
					foreach ($dataByKeys as $key => $value) {
						if (!empty($value)) {
							$newData .= "$key:$value;";
						}
					}
					$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
					file_put_contents($filePath, $newData);
					echo "<div class=\"block-info berr2\">Delete</div>";
				} else {
					echo "<div class=\"block-info berr1\">Empty key</div>";
				}
			} else {
				if (isset($dataByKeys[$name])) {
					$keyValues = explode(',', $dataByKeys[$name]);
					if (!in_array($request['value'], $keyValues)) {
						$keyValues[] = $request['value'];
						$dataByKeys[$name] = implode(',', $keyValues);
						$newData = '';
						foreach ($dataByKeys as $key => $value) {
							$newData .= "$key:$value;";
						}
						$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
						file_put_contents($filePath, $newData);
						echo "<div class=\"block-info berr3\">Save</div>";
					} else {
						echo "<div class=\"block-info berr1\">Error \"replace\"</div>";
					}
				} else {
					$dataByKeys[$name] = $request['value'];
					$newData = '';
					foreach ($dataByKeys as $key => $value) {
						$newData .= "$key:$value;";
					}
					$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
					file_put_contents($filePath, $newData);
					echo "<div class=\"block-info berr3\">Save</div>";
				}
			}
		} else {
			echo "<div class=\"block-info berr2\">Check data</div>";
		}
		exit;
	break;
	case 'saveaccesolt';		
		$request = $_REQUEST;
		if (isValidContent($request['name']) && isValidContent($request['type'])) {
			$template = $request['temp'];
			$name = $request['name'];
			$type = $request['type'];
			$filePath = $templatereg . $template . '.register';
			if (file_exists($filePath)) {
				$currentData = file_get_contents($filePath);
				$dataByKeys = [];
				if(isset($currentData)){
					$keyValuePairs = explode(';', trim($currentData, ";\n"));
					foreach ($keyValuePairs as $keyValuePair) {
						$keyValue = explode(':', $keyValuePair);
						if(isset($keyValue[0]) && isset($keyValue[1])){
							$dataByKeys[trim($keyValue[0])] = trim($keyValue[1]);
						}
					}
				}
			}
			if (isset($type) && $type === 'del') {
				if (isset($dataByKeys[$name])) {
					$valueToRemove = $request['value'];
					$dataByKeys[$name] = str_replace($valueToRemove, '', $dataByKeys[$name]);
					$newData = '';
					foreach ($dataByKeys as $key => $value) {
						if (!empty($value)) {
							$newData .= "$key:$value;";
						}
					}
					$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
					file_put_contents($filePath, $newData);
					echo "<div class=\"block-info berr2\">Delete</div>";
				} else {
					echo "<div class=\"block-info berr1\">Checke KEY!</div>";
				}
			} else {
				if (isset($dataByKeys[$name])) {
					$keyValues = explode(',', $dataByKeys[$name]);
					if (!in_array($request['value'], $keyValues)) {
						$keyValues[] = $request['value'];
						$dataByKeys[$name] = implode(',', $keyValues);
						$newData = '';
						foreach ($dataByKeys as $key => $value) {
							$newData .= "$key:$value;";
						}
						$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
						file_put_contents($filePath, $newData);
						echo "<div class=\"block-info berr3\">Save</div>";
					} else {
						echo "<div class=\"block-info berr1\">Error \"replace\"</div>";
					}
				} else {
					$dataByKeys[$name] = $request['value'];
					$newData = '';
					foreach ($dataByKeys as $key => $value) {
						$newData .= "$key:$value;";
					}
					$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
					file_put_contents($filePath, $newData);
					echo "<div class=\"block-info berr3\">Save</div>";
				}
			}
		} else {
			echo "<div class=\"block-info berr2\">Check log Apache</div>";
		}
		exit;
	break;
	case 'rebilding';		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$get_type = $_POST['type'] ?? null;
			$get_name = $_POST['name'] ?? null;
			$get_template = $_POST['template'] ?? null;
			$filePath = $templatereg . Clean::text($get_name) . '.pmon';
			if($get_type=='new'){
				file_put_contents($filePath, $get_template);
				echo "<div class=\"block-info berr3\">Added template</div>";
			}elseif(isValidContent($get_template) && $get_name !== null && $get_type=='update') {
				if (file_exists($filePath)) {
					file_put_contents($filePath, $get_template);
					echo "<div class=\"block-info berr3\">Save</div>";
				}
			}else{
				if(file_exists($filePath)) {
					file_put_contents($filePath, $get_template);
					echo "<div class=\"block-info berr3\">Save</div>";
				}
			}
		}
		exit;
	break;
	case 'reparametr';
		$request = $_REQUEST;
		$filePath = $templatereg .'data.service';
		$type = $request['type'];
		if (isValidContent($request['name']) && isValidContent($request['type']) && file_exists($filePath)) {
			if ($type === 'del') {
				$name = $request['name'];
				$currentData = file_get_contents($filePath);
				$dataByKeys = [];
				$keyValuePairs = explode(';', trim($currentData, ";\n"));
				foreach ($keyValuePairs as $keyValuePair) {
					$keyValue = explode(':', $keyValuePair);
					$dataByKeys[trim($keyValue[0])] = trim($keyValue[1]);
				}
				if (isset($dataByKeys[$name])) {
					$valueToRemove = $request['value'];
					if(isset($request['value'])){
						$dataByKeys[$name] = str_replace($valueToRemove, '', $dataByKeys[$name]);
						$newData = '';
						foreach ($dataByKeys as $key => $value) {
							if (!empty($value)) {
								$newData .= "$key:$value;";
							}
						}
					}else{
						$newData = $currentData;
					}
					if(isset($newData)){
						$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
						file_put_contents($filePath, $newData);
					}
				}
			}
		}
		$go->go('/?do=regonu&act=template');
	break;
	case 'parametr';	
		$request = $_REQUEST;
		if (isValidContent($request['value']) && isValidContent($request['name'])) {
			$name = $request['name'];
			$filePath = $templatereg .'data.service';
			$currentData = file_get_contents($filePath);
			$dataByKeys = [];
			$keyValuePairs = explode(';', trim($currentData, ";\n"));
			foreach ($keyValuePairs as $keyValuePair) {
				$keyValue = explode(':', $keyValuePair);
				$dataByKeys[trim($keyValue[0])] = trim($keyValue[1]);
			}
			if (isset($dataByKeys[$name]) && file_exists($filePath)) {
				$keyValues = explode(',', $dataByKeys[$name]);
				if (!in_array($request['value'], $keyValues)) {
					$keyValues[] = $request['value'];
					$dataByKeys[$name] = implode(',', $keyValues);
					$newData = '';
					foreach ($dataByKeys as $key => $value) {
						$newData .= "$key:$value;";
					}
					$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
					file_put_contents($filePath, $newData);
				}
			} else {
				$dataByKeys[$name] = $request['value'];
				$newData = '';
				foreach ($dataByKeys as $key => $value) {
					$newData .= "$key:$value;";
				}
				$newData = str_replace([',;', ':;', ',,'], [';', '', ','], $newData);
				file_put_contents($filePath, $newData);
			}
		}
		$go->go('/?do=regonu&act=template');
	break;
	case 'delet';		
		$get_name = isset($_GET['name']) ? Clean::text($_GET['name']): null;
		$filePath = $templatereg . $get_name . '.pmon';
		if (file_exists($filePath) && isset($get_name)) {
			@unlink($filePath);
		}		
		$go->go('/?do=regonu&act=template');
	break;
	case 'edit';	
		$get_name = isset($_GET['name']) ? Clean::text($_GET['name']): null;
		$filePath = $templatereg . $get_name . '.pmon';
		$speedbar .= '<a class="brmhref" href="/?do=regonu&act=template"><i class="fi fi-rr-layers"></i>Reger</a>';
		$currentData = '';
		if (file_exists($filePath) && isset($get_name)) {
			$currentData = file_get_contents($filePath);
			$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>Edit</span>';			
		}
		if(isset($get_name)) {
		$result .='<div class="block-monitor"><div class="block-head"><h2>'.$get_name.'</h2></div><div class="block-center"><div id="billing"><div class="block-setup">';
		$result .='<form action="" method="post"><input type="hidden" name="name" value="'.$get_name.'"><input type="hidden" id="type" name="type" value="'.(isset($currentData) && !empty($currentData) ? 'update':'new').'">
		<textarea class="codetemplate" id="template" name="template">'.$currentData.'</textarea></form>';
		$result .='</div><div class="block-connect-list"><div class="pole3"><div class="form1">';
		$result .='<div id="tempresult"></div><button id="add" class="css_add gosave" onclick="rebilgingunregistr(\''.$get_name.'\')">'.$lang['save'].'</button></div><div class="form2"><!--<button class="css_add gotemplate" onclick="sendunregistr(\''.$get_name.'\', \'save\')">Сформувати</button>--></div></div>';			
			$result .='<div class="pole3">'.standart().'</div>
			'.gettemplatevalueview($get_name);
			$result .='</div></div></div></div></div>';
		}else{
			$go->go('/?do=regonu&act=template');
		}
	break;
	case 'template';
		$tpl->load_template('regonu/form.tpl');
		$tpl->compile('addform');
		$tpl->clear();		
		$speedbar .= '<a class="brmhref" href="/?do=regonu&act=checker"><i class="fi fi-rr-layers"></i>ONU Management</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>Template Management</span>';	
		$get_name = isset($_GET['name']) ? Clean::text($_GET['name']): null;
		$get_type = isset($_GET['type']) ? Clean::text($_GET['type']): null;
		$template_files = @getTemplateFiles($templatereg);
			$result .='<table width="100%"><tr><td>';
			if(isset($template_files) && count($template_files)>0){
				$result .='<table class="resp-tab none"><thead><tr><th class="td_name_d">Name</th><th width="60%">Value</th><th></th></tr></thead><tbody>';
				foreach ($template_files as $file) {
					$name = getnametpl($file);
					$result .='<tr>';    
					$result .='<td class="td_name_l"><span class="templates">'.$name.'</span>';
					$result .='<span id="s-'.$name.'" onclick="unregistr(\''.$name.'\',\'olt\')"><img class="addperemina" src="../style/img/database.png"></span>';					
					$result .='<span id="k-'.$name.'" onclick="unregistr(\''.$name.'\',\'add\')"><img class="addperemina" src="../style/img/min_add.png"></span>';
					$result .='<a href="/?do=regonu&act=edit&name='.$name.'"><img class="addperemina" src="../style/img/min_edit.png"></a>
					<div id="b-'.$name.'"></div></td>';
					$result .='<td class="td_name_l"><div id="global-'.$name.'">';
					$result .= gettemplatevalue($name);
					$result .= gettemplateolt($name);
					$result .='<div id="result-'.$name.'"></div></div>';
					$result .='</td>';
					$result .='<td>
					<a href="/?do=regonu&act=delet&name='.$name.'" onclick="return confirm("Delete '.$name.'?")">'.$lang['delet'].'</a></td>';
					$result .='</tr>';
					
				}
				$result .='</tbody></table>';
				}
			$result .= (isset($tpl->result['addform']) ? $tpl->result['addform'] : '');
			$result .='</td></tr></table>';

		$filePath = $templatereg . 'data.service';
		if (file_exists($filePath)) {
			$currentData = file_get_contents($filePath);
			$dataByKeys = [];
			$keyValuePairs = explode(';', trim($currentData, ";\n"));
			foreach ($keyValuePairs as $keyValuePair) {
				$keyValue = explode(':', $keyValuePair);
				if(isset($keyValue[0]) && isset($keyValue[1]))
					$dataByKeys[trim($keyValue[0])] = trim($keyValue[1]);
			}
			if(isset($dataByKeys) && is_array($dataByKeys) && count($dataByKeys)>0){
			$result .='<table width="40%"><tr><td><table class="resp-tab"><thead><tr><th width="50%">Параметри</th><th>Дані</th><th width="1%"></th></tr></thead><tbody>';
			$output = '';
			foreach ($dataByKeys as $key => $values) {
				$valueArray = explode(',', $values);
				foreach ($valueArray as $value) {
					$result .='<tr><td class="td_name_l">'.$key.'</td><td class="td_name_l">'.$value.'</td>';
					$result .='<td><a href="/?do=regonu&act=reparametr&type=del&name='.$key.'&value='.$value.'"><img src="../style/img/close.png"></a></td>';
					$result .='</tr>';	
				}
			}
			$result .='</tbody></table>';
			}
			$result .='</td></tr></table>';
		}
	break;
	case 'register';	
		$conf = array();
		$original_conf = array();
		$generate_conf = array();
		$shablomregister = isset($_POST['data']['shablomregister']) ? Clean::text($_POST['data']['shablomregister']): null;	
		$filePath = $templatereg . $shablomregister . '.pmon';
		if (file_exists($filePath) && isset($shablomregister)) {
			$currentData = file_get_contents($filePath);
		}
		$olt = isset($_POST['data']['olt']) ? Clean::int($_POST['data']['olt']): null;	
		$sn = isset($_POST['data']['sn']) ? Clean::text($_POST['data']['sn']): null;	
		$name = isset($_POST['data']['name']) ? Clean::text($_POST['data']['name']): 'ont_name';	
		$descr = isset($_POST['data']['description']) ? Clean::text($_POST['data']['description']): 'ont_descr';	
		$pon = isset($_POST['data']['pon']) ? Clean::text($_POST['data']['pon']): null;	
		$inface_slot = isset($_POST['data']['inface_slot']) ? Clean::int($_POST['data']['inface_slot']): null;	
		$inface_port = isset($_POST['data']['inface_port']) ? Clean::int($_POST['data']['inface_port']): null;	
		$inface_shlef = isset($_POST['data']['inface_shlef']) ? Clean::int($_POST['data']['inface_shlef']): null;	
		$inface_ont = isset($_POST['data']['inface_ont']) ? Clean::int($_POST['data']['inface_ont']): null;	
		$debug_mode = isset($_POST['data']['debug_mode']) ? Clean::int($_POST['data']['debug_mode']): null;	
		// PPPoE login, password
		$input_pppoe_login = isset($_POST['data']['input_pppoe_login']) ? Clean::text($_POST['data']['input_pppoe_login']): null;
		$input_pppoe_password = isset($_POST['data']['input_pppoe_password']) ? Clean::text($_POST['data']['input_pppoe_password']): null;
		if(isset($input_pppoe_login) && isset($input_pppoe_password)){
			$original_conf['input_pppoe_login'] = $input_pppoe_login;	
			$original_conf['input_pppoe_password'] = $input_pppoe_password;	
		}
		// IP-Address
		$input_ip_onu = isset($_POST['data']['input_ip_onu']) ? Clean::text($_POST['data']['input_ip_onu']): null;
		if(isset($input_ip_onu) && isset($input_ip_onu)){
			$original_conf['input_ip_onu'] = $input_ip_onu;
		}
		// VoIp
		$input_voip_user = isset($_POST['data']['input_voip_user']) ? Clean::text($_POST['data']['input_voip_user']): null;
		$input_voip_password = isset($_POST['data']['input_voip_password']) ? Clean::text($_POST['data']['input_voip_password']): null;
		if(isset($input_voip_password) && isset($input_voip_user)){
			$original_conf['input_voip_user'] = $input_voip_user;	
			$original_conf['input_voip_password'] = $input_voip_password;	
		}		
		#$original_conf['onu_port'] = $inface_port;
		$original_conf['onu_index'] = $inface_ont;
		$original_conf['slot_index'] = $inface_slot;
		$original_conf['port_index'] = $inface_port;
		$original_conf['shlef_index'] = $inface_shlef;
		$original_conf['onu_mac'] = strtolower($sn);
		$original_conf['onu_sn'] = $sn;
		$original_conf['onu_description'] = $descr;
		$original_conf['onu_name'] = $name;
		$original_conf['interface'] = $inface_shlef.'/'.$inface_slot.'';
		$original_conf['interface_pon'] = $inface_shlef.'/'.$inface_slot.'/'.$inface_port.'';
		$original_conf['interface_onu'] = $inface_shlef.'/'.$inface_slot.'/'.$inface_port.':'.$inface_ont;
		$original_conf['type_pon'] = $pon;
		if (isset($_POST['data']) && is_array($_POST['data'])) {
			foreach ($_POST['data'] as $key => $value) {
				$generate_conf[$key] = Clean::text($value);
			}
		}
		$conf = array_merge($generate_conf, $original_conf);
		$temp_conf = processPMon($currentData, $conf);
		if(isset($temp_conf)){
			$lines = explode("\n", $temp_conf);
			$lines = array_filter($lines, 'trim');
			$array_from_text = array_values($lines);
			$dataSwitch = $db->Fast('switch','*',['id'=>$olt]);
			$telnet = new PMonTelnet($dataSwitch);	
			$err_num = $telnet->err_num;
			if($err_num){	
				$telnet->err('error '.$telnet->descr($err_num));
			}
			$array_from_text = replaceEnterWithSpace($array_from_text);
			$result = $telnet->executeCommands($array_from_text);
			$newData = str_replace(['/', ':;'], ['.', ''], $original_conf['interface_onu']);
			if(isset($original_conf['type_pon']) && $original_conf['type_pon']=='gpon'){
				$data_port = $db->Simple("SELECT * FROM onus WHERE olt = '{$olt}' AND sw_shelf = '{$original_conf['shlef_index']}' AND sw_slot = '{$original_conf['slot_index']}' AND sw_port = '{$original_conf['port_index']}' LIMIT 1");						
				$sqlset = [
					'sw_shelf' => $original_conf['shlef_index'],
					'sw_slot' => $original_conf['slot_index'],
					'sw_port' => $original_conf['port_index'],
					'olt' => $olt,
					'added' => $time, 
					'keyonu' => $original_conf['onu_index'],
					'status' => 1,
					($original_conf['type_pon']=='gpon' ? 'sn' : 'mac') => ($original_conf['type_pon']=='gpon' ? $original_conf['onu_sn'] : $original_conf['onu_mac']),
					'inface' => $original_conf['interface_onu'],
					'name' => (!empty($original_conf['onu_name']) ? $original_conf['onu_name'] : ''),
					'type' => $original_conf['type_pon']
				];
				if (isset($data_port['zte_idport'])) {
					$sqlset['zte_idport'] = $data_port['zte_idport'];
				}					
				if (isset($data_port['portolt'])) {
					$sqlset['portolt'] = $data_port['portolt'];
				}				
				$data = serialize($sqlset);
				$stmt = $pdo->prepare("INSERT INTO switch_temp (name, deviceid, status, tempdata, added, created_note) VALUES (:name, :deviceid, :status, :tempdata, :added, :created_note)");
				$stmt->execute([
					':name'=> 'reger_ont',':deviceid'=> $olt,':status'=> 2,
					':tempdata'=> $data,':added'=> $time,':created_note' => 'module_register'
				]);					
				$finish = $pdo->prepare("INSERT INTO switch_temp (name, deviceid, status, tempdata, added, sub_name, created_note) VALUES (:name, :deviceid, :status, :tempdata, :added, :sub_name, :created_note)");
				$finish->execute([
					':name'=> 'config_ont',':deviceid'=> $olt,':status'=> 1,
					':tempdata'=> $temp_conf,':added'=> $time,':sub_name' => "{$pon}_{$newData}",':created_note' => "module_register"
				]);				
			}
			if(isset($result) && (stripos($result, 'Successful') !== false || stripos($result, 'configuration') !== false )) {
				$logger->init([
					'log'=>'device',
					'type'=>'reg',
					'descr'=>"Register ".$original_conf['interface_onu']." {$sn}",
					'deviceid'=>$olt,'userid'=>$USER['id'],'username'=>$USER['username']]);
			}
			if(isset($debug_mode)){
				$result = addLineBreaks($result);
				echo'<div class="configonu" style="text-align:left;">'.$result.'</div></br><a href="?do=regonu&act=checker">Update</a>';
				die;
			}
			if(isset($result) && (stripos($result, 'Successful') !== false || stripos($result, 'configuration') !== false )) {
				echo'ok';
			}else{
				echo'err';
				echo $result;
				$filerror = $tempfolder."olt_{$olt}_onu_{$pon}_{$newData}.errorreger";
				@file_put_contents($filerror,$result);
			}
		}		
		die;
	break;
	case 'start';
		$matchingFiles = '';	
		$olt = isset($_POST['olt']) ? Clean::int($_POST['olt']): null;	
		$ont = isset($_POST['ont']) ? Clean::int($_POST['ont']): null;	
		$index = isset($_POST['index']) ? Clean::text($_POST['index']): null;	
		$infoonu = isset($_POST['infoonu']) ? Clean::text($_POST['infoonu']): null;	
		$pon = isset($_POST['pon']) ? Clean::text($_POST['pon']): null;	
		$select_vlan = isset($_POST['select_vlan']) ? Clean::int($_POST['select_vlan']): 2;	
		$matchingFiles = findFilesWithId($olt);
		$formid = $olt.$ont;
		$inface = parseDataInface($index);
		if(isset($matchingFiles) && isset($inface)){
			$free = free_number($index, $olt, $pon);
			$firstValue = array_shift($free);
			echo'<td colspan="6" id="result-reger-'.$formid.'"><form id="formregonu-'.$formid.'"><div class="formregonu" >';
			echo'<input name="olt" type="hidden" value="'.$olt.'"><input name="pon" type="hidden" value="'.$pon.'"><input name="pon" type="hidden" value="'.$pon.'"><input name="type_vlan" type="hidden" value="'.$select_vlan.'">';
			// input serial name
			echo'
			<div class="pole">
				<div class="balance">
					<b>Mac_Sn</b><input class="forminput" name="sn" value="'.$infoonu.'" style="width:300px;">
				</div>
			</div>
			<div class="pole">
				<div class="balance">
					<b>Onu name</b><input class="forminput" name="name" style="width:300px;" autocomplete="off" value="ont_name">
				</div>
			</div>
			<div class="pole">
				<div class="balance">
					<b>Onu description</b><input class="forminput" name="description" style="width:300px;" autocomplete="off" value="ont_descr">
				</div>
			</div>';
			// inface onu
			echo'<div class="pole">
				<div>
					<b>'.$pon.'</b>
				</div><div><b>_</b><input required class="forminput" name="inface_shlef" value="'.$inface['shlef'].'"></div><div><b>/</b><input required class="forminput" name="inface_slot" value="'.$inface['slot'].'"></div><div><b>/</b><input required class="forminput" name="inface_port" value="'.$inface['port'].'"></div><div><b>:</b><input required class="forminput" name="inface_ont" id="inface_ont" value="'.$firstValue.'"></div>';
			// repemini
			echo'<div class="free_index"><span class="free_spaces">';
			foreach ($free as $value) {
				echo '<span class="inface_ont" onclick="freeindex(' . $value . ')">' . $value . '</span>';
			}
			echo'</span></div>';
			echo'</div>';
			// select template
			echo'
			<div class="pole">
				<div>
					<b>Select template</b><select name="template" id="templateSelect" class="inputonuselect"><option value="0"></option>';
			foreach ($matchingFiles as $fileName) {
				$fileNames = str_replace('.register', '',$fileName);
				echo'<option value="'.$fileNames.'">'.$fileNames.'</option>';
			}
			echo'</select></div></div><div id="loadtemplate"></div>';
			echo getlelectTP($formid,$select_vlan,$olt);
			echo'<div class="pole viewblock" style="display:none;">
			<span onclick="regeronu('.$formid.')" class="btnregonu">Register ONU</span>			
			<span class="m_left_20"><input type="checkbox" id="debug_mode" name="debug_mode"/> Display terminal result OLT</span>
			</div>';
			echo'</div></form></td>';
		}else{
			echo'empty';
		}
		die;
	break;	
	case 'loadtemplate';	
		if (isset($_POST['template'])) {
			$template = isset($_POST['template']) ? Clean::text($_POST['template']): null;
			$formid = isset($_GET['formid']) ? Clean::int($_GET['formid']): null;
			$type_vlan = isset($_GET['data']) ? Clean::int($_GET['data']): 2;
			$olt = isset($_GET['olt']) ? Clean::int($_GET['olt']): null;
			echo'<input name="shablomregister" type="hidden" value="'.$template.'">';
			echo'<div class="poles">';
			$peremini = arraytemplatevalue($template);
			if (isset($peremini)){
				foreach($peremini as $name => $options) {
					if (isset($type_vlan) && !($name == 'input_pppoe_login' || $name == 'input_voip_user'  || $name == 'input_voip_password' || $name == 'input_pppoe_password' || $name == 'input_ip_onu')) {
						if ($type_vlan == 1 && isset($name) && $name != 'vlan') {
							echo "<div data-name='$name'><b>$name</b>";
							echo "<span><select name='$name' id='$name' class='inputonuselect'>";
							foreach ($options as $option) {
								echo "<option value='$option'>$option</option>";
							}
							echo "</select></span></div>";
						} elseif ($type_vlan == 2) {
							echo "<div data-name='$name'><b>$name</b>";
							echo "<span><select name='$name' id='$name' class='inputonuselect'>";
							foreach ($options as $option) {
								echo "<option value='$option'>$option</option>";
							}
							echo "</select></span></div>";
						}
					}
					if ($name == 'input_ip_onu' || $name == 'input_pppoe_login' || $name == 'input_voip_password'  || $name == 'input_voip_user' || $name == 'input_pppoe_password') {
						echo "
							<div data-name='$name'>
								<b>".($name == 'input_ip_onu' || $name == 'input_pppoe_login' || $name == 'input_voip_user' || $name == 'input_voip_password' || $name == 'input_pppoe_password' ? 
								"<img src='../style/img/{$name}.png'>":'').$input_lang[$name]."</b>
								<span>
									<input type='text' name='$name' id='$name' class='inputonuselect' placeholder='Input ".$input_lang[$name]."'>
								</span>";
						echo "</div>";
					}
					
				}
			}
			if(isset($type_vlan) && $type_vlan==1){
				$dataswitch = $db->Fast('switch','id,oidid,netip,snmpro',['id'=>$olt]);
				if(isset($dataswitch['id']) && !empty($dataswitch['id'])){
					if($dataswitch['oidid']==6){
						$all_vlan = zte6_get_gpon_all_vlan($dataswitch['netip'],$dataswitch['snmpro'],$dataswitch['id']);
					}else{
						$all_vlan = zte3_get_gpon_all_vlan($dataswitch['netip'],$dataswitch['snmpro'],$dataswitch['id']);						
					}
				}
			}else{
				if (isset($peremini) && isset($peremini['vlan'])) {
					
				}else{
					echo'<div data-name="vlan"><b>vlan</b><input class="vlaninput" name="vlan" id="vlan"></div>';
				}
			}
			if(isset($all_vlan) && $type_vlan==1){
				echo '<div data-name="vlan"><b>vlan</b><select name="vlan" id="vlan" class="inputonuselect">';
				if(isset($all_vlan)){
					foreach ($all_vlan as $vl) {
						echo'<option value="'.$vl['vlan'].'">'.$vl['vlan'].'</option>';
					}
					echo'</select></div>';
				}
			}
			echo'</div>';
		}
		die;
	break;	
	case 'allvlan';		
		$id = isset($_GET['id']) ? Clean::int($_GET['id']): null;
		if(isset($id)){
			$dataswitch = $db->Fast('switch','*',['id'=>$id]);
			if($dataswitch['oidid']==6){
				print_R(zte3_get_gpon_all_vlan($dataswitch['netip'],$dataswitch['snmpro'],$id));
			}else{
				print_R(zte3_get_gpon_all_vlan($dataswitch['netip'],$dataswitch['snmpro'],$id));
			}
		}		
		die;
	break;	
	case 'get';		
		$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
		if(isset($id)){
			$dataswitch = $db->Fast('switch','*',['id'=>$id]);
			if($access->get('dev'.$dataswitch['id'])){
				echo $Reger->get_onu($dataswitch);
			}
		}
		die;
	break;
	case 'checker';	
		if ($access->get('great_template') ){	
			$speedbar .= '<a class="brmhref" href="/?do=regonu&act=template"><i class="fi fi-rr-layers"></i>Template Management</a>';
		}
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>ONU Management</span>';	
		$sql = "SELECT s.* FROM checkaccess a JOIN switch s ON CONCAT('dev', s.id) = a.types WHERE a.uid = :uid AND s.device = 'olt'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':uid' => $USER['id']]);
		$sql_olt_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($sql_olt_list)) {
			foreach($sql_olt_list as $swid => $switch){
				$result .='<div id="olt_' . $switch['id'] . '" class="reger-switch" data-id="' . $switch['id'] . '">';
				$result .='<div class="reger-status" id="status_' . $switch['id'] . '"><span class="reger-loader">' . $switch['place'] . '</span><img style="height:9px;" src="../style/img/ajax-loader-big.gif" /></div>';
				$result .='</div>';
			}
		}else{
			
		}
	break;
	default:
}
$metatags = [
	'title'=>'ONU Management',
	'description'=>'ONU Management',
	'page'=>'regonu'
];
$tpl->load_template('regonu/main.tpl');
$tpl->set('{script}',$script);
$tpl->set('{speedbar}',$speedbar);
$tpl->set('{result}',(isset($result)?$result:$tpl->result['result']));
$tpl->compile('content');
$tpl->clear();
?>