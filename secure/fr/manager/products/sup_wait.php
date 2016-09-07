<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 19 juillet 2005

 Fichier : /secure/manager/products/sup_wait.php
 Description : Produits en attente de validation de suppression (demandée par fournisseurs)
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."products.php");

$title  = 'Base de données des produits';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des produits</a> &raquo; Validation de suppression de fiches produits';
require_once(ADMIN . 'head.php');

function & loadDel(& $handle, $id)
{
    $ret = false;
    if(($res = & $handle->query('select p.name, s.idProduct from sup_requests s, products_fr p where s.id = \'' . $handle->escape($id) . '\' and p.id = s.idProduct' , __FILE__, __LINE__))
       && $handle->numrows($res, __FILE__, __LINE__) == 1)
    {
        $ret = & $handle->fetch($res);
    }
    
    return $ret;
}



if($user->rank == CONTRIB)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{

    if(isset($_GET['action']) && ($_GET['action'] == 'a' || $_GET['action'] == 'r') && isset($_GET['id']) && preg_match('/^[0-9]{1,8}$/', $_GET['id'])
       && ($data = & loadDel($handle, $_GET['id'])))
    {
        if($_GET['action'] == 'a')
        {
            if(delProduct($handle, $data[1], $data[0], $user->id))
            {
                $msg = 'Produit ' . to_entities($data[0]) . ' supprimé avec succès';
            }
            else
            {
                $msg = 'Erreur interne lors de la suppression du produit ' . to_entities($data[0]);
            }

        }
        else
        {
            if($handle->query('delete from sup_requests where id = \'' . $handle->escape($_GET['id']) . '\'', __FILE__, __LINE__) && $handle->affected() == 1)
            {
                ManagerLog($handle, $user->id, $user->login, $user->pass, $user->ip, 'Rejet de la demande de suppression du produit ' . $data[0] . ' [ID : ' . $_GET['id'] . ']');
                $msg = 'Demande de suppression du produit ' . to_entities($data[0]) . ' rejetée avec succès';
            }
            else
            {
                $msg = 'Erreur interne lors du rejet de la demande de suppression du produit ' . to_entities($data[0]);
            }


        }

        print('<div class="bg"><div class="confirm">' . $msg . '.</div></div><br><br>');
    }


    $products = array();

    if($res = & $handle->query('select p.name, p.fastdesc, p.id, s.timestamp, s.id, pf.idFamily, p.ref_name, a.nom1 from products_families pf, products_fr p, sup_requests s, advertisers a where p.id = s.idProduct and p.id = pf.idProduct and p.idAdvertiser = a.id and p.deleted !=1 group by p.id, a.id order by s.timestamp desc, a.id, p.name', __FILE__, __LINE__))
    {
        while($row = & $handle->fetch($res))
        {
            $products[] = & $row;
        }
    }


?>
<div class="titreStandard">Fiches produits en attente de validation de suppression</div><br>
<div class="bg">

<?php

if(count($products) > 0)
{
    $prev = '';   $open = false;
    
    print('<ul>');

    foreach($products as $k => $v)
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
                print('</ul><li>');
            }

            print('<u>Demandes de suppression du ' . $md . ' :</u><br><br><ul>');

            $prev = $md;
        }


        $extra = ($v[1] != '') ? ' - ' . $v[1] : '';
        
        print('<li> ' . to_entities($v[0] . $extra) . ' (Annonceur ' . to_entities($v[7]) . ')<a href="' . URL . 'produits/' . $v[5] . '-' . $v[2] . '-' . $v[6] . '.html" target="_blank"><img src="../images/web.gif" border="0"></a> <a href="sup_wait.php?' . session_name() . '=' . session_id() . '&id=' . $v[4] . '&action=a">Valider la suppression</a> - <a href="sup_wait.php?' . session_name() . '=' . session_id() . '&id=' . $v[4] . '&action=r">Rejeter la suppression</a>');

    }
    
    if($open)
    {
        print('</ul>');
    }
    
    print('</ul>');

}
else
{
    print('<div class="confirm">Aucune fiche produit en attente de validation de suppression</div>');
}

?></div><br><br>
<?php

}  // fin accès

require(ADMIN . 'tail.php');

?>