<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
function savePhoto($file,$name){
	global $config;
	$min_wid = '380x640';
	$big_wid = '280x440';
	$quality = 100;
	$crop = true;
	if( !empty( $file ) ){
		$DIR_IMG_BIG = ROOT_DIR . '/file/photo/';
		$DIR_IMG_MIN = ROOT_DIR . '/file/photo/small/';
		$DIR_IMG_TMP = ROOT_DIR . '/file/photo/tamp/';
		if( !is_dir( $DIR_IMG_BIG ) ) mkdir( $DIR_IMG_BIG, 0777 ); chmod($DIR_IMG_BIG, 0777);
		if( !is_dir( $DIR_IMG_MIN ) ) mkdir( $DIR_IMG_MIN, 0777 ); chmod($DIR_IMG_MIN, 0777);
		if( !is_dir( $DIR_IMG_TMP ) ) mkdir( $DIR_IMG_TMP, 0777 ); chmod($DIR_IMG_TMP, 0777);
		// Detect mime-type from the uploaded file to avoid trusting the original extension.
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($file['tmp_name']);
		$allowed = [
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
		];
		if (!isset($allowed[$mime])) {
			return null;
		}

		$safeName = preg_replace('/[^a-zA-Z0-9_\\-]/', '_', (string)$name);
		$ext = $allowed[$mime];
		$tmpName = time() . '_' . $safeName . '.' . $ext;
		$tmpPath = $DIR_IMG_TMP . $tmpName;
		if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
			return null;
		}

		$FILE_UP = $tmpName;
		$CropImg = $tmpPath;
		# Класс для работы с Изображением
		$resizeImg = new resize($CropImg);
		# Кроп большой картинки
		$ExWidBigImg = explode('x', $big_wid);
		if( isset( $ExWidBigImg ) AND $crop ){
			$resizeImg->resizeImage($ExWidBigImg[0], $ExWidBigImg[1], 'crop');
			if( $quality ){
				$resizeImg->saveImage($DIR_IMG_BIG.$FILE_UP, $quality);
			}else{
				$resizeImg->saveImage($DIR_IMG_BIG.$FILE_UP, 80);
			}
		}else{
			copy($tmpPath,$DIR_IMG_BIG.$FILE_UP);
		}
		# Кроп маленькой картинки
		$ExWidMinImg = explode('x', $min_wid);
		if( isset( $ExWidMinImg ) AND $crop ){
			$resizeImg->resizeImage($ExWidMinImg[0],$ExWidMinImg[1],'crop');
			if( $quality ){
				$resizeImg->saveImage($DIR_IMG_MIN.$FILE_UP,$quality);
			}else{
				$resizeImg->saveImage($DIR_IMG_MIN.$FILE_UP, 80);
			}
		}
		@unlink($tmpPath);
		return $FILE_UP;
	}
}
