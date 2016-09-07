<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 19 mai 2011

 Fichier : /secure/contacts/AJAX_internal-notes.php
 Description : retour d'appel ajax de gestion de note interne dans le context de la creation le contact

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$handle = DBHandle::get_instance();

$user = new BOUser();

if(!$user->login())
{

  $o["error"] = "Votre session a expiré, vous devez vous relogger.";
  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
	print json_encode($o);
	exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

$o = array();

$context = 'compte_client';


if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $idCustomer = !empty($_POST['idCustomer']) ? $_POST['idCustomer'] : '';
  $contenu = !empty($_POST['contenu']) ? $_POST['contenu'] : '';
  $action = !empty($_POST['action']) ? $_POST['action'] : '';
//  $commande = !empty($_POST['idCommand']) ? $_POST['idCommand'] : '';
}
elseif($_SERVER['REQUEST_METHOD'] == 'GET'){
  $idCustomer = !empty($_GET['idCustomer']) ? $_GET['idCustomer'] : '';
  $contenu = !empty($_GET['contenu']) ? $_GET['contenu'] : '';
  $action = !empty($_GET['action']) ? $_GET['action'] : '';
//  $commande = !empty($_GET['idCommand']) ? $_GET['idCommand'] : '';
}
else {
  $o["error"] = "Erreur grave";
	print json_encode($o);
	exit();
}
//$id_reference = $idCustomer.'-'.$commande;

if (empty($idCustomer) || empty($action)) { //  || empty($commande)
	$o["error"] = "RequÃ¨te incorrecte";
	print json_encode($o);
	exit();
}
if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expirÃ©e, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$customer = new CustomerUser($handle);
$customer->getCustomerFromLogin($idCustomer);


if(!$customer->exists){

  $o["error"] = "Le client recherchÃ© est introuvable";
	print json_encode($o);
	exit();
}

if($action == 'get'){

  $notesInternes = new InternalNotesOld($context);
  $notes = $notesInternes->getAllNotesByIdRef($customer->login);

  if(!empty($notes))
    foreach ( $notes as $note => $contenu){
      $notes[$note]['date'] = date('d/m/Y à H:i:s', $contenu['timestamp']);
      $sender = new BOUser($contenu['operator']);
      $notes[$note]['sender_name'] = $sender->name;
    }

  $o['notes'] = !empty($notes) ? $notes : 'vide';

}elseif($action == 'add'){
  if (empty($contenu)) {
	$o["error"] = "Note interne sans contenu";
	print json_encode($o);
	exit();
  }

  $noteInterne = new InternalNotesOld($context);

  if($noteInterne->addNote($user, $contenu, $customer->login))
    $o["result"] = 'ok';

} else {
  $o["error"] = "Action impossible";
	print json_encode($o);
	exit();
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
?>