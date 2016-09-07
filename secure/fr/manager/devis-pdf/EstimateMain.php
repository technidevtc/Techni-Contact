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
require(ICLASS . "CCustomerUser.php");
require(ICLASS . "CCart.php");
require(ADMIN  . "statut.php");

$title = 'Gestion des Devis Clients';
$navBar = '<a href="index.php?SESSION" class="navig">Gestion des Devis Clients</a> &raquo; Editer un devis';

require(ADMIN . 'head.php');

$errorstring = '';

$cartID = isset($_POST["estimateID"]) ? $_POST["estimateID"] : (isset($_GET["estimateID"]) ? $_GET["estimateID"] : 0);
if (!preg_match("/^[0-9a-v]{26,32}$/", $cartID))
	$errorstring .= "- Le numéro d'identifiant de devis est invalide<br />\n";
else {
	$cart = new Cart($handle, $cartID);
	if (!$cart->existsInDB) {
		$errorstring .= '- Le devis ayant pour numéro identifiant ' . $cartID . " n'existe pas<br />\n";
	}
}
if ($errorstring != "") {
?>
<div class="titreStandard">Devis n°<?php echo $cartID ?></div>
<br />
<div class="bg" style="position: relative">
	<h2><?php echo $errorstring ?></h2>
</div>
<?php
	exit();
}
$cart->calculateCart();

$user = new CustomerUser($handle, $cart->idClient);
$coord = $user->getCoordFromArray();
?>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __ESTIMATE_ID__ = '<?php echo $cartID ?>';
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';
</script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script src="Estimate.js" type="text/javascript"></script>
<div class="titreStandard">Devis n°<?php echo $cart->estimate ?> | <a href="EstimateDelete.php?<?php echo $sid ?>&estimateID=<?php echo $cartID ?>" onClick="return confirm('Etes-vous sûr de vouloir supprimer ce devis ?')">Supprimer le devis</a></div>
<div class="bg" style="position: relative">
	<div id="commande">
		<div id="performingRequestMW">Modification en cours...</div>
		<a href="index.php?<?php echo $sid ?>">&lt;&lt; Aller à la liste des devis client</a>
		<div class="zero"></div>
<?php

$coord["titre"] = CustomerUser::getTitle($coord["titre"]);
$coord["titre_l"] = CustomerUser::getTitle($coord["titre_l"]);

$coord["email"] = empty($user->email) ? "non renseigné" : $user->email;
$coord["tel1"] = empty($user->tel1) ? "non renseigné" : $user->tel1;
$coord["fax1"] = empty($user->fax1) ? "non renseigné" : $user->fax1;

require('CustomerSearchWindow.php');
require('ProductExplorerWindow.php');

