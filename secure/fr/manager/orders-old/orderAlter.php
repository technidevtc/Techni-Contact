<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 25/2/2011

 Mises à jour :

 Fichier : /secure/manager/tva/index.php
 Description : modif ajax des ordres

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");
require(ADMIN."customers.php");
require(ADMIN."statut.php");
require(ADMIN."tva.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
    header('Location: ' . ADMIN_URL . 'login.html');
    exit();
}

header("Content-Type: text/html; charset=iso-8859-15");

if (!$user->get_permissions()->has("m-comm--sm-partners-orders","e")) {
  print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
  exit();
}

$sid = session_name() . '=' . session_id();

//function GetTitle($val)
//{
//	switch ($val)
//	{
//		case 1  : return 'M.';
//		case 2  : return 'Mlle';
//		case 3  : return 'Mme';
//		default : return 'M.';
//	}
//}

//function GetTitleNum($val)
//{
//	switch ($val)
//	{
//		case 'M.'   : return 1;
//		case 'Mlle' : return 2;
//		case 'Mme'  : return 3;
//		default : return 1;
//	}
//}

$orderID = isset($_GET['commandID']) ? $_GET['commandID'] : '';

$errorstring = '';

if (preg_match('/^[1-9]{1}[0-9]{0,8}\-[1-9]{1}[0-9]{0,8}$/', $orderID))
{
	
	//header("content-type: text/css; charset=iso-8859-1");
	$newClient = isset($_GET['newClient']) ? $_GET['newClient'] : '';
	$addProduct = isset($_GET['addProduct']) ? trim($_GET['addProduct']) : '';
	$delProduct = isset($_GET['delProduct']) ? trim($_GET['delProduct']) : '';
	$UpdatePdtsQties = isset($_GET['UpdatePdtsQties']) ? trim($_GET['UpdatePdtsQties']) : '';
	$addDiscount = isset($_GET['addDiscount']) ? trim($_GET['addDiscount']) : '';
	$delDiscount = isset($_GET['delDiscount']) ? trim($_GET['delDiscount']) : '';
	$alterPaymentMean = isset($_GET['alterPaymentMean']) ? trim($_GET['alterPaymentMean']) : '';
	$alterPaymentStatus = isset($_GET['alterPaymentStatus']) ? trim($_GET['alterPaymentStatus']) : '';
	$alterProcessingStatus = isset($_GET['alterProcessingStatus']) ? trim($_GET['alterProcessingStatus']) : '';
	$alterShippingFee = isset($_GET['alterShippingFee']) ? trim($_GET['alterShippingFee']) : '';
	$alterShipAddress = isset($_GET['alterShipAddress']) ? trim($_GET['alterShipAddress']) : '';
        $alterOrderStatus = isset($_GET['alterOrderStatus']) ? trim($_GET['alterOrderStatus']) : '';
  $sendEmail = isset($_GET['sendEmail']) ? (trim($_GET['sendEmail']) == "true" ? true : false) : false;
  $plannedDeliveryDate = isset($_GET["plannedDeliveryDate"]) ? trim($_GET["plannedDeliveryDate"]) : "";
	
	$isValid = array();
	
	// Vérification de la validité des différentes informations
	if ($alterPaymentMean != '')
	{
		if (array_key_exists($alterPaymentMean, $TypePaiementList)) $isValid['alterPaymentMean'] = true;
		else $errorstring .= "PaymentMeanError" . __ERRORID_SEPARATOR__ . "Le type de paiement est invalide" . __ERROR_SEPARATOR__;
	}
	if ($alterPaymentStatus != '')
	{
		if (array_key_exists($alterPaymentStatus, $statutPaiementList)) $isValid['alterPaymentStatus'] = true;
		else $errorstring .= "PaymentStatusError" . __ERRORID_SEPARATOR__ . "Le statut de paiement est invalide" . __ERROR_SEPARATOR__;
	}
	if ($alterProcessingStatus != '')
	{
		if (array_key_exists($alterProcessingStatus, $statutTraitementGlobalList)) $isValid['alterProcessingStatus'] = true;
		else $errorstring .= "ProcessingStatusError" . __ERRORID_SEPARATOR__ . "Le statut de traitement est invalide" . __ERROR_SEPARATOR__;
	}
	if ($alterShippingFee != '')
	{
		if (preg_match('/^[0-9]+((\.|\,)[0-9]+)?$/', $alterShippingFee)) $isValid['alterShippingFee'] = true;
		else $errorstring .= "ShippingFeeError" . __ERRORID_SEPARATOR__ . "Les frais de ports saisis sont invalides" . __ERROR_SEPARATOR__;
	}
        $statutOrderList = array(
            0 => 'Non encore consultée',
            1 => 'Attente Accusé Réception',
            2 => 'AR commande reçu',
            );
        if ($alterOrderStatus != '')
	{
		if (array_key_exists($alterOrderStatus, $statutOrderList)) $isValid['alterOrderStatus'] = true;
		else $errorstring .= "OrderStatusError" . __ERRORID_SEPARATOR__ . "Le statut de l'ordre est invalide" . __ERROR_SEPARATOR__;
	}
	
	// On vérifie que le client est valide
	if (!empty($newClient))
	{
		if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $newClient))
		{
			$newClientInfos = & loadCustomer($handle, $newClient);
			if ($newClientInfos === false)
				$errorstring .= "NewClientError" . __ERRORID_SEPARATOR__ . "Le client $newClient n'existe pas" . __ERROR_SEPARATOR__;
			else
				$isValid['newClient'] = true;
		}
		else $errorstring .= "NewClientError" . __ERRORID_SEPARATOR__ . "L'identifiant client est invalide" . __ERROR_SEPARATOR__;
	}
	elseif(!empty($alterShipAddress)) //  On traite le changement d'addresse si pas de nouveau client
	{
		if (isset($_GET['coord_livraison']))
		{
			if ($_GET['coord_livraison'] == '1')
			{
				$errorstring_c = '';
				$tab_coord = array('coord_livraison' => 1);
				$tab_coord['titre_l']      = isset($_GET['titre_l']) ?      substr(trim(urldecode($_GET['titre_l'])), 0, 255) : '';
				$tab_coord['nom_l']        = isset($_GET['nom_l']) ?        strtoupper(substr(trim(urldecode($_GET['nom_l'])), 0, 255)) : '';
				$tab_coord['prenom_l']     = isset($_GET['prenom_l']) ?     ucfirst(substr(trim(urldecode($_GET['prenom_l'])), 0, 255)) : '';
				$tab_coord['societe_l']    = isset($_GET['societe_l']) ?    ucfirst(substr(trim(urldecode($_GET['societe_l'])), 0, 255)) : '';
				$tab_coord['adresse_l']    = isset($_GET['adresse_l']) ?    substr(trim(urldecode($_GET['adresse_l'])), 0, 255) : '';
				$tab_coord['complement_l'] = isset($_GET['complement_l']) ? substr(trim(urldecode($_GET['complement_l'])), 0, 255) : '';
				$tab_coord['ville_l']      = isset($_GET['ville_l']) ?      strtoupper(substr(trim(urldecode($_GET['ville_l'])), 0, 255)) : '';
				$tab_coord['cp_l']         = isset($_GET['cp_l']) ?         substr(trim(urldecode($_GET['cp_l'])), 0, 5) : '';
				$tab_coord['pays_l']       = isset($_GET['pays_l']) ?       strtoupper(substr(trim(urldecode($_GET['pays_l'])), 0, 255)) : '';
				
				if ($tab_coord['titre_l'] != '1' && $tab_coord['titre_l'] != '2' && $tab_coord['titre_l'] != '3')
					$errorstring_c .= "- Le titre choisi n'existe pas<br />\n";
				
				if (($tab_coord['prenom_l'] == '' || $tab_coord['nom_l'] == '') && $tab_coord['societe_l'] == '')
					$errorstring_c .= "- Vous n'avez pas saisi les nom et prénom, ou le nom de la société<br />\n";
				
				if ($tab_coord['adresse_l'] == '')
					$errorstring_c .= "- Vous n'avez pas saisi l'adresse<br />\n";
				
				if ($tab_coord['ville_l'] == '')
					$errorstring_c .= "- Vous n'avez pas saisi la ville<br />\n";
				
				if ($tab_coord['cp_l'] == '' || !preg_match('/^[0-9]+$/', $tab_coord['cp_l']))
					$errorstring_c .= "- Le code postal est invalide<br />\n";
				
				if ($tab_coord['pays_l'] == '')
					$errorstring_c .= "- Vous n'avez pas saisi le pays<br />\n";
				
				if ($errorstring_c == '') $isValid['alterShipAddress'] = true;
				else $errorstring .= "ShipAddressError" . __ERRORID_SEPARATOR__ . "<br />Une ou plusieurs erreurs sont survenues lors de la validation :<br />" . $errorstring_c . "<br />" . __ERROR_SEPARATOR__;
			}
			else
			{
				$tab_coord = array('coord_livraison' => 0);
				$isValid['alterShipAddress'] = true;
			}
		}
		else $errorstring .= "ShipAddressError" . __ERRORID_SEPARATOR__ . "Il n'a pas été spécifié si les coordonnées de livraison sont les mêmes que celles de facturation ou pas" . __ERROR_SEPARATOR__;
	}
	
	if (!empty($UpdatePdtsQties))
	{
		$listpdt = explode('_', $UpdatePdtsQties);
		$nbpdt = count($listpdt)-1;
		$isValid['UpdatePdtsQties'] = array();
		$errorstring_q = '';
		for ($i = 0; $i < $nbpdt; $i++)
		{
			list($idProduct, $idTC, $quantity) = explode('-', $listpdt[$i]);
			if (isset($idProduct) && isset($quantity) && isset($idTC))
			{
				if (preg_match('/^[1-9]{1}[0-9]{1,7}$/', $idProduct))
				{
					if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC))
					{
						if (preg_match('/^[0-9]+$/',$quantity))
						{
							$isValid['UpdatePdtsQties'][$idTC] = $quantity;
						}
						else $errorstring_q .= "- La quantité saisie du produit de la ligne " . $i . " est invalide<br />\n";
					}
					else $errorstring_q .= "- La référence TC du produit " . $idProduct . " de la ligne " . $i . " est invalide<br />\n";
				}
				else $errorstring_q .= "- Le numéro identifiant du produit de la ligne " . $i . " est invalide<br />\n";
			}
			else $errorstring_q .= "- Les données identifiants le produit de la ligne " . $i . " sont invalides<br />\n";
		}
		if ($errorstring_q != '') $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Une ou plusieurs erreurs sont survenus lors de la mise à jour des quantités:<br />" . $errorstring_q . "<br />" . __ERROR_SEPARATOR__;
	}
	
	//  On vérifie la validité du produit à ajouter
	if (!empty($addProduct))
	{
		list($idProduct, $idTC, $quantity) = explode('-', $addProduct);
		
		if (isset($idProduct) && isset($quantity) && isset($idTC))
		{
			if (preg_match('/^[1-9]{1}[0-9]{1,7}$/', $idProduct))
			{
				if (preg_match('/^[0-9]+$/',$quantity))
				{
					if (empty($idTC))
					{
						if(($result = & $handle->query("select p.price, p.idTC from products_fr pfr, products p, advertisers a where p.id = $idProduct and p.id = pfr.id and p.idAdvertiser = a.id and a.actif = 1 and a.parent = 61049", __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
						{
							$rec = & $handle->fetch($result);
							if (preg_match('/^[0-9]+((\.|,)[0-9]{0,2})?$/', $rec[0])) $isValid['addProduct'] = $idProduct . '-' . $rec[1];
							else
							{
								if ($rec[0] == 'ref') $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Ce produit a des références. Veuillez choisir l'une d'elle pour ajouter ce produit à la commande" . __ERROR_SEPARATOR__;
								else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Ce produit n'a pas de prix et n'est donc pas disponible à la vente" . __ERROR_SEPARATOR__;
							}
						}
						else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Le produit ayant pour numéro identifiant $idProduct n'existe pas" . __ERROR_SEPARATOR__;
					}
					elseif (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC))
					{
						if(($result = & $handle->query("select rc.id from products_fr pfr, products p, advertisers a, references_content rc where p.id = $idProduct and p.price = 'ref' and rc.id = $idTC and p.id = pfr.id and p.id = rc.idProduct and p.idAdvertiser = a.id and a.actif = 1 and a.parent = 61049", __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
							$isValid['addProduct'] = $idProduct . '-' . $idTC;
						else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "La référence $idTC du produit $idProduct n'existe pas" . __ERROR_SEPARATOR__;
					}
					else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Le numéro identifiant TC du produit à ajouter est invalide" . __ERROR_SEPARATOR__;
				}
				else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "La quantité saisie du produit à ajouter est invalide" . __ERROR_SEPARATOR__;
			}
			else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Le numéro identifiant du produit à ajouter est invalide" . __ERROR_SEPARATOR__;
		}
		else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Les données identifiants le produit à ajouter sont invalides" . __ERROR_SEPARATOR__;
	}
	
	//  On vérifie la validité du produit à supprimer
	if (!empty($delProduct))
	{
		list($idProduct, $idTC) = explode('-', $delProduct);
		
		if (isset($idProduct) && isset($idTC))
		{
			if (preg_match('/^[1-9]{1}[0-9]{1,7}$/', $idProduct))
			{
				if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC))
				{
					$isValid['delProduct'] = $idProduct . '-' . $idTC;
				}
				else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Le numéro identifiant TC du produit à supprimer est invalide" . __ERROR_SEPARATOR__;
			}
			else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Le numéro identifiant du produit à supprimer est invalide" . __ERROR_SEPARATOR__;
		}
		else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Les données identifiants le produit à supprimer sont invalides" . __ERROR_SEPARATOR__;
	}
	
	// On vérifie que les produits à ajouter et à supprimer n'aient pas les mêmes identifiants
	if (isset($isValid['addProduct']) && isset($isValid['delProduct']) && $isValid['addProduct'] == $isValid['delProduct'])
	{
		unset($isValid['addProduct']);
		unset($isValid['delProduct']);
		$errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Vous ne pouvez pas supprimer et ajouter un produit ayant les mêmes identifiants" . __ERROR_SEPARATOR__;
	}
	
	print $errorstring . __MAIN_SEPARATOR__;
	
	// S'il y a quelque chose à faire
	if (!empty($isValid))
	{
		$order = new OrderOld($handle, $orderID);
		

		if (!$order->exists) {
			$errorstring .= '- La commande ayant pour numéro identifiant ' . $order->id . " n'existe pas<br />\n";
		}
		else {
			$outputstring = '';
			
//			if (isset($isValid['alterPaymentMean'])) {
//				$order->type_paiement = $alterPaymentMean;
//				print "PaymentMeanValue" . __OUTPUTID_SEPARATOR__ . $alterPaymentMean . __OUTPUT_SEPARATOR__;
//			}
//			if (isset($isValid['alterPaymentStatus'])) {
//				$order->statut_paiement = $alterPaymentStatus;
//				print "PaymentStatusValue" . __OUTPUTID_SEPARATOR__ . $alterPaymentStatus . __OUTPUT_SEPARATOR__;
//			}
                        if (isset($isValid['alterOrderStatus'])) {
                          switch ( $alterOrderStatus){
                            case 0: // Non encore consultée
                              $order->timestampSeen = 0;
                              $order->statut_traitement = 2;
                              $order->timestampArc = 0;
                              $order->arc = 0;
                              break;
                            case 1: // Attente Accusé Réception
                              if(!$order->timestampSeen)
                                    $order->timestampSeen = time();
                              $order->statut_traitement = 3;
                              $order->timestampArc = 0;
                              $order->arc = 0;
                              break;
                            case 2: // AR commande reçu
                              if(!$order->timestampSeen)
                                    $order->timestampSeen = time();
                              $order->statut_traitement = 4;
                              if(!$order->timestampArc)
                                    $order->timestampArc = time();
                              $order->arc = 0;
                              break;
                          }
                                $order->statut_timestamp = $orderStatusTimestamp = time();
                                print "OrderStatusTimestamp" . __OUTPUTID_SEPARATOR__ . $orderStatusTimestamp . __OUTPUT_SEPARATOR__;
				$order->statut_traitement_order = $alterOrderStatus;
				print "OrderStatusValue" . __OUTPUTID_SEPARATOR__ . $alterOrderStatus . __OUTPUT_SEPARATOR__;
			}
                        $cmd = new Command($handle, $order->idCommande);
                        if (!$cmd->exists) {
			$errorstring .= '- La commande ayant pour numéro identifiant ' . $order->id . " n'existe pas<br />\n";
                        }
                        else {
                          if (isset($isValid['alterProcessingStatus'])) {
                                  $order->statut_traitement = $alterProcessingStatus;
                                  $order->planned_delivery_date = $plannedDeliveryDate;
                                  $order->cancel_reason = $cancelReason;
                                  $order->open_sav = $openSav;
                                  $order->close_sav = $closeSav;
                                  $cmd->statut_traitement = $alterProcessingStatus;
                                  $cmd->planned_delivery_date = $plannedDeliveryDate;
                                  $cmd->cancel_reason = $cancelReason;
                                  $cmd->open_sav = $openSav;
                                  $cmd->close_sav = $closeSav;
                                  print "ProcessingStatusValue" . __OUTPUTID_SEPARATOR__ . $alterProcessingStatus . __OUTPUT_SEPARATOR__;
                                  print "PlannedDeliveryDate" . __OUTPUTID_SEPARATOR__ . $plannedDeliveryDate . __OUTPUT_SEPARATOR__;
                                  print "cancel_reason" . __OUTPUTID_SEPARATOR__ . $cancelReason . __OUTPUT_SEPARATOR__;

                                  // update command, not order
  //                                $updateOk = $order->updateCmd();
                                  $updateOk = $cmd->save();
                                  
                                  if ($sendEmail) {
                                    $customer = new CustomerUser($handle, $order->idClient);
                                    $mail = new Email(array(
                                      "email" => $customer->login,
                                      "subject" => "Suivi de votre commande ".$order->idCommande,
                                      "headers" => "From: Service achat Techni-Contact <achat@techni-contact.com>\nReply-To: Service achat Techni-Contact <achat@techni-contact.com>\r\n",
                                      "template" => "user-bo_orders-order_status_update",
                                      "data" => array(
                                        "FO_URL" => URL,
                                        "FO_ACCOUNT_URL" => COMPTE_URL."contact-form.html?id=".$order->idCommande."&type=1",
                                        "ORDER_ID" => $order->idCommande,
                                        "CUSTOMER_FIRSTNAME" => $customer->prenom,
                                        "CUSTOMER_LASTNAME" => $customer->nom,
                                        "ORDER_PROCESSING_STATUS" => getStatutTraitementGlobal($order->statut_traitement)." ".$order->planned_delivery_date
                                      )
                                    ));
                                    if($updateOk)
                                      if ($mail->send())
                                        print "sendEmail".__OUTPUTID_SEPARATOR__."Email envoyé avec succès".__OUTPUT_SEPARATOR__;
                                      else
                                        print "sendEmail".__OUTPUTID_SEPARATOR__."Erreur lors de l'envoi de l'email".__OUTPUT_SEPARATOR__;
                                  }

                                  exit;
                          }
			}
			if (isset($isValid['alterShippingFee'])) {
                          
//                          var_dump($order->stotalOrdreHT, $order->stotalOrdreTTC);
//                          if($alterShippingFee == 0 && $order->fdpOrdreHT > 0){
                            $order->stotalOrdreHT = $order->totalOrdreHT - $order->fdpOrdreHT;
                            $order->stotalOrdreTTC = $order->totalOrdreTTC - $order->fdpOrdreTTC;
//                          }else{
//                            $order->stotalOrdreHT = $order->totalOrdreHT;
//                            $order->stotalOrdreTTC = $order->totalOrdreTTC;
//                          }

				$order->fdpOrdreHT = round(floatval($alterShippingFee), 2);
				$order->fdpOrdre_tva = $order->getTVArate(__FPD_IDTVA_DFT__);
				$order->fdpOrdreTVA = round($order->fdpOrdreHT * $order->fdpOrdre_tva / 100, 2);
				$order->fdpOrdreTTC = $order->fdpOrdreHT + $order->fdpOrdreTVA;
				
				// Setting saved vars
				$order->totalOrdreHT = $order->stotalOrdreHT + $order->fdpOrdreHT;
				$order->totalOrdreTTC = $order->stotalOrdreTTC + $order->fdpOrdreTTC;
//                                var_dump($order->totalOrdreHT, $order->totalOrdreTTC);
                                
				
				print "Totals" . __OUTPUTID_SEPARATOR__ ;
				print "ShippingFee" . __DATA_SEPARATOR__ . sprintf("%.2f", $order->fdpOrdreHT) . __DATA_SEPARATOR__;
				print "TotalHT" . __DATA_SEPARATOR__ . sprintf("%.2f", $order->totalOrdreHT) . __DATA_SEPARATOR__;
				print "TotalTTC" . __DATA_SEPARATOR__ . sprintf("%.2f", $order->totalOrdreTTC) . __DATA_SEPARATOR__;
				print __OUTPUT_SEPARATOR__;
			}
			
//			if (isset($isValid['newClient'])) {
//				$order->idClient = $newClient;
//				$nci = &$newClientInfos;
//
//				$order->setCoordFromArray($newClientInfos);
//				foreach($nci as &$ci) $ci = to_entities($ci);
//
//				print "clientID_fixed" . __OUTPUTID_SEPARATOR__ . $newClient . __OUTPUT_SEPARATOR__;
//				print "company_fixed" . __OUTPUTID_SEPARATOR__ . $nci['societe'] . __OUTPUT_SEPARATOR__;
//
//				$fields = array('titre', 'nom', 'prenom', 'societe', 'adresse', 'complement', 'ville', 'cp', 'pays');
//				$size = count($fields);
//
//
//				print "BillingAddress" . __OUTPUTID_SEPARATOR__;
//				$nci['titre'] = GetTitle($nci['titre']);
//				for($i=0; $i < $size; $i++) print $fields[$i].'_fixed' . __DATA_SEPARATOR__ . $nci[$fields[$i]] . __DATA_SEPARATOR__;
//				print "societe_br" . __DATA_SEPARATOR__ . ($nci['societe'] != '' ? '<br />' : '') . __DATA_SEPARATOR__;
//				print "email_fixed" . __DATA_SEPARATOR__ . $nci['email'] . __DATA_SEPARATOR__;
//				print "tel1_fixed" . __DATA_SEPARATOR__ . $nci['tel1'] . __DATA_SEPARATOR__;
//				print "fax1_fixed" . __DATA_SEPARATOR__ . $nci['fax1'] . __DATA_SEPARATOR__;
//				print __OUTPUT_SEPARATOR__;
//
//				print "ShipAddress" . __OUTPUTID_SEPARATOR__;
//				print "coord_livraison" . __DATA_SEPARATOR__ . $nci['coord_livraison'] . __DATA_SEPARATOR__;
//				$_l = $nci['coord_livraison'] == 1 ? '_l' : '';
//				$nci['titre'.$_l] = GetTitle($nci['titre'.$_l]);
//				for($i=0; $i < $size; $i++) print $fields[$i].'_l_fixed' . __DATA_SEPARATOR__ . $nci[$fields[$i].$_l] . __DATA_SEPARATOR__;
//				print "societe_l_br" . __DATA_SEPARATOR__ . ($nci['societe'.$_l] != '' ? '<br />' : '') . __DATA_SEPARATOR__;
//				print __OUTPUT_SEPARATOR__;
//
//			}
//			elseif (isset($isValid['alterShipAddress'])) {
//				$fields = array('titre', 'nom', 'prenom', 'societe', 'adresse', 'complement', 'ville', 'cp', 'pays');
//				$fields_l = array('titre_l', 'nom_l', 'prenom_l', 'societe_l', 'adresse_l', 'complement_l', 'ville_l', 'cp_l', 'pays_l');
//				$size = count($fields);
//
//				print "ShipAddress" . __OUTPUTID_SEPARATOR__;
//				print "coord_livraison" . __DATA_SEPARATOR__ . $tab_coord['coord_livraison'] . __DATA_SEPARATOR__;
//				$order->coord['coord_livraison'] = $tab_coord['coord_livraison'];
//				if ($tab_coord['coord_livraison'] == 1)
//					for($i=0; $i < $size; $i++) $order->coord[$fields_l[$i]] = $tab_coord[$fields_l[$i]];
//				else
//					for($i=0; $i < $size; $i++) $order->coord[$fields_l[$i]] = $order->coord[$fields[$i]];
//
//				$order->coord['titre_l'] = GetTitle($order->coord['titre_l']);
//				for($i=0; $i < $size; $i++)	print $fields_l[$i].'_fixed' . __DATA_SEPARATOR__ . to_entities($order->coord[$fields_l[$i]]) . __DATA_SEPARATOR__;
//				$order->coord['titre_l'] = GetTitleNum($order->coord['titre_l']);
//
//				print "societe_l_br" . __DATA_SEPARATOR__ . ($order->coord['societe_l'] != '' ? '<br />' : '') . __DATA_SEPARATOR__;
//				print __OUTPUT_SEPARATOR__;
//			}
			
//			if (isset($isValid['UpdatePdtsQties']) && !empty($isValid['UpdatePdtsQties'])) {
//				foreach($isValid["UpdatePdtsQties"] as $idTC => $qty) {
//					$order->UpdateProductQuantity($idTC, $qty);
//				}
//				print "UpdatePdtsQties" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
//			}
//			if (isset($isValid['addProduct'])) {
//				list($idProduct, $idTC, $quantity) = explode('-', $addProduct);
//				$order->AddProduct($idProduct, $idTC, $quantity);
//				print "AddProduct" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
//			}
//			if (isset($isValid['delProduct'])) {
//				list($idProduct, $idTC) = explode('-', $delProduct);
//				$order->DelProduct($idProduct, $idTC);
//				print "DelProduct" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
//			}
//			if (isset($isValid['addProduct']) || isset($isValid['delProduct'])) {
//			}
			
			$order->updateOrder();
		}
	}
	else {
		//$errorstring .= "<b>Aucun changement n'a été effectuée sur cette commande car aucune des données soumises n'était valide</b><br />\n";
	}
}
else {
	$errorstring .= "- Le numéro d'identifiant de commande est invalide<br />\n";
}

?>