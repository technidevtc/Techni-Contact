<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 avril 2006

 Mises à jour :

 Fichier : /secure/manager/tva/index.php
 Description : Accueil gestion des taux de TVA

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."products.php");

$title = $navBar = 'Trouver un produit';
require(ADMIN . 'head.php');

//addCustomer($handle);

$searchType   = isset($_POST['searchType'])   ? $_POST['searchType'] : '';
$idTC         = isset($_POST['idTC'])         ? trim($_POST['idTC']) : '';
$refSupplier  = isset($_POST['refSupplier'])  ? stripslashes(trim($_POST['refSupplier'])) : '';
$idAdvertiser = isset($_POST['idAdvertiser']) ? trim($_POST['idAdvertiser']) : '';

?>
<link rel="stylesheet" type="text/css" href="Products.css" />
<div class="titreStandard">Trouver un produit par son identifiant ou sa référence</div>
<br />
<div class="bg">
	<form id="TrouverProduit" name="TrouverProduit" method="post" action="find.php?<?php print(session_name() . '=' . session_id()) ?>">
		<input type="hidden" name="searchType" />
		<div class="caption">Trouver un produit</div>
		<div id="directe">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td style="width: 200px">- par son identifiant Techni-Contact:</td><td style="width: 150px"><input type="text" name="idTC" value="<?php echo $idTC ?>" onkeypress="checkEnter('by_idTC', event)" /></td><td style="width: 120px"><input type="button" class="button" value="Rechercher" onclick="findProduct('by_idTC')"></td>
				</tr><tr>
					<td colspan="3">OU</td>
				</tr><tr>
					<td style="width: 200px">- par son identifiant fournisseur :</td><td style="width: 150px"><input type="text" name="refSupplier" value="<?php echo $refSupplier ?>" onkeypress="checkEnter('by_ref', event)" /></td><td style="width: 120px"><input type="button" class="button" value="Rechercher" onclick="findProduct('by_ref')"></td>
				</tr><tr>
					<td style="width: 200px">- et l'identifiant du fournisseur :</td><td style="width: 150px"><input type="text" name="idAdvertiser" value="<?php echo $idAdvertiser ?>" onkeypress="checkEnter('by_refA', event)" /></td><td style="width: 120px"><input type="button" class="button" value="Rechercher" onclick="findProduct('by_refA')"></td>
				</tr>
			</table>
		</div>
	</form>
<script type="text/javascript">
<!--
function findProduct(by_type)
{
	document.TrouverProduit.searchType.value = by_type;
	document.TrouverProduit.submit();
}

function checkEnter(by_type, e)
{
	// supporté par ie et firefox, le plus important
	if (e.keyCode == 13)
	{
		document.TrouverProduit.searchType.value = by_type;
		document.TrouverProduit.submit();
		return false;
	}
	else return true;
}

<?

$error = false;
$errorstring = '';

// Afficher un client ou une liste de client en fonction de critère de recherche
switch ($searchType)
{
	case 'by_idTC' :
		print('document.TrouverProduit.idTC.focus();');
		if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC))
		{
			$patternPdt = "p.idTC = " . $idTC;
			$patternRef = "rc.id = " . $idTC;
		}
		else
		{
			$error = true;
			$errorstring .= "- L'identifiant Techni-Contact saisi est invalide<br />\n";
		}
		break;
		
	case 'by_ref' :
	case 'by_refA' :
		if ($searchType == 'by_ref') print('document.TrouverProduit.refSupplier.focus();');
		else print('document.TrouverProduit.idAdvertiser.focus();');
		if (refSupplier == '')
		{
			$error = true;
			$errorstring .= "- Veuillez saisir une référence Fournisseur<br />\n";
		}
		if (preg_match('/^[1-9]{1}[0-9]{0,4}$/', $idAdvertiser))
		{
			$result = & $handle->query("select id, nom1 from advertisers where id = '$idAdvertiser'");
			if ($handle->numrows($result, __FILE__, __LINE__) != 1)
			{
				$error = true;
				$errorstring .= "- Il n'existe pas d'Annonceur ou de Fournisseur ayant pour identifiant $idAdvertiser<br />\n";
			}
			else
			{
				$adv = & $handle->fetchArray($result, 'assoc');
			}
		}
		else
		{
			$error = true;
			$errorstring .= "- L'identifiant Annonceur ou Fournisseur saisi est invalide<br />\n";
		}
		
		if (!$error)
		{
			$patternPdt = "p.refSupplier = '$refSupplier' and a.id = '$idAdvertiser'";
			$patternRef = "rc.refSupplier = '$refSupplier' and a.id = '$idAdvertiser'";
		}
		break;
		
	case '' : break;
	
	default :
		$error = true;
		$errorstring .= "- Le type de recherche spécifié n'existe pas<br />\n";
}

