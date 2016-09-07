<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 juin 2005

 Fichier : /secure/manager/products/del.php
 Description : Suppression d'un produit

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'products.php');

$type_a = isset($_GET['type']) ? $_GET['type'] : '';

$title  = 'Base de données des produits';

if($type_a == 'add')
{
    $navBar = '<a href="add_wait.php?SESSION" class="navig">Base de données des produits en attente de validation de création</a> &raquo; Rejeter un produit';
}
else if($type_a == 'add_adv')
{
    $navBar = '<a href="add_wait.php?SESSION&from=adv" class="navig">Base de données des produits extranet en attente de validation de création</a> &raquo; Rejeter un produit';
}
else if($type_a == 'edit_adv')
{
    $navBar = '<a href="edit_wait.php?SESSION&from=adv" class="navig">Base de données des produits extranet en attente de validation de modification</a> &raquo; Rejeter une modification';
}
else
{
    $navBar = '<a href="index.php?SESSION" class="navig">Base de données des produits</a> &raquo; Supprimer un produit';
}

require(ADMIN."head.php");

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$confirm = true;
	$motif   = (isset($_POST['why']) && $_POST['why'] != "") ? $_POST['why'] : false;
	if ($motif == "Autre") $motif = (isset($_POST['why2']) && $_POST['why2'] != "") ? $_POST['why2'] : false;
}
else
{
	$confirm = false;
	$motif = '';
}


if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || ($type_a != '' && $type_a != 'add' && $type_a != 'add_adv' && $type_a != 'edit' && $type_a != 'edit_adv') || !($data = & loadProduct($handle, $_GET['id'], $type_a)))
{
	print('<div class="bg"><div class="fatalerror">Identifiant produit incorrect.</div></div>');
}
// Seul admin / tech autorisé  + commercial
elseif($user->rank == CONTRIB || !$user->get_permissions()->has("m-prod--sm-products", "d"))
{
	print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
    if($type_a == 'add_adv' || $type_a == 'edit_adv')
    {
        if ($confirm && $motif !== false)
        {
			delProduct($handle, $_GET['id'], $data[0], $user->id, $type_a, $motif);
        }
    }
    else
    {
        delProduct($handle, $_GET['id'], $data[0], $user->id, $type_a);
    }


$filter = (isset($_GET['filter']) && $_GET['filter'] == '1') ? 1 : 0;
$liste  = array(10, 25, 50, 75);
$lmois  = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

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
else if(isset($_GET['month']) && in_array($_GET['month'], $lmois))
{
    $month = $_GET['month'];
    $type   = 2;
}
else
{
    $type = 1;
    $nb   = 10;
}

?><div class="titreStandard">Liste des produits</div><br>
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

    else if($type == 2)
    {
        $_url = 'month=' . $month;
    }

    else
    {
        $_url = 'lettre=' . $lettre;
    }

    print('<a href="index.php?' . $_url . '&' . session_name() . '=' . session_id() . '&filter=');

    if($filter)
    {
        print('0">Afficher tous les produits');
    }
    else
    {
        print('1">Afficher uniquement les produits de vos annonceurs');
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

?></select> derniers produits ajoutés ou mis à jour. <form method="get" action="index.php">Afficher les produits non mis à jour depuis  <select name="month">
<?php

foreach($lmois as $k => $v)
{

    $sel = ($month == $v) ? ' selected' : '';
    print('<option value="' . $v . '"' . $sel . '>' . $v . '</option>');

}

?></select> mois. <input type="hidden" name="filter" value="<?php print($filter) ?>"><input type="hidden" name="<?php print(session_name()) ?>" value="<?php print(session_id()) ?>"><input type="button" value="Go" onClick="this.form.submit(); this.disabled=true"></form><br><br><?php

if($type == 0)
{
    print('<b>Liste des produits dont le nom commence par ');
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
        $p = & displayGProducts($handle, 'and pfr.name '.$pattern.' group by pfr.id order by pfr.name', $user->id);
    }
    else
    {
        $p = & displayGProducts($handle, 'and pfr.name '.$pattern.' group by pfr.id order by pfr.name');
    }

}
else if($type == 2)
{
    print('<b>Liste des produits non mis à jour depuis ' . $month . ' mois : </b><br><br>');

    $line = time() - $month * 30 * 24 * 3600;

    if($user->rank == COMM && $filter == 1)
    {
        $p = & displayGProducts($handle, 'and p.timestamp <  ' . $line . ' group by p.id order by p.timestamp', $user->id);
    }
    else
    {
        $p = & displayGProducts($handle, 'and p.timestamp <  ' . $line . ' group by p.id order by p.timestamp');
    }

}
else
{
    print('<b>Liste des '.$nb.' derniers produits ajoutés ou mis à jour : </b><br><br>');
   
    if($user->rank == COMM && $filter == 1)
    {
        $p = & displayGProducts($handle, 'group by p.id order by p.timestamp desc limit ' . $nb, $user->id);
    }
    else
    {
        $p = & displayGProducts($handle, 'group by p.id order by p.timestamp desc limit ' . $nb);
    }

}

if(count($p) > 0)
{
    print('<ul>');

    foreach($p as $k => $v)
    {
         if($v[4] != '')
         {
             $extra = ' - ' . $v[4];
         }
         else
         {
             $extra = '';
         }

         print('<li><a href="edit.php?id=' . $v[0] . '&' . session_name() . '=' . session_id() . '">' . to_entities($v[1]) . '</a>' . $extra . ' <a href="' . URL . 'produits/' . $v[2] . '-' . $v[0] . '-' . $v[3] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a>');
    }

    print('</ul>');
}


?></div>
<br><br>
<div class="titreStandard"><?php

if($type_a == 'add')
{
    print('Rejet du produit ');
}
if($type_a == 'add_adv')
{
    print('Rejet du produit extranet ');
}
else if($type_a == 'edit')
{
    print('Rejet de la modification du produit ');
}
else if($type_a == 'edit_adv')
{
    print('Rejet de la modification du produit extranet ');
}
else
{
    print('Suppression du produit ');
}

print(to_entities($data[0]));

?></div><br><div class="bg">
<div class="confirm"><?php

if($type_a == 'add')
{
    print('Produit rejeté avec succès.');
}
else if($type_a == 'add_adv' || $type_a == 'edit_adv')
{
	if ($confirm && $motif !== false)
	{
		$type_t = ($type_a == 'add_adv') ? "Produit extranet rejeté" : "Modification produit extranet rejetée";
		print $type_t . " avec succès. L'annonceur a été averti du rejet et de son motif.";
	}
	else
	{
?>
	<form method="post">Motif du rejet :
		<select name="why" onchange="whyp = document.getElementsByName('why2')[0]; if (this.options.selectedIndex == 2) whyp.style.display='inline'; else whyp.style.display='none';">
			<option value="Orthographe">Orthographe</option>
			<option value="Description incomplète">Description incomplète</option>
			<option value="Autre">Autre... (préciser)</option>
		</select>
		<input size="40" maxlength="255" type="text" value="" name="why2" style="display: none" />
		<input type="button" value="Valider" onClick="this.form.submit(); this.disabled=true">
	</form>
<?php
    }
}
else if($type_a == 'edit')
{
    print('Modification rejetée avec succès.');
}
else
{
    print('Produit supprimé avec succès.');
}


?></div>
</div><?php

}  // fin autorisation ou id valide

require(ADMIN . 'tail.php');

?>
