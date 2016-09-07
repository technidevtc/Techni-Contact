<?php
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
	
	if ($im_infos)
	{
		//$mts["total"]["start"] = microtime(true);
		$data = file_get_contents($from);
		//$mts["imagecreatefromjpeg"]["start"] = microtime(true);
		$im = @imagecreatefromstring($data);
		
		/*switch ($im_infos[2])
		{
			case IMAGETYPE_GIF :	$im = @imagecreatefromgif($from); break;
			case IMAGETYPE_JPEG :	$im = @imagecreatefromjpeg($from); break;
			case IMAGETYPE_PNG :	$im = @imagecreatefrompng($from); break;
			default :				$im = false; break;
		}*/
		//$mts["imagecreatefromjpeg"]["end"] = microtime(true);
		
		$w_ratio = $im_infos[0] / $w;	// Width Ratio
		$h_ratio = $im_infos[1] / $h;	// Height Ratio
		
		if ($w_ratio > 1 || $h_ratio > 1)
		{
			// Image > max size -> resizing keeping the ratio
			$ratio = max($w_ratio, $h_ratio);
			
			$wd = floor($im_infos[0] / $ratio);	// Width Destination
			$hd = floor($im_infos[1] / $ratio);	// Height Destination
		}
		else
		{
			if ($upsample)
			{
				// Upsampling the image
				$ratio = max($w_ratio, $h_ratio);
				
				$wd = floor($im_infos[0] / $ratio);	// Width Destination
				$hd = floor($im_infos[1] / $ratio);	// Height Destination
			}
			else
			{
				// No upsample
				$wd = $im_infos[0];
				$hd = $im_infos[1];
			}
		}
		
		//$mts["imagecreatetruecolor"]["start"] = microtime(true);
		$imd = imagecreatetruecolor($wd, $hd);	// Image Destination
		//$mts["imagecreatetruecolor"]["end"] = microtime(true);
		
		//$mts["imagecopyresampled"]["start"] = microtime(true);
		imagecopyresampled($imd, $im, 0, 0, 0, 0, $wd, $hd, $im_infos[0], $im_infos[1]);
		//$mts["imagecopyresampled"]["end"] = microtime(true);
		
		//$mts["imagejpeg"]["start"] = microtime(true);
		imagejpeg($imd, $to);
		//$mts["imagejpeg"]["end"] = microtime(true);
		
		//$mts["total"]["end"] = microtime(true);
		
		//return $mts;
		/*
		switch ($im_infos[2])
		{
			case IMAGETYPE_GIF :	imagegif($imd, $to); break;
			case IMAGETYPE_JPEG :	imagejpeg($imd, $to); break;
			case IMAGETYPE_PNG :	imagepng($imd, $to); break;
			default : break;
		}
		*/
	}
}

/*
Home = 148 x 110
Recherche & Famille niveau 3 = Auto x 120
Famille niveau 1 = 165x113
Famille niveau 2 = 113x80
Autre produits = 113x80
produits = 250 x 226


thumbs_big 147x110
thumbs_small 112x84
cards 250x226
*/
define("__IMAGES_DIRECTORY_SRC_", "/data/technico-save/includes/fr/files/images/products/");
define("__IMAGES_DIRECTORY_DST_", "/data/technico/includes/fr/files/images/products/");
//print "Redimensionnement de toutes les images présentes dans le répertoire '" . __IMAGES_DIRECTORY_SRC_ . "thumb_small/'\n";

$startt = microtime(true);
$dir = dir(__IMAGES_DIRECTORY_SRC_."zoom/");

