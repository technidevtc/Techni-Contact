<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/products/search.php
 Description : Recherche interne des produits

/=================================================================*/


if(!isset($_GET['name']) || strlen($search = urldecode($_GET['name'])) < 3)
{
    exit;
}

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."products.php");

$title = $navBar = 'Rechercher un produit';

require(ADMIN . 'head.php');

?><script language="JavaScript">
<!--

function select(name)
{

    window.opener.document.addProduct.nom.value = name;
    window.close();

}

//-->
</script><div class="titreStandard">Résultat de votre recherche sur les termes <b><?php print(to_entities($search)) ?></b> - <a href="javascript:window.close()">Fermer cette page</a></div>
<br><div class="bg"><?php

$tab     = explode(' ', $search);
$pattern = '';

for($i = 0; $i < count($tab); ++$i)
{
    if($i > 0)
    {
        $pattern .= 'and ';
    }
    
    $pattern .= 'name like \'%' . $handle->escape($tab[$i]) . '%\'';
}

$p = & searchProduct($handle, $pattern);

if(count($p) == 0)
{
    print('<div class="confirm">Aucun résultat</div>');
}
else
{
    print('<ul>');

    foreach($p as $v)
    {
        print('<li><a href="javascript:select(\'' . to_entities($v, ENT_QUOTES) . '\')">' . to_entities($v) . '</a>');
    }

    print('</ul>');
}

?></div><br><br><div class="titreStandard">Liste des produits dont le nom commence par le même caractère - <a href="javascript:window.close()">Fermer cette page</a></div>
<br><div class="bg"><?php


if(preg_match('/^[0-9]$/', $search[0]))
{
    $pattern = 'REGEXP(\'^[0-9]\')';
}
else
{
    $pattern = 'like \'' . $handle->escape($search[0]) . '%\'';
}

$p = & searchProduct($handle, 'name ' . $pattern);

if(count($p) == 0)
{
    print('<div class="confirm">Aucun résultat</div>');
}
else
{
    print('<ul>');

    foreach($p as $v)
    {
        print('<li><a href="javascript:select(\'' . to_entities($v, ENT_QUOTES) . '\')">' . to_entities($v) . '</a>');
    }

    print('</ul>');
}

print('</div>');

require(ADMIN . 'tail.php');

?>

