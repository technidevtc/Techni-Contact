<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 juin 2005

 Mises à jour :

	28 mars 2006 : + options fournisseurs

 Fichier : /secure/manager/advertisers/del.php
 Description : Suppression d'un annonceurs

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN . 'advertisers.php');

$title  = 'Base de données des annonceurs et fournisseurs';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des annonceurs et fournisseurs</a> &raquo; Supprimer un annonceur ou fournisseur';
require(ADMIN . 'head.php');

$from_extranet = isset($_GET['type']) ? true : false;

if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || !($data = & loadAdvertiser($handle, $_GET['id'], $from_extranet)))
{
    print('<div class="bg"><div class="fatalerror">Identifiant annonceur ou fournisseur incorrect.</div></div>');
}
// Seul admin / tech autorisé  + commercial associé
else if($user->rank == CONTRIB || ($user->rank == COMM && $user->id != $data[1]) || !$user->get_permissions()->has("m-prod--sm-partners","d"))
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
	if($from_extranet)
	{
		$handle->query('delete from advertisers_adv where id = \'' . $handle->escape($_GET['id']) . '\'');
	}
	else
	{
		delAdvertiser($handle, $_GET['id'], $data[2]);
	}


    $filter = (isset($_GET['filter']) && $_GET['filter'] == '1') ? 1 : 0;
    $liste  = array(10, 25, 50, 75);

    if(isset($_GET['nb']) && in_array($_GET['nb'], $liste))
    {
        $nb   = $_GET['nb'];
        $type = 1;
    }
    else if(isset($_GET['lettre']) && preg_match('/^[0a-z]$/', $_GET['lettre']))
    {
        $lettre = $_GET['lettre'];
        $type   = 0;
    }
    else
    {
        $type = 1;
        $nb   = 10;
    }

?><div class="titreStandard">Liste des annonceurs et fournisseurs</div><br>
<div class="bg"><div align="center"><a href="index.php?nb=10&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>">Récents</a> - <a href="index.php?lettre=0&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>">0-9</a>
<?php

    for($i = ord('a'); $i <= ord('z'); ++$i)
    {
        print(' - <a href="index.php?lettre='.chr($i).'&' . session_name() . '=' . session_id() . '&filter=' . $filter . '">'.strtoupper(chr($i)).'</a> ');
    }

    print('</div><br><br>');

    if($user->rank == COMM)
    {
        if($type == 1)
        {
            $_url = 'nb=' . $nb;
        }
        else
        {
            $_url = 'lettre=' . $lettre;
        }

        print('<a href="index.php?' . $_url . '&' . session_name() . '=' . session_id() . '&filter=');

        if($filter)
        {
            print('0">Afficher tous les annonceurs et fournisseurs');
        }
        else
        {
            print('1">Afficher uniquement vos annonceurs et fournisseurs');
        }

        print('</a><br><br>');
    }

?>
<br><br>Afficher les <select onChange="goTo('index.php?nb=' + this.options[this.selectedIndex].value + '&<?php print(session_name() . '=' . session_id() . '&filter=' . $filter) ?>')">
<?php

    foreach($liste as $k => $v)
    {

        $sel = ($nb == $v) ? ' selected' : '';
        print('<option value="' . $v . '"' . $sel . '>' . $v . '</option>');

    }

?></select> derniers annonceurs et fournisseurs ajoutés ou mis à jour. <br><br><?php

    if($type == 0)
    {
        print('<b>Liste des annonceurs et fournisseurs dont le nom commence par ');
        if($lettre == '0')
        {
            $pattern = 'REGEXP(\'^[0-9]\')';
            print('un chiffre :</b><br><br>');
        }
        else
        {
            $pattern = 'like \'' . $lettre . '%\'';
            print('la lettre ' . strtoupper($lettre) . ' :</b><br><br>');
        }

        if($user->rank == COMM && $filter == 1)
        {
            $a = & displayAdvertisers($handle, 'and a.nom1 ' . $pattern . ' order by a.nom1', $user->id);
        }
        else
        {
            $a = & displayAdvertisers($handle, 'where a.nom1 ' . $pattern . ' order by a.nom1');
        }

    }
    else
    {
        print('<b>Liste des '.$nb.' derniers annonceurs ou fournisseurs ajoutés ou mis à jour : </b><br><br>');
   
        if($user->rank == COMM && $filter == 1)
        {
            $a = & displayAdvertisers($handle, 'order by a.timestamp desc limit ' . $nb, $user->id);
        }
        else
        {
            $a = & displayAdvertisers($handle, 'order by a.timestamp desc limit ' . $nb);
        }

    }
    
    
    if(count($a) > 0)
    {
        print('<ul>');

        foreach($a as $k => $v)
        {
             print('<li><a href="edit.php?id=' . $k . '&' . session_name() . '=' . session_id() . '">' . to_entities($v) . '</a>');
        }

        print('</ul>');
    }


?></div><br><br>
<?php

	if($from_extranet)
	{
		print('<div class="titreStandard">Rejet d\'une demande de mise à jour de l\'annonceur ' . to_entities($data[2]) . '</div><br><div class="bg"><div class="confirm">Demande rejetée avec succès.</div></div>');
	}
	else
	{
		print('<div class="titreStandard">Suppression de l\'annonceur ' . to_entities($data[2]) . '</div><br><div class="bg"><div class="confirm">Annonceur supprimé avec succès.</div></div>');
	}

}  // fin autorisation ou id valide

require(ADMIN . 'tail.php');

?>