$nbimage = $nbredim = $nbcopy = $nbdel = $nbrename = $nbnotafile = 0;
while ($file = $dir->read()) {
	if ($file != "." && $file != "..") {
		if (preg_match("/^[1-9]{1}[0-9]*\.[^.]*/", $file)) {
			list($fn, $ext) = explode(".", $file);
			$newfile = $fn."-1.".$ext;
		/*if (is_file(__IMAGES_DIRECTORY_SRC_."thumb_small/" . $file)) {
			if (preg_match("/^[1-9]{1}[0-9]*\.[^.]*", $file)) {
				list($fn, $ext) = explode(".", $file);
				$newfile = $fn."-1.".$ext;
				//print "renaming '".$file."' to ". $newfile ."\n";
				rename(__IMAGES_DIRECTORY_SRC_."thumb_small/".$file, __IMAGES_DIRECTORY_SRC_."thumb_small/".$fn."-1.".$ext);
				$file = $newfile;
				$nbrename++;
				if ($nbrename%1000 == 0) print "Renamed " . $nbrename . " images files\n";
			}
			else {
				print $file." non traité\n";
			}
			$nbimage++;*/
			if (!is_file(__IMAGES_DIRECTORY_DST_."zoom/".$newfile)) {
				if (!is_file(__IMAGES_DIRECTORY_DST_."zoom/".$file)) {
					$start = microtime(true);
					copy(__IMAGES_DIRECTORY_SRC_."zoom/".$file, __IMAGES_DIRECTORY_DST_."zoom/".$newfile);
					//$mts = ImageResize(4000, 3000, __IMAGES_DIRECTORY_SRC_ . 'zoom/' . $file, __IMAGES_DIRECTORY_DST_ . 'zoom/' . $file);
					$end = microtime(true);
					print $file . " zoom copied to ".$newfile." in " . ($end-$start)*1000 . "ms\n";
					$nbcopy++;
				}
				else {
					rename(__IMAGES_DIRECTORY_DST_."zoom/".$file, __IMAGES_DIRECTORY_DST_."zoom/".$newfile);
					print $file." zoom renamed to ".$newfile."\n";
					$nbrename++;
				}
				//foreach($mts as $mtName => $mtTime)
				//	print "\t".$mtName."=".($mtTime["end"]-$mtTime["start"])*1000 ."ms\n";
				//foreach($_mts as $mtName => $mtTime)
				//	print "\t".$mtName."=".($mtTime["end"]-$mtTime["start"])*1000 ."ms\n";
				//print "\n";
			}
			else {
				if (is_file(__IMAGES_DIRECTORY_DST_."zoom/".$file)) {
					unlink(__IMAGES_DIRECTORY_DST_."zoom/".$file);
					echo $file . " zoom deleted\n";
					$nbdel++;
				}
			}
			
			if (!is_file(__IMAGES_DIRECTORY_DST_."card/".$newfile)) {
				if (!is_file(__IMAGES_DIRECTORY_DST_."card/".$file)) {
					$start = microtime(true);
					$mts = ImageResize(250, 225, __IMAGES_DIRECTORY_SRC_."zoom/".$file, __IMAGES_DIRECTORY_DST_."card/".$newfile);
					$end = microtime(true);
					print $file . " redimensionned to 250x225 (card) in " . ($end-$start)*1000 . "ms\n";
					$nbredim++;
				}
				else {
					rename(__IMAGES_DIRECTORY_DST_."card/".$file, __IMAGES_DIRECTORY_DST_."card/".$newfile);
					print $file." card renamed to ".$newfile."\n";
					$nbrename++;
				}
				//foreach($mts as $mtName => $mtTime)
				//	print "\t".$mtName."=".($mtTime["end"]-$mtTime["start"])*1000 ."ms\n";
				//foreach($_mts as $mtName => $mtTime)
				//	print "\t".$mtName."=".($mtTime["end"]-$mtTime["start"])*1000 ."ms\n";
				//print "\n";
			}
			else {
				if (is_file(__IMAGES_DIRECTORY_DST_."card/".$file)) {
					unlink(__IMAGES_DIRECTORY_DST_."card/".$file);
					echo $file . " card deleted\n";
					$nbdel++;
				}
			}
			
			if (!is_file(__IMAGES_DIRECTORY_DST_."thumb_big/".$newfile)) {
				if (!is_file(__IMAGES_DIRECTORY_DST_."thumb_big/".$file)) {
					$start = microtime(true);
					$mts = ImageResize(147, 110, __IMAGES_DIRECTORY_SRC_."zoom/".$file, __IMAGES_DIRECTORY_DST_."thumb_big/".$newfile);
					$end = microtime(true);
					print $file . " redimensionned to 147x110 (thumb_big) in " . ($end-$start)*1000 . "ms\n";
					$nbredim++;
				}
				else {
					rename(__IMAGES_DIRECTORY_DST_."thumb_big/".$file, __IMAGES_DIRECTORY_DST_."thumb_big/".$newfile);
					print $file." thumb_big renamed to ".$newfile."\n";
					$nbrename++;
				}
			}
			else {
				if (is_file(__IMAGES_DIRECTORY_DST_."thumb_big/".$file)) {
					unlink(__IMAGES_DIRECTORY_DST_."thumb_big/".$file);
					echo $file . " thumb_big deleted\n";
					$nbdel++;
				}
			}
			
			if (!is_file(__IMAGES_DIRECTORY_DST_."thumb_small/".$newfile)) {
				if (!is_file(__IMAGES_DIRECTORY_DST_."thumb_small/".$file)) {
					$start = microtime(true);
					$mts = ImageResize(112, 84, __IMAGES_DIRECTORY_SRC_."zoom/".$file, __IMAGES_DIRECTORY_DST_."thumb_small/".$newfile);
					$end = microtime(true);
					print $file . " redimensionned to 112x84 (thumb_small) in " . ($end-$start)*1000 . "ms\n";
					$nbredim++;
				}
				else {
					rename(__IMAGES_DIRECTORY_DST_."thumb_small/".$file, __IMAGES_DIRECTORY_DST_."thumb_small/".$newfile);
					print $file." thumb_small renamed to ".$newfile."\n";
					$nbrename++;
				}
			}
			else {
				if (is_file(__IMAGES_DIRECTORY_DST_."thumb_small/".$file)) {
					unlink(__IMAGES_DIRECTORY_DST_."thumb_small/".$file);
					echo $file . " thumb_small deleted\n";
					$nbdel++;
				}
			}
			//print "\n";
			$nbimage++;
		}
		else {
			print $file." non valide\n";
			$nbnotafile++;
		}
	}
}
$dir->close();
$endt = microtime(true);

print "Nombre d'images traitées : " . $nbimage . "\n";
print "Nombre de fichiers non valides : " . $nbnotafile . "\n";
print "Nombre de renommage effectués : " . $nbrename . "\n";
print "Nombre de redimensionnement effectués : " . $nbredim . "\n";
print "Nombre de copie effectuées : " . $nbcopy . "\n";
print "Nombre de suppression effectuées : " . $nbdel . "\n";
print "temps total d'execution : " . (($endt-$startt)*1000) . "ms\n";


?>