<?php
if (!defined('PONMONITOR') && !defined('FIBER')){
	die('Hacking attempt!');
}
$pontree = (isset($_POST['pontree'])?Clean::int($_POST['pontree']):null);
$myicon_file = (isset($_POST['myicon_file'])?Clean::text($_POST['myicon_file']):null);
if(!empty($myicon_file)){
    $sqlinsert['myicon'] = $myicon_file;	
}
$tree = (isset($_POST['tree'])?Clean::int($_POST['tree']):null);
$id = (isset($_POST['id'])?Clean::int($_POST['id']):null);
$getponelement = $db->Fast('ponelement','*',['id'=>$id]);
$old_pontree = $db->Fast('pontree','*',['id'=>$tree]);
$sqlinsert['description'] = isset($_POST['description']) ? Clean::text($_POST['description']): null;
$sqlinsert['name'] = isset($_POST['name']) ? Clean::text($_POST['name']): null;
if (isset($_POST['types']) && !empty($_POST['types'])) {
    $types = (isset($_POST['types']) ? Clean::int($_POST['types']) : null);
    if ($types > 0) {
        $sqlinsert['types'] = $types;
    }	
}
if(isset($_POST['lan']) && !empty($_POST['lan']) && !empty($_POST['lon'])){
$sqlinsert['lan'] = isset($_POST['lan']) ? Clean::text($_POST['lan']): null;
$sqlinsert['lon'] = isset($_POST['lon']) ? Clean::text($_POST['lon']): null;
}
if(isset($pontree) && $pontree>0){
$sql_pontree = $db->Fast('pontree','*',['id'=>$pontree]);
$sqlinsert['unit_id'] = $sql_pontree['unit_id'];
$sqlinsert['tree'] = $sql_pontree['id'];
}
if(!empty($getponelement['id'])) {		
if(isset($sqlinsert)){
$sqlupdate = array('pontree' => $sql_pontree['id'],'ponelement' => $getponelement['id']);
$db->SQLupdate('ponelement',$sqlinsert,['id'=>$getponelement['id']]);
$db->SQLupdate('onusdata',$sqlupdate,['pontree'=>$old_pontree['id'], 'ponelement'=>$getponelement['id']]);
}
$go->go('/?do=fiber&act=viewtree&id='.$sql_pontree['id']);
}
$go->redirect('fiber');
exit;
?>