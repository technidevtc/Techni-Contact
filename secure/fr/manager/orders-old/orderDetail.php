<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 21 février 2011

 Fichier : /secure/fr/manager/orders/orderDetail.php
 Description : Affichage liste des ordres

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = "Gestion des Ordres Fournisseurs";
$navBar = "<a href=\"index.php?SESSION\" class=\"navig\">Gestion des Ordres fournisseurs</a> &raquo; Editer un ordre";
require(ADMIN."head.php");

require(ADMIN."statut.php");

$errorstring = "";

if (!$user->get_permissions()->has("m-comm--sm-partners-orders","red")) {
    print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
    exit();
  }

$orderID = (isset($_GET["idOrdre"]) ? $_GET["idOrdre"] : 0);
//$orderID = isset($_POST["commandID"]) ? $_POST["commandID"] : (isset($_GET["commandID"]) ? $_GET["commandID"] : 0);
if (!preg_match("/^[1-9]{1}[0-9]{0,8}\-[1-9]{1}[0-9]{0,8}$/", $orderID))
	$errorstring .= "- Le numéro d'identifiant de l'ordre est invalide<br />\n";
else {
	$order = new OrderOld($handle, $orderID);
	if (!$order->exists || $order->timestampIMS == 0) {
		$errorstring .= '- L\'ordre ayant pour numéro identifiant ' . $orderID . " n'existe pas<br />\n";
	}
}
try {
  $user_deleted = false;
  $user = new CustomerUser($handle, $order->idClient);
} catch (Exception $e) {
  if (preg_match("/Error while loading the Customer/i",$e->getMessage()))
    $user_deleted = true;
}

$coord = $order->getDeliveryInfos();
$cmd = new Command($handle, $order->idCommande);

// We add the fdp for the commands before 8/10/2008 04:00:00
if ($order->create_time < 1223438400) {
	$order->totalHT += $order->fdpHT;
	$order->totalTTC += $order->fdpTTC;
}

/**
 * file upload
 */
if(isset($_POST['load-arc']) && $_POST['load-arc'] == 1 && isset($_POST['supplier']) && !empty($_FILES['arcFile'])){

  if (!$userChildScript->get_permissions()->has("m-comm--sm-orders","e")) {
    print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
    exit();
  }

  if($order->idOrder == $_GET['idOrdre']){

    if(is_uploaded_file($_FILES['arcFile']['tmp_name'])){
      if($_FILES['arcFile']['type'] == 'application/pdf'){
        $nameFile = $order->idOrder.'_'.$_FILES['arcFile']['name'] ;
          if(@move_uploaded_file ($_FILES['arcFile']['tmp_name'], PDF_ARC.$nameFile)){
//            $cmd = new Command($handle, $order->idCommande);
              $cmd->updateArc($_POST['supplier'], $nameFile);
              unset($order);
              $order = new OrderOld($handle, $orderID);
          }else
            $errorstring .= "- Le fichier uploadé n'a pu être copié correctement<br />\n";
      }else
            $errorstring .= "- Type de fichier incorrect<br />\n";
    }

  }

}

