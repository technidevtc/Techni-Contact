<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/families/see_adv_files.php
 Description : Aperçus fichiers up par annonceurs

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if(!isset($_GET['type']) || ($_GET['type'] != 'image' && $_GET['type'] != 'file'))
{
    header('Location: ' . ADMIN_URL);
    exit;
}
else
{
    $type = ($_GET['type'] == 'image') ? 'i' : 'f';
}

if($type == 'i' && (!isset($_GET['id']) || !preg_match('/^[0-9]{1,8}$/', $_GET['id']) || !is_file(PRODUCTS_IMAGE_ADV_INC . 'zoom/' . $_GET['id'] . '.jpg')))
{
    header('Location: ' . ADMIN_URL);
    exit;
}
else if($type == 'f' && (!isset($_GET['file']) || !preg_match('/^[0-9]{1,8}\-[1-3]\.(pdf|doc)$/', $_GET['file']) || !is_file(PRODUCTS_FILES_ADV_INC . $_GET['file'])))
{
    header('Location: ' . ADMIN_URL);
    exit;
}

require(ADMIN  . 'logs.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
    header('Location: ' . ADMIN_URL);
    exit;
}

if($type == 'i')
{

    header('Content-type: image/jpg');
    imagejpeg(imagecreatefromjpeg(PRODUCTS_IMAGE_ADV_INC . 'zoom/' . $_GET['id'] . '.jpg'));
}
else
{
    $ext = explode('.', $_GET['file']);
    header('Content-type: application/' . $ext[1]);
    header('Content-Disposition: attachment; filename="' . $_GET['file'] . '"');
          
    readfile(PRODUCTS_FILES_ADV_INC . $_GET['file']);
}

exit;


?>
