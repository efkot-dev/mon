<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$speedbar = '';
$result = '';
$id = (isset($_GET['id']) ? Clean::int($_GET['id']) : null);
if (!$confPMon['MONITORDC'] && empty($confPMon['MONITORDC'])) {
	$go->redirect('main');
}
$speedbar .= '<a class="brmhref" href="/?do=monitordc"><i class="fi fi-rr-car-battery"></i>Monitor DC (Huawei,Zte)</a>';
switch($act){
	case 'add': 
		$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$lang['newping3'].'</span>';
		$listgroup = '';
		$listlocation = '';
		$result .= '<form action="/?do=send" method="post" id="formadd"><input name="act" type="hidden" value="savemonitordc">';
		$typean = '<select class="select" name="dc">
			<option value="huawei">Huawei</option>
			<option value="zte">ZTE</option>
			<option value="bdcom">BDCOM</option>
			</select>';
		$result .= formpage(['img'=>'addconnect.png','name'=>'OLT','descr'=>'Monitor mode olt','pole'=>$typean]);		
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['ip'],'descr'=>$lang['ipdescr'],'pole'=>'<input style="width:33%;" name="netip" class="input1" type="text">']);
		$result .= formpage(['img'=>'addconnect.png','name'=>'Community','descr'=>'Snmp community','pole'=>'<input style="width:33%;" name="snmpro" class="input1" type="text">']);
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['oid_gpon_name'],'descr'=>$lang['oid_gpon_name_desc'],'pole'=>'<input style="width:99%;" name="name" class="input1" type="text">']);
		$typebattery = '<select class="select" name="typebattery"><option value="12">12</option><option value="24">24</option><option value="48">48</option><option value="72">72</option></select>';
		// OID
		$energystatus  = '<select class="select" name="energystatus"><option value="yes">ON</option><option value="no">NO</option></select>';
		$result .= formpage(['img'=>'addconnect.png','name'=>$lang['types_220'],'descr'=>$lang['types_220i'],'pole'=>$energystatus]);
		$group = getListGroup();
		if(is_array($group)){
			foreach($group as $gr){
				$listgroup .= '<option value="'.$gr['id'].'">'.$gr['name'].'</option>';
			}
			$result .= formpage(['img'=>'folders.png','name'=>$lang['group'],'descr'=>$lang['title_group'],'pole'=>'<select class="select" name="group" id="group"><option value="0"></option>'.$listgroup.'</select>']);
		}
		$location = getListLocations();
		if(is_array($location)){
			foreach($location as $loc){
				$listlocation .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
			}
			$result .= formpage(['img'=>'m6.png','name'=>$lang['location'],'descr'=>$lang['getlocation'],'pole'=>'<select class="select" name="location" id="location"><option value="0"></option>'.$listlocation.'</select>']);
		}
		$result .= '<div class="batterystatus">
			<div class="batblock"><span class="col0">0%</span><span><input name="status0" type="text"></span></div>		
			<div class="batblock"><span class="col20">20%</span><span><input name="status20" type="text"></span></div>		
			<div class="batblock"><span class="col40">40%</span><span><input name="status40" type="text"></span></div>		
			<div class="batblock"><span class="col60">60%</span><span><input name="status60" type="text"></span></div>		
			<div class="batblock"><span class="col80">80%</span><span><input name="status80" type="text"></span></div>		
			<div class="batblock"><span class="col100">100%</span><span><input name="status100" type="text"></span></div>
		</div>';
		$result .= '<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['save'].'</button></form>';
	break;		
	case 'del': 	

	break;		
	case 'view': 
		$getMonitorDev = $db->Fast('mon_device','*',['id'=>$id]);
		if(!empty($getMonitorDev['id'])){
			$result .='<table class="view-ping3"><tbody><tr><td class="ping3-img"><div class="lg"><img src="../style/img/'.$getMonitorDev['dc'].'.png"></div></td>';
			$result .='<td class="ping3-info"><h2>'.$getMonitorDev['name'].'</h2>';
			$result .='<b>IP:</b> '.$getMonitorDev['netip'].'<br> <b>'.$lang['v'].':</b> '.$getMonitorDev['volt'].'V';
			$result .='</td></tr></tbody></table>';
			$result .='<div id="viewping3"><div class="contentping">' . viewGraphPing3($id,$getMonitorDev['dc']).'</div></div>';
			$speedbar .= '<span class="brmspan"><i class="fi fi-rr-angle-left"></i>'.$getMonitorDev['name'].'</span>';
		}
	break;	
	default:
		$result .='<div id="ping3">';
		$sqllistping3 = $db->Multi('mon_device');
		if(is_array($sqllistping3) && count($sqllistping3)>0){
			foreach($sqllistping3 as $ping3){
				$result .='<a href="/?do=monitordc&act=view&id='.$ping3['id'].'" class="battery">
					<div class="battery-img">
					'.monitorPing3img($ping3).'
					<div class="battery-volt">'.(!empty($ping3['volt'])?$ping3['volt']:0).'v</div>
					</div>
					<div class="battery-name">'.$ping3['name'].'</div>
				</a>';
			}
		}else{
			$go->go('/?do=monitordc&act=add');
		}
		$result .='</div>';	
}
$metatags = array('title'=>'battery','description'=>'battery','page'=>'battery');
$tpl->load_template('battery/page.tpl');
$tpl->set('{speedbar}',$speedbar);
$tpl->set('{result}',$result);
$tpl->compile('content');
$tpl->clear();
?>
