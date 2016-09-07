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

require(ADMIN."logs.php");
require(ICLASS . 'CustomerDevis.php');
require(ICLASS . 'Command.php');
require(ADMIN  . 'customers.php');
require(ADMIN  . 'tva.php');
require(SITE   . 'commandes.php');
require(SITE   . 'devis.php');

$handle = DBHandle::get_instance();
$user = new BOUser();


if(!$user->login())
{
    header('Location: ' . ADMIN_URL . 'login.html');
    exit();
}

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

$sid = session_name() . '=' . session_id();

function & loadProduct(& $handle, $id, $idFamily)
{
	$query = "select p.id, p.name, p.alias, p.fastdesc, p.descc as `desc`, p.descd, pg.idAdvertiser, pg.idTC, pg.refSupplier, pg.price, pg.unite, pg.idTVA, p.delai_livraison, " .
	"pg.contrainteProduit, pg.tauxRemise, pg.price2, a.parent from products_fr p, products_families pf, products pg, advertisers a where pf.idProduct = " . $id .
	" and pf.idFamily = " . $idFamily . " and p.id = pf.idProduct and p.id = pg.id and a.id = pg.idAdvertiser and a.actif = 1";
	$result = & $handle->query($query, __FILE__, __LINE__);
	if ($handle->numrows($result, __FILE__, __LINE__) == 1) return $handle->fetchArray($result);
	else return false;
}

$products_filter = isset($_GET['products_filter']) ? (int)$_GET['products_filter'] : 0;

if(!isset($_GET['family_ref_name']))
{
	if(!isset($_GET['product_ID']) || !isset($_GET['family_ID'])
	|| !preg_match('/^[1-9]{1}[0-9]{0,7}$/', $_GET['product_ID']) || !preg_match('/^[1-9]{1}[0-9]{0,5}$/', $_GET['family_ID'])
	|| !($pdt = & loadProduct($handle, $_GET['product_ID'], $_GET['family_ID'])))
	{
		print '__ERROR__<col_separator_p4le1iazia8rLab8>Erreur fatal lors du chargement du produit ' . $_GET['product_ID'];
		exit;
	}
	
	if($pdt['price'] == 'ref')
	{
		$tab_ref_cols = array();
		$tab_ref_lines = array();
		
	    if($result = & $handle->query("select content from references_cols where idProduct = " . $pdt['id'], __FILE__, __LINE__, false))
		{
		    $data = & $handle->fetch($result);
			$tab_ref_cols = mb_unserialize($data[0]);
			if ($tab_ref_cols[2] == 'Référence Fournisseur') $online_sell = true; // tableau de référence fournisseur normal
			else $online_sell = false; // le tableau de référence est celui d'un annonceur après lot3
		}
		
	    if ($online_sell)
		{
			$result = & $handle->query("
        SELECT id, label, content, refSupplier, price, idTVA, unite
        FROM references_content
        WHERE idProduct = " . $pdt['id'] . " AND vpc = 1 AND deleted = 0
        ORDER BY classement", __FILE__, __LINE__, false);
		    while($data = & $handle->fetchAssoc($result))
				$tab_ref_lines[] = $data;
		}
	}
	elseif (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $pdt['price']))
	{
		if ($pdt['price2'] != '0' && preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $pdt['price2'])) $online_sell = true;
		else $online_sell = false;
	}
	else $online_sell = false;
	
	if ($products_filter == 1 && !$online_sell)
	{
		print "__ERROR__<col_separator_p4le1iazia8rLab8>Ce produit a bien sa vente en ligne activée, mais il n'a pas encore été mis à jour pour pouvoir être commandé";
	    exit;
	}
	
	if (!($family = loadFamilyByID($handle, $_GET['family_ID'])))
	{
		print '__ERROR__<col_separator_p4le1iazia8rLab8>Erreur fatal lors du chargement de la famille ' . $_GET['family_ID'];
	    exit;
	}

	$cur_family = $family;
	$cur_family_tree = array('count' => 1, 1 => $family);
	if (!($family = get_family_parent_tree($handle, $family, $cur_family_tree)))
	{
		print "__ERROR__<col_separator_p4le1iazia8rLab8>Erreur fatal lors du chargement de l'arbre des familles";
		exit;
	}
	$top_family = & $family;

	$cur_family_tree2 = array();
	for ($k2 = $cur_family_tree['count'], $k = 1; $k2 > 0; $k2--, $k++)
		$cur_family_tree2[$k] = $cur_family_tree[$k2];
	$cur_family_tree = & $cur_family_tree2;
	
	$tag_opened = array(1 => false, 2 => false, 3 => false);
	print "__FAMILIES_FIXED__<col_separator_p4le1iazia8rLab8>\n";
	print_family_menu_fixed($top_family, 1, $tag_opened);
	print "<col_separator_p4le1iazia8rLab8>";
	
