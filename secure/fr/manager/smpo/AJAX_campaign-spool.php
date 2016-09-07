<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 23 mars 2011

 Fichier : /secure/fr/manager/smpo/AJAX_calls-liste.php
 Description : résultat ajax du listing de la pile d'appels

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);
  exit();
}

$userPerms = $user->get_permissions();

// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];

if (!$userPerms->has($fntByName["m-smpo--sm-campaign"], "re")){
  $o["error"] = "Vous n'avez pas les droits nécessaires";
  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);
  exit();
}

$db = DBHandle::get_instance();
$o = array();

define('NB', 10);

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $_GET);

$idCampaign = isset($_GET['idCampaign']) ? trim($_GET['idCampaign']) : '';
$unanswered = isset($_GET['unanswered']) ? trim($_GET['unanswered']) : '';
$uncalled   = isset($_GET['uncalled'])   ? trim($_GET['uncalled']) : '';

// ADD LE 15-07-2015
$fonction         = isset($_GET['fonction'])         ? trim($_GET['fonction']) : '';
$salaries         = isset($_GET['salaries'])         ? trim($_GET['salaries']) : '';
$secteur          = isset($_GET['secteur'])          ? trim($_GET['secteur']) : '';
$shown_call_count = isset($_GET['shown-call-count']) ? (int)($_GET['shown-call-count']) : '';

$queryFilter_add = array();
if (!empty($fonction)) {
  switch (strtolower(utf8_decode($fonction))) {
    case 'direction générale':
    case 'service achats / services généraux':
    case 'service administratif et financier':
    case 'service commercial':
    case 'service communication / marketing':
    case 'service informatique':
    case 'service logistique / production':
    case 'service maintenance / sécurité':
    case 'service ressources humaines':
    case 'autres services':
    case 'administration':
      $queryFilter_add[] = "co.fonction like '%".$db->escape($fonction)."%'";
      break;
    default :
      $queryFilter_add[] = "co.fonction = '".$db->escape($fonction)."'";
      break;
  }
}

if (!empty($salaries)) {
	$salaries  == 'de 200 salariés' ? '+ de 200 salariés' : $salaries;
	//$salaries = utf8_decode($salaries) == 'de 200 salariés' ? '+ de 200 salariés' : utf8_decode($salaries);
	$queryFilter_add[] = "co.nb_salarie = '".$db->escape($salaries)."'";
}

if (!empty($secteur)) {
	$queryFilter_add[] = "co.secteur_activite = '".$db->escape($secteur)."'";
}


$campaign = new Campaign($idCampaign);
if (!$campaign->exists) {
  $o["error"] = "Campagne inexistante";
  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);
  exit();
}

if (time()%300+150 < 160) // launch every 5 mn for 10s (150s later than call spool
  $db->query("UPDATE campaigns_spool SET timestamp_in_line = 0 WHERE timestamp_in_line BETWEEN 1 AND UNIX_TIMESTAMP()-3600", __FILE__, __LINE__);

$queryFilter = array();
if ($unanswered)
  $queryFilter[] = "cs.call_result = 'absence'";
if ($uncalled)
  $queryFilter[] = "cs.call_result = 'not_called'";

$query = "
  SELECT
    cs.id,
    cs.id_client,
    cs.timestamp,
    cs.timestamp_calls
  FROM campaigns_spool cs
  LEFT JOIN clients co ON co.login = cs.id_client
  WHERE
	cs.timestamp_in_line = 0 AND
    id_campaign = '".$idCampaign."'AND
    calls_count < ".CallsCampaign::$maxCallsInAbsence." AND
    call_result != 'call_ok' AND
    call_result != 'call_ok_no_lead' AND
    call_result != 'customer_calls_back'
    ".(!empty($queryFilter) ? " AND ".implode(" AND ",$queryFilter) : "")."
	".(!empty($queryFilter_add) ? " AND ".implode(" AND ",$queryFilter_add) : "")."
  GROUP BY cs.id
  ORDER BY cs.timestamp ASC
  LIMIT 0, $shown_call_count";


  
 //echo $query;
 
 
