<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 16 juin 2011

 Fichier : /secure/fr/manager/reporting/AJAX_call-chat.php
 Description : résultat du reporting call/chat

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirÃ©, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

if (!$user->get_permissions()->has("m-reporting--sm-call-chat","r")) {
  $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération";
  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);
    exit();
  }

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");

$o = array();

$NB = !empty($_GET['NB']) && is_numeric($_GET['NB']) ? $_GET['NB'] : 30;
define('NB', $NB);
define("__BEGIN_TIME__", mktime(0,0,0,6,15,2011));

// time interval
$yearS = isset($_GET['yearS']) ? (int)trim($_GET['yearS']) : date("Y");
$monthS = isset($_GET['monthS']) ? (int)trim($_GET['monthS']) : date("m");
$dayS = isset($_GET['dayS']) ? (int)trim($_GET['dayS']) : date("d");
$yearS2 = isset($_GET['yearS2']) ? (int)trim($_GET['yearS2']) : date("Y");
$monthS2 = isset($_GET['monthS2']) ? (int)trim($_GET['monthS2']) : date("m");
$dayS2 = isset($_GET['dayS2']) ? (int)trim($_GET['dayS2']) : date("d");
$yearE = isset($_GET['yearE']) ? (int)trim($_GET['yearE']) : date("Y");
$monthE = isset($_GET['monthE']) ? (int)trim($_GET['monthE']) : date("m");
$dayE = isset($_GET['dayE']) ? (int)trim($_GET['dayE']) : date("d");


if (isset($_GET['yearS'])) {
//	$dateFilterType = "simple";
	$yearS	= (int)trim($_GET['yearS']);
	$monthS	= isset($_GET['monthS'])	? (int)trim($_GET['monthS']) : 0;
	$dayS	= isset($_GET['dayS'])		? (int)trim($_GET['dayS']) : 0;

	if (isset($_GET['yearE'])) {
//		$dateFilterType = "interval";
		$yearE	= (int)trim($_GET['yearE']);
		$monthE	= isset($_GET['monthE'])	? (int)trim($_GET['monthE']) : 0;
		$dayE	= isset($_GET['dayE'])		? (int)trim($_GET['dayE']) : 0;
	}
}

$page	= isset($_GET['page'])	? (int)trim($_GET['page']) : 1; if ($page < 1) $page = 1;
$sort     = isset($_GET['sort'])     ? trim($_GET['sort']) : '';
$lastsort = isset($_GET['lastsort']) ? trim($_GET['lastsort']) : '';
$sortway  = isset($_GET['sortway'])  ? trim($_GET['sortway']) : '';
$findText  = isset($_GET['findText'])  ? trim($_GET['findText']) : '';
$findType  = isset($_GET['findType'])  ? trim($_GET['findType']) : '';
$status  = isset($_GET['filter_status'])  ? trim($_GET['filter_status']) : '';

$queryFilter = array();
$dateFilterType = isset($_GET["dateFilterType"]) ? ($_GET["dateFilterType"] == "interval" ? "interval" : "simple") : "simple";
if ($dateFilterType == "simple") {
	if ($yearS != 0) {
		if ($monthS == 0)	{ $dateStart = mktime(0,0,0,      1,    1,$yearS);	$dateEnd = mktime(0,0,0,        1,      1,$yearS+1); }
		elseif ($dayS == 0)	{ $dateStart = mktime(0,0,0,$monthS,    1,$yearS);	$dateEnd = mktime(0,0,0,$monthS+1,      1,$yearS  ); }
		else				{ $dateStart = mktime(0,0,0,$monthS,$dayS,$yearS);	$dateEnd = mktime(0,0,0,$monthS  ,$dayS+1,$yearS  ); }
	}
}
elseif ($dateFilterType == "interval") {
	if ($yearS2 != 0 && $yearE != 0) {
		if ($monthS2 == 0)   { $dateStart = mktime(0,0,0,       1,     1,$yearS2); }
		elseif ($dayS2 == 0) { $dateStart = mktime(0,0,0,$monthS2,     1,$yearS2); }
		else                 { $dateStart = mktime(0,0,0,$monthS2,$dayS2,$yearS2); }

		if ($monthE == 0)   { $dateEnd = mktime(0,0,0,      1,      1,$yearE); }
		elseif ($dayE == 0) { $dateEnd = mktime(0,0,0,$monthE,      1,$yearE); }
		else                { $dateEnd = mktime(0,0,0,$monthE,$dayE+1,$yearE); }
	}
}
if (!isset($dateStart) || !isset($dateEnd)) {
	$dateStart = __BEGIN_TIME__;
	$dateEnd = time() + 86400 - (time() % 86400);
}
// dates for tests
//$dateStart = 1296050725;
//$dateEnd = 1305800128;