?>
	<input type="hidden" id="FamiliesExplore_ProductID" value="<?php echo $pdt['id'] ?>"/>
	<input type="hidden" id="FamiliesExplore_ProductIDTC" value="<?php echo $pdt['idTC'] ?>"/>
	<input type="hidden" id="FamiliesExplore_FamilyID" value="<?php echo $_GET['family_ID'] ?>"/>
	<div id="produit">
		<div class="liUn">
			<div class="back">&laquo; <a href="<?php echo URL . 'familles/' . to_entities($cur_family_tree[3]['ref_name']) . '.html' ?>">Voir tous les produits de la cat&eacute;gorie</a></div>
<?php
	if (is_file(PRODUCTS_IMAGE_INC . $pdt['id'] . '.jpg'))
	{
		if (is_file(PRODUCTS_IMAGE_INC . 'zoom/' . $pdt['id'] . '.jpg'))
		{
?>
			<div class="img" id="imgUn">
				<img src="<?php echo PRODUCTS_IMAGE_ADMIN_URL . $pdt['id'] ?>.jpg" alt="<?php echo to_entities($pdt['name']) ?>" />
				<em>[ <a href="#" onmouseover="document.getElementById('imgDeux').style.display = 'inline'; document.getElementById('imgUn').style.display = 'none';">zoom</a> ] </em>
			</div>
			<div class="imgDeux" id="imgDeux">
				<img src="<?php echo PRODUCTS_IMAGE_ADMIN_URL . $pdt['id'] ?>-zoom.jpg" alt="<?php echo to_entities($pdt['name']) ?>" onmouseout="document.getElementById('imgUn').style.display = 'inline'; document.getElementById('imgDeux').style.display = 'none';" />
			</div>
<?php	} else {
?>			<div class="img" id="img">
				<img src="<?php echo PRODUCTS_IMAGE_ADMIN_URL . $pdt['id'] ?>.jpg" alt="<?php echo to_entities($pdt['name']) ?>" />
			</div>
<?php
		}
	}
?>
			<h2>description du produit :</h2>
			<?php echo $pdt['desc'] ?> 