?>
		<div id="panier">
			<div id="NewClientError"><br /><?php echo $ClientError ?></div>
			<div class="infosL">
				<div id="ModifierClient">
					<div class="infos">
						<div class="intitule" style="width: 70px">N° Client :</div>
						<input type="text" class="clientID" id="clientID_fixed" maxlength="10" value="<?php echo $cart->idClient ?>" readonly="readonly" />
						<input type="text" class="clientIDn" id="clientID_edit" maxlength="10" value="<?php echo $cart->idClient ?>" />
						<input type="button" class="bouton" class="fValidUn" style="width: 65px; margin: -5px 0 0 10px;" id="button_change" value="Changer" onclick="showNewClientOptions()" />
						<input type="button" class="bouton" class="fValidUn" style="width: 65px; margin: -5px 0 0 10px; display: none" id="button_search" value="Chercher" onclick="showClientSearchWindow()" />
						<input type="button" class="bouton" class="fValidUn" style="width: 75px; margin: -5px 0 0 10px; display: none" id="button_save" value="Enregistrer" onclick="saveNewClient()" />
						<input type="button" class="bouton" class="fValidUn" style="width: 58px; margin: -5px 0 0 10px; display: none" id="button_cancel" value="Annuler" onclick="cancelNewClient()" />
					</div>
					<div class="infos">
						<div class="intitule" style="width: 70px">Société :</div>
						<div class="valeur" id="company_fixed"><?php echo $coord["societe"] ?></div>
					</div>
				</div>
			</div>
			<div class="infosC">
				<div class="intitule" style="width: 40px">Email :</div><div id="email_fixed" class="valeurL" style="width: 256px"><?php echo $coord["email"] ?></div><div class="zero"></div>
				<div class="intitule" style="width: 40px">Tel :</div><div id="tel1_fixed" class="valeurL" style="width: 105px"><a href="dial:<?php echo preg_replace('/[^0-9\+.]?/', '', $coord["tel1"]) ?>"><?php echo preg_replace('/[^0-9\+.]?/', '', $coord["tel1"]) ?> <img src="../ressources/icons/telephone.png" alt="Tel" style="vertical-align:middle" /></a></div>
				<div class="intitule" style="width: 40px">Fax :</div><div id="fax1_fixed" class="valeurL" style="width: 105px"><?php echo $coord["fax1"] ?></div><div class="zero"></div>
			</div>
			<div class="infosR">
				<div class="intitule" style="width: 125px">Date de création :</div><div class="valeurR">le <?php echo date("d/m/Y à H:i.s", $cart->create_time) ?></div><div class="zero"></div>
				<div class="intitule" style="width: 125px">Dernière mise à jour :</div><div class="valeurR">le <?php echo date("d/m/Y à H:i.s", $cart->timestamp) ?></div><div class="zero"></div>
			</div>
			<div class="zero"></div>
			<div class="infos">Contenu du devis n°<?php echo $cart->estimate ?></div>
			<div class="zero"></div>
			<div id="MainCommandTab">
				<table class="liste_produits" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							<th style="width: 80px">Réf. Produit</th>
							<th style="width: 80px">Réf. TC</th>
							<th style="width: 300px">Désignation</th>
							<th style="width: 60px">Qté.</th>
							<th style="width: 40px">Unité</th>
							<th style="width: 80px">P.U. Euro HT</th>
							<th style="width: 80px">MT Euro HT</th>
							<th style="width: 40px">Tva</th>
							<th style="width: 40px">Sup.</th>
						</tr>
					</thead>
					<tbody id="ProductsList">
