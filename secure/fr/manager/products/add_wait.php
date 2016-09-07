<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/products/add_wait.php
 Description : Produits en attente de validation de création
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."products.php");

$title  = 'Base de données des produits';

$extra_title = (isset($_GET['from']) && $_GET['from'] == 'adv') ? ' extranet' : '';

$navBar = '<a href="index.php?SESSION" class="navig">Base de données des produits</a> &raquo; Validation de fiches produits' . $extra_title;
require_once(ADMIN . 'head.php');

if($user->rank == CONTRIB)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
    if($extra_title == '')
        $products = & displayWait($handle, 'c');
    else
        $products = & displayWaitAdv($handle, 'c');


?>
<div class="titreStandard">Fiches<?php print($extra_title) ?> créées en attente de validation</div><br>
<div class="bg">

<?php

if(count($products) > 0)
{
    $prev = '';   $open = false;

    print('<ul>');

    foreach($products as $k => $v)
    {
        $extra = ($v[1] != '') ? ' - ' . $v[1] : '';

        if($extra_title == '')
            print('<li><a href="edit.php?type=add&id=' . $k . '&' . session_name() .'=' . session_id() . '">' . to_entities($v[0]) .'</a>' . to_entities($extra));
        else
        {
            $open = true;

            if($prev != ($md = date('d/m/Y', $v[3])))
            {
                if($prev == '')
                {
                    print('<li>');
                }
                else
                {
                    print('</ul><br><li>');
                }

                print('<u>Demandes de création du ' . $md . ' :</u><br><br><ul>');

                $prev = $md;
            }

            print('<li><a href="edit.php?type=add_adv&id=' . $k . '&' . session_name() .'=' . session_id() . '">' . to_entities($v[0]) .'</a>' . to_entities($extra) . ' ( Annonceur : ' . to_entities($v[2]) . ')');
        }

    }

    if($open)
    {
        print('</ul>');
    }

    print('</ul>');
}
else
{
    print('<div class="confirm">Aucune fiche produit' . $extra_title . ' en attente de validation de création</div>');
}

?></div><br><br>
<?php

}  // fin accès

require(ADMIN . 'tail.php');

?>
