<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 avril 2006

 Fichier : /www/devis_add.php
 Description : Ajout d'un devis

/=================================================================*/

/*
$tab_coord = & $commande->loadCoord();
$listpdt   = & $commande->loadProducts();
$cmdInfos  = & $commande->loadInfos();
*/

require(ADMIN  . 'statut.php');

$cmdAll = & $commande->loadAll();

$tab_coord = & $cmdAll['coord'];
$listpdt   = & $cmdAll['produits'];
$cmdInfos  = & $cmdAll['infos'];

$typePaiement = getTypePaiement($cmdInfos['type_paiement']);
$statutPaiement = getStatutPaiement($cmdInfos['statut_paiement']);
$statutTraitement = getStatutTraitementGlobal($cmdInfos['statut_traitement']);

?>
<br />
<br />
<div class="titreStandard">Commande n°<?php echo $commandID ?> du client n°<?php echo $clientID ?></div>
<br />
<div class="bg">
	<a href="index.php?id=<?php echo $clientID ?>"><< Aller à la fiche client</a>
<?php
foreach ($tab_coord as $coord => $value) $tab_coord[$coord] = to_entities($value);
  
switch ($tab_coord['titre'])
{
	case 1  : $titre = 'M.'; break;
	case 2  : $titre = 'Mme'; break;
	case 3  : $titre = 'Mlle'; break;
	default : $titre = 'M.'; break;
}

switch ($tab_coord['titre_l'])
{
	case 1  : $titre_l = 'M.'; break;
	case 2  : $titre_l = 'Mme'; break;
	case 3  : $titre_l = 'Mlle'; break;
	default : $titre_l = 'M.'; break;
}

?>
	<div id="deviscmd">
		<div id="tabdeviscmd">
			<div style="float: right">
				<div class="infos2"><div class="intitule">Date de création : </div><div class="valeur">le <?php echo date("d/m/Y à H:i.s", $cmdInfos['create_time']) ?></div></div>
				<div class="infos2"><div class="intitule">Dernière mise à jour : </div><div class="valeur">le <?php echo date("d/m/Y à H:i.s", $cmdInfos['timestamp']) ?></div></div>
			</div>
			<div class="infos">Contenu de la commande n°<?php echo $commande->getID() ?></div>
			<div class="miseAZero"></div>
			<table id="produits" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th style="width: 105px">Réf. Produit</th>
						<th style="width: 115px">Réf. TC</th>
						<th style="width: 330px">Désignation</th>
						<th style="width: 50px">Qté.</th>
						<th style="width: 50px">Unité</th>
						<th style="width: 100px">P.U. Euro HT</th>
						<th style="width: 100px">MT Euro HT</th>
						<th style="width: 49px">Tva</th>
					</tr>
				</thead>
				<tbody>
<?php

$totalHT = 0;

$total_par_Taux  = array();
$pdtTauxRemise = $pdtQteList = $pdtSumList = array();

for($i = 0; $i < count($listpdt); ++$i)
{
	$pdt = & $listpdt[$i];

	if ($pdt['fastdesc'] != '') $pdt['fastdesc'] = ' - ' . $pdt['fastdesc'];
	if ($pdt['label'] != '') $pdt['label'] = ' - ' . $pdt['label'];
	
	$pdt_somme = $pdt['quantity'] * $pdt['price'];
	$totalHT += $pdt_somme;
	
	if (isset($pdtQteList[$pdt['idProduct']]))
	{
		$pdtQteList[$pdt['idProduct']]['qty'] += $pdt['quantity'];
	}
	else
	{
		$pdtQteList[$pdt['idProduct']]['qty']  = $pdt['quantity'];
		$pdtQteList[$pdt['idProduct']]['name'] = $pdt['name'];
		$pdtSumList[$pdt['idProduct']] = array();
	}
	$pdtSumList[$pdt['idProduct']][$pdt['idTC']]['sum'] = $pdt_somme;
	$pdtSumList[$pdt['idProduct']][$pdt['idTC']]['tva'] = $pdt['tauxTVA'];
	
	if ($pdt['tauxRemise'] != '' && !isset($pdtTauxRemise[$pdt['idProduct']]))
		$pdtTauxRemise[$pdt['idProduct']] = mb_unserialize($pdt['tauxRemise']);
	
	$total_par_Taux[$pdt['tauxTVA']] += $pdt_somme;
?>
					<tr>
						<td><?php echo to_entities($pdt['idProduct']) ?></td>
						<td><?php echo to_entities($pdt['idTC']) ?></td>
						<td><?php echo to_entities($pdt['name']) . to_entities($pdt['fastdesc'] . $pdt['label']) ?></td>
						<td class="ref-qte"><?php echo $pdt['quantity'] ?></td>
						<td><?php echo $pdt['unite'] ?></td>
						<td class="ref-prix"><?php echo to_entities(sprintf("%0.2f", $pdt['price'])) ?></td>
						<td class="ref-prix"><?php echo sprintf("%0.2f", $pdt_somme) ?></td>
						<td class="tva"><?php echo $pdt['tauxTVA'] ?></td>
					</tr>
<?php
}

