<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 19 mai 2011

 Fichier : /secure/fr/manager/contacts/AJAX_getLastCall.php
 Description : récupère les information du dernier appel effectué vers le client d'après la pile d'appels de lead ou une campagne d'appels

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require_once(ADMIN."logs.php");

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expir&eacute;, veuillez vous identifier &agrave; nouveau apr&egrave;s avoir rafra&icirc;chi votre page";
  print json_encode($o);
  exit();
}

// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];

$userPerms = $user->get_permissions();

if (!$userPerms->has($fntByName["m-smpo--sm-call-list"], "re")){
  $errorstring .= 'Vous n\'avez pas les droits nécessaires';
}

if (!empty($_GET['action']) && $_GET['action'] == 'get_call_infos' && !empty($_GET['customerEmail'])) {
  if (!preg_match('/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/', $_GET['customerEmail'])) // email test
    $o["error"] = "Format d'email incorrect";
  else {
    
    $lastCall = new Calls(Calls::getLastConvIdFromEmail($_GET['customerEmail']));
    $lastCallC = new CallsCampaign(CallsCampaign::getLastConvIdFromEmail($_GET['customerEmail']));
    
    if (!$lastCall->timestamp && !$lastCallC->timestamp)
      $o["error"] = "Aucun appel trouvé pour ce client";
    elseif ($lastCall->timestamp < $lastCallC->timestamp)
      $lastCall = $lastCallC;

    if ($lastCall->timestamp) {
      $operator = new BOUser($lastCall->operator);
      $status = $lastCall->timestamp_in_line ? 'Appel en cours' : $lastCall->call_resultTitle ;
      $reponse = array(
        "operator"    => $operator->name,
        "date"        => $lastCall->calls_count > 0 ? date('d/m/Y à H:i:s', $lastCall->timestamp_calls[$lastCall->calls_count-1]) : date('d/m/Y à H:i:s', $lastCall->timestamp),
        "status"      => $status,
        "callResult"  => $lastCall->call_result,
        "pendingCall" => ($lastCall->hasPendingCall($lastCall->id_client) || $lastCall->timestamp_in_line),
        "callId"      => $lastCall->id
      );
      $o['reponse'] = $reponse;
    }
  }
}
elseif (!empty($_POST['customerEmail']) && !empty($_POST['idCall']) && !empty($_POST['action']) && $_POST['action'] == 'GetOutOfSpool') {
  if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $_POST['idCall'])) {
    $call = new Calls($_POST['idCall']);
    if ($call->existsInDB()) {
      $call->setCustomerCallsBack();
      $o['sortie'] = 'ok';
    }
    else {
      $o["error"] = "identifiant call incorrect";
    }
  }
  elseif (empty($_POST['idCall'])) {
    $lastCall = new Calls(Calls::getLastConvIdFromEmail($_POST['customerEmail']));
    if ($lastCall->existsInDB()) {
      $lastCall->setCustomerCallsBack();
      $o['sortie'] = 'ok';
    }
    else {
      $o["error"] = "email call incorrect";
    }
  }
  else {
    $o["error"] = "appel incorrect";
  }

}
else {
  $o['error'] = 'Requête incorrecte';
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>
