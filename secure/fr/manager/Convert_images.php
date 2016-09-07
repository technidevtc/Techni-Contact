<?php
function ImageResize($w, $h, $from, $to, $upsample = false)
{   
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
		switch ($im_infos[2])
		{
			case IMAGETYPE_GIF :	$im = @imagecreatefromgif($from); break;
			case IMAGETYPE_JPEG :	$im = @imagecreatefromjpeg($from); break;
			case IMAGETYPE_PNG :	$im = @imagecreatefrompng($from); break;
			default :				$im = false; break;
		}
		
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
		
		$imd = imagecreatetruecolor($wd, $hd);	// Image Destination
		imagecopyresampled($imd, $im, 0, 0, 0, 0, $wd, $hd, $im_infos[0], $im_infos[1]);
		imagejpeg($imd, $to);
		
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

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec%1000);
}

define("__IMAGES_DIRECTORY__", "/data/technico/includes/fr/files/images/products/") ;
print "Redimensionnement de toutes les images présentes dans le répertoire '" . __IMAGES_DIRECTORY__ . "zoom/' vers le répertoire '" . __IMAGES_DIRECTORY__ . "cards/'<br/>\n";
$dir = dir(__IMAGES_DIRECTORY__ . 'zoom/');

while ($file = $dir->read())
{
	if ($file != "." && $file != "..")
	{
		if (is_file(__IMAGES_DIRECTORY__ . 'zoom/' . $file))
		{
			if (!is_file(__IMAGES_DIRECTORY__ . 'cards/' . $file))
			{
				ImageResize(240, 240, __IMAGES_DIRECTORY__ . 'zoom/' . $file, __IMAGES_DIRECTORY__ . 'cards/' . $file);
				echo $file . " 240x240 processed\n";
			}
			else echo $file . " 240x240 ok\n";
			
			if (!is_file(__IMAGES_DIRECTORY__ . $file))
			{
				ImageResize(100,  75, __IMAGES_DIRECTORY__ . 'zoom/' . $file, __IMAGES_DIRECTORY__ . $file);
				echo $file . " 100x75 processed\n";
			}
			else echo $file . " 100x75 ok\n";
		}
	}
}
$dir->close();


exit();

$start1 = microtime_float();

print "Nombre de redimensionnement effectués : " . $nbredim . "<br/>\n";

$end3 = microtime_float();
print "<br/>\ntemps total d'execution : " . (($end3-$start1)*1000) . "ms<br/>\n";


?>