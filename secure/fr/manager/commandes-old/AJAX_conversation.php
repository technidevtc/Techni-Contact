<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 22 février 2011
 Mise à jour : 26/08/2011

 Fichier : /secure/extranet/commandes/AJAX_conversation.php
 Description : retour d'appel ajax de gestion de messagerie

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN.'generator.php');
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");
require_once(ICLASS."ExtranetUser.php");

$handle = DBHandle::get_instance();

if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expirÃ©e, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array();

$context = __MSGR_CTXT_CUSTOMER_TC_CMD__;


if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $idUser = !empty($_POST['idUser']) ? $_POST['idUser'] : '';
  $contenu = !empty($_POST['contenu']) ? $_POST['contenu'] : '';
  $action = !empty($_POST['action']) ? $_POST['action'] : '';
  $commande = !empty($_POST['ordre']) ? $_POST['ordre'] : '';
  $pjMessenger = !empty($_POST['pjMessenger']) ? $_POST['pjMessenger'] : '';
//  foreach ($_POST as $attr => $val)
//    $$attr = $val;
}elseif($_SERVER['REQUEST_METHOD'] == 'GET'){
  $idUser = !empty($_GET['idUser']) ? $_GET['idUser'] : '';
  $contenu = !empty($_GET['contenu']) ? $_GET['contenu'] : '';
  $action = !empty($_GET['action']) ? $_GET['action'] : '';
  $commande = !empty($_GET['ordre']) ? $_GET['ordre'] : '';
//  foreach ($_GET as $attr => $val)
//    $$attr = $val;
}else {
  $o["error"] = "Erreur grave";
	print json_encode($o);
	exit();
}

if (empty($idUser) || empty($action) || empty($commande)) {
	$o["error"] = "RequÃ¨te incorrecte";
	print json_encode($o);
	exit();
}
if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expirÃ©e, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
	print json_encode($o);
	exit();
}
$CustomerUser = new CustomerUser($db, $idUser);

if($action == 'get'){

  $messagerie = new MessengerOld($handle, $CustomerUser, $context);
  $conv = $messagerie->getConversationFromReference($commande);
//  var_dump($conv);
  if(!empty($conv))
    foreach ( $conv as $message => $contenu){
      $conv[$message]['date'] = date('d/m/Y à H:i:s', $conv[$message]['timestamp']);
      $conv[$message]['text'] = to_entities($conv[$message]['text']);

      // récupération des pièces jointes
      $uploaded_pj_table = Doctrine::getTable('MessengerPjs');
      $pjs = $uploaded_pj_table->findById_messenger($conv[$message]['id']);
      $pjList = array();
      foreach($pjs as $pj){
        $pjListTable = Doctrine::getTable('UploadedFiles');
        $pjList[] = $pjListTable->findOneByIdAndContext($pj->id_uploaded_files, 'bo-pjmess')->toArray();
      }
      $conv[$message]['attachment'] = $pjList;
    }
  

  $o['conversations'] = !empty($conv) ? $conv : 'vide';

}elseif($action == 'add'){
  if (empty($contenu)) {
	$o["error"] = "Message vide";
	print json_encode($o);
	exit();
  }

  $containsPjs = false;
  if(!empty ($pjMessenger) && is_array($pjMessenger) && count($pjMessenger) > 0)
      $containsPjs = true;

  //mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $contenu);
    $messagerie = new MessengerOld($handle, $user, $context);
    if($messagerie->sendMessageToAdvertiser($contenu, $idUser, $commande)){

    if($containsPjs){
      $pjsList = array();
        foreach($pjMessenger as $uploaded_pj){
          $uploaded_pj_table = Doctrine::getTable('UploadedFiles');
          $pj = $uploaded_pj_table->findOneByIdAndContext($uploaded_pj, 'bo-tmp-pjmess');
          if($pj){
            $pj->context = 'bo-pjmess';
            $pj->save();

            $pjsList[] = $pj->toArray();
          }
          $pjByMessage = new MessengerPjs();
          $pjByMessage->id = doctrine_generateID(1, 0x7fffffff,MessengerPjs, 'id');
          $pjByMessage->id_messenger = $messagerie->id;
          $pjByMessage->id_uploaded_files = $uploaded_pj;
          $pjByMessage->save();

        }
      }
  // Mail envoyé au client


  //    Le message envoyé par TC au client comprend les éléments suivants :

  // Contenu



  $CustomerUserEmail = !$CustomerUser->email ? $CustomerUser->login : $CustomerUser->email;
  if(!$CustomerUserEmail)
          $o["error"] = 'Adresse mail absente';

  // attachment management
    $attachments = '';
    if(!empty ($pjsList)){
      $attachments = '<br /><br />Documents joints à ce message :<br /><br />';
      foreach($pjsList as $pj){
        $filename = !empty ($pj['alias_filename']) ? $pj['alias_filename'] : $pj['filename'];
        $attachments .= $filename.'.'.$pj['extension'].' : <a target="_blank" href="'.BO_UPLOAD_DIR.'messenger/'.$pj['filename'].'.'.$pj['extension'].'">ouvrir le fichier</a><br />';
      }
    }

    $arrayEmail = array(
      "email" => $CustomerUserEmail,
      "subject" => "Message concernant votre commande n ".$commande,
      "headers" => "From: Techni-Contact - Service commercial<sav@techni-contact.com>\nReply-To: Techni-Contact - Service commercial<commandes@techni-contact.com>\r\n",
      "template" => "user-bo_messenger-answer",
      "data" => array(
        'CUSTOMER_NAME' => $CustomerUser->prenom.' '.$CustomerUser->nom,
        'MESSAGE' => to_entities($contenu, ENT_QUOTES, "UTF-8"),
        'CMD_NUMBER' => $commande,
        'SITE_ACCOUNT_URL_LOGIN' => COMPTE_URL.'login.html',
        'CUSTOMER_MAIL' => $CustomerUserEmail,
        'ATTACHMENTS' => $attachments
      )
    );
    
    $mail = new Email($arrayEmail);
    if($mail->send()){
      $command = new Command($handle, $commande);
      $command->setMessageAnswered();
      $o['result'] = 'Message envoyÃ© avec succÃ¨s'; 
    }else
      $o["error"] = 'Erreur Ã l\'envoi du message';

    
    
    $arrayEmail = array();

      
  }else
      $o["error"] = 'Erreur Ã l\'envoi du message';


} elseif($action == 'end'){

  $command = new Command($handle, $commande);
  $command->setMessageAnswered();
  $o['result'] = 'Conversation close';
}else {
  $o["error"] = "Action impossible";
	print json_encode($o);
	exit();
}
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
?>