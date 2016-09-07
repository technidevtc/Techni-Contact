<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 novembre 2006

 Mises à jour :

 Fichier : /secure/manager/commandes/index.php
 Description : Accueil gestion des commandes clients
 
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . 'Command.php');
require(ADMIN  . 'statut.php');

$title = $navBar = 'Gestion des Produits Phares';

require(ADMIN . 'head.php');

$pdtpresent = array();
$result = & $handle->query("select id from products_flagship", __FILE__, __LINE__);
while($pdt = & $handle->fetch($result)) $pdtpresent[$pdt[0]] = true;

if (isset($_POST['todo']))
{
	list($action, $id) = explode('_', $_POST['todo']);
	switch ($action)
	{
		case 'add' :
			if (count($pdtpresent) < 10 && preg_match('/^[1-9]{1}[0-9]{1,7}$/', $id) & !isset($pdtpresent[$id]))
			{
				$handle->query("insert into products_flagship values (" . $id . ")");
				$pdtpresent[$id] = $id;
			}
			break;
		
		case 'del' :
			if (preg_match('/^[1-9]{1}[0-9]{1,7}$/', $id) & isset($pdtpresent[$id]))
			{
				$handle->query("delete from products_flagship where id = " . $id);
				unset($pdtpresent[$id]);
			}
			break;
		
		case 'clear' :
				$handle->query("delete from products_flagship");
				unset($pdtpresent);
				$pdtpresent = array();
			break;
		
		default :
			break;
	}
}
switch(count($pdtpresent))
{
	case 0 : $recap_sentence = "Il n'y a actuellement aucun produit phare."; break;
	case 1 : $recap_sentence = "Il y a actuellement un produit phare sur les 10 maximum autorisés"; break;
	default : $recap_sentence = "Il y a actuellement " . count($pdtpresent) . " produits phares sur les 10 maximum autorisés"; break;
}

?>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';
</script>
<link rel="stylesheet" type="text/css" href="Products.css" />
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script src="ProductsFlagship.js" type="text/javascript"></script>
<?php
require('ProductExplorerWindow.php');
?>
<div class="titreStandard">Liste des Produits phares du moment</div>
<br />
<div class="bg">
	<form name="ManagerProductsFlagship" method="post" action="ProductsFlagship.php?<?php echo $sid ?>">
	<div><input type="hidden" name="todo" value=""></div>
	</form>
	<h3><?php echo $recap_sentence ?></h3>
	<input type="button" value="Ajouter un produit" class="fValidUn" style="width: 150px" onclick="showProductExplorerWindow()" /> &nbsp;
	<input type="button" value="Supprimer tous les produits phares" class="fValidUn" style="width: 250px" onclick="ClearProducts()" /><br />
	<br />
<?php
if (!empty($pdtpresent))
{
	$n = 0;
	$result = & $handle->query("select pfs.id, p.idTC, pfam.idFamily, pfr.name, pfr.ref_name, pfr.fastdesc, p.refSupplier, p.idAdvertiser, a.nom1, a.parent from products_flagship pfs, products p, products_fr pfr, products_families pfam, advertisers a where pfs.id = p.id and p.id = pfr.id and pfr.id = pfam.idProduct and p.idAdvertiser = a.id group by pfs.id", __FILE__, __LINE__);
	while ($pdt = & $handle->fetchAssoc($result))
	{
		$isF = $pdt['parent'] == 61049 ? true : false;
?>
	<div class="produitResultat">
		<table cellspacing="0" cellpadding="0">
			<tr><td colspan="2" class="prod">
				<img src="<?php echo ADMIN_URL ?>images/b_drop.png" style="float: right; margin-top: 3px; cursor: pointer;" title="Supprimer ce produit phare" onclick="DelProduct(<?php echo $pdt['id'] ?>)" />
				<a href="edit.php?<?php echo $sid . '&id=' . $pdt['id'] ?>" title="Editer ce produit"> Produit n° <?php echo $pdt['id'] ?></a>
				<a href="<?php echo URL . 'produits/' . $pdt['idFamily'] . '-' . $pdt['id'] . '-' . $pdt['ref_name'] ?>.html" target="_blank"><img src="<?php echo ADMIN_URL ?>images/web.gif" title="Voir le produit en ligne" style="border: 0; margin-bottom: -2px" /></a>
			</td></tr>
			<tr><td class="intitule">Réf. TC :</td><td><?php echo $pdt['idTC'] ?></td></tr>
			<tr><td class="intitule">Réf. Fournisseur :</td><td><?php echo $pdt['refSupplier'] ?></td></tr>
			<tr><td class="intitule">Nom Produit :</td><td><?php echo $pdt['name'] ?></td></tr>
			<tr><td class="intitule">Description :</td><td><?php echo $pdt['fastdesc'] ?></td></tr>
			<tr><td class="intitule">Label :</td><td><?php echo $pdt['label'] ?></td></tr>
			<tr><td class="intitule"><?php echo $isF ? 'Fournisseur' : 'Annonceur'?> :</td><td><a href="../advertisers/edit.php?id=<?php echo $pdt['idAdvertiser'] ?>" title="Editer <?php echo $isF ? 'ce fournisseur' : 'cet annonceur' ?>"><?php echo $pdt['nom1'] ?> (<?php echo $pdt['idAdvertiser']  ?>)</a></td></tr>
		</table>
	</div>
<?php
	if ($n%3 == 2) print "	<div class=\"zero\"></div>\n";
	$n++;
	
	}
}
?>
<div class="zero"></div>
</div>
<br />
<?php
require(ADMIN . 'tail.php');
?>
