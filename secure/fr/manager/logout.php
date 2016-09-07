<?php


/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 18 juillet 2005

 Fichier : /secure/manager/logout.php
 Description : Fichier déconnexion manager

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN     . 'logs.php');
require(ICLASS    . 'ManagerUser.php');

$handle = DBHandle::get_instance();
$user   = & new ManagerUser($handle);

if($user->login())
{
    $n = '';
    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $n, $_SESSION['ip'], 'Déconnexion');
    @session_destroy();
}

header('Location: ' . ADMIN_URL);

?>
