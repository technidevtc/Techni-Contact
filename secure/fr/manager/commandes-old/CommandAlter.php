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

if (!$user->get_permissions()->has("m-comm--sm-orders","e")) {
  print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
  exit();
}

$sid = session_name() . '=' . session_id();

function GetTitle($val)
{
	switch ($val)
	{
		case 1  : return 'M.';
		case 2  : return 'Mlle';
		case 3  : return 'Mme';
		default : return 'M.';
	}
}

function GetTitleNum($val)
{
	switch ($val)
	{
		case 'M.'   : return 1;
		case 'Mlle' : return 2;
		case 'Mme'  : return 3;
		default : return 1;
	}
}

$commandID = isset($_GET['commandID']) ? $_GET['commandID'] : '';

$errorstring = '';

if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $commandID))
{
	
	//header("content-type: text/css; charset=iso-8859-1");
	$newClient = isset($_GET['newClient']) ? $_GET['newClient'] : '';
	$addProduct = isset($_GET['addProduct']) ? trim($_GET['addProduct']) : '';
        $addMultipleProducts = isset($_GET['addMultipleProducts']) && trim($_GET['addMultipleProducts']) == 1 ? true : false;
	$delProduct = isset($_GET['delProduct']) ? trim($_GET['delProduct']) : '';
	$UpdatePdtsQties = isset($_GET['UpdatePdtsQties']) ? trim($_GET['UpdatePdtsQties']) : '';
	$addDiscount = isset($_GET['addDiscount']) ? trim($_GET['addDiscount']) : '';
	$delDiscount = isset($_GET['delDiscount']) ? trim($_GET['delDiscount']) : '';
        $alterPaymentMean = isset($_GET['alterPaymentMean']) ? trim($_GET['alterPaymentMean']) : '';
        $alterCommandeType = isset($_GET['alterCommandeType']) ? trim($_GET['alterCommandeType']) : '';
	$alterPaymentStatus = isset($_GET['alterPaymentStatus']) ? trim($_GET['alterPaymentStatus']) : '';
	$alterProcessingStatus = isset($_GET['alterProcessingStatus']) ? trim($_GET['alterProcessingStatus']) : '';
	$alterShippingFee = isset($_GET['alterShippingFee']) ? trim($_GET['alterShippingFee']) : '';
	$alterShipAddress = isset($_GET['alterShipAddress']) ? trim($_GET['alterShipAddress']) : '';
  $sendEmail = isset($_GET['sendEmail']) ? (trim($_GET['sendEmail']) == "true" ? true : false) : false;
  $plannedDeliveryDate = isset($_GET["plannedDeliveryDate"]) ? trim($_GET["plannedDeliveryDate"]) : "";
  $partiallyCancelledReason = isset($_GET["partiallyCancelledReason"]) ? trim($_GET["partiallyCancelledReason"]) : "";
  $cancelReason = isset($_GET["cancelReason"]) ? trim($_GET["cancelReason"]) : "";
  $openSav = isset($_GET["openSav"]) ? trim($_GET["openSav"]) : "";
  $closeSav = isset($_GET["closeSav"]) ? trim($_GET["closeSav"]) : "";
  $dispatchComment = isset($_GET["dispatchComment"]) ? trim($_GET["dispatchComment"]) : "";
	
	$isValid = array();
	
	// Vérification de la validité des différentes informations
	if ($alterPaymentMean != '')
	{
		if (array_key_exists($alterPaymentMean, $TypePaiementList)) $isValid['alterPaymentMean'] = true;
		else $errorstring .= "PaymentMeanError" . __ERRORID_SEPARATOR__ . "Le type de paiement est invalide" . __ERROR_SEPARATOR__;
	}
        if ($alterCommandeType != '')
	{
		if (array_key_exists($alterCommandeType, $TypeCommande)) $isValid['alterCommandeType'] = true;
		else $errorstring .= "CommandeTypeError" . __ERRORID_SEPARATOR__ . "Le type de commande est invalide" . __ERROR_SEPARATOR__;
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
                                $tab_coord['tel2']         = isset($_GET['tel2']) ?         substr(trim(urldecode($_GET['tel2'])), 0, 10) : '';
                                $tab_coord['infos_sup_l']  = isset($_GET['infos_sup_l']) ? trim(utf8_decode(urldecode($_GET['infos_sup_l']))) : '';
				
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

                                if ($tab_coord['tel2'] != '')
                                  if(!preg_match('/^[0-9]{10}$/', $tab_coord['tel2']))
					$errorstring_c .= "- Le téléphone de livraison est invalide<br />\n";
				
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
                  $idTC = trim($idTC);
                  if(empty ($idProduct) && preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC)){
                    if($result = & $handle->query("select idProduct from references_content where id = '".$handle->escape($idTC)."'")  && $handle->numrows($result, __FILE__, __LINE__) == 1){
                      $row = $handle->fetchAssoc($result);
                      $idProduct = $row['idProduct'];
                    }
                  }

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
						if(($result = & $handle->query("select rc.id from products_fr pfr, products p, advertisers a, references_content rc where p.id = ".$idProduct." and p.price = 'ref' and rc.id = ".$idTC." and p.id = pfr.id and p.id = rc.idProduct and p.idAdvertiser = a.id and a.actif = 1 and a.parent = 61049", __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
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
		$cmd = new Command($handle, $commandID);
		if ($cmd->statut < 10) {
			$errorstring .= '- La commande ayant pour numéro identifiant ' . $cmd->id . " n'existe pas<br />\n";
		}
		else {
			$outputstring = '';
			
			if (isset($isValid['alterPaymentMean'])) {
				$cmd->type_paiement = $alterPaymentMean;
				print "PaymentMeanValue" . __OUTPUTID_SEPARATOR__ . $alterPaymentMean . __OUTPUT_SEPARATOR__;
			}
                        if (isset($isValid['alterCommandeType'])) {
				$cmd->type_commande = $alterCommandeType;
				print "CommandeTypeValue" . __OUTPUTID_SEPARATOR__ . $alterCommandeType . __OUTPUT_SEPARATOR__;
			}
			if (isset($isValid['alterPaymentStatus'])) {
				$cmd->statut_paiement = $alterPaymentStatus;
				print "PaymentStatusValue" . __OUTPUTID_SEPARATOR__ . $alterPaymentStatus . __OUTPUT_SEPARATOR__;
			}
            if (isset($isValid['alterProcessingStatus'])) {
              $cmd->statut_traitement = $alterProcessingStatus;
              $cmd->planned_delivery_date = $plannedDeliveryDate;
              $cmd->partially_cancelled_reason = $partiallyCancelledReason;
              $cmd->cancel_reason = $cancelReason;
              $cmd->open_sav = $openSav;
              $cmd->close_sav = $closeSav;
              $cmd->dispatch_comment = $dispatchComment;
              $cmd->statut_timestamp = $statutTimestamp = time();

              $customer = new CustomerUser($handle,$cmd->idClient);

              // Record notification in messenger
              $contenu = 'Mail de notification de changement de statut commande
Opérateur : '.$user->name.'
Nouveau statut : '.getStatutTraitementGlobal($cmd->statut_traitement).'
'.$cmd->planned_delivery_date.$cmd->partially_cancelled_reason.$cmd->cancel_reason.$cmd->open_sav.$cmd->close_sav.$cmd->dispatch_comment;
              $messagerie = new MessengerOld($handle, $user, __MSGR_CTXT_CUSTOMER_TC_CMD__);
              $messagerie->sendMessageToAdvertiser(utf8_encode($contenu), $cmd->idClient, $cmd->id);

              // Mise en spool de la commande pour demande de commentaire des produits
              if($cmd->statut_traitement == 20){
                $notation_test = ProductNotation::get('id_commande = '.$cmd->id);
                if(count($notation_test) < 1){
                  $notation_spool = new ProductNotationSpool();
                  $notation_spool_data = array(
                      'id_commande' => $cmd->id,
                      'insertion_timestamp' => time(),
                      'mail_sent' => 0
                  );
                  $notation_spool->setData($notation_spool_data);
                  $notation_spool->save();
                }
              }
              
              // ClicProtect : assurance de la commande. Envoi par curl d'un xml si statut = en cours de traitement ou partiellement annulée ou annulée (1 ou 0)
              if ($cmd->insurance > 0 && ($cmd->statut_traitement == 20 || $cmd->statut_traitement == 90 || $cmd->statut_traitement == 99)) {
                $siteid = 1053;
                $token = "N7dX4VKMGQ";
                $date = date("Y-m-d\TH:i:sP");
                $ch = curl_init("http://www.clicprotect.pro/ws/insure.php");
                $curl_input = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
                  "<cart>\n".
                  "  <siteID>".$siteid."</siteID>\n".
                  "  <datestamp>".$date."</datestamp>\n".
                  "  <token>".md5($siteid."|".$token."|".$date)."</token>\n".
                  "  <insurancestatus>".($cmd->statut_traitement == 20 ? "1" : "0")."</insurancestatus>\n".
                  "  <orderID>".$cmd->id."</orderID>\n".
                  "  <orderdate>".date("Y-m-d",$cmd->create_time)."</orderdate>\n".
                  "  <shippingdate>".($cmd->dispatch_time ? date("Y-m-d",$cmd->dispatch_time) : "")."</shippingdate>\n".
                  "  <amount>".round($cmd->totalTTC*100)."</amount>\n".
                  "  <email><![CDATA[".$customer->login."]]></email>\n".
                  "  <lastname><![CDATA[".$cmd->coord["nom"]."]]></lastname>\n".
                  "  <firstname><![CDATA[".$cmd->coord["prenom"]."]]></firstname>\n".
                  "  <company><![CDATA[".$cmd->coord["societe"]."]]></company>\n".
                  "  <address><![CDATA[".$cmd->coord["adresse"]."]]></address>\n".
                  "  <zipcode><![CDATA[".$cmd->coord["cp"]."]]></zipcode>\n".
                  "  <city><![CDATA[".$cmd->coord["ville"]."]]></city>\n".
                  "  <country>"."FR"."</country>\n".
                  "  <phone><![CDATA[".$cmd->coord["tel1"]."]]></phone>\n".
                  "  <cellphone>".""."</cellphone>\n".
                  "  <orderurl>".""."</orderurl>\n".
                  "</cart>";
                mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$curl_input);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_input);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $curl_output = curl_exec($ch);      
                curl_close($ch);
                $xml = new DomDocument();
                $xml->loadXML($curl_output);
                $xPath = new DOMXPath($xml);
                $code = $xPath->query("/result/code")->item(0)->nodeValue;
                $msg = $xPath->query("/result/msg")->item(0)->nodeValue;
              }
              
              print "ProcessingStatusValue".__OUTPUTID_SEPARATOR__.$alterProcessingStatus.__OUTPUT_SEPARATOR__;
              if($plannedDeliveryDate)
                print "PlannedDeliveryDate".__OUTPUTID_SEPARATOR__.$plannedDeliveryDate.__OUTPUT_SEPARATOR__;
              if($partiallyCancelledReason)
                print "partiallyCancelledReason".__OUTPUTID_SEPARATOR__.$partiallyCancelledReason.__OUTPUT_SEPARATOR__;
              if($cancelReason)
                print "cancelReason".__OUTPUTID_SEPARATOR__.$cancelReason.__OUTPUT_SEPARATOR__;
              if($openSav)
                print "openSav".__OUTPUTID_SEPARATOR__.$openSav.__OUTPUT_SEPARATOR__;
              if($closeSav)
                print "closeSav".__OUTPUTID_SEPARATOR__.$closeSav.__OUTPUT_SEPARATOR__;
              if($dispatchComment)
                print "dispatchComment".__OUTPUTID_SEPARATOR__.$dispatchComment.__OUTPUT_SEPARATOR__;
              if($statutTimestamp)
                print "statutTimestamp".__OUTPUTID_SEPARATOR__.$statutTimestamp.__OUTPUT_SEPARATOR__;

              $copieCompta = ($cmd->statut_traitement == 90 || $cmd->statut_traitement == 99) ? "\r\nCc:comptabilite@techni-contact.com" : '';
              if ($sendEmail) {

                $template = $cmd->statut_traitement == 21 ? 'user-bo_orders-order_status_opensav' : "user-bo_orders-order_status_update";

                $mail = new Email(array(
                    "email" => $customer->login,
                    "subject" => "Suivi de votre commande ".$cmd->id,
                    "headers" => "From: Service achat Techni-Contact <achat@techni-contact.com>\r\nReply-To: Service achat Techni-Contact <achat@techni-contact.com>".$copieCompta."\r\n",
                    "template" => $template,
                    "data" => array(
                        "FO_URL" => URL,
                        "FO_ACCOUNT_URL" => COMPTE_URL."contact-form.html?id=".$cmd->id."&type=1",
                        "ORDER_ID" => $cmd->id,
                        "CUSTOMER_FIRSTNAME" => $customer->prenom,
                        "CUSTOMER_LASTNAME" => $customer->nom,
                        "ORDER_PROCESSING_STATUS" => getStatutTraitementGlobal($cmd->statut_traitement)." ".$cmd->planned_delivery_date.$cmd->cancel_reason.$cmd->partially_cancelled_reason.$cmd->open_sav.$cmd->close_sav.$cmd->dispatch_comment,
                        "PDF_RECLAMATION_URL" => URL.'media/reclamations-retours-tc.pdf'
                    )
                ));
                if ($mail->send())
                  print "sendEmail".__OUTPUTID_SEPARATOR__."Email envoyé avec succès".__OUTPUT_SEPARATOR__;
                else
                  print "sendEmail".__OUTPUTID_SEPARATOR__."Erreur lors de l'envoi de l'email".__OUTPUT_SEPARATOR__;
              }
            }
            if (isset($isValid['alterShippingFee'])) {
				$stotalTVA = $cmd->totalTVA - $cmd->fdpTVA; // totalTVA already includes previous fdp tva
                $cmd->fdpHT = round(floatval($alterShippingFee), 2);
				$cmd->fdp_tva = $cmd->getTVArate(__FPD_IDTVA_DFT__);
				$cmd->fdpTVA = round($cmd->fdpHT * $cmd->fdp_tva / 100, 2);
				$cmd->fdpTTC = $cmd->fdpHT + $cmd->fdpTVA;
                //pp($cmd);
                //exit();
				
				// Setting saved vars
				$cmd->totalHT = $cmd->stotalHT + $cmd->fdpHT;
                $cmd->totalTVA = $stotalTVA + $cmd->fdpTVA;
				$cmd->totalTTC = $cmd->totalHT + $cmd->totalTVA;
				
				print "Totals" . __OUTPUTID_SEPARATOR__ ;
				print "ShippingFee" . __DATA_SEPARATOR__ . sprintf("%.2f", $cmd->fdpHT) . __DATA_SEPARATOR__;
				print "TotalHT" . __DATA_SEPARATOR__ . sprintf("%.2f", $cmd->totalHT) . __DATA_SEPARATOR__;
				print "TotalTTC" . __DATA_SEPARATOR__ . sprintf("%.2f", $cmd->totalTTC) . __DATA_SEPARATOR__;
				print __OUTPUT_SEPARATOR__;
			}
			
			if (isset($isValid['newClient'])) {
				$cmd->idClient = $newClient;
				$nci = &$newClientInfos;
				
				$cmd->setCoordFromArray($newClientInfos);
				foreach($nci as &$ci) $ci = to_entities($ci);
				
				print "clientID_fixed" . __OUTPUTID_SEPARATOR__ . $newClient . __OUTPUT_SEPARATOR__;
				print "company_fixed" . __OUTPUTID_SEPARATOR__ . $nci['societe'] . __OUTPUT_SEPARATOR__;
				
				$fields = array('titre', 'nom', 'prenom', 'societe', 'adresse', 'complement', 'ville', 'cp', 'pays');
				$size = count($fields);
				
				
				print "BillingAddress" . __OUTPUTID_SEPARATOR__;
				$nci['titre'] = GetTitle($nci['titre']);
				for($i=0; $i < $size; $i++) print $fields[$i].'_fixed' . __DATA_SEPARATOR__ . $nci[$fields[$i]] . __DATA_SEPARATOR__;
				print "societe_br" . __DATA_SEPARATOR__ . ($nci['societe'] != '' ? '<br />' : '') . __DATA_SEPARATOR__;
				print "email_fixed" . __DATA_SEPARATOR__ . $nci['email'] . __DATA_SEPARATOR__;
				print "tel1_fixed" . __DATA_SEPARATOR__ . $nci['tel1'] . __DATA_SEPARATOR__;
				print "fax1_fixed" . __DATA_SEPARATOR__ . $nci['fax1'] . __DATA_SEPARATOR__;
				print __OUTPUT_SEPARATOR__;
				
				print "ShipAddress" . __OUTPUTID_SEPARATOR__;
				print "coord_livraison" . __DATA_SEPARATOR__ . $nci['coord_livraison'] . __DATA_SEPARATOR__;
				$_l = $nci['coord_livraison'] == 1 ? '_l' : '';
				$nci['titre'.$_l] = GetTitle($nci['titre'.$_l]);
				for($i=0; $i < $size; $i++) print $fields[$i].'_l_fixed' . __DATA_SEPARATOR__ . $nci[$fields[$i].$_l] . __DATA_SEPARATOR__;
				print "societe_l_br" . __DATA_SEPARATOR__ . ($nci['societe'.$_l] != '' ? '<br />' : '') . __DATA_SEPARATOR__;
				print __OUTPUT_SEPARATOR__;
				
			}
			elseif (isset($isValid['alterShipAddress'])) {
				$fields = array('titre', 'nom', 'prenom', 'societe', 'adresse', 'complement', 'ville', 'cp', 'pays', 'infos_sup', 'tel1');
				$fields_l = array('titre_l', 'nom_l', 'prenom_l', 'societe_l', 'adresse_l', 'complement_l', 'ville_l', 'cp_l', 'pays_l', 'infos_sup_l', 'tel2');
				$size = count($fields);
                                
				print "ShipAddress" . __OUTPUTID_SEPARATOR__;
				print "coord_livraison" . __DATA_SEPARATOR__ . $tab_coord['coord_livraison'] . __DATA_SEPARATOR__;
				$cmd->coord['coord_livraison'] = $tab_coord['coord_livraison'];
				if ($tab_coord['coord_livraison'] == 1)
					for($i=0; $i < $size; $i++) $cmd->coord[$fields_l[$i]] = $tab_coord[$fields_l[$i]];
				else
					for($i=0; $i < $size; $i++) $cmd->coord[$fields_l[$i]] = $cmd->coord[$fields[$i]];
				
				$cmd->coord['titre_l'] = GetTitle($cmd->coord['titre_l']);
				for($i=0; $i < $size; $i++)	print $fields_l[$i].'_fixed' . __DATA_SEPARATOR__ . to_entities($cmd->coord[$fields_l[$i]]) . __DATA_SEPARATOR__;
				$cmd->coord['titre_l'] = GetTitleNum($cmd->coord['titre_l']);
				
				print "societe_l_br" . __DATA_SEPARATOR__ . ($cmd->coord['societe_l'] != '' ? '<br />' : '') . __DATA_SEPARATOR__;
				print __OUTPUT_SEPARATOR__;
			}
			
			if (isset($isValid['UpdatePdtsQties']) && !empty($isValid['UpdatePdtsQties'])) {
				foreach($isValid["UpdatePdtsQties"] as $idTC => $qty) {
					$cmd->UpdateProductQuantity($idTC, $qty);
				}
				print "UpdatePdtsQties" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
			}
			if (isset($isValid['addProduct'])) {
				list($idProduct, $idTC, $quantity) = explode('-', $addProduct);
//				$cmd->AddProduct($idProduct, $idTC, $quantity); correction 14/04/2011
                                $cmd->AddProduct($idTC, $quantity);

                                if($addMultipleProducts)
                                  print "AddMultipleProducts" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
                                else
                                  print "AddProduct" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
			}
			if (isset($isValid['delProduct'])) {
				list($idProduct, $idTC) = explode('-', $delProduct);
				$cmd->DelProduct( $idTC);
				print "DelProduct" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
			}
			if (isset($isValid['addProduct']) || isset($isValid['delProduct'])) {
			}
                        
			$cmd->save();
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