<?php	$dft_qte_list = array();
			foreach ($cart->items as &$item) {
				$dft_qte_list[$item["idTC"]] = $item["quantity"];
        if (!empty($item["customCols"])) {
          $itemDesc = $item["label"];
          foreach($item["customCols"] as $ccol_header => $ccol_content)
            $itemDesc .= " - ".$ccol_header.": ".$ccol_content;
        }
        else {
          $itemDesc = $item["name"].(empty($item["fastdesc"]) ? "" : " - ".$item["fastdesc"]).(empty($item["label"]) ? "" : " - ".$item["label"]); 
        } ?>
					<tr>
						<td class="center"><input type="hidden" value="<?php echo $item["idProduct"] . "-" . $item["idTC"] ?>"><?php echo $item["idProduct"] ?></td>
						<td class="center"><?php echo $item["idTC"] ?></td>
						<td><?php echo $itemDesc ?></td>
						<td class="ref-qte">
							<img src="../ressources/quantite_plus.gif"  width="14" height="10" border="0" alt="Ajouter" onclick="set_qte(<?php echo $item["idTC"] ?>,'+1')" />
							<img src="../ressources/quantite_moins.gif" width="14" height="10" border="0" alt="Retirer" onclick="set_qte(<?php echo $item["idTC"] ?>,'-1')" />
							<input type="text" id="qte<?php echo $item["idTC"] ?>" onblur="set_qte(<?php echo $item["idTC"] ?>, this.value)" value="<?php echo $item["quantity"] ?>" />
						</td>
						<td class="right"><?php echo $item["unite"] ?></td>
						<td class="ref-prix"><?php echo sprintf("%0.2f", $item["price"]) ?></td>
						<td class="ref-prix"><?php echo sprintf("%0.2f", $item["sum_base"]) ?></td>
						<td class="right"><?php echo sprintf("%0.2f", $item["tauxTVA"]) ?></td>
						<td class="suppr"><img title="Supprimer ce produit" src="../ressources/b_drop.png" onclick="DelProduct(<?php echo $item["idTC"] ?>)" alt="Supprimer" /></td>
					</tr>
<?php		if (!empty($item["comment"])) { ?>
					<tr>
						<td></td>
						<td></td>
						<td colspan="5" class="product-free-field"><div class="pff-label">Commentaires :</div><div class="pff-content"><?php echo str_replace('"', "&quot;", $item['comment']) ?></div><div class="zero"></div></td>
						<td></td>
						<td class="suppr"></td>
					</tr>
<?php		}
				if (!empty($item["promotion"])) { ?>
					<tr>
						<td></td>
						<td></td>
						<td class="ref-promotion ref-desc-l " colspan="4">
							Promotion de <b><?php echo sprintf("%.02f", $item["promotionpc"]) ?>%</b> pour <b><?php echo $item["quantity"] ?></b> x <?php echo $item["name"] ?>
<?php			if (!empty($cart->promotionCode)) { ?>
							(code : <?php echo $cart->promotionCode ?>)
<?php 		} ?>
						</td>
						<td class="ref-dde2"><?php echo to_entities(sprintf("%0.2f", -$item["sum_promotion"])) ?></td>
						<td></td>
						<td class="suppr"></td>
					</tr>
<?php		}
				if (!empty($item["discount"])) { ?>
					<tr>
						<td></td>
						<td></td>
						<td class="ref-discount ref-desc-l " colspan="4">Remise de <b><?php echo sprintf("%.02f", $item["discountpc"]) ?>%</b> pour <b><?php echo $item["quantity"] ?></b> x <?php echo $item["name"] ?></td>
						<td class="ref-dde2"><?php echo to_entities(sprintf("%0.2f", -$item["sum_discount"])) ?></td>
						<td></td>
						<td class="suppr"></td>
					</tr>
<?php		}
			} ?>
					</tbody>
				</table>
<script type="text/javascript">
var dft_qte_list = <?php echo json_encode($dft_qte_list) ?>;
</script>
				<br />
				<div id="ProductsError"></div>
				<div id="montant-totaux">
					<div class="total_H">
						<div class="total_G">Sous-total HT :</div>
						<div class="total_D"><?php echo sprintf("%.02f", $cart->stotalHT) ?>€</div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Frais de Port HT :</div>
						<div class="total_D"><?php echo sprintf("%.02f€", $cart->fdpHT) ?></div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Total HT :</div>
						<div class="total_D" id="TotalHT"><?php echo sprintf("%.02f", $cart->totalHT) ?>€</div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Total TTC :</div>
						<div class="total_D" id="TotalTTC"><?php echo sprintf("%.02f", $cart->totalTTC) ?>€</div>
						<div class="zero"></div>
					</div>
					<div id="ShippingFeeError"></div>
				</div>
				<div class="buttons">
					<input type="button" class="bouton" value="Ajouter un produit" class="fValidUn" style="width: 120px" onclick="showProductExplorerWindow()" /><br />
					<input type="button" class="bouton" value="Recalculer" class="fValidUn" style="width: 120px" onclick="updateProductsQuantity()" />
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
<?php	foreach ($cart->tvaTable as $vatRate => $amount) {
				if (!empty($amount["total"])) { ?>
					<tr>
						<td class="base_euro"><?php echo sprintf("%.02f", $amount["total"]) ?></td>
						<td><?php echo sprintf("%.02f", $vatRate) ?></td>
						<td class="montant_tva"><?php echo sprintf("%.02f", $amount["tva"]) ?></td>
					</tr>
<?php		}
			} ?>
					</tbody>
					<tfoot>
					<tr>
						<td class="total"><div><?php echo sprintf("%.02f", $cart->stotalHT) ?></div>Total</td><td class="tvas"></td><td class="total-tva"><?php echo sprintf("%.02f", $cart->totalTVA) ?></td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="zero"></div>
		<div class="livraison" ondblclick="showCoordChangeWindow()" title="Double cliquer pour modifier">
			<div class="titreBloc">Adresse de livraison</div>
			<div class="coord" id="ShipAddress">
				<input type="hidden" value="<?php echo $coord['coord_livraison'] ?>" id="coord_livraison" />
