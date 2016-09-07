<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 23 juin 2005

 Fichier : /secure/manager/families/del.php
 Description : Suppression d'une famille

/=================================================================*/


require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'families.php');

$title  = 'Base de données des familles';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des familles</a> &raquo; Supprimer une famille';
require(ADMIN . 'head.php');

if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || $_GET['id'] < 11 || !($data = & loadFamily($handle, $_GET['id'])))
{
    print('<div class="bg"><div class="fatalerror">Identifiant famille incorrect.</div></div>');
}
else
{
    if($data[1] <= 11)
    {   
        $families = & displayFamilies($handle);
        foreach($families as $k => $v)
        {
            if(preg_match('/^' . $data[1] . '<!>.*$/', $k))
            {
                $under = & $v[$_GET['id'] . '<!>' . $data[0]];
                break;
            }
        }
        
        $word = 'sous-famille';
    }
    else
    {
        $under = & listProducts($handle, $_GET['id']);
        
        $word = 'produit';
    }


    if(($nb = count($under)) > 0)
    {
        $s = $nb > 1 ? 's' : '';

        $msg = 'Impossible de supprimer la famille car elle comporte ' . $nb  . ' ' . $word . $s . '.';
    }
    else
    {
        delFamily($handle, $_GET['id'], $data[0], $data[1]);
        $msg = 'Famille supprimée avec succès.';
    }

?>
<div class="titreStandard">Suppression de la famille <?php print(to_entities($data[0])) ?></div><br><div class="bg">
<div class="confirm"><?php print($msg) ?></div>
</div><?php

}  // fin id valide

require(ADMIN . 'tail.php');

?>