<?php
	if(!empty($pdt['descd']))
	{
?>			<h2>description technique :</h2>
			<?php echo $pdt['descd'] ?>
<?php
	}
	
	if ($pdt['delai_livraison'] == '')
	{
		$res = & $handle->query("select delai_livraison from advertisers where id = " .$pdt['idAdvertiser']);
		$record = & $handle->fetch($res);
		$pdt['delai_livraison'] = $record[0];
	}
	
	if ($pdt['delai_livraison'] != '')
		print "			<h2>d&eacute;lais de livraison habituels : &nbsp;&nbsp;<span>" . to_entities($pdt['delai_livraison']) . "</span></h2>\n";
	
	if ($pdt['contrainteProduit'] > 1)
		print "			<h2>Nombre de produit minimum à commander : &nbsp;&nbsp;<span>" . to_entities($pdt['contrainteProduit']) . "</span></h2>\n";
	
	$tauxRemise_tab = mb_unserialize($pdt['tauxRemise']);
	if ($tauxRemise_tab[0] != '' && $tauxRemise_tab[1] != '')
		print "			<h2>Taux de remise pour " . $tauxRemise_tab[0] . " produits : &nbsp;&nbsp;<span>" . $tauxRemise_tab[1] . "%</span></h2>\n";

	if ($tauxRemise_tab[2] != '' && $tauxRemise_tab[3] != '')
		print "			<h2>Taux de remise pour " . $tauxRemise_tab[2] . " produits : &nbsp;&nbsp;<span>" . $tauxRemise_tab[3] . "%</span></h2>\n";
	
	if ($pdt['price'] != 'ref')
	{
?>
			<h2>Unit&eacute; : &nbsp;&nbsp;<span><?php echo $pdt['unite'] ?></span></h2>
			<br />
			<div class="zero"></div>
			<div class="prixProduit">
				<form method="post" action="">
					<input type="hidden" name="famille" value="<?php echo $_GET['priv_shortidfamily'] ?>">
					<input type="hidden" name="produit" value="<?php echo $_GET['priv_idproduct'] ?>">
					<input type="hidden" name="idTC" value="">
					<table id="prixProduit" cellspacing="0" cellpadding="0"><tr>
						<td class="prixLabel">Prix :</td><td class="prix"><?php echo $pdt['price'] ?> € HT</td>
					</tr></table>
				</form>
			</div>
<?php
	}
	else
	{
?>			<div class="zero"></div>
			<div id="ref">
				<h2>R&eacute;f&eacute;rences :</h2> 
				<br />
				<form name="buy" method="post" action="">
					<table id="ref_list" border="0" align="center" cellpadding="0" cellspacing="0" style="width: auto; margin: 0">
						<thead>
						<tr>
							<th>R&eacute;f. TC</th>
							<th>Libell&eacute;</th>
<?php	for($i = 3; $i < count($tab_ref_cols)-5; ++$i)
		{
			print "							<th>" . to_entities($tab_ref_cols[$i]) . "</th>\n";
		}
?>							<th>Unit&eacute;</th>
							<th>Prix HT</th>
						</tr>
						</thead>
						<tbody>
<?php	for($i = 0; $i < count($tab_ref_lines); ++$i)
		{
			$ref = & $tab_ref_lines[$i];
			$content = mb_unserialize($ref['content']);
?>						<tr>
							<td><?php echo to_entities($ref['id']) ?></td>
							<td><?php echo to_entities($ref['label']) ?></td>
<?php		for($j = 0; $j < count($content); ++$j)
			{
				if (trim($content[$j]) == '') $content[$j] = '-';
				print "							<td>" . to_entities($content[$j]) . "</td>\n";
			}
?>							<td><?php echo to_entities($ref['unite']) ?></td>
							<td class="ref-prix"><?php echo to_entities(sprintf('%.02f',$ref['price'])) ?>€</td>
							<!--<td class="ref-buy"><img src="../ressources/buy.gif" alt="Acheter" onclick="AddProduct(<?php echo $pdt['id'] ?>, <?php echo $ref['id'] ?>, document.getElementById('ProductQuantity<?php echo $ref['id'] ?>').value)" /></td>-->
						</tr>
<?php	}
?>						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
<?php
	}
}
else
{
	if (!preg_match('/^[0-9a-z\-]+$/', $_GET['family_ref_name']) || !($family = loadFamilyByName($handle, $_GET['family_ref_name'])))
	{
	    print '__ERROR__<col_separator_p4le1iazia8rLab8>Erreur fatal lors du chargement de la famille' . $_GET['family_ref_name'];
	    exit;
	}

	$family['child'] = get_family_sub_tree($handle, $family['id'], __MAX_DEPTH__);
	// Listage des produits avec menu de gauche fixe
	if ($family['child'] == null)
	{
		unset($family['child']);
		
		// On recherche les familles parente pour constituer un arbre
		$cur_family = $family;
		$cur_family_tree = array('count' => 1, 1 => $family);
		if (!($family = get_family_parent_tree($handle, $family, $cur_family_tree)))
		{
			print "__ERROR__<col_separator_p4le1iazia8rLab8>Erreur fatal lors du chargement de l'arbre des familles";
			exit;
		}
		$top_family = & $family;
		
		$cur_family_tree2 = array();
		for ($k2 = $cur_family_tree['count'], $k = 1; $k2 > 0; $k2--, $k++)
			$cur_family_tree2[$k] = $cur_family_tree[$k2];
		$cur_family_tree = & $cur_family_tree2;
		
		// On affiche le menu de gauche
		$tag_opened = array(1 => false, 2 => false, 3 => false);
		print "__FAMILIES_FIXED__<col_separator_p4le1iazia8rLab8>\n";
		print_family_menu_fixed($top_family, 1, $tag_opened);
		
		print "<col_separator_p4le1iazia8rLab8>";

		// On regarde combien il y a de produit dans cette famille
		$query = "select count(pfam.idProduct) from products_families pfam, products p, advertisers a where pfam.idProduct = p.id and p.idAdvertiser = a.id ";
		if ($products_filter == 1) $query .= "and a.parent = 61049 ";
		elseif($products_filter == 2) $query .= "and a.parent != 61049 ";
		$query .= "and idFamily = " .$cur_family['id'] . " and a.actif = 1";
		$result = & $handle->query($query, __FILE__, __LINE__);
		$record = & $handle->fetch($result);
		$nbpdt  = $record[0];
		
		if ($nbpdt > 0)
		{
			define('NB', 20);
			
			// Détermination de la page
			$page = isset($_GET['page'])? trim($_GET['page']) : 1;
			settype($page, 'integer');
			if ($page < 1) $page = 1;
			elseif (($page-1) * NB >= $nbpdt) $page = ($nbpdt - $nbpdt%NB) / NB + 1;
			
			if ($nbpdt > NB)
			{
				$lastpage = ceil($nbpdt/NB);
?>
	<div class="listing">
		<span style="visibility: <?php echo $page > 2 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . 1 . ''  ?>">&lt;&lt;</a></span>
		<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page-1) . ''  ?>">&lt;</a> ... |</span>
		<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page-1) . '' ?>"><?php echo ($page-1)  ?></a> |</span>
		<span class="listing-current"><?php echo $page ?></span>
		<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| <a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page+1) . '' ?>"><?php echo ($page+1)  ?></a></span>
		<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| ... <a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page+1) . ''  ?>">&gt;</a></span>
		<span style="visibility: <?php echo $page < $lastpage-1 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($lastpage) . ''  ?>">&gt;&gt;</a></span>
	</div>
