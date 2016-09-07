<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 10 mai 2011

 Fichier : /secure/fr/manager/smpo/AJAX_campaign-liste.php
 Description : résultat ajax du listing des campagnes

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirÃ©, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

$errorstring = '';

$userPerms = $user->get_permissions();

// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];

if (!$userPerms->has($fntByName["m-smpo--sm-campaign"], "re")) {
  $errorstring .= 'Vous n\'avez pas les droits nécessaires';
}

$db = DBHandle::get_instance();
$o = array();

if (!empty($_POST['supprime_campagne']) && $_POST['supprime_campagne'] == 1 && !empty($_POST['campaignID'])) {
  $campaign = new Campaign($_POST['campaignID']);
  if ($campaign->exists) {
    if ($campaign->isDeletable()) {
      if ($userPerms->has($fntByName["m-smpo--sm-campaign"], "d")) {
        $campaign->delete($_POST['campaignID']);
        $o['delete'] = 'ok';
      }
      else
        $errorstring = 'Vous n\'avez pas les droits requis pour effectuer cette actionS';
    }
    else
      $errorstring = 'Il est impossible de supprimer cette campagne';
  }
  else
    $errorstring = 'Cette campagne n\'existe pas';

  if ($errorstring)
    $o['error'] = $errorstring;
}

$sort     = isset($_GET['sort'])     ? trim($_GET['sort']) : 'date';
$lastsort = isset($_GET['lastsort']) ? trim($_GET['lastsort']) : 'date';
$sortway  = isset($_GET['sortway'])  ? trim($_GET['sortway']) : 'asc';

$queryFilter = array();

$res = $db->query("SELECT COUNT(*) FROM campaigns", __FILE__, __LINE__);
list($nbcmd) = $db->fetch($res);

$query = "
  SELECT cp.id, cp.nom, cp.timestamp
  FROM campaigns cp
  ORDER BY ";

// ordre de tri
if ($sort == $lastsort && $sort != '') {
  if ($lastpage == $page) $sortway = ($sortway == 'asc' ? 'DESC' : 'ASC');
  else $sortway = ($sortway == 'asc' ? 'ASC' : 'DESC');
}
else {
  $sortway = 'ASC';
}

switch ($sort) {
  case "date" : $query .= "cp.timestamp ".$sortway; break;
  case "name" : $query .= "cp.nom ".$sortway; break;
  default : $query .= "cp.timestamp DESC";
}
$lastsort = $sort;

if (!$errorstring) {
  $res = $db->query($query, __FILE__, __LINE__);
  if ($db->numrows($res, __FILE__, __LINE__) == 0)
    $o['reponses'] = 'vide';
  else {
    while ($reponse = $db->fetchAssoc($res, __FILE__, __LINE__)) {
      $campaign = new Campaign($reponse['id']);
      if ($campaign->exists) {
        $nbrCalls = $campaign->getNbrCalls();
        if ($nbrCalls > 0) {
          $nbrEffectiveCalls = $campaign->getNbrEffectiveCalls();
          $nbrCallsMade = $campaign->getNbrCallsMade();
          $callsMadeRate = $nbrEffectiveCalls != 0 ? ($nbrCallsMade/$nbrEffectiveCalls)*100 : 0;
          $nbLeadsMade = $campaign->getNbrLeadsMade();
          $leadsMadeRate = $nbrCallsMade != 0 ? ($nbLeadsMade/$nbrCallsMade)*100 : 0;
          $reponse['nbCallsTotal'] = $nbrCalls;
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
          $reponse['nbCallsTotal'] = $reponse['nbCallsToDo'] = $reponse['callsMadeRate'] = $callsMadeRate = $reponse['nbCallsMade'] = $reponse['nbLeadsMade'] = $reponse['leadsMadeRate'] = $reponse['status_title'] = $reponse['status_value'] = $reponse['nbCallsInAbsence'] = 0;
          $reponse['error'] = 'Cette campagne ne contient aucun appel';
        }
        $o['reponses'][] = $reponse;
      }
    }
    $switchSortway = $sortway == 'ASC' ? SORT_ASC : SORT_DESC;
    switch ($sort) {
      case 'status':
        foreach ($o['reponses'] as $key => $reponse) {
          $status_title[$key] = $reponse['status_title'];
        }
        array_multisort($status_title, $switchSortway, $o['reponses']);
        break;
      case 'callsLeft':
        foreach($o['reponses'] as $key => $reponse) {
          $status_title[$key] = $reponse['status_title'];
        }
        array_multisort($status_title, $switchSortway, $o['reponses']);
        break;
    }
  }
}
else {
  $o['error'] = $errorstring;
}

if ($nbcmd > NB)
  $lastpage = ceil($nbcmd/NB);
  
$o['pagination'] = array('lastsort' => $lastsort , 'sort' =>  $sort, 'sortway' =>  $sortway);
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
?>