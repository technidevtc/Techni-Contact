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
  $o["error"] = "Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page";
  mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
  print json_encode($o);
  exit();
}

$o = array();

$userPerms = $user->get_permissions();

// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];

if (!$userPerms->has($fntByName["m-smpo--sm-call-list"], "re")){
  $o["error"] = "Vous n'avez pas les droits nécessaires";
  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);
  exit();
}

$db = DBHandle::get_instance();

define('DAILY_NB_UNANSWERED_CALLS', 3); // defines the number of unanswered call per day

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $_GET);

$fonction         = isset($_GET['fonction'])         ? trim($_GET['fonction']) : '';
$salaries         = isset($_GET['salaries'])         ? trim($_GET['salaries']) : '';
$secteur          = isset($_GET['secteur'])          ? trim($_GET['secteur']) : '';
$placement_time   = isset($_GET['placement-time'])   ? trim($_GET['placement-time']) : '';
$unanswered       = isset($_GET['unanswered'])       ? trim($_GET['unanswered']) : '';
$shown_call_count = isset($_GET['shown-call-count']) ? (int)($_GET['shown-call-count']) : '';

$queryFilter = array();

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
      $queryFilter[] = "co.fonction like '%".$db->escape($fonction)."%'";
      break;
    default :
      $queryFilter[] = "co.fonction = '".$db->escape($fonction)."'";
      break;
  }
}

if (!empty($salaries)) {
  $salaries = utf8_decode($salaries) == 'de 200 salariés' ? '+ de 200 salariés' : utf8_decode($salaries);
  $queryFilter[] = "co.salaries = '".$db->escape($salaries)."'";
}

if (!empty($secteur)) {
  $queryFilter[] = "co.secteur = '".$db->escape($secteur)."'";
}

if (!empty($placement_time)) {
  $durations = array(
    '15mn' => 900,
    '30mn' => 1800,
    '1h' => 3600,
    '2h' => 7200,
    '3h' => 10800,
    '1j' => 86400,
    '2j' => 172800,
    '3j' => 259200
  );
  $queryFilter[] = "co.timestamp >= ".(time()-$durations[$placement_time]);
}

if ($unanswered == '1')
  $queryFilter[] = "cs.call_result = 'absence'";
else
  $queryFilter[] = "cs.call_result IN ('absence', 'not_called')";

if ($shown_call_count <= 0)
  $shown_call_count = 5;
elseif ($shown_call_count > 50)
  $shown_call_count = 50;

if (time()%300 < 10) // update every 5mn for 10s
  $db->query("UPDATE call_spool SET timestamp_in_line = 0 WHERE timestamp_in_line BETWEEN 1 AND UNIX_TIMESTAMP()-3600", __FILE__, __LINE__);

$query = "
  SELECT *
  FROM (
    SELECT
      cs.id,
      cs.id_lead,
      cs.id_client,
      cs.operator,
      cs.timestamp,
      cs.timestamp_in_line,
      cs.timestamp_calls,
      cs.calls_count,
      cs.call_result,
      co.nom,
      co.prenom,
      co.fonction,
      co.societe,
      co.salaries,
      co.secteur,
      co.tel,
      co.timestamp as dateLead, 
      pfr.name as product_name
    FROM call_spool cs 
    LEFT JOIN contacts co ON co.id = cs.id_lead 
    LEFT JOIN products_fr pfr ON pfr.id = co.idProduct
    WHERE
      cs.timestamp_in_line = 0 AND
      cs.calls_count < ".Calls::$maxCallsInAbsence." AND
      cs.daily_absence < ".DAILY_NB_UNANSWERED_CALLS."
      ".(!empty($queryFilter) ? " AND ".implode(" AND ",$queryFilter) : "")."
    ORDER BY cs.timestamp ASC
    LIMIT 0, ".$shown_call_count."
  ) c
  INNER JOIN (
    SELECT COUNT(cs.id) AS cnt
    FROM call_spool cs
    LEFT JOIN contacts co ON co.id = cs.id_lead 
    WHERE
      cs.timestamp_in_line = 0 AND
      cs.calls_count < ".Calls::$maxCallsInAbsence." AND
      cs.daily_absence < ".DAILY_NB_UNANSWERED_CALLS."
      ".(!empty($queryFilter) ? " AND ".implode(" AND ",$queryFilter) : "")."
  ) cnt";

$res = $db->query($query, __FILE__, __LINE__);
if ($db->numrows($res, __FILE__, __LINE__) == 0) {
  $o['nbrTotalCalls'] = 0;
  $o['reponses'] = 'vide';
}
else {
  while ($reponse = $db->fetchAssoc($res, __FILE__, __LINE__)) {
    $o['nbrTotalCalls'] = $reponse["cnt"];
    $timestampCalls = mb_unserialize($reponse['timestamp_calls']);
    $reponse['nbrDailyCalls'] = 0;
    foreach($timestampCalls as $timestampCall) {
      if ($timestampCall >= mktime(0, 0, 0) && $timestampCall <= mktime(23, 59, 59))
        $reponse['nbrDailyCalls']++;
    }
    $rdvLead = Rdv::get('id_relation = '.$reponse['id_lead'], 'type_relation = 1', 'active = 1');
    
    if(count($rdvLead) != 0)
      $reponse['rdvExists'] = true;
    
    $o['reponses'][] = $reponse;
  }
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
?>
