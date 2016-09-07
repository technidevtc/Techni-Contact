<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /www/images/produits/image_vl2DgkdS8keH6z.php4
 Description : images produits

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if(!isset($_GET['id']) || !preg_match('/^\d+$/', $_GET['id'])) exit();

if(is_file(PRODUCTS_IMAGE_INC."thumb_small/".$_GET['id']."-1".".jpg")) {
	header('Content-type: image/jpg');
	@imagejpeg(imagecreatefromjpeg(PRODUCTS_IMAGE_URL."thumb_small/".$_GET['id']."-1".".jpg"));
}
else {
	header('Content-type: image/gif');
	@imagegif(imagecreatefromgif(PRODUCTS_IMAGE_URL."no-pic-thumb_small.gif"));
}
exit();

?>
