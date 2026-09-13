<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
$img = isset($_GET['img']) ? Clean::int($_GET['img']) : null;
$fn = isset($_GET['fn']) ? Clean::text($_GET['fn']) : null;
if(isset($fn) && $fn=='delet' && $dataSwitch['gallery'] === 'yes' && $access->get('gallerydevice') && $page=='viewgallery' && isset($img) && $img>0){
	$sql_gallery_photo = $db->Simple("SELECT * FROM switch_photo WHERE id = '{$img}'");
	if(!empty($sql_gallery_photo['deviceid'])){
		$db->SQLdelete('switch_photo',['id' => $sql_gallery_photo['id']]);
		@unlink('file/photo/'.$sql_gallery_photo['photo']);
	}
	$go->go('/?do=detail&act=' . $dataSwitch['device'] . '&page=gallery&id='.$sql_gallery_photo['deviceid'].'');
	exit;
}
if ($dataSwitch['gallery'] === 'yes' && $access->get('gallerydevice') && $page=='viewgallery' && isset($img) && $img>0){ 
	$sql_gallery_photo = $db->Simple("SELECT * FROM switch_photo WHERE id = '{$img}'");
	if(isset($sql_gallery_photo['id']) && $sql_gallery_photo['id']>0){
		$tplRes .= '
		<div class="block_galler_view">
		<div class="block_galler_view_img">
			<img src="/?do=thumb&type=photo&img=' . $sql_gallery_photo['photo'] . '&s=1">
		</div>
		<div class="block_galler_view_content">
			<div class="block_gall">
			<h2>'.$sql_gallery_photo['name'].'</h2>
			<div class="panel_olt">
			<span class="photo_added">'.$sql_gallery_photo['added'].'</span>
			<a href="/?do=detail&act=' . $dataSwitch['device'] . '&page=viewgallery&id=' . $id . '&img=' . $sql_gallery_photo['id'] . '&fn=delet">Delete</a>
			</div>
			'.$sql_gallery_photo['note'].'
			</div>
		</div>
		</div>';
	}	
}
if ($dataSwitch['gallery'] === 'yes' && $access->get('gallerydevice') && $page === 'gallery') { 
    $sql_gallery_photo = $db->SimpleWhile("SELECT * FROM switch_photo WHERE deviceid = '{$id}' ORDER BY id DESC");
        $tplRes .= '
            <div class="addedphoto">
                <a href="#" onclick="ajaxaddphoto(\'' . $id . '\');">Add photo</a>
            </div>
            <div class="gallery">'; 
	if (!empty($sql_gallery_photo)) {			
        foreach ($sql_gallery_photo as $foto) {
            $tplRes .= '
                <div class="photo">
                    <div class="timed_block">
                        <div class="btn_style_im_added"><img src="../style/img/task_calendar.png"><span>' . $foto['added'] . '</span></div>
                        <a href="../file/photo/' . $foto['photo'] . '" target="_blank" class="btn_style_im"><img src="../style/img/task_zoom.png"></a>
                    </div>
                    <div class="img">
                        <a href="/?do=detail&act=' . $dataSwitch['device'] . '&page=viewgallery&id=' . $id . '&img=' . $foto['id'] . '">
                            <img src="/?do=thumb&type=photo&img=' . $foto['photo'] . '">
                        </a>
                    </div>
                </div>';
        }    
	}		
        $tplRes .= '</div>';

}
if($page=='nomdu' && $dataSwitch['monitor']=='yes'){
	$tplRes .= "<div id=\"nomdu\"></div>
	<script>nomdu(".$dataSwitch['id'].");</script>
	";	
}
?>