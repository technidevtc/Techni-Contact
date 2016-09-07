<?php


/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /includes/managerV2/logo.php
 Description : Upload de logos

/=================================================================*/


/* Upload un logo
   i : nom original
   i : type image (gif ou jpg)
   i : nouveau nom
   i : largeur
   i : hauteur
   i : rep stockage
   o : true si up, false si erreur */

function upload($field, $type, $name, $width, $height, $dir) {
	if(is_uploaded_file($_FILES[$field]['tmp_name'])) {
		$ext = ($type == 'gif') ? '.gif' : '.jpg';
		copy($_FILES[$field]['tmp_name'], $dir . $name . $ext);
                if(!empty($width) || !empty($height))
                  ImageResize($width, $height, $dir . $name . $ext, $dir . $name . $ext);
		return true;
	}
	return false;
}

function uploadAndProceedImage($field, $baseName, $dir) {
  if (is_uploaded_file($_FILES[$field]['tmp_name'])) {
          $num = 1;
          while (is_file($dir."zoom/".$baseName."-".$num.".jpg")) $num++;
          $fileName = $baseName."-".$num.".jpg";

          copy($_FILES[$field]['tmp_name'], $dir."zoom/".$fileName);
          if (ImageResize(250, 225, $dir."zoom/".$fileName, $dir."card/".$fileName)
          && ImageResize(147, 110, $dir."zoom/".$fileName, $dir."thumb_big/".$fileName)
          && ImageResize(112, 84, $dir."zoom/".$fileName, $dir."thumb_small/".$fileName))
          return true;
	}
	return false;
}

function deleteImageAndProceed($baseName, $dir, $num) {
	$fileName = $baseName."-".$num.".jpg";
	@unlink($dir."zoom/".$fileName);
	@unlink($dir."card/".$fileName);
	@unlink($dir."thumb_big/".$fileName);
	@unlink($dir."thumb_small/".$fileName);
	$num++;
	while (is_file($dir."zoom/".$baseName."-".$num.".jpg")) {
		$oldFileName = $baseName."-".$num.".jpg";
		$newFileName = $baseName."-".($num-1).".jpg";
		@rename($dir."zoom/".$oldFileName, $dir."zoom/".$newFileName);
		@rename($dir."card/".$oldFileName, $dir."card/".$newFileName);
		@rename($dir."thumb_big/".$oldFileName, $dir."thumb_big/".$newFileName);
		@rename($dir."thumb_small/".$oldFileName, $dir."thumb_small/".$newFileName);
		$num++;
	}
}

function reorderImagesAndProceed($baseName, $dir, array $newOrder){
  $currentNum = 1;
  while (is_file($dir."zoom/".$baseName."-".$currentNum.".jpg")) {

    if(isset($newOrder[$currentNum-1]))
      $newNum = $newOrder[$currentNum-1];
    else {
      return false;
    }
$dirNames = array('zoom', 'thumb_small', 'thumb_big', 'card');
    if(isset($newNum)){
      $currentFileName = $baseName."-".$currentNum.".jpg";
        $newFileName = $baseName."-".$newNum.".jpg";
        $tmpFileName = $baseName."-tmp.jpg";
        $tmpCurrentFileName = $baseName."-tmp-".$currentNum.".jpg";
        $tmpNewFileName = $baseName."-tmp-".$newNum.".jpg";

        foreach($dirNames as $dirName){

          if(is_file($dir.$dirName."/".$tmpNewFileName)){
            copy($dir.$dirName."/".$currentFileName, $dir.$dirName."/".$tmpCurrentFileName);
            @rename($dir.$dirName."/".$currentFileName, $dir.$dirName."/".$tmpCurrentFileName);
            @rename($dir.$dirName."/".$tmpNewFileName, $dir.$dirName."/".$currentFileName);
          }elseif(is_file($dir.$dirName."/".$newFileName)){
            copy($dir.$dirName."/".$currentFileName, $dir.$dirName."/".$tmpCurrentFileName);
            @rename($dir.$dirName."/".$newFileName, $dir.$dirName."/".$tmpNewFileName);
            @rename($dir.$dirName."/".$currentFileName, $dir.$dirName."/".$newFileName);
            @rename($dir.$dirName."/".$tmpNewFileName, $dir.$dirName."/".$currentFileName);
          }
        }
    }
    $currentNum++;
  }
  // deletion of tmp files
  foreach($dirNames as $dirName){
    if ($handle = opendir($dir.$dirName."/")) {
        while (false !== ($entry = readdir($handle))) {
            if (strpos($entry, $baseName."-tmp-")!== false) {
                @unlink($dir.$dirName."/".$entry);
            }
        }
        closedir($handle);
    }
  }
  return true;
}

function ImageResize($w, $h, $from, $to, $upsample = false) {
	/* Image Infos array (
		0 => width, ex: 1024
		1 => height, ex: 768
		2 => type, ex: IMAGETYPE_GIF
		3 => tag attributes, ex: 'height="xxx" width="yyy"'
		'bits' => bits per channel, ex: 8
		'channels' => RGB=3 or CMYK=4, ex: 3
		'mime' => mime type ex: 'image/jpeg'
	)*/
	$im_infos = getimagesize($from);
  
	if ($im_infos) {
		$data = file_get_contents($from);
		$im = @imagecreatefromstring($data);
		
		$w_ratio = $im_infos[0] / $w;	// Width Ratio
		$h_ratio = $im_infos[1] / $h;	// Height Ratio
		
		if ($w_ratio > 1 || $h_ratio > 1) {
			// Image > max size -> resizing keeping the ratio
			$ratio = max($w_ratio, $h_ratio);
			
			$wd = floor($im_infos[0] / $ratio);	// Width Destination
			$hd = floor($im_infos[1] / $ratio);	// Height Destination
		}
		else {
			if ($upsample) {
				// Upsampling the image
				$ratio = max($w_ratio, $h_ratio);
				
				$wd = floor($im_infos[0] / $ratio);	// Width Destination
				$hd = floor($im_infos[1] / $ratio);	// Height Destination
			}
			else {
				// No upsample
				$wd = $im_infos[0];
				$hd = $im_infos[1];
			}
		}
		
		$imd = imagecreatetruecolor($wd, $hd);	// Image Destination
		imagecopyresampled($imd, $im, 0, 0, 0, 0, $wd, $hd, $im_infos[0], $im_infos[1]);
		imagejpeg($imd, $to);
    return true;
	}
  else
    return false;
}

?>
