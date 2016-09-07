<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 17 mai 2011

 Fichier : /secure/fr/manager/smpo/AJAX_calls-liste.php
 Description : résultat ajax du listing de la pile d'appels

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"][] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

$o = array();

$id           = isset($_GET['id_call'])      ? trim($_GET['id_call']) : '';
$idCampaign   = isset($_GET['id_campaign'])  ? trim($_GET['id_campaign']) : '';
$idClient     = isset($_GET['id_client'])    ? trim($_GET['id_client']) : '';
$setPickedUp  = isset($_GET['setPickedUp'])  ? trim($_GET['setPickedUp']) : 0;
$callOk       = isset($_GET['callOk'])       ? trim($_GET['callOk']) : 0;
$callNok      = isset($_GET['callNok'])      ? trim($_GET['callNok']) : 0;
$callOkNoLead = isset($_GET['callOkNoLead']) ? trim($_GET['callOkNoLead']) : 0;
$testPickedUp = isset($_GET['testPickedUp']) ? trim($_GET['testPickedUp']) : 0;

if (preg_match("/^[1-9]{1}[0-9]{0,8}$/", $id) && ($call = new CallsCampaign($id)) && $call->existsInDB()) {
  if ($setPickedUp){
    if($call->setInLine($user->id) == true)
      $o['result'][] = 'ok';
    else
      $o['error'][] = 'erreur requete';
  }
  elseif ($testPickedUp) {
    if ($call->timestamp_in_line) {
      $o['result'][] = 'testNok';
    }
    else {
      $paramsCall = array(
        'id_call' => $id,
        'id_campaign' => $idCampaign,
        'id_client' => $idClient
      );
      $o['params'][] = $paramsCall;
      $o['result'][] = 'testOk';
    }
  }
  elseif ($callOk) {
    if ($call->id_client == $idClient && $call->id_campaign == $idCampaign) { //$call->id_campaign == $idCampaign &&
      if ($call->setCallOk()) {
        $o['result'][] = 'ok';
        $o['idCampaign'][] = $call->id_campaign;
      }
      else {
        $o['error'][] = 'erreur traitement';
      }
    }
    else {
      $o['error'][] = 'erreur requete';
    }
  }
  elseif ($callNok) {
    if($call->id_client == $idClient) { // $call->id_campaign == $idCampaign && 
      if($call->setCallNok()) {
        $o['result'][] = 'ok';
        $o['idCampaign'][] = $call->id_campaign;
      }
      else {
        $o['error'][] = 'erreur traitement';
      }
    }
    else {
      $o['error'][] = 'erreur requete';
    }
  }
  elseif ($callOkNoLead) {
    if($call->id_client == $idClient) { //$call->id_campaign == $idCampaign && 
      if($call->setCallOkNoLead()) {
        $o['result'][] = 'ok';
        $o['idCampaign'][] = $call->id_campaign;
      }
      else {
        $o['error'][] = 'erreur traitement';
      }
    }
    else {
      $o['error'][] = 'erreur requete';
    }
  }
  else {
    $o['error'][] = 'Instruction incorrecte';
  }
}
else {
  $o['error'][] = "Cet appel n'existe pas";
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>