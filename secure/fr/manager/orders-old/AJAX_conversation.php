<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 22 février 2011

 Fichier : /secure/extranet/AJAX_conversation.php
 Description : retour d'appel ajax de gestion de messagerie

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

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

$context = __MSGR_CTXT_SUPPLIER_TC_ORDER__;


if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $idUser = !empty($_POST['idUser']) ? $_POST['idUser'] : '';
  $contenu = !empty($_POST['contenu']) ? $_POST['contenu'] : '';
  $action = !empty($_POST['action']) ? $_POST['action'] : '';
  $ordre = !empty($_POST['ordre']) ? $_POST['ordre'] : '';
//  foreach ($_POST as $attr => $val)
//    $$attr = $val;
}elseif($_SERVER['REQUEST_METHOD'] == 'GET'){
  $idUser = !empty($_GET['idUser']) ? $_GET['idUser'] : '';
  $contenu = !empty($_GET['contenu']) ? $_GET['contenu'] : '';
  $action = !empty($_GET['action']) ? $_GET['action'] : '';
  $ordre = !empty($_GET['ordre']) ? $_GET['ordre'] : '';
//  foreach ($_GET as $attr => $val)
//    $$attr = $val;
}else {
  $o["error"] = "Erreur grave";
	print json_encode($o);
	exit();
}

if (empty($idUser) || empty($action) || empty($ordre)) {
	$o["error"] = "RequÃ¨te incorrecte";
	print json_encode($o);
	exit();
}
if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expirÃ©e, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
	print json_encode($o);
	exit();
}
$supplier = new AdvertiserOld($idUser);
if($action == 'get'){

  
  $messagerie = new MessengerOld($handle, $supplier, $context);
  $conv = $messagerie->getConversationFromReference($ordre);

  if(!empty($conv))
    foreach ( $conv as $message => $contenu){
      $conv[$message]['date'] = date('d/m/Y à H:i:s', $conv[$message]['timestamp']);
      $conv[$message]['text'] = to_entities($conv[$message]['text']);
    }
  

  $o['conversations'] = !empty($conv) ? $conv : 'vide';

}elseif($action == 'add'){
  if (empty($contenu)) {
	$o["error"] = "Message vide";
	print json_encode($o);
	exit();
  }

  $messagerie = new MessengerOld($handle, $user, $context);
  if($messagerie->sendMessageToAdvertiser($contenu, $idUser, $ordre)){

    // Mail envoyé aux fournisseurs
if(!$supplier->email)
        $o["error"] = 'Adresse mail absente';
    $arrayEmail = array(
      "email" => $supplier->email,
      "subject" => "Information sur commande n ".$idUser."-".$ordre,
      "headers" => "From: Service Achat Techni-Contact <achat@techni-contact.com>\nReply-To: Service Achat Techni-Contact <achat@techni-contact.com>\r\n",
      "template" => "partner-bo_messagerie-answer_supplier",
      "data" => array(
        'CMD_NUMBER' => $idUser."-".$ordre,
        'MESSAGE_CONTENT' => to_entities($contenu, ENT_QUOTES, "UTF-8"),
        'LIEN_CMD' => EXTRANET_URL.'commande.html?idCommande='.$idUser."-".$ordre
      )
    );
    
    $mail = new Email($arrayEmail);
    if($mail->send()){
      $order = new OrderOld($handle, $idUser."-".$ordre);
      $order->setMessageAnswered();
      $o['result'] = 'Message envoyÃ© avec succÃ¨s'; 
    }else
      $o["error"] = 'Erreur Ã l\'envoi du message';

    
    
    $arrayEmail = array();

      
  }else
      $o["error"] = 'Erreur Ã l\'envoi du message';


} elseif($action == 'end'){

  $order = new OrderOld($handle, $idUser."-".$ordre);
  $order->setMessageAnswered();
  $o['result'] = 'Conversation close';
}else {
  $o["error"] = "Action impossible";
	print json_encode($o);
	exit();
}
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
?>