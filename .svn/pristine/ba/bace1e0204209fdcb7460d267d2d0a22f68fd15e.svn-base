<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$speedbar = '';
$speedbar_block = '';
$content = '';
$content_head = '';
$clock = date('Y-m-d H:i:s');
$cacheFilePath_1 = CACHE_DIR . CACHE_FILE_NAME . '.json';
if (isset($USER['class']) && $USER['class']>=6 && $access->get('setup')){
	switch($act){
		case 'save':
			$note = isset($_POST['note']) ? Clean::text($_POST['note']) : 'n/a';		
			$name = isset($_POST['name']) ? Clean::text($_POST['name']) : false;		
			$value = isset($_POST['value']) ? Clean::text($_POST['value']) : false;		
			if(isset($name) && isset($value) && isvalidtext($name) && isvalidtext($value)){
				$upperCaseStr = trim(strtoupper(sql_checker($name)));
				$value = trim($value);
				$sql_checker = $db->Simple("SELECT count(id) as pmon FROM pmonini WHERE name = '".$upperCaseStr."'");
				if(isset($upperCaseStr) && isset($value) && isset($sql_checker) && empty($sql_checker['pmon'])){
					$db->SQLinsert('pmonini',['name'=>$upperCaseStr,'value'=>$value,'note'=>$note,'added'=>$clock]);				
					$cacheType = defined('CACHE') ? CACHE : 'file';
					if($cacheType=='file'){
						if (file_exists($cacheFilePath_1)) {
							@unlink($cacheFilePath_1);
						}
					}elseif($cacheType=='redis'){
						$cacheKey = 'config:' . CACHE_FILE_NAME;
						$redis->del($cacheKey);
					}
				}
			}		
			$go->go('/?do=pmon');	
		break;	
		case 'delet':		
			$id = isset($_GET['id']) ? Clean::int($_GET['id']) : 0;	
			if($id>0){
				$db->SQLdelete('pmonini',['id' => $id]);
			}
			$go->go('/?do=pmon');			
		break;	
		case 'update':	
			$id = isset($_POST['id']) ? Clean::int($_POST['id']) : 0;		
			$note = isset($_POST['note']) ? Clean::text($_POST['note']) : 'n/a';		
			$name = isset($_POST['name']) ? Clean::text($_POST['name']) : false;		
			$value = isset($_POST['value']) ? Clean::text($_POST['value']) : false;		
			if(isset($name) && isset($value) && isvalidtext($name) && isvalidtext($value) && isset($id) && $id>0){
				$upperCaseStr = strtoupper(sql_checker($name));
				$sqlinsert['name'] = $name;
				if(isset($note))
					$sqlinsert['note'] = $note;
				$sqlinsert['value'] = $value;
				$sqlinsert['added'] = date('Y-m-d H:i:s');
				$db->SQLupdate('pmonini',$sqlinsert,['id'=>$id]);
				$cacheType = defined('CACHE') ? CACHE : 'file';
				if($cacheType=='file'){
					if (file_exists($cacheFilePath_1)) {
						@unlink($cacheFilePath_1);
					}
				}elseif($cacheType=='redis'){
					$cacheKey = 'config:' . CACHE_FILE_NAME;
					$redis->del($cacheKey);
				}
			}
			$go->go('/?do=pmon');			
		break;		
		default:	
			$metatags = ['title'=>$lang['pmon_list_var'],'description'=>$lang['pmon_list_var'],'page'=>'pmonini'];
			$speedbar .='<a class="brmhref" href="/?do=main"><i class="fi fi-rr-angle-left"></i>'.$lang['main'].'</a>
				<a class="brmhref" href="/?do=operator"><i class="fi fi-rr-angle-left"></i>'.$lang['services'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['pmon_list_var'].'</span>';
			$speedbar_block .= '<div id="onu-speedbar">'.$speedbar.'</div>';
			$sql_pmonini = $db->SimpleWhile("SELECT * FROM pmonini ORDER BY added DESC");
			$content .= '<div id="ajax"></div><table class="resp-tab"><thead><tr><th>'.$lang['name'].' <a href="#" class="new_pmonini" onclick="ajaxcore(\'pmonini\',1)" >['.$lang['addeds'].']</a></th><th style="width: 50%;">'.$lang['value'].'</th><th>'.$lang['opis'].'</th><th></th></tr></thead><tbody>';
			if(isset($sql_pmonini) && count($sql_pmonini) > 0){
				foreach($sql_pmonini as $id => $pm){				
					$content .= '<tr><td class="td_url"><a href="#" onclick="ajaxcore(\'editpmonini\','.$pm['id'].')">'. $pm['name'].'</a></td><td>'. $pm['value'].'</td><td>'. $pm['note'].'</td><td ><a class="panel_house rr_1" href="/?do=pmon&act=delet&id='.$pm['id'].'">X</a></td></tr>';
				}
			}
			$content .= '</table>';
	}
	$tpl->load_template('taskman.tpl');
	$tpl->set('{speedbar}',$speedbar_block);
	$tpl->set('{content_head}',$content_head);
	$tpl->set('{content}',$content);
	$tpl->compile('content');
	$tpl->clear();
}else{
	$go->go('/?do=main');	
}
?>