<?php
			}
			
			print "	<div id=\"liste\">\n";
			$n = 0;
			$result = & $handle->query("select p.id, pf.ref_name, pf.name, pf.fastdesc, p.price, p.price2 from products p, products_fr pf, products_families pfam, advertisers a where p.id = pf.id and p.id = pfam.idProduct and p.idAdvertiser = a.id and a.actif = 1 and a.parent = 61049 and pfam.idFamily = " . $cur_family['id'] . " order by pf.ref_name limit " . (($page-1)*NB) . "," . NB, __FILE__, __LINE__);
			while ($pdt = & $handle->fetchAssoc($result))
			{
				// On vérifie que le produit peut bien être acheté
				$online_sell = true;
				if (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $pdt['price']))
				{
					if ($pdt['price2'] == '0' || !preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $pdt['price2'])) $online_sell = false;
				}
				elseif ($pdt['price'] == 'ref')
				{
					$res = & $handle->query("select content from references_cols where idProduct = " . $pdt['id'], __FILE__, __LINE__);
					$data = & $handle->fetch($res);
					$tab_ref_cols = mb_unserialize($data[0]);
					if ($tab_ref_cols[2] != 'Référence Fournisseur') $online_sell = false;
				}
				else $online_sell = false;
				
				if ($products_filter == 1 && !$online_sell) continue;	// show only selling products
				if ($products_filter == 2 && $online_sell) continue;	// show only non-selling products
				
				// On affiche les produits avec des style spécifiques en fonction de leurs positions
				if ($n %4 == 0) print "		<div class=\"liUn\">\n";
				elseif (($n %4 == 2))  print "		<div class=\"liDeux\">\n";
				
				if ($n %2 == 0) print '			<div class="elt">';
				else print '			<div class="eltDeux">';
				
				$link = 'produits/' . $cur_family['id'] . '-' . $pdt['id'] . '-' . $pdt['ref_name'];
				$picture = is_file(PRODUCTS_IMAGE_INC . $pdt['id'] . '.jpg') ? '<img src="' . PRODUCTS_IMAGE_ADMIN_URL . $pdt['id'] . '.jpg" alt="' . to_entities($pdt['name']) . '"  />' : '';
				print $picture . '<a href="' . $link . '">' . to_entities($pdt['name']) . '</a>' . to_entities($pdt['fastdesc']) . "</div>\n";
				
				if ($n %2 == 1) print "		</div><div class=\"zero\"></div>\n";
				
				$n++;
			}
			if ($n %2 == 1) print "		</div>\n";
			print "		</div>\n";
			
			if ($nbpdt > NB)
			{
?>
	<div class="listing">
		<span style="visibility: <?php echo $page > 2 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . 1 . ''  ?>">&lt;&lt;</a></span>
		<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page-1) . ''  ?>">&lt;</a> ... |</span>
		<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page-1) . '' ?>"><?php echo ($page-1)  ?></a> |</span>
		<span class="listing-current"><?php echo $page ?></span>
		<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| <a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page+1) . '' ?>"><?php echo ($page+1)  ?></a></span>
		<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| ... <a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($page+1) . ''  ?>">&gt;</a></span>
		<span style="visibility: <?php echo $page < $lastpage-1 ? 'visible' : 'hidden'?>"><a href="<?php echo 'familles/' . $cur_family['ref_name'] . ',' . ($lastpage) . ''  ?>">&gt;&gt;</a></span>
	</div>