?>

//-->
</script>
	<br />
<?php

if ($error)
{
?>
	<div style="color: #FF0000">Une ou plusieurs erreurs sont survenues :
	<br/ >
	<?php echo $errorstring ?>
	</div>
</div>
<?php
}
elseif ($searchType != '')
{
	
	$created = $ddeEdit = $ddeEditAdv = $ddeCreation = $ddeCreationAdv = false;
	
	// C'est un produit ?
	$result = & $handle->query("select p.id, p.idTC, pf.idFamily, pfr.name, pfr.ref_name, pfr.fastdesc, p.refSupplier, p.idAdvertiser, a.nom1, a.parent from products p, products_fr pfr, products_families pf, advertisers a where " . $patternPdt . " and p.id = pfr.id and p.idAdvertiser = a.id and p.id = pf.idProduct group by p.idTC", __FILE__, __LINE__);
	if ($handle->numrows($result, __FILE__, __LINE__) == 1)
	{
		$created = true;
	}
	else
	{
		// C'est une référence ?
		$result = & $handle->query("select p.id, rc.id as idTC, pf.idFamily, pfr.name, pfr.ref_name, pfr.fastdesc, rc.label, rc.refSupplier, rc.classement, p.idAdvertiser, a.nom1, a.parent from products p, products_fr pfr, products_families pf, advertisers a, references_content rc where " . $patternRef  . " and rc.idProduct = p.id and p.id = pfr.id and p.idAdvertiser = a.id and p.id = pf.idProduct group by rc.id", __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			$created = true;
		}
	}
	
	if ($created)
	{
		$pdt = & $handle->fetchArray($result, 'assoc');
		$result = & $handle->query("select id from products_add where id = '{$pdt['id']}' and type = 'm'", __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) == 1) $ddeEdit = true;
		
		$result = & $handle->query("select id from products_add_adv where id = '{$pdt['id']}' and type = 'm'", __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) == 1) $ddeEditAdv = true;
		
		$result = & $handle->query("select idProduct from sup_requests where idProduct = '{$pdt['id']}'", __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) == 1) $ddeSup = true;
	}
	else
	{
		// C'est un produit qui demande à être créé ? (les références TC des produits non existant ne sont pas encore définies, donc les recherches sont impossibles)
		$result = & $handle->query("select p.id, p.idTC, p.name, p.fastdesc, p.refSupplier, p.idAdvertiser, a.nom1, a.parent from products_add p, advertisers a where $patternPdt and p.idAdvertiser = a.id and type = 'c'", __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			$pdt = & $handle->fetchArray($result, 'assoc');
			$ddeCreation = true;
		}
		else
		{
			// C'est un produit qui demande à être créé par un annonceur ou fournisseur ?
			$result = & $handle->query("select p.id, p.idTC, p.name, p.fastdesc, p.refSupplier, p.idAdvertiser, a.nom1, a.parent from products_add_adv p, advertisers a where $patternPdt and p.idAdvertiser = a.id and type = 'c'", __FILE__, __LINE__);
			if ($handle->numrows($result, __FILE__, __LINE__) == 1)
			{
				$pdt = & $handle->fetchArray($result, 'assoc');
				$ddeCreationAdv = true;
			}
			else
			{
				$pdt = false;
			}
		}
	}





