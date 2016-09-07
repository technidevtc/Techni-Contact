<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 3 avril 2006
 
Mises à jour :

 Fichier : /includes/managerV2/tva.php
 Description : Fonction manipulation taux de TVA

/=================================================================*/

$TypePaiementList = array();
$TypePaiementList[0] = "Carte Bancaire (type en attente)";
$TypePaiementList[1] = "Carte Bancaire (Carte Bleue)";
$TypePaiementList[2] = "Carte Bancaire (Visa)";
$TypePaiementList[3] = "Carte Bancaire (Mastercard)";
$TypePaiementList[4] = "Carte Bancaire (American Express)";
$TypePaiementList[5] = "Paypal";
$TypePaiementList[10] = "Chèque";
$TypePaiementList[20] = "Virement bancaire";
$TypePaiementList[30] = "Paiement différé";
$TypePaiementList[40] = "Contre-remboursement";
$TypePaiementList[50] = "Mandat administratif";
//$TypePaiementList[60] = "Paypal";
/*$TypePaiementList[0] = "Carte Bancaire (type en attente)";
$TypePaiementList[1] = "Chèque";
$TypePaiementList[2] = "Virement bancaire";
$TypePaiementList[3] = "Paiement différé";
$TypePaiementList[4] = "Contre-remboursement";
$TypePaiementList[5] = "Mandat administratif";*/

$statutPaiementList = array();
$statutPaiementList[ 0] = "Attente confirmation BNP";
$statutPaiementList[ 1] = "Attente chèque";
$statutPaiementList[ 2] = "Attente virement";
$statutPaiementList[ 3] = "Paiement différé à valider";
$statutPaiementList[ 4] = "Paiement par contre-remboursement à valider";
$statutPaiementList[ 5] = "Paiement par mandat administratif à valider";
//$statutPaiementList[ 6] = "Paiement par Paypal à valider";
$statutPaiementList[10] = "Payé";
$statutPaiementList[11] = "Paiement différé validé";

$statutTraitementList = array();
$statutTraitementList[ 0] = "Attente validation paiement";
$statutTraitementList[10] = "Commande reçue non consultée";
$statutTraitementList[20] = "Commande en cours de traitement";
$statutTraitementList[30] = "Commande expédiée";

$statutTraitementGlobalList = array();
$statutTraitementGlobalList[ 0] = "Attente validation paiement";
$statutTraitementGlobalList[10] = "Commande en attente de traitement";
$statutTraitementGlobalList[20] = "Commande en cours de traitement";
$statutTraitementGlobalList[21] = "SAV ouvert :";
$statutTraitementGlobalList[22] = "SAV résolu :";
$statutTraitementGlobalList[25] = "Date d'expédition prévisionnelle :";
$statutTraitementGlobalList[30] = "Commande partiellement expédiée";
$statutTraitementGlobalList[40] = "Commande expédiée";
$statutTraitementGlobalList[90] = "Commande partiellement annulée :";
$statutTraitementGlobalList[99] = "Commande annulée :";


$TypeCommande = array();
$TypeCommande[0] = "Internet";
$TypeCommande[1] = "Devis";
$TypeCommande[2] = "Tel";
$TypeCommande[3] = "Fax";
$TypeCommande[4] = "Courrier";


/*
function getTypePaiement($type)
{
	switch ($type)
	{
		case  0: $typestring = "Carte Bancaire (Carte Bleue)"; break;
		case  1: $typestring = "Carte Bancaire (Visa)"; break;
		case  2: $typestring = "Carte Bancaire (American Express)"; break;
		case  3: $typestring = "Carte Bancaire (Mastercard)"; break;
		case  4: $typestring = "Chèque"; break;
		case  5: $typestring = "Virement bancaire"; break;
		case  6: $typestring = "Paiement différé"; break;
		case  7: $typestring = "Contre-remboursement"; break;
		case  8: $typestring = "Mandat administratif"; break;
		default: $typestring = "";
	}
	return $typestring;
}
*/

function getTypePaiement($type)
{
	global $TypePaiementList;
	if (isset($TypePaiementList[$type])) return $TypePaiementList[$type];
	else return "";
}

function getStatutPaiement($statut)
{
	global $statutPaiementList;
	if (isset($statutPaiementList[$statut])) return $statutPaiementList[$statut];
	else return "";
}

function & getStatutPaiementList()
{
	global $statutPaiementList;
	return $statutPaiementList;
}

function getStatutTraitement($statut)
{
	global $statutTraitementList;
	if (isset($statutTraitementList[$statut])) return $statutTraitementList[$statut];
	else return "";
}

function getStatutTraitementGlobal($statut)
{
	global $statutTraitementGlobalList;
	if (isset($statutTraitementGlobalList[$statut])) return $statutTraitementGlobalList[$statut];
	else return "";
}

function & getStatutTraitementList()
{
	global $statutTraitementList;
	return $statutTraitementList;
}

function getTypeCommande($type)
{
	global $TypeCommande;
	if (isset($TypeCommande[$type])) return $TypeCommande[$type];
	else return "";
}

function & getTypeCommandeList()
{
	global $commandeType;
	return $commandeType;
}

function & getCreditMonth($lead = array())
{
  require(SECURE_PATH.'extranet/fr_local.php');
  $listMonth = array(
	1 => COMMON_JANUARY,
	2 => COMMON_FEBRUARY,
	3 => COMMON_MARCH,
	4 => COMMON_APRIL,
	5 => COMMON_MAY,
	6 => COMMON_JUNE,
	7 => COMMON_JULY,
	8 => COMMON_AUGUST,
	9 => COMMON_SEPTEMBER,
	10 => COMMON_OCTOBER,
	11 => COMMON_NOVEMBER,
	12 => COMMON_DECEMBER
);
  $return = '';
  if( !empty ($lead['credited_on']) && $lead['invoice_status'] == __LEAD_INVOICE_STATUS_CREDITED__){

    $month = $listMonth[date("n", $lead['credited_on'])];
    $return = $month;
  }
return $return;
  
}
?>
