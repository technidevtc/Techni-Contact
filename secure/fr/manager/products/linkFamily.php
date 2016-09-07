<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/families/linkFamily.php
 Description : Recherche famille

/=================================================================*/

if(!isset($_GET['id']) || !preg_match('/^[1-9][0-9]*$/', $_GET['id']))
{
    print('-1');
    flush();
    
    exit;
}

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
    print('-1');
    flush();
    
    exit;
}

if(($result = & $handle->query('select name from families_fr where id = \'' . $handle->escape($_GET['id']). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
{
    $record = & $handle->fetch($result);

    print($_GET['id'] . '<separator>' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[0]))));
}
else
{
    print('-1');
}



flush();
exit;


?>
