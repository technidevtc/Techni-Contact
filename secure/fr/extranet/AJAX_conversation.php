<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : ODpour Hook Network SARL - http://www.hook-network.com
 Date de cr?ion : 16 f?ier 2011

 Fichier : /secure/extranet/AJAX_conversation.php
 Description : retour d'appel ajax de gestion de messagerie

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

//header('Content-Type: text/plain; charset=utf-8');

require_once(ADMIN.'logs.php');
require_once(ADMIN.'logo.php');
require_once(ICLASS.'ExtranetUser.php');

$db = DBHandle::get_instance();
$user = new ExtranetUser($db);

$o = array();
$inputs = filter_input_array(
  INPUT_POST,
  array(
    'idUser' => FILTER_VALIDATE_INT,
    'action' => FILTER_SANITIZE_STRING,
    'ordre' => FILTER_VALIDATE_INT,
    'lead' => FILTER_VALIDATE_INT,
    'contenu' => FILTER_SANITIZE_STRING,
    'copyToSender' => FILTER_VALIDATE_INT
  )
);
foreach ($inputs as $k => $v)
  $$k = $v;

try {

if (empty($idUser) || empty($action) || (empty($ordre) && empty($lead)))
  throw new Exception("Requète incorrecte");

if (!$user->login($login, $pass) || !$user->active)
  throw new Exception("Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page");

if ($idUser != $user->id)
  throw new Exception("Erreur d'identification");

switch ($action) {
  case 'get':
    if(!empty ($ordre))
      $context = __MSGR_CTXT_ORDER_CMD__;
    elseif(!empty ($lead)){
      $context = __MSGR_CTXT_CUSTOMER_ADVERTISER_LEAD__;
      $ordre = $lead;
    }
    if(!empty ($context))
      $data = Messenger::getConversation($context, $user->id, $ordre);
    
    $o['conversations'] = empty($data) ? "vide" : $data;
    break;

  // send message concerning order to TC BO operator
  case 'add':
    if (empty($contenu))
      throw new Exception("Information manquante");

    if (!empty($ordre)) { // send message about order to TC
      
      $m = new Messenger();
      $m->context = __MSGR_CTXT_SUPPLIER_TC_ORDER__;
      $m->type_sender = __MSGR_USR_TYPE_ADV__;
      $m->id_sender = $user->id;
      $m->type_recipient = __MSGR_USR_TYPE_BOU__;
      $m->id_recipient = __ID_TECHNI_CONTACT_BOUSER__;
      $m->reference_to = $ordre;
      $m->text = $contenu;
      $m->save();
    
    } elseif (!empty($lead)) { // send message about lead to customer
      
      $m = new Messenger();
      $m->context = __MSGR_CTXT_CUSTOMER_ADVERTISER_LEAD__;
      $m->type_sender = __MSGR_USR_TYPE_ADV__;
      $m->id_sender = $user->id;
      $m->type_recipient = __MSGR_USR_TYPE_INT__;
      $m->reference_to = $lead;
      $m->text = $contenu;
      $l = $m->getLead();
      $l->client->genTempAuthToken(); // gen a temp token for use in the email
      $l->client->save(); // client isn't directly linked to messenger, so the save will not cascade automatically
      $m->id_recipient = $l->client->id;
      $m->sendCopyToSender($copyToSender);
      $m->setAttachmentCtx("lead-tmppjmess");
      $m->save();
      
    }

    $o['result'] = "Message envoyé avec succès";
    break;
    
  default:
    throw new Exception("Action impossible");
}

} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  $o['error'] = "Erreur fatale: ".$e->getMessage();
}

print json_encode($o);