<?php
			}
		}
		else
		{
?>
	<div id="liste">
		<br />
		<br />
		Aucun produit n'est pr&eacute;sent dans cette cat&eacute;gorie
	</div>
<?php
		}
	}
	else // Listage des familles dynamiques
	{
		// On recherche les familles parente pour constituer un arbre
		$cur_family = $family;
		while ($family['idParent'] != 0)
		{
			if (!($family = loadFamilyByID($handle, $family['idParent'])))
			{
				header('Location: ' . URL);
				exit;
			}
		}
		$family['child'] = get_family_sub_tree($handle, $family['id'], 1);
		$top_family = & $family;
		
		// On affiche le menu de gauche
		$tag_opened = array(1 => false, 2 => false, 3 => false);
		print "__FAMILIES_DYNAMIC__<col_separator_p4le1iazia8rLab8>\n";
		print_family_menu_dynamic($top_family, 1, $tag_opened);
		
		print "<col_separator_p4le1iazia8rLab8>";
?>
	<div id="famille">
<?php
		$num_family = 0;
		
		// On affiche les familes à droite de façon dynamique
		foreach ($top_family['child'] as $s_fam)
		{
			
			if ($num_family%4 == 0) print "<div class=\"elt\">\n";
			elseif ($num_family%4 == 2) print "<div class=\"eltDeux\">\n";
			$toprint = '';
			$nbpdt = 0;
			foreach ($s_fam['child'] as $ss_fam)
			{
				$toprint .= '		<a href="familles/' . $ss_fam['ref_name'] . '">' . $ss_fam['name'] . " (" . $ss_fam['nbpdt'] . ")</a>\n";
				$nbpdt   += $ss_fam['nbpdt'];
			}
			print '	<div class="elt_float" id="folder'. $s_fam['id'] . '_s"><strong>' . $s_fam['name'] . " (" . $nbpdt . ")</strong>\n";
			print $toprint;
			print "	</div>\n";
			if ($num_family++%2 == 1) print "<div class=\"zero\"></div>\n</div>\n";
		}
		if ($num_family%2 == 1) print "<div class=\"zero\"></div>\n</div>\n";
		
		if ($cur_family['id'] != $top_family['id'])
			print "<col_separator_p4le1iazia8rLab8>" . $cur_family['id'];

?>
	</div>
<?php
	}
}