if ($errorstring != "") {
?>
<div class="titreStandard">Ordre n°<?php echo $orderID ?></div>
<br/>
<div class="bg" style="position: relative">
	<h2><?php echo $errorstring ?></h2>
</div>
<?php
  require(ADMIN."tail.php");
	exit();
}
else {
	$typePaiement = getTypePaiement($order->type_paiement);
	$statutPaiement = getStatutPaiement($order->statut_paiement);
        $statutTimestamp = $order->statut_timestamp_order;
	$statutTraitement = $order->getGlobalProcessingStatusText();//getStatutTraitementGlobal($order->statut_traitement);
}
?>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<style type="text/css">
.DB-bg  { display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: #000000; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=40)"; filter: alpha(opacity=40); opacity:.40 }
.DB { display: none; position: absolute; padding: 10px; font: small-caps bold 13px tahoma, arial, sans-serif; color: #000000; text-align: center; border: 1px solid #cccccc; background: #f4faff }
#CQDB { left: 20px; top: 50px; width: 900px; }
#commentMail{width: 700px;}
</style>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __COMMAND_ID__ = <?php echo $order->idAdvertiser ?>+"-"+<?php echo $order->idCommande ?>;
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';

var OrderStatusList = new Array();
OrderStatusList[0] = 'Non encore consultée';
OrderStatusList[1] = 'Attente Accusé Réception';
OrderStatusList[2] = 'AR commande reçu';
</script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script src="../commandes/Command.js" type="text/javascript"></script>
<div class="titreStandard">Ordre n°<?php echo $orderID ?></div>
<div class="bg" style="position: relative">
	<div id="commande" <?php if($order->annulation){echo 'class="orderCancelled"';} ?>>
		<div id="performingRequestMW">Modification en cours...</div>
		<a href="ordersList.php?<?php echo $sid ?>">&lt;&lt; Aller à la liste des ordres fournisseurs</a>
		<div class="zero"></div>
<?php

$coord["titre"] = Command::getTitle($coord["titre"]);
$coord["titre_l"] = Command::getTitle($coord["titre_l"]);
//$coord["email"] = empty($user->email) ? "non renseigné" : $user->email;

// PATCH MDS 11/10/2010 DEB
if (!$user_deleted) {
  if(empty($user->email)) {
    if(empty($user->login))
      $coord["email"] = "non renseigné";
    else
      $coord["email"] = $user->login;
  }
  else {
    $coord["email"] = $user->email;
  }
  // PATCH MDS 11/10/2010 END
  $coord["tel1"] = empty($user->tel1) ? "non renseigné" : $user->tel1;
  $coord["fax1"] = empty($user->fax1) ? "non renseigné" : $user->fax1;
  $coord['societe'] = empty($user->societe) ? "non renseigné" : $user->societe;
}
else {
  $coord["email"] = $coord["tel1"] = $coord["fax1"] = "utilisateur supprimé";
}

//require('CustomerSearchWindow.php');
//require('CoordChangeWindow.php');
//require('ProductExplorerWindow.php');

?>
		<div id="panier">
			<div id="NewClientError"><br /><?php echo $ClientError ?></div>
			<div class="infosL">
				<div id="ModifierClient">
					<div class="infos">
						<div class="intitule" style="width: 70px">N° Client :</div><?php echo $order->idClient ?>
					</div>
					<div class="infos">
						<div class="intitule" style="width: 80px">Fournisseur :</div>
						<div class="valeur" id="company_fixed"><?php echo $order->getAdvertiserName() ?></div>
					</div>
				</div>
			</div>
			<div class="infosC">
                          <div class="intitule" style="width: 70px">Email client:</div><div id="email_fixed" class="valeurL" style="width: 226px"><a href="mailto:<?php echo $coord["email"] ?>?subject=Techni-Contact : Question concernant votre commande n <?php echo $order->idCommande ?>"><?php echo $coord["email"] ?></a></div><div class="zero"></div>
                          <div class="intitule" style="width: 60px">Tel client:</div><div id="tel1_fixed" class="valeurL" style="width: 85px"><a href="dial:<?php echo $coord["tel1"] ?>"><?php echo $coord["tel1"] ?></a></div>
                          <div class="intitule" style="width: 60px">Fax client:</div><div id="fax1_fixed" class="valeurL" style="width: 85px"><?php echo $coord["fax1"] ?></div><div class="zero"></div>
                          <div class="intitule" style="width: 16px"><a href="../commandes/CommandMain.php?&commandID=<?php echo $order->idCommande ?>"><img src="../images/basket_go.png" /></a></div>
                          <div id="email_fixed" class="valeurL" style="width: 226px"><a href="../commandes/CommandMain.php?&commandID=<?php echo $order->idCommande ?>">Voir commande client</a></div>
                          <div class="zero"></div>
			</div>
			<div class="infosR">
				<div class="intitule" style="width: 125px">Date de création :</div><div class="valeurR">le <?php echo date("d/m/Y à H:i:s", $order->create_time) ?></div><div class="zero"></div>
				<div class="intitule" style="width: 125px">Dernière mise à jour :</div><div class="valeurR">le <?php echo date("d/m/Y à H:i:s", $order->timestamp) ?></div><div class="zero"></div>
			</div>
			<div class="zero"></div>
			<div class="intitule" style="width: 110px">Ordre donné par :</div><div id="email_fixed" class="valeurL" style="width: 100px"><?php echo $order->getSenderName() ?></div><div class="zero"></div>
			<div class="infos">Contenu de l'ordre n°<?php echo $orderID ?></div>
			<div class="zero"></div>
                        <img src="../ressources/icons/<?php echo $order->timestampArc != 0 ? 'ok-16x16.png' : 'hexa-no-16x16.png' ?>" alt="<?php echo $order->timestampArc != 0 ? 'Arc disponible' : 'Pas d\'Arc;' ?>" />
                           <?php
                           if($order->timestampArc != 0 ){ ?>
                              <a href="<?php echo PDF_URL_ARC.$order->arc ?>" target="_blank">voir ARC</a> - <a href="javascript: return false;" onClick="loadArc(<?php echo $order->idCommande.','.$order->idAdvertiser ?>);">Modifier ARC</a>
                           <?php }else{ ?>
                              <a href="javascript: return false;" onClick="loadArc(<?php echo $order->idCommande.','.$order->idAdvertiser ?>);">Lier ARC</a>
                           <?php } ?>
                              - <a href="javascript: print_order()">Voir bon de livraison</a> - <a href="javascript: print_page_order()">Voir bon de commande</a>
                        <br />
                        <br />
			<div id="MainCommandTab">
				<table class="liste_produits" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							<th style="width: auto;">Image</th>
							<th style="width: 110px">Réf. fournisseur</th>
							<th style="width: 80px">Réf. TC</th>
							<th style="width: 110px">Fournisseur</th>
							<th style="width: 300px">Désignation</th>
							<th style="width: 60px">Qté.</th>
							<th style="width: 80px">P.U. Euro HT</th>
							<th style="width: 80px">MT Euro HT</th>
							<th style="width: 40px">Tva</th>
						</tr>
					</thead>
					<tbody id="ProductsList">
<?php	$dft_qte_list = array();
        foreach ($order->items as &$item) {

          $resref = $handle->query("SELECT content FROM references_cols WHERE idProduct = '".$handle->escape($item['idProduct'])."'");
          $cols_headers = $handle->fetchAssoc($resref);
          $cccols_headers = mb_unserialize($cols_headers['content']);
          $cccols_headers = array_slice($cccols_headers, 3, -5); // get only custom cols headers

              if(is_string($item["customCols"]))
                $item["customCols"] = mb_unserialize ($item["customCols"]);

          if (!empty($item["customCols"])) {
          $itemDesc = $item["label"];
          foreach($item["customCols"] as $ccol_header => $ccol_content){
            $labelCol = is_numeric($ccol_header) ? $cccols_headers[$ccol_header] : $ccol_header;
            $itemDesc .= " - ".$labelCol.": ".$ccol_content;
          }
        }
        else {
          $itemDesc = $item["name"] . (empty($item["fastdesc"]) ? "" : " - " . $item["fastdesc"]) . (empty($item["label"]) ? "" : " - " . $item["label"]);
        }
				$dft_qte_list[$item["idTC"]] = $item["quantity"];
				$pdt_image = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$item["idProduct"]."-1.jpg") ? SECURE_RESSOURCES_URL."images/produits/thumb_small/".$item["idProduct"]."-1.jpg" : SECURE_RESSOURCES_URL."images/produits/no-pic-thumb_small.gif";
                                $refPdtName = $item["refSupplier"];
				?>
					<tr>
						<td class="center"><a href="<?php echo URL.'produits/'.$item['idFamily'].'-'.$item['idProduct'].'-'.$refPdtName.'.html' ?>" target="_blank"><img src="<?php echo $pdt_image ?>" /></a></td>
						<td class="center"><?php echo $item["refSupplier"] ?></td>
						<td class="center"><a href="<?php echo ADMIN_URL ?>products/edit.php?id=<?php echo $item["idProduct"] ?>"><?php echo $item["idTC"] ?></a></td>
						<td class="center"><a href="<?php echo ADMIN_URL ?>advertisers/edit.php?id=<?php echo $item["idAdvertiser"] ?>"><?php echo $order->getAdvertiserName() ?></a></td>
						<td><?php echo $itemDesc ?></td>
						<td class="ref-qte"><?php echo $item["quantity"] ?></td>
						<td class="ref-prix"><?php echo sprintf("%0.2f", $item["price2"]) ?></td>
						<td class="ref-prix"><?php echo sprintf("%0.2f", $item["price2"]*$item["quantity"]) ?></td>
						<td class="right"><?php echo sprintf("%0.2f", $item["tauxTVA"]) ?></td>
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
<?php			if (!empty($order->promotionCode)) { ?>
							(code : <?php echo $order->promotionCode ?>)
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
						<div class="total_D"><?php echo sprintf("%.02f", $order->stotalOrdreHT) ?>€</div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Frais de Port HT :</div>
						<div class="total_D" id="ShippingFee" title="Double cliquer pour modifier les frais de port"><?php echo sprintf("%.02f€", $order->fdpOrdreHT) ?></div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Total HT :</div>
						<div class="total_D" id="TotalHT"><?php echo sprintf("%.02f", $order->totalOrdreHT) ?>€</div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Total TTC :</div>
						<div class="total_D" id="TotalTTC"><?php echo sprintf("%.02f", $order->totalOrdreTTC) ?>€</div>
						<div class="zero"></div>
					</div>
					<div id="ShippingFeeError"></div>
				</div>
				<div id="mod-montant-totaux">
					<div></div>
					<div><a id="ShippingFeeMod" href="javascript: editShipppingFee()" title="Cliquer ici pour modifier les frais de port">Modifier</a></div>
					<div></div>
					<div></div>
				</div>
                                <div id="calageTva">&nbsp;</div>
                                <div class="zero"></div>
<!--				<table id="tva" cellspacing="0" cellpadding="0">
					<thead>
					<tr>
						<th style="width: 150px">Base € HT</th>
						<th style="width: 60px">Taux</th>
						<th style="width: 139px">Montant TVA</th>
					</tr>
					</thead>
					<tbody>-->
<?php	//foreach ($order->tvaTable as $vatRate => $amount) {
	//			if (!empty($amount["total"])) { ?>
<!--					<tr>
						<td class="base_euro"><?php //echo sprintf("%.02f", $amount["total"]) ?></td>
						<td><?php // echo sprintf("%.02f", $vatRate) ?></td>
						<td class="montant_tva"><?php //echo sprintf("%.02f", $amount["tva"]) ?></td>
					</tr>-->
<?php	//	}
	//		} ?>
<!--					</tbody>
					<tfoot>
					<tr>
						<td class="total"><div><?php echo sprintf("%.02f", $order->stotalHT) ?></div>Total</td><td class="tvas"></td><td class="total-tva"><?php echo sprintf("%.02f", $order->totalTVA) ?></td>
					</tr>
					</tfoot>
				</table>-->
			</div>
		</div>
                <br/>
                <div id="conversation"></div>
		<br />
                <div id="statusDetailLeft">
                  <div id="statusListOrderDetail">
                          <div class="infos">
                            <input type="hidden" id="OrderStatusValue" value="<?php echo $order->statut_traitement_order?>" />
                                  <div class="intitule">Statut de traitement : </div>
                                  <div class="valeurR"><a id="OrderStatusMod" href="javascript: editOrderStatus()">Modifier</a></div>
                                  <div class="valeur" id="OrderStatusValueShow"><?php echo $statutTraitement ?></div>
                                  <div class="zero"></div>
                          </div>
                      <div class="valeur" id="OrderStatusTimestamp"><?php if($statutTimestamp)echo 'MAJ : '.date('d/m/Y H:i', $statutTimestamp)  ?></div>
                          <div class="infos">
                            <?php if(!$order->annulation){ ?>
                            <input type="button" class="fValidUn" name="cancelOrder" id="cancelOrder" value="Annuler ordre fournisseur" onClick="removeOrder();return false;" />
                            <?php } ?>
                          </div>

                  </div>
                  <br />
                  <div id="plannedDeliveryDateDetail">
                          <div class="infosDate">
                                  <div class="intitule">Date d'expédition prévisionnelle : </div>
  
                                  <div class="valeur" id="ProcessingDeliveryDate"><?php echo $cmd->planned_delivery_date ? $cmd->planned_delivery_date : 'Non encore fixée' ?></div>
                                  <div id="ProcessingStatus"><input type="hidden" id="ProcessingStatusValue" value="25" /></div>
                          </div>
                          <div class="infosDate" id="toggleDateDetailForm">
                            <input type="button" class="fValidUn" name="editPlannedDeliveryDate" id="editPlannedDeliveryDate" value="modifier" onClick="javascript: setDeliveryDateForm();" />
                          </div>
                          <div class="infosDate">
                            <div id="sendEmailMsg" class="valeurR"></div>
                            <input type="checkbox" name="sendEmailCB" id="sendEmailCB" value="sendEmailCB" /> Envoyer un email au client
                          </div>
                  </div>
                </div>
		<div id="bloc-IMOrderDetail">
                  <div class="bloc-IM-titre">Messagerie :</div>
                  <div class="bloc-IM-content">
                    Envoyer un message au fournisseur:<br />
                    <br />
                    <textarea name="contenu_message" cols="65" rows="6"></textarea><br />
                    <div class="bloc-preview">
                      <a href="#" onClick="sendMessage();return false;">Envoi du message</a>
                      <?php if(($order->attente_info == __MSGR_CTXT_SUPPLIER_TC_ORDER__) || ($order->attente_info == __MSGR_CTXT_ORDER_CMD__)): ?>
                      <a href="#" style="float:right; color: red" onClick="endConversation();return false;">Clore conversation</a>
                      <?php endif ?>
                    </div>
                  </div>
                </div>
                <div class="zero"></div>
		<br />
		<div class="livraison">
			<div class="titreBloc">Adresse de livraison</div>
			<div class="coord" id="ShipAddress">
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
			<div class="coord_infos_sup"><span id="infos_sup_l_fixed"><?php echo to_entities($coord['infos_sup_l']) ?></span><br /></div>
		</div>
		<div class="facturation">
			<div class="titreBloc">Adresse de facturation</div>
			<div class="coord" id="BillingAddress">
				<b><span id="titre_fixed"><?php echo $titre ?></span> <span id="nom_fixed"><?php echo $coord['nom'] ?></span> <span id="prenom_fixed"><?php echo $coord['prenom'] ?></span></b><br />
				<span id="societe_fixed"><?php echo $coord['societe'] ?></span><span id="societe_br"><?php echo $coord['societe'] != '' ? '<br />' : '' ?></span>
				<span id="adresse_fixed"><?php echo $coord['adresse'] ?></span><br />
				<span id="complement_fixed"><?php echo $coord['complement'] ?></span> <span id="cp_fixed"><?php echo $coord['cp'] ?></span> <span id="ville_fixed"><?php echo $coord['ville'] ?></span><br />
				<span id="pays_fixed"><?php echo $coord['pays'] ?></span><br />
				<span id="tel1_fixed">tel : <?php echo $coord['tel1'] ?></span><br />
			</div>
			<br/>
			<div class="titreBloc">Instructions de livraison</div>
			<div class="coord_infos_sup"><span id="infos_sup_fixed"><?php echo to_entities($coord['infos_sup']) ?></span><br /></div>
		</div>
<!--                notes internes-->
                <br/><br/>
                <div id="notes_internes"></div>
                <br/>
                <div id="bloc-IMOrderDetail">
                  <div class="bloc-IM-titre">Note interne :</div>
                  <div class="bloc-IM-content">
                    Laisser une note :<br />
                    <br />
                    <textarea name="contenu_note_interne" cols="65" rows="6"></textarea><br />
                    <div class="bloc-preview">
                      <a href="#" onClick="sendNoteInterne();return false;">Poster la note</a>
                    </div>
                  </div>
                </div>
<!--                notes internes-->
	</div>
	<div class="zero"></div>
        <style type="text/css">
        .DB-bg  { display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: #000000; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=40)"; filter: alpha(opacity=40); opacity:.40 }
        .DB { display: none; position: absolute; padding: 120px 10px 10px 10px; font: small-caps bold 13px tahoma, arial, sans-serif; color: #000000; text-align: center; border: 1px solid #cccccc; background: #f4faff }
        #EmlConfDB { left: 100px; top: 450px; width: 600px; height: 200px}
        #EmlCloseDB { left: 100px; top: 450px; width: 600px; height: 200px}
        #EmlNIDB { left: 100px; top: 850px; width: 600px; height: 200px}
        #CancelOrderDB { left: 100px; top: 450px; width: 600px; height: 250px}
        #commentMail{width: 700px;}
        </style>
        <div class="DB-bg"></div>
        <div id="CQDB" class="DB"></div>
        <div id="CancelOrderDB" class="DB">
            <form name="cancelOrder" method="post" action="">
                <input type="hidden" name="remove" value="1"/>
                <input type="hidden" name="ordre" value="<?php echo $idOrdre ?>"/>
                Vous &ecirc;tes sur le point d'annuler l'ordre fournisseur N&deg; <?php echo $orderID ?>.<br/>
                <br/>
                Merci pr&eacute;ciser la raison de cette annulation <br/>
                <br/>
                <textarea name="motif_annulation" cols="60" rows="6"></textarea><br/>
                <br/>
                <input type="button" value="Annuler"/> &nbsp; &nbsp; <input type="button" value="Confirmer l'annulation"/>
            </form>
        </div>
        <div id="EmlConfDB" class="DB">
          Votre message a bien été transmis au fournisseur. Votre conversation est maintenant archivée sur cette page.<br />
          <br />
          <br />
          <input type="button" value="Ok"/>
        </div>
        <div id="EmlCloseDB" class="DB">
          Cette conversation est maintenant close.<br />
          <br />
          <br />
          <input type="button" value="Ok" onClick="document.location.href = window.location"/>
        </div>
        <div id="EmlNIDB" class="DB">
          Note interne postée.<br />
          <br />
          <br />
          <input type="button" value="Ok" onClick="document.location.href = window.location"/>
        </div>
        <div id="LoadArcDB" class="DB">
        </div>
</div>
<script type="text/javascript">

  function setDeliveryDateForm(){
    $('#editPlannedDeliveryDate').remove();
//    $('#editPlannedDeliveryDate').onClick('javascript: saveDeliveryDate();');
    $("#toggleDateDetailForm").append("<input type=\"text\" size=\"50\" value=\"\" maxlength=\"255\" /> <input type=\"button\" onClick=\"saveDeliveryDate()\" value=\"Sauvegarder\" \>");
  }

  function saveDeliveryDate(){
    var PlannedDeliveryDate = $("#toggleDateDetailForm input").val();
    AlterCommand("&alterProcessingStatus=25"+(PlannedDeliveryDate?"&plannedDeliveryDate="+PlannedDeliveryDate:"")+"&sendEmail="+$("#sendEmailCB").attr("checked"));
    $('#toggleDateDetailForm input').remove();
    $('#toggleDateDetailForm').append('<input type="button" class="fValidUn" name="editPlannedDeliveryDate" id="editPlannedDeliveryDate" value="modifier" onClick="javascript: setDeliveryDateForm();" />');
  }

  function print_delivery_bill()
  {
          window.open('order_delivery_bill_print.php?idOrder=<?php echo $order->idOrder ?>', 'TC_order_print', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, height=670, width=1040');
  }

  var AJAXHandle = {
      dataType: "json",
      error: function(XMLHttpRequest, textStatus, errorThrown){
        if(AJAXHandle.url == "AJAX_conversation.php"){
          AJAX_conversation_error(XMLHttpRequest, textStatus, errorThrown);
        };

      },
      success: function(data, textStatus) {
        if(AJAXHandle.url == "AJAX_conversation.php"){
          AJAX_conversation_success(data, textStatus);
        };
      }
  };

  var AJAXHandleCO = {
      dataType: "json",
      error: function(XMLHttpRequest, textStatus, errorThrown){
        if(AJAXHandleCO.url == "AJAX_cancelOrder.php"){
          AJAX_cancelOrder_error(XMLHttpRequest, textStatus, errorThrown)
        };
      },
      success: function(data, textStatus) {
        if(AJAXHandleCO.url == "AJAX_cancelOrder.php"){
          AJAX_cancelOrder_success(data, textStatus);
        };
      }
  };

  function AJAX_conversation_error(XMLHttpRequest, textStatus, errorThrown) {
      var divConversation = $("#conversation");
                  divConversation.empty();
                  divConversation.append( '<div class="bloc-titre2">'+textStatus+'</div>');
  }
  
  function AJAX_conversation_success (data, textStatus) {

    var divConversation = $("#conversation");
    divConversation.empty();
    
    if(data.conversations){

      if(data.conversations != 'vide'){
        var html = "<div class=\"bloc\">"+
                    "<div class=\"bloc-titre2\">Conversation liées à cet ordre</div>"+
                    "<div id=\"messenger-conversation\" class=\"conversation\">";

        for (i = 0; i < data.conversations.length; i++){
          html = html+"<h2>Message de "+data.conversations[i]['sender_name']+" envoyé le "+data.conversations[i]['date']+"</h2>"+
                  "<div class=\"zero\"></div>"+
                    "<ul class=\"list grey\">"+
                        "<li class=\"conversation first\"><pre style=\"white-space : pre-line\">"+
                        data.conversations[i]['text']+
                        "</pre></li></ul>";
        };

        html = html+"</div></div>";

        divConversation.append(html);
      }
    }else if(data.error){
      var textarea = $('textarea[name=contenu_message]');
      textarea[0].value = '';
      $("div.DB-bg").show();
      $("#EmlConfDB").append('<br /><br />La messagerie interne a bien fonctionné, mais une erreur est survenue à l\'envoi de l\'e-mail au fournisseur :'+data.error);
      $("#EmlConfDB").show()
    }else if(data.result == 'Conversation close'){
      var textarea = $('textarea[name=contenu_message]');
      textarea[0].value = '';
      $("div.DB-bg").show();
      $("#EmlCloseDB").show()
    }else if(data.result){
      var textarea = $('textarea[name=contenu_message]');
      textarea[0].value = '';
      $("div.DB-bg").show();
      $("#EmlConfDB").show()
    }
  }


  function getConversation(){

     var divConversation = $("#conversation");
                  divConversation.empty();
                  divConversation.append( '<div class="bloc-titre2"></div>');

      AJAXHandle.data = "idUser="+<?php echo $order->idAdvertiser ?>+"&action=get&ordre="+<?php echo $order->idCommande ?>;
      AJAXHandle.type = "GET";
      AJAXHandle.url = "AJAX_conversation.php";
      $.ajax(AJAXHandle);

   return false;
  }

  function sendMessage(){

      var contenuMessage = $('textarea[name=contenu_message]').val();
      AJAXHandle.data = "idUser="+<?php echo $order->idAdvertiser ?>+"&contenu="+encodeURIComponent(contenuMessage)+"&action=add&ordre="+<?php echo $order->idCommande ?>;
      AJAXHandle.type = "POST";

      AJAXHandle.url = "AJAX_conversation.php";
      $.ajax(AJAXHandle);

      getConversation();

  }

    function endConversation(){

      var contenuMessage = $('textarea[name=contenu_message]').val();
      AJAXHandle.data = "idUser="+<?php echo $order->idAdvertiser ?>+"&contenu="+encodeURIComponent(contenuMessage)+"&action=end&ordre="+<?php echo $order->idCommande ?>;
      AJAXHandle.type = "POST";

      AJAXHandle.url = "AJAX_conversation.php";
      $.ajax(AJAXHandle);

      getConversation();

  }

    var AJAXHandleInternalNotes = {
      dataType: "json",
      error: function(XMLHttpRequest, textStatus, errorThrown){

        if(AJAXHandleInternalNotes.url == "AJAX_internalNotes.php"){
          AJAX_InternalNotes_error(XMLHttpRequest, textStatus, errorThrown)
        };
      },
      success: function(data, textStatus) {
        if(AJAXHandleInternalNotes.url == "AJAX_internalNotes.php"){
          AJAX_InternalNotes_success(data, textStatus);
        };


      }
  };

  function AJAX_InternalNotes_error(XMLHttpRequest, textStatus, errorThrown) {
      var divInternalNotes = $("#notes_internes");
                  divInternalNotes.empty();
                  divInternalNotes.append( '<div class="bloc-titre2" style="color : red">Erreur : '+textStatus+'</div>');
  }

  function AJAX_InternalNotes_success (data, textStatus) {
    var divInternalNotes = $("#notes_internes");
    divInternalNotes.empty();

    if(data.notes){
      if(data.notes != 'vide'){
        var html = "<div class=\"bloc\">"+
                    "<div class=\"bloc-titre2\">Notes internes liées à cette ordre</div>"+
                    "<div id=\"affiche_notes_internes\" class=\"conversation\">";

        for (i = 0; i < data.notes.length; i++){
          html = html+"<h2>Message de "+data.notes[i]['sender_name']+" envoyé le "+data.notes[i]['date']+"</h2>"+
                  "<div class=\"zero\"></div>"+
                    "<ul class=\"list grey\">"+
                        "<li class=\"conversation first\"><pre style=\"white-space : pre-line\">"+
                        data.notes[i]['content']+
                        "</pre></li></ul>";
        };

        html = html+"</div></div>";

        divInternalNotes.append(html);
      }

    }else if(data.error){
      divInternalNotes.append( '<div class="bloc-titre2" style="color : red">Erreur : '+data.error+'</div>');
    }else if(data.result){
      divInternalNotes.append(data.result);
      var textarea = $('textarea[name=contenu_note_interne]');
      textarea[0].value = '';
      $("div.DB-bg").show();
      $("#EmlNIDB").show()
    }
  }

    function getNotesInternes(){

     var divInternalNotes = $("#notes_internes");
                  divInternalNotes.empty();
                  divInternalNotes.append( '<div class="bloc-titre2"></div>');

      AJAXHandleInternalNotes.data = "idUser="+<?php echo $order->idAdvertiser ?>+"&action=get&ordre="+<?php echo $order->idCommande ?>;
      AJAXHandleInternalNotes.type = "GET";
      AJAXHandleInternalNotes.url = "AJAX_internalNotes.php";
      $.ajax(AJAXHandleInternalNotes);

  }

  function sendNoteInterne(){

      var contenuNoteInterne = $('textarea[name=contenu_note_interne]').val();
      AJAXHandleInternalNotes.data = "idUser="+<?php echo $order->idAdvertiser ?>+"&contenu="+encodeURIComponent(contenuNoteInterne)+"&action=add&ordre="+<?php echo $order->idCommande ?>;
      AJAXHandleInternalNotes.type = "POST";

      AJAXHandleInternalNotes.url = "AJAX_internalNotes.php";
      $.ajax(AJAXHandleInternalNotes);

      getNotesInternes();

  }

  getConversation();
    getNotesInternes();

  $("#EmlConfDB input[type='button']:first").click(function(){
    $("div.DB-bg").hide();
    $("#EmlConfDB").hide();
  });

  function AJAX_cancelOrder_error(XMLHttpRequest, textStatus, errorThrown) {
      $('#ProcessingStatus').text('Erreur XHR') ;
  }

  function AJAX_cancelOrder_success (data, textStatus) {
    if(data.error)
      alert(data.error)
    else{
      $('#commande').attr('class','orderCancelled');
      $('#ProcessingStatus').text('Commande annulée') ;
      $("#cancelOrder").hide();
    }
  }

  function removeOrder(){
      $("div.DB-bg").show();
      $("#CancelOrderDB").css({'top': ($('#cancelOrder').offset().top-200)+'px'});
      $("#CancelOrderDB").show();
  }

$("#CancelOrderDB input[type='button']:first").click(function(){
  $("div.DB-bg").hide();
  $("#CancelOrderDB").hide();
});
$("#CancelOrderDB input[type='button']:last").click(function(){
//  $("form[name='cancelOrder']").submit();
    var motif = $('textarea[name=motif_annulation]').val();
      AJAXHandleCO.data = "idAdvertiser="+<?php echo $order->idAdvertiser ?>+"&action=remove&idOrdre="+<?php echo $order->idCommande ?>+"&motif="+motif;
      AJAXHandleCO.type = "POST";

      AJAXHandleCO.url = "AJAX_cancelOrder.php";
      $.ajax(AJAXHandleCO);

      $("div.DB-bg").hide();
      $("#CancelOrderDB").hide();
      getConversation();
});

  function print_order()
  {
          window.open('order_delivery_bill_print.php?idOrder=<?php echo $order->idOrder ?>', 'TC_order_print', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, height=670, width=1040');
  }
  function print_page_order()
{
	window.open('order_page_print.php?idOrder=<?php echo $order->idOrder ?>', 'TC_order_print', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, height=670, width=1040');
}
</script>
<br />
<?php require(ADMIN."tail.php") ?>
