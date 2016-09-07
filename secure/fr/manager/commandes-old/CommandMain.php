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

$title = "Gestion des Commandes Clients";
$navBar = "<a href=\"index.php?SESSION\" class=\"navig\">Gestion des Commandes Clients</a> &raquo; Editer une commande";
require(ADMIN."head.php");

require(ADMIN."statut.php");

$errorstring = "";

$orderID = isset($_POST["commandID"]) ? $_POST["commandID"] : (isset($_GET["commandID"]) ? $_GET["commandID"] : 0);
if (!preg_match("/^[1-9]{1}[0-9]{0,8}$/", $orderID))
	$errorstring .= "- Le numéro d'identifiant de commande est invalide<br />\n";
else {
	$cmd = new Command($handle, $orderID);
	if ($cmd->statut < 10) {
		$errorstring .= '- La commande ayant pour numéro identifiant ' . $orderID . " n'existe pas<br />\n";
	}
}
try {
  $user_deleted = false;
  $user = new CustomerUser($handle, $cmd->idClient);
} catch (Exception $e) {
  if (preg_match("/Error while loading the Customer/i",$e->getMessage()))
    $user_deleted = true;
}
if($cmd)
  $coord = $cmd->getCoordFromArray();

// We add the fdp for the commands before 8/10/2008 04:00:00
if ($cmd->create_time < 1223438400) {
	$cmd->totalHT += $cmd->fdpHT;
	$cmd->totalTTC += $cmd->fdpTTC;
}

/**
 * email sending
 */
if(isset($_POST['confirm-resendCustomerMail']) && $_POST['confirm-resendCustomerMail'] == 1 && isset($_POST['customer'])){

  // Writing the email
		$cmd_content .=	"
			<table cellspacing=\"1\" cellpadding=\"2\" border=\"1\">
				<thead>
				<tr>
					<th>Réf</th>
					<th>Libellé</th>
					<th>Montant ht unitaire</th>
					<th>Quantité</th>
					<th>Montant total ht</th>
				</tr>
				</thead>
				<tbody>";

		foreach ($cmd->items as &$item) {

                  $resref = $handle->query("SELECT content FROM references_cols WHERE idProduct = '".$handle->escape($item['idProduct'])."'");
              $cols_headers = $handle->fetchAssoc($resref);
              $cccols_headers = mb_unserialize($cols_headers['content']);
              $cccols_headers = array_slice($cccols_headers, 3, -5); // get only custom cols headers
                  
                  if(is_string($item["customCols"]))
                    $item["customCols"] = mb_unserialize ($item["customCols"]);

      if (!empty($item["customCols"])) {
        $itemDesc = $item["label"];
        foreach($item["customCols"] as $ccol_header => $ccol_content)
          $itemDesc .= " - ".$cccols_headers[$ccol_header].": ".$ccol_content;
      }
      else {
        $itemDesc = $item["name"] . (empty($item["label"]) ? "" : " - " . $item["label"]);
      }
			$cmd_content .=	"
				<tr>
					<td>" . to_entities($item["idTC"]) . "</td>
					<td>" . to_entities($itemDesc) . "</td>
					<td>" . sprintf("%0.2f", $item["price"]) . "</td>
					<td>" . $item["quantity"] . "</td>
					<td>" . sprintf("%.02f", $item["sum_base"]) . "</td>
				</tr>";
			if (!empty($item["promotion"])) {
				$cmd_content .= "
				<tr>
					<td></td>
					<td colspan=\"3\">" . "Promotion de" . " <b>" . sprintf("%.02f", $item["promotionpc"]) . "%</b> " . "pour" . " <b>" . $item["quantity"] . "</b> x " . $item['name'] . "</td>
					<td>" . sprintf("%.02f", -$item["sum_promotion"]) . "</td>
				</tr>";
			}
			if (!empty($item["discount"])) {
				$cmd_content .=	"
				<tr>
					<td></td>
					<td colspan=\"3\">" . "Remise de " . " <b>" . sprintf("%.02f", $item["discountpc"]) . "%</b> " . "pour" . " <b>" . $item["quantity"] . "</b> x " . $item["name"] . "</td>
					<td>" . to_entities(sprintf("%0.2f", -$item["sum_discount"])) . "</td>
				</tr>";
			}
		}

		$cmd_content .=	"
				</tbody>
			</table>
			<br/>
			Sous-total HT : <b>" . sprintf("%.02f", $cmd->stotalHT) . "€</b><br/>
			Frais de Port HT : <b>" . sprintf("%.02f", $cmd->fdpHT) . "€</b><br/>
			Total HT : <b>" . sprintf("%.02f", $cmd->totalHT) . "€</b><br/>
			TVA : <b>". sprintf("%.02f", $cmd->totalTVA) ."</b><br/>
			Total TTC : <b>" . sprintf("%.02f", $cmd->totalTTC) . "€</b><br/>
			<br/>
			<br/>
			<u>Adresse de livraison</u><br/>
				<b>" . $titre_l . " " . $cmd->coord["nom_l"] . " " . $cmd->coord["prenom_l"] . "</b><br/>
				" . ($cmd->coord["societe_l"] != "" ? $cmd->coord["societe_l"] . "<br/>\n" : "\n") . "
				" . $cmd->coord["adresse_l"] . "<br/>
				" . $cmd->coord["complement_l"] . " " . $cmd->coord["cp_l"] . " " . $cmd->coord["ville_l"] . "<br/>
				" . $cmd->coord["pays_l"] . "<br/>
				" . ($cmd->coord["societe_l"] != "" ? "" : "<br/>") . "
			<br/>
			<u>Adresse de facturation</u><br/>
				<b>" . $titre . " " . $cmd->coord["nom"] . " " . $cmd->coord["prenom"] . "</b><br/>
				" . ($cmd->coord["societe"] != "" ? $cmd->coord["societe"] . "<br/>\n" : "\n") . "
				" . $cmd->coord["adresse"] . "<br/>
				" . $cmd->coord["complement"] . " " . $cmd->coord["cp"] . " " . $cmd->coord["ville"] . "<br/>
				" . $cmd->coord["pays"] . "<br/>
				" . ($cmd->coord["societe"] != "" ? "" : "<br/>") . "
			";

  $mail = new Email(array(
      "email" => $user->email.",passage-commande@techni-contact.com",
      "subject" => "Votre commande Techni-Contact n°".$cmd->id,
      "headers" => "From: Service client Techni-Contact <web@techni-contact.com>\nReply-To: Service client Techni-Contact <web@techni-contact.com>\r\n",
      "template" => "user-fo_order-new_order",
      "data" => array(
        "FO_URL" => URL,
        "FO_ACCOUNT_URL_INFOS" => COMPTE_URL."infos.html",
        "CUSTOMER_FIRSTNAME" => $cmd->coord["prenom"],
        "CUSTOMER_LASTNAME" => $cmd->coord["nom"],
        "ORDER_ID" => $cmd->id,
        "ORDER_PAYMENT_TYPE" => Command::getPaymentTypeText($cmd->type_paiement),
        "ORDER_COMPLETE_CONTENT" => $cmd_content,
        "ORDER_FORM_URL" => PDF_URL."bon_commande_generate.php?orderID=".$cmd->id
      )
    ));
    $mail->send();

}


