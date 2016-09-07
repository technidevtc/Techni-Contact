<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 avril 2006

 Fichier : /www/devis_add.php
 Description : Ajout d'un devis

/=================================================================*/

$listpdt    = & $devis->loadProducts();
$devisTimes = & $devis->loadTimes();

?>
<br />
<br />
<div class="titreStandard">Devis n°<?php echo $devisID ?> du client n°<?php echo $clientID ?></div>
<br />
<div class="bg">
	<a href="index.php?id=<?php echo $clientID ?>"><< Aller à la fiche client</a>
	<div id="deviscmd">
		<div id="tabdeviscmd">
			<div style="float: right">
				<div class="infos2"><div class="intitule">Date de création : </div><div class="valeur">le <?php echo date("d/m/Y à H:i.s", $devisTimes['create_time']) ?></div></div>
				<div class="infos2"><div class="intitule">Dernière mise à jour : </div><div class="valeur">le <?php echo date("d/m/Y à H:i.s", $devisTimes['timestamp']) ?></div></div>
			</div>
			<div class="infos">Devis N° <?php echo $devisID ?></div>
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

$listeTVAs = & getTVAs($handle, ' order by taux desc');
$total_par_Taux = array();
foreach ($listeTVAs as $_id => $_taux)
	$total_par_Taux[$_id] = 0;

$pdtTauxRemise = $pdtQteList = $pdtSumList = array();

for($i = 0; $i < count($listpdt); ++$i)
{
	$ok = false;
	
	if ($listpdt[$i]['idTC'] == '0')
	{
		if(($result = & $handle->query('select pg.idTC, p.name, p.fastdesc, pg.price, pg.unite, pg.idTVA, pg.tauxRemise from products_fr p, products pg where p.id = \'' . $handle->escape($listpdt[$i]['id']) . '\' and p.id = pg.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
			$ok = true;
	}
	else
	{
		if(($result = & $handle->query('select rc.id as idTC, p.name, p.fastdesc, rc.label, rc.price, rc.unite, rc.idTVA, pg.tauxRemise from products_fr p, products pg, references_content rc where p.id = \'' . $handle->escape($listpdt[$i]['id']) . '\' and rc.id = \'' . $handle->escape($listpdt[$i]['idTC']) . '\' and p.id = rc.idProduct and p.id = pg.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
			$ok = true;
	}
	
	
	if ($ok)
	{
		$pdt = & $handle->fetchArray($result, 'assoc');
		$pdt['id']       = $listpdt[$i]['id'];
		$pdt['quantity'] = $listpdt[$i]['quantity'];
		
		if ($pdt['fastdesc'] != '') $pdt['fastdesc'] = ' - ' . $pdt['fastdesc'];
		if ($pdt['label'] != '') $pdt['label'] = ' - ' . $pdt['label'];
		
		$pdt_somme = $pdt['quantity'] * $pdt['price'];
		$totalHT += $pdt_somme;

		if (isset($pdtQteList[$pdt['id']]))
		{
			$pdtQteList[$pdt['id']]['qty'] += $pdt['quantity'];
		}
		else
		{
			$pdtQteList[$pdt['id']]['qty']  = $pdt['quantity'];
			$pdtQteList[$pdt['id']]['name'] = $pdt['name'];
			$pdtSumList[$pdt['id']] = array();
		}
		$pdtSumList[$pdt['id']][$pdt['idTC']]['sum'] = $pdt_somme;
		$pdtSumList[$pdt['id']][$pdt['idTC']]['tva'] = $pdt['idTVA'];
		
		if ($pdt['tauxRemise'] != '' && !isset($pdtTauxRemise[$pdt['id']]))
			$pdtTauxRemise[$pdt['id']] = mb_unserialize($pdt['tauxRemise']);
		
		$total_par_Taux[$pdt['idTVA']] += $pdt_somme;
?>
					<tr>
						<td><?php echo to_entities($pdt['id']) ?></td>
						<td><?php echo to_entities($pdt['idTC']) ?></td>
						<td><?php echo to_entities($pdt['name']) . to_entities($pdt['fastdesc'] . $pdt['label']) ?></td>
						<td class="ref-qte"><?php echo $pdt['quantity'] ?></td>
						<td><?php echo $pdt['unite'] ?></td>
						<td class="ref-prix"><?php echo to_entities(sprintf("%0.2f", $pdt['price'])) ?></td>
						<td class="ref-prix"><?php echo sprintf("%0.2f", $pdt_somme) ?></td>
						<td class="tva"><?php echo $listeTVAs[$pdt['idTVA']] ?></td>
					</tr>
<?php
	}
	else
	{
		print(
'					<tr><td colspan="7">Erreur fatale lors du chargement du produit de la ligne n° ' .$i . "</td></tr>\n");
	}
}

	foreach ($pdtTauxRemise as $idProduct => $remises)
	{
		if ($pdtQteList[$idProduct]['qty'] >= $remises[0])
		{
			if ($pdtQteList[$idProduct]['qty'] >= $remises[2])
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
						<td class="ref-remise" colspan="6">Remise de <b><?php echo $remise . '%</b> pour <b>' . $pdtQteList[$idProduct]['qty'] . '</b> x ' . $pdtQteList[$idProduct]['name'] ?></td>
						<td class="ref-remise2"><?php echo to_entities(sprintf("%0.2f", -$sommeRemise)) ?></td>
						<td class="ref-remise3"></td>
					</tr>
<?php
		}
	}
	
	$totalTVA_par_Taux = array();
	$totalTVA = 0;
	foreach ($total_par_Taux as $_id => $_total)
	{
		$totalTVA_par_Taux[$_id] = round($_total * $listeTVAs[$_id]) / 100;
		$totalTVA += $totalTVA_par_Taux[$_id];
	}
	$totalTVA = ceil($totalTVA*100)/100;
	$totalHT  = ceil($totalHT *100)/100;
	$totalTTC = $totalHT + $totalTVA;
	
?>
				</tbody>
			</table>
			<br />
			<div id="montant-totaux">
				<div class="total_H">
					<div class="total_G">Sous-total HT : </div>
					<div class="total_D"><?php echo sprintf("%.02f", $totalHT) ?>€</div>
				</div>
				<div class="total_Hn">
					<div class="total_G">Sous-total TTC : </div>
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
	foreach ($total_par_Taux as $_id => $_total)
	{
		print(
'					<tr><td class="base_euro">' . sprintf("%.02f", $_total) . '</td><td>' . sprintf("%.02f", $listeTVAs[$_id]) . '</td><td class="montant_tva">' . sprintf("%.02f", $totalTVA_par_Taux[$_id]) . "</td></tr>\n");
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
	</div>
	<div class="miseAZero"></div>
</div>
