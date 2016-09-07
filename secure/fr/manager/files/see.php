<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 11 juin 2005

 Fichier : /secure/manager/files/see.php
 Description : Aperçu interne images catalogues

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if(isset($_GET['type']) && isset($_GET['id']) && preg_match('/^[1-9][0-9]*\.(gif|jpg)$/', $_GET['id']))
{

    switch($_GET['type'])
    {
        case 1  : $path = CAT_INC; break;
        case 2  : $path = BIBLI_INC; break;
        default : header('Location: ' . ADMIN_URL);
                  exit;
    }
    

    if(is_file($path . $_GET['id']))
    {
        if(substr($_GET['id'], -3) == 'gif')
        {
            header('Content-type: image/gif');
            @imagegif(@imagecreatefromgif($path . $_GET['id']));
        }
        else
        {
            header('Content-type: image/jpeg');
            @imagegif(@imagecreatefromjpeg($path . $_GET['id']));
        }
        exit;
    }
    
    header('Location: ' . ADMIN_URL);
    exit;

}
else
{

    header('Location: ' . ADMIN_URL);
    exit;
}

?>
