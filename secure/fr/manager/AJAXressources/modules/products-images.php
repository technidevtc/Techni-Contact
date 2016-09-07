<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if(!isset($_GET['id']) || !preg_match('/^[0-9]{1,8}$/', $_GET['id'])) exit();

if(is_file(PRODUCTS_IMAGE_INC."thumb_small/".$_GET['id']."-1".".jpg")) {
	header('Content-type: image/jpg');
	@imagejpeg(imagecreatefromjpeg(PRODUCTS_IMAGE_INC."thumb_small/".$_GET['id']."-1".".jpg"));
}
else {
	header('Content-type: image/gif');
	@imagegif(imagecreatefromgif(PRODUCTS_IMAGE_INC."no-pic-thumb_small.gif"));
}
exit();

?>