if(isset($_POST['confirm-mail']) && $_POST['confirm-mail'] == 1 && isset($_POST['supplier'])){
  
  if (!$userChildScript->get_permissions()->has("m-comm--sm-orders","e")) {
    print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
    exit();
  }

  if($cmd->isCmdAdvertiser($_POST['supplier'])){

         $infosFournisseur = $cmd->getAdvertiserInfos($_POST['supplier']);

         $dft_qte_list = array();
         $totalHT = 0;
         $totalTTC = 0;

         $tableProducts = '
        <table class="liste_produits_popup">
           <thead><tr><td>Image</td><td>Ref TC</td><td>Ref Fournisseur</td><td>Qt&eacute;</td><td>Prix fournisseur Unitaire</td><td>Prix total HT</td></tr></thead>
           <tbody>';


                foreach ($cmd->items as &$item){
                    if($item["idAdvertiser"] == $_POST['supplier']){
//                      $fournisseur = $cmd->suppliersProccessingStatus[$_POST['supplier']];
                      $opUser = new BOUser($userChildScript->id);
                      $opUser = $opUser->name;
                      $totalHT += $item["sum_base_price2"];
//                      $totalHT += $item["sum_base_price"];
                      $diffTVA = $totalHT*($item["tauxTVA"]/100);
                      $totalTTC = $totalHT+$diffTVA;
                      $dft_qte_list[$item["idTC"]] = $item["quantity"];
                      $pdt_image = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$item["idProduct"]."-1.jpg") ? SECURE_RESSOURCES_URL."images/produits/thumb_small/".$item["idProduct"]."-1.jpg" : SECURE_RESSOURCES_URL."images/produits/no-pic-thumb_small.gif";
                      $refPdtName = $cmd->getRefNameById($item["idProduct"]);
                      $tableProducts .= '<tr>
                        <td class="center"><a href="'.URL.'produits/'.$item['idFamily'].'-'.$item['idProduct'].'-'.$refPdtName.'.html'.'" target="_blank"><img src="'.$pdt_image.'" /></a></td>
                        <td class="center"><a href="'.ADMIN_URL.'products/edit.php?id='.$item["idProduct"].'">'.$item["idProduct"].'</a></td>
                        <td class="center">'.$cmd->getRefSupplier($item["idProduct"],$item["idTC"]).'</td>
                        <td class="right">'.$item["quantity"].'</td>
                        <td class="ref-prix">'.sprintf("%0.2f", $item["price2"]).'</td>
                        <td class="ref-prix">'.sprintf("%0.2f", $item["sum_base_price2"]).'</td>
                      </tr>';

                }
             }

           $tableProducts .= '</tbody>
         </table>';

         $tableTotal = '<table class="libelle_total">
           <tr>
             <td class="text">Total commande HT</td>
             <td class="valeur">'.sprintf("%0.2f", $totalHT).'</td>
           </tr>
           <tr>
             <td class="text">Total commande TTC</td>
             <td class="valeur">'.sprintf("%0.2f", $totalTTC).'</td>
           </tr>
         </table>';


    if($cmd->processSendMailToAdvertiser($_POST['supplier'], $userChildScript->id, $_POST['commentMail'])){

      $emailFournisseur = !empty ($infosFournisseur['econtact']) ? $infosFournisseur['econtact'] : $infosFournisseur['email'];
      // TC mail
      $arrayEmail = array(
        "email" => 'achat@techni-contact.com',
        "subject" => "Confirmation envoi commande ".$_POST['supplier']."-".$cmd->id." à ".$infosFournisseur['nom1'],
        "headers" => "From: achat@techni-contact.com\nReply-To: ".$emailFournisseur."<".$emailFournisseur.">\r\n",
        "template" => "advertiser-bo_commandes-TC_info",
        "data" => array(
          "EXP_DATE" => date('d/m/Y - H:i:s'),
          'ADVERTISER_NAME' => $infosFournisseur['nom1'],
          'OPERATOR_NAME' => $opUser,
          'LINK' => ADMIN_URL.'orders/orderDetail.php?idOrdre='.$_POST['supplier'].'-'.$cmd->id,
          'USER_ID' => $_POST['supplier'],
          'CMD_NUMBER' => $cmd->id,
          'PRODUCTS_TABLE' => $tableProducts,
          'TOTAL_TABLE' => $tableTotal,
        )
      );

      $mail = new Email($arrayEmail);
      $mail->send();

      $arrayEmail = array();
      // prospect's mail
      $Cc = '';

      if(!empty($infosFournisseur['econtact']))
        $Cc .= !empty($Cc) ? ', '.$infosFournisseur['pcontact'].' '.$infosFournisseur['ncontact'].'<'.$infosFournisseur['econtact'].'>' : $infosFournisseur['pcontact'].' '.$infosFournisseur['ncontact'].'<'.$infosFournisseur['econtact'].'>';

      if(!empty($infosFournisseur['contacts']))
        foreach ($infosFournisseur['contacts'] as $contact)
          if(!empty($contact['email']))
              $Cc .= !empty($Cc) ? ', '.$contact['prenom'].' '.$contact['nom'].'<'.$contact['email'].'>' : $contact['prenom'].' '.$contact['nom'].'<'.$contact['email'].'>';

      $Cc = !empty($Cc) ? 'Cc:'.$Cc."\n" : '';

      $arrayEmail = array(
        "email" => $emailFournisseur,
        "subject" => "Passation de commande n°".$_POST['supplier']."-".$cmd->id,
        "headers" => "From: Service Achat Techni-Contact <achat@techni-contact.com>\n".$Cc."Reply-To: Service Achat Techni-Contact <achat@techni-contact.com>\r\n",
        "template" => "advertiser-bo_commandes-envoi_commande",
        "data" => array(
          'ADVERTISER_LOGIN' => $infosFournisseur['login'],
          'ADVERTISER_PASSWORD' => $infosFournisseur['pass'],
          'LINK' => EXTRANET_URL,
          'CMD_NUMBER' => $_POST['supplier']."-".$cmd->id,
        )
      );
      $mail = new Email($arrayEmail);
      $mail->send();

    }elseif($_POST['resend_mail'] = 'Renvoyer le mail'){

      $arrayEmail = array();
      // prospect's mail
      $Cc = '';

      if(!empty($infosFournisseur['econtact']))
        $Cc .= !empty($Cc) ? ', '.$infosFournisseur['pcontact'].' '.$infosFournisseur['ncontact'].'<'.$infosFournisseur['econtact'].'>' : $infosFournisseur['pcontact'].' '.$infosFournisseur['ncontact'].'<'.$infosFournisseur['econtact'].'>';
      if(!empty($infosFournisseur['contacts']))
        foreach ($infosFournisseur['contacts'] as $contact)
          if(!empty($contact['email']))
              $Cc .= !empty($Cc) ? ', '.$contact['prenom'].' '.$contact['nom'].'<'.$contact['email'].'>' : $contact['prenom'].' '.$contact['nom'].'<'.$contact['email'].'>';

      $emailFournisseur = !empty ($infosFournisseur['econtact']) ? $infosFournisseur['econtact'] : $infosFournisseur['email'];

      $Cc = !empty($Cc) ? 'Cc:'.$Cc."\n" : '';

      $arrayEmail = array(
        "email" => $emailFournisseur,
        "subject" => "Passation de commande n°".$_POST['supplier']."-".$cmd->id,
        "headers" => "From: Service Achat Techni-Contact <achat@techni-contact.com>\n".$Cc."Reply-To: Service Achat Techni-Contact <achat@techni-contact.com>\r\n",
        "template" => "advertiser-bo_commandes-envoi_commande",
        "data" => array(
          'ADVERTISER_LOGIN' => $infosFournisseur['login'],
          'ADVERTISER_PASSWORD' => $infosFournisseur['pass'],
          'LINK' => EXTRANET_URL,
          'CMD_NUMBER' => $_POST['supplier']."-".$cmd->id,
        )
      );

      $mail = new Email($arrayEmail);
      $mail->send();

    }
  }

}

