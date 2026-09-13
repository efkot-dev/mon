<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!$access->get('group')) 
	$go->redirect('main');
$devicelist ='';
$addgroup ='';
$moderpanel ='';
$id = isset($_GET['id']) ? Clean::int($_GET['id']) : null;
if($id)
	$datagroup = $db->Fast($PMonTables['gr'],'*',['id'=>$id]);
if(!empty($datagroup['id'])){
	$metatags = array('title'=>$lang['title_group'],'description'=>$lang['descr_group'],'page'=>'group');
	$tpl->load_template('group/detail.tpl');
	$tpl->set('{id}',$datagroup['id']);
	$tpl->set('{name}',$datagroup['name']);
	$SQLDevice = $db->Multi($PMonTables['switch'],'*',['groups'=>$datagroup['id']]);
	if(count($SQLDevice)){
		$devicelist .='<div class="group-list-detail">';
		foreach($SQLDevice as $Device){
			$devicelist .='<div class="grlist"><a href="/"><h2>'.$Device['place'].'</h2></a><span class="netip">'.$Device['netip'].'</span><span class="model">'.$Device['inf'].' '.$Device['model'].'</span><span class="btn-del-gr"><span><img onclick="ajaxcore(\'delgroupdev\','.$Device['id'].');" src="../style/img/cross-mark.png"></span></span></div>';
		}
		$devicelist .='</div>';
	}else{
		$devicelist = infdisplay($lang['emptygr']);
	}
	if(!empty($USER['class']) && $USER['class']>=4){
		$moderpanel = '<a href="#" onclick="ajaxcore(\'editgroup\','.$datagroup['id'].')">'.$lang['edit'].'</a>';
		$moderpanel .= '<a href="#" onclick="ajaxcore(\'delgroup\','.$datagroup['id'].')">'.$lang['delet'].'</a>';
	}
	$tpl->set('{device}',$devicelist);
	$tpl->set('{moderpanel}',$moderpanel);
	$tpl->compile('group');
	$tpl->clear();	
	$speedbar = '<div id="onu-speedbar"><a class="brmhref" href="/?do=group"><i class="fi fi-rr-folder"></i>'.$lang['group'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$datagroup['name'].'</span></div>';
}else{
	$metatags = array('title'=>$lang['title_group'],'description'=>$lang['descr_group'],'page'=>'group');
	$sqlgroups = getGroupsAll(); 
	if(count($sqlgroups)){
		$addgroup = '<div class="navigation mbottom20"><span class="deviceadd" onclick="ajaxcore(\'addgroup\');">'.$lang['addgroup'].'</span></div>';
		foreach($sqlgroups as $group){
			$tpl->load_template('group/list.tpl');
			$tpl->set('{id}',$group['id']);
			$tpl->set('{name}',$group['name']);
			$SQLcountGroups = $db->Multi('switch','*',['groups'=>$group['id']]);
			$tpl->set('{count}',(count($SQLcountGroups)?'<span>'.count($SQLcountGroups).'</span>':''));
			$tpl->set('{url}','/?do=group&id='.$group['id']);
			$tpl->compile('group');
			$tpl->clear();				
		}
	}else{
		$addgroup = '<div class="navigation mbottom20"><span class="deviceadd" onclick="ajaxcore(\'addgroup\');">'.$lang['addgroup'].'</span></div>';
		$tpl->load_template('group/empty.tpl');
		$tpl->set('{result}','');
		$tpl->compile('group');
		$tpl->clear();	
	}	
	$speedbar = '<div id="onu-speedbar"><a class="brmhref" href="/?do=group"><i class="fi fi-rr-folder"></i>'.$lang['group'].'</a><span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['title_group'].'</span></div>';
}
$tpl->load_template('group/main.tpl');
$tpl->set('{result}',$tpl->result['group']);
$tpl->set('{speedbar}',$speedbar);
$tpl->set('{addgroup}',$addgroup);
$tpl->compile('content');
$tpl->clear();
?>