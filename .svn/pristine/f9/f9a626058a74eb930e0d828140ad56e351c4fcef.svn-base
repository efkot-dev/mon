<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
switch($view){
	case'detail':
		$notes = isset($_GET['notes']) ? Clean::int($_GET['notes']): null;
		if(is_valid_id($notes)){
			$row = $db->Fast('notes','*',['id' => $notes]);
			$tplRes .= '
			<div class="card m0">
				<div class="name_h1">'.$row['title'].'</div>
				<div class="name_detail">
				<a href="/?do=detail&act=olt&page=note&id='.$id.'&view=edit&notes='.$row['id'].'">Редагувати</a>
				<a href="/?do=detail&act=olt&page=note&id='.$id.'&view=delet&notes='.$row['id'].'">Видалити</a></div>
				'.bbcode($row['message']).'
			</div>';
		}
	break;	
	case 'delet': 		
		$notes = isset($_GET['notes']) ? Clean::int($_GET['notes']): null;
		if(is_valid_id($notes)){
			$db->SQLdelete('notes',['id' => $notes]);
		}
		$go->go('/?do=detail&act=olt&page=note&id='.$id);
		exit;
	break;	
	case 'edit':
		$notes = isset($_GET['notes']) ? Clean::int($_GET['notes']): null;
		if(is_valid_id($notes)){
			$datanotes = $db->Fast('notes','*',['id' => $notes]);
		$tplRes .= '
		<div class="card m0">
			<form action="/?do=send" method="post" id="formadd" enctype="multipart/form-data">
			<input name="act" type="hidden" value="updatenote">
			<input name="id" type="hidden" value="'.$id.'">
			<input name="notes" type="hidden" value="'.$datanotes['id'].'">
			<div class="polebtn">
				<input style="width:90%;" name="name" class="input1" type="text" value="'.$datanotes['title'].'">
			</div>
			<div class="polebtn">
				<div class="bbdcode">
					<div onclick="insertTag(\'b\')"><img src="../style/bbcodes/b.png"></div>
					<div onclick="insertTag(\'code\')"><img src="../style/bbcodes/code.png"></div>
				</div>
				<textarea id="content" name="content" class="input_note">'.$datanotes['message'].'</textarea>
			</div>
			<div class="polebtn">
				<button type="submit" form="formadd" value="submit">'.$lang['update'].'</button>
			</div>
			</form>
		</div>';
		}			
	break;	
	case'add':
		$tplRes .= '
		<div class="card m0">
			<form action="/?do=send" method="post" id="formadd" enctype="multipart/form-data">
			<input name="act" type="hidden" value="savenote">
			<input name="id" type="hidden" value="'.$id.'">
			<div class="polebtn">
				<input style="width:90%;" name="name" class="input1" type="text">
			</div>
			<div class="polebtn">
				<div class="bbdcode">
					<div onclick="insertTag(\'b\')"><img src="../style/bbcodes/b.png"></div>
					<div onclick="insertTag(\'code\')"><img src="../style/bbcodes/code.png"></div>
				</div>
				<textarea id="content" name="content" class="input_note"></textarea>
			</div>
			<div class="polebtn">
				<button type="submit" form="formadd" value="submit">'.$lang['save'].'</button>
			</div>
			</form>
		</div>';
	break;
	default:
	$tplRes .='
		<script>
			load_note('. $dataSwitch['id'].');
		</script>
		<div id="load_note"></div>';
}
?>