foreach ($pdtTauxRemise as $idProduct => $remises)
{
	if ($pdtQteList[$idProduct]['qty'] >= $remises[0])
	{
		if (isset($remises[2]) && $pdtQteList[$idProduct]['qty'] >= $remises[2])
			$remise = $remises[3];
		else
			$remise = $remises[1];
		
		$sommeRemise = 0;
		foreach ($pdtSumList[$idProduct] as $_idtc => $_ref)
		{
			$refRemise = $_ref['sum'] * $remise / 100;
			$total_par_Taux[$_ref['tva']] -= $refRemise;
			$sommeRemise += $refRemise;
			
		}
		$totalHT -= $sommeRemise;
?>
					<tr>
						<td class="ref-remise" colspan="6">Remise de <b><?php echo $remise . '%</b> pour <b>' . $pdtQteList[$idProduct]['qty'] . '</b> x ' . $pdtQteList[$idProduct]['name'] ?>
						</td>
						<td class="ref-remise2"><?php echo to_entities(sprintf("%0.2f", -$sommeRemise)) ?></td>
						<td class="ref-remise3"></td>
					</tr>
<?php
	}
}

krsort($total_par_Taux);
$totalTVA_par_Taux = array();
$totalTVA = 0;
foreach ($total_par_Taux as $_taux => $_total)
{
	$totalTVA_par_Taux[$_taux] = $_total * $_taux / 100;
	$totalTVA += $totalTVA_par_Taux[$_taux];
}

$totalTVA = ceil($totalTVA*100)/100;
$stotalHT  = ceil($totalHT *100)/100;
$totalHT = $stotalHT + $cmdInfos['fdp'];
$totalTTC = $totalHT + $cmdInfos['fdp'] * $cmdInfos['fdp_tva'] / 100 + $totalTVA;;

?>
				</tbody>
			</table>
			<br />
			<div id="montant-totaux">
				<div class="total_H">
					<div class="total_G">Sous-total HT :</div>
					<div class="total_D"><?php echo sprintf("%.02f", $stotalHT) ?>€</div>
				</div>
				<div class="total_Hn">
					<div class="total_G">Frais de Port HT :</div>
					<div class="total_D"><?php echo sprintf("%.02f", $cmdInfos['fdp']) ?>€</div>
				</div>
				<div class="total_Hn">
					<div class="total_G">Total HT :</div>
					<div class="total_D"><?php echo sprintf("%.02f", $totalHT) ?>€</div>
				</div>
				<div class="total_Hn">
					<div class="total_G">Total TTC :</div>
					<div class="total_D"><?php echo sprintf("%.02f", $totalTTC) ?>€</div>
				</div>
			</div>
			<table id="tva" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th style="width: 150px">Base € HT</th>
						<th style="width: 60px">Taux</th>
						<th style="width: 139px">Montant TVA</th>
					</tr>
				</thead>
				<tbody>
<?php
foreach ($total_par_Taux as $_taux => $_total)
{
	print
'					<tr><td class="base_euro">' . sprintf("%.02f", $_total) . '</td><td>' . sprintf("%.02f", $_taux) . '</td><td class="montant_tva">' . sprintf("%.02f", $totalTVA_par_Taux[$_taux]) . "</td></tr>\n";
}
?>
				</tbody>
				<tfoot>
					<tr>
						<td class="total"><div><?php echo sprintf("%.02f", $totalHT) ?></div>Total</td><td class="tvas"></td><td class="total-tva"><?php echo sprintf("%.02f", $totalTVA) ?></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<br />
		<br />
		<div class="infos2"><div class="intitule">Mode de paiement : </div><div class="valeur"><?php echo $typePaiement ?></div></div>
		<div class="infos2"><div class="intitule">Statut de paiement : </div><div class="valeur"><?php echo $statutPaiement ?></div></div>
		<div class="infos2"><div class="intitule">Statut de traitement : </div><div class="valeur"><?php echo $statutTraitement ?></div></div>
		<br />
		<br />
		<div class="livraison">
			<div class="titreBloc">Adresse de livraison</div>
			<div class="coord">
				<b><?php echo $titre_l ?> <?php echo $tab_coord['nom_l'] ?> <?php echo $tab_coord['prenom_l'] ?></b><br />
				<?php echo $tab_coord['societe_l'] != '' ? $tab_coord['societe_l'] . '<br />' : '' ?>
				<?php echo $tab_coord['adresse_l'] ?><br />
				<?php echo $tab_coord['complement_l'] ?> <?php echo $tab_coord['cp_l'] ?> <?php echo $tab_coord['ville_l'] ?><br />
				<?php echo $tab_coord['pays_l'] ?><br />
				<?php echo $tab_coord['societe_l'] != '' ? '' : '<br />' ?>
			</div>
		</div>
		<div class="facturation">
			<div class="titreBloc">Adresse de facturation</div>
			<div class="coord">
				<b><?php echo $titre ?> <?php echo $tab_coord['nom'] ?> <?php echo $tab_coord['prenom'] ?></b><br />
				<?php echo $tab_coord['societe'] != '' ? $tab_coord['societe'] . '<br />' : '' ?>
				<?php echo $tab_coord['adresse'] ?><br />
				<?php echo $tab_coord['complement'] ?> <?php echo $tab_coord['cp'] ?> <?php echo $tab_coord['ville'] ?><br />
				<?php echo $tab_coord['pays'] ?><br />
				<?php echo $tab_coord['societe'] != '' ? '' : '<br />' ?>
			</div>
		</div>
	</div>
	<div class="miseAZero"></div>
</div>