/**
 * file upload
 */
if(isset($_POST['load-arc']) && $_POST['load-arc'] == 1 && isset($_POST['supplier']) && !empty($_FILES['arcFile'])){

  if (!$userChildScript->get_permissions()->has("m-comm--sm-orders","e")) {
    print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
    exit();
  }

  if($cmd->isCmdAdvertiser($_POST['supplier'])){

    if(is_uploaded_file($_FILES['arcFile']['tmp_name'])){
      if($_FILES['arcFile']['type'] == 'application/pdf'){
        $nameFile = $_POST['supplier']."-".$cmd->id.'_'.$_FILES['arcFile']['name'];
          if(@move_uploaded_file ($_FILES['arcFile']['tmp_name'], PDF_ARC.$nameFile)){
              $cmd->updateArc($_POST['supplier'], $nameFile);
          }else
            $errorstring .= "- Le fichier uploadé n'a pu être copié correctement<br />\n";
      }else
            $errorstring .= "- Type de fichier incorrect<br />\n";
    }

  }

}

if ($errorstring != "") {
?>
<div class="titreStandard">Commande n°<?php echo $orderID ?></div>
<br/>
<div class="bg" style="position: relative">
	<h2><?php echo $errorstring ?></h2>
</div>
<?php
  require(ADMIN."tail.php");
	exit();
}
else {
	$typePaiement = getTypePaiement($cmd->type_paiement);
	$statutPaiement = getStatutPaiement($cmd->statut_paiement);
	$statutTraitement = getStatutTraitementGlobal($cmd->statut_traitement);
        $statutTimestamp = $cmd->statut_timestamp != 0 ? date('d/m/Y H:i', $cmd->statut_timestamp) : '';
        $commandeType = getTypeCommande($cmd->type_commande);
}
?>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<style type="text/css">
.DB-bg  { display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: #000000; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=40)"; filter: alpha(opacity=40); opacity:.40 ; z-index: 1}
.DB { display: none; position: absolute; padding: 10px; font: small-caps bold 13px tahoma, arial, sans-serif; color: #000000; text-align: center; border: 1px solid #cccccc; background: #f4faff; z-index: 1}
#CQDB { left: 20px; top: 50px; width: 900px; }
#commentMail{width: 700px;}
#EmlConfDB { left: 100px; top: 450px; width: 600px; height: 200px;  z-index: 1}
#EmlCloseDB { left: 100px; top: 450px; width: 600px; height: 200px;  z-index: 1}
</style>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __COMMAND_ID__ = <?php echo $orderID ?>;
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';

var PaymentMeanList = new Array();

<?php
foreach($TypePaiementList as $k => $v)
	print "PaymentMeanList[$k] = '" . addslashes($v) . "';\n";
?>
var PaymentStatusList = new Array();
<?php
foreach($statutPaiementList as $k => $v)
	print "PaymentStatusList[$k] = '" . addslashes($v) . "';\n";
?>
var ProcessingStatusList = new Array();
<?php
foreach($statutTraitementGlobalList as $k => $v)
	print "ProcessingStatusList[$k] = '" . addslashes($v) . "';\n";
?>
var TypeCommandeList = new Array();
<?php
foreach($TypeCommande as $k => $v)
	print "TypeCommandeList[$k] = '" . addslashes($v) . "';\n";
?>
</script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>

<div class="titreStandard">Commande n°<?php echo $orderID ?>| <a href="CommandDelete.php?<?php echo $sid ?>&commandID=<?php echo $orderID ?>" onClick="return confirm('Etes-vous sûr de vouloir supprimer cette commande ?')">Supprimer la commande</a> | <button onclick="resendCustomerMail(<?php echo $orderID ?>,<?php echo $cmd->idClient ?>);">Renvoyer la commande</button></div>
<div class="bg" style="position: relative">
	<div id="commande">
		<div id="performingRequestMW">Modification en cours...</div>
		<a href="index.php?<?php echo $sid ?>">&lt;&lt; Aller à la liste des commande client</a>
                <a style="float :right" href="../clients/index.php?&idClient=<?php echo $cmd->idClient ?>">Aller à la fiche client &gt;&gt;</a>
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
}
else {
  $coord["email"] = $coord["tel1"] = $coord["fax1"] = "utilisateur supprimé";
}

require('CustomerSearchWindow.php');
require('CoordChangeWindow.php');
//require('ProductExplorerWindow.php');
require('ProductChooserWindow.php');
?>
		<div id="panier">
			<div id="NewClientError"><br /><?php echo $ClientError ?></div>
			<div class="infosL">
				<div id="ModifierClient">
					<div class="infos">
						<div class="intitule" style="width: 70px">N° Client :</div>
						<input type="text" class="clientID" id="clientID_fixed" maxlength="10" value="<?php echo $cmd->idClient ?>" readonly="readonly" />
						<input type="text" class="clientIDn" id="clientID_edit" maxlength="10" value="<?php echo $cmd->idClient ?>" />
						<input type="button" class="fValidUn" style="width: 65px" id="button_change" value="Changer" onclick="showNewClientOptions()" />
						<input type="button" class="fValidUn" style="width: 65px; display: none" id="button_search" value="Chercher" onclick="showClientSearchWindow()" />
						<input type="button" class="fValidUn" style="width: 75px; display: none" id="button_save" value="Enregistrer" onclick="saveNewClient()" />
						<input type="button" class="fValidUn" style="width: 58px; display: none" id="button_cancel" value="Annuler" onclick="cancelNewClient()" />
					</div>
					<div class="infos">
						<div class="intitule" style="width: 70px">Société :</div>
						<div class="valeur" id="company_fixed"><?php echo $coord['societe'] ?></div>
					</div>
				</div>
			</div>
			<div class="infosC">
				<div class="intitule" style="width: 40px">Email :</div><div id="email_fixed" class="valeurL" style="width: 256px"><a href="mailto:<?php echo $coord["email"] ?>?subject=Important - votre commande Techni-Contact n <?php echo $orderID ?> - demande d'infos"><?php echo $coord["email"] ?></a></div><div class="zero"></div>
                                <div class="intitule" style="width: 40px">Tel :</div><div id="tel1_fixed" class="valeurL" style="width: 105px"><a href="dial:<?php echo preg_replace('/[^0-9\+.]?/', '', $coord["tel1"]) ?>"><?php echo preg_replace('/[^0-9\+.]?/', '', $coord["tel1"]) ?> <img src="../ressources/icons/telephone.png" alt="Tel" style="vertical-align:middle" /></a></div>
				<div class="intitule" style="width: 40px">Fax :</div><div id="fax1_fixed" class="valeurL" style="width: 105px"><?php echo $coord["fax1"] ?></div><div class="zero"></div>
			</div>
			<div class="infosR">
				<div class="intitule" style="width: 125px">Date de création :</div><div class="valeurR">le <?php echo date("d/m/Y à H:i.s", $cmd->create_time) ?></div><div class="zero"></div>
				<div class="intitule" style="width: 125px">Dernière mise à jour :</div><div class="valeurR">le <?php echo date("d/m/Y à H:i.s", $cmd->timestamp) ?></div><div class="zero"></div>
			</div>
			<div class="zero"></div>
			<div class="intitule" style="width: 160px">ID de la campagne source :</div><div id="email_fixed" class="valeurL" style="width: 100px"><?php echo $cmd->campaignID ?></div><div class="zero"></div>
			<div class="intitule" style="width: 130px">
				<input type="hidden" id="CommandeTypeValue" value="<?php echo $cmd->type_commande ?>" />
				<div class="intitule">Type de commande : </div>
                        </div>
                        <div id="CommandeType_fixed" class="valeurL" style="width: 120px">
				<div class="valeurR"><a id="CommandeTypeMod" href="javascript: editCommandeType()">Modifier</a></div>
				<div class="valeur" id="CommandeType"><?php echo $commandeType ?></div>
                        </div>
                        <div class="zero"></div>
                        <div class="infos">Contenu de la commande n°<?php echo $orderID ?> | <a href="" id="call_upload_form_dialog">Ajouter un document</a> </div>
			<div class="zero"></div>
                        <div id="uploadedFilesList"></div>
                        <div class="zero"></div>
                        <div id="MainSuppliersTab">
                          <div class="DB-bg"></div>
                          <div id="CQDB" class="DB"></div>
                          <div id="EmlConfDB" class="DB">
                            Votre message a bien été transmis au client. Votre conversation est maintenant archivée sur cette page.<br />
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
                          <table class="liste_produits" cellspacing="0" cellpadding="0">
                                <thead>
                                        <tr>
                                        <th style="width: auto;">Fournisseurs concernés :</th>
                                        </tr>
                                 </thead>
                                <tbody id="SuppliersList">
                                  <?php
                                  if($cmd->suppliersProccessingStatus)
                                  foreach ($cmd->suppliersProccessingStatus as &$supplier) {?>
                                        <tr>
                                          <td>
                                            <img src="../ressources/icons/<?php echo $supplier["isMailSent"] ? 'ok-16x16.png' : 'hexa-no-16x16.png' ?>" alt="<?php echo $supplier["isMailSent"] ? 'Mail envoyé' : 'Mail à envoyer' ?>" />
                                             <?php echo $cmd->getAdvertiserName($supplier["idAdvertiser"]) ?> -
                                             <?php 
                                             if($supplier["isMailSent"]){
                                               $sender = new BOUser($supplier["idSender"]);
                                               ?>
                                               Commande <a href="../orders/orderDetail.php?idOrdre=<?php echo $supplier["idAdvertiser"].'-'.$cmd->id ?>"><?php echo $supplier["idAdvertiser"].'-'.$cmd->id ?></a> envoyée le <?php echo date('d/m/y à H:i', $supplier["timestampIMS"]) ?> par <?php echo $sender->name ?>
                                                - 
                                               <form action="" method="post" name="resend_mail" id="resend_mail">
                                                 <input type="hidden" name="confirm-mail" value="1" />
                                                 <input type="hidden" name="supplier" value="<?php echo $supplier["idAdvertiser"] ?>" />
                                                 <input type="submit" name="resend_mail" value="Renvoyer le mail" />
                                               </form>
                                             <?php
                                             unset($sender);
                                             }else{ ?>
                                                <a href="javascript: return false;" onClick="sendMail(<?php echo $cmd->id.','.$supplier["idAdvertiser"] ?>);">Envoyer la commande au fournisseur</a>
                                          <?php } ?>
                                                 -
                                                 <img src="../ressources/icons/<?php echo $supplier["timestampArc"] != 0 ? 'ok-16x16.png' : 'hexa-no-16x16.png' ?>" alt="<?php echo $supplier["timestampArc"] != 0 ? 'Arc disponible' : 'Pas d\'Arc;' ?>" />
                                                 <?php if($supplier["timestampArc"] != 0 ){ ?>
                                                    <a href="<?php echo PDF_URL_ARC.$supplier['arc'] ?>" target="_blank">voir ARC</a> - <a href="javascript: return false;" onClick="loadArc(<?php echo $cmd->id.','.$supplier["idAdvertiser"] ?>);">Modifier ARC</a>
                                                 <?php }else{ ?>
                                                    <a href="javascript: return false;" onClick="loadArc(<?php echo $cmd->id.','.$supplier["idAdvertiser"] ?>);">Lier ARC</a>
                                                 <?php } ?>
                                          </td>
                                        </tr>
                                  <?php } ?>
                                </tbody>
                        </table>

                        </div>
                        <br />
			<div id="MainCommandTab">
				<table class="liste_produits" cellspacing="0" cellpadding="0">
					<thead>
						<tr>
							<th style="width: auto;">Image</th>
							<th style="width: 80px">Réf. Produit</th>
							<th style="width: 80px">Réf. TC</th>
                                                        <th style="width: 80px">Réf. Fourn.</th>
							<th style="width: 110px">Fournisseur</th>
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

            foreach ($cmd->items as &$item) {

              $resref = $handle->query("SELECT content FROM references_cols WHERE idProduct = '".$handle->escape($item['idProduct'])."'");
              $cols_headers = $handle->fetchAssoc($resref);
              $cccols_headers = mb_unserialize($cols_headers['content']);
              $cccols_headers = array_slice($cccols_headers, 3, -5); // get only custom cols headers

                if(is_string($item["customCols"]))
                  $item["customCols"] = mb_unserialize($item["customCols"]);
                if (!empty($item["customCols"])) {
                  $itemDesc = $item["label"];
                  foreach($item["customCols"] as $ccol_header => $ccol_content){
                    $labelCol = is_numeric($ccol_header) ? $cccols_headers[$ccol_header] : $ccol_header;
                    $itemDesc .= " - ".$labelCol.": ".$ccol_content; //$itemDesc .= " - ".$cccols_headers[$ccol_header].": ".$ccol_content;
                  }
                }
                else {
                  $itemDesc = $item["name"] . (empty($item["fastdesc"]) ? "" : " - " . $item["fastdesc"]) . (empty($item["label"]) ? "" : " - " . $item["label"]);
                }
				$dft_qte_list[$item["idTC"]] = $item["quantity"];
				$pdt_image = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$item["idProduct"]."-1.jpg") ? SECURE_RESSOURCES_URL."images/produits/thumb_small/".$item["idProduct"]."-1.jpg" : SECURE_RESSOURCES_URL."images/produits/no-pic-thumb_small.gif";
				$refPdtName = $cmd->getRefNameById($item["idProduct"]);
				?>
					<tr>
						<td class="center"><a href="<?php echo URL.'produits/'.$item['idFamily'].'-'.$item['idProduct'].'-'.$refPdtName.'.html' ?>" target="_blank"><img src="<?php echo $pdt_image ?>" /></a></td>
						<td class="center"><input type="hidden" value="<?php echo $item["idProduct"] . "-" . $item["idTC"] ?>"><?php echo $item["idProduct"] ?></td>
						<td class="center"><a href="<?php echo ADMIN_URL ?>products/edit.php?id=<?php echo $item["idProduct"] ?>"><?php echo $item["idTC"] ?></a></td>
                                                <td class="center"><?php echo $item["refSupplier"] ?></td>
						<td class="center"><a href="<?php echo ADMIN_URL ?>advertisers/edit.php?id=<?php echo $item["idAdvertiser"] ?>"><?php echo $cmd->getAdvertiserName($item["idAdvertiser"]) ?></a></td>
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
						<td class="suppr"><img title="Supprimer ce produit" src="../ressources/b_drop.png" onclick="DelProduct(<?php echo $item["idProduct"] ?>, <?php echo $item["idTC"] ?>)" alt="Supprimer" /></td>
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
<?php			if (!empty($cmd->promotionCode)) { ?>
							(code : <?php echo $cmd->promotionCode ?>)
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
						<div class="total_D"><?php echo sprintf("%.02f", $cmd->stotalHT) ?>€</div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Frais de Port HT :</div>
						<div class="total_D" id="ShippingFee" title="Double cliquer pour modifier les frais de port" ondblclick="editShipppingFee()" onmouseover="toggleShippingFee_bg('over')" onmouseout="toggleShippingFee_bg('out')"><?php echo sprintf("%.02f€", $cmd->fdpHT) ?></div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Total HT :</div>
						<div class="total_D" id="TotalHT"><?php echo sprintf("%.02f", $cmd->totalHT) ?>€</div>
						<div class="zero"></div>
					</div>
                    <div class="total_Hn">
						<div class="total_G">ClicProtect :</div>
						<div class="total_D" id="insurance"><?php echo sprintf("%.02f", $cmd->insurance) ?>€</div>
						<div class="zero"></div>
					</div>
					<div class="total_Hn">
						<div class="total_G">Total TTC :</div>
						<div class="total_D" id="TotalTTC"><?php echo sprintf("%.02f", $cmd->totalTTC) ?>€</div>
						<div class="zero"></div>
					</div>
					<div id="ShippingFeeError"></div>
				</div>
				<div id="mod-montant-totaux">
					<div></div>
					<div><a id="ShippingFeeMod" href="javascript: editShipppingFee()" title="Cliquer ici pour modifier les frais de port" onmouseover="toggleShippingFee_bg('over')" onmouseout="toggleShippingFee_bg('out')">Modifier</a></div>
					<div></div>
					<div></div>
				</div>
				<div class="buttons">
					<input type="button" value="Ajouter un produit" class="fValidUn" style="width: 120px" onclick="showProductChooserWindow()" /><br />
					<input type="button" value="Recalculer" class="fValidUn" style="width: 120px" onclick="updateProductsQuantity()" />
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
<?php	foreach ($cmd->tvaTable as $vatRate => $amount) {
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
						<td class="total"><div><?php echo sprintf("%.02f", $cmd->stotalHT) ?></div>Total</td><td class="tvas"></td><td class="total-tva"><?php echo sprintf("%.02f", $cmd->totalTVA) ?></td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
                <div class="zero"></div>
                <div id="conversation"></div>
		<br />
                <div class="zero"></div>
		<div id="statusList" style="width:350px; float: left">
			<div class="infos">
				<input type="hidden" id="PaymentMeanValue" value="<?php echo $cmd->type_paiement ?>" />
				<div class="intitule">Mode de paiement : </div>
				<div class="valeurR"><a id="PaymentMeanMod" href="javascript: editPaymentMean()">Modifier</a></div>
				<div class="valeur" id="PaymentMean"><?php echo $typePaiement ?></div>
			</div>
			<div class="infos">
				<input type="hidden" id="PaymentStatusValue" value="<?php echo $cmd->statut_paiement ?>" />
				<div class="intitule">Statut de paiement : </div>
				<div class="valeurR"><a id="PaymentStatusMod" href="javascript: editPaymentStatus()">Modifier</a></div>
				<div class="valeur" id="PaymentStatus"><?php echo $statutPaiement ?></div>
			</div>
			<div class="infos">
				<input type="hidden" id="ProcessingStatusValue" value="<?php echo $cmd->statut_traitement ?>" />
                                <input type="hidden" id="PlannedDeliveryDate" value="<?php echo $cmd->planned_delivery_date ?>" />
                                <input type="hidden" id="partiallyCancelledReason" value="<?php echo $cmd->partially_cancelled_reason ?>" />
                                <input type="hidden" id="cancelReason" value="<?php echo $cmd->cancel_reason ?>" />
                                <input type="hidden" id="openSav" value="<?php echo $cmd->open_sav ?>" />
                                <input type="hidden" id="closeSav" value="<?php echo $cmd->close_sav ?>" />
                                <input type="hidden" id="dispatchComment" value="<?php echo $cmd->dispatch_comment ?>" />
				<div class="intitule">Statut de traitement : </div>
				<div class="valeurR"><a id="ProcessingStatusMod" href="javascript: editProcessingStatus()">Modifier</a></div>
				<div class="valeur" id="ProcessingStatus"><?php echo $statutTraitement ?> <?php echo $cmd->planned_delivery_date.$cmd->partially_cancelled_reason.$cmd->cancel_reason.$cmd->open_sav.$cmd->close_sav.$cmd->dispatch_comment ?></div>
                                <div class="valeur" id="ProcessingStatusTimestamp"><?php if($statutTimestamp)echo 'MAJ : '.$statutTimestamp  ?></div>
			</div>
			<div class="infos">
        <div id="sendEmailMsg" class="valeurR"></div>
        <div class="valeur"><input id="sendEmailCB" type="checkbox" checked="checked"/> Envoyer un email au client</div>
      </div>
			<div class="zero"></div>
		</div>
                <div id="bloc-IMOrderDetail">
                  <div class="bloc-IM-titre" style="background-color : #5D6068; color: #fff">Messagerie :</div>
                  <div class="bloc-IM-content">
                    Envoyer un message au client:<br />
                    <br />
                    <textarea name="contenu_message" cols="65" rows="6"></textarea><br />
                    <div class="bloc-preview">
                      <a href="#" onClick="sendMessage();return false;">Envoi du message</a> | <a href="" id="call_pjmess_form_dialog">Ajouter une pièce jointe</a>
                      <div id="pjMessList"></div>
                      <?php if(($cmd->attente_info == __MSGR_CTXT_CUSTOMER_TC_CMD__ || $cmd->attente_info == __MSGR_CTXT_ORDER_CMD__)): ?>
                      <a href="#" style="float:right; color: red" onClick="endConversation();return false;">Clore conversation</a>
                      <?php endif ?>
                    </div>
                  </div>
                </div>
                <div class="zero"></div>
		<br />
		<div class="livraison" ondblclick="showCoordChangeWindow()" title="Double cliquer pour modifier">
			<div class="titreBloc">Adresse de livraison</div>
			<div class="coord" id="ShipAddress">
				<input type="hidden" value="<?php echo $coord['coord_livraison'] ?>" id="coord_livraison" />
<?php	if ($coord['coord_livraison'] == 1) { ?>
				<b><span id="titre_l_fixed"><?php echo $titre_l ?></span> <span id="nom_l_fixed"><?php echo $coord['nom_l'] ?></span> <span id="prenom_l_fixed"><?php echo $coord['prenom_l'] ?></span></b><br />
				<span id="societe_l_fixed"><?php echo $coord['societe_l'] ?></span><span id="societe_l_br"><?php echo $coord['societe_l'] != '' ? '<br />' : '' ?></span>
				<span id="adresse_l_fixed"><?php echo $coord['adresse_l'] ?></span><br />
				<span id="complement_l_fixed"><?php echo $coord['complement_l'] ?></span> <span id="cp_l_fixed"><?php echo $coord['cp_l'] ?></span> <span id="ville_l_fixed"><?php echo $coord['ville_l'] ?></span><br />
				<div class="adr_modif"><a href="javascript: showCoordChangeWindow()" title="Cliquer ici pour modifier">Modifier</a></div>
				<span id="pays_l_fixed"><?php echo $coord['pays_l'] ?></span><br />
                                <span id="tel2_fixed"><?php echo $coord['tel2'] ?></span><br />
<?php	} else { ?>
				<b><span id="titre_l_fixed"><?php echo $titre ?></span> <span id="nom_l_fixed"><?php echo $coord['nom'] ?></span> <span id="prenom_l_fixed"><?php echo $coord['prenom'] ?></span></b><br />
				<span id="societe_l_fixed"><?php echo $coord['societe'] ?></span><span id="societe_l_br"><?php echo $coord['societe'] != '' ? '<br />' : '' ?></span>
				<span id="adresse_l_fixed"><?php echo $coord['adresse'] ?></span><br />
				<span id="complement_l_fixed"><?php echo $coord['complement'] ?></span> <span id="cp_l_fixed"><?php echo $coord['cp'] ?></span> <span id="ville_l_fixed"><?php echo $coord['ville'] ?></span><br />
				<div class="adr_modif"><a href="javascript: showCoordChangeWindow()" title="Cliquer ici pour modifier">Modifier</a></div>
				<span id="pays_l_fixed"><?php echo $coord['pays'] ?></span><br />
                                <span id="tel2_fixed"><?php echo $coord['tel1'] ?></span><br />
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
	</div>
	<div class="zero"></div>
        <div id="upload_form_dialog" title="Ajouter un document"></div>
        <div id="upload_pjmess_dialog" title="Ajouter une pièce jointe"></div>
</div>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ajaxFileUpload.js"></script>
<script src="Command.js" type="text/javascript"></script>
<script type="text/javascript">
// conversation / messagerie script
  var AJAXHandle = {
      dataType: "json",
      error: function(XMLHttpRequest, textStatus, errorThrown){
        if(AJAXHandle.url == "AJAX_conversation.php"){
          AJAX_conversation_error(XMLHttpRequest, textStatus, errorThrown);
        };

        if(AJAXHandle.url == "AJAX_cancelOrder.php"){
          AJAX_cancelOrder_error(XMLHttpRequest, textStatus, errorThrown)
        };
      },
      success: function(data, textStatus) {
        if(AJAXHandle.url == "AJAX_conversation.php"){
          AJAX_conversation_success(data, textStatus);
        };

        if(AJAXHandle.url == "AJAX_cancelOrder.php"){
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
                    "<div class=\"bloc-titre2\">Conversation liée à cette commande</div>"+
                    "<div id=\"messenger-conversation\" class=\"conversation\">";

        for (i = 0; i < data.conversations.length; i++){
          var attachment = data.conversations[i]['attachment'] != '' ? '&nbsp;<img src="../ressources/attachment-icon.png" class="show_messenger_attachment" id="show_messenger_attachment_'+data.conversations[i]['id']+'" alt="Pièces jointes" />' : '' ;
          html += "<h2>Message de "+data.conversations[i]['sender_name']+" envoyé le "+data.conversations[i]['date']+"</h2>"+attachment;
          html += "<div id=\"show_attachment_links_"+data.conversations[i]['id']+"\" class=\"show_attachment_links\" >";


        $.each(data.conversations[i]['attachment'], function(){
          var filename = this.alias_filename != '' ? this.alias_filename : this.filename;
           html += '<a href="<?php echo BO_UPLOAD_DIR ?>messenger/'+this.filename+'.'+this.extension+'" target="_blank">'+filename+'.'+this.extension+'</a><br />';
        });

        html += "</div>\n\
      <div class=\"zero\"></div>"+
                    "<ul class=\"list grey\">"+
                        "<li class=\"conversation first\"><pre style=\"white-space : pre-line\">"+
                        data.conversations[i]['text']+
                        "</pre></li></ul>";

        };
        html += "</div></div>";

        divConversation.append(html);

      }
    }else if(data.error){
      if($('#clientID_fixed').val() != 0){
        var textarea = $('textarea[name=contenu_message]');
        textarea[0].value = '';
        $("div.DB-bg").show();
        $("#EmlConfDB").html('');
        $("#EmlConfDB").append('<br /><br />La messagerie interne a bien fonctionné, mais une erreur est survenue à l\'envoi de l\'e-mail au client :'+data.error+'<br /><br /><input type="button" value="Ok" onClick="$(\'div.DB-bg\').hide();$(\'#EmlConfDB\').hide();" />');
        $("#EmlConfDB").show()
      }
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

$('.show_messenger_attachment').live(
  'click', function(){
  var id_pj = $(this).attr('id').replace('show_messenger_attachment_', '');
  $('#show_attachment_links_'+id_pj).css({
    position: 'absolute',
    top: ($('#show_messenger_attachment_'+id_pj).offset().top - 240),
    left: ($('#show_messenger_attachment_'+id_pj).offset().left+$('#show_messenger_attachment_'+id_pj).width() - 40),
    border: '1px solid gray',
    backgroundColor: '#C3FF7F',
    padding: '3px'
});
  $('#show_attachment_links_'+id_pj).toggle();
});

 $("#EmlConfDB input[type='button']:first").click(function(){
    $("div.DB-bg").hide();
    $("#EmlConfDB").hide();
  });


  function getConversation(){

     var divConversation = $("#conversation");
                  divConversation.empty();
                  divConversation.append( '<div class="bloc-titre2"></div>');

      AJAXHandle.data = "idUser="+<?php echo $cmd->idClient ?>+"&action=get&ordre="+<?php echo $cmd->id ?>;
      AJAXHandle.type = "GET";
      AJAXHandle.url = "AJAX_conversation.php";
      $.ajax(AJAXHandle);

   return false;
  }

  function sendMessage(){
      var contenuMessage = $('textarea[name=contenu_message]').val();
      var ext_pj_mess = '';
      if($('input[type=hidden][name^=pjMessenger]').length > 0){
        ext_pj_mess = '&';
//        var list_pj = new Array();
//        var returnListPj = $('input[type=hidden][name=pjMessenger\[\]]').each(function (){
//          var value = $(this).val();
//          list_pj.push(value);
////          return $(this).val();
//        });
        ext_pj_mess += $('input[type=hidden][name^=pjMessenger]').serialize();
      }

      AJAXHandle.data = "idUser="+<?php echo $cmd->idClient ?>+"&contenu="+encodeURIComponent(contenuMessage)+"&action=add&ordre="+<?php echo $cmd->id ?>+ext_pj_mess;
      AJAXHandle.type = "POST";

      AJAXHandle.url = "AJAX_conversation.php";
      $.ajax(AJAXHandle);

      getPjMessFilesList();
      getConversation();
  }

    function endConversation(){

      var contenuMessage = $('textarea[name=contenu_message]').val();
      AJAXHandle.data = "idUser="+<?php echo $cmd->idClient ?>+"&contenu="+encodeURIComponent(contenuMessage)+"&action=end&ordre="+<?php echo $cmd->id ?>;
      AJAXHandle.type = "POST";

      AJAXHandle.url = "AJAX_conversation.php";
      $.ajax(AJAXHandle);

      getConversation();

  }

  getConversation();

  // file upload dialog declaration
  $("#upload_form_dialog").dialog({
    width: 550,
    autoOpen: false,
    modal: true
  });

  $(".ui-dialog").draggable("option", "containment", '.ui-widget-overlay');

$('#call_upload_form_dialog').click(function(e){
    e.preventDefault();
    var uploadForm = '<form name="loadDoc" method="post" action="" enctype="multipart/form-data">\n\
    <img id="loading-gif" src="<?php echo SECURE_RESSOURCES_URL ?>images/lightbox-ico-loading.gif" style="display:none;">\n\
    <input type="hidden" name="action" value="load-doc"/>\n\
    <input type="hidden" name="supplier" value="<?php echo $_GET['idSupplier'] ?>"/>\n\
    <input type="hidden" name="cmdId" value="<?php echo $cmd->id ?>"/>\n\
    Nom : <input type="text" name="aliasFileName" value=""/><br /><br/>\n\
    S&eacute;lectionnez le document &agrave; lier &agrave; la commande <?php echo $cmd->id ?> <br/>\n\
     <br/>\n\
    <input type="file" name="docFile"  id="docFile"  accept="application/pdf" /><br/>\n\
     <br/>\n\
     <input type="button" value="Annuler" onclick="$(\'#upload_form_dialog\').dialog(\'close\');" /> &nbsp; &nbsp; <input type="button" value="Envoi" onclick="triggerUpload();"/>\n\
    </form>';
    $('#upload_form_dialog').html(uploadForm);
    $('#upload_form_dialog').dialog('open');
});

// file upload functions
function triggerUpload(){
  var uploadFile = new HN.TC.ajaxUploadFile();
  uploadFile.userId = <?php echo $userChildScript->id ?>;
  uploadFile.itemId = <?php echo $cmd->id ?>;
  uploadFile.requiredFileTypes = new Array('pdf');
  uploadFile.idDialog = 'upload_form_dialog';
  uploadFile.fileElementId = 'docFile';
  uploadFile.aliasFileName = $('input[name=aliasFileName]').val();
  uploadFile.callbackFunctionName = 'getUploadedFilesList()';
  uploadFile.doAjaxFileUpload();
}

function getUploadedFilesList(){
  $('#uploadedFilesList').html('');
  var uploadedFiles = '';
  uploadedFiles = new HN.TC.getUploadedFiles();
  uploadedFiles.userId = <?php echo $userChildScript->id ?>;
  uploadedFiles.itemId = <?php echo $cmd->id ?>;
  var fileList = uploadedFiles.getUploadedFilesListFunction();

  var html = '';
  $.each(fileList.list, function(){
//    Nom document + lien "Voir" + bouton supprimer (alerte de confirmation avant suppression
    var filename = this.alias_filename ? this.alias_filename : this.filename;
    html += filename+'.'+this.extension+' <a href="'+fileList.directory+this.filename+'.'+this.extension+'" target="_blank">Voir</a> <a href="" onClick="deleteUploadedFile('+this.id+',\''+filename+'.'+this.extension+'\');return false;"><img class="deleteFileIcon" src="../ressources/b_drop.png" alt="Supprimer" /></a><br />';
  });
  $('#uploadedFilesList').html(html);
}

function deleteUploadedFile(fileId, filename){
  deleteFile = new HN.TC.deleteUploadedFile();
  deleteFile.userId = <?php echo $userChildScript->id ?>;
  deleteFile.fileId = fileId;
  if(confirm('Souhaitez-vous supprimer le fichier '+filename+'?'))
    deleteFile.deleteFileFunction();
  
  getUploadedFilesList();
}

getUploadedFilesList();

  // messenger pj dialog declaration
  $("#upload_pjmess_dialog").dialog({
    width: 550,
    autoOpen: false,
    modal: true
  });
  $(".ui-dialog").draggable("option", "containment", '.ui-widget-overlay');

$('#call_pjmess_form_dialog').click(function(e){
    e.preventDefault();
    var uploadForm = '<form name="loadDoc" method="post" action="" enctype="multipart/form-data">\n\
    <img id="loading-gif" src="<?php echo SECURE_RESSOURCES_URL ?>images/lightbox-ico-loading.gif" style="display:none;">\n\
    <input type="hidden" name="action" value="load-doc"/>\n\
    <input type="hidden" name="supplier" value="<?php echo $_GET['idSupplier'] ?>"/>\n\
    <input type="hidden" name="cmdId" value="<?php echo $cmd->id ?>"/>\n\
    Nom : <input type="text" name="aliasPjMessFileName" value=""/><br /><br/>\n\
    S&eacute;lectionnez le document &agrave; lier &agrave; au message <br/>\n\
     <br/>\n\
    <input type="file" name="pjMessFile"  id="pjMessFile"  accept="application/pdf, image/jpeg" /><br/>\n\
     <br/>\n\
     <input type="button" value="Annuler" onclick="$(\'#upload_pjmess_dialog\').dialog(\'close\');" /> &nbsp; &nbsp; <input type="button" value="Envoi" onclick="triggerPjMess();"/>\n\
    </form>';
    $('#upload_pjmess_dialog').html(uploadForm);
    $('#upload_pjmess_dialog').dialog('open');
});

// messenger pj functions
function triggerPjMess(){
  var pjMessFile = new HN.TC.ajaxUploadFile();
  pjMessFile.userId = <?php echo $userChildScript->id ?>;
  pjMessFile.itemId = <?php echo $cmd->id ?>;
  pjMessFile.requiredFileTypes = new Array('pdf', 'jpg');
  pjMessFile.idDialog = 'upload_pjmess_dialog';
  pjMessFile.context = 'tmp-pjmess';
  pjMessFile.fileElementId = 'pjMessFile';
  pjMessFile.aliasFileName = $('input[name=aliasPjMessFileName]').val();
  pjMessFile.callbackFunctionName = 'getPjMessFilesList()';
  pjMessFile.doAjaxFileUpload();
}

function getPjMessFilesList(){
  $('#pjMessList').html('');
  var pjMessFiles = '';
  pjMessFiles = new HN.TC.getUploadedFiles();
  pjMessFiles.userId = <?php echo $userChildScript->id ?>;
  pjMessFiles.context = 'tmp-pjmess';
  pjMessFiles.itemId = <?php echo $cmd->id ?>;
  var fileList = pjMessFiles.getUploadedFilesListFunction();

  var html = '';
  $.each(fileList.list, function(){
//    Nom document + lien "Voir" + bouton supprimer (alerte de confirmation avant suppression
    var filename = this.alias_filename ? this.alias_filename : this.filename;
    html += filename+'.'+this.extension+' <a href="'+fileList.directory+this.filename+'.'+this.extension+'" target="_blank">Voir</a> <a href="" onClick="deletePjMessFile('+this.id+',\''+filename+'.'+this.extension+'\');return false;"><img class="deleteFileIcon" src="../ressources/b_drop.png" alt="Supprimer" /></a><input type="hidden" name="pjMessenger[]" value="'+this.id+'" /><br />';
  });
  $('#pjMessList').html(html);
}

function deletePjMessFile(fileId, filename){
  deletePjFile = new HN.TC.deleteUploadedFile();
  deletePjFile.userId = <?php echo $userChildScript->id ?>;
  deletePjFile.fileId = fileId;
  deletePjFile.context = 'tmp-pjmess';
  if(confirm('Souhaitez-vous supprimer la pièce jointe '+filename+'?'))
    deletePjFile.deleteFileFunction();

  getPjMessFilesList();
}

getPjMessFilesList();
</script>
<br />
<?php require(ADMIN."tail.php") ?>