<?php	if ($coord['coord_livraison'] == 1) { ?>
				<b><span id="titre_l_fixed"><?php echo $titre_l ?></span> <span id="nom_l_fixed"><?php echo $coord['nom_l'] ?></span> <span id="prenom_l_fixed"><?php echo $coord['prenom_l'] ?></span></b><br />
				<span id="societe_l_fixed"><?php echo $coord['societe_l'] ?></span><span id="societe_l_br"><?php echo $coord['societe_l'] != '' ? '<br />' : '' ?></span>
				<span id="adresse_l_fixed"><?php echo $coord['adresse_l'] ?></span><br />
				<span id="complement_l_fixed"><?php echo $coord['complement_l'] ?></span> <span id="cp_l_fixed"><?php echo $coord['cp_l'] ?></span> <span id="ville_l_fixed"><?php echo $coord['ville_l'] ?></span><br />
				<span id="pays_l_fixed"><?php echo $coord['pays_l'] ?></span><br />
<?php	} else { ?>
				<b><span id="titre_l_fixed"><?php echo $titre ?></span> <span id="nom_l_fixed"><?php echo $coord['nom'] ?></span> <span id="prenom_l_fixed"><?php echo $coord['prenom'] ?></span></b><br />
				<span id="societe_l_fixed"><?php echo $coord['societe'] ?></span><span id="societe_l_br"><?php echo $coord['societe'] != '' ? '<br />' : '' ?></span>
				<span id="adresse_l_fixed"><?php echo $coord['adresse'] ?></span><br />
				<span id="complement_l_fixed"><?php echo $coord['complement'] ?></span> <span id="cp_l_fixed"><?php echo $coord['cp'] ?></span> <span id="ville_l_fixed"><?php echo $coord['ville'] ?></span><br />
				<span id="pays_l_fixed"><?php echo $coord['pays'] ?></span><br />
<?php	} ?>
			</div>
			<br/>
			<div class="titreBloc">Instructions de livraison</div>
			<div class="coord_infos_sup"><span id="infos_sup_l_fixed"><?php echo $coord['infos_sup_l'] ?></span><br /></div>
		</div>
		<div class="facturation">
			<div class="titreBloc">Adresse de facturation</div>
			<div class="coord" id="BillingAddress">
				<b><span id="titre_fixed"><?php echo $titre ?></span> <span id="nom_fixed"><?php echo $coord['nom'] ?></span> <span id="prenom_fixed"><?php echo $coord['prenom'] ?></span></b><br />
				<span id="societe_fixed"><?php echo $coord['societe'] ?></span><span id="societe_br"><?php echo $coord['societe'] != '' ? '<br />' : '' ?></span>
				<span id="adresse_fixed"><?php echo $coord['adresse'] ?></span><br />
				<span id="complement_fixed"><?php echo $coord['complement'] ?></span> <span id="cp_fixed"><?php echo $coord['cp'] ?></span> <span id="ville_fixed"><?php echo $coord['ville'] ?></span><br />
				<span id="pays_fixed"><?php echo $coord['pays'] ?></span><br />
			</div>
			<br/>
			<div class="titreBloc">Instructions de livraison</div>
			<div class="coord_infos_sup"><span id="infos_sup_fixed"><?php echo $coord['infos_sup'] ?></span><br /></div>
		</div>
	</div>
	<div class="zero"></div>
</div>
<br />