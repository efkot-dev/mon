<?php
if (!defined('PONMONITOR')){
	die('System Error Attempt!');
}
class Route{
	public function redirect($type, $page = false){
		$act = $this->type($type);
		match ($act) {
			'fiber' => $this->go('/?do=fiber&act=map'),
			'unit' => $this->go('/?do=pon&act=unit'),
			'location' => $this->go('/?do=location'),
			'config' => $this->go('/?do=config'),
			'group' => $this->go('/?do=group'),
			'sklad' => $this->go('/?do=sklad'),
			'device' => $this->go('/?do=device'),
			'users' => $this->go('/?do=users'),
			'billing' => $this->go('/?do=billing'),
			'pondog' => $this->go('/?do=pondog'),
			'battery' => $this->go('/?do=battery'),
			'monitordc' => $this->go('/?do=monitordc'),
			'calendar' => $this->go('/?do=calendar'),
			'vlan' => $this->go('/?do=vlan'),
			'ping3' => $this->go('/?do=ping3'),
			'operator' => $this->go('/?do=operator'),
			'oid' => $this->go('/?do=oid'),
			'main' => $this->go('/index.php'),
			default => $this->go('/index.php')
		};
	}	    
	public function type($type){
		return $type;
	}	
	public function url($url){
		header('location: /?do='.$url);
		exit;
	}		
	public function go($url){
		header_remove();
		header('Location: '.$url);
		exit;
	}	
}
?>