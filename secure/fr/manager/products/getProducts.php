<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/families/getProducts.php
 Description : Recherche produits annonceur

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

if(($result = & $handle->query('select p.id, p.name, a.nom1, p.fastdesc from products_fr p, advertisers a where p.idAdvertiser = \'' . $handle->escape($_GET['id']). '\' and p.idAdvertiser = a.id group by p.id order by p.name', __FILE__, __LINE__)))
{

    $first = true;

    while($record = & $handle->fetch($result))
    {
        if($first)
        {
            $first = false;
            print(to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[2]))));
        }

        if($record[3] != '')
        {
            $record[1] .= ' - ' . $record[3];
        }

        print('<main_separator>' . $record[0] . '<separator>' . to_entities(str_replace(array('"', "'", '&', '(', ')'), array(' ', ' ', '', '', ''), Utils::toASCII($record[1]))));
    }
}
else
{
    print('-1');
}



flush();
exit;


?>
