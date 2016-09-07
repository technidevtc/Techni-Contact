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
require(ICLASS . "CCustomerUser.php");
require(ADMIN  . 'customers.php');
require(ADMIN  . 'statut.php');
require(ADMIN  . 'tva.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login()) {
	header('Location: ' . ADMIN_URL . 'login.html');
	exit();
}

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

if (!$user->get_permissions()->has("m-comm--sm-estimates","e")) {
  print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
  exit();
}

$sid = session_name() . '=' . session_id();

function GetTitle($val) {
	switch ($val) {
		case 1  : return 'M.';
		case 2  : return 'Mlle';
		case 3  : return 'Mme';
		default : return 'M.';
	}
}

function GetTitleNum($val) {
	switch ($val) {
		case 'M.'   : return 1;
		case 'Mlle' : return 2;
		case 'Mme'  : return 3;
		default : return 1;
	}
}

$estimateID = isset($_GET['estimateID']) ? $_GET['estimateID'] : '';

$errorstring = '';

if (preg_match('/^[0-9a-v]{26,32}$/', $estimateID)) {
	
	//header("content-type: text/css; charset=iso-8859-1");
	$newClient = isset($_GET['newClient']) ? $_GET['newClient'] : '';
	$addProduct = isset($_GET['addProduct']) ? trim($_GET['addProduct']) : '';
	$delProduct = isset($_GET['delProduct']) ? trim($_GET['delProduct']) : '';
	$UpdatePdtsQties = isset($_GET['UpdatePdtsQties']) ? trim($_GET['UpdatePdtsQties']) : '';
	$addDiscount = isset($_GET['addDiscount']) ? trim($_GET['addDiscount']) : '';
	$delDiscount = isset($_GET['delDiscount']) ? trim($_GET['delDiscount']) : '';
	
	$isValid = array();
	
	// On vérifie que le client est valide
	if (!empty($newClient)) {
		if (preg_match('/^\d+$/', $newClient)) {
			$user = new CustomerUser($handle, $newClient);
			$newClientInfos = $user->getCoordFromArray();
			$isValid['newClient'] = true;
		}
		else $errorstring .= "NewClientError" . __ERRORID_SEPARATOR__ . "L'identifiant client est invalide" . __ERROR_SEPARATOR__;
	}
	
	if (!empty($UpdatePdtsQties)) {
		$listpdt = explode('_', $UpdatePdtsQties);
		$nbpdt = count($listpdt)-1;
		$isValid['UpdatePdtsQties'] = array();
		$errorstring_q = '';
		for ($i = 0; $i < $nbpdt; $i++) {
			list($idProduct, $idTC, $quantity) = explode('-', $listpdt[$i]);
			if (isset($idProduct) && isset($quantity) && isset($idTC)) {
				if (preg_match('/^[1-9]{1}[0-9]{1,7}$/', $idProduct)) {
					if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC)) {
						if (preg_match('/^[0-9]+$/',$quantity)) {
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
	if (!empty($addProduct)) {
		list($idProduct, $idTC, $quantity) = explode('-', $addProduct);
		
		if (isset($idProduct) && isset($quantity) && isset($idTC)) {
			if (preg_match('/^[1-9]{1}[0-9]{1,7}$/', $idProduct)) {
				if (preg_match('/^[0-9]+$/',$quantity)) {
					if (empty($idTC)) {
						if(($result = & $handle->query("select p.price, p.idTC from products_fr pfr, products p, advertisers a where p.id = $idProduct and p.id = pfr.id and p.idAdvertiser = a.id and a.actif = 1 and a.parent = 61049", __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1) {
							$rec = & $handle->fetch($result);
							if (preg_match('/^[0-9]+((\.|,)[0-9]{0,2})?$/', $rec[0])) $isValid['addProduct'] = $idProduct . '-' . $rec[1];
							else {
								if ($rec[0] == 'ref') $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Ce produit a des références. Veuillez choisir l'une d'elle pour ajouter ce produit au devis" . __ERROR_SEPARATOR__;
								else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Ce produit n'a pas de prix et n'est donc pas disponible à la vente" . __ERROR_SEPARATOR__;
							}
						}
						else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Le produit ayant pour numéro identifiant $idProduct n'existe pas" . __ERROR_SEPARATOR__;
					}
					elseif (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC)) {
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
	if (!empty($delProduct)) {
		$idTC = $delProduct;
		
		if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $idTC)) {
			$isValid['delProduct'] = $idTC;
		}
		else $errorstring .= "ProductsError" . __ERRORID_SEPARATOR__ . "Le numéro identifiant TC du produit à supprimer est invalide" . __ERROR_SEPARATOR__;
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
		$cart = new Cart($handle, $estimateID);
		
		if (!$cart->existsInDB) {
			$errorstring .= '- Le devis ayant pour numéro identifiant ' . $cart->id . " n'existe pas<br />\n";
		}
		else {
			$outputstring = '';
			
			if (isset($isValid['newClient'])) {
				$cart->idClient = $newClient;
				
				print "clientID_fixed" . __OUTPUTID_SEPARATOR__ . $newClient . __OUTPUT_SEPARATOR__;
				print "company_fixed" . __OUTPUTID_SEPARATOR__ . $newClientInfos['societe'] . __OUTPUT_SEPARATOR__;
			}
			
			if (isset($isValid['UpdatePdtsQties']) && !empty($isValid['UpdatePdtsQties'])) {
				foreach($isValid["UpdatePdtsQties"] as $idTC => $qty) {
					$cart->UpdateProductQuantity($idTC, $qty);
				}
				print "UpdatePdtsQties" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
			}
			if (isset($isValid['addProduct'])) {
				list($idProduct, $idTC, $quantity) = explode('-', $addProduct);
				$cart->AddProduct($idProduct, $idTC, null, $quantity);
				print "AddProduct" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
			}
			if (isset($isValid['delProduct'])) {
				$cart->DelProduct($delProduct);
				print "DelProduct" . __OUTPUTID_SEPARATOR__ . __OUTPUT_SEPARATOR__;
			}
			if (isset($isValid['addProduct']) || isset($isValid['delProduct'])) {
			}
			
			$cart->calculateCart();
			$cart->save();
		}
	}
	else {
		//$errorstring .= "<b>Aucun changement n'a été effectuée sur cette commande car aucune des données soumises n'était valide</b><br />\n";
	}
}
else {
	$errorstring .= "- Le numéro d'identifiant du devis est invalide<br />\n";
}

?>