$res = $db->query($query, __FILE__, __LINE__);
if( $db->numrows($res, __FILE__, __LINE__) == 0) {
  $o['reponses'] = 'vide';
}
else {
  while ($reponse = $db->fetchAssoc($res, __FILE__, __LINE__)) {
    $timestampCalls = mb_unserialize($reponse['timestamp_calls']);
    $reponse['nbrDailyCalls'] = 0;
    foreach ($timestampCalls as $timestampCall) {
      if ($timestampCall >= mktime(0, 0, 0) && $timestampCall <= mktime(23, 59, 59))
        $reponse['nbrDailyCalls']++;
    }
    $reponse['nbrCallsInAbsence'] = count($timestampCalls);

  	  
	 $res2 = $db->query("
      SELECT
        co.id AS id_lead,
        co.nom,
        co.prenom,
        co.societe,
        co.fonction,
        co.societe,
        co.salaries,
        co.secteur,
        co.tel,
        co.timestamp AS dateLead,
        pfr.name AS product_name
      FROM contacts co
      LEFT JOIN products_fr pfr ON pfr.id = co.idProduct
      WHERE co.email = '".$db->escape($reponse['id_client'])."'
      ORDER BY dateLead DESC
      LIMIT 0,1", __FILE__, __LINE__);
   
    if ($db->numrows($res2) == 1) {
      $lead = $db->fetchAssoc($res2);

      $rdvLead = Rdv::get('id_relation = '.$lead['id_lead'], 'type_relation = 1', 'active = 1');

      if(count($rdvLead) != 0)
        $reponse['rdvExists'] = true;

    }
    else {
      $lead = array_fill_keys(array("id_lead", "dateLead", "nom", "prenom", "societe","societe","fonction","salaries","secteur","tel", "product_name"), "");
      $reponse["prenom"] = "Lead introuvable";
    }

    $reponse = array_merge($reponse, $lead);
    $o['reponses'][] = $reponse;
  }
}

// campaign datas
//def du taux de joignabilité :
//Abouti sans lead + abouti avec lead + recontact direct / Abouti sans lead + avec lead + recontact direct + en absence)
$nbrCalls = $campaign->getNbrCalls();
if ($nbrCalls > 0) {
  $nbrEffectiveCalls = $campaign->getNbrEffectiveCalls();
  $nbrCallsMade = $campaign->getNbrCallsMade();
  $callsMadeRate = $nbrEffectiveCalls != 0 ? ($nbrCallsMade/$nbrEffectiveCalls)*100 : 0;
  $nbLeadsMade = $campaign->getNbrLeadsMade();
  $leadsMadeRate = $nbrEffectiveCalls != 0 ? ($nbLeadsMade/$nbrCallsMade)*100 : 0;
  $reponse['nbCallsTotal'] = $campaign->getNbrCalls();
  $reponse['nbCallsToDo'] = $campaign->getNbrCallsToDo();
  $reponse['callsMadeRate'] = sprintf("%.02f",$callsMadeRate);
  $reponse['nbCallsMade'] = $nbrCallsMade;
  $reponse['nbLeadsMade'] = $nbLeadsMade;
  $reponse['leadsMadeRate'] = sprintf("%.02f",$leadsMadeRate);
  $reponse['status_title'] = $campaign->getStatus()->title;
  $reponse['status_value'] = $campaign->getStatus()->value;
  $reponse['nbCallsInAbsence'] = $campaign->getNbrCallsInAbsence();
}
else {
  $callsMadeRate = 0;
  $reponse = array_merge($reponse, array_fill_keys(array(
    "nbCallsTotal",
    "nbCallsToDo",
    "callsMadeRate",
    "nbCallsMade",
    "nbLeadsMade",
    "leadsMadeRate",
    "status_title",
    "status_value",
    "nbCallsInAbsence"), 0)
  );
  $reponse['error'] = 'Cette campagne ne contient aucun appel';
}

$opStats = array();
$operators = $campaign->getOperators();

//"Contact transformés" doit prendre en compte uniquement les "Aboutis avec lead" (prend aussi en compte actuellement les "Aboutis sans lead"
//  Taux de joignabilité = Nombre de contact aboutis avec lead / Nombre de contact aboutis avec lead + Nombre de contact aboutis sans lead
foreach ($operators as $operator) {
  $nbrCallsMadeByOp = $campaign->getNbrCallsMadeByOperator($operator['id']);
  $nbLeadsMadeByOp = $nbrCallsMadeByOp != 0 ? $campaign->getNbrLeadsMadeByOperator($operator['id']) : '0';
  $nbCallsOkByOp = $nbrCallsMadeByOp != 0 ? $campaign->getNbrCallsOkByOperator($operator['id']) : '0';
  $leadsMadeRateByOp = $nbrCallsMadeByOp != 0 ? (($nbLeadsMadeByOp/$nbCallsOkByOp)*100).' %' : 'N/C';
  $opStat['operatorName'] = $operator['name'];
  $opStat['nbCallsMadeByOp'] = $nbrCallsMadeByOp;
  $opStat['nbLeadsMadeByOp'] = $nbLeadsMadeByOp;
  $opStat['leadsMadeRateByOp'] = sprintf("%.02f",$leadsMadeRateByOp);
  $opStats[] = $opStat;
}

$o['opStats'] = $opStats;
$o['dataCampaign'][] = $reponse;

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
?>