$queryFilter = array("timestamp >= ".$dateStart, "timestamp < ".$dateEnd, "origin != ''" , "origin != 'Internaute'", "origin != 'Probance'", "origin != 'Chat'");

$errorstring = '';

$leads = new Lead();

$leadsCollection = $leads->get($queryFilter);

foreach ($leadsCollection as $lead){

  $googledLeadOrigin = Utils::toDashAz09($lead['origin']);
  if(($lead['invoice_status'] & __LEAD_CHARGED__ || $lead['invoice_status'] & __LEAD_CHARGEABLE__) && ($lead['reject_timestamp'] == 0 || $lead['credited_on'] == 0) && $lead['parent'] == 0)
    $leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['ca_leads'] += $lead['income'] != 0 ? $lead['income'] : $lead['income_total'];

//  Rajouter 1 colonne : Nb leads T
//--> Comprend le nombre leads au total généré par l'opérateur = Leads primaire + secondaire
//  Taux rejet = Nb lead rejetés / (Nb leads Facturable + facturé + rejetés + avoir + en attente de validation rejet)
if($lead['invoice_status'] & __LEAD_CHARGED__ || $lead['invoice_status'] & __LEAD_CHARGEABLE__ || $lead['invoice_status'] & __LEAD_REJECTED__ || $lead['invoice_status'] & __LEAD_REJECTABLE__ || $lead['credited_on'] != 0)
  $leadsByUsersThenOrigin[$lead['id_user']]['denominateurTxRejet']++;

$leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_total']++;

//Rajouter un colonne : Tx rejet
//Indique pour l'opérateur son taux de rejet = Nb issus de l'opérateur sur la période qui ont été rejetés / Nb leads T de l'opérateur sur la période
//hello toujours petit soucis avec le taux de rejet dans tableau call / chat
//il faudrait qu'il soit = nb lead rejeté + avoir+ en attente de rejet / Nb lead facturé + facturable + rejeté + avoir+ en attente de rejet
//  var_dump($lead['invoice_status'], $lead['invoice_status'] & __LEAD_REJECTED__, $lead['invoice_status'] & __LEAD_INVOICE_STATUS_REJECTED_WAIT__, $lead['invoice_status'] & __LEAD_INVOICE_STATUS_CREDITED__);
if(($lead['invoice_status'] & __LEAD_REJECTED__ || $lead['invoice_status'] == __LEAD_INVOICE_STATUS_REJECTED_WAIT__  || $lead['invoice_status'] == __LEAD_INVOICE_STATUS_CHARGEABLE_REJECTED_WAIT__  || $lead['invoice_status'] & __LEAD_CREDITED__))
    $leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_rejected']++;
  
  $currUser = BOUser::get('id = '.$lead['id_user']);
  $leadsByUsersThenOrigin[$lead['id_user']]['name'] = $currUser[0]['name'];
  if($lead['parent'] == 0)
    $leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['nb_leads']++;
  else
    $leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['ca_leads'] += $lead['income'];

  $leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_total'] = !empty ($leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_total']) ? $leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_total'] : 0;
  $leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_rejected'] = !empty ($leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_rejected']) ? $leadsByUsersThenOrigin[$lead['id_user']]['nb_leads_rejected'] : 0;
//  $leadsByUsersThenOrigin[$lead['id_user']]['denominateurTxRejet'] = $denominateurTxRejet;
  $total[$googledLeadOrigin][$lead['id_user']]['nb_leads'] = $leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['nb_leads'] = !empty ($leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['nb_leads']) ? $leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['nb_leads'] : 0;
  $total[$googledLeadOrigin][$lead['id_user']]['ca_leads'] = $leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['ca_leads'] = !empty ($leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['ca_leads']) ? $leadsByUsersThenOrigin[$lead['id_user']][$googledLeadOrigin]['ca_leads'] : 0;

}
//$TotalDenominateurTxRejet = 0;
if(!empty ($total))
  foreach($total as $k => $v)
    foreach($v as $user){
        $total2[$k]['nb_leads'] += $user['nb_leads'];
        $total2[$k]['ca_leads'] += $user['ca_leads'];
      }
      
if(!empty ($total2)){
  $leadsByUsersThenOrigin['total_users'] = $total2;
  $leadsByUsersThenOrigin['total_users']['name'] = 'TOTAL';
}

if(!$errorstring){

  if(empty ($leadsByUsersThenOrigin))
    $o['reponses'] = 'vide';
  else
    $o['reponses'] = $leadsByUsersThenOrigin;

}else
  $o['error'] = $errorstring;

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>
