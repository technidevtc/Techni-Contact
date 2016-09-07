<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /www/images/produits/image_vl2DgkdS8keH6z.php
 Description : images produits

/=================================================================*/


require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if(!isset($_GET['id']) || !preg_match('/^[0-9]{1,8}$/', $_GET['id']) || !isset($_GET['zoom']) || ($_GET['zoom'] != 0 && $_GET['zoom'] != 1))
{
    header('Location: ' . ADMIN_URL);
    exit;
}

$extra = ($_GET['zoom'] == 1) ? 'zoom/' : '';

if(is_file(PRODUCTS_IMAGE_INC . $extra . $_GET['id'] . '.jpg'))
{
    header('Content-type: image/jpg');
	@imagejpeg(imagecreatefromjpeg(PRODUCTS_IMAGE_INC . $extra . $_GET['id'] . '.jpg'));
    exit();
}
else
{
    header('Location: ' . ADMIN_URL);
    exit;
}

?>
