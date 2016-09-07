<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004


 Fichier : /secure/manager/actions.php
 Description : Historique des actions annonceur / produit

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = $navBar = 'Historique des actions';
require(ADMIN . 'head.php');


/* Nombre d'enregistrements
   i : réf handle connexion
   i : type
   i : id
   o : nombre enregistrements concernés */
function nb(& $handle, $type, $id)
{
    $ret = 0;

    $type = ($type == 'produit') ? 'de la fiche produit' : 'de l\'annonceur';

    if($result = & $handle->query('select id from actions where action like \'%[ID : ' . $handle->escape($id) . ']%\' and action like \'%' . $handle->escape($type) . '%\'', __FILE__, __LINE__))
    {
        $ret = $handle->numrows($result, __FILE__, __LINE__);
    }

    return $ret;
}



if(!isset($_GET['type']) || ($_GET['type'] != 'produit' && $_GET['type'] != 'annonceur') || !isset($_GET['id']) || (isset($_GET['type_a']) && $_GET['type_a'] != '' && $_GET['type_a'] != 'edit' && $_GET['type_a'] != 'backup' && $_GET['type_a'] != 'edit_adv') || !preg_match('/^[0-9]+$/', $_GET['id']))
{
    print('<div class="bg"><div class="fatalerror">Accès impossible.</div></div>');
}
else if($user->rank == CONTRIB)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else if(!($nb = nb($handle, $_GET['type'], $_GET['id'])))
{
    print('<div class="bg"><div class="fatalerror">Identifiant ' . $_GET['type'] . ' incorrect.</div></div>');
}
else
{
    $page = isset($_GET['page']) ? $_GET['page'] : 1;

    if(!preg_match('/^[1-9][0-9]*$/', $page))
    {
        $page = 1;
    }
    
    $pages = ceil($nb / 20);

    if($pages < $page)
    {        
        print('<div class="bg"><div class="fatalerror">Identifiant de page incorrect.</div></div>');
    }
    else
    {
      

?>
<div class="titreStandard">Historiques des actions des utilisateurs sur <?php

        if($_GET['type'] == 'produit')
        {
            $type_a = isset($_GET['type_a']) ? $_GET['type_a'] : '';
            print('ce produit | <a href="' . ADMIN_URL . 'products/edit.php?id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '&type=' . $type_a . '">Retour</a>');
        }
        else
        {
            print('cet annonceur | <a href="' . ADMIN_URL . 'advertisers/edit.php?id='.$_GET['id'] . '&' . session_name() . '=' . session_id() . '">Retour</a>');
        }

?></div><br>
<div class="bg"><?php


        $first = ($page - 1) * 20;

        $type_sql = ($_GET['type'] == 'produit') ? 'de la fiche produit' : 'de l\'annonceur';
        if($result = & $handle->query('select id, action, username from actions where action like \'%[ID : ' . $handle->escape($_GET['id']) . ']%\' and action like \'%' . $handle->escape($type_sql) . '%\' order by id desc limit ' . $first . ', 20', __FILE__, __LINE__))
        {
            print('<ul><table><tr><td class="intitule"><div class="confirm">Action</div></td><td class="intitule"><div class="confirm">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Date</div></td><td class="intitule"><div class="confirm">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Utilisateur</div></td></tr>');

            while($row = & $handle->fetch($result))
            {
                print('<tr><td class="intitule"><li> ' . to_entities($row[1]) . ' </td><td class="intitule">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'.date('d/m/Y à H:i:s', $row[0]).'</td><td class="intitule">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;' . to_entities($row[2]) . '</td></tr>');
            }

            print('</ul></table>');
        }


        print('<br><br>Afficher la page ');
        for($i = 1; $i <= $pages; ++$i)
        {
            if($i > 1)
            {
                print(' | ');
            }

            if($i == $page)
            {
                print($i);
            }
            else
            {
                $extra = ($_GET['type'] == 'produit') ? (isset($_GET['type_a']) ? '&type=' . $type_a : '&type=')   :   '';
                print('<a href="actions.php?page=' . $i . '&' . session_name() . '=' . session_id() . '&id=' . $_GET['id'] . '&type=' . $_GET['type'] . $extra . '">' . $i . '</a>');
            }

        }


?></div><br><br>
<?php

    } // fin id page

}   // fin droits

require(ADMIN . 'tail.php');

?>