?>
</div>
<br />
<br />
<div class="titreStandard">Résultat de la recherche</div>
<br />
<div class="bg">
	<div class="produitResultat">
<?php

	//$ddeEdit $ddeEditAdv $ddeCreation $ddeCreationAdv
	if ($pdt === false)
	{
		switch ($searchType)
		{
			case 'by_idTC' :?>
		Il n'existe pas de produit ayant pour identifiant Techni-Contact <?php echo $idTC ?><br />
			<?php	break;
			case 'by_ref' :
			case 'by_refA' :?>
		Le Fournisseur <?php echo $adv['nom1'] ?> (<?php echo $adv['id'] ?>) n'a pas de produit ayant pour Référence <?php echo $refSupplier ?><br />
			<?php	break;
			}
	}
	else
	{
		$sid = '&' . session_name() . '=' . session_id();
		$isF = $pdt['parent'] == 61049 ? true : false;
		
		if (isset($pdt['classement'])) // Référence trouvée
		{
?>
		<table cellspacing="0" cellpadding="0">
			<tr><td colspan="2" class="prod">
				<a href="edit.php?<?php echo 'id=' . $pdt['id'] . $sid ?>"><?php echo $pdt['classement'] > 1 ? $pdt['classement'] . 'ème' : '1ère'?> référence du produit <?php echo $pdt['id']  ?></a>
				<a href="<?php echo URL . 'produits/' . $pdt['idFamily'] . '-' . $pdt['id'] . '-' . $pdt['ref_name'] ?>.html" target="_blank"><img src="<?php echo ADMIN_URL ?>images/web.gif" border="0"></a>
			</td></tr>
			<tr><td class="intitule">Réf. TC :</td><td><?php echo $pdt['idTC'] ?></td></tr>
			<tr><td class="intitule">Réf. Fournisseur :</td><td><?php echo $pdt['refSupplier'] ?></td></tr>
			<tr><td class="intitule">Nom	Produit :</td><td><?php echo $pdt['name'] ?></td></tr>
			<tr><td class="intitule">Description :</td><td><?php echo $pdt['fastdesc'] ?></td></tr>
			<tr><td class="intitule">Label :</td><td><?php echo $pdt['label'] ?></td></tr>
			<tr><td class="intitule"><?php echo $isF ? 'Fournisseur' : 'Annonceur'?> :</td><td><a href="../advertisers/edit.php?id=<?php echo $pdt['idAdvertiser'] ?>"><?php echo $pdt['nom1'] ?> (<?php echo $pdt['idAdvertiser']  ?>)</a></td></tr>
		</table>
<?php
		}
		else
		{
			if ($ddeCreation)
			{
?>
		<table cellspacing="0" cellpadding="0">
			<tr><td colspan="2" class="prod">
				<a href="edit.php?type=add&<?php echo 'id=' . $pdt['id'] . $sid ?>">Produit <?php echo $pdt['id'] ?> en demande de création</a>
			</td></tr>
			<tr><td class="intitule">Réf. TC :</td><td><?php echo $pdt['idTC'] ?></td></tr>
			<tr><td class="intitule">Réf. Fournisseur :</td><td><?php echo $pdt['refSupplier'] ?></td></tr>
			<tr><td class="intitule">Nom	Produit :</td><td><?php echo $pdt['name'] ?></td></tr>
			<tr><td class="intitule">Description :</td><td><?php echo $pdt['fastdesc'] ?></td></tr>
			<tr><td class="intitule"><?php echo $isF ? 'Fournisseur' : 'Annonceur'?> :</td><td><a href="../advertisers/edit.php?id=<?php echo $pdt['idAdvertiser'] ?>"><?php echo $pdt['nom1'] ?> (<?php echo $pdt['idAdvertiser']  ?>)</a></td></tr>
		</table>
<?php
			}
			elseif ($ddeCreationAdv)
			{
?>
		<table cellspacing="0" cellpadding="0">
			<tr><td colspan="2" class="prod">
				<a href="edit.php?type=add_adv&<?php echo 'id=' . $pdt['id'] . $sid ?>">Produit <?php echo $pdt['id'] ?> en demande de création par un <?php echo $pdt['parent'] == 61049 ? 'Fournisseur' : 'Annonceur' ?></a>
			</td></tr>
			<tr><td class="intitule">Réf. TC :</td><td><?php echo $pdt['idTC'] ?></td></tr>
			<tr><td class="intitule">Réf. Fournisseur :</td><td><?php echo $pdt['refSupplier'] ?></td></tr>
			<tr><td class="intitule">Nom	Produit :</td><td><?php echo $pdt['name'] ?></td></tr>
			<tr><td class="intitule">Description :</td><td><?php echo $pdt['fastdesc'] ?></td></tr>
			<tr><td class="intitule"><?php echo $isF ? 'Fournisseur' : 'Annonceur'?> :</td><td><a href="../advertisers/edit.php?id=<?php echo $pdt['idAdvertiser'] ?>"><?php echo $pdt['nom1'] ?> (<?php echo $pdt['idAdvertiser']  ?>)</a></td></tr>
		</table>
<?php
			}
			else
			{
?>
		<table cellspacing="0" cellpadding="0">
			<tr><td colspan="2" class="prod">
				<a href="edit.php?<?php echo 'id=' . $pdt['id'] . $sid ?>">Produit <?php echo $pdt['id'] ?></a>
				<a href="<?php echo URL . 'produits/' . $pdt['idFamily'] . '-' . $pdt['id'] . '-' . $pdt['ref_name'] ?>.html" target="_blank"><img src="<?php echo ADMIN_URL ?>images/web.gif" border="0"></a>
			</td></tr>
			<tr><td class="intitule">Réf. TC :</td><td><?php echo $pdt['idTC'] ?></td></tr>
			<tr><td class="intitule">Réf. Fournisseur :</td><td><?php echo $pdt['refSupplier'] ?></td></tr>
			<tr><td class="intitule">Nom	Produit :</td><td><?php echo $pdt['name'] ?></td></tr>
			<tr><td class="intitule">Description :</td><td><?php echo $pdt['fastdesc'] ?></td></tr>
			<tr><td class="intitule"><?php echo $isF ? 'Fournisseur' : 'Annonceur'?> :</td><td><a href="../advertisers/edit.php?id=<?php echo $pdt['idAdvertiser'] ?>"><?php echo $pdt['nom1'] ?> (<?php echo $pdt['idAdvertiser']  ?>)</a></td></tr>
		</table>
<?php
			}
		}
		if ($ddeEdit)
		{?>
		<br />
		- <a href="edit.php?type=edit&<?php echo 'id=' . $pdt['id'] . $sid ?>">Une demande de modification pour ce produit</a><br />
<?php	}
		
		if ($ddeEditAdv)
		{?>
		<br />
		- <a href="edit.php?type=edit_adv&<?php echo 'id=' . $pdt['id'] . $sid ?>">Une demande de modification par <?php echo $pdt['parent'] == 61049 ? 'le Fournisseur' : 'l\'Annonceur' ?> pour ce produit</a><br />
<?php	}
		
		if ($ddeSup)
		{?>
		<br />
		- <a href="sup_wait.php?<?php echo $sid ?>">En attente de validation de suppression</a><br />
<?php	}

	}
?>
	</div>
	<div class="zero"></div>
</div>
<?php

}

require(ADMIN . 'tail.php');

?>
