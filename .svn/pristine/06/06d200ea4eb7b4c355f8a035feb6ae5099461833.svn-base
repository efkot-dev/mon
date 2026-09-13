<?php
define('AJAX',true);
define('ROOT_DIR',substr( dirname( __FILE__),0,-5));
define('ENGINE_DIR',ROOT_DIR.'/inc/');	
require_once ENGINE_DIR.'ajax.php';
$getTypeForm='';
$id = isset($_POST['id']) ? Clean::int($_POST['id']): null;
$speed = isset($_POST['speed']) ? Clean::int($_POST['speed']): null;
$type = isset($_POST['type']) ? Clean::text($_POST['type']): null;
if(isset($id) && $id>0 && isset($speed) && $speed>100 && $access->get('edit_pir_sla')){
	okno_title('Зміна швидкості');
	echo'<form action="/?do=service" method="post" id="formadd">';
	echo'<input name="id" type="hidden" value="'.$id.'">';
	echo'<input name="type" type="hidden" value="'.$type.'">';
	echo'<input name="act" type="hidden" value="speed">';
	if($type=='upir'){
		// 
		$pole_device = '<b>UPSTREAM</b> Вихідна швидкість';
		$name_device = 'Pir';
	}else{
		$pole_device = '<b>DOWNSTREAM</b> Вхідна швидкість';
		$name_device = 'Pir';
	}
	$tpl_speed = '
	<font color="green">100000</font> - 100 Mbps<br>
	<font color="orange">300000</font> - 300 Mbps<br>
	<font color="blue">600000</font> - 600 Mbps<br>
	<font color="red">950000</font> - 950 Mbps
	';
	echo form(['name'=>'Тип','descr'=>'Обмеження на швидкість передачі','pole'=>$pole_device]);
	echo form(['name'=>$name_device,'descr'=>'Максимальна швидкість передачі','pole'=>'<input name="speed" class="input1" id="speed" type="text" value="'.$speed.'">']);
	echo form(['name'=>'Швидкості','descr'=>'Приклади швидкості','pole'=>$tpl_speed]);
	echo form(['name'=>'Write all','descr'=>'Збереженн всієї конфігурації','pole'=>'<input class="checkcss" name="writeall" type="checkbox">']);
	echo'</form>';
	echo'<div class="polebtn"><button type="submit" form="formadd" value="submit">'.$lang['add'].'</button></div>';
	okno_end